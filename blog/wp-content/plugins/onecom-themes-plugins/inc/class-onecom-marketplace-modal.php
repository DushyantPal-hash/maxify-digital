<?php

declare( strict_types=1 );
defined( 'WPINC' ) or die();

/**
 * Marketplace launch announcement modal.
 *
 * Shown in WP-admin once to admin users who have not yet visited the Marketplace,
 * but only after 24 hours have elapsed since the plugin was installed or updated.
 * The 24-hour gate prevents showing the modal immediately after activation.
 */
class GroupOne_Marketplace_Modal {

	const META_VISITED      = 'oc_mp_modal_visited';
	const META_LAST_SHOWN   = 'oc_mp_modal_last_shown';
	const OPTION_FREQUENCY  = 'oc_mp_modal_frequency';
	const OPTION_INSTALL_TS = 'oc_mp_plugin_install_time';
	const AJAX_DISMISS      = 'oc_marketplace_modal_dismiss';
	const AJAX_VISITED      = 'oc_marketplace_modal_visited';

	public function init(): void {
		add_action( 'activated_plugin', array( $this, 'maybe_record_install_time' ) );
		add_action( 'admin_init', array( $this, 'track_marketplace_visit' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $this, 'render' ) );
		add_action( 'wp_ajax_' . self::AJAX_DISMISS, array( $this, 'handle_dismiss' ) );
		add_action( 'wp_ajax_' . self::AJAX_VISITED, array( $this, 'handle_visited' ) );
	}

