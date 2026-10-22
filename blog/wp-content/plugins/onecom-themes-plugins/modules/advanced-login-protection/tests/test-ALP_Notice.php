<?php

/**
 * Class GroupOne_ALP_Notice
 * Performs tests related to GroupOne_ALP_Notice:
 */
class Test_GroupOne_ALP_Notice extends WP_UnitTestCase
{
	public $alp_notice;

	// test with default data after activation
    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        //code here
        parent::tearDown();
    }

	// validate alp_admin_masking_notice_html dom
	public function test_admin_masking_notice_dom()
	{
		$this->alp_notice = new GroupOne_ALP_Notice();
		$this->alp_notice->init();
		ob_start();
		$this->alp_notice->admin_masking_notice();
		$alp_admin_masking_notice_html = ob_get_contents();
		ob_end_clean();

        // Replacing here, as we cannot replace '&' in GROUPONE_CP_LOGIN_URL. It cause redirection issues in ALP
        $alp_admin_masking_notice_html = str_replace("&targetUrl", " &amp;targetUrl ", $alp_admin_masking_notice_html);

		// Load html (include utf8 if needed)
		$xml = new SimpleXMLElement("<div>".$alp_admin_masking_notice_html."</div>");

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
			$this->assertTrue(TRUE);
		}
	}

    // validate all_masking_notice dom
	public function test_all_masking_notice_dom()
	{
		$this->alp_notice = new GroupOne_ALP_Notice();
		$this->alp_notice->init();
		ob_start();
		$this->alp_notice->all_masking_notice();
		$alp_admin_masking_notice_html = ob_get_contents();
		ob_end_clean();

        // Replacing here, as we cannot replace '&' in GROUPONE_CP_LOGIN_URL. It cause redirection issues in ALP
        $alp_admin_masking_notice_html = str_replace("&targetUrl", " &amp;targetUrl ", $alp_admin_masking_notice_html);

		// Load html (include utf8 if needed)
		$xml = new SimpleXMLElement("<div>".$alp_admin_masking_notice_html."</div>");

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
			$this->assertTrue(TRUE);
		}
	}
}
