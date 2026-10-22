<?php
/**
 * Abilities: navigation menus.
 *
 * Classic (nav_menu) menus: menu CRUD, item CRUD and theme-location
 * assignment. Reads need `read`; every write is gated on
 * `edit_theme_options`, matching wp-admin's Appearance → Menus screen.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Abilities_Menus' ) ) {

	class Onecom_Abilities_Menus {

		/**
		 * Ability slugs this class registers.
		 *
		 * @return string[]
		 */
		public static function ability_ids(): array {
			return array(
				'onecom/list-menus',
				'onecom/get-menu',
				'onecom/create-menu',
				'onecom/delete-menu',
				'onecom/get-menu-locations',
				'onecom/assign-menu-location',
				'onecom/add-menu-item',
				'onecom/update-menu-item',
				'onecom/remove-menu-item',
			);
		}

		/**
		 * Register every ability of this domain.
		 */
		public static function register(): void {
			$read  = static fn() => Onecom_Abilities_Helper::can( 'read' );
			$write = static fn() => Onecom_Abilities_Helper::can( 'edit_theme_options' );

			wp_register_ability(
				'onecom/list-menus',
				array(
					'label'               => __( 'List menus', 'onecom-wp' ),
					'description'         => __( 'List navigation menus with their assigned theme locations and item counts.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array( 'type' => 'object', 'properties' => new \stdClass() ),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'menus' => array( 'type' => 'array' ) )
					),
					'permission_callback' => $read,
					'execute_callback'    => array( self::class, 'list_menus' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/get-menu',
				array(
					'label'               => __( 'Get menu', 'onecom-wp' ),
					'description'         => __( 'Get a navigation menu and its items (hierarchy via parent_item_id).', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'menu_id' => array( 'type' => 'integer' ),
						),
						'required'   => array( 'menu_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array(
							'menu'  => array( 'type' => 'object' ),
							'items' => array( 'type' => 'array' ),
						)
					),
					'permission_callback' => $read,
					'execute_callback'    => array( self::class, 'get_menu' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/create-menu',
				array(
					'label'               => __( 'Create menu', 'onecom-wp' ),
					'description'         => __( 'Create an empty navigation menu.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'name' => array( 'type' => 'string' ),
						),
						'required'   => array( 'name' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'menu' => array( 'type' => 'object' ) )
					),
					'permission_callback' => $write,
					'execute_callback'    => array( self::class, 'create_menu' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/delete-menu',
				array(
					'label'               => __( 'Delete menu', 'onecom-wp' ),
					'description'         => __( 'Delete a navigation menu and all of its items.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'menu_id' => array( 'type' => 'integer' ),
						),
						'required'   => array( 'menu_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'deleted' => array( 'type' => 'boolean' ) )
					),
					'permission_callback' => $write,
					'execute_callback'    => array( self::class, 'delete_menu' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/get-menu-locations',
				array(
					'label'               => __( 'Get menu locations', 'onecom-wp' ),
					'description'         => __( 'List the theme\'s registered menu locations and which menu each one shows.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array( 'type' => 'object', 'properties' => new \stdClass() ),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'locations' => array( 'type' => 'array' ) )
					),
					'permission_callback' => $read,
					'execute_callback'    => array( self::class, 'get_menu_locations' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/assign-menu-location',
				array(
					'label'               => __( 'Assign menu location', 'onecom-wp' ),
					'description'         => __( 'Show a menu at a theme location, or clear the location by passing menu_id 0.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'menu_id'  => array( 'type' => 'integer', 'description' => __( '0 clears the location.', 'onecom-wp' ) ),
							'location' => array( 'type' => 'string' ),
						),
						'required'   => array( 'menu_id', 'location' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(),
					'permission_callback' => $write,
					'execute_callback'    => array( self::class, 'assign_menu_location' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/add-menu-item',
				array(
					'label'               => __( 'Add menu item', 'onecom-wp' ),
					'description'         => __( 'Add an item to a menu: either a custom link (title + url) or a link to existing content (post_id).', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'menu_id'        => array( 'type' => 'integer' ),
							'title'          => array( 'type' => 'string' ),
							'url'            => array( 'type' => 'string', 'description' => __( 'For custom link items.', 'onecom-wp' ) ),
							'post_id'        => array( 'type' => 'integer', 'description' => __( 'Link to this post/page instead of a custom url.', 'onecom-wp' ) ),
							'parent_item_id' => array( 'type' => 'integer' ),
							'position'       => array( 'type' => 'integer', 'minimum' => 1 ),
						),
						'required'   => array( 'menu_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'item' => array( 'type' => 'object' ) )
					),
					'permission_callback' => $write,
					'execute_callback'    => array( self::class, 'add_menu_item' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/update-menu-item',
				array(
					'label'               => __( 'Update menu item', 'onecom-wp' ),
					'description'         => __( 'Update a menu item\'s title, url, parent or position. Only the provided fields change.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'item_id'        => array( 'type' => 'integer' ),
							'title'          => array( 'type' => 'string' ),
							'url'            => array( 'type' => 'string' ),
							'parent_item_id' => array( 'type' => 'integer', 'description' => __( '0 makes it top-level.', 'onecom-wp' ) ),
							'position'       => array( 'type' => 'integer', 'minimum' => 1 ),
						),
						'required'   => array( 'item_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'item' => array( 'type' => 'object' ) )
					),
					'permission_callback' => $write,
					'execute_callback'    => array( self::class, 'update_menu_item' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/remove-menu-item',
				array(
					'label'               => __( 'Remove menu item', 'onecom-wp' ),
					'description'         => __( 'Remove a single item from a menu. The linked content is not deleted.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'item_id' => array( 'type' => 'integer' ),
						),
						'required'   => array( 'item_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'removed' => array( 'type' => 'boolean' ) )
					),
					'permission_callback' => $write,
					'execute_callback'    => array( self::class, 'remove_menu_item' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);
		}

		/**
		 * Compact API shape for a menu.
		 *
		 * @param \WP_Term $menu Menu term.
		 * @return array
		 */
		public static function format_menu( \WP_Term $menu ): array {
			$locations = array_keys( array_filter( get_nav_menu_locations(), static fn( $id ) => (int) $id === (int) $menu->term_id ) );
			return array(
				'id'        => (int) $menu->term_id,
				'name'      => $menu->name,
				'slug'      => $menu->slug,
				'count'     => (int) $menu->count,
				'locations' => $locations,
			);
		}

		/**
		 * Compact API shape for a menu item.
		 *
		 * @param object $item Item as returned by wp_get_nav_menu_items() / wp_setup_nav_menu_item().
		 * @return array
		 */
		public static function format_item( $item ): array {
			return array(
				'id'             => (int) $item->ID,
				'title'          => $item->title,
				'url'            => $item->url,
				'type'           => $item->type,
				'object'         => $item->object,
				'object_id'      => (int) $item->object_id,
				'parent_item_id' => (int) $item->menu_item_parent,
				'position'       => (int) $item->menu_order,
			);
		}

		/**
		 * Load a validated nav_menu_item post.
		 *
		 * @param int $item_id Menu item id.
		 * @return \WP_Post|null
		 */
		private static function get_item_post( int $item_id ): ?\WP_Post {
			$post = get_post( $item_id );
			return ( $post instanceof \WP_Post && 'nav_menu_item' === $post->post_type ) ? $post : null;
		}

		/**
		 * onecom/list-menus
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function list_menus( array $input ): array {
			$menus = wp_get_nav_menus();
			if ( is_wp_error( $menus ) ) {
				return Onecom_Abilities_Helper::fail( $menus->get_error_message(), $menus->get_error_code() );
			}
			return Onecom_Abilities_Helper::ok(
				array( 'menus' => array_map( array( self::class, 'format_menu' ), $menus ) )
			);
		}

		/**
		 * onecom/get-menu
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function get_menu( array $input ): array {
			$menu = wp_get_nav_menu_object( absint( $input['menu_id'] ?? 0 ) );
			if ( ! $menu ) {
				return Onecom_Abilities_Helper::fail( __( 'Menu not found.', 'onecom-wp' ), 'onecom_menu_not_found' );
			}
			$items = wp_get_nav_menu_items( $menu->term_id ) ?: array();
			return Onecom_Abilities_Helper::ok(
				array(
					'menu'  => self::format_menu( $menu ),
					'items' => array_map( array( self::class, 'format_item' ), $items ),
				)
			);
		}

		/**
		 * onecom/create-menu
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function create_menu( array $input ): array {
			$name = sanitize_text_field( (string) ( $input['name'] ?? '' ) );
			if ( '' === $name ) {
				return Onecom_Abilities_Helper::fail( __( 'A menu name is required.', 'onecom-wp' ), 'onecom_menu_name_required' );
			}
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				return Onecom_Abilities_Helper::fail( $menu_id->get_error_message(), $menu_id->get_error_code() );
			}
			return Onecom_Abilities_Helper::ok(
				array( 'menu' => self::format_menu( wp_get_nav_menu_object( $menu_id ) ) )
			);
		}

		/**
		 * onecom/delete-menu
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function delete_menu( array $input ): array {
			$menu = wp_get_nav_menu_object( absint( $input['menu_id'] ?? 0 ) );
			if ( ! $menu ) {
				return Onecom_Abilities_Helper::fail( __( 'Menu not found.', 'onecom-wp' ), 'onecom_menu_not_found' );
			}
			$result = wp_delete_nav_menu( $menu->term_id );
			if ( is_wp_error( $result ) || ! $result ) {
				return Onecom_Abilities_Helper::fail( __( 'Could not delete the menu.', 'onecom-wp' ), 'onecom_menu_delete_failed' );
			}
			return Onecom_Abilities_Helper::ok( array( 'deleted' => true ) );
		}

		/**
		 * onecom/get-menu-locations
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function get_menu_locations( array $input ): array {
			$registered = get_registered_nav_menus();
			$assigned   = get_nav_menu_locations();

			$locations = array();
			foreach ( $registered as $slug => $label ) {
				$menu_id     = isset( $assigned[ $slug ] ) ? (int) $assigned[ $slug ] : 0;
				$menu        = $menu_id ? wp_get_nav_menu_object( $menu_id ) : false;
				$locations[] = array(
					'location'  => $slug,
					'label'     => $label,
					'menu_id'   => $menu ? (int) $menu->term_id : 0,
					'menu_name' => $menu ? $menu->name : '',
				);
			}

			return Onecom_Abilities_Helper::ok( array( 'locations' => $locations ) );
		}

		/**
		 * onecom/assign-menu-location
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function assign_menu_location( array $input ): array {
			$location = sanitize_key( (string) ( $input['location'] ?? '' ) );
			$menu_id  = absint( $input['menu_id'] ?? 0 );

			if ( ! array_key_exists( $location, get_registered_nav_menus() ) ) {
				return Onecom_Abilities_Helper::fail(
					__( 'The theme does not register this menu location.', 'onecom-wp' ),
					'onecom_unknown_menu_location'
				);
			}
			if ( $menu_id && ! wp_get_nav_menu_object( $menu_id ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Menu not found.', 'onecom-wp' ), 'onecom_menu_not_found' );
			}

			$locations = get_nav_menu_locations();
			if ( $menu_id ) {
				$locations[ $location ] = $menu_id;
			} else {
				unset( $locations[ $location ] );
			}
			set_theme_mod( 'nav_menu_locations', $locations );

			return Onecom_Abilities_Helper::ok();
		}

		/**
		 * onecom/add-menu-item
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function add_menu_item( array $input ): array {
			$menu = wp_get_nav_menu_object( absint( $input['menu_id'] ?? 0 ) );
			if ( ! $menu ) {
				return Onecom_Abilities_Helper::fail( __( 'Menu not found.', 'onecom-wp' ), 'onecom_menu_not_found' );
			}

			$post_id = absint( $input['post_id'] ?? 0 );
			$url     = isset( $input['url'] ) ? esc_url_raw( (string) $input['url'] ) : '';
			$title   = sanitize_text_field( (string) ( $input['title'] ?? '' ) );

			$item_data = array(
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => absint( $input['parent_item_id'] ?? 0 ),
				'menu-item-position'  => absint( $input['position'] ?? 0 ),
			);

			if ( $post_id ) {
				$target = get_post( $post_id );
				if ( ! $target instanceof \WP_Post ) {
					return Onecom_Abilities_Helper::fail( __( 'Linked content not found.', 'onecom-wp' ), 'onecom_post_not_found' );
				}
				$item_data['menu-item-type']      = 'post_type';
				$item_data['menu-item-object']    = $target->post_type;
				$item_data['menu-item-object-id'] = $target->ID;
				if ( '' !== $title ) {
					$item_data['menu-item-title'] = $title;
				}
			} elseif ( '' !== $url && '' !== $title ) {
				$item_data['menu-item-type']  = 'custom';
				$item_data['menu-item-url']   = $url;
				$item_data['menu-item-title'] = $title;
			} else {
				return Onecom_Abilities_Helper::fail(
					__( 'Provide either post_id, or both title and url for a custom link.', 'onecom-wp' ),
					'onecom_invalid_menu_item'
				);
			}

			$item_id = wp_update_nav_menu_item( $menu->term_id, 0, $item_data );
			if ( is_wp_error( $item_id ) ) {
				return Onecom_Abilities_Helper::fail( $item_id->get_error_message(), $item_id->get_error_code() );
			}

			return Onecom_Abilities_Helper::ok(
				array( 'item' => self::format_item( wp_setup_nav_menu_item( get_post( $item_id ) ) ) )
			);
		}

		/**
		 * onecom/update-menu-item
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function update_menu_item( array $input ): array {
			$item_post = self::get_item_post( absint( $input['item_id'] ?? 0 ) );
			if ( ! $item_post ) {
				return Onecom_Abilities_Helper::fail( __( 'Menu item not found.', 'onecom-wp' ), 'onecom_menu_item_not_found' );
			}

			$menus   = wp_get_object_terms( $item_post->ID, 'nav_menu' );
			$menu_id = ( ! is_wp_error( $menus ) && ! empty( $menus ) ) ? (int) $menus[0]->term_id : 0;
			$item    = wp_setup_nav_menu_item( $item_post );

			$item_data = array(
				'menu-item-status'    => 'publish',
				'menu-item-type'      => $item->type,
				'menu-item-object'    => $item->object,
				'menu-item-object-id' => $item->object_id,
				'menu-item-title'     => array_key_exists( 'title', $input )
					? sanitize_text_field( (string) $input['title'] )
					: $item->title,
				'menu-item-url'       => array_key_exists( 'url', $input )
					? esc_url_raw( (string) $input['url'] )
					: $item->url,
				'menu-item-parent-id' => array_key_exists( 'parent_item_id', $input )
					? absint( $input['parent_item_id'] )
					: (int) $item->menu_item_parent,
				'menu-item-position'  => array_key_exists( 'position', $input )
					? absint( $input['position'] )
					: (int) $item->menu_order,
			);

			$result = wp_update_nav_menu_item( $menu_id, $item_post->ID, $item_data );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}

			return Onecom_Abilities_Helper::ok(
				array( 'item' => self::format_item( wp_setup_nav_menu_item( get_post( $item_post->ID ) ) ) )
			);
		}

		/**
		 * onecom/remove-menu-item
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function remove_menu_item( array $input ): array {
			$item_post = self::get_item_post( absint( $input['item_id'] ?? 0 ) );
			if ( ! $item_post ) {
				return Onecom_Abilities_Helper::fail( __( 'Menu item not found.', 'onecom-wp' ), 'onecom_menu_item_not_found' );
			}
			if ( ! wp_delete_post( $item_post->ID, true ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Could not remove the menu item.', 'onecom-wp' ), 'onecom_menu_item_delete_failed' );
			}
			return Onecom_Abilities_Helper::ok( array( 'removed' => true ) );
		}
	}
}
