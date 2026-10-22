<?php
/**
 * Plugin Name: one.com
 * Plugin URI:  https://help.one.com/hc/en-us/articles/115005593945
 * Plugin Info:  https://one.com
 * Version:        5.3.5
 * Text Domain:    onecom-wp
 * Domain Path:    /languages
 * Description:    Integrate your WordPress site with the one.com control panel to get improved performance, security and feature updates.
 * Network: true
 * Author:        one.com
 * Author URI:    https://one.com/
 * License:        GPL v2 or later
 *
 *
 *    Copyright 2017-2025 one.com
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 */
defined( 'ABSPATH' ) or die( 'Cheating Huh!' ); // Security.

if ( ! defined( 'GROUPONE_AJAX_NONCE_ACTION' ) ) {
	define( 'GROUPONE_AJAX_NONCE_ACTION', 'groupone_admin_ajax' );
}
if ( ! defined( 'GROUPONE_WP_VERSION' ) ) {
	define( 'GROUPONE_WP_VERSION', '5.3.5' );
}


if ( ! defined( 'GROUPONE_PLUGIN_API_VERSION' ) ) {
	define( 'GROUPONE_PLUGIN_API_VERSION', '1' );
}


if ( ! defined( 'OCIPID' ) ) {
	define( 'OCIPID', 'one' );
}

if ( ! defined( 'GROUPONE_WP_PATH' ) ) {
	define( 'GROUPONE_WP_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'GROUPONE_WP_URL' ) ) {
	define( 'GROUPONE_WP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'OCL_FILE_PATH' ) ) {
	define( 'OCL_FILE_PATH', '/modules/authenticator/authenticator.php' );
}

if( !defined( 'MARKETPLACE_PAGE_SLUG' ) ) {
	define( 'MARKETPLACE_PAGE_SLUG' , 'onecom-marketplace' );
}

if ( ! defined( 'MIDDLEWARE_URL' ) ) {
	$api_version = 'v1.0';
	if ( isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) && substr( $_SERVER['ONECOM_DOMAIN_NAME'], -17 ) === '-testwp.1test.one' ) {
		$ONECOM_WP_ADDONS_API = 'https://wpapi-test.one.com/';
	}
	elseif (
		isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) &&
		substr( $_SERVER['ONECOM_DOMAIN_NAME'], -10 ) === '.1test.one'
	) {
		$ONECOM_WP_ADDONS_API = 'https://wpapi-next.one.com/';
	} elseif (
		isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) &&
		substr( $_SERVER['ONECOM_DOMAIN_NAME'], -9 ) === '.1stg.one'
	) {
		$ONECOM_WP_ADDONS_API = 'https://wpapi-test.one.com/';
	} elseif ( isset( $_SERVER['ONECOM_WP_ADDONS_API'] ) && $_SERVER['ONECOM_WP_ADDONS_API'] != '' ) {
		$ONECOM_WP_ADDONS_API = $_SERVER['ONECOM_WP_ADDONS_API'];
	} elseif ( defined( 'ONECOM_WP_ADDONS_API' ) && ONECOM_WP_ADDONS_API != '' && ONECOM_WP_ADDONS_API != false ) {
		$ONECOM_WP_ADDONS_API = ONECOM_WP_ADDONS_API;
	} else {
		$ONECOM_WP_ADDONS_API = 'http://wpapi.one.com/';
	}
	$ONECOM_WP_ADDONS_API = rtrim( $ONECOM_WP_ADDONS_API, '/' );
	define( 'MIDDLEWARE_URL', $ONECOM_WP_ADDONS_API . '/api/' . $api_version );
}
if ( ! defined( 'WP_API_URL' ) ) {
	$api_version = '1.0';
	define( 'WP_API_URL', 'https://api.wordpress.org/plugins/info/' . $api_version . '/' );
}
if ( ! defined( 'GROUPONE_WP_CORE_VERSION' ) ) {
	global $wp_version;
	define( 'GROUPONE_WP_CORE_VERSION', $wp_version );
}
if ( ! defined( 'GROUPONE_PHP_VERSION' ) ) {
	define( 'GROUPONE_PHP_VERSION', phpversion() );
}
if ( ! defined( 'GROUPONE_PLUGIN_DOMAIN' ) ) {
	define( 'GROUPONE_PLUGIN_DOMAIN', 'onecom-wp' );
}
// Backward-compat alias: inc/lib's shared onecom-nested-menu.php and
// class-onecom-centralized-menu.php (bundled identically in every legacy
// standalone one.com plugin) reference OC_PLUGIN_DOMAIN directly, assuming
// whichever one.com plugin loaded them already defined it -- true when every
// plugin shared the same oc_/OC_ names, broken once this hub renamed its own
// copy. Reproduced live: "Undefined constant OC_PLUGIN_DOMAIN" fatal in
// onecom-spam-protection's bundled onecom-nested-menu.php while building the
// admin_menu (see docs/known-issues-and-verification.md).
if ( ! defined( 'OC_PLUGIN_DOMAIN' ) ) {
	define( 'OC_PLUGIN_DOMAIN', GROUPONE_PLUGIN_DOMAIN );
}


if ( ! defined( 'GROUPONE_HTTP_HOST' ) ) {
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	define( 'GROUPONE_HTTP_HOST', $host );
}

if ( ! defined( 'GROUPONE_CP_LOGIN_URL' ) ) {
	$domain = $_SERVER['ONECOM_DOMAIN_NAME'] ?? '';
	define( 'GROUPONE_CP_LOGIN_URL', sprintf( 'https://one.com/admin/select-admin-domain.do?domain=%s&targetUrl=/admin/managedwp/%s/managed-wp-dashboard.do', $domain, GROUPONE_HTTP_HOST ) );
}


/*  Plugins REST API  */
add_action(
	'rest_api_init',
	function () {
		$oc_api_class = GROUPONE_WP_PATH . '/modules/api/class-onecom-plugins-api.php';
		if ( file_exists( $oc_api_class ) ) {
			require_once $oc_api_class;
			$oc_api = new OnecomPluginsApi();
			$oc_api->register_routes();
		}
		if ( ! class_exists( 'GroupOne_LAUTH' ) && file_exists( GROUPONE_WP_PATH . OCL_FILE_PATH ) ) {
			require_once GROUPONE_WP_PATH . OCL_FILE_PATH;
		}
	}
);


/*
 * Validator (WPIN-8795): always load our own copy, regardless of whether
 * OTPHP\TOTP/ParagonIE\ConstantTime\Base32 already exist globally. Those
 * classes are also bundled (identically) by each absorbed legacy standalone
 * plugin, so when one of them is active alongside this hub, whichever one
 * loads first in a given request satisfies that check for both — leaving
 * the other's copy of validator.php (and everything it defines: constants,
 * helper functions, these two filters) silently skipped. validator.php
 * itself now guards its internal vendor/autoload.php include the same way
 * (see inc/lib/validator.php), so requiring it here unconditionally is safe:
 * require_once already prevents this exact file from running twice, and the
 * shared Composer classes are never redeclared.
 */
require_once GROUPONE_WP_PATH . '/inc/lib/validator.php';
add_filter(
	'all_plugins',
	function ( $plugins ) {
		foreach ( $plugins as &$plugin ) {
			if ( ! in_array( strtolower( $plugin['Author'] ), array( 'one.com', 'onecom' ) ) ) {
				continue;
			}
			$plugin['update-supported'] = 1;
		}

		return $plugins;
	},
	99999
);

add_filter(
	'wp_prepare_themes_for_js',
	function ( $themes ) {
		foreach ( $themes as &$theme ) {
			if ( ! in_array( strtolower( $theme['author'] ), array( 'one.com', 'onecom' ) ) ) {
				continue;
			}
			$theme['autoupdate']['supported'] = 1;
		}

		return $themes;
	},
	99999
);

/**
 * Include stats script file
 **/

require_once GROUPONE_WP_PATH . '/inc/lib/OCPushStats.php';
/**
 * Include API hook file
 **/
require_once GROUPONE_WP_PATH . '/inc/api-hooks.php';


