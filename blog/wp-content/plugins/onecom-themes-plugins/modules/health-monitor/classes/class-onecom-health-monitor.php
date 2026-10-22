<?php
declare(strict_types=1);

/**
 * Deals with all the core features
 */
class OnecomHealthMonitor {

	use OnecomHMTexts;
	use OnecomCheckCategory;
	use OnecomChecksList;
	use OnecomLite;

	public $score_key  = 'score';
	public $status_key = 'status';
	public $title_key  = 'title';
	public $desc_key   = 'desc';
	public $module_path;
	public $checks              = array();
	public $flag_resolved       = 0;
	public $flag_open           = 1;
	public $flag_hidden         = 2;
	public $flag_open_critical  = 4;
	public $resolved_option     = 'oc_marked_resolved';
	public $active_plugins      = array();
	public $option_key          = 'onecom_hm_data';
	public $closing_li          = '</li>';
	public $ignored             = array();
	private $is_premium_memo    = null;
	public $saved_critical_todo = 'saved_critical_todo';
	public $raw_list_key        = 'raw_list';

	public function __construct() {
		$this->init_trait();
		$this->init_trait_category();
		$all_checks           = $this->onecom_get_checks();
		$this->active_plugins = get_option( 'active_plugins' );
		$this->module_path    = GROUPONE_WP_PATH . 'modules' . DIRECTORY_SEPARATOR . 'health-monitor' . DIRECTORY_SEPARATOR;
		if ( ! empty( $this->active_plugins ) && in_array( 'woocommerce/woocommerce.php', $this->active_plugins ) ) {
			$all_checks[] = 'woocommerce_sessions';
		}

		// Keep $this->ignored faithful to what is stored - the premium gate is an
		// entitlement/display concern and must never touch the persisted value.
		$stored         = get_option( $this->resolved_option, array() );
		$this->ignored  = is_array( $stored ) ? $stored : array();
		$this->checks = apply_filters( 'onecom-checks-list', $all_checks );
		add_filter( 'onecom_if_premium', array( $this, 'groupone_premium_filter' ) );
		add_action( 'activated_plugin', array( $this, 'redirect_user_imagify_activation' ), 10, 2 );
		$this->remove_check_from_ignored_list();
	}

	public function log_entry( string $message, $single = 0 ) {

		$uploads_dir = wp_upload_dir();
		$log_dir     = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'onecom_logs/health_monitor';
		if (
			( ! ( ( WP_DEBUG_LOG || WP_DEBUG_LOG == 'true' ) || $single == 1 ) )
			||
			! file_exists( $log_dir )
			&& is_writable( $uploads_dir['basedir'] )
			&& ! mkdir( $log_dir )
		) {
			return false;
		}
		// return if log directory not reachable
		if ( ! is_writable( $log_dir ) ) {
			error_log( "Couldn't create onecom_logs directory for logging health monitor results. Insufficient file permissions for {$log_dir}" );

			return false;
		}
		// create health report log file

		$log_file = $log_dir . DIRECTORY_SEPARATOR . 'onecom-health-monitor.log';
		$time     = date( 'Y-m-d H:i:s' );

		// first lets check if it is writable
		return file_put_contents( $log_file, '[' . $time . '] ' . $message . "\n", FILE_APPEND );
	}

	public function save_result( $stage, $oc_hm_status, $finish = 0 ): bool {
		$result = get_site_transient( 'ocsh_site_scan_result' );

		$time = time();
		if ( ! $result ) {
			$result = array();
		}
		$removed_checks = array(
			'DB',
			'vulnerability_exists',
			'login_recaptcha',
			'asset_minification',
			'logout_duration',
			'login_protection',
		);
		foreach ( $result as $check => $status ) {
			if ( in_array( $check, $removed_checks ) ) {
				unset( $result[ $check ] );
			}
		}
		$result['time']   = $time;
		$result[ $stage ] = $oc_hm_status;
		$save             = set_site_transient( 'ocsh_site_scan_result', $result, 24 * HOUR_IN_SECONDS );

		if ( $finish == 1 ) {
			unset( $result['time'] );
			$health                     = array();
			$health['issues']           = $result;
			$health[ $this->score_key ] = round( $this->calculate_score( $result )[ $this->score_key ] );

			/* save health monitor result */
			$this->log_entry( '== one.com Health Monitor Scan ==' );
			$this->log_entry( json_encode( $health ), 1 );

			( class_exists( 'GroupOne_PushStats' ) ? GroupOne_PushStats::push_health_monitor_stats_request( 'scan', 'blog', GroupOne_PushStats::get_subdomain(), '1', $health ) : '' );
		}
		return $save;
	}

