<div id="onestaging-clonepage-wrapper">

	<!-- Page Header -->
	<?php require_once $this->path . 'views/includes/header.php'; ?>

	<div class="gv-surface-bright gv-p-fluid gv-text-center">
		<div class="gv-flex gv-flex-col gv-align-center">
			<h5 class="gv-heading-sm"><?php _e('Staging feature not available', 'onecom-wp') ?></h5>
			<p class="gv-text-md gv-text-center gv-mt-sm"><?php _e('The one.com Staging feature is not available for WordPress websites that:', 'onecom-wp') ?></p>
			<ul class="gv-list-items gv-list-none gv-text-center gv-mt-sm gv-flex gv-flex-col gv-align-center">
				<li class="gv-mb-0"><?php _e('Are part of a Multisite installation', 'onecom-wp') ?></li>
				<li class="gv-mb-0"><?php _e('Have their files stored on a subdirectory but are running from the root domain', 'onecom-wp') ?></li>
			</ul>
		</div>
	</div>
</div>
</div>