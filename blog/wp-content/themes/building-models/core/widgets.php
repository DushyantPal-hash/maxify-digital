<?php
add_action( 'load-widgets.php', 'one_color_picker_load' );

function one_color_picker_load() {    
    wp_enqueue_style( 'wp-color-picker' );        
    wp_enqueue_script( 'wp-color-picker' );
}

/**
* Social widget
**/
if( ! class_exists( 'one_social_widget' ) ) {
    class one_social_widget extends WP_Widget {

        function __construct() {

            parent::__construct(
                'one_social_widget', // Base ID
                __( 'Social Icons', OC_TEXT_DOMAIN ), // Name
                array( 'description' => __( 'Displays social icons list.', OC_TEXT_DOMAIN ), ) // Args
            );
        }

        public function widget( $args, $instance ) {
            global $widget_default_color;
            $widget_default_color = true;

            $widget_id = $args['widget_id'] ?? '';
            // Our variables from the widget settings
            $title = $instance['title'];

            ob_start();
            echo $args['before_widget'];

            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            if ( empty( $instance['icon_default_color'] ) || $instance['icon_default_color'] === 'off' ) {
                if ( ! empty( $instance['icon_color'] ) ) {
                    echo '
                        <style>
                            html #'.$widget_id.' .social-icons ul li > a svg * {
                                fill : '.$instance['icon_color'].'
                            }
                        </style>';

                }
                if ( ! empty( $instance['icon_hover_color'] ) ) {
                    echo '
                        <style>
                        html #'.$widget_id.' .social-icons ul li:hover > a svg * {
                            fill : '.$instance['icon_hover_color'].'
                        }
                        </style>';
                }
                $widget_default_color = false;
            }

            /* Include social media links */
            include(THM_DIR_PATH.'/template-parts/social-icons.php');
            echo $args['after_widget'];
            ob_end_flush();
        }

        public function form( $instance ) {
            $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

            $icon_color = $icon_hover_color = $icon_default_color = '';

            $skin_customize_on_off = ot_get_option( 'skin_customize_on_off' );
            if( $skin_customize_on_off == 'on' ) {
                $icon_color = ot_get_option( 'skin_customize_social_color' );
                $icon_hover_color = ot_get_option( 'skin_customize_social_hover_color' );
            }

            $icon_color = ! empty( $instance['icon_color'] ) ? $instance['icon_color'] : $icon_color;
            $icon_hover_color = ! empty( $instance['icon_hover_color'] ) ? $instance['icon_hover_color'] : $icon_hover_color;
            $icon_default_color = ! empty( $instance['icon_default_color'] ) ? $instance['icon_default_color'] : $icon_default_color;

            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', OC_TEXT_DOMAIN ); ?>:</label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <table class="form-table">
                <tr>
                    <td style="width:45%"><label for="<?php echo $this->get_field_id( 'icon_default_color' ); ?>"><?php _e( 'Social Icon Default Colors', OC_TEXT_DOMAIN ); ?>:</label></td>
                    <td><input class="one-social-default-checkbox" id="<?php echo $this->get_field_id( 'icon_default_color' ); ?>" name="<?php echo $this->get_field_name( 'icon_default_color' ); ?>" type="checkbox" <?php checked( 'on', esc_attr( $icon_default_color ) ); ?> /></td>
                </tr>
                <tr class="toggle-tr">
                    <td style="vertical-align:top"><label for="<?php echo $this->get_field_id( 'icon_color' ); ?>"><?php _e( 'Icon Color', OC_TEXT_DOMAIN ); ?>:</label></td>
                    <td><input class="widefat colorpicker" id="<?php echo $this->get_field_id( 'icon_color' ); ?>" name="<?php echo $this->get_field_name( 'icon_color' ); ?>" type="text" value="<?php echo esc_attr( $icon_color ); ?>" /></td>
                </tr>
                <tr class="toggle-tr">
                    <td style="vertical-align:top"><label for="<?php echo $this->get_field_id( 'icon_hover_color' ); ?>"><?php _e( 'Icon Hover Color', OC_TEXT_DOMAIN ); ?>:</label></td>
                    <td><input class="widefat colorpicker" id="<?php echo $this->get_field_id( 'icon_hover_color' ); ?>" name="<?php echo $this->get_field_name( 'icon_hover_color' ); ?>" type="text" value="<?php echo esc_attr( $icon_hover_color ); ?>" /></td>
                </tr>
                <tr class="toggle-tr">
                    <td colspan="2">
                        <p class="description"><?php _e( 'If colors are not set, skin colors will be applied.', OC_TEXT_DOMAIN ); ?></p>
                    </td>
                </tr>
            </table>

            <p><span class="dashicons dashicons-external"></span> <a href="<?php echo menu_page_url( 'octheme_settings', false ).'#section_social'; ?>" target="_blank"><?php _e( 'Manage Social Icons', OC_TEXT_DOMAIN ) ?></a></p>
            <br/>
            <?php
        }

        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['icon_color'] = ( ! empty( $new_instance['icon_color'] ) ) ? strip_tags( $new_instance['icon_color'] ) : '';
            $instance['icon_hover_color'] = ( ! empty( $new_instance['icon_hover_color'] ) ) ? strip_tags( $new_instance['icon_hover_color'] ) : '';
            $instance['icon_default_color'] = ( ! empty( $new_instance['icon_default_color'] ) ) ? strip_tags( $new_instance['icon_default_color'] ) : '';
            return $instance;
        }
    }
}

/**
 * Register widgets
 */
add_action('widgets_init', 'one_theme_register_widgets');

function one_theme_register_widgets() {
    register_widget("one_social_widget");
}

?>