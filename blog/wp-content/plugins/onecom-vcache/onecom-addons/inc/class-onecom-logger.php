<?php
/**
 * Class Onecom_Logger
 */
if ( ! class_exists( 'Onecom_Logger' ) ) {
    class Onecom_Logger {

        const TYPE_ERROR = 'ERROR';

        const TYPE_CRITICAL = 'CRITICAL';

        const TYPE_FATAL = 'FATAL';

        const TYPE_WARNING = 'WARNING';

        const TYPE_INFO = 'STATE';

        const TYPE_DEBUG = 'DEBUG';

        const TYPE_STATUS = 'STATUS';

        private $middleware;
        private $middleware_ver      = 'v1.0';
        private $middleware_endpoint = 'log';

        /**
         * Logger constructor.
         * @param null|string $log_dir
         * @param null|string $log_extension
         * @throws \Exception
         */
        public function __construct() {
            if (
                isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) &&
                substr( $_SERVER['ONECOM_DOMAIN_NAME'], -10 ) === '.1test.one'
            ) {
                $onecom_wp_addons_api = 'https://wpapi-next.one.com/';
			} elseif (
				isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) &&
				substr( $_SERVER['ONECOM_DOMAIN_NAME'], -9 ) === '.1stg.one'
			) {
				$onecom_wp_addons_api = 'https://wpapi-test.one.com/';
			} elseif ( isset( $_SERVER['ONECOM_WP_ADDONS_API'] ) && '' !== $_SERVER['ONECOM_WP_ADDONS_API'] ) {
                $onecom_wp_addons_api = $_SERVER['ONECOM_WP_ADDONS_API'];
            } elseif ( defined( 'ONECOM_WP_ADDONS_API' ) && ONECOM_WP_ADDONS_API !== '' && ONECOM_WP_ADDONS_API !== false ) {
                $onecom_wp_addons_api = ONECOM_WP_ADDONS_API;
            } else {
                $onecom_wp_addons_api = 'http://wpapi.one.com/';
            }
            $onecom_wp_addons_api = rtrim( $onecom_wp_addons_api, '/' );
            $this->middleware     = $onecom_wp_addons_api . '/api/' . $this->middleware_ver . '/' . $this->middleware_endpoint;
        }

        /**
         * Generic log to WP API
         * @param string entry_prefix // unique prefix to indetify the plugin or theme
         * @param string action_type //
         * @param string message // message for log
         * @param string version // version of plugin or theme
         * @param boolean error // is log having error or not
         * @return bool
         **/
        public function wp_api_sendlog( $action_type, $entry_prefix = 'general_', $message = '', $version = null, $error = 'false' ) {
            if ( '' === $action_type || null === $action_type ) {
                return;
            }
            $error   = (string) $error;
            $log_url = $this->middleware;

            $entry_prefix = rtrim( $entry_prefix, '_' ) . '_';

            $params = array(
                'action_type' => $entry_prefix . filter_var( $action_type, FILTER_SANITIZE_STRING ),
                'message'     => $message,
                'error'       => $error,
            );

            if ( null !== $version ) {
                $params['version']  = $version;
                $params['message'] .= ' | ' . 'Version:' . $version;
            }

            $client_ip     = $this->onecom_get_client_ip_env();
            $client_domain = ( isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) && ! empty( $_SERVER['ONECOM_DOMAIN_NAME'] ) ) ? $_SERVER['ONECOM_DOMAIN_NAME'] : 'localhost';

            global $wp_version;

            $log_entry = json_encode( $params );

            $save_log = wp_safe_remote_post(
                $log_url,
                array(
                    'method'     => 'POST',
                    'timeout'    => 3,
                    'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url(),
                    'compress'   => false,
                    'decompress' => true,
                    'sslverify'  => true,
                    'stream'     => false,
                    'body'       => $log_entry,
                    'headers'    => array(
                        'X-ONECOM-CLIENT-IP'     => $client_ip,
                        'X-ONECOM-CLIENT-DOMAIN' => $client_domain,
                    ),
                )
            );

            if ( ! is_wp_error( $save_log ) ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Function to get the client ip address..
         **/
        public function onecom_get_client_ip_env() {
            if ( getenv( 'HTTP_CLIENT_IP' ) ) {
                $ipaddress = getenv( 'HTTP_CLIENT_IP' );
            } elseif ( getenv( 'REMOTE_ADDR' ) ) {
                $ipaddress = getenv( 'REMOTE_ADDR' );
            } else {
                $ipaddress = '0.0.0.0';
            }
            return $ipaddress;
        }
    }
}