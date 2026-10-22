<?php
require_once GROUPONE_WP_PATH . '/modules/home/oc-home-sections.php';
$home_sections           = new OneHomeSections();
$cards                   = $home_sections->get_cards();
$help_cards              = $home_sections->get_help_cards();
$articles                = ismWP() ? $home_sections->get_articles_mwp() : $home_sections->get_articles_basic();
$cp_url                  = $home_sections->get_cp_url();
$wp_heading              = ismWP() ? __( 'Managed WordPress', 'onecom-wp' ) : __( 'Basic WordPress', 'onecom-wp' );
$premium_care_dismissed  = get_site_option( 'oc_home_premium_care_dismiss', false );
$data_consent_status          = get_site_option( 'onecom_data_consent_status', false );
if ( ! class_exists( 'GroupOne_Performance_Column' ) ) {
	require_once GROUPONE_WP_PATH . '/inc/class-onecom-performance-column.php';
}
$perf_column_enabled     = get_option( GroupOne_Performance_Column::OPTION_KEY, '1' ) === '1';
$module_toggles          = $home_sections->get_module_toggles();
$module_toggle_nonce     = wp_create_nonce( 'oc_toggle_module' );

$imagify_promoter_enabled     = get_site_option( 'onecom_imagify_promoter_enabled', 1 );
$premium_care_requested  = get_transient( 'onecom_premium_wp_care_request' );
$premium_request_class   = $premium_care_requested ? 'gv-hidden' : '';
$premium_requested_class = $premium_care_requested ? '' : 'gv-hidden';
$current_user            = wp_get_current_user();
$user_email              = $current_user->user_email;
?>
<div class="wrap2 gv-activated">
	<div class="inner-wrapper gv-p-fluid">
	<!-- Header For managed and button -->
	<div class="gv-grid gv-desk-grid-cols-3 gv-tab-grid-cols-5 gv-grid-cols-3 gv-mb-fluid">
		<div class="oc-header-text gv-justify-between gv-desk-span-1 gv-tab-span-2 gv-span-3">
			<h1 class="gv-heading-lg">one.com</h1>
			<p class="gv-heading-sm oc-wp-type"><?php echo $wp_heading; ?></p>
		</div>
		<div class="oc-header-action gv-button-group gv-desk-span-2 gv-tab-span-3 gv-span-3 gv-mt-sm oc-ol gv-max-mob-pb-lg">
			<a href="<?php echo $home_sections->get_cp_url(); ?>" class="gv-button gv-button-secondary ocwp_ocp_home_back_to_cp_clicked_event" target="_blank">
				<gv-icon src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/arrow_back.svg" class="ocwp_ocp_home_back_to_cp_clicked_event"></gv-icon>
				<span><?php echo __( 'Back to control panel', 'onecom-wp' ); ?></span>
			</a>
			<a href="<?php echo get_site_url(); ?>"
				class="gv-button gv-button-primary gv-max-mob-order-first ocwp_ocp_home_site_viewed_event" target="_blank"><span><?php echo __( 'View site', 'onecom-wp' ); ?></span>
			<gv-icon src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/open_in_new.svg" class="ocwp_ocp_home_site_viewed_event"></gv-icon></a>
		</div>
		<p class="gv-text-sm gv-mt-md oc-header-desc gv-span-3 gv-tab-span-5"><?php echo __( 'Enhance your WordPress experience with the one.com plugin and one.com dashboard. Get automated updates, boosted security, recommended plugins, and optimised performance.', 'onecom-wp' ); ?></p>
	</div>
	<!-- Header For managed and button End -->

	<!-- Start: Premium care request notice -->
	<div id="oc-premium-care-request-notice" class="gv-notice gv-notice-success gv-mb-lg gv-hidden">
		<gv-icon class="gv-notice-icon" src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/check_circle.svg"></gv-icon>
		<p class="gv-notice-content"><?php echo sprintf( __( "Thank you for your request! We will send a reply to your email address %s soon.", 'onecom-wp' ), $user_email ); ?>
		</p>
		<button class="gv-notice-close" id="oc-close-premium-care">
			<gv-icon src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/close.svg"></gv-icon>
		</button>
	</div>
	<!-- Ends: Premium care request notice -->

	<!-- link for mwp and non-mWP -->
	<div class="gv-grid gv-gap-lg gv-tab-grid-cols-1 gv-desk-grid-cols-3 gv-mt-lg gv-max-mob-mb-lg gv-max-mob-pb-lg">
		<?php foreach ( $cards as $card ) : ?>
			<div
				class="gv-card gv-content-container gv-p-lg gv-grid gv-grid-cols-12">
				<gv-tile class="gv-desk-span-2 gv-tab-span-1 gv-span-2 oc-grid-img"
						src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/tiles/<?php echo $card['icon']; ?>.svg"></gv-tile>
				<div class="gv-desk-span-9 gv-tab-span-10 gv-span-9 oc-grid-text">
					<p class="gv-text-lg"><?php echo $card['title']; ?></p>
					<span class="gv-text-sm"><?php echo $card['subtitle']; ?></span>
				</div>
				<?php
				// WPIN-8936: a module-backed tile (currently only Performance)
				// has an empty url while its module is off/unavailable -- the
				// settings page it would link to doesn't exist yet. Point the
				// CTA at the module toggle row instead of a dead link.
				$tile_module_disabled = empty( $card['url'] ) && ! empty( $card['module_slug'] );

				// Emitted for every module-backed tile, not just a disabled one:
				// the JS that runs after a module toggle needs a stable handle on
				// the tile and its target so it can enable the link in place.
				// Only the class and the href tell the two states apart.
				$tile_module_slug = ! empty( $card['module_slug'] ) ? $card['module_slug'] : '';
				$tile_module_url  = $tile_module_slug ? $home_sections->get_module_page_url( $tile_module_slug ) : '';
				?>
				<div class="gv-span-1 gv-grid gv-content oc-grid-link">
					<a href="<?php echo $tile_module_disabled ? 'javascript:void(0);' : esc_url( $card['url'] ); ?>"
						class="gv-action <?php echo esc_attr( $card['event_track_class'] ); ?><?php echo $tile_module_disabled ? ' oc-scroll-to-module' : ''; ?>"
						<?php if ( $tile_module_slug ) : ?>data-module="<?php echo esc_attr( $tile_module_slug ); ?>" data-module-url="<?php echo esc_url( $tile_module_url ); ?>"<?php endif; ?>>
						<gv-icon
							src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/arrow_forward.svg" class="<?php echo $card['event_track_class']; ?>"></gv-icon>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<!-- link for mwp and non-mWP End -->
	<!-- Start: Recommended plugins list -->
		<?php echo $home_sections->get_recommended_products_list();?>
	<!-- End: Plugin and WordPress introduction tour -->
	<!-- Premium WP care -->
	<?php if ( groupone_is_premium_wp_care_addon_purchased() || ! $premium_care_dismissed ) { ?>
		<div class="oc-premium-care-box gv-mt-lg">
			<strong
				class="gv-text-lg"><?php echo __( 'Expert care for your WordPress site', 'onecom-wp' ); ?></strong>
			<?php if ( groupone_is_premium_wp_care_addon_purchased() ) { ?>
				<div class="gv-notice gv-notice-premium-care gv-mt-md gv-p-lg gv-max-mob-pt-lg  <?php echo $premium_request_class; ?>">
					<gv-tile class="gv-desk-span-2 gv-tab-span-1 gv-span-2 oc-grid-img"
							src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/tiles/upgrade.svg"></gv-tile>
					<div class="oc-content gv-notice-content">
						<p class="gv-text-lg"><strong><?php echo __( 'Premium WP care', 'onecom-wp' ); ?></strong></p>
						<span class="gv-text-sm"><?php echo __( 'You have access to premium WordPress support! As a Premium WP Care customer, you have priority access to our expert support team.', 'onecom-wp' ); ?></span>
					</div>
					<a href="javascript:void(0)"
						class="gv-button gv-button-primary oc_premium_care_modal_show ocwp_ocp_home_contact_pwpc_clicked_event"><?php echo __( 'Contact premium support', 'onecom-wp' ); ?></a>
				</div>
			<?php } elseif ( ( ! $premium_care_dismissed ) ) { ?>
				<div  class="gv-notice gv-notice-premium-care gv-mt-md gv-p-lg gv-max-mob-pt-lg <?php echo $premium_request_class; ?>">
					<gv-tile class="gv-desk-span-2 gv-tab-span-1 gv-span-2 oc-grid-img"
							src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/tiles/upgrade.svg"></gv-tile>
					<div class="oc-content gv-notice-content">
						<p class="gv-text-lg"><strong><?php echo __( 'Premium WP care', 'onecom-wp' ); ?></strong></p>
						<span class="gv-text-sm"><?php echo __( 'Let us monitor your site 24/7 and keep it safe and secure. Including emergency fixes and monthly reports.', 'onecom-wp' ); ?></span>
					</div>
					<a href="javascript:void(0)"
						class="gv-button gv-button-primary oc_premium_care_modal_show ocwp_ocp_home_pwpc_learn_more_clicked_event"><?php echo __( 'Learn more', 'onecom-wp' ); ?></a>
					<button class="gv-notice-close ocwp_ocp_home_pwpc_dismiss_event">
						<gv-icon src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/close.svg"></gv-icon>
					</button>
				</div>
			<?php } ?>
			<div class="gv-notice gv-notice-premium-care-requested gv-mt-md gv-p-lg gv-max-mob-pt-lg <?php echo $premium_requested_class; ?>">
				<gv-tile class="gv-desk-span-2 gv-tab-span-1 gv-span-2 oc-grid-img"
						src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/tiles/upgrade.svg"></gv-tile>
				<div class="oc-content gv-notice-content">
					<p class="gv-text-lg"><strong><?php echo __( 'Premium WP care', 'onecom-wp' ); ?></strong></p>
					<span class="gv-text-sm"><?php printf( __( 'We’ve received your request and will reply to your email address %s as soon as possible.', 'onecom-wp' ), $user_email ); ?></span>
				</div>
			</div>
		</div>
	<?php } ?>
	<!-- Premium WP care End -->

	<!-- Footer area -->
	<div class="gv-mt-fluid gv-grid gv-gap-fluid gv-tab-grid-cols-1 gv-desk-grid-cols-2 gv-mobile-grid-cols-1 footer-section">
		<!-- Any question -->
		<div>

			<strong class="gv-text-lg"><?php echo __( 'Preferences', 'onecom-wp' ); ?></strong>
			<p class="gv-text-sm"><?php echo __( 'Allow us to collect non-sensitive data to enhance our services.', 'onecom-wp' ); ?></p>
			<div id="oc-consent-settings" class="gv-card gv-content-container gv-my-lg gv-py-md gv-px-lg gv-grid gv-grid-cols-12 ">
				<div class="gv-span-12">
					<div class=" gv-mode-condensed">
						<div class="gv-form-option">
							<div class="gv-option-inline">
								<div class="gv-toggle">
									<input type="checkbox" id="oc-data-consent-toggle" class="ocwp_ocp_home_consent_banner_toggled_event" <?php checked( $data_consent_status, 1 ); ?> />
									<div class="gv-toggle-slider"></div>
								</div>
								<label for="oc-data-consent-toggle" class="gv-label ocwp_ocp_home_consent_banner_toggled_event">
									<span class="gv-text-sm"><?php echo esc_html__( 'Allow analytics', 'onecom-wp' ); ?></span>
									<span class="gv-description ">
									<?php echo __( 'Help us improve your services and recommendations by allowing anonymous usage analytics. We only collect non-sensitive data.', 'onecom-wp' ); ?> <a class="oc_consent_modal_show ocwp_ocp_home_data_modal_link_clicked_event" href="javascript:void(0);"> <?php echo __( 'Learn more', 'onecom-wp' ); ?></a>.
								</span>
								</label>
							</div>
						</div>

					</div>

					<div class="gv-flex gv-mb-sm">
						<div class="gv-toggle-wrapper gv-content-center">
						</div>
					</div>
					<div class="gv-text-sm oc-wp-type" style=""></div>
				</div>
			</div>

			<!-- Imagify Image Optimisation Promoter toggle -->
			<div id="oc-imagify-settings" class="gv-card gv-content-container gv-my-lg gv-py-md gv-px-lg gv-grid gv-grid-cols-12">
				<div class="gv-span-12">
					<div class="gv-mode-condensed">
						<div class="gv-form-option">
							<div class="gv-option-inline">
								<div class="gv-toggle">
									<input type="checkbox" id="oc-imagify-promoter-toggle" <?php checked( $imagify_promoter_enabled, 1 ); ?> />
									<div class="gv-toggle-slider"></div>
								</div>
								<label for="oc-imagify-promoter-toggle" class="gv-label">
									<span class="gv-text-sm"><?php echo esc_html__( 'Image optimisation', 'onecom-wp' ); ?></span>
									<span class="gv-description">
										<?php echo esc_html__( 'Show Imagify recommendations when editing posts, pages, or uploading media.', 'onecom-wp' ); ?>
									</span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Imagify Image Optimisation Promoter toggle -->
			<?php if ( ! GroupOne_Performance_Column::is_wp_rocket_active() ) : ?>
			<!-- Start: Performance Score toggle -->
			<?php
			$perf_nonce = wp_create_nonce( GroupOne_Performance_Column::AJAX_TOGGLE );
			?>
			<div id="oc-perf-column-settings" class="gv-card gv-content-container gv-my-lg gv-py-md gv-px-lg gv-grid gv-grid-cols-12"
			     data-nonce="<?php echo esc_attr( $perf_nonce ); ?>"
			     data-ajax-action="<?php echo esc_attr( GroupOne_Performance_Column::AJAX_TOGGLE ); ?>">
				<div class="gv-span-12">
					<div class="gv-mode-condensed">
						<div class="gv-form-option">
							<div class="gv-option-inline">
								<div class="gv-toggle">
									<input type="checkbox" id="oc-perf-column-toggle" <?php checked( $perf_column_enabled, true ); ?> />
									<div class="gv-toggle-slider"></div>
								</div>
								<label for="oc-perf-column-toggle" class="gv-label">
									<span class="gv-text-sm"><?php echo esc_html__( 'Enable Performance Score', 'onecom-wp' ); ?></span>
									<span class="gv-description">
										<?php echo __( 'Show a performance score column on the Pages list. Unlock real scores by installing WP Rocket from the one.com Marketplace.', 'onecom-wp' ); ?>
									</span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End: Performance Score toggle -->
			<?php endif; ?>
			<strong class="gv-text-lg"><?php echo __( 'Any questions?', 'onecom-wp' ); ?></strong>
			<p class="gv-text-sm"><?php echo __( 'Check out our Help Centre or get in touch with customer support.', 'onecom-wp' ); ?></p>
			<?php foreach ( $help_cards as $card ) : ?>
				<div
					class="gv-card gv-content-container gv-my-lg gv-py-md gv-px-lg gv-grid gv-grid-cols-12 gv-items-center">
					<gv-icon class="oc-grid-img-two"
						src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/<?php echo $card['icon']; ?>.svg"></gv-icon>

					<div class="oc-content gv-span-10 oc-grid-content-two">
						<p class="gv-text-lg"><strong><?php echo $card['title']; ?></strong></p>
						<span class="gv-text-sm oc-wp-type"><?php echo $card['subtitle']; ?></span>
					</div>
					<div class="gv-span-1 gv-grid gv-content oc-grid-link">
						<a href="<?php echo $card['url']; ?>" class="gv-action <?php echo $card['event_track_class']; ?>" target="_blank">
							<gv-icon
								src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/open_in_new.svg" class="<?php echo $card['event_track_class']; ?>"></gv-icon>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
                <div class="gv-card gv-content-container oc-reset-wlk-tour gv-my-lg gv-py-md gv-px-lg gv-grid gv-grid-cols-12 gv-max-mob-hidden gv-items-center">
                <gv-icon class="oc-grid-img-two"
                         src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/wordpress.svg"></gv-icon>

                <div class="oc-content gv-span-10 oc-grid-content-two">
                    <p class="gv-text-lg"><strong><?php echo __( 'Restart tour', 'onecom-wp' ); ?></strong></p>
                    <span class="gv-text-sm oc-wp-type"><?php echo __( 'Get introduced to our plugin and WordPress.', 'onecom-wp' ); ?></span>
                </div>
                <div class="gv-span-1 gv-grid gv-content oc-grid-link">
                    <a href="javascript:void(0);" class="gv-action ocwp_ocp_home_tour_restarted_event">
                        <gv-icon
                                src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/arrow_forward.svg" class="<?php echo $card['event_track_class']; ?>"></gv-icon>
                    </a>
                </div>
            </div>
		</div>
		<!-- Any question End -->

		<!-- article links -->
		<div>
			<?php if ( ! empty( $module_toggles ) ) : ?>
			<!-- Start: Module toggle list (moved from the Settings page, WPIN-8736) -->
			<div class="gv-grid gv-grid-cols-2">
				<strong class="gv-text-lg oc-dicover-wp"><?php echo __( 'Modules', 'onecom-wp' ); ?></strong>
			</div>
			<p class="gv-text-sm"><?php echo __( 'Turn individual one.com features on or off. Disabling a feature stops its code from loading on this site.', 'onecom-wp' ); ?></p>
			<div id="oc-module-toggles" class="gv-card gv-content-container gv-my-lg gv-py-md gv-px-lg" data-nonce="<?php echo esc_attr( $module_toggle_nonce ); ?>">
				<?php foreach ( $module_toggles as $index => $module ) : ?>
					<?php if ( $index > 0 ) : ?>
						<hr class="gv-divider" />
					<?php endif; ?>
					<div class="gv-grid gv-grid-cols-12 gv-my-sm gv-items-center">
						<?php if ( $module['icon'] ) : ?>
							<div class="gv-span-2 gv-tab-span-1 gv-grid">
								<gv-icon src="<?php echo esc_url( GROUPONE_WP_URL . '/' . $module['icon'] ); ?>"></gv-icon>
							</div>
						<?php endif; ?>
						<?php
						// WPIN-8937: the row's text needs to be a real <label for>
						// bound to the checkbox, the way the "Allow analytics"
						// toggle above already does it. Without the id/for pair
						// only the input itself was clickable, so clicking the
						// module name or the visible slider did nothing.
						$module_toggle_id = 'oc-module-toggle-' . sanitize_html_class( $module['slug'] );
						?>
						<?php
						// WPIN-9042: the row's 12-column grid is fully packed
						// (icon + label + toggle spans always sum to 12), so
						// giving the toggle column a wider reserved slot for
						// the spinner (below) must borrow exactly one column
						// back from the label, or the toggle would overflow
						// into an implicit extra grid row. Scoped to the
						// mwp_only row only via oc-label-reserve-spinner.
						$label_reserve_class = ! empty( $module['mwp_only'] ) ? ' oc-label-reserve-spinner' : '';
						?>
						<label for="<?php echo esc_attr( $module_toggle_id ); ?>" class="gv-label <?php echo $module['icon'] ? 'gv-tab-span-10 gv-span-8' : 'gv-tab-span-11 gv-span-9'; ?><?php echo esc_attr( $label_reserve_class ); ?>">
							<p class="gv-text-sm">
								<strong><?php echo esc_html( $module['label'] ); ?></strong>
								<?php if ( $module['badge'] ) : ?>
									<span class="gv-badge gv-badge-generic oc-mwp-badge"><?php echo esc_html( $module['badge'] ); ?></span>
								<?php endif; ?>
							</p>
							<?php if ( $module['description'] ) : ?>
								<span class="gv-description gv-text-sm"><?php echo esc_html( $module['description'] ); ?></span>
							<?php endif; ?>
							<?php if ( $module['note'] ) : ?>
								<span class="gv-description gv-text-sm"><?php echo esc_html( $module['note'] ); ?></span>
							<?php endif; ?>
						</label>
						<?php
						// WPIN-9042: only mWP-gated modules (currently just
						// Spam Protection) incur the multi-second live
						// ismWP(true) check on enable (see mwp_only in
						// groupone_merged_modules_map()), so only their row
						// gets a reserved spinner slot, and only that row's
						// toggle column is widened (oc-toggle-wrap--reserve-spinner)
						// to fit it beside the toggle without overlapping the
						// label column (see the matching label class above).
						$toggle_reserve_class = ! empty( $module['mwp_only'] ) ? ' oc-toggle-wrap--reserve-spinner' : '';
						?>
						<div class="gv-span-2 gv-tab-span-1 gv-grid oc-place-ce oc-toggle-wrap<?php echo esc_attr( $toggle_reserve_class ); ?>">
							<?php if ( ! empty( $module['mwp_only'] ) ) : ?>
								<?php
								// Sits before the toggle in markup so it renders
								// to the LEFT of it once .oc-toggle-wrap
								// positions the pair in normal flex flow.
								?>
								<span class="spinner oc-module-toggle-spinner"></span>
							<?php endif; ?>
							<div class="gv-toggle">
								<input type="checkbox"
									id="<?php echo esc_attr( $module_toggle_id ); ?>"
									class="oc-module-toggle-input"
									data-module="<?php echo esc_attr( $module['slug'] ); ?>"
									<?php checked( $module['enabled'] ); ?>
									<?php disabled( ! $module['toggleable'] ); ?> />
								<div class="gv-toggle-slider"></div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<!-- WPIN-8899: shown by main.js instead of the generic failure
			     toast when a toggle-enable is blocked by the mWP check;
			     reuses the same banner shown on the Spam Protection settings
			     page rather than a separate copy. -->
			<div id="oc-sp-mwp-upsell-banner" class="gv-hidden">
				<?php echo groupone_sp_mwp_upsell_banner_html( GROUPONE_WP_URL . 'modules/spam-protection/assets/images/beginner-icon.svg', 'module_toggle' ); ?>
			</div>
			<!-- End: Module toggle list -->
			<?php endif; ?>
			<div class="gv-grid gv-grid-cols-2">
				<strong class="gv-text-lg oc-dicover-wp"><?php echo __( 'Discover WordPress', 'onecom-wp' ); ?></strong>
				<div class="gv-flex gv-justify-end">
					<a href="https://help.one.com/hc/en-us/categories/360002171377"
						class="gv-button gv-button-secondary gv-mode-condensed ocwp_ocp_home_all_articles_link_clicked_event" target="_blank">
						<span class="oc-all-articles"><?php echo __( 'See all articles', 'onecom-wp' ); ?></span>
						<gv-icon src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/open_in_new.svg" class
						="ocwp_ocp_home_all_articles_link_clicked_event"></gv-icon>
					</a>
				</div>
			</div>
			<p class="gv-text-sm"><?php echo __( 'Get inspired by our articles and learn more about WordPress.', 'onecom-wp' ); ?></p>
			<div
				class="gv-card gv-content-container gv-my-lg gv-py-md gv-px-lg">
				<?php foreach ( $articles as $article ) : ?>
					<div class="gv-grid gv-grid-cols-12 gv-my-sm">
						<p class="gv-span-11 gv-text-sm"><?php echo $article['title']; ?></p>
						<div class="gv-span-1 gv-grid oc-place-ce">
							<a href="<?php echo $article['url']; ?>" target="_blank" class="<?php echo $article['event_track_class']; ?>">
								<gv-icon
									src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/arrow_forward.svg" class="<?php echo $article['event_track_class']; ?>"></gv-icon>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<!-- article links end-->
	</div>
	<!-- Footer area end -->
	</div>
</div>
