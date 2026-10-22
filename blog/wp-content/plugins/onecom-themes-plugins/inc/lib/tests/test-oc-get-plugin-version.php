<?php
/**
 * Regression test for WPIN-8923.
 *
 * This repo has no PHPUnit suite (see MR !356), so this is a small
 * framework-free script, matching test-oc-module-active-fallback.php: run it
 * directly with `php tests/test-oc-get-plugin-version.php` and check the exit
 * code (0 = pass).
 *
 * The bug: /wp-admin/plugins.php white-screened with
 *
 *   Uncaught TypeError: version_compare(): Argument #1 ($version1) must be of
 *   type string, false given ... onecom-nested-menu.php:302
 *
 * reached through onecom_remove_menu() on admin_menu. This file declares
 * strict_types=1, so a false reaching version_compare()'s string parameter is
 * a hard TypeError rather than a coercion to '' — one missing file took the
 * entire admin down.
 *
 * It is reachable because onecom_plugin_activated() is is_plugin_active(),
 * which only consults the active_plugins option and never checks that the
 * file is on disk. A plugin that is "active" but whose file is missing or
 * half-written — exactly what the aborted install in QA finding #1 leaves
 * behind — passes that guard and then yields false from
 * oc_get_plugin_version().
 *
 * The fix makes oc_get_plugin_version() return '' rather than false and pins
 * that with a `: string` return type, so no call site can be handed a
 * non-string. Phase 1 reproduces the original crash end-to-end through the
 * real onecom_remove_menu(); phases 2-4 pin the return contract itself, which
 * is what also protects the second, structurally identical call site at
 * onecom_register_menu() (that one needs far more of the WordPress menu API
 * to drive directly, so it is covered via the contract rather than by
 * re-stubbing all of it).
 */

// Satisfies onecom-nested-menu.php's `defined('WPINC') or die()` direct-access
// guard so the file can be required outside of a WordPress bootstrap.
define( 'WPINC', 'wp-includes' );

// --- Minimal WordPress surface -------------------------------------------
// oc_get_plugin_version() does require_once ABSPATH . 'wp-admin/includes/plugin.php',
// so ABSPATH must point at a tree where that file actually exists.
$wpin8923_tmp = sys_get_temp_dir() . '/wpin8923-' . getmypid();
mkdir( $wpin8923_tmp . '/wp-admin/includes', 0777, true );
mkdir( $wpin8923_tmp . '/plugins', 0777, true );
file_put_contents( $wpin8923_tmp . '/wp-admin/includes/plugin.php', "<?php\n" );

define( 'ABSPATH', $wpin8923_tmp . '/' );
define( 'WP_PLUGIN_DIR', $wpin8923_tmp . '/plugins' );

register_shutdown_function(
	function () use ( $wpin8923_tmp ) {
		// Best-effort cleanup of the throwaway tree.
		foreach ( array_reverse( iterator_to_array(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $wpin8923_tmp, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			)
		) ) as $path => $info ) {
			$info->isDir() ? @rmdir( $path ) : @unlink( $path );
		}
		@rmdir( $wpin8923_tmp );
	}
);

$GLOBALS['wpin8923_active_plugins'] = array();

function is_plugin_active( $file ) {
	return in_array( $file, $GLOBALS['wpin8923_active_plugins'], true );
}

// onecom_remove_menu() calls these for every menu entry; no-ops are enough,
// the assertion here is only about whether the method survives the call.
function remove_menu_page( $slug ) {}
function remove_submenu_page( $parent, $slug ) {}

function get_file_data( $file, $headers, $context = '' ) {
	// Real get_file_data() opens the path with fopen(), which fails on a
	// directory; is_file() keeps this stub faithful for the '' path case.
	$contents = is_file( $file ) ? (string) file_get_contents( $file ) : '';
	$out      = array();
	foreach ( $headers as $key => $label ) {
		$out[ $key ] = preg_match( '/^[ \t\/*#@]*' . preg_quote( $label, '/' ) . ':(.*)$/mi', $contents, $m )
			? trim( $m[1] )
			: '';
	}
	return $out;
}

require_once __DIR__ . '/../class-onecom-centralized-menu.php';
require_once __DIR__ . '/../onecom-nested-menu.php';

$failures = array();

function wpin8923_check( $condition, $message ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $message;
	}
}

