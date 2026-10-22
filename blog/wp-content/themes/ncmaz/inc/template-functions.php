<?php

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package ncmaz
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function ncmaz_body_classes($classes)
{
	// Adds a class of hfeed to non-singular pages.
	if (!is_singular()) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if (!is_active_sidebar('sidebar-1')) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter('body_class', 'ncmaz_body_classes');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function ncmaz_pingback_header()
{
	if (is_singular() && pings_open()) {
		printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
	}
}
add_action('wp_head', 'ncmaz_pingback_header');


// 
//  ADD CLASS CUSTOMM TEMPALTE TO ADMIN EDITOR
add_filter('admin_body_class', function ($classes) {
	global $pagenow;
	if ($pagenow === 'post.php' && isset($_GET['post'])) {
		$pageTemplate = get_page_template_slug($_GET['post']);
		if ($pageTemplate === 'page_custom.php') {
			$classes .= ' ncmaz-custom-page ';
			// return ngay de khong chua class ncmmaz-nomal
			return $classes;
		}
	}
	return $classes . ' ncmaz-not-custom-page ';
});

// 
