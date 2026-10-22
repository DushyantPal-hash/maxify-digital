<?php

/**
 * Maintenance Mode module bootstrap.
 *
 * Ported from the standalone "one.com Maintenance Mode" plugin
 * (onecom-under-construction, v3.1.16) into modules/maintenance-mode as part
 * of WPIN-8745 / WPIN-8736. This file is required by
 * groupone_load_merged_modules() (inc/module-registry.php) at plugin parse
 * time, and only when the legacy standalone plugin
 * (onecom-under-construction/onecom-under-construction.php) is NOT active -
 * that guard already lives in the registry, so this bootstrap does not
 * duplicate it with its own is_plugin_active() check.
 *
 * Changes vs. the standalone plugin's onecom-under-construction.php:
 * - ONECOM_UC_PLUGIN_MAIN_FILE / ONECOM_UC_PLUGIN_URL / ONECOM_UC_DIR_URL were
 *   renamed to GROUPONE_UC_PLUGIN_MAIN_FILE / _UC_PLUGIN_URL /
 *   _UC_DIR_URL and guarded with defined() checks (WPIN-8758): the legacy
 *   plugin defines these same 3 names unconditionally, and since define()
 *   silently keeps whichever value was set first, having the module share
 *   the legacy plugin's constant names meant the module could end up using
 *   the LEGACY plugin's path if it happened to load first (or vice versa),
 *   which broke file includes that depend on the path being correct - this
 *   is not just a redefinition notice, it is a real fatal risk.
 * - register_activation_hook()/register_deactivation_hook() are gone (they
 *   never fire once this is a module rather than its own plugin file).
 *   Activation-time default-seeding now happens on the hub's own
 *   'onecom_merged_modules_activate' action (fired from
 *   onecom_plugin_activation() in onecom-themes-plugins.php).
 *   GroupOne_UC_Activator::uc_activate_actions() already only writes defaults when
 *   the 'onecom_under_construction_info' option does not exist yet, so this
 *   is safe to replay on every hub activation without clobbering existing
 *   customer settings.
 * - There is no module-level equivalent for register_deactivation_hook(): a
 *   module is not separately "deactivated" the way a standalone plugin is,
 *   and the hub does not expose a matching 'onecom_merged_modules_deactivate'
 *   action. GroupOne_UC_Deactivator is still ported (classes/class-ocuc-deactivator.php)
 *   for parity/tests, but it is intentionally left unhooked here - see the
 *   port summary for WPIN-8745.
 */

// Exit if file accessed directly / loaded outside WordPress.
defined( 'WPINC' ) or die();

