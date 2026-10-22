<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Activator
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GroupOne_UC_Activator {


	/**
	 * On activation, set default under-construction settings and data if not exists
	 * Note: onecom plugins and WP plugins both returns $pagenow as plugins_page
	 */
	public function uc_activate_actions() {

		// if no option data exists for uc, set default (on first time activation)
		if ( get_option( 'onecom_under_construction_info' ) === false ) {
			update_option( 'onecom_under_construction_info', groupone_uc_default_settings(), 'no' );
		}

		// Clear cache upon plugin activation
		$uc_cache_obj = new GroupOne_UC_Cache_Purge();
		$uc_cache_obj->uc_purge_cache();
	}

}
