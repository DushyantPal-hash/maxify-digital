<?php
/**
 * class OnecomUsername
 * Contains checks and fixes related to username. This also includes user enumeration
 */
declare( strict_types=1 );

class OnecomCheckUsername extends OnecomHealthMonitor {
	public $block_ue_key = 'block_user_enumeration';

	public function init() {
		if ( ! is_admin() || true ) {
			$hm_data = get_option( $this->option_key );
			if ( isset( $hm_data[ $this->block_ue_key ] ) && $hm_data[ $this->block_ue_key ] === '1' ) {
				add_filter( 'redirect_canonical', array( $this, 'block_user_enumeration_request' ), 200, 2 );
			}
		}
	}

	public function get_vulnerable_user_list(): array {
		global $wpdb;

		$logins       = [ 'admin', 'user', 'usr', 'wp', 'wordpress' ];
		$login_names  = implode( "','", $logins );

		$matched_users = $wpdb->get_col( "SELECT user_login FROM $wpdb->users WHERE user_login IN ('{$login_names}')" );
		$list          = [];

		foreach ( $matched_users as $user ) {

			$list[] = [
				'user'  => $user,
				'nonce' =>  wp_create_nonce( 'ocsh_edit_username_' . $user ),
				'label' =>  __( 'Change username', 'onecom-wp' ) ,
			];
		}

		return $list;
	}

	/**
	 * Check if common usernames are used
	 * @return array
	 */
	public function check_usernames(): array {
		$this->log_entry( 'Checking if vulnerable usernames used' );

		$list   = $this->get_vulnerable_user_list();
		$status = empty( $list ) ? $this->flag_resolved : $this->flag_open;

		$this->log_entry( 'Finished checking for vulnerable usernames used' );

		$result = $this->format_result( $status );

		if ( ! empty( $list ) ) {
			$result[ $this->raw_list_key ] = $list;
		}

		return $result;
	}

	/**
	 * Check user enumeration
	 * @return array
	 */
	public function check_user_enumeration(): array {
		$url             = get_home_url();
		$user_id         = $this->get_user_id();
		$enumeration_url = add_query_arg( array( 'author' => $user_id ), $url );
		$response        = wp_remote_get( $enumeration_url );
		$response_code   = wp_remote_retrieve_response_code( $response );
		$blocked         = false;
		$hm_data         = get_option( $this->option_key );
		if ( empty( $hm_data ) ) {
			$hm_data = array();
		}
		if ( isset( $hm_data[ $this->block_ue_key ] ) && $hm_data[ $this->block_ue_key ] == '1' ) {
			$blocked = true;
		}

		$plugin_activated = is_plugin_active( 'stop-user-enumeration/stop-user-enumeration.php' );

		if ( in_array( $response_code, array( 200, 301 ) ) && ! $plugin_activated && ( ! $blocked ) ) {
			return $this->format_result( $this->flag_open );
		}
		return $this->format_result( $this->status_resolved );
	}

	/**
	 * Get any valid user id existing in database
	 * @return int
	 */
	private function get_user_id(): int {

		$users = get_users(
			array(
				'number' => 1,
			)
		);

		return $users[0]->ID;
	}

	function format_vulnerable_usernames(): string {
		$vulnerable_list = $this->get_vulnerable_user_list();

		// Extract only usernames with core PHP function
		$usernames = array_column( $vulnerable_list, 'user' );

		if ( empty( $usernames ) ) {
			return '';
		}

		// Build HTML list
		$output = '<ul>';
		foreach ( $usernames as $username ) {
			$output .= '<li>' . esc_html( $username ) . '</li>';
		}
		$output .= '</ul>';

		return $output;
	}

	public function fix_usernames() {
		$username = $_POST['username'];
		if ( ! validate_username( $_POST['username'] ) ) {
			return array(
				$this->status_key => $this->flag_open,
				$this->desc_key   => $this->quick_fix_messages['error']['invalid_username'],
			);

		}
		$user = $_POST['oldUser'];
		error_log( 'Expecting nonce for: ocsh_edit_username_' . $user );
		error_log( 'Got nonce: ' . $_REQUEST['_ajax_nonce'] );
		check_ajax_referer( 'ocsh_edit_username_' . $user );
		global $wpdb;
		$query              = "UPDATE {$wpdb->users} SET user_login = %s, user_nicename=%s WHERE user_login = %s";
		$username_sanitized = filter_var( $_POST['username'], FILTER_SANITIZE_STRING );
		$result             = $wpdb->query( $wpdb->prepare( $query, $username_sanitized, $username_sanitized, $user ) );

		if ( $result === false ) {
			return array(
				$this->status_key => $this->flag_open,
				$this->desc_key   => $this->quick_fix_messages['error']['username_not_changed'],
			);
		}

		return array(
			$this->status_key => $this->flag_resolved,
			$this->desc_key   => $this->quick_fix_messages['success']['username_changed'],
		);
	}

	public function fix_user_enumeration(): array {
		$hm_data = get_option( $this->option_key, array() );
		if ( empty( $hm_data ) ) {
			$hm_data = array();
		}
		$hm_data[ $this->block_ue_key ] = '1';

		if ( update_option( $this->option_key, $hm_data, 'no' ) ) {
			// flush varnish cache
			wp_remote_request( get_option( 'home' ), array( 'method' => 'PURGE' ) );

			return $this->format_result(
				$this->flag_resolved,
				$this->text['user_enumeration'][ $this->fix_confirmation ],
				$this->text['user_enumeration'][ $this->status_desc ][ $this->status_resolved ]
			);
		} else {
			return $this->format_result( $this->flag_open );
		}
	}

	public function block_user_enumeration_request( $redirect, $request ) {
		if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		} else {
			return $redirect;
		}
	}
}
