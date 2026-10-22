<?php

/**
 * Class GroupOne_ALP_Reset_Password
 * Performs tests related to GroupOne_ALP_Reset_Password:
 */
class Test_GroupOne_ALP_Reset_Password extends WP_UnitTestCase
{
	public $alp_rp;

	// test with default data after activation
    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        //code here
        parent::tearDown();
    }

	// validate reset_password_mail actions
	public function test_password_reset_cases()
	{
		// Consider ALP flag is activated by installer
		update_site_option('onecom_alp_disable_mail', 1);

		$this->alp_rp = new GroupOne_ALP_Reset_Password();
		$this->alp_rp->reset_password_mail();
		$this->assertEquals(false, get_site_option('onecom_alp_disable_mail', 0));
	}

	// validate if actions are regisered with
	public function test_init_hooks()
	{
		$this->alp_notice = new GroupOne_ALP_Reset_Password();
		$this->alp_notice->init();
		$this->assertNotFalse(has_action('disable_onecom_alp', [$this->alp_notice, 'reset_password_mail']));
		$this->assertNotFalse(has_action('after_password_reset', [$this->alp_notice, 'disable_flag_password_reset']));
		$this->assertNotFalse(has_action('profile_update', [$this->alp_notice, 'disable_flag_profile_update']));
	}
}
