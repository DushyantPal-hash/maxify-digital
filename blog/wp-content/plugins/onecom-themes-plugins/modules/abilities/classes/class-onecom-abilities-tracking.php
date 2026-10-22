<?php
/**
 * MCP ability-execution telemetry.
 *
 * Emits one analytics "event" hit per ability run that originates over MCP,
 * reusing the plugin's existing OCPushStats pipeline. That pipeline dispatches
 * server-side (a cURL to the stats middleware), so it works in headless MCP
 * requests where there is no browser to run the JS tracking path.
 *
 * What each hit carries (WPIN-8851 schema):
 *   event_action   snake_case action — post_created, plugin_installed, …
 *   item_category  coarse domain — content | navigation | plugin | theme | marketplace
 *   item_name      the target — post|page, menu, plugin slug, stylesheet, addon slug
 *   item_source    always "MCP"
 *   status         success | error  (error also carries error_code)  [additional_info]
 * The base properties (domain, brand, wp/php version, hosting package, …) are
 * added by OCPushStats::stats_base_parametres() exactly like every other event.
 *
 * Guarantees (this must never affect existing behaviour):
 *   - No-op unless OCPushStats is present.
 *   - Only the abilities in self::events() are tracked; core/* and other
 *     plugins' abilities are ignored.
 *   - Only MCP-originated executions are recorded (see is_mcp_request()), so a
 *     non-MCP REST/programmatic run is never mislabelled as MCP.
 *   - Consent / plugin-active / endpoint eligibility stay owned by
 *     OCPushStats::should_send_stats() — this class adds no new consent logic.
 *   - Dispatch is deferred to `shutdown`, so the blocking stats cURL never adds
 *     latency to the MCP tool response, and any failure is swallowed.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Abilities_Tracking' ) ) {

	class Onecom_Abilities_Tracking {

		/** Constant item_source for every MCP ability event. */
		const ITEM_SOURCE = 'MCP';

		/** @var array<int,array<string,mixed>> Queued events, flushed on shutdown. */
		private static $queue = array();

		/** @var bool Whether the shutdown flush has been registered. */
		private static $flush_registered = false;

		/**
		 * Hook the tracker. Called from onecom_abilities_boot() after the
		 * Abilities API presence check, so it only runs where abilities can
		 * actually execute. No-op without the telemetry pipeline.
		 */
		public static function boot(): void {
			if ( ! class_exists( 'OCPushStats' ) ) {
				return;
			}
			add_action( 'wp_after_execute_ability', array( self::class, 'record' ), 10, 3 );
		}

		/**
		 * Ability slug => [ event_action, item_category ].
		 *
		 * The single source of truth for which abilities are tracked and how the
		 * action/domain columns are filled. item_name is derived per execution
		 * from the input/result (see item_name()).
		 *
		 * @return array<string,array{0:string,1:string}>
		 */
		public static function events(): array {
			return array(
				// Posts & pages.
				'onecom/find-posts'                   => array( 'posts_listed', 'content' ),
				'onecom/get-post'                     => array( 'post_fetched', 'content' ),
				'onecom/create-post'                  => array( 'post_created', 'content' ),
				'onecom/update-post'                  => array( 'post_updated', 'content' ),
				'onecom/delete-post'                  => array( 'post_deleted', 'content' ),
				// Navigation menus.
				'onecom/list-menus'                   => array( 'menus_listed', 'navigation' ),
				'onecom/get-menu'                     => array( 'menu_fetched', 'navigation' ),
				'onecom/create-menu'                  => array( 'menu_created', 'navigation' ),
				'onecom/delete-menu'                  => array( 'menu_deleted', 'navigation' ),
				'onecom/get-menu-locations'           => array( 'menu_locations_listed', 'navigation' ),
				'onecom/assign-menu-location'         => array( 'menu_location_assigned', 'navigation' ),
				'onecom/add-menu-item'                => array( 'menu_item_added', 'navigation' ),
				'onecom/update-menu-item'             => array( 'menu_item_updated', 'navigation' ),
				'onecom/remove-menu-item'             => array( 'menu_item_removed', 'navigation' ),
				// Plugins.
				'onecom/list-plugins'                 => array( 'plugins_listed', 'plugin' ),
				'onecom/get-plugin'                   => array( 'plugin_fetched', 'plugin' ),
				'onecom/install-plugin'               => array( 'plugin_installed', 'plugin' ),
				'onecom/activate-plugin'              => array( 'plugin_activated', 'plugin' ),
				'onecom/deactivate-plugin'            => array( 'plugin_deactivated', 'plugin' ),
				'onecom/update-plugin'                => array( 'plugin_updated', 'plugin' ),
				// Themes.
				'onecom/list-themes'                  => array( 'themes_listed', 'theme' ),
				'onecom/get-theme'                    => array( 'theme_fetched', 'theme' ),
				'onecom/install-theme'                => array( 'theme_installed', 'theme' ),
				'onecom/switch-theme'                 => array( 'theme_switched', 'theme' ),
				'onecom/update-theme'                 => array( 'theme_updated', 'theme' ),
				// Marketplace premium add-ons.
				'onecom-marketplace/get-addon-status' => array( 'addon_status_fetched', 'marketplace' ),
				'onecom-marketplace/install-addon'    => array( 'addon_installed', 'marketplace' ),
			);
		}

		/**
		 * `wp_after_execute_ability` listener. Queues a tracked ability run.
		 *
		 * @param string $name   Ability slug.
		 * @param mixed  $input  Input the ability was called with.
		 * @param mixed  $result Ability return value.
		 */
		public static function record( $name, $input, $result ): void {
			if ( ! self::is_mcp_request() ) {
				return; // These events describe MCP usage only.
			}

			$event = self::describe( $name, is_array( $input ) ? $input : array(), $result );
			if ( null === $event ) {
				return; // Not one of ours.
			}

			self::$queue[] = $event;
			self::ensure_flush();
		}

		/**
		 * Build the tracking payload for one ability run, or null if the ability
		 * is not tracked. Pure: no request-context or telemetry side effects, so
		 * it is the single unit-testable seam for the schema mapping.
		 *
		 * @param string $name   Ability slug.
		 * @param array  $input  Ability input.
		 * @param mixed  $result Ability return value.
		 * @return array{event_action:string,item_category:string,item_name:string,additional:array}|null
		 */
		public static function describe( string $name, array $input, $result ): ?array {
			$events = self::events();
			if ( ! isset( $events[ $name ] ) ) {
				return null;
			}

			$status     = self::status_of( $result );
			$additional = array( 'status' => $status );
			if ( 'error' === $status ) {
				$code = self::error_code_of( $result );
				if ( '' !== $code ) {
					$additional['error_code'] = $code;
				}
			}

			return array(
				'event_action'  => $events[ $name ][0],
				'item_category' => $events[ $name ][1],
				'item_name'     => self::item_name( $name, $input, $result ),
				'additional'    => $additional,
			);
		}

		/**
		 * Is the current request an MCP call?
		 *
		 * A union of signals, because no single one is universally present:
		 *
		 * - `Mcp-Session-Id` / `MCP-Protocol-Version` headers (surfaced as HTTP_*
		 *   in $_SERVER) — authoritative when sent, but both optional. The session
		 *   header exists only if the server issued one at `initialize`, and the
		 *   protocol header only entered the spec in the 2025-06-18 revision.
		 *   ChatGPT (`openai-mcp/1.0.0`) sends neither; it carries its own
		 *   `X-OpenAI-Session` instead, so gating on these lost every ChatGPT
		 *   ability execution silently.
		 * - `Accept: text/event-stream` — the streamable-HTTP transport requires
		 *   clients to accept both `application/json` and `text/event-stream` on
		 *   POST, so it is present exactly where the headers above are not.
		 * - A request path resolving to an MCP endpoint (`…/<ns>/mcp`).
		 *
		 * Only ever consulted from `wp_after_execute_ability`, so the request is
		 * already known to be an ability execution — the non-MCP callers there do
		 * not send `text/event-stream` nor post to an `/mcp` path.
		 *
		 * @return bool
		 */
		public static function is_mcp_request(): bool {
			$is_mcp = self::has_mcp_transport_headers()
				|| self::accepts_event_stream()
				|| self::is_mcp_endpoint_path();

			/**
			 * Filter whether the current request is treated as MCP-originated
			 * for ability telemetry.
			 *
			 * @param bool $is_mcp Detected from the MCP transport signals above.
			 */
			return (bool) apply_filters( 'onecom_abilities_is_mcp_request', $is_mcp );
		}

		/**
		 * The two MCP transport headers, when the client bothers to send them.
		 *
		 * @return bool
		 */
		private static function has_mcp_transport_headers(): bool {
			return ! empty( $_SERVER['HTTP_MCP_SESSION_ID'] ) || ! empty( $_SERVER['HTTP_MCP_PROTOCOL_VERSION'] );
		}

		/**
		 * Does the client accept an SSE stream? Mandatory for streamable-HTTP MCP POSTs.
		 *
		 * @return bool
		 */
		private static function accepts_event_stream(): bool {
			if ( empty( $_SERVER['HTTP_ACCEPT'] ) ) {
				return false;
			}

			$accept = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) );

			return false !== strpos( $accept, 'text/event-stream' );
		}

		/**
		 * Does the request target an MCP endpoint?
		 *
		 * Covers pretty REST permalinks (`/wp-json/<ns>/mcp`), the `?rest_route=`
		 * fallback, and `X-Original-URL` for proxies that rewrite `REQUEST_URI`
		 * (group.one's Varnish tier does). Worst case a spoofed header mislabels
		 * one telemetry event, which grants a caller nothing.
		 *
		 * @return bool
		 */
		private static function is_mcp_endpoint_path(): bool {
			foreach ( array( 'REQUEST_URI', 'HTTP_X_ORIGINAL_URL' ) as $key ) {
				if ( empty( $_SERVER[ $key ] ) ) {
					continue;
				}

				$raw   = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				$query = (string) wp_parse_url( $raw, PHP_URL_QUERY );
				$path  = (string) wp_parse_url( $raw, PHP_URL_PATH );

				// `?rest_route=/easy-mcp-ai/v1/mcp` when pretty permalinks are off.
				if ( '' !== $query ) {
					$args = array();
					wp_parse_str( $query, $args );
					if ( ! empty( $args['rest_route'] ) ) {
						$path = (string) $args['rest_route'];
					}
				}

				if ( '' === $path ) {
					continue;
				}

				if ( 'mcp' === strtolower( basename( untrailingslashit( $path ) ) ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Map an ability return value to success|error.
		 *
		 * @param mixed $result Ability return value.
		 * @return string
		 */
		private static function status_of( $result ): string {
			if ( is_wp_error( $result ) ) {
				return 'error';
			}
			if ( is_array( $result ) && array_key_exists( 'success', $result ) ) {
				return $result['success'] ? 'success' : 'error';
			}
			return 'success';
		}

		/**
		 * Extract a machine-readable error code from a failed result.
		 *
		 * @param mixed $result Ability return value.
		 * @return string
		 */
		private static function error_code_of( $result ): string {
			if ( is_wp_error( $result ) ) {
				return (string) $result->get_error_code();
			}
			if ( is_array( $result ) && ! empty( $result['code'] ) ) {
				return (string) $result['code'];
			}
			return '';
		}

		/**
		 * Derive item_name (the target of the operation) for a given ability.
		 *
		 * @param string $name   Ability slug.
		 * @param array  $input  Ability input.
		 * @param mixed  $result Ability return value.
		 * @return string Empty string means "no single target" (list actions).
		 */
		private static function item_name( string $name, array $input, $result ): string {
			switch ( $name ) {
				// Posts & pages: the post type is the meaningful dimension.
				case 'onecom/find-posts':
				case 'onecom/get-post':
				case 'onecom/create-post':
				case 'onecom/update-post':
				case 'onecom/delete-post':
					if ( is_array( $result ) && ! empty( $result['post']['type'] ) ) {
						return (string) $result['post']['type'];
					}
					return isset( $input['post_type'] ) ? sanitize_key( (string) $input['post_type'] ) : 'post';

				// Menus: a single type marker (no stable per-menu slug).
				case 'onecom/list-menus':
				case 'onecom/get-menu':
				case 'onecom/create-menu':
				case 'onecom/delete-menu':
				case 'onecom/get-menu-locations':
				case 'onecom/assign-menu-location':
				case 'onecom/add-menu-item':
				case 'onecom/update-menu-item':
				case 'onecom/remove-menu-item':
					return 'menu';

				// List-all reads have no single target.
				case 'onecom/list-plugins':
				case 'onecom/list-themes':
					return '';

				// Plugins addressed by file → directory slug.
				case 'onecom/get-plugin':
				case 'onecom/activate-plugin':
				case 'onecom/deactivate-plugin':
				case 'onecom/update-plugin':
					return ! empty( $input['plugin_file'] ) ? dirname( (string) $input['plugin_file'] ) : '';

				// Installs addressed by wp.org slug.
				case 'onecom/install-plugin':
				case 'onecom/install-theme':
					return isset( $input['slug'] ) ? sanitize_key( (string) $input['slug'] ) : '';

				// Themes addressed by stylesheet (falls back to the result).
				case 'onecom/get-theme':
				case 'onecom/switch-theme':
				case 'onecom/update-theme':
					if ( ! empty( $input['stylesheet'] ) ) {
						return sanitize_text_field( (string) $input['stylesheet'] );
					}
					if ( is_array( $result ) && ! empty( $result['theme']['stylesheet'] ) ) {
						return (string) $result['theme']['stylesheet'];
					}
					if ( is_array( $result ) && ! empty( $result['stylesheet'] ) ) {
						return (string) $result['stylesheet'];
					}
					return '';

				// Marketplace add-ons: the addon key (wp-rocket | rank-math).
				case 'onecom-marketplace/get-addon-status':
				case 'onecom-marketplace/install-addon':
					return isset( $input['addon'] ) ? sanitize_key( (string) $input['addon'] ) : '';
			}

			return '';
		}

		/**
		 * Register the one-shot shutdown flush.
		 */
		private static function ensure_flush(): void {
			if ( self::$flush_registered ) {
				return;
			}
			self::$flush_registered = true;
			add_action( 'shutdown', array( self::class, 'flush' ), 100 );
		}

		/**
		 * Send all queued events. Runs on `shutdown` so the blocking stats cURL
		 * never delays the tool response. Never throws.
		 */
		public static function flush(): void {
			if ( empty( self::$queue ) ) {
				return;
			}

			$queue        = self::$queue;
			self::$queue  = array();

			if ( ! class_exists( 'OCPushStats' ) ) {
				return;
			}

			// OCPushStats::should_send_stats() calls is_plugin_active(), which
			// lives in an admin include not auto-loaded on REST requests.
			if ( ! function_exists( 'is_plugin_active' ) && defined( 'ABSPATH' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( $queue as $event ) {
				try {
					\OCPushStats::push_stats_misc_events(
						$event['event_action'],
						$event['item_category'],
						$event['item_name'],
						self::ITEM_SOURCE,
						null,
						$event['additional']
					);
				} catch ( \Throwable $e ) {
					// Telemetry must never break the request.
					error_log( '[Onecom_Abilities_Tracking] dispatch failed: ' . $e->getMessage() );
				}
			}
		}
	}
}