require_once GROUPONE_WP_PATH . '/inc' . DIRECTORY_SEPARATOR . 'class-onecom-shortcuts.php';
require_once GROUPONE_WP_PATH . '/inc' . DIRECTORY_SEPARATOR . 'class-onecom-performance-column.php';
//Temporally disable marketplace modal
/*require_once GROUPONE_WP_PATH . '/inc' . DIRECTORY_SEPARATOR . 'class-onecom-marketplace-modal.php';

add_action( 'init', function () {
	if ( is_admin() ) {
		( new GroupOne_Marketplace_Modal() )->init();
	}
} );*/

add_action( 'admin_init', 'groupone_shortcut_call' );
function groupone_shortcut_call() {
	if ( ! is_multisite() ) {
		$shortcuts = new GroupOne_Shortcuts();
	}
}


add_action( 'init', function () {
	if ( is_admin() ) {
		( new GroupOne_Performance_Column() )->init();
	}
} );

add_action( 'admin_init', 'groupone_walkthrough' );
function groupone_walkthrough() {
	if ( ! is_multisite() ) {
		include_once GROUPONE_WP_PATH . 'inc' . DIRECTORY_SEPARATOR . 'class-onecom-walkthrough.php';

		$walkthrough = new GroupOne_Walkthrough();

	}
}

/** s
 * Plugin activation hook
 **/
if ( ! function_exists( 'groupone_plugin_activation' ) ) {
	function groupone_plugin_activation() {

		// Call to the features endpoint for restoring transient value.
		groupone_set_premi_flag( true );

		// Schedule Health Monitor scan after 10 minutes.
		groupone_schedule_single_events();

		/**
		 * Captured before the takeover: deactivating a legacy plugin now
		 * reconciles onecom_disabled_modules through the deactivated_plugin
		 * listener, so the mwp_only seeding further down can no longer read
		 * the option's absence as "this site is fresh".
		 */
		$modules_option_existed = ( false !== get_option( 'onecom_disabled_modules' ) );

		/**
		 * Keep exactly one implementation of each merged feature active
		 * (WPIN-8758): deactivate any legacy standalone plugin the hub
		 * absorbed, before letting the modules below seed their defaults.
		 */
		if ( function_exists( 'groupone_deactivate_legacy_plugins' ) ) {
			groupone_deactivate_legacy_plugins();
		}

		/**
		 * WPIN-8963: groupone_load_merged_modules() already ran once at this
		 * file's parse time, i.e. earlier in this same activation request,
		 * while any just-deactivated legacy plugin above was still active —
		 * so it skipped requiring that module's bootstrap and the module never
		 * got a chance to hook 'onecom_merged_modules_activate' below. Re-run
		 * it now that the legacy plugin(s) are gone, so every module that is
		 * eligible as of this moment has its listener attached before the
		 * action fires. Safe to repeat: modules already loaded are skipped by
		 * require_once, so this only loads modules that were skipped above.
		 */
		if ( function_exists( 'groupone_load_merged_modules' ) ) {
			groupone_load_merged_modules();
		}

		/**
		 * WPIN-9027: For non-mWP sites on fresh activation, seed onecom_disabled_modules
		 * with mwp_only modules (e.g. spam-protection) so the persisted database state
		 * explicitly disables them by default.
		 */
		if ( ! $modules_option_existed ) {
			if ( ! function_exists( 'ismWP' ) && file_exists( GROUPONE_WP_PATH . 'inc/functions.php' ) ) {
				require_once GROUPONE_WP_PATH . 'inc/functions.php';
			}
			groupone_seed_mwp_only_defaults();
		}

		/**
		 * Lets merged feature modules (WPIN-8736) seed their defaults.
		 * Listeners must be idempotent and never overwrite options that
		 * already exist on the site.
		 */
		do_action( 'onecom_merged_modules_activate' );

		/**
		 * Stamp the version a fresh install starts life on, so the
		 * version-stamp check on admin_init doesn't mistake activation for an
		 * upgrade and blanket-disable the merged modules on a brand new site.
		 */
		update_option( 'oc_tp_version', GROUPONE_WP_VERSION, 'no' );
	}
}
register_activation_hook( __FILE__, 'groupone_plugin_activation' );

/**
 * Plugin deactivation hook (WPIN-8737 rollback safety): deactivating the hub
 * itself must not leave the site stuck with stale Performance Cache headers.
 * Reuses GroupOne_VCaching::purge_theme_cache() -- the same public method WP
 * core's own 'switch_theme' action already triggers today for the same
 * "site config changed, cached output may now be stale" reasoning -- rather
 * than reaching into the module's private purgeAll() internals. A fresh
 * instance is used because the module's own $oc_vcaching variable
 * (performance-cache.php) is file-scoped, not a global; instantiating a
 * second instance purely to purge is the same pattern the WP-CLI purge
 * command already uses (class-wp-cli-vcaching-purge-command.php).
 *
 * Maintenance Mode needs no equivalent action here: its front-end override
 * only runs from the module's own bootstrap, which stops loading the moment
 * the hub deactivates, so there is no "stuck maintenance page" state to
 * clean up.
 */
if ( ! function_exists( 'groupone_plugin_deactivation' ) ) {
	function groupone_plugin_deactivation() {
		if ( class_exists( 'GroupOne_VCaching' ) ) {
			( new GroupOne_VCaching() )->purge_theme_cache();
		}
	}
}
register_deactivation_hook( __FILE__, 'groupone_plugin_deactivation' );

/**
 * Body of the upgrade routine, shared by both entry points below.
 *
 * Idempotent by design: every step it runs guards itself (the
 * `onecom_disabled_modules` seeding, the version stamp write, and the
 * scan scheduling all tolerate being re-run without side effects), so
 * calling it more than once for the same upgrade is harmless.
 */
if ( ! function_exists( 'groupone_run_upgrade_routine' ) ) {
	function groupone_run_upgrade_routine() {
		/**
		 * WPIN-8736: an existing customer updating to a version that ships
		 * the merged modules for the first time must not silently get
		 * Performance Cache / Spam Protection / Maintenance Mode turned on --
		 * that's a live behavior change (caching, CDN rewriting, etc.) with
		 * no explicit action from the customer. New installs are unaffected
		 * (this runs on upgrade, never on first activation) and default to
		 * every module enabled, matching WPIN-8736's intent for new
		 * customers. Guarded so a customer who later re-enables a module via
		 * the Features screen isn't overridden again on the next update.
		 */
		if ( false === get_option( 'onecom_disabled_modules' ) ) {
			update_option( 'onecom_disabled_modules', array_keys( groupone_merged_modules_map() ) );
		}

		/**
		 * Only the activation hook used to run the takeover, so a site that
		 * already had the hub active and merely updated kept its legacy
		 * standalone plugins running alongside the merged modules. Deliberately
		 * after the default above: the takeover enables each module it takes
		 * over, which would otherwise be undone by that blanket disable and
		 * leave the feature off on both sides.
		 */
		if ( function_exists( 'groupone_takeover_legacy_plugins_on_update' ) ) {
			groupone_takeover_legacy_plugins_on_update();
		}

		update_option( 'oc_tp_version', GROUPONE_WP_VERSION, 'no' );

		//remove old checks from previous scan
		groupone_unset_removed_checks();

		// Schedule Health Monitor scan after 10 minutes.
		groupone_schedule_single_events();
	}
}

/**
 * Plugin upgradation hook
 *
 * Kept for sites whose next update runs a build that already has this hook -
 * it's a no-op in the exact case this ticket is about (upgrading from a build
 * that predates it, so the old in-memory code runs instead), which is why the
 * admin_init version-stamp check below is the routine's real trigger.
 */
if ( ! function_exists( 'groupone_plugin_upgradation' ) ) {
	function groupone_plugin_upgradation( $upgrader_object, $options ) {
		$our_plugin = plugin_basename( __FILE__ );

		// Confirm this upgrade/install action affected our plugin specifically.
		if ( 'plugin' !== ( $options['type'] ?? '' ) ) {
			return;
		}
		$affected = $options['plugins'] ?? ( isset( $options['plugin'] ) ? array( $options['plugin'] ) : array() );
		if ( ! in_array( $our_plugin, $affected, true ) ) {
			return;
		}

		groupone_run_upgrade_routine();
	}
}
add_action( 'upgrader_process_complete', 'groupone_plugin_upgradation', 10, 2 );

