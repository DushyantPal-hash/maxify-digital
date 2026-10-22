<?php
/**
 * Regression test for WPIN-8922.
 *
 * The four templates under modules/home/templates that are require()d on the
 * admin_footer hook must not reference GROUPONE_* constants directly.
 *
 * During a plugin update the request rendering wp-admin was bootstrapped from
 * the PREVIOUS plugin version, which defined ONECOM_WP_URL and never
 * GROUPONE_WP_URL. require_once reads the template from disk as it is NOW, so
 * the old process loads the new template and hits an undefined constant —
 * a fatal dumped into the admin footer right after "Plugin updated
 * successfully." Because the pre-rename release used the ONECOM_* names, that
 * is the upgrade path every existing customer takes when the merged build
 * ships, not just a test-site artefact.
 *
 * A helper function of ours cannot fix this: it would not be defined in that
 * stale process either. The guard therefore has to be inline in each template
 * and use only WordPress core functions.
 *
 * Note that a constant cannot be undefined in PHP, so these tests do not try
 * to simulate the stale process. They pin the two things that can actually
 * regress: that the fallback resolves to the same URL as the constant (a wrong
 * fallback would silently produce broken asset URLs), and that no template
 * reintroduces a bare constant reference.
 */
class Test_Admin_Footer_Template_Constants extends WP_UnitTestCase {

	/**
	 * Templates require()d on admin_footer by modules/home/inc/functions.php.
	 */
	private function admin_footer_templates() {
		return array(
			'welcome-modal.php',
			'data-consent-modal.php',
			'data-consent-banner.php',
			'premium-wp-care-modal.php',
		);
	}

	private function template_path( $file ) {
		return GROUPONE_WP_PATH . 'modules/home/templates/' . $file;
	}

	/**
	 * The inline fallback must resolve to exactly what GROUPONE_WP_URL holds,
	 * otherwise every asset in these modals 404s in the stale-process case.
	 *
	 * This mirrors the expression used in the templates:
	 *   plugin_dir_url( dirname( __DIR__, 3 ) . '/onecom-themes-plugins.php' )
	 * where __DIR__ is modules/home/templates.
	 */
	public function test_fallback_resolves_to_the_same_url_as_the_constant() {
		$template_dir = GROUPONE_WP_PATH . 'modules/home/templates';
		$plugin_root  = dirname( $template_dir, 3 );

		$this->assertFileExists(
			$plugin_root . '/onecom-themes-plugins.php',
			'dirname( __DIR__, 3 ) from modules/home/templates must land on the plugin root.'
		);

		$this->assertSame(
			GROUPONE_WP_URL,
			plugin_dir_url( $plugin_root . '/onecom-themes-plugins.php' ),
			'The inline fallback must produce the same base URL as GROUPONE_WP_URL.'
		);
	}

	/**
	 * Each template may name GROUPONE_WP_URL exactly once as a bare constant:
	 * the `? GROUPONE_WP_URL` arm of its own defined() ternary. Anything beyond
	 * that is an unguarded reference and reintroduces the fatal.
	 *
	 * The `defined( 'GROUPONE_WP_URL' )` occurrence is a quoted string, so it
	 * tokenises as T_CONSTANT_ENCAPSED_STRING and is not counted here. Mentions
	 * inside comments are T_COMMENT and are likewise not counted.
	 */
	public function test_templates_have_no_unguarded_constant_references() {
		foreach ( $this->admin_footer_templates() as $file ) {
			$path = $this->template_path( $file );
			$this->assertFileExists( $path );

			$bare = array();
			foreach ( token_get_all( file_get_contents( $path ) ) as $token ) {
				if ( is_array( $token ) && T_STRING === $token[0] && 0 === strpos( $token[1], 'GROUPONE_' ) ) {
					$bare[] = $token[1];
				}
			}

			$this->assertSame(
				array( 'GROUPONE_WP_URL' ),
				$bare,
				$file . ' must reference GROUPONE_WP_URL exactly once, as the guarded ternary arm. Found: '
				. implode( ', ', $bare ) . '. Use the $groupone_asset_url local instead — this template is '
				. 'loaded on admin_footer and can run under a pre-rename process (WPIN-8922).'
			);
		}
	}

