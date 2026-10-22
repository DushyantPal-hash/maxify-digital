<?php

/**
 * Deals with admin pages
 */
class OnecomAdminPages extends OnecomHealthMonitor {
	use OnecomHMTexts;
	private $page_name = 'Health Monitor';

	public function init() {
		add_action( 'admin_menu' , array( $this , 'report_page' ) );
		add_action( 'network_admin_menu' , array( $this , 'report_page' ) );
		add_action( 'admin_enqueue_scripts' , array( $this , 'page_scripts' ) );
		add_action( 'admin_menu' , array( $this , 'groupone_remove_duplicate_menu' ) , 20 );
	}

	public function report_page() {
		add_submenu_page(
			$this->text_domain ,
			__( $this->page_name , 'onecom-wp' ) ,
			'<span id="onecom_health_monitor">' . __( $this->page_name , 'onecom-wp' ) . '</span>' ,
			'manage_options' ,
			'onecom-wp-health-monitor' ,
			array( $this , 'report_page_callback' ) ,
			0
		);

		add_submenu_page(
			'',
			__( 'Discouraged Plugins', 'onecom-wp' ),
			'',
			'manage_options',
			'onecom-wp-discouraged-plugins',
			array( $this, 'discouraged_plugins_page_callback' )
		);
	}

	/**
	 * @return void
	 * function to remove duplicate HM menu entries in  case of outdated validator
	 */
	public function groupone_remove_duplicate_menu(): void {
		global $submenu;

		$parent_slug  = $this->text_domain;
		$submenu_slug = 'onecom-wp-health-monitor';

		if ( isset( $submenu[ $parent_slug ] ) ) {
			$submenu_items = &$submenu[ $parent_slug ];
			$found_count   = 0;

			foreach ( $submenu_items as $index => $menu_item ) {
				if ( $menu_item[2] === $submenu_slug ) {
					$found_count ++;
					if ( $found_count > 1 ) {
						// Remove duplicate submenu
						unset( $submenu_items[ $index ] );
					}
				}
			}

			// Re-index the array to avoid issues with missing keys
			$submenu[ $parent_slug ] = array_values( $submenu_items );
		}
	}

	public function report_page_callback() {
		if ( is_multisite() ) {
			include_once $this->module_path . 'templates/multisite_support_banner.php';
		} else {
			include_once $this->module_path . 'templates/oc_sh_health_monitor.php';
		}
	}

	public function discouraged_plugins_page_callback() {
		include_once $this->module_path . 'templates/oc_sh_discouraged_plugins.php';
	}

