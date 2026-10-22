<?php
// Test landing page home

class OneHomeSectionsTest extends WP_UnitTestCase {
    protected function setUp(): void {
        parent::setUp();
        // Clear relevant transients before each test to ensure isolation
        delete_site_transient('oc_premi_flag');
        delete_site_transient('onecom_marketplace_catalog');
    }

    // test landing page home content - valid html dom (As a mWP)
    public function test_home_html() {

        // Test as Managed WordPress user
        $features_transient = array (
            'ONE_CLICK_INSTALL', 'STAGING_ENV', 'STANDARD_THEMES', 'PERFORMANCE_CACHE', 'FREE_MIGRATION', 'MWP_ADDON'
        );
        set_site_transient('oc_premi_flag', $features_transient, 12 * HOUR_IN_SECONDS);

		// Set up mock API response for featured products
		$mock_products_response = array(
			'success' => true,
			'data' => array(
				'catalog' => array(
					array(
						'slug' => 'test-plugin',
						'name' => 'Test Plugin',
						'featured' => true,
						'bannerUrl' => 'https://example.com/banner.jpg',
						'i18n' => array(
							'featuredTitle' => 'Test Plugin',
							'featuredContent' => 'Test Description'
						)
					)
				),
				'uiI18n' => array(
					'featuredCta' => 'Get started'
				)
			)
		);
		set_site_transient('onecom_marketplace_catalog', $mock_products_response, 15 * MINUTE_IN_SECONDS);

		// Mock HTTP request to prevent actual API calls
		add_filter('pre_http_request', function($preempt, $args, $url) use ($mock_products_response) {
			// Return mock response for marketplace API
			if (strpos($url, '/marketplace/products/catalog') !== false) {
				return [
					'headers' => [],
					'body' => json_encode($mock_products_response),
					'response' => ['code' => 200],
					'cookies' => [],
					'filename' => null
				];
			}
			return $preempt;
		}, 10, 3);

        /*
         * WPIN-8909: what stood here was the tail of a SimpleXMLElement-based
         * HTML validation that no longer exists - the render step that produced
         * $xml had been removed, leaving this block reading an undefined
         * variable, and a remove_all_filters() call spliced into the middle of
         * an elseif branch. The test errored with "Undefined variable $xml"
         * before asserting anything.
         *
         * Rewritten to match its working sibling test_home_html_non_mwp() just
         * below: render the template and assert on the output. The mWP heading
         * is the cleanest discriminator - home.php line 8 picks 'Managed
         * WordPress' vs 'Basic WordPress' straight off ismWP(). Deliberately not
         * asserting on 'WP Rocket': that string is in the template ungated, so
         * it says nothing about which branch ran.
         */
        ob_start();
        include GROUPONE_WP_PATH . 'modules/home/templates/home.php';
        $home_html = ob_get_clean();

        remove_all_filters('pre_http_request');

        $this->assertNotEmpty($home_html, 'The home template rendered nothing');

        /*
         * Assert on a tracking class rather than on visible copy. The first
         * version of this checked for the 'Managed WordPress' heading, which
         * failed: home.php:8 does read __( 'Managed WordPress', 'onecom-wp' ),
         * but what actually renders is "Managed WP" - the string goes through
         * translation, so it is not a stable thing for a test to pin.
         * ocwp_ocp_home_special_mwp_support_link_clicked_event comes from
         * get_articles_mwp() (oc-home-sections.php:91) and has no counterpart in
         * get_articles_basic(), so it is a structural marker of the mWP branch.
         */
        $this->assertStringContainsString(
            'ocwp_ocp_home_special_mwp_support_link_clicked_event',
            $home_html,
            'The mWP-only support article should render for a Managed WordPress user'
        );
    }

	// test landing page home content - valid html dom (As a non-mWP)
	public function test_home_html_non_mwp() {

		// Test as Non-Managed WordPress user
		$features_transient = array('ONE_CLICK_INSTALL');
		set_site_transient('oc_premi_flag', $features_transient, 12 * HOUR_IN_SECONDS);

		ob_start();
		// WPIN-8909: include, not include_once. test_home_html() above renders the
		// same template, and PHPUnit runs both in one process - so include_once
		// here silently rendered nothing at all once that test started including
		// it, and the assertion below ran against an empty string. A template is
		// meant to be renderable more than once per request.
		include GROUPONE_WP_PATH . 'modules/home/templates/home.php';
		$welcome_modal_html = ob_get_contents();
		ob_end_clean();

		// Validate some text which should only come for non-mWP
		$this->assertStringContainsString('WP Rocket', $welcome_modal_html);

	}

	// Test get_cards returns expected array structure
	public function test_get_cards_returns_expected_structure() {
		$obj = new OneHomeSections();
		$cards = $obj->get_cards();
		$this->assertIsArray($cards);
		$this->assertNotEmpty($cards);
		foreach ($cards as $card) {
			$this->assertArrayHasKey('title', $card);
		}
	}

	// Test get_articles_basic returns expected array structure
	public function test_get_articles_basic_returns_expected_structure() {
		// Set up mock API response for featured products
		$mock_products_response = array(
			'success' => true,
			'data' => array(
				'catalog' => array(
					array(
						'slug' => 'wp-rocket',
						'name' => 'WP Rocket',
						'featured' => true,
						'bannerUrl' => 'https://example.com/banner.jpg',
						'i18n' => array(
							'featuredTitle' => 'WP Rocket',
							'featuredContent' => 'Test Description'
						)
					)
				),
				'uiI18n' => array(
					'featuredCta' => 'Get started'
				)
			)
		);

		// Mock HTTP request to prevent actual API calls
		add_filter('pre_http_request', function($preempt, $args, $url) use ($mock_products_response) {
			// Return mock response for marketplace API
			if (strpos($url, '/marketplace/products/catalog') !== false) {
				return [
					'headers' => [],
					'body' => json_encode($mock_products_response),
					'response' => ['code' => 200],
					'cookies' => [],
					'filename' => null
				];
			}
			return $preempt;
		}, 10, 3);

		$obj = new OneHomeSections();
		$articles = $obj->get_articles_basic();
		$this->assertIsArray($articles);
		$this->assertNotEmpty($articles);
		foreach ($articles as $article) {
			$this->assertArrayHasKey('title', $article);
		}

		// Remove the HTTP filter
		remove_all_filters('pre_http_request');
	}

	// Test get_cp_url returns a valid URL
	public function test_get_cp_url_returns_url() {
		$obj = new OneHomeSections();
		$_SERVER['HTTP_X_GROUPONE_HOST'] = 'example.com';
		$url = $obj->get_cp_url();
		$this->assertStringStartsWith('https://', $url);
		unset($_SERVER['HTTP_X_GROUPONE_HOST']);
	}

