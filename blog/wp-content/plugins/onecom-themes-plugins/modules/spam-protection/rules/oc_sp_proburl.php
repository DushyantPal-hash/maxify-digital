<?php
class GroupOne_SpProburl {

	public function execute(
		&$sp_options = array(),
		&$oc_post = array()
	) {
		$url = GroupOne_Sp::oc_get_spam_url();

		// WPIN-8909: 'exploit-urls' is populated from the middleware API
		// (groupone_sp_restore_data()), so it is legitimately absent before the
		// first successful fetch and non-array whenever that fetch failed.
		// Nothing to probe for in either case. oc-sp-blocked-lists.php already
		// reads this key the same defensive way.
		$exploit_urls = $sp_options['exploit-urls'] ?? array();
		if ( ! is_array( $exploit_urls ) ) {
			return false;
		}

		foreach ( $exploit_urls as $exploits ) {

			if ( strpos( $url, $exploits ) !== false ) {

				return __( '404 exploit probing detected!', 'onecom-sp' ) . ': ' . $url;
			}
		}

		return false;
	}
}
