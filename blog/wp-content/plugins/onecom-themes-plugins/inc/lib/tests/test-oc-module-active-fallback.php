<?php
/**
 * Regression test for WPIN-8757.
 *
 * This repo has no PHPUnit suite (see MR !356), so this is a small
 * framework-free script: run it directly with `php
 * tests/test-oc-module-active-fallback.php` and check the exit code (0 = pass).
 *
 * oc_module_active() in both onecom-nested-menu.php (GroupOne_Nested_Menu)
 * and class-onecom-centralized-menu.php (OneComCentralizedMenu) gates every
 * merged-module menu item on `function_exists('groupone_module_active') &&
 * groupone_module_active($slug)`. This exact guard already caused a real
 * regression once, during the WPIN-8739 GroupOne rebrand (see the WPIN-8757
 * comment thread): the hub's function was renamed but the function_exists()
 * check here was not updated, so it silently evaluated to false forever and
 * every merged-module menu item disappeared with no error anywhere. This
 * test would have caught that -- it asserts oc_module_active() returns false
 * when the hub function is undefined, and defers to it once defined.
 *
 * A second, near-identical regression was found in the same code during the
 * WPIN-8736 review: as of that MR, the hub (inc/module-registry.php) still
 * only defines onecom_module_active() -- groupone_module_active() has never
 * actually existed in the hub. oc_module_active() now checks for it first
 * (in case the hub renames to match later) and falls back to
 * onecom_module_active(), the name the hub actually defines today. Phase 2
 * below exercises exactly that fallback -- the code path this submodule
 * depends on in production right now -- which the original test never
 * covered, since it only simulated the hub's *future* renamed function.
 */

// Satisfies onecom-nested-menu.php's `defined('WPINC') or die()` direct-access
// guard so the file can be required outside of a WordPress bootstrap.
define( 'WPINC', 'wp-includes' );

require_once __DIR__ . '/../class-onecom-centralized-menu.php';
require_once __DIR__ . '/../onecom-nested-menu.php';

$failures = array();

function wpin8757_check( $condition, $message ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $message;
	}
}

// Instantiate without running the constructors -- they call add_action(),
// plugin_dir_url() and __(), none of which exist outside WordPress, and none
// of which oc_module_active() needs (it doesn't touch $this at all).
$centralized_menu = ( new ReflectionClass( 'OneComCentralizedMenu' ) )->newInstanceWithoutConstructor();
$nested_menu      = ( new ReflectionClass( 'GroupOne_Nested_Menu' ) )->newInstanceWithoutConstructor();

// --- Phase 1: groupone_module_active() undefined ------------------------
// Must run BEFORE the function is defined below -- PHP functions can't be
// un-defined once declared in the same process.

wpin8757_check(
	false === $centralized_menu->oc_module_active( 'performance-cache' ),
	'OneComCentralizedMenu::oc_module_active() should return false when groupone_module_active() is undefined'
);

wpin8757_check(
	false === $nested_menu->oc_module_active( 'performance-cache' ),
	'GroupOne_Nested_Menu::oc_module_active() should return false when groupone_module_active() is undefined'
);

// --- Phase 2: only onecom_module_active() defined (today's real state) --
// Simulates the hub exactly as it is today (inc/module-registry.php) --
// groupone_module_active() does NOT exist; onecom_module_active() does.
// This is the fallback branch that is actually load-bearing in production
// right now, and the gap this phase closes: neither prior version of this
// test ever defined onecom_module_active() without also defining
// groupone_module_active(), so a regression in this exact fallback (e.g. a
// typo, or dropping the branch entirely) would have passed unnoticed.
//
// Wrapped in a runtime condition for the same compile-time-hoisting reason
// as groupone_module_active() below.

if ( ! function_exists( 'onecom_module_active' ) ) {
	function onecom_module_active( string $slug ): bool {
		return 'spam-protection' === $slug;
	}
}

wpin8757_check(
	true === $centralized_menu->oc_module_active( 'spam-protection' ),
	'OneComCentralizedMenu::oc_module_active() should defer to onecom_module_active() and return true for an active module when groupone_module_active() does not exist'
);

wpin8757_check(
	false === $centralized_menu->oc_module_active( 'performance-cache' ),
	'OneComCentralizedMenu::oc_module_active() should defer to onecom_module_active() and return false for an inactive module when groupone_module_active() does not exist'
);

wpin8757_check(
	true === $nested_menu->oc_module_active( 'spam-protection' ),
	'GroupOne_Nested_Menu::oc_module_active() should defer to onecom_module_active() and return true for an active module when groupone_module_active() does not exist'
);

wpin8757_check(
	false === $nested_menu->oc_module_active( 'performance-cache' ),
	'GroupOne_Nested_Menu::oc_module_active() should defer to onecom_module_active() and return false for an inactive module when groupone_module_active() does not exist'
);

