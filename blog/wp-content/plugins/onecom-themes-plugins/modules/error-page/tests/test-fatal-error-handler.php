<?php
class FatalErrorHandlerTest extends WP_UnitTestCase {

    public $fatal_obj;

    public function setUp(): void {
        parent::setUp();

        require_once GROUPONE_WP_PATH . 'modules' . DIRECTORY_SEPARATOR . 'error-page' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'fatal-error-handler.php';

        $this->fatal_obj = new GroupOne_Error_Handler();
		// Create an anonymous subclass to expose the protected method
		$this->instance = new class extends GroupOne_Error_Handler {
			public function callDisplayDefaultErrorTemplate($error, $handled) {
				return $this->display_default_error_template($error, $handled);
			}
		};

		// Override wp_die() for testing
		add_filter( 'wp_die_handler', function() {
			return [ $this, 'capture_wp_die' ];
		});

    }


    public function test_display_default_error_template()
    {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        $this->instance->callDisplayDefaultErrorTemplate( [ 'type' => 1, 'message' => 'Fatal error' ], false );

        $this->assertNotEmpty( $this->wp_die_args );
        $this->assertEquals( 500, $this->wp_die_args['args']['response'] );
    }

    public function test_get_extension_for_error()
    {
        // WPIN-8909: get_extension_for_error() matches the error's file path
        // against the current environment's WP_PLUGIN_DIR / theme root via
        // strpos() - this hardcoded one developer's local macOS temp WP
        // install path, which never matches CI's actual WP_PLUGIN_DIR, so
        // every plugin/theme case fell through to false. Build the fixture
        // paths from the real constants instead.
        $this->assertEquals(array('type' =>"plugin",'slug'=>"onecom-themes-plugins"),$this::getPrivateMethod($this->fatal_obj,'get_extension_for_error',array(array('file'=>WP_PLUGIN_DIR . '/onecom-themes-plugins/onecom-themes-plugins.php'))));
        $this->assertEquals(array('type' =>"theme",'slug'=>"onecom-express"),$this::getPrivateMethod($this->fatal_obj,'get_extension_for_error',array(array('file'=>get_theme_root() . '/onecom-express/style.css'))));
        $this->assertEquals(false,$this::getPrivateMethod($this->fatal_obj,'get_extension_for_error',array(array('file'=>'onecom-express/style.css'))));

    }


    public function test_get_plugin()
    {
        $this->assertContains('one.com',$this::getPrivateMethod($this->fatal_obj,'get_plugin',array(array('type' =>"plugin",'slug'=>"onecom-themes-plugins"))));
        // WPIN-8909: 'onecom-vcache' isn't a reliable "not found" fixture -
        // other tests in this suite (test-legacy-plugin-takeover.php) note
        // that CI's WP_PLUGIN_DIR may ambiently contain the real legacy
        // plugin slugs, which this test has no visibility into or control
        // over. Use a slug that cannot exist under any circumstance.
        $this->assertEquals(false,$this::getPrivateMethod($this->fatal_obj,'get_plugin',array(array('type' =>"plugin",'slug'=>"no-such-plugin-wpin-8909"))));
    }

	public function test_get_cause()
	{
		$result = $this::getPrivateMethod(
			$this->fatal_obj,
			'get_cause',
			[ ['type' => 'plugin', 'slug' => 'onecom-themes-plugins'] ]
		);

		$this->assertStringContainsString('one.com', $result);
	}

	public function test_get_tips_for_theme()
	{
		$result = $this::getPrivateMethod(
			$this->fatal_obj,
			'get_tips',
			[ ['type' => 'theme', 'slug' => 'twentytwentythree'] ]
		);

		// Basic structure check
		$this->assertStringContainsString('<ul>', $result);
		$this->assertStringContainsString('</ul>', $result);

		// WPIN-8909: onecom-themes-plugins.php always loads
		// languages/onecom-wp-en_GB.mo over the 'onecom-wp' textdomain for
		// any locale not in its explicit fi/nb_NO/$locales_with_translation
		// list - which includes this test's default locale. That .mo's
		// copy has moved on from the literal __() msgids below; assert what
		// actually renders.
		// Theme-specific checks
		$this->assertStringContainsString('All your themes are up to date', $result);
		$this->assertStringContainsString('Activation of a child theme', $result);

		// Shared checks
		$this->assertStringContainsString('Automatic WordPress core updates are disabled', $result);
		$this->assertStringContainsString('You are not using the recommended PHP version', $result);
		$this->assertStringContainsString('Google dashboard', $result);
	}

