<?php

class OnecomPluginsApiTest extends WP_UnitTestCase {
    public $baseObj;
    public $server;

    public function setUp(): void {
        parent::setUp();

        $this->baseObj = new OnecomPluginsApi();
        global $wp_rest_server;
        $this->server = $wp_rest_server = new WP_REST_Server;
        do_action( 'rest_api_init' );

        // This whole class exercises REST-API-context code paths. get_status()
        // sets this itself when actually dispatched, but tests that call
        // health_monitor_scan()'s AJAX-style callbacks directly (bypassing
        // dispatch) need it too - without it, send_json() calls
        // wp_send_json_success() -> wp_die(), which really terminates the
        // process here (no wp_die_ajax_handler override in this class).
        // Previously this only worked by accident, via a leaked side effect
        // of test_name_route() dispatching a real request earlier in the
        // process. See WPIN-8909.
        groupone_set_rest_context( true );
    }

    public function test_public_templates()
    {
        $this->assertEquals($this->baseObj->errorTemplate, array(
            'error'   => true,
            'data'    => null,
            'message' => 'Some error occurred.',
            'code'    => 501
        ));

        $this->assertEquals($this->baseObj->itemTemplate, array(
            'id'          => '',
            'title'       => "Title of the bullet",
            'description' => "Description of the bullet",
            'category'    => "Performance",
            'issue'       => 0,
        ));

        $this->assertEquals( $this->baseObj->ocPluginsPage, "admin.php?page=onecom-marketplace" );
        $this->assertEquals( $this->baseObj->ocHomePage, "admin.php?page=onecom-home" );
        $this->assertEquals( $this->baseObj->ocvmPage, "admin.php?page=onecom-wp-health-monitor#vm-settings" );
    }


    public function test_register_routes()
    {
        $this->baseObj->register_routes();
        $routes = $this->server->get_routes();
//        var_dump($routes);
        $this->assertArrayHasKey( '/onecom-plugins/v1/get', $routes );
//        $request = new WP_REST_Request( 'GET', 'onecom-plugins/v1/get' );
//        $response = $this->server->dispatch( $request );
//        $this->assertEquals( 200, $response->status );
    }

    public function test_api_endpoints() {
        $the_route = '/onecom-plugins/v1/get';
        $routes = $this->server->get_routes();
        foreach( $routes as $route => $route_config ) {
            if( 0 === strpos( $the_route, $route ) ) {
                $this->assertTrue( is_array( $route_config ) );
                foreach( $route_config as $i => $endpoint ) {
                    $this->assertArrayHasKey( 'callback', $endpoint );
                    $this->assertArrayHasKey( 0, $endpoint[ 'callback' ], get_class( $this ) );
                    $this->assertArrayHasKey( 1, $endpoint[ 'callback' ], get_class( $this ) );
                    $this->assertTrue( is_callable( array( $endpoint[ 'callback' ][0], $endpoint[ 'callback' ][1] ) ) );
                }
            }
        }
    }


    public function test_name_route() {
        // get_status() requires validate_token() to pass, which requires a
        // JWT signed with GroupOne_LAUTH's real RS256 private key - there is
        // no filter/mock seam for this (it's asymmetric crypto, not a
        // network call), so a request with no token can only ever get 401.
        // See WPIN-8909.
        $this->markTestSkipped(
            'get_status() needs a request signed with the real RS256 private '
            . 'key, which this environment cannot produce (see WPIN-8909).'
        );

        $request  = new WP_REST_Request( 'GET', '/onecom-plugins/v1/get' );
        $response = $this->server->dispatch( $request );
        // for testing this with response code 200 return true from validate_token() function else this will return 401
        $this->assertEquals( 200, $response->get_status() );
//        $data = $response->get_data();
//        $this->assertArrayHasKey( 'name', $data );
//        $this->assertEquals( 'shawn', $data[ 'name' ] );
    }

