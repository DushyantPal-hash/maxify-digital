<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <!-- Mobile Specific Metas ================================================== -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>

        <link rel="profile" href="http://gmpg.org/xfn/11"/>
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
        <?php wp_head(); ?>
        <?php include(TEMPLATEPATH . '/assets/css/header-css.php'); ?>

        <link rel='stylesheet' id='responsive-css'  href='<?php echo get_parent_theme_file_uri('assets/css/responsive.css') . '?ver=' . THM_VER; ?>' media='all' />

        <?php if (is_singular()) wp_enqueue_script('comment-reply'); ?>
    </head>

    <body <?php body_class(); ?>>
        <!-- START master wrapper -->
        <div id="wrapper">
            <!-- START page wrapper -->
            <div id="page">
                <!-- START header container-->
                <header id="site-header" role="banner">
                    <div class="container no-padding">
                        <div class="row">

                            <div class="col-md-12 text-left site-logo-box">
                                <button class="menu-toggle mobile-only" aria-controls="sticky_menu" aria-expanded="false">Menu</button>
                                <span class="mobile-page-title mobile-only"> 
                                    <?php
                                    if (is_front_page()) {
                                        the_title();
                                    } else {
                                        wp_title('');
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="col-md-9 desktop-only text-left">
                                <?php
                                $logo_class = ('off' != ot_get_option('logo_switch') ? '' : 'zero-size');
                                ?>
                                <div class="site-logo <?php echo $logo_class; ?>">
                                    <a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name') ?>" rel="home">
                                        <?php
                                        $logo = ot_get_option('logo_img');
                                        ?>
                                        <?php if (is_front_page()) { ?>
                                            <h1 class="site-title">
                                                <?php
                                                if (strlen($logo)) {
                                                    printf('<img src="%s" alt="%s" />', $logo, get_bloginfo('name'));
                                                } else {
                                                    bloginfo('title');
                                                }
                                                ?>
                                            </h1>
                                        <?php } else { ?>

                                            <h2 class="site-title">
                                                <?php
                                                if (strlen($logo)) {
                                                    printf('<img src="%s" alt="%s" />', $logo, get_bloginfo('name'));
                                                } else {
                                                    bloginfo('title');
                                                }
                                                ?>
                                            </h2>
                                            <?php
                                        }
                                        ?> 
                                    </a>
                                </div>
                                <!-- START nav container -->
                                <nav class="nav primary-nav" id="primary-nav" role="navigation">
                                    <?php
                                    wp_nav_menu(
                                            array(
                                                'theme_location' => 'primary_building_models',
                                                'container' => '',
                                                'fallback_cb' => 'onecom_add_nav_menu',
                                            )
                                    );
                                    ?>
                                </nav>
                                <!-- END nav container -->
                            </div>
                            <div class="col-md-3 text-right">
                                <!-- START Header Sidebar -->
                                <?php dynamic_sidebar('header'); ?>
                                <!-- END Header Sidebar -->
                            </div>
                        </div>
                    </div>
                </header>
                <!-- END nav container -->
                <?php if ('off' != ot_get_option('header_switch')): ?>
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <div class="header-banner">
                                    <h2>
                                        <a href="<?php echo home_url('/'); ?>" rel="home" >
                                            <?php
                                            echo ot_get_option('header_left_text');
                                            $logo = ot_get_option('header_img', '');
                                            if ('off' != ot_get_option('header_img_switch') && strlen($logo)) {
                                                printf(' <img src="%s" alt="%s" /> ', $logo, get_bloginfo('name'));
                                            }
                                            echo ot_get_option('header_right_text');
                                            ?>
                                        </a>
                                    </h2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center header-subtitle">
                                <?php echo ot_get_option('header_subtitle'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center header-tagline">
                                <h2><?php echo ot_get_option('header_tagline'); ?></h2>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>