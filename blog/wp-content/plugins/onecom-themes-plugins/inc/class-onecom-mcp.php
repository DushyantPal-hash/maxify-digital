<?php
/**
 * One.com MCP integration.
 *
 * Stands up a dedicated, branded MCP server for one.com's OWN capabilities and
 * exposes two sets of tools on it:
 *
 *   1. One.com's own abilities — any one.com module (health monitor, vulnerability
 *      monitor, …) registers its abilities with the WordPress Abilities API
 *      (`wp_register_ability` on `wp_abilities_api_init`) and contributes their slugs
 *      through the `onecom_mcp_ability_ids` filter. That is the single, easy seam for
 *      the rest of the plugin to plug capabilities into this server.
 *
 *   2. WordPress core's own abilities — the `core/*` abilities WordPress registers
 *      (get-site-info, get-user-info, get-environment-info, …), discovered from the
 *      registry so an agent can drive core WordPress through this server too.
 *
 * The marketplace module contributes its ability slugs through the same filter (see
 * modules/marketplace/marketplace.php), so marketplace tools are first-class citizens
 * of this branded endpoint too. The marketplace package additionally self-exposes on
 * the site's default MCP server via meta.mcp.public — that host-agnostic channel is
 * left untouched so other hosts (e.g. RankMath) keep working with zero wiring.
 *
 * The Abilities API + mcp-adapter are shared, site-level libraries (NOT Mozart-prefixed).
 * Everything here degrades to a no-op if they are absent, so the plugin never fatals.
 *
 * @package onecom-themes-plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Onecom_Mcp' ) ) {
	class Onecom_Mcp {

		/** mcp-adapter server identity. URL: /wp-json/{ROUTE_NS}/{ROUTE}. */
		const SERVER_ID = 'onecom-mcp';
		const ROUTE_NS  = 'mcp';
		const ROUTE     = 'onecom-mcp';

		/**
		 * Hook the integration. Safe to call once per request on `init`.
		 */
		public static function init(): void {
			// MCP needs BOTH the Abilities API and the MCP adapter. Both are provided by
			// WordPress core from 7.0; we bundle neither. On any earlier version one or
			// both are absent, so we bail quietly here — the marketplace's normal admin
			// UI / install / activate flows are untouched and keep working. The feature
			// simply switches on once the site reaches a core that ships both.
			if ( ! function_exists( 'wp_register_ability' ) || ! class_exists( '\\WP\\MCP\\Core\\McpAdapter' ) ) {
				return;
			}

			// Boot the adapter if nothing else has (idempotent singleton).
			\WP\MCP\Core\McpAdapter::instance();

			// Servers must be created during mcp_adapter_init. Registered at init:10,
			// before the adapter's own init (init:20 / rest_api_init:15) fires.
			add_action( 'mcp_adapter_init', [ self::class, 'register_server' ] );
		}

		/**
		 * Create the one.com MCP server with the merged tool list.
		 *
		 * @param mixed $adapter The WP\MCP\Core\McpAdapter instance passed by the action.
		 */
		public static function register_server( $adapter ): void {
			$tools = self::ability_ids();
			if ( empty( $tools ) ) {
				return; // No abilities to expose — don't create an empty server.
			}

			// Creating the dedicated server is the only failure-prone step. Keep it
			// non-fatal: if create_server errors or throws, the abilities are still
			// registered with meta.mcp.public=true, so they remain discoverable and
			// executable through the core default server's discover/execute tools.
			// MCP keeps working — just without the branded onecom-mcp endpoint.
			try {
				$result = $adapter->create_server(
					self::SERVER_ID,
					self::ROUTE_NS,
					self::ROUTE,
					__( 'One.com Site Assistant', 'onecom-wp' ),
					__( 'Discover, install and manage plugins and one.com site features over MCP.', 'onecom-wp' ),
					'v1.0.0',
					[ \WP\MCP\Transport\HttpTransport::class ],
					\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
					null,
					$tools
				);

				if ( is_wp_error( $result ) ) {
					error_log( '[Onecom_Mcp] create_server failed: ' . $result->get_error_message() . ' — abilities remain available via the default MCP server (mcp.public).' );
				}
			} catch ( \Throwable $e ) {
				error_log( '[Onecom_Mcp] create_server threw: ' . $e->getMessage() . ' — abilities remain available via the default MCP server (mcp.public).' );
			}
		}

		/**
		 * The full tool list for the one.com server: the plugin's own abilities merged
		 * with the marketplace module's. Deduplicated, order-stable.
		 *
		 * @return string[]
		 */
		public static function ability_ids(): array {
			/**
			 * one.com modules (abilities, marketplace, health monitor, …) add their
			 * own MCP ability slugs here. Only slugs actually present in the abilities
			 * registry should be contributed (guard with wp_has_ability()).
			 *
			 * @param string[] $ids Ability slugs to expose on the one.com MCP server.
			 */
			$host = apply_filters( 'onecom_mcp_ability_ids', [] );
			$host = is_array( $host ) ? $host : [];

			return array_values( array_unique( array_merge( $host, self::core_ability_ids() ) ) );
		}

		/**
		 * WordPress core's own abilities (the `core/*` namespace), discovered from the
		 * registry so the agent can drive core WordPress through this server too. Tools
		 * only — resource/prompt abilities are skipped. Opt out entirely via the
		 * `onecom_mcp_include_core_abilities` filter.
		 *
		 * Core abilities that a one.com module supersedes with a policy-carrying
		 * equivalent (e.g. onecom/deactivate-plugin guards platform plugins) are
		 * excluded via `onecom_mcp_core_ability_exclusions`, so this server offers
		 * exactly one tool per operation and one.com policy cannot be bypassed by
		 * picking the unguarded core variant.
		 *
		 * @return string[]
		 */
		private static function core_ability_ids(): array {
			if ( ! function_exists( 'wp_get_abilities' )
				|| ! apply_filters( 'onecom_mcp_include_core_abilities', true ) ) {
				return [];
			}

			/**
			 * Core ability slugs to keep OFF the one.com MCP server because a
			 * one.com ability supersedes them (see modules/abilities/abilities.php).
			 *
			 * @param string[] $excluded Core ability slugs to exclude.
			 */
			$excluded = (array) apply_filters( 'onecom_mcp_core_ability_exclusions', [] );

			$ids = [];
			foreach ( wp_get_abilities() as $ability ) {
				if ( ! is_object( $ability ) || ! method_exists( $ability, 'get_name' ) ) {
					continue;
				}
				$name = $ability->get_name();
				if ( strpos( $name, 'core/' ) !== 0 ) {
					continue; // WordPress core abilities only.
				}
				if ( in_array( $name, $excluded, true ) ) {
					continue; // Superseded by a one.com ability on this server.
				}
				// Expose tools; skip resource/prompt abilities.
				$meta = method_exists( $ability, 'get_meta' ) ? $ability->get_meta() : [];
				if ( ( $meta['mcp']['type'] ?? 'tool' ) !== 'tool' ) {
					continue;
				}
				$ids[] = $name;
			}
			return $ids;
		}
	}
}