	/**
	 * Enqueue the marketplace modal CSS on all admin pages when the modal is eligible to show.
	 * Uses a dedicated stylesheet scoped to #oc-mp-modal-* so it doesn't load main.css globally.
	 */
	public function enqueue_assets(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! $this->should_show() ) {
			return;
		}
		wp_enqueue_style(
			'oc-mp-modal',
			GROUPONE_WP_URL . 'assets/css/marketplace-modal.css',
			array(),
			GROUPONE_WP_VERSION
		);
	}

	/**
	 * Record install timestamp when the one.com plugin is activated for the first time.
	 * Only sets the option if it doesn't already exist (install, not re-activation).
	 */

	public function maybe_record_install_time( string $plugin ): void {
		if ( 'onecom-themes-plugins/onecom-themes-plugins.php' !== $plugin ) {
			return;
		}
		if ( ! get_option( self::OPTION_INSTALL_TS ) ) {
			update_option( self::OPTION_INSTALL_TS, time(), false );
		}
	}

	/**
	 * Reset the install timestamp when the one.com plugin is updated so the 24-hour gate re-applies.
	 * Called from upgrader_process_complete after confirming our plugin was upgraded.
	 */
	public static function record_update_time(): void {
		update_option( self::OPTION_INSTALL_TS, time(), false );
	}

	/** Mark the current user as a Marketplace visitor when on the Marketplace page. */
	public function track_marketplace_visit(): void {
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( $page === MARKETPLACE_PAGE_SLUG || $page === MARKETPLACE_PRODUCTS_PAGE_SLUG ) {
			update_user_meta( get_current_user_id(), self::META_VISITED, true );
		}
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! $this->should_show() ) {
			return;
		}

		$marketplace_url  = admin_url( 'admin.php?page=' . MARKETPLACE_PAGE_SLUG );
		$dismiss_nonce    = wp_create_nonce( self::AJAX_DISMISS );
		$visited_nonce    = wp_create_nonce( self::AJAX_VISITED );
		$scheme           = is_ssl() ? 'https' : 'http';
		$close_icon_url   = esc_url( set_url_scheme( GROUPONE_WP_URL . 'modules/home/assets/icons/close.svg', $scheme ) );
		$modal_image_url  = esc_url( set_url_scheme( GROUPONE_WP_URL . 'modules/marketplace/assets/images/marketplace-modal.svg', $scheme ) );
		?>
		<div id="oc-mp-modal-overlay">
			<div id="oc-mp-modal-wrap" class="gv-activated">
				<span id="oc-mp-modal-close" class="oc_welcome_modal_close">
					<img src="<?php echo $close_icon_url; ?>" alt="" />
				</span>
				<div class="oc-bg-wl-inner-wrap mp-awareness-modal">
					<div class="oc-welcome-head">
						<h5 class="gv-mb-sm"><?php _e( 'Discover the one.com Marketplace', 'onecom-wp' ); ?></h5>
					</div>
					<div style="background:#f7f7f7;display:flex;align-items:center;justify-content:center;">
						<img src="<?php echo $modal_image_url; ?>" alt="" />
					</div>
					<div class="gv-mt-sm gv-text-sm">
						<?php _e( 'Boost your site with premium plugins — WP Rocket, Rank Math PRO and more — at exclusive one.com prices. All in one place, built for your WordPress site.', 'onecom-wp' ); ?>
					</div>
					<div class="gv-mt-lg">
						<a id="oc-mp-modal-cta" href="<?php echo esc_url( $marketplace_url ); ?>" class="gv-button gv-button-primary">
							<?php _e( 'Explore Marketplace', 'onecom-wp' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		(function($){
			var nonceDismiss = '<?php echo esc_js( $dismiss_nonce ); ?>';
			var nonceVisited = '<?php echo esc_js( $visited_nonce ); ?>';

			// Lock scroll immediately — <body> is already in the DOM at admin_footer time.
			$('body').addClass('oc-mp-modal-open');

			function dismissModal() {
				$('#oc-mp-modal-overlay').hide();
				$('body').removeClass('oc-mp-modal-open');
				$.post(ajaxurl, { action: '<?php echo esc_js( self::AJAX_DISMISS ); ?>', nonce: nonceDismiss });
			}

			$(document).ready(function(){
				$('#oc-mp-modal-close').on('click', dismissModal);

				$('#oc-mp-modal-overlay').on('click', function(e){
					if ($(e.target).is('#oc-mp-modal-overlay')) { dismissModal(); }
				});

				$('#oc-mp-modal-cta').on('click', function(){
					$.post(ajaxurl, { action: '<?php echo esc_js( self::AJAX_VISITED ); ?>', nonce: nonceVisited });
				});

				$(document).on('keydown', function(e){
					if (e.key === 'Escape') { dismissModal(); }
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	public function handle_dismiss(): void {
		check_ajax_referer( self::AJAX_DISMISS, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		update_user_meta( get_current_user_id(), self::META_LAST_SHOWN, time() );
		wp_send_json_success();
	}

	public function handle_visited(): void {
		check_ajax_referer( self::AJAX_VISITED, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		update_user_meta( get_current_user_id(), self::META_VISITED, true );
		wp_send_json_success();
	}

	private function should_show(): bool {
		$user_id = get_current_user_id();

		// Do not show while already on the Marketplace page and marketplace product detail page.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( $page === MARKETPLACE_PAGE_SLUG || $page === MARKETPLACE_PRODUCTS_PAGE_SLUG) {
			return false;
		}

		// Do not show if the user has already visited the Marketplace.
		if ( get_user_meta( $user_id, self::META_VISITED, true ) ) {
			return false;
		}

		// Enforce 24-hour delay after plugin install or update.
		$install_ts = (int) get_option( self::OPTION_INSTALL_TS, 0 );
		if ( $install_ts > 0 && ( time() - $install_ts ) < DAY_IN_SECONDS ) {
			return false;
		}

		$last_shown = (int) get_user_meta( $user_id, self::META_LAST_SHOWN, true );
		$frequency  = get_option( self::OPTION_FREQUENCY, 'once' );

		if ( $frequency === 'once' && $last_shown > 0 ) {
			return false;
		}

		if ( $frequency === 'monthly' && $last_shown > 0 ) {
			return ( time() - $last_shown ) < ( 30 * DAY_IN_SECONDS ) ? false : true;
		}

		return true;
	}
}
