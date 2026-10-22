<div class="gv-activated onecom-staging-wrap">
    <div class="gv-p-fluid">
		<div class="gv-flex gv-align-center gv-justify-between">
			<h3> <?php _e( 'Staging' , 'onecom-wp' ); ?></h3>
			<?php
			if ( ! is_multisite() ):
				$clone = get_option( 'onecom_staging_existing_staging' );
				$existing_live = get_option( 'onecom_staging_existing_live' );
				$cloneExists = self::checkCloneExists( $clone );
				$broken_staging = ( ! empty( $clone ) && ! $cloneExists );

				if ( ! empty( $clone ) && is_array( $clone ) ) {
					foreach ( $clone as $clone_key => $clone_data ) {
						$clone_url = isset( $clone_data['url'] ) ? $clone_data['url'] : '';
						// Use $url as needed
						break;
					}
				} ?>

			<?php if ( empty( $clone ) && (bool)$is_staging !== true && empty( $existing_live )) : ?>
				<?php if ( isPremium() ) : ?>
				<div class="gv-max-mob-hidden create-staging-wrapper gv-flex">
                    <button type="button" class="gv-button gv-button-primary one-button-create-staging ocwp_ocp_staging_created_event oc-create-stg-header">
                        <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/add.svg'; ?>"></gv-icon>
                        <span><?php _e( 'Create staging site', 'onecom-wp' ); ?></span>
                    </button>
				</div>
				<?php endif; ?>

			<?php elseif ( isset( $is_staging ) && (bool) $is_staging === true && ! empty( $existing_live ) && isset( $existing_live->directoryName ) ) : ?>
                <div class="gv-flex gv-align-center gv-max-mob-hidden">
                    <button id="deploy_to_live" type="button" class="gv-button gv-button-secondary  gv-mr-md oc-open-modal ocwp_ocp_staging_copy_intiated_event" data-modal-target=".oc-copy-staging-modal" data-live-id="<?php echo $existing_live->directoryName; ?>">
                        <span><?php _e( 'Copy staging to live', 'onecom-wp' ); ?></span>
                    </button>
                    <a href="<?php echo $existing_live->url . 'wp-admin'; ?>" class="gv-button gv-button-primary ocwp_ocp_staging_back_to_live" target="_blank">
                        <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/arrow_left_alt.svg'; ?>"></gv-icon>
                        <span><?php _e( 'Back to live site', 'onecom-wp' ); ?></span>
                    </a>
                </div>

			<?php else : ?>
				<div class="gv-flex gv-align-center gv-max-mob-hidden oc-stg-btns <?php echo $broken_staging === true ? 'gv-hidden' : ''; ?>">                    <a href="<?php echo $clone_url; ?>" target="_blank" class="gv-button gv-button-secondary ocwp_ocp_staging_viewed_event gv-mr-md">
                        <span><?php _e( 'View your site', 'onecom-wp' ); ?></span>
                        <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/open_in_new.svg'; ?>"></gv-icon>
                    </a>
                    <a href="javascript:void(0);"
                       data-loginUrl="<?php echo trailingslashit( $clone_url ); ?>wp-login.php"
                       data-stgUrl="<?php echo trailingslashit( $clone_url ); ?>"
                       class="gv-button gv-button-primary loginStaging ocwp_ocp_staging_logged_in_event"
                    >
                        <span><?php _e( 'Log in to staging', 'onecom-wp' ); ?></span>
                        <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/open_in_new.svg'; ?>"></gv-icon>
                    </a>
                </div>
			<?php endif; ?>
			<?php endif; ?>


        </div>
        <div class="gv-mt-sm gv-text-sm gv-mb-fluid" style="max-width:700px">
			<?php
			if ( isset( $is_staging ) && (bool) $is_staging === true ) {
				$link = groupone_generic_locale_link( $request = 'staging_guide' , get_locale() );
				echo sprintf(
					__('Your staging site is a snapshot of your live website. In Staging, you can test changes without affecting your live website.  %sLearn more%s', 'onecom-wp'),
					'<a href="'.$link.'" target="_blank" class="ocwp_ocp_staging_help_guide_clicked_event">',
					'</a>'
				);
				?>
				<?php
			} else {
				$link = groupone_generic_locale_link( $request = 'staging_guide' , get_locale() );
				echo sprintf(
					__('A staging site is a copy of your live website, where you can test new plugins and themes without affecting your live website. Only one staging environment can be created for each website. %sLearn more%s', 'onecom-wp'),
					'<a href="'.$link.'" target="_blank" class="ocwp_ocp_staging_help_guide_clicked_event">',
					'</a>'
				);
			}
			?>
        </div>
		<?php if ( ! is_multisite() ): ?>
		<?php if ( empty( $clone ) && (bool)$is_staging !== true && empty( $existing_live )) : ?>
		<?php if ( isPremium() ) : ?>
			<div class="gv-desk-hidden gv-tab-hidden gv-mb-lg create-staging-wrapper gv-flex gv-flex-col">
        <button type="button" class="gv-button gv-button-primary one-button-create-staging gv-mode-condensed gv-mb-lg oc-create-stg-header ocwp_ocp_staging_created_event">
            <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/add.svg'; ?>" ></gv-icon>
            <span><?php _e( 'Create staging site' , 'onecom-wp' ) ?></span>

        </button>
			</div>
        <?php endif; ?>
		<?php elseif ( isset( $is_staging ) && (bool) $is_staging === true && ! empty( $existing_live ) && isset( $existing_live->directoryName ) ) : ?>
        <div class="gv-flex gv-align-center gv-flex-col gv-desk-hidden gv-tab-hidden gv-mode-condensed">
            <a href="<?php echo $existing_live->url . 'wp-admin'; ?>" class="gv-button gv-button-primary ocwp_ocp_staging_back_to_live gv-mb-sm " target="_blank">
                <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/arrow_left_alt.svg'; ?>"></gv-icon>
                <span><?php _e( 'Back to live site', 'onecom-wp' ); ?></span>
            </a>
            <button id="deploy_to_live" type="button" class="gv-button gv-button-secondary  gv-mb-md oc-open-modal ocwp_ocp_staging_copy_intiated_event" data-modal-target=".oc-copy-staging-modal" data-live-id="<?php echo $existing_live->directoryName; ?>">
                <span><?php _e( 'Copy staging to live', 'onecom-wp' ); ?></span>
            </button>
        </div>
		<?php else : ?>
                <div class="gv-flex gv-flex-col gv-align-center gv-desk-hidden gv-tab-hidden gv-mode-condensed gv-mb-lg oc-stg-btns <?php echo $broken_staging === true ? 'gv-hidden' : ''; ?>">
                    <a href="javascript:void(0);"
                       data-loginUrl="<?php echo trailingslashit( $clone_url ); ?>wp-login.php"
                       data-stgUrl="<?php echo trailingslashit( $clone_url ); ?>"
                       class="gv-button gv-order-first gv-button-primary loginStaging ocwp_ocp_staging_logged_in_event gv-mb-sm"
                    >
                        <span><?php _e( 'Log in to staging', 'onecom-wp' ); ?></span>
                        <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/open_in_new.svg'; ?>"></gv-icon>
                    </a>
                    <a href="<?php echo $clone_url; ?>" target="_blank" class="gv-button gv-button-secondary ocwp_ocp_staging_viewed_event">
                        <span><?php _e( 'View your site', 'onecom-wp' ); ?></span>

                        <gv-icon src="<?php echo GROUPONE_WP_URL . 'assets/images/open_in_new.svg'; ?>"></gv-icon>
                    </a>

                </div>
        <?php endif; ?>
        <?php endif; ?>
