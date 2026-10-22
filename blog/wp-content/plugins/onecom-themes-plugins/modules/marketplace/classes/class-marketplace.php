<?php

declare(strict_types=1);
defined('WPINC') or die(); // No Direct Access

/**
 * Class OnecomMarketplace
 * Generalized plugin activation logic for any plugin via plugin_slug
 */
#[\AllowDynamicProperties]
class OnecomMarketplace {

    const EXPIRATION_TIME_IN_MINUTES = 5; // 5-minute

	const PLUGIN_HANDLE = array(
                'wp-rocket' => 'wp-rocket/wp-rocket.php',
                'rank-math' => 'seo-by-rank-math-pro/rank-math-pro.php',
	);

	const ADDONS_SLUGS = array(
                'wp-rocket' => 'WP_ROCKET',
                'rank-math' => 'RANK_MATH',
                'ai-wordpress' => 'AI_WORDPRESS'
	);

	const PLUGIN_SLUGS_NAME = array(
                'wp-rocket' => 'WP Rocket',
                'rank-math' => 'Rank Math PRO'
	);

	CONST ITEM_CATEGORY = array(
		'wp-rocket' => 'performance',
		'rank-math' => 'seo'
	);

	CONST PLUGIN_SLUGS = array(
		'wp-rocket' => 'wp-rocket',
		'rank-math' => 'seo-by-rank-math-pro'
	);

	public array $contact_support_links = array(
			'en' => 'https://help.one.com/hc/en-us/requests/new',
			'da' => 'https://help.one.com/hc/da/requests/new',
			'de' => 'https://help.one.com/hc/de/requests/new',
			'es' => 'https://help.one.com/hc/es/requests/new',
			'fr' => 'https://help.one.com/hc/fr/requests/new',
			'fi' => 'https://help.one.com/hc/fi/requests/new',
			'it' => 'https://help.one.com/hc/it/requests/new',
			'nl' => 'https://help.one.com/hc/nl/requests/new',
			'no' => 'https://help.one.com/hc/no/requests/new',
			'pt' => 'https://help.one.com/hc/pt/requests/new',
			'sv' => 'https://help.one.com/hc/sv/requests/new'
	);
    /**
     * Constructor is intentionally empty. Initialization is handled via init().
     * @note This class is designed for use with WordPress hooks and AJAX actions, so no setup is required here.
     */
    public function __construct() {

	}

    // Initialize actions
    public function init(): void
	{
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_marketplace_plugin_activate', [$this, 'onclickPluginActivate']);
		add_action('wp_ajax_marketplace_plugin_activate_reload', [$this, 'onReloadPluginActivateCheck']);

        add_action('wp_ajax_marketplace_addon_purchase_check', [$this, 'addonStatusCheck']);
		add_action('wp_ajax_marketplace_addon_purchase_check_onload', [$this, 'addonStatusCheckOnLoad']);

		//Check addon activation banner on products page reload
		add_action('wp_ajax_marketplace_check_activate_banner', [$this, 'checkActivateBannerOnReload']);

		//Can use in marketplace service also to check addon purchase status
        add_action('wp_ajax_get_addon_purchase_status', [$this, 'isAddonPurchased']);

		//disable activation banner on plugin deactivation
		add_action('deactivated_plugin', [$this, 'pluginDeactivated'], 10, 2);

    }

    // Load scripts on relevant admin pages
    public function enqueueScripts($hook_suffix): void
	{

		$allowed_pages = [
			'one-com_page_' . MARKETPLACE_PAGE_SLUG,
			'one-com_page_' . MARKETPLACE_PRODUCTS_PAGE_SLUG,
		];

		if (!in_array($hook_suffix, $allowed_pages)) {
			return;
		}

        wp_enqueue_style('oc_mp_style', GROUPONE_WP_URL . 'modules/marketplace/assets/css/marketplace.css', [], GROUPONE_WP_VERSION);
        wp_enqueue_script('oc_mp_script', GROUPONE_WP_URL . 'modules/marketplace/assets/js/marketplace.js', ['jquery'], GROUPONE_WP_VERSION, true);

		// Localize JS with config
		wp_localize_script( 'oc_mp_script', 'mp_config', [
			'ajaxURL' => admin_url( 'admin-ajax.php' ),
			'mp_asset_url' => GROUPONE_WP_URL . 'modules/marketplace/assets/images/',
			'wp_rocket_buy_url' => GROUPONE_WPR_BUY_URL,
			'rank_math_buy_url' => GROUPONE_RM_PRO_BUY_URL,
			'wp_rocket_manage_url' => admin_url('options-general.php?page=wprocket'),
			'rank_math_manage_url' => admin_url('admin.php?page=rank-math&view=modules'),
			'marketplace_page_slug' => MARKETPLACE_PAGE_SLUG,
			'marketplace_products_page_slug' => MARKETPLACE_PRODUCTS_PAGE_SLUG,
			'mp_labels'=> array(
				'install' => __('ui.notifications.installing', 'onecom-wp'),
				'error_installing' => __("Couldn’t install plugin.", 'onecom-wp'),
				'deactivate' => __('Deactivate', 'onecom-wp'),
				'install_success' => __('Plugin installed successfully.', 'onecom-wp'),
				'wp_rocket' => __('WP Rocket', 'onecom-wp'),
				'rank_math' => __('Rank Math PRO', 'onecom-wp'),
				'active' => __('Active', 'onecom-wp'),
				'rank-math' => 'seo-by-rank-math-pro',
				'wp-rocket' => 'wp-rocket',
				'plugin_activated' => array(
					'title' => __('Plugin was activated', 'onecom-wp'),
					'description' => __('You can start using it.', 'onecom-wp'),
					'btn_text' => __('Manage', 'onecom-wp'),
					'wp-rocket_link' => $this->getNoticePluginUrl('wp-rocket'),
					'rank-math_link' => $this->getNoticePluginUrl('rank-math'),
				),
				'plugin_installed' => array(
					'title' => __('Plugin was installed.', 'onecom-wp'),
					'description' => __('Activate it now to start using it.', 'onecom-wp'),
					'btn_text' => __('Activate', 'onecom-wp'),
					'wp-rocket_link' => $this->getNoticePluginUrl('wp-rocket'),
					'rank-math_link' => $this->getNoticePluginUrl('rank-math'),
				),
			),
		] );
    }

