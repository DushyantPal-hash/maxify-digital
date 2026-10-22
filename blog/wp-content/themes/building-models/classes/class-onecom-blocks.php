<?php

/**
 * Class MomentsBlocks
 * Registers additional blocks in Gutenberg editor
 */
class OnecomBlocks {

    private $theme_url;
    const OC_CAPTCHA_KEY = '1ASD2A4D2AA4DA15A';
    const OC_CLOSING_DIV = '</div>';
    const POST_CONTENT = 'postContent';
    const OC_BLOCK_CF_JS = 'oc_block_contact_form_js';
    const OC_DEFAULT = 'oc_block_contact_form_js';
    const OC_STATUS = 'status';
    const OC_ERROR = 'error';
    const CP_IMG_PATH = '/inc/image.php?i=';
    const OC_CPT = 'oc_cpt';
    const OC_CPT_VAL = 'oc_captcha_val';
    const OC_FORM_DATA = 'formData';
    const OC_ERR_MSG = 'Some error occurred, please reload the page and try again.';
    const OC_TEXT_DOMAIN = '';

    public function __construct() {
        $this->theme_url = get_stylesheet_directory_uri();
        add_filter( 'block_categories_all', [ $this, 'oc_block_categories' ], 10, 2 );
        add_action( 'init', [ $this, 'oc_register_block' ] );

        // Generate booking_form nonce via ajax and return to caller
        add_action( 'wp_ajax_oc_booking_nonce', [ $this, 'oc_nonce_cb' ] );
        add_action( 'wp_ajax_nopriv_oc_booking_nonce', [ $this, 'oc_nonce_cb' ] );
        add_action( 'wp_ajax_oc_refresh_captcha', [ $this, 'oc_get_captcha_string' ] );
        add_action( 'wp_ajax_nopriv_oc_get_fields', [ $this, 'oc_secure_fields' ] );
        add_action( 'wp_ajax_oc_get_fields', [ $this, 'oc_secure_fields' ] );


        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        add_action( 'wp_ajax_oc_form_submit', [ $this, 'contact_form_cb' ] );
        add_action( 'wp_ajax_nopriv_oc_form_submit', [ $this, 'contact_form_cb' ] );
    }

    public function enqueue_scripts() {
        $ignored_post_types = [
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
        ];
        $post_types         = get_post_types( null, 'objects' );
        $post_type_array    = [];
        if ( $post_types ) {
            foreach ( $post_types as $post_type ) {
                if ( in_array( $post_type->name, $ignored_post_types ) ) {
                    continue;
                }
                $post_type_array[] = [
                    'label' => $post_type->label,
                    'value' => $post_type->name,
                ];
            }
        }
    }

    /**
     * Add one.com as a block category
     *
     * @param $categories
     * @param $post
     *
     * @return array
     */
    public function oc_block_categories( $categories, $post ) {
        $post = '';

        return array_merge(
            $categories,
            array(
                array(
                    'slug'  => 'onecom-blocks',
                    'title' => __( 'one.com blocks', OC_TEXT_DOMAIN ),
                ),
            )
        );
    }

    function oc_custom_post_render_callback( $attributes, $content ) {
        $content        = '';
        $post_type      = $attributes['posttype'];
        $posts_per_page = $attributes['postsPerPage'];
        $posts          = get_posts( [
            'post_type'      => $post_type,
            'posts_per_page' => $posts_per_page,
        ] );
        $html           = '';
        foreach ( $posts as $post_item ) {
            $html .= '<h2 class="oc-cpt-title">' . get_the_title( $post_item->ID ) . '</h2>';
            if ( $attributes['thumbnail'] == '1' ) {
                $html .= '<div class="oc-cpt-thumbnail">' . get_the_post_thumbnail( $post_item->ID ) . self::OC_CLOSING_DIV;
            }
            if ( $attributes[ self::POST_CONTENT ] == 'excerpt' ) {
                $html .= '<div class="oc-cpt-content oc-post-excerpt">' . get_the_excerpt( $post_item->ID ) . self::OC_CLOSING_DIV;
            } elseif ( $attributes[ self::POST_CONTENT ] == 'content' ) {
                $html .= '<div class="oc-cpt-content">' . get_the_content( null, null, $post_item->ID ) . self::OC_CLOSING_DIV;
            }
        }

        return $html;
    }

