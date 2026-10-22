<?php
/**
 * Shared plumbing for the one.com site abilities.
 *
 * @package onecom-themes-plugins
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Onecom_Abilities_Helper' ) ) {

	class Onecom_Abilities_Helper {

		/** Ability category slug shared by every ability in this module. */
		const CATEGORY = 'onecom';

		/**
		 * Register the shared ability category. Safe to call repeatedly.
		 */
		public static function register_category(): void {
			if ( ! function_exists( 'wp_register_ability_category' ) ) {
				return;
			}
			if ( function_exists( 'wp_has_ability_category' ) && wp_has_ability_category( self::CATEGORY ) ) {
				return;
			}
			wp_register_ability_category(
				self::CATEGORY,
				array(
					'label'       => __( 'one.com site management', 'onecom-wp' ),
					'description' => __( 'First-party abilities for managing this WordPress site: posts and pages, navigation menus, plugins and themes.', 'onecom-wp' ),
				)
			);
		}

		/**
		 * Capability gate used by permission callbacks.
		 *
		 * @param string   $capability Capability to check.
		 * @param int|null $object_id  Optional object id for meta capabilities
		 *                             (edit_post, delete_post, …).
		 * @return true|\WP_Error
		 */
		public static function can( string $capability, ?int $object_id = null ) {
			if ( ! is_user_logged_in() ) {
				return new \WP_Error(
					'onecom_not_authenticated',
					__( 'Authentication is required.', 'onecom-wp' )
				);
			}

			$allowed = null === $object_id
				? current_user_can( $capability ) // phpcs:ignore WordPress.WP.Capabilities.Undetermined
				: current_user_can( $capability, $object_id ); // phpcs:ignore WordPress.WP.Capabilities.Undetermined

			if ( ! $allowed ) {
				return new \WP_Error(
					'onecom_insufficient_capability',
					sprintf(
						/* translators: %s: WordPress capability name */
						__( 'Missing required capability: %s', 'onecom-wp' ),
						$capability
					)
				);
			}

			return true;
		}

		/**
		 * Meta for an ability exposed as an MCP tool.
		 *
		 * Intentionally does NOT set `mcp.public`: these abilities are exposed
		 * only on the curated one.com MCP server (inc/class-onecom-mcp.php),
		 * never auto-discovered onto the adapter's default server.
		 *
		 * @param array $annotations Optional tool annotations (e.g. ['readonly' => true],
		 *                           ['destructive' => true]).
		 * @return array
		 */
		public static function tool_meta( array $annotations = array() ): array {
			$meta = array(
				'mcp' => array( 'type' => 'tool' ),
			);
			if ( ! empty( $annotations ) ) {
				$meta['annotations'] = $annotations;
			}
			return $meta;
		}

		/**
		 * Successful ability result.
		 *
		 * @param array $data Extra top-level fields.
		 * @return array
		 */
		public static function ok( array $data = array() ): array {
			return array_merge( array( 'success' => true ), $data );
		}

		/**
		 * Failed ability result.
		 *
		 * @param string $message Human-readable error.
		 * @param string $code    Optional machine-readable code.
		 * @return array
		 */
		public static function fail( string $message, string $code = '' ): array {
			$result = array(
				'success' => false,
				'error'   => $message,
			);
			if ( '' !== $code ) {
				$result['code'] = $code;
			}
			return $result;
		}

		/**
		 * Output schema shared by every ability: success/error plus
		 * ability-specific fields.
		 *
		 * @param array $properties Extra top-level properties.
		 * @return array
		 */
		public static function output_schema( array $properties = array() ): array {
			return array(
				'type'       => 'object',
				'properties' => array_merge(
					array(
						'success' => array( 'type' => 'boolean' ),
						'error'   => array( 'type' => 'string' ),
						'code'    => array( 'type' => 'string' ),
					),
					$properties
				),
			);
		}
	}
}
