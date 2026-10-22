<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.1.0
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Deactivator
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GroupOne_UC_Deactivator {


	/**
	 * deactivation/uninstall hooks
	 */
	public function uc_deactivate_actions() {
		// Clear cache upon plugin deactivation
		$uc_cache_obj = new GroupOne_UC_Cache_Purge();
		$uc_cache_obj->uc_purge_cache();
	}
}
