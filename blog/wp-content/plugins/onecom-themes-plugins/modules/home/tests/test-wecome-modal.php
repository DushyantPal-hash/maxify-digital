<?php
// Test landing page - welcome modal
class Test_Welcome_Modal extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
    }

    // test landing page welcome modal - valid html dom
    public function test_welcome_modal()
    {

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

        ob_start();
		$_GET['onboarding-flow'] = 'oci-wp-install';
		$_GET['page'] = 'onecom-home';

		include GROUPONE_WP_PATH . 'modules/home/templates/welcome-modal.php';
        $welcome_modal_html = ob_get_contents();
        ob_end_clean();

		// & replaced by &amp; (Might come from translations as well) - Required to support XML DOM validation
		$welcome_modal_html = str_replace(" & ", " &amp; ", $welcome_modal_html);

        // Load html (include utf8 if needed)
        $xml = new SimpleXMLElement("<body>".$welcome_modal_html."</body>");

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

	// test landing page welcome modal - demo_import, fast track and default case
	public function test_welcome_modal_scenarious()
	{

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

		// Test for modal content for demo scenario
		ob_start();
		$_GET['onboarding-flow'] = 'demo_import';
		$_GET['page'] = 'onecom-home';
		include GROUPONE_WP_PATH . 'modules/home/templates/welcome-modal.php';
		$welcome_modal_html = ob_get_contents();
		ob_end_clean();
		$this->assertStringContainsString('demo', $welcome_modal_html);

		// Test for modal content for fast track
		ob_start();
		$_GET['onboarding-flow'] = 'fast_track';
		$_GET['page'] = 'onecom-home';
		include GROUPONE_WP_PATH . 'modules/home/templates/welcome-modal.php';
		$welcome_modal_html = ob_get_contents();
		ob_end_clean();
		$this->assertStringContainsString('theme', $welcome_modal_html);

		// Test for modal content for default/else case
		ob_start();
		$_GET['onboarding-flow'] = 'non-existence';
		$_GET['page'] = 'onecom-home';
		include GROUPONE_WP_PATH . 'modules/home/templates/welcome-modal.php';
		$welcome_modal_html = ob_get_contents();
		ob_end_clean();
		$this->assertStringContainsString('welcome-modal.svg', $welcome_modal_html);
	}
}