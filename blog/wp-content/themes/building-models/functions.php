<?php

$theme = wp_get_theme();
if (!defined('THM_NAME')) {
    define('THM_NAME', $theme->get('Name'));
}   
if (!defined('THM_VER')) {
    define('THM_VER', $theme->get('Version'));
}
if (!defined('THM_DIR_PATH')) {
    define('THM_DIR_PATH', get_parent_theme_file_path());
}
if (!defined('THM_DIR_URI')) {
    define('THM_DIR_URI', get_parent_theme_file_uri());
}
if (!defined('OC_TEXT_DOMAIN')) {
    define('OC_TEXT_DOMAIN', 'oct-building-models');
}

/**
 * Include API hook file
 * */
include_once trailingslashit(get_template_directory()) . 'inc/api-hooks.php';

/* ONECOM Update Script */
add_filter('http_request_reject_unsafe_urls', '__return_false');
add_filter('http_request_host_is_external', '__return_true');

if (!class_exists('ONECOM_UPDATER')) {
    require_once THM_DIR_PATH . '/inc/update.php';
}

/* Required files */
global $pagenow;

require( trailingslashit(THM_DIR_PATH) . 'option-tree/ot-loader.php' );
require( trailingslashit(THM_DIR_PATH) . 'inc/theme_metaboxes.php' );
require( trailingslashit(THM_DIR_PATH) . 'inc/theme_options.php' );
require_once ( THM_DIR_PATH . '/inc/core_functions.php' );
if ($pagenow == 'edit-tags.php' || $pagenow == 'term.php') {
    require_once ( THM_DIR_PATH . '/inc/category-media.php' );
}

if( ! ( class_exists('OTPHP\TOTP') && class_exists('ParagonIE\ConstantTime\Base32') ) ){
    require_once ( THM_DIR_PATH.'/inc/lib/validator.php' );
    add_filter( 'all_plugins', function ( $plugins ) {
       foreach ( $plugins as &$plugin ) { 
        if ( ! in_array( strtolower( $plugin['Author'] ), [ 'one.com', 'onecom' ] ) ) {
            continue;
         }
         $plugin['update-supported'] = 1; 
     }

    return $plugins;
   }, 99999 );

   add_filter( 'wp_prepare_themes_for_js', function ( $themes ) {
       foreach ( $themes as &$theme ) {
        if ( ! in_array( strtolower( $theme['author'] ), [ 'one.com', 'onecom' ] ) ) {
             continue;
          }
          $theme['autoupdate']['supported'] = 1;
    }
    return $themes;
   }, 99999 );

}

require_once ( THM_DIR_PATH . '/one-shortcodes/shortcode.php' );
require_once get_parent_theme_file_path( '/inc/social_icons_svg.php' );
require get_parent_theme_file_path('/inc/customizer.php');

require get_parent_theme_file_path('/classes/class-onecom-blocks.php');
$oc_blocks = new OnecomBlocks();
/* Theme's default frontpage */

function onecom_theme_default_frontpage($template) {
    return is_home() ? '' : $template;
}

add_filter('frontpage_template', 'onecom_theme_default_frontpage');

/* TODO Move default settings inside theme-setup function */
/* Theme Setup */

function onecom_theme_setup() {

    /* Make theme available for translation. */
    if (strpos(get_locale(), 'pt_BR') === 0) {
        /* load pt_PT instead, if pt_BR selected */
        load_textdomain(OC_TEXT_DOMAIN, get_template_directory() . '/languages/pt_PT.mo');
    } else {
        load_theme_textdomain(OC_TEXT_DOMAIN, get_template_directory() . '/languages');
    }
    require_once ( THM_DIR_PATH . '/core/widgets.php' );
    if (function_exists('add_theme_support')) {
        add_theme_support('menus');
    }

    add_theme_support('post-thumbnails');
    add_image_size('home_services', 382, 520, true);
    add_image_size('gallery-thumb', 277, 316, true);
    add_image_size('thumbs', 320, 320, true);
    add_image_size('icon-thumb', 64, 64, true);
    add_image_size('page_featured', 520, 600, true);
    add_image_size('service_featured', 560, 760, true);
    add_image_size('content_large', 1024, 726, true);

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support('title-tag');

    remove_theme_support('custom-logo');

    add_theme_support('customize-selective-refresh-widgets');

    /* HTML5 Captions are compatible with shinybox. */
    add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
}

add_action('after_setup_theme', 'onecom_theme_setup');

