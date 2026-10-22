<?php

declare( strict_types=1 );
defined( 'WPINC' ) or die();

/**
 * WPIN-8528: Performance score column on the WP Pages list.
 *
 * When the deep integration toggle is enabled and WP Rocket is NOT active,
 * a "Performance Score" column is added to the pages list. It shows a blurred
 * dummy score with a Gravity CSS tooltip (info icon) and a CTA to install
 * WP Rocket via the Marketplace.
 *
 * If WP Rocket is active, the column is hidden (WP Rocket handles real scores).
 * The toggle is stored in a site option and can be flipped from the one.com
 * Home page Preferences section.
 */
class GroupOne_Performance_Column {

	const OPTION_KEY  = 'oc_perf_column_enabled';
	const AJAX_TOGGLE = 'oc_toggle_perf_column';
	const COLUMN_KEY  = 'oc_performance_score';

	public function init(): void {
		add_filter( 'manage_pages_columns', array( $this, 'add_column' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_head-edit.php', array( $this, 'add_column_styles' ) );
		add_action( 'wp_ajax_' . self::AJAX_TOGGLE, array( $this, 'handle_toggle' ) );
	}

	/**
	 * Enqueue Gravity CSS on the pages list so tooltip styles are available.
	 *
	 * @param string|null $hook Current admin page hook. Null when a third party
	 *                          (e.g. Visual Composer's frontend editor) fires
	 *                          admin_enqueue_scripts without arguments.
	 */
	public function enqueue_assets( ?string $hook = null ): void {
		if ( $hook !== 'edit.php' ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'page' ) {
			return;
		}
		if ( ! $this->should_show_column() ) {
			return;
		}
		// Gravity CSS is registered in the main plugin file — enqueue it here.
		wp_enqueue_style( 'onecom-gravity-styles' );
	}

	/**
	 * Add the performance score column — only when toggle is on and WP Rocket is inactive.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_column( array $columns ): array {
		if ( ! $this->should_show_column() ) {
			return $columns;
		}
		// Insert after the 'title' column.
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( $key === 'title' ) {
				$new[ self::COLUMN_KEY ] = __( 'Performance score', 'onecom-wp' );
			}
		}
		return $new;
	}

	/**
	 * Render the blurred dummy score with a Gravity tooltip on hover and a CTA.
	 *
	 * Tooltip text is dynamic:
	 *  - WP Rocket not installed → "Install WP Rocket to see your real score."
	 *  - WP Rocket installed but inactive → "Activate WP Rocket to see your real score."
	 * Both end with a "Get WP Rocket" CTA link inside the tooltip.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( $column !== self::COLUMN_KEY ) {
			return;
		}
		if ( ! $this->should_show_column() ) {
			return;
		}

		// Stable dummy score per post (42–94 range).
		$dummy_score     = 42 + ( $post_id % 53 );
		$marketplace_url = admin_url( 'admin.php?page=' . MARKETPLACE_PAGE_SLUG . '&plugin=wp-rocket' );
		$tooltip_id      = 'oc-perf-tip-' . $post_id;
		$info_icon_url   = GROUPONE_WP_URL . 'modules/home/assets/icons/info.svg';

		// Dynamic action word based on WP Rocket install/activation status.
		$action      = $this->get_wp_rocket_action();
		$tooltip_msg = sprintf(
			/* translators: %s: "Install" or "Activate" */
			__( '%s WP Rocket to see your real score.', 'onecom-wp' ),
			$action
		);

		?>
		<div class="gv-activated gv-inline-flex gv-items-center">

			<?php /* gv-tooltip-container + gv-tooltip-top-center: Gravity positions tooltip above, centred */ ?>
			<div class="gv-tooltip-container gv-tooltip-top-center gv-inline-flex gv-items-center gv-gap-xs oc-perf-score-row"
				style="cursor:pointer;"
				aria-describedby="<?php echo esc_attr( $tooltip_id ); ?>">

				<span class="gv-inline-flex gv-items-center" aria-hidden="true">
					<img src="<?php echo esc_url( $info_icon_url ); ?>" alt="" width="20" height="20" />
				</span>
				<?php /* gv-text-xl = fluid font size, gv-text-bold = font-weight semibold, gv-text-primary = Gravity blue.
				       filter/user-select/pointer-events have no Gravity class — inline style used instead. */ ?>
				<span class="gv-text-xl gv-text-bold gv-text-primary oc-perf-score-value"
					style="filter:blur(5px);user-select:none;pointer-events:none;"
					aria-hidden="true"><?php echo esc_html( (string) $dummy_score ); ?></span>

				<div id="<?php echo esc_attr( $tooltip_id ); ?>"
					class="gv-tooltip gv-arrow-bottom-center gv-text-on-inverted gv-mode-condensed oc-perf-tooltip"
					role="tooltip">
					<?php echo esc_html( $tooltip_msg ); ?>
					<?php /* gv-block = display:block, gv-mt-xs = margin-top, gv-text-on-inverted = white.
					       text-decoration has no Gravity class — inline style used instead.
					       hover colour is fixed via .oc-perf-tooltip-cta:hover rule below (Gravity overrides a:hover with --color-link). */ ?>
					<a href="<?php echo esc_url( $marketplace_url ); ?>"
						class="gv-text-on-inverted gv-block gv-mt-xs oc-perf-tooltip-cta"
						style="text-decoration:underline;">
						<?php _e( 'Get WP Rocket', 'onecom-wp' ); ?>
					</a>
				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Returns the action label for the tooltip based on WP Rocket's install/active state.
	 *
	 * @return string "Install" or "Activate"
	 */
	private function get_wp_rocket_action(): string {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_file = 'wp-rocket/wp-rocket.php';
		if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) && ! is_plugin_active( $plugin_file ) ) {
			return __( 'Activate', 'onecom-wp' );
		}
		return __( 'Install', 'onecom-wp' );
	}

	/**
	 * Inline styles for the blurred score and tooltip positioning — pages list screen only.
	 */
	public function add_column_styles(): void {
		if ( ! $this->should_show_column() ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'page' ) {
			return;
		}
		?>
		<style>
		/* Performance score column — WPIN-8528
		   All layout/colour/spacing uses Gravity utility classes on the HTML elements.
		   Only 3 things remain here that Gravity has no class for: */

		/* 1. Column width */
		.column-oc_performance_score { width: 160px; }

		/* 2. JS-toggled single-tooltip visibility (opacity so only one shows at a time).
		      Overrides Gravity's display:none so CSS transition works. */
		.gv-tooltip.oc-perf-tooltip {
			display: block !important;
			opacity: 0;
			pointer-events: none;
			transition: opacity 0.15s ease;
		}
		.gv-tooltip.oc-perf-tooltip.oc-perf-tooltip--visible {
			opacity: 1;
			pointer-events: auto;
		}

		/* 3. CTA hover fix: Gravity sets .gv-activated a:hover { color: var(--color-link) }
		      which overrides gv-text-on-inverted on hover — force white back. */
		.oc-perf-tooltip-cta:hover,
		.oc-perf-tooltip-cta:focus {
			color: var(--color-body-on-inverted) !important;
		}
		</style>
		<script>
		(function () {
			var hideTimer = null;
			var activeTooltip = null;

			function showTooltip(tooltip) {
				/* Instantly hide any currently visible tooltip from a different row */
				if (activeTooltip && activeTooltip !== tooltip) {
					clearTimeout(hideTimer);
					activeTooltip.classList.remove('oc-perf-tooltip--visible');
					activeTooltip = null;
				}
				clearTimeout(hideTimer);
				tooltip.classList.add('oc-perf-tooltip--visible');
				activeTooltip = tooltip;
			}

			function scheduleHide(tooltip) {
				clearTimeout(hideTimer);
				hideTimer = setTimeout(function () {
					tooltip.classList.remove('oc-perf-tooltip--visible');
					if (activeTooltip === tooltip) { activeTooltip = null; }
				}, 1500);
			}

			document.addEventListener('DOMContentLoaded', function () {
				document.querySelectorAll('.oc-perf-score-row').forEach(function (row) {
					var tooltip = row.querySelector('.oc-perf-tooltip');
					if (!tooltip) { return; }

					row.addEventListener('mouseenter', function () { showTooltip(tooltip); });
					row.addEventListener('mouseleave', function () { scheduleHide(tooltip); });
					tooltip.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
					tooltip.addEventListener('mouseleave', function () { scheduleHide(tooltip); });
				});
			});
		})();
		</script>
		<?php
	}

	/**
	 * AJAX: toggle the performance column visibility option.
	 */
	public function handle_toggle(): void {
		check_ajax_referer( self::AJAX_TOGGLE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		if ( self::is_wp_rocket_active() ) {
			wp_send_json_error( 'wp_rocket_active', 400 );
		}
		$enabled = isset( $_POST['enabled'] ) ? (bool) intval( $_POST['enabled'] ) : false;
		update_option( self::OPTION_KEY, $enabled ? '1' : '0', false );
		wp_send_json_success( array( 'enabled' => $enabled ) );
	}

	/**
	 * Returns true when the column should be rendered.
	 * Conditions: toggle is on AND WP Rocket plugin is NOT active.
	 */
	private function should_show_column(): bool {
		if ( get_option( self::OPTION_KEY, '1' ) !== '1' ) {
			return false;
		}
		// Hide column when WP Rocket is already active.
		if ( self::is_wp_rocket_active() ) {
			return false;
		}
		return true;
	}

	/**
	 * Whether the WP Rocket plugin is currently active.
	 * Used to hide the column itself and the Home page toggle when WP Rocket
	 * already provides real performance scores.
	 */
	public static function is_wp_rocket_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( 'wp-rocket/wp-rocket.php' );
	}
}
