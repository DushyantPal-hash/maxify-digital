<?php
/**
 * one.com Spam Protection module.
 *
 * Ported from the standalone "one.com Spam Protection" plugin
 * (onecom-spam-protection/onecom-spam-protection.php, v1.0.48) as part of
 * WPIN-8736 / WPIN-8744. Loaded by inc/module-registry.php via
 * groupone_load_merged_modules(), which already guards against the legacy
 * standalone plugin being active at the same time — no is_plugin_active()
 * guard is needed here.
 *
 * Shared infrastructure (inc/lib validator, GroupOne_PushStats, ONECOMUPDATER,
 * GroupOne_Nested_Menu, MIDDLEWARE_URL) is provided once by the hub itself and
 * is intentionally NOT re-included here.
 */

defined( 'WPINC' ) or die(); // No Direct Access.

if ( ! defined( 'GROUPONE_SP_VERSION' ) ) {
	define( 'GROUPONE_SP_VERSION', '1.0.48' );
}

/**
 * Renamed from the original ONECOM_PLUGIN_PATH: that name is generic enough
 * to collide with other constants in the hub's process-wide namespace, so
 * the ported code uses this module-scoped name throughout instead.
 */
if ( ! defined( 'GROUPONE_SP_PATH' ) ) {
	define( 'GROUPONE_SP_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'GROUPONE_SP_WP_URL' ) ) {
	define( 'GROUPONE_SP_WP_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Legacy stats-identity slug. Kept as the literal folder name of the former
 * standalone plugin (rather than re-derived from this file's new location)
 * so that GroupOne_PushStats::push_stats_performance_cache() calls keep reporting
 * under the same identifier they always have. See port summary: this is a
 * policy call that may need confirmation from whoever owns the analytics
 * data — flagged, not guessed silently.
 */
if ( ! defined( 'GROUPONE_SP_PLUGIN_SLUG' ) ) {
	define( 'GROUPONE_SP_PLUGIN_SLUG', 'onecom-spam-protection' );
}

/**
 * Relative path (from WP_PLUGIN_DIR) to this module's languages directory,
 * used for load_plugin_textdomain() instead of GROUPONE_SP_PLUGIN_SLUG so that
 * translations resolve correctly regardless of what the legacy slug above is
 * set to, or what the hub plugin's own folder is named.
 */
if ( ! defined( 'GROUPONE_SP_LANGUAGES_RELPATH' ) ) {
	define( 'GROUPONE_SP_LANGUAGES_RELPATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

if ( ! defined( 'GROUPONE_SP_TEXTDOMAIN' ) ) {
	define( 'GROUPONE_SP_TEXTDOMAIN', 'onecom-sp' );
}

// MIDDLEWARE_URL is already defined once by the hub (onecom-themes-plugins.php) — not redefined here.

require_once GROUPONE_SP_PATH . 'inc' . DIRECTORY_SEPARATOR . 'functions.php';

/**
 * Autoloading: the original main file registered a generic
 * spl_autoload_register() that mapped ANY CamelCase class name to
 * inc/classes/class-*.php. Running inside the hub's single PHP process, a
 * loader like that would be asked to resolve class names it has no business
 * handling. Rather than scope a loader down to a class-name prefix, this
 * port replaces it entirely with explicit requires below — simpler, and
 * consistent with how the hub's own bootstrap files avoid generic
 * autoloading (see e.g. modules/marketplace/marketplace.php).
 */
require_once GROUPONE_SP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class-onecom-sp.php';
require_once GROUPONE_SP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class-onecom-sp-settings.php';
require_once GROUPONE_SP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class-onecom-sp-api-check.php';
require_once GROUPONE_SP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class-onecom-sp-website-check.php';

// Spam-check rule classes (originally inc/modules/*.php, relocated to rules/
// to avoid clashing conceptually with the hub's own top-level modules/ dir).
// Also required lazily by GroupOne_SpWebsiteCheck::sp_load_check_modules() at
// runtime (require_once is idempotent), but requiring them all up front here
// keeps every ported class defined as soon as the module bootstraps, matching
// how the other classes above are loaded.
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_accept.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_bbcode.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_exploit.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_long.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_proburl.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_quickres.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_referrer.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_short.php';
require_once GROUPONE_SP_PATH . 'rules' . DIRECTORY_SEPARATOR . 'oc_sp_urlshort.php';

// Instantiate exactly as the original main file did, at parse time.
new GroupOne_Sp();
new GroupOne_SpSettings();
if ( groupone_sp_is_premium() ) {
	new GroupOne_SpApiCheck();
	new GroupOne_SpWebsiteCheck();
}

/**
 * Activation rewiring: register_activation_hook() never fires for a module
 * (it isn't its own plugin file), so the same logic now hooks the hub's own
 * activation action instead. groupone_spam_protection_activate() (inc/functions.php)
 * is idempotent — it only seeds default options when none exist yet — so
 * existing installs' settings and logs are never clobbered by this.
 */
add_action( 'onecom_merged_modules_activate', 'groupone_spam_protection_activate' );