// Instantiate without running the constructor -- it calls add_action(),
// plugin_dir_url() and __(), none of which exist outside WordPress, and none
// of which the methods under test touch.
$menu = ( new ReflectionClass( 'GroupOne_Nested_Menu' ) )->newInstanceWithoutConstructor();

$hub_file      = 'onecom-themes-plugins/onecom-themes-plugins.php';
$hub_full_path = WP_PLUGIN_DIR . '/' . $hub_file;

// --- Phase 1: the reported crash -----------------------------------------
// Hub listed as active, but its file is NOT on disk. Before the fix this
// threw TypeError out of version_compare() and white-screened wp-admin.
$GLOBALS['wpin8923_active_plugins'] = array( $hub_file );

wpin8923_check(
	! file_exists( $hub_full_path ),
	'Phase 1 precondition: hub plugin file must be absent for this phase.'
);

try {
	$menu->onecom_remove_menu();
	wpin8923_check( true, '' );
} catch ( TypeError $e ) {
	wpin8923_check(
		false,
		'Phase 1: onecom_remove_menu() threw TypeError when the hub is active but its file is missing -- '
		. 'this is the WPIN-8923 crash: ' . $e->getMessage()
	);
} catch ( Throwable $e ) {
	wpin8923_check(
		false,
		'Phase 1: onecom_remove_menu() threw ' . get_class( $e ) . ': ' . $e->getMessage()
	);
}

wpin8923_check(
	'' === $menu->oc_get_plugin_version( $hub_file ),
	'Phase 1: a missing plugin file must yield the empty string, never false.'
);

// --- Phase 2: file present, no Version header ----------------------------
mkdir( dirname( $hub_full_path ), 0777, true );
file_put_contents( $hub_full_path, "<?php\n/*\nPlugin Name: Hub\n*/\n" );

$version_no_header = $menu->oc_get_plugin_version( $hub_file );

wpin8923_check(
	is_string( $version_no_header ),
	'Phase 2: a plugin file with no Version header must still yield a string, got '
	. gettype( $version_no_header ) . '.'
);
wpin8923_check(
	'' === $version_no_header,
	'Phase 2: a plugin file with no Version header must yield the empty string.'
);

try {
	$menu->onecom_remove_menu();
} catch ( Throwable $e ) {
	wpin8923_check(
		false,
		'Phase 2: onecom_remove_menu() threw ' . get_class( $e ) . ' for a headerless plugin file: '
		. $e->getMessage()
	);
}

// The whole point of returning '' rather than false: an unknown version must
// still compare as "older than", which is what both call sites assume.
wpin8923_check(
	false === version_compare( $version_no_header, '3.0', '>=' ),
	'Phase 2: an unknown version must not satisfy a >= 3.0 check.'
);

// --- Phase 3: a real version is still read correctly ----------------------
file_put_contents( $hub_full_path, "<?php\n/*\nPlugin Name: Hub\nVersion: 5.2.5\n*/\n" );

wpin8923_check(
	'5.2.5' === $menu->oc_get_plugin_version( $hub_file ),
	'Phase 3: a present Version header must be returned verbatim.'
);
wpin8923_check(
	true === version_compare( $menu->oc_get_plugin_version( $hub_file ), '3.0', '>=' ),
	'Phase 3: 5.2.5 must satisfy the >= 3.0 check (behaviour must be unchanged).'
);

// --- Phase 4: the contract that protects the second call site -------------
// onecom_register_menu() feeds ONECOM_VCACHE_FILE through the same helper at
// what is line 114. Driving that method directly would mean stubbing most of
// the WordPress menu API, so assert the guarantee it relies on instead: the
// helper is incapable of returning a non-string for any input.
foreach ( array( 'onecom-vcache/vcaching.php', 'does-not/exist.php', '', 'no-slash' ) as $path ) {
	$result = $menu->oc_get_plugin_version( $path );
	wpin8923_check(
		is_string( $result ),
		'Phase 4: oc_get_plugin_version(' . var_export( $path, true ) . ') returned '
		. gettype( $result ) . ', must always be string.'
	);
}

// --- Report ---------------------------------------------------------------
if ( $failures ) {
	fwrite( STDERR, "FAIL (" . count( $failures ) . ")\n" );
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "  - $failure\n" );
	}
	exit( 1 );
}

echo "PASS: oc_get_plugin_version() always returns a string; onecom_remove_menu() survives a missing/headerless hub plugin file (WPIN-8923).\n";
exit( 0 );