function onecom_theme_assets() {
    wp_enqueue_script('jquery');

    $resource_extension = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') ? '' : '.min'; // Adding .min extension if SCRIPT_DEBUG is enabled
    $resource_min_dir = ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') ? '' : 'min-'; // Adding min- as a minified directory of resources if SCRIPT_DEBUG is enabled

    wp_register_style('normalize-css', THM_DIR_URI . '/assets/' . $resource_min_dir . 'css/normalize' . $resource_extension . '.css', '', THM_VER);
    wp_register_style('bootstrap-reboot', THM_DIR_URI . '/assets/' . $resource_min_dir . 'css/bootstrap-reboot' . $resource_extension . '.css', '', THM_VER);
    wp_register_style('bootstrap-grid', THM_DIR_URI . '/assets/' . $resource_min_dir . 'css/bootstrap-grid' . $resource_extension . '.css', '', THM_VER);
    wp_register_style('base-css', THM_DIR_URI . '/assets/' . $resource_min_dir . 'css/base' . $resource_extension . '.css', '', THM_VER);
    wp_register_style('theme-css', THM_DIR_URI . '/assets/' . $resource_min_dir . 'css/theme' . $resource_extension . '.css', '', THM_VER);
    wp_register_style('theme-stylesheet', get_stylesheet_uri());

    wp_register_style('style-building-models-all', THM_DIR_URI . '/assets/min-css/style.min.css', '', THM_VER);

    wp_register_style('responsive-css', THM_DIR_URI . '/assets/' . $resource_min_dir . 'css/responsive' . $resource_extension . '.css', '', THM_VER);

    /* Fallback : If Option Tree failed to Enqueue the theme's default font families */
   if (!wp_style_is('ot-google-fonts')) {
        wp_register_style('custom-google-font', '//fonts.googleapis.com/css?family=Josefin+Sans:400,400i,700,700i|Lato:400,400i,700,700i&amp;subset=latin-ext', false);
        wp_enqueue_style('custom-google-font');
   }

    wp_register_script('custom-js', THM_DIR_URI . '/assets/' . $resource_min_dir . 'js/z-custom' . $resource_extension . '.js', array('jquery'), THM_VER, true);
    wp_register_script('script-building-models-all', THM_DIR_URI . '/assets/min-js/script.min.js', array('jquery', 'one-shortcode-js'), THM_VER, true);

    if ((WP_DEBUG != true || WP_DEBUG != 'true' ) && (SCRIPT_DEBUG != true || SCRIPT_DEBUG != 'true' )) {

        wp_enqueue_style('style-building-models-all');
        wp_enqueue_script('script-building-models-all');

        /* Localization */
        wp_localize_script('script-building-models-all', 'one_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'msg' => __('Please wait...', OC_TEXT_DOMAIN),
            'subscribe_btn' => __('Subscribe', OC_TEXT_DOMAIN),
            'send' => __('SUBMIT', OC_TEXT_DOMAIN),
                )
        );
    } else {
        wp_enqueue_style('normalize-css');
        wp_enqueue_style('bootstrap-reboot');
        wp_enqueue_style('bootstrap-grid');
        wp_enqueue_style('base-css');
        wp_enqueue_style('theme-css');
        wp_enqueue_style('theme-stylesheet');
        wp_enqueue_style('responsive-css');
        wp_enqueue_style('slick-style');
        wp_enqueue_script('slick-slider');
        wp_enqueue_script('custom-js');

        /* Localization */
        wp_localize_script('custom-js', 'one_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'msg' => __('Please wait...', OC_TEXT_DOMAIN),
            'subscribe_btn' => __('Subscribe', OC_TEXT_DOMAIN),
            'send' => __('SUBMIT', OC_TEXT_DOMAIN),
                )
        );
    }
    wp_enqueue_style('dashicons');
}

add_action('wp_enqueue_scripts', 'onecom_theme_assets');

/**
  /* Gallery Default Settings - Set 5 column in Admin
 */
function building_models_gallery_defaults($settings) {
    $settings['galleryDefaults']['columns'] = 4;
    return $settings;
}

add_filter('media_view_settings', 'building_models_gallery_defaults');

/* Register navigation menus */

function register_my_menus() {
    register_nav_menus(
            array(
                'primary_building_models' => 'Primary - Building Models',
                'mobile_building_models' => 'Mobile Menu - Building Models',
            )
    );
}

add_action('init', 'register_my_menus');

/* show attachment data */

function wp_get_attachment($attachment_id) {
    $attachment = get_post($attachment_id);
    return array(
        'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'href' => get_permalink($attachment->ID),
        'src' => $attachment->guid,
        'title' => $attachment->post_title
    );
}

/**
 * Filter the excerpt length
 */
function building_models_excerpt_length() {
    if (is_page_template('page-templates/services-page.php')) {
        return 25;
    } else {
        return 22;
    }
}

add_filter('excerpt_length', 'building_models_excerpt_length', 999);

