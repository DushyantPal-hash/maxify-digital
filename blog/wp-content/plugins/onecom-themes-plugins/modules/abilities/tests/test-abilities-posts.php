<?php
/**
 * Posts & pages ability tests.
 *
 * @package Onecom_Themes_Plugins
 */

class Test_Onecom_Abilities_Posts extends WP_UnitTestCase {

	private $admin_id;

	public function set_up() {
		parent::set_up();
		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_id );
	}

	public function test_create_post_defaults_to_draft() {
		$result = Onecom_Abilities_Posts::create_post( array( 'title' => 'MCP created' ) );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 'draft', $result['post']['status'] );
		$this->assertSame( 'post', $result['post']['type'] );
		$this->assertSame( 'MCP created', get_post( $result['post']['id'] )->post_title );
	}

	public function test_create_page_with_publish_status() {
		$result = Onecom_Abilities_Posts::create_post(
			array(
				'post_type' => 'page',
				'title'     => 'MCP page',
				'content'   => '<p>Hello</p>',
				'status'    => 'publish',
			)
		);

		$this->assertTrue( $result['success'] );
		$this->assertSame( 'page', $result['post']['type'] );
		$this->assertSame( 'publish', $result['post']['status'] );
	}

	public function test_create_post_rejects_unsupported_post_type() {
		$result = Onecom_Abilities_Posts::create_post(
			array(
				'post_type' => 'attachment',
				'title'     => 'nope',
			)
		);

		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_invalid_post_type', $result['code'] );
	}

	public function test_contributor_cannot_publish() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'contributor' ) ) );
		$result = Onecom_Abilities_Posts::create_post(
			array(
				'title'  => 'try publish',
				'status' => 'publish',
			)
		);

		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_cannot_publish', $result['code'] );
	}

	public function test_content_is_filtered_for_users_without_unfiltered_html() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'contributor' ) ) );
		$result = Onecom_Abilities_Posts::create_post(
			array(
				'title'   => 'xss attempt',
				'content' => '<p>ok</p><script>alert(1)</script>',
			)
		);

		$this->assertTrue( $result['success'] );
		$content = get_post( $result['post']['id'] )->post_content;
		$this->assertStringNotContainsString( '<script>', $content );
		$this->assertStringContainsString( '<p>ok</p>', $content );
	}

	public function test_get_post_returns_content() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'body text' ) );
		$result  = Onecom_Abilities_Posts::get_post( array( 'post_id' => $post_id ) );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 'body text', $result['post']['content'] );
	}

	public function test_get_post_rejects_non_content_types() {
		$attachment_id = self::factory()->attachment->create();
		$result        = Onecom_Abilities_Posts::get_post( array( 'post_id' => $attachment_id ) );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_post_not_found', $result['code'] );
	}

	public function test_update_post_updates_only_provided_fields() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'original title',
				'post_content' => 'original content',
			)
		);

		$result = Onecom_Abilities_Posts::update_post(
			array(
				'post_id' => $post_id,
				'title'   => 'new title',
			)
		);

		$this->assertTrue( $result['success'] );
		$post = get_post( $post_id );
		$this->assertSame( 'new title', $post->post_title );
		$this->assertSame( 'original content', $post->post_content );
	}

	public function test_find_posts_filters_by_status_and_paginates() {
		self::factory()->post->create_many( 3, array( 'post_status' => 'publish' ) );
		self::factory()->post->create( array( 'post_status' => 'draft' ) );

		$published = Onecom_Abilities_Posts::find_posts( array( 'status' => 'publish', 'per_page' => 2 ) );
		$this->assertTrue( $published['success'] );
		$this->assertSame( 3, $published['total'] );
		$this->assertCount( 2, $published['posts'] );
		$this->assertSame( 2, $published['pages'] );

		$drafts = Onecom_Abilities_Posts::find_posts( array( 'status' => 'draft' ) );
		$this->assertSame( 1, $drafts['total'] );
	}

	public function test_delete_post_trashes_by_default_and_force_deletes() {
		$post_id = self::factory()->post->create();

		$trash = Onecom_Abilities_Posts::delete_post( array( 'post_id' => $post_id ) );
		$this->assertTrue( $trash['success'] );
		$this->assertTrue( $trash['trashed'] );
		$this->assertSame( 'trash', get_post_status( $post_id ) );

		$force = Onecom_Abilities_Posts::delete_post(
			array(
				'post_id' => $post_id,
				'force'   => true,
			)
		);
		$this->assertTrue( $force['success'] );
		$this->assertTrue( $force['deleted'] );
		$this->assertNull( get_post( $post_id ) );
	}
}
