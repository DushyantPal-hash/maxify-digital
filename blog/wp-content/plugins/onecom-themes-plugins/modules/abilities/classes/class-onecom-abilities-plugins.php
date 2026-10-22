<?php
/**
 * Abilities: plugin management.
 *
 * Read + lifecycle abilities for installed plugins, and installation from the
 * WordPress.org directory only — installs never accept arbitrary package URLs.
 * Premium/catalog products install through the marketplace abilities instead
 * (marketplace/install-plugin), which also owns plugin deletion; that is why
 * there is deliberately no onecom/delete-plugin.
 *
 * A protected list (filterable) blocks lifecycle operations on the one.com
 * platform plugins themselves, so an agent cannot sever its own integration.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Abilities_Plugins' ) ) {

	class Onecom_Abilities_Plugins {

		/**
		 * Ability slugs this class registers.
		 *
		 * @return string[]
		 */
		public static function ability_ids(): array {
			return array(
				'onecom/list-plugins',
				'onecom/get-plugin',
				'onecom/install-plugin',
				'onecom/activate-plugin',
				'onecom/deactivate-plugin',
				'onecom/update-plugin',
			);
		}

		/**
		 * Load the wp-admin includes the lifecycle operations need.
		 */
		public static function load_admin_dependencies(): void {
			if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'activate_plugin' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}
			if ( ! class_exists( 'Plugin_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
		}

		/**
		 * Plugin directories that MCP lifecycle operations must not touch.
		 *
		 * @return string[] Plugin directory names.
		 */
		public static function protected_plugin_dirs(): array {
			return (array) apply_filters(
				'onecom_abilities_protected_plugins',
				array(
					'onecom-themes-plugins',
					'onecom-vcache',
				)
			);
		}

		/**
		 * Whether a plugin file belongs to a protected plugin.
		 *
		 * @param string $plugin_file Plugin file (e.g. "akismet/akismet.php").
		 * @return bool
		 */
		public static function is_protected( string $plugin_file ): bool {
			return in_array( dirname( $plugin_file ), self::protected_plugin_dirs(), true );
		}

		/**
		 * Validate a plugin_file input against the installed plugins.
		 *
		 * @param array $input Ability input.
		 * @return string|\WP_Error Plugin file.
		 */
		public static function resolve_plugin_file( array $input ) {
			self::load_admin_dependencies();

			$plugin_file = isset( $input['plugin_file'] ) ? trim( (string) $input['plugin_file'] ) : '';
			if ( '' === $plugin_file || 0 !== validate_file( $plugin_file ) || str_starts_with( $plugin_file, '/' ) || str_starts_with( $plugin_file, '\\' ) ) {
				return new \WP_Error( 'onecom_invalid_plugin_file', __( 'Invalid plugin file path.', 'onecom-wp' ) );
			}
			if ( ! array_key_exists( $plugin_file, get_plugins() ) ) {
				return new \WP_Error( 'onecom_plugin_not_found', __( 'Plugin not found.', 'onecom-wp' ) );
			}
			return $plugin_file;
		}

		/**
		 * Register every ability of this domain.
		 */
		public static function register(): void {
			$plugin_file_input = array(
				'type'       => 'object',
				'properties' => array(
					'plugin_file' => array(
						'type'        => 'string',
						'description' => __( 'Plugin file path relative to the plugins directory, e.g. "akismet/akismet.php" (see list-plugins).', 'onecom-wp' ),
					),
				),
				'required'   => array( 'plugin_file' ),
			);

			wp_register_ability(
				'onecom/list-plugins',
				array(
					'label'               => __( 'List installed plugins', 'onecom-wp' ),
					'description'         => __( 'List installed plugins with activation state and available updates.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'status' => array(
								'type' => 'string',
								'enum' => array( 'active', 'inactive', 'all' ),
							),
						),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array(
							'plugins' => array( 'type' => 'array' ),
							'total'   => array( 'type' => 'integer' ),
						)
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'activate_plugins' ),
					'execute_callback'    => array( self::class, 'list_plugins' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/get-plugin',
				array(
					'label'               => __( 'Get plugin details', 'onecom-wp' ),
					'description'         => __( 'Get details for one installed plugin.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => $plugin_file_input,
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'plugin' => array( 'type' => 'object' ) )
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'activate_plugins' ),
					'execute_callback'    => array( self::class, 'get_plugin' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/install-plugin',
				array(
					'label'               => __( 'Install plugin from WordPress.org', 'onecom-wp' ),
					'description'         => __( 'Install a free plugin from the WordPress.org directory by slug, optionally activating it. For marketplace/premium products use the marketplace tools instead.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'slug'     => array( 'type' => 'string', 'description' => __( 'WordPress.org plugin slug.', 'onecom-wp' ) ),
							'activate' => array( 'type' => 'boolean' ),
						),
						'required'   => array( 'slug' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array(
							'plugin_file' => array( 'type' => 'string' ),
							'activated'   => array( 'type' => 'boolean' ),
						)
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'install_plugins' ),
					'execute_callback'    => array( self::class, 'install_plugin' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/activate-plugin',
				array(
					'label'               => __( 'Activate plugin', 'onecom-wp' ),
					'description'         => __( 'Activate an installed plugin.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => $plugin_file_input,
					'output_schema'       => Onecom_Abilities_Helper::output_schema(),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'activate_plugins' ),
					'execute_callback'    => array( self::class, 'activate_plugin' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/deactivate-plugin',
				array(
					'label'               => __( 'Deactivate plugin', 'onecom-wp' ),
					'description'         => __( 'Deactivate an active plugin. one.com platform plugins cannot be deactivated this way.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => $plugin_file_input,
					'output_schema'       => Onecom_Abilities_Helper::output_schema(),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'activate_plugins' ),
					'execute_callback'    => array( self::class, 'deactivate_plugin' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/update-plugin',
				array(
					'label'               => __( 'Update plugin', 'onecom-wp' ),
					'description'         => __( 'Update an installed plugin to its latest available version.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => $plugin_file_input,
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'new_version' => array( 'type' => 'string' ) )
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'update_plugins' ),
					'execute_callback'    => array( self::class, 'update_plugin' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);
		}

		/**
		 * Compact API shape for a plugin row.
		 *
		 * @param string $plugin_file Plugin file.
		 * @param array  $data        Header data from get_plugins().
		 * @return array
		 */
		public static function format_plugin( string $plugin_file, array $data ): array {
			$updates = get_site_transient( 'update_plugins' );
			return array(
				'file'             => $plugin_file,
				'name'             => (string) ( $data['Name'] ?? '' ),
				'version'          => (string) ( $data['Version'] ?? '' ),
				'author'           => (string) ( $data['AuthorName'] ?? $data['Author'] ?? '' ),
				'description'      => (string) ( $data['Description'] ?? '' ),
				'active'           => is_plugin_active( $plugin_file ),
				'update_available' => isset( $updates->response[ $plugin_file ] ),
				'protected'        => self::is_protected( $plugin_file ),
			);
		}

		/**
		 * onecom/list-plugins
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function list_plugins( array $input ): array {
			self::load_admin_dependencies();

			$status  = isset( $input['status'] ) ? sanitize_key( (string) $input['status'] ) : 'all';
			$plugins = array();
			foreach ( get_plugins() as $plugin_file => $data ) {
				$active = is_plugin_active( $plugin_file );
				if ( ( 'active' === $status && ! $active ) || ( 'inactive' === $status && $active ) ) {
					continue;
				}
				$plugins[] = self::format_plugin( $plugin_file, $data );
			}

			return Onecom_Abilities_Helper::ok(
				array(
					'plugins' => $plugins,
					'total'   => count( $plugins ),
				)
			);
		}

		/**
		 * onecom/get-plugin
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function get_plugin( array $input ): array {
			$plugin_file = self::resolve_plugin_file( $input );
			if ( is_wp_error( $plugin_file ) ) {
				return Onecom_Abilities_Helper::fail( $plugin_file->get_error_message(), $plugin_file->get_error_code() );
			}
			$data = get_plugins()[ $plugin_file ];
			return Onecom_Abilities_Helper::ok(
				array(
					'plugin' => array_merge(
						self::format_plugin( $plugin_file, $data ),
						array(
							'plugin_uri'   => (string) ( $data['PluginURI'] ?? '' ),
							'requires_wp'  => (string) ( $data['RequiresWP'] ?? '' ),
							'requires_php' => (string) ( $data['RequiresPHP'] ?? '' ),
						)
					),
				)
			);
		}

		/**
		 * onecom/install-plugin — WordPress.org directory only.
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function install_plugin( array $input ): array {
			self::load_admin_dependencies();

			$slug = sanitize_key( (string) ( $input['slug'] ?? '' ) );
			if ( '' === $slug ) {
				return Onecom_Abilities_Helper::fail( __( 'A plugin slug is required.', 'onecom-wp' ), 'onecom_slug_required' );
			}

			// Resolve the package through the WordPress.org API — the only
			// accepted install source for this ability.
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);
			if ( is_wp_error( $api ) ) {
				return Onecom_Abilities_Helper::fail( $api->get_error_message(), $api->get_error_code() );
			}
			if ( empty( $api->download_link ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Could not resolve a download for this plugin.', 'onecom-wp' ), 'onecom_no_package' );
			}

			if ( ! WP_Filesystem() ) {
				return Onecom_Abilities_Helper::fail( __( 'The WordPress filesystem is not writable.', 'onecom-wp' ), 'onecom_fs_unavailable' );
			}

			$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
			$result   = $upgrader->install( esc_url_raw( (string) $api->download_link ) );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}
			if ( true !== $result ) {
				return Onecom_Abilities_Helper::fail( __( 'Plugin installation failed.', 'onecom-wp' ), 'onecom_install_failed' );
			}

			$plugin_file = (string) $upgrader->plugin_info();
			$activated   = false;

			if ( ! empty( $input['activate'] ) && '' !== $plugin_file ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return Onecom_Abilities_Helper::fail( __( 'Installed, but you are not allowed to activate plugins.', 'onecom-wp' ), 'onecom_cannot_activate' );
				}
				$activation = activate_plugin( $plugin_file );
				if ( is_wp_error( $activation ) ) {
					return Onecom_Abilities_Helper::fail(
						sprintf(
							/* translators: %s: activation error message */
							__( 'Installed, but activation failed: %s', 'onecom-wp' ),
							$activation->get_error_message()
						),
						'onecom_activation_failed'
					);
				}
				$activated = true;
			}

			return Onecom_Abilities_Helper::ok(
				array(
					'plugin_file' => $plugin_file,
					'activated'   => $activated,
				)
			);
		}

		/**
		 * onecom/activate-plugin
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function activate_plugin( array $input ): array {
			$plugin_file = self::resolve_plugin_file( $input );
			if ( is_wp_error( $plugin_file ) ) {
				return Onecom_Abilities_Helper::fail( $plugin_file->get_error_message(), $plugin_file->get_error_code() );
			}
			if ( is_plugin_active( $plugin_file ) ) {
				return Onecom_Abilities_Helper::ok();
			}
			$result = activate_plugin( $plugin_file );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}
			return Onecom_Abilities_Helper::ok();
		}

		/**
		 * onecom/deactivate-plugin
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function deactivate_plugin( array $input ): array {
			$plugin_file = self::resolve_plugin_file( $input );
			if ( is_wp_error( $plugin_file ) ) {
				return Onecom_Abilities_Helper::fail( $plugin_file->get_error_message(), $plugin_file->get_error_code() );
			}
			if ( self::is_protected( $plugin_file ) ) {
				return Onecom_Abilities_Helper::fail(
					__( 'This one.com platform plugin cannot be deactivated over MCP.', 'onecom-wp' ),
					'onecom_plugin_protected'
				);
			}
			if ( ! is_plugin_active( $plugin_file ) ) {
				return Onecom_Abilities_Helper::ok();
			}
			deactivate_plugins( $plugin_file );
			return Onecom_Abilities_Helper::ok();
		}

		/**
		 * onecom/update-plugin
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function update_plugin( array $input ): array {
			$plugin_file = self::resolve_plugin_file( $input );
			if ( is_wp_error( $plugin_file ) ) {
				return Onecom_Abilities_Helper::fail( $plugin_file->get_error_message(), $plugin_file->get_error_code() );
			}

			$updates = get_site_transient( 'update_plugins' );
			if ( ! isset( $updates->response[ $plugin_file ] ) ) {
				// A prior update in the same session clears the update_plugins
				// transient (wp_clean_update_cache on upgrader_process_complete),
				// which would make a second plugin look up-to-date. Rebuild the
				// update list and re-check before reporting "no update".
				wp_update_plugins();
				$updates = get_site_transient( 'update_plugins' );
				if ( ! isset( $updates->response[ $plugin_file ] ) ) {
					return Onecom_Abilities_Helper::fail( __( 'No update is available for this plugin.', 'onecom-wp' ), 'onecom_no_update' );
				}
			}

			if ( ! WP_Filesystem() ) {
				return Onecom_Abilities_Helper::fail( __( 'The WordPress filesystem is not writable.', 'onecom-wp' ), 'onecom_fs_unavailable' );
			}

			$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
			$result   = $upgrader->upgrade( $plugin_file );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}
			if ( true !== $result ) {
				return Onecom_Abilities_Helper::fail( __( 'Plugin update failed.', 'onecom-wp' ), 'onecom_update_failed' );
			}

			self::load_admin_dependencies();
			$data = get_plugins()[ $plugin_file ] ?? array();

			return Onecom_Abilities_Helper::ok(
				array( 'new_version' => (string) ( $data['Version'] ?? '' ) )
			);
		}
	}
}