	public function page_scripts( $hook_suffix ) {
		if ( $hook_suffix === 'admin_page_onecom-wp-discouraged-plugins' ) {
			$this->enqueue_discouraged_plugins_scripts();
			return;
		}

		if ( $hook_suffix === 'one-com_page_onecom-wp-health-monitor' || $hook_suffix === 'adminone-com_page_onecom-wp-health-monitor' || $hook_suffix === 'one-com_page_onecom-wp-staging-blocked') {
			if ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true' ) {
				$folder      = '';
				$extenstion  = '';
				$script_path = GROUPONE_WP_URL . 'modules/health-monitor/assets/';
			} else {
				$folder      = 'min-';
				$extenstion  = '.min';
				$script_path = GROUPONE_WP_URL . 'assets/';
			}
			wp_enqueue_script( 'updates' );
			wp_enqueue_style( 'oc_sh_fonts', GROUPONE_WP_URL . 'assets/css/onecom-fonts.css' );
			wp_enqueue_style( 'oc_sh_css', $script_path . $folder . 'css/site-scanner' . $extenstion . '.css' );
			wp_enqueue_script(
				'oc_sh_js' ,
				$script_path . $folder . 'js/oc_sh_script' . $extenstion . '.js' ,
				array(
					'jquery' ,
					'wp-theme-plugin-editor' ,
				) ,
				null ,
				true
			);
			$cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'shell' ) );
			wp_enqueue_script( 'wp-theme-plugin-editor' );
			wp_enqueue_style( 'wp-codemirror' );
			wp_localize_script(
				'oc_sh_js' ,
				'oc_constants' ,
				array(
					'GROUPONE_RESOLVED'         => GROUPONE_RESOLVED,
					'GROUPONE_OPEN'             => GROUPONE_OPEN,
					'ocsh_page_url'       => menu_page_url( 'adminone-com_page_onecom-wp-health-monitor', false ),
					'ocsh_scan_btn'       => __( 'Scan again', 'onecom-wp' ),
					'nonce'               => wp_create_nonce( HT_NONCE_STRING ),
					'nonce_error'         => __( 'An error occurred. Please reload the page and try again', 'onecom-wp' ),
					'cm_settings'         => $cm_settings,
					'resetHtaccess'       => base64_encode(
						'<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|html|htm|shtml|sh|cgi|suspected)$">
    deny from all
</FilesMatch>'
					),
					'checks'              => $this->checks,
					'error_empty'         => __( 'This field cannot be empty', 'onecom-wp' ),
					'error_empty_sitekey' => __( 'Please, enter your site key.', 'onecom-wp' ),
					'error_length'        => __( 'The entered value seems to be incomplete.', 'onecom-wp' ),
					'ajaxurl'             => $this->groupone_is_premium() ? add_query_arg(
						array(
							'premium' => 1 ,
						) ,
						admin_url( 'admin-ajax.php' )
					) : admin_url( 'admin-ajax.php' ) ,
					'asset_url'           => GROUPONE_WP_URL ,
					'empty_list_messages' => array(
						'todo'    => __( 'Awesome, you completed all recommendations!' , 'onecom-wp' ) ,
						'done'    => __( 'You haven\'t completed any recommendations. See the <span data-target="todo">To do</span> section.' , 'onecom-wp' ) ,
						'ignored' => __( 'You haven’t ignored any recommendations.' , 'onecom-wp' ) ,
					) ,
					'text'                => array(
						'unignore'        => __( 'Unignore' , 'onecom-wp' ) ,
						'ignore'          => __( 'Ignore from future scans' , 'onecom-wp' ) ,
						'ignore_critical' => __( 'Ignore for 24 hours' , 'onecom-wp' ) ,
					) ,
					'current_screen'      => get_current_screen()->base ,
				)
			);

			wp_enqueue_script(
				'oc_hm_script' ,
				GROUPONE_WP_URL . '/assets/js/block-scripts/oc-hm-script.js' ,
				[ 'wp-element' ] ,
				GROUPONE_WP_VERSION ,
				true
			);

			$scan_result      = get_site_transient( 'ocsh_site_scan_result' );
			$previous_scan    = get_site_transient( 'ocsh_site_previous_scan' );
			// WPIN-8931: a scan is written one check at a time across many requests, so a
			// non-empty $scan_result is not necessarily a *finished* one. Treating a
			// partial/in-progress snapshot as "no scan needed" is what let the todo/done
			// counts and score oscillate between page loads that reported the same
			// "Last scan" time. is_scan_result_complete() guards against that.
			$needs_scan       = ( empty( $scan_result ) || ! is_array( $scan_result ) || empty( $previous_scan ) || ! is_array( $previous_scan ) || ! $this->is_scan_result_complete( $scan_result ) );
			$last_scan_time   = is_array( $scan_result ) && isset( $scan_result['time'] ) ? $scan_result['time'] : __( 'No scan available', 'onecom-wp' );

			/* Format the last scan date time as per WP date-time settings */
			if ( is_numeric( $last_scan_time ) && function_exists( 'wp_date' ) ) {
				$frmt                     = 'l ' . get_site_option( 'date_format' ) . ' ' . get_site_option( 'time_format' );
				$tz                       = get_site_option( 'timezone_string' ) && ! empty( get_site_option( 'timezone_string' ) ) ? get_site_option( 'timezone_string' ) : 'UTC';
				$last_scan_time_localised = wp_date( $frmt, $last_scan_time, new DateTimeZone( $tz ) );
			} else {
				$last_scan_time_localised = __( 'No scan available', 'onecom-wp' );

			}

			require_once GROUPONE_WP_PATH . 'modules' . DIRECTORY_SEPARATOR . 'vulnerability-monitor' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class-ocvm.php';
			require_once GROUPONE_WP_PATH . 'modules' . DIRECTORY_SEPARATOR . 'vulnerability-monitor' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class-ocvm-admin-page.php';

			$ocvmsettings  = new GroupOne_VM();
			$ocvmadminPage = new GroupOne_VMAdmin( $ocvmsettings->get_version(), $ocvmsettings->get_OCVM() );