if ( ! function_exists( 'groupone_seed_mwp_only_defaults' ) ) {
	/**
	 * Seeds the mwp_only modules into `onecom_disabled_modules` on a fresh
	 * install, so a non-mWP site explicitly records them as off (WPIN-9027).
	 *
	 * The entitlement lookup can come back empty on a brand new webspace -
	 * observed on a genuinely mWP install, where it returned nothing during
	 * activation and the full feature list 26 seconds later. Persisting that
	 * as "not mWP" used to be permanent, because this only ever ran once. So
	 * an inconclusive answer now seeds nothing and leaves a marker for
	 * groupone_retry_mwp_only_defaults() to settle on a later request.
	 *
	 * @return bool True once the answer was conclusive and the seeding is done.
	 */
	function groupone_seed_mwp_only_defaults(): bool {
		$state = function_exists( 'groupone_mwp_state' ) ? groupone_mwp_state() : null;

		if ( null === $state ) {
			update_option( 'onecom_mwp_defaults_pending', 1, false );
			return false;
		}

		$default_disabled = array();
		if ( ! $state && function_exists( 'groupone_merged_modules_map' ) ) {
			foreach ( groupone_merged_modules_map() as $slug => $mod ) {
				if ( ! empty( $mod['mwp_only'] ) ) {
					$default_disabled[] = $slug;
				}
			}
		}

		// Merge rather than overwrite: reconciliation may already have written
		// the option in this same request.
		$default_disabled = array_merge( groupone_get_disabled_modules(), $default_disabled );
		update_option( 'onecom_disabled_modules', array_values( array_unique( $default_disabled ) ) );

		delete_option( 'onecom_mwp_defaults_pending' );

		return true;
	}
}

if ( ! function_exists( 'groupone_retry_mwp_only_defaults' ) ) {
	/**
	 * Keeps the mwp_only defaults in step with the entitlement.
	 *
	 * A negative reading is never final. The lookup can answer a populated
	 * feature list that simply does not carry MWP_ADDON yet while a webspace is
	 * still being provisioned, which is indistinguishable from a genuinely
	 * non-mWP site at that moment - observed on a site that reported the full
	 * list, MWP_ADDON included, only afterwards. So this keeps running and
	 * hands the feature over whenever the entitlement does turn up.
	 *
	 * It only ever enables. A site that really is not mWP is already kept from
	 * running the module by the loader's own gate, and an owner who switched
	 * the module off themselves is recorded in onecom_modules_user_choice and
	 * left alone.
	 */
	function groupone_retry_mwp_only_defaults(): void {
		/**
		 * Real admin page loads only. admin_init fires inside admin-ajax.php
		 * too, and the module toggle deliberately re-checks the entitlement
		 * live there (WPIN-8899) precisely because the cached answer can be a
		 * stale "yes". Reconciling from that cache mid-request would undo the
		 * toggle's own decision.
		 */
		if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		/**
		 * inc/functions.php is loaded lazily, by an admin_init callback
		 * registered further down this file - later than this one, so at the
		 * same priority it has not run yet. Without this the state helper
		 * would be undefined here and this would quietly never fire.
		 */
		if ( ! function_exists( 'groupone_mwp_state' ) && file_exists( GROUPONE_WP_PATH . 'inc/functions.php' ) ) {
			require_once GROUPONE_WP_PATH . 'inc/functions.php';
		}
		if ( ! function_exists( 'groupone_mwp_state' ) ) {
			return;
		}

		$state = groupone_mwp_state();

		// Nothing to act on yet; the next request tries again.
		if ( null === $state ) {
			return;
		}

		// A conclusive reading is enough to write the initial defaults.
		if ( get_option( 'onecom_mwp_defaults_pending' ) ) {
			groupone_seed_mwp_only_defaults();
		}

		if ( true !== $state ) {
			return;
		}

		if ( function_exists( 'groupone_merged_modules_map' ) && function_exists( 'groupone_set_module_enabled' ) ) {
			$disabled = groupone_get_disabled_modules();
			foreach ( groupone_merged_modules_map() as $slug => $module ) {
				if ( empty( $module['mwp_only'] ) || ! in_array( $slug, $disabled, true ) ) {
					continue;
				}
				if ( function_exists( 'groupone_module_user_choice_recorded' ) && groupone_module_user_choice_recorded( $slug ) ) {
					continue;
				}
				groupone_set_module_enabled( $slug, true );
			}
		}

		if ( ! class_exists( 'GroupOne_VMSettings' ) ) {
			$vm_settings_file = GROUPONE_WP_PATH . 'modules/vulnerability-monitor/classes/class-ocvm-settings.php';
			if ( file_exists( $vm_settings_file ) ) {
				require_once $vm_settings_file;
			}
		}
		if ( class_exists( 'GroupOne_VMSettings' ) ) {
			$vm_settings = new GroupOne_VMSettings();
			$vm_settings->apply_mwp_defaults();
		}
	}
}
add_action( 'admin_init', 'groupone_retry_mwp_only_defaults' );

/**
 * Version-stamp fallback: `oc_tp_version` is written by the upgrade routine
 * but was never read anywhere, so a customer upgrading from a build that
 * predates the routine never had it seeded (upgrader_process_complete ran
 * the old code that request, not this one). Comparing the stamp on a normal
 * admin request lets the new code catch up regardless of which build ran
 * the upgrader hook.
 *
 * The stamp is per-site, deliberately: it gates `onecom_disabled_modules`,
 * which is itself per-site. A network-wide stamp would let the first subsite
 * an admin visits mark the whole network done and leave every other subsite
 * unseeded.
 */
if ( ! function_exists( 'groupone_maybe_run_upgrade_routine' ) ) {
	function groupone_maybe_run_upgrade_routine() {
		/**
		 * Only on a real admin page load. The routine deactivates legacy
		 * plugins, so letting it fire inside an AJAX, REST or cron request
		 * would mutate the active plugin set underneath a handler that has
		 * already booted against the old one.
		 */
		if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		/**
		 * Deactivating plugins on behalf of whoever happens to load an admin
		 * page is too much: wait for someone who could have done it by hand.
		 *
		 * Deliberately manage_options and not activate_plugins. On multisite
		 * map_meta_cap() adds manage_network_plugins to activate_plugins unless
		 * the network enabled the Plugins menu (wp-includes/capabilities.php),
		 * so a site administrator visiting their own subsite would not qualify
		 * and that subsite would never get seeded.
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( get_option( 'oc_tp_version' ) !== GROUPONE_WP_VERSION ) {
			groupone_run_upgrade_routine();
		}
	}
}
add_action( 'admin_init', 'groupone_maybe_run_upgrade_routine' );

/**
 * A subsite created after the plugin is already network-active is a new site,
 * not an upgrade, so stamp it at creation. Without this the check above would
 * treat its missing stamp as an upgrade and disable every merged module on a
 * brand new subsite.
 */
if ( ! function_exists( 'groupone_stamp_new_site' ) ) {
	function groupone_stamp_new_site( $new_site ) {
		if ( ! is_object( $new_site ) || ! isset( $new_site->blog_id ) ) {
			return;
		}
		switch_to_blog( (int) $new_site->blog_id );
		update_option( 'oc_tp_version', GROUPONE_WP_VERSION, 'no' );
		restore_current_blog();
	}
}
add_action( 'wp_initialize_site', 'groupone_stamp_new_site', 100 );
add_action( 'admin_init', 'groupone_check_for_get_request', - 1 );
if ( ! function_exists( 'groupone_check_for_get_request' ) ) {
	function groupone_check_for_get_request() {
		/**
		 * Deactivate plugin
		 **/
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'deactivate_plugin' ) {
			if ( isset( $_POST['plugin'] ) && trim( $_POST['plugin'] ) != '' ) {
				$network_wide = false;
				$silent       = false;
				if ( is_multisite() && is_network_admin() ) {
					$network_wide = true;
				}
				$is = deactivate_plugins( $_POST['plugin'], $silent, $network_wide );
				wp_safe_redirect( wp_get_referer() );
			}
		}

		/**
		 * Delete site transient
		 **/
		if ( isset( $_GET['request'] ) && $_GET['request'] != '' ) {
			if ( $_GET['request'] == 'recommended_plugins' ||
				$_GET['request'] == 'discouraged_plugins' ||
				$_GET['request'] == 'plugins' ) {
				delete_site_transient( 'onecom_' . $_GET['request'] );
				$url = ( is_network_admin() && is_multisite() ) ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );
				$url = add_query_arg(
					array(
						'page' => 'onecom-wp-themes',
					),
					$url
				);
				wp_safe_redirect( $url );
				die();
			}
		}

		return;
	}
}

