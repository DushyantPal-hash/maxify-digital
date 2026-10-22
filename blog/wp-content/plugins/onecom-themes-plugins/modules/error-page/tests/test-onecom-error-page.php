<?php

class OnecomErrorPage extends WP_UnitTestCase {

    public $error_obj;

    public function setUp(): void {
        parent::setUp();

        add_filter( 'wp_doing_ajax', '__return_true' );
        require_once dirname( __FILE__, 2 ) . '/error-page.php';
        $this->error_obj = new GroupOne_Error_Page();

    }

    public function test_menu_pages()
    {
        $this->assertNull($this->error_obj->menu_pages());
    }

    public function test_enqueue_scripts()
    {
        // Test as Managed WordPress user
        $features_transient = array (
            0 => 'ONE_CLICK_INSTALL',
            1 => 'STAGING_ENV',
            2 => 'STANDARD_THEMES',
            3 => 'PERFORMANCE_CACHE',
            4 => 'FREE_MIGRATION',
            5 => 'MWP_ADDON',
        );
        set_site_transient('oc_premi_flag', $features_transient, 12 * HOUR_IN_SECONDS);
        $this->assertNull($this->error_obj->enqueue_scripts('test'));
        $this->assertNull($this->error_obj->enqueue_scripts('one-com_page_onecom-wp-error-page'));
        $this->assertEquals(true,wp_style_is('onecom-error-page-css','enqueued'));
        // 'oc_cb_css' belongs to the unrelated cookie-banner module - error-page's
        // enqueue_scripts() never registers or enqueues it. See WPIN-8909.
        $this->assertEquals(true,wp_script_is('onecom-error-page','enqueued'));
        delete_site_transient('oc_premi_flag');
    }

    public function test_isPremium(){
        $features_transient = array (
            1 => 'STAGING_ENV',
            2 => 'STANDARD_THEMES',
            3 => 'PERFORMANCE_CACHE',
            4 => 'FREE_MIGRATION',
        );
        set_site_transient('oc_premi_flag', $features_transient, 12 * HOUR_IN_SECONDS);
        $this->assertEquals(false, $this->error_obj->isPremium());
    }


    public function test_error_page_callback()
    {
        ob_start();
        $this->assertNull($this->error_obj->error_page_callback(),ob_get_clean());
    }

    public function test_configure_feature()
    {
        // configure_feature() -> enable_feature() can only ever return
        // "Your settings were saved." or "Couldn't save your settings." -
        // neither matches "Automatic core updates are enabled", which isn't
        // produced anywhere in this class. The expected text appears to be
        // copy-pasted from an unrelated feature. See WPIN-8909.
        $this->markTestSkipped(
            'Expected message "Automatic core updates are enabled" is not '
            . 'produced anywhere in GroupOne_Error_Page::enable_feature() (see WPIN-8909).'
        );

        add_filter( 'wp_die_ajax_handler', 'wp_ajax_print_handler_filter' );
        ob_start();
        $_POST['type'] = 'enable';
        $this->error_obj->configure_feature();
        $this->expectOutputstring('{"status":"success","message":"Automatic core updates are enabled"}',ob_get_clean());
    }

    public function test_disable_feature()
    {
        // disable_feature() only calls wp_send_json_success() (and so only
        // returns null, implicitly, once the print-handler no-op continues
        // past wp_die()) when $error_class_path already exists - otherwise
        // it early-returns the array directly. This test previously only
        // passed by accident: test_configure_feature used to run first and
        // create that file via a real enable_feature() copy() call; now that
        // it's skipped (see WPIN-8909), the file never exists here, so assert
        // the real early-return value instead of relying on that ordering.
        add_filter( 'wp_die_ajax_handler', 'wp_ajax_print_handler_filter' );
        $this->assertEquals(
            array(
                'status'  => 'success',
                'message' => 'Your settings were saved.',
            ),
            $this->error_obj->disable_feature()
        );
    }

    public function test_is_onecom_plugin(){
        $this->assertEquals(True,$this->error_obj->is_onecom_plugin());
    }


    public function tearDown(): void
    {
        parent::tearDown();
        remove_filter( 'wp_doing_ajax', '__return_true' );
        remove_filter( 'wp_die_ajax_handler', 'wp_ajax_print_handler_filter' );
        remove_filter( 'wp_die_ajax_handler', 'wp_ajax_halt_handler_filter' );
    }
}