    function oc_register_block() {
        wp_register_script(
            'oc_block_scripts',
            $this->theme_url . '/assets/js/contact-forms-block.js',
            $this->oc_dependency_array()
        );

        wp_register_script(
            self::OC_BLOCK_CF_JS,
            $this->theme_url . '/assets/js/oc_block_contact_form.js',
            [ 'jquery' ]
        );
        wp_register_style(
            'oc_block_contact_form_backend',
            $this->theme_url . '/assets/css/contact-form-backend.css'
        );
        wp_register_style(
            'oc_block_contact_form',
            $this->theme_url . '/assets/css/contact-form.css'
        );

        register_block_type( 'onecom/contact-form', array(
            'editor_script' => 'oc_block_scripts',
            'editor_style'  => 'oc_block_contact_form_backend',
            'style'         => 'oc_block_contact_form',
            'script'        => self::OC_BLOCK_CF_JS,
        ) );
        wp_localize_script( self::OC_BLOCK_CF_JS, 'ocAjaxData', [
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'waitText' => __( 'Please wait', OC_TEXT_DOMAIN )
        ] );
    }

    /**
     * Function oc_sucure_forms
     * Secure form submission, try to block spams by using captcha and honeypot
     *
     * @param void
     *
     * @return void
     */
    public function oc_secure_form() {

        /* Check Captcha */
        if ( ! isset( $_POST[ self::OC_CPT ] ) || ! isset( $_POST[ self::OC_CPT_VAL ] ) || ! $_POST[ self::OC_CPT_VAL ]
            || ! $_POST[ self::OC_CPT ] || ! $this->oc_validate_captcha( $_POST[ self::OC_CPT_VAL ], $_POST[ self::OC_CPT ] )
        ) {
            wp_send_json( [
                self::OC_STATUS => self::OC_ERROR,
                'text'          => __( 'Invalid answer, please try again.', OC_TEXT_DOMAIN )
            ] );
        }

        /** Check Honey Pot field */
        if ( ! isset( $_POST['oc_csrf_token'] ) || $_POST['oc_csrf_token'] !== '' ) {
            wp_send_json( [
                self::OC_STATUS => self::OC_ERROR,
                'text'          => __( 'Some error occurred, please reload the page and try again.', OC_TEXT_DOMAIN ),
            ] );
        }
    }

    /**
     * Function oc_nonce_cb
     * Ajax handler to generate nonce and return it as response
     *
     * @param void
     *
     * @return void
     */
    public function oc_nonce_cb() {
        wp_send_json( [
            'nonce'         => wp_create_nonce( 'booking_form' ),
            self::OC_STATUS => '0',
        ] );
    }

    /**
     * Function oc_secure_fields
     * Return HTML string contaning the fields that will be used in forms to track
     * token etc.
     *
     * @param void
     *
     * @return string
     */
    public function oc_secure_fields() {
        $oc_token = $this->oc_get_captcha_string();
        $fields   = '<label class="d-block">' . __( 'Type in the answer to the sum below:', OC_TEXT_DOMAIN ) . '</label><span class="d-inline-block oc-cap-container"><img id="oc_cap_img" alt="Please reload" src="' . get_stylesheet_directory_uri() . self::CP_IMG_PATH . $oc_token . '">'
            . '<input type="text" name="oc_captcha_val" class="oc-captcha-val" id="oc-captcha-val" placeholder="?" maxlength="3" required/></span>'
            . '<input type="hidden" name="oc_cpt" id="oc_cpt" value="' . $oc_token . '">'
            . '<input type="text" name="oc_csrf_token" value="" class="oc_csrf_token" id="oc_csrf_token">' .
            '<input type="hidden" value="" name="validate_nonce" id="validate_nonce">';

        wp_send_json( [
            self::OC_STATUS => 'success',
            'data'          => $fields
        ] );

    }

