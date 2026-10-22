<?php

class OneHomeSections {

	const MARKETPLACE_SLUG = 'onecom-marketplace';
	public function __construct() {
		add_action( 'wp_ajax_oc_close_welcome_modal', array( $this, 'oc_close_welcome_modal' ) );
		add_action('upgrader_process_complete', array($this, 'reset_transient_on_core_update'), 10, 2);
		add_action('update_option_WPLANG', array($this, 'reset_transient_for_featured_list'), 10, 0);
		add_action('upgrader_process_complete', array($this,'groupone_clear_marketplace_transient_on_update'), 10, 2);

		add_action('activated_plugin', array($this,'groupone_clear_mp_catalog_transients'));
		add_action('deactivated_plugin', array($this,'groupone_clear_mp_catalog_transients'));
	}

	function get_cards(): array {
		return array(
			array(
				'title'    => __( 'Health and Security', 'onecom-wp' ),
				'subtitle' => __( 'Keep your site secure.', 'onecom-wp' ),
				'url'      => admin_url( 'admin.php?page=onecom-wp-health-monitor' ),
				'icon'     => 'health-monitor',
				'event_track_class'    => 'ocwp_ocp_home_health_security_clicked_event'
			),
			array(
				'title'    => __( 'Performance', 'onecom-wp' ),
				'subtitle' => __( 'Make sure your website loads fast.', 'onecom-wp' ),
				'url'      => $this->get_performance_plugin_url(),
				'icon'     => 'speedometer',
				'event_track_class'    => 'ocwp_ocp_home_performance_link_clicked_event',
				// WPIN-8936: when the module is off, get_performance_plugin_url()
				// returns '' and the template points this tile at the module
				// toggle row instead of a dead link.
				'module_slug' => 'performance-cache',
			),
			array(
				'title'    => __( 'Staging', 'onecom-wp' ),
				'subtitle' => __( 'Test changes in staging.', 'onecom-wp' ),
				'url'      => admin_url( 'admin.php?page=onecom-wp-staging' ),
				'icon'     => 'staging',
				'event_track_class'    => 'ocwp_ocp_home_staging_link_clicked_event'
			),
		);
	}

	function get_help_cards(): array {
		return array(
			array(
				'title'    => __( 'Help Centre', 'onecom-wp' ),
				'subtitle' => __( 'Find answers quickly in our Help Centre.', 'onecom-wp' ),
				'url'      => 'https://help.one.com',
				'icon'     => 'help',
				'event_track_class'    => 'ocwp_ocp_home_help_centre_link_clicked_event'
			),
			array(
				'title'    => __( 'Email support', 'onecom-wp' ),
				'subtitle' => __( 'We will respond within 24 hours, all year round.', 'onecom-wp' ),
				'url'      => 'https://help.one.com/hc/en-us/requests/new',
				'icon'     => 'library_books',
				'event_track_class'    => 'ocwp_ocp_home_email_support_link_clicked_event'
			),
		);
	}

