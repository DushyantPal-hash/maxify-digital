<?php
/**
 * Module-level tests: slug inventory, helper plumbing.
 *
 * @package Onecom_Themes_Plugins
 */

class Test_Onecom_Abilities_Module extends WP_UnitTestCase {

	public function test_domain_classes_exist() {
		foreach ( onecom_abilities_domain_classes() as $class ) {
			$this->assertTrue( class_exists( $class ), "$class should be loaded" );
		}
	}

	public function test_ability_ids_are_unique_and_onecom_namespaced() {
		$ids = onecom_abilities_ids();
		$this->assertNotEmpty( $ids );
		$this->assertSame( $ids, array_values( array_unique( $ids ) ), 'ability slugs must be unique' );
		foreach ( $ids as $id ) {
			$this->assertStringStartsWith( 'onecom/', $id, "$id must use the onecom/ namespace" );
		}
	}

	public function test_expected_surface_is_registered() {
		$ids = onecom_abilities_ids();
		foreach ( array( 'onecom/find-posts', 'onecom/get-menu', 'onecom/install-plugin', 'onecom/switch-theme' ) as $expected ) {
			$this->assertContains( $expected, $ids );
		}
		// Policy: deletion of plugins/themes is not part of this surface.
		$this->assertNotContains( 'onecom/delete-plugin', $ids );
		$this->assertNotContains( 'onecom/delete-theme', $ids );
	}

	public function test_superseded_core_abilities_are_core_namespaced() {
		$superseded = onecom_abilities_superseded_core_ids();
		$this->assertNotEmpty( $superseded );
		$this->assertSame( $superseded, array_values( array_unique( $superseded ) ) );
		foreach ( $superseded as $id ) {
			$this->assertStringStartsWith( 'core/', $id, "$id must be a core ability slug" );
		}
		// The one.com policy-carrying replacements must actually exist.
		foreach ( array( 'core/update-post', 'core/deactivate-plugin', 'core/get-menu' ) as $core ) {
			$this->assertContains( $core, $superseded );
		}
		// The info abilities we do NOT replace must stay available on the server.
		foreach ( array( 'core/get-site-info', 'core/get-user-info', 'core/get-environment-info' ) as $kept ) {
			$this->assertNotContains( $kept, $superseded );
		}
	}

	public function test_helper_can_requires_authentication() {
		wp_set_current_user( 0 );
		$result = Onecom_Abilities_Helper::can( 'read' );
		$this->assertWPError( $result );
		$this->assertSame( 'onecom_not_authenticated', $result->get_error_code() );
	}

	public function test_helper_can_enforces_capability() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$result = Onecom_Abilities_Helper::can( 'activate_plugins' );
		$this->assertWPError( $result );
		$this->assertSame( 'onecom_insufficient_capability', $result->get_error_code() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( Onecom_Abilities_Helper::can( 'activate_plugins' ) );
	}

	public function test_tool_meta_is_never_public() {
		$meta = Onecom_Abilities_Helper::tool_meta( array( 'readonly' => true ) );
		$this->assertSame( 'tool', $meta['mcp']['type'] );
		$this->assertArrayNotHasKey( 'public', $meta['mcp'], 'onecom abilities must not auto-expose on the default MCP server' );
		$this->assertSame( array( 'readonly' => true ), $meta['annotations'] );
	}

	public function test_ok_and_fail_shapes() {
		$this->assertSame( array( 'success' => true, 'x' => 1 ), Onecom_Abilities_Helper::ok( array( 'x' => 1 ) ) );
		$fail = Onecom_Abilities_Helper::fail( 'nope', 'code_x' );
		$this->assertFalse( $fail['success'] );
		$this->assertSame( 'nope', $fail['error'] );
		$this->assertSame( 'code_x', $fail['code'] );
	}
}
