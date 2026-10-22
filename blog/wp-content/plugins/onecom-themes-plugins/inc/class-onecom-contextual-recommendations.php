<?php

declare( strict_types=1 );
defined( 'WPINC' ) or die();

/**
 * Contextual product recommendations engine.
 *
 * Evaluates trigger rules and shows dismissible recommendations on the
 * one.com plugin home page. Each rule is data-driven so new
 * trigger/product pairs can be added without changing the display logic.
 */
class GroupOne_Contextual_Recommendations {

	const AJAX_DISMISS    = 'oc_dismiss_recommendation';
	const META_KEY_PREFIX = 'oc_rec_dismissed_';

	/** @var array[] */
	private array $rules = [];

	public function init(): void {
		$this->rules = $this->build_rules();
		add_action( 'admin_notices', array( $this, 'render_notices' ) );
		add_action( 'wp_ajax_' . self::AJAX_DISMISS, array( $this, 'handle_dismiss' ) );
	}

	/**
	 * Data-driven rules list. Each entry defines when to surface a recommendation.
	 * Add new rules here — no other code changes needed.
	 */
	private function build_rules(): array {
		// Order determines priority: only the first matching rule is shown at a time.
		// Priority: WP Rocket → Rank Math → Imagify.
		return [
			[
				'id'          => 'wp-rocket',
				'trigger'     => function (): bool {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					// WooCommerce active but WP Rocket not active.
					if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
						return false;
					}
					$speed_plugins = [
						'wp-rocket/wp-rocket.php',
					];
					foreach ( $speed_plugins as $p ) {
						if ( is_plugin_active( $p ) ) {
							return false;
						}
					}
					return true;
				},
				'heading'     => __( 'Speed up your WooCommerce store', 'onecom-wp' ),
				'body'        => __( 'Slow shops lose sales. WP Rocket is the easiest way to boost your store\'s speed — caching, lazy load and more, out of the box.', 'onecom-wp' ),
				'cta_label'   => __( 'Get WP Rocket', 'onecom-wp' ),
				'cta_url'     => admin_url( 'admin.php?page=' . MARKETPLACE_PAGE_SLUG . '&plugin=wp-rocket' ),
			],
			[
				'id'          => 'rank-math',
				'trigger'     => function (): bool {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					// No SEO plugin active.
					$seo_plugins = [
						'seo-by-rank-math-pro/rank-math-pro.php',
					];
					foreach ( $seo_plugins as $p ) {
						if ( is_plugin_active( $p ) ) {
							return false;
						}
					}
					return true;
				},
				'heading'     => __( 'Improve your search rankings', 'onecom-wp' ),
				'body'        => __( 'No SEO plugin detected — your site may be missing a sitemap and key ranking signals. Rank Math PRO sets it all up in minutes.', 'onecom-wp' ),
				'cta_label'   => __( 'Get Rank Math PRO', 'onecom-wp' ),
				'cta_url'     => admin_url( 'admin.php?page=' . MARKETPLACE_PAGE_SLUG . '&plugin=seo-by-rank-math' ),
			],
			[
				'id'          => 'imagify',
				'trigger'     => function (): bool {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					// Site has media and no image optimiser is active.
					$counts      = (array) wp_count_attachments( 'image' );
					$image_total = array_sum( array_filter( $counts, fn( $k ) => str_starts_with( $k, 'image/' ), ARRAY_FILTER_USE_KEY ) );
					if ( $image_total < 1 ) {
						return false;
					}
					$optimisers = [
						'imagify/imagify.php',
					];
					foreach ( $optimisers as $p ) {
						if ( is_plugin_active( $p ) ) {
							return false;
						}
					}
					return true;
				},
				'heading'     => __( 'Optimise your images automatically', 'onecom-wp' ),
				'body'        => __( 'Your site has images that could be slowing it down. Imagify compresses and optimises them automatically — available now in the one.com Marketplace.', 'onecom-wp' ),
				'cta_label'   => __( 'Get Imagify', 'onecom-wp' ),
				'cta_url'     => admin_url( 'admin.php?page=' . MARKETPLACE_PAGE_SLUG . '&plugin=imagify' ),
			],
		];
	}

	public function render_notices(): void {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$screen = get_current_screen();

		// Show only on the Plugins and Themes admin pages (plugins.php and themes.php).
		// Excludes the WP dashboard, one.com plugin home page, Marketplace pages,
		// and post/page edit screens. (WPIN-8787)
		$allowed_screens = [ 'plugins', 'themes' ];
		if ( ! $screen instanceof WP_Screen || ! in_array( $screen->base, $allowed_screens, true ) ) {
			return;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$user_id  = get_current_user_id();
		$nonce    = wp_create_nonce( self::AJAX_DISMISS );
		$rendered = 0;

		foreach ( $this->rules as $rule ) {
			if ( get_user_meta( $user_id, self::META_KEY_PREFIX . $rule['id'], true ) ) {
				continue;
			}

			if ( ! ( $rule['trigger'] )() ) {
				continue;
			}

			$rendered++;

			?>
			<div class="notice notice-info is-dismissible oc-recommendation" data-rule-id="<?php echo esc_attr( $rule['id'] ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
				<p>
					<strong><?php echo esc_html( $rule['heading'] ); ?></strong><br>
					<?php echo esc_html( $rule['body'] ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( $rule['cta_url'] ); ?>" class="button button-primary">
						<?php echo esc_html( $rule['cta_label'] ); ?>
					</a>
				</p>
			</div>
			<?php
			// Show only one recommendation at a time (priority order: WP Rocket → Rank Math → Imagify).
			break;
		}

		if ( $rendered > 0 ) {
			$this->render_dismiss_script();
		}
	}

	private function render_dismiss_script(): void {
		?>
		<script type="text/javascript">
		(function($){
			$(document).on('click', '.oc-recommendation .notice-dismiss', function(){
				var $notice = $(this).closest('.oc-recommendation');
				$.post(ajaxurl, {
					action: '<?php echo esc_js( self::AJAX_DISMISS ); ?>',
					rule_id: $notice.data('rule-id'),
					nonce:   $notice.data('nonce')
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	public function handle_dismiss(): void {
		check_ajax_referer( self::AJAX_DISMISS, 'nonce' );
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		$rule_id = isset( $_POST['rule_id'] ) ? sanitize_key( $_POST['rule_id'] ) : '';
		if ( empty( $rule_id ) ) {
			wp_send_json_error( 'missing rule_id', 400 );
		}
		update_user_meta( get_current_user_id(), self::META_KEY_PREFIX . $rule_id, true );
		wp_send_json_success();
	}
}
