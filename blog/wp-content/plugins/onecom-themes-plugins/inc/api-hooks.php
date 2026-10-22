<?php
/**
 * @Version 2.2
 **/
if ( ! defined( 'GROUPONE_ACTIVATE_STR' ) ) {
	define( 'GROUPONE_ACTIVATE_STR', 'activate' );
}
if ( ! defined( 'GROUPONE_DEACTIVATE_STR' ) ) {
	define( 'GROUPONE_DEACTIVATE_STR', 'deactivate' );
}
if ( ! defined( 'GROUPONE_DM_NAME' ) ) {
	define( 'GROUPONE_DM_NAME', 'ONECOM_DOMAIN_NAME' );
}
if ( ! defined( 'ONECOM' ) ) {
	define( 'ONECOM', 'one.com' );
}
if ( ! defined( 'AUTHOR' ) ) {
	define( 'AUTHOR', 'Author' );
}
if ( ! defined( 'OCPUSHSTATS' ) ) {
	define( 'OCPUSHSTATS', 'GroupOne_PushStats' );
}
if ( ! defined( 'THEME' ) ) {
	define( 'THEME', 'theme' );
}
if ( ! defined( 'THEMESPAGE' ) ) {
	define( 'THEMESPAGE', 'themes_page' );
}
if ( ! defined( 'UPDATE' ) ) {
	define( 'UPDATE', 'update' );
}
/**
 * WordPress action to trigger after activating the theme
 **/
add_action( 'after_switch_theme', 'groupone_activate_theme_stats' );
/**
 * WordPress action to trigger after deactivating the theme
 **/
add_action( 'switch_theme', 'groupone_deactivate_theme_stats', 10, 3 );
/**
 * Function after activating the theme
 **/
if ( ! function_exists( 'groupone_activate_theme_stats' ) ) {
	function groupone_activate_theme_stats() {

		$theme = wp_get_theme();

		if ( ONECOM !== strtolower( $theme->display( AUTHOR, false ) ) ) {
			return false;
		}

		$url = ( isset( $_SERVER['HTTP_REFERER'] ) ? parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY ) : '' );

		if ( ! empty( $url ) && $url == 'step=theme' ) {
			$referrer = 'install_wizard';
		} else {
			$referrer = THEMESPAGE;
		}

		( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( 'activate', THEME, $theme->stylesheet, "$referrer" ) : '' );
		return true;
	}
}

/**
 * Function after deactivating the theme
 **/
if ( ! function_exists( 'groupone_deactivate_theme_stats' ) ) {
	function groupone_deactivate_theme_stats( $new_name, $new_theme, $old_theme ) {
		if ( ONECOM !== strtolower( $old_theme->display( AUTHOR, false ) ) ) {
			return false;
		}
		// send stats
		( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( 'deactivate', THEME, $old_theme->stylesheet, THEMESPAGE ) : '' );
		return true;
	}
}


if ( ! function_exists( 'groupone_upgradation_check' ) ) {
	function groupone_upgradation_check( $upgrader_object, $options ) {

		if ( isset( $options['action'] ) && isset( $options['type'] ) && isset( $options['plugins'] ) && $options['action'] == UPDATE && $options['type'] == 'plugin' ) {

			if ( $upgrader_object->skin->plugin_info['AuthorName'] !== ONECOM ) {
				return;
			}

			// send stats
			( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( UPDATE, 'plugin', $upgrader_object->result['destination_name'], 'plugins_page' ) : '' );

		} elseif ( isset( $options['action'], $options['type'] ) && isset( $options['themes'] ) && $options['action'] == UPDATE && $options['type'] == THEME ) {

			if ( $upgrader_object->skin->theme_info->get( AUTHOR ) !== ONECOM ) {
				return;
			}

			// send stats
			( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( UPDATE, THEME, $upgrader_object->result['destination_name'], THEMESPAGE ) : '' );
		}
	}
}
add_action( 'upgrader_process_complete', 'groupone_upgradation_check', 10, 2 );

/**
 * one.com register Imagify partner
 */
function groupone_register_imagify_partner() {
	if ( ! is_admin() ) {
		return;
	}

	if ( ! file_exists( GROUPONE_WP_PATH . 'vendor/imagify/class-imagify-partner.php' ) ) {
		return;
	}

	require_once GROUPONE_WP_PATH . 'vendor/imagify/class-imagify-partner.php';
	if ( Imagify_Partner::has_imagify_api_key() ) {
		return;
	}

	// Saving the Partner ID in DB as a fallback.
	update_site_option( 'imagifyp_id', OCIPID, 'no' );

	// The class needs to be initiated to launch hooks.
	$imagify = new Imagify_Partner( OCIPID );
	$imagify->init();
}
// Save Imagify partner ID
add_action( 'plugins_loaded', 'groupone_register_imagify_partner' );
