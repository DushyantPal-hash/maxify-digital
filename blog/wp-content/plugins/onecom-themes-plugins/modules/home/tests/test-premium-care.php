<?php
// Test data consent modal, notice and misc functions
class Test_Premium_Care extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
    }

    // test data consent modal - valid html dom
    public function test_premium_care_section()
    {

        ob_start();

		include GROUPONE_WP_PATH . 'modules/home/templates/premium-wp-care-modal.php';
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

	// test premium care api response with mock success
	public function test_oc_request_premium_care_success()
	{

		$_POST['premium_wp_request'] = 1;
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'pre_http_request', [ $this, 'mock_premium_wp_care_success_response' ], 10, 3 );
		add_filter( 'wp_die_ajax_handler', [ $this,'wp_ajax_print_handler_filter']);
		ob_start();
		$welcome_modal_html = oc_request_premium_care();
		$output = ob_get_clean(); // Capture wp_send_json output

		// Remove filter after test to avoid polluting other tests
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'pre_http_request', [ $this, 'mock_premium_wp_care_success_response' ], 10 );
		remove_filter( 'wp_die_ajax_handler', [ $this, 'wp_ajax_print_handler_filter' ] );


		// Assert that your fake JSON response is there
		$this->assertJson($output);
		$data = json_decode($output, true);
		$this->assertTrue($data['success']);
	}

	// test premium care api response with mock success
	// @todo Improvement - This tests fails due to both wp_send_json_error adds their response as our wp_die_ajax_handler does not let it die() after first wp_send_json_error in oc_request_premium_care()
	/*public function test_oc_request_premium_care_error_1()
	{

		$_POST['premium_wp_request'] = 1;
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'pre_http_request', [ $this, 'mock_http_failure' ], 10, 3 );
		add_filter( 'wp_die_ajax_handler', [ $this,'wp_ajax_print_handler_filter']);
		ob_start();
		$welcome_modal_html = oc_request_premium_care();
		$output = ob_get_clean(); // Capture wp_send_json output

		// Remove filter after test to avoid polluting other tests
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'pre_http_request', [ $this, 'mock_http_failure' ], 10 );
		remove_filter( 'wp_die_ajax_handler', [ $this, 'wp_ajax_print_handler_filter' ] );

		// Assert mock json response
		$this->assertJson($output);
		$data = json_decode($output, true);
		$this->assertFalse($data['success']);
	}*/

	// Return dummy JSON http failure response for wp_remote_post
	public function mock_http_failure() {
		return new WP_Error( 'http_request_failed', 'Simulated error response' );
	}

	// test premium care api response with mock success
	public function test_oc_request_premium_care_error_2()
	{

		$_POST['premium_wp_request'] = 1;
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'pre_http_request', [ $this, 'mock_http_error_response' ], 10, 3 );
		add_filter( 'wp_die_ajax_handler', [ $this,'wp_ajax_print_handler_filter']);
		ob_start();
		$welcome_modal_html = oc_request_premium_care();
		$output = ob_get_clean(); // Capture wp_send_json output

		// Remove filter after test to avoid polluting other tests
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'pre_http_request', [ $this, 'mock_http_error_response' ], 10 );
		remove_filter( 'wp_die_ajax_handler', [ $this, 'wp_ajax_print_handler_filter' ] );


		// Assert that your fake JSON response is there
		$this->assertJson($output);
		$data = json_decode($output, true);
		$this->assertFalse($data['success']);
	}

	// Return dummy JSON error response for wp_remote_post
	public function mock_http_error_response() {

		return array(
			'headers'  => array(),
			'body'     => '{"success":false}',
			'response' => array(
				'code'    => 500
			)
		);
	}

	// test premium care addon response
	public function test_premium_wp_care_addon_success()
	{
		// set site transient with feature subscription response
		$feature_data = array ( 'data' =>  array ( 'addonExists' => true, 'invoicedUntil' => '2028-10-28T00:00:00.000+00:00', 'product' => 'PREMIUM_WORDPRESS_CARE', 'source' => 'PURCHASED'), 'error' => NULL, 'success' => true );
		// groupone_premium_wp_care_addon_info() reads 'onecom_pwpc_addon_info',
		// not 'onecom_premium_wp_care_addon_info' - the wrong key meant this
		// transient was never found, falling through to a live API call.
		// See WPIN-8909.
		set_site_transient('onecom_pwpc_addon_info', $feature_data, 12 * HOUR_IN_SECONDS);

		ob_start();
		$status = groupone_is_premium_wp_care_addon_purchased();
		ob_end_clean();

		$this->assertTrue($status);
	}

	// test premium care addon error response if domain is missing
	public function test_premium_wp_care_addon_error()
	{
		// Save original value (if exists)
		$originalDomain = $_SERVER['ONECOM_DOMAIN_NAME'] ?? null;
		// Temporarily set it empty
		$_SERVER['ONECOM_DOMAIN_NAME'] = '';

		ob_start();
		$status = groupone_premium_wp_care_addon_info('force');
		ob_end_clean();

		// Restore original value
		if ($originalDomain !== null) {
			$_SERVER['ONECOM_DOMAIN_NAME'] = $originalDomain;
		} else {
			unset($_SERVER['ONECOM_DOMAIN_NAME']);
		}

		$this->assertFalse($status['success']);
	}


	// Return dummy JSON success response for wp_remote_post
	public function mock_premium_wp_care_success_response() {

		return array(
			'headers'  => array(),
			'body'     => '{"success":true}',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	function wp_ajax_print_handler_filter() {
		return [ $this, 'wp_ajax_print_handler' ];
	}

	function wp_ajax_print_handler( $message ) {
		echo $message;
	}
}