<?php
$oc_hm_status = 'status';
$oc_hm_desc   = 'desc';
$oc_hm_title  = 'title';
if ( ! defined( 'GROUPONE_HM_SCORE' ) ) {
	define( 'GROUPONE_HM_SCORE', 'score' );
}

if ( ! function_exists( 'groupone_sh_save_result' ) ) {
	function groupone_sh_save_result( $stage, $oc_hm_status, $finish = 0 ) {
		$result = get_site_transient( 'ocsh_site_scan_result' );
		$time   = time();
		if ( ! $result ) {
			$result = array();
		}
		$result['time']   = $time;
		$result[ $stage ] = $oc_hm_status;
		$save             = set_site_transient( 'ocsh_site_scan_result', $result, 4 * HOUR_IN_SECONDS );

		if ( $finish == 1 ) {
			unset( $result['time'] );
			$health                 = array();
			$health['issues']       = $result;
			$health[ GROUPONE_HM_SCORE ] = round( groupone_sh_calculate_score( $result )[ GROUPONE_HM_SCORE ] );

			/* save health monitor result */
			groupone_sh_log_entry( '== one.com Health Monitor Scan ==' );
			groupone_sh_log_entry( json_encode( $health ), 1 );

			( class_exists( 'GroupOne_PushStats' ) ? \GroupOne_PushStats::push_health_monitor_stats_request( 'scan', 'blog', GroupOne_PushStats::get_subdomain(), '1', $health ) : '' );
		}

		return $save;
	}
}

if ( ! function_exists( 'groupone_sh_calculate_score' ) ) {
	function groupone_sh_calculate_score( $transient ) {
		$count = groupone_vulns_count() > 0 ? 1 : 0;

		if ( ! $transient || empty( $transient ) ) {
			return 0;
		}
		$ignored_checks = get_site_option( 'oc_marked_resolved', array() );
		if ( empty( $ignored_checks ) ) {
			$ignored_checks = array();
		}
		@$time = $transient['time'];
		unset( $transient['time'] );

		$success = 0;
		$todo    = 0;

		foreach ( $transient as $check => $score ) {
			if ( in_array( str_replace( array( 'ocsh_fix_', 'ocsh_fix_check_', 'check_' ), '', $check ), $ignored_checks ) ) {
				$score = GROUPONE_RESOLVED;
			}

			if ( $score == GROUPONE_RESOLVED ) {
				$success++;
			} elseif ( $score == GROUPONE_OPEN ) {
				$todo++;
			}
		}
		$percent = floor( ( $success * 100 ) / ( count( $transient ) + $count ) );
		if ( $percent == '100.00' ) {
			$percent = 100;
		}

		return array(
			GROUPONE_HM_SCORE => $percent,
			'time'       => $time,
			'todo'       => $todo,
		);
	}
}

if ( ! function_exists( 'groupone_vulns_count' ) ) {
	function groupone_vulns_count() {
		if ( class_exists( 'GroupOne_VMNotifications' ) ) {
			$notices = new GroupOne_VMNotifications();
			$notices->prepareNotifications( 1 );
			// get notices count
			$count = is_countable( $notices->notices ) ? count( $notices->notices ) : 0;
		} else {
			$count = 0;
		}
		return $count;
	}
}
