<?php
/**
 * Navigation menu ability tests.
 *
 * @package Onecom_Themes_Plugins
 */

class Test_Onecom_Abilities_Menus extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	private function create_menu( string $name = 'Test menu' ): int {
		$result = Onecom_Abilities_Menus::create_menu( array( 'name' => $name ) );
		$this->assertTrue( $result['success'] );
		return $result['menu']['id'];
	}

	public function test_create_and_list_menus() {
		$menu_id = $this->create_menu( 'Header menu' );

		$list = Onecom_Abilities_Menus::list_menus( array() );
		$this->assertTrue( $list['success'] );
		$ids = wp_list_pluck( $list['menus'], 'id' );
		$this->assertContains( $menu_id, $ids );
	}

	public function test_create_menu_requires_name() {
		$result = Onecom_Abilities_Menus::create_menu( array( 'name' => '' ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_menu_name_required', $result['code'] );
	}

	public function test_add_custom_link_item_and_get_menu() {
		$menu_id = $this->create_menu();

		$added = Onecom_Abilities_Menus::add_menu_item(
			array(
				'menu_id' => $menu_id,
				'title'   => 'one.com',
				'url'     => 'https://one.com/',
			)
		);
		$this->assertTrue( $added['success'] );
		$this->assertSame( 'custom', $added['item']['type'] );

		$menu = Onecom_Abilities_Menus::get_menu( array( 'menu_id' => $menu_id ) );
		$this->assertTrue( $menu['success'] );
		$this->assertCount( 1, $menu['items'] );
		$this->assertSame( 'one.com', $menu['items'][0]['title'] );
	}

	public function test_add_post_item_links_existing_content() {
		$menu_id = $this->create_menu();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page', 'post_title' => 'About' ) );

		$added = Onecom_Abilities_Menus::add_menu_item(
			array(
				'menu_id' => $menu_id,
				'post_id' => $page_id,
			)
		);

		$this->assertTrue( $added['success'] );
		$this->assertSame( 'post_type', $added['item']['type'] );
		$this->assertSame( $page_id, $added['item']['object_id'] );
	}

	public function test_add_menu_item_requires_target() {
		$menu_id = $this->create_menu();
		$result  = Onecom_Abilities_Menus::add_menu_item( array( 'menu_id' => $menu_id ) );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_invalid_menu_item', $result['code'] );
	}

	public function test_update_menu_item_changes_only_provided_fields() {
		$menu_id = $this->create_menu();
		$added   = Onecom_Abilities_Menus::add_menu_item(
			array(
				'menu_id' => $menu_id,
				'title'   => 'Old title',
				'url'     => 'https://example.com/',
			)
		);

		$edited = Onecom_Abilities_Menus::update_menu_item(
			array(
				'item_id' => $added['item']['id'],
				'title'   => 'New title',
			)
		);

		$this->assertTrue( $edited['success'] );
		$this->assertSame( 'New title', $edited['item']['title'] );
		$this->assertSame( 'https://example.com/', $edited['item']['url'] );
	}

	public function test_remove_menu_item() {
		$menu_id = $this->create_menu();
		$added   = Onecom_Abilities_Menus::add_menu_item(
			array(
				'menu_id' => $menu_id,
				'title'   => 'temp',
				'url'     => 'https://example.com/',
			)
		);

		$removed = Onecom_Abilities_Menus::remove_menu_item( array( 'item_id' => $added['item']['id'] ) );
		$this->assertTrue( $removed['success'] );

		$menu = Onecom_Abilities_Menus::get_menu( array( 'menu_id' => $menu_id ) );
		$this->assertCount( 0, $menu['items'] );
	}

	public function test_assign_and_clear_menu_location() {
		register_nav_menu( 'onecom-test-location', 'Test location' );
		$menu_id = $this->create_menu();

		$assigned = Onecom_Abilities_Menus::assign_menu_location(
			array(
				'menu_id'  => $menu_id,
				'location' => 'onecom-test-location',
			)
		);
		$this->assertTrue( $assigned['success'] );
		$this->assertSame( $menu_id, (int) get_nav_menu_locations()['onecom-test-location'] );

		$cleared = Onecom_Abilities_Menus::assign_menu_location(
			array(
				'menu_id'  => 0,
				'location' => 'onecom-test-location',
			)
		);
		$this->assertTrue( $cleared['success'] );
		$this->assertArrayNotHasKey( 'onecom-test-location', get_nav_menu_locations() );
	}

	public function test_assign_rejects_unknown_location() {
		$menu_id = $this->create_menu();
		$result  = Onecom_Abilities_Menus::assign_menu_location(
			array(
				'menu_id'  => $menu_id,
				'location' => 'does-not-exist',
			)
		);

		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_unknown_menu_location', $result['code'] );
	}

	public function test_delete_menu() {
		$menu_id = $this->create_menu( 'Doomed' );

		$deleted = Onecom_Abilities_Menus::delete_menu( array( 'menu_id' => $menu_id ) );
		$this->assertTrue( $deleted['success'] );
		$this->assertFalse( wp_get_nav_menu_object( $menu_id ) );
	}

	public function test_menu_not_found_errors() {
		$this->assertSame( 'onecom_menu_not_found', Onecom_Abilities_Menus::get_menu( array( 'menu_id' => 999999 ) )['code'] );
		$this->assertSame( 'onecom_menu_not_found', Onecom_Abilities_Menus::delete_menu( array( 'menu_id' => 999999 ) )['code'] );
		$this->assertSame( 'onecom_menu_item_not_found', Onecom_Abilities_Menus::remove_menu_item( array( 'item_id' => 999999 ) )['code'] );
	}
}