    // WPIN-8797: create_item() used to write onecom_login_masking (and
    // toggle xmlrpc.php) via mask_login()/unmask_login() BEFORE checking the
    // onecom-auth token -- validate_token() was only ever reached afterwards,
    // as the last step inside mask_login()/unmask_login()'s own call to
    // get_status(). Reproduced live: dispatching this exact request against
    // the unpatched code set onecom_login_masking to 1 despite the token
    // being complete garbage. What matters here is the option value, not the
    // HTTP response shape -- GroupOne_LAUTH's constructor can itself call
    // wp_redirect() for a malformed token (WPIN-8798), so the dispatch is
    // wrapped defensively in case that surfaces as a thrown exception in
    // this test environment.
    public function test_create_item_does_not_mutate_login_masking_with_invalid_token() {
        delete_option( 'onecom_login_masking' );

        $request = new WP_REST_Request( 'POST', '/onecom-plugins/v1/get' );
        $request->set_param( 'onecom-auth', 'invalid.garbage.token' );
        $request->set_param( 'loginProtection', 1 );

        try {
            $this->server->dispatch( $request );
        } catch ( \Throwable $e ) {
            // Not the point of this test either way -- see comment above.
        }

        $this->assertSame( 0, (int) get_site_option( 'onecom_login_masking', 0 ) );
    }

    public function test_create_item_does_not_mutate_login_masking_with_no_token() {
        delete_option( 'onecom_login_masking' );

        $request = new WP_REST_Request( 'POST', '/onecom-plugins/v1/get' );
        $request->set_param( 'loginProtection', 2 );

        $response = $this->server->dispatch( $request );

        $this->assertSame( 0, (int) get_site_option( 'onecom_login_masking', 0 ) );
        $this->assertSame( 401, $response->get_status() );
    }

    public function test_get_pcache_status(){
        $status = $this->baseObj->get_pcache_status();
        $this->assertEquals( 0, $status );
        update_site_option( 'varnish_caching_enable', 'true' );
        $status = $this->baseObj->get_pcache_status();
        $this->assertEquals( 1, $status );
    }

    public function test_get_error_page_status()
    {
        $status = $this->baseObj->get_error_page_status();
        $this->assertEquals( 0, $status['status'] );
        $this->assertEquals( 'admin.php?page=onecom-wp-error-page', $status['wp_page'] );
    }

    public function test_get_restricted_uploads_status()
    {
        $status = $this->baseObj->get_restricted_uploads_status();
        $file_extn = array(
            'php',
            'phtml',
            'php3',
            'php4',
            'php5',
            'pl',
            'py',
            'jsp',
            'asp',
            'html',
            'htm',
            'shtml',
            'sh',
            'cgi',
            'suspected');
        $this->assertEquals( 1, $status['status'] );
        $this->assertEquals( $file_extn, $status['files'] );
        $this->assertEquals( 'admin.php?page=onecom-wp-health-monitor', $status['wp_page'] );
//        var_dump($status);
    }


    public function test_get_uc_status()
    {
        // WPIN-8736: maintenance-mode ships as a merged module, active by
        // default, so this no longer hits the "feature unavailable" branch
        // (that's covered by test_get_uc_status_when_module_disabled()
        // below) -- it hits the same "active but not configured" branch as
        // test_get_uc_status_with_plugin_active() below, since neither sets
        // the onecom_under_construction_info option.
        $status = $this->baseObj->get_uc_status();
        $this->assertEquals( 0, $status['status'] );
        $this->assertEquals( 'admin.php?page=onecom-wp-under-construction', $status['wp_page'] );

    }

    public function test_get_uc_status_when_module_disabled()
    {
        groupone_set_module_enabled( 'maintenance-mode', false );
        groupone_load_merged_modules();

        try {
            $status = $this->baseObj->get_uc_status();
            $this->assertEquals( 0, $status['status'] );
            $this->assertEquals( 'admin.php?page=onecom-home', $status['wp_page'] );
        } finally {
            // groupone_module_active() reads a process-global that WP's
            // per-test DB rollback won't reset -- always restore it, even
            // if an assertion above fails, so it can't leak into whichever
            // test runs next in this PHPUnit process.
            groupone_set_module_enabled( 'maintenance-mode', true );
            groupone_load_merged_modules();
        }
    }