	/**
	 * Whether a `ocsh_site_scan_result` transient snapshot represents a finished scan
	 * or a partial/in-progress one.
	 *
	 * A scan is written incrementally: each check callback does its own
	 * get_site_transient() -> mutate one key -> set_site_transient() round trip
	 * (see save_result() above and every wp_ajax_ocsh_check_* handler). A page
	 * render that reads the transient while a scan is still streaming in sees a
	 * torn/partial array (fewer recorded checks than the site actually has),
	 * which produces a different todo/done/score for the same "Last scan" time
	 * shown on a subsequent reload once more checks have landed (WPIN-8931).
	 *
	 * We can't do an exact key-for-key match against $this->checks because some
	 * check callbacks persist under a differently-prefixed key than the check
	 * slug itself (e.g. 'check_staging_time' vs the 'staging_time' slug) - that
	 * naming inconsistency is a separate, lower-risk cleanup. Comparing counts
	 * is a safe, conservative heuristic: it can't under-report an in-progress
	 * scan as complete, which is the failure mode that matters here.
	 *
	 * @param array|false $scan_result Value of the `ocsh_site_scan_result` transient.
	 * @return bool
	 */
	public function is_scan_result_complete( $scan_result ): bool {
		if ( empty( $scan_result ) || ! is_array( $scan_result ) ) {
			return false;
		}

		$recorded_checks = array_diff( array_keys( $scan_result ), array( 'time' ) );
		$expected_count  = ( ! empty( $this->checks ) && is_array( $this->checks ) ) ? count( $this->checks ) : 0;

		if ( 0 === $expected_count ) {
			// No known check list to compare against - don't block rendering on it.
			return true;
		}

		return count( $recorded_checks ) >= $expected_count;
	}

	public function calculate_score( $transient ) {
		if ( ! $transient || empty( $transient ) ) {
			return 0;
		}
		$time = $transient['time'];
		unset( $transient['time'] );

		$success = 0;

		foreach ( $transient as $score ) {
			if ( $score == GROUPONE_RESOLVED ) {
				$success++;
			}
		}
		$percent = round( ( ( $success * 100 ) / count( $transient ) ), 2 );
		if ( $percent == '100.00' ) {
			$percent = 100;
		}

		return array(
			$this->score_key => $percent,
			'time'           => $time,
		);
	}

	/**
	 * Prepare the response html for the provided check and the result.
	 *
	 * @param string $check
	 * @param array $result
	 *
	 * @return array
	 */
	public function get_html( string $check, array $result ) {
		$this->init_trait();
		$this->init_trait_category();
		$texts       = $this->get_text( $check );
		$caret       = $this->get_caret( $result );
		$list        = $this->get_list( $result, $check );
		$fix_button  = $this->get_fix_button_data( $check, $result );
		$undo_button = $this->get_undo_button( $check, $result );
		$upsell_text = $this->get_upsell_text( $check, $result, $texts );
		$status      = $this->get_status( $result, $list, $texts, $check );
		$resolve_button = $this->get_resolve_button_data( $check );
		$category_tag = $this->get_check_category_text( $check );
		$status_data['status_key'] = $status;

		$check_data = array(
			'check'          => $check,
			'overview'       => $texts[ $this->overview ],
			'action_title'   => $texts[ $this->action_title ],
			'description'    => $this->get_text( $check, 'desc' ),
			'status'         => $status_data,
			'is_ignored'     => $this->is_ignored( $check ),
			'is_premium'     => $this->groupone_is_premium(),
			'category'       => $this->get_check_category_text( $check ),
			'resolve_button' => $this->get_resolve_button_data( $check ),
			'undo_button'    => $undo_button,
			'fix_button'     => $this->get_fix_button_data( $check, $result ), // this returns data not HTML
			'check_result'   => $result,
		);

		return $check_data;
	}

