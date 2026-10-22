<?php  $broken_staging = ( ! empty( $clones ) && ! $cloneExists );?>
	<div id="onecom-wrap">
        <?php if( $broken_staging === true && ! empty( $clones ) ):?>
        <div id="oc-staging-broken" class="gv-notice gv-mb-lg gv-notice-alert">
            <gv-icon class="gv-notice-icon gv-alert-icon" src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/notice-error.svg' ); ?>"></gv-icon>
            <div class="gv-notice-content">
                <div class="gv-notice-title"><?php _e('Staging site broken', 'onecom-wp') ?></div>
                <p><?php _e('It looks like your staging site is broken due to missing database tables and/or directories. You can fix this by rebuilding or deleting your staging site.', 'onecom-wp') ?></p>
            </div>
        </div>
		<?php endif; ?>
		<div id="ajax-response-notice" class="gv-notice gv-hidden gv-mb-lg">
			<gv-icon id="ajax-response-icon" class="gv-notice-icon" src=""></gv-icon>
			<p id="ajax-response-content" class="gv-notice-content"></p>
			<button class="gv-notice-close">
				<gv-icon src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/close-modal.svg' ); ?>"></gv-icon>
			</button>

		</div>
					<?php
					if ( ! empty( $clones ) ) :
						require $this->path . 'views/ajax/staging_details.php';
						?>
						<div id="staging-create" class="one-card-staging-create card-1 hide">
					<?php else : ?>
						<div id="staging-create" class="one-card-staging-create card-1">
					<?php endif; ?>
						<div class="gv-surface-bright gv-p-lg">
							<div class="gv-flex gv-flex-col gv-align-center gv-p-fluid <?php echo !isPremium() ? 'gv-pos-relative' : ''; ?>">
								<?php if ( !isPremium() ) : ?>
									<div class="gv-oc-overlay">
										<gv-icon src="<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/mwp-lock.svg' ); ?>"></gv-icon>

										<p class="gv-text-sm gv-text-bold"><?php _e( 'This is a Managed WP feature', 'onecom-wp' ); ?></p>
										<p class="gv-text-sm"><?php _e( 'Upgrade now to get access to Staging and many other features.', 'onecom-wp' ); ?></p>

										<button
											type="button"
											class="gv-button gv-button-primary oc-mwp-modal gv-mt-md gv-mode-condensed"
											data-upsell=""
										>
											<?php _e('Upgrade now' , 'onecom-wp') ?>
											<gv-icon src=<?php echo esc_url( GROUPONE_WP_URL . '/assets/images/open_in_new.svg' ); ?>></gv-icon>
										</button>
									</div>
								<?php endif; ?>

								<h5 class="gv-heading-sm gv-text-center"><?php _e('No staging site yet', 'onecom-wp') ?></h5>
								<p class="gv-text-md gv-mt-sm gv-text-center"><?php _e('Create a staging environment of your site to try out new plugins, themes and customisations.', 'onecom-wp') ?></p>
								<div class="gv-text-center">
								<button type="button" class="gv-button gv-button-primary one-button-create-staging gv-mt-lg ocwp_ocp_staging_created_event">
									<gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/add.svg'; ?>" ></gv-icon>
									<span><?php _e( 'Create staging site' , 'onecom-wp' ) ?></span>

								</button>
								</div>
							</div>
							</div>
						</div>
						<div class="loading-overlay fullscreen-loader update-loader">
							<div class="loading-overlay-content">
								<div class="gv-loader-container">
									<gv-loader src="<?php echo GROUPONE_WP_URL . '/assets/images/spinner.svg' ?>"></gv-loader>
									<p><?php _e( 'Please wait, while we are updating staging site.', 'onecom-wp' ); ?></p>
								</div>
							</div>
						</div><!-- loader -->
						<div class="loading-overlay fullscreen-loader delete-loader">
							<div class="loading-overlay-content">
								<div class="gv-loader-container">
									<gv-loader src="<?php echo GROUPONE_WP_URL . '/assets/images/spinner.svg' ?>"></gv-loader>
									<p><?php _e( 'Please wait, while we are deleting staging site.', 'onecom-wp' ); ?></p>
								</div>
							</div>
						</div><!-- loader -->
	<div class="loading-overlay fullscreen-loader new-staging">
						<div class="loading-overlay-content">
							<div class="gv-loader-container">
								<gv-loader src="<?php echo GROUPONE_WP_URL . '/assets/images/spinner.svg' ?>"></gv-loader>
								<p><?php _e( 'Please wait, while we are copying your live site to the staging site.', 'onecom-wp' ); ?></p>
							</div>
						</div>
					</div><!-- loader -->
	<?php do_action( 'oc_print_scripts' ); ?>