add_action( 'init', 'groupone_wp_load_textdomain', - 1 );
if ( ! function_exists( 'groupone_wp_load_textdomain' ) ) {
	function groupone_wp_load_textdomain() {
// moved here to prevent warnings
		if ( ! defined( 'GROUPONE_INLINE_LOGO' ) ) {
			define( 'GROUPONE_INLINE_LOGO', sprintf( '<img src="%s" alt="%s" />', GROUPONE_WP_URL . 'assets/images/one.com.black.svg', __( 'One.com', 'onecom-wp' ) ) );
		}

		$current_locale           = get_locale();
		$locales_with_translation = array(
			'da_DK',
			'de_DE',
			'es_ES',
			'fr_FR',
			'it_IT',
			'pt_PT',
			'nl_NL',
			'sv_SE',
		);

		// Locales fallback and load english translations [as] if selected unsupported language in WP-Admin.
		if ( $current_locale === 'fi' ) {
			load_textdomain( GROUPONE_PLUGIN_DOMAIN, __DIR__ . '/languages/onecom-wp-fi_FI.mo' );
		} elseif ( $current_locale === 'nb_NO' ) {
			load_textdomain( GROUPONE_PLUGIN_DOMAIN, __DIR__ . '/languages/onecom-wp-no_NO.mo' );
		} elseif ( in_array( get_locale(), $locales_with_translation ) ) {
			load_plugin_textdomain( GROUPONE_PLUGIN_DOMAIN, false, basename( __DIR__ ) . '/languages' );
		} else {
			load_textdomain( GROUPONE_PLUGIN_DOMAIN, __DIR__ . '/languages/onecom-wp-en_GB.mo' );
		}
	}
}

/**
 * Limit load of resources on only specific admin pages to optimize loading time
 */
/* Add hook to following array where you want to enquque your resources */
global $load_onecom_wp_resources_slugs;
$load_onecom_wp_resources_slugs = array(
	'one-com_page_onecom-wp-themes',
	'toplevel_page_onecom-wp',
	'one-com_page_onecom-wp-themes', //Remove plugin entry
	'_page_onecom-wp-recommended-plugins',
	'admin_page_onecom-wp-recommended-plugins',
	'admin_page_onecom-wp-discouraged-plugins',
	'one-com_page_onecom-wp-staging',
	'one-com_page_onecom-wp-staging-blocked',
	'one-com_page_onecom-wp-cookie-banner',
	'one-com_page_onecom-wp-error-page',
	'one-com_page_onecom-home',
	'index.php',
);

$load_onecom_wp_resources_slugs = apply_filters( 'load_onecom_wp_resources_slugs', $load_onecom_wp_resources_slugs );

add_action( 'limit_enqueue_resources', 'limit_enqueue_resources_callback', 10, 3 );
if ( ! function_exists( 'limit_enqueue_resources_callback' ) ) {
	function limit_enqueue_resources_callback( $handle, $hook, $type ) {
		global $load_onecom_wp_resources_slugs;
		$alreadyEnqueued = false;
		if ( in_array( $hook, $load_onecom_wp_resources_slugs ) ) { // Checking hook with provided array to be allowed.
			if ( $type == 'style' ) {
				$alreadyEnqueued = true;
				wp_enqueue_style( $handle ); // If allowed, enqueue the style.
			} elseif ( $type == 'script' ) {
				wp_enqueue_script( $handle ); // If allowed, enqueue the script.
			}
		}

		//List of pages where duplicate one.min.css should not be enqueued, and must be enqueued only once from specific plugins
		$pageShouldNotHave = array(
			'toplevel_page_onecom-vcache-plugin',
			'one-com_page_onecom-vcache-plugin',
			'one-com_page_onecom-cdn',
			'one-com_page_onecom-marketplace',
			'one-com_page_onecom-marketplace-products',
			'toplevel_page_onecom-wp-under-construction',
			'one-com_page_onecom-wp-under-construction',
		);

		//Enqueue one.min.css on other pages and not on $pageShouldNotHave from one.com plugin
		if(!$alreadyEnqueued && $handle === 'onecom-gravity-styles' && !in_array($hook, $pageShouldNotHave)){
			wp_enqueue_style( $handle );
		}
	}
}

add_action( 'wp_head', 'add_onecom_branding_css' );
add_action( 'admin_head', 'add_onecom_branding_css' );
if ( ! function_exists( 'add_onecom_branding_css' ) ) {
	function add_onecom_branding_css() {
		echo "<style>[class*=\" icon-oc-\"],[class^=icon-oc-]{speak:none;font-style:normal;font-weight:400;font-variant:normal;text-transform:none;line-height:1;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.icon-oc-one-com-white-32px-fill:before{content:\"\e901\"}.icon-oc-one-com:before{content:\"\e900\"}#one-com-icon,.toplevel_page_onecom-wp .wp-menu-image{speak:none;display:flex;align-items:center;justify-content:center;text-transform:none;line-height:1;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.onecom-wp-admin-bar-item>a,.toplevel_page_onecom-wp>.wp-menu-name{font-size:16px;font-weight:400;line-height:1}.toplevel_page_onecom-wp>.wp-menu-name img{width:69px;height:9px;}.wp-submenu-wrap.wp-submenu>.wp-submenu-head>img{width:88px;height:auto}.onecom-wp-admin-bar-item>a img{height:7px!important}.onecom-wp-admin-bar-item>a img,.toplevel_page_onecom-wp>.wp-menu-name img{opacity:.8}.onecom-wp-admin-bar-item.hover>a img,.toplevel_page_onecom-wp.wp-has-current-submenu>.wp-menu-name img,li.opensub>a.toplevel_page_onecom-wp>.wp-menu-name img{opacity:1}#one-com-icon:before,.onecom-wp-admin-bar-item>a:before,.toplevel_page_onecom-wp>.wp-menu-image:before{content:'';position:static!important;background-color:rgba(240,245,250,.4);border-radius:102px;width:18px;height:18px;padding:0!important}.onecom-wp-admin-bar-item>a:before{width:14px;height:14px}.onecom-wp-admin-bar-item.hover>a:before,.toplevel_page_onecom-wp.opensub>a>.wp-menu-image:before,.toplevel_page_onecom-wp.wp-has-current-submenu>.wp-menu-image:before{background-color:#76b82a}.onecom-wp-admin-bar-item>a{display:inline-flex!important;align-items:center;justify-content:center}#one-com-logo-wrapper{font-size:4em}#one-com-icon{vertical-align:middle}.imagify-welcome{display:none !important;}
		</style>";
	}
}

add_action( 'admin_enqueue_scripts', 'register_one_core_resources' );
if ( ! function_exists( 'register_one_core_resources' ) ) {
	function register_one_core_resources( $hook ) {

		$resource_extension = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true' ) ? '' : '.min'; // Adding .min extension if SCRIPT_DEBUG is enabled.
		$resource_min_dir   = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true' ) ? '' : 'min-'; // Adding min- as a minified directory of resources if SCRIPT_DEBUG is enabled.

		wp_register_style(
			GROUPONE_PLUGIN_DOMAIN,
			GROUPONE_WP_URL . 'assets/' . $resource_min_dir . 'css/style' . $resource_extension . '.css',
			null,
			GROUPONE_WP_VERSION,
			'all'
		);

		wp_register_script(
			GROUPONE_PLUGIN_DOMAIN,
			GROUPONE_WP_URL . 'assets/' . $resource_min_dir . 'js/script' . $resource_extension . '.js',
			array( 'jquery', 'thickbox', 'jquery-ui-dialog' ),
			GROUPONE_WP_VERSION,
			true
		);
		wp_localize_script(
			GROUPONE_PLUGIN_DOMAIN,
			'onecom_vars',
			array(
				'network' => ( is_network_admin() && is_multisite() ) ? true : false,
				// 'nonce' is a fixed string, identical on every site and every
				// user, and nothing of ours verifies it. It is left alone
				// because the third-party demo-import calls in script.js pass
				// it through as their own 'security' field; untangling that is
				// separate work. The privileged handlers check 'ajaxNonce'.
				'nonce'    => base64_encode('onboarding-revamp'),
				'ajaxNonce' => wp_create_nonce( GROUPONE_AJAX_NONCE_ACTION ),
				'customizeURL' => admin_url('customize.php'),
				'activationMsg' => __('Theme %s% successfully activated.',GROUPONE_PLUGIN_DOMAIN),
				'installMsg' => __('Theme %s% successfully installed.',GROUPONE_PLUGIN_DOMAIN),
				'activateProgress'=> __('Activating %s% theme',GROUPONE_PLUGIN_DOMAIN),
				'installProgress' => __('Installing %s% theme',GROUPONE_PLUGIN_DOMAIN),
				'themeActivationErr'=> __('Couldn’t activate theme %s%.',GROUPONE_PLUGIN_DOMAIN),
				'democontentErr'=> __('Couldn’t import demo content.',GROUPONE_PLUGIN_DOMAIN),
				'installTheme'=> __('Install theme',GROUPONE_PLUGIN_DOMAIN),
			)
		);

		wp_register_style(
			'onecom-promo',
			GROUPONE_WP_URL . 'assets/' . $resource_min_dir . 'css/promo' . $resource_extension . '.css',
			null,
			GROUPONE_WP_VERSION,
			'all'
		);

		wp_register_script(
			'onecom-promo',
			GROUPONE_WP_URL . 'assets/' . $resource_min_dir . 'js/promo' . $resource_extension . '.js',
			array( 'jquery' ),
			GROUPONE_WP_VERSION
		);
		wp_localize_script(
			'onecom-promo',
			'onecom_promo_vars',
			array( 'ajaxNonce' => wp_create_nonce( GROUPONE_AJAX_NONCE_ACTION ) )
		);

		/**
		 * Hooking resource into limit utilization
		 **/
		do_action( 'limit_enqueue_resources', 'onecom-wp', $hook, 'style' );
		do_action( 'limit_enqueue_resources', 'onecom-wp', $hook, 'script' );

		/* Google fonts */
		wp_register_style(
			'onecom-wp-fonts',
			GROUPONE_WP_URL . 'assets/css/onecom-fonts.css',
			null,
			null,
			'all'
		);
		do_action( 'limit_enqueue_resources', 'onecom-wp-fonts', $hook, 'style' );

		/* ---Gravity CSS--- */
		wp_register_style(
			'onecom-gravity-styles',
			GROUPONE_WP_URL . 'assets/min-css/one.min.css',
			null,
			GROUPONE_WP_VERSION,
			'all'
		);

		do_action( 'limit_enqueue_resources', 'onecom-gravity-styles', $hook, 'style' );

		$allow = array( 'one-com_page_onecom-wp-health-monitor', 'toplevel_page_onecom-wp-spam-protection', 'one-com_page_onecom-wp-spam-protection' );
		if ( function_exists( 'get_current_screen' ) && in_array($hook, $allow)) {
			wp_enqueue_style('onecom-gravity-styles');
		}

	}
}



add_action( 'admin_menu', 'one_core_admin', - 1 );
add_action( 'network_admin_menu', 'one_core_admin', - 1 );
if ( ! function_exists( 'one_core_admin' ) ) {
	function one_core_admin() {
		if ( ! is_network_admin() && is_multisite() ) {
			return false;
		}
		$position = groupone_get_free_menu_position( '2.1' );

		// Save for other one.com plugins and themes.
		global $onecom_generic_menu_position;
		$onecom_generic_menu_position = $position;

		add_menu_page(
			__( 'One.com', 'onecom-wp' ),
			'<span class="onecom-top-menu-item">one.com</span>',
			'manage_options',
			GROUPONE_PLUGIN_DOMAIN,
			'one_core_admin_callback',
			'dashicons-admin-generic',
			$position
		);

		add_submenu_page(
			GROUPONE_PLUGIN_DOMAIN,
			__( 'Themes', 'onecom-wp' ),
			'<span id="onecom_themes">' . __( 'Themes', 'onecom-wp' ) . '</span>',
			'manage_options',
			'onecom-wp-themes',
			'one_core_theme_listing_callback'
		);

		remove_submenu_page( GROUPONE_PLUGIN_DOMAIN, 'onecom-wp' ); // Remove admin duplicate menu item.
		remove_submenu_page( GROUPONE_PLUGIN_DOMAIN, 'onecom-wp-themes' );
	}
}

/**
 * Whether the current request should render the one.com toolbar items.
 *
 * The dropdown is filled by more than one callback, so the visibility rules
 * live in a single place to keep them from drifting apart.
 *
 * @return bool
 */
if ( ! function_exists( 'one_bar_items_visible' ) ) {
	function one_bar_items_visible() {
		if ( ! is_network_admin() && is_multisite() ) {
			return false;
		}

		return current_user_can( 'manage_options' );
	}
}

add_action( 'admin_bar_menu', 'add_one_bar_items', 100, 100 );
if ( ! function_exists( 'add_one_bar_items' ) ) {
	function add_one_bar_items( $admin_bar ) {
		if ( ! one_bar_items_visible() ) {
			return false;
		}
		$args = array(
			'id'    => GROUPONE_PLUGIN_DOMAIN,
			//'parent' => 'top-secondary',
			'title' => GROUPONE_INLINE_LOGO,
			// Network admin keeps onecom-wp: onecom-home is registered on
			// admin_menu only, so it does not exist there.
			'href'  => ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp' ) : admin_url( 'admin.php?page=onecom-home' ),
			'meta'  => array(
				'title' => __( 'One.com', 'onecom-wp' ),
				'class' => 'onecom-wp-admin-bar-item',
			),
		);
		$admin_bar->add_menu( $args );

		// Toolbar one.com Marketplace menu link. Labelled to match the
		// Marketplace entry in the left-hand admin sidebar (WPIN-9015).
		$args = array(
			'id'     => 'onecom-marketplace',
			'parent' => GROUPONE_PLUGIN_DOMAIN,
			'title'  => __( 'Marketplace', 'onecom-wp' ),
			'href'   => ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page='.MARKETPLACE_PAGE_SLUG ) : admin_url( 'admin.php?page='.MARKETPLACE_PAGE_SLUG ),
			'meta'   => array(
				'title' => __( 'Marketplace', 'onecom-wp' ),
			),
		);
		$admin_bar->add_menu( $args );

		$args = array(
			'id'     => 'onecom-wp-staging',
			'parent' => GROUPONE_PLUGIN_DOMAIN,
			'title'  => __( 'Staging', 'onecom-wp' ),
			'href'   => ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp-staging' ) : admin_url( 'admin.php?page=onecom-wp-staging' ),
			'meta'   => array(
				'title' => __( 'Staging', 'onecom-wp' ),
			),
		);
		$admin_bar->add_menu( $args );
	}
}

/*
 * Control Panel and Guides & FAQ close the one.com dropdown.
 *
 * WP_Admin_Bar::_bind() appends children in insertion order, and the merged
 * modules hang their own items off this menu on 'admin_bar_menu' at priority
 * 101 (Maintenance Mode, Clear Performance Cache - see
 * docs/merged-modules.md). Registering these two links from add_one_bar_items()
 * at priority 100 therefore left them stranded mid-list. Running late instead
 * keeps them at the bottom no matter which modules are active (WPIN-9015).
 */
add_action( 'admin_bar_menu', 'add_one_bar_footer_items', 999 );
if ( ! function_exists( 'add_one_bar_footer_items' ) ) {
	function add_one_bar_footer_items( $admin_bar ) {
		if ( ! ( $admin_bar instanceof WP_Admin_Bar ) ) {
			return false;
		}
		if ( ! one_bar_items_visible() ) {
			return false;
		}
		// Nothing to attach to if the parent node was never registered.
		if ( ! $admin_bar->get_node( GROUPONE_PLUGIN_DOMAIN ) ) {
			return false;
		}

		/*
		* Account link to Control Panel
		*/
		$args = array(
			'id'     => 'one-cp',
			'parent' => GROUPONE_PLUGIN_DOMAIN,
			'title'  => __( 'One.com Control Panel', 'onecom-wp' ),
			'href'   => GROUPONE_CP_LOGIN_URL,
			'meta'   => array(
				'title'  => __( 'Go to Control Panel at One.com', 'onecom-wp' ),
				'target' => '_blank',
			),
		);
		$admin_bar->add_menu( $args );

		/*
		* WordPress support
		*/
		$locale = get_locale();

		$args = array(
			'id'     => 'one-wp-support',
			'parent' => GROUPONE_PLUGIN_DOMAIN,
			'title'  => __( 'One.com Guides & FAQ', 'onecom-wp' ),
			'href'   => groupone_generic_locale_link( $request = 'main_guide', $locale ),
			'meta'   => array(
				'title'  => __( 'Go to Guides & FAQ at One.com', 'onecom-wp' ),
				'target' => '_blank',
			),
		);
		$admin_bar->add_menu( $args );
	}
}

