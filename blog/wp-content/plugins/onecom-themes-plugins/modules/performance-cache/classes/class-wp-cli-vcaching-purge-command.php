<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

/**
 * Purges Varnish Cache
 *
 * Ported unchanged from onecom-vcache/class-wp-cli-vcaching-purge-command.php as
 * part of WPIN-8743. Still gated on `defined( 'WP_CLI' ) && WP_CLI`, both here and
 * in the module bootstrap (performance-cache.php) that requires this file.
 */
class GroupOne_WP_CLI_VCaching_Purge_Command extends WP_CLI_Command {


	protected GroupOne_VCaching_Base $vcaching;
	protected GroupOne_VCaching $onecominc;

	public function __construct() {
		$this->vcaching = new GroupOne_VCaching_Base();

	}

	/**
	 * Forces a Varnish Purge
	 *
	 * ## wp cli command for purging cache
	 *
	 *     wp ocvcaching purge
	 *
	 */
	public function purge() {
		wp_create_nonce( 'vcaching-purge-cli' );
		$this->vcaching->purge_url( home_url() . '/?vc-regex' );
		WP_CLI::success( 'ALL Varnish cache was purged.' );
	}

	/**
	 * Manually purges CDN
	 *
	 * ## wp cli command for purging cdn
	 *
	 *     wp ocvcaching cdnpurge
	 *
	 */

	public function cdnpurge( $args, $assoc_args ) {
		if ( ! isset( $assoc_args['onecom-domain'] ) || ! isset( $assoc_args['server-name'] ) ) {
			WP_CLI::error( 'Argument Error! Try again with --onecom-domain=xxx --server-name=xxx' );
		}
		$_SERVER['ONECOM_DOMAIN_NAME'] = $assoc_args['onecom-domain'];
		$_SERVER['SERVER_NAME']        = $assoc_args['server-name'];
		$this->onecominc               = new GroupOne_VCaching();
		$response                      = $this->onecominc->oc_purge_cdn_cache();
		if ( $response ) {
			WP_CLI::success( 'CDN purged successfully' );
		} else {
			WP_CLI::error( 'Something unexpected occurred. PLease check debug log for further details' );
		}
	}
}

WP_CLI::add_command( 'ocvcaching', 'GroupOne_WP_CLI_VCaching_Purge_Command' );
