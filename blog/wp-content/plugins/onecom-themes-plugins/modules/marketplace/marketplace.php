<?php

// Exit if file accessed directly.
defined('WPINC') or die(); // No Direct Access

/**
 * The core plugin class that is used to define functions,
 * admin-specific hooks, and public-facing site hooks.
 */

// WP Rocket buy CP purchase URL
if ( ! defined( 'GROUPONE_WPR_BUY_URL' ) ) {
	$domain        = $_SERVER['ONECOM_DOMAIN_NAME'] ?? '';
	define( 'GROUPONE_WPR_BUY_URL', sprintf( "https://one.com/admin/wprocket-prepare-buy.do?directToDomainAfterPurchase=%s&amp;domain=%s", GROUPONE_HTTP_HOST, $domain ) );
}

// Rank Math buys CP purchase URL
if ( ! defined( 'GROUPONE_RM_PRO_BUY_URL' ) ) {
	$domain        = $_SERVER['ONECOM_DOMAIN_NAME'] ?? '';
	define( 'GROUPONE_RM_PRO_BUY_URL', sprintf( "https://one.com/admin/rankmath-prepare-buy.do?directToDomainAfterPurchase=%s&amp;domain=%s", GROUPONE_HTTP_HOST, $domain ) );
}

if( !defined( 'MARKETPLACE_PAGE_SLUG' ) ) {
	define( 'MARKETPLACE_PAGE_SLUG' , 'onecom-marketplace' );
}

if( !defined( 'MARKETPLACE_PRODUCTS_PAGE_SLUG' ) ) {
	define( 'MARKETPLACE_PRODUCTS_PAGE_SLUG' , 'onecom-marketplace-products' );
}

require_once plugin_dir_path(__FILE__) . 'classes/class-marketplace.php';
require_once plugin_dir_path(__FILE__) . 'classes/class-marketplace-admin-survey.php';

// Load WP-Rocket
if (class_exists('OnecomMarketplace')) {
	$mp_object = new OnecomMarketplace();
	$mp_object->init();
}

if (class_exists('Marketplace_Admin_Survey')) {
	new Marketplace_Admin_Survey();
}

// Subscription-gated premium add-on abilities (WP Rocket, Rank Math PRO) over MCP.
require_once plugin_dir_path(__FILE__) . 'classes/class-onecom-marketplace-premium-abilities.php';

/**
 * Expose the marketplace abilities on the branded one.com MCP server as well
 * (see inc/class-onecom-mcp.php). The marketplace package keeps self-exposing
 * on the adapter's default server via meta.mcp.public — this only makes the
 * same tools first-class citizens of the curated one.com endpoint.
 *
 * Discovered from the registry rather than built for one brand: every embedded
 * copy of the marketplace package (one.com's own, Rank Math PRO's, …) registers
 * its abilities under "<brand>-marketplace/" plus the shared "marketplace/"
 * actions, so a prefix match picks up all brands present on the site.
 */
add_filter( 'onecom_mcp_ability_ids', static function ( $ids ) {
	$ids = is_array( $ids ) ? $ids : array();

	if ( ! function_exists( 'wp_get_abilities' ) ) {
		return $ids;
	}

	foreach ( wp_get_abilities() as $ability ) {
		if ( ! is_object( $ability ) || ! method_exists( $ability, 'get_name' ) ) {
			continue;
		}
		$name = $ability->get_name();
		if ( preg_match( '#^([a-z0-9-]+-)?marketplace/#', (string) $name ) ) {
			$ids[] = $name;
		}
	}

	return array_values( array_unique( $ids ) );
} );

/**
 * Compatibility shim for the vendored marketplace install ability over MCP/REST.
 *
 * The package's PluginService::install() runs Plugin_Upgrader but only loads
 * wp-admin/includes/class-wp-upgrader.php. In an MCP/REST request the filesystem
 * helpers in wp-admin/includes/file.php (request_filesystem_credentials() and
 * WP_Filesystem()) are not auto-loaded — that context only happens in wp-admin —
 * so the install callback fatals with "Call to undefined function
 * request_filesystem_credentials()". Preload those admin includes for REST
 * requests so marketplace/install-plugin (and any upgrader-based ability) works
 * over MCP. Idempotent and guarded; the durable fix belongs upstream in
 * groupone/marketplace.
 */
add_action( 'rest_api_init', static function () {
	if ( ! function_exists( 'request_filesystem_credentials' ) || ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
}, 5 );
