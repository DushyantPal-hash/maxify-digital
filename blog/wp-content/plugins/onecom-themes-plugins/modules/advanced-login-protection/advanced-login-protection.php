<?php
// Exit if file accessed directly.
defined( 'WPINC' ) or die(); // No Direct Access

// Autoload ALM classes
function alp_autoloader( $class_name ) {
	// Map class name to its (unchanged) on-disk file name -- class names were
	// rebranded from Onecom_* to GroupOne_* but the physical file names were
	// intentionally left untouched, so they can no longer be derived from the
	// class name via simple string transforms and must be looked up explicitly.
	$alp_class_files = array(
		'GroupOne_ALP'               => 'onecom-alp',
		'GroupOne_ALP_Notice'        => 'onecom-alp-notice',
		'GroupOne_ALP_Reset_Password' => 'onecom-alp-reset-password',
		'GroupOne_ALP_Login'         => 'onecom-alp-onecom-login',
	);

	// Only include if ALP files
	if ( isset( $alp_class_files[ $class_name ] ) ) {
		require_once 'classes' . DIRECTORY_SEPARATOR . 'class-' . $alp_class_files[ $class_name ] . '.php';
	}
}
spl_autoload_register( 'alp_autoloader' );

// Load ALM if old login masking class does not exists in validator
if ( ! class_exists( 'OnecomLoginMasking' ) ) {
	$alp = new GroupOne_ALP();
	$alp->init();
}

// ALM notice for new users
$alm_notice = new GroupOne_ALP_Notice();
$alm_notice->init();

// Trigger Password Reset mail when disable ALP
$alm_reset_password = new GroupOne_ALP_Reset_Password();
$alm_reset_password->init();

// "Login with one.com" button on WordPress login form
$alm_onecom_login = new GroupOne_ALP_Login();
$alm_onecom_login->init();
