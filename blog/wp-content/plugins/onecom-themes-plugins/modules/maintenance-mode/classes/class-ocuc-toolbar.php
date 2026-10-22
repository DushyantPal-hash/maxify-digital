<?php

/**
 * Add shortcut to admin and frontend toolbar
 *
 * This class defines under-construction shortcut link in toolbar
 *
 * @since      0.2.0
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Toolbar
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GroupOne_UC_Toolbar {


	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'add_toolbar_link' ), 101 );
		add_action( 'admin_head', array( $this, 'add_ocuc_toolbar_css' ), 10 );
		add_action( 'wp_head', array( $this, 'add_ocuc_toolbar_css' ), 10 );
	}

	public function add_toolbar_link( $wp_admin_bar = null ) {
		if ( ! is_object( $wp_admin_bar ) || ! ( $wp_admin_bar instanceof WP_Admin_Bar ) ) {
			global $wp_admin_bar;
		}

		if ( empty( $wp_admin_bar ) || ! ( $wp_admin_bar instanceof WP_Admin_Bar ) ) {
			return;
		}

		if ( ! is_super_admin() && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$uc_option       = groupone_uc_get_settings();
		$uc_settings_url = admin_url( 'admin.php?page=onecom-wp-under-construction' );

		// Set toolbar shortcut text based on current uc status
		if ( isset( $uc_option['uc_status'] ) && 'on' === $uc_option['uc_status'] ) {
			$uc_status_class = 'ocuc_toggle_settings ocuc_setting_is_on ocwp_ocmm_toolbar_status_link_clicked_event';
			/* translators: %s a text wrapped in span */
			$uc_status = sprintf( __( 'Maintenance Mode is %sON%s', 'onecom-wp' ), '<span class="last_on">', '</span>' );
			/* translators: %s a text wrapped in span */
			$uc_meta_title = sprintf( __( 'Maintenance Mode is %sON%s', 'onecom-wp' ), '', '' );
		} else {
			$uc_status_class = 'ocuc_toggle_settings ocuc_setting_is_off ocwp_ocmm_toolbar_status_link_clicked_event';
			$uc_status       = __( 'Maintenance Mode is OFF', 'onecom-wp' );
			$uc_meta_title   = __( 'Maintenance Mode is OFF', 'onecom-wp' );
		}
		$parent_node = defined( 'GROUPONE_PLUGIN_DOMAIN' ) ? GROUPONE_PLUGIN_DOMAIN : 'onecom-wp';

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'ocuc_options',
				'parent' => $parent_node,
				'title'  => $uc_status,
				'href'   => $uc_settings_url,
				'meta'   => array(
					'title' => $uc_meta_title,
					'class' => $uc_status_class,
				),
			)
		);
	}

	/**
	 * Add css and js for maintenance Mode ON/OFF
	 * @return void
	 */
	public function add_ocuc_toolbar_css() {
		if ( function_exists( 'is_admin_bar_showing' ) && is_admin_bar_showing() ) {
			?>
		<style>

			.ocuc_toggle_settings.ocuc_setting_is_on > a > span {
				color: #E85E0F;
			}

			.ocuc_toggle_settings.ocuc_setting_is_on > a:hover > span {
				color: #72AEE6;
			}

		</style>
			<?php
		}
	}
}

$toolbar = new GroupOne_UC_Toolbar();