    /**
     * Function oc_get_captcha_string
     * Generate a token to be used to add value in captcha
     *
     * @param void
     *
     * @return string
     */
    public function oc_get_captcha_string( $echo = false ) {
        $num1  = rand( 0, 10 );
        $num2  = rand( 1, 10 );
        $token = self::OC_CAPTCHA_KEY . base64_encode( $num1 . '#' . $num2 );
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && $echo ) {
            wp_send_json( [
                'token' => $token,
                'image' => get_stylesheet_directory_uri() . self::CP_IMG_PATH . $token
            ] );
        }

        return $token;
    }

    /**
     * Function oc_validate_captcha
     * Check if incoming value of captcha is valid
     *
     * @param $value , string that user entered as captcha solution
     * @param $encrypted_val , string the token that was used to generate captcha
     *
     * @return string
     */
    public function oc_validate_captcha( $value, $encrypted_val ) {
        $decrypted_value = base64_decode( str_replace( self::OC_CAPTCHA_KEY, '', $encrypted_val ) );
        if ( ! $decrypted_value ) {
            return false;
        }
        $exploded = explode( '#', $decrypted_value );

        if ( count( $exploded ) < 2 ) {
            return false;
        }

        return ( intval( $exploded[0] ) + intval( $exploded[1] ) ) === intval( $value );
    }

    function contact_form_cb() {

        if ( ! ( isset( $_POST[ self::OC_FORM_DATA ] ) && count( $_POST[ self::OC_FORM_DATA ] ) ) ) {
            wp_send_json( [
                self::OC_STATUS => self::OC_ERROR,
                'text'          => __( self::OC_ERR_MSG, OC_TEXT_DOMAIN ),
            ] );
        }

        $this->oc_secure_form();

        $to           = filter_var( $_POST['recipient'], FILTER_SANITIZE_EMAIL );
        $subject      = sanitize_text_field( $_POST['subject'] );
        $message_body = '';
        foreach ( $_POST[ self::OC_FORM_DATA ] as $data ) {
            if ( in_array( $data['label'], [ 'oc_captcha_val', 'oc_cpt', 'oc_csrf_token', 'validate_nonce' ] ) ) {
                continue;
            }
            $message_body .= '<p><strong>' . $data['label'] . ': </strong> ' . $data['val'] . '</p>';
        }
        add_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );
        if ( wp_mail( $to, $subject, $message_body ) ) {
            $token_new = $this->oc_get_captcha_string();
            wp_send_json( [
                self::OC_STATUS => 'success',
                'token'         => $token_new,
                'image'         => get_stylesheet_directory_uri() . self::CP_IMG_PATH . $token_new,
                'text'          => __( 'Your message has been successfully sent.', OC_TEXT_DOMAIN ),
            ] );
        } else {
            wp_send_json( [
                self::OC_STATUS => self::OC_ERROR,
                'text'          => __( self::OC_ERR_MSG, OC_TEXT_DOMAIN ),
            ] );
        }
        remove_filter( 'wp_mail_content_type', [ $this, 'mail_content_type' ] );
        die();
    }

    function mail_content_type() {
        return 'text/html';
    }

    public function oc_dependency_array()
    {
        global $pagenow;

        if ( $pagenow === 'widgets.php' ) {
            return array( 'wp-edit-widgets',
                'wp-blocks',
                'wp-element', );
        }

        return array( 'wp-editor',
            'wp-blocks',
            'wp-element', );
    }

}