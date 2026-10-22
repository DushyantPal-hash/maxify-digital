<?php
/**
 * One.com Site Abilities module.
 *
 * Registers first-party abilities for everyday WordPress management — posts &
 * pages, navigation menus, plugins and themes — with the WordPress Abilities
 * API, and contributes their slugs to the branded one.com MCP server through
 * the `onecom_mcp_ability_ids` filter (see inc/class-onecom-mcp.php).
 *
 * Exposure model: these abilities intentionally do NOT set `meta.mcp.public`,
 * so they never appear on the adapter's default MCP server. The curated
 * one.com server is their only endpoint, which keeps the tool surface (and
 * one.com policy, e.g. wp.org-only installs) under this plugin's control.
 *
 * The domain classes are always loaded (their static execute/permission
 * methods are unit-testable on any WordPress), but registration is skipped
 * when the Abilities API is absent — the module degrades to a no-op.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

require_once __DIR__ . '/classes/class-onecom-abilities-helper.php';
require_once __DIR__ . '/classes/class-onecom-abilities-posts.php';
require_once __DIR__ . '/classes/class-onecom-abilities-menus.php';
require_once __DIR__ . '/classes/class-onecom-abilities-plugins.php';
require_once __DIR__ . '/classes/class-onecom-abilities-themes.php';
require_once __DIR__ . '/classes/class-onecom-abilities-tracking.php';

if ( ! function_exists( 'onecom_abilities_domain_classes' ) ) {
	/**
	 * The domain classes this module registers abilities from.
	 *
	 * @return string[] Class names.
	 */
	function onecom_abilities_domain_classes(): array {
		return array(
			'Onecom_Abilities_Posts',
			'Onecom_Abilities_Menus',
			'Onecom_Abilities_Plugins',
			'Onecom_Abilities_Themes',
		);
	}
}

if ( ! function_exists( 'onecom_abilities_ids' ) ) {
	/**
	 * Every ability slug this module can register.
	 *
	 * @return string[] Ability slugs.
	 */
	function onecom_abilities_ids(): array {
		$ids = array();
		foreach ( onecom_abilities_domain_classes() as $class ) {
			$ids = array_merge( $ids, $class::ability_ids() );
		}
		return $ids;
	}
}

if ( ! function_exists( 'onecom_abilities_superseded_core_ids' ) ) {
	/**
	 * Core ability slugs (current or on the WordPress core roadmap) that this
	 * module supersedes with a one.com equivalent. They are kept off the
	 * one.com MCP server so the endpoint offers exactly one tool per operation
	 * and one.com policy (protected plugins, publish gating, …) cannot be
	 * bypassed by picking the unguarded core variant. Slugs that are not
	 * registered are simply never encountered, so listing planned ones is safe.
	 *
	 * @return string[] Core ability slugs.
	 */
	function onecom_abilities_superseded_core_ids(): array {
		return array(
			// Posts & pages → onecom/find-posts, get-post, create-post, update-post.
			'core/read-content',
			'core/find-posts',
			'core/get-post',
			'core/find-pages',
			'core/get-page',
			'core/create-post',
			'core/update-post',
			'core/create-page',
			'core/update-page',
			// Menus → onecom/get-menu, get-menu-locations.
			'core/get-menu',
			'core/get-menu-locations',
			// Themes → onecom/list-themes, get-theme.
			'core/list-themes',
			'core/get-active-theme',
			// Plugins → onecom/list-plugins, get-plugin, activate/deactivate/update-plugin.
			'core/list-plugins',
			'core/get-plugin',
			'core/activate-plugin',
			'core/deactivate-plugin',
			'core/update-plugin',
		);
	}
}

if ( ! function_exists( 'onecom_abilities_boot' ) ) {
	/**
	 * Hook registration into the Abilities API and expose the slugs on the
	 * one.com MCP server. No-op when the Abilities API is unavailable.
	 */
	function onecom_abilities_boot(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		// MCP usage telemetry for these abilities (no-op without OCPushStats).
		Onecom_Abilities_Tracking::boot();

		add_action( 'wp_abilities_api_categories_init', array( 'Onecom_Abilities_Helper', 'register_category' ) );

		add_action( 'wp_abilities_api_init', static function (): void {
			foreach ( onecom_abilities_domain_classes() as $class ) {
				$class::register();
			}
		} );

		add_filter( 'onecom_mcp_ability_ids', static function ( $ids ): array {
			$ids = is_array( $ids ) ? $ids : array();
			foreach ( onecom_abilities_ids() as $id ) {
				// The registry is the source of truth — only expose what registered.
				if ( function_exists( 'wp_has_ability' ) && wp_has_ability( $id ) ) {
					$ids[] = $id;
				}
			}
			return array_values( array_unique( $ids ) );
		} );

		add_filter( 'onecom_mcp_core_ability_exclusions', static function ( $excluded ): array {
			return array_values( array_unique( array_merge(
				is_array( $excluded ) ? $excluded : array(),
				onecom_abilities_superseded_core_ids()
			) ) );
		} );
	}
}

onecom_abilities_boot();
