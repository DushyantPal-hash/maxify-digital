<?php
// Required information for functions
const PWPC_ADDON_API         = MIDDLEWARE_URL . '/features/addon/PREMIUM_WORDPRESS_CARE/status';
const PWPC_ADDON_CLUSTER_API = MIDDLEWARE_URL . '/features/addon/PREMIUM_WORDPRESS_CARE/status/cluster';
const WR_PWPC_API            = MIDDLEWARE_URL . '/premium-wp-care';

add_action(
	'admin_enqueue_scripts',
	function ($hook) {
		if (function_exists('get_current_screen') && get_current_screen()->id === 'one-com_page_onecom-home') {
			wp_deregister_style('wp-block-editor');
		}
		if (function_exists('get_current_screen') && (get_current_screen()->id === 'one-com_page_onecom-home'|| get_current_screen()->id === 'one-com_page_onecom-wp-cookie-banner'|| get_current_screen()->id === 'one-com_page_onecom-wp-themes' || get_current_screen()->id === 'toplevel_page_onecom-wp-spam-protection')) {
			wp_enqueue_script('oc_home_page', GROUPONE_WP_URL . 'modules/home/js/index.umd.js', array('jquery'), GROUPONE_WP_VERSION, true);
		}
		//Added allow to load resources on specific pages + one.com pages only
		$allow = array(
			'one-com_page_onecom-wp-health-monitor',
			'toplevel_page_onecom-wp-spam-protection',
			'toplevel_page_onecom-vcache-plugin',
			'one-com_page_onecom-cdn',
			'toplevel_page_onecom-wp-under-construction',
			'one-com_page_onecom-marketplace',
			'one-com_page_onecom-marketplace-products',
		);

		global $load_onecom_wp_resources_slugs;
		if (in_array($hook, $load_onecom_wp_resources_slugs) || in_array($hook, $allow)) {
			if (SCRIPT_DEBUG || SCRIPT_DEBUG == 'true') {
				wp_enqueue_script('oc_home_page_main', GROUPONE_WP_URL . 'modules/home/js/main.js', array('jquery'), GROUPONE_WP_VERSION, true);
				wp_enqueue_style('oc_home_page-css', GROUPONE_WP_URL . 'modules/home/css/main.css', '', GROUPONE_WP_VERSION, 'all');
			} else {
				wp_enqueue_script('oc_home_page_main', GROUPONE_WP_URL . 'assets/min-js/main.min.js', array('jquery'), GROUPONE_WP_VERSION, true);
				wp_enqueue_style('oc_home_page-css', GROUPONE_WP_URL . 'assets/min-css/main.min.css', '', GROUPONE_WP_VERSION, 'all');
			}
			wp_localize_script(
				'oc_home_page_main',
				'oc_home_ajax_obj',
				array(
					'ajax_url'         => admin_url('admin-ajax.php'),
					'nonce'            => wp_create_nonce('oc_home_ajax'),
					'home_url'         => admin_url('admin.php?page=onecom-home'),
					'close_icon'       => GROUPONE_WP_URL . '/modules/home/assets/icons/close.svg',
					'toast_success_msg'      => __( 'Your preferences were saved.', 'onecom-wp' ),
					'toast_failure_msg'      => __( 'Couldn’t save your preferences.', 'onecom-wp' ),
					// WPIN-8899: shown instead of the generic failure toast
					// when the live MWP subscription check itself errors out.
					'toast_mwp_check_failed_msg' => __( 'Couldn’t verify your subscription. Please try again.', 'onecom-wp' ),
					// WPIN-8925: module slug => settings-page slug(s), so the
					// Features toggle list's change handler knows which
					// sidebar <li> elements to hide/show immediately.
					'module_page_slugs' => function_exists( 'groupone_module_page_slugs' ) ? groupone_module_page_slugs() : array(),
					// WPIN-9030: popup content for Spam Protection mWP upgrade modal on Home page.
					'sp_popup_content'  => array(
						'title'       => sprintf( __( 'Get Spam Protection plugin with %s Managed WordPress', 'onecom-wp' ), "\n" ),
						'top-desc'    => __( 'Spend less time worrying about your site’s safety and more time growing your business with one.com Managed WordPress.', 'onecom-wp' ),
						'footer-desc' => __( 'Managed WordPress includes other great features such as Vulnerability Monitor and Upgrade Manager', 'onecom-wp' ),
						'bodylist'    => array(
							__( 'Protects the website from spam comments, spam registration, spambots, and other spammers.', 'onecom-wp' ),
							__( 'Customizable protection settings, spam logs, and other options.', 'onecom-wp' ),
						),
					),
				)
			);
			wp_enqueue_style('oc_gravity-css', GROUPONE_WP_URL . 'assets/min-css/one.min.css', null, GROUPONE_WP_VERSION);
		}
	}
);
function wporg_options_page_html() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require_once GROUPONE_WP_PATH . 'modules/home/templates/home.php';
}

function wporg_options_page() {
	add_submenu_page( 'onecom-wp', __( 'Home', 'onecom-wp' ), '<span id="onecom_home">Home</span>', 'manage_options', 'onecom-home', 'wporg_options_page_html', -1 );
}

add_action( 'admin_menu', 'wporg_options_page' );
// On multisite the one.com menu exists only in network admin, where Home is
// the page it lands on. Without this the landing page has no callback.
add_action( 'network_admin_menu', 'wporg_options_page' );

add_action(
	'wp_ajax_oc_home_premium_care_dismiss',
	function () {
		$status = update_site_option( 'oc_home_premium_care_dismiss', true );
		if ( $status ) {
			wp_send_json( array( 'status' => 'success' ) );
		} else {
			wp_send_json( array( 'status' => 'error' ) );
		}
	}
);

add_action( 'init', 'show_welcome_modal' );

// function to show the modal based on the user meta values
function show_welcome_modal(): void {
	$welcome_modal_closed = false;
	$user_id              = get_current_user_id();
	if ( $user_id ) {
		// Retrieve the user meta
		$welcome_modal_closed = get_user_meta( $user_id, 'oc-welcome-modal-closed', true );
	}
	if ( $welcome_modal_closed !== true && $welcome_modal_closed !== '1' ) {
		add_action( 'admin_footer', 'welcome_popup_init' );
	}
}


/**
 * @return void
 * function to include the template of welcome modal
 */
function welcome_popup_init() {
	require_once GROUPONE_WP_PATH . 'modules/home/templates/welcome-modal.php';
}

/**
 * Check if current admin screen is for one.com plugin pages
 */
function groupone_is_onecom_plugins_page() {
	$screen = get_current_screen();

	if ( ! $screen ) {
		return false;
	}

	$allowed_screens = array(
		'one-com_page_onecom-home',
		'one-com_page_onecom-wp-health-monitor',
		'one-com_page_onecom-wp-themes',
		'toplevel_page_onecom-vcache-plugin',
		'one-com_page_onecom-cdn',
		'admin_page_onecom-wp-recommended-plugins',
		'admin_page_onecom-wp-discouraged-plugins',
		'one-com_page_onecom-wp-staging',
		'one-com_page_onecom-wp-staging-blocked',
		'one-com_page_onecom-wp-error-page',
		'one-com_page_onecom-wp-cookie-banner',
		'toplevel_page_onecom-wp-under-construction',
		'toplevel_page_onecom-wp-spam-protection',
		'one-com_page_onecom-marketplace',
		'one-com_page_onecom-marketplace-products'
	);

	return in_array( $screen->id, $allowed_screens, true );
}

// Include data consent and premium care modals
function groupone_data_consent_modal_init() {
	// Include data consent modal on all onecom plugins screen
	if ( groupone_is_onecom_plugins_page() ) {
		require_once GROUPONE_WP_PATH . 'modules/home/templates/data-consent-modal.php';
	}

	// Include premium wp care modal template on onecom home screen
	if ( function_exists( 'get_current_screen' ) && get_current_screen()->id === 'one-com_page_onecom-home' ) {
		require_once GROUPONE_WP_PATH . 'modules/home/templates/premium-wp-care-modal.php';
	}
}
add_action( 'admin_footer', 'groupone_data_consent_modal_init' );

/**
 * Set transient when onboarding tour is dismissed
 * Used to track 5 second delay before consent banner is eligible
 */
function groupone_set_onboarding_dismissed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
		return;
	}
	// Store dismissed time, keep transient for 5 minutes
	// After 5 minutes, installation timestamp condition takes over
	set_transient( 'oc_onboarding_dismissed', time(), 300 );
	wp_send_json_success();
}
add_action( 'wp_ajax_oc_set_onboarding_dismissed', 'groupone_set_onboarding_dismissed' );

/**
 * Check if consent banner is eligible to display
 * Cached per request — ensures body class and template hooks stay in sync using static $eligible
 */
function groupone_is_consent_banner_eligible() {
	static $eligible = null;

	// Return cached result only for same request if already computed
	if ( ! is_null( $eligible ) ) {
		return $eligible;
	}

	// Must be a valid onecom plugin page with admin capability
	if ( ! groupone_is_onecom_plugins_page() || ! current_user_can( 'manage_options' ) ) {
		return $eligible = false;
	}

	// Skip if consent already resolved
	$data_consent_status = get_site_option( 'onecom_data_consent_status', false );
	$resolved_statuses   = array( '0', '1', 'dismissed' );
	if ( in_array( $data_consent_status, $resolved_statuses, true ) ) {
		return $eligible = false;
	}

	// Hard block: Consent banner is applicable for new installations (with a timestamp)
	$timestamp = get_option( 'onecom_installation_timestamp' );
	if ( ! $timestamp ) {
		return $eligible = false;
	}

	// Condition 1: installation timestamp has passed 5 minutes
	$timestamp_eligible = ( time() - (int) $timestamp ) >= 300;

	// Condition 2: onboarding dismissed 5+ seconds ago (transient expires after 5 minutes)
	$onboarding_dismissed_at = get_transient( 'oc_onboarding_dismissed' );
	$onboarding_eligible     = $onboarding_dismissed_at && ( time() - (int) $onboarding_dismissed_at ) >= 5;

	// Eligible if EITHER condition is met
	if ( ! $timestamp_eligible && ! $onboarding_eligible ) {
		return $eligible = false;
	}

	return $eligible = true;
}

/**
 * Render consent banner template in footer
 * Fires late — after page content is rendered
 */
function groupone_data_consent_banner() {
	if ( ! groupone_is_consent_banner_eligible() ) {
		return;
	}

	require_once GROUPONE_WP_PATH . 'modules/home/templates/data-consent-banner.php';
}
add_action( 'admin_footer', 'groupone_data_consent_banner' );

/**
 * Add body class when consent banner is eligible
 * Fires early — before <body> tag is rendered
 */
function groupone_consent_banner_admin_body_class( $classes ) {
	if ( groupone_is_consent_banner_eligible() ) {
		$classes .= ' oc-consent-banner-active';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'groupone_consent_banner_admin_body_class' );

// Update Imagify promoter toggle status
add_action( 'wp_ajax_oc_update_imagify_promoter_status', 'oc_update_imagify_promoter_status' );
function oc_update_imagify_promoter_status() {
	check_ajax_referer( 'oc_home_ajax', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized' );
		return;
	}

	$status  = isset( $_POST['imagify_promoter_status'] ) ? sanitize_text_field( $_POST['imagify_promoter_status'] ) : false;
	$allowed = array( '0', '1' );
	if ( ! in_array( (string) $status, $allowed, true ) ) {
		wp_send_json_error( 'Invalid status' );
		return;
	}
	update_site_option( 'onecom_imagify_promoter_enabled', $status );
	wp_send_json_success( array( 'imagify_promoter_status' => $status ) );
}

// Update consent banner status
add_action( 'wp_ajax_oc_update_consent_status', 'oc_update_consent_status' );
function oc_update_consent_status() {
	$status = isset( $_POST['consent_status'] ) ? sanitize_text_field( $_POST['consent_status'] ) : false;
	// Validate only allowed values are saved
	$allowed = array( '0', '1', 'dismissed' );
	if ( ! in_array( (string) $status, $allowed, true ) ) {
		wp_send_json_error( 'Invalid status' );
		return;
	}

	update_site_option( 'onecom_data_consent_status', $status );
	wp_send_json_success( array( 'message' => 'Status updated', 'consent_status' => $status ) );
}

function groupone_get_privacy_policy_url(): string {
	$locale = get_locale();

	$supported_locales = array(
		'en_US' => 'https://www.one.com/en-gb/legal/privacy/',
		'da_DK' => 'https://www.one.com/da-dk/juridiske-oplysninger/privatlivspolitik/',
		'de_DE' => 'https://www.one.com/de-de/legal/datenschutz/',
		'es_ES' => 'https://www.one.com/es-es/legal/privacidad/',
		'fr_FR' => 'https://www.one.com/fr-fr/legal/confidentialite/',
		'fi'    => 'https://www.one.com/fi-fi/legal/tietosuojaseloste/',
		'it_IT' => 'https://www.one.com/it-it/legale/privacy/',
		'nl_NL' => 'https://www.one.com/nl-nl/legal/privacy/',
		'nb_NO' => 'https://www.one.com/nb-no/juridisk-informasjon/personvern/',
		'pt_PT' => 'https://www.one.com/pt-pt/legal/privacidade/',
		'sv_SE' => 'https://www.one.com/sv-se/juridisk-information/integritet/',
	);

	if ( ! array_key_exists( $locale, $supported_locales ) ) {
		return $supported_locales['en_US'];
	}

	return $supported_locales[ $locale ];
}

// Request Premium care via API
add_action( 'wp_ajax_oc_request_premium_care', 'oc_request_premium_care' );
function oc_request_premium_care() {
	if ( ! isset( $_POST['premium_wp_request'] ) ) {
		wp_send_json_error( array( 'message' => 'Missing required data' ) );
	}

	// Get logged-in user info
	$current_user = wp_get_current_user();
	$user_name    = $current_user->display_name;
	$user_email   = $current_user->user_email;

	// Prepare data
	$text = isset( $_POST['text'] ) ? sanitize_text_field( $_POST['text'] ) : '';
	$final_description   = "Description: " .$text . "\n\nWordPress user name: " . $user_name . "\nWordPress user email: " . $user_email . "\nWordPress installation: " . GROUPONE_HTTP_HOST;

	$payload = array(
		'text'    => $final_description,
		'subject' => 'I want to learn more about Premium WP Care',
	);

	// Send API request (replace URL with actual endpoint)
	$response = wp_remote_post(
		WR_PWPC_API,
		array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $payload ),
			'method'  => 'POST',
			'timeout' => 60,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error(
			array(
				'message' => 'API error',
				'error'   => $response->get_error_message(),
			)
		);
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! empty( $body['success'] ) ) {
		set_transient( 'onecom_premium_wp_care_request', '1', 24 * HOUR_IN_SECONDS );
		wp_send_json_success(
			array(
				'message'      => 'Request submitted successfully',
				'api_response' => $body,
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message'      => 'API call failed',
				'api_response' => $body,
			)
		);
	}
}

// Fetch premium wp care addon info via feature endpoint or transient
function groupone_premium_wp_care_addon_info( $force = false, $domain = '' ) {
	$premium_wp_care_addon_info = get_site_transient( 'onecom_pwpc_addon_info' );
	if ( ! empty( $premium_wp_care_addon_info ) && false === $force && isset( $premium_wp_care_addon_info['success'] ) && $premium_wp_care_addon_info['success']) {
		return $premium_wp_care_addon_info;
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

	$api_url = is_cluster_domain() ? PWPC_ADDON_CLUSTER_API : PWPC_ADDON_API;
	$result  = wp_remote_get( $api_url, array( 'timeout' => 60 ) );

	// Handle errors first
	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			array(
				'code'    => 500,
				'message' => $result->get_error_message(),
			),
			500
		);
	}

	$response_code = wp_remote_retrieve_response_code( $result );
	$response_body = wp_remote_retrieve_body( $result );
	$response_json = json_decode( $response_body, true );
	$response      = $response_json ?? $response_body;

	// save transient for next calls, & return latest response
	if ( isset( $response['success'] ) && $response['success']) {
		set_site_transient('onecom_pwpc_addon_info', $response, 12 * HOUR_IN_SECONDS);
	}
	return $response;
}

/**
 * Check if premium_wp_care plugin addon purchased
 */
function groupone_is_premium_wp_care_addon_purchased(): bool {
	$premium_wp_care_addon_info = groupone_premium_wp_care_addon_info();

	return (
		is_array( $premium_wp_care_addon_info ) &&
		array_key_exists( 'success', $premium_wp_care_addon_info ) &&
		$premium_wp_care_addon_info['success'] &&
		array_key_exists( 'data', $premium_wp_care_addon_info ) &&
		array_key_exists( 'product', $premium_wp_care_addon_info['data'] ) &&
		'PREMIUM_WORDPRESS_CARE' === $premium_wp_care_addon_info['data']['product'] &&
		array_key_exists( 'source', $premium_wp_care_addon_info['data'] ) &&
		'PURCHASED' === $premium_wp_care_addon_info['data']['source']
	);
}