/* Social Icons Widget Assets */
add_action( 'admin_enqueue_scripts', 'one_social_admin_assets' );
function one_social_admin_assets() {
	wp_register_script( $handle = 'one-theme-admin', THM_DIR_URI.'/core/admin.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'one-theme-admin' );
}

/* Modified wp_get_attachment_link to have the caption compatible with shinybox. */

function caption_for_shinybox($markup, $id, $size, $permalink, $icon, $text) {
    $_post = get_post($id);
    if ($permalink) {
        $url = get_attachment_link($_post->ID);
    }

    if (empty($_post) || ( 'attachment' !== $_post->post_type ) || !$url = wp_get_attachment_url($_post->ID)) {
        return __('Missing Attachment', OC_TEXT_DOMAIN);
    }

    $link_text = wp_get_attachment_image($id, $size, $icon);
    if (trim($link_text) == '') {
        $link_text = $_post->post_title;
    }

    $link_title = get_post($id)->post_excerpt;
    if (trim($link_title) == '') {
        $link_title = $text;
    }

    return '<a href="' . $url . '" title="' . $link_title . '">' . $link_text . '</a>';
}

add_filter('wp_get_attachment_link', 'caption_for_shinybox', 10, 6);

/* Remove BR tags from gallery */
add_filter('use_default_gallery_style', '__return_false');

/* Register Sidebars */

function onecom_widgets_init() {

    /* Common sidebar. */
    register_sidebar(array(
        'name' => __('Sidebar', OC_TEXT_DOMAIN),
        'id' => 'sidebar-1',
        'description' => __('Add widgets here to appear in your sidebar.', OC_TEXT_DOMAIN),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-title"><h3>',
        'after_title' => '</h3 ></div>',
    ));
    
    // Header Sidebar
    register_sidebar(array(
        'name' => __('Header', OC_TEXT_DOMAIN),
        'id' => 'header-1',
        'description' => __('Add widgets here to appear in your header.', OC_TEXT_DOMAIN),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-title"><h5>',
        'after_title' => '</h5></div>',
    ));

    // Footer Sidebar
    register_sidebar(array(
        'name' => __('Footer', OC_TEXT_DOMAIN) . ' 1',
        'id' => 'footer-1',
        'description' => __('Add widgets here to appear in your footer.', OC_TEXT_DOMAIN),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-title"><h5>',
        'after_title' => '</h5></div>',
    ));
    register_sidebar(array(
        'name' => __('Footer', OC_TEXT_DOMAIN) . ' 2',
        'id' => 'footer-2',
        'description' => __('Add widgets here to appear in your footer.', OC_TEXT_DOMAIN),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-title"><h5>',
        'after_title' => '</h5></div>',
    ));
    register_sidebar(array(
        'name' => __('Footer', OC_TEXT_DOMAIN) . ' 3',
        'id' => 'footer-3',
        'description' => __('Add widgets here to appear in your footer.', OC_TEXT_DOMAIN),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-title"><h5>',
        'after_title' => '</h5></div>',
    ));
}

add_action('widgets_init', 'onecom_widgets_init');

/* Custom header scripts coming from Theme Options */
if (!defined('onecom_head_scripts')) {

    function onecom_head_scripts() {
        /* Head Scripts */
        $head_scripts = ot_get_option('head_scripts');
        if (strlen($head_scripts)) {
            echo $head_scripts;
        } else {
            return true;
        }
    }

    add_action('wp_head', 'onecom_head_scripts', 30);
}

/* Custom footer scripts coming from Theme Options */
if (!defined('onecom_footer_scripts')) {

    function onecom_footer_scripts() {
        /* Footer Scripts */
        $footer_scripts = ot_get_option('footer_scripts');
        if (strlen($footer_scripts)) {
            echo $footer_scripts;
        } else {
            return true;
        }
    }

    add_action('wp_footer', 'onecom_footer_scripts', 30);
}

/* Include the One Click Importer */
if (!class_exists('OCDI_Plugin')) {
    require_once ( THM_DIR_PATH . '/importer/importer.php' );
}

/* Pass the importable files to the Importer. */
if (!function_exists('ocdi_import_files')) {

    function ocdi_import_files() {
        return array(
            array(
                'import_file_name' => 'Theme demo data',
                'local_import_file' => trailingslashit(get_template_directory()) . 'importer/content.xml',
                'import_widget_file_url' => trailingslashit(get_template_directory_uri()) . 'importer/widgets.json',
            ),
        );
    }

}
add_filter('pt-ocdi/import_files', 'ocdi_import_files');

if (!function_exists('ocdi_after_import_setup')) {

    function ocdi_after_import_setup() {
        /* Assign menus to their locations. */
        $main_menu = get_term_by('name', 'Primary - Building Models', 'nav_menu');
        $mobile_menu = get_term_by('name', 'Primary - Building Models', 'nav_menu');
        set_theme_mod('nav_menu_locations', array(
            'primary_building_models' => $main_menu->term_id,
            'mobile_building_models' => $mobile_menu->term_id,
                )
        );

        /* Assign front page and posts page (blog page). */
        $front_page_id = get_page_by_title('Home');

        update_option('show_on_front', 'page');
        update_option('page_on_front', $front_page_id->ID);

        $blog_page_id = get_page_by_title('Blog');
        update_option('page_for_posts', $blog_page_id->ID);
    }

}
add_action('pt-ocdi/after_import', 'ocdi_after_import_setup');

/**
 * Include Stats Script
 **/
if(!class_exists('OCPushStats')){
    require_once trailingslashit( get_template_directory() ) . 'inc/lib/OCPushStats.php';
}

/**
 * Include Spam Protection file
 **/
if(! (class_exists('OCSpamProtection'))) {
    require_once trailingslashit( get_template_directory() ) . 'inc/OCSpamProtection.php';
}
