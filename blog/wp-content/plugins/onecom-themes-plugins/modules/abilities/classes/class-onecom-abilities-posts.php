<?php
/**
 * Abilities: posts & pages.
 *
 * One set of CRUD abilities covering both posts and pages via a `post_type`
 * input (restricted to the built-in public types), instead of parallel
 * per-type ability sets — fewer, clearer tools for MCP clients.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Abilities_Posts' ) ) {

	class Onecom_Abilities_Posts {

		/**
		 * Ability slugs this class registers.
		 *
		 * @return string[]
		 */
		public static function ability_ids(): array {
			return array(
				'onecom/find-posts',
				'onecom/get-post',
				'onecom/create-post',
				'onecom/update-post',
				'onecom/delete-post',
			);
		}

		/**
		 * Post types these abilities may touch.
		 *
		 * @return string[]
		 */
		public static function allowed_post_types(): array {
			return (array) apply_filters( 'onecom_abilities_post_types', array( 'post', 'page' ) );
		}

		/**
		 * Resolve and validate the post_type input.
		 *
		 * @param array  $input   Ability input.
		 * @param string $default Default type.
		 * @return string|\WP_Error
		 */
		public static function resolve_post_type( array $input, string $default = 'post' ) {
			$type = isset( $input['post_type'] ) ? sanitize_key( (string) $input['post_type'] ) : $default;
			if ( ! in_array( $type, self::allowed_post_types(), true ) || ! post_type_exists( $type ) ) {
				return new \WP_Error(
					'onecom_invalid_post_type',
					sprintf(
						/* translators: %s: post type slug */
						__( 'Unsupported post type: %s', 'onecom-wp' ),
						$type
					)
				);
			}
			return $type;
		}

		/**
		 * Register every ability of this domain.
		 */
		public static function register(): void {
			$post_type_property = array(
				'type'        => 'string',
				'enum'        => self::allowed_post_types(),
				'description' => __( 'Content type to operate on. Defaults to "post".', 'onecom-wp' ),
			);

			wp_register_ability(
				'onecom/find-posts',
				array(
					'label'               => __( 'Find posts', 'onecom-wp' ),
					'description'         => __( 'Find posts or pages with optional status filter, search and pagination.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'post_type' => $post_type_property,
							'status'    => array(
								'type'        => 'string',
								'enum'        => array( 'publish', 'draft', 'pending', 'private', 'future', 'any' ),
								'description' => __( 'Filter by post status. Defaults to "any" readable status.', 'onecom-wp' ),
							),
							'search'    => array( 'type' => 'string' ),
							'page'      => array( 'type' => 'integer', 'minimum' => 1 ),
							'per_page'  => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 100 ),
						),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array(
							'posts' => array( 'type' => 'array' ),
							'total' => array( 'type' => 'integer' ),
							'pages' => array( 'type' => 'integer' ),
						)
					),
					'permission_callback' => static fn() => Onecom_Abilities_Helper::can( 'edit_posts' ),
					'execute_callback'    => array( self::class, 'find_posts' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/get-post',
				array(
					'label'               => __( 'Get post', 'onecom-wp' ),
					'description'         => __( 'Get a single post or page by id, including its content.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'post_id' => array( 'type' => 'integer', 'description' => __( 'Post or page id.', 'onecom-wp' ) ),
						),
						'required'   => array( 'post_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'post' => array( 'type' => 'object' ) )
					),
					'permission_callback' => static function ( $input = array() ) {
						$post_id = absint( is_array( $input ) ? ( $input['post_id'] ?? 0 ) : 0 );
						$post    = $post_id ? get_post( $post_id ) : null;
						if ( $post instanceof \WP_Post && 'publish' !== $post->post_status ) {
							return Onecom_Abilities_Helper::can( 'edit_post', $post_id );
						}
						return Onecom_Abilities_Helper::can( 'read' );
					},
					'execute_callback'    => array( self::class, 'get_post' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) ),
				)
			);

			wp_register_ability(
				'onecom/create-post',
				array(
					'label'               => __( 'Create post', 'onecom-wp' ),
					'description'         => __( 'Create a post or page. Content accepts block markup or HTML. Created as draft unless a status is given.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'post_type' => $post_type_property,
							'title'     => array( 'type' => 'string' ),
							'content'   => array( 'type' => 'string' ),
							'status'    => array(
								'type' => 'string',
								'enum' => array( 'draft', 'publish', 'pending', 'private' ),
							),
							'excerpt'   => array( 'type' => 'string' ),
							'slug'      => array( 'type' => 'string' ),
							'parent_id' => array( 'type' => 'integer', 'description' => __( 'Parent id (pages only).', 'onecom-wp' ) ),
						),
						'required'   => array( 'title' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'post' => array( 'type' => 'object' ) )
					),
					'permission_callback' => static function ( $input = array() ) {
						$type = self::resolve_post_type( is_array( $input ) ? $input : array() );
						if ( is_wp_error( $type ) ) {
							return $type;
						}
						$pto = get_post_type_object( $type );
						return Onecom_Abilities_Helper::can( $pto->cap->create_posts ?? 'edit_posts' );
					},
					'execute_callback'    => array( self::class, 'create_post' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/update-post',
				array(
					'label'               => __( 'Update post', 'onecom-wp' ),
					'description'         => __( 'Update fields of an existing post or page. Only the provided fields change.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'post_id'   => array( 'type' => 'integer' ),
							'title'     => array( 'type' => 'string' ),
							'content'   => array( 'type' => 'string' ),
							'status'    => array(
								'type' => 'string',
								'enum' => array( 'draft', 'publish', 'pending', 'private' ),
							),
							'excerpt'   => array( 'type' => 'string' ),
							'slug'      => array( 'type' => 'string' ),
							'parent_id' => array( 'type' => 'integer' ),
						),
						'required'   => array( 'post_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'post' => array( 'type' => 'object' ) )
					),
					'permission_callback' => static function ( $input = array() ) {
						$post_id = absint( is_array( $input ) ? ( $input['post_id'] ?? 0 ) : 0 );
						return Onecom_Abilities_Helper::can( 'edit_post', $post_id );
					},
					'execute_callback'    => array( self::class, 'update_post' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta(),
				)
			);

			wp_register_ability(
				'onecom/delete-post',
				array(
					'label'               => __( 'Delete post', 'onecom-wp' ),
					'description'         => __( 'Move a post or page to trash. Set force=true to delete permanently.', 'onecom-wp' ),
					'category'            => Onecom_Abilities_Helper::CATEGORY,
					'input_schema'        => array(
						'type'       => 'object',
						'properties' => array(
							'post_id' => array( 'type' => 'integer' ),
							'force'   => array( 'type' => 'boolean', 'description' => __( 'Skip trash and delete permanently.', 'onecom-wp' ) ),
						),
						'required'   => array( 'post_id' ),
					),
					'output_schema'       => Onecom_Abilities_Helper::output_schema(
						array( 'deleted' => array( 'type' => 'boolean' ), 'trashed' => array( 'type' => 'boolean' ) )
					),
					'permission_callback' => static function ( $input = array() ) {
						$post_id = absint( is_array( $input ) ? ( $input['post_id'] ?? 0 ) : 0 );
						return Onecom_Abilities_Helper::can( 'delete_post', $post_id );
					},
					'execute_callback'    => array( self::class, 'delete_post' ),
					'meta'                => Onecom_Abilities_Helper::tool_meta( array( 'destructive' => true ) ),
				)
			);
		}

		/**
		 * Compact API shape for a post.
		 *
		 * @param \WP_Post $post            Post.
		 * @param bool     $include_content Include full content.
		 * @return array
		 */
		public static function format_post( \WP_Post $post, bool $include_content = false ): array {
			$data = array(
				'id'        => (int) $post->ID,
				'type'      => $post->post_type,
				'title'     => $post->post_title,
				'slug'      => $post->post_name,
				'status'    => $post->post_status,
				'excerpt'   => $post->post_excerpt,
				'parent_id' => (int) $post->post_parent,
				'author_id' => (int) $post->post_author,
				'date'      => $post->post_date_gmt,
				'modified'  => $post->post_modified_gmt,
				'url'       => get_permalink( $post ),
			);
			if ( $include_content ) {
				$data['content'] = $post->post_content;
			}
			return $data;
		}

		/**
		 * Filter content according to the current user's capabilities.
		 *
		 * @param string $content Raw content.
		 * @return string
		 */
		private static function sanitize_content( string $content ): string {
			return current_user_can( 'unfiltered_html' ) ? $content : wp_kses_post( $content );
		}

		/**
		 * onecom/find-posts
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function find_posts( array $input ): array {
			$type = self::resolve_post_type( $input );
			if ( is_wp_error( $type ) ) {
				return Onecom_Abilities_Helper::fail( $type->get_error_message(), $type->get_error_code() );
			}

			$status   = isset( $input['status'] ) ? sanitize_key( (string) $input['status'] ) : 'any';
			$per_page = isset( $input['per_page'] ) ? min( 100, max( 1, absint( $input['per_page'] ) ) ) : 20;
			$page     = isset( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;

			$query = new \WP_Query(
				array(
					'post_type'      => $type,
					'post_status'    => 'any' === $status ? array( 'publish', 'draft', 'pending', 'private', 'future' ) : $status,
					'perm'           => 'readable',
					's'              => isset( $input['search'] ) ? sanitize_text_field( (string) $input['search'] ) : '',
					'posts_per_page' => $per_page,
					'paged'          => $page,
					'orderby'        => 'modified',
					'order'          => 'DESC',
					'no_found_rows'  => false,
				)
			);

			return Onecom_Abilities_Helper::ok(
				array(
					'posts' => array_map(
						static fn( $post ) => self::format_post( $post ),
						$query->posts
					),
					'total' => (int) $query->found_posts,
					'pages' => (int) $query->max_num_pages,
				)
			);
		}

		/**
		 * onecom/get-post
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function get_post( array $input ): array {
			$post = get_post( absint( $input['post_id'] ?? 0 ) );
			if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, self::allowed_post_types(), true ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Post not found.', 'onecom-wp' ), 'onecom_post_not_found' );
			}
			return Onecom_Abilities_Helper::ok( array( 'post' => self::format_post( $post, true ) ) );
		}

		/**
		 * onecom/create-post
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function create_post( array $input ): array {
			$type = self::resolve_post_type( $input );
			if ( is_wp_error( $type ) ) {
				return Onecom_Abilities_Helper::fail( $type->get_error_message(), $type->get_error_code() );
			}

			$status = isset( $input['status'] ) ? sanitize_key( (string) $input['status'] ) : 'draft';
			$pto    = get_post_type_object( $type );
			if ( in_array( $status, array( 'publish', 'private' ), true )
				&& ! current_user_can( $pto->cap->publish_posts ?? 'publish_posts' ) ) {
				return Onecom_Abilities_Helper::fail(
					__( 'You are not allowed to publish this content — create it as a draft instead.', 'onecom-wp' ),
					'onecom_cannot_publish'
				);
			}

			$postarr = array(
				'post_type'    => $type,
				'post_title'   => sanitize_text_field( (string) ( $input['title'] ?? '' ) ),
				'post_content' => self::sanitize_content( (string) ( $input['content'] ?? '' ) ),
				'post_status'  => $status,
				'post_excerpt' => sanitize_text_field( (string) ( $input['excerpt'] ?? '' ) ),
			);
			if ( ! empty( $input['slug'] ) ) {
				$postarr['post_name'] = sanitize_title( (string) $input['slug'] );
			}
			if ( ! empty( $input['parent_id'] ) && is_post_type_hierarchical( $type ) ) {
				$postarr['post_parent'] = absint( $input['parent_id'] );
			}

			$post_id = wp_insert_post( $postarr, true );
			if ( is_wp_error( $post_id ) ) {
				return Onecom_Abilities_Helper::fail( $post_id->get_error_message(), $post_id->get_error_code() );
			}

			return Onecom_Abilities_Helper::ok( array( 'post' => self::format_post( get_post( $post_id ) ) ) );
		}

		/**
		 * onecom/update-post
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function update_post( array $input ): array {
			$post = get_post( absint( $input['post_id'] ?? 0 ) );
			if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, self::allowed_post_types(), true ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Post not found.', 'onecom-wp' ), 'onecom_post_not_found' );
			}

			$update = array( 'ID' => $post->ID );
			if ( array_key_exists( 'title', $input ) ) {
				$update['post_title'] = sanitize_text_field( (string) $input['title'] );
			}
			if ( array_key_exists( 'content', $input ) ) {
				$update['post_content'] = self::sanitize_content( (string) $input['content'] );
			}
			if ( array_key_exists( 'excerpt', $input ) ) {
				$update['post_excerpt'] = sanitize_text_field( (string) $input['excerpt'] );
			}
			if ( ! empty( $input['slug'] ) ) {
				$update['post_name'] = sanitize_title( (string) $input['slug'] );
			}
			if ( array_key_exists( 'parent_id', $input ) && is_post_type_hierarchical( $post->post_type ) ) {
				$update['post_parent'] = absint( $input['parent_id'] );
			}
			if ( ! empty( $input['status'] ) ) {
				$status = sanitize_key( (string) $input['status'] );
				$pto    = get_post_type_object( $post->post_type );
				if ( in_array( $status, array( 'publish', 'private' ), true )
					&& 'publish' !== $post->post_status
					&& ! current_user_can( $pto->cap->publish_posts ?? 'publish_posts' ) ) {
					return Onecom_Abilities_Helper::fail(
						__( 'You are not allowed to publish this content.', 'onecom-wp' ),
						'onecom_cannot_publish'
					);
				}
				$update['post_status'] = $status;
			}

			$result = wp_update_post( $update, true );
			if ( is_wp_error( $result ) ) {
				return Onecom_Abilities_Helper::fail( $result->get_error_message(), $result->get_error_code() );
			}

			return Onecom_Abilities_Helper::ok( array( 'post' => self::format_post( get_post( $post->ID ) ) ) );
		}

		/**
		 * onecom/delete-post
		 *
		 * @param array $input Ability input.
		 * @return array
		 */
		public static function delete_post( array $input ): array {
			$post = get_post( absint( $input['post_id'] ?? 0 ) );
			if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, self::allowed_post_types(), true ) ) {
				return Onecom_Abilities_Helper::fail( __( 'Post not found.', 'onecom-wp' ), 'onecom_post_not_found' );
			}

			$force  = ! empty( $input['force'] );
			$result = $force ? wp_delete_post( $post->ID, true ) : wp_trash_post( $post->ID );
			if ( ! $result ) {
				return Onecom_Abilities_Helper::fail( __( 'Could not delete the post.', 'onecom-wp' ), 'onecom_delete_failed' );
			}

			return Onecom_Abilities_Helper::ok(
				array(
					'deleted' => $force,
					'trashed' => ! $force,
				)
			);
		}
	}
}
