<?php
global $ncmaz_redux_demo;

$showToogleDarkmode = false;

if ($ncmaz_redux_demo !== null) {
    $showToogleDarkmode = boolval($ncmaz_redux_demo['nc-general-settings--header-switch-enableDarkmode']);
}

class WalkerSidebarMenu extends Walker_Nav_Menu
{
    function start_lvl(&$output, $depth = 0, $args = array())
    {
        // Depth-dependent classes.
        $indent = ($depth > 0  ? str_repeat("\t", $depth) : ''); // code indent
        $display_depth = ($depth + 1); // because it counts the first submenu as 0
        $classes = array(
            'sub-menu',
            ($display_depth >= 2 ? 'sub-sub-menu' : ''),
        );
        $class_names = implode(' ', $classes);

        // Build HTML for output.
        $output .= "\n" . $indent . '<ul class="' . $class_names . '">' . "\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $indent = ($depth > 0 ? str_repeat("\t", $depth) : ''); // code indent
        // Depth-dependent classes.
        $depth_classes = array(
            ($depth == 0 ? 'main-menu-item' : 'sub-menu-item'),
            ($depth >= 2 ? 'sub-sub-menu-item' : ''),
        );
        $depth_class_names = esc_attr(implode(' ', $depth_classes));

        // Passed classes.
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $class_names = esc_attr(implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item)));

        // Build HTML.
        $output .= $indent . '<li id="nav-menu-item-' . $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . '">';

        // Link attributes.
        $attributes  = !empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target)     ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url)        ? ' href="'   . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="menu-link ' . ($depth > 0 ? 'sub-menu-link' : 'main-menu-link') . '"';

        // Build HTML output and pass through the proper filter.
        $item_output = sprintf(
            '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
            $args->before,
            $attributes,
            $args->link_before,
            '<span>' . apply_filters('the_title', $item->title, $item->ID) . '</span>' . (boolval($args->walker->has_children) ? '<div class="icon-after-menu block flex-grow"><div class="flex justify-end flex-grow" ><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ml-2 h-4 w-4 text-neutral-500" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg></div></div>' : ''),
            $args->link_after,
            $args->after
        );
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}
?>

<div id="site-navigation-mobile" class="hidden fixed inset-0 z-max overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed left-0 top-0 bottom-0 w-full md:w-auto z-max outline-none focus:outline-none">
        <div class="z-10 relative">
            <div class="overflow-y-auto w-full max-w-sm min-w-[300px] h-screen py-2 transition transform shadow-lg ring-1 dark:ring-neutral-700 bg-white dark:bg-neutral-900 divide-y-2 divide-neutral-100 dark:divide-neutral-800">
                <div class="py-6 px-5">
                    <!-- LOGO -->
                    <?php get_template_part('template-parts/header/site-branding'); ?>

                    <!-- WIDGET -->
                    <div class="ncWidget-mobile-sidebar-menu text-neutral-700 dark:text-neutral-300 text-sm ">
                        <?php if (is_active_sidebar('mobile-menu-sidebar')) : ?>
                            <?php dynamic_sidebar('mobile-menu-sidebar'); ?>
                        <?php endif; ?>
                    </div>

                    <!-- BUTTON CLOSE MODAL -->
                    <span id="btn-close-modal-mobile-sidebar-menu" class="absolute right-2 top-2 p-1">
                        <button class="w-8 h-8 flex items-center justify-center rounded-full text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700  focus:outline-none">
                            <span class="sr-only">
                                <?php esc_html__('Close', 'ncmaz'); ?>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </span>

                </div>

                <!-- MENU -->
                <?php
                wp_nav_menu(
                    array(
                        'theme_location'  => 'primary',
                        'menu_class'      => 'menu-wrapper',
                        'container_class' => 'primary-menu-container w-full',
                        'items_wrap'      => '<ul id="mobile-menu-list" class="flex flex-col py-6 px-2 space-y-1 %2$s">%3$s</ul>',
                        'fallback_cb'     => false,
                        'walker' => new WalkerSidebarMenu()

                    )
                );
                ?>

                <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
                    <div class="flex items-center justify-between py-6 px-5 space-x-4">
                        <!-- BUTTON FOOTER -->
                        <?php if ($ncmaz_redux_demo['nc-general-settings--mobile-menu-btn-foot-text']) : ?>
                            <a href="<?php echo esc_url($ncmaz_redux_demo['nc-general-settings--mobile-menu-btn-foot-href']); ?>" target="_blank" rel="noopener noreferrer">
                                <span class="nc-Button relative h-auto inline-flex items-center justify-center rounded-full transition-colors text-sm sm:text-base font-medium px-4 py-3 sm:px-6  ttnc-ButtonPrimary disabled:bg-opacity-70 bg-primary-6000 hover:bg-primary-700 text-neutral-50  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-6000 dark:focus:ring-offset-0">
                                    <?php echo esc_html($ncmaz_redux_demo['nc-general-settings--mobile-menu-btn-foot-text']); ?>
                                </span>
                            </a>
                        <?php endif; ?>
                        <!-- DARKMODE -->
                        <div class="block">
                            <?php
                            $SwitchDarkModeProps = (object)[];
                            $SwitchDarkModeProps->className = 'w-12 h-12 bg-neutral-100 dark:bg-neutral-800';
                            ?>
                            <?php if ($showToogleDarkmode) : ?>
                                <div data-is-react-component="SwitchDarkMode" data-component-props="<?php echo esc_attr(json_encode($SwitchDarkModeProps)); ?>"></div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <div class="fixed inset-0 bg-neutral-900 bg-opacity-50" id="ncmaz-headlessui-dialog-overlay-84" aria-hidden="true"></div>
    </div>
</div>