<?php

// SINGLE SIDEBAR WIDGET REGISTER
add_action('widgets_init', "ncmazThemeSingleRegisterSidebars");
function ncmazThemeSingleRegisterSidebars()
{
    register_sidebar(
        [
            'name'          => esc_html__('Single Sidebar', 'ncmaz'),
            'description'   => esc_html__('Displaying widget items on the Sidebar area single page', 'ncmaz'),
            'id'            => 'single-sidebar',
            'before_widget' => '<div  id="%1$s" class="nc-WidgetItem overflow-hidden widget %2$s relative p-4 xl:p-5 rounded-3xl bg-neutral-100 dark:bg-neutral-800" >',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="nc-WidgetHeading1 relative pb-4 xl:pb-5 mb-4 xl:mb-5 flex items-center justify-between "><h4 class="text-lg text-neutral-900 dark:text-neutral-100 font-semibold flex-grow">',
            'after_title'   => '</h4><div class="absolute -inset-x-10 bottom-0 border-b border-neutral-200 dark:border-neutral-700"></div></div>'
        ]
    );
}

// FOOTER WIDGET REGISTER
add_action('widgets_init', "ncmazThemeFooterWidgetRegisterSidebars");
function ncmazThemeFooterWidgetRegisterSidebars()
{
    // FOOTER 1
    register_sidebar(
        [
            'name'          => esc_html__('Footer-1 Sidebar', 'ncmaz'),
            'description'   => esc_html__('Displaying widget items on the Sidebar area column 1 of footer', 'ncmaz'),
            'id'            => 'footer-1-sidebar',
            'before_widget' => '<div>',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="ncmaz-footer-sidebar-title"><span class="truncate">',
            'after_title'   => '</span></div>'
        ]
    );

    // FOOTER 2
    register_sidebar(
        [
            'name'          => esc_html__('Footer-2 Sidebar', 'ncmaz'),
            'description'   => esc_html__('Displaying widget items on the Sidebar area column 2 of footer', 'ncmaz'),
            'id'            => 'footer-2-sidebar',
            'before_widget' => '<div>',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="ncmaz-footer-sidebar-title"><span class="truncate">',
            'after_title'   => '</span></div>'
        ]
    );

    // FOOTER 3
    register_sidebar(
        [
            'name'          => esc_html__('Footer-3 Sidebar', 'ncmaz'),
            'description'   => esc_html__('Displaying widget items on the Sidebar area column 3 of footer', 'ncmaz'),
            'id'            => 'footer-3-sidebar',
            'before_widget' => '<div>',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="ncmaz-footer-sidebar-title"><span class="truncate">',
            'after_title'   => '</span></div>'
        ]
    );

    // FOOTER 4
    register_sidebar(
        [
            'name'          => esc_html__('Footer-4 Sidebar', 'ncmaz'),
            'description'   => esc_html__('Displaying widget items on the Sidebar area column 4 of footer', 'ncmaz'),
            'id'            => 'footer-4-sidebar',
            'before_widget' => '<div>',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="ncmaz-footer-sidebar-title"><span class="truncate">',
            'after_title'   => '</span></div>'
        ]
    );
}
