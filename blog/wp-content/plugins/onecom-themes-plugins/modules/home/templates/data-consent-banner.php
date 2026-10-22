<?php
// WPIN-8922: resolve the asset base URL without depending on our own constants.
// This template is require()d on admin_footer, so a still-running pre-rename
// process (mid-update) can load it while only ONECOM_WP_URL is defined. Must
// stay inline and use core functions only — see welcome-modal.php for detail.
$groupone_asset_url = defined( 'GROUPONE_WP_URL' )
	? GROUPONE_WP_URL
	: plugin_dir_url( dirname( __DIR__, 3 ) . '/onecom-themes-plugins.php' );

// Prepare translated strings first
$title     = __( 'Help us improve your website experience', 'onecom-wp' );
$info      = __( 'Allow anonymous usage analytics to help us improve your services and recommendations. We only collect non-sensitive data.', 'onecom-wp' );
$link_text = __( 'Learn more', 'onecom-wp' );

// Begin output buffering
ob_start();
?>
<div id="oc-data-consent-banner" class="gv-activated">
	<div class="gv-notice gv-notice-info gv-items-center">
		<gv-icon class="gv-notice-icon" src="<?php echo esc_url( $groupone_asset_url . '/modules/home/assets/icons/info.svg' ); ?>"></gv-icon>
		<div class="gv-flex-grow">
			<div class="gv-notice-content oc-consent-heading">
				<?php echo esc_html( $title ); ?>
			</div>
			<div class="gv-notice-content">
				<?php echo esc_html( $info ); ?>
				<a class="oc_consent_modal_show ocwp_ocp_consent_banner_modal_link_clicked_event" href="javascript:void(0);">
					<?php echo esc_html( $link_text ); ?>
				</a>.
			</div>
		</div>
        
        <button type="button" class="oc-data-consent-decline gv-button gv-button-secondary gv-tab-mode-condensed gv-flex-shrink-0 ocwp_ocp_consent_banner_opted_out_event"><?php echo __( 'Decline', 'onecom-wp' ); ?></button>
        <button type="button" class="oc-data-consent-accept gv-button gv-button-primary gv-tab-mode-condensed gv-flex-shrink-0 ocwp_ocp_consent_banner_accepted_event"><?php echo __( 'Allow', 'onecom-wp' ); ?></button>
        <button class="gv-notice-close oc-data-consent-dismiss">
            <gv-icon src="<?php echo $groupone_asset_url; ?>/modules/home/assets/icons/close.svg"></gv-icon>
        </button>
	</div>
</div>
<?php
$consent_banner_html = ob_get_clean();
?>
<script>
	jQuery(function($) {
		const bannerHTML = <?php echo wp_json_encode( $consent_banner_html ); ?>;
		$('#wpcontent').append(bannerHTML);
	});
</script>