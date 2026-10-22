<?php
/**
 * one.com spam prevention for comment forms
 */

if (!function_exists('get_plugins')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!class_exists('OCSpamProtection')) {

    class OCSpamProtection
    {

        const OCCHECKBOX = 'oc_checkbox';
        const DESCRIPTION = 'description';
        const OC_CPT = 'oc_cpt';
        const OC_CAPTCHA = 'oc_captcha_val';
        const OC_CSRF_TOKEN = 'oc_csrf_token';
        const STATUS = 'status';
        const ERROR = 'error';
        const SUBMIT_FIELD = 'submit_field';
        const OC_CAPTCHA_KEY = '1ASD2A4D2AA4DA15A';
        public $text_domain;

        public function __construct()
        {
            $theme = wp_get_theme();


            $this->text_domain = esc_html($theme->get('TextDomain'));

            add_action('after_switch_theme', array($this, 'onecom_check_activated_plugins'));
            add_action( 'upgrader_process_complete',array($this, 'onecom_check_activated_plugins'),10, 2);
            add_action('updated_option', array($this, 'check_plugins'), 10, 3);
            add_action('customize_register', array($this, 'oc_add_captcha_control'));
            add_action('wp_head', array($this, 'oc_captcha_style'));

            add_filter('comment_form_defaults', array($this, 'oc_secure_comment_form'));
            if (!current_user_can('manage_options')) {
                add_action('pre_comment_on_post', array($this, 'oc_verify_captcha'));

                add_action('wp_footer', array($this, 'oc_captcha_script'));
                add_action('wp_ajax_oc_comment_captcha', [$this, 'oc_ajax_verify_captcha']);
                add_action('wp_ajax_nopriv_oc_comment_captcha', [$this, 'oc_ajax_verify_captcha']);
            }




        }

        /**
         * Check for antispam plugins on plugin activation/deactivation
         */

        public function check_plugins($option_name)
        {

            if ('active_plugins' == $option_name) {
                $this->onecom_check_activated_plugins();
            }


        }


        /**
         * Extract & store slug of the activated plugins in array.
         */


        public function onecom_get_activated_plugins()
        {

            $active = get_option('active_plugins');
            $plugin_slug = array();

            foreach ($active as $slug) {

                $plugin_slug[] = strtok($slug, '/');

            }
            return $plugin_slug;

        }


        /**
         * Match the activated plugins against the list of antispam plugins fetched from wp API
         */


        public function onecom_check_activated_plugins()
        {

            $activated_plugins = $this->onecom_get_activated_plugins();

            if (!is_array($activated_plugins) || empty($activated_plugins)) {


                set_theme_mod(self::OCCHECKBOX, true);


                return false;
            }

            $wpapi_plugins_list = get_site_transient('onecom_fetched_plugins');
            if (false === $wpapi_plugins_list) {
                $wpapi_plugins_list = $this->oc_fetch_wp_api_plugins();
            }
            $plugin_activated = false;
            if (is_array($wpapi_plugins_list)) {

                $wpapi_plugins_list = array_flip($wpapi_plugins_list);

                foreach ($activated_plugins as $value) {


                    if (isset($wpapi_plugins_list[$value])) {

                        $plugin_activated = true;
                        break;

                    }

                }


            }
            if ($plugin_activated === true) {
                set_theme_mod(self::OCCHECKBOX, false);
            } else {
                set_theme_mod(self::OCCHECKBOX, true);
            }
        }

        /**
         * Fetch Plugins from wp API endpoint
         */


        public function oc_fetch_wp_api_plugins()
        {
            $fetch_plugins_url = MIDDLEWARE_URL . '/antispam-plugins';

            $args = array(
                'timeout' => 5,
                'httpversion' => '1.0',
                'sslverify' => true,
            );

            $response = wp_remote_get($fetch_plugins_url, $args);
            $get_plugins = array();

            if (!is_wp_error($response && is_array($response))) {

                $body = wp_remote_retrieve_body($response);
                $body = json_decode($body);
                if (!empty($body) && $body->success) {
                    $get_plugins = $body->data;
                } else {

                    error_log(print_r($body, true));
                }

            } else {
                $errorMessage = '(' . wp_remote_retrieve_response_code($response) . ') ' . wp_remote_retrieve_response_message($response);

                error_log(print_r($errorMessage, true));


            }
            if (!empty($get_plugins)) {

                set_site_transient('onecom_fetched_plugins', $get_plugins, 10 * HOUR_IN_SECONDS);
                return $get_plugins;
            }

        }


        /**
         * Add captcha and other changes to the wp comment form
         */


        public function oc_secure_comment_form($submit_field)
        {
            if (true !== get_theme_mod(self::OCCHECKBOX)) {

                    remove_filter(current_filter(), __FUNCTION__);

                    return false;
                }
            if (!current_user_can('manage_options')) {
                $submit_field['class_form'] = 'comment-form ocsp-spam-protection';

                $submit_field[self::SUBMIT_FIELD] = '<div class= "ocsp-comment-captcha" >' . $this->oc_secure_fields() . '</div>' . $submit_field[self::SUBMIT_FIELD] . '<span class="ocsp-wait-msg ocsp-d-none ocsp-text-info">' . __("Please Wait", $this->text_domain) . '</span><span class="ocsp-cpt-msg ocsp-d-none ocsp-text-danger"></span>'
                    .wp_nonce_field( 'comment-submit', 'onecom-comment-check' );


            } else {

                $submit_field[self::SUBMIT_FIELD] = '<div class= "ocsp-comment-captcha" ><a class="small" target="_blank" href="' . admin_url('/customize.php?autofocus[section]=oc_spam_checkbox') . '">' . __('Captcha Settings', $this->text_domain) . '</a> </div>' . $submit_field[self::SUBMIT_FIELD];

            }
            return $submit_field;


        }

        /**
         * Captcha styles hooked to wp head
         */

        public function oc_captcha_style()
        {
            if (false !== get_theme_mod(self::OCCHECKBOX) && is_single() && comments_open()) {
                ?>
                <style type="text/css">
                    .ocsp-comment-captcha input.oc-captcha-val {
                        width: 50px !important;
                        height: 32px !important;
                        vertical-align: middle;
                        border-radius: 0 !important;
                        border: 0 !important;
                        font-size: 16px;
                        outline: none;
                        text-align: center;
                        border-left: 1px solid #ccc !important;
                        margin-left: 8px;

                    }

                    .ocsp-comment-captcha a.small {
                        font-size: 11px;
                        font-weight: 400;

                    }

                    .ocsp-d-block{
                        display: block!important;
                    }

                    .ocsp-d-none {
                        display: none !important;
                    }

                    .ocsp-d-inline-block{

                        display: inline-block !important;
                    }

                    .ocsp-text-info {
                        color: #17a2b8 !important;

                    }

                    .ocsp-text-danger {
                        color: #dc3545 !important;
                    }

                    .ocsp-comment-captcha #oc_cap_img {
                        border-radius: 0 !important;
                    }

                    .ocsp-comment-captcha .ocsp-cap-container {
                        border: 1px solid #BBBBBB !important;
                        background-color: white !important;
                    }

                    .ocsp-comment-captcha {
                        margin-bottom: 12px;
                        margin-top: 10px;
                    }
                </style>

                <?php
            }

        }

        /**
         * Jquery script taking care of ajax validation of captcha.
         */

        public function oc_captcha_script()
        {
            if (false !== get_theme_mod(self::OCCHECKBOX) && is_single() && comments_open()) {
                ?>
                <script>
                    (function ($) {
                        $(document).ready(function () {
                            $('.ocsp-spam-protection').removeAttr('novalidate');
                            $('.ocsp-spam-protection').on('submit', function (e) {

                                e.preventDefault();
                                var captchaField = $(this).find('.oc-captcha-val'),
                                    form = $(this),
                                    cptField = form.find('input[name="oc_cpt"]'),
                                    csrfField = form.find('.oc_csrf_token'),
                                    nonce = form.find('#onecom-comment-check'),
                                    ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>';


                                form.find('.ocsp-cpt-msg').addClass('ocsp-d-none');
                                form.find('.ocsp-wait-msg').removeClass('ocsp-d-none');
                                $.post(ajaxurl, {
                                    action: 'oc_comment_captcha',
                                    oc_cpt: cptField.val(),
                                    oc_csrf_token: csrfField.val(),
                                    onecom_comment_filter: nonce.val(),
                                    oc_captcha_val: captchaField.val(),
                                }, function (response) {
                                    if (response.status === 'success') {
                                        form.find('.ocsp-wait-msg').addClass('ocsp-d-none');
                                        form.find(':submit').attr('id', 'ocsp-submit');
                                        form.find(':submit').attr('name', 'ocsp-submit');
                                        form.unbind('submit').submit();

                                    } else {
                                        form.find('.ocsp-wait-msg').addClass('ocsp-d-none');
                                        form.find('.ocsp-cpt-msg').removeClass('ocsp-d-none').text(response.text);
                                    }

                                });

                            })


                        });

                    })(jQuery)


                </script>

                <?php
            }
        }


        /**
         * Ajax handler for verification of captcha
         */


        public function oc_ajax_verify_captcha()
        {
            /* Check Captcha */


            if (!isset($_POST[self::OC_CPT]) || !isset($_POST[self::OC_CAPTCHA]) || !$_POST[self::OC_CAPTCHA]
                || !$_POST[self::OC_CPT] || !$this->oc_validate_captcha($_POST[self::OC_CAPTCHA], $_POST[self::OC_CPT])
            ) {
                wp_send_json([
                    self::STATUS => self::ERROR,
                    'text' => __('Invalid answer, please try again.', $this->text_domain)
                ]);
            }


            /** Check Honey Pot field  & verify noonce*/
            if (!isset($_POST[self::OC_CSRF_TOKEN]) || $_POST[self::OC_CSRF_TOKEN] !== '' ||
                !isset($_POST['onecom_comment_filter']) || !$_POST['onecom_comment_filter'] ||
                !wp_verify_nonce($_POST['onecom_comment_filter'], 'comment-submit')) {
                wp_send_json([
                    self::STATUS => self::ERROR,
                    'text' => __('Some error occurred, please reload the page and try again.', $this->text_domain),
                ]);
            }

            if ($this->oc_validate_captcha($_POST[self::OC_CAPTCHA], $_POST[self::OC_CPT])) {
                wp_send_json([
                    self::STATUS => 'success',

                ]);

                wp_die();


            }

        }


        /**
         * Server side captcha validation
         */
        public function oc_verify_captcha()
        {

            // if our spam protection is disabled, bail out
            if (!get_theme_mod(self::OCCHECKBOX)){
                return false;
            }



            $recaptcha = $_POST['oc_captcha_val'];

            /** Check Honey Pot field & the nonce field */
            if (!isset($_POST[self::OC_CSRF_TOKEN]) || $_POST[self::OC_CSRF_TOKEN] !== '' ||
                !isset($_POST['onecom-comment-check']) || !$_POST['onecom-comment-check'] ||
                !wp_verify_nonce($_POST['onecom-comment-check'], 'comment-submit')) {

                wp_die(__(" <b>Something went wrong! Please try again!</b>", $this->text_domain) . "<p><a href='javascript:history.back()'>« Back</a></p>",403);

            }
            if (empty($recaptcha)) {
                wp_die(__(" <b>Something went wrong! Please try again!</b>",$this->text_domain)."<p><a href='javascript:history.back()'>« Back</a></p>",403);
            } else if (!$this->oc_validate_captcha($recaptcha, $_POST['oc_cpt'])) {
                wp_die(__("<b>Invalid answer, please try again.</b>", $this->text_domain). "<p><a href='javascript:history.back()'>« Back</a></p></p>",403);
            }
        }

        /**
         * Returns the captcha &  honeypot fields.
         */
        function oc_secure_fields()
        {
            
             if (is_plugin_active("onecom-vcache/vcaching.php")) {

            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', FALSE);
            header('Pragma: no-cache');
            }
            $oc_token = $this->oc_get_captcha_string();
            $fields = '<label class="ocsp-d-block">' . __('Type in the answer to the sum below:', $this->text_domain) . '</label><span class="ocsp-d-inline-block ocsp-cap-container"><img id="oc_cap_img" alt="Please reload" src="' . get_stylesheet_directory_uri() . '/inc/image.php?i=' . $oc_token . '">'
                . '<input type="text" name="oc_captcha_val" class="oc-captcha-val" id="oc-captcha-val" placeholder="?" maxlength="3" required/></span>'
                . '<input type="hidden" name="oc_cpt" id="oc_cpt" value="' . $oc_token . '">'
                . '<input type="text" name="oc_csrf_token" value="" class="oc_csrf_token" id="oc_csrf_token">';
            return $fields;
        }

        /**
         * Returns the captcha token
         */

        function oc_get_captcha_string($echo = false)
        {
            $num1 = random_int(0, 10);
            $num2 = random_int(1, 10);
            $token = self::OC_CAPTCHA_KEY . base64_encode($num1 . '#' . $num2);
            if (defined('DOING_AJAX') && DOING_AJAX && $echo) {
                wp_send_json([
                    'token' => $token,
                    'image' => get_stylesheet_directory_uri() . '/inc/image.php?i=' . $token
                ]);
                wp_die();
            }
            return $token;
        }

        /**
         * Validates the captcha value
         */

        function oc_validate_captcha($value, $encrypted_val)
        {
            $decrypted_value = base64_decode(str_replace(self::OC_CAPTCHA_KEY, '', $encrypted_val));
            if (!$decrypted_value) {
                return false;
            }
            $exploded = explode('#', $decrypted_value);

            if (count($exploded) < 2) {
                return false;
            }

            return (intval($exploded[0]) + intval($exploded[1])) === intval($value);
        }


        /**
         * Registers customizer section for captcha control
         */


        function oc_add_captcha_control($wp_customize)
        {

            $theme_name = wp_get_theme();

            //add section
            $wp_customize->add_section('oc_spam_checkbox', array(

                'title' => 'Comments captcha',
                'priority' => 200,
                self::DESCRIPTION => "This is a part of $theme_name theme",
            ));

            //add setting
            $wp_customize->add_setting(self::OCCHECKBOX, array(
                'default' => '0',
                'transport' => 'refresh'
            ));

            //add control
            $wp_customize->add_control('oc_spam_control', array(
                'label' => __('Enable captcha on comment form', $this->text_domain),
                self::DESCRIPTION => '</br>' . __('Please note that if you are using any spam prevention or captcha plugins for your comment forms then disable this.', $this->text_domain),
                'type' => 'checkbox', // this indicates the type of control
                'section' => 'oc_spam_checkbox',
                'settings' => self::OCCHECKBOX
            ));

        }


    }
}
$spam = new OCSpamProtection();




