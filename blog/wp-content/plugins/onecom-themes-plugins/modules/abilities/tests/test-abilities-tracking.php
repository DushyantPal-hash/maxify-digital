<?php
/**
 * MCP ability-execution tracking tests.
 *
 * The dispatch itself (OCPushStats cURL) is not exercised; these tests lock the
 * schema mapping (event_action / item_category / item_name / status) and the
 * MCP-request gating, which is where the behaviour actually lives.
 *
 * @package Onecom_Themes_Plugins
 */

class Test_Onecom_Abilities_Tracking extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Detection now also reads Accept and the request path, so clear every
		// signal up front to keep the negative assertions deterministic.
		$this->clear_mcp_signals();
	}

	public function tear_down() {
		$this->clear_mcp_signals();
		remove_all_filters( 'onecom_abilities_is_mcp_request' );
		parent::tear_down();
	}

	private function clear_mcp_signals() {
		unset(
			$_SERVER['HTTP_MCP_SESSION_ID'],
			$_SERVER['HTTP_MCP_PROTOCOL_VERSION'],
			$_SERVER['HTTP_ACCEPT'],
			$_SERVER['HTTP_X_ORIGINAL_URL'],
			$_SERVER['REQUEST_URI']
		);
	}

	public function test_tracked_surface_matches_the_ability_inventory() {
		$tracked  = array_keys( Onecom_Abilities_Tracking::events() );
		$expected = array_merge(
			onecom_abilities_ids(),
			array( 'onecom-marketplace/get-addon-status', 'onecom-marketplace/install-addon' )
		);

		sort( $tracked );
		sort( $expected );
		$this->assertSame( $expected, $tracked, 'tracker must cover exactly the onecom abilities + the two marketplace add-on abilities' );
	}

	public function test_event_action_and_category_are_well_formed() {
		$allowed_domains = array( 'content', 'navigation', 'plugin', 'theme', 'marketplace' );

		foreach ( Onecom_Abilities_Tracking::events() as $slug => $descriptor ) {
			list( $action, $category ) = $descriptor;
			$this->assertMatchesRegularExpression( '/^[a-z0-9]+(_[a-z0-9]+)*$/', $action, "$slug action must be snake_case" );
			$this->assertContains( $category, $allowed_domains, "$slug category must be a coarse domain" );
		}
	}

	public function test_describe_ignores_untracked_abilities() {
		$this->assertNull( Onecom_Abilities_Tracking::describe( 'core/get-site-info', array(), array( 'success' => true ) ) );
		$this->assertNull( Onecom_Abilities_Tracking::describe( 'marketplace/install-plugin', array(), array( 'success' => true ) ) );
	}

	public function test_describe_success_payload() {
		$event = Onecom_Abilities_Tracking::describe(
			'onecom/create-post',
			array( 'post_type' => 'page' ),
			array( 'success' => true, 'post' => array( 'id' => 5, 'type' => 'page' ) )
		);

		$this->assertSame( 'post_created', $event['event_action'] );
		$this->assertSame( 'content', $event['item_category'] );
		$this->assertSame( 'page', $event['item_name'] );
		$this->assertSame( array( 'status' => 'success' ), $event['additional'] );
	}

	public function test_describe_error_payload_carries_code() {
		$event = Onecom_Abilities_Tracking::describe(
			'onecom/create-post',
			array(),
			array( 'success' => false, 'code' => 'onecom_cannot_publish', 'error' => 'no' )
		);

		$this->assertSame( 'error', $event['additional']['status'] );
		$this->assertSame( 'onecom_cannot_publish', $event['additional']['error_code'] );
	}

	public function test_describe_maps_wp_error_to_error_status() {
		$event = Onecom_Abilities_Tracking::describe(
			'onecom/update-plugin',
			array( 'plugin_file' => 'akismet/akismet.php' ),
			new WP_Error( 'boom', 'kaboom' )
		);

		$this->assertSame( 'error', $event['additional']['status'] );
		$this->assertSame( 'boom', $event['additional']['error_code'] );
	}

	public function test_item_name_derivations() {
		$cases = array(
			// slug, input, result, expected item_name.
			array( 'onecom/list-plugins', array(), array( 'success' => true ), '' ),
			array( 'onecom/list-themes', array(), array( 'success' => true ), '' ),
			array( 'onecom/list-menus', array(), array( 'success' => true ), 'menu' ),
			array( 'onecom/add-menu-item', array( 'menu_id' => 3 ), array( 'success' => true ), 'menu' ),
			array( 'onecom/install-plugin', array( 'slug' => 'wordpress-seo' ), array( 'success' => true ), 'wordpress-seo' ),
			array( 'onecom/get-plugin', array( 'plugin_file' => 'akismet/akismet.php' ), array( 'success' => true ), 'akismet' ),
			array( 'onecom/switch-theme', array( 'stylesheet' => 'twentytwentyfour' ), array( 'success' => true ), 'twentytwentyfour' ),
			array( 'onecom/install-theme', array( 'slug' => 'astra' ), array( 'success' => true ), 'astra' ),
			array( 'onecom-marketplace/install-addon', array( 'addon' => 'wp-rocket' ), array( 'success' => true ), 'wp-rocket' ),
			array( 'onecom-marketplace/get-addon-status', array( 'addon' => 'rank-math' ), array( 'success' => true ), 'rank-math' ),
			// Posts fall back to the input post_type when the result has no post.
			array( 'onecom/find-posts', array( 'post_type' => 'page' ), array( 'success' => true, 'posts' => array() ), 'page' ),
			array( 'onecom/find-posts', array(), array( 'success' => true, 'posts' => array() ), 'post' ),
		);

		foreach ( $cases as $case ) {
			list( $slug, $input, $result, $expected ) = $case;
			$event = Onecom_Abilities_Tracking::describe( $slug, $input, $result );
			$this->assertSame( $expected, $event['item_name'], "$slug item_name" );
		}
	}

	public function test_is_mcp_request_reads_headers() {
		$this->assertFalse( Onecom_Abilities_Tracking::is_mcp_request() );

		$_SERVER['HTTP_MCP_SESSION_ID'] = 'abc123';
		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );
		unset( $_SERVER['HTTP_MCP_SESSION_ID'] );

		$_SERVER['HTTP_MCP_PROTOCOL_VERSION'] = '2025-11-25';
		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );
	}

	/**
	 * ChatGPT (`openai-mcp/1.0.0`) sends neither transport header — it carries its
	 * own `X-OpenAI-Session` — so gating on those alone dropped all of its ability
	 * executions. Headers below are from a captured Easy MCP AI request.
	 */
	public function test_is_mcp_request_detects_client_without_transport_headers() {
		$_SERVER['HTTP_ACCEPT']         = 'application/json, text/event-stream';
		$_SERVER['HTTP_X_ORIGINAL_URL'] = '/wp-json/easy-mcp-ai/v1/mcp';

		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );
	}

	public function test_is_mcp_request_detects_event_stream_accept_alone() {
		$_SERVER['HTTP_ACCEPT'] = 'application/json, text/event-stream';

		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );
	}

	public function test_is_mcp_request_detects_mcp_endpoint_path() {
		$_SERVER['REQUEST_URI'] = '/wp-json/easy-mcp-ai/v1/mcp';
		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );

		// `?rest_route=` fallback when pretty permalinks are off.
		$_SERVER['REQUEST_URI'] = '/index.php?rest_route=/easy-mcp-ai/v1/mcp';
		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );
	}

	/**
	 * Widening detection must not start attributing our own UI traffic to MCP.
	 */
	public function test_is_mcp_request_ignores_non_mcp_requests() {
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin-ajax.php?action=onecom_install_plugin';
		$_SERVER['HTTP_ACCEPT'] = '*/*';
		$this->assertFalse( Onecom_Abilities_Tracking::is_mcp_request() );

		$_SERVER['REQUEST_URI'] = '/wp-json/onecom-marketplace/v1/plugins';
		$_SERVER['HTTP_ACCEPT'] = 'application/json';
		$this->assertFalse( Onecom_Abilities_Tracking::is_mcp_request() );

		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=onecom-marketplace';
		$_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml';
		$this->assertFalse( Onecom_Abilities_Tracking::is_mcp_request() );
	}

	public function test_is_mcp_request_is_filterable() {
		add_filter( 'onecom_abilities_is_mcp_request', '__return_true' );
		$this->assertTrue( Onecom_Abilities_Tracking::is_mcp_request() );
	}

	public function test_flush_is_safe_when_empty() {
		// Should neither dispatch nor error with an empty queue.
		Onecom_Abilities_Tracking::flush();
		$this->assertTrue( true );
	}
}
