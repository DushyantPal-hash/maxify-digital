<?php
// WPIN-8922: resolve the asset base URL without depending on our own constants.
// This template is require()d on admin_footer, so a still-running pre-rename
// process (mid-update) can load it while only ONECOM_WP_URL is defined. Must
// stay inline and use core functions only — see welcome-modal.php for detail.
$groupone_asset_url = defined( 'GROUPONE_WP_URL' )
	? GROUPONE_WP_URL
	: plugin_dir_url( dirname( __DIR__, 3 ) . '/onecom-themes-plugins.php' );

$current_user = wp_get_current_user();
$user_email   = $current_user->user_email;
?>

<!-- Consent banner modal -->
<div id="oc_premium_care_overlay" style="display:none;">
	<div id="oc_login_masking_overlay_wrap" class="gv-activated oc_home_modal_wrap">

		<span class="oc_welcome_modal_close ocwp_ocp_home_request_pwpc_modal_closed_event"><img src="<?php echo $groupone_asset_url . '/modules/home/assets/icons/close.svg'; ?>" /></span>
		<div class="oc-bg-wl-inner-wrap">

			<div class="oc-welcome-head">
				<h2 class="gv-mb-sm gv-heading-sm gv-mr-fluid"><?php _e( 'Premium WP Care support', 'onecom-wp' ); ?></h2>

			</div>

			<div class="gv-text-sm gv-mt-sm">
				<?php
				echo __( 'With Premium WP Care, your WordPress site is always in expert hands.', 'onecom-wp' ) . '<br />' . __( 'We monitor your site 24/7, act immediately if something goes wrong, and send you monthly reports so you always know what’s been done.', 'onecom-wp' );
				?>
			</div>
			<div class="gv-text-sm gv-mt-sm gv-mt-lg">
				<?php echo __( "Whether you're already using the service or just curious to learn more, our team is here to help.", 'onecom-wp' ); ?>
				<?php printf( __( "We'll reply via your %sregistered email%s: %s.", 'onecom-wp' ), '<strong>', '</strong>', $user_email ); ?>
			</div>

			<div id="premium-care-request-error" class="gv-notice gv-notice-alert gv-hidden gv-mt-sm">
				<gv-icon class="gv-notice-icon" src="<?php echo $groupone_asset_url; ?>/modules/home/assets/icons/alert-icon.svg"></gv-icon>
				<p class="gv-notice-content"><?php echo __( "Couldn't send your request due to a technical issue. Please try again.", 'onecom-wp' ); ?></p>
			</div>

			<div class="gv-form-option gv-mt-sm">
				<label for="oc-premium-care-description" class="gv-label">
					<?php _e( 'Your description', 'onecom-wp' ); ?>
					<span class="gv-label-optional"><?php _e( 'optional', 'onecom-wp' ); ?></span>
				</label>
				<textarea id="oc-premium-care-description"
					type="text"
					class="gv-input gv-input-textarea"
					placeholder=""
				></textarea>
			</div>

			<div class="gv-button-group gv-mt-lg">
				<button id="oc-premium-care-modal-close" type="button" class="gv-button gv-button-cancel ocwp_ocp_home_cancel_pwpc_modal_event"><?php _e( 'Cancel', 'onecom-wp' ); ?></button>
				<button id="oc-premium-care-request-action" type="button" class="gv-button gv-button-primary ocwp_ocp_home_request_pwpc_clicked_event"><?php _e( 'Send request', 'onecom-wp' ); ?></button>
			</div>
		</div>

		<div id="modal-loader-overlay" class="modal-loader-overlay" style="display: none;">
			<div class="gv-loader-container">
				<gv-loader src="<?php echo $groupone_asset_url; ?>/modules/home/assets/icons/spinner.svg"></gv-loader>
				<p><?php _e( 'Sending request', 'onecom-wp' ); ?>...</p>
			</div>
		</div>
	</div>
</div>

<!-- Consent banner toast messages upon consent update -->
<div class="gv-activated oc-consent-toast-container">
	<div id="oc-consent-toast-success" class="gv-toast-container">
		<div class="gv-toast gv-toast-success" >
			<div class="gv-toast-content">
				<div><?php echo __( 'Your preferences were saved.', 'onecom-wp' ); ?></div>
			</div>
			<button class="gv-toast-close">
				<gv-icon src="<?php echo $groupone_asset_url; ?>/modules/home/assets/icons/close.svg"></gv-icon>
			</button>
		</div>
	</div>

	<div id="oc-consent-toast-failure" class="gv-toast-container">
		<div class="gv-toast gv-toast-alert">
			<div class="gv-toast-content">
				<div><?php echo __( 'Couldn’t save your preferences.', 'onecom-wp' ); ?></div>
			</div>
			<button class="gv-toast-close">
				<gv-icon src="<?php echo $groupone_asset_url; ?>/modules/home/assets/icons/close.svg"></gv-icon>
			</button>
		</div>
	</div>
</div>