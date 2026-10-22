<!--Staging entry box-->
<?php
$staging_id = '';
if ( ! empty( $clones ) ) {
	$staging_id = array_key_first( $clones );
}
?>
<div id="staging_entry" data-staging-id="<?php echo esc_attr( $staging_id ); ?>">
	<div class="one-staging-details card-2">
		<div class="one-staging-site-info box one-card-staging-create-info">
			<div class="oc-column oc-left-column">
				<p class="gv-text-lg gv-text-bold gv-mb-md">
					<?php _e('Your staging environment','onecom-wp') ?>
				</p>
				<div class="gv-surface-bright gv-p-lg gv-mb-md">
					<ul class="gv-list-items gv-list-bullet">
						<li><?php _e('When you copy from the staging to the live environment, the live website will get replaced with a snapshot of this staging website.','onecom-wp') ?></li>
						<li><?php _e('The login details for the staging backend are the same as for the live website.','onecom-wp') ?></li>
					</ul>
				</div>
			</div>
			<div class="gv-grid gv-gap-md gv-tab-lg-grid-cols-2">
				<a href="javascript:void(0);" class="gv-shortcut oc-open-modal ocwp_ocp_staging_rebuild_event" data-modal-target=".oc-rebuild-modal" data-cu-confirm-journey-event="ocwp_ocp_staging_rebuild_confirmed_event">
					<gv-icon aria-hidden="true" src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/redo.svg' ); ?>"></gv-icon>
					<div class="gv-content">
						<h3 class="gv-title"><?php _e('Rebuild staging','onecom-wp') ?></h3>
						<p><?php _e('Overwrite the files and database of your current staging website with the files and database of your live website.','onecom-wp') ?></p>
					</div>
					<gv-icon class="gv-align-bottom" aria-hidden="true" src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/arrow_forward.svg' ); ?>"></gv-icon>
				</a>
				<a href="javascript:void(0);" class="gv-shortcut oc-open-modal ocwp_ocp_staging_deletion_initiated_event" data-modal-target=".oc-delete-modal" data-cu-confirm-journey-event="ocwp_ocp_staging_delete_confirmed_event">
					<gv-icon aria-hidden="true" src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/delete.svg' ); ?>"></gv-icon>
					<div class="gv-content">
						<h3 class="gv-title"><?php _e('Delete staging','onecom-wp') ?></h3>
						<p><?php _e('Delete the staging environment including all its files.','onecom-wp') ?></p>
					</div>
					<gv-icon class="gv-align-bottom" aria-hidden="true" src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/arrow_forward.svg' ); ?>"></gv-icon>
				</a>
			</div>
		</div>
	</div>
</div>