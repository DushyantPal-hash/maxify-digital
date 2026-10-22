<?php
defined( 'ABSPATH' ) or die( 'Cheating Huh!' );

if ( ! class_exists( 'GroupOne_Imagify_Promoter' ) ) {
	class GroupOne_Imagify_Promoter {

		const DISMISS_OPTION = 'plugin_family_dismiss_promote_imagify';

		private static $competing_plugins = array(
			'wp-smushit/wp-smush.php',
			'wp-smush-pro/wp-smush.php',
			'kraken-image-optimizer/kraken.php',
			'tiny-compress-images/tiny-compress-images.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php',
			'imagerecycle-pdf-image-compression/wp-image-recycle.php',
			'imagify/imagify.php',
		);

		public function __construct() {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_assets' ) );
			add_action( 'wp_ajax_onecom_dismiss_imagify_promoter', array( $this, 'handle_dismiss' ) );
		}

		private function should_show(): bool {
			// Feature toggle: off = hide notice everywhere (default 1 = on).
			if ( ! get_site_option( 'onecom_imagify_promoter_enabled', 1 ) ) {
				return false;
			}

			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( self::$competing_plugins as $plugin_file ) {
				if ( is_plugin_active( $plugin_file ) ) {
					return false;
				}
			}

			if ( ! empty( get_option( self::DISMISS_OPTION ) ) ) {
				return false;
			}

			return true;
		}

		private function get_inspector_group(): string {
			$wp_ver = $GLOBALS['wp_version'];
			if ( version_compare( $wp_ver, '7.0', '>=' ) ) {
				return 'content';
			}
			if ( version_compare( $wp_ver, '6.3', '>=' ) ) {
				return 'list';
			}
			return 'default';
		}

		private function get_inline_data(): string {
			return 'window.onecomImagifyPromoter = ' . wp_json_encode( array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'onecom-imagify-promoter' ),
				'plugins_page'    => admin_url('admin.php?page=' . MARKETPLACE_PAGE_SLUG . '&plugin=imagify'),
				'title'           => __( 'Optimize your images', 'onecom-wp' ),
				'description'     => __( 'Did you know you can compress and optimize your images automatically? We recommend Imagify — a free, easy-to-use image optimization plugin.', 'onecom-wp' ),
				'cta_label'       => __( 'Try Imagify for free', 'onecom-wp' ),
				'dismiss_label'   => __( 'No thanks', 'onecom-wp' ),
				'inspector_group' => $this->get_inspector_group(),
			) ) . ';';
		}

		public function enqueue_editor_assets(): void {
			if ( ! $this->should_show() ) {
				return;
			}

			$asset_ver = GROUPONE_WP_VERSION . '-20260624';

			wp_enqueue_script(
				'onecom-imagify-promoter-editor',
				GROUPONE_WP_URL . 'assets/js/imagify-promoter-editor.js',
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-hooks', 'wp-compose' ),
				$asset_ver,
				true
			);

			wp_enqueue_style(
				'onecom-imagify-promoter',
				GROUPONE_WP_URL . 'assets/css/imagify-promoter.css',
				array(),
				$asset_ver
			);

			wp_add_inline_script( 'onecom-imagify-promoter-editor', $this->get_inline_data(), 'before' );
		}

		/**
		 * @param string|null $hook Current admin page hook. Null when a third party
		 *                          (e.g. Visual Composer's frontend editor) fires
		 *                          admin_enqueue_scripts without arguments.
		 */
		public function enqueue_media_assets( ?string $hook = null ): void {
			if ( ! in_array( $hook, array( 'upload.php', 'media-new.php' ), true ) ) {
				return;
			}

			if ( ! $this->should_show() ) {
				return;
			}

			$asset_ver = GROUPONE_WP_VERSION . '-20260624';

			wp_enqueue_script(
				'onecom-imagify-promoter-media',
				GROUPONE_WP_URL . 'assets/js/imagify-promoter-media.js',
				array(),
				$asset_ver,
				true
			);

			wp_enqueue_style(
				'onecom-imagify-promoter',
				GROUPONE_WP_URL . 'assets/css/imagify-promoter.css',
				array(),
				$asset_ver
			);

			wp_add_inline_script( 'onecom-imagify-promoter-media', $this->get_inline_data(), 'before' );
		}

		public function handle_dismiss(): void {
			check_ajax_referer( 'onecom-imagify-promoter', 'nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'message' => 'Unauthorized' ) );
			}

			update_option( self::DISMISS_OPTION, true );
			wp_send_json_success();
		}
	}
}