// get vulnerabilities count
			$count = groupone_vulns_count();

//get vm features check
			$settings = new GroupOne_VMSettings();
			$vmcheck  = $settings->isPremium();

			if ( ! class_exists( 'OnecomPluginsApi' ) ) {
				require_once GROUPONE_WP_PATH . '/modules/api/class-onecom-plugins-api.php';
			}
			$api         = new OnecomPluginsApi();
			$health_scan = $api->get_health_monitor_recent_results();
			$calc        = groupone_sh_calculate_score( $health_scan );
			$score       = round( $calc['score'] );
			$todo_count  = $calc['todo'];
			unset( $health_scan['time'] );
			$ignored       = get_site_option( 'oc_marked_resolved', array() );
			$ignored_count = is_countable( $ignored ) ? count( $ignored ) : 0;
			$done_count    = count( $health_scan ) - ( $todo_count + $ignored_count );
			$template       = new OnecomTemplate();
			$status        = $template->get_status_with_score( $score );
			$is_mwp         = $template->groupone_is_premium();
			$this->init_trait();
			$results      = is_array( $previous_scan ) ? $previous_scan : array();
			$current_user = wp_get_current_user();


			wp_localize_script(
				'oc_hm_script' ,
				'ocHMconstants' ,
				array(
					'lastScanTime' => $last_scan_time_localised,
					'adminAjaxurl'             => $this->groupone_is_premium() ? add_query_arg(
						array(
							'premium' => 1 ,
						) ,
						admin_url( 'admin-ajax.php' )
					) : admin_url( 'admin-ajax.php' ) ,
					'asset_url'           => GROUPONE_WP_URL,
					'isPremium' => $is_mwp,
					'scanResults' => array(
						'todo'   => $this->prepare_react_friendly_data( $results, 1 ), // status 1 = To Do
						'done'    => $this->prepare_react_friendly_data( $results, 0 ), // status 0 = Done
						'ignored' => $this->prepare_react_friendly_data( $results, 3 ), // status 3 = Ignored
					),
					'texts' => array(
						'ignore'         => $this->ignore_text,
						'unignore'       => $this->unignore_text,
						'ignoreCritical' => $this->ignore_critical_text,
					),
					'score' => $score,
					'vulncount' => $count,
					'todoCount' => $todo_count,
					'doneCount' => $done_count,
					'ignoreCount' => $ignored_count,
					'scoreStatus' => $status,
					'imageDIR'=> GROUPONE_WP_URL,
					'userNonce' => wp_create_nonce( 'ocsh_edit_username_' . $current_user->user_login ),
					'currentUser' => $current_user->user_login,
					'pluginPageURL' => admin_url( 'admin.php?page=onecom-wp-health-monitor' ),
					'labelTodo'=> __( 'To do' , 'onecom-wp' ),
					'labelDone'=> __( 'Done' , 'onecom-wp' ) ,
					'labelIgnore'=> __( 'Ignored' , 'onecom-wp' ) ,
					'descIgnore'=> __( 'All recommended action items that were marked to be ignored in future scans.' , 'onecom-wp' ) ,
					'labelVulnerabilities'=> __( 'Vulnerabilities' , 'onecom-wp' ) ,
					'descTodo'=> __( 'Recommendations for common security and performance optimisations you can implement to make your site more protected against hackers and bots. We run automatic scans daily.' , 'onecom-wp' ) ,
					'labelDone'=> __( 'Done' , 'onecom-wp' ) ,
					'fixsuccess'=> __( 'Your item was solved and moved to “Done”.' , 'onecom-wp' ) ,
					'fixError'=> __( 'Couldn’t perform action.' , 'onecom-wp' ) ,
					'revertText'     => __( 'Revert', 'onecom-wp' ),
					'undoSuccess'    => __( 'The item was reverted and moved to “To do”.', 'onecom-wp' ),
					'undoError'      => __( 'Couldn’t revert item.', 'onecom-wp' ),
					'undoException'  => __( 'An error occurred while undoing the action.', 'onecom-wp' ),
					'ignoreText'     => __( 'Always ignore', 'onecom-wp' ),
					'unignoreText'   => __( 'Unignore', 'onecom-wp' ),
					'scoreLabel' => __('Score', 'onecom-wp'),
					'scoreTooltip' => __('Your site’s combined score based on security, performance and best practices.', 'onecom-wp'),
					'securityPerformanceNote' => __('for security and performance', 'onecom-wp'),
					'todoLabel' => __('To do', 'onecom-wp'),
					'todoTooltip' => __('Our recommendations to optimise your site’s security and performance.', 'onecom-wp'),
					'vulnLabel' => __('Vulnerabilities', 'onecom-wp'),
					'vulnTooltip' => __('Detected insecure plugins, themes, or WordPress core files.', 'onecom-wp'),
					'itemsText' => __('items', 'onecom-wp'),
					'actionInProgress' => __('Action in progress...', 'onecom-wp'),
					'scanNow'         => __('Scan now', 'onecom-wp'),
					'scanning'        => __('Scanning...', 'onecom-wp'),
					'labelLastScan'   => __('Last scan:', 'onecom-wp'),
					'labelStatus'   => __('Status', 'onecom-wp'),
					'labelDelete'   => __('Delete', 'onecom-wp'),
					'labelSave'   => __('Save', 'onecom-wp'),
					'labelConfirm'   => __('Confirm', 'onecom-wp'),
					'usermodaltitle'   => __('Your WP Admin username was successfully changed', 'onecom-wp'),
					'changeUsername'   => __('Change your username', 'onecom-wp'),
					'descUsername'   => __('Use a personal username so your WordPress account and website become more protected against potential hacking.', 'onecom-wp'),
					'confirmUsername'   => __('When you confirm your new username, you’ll be logged out immediately and will be asked to log back in to WP Admin with your new username.', 'onecom-wp'),
					'currUsername'   => __('Current WP Admin username', 'onecom-wp'),
					'newUsername'   => __('New WP Admin username', 'onecom-wp'),
					'labelLogin'   => __('Go to Login', 'onecom-wp'),
					'labelCancel'   => __('Cancel', 'onecom-wp'),
					'errEmptyUsername'   => __('Enter a username.', 'onecom-wp'),
					'errUsernameFormat'   => __('You can only use letters, numbers, underscores, hyphens, periods, and the @ symbol in your username.', 'onecom-wp'),
					'errorDuplicate'   => __('Enter a different username than your current one.', 'onecom-wp'),
					'usernameChanged'   => __('Your WP Admin username was changed from %s to %s. Please log back in to your account with the new username.', 'onecom-wp'),


					'needsScan' => $needs_scan,
					'no_audits_messages' => [
						'todo' => [
							'title'       => __('No items to do!', 'onecom-wp'),
							'description' => __('Your site’s score is great and there is currently no action needed to improve the security or performance.', 'onecom-wp'),
						],
						'done' => [
							'title'       => __('No item resolved yet', 'onecom-wp'),
							'description' => __('Once you resolve the first item from the “To do” column, it will appear here.', 'onecom-wp'),
						],
						'ignored' => [
							'title'       => __('No items to show', 'onecom-wp'),
							'description' => __('Once the first item will be marked to be ignored in future scans, it will appear here.', 'onecom-wp'),
						],
						'fallback' => [
							'title'       => __('Nothing here', 'onecom-wp'),
							'description' => __('There is no data to display.', 'onecom-wp'),
						],
					],

				)
			);

		}
	}

	private function enqueue_discouraged_plugins_scripts() {
		if ( SCRIPT_DEBUG || SCRIPT_DEBUG == 'true' ) {
			$folder     = '';
			$extenstion = '';
			$script_path = GROUPONE_WP_URL . 'modules/health-monitor/assets/';
		} else {
			$folder     = 'min-';
			$extenstion = '.min';
			$script_path = GROUPONE_WP_URL . 'assets/';
		}

		wp_enqueue_style( 'oc_sh_fonts', GROUPONE_WP_URL . 'assets/css/onecom-fonts.css' );
		wp_enqueue_style( 'oc_sh_css', $script_path . $folder . 'css/site-scanner' . $extenstion . '.css' );
		add_thickbox();

		wp_enqueue_script(
			'oc_hm_script',
			GROUPONE_WP_URL . '/assets/js/block-scripts/oc-hm-script.js',
			[ 'wp-element' ],
			GROUPONE_WP_VERSION,
			true
		);

		wp_localize_script(
			'oc_hm_script',
			'ocHMdiscouragedVars',
			array(
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'imageURL'                  => GROUPONE_WP_URL,
				'discouragedListUrl'        => function_exists( 'groupone_generic_locale_link' ) ? groupone_generic_locale_link( 'discouraged_guide', get_locale() ) : 'https://help.one.com/hc/en/articles/115005586029-Discouraged-WordPress-plugins',
				'headingDiscouragedPlugins' => __( 'Discouraged plugins', 'onecom-wp' ),
				'viewDiscouragedPlugins'    => __( 'View discouraged plugins', 'onecom-wp' ),
				'discouragedPluginDesc'     => __( 'Keep your WordPress site running smoothly. We review your plugins and list those we don’t recommend using.', 'onecom-wp' ),
				'wellDone'                  => __( 'Well done!', 'onecom-wp' ),
				'noDiscouragedPlugins'      => __( 'No discouraged plugins found on your site.', 'onecom-wp' ),
				'deactivateLabel'           => __( 'Deactivate', 'onecom-wp' ),
				'deactivatingLabel'         => __( 'Deactivating', 'onecom-wp' ),
				'moreDetailsLabel'          => __( 'More details', 'onecom-wp' ),
				'successMessage'            => __( 'Plugin deactivated.', 'onecom-wp' ),
				'errorMessage'              => __( 'Couldn’t deactivate plugin.', 'onecom-wp' ),
				'backToHM'                  => __( 'Back', 'onecom-wp' ),
				'hmPageURL'                 => admin_url( 'admin.php?page=onecom-wp-health-monitor' ),
			)
		);
	}

	public function prepare_react_friendly_data( array $results, int $status ): array {
		$prepared = array();

		foreach ( $results as $check => $data ) {
			$is_ignored = $this->is_ignored( $check );
			// A missing/non-int status must never default to Done (0) - fail open, not resolved.
			if ( ! isset( $data['status'] ) || ! is_int( $data['status'] ) ) {
				$data['status'] = $this->flag_open;
			}
			$actual_status = $data['status'];

			if ( $status === 3 && ! $is_ignored ) {
				continue;
			} elseif ( $status !== 3 ) {
				if ( $is_ignored || $actual_status !== $status ) {
					continue;
				}
			}

			$prepared[] = $this->prepare_single_check_data( $check, $data, $status );
		}

		return $prepared;
	}


}
