<?php
get_header();
global $ncmaz_redux_demo;
$singlePageType = '2'; // 1 | 2 | 3
$enableSidebar = is_active_sidebar('single-sidebar');

$postFormat = get_post_format();
if (defined('_NCMAZ_FRONTEND_VERSION')) {
	$singlePageType = get_field('single_page_style');
	$enableSidebar = is_active_sidebar('single-sidebar') && get_field('show_right_sidebar');
	if (!empty($ncmaz_redux_demo)) {
		if ($ncmaz_redux_demo['nc-single-page-settings--general-single-widget-opt'] === 'enable') {
			$enableSidebar = is_active_sidebar('single-sidebar');
		}
		if ($ncmaz_redux_demo['nc-single-page-settings--general-single-widget-opt'] === 'disable') {
			$enableSidebar = false;
		}
	}
}

$mainClasses = "pt-8 lg:pt-16 ";
if ($postFormat === 'video') {
	$mainClasses = "nc-PageSingleTemplateVideo ";
}

if ($singlePageType === '3') {
	$mainClasses = "nc-PageSingleTemplate3 ";
}
?>

<main id="primary" class="nc-PageSingleTemplate2 relative <?php echo esc_attr($mainClasses); ?>">

	<?php
	while (have_posts()) :
		the_post();

		if (defined('_NCMAZ_FRONTEND_VERSION')) {
			if ($postFormat === 'gallery') {
				get_template_part('template-parts/single-header/single-header-gallery');
			} else if ($postFormat === 'video') {
				get_template_part('template-parts/single-header/single-header-video');
			} else if ($postFormat === 'audio') {
				get_template_part('template-parts/single-header/single-header-audio');
			} else {
				if (!$enableSidebar) {
					// ============== HEADER ==============
					if ($singlePageType == '1') {
						get_template_part('template-parts/single-header/single-header-1');
					} else if ($singlePageType == '2') {
						get_template_part('template-parts/single-header/single-header-2', null, ['has_sidebar' => false]);
					} else if ($singlePageType == '3') {
						get_template_part('template-parts/single-header/single-header-3');
					} else {
						get_template_part('template-parts/single-header/single-header-1');
					}
				} else {
					if ($singlePageType == '3') {
						get_template_part('template-parts/single-header/single-header-3');
					} else {
						get_template_part('template-parts/single-header/single-header-2', null, ['has_sidebar' => true]);
					}
				}
			}
		} else {
			get_template_part('template-parts/single-header/single-header-2', null, ['has_sidebar' => $enableSidebar]);
		}


		// ENTRY CONTENT
		echo '<div class="container my-10 ' . esc_html($enableSidebar ? "flex flex-col lg:flex-row" : "") . '">';
		if ($enableSidebar) : ?>
			<div class="w-full lg:w-3/5 xl:w-2/3 xl:pr-20">
				<?php get_template_part('template-parts/content', get_post_type(), ['has_sidebar' => $enableSidebar]); ?>
			</div>
			<div class="w-full mt-12 lg:mt-0 lg:w-2/5 lg:pl-10 xl:pl-0 xl:w-1/3 space-y-7">
				<?php dynamic_sidebar('single-sidebar'); ?>
			</div>
		<?php else : ?>
			<?php get_template_part('template-parts/content', get_post_type(),  ['has_sidebar' => $enableSidebar]); ?>
	<?php endif;
		echo '</div>';


		// RELATED POSTS
		get_template_part('template-parts/single-page/single-related-posts', get_post_type());

	endwhile; // End of the loop.
	?>



</main>

<?php

// -------------------- UPDATE COUNT VIEW --------------------
if (is_single() &&  defined('_NCMAZ_FRONTEND_VERSION')) {
	ncmaz_update_count_views(get_the_ID());
}

get_footer();