	function get_articles_mwp(): array {
		return array(
			array(
				'title' => __( 'How to build your WordPress website', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/360001788897',
				'event_track_class' => 'ocwp_ocp_home_build_wp_site_link_clicked_event'
			),
			array(
				'title' => __( 'What is the one.com plugin?', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/115005593945',
				'event_track_class' => 'ocwp_ocp_home_what_is_ocp_plugin_link_clicked_event'
			),
			array(
				'title' => __( 'What is Maintenance Mode', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/8096988382353',
				'event_track_class' => 'ocwp_ocp_home_what_is_mm_link_clicked_event'
			),
			array(
				'title' => __( 'Using the one.com Staging feature for WordPress', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/360000020617',
				'event_track_class' => 'ocwp_ocp_home_using_staging_link_clicked_event'
			),
			array(
				'title' => __( 'How to use the Performance Cache plugin for WordPress', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/360000080458',
				'event_track_class' => 'ocwp_ocp_home_using_pcache_link_clicked_event'
			),
			array(
				'title' => __( 'How can I improve the speed of my WordPress site', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/6555011842705-How-can-I-improve-the-speed-of-my-WordPress-site',
				'event_track_class' => 'ocwp_ocp_home_special_mwp_support_link_clicked_event'
			),
		);
	}

	function get_articles_basic(): array {
		return array(
			array(
				'title' => __( 'How to build your WordPress website', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/360001788897',
				'event_track_class' => 'ocwp_ocp_home_build_wp_site_link_clicked_event'
			),
			array(
				'title' => __( 'What is the one.com plugin?', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/115005593945',
				'event_track_class' => 'ocwp_ocp_home_what_is_ocp_plugin_link_clicked_event'
			),
			array(
				'title' => __( "What is one.com's Managed WordPress", 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/360020315097',
				'event_track_class' => 'ocwp_ocp_home_what_is_ocp_mwp_link_clicked_event'
			),
			array(
				'title' => __( 'What is Maintenance Mode', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/8096988382353',
				'event_track_class' => 'ocwp_ocp_home_what_is_mm_link_clicked_event'
			),
			array(
				'title' => __( 'How to use the Performance Cache plugin for WordPress', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/360000080458',
				'event_track_class' => 'ocwp_ocp_home_using_pcache_link_clicked_event'
			),
			array(
				'title' => __( 'What is WP Rocket', 'onecom-wp' ),
				'url'   => 'https://help.one.com/hc/en-us/articles/5927991871761',
				'event_track_class' => 'ocwp_ocp_home_what_is_wpr_link_clicked_event'
			),
		);
	}


	function get_cp_url() {
		$domain = $_SERVER['HTTP_X_GROUPONE_HOST'] ?? '';
		return esc_url( 'https://www.one.com/admin/managedwp/' . $domain . '/managed-wp-dashboard.do' );
	}

	/**
	 * The settings page a module-backed tile points at once its module is on.
	 *
	 * Deliberately separate from the tile's own url, which stays empty while the
	 * module is off: the template hands this to the JS so a tile enabled by a
	 * toggle can start navigating without a page reload. Reads the same slug
	 * registry the sidebar sync uses, so there is one source of truth.
	 *
	 * @param string $module_slug e.g. 'performance-cache'.
	 * @return string Admin URL, or '' when the module has no settings page.
	 */
	function get_module_page_url( string $module_slug ): string {
		if ( ! function_exists( 'groupone_module_page_slugs' ) ) {
			return '';
		}

		$slugs = groupone_module_page_slugs();

		if ( empty( $slugs[ $module_slug ][0] ) ) {
			return '';
		}

		return admin_url( 'admin.php?page=' . $slugs[ $module_slug ][0] );
	}

	function get_performance_plugin_url() {
		// WPIN-8936: this used to check whether the standalone
		// 'onecom-vcache/vcaching.php' plugin file was active, which was
		// only ever true before the WPIN-8736 merge. Once Performance Cache
		// became a merged module, that file is never in active_plugins on a
		// merged site, so this returned '' unconditionally — the tile's CTA
		// link was dead regardless of the module's on/off state. Check the
		// module's actual runtime state instead, which also naturally
		// returns false while the legacy plugin still holds precedence
		// (WPIN-8737 takeover) or the module has been toggled off.
		if ( function_exists( 'groupone_module_active' ) && groupone_module_active( 'performance-cache' ) ) {
			return admin_url( 'admin.php?page=onecom-vcache-plugin' );
		}
		return '';
	}

	/**
	 * Per-module data for the Home page module toggle list (WPIN-8736,
	 * moved off the former Settings page). Reuses inc/module-registry.php as
	 * the single source of truth for the module map and enabled/disabled
	 * state — this method only shapes that data for the template.
	 *
	 * @return array Each entry: slug, label, description, icon, badge,
	 *               mwp_only, enabled, toggleable, note.
	 */
	function get_module_toggles(): array {
		if ( ! function_exists( 'groupone_merged_modules_map' ) ) {
			return array();
		}

		$labels           = function_exists( 'groupone_module_labels' ) ? groupone_module_labels() : array();
		$descriptions     = function_exists( 'groupone_module_descriptions' ) ? groupone_module_descriptions() : array();
		$icons            = function_exists( 'groupone_module_icons' ) ? groupone_module_icons() : array();
		$disabled_modules = groupone_get_disabled_modules();
		$toggles          = array();

		// ismWP() is defined lazily on admin_init (onecom-themes-plugins.php),
		// which has always fired by the time a real admin page render reaches
		// this method — but not for a test calling it directly, so make the
		// dependency explicit rather than implicit (WPIN-8859).
		if ( ! function_exists( 'ismWP' ) ) {
			require_once GROUPONE_WP_PATH . 'inc/functions.php';
		}

		foreach ( groupone_merged_modules_map() as $slug => $module ) {
			$legacy_active     = groupone_legacy_plugin_active( $module['legacy'] );
			$bootstrap_exists  = file_exists( $module['bootstrap'] );
			$mwp_only          = ! empty( $module['mwp_only'] );
			$mwp_blocked       = $mwp_only && ! ismWP();
			$persisted_enabled = ! in_array( $slug, $disabled_modules, true );

			// WPIN-8862: a module can be gated by more than one thing at once
			// (spam-protection is both mwp_only and has a legacy standalone
			// plugin), and the old if/elseif chain showed only the first
			// reason. The mWP-only badge still rendered, so the row could
			// display the badge while the text never mentioned MWP at all.
			//
			// Not a blind concatenation, because the reasons aren't peers:
			// "not bundled in this release" makes the other two unactionable
			// (there is nothing here to control yet, on any plan), so it wins
			// outright. Legacy-active and mwp_blocked are both actionable and
			// independent, so those two do combine — legacy first, since
			// deactivating the standalone plugin is the step that has to
			// happen before the MWP gate is even reachable.
			$notes = array();
			if ( ! $bootstrap_exists ) {
				$notes[] = __( 'Not available yet.', 'onecom-wp' );
			} else {
				if ( $legacy_active ) {
					$notes[] = __( 'Managed by the standalone plugin — deactivate it to control this feature here.', 'onecom-wp' );
				}
				if ( $mwp_blocked ) {
					$notes[] = __( 'Available on Managed WordPress plans only.', 'onecom-wp' );
				}
			}

			$note = implode( ' ', $notes );

			$toggles[] = array(
				'slug'        => $slug,
				'label'       => $labels[ $slug ] ?? $slug,
				'description' => $descriptions[ $slug ] ?? '',
				'icon'        => $icons[ $slug ] ?? '',
				'badge'       => $mwp_only ? __( 'mWP-only', 'onecom-wp' ) : '',
				// WPIN-9042: lets the template reserve the toggle-loader slot
				// only for modules whose enable path can run the multi-second
				// live ismWP(true) check, instead of rendering it (unused) on
				// every module row.
				'mwp_only'    => $mwp_only,
				// WPIN-8907: a never-toggled (or since-downgraded) mWP-only
				// module must not display as on when it isn't actually
				// running — onecom_disabled_modules alone doesn't reflect
				// that. The persisted option itself is untouched, so a
				// module enabled before a downgrade still shows enabled
				// again the moment mWP status returns (WPIN-8859's original
				// intent), instead of requiring the customer to re-toggle it.
				// $legacy_active is the same case: a module whose standalone
				// plugin is active is skipped at load time, so it is not
				// running either. Persisted option untouched here too.
				'enabled'     => $persisted_enabled && ! $mwp_blocked && ! $legacy_active,
				// WPIN-8907: stays clickable even when mwp_blocked, so a
				// customer attempting to turn it on reaches
				// groupone_ajax_toggle_module()'s live mWP check and sees
				// the upsell banner (WPIN-8899) instead of a dead control.
				'toggleable'  => ! $legacy_active && $bootstrap_exists,
				'note'        => $note,
			);
		}

		return $toggles;
	}

	function oc_close_welcome_modal() {

		$user_id = get_current_user_id();

		if ( $user_id ) {
			// Update the user meta for the currently logged-in user
			$update_meta = update_user_meta( $user_id, 'oc-welcome-modal-closed', true );

			if ( $update_meta || is_integer( $update_meta ) ) {
                // Store dismissed time, keep for 5 minutes
                set_transient( 'oc_onboarding_dismissed', time(), 300 );
				// Send a success response
				wp_send_json_success( array( 'message' => 'Welcome modal successfully closed' ) );
			} else {
				// Send a failure response
				wp_send_json_error( array( 'message' => 'Failed to update the welcome modal user meta' ) );
			}
		} else {
			// Send an error response if no user is logged in
			wp_send_json_error( array( 'message' => 'User not logged in' ) );
		}
	}

	/**
	 * Fetch products from API
	 * @param bool $force
	 * @param string $domain
	 * @return array|mixed|WP_Error
	 */
	public function fetch_products_from_api(bool $force = false, string $domain = '' ): mixed
	{
		// check transient
		$featured_products_info = get_site_transient( 'onecom_marketplace_catalog' );
		if ( is_array( $featured_products_info ) &&
			! empty( $featured_products_info['success'] ) &&
			isset( $featured_products_info['data']['catalog'] ) &&
			is_array( $featured_products_info['data']['catalog']) && false === $force ) {
			error_log( '[MP_ONECOM_PLUGIN] Using cached featured products info' );
			return $featured_products_info;
		}

		//prepare headers for a domain model
		$api_url = MIDDLEWARE_URL .'/marketplace/products/catalog';

		$wpAdminEnvInfo = $this->get_admin_env_info();

		// Set default locale if empty
		if ( empty( get_locale() ) ) {
			$wpAdminEnvInfo['locale'] = 'en_GB';
		}

		// Add locale, php_version and wp_version as query parameter
		$url = add_query_arg( 'locale', $wpAdminEnvInfo['locale'], $api_url );
		$url = add_query_arg( 'php', $wpAdminEnvInfo['php_version'], $url );
		$url = add_query_arg( 'wp', $wpAdminEnvInfo['wp_version'], $url );

		// headers and api url based on cluster domain or not
		add_filter('http_request_args', 'groupone_add_http_headers', 10, 2);

		$response = wp_remote_get($url, array('timeout' => 60));

		remove_filter('http_request_args', 'groupone_add_http_headers');

		if ( is_wp_error( $response ) ) {
			error_log( '[MP_ONECOM_PLUGIN] Error fetching featured products info from API: ');
			return array(
				'data'    => array(),
				'error'   => __( 'Some error occurred, please reload the page and try again.', 'validator' ),
				'success' => false,
			);
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if (
			! empty( $response['success'] ) &&
			isset( $response['data']['catalog'] ) &&
			is_array( $response['data']['catalog'] )
		){
			// save transient for next calls and return the latest response
			set_site_transient( 'onecom_marketplace_catalog', $response, 15 * MINUTE_IN_SECONDS );
			error_log( '[MP_ONECOM_PLUGIN] Successfully fetched featured products info from API' );
			return $response;
		} else {
			return array(
				'data'    => array(),
				'error'   => __( 'Some error occurred, please reload the page and try again.', 'validator' ),
				'success' => false,
			);
		}
	}

	/**
	 * Get featured products only
	 *
	 * @return array Array of featured products
	 */
	public function get_featured_products(): array
	{
		// Fetch products from API and return the data, we can also use true for force parameter to fetch fresh data
		//For now fetch on each page load
		$response = $this->fetch_products_from_api();


		// Check if the response has the expected structure
		if ( ! is_array( $response['data'] ) || empty( $response['data']) ) {
			error_log( '[MP_ONECOM_PLUGIN] Error fetching featured products info from API: Invalid response structure' );
			return array(
				'catalog' => [],
				'uiI18n' => '',
			);
		}

		$catalog = $response['data']['catalog'] ?? [];

		// Check if Rank Math free version is active
		$is_rank_math_free_active = $this->is_plugin_active_by_slug('seo-by-rank-math');

		// Filter products where featured === true
		$featured_products = array_filter( $catalog, function( $product ) use ( $is_rank_math_free_active ) {
			if (!isset($product['featured']) || $product['featured'] !== true || $this->is_plugin_active_by_slug($product['slug'])) {
				return false;
			}

			// Special filter for Rank Math: if free version is not active, exclude pro version
			if (!$is_rank_math_free_active && $product['slug'] === 'seo-by-rank-math-pro') {
				return false;
			}

			// If a product has mustHavePlugins dependency, check it
			if (isset($product['rules']['mustHavePlugins']) && is_array($product['rules']['mustHavePlugins'])) {
				return $this->are_plugin_dependencies_active($product['rules']['mustHavePlugins']);
			}
			// If a product has mustHaveThemesByAuthor dependency, check it
			if (isset($product['rules']['mustHaveThemesByAuthor'])) {
				return $this->are_theme_dependencies_active($product['rules']);
			}
			// Default for other plugins
			return true;
		} );

		$featured_products = array_values( $featured_products );

		// Sort by displayOrder key (ascending)
		usort( $featured_products, function( $a, $b ) {
			$order_a = isset( $a['displayOrder'] ) ? (int) $a['displayOrder'] : PHP_INT_MAX;
			$order_b = isset( $b['displayOrder'] ) ? (int) $b['displayOrder'] : PHP_INT_MAX;
			return $order_a <=> $order_b;
		} );

		// Re-index array
		return array(
			'catalog' => $featured_products,
			'uiI18n' => $response['data']['uiI18n'] ?? [],
		);
	}

	/**
	 * Check if a plugin is active by its slug
	 * @param $plugin_slug
	 * @return bool
	 */
	public function is_plugin_active_by_slug( $plugin_slug ): bool
	{
		$plugins = get_option( 'active_plugins' );

		foreach ( $plugins as $key => $plugin_path ) {
			// Example $plugin_path = "rank-math/rank-math.php"
			if ( strpos( $plugin_path, $plugin_slug . '/' ) === 0 ) {

				// Found plugin inside folder "<slug>/..."
				if ( is_plugin_active( $plugin_path ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Example cURL request for the API endpoint
	 * Usage: Call this from the command line or use as a reference
	 *
	 * curl -X GET "https://local-ssh.mwp.1prod.one/wp-content/wp-mp.json?locale=en_US" \
	 *      -H "Accept: application/json"
	 */

	public function get_recommended_products_list(): bool|string
	{

		ob_start();
		$products = $this->get_featured_products();
		$featured_products = $products['catalog'];
		$uiI18n = $products['uiI18n']['featuredCta'] ?? __( 'Get started', 'onecom-wp' );

		if(empty($featured_products) || !is_array($featured_products)){
			error_log('[MP_ONECOM_PLUGIN] No featured products found or all feature products are already installed and activated.');
			return ob_get_clean();
		}

		$plugin_attr = array_slice(array_column($featured_products, 'slug'), 0, 3);//Get plugin slugs
		$referrer = ( isset( $_SERVER['HTTP_REFERER'] ) ? parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY ) : '' );
		( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins( 'recommended_products_viewed', 'misc' , 'onecom_home', "$referrer", ["home_recomm_items" => implode(',', $plugin_attr)] ) : '' );
		?>
		<div class="gv-grid gv-grid-cols-2 gv-pb-md gv-pt-lg">
			<strong class="gv-text-lg oc-dicover-wp"><?php echo __( 'Recommended WordPress products', 'onecom-wp' ); ?></strong>
			<div class="gv-flex gv-justify-end">
				<a href="<?php echo admin_url( 'admin.php?page='.OneHomeSections::MARKETPLACE_SLUG );?>"
				   class="gv-button gv-button-primary gv-mode-condensed ocwp_ocp_home_all_products_link_clicked_event">
					<span class="oc-all-articles"><?php echo __( 'ui.button.seeAllProducts', 'onecom-wp' ); ?></span>
					<img src="<?php echo GROUPONE_WP_URL; ?>modules/home/assets/icons/white-arrow.svg" class
					="ocwp_ocp_home_all_products_link_clicked_event gv-ml-sm" height="14px" width="14px" alt="<?php echo __( 'ui.button.seeAllProducts', 'onecom-wp' ); ?>"/>
				</a>
			</div>
		</div>
		<div class="gv-grid gv-gap-lg gv-tab-grid-cols-2 gv-desk-grid-cols-3 recommendations-grid">
			<?php
			//Filter only 3 featured products
			$featured_products = array_slice($featured_products, 0, 3);
			if ( ! empty( $featured_products ) ) {
				foreach ( $featured_products as $product ) {
					$icon_url =  esc_url( $product['bannerUrl'] );
					$product_name = esc_html( $product['i18n']['featuredTitle'] );
					$product_description = esc_html( $product['i18n']['featuredContent'] );
					$install_button_text = $uiI18n;
					$product_slug = esc_attr( $product['slug'] );
					?>
					<div class="gv-card" data-product-slug="<?php echo $product_slug; ?>">
						<div class="gv-card-image">
							<img
								src="<?php echo $icon_url; ?>"
								alt="<?php echo $product_name; ?>"
							/>
						</div>
						<div class="gv-card-content">
							<h3 class="gv-card-title"><?php echo $product_name; ?></h3>
							<p><?php echo $product_description; ?></p>
						</div>
						<div class="gv-card-footer">
							<a href="<?php echo admin_url( 'admin.php?page='.OneHomeSections::MARKETPLACE_SLUG.'&plugin='.$product_slug );?>" class="gv-button gv-button-secondary gv-button-sm ocwp_ocp_home_<?php echo $product_slug;?>_link_clicked_event">
								<span><?php echo $install_button_text; ?></span>
								<img src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/arrow_forward.svg" aria-hidden="true" height="24px" width="24px" alt="<?php echo $install_button_text; ?>"/>
							</a>
						</div>
					</div>
				<?php
				}
			} ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Retrieve WordPress admin environment information.
	 *
	 * @return array Associative array containing environment details including locale, user locale, WordPress version, and PHP version.
	 */
	public function get_admin_env_info(): array
	{
			return [
				'locale'       => get_locale(),
				'user_locale'  => get_user_locale(),
				'wp_version'   => get_bloginfo('version'),
				'php_version'  => phpversion(),
			];
	}

	/**
	 * Resets the transient cache for the featured list.
	 *
	 * This method deletes the site transient related to featured products information, ensuring that the data is refreshed.
	 *
	 * @return void
	 */
	public function reset_transient_for_featured_list(): void
	{
		error_log('[MP_ONECOM_PLUGIN] Reset transient for featured products list on locale change');
		delete_site_transient( 'onecom_marketplace_catalog' );
	}

	/**
	 * Resets the transient cache for the featured list on core update.
	 * @param $upgrader
	 * @param $hook_extra
	 * @return void
	 */
	public function reset_transient_on_core_update($upgrader, $hook_extra): void
	{
		if (
			empty( $hook_extra['action'] ) || 'update' !== $hook_extra['action'] ||
			empty( $hook_extra['type'] )   || 'core' !== $hook_extra['type']
		) {
			return;
		}

		error_log('[MP_ONECOM_PLUGIN] Reset transient for featured products list on core update.');
		delete_site_transient( 'onecom_marketplace_catalog' );
	}

	/**
	 * Checks if any required plugin from the mustHavePlugins array is installed and active.
	 * Uses is_plugin_active_by_slug for each slug.
	 *
	 * @param array $mustHavePlugins Array of plugin slugs (e.g., 'wpforms-lite', 'gravityforms')
	 * @return bool True if at least one required plugin is active, false otherwise.
	 */
	public function are_plugin_dependencies_active(array $mustHavePlugins = array()): bool
	{
		if (empty($mustHavePlugins) || !is_array($mustHavePlugins)) {
			return false;
		}
		foreach ($mustHavePlugins as $plugin_slug) {
			if ($this->is_plugin_active_by_slug($plugin_slug)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if the required theme dependency is met based on the mustHaveThemesByAuthor rule.
	 *
	 * @param array $rules Product rules array
	 * @return bool True if the theme author matches, false otherwise.
	 */
	public function are_theme_dependencies_active(array $rules = array()): bool
	{
		$theme = wp_get_theme();
		if (isset($rules['mustHaveThemesByAuthor']) && $rules['mustHaveThemesByAuthor']) {
			$author = $theme->get('Author');
			if (strtolower($author) === strtolower($rules['mustHaveThemesByAuthor'])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Clear marketplace catalog transient on onecom-themes-plugins update
	 * @param $upgrader_object
	 * @param $options
	 * @return void
	 */
	public function groupone_clear_marketplace_transient_on_update($upgrader_object, $options): void
	{
        if (!isset($options['action'], $options['type']) || $options['action'] !== 'update' || $options['type'] !== 'plugin') {
			return;
		}

		$plugin_slug = 'onecom-themes-plugins/onecom-themes-plugins.php';

		if (!empty($options['plugins']) && in_array($plugin_slug, $options['plugins'], true)) {
			error_log('[MP_ONECOM_PLUGIN] Reset transient marketplace catalog on onecom-themes-plugins update.');
			delete_site_transient('onecom_marketplace_catalog');
		}
	}

	/**
	 * Clear marketplace catalog transient on plugin activation/deactivation
	 * @param $plugin
	 * @return void
	 */
	public function groupone_clear_mp_catalog_transients($plugin): void {
		if ($plugin === 'onecom-themes-plugins/onecom-themes-plugins.php') {
			error_log('[MP_ONECOM_PLUGIN] Reset transient marketplace catalog on onecom-themes-plugins activation/deactivation.');
        	delete_site_transient('onecom_marketplace_catalog');
    	}
	}
}