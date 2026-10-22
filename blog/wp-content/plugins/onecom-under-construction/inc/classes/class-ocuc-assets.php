<?php

/**
 * Defines assets functions
 *
 * This class includes all assets for admin and public.
 *
 * @since      0.1.0
 * @package    Under_Construction
 * @subpackage OCUC_Assets
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OCUC_Assets {


	/**
	 * Constructor to add actions for enqueue styles and scripts
	 */
	public function init_assets() {
		add_action( 'admin_head', array( $this, 'uc_custom_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action('wp_ajax_ocmm_render_editor', array( $this,'ocmm_render_editor'));

	}

	/**
	 * Enqueue admin styles.
	 */
	public function admin_styles() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Register admin styles.
		wp_register_style( 'onecom_uc_flatpickr_styles', ONECOM_UC_DIR_URL . 'assets/css/flatpickr.css', array(), ONECOM_UC_VERSION );
		wp_register_style( 'onecom_uc_admin_styles', ONECOM_UC_DIR_URL . 'assets/css/admin.css', array(), ONECOM_UC_VERSION );
		wp_register_style( 'onecom_uc_select2_styles', ONECOM_UC_DIR_URL . 'assets/css/select2.min.css', array(), ONECOM_UC_VERSION );

		// Enqueue style only on required plugin pages
		if ( in_array( $screen_id, array( 'toplevel_page_onecom-wp-under-construction', 'toplevel_page_onecom-wp-under-construction-network' ), true ) ) {
			wp_enqueue_style( 'onecom_uc_flatpickr_styles' );
			wp_enqueue_style( 'onecom_uc_admin_styles' );
			wp_enqueue_style( 'onecom_uc_select2_styles' );
		}

		return null;
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_scripts() {
		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		$settings_api = new OCUC_Admin_Settings_API();

		$jquery_core_handle  = 'jquery';
		$wpcoloralpha_handle = 'wp-color-picker-alpha-uc';
		//Admin uc js
		wp_register_script( 'onecom_uc_admin_script', ONECOM_UC_DIR_URL . 'assets/js/admin.js', array( $jquery_core_handle ), ONECOM_UC_VERSION, true );
		wp_register_script( 'oc-mm-admin-script' ,
			ONECOM_UC_DIR_URL . 'assets/js/block-scripts/oc-maintenance-mode.js'  ,
			['wp-element', 'wp-media-utils'],
			ONECOM_UC_VERSION ,
			true );

		// Enqueue script only on plugin pages
		if ( in_array( $screen_id, array( 'toplevel_page_onecom-wp-under-construction' ), true ) ) {
			wp_enqueue_style( 'gravity-css', ONECOM_UC_DIR_URL . 'assets/css/one.min.css', array(), ONECOM_UC_VERSION );
			wp_enqueue_script('oc-mm-admin-script' );


			$countDownOptions = array(
				array(
					'value' => 'no-action',
					'label' => __( 'Do nothing', 'onecom-uc' ),
				),
				array(
					'value' => 'hide',
					'label' => __( 'Hide countdown, keep Maintenance Mode', 'onecom-uc' ),
				),
				array(
					'value' => 'disable',
					'label' => __( 'Deactivate Maintenance Mode, show my website', 'onecom-uc' ),
				),
			);
			wp_localize_script( 'oc-mm-admin-script', 'ocmmSettings', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'site_url' => get_site_url(),
				'imageDIR' => ONECOM_UC_DIR_URL,
				'isPremium' => $settings_api->oc_premium(),
				'siteTitle' => get_bloginfo('name'),
				'mmTagline' =>__('Show a maintenance page while you’re working on your website.', 'onecom-uc'),
				'activeMsg'=> __('Your maintenance page is active for visitors.','onecom-uc'),
				'notactiveMsg'=> __('Your maintenance page is not active for visitors.','onecom-uc'),
				'hideLabel' => __('Hide website from search results', 'onecom-uc'),
				'hideDescription' => __('Your site won’t be indexed by search engines and won’t show up in search results.', 'onecom-uc'),
				'optionsLabel' => __('Options', 'onecom-uc'),
				'allowLabel' => __('Allowlist users', 'onecom-uc'),
				'allowDescription' => __('Your WordPress users will see the normal site, instead of the Maintenance Mode.', 'onecom-uc'),
				'countdownLabel' => __('Countdown timer', 'onecom-uc'),
				'countdownDesc' => __('Show visitors when your site will be live.', 'onecom-uc'),
				'contentLabel' => __('Content', 'onecom-uc'),
				'contentShortcut' => __('Edit the content on your maintenance page.', 'onecom-uc'),
				'contentDesc' => __('Add the text that should show on your page when Maintenance Mode is active.', 'onecom-uc'),
				'appearanceLabel' => __('Appearance', 'onecom-uc'),
				'appearanceDesc' => __('Customise the design of your maintenance page.', 'onecom-uc'),
				'appearancePageDesc' => __('Choose how your page should look when Maintenance Mode is active.', 'onecom-uc'),
				'allowTitle' => __('Allowlist posts and pages', 'onecom-uc'),
				'allowDesc' => __('Add posts and pages that will always be visible to your visitors.', 'onecom-uc'),
				'allowPageDesc' => __('Select pages and posts that should stay available for your website visitors when Maintenance Mode is active. This can, for example, be useful for login or lost-password pages.', 'onecom-uc'),
				'advanceTitle' => __('Advanced settings', 'onecom-uc'),
				'advancePageDesc' => __('Personalise your maintenance page with custom CSS and scripts.', 'onecom-uc'),
				'countdownTitle' => __('Countdown timer', 'onecom-uc'),
				'countdownEnds' => __('After countdown ends', 'onecom-uc'),
				'labelSave' => __('Save', 'onecom-uc'),
				'labelSaving' => __('Saving', 'onecom-uc'),
				'labelBack' => __('Back', 'onecom-uc'),
				'labelDesc' => __('Description', 'onecom-uc'),
				'labelMedia' => __('Add media'),
				'countdownnotice' => __( 'Select the date and time when you plan to release your website. Current time format:', 'onecom-uc' ) .
									 current_time( 'Y-m-d H:i' ) . '. <a href="' . admin_url( 'options-general.php' ) . '" target="_blank">' .
									 __( 'Change timezone', 'onecom-uc' ) .
									 '</a>',



				'labelActive' => __('Active', 'onecom-uc'),
				'labelActivate' => __('Activate', 'onecom-uc'),
				'labelDeactivate' => __('Deactivate', 'onecom-uc'),
				'labelActivating' => __('Activating', 'onecom-uc'),
				'labelDeactivating' => __('Deactivating', 'onecom-uc'),
				'labelInactive' => __('Inactive', 'onecom-uc'),
				'labelDate' => __('End date and time', 'onecom-uc'),
				'advanceDesc' => __('Add your own code.', 'onecom-uc'),
				'countDownOptions' => $countDownOptions,
				'postTypes' => $this->ocmm_get_post_types_data(),
				'excluded'  => get_option( 'onecom_under_construction_info' )['exclude_pages'] ?? [],
				'initialSettings' => get_option( 'onecom_under_construction_info' ),
				'nonce' => wp_create_nonce('ocmm_save_settings'),
				'activationSuccess' => __('Maintenance Mode was activated.', 'onecom-uc'),
				'activationFail' => __('Couldn’t activate Maintenance Mode.', 'onecom-uc'),
				'deactivationSuccess' => __('Maintenance Mode was deactivated.', 'onecom-uc'),
				'deactivationFail' => __('Couldn’t deactivate Maintenance Mode.', 'onecom-uc'),
					'noticeSuccess' => __('Your settings were saved. Remember to empty your cache if you’re using a caching plugin.', 'onecom-uc'),
					'noticeError' => __('Couldn’t save your settings. Please reload the page and try again. If the issue persists, please contact our support.', 'onecom-uc'),
					'mwpTitle' => __('This is a Managed WP feature','onecom-uc'),
					'mwpCountdownDesc' => __('Upgrade now to get access to the Countdown timer and many other features.','onecom-uc'),
					'mwpAllowDesc' => __('Upgrade now to allowlist posts and pages and to get access to other great features.','onecom-uc'),
					'mwpAdvanced' => __('Upgrade now to add your own footer script and to get access to other great features.','onecom-uc'),
					'mwpFavicon' => __('Upgrade now to add your own site icon and to get access to other great features.','onecom-uc'),
					'mwpUpsellBtn' => __('Upgrade now','onecom-uc'),
					'httpModeOn' => __('Your website is hidden from search results.','onecom-uc'),
					'httpModeOnError' => __('Couldn’t activate hiding your website from search results.','onecom-uc'),
					'httpModeOff' => __('Hiding your website from search results was deactivated.','onecom-uc'),
					'httpModeOffError' => __('Couldn’t deactivate hiding your website from search results.','onecom-uc'),
					'allowUserOn' => __('Allowlisting users was activated.','onecom-uc'),
					'allowUserOff' => __('Allowlisting users was deactivated.','onecom-uc'),
					'allowUserOnError' => __('Couldn’t activate allowlisting users.','onecom-uc'),
					'allowUserOffError' => __('Couldn’t deactivate allowlisting users.','onecom-uc'),
					'validationURLErr' => __('Enter a URL in the format https://www.example.com.','onecom-uc'),
					'placeholder' => __('Something is happening. Check in later!','onecom-uc'),
					'labelHeadline' => __('Headline','onecom-uc'),
					'labelSeo' => __('SEO title','onecom-uc'),
					'descriptionSeo' => __('Search engines display the first 50-65 characters of the title in the search results.','onecom-uc'),
					'labelSeoMeta' => __('SEO meta description','onecom-uc'),
					'descSeoMeta' => __('The recommended length for SEO meta description is between 120-160 characters.','onecom-uc'),
					'labelcopyright' => __('Copyright text','onecom-uc'),
					'labelsignup' => __('Show a signup form','onecom-uc'),
					'descriptionsignup' => __('Collect email addresses in a signup form and build a mailing list to send updates to visitors interested in your website. Note: You need to select “Anyone can register” in the membership section in the general settings for the form to work.','onecom-uc'),
					'placeholderCopyright' => __('Copyright © {year}. All rights reserved','onecom-uc'),
					'labelbackground' => __('Background colour','onecom-uc'),
					'descbackground' => __('Choose a background colour. If you don’t add a background image, this colour will be shown.','onecom-uc'),
					'labelPrimary' => __('Primary colour','onecom-uc'),
					'descPrimary' => __('Select a colour for the site title and button.','onecom-uc'),
					'labelfavicon' => __('Favicon','onecom-uc'),
					'descfavicon' => __('Add your personal favicon. Favicons are small images shown in your browser tab, next to the site title.','onecom-uc'),
					'labelLogo' => __('Logo','onecom-uc'),
					'descLogo' => __('Upload your logo. If no image is added, the site title will be displayed.','onecom-uc'),
					'labelBackgroundImg' => __('Background image','onecom-uc'),
					'descBackgroundImg' => __("Add your own background image. If you don't add one, the background colour will be shown.",'onecom-uc'),
					'labelLayout' => __('Select a layout','onecom-uc'),
					'descLayout' => __('Choose a layout and structure for your maintenance page.','onecom-uc'),
				'labelCss' => __('Custom CSS','onecom-uc'),
				'descCss' => __('Add your custom CSS code.','onecom-uc'),
				'labelHeaderScript' => __('Header scripts','onecom-uc'),
				'descHeaderScript' => __('Paste in scripts that should be placed in the header, for example, for Google Analytics.','onecom-uc'),
				'labelFooterScript' => __('Footer scripts','onecom-uc'),
				'descFooterScript' => __('Paste in scripts that should be placed in the footer, for example, for Google Analytics.','onecom-uc'),
					'headerMenuOn' => sprintf( __( 'Maintenance Mode is %sON%s', 'onecom-uc' ), '<span class="last_on">', '</span>' ),
					'headerMenuOFF' =>  __( 'Maintenance Mode is OFF', 'onecom-uc' ),
					'headerMenuTitle' => sprintf( __( 'Maintenance Mode is %sON%s', 'onecom-uc' ), '', '' ),
					'choosecolor' => __('Select color', 'onecom-uc'),
					'chooseImage' => __('Select Image', 'onecom-uc'),





			)
			);



		}

		return null;
	}

	/**
	 * Hide UC tabs initially,
	 * * else it shows unformatted tabs
	 */
	public function uc_custom_css() {
		echo '<style>
		.ddresponsiveTabsDemo {
			display: none;
		  } 
		</style>';
	}

	public function ocmm_get_post_types_data() {
		$post_types = get_post_types(
			[
				'show_ui' => true,
				'public'  => true,
			],
			'objects'
		);

		$data = [];

		foreach ( $post_types as $post_slug => $type ) {
			if ( in_array( $post_slug, [ 'attachment', 'revision', 'nav_menu_item' ], true ) ) {
				continue;
			}

			$posts = get_posts([
				'post_type'      => $post_slug,
				'post_status'    => 'publish',
				'posts_per_page' => 250,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]);

			$posts_data = array_map(
				function ( $post ) {
					return [
						'id'    => (string) $post->ID,
						'title' => get_the_title( $post ),
					];
				},
				$posts
			);

			// Prepend the "All" option
			array_unshift(
				$posts_data,
				[
					'id'    => "all-{$post_slug}",
					'title' => sprintf( __( 'All %s', 'your-text-domain' ), $type->labels->name ),
				]
			);

			$data[] = [
				'slug'  => $post_slug,
				'label' => $type->labels->name,
				'posts' => $posts_data,
			];
		}

		return $data;
	}
}
