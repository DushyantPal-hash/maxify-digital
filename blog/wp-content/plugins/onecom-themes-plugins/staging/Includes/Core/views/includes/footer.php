</div> <!-- inner gv-p-lg -->


<div id="onecom-staging-error-wrapper">
    <div id="onecom-staging-error-details"></div>
</div>

<!--// Rebuilding Modal -->
<div class="gv-modal oc-rebuild-modal gv-hidden">
    <div class="gv-modal-content">
        <button class="gv-modal-close oc-close-modal" data-modal-target=".oc-rebuild-modal">
            <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/close-modal.svg' ?>"></gv-icon>
        </button>
        <div class="gv-modal-body">
            <h2 class="gv-modal-title"><?php _e( 'Rebuild staging', 'onecom-wp' ); ?>?</h2>
            <p class="gv-text-sm">
				<?php _e( 'This will overwrite your staging website with a copy of the files and database from your live website. All changes made in your staging website will be lost.', 'onecom-wp' ); ?>
            </p>
        </div>
        <div class="gv-button-group">
            <button class="gv-button-cancel gv-button  oc-close-modal cancel-done" data-modal-target=".oc-rebuild-modal"><?php _e( 'Cancel', 'onecom-wp' ); ?></button>
            <button class="gv-button-destructive gv-button one-button-update-staging-confirm ocwp_ocp_staging_rebuild_confirmed_event confirm-done"><?php _e( 'Rebuild staging', 'onecom-wp' ); ?></button>
        </div>
    </div>
</div>

<!--Delete Modal-->
<div class="gv-modal oc-delete-modal gv-hidden">
    <div class="gv-modal-content">
        <button class="gv-modal-close oc-close-modal" data-modal-target=".oc-delete-modal">
            <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/close-modal.svg' ?>"></gv-icon>
        </button>
        <div class="gv-modal-body">
            <h2 class="gv-modal-title"><?php _e( 'Delete staging', 'onecom-wp' ); ?>?</h2>
            <p class="gv-text-sm">
				<?php _e( 'Your staging site including all its files will be deleted. This action cannot be undone, but you can create a new staging site later.', 'onecom-wp' ); ?>
            </p>
        </div>
        <div class="gv-button-group">
            <button class="gv-button-cancel gv-button  oc-close-modal cancel-done" data-modal-target=".oc-delete-modal"><?php _e( 'Cancel', 'onecom-wp' ); ?></button>
            <button class="gv-button-destructive gv-button one-button-delete-staging-confirm ocwp_ocp_staging_deletion_confirmed_event confirm-done" ><?php _e( 'Delete staging', 'onecom-wp' ); ?></button>
        </div>
    </div>
</div>



<!-- STAGING TO LIVE MODAL -->
<div id="staging-copy-confirmation" class=" gv-modal oc-copy-staging-modal gv-hidden">
    <div class="gv-modal-content">
        <button class="gv-modal-close oc-close-modal" data-modal-target=".oc-copy-staging-modal">
            <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/close-modal.svg' ?>"></gv-icon>
        </button>
        <div class="gv-modal-body">
            <h2 class="gv-modal-title"><?php _e( 'Copy staging to live', 'onecom-wp' ); ?>?</h2>
            <p class="gv-text-sm">
				<?php _e( 'This will overwrite your live website with a copy of the files and database from your staging website.', 'onecom-wp' ); ?>
            </p>
        </div>
        <div class="gv-button-group">
            <button class="gv-button-cancel gv-button  oc-close-modal cancel-done" data-modal-target=".oc-copy-staging-modal"><?php _e( 'Cancel', 'onecom-wp' ); ?></button>
            <button id="one-button-copy-to-live-confirm" class="gv-button-destructive gv-button one-button-copy-to-live-confirm ocwp_ocp_staging_copy_confirm_event confirm-done" ><?php _e( 'Copy staging to live', 'onecom-wp' ); ?></button>
        </div>
    </div>
</div>

<!-- loader -->
<div class="loading-overlay fullscreen-loader">
    <div class="gv-loader-container">
        <gv-loader src="<?php echo GROUPONE_WP_URL . '/assets/images/spinner.svg' ?>"></gv-loader>
    </div>
</div>
</div> <!-- gv-activated -->