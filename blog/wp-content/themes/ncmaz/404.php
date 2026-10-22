<?php
get_header();
?>

<main id="primary" class="site-main">
	<div class="nc-Page404">
		<div class="container relative pt-5 pb-16 lg:pb-20 lg:pt-5">
			<header class="text-center max-w-2xl mx-auto space-y-2">
				<div class="nc-NcImage " data-nc-id="NcImage">
					<img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/404.png') ?>" class="w-full" alt="<?php echo esc_attr__('Page 404 Image', 'ncmaz') ?>">
				</div>
				<span class="block text-sm text-neutral-800 sm:text-base dark:text-neutral-200 tracking-wider font-medium">
					<?php echo esc_html__('THE PAGE YOU WERE LOOKING FOR DOESN\'T EXIST.', 'ncmaz'); ?>
				</span>
				<div class="pt-8">
					<a class="nc-Button relative h-auto inline-flex items-center justify-center rounded-full transition-colors text-sm sm:text-base font-medium px-4 py-3 sm:px-6  ttnc-ButtonPrimary disabled:bg-opacity-70 bg-primary-6000 hover:bg-primary-700 text-neutral-50  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-6000 dark:focus:ring-offset-0" rel="noopener noreferrer" href="<?php echo esc_url(get_home_url('/')); ?>">
						<?php echo esc_html__('Return Home Page', 'ncmaz'); ?>
					</a>
				</div>
			</header>
		</div>
	</div>

</main>

<?php
get_footer();
