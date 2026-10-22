<?php
/**
 * adds one.com Shortcuts to WordPress admin
 */


if ( ! class_exists( 'GroupOne_Shortcuts' ) ) {
	class GroupOne_Shortcuts {




		public $url;
		public $plugin_url;
		public $theme_url;
		public $staging_url;
		public $cookie_banner_url;
		public $health_tooltip;
		public $theme_tooltip;
		public $plugin_tooltip;
		public $cookie_banner_tooltip;
		const WID                   = 'ocsh_dashboard_widget';
		const ONECOM                = 'one.com ';
		const DASHBOARD             = 'dashboard';
		const SCORE                 = 'score';
		const HEALTH_MONITOR        = 'Health Monitor';
		const VULNERABILITY_MONITOR = 'Vulnerability Monitor';
		const SCAN_NOW              = 'Scan now';
		const CREATE_STAGING_SITE   = 'Create staging';


		public function __construct() {
			$this->url                   = menu_page_url( 'onecom-wp-health-monitor', false );
			$this->staging_url           = (empty(menu_page_url( 'onecom-wp-staging', false ))) ? menu_page_url( 'onecom-wp-staging-blocked', false ) : menu_page_url( 'onecom-wp-staging', false );
			$this->theme_url             = menu_page_url( 'onecom-wp-themes', false );
			$this->cookie_banner_url     = menu_page_url( 'onecom-wp-cookie-banner', false );
			//plugin page one.com Plugins menu
			$this->plugin_url            = ( is_multisite() && is_network_admin() ) ? network_admin_url( 'admin.php?page='.MARKETPLACE_PAGE_SLUG ) : admin_url( 'admin.php?page='.MARKETPLACE_PAGE_SLUG );
			$this->health_tooltip        = addslashes( __( 'Health Monitor scans your website for potential security issues and checks the overall state of your site.', 'onecom-wp' ) );
			$this->theme_tooltip         = addslashes( __( 'Exclusive themes specially crafted for one.com customers.', 'onecom-wp' ) );
			$this->cookie_banner_tooltip = addslashes( __( 'Show a banner on your website to inform visitors about cookies and get their consent.', 'onecom-wp' ) );

			add_action( 'wp_dashboard_setup', array( $this, 'osch_widget_cb' ) );
			//removed themes shortcut link from the welcome panel//
			add_action( 'admin_head-themes.php', array( $this, 'groupone_themes_button' ) );
			add_action( 'admin_head-plugins.php', array( $this, 'groupone_plugins_button' ) );
			add_action( 'admin_head-plugin-install.php', array( $this, 'groupone_plugins_button' ) );
			add_action( 'admin_head-options-privacy.php', array( $this, 'groupone_cookie_banner_box' ) );
			add_action( 'admin_head-options-general.php', array( $this, 'groupone_staging_button' ) );
			add_action( 'admin_head-site-health.php', array( $this, 'groupone_site_health_info' ) );
			add_action( 'tool_box', array( $this, 'tools_page_staging_box' ) );
			add_action( 'tool_box', array( $this, 'tools_page_health_monitor_box' ) );
			add_action( 'admin_head', array( $this, 'groupone_button_css' ) );
		}

		/**
		 * adds widget to the wp admin dashboard
		 */

		public function osch_widget_cb() {

			$user = wp_get_current_user();

			if ( ( ! isset( $user->roles ) ) || ( ! in_array( 'administrator', (array) $user->roles ) ) ) {
				return;
			}
			wp_add_dashboard_widget(
				self::WID,
				'<div class="oc-widget-header">
                    <div class="oc-flex oc-head-wrap">
                         <img class="onecom-logo" alt="onecom-logo" src="' . GROUPONE_WP_URL . '/assets/images/one.com-logo@2x.svg" />
                         <a class="oc-header-link" target="_blank" href="' . GROUPONE_CP_LOGIN_URL . '">' . __( 'Go to control panel', 'onecom-wp' ) . '<img class="oc-expand" alt="onecom-logo" src="' . GROUPONE_WP_URL . '/assets/images/expand.svg" /></a>
                    </div>
            </div>',
				array( $this, 'ocsh_widget_cb' )
			);

			global $wp_meta_boxes;
			if ( isset( $wp_meta_boxes[ self::DASHBOARD ] ) ) {
				$normal_dashboard      = $wp_meta_boxes[ self::DASHBOARD ]['normal']['core'];
				$example_widget_backup = array( self::WID => $normal_dashboard[ self::WID ] );
				unset( $normal_dashboard[ self::WID ] );
				$sorted_dashboard                                   = array_merge( $example_widget_backup, $normal_dashboard );
				$wp_meta_boxes[ self::DASHBOARD ]['normal']['core'] = $sorted_dashboard;
			}
		}


		/**
		 * checks for health monitor scan in db and returns value based on the result
		 */

		public function ocsh_widget_cb() {

			$site_scan_transient = get_site_transient( 'ocsh_site_scan_result' );
			$site_scan_result    = groupone_sh_calculate_score( $site_scan_transient );
			$colors              = array(
				'poor' => '#D20019',
				'ok'   => '#FF755A',
				'good' => '#76B82A',
			);
			$color               = $colors['good'];
			$score               = '';
			if ( isset( $site_scan_result[ self::SCORE ] ) ) {
				if ( $site_scan_result[ self::SCORE ] < 75 && $site_scan_result[ self::SCORE ] >= 50 ) {
					$color = $colors['ok'];
				} elseif ( $site_scan_result[ self::SCORE ] < 50 ) {
					$color = $colors['poor'];
				}
				$score = '<span class="hm-score" style="color:' . $color . '">' . round( $site_scan_result[ self::SCORE ] ) . '%</span>';
			}


			echo '            <div class="activity-block">
                <div class="oc-flex oc-underline">
                    <h3>' . __( self::HEALTH_MONITOR, 'onecom-wp' ) . '</h3>
                    <span><a href="' . $this->url . '">' . __( 'More insights', 'onecom-wp' ) . '</a></span>
                </div>';

			if ( $site_scan_result && isset( $site_scan_result['todo'] ) ) {
				$todo = $site_scan_result['todo'];

				echo '<div class="oc-hm-info">' . __( 'Score', 'onecom-wp' ) . ':' . $score . '
                        <div class="hm-bar-bg">
                            <div class="hm-bar-progress" style="background-color: ' . $color . '; width:' . round( $site_scan_result[ self::SCORE ] ) . '%"></div>
                        </div>
                    </div>
                    <div class="oc-hm-info"><span class="ocsh_widget_todo">' . __( 'To do', 'onecom-wp' ) . ':</span> <span class="ocsh_todo_count_widget">' . $todo . ' ' . __( 'items', 'onecom-wp' ) . '</span></div>';
			} else {
				echo '<div class="oc-hm-desc">' . __( 'Health Monitor lets you monitor the essential security and performance checkpoints and fix them if needed.', 'onecom-wp' ) . '</div>
                    <div>
                        <a class="btn button_1" title="' . $this->health_tooltip . '" href="' . $this->url . '">' . __( self::SCAN_NOW, 'onecom-wp' ) . '</a>
                </div>';
			}

			echo $this->render_html( false );
		}


		/**
		 * generates html for the dashboard widget
		 * @param bool $scan
		 */


		public function render_html( $scan = false ) {
			?>
			</div>
			<div class="activity-block">
				<div class="oc-flex oc-underline">
					<h3><?php _e( 'Discover', 'onecom-wp' ); ?></h3>
					<span>
						<a href="https://help.one.com/hc/en-us/categories/360002171377-WordPress" target="_blank">
							<?php _e( 'See all guides', 'onecom-wp' ); ?>
						</a>
					</span>
				</div>

				<?php
				if ( ismWP() ) {
					$this->groupone_dashboard_mwp_links();
				} else {
					$this->groupone_dashboard_non_mwp_links();
				}
				?>


			</div>
			<div class="activity-block oc-reset-wlk-tour">
				<div class="oc-restart-tour">
					<?php _e( 'Missed plugin introduction?', 'onecom-wp' ); ?> <a href="#"><?php _e( 'Restart tour', 'onecom-wp' ); ?></a><span id="oc_hmwidget_spinner" class="oc_cb_spinner spinner"></span>
				</div>


			</div>


			<?php
		}

		public function groupone_dashboard_non_mwp_links() {
			$marketplace_url     = admin_url( 'admin.php?page=onecom-marketplace' );
			$wpr_marketplace_url = admin_url( 'admin.php?page=onecom-marketplace&plugin=wp-rocket' );
			?>
			<ul class="oc-discover-links">
					<li>
						<span>
							<a href="<?php echo esc_url( $marketplace_url ); ?>" target="_blank">
								<?php _e( 'Fuel your online success with powerful add-ons', 'onecom-wp' ); ?>
							</a>
							<strong style="font-size:10px;background:#00a32a;color:#fff;padding:1px 5px;border-radius:3px;margin-left:5px;vertical-align:middle;"><?php _e( 'NEW', 'onecom-wp' ); ?></strong>
						</span>
						<span>
							<a href="<?php echo esc_url( $marketplace_url ); ?>" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/6555011842705-How-can-I-improve-the-speed-of-my-WordPress-site-" target="_blank">
								<?php _e( 'How can I improve the speed of my WordPress site', 'onecom-wp' ); ?>
							</a>
						</span>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/6555011842705-How-can-I-improve-the-speed-of-my-WordPress-site-" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/360012045457-How-to-optimise-the-WordPress-database-" target="_blank">
								<?php _e( 'How to optimise the WordPress database', 'onecom-wp' ); ?>
							</a>
						</span>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/360012045457-How-to-optimise-the-WordPress-database-" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/115005586009-Improve-security-of-your-WordPress-site" target="_blank">
								<?php _e( 'Improve security of your WordPress site', 'onecom-wp' ); ?>
							</a></span>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/115005586009-Improve-security-of-your-WordPress-site" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="<?php echo esc_url( $wpr_marketplace_url ); ?>" target="_blank">
								<?php _e( 'Improve performance of your WordPress site', 'onecom-wp' ); ?>
							</a>
						</span>
						<span>
							<a href="<?php echo esc_url( $wpr_marketplace_url ); ?>">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/360020315097-What-is-one-com-s-Managed-WordPress-" target="_blank">
								<?php _e( "What is one.com's Managed WordPress", 'onecom-wp' ); ?>
							</a>
						</span>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/360020315097-What-is-one-com-s-Managed-WordPress-" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
				</ul>
			<?php
		}

		public function groupone_dashboard_mwp_links() {
			$marketplace_url     = admin_url( 'admin.php?page=onecom-marketplace' );
			$wpr_marketplace_url = admin_url( 'admin.php?page=onecom-marketplace&plugin=wp-rocket' );
			?>
			<ul class="oc-discover-links">
					<li>
						<span>
							<a href="<?php echo esc_url( $marketplace_url ); ?>" target="_blank">
								<?php _e( 'Fuel your online success with powerful add-ons', 'onecom-wp' ); ?>
							</a>
							<strong style="font-size:10px;background:#00a32a;color:#fff;padding:1px 5px;border-radius:3px;margin-left:5px;vertical-align:middle;"><?php _e( 'NEW', 'onecom-wp' ); ?></strong>
						</span>
						<span>
							<a href="<?php echo esc_url( $marketplace_url ); ?>" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/8096988382353-What-is-Maintenance-Mode-" target="_blank">
								<?php _e( 'What is Maintenance Mode', 'onecom-wp' ); ?>
							</a>
						</span>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/8096988382353-What-is-Maintenance-Mode-" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/115005586009-Improve-security-of-your-WordPress-site" target="_blank">
								<?php _e( 'Improve security of your WordPress site', 'onecom-wp' ); ?>
							</a></span>
						<span>
							<a href="https://help.one.com/hc/en-us/articles/115005586009-Improve-security-of-your-WordPress-site" target="_blank">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
					<li>
						<span>
							<a href="<?php echo esc_url( $wpr_marketplace_url ); ?>" target="_blank">
								<?php _e( 'Improve performance of your WordPress site', 'onecom-wp' ); ?>
							</a>
						</span>
						<span>
							<a href="<?php echo esc_url( $wpr_marketplace_url ); ?>">
								<img alt="" src="<?php echo GROUPONE_WP_URL . '/assets/images/link.svg'; ?>" />
							</a>
						</span>
					</li>
				</ul>
			<?php
		}


		/**
		 * adds the one.com themes button to the themes screen
		 */

		public function groupone_themes_button() {

			$label = self::ONECOM . __( 'Themes', 'onecom-wp' );
			$title = $this->theme_tooltip;

			$this->groupone_append_buttons( $this->theme_url, $label, '', $title );
		}

		/**
		 * adds the one.com plugins button to the plugins screen
		 */


		public function groupone_plugins_button() {

			$label = self::ONECOM . __( 'Marketplace', 'onecom-wp' );

			$this->groupone_append_buttons( $this->plugin_url, $label, '', '' );
		}

		/**
		 * generates and appends the buttons through jquery
		 * @param $url url link to be plaved on the button
		 * @param $label string  label of button
		 * @param string $desc  description for general screen under settings
		 * @param string $title  tooltip for buttons
		 * @param bool $new  if the button is for add new media screen
		 */

		public function groupone_append_buttons( $url, $label, $desc = '', $title = '', $new = false ) {

			if ( $desc === '' ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready( function($)
					{
						<?php if ( ! $new ) { ?>
                        $('<a href="<?php echo $url; ?>"  title="<?php echo $title; ?>" class="oc_button"><span><?php echo $label; ?></span> <span><img src="<?php echo GROUPONE_WP_URL; ?>/modules/home/assets/icons/arrow_forward.svg" aria-hidden="true" height="20px" width="20px" alt="<?php echo $title; ?>" /> </span><span class="oc_plugin_tooltip"><?php _e( 'Explore and install plugins recommended by one.com to boost your website and business.', 'onecom-wp' ) ?>  </span></a>').insertAfter('.page-title-action');

						<?php } else { ?>
						$('.wrap h1').append('<a href="<?php echo $url; ?>" title="<?php echo $title; ?>"  class="oc_button"><?php echo $label; ?></a>');

						<?php } ?>

					});
				</script>

				<?php
			} else {
				?>
				<script type="text/javascript">
					jQuery(document).ready( function($)
					{
						$('<p class="description"><?php echo $desc; ?> <a href="<?php echo $url; ?>"><?php echo $label; ?></a>.</p>').insertAfter('#home-description');
					});
				</script>


				<?php
			}
		}
		/**
		 * adds the staging link to the settings screen
		 */

		public function groupone_staging_button() {

			$desc  = addslashes( __( 'Create a staging version of your site to try out new plugins, themes and customizations', 'onecom-wp' ) );
			$label = __( self::CREATE_STAGING_SITE, 'onecom-wp' );

			$this->groupone_append_buttons( $this->staging_url, $label, $desc );
		}

		/**
		 * adds the staging box to the tools screen
		 */
		public function tools_page_staging_box() {
			$title = __( 'Staging', 'onecom-wp' );
			$desc  = __( 'Create a staging version of your site to try out new plugins, themes and customizations', 'onecom-wp' );
			$label = __( self::CREATE_STAGING_SITE, 'onecom-wp' );
			$this->tools_page_content_render_html( $title, $desc, $label, $this->staging_url );
		}


		/**
		 * adds the health monitor box to the tools screen
		 */

		public function tools_page_health_monitor_box() {
			$title = __( self::HEALTH_MONITOR, 'onecom-wp' );
			$desc  = __( 'Health Monitor scans your website for potential security issues and checks the overall state of your site.', 'onecom-wp' );
			$label = __( self::SCAN_NOW, 'onecom-wp' );
			$this->tools_page_content_render_html( $title, $desc, $label, $this->url );
		}


		/**
		 * returns html for the boxes on tools screen
		 */

		public function tools_page_content_render_html( $title, $desc, $label, $url ) {
			echo '<div class="card">
                <h2 class="title">' . $title . '</h2>
                <p>' . $desc . '</p>
                <p><a class="button" href="' . $url . '">' . $label . '</a></p>
            </div>';
		}

		/**
		 * css for the shortcuts to be added
		 */

		public function groupone_button_css() {

            echo "<style>
			
                    .oc_button {
                      display: inline-flex;
                      align-items: center;
                      gap: 4px;
                      margin-left: 10px;
                      position: relative;
                      box-sizing: border-box;
                      top: -2px;
                      background-color: #ffffff;
                      font-family: 'Plus Jakarta Sans', sans-serif;
                      font-size: 14px;
                      line-height: 1.2;
                      padding: 2px 16px;
                      border-radius: 8px;
                      border: 1px solid #0078C8;
                      cursor: pointer;
                      text-decoration: none;
                      text-shadow: none;
                      color: #141414;
                      min-height: 30px;
                    }
                    
                    /* Responsive: Move button to next line on small screens */
                    @media (max-width: 480px) {
                      .oc_button {
                        display: flex;
                        margin-left: 0;
                        margin-top: 11px;
                        margin-bottom: 6px;
                        width: fit-content;
                      }
                      /* Hide tooltip on mobile - hover doesn't work on touch devices */
                      .oc_button .oc_plugin_tooltip {
                        display: none !important;
                      }
                    }
                           
                    .oc_button img {
                      display: block;
                    }
                    
                    .oc_button .oc_plugin_tooltip {
                      font-weight: 500;
                      box-sizing: border-box;
                      font-size: 12px;
                      line-height: 1.5;
                      visibility: hidden;
                      width: 200px;
                      background-color: #282629;
                      color: #F7F7F7;
                      border-radius: 6px;
                      padding: 16px;
                      position: absolute;
                      z-index: 1;
                      top: 150%;
                      left: 50%;
                      transform: translateX(-50%); /* Better centering method */
                    }
                    
                    .oc_button .oc_plugin_tooltip::after {
                      content: '';
                      position: absolute;
                      bottom: 100%;
                      left: 50%;
                      transform: translateX(-50%); /* Changed from margin-left for perfect centering */
                      border-width: 5px;
                      border-style: solid;
                      border-color: transparent transparent #282629 transparent;
                    }
                    
                    .oc_button:hover {
                      color: #141414;
                    }
                    
                    .plugins-php .oc_button:hover .oc_plugin_tooltip {
                      visibility: visible;
                    }
                    
                    #ocsh_dashboard_widget{
                      color: #3C3C3C;
                    }
                   #ocsh_dashboard_widget .oc-header-link{
						color: #0078C8;
						font-family: Montserrat;
						font-size: 14px;
						font-weight: 500;
						line-height: 22px;
						letter-spacing: 0.25px;
						text-align: left;
						text-decoration:none!important;
						margin-left: 16px

                    }
                    img.oc-expand {
						margin-left: 8px;
						vertical-align: middle;
					}
                    #ocsh_dashboard_widget .postbox {
                        border: 1px solid #BBBBBB;
                    }
                    #ocsh_dashboard_widget .inside {
                        padding:0;
                        margin: 0;
                    }
                    #ocsh_dashboard_widget .activity-block {
                        border: 0;
                        padding: 0;
                        margin: 24px 24px 18px 24px;
                    }
                    #ocsh_dashboard_widget {
                        font-family: 'Open Sans', sans-serif;
                        -webkit-font-smoothing: antialiased;
                    }
                    #ocsh_dashboard_widget h3{
                        font-size: 18px;
                        font-weight: 600;
                        color: #3C3C3C;
                        line-height: 36px;
                    }
                    #ocsh_dashboard_widget .activity-block h3 {
                        line-height: 30px;
                        margin: 0;
                    }
                    #ocsh_dashboard_widget p{
                        color: #3C3C3C;
                        font-size: 14px;
                        line-height: 24px;
                        margin: 0;
                    }

                    #ocsh_dashboard_widget .btn.button_1,
                    #ocsh_dashboard_widget .btn.button_3,
                    #ocsh_dashboard_widget .btn.button_4 {
                        padding: 0 30px;
                        border-radius: 100px;
                        text-decoration: none;
                        display: inline-block;
                        line-height: 30px;
                        font-weight: 700;
                        cursor: pointer;
                        -webkit-transition: all 0.2s ease-in-out;
                        -moz-transition: all 0.2s ease-in-out;
                        transition: all 0.2s ease-in-out;
                        border: none;
                        font-size: 12px;
                }
                #ocsh_dashboard_widget .btn.button_4 {
                    font-family: Montserrat, sans-serif;
                    color: #0078C8;
                    border: 1px solid #0078C8;
                    font-weight: 500;
                    flex-shrink: 0;
                }

                    #ocsh_dashboard_widget .btn.button_1 {
                        margin-top: 16px;
                        font-weight: 700;
                        font-size: 12px;
                        color: #ffffff;
                    }
                    #ocsh_dashboard_widget .btn.button_1:hover{
                        -webkit-transition: all 0.2s ease-in-out;
                        -moz-transition: all 0.2s ease-in-out;
                        transition: all 0.2s ease-in-out;
                        background-color: #284f90;
                        border: none;
                        color: #ffffff;
                    }
                    #ocsh_dashboard_widget .activity-block:first-child{
                        margin-bottom: 21px;
                    }
                    #ocsh_dashboard_widget .oc-reset-wlk-tour{
                        margin: 0;
                        padding: 20px;
                        background-color: #D9EBF7;
                        font-size: 14px;
                    }
                    #ocsh_dashboard_widget a,
                    #ocsh_dashboard_widget a:visited {
                        text-decoration: none;
                        color: #0078C8;
                        font-weight: 600;
                    }
                    #ocsh_dashboard_widget .oc-reset-wlk-tour img.onecom-logo{
                        width: 97.2px;
                        float: right;
                        height: 12px;
                        margin-top: 8px;
                    }
                    #ocsh_dashboard_widget .oc-widget-header {
                        padding: 0;
                    }
                    #ocsh_dashboard_widget .oc-widget-header img.onecom-logo {
                        width: 130px;
                        height: 16px;
                        max-width: 100%;
                    }
                    #ocsh_dashboard_widget .oc-flex {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    #ocsh_dashboard_widget .oc-underline {
                        position: relative;
                        margin-bottom: 9px;
                    }
                    #ocsh_dashboard_widget .oc-underline a,
                    #ocsh_dashboard_widget .oc-underline a:visited {
                        font-weight: 700;
                        line-height: 30px;
                        font-size: 12px;
                    }
                    #ocsh_dashboard_widget .oc-underline::after {
                        content : '';
                        position : absolute;
                        width : 100%;
                        height : 1px;
                        background-color: #CECECE;
                        bottom: 0;
                        left: 0;
                    }
                    #ocsh_dashboard_widget .oc-hm-info {
                        font-weight: 500;
                        font-size: 14px;
                        line-height: 24px;
                        display: flex;
                        align-items: center;
                    }
                    #ocsh_dashboard_widget .oc-hm-desc {
                        font-weight: 500;
                        font-size: 14px;
                        line-height: 24px;
                    }
                    #ocsh_dashboard_widget .hm-bar-bg{
                        background-color: #EDEDED;
                        width: 160px;
                        max-width: 100%;
                        height: 6px;
                        margin-left: 8px;
                    }
                    #ocsh_dashboard_widget .hm-bar-progress {
                        background-color: #76B82A;
                        height: 6px;
                    }
                    #ocsh_dashboard_widget span.hm-score {
                        margin-left: 8px;
                        margin-bottom: 1px;
                    }
                    #ocsh_dashboard_widget .oc-discover-links {
                        margin-top: 12px;
                    }
                    #ocsh_dashboard_widget .oc-discover-links li {
                        font-size: 14px;
                        line-height: 24px;
                        margin-bottom: 12px;
                    }
                    #ocsh_dashboard_widget .oc-discover-links li span:last-child {
                        margin-left: 2px;
                        display: inline-flex;
                        vertical-align: middle;
                    }
                    #ocsh_dashboard_widget .oc-discover-links li span:last-child a {
                        display: inline-flex;
                        align-items: center;
                        line-height: 24px;
                    }
                    #ocsh_dashboard_widget .oc-discover-links img {
                        height: 14px;
                        width: 14px;
                    }
                    #ocsh_dashboard_widget .oc-discover-links a,
                    #ocsh_dashboard_widget .oc-discover-links a:visited {
                        color: #3C3C3C;
                        font-weight: 400;
                    }
                    #ocsh_dashboard_widget .oc-cp-link {
                        display: none;
                    }
                    #ocsh_dashboard_widget span.ocsh_todo_count_widget{
                        font-weight: 600;
                        margin-left: 8px;
                    }
                    @media screen and (max-width: 1200px) and (min-width: 782px){
                       #ocsh_dashboard_widget .oc-flex.oc-head-wrap{
                                flex-direction: column;
    							align-items: flex-start;
    							gap: 4px;
                        }
						#ocsh_dashboard_widget .oc-header-link{
							margin-left: 0;
						}

                    }
                     @media screen and (max-width: 1024px){
                     #ocsh_dashboard_widget .oc-flex.oc-head-wrap{
                                flex-direction: column;
    							align-items: flex-start;
    							gap: 4px;
                        }
                     #ocsh_dashboard_widget .oc-header-link{
                        margin-left: 0;
                    }
                     }
                    @media screen and (max-width: 782px){
                        #ocsh_dashboard_widget .btn.button_4,
                        #ocsh_dashboard_widget #oc_hmwidget_spinner,
                        #ocsh_dashboard_widget .oc-reset-wlk-tour{
                            display: none;
                        }
                        #ocsh_dashboard_widget .oc-cp-link,
                        #ocsh_dashboard_widget .oc-cp-link .btn.button_4 {
                            display: flex;
                        }
                        #ocsh_dashboard_widget .oc-cp-link {
                            margin-top: 16px;
                            margin-bottom: 24px;
                        }

                    }
                    @media (max-width: 576px) {
                        #ocsh_dashboard_widget .activity-block h3 {
                            line-height: 25px;
                        }
                        #ocsh_dashboard_widget .hm-bar-bg {
                            width: 100%;
                        }
                        #ocsh_dashboard_widget .oc-underline span {
                            margin-bottom: 4px;
                        }
                        #ocsh_dashboard_widget .oc-underline a,
                        #ocsh_dashboard_widget .oc-underline a:visited {
                            font-size: 14px;
                            line-height: 19px;
                        }
                        #ocsh_dashboard_widget .oc-cp-link {
                            display: block;
                            text-align: center;
                            margin-top: 40px;
                        }
                        #ocsh_dashboard_widget .oc-cp-link .btn.button_4 {
                            display: block;
                            text-align: center;
                            line-height: 22px;
                            font-size: 14px;
                            padding: 12px 24px;
                        }
                        #ocsh_dashboard_widget .activity-block:first-child {
                            margin-bottom: 37px;
                        }
                        #ocsh_dashboard_widget .btn.button_1 {
                            display: flex;
                            text-align: center;
                            justify-content: center;
                            line-height: 50px;
                            font-size: 16px;
                            margin: 24px 0 32px 0;
                        }
                        #ocsh_dashboard_widget p {
                            font-size: 18px;
                            line-height: 32px;
                        }
                        #ocsh_dashboard_widget .oc-discover-links li {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }
                        #ocsh_dashboard_widget .oc-underline {
                            flex-direction: column;
                            align-items: flex-start;
                        }

                    }
                    @media screen and (min-width: 1500px){
                    #ocsh_dashboard_widget .oc-flex.oc-head-wrap{
                                flex-direction: column;
    							align-items: flex-start;
    							gap: 4px;
                        }
                    #ocsh_dashboard_widget .oc-header-link{
                        margin-left: 0;
                    }
					}
					</style>";
		}





		/**
		 * adds the cookie banner box to the privacy screen
		 */
		public function groupone_cookie_banner_box() {

			$title       = __( 'one.com Cookie Banner', 'onecom-wp' );
			$description = addslashes( __( 'Show a banner on your website to inform visitors about cookies and get their consent.', 'onecom-wp' ) );
			$label       = __( 'Cookie banner', 'onecom-wp' );

			?>
			<script type="text/javascript">
				jQuery(document).ready( function($)
				{
					$('<div class="card">\n' +
						'<h2 class="title"><?php echo $title; ?></h2>\n' +
						' <p><?php echo $description; ?></p> \n' +
						' <p><a class="button" href="<?php echo $this->cookie_banner_url; ?>"><?php echo $label; ?></a></p>\n' +
						' </div>').insertAfter('.tools-privacy-policy-page');
				});
			</script>

			<?php
		}

		/**
		 * adds the health monitor text to the site health screen
		 */

		public function groupone_site_health_info() {
			$health_des = addslashes( __( 'Health Monitor scans your website for potential security issues and checks the overall state of your site', 'onecom-wp' ) );
			?>
			<script type="text/javascript">
				jQuery(document).ready( function($)
				{
					$('body').find('.health-check-body').append('<br/><p class="description"><?php echo $health_des; ?>&nbsp;<a title="<?php echo $this->health_tooltip; ?>" href="<?php echo $this->url; ?>"><?php _e( self::SCAN_NOW, 'onecom-wp' ); ?></a></p>');
				});
			</script>


			<?php
		}
	}
}