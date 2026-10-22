<?php

/**
 * Defines admin settings functions (sections and fields)
 *
 * @since      0.1.0
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Admin_Settings
 *
 * Ported into modules/maintenance-mode (WPIN-8745) unchanged: admin_menu /
 * network_admin_menu registration still targets the top-level page slug
 * 'onecom-wp-under-construction' exactly as the standalone plugin did. Per
 * WPIN-8741, the hub's shared inc/lib menu-decorator classes already re-parent
 * this entry under the hub's own menu regardless of whether it comes from this
 * module or the standalone plugin, so no change is needed here.
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GroupOne_UC_Admin_Settings {

	private $settings_api;

	public function init_admin_settings() {
		$this->settings_api = new GroupOne_UC_Admin_Settings_API();
		add_action( 'admin_menu', array( $this, 'uc_add_page_fn' ) );
		add_action( 'network_admin_menu', array( $this, 'uc_add_page_fn' ) );
		add_action( 'admin_head', array( $this, 'uc_menu_icon_css_fn' ) );
		add_filter('user_can_richedit', '__return_true'); // Make sure rich editor is allowed
		add_action('wp_ajax_ocmm_save_settings', array( $this,'ocmm_handle_save_settings'));


	}


	// Add sub page to the Settings Menu
	public function uc_add_page_fn() {
		// @later-todo - move out as public var if getting used at multiple places
		$menu_title = __( 'Maintenance Mode', 'onecom-uc' );
		add_menu_page( $menu_title, $menu_title, 'manage_options', 'onecom-wp-under-construction', array( $this, 'uc_page_fx' ), 'dashicons-admin-generic' );
	}

	// add uc settings menu icon
	public function uc_menu_icon_css_fn() {
		define( 'OCUC_MENU_ICON_GREY', GROUPONE_UC_DIR_URL . 'assets/images/uc-menu-icon-grey.svg' );
		define( 'OCUC_MENU_ICON_BLUE', GROUPONE_UC_DIR_URL . 'assets/images/uc-menu-icon-blue.svg' );

		echo "<style>.toplevel_page_onecom-wp-under-construction > .wp-menu-image{display:flex !important;align-items: center;justify-content: center;}.toplevel_page_onecom-wp-under-construction > .wp-menu-image:before{content:'';background-image:url('" . OCUC_MENU_ICON_GREY . "');font-family: sans-serif !important;background-repeat: no-repeat;background-position: center center;background-size: 18px 18px;background-color:#fff;border-radius: 100px;padding:0 !important;width:18px;height: 18px;}.toplevel_page_onecom-wp-under-construction.current > .wp-menu-image:before{background-size: 16px 16px; background-image:url('" . OCUC_MENU_ICON_BLUE . "');}.ab-top-menu #wp-admin-bar-purge-all-varnish-cache .ab-icon:before,#wpadminbar>#wp-toolbar>#wp-admin-bar-root-default>#wp-admin-bar-onecom-wp .ab-item:before, .ab-top-menu #wp-admin-bar-onecom-staging .ab-item .ab-icon:before{top: 2px;}a.current.menu-top.toplevel_page_onecom-wp-under-construction.menu-top-last{word-spacing: 10px;}@media only screen and (max-width: 960px){.auto-fold #adminmenu a.menu-top.toplevel_page_onecom-wp-under-construction{height: 55px;}}</style>";
		return true;
	}

	// Display the admin options page
	public function uc_page_fx() {
		if ( is_multisite() ) {
			include_once GROUPONE_UC_PLUGIN_URL . 'inc/multisite-support-banner.php';
		} else {
			$premium_class = $this->settings_api->oc_premium() ? 'oc-premium' : 'oc-non-premium';
			?>
		<div class="oc-uc-wrap gv-activated">
            <div id="oc-error-toast" class="gv-toast-container"></div>
            <div class="gv-p-fluid gv-pt-0">
                <div id="oc-maintenance-mode-root"></div>
            </div>
			<?php
		}
	}


	public function ocmm_handle_save_settings(): void {
		check_ajax_referer( 'ocmm_save_settings' , 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
		}

		if ( empty( $_POST['settings'] ) ) {
			wp_send_json_error( [ 'message' => 'No settings provided.' ] );
		}

		$settings = json_decode( stripslashes( $_POST['settings'] ) , true );

		if ( ! is_array( $settings ) ) {
			wp_send_json_error( [ 'message' => 'Invalid settings format.' ] );
		}

		$settings_api = new GroupOne_UC_Admin_Settings_API();
		if ( !$settings_api->oc_premium() ) {
			$settings = $settings_api->sanitize_options( $settings );
        }
        
        $uc_data = groupone_uc_default_settings();

        // Merge — defaults first, incoming settings override them
        $settings = array_merge( $uc_data, $settings );
        
		// Save to a single option (example: 'onecom_under_construction_info')
		update_option( 'onecom_under_construction_info' , $settings );

		wp_send_json_success( [ 'message' => 'Settings saved.' ] );
	}
}