<?php
// WPIN-8922: resolve the asset base URL without depending on our own constants.
// This template is require()d on admin_footer, so a still-running pre-rename
// process (mid-update) can load it while only ONECOM_WP_URL is defined. Must
// stay inline and use core functions only — see welcome-modal.php for detail.
$groupone_asset_url = defined( 'GROUPONE_WP_URL' )
	? GROUPONE_WP_URL
	: plugin_dir_url( dirname( __DIR__, 3 ) . '/onecom-themes-plugins.php' );

/*
 * Same stale-process hazard, one step further: this is the only one of the four
 * templates that calls a plugin helper, and WPIN-8739 renamed that helper from
 * oc_get_privacy_policy_url() to groupone_get_privacy_policy_url(). A pre-rename
 * process has only the old name, so the constant guard above is not enough on
 * its own — verified on 6aug-test1.mwp.1next.one, where this template still
 * fatalled with "Call to undefined function groupone_get_privacy_policy_url()".
 * Fall back through the old name, then to the English policy, so the modal
 * degrades to a working link rather than taking down wp-admin.
 */
if ( function_exists( 'groupone_get_privacy_policy_url' ) ) {
	$groupone_privacy_url = groupone_get_privacy_policy_url();
} elseif ( function_exists( 'oc_get_privacy_policy_url' ) ) {
	$groupone_privacy_url = oc_get_privacy_policy_url();
} else {
	$groupone_privacy_url = 'https://www.one.com/en-gb/legal/privacy/';
}
?>
<!-- This file contains consent modal and toast messages which are required on all one.com plugin pages, so added in this common file -->

<!-- Consent banner modal -->
<div id="oc_data_consent_overlay" style="display:none;">
	<div id="oc_login_masking_overlay_wrap" class="gv-activated oc_home_modal_wrap">

		<span class="oc_welcome_modal_close ocwp_ocp_home_data_modal_closed_event"><img src="<?php echo $groupone_asset_url . '/modules/home/assets/icons/close.svg'; ?>" /></span>
		<div class="oc-bg-wl-inner-wrap gv-text-on-default">

			<div class="oc-welcome-head">
				<h2 class="gv-mb-sm gv-heading-sm gv-mr-fluid"><?php _e( 'What data is included?', 'onecom-wp' ); ?></h2>
			</div>

			<div class="gv-notice gv-notice-info">
				<gv-icon class="gv-notice-icon" src="<?php echo $groupone_asset_url . '/modules/home/assets/icons/info.svg'; ?>"></gv-icon>
				<p class="gv-notice-content">
					<?php echo __( 'To improve our products and deliver the best customer experience, one.com would like to collect non-sensitive data from your website. You can opt-out at any time via the one.com plugin.', 'onecom-wp' ); ?>
				</p>
			</div>

			<h3 class="gv-text-sm gv-mt-sm">
				<?php echo __( 'We would like to collect the following information:', 'onecom-wp' ); ?>
			</h3>
			<ul class="gv-list-items gv-text-sm gv-mt-sm gv-mode-condensed gv-list-bullet">
				<li><?php echo __( 'Installed plugins and themes', 'onecom-wp' ); ?></li>
				<li><?php echo __( 'Number of: posts, pages, media, products, comments and users', 'onecom-wp' ); ?></li>
				<li><?php echo __( 'Use of one.com plugins and features', 'onecom-wp' ); ?></li>
				<li><?php echo __( 'Staging and multisite creation', 'onecom-wp' ); ?></li>
				<li><?php echo __( 'Help centre article visits', 'onecom-wp' ); ?></li>
				<li><?php echo __( 'Feature access and actions taken in the interface', 'onecom-wp' ); ?></li>
				<li><?php echo __( 'Upgrade start and completion', 'onecom-wp' ); ?></li>
			</ul>

			<div class="gv-text-sm gv-mt-lg">
				<?php
                    echo sprintf( __( 'Find out more about how we process your personal data in our %sPrivacy Policy%s.', 'onecom-wp' ), '<a target="_blank" href="' . esc_url( $groupone_privacy_url ) . '">', '</a>' );  ?>
			</div>

			<div class="gv-mt-lg">
				<a id="oc-consent-modal-close" href="javascript:;" class="gv-button gv-button-primary ocwp_ocp_home_data_modal_closed_event"><?php echo __( 'Got it', 'onecom-wp' ); ?></a>
			</div>
		</div>
	</div>
</div>

<!-- Consent banner toast messages upon consent update — content populated by JS -->
<div class="gv-activated oc-consent-toast-container">
	<div id="oc-consent-toast-success" class="gv-toast-container"></div>
	<div id="oc-consent-toast-failure" class="gv-toast-container"></div>
</div>