	/**
	 * Accept scan outcome and format a result array to be returned.
	 *
	 * @param int $status status of current check 1,0
	 * @param string $title one liner description for current issue
	 * @param string $desc more detailed description for current issue
	 *
	 * @return array
	 */
	public function format_result( int $status = 1, string $title = '', string $desc = '' ): array {
		return array(
			$this->status_key => $status,
			$this->title_key  => __( $title, 'onecom-wp' ),
			$this->desc_key   => __( $desc, 'onecom-wp' ),
		);
	}

	/**
	 * Remove the parent directory path from the provided file path.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function format_path( string $path ): string {
		return ( str_replace( ABSPATH, '', $path ) );
	}

	/**
	 * get_list()
	 * Get list of items causing the issue
	 *
	 * @param array $result
	 *
	 * @return string
	 */
	public function get_list( array $result, string $check ): string|bool {

		if ( ! ( isset( $result['list'] ) || isset( $result['file-list'] ) || isset( $result[ $this->raw_list_key ] ) ) ) {
			return '';
		}
		if ( ( $check === 'inactive_plugins' || $check === 'inactive_themes' ) && count( $result['list'] ) > 10 ) {
			return '';
		}
		$list = '<ul class="ocsh-desc-li">';

		if ( isset( $result['file-list'] ) ) {
			foreach ( $result['file-list'] as $key => $item ) {
				$path        = $this->format_path( $key );
				$delete_link = '';
				if ( isset( $result['delete-link'] ) && $result['delete-link'] ) {
					$delete_link = '<a href="javascript:void(0)" class="ocsh-delete-link" data-file="' . $path . '">' . __( 'Delete', 'onecom-wp' ) . '</a>';
				}
				$list .= '<li>' . $path . ' <span class="ocsh-file-list-item">(' . $item . ')</span> ' . apply_filters( 'onecom_if_premium', $delete_link ) . $this->closing_li;
			}
		}

		if ( isset( $result['list'] ) ) {
			foreach ( $result['list'] as $key => $item ) {
				$delete_link = '';
				$path        = $this->format_path( $item );
				if ( isset( $result['delete-link'] ) && $result['delete-link'] ) {
					$delete_link = '<a href="javascript:void(0)" class="ocsh-delete-link" data-file="' . $path . '">' . __( 'Delete', 'onecom-wp' ) . '</a>';
				}
				$list .= '<li>' . $this->format_path( $item ) . apply_filters( 'onecom_if_premium', $delete_link ) . $this->closing_li;
			}
		}
		if ( str_contains( $list, '<li>' ) ) {
			return $list . '</ul>';

		} else {
			return false;
		}
	}


	public function get_list_data( array $result, string $check ): array {
		if ( ! ( isset( $result['list'] ) || isset( $result['file-list'] ) || isset( $result[ $this->raw_list_key ] ) ) ) {
			return [];
		}

		if ( ( $check === 'inactive_plugins' || $check === 'inactive_themes' ) && count( $result['list'] ) > 10 ) {
			return [];
		}

		$list = [];

		// Special handling for usernames check — return structured list directly
		if ( $check === 'usernames' && isset( $result[ $this->raw_list_key ] ) && is_array( $result[ $this->raw_list_key ] ) ) {
			$translated_text = __( 'Change username', 'onecom-wp' );
			foreach ( $result[ $this->raw_list_key ] as &$item ) {
				if ( isset( $item['label'] ) ) {
					$item['label'] = $translated_text;
				}
			}
			return $result[ $this->raw_list_key ];
		}

		// For file-list items (like suspicious files)
		if ( isset( $result['file-list'] ) ) {
			foreach ( $result['file-list'] as $key => $item ) {
				$path = $this->format_path( $key );
				$list[] = [
					'text'       => $path . ' (' . $item . ')',
					'file'       => $path,
					'can_delete' => ! empty( $result['delete-link'] ),
				];
			}
		}

		// For simple list of paths
		if ( isset( $result['list'] ) ) {
			foreach ( $result['list'] as $item ) {
				$path = $this->format_path( $item );
				$list[] = [
					'text'       => $path,
					'file'       => $path,
					'can_delete' => ! empty( $result['delete-link'] ),
				];
			}
		}

		return $list;
	}