// --- Phase 3: groupone_module_active() also defined ----------------------
// Simulates a future hub rename to match this submodule's own GroupOne
// rebrand. groupone_module_active() must take priority over
// onecom_module_active() (still defined from Phase 2 above -- PHP functions
// can't be un-defined) so the two stubs are deliberately given opposite
// answers per slug: if the result matches groupone's stub instead of
// onecom's, priority ordering is correct.
//
// The declaration below is wrapped in a runtime condition on purpose: PHP
// hoists unconditional top-level function declarations at compile time, so
// an unwrapped `function groupone_module_active(){}` here would already
// exist during Phases 1-2 above and silently defeat this entire test.

if ( ! function_exists( 'groupone_module_active' ) ) {
	function groupone_module_active( string $slug ): bool {
		return 'performance-cache' === $slug;
	}
}

wpin8757_check(
	true === $centralized_menu->oc_module_active( 'performance-cache' ),
	'OneComCentralizedMenu::oc_module_active() should prefer groupone_module_active() over onecom_module_active() and return true for an active module'
);

wpin8757_check(
	false === $centralized_menu->oc_module_active( 'spam-protection' ),
	'OneComCentralizedMenu::oc_module_active() should prefer groupone_module_active() over onecom_module_active() and return false once it disagrees with the fallback'
);

wpin8757_check(
	true === $nested_menu->oc_module_active( 'performance-cache' ),
	'GroupOne_Nested_Menu::oc_module_active() should prefer groupone_module_active() over onecom_module_active() and return true for an active module'
);

wpin8757_check(
	false === $nested_menu->oc_module_active( 'spam-protection' ),
	'GroupOne_Nested_Menu::oc_module_active() should prefer groupone_module_active() over onecom_module_active() and return false once it disagrees with the fallback'
);

// --- Phase 4: oc_feature_active() -----------------------------------------
// oc_feature_active() is the actual method wired into every submenu gate
// call site (Performance Cache, Spam Protection, Maintenance Mode, across
// both classes) -- oc_module_active() above is only ever consumed through
// it. Its logic is a plain OR: active as a merged module, OR active as the
// legacy standalone plugin. Stubs is_plugin_active() the same way the hub
// functions above are stubbed, backed by a settable global list so each
// case below can control which plugin file is "active".
//
// From Phase 3, oc_module_active() now reports 'performance-cache' active
// and 'spam-protection' inactive (groupone_module_active()'s stub, which
// takes priority) -- reused here rather than re-stubbed, to exercise both
// sides of the OR against a case oc_module_active() alone doesn't cover.

if ( ! function_exists( 'is_plugin_active' ) ) {
	$GLOBALS['wpin8757_stub_active_plugins'] = array();
	function is_plugin_active( string $plugin ): bool {
		return in_array( $plugin, $GLOBALS['wpin8757_stub_active_plugins'], true );
	}
}

$GLOBALS['wpin8757_stub_active_plugins'] = array();

wpin8757_check(
	true === $centralized_menu->oc_feature_active( 'performance-cache', 'onecom-vcache/vcaching.php' ),
	'OneComCentralizedMenu::oc_feature_active() should return true when the module is active, even if the legacy plugin is not'
);

wpin8757_check(
	false === $centralized_menu->oc_feature_active( 'spam-protection', 'onecom-spam-protection/onecom-spam-protection.php' ),
	'OneComCentralizedMenu::oc_feature_active() should return false when neither the module nor the legacy plugin is active'
);

$GLOBALS['wpin8757_stub_active_plugins'] = array( 'onecom-spam-protection/onecom-spam-protection.php' );

wpin8757_check(
	true === $centralized_menu->oc_feature_active( 'spam-protection', 'onecom-spam-protection/onecom-spam-protection.php' ),
	'OneComCentralizedMenu::oc_feature_active() should return true when the legacy plugin is active, even if the module is not'
);

$GLOBALS['wpin8757_stub_active_plugins'] = array();

wpin8757_check(
	true === $nested_menu->oc_feature_active( 'performance-cache', 'onecom-vcache/vcaching.php' ),
	'GroupOne_Nested_Menu::oc_feature_active() should return true when the module is active, even if the legacy plugin is not'
);

wpin8757_check(
	false === $nested_menu->oc_feature_active( 'spam-protection', 'onecom-spam-protection/onecom-spam-protection.php' ),
	'GroupOne_Nested_Menu::oc_feature_active() should return false when neither the module nor the legacy plugin is active'
);

$GLOBALS['wpin8757_stub_active_plugins'] = array( 'onecom-spam-protection/onecom-spam-protection.php' );

wpin8757_check(
	true === $nested_menu->oc_feature_active( 'spam-protection', 'onecom-spam-protection/onecom-spam-protection.php' ),
	'GroupOne_Nested_Menu::oc_feature_active() should return true when the legacy plugin is active, even if the module is not'
);

if ( ! empty( $failures ) ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: $failure\n" );
	}
	exit( 1 );
}

echo "PASS: oc_module_active() returns false when neither hub function is defined, defers to onecom_module_active() when that's all the hub provides (today's real state), prefers groupone_module_active() over it once both exist, and oc_feature_active() correctly ORs module-active with legacy-plugin-active, in both onecom-nested-menu.php and class-onecom-centralized-menu.php.\n";
exit( 0 );