// Define essential constants (guarded: a second load in the same process,
// e.g. from tests, would otherwise throw redefinition E_NOTICEs).
if ( ! defined( 'GROUPONE_UC_PLUGIN_MAIN_FILE' ) ) {
	define( 'GROUPONE_UC_PLUGIN_MAIN_FILE', __FILE__ );
}
if ( ! defined( 'GROUPONE_UC_PLUGIN_URL' ) ) {
	// NOTE: despite the "_URL" name this holds a filesystem PATH
	// (plugin_dir_path()), not a URL. That is how the original standalone
	// plugin defined it and other ported code relies on it holding a path,
	// so the (confusing) name is kept as-is rather than "fixed" here.
	define( 'GROUPONE_UC_PLUGIN_URL', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'GROUPONE_UC_DIR_URL' ) ) {
	define( 'GROUPONE_UC_DIR_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Canonical default settings for the module, and the single read point for
 * them.
 *
 * The defaults used to be spelled out twice - once in the activator and once
 * in the settings-save handler - and every consumer read the raw option. That
 * left the whole module dependent on the activation hook having run: on an
 * install where 'onecom_under_construction_info' was never seeded, the option
 * reads as false, the admin app falls back to an empty settings object, and
 * every toggle renders off regardless of what the defaults say. Parsing reads
 * against the defaults makes a missing or partial option behave the same as a
 * freshly seeded one, for the settings screen and the front end alike.
 */
if ( ! function_exists( 'groupone_uc_default_settings' ) ) {
	function groupone_uc_default_settings(): array {
		$start_date = strtotime( current_time( 'Y-m-d H:i:s' ) );

		return array(
			'uc_status'            => 'off',
			'uc_http_mode'         => '503',
			'uc_theme'             => 'theme-1',
			'uc_timer_switch'      => 'on',
			'uc_timer_action'      => 'no-action',
			'uc_timer'             => gmdate( 'Y-m-d H:i', strtotime( '+7 day', $start_date ) ),
			'uc_subscribe_form'    => 'off',
			'uc_whitelisted_roles' => 'true',
			'uc_headline'          => __( 'Something is happening. Check in later!', 'onecom-uc' ),
			'uc_description'       => '',
			'uc_seo_title'         => '',
			'uc_primary_color'     => '',
			'uc_page_bg_color'     => '',
			'uc_seo_description'   => '',
			'uc_footer_scripts'    => '',
			'uc_page_bg_image'     => GROUPONE_UC_DIR_URL . 'assets/images/design-1-bg.jpeg',
			'uc_copyright'         => __( 'Copyright © {year}. All rights reserved', 'onecom-uc' ),
		);
	}
}

if ( ! function_exists( 'groupone_uc_get_settings' ) ) {
	function groupone_uc_get_settings(): array {
		$stored = get_option( 'onecom_under_construction_info' );

		return wp_parse_args( is_array( $stored ) ? $stored : array(), groupone_uc_default_settings() );
	}
}

// Seed activation-time defaults. Replaces register_activation_hook(), which
// never fires for a module. Hooked onto the hub's own activation hook instead;
// GroupOne_UC_Activator::uc_activate_actions() is idempotent (only sets defaults when
// 'onecom_under_construction_info' does not already exist), so replaying this
// on every hub activation is safe for existing installs.
if ( ! function_exists( 'onecom_uc_module_activate' ) ) {
	function onecom_uc_module_activate() {
		require_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-activator.php';
		require_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-cache-purge.php';
		$activate_obj = new GroupOne_UC_Activator();
		$activate_obj->uc_activate_actions();
	}
}
add_action( 'onecom_merged_modules_activate', 'onecom_uc_module_activate' );

// Because ajax handlers work well outside a class (registered at top level,
// exactly as in the standalone plugin - the callbacks are static/global).
add_action( 'wp_ajax_oc_newsleter_sub', array( 'GroupOne_UC_Newsletter', 'newsletter_cb' ) );
add_action( 'wp_ajax_nopriv_oc_newsleter_sub', array( 'GroupOne_UC_Newsletter', 'newsletter_cb' ) );
add_action( 'wp_ajax_ocuc_wp_time', 'groupone_uc_wp_time' );
add_action( 'wp_ajax_nopriv_ocuc_wp_time', 'groupone_uc_wp_time' );
if ( ! function_exists( 'groupone_uc_wp_time' ) ) {
	function groupone_uc_wp_time() {
		echo (int) strtotime( current_time( 'Y-m-d H:i:s' ) );
		wp_die();
	}
}

/**
 * The main plugin class that is used to define:
 * * admin-specific hooks,
 * * and public-facing site hooks.
 */
require_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-loader.php';

// Load module's essential files
$loader = new GroupOne_UC_Loader();
$loader->init_loader();

// Load assets
$assets = new GroupOne_UC_Assets();
$assets->init_assets();

// Initialize admin settings
if ( is_admin() ) {
	$admin_settings_obj = new GroupOne_UC_Admin_Settings();
	$admin_settings_obj->init_admin_settings();
}

// Hook under-construction frontend page. This is the front-end controller:
// it hooks template_redirect -> under_construction(), which is what actually
// shows the maintenance page to site visitors. It must run unconditionally,
// on every relevant front-end request, exactly as the standalone plugin did.
$themes = new GroupOne_UC_Themes();
$themes->init_theme();
