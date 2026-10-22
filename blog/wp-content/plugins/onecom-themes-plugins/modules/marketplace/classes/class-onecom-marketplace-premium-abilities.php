<?php
/**
 * MCP abilities for one.com's subscription-gated premium add-ons.
 *
 * The generic marketplace/install-plugin ability only installs plugins that
 * expose a plain download URL in the catalog. WP Rocket and Rank Math PRO are
 * not plain downloads: they are entitlement-gated per one.com domain and are
 * provisioned server-side (WP Rocket) or fetched from a vendor-license URL
 * (Rank Math). These abilities mirror that flow so an MCP client can install
 * and activate them exactly as the Marketplace UI does — install only when the
 * domain holds a subscription, otherwise return the purchase URL.
 *
 * Brand-specific one.com logic lives here in the host plugin (not the shared
 * marketplace package). It reuses OnecomMarketplace's existing entitlement,
 * provisioner and vendor-license methods plus the package's PluginService, so
 * there is a single source of truth for the premium flow across the web UI and
 * MCP. Exposed on the branded one.com MCP server via the marketplace-slug
 * discovery in this module's marketplace.php.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Marketplace_Premium_Abilities' ) ) {

	class Onecom_Marketplace_Premium_Abilities {

		/** Ability category (registered by the marketplace package). */
		const CATEGORY = 'marketplace';

		/** PluginService FQCN (Mozart-prefixed dependency). */
		const PLUGIN_SERVICE = '\WPMarketplace\Dependencies\Groupone\Marketplace\Services\PluginService';

		/**
		 * Premium add-ons this exposes, keyed by the MCP-facing addon key.
		 * Each maps to the plugin slug used on disk and the buy-URL constant.
		 *
		 * @return array<string,array{slug:string,buy_url_const:string,name:string}>
		 */
		public static function addons(): array {
			return array(
				'wp-rocket' => array(
					'slug'          => 'wp-rocket',
					'buy_url_const' => 'OC_WPR_BUY_URL',
					'name'          => 'WP Rocket',
				),
				'rank-math' => array(
					'slug'          => 'seo-by-rank-math-pro',
					'buy_url_const' => 'OC_RM_PRO_BUY_URL',
					'name'          => 'Rank Math PRO',
				),
			);
		}

		/** Ability slugs this class registers. */
		public static function ability_ids(): array {
			return array(
				'onecom-marketplace/get-addon-status',
				'onecom-marketplace/install-addon',
			);
		}

		/**
		 * Whether the premium flow is available (host class + package present).
		 */
		public static function available(): bool {
			return class_exists( 'OnecomMarketplace' ) && class_exists( self::PLUGIN_SERVICE );
		}

		/**
		 * Load the wp-admin includes the upgrader needs.
		 *
		 * In an MCP/REST request these are not auto-loaded, so the upgrader's
		 * filesystem step (request_filesystem_credentials()/WP_Filesystem(),
		 * both in file.php) would fatal. The shared PluginService::install()
		 * only pulls in class-wp-upgrader.php, so the host loads the rest here
		 * before driving an install.
		 */
		private static function load_admin_dependencies(): void {
			if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'activate_plugin' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( ! function_exists( 'request_filesystem_credentials' ) || ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			if ( ! function_exists( 'show_message' ) ) {
				require_once ABSPATH . 'wp-admin/includes/misc.php';
			}
			if ( ! class_exists( 'Plugin_Upgrader' ) || ! class_exists( 'Automatic_Upgrader_Skin' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
		}

		/**
		 * Hook registration. Safe to call once per request; no-op without the
		 * Abilities API.
		 *
		 * The premium dependencies (OnecomMarketplace + the Mozart-prefixed
		 * PluginService) are only guaranteed once the marketplace autoloader is
		 * registered on init, which is later than this file loads. So the
		 * availability check is deferred to register_abilities(), which runs on
		 * wp_abilities_api_init — by then the autoloader is up.
		 */
		public static function register(): void {
			if ( ! function_exists( 'wp_register_ability' ) ) {
				return;
			}
			add_action( 'wp_abilities_api_init', array( self::class, 'register_abilities' ) );
		}

		/**
		 * Meta matching the sibling marketplace abilities so these behave the
		 * same way across servers.
		 *
		 * @param array $annotations Tool annotations.
		 * @return array
		 */
		private static function tool_meta( array $annotations = array() ): array {
			$meta = array(
				'mcp'          => array( 'public' => true, 'type' => 'tool' ),
				'show_in_rest' => true,
			);
			if ( ! empty( $annotations ) ) {
				$meta['annotations'] = $annotations;
			}
			return $meta;
		}

		/**
		 * The addon-key input schema shared by both abilities.
		 */
		private static function addon_input(): array {
			return array(
				'type'       => 'object',
				'properties' => array(
					'addon' => array(
						'type'        => 'string',
						'enum'        => array_keys( self::addons() ),
						'description' => __( 'Premium add-on to act on.', 'onecom-wp' ),
					),
				),
				'required'   => array( 'addon' ),
			);
		}

		/**
		 * Register the two premium-addon abilities.
		 */
		public static function register_abilities(): void {
			if ( ! self::available() ) {
				return; // Premium dependencies (host class + package) not present.
			}
			if ( function_exists( 'wp_has_ability_category' ) && ! wp_has_ability_category( self::CATEGORY ) ) {
				return; // Marketplace category not registered — package not booted.
			}

			wp_register_ability(
				'onecom-marketplace/get-addon-status',
				array(
					'label'               => __( 'Get premium add-on status', 'onecom-wp' ),
					'description'         => __( 'Check a one.com premium add-on (WP Rocket, Rank Math PRO): whether the domain has an active subscription, and whether the plugin is installed and active. When not subscribed, returns the purchase URL.', 'onecom-wp' ),
					'category'            => self::CATEGORY,
					'input_schema'        => self::addon_input(),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'addon'     => array( 'type' => 'string' ),
							'name'      => array( 'type' => 'string' ),
							'purchased' => array( 'type' => 'boolean', 'description' => 'Domain holds an active subscription/entitlement.' ),
							'installed' => array( 'type' => 'boolean' ),
							'active'    => array( 'type' => 'boolean' ),
							'buy_url'   => array( 'type' => 'string', 'description' => 'Control-panel purchase URL (present when not purchased).' ),
							'error'     => array( 'type' => 'string' ),
						),
					),
					'permission_callback' => static fn() => current_user_can( 'install_plugins' ),
					'execute_callback'    => array( self::class, 'get_addon_status' ),
					'meta'                => self::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom-marketplace/install-addon',
				array(
					'label'               => __( 'Install a premium add-on', 'onecom-wp' ),
					'description'         => __( 'Install and activate a one.com premium add-on (WP Rocket, Rank Math PRO) when the domain has an active subscription. If there is no subscription, no install happens and the purchase URL is returned. WP Rocket is provisioned server-side and may take a short while — re-check with get-addon-status.', 'onecom-wp' ),
					'category'            => self::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'addon'    => array(
								'type'        => 'string',
								'enum'        => array_keys( self::addons() ),
								'description' => __( 'Premium add-on to install.', 'onecom-wp' ),
							),
							'activate' => array(
								'type'        => 'boolean',
								'description' => __( 'Activate after install when possible. Defaults to true.', 'onecom-wp' ),
							),
						),
						'required'   => array( 'addon' ),
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success'   => array( 'type' => 'boolean' ),
							'code'      => array( 'type' => 'string', 'description' => 'already_active | addon_not_purchased | provisioning | installed | activated | error' ),
							'message'   => array( 'type' => 'string' ),
							'addon'     => array( 'type' => 'string' ),
							'installed' => array( 'type' => 'boolean' ),
							'active'    => array( 'type' => 'boolean' ),
							'buy_url'   => array( 'type' => 'string' ),
						),
					),
					'permission_callback' => static fn() => current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ),
					'execute_callback'    => array( self::class, 'install_addon' ),
					// Installs and runs third-party code; idempotent (re-running when
					// already active is a no-op).
					'meta'                => self::tool_meta( array( 'readonly' => false, 'destructive' => true, 'idempotent' => true ) ),
				)
			);
		}

		/**
		 * Resolve and validate the addon key from input.
		 *
		 * @param array $input Ability input.
		 * @return string|\WP_Error
		 */
		private static function resolve_addon( array $input ) {
			$addon = isset( $input['addon'] ) ? sanitize_key( (string) $input['addon'] ) : '';
			// Accept the on-disk slug as an alias for convenience.
			if ( 'seo-by-rank-math-pro' === $addon ) {
				$addon = 'rank-math';
			}
			if ( ! isset( self::addons()[ $addon ] ) ) {
				return new \WP_Error(
					'onecom_unknown_addon',
					__( 'Unknown premium add-on. Use "wp-rocket" or "rank-math".', 'onecom-wp' )
				);
			}
			return $addon;
		}

		/**
		 * Buy URL for an addon, or '' if its constant is undefined.
		 *
		 * @param string $addon Addon key.
		 * @return string
		 */
		private static function buy_url( string $addon ): string {
			$const = self::addons()[ $addon ]['buy_url_const'] ?? '';
			return ( '' !== $const && defined( $const ) ) ? (string) constant( $const ) : '';
		}

		/**
		 * onecom-marketplace/get-addon-status
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function get_addon_status( array $input ): array {
			$addon = self::resolve_addon( $input );
			if ( is_wp_error( $addon ) ) {
				return array( 'error' => $addon->get_error_message() );
			}

			$slug      = self::addons()[ $addon ]['slug'];
			$service   = self::PLUGIN_SERVICE;
			$mp        = new \OnecomMarketplace();
			$purchased = (bool) $mp->isAddonPurchased( $addon );
			$installed = $service::is_installed( $slug );
			$active    = $installed && $service::is_active( $slug );

			$status = array(
				'addon'     => $addon,
				'name'      => self::addons()[ $addon ]['name'],
				'purchased' => $purchased,
				'installed' => $installed,
				'active'    => $active,
			);
			if ( ! $purchased ) {
				$status['buy_url'] = self::buy_url( $addon );
			}
			return $status;
		}

		/**
		 * onecom-marketplace/install-addon
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function install_addon( array $input ): array {
			$addon = self::resolve_addon( $input );
			if ( is_wp_error( $addon ) ) {
				return self::result( false, 'error', $addon->get_error_message(), $addon );
			}

			$slug     = self::addons()[ $addon ]['slug'];
			$name     = self::addons()[ $addon ]['name'];
			$activate = ! array_key_exists( 'activate', $input ) || ! empty( $input['activate'] );
			$service  = self::PLUGIN_SERVICE;
			$mp       = new \OnecomMarketplace();

			// The shared PluginService only loads the upgrader class; load the
			// remaining wp-admin includes so the filesystem step does not fatal
			// in this non-admin (MCP/REST) request.
			self::load_admin_dependencies();

			// Already active — nothing to do.
			if ( $service::is_installed( $slug ) && $service::is_active( $slug ) ) {
				return self::result(
					true,
					'already_active',
					sprintf( /* translators: %s: add-on name */ __( '%s is already installed and active.', 'onecom-wp' ), $name ),
					null,
					true,
					true
				);
			}

			// Entitlement gate — the whole point of this ability. No subscription
			// means no install; hand back the purchase URL instead.
			if ( ! $mp->isAddonPurchased( $addon ) ) {
				$out            = self::result(
					false,
					'addon_not_purchased',
					sprintf( /* translators: %s: add-on name */ __( '%s requires an active one.com subscription for this domain. Purchase it, then install.', 'onecom-wp' ), $name ),
					null,
					$service::is_installed( $slug ),
					false
				);
				$out['buy_url'] = self::buy_url( $addon );
				return $out;
			}

			// Rank Math PRO: when the domain holds a vendor licence (rather than a
			// purchased subscription), it can be installed directly from the vendor
			// package URL. Best-effort only — on any failure we fall through to the
			// server-side provisioner below, which is the mechanism for purchased
			// subscriptions and also installs the required Rank Math (free)
			// dependency. Never bail here: a vendor-URL that is empty or not
			// directly downloadable must not block the provisioner path.
			if ( 'rank-math' === $addon && ! $service::is_installed( $slug ) ) {
				$vendor = $mp->getVendorLicenseDetails( $addon );
				if ( ! empty( $vendor['download_url'] ) ) {
					// Skip Rank Math's post-install registration prompt.
					update_site_option( 'rank_math_registration_skip', 1 );
					$service::install( $slug, (string) $vendor['download_url'] );
				}
			}

			// Server-side provisioning (WP Rocket always; Rank Math purchased
			// subscription path). Asynchronous: the plugin appears once the
			// provisioner runs, so an entitled request that is not yet on disk
			// returns 'provisioning' and the client re-checks with get-addon-status.
			$provision_status = $mp->callWpApiProvisioner( $addon );

			// If it is on disk now (Rank Math vendor install, or an earlier
			// provisioning run), activate it.
			if ( $activate && $service::is_installed( $slug ) ) {
				$act = $service::activate( $slug );
				if ( ! empty( $act['success'] ) ) {
					return self::result(
						true,
						'activated',
						sprintf( /* translators: %s: add-on name */ __( '%s installed and activated.', 'onecom-wp' ), $name ),
						null,
						true,
						true
					);
				}
			}

			if ( $service::is_installed( $slug ) ) {
				return self::result(
					true,
					'installed',
					sprintf( /* translators: %s: add-on name */ __( '%s installed. Activate it to start using it.', 'onecom-wp' ), $name ),
					null,
					true,
					false
				);
			}

			// Not on disk yet — provisioning is in flight; the client should
			// re-check with get-addon-status.
			return self::result(
				'addon_not_subscribed' !== $provision_status,
				'provisioning',
				sprintf( /* translators: %s: add-on name */ __( '%s is being provisioned for this site. Re-check its status in a little while.', 'onecom-wp' ), $name ),
				null,
				false,
				false
			);
		}

		/**
		 * Build a normalized result array.
		 *
		 * @param bool          $success   Success flag.
		 * @param string        $code      Machine-readable outcome.
		 * @param string        $message   Human-readable message.
		 * @param \WP_Error|null $error    Optional error (adds its message).
		 * @param bool          $installed Post-state on disk.
		 * @param bool          $active    Post-state active.
		 * @return array
		 */
		private static function result( bool $success, string $code, string $message, ?\WP_Error $error = null, bool $installed = false, bool $active = false ): array {
			return array(
				'success'   => $success,
				'code'      => $code,
				'message'   => null !== $error ? $error->get_error_message() : $message,
				'installed' => $installed,
				'active'    => $active,
			);
		}
	}
}

Onecom_Marketplace_Premium_Abilities::register();
