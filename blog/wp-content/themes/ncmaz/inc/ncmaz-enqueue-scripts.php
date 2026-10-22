<?php
// ENQUEUE LOCALE TRANSLATE JS FILES
add_action('wp_enqueue_scripts', 'ncmaz_theme_locals_scripts');
function ncmaz_theme_locals_scripts()
{
    $dirJS = [];

    if (file_exists(get_theme_file_path('locales'))) {
        $dirJS = new DirectoryIterator(get_theme_file_path('locales'));
    }

    // ONLY GET DEFAULT FILE
    global $ncmaz_redux_demo;
    if ($ncmaz_redux_demo !== null) {
        if (!boolval($ncmaz_redux_demo['nc-general-settings--general-switch-polylang'])) {
            wp_enqueue_script("ncmaz-locales--default", get_theme_file_uri('locales/default.js'), [], _NCMAZ_THEME_VERSION, false);
            return;
        }
    }

    // GET ALL FILE JS LOCALS WHEN ACTIVE POLYLANG
    foreach ($dirJS as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
            $fullName = basename($file);
            $name = 'ncmaz-locales--' . substr(basename($fullName), 0, strpos(basename($fullName), '.'));
            wp_enqueue_script($name, get_theme_file_uri('locales/' . $fullName), [], _NCMAZ_THEME_VERSION, false);
        }
    }
}

// ENQUEUE SCRIPTS
function ncmaz_theme_enqueue_script()
{
    wp_enqueue_script('ncmaz-script', get_template_directory_uri() . '/assets/js/scripts.js', ['jquery'], _NCMAZ_THEME_VERSION, true);
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'ncmaz_theme_enqueue_script');

// ENQUEUE STYLES TO FOOTER
function ncmaz_theme_enqueue_add_footer_styles()
{
    wp_enqueue_style('ncmaz-style', get_template_directory_uri() . '/style.css', [], _NCMAZ_THEME_VERSION, 'all');
    // LINE AWESOME
    wp_register_style('Line_Awesome', 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css');
    wp_enqueue_style('Line_Awesome');
}
add_action('get_footer', 'ncmaz_theme_enqueue_add_footer_styles');


add_filter('style_loader_tag',  'ncmaz_theme_preload_filter', 10, 2);
function ncmaz_theme_preload_filter($html, $handle)
{
    if (strcmp($handle, 'Line_Awesome') == 0) {
        $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload='this.rel=\"stylesheet\"'", $html);
    }
    return $html;
}


// FOR DEFAULT THEME (NOT ACTIVE CORE PLUGIN)
// AFTER ACTIVE PLUGIN WHEN ALL THIS CSS WILL ENQUEUE ON PLUGIN
if (!defined('_NCMAZ_FRONTEND_VERSION')) {
    // ENQUEUE FOR FRONT END
    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_style('ncmaz-main-styles', get_template_directory_uri() . '/assets/css/main.css', [], _NCMAZ_THEME_VERSION, 'all');
    });

    // ENQUEUE FOR BACKEND EDITOR
    function ncmaz_theme_enqueue_admin_stylesheet($hook)
    {
        if ($hook !== 'post.php') {
            return;
        }
        wp_enqueue_style('ncmaz-editor', get_template_directory_uri() . '/assets/editor-css/index.css', [], _NCMAZ_THEME_VERSION, 'all');
        wp_enqueue_style('ncmaz-style-editor', get_template_directory_uri() . '/assets/editor-css/style-index.css', [], _NCMAZ_THEME_VERSION, 'all');
    }
    add_action('admin_enqueue_scripts', 'ncmaz_theme_enqueue_admin_stylesheet');
}