	// WPIN-8936: get_performance_plugin_url() must key off whether the merged
	// Performance Cache module is actually running (groupone_module_active()),
	// not the standalone plugin file — that file is never in active_plugins
	// once the plugin merge (WPIN-8736) has happened, which previously made
	// this always return '' regardless of the module's on/off state.
	public function test_get_performance_plugin_url_returns_expected_url() {
		global $onecom_active_modules;
		$obj = new OneHomeSections();

		$onecom_active_modules['performance-cache'] = true;
		$url = $obj->get_performance_plugin_url();
		$this->assertStringContainsString('onecom-vcache-plugin', $url);

		$onecom_active_modules['performance-cache'] = false;
		$url = $obj->get_performance_plugin_url();
		$this->assertEmpty($url);

		unset($onecom_active_modules['performance-cache']);
	}

	// WPIN-8936: the Performance tile carries a module_slug so the template
	// knows which module toggle row to point a disabled tile's CTA at.
	public function test_get_cards_performance_tile_has_module_slug() {
		$obj = new OneHomeSections();
		$cards = $obj->get_cards();
		$performance_card = null;
		foreach ($cards as $card) {
			if ('Performance' === $card['title']) {
				$performance_card = $card;
			}
		}
		$this->assertNotNull($performance_card);
		$this->assertSame('performance-cache', $performance_card['module_slug']);
	}

	// Test get_admin_env_info returns expected keys
	public function test_get_admin_env_info_returns_expected_keys() {
		$obj = new OneHomeSections();
		$env = $obj->get_admin_env_info();
		$this->assertArrayHasKey('locale', $env);
		$this->assertArrayHasKey('user_locale', $env);
		$this->assertArrayHasKey('wp_version', $env);
		$this->assertArrayHasKey('php_version', $env);
	}

	// Test reset_transient_for_featured_list clears transient
	public function test_reset_transient_for_featured_list_clears_transient() {
		$obj = new OneHomeSections();

		set_site_transient('onecom_marketplace_catalog', ['test' => 'data'], 15 * MINUTE_IN_SECONDS);
		$this->assertNotFalse(get_site_transient('onecom_marketplace_catalog'));

		$obj->reset_transient_for_featured_list();
		$this->assertFalse(get_site_transient('onecom_marketplace_catalog'));
	}

	// Test is_plugin_active_by_slug returns true/false
	public function test_is_plugin_active_by_slug() {
        // Simulate plugin activation via WordPress option

        $obj = new OneHomeSections();
		update_option('active_plugins', ['rank-math/rank-math.php']);
		add_filter('active_plugins', function($plugins) {
			return ['rank-math/rank-math.php'];
		});
        $this->assertTrue($obj->is_plugin_active_by_slug('rank-math'));
		remove_filter('active_plugins', '');
        update_option('active_plugins', []);
		add_filter('active_plugins', function($plugins) { return [];});
        $this->assertFalse($obj->is_plugin_active_by_slug('not-installed'));
		remove_filter('active_plugins', '');
    }

	// Test get_recommended_products_list returns HTML for products
	public function test_get_recommended_products_list_returns_html() {
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_featured_products'])
			->getMock();
		$obj->method('get_featured_products')->willReturn([
			'catalog' => [
				[
					'bannerUrl' => 'https://example.com/icon.png',
					'i18n' => [
						'featuredTitle' => 'Test Plugin',
						'featuredContent' => 'Test Description'
					],
					'slug' => 'test-plugin'
				]
			],
			'uiI18n' => [
				'featuredCta' => 'Get started'
			]
		]);
		$html = $obj->get_recommended_products_list();
		$this->assertStringContainsString('gv-card', $html);
	}

	/**
	 * Test oc_close_welcome_modal sets user meta
	 */
	/*public function test_oc_close_welcome_modal_sets_user_meta() {
		// Setup: create a user and set as current
		$user_id = $this->factory->user->create(['role' => 'administrator']);
		wp_set_current_user($user_id);
		delete_user_meta($user_id, 'oc-welcome-modal-closed');
		set_site_transient('oc_premi_flag', ['ONE_CLICK_INSTALL'], 12 * HOUR_IN_SECONDS);

		// Intercept wp_die to catch wp_send_json_success output
		add_filter('wp_die_ajax_handler', function() {
			return function($message) { echo $message; };
		});

		// Capture output
		ob_start();
		try {
			$obj = new OneHomeSections();
			$obj->oc_close_welcome_modal();
		} catch (Exception $e) {
			// wp_send_json_success calls wp_die internally
		}
		ob_get_clean();

		// Assert user meta is set
		$this->assertEquals('1', get_user_meta($user_id, 'oc-welcome-modal-closed', true));

		// Clean up
		delete_user_meta($user_id, 'oc-welcome-modal-closed');
		wp_set_current_user(0);
	}*/

	/**
	 * Test fetch_products_from_api uses transient and clears it on force
	 */
	public function test_fetch_products_from_api_uses_and_clears_transient() {
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_admin_env_info'])
			->getMock();
		$obj->method('get_admin_env_info')->willReturn([
			'locale' => 'en_US',
			'user_locale' => 'en_US',
			'wp_version' => '6.0',
			'php_version' => '8.0',
		]);
		// Set mock transient with proper structure.
		// WPIN-8909: fetch_products_from_api()'s cache-hit check requires
		// isset($cached['data']['catalog']) - a flat 'data' array (as this
		// fixture had) always misses the cache and falls through to a real
		// network call, which fails in CI.
		$mock_data = [
			'success' => true,
			'data' => ['catalog' => ['mocked' => true]],
			'error' => null
		];
		set_site_transient('onecom_marketplace_catalog', $mock_data, 15 * MINUTE_IN_SECONDS);

		// Should use cached value
		$result = $obj->fetch_products_from_api(false, '');
		$this->assertEquals($mock_data, $result);

		// Should clear and not use cached value if force=true
		delete_site_transient('onecom_marketplace_catalog');
		set_site_transient('onecom_marketplace_catalog', $mock_data, 15 * MINUTE_IN_SECONDS);

		// When force is true, transient should be ignored - result will be different since API call is mocked
		$result_forced = $obj->fetch_products_from_api();
		// Since API call will fail in test environment, we just verify transient was not used
		$this->assertIsArray($result_forced);

		// Clean up
		delete_site_transient('onecom_marketplace_catalog');
	}