	/**
	 * get_undo_button()
	 * Get undo button
	 *
	 * @param $check
	 * @param $result
	 *
	 * @return string
	 */
	public function get_undo_button( string $check, array $result ): array {
		if ( ! ( $result['status'] == $this->flag_resolved && isset( $result['undo'] ) && $result['undo'] ) ) {
			return [];
		}
		if ( $check === 'performance_cache' ) {
			$check = 'check_' . $check;
		}

		if ( $this->groupone_is_premium() ) {
			return [
				'type'  => 'undo',
				'class' => 'gv-action',
				'text'  => $this->revert_text,
				'data'  => [
					'check' => $check,
				],
			];
		}

		return [];
	}

	/**
	 * @param string $check
	 * @param array $result
	 * @param array $texts
	 *
	 * @return string
	 */
	private function get_upsell_text( string $check, array $result, array $texts ): string {
		if ( ( $result[ $this->status_key ] === $this->flag_resolved )
			|| empty( $texts['upsell_text'] )
			|| $this->groupone_is_premium()
		) {
			return '';
		}

		return '<div class="gv-notice gv-notice-info"><gv-icon src="' . GROUPONE_WP_URL . '/assets/images/info.svg" alt="info"></gv-icon><p class="gv-notice-content gv-flex gv-max-mob-flex-col gv-items-center" >' . $texts['upsell_text'] . '</p></div>';
	}

	/**
	 * get_input_fields()
	 * Get HTML for input fields
	 *
	 * @param $result
	 *
	 * @return string
	 */
	private function get_input_fields( $result ): string {
		if ( ( ! ( isset( $result['input_fields'] ) && $result['input_fields'] ) ) || $result[ $this->status_key ] === $this->flag_resolved ) {
			return '';
		}
		$fields  = '';
		$hm_data = get_option( $this->option_key );
		$inputs  = $hm_data['recaptcha_keys'] ?? array();
		foreach ( $result['input_fields'] as $field ) {
			$value   = $inputs[ $field['name'] ] ?? '';
			$fields .= ' <div class="ocsh_input-fields">
<label> ' . $field['label'] . ' </label>
<input value = "' . $value . '" type = "text" name = "' . $field['name'] . '" id = "' . $field['name'] . '"><p class="oc-error-message"></p>
</div> ';
		}

		return apply_filters( 'onecom_if_premium', $fields );
	}

	private function get_caret( $result ): string {
		return $result[ $this->status_key ] === $this->flag_open ? '<span class="oc-caret"></span> ' : '';
	}

	/**
	 * Push stats
	 *
	 * @param array $additional_info
	 */
	public function push_stats( string $event_action, string $check_name ): void {
		if ( ! class_exists( 'GroupOne_PushStats' ) ) {
			return;
		}
		\GroupOne_PushStats::push_health_monitor_stats_request( $event_action, 'blog', GroupOne_PushStats::get_subdomain(), '1', array( 'check_name' => $check_name ), array( 'item_source' => 'health_monitor' ) );
	}

	/**
	 * @param $url
	 *
	 * @return array
	 */
	public function get_curl_header( $url ) {
		$headers = array();
		$ch      = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1 );
		curl_setopt(
			$ch,
			CURLOPT_HEADERFUNCTION,
			function ( $curl, $header ) use ( &$headers ) {
				@$curl;
				$len    = strlen( $header );
				$header = explode( ':', $header, 2 );
				if ( count( $header ) < 2 ) { // ignore invalid headers
					return $len;
				}
				$name = strtolower( trim( $header[0] ) );
				if ( ! array_key_exists( $name, $headers ) ) {
					$headers[ $name ] = array( trim( $header[1] ) );
				} else {
					$headers[ $name ][] = trim( $header[1] );
				}

				return $len;
			}
		);
		curl_exec( $ch );