if ( ! function_exists( 'one_core_admin_callback' ) ) {
	function one_core_admin_callback() {
		$network = ( is_network_admin() && is_multisite() ) ? 'network/' : '';
		include_once 'templates/' . $network . 'theme-listing.php';
	}
}

if ( ! function_exists( 'one_core_theme_listing_callback' ) ) {
	function one_core_theme_listing_callback() {
		$network = ( is_network_admin() && is_multisite() ) ? 'network/' : '';
		include_once 'templates/' . $network . 'theme-listing.php';
	}
}

//Removed plugin listing


/*
 * groupone_get_free_menu_position() used to be declared here. It is not any more.
 *
 * It lives in inc/lib/validator.php, which this file require_once's unconditionally
 * near the top - see the require above. That happens before execution ever reaches
 * this point, and conditional declarations are not compile-time hoisted, so the
 * validator's copy always won the race. The copy that used to sit here was dead
 * code in every load order: the legacy sibling plugins declare oc_/onecom_-prefixed
 * symbols, not groupone_ ones, so nothing else can define this name first.
 *
 * That was not obvious while it was here. WPIN-9033 was first "fixed" in this copy
 * and shipped with zero runtime effect; CI caught it only because the stack traces
 * pointed at validator.php. Removing it means the next reader cannot repeat that.
 *
 * WPIN-9036 adds a CI guard against a function being declared in both this file and
 * inc/lib. Do not reintroduce a copy here - change the validator instead.
 */


/**
 * one.com updater
 **/
if ( ! class_exists( 'GroupOne_Updater' ) ) {
	require_once GROUPONE_WP_PATH . '/inc/update.php';
}

// Clean onboarding files if exists
add_action( 'admin_init', 'groupone_admin_init_callback' );
if ( ! function_exists( 'groupone_admin_init_callback' ) ) {
	function groupone_admin_init_callback() {
		require_once GROUPONE_WP_PATH . '/inc/functions.php';
		groupone_cleanup_onboarding_files();
	}
}

/**
 * one.com staging
 **/
if ( ! class_exists( 'OneStaging\\OneStaging' ) && is_admin() ) {
	include_once 'staging/one_staging.php';
	\OneStaging\OneStaging::getInstance()->run();
}


/**
 * oci entry point
 **/
// Get POST or PUT request body to check if it is a POST call or GET call.
$entityBody = file_get_contents( 'php://input' );
// If POST content length is 0, then it is most probably a GET call.
if ( ! strlen( trim( $entityBody ) ) ) {
	// Handle authentication request.
	add_action(
		'init',
		function () {
			if ( ! class_exists( 'GroupOne_LAUTH' ) &&
				isset( $_GET['onecom-auth'] ) &&
				! empty( $_GET['onecom-auth'] ) &&
				file_exists( GROUPONE_WP_PATH . OCL_FILE_PATH ) ) {
				require_once GROUPONE_WP_PATH . OCL_FILE_PATH;

				$oclObj = GroupOne_LAUTH::getInstance();
				$oclObj = null; // Destruct object.
			}
		}
	);
}

/**
 * oci show message on login page
 * THIS FUNCTION IS CAUSING ISSUES ON LOGIN PAGE
 **/
/*add_filter( 'login_message', function ( $message ) {

	if ( ! is_user_logged_in() && ! class_exists( 'GroupOne_LAUTH' ) &&
		isset( $_GET['redirect_to'] ) &&
		file_exists( GROUPONE_WP_PATH . OCL_FILE_PATH ) ) {
		require_once GROUPONE_WP_PATH . OCL_FILE_PATH;

		$oclObj = GroupOne_LAUTH::getInstance();

		$redirectto = $_GET['redirect_to'];
		$query      = parse_url( $redirectto, PHP_URL_QUERY );
		$queries    = array();

		parse_str( $query, $queries );

		if ( isset( $queries[ $oclObj::TOKENKEY ] ) && ! empty( $queries[ $oclObj::TOKENKEY ] ) ) {
			$jwtPassVal = $queries[ $oclObj::TOKENKEY ];
			$oclObj->checkToken( $jwtPassVal );

			if ( ! $oclObj->tokenStatus ) {
				$message = $oclObj->tokenMessage;
				$oclObj  = null;//destruct object

				return '<div id="login_error">  ' . __( $message, 'ocl' ) . '<br>
				</div>';
			}
		}

	}

	return $message;
} );*/

add_action( 'init', function() {

	if ( file_exists( GROUPONE_WP_PATH . '/modules/health-monitor/health-monitor.php' ) ) {
		require_once GROUPONE_WP_PATH . '/modules/health-monitor/health-monitor.php';
	}
	if ( file_exists( GROUPONE_WP_PATH . '/modules/vulnerability-monitor/vulnerability-monitor.php' ) ) {
		require_once GROUPONE_WP_PATH . '/modules/vulnerability-monitor/vulnerability-monitor.php';
	}
});


/**
 * Feature modules merged from the standalone one.com plugins (WPIN-8736).
 * Loaded at parse time — see inc/module-registry.php for the
 * standalone-plugin precedence rules.
 */
require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
groupone_load_merged_modules();

/**
 * Legacy standalone-plugin takeover (WPIN-8758): keep exactly one of
 * {hub module, legacy plugin} active per feature, in both directions.
 */
require_once GROUPONE_WP_PATH . 'inc/legacy-plugin-takeover.php';

if ( file_exists( GROUPONE_WP_PATH . '/modules/advanced-login-protection/advanced-login-protection.php' ) ) {
	require_once GROUPONE_WP_PATH . '/modules/advanced-login-protection/advanced-login-protection.php';
}

if ( file_exists( GROUPONE_WP_PATH . '/modules/cookie-banner/cookie-banner.php' ) ) {
	require_once GROUPONE_WP_PATH . '/modules/cookie-banner/cookie-banner.php';
}

if ( file_exists( GROUPONE_WP_PATH . '/modules/error-page/error-page.php' ) ) {
	require_once GROUPONE_WP_PATH . '/modules/error-page/error-page.php';
}


//marketplace module loading
if ( file_exists( GROUPONE_WP_PATH . '/modules/marketplace/marketplace.php' ) ) {
	require_once GROUPONE_WP_PATH . '/modules/marketplace/marketplace.php';
}

// Site abilities module (posts, menus, plugins, themes over MCP)
if ( file_exists( GROUPONE_WP_PATH . '/modules/abilities/abilities.php' ) ) {
	require_once GROUPONE_WP_PATH . '/modules/abilities/abilities.php';
}

/**
 * Include nested menu
 *
 * Both class names must be checked: inc/lib is bundled in the legacy
 * standalone plugins too, and only one instance may hook admin_menu per
 * request. A pre-rename standalone defines only Onecom_Nested_Menu and loads
 * first, so checking the new name alone misses it and builds a second menu —
 * WordPress keys object callbacks by spl_object_hash, so both fire and
 * add_menu_page() adds 'onecom-wp' twice.
 **/