	/**
	 * Every template must actually declare the fallback local before using it,
	 * so a bare $groupone_asset_url can never be undefined at render time.
	 */
	public function test_templates_declare_the_fallback_local() {
		foreach ( $this->admin_footer_templates() as $file ) {
			$contents = file_get_contents( $this->template_path( $file ) );

			$this->assertStringContainsString(
				"defined( 'GROUPONE_WP_URL' )",
				$contents,
				$file . ' must guard the constant with defined().'
			);
			$this->assertStringContainsString(
				'$groupone_asset_url =',
				$contents,
				$file . ' must assign the $groupone_asset_url fallback local.'
			);
		}
	}

	/**
	 * Constants are only half of it. A stale process is missing our *functions*
	 * too, and WPIN-8739 renamed those as well — data-consent-modal.php called
	 * groupone_get_privacy_policy_url(), which a pre-rename process knows only as
	 * oc_get_privacy_policy_url(). Reproduced on 6aug-test1.mwp.1next.one, where
	 * that template still fatalled with "Call to undefined function" after the
	 * constant guard was in place.
	 *
	 * So: any call to a function in our own namespace, from a template loaded on
	 * admin_footer, must be guarded by function_exists() in that same file.
	 */
	public function test_templates_guard_calls_to_our_own_functions() {
		foreach ( $this->admin_footer_templates() as $file ) {
			$path     = $this->template_path( $file );
			$contents = file_get_contents( $path );
			$tokens   = array_values(
				array_filter(
					token_get_all( $contents ),
					function ( $t ) {
						return ! is_array( $t ) || ! in_array( $t[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true );
					}
				)
			);

			foreach ( $tokens as $i => $token ) {
				if ( ! is_array( $token ) || T_STRING !== $token[0] ) {
					continue;
				}
				if ( ! preg_match( '/^(groupone|onecom|oc)_/', $token[1] ) ) {
					continue;
				}
				// Only interested in call sites, not bare identifiers.
				if ( ! isset( $tokens[ $i + 1 ] ) || '(' !== $tokens[ $i + 1 ] ) {
					continue;
				}

				$this->assertStringContainsString(
					"function_exists( '" . $token[1] . "' )",
					$contents,
					$file . ' calls ' . $token[1] . '() without a function_exists() guard. This template is '
					. 'loaded on admin_footer and can run under a pre-rename process that does not have our '
					. 'renamed functions (WPIN-8922 / WPIN-8739).'
				);
			}
		}
	}

	/**
	 * The privacy link must survive whichever helper name is available, and must
	 * never render an empty href.
	 */
	public function test_data_consent_modal_renders_a_privacy_url() {
		ob_start();
		include $this->template_path( 'data-consent-modal.php' );
		$html = ob_get_clean();

		$this->assertStringContainsString(
			esc_url( groupone_get_privacy_policy_url() ),
			$html,
			'The privacy policy link must resolve through the live helper when it exists.'
		);
		$this->assertStringNotContainsString(
			'href=""',
			$html,
			'The privacy policy link must never degrade to an empty href.'
		);
	}

	/**
	 * The reported crash was in the else branch — an onboarding-flow value that
	 * matches none of the known cases. Render it and assert real asset URLs come
	 * out, confirming the constant-to-local swap did not change the output.
	 */
	public function test_welcome_modal_renders_asset_urls_for_unknown_flow() {
		$_GET['onboarding-flow'] = 'non-existence';
		$_GET['page']            = 'onecom-home';

		ob_start();
		include $this->template_path( 'welcome-modal.php' );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'welcome-modal.svg', $html );
		$this->assertStringContainsString(
			GROUPONE_WP_URL . '/modules/home/assets/tiles/welcome-modal.svg',
			$html,
			'Asset URLs must still be built from the plugin base URL.'
		);
		$this->assertStringNotContainsString(
			'GROUPONE_WP_URL',
			$html,
			'The constant name must never leak into rendered output.'
		);
	}

	/**
	 * Without an onboarding flow the template returns before doing any work.
	 * The guard used to sit after the asset and theme lookups, so those ran on
	 * every admin page load for anyone who had not dismissed the modal.
	 */
	public function test_welcome_modal_returns_early_without_onboarding_flow() {
		unset( $_GET['onboarding-flow'] );

		ob_start();
		include $this->template_path( 'welcome-modal.php' );
		$html = ob_get_clean();

		$this->assertSame( '', trim( $html ), 'No onboarding flow must render nothing at all.' );
	}
}
