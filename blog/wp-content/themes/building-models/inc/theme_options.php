<?php

/**
 * Initialize the custom Theme Options.
 */
add_action('init', 'custom_theme_options');

/**
 * Build the custom settings & update OptionTree.
 *
 * @return    void
 * @since     2.0
 */
function custom_theme_options() {

    /* OptionTree is not loaded yet, or this is not an admin request */
    if (!function_exists('ot_settings_id') || !is_admin())
        return false;
    
    /* Check if action is reset (Reset Options) */
    $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
    $social_std = $header_img_std = $background_img_std = $header_tagline_std = '';
    
    /* Save default values if Reset or Fresh site */
    if (is_ot_fresh_site() === true OR $action === 'reset') :
        $header_img_std = THM_DIR_URI . '/assets/images/building-models-logo.png';
        $background_img_std = THM_DIR_URI . '/assets/images/building-models-header-bg.png';
        $header_tagline_std = 'BUILDING MODELS';
        $social_std = array(
            array(
                'title' => 'Twitter',
                'social_icon_entry' => 'twitter',
                'social_icon_link' => '#',
            ),
            array(
                'title' => 'Facebook',
                'social_icon_entry' => 'facebook',
                'social_icon_link' => '#',
            ),
            array(
                'title' => 'LinkedIn',
                'social_icon_entry' => 'linkedin',
                'social_icon_link' => '#',
            )
        );
    endif;


    /**
     * Get a copy of the saved settings array.
     */
    $saved_settings = get_option(ot_settings_id(), array());

    /**
     * Custom settings array that will eventually be
     * passes to the OptionTree Settings API Class.
     */
    $custom_settings = array(
        /* 'contextual_help' => array(
          'content'       => array(
          array(
          'id'        => 'option_types_help',
          'title'     => __( 'Option Types', OC_TEXT_DOMAIN ),
          'content'   => '<p>' . __( 'Help content goes here!', OC_TEXT_DOMAIN ) . '</p>'
          ),
          ),
          'sidebar'       => '<p>' . __( 'Sidebar content goes here!', OC_TEXT_DOMAIN ) . '</p>'
          ), */
        'sections' => array(
            array(
                'id' => 'header_option',
                'title' => __('Header', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'footer_options',
                'title' => __('Footer', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'typo_option',
                'title' => __('Typography', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'layout_options',
                'title' => __('Layout', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'social_links',
                'title' => __('Social', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'styling_options',
                'title' => __('Color Scheme', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'advanced_options',
                'title' => __('Advanced', OC_TEXT_DOMAIN)
            ),
            array(
                'id' => 'importer_section',
                'title' => __('Import', OC_TEXT_DOMAIN)
            ),
        ),
        'settings' => array(
            /* Styling Options */

            array(
                'id' => 'skin_primary',
                'label' => __('Skin - Primary Color', OC_TEXT_DOMAIN),
                'std' => '#de5c5a',
                'type' => 'colorpicker',
                'section' => 'styling_options',
            ),
            array(
                'id' => 'skin_secondary',
                'label' => __('Skin - Secondary Color', OC_TEXT_DOMAIN),
                'std' => '#f8bcc5',
                'type' => 'colorpicker',
                'section' => 'styling_options',
            ),
            array(
                'id' => 'custom_skin_switch',
                'label' => __('Customize Skin', OC_TEXT_DOMAIN),
                'desc' => __('Custom colors selection may override the Primary and Secondary Skin colors at some places', OC_TEXT_DOMAIN),
                'std' => 'off',
                'type' => 'on-off',
                'class' => 'switch_div',
                'section' => 'styling_options',
            ),
            array(
                'id' => 'body_bg_tab',
                'label' => __('Body', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'styling_options',
            ),
            array(
                'id' => 'body_bg',
                'label' => __('Background', OC_TEXT_DOMAIN),
                'desc' => __('Body background with image, color, etc.', OC_TEXT_DOMAIN),
                'std' => array(
                    'background-color' => '#ffffff',
                    'background-image' => $background_img_std,
                ),
                'type' => 'background',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'body_bg_image_switch',
                'label' => __('Show Background Image', OC_TEXT_DOMAIN),
                'std' => 'on',
                'type' => 'on-off',
                'class' => 'switch_div',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'body_text_color',
                'label' => __('Body Text Color', OC_TEXT_DOMAIN),
                'std' => '#000000',
                'type' => 'colorpicker',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'headings_colors',
                'label' => __('Headings Colors', OC_TEXT_DOMAIN),
                'std' => array(
                    'h1' => '#000000',
                    'h2' => '#000000',
                    'h3' => '#000000',
                    'h4' => '#000000',
                    'h5' => '#000000',
                    'h6' => '#000000',
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'page_main_title_color',
                'label' => __('Page Title', OC_TEXT_DOMAIN),
                'std' => '#000000',
                'type' => 'colorpicker',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'body_link_color',
                'label' => __('Link Color', OC_TEXT_DOMAIN),
                'std' => array(
                    'link' => '#de5c5a',
                    'active' => '#de5c5a',
                    'hover' => '#f8bcc5',
                    'visited' => '#de5c5a',
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'header_bg_tab',
                'label' => __('Header', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'styling_options',
            /* 'condition'     => 'custom_skin_switch:is(on)', */
            ),
            array(
                'id' => 'header_bg',
                'label' => __('Background', OC_TEXT_DOMAIN),
                'std' => array(
                    'background-color' => '#FFF8E7',
                ),
                'type' => 'background',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'logo_color',
                'label' => __('Logo Text Color', OC_TEXT_DOMAIN),
                'std' => array(
                    'link' => '#de5c5a',
                    'hover' => '#f8bcc5'
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'menu_bg_tab',
                'label' => __('Menu', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'styling_options',
            /* 'condition'     => 'custom_skin_switch:is(on)', */
            ),
            array(
                'id' => 'menu_bg_color',
                'label' => __('Menu Background Color', OC_TEXT_DOMAIN),
                'type' => 'background',
                'section' => 'styling_options',
                'std' => array(
                    'background-color' => '#FFF8E7',
                ),
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'menu_link_color',
                'label' => __('Menu Item Color', OC_TEXT_DOMAIN),
                'std' => array(
                    'link' => '#000000',
                    'hover' => '#de5c5a',
                    'active' => '#de5c5a',
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'menu_typo_bg',
                'label' => __('Menu Item Background Color', OC_TEXT_DOMAIN),
                'std' => array(
                    'link' => '#FFF8E7',
                    'hover' => '#FFF8E7',
                    'active' => '#FFF8E7',
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'submenu_link_color',
                'label' => __('Submenu Item Color', OC_TEXT_DOMAIN),
                'std' => array(
                    'link' => '#000000',
                    'hover' => '#ffffff',
                    'active' => '#ffffff',
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'submenu_typo_bg',
                'label' => __('Submenu Item Background Color', OC_TEXT_DOMAIN),
                'std' => array(
                    'link' => '#FDF6E1',
                    'hover' => '#de5c5a',
                    'active' => '#de5c5a',
                ),
                'type' => 'link-color',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'page_sections_color_tab',
                'label' => __('Featured', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'styling_options',
            ),
            array(
                'id' => 'page_section_text_color',
                'label' => __('Section Title', OC_TEXT_DOMAIN),
                'std' => '#000000',
                'type' => 'colorpicker',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'page_section_cont_color',
                'label' => __('Section Content', OC_TEXT_DOMAIN),
                'std' => '#000000',
                'type' => 'colorpicker',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'footer_bg_tab',
                'label' => __('Footer', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'styling_options',
            /* 'condition'     => 'custom_skin_switch:is(on)', */
            ),
            array(
                'id' => 'footer_bg',
                'label' => __('Background', OC_TEXT_DOMAIN),
                'type' => 'background',
                'section' => 'styling_options',
                'std' => array(
                    'background-color' => '#ffffff',
                ),
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'footer_tcolor',
                'label' => __('Text Color', OC_TEXT_DOMAIN),
                'type' => 'colorpicker',
                'section' => 'styling_options',
                'std' => '#000000',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'button_bg_tab',
                'label' => __('Buttons', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'styling_options',
            /* 'condition'     => 'custom_skin_switch:is(on)', */
            ),
            /* Content Buttons */
            array(
                'id' => 'content_button_text_color',
                'label' => '<b>' . __('Content Buttons', OC_TEXT_DOMAIN) . '</b>',
                'type' => 'link-color',
                'std' => array(
                    'link' => '#ffffff',
                    'hover' => '#ffffff',
                    'active' => '#de5c5a',
                    'visited' => '#e58681',
                ),
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'content_buttons_border_sw',
                'label' => __('Content Buttons - Show Border', OC_TEXT_DOMAIN),
                'type' => 'on-off',
                'std' => 'off',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'content_buttons_border',
                'label' => __('Content Buttons - Border', OC_TEXT_DOMAIN),
                'type' => 'border',
                'section' => 'styling_options',
                'condition' => 'content_buttons_border_sw:is(on)',
            ),
            array(
                'id' => 'content_buttons_border_rad',
                'label' => __('Content Buttons - Border Radius', OC_TEXT_DOMAIN),
                'desc' => 'pixels',
                'type' => 'numeric-slider',
                'section' => 'styling_options',
                'condition' => 'content_buttons_border_sw:is(on)',
            ),
            /* Form Buttons */
            array(
                'id' => 'form_button_text_color',
                'label' => '<b>' . __('Form Buttons', OC_TEXT_DOMAIN) . '</b>',
                'type' => 'link-color',
                'std' => array(
                    'link' => '#ffffff',
                    'hover' => '#ffffff',
                    'active' => '#de5c5a',
                    'visited' => '#e58681',
                ),
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'form_buttons_border_sw',
                'label' => __('Form Buttons - Show Border', OC_TEXT_DOMAIN),
                'type' => 'on-off',
                'std' => 'off',
                'section' => 'styling_options',
                'condition' => 'custom_skin_switch:is(on)',
            ),
            array(
                'id' => 'form_buttons_border',
                'label' => __('Form Buttons - Border', OC_TEXT_DOMAIN),
                'type' => 'border',
                'section' => 'styling_options',
                'condition' => 'form_buttons_border_sw:is(on)',
            ),
            array(
                'id' => 'form_buttons_border_rad',
                'label' => __('Form Buttons - Border Radius', OC_TEXT_DOMAIN),
                'desc' => 'pixels',
                'type' => 'numeric-slider',
                'section' => 'styling_options',
                'condition' => 'form_buttons_border_sw:is(on)',
            ),
            /* Header Options */

            /* Logo */
            array(
                'id' => 'logo_switch',
                'label' => __('Show Logo', OC_TEXT_DOMAIN),
                'std' => 'off',
                'type' => 'on-off',
                'section' => 'header_option',
                'class' => 'switch_div',
            ),
            array(
                'id' => 'logo_img',
                'label' => __('Upload Logo', OC_TEXT_DOMAIN),
                'desc' => __('Site title will be displayed if no image uploaded.', OC_TEXT_DOMAIN) . '<br>' . __('Site title', OC_TEXT_DOMAIN) . ' : ' . get_bloginfo('blogname'),
                'std' => '',
                'type' => 'upload',
                'section' => 'header_option',
                'class' => 'align_top',
                'condition' => 'logo_switch:is(on)',
                'operator' => 'and'
            ),
            array(
                'id' => 'header_switch',
                'label' => __('Show Header', OC_TEXT_DOMAIN),
                'std' => 'on',
                'type' => 'on-off',
                'section' => 'header_option',
                'class' => 'switch_div',
            ),
            array(
                'id' => 'header_img_switch',
                'label' => __('Show Header Image', OC_TEXT_DOMAIN),
                'std' => 'on',
                'type' => 'on-off',
                'section' => 'header_option',
                'class' => 'switch_div',
                'condition' => 'header_switch:is(on)',
            ),
            array(
                'id' => 'header_img',
                'label' => __('Upload Header Image', OC_TEXT_DOMAIN),
                'std' => $header_img_std,
                'type' => 'upload',
                'section' => 'header_option',
                'class' => 'align_top',
                'condition' => 'header_switch:is(on),header_img_switch:is(on)',
                'operator' => 'and'
            ),
            array(
                'id' => 'header_left_text',
                'label' => __('Header Left Text', OC_TEXT_DOMAIN),
                'std' => '',
                'type' => 'text',
                'section' => 'header_option',
                'class' => 'align_top',
                'condition' => 'header_switch:is(on)',
            ),
            array(
                'id' => 'header_right_text',
                'label' => __('Header Right Text', OC_TEXT_DOMAIN),
                'std' => '',
                'type' => 'text',
                'section' => 'header_option',
                'class' => 'align_top',
                'condition' => 'header_switch:is(on)',
            ),
            array(
                'id' => 'header_subtitle',
                'label' => __('Header Subtitle', OC_TEXT_DOMAIN),
                'std' => '',
                'type' => 'text',
                'section' => 'header_option',
                'class' => 'align_top',
                'condition' => 'header_switch:is(on)',
            ),
            array(
                'id' => 'header_tagline',
                'label' => __('Header Tagline', OC_TEXT_DOMAIN),
                'std' => $header_tagline_std,
                'type' => 'text',
                'section' => 'header_option',
                'class' => 'align_top',
                'condition' => 'header_switch:is(on)',
            ),
            array(
                'id' => 'logo_text_helper',
                'label' => __('Manage Site Title', OC_TEXT_DOMAIN),
                'desc' => '<span class="dashicons dashicons-external"></span> ' . sprintf('<a href="%s" target="_blank">' . __('Manage Logo Text', OC_TEXT_DOMAIN) . '</a>', admin_url('customize.php?autofocus[control]=blogname')) . '<br><br>' . __('To change the font style of logo', OC_TEXT_DOMAIN) . ': <b>' . __('Typography > Header Font Style > Logo Font Style', OC_TEXT_DOMAIN) . '</b>',
                'std' => '',
                'type' => 'textblock',
                'section' => 'header_option',
                'class' => 'ot-upload-attachment-id',
                'condition' => 'logo_switch:is(on)',
                'operator' => 'and'
            ),
            array(
                'id' => 'header_nav_helper',
                'label' => __('Manage Logo Text', OC_TEXT_DOMAIN),
                'desc' => '<span class="dashicons dashicons-external"></span> ' . sprintf('<a href="%s" target="_blank">' . __('Manage Header Menu', OC_TEXT_DOMAIN) . '</a>', admin_url('customize.php?autofocus[panel]=nav_menus')) . '<br><br>' . __('To change the font style of header', OC_TEXT_DOMAIN) . ': <b>' . __('Typography > Header Font Style > Header Menu Font Style', OC_TEXT_DOMAIN) . '</b>',
                'std' => '',
                'type' => 'textblock',
                'section' => 'header_option',
                'class' => 'ot-upload-attachment-id',
            ),
            array(
                'id' => 'favicon_img',
                'desc' => '<span class="dashicons dashicons-external"></span> ' . sprintf('<a href="%s" target="_blank">' . __('Upload Favicon', OC_TEXT_DOMAIN) . '</a>', admin_url('customize.php?autofocus[control]=site_icon')) . '<br><br>' . __('Upload favicon of your website.', OC_TEXT_DOMAIN) . ' : <b>' . __('Customize > Site Identity > Site Icon', OC_TEXT_DOMAIN) . '</b>',
                'std' => '',
                'type' => 'textblock',
                'section' => 'header_option',
                'class' => 'ot-upload-attachment-id',
            ),
            /* #Fonts# */
            array(
                'id' => 'typo_fonts',
                'label' => __('Font Families', OC_TEXT_DOMAIN),
                'desc' => __("Add fonts in your website.", OC_TEXT_DOMAIN) . PHP_EOL . __("The newly added font families will appear after saving the options.", OC_TEXT_DOMAIN),
                'std' => array(
                    array(
                        'family' => 'lato',
                        'variants' => array('700', 'regular', 'italic', '700italic'),
                        'subsets' => array('latin', 'latin-ext')
                    ),
                    array(
                        'family' => 'josefinsans',
                        'variants' => array('700', 'regular', 'italic', '700italic'),
                        'subsets' => array('latin', 'latin-ext')
                    ),
                ),
                'type' => 'google-fonts',
                'section' => 'typo_option',
                'rows' => '',
                'post_type' => '',
                'taxonomy' => '',
                'min_max_step' => '',
                'class' => 'align_top',
                'condition' => '',
                'operator' => 'and'
            ),
            array(
                'id' => 'font_typos',
                'label' => __('Font Styles', OC_TEXT_DOMAIN),
                'desc' => __('Theme\'s default font properties can be changed from the section specific tabs given below.', OC_TEXT_DOMAIN),
                'std' => '',
                'type' => 'textblock-titled',
                'section' => 'typo_option',
                'rows' => '',
                'post_type' => '',
                'taxonomy' => '',
                'min_max_step' => '',
                'class' => '',
                'condition' => '',
                'operator' => 'and'
            ),
            /* Logo Fonts */
            array(
                'id' => 'logof_tab',
                'label' => __('Header', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'logo_typo',
                'label' => __('Logo Font Style', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of logo text only.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '48px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => 'bold',
                    'letter-spacing' => '',
                    'line-height' => '48px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'menu_typo',
                'label' => __('Header Menu Font Style', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of navigation menu in header.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => 'normal',
                    'letter-spacing' => '',
                    'line-height' => '17px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* Body Fonts */
            array(
                'id' => 'bodyf_tab',
                'label' => __('Body', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'body_typo',
                'label' => __('Body', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of content areas only.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '29px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* H1 - H6 */
            array(
                'id' => 'h1_typo',
                'label' => __('H1 Font Style', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'josefinsans',
                    'font-color' => '#000000',
                    'font-size' => '30px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '700',
                    'letter-spacing' => '',
                    'line-height' => 'normal',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'h2_typo',
                'label' => __('H2 Font Style', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'josefinsans',
                    'font-color' => '#000000',
                    'font-size' => '30px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '30px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'h3_typo',
                'label' => __('H3 Font Style', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'josefinsans',
                    'font-color' => '#000000',
                    'font-size' => '26px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => 'normal',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'h4_typo',
                'label' => __('H4 Font Style', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => 'normal',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'h5_typo',
                'label' => __('H5 Font Style', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '30px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'h6_typo',
                'label' => __('H6 Font Style', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '12px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '21px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* Page Sections */
            array(
                'id' => 'hsections_tab',
                'label' => __('Page Sections', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'page_title_typo',
                'label' => __('Page Title', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of main titles on pages.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'josefinsans',
                    'font-color' => '#000000',
                    'font-size' => '30px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '30px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'section_heading_typo',
                'label' => __('Section Titles Font Style', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of section titles on pages.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'josefinsans',
                    'font-color' => '#000000',
                    'font-size' => '30px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '30px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'sections_content_typo',
                'label' => __('Text', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of content areas only.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => '400',
                    'letter-spacing' => '',
                    'line-height' => '29px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* Footer */
            array(
                'id' => 'footerf_tab',
                'label' => __('Footer', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'footerh_typo',
                'label' => __('Footer', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of the Footer.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'comfortaa',
                    'font-color' => '#000000',
                    'font-size' => '12px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => 'normal',
                    'letter-spacing' => '',
                    'line-height' => '22px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* Footer */
            array(
                'id' => 'footerf_tab',
                'label' => __('Footer', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'footerh_typo',
                'label' => __('Footer', OC_TEXT_DOMAIN),
                'desc' => __('This will change the typography of the Footer.', OC_TEXT_DOMAIN),
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => 'normal',
                    'letter-spacing' => '',
                    'line-height' => '29px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* Buttons */
            array(
                'id' => 'buttonsf_tab',
                'label' => __('Buttons', OC_TEXT_DOMAIN),
                'type' => 'tab',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'content_button_typo',
                'label' => __('Content Buttons', OC_TEXT_DOMAIN),
                'desc' => '',
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => 'normal',
                    'letter-spacing' => '',
                    'line-height' => '50px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            array(
                'id' => 'form_button_typo',
                'label' => __('Form Buttons', OC_TEXT_DOMAIN),
                'desc' => '',
                'std' => array(
                    'font-family' => 'lato',
                    'font-color' => '#000000',
                    'font-size' => '16px',
                    'font-style' => 'normal',
                    'font-variant' => 'normal',
                    'font-weight' => 'normal',
                    'letter-spacing' => '',
                    'line-height' => '30px',
                    'text-decoration' => 'none',
                    'text-transform' => 'none',
                ),
                'type' => 'typography',
                'section' => 'typo_option',
            ),
            /* Blog Options */
            array(
                'id' => 'show_post_meta',
                'label' => __('Show Post Metadata', OC_TEXT_DOMAIN),
                'desc' => __('This will show/hide the post details.', OC_TEXT_DOMAIN) . '<br>' . __('For example: Post Author, Published Date, Post Categories', OC_TEXT_DOMAIN),
                'std' => 'on',
                'type' => 'on-off',
                'section' => 'layout_options',
                'class' => 'switch_div',
            ),
            // layout section
            array(
                'id' => 'blog_layout_radio',
                'label' => __('Blog Listing - Page Layout', OC_TEXT_DOMAIN),
                'desc' => __('This will change the visibility and position of sidebar on the blog post listing pages.', OC_TEXT_DOMAIN),
                'std' => 'right-sidebar',
                'type' => 'radio-image',
                'section' => 'layout_options',
                'choices' => array(
                    array(
                        'value' => 'right-sidebar',
                        'label' => __('Right Sidebar', OC_TEXT_DOMAIN),
                        'src' => get_template_directory_uri() . '/option-tree/assets/images/layout/right-sidebar.png',
                    ),
                    array(
                        'value' => 'full-width',
                        'label' => __('No Sidebar', OC_TEXT_DOMAIN),
                        'src' => get_template_directory_uri() . '/option-tree/assets/images/layout/full-width.png',
                    ),
                    array(
                        'value' => 'left-sidebar',
                        'label' => __('Left Sidebar', OC_TEXT_DOMAIN),
                        'src' => get_template_directory_uri() . '/option-tree/assets/images/layout/left-sidebar.png',
                    ),
                )
            ),
            array(
                'id' => 'single_post_layout_radio',
                'label' => __('Single Post - Page Layout', OC_TEXT_DOMAIN),
                'desc' => __('This will change the visibility and position of sidebar on the post details pages.', OC_TEXT_DOMAIN) . PHP_EOL . __('Note: You can override this setting on a specific post.', OC_TEXT_DOMAIN),
                'std' => 'right-sidebar',
                'type' => 'radio-image',
                'section' => 'layout_options',
                'choices' => array(
                    array(
                        'value' => 'right-sidebar',
                        'label' => __('Right Sidebar', OC_TEXT_DOMAIN),
                        'src' => get_template_directory_uri() . '/option-tree/assets/images/layout/right-sidebar.png',
                    ),
                    array(
                        'value' => 'full-width',
                        'label' => __('No Sidebar', OC_TEXT_DOMAIN),
                        'src' => get_template_directory_uri() . '/option-tree/assets/images/layout/full-width.png',
                    ),
                    array(
                        'value' => 'left-sidebar',
                        'label' => __('Left Sidebar', OC_TEXT_DOMAIN),
                        'src' => get_template_directory_uri() . '/option-tree/assets/images/layout/left-sidebar.png',
                    ),
                )
            ),
            /* Social Links */
            array(
                'id' => 'social_icons',
                'label' => __('Social Links', OC_TEXT_DOMAIN),
                'desc' => __('Enter your social profile links here:', OC_TEXT_DOMAIN),
                'type' => 'list-item',
                'section' => 'social_links',
                'rows' => '',
                'post_type' => '',
                'taxonomy' => '',
                'min_max_step' => '',
                'class' => 'hide_title social_grid align_top',
                'operator' => 'and',
                'settings' => array(
                    array(
                        'id' => 'social_icon_entry',
                        'label' => __('Social Profile Icon', OC_TEXT_DOMAIN),
                        'desc' => '',
                        'class' => 'social_icons_array',
                        'type' => 'radio-image',
                        'choices' => array(
                            array(
                                'value' => 'facebook',
                                'label' => 'Facebook',
                                'src' => get_template_directory_uri() . '/assets/images/social/facebook.svg',
                            ),
                            array(
                                'value' => 'linkedin',
                                'label' => 'LinkedIn',
                                'src' => get_template_directory_uri() . '/assets/images/social/linkedin.svg',
                            ),
                            array(
                                'value' => 'twitter',
                                'label' => 'Twitter',
                                'src' => get_template_directory_uri() . '/assets/images/social/twitter.svg',
                            ),
                            array(
                                'value' => 'instagram',
                                'label' => 'Instagram',
                                'src' => get_template_directory_uri() . '/assets/images/social/instagram.svg',
                            ),
                            array(
                                'value' => 'skype',
                                'label' => 'Skype',
                                'src' => get_template_directory_uri() . '/assets/images/social/skype.svg',
                            ),
                            array(
                                'value' => 'youtube',
                                'label' => 'Youtube',
                                'src' => get_template_directory_uri() . '/assets/images/social/youtube.svg',
                            ),
                            array(
                                'value' => 'vimeo',
                                'label' => 'Vimeo',
                                'src' => get_template_directory_uri() . '/assets/images/social/vimeo.svg',
                            ),
                            array(
                                'value' => 'pinterest',
                                'label' => 'Pinterest',
                                'src' => get_template_directory_uri() . '/assets/images/social/pinterest.svg',
                            ),
                            array(
                                'value' => 'stumbleupon',
                                'label' => 'Stumblupon',
                                'src' => get_template_directory_uri() . '/assets/images/social/stumblupon.svg',
                            ),
                            array(
                                'value' => 'tumblr',
                                'label' => 'Tumblr',
                                'src' => get_template_directory_uri() . '/assets/images/social/tumblr.svg',
                            ),
                            array(
                                'value' => 'behance',
                                'label' => 'Behance',
                                'src' => get_template_directory_uri() . '/assets/images/social/behance.svg',
                            ),
                            array(
                                'value' => 'blogger',
                                'label' => 'Blogger',
                                'src' => get_template_directory_uri() . '/assets/images/social/blogger.svg',
                            ),
                            array(
                                'value' => 'delicios',
                                'label' => 'Delicios',
                                'src' => get_template_directory_uri() . '/assets/images/social/delicios.svg',
                            ),
                            array(
                                'value' => 'github',
                                'label' => 'Github',
                                'src' => get_template_directory_uri() . '/assets/images/social/github.svg',
                            ),
                            array(
                                'value' => 'amazon',
                                'label' => 'Amazon',
                                'src' => get_template_directory_uri() . '/assets/images/social/amazon.svg',
                            ),
                            array(
                                'value' => 'android',
                                'label' => 'Android',
                                'src' => get_template_directory_uri() . '/assets/images/social/android.svg',
                            ),
                            array(
                                'value' => 'apple',
                                'label' => 'Apple',
                                'src' => get_template_directory_uri() . '/assets/images/social/apple.svg',
                            ),
                            array(
                                'value' => 'digg',
                                'label' => 'Digg',
                                'src' => get_template_directory_uri() . '/assets/images/social/digg.svg',
                            ),
                            array(
                                'value' => 'dribble',
                                'label' => 'Dribble',
                                'src' => get_template_directory_uri() . '/assets/images/social/dribble.svg',
                            ),
                            array(
                                'value' => 'dropbox',
                                'label' => 'Dropbox',
                                'src' => get_template_directory_uri() . '/assets/images/social/dropbox.svg',
                            ),
                            array(
                                'value' => 'ebay',
                                'label' => 'Ebay',
                                'src' => get_template_directory_uri() . '/assets/images/social/ebay.svg',
                            ),
                            array(
                                'value' => 'foursquare',
                                'label' => 'Foursquare',
                                'src' => get_template_directory_uri() . '/assets/images/social/foursquare.svg',
                            ),
                            array(
                                'value' => 'myspace',
                                'label' => 'Myspace',
                                'src' => get_template_directory_uri() . '/assets/images/social/myspace.svg',
                            ),
                            array(
                                'value' => 'soundcloud',
                                'label' => 'Soundcloud',
                                'src' => get_template_directory_uri() . '/assets/images/social/soundcloud.svg',
                            ),
                            array(
                                'value' => 'stackoverflow',
                                'label' => 'Stackoverflow',
                                'src' => get_template_directory_uri() . '/assets/images/social/stackoverflow.svg',
                            ),
                            array(
                                'value' => 'windows',
                                'label' => 'Windows',
                                'src' => get_template_directory_uri() . '/assets/images/social/windows.svg',
                            ),
                            array(
                                'value' => 'wordpress',
                                'label' => 'WordPress',
                                'src' => get_template_directory_uri() . '/assets/images/social/wordpress.svg',
                            ),
                            array(
                                'value' => 'rss',
                                'label' => 'RSS',
                                'src' => get_template_directory_uri() . '/assets/images/social/rss.svg',
                            ),
                            array(
                                'value' => 'connection',
                                'label' => 'Social',
                                'src' => get_template_directory_uri() . '/assets/images/social/general.svg',
                            ),
                        ),
                    ),
                    array(
                        'id' => 'social_profile_link',
                        'label' => __('Social Profile Link', OC_TEXT_DOMAIN),
                        'std' => '#',
                        'type' => 'text',
                    ),
                ),
                'std' => $social_std,
            ),
            array(
                'id' => 'footer_color',
                'label' => __('Footer Color', OC_TEXT_DOMAIN),
                'type' => 'colorpicker',
                'section' => 'footer_options',
                'condition' => 'footer_widgets_switch:is(on)',
                'std' => '#ffffff',
            ),
            array(
                'id' => 'footer_widgets_url',
                'label' => __('Manage Footer Widgets', OC_TEXT_DOMAIN),
                'desc' => sprintf('<span class="dashicons dashicons-external"></span> <a href="%s" target="_blank">' . __('Edit Footer Widgets', OC_TEXT_DOMAIN) . '</a>', admin_url('widgets.php')),
                'type' => 'textblock-titled',
                'section' => 'footer_options',
            ),
            /* Miscellaneous */

            /* Custom CSS */
            array(
                'id' => 'custom_css',
                'label' => __('Custom CSS', OC_TEXT_DOMAIN),
                'desc' => __('The rules added here will be applied as additional CSS.', OC_TEXT_DOMAIN),
                'type' => 'css',
                'section' => 'advanced_options',
                'std' => '/* Your custom CSS goes here */',
            ),
            array(
                'id' => 'head_scripts',
                'label' => __('Head Scripts', OC_TEXT_DOMAIN),
                'desc' => __('Scripts to be inserted in "head" tag.', OC_TEXT_DOMAIN),
                'std' => '',
                'type' => 'textarea-simple',
                'rows' => '3',
                'section' => 'advanced_options',
            ),
            array(
                'id' => 'footer_scripts',
                'label' => __('Footer Scripts', OC_TEXT_DOMAIN),
                'desc' => __('Scripts to be inserted after footer.', OC_TEXT_DOMAIN),
                'std' => '',
                'type' => 'textarea-simple',
                'rows' => '3',
                'section' => 'advanced_options',
            ),
            array(
                'id' => '404_content',
                'label' => __('404 Page Content', OC_TEXT_DOMAIN),
                'type' => 'textarea',
                'section' => 'advanced_options',
                'std' => '<h1>{404} Not Found!</h1><h3>Sorry, we could not find what you were looking for.</h3>',
            ),
            array(
                'id' => 'importer_button',
                'label' => __('Import', OC_TEXT_DOMAIN),
                'type' => 'onecom_importer',
                'section' => 'importer_section',
                'class' => 'importer',
            ),
        )
    );

    /* allow settings to be filtered before saving */
    $custom_settings = apply_filters(ot_settings_id() . '_args', $custom_settings);

    /* settings are not the same update the DB */
    if ($saved_settings !== $custom_settings) {
        update_option(ot_settings_id(), $custom_settings);
    }

    /* Lets OptionTree know the UI Builder is being overridden */
    global $ot_has_custom_theme_options;
    $ot_has_custom_theme_options = true;
}
