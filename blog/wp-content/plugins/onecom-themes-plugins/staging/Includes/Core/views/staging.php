<?php
$existing_live = get_option( 'onecom_staging_existing_live' );
?>
<div class="wrap_inner inner one_wrap" id="onecom-wrap">
	<div id="ajax-response-notice" class="gv-notice gv-hidden gv-mb-lg">
		<gv-icon id="ajax-response-icon" class="gv-notice-icon" src=""></gv-icon>
		<p id="ajax-response-content" class="gv-notice-content"></p>
		<button class="gv-notice-close">
			<gv-icon src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/close-modal.svg' ); ?>"></gv-icon>
		</button>

	</div>
    <p class="gv-text-lg gv-text-bold gv-mb-md"> <?php _e( 'Your staging environment', 'onecom-wp' ); ?></p>
    <div class="gv-content-container">
        <div class="gv-surface-bright gv-p-lg">
			<?php
			if ( ! empty( $existing_live ) && isset( $existing_live->directoryName ) ) {
				?>
                <ul class="gv-list-items gv-list-bullet">
                    <li><?php _e('Your staging website is a snapshot of your live website. Here you can test changes without affecting your live website. ','onecom-wp') ?></li>
                    <li><?php _e('When you copy from the staging to the live environment, the live website will get replaced with a snapshot of this staging website.','onecom-wp') ?></li>
                    <li><?php _e('The login details for the staging backend are the same as for the live website.','onecom-wp') ?></li>
                </ul>
				<?php
			} else {
				?>
                <p><?php _e( 'Live website details not found. Can not copy staging into live!', 'onecom-wp' ); ?></p>
			<?php } ?>
        </div>
        <div class="gv-notice gv-notice-warning gv-mode-condensed">
            <gv-icon class="gv-notice-icon" src="<?php echo GROUPONE_WP_URL . 'assets/images/warning.svg'; ?>"></gv-icon>
            <p class="gv-notice-content"><?php _e( 'Please note: Plugins that are URL-sensitive may not work as expected on the staging site.', 'onecom-wp' ); ?></p>
        </div>
    </div>
</div>

<div class="loading-overlay fullscreen-loader deploy-loader">
    <div class="loading-overlay-content">
        <div class="gv-loader-container">
            <gv-loader src="<?php echo GROUPONE_WP_URL . '/assets/images/spinner.svg' ?>"></gv-loader>
            <p><?php _e( 'Please wait, while we are copying staging into live.', 'onecom-wp' ); ?></p>
        </div>
    </div>
</div><!-- loader -->