<?php
/**
 * Regression test for WPIN-8934.
 *
 * This repo has no PHPUnit suite (see MR !356), so this is a small
 * framework-free script, matching the other files under tests/: run it
 * directly with `php tests/test-webconfig-header-name.php` and check the
 * exit code (0 = pass).
 *
 * The bug: "Rebuild staging" (and every other premium/feature check —
 * ismWP(), isPremium(), the Performance Cache/Spam Protection/Maintenance
 * Mode MWP gates, the Marketplace addon-status check) showed the "upgrade
 * to Managed WordPress" modal even on domains that already had an MWP
 * subscription. Root cause: groupone_validate_domain() and
 * groupone_add_http_headers() both build the outgoing X-ONECOM-WEBCONFIG-NAME
 * header (sent to the wpapi.one.com middleware on cluster-model domains,
 * i.e. MWP) from an *incoming* request header read as
 * $_SERVER['HTTP_X_GROUPONE_WEBCONFIG_NAME'] — but every other header this
 * file reads off the incoming request uses the 'Onecom' naming
 * (X-Onecom-Client-Domain, X-Onecom-Client-Subdomain), as does the
 * middleware's own header constant (HEADER_WEBCONFIG =
 * 'x-onecom-webconfig-name' in the wordpress-addons-api repo's
 * src/shared/constants.ts). The incoming request never actually carries an
 * 'X-Groupone-Webconfig-Name' header, so $webconfig_name was always empty on
 * every MWP domain, and the middleware could not identify the site's
 * webconfig — breaking the feature lookup for exactly the customers it's
 * supposed to identify as premium.
 *
 * This test stubs the minimal set of WordPress functions validator.php
 * touches at file-load time, simulates a cluster-model (MWP) request
 * carrying both the correct and the legacy header name, and asserts
 * groupone_add_http_headers() prefers the correctly-named one.
 */

define( 'WPINC', 'wp-includes' );

$failures = array();
function wpin8934_check( $condition, $message ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $message;
	}
}

// --- Minimal WordPress stubs so validator.php can be loaded standalone ---
foreach ( array( 'add_action', 'add_filter', 'register_activation_hook', 'register_deactivation_hook', 'add_shortcode', 'load_textdomain', 'load_plugin_textdomain', 'wp_enqueue_script', 'wp_enqueue_style', 'wp_register_script', 'wp_localize_script', 'add_option', 'update_option', 'add_site_option', 'update_site_option' ) as $fn ) {
	if ( ! function_exists( $fn ) ) {
		eval( "function $fn(...\$a){ return true; }" );
	}
}
foreach ( array( '__', 'esc_html__', 'esc_attr__', '_e', 'esc_html', 'esc_attr', 'esc_url', 'esc_js', 'wp_kses_post' ) as $fn ) {
	if ( ! function_exists( $fn ) ) {
		eval( "function $fn(\$s='',...\$a){ return \$s; }" );
	}
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $f ) {
		return 'http://example.test/';
	}
}
if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $f ) {
		return dirname( $f ) . '/';
	}
}
if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $f ) {
		return basename( $f );
	}
}

// --- Simulate an incoming request on a cluster-model (MWP) domain,
// carrying BOTH header names, before validator.php defines
// GROUPONE_CLUSTER_ID/GROUPONE_WEBCONFIG_ID from these same superglobals ---
$_SERVER['REQUEST_METHOD']                     = 'GET';
$_SERVER['ONECOM_CLUSTER_ID']                  = 'cluster-42';
$_SERVER['ONECOM_WEBCONFIG_ID']                = 'webconfig-99';
$_SERVER['HTTP_X_ONECOM_WEBCONFIG_NAME']       = 'the-correct-name';
$_SERVER['HTTP_X_GROUPONE_WEBCONFIG_NAME']     = 'the-legacy-wrong-name';

require __DIR__ . '/../validator.php';

wpin8934_check(
	is_cluster_domain() === true,
	'is_cluster_domain() should be true once ONECOM_CLUSTER_ID and ONECOM_WEBCONFIG_ID are both set (this is what an MWP domain looks like)'
);

// --- Phase 1: groupone_add_http_headers() (the http_request_args filter) ---
$data = groupone_add_http_headers( array(), 'https://wpapi.one.com/api/v1.0/features/cluster' );

wpin8934_check(
	isset( $data['headers']['X-ONECOM-WEBCONFIG-NAME'] ),
	'groupone_add_http_headers() should set an X-ONECOM-WEBCONFIG-NAME header on a cluster-model domain'
);
wpin8934_check(
	isset( $data['headers']['X-ONECOM-WEBCONFIG-NAME'] ) && $data['headers']['X-ONECOM-WEBCONFIG-NAME'] === 'the-correct-name',
	"groupone_add_http_headers() should read \$_SERVER['HTTP_X_ONECOM_WEBCONFIG_NAME'] (got '" . ( $data['headers']['X-ONECOM-WEBCONFIG-NAME'] ?? 'null' ) . "', wanted 'the-correct-name') -- WPIN-8934 regression: it read the never-sent 'HTTP_X_GROUPONE_WEBCONFIG_NAME' key instead and always got an empty value on real MWP domains"
);

// --- Phase 2: same fallback logic with only the legacy header present,
// confirming the fix doesn't regress a hypothetical setup that somehow only
// has the old header name (defensive fallback, not expected in production) --
unset( $_SERVER['HTTP_X_ONECOM_WEBCONFIG_NAME'] );
$GLOBALS['webconfig_name'] = null; // groupone_add_http_headers() memoizes this across calls in the same request.
$data2 = groupone_add_http_headers( array(), 'https://wpapi.one.com/api/v1.0/features/cluster' );
wpin8934_check(
	isset( $data2['headers']['X-ONECOM-WEBCONFIG-NAME'] ) && $data2['headers']['X-ONECOM-WEBCONFIG-NAME'] === 'the-legacy-wrong-name',
	'groupone_add_http_headers() should still fall back to the legacy header name if the correct one is absent, so this fix cannot regress an unforeseen setup'
);

if ( ! empty( $failures ) ) {
	fwrite( STDERR, "FAIL:\n" );
	foreach ( $failures as $f ) {
		fwrite( STDERR, " - $f\n" );
	}
	exit( 1 );
}

echo "PASS: groupone_add_http_headers() prefers the correctly-named ONECOM webconfig header (WPIN-8934)\n";
exit( 0 );
