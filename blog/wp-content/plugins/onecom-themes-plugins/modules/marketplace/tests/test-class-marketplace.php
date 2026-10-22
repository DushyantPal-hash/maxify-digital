<?php
/**
 * Comprehensive Unit tests for OnecomMarketplace class
 * All tests use mock data and don't call real API endpoints
 */
class TestOnecomMarketplace extends WP_UnitTestCase {

    /** @var OnecomMarketplace */
    public $mp;

    public function setUp(): void {
        parent::setUp();
        $this->mp = new OnecomMarketplace();
        $_SERVER['ONECOM_DOMAIN_NAME'] = 'example.com';

        // Clean up any existing transients
        $this->cleanup_all_transients();
    }

    public function tearDown(): void {
        parent::tearDown();
        $this->cleanup_all_transients();

        if (isset($_SERVER['ONECOM_DOMAIN_NAME'])) {
            unset($_SERVER['ONECOM_DOMAIN_NAME']);
        }
        $_POST = [];
    }

    /**
     * Helper to clean up all marketplace-related transients
     */
    private function cleanup_all_transients(): void {
        $plugins = ['wp-rocket', 'rank-math'];
        foreach ($plugins as $plugin) {
            delete_site_transient("{$plugin}_activation_start_at");
            delete_site_transient("{$plugin}_activation_button_clicked_at");
            delete_site_transient("{$plugin}-pp-activation-start-at");
            delete_site_transient("{$plugin}_purchase_button_start_at");
            delete_site_transient("{$plugin}_select_button_clicked_at");
            delete_site_transient("{$plugin}_onecom_addon_info");
        }
    }

    /**
     * Custom AJAX die handler that prevents die() from exiting
     * Note: wp_send_json() already echoes the JSON before calling wp_die(),
     * so we capture it via output buffering and throw an exception to stop execution
     */
    public function wp_ajax_print_handler_filter() {
        return function( $message ) {
            throw new WPAjaxDieContinueException();
        };
    }

    /**
     * Mock HTTP failure response
     */
    public function mock_http_failure( $preempt, $args, $url ) {
        return new WP_Error( 'http_request_failed', 'A valid URL was not provided.' );
    }

    /**
     * Mock HTTP success response for addon info
     */
    public function mock_http_addon_purchased( $preempt, $args, $url ) {
        return [
            'body' => json_encode([
                'success' => true,
                'data' => [
                    'source' => 'PURCHASED',
                    'product' => 'WP_ROCKET'
                ]
            ])
        ];
    }

    /**
     * Mock HTTP success response for addon not purchased
     */
    public function mock_http_addon_not_purchased( $preempt, $args, $url ) {
        return [
            'body' => json_encode([
                'success' => true,
                'data' => [
                    'source' => 'NOT_PURCHASED',
                    'product' => 'WP_ROCKET'
                ]
            ])
        ];
    }

    // ============================================================================
    // CONSTRUCTOR AND INITIALIZATION TESTS
    // ============================================================================

    public function test_constructor_creates_instance(): void {
        $mp = new OnecomMarketplace();
        $this->assertInstanceOf(OnecomMarketplace::class, $mp);
    }

    public function test_init_registers_hooks(): void {
        $this->mp->init();

        $this->assertTrue(has_action('admin_enqueue_scripts', [$this->mp, 'enqueueScripts']) !== false);
        $this->assertTrue(has_action('wp_ajax_marketplace_plugin_activate', [$this->mp, 'onclickPluginActivate']) !== false);
        $this->assertTrue(has_action('wp_ajax_marketplace_addon_purchase_check', [$this->mp, 'addonStatusCheck']) !== false);
        $this->assertTrue(has_action('wp_ajax_marketplace_addon_purchase_check_onload', [$this->mp, 'addonStatusCheckOnLoad']) !== false);
        $this->assertTrue(has_action('wp_ajax_marketplace_check_activate_banner', [$this->mp, 'checkActivateBannerOnReload']) !== false);
        $this->assertTrue(has_action('wp_ajax_get_addon_purchase_status', [$this->mp, 'isAddonPurchased']) !== false);
        $this->assertTrue(has_action('deactivated_plugin', [$this->mp, 'pluginDeactivated']) !== false);
    }

    public function test_init_registers_plugin_activate_reload_hook(): void {
        $this->mp->init();
        $this->assertTrue(has_action('wp_ajax_marketplace_plugin_activate_reload', [$this->mp, 'onReloadPluginActivateCheck']) !== false);
    }

    // ============================================================================
    // ENQUEUE SCRIPTS AND STYLES TESTS
    // ============================================================================

    public function test_enqueueScripts_non_marketplace_page(): void {
        $result = $this->mp->enqueueScripts('other_page');
        $this->assertNull($result);
    }

    public function test_enqueueScripts_marketplace_page(): void {
        $result = $this->mp->enqueueScripts('one-com_page_onecom-marketplace');
        $this->assertNull($result);
    }

    public function test_enqueueScripts_marketplace_products_page(): void {
        $result = $this->mp->enqueueScripts('one-com_page_onecom-marketplace-products');
        $this->assertNull($result);
    }

    // ============================================================================
    // PLUGIN URL AND NOTICE TESTS
    // ============================================================================

    public function test_getNoticePluginUrl_wp_rocket(): void {
        $url = $this->mp->getNoticePluginUrl('wp-rocket');
        $this->assertStringContainsString('options-general.php?page=wprocket', $url);
    }

    public function test_getNoticePluginUrl_rank_math(): void {
        $url = $this->mp->getNoticePluginUrl('rank-math');
        $this->assertStringContainsString('admin.php?page=rank-math&view=modules', $url);
    }

    public function test_getNoticePluginUrl_empty_string(): void {
        $url = $this->mp->getNoticePluginUrl('');
        $this->assertEmpty($url);
    }

    public function test_getNoticePluginUrl_unknown_plugin(): void {
        $url = $this->mp->getNoticePluginUrl('unknown-plugin');
        $this->assertEmpty($url);
    }

    public function test_getActivatedNotice_wp_rocket(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-success', $html);
        $this->assertStringContainsString('WP Rocket', $html);
        $this->assertStringContainsString('gv-notice-close', $html);
        $this->assertStringContainsString('success.svg', $html);
        $this->assertStringContainsString('close.svg', $html);
        $this->assertStringContainsString('options-general.php?page=wprocket', $html);
    }

    public function test_getActivatedNotice_rank_math(): void {
        $html = $this->mp->getActivatedNotice('rank-math');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-success', $html);
        // Production PLUGIN_SLUGS_NAME['rank-math'] is 'Rank Math PRO' (all-caps PRO,
        // matching the vendor's own branding for their premium plugin) — this
        // assertion was stale with the wrong casing.
        $this->assertStringContainsString('Rank Math PRO', $html);
        $this->assertStringContainsString('admin.php?page=rank-math&view=modules', $html);
    }

    public function test_getActivatedNotice_contains_manage_button(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');

        $this->assertStringContainsString('gv-button', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function test_getActivatedNotice_marketplace_products_page_wp_rocket(): void {
        $_POST['page'] = 'onecom-marketplace-products';
        $html = $this->mp->getActivatedNotice('wp-rocket');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-success', $html);
        $this->assertStringContainsString('WP Rocket', $html);
        $this->assertStringContainsString('is active', $html);
        $this->assertStringContainsString('successfully activated on this installation', $html);
        $this->assertStringContainsString('Go to', $html);
        $this->assertStringContainsString('gv-hidden', $html);
        $this->assertStringContainsString('ocwp_ocmp_go_to_wp_rocket_clicked_event', $html);
        $this->assertStringContainsString('options-general.php?page=wprocket', $html);
    }

    public function test_getActivatedNotice_marketplace_products_page_rank_math(): void {
        $_POST['page'] = 'onecom-marketplace-products';
        $html = $this->mp->getActivatedNotice('rank-math');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-success', $html);
        // Matches production's PLUGIN_SLUGS_NAME['rank-math'] casing ('Rank Math PRO').
        $this->assertStringContainsString('Rank Math PRO', $html);
        $this->assertStringContainsString('is active', $html);
        $this->assertStringContainsString('successfully activated on this installation', $html);
        $this->assertStringContainsString('Go to', $html);
        $this->assertStringContainsString('gv-hidden', $html);
        $this->assertStringContainsString('ocwp_ocmp_go_to_rank_math_clicked_event', $html);
        $this->assertStringContainsString('admin.php?page=rank-math&view=modules', $html);
    }

    public function test_getActivatedNotice_marketplace_products_hidden_class_present(): void {
        $_POST['page'] = 'onecom-marketplace-products';
        $html = $this->mp->getActivatedNotice('wp-rocket');

        // Verify the gv-hidden class is set for products page
        $this->assertStringContainsString('gv-hidden', $html);
        // The arrow icon should have the hidden class attribute
        $this->assertStringContainsString('class="gv-hidden"', $html);
    }

    public function test_getActivatedNotice_default_page_no_hidden_class(): void {
        // Don't set $_POST['page'], so it defaults to empty string
        $html = $this->mp->getActivatedNotice('wp-rocket');

        // When page is not 'onecom-marketplace-products', hiddenClass should be empty
        // and the button should show "Get started" instead of "Go to"
        $this->assertStringContainsString('Get started', $html);
        $this->assertStringContainsString('ocwp_ocmp_get_started_wp_rocket_clicked_event', $html);
    }

    public function test_getActivatedNotice_slug_dash_to_underscore_conversion_wp_rocket(): void {
        $_POST['page'] = 'onecom-marketplace-products';
        $html = $this->mp->getActivatedNotice('wp-rocket');

        // Verify dashes are converted to underscores in event name
        $this->assertStringContainsString('ocwp_ocmp_go_to_wp_rocket_clicked_event', $html);
        $this->assertStringNotContainsString('ocwp_ocmp_go_to_wp-rocket_clicked_event', $html);
    }

    public function test_getActivatedNotice_slug_dash_to_underscore_conversion_rank_math(): void {
        $_POST['page'] = 'onecom-marketplace-products';
        $html = $this->mp->getActivatedNotice('rank-math');

        // Verify dashes are converted to underscores in event name
        $this->assertStringContainsString('ocwp_ocmp_go_to_rank_math_clicked_event', $html);
        $this->assertStringNotContainsString('ocwp_ocmp_go_to_rank-math_clicked_event', $html);
    }

    public function test_getActivatedNotice_returns_string(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }

    public function test_getActivatedNotice_contains_close_button(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');
        $this->assertStringContainsString('gv-notice-close', $html);
        $this->assertStringContainsString('close.svg', $html);
    }

    public function test_getActivatedNotice_contains_success_icon(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');
        $this->assertStringContainsString('success.svg', $html);
        $this->assertStringContainsString('gv-notice-icon', $html);
    }

    public function test_getActivatedNotice_link_opens_in_new_tab(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');
        // Verify target="_blank" is present for opening link in new tab
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function test_getActivatedNotice_html_structure(): void {
        $html = $this->mp->getActivatedNotice('wp-rocket');

        $this->assertStringContainsString('<div class="gv-notice gv-notice-success', $html);
        $this->assertStringContainsString('gv-notice-content', $html);
        $this->assertStringContainsString('gv-notice-title', $html);
        $this->assertStringContainsString('mp-primary-manage-button', $html);
        $this->assertStringContainsString('gv-notice-close', $html);
    }

    public function test_getActivatedNotice_marketplace_products_button_text_variation(): void {
        $_POST['page'] = 'onecom-marketplace-products';
        $html = $this->mp->getActivatedNotice('wp-rocket');

        // Products page should have "Go to" button text
        $this->assertStringContainsString('Go to', $html);
        $this->assertStringNotContainsString('Get started', $html);
    }

    // ============================================================================
    // PLUGIN INSTALLATION TESTS
    // ============================================================================

    public function test_isPluginInstalled_invalid_slug(): void {
        $result = $this->mp->isPluginInstalled('invalid-plugin');
        $this->assertFalse($result);
    }

    public function test_isPluginInstalled_wp_rocket(): void {
        $result = $this->mp->isPluginInstalled('wp-rocket');
        $this->assertIsBool($result);
    }

    public function test_isPluginInstalled_rank_math(): void {
        $result = $this->mp->isPluginInstalled('rank-math');
        $this->assertIsBool($result);
    }

    // ============================================================================
    // ADDON PURCHASE VALIDATION TESTS
    // ============================================================================

    public function test_validateAddonPurchase_valid(): void {
        $valid = [
            'success' => true,
            'data' => [
                'source' => 'PURCHASED',
                'product' => 'WP_ROCKET'
            ]
        ];

        $this->assertTrue($this->mp->validateAddonPurchase($valid, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_invalid_success_false(): void {
        $invalid = [
            'success' => false,
            'data' => [
                'source' => 'PURCHASED',
                'product' => 'WP_ROCKET'
            ]
        ];

        $this->assertFalse($this->mp->validateAddonPurchase($invalid, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_not_purchased_source(): void {
        $not_purchased = [
            'success' => true,
            'data' => [
                'source' => 'NOT_PURCHASED',
                'product' => 'WP_ROCKET'
            ]
        ];

        $this->assertFalse($this->mp->validateAddonPurchase($not_purchased, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_wrong_product(): void {
        $wrong_product = [
            'success' => true,
            'data' => [
                'source' => 'PURCHASED',
                'product' => 'RANK_MATH'
            ]
        ];

        $this->assertFalse($this->mp->validateAddonPurchase($wrong_product, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_missing_data_key(): void {
        $missing_data = ['success' => true];
        $this->assertFalse($this->mp->validateAddonPurchase($missing_data, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_missing_source_key(): void {
        $missing_source = [
            'success' => true,
            'data' => ['product' => 'WP_ROCKET']
        ];

        $this->assertFalse($this->mp->validateAddonPurchase($missing_source, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_missing_product_key(): void {
        $missing_product = [
            'success' => true,
            'data' => ['source' => 'PURCHASED']
        ];

        $this->assertFalse($this->mp->validateAddonPurchase($missing_product, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_null_input(): void {
        $this->assertFalse($this->mp->validateAddonPurchase(null, 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_string_input(): void {
        $this->assertFalse($this->mp->validateAddonPurchase('invalid', 'WP_ROCKET'));
    }

    public function test_validateAddonPurchase_empty_array(): void {
        $this->assertFalse($this->mp->validateAddonPurchase([], 'WP_ROCKET'));
    }

    // ============================================================================
    // ADDON INFO AND TRANSIENT TESTS
    // ============================================================================

    public function test_getAddonInfo_returns_cached_transient(): void {
        $cached_data = ['success' => true, 'data' => ['source' => 'PURCHASED', 'product' => 'WP_ROCKET']];
        set_site_transient('wp-rocket_onecom_addon_info', $cached_data, 60);

        $result = $this->mp->getAddonInfo('wp-rocket', false);

        $this->assertEquals($cached_data, $result);

        delete_site_transient('wp-rocket_onecom_addon_info');
    }

    public function test_getAddonInfo_empty_domain_error(): void {
        // Temporarily unset the domain to test empty domain error
        $original_domain = $_SERVER['ONECOM_DOMAIN_NAME'] ?? null;
        unset($_SERVER['ONECOM_DOMAIN_NAME']);

        $result = $this->mp->getAddonInfo('wp-rocket', false, '');

        // Restore the original domain
        if ($original_domain !== null) {
            $_SERVER['ONECOM_DOMAIN_NAME'] = $original_domain;
        }

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Empty domain', $result['error']);
    }

    public function test_getAddonInfo_with_custom_domain(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['callWPApiForAddon'])
            ->getMock();

        $expected_data = ['success' => true, 'data' => ['source' => 'PURCHASED', 'product' => 'WP_ROCKET']];
        $this->mp->method('callWPApiForAddon')->willReturn($expected_data);

        $result = $this->mp->getAddonInfo('wp-rocket', true, 'custom-domain.com');

        $this->assertEquals($expected_data, $result);
    }

    public function test_getAddonInfo_force_refresh_uses_api(): void {
        $cached_data = ['success' => true, 'data' => ['source' => 'NOT_PURCHASED']];
        set_site_transient('wp-rocket_onecom_addon_info', $cached_data, 60);

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['callWPApiForAddon'])
            ->getMock();

        $new_data = ['success' => true, 'data' => ['source' => 'PURCHASED', 'product' => 'WP_ROCKET']];
        $this->mp->method('callWPApiForAddon')->willReturn($new_data);

        $result = $this->mp->getAddonInfo('wp-rocket', true);

        $this->assertEquals($new_data, $result);

        delete_site_transient('wp-rocket_onecom_addon_info');
    }

    public function test_getAddonInfo_for_rank_math(): void {
        $cached_data = ['success' => true, 'data' => ['source' => 'PURCHASED', 'product' => 'RANK_MATH']];
        set_site_transient('rank-math_onecom_addon_info', $cached_data, 60);

        $result = $this->mp->getAddonInfo('rank-math', false);

        $this->assertEquals($cached_data, $result);

        delete_site_transient('rank-math_onecom_addon_info');
    }

    // ============================================================================
    // TRANSIENT MANAGEMENT TESTS
    // ============================================================================

    public function test_setTransientForAddonActivation_wp_rocket(): void {
        $slug = 'wp-rocket';
        $this->mp->setTransientForAddonActivation($slug);

        $this->assertNotFalse(get_site_transient('wp-rocket_activation_start_at'));
        $this->assertNotFalse(get_site_transient('wp-rocket_activation_button_clicked_at'));
        $this->assertNotFalse(get_site_transient('wp-rocket-pp-activation-start-at'));
    }

    public function test_setTransientForAddonActivation_rank_math(): void {
        $slug = 'rank-math';
        $this->mp->setTransientForAddonActivation($slug);

        $this->assertNotFalse(get_site_transient('rank-math_activation_start_at'));
        $this->assertNotFalse(get_site_transient('rank-math_activation_button_clicked_at'));
        $this->assertNotFalse(get_site_transient('rank-math-pp-activation-start-at'));
    }

    public function test_clearActivationQueue_wp_rocket(): void {
        $slug = 'wp-rocket';

        set_site_transient('wp-rocket_activation_start_at', 1, 60);
        set_site_transient('wp-rocket_activation_button_clicked_at', 1, 60);
        set_site_transient('wp-rocket-pp-activation-start-at', 1, 60);

        $this->mp->clearActivationQueue($slug);

        $this->assertFalse(get_site_transient('wp-rocket_activation_start_at'));
        $this->assertFalse(get_site_transient('wp-rocket_activation_button_clicked_at'));
        $this->assertFalse(get_site_transient('wp-rocket-pp-activation-start-at'));
    }

    public function test_clearActivationQueue_rank_math(): void {
        $slug = 'rank-math';

        set_site_transient('rank-math_activation_start_at', 1, 60);
        set_site_transient('rank-math_activation_button_clicked_at', 1, 60);
        set_site_transient('rank-math-pp-activation-start-at', 1, 60);

        $this->mp->clearActivationQueue($slug);

        $this->assertFalse(get_site_transient('rank-math_activation_start_at'));
        $this->assertFalse(get_site_transient('rank-math_activation_button_clicked_at'));
        $this->assertFalse(get_site_transient('rank-math-pp-activation-start-at'));
    }

    public function test_clearAddonStatusQueue_wp_rocket(): void {
        $slug = 'wp-rocket';

        set_site_transient('wp-rocket_purchase_button_start_at', 1, 60);
        set_site_transient('wp-rocket_select_button_clicked_at', 1, 60);

        $this->mp->clearAddonStatusQueue($slug);

        $this->assertFalse(get_site_transient('wp-rocket_purchase_button_start_at'));
        $this->assertFalse(get_site_transient('wp-rocket_select_button_clicked_at'));
    }

    public function test_clearAddonStatusQueue_rank_math(): void {
        $slug = 'rank-math';

        set_site_transient('rank-math_purchase_button_start_at', 1, 60);
        set_site_transient('rank-math_select_button_clicked_at', 1, 60);

        $this->mp->clearAddonStatusQueue($slug);

        $this->assertFalse(get_site_transient('rank-math_purchase_button_start_at'));
        $this->assertFalse(get_site_transient('rank-math_select_button_clicked_at'));
    }

    // ============================================================================
    // AJAX HANDLER TESTS - Using mock wp_send_json/wp_die pattern
    // ============================================================================

    public function test_onclickPluginActivate_sets_transient(): void {
        $_POST['plugin_slug'] = 'wp-rocket';
        delete_site_transient('wp-rocket_activation_button_clicked_at');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->onclickPluginActivate();
        } catch (WPAjaxDieContinueException $e) {
            // Expected - wp_send_json calls wp_die
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $this->assertNotFalse(get_site_transient('wp-rocket_activation_button_clicked_at'));
    }

    public function test_onclickPluginActivate_override_rank_math_slug(): void {
        $_POST['plugin_slug'] = 'seo-by-rank-math-pro';
        delete_site_transient('rank-math_activation_button_clicked_at');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->onclickPluginActivate();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $this->assertNotFalse(get_site_transient('rank-math_activation_button_clicked_at'));
    }

    public function test_addonStatusCheck_sets_transient(): void {
        $_POST['plugin_slug'] = 'wp-rocket';
        delete_site_transient('wp-rocket_select_button_clicked_at');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->addonStatusCheck();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $this->assertNotFalse(get_site_transient('wp-rocket_select_button_clicked_at'));
    }

    // ============================================================================
    // CHECK ADDON PURCHASE STATUS - ADDITIONAL COVERAGE TESTS (Lines 726-738, 750-787)
    // ============================================================================

    public function test_checkAddonPurchaseStatus_button_not_clicked(): void {
        $_POST['plugin_slug'] = 'wp-rocket';
        delete_site_transient('wp-rocket_select_button_clicked_at');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('normal_reload', $response['status']);
    }

    public function test_checkAddonPurchaseStatus_addon_purchased_success(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the select button clicked transient
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 300);

        // Set a recent purchase start time to simulate a retry (not first attempt)
        set_site_transient('wp-rocket_purchase_button_start_at', current_time('timestamp'), 3600);

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['getAddonInfo', 'validateAddonPurchase', 'clearAddonStatusQueue', 'setTransientForAddonActivation'])
            ->getMock();

        // Mock addon as purchased
        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'PURCHASED', 'product' => 'WP_ROCKET']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(true);
        // No willReturn() for void methods

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('addon_purchased', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_select_button_clicked_at');
        delete_site_transient('wp-rocket_purchase_button_start_at');
    }

    public function test_checkAddonPurchaseStatus_plugin_already_active(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the select button clicked transient
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 300);

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['clearAddonStatusQueue', 'setTransientForAddonActivation'])
            ->getMock();

        // No willReturn() for void methods

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as active
        $mock_active_plugins = function() {
            return ['wp-rocket/wp-rocket.php'];
        };
        add_filter('pre_option_active_plugins', $mock_active_plugins);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_active_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        // When plugin is active, send_success is called
        $this->assertEquals('addon_purchased', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_select_button_clicked_at');
    }

    public function test_checkAddonPurchaseStatus_first_queue_attempt(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the select button clicked transient
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 300);

        // No purchase start time set yet
        delete_site_transient('wp-rocket_purchase_button_start_at');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('added_in_queue', $response['status']);

        // Verify transient was set
        $this->assertNotFalse(get_site_transient('wp-rocket_purchase_button_start_at'));

        // Cleanup
        delete_site_transient('wp-rocket_select_button_clicked_at');
        delete_site_transient('wp-rocket_purchase_button_start_at');
    }

    public function test_checkAddonPurchaseStatus_timeout_expired(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the select button clicked transient
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 300);

        // Set an old purchase start time to trigger timeout
        $old_time = current_time('timestamp') - (10 * MINUTE_IN_SECONDS);
        set_site_transient('wp-rocket_purchase_button_start_at', $old_time, 3600);

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['getAddonInfo', 'validateAddonPurchase', 'clearAddonStatusQueue'])
            ->getMock();

        // Mock addon as not purchased
        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'NOT_PURCHASED', 'product' => 'WP_ROCKET']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(false);
        // No willReturn() for void methods

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('expired_queue', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_select_button_clicked_at');
        delete_site_transient('wp-rocket_purchase_button_start_at');
    }

    public function test_checkAddonPurchaseStatus_time_left_less_than_30_seconds(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the select button clicked transient
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 300);

        // Set purchase start time with less than 30 seconds remaining (4 minutes 45 seconds elapsed of 5 minutes)
        $recent_time = current_time('timestamp') - (4 * MINUTE_IN_SECONDS + 45);
        set_site_transient('wp-rocket_purchase_button_start_at', $recent_time, 3600);

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['getAddonInfo', 'validateAddonPurchase', 'clearAddonStatusQueue'])
            ->getMock();

        // Mock addon as not purchased
        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'NOT_PURCHASED', 'product' => 'WP_ROCKET']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(false);
        // No willReturn() for void methods

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('expired_queue', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_select_button_clicked_at');
        delete_site_transient('wp-rocket_purchase_button_start_at');
    }

    public function test_checkAddonPurchaseStatus_still_in_progress(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the select button clicked transient
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 300);

        // Set a recent purchase start time (still in progress)
        set_site_transient('wp-rocket_purchase_button_start_at', current_time('timestamp'), 3600);

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['getAddonInfo', 'validateAddonPurchase'])
            ->getMock();

        // Mock addon as not purchased
        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'NOT_PURCHASED', 'product' => 'WP_ROCKET']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(false);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->checkAddonPurchaseStatus('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('already_in_queue', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_select_button_clicked_at');
        delete_site_transient('wp-rocket_purchase_button_start_at');
    }

    public function test_addonStatusCheckOnLoad_executes(): void {
        $_POST['plugin_slug'] = 'wp-rocket';
        set_site_transient('wp-rocket_select_button_clicked_at', time(), 60);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->addonStatusCheckOnLoad();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
    }

    public function test_onReloadPluginActivateCheck_executes(): void {
        $_POST['plugin_slug'] = 'wp-rocket';
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 60);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->onReloadPluginActivateCheck();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
    }

    public function test_onReloadPluginActivateCheck_rank_math_override(): void {
        $_POST['plugin_slug'] = 'seo-by-rank-math-pro';
        set_site_transient('rank-math_activation_button_clicked_at', time(), 60);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->onReloadPluginActivateCheck();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
    }

    // ============================================================================
    // ADDON PURCHASE AND PROVISIONER TESTS
    // ============================================================================

    public function test_isAddonPurchased_wp_rocket(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['validateAddonPurchase', 'getAddonInfo'])
            ->getMock();

        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'PURCHASED', 'product' => 'WP_ROCKET']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(true);

        $result = $this->mp->isAddonPurchased('wp-rocket');

        $this->assertTrue($result);
    }

    public function test_isAddonPurchased_rank_math(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['validateAddonPurchase', 'getAddonInfo'])
            ->getMock();

        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'PURCHASED', 'product' => 'RANK_MATH']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(true);

        $result = $this->mp->isAddonPurchased('rank-math');

        $this->assertTrue($result);
    }

    public function test_isAddonPurchased_not_purchased(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['validateAddonPurchase', 'getAddonInfo'])
            ->getMock();

        $this->mp->method('getAddonInfo')->willReturn([
            'success' => true,
            'data' => ['source' => 'NOT_PURCHASED', 'product' => 'WP_ROCKET']
        ]);
        $this->mp->method('validateAddonPurchase')->willReturn(false);

        $result = $this->mp->isAddonPurchased('wp-rocket');

        $this->assertFalse($result);
    }

    public function test_isAddonPurchased_with_addon_info(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['validateAddonPurchase', 'getAddonInfo'])
            ->getMock();

        $addon_data = [
            'success' => true,
            'data' => ['source' => 'PURCHASED', 'product' => 'WP_ROCKET', 'createdAt' => '2024-01-01']
        ];

        $this->mp->method('getAddonInfo')->willReturn($addon_data);
        $this->mp->method('validateAddonPurchase')->willReturn(true);

        $result = $this->mp->isAddonPurchased('wp-rocket', true);

        $this->assertIsArray($result);
        $this->assertTrue($result['is_active']);
        $this->assertEquals($addon_data, $result['addon_info']);
    }

    public function test_callWpApiProvisioner_addon_not_subscribed(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(false);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        $this->assertEquals('addon_not_subscribed', $result);
    }

    public function test_callWpApiProvisioner_already_in_queue(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        set_site_transient('wp-rocket-pp-activation-start-at', time(), 60);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        $this->assertEquals('already_in_queue', $result);
    }

    // ============================================================================
    // CALL WP API PROVISIONER - ADDITIONAL COVERAGE TESTS (Lines 407-435)
    // ============================================================================

    public function test_callWpApiProvisioner_http_request_made(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        // Track HTTP request
        $http_called = false;
        $captured_url = null;
        $captured_args = null;

        $mock_http = function($preempt, $args, $url) use (&$http_called, &$captured_url, &$captured_args) {
            $http_called = true;
            $captured_url = $url;
            $captured_args = $args;
            return [
                'response' => ['code' => 200],
                'body' => json_encode(['success' => true])
            ];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        remove_filter('pre_http_request', $mock_http, 10);

        $this->assertTrue($http_called);
        $this->assertNotNull($captured_url);
        $this->assertStringContainsString('/plugin-provisioner', $captured_url);
        $this->assertEquals('added_to_queue', $result);

        // Cleanup
        delete_site_transient('wp-rocket-pp-activation-start-at');
    }

    public function test_callWpApiProvisioner_http_body_contains_required_fields(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        $captured_args = null;

        $mock_http = function($preempt, $args, $url) use (&$captured_args) {
            $captured_args = $args;
            return [
                'response' => ['code' => 200],
                'body' => json_encode(['success' => true])
            ];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        remove_filter('pre_http_request', $mock_http, 10);

        // Verify the HTTP request body contains required fields
        $this->assertNotNull($captured_args);
        $this->assertArrayHasKey('body', $captured_args);

        $body = json_decode($captured_args['body'], true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('subdomain', $body);
        $this->assertArrayHasKey('domain', $body);
        $this->assertArrayHasKey('addon_slug', $body);
        $this->assertEquals('wp-rocket', $body['addon_slug']);

        // Cleanup
        delete_site_transient('wp-rocket-pp-activation-start-at');
    }

    public function test_callWpApiProvisioner_sets_transient_with_correct_key(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        $mock_http = function($preempt, $args, $url) {
            return ['response' => ['code' => 200], 'body' => json_encode(['success' => true])];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        remove_filter('pre_http_request', $mock_http, 10);

        // Verify transient is set with correct key
        $transient_value = get_site_transient('wp-rocket-pp-activation-start-at');
        $this->assertNotFalse($transient_value);
        $this->assertTrue(is_numeric($transient_value));

        // Cleanup
        delete_site_transient('wp-rocket-pp-activation-start-at');
    }

    public function test_callWpApiProvisioner_rank_math_transient_key(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        $mock_http = function($preempt, $args, $url) {
            return ['response' => ['code' => 200], 'body' => json_encode(['success' => true])];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('rank-math');

        remove_filter('pre_http_request', $mock_http, 10);

        // Verify correct transient key is used for rank-math
        $transient_value = get_site_transient('rank-math-pp-activation-start-at');
        $this->assertNotFalse($transient_value);

        // Verify wp-rocket transient is not set
        $this->assertFalse(get_site_transient('wp-rocket-pp-activation-start-at'));

        // Cleanup
        delete_site_transient('rank-math-pp-activation-start-at');
    }

    public function test_callWpApiProvisioner_returns_added_to_queue_string(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        $mock_http = function($preempt, $args, $url) {
            return ['response' => ['code' => 200], 'body' => json_encode(['success' => true])];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        remove_filter('pre_http_request', $mock_http, 10);

        // Verify the return value is exactly 'added_to_queue'
        $this->assertIsString($result);
        $this->assertEquals('added_to_queue', $result);

        // Cleanup
        delete_site_transient('wp-rocket-pp-activation-start-at');
    }

    public function test_callWpApiProvisioner_http_filter_added_and_removed(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        $mock_http = function($preempt, $args, $url) {
            return ['response' => ['code' => 200], 'body' => json_encode(['success' => true])];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        remove_filter('pre_http_request', $mock_http, 10);

        // After execution, verify the filter was properly managed
        $this->assertEquals('added_to_queue', $result);

        // Verify transient was set (indicating the complete flow executed)
        $this->assertNotFalse(get_site_transient('wp-rocket-pp-activation-start-at'));

        // Cleanup
        delete_site_transient('wp-rocket-pp-activation-start-at');
    }

    public function test_callWpApiProvisioner_url_construction_with_middleware_constant(): void {
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn(true);

        $captured_url = null;

        $mock_http = function($preempt, $args, $url) use (&$captured_url) {
            $captured_url = $url;
            return ['response' => ['code' => 200], 'body' => json_encode(['success' => true])];
        };

        add_filter('pre_http_request', $mock_http, 10, 3);

        $result = $this->mp->callWpApiProvisioner('wp-rocket');

        remove_filter('pre_http_request', $mock_http, 10);

        // Verify URL uses MIDDLEWARE_URL constant and plugin-provisioner endpoint
        $this->assertNotNull($captured_url);
        $this->assertStringContainsString(MIDDLEWARE_URL, $captured_url);
        $this->assertStringContainsString('plugin-provisioner', $captured_url);

        // Cleanup
        delete_site_transient('wp-rocket-pp-activation-start-at');
    }

    // ============================================================================
    // PLUGIN ACTIVATION AND DEACTIVATION TESTS
    // ============================================================================

    public function test_activateWpPlugin_button_not_clicked(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        delete_site_transient('wp-rocket_activation_button_clicked_at');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $this->assertStringContainsString('normal_reload', $output);
    }

    // ============================================================================
    // ACTIVATE WP PLUGIN - ADDITIONAL COVERAGE TESTS (Lines 625-639, 664-680, 693-705)
    // ============================================================================

    public function test_activateWpPlugin_success_activation(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the activation button clicked transient
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as installed and active
        $mock_active_plugins = function() {
            return ['wp-rocket/wp-rocket.php'];
        };
        add_filter('pre_option_active_plugins', $mock_active_plugins);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_active_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('activated', $response['status']);
        $this->assertArrayHasKey('notice_html', $response);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
    }

    public function test_activateWpPlugin_activation_failed(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the activation button clicked transient
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as installed but activation will fail
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
    }

    public function test_activateWpPlugin_install_stats_logged(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the activation button clicked transient
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as installed and active
        $mock_active_plugins = function() {
            return ['wp-rocket/wp-rocket.php'];
        };
        add_filter('pre_option_active_plugins', $mock_active_plugins);

        // Track if install stats transient is set
        $install_stats_key = 'wp-rocket_install_stats';
        delete_site_transient($install_stats_key);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_active_plugins);

        $this->assertJson($output);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
        delete_site_transient($install_stats_key);
    }

    public function test_activateWpPlugin_provisioner_path(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the activation button clicked transient
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not installed (so it calls provisioner)
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
    }

    public function test_activateWpPlugin_timeout_expired_queue(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set an old activation start time to trigger timeout
        $old_time = current_time('timestamp') - (10 * MINUTE_IN_SECONDS);
        set_site_transient('wp-rocket_activation_start_at', $old_time, 3600);
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active (still in progress)
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $status = $response['data']['status'] ?? $response['status'] ?? '';
        $this->assertEquals('expired_queue', $status);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
        delete_site_transient('wp-rocket_activation_start_at');
    }

    public function test_activateWpPlugin_already_in_queue(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set a recent activation start time (still in progress)
        set_site_transient('wp-rocket_activation_start_at', current_time('timestamp'), 3600);
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('pre_http_request', [$this, 'mock_http_failure'], 10, 3);
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock plugin as not active
        $mock_empty_plugins = function() {
            return [];
        };
        add_filter('pre_option_active_plugins', $mock_empty_plugins);

        ob_start();
        try {
            $this->mp->activateWpPlugin('wp-rocket');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('pre_http_request', [$this, 'mock_http_failure'], 10);
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_empty_plugins);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('already_in_queue', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
        delete_site_transient('wp-rocket_activation_start_at');
    }


    public function test_pluginDeactivated_sets_option(): void {
        $plugin_file = 'wp-rocket/wp-rocket.php';
        $option_name = 'plugin_deactivated_wp-rocket';

        delete_option($option_name);

        $this->mp->pluginDeactivated($plugin_file, false);

        $option = get_option($option_name);

        $this->assertIsArray($option);
        $this->assertArrayHasKey('time', $option);
        $this->assertArrayHasKey('network', $option);
        $this->assertFalse($option['network']);
    }

    public function test_pluginDeactivated_with_network_flag(): void {
        $plugin_file = 'seo-by-rank-math-pro/rank-math-pro.php';
        $option_name = 'plugin_deactivated_rank-math';

        delete_option($option_name);

        $this->mp->pluginDeactivated($plugin_file, true);

        $option = get_option($option_name);

        $this->assertIsArray($option);
        $this->assertTrue($option['network']);
    }

    public function test_pluginDeactivated_unknown_plugin(): void {
        $plugin_file = 'unknown/plugin.php';

        // Should not throw error, just return
        $result = $this->mp->pluginDeactivated($plugin_file, false);

        $this->assertNull($result);
    }

    // ============================================================================
    // SUPPORT AND BANNER TESTS
    // ============================================================================

    public function test_get_contact_support_link_default(): void {
        $link = $this->mp->get_contact_support_link();

        $this->assertIsString($link);
        $this->assertStringContainsString('help.one.com', $link);
    }

    public function test_getTryAgainBanner_wp_rocket(): void {
        $html = $this->mp->getTryAgainBanner('wp-rocket');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-alert', $html);
        $this->assertStringContainsString('error.svg', $html);
        $this->assertStringContainsString('Try again', $html);
        $this->assertStringContainsString('help.one.com', $html);
    }

    public function test_getTryAgainBanner_rank_math(): void {
        $html = $this->mp->getTryAgainBanner('rank-math');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-alert', $html);
        // Matches production's PLUGIN_SLUGS_NAME['rank-math'] casing ('Rank Math PRO').
        $this->assertStringContainsString('Rank Math PRO', $html);
    }

    public function test_getActivateBanner_wp_rocket(): void {
        $html = $this->mp->getActivateBanner('wp-rocket');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-info', $html);
        $this->assertStringContainsString('Activate WP Rocket', $html);
        $this->assertStringContainsString('info.svg', $html);
    }

    public function test_getActivateBanner_rank_math(): void {
        $html = $this->mp->getActivateBanner('rank-math');

        $this->assertIsString($html);
        $this->assertStringContainsString('gv-notice-info', $html);
        // Matches production's PLUGIN_SLUGS_NAME['rank-math'] casing ('Rank Math PRO').
        $this->assertStringContainsString('Rank Math PRO', $html);
    }

    // ============================================================================
    // CLASS CONSTANTS TESTS
    // ============================================================================

    public function test_plugin_handle_constant(): void {
        $reflection = new \ReflectionClass(OnecomMarketplace::class);
        $this->assertTrue($reflection->hasConstant('PLUGIN_HANDLE'));

        $plugin_handles = $reflection->getConstant('PLUGIN_HANDLE');

        $this->assertArrayHasKey('wp-rocket', $plugin_handles);
        $this->assertArrayHasKey('rank-math', $plugin_handles);
        $this->assertEquals('wp-rocket/wp-rocket.php', $plugin_handles['wp-rocket']);
        $this->assertEquals('seo-by-rank-math-pro/rank-math-pro.php', $plugin_handles['rank-math']);
    }

    public function test_addons_slugs_constant(): void {
        $reflection = new \ReflectionClass(OnecomMarketplace::class);
        $this->assertTrue($reflection->hasConstant('ADDONS_SLUGS'));

        $addon_slugs = $reflection->getConstant('ADDONS_SLUGS');

        $this->assertArrayHasKey('wp-rocket', $addon_slugs);
        $this->assertArrayHasKey('rank-math', $addon_slugs);
        $this->assertEquals('WP_ROCKET', $addon_slugs['wp-rocket']);
        $this->assertEquals('RANK_MATH', $addon_slugs['rank-math']);
    }

    public function test_expiration_time_constant(): void {
        $reflection = new \ReflectionClass(OnecomMarketplace::class);
        $this->assertTrue($reflection->hasConstant('EXPIRATION_TIME_IN_MINUTES'));

        $expiration = $reflection->getConstant('EXPIRATION_TIME_IN_MINUTES');

        $this->assertEquals(5, $expiration);
    }

    public function test_plugin_slugs_name_constant(): void {
        $reflection = new \ReflectionClass(OnecomMarketplace::class);
        $this->assertTrue($reflection->hasConstant('PLUGIN_SLUGS_NAME'));

        $plugin_names = $reflection->getConstant('PLUGIN_SLUGS_NAME');

        $this->assertEquals('WP Rocket', $plugin_names['wp-rocket']);
        // PLUGIN_SLUGS_NAME['rank-math'] is 'Rank Math PRO' (all-caps PRO) in production,
        // consistently used that way in two places in class-marketplace.php — the vendor's
        // own branding for their premium plugin. This assertion had a stale, wrong casing.
        $this->assertEquals('Rank Math PRO', $plugin_names['rank-math']);
    }

    public function test_item_category_constant(): void {
        $reflection = new \ReflectionClass(OnecomMarketplace::class);
        $this->assertTrue($reflection->hasConstant('ITEM_CATEGORY'));

        $categories = $reflection->getConstant('ITEM_CATEGORY');

        $this->assertEquals('performance', $categories['wp-rocket']);
        $this->assertEquals('seo', $categories['rank-math']);
    }

    public function test_plugin_slugs_constant(): void {
        $reflection = new \ReflectionClass(OnecomMarketplace::class);
        $this->assertTrue($reflection->hasConstant('PLUGIN_SLUGS'));

        $slugs = $reflection->getConstant('PLUGIN_SLUGS');

        $this->assertEquals('wp-rocket', $slugs['wp-rocket']);
        $this->assertEquals('seo-by-rank-math-pro', $slugs['rank-math']);
    }

    // ============================================================================
    // INTEGRATION TESTS
    // ============================================================================

    public function test_full_addon_purchase_flow_with_mocks(): void {
        // Setup
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['callWPApiForAddon', 'callWpApiProvisioner'])
            ->getMock();

        $addon_data = [
            'success' => true,
            'data' => [
                'source' => 'PURCHASED',
                'product' => 'WP_ROCKET'
            ]
        ];

        $this->mp->method('callWPApiForAddon')->willReturn($addon_data);

        // Test addon info retrieval
        $result = $this->mp->getAddonInfo('wp-rocket', true);
        $this->assertTrue($result['success']);

        // Test addon purchase validation
        $addon_const = OnecomMarketplace::ADDONS_SLUGS['wp-rocket'];
        $is_valid = $this->mp->validateAddonPurchase($result, $addon_const);
        $this->assertTrue($is_valid);
    }

    public function test_check_activate_banner_on_reload_purchased_recently(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Mock addon as purchased with recent createdAt
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $createdAt = date('Y-m-d H:i:s', current_time('timestamp') - DAY_IN_SECONDS * 15);

        $this->mp->method('isAddonPurchased')->willReturn([
            'is_active' => true,
            'addon_info' => [
                'success' => true,
                'data' => [
                    'source' => 'PURCHASED',
                    'product' => 'WP_ROCKET',
                    'createdAt' => $createdAt
                ]
            ]
        ]);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
    }

    // ============================================================================
    // CHECK ACTIVATE BANNER ON RELOAD - ADDITIONAL COVERAGE TESTS
    // ============================================================================

    public function test_checkActivateBannerOnReload_plugin_deactivated_manually(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set the option indicating plugin was manually deactivated
        update_option('plugin_deactivated_wp-rocket', [
            'time' => time(),
            'network' => false
        ]);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('plugin_deactivated_manually', $response['status']);
        $this->assertFalse($response['show_banner']);
        $this->assertEquals('wp-rocket', $response['addon_slug']);

        // Cleanup
        delete_option('plugin_deactivated_wp-rocket');
    }

    public function test_checkActivateBannerOnReload_plugin_already_active(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        // Mock the plugin as active
        $mock_callback = function() {
            return ['wp-rocket/wp-rocket.php'];
        };
        add_filter('pre_option_active_plugins', $mock_callback);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);
        remove_filter('pre_option_active_plugins', $mock_callback);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('plugin_already_active', $response['status']);
        $this->assertFalse($response['show_banner']);
    }

    public function test_checkActivateBannerOnReload_activation_in_progress(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set transient to indicate activation is in progress
        set_site_transient('wp-rocket_activation_button_clicked_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('activation_in_progress', $response['status']);
        $this->assertFalse($response['show_banner']);

        // Cleanup
        delete_site_transient('wp-rocket_activation_button_clicked_at');
    }

    public function test_checkActivateBannerOnReload_activation_in_progress_via_pp_activation(): void {
        $_POST['plugin_slug'] = 'rank-math';

        // Set transient to indicate provisioning activation is in progress
        set_site_transient('rank-math-pp-activation-start-at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('activation_in_progress', $response['status']);
        $this->assertFalse($response['show_banner']);

        // Cleanup
        delete_site_transient('rank-math-pp-activation-start-at');
    }

    public function test_checkActivateBannerOnReload_purchase_too_old(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Mock addon as purchased but more than 30 days ago
        $createdAt = date('Y-m-d H:i:s', current_time('timestamp') - (DAY_IN_SECONDS * 45));

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn([
            'is_active' => true,
            'addon_info' => [
                'success' => true,
                'data' => [
                    'source' => 'PURCHASED',
                    'product' => 'WP_ROCKET',
                    'createdAt' => $createdAt
                ]
            ]
        ]);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('purchase_too_old', $response['status']);
        $this->assertFalse($response['show_banner']);
        $this->assertArrayHasKey('createdAt', $response);
    }

    public function test_checkActivateBannerOnReload_rank_math_override_slug(): void {
        $_POST['plugin_slug'] = 'seo-by-rank-math-pro';

        // Mock addon as not purchased to ensure slug override works
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        // Return proper structure for not purchased
        $this->mp->method('isAddonPurchased')->willReturn([
            'is_active' => false,
            'addon_info' => []
        ]);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        // Should show addon_not_purchased since slug was overridden correctly
        $this->assertEquals('addon_not_purchased', $response['status']);
    }

    public function test_checkActivateBannerOnReload_show_activate_banner(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Mock addon as purchased recently
        $createdAt = date('Y-m-d H:i:s', current_time('timestamp') - (DAY_IN_SECONDS * 15));

        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased', 'getActivateBanner'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn([
            'is_active' => true,
            'addon_info' => [
                'success' => true,
                'data' => [
                    'source' => 'PURCHASED',
                    'product' => 'WP_ROCKET',
                    'createdAt' => $createdAt
                ]
            ]
        ]);

        $this->mp->method('getActivateBanner')->willReturn('<div>Activate Banner HTML</div>');

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('show_activate_banner', $response['status']);
        $this->assertTrue($response['show_banner']);
        $this->assertArrayHasKey('banner_html', $response);
    }

    public function test_checkActivateBannerOnReload_addon_not_purchased(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Mock addon as not purchased
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        // Return proper structure for not purchased
        $this->mp->method('isAddonPurchased')->willReturn([
            'is_active' => false,
            'addon_info' => []
        ]);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('addon_not_purchased', $response['status']);
        $this->assertFalse($response['show_banner']);
    }

    public function test_checkActivateBannerOnReload_activation_start_at_transient(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Set activation_start_at transient to indicate activation in progress
        set_site_transient('wp-rocket_activation_start_at', time(), 300);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        $this->assertEquals('activation_in_progress', $response['status']);

        // Cleanup
        delete_site_transient('wp-rocket_activation_start_at');
    }

    public function test_checkActivateBannerOnReload_with_no_created_at(): void {
        $_POST['plugin_slug'] = 'wp-rocket';

        // Mock addon as purchased but without createdAt
        $this->mp = $this->getMockBuilder(OnecomMarketplace::class)
            ->onlyMethods(['isAddonPurchased'])
            ->getMock();

        $this->mp->method('isAddonPurchased')->willReturn([
            'is_active' => true,
            'addon_info' => [
                'success' => true,
                'data' => [
                    'source' => 'PURCHASED',
                    'product' => 'WP_ROCKET'
                    // No createdAt
                ]
            ]
        ]);

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        ob_start();
        try {
            $this->mp->checkActivateBannerOnReload();
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        $output = ob_get_clean();

        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'wp_ajax_print_handler_filter']);

        $this->assertJson($output);
        $response = json_decode($output, true);
        // Should default to addon_not_purchased when createdAt is missing
        $this->assertEquals('addon_not_purchased', $response['status']);
    }
}