if ( ! class_exists( 'GroupOne_Nested_Menu' ) && ! class_exists( 'Onecom_Nested_Menu' ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/inc/lib/onecom-nested-menu.php';
	$onecom_menu = new GroupOne_Nested_Menu();
	$onecom_menu->init();
}

/* Add "View Details" link for all One.com plugins, if not already exist */

add_filter( 'plugin_row_meta', 'groupone_generic_plugin_row_meta', 20, 2 );
function groupone_generic_plugin_row_meta( $links, $file ) {

	// Skip all non-one.com plugin entries.
	if ( $file != plugin_basename( __FILE__ ) ) {
		return $links;
	}

	$health_url = ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp-health-monitor' ) : admin_url( 'admin.php?page=onecom-wp-health-monitor' );

	$stg_url = ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp-staging' ) : admin_url( 'admin.php?page=onecom-wp-staging' );

	$themes_url = ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-wp-themes' ) : admin_url( 'admin.php?page=onecom-wp-themes' );

	$plugin_url = ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page=onecom-marketplace' ) : admin_url( 'admin.php?page=onecom-marketplace' );

	// Add new link - "View Details".
	$anchor = '<a href="%s">%s</a>';

	$new_links = array(
		'oc-health'  => sprintf( $anchor, $health_url, __( 'Health Monitor', 'onecom-wp' ) ),
		'oc-staging' => sprintf( $anchor, $stg_url, __( 'Staging', 'onecom-wp' ) ),
		'oc-themes'  => sprintf( $anchor, $themes_url, __( 'Themes' ) ),
		'oc-plugins' => sprintf( $anchor, $plugin_url, __( 'Plugins' ) ),
	);

	// Club the new link with existing links.
	return array_merge( $links, $new_links );
}

/**
 * Switch to GD Image editor as default.
 */
add_filter( 'wp_image_editors', 'groupone_default_to_gd' );
if ( ! function_exists( 'groupone_default_to_gd' ) ) {
	function groupone_default_to_gd( $editors ) {
		$gd            = 'WP_Image_Editor_GD';
		$editors_array = array_diff( $editors, array( $gd ) );
		array_unshift( $editors_array, $gd );

		return $editors_array;
	}
}

register_uninstall_hook( __FILE__, 'groupone_plugin_uninstall' );
function groupone_plugin_uninstall() {
	// required since the file above is loaded on init
	if ( ! class_exists( 'GroupOne_VMHistoryLog' ) ) {
		require_once GROUPONE_WP_PATH . '/modules/vulnerability-monitor/vulnerability-monitor.php';
	}
	// Delete VM log table upon uninstall.
	$vm_log = new GroupOne_VMHistoryLog();
	$vm_log->vm_log_delete();

	// Delete data consent status
	delete_site_option( 'onecom_data_consent_status' );

	// Send stats.
	( class_exists( 'GroupOne_PushStats' ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( 'delete', 'plugin', dirname( plugin_basename( __FILE__ ) ), 'plugins_page' ) : '' );
}

function groupone_schedule_single_events() {
	do_action( 'onecom_hm_scan' );
}

/**
 * @return void
 * function to unset all the removed checks from previous HM scan
 * @todo remove it after next major update i.e 5.0
 */
if ( ! function_exists( 'groupone_unset_removed_checks' ) ) {

	function groupone_unset_removed_checks() {
		$prev_scan      = get_site_transient( 'ocsh_site_previous_scan' );
		$removed_checks = array(
			'DB',
			'vulnerability_exists',
			'login_recaptcha',
			'asset_minification',
			'logout_duration',
			'login_protection',
		);
		if ( $prev_scan && is_array( $prev_scan ) ) {
			foreach ( $prev_scan as $check => $status ) {
				if ( in_array( $check, $removed_checks ) ) {
					unset( $prev_scan[ $check ] );
				}
			}
			set_site_transient( 'ocsh_site_previous_scan', $prev_scan );
		}
	}
}

/**
 * Entrypoint for stg login
 */
if ( isset( $_GET['timelog'] ) && isset( $_GET['stgUrl'] ) ) {
	add_action( 'init', 'staging_login_callback' );
}
if ( isset( $_GET['redirect_to'] ) ) {
	add_filter( 'login_message', 'groupone_wp_login_error_callback' );
}
/**
 * Handling stg token and login
 */
if ( ! function_exists( 'staging_login_callback' ) ) {
	function staging_login_callback() {
		// If already logged in then return.
		if ( is_user_logged_in() ) {
			wp_redirect( admin_url() );
			exit;
		}
		// Check timelog is not empty.
		if ( ! empty( $_GET['timelog'] ) && ! empty( $_GET['stgUrl'] ) ) {

			$timelog  = trim( $_GET['timelog'] );
			$stg_url  = $_GET['stgUrl'];
			$site_url = site_url();

			// Check if both url is valid.
			if ( ! filter_var( $stg_url, FILTER_VALIDATE_URL ) && ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
				return;
			}
			// Check for equal url.
			// Extra slash trimmed from stg_url to prevent failed logins.
			if ( $site_url !== rtrim( $stg_url, '/' ) ) {
				return;
			}

			$decryptedKey = validateStgToken( $timelog );
			global $wpdb;
			if ( $decryptedKey !== false ) {

				$user_id = getStgUserIds( $wpdb->prefix );

				if ( $user_id > 0 && ! is_user_logged_in() ) {
					$user      = get_user_by( 'ID', $user_id );
					$loginuser = $user->data->user_login;

					wp_set_current_user( $user_id, $loginuser );
					wp_set_auth_cookie( $user_id );
					do_action( 'wp_login', $loginuser, $user );

					if ( is_user_logged_in() ) {
						wp_redirect( admin_url() );
						exit;
					}
				}
			}
		}
	}
}

/**
 * Show loggin error message
 */
if ( ! function_exists( 'groupone_wp_login_error_callback' ) ) {
	function groupone_wp_login_error_callback( $message ) {

		if ( ! isset( $_GET['redirect_to'] ) && empty( $_GET['redirect_to'] ) ) {
			return $message;
		}

		$redirectto = $_GET['redirect_to'];
		$query      = parse_url( $redirectto, PHP_URL_QUERY );
		$queries    = array();
		if ( $query !== null ) {
			parse_str( $query, $queries );
		}

		if ( isset( $queries['timelog'] ) && ! empty( $queries['timelog'] ) ) {
			$tokenPassVal = $queries['timelog'];
			$tokenStatus  = validateStgToken( $tokenPassVal );

			if ( ! $tokenStatus ) {
				return '<div id="login_error">Invalid Token!<br>
                </div>';
			}
		}
		return $message;
	}
}


/**
 * @param $timelog
 * @return false|string
 *
 * Validate stg token
 */
if ( ! function_exists( 'validateStgToken' ) ) {
	function validateStgToken( $timelog ) {
		global $wpdb;
		$getLiveDetails = get_site_option( 'onecom_staging_existing_live', true );

		$encrypt_method = 'AES-256-CBC';
		$stgHash        = getStgHash( $wpdb->prefix );
		$secret_key     = $getLiveDetails->prefix . '__' . $wpdb->prefix . $stgHash;
		$secret_iv      = $wpdb->prefix . '__' . $getLiveDetails->prefix;
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		return openssl_decrypt( base64_decode( $timelog ), $encrypt_method, $key, 0, $iv );
	}
}
/**
 * @param $stgPrefix
 * @return int|mixed
 * Get admin ids
 */
if ( ! function_exists( 'getStgUserIds' ) ) {
	function getStgUserIds( $stgPrefix ) {
		global $wpdb;
		$getAdminQuery = 'SELECT u.ID, u.user_login, u.user_email,u.user_nicename FROM ' . $stgPrefix . 'users u, ' . $stgPrefix . 'usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE "' . $stgPrefix . 'capabilities" AND m.meta_value LIKE "%administrator%" order by ID ASC';
		// Get all admin users ids from DB.
		$wpAuthUser = $wpdb->get_results( $getAdminQuery );
		// Return first found user id.
		if ( ! empty( $wpAuthUser ) ) {
			return $wpAuthUser[0]->ID;
		}
		return 0;
	}
}

/**
 * @param $stgPrefix
 * @return int|mixed
 * Get stg user hash
 */
function getStgHash( $stgPrefix ) {
	global $wpdb;
	$getAdminQuery = 'SELECT u.user_pass FROM ' . $stgPrefix . 'users u, ' . $stgPrefix . 'usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE "' . $stgPrefix . 'capabilities" AND m.meta_value LIKE "%administrator%" order by ID ASC';
	// Get admin hash.
	$wpAuthUser = $wpdb->get_results( $getAdminQuery );
	// Return found admin hash.
	if ( ! empty( $wpAuthUser ) ) {
		return $wpAuthUser[0]->user_pass;
	}
	return 0;
}
require_once GROUPONE_WP_PATH . '/modules/home/index.php';


if ( ! class_exists( 'GroupOne_Themes_Loader' ) ) {
	require_once GROUPONE_WP_PATH . '/inc/class-onecom-themes-loader.php';
	// Instantiate the class
	new GroupOne_Themes_Loader();
}

if ( ! class_exists( 'GroupOne_Imagify_Promoter' ) && is_admin() ) {
	require_once GROUPONE_WP_PATH . '/inc/class-onecom-imagify-promoter.php';
	new GroupOne_Imagify_Promoter();
}

add_action( 'init', function () {
	if ( is_admin() ) {
		require_once GROUPONE_WP_PATH . '/inc' . DIRECTORY_SEPARATOR . 'class-onecom-contextual-recommendations.php';
		( new GroupOne_Contextual_Recommendations() )->init();
	}
} );

/**
 * @return void
 * Update a staging path for migrated oneHop domains
 */
add_action('wp_loaded','ohStagingPathFix', 10);
if ( ! function_exists( 'ohStagingPathFix' ) ) {
	function ohStagingPathFix(): void
	{
		try{

			global $wpdb;
			$option_name = 'onecom_staging_existing_staging';
			$cluster_id = $_SERVER['ONECOM_CLUSTER_ID'] ?? '';
			$staging_data = get_site_option($option_name, true);
			$onecom_stg_path_fixed = get_site_option('onecom_stg_path_fixed');

			//Check is staging updated for migrated v3 domain
			if(!empty($onecom_stg_path_fixed)){
				return;
			}

			//Return if anyone is empty
			if (
				empty($cluster_id) ||
				empty($staging_data) ||
				!is_array($staging_data)
			) {
				return;
			}

			$staging_prefix = array_key_first($staging_data);

			//Return if a staging path is missing
			if (
				empty($staging_prefix) ||
				!isset($staging_data[$staging_prefix]['path'])
			) {
				return;
			}


			//Update the path as per oneHoP absolute path
			$staging_data[$staging_prefix]['path'] = ABSPATH . $staging_prefix;

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}options SET option_value = %s WHERE option_name = 'onecom_staging_existing_staging'",
					serialize( $staging_data )
				)
			);
			delete_site_option('staging_broken');
			update_site_option('onecom_stg_path_fixed', time());

		} catch (Exception $e){
			error_log("Staging error during oneHoP db update: " . $e->getMessage());
			( class_exists( 'GroupOne_PushStats' ) ? \GroupOne_PushStats::push_stats_misc_events( 'stgfix', '', 'staging', '', '', ['error'=> $e->getMessage()]): '');
		}
	}
}