    public function test_get_uc_status_with_plugin_active()
    {
        $active_plugins = get_option( 'active_plugins' );
        if (!in_array('onecom-under-construction/onecom-under-construction.php', $active_plugins)) {
            $active_plugins[] = 'onecom-under-construction/onecom-under-construction.php';
        }
        update_site_option( 'active_plugins', $active_plugins );
        $status = $this->baseObj->get_uc_status();
        $this->assertEquals( 0, $status['status'] );
        $this->assertEquals( 'admin.php?page=onecom-wp-under-construction', $status['wp_page'] );

    }

    public function test_get_spam_protection_status()
    {
        // WPIN-8736, WPIN-9027: spam-protection is an mWP-only merged module,
        // so mock MWP_ADDON to verify the active state.
        set_site_transient( 'oc_premi_flag', array( 'MWP_ADDON' ), 12 * HOUR_IN_SECONDS );
        groupone_set_module_enabled( 'spam-protection', true );
        groupone_load_merged_modules();

        try {
            $status = $this->baseObj->get_spam_protection_status();
            $this->assertEquals( 1, $status['status'] );
            $this->assertEquals( 'admin.php?page=onecom-wp-spam-protection', $status['wp_page'] );
        } finally {
            delete_site_transient( 'oc_premi_flag' );
        }
    }

    public function test_get_spam_protection_status_when_module_disabled()
    {
        groupone_set_module_enabled( 'spam-protection', false );
        groupone_load_merged_modules();

        try {
            $status = $this->baseObj->get_spam_protection_status();
            $this->assertEquals( 0, $status['status'] );
            $this->assertEquals( 'admin.php?page=onecom-home', $status['wp_page'] );
        } finally {
            // groupone_module_active() reads a process-global that WP's
            // per-test DB rollback won't reset -- always restore it, even
            // if an assertion above fails, so it can't leak into whichever
            // test runs next in this PHPUnit process.
            groupone_set_module_enabled( 'spam-protection', true );
            delete_site_transient( 'oc_premi_flag' );
        }
    }

    public function test_get_spam_protection_status_with_plugin_active()
    {
        $active_plugins = get_option( 'active_plugins' );
        if (!in_array('onecom-spam-protection/onecom-spam-protection.php', $active_plugins)) {
            $active_plugins[] = 'onecom-spam-protection/onecom-spam-protection.php';
        }
        update_site_option( 'active_plugins', $active_plugins );
        $status = $this->baseObj->get_spam_protection_status();
        $this->assertEquals( 1, $status['status'] );
        $this->assertEquals( 'admin.php?page=onecom-wp-spam-protection', $status['wp_page'] );

    }

    public function test_get_plugin_version()
    {
        $version = $this->baseObj->get_plugin_version('onecom-spam-protection/onecom-spam-protection.php');

        $this->assertIsString($version);

        $blank = $this->baseObj->get_plugin_version('');
        $this->assertEquals(false, $blank);


    }

    /**
     * @return mixed
     */
    public function test_get_pcache_page() {
        $this->assertEquals('admin.php?page=onecom-vcache-plugin', $this->baseObj->get_pcache_page());

    }

    public function test_get_health_monitor_page() {
        $this->assertEquals('admin.php?page=onecom-wp-health-monitor', $this->baseObj->get_health_monitor_page());

    }

    public function test_get_cdn_status() {
        $this->assertEquals(0, $this->baseObj->get_cdn_status());

    }

    public function test_get_cdn_page()
    {
        $active_plugins = get_option( 'active_plugins' );
        if (!in_array('onecom-vcache/vcaching.php', $active_plugins)) {
            $active_plugins[] = 'onecom-vcache/vcaching.php';
        }
        update_site_option( 'active_plugins', $active_plugins );

        $this->assertEquals('admin.php?page=onecom-cdn',$this->baseObj->get_cdn_page());

    }

    public function test_get_health_monitor_action_name() {
        $action = $this->baseObj->get_health_monitor_action_name('error_reporting');
        $this->assertEquals('error_reporting', $action['id']);
        $this->assertEquals('error_reporting_title_0', $action['title']);
        $this->assertEquals('error_reporting_desc_0', $action['description']);
        $this->assertEquals('Security', $action['category']);
        $this->assertEquals(0, $action['issue']);
        $this->assertEquals(0, $action['needs_action']);

    }