	/**
	 * Test fetch_products_from_api returns cached data when transient exists
	 */
	public function test_fetch_products_from_api_returns_cached_data() {
		// Arrange: Mock the OneHomeSections class
		$obj = new OneHomeSections();

		// WPIN-8909: fetch_products_from_api()'s cache-hit check requires
		// isset($cached['data']['catalog']) - nest the fixture accordingly,
		// or this always misses the cache and falls through to a real
		// network call, which fails in CI.
		$cached_data = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'test-plugin',
						'name' => 'Test Plugin',
						'featured' => true,
						'iconUrl' => 'https://example.com/icon.png',
						'textKeys' => ['description' => 'Test description']
					]
				]
			],
			'error' => null
		];

		// Set transient
		set_site_transient('onecom_marketplace_catalog', $cached_data, 15 * MINUTE_IN_SECONDS);

		// Act: Call the method without forcing refresh
		$result = $obj->fetch_products_from_api(false);

		// Assert: Should return cached data
		$this->assertEquals($cached_data, $result);
		$this->assertTrue($result['success']);
		$this->assertArrayHasKey('data', $result);

		// Cleanup
		delete_site_transient('onecom_marketplace_catalog');
	}

	/**
	 * Test fetch_products_from_api bypasses cache when force is true
	 */
	public function test_fetch_products_from_api_force_bypasses_cache() {
		// Arrange
		$obj = new OneHomeSections();

		$cached_data = [
			'success' => true,
			'data' => ['cached' => 'data'],
			'error' => null
		];

		set_site_transient('onecom_marketplace_catalog', $cached_data, 15 * MINUTE_IN_SECONDS);

		// Mock wp_remote_get to return fresh data.
		// WPIN-8909: fetch_products_from_api()'s success path also requires
		// isset($response['data']['catalog']) before it treats the response
		// as usable (and returns it / caches it) - a flat 'data' array falls
		// through to the error shape instead.
		$mock_response_body = json_encode([
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'fresh-plugin',
						'name' => 'Fresh Plugin',
						'featured' => true
					]
				]
			],
			'error' => null
		]);

		add_filter('pre_http_request', function($preempt, $args, $url) use ($mock_response_body) {
			return [
				'headers' => [],
				'body' => $mock_response_body,
				'response' => ['code' => 200],
				'cookies' => [],
				'filename' => null
			];
		}, 10, 3);

		// Act: Call with force = true
		$result = $obj->fetch_products_from_api(true);

		// Assert: Should not return cached data
		$this->assertNotEquals($cached_data, $result);
		$this->assertTrue($result['success']);

		// Cleanup
		remove_all_filters('pre_http_request');
		delete_site_transient('onecom_marketplace_catalog');
	}

	/**
	 * Test fetch_products_from_api handles API error gracefully
	 */
	public function test_fetch_products_from_api_handles_api_error() {
		// Arrange
		$obj = new OneHomeSections();

		// Ensure no cached data
		delete_site_transient('onecom_marketplace_catalog');

		// Mock wp_remote_get to return WP_Error
		add_filter('pre_http_request', function($preempt, $args, $url) {
			return new WP_Error('http_request_failed', 'Connection timeout');
		}, 10, 3);

		// Act
		$result = $obj->fetch_products_from_api(true);

		// Assert: Should return error response structure
		$this->assertIsArray($result);
		$this->assertArrayHasKey('success', $result);
		$this->assertFalse($result['success']);
		$this->assertArrayHasKey('error', $result);
		$this->assertArrayHasKey('data', $result);
		$this->assertEmpty($result['data']);

		// Cleanup
		remove_all_filters('pre_http_request');
	}

	/**
	 * Test fetch_products_from_api handles unsuccessful API response
	 */
	public function test_fetch_products_from_api_handles_unsuccessful_response() {
		// Arrange
		$obj = new OneHomeSections();
		delete_site_transient('onecom_marketplace_catalog');

		// Mock wp_remote_get to return unsuccessful response
		$mock_response_body = json_encode([
			'success' => false,
			'data' => [],
			'error' => 'API Error'
		]);

		add_filter('pre_http_request', function($preempt, $args, $url) use ($mock_response_body) {
			return [
				'headers' => [],
				'body' => $mock_response_body,
				'response' => ['code' => 500],
				'cookies' => [],
				'filename' => null
			];
		}, 10, 3);

		// Act
		$result = $obj->fetch_products_from_api(true);

		// Assert
		$this->assertIsArray($result);
		$this->assertFalse($result['success']);
		$this->assertEmpty($result['data']);

		// Cleanup
		remove_all_filters('pre_http_request');
	}

	/**
	 * Test fetch_products_from_api stores successful response in transient
	 */
	public function test_fetch_products_from_api_stores_in_transient() {
		// Arrange
		$obj = new OneHomeSections();
		delete_site_transient('onecom_marketplace_catalog');

		// WPIN-8909: see test_fetch_products_from_api_force_bypasses_cache -
		// the success path requires data.catalog to treat this as usable.
		$api_data = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'new-plugin',
						'name' => 'New Plugin',
						'featured' => true
					]
				]
			],
			'error' => null
		];

		// Mock successful API response
		add_filter('pre_http_request', function($preempt, $args, $url) use ($api_data) {
			return [
				'headers' => [],
				'body' => json_encode($api_data),
				'response' => ['code' => 200],
				'cookies' => [],
				'filename' => null
			];
		}, 10, 3);

		// Act
		$result = $obj->fetch_products_from_api(true);

		// Assert: Verify transient was set
		$cached = get_site_transient('onecom_marketplace_catalog');
		$this->assertNotFalse($cached);
		$this->assertEquals($api_data, $cached);

		// Cleanup
		remove_all_filters('pre_http_request');
		delete_site_transient('onecom_marketplace_catalog');
	}

	/**
	 * Test fetch_products_from_api builds correct URL with query parameters
	 */
	public function test_fetch_products_from_api_builds_correct_url() {
		// Arrange
		$obj = new OneHomeSections();
		delete_site_transient('onecom_marketplace_catalog');

		$captured_url = '';

		// Mock to capture the URL
		add_filter('pre_http_request', function($preempt, $args, $url) use (&$captured_url) {
			$captured_url = $url;
			return [
				'headers' => [],
				'body' => json_encode(['success' => true, 'data' => [], 'error' => null]),
				'response' => ['code' => 200],
				'cookies' => [],
				'filename' => null
			];
		}, 10, 3);

		// Act
		$obj->fetch_products_from_api(true);

		// Assert: URL should contain locale, php, and wp parameters
		$this->assertStringContainsString('locale=', $captured_url);
		$this->assertStringContainsString('php=', $captured_url);
		$this->assertStringContainsString('wp=', $captured_url);

		// Cleanup
		remove_all_filters('pre_http_request');
	}

	/**
	 * Test get_featured_products filters correctly
	 */
	public function test_get_featured_products_filters_featured_only() {
		// Arrange: Mock fetch_products_from_api to return test data
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'featured-plugin',
						'name' => 'Featured Plugin',
						'featured' => true
					],
					[
						'slug' => 'not-featured',
						'name' => 'Not Featured',
						'featured' => false
					],
					[
						'slug' => 'featured-plugin-2',
						'name' => 'Featured Plugin 2',
						'featured' => true
					]
				],
				'uiI18n' => []
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		// Mock active plugins to ensure none are active
		update_option('active_plugins', []);
		add_filter('active_plugins', function($plugins) { return []; });

		// Act
		$featured = $obj->get_featured_products();

		// Assert: Should only return featured products in catalog
		$this->assertCount(2, $featured['catalog']);
		foreach ($featured['catalog'] as $product) {
			$this->assertTrue($product['featured']);
		}

		// Cleanup
		remove_filter('active_plugins', '');
	}

	/**
	 * Test get_featured_products excludes active plugins
	 */
	public function test_get_featured_products_excludes_active_plugins() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'active-plugin',
						'name' => 'Active Plugin',
						'featured' => true
					],
					[
						'slug' => 'inactive-plugin',
						'name' => 'Inactive Plugin',
						'featured' => true
					]
				],
				'uiI18n' => []
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		// Mock active-plugin as active
		update_option('active_plugins', ['active-plugin/active-plugin.php']);
		add_filter('active_plugins', function($plugins) {
			return ['active-plugin/active-plugin.php'];
		});

		// Act
		$featured = $obj->get_featured_products();

		// Assert: Should only return inactive-plugin
		$this->assertCount(1, $featured['catalog']);
		$this->assertEquals('inactive-plugin', $featured['catalog'][0]['slug']);

		// Cleanup
		remove_filter('active_plugins', '');
		update_option('active_plugins', []);
	}

	/**
	 * Test get_featured_products handles invalid response structure
	 */
	public function test_get_featured_products_handles_invalid_response() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$obj->method('fetch_products_from_api')->willReturn([
			'success' => true,
			'data' => 'invalid_structure' // Not an array
		]);

		// Act
		$featured = $obj->get_featured_products();

		// Assert: Should return structure with empty catalog
		$this->assertIsArray($featured);
		$this->assertArrayHasKey('catalog', $featured);
		$this->assertEmpty($featured['catalog']);
	}

	/**
	 * Test get_featured_products returns empty array when no products
	 */
	public function test_get_featured_products_returns_empty_when_no_products() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$obj->method('fetch_products_from_api')->willReturn([
			'success' => true,
			'data' => [
				'catalog' => [],
				'uiI18n' => []
			]
		]);

		// Act
		$featured = $obj->get_featured_products();

		// Assert
		$this->assertIsArray($featured);
		$this->assertArrayHasKey('catalog', $featured);
		$this->assertEmpty($featured['catalog']);
	}

	/**
	 * Test get_admin_env_info returns all required fields
	 */
	public function test_get_admin_env_info_returns_all_fields() {
		// Arrange
		$obj = new OneHomeSections();

		// Act
		$env_info = $obj->get_admin_env_info();

		// Assert
		$this->assertIsArray($env_info);
		$this->assertArrayHasKey('locale', $env_info);
		$this->assertArrayHasKey('user_locale', $env_info);
		$this->assertArrayHasKey('wp_version', $env_info);
		$this->assertArrayHasKey('php_version', $env_info);

		// Verify values are not empty
		$this->assertNotEmpty($env_info['locale']);
		$this->assertNotEmpty($env_info['wp_version']);
		$this->assertNotEmpty($env_info['php_version']);
	}

	/**
	 * Test fetch_products_from_api uses default locale when empty
	 */
	public function test_fetch_products_from_api_uses_default_locale() {
		// Arrange
		$obj = new OneHomeSections();
		delete_site_transient('onecom_marketplace_catalog');

		$captured_url = '';

		// Mock get_locale to return empty
		add_filter('locale', function($locale) {
			return '';
		});

		add_filter('pre_http_request', function($preempt, $args, $url) use (&$captured_url) {
			$captured_url = $url;
			return [
				'headers' => [],
				'body' => json_encode(['success' => true, 'data' => [], 'error' => null]),
				'response' => ['code' => 200],
				'cookies' => [],
				'filename' => null
			];
		}, 10, 3);

		// Act
		$obj->fetch_products_from_api(true);

		// Assert: Should use en_GB as default
		$this->assertStringContainsString('locale=en_GB', $captured_url);

		// Cleanup
		remove_all_filters('pre_http_request');
		remove_all_filters('locale');
	}

	/**
	 * Test is_plugin_active_by_slug with various plugin path formats
	 */
	public function test_is_plugin_active_by_slug_various_formats() {
		// Arrange
		$obj = new OneHomeSections();

		// Test case 1: Plugin in subfolder
		update_option('active_plugins', ['test-plugin/test-plugin.php']);
		add_filter('active_plugins', function($plugins) {
			return ['test-plugin/test-plugin.php'];
		});

		$this->assertTrue($obj->is_plugin_active_by_slug('test-plugin'));

		// Test case 2: Different plugin
		$this->assertFalse($obj->is_plugin_active_by_slug('other-plugin'));

		// Test case 3: Multiple plugins active
		update_option('active_plugins', ['plugin-a/plugin-a.php', 'plugin-b/plugin-b.php']);
		remove_all_filters('active_plugins');
		add_filter('active_plugins', function($plugins) {
			return ['plugin-a/plugin-a.php', 'plugin-b/plugin-b.php'];
		});

		$this->assertTrue($obj->is_plugin_active_by_slug('plugin-a'));
		$this->assertTrue($obj->is_plugin_active_by_slug('plugin-b'));
		$this->assertFalse($obj->is_plugin_active_by_slug('plugin-c'));

		// Cleanup
		remove_all_filters('active_plugins');
		update_option('active_plugins', []);
	}

	/**
	 * Test reset_transient_for_featured_list actually deletes the transient
	 */
	public function test_reset_transient_actually_deletes() {
		// Arrange
		$obj = new OneHomeSections();
		$test_data = ['test' => 'data'];
		set_site_transient('onecom_marketplace_catalog', $test_data, 15 * MINUTE_IN_SECONDS);

		// Verify transient is set
		$this->assertEquals($test_data, get_site_transient('onecom_marketplace_catalog'));

		// Act
		$obj->reset_transient_for_featured_list();

		// Assert: Transient should be deleted
		$this->assertFalse(get_site_transient('onecom_marketplace_catalog'));
	}

	/**
	 * Test are_plugin_dependencies_active returns false when no plugins active
	 */
	public function test_are_plugin_dependencies_active_no_plugins() {
		$obj = new OneHomeSections();
		update_option('active_plugins', []);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertFalse($obj->are_plugin_dependencies_active($mustHavePlugins));
	}

	/**
	 * Test are_plugin_dependencies_active returns true with WPForms Lite
	 */
	public function test_are_plugin_dependencies_active_wpforms_lite() {
		$obj = new OneHomeSections();
		update_option('active_plugins', ['wpforms-lite/wpforms.php']);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
		update_option('active_plugins', []);
	}

	/**
	 * Test are_plugin_dependencies_active returns true with WPForms Pro
	 */
	public function test_are_plugin_dependencies_active_wpforms_pro() {
		$obj = new OneHomeSections();
		update_option('active_plugins', ['wpforms/wpforms.php']);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
		update_option('active_plugins', []);
	}

	/**
	 * Test are_plugin_dependencies_active returns true with Gravity Forms
	 */
	public function test_are_plugin_dependencies_active_gravity_forms() {
		$obj = new OneHomeSections();
		update_option('active_plugins', ['gravityforms/gravityforms.php']);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
		update_option('active_plugins', []);
	}

	/**
	 * Test are_plugin_dependencies_active returns true with Contact Form 7
	 */
	public function test_are_plugin_dependencies_active_contact_form_7() {
		$obj = new OneHomeSections();
		update_option('active_plugins', ['contact-form-7/wp-contact-form-7.php']);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
		update_option('active_plugins', []);
	}

	/**
	 * Test are_plugin_dependencies_active with multiple supported plugins
	 */
	public function test_are_plugin_dependencies_active_multiple_plugins() {
		$obj = new OneHomeSections();
		update_option('active_plugins', [
			'wpforms-lite/wpforms.php',
			'contact-form-7/wp-contact-form-7.php'
		]);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
		update_option('active_plugins', []);
	}

	/**
	 * Test are_plugin_dependencies_active with unsupported plugin
	 */
	public function test_are_plugin_dependencies_active_unsupported_plugin() {
		$obj = new OneHomeSections();
		update_option('active_plugins', ['unsupported-plugin/unsupported-plugin.php']);
		$mustHavePlugins = ['wpforms-lite', 'wpforms', 'gravityforms', 'contact-form-7'];
		$this->assertFalse($obj->are_plugin_dependencies_active($mustHavePlugins));
		update_option('active_plugins', []);
	}

	/**
	 * Test are_theme_dependencies_active returns boolean result
	 */
	public function test_are_theme_dependencies_active_returns_bool() {
		$obj = new OneHomeSections();
		$rules = ['mustHaveThemesByAuthor' => 'superbaddons'];
		// This test will pass regardless of the current theme
		// It just checks that the method returns a boolean
		$result = $obj->are_theme_dependencies_active($rules);
		$this->assertIsBool($result);
	}

	/**
	 * Test are_theme_dependencies_active with empty rules
	 */
	public function test_are_theme_dependencies_active_empty_rules() {
		$obj = new OneHomeSections();
		$result = $obj->are_theme_dependencies_active([]);
		$this->assertFalse($result);
	}

	/**
	 * Test get_featured_products filters out minicrm-bridge when no supported plugins
	 */
	public function test_get_featured_products_filters_minicrm_bridge_no_support() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'minicrm-bridge',
						'name' => 'MiniCRM Bridge',
						'featured' => true,
						'rules' => [
							'mustHavePlugins' => [
								'wpforms-lite',
								'wpforms',
								'gravityforms',
								'contact-form-7'
							]
						]
					],
					[
						'slug' => 'other-plugin',
						'name' => 'Other Plugin',
						'featured' => true
					]
				],
				'uiI18n' => []
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		// Ensure no supported plugins are active
		update_option('active_plugins', []);

		// Act
		$featured = $obj->get_featured_products();

		// Assert: minicrm-bridge should be filtered out
		$slugs = array_column($featured['catalog'], 'slug');
		$this->assertNotContains('minicrm-bridge', $slugs);
		$this->assertContains('other-plugin', $slugs);

		// Cleanup
		update_option('active_plugins', []);
	}

	/**
	 * Test get_featured_products includes minicrm-bridge when supported plugin active
	 */
	public function test_get_featured_products_includes_minicrm_bridge_with_support() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'minicrm-bridge',
						'name' => 'MiniCRM Bridge',
						'featured' => true
					]
				],
				'uiI18n' => []
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		// Activate a supported plugin
		update_option('active_plugins', ['wpforms-lite/wpforms.php']);

		// Act
		$featured = $obj->get_featured_products();

		// Assert: minicrm-bridge should be included
		$slugs = array_column($featured['catalog'], 'slug');
		$this->assertContains('minicrm-bridge', $slugs);

		// Cleanup
		update_option('active_plugins', []);
	}

	/**
	 * Test get_featured_products filters out superb-blocks when theme not active
	 */
	public function test_get_featured_products_filters_superb_blocks_no_theme() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'superb-blocks',
						'name' => 'Superb Blocks',
						'featured' => true,
						'rules' => [
							'mustHaveThemesByAuthor' => 'superbaddons'
						]
					],
					[
						'slug' => 'other-plugin',
						'name' => 'Other Plugin',
						'featured' => true
					]
				],
				'uiI18n' => []
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		update_option('active_plugins', []);

		// Act
		$featured = $obj->get_featured_products();

		// Assert: superb-blocks should be filtered out
		$slugs = array_column($featured['catalog'], 'slug');
		$this->assertNotContains('superb-blocks', $slugs);

		// Cleanup
		update_option('active_plugins', []);
	}

	/**
	 * Test get_featured_products returns proper structure with catalog and uiI18n
	 */
	public function test_get_featured_products_returns_proper_structure() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'test-plugin',
						'name' => 'Test Plugin',
						'featured' => true
					]
				],
				'uiI18n' => [
					'featuredCta' => 'Get started'
				]
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		update_option('active_plugins', []);

		// Act
		$featured = $obj->get_featured_products();

		// Assert
		$this->assertIsArray($featured);
		$this->assertArrayHasKey('catalog', $featured);
		$this->assertArrayHasKey('uiI18n', $featured);
		$this->assertEquals('Get started', $featured['uiI18n']['featuredCta']);

		// Cleanup
		update_option('active_plugins', []);
	}

	/**
	 * Test get_featured_products reverses product order
	 */
	public function test_get_featured_products_reverses_order() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['fetch_products_from_api'])
			->getMock();

		// WPIN-8909: get_featured_products() doesn't reverse anything - it
		// sorts by each product's 'displayOrder' (ascending). This fixture
		// never set that key, so every product fell back to the same
		// PHP_INT_MAX default and usort() (stable since PHP 8) just kept
		// insertion order - the opposite of what this test asserts. Give
		// each product an explicit displayOrder so the test actually
		// exercises the sort instead of relying on it being a no-op.
		$api_response = [
			'success' => true,
			'data' => [
				'catalog' => [
					[
						'slug' => 'first-plugin',
						'name' => 'First Plugin',
						'featured' => true,
						'displayOrder' => 3
					],
					[
						'slug' => 'second-plugin',
						'name' => 'Second Plugin',
						'featured' => true,
						'displayOrder' => 2
					],
					[
						'slug' => 'third-plugin',
						'name' => 'Third Plugin',
						'featured' => true,
						'displayOrder' => 1
					]
				],
				'uiI18n' => []
			]
		];

		$obj->method('fetch_products_from_api')->willReturn($api_response);

		update_option('active_plugins', []);

		// Act
		$featured = $obj->get_featured_products();

		// Assert: Order should be reversed
		$this->assertEquals('third-plugin', $featured['catalog'][0]['slug']);
		$this->assertEquals('second-plugin', $featured['catalog'][1]['slug']);
		$this->assertEquals('first-plugin', $featured['catalog'][2]['slug']);

		// Cleanup
		update_option('active_plugins', []);
	}

	/**
	 * Test get_recommended_products_list returns HTML with products
	 */
	public function test_get_recommended_products_list_with_products() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_featured_products'])
			->getMock();

		$obj->method('get_featured_products')->willReturn([
			'catalog' => [
				[
					'slug' => 'test-plugin',
					'bannerUrl' => 'https://example.com/banner.jpg',
					'i18n' => [
						'featuredTitle' => 'Test Plugin',
						'featuredContent' => 'Test Description'
					]
				]
			],
			'uiI18n' => [
				'featuredCta' => 'Get started'
			]
		]);

		// Act
		$html = $obj->get_recommended_products_list();

		// Assert
		$this->assertIsString($html);
		$this->assertStringContainsString('gv-card', $html);
		$this->assertStringContainsString('Test Plugin', $html);
		$this->assertStringContainsString('Test Description', $html);
		$this->assertStringContainsString('Get started', $html);
		$this->assertStringContainsString('data-product-slug="test-plugin"', $html);
	}

	/**
	 * Test get_recommended_products_list returns empty when no products
	 */
	public function test_get_recommended_products_list_no_products() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_featured_products'])
			->getMock();

		$obj->method('get_featured_products')->willReturn([
			'catalog' => [],
			'uiI18n' => []
		]);

		// Act
		$html = $obj->get_recommended_products_list();

		// Assert: Should return empty string or minimal HTML
		$this->assertIsString($html);
		$this->assertStringNotContainsString('gv-card', $html);
	}

	/**
	 * Test get_recommended_products_list limits to 3 products
	 */
	public function test_get_recommended_products_list_limits_to_three() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_featured_products'])
			->getMock();

		$products = [];
		for ($i = 1; $i <= 5; $i++) {
			$products[] = [
				'slug' => "plugin-$i",
				'bannerUrl' => "https://example.com/banner-$i.jpg",
				'i18n' => [
					'featuredTitle' => "Plugin $i",
					'featuredContent' => "Description $i"
				]
			];
		}

		$obj->method('get_featured_products')->willReturn([
			'catalog' => $products,
			'uiI18n' => ['featuredCta' => 'Get started']
		]);

		// Act
		$html = $obj->get_recommended_products_list();

		// Assert: Should only show 3 cards
		$card_count = substr_count($html, 'data-product-slug=');
		$this->assertEquals(3, $card_count);
		$this->assertStringContainsString('plugin-1', $html);
		$this->assertStringContainsString('plugin-2', $html);
		$this->assertStringContainsString('plugin-3', $html);
		$this->assertStringNotContainsString('plugin-4', $html);
		$this->assertStringNotContainsString('plugin-5', $html);
	}

	/**
	 * Test get_recommended_products_list uses default CTA when not in uiI18n
	 */
	public function test_get_recommended_products_list_default_cta() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_featured_products'])
			->getMock();

		$obj->method('get_featured_products')->willReturn([
			'catalog' => [
				[
					'slug' => 'test-plugin',
					'bannerUrl' => 'https://example.com/banner.jpg',
					'i18n' => [
						'featuredTitle' => 'Test Plugin',
						'featuredContent' => 'Test Description'
					]
				]
			],
			'uiI18n' => [] // No featuredCta
		]);

		// Act
		$html = $obj->get_recommended_products_list();

		// Assert: Should use default "Get started" text
		$this->assertStringContainsString('Get started', $html);
	}

	/**
	 * Test get_recommended_products_list includes marketplace link
	 */
	public function test_get_recommended_products_list_includes_marketplace_link() {
		// Arrange
		$obj = $this->getMockBuilder('OneHomeSections')
			->setMethods(['get_featured_products'])
			->getMock();

		$obj->method('get_featured_products')->willReturn([
			'catalog' => [
				[
					'slug' => 'test-plugin',
					'bannerUrl' => 'https://example.com/banner.jpg',
					'i18n' => [
						'featuredTitle' => 'Test Plugin',
						'featuredContent' => 'Test Description'
					]
				]
			],
			'uiI18n' => ['featuredCta' => 'Get started']
		]);

		// Act
		$html = $obj->get_recommended_products_list();

		// Assert
		$this->assertStringContainsString('onecom-marketplace', $html);
		$this->assertStringContainsString('See all products', $html);
	}

	/**
	 * Test constructor hooks are registered
	 */
	public function test_constructor_registers_hooks() {
		// Arrange & Act
		$obj = new OneHomeSections();

		// Assert: Verify hooks are registered
		// WPIN-8909: the constructor now wires activated_plugin/deactivated_plugin
		// to groupone_clear_mp_catalog_transients() - reset_transient_for_featured_list()
		// still exists, but it's hooked to update_option_WPLANG instead.
		$this->assertNotFalse(has_action('wp_ajax_oc_close_welcome_modal'));
		$this->assertNotFalse(has_action('deactivated_plugin', [$obj, 'groupone_clear_mp_catalog_transients']));
		$this->assertNotFalse(has_action('activated_plugin', [$obj, 'groupone_clear_mp_catalog_transients']));
	}

	/**
	 * Test get_module_toggles returns one entry per merged module, shaped
	 * for the Home page toggle list (WPIN-8736).
	 */
	public function test_get_module_toggles_returns_expected_structure() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		delete_option( 'onecom_disabled_modules' );
		update_option( 'active_plugins', [] );
		// get_module_toggles() now calls ismWP(), which reads this transient;
		// seed it so the test doesn't fall through to a live network call.
		set_site_transient( 'oc_premi_flag', [ 'MWP_ADDON' ], 12 * HOUR_IN_SECONDS );

		$obj     = new OneHomeSections();
		$toggles = $obj->get_module_toggles();

		$this->assertIsArray( $toggles );
		$this->assertNotEmpty( $toggles );

		$slugs = array_column( $toggles, 'slug' );
		$this->assertContains( 'performance-cache', $slugs );
		$this->assertContains( 'spam-protection', $slugs );
		$this->assertContains( 'maintenance-mode', $slugs );

		foreach ( $toggles as $toggle ) {
			$this->assertArrayHasKey( 'label', $toggle );
			$this->assertArrayHasKey( 'enabled', $toggle );
			$this->assertArrayHasKey( 'toggleable', $toggle );
			$this->assertArrayHasKey( 'note', $toggle );
			$this->assertArrayHasKey( 'mwp_only', $toggle );
			$this->assertIsBool( $toggle['enabled'] );
			$this->assertIsBool( $toggle['toggleable'] );
			$this->assertIsBool( $toggle['mwp_only'] );
		}

		// WPIN-9042: the Home template only reserves the toggle-loader slot
		// for mwp_only rows, so this flag has to correctly single out
		// spam-protection (the only merged module gated on ismWP()) from the
		// other two.
		$toggles_by_slug = array_column( $toggles, 'mwp_only', 'slug' );
		$this->assertTrue( $toggles_by_slug['spam-protection'] );
		$this->assertFalse( $toggles_by_slug['performance-cache'] );
		$this->assertFalse( $toggles_by_slug['maintenance-mode'] );

		delete_option( 'onecom_disabled_modules' );
	}

	/**
	 * A module whose legacy standalone plugin is active should report as
	 * non-toggleable with an explanatory note, matching the old Settings
	 * page's UX.
	 */
	public function test_get_module_toggles_marks_legacy_active_module_non_toggleable() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		delete_option( 'onecom_disabled_modules' );
		update_option( 'active_plugins', [ 'onecom-spam-protection/onecom-spam-protection.php' ] );
		// get_module_toggles() now calls ismWP(), which reads this transient;
		// seed it so the test doesn't fall through to a live network call.
		set_site_transient( 'oc_premi_flag', [ 'MWP_ADDON' ], 12 * HOUR_IN_SECONDS );

		$obj     = new OneHomeSections();
		$toggles = $obj->get_module_toggles();

		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );

		$this->assertNotFalse( $spam_toggle );
		$this->assertFalse( $spam_toggle['toggleable'] );
		$this->assertNotSame( '', $spam_toggle['note'] );

		update_option( 'active_plugins', [] );
		delete_option( 'onecom_disabled_modules' );
	}

	/**
	 * A module whose standalone plugin is active is skipped at load time, so
	 * it is not running and must not render as enabled — the persisted
	 * onecom_disabled_modules option alone doesn't capture that.
	 */
	public function test_get_module_toggles_reports_legacy_active_module_disabled() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		delete_option( 'onecom_disabled_modules' );
		update_option( 'active_plugins', [ 'onecom-spam-protection/onecom-spam-protection.php' ] );
		// mWP present, so the only thing gating the module is the legacy plugin.
		set_site_transient( 'oc_premi_flag', [ 'MWP_ADDON' ], 12 * HOUR_IN_SECONDS );

		$obj     = new OneHomeSections();
		$toggles = $obj->get_module_toggles();

		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );

		$this->assertNotFalse( $spam_toggle );
		$this->assertFalse( $spam_toggle['enabled'] );

		// The persisted option is untouched, so the module reports enabled
		// again the moment the standalone plugin is deactivated.
		update_option( 'active_plugins', [] );
		$toggles     = ( new OneHomeSections() )->get_module_toggles();
		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );
		$this->assertTrue( $spam_toggle['enabled'] );

		delete_option( 'onecom_disabled_modules' );
	}

	/**
	 * WPIN-8862: spam-protection is the one module that is both mwp_only and
	 * has a legacy standalone plugin, so it can be gated by both at once. The
	 * old if/elseif chain showed only the legacy reason, leaving the row
	 * displaying an 'mWP-only' badge whose text never mentioned MWP.
	 */
	public function test_get_module_toggles_shows_both_reasons_when_doubly_gated() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		delete_option( 'onecom_disabled_modules' );
		// Both gates at once: legacy plugin active AND no MWP.
		update_option( 'active_plugins', [ 'onecom-spam-protection/onecom-spam-protection.php' ] );
		set_site_transient( 'oc_premi_flag', [ 'PERFORMANCE_CACHE' ], 12 * HOUR_IN_SECONDS );

		$obj     = new OneHomeSections();
		$toggles = $obj->get_module_toggles();

		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );

		$this->assertNotFalse( $spam_toggle );
		$this->assertStringContainsString( 'standalone plugin', $spam_toggle['note'] );
		$this->assertStringContainsString( 'Managed WordPress', $spam_toggle['note'] );
		// The badge was always rendered; the point is that the text now agrees
		// with it instead of contradicting it by omission.
		$this->assertSame( 'mWP-only', $spam_toggle['badge'] );
		$this->assertFalse( $spam_toggle['toggleable'] );

		update_option( 'active_plugins', [] );
		delete_option( 'onecom_disabled_modules' );
	}

	/**
	 * WPIN-8862: a single gate must still read as a single sentence — the
	 * combining must not leave stray separators or duplicated text.
	 */
	public function test_get_module_toggles_single_gate_note_is_unchanged() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		delete_option( 'onecom_disabled_modules' );
		update_option( 'active_plugins', [] );
		set_site_transient( 'oc_premi_flag', [ 'PERFORMANCE_CACHE' ], 12 * HOUR_IN_SECONDS );

		$obj     = new OneHomeSections();
		$toggles = $obj->get_module_toggles();

		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );

		$this->assertSame(
			'Available on Managed WordPress plans only.',
			$spam_toggle['note'],
			'With only the MWP gate active the note must be exactly that one sentence.'
		);

		// And a module under no gate at all keeps an empty note, not ' '.
		$mm_toggle = current( array_filter( $toggles, fn( $t ) => 'maintenance-mode' === $t['slug'] ) );

		$this->assertSame( '', $mm_toggle['note'] );

		delete_option( 'onecom_disabled_modules' );
	}

	/**
	 * WPIN-8907: on a non-MWP site where spam-protection was never
	 * explicitly toggled, onecom_disabled_modules has no entry for it, so
	 * checking persisted state alone would report 'enabled' => true even
	 * though the module's own bootstrap never loads its filtering classes
	 * without MWP. The row must report 'enabled' => false while staying
	 * 'toggleable' => true, so a click reaches the live AJAX gate and shows
	 * the upsell banner instead of hitting a dead, already-checked control.
	 */
	public function test_get_module_toggles_mwp_only_module_reports_disabled_when_not_mwp() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		delete_option( 'onecom_disabled_modules' );
		update_option( 'active_plugins', [] );
		// Non-empty and truthy so groupone_set_premi_flag() doesn't fall
		// through to a live network call, but without MWP_ADDON so ismWP()
		// resolves to false.
		set_site_transient( 'oc_premi_flag', [ 'PERFORMANCE_CACHE' ], 12 * HOUR_IN_SECONDS );

		$obj     = new OneHomeSections();
		$toggles = $obj->get_module_toggles();

		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );

		$this->assertNotFalse( $spam_toggle );
		$this->assertFalse( $spam_toggle['enabled'] );
		$this->assertTrue( $spam_toggle['toggleable'] );
		$this->assertSame( 'mWP-only', $spam_toggle['badge'] );
		$this->assertNotSame( '', $spam_toggle['note'] );

		delete_option( 'onecom_disabled_modules' );
	}

	/**
	 * WPIN-8907: the persisted onecom_disabled_modules option must not be
	 * touched by the MWP gate itself — only the displayed 'enabled' state.
	 * A site that had spam-protection enabled before losing MWP keeps that
	 * persisted state, so the row shows enabled again the instant MWP
	 * status returns, without the customer re-toggling anything.
	 */
	public function test_get_module_toggles_mwp_only_module_re_enables_once_mwp_returns() {
		require_once GROUPONE_WP_PATH . 'inc/module-registry.php';
		// Explicitly persisted as enabled (not in the disabled list), same
		// as a site that legitimately turned this on while it had MWP.
		update_option( 'onecom_disabled_modules', [] );
		update_option( 'active_plugins', [] );
		set_site_transient( 'oc_premi_flag', [ 'MWP_ADDON' ], 12 * HOUR_IN_SECONDS );

		$obj         = new OneHomeSections();
		$toggles     = $obj->get_module_toggles();
		$spam_toggle = current( array_filter( $toggles, fn( $t ) => 'spam-protection' === $t['slug'] ) );

		$this->assertNotFalse( $spam_toggle );
		$this->assertTrue( $spam_toggle['enabled'] );
		$this->assertTrue( $spam_toggle['toggleable'] );

		delete_option( 'onecom_disabled_modules' );
	}

    // Test are_plugin_dependencies_active uses is_plugin_active_by_slug for plugin activation
    public function test_are_plugin_dependencies_active() {
        $obj = new OneHomeSections();
        update_option('active_plugins', [
            'wpforms-lite/wpforms.php',
            'gravityforms/gravityforms.php',
            'some-other/some-other.php'
        ]);
        add_filter('active_plugins', function($plugins) {
            return [
                'wpforms-lite/wpforms.php',
                'gravityforms/gravityforms.php',
                'some-other/some-other.php'
            ];
        });
        $mustHavePlugins = [
            'wpforms-lite',
            'wpforms',
            'gravityforms',
            'contact-form-7'
        ];
        $this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
        update_option('active_plugins', ['some-other/some-other.php']);
        add_filter('active_plugins', function($plugins) {
            return ['some-other/some-other.php'];
        });
        $this->assertFalse($obj->are_plugin_dependencies_active($mustHavePlugins));
        update_option('active_plugins', ['contact-form-7/wp-contact-form-7.php']);
        add_filter('active_plugins', function($plugins) {
            return ['contact-form-7/wp-contact-form-7.php'];
        });
        $this->assertTrue($obj->are_plugin_dependencies_active($mustHavePlugins));
        $genericPlugins = ['some-other'];
        $this->assertFalse($obj->are_plugin_dependencies_active($genericPlugins));
        update_option('active_plugins', []);
        add_filter('active_plugins', function($plugins) {
            return [];
        });
        $this->assertFalse($obj->are_plugin_dependencies_active($genericPlugins));
        remove_filter('active_plugins', '');
        update_option('active_plugins', []);
    }

    // WPIN-8925: oc_home_ajax_obj must carry the module->settings-page-slug
    // map so modules/home/js/main.js knows which sidebar <li> to hide/show
    // immediately after a toggle.
    public function test_localizes_module_page_slugs_for_home_scripts() {
        // Firing admin_enqueue_scripts here also invokes every other module's
        // own callback hooked to it (this test bootstrap doesn't load every
        // module the way a full site request would) -- e.g. cookie-banner
        // registers 'groupone_cb_scripts_admin' by name at file-load time,
        // so it must actually be defined before we fire the shared hook.
        if ( ! function_exists( 'groupone_cb_scripts_admin' ) ) {
            require_once GROUPONE_WP_PATH . 'modules/cookie-banner/inc/functions.php';
        }

        set_current_screen('toplevel_page_onecom-wp-spam-protection');

        do_action('admin_enqueue_scripts', 'toplevel_page_onecom-wp-spam-protection');

        $localized = wp_scripts()->get_data('oc_home_page_main', 'data');
        $this->assertNotFalse($localized, 'Expected oc_home_page_main to have localized data.');
        $this->assertStringContainsString('module_page_slugs', $localized);

        foreach (groupone_module_page_slugs() as $slugs) {
            foreach ($slugs as $slug) {
                $this->assertStringContainsString($slug, $localized);
            }
        }
    }

    // WPIN-9031: Verify home template renders the toggle wrapper and spinner markup for module toggles
    public function test_home_template_includes_toggle_spinner_elements() {
        set_site_transient('oc_premi_flag', ['MWP_ADDON'], 12 * HOUR_IN_SECONDS);
        $obj = new OneHomeSections();
        $module_toggles = $obj->get_module_toggles();
        $module_toggle_nonce = wp_create_nonce('oc_toggle_module');

        ob_start();
        include GROUPONE_WP_PATH . 'modules/home/templates/home.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('oc-toggle-wrap', $output);
        $this->assertStringContainsString('oc-module-toggle-spinner', $output);
        $this->assertStringContainsString('oc-module-toggle-spam-protection', $output);

        // WPIN-9042: the spinner markup must be scoped to mwp_only rows
        // (only spam-protection today), not emitted once per module — and it
        // must sit before the toggle in markup so it renders to the left.
        $mwp_only_count = count( array_filter( array_column( $module_toggles, 'mwp_only' ) ) );
        $this->assertSame(
            $mwp_only_count,
            substr_count( $output, 'oc-module-toggle-spinner' ),
            'Expected exactly one spinner per mwp_only module row.'
        );
        $this->assertMatchesRegularExpression(
            '/spinner oc-module-toggle-spinner.*?gv-toggle"/s',
            $output,
            'Expected the spinner markup to precede the toggle control in the row.'
        );
    }

    // WPIN-9030: Verify oc_home_page_main localizes sp_popup_content for mWP upgrade modal
    public function test_oc_home_main_localizes_sp_popup_content() {
        wp_dequeue_script('oc_home_page_main');
        wp_deregister_script('oc_home_page_main');

        set_current_screen('one-com_page_onecom-home');
        do_action('admin_enqueue_scripts', 'one-com_page_onecom-home');

        $localized = wp_scripts()->get_data('oc_home_page_main', 'data');
        $this->assertNotFalse($localized, 'Expected oc_home_page_main to have localized data.');
        $this->assertStringContainsString('sp_popup_content', $localized);
        $this->assertStringContainsString('Get Spam Protection', $localized);
    }

    // WPIN-9029: verify module menu entries returned for sidebar and toolbar sync
    public function test_groupone_module_menu_entries_returns_expected_structure() {
        $pc_menu = groupone_module_menu_entries('performance-cache');
        $this->assertIsArray($pc_menu);
        $this->assertArrayHasKey('accordion', $pc_menu);
        $this->assertArrayHasKey('items', $pc_menu);

        $mm_menu = groupone_module_menu_entries('maintenance-mode');
        $this->assertIsArray($mm_menu);
        $this->assertArrayHasKey('items', $mm_menu);

        $sp_menu = groupone_module_menu_entries('spam-protection');
        $this->assertIsArray($sp_menu);
        $this->assertArrayHasKey('items', $sp_menu);
    }
}
