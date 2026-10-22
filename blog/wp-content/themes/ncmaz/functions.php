<?php

if (!defined('_NCMAZ_THEME_VERSION')) {
	define('_NCMAZ_THEME_VERSION', '2.2.5');
}
define('_NCMAZ_THEME_DIR_PATH', get_template_directory(__FILE__));

// 
function ncmaz_var_dump($value)
{
	echo '<pre class="text-red-700"><code>';
	echo ('-----------start--ncmaz_vardump-----------------');
	echo ('<br />');
	var_dump($value);
	echo ('<br />');
	echo ('-----------end--ncmaz_vardump-----------------');
	echo '</code></pre>';
};
function ncmaz_var_export($value)
{
	echo '<pre class="text-red-700"><code>';
	echo ('-----------start--ncmaz_varexport-----------------');
	echo ('<br />');
	var_export($value);
	echo ('<br />');
	echo ('-----------end--ncmaz_varexport-----------------');
	echo '</code></pre>';
};

// 
if (!function_exists('ncmaz_setup')) :
	function ncmaz_setup()
	{
		load_theme_textdomain('ncmaz', get_template_directory() . '/languages');

		add_theme_support('automatic-feed-links');

		add_theme_support('title-tag');

		add_theme_support('post-thumbnails');

		register_nav_menus(
			array(
				'primary' => esc_html__('Primary', 'ncmaz'),
			)
		);

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'ncmaz_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		/** post formats */
		add_theme_support('post-formats',  ['gallery', 'video', 'audio']);

		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		// OTHER ============
		add_theme_support('wp-block-styles');
		add_theme_support("responsive-embeds");
		add_theme_support("align-wide");
		if (!isset($content_width)) {
			$content_width = 768;
		}
		// END OTHER ============

	}
endif;
add_action('after_setup_theme', 'ncmaz_setup');

// CUSTOM PASSWORD FORM
function ncmaz_password_form($output, $post = 0)
{
	$post   = get_post($post);
	$label  = 'pwbox-' . (empty($post->ID) ? wp_rand() : $post->ID);
	$output = '<p class="post-password-message">' . esc_html__('This content is password protected. Please enter a password to view.', 'ncmaz') . '</p>
	<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" class="post-password-form" method="post">
	<label class="post-password-form__label" for="' . esc_attr($label) . '">' . esc_html_x('Password', 'Post password form', 'ncmaz') . '</label><input class="post-password-form__input" name="post_password" id="' . esc_attr($label) . '" type="password" size="20" /><input type="submit" class="post-password-form__submit" name="' . esc_attr_x('Submit', 'Post password form', 'ncmaz') . '" value="' . esc_attr_x('Enter', 'Post password form', 'ncmaz') . '" /></form>
	';
	return $output;
}
add_filter('the_password_form', 'ncmaz_password_form', 10, 2);

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * NCmaz enque
 */
require get_template_directory() . '/inc/ncmaz-enqueue-scripts.php';

/**
 * NCmaz SIDEBAE REGISTER
 */
require get_template_directory() . '/inc/ncmaz-register-widgets.php';

/**
 * NCmaz requiredPlugins
 */
require get_template_directory() . '/requiredPlugins.php';

/**
 * NCmaz requiredPlugins
 */
require get_template_directory() . '/ncmaz-demo-importer.php';


/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}
