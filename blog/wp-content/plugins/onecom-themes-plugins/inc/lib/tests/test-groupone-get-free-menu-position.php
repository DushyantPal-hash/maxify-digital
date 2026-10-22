<?php
/**
 * Test for groupone_get_free_menu_position() (WPIN-9033, WPIN-9035).
 *
 * Standalone framework-free test script: run directly with:
 *   php tests/test-groupone-get-free-menu-position.php
 * Exit code 0 = pass.
 */

// Define stubs for WP functions used during validator bootstrap
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'https://example.com/wp-content/plugins/';
	}
}
if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return dirname( $file ) . '/';
	}
}
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}
if ( ! function_exists( 'is_admin' ) ) {
	function is_admin() {
		return false;
	}
}
if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return 0;
	}
}

// Load validator file
require_once __DIR__ . '/../validator.php';

if ( ! function_exists( 'groupone_get_free_menu_position' ) ) {
	fwrite( STDERR, "FAIL: groupone_get_free_menu_position is not defined\n" );
	exit( 1 );
}

// 1. Multiple collisions (float accumulation drift fix WPIN-9035)
$GLOBALS['menu'] = array( '2.1' => 'a', '2.4' => 'b', '2.7' => 'c' );
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '3' ) {
	fwrite( STDERR, "FAIL: multiple collisions expected '3', got '$result'\n" );
	exit( 1 );
}

// 2. Initial position is free
$GLOBALS['menu'] = array( '2' => 'dashboard', '10' => 'media' );
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '2.1' ) {
	fwrite( STDERR, "FAIL: free position expected '2.1', got '$result'\n" );
	exit( 1 );
}

// 3. Single collision
$GLOBALS['menu'] = array( '2' => 'dashboard', '2.1' => 'taken' );
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '2.4' ) {
	fwrite( STDERR, "FAIL: single collision expected '2.4', got '$result'\n" );
	exit( 1 );
}

// 4. Strict type check: '2.1' must not match '2.10'
$GLOBALS['menu'] = array( '2.10' => 'something' );
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '2.1' ) {
	fwrite( STDERR, "FAIL: strict check expected '2.1' not to match '2.10', got '$result'\n" );
	exit( 1 );
}

// 5. Unset / empty / non-array $GLOBALS['menu'] (WPIN-9033 guards)
unset( $GLOBALS['menu'] );
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '2.1' ) {
	fwrite( STDERR, "FAIL: unset global expected '2.1', got '$result'\n" );
	exit( 1 );
}

$GLOBALS['menu'] = array();
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '2.1' ) {
	fwrite( STDERR, "FAIL: empty global expected '2.1', got '$result'\n" );
	exit( 1 );
}

$GLOBALS['menu'] = 'not-an-array';
$result = groupone_get_free_menu_position( '2.1' );
if ( $result !== '2.1' ) {
	fwrite( STDERR, "FAIL: non-array global expected '2.1', got '$result'\n" );
	exit( 1 );
}

echo "PASS: groupone_get_free_menu_position tests passed successfully.\n";
exit( 0 );
