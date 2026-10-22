<?php
/* @header("Content-type: text/css"); */

function ot_check_css_prop($prop, $val, $val2 = '') {
    if (!(isset($prop) && strlen($prop)))
        return;

    if (isset($val) && !is_array($val) && strlen($val)) {
        if (isset($val2) && strlen($val2)) {
            return sprintf($prop, $val, $val2);
        }
        return sprintf($prop, $val);
    }
    return;
}

function ot_check_bg_css_prop($pairs) {
    if (!(isset($pairs) && is_array($pairs)))
        return;
    $pairs = array_filter($pairs, 'strlen');
    $css = '';
    foreach ($pairs as $key => $prop) {
        $css .= ('background-image' === $key ) ? sprintf('%s:url(%s);', $key, $prop) : sprintf('%s:%s;', $key, $prop);
    }
    return $css;
}

function ot_check_font_css_prop($pairs) {
    if (!(isset($pairs) && is_array($pairs)))
        return;
    $pairs = array_filter($pairs, 'strlen');
    $css = '';
    unset($pairs['font-color']);
    foreach ($pairs as $key => $prop) {
        $css .= ('font-family' === $key ) ? sprintf('%s:%s;', $key, ot_google_font_family($prop)) : sprintf('%s:%s;', $key, $prop);
    }
    return $css;
}
?>



<style type="text/css">

    <?php