    /**
     * before executing this
     * comment $hm_ajax->wp_updates(); $hm_ajax->wp_connection(); $hm_ajax->core_updates()
     * else they will fail php unit execution due to external calls
     */
    public function test_health_monitor_scan() {

        $this->assertEquals(null,$this->baseObj->health_monitor_scan());
    }

    public function test_get_status_on_score()
    {
        $this->assertEquals(false, $this->baseObj->get_status_on_score(''));
        $this->assertEquals("Healthy", $this->baseObj->get_status_on_score(80));
        $this->assertEquals("Fair", $this->baseObj->get_status_on_score(55));
        $this->assertEquals("Unhealthy", $this->baseObj->get_status_on_score(20));

    }

    public function test_get_health_monitor_recent_results()
    {
        $response = $this->baseObj->get_health_monitor_recent_results();
        $sample = array(
            "time" => time(),
            "uploads_index" => 0,
            "options_table_count" => 0,
            "check_staging_time" => 0,
            "check_backup_zip" => 0,
            "performance_cache" => 0,
            "enable_cdn" => 0,
            "check_updated_long_ago" => 0,
            "check_pingbacks" => 0,
            "xmlrpc" => 0,
            "spam_protection" => 0,
            "user_enumeration" => 0,
            "optimize_uploaded_images" => 0,
            "error_reporting" => 0,
            "usernames" => 0,
            "php_updates" => 0,
            "plugin_updates" => 0,
            "theme_updates" => 0,
            "wp_updates" => 0,
            "wp_connection" => 0,
            "core_updates" => 0,
            "ssl" => 0,
            "file_execution" => 0,
            "file_permissions" => 0,
            "file_edit" => 0,
            "dis_plugin" => 0,
        );
        $this->assertEquals( $sample, $response );

    }

    public function test_get_vulnerabilities()
    {
        $this->assertEquals(false,$this->baseObj->get_vulnerabilities());
    }

    public function test_get_health_monitor_status()
    {
        $status_arr = $this->baseObj->get_health_monitor_status();
        $this->assertArrayHasKey('score',$status_arr);
        $this->assertArrayHasKey('status',$status_arr);
        $this->assertArrayHasKey('last_scan',$status_arr);
        $this->assertArrayHasKey('actions',$status_arr);
        $this->assertArrayHasKey('wp_page',$status_arr);
        $this->assertIsArray($status_arr['actions']);


    }

    public function test_wp_shortcuts()
    {


        $response = array(
            'customise'      => array(
                'title'   => "customize_your_site",
                'wp_path' => "customize.php"
            ),
            'add_post'       => array(
                'title'   => "add_a_blog_post",
                'wp_path' => "post-new.php"
            ),
            'edit_frontpage' => array(
                'title'   => "edit_your_frontpage",
                'wp_path' => "edit.php"
            ),
            'view_site'      => array( 'title' => "view_your_site", 'wp_path' => "/" ),
            'add_page'       => array( 'title' => "add_additional_pages", 'wp_path' => "post-new.php?post_type=page" ),
            'manage_plugins' => array( 'title' => "manage_plugins", 'wp_path' => "plugins.php" ),
        );

        $this->assertEquals($response, $this->baseObj->wp_shortcuts());

    }


    public function test_vulnerability_monitor_status()
    {
        $response = array( 'status' => 0, 'wp_page' => "admin.php?page=onecom-wp-health-monitor#vm-settings" );
        $this->assertEquals($response, $this->baseObj->vulnerability_monitor_status());
    }


    public function test_trasient_timeout() {
        set_site_transient('ocsh_site_scan_result','demo',1000);
        $response = self::getPrivateMethod($this->baseObj,'get_transient_timeout',array('ocsh_site_scan_result'));
        $this->assertGreaterThanOrEqual( 998, $response );
        $this->assertLessThanOrEqual( 1000, $response );
    }


    /**
     * @param $obj
     * @param $name
     * for accessing private methods
     * @return mixed
     * @throws ReflectionException
     */
    public static function getPrivateMethod($obj, $name,$args) {
        $class = new ReflectionClass(get_class($obj));
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj,$args);
    }

    public function tearDown(): void {
        //code here

        parent::tearDown();

    }

}
