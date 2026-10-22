<?php
/**
 * Abilities: theme management.
 *
 * List/get installed themes, install from the WordPress.org directory only,
 * switch the active theme and update. There is deliberately no delete-theme:
 * removing themes is rarely what a site owner wants an agent to do, and a
 * wrong guess (deleting the parent of the active child theme) breaks the site.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Abilities_Themes' ) ) {

	class Onecom_Abilities_Themes {

		/**
		 * Ability slugs this class registers.
		 *
		 * @return string[]
		 */
		public static function ability_ids(): array {
			return array(
				'onecom/list-themes',
				'onecom/get-theme',
				'onecom/install-theme',
				'onecom/switch-theme',
				'onecom/update-theme',
			);
		}

		/**
		 * Load the wp-admin includes the lifecycle operations need.
		 */
		public static function load_admin_dependencies(): void {
			if ( ! function_exists( 'themes_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/theme-install.php';
				require_once ABSPATH . 'wp-admin/includes/theme.php';
			}
			if ( ! class_exists( 'Theme_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
		}

		/**
		 * Register every ability of this domain.
		 */
		public static function register(): void {
			$stylesheet_input = array(
				'type'       => 'object',
				'properties' => array(
					'stylesheet' => array(
						'type'        => 'string',
						'description' => __( 'Theme directory name, e.g. "twentytwentyfive" (see list-themes).', 'onecom-wp' ),
					),
				),
				'required'   => array( 'stylesheet' ),
			);

			wp_register_ability(
				'onecom/list-themes',
				array(
					'label'               => __( 'List installed themes', 'onecom-wp' ),
					'description'         => __( 'List installed themes with the active one flagged and available updates.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array( 'type' => 'object', 'properties' => new \stdClass() ),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array(
							'themes' => array( 'type' => 'array' ),
							'total'  => array( 'type' => 'integer' ),
						)
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'switch_themes' ),
					'execute_callback'    => array( self::class, 'list_themes' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/get-theme',
				array(
					'label'               => __( 'Get theme details', 'onecom-wp' ),
					'description'         => __( 'Get details for one installed theme, or the active theme when no stylesheet is given.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'stylesheet' => array( 'type' => 'string' ),
						),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'theme' => array( 'type' => 'object' ) )
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'switch_themes' ),
					'execute_callback'    => array( self::class, 'get_theme' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/install-theme',
				array(
					'label'               => __( 'Install theme from WordPress.org', 'onecom-wp' ),
					'description'         => __( 'Install a free theme from the WordPress.org directory by slug. Does not switch to it unless activate=true.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'slug'     => array( 'type' => 'string', 'description' => __( 'WordPress.org theme slug.', 'onecom-wp' ) ),
							'activate' => array( 'type' => 'boolean' ),
						),
						'required'   => array( 'slug' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array(
							'stylesheet' => array( 'type' => 'string' ),
							'activated'  => array( 'type' => 'boolean' ),
						)
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'install_themes' ),
					'execute_callback'    => array( self::class, 'install_theme' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/switch-theme',
				array(
					'label'               => __( 'Switch active theme', 'onecom-wp' ),
					'description'         => __( 'Switch the site to another installed theme. Changes the site\'s appearance immediately.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => $stylesheet_input,
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'stylesheet' => array( 'type' => 'string' ) )
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'switch_themes' ),
					'execute_callback'    => array( self::class, 'switch_theme' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/update-theme',
				array(
					'label'               => __( 'Update theme', 'onecom-wp' ),
					'description'         => __( 'Update an installed theme to its latest available version.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => $stylesheet_input,
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'new_version' => array( 'type' => 'string' ) )
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'update_themes' ),
					'execute_callback'    => array( self::class, 'update_theme' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);
		}

		/**
		 * Compact API shape for a theme.
		 *
		 * @param \WP_Theme $theme Theme.
		 * @return array
		 */
		public static function format_theme( \WP_Theme $theme ): array {
			$updates = get_site_transient( 'update_themes' );
			return array(
				'stylesheet'       => $theme->get_stylesheet(),
				'name'             => (string) $theme->get( 'Name' ),
				'version'          => (string) $theme->get( 'Version' ),
				'author'           => (string) $theme->get( 'Author' ),
				'description'      => (string) $theme->get( 'Description' ),
				'parent'           => $theme->parent() ? $theme->parent()->get_stylesheet() : '',
				'active'           => get_stylesheet() === $theme->get_stylesheet(),
				'update_available' => isset( $updates->response[ $theme->get_stylesheet() ] ),
			);
		}

		/**
		 * onecom/list-themes
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function list_themes( array $input ): array {
			$themes = array_map( array( self::class, 'format_theme' ), array_values( wp_get_themes() ) );
			return Onecom_Abilities_Helper::ok(
				array(
					'themes' => $themes,
					'total'  => count( $themes ),
				)
			);
		}

		/**
		 * onecom/get-theme
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function get_theme( array $input ): array {
			$stylesheet = isset( $input['stylesheet'] ) ? sanitize_text_field( (string) $input['stylesheet'] ) : '';
			$theme      = '' === $stylesheet ? wp_get_theme() : wp_get_theme( $stylesheet );
			if ( ! $theme->exists() ) {
				return Onecom_Abilities_Helper::fail( __( 'Theme not found.', 'onecom-wp' ), 'onecom_theme_not_found' );
			}
			return Onecom_Abilities_Helper::ok( array( 'theme' => self::format_theme( $theme ) ) );
		}

		/**
		 * onecom/install-theme — WordPress.org directory only.
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function install_theme( array $input ): array {
			self::load_admin_dependencies();

			$slug = sanitize_key( (string) ( $input['slug'] ?? '' ) );
			if ( '' === $slug ) {
				return Onecom_Abilities_Helper::fail( __( 'A theme slug is required.', 'onecom-wp' ), 'onecom_slug_required' );
			}

			// Resolve the package through the WordPress.org API — the only
			// accepted install source for this ability.
			$api = themes_api(
				'theme_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);
			if ( is_wp_error( $api ) ) {
				return Onecom_Abilities_Helper::fail( $api->get_error_message(), $api->get_error_code() );
			}
			if ( empty( $api->download_link ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Could not resolve a download for this theme.', 'onecom-wp' ), 'onecom_no_package' );
			}

			if ( ! WP_Filesystem() ) {
				return Onecom_Abilities_Helper::fail( __( 'The WordPress filesystem is not writable.', 'onecom-wp' ), 'onecom_fs_unavailable' );
			}

			$upgrader = new \Theme_Upgrader( new \Automatic_Upgrader_Skin() );
			$result   = $upgrader->install( esc_url_raw( (string) $api->download_link ) );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}
			if ( true !== $result ) {
				return Onecom_Abilities_Helper::fail( __( 'Theme installation failed.', 'onecom-wp' ), 'onecom_install_failed' );
			}

			$stylesheet = (string) $upgrader->theme_info()->get_stylesheet();
			$activated  = false;

			if ( ! empty( $input['activate'] ) && '' !== $stylesheet ) {
				if ( ! current_user_can( 'switch_themes' ) ) {
					return Onecom_Abilities_Helper::fail( __( 'Installed, but you are not allowed to switch themes.', 'onecom-wp' ), 'onecom_cannot_switch' );
				}
				switch_theme( $stylesheet );
				$activated = true;
			}

			return Onecom_Abilities_Helper::ok(
				array(
					'stylesheet' => $stylesheet,
					'activated'  => $activated,
				)
			);
		}

		/**
		 * onecom/switch-theme
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function switch_theme( array $input ): array {
			$stylesheet = sanitize_text_field( (string) ( $input['stylesheet'] ?? '' ) );
			$theme      = wp_get_theme( $stylesheet );
			if ( '' === $stylesheet || ! $theme->exists() ) {
				return Onecom_Abilities_Helper::fail( __( 'Theme not found.', 'onecom-wp' ), 'onecom_theme_not_found' );
			}
			if ( $theme->errors() ) {
				return Onecom_Abilities_Helper::fail( $theme->errors()->get_error_message(), 'onecom_theme_broken' );
			}
			if ( get_stylesheet() === $theme->get_stylesheet() ) {
				return Onecom_Abilities_Helper::ok( array( 'stylesheet' => $stylesheet ) );
			}

			switch_theme( $theme->get_stylesheet() );

			return Onecom_Abilities_Helper::ok( array( 'stylesheet' => get_stylesheet() ) );
		}

		/**
		 * onecom/update-theme
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function update_theme( array $input ): array {
			self::load_admin_dependencies();

			$stylesheet = sanitize_text_field( (string) ( $input['stylesheet'] ?? '' ) );
			if ( '' === $stylesheet || ! wp_get_theme( $stylesheet )->exists() ) {
				return Onecom_Abilities_Helper::fail( __( 'Theme not found.', 'onecom-wp' ), 'onecom_theme_not_found' );
			}

			$updates = get_site_transient( 'update_themes' );
			if ( ! isset( $updates->response[ $stylesheet ] ) ) {
				// A prior update in the same session clears the update_themes
				// transient (wp_clean_update_cache on upgrader_process_complete),
				// which would make a second theme look up-to-date. Rebuild the
				// update list and re-check before reporting "no update".
				wp_update_themes();
				$updates = get_site_transient( 'update_themes' );
				if ( ! isset( $updates->response[ $stylesheet ] ) ) {
					return Onecom_Abilities_Helper::fail( __( 'No update is available for this theme.', 'onecom-wp' ), 'onecom_no_update' );
				}
			}

			if ( ! WP_Filesystem() ) {
				return Onecom_Abilities_Helper::fail( __( 'The WordPress filesystem is not writable.', 'onecom-wp' ), 'onecom_fs_unavailable' );
			}

			$upgrader = new \Theme_Upgrader( new \Automatic_Upgrader_Skin() );
			$result   = $upgrader->upgrade( $stylesheet );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}
			if ( true !== $result ) {
				return Onecom_Abilities_Helper::fail( __( 'Theme update failed.', 'onecom-wp' ), 'onecom_update_failed' );
			}

			return Onecom_Abilities_Helper::ok(
				array( 'new_version' => (string) wp_get_theme( $stylesheet )->get( 'Version' ) )
			);
		}
	}
}
