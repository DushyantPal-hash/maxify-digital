<?php

defined( 'WPINC' ) or die(); // No Direct Access

/**
 * Performance Cache module bootstrap (WPIN-8743).
 *
 * Ported from the standalone "Performance Cache" plugin (onecom-vcache,
 * vcaching.php + onecom-addons/class-ocvcaching.php, v4.1.30) as part of
 * WPIN-8736 (merging one.com's standalone plugins into this hub as modules).
 *
 * Required by inc/module-registry.php::groupone_load_merged_modules() at plugin
 * parse time, and only when the legacy 'onecom-vcache/vcaching.php' plugin is
 * NOT active - that guard already lives in the registry, so this file does not
 * repeat an is_plugin_active() check of its own.
 *
 * De-duplicated against the hub (intentionally NOT ported here, because the hub
 * already provides every one of these exactly once, before any merged module is
 * loaded):
 *  - inc/lib (validator.php, GroupOne_PushStats.php, GroupOne_Nested_Menu - the shared
 *    menu class was already updated for this module by WPIN-8741)
 *  - ONECOMUPDATER (was onecom-addons/inc/class-onecomupdater.php)
 *  - the MIDDLEWARE_URL / GROUPONE_HTTP_HOST / GROUPONE_CP_LOGIN_URL constant definitions
 *    (onecom-themes-plugins.php defines all three before it loads any module)
 */

require_once __DIR__ . '/classes/class-vcachingoc.php';
require_once __DIR__ . '/classes/class-onecom-logger.php';
require_once __DIR__ . '/classes/class-onecomexcludecache.php';
require_once __DIR__ . '/classes/class-onecom-vcache-shortcuts.php';
require_once __DIR__ . '/classes/class-ocvcaching.php';

/**
 * Base caching engine: loads the 'vcaching' textdomain, wires the wp/shutdown
 * output-buffering pair and the send_headers hook (both load-bearing at
 * priority 1000000 - see class-vcachingoc.php), purge-on-save-post hooks, and
 * the admin-bar purge UI.
 */
$vcaching = new GroupOne_VCaching_Base();

/**
 * one.com feature layer: settings/CDN admin pages, purge AJAX, CDN rewriting,
 * .htaccess CORS header management, and the activation-defaults wiring (see
 * GroupOne_VCaching::onecom_activate_defaults() in class-ocvcaching.php).
 */
$oc_vcaching = new GroupOne_VCaching();

// WP-CLI: `wp ocvcaching purge` / `wp ocvcaching cdnpurge`.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/classes/class-wp-cli-vcaching-purge-command.php';
}

/**
 * Ported from vcaching.php's uninstall function (WPIN-8736: renamed here to
 * groupone_vcache_plugin_uninstall() so this merged-module
 * copy can never collide with the standalone onecom-vcache plugin's own
 * identically-named global function, if that legacy plugin is also active).
 * The legacy function was previously wired via register_uninstall_hook()
 * against that standalone plugin's own main file.
 * Uninstall hooks are keyed to a standalone plugin's own main file and never
 * fire for a bootstrap merely required by the hub, so register_uninstall_hook()
 * is intentionally NOT called here. The function itself is kept (function-
 * exists guarded) in case a future ticket wants the hub's own uninstall routine
 * to invoke it explicitly for cleanup.
 */
if ( ! function_exists( 'groupone_vcache_plugin_uninstall' ) ) {
	function groupone_vcache_plugin_uninstall() {
		( class_exists( 'GroupOne_PushStats' ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( 'delete', 'plugin', 'onecom-vcache', 'plugins_page' ) : '' );
	}
}