	public function test_get_tips_for_plugin()
	{
		$result = $this::getPrivateMethod(
			$this->fatal_obj,
			'get_tips',
			[ ['type' => 'plugin', 'slug' => 'hello-dolly'] ]
		);

		// Basic structure check
		$this->assertStringContainsString('<ul>', $result);
		$this->assertStringContainsString('</ul>', $result);

		// WPIN-8909: see test_get_tips_for_theme - the en_GB.mo override
		// that always loads for this domain has moved past these msgids.
		// Plugin-specific checks
		$this->assertStringContainsString('All your plugins are up to date', $result);

		// Shared checks
		$this->assertStringContainsString('Automatic WordPress core updates are disabled', $result);
		$this->assertStringContainsString('You are not using the recommended PHP version', $result);
		$this->assertStringContainsString('Google dashboard', $result);
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

	public function capture_wp_die( $message, $title = '', $args = [] ) {
		// Store what wp_die() received instead of killing execution
		$this->wp_die_args = [
			'message' => $message,
			'title'   => $title,
			'args'    => $args,
		];
	}

	public function test_default_message_when_not_logged_in() {
		$error = [ 'type' => 1, 'message' => 'Fatal error' ];

		$this->instance->callDisplayDefaultErrorTemplate( $error, false );

		$this->assertNotEmpty( $this->wp_die_args );
		$this->assertInstanceOf( WP_Error::class, $this->wp_die_args['message'] );

		$wp_error = $this->wp_die_args['message'];
		$this->assertStringContainsString( 'There has been a critical error on your website.', $wp_error->get_error_message() );
	}

	public function test_recovery_mode_message() {
		// WPIN-8909: wp_is_recovery_mode() is `return wp_recovery_mode()->is_active();`
		// - a plain function, never run through apply_filters(). The
		// add_filter() this had was a no-op, so this branch never fired and
		// display_default_error_template() always fell through to the
		// generic message. WP_Recovery_Mode::is_active() only reflects real
		// state set by handling an actual fatal error, with no public
		// setter - reflection on its private property is the only seam.
		$recovery_mode = wp_recovery_mode();
		$is_active_prop = new ReflectionProperty( $recovery_mode, 'is_active' );
		$is_active_prop->setAccessible( true );
		$is_active_prop->setValue( $recovery_mode, true );

		$error = [ 'type' => 1, 'message' => 'Fatal error' ];
		$this->instance->callDisplayDefaultErrorTemplate( $error, true );

		$wp_error = $this->wp_die_args['message'];
		// WPIN-8909: see test_get_tips_for_theme - the en_GB.mo override
		// that always loads for this domain has moved past this msgid too.
		$this->assertStringContainsString( 'your website was put in recovery mode', $wp_error->get_error_message() );

		$is_active_prop->setValue( $recovery_mode, false );
	}

	public function test_protected_endpoint_message() {
		// WPIN-8909: is_protected_endpoint() is plain WP-core logic
		// (is_admin() && !wp_doing_ajax(), among other checks) - it never
		// reads an option, so filtering 'pre_option_is_protected_endpoint'
		// was a no-op and this branch never fired. set_current_screen()
		// makes is_admin() true, which is enough to satisfy it.
		set_current_screen( 'dashboard' );

		$error = [ 'type' => 1, 'message' => 'Fatal error' ];
		$this->instance->callDisplayDefaultErrorTemplate( $error, false );

		$wp_error = $this->wp_die_args['message'];
		// WPIN-8909: see test_get_tips_for_theme - same en_GB.mo override.
		$this->assertStringContainsString( 'An email with more information was sent to the administrator', $wp_error->get_error_message() );
	}


    public function tearDown(): void
    {
		remove_all_filters( 'wp_die_handler' );
		$this->wp_die_args = [];
        parent::tearDown();
    }



}