		return $headers;
	}

	/**
	 * @param $content
	 *
	 * @return string
	 */
	public function get_status( $result, $list, $texts, $check ): array {
		$count = 0;
		$status_desc_open = '';

		if ( $result[ $this->status_key ] === $this->status_open && ( $check === 'inactive_plugins' || $check === 'inactive_themes' ) ) {
			$count = count( $result['list'] );

			if ( $count > 0 ) {
				$status_desc_open = $texts[ $this->status_desc ][ $this->status_open ];

				if ( $list !== '' ) {
					$status_desc_open = sprintf( '%s', $status_desc_open );
				}

				$texts[ $this->status_desc ][ $this->status_open ] = sprintf( $status_desc_open, $count );
			}
		}

		$key_status     = $result[ $this->status_key ] === $this->flag_resolved ? $this->status_resolved : $this->status_open;
		$how_to_fix_key = $this->groupone_is_premium() ? $this->how_to_fix : $this->how_to_fix_lite;

		// Fallback if premium text missing
		if ( empty( $texts[ $how_to_fix_key ] ) ) {
			$how_to_fix_key = $this->how_to_fix;
		}

		return array(
			'status_title'      => $this->status_text,
			'status_desc'       => $texts[ $this->status_desc ][ $key_status ],
			'status_key'        => $key_status,
			'how_to_fix_title'  => $this->fix_text,
			'how_to_fix_text'   => ( $result[ $this->status_key ] !== $this->flag_resolved ) ? $texts[ $how_to_fix_key ] : '',
			'show_how_to_fix'   => ( $result[ $this->status_key ] !== $this->flag_resolved ) &&
								   ( ! empty( $texts[ $how_to_fix_key ] ) || ! empty( $texts['upsell_text'] ) ),
			'input_fields'      => $this->get_input_fields( $result ),
			'list_items_html'   => $list, // If you want to send list HTML directly, else adjust accordingly.
		);
	}

	/**
	 * The stored ignored list, filtered by the premium entitlement.
	 * Non-premium sites must not honour the list even though it is kept intact in storage.
	 *
	 * @return array
	 */
	public function get_honoured_ignored(): array {
		if ( null === $this->is_premium_memo ) {
			$this->is_premium_memo = (bool) $this->groupone_is_premium();
		}

		return $this->is_premium_memo ? $this->ignored : array();
	}

	/**
	 * Check if a check is ignored
	 *
	 * @param string $check
	 *
	 * @return bool
	 */
	public function is_ignored( $check = '' ): bool {
		$check = str_replace( 'check_', '', $check );

		return in_array( $check, $this->get_honoured_ignored() );
	}

	/**
	 * Save the list of critical issues found in scan
	 *
	 * @param $check
	 *
	 * @return bool
	 */
	public function save_critical_todo( $check, $result ): bool {
		$hm_data = get_option( $this->option_key, array() );
		if ( empty( $hm_data ) ) {
			$hm_data = array();
		}
		if ( isset( $hm_data[ $this->saved_critical_todo ] ) && array_key_exists( $check, $hm_data[ $this->saved_critical_todo ] ) ) {
			return false;
		}
		$hm_data[ $this->saved_critical_todo ][ $check ] = $result['html'];

		return update_option( $this->option_key, $hm_data, 'no' );
	}


	/**
	 * Response format based on type of request
	 */
	public function send_json( array $result, string $check = '' ) {
		// WPIN-8909: was ! defined( 'REST_REQUEST' ) — see groupone_is_rest_context().
		if ( ! groupone_is_rest_context() ) {
			if ( $this->is_ignored( $check ) ) {
				$result[ $this->status_key ] = 3;
			}
			// add "html" key if not present
			if ( ! isset( $result['html'] ) ) {
				$result['html'] = $this->get_html( $check, $result );
			}
			if ( $result[ $this->status_key ] === $this->flag_open_critical ) {
				$this->save_critical_todo( $check, $result );
			}
			wp_send_json( $result );
		}

		return $result;
	}

	/**
	 * @param $plugin
	 * @param $network_activation
	 * redirects the user to imagify settings page after plugin activation
	 */
	public function redirect_user_imagify_activation( $plugin, $network_activation ) {
		if ( $plugin == 'imagify/imagify.php' ) {
			wp_redirect( admin_url( 'options-general.php?page=imagify' ) );
			exit();
		}
	}

	/**
	 * @return bool
	 * function for removing  optimize_uploaded_images check from the ignored list based on plugin activation
	 */
	public function remove_check_from_ignored_list(): bool {
		$marked_as_resolved = $this->ignored;
		$plugin_installed   = false;
		if ( empty( $marked_as_resolved ) || ! in_array( 'optimize_uploaded_images', $marked_as_resolved, true ) ) {

			return false;
		}

		// Added the recommended function to check Imagify API key
		// added in Imagify v2.1.1
		if (
			in_array( 'imagify/imagify.php', $this->active_plugins )
			&& function_exists( 'imagify_is_api_key_valid' ) && imagify_is_api_key_valid() ) {
			$plugin_installed = true;

		} else {

			$optimisation_plugins = array(
				'wp-smushit/wp-smush.php',
				'ewww-image-optimizer/ewww-image-optimizer.php',
				'optimole-wp/optimole-wp.php',
				'shortpixel-image-optimiser/wp-shortpixel.php',

			);
			foreach ( $optimisation_plugins as $optimisation_plugin ) {
				if ( in_array( $optimisation_plugin, $this->active_plugins ) ) {
					$plugin_installed = true;
					break;
				}
			}
		}

		if ( ( $plugin_installed === true ) && ( $key = array_search( 'optimize_uploaded_images', $marked_as_resolved ) ) !== false ) {

			unset( $marked_as_resolved[ $key ] );
			update_option( $this->resolved_option, $marked_as_resolved, 'no' );
			$this->ignored = $marked_as_resolved;
			return true;
		}

		return false;
	}

	/**
	 * @param $check
	 * @param $status
	 * updates the values in previous scans based on the undo and fix actions
	 * @return void
	 */
	public function update_previous_scan( $check, $status, $file = null ): void {
		$prev_scan   = get_site_transient( 'ocsh_site_previous_scan' );
		$scan_result = get_site_transient( 'ocsh_site_scan_result' );
		if ( empty( $prev_scan ) ) {
			$prev_scan = array();
		}
		if ( empty( $scan_result ) ) {
			$scan_result = array();
		}
		if ( $check == 'check_backup_zip' && $status === 0 ) {
			if ( isset( $prev_scan[ $check ]['list'] ) && is_array( $prev_scan[ $check ]['list'] ) ) {
				foreach ( $prev_scan[ $check ]['list'] as $key => $value ) {
					if ( str_contains( $value, $file ) ) {
						unset( $prev_scan[ $check ]['list'][ $key ] );
					}
				}

				if ( empty( $prev_scan[ $check ]['list'] ) ) {
					$prev_scan[ $check ]['delete-link'] = 0;
				} else {
					$status = 1;
				}
			}
		}
		$prev_scan[ $check ]['status'] = $status;
		$scan_result[ $check ]         = $status;

		// WPIN-8931: use the same TTL as save_result()/send_json() for both transients
		// here too - this method (fix/undo actions) was previously writing them with no
		// expiry at all, a third divergent TTL for the same two keys, which let
		// `ocsh_site_scan_result` and `ocsh_site_previous_scan` fall out of sync.
		set_site_transient( 'ocsh_site_previous_scan', $prev_scan, 24 * HOUR_IN_SECONDS );
		set_site_transient( 'ocsh_site_scan_result', $scan_result, 24 * HOUR_IN_SECONDS );
	}

	public function prepare_single_check_data( string $check, array $data, ?int $status = null ): array {
		$texts = $this->get_text( $check );
		$list = $this->get_list( $data, $check );
		$undo_button = $this->get_undo_button( $check, $data );


		$status_data = $this->get_status( $data, $list, $texts, $check );

		$fix_button = $this->get_fix_button_data( $check, $data, $texts );

		$upsell_text = $this->get_upsell_text( $check, $data, $texts );


		$is_resolved = ( $data['status'] ?? 0 ) === 0;

		return array(
			'check'          => $check,
			'overview'       => $texts[ $this->overview ],
			'action_title'   => $texts[ $this->action_title ],
			'description'    => $this->get_text( $check, 'desc' ),
			'status'         => $status_data,
			'is_ignored'     => $this->is_ignored( $check ),
			'is_premium'     => $this->groupone_is_premium(),
			'category'       => $this->get_check_category_text( $check ),
			'resolve_button' => $this->get_resolve_button_data( $check ),
			'undo_button'    => $undo_button,
			'fix_button'     => $fix_button,
			'check_result' => array_merge(
				$data,
				[ 'list_data' => $is_resolved ? [] : $this->get_list_data( $data, $check ) ]
			),
			'upsell_text'	=> $upsell_text,
		);
	}


	public function get_resolve_button_data( string $check ): array {
		if ( ! $this->groupone_is_premium() ) {
			return [];
		}

		$class    = $this->groupone_is_premium() ? '' : 'ocsh-open-modal';
		$ignored  = $this->is_ignored( $check );
		$priority = 'normal';
		$link_text = $this->ignore_text;

		if ( $ignored ) {
			$link_text = $this->unignore_text;
			$class    .= ' onecom_unignore';
		} else {
			$class    .= ' oc-mark-resolved';
		}

		if ( $this->get_check_category( $check ) === '<span class="onecom_tag">' . $this->category['critical'] . '</span>' ) {
			$priority = 'critical';
			if ( ! $ignored ) {
				$link_text = $this->ignore_critical_text;
			}
		}

		return array(
			'link_text' => $link_text,
			'class'     => trim( $class ),
			'data'      => array(
				'check'    => $check,
				'priority' => $priority,
			),
		);
	}

	public function get_fix_button_data( string $check, array $result, $text = array() ): array {
		if ( ! ( isset( $result['fix'] ) && $result[ $this->status_key ] === $this->flag_open ) ) {
			return array(); // No fix button needed
		}

		// Discouraged plugins have a dedicated page — no fix button in the audit
		if ( $check === 'dis_plugin' ) {
			return array();
		}

		// Only premium users can access fix buttons for most checks
		if ( ! $this->groupone_is_premium() && ! in_array( $check, [ 'performance_cache', 'enable_cdn' ], true ) ) {
			return [];
		}

		$fix_url = '';
		$class   = 'ocsh-open-modal';

		// Premium or specific checks use fix_url directly
		if ( $this->groupone_is_premium() || in_array( $check, [ 'check_performance_cache','performance_cache', 'enable_cdn', 'check_staging_time' ] ) ) {
			$fix_url = $result['fix_url'] ?? '';
			$class   = 'oc-fix-button';
		} else {
			$check = '';
		}

		$fix_text = $result['fix_text'] ?? $text['fix_button_text'] ?? '';

		// Special Imagify cases
		if ( $check === 'optimize_uploaded_images' && ! file_exists( WP_PLUGIN_DIR . '/imagify/imagify.php' ) ) {
			$install_url = add_query_arg(
				array(
					'action'   => 'install-plugin',
					'plugin'   => 'imagify',
					'_wpnonce' => wp_create_nonce( 'install-plugin_imagify' ),
				),
				self_admin_url( 'update.php' )
			);

			return array(
				'type' => 'install_plugin',
				'plugin_slug' => 'imagify',
				'install_url' => $install_url,
				'text' => __( 'Install Imagify', $this->text_domain ),
			);
		} elseif ( $check === 'optimize_uploaded_images' && ! is_plugin_active( 'imagify/imagify.php' ) ) {
			$activate_url = add_query_arg(
				array(
					'_wpnonce' => wp_create_nonce( 'activate-plugin_imagify/imagify.php' ),
					'action'   => 'activate',
					'plugin'   => 'imagify/imagify.php',
				),
				admin_url( 'plugins.php' )
			);

			return array(
				'type' => 'activate_plugin',
				'plugin_slug' => 'imagify',
				'activate_url' => $activate_url,
				'text' => __( 'Activate Imagify', $this->text_domain ),
			);
		} elseif ( $check === 'optimize_uploaded_images' && is_plugin_active( 'imagify/imagify.php' ) ) {
			return array(
				'type' => 'settings_link',
				'settings_url' => admin_url( 'options-general.php?page=imagify' ),
				'text' => __( 'Imagify Settings', $this->text_domain ),
			);
		}

		// Staging time and other cases with fix_url
		if ( in_array( $check, [ 'check_staging_time', 'inactive_plugins', 'inactive_themes' ] ) ) {
			return array(
				'type' => 'link',
				'url' => $fix_url,
				'class' => $class,
				'text' => __( $fix_text, $this->text_domain ),
				'data' => array( 'check' => $check ),
			);
		}

		if ( $check === 'performance_cache' ) {
			$check = 'check_' . $check;
		}

		// Default button
		return array(
			'type' => 'button',
			'class' => $class,
			'text' => __( $fix_text, $this->text_domain ),
			'data' => array( 'check' => $check ),
		);
	}

	public function get_check_category_text( string $check ): string {
		$category = $this->get_check_category( $check );

		// remove HTML if present
		return strip_tags( $category );
	}
}