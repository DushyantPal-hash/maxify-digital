<?php
/**
 * Global functions for the Spam Protection module.
 *
 * Wrapped in function_exists() guards: this code now runs in the same PHP
 * process as the rest of the hub and its other modules, where a name
 * collision would be a hard fatal (cannot redeclare) instead of the
 * silent redefinition-avoidance a standalone plugin process would never
 * have hit in practice.
 */

if ( ! function_exists( 'groupone_sp_post_values' ) ) {
	function groupone_sp_post_values() {
		$post = $_POST;

		$default_arr = array(
			'email'   => '',
			'author'  => '',
			'comment' => '',
			'subject' => '',
			'url'     => '',
		);
		if ( empty( $post ) || ! is_array( $post ) ) {
			return $default_arr;
		}

		// removed _id from the list for resolving the conflict with woocommere add_to_cart action
		// address removed from list under email to resolve conflict with woocommerce address field
		$dict = array(
			'email'   => array( 'user_email', 'email' ),
			'author'  => array(
				'author',
				'name',
				'user_login',
				'signup_for',
				'log',
				'user',
				'booking_name',
			),
			'comment' => array( 'comment', 'message', 'body', 'excerpt' ),
			'subject' => array( 'subj', 'topic' ),
			'url'     => array( 'url', 'blog_name', 'blogname' ),
		);

		$bypass_fields = array(
			'email_toUser',
			'gtm4wp_name',
			'hidden_field_names',
			'wc_order_attribution_user_agent',
			'et_pb_contact_email_fields_0'
		);

		foreach ( $dict as $dict_key => $subset ) {
			foreach ( $subset as $subset_val ) {
				foreach ( $post as $post_param => $post_value ) {
					if ( in_array( $post_param, $bypass_fields ) ) {
						return $default_arr;

					}

					if ( stripos( $post_param, $subset_val ) !== false ) {

						if ( is_array( $post_value ) ) {
							// Return default array if an array is found
							return $default_arr;
						}
						$default_arr[ $dict_key ] = $post_value;
						break;
					}
				}
				if ( ! empty( $default_arr[ $dict_key ] ) ) {
					break;
				}
			}
		}
		return $default_arr;
	}
}

if ( ! function_exists( 'groupone_log_spam' ) ) {
	function groupone_log_spam( $user_ip, $oc_post, $detected_spam ) {

		if ( '' == $detected_spam ) {
			return false;
		}

		$spam = groupone_get_sp_options( 'onecom_sp_spam_logs' );

		if ( array_key_exists( 'spam_count', $spam ) ) {
			$spam['spam_count']++;
		} else {
			$spam['spam_count'] = 1;
		}

		if ( is_array( $oc_post ) ) {
			$user_email = isset( $oc_post['email'] ) ? $oc_post['email'] : '';
			$user_name  = isset( $oc_post['author'] ) ? $oc_post['author'] : '';
		}

		$url  = GroupOne_Sp::oc_get_spam_url();
		$time = date( 'Y/m/d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) );

		$spam['records'][ $time ] = array( $user_ip, $user_email, $user_name, $url, $detected_spam );

		groupone_save_sp_options( $spam, 'onecom_sp_spam_logs' );
	}
}


if ( ! function_exists( 'groupone_get_sp_options' ) ) {
	function groupone_get_sp_options( $option_name ) {

		$sp_options = get_option( $option_name );

		if ( $sp_options && is_string( $sp_options ) && '' != $sp_options ) {

			$sp_options = json_decode( $sp_options, true );

			// condition added for handling corrupted json & restoring data
			if ( JSON_ERROR_NONE !== json_last_error() ) {

				groupone_sp_restore_data();

			}
		} else {
			$sp_options = array();

		}

		return $sp_options;
	}
}

if ( ! function_exists( 'groupone_save_sp_options' ) ) {
	function groupone_save_sp_options( $sp_options, $option_name ) {

		if ( is_array( $sp_options ) && ! empty( $sp_options ) ) {

			$sp_options = json_encode( $sp_options );
			update_option( $option_name, $sp_options, false );

		} else {
			return false;

		}
	}
}


if ( ! function_exists( 'groupone_spam_protection_activate' ) ) {
	/**
	 * Seeds default Spam Protection options. Hooked onto the hub's
	 * onecom_merged_modules_activate action (see spam-protection.php) instead
	 * of register_activation_hook(), which never fires for a module. Already
	 * idempotent as ported: existing 'checks' are only seeded when no options
	 * exist yet, so settings/logs on existing installs are never clobbered.
	 */
	function groupone_spam_protection_activate() {

		$sp_options = groupone_get_sp_options( 'onecom_sp_protect_options' );
		if ( ! $sp_options || empty( $sp_options ) ) {
			$sp_options['checks']                = groupone_set_default_options();
			$sp_options['sp_protection_version'] = GROUPONE_SP_VERSION;
		}
		groupone_save_sp_options( $sp_options, 'onecom_sp_protect_options' );
		$sp_options = groupone_get_sp_options( 'onecom_sp_protect_options' );

		if ( ! isset( $sp_options['url-shortners'] ) ) {
			$sp_options['url-shortners'] = GroupOne_Sp::oc_get_values_from_api( MIDDLEWARE_URL . GroupOne_Sp::SPAM_URL_SHORTNERS );

		} elseif ( isset( $sp_options['url-shortners'] ) && $sp_options['sp_protection_version'] != GROUPONE_SP_VERSION ) {

			$sp_options['url-shortners'] = GroupOne_Sp::oc_get_values_from_api( MIDDLEWARE_URL . GroupOne_Sp::SPAM_URL_SHORTNERS, $sp_options['url-shortners'] );

		}

		if ( ! isset( $sp_options['exploit-urls'] ) ) {
			$sp_options['exploit-urls'] = GroupOne_Sp::oc_get_values_from_api( MIDDLEWARE_URL . GroupOne_Sp::SPAM_EXPLOIT_URLS );

		} elseif ( isset( $sp_options['exploit-urls'] ) && GROUPONE_SP_VERSION != $sp_options['sp_protection_version'] ) {

			$sp_options['exploit-urls'] = GroupOne_Sp::oc_get_values_from_api( MIDDLEWARE_URL . GroupOne_Sp::SPAM_EXPLOIT_URLS, $sp_options['exploit-urls'] );

		}

		$sp_options['sp_protection_version'] = GROUPONE_SP_VERSION;
		groupone_save_sp_options( $sp_options, 'onecom_sp_protect_options' );
	}
}

if ( ! function_exists( 'groupone_set_default_options' ) ) {
	function groupone_set_default_options() {
		$sp_options = GroupOne_Sp::sp_protect_options();
		$default    = array();

		foreach ( $sp_options as $option ) {
			$default[ $option ] = 'true';

		}
		$default['oc_max_login_val'] = 5;
		$default['oc_block_time']    = 30;
		$default['oc_sp_proburl']    = 'true';
		$default['oc_sp_quickres']   = 'true';
		$default['oc_sp_urlshort']   = 'true';
		$default['oc_spbadusragent'] = 'false';
		$default['oc_spwhitelistip'] = 'false';

		return $default;
	}
}

if ( ! function_exists( 'groupone_sp_is_premium' ) ) {
	function groupone_sp_is_premium() {
		$features = groupone_set_premi_flag();
		if ( isset( $features['data'] ) && ( ! empty( $features['data'] ) ) && ( in_array( 'MWP_ADDON', $features['data'] ) ) ) {
			return true;
		}
	}
}


if ( ! function_exists( 'groupone_sp_restore_data' ) ) {
	/**
	 * function for restoring  JSON data of spam protection settings from WP API
	 */
	function groupone_sp_restore_data() {
		$sp_options = array();

		$sp_options['checks'] = groupone_set_default_options();

		$sp_options['sp_protection_version'] = GROUPONE_SP_VERSION;

		$sp_options['url-shortners'] = GroupOne_Sp::oc_get_values_from_api( MIDDLEWARE_URL . GroupOne_Sp::SPAM_URL_SHORTNERS );

		$sp_options['exploit-urls'] = GroupOne_Sp::oc_get_values_from_api( MIDDLEWARE_URL . GroupOne_Sp::SPAM_EXPLOIT_URLS );

		groupone_save_sp_options( $sp_options, 'onecom_sp_protect_options' );
	}
}