add_action('init', 'groupone_boot_marketplace', 10);
if(!function_exists('groupone_boot_marketplace')){
	function groupone_boot_marketplace(): void
	{
		// Make $oc_stats_data global so domain and subdomain can be accessed for API URL query params
		global $oc_stats_data;
		$oc_stats_data = GroupOne_PushStats::stats_base_parametres();

		if ( ! function_exists( 'get_mixpanel_props' ) ) {
			function get_mixpanel_props( $mixpanel_props = [] ): array {
				global $oc_stats_data;
				$oc_validate_domain = groupone_validate_domain();

				//Mixpanel sandbox mode, true for sandbox, false for production
				$is_sandbox = true;
				if (str_contains(MIDDLEWARE_URL, 'wpapi.one.com')) {
					$is_sandbox = false;
				}

				// your base props
				$default_props = [
					"user_id"                => is_array($oc_validate_domain) && isset($oc_validate_domain['uuid']) ? $oc_validate_domain['uuid'] : '',
					"customer_country"       => (is_array($oc_validate_domain) && isset($oc_validate_domain['country'])) ? $oc_validate_domain['country'] : '',
					"domain"                 => isset($oc_stats_data['domain']) ? hash('sha256', $oc_stats_data['domain']) : $_SERVER['HTTP_HOST'],
					"env"                    => str_contains(MIDDLEWARE_URL, "wpapi.one.com") ? "production" : "development",
					"generic_plugin_version" => $oc_stats_data['generic_plugin_version'] ?? null,
					"hosting_package"        => $oc_stats_data['hosting_package'] ?? null,
					"is_mwp"                 => is_array($oc_validate_domain) && isset($oc_validate_domain['data']) && in_array("MWP_ADDON", $oc_validate_domain['data']),
					"is_one_hop"             => function_exists('is_cluster_domain') && is_cluster_domain(),
					"package_features"       => $oc_stats_data['package_features'] ?? null,
					"php_version"            => $oc_stats_data['php_version'] ?? null,
					"subdomain"              => isset($oc_stats_data['subdomain']) ? hash('sha256', $oc_stats_data['subdomain']): '',
					"page_opened_at"		 => current_time('timestamp'),
					"is_sandbox"			 => $is_sandbox,
				];

				if (isset($oc_stats_data['cluster']) && !empty($oc_stats_data['cluster'])) {
					$default_props['cluster'] = $oc_stats_data['cluster'];
					$default_props['fqdn'] = isset($oc_stats_data['fqdn']) ? hash('sha256', $oc_stats_data['fqdn']) : $_SERVER['HTTP_HOST'];
					$default_props['webroot'] = $oc_stats_data['webroot'] ?? null;
				}

				// merge incoming props with default
				return array_merge( $default_props, $mixpanel_props );

			}
		}

		$mixpanel_props = get_mixpanel_props();

		// Composer autoloader registers both your plugin classes and the Mozart-prefixed dependencies
		require_once __DIR__ . '/vendor/autoload.php';
		try {

		//	// Using the Mozart-prefixed class name after Composer autoload is registered
			$base_url = MIDDLEWARE_URL .'/marketplace/products/catalog';
			$api_url = add_query_arg([
				'locale' => get_locale(),
				'php' => GROUPONE_PHP_VERSION,
				'wp' => GROUPONE_WP_CORE_VERSION,
				'domain'=> $oc_stats_data['domain'],
				'subdomain'=> $oc_stats_data['subdomain'],
			], $base_url);

			// Extract distinct_id (UUID) from mixpanel_props
			$distinct_id = isset($mixpanel_props['user_id']) ? $mixpanel_props['user_id'] : '';
			$data_consent_status     = get_site_option( 'onecom_data_consent_status', false );
			\WPMarketplace\Dependencies\Groupone\Marketplace\Marketplace::run([
				'parent_menu_slug' => 'onecom-wp',
				'page_title' => 'Plugin Marketplace',
				'menu_title' => 'Marketplace',
				'menu_slug' => 'onecom-marketplace',
				'addons_menu_slug' => 'onecom-marketplace-products',
				'addons_page_title' => 'Your add-ons',
				'addons_menu_title' => 'Your add-ons',
				'api_url' => $api_url,
				'brand' => 'onecom', // Optional: brand identifier for API filtering
				// Optional: Explicitly set assets path if auto-detection doesn't work
				'assets_path' => __DIR__ . '/inc/Dependencies/WPMarketplace/Groupone/Marketplace/',
				'mixp_props' =>  $mixpanel_props ,
				'mixp_distinct_id' => $distinct_id ,
				'data_consent_status' => $data_consent_status
			]);

			// Branded one.com MCP server (no-op until the Abilities API + mcp-adapter are present).
			require_once __DIR__ . '/inc/class-onecom-mcp.php';
			Onecom_Mcp::init();
		}catch ( \Throwable $e ) {
			error_log("Error loading Marketplace: " . $e->getMessage());
			// Avoid breaking the admin; vendor-test will still show load status.
		}
	}
}

// Remove one.com submenu plugin from everywhere
add_action('admin_menu', 'remove_onecom_plugin_submenu', 99);
if(!function_exists('remove_onecom_plugin_submenu')){
	function remove_onecom_plugin_submenu(): void
	{
		global $submenu;

		foreach ((array) $submenu as $parent_slug => $submenus) {
			foreach ((array) $submenus as $index => $submenu_item) {
				if (isset($submenu_item[2]) && $submenu_item[2] === 'onecom-wp-plugins') {
					unset($submenu[$parent_slug][$index]);
				}
			}
		}
	}
}

// Force redirect old one.com plugins page to marketplace page
add_action('admin_page_access_denied', 'handle_onecom_plugins_redirection', 10, 2); ;
if(!function_exists('handle_onecom_plugins_redirection')){
	function handle_onecom_plugins_redirection(): void
	{
		if (isset($_GET['page']) && $_GET['page'] === 'onecom-wp-plugins' && is_admin()) {
			wp_safe_redirect(admin_url('admin.php?page=onecom-marketplace'));
			exit;
		}
	}
}