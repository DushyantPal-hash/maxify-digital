<?php

class GroupOne_SpUrlshort {

	public function execute(
		&$sp_options = array(),
		&$oc_post = array()
	) {

		// WPIN-8957: 'url-shortners' only exists once the Spam Protection settings
		// have been saved. oc_sp_diagnostics() runs every rule regardless of
		// whether its check is enabled, so on a never-configured site this read
		// warned twice - once for the missing key, once for foreach() on null.
		// Falling back to an empty list matches the sibling guard in
		// oc_sp_proburl.php and keeps the outcome identical: no list to match
		// against, so nothing is flagged.
		$url_shorteners = $sp_options['url-shortners'] ?? array();

		foreach ( $oc_post as $key => $post ) {
			if ( ! empty( $post ) ) {
				foreach ( $url_shorteners as $shortener ) {

					// checks redefined for better detection of url shortners in the post fields
					if ( stripos( $post, $shortener ) !== false
						&& ( stripos( $post, $shortener ) == 0
							|| substr( $post, stripos( $post, $shortener ) - 1, 1 ) == ' '
							|| substr( $post, stripos( $post, $shortener ) - 1, 1 ) == '/'
							|| substr( $post, stripos( $post, $shortener ) - 1, 1 ) == '@'
							|| substr( $post, stripos( $post, $shortener ) - 1, 1 ) == '.' ) ) {

						return "URL shortner detected: $shortener in $key";
					}
				}
			}
		}

		return false;
	}
}
