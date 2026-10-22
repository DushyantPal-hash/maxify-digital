<?php


class GroupOne_SpWebsiteCheck extends GroupOne_Sp {

	public $sp_options;

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'oc_check_exploit_url' ) );
		$this->sp_options = groupone_get_sp_options( 'onecom_sp_protect_options' );
	}

	/**
	 * Is one of the Spam Protection checks switched on?
	 *
	 * WPIN-8957: $this->sp_options['checks'] only exists once the Spam
	 * Protection settings have been saved at least once. On a site that never
	 * configured the module the option does not exist at all, so
	 * groupone_get_sp_options() hands back an empty array and every
	 * ['checks'][ ... ] read below warned twice - once for the missing 'checks'
	 * key, once for reading an offset on the resulting null. oc_check_exploit_url()
	 * is on template_redirect, so that fired on every single front-end request.
	 *
	 * Reading through here returns false when the option is absent, which
	 * preserves exactly what the unguarded code already did: null == 'true' is
	 * false, so the check was skipped. Deliberately NOT defaulting an absent
	 * option to "on" - that would start blocking visitors on sites that never
	 * enabled Spam Protection, which is the failure mode WPIN-8949 just fixed.
	 *
	 * @param string $key Check name, e.g. 'oc_sp_proburl'.
	 * @return bool
	 */
	private function sp_check_enabled( $key ) {
		return isset( $this->sp_options['checks'][ $key ] )
			&& 'true' === $this->sp_options['checks'][ $key ];
	}

	public function execute( $user_ip, $logs = array(), $sp_options = array(), &$oc_post = array() ) {

		$spam_detected = ' ';

		if ( isset( $this->sp_options['checks']['oc_sp_quickres'] ) && $this->sp_options['checks']['oc_sp_quickres'] != 'false' ) {

			$this->oc_sp_limit_login_attempts();
		}

		$sp_checks = GroupOne_Sp::sp_protect_options();

		foreach ( $sp_checks as $sp_check ) {
			if ( $this->sp_check_enabled( $sp_check ) ) {

				$spam_detected = $this->sp_load_check_modules( $sp_check, $user_ip, $this->sp_options, $oc_post );

				if ( $spam_detected !== false ) {
					break;
				}
			}
		}
		if ( $spam_detected === false ) {
			return false;
		}

		if ( $spam_detected !== ' ' ) {

			groupone_log_spam( $user_ip, $oc_post, $spam_detected );

			return $spam_detected;
		} else {
			return false;
		}
	}

	public function oc_check_exploit_url() {
		// WPIN-8957: runs on template_redirect, i.e. every front-end request -
		// see sp_check_enabled() for why this must not read ['checks'] directly.
		if ( ! $this->sp_check_enabled( 'oc_sp_proburl' ) || ! is_404() ) {
			return false;

		}

		$spam_detected = $this->sp_load_check_modules( 'oc_sp_proburl', '', $this->sp_options );

		if ( $spam_detected === false ) {
			return false;
		}

		groupone_log_spam( $this->get_user_ip(), array(), $spam_detected );

		wp_die(
			"<p style='text-align: center'>" . __( '404 exploit probing detected!', GROUPONE_SP_TEXTDOMAIN ) . '</p>',
			__( 'Spam detected!', GROUPONE_SP_TEXTDOMAIN ),
			array( 'response' => 403 )
		);
		exit();
	}

	public function oc_sp_limit_login_attempts() {

		if ( ! isset( $_POST['log'] ) ) {
			return false;

		}

		$oc_post       = array( 'author' => $_POST['log'] );
		$spam_detected = $this->sp_load_check_modules( 'oc_sp_quickres', $this->get_user_ip(), $this->sp_options, $oc_post );

		if ( $spam_detected === false ) {
			return false;
		}

		groupone_log_spam( $this->get_user_ip(), $oc_post, $spam_detected );
	}

	public function oc_sp_diagnostics( $user_ip, $oc_post ) {

		$options = array(
			'oc_sp_accept'   => array( 'description' => 'HTTP_ACCEPT header' ),
			'oc_sp_referrer' => array( 'description' => 'Invalid HTTP_REFERER' ),
			'oc_sp_long'     => array( 'description' => 'Disable lengthy emails and author names' ),
			'oc_sp_short'    => array( 'description' => 'Disable too short emails and author names' ),
			'oc_sp_bbcode'   => array( 'description' => 'Mark comments having BBCodes as spam' ),
			'oc_sp_urlshort' => array( 'description' => 'Block URL shortening services' ),
			'oc_sp_exploit'  => array( 'description' => 'Analyse comments for exploits' ),
		);

		foreach ( $options as $key => $value ) {

			// WPIN-8957: same missing-'checks' case as sp_check_enabled(). This one
			// is a displayed value rather than a condition, so keep the raw string
			// and fall back to '' - which reads as "not enabled" downstream, the
			// same as the null this used to produce, but without the warnings.
			$options[ $key ]['status'] = $this->sp_options['checks'][ $key ] ?? '';

			$options[ $key ]['result'] = $this->sp_load_check_modules( $key, $user_ip, $this->sp_options, $oc_post );

		}

		return $options;
	}

	public function sp_load_check_modules( $file_name, $user_ip, &$sp_options = array(), &$oc_post = array() ) {

		if ( empty( $file_name ) ) {

			return false;
		}

		$module_file = GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . $file_name . '.php';
		if ( ! file_exists( $module_file ) ) {
			return false;
		}
		require_once $module_file;

		// Rebranding note (GroupOne whitelabel initiative): the rule-check keys
		// (e.g. 'oc_sp_accept') are PERSISTED data -- stored in the
		// 'onecom_sp_protect_options' option on real sites -- so they are never
		// renamed. The declared rule classes, however, dropped their old hub-wide
		// prefix plus its leading 'Oc' segment in favour of a plain 'GroupOne_'
		// prefix (e.g. the class for 'oc_sp_accept' is now 'GroupOne_SPAccept',
		// not '..._OcSpAccept'). Naively prefixing the ucwords() result with 'GroupOne_'
		// would still leave the untouched 'oc_sp_accept' key producing
		// '...OcSpAccept', which no longer resolves to any declared class. The
		// leading 'Oc' segment is stripped from the ucwords() output before the
		// new prefix is applied, so this keeps resolving (case-insensitively, as
		// PHP class name resolution always does) to the renamed rule classes
		// below (e.g. 'oc_sp_accept' -> 'OcSpAccept' -> 'SpAccept' -> 'GroupOne_SpAccept',
		// matching the declared class 'GroupOne_SPAccept').
		$class_name = 'GroupOne_' . preg_replace( '/^Oc/', '', str_replace( ' ', '', ucwords( str_replace( '_', ' ', $file_name ) ) ) );

		$oc_object = new $class_name();
		$response  = $oc_object->execute( $sp_options, $oc_post );

		unset( $oc_object );
		return $response;
	}
}
