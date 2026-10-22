<?php
/**
 * Regression test for WPIN-8795.
 *
 * This repo has no PHPUnit suite (see MR !356), so this is a small
 * framework-free script: run it directly with `php
 * tests/test-validator-vendor-guard.php` and check the exit code (0 = pass).
 *
 * validator.php is bundled as a byte-identical copy in the hub plugin and
 * all three legacy standalone plugins. Its vendor/autoload.php include is
 * guarded by `file_exists( $filepath ) && ! ( class_exists( 'OTPHP\TOTP' )
 * && class_exists( 'ParagonIE\ConstantTime\Base32' ) )` so that a sibling
 * plugin which already loaded an identical vendor tree in the same request
 * does not redeclare the same Composer autoloader class and fatal. This
 * test simulates that scenario directly (without needing a second physical
 * plugin/vendor tree) and confirms both that the guard correctly skips the
 * second include, and that OTPHP\TOTP/ParagonIE\ConstantTime\Base32 remain
 * fully usable afterwards -- do NOT swap the class_exists() checks below
 * for a generic `class_exists( 'Composer\Autoload\ClassLoader' )` check; an
 * earlier attempt at that false-positived against unrelated Composer
 * autoloaders already loaded elsewhere on a site (e.g. a hosting mu-plugin),
 * breaking this file even when no sibling one.com plugin was involved.
 */

$vendor_autoload = __DIR__ . '/../vendor/autoload.php';

if ( ! file_exists( $vendor_autoload ) ) {
	fwrite( STDERR, "FAIL: vendor/autoload.php not found at $vendor_autoload\n" );
	exit( 1 );
}

// First "plugin" loads the vendor tree normally.
require_once $vendor_autoload;

if ( ! class_exists( 'OTPHP\TOTP' ) || ! class_exists( 'ParagonIE\ConstantTime\Base32' ) ) {
	fwrite( STDERR, "FAIL: expected OTPHP\\TOTP and ParagonIE\\ConstantTime\\Base32 to be loaded after the first include.\n" );
	exit( 1 );
}

// Second "plugin" (a sibling bundling an identical copy of this same vendor
// tree) runs the exact guard expression from validator.php. It must
// evaluate to "skip" -- if it evaluated to "include" instead, re-including
// this exact vendor/autoload.php a second time would redeclare the same
// ComposerAutoloaderInit<hash> class and fatal, exactly as WPIN-8795
// reproduced live on wpin10.
$should_include_again = file_exists( $vendor_autoload )
	&& ! ( class_exists( 'OTPHP\TOTP' ) && class_exists( 'ParagonIE\ConstantTime\Base32' ) );

if ( $should_include_again ) {
	fwrite( STDERR, "FAIL: guard evaluated to include a second time; this would redeclare the Composer autoloader and fatal in a real two-plugin scenario.\n" );
	exit( 1 );
}

// Confirm the classes are still fully functional after being skipped the
// second time -- the point of the fix is that skipping the redundant
// vendor include must not leave anything broken for the plugin that skipped
// it.
try {
	$totp   = OTPHP\TOTP::create();
	$secret = $totp->getSecret();

	if ( empty( $secret ) || strlen( $secret ) < 16 ) {
		fwrite( STDERR, "FAIL: TOTP::create() did not return a usable secret.\n" );
		exit( 1 );
	}

	$encoded = ParagonIE\ConstantTime\Base32::encodeUpper( 'wpin-8795' );

	if ( empty( $encoded ) ) {
		fwrite( STDERR, "FAIL: Base32::encodeUpper() did not return an encoded value.\n" );
		exit( 1 );
	}
} catch ( \Throwable $e ) {
	fwrite( STDERR, 'FAIL: exception using OTPHP\TOTP/Base32 after guard skip: ' . $e->getMessage() . "\n" );
	exit( 1 );
}

echo "PASS: vendor-autoload guard correctly skips a redundant re-include and OTPHP\\TOTP/Base32 remain usable afterwards.\n";
exit( 0 );