################################
########  Skin Styles  #########
################################

    $skin_primary = ot_get_option('skin_primary');
    $skin_secondary = ot_get_option('skin_secondary');


    if (!empty($skin_primary) && !empty($skin_secondary)) {
        ?>
        /* Primary Skin Color */
        .primary-color,
        a.primary-color,
        #primary-nav ul li.current_page_item,
        #primary-nav ul li:hover > a {
            color: <?php echo $skin_primary; ?>;
        }

        /* Change all linke as above but not these */
            
        a.button,
        a.button:visited,
        #wp-calendar thead th,
        #sticky_menu li.current-menu-item > a {
            background-color: <?php echo $skin_primary; ?>;
        }

        a.button:hover {
            background-color: <?php echo $skin_secondary; ?>;
        }
        
        .onecom-webshop-main a.button,
        .onecom-webshop-main a.button:visited {
            
        }
        .onecom-webshop-main a.button:hover{
            
        }

        <?php
    }
    
    /* Dynamic CSS */
    printf("#page { background-image:url('".THM_DIR_URI."/assets/images/building-models-header-bg.png')}");

    /* Custom Color If Custom Skin ON */
    $custom_skin_switch = ot_get_option('custom_skin_switch');
    if ('off' != $custom_skin_switch) {

        /* Body Text Color */
        $body_text_color = ot_get_option('body_text_color');
        if (!empty($body_text_color)) {
            echo 'body, .regular-color{' .
            ot_check_css_prop('color:%s;', $body_text_color) .
            '}';
            echo '.onecom-webshop-main svg {' .
            ot_check_css_prop('fill:%s;', $body_text_color) .
            '}';
        }

        /* Body CSS */
        $body_css_val = ot_get_option('body_bg');
        if (!empty($body_css_val)) {
            printf("#page{%s}", ot_check_bg_css_prop($body_css_val));
            printf(".post-meta ul:after{box-shadow:-17px 0 16px %s inset}", $body_css_val['background-color']);
        }

        /* Body Image switch */
        if ('off' == ot_get_option('body_bg_image_switch')) {
            printf("#page { background-image: %s}", 'none');
        }

        /* Headings Colors */
        $headings_colors = ot_get_option('headings_colors');
        if (!empty($headings_colors)) {
            echo '.section-content h1, .featured-box h1, .main-content-box h1, .plan-content h1, .widget-content h1, .textwidget h1, .service-details h1{' .
            ot_check_css_prop('color:%s;', $headings_colors['h1']) .
            '}';
            echo '.section-content h2, .featured-box h2, .main-content-box h2, .widget-content h2, .textwidget h2, .service-details h2{' .
            ot_check_css_prop('color:%s;', $headings_colors['h2']) .
            '}';
            echo '.section-content h3, .featured-box h3, .main-content-box h3, .widget-content h3, .textwidget h3, .service-details h3{' .
            ot_check_css_prop('color:%s;', $headings_colors['h3']) .
            '}';
            echo '.section-content h4, .featured-box h4, .main-content-box h4, .widget-content h4, .textwidget h4, .service-details h4{' .
            ot_check_css_prop('color:%s;', $headings_colors['h4']) .
            '}';
            echo '.section-content h5, .featured-box h5, .main-content-box h5, .widget-content h5, .textwidget h5, .service-details h5{' .
            ot_check_css_prop('color:%s;', $headings_colors['h5']) .
            '}';
            echo '.section-content h6, .featured-box h6, .main-content-box h6, .widget-content h6, .textwidget h6, .service-details h6{' .
            ot_check_css_prop('color:%s;', $headings_colors['h6']) .
            '}';
        }

        /* Body Text Color */
        $page_title_color = ot_get_option('page_main_title_color');
        if (!empty($page_title_color)) {
            echo '.main-title{' .
            ot_check_css_prop('color:%s;', $page_title_color) .
            '}';
        }

        /* Link Colors */
        $link_colors = ot_get_option('body_link_color');

        if (!empty($link_colors)) {
            echo 'a, .section-content a, .featured-box a, .main-content-box a, .widget a, .textwidget a, .service-details a{' .
            ot_check_css_prop('color:%s;', $link_colors['link']) .
            '}';
            echo 'a:active, .section-content a:active, .featured-box a:active, .main-content-box a:active, .widget a:active, .textwidget a:active, .service-details a:active{' .
            ot_check_css_prop('color:%s;', $link_colors['active']) .
            '}';

            echo 'a:visited, .section-content a:visited, .featured-box a:visited, .main-content-box a:visited, .widget a:visited, .textwidget a:visited, .service-details a:visited{' .
            ot_check_css_prop('color:%s;', $link_colors['visited']) .
            '}';
            echo 'a:hover, .section-content a:hover, .featured-box a:hover, .main-content-box a:hover, .widget a:hover, .textwidget a:hover, .service-details a:hover{' .
            ot_check_css_prop('color:%s;', $link_colors['hover']) .
            '}';
        }

        /* Content Buttons Colors */
        $btn_cont_color = ot_get_option('content_button_text_color');

        if (!empty($btn_cont_color)) {
            echo 'a.button, a.button:visited{' .
            ot_check_css_prop('color:%s;', $btn_cont_color['link']) .
            ot_check_css_prop('background-color:%s;', $btn_cont_color['active']) .
            '}';
            echo 'a.button:hover, .button:hover{' .
            ot_check_css_prop('color:%s;', $btn_cont_color['hover']) .
            ot_check_css_prop('background-color:%s;', $btn_cont_color['visited']) .
            '}';
            echo '.onecom-webshop-main a.button, .onecom-webshop-main a.button:visited, .onecom-webshop-main a.button:hover{' .
            ot_check_css_prop('color:%s !important;', $btn_cont_color['hover']) .
            ot_check_css_prop('background-color:%s !important;', $btn_cont_color['visited']) .
            '}';
        }

        /* Content Buttons Borders */
        $cont_buttons_border_sw = ot_get_option('content_buttons_border_sw');

        if ('off' != $cont_buttons_border_sw) {
            $cont_buttons_border = ot_get_option('content_buttons_border');
            $cont_buttons_border_rad = ot_get_option('content_buttons_border_rad');

            if (!empty($cont_buttons_border)) {
                echo '.button{' .
                ot_check_css_prop('border-width:%s%s;', $cont_buttons_border['width'], $cont_buttons_border['unit']) .
                ot_check_css_prop('border-style:%s;', $cont_buttons_border['style']) .
                ot_check_css_prop('border-color:%s;', $cont_buttons_border['color']) .
                ot_check_css_prop('border-radius:%s;', $cont_buttons_border_rad . 'px') .
                '}';
            }
        }

        /* Form Buttons Colors */
        $btn_form_color = ot_get_option('form_button_text_color');

        if (!empty($btn_form_color)) {
            echo '[type="submit"].button, [type="submit"].button:visited{' .
            ot_check_css_prop('color:%s;', $btn_form_color['link']) .
            ot_check_css_prop('background-color:%s;', $btn_form_color['active']) .
            '}';
            echo '[type="submit"].button:hover, [type="submit"].button:hover{' .
            ot_check_css_prop('color:%s;', $btn_form_color['hover']) .
            ot_check_css_prop('background-color:%s;', $btn_form_color['visited']) .
            '}';
        }


        /* Form Buttons Border */
        $form_buttons_border_sw = ot_get_option('form_buttons_border_sw');
        if ('off' != $form_buttons_border_sw) {

            $form_buttons_border = ot_get_option('form_buttons_border');
            $form_buttons_border_rad = ot_get_option('form_buttons_border_rad');
            if (!empty($form_buttons_border)) {

                echo '#searchsubmit, [type="submit"].button{' .
                ot_check_css_prop('border-width:%s%s;', $form_buttons_border['width'], $form_buttons_border['unit']) .
                ot_check_css_prop('border-style:%s;', $form_buttons_border['style']) .
                ot_check_css_prop('border-color:%s;', $form_buttons_border['color']) .
                ot_check_css_prop('border-radius:%s;', $form_buttons_border_rad . 'px') .
                '}';
            }
        }

        /* Menu Background Color */
        $menu_bg_color = ot_get_option('menu_bg_color');

        if (!empty($menu_bg_color)) {
            printf("#primary-nav{%s}", ot_check_bg_css_prop($menu_bg_color));
        }

        /* Menu item color */
        $menu_link_color = ot_get_option('menu_link_color');

        if (!empty($menu_link_color)) {
            echo '#primary-nav ul li a{' .
            ot_check_css_prop('color:%s;', $menu_link_color['link']) .
            '}';

            echo '#primary-nav ul li:hover > a{' .
            ot_check_css_prop('color:%s;', $menu_link_color['hover']) .
            '}';

            echo '#primary-nav ul li.current_page_item a, #primary-nav ul li.current-menu-parent a{' .
            ot_check_css_prop('color:%s;', $menu_link_color['active']) .
            '}';
        }

        /* Menu Item Background */
        $menu_typo_bg = ot_get_option('menu_typo_bg');
        if (!empty($menu_typo_bg)) {
            echo '#primary-nav ul li a{' .
            ot_check_css_prop('background-color:%s;', $menu_typo_bg['link']) .
            '}';

            echo '#primary-nav ul li:hover > a{' .
            ot_check_css_prop('background-color:%s;', $menu_typo_bg['hover']) .
            '}';

            echo '#primary-nav ul li.current_page_item a, #primary-nav ul li.current-menu-parent a{' .
            ot_check_css_prop('background-color:%s;', $menu_typo_bg['active']) .
            '}';
        }

        /* Submenu item color */
        $submenu_link_color = ot_get_option('submenu_link_color');

        if (!empty($submenu_link_color)) {
            echo '#primary-nav ul.sub-menu li a{' .
            ot_check_css_prop('color:%s;', $submenu_link_color['link']) .
            '}';

            echo '#primary-nav ul.sub-menu li:hover > a{' .
            ot_check_css_prop('color:%s;', $submenu_link_color['hover']) .
            '}';

            echo '#primary-nav ul.sub-menu li.current_page_item a, #primary-nav ul.sub-menu li.current-menu-item a{' .
            ot_check_css_prop('color:%s;', $submenu_link_color['active']) .
            '}';
        }

        /* Submenu Item Background */
        $submenu_typo_bg = ot_get_option('submenu_typo_bg');
        if (!empty($submenu_typo_bg)) {
            echo '#primary-nav ul.sub-menu li a{' .
            ot_check_css_prop('background-color:%s;', $submenu_typo_bg['link']) .
            '}';

            echo '#primary-nav ul.sub-menu li:hover > a{' .
            ot_check_css_prop('background-color:%s;', $submenu_typo_bg['hover']) .
            '}';

            echo '#primary-nav ul.sub-menu li.current_page_item a, #primary-nav ul.sub-menu li.current-menu-item a{' .
            ot_check_css_prop('background-color:%s;', $submenu_typo_bg['active']) .
            '}';
        }

        /* Header BG */
        $header_bg = ot_get_option('header_bg');
        if (!empty($header_bg)) {
            printf("header#site-header{%s}", ot_check_bg_css_prop($header_bg));
        }


        $logo_color = ot_get_option('logo_color');
        if (!empty($logo_color)) {
            echo '.header-banner h1 a, .header-banner h2 a, .site-title a, .header-banner h1 a:visited, .header-banner h2 a:visited, .site-title a:visited,{' .
            ot_check_css_prop('color:%s;', $logo_color['link']) .
            '}';

            echo '.header-banner h1 a:hover, .header-banner h2 a:hover, .site-title a:hover{' .
            ot_check_css_prop('color:%s;', $logo_color['hover']) .
            '}';
        }

        /* Section Title Color */
        $section_title_color = ot_get_option('page_section_text_color');
        if (!empty($section_title_color)) {

            echo '.featured-container h2, .featured-container h3 {' .
            ot_check_css_prop('color:%s;', $section_title_color) .
            '}';
        }

        /* Section Description Color */
        $section_desc_color = ot_get_option('page_section_cont_color');
        if (!empty($section_desc_color)) {
            echo '.featured-container .main-content-box {' .
            ot_check_css_prop('color:%s;', $section_desc_color) .
            '}';
        }

        /* Footer BG Color */
        $footer_bg = ot_get_option('footer_bg');
        if (!empty($footer_bg))
            printf(".footer-widgets{%s}", ot_check_bg_css_prop($footer_bg));

        /* Footer Text Color */
        $footer_tcolor = ot_get_option('footer_tcolor');
        if (!empty($footer_tcolor)) {
            echo '.footer-widgets, .footer-widgets p, .footer-widgets .footer-logo.site-logo .site-title, .footer-widgets a, .footer-widgets a:hover, .footer-widgets a:visited{' .
            ot_check_css_prop('color:%s;', $footer_tcolor) .
            '}';
            echo '.footer-widgets .widget_calendar table thead{' .
            ot_check_css_prop('background-color:%s;', $footer_tcolor) .
            '}';
            echo '.footer-widgets .widget_calendar table, .footer-widgets .widget_calendar table tfoot{' .
            ot_check_css_prop('border-color:%s;', $footer_tcolor) .
            '}';
        }
    }


    /* ################################## Typography ################################# */
    /* Logo Font Style */
    $logo_typo = ot_get_option('logo_typo');

    if (!empty($logo_typo)) {
        printf(".header-banner h1 a, .header-banner h2 a, .site-logo h1, .site-logo h2 {%s}", ot_check_font_css_prop($logo_typo));
    }

    /* Header Menu Font Style */
    $menu_typo = ot_get_option('menu_typo');
    if (!empty($menu_typo)) {
        printf("#primary-nav ul li a{%s}", ot_check_font_css_prop($menu_typo));
    }

    /* Body Font Style */
    $body_typo = ot_get_option('body_typo');
    if (!empty($body_typo)) {
        printf("body, body p, .section-content, .section-content p, .sidebar, .featured-box, .main-content-box, .main-content-box p, .plan-content, .widget-content, .textwidget, .highlighted-text, .package-highlights{%s}", ot_check_font_css_prop($body_typo));
        
        /* Keep webshop fonts same as of font to avoid various conflicts */
        printf(".onecom-webshop-main *, .main-content-box .onecom-webshop-main h1, .main-content-box .onecom-webshop-main h2 {%s}", ot_check_font_css_prop($body_typo));
    }

    /* H1 = Heading Font Style */
    $h1_typo = ot_get_option('h1_typo');
    if (!empty($h1_typo)) {
        echo 'h1, .section-content h1, .featured-box h1, .main-content-box h1, .plan-content h1, .widget-content h1, .textwidget h1, .service-details h1{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($h1_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $h1_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $h1_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $h1_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $h1_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $h1_typo['text-decoration']) .
        '}';
    }

    /* H2 = Heading Font Style */
    $h2_typo = ot_get_option('h2_typo');
    if (!empty($h2_typo)) {
        echo '.main-title, h2, .section-content h2, .featured-box h2, .main-content-box h2, .plan-content h2, .widget-content h2, .textwidget h2, .service-details h2{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($h2_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $h2_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $h2_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $h2_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $h2_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $h2_typo['text-decoration']) .
        '}';
    }

    /* H3 = Heading Font Style */
    $h3_typo = ot_get_option('h3_typo');
    if (!empty($h3_typo)) {
        echo 'h3, .section-content h3, .featured-box h3, .main-content-box h3, .plan-content h3, .widget-content h3, .textwidget h3, .service-details h3{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($h3_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $h3_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $h3_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $h3_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $h3_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $h3_typo['text-decoration']) .
        '}';
    }

    /* H4 = Heading Font Style */
    $h4_typo = ot_get_option('h4_typo');
    if (!empty($h4_typo)) {
        echo 'h4, .section-content h4, .featured-box h4, .main-content-box h4, .plan-content h4, .widget-content h4, .textwidget h4, .service-details h4{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($h4_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $h4_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $h4_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $h4_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $h4_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $h4_typo['text-decoration']) .
        '}';
    }

    /* H5 = Heading Font Style */
    $h5_typo = ot_get_option('h5_typo');
    if (!empty($h5_typo)) {
        echo 'h5, .section-content h5, .featured-box h5, .main-content-box h5, .plan-content h5, .widget-content h5, .textwidget h5, .service-details h5{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($h5_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $h5_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $h5_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $h5_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $h5_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $h5_typo['text-decoration']) .
        '}';
    }

    /* H6 = Heading Font Style */
    $h6_typo = ot_get_option('h6_typo');
    if (!empty($h6_typo)) {
        echo 'h6, .section-content h6, .featured-box h6, .main-content-box h6, .plan-content h6, .widget-content h6, .textwidget h6, .site-logo h6, .service-details h6{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($h6_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $h6_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $h6_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $h6_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $h6_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $h6_typo['text-decoration']) .
        '}';
    }

    /* Homepage Banner Font Style */
    $banner_htypo = ot_get_option('banner_htypo');
    if (!empty($banner_htypo)) {
        echo '.banner-box .banner-caption h2{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($banner_htypo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $banner_htypo['font-size']) .
        ot_check_css_prop('font-style:%s;', $banner_htypo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $banner_htypo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $banner_htypo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $banner_htypo['text-decoration']) .
        '}';
    }

    /* Page - Font Style */
    $page_title_typo = ot_get_option('page_title_typo');

    if (!empty($page_title_typo)) {
        echo 'h2.main-title, h1.main-title {' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($page_title_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $page_title_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $page_title_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $page_title_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $page_title_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $page_title_typo['text-decoration']) .
        '}';
    }

    /* Sections - Font Style */
    $sectionsh_typo = ot_get_option('section_heading_typo');
    if (!empty($sectionsh_typo)) {
        echo '.featured-container h2, .section-title h2 {' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($sectionsh_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $sectionsh_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $sectionsh_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $sectionsh_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $sectionsh_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $sectionsh_typo['text-decoration']) .
        '}';
    }

    /* Body Font Style */
    $sectionsc_typo = ot_get_option('sections_content_typo');
    if (!empty($sectionsc_typo)) {
        printf(".featured-container .main-content-box, .featured-container .main-content-box p{%s}", ot_check_font_css_prop($sectionsc_typo));
    }

        /* Content Buttons - Font Style */
    $content_button_typo = ot_get_option('content_button_typo');
    if (!empty($content_button_typo)) {
        echo '.button, a.button{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($content_button_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $content_button_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $content_button_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $content_button_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $content_button_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $content_button_typo['text-decoration']) .
        '}';
    }

    /* Form Buttons - Font Style */
    $form_button_typo = ot_get_option('form_button_typo');
    if (!empty($form_button_typo)) {
        echo '[type="submit"].button, [type="submit"].button:visited, #searchsubmit{' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($form_button_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $form_button_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $form_button_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $form_button_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $form_button_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $form_button_typo['text-decoration']) .
        '}';
    }

    /* Footer -  Font Style */
    $footerh_typo = ot_get_option('footerh_typo');
    if (!empty($footerh_typo)) {
        echo '.footer-widgets, .footer-widgets div, .footer-widgets p, .footer-widgets li {' .
        ot_check_css_prop('font-family:%s;', ot_google_font_family($footerh_typo['font-family'])) .
        ot_check_css_prop('font-size:%s;', $footerh_typo['font-size']) .
        ot_check_css_prop('font-style:%s;', $footerh_typo['font-style']) .
        ot_check_css_prop('font-weight:%s;', $footerh_typo['font-weight']) .
        ot_check_css_prop('line-height:%s;', $footerh_typo['line-height']) .
        ot_check_css_prop('text-decoration:%s;', $footerh_typo['text-decoration']) .
        '}';
    }

    /* ################## Custom CSS ######################## */
    echo ot_get_option('custom_css');
    ?>


</style>
