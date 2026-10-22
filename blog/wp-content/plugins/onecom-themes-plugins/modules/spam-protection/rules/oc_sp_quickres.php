<?php

class GroupOne_SpQuickres {


	public $user_ip;
	public $oc_post;
	public $oc_max_login_val;
	public $oc_block_time;

	public function execute(
		&$sp_options = array(),
		&$oc_post = array()
	) {
		add_filter( 'authenticate', array( $this, 'oc_check_attempted_login' ), 30, 3 );
		add_filter( 'wp_login_failed', array( $this, 'oc_get_failed_logins' ), 30, 3 );
		add_action( 'login_errors', array( $this, 'oc_login_errors' ) );

		$base = new GroupOne_Sp();

		// WPIN-8909: $sp_options['checks'] is only written once the settings have
		// been saved or restored, so both keys are absent on a fresh install and
		// on any site whose options row predates them. Reading them unguarded
		// warned and then left oc_max_login_val null, which makes every
		// "attempts remaining" subtraction below count down from zero - i.e. the
		// lockout triggers on the first failed login. Fall back to the module's
		// own defaults rather than to null; groupone_set_default_options() is the
		// single source of truth for these two numbers.
		$defaults = function_exists( 'groupone_set_default_options' )
			? groupone_set_default_options()
			: array();
		$checks   = isset( $sp_options['checks'] ) && is_array( $sp_options['checks'] )
			? $sp_options['checks']
			: array();

		$this->user_ip          = $base->get_user_ip();
		$this->oc_post          = $oc_post;
		$this->oc_max_login_val = $checks['oc_max_login_val'] ?? ( $defaults['oc_max_login_val'] ?? 5 );
		$this->oc_block_time    = ( $checks['oc_block_time'] ?? ( $defaults['oc_block_time'] ?? 30 ) ) + 5;
	}

	/**
	 * @param $user
	 * @param $username
	 * @param $password
	 * @return false|mixed|WP_Error
	 * checks for the no of failed logins & blocks the user temporarily if the no exceeds the defined limit
	 * logs the blocked user in spam logs.
	 */
	public function oc_check_attempted_login( $user, $username, $password ) {

		if ( get_transient( 'oc_sp_attempted_login' ) ) {
			$logins = get_transient( 'oc_sp_attempted_login' );

			if ( ( $this->oc_max_login_val - $logins['tried'] ) <= 1 && $logins['ip'] === $this->user_ip ) {
				$blocked_till = get_option( '_transient_timeout_' . 'oc_sp_attempted_login' );
				$time_blocked = $this->blocked_till_time( $blocked_till );
				groupone_log_spam( $this->user_ip, $this->oc_post, __( 'Too many failed login attempts', 'onecom-sp' ) );

				return new WP_Error( 'too_many_attempts', sprintf( __( '<strong>ERROR</strong>: You have reached authentication limit, you will be able to try again in %1$s.', GROUPONE_SP_TEXTDOMAIN ), $time_blocked ) );
			}

			return false;
		}

		return $user;
	}

	/**
	 * @param $username
	 * @return string
	 * Sets / updates the value of transient on every failed login attempt & displays the message.
	 */
	public function oc_get_failed_logins( $username ) {

		global $message;

		if ( get_transient( 'oc_sp_attempted_login' ) ) {
			$login_attempts = get_transient( 'oc_sp_attempted_login' );
			$login_attempts['tried']++;

			if ( ( $this->oc_max_login_val - $login_attempts['tried'] ) >= 1 ) {
				set_transient( 'oc_sp_attempted_login', $login_attempts, $this->oc_block_time );
				$message = $this->oc_max_login_val - $login_attempts['tried'] . ' attempts remaining!';
				return $message;
			} else {
				$message = '';
				return $message;
			}
		} else {
			$login_attempts = array(
				'ip'    => $this->user_ip,
				'tried' => 1,
			);
			$message        = $this->oc_max_login_val - $login_attempts['tried'] . ' attempts remaining!';
			set_transient( 'oc_sp_attempted_login', $login_attempts, $this->oc_block_time );
			return $message;
		}
	}

	/**
	 * @param $timestamp
	 * @return string
	 * converts the mysql time stamp to human readable time difference
	 */

	public function blocked_till_time( $timestamp ) {

		// converting the mysql timestamp to php time
		$periods           = array(
			'second',
			'minute',
			'hour',
			'day',
			'week',
			'month',
			'year',
		);
		$lengths           = array(
			'60',
			'60',
			'24',
			'7',
			'4.35',
			'12',
		);
		$current_timestamp = time();
		$difference        = abs( $current_timestamp - $timestamp );
		for ( $i = 0; $difference >= $lengths[ $i ] && $i < count( $lengths ) - 1; $i++ ) {
			$difference /= $lengths[ $i ];
		}
		$difference = round( $difference );
		if ( isset( $difference ) ) {
			if ( 1 != $difference ) {
				$periods[ $i ] .= 's';
			}
			$output = "$difference $periods[$i]";
			return $output;
		}
	}

	/**
	 * @param $error
	 * @return mixed|string
	 * Formats the error displayed on failed login attempt.
	 */
	public function oc_login_errors( $error ) {
		global $message;

		if ( '' !== $message ) {
			$error = $message . '</br>' . $error;
		}
		return $error;
	}
}
