<?php
// Test data consent modal, notice and misc functions
class Test_Data_Consent extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
    }

    // test data consent modal - valid html dom
    public function test_data_consent_modal()
    {

        ob_start();

		include GROUPONE_WP_PATH . 'modules/home/templates/data-consent-modal.php';
        $output_html = ob_get_contents();
        ob_end_clean();

        // Load html (include utf8 if needed)
        $xml = new SimpleXMLElement("<body>".$output_html."</body>");

        // Validate html document
        $nonDocumentErrors = $xml->{'non-document-error'};

        $errors = $xml->error;
        if (count($nonDocumentErrors) > 0) {
            // Indeterminate
            $this->markTestIncomplete();
        } elseif (count($errors) > 0) {
            // Invalid html
            $this->fail("HTML output did not validate.");
        } else {
			// Assert true if no HTML DOM error
            $this->assertTrue(TRUE);
        }

    }


	// test positive case where consent banner should be injected in admin_footer
	public function test_consent_modal_injected_into_admin_footer() {

		// Setup required conditions
		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

		// New onboarding + no consent status exists + HM page
		set_current_screen('one-com_page_onecom-wp-health-monitor');
		delete_site_option( 'onecom_data_consent_status' );
		update_site_option( 'onecom_installation_timestamp', '1736968504' );

		// Test as Managed WordPress user
		$features_transient = array (
			0 => 'ONE_CLICK_INSTALL',
			1 => 'STAGING_ENV',
			2 => 'STANDARD_THEMES',
			3 => 'PERFORMANCE_CACHE',
			4 => 'FREE_MIGRATION',
			5 => 'MWP_ADDON',
		);
		set_site_transient('oc_premi_flag', $features_transient, 12 * HOUR_IN_SECONDS);

		// Start output buffering to capture the output
		ob_start();

		// Simulate the WordPress admin_footer action
		do_action( 'admin_footer' );

		// Get the captured output and clean the buffer
		$output = ob_get_clean();

		// Now assert that some expected HTML is present
		$this->assertStringContainsString(
			'oc-data-consent-banner',
			$output,
			'Consent banner was not injected into admin footer.'
		);

		// Optionally check more selectors or contents
		$this->assertStringContainsString(
			'<div',
			$output,
			'Expected HTML structure not found in admin footer output.'
		);
	}

	// test negative case where consent banner should not be injected in admin_footer
	public function test_consent_modal_not_injected_into_admin_footer() {

		// Setup required conditions
		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

		set_current_screen('one-com_page_onecom-wp-health-monitor');

		// Set consent status already, so consent banner template should not get included
		update_site_option( 'onecom_data_consent_status', '1' );

		// Test as Managed WordPress user
		$features_transient = array (
			0 => 'ONE_CLICK_INSTALL',
			1 => 'STAGING_ENV',
			2 => 'STANDARD_THEMES',
			3 => 'PERFORMANCE_CACHE',
			4 => 'FREE_MIGRATION',
			5 => 'MWP_ADDON',
		);
		set_site_transient('oc_premi_flag', $features_transient, 12 * HOUR_IN_SECONDS);

		// Start output buffering to capture the output
		ob_start();

		// Simulate the WordPress admin_footer action
		do_action( 'admin_footer' );

		// Get the captured output and clean the buffer
		$output = ob_get_clean();

		// Now assert that some expected HTML (main id oc-data-consent-banner) is present
		$this->assertStringNotContainsString(
			'oc-data-consent-banner',
			$output,
			'Consent banner was injected into admin footer.'
		);

	}
}