	/**
	 * Retrieves the admin URL for the settings page of a specific plugin based on the provided addon slug.
	 *
	 * @param string $addon_slug The identifier slug of the addon/plugin to retrieve the URL for.
	 * @return string|null The admin URL for the plugin settings page, or null if the slug does not match a known plugin.
	 */
	public function getNoticePluginUrl(string $addon_slug): ?string
	{
		$url = '';
		if($addon_slug === 'wp-rocket'){
			$url = admin_url('options-general.php?page=wprocket');
		}

		if($addon_slug === 'rank-math'){
			$url = admin_url('admin.php?page=rank-math&view=modules');
		}

		return $url;
	}

	/**
	 * Generates and returns HTML for a success notice indicating that a plugin was activated.
	 *
	 * @param string $addon_slug The slug of the add-on/plugin that was activated.
	 * @return string The HTML for the success notice, including a message, manage button, and close button.
	 */
	public function getActivatedNotice(string $addon_slug): string
	{
		$manageUrl = $this->getNoticePluginUrl($addon_slug);
		$pluginName = self::PLUGIN_SLUGS_NAME[$addon_slug] ?? '';

		$page = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : '';
		$hiddenClass = '';
		//Below is for onecom-marketplace-products page success banner
		if($page === 'onecom-marketplace-products'){
			$title = __('{name} is active', 'onecom-wp');
			$notice_title = str_replace('{name}', $pluginName, $title);

			$description = __('{name} was successfully activated on this installation.', 'onecom-wp');

			$notice_description = str_replace('{name}', $pluginName, $description);

			$button_key = __('Go to {name}', 'onecom-wp');
			$button_text = str_replace('{name}', $pluginName, $button_key);

			$button_event = 'ocwp_ocmp_go_to_' . str_replace('-', '_', $addon_slug). '_clicked_event';

			$hiddenClass = 'class="gv-hidden"';
		} else {
			$title = __('ui.notifications.pluginActivated', 'onecom-wp');
			$notice_title = str_replace('{0}', $pluginName, $title);

			$description = __('ui.notifications.manageInMyProducts', 'onecom-wp');
			$notice_description = str_replace('{0}', $pluginName, $description);

			$button_text = __('Get started', 'onecom-wp');
			$button_event = 'ocwp_ocmp_get_started_' . str_replace('-', '_', $addon_slug). '_clicked_event';
		}
		ob_start();
		?>
		<!-- Notice success -->
		<div class="gv-notice gv-notice-success gv-p-lg gv-max-mob-pt-lg gv-mb-0 gv-mt-lg wpr-notice gv-w-full">
			<img class="gv-notice-icon" src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/success.svg" alt="<?php echo __('Success', 'onecom-wp'); ?>"/>
			<div class="gv-notice-content">
				<div class="gv-notice-title"><?php echo $notice_title;?></div>
				<p><?php echo $notice_description; ?></p>
			</div>
			<a href="<?php echo $manageUrl;?>"
			   class="gv-action gv-button gv-button-neutral <?php echo $button_event;?>" target="_blank"><span class="gv-pr-sm gv-text-on-default "><?php echo $button_text;?></span>
			<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/black-arrow.svg" height="15px" width="15px" <?php echo $hiddenClass;?> alt="<?php echo $button_text; ?>"/>
			</a>
			<button class="gv-notice-close">
				<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/close.svg" height="24px" width="24px" alt="<?php echo __('Close', 'onecom-wp'); ?>"/>
			</button>
		</div>
		<!-- Notice success End -->
		<div class="gv-hidden mp-primary-manage-button">
			<a href="<?php echo $manageUrl;?>"
			   class="gv-action gv-button gv-button-primary" target="_blank"><span class="gv-pr-sm"><?php echo __('ui.labels.manage', 'onecom-wp');?></span>
				<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/white-arrow.svg" height="15px" width="15px" alt="<?php echo __('ui.labels.manage', 'onecom-wp'); ?>"/>
			</a>
		</div>
		<?php
		return ob_get_clean();
	}


	/**
	 * Check if a plugin is installed by its slug
	 * @param $plugin_slug
	 * @return bool
	 */
	public function isPluginInstalled($plugin_slug): bool {
		wp_clean_plugins_cache();
		$plugins = get_plugins();
		$plugin_handle = self::PLUGIN_HANDLE[$plugin_slug] ?? '';
		return array_key_exists( $plugin_handle, $plugins );
	}


    /**
     * Plugin activates on the button click
     * @return void
     */
    public function onclickPluginActivate(): void
	{

        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);

        //override for seo-by-rank-math-pro activation if needed
        if($plugin_slug === 'seo-by-rank-math-pro'){
            $plugin_slug = 'rank-math';
        }

        $transient_key = "{$plugin_slug}_activation_button_clicked_at";
        set_site_transient($transient_key, current_time('timestamp'), self::EXPIRATION_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
        $this->activateWpPlugin($plugin_slug);
    }

    /**
     * Addon status check on the button click
     * @return void
     */
    public function addonStatusCheck(): void
	{
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        $transient_key = "{$plugin_slug}_select_button_clicked_at";
        set_site_transient($transient_key, current_time('timestamp'), self::EXPIRATION_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
        $this->checkAddonPurchaseStatus($plugin_slug);
    }

	/**
	 * Check addon status on page load
	 * @return void
	 */
	public function addonStatusCheckOnLoad(): void
	{
		$plugin_slug = sanitize_text_field($_POST['plugin_slug']);
		$this->checkAddonPurchaseStatus($plugin_slug);
	}

	/**
	 * Check if activate banner should be shown on products page reload
	 * This checks if addon is purchased but activation is not in progress
	 * @return void
	 */
	public function checkActivateBannerOnReload(): void
	{
		$plugin_slug = sanitize_text_field($_POST['plugin_slug']);

		//override for seo-by-rank-math-pro activation if needed
		if($plugin_slug === 'seo-by-rank-math-pro'){
			$plugin_slug = 'rank-math';
		}

		$option_name = 'plugin_deactivated_' . sanitize_key($plugin_slug);

		//Return if plugin already deactivated manually
		if(get_option($option_name)){
			wp_send_json([
				'status' => 'plugin_deactivated_manually',
				'addon_slug' => $plugin_slug,
				'show_banner' => false
			]);
		}

		$plugin_handle = self::PLUGIN_HANDLE[$plugin_slug] ?? '';

		// Check if plugin is already active
		if ($plugin_handle && function_exists('is_plugin_active') && is_plugin_active($plugin_handle)) {
			wp_send_json([
				'status' => 'plugin_already_active',
				'addon_slug' => $plugin_slug,
				'show_banner' => false
			]);
		}

		// Check if activation is in progress
		$activation_button_clicked = get_site_transient("{$plugin_slug}_activation_button_clicked_at");
		$activation_start_at = get_site_transient("{$plugin_slug}_activation_start_at");
		$pp_activation_start = get_site_transient("{$plugin_slug}-pp-activation-start-at");

		$activation_in_progress = $activation_button_clicked || $activation_start_at || $pp_activation_start;

		if ($activation_in_progress) {
			error_log("[MP_ONECOM_PLUGIN] Activation in progress for {$plugin_slug}, not showing activate banner");
			wp_send_json([
				'status' => 'activation_in_progress',
				'addon_slug' => $plugin_slug,
				'show_banner' => false
			]);
		}

		// Get addon purchase status and createdAt
		$addonStatus = $this->isAddonPurchased($plugin_slug, true);
		// Check if addon is purchased
		if ($addonStatus['is_active']) {
			$createdAt = $addonStatus['addon_info']['data']['createdAt'] ?? null;
			if ($createdAt) {
				$createdAtTimestamp = strtotime($createdAt);
				$currentTimestamp = current_time('timestamp');
				$daysSincePurchase = ($currentTimestamp - $createdAtTimestamp) / DAY_IN_SECONDS;

				// Only show banner if purchased within last 30 days
				if ($daysSincePurchase > 30) {
					error_log("[MP_ONECOM_PLUGIN] Addon purchased more than 30 days ago for {$plugin_slug}, not showing activate banner");
					wp_send_json([
						'status' => 'purchase_too_old',
						'addon_slug' => $plugin_slug,
						'show_banner' => false,
						'createdAt' => $createdAt
					]);
				}

				//If createdAt is less than 1 month from today, show activate banner
				error_log("[MP_ONECOM_PLUGIN] Addon purchased but not activated for {$plugin_slug}, showing activate banner");
				wp_send_json([
					'status' => 'show_activate_banner',
					'addon_slug' => $plugin_slug,
					'show_banner' => true,
					'banner_html' => $this->getActivateBanner($plugin_slug),
					'createdAt' => $createdAt
				]);
			}
		}

		// Default case - don't show banner
		wp_send_json([
			'status' => 'addon_not_purchased',
			'addon_slug' => $plugin_slug,
			'show_banner' => false
		]);
	}


    /**
     * Check plugin activation on reload
     * @return void
     */
    public function onReloadPluginActivateCheck(): void
	{
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);

        //override for seo-by-rank-math-pro activation if needed
        if($plugin_slug === 'seo-by-rank-math-pro'){
            $plugin_slug = 'rank-math';
        }

        $this->activateWpPlugin($plugin_slug);
    }


