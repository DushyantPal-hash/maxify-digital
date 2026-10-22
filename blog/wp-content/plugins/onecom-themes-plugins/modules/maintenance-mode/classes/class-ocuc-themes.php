<?php

/**
 * Hook into frontend to load under construction theme
 *
 * @since      0.1.0
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Themes
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GroupOne_UC_Themes {

	// Main hook to call under construction feature
	public function init_theme() {
		// actions based on settings
		$this->uc_action();

		// under construction page rendering on frontend
		add_action( 'template_redirect', array( $this, 'under_construction' ) );
		// Get UC Options data
		$uc_data   = new GroupOne_UC_Render_Views();
		$uc_option = $uc_data->get_uc_option();

		// Revert WooCommerce lost-password if uc is on
		if ( is_array( $uc_option ) && isset( $uc_option['uc_status'] ) && 'on' === $uc_option['uc_status'] ) {
			add_filter( 'lostpassword_url', array( $this, 'uc_reset_pass_url' ), 999, 0 );
		}
	}

	// Revert WooCommerce /my-account/lost-password to default
	public function uc_reset_pass_url() {
		$args             = array( 'action' => 'lostpassword' );
		$lostpassword_url = add_query_arg( $args, network_site_url( 'wp-login.php', 'login' ) );
		return $lostpassword_url;
	}

	// Perform certain actions based on timer settings
	public function uc_action() {
		// Get UC Options data
		$uc_data   = new GroupOne_UC_Render_Views();
		$uc_option = $uc_data->get_uc_option();

		/**
		 * Disable UC status if countdown action is set to 'disable' for past date
		 * Checks: valid date, switch is on, past timer and disable countdown set
		 */
		$uc_timer_action = isset( $uc_option['uc_timer_action'] ) ? $uc_option['uc_timer_action'] : '';
		$uc_timer        = isset( $uc_option['uc_timer'] ) ? $uc_option['uc_timer'] : '';
		$uc_timer_switch = isset( $uc_option['uc_timer_switch'] ) ? $uc_option['uc_timer_switch'] : '';
		if (
			strtotime( $uc_timer ) !== false &&
			'on' === $uc_timer_switch &&
			strtotime( $uc_timer ) < strtotime( current_time( 'Y-m-d H:i:s' ) ) &&
			'disable' === $uc_timer_action &&
			'on' === $uc_option['uc_status']
		) {
			$uc_option['uc_status'] = 'off';
			update_option( 'onecom_under_construction_info', $uc_option, 'no' );
		}

		/**
		 * Disable UC timer in admin if action is set to 'hide' timer for past date
		 * Checks: valid date, switch is on, past timer and disable countdown set
		 */
		$uc_timer_action = isset( $uc_option['uc_timer_action'] ) ? $uc_option['uc_timer_action'] : '';
		if (
			strtotime( $uc_timer ) !== false &&
			'on' === $uc_timer_switch &&
			strtotime( $uc_timer ) < strtotime( current_time( 'Y-m-d H:i:s' ) ) &&
			'hide' === $uc_timer_action
		) {
			$uc_option['uc_timer_switch'] = 'off';
			update_option( 'onecom_under_construction_info', $uc_option, 'no' );
		}
	}

	/**
	 * Check if current request needs to whitelist for migrator (via user-agent) or performance monitor scan (via IP 46.30.212.65)
	 * Check if migrator request for WP directory detection (using wp_migrate_test_file_<epoch>)
	 */
	public function uc_whitelist() {
		$headers = getallheaders();

		if (
			( is_array( $headers ) && array_key_exists( 'User-Agent', $headers ) && 'ocm-client' === $headers['User-Agent'] ) ||
			( is_array( $headers ) && array_key_exists( 'X-Forwarded-For', $headers ) && '46.30.212.65' === $headers['X-Forwarded-For'] )
		) {
			return true;
		}

		return false;
	}

	// Render under construction feature and exit (die) to prevent wp theme loading
	public function under_construction() {
		global $wp;

		// Get UC Options data
		$uc_data   = new GroupOne_UC_Render_Views();
		$uc_option = $uc_data->get_uc_option();

		// Check if current user is logged-in
		if ( is_user_logged_in() ) { //@todo checkif capabilities needs to be added here
			$whitelisted_users = filter_var( $uc_option['uc_whitelisted_roles'] ?? false, FILTER_VALIDATE_BOOLEAN );
			// if whitelist users is on bypass all the logged in users
			if ( $whitelisted_users === true ) {
				return null;
			}

		}

		/**
		 * Whitelist pages
		 * If current post/page is found in exclude list, return
		 * Note: Home (with Posts Page settings) also returns 'post' type
		 * Skip whitelist check if MWP addon is not active
		 */
		if (
			isset( $uc_option['uc_exclude_pages'] )
			&& ! empty( $uc_option['uc_exclude_pages'] )
			&& is_array( $uc_option['uc_exclude_pages'] )
		) {

			// Proceed only if MWP addon is active
			$features = groupone_set_premi_flag();
			$is_mwp_active = ! empty( $features['data'] ) && in_array( 'MWP_ADDON', $features['data'], true );

			if ( $is_mwp_active ) {

				// merge all cpt ids to simply match current id with in_array()
				$excluded_ids = array();
				foreach ($uc_option['uc_exclude_pages'] as $post_types) {
					$excluded_ids = array_merge($excluded_ids, $post_types);
				}
				$current_id = get_queried_object_id();

				// If current post/page id found in excluded, return
				if (
					!empty($excluded_ids)
					&& !is_home()
					&& (in_array((string)$current_id, $excluded_ids, true)
						|| in_array('all-' . get_post_type(), $excluded_ids, true)
					)
				) {
					return null;
				}
			}
		}

		/**
		 * If under construction status enabled
		 * * show under construction page
		 */
		if ( is_array( $uc_option ) && isset( $uc_option['uc_status'] ) && 'on' === $uc_option['uc_status'] ) {
			// Send 503 headers if maintenance mode
			$uc_http_mode = $uc_option['uc_http_mode'] ?? '503';

			/*
			 * WPIN-8909: these header() calls used to carry "@phpunit-todo -
			 * comment before phpunit" because the shared CI script
			 * (wp-pipelines-templates scripts/pre-unit-tests.sh) commented
			 * them out before the suite ran. That script still targets
			 * ./inc/classes/class-ocuc-themes.php and only runs its
			 * maintenance-mode branch when the plugin's main file is
			 * ./onecom-under-construction.php - neither is true since the
			 * merge, so it has silently done nothing here and these lines
			 * account for five of the suite's errors. Guarding on
			 * headers_sent() fixes it in the plugin instead of in CI: once
			 * output has begun header() only emits a warning and changes
			 * nothing, so skipping it is correct in production too.
			 */
			if ( ! headers_sent() ) {
				// Always send custom header for MM.
				header( 'x-onecom-maintenance-mode: on' );

				// Prevent http 503 for some whitelisted requests.
				if ( '503' === $uc_http_mode && ! $this->uc_whitelist() ) {
					header( 'HTTP/1.1 503 Service Unavailable' );
				}
			}

			// render selected theme design (default: theme-1)
			$theme_folder = isset( $uc_option['uc_theme'] ) && strlen( $uc_option['uc_theme'] ) ? $uc_option['uc_theme'] : 'theme-1';
			include_once GROUPONE_UC_PLUGIN_URL . 'themes/' . $theme_folder . '/index.php';

			/*
			 * WPIN-8869: the maintenance page has been rendered, so the
			 * request ends here - but a bare die() under PHPUnit ends the
			 * whole process with status 0, mid-suite, which reads as a
			 * passing run rather than a crash (that exact failure mode is
			 * WPIN-8942). The same CI script used to comment this line out
			 * too, and no longer does. Nothing follows it in production, so
			 * returning instead under test changes only the test process.
			 */
			if ( defined( 'PHPUNIT_RUNNING' ) && PHPUNIT_RUNNING ) {
				return;
			}

			die();
		}
	}
}
