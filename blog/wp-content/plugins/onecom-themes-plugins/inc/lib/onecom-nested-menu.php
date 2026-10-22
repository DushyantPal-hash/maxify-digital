<?php

declare(strict_types=1);
defined( 'WPINC' ) or die(); // No Direct Access

// WPIN-8739 renamed OC_PLUGIN_DOMAIN to GROUPONE_PLUGIN_DOMAIN here, but the
// hub (onecom-themes-plugins.php) still only defines the old name — fall
// back to it so this doesn't fatal until the hub is renamed to match.
if ( ! defined( 'GROUPONE_PLUGIN_DOMAIN' ) ) {
	define( 'GROUPONE_PLUGIN_DOMAIN', defined( 'OC_PLUGIN_DOMAIN' ) ? OC_PLUGIN_DOMAIN : 'onecom-wp' );
}

/**
 * Class GroupOne_Nested_Menu
 * Nested admin menu with accordion
 */

require_once __DIR__ . '/trait-onecom-module-active.php';

if ( ! class_exists( 'GroupOne_Nested_Menu' ) ) {
	final class GroupOne_Nested_Menu {
		use Onecom_Module_Active_Trait;

		const ONECOM_MENU_SLUG = 'onecom-wp';
		public $oc_menu_logo;
		const ONECOM_PLUGIN_FILE  = 'onecom-themes-plugins/onecom-themes-plugins.php';
		const ONECOM_VCACHE_FILE  = 'onecom-vcache/vcaching.php';
		const ONECOM_WEBSHOP_FILE = 'onecom-webshop/webshop.php';
		const WP_PCACHE_TITLE     = 'Performance Cache';
		// WPIN-8866: per-user flag recording that the Marketplace "new" dot has been seen.
		const MARKETPLACE_DOT_META = 'onecom_marketplace_dot_seen';


		public function __construct() {
			if ( class_exists( 'OneComCentralizedMenu' ) ) {
				$old_menu = new OneComCentralizedMenu();
				require __DIR__ . '/class-centralized-menu-decorator.php';
				new OneComCentralizedMenuDecorator( $old_menu );
			}
			$this->oc_menu_logo = sprintf( '<img class="onecom-menu-logo" src="%s" alt="%s" />', plugin_dir_url( __FILE__ ) . '/assets/images/one.com.black.svg', __( 'one.com', GROUPONE_VALIDATOR_DOMAIN ) );
		}

		public function init() {

			// Dismiss the Marketplace dot as soon as the user opens the Marketplace page.
			// admin_init runs before admin_menu, so the dot is already gone on that same load.
			add_action( 'admin_init', array( $this, 'onecom_dismiss_marketplace_dot' ) );

			// remove menus registered from plugin
			add_action( 'admin_menu', array( $this, 'onecom_remove_menu' ), 14 );
			add_action( 'network_admin_menu', array( $this, 'onecom_remove_menu' ), 14 );

			// one.com admin menu
			add_action( 'admin_menu', array( $this, 'onecom_register_menu' ), 15 );
			add_action( 'network_admin_menu', array( $this, 'onecom_register_menu' ), 15 );

			// Enqueue CSS assets
			add_action( 'admin_head', array( $this, 'onecom_css_assets' ) );
			// Enqueue JS assets
			add_action( 'admin_print_footer_scripts', array( $this, 'onecom_js_assets' ), 100 );
		}

		// Register all menu and submenu
		public function onecom_register_menu() {
			global $submenu;

			$plugin_menu        = 'onecom-wp-plugins';
			$theme_menu         = 'onecom-wp-themes';
			$generic_plugin_ver = $this->oc_get_plugin_version( self::ONECOM_PLUGIN_FILE );

			// Return if multisite
			if ( ! is_network_admin() && is_multisite() ) {
				return false;
			}

			// Main menu
			$position = $this->onecom_get_free_menu_position( '2.1' );
			add_menu_page( __( 'One.com', self::ONECOM_MENU_SLUG ), '<span class="onecom-top-menu-item">one.com</span>', 'manage_options', self::ONECOM_MENU_SLUG, '', 'dashicons-admin-generic', $position );
			if ( $this->onecom_plugin_activated() || $this->oc_feature_active( 'spam-protection', 'onecom-spam-protection/onecom-spam-protection.php' ) ) {
				// Health and Security
				add_submenu_page(
					self::ONECOM_MENU_SLUG,
					'Health and Security',
					'<span id="onecom_health_security">Health and Security</span>',
					'manage_options',
					'onecom-health-security',
					array(
						$this,
						'onecom_menu_callback',
					),
					2
				);
				if ( $this->onecom_plugin_activated() ) {
					add_submenu_page( self::ONECOM_MENU_SLUG, __( 'Home', GROUPONE_VALIDATOR_DOMAIN ), '<span id="onecom_home">' . __( 'Home', GROUPONE_VALIDATOR_DOMAIN ) . '</span>', 'manage_options', 'onecom-home', '', -1 );

					add_submenu_page( self::ONECOM_MENU_SLUG, 'Health Monitor', 'Health Monitor', 'manage_options', 'onecom-wp-health-monitor', '' );
				}
				if ( $this->oc_feature_active( 'spam-protection', 'onecom-spam-protection/onecom-spam-protection.php' ) ) {

					add_submenu_page( self::ONECOM_MENU_SLUG, 'Spam Protection', 'Spam Protection', 'manage_options', 'onecom-wp-spam-protection', '' );
				}
			}

			// Show 'Performance' nested menu heading only if PCache module or plugin is active
			if ( $this->oc_feature_active( 'performance-cache', self::ONECOM_VCACHE_FILE ) ) {
				// Performance
				add_submenu_page(
					self::ONECOM_MENU_SLUG,
					'Performance',
					'<span id="onecom-performance-tools">Performance</span>',
					'manage_options',
					'onecom-performance-menu',
					array(
						$this,
						'onecom_menu_callback',
					),
					3
				);
			}

			if ( $this->oc_module_active( 'performance-cache' ) ||
				( is_plugin_active( self::ONECOM_VCACHE_FILE ) &&
				version_compare( $this->oc_get_plugin_version( self::ONECOM_VCACHE_FILE ), '2.0', '>=' ) ) ) {
				// The merged hub module (WPIN-8736) renamed its copy of this
				// class to GroupOne_VCaching (WPIN-8758, shortened under the
				// GroupOne rebrand) to avoid colliding with the legacy
				// standalone plugin's own OCVCaching class — pick whichever
				// one is actually loaded.
				$vcaching_class = $this->oc_module_active( 'performance-cache' ) ? 'GroupOne_VCaching' : 'OCVCaching';
				add_submenu_page(
					self::ONECOM_MENU_SLUG,
					self::WP_PCACHE_TITLE,
					self::WP_PCACHE_TITLE,
					'manage_options',
					'onecom-vcache-plugin',
					array(
						$vcaching_class,
						'cache_settings_page',
					),
					2
				);
				add_submenu_page(
					self::ONECOM_MENU_SLUG,
					'CDN',
					'CDN',
					'manage_options',
					'onecom-cdn',
					array(
						$vcaching_class,
						'cdn_settings_page',
					),
					2
				);
			} elseif ( $this->oc_feature_active( 'performance-cache', self::ONECOM_VCACHE_FILE ) ) {
				add_submenu_page( self::ONECOM_MENU_SLUG, self::WP_PCACHE_TITLE, 'Performance Cache&nbsp;', 'manage_options', 'onecom-vcache-plugin', '', 2 );

			}

            //Staging feature not supported on multisite
            //or if the site and home url are different
            if ( $this->onecom_plugin_activated()
                && is_multisite() || ( get_option( 'siteurl' ) !== get_option( 'home' ) ) ) {
                add_submenu_page( self::ONECOM_MENU_SLUG, 'Staging blocked', '<span id="onecom_staging">Staging</span>', 'manage_options', 'onecom-wp-staging-blocked', '', 7 );
            } else if ( $this->onecom_plugin_activated()
                && ( isset( $submenu[ self::ONECOM_MENU_SLUG ] )
                    && ( ! is_multisite() )
                ) ) {
                add_submenu_page( self::ONECOM_MENU_SLUG, 'Staging', '<span id="onecom_staging">Staging</span>', 'manage_options', 'onecom-wp-staging', '', 7 );
            }

			if (
				is_plugin_active( self::ONECOM_WEBSHOP_FILE ) ||
				is_plugin_active( 'onecom-onephoto/onecom-onephoto.php' ) ||
				$this->onecom_plugin_activated() ||
				$this->oc_feature_active( 'maintenance-mode', 'onecom-under-construction/onecom-under-construction.php' ) ||
				is_plugin_active( 'onecom-php-scanner/onecom-compatibility-scanner.php' )

			) {
				// Utility
				add_submenu_page(
					self::ONECOM_MENU_SLUG,
					'Utility',
					'<span id="onecom-utility">Utility</span>',
					'manage_options',
					'onecom-utility',
					array(
						$this,
						'onecom_menu_callback',
					),
					9
				);

				if ( is_plugin_active( self::ONECOM_WEBSHOP_FILE ) ) {
					include_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::ONECOM_WEBSHOP_FILE;
					add_submenu_page( self::ONECOM_MENU_SLUG, 'Online Shop', 'Online Shop', 'manage_options', 'one-webshop-settings', 'one_webshop_plugin_settings_page', 10 );

				}

				if ( is_plugin_active( 'onecom-onephoto/onecom-onephoto.php' ) ) {
					add_submenu_page( self::ONECOM_MENU_SLUG, 'One Photo', 'One Photo', 'manage_options', 'oc_onephoto', '', 11 );
				}

				if ( $this->onecom_plugin_activated() ) {
					add_submenu_page( self::ONECOM_MENU_SLUG, 'Advanced Error Page', 'Advanced Error Page', 'manage_options', 'onecom-wp-error-page', '', 12 );
					add_submenu_page( self::ONECOM_MENU_SLUG, 'Cookie Banner', 'Cookie Banner', 'manage_options', 'onecom-wp-cookie-banner', '', 13 );
				}
				if ( $this->oc_feature_active( 'maintenance-mode', 'onecom-under-construction/onecom-under-construction.php' ) ) {
					add_submenu_page( self::ONECOM_MENU_SLUG, 'Maintenance Mode', 'Maintenance Mode', 'manage_options', 'onecom-wp-under-construction', '', 14 );
				}

				if ( is_plugin_active( 'onecom-php-scanner/onecom-compatibility-scanner.php' ) ) {
					add_submenu_page( self::ONECOM_MENU_SLUG, 'PHP Scanner', 'PHP Scanner', 'manage_options', 'onecom-php-compatibility-scanner', '', 15 );
				}

				// Themes & Plugins
				if ( $this->onecom_plugin_activated()
					&&
					( isset( $submenu[ self::ONECOM_MENU_SLUG ] ) ) &&
					! ( in_array( $plugin_menu, wp_list_pluck( $submenu[ self::ONECOM_MENU_SLUG ], 2 ) ) ||
						in_array( $theme_menu, wp_list_pluck( $submenu[ self::ONECOM_MENU_SLUG ], 2 ) ) )
				) {
					add_submenu_page( self::ONECOM_MENU_SLUG, 'Themes', '<span id="onecom_themes">Themes</span>', 'manage_options', 'onecom-wp-themes', '', 16 );
				}
			}

            // Add WordPress product accordion menu only if plugin is active
            if ($this->onecom_plugin_activated()) {
                // Use static variable to ensure menus are registered only once per request
                static $wp_products_registered = false;

                if (!$wp_products_registered) {
                    global $submenu;

                    // Check if onecom-products already exists
                    $products_exists = false;
                    if (isset($submenu[self::ONECOM_MENU_SLUG])) {
                        foreach ($submenu[self::ONECOM_MENU_SLUG] as $item) {
                            if ($item[2] === 'onecom-products') {
                                $products_exists = true;
                                break;
                            }
                        }
                    }


                    // Check if onecom-marketplace already exists
                    $marketplace_exists = false;
                    if (isset($submenu[self::ONECOM_MENU_SLUG])) {
                        foreach ($submenu[self::ONECOM_MENU_SLUG] as $item) {
                            if ($item[2] === 'onecom-marketplace') {
                                $marketplace_exists = true;
                                break;
                            }
                        }
                    }

                    if (!$marketplace_exists) {
                        // WPIN-8866: the dot is only rendered until the user has opened the
                        // Marketplace page once. The #onecom_marketplace wrapper always stays —
                        // the walkthrough pointer and the menu CSS both anchor on it.
                        $marketplace_indicator = $this->onecom_marketplace_dot_seen() ? '' : '<div class="gv-indicator"></div>';
                        $marketplace_title     = '<div id="onecom_marketplace" class="gv-activated gv-dark-mode"> <div class="gv-text-indicator"><span>'
                                . __( 'Marketplace', 'onecom-wp' )
                                . '</span>' . $marketplace_indicator . '</div></div>';

                        add_submenu_page(
                                self::ONECOM_MENU_SLUG,
                                __( 'Marketplace', 'onecom-wp' ),
                                $marketplace_title,
                                'manage_options',
                                'onecom-marketplace',
                                '',
                                2
                        );
                    }

                    // Check if onecom-marketplace-products already exists
                    $marketplace_products_exists = false;
                    if (isset($submenu[self::ONECOM_MENU_SLUG])) {
                        foreach ($submenu[self::ONECOM_MENU_SLUG] as $item) {
                            if ($item[2] === 'onecom-marketplace-products') {
                                $marketplace_products_exists = true;
                                break;
                            }
                        }
                    }

                    if (!$marketplace_products_exists) {
                        add_submenu_page(
                                self::ONECOM_MENU_SLUG,
                                __( 'My products', 'onecom-wp' ),
                                __( 'My products', 'onecom-wp' ),
                                'manage_options',
                                'onecom-marketplace-products',
                                '',
                                3
                        );
                    }

                    // Mark as registered to prevent duplicate registration
                    $wp_products_registered = true;
                }

            }

			if ( ! is_network_admin() && is_multisite() ) {
				return false;
			}
		}


		/**
		 * WPIN-8866: has the current user already opened the Marketplace page?
		 *
		 * @return bool True once the dot has been dismissed for this user.
		 */
		public function onecom_marketplace_dot_seen(): bool {
			$user_id = get_current_user_id();

			if ( ! $user_id ) {
				return false;
			}

			return (bool) get_user_meta( $user_id, self::MARKETPLACE_DOT_META, true );
		}

		/**
		 * WPIN-8866: mark the Marketplace dot as seen when the user lands on the page.
		 *
		 * Hooked on admin_init, which fires before admin_menu, so the very visit that
		 * dismisses the dot already renders the menu without it.
		 *
		 * @return void
		 */
		public function onecom_dismiss_marketplace_dot() {
			$user_id = get_current_user_id();

			if ( ! $user_id ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check, no state is changed on the site.
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

			if ( 'onecom-marketplace' !== $page ) {
				return;
			}

			if ( $this->onecom_marketplace_dot_seen() ) {
				return;
			}

			update_user_meta( $user_id, self::MARKETPLACE_DOT_META, 1 );
		}

		public function onecom_remove_menu() {

			remove_menu_page( self::ONECOM_MENU_SLUG );

			remove_menu_page( 'onecom-vcache-plugin' );
			remove_menu_page( 'onecom-wp-under-construction' );
			remove_menu_page( 'onecom-wp-spam-protection' );
			remove_menu_page( 'onecom-php-compatibility-scanner' );
			remove_menu_page( 'vcaching-plugin' );
			remove_menu_page( 'one-webshop' );
			remove_menu_page( 'oc_onephoto' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-health-monitor' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-vcache-plugin' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-error-page' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-cookie-banner' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-staging' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-staging-blocked' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, self::ONECOM_MENU_SLUG );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-plugins' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-themes' );

			if ( $this->onecom_plugin_activated() && version_compare( $this->oc_get_plugin_version( 'onecom-themes-plugins/onecom-themes-plugins.php' ), '3.0', '>=' ) ) {
				remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-vulnerability-monitor' );

			}
		if ( $this->onecom_plugin_activated() ) {
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-wp-under-construction' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-marketplace' );
			remove_submenu_page( self::ONECOM_MENU_SLUG, 'onecom-marketplace-products' );
		}
		}

		public function onecom_plugin_activated(): bool {
			return is_plugin_active( self::ONECOM_PLUGIN_FILE );
		}

		// Menu callback for non-functional menus //
		public function onecom_menu_callback() {
			wp_die( '<h2>' . __( 'Sorry, you are not allowed to access this page.' ) . '</h2>' );
		}

		public function onecom_get_free_menu_position( $start, $increment = 0.3 ) {
			foreach ( $GLOBALS['menu'] as $key => $menu ) {
				$menus_positions[] = $key;
			}

			if ( ! in_array( $start, $menus_positions ) ) {
				return $start;
			}

			/* the position is already reserved find the closet one */
			while ( in_array( $start, $menus_positions ) ) {
				$start += $increment;
			}

			return (string) $start;
		}



		/**
		 * Version string from a plugin's file header.
		 *
		 * Always returns a string; an unknown version is '', never false.
		 * This file runs under declare(strict_types=1) and both callers pass
		 * the result straight into version_compare(), whose $version1 is typed
		 * string — under strict types false is a fatal TypeError there rather
		 * than a coercion to '', which white-screened wp-admin (WPIN-8923).
		 * '' sorts below every real version, so an unknown version still fails
		 * a >= check, which is the behaviour both call sites already assumed.
		 *
		 * Note that is_plugin_active() only consults the active_plugins option,
		 * so a plugin can be "active" while its file is missing or half-written
		 * (an aborted update) — that is the state that produced the crash.
		 *
		 * @param string $path Plugin file path relative to WP_PLUGIN_DIR.
		 *
		 * @return string Version, or '' when it cannot be determined.
		 */
		public function oc_get_plugin_version( $path ): string {

			if ( function_exists( 'get_file_data' ) ) {

				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				$file_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $path;
				if ( ! file_exists( $file_path ) ) {
					return '';
				}
				$plugin_data = get_file_data(
					$file_path,
					array(
						'Version' => 'Version',
					)
				);

				if ( ! empty( $plugin_data['Version'] ) ) {
					return (string) $plugin_data['Version'];
				}
			}

			return '';
		}

		// Insert script & styles related to admin menu
		public function onecom_css_assets() {
			?>
			<style>
				li#toplevel_page_onecom-wp {
					font-family: 'Open Sans', sans-serif;
					font-size: 13px;
					letter-spacing: -0.4px;
				}
				li#toplevel_page_onecom-wp .onecom-top-menu-item {
					font-weight: 600;
					font-size: 14px;
					line-height: 18px;
					letter-spacing: 0px;
				}

				li#toplevel_page_onecom-wp .wp-submenu-wrap li.accordion a {
					color: #B6BCC0;
					font-weight: 600;
					-webkit-font-smoothing: antialiased;
					cursor: pointer;
				}

				li#toplevel_page_onecom-wp .wp-submenu-wrap .panel li a,
				li#toplevel_page_onecom-wp .wp-submenu-wrap .panel li a:visited {
					font-size: 13px;
					color: #92989C;
					letter-spacing: -0.4px;
				}

				#adminmenu li#toplevel_page_onecom-wp .panel a:hover,
				#adminmenu li#toplevel_page_onecom-wp .panel a:focus {
					box-shadow: none;
				}

				li#toplevel_page_onecom-wp .accordion {
					transition: 0.4s;
				}

				li#toplevel_page_onecom-wp .accordion a:after {
					content: " ";
					background-image: url("data:image/svg+xml;charset=utf8,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0.5 0.75L5 5.25L9.5 0.75' stroke='%23B6BCC0'/%3E%3C/svg%3E");
					background-repeat: no-repeat;
					color: #777;
					float: right;
					height: 6px;
					width: 10px;
					margin-top: 7px;
				}

				li#toplevel_page_onecom-wp .accordion.active a:after {
					content: " ";
					background-image: url("data:image/svg+xml;charset=utf8,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9.5 5.25L5 0.75L0.5 5.25' stroke='%23B6BCC0'/%3E%3C/svg%3E");
					background-repeat: no-repeat;
					height: 6px;
					width: 10px;
				}

				li#toplevel_page_onecom-wp .panel {
					padding: 0 0 0 27px;
					max-height: 0;
					overflow: hidden;
					/* transition: max-height 0.2s ease-out; */
					border: none;
					margin: 0;
					background: transparent;
				}

				li#toplevel_page_onecom-wp .panel li a:hover {
					box-shadow: none;
				}

				li#toplevel_page_onecom-wp.opensub .panel li a {
					padding-left: 0;
					padding-right: 0;
				}

				#adminmenu li#toplevel_page_onecom-wp ul > li > a,
				.folded #adminmenu li#toplevel_page_onecom-wp li.menu-top .wp-submenu>li>a {
					padding: 5px 10px 5px 12px;
				}

				/* Mobile menu works on visiblity hidden */
				@media screen and ( max-width: 782px ) {
					li#toplevel_page_onecom-wp ul.wp-submenu-wrap {
						visibility: hidden;
					}
					.auto-fold #adminmenu li#toplevel_page_onecom-wp a {
						font-family: 'Open Sans', sans-serif;
						font-size: 13px;
					}
				}

				/* Desktop menu works on display none */
				@media screen and ( min-width: 783px ) {
					li#toplevel_page_onecom-wp ul.wp-submenu-wrap {
						display: none;
					}
				}

				li#toplevel_page_onecom-wp ul.wp-submenu-wrap {
					visibility: hidden;
				}

				li#toplevel_page_onecom-wp .wp-first-item {
					display: none;
				}

				li#toplevel_page_onecom-wp .wp-submenu-wrap li.current a.current,
				li#toplevel_page_onecom-wp .wp-submenu-wrap .panel li a:hover {
					color: #ffffff;
				}

				li#toplevel_page_onecom-wp .wp-submenu-wrap li.current a,
				li#toplevel_page_onecom-wp .wp-submenu-wrap li.current a:hover {
					font-weight: normal;
				}
			</style>
			<style>
				[class*=\" icon-oc-\"],
				[class^=icon-oc-] {
					speak: none;
					font-style: normal;
					font-weight: 400;
					font-variant: normal;
					text-transform: none;
					line-height: 1;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale
				}

				.icon-oc-one-com-white-32px-fill:before {
					content: \"\e901\"
				}

				.icon-oc-one-com:before {
					content: \"\e900\"
				}

				#one-com-icon,
				.toplevel_page_onecom-wp .wp-menu-image {
					speak: none;
					display: flex;
					align-items: center;
					justify-content: center;
					text-transform: none;
					line-height: 1;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale
				}

				.onecom-wp-admin-bar-item>a,
				.toplevel_page_onecom-wp>.wp-menu-name {
					font-size: 16px;
					font-weight: 400;
					line-height: 1
				}

				#adminmenu .toplevel_page_onecom-wp>.wp-menu-name img.onecom-menu-logo {
					width: 69px;
					height: 9px;
					max-width: 100%;
				}

				#adminmenu .wp-submenu-wrap.wp-submenu>.wp-submenu-head>img.onecom-menu-logo {
					width: 88px;
					height: auto;
					max-width: 100%;
				}

				.onecom-wp-admin-bar-item>a img {
					height: 7px !important
				}

				.onecom-wp-admin-bar-item>a img,
				.toplevel_page_onecom-wp>.wp-menu-name img {
					opacity: .8
				}

				.onecom-wp-admin-bar-item.hover>a img,
				.toplevel_page_onecom-wp.wp-has-current-submenu>.wp-menu-name img,
				li.opensub>a.toplevel_page_onecom-wp>.wp-menu-name img {
					opacity: 1
				}

				#one-com-icon:before,
				.onecom-wp-admin-bar-item>a:before,
				.toplevel_page_onecom-wp>.wp-menu-image:before {
					content: '';
					position: static !important;
					background-color: rgba(240, 245, 250, .4);
					border-radius: 102px;
					width: 18px;
					height: 18px;
					padding: 0 !important
				}

				.onecom-wp-admin-bar-item>a:before {
					width: 14px;
					height: 14px
				}

				.onecom-wp-admin-bar-item.hover>a:before,
				.toplevel_page_onecom-wp.opensub>a>.wp-menu-image:before,
				.toplevel_page_onecom-wp.wp-has-current-submenu>.wp-menu-image:before {
					background-color: #76b82a
				}

				.onecom-wp-admin-bar-item>a {
					display: inline-flex !important;
					align-items: center;
					justify-content: center
				}

				#one-com-logo-wrapper {
					font-size: 4em
				}

				#one-com-icon {
					vertical-align: middle
				}

				.imagify-welcome {
					display: none !important;
				}
				#onecom_marketplace .gv-text-indicator span {
					font-family: 'Open Sans', sans-serif;
					font-size: 13px;
					font-weight: 600;
					color: #B6BCC0;
				}
				a:hover #onecom_marketplace .gv-text-indicator span {
					color: #72aee6;
				}
				li.current a #onecom_marketplace .gv-text-indicator span {
					color: #ffffff;
				}
			</style>
			<?php
		}


		// Insert script & styles related to admin menu
		public function onecom_js_assets() {
			?>
			<script>
				// Group menu items to construct accordion based on href
				document.addEventListener("DOMContentLoaded", function() {

					// Prepare Health & Security accordion & panel
					jQuery(".wp-has-submenu a[href$='onecom-wp']").removeAttr("href")
					jQuery(".wp-submenu a[href$='onecom-health-security']").removeAttr("href").parent().addClass('accordion oc-health-security-accordion');
					jQuery("<div class='panel oc-health-security-panel'></div>").insertAfter(jQuery("li.oc-health-security-accordion"));
					// Add submenu items to Health & Security accordion panel
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-health-monitor']:not(.wp-first-item)").parent().appendTo(jQuery('.oc-health-security-panel'));
					jQuery('.wp-first-item span').removeAttr('id', 'onecom_home');
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-health-monitor']").parent().appendTo(jQuery('.oc-health-security-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-vulnerability-monitor']").parent().appendTo(jQuery('.oc-health-security-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-spam-protection']").parent().appendTo(jQuery('.oc-health-security-panel'));

					// Prepare Performance accordion & panel
					jQuery(".wp-submenu a[href$='onecom-performance-menu']").removeAttr("href").parent().addClass('accordion oc-performance-accordion');
					jQuery("<div class='panel oc-performance-panel'></div>").insertAfter(jQuery("li.oc-performance-accordion"));
					// Add submenu items to Performance accordion panel
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-vcache-plugin']").parent().appendTo(jQuery('.oc-performance-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-cdn']").parent().appendTo(jQuery('.oc-performance-panel'));
	
					// Prepare Utility accordion & panel
					jQuery(".wp-submenu a[href$='onecom-utility']").removeAttr("href").parent().addClass('accordion oc-utility-accordion');
					jQuery("<div class='panel oc-utility-panel'></div>").insertAfter(jQuery("li.oc-utility-accordion"));
					// Add submenu items to Utility accordion panel
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-online-shop']").parent().appendTo(jQuery('.oc-utility-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=one-webshop-settings']").parent().appendTo(jQuery('.oc-utility-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-error-page']").parent().appendTo(jQuery('.oc-utility-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=oc_onephoto']").parent().appendTo(jQuery('.oc-utility-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-cookie-banner']").parent().appendTo(jQuery('.oc-utility-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-wp-under-construction']").parent().appendTo(jQuery('.oc-utility-panel'));
					jQuery(".wp-submenu a[href$='admin.php?page=onecom-php-compatibility-scanner']").parent().appendTo(jQuery('.oc-utility-panel'));

                    // Remove duplicate marketplace and marketplace-products items before appending
                    // Keep only the first occurrence of each
                    let marketplaceItems = jQuery(".wp-submenu a[href$='onecom-marketplace']").parent();
                    if (marketplaceItems.length > 1) {
                        marketplaceItems.slice(1).remove();
                    }

                    let marketplaceProductsItems = jQuery(".wp-submenu a[href$='onecom-marketplace-products']").parent();
                    if (marketplaceProductsItems.length > 1) {
                        marketplaceProductsItems.slice(1).remove();
                    }

					// Display was none for desktop, so display now
					let mediaQuery = window.matchMedia('(min-width: 783px)')
					if (mediaQuery.matches) {
						jQuery("li#toplevel_page_onecom-wp ul.wp-submenu-wrap").show();
					}

				/**
				 * Expand submenu for current active item (Desktop - when page loaded)
				 * This only works in desktop becuase in mobile menu is hidden (display:none) by default, and
				 * we cannot get height of element (.panel) inside hidden wrap (#adminmenuwrap)
				 */
				jQuery('li#toplevel_page_onecom-wp li.current').each(function() {
					// Only expand if this is a child item inside a panel, not the accordion header itself
					if (jQuery(this).parent().hasClass('panel')) {
						let submenu_height = jQuery(this).parent(".panel").prop('scrollHeight') + "px";
						jQuery(this).parent(".panel").prev().addClass('active');
						jQuery(this).parent(".panel").css('max-height', submenu_height);
					}
				});

					// Display menu only after submenu items arranged
					jQuery("li#toplevel_page_onecom-wp ul.wp-submenu-wrap").css('visibility', 'visible');

					/**
					 * Expand submenu for current active item (Mobile - when mobile menu opened/toggled)
					 * Expand (settings panel height) only works when mobile menu is visible and
					 * * it is hidden until clicked on #wp-admin-bar-menu-toggle.
					 * Hooking into #wp-admin-bar-menu-toggle also does not work becuase our code fires before submenu visible
					 * * therefore we used mutuation observer to detect dom change along with aria-expanded
					 * * It seems that aria-expanded is set to true after mobile menu is visible and somehow this works for us
					 */

					// Detect change in #wp-admin-bar-menu-toggle & aria-expanded is true, expand one.com current menu
				let mutationObserver = new MutationObserver(function(mutations) {
						mutations.forEach(function(mutation) {
							// console.log(mutation);
							if (jQuery('#wp-admin-bar-menu-toggle a').attr('aria-expanded') === "true") {
								jQuery('li#toplevel_page_onecom-wp li.current').each(function() {
									// Only expand if this is a child item inside a panel, not the accordion header itself
									if (jQuery(this).parent().hasClass('panel')) {
										let submenu_height = jQuery(this).parent(".panel").prop('scrollHeight') + "px";
										jQuery(this).parent(".panel").prev().addClass('active');
										jQuery(this).parent(".panel").css('max-height', submenu_height);
									}
								});
							}
						});
					});

					// Starts listening for changes in the HTML element (#wp-admin-bar-menu-toggle) of the page.
					if( jQuery('#wp-admin-bar-menu-toggle').length ) {
						mutationObserver.observe(document.querySelector("#wp-admin-bar-menu-toggle"), {
							attributes: true,
							characterData: true,
							childList: true,
							subtree: true,
							attributeOldValue: true,
							characterDataOldValue: true
						});
					}
					// accordion to expand/collapse menu items (when clicked .accordion)
					let acc = document.getElementsByClassName("accordion");
					let i;

					for (i = 0; i < acc.length; i++) {
						acc[i].addEventListener("click", function() {
							this.classList.toggle("active");
							let panel = this.nextElementSibling;
							if (panel.style.maxHeight) {
								panel.style.maxHeight = null;
							} else {
								panel.style.maxHeight = panel.scrollHeight + "px";
							}
						});
					}

				});
			</script>
			<?php
		}
	}
}

// WPIN-8739 renamed Onecom_Nested_Menu to GroupOne_Nested_Menu with no
// alias — the hub's activation hook still instantiates the old name
// directly (onecom-themes-plugins.php), which fatals without this.
if ( class_exists( 'GroupOne_Nested_Menu' ) && ! class_exists( 'Onecom_Nested_Menu' ) ) {
	class_alias( 'GroupOne_Nested_Menu', 'Onecom_Nested_Menu' );
}