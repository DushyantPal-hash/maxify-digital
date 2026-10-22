<?php

$display_header_text   = boolval(display_header_text());
if (!defined('_NCMAZ_FRONTEND_VERSION') && !has_custom_logo() && !$display_header_text) {
	return '';
}

$blog_info    = get_bloginfo('name');
$description  = get_bloginfo('description', 'display');
$header_class = $display_header_text ? 'site-title' : 'screen-reader-text';
// 
global $ncmaz_redux_demo;
if (isset($ncmaz_redux_demo)) {
	$logoUrl = $ncmaz_redux_demo['nc-general-settings--header-media-logo']['url'];
	$logoDarkModeUrl = $ncmaz_redux_demo['nc-general-settings--header-media-logo-darkmode']['url'];
}

?>

<div>
	<?php if ($display_header_text) : ?>
		<div class="site-logo">
			<?php if (isset($logoUrl) && isset($logoDarkModeUrl)) : ?>
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<img class="block dark:hidden" src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr__('logo', 'ncmaz'); ?>">
					<img class="hidden dark:block" src="<?php echo esc_url($logoDarkModeUrl); ?>" alt="<?php echo esc_attr__('logo darkmode', 'ncmaz'); ?>">
				</a>
			<?php elseif (has_custom_logo()) : ?>
				<?php the_custom_logo(); ?>
			<?php endif; ?>

		</div>
	<?php endif; ?>

	<div class="site-branding">

		<?php if (!$display_header_text) : ?>
			<div class="site-logo">
				<?php if (isset($logoUrl) && isset($logoDarkModeUrl)) : ?>
					<a href="<?php echo esc_url(home_url('/')); ?>">
						<img class="block dark:hidden" src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo esc_attr__('logo', 'ncmaz'); ?>">
						<img class="hidden dark:block" src="<?php echo esc_url($logoDarkModeUrl); ?>" alt="<?php echo esc_attr__('logo darkmode', 'ncmaz'); ?>">
					</a>
				<?php elseif (has_custom_logo()) : ?>
					<?php the_custom_logo(); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($display_header_text) : ?>
			<?php if ($blog_info) : ?>
				<h1 class="<?php echo esc_attr($header_class); ?> text-3xl font-bold">
					<a href="<?php echo esc_url(home_url('/')); ?>">
						<?php echo esc_html($blog_info); ?>
					</a>
				</h1>
			<?php endif; ?>

			<?php if ($description) : ?>
				<p class="site-description">
					<?php echo esc_html($description);  ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>

	</div>
</div><!-- .site-branding -->