<?php

/**
 * Class GroupOne_ALP
 * Performs tests related to GroupOne_ALP:
 */
class Test_GroupOne_ALP extends WP_UnitTestCase
{
	public $alp;

	// test with default data after activation
    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        //code here
        parent::tearDown();
    }

	// validate if appropricate callback/hook available on init hook if alp enabed.
	public function test_login_masking_cases()
	{
		// Case 1: Enable ALP for all - admin_login_redirection hook validate
		update_site_option('onecom_login_masking', 1);
		$this->alp = new GroupOne_ALP();
		$this->alp->login_masking();
		$this->assertNotFalse(has_action('wp_login', [$this->alp, 'admin_login_redirection']));

		/**
		 * Case 2: Enable ALP for all - admin_login_redirection hook validate
		 * Initially, login_init is not registered, but should get registered with login_masking();
		 **/
		// Initially, login_init is not registered, so false
		$this->assertEquals(false, has_action('login_init', [$this->alp, 'login_redirection']));
		update_site_option('onecom_login_masking', 2);
		$this->alp = new GroupOne_ALP();
		$this->alp->login_masking();
		// validate if login_init hook is registered
		$this->assertEquals(true, has_action('login_init', [$this->alp, 'login_redirection']));

		// Case 3: User is already logged-in
		// Set logged in user
		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

		// This filter will be used to validate redirection and halt redirection by setting location empty
		add_filter( 'wp_redirect', [$this, 'groupone_validate_login_redirect'], 999, 2 );

		update_site_option('onecom_login_masking', 1);
		$this->alp = new GroupOne_ALP();
		$this->alp->login_masking();

		// Remove filter so that it does not affect other redirections
		remove_filter( 'wp_redirect', [$this, 'dummy_remote_cc_activate_api_reponse'], 10, 2 );
	}

	// Test login redirection with user already logged-in
	public function test_login_redirection(){
		// Set logged in user
		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

		// This filter will be used to validate redirection and halt redirection by setting location empty
		add_filter( 'wp_redirect', [$this, 'groupone_validate_login_redirect'], 999, 2 );

		/**
		 * We need to wrap in try, becuase we are going to terminate it by throwing exeption via above filter,
		 ** Otherwise, redirect (or exit; after redirect if location is bypassed by making it empty) terminates script
		 **/
		try {
			$this->alp = new GroupOne_ALP();
			$this->alp->login_redirection();
        } catch (\Exception $e) {
            return;
        }
		// Remove filter so that it does not affect other redirections
		remove_filter( 'wp_redirect', [$this, 'dummy_remote_cc_activate_api_reponse'], 10, 2 );
	}

	// Test login redirection with user already logged-in
	public function test_login_redirection_non_login(){
		// This filter will be used to validate redirection and halt redirection by setting location empty
		add_filter( 'wp_redirect', [$this, 'groupone_validate_login_redirect'], 999, 2 );

		/**
		 * We need to wrap in try, becuase we are going to terminate it by throwing exeption via above filter,
		 ** Otherwise, redirect (or exit; after redirect if location is bypassed by making it empty) terminates script
		 **/
		try {
			$this->alp = new GroupOne_ALP();
			$this->alp->login_redirection();
        } catch (\Exception $e) {
            return;
        }
		// Remove filter so that it does not affect other redirections
		remove_filter( 'wp_redirect', [$this, 'dummy_remote_cc_activate_api_reponse'], 10, 2 );
	}

	// Hook into redirect, validate, and exit by throwing exception
	public function groupone_validate_login_redirect($location, $status){
		// Login redirect location should be admin url
		$this->assertEquals(admin_url(), $location);
		// throw exception to terminate the ALP redirection code execution
		throw new Exception("Exception");
	}

	// Validate flow: admin login redirection > redirect_to_cp() > wp_redirect
	public function test_admin_login_redirection(){
		// Set logged in user

		// This filter will be used to validate redirection and halt redirection by setting location empty
		add_filter( 'wp_redirect', [$this, 'groupone_validate_admin_login_redirect'], 999, 2 );
		/**
		 * We need to wrap in try, becuase we are going to terminate it by throwing exeption via above filter,
		 ** Otherwise, redirect (or exit; after redirect if location is bypassed by making it empty) terminates script
		 **/
		$author_obj = get_user_by('id', 1);
		try {
			$this->alp = new GroupOne_ALP();
			$this->alp->admin_login_redirection($author_obj->data->user_login, $author_obj);
        } catch (\Exception $e) {
            return;
        }

		// Remove filter so that it does not affect other redirections
		remove_filter( 'wp_redirect', [$this, 'groupone_validate_admin_login_redirect'], 10, 2 );
	}

	// Validate flow: admin login return immediately if onecom-auth set
	public function test_admin_login_redirection_auth_set(){
		$_GET['onecom-auth'] = "xyz123";
		$this->alp = new GroupOne_ALP();
		$status = $this->alp->admin_login_redirection('', '');
		// return immedaitely NULL and so no user login executed
		$this->assertNULL($status);
		$this->assertEquals(false, is_user_logged_in());
	}

	// Hook into redirect, validate, and exit by throwing exception
	public function groupone_validate_admin_login_redirect($location, $status){
		// validate user has logged in and redirection location
		$this->assertTrue(is_user_logged_in());
		$this->assertEquals(admin_url(), GROUPONE_CP_LOGIN_URL);
		// throw exception to terminate the ALP redirection code execution
		throw new Exception("Exception");
	}

	// Hook into init and validate callback registered
	public function test_validate_init_hook(){
		$this->alp = new GroupOne_ALP();
		$this->alp->init();
		$this->assertNotFalse(has_action('init', [$this->alp, 'login_masking']));
	}
}
