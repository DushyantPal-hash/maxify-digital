<?php

/**
 * Defines admin settings functions
 *
 * @since      0.1.0
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Admin_Settings_API
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GroupOne_UC_Admin_Settings_API {

	public $premium_inline_msg;

	// Constructor
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	// initialize settings array
	protected $settings_sections = array();
	protected $settings_fields   = array();



	/**
	 * Enqueue scripts and styles
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
	}

	// Set settings sections
	public function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	// Set settings fields
	public function set_fields( $fields ) {
		$this->settings_fields = $fields;
		return $this;
	}

	// Function to check if given premium feature (default: stg) available or not
	public function oc_premium( $call_for = null ) {
		$features = groupone_set_premi_flag();
		if ( null === $call_for ) {
			if ( isset( $features['data'] ) && ( ! empty( $features['data'] ) ) && ( in_array( 'MWP_ADDON', $features['data'], true ) ) ) {
				return true;
			} else {
				return false;
			}
		} elseif ( 'all_plugins' === $call_for ) {
			if ( ( isset( $features['data'] ) && ( empty( $features['data'] ) ) )
				|| ( in_array( 'ONE_CLICK_INSTALL', $features['data'], true ) )
				|| ( in_array( 'MWP_ADDON', $features['data'], true ) ) ) {
				return true;
			}
			return false;
		}
	}


	// Sanitize callback for Settings API
	public function sanitize_options( $options ) {

		if ( ! $options ) {
			return $options;
		}

		/**
		 * Server side premium settings validation for non-premium package
		 * * Reset, Retain or Update settings based on package
		 */

		$old_settings = get_option( ONECOM_UC_OPTION_FIELD );

		if ( $this->oc_premium() === false ) {
			/**
			 * If key exists, and design is selected design is not non-premium
			 * * Or If key does not exist (because premium selected but disabled)
			 * * * retain old design (even if it is premium)
			 */
			$non_prem_designs = array( 'theme-1', 'theme-2', 'theme-3' );
			if ( ( array_key_exists( 'uc_theme', $options ) &&
					! in_array( $options['uc_theme'], $non_prem_designs, true ) ) ||
				! array_key_exists( 'uc_theme', $options )
			) {
				$options['uc_theme'] = $old_settings['uc_theme'];
			}

			/**
			 * If favicon exists and changed (but not allowed)
			 * [Case: Cu modified using html source]
			 * * * revert to old favicon
			 */
			if ( ( array_key_exists( 'uc_favicon', $options ) &&
					trim( $options['uc_favicon'] ) !== '' ) &&
				$options['uc_favicon'] !== $old_settings['uc_favicon']
			) {
				$options['uc_favicon'] = $old_settings['uc_favicon'];
			}



			/**
			 * If key exists, and selected setting is not non-premium
			 * Or If key does not exist (because premium selected but disabled)
			 * * * retain old settings (even if it is premium)
			 */
			if ( ( array_key_exists( 'uc_timer_action', $options ) &&
				'no-action' !== $options['uc_timer_action'] ) ||
				! array_key_exists( 'uc_timer_action', $options )
			) {
				$options['uc_timer_action'] = $old_settings['uc_timer_action'];
			}

			if ( ( array_key_exists( 'uc_seo_title', $options ) &&
					trim( $options['uc_seo_title'] ) !== '' ) ||
				! array_key_exists( 'uc_seo_title', $options )
			) {
				$options['uc_seo_title'] = $old_settings['uc_seo_title'];
			}

			if ( ( array_key_exists( 'uc_seo_description', $options ) &&
					trim( $options['uc_seo_description'] ) !== '' ) ||
				! array_key_exists( 'uc_seo_description', $options )
			) {
				$options['uc_seo_description'] = $old_settings['uc_seo_description'];
			}

			if ( ( array_key_exists( 'uc_footer_scripts', $options ) &&
					trim( $options['uc_footer_scripts'] ) !== '' ) ||
				! array_key_exists( 'uc_footer_scripts', $options )
			) {
				$options['uc_footer_scripts'] = $old_settings['uc_footer_scripts'];
			}
		}

		return $options;
	}



}