    /**
     * Call WP API Provisioner for plugin install/activation (generic for any plugin slug)
     */
    public function callWpApiProvisioner($plugin_slug): string
	{

        if (!$this->isAddonPurchased($plugin_slug)) {
            error_log('[MP_ONECOM_PLUGIN] addon_not_subscribed, skipping WP API Provisioner call from marketplace: ' . $plugin_slug);
            return 'addon_not_subscribed';
        }

        error_log("[MP_ONECOM_PLUGIN] Request plugin activation from marketplace: $plugin_slug");
        $pp_start_at = "$plugin_slug-pp-activation-start-at";
        $start_time = get_site_transient($pp_start_at);
        if ($start_time) {
            error_log("[MP_ONECOM_PLUGIN] The provisioning request has already been sent; skipping the re-request: " . $plugin_slug);
            return 'already_in_queue';
        }

		$subdomain =  GroupOne_PushStats::get_subdomain();
		$domain = GroupOne_PushStats::get_domain();

        error_log("[MP_ONECOM_PLUGIN] Calling WP API Provisioner for plugin from marketplace ".MIDDLEWARE_URL.": $subdomain.$domain" . $plugin_slug);

        if (function_exists('is_cluster_domain') && is_cluster_domain()) {
			error_log("[MP_ONECOM_PLUGIN] Calling WP API Provisioner for cluster domain");
            $url = MIDDLEWARE_URL . '/plugin-provisioner/cluster';
        } else {
			error_log("[MP_ONECOM_PLUGIN] Calling WP API Provisioner for legacy domain");
            $url = function_exists('groupone_query_check') ? groupone_query_check(MIDDLEWARE_URL . '/plugin-provisioner') : MIDDLEWARE_URL . '/plugin-provisioner';
        }

        add_filter('http_request_args', 'groupone_add_http_headers', 10, 2);
        wp_remote_post(
            $url,
            array(
                'body' => json_encode(array(
                    'subdomain' => $subdomain,
                    'domain' => $domain,
                    'addon_slug' => $plugin_slug
                ))
            )
        );

        remove_filter('http_request_args', 'groupone_add_http_headers');

        set_site_transient($pp_start_at, current_time('timestamp'), self::EXPIRATION_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
        return 'added_to_queue';
    }

	/**
	 * Validate addon purchase status
	 * @param $addon_info
	 * @param $addon_const
	 * @return bool
	 */
    public function validateAddonPurchase($addon_info, $addon_const): bool
    {
        return
            is_array($addon_info) &&
            array_key_exists('success', $addon_info) &&
            $addon_info['success'] &&
            array_key_exists('data', $addon_info) &&
            array_key_exists('source', $addon_info['data']) &&
            $addon_info['data']['source'] === 'PURCHASED' &&
            array_key_exists('product', $addon_info['data']) &&
            $addon_info['data']['product'] === $addon_const
        ;
    }

	/**
	 * Check if addon is purchased for any plugin slug
	 * @param string $plugin_slug
	 * @return bool
	 */
	public function isAddonPurchased(string $plugin_slug, $getAddonInfo = false): bool | array
	{
		// Allow override via AJAX request
		$isMPCall = false;
		//Added handle for ajax call to check addon purchase status from marketplace service
		if(isset($_POST['addon_purchase_check']) && $_POST['addon_purchase_check'] === 'true' && isset($_POST['addon_slug'])){
			$plugin_slug = sanitize_text_field($_POST['addon_slug']);

			//override for seo-by-rank-math-pro activation if needed
			if($plugin_slug === 'seo-by-rank-math-pro'){
				$plugin_slug = 'rank-math';
			}

			//Ensure plugin slug should be valid
			$isMPCall = true;
		}

		// Fetch info for the given plugin slug
		$addon_const = self::ADDONS_SLUGS[$plugin_slug] ?? '';
		$addon_info = $this->getAddonInfo($plugin_slug, true);


		// Validate purchase once
		$isPurchased = $this->validateAddonPurchase($addon_info, $addon_const);

		// Rank Math specific checks
		$vendorLicenseInfo = false;
		$download_url = null;

		if ($plugin_slug === 'rank-math') {
			$vendorDetails = $this->getVendorLicenseDetails($plugin_slug);

			$download_url = $vendorDetails['download_url'];
			$vendorLicenseInfo = $vendorDetails['is_vendor_license_active'];

			$isPurchased = $isPurchased || $vendorLicenseInfo;
		}

		// Send ajax response if called from marketplace service
		if ($isMPCall) {
			$response = [
				'is_purchased' => $isPurchased,
				'addon_slug'   => $plugin_slug,
			];

			if ($plugin_slug === 'rank-math') {
				$response['download_url'] = $download_url;
			}

			wp_send_json($response);
		}

		// Return addon info if requested
		if ($getAddonInfo) {
			$response = [
				'is_active'  => $isPurchased,
				'addon_info' => $addon_info,
			];

			if ($plugin_slug === 'rank-math') {
				$response['download_url'] = $download_url;
			}

			return $response;
		}

		// Default return
		return $isPurchased;
	}

	/**
	 * Get addon info for a plugin slug (should be implemented to fetch actual info)
	 * @param string $plugin_slug
	 * @param bool $force
	 * @param string $domain
	 * @return array
	 */

	public function getAddonInfo(string $plugin_slug, bool $force = false, string $domain = '' ): array
	{

		$addon_slug = self::ADDONS_SLUGS[$plugin_slug] ?? '';
		// check transient
		$addon_info_transient_key = "{$plugin_slug}_onecom_addon_info";
		$addon_info = get_site_transient( $addon_info_transient_key);

		if ( ! empty( $addon_info ) && isset( $addon_info['success'] ) && $addon_info['success'] && false === $force) {
			return $addon_info;
		}

		if ( ! $domain ) {
			$domain = isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) ? $_SERVER['ONECOM_DOMAIN_NAME'] : false;
		}

		if ( ! $domain ) {
			return array(
				'data'    => null,
				'error'   => 'Empty domain',
				'success' => false,
			);
		}

		return $this->callWPApiForAddon($addon_slug, $domain, $addon_info_transient_key);
	}

	/**
	 * Get addon purchase status for a plugin slug
	 * @param string $plugin_slug
	 * @return array
	 */
	public function getVendorLicenseDetails(string $plugin_slug): array
	{
		$vendorLicense_info = $this->getVendorLicenseStatus($plugin_slug);

		error_log('Vendor licence details: ' . ($vendorLicense_info['data']['vendor_licence_exist'] ?? false). ' for plugin: ' . $plugin_slug .', using download_url');

		return [
			'is_vendor_license_active' => $vendorLicense_info['data']['vendor_licence_exist'] ?? false,
			'download_url' => $vendorLicense_info['data']['download_url'] ?? ''
		];
	}

	/**
	 * Get vendor licence status
	 * @param string $plugin_slug
	 * @param $force
	 * @return array|mixed
	 */
	public function getVendorLicenseStatus(string $plugin_slug, $force = false) {

		if( $plugin_slug !== "rank-math"){
			return array();
		}

		$vendorLicense_info = get_site_transient( "onecom_marketplace_{$plugin_slug}_vendor_status" );
		if ( ! empty( $vendorLicense_info ) && false === $force && isset( $vendorLicense_info['success'] ) && $vendorLicense_info['success']) {
			return $vendorLicense_info;
		}

		$apiEndpoint = MIDDLEWARE_URL. "/plugin-provisioner/vendor-license/status?brand=one.com&addon_slug=$plugin_slug";

		// headers and api url based on cluster domain or not
		add_filter('http_request_args', 'groupone_add_http_headers', 10, 2);

		$response = wp_remote_get($apiEndpoint, array('timeout' => 60));

		remove_filter('http_request_args', 'groupone_add_http_headers');

		if ( is_wp_error( $response ) ) {
			error_log( '[MP_ONECOM_PLUGIN] Error fetching vendor license info from API: ');
			return array(
				'data'    => array(),
				'error'   => __( 'Some error occurred, please reload the page and try again.', 'validator' ),
				'success' => false,
			);
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if (
			! empty( $response['success'] ) &&
			isset( $response['data']['vendor_licence_exist'] ) &&
			is_array( $response['data'] )
		){
			// save transient for next calls and return the latest response
			set_site_transient( "onecom_marketplace_{$plugin_slug}_vendor_status", $response, 12 * HOUR_IN_SECONDS );
			error_log( '[MP_ONECOM_PLUGIN] Successfully fetched vendor license info from API' );
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
	 * Call WP API for addon info
	 * @param $addon_slug
	 * @param $domain
	 * @param $addon_info_transient_key
	 * @return array|mixed
	 */
	// Returns an array on every path (WPIN-8909) — its only caller, getAddonInfo(),
	// declares ": array", so "mixed" here was a contract the caller could not keep.
	public function callWPApiForAddon($addon_slug, $domain, $addon_info_transient_key): array
	{
		$totp = groupone_generate_totp();

		// headers and api url based on cluster domain or not
		if ( is_cluster_domain() ) {
			//create a header for a cluster model
			$curl_url = MIDDLEWARE_URL . "/features/addon/$addon_slug/status/cluster";

			$http_header = array(
				'Cache-Control: no-cache',
				'X-Onecom-Client-Domain: ' . $domain,
				'X-TOTP: ' . $totp,
				'cache-control: no-cache',
			);

			$http_header[] = 'X-ONECOM-CLUSTER-ID: ' . OC_CLUSTER_ID;
			// WPIN-8934: this incoming header is 'Onecom', not 'GroupOne' —
			// see the matching fix/comment in inc/lib/validator.php's
			// groupone_validate_domain() and groupone_add_http_headers().
			// Reading the never-sent 'GROUPONE' key here meant this addon
			// status check was always missing the site's webconfig on
			// cluster-model (MWP) domains. Prefer the correct name, fall
			// back to the old one for safety.
			$http_header[] = 'X-ONECOM-WEBCONFIG-NAME: ' . ( $_SERVER['HTTP_X_ONECOM_WEBCONFIG_NAME'] ?? $_SERVER['HTTP_X_GROUPONE_WEBCONFIG_NAME'] ?? '' );

		} else {
			//prepare headers for a domain model
			$curl_url = MIDDLEWARE_URL . "/features/addon/$addon_slug/status";
			$http_header = array(
				'Cache-Control: no-cache',
				'X-Onecom-Client-Domain: ' . $domain, //need to use from wp-config if available otherwise use domain parse
				'X-TOTP: ' . $totp,
				'cache-control: no-cache',
			);
		}

		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $curl_url,

				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => $http_header
			)
		);

		$response = curl_exec( $curl );
		$response = json_decode( $response, true );
		$err      = curl_error( $curl );

		/*
		 * WPIN-8909: curl_error() only reports TRANSPORT failures. A request
		 * that completes but answers with something that is not JSON — an HTML
		 * error page from a proxy, a 502 body, an empty response — leaves $err
		 * empty while json_decode() yields null. Returning that straight out
		 * used to blow up in getAddonInfo(), which declares ": array":
		 *   TypeError: getAddonInfo(): Return value must be of type array,
		 *   null returned
		 * That was 10 of the suite's 62 errors, and it is a production hazard
		 * too, not merely a test artefact — a bad gateway response would fatal
		 * the marketplace screen rather than degrade to the error notice.
		 *
		 * Normalise to the same error shape the transport-failure branch above
		 * already returns, so every path out of here honours the contract.
		 */
		if ( $err || ! is_array( $response ) ) {
			return array(
				'data'    => null,
				'error'   => __( 'Some error occurred, please reload the page and try again.', 'validator' ),
				'success' => false,
			);
		}

		if ( isset( $response['success'] ) && $response['success'] ) {
			// save transient for next calls, & return latest response
			set_site_transient( $addon_info_transient_key, $response, 12 * HOUR_IN_SECONDS );
		}

		return $response;
	}

	/**
	 * Activate plugin via WP API Provisioner
	 * @param $plugin_slug
	 * @return void
	 */
	public function activateWpPlugin($plugin_slug): void
	{
		$start_time_key = "{$plugin_slug}_activation_start_at";
		$btn_transient_key = "{$plugin_slug}_activation_button_clicked_at";
		$timeout_limit = self::EXPIRATION_TIME_IN_MINUTES * MINUTE_IN_SECONDS;
		$current_time = current_time('timestamp');
		$plugin_handle = self::PLUGIN_HANDLE[$plugin_slug] ?? '';
		$install_stats_key = "mp_{$plugin_slug}_install_logged";

		//Check if the activation button was clicked
		if (!get_site_transient($btn_transient_key)) {
			error_log('[MP_ONECOM_PLUGIN] Activation button not clicked, normal reload for plugin: ' . $plugin_slug);
			wp_send_json(['status' => 'normal_reload', 'addon_slug' => $plugin_slug]);
		}

		$send_success = function() use ($install_stats_key, $plugin_slug) {
			error_log("[MP_ONECOM_PLUGIN] Plugin activated successfully from Marketplace: {$plugin_slug}");

			delete_site_transient( $install_stats_key );

			//Success activation stats log
			( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins('activate', self::ITEM_CATEGORY[$plugin_slug], self::PLUGIN_SLUGS[$plugin_slug], 'onecom-marketplace') : '' );

			$this->clearActivationQueue($plugin_slug);
			wp_send_json([
				'status' => 'activated',
				'url' => admin_url('plugins.php'),
				'btn_text' => __('Go to plugin', 'onecom-wp'),
				'addon_slug' => $plugin_slug,
				'notice_html' => $this->getActivatedNotice($plugin_slug)
			]);
		};

		//Add only a single installation stats log
		if (
			$this->isPluginInstalled($plugin_slug)
			&& false === get_site_transient($install_stats_key)
			&& set_site_transient($install_stats_key, 1, 5 * MINUTE_IN_SECONDS)
		){
			( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins('install', self::ITEM_CATEGORY[$plugin_slug], self::PLUGIN_SLUGS[$plugin_slug], 'onecom-marketplace') : '' );
			error_log("[MP_ONECOM_PLUGIN] Install stats logged for: {$plugin_slug}");
		}

		$activate_if_installed = function () use (
			$plugin_slug,
			$plugin_handle,
			$install_stats_key,
			$send_success
		) {
			if ($this->isPluginInstalled($plugin_slug) && !is_plugin_active($plugin_handle)) {
				error_log('[MP_ONECOM_PLUGIN] Plugin was installed activating only from wp-admin ' . $plugin_handle . ', Slug:' . $plugin_slug);
				$result = activate_plugin($plugin_handle);

				if (is_wp_error($result)) {
					error_log("[MP_ONECOM_PLUGIN] Plugin activation failed from Marketplace: {$plugin_slug}");
					$this->clearActivationQueue($plugin_slug);

					// Delete install stats transient to allow re-log on next attempt
					delete_site_transient($install_stats_key);

					wp_send_json_error([
						'status' => 'activation_failed',
						'message' => $result->get_error_message(),
						'addon_slug' => $plugin_slug,
						'try_again_banner' => $this->getTryAgainBanner($plugin_slug)
					]);
				}

				$send_success();
			}
		};

		//More shield check before activation
		if ( $plugin_handle && function_exists( 'is_plugin_active' ) && is_plugin_active( $plugin_handle ) ) {
			$send_success();
		}

		$start_time = get_site_transient($start_time_key);

		$handle_timeout = function() use (
			$current_time,
			$start_time,
			$timeout_limit,
			$plugin_handle,
			$send_success,
			$plugin_slug,
			$install_stats_key,
			$start_time_key
		) {
			$start_time = get_site_transient($start_time_key);

			if (!$start_time) {
				return; // not started yet, don't expire
			}

			if (($current_time - (int)$start_time) >= $timeout_limit) {
				if (is_plugin_active($plugin_handle)) {
					$send_success();
				}

				error_log("[MP_ONECOM_PLUGIN] Plugin not activated and timed out from marketplace: {$plugin_slug}");

				$this->clearActivationQueue($plugin_slug);

				// Delete install stat transient to allow re-log on the next attempt
				delete_site_transient($install_stats_key);

				wp_send_json_error([
					'status' => 'expired_queue',
					'addon_slug' => $plugin_slug,
					'try_again_banner' => $this->getTryAgainBanner($plugin_slug)
				]);
			}
		};

		if (!$start_time) {
			error_log('[MP_ONECOM_PLUGIN] Starting plugin activation process from marketplace: ' . $plugin_slug);
			set_site_transient($start_time_key, $current_time, $timeout_limit);

			//Install the plugin for rank-math using download url, if the vendor license is active
			if($plugin_slug === 'rank-math'){

				$license = $this->getVendorLicenseDetails($plugin_slug);
				$download_url = $license['download_url'];
				$license_status = $license['is_vendor_license_active'];

				// Fetch info for the given plugin slug
				$addon_const = self::ADDONS_SLUGS[$plugin_slug] ?? '';

				// Validate purchase once, by force access
				$addon_info = $this->getAddonInfo($plugin_slug, true);

				$isPurchased = $this->validateAddonPurchase($addon_info, $addon_const);

				if ( $license_status && !empty( $download_url ) && !$this->isPluginInstalled($plugin_slug) && !$isPurchased) {
					error_log('[MP_ONECOM_PLUGIN] Installing rank-math plugin from download url.' );
					require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

					//To test download url, mock download url here

					//Skip the registration step for rank-math plugin
					update_site_option('rank_math_registration_skip', 1);
					$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
					$install_result   = $upgrader->install( $download_url ); //  use URL from React

					$install_error = false;
					if ( is_wp_error( $install_result ) ) {
						$install_error = true;
					}

					// Check if the upgrader returned NULL or false (download/installation failed)
					if ( $install_result === null || $install_result === false ) {
						$install_error = true;
					}

					$handle_timeout();

					if ( $install_error ) {
						$this->clearActivationQueue($plugin_slug);
						//Delete it install stat transient to allow re-log on the next attempt
						delete_site_transient( $install_stats_key );
						wp_send_json_error([
							'status' => 'activation_failed',
							'message' => 'Failed to install rank-math plugin from download url. Download url: ' . $download_url,
							'addon_slug' => $plugin_slug,
							'try_again_banner' => $this->getTryAgainBanner($plugin_slug)
						]);
					}
				}
			}

			$activate_if_installed();

			//First call to WP API Provisioner for installation/activation
			if (!is_plugin_active($plugin_handle)) {
				$status = $this->callWpApiProvisioner($plugin_slug);
				error_log('[MP_ONECOM_PLUGIN] Provisioner status: ' . $status);
				wp_send_json(['status' => $status, 'addon_slug' => $plugin_slug]);
			}

		}

		//Check status every interval
		$handle_timeout();

		error_log("[MP_ONECOM_PLUGIN] Plugin activation in progress: {$plugin_slug}");
		wp_send_json(['status' => 'already_in_queue', 'addon_slug' => $plugin_slug]);
	}

	/**
	 * Check addon purchase status
	 * @param $plugin_slug
	 * @return void
	 */
    public function checkAddonPurchaseStatus($plugin_slug): void
	{
        $start_time_key = "{$plugin_slug}_purchase_button_start_at";
        $btn_transient_key = "{$plugin_slug}_select_button_clicked_at";
        $timeout_limit = self::EXPIRATION_TIME_IN_MINUTES * MINUTE_IN_SECONDS;
        $current_time = current_time('timestamp');
		$plugin_handle = self::PLUGIN_HANDLE[$plugin_slug] ?? '';

        if (!get_site_transient($btn_transient_key)) {
            wp_send_json(['status' => 'normal_reload', 'addon_slug' => $plugin_slug]);
        }

        $send_success = function() use ($plugin_slug) {
            error_log("[MP_ONECOM_PLUGIN] Addon purchased from Marketplace: {$plugin_slug}");
			//Success activation stats log
			( class_exists( OCPUSHSTATS ) ? \GroupOne_PushStats::push_stats_event_themes_and_plugins('addon_purchased', self::ITEM_CATEGORY[$plugin_slug], self::PLUGIN_SLUGS[$plugin_slug], 'onecom-marketplace') : '' );

            $this->clearAddonStatusQueue($plugin_slug);
            $this->setTransientForAddonActivation($plugin_slug);
            wp_send_json(['status' => 'addon_purchased', 'addon_slug' => $plugin_slug]);
        };

		//Check if the plugin is already active, a rare case but possible
        if (is_plugin_active($plugin_handle)) {
			error_log("[MP_ONECOM_PLUGIN] Plugin active during addon status check: {$plugin_slug}, skipping purchase check");
			$send_success();
        }

        $start_time = get_site_transient($start_time_key);
		// Case 1: First time select click attempt for purchase
        if (!$start_time) {
            set_site_transient($start_time_key, $current_time, $timeout_limit);
            wp_send_json(['status' => 'added_in_queue', 'addon_slug' => $plugin_slug]);
        }

		//get addon purchase status, force refresh feature endpoint
		//call feature endpoint to get latest addon status
		$addon_info = $this->getAddonInfo($plugin_slug,true);
		$addon_const = self::ADDONS_SLUGS[$plugin_slug] ?? '';
		$addon_purchased = $this->validateAddonPurchase($addon_info, $addon_const);

		if ($addon_purchased) {
			$send_success();
		}

        $elapsed_time = $current_time - (int)$start_time;
        $time_left = $timeout_limit - $elapsed_time;

		// Case 2: Stop polling early if less than 30 seconds left
        if ($time_left <= 30) {

			if ($addon_purchased) {
				$send_success();
			}

            error_log("[MP_ONECOM_PLUGIN] Polling stopped early (time left: {$time_left}s) for {$plugin_slug}");
            $this->clearAddonStatusQueue($plugin_slug);
            wp_send_json(['status' => 'expired_queue', 'addon_slug' => $plugin_slug]);
        }

		// Case 3: Queue expired after timeout
        if ($elapsed_time >= $timeout_limit) {

			if ($addon_purchased) {
				$send_success();
			}

			error_log("[MP_ONECOM_PLUGIN] Addon not purchased and timed out: {$plugin_slug}");
            $this->clearAddonStatusQueue($plugin_slug);
            wp_send_json(['status' => 'expired_queue', 'addon_slug' => $plugin_slug]);
        }

		// Case 4: Queue still in progress
		error_log("[MP_ONECOM_PLUGIN] Addon purchase in progress: {$plugin_slug}");
        wp_send_json(['status' => 'already_in_queue', 'addon_slug' => $plugin_slug]);
    }

	/**
	 * Set transient for addon activation
	 * @param $plugin_slug
	 * @return void
	 */
    public function setTransientForAddonActivation($plugin_slug): void
	{
        $activation_button_clicked_at = "{$plugin_slug}_activation_button_clicked_at";
        $pp_activation_start_at = "{$plugin_slug}-pp-activation-start-at";
		$activation_start_at = "{$plugin_slug}_activation_start_at";
        $timeout_limit = self::EXPIRATION_TIME_IN_MINUTES * MINUTE_IN_SECONDS;
        $current_time = current_time('timestamp');
        set_site_transient($activation_start_at, $current_time, $timeout_limit);
        set_site_transient($activation_button_clicked_at, $current_time, $timeout_limit);
        set_site_transient($pp_activation_start_at, $current_time, $timeout_limit);
    }

	/**
	 * Clears the activation queue for a specified plugin.
	 *
	 * @param string $plugin_slug The slug of the plugin for which the activation queue should be cleared.
	 * @return void
	 */
    public function clearActivationQueue(string $plugin_slug): void
	{
        delete_site_transient("{$plugin_slug}_activation_start_at");
        delete_site_transient("{$plugin_slug}_activation_button_clicked_at");
        delete_site_transient("{$plugin_slug}-pp-activation-start-at");
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

	/**
	 * Clears the addon status queue by removing associated transients for a given plugin and flushing cache if available.
	 *
	 * @param string $plugin_slug The slug of the plugin whose addon status queue should be cleared.
	 * @return void
	 */
    public function clearAddonStatusQueue(string $plugin_slug): void
	{
        delete_site_transient("{$plugin_slug}_purchase_button_start_at");
        delete_site_transient("{$plugin_slug}_select_button_clicked_at");
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

	public function getTryAgainBanner($plugin_slug): string
	{
		$notice_title = __('ui.notifications.activationFailedTitle','onecom-wp');
		$notice_title = str_replace('{plugin name}', self::PLUGIN_SLUGS_NAME[$plugin_slug] ?? '', $notice_title);
		$support_link = $this->get_contact_support_link();
		$notice_desc = __('ui.notifications.activationFailedText', 'onecom-wp');
		$notice_desc = str_replace('{plugin name}', self::PLUGIN_SLUGS_NAME[$plugin_slug] ?? '', $notice_desc);
		$message = sprintf($notice_desc,
			'<a class="gv-inline-block" href="'.$support_link.'" target="_blank"><span class="gv-text-on-default" style="text-decoration: underline">',
			'</span></a>'
		);
		ob_start();
		?>
		<!-- Notice error -->
		<div class="gv-notice gv-notice-alert gv-p-lg gv-max-mob-pt-lg gv-mb-0 gv-mt-lg gv-w-full">
			<img class="gv-notice-icon" src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/error.svg" />
			<div class="gv-notice-content">
				<div class="gv-notice-title"><?php echo $notice_title;?></div>
				<p class="gv-text-sm"><?php echo $message;?></p>
			</div>
			<a href="javascript:void(0)"
			   class="gv-button gv-button-neutral ocwp_ocmp_try_again_<?php echo str_replace('-', '_',$plugin_slug );?>_clicked_event" id="try-again-<?php echo $plugin_slug;?>"><span class="gv-text-on-default"><?php echo __( 'Try again', 'onecom-wp' ); ?></span></a>
			<button class="gv-notice-close">
				<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/close.svg" height="24px" width="24px" />
			</button>
		</div>
		<!-- Notice error End -->
		<?php
		return ob_get_clean();
	}

	public function get_contact_support_link(): string {
		$locale = explode( '_', get_locale() )[0];
		if ( ! array_key_exists( $locale, $this->contact_support_links ) ) {
			$locale = 'en';
		}
		return $this->contact_support_links[ $locale ];
	}

	public function getActivateBanner($addon_slug): string{
		$domain = GroupOne_PushStats::get_domain() ?? 'localhost';
		$domainBold = "<strong>$domain</strong>";

		$title =  str_replace('{name}', self::PLUGIN_SLUGS_NAME[$addon_slug], __('Activate {name}', 'onecom-wp'));

		if($addon_slug == 'wp-rocket') {
			$desc = str_replace(
				['{name}', '{domain.one}'],
				[self::PLUGIN_SLUGS_NAME[$addon_slug], $domainBold],
				__("You have a {name} subscription for {domain.one}, but you still need to activate it for the installation on {domain.one}. Activate the plugin to boost your site's performance.", 'onecom-wp')
			);
		}

		if($addon_slug == 'rank-math') {
			$desc = str_replace(
				['{name}', '{domain}'],
				[self::PLUGIN_SLUGS_NAME[$addon_slug], $domainBold],
				__("You have a {name} subscription for {domain}, but you still need to activate it for the installation on {domain}. Activate the plugin to start improving your site's SEO.", 'onecom-wp')
			);
		}

		// Set button classes based on addon slug
		$button_class = 'oc-activate-' . $addon_slug . '-btn';
		$event_class = 'ocwp_ocmp_activate_' . str_replace('-', '_', $addon_slug) . '_clicked_event';

		ob_start();
		?>
		<!-- Notice Warning -->
		<div class="gv-notice gv-notice-info gv-p-lg gv-max-mob-pt-lg gv-mb-0 gv-mt-lg activate-notice-banner" data-addon-slug="<?php echo esc_attr($addon_slug); ?>">
			<img class="gv-notice-icon" src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/info.svg" />
			<div class="gv-notice-content">
				<div class="gv-notice-title"><?php echo $title;?></div>
				<p><?php echo $desc; ?></p>
			</div>
			<a href="javascript:void(0)"
			   class="gv-button gv-button-neutral <?php echo $event_class; ?> <?php echo $button_class; ?>"><?php echo $title; ?></a>
			<button class="gv-notice-close">
				<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/close.svg" height="24px" width="24px" />
			</button>
		</div>
		<!-- Notice Warning End -->
		<?php
		return ob_get_clean();
	}

	/**
	 * Set option on premium plugin deactivation
	 * @param $plugin
	 * @param $network_deactivating
	 * @return void
	 */
	public function pluginDeactivated($plugin, $network_deactivating): void
	{
		foreach (self::PLUGIN_HANDLE as $handle => $plugin_file) {

			if ($plugin !== $plugin_file) {
				continue;
			}

			$option_name = 'plugin_deactivated_' . sanitize_key($handle);

			update_option($option_name, [
				'time'    => time(),
				'network' => (bool) $network_deactivating,
			]);

			break;
		}
	}
}