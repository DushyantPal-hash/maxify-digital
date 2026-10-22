<?php
/**
 * Tests for marketplace.php
 */
class TestMarketplaceProcedural extends WP_UnitTestCase {

    public function test_marketplace_php_is_covered(): void {
        $marketplace_file = dirname(__DIR__) . '/marketplace.php';
        $this->assertFileExists($marketplace_file);

		require_once $marketplace_file;

        $this->assertTrue(defined('MARKETPLACE_PAGE_SLUG'));
    }

    public function test_constants_defined(): void {
        $this->assertTrue(defined('GROUPONE_WPR_BUY_URL'));
        $this->assertTrue(defined('GROUPONE_RM_PRO_BUY_URL'));
        $this->assertTrue(defined('MARKETPLACE_PAGE_SLUG'));
        $this->assertTrue(defined('MARKETPLACE_PRODUCTS_PAGE_SLUG'));
    }

    public function test_marketplace_page_slug_value(): void {
        $this->assertEquals('onecom-marketplace', MARKETPLACE_PAGE_SLUG);
    }

    public function test_marketplace_products_page_slug_value(): void {
        $this->assertEquals('onecom-marketplace-products', MARKETPLACE_PRODUCTS_PAGE_SLUG);
    }

    public function test_wprocket_buy_url_value(): void {
        $this->assertEquals(GROUPONE_WPR_BUY_URL, GROUPONE_WPR_BUY_URL); // This is always true but keeps the test if needed, better to check against expected format
        $this->assertStringContainsString('https://one.com/admin/wprocket-prepare-buy.do', GROUPONE_WPR_BUY_URL);
        $this->assertStringContainsString('domain=' . ($_SERVER['ONECOM_DOMAIN_NAME'] ?? ''), GROUPONE_WPR_BUY_URL);
    }

    public function test_rankmath_buy_url_value(): void {
        $this->assertStringContainsString('https://one.com/admin/rankmath-prepare-buy.do', GROUPONE_RM_PRO_BUY_URL);
        $this->assertStringContainsString('domain=' . ($_SERVER['ONECOM_DOMAIN_NAME'] ?? ''), GROUPONE_RM_PRO_BUY_URL);
    }

    public function test_mp_object_initialization(): void {
        $this->assertTrue(class_exists('OnecomMarketplace'));

        // Since $mp_object was initialized in _manually_load_plugin() scope in bootstrap.php,
        // it is not available as a global. We verify initialization by checking registered hooks.
        $this->assertTrue(has_action('admin_enqueue_scripts'));
        // We can check if any of the hooks registered by OnecomMarketplace::init() are present
        $this->assertNotFalse(has_action('wp_ajax_get_addon_purchase_status'));
    }
}
