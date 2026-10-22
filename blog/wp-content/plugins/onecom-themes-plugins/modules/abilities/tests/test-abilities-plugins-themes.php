<?php
/**
 * Plugin & theme ability tests.
 *
 * Network operations (wp.org installs, updates) are not exercised here — these
 * tests cover the read paths, input validation and the one.com policy guards.
 *
 * @package Onecom_Themes_Plugins
 */

class Test_Onecom_Abilities_Plugins_Themes extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function test_list_plugins_returns_installed_plugins() {
		$result = Onecom_Abilities_Plugins::list_plugins( array() );

		$this->assertTrue( $result['success'] );
		$this->assertIsArray( $result['plugins'] );
		$this->assertSame( count( $result['plugins'] ), $result['total'] );

		Onecom_Abilities_Plugins::load_admin_dependencies();
		$this->assertSame( count( get_plugins() ), $result['total'] );
	}

	public function test_list_plugins_status_filters_are_disjoint() {
		$all      = Onecom_Abilities_Plugins::list_plugins( array() );
		$active   = Onecom_Abilities_Plugins::list_plugins( array( 'status' => 'active' ) );
		$inactive = Onecom_Abilities_Plugins::list_plugins( array( 'status' => 'inactive' ) );

		$this->assertSame( $all['total'], $active['total'] + $inactive['total'] );
	}

	public function test_plugin_file_validation_rejects_traversal_and_unknown() {
		foreach ( array( '../../wp-config.php', '/etc/passwd', '' ) as $bad ) {
			$result = Onecom_Abilities_Plugins::get_plugin( array( 'plugin_file' => $bad ) );
			$this->assertFalse( $result['success'] );
			$this->assertSame( 'onecom_invalid_plugin_file', $result['code'], "'$bad' must be rejected as invalid" );
		}

		$unknown = Onecom_Abilities_Plugins::get_plugin( array( 'plugin_file' => 'not-installed/not-installed.php' ) );
		$this->assertFalse( $unknown['success'] );
		$this->assertSame( 'onecom_plugin_not_found', $unknown['code'] );
	}

	public function test_onecom_platform_plugins_are_protected() {
		$this->assertTrue( Onecom_Abilities_Plugins::is_protected( 'onecom-themes-plugins/onecom-themes-plugins.php' ) );
		$this->assertTrue( Onecom_Abilities_Plugins::is_protected( 'onecom-vcache/vcaching.php' ) );
		$this->assertFalse( Onecom_Abilities_Plugins::is_protected( 'akismet/akismet.php' ) );
	}

	public function test_protected_list_is_filterable() {
		add_filter( 'onecom_abilities_protected_plugins', static fn( $dirs ) => array_merge( $dirs, array( 'my-guarded' ) ) );
		$this->assertTrue( Onecom_Abilities_Plugins::is_protected( 'my-guarded/my-guarded.php' ) );
	}

	public function test_deactivate_refuses_protected_plugin() {
		Onecom_Abilities_Plugins::load_admin_dependencies();
		$installed = array_keys( get_plugins() );
		if ( empty( $installed ) ) {
			$this->markTestSkipped( 'No plugins installed in the test environment.' );
		}

		// Mark a really-installed plugin as protected so resolve_plugin_file passes.
		$victim = $installed[0];
		$dir    = dirname( $victim );
		add_filter( 'onecom_abilities_protected_plugins', static fn( $dirs ) => array_merge( $dirs, array( $dir ) ) );

		$result = Onecom_Abilities_Plugins::deactivate_plugin( array( 'plugin_file' => $victim ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_plugin_protected', $result['code'] );
	}

	public function test_install_plugin_requires_slug() {
		$result = Onecom_Abilities_Plugins::install_plugin( array( 'slug' => '' ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_slug_required', $result['code'] );
	}

	public function test_update_plugin_without_available_update_fails() {
		Onecom_Abilities_Plugins::load_admin_dependencies();
		$installed = array_keys( get_plugins() );
		if ( empty( $installed ) ) {
			$this->markTestSkipped( 'No plugins installed in the test environment.' );
		}

		// update_plugin now refreshes (wp_update_plugins) when the transient
		// lacks the entry; stub that refresh to an empty response so the test
		// stays hermetic (no wp.org call) and deterministically has no update.
		$empty = (object) array( 'response' => array(), 'no_update' => array() );
		add_filter( 'pre_site_transient_update_plugins', static fn() => $empty );

		delete_site_transient( 'update_plugins' );
		$result = Onecom_Abilities_Plugins::update_plugin( array( 'plugin_file' => $installed[0] ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_no_update', $result['code'] );

		remove_all_filters( 'pre_site_transient_update_plugins' );
	}

	public function test_update_theme_without_available_update_fails() {
		// Same hermetic stub for the theme path's refresh-and-retry.
		$empty = (object) array( 'response' => array(), 'no_update' => array() );
		add_filter( 'pre_site_transient_update_themes', static fn() => $empty );

		delete_site_transient( 'update_themes' );
		$result = Onecom_Abilities_Themes::update_theme( array( 'stylesheet' => get_stylesheet() ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_no_update', $result['code'] );

		remove_all_filters( 'pre_site_transient_update_themes' );
	}

	public function test_list_themes_flags_the_active_theme() {
		$result = Onecom_Abilities_Themes::list_themes( array() );

		$this->assertTrue( $result['success'] );
		$this->assertSame( count( $result['themes'] ), $result['total'] );

		$active = array_values( array_filter( $result['themes'], static fn( $theme ) => $theme['active'] ) );
		$this->assertCount( 1, $active );
		$this->assertSame( get_stylesheet(), $active[0]['stylesheet'] );
	}

	public function test_get_theme_defaults_to_active_theme() {
		$result = Onecom_Abilities_Themes::get_theme( array() );

		$this->assertTrue( $result['success'] );
		$this->assertSame( get_stylesheet(), $result['theme']['stylesheet'] );
	}

	public function test_get_theme_not_found() {
		$result = Onecom_Abilities_Themes::get_theme( array( 'stylesheet' => 'no-such-theme' ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_theme_not_found', $result['code'] );
	}

	public function test_switch_theme_rejects_unknown_stylesheet() {
		$result = Onecom_Abilities_Themes::switch_theme( array( 'stylesheet' => 'no-such-theme' ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_theme_not_found', $result['code'] );
	}

	public function test_switch_theme_to_current_is_a_noop_success() {
		$result = Onecom_Abilities_Themes::switch_theme( array( 'stylesheet' => get_stylesheet() ) );
		$this->assertTrue( $result['success'] );
		$this->assertSame( get_stylesheet(), $result['stylesheet'] );
	}

	public function test_install_theme_requires_slug() {
		$result = Onecom_Abilities_Themes::install_theme( array( 'slug' => '' ) );
		$this->assertFalse( $result['success'] );
		$this->assertSame( 'onecom_slug_required', $result['code'] );
	}
}
