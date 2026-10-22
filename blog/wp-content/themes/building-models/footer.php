<!--footer start from here-->
<?php
$footer_widgets = wp_get_sidebars_widgets();
if (!empty($footer_widgets['footer-1']) || !empty($footer_widgets['footer-2']) || !empty($footer_widgets['foote-3']) || !empty($footer_widgets['footer-4']) || !empty($footer_widgets['footer-5'])) :
    ?>
    <footer id="site-footer" role="contentinfo">
        <div class="footer-widgets">
            <div class="container no-padding">
                <div class="row">
                    <div class="col-md-4 flex-column footer-sidebar-1">
                        <div class="v-center">
                            <?php dynamic_sidebar('footer-1'); ?>
                        </div>

                    </div>
                    <div class="col-md-4 push-md-4 flex-column footer-sidebar-3">
                        <div class="v-center">
                            <?php dynamic_sidebar('footer-3'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 pull-md-4 flex-column text-center footer-sidebar-2">
                        <div class="v-center">
                            <?php dynamic_sidebar('footer-2'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

<?php endif; ?>
</div>
</div>
<!--footer ends here-->

<?php

function mobile_menu() {
    echo '<!-- START Mobile Menu -->
    <div id="sticky_menu_wrapper" class="mobile-only">';
    wp_nav_menu(
            array(
                'theme_location' => 'primary_building_models',
                'menu_id' => 'sticky_menu',
                'container' => '',
            )
    );

    echo '<div class="sticky_menu_collapse"><i></i></div></div>';
}

add_action('wp_footer', 'mobile_menu');
?>
<?php wp_footer(); ?>

</body>
</html>