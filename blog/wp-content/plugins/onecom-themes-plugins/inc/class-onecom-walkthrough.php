<?php
/**
 * adds one.com walkthrough tour to wordpress admin
 */
if ( ! class_exists( 'GroupOne_Walkthrough' ) ) {
	class GroupOne_Walkthrough {
		const BUTTON_2           = 'button2';
		const CONTENT            = 'content';
		const PARENT             = 'parent';
		const VCACHE_PLUGIN      = 'onecom-vcache/vcaching.php';
		const WEBSHOP_PLUGIN     = 'onecom-webshop/webshop.php';
		const WEBSHOP_SETTINGS   = 'one-webshop-settings';
		const PHP_SCANNER        = 'onecom-php-scanner/onecom-compatibility-scanner.php';
		const GROUPONE_FUNCTION        = 'function';
		const DISMISSED_POINTERS = 'dismissed_wp_pointers';

		// Initiate construct
		public function __construct() {
			add_action(
				'admin_enqueue_scripts',
				array(
					$this,
					'groupone_enqueue_scripts',
				)
			);
			add_action( 'wp_ajax_ocwkt_reset_tour', array( $this, 'groupone_walkthrough_reset_tour' ) );
			add_action( 'admin_head', array( $this, 'groupone_restart_tour' ) );
		}

		public function groupone_enqueue_scripts() {
			$dismissed            = explode( ',', get_user_meta( wp_get_current_user()->ID, self::DISMISSED_POINTERS, true ) );
			$do_tour              = ! in_array( 'oc_walthrough_pointer', $dismissed );
			$welcome_modal_closed = false;
			$user_id              = get_current_user_id();
			if ( $user_id ) {
				$welcome_modal_closed = get_user_meta( $user_id, 'oc-welcome-modal-closed', true );
			}

			if ( $do_tour && $welcome_modal_closed ) {
				wp_enqueue_style( 'wp-pointer' );
				wp_enqueue_script( 'wp-pointer' );

				add_action(
					'admin_print_footer_scripts',
					array(
						$this,
						'walkthrough_footer_scripts',
					)
				);
			}
			add_action( 'admin_head', array( $this, 'css_admin_head' ) );
		}

		public function css_admin_head() {
			?>
			<style>
				#oc_hmwidget_spinner {
					background: url(data:image/gif;base64,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) no-repeat;
					background-size: 20px 20px;
					display: inline-block;
					visibility: hidden;
					float: none;
					vertical-align: middle;
					opacity: .7;
					width: 20px;
					height: 20px;
					margin: 0 10px 0;
				}

				/* Arrow styling */
				.oc-pointer-wrap .ocwt_pointer .wp-pointer-arrow,
				.oc-pointer-wrap .ocwt_pointer .wp-pointer-arrow-inner {
					border-right-color: #282629 !important;
				}

				.oc-pointer-wrap {
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					background: rgba(60, 60, 60, 0.5);
					z-index: 99999;
				}

				.oc-reset-wlk-tour span.oc_reset {
					text-decoration: none !important;
					color: #d5d5d5;
					float: left;
					margin-right: 10px;
				}

				@media screen and (max-width: 782px) {
					.ocwt_pointer, .oc-pointer-wrap, .oc-reset-wlk-tour a {
						display: none;
					}
				}
			</style>
			<?php
		}

		public function walkthrough_footer_scripts() {
			$tour = $this->groupone_generate_array();

			$page = isset( $_GET['page'] ) ? $_GET['page'] . '-tour' : '';
			$tab  = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

			$function     = '';
			$button2      = '';
			$id           = '';
			$options      = array();
			$show_pointer = false;

			if ( $page != '' && in_array( $page, array_keys( $tour ) ) && $tab == '' ) {
				$show_pointer = true;
				$parent       = true;
				$screen       = $page;
				if ( isset( $tour[ $page ]['id'] ) ) {
					$id = $tour[ $page ]['id'];
				}
				$options = array(
					self::CONTENT  => $tour[ $page ][ self::CONTENT ],
					'pointerWidth' => '342',
					'position'     => array(
						'edge'  => 'left',
						'align' => 'left',
					),
				);

				$button2  = false;
				$function = '';

				if ( isset( $tour[ $page ][ self::BUTTON_2 ] ) ) {
					$button2 = $tour[ $page ][ self::BUTTON_2 ];
				}
				if ( isset( $tour[ $page ][ self::GROUPONE_FUNCTION ] ) ) {
					$function = $tour[ $page ][ self::GROUPONE_FUNCTION ];
				}
				$parent = $tour[ $page ][ self::PARENT ];
			}

			if ( $show_pointer && $id == '#onecom_themes' ) {
				$this->make_pointer_script( $id, $options, __( 'Got it!', 'onecom-wp' ), $button2, $function, $parent, $screen );
			} elseif ( $show_pointer && $id == '#onecom_home' ) {
				$this->make_pointer_script( $id, $options, __( 'End tour', 'onecom-wp' ), $button2, $function, $parent, $screen );
			} elseif ( $show_pointer ) {
				$this->make_pointer_script( $id, $options, __( 'Back', 'onecom-wp' ), $button2, $function, $parent, $screen );
			}
		}

		public function groupone_generate_array( $arr = array() ) {
			$home_page              = $this->groupone_home_tour();
			$themes_page            = $this->groupone_theme_page_tour();
			$marketplace_page       = $this->groupone_marketplace_tour();
			$staging_page           = $this->groupone_staging_tour();
			$health_monitor_page    = $this->groupone_health_monitor_tour();
			$cookie_banner_page     = $this->groupone_utility_tour();
			$error_page             = $this->groupone_utility_tour();
			$webshop                = $this->groupone_utility_tour();
			$one_photo              = $this->groupone_utility_tour();
			$maintenance_mode       = $this->groupone_utility_tour();
			$php_scanner            = $this->groupone_utility_tour();
			$performance_cache_page = $this->groupone_performance_cache_tour();

			$tours = array(
				'onecom-home-tour'                      => $home_page,
				'onecom-wp-themes-tour'                 => $themes_page,
				'onecom-marketplace-tour'               => $marketplace_page,
				'onecom-wp-staging-tour'                => $staging_page,
				'onecom-wp-health-monitor-tour'         => $health_monitor_page,
				'onecom-wp-spam-protection-tour'        => $health_monitor_page,
				'onecom-wp-cookie-banner-tour'          => $cookie_banner_page,
				'onecom-wp-error-page-tour'             => $error_page,
				'onecom-php-compatibility-scanner-tour' => $php_scanner,
				'onecom-wp-under-construction-tour'     => $maintenance_mode,
				'onecom-vcache-plugin-tour'             => $performance_cache_page,
				'one-webshop-settings-tour'             => $webshop,
				'oc_onephoto-tour'                      => $one_photo,
			);

			if ( isset( $arr ) && ! empty( $arr ) ) {
				return array_merge( $tours, $arr );
			}

			return $tours;
		}

		private function vcache_active() {
			return is_plugin_active( self::VCACHE_PLUGIN );
		}

		private function total_steps() {
			return $this->vcache_active() ? '7' : '6';
		}

		public function groupone_home_tour() {
			$health_mn = array(
				'id'          => '#onecom_home',
				self::CONTENT => array(
					'step'        => '1',
					'total_steps' => $this->total_steps(),
					'title'       => __( 'Home', 'onecom-wp' ),
					'body'        => __( 'Here you find shortcuts to main features of our one.com plugin, such as performance and security tools.', 'onecom-wp' ) . ' ' . __( 'Get inspired by our selected articles or browse the complete help centre.', 'onecom-wp' ) . ' ' . __( "If you need support, you've also come to the right place.", 'onecom-wp' ),
				),
				self::PARENT  => true,
			);
			$button2   = array(
				self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
				self::GROUPONE_FUNCTION => 'window.location="' . menu_page_url( 'onecom-marketplace', false ) . '"',
			);
			$health_mn = array_merge( $health_mn, $button2 );

			return $health_mn;
		}

		public function groupone_theme_page_tour() {
			return array(
				'id'              => '#onecom_themes',
				self::CONTENT     => array(
					'step'        => $this->total_steps(),
					'total_steps' => $this->total_steps(),
					'title'       => 'Themes',
					'body'        => __( 'Choose the perfect theme to suit your brand and give your site a professional appearance.', 'onecom-wp' ),
				),
				self::BUTTON_2    => false, // No next button on last step
				self::GROUPONE_FUNCTION => '',
				self::PARENT      => true,
			);
		}

		public function groupone_marketplace_tour() {
			return array(
				'id'          => '#onecom_marketplace',
				self::CONTENT => array(
					'step'        => '2',
					'total_steps' => $this->total_steps(),
					'title'       => 'Marketplace',
					'body'        => __( 'Boost the performance, security, marketing, and design of your WordPress site with tools that help your site and business grow faster and smarter.', 'onecom-wp' ),
				),
				self::PARENT  => true,
				self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
				self::GROUPONE_FUNCTION => 'window.location="' . menu_page_url( 'onecom-wp-health-monitor', false ) . '"', // No function needed
			);
		}

		public function groupone_staging_tour() {
			$staging = array(
				'id'          => '#onecom_staging',
				self::CONTENT => array(
					'step'        => $this->vcache_active() ? '5' : '4',
					'total_steps' => $this->total_steps(),
					'title'       => 'Staging',
					'body'        => __( 'Create a copy fo your page to try out new plugins and themes without disturbing or breaking your existing website.', 'onecom-wp' ),
				),
				self::PARENT  => true,
			);

			if ( is_plugin_active( self::WEBSHOP_PLUGIN ) ) {
				$button2 = array(
					self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
					self::GROUPONE_FUNCTION => 'window.location="' . menu_page_url( 'one-webshop-settings', false ) . '"',
				);
				$staging = array_merge( $staging, $button2 );
			} else {
				$button2 = array(
					self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
					self::GROUPONE_FUNCTION => 'window.location="' . menu_page_url( 'onecom-wp-error-page', false ) . '"',
				);
				$staging = array_merge( $staging, $button2 );
			}

			return $staging;
		}

		public function groupone_health_monitor_tour() {
			$health_mn = array(
				'id'          => '#onecom_health_security',
				self::CONTENT => array(
					'step'        => '3',
					'total_steps' => $this->total_steps(),
					'title'       => __( 'Health and Security', 'onecom-wp' ),
					'body'        => sprintf( __( '%1$s and %2$s keeps an eye on any issues, spammers, vulnerabilities and lets you fix most of them with just 1-click.', 'onecom-wp' ), '<b>Health Monitor</b>', '<b>Spam Protection</b>' ) . '<br><br>' . __( 'The tour can be restarted anytime from the dashboard.', 'onecom-wp' ),
				),
				self::PARENT  => true,
			);

			if ( is_plugin_active( self::VCACHE_PLUGIN ) ) {
				$next_page = menu_page_url( 'onecom-vcache-plugin', false );
			} else {
				$next_page = menu_page_url( 'onecom-wp-staging', false );
			}
			$button2 = array(
				self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
				self::GROUPONE_FUNCTION => 'window.location="' . $next_page . '"',
			);
			$health_mn = array_merge( $health_mn, $button2 );

			return $health_mn;
		}

		public function groupone_performance_cache_tour() {
			$performance_cache = array(
				'id'          => '#onecom-performance-tools',
				self::CONTENT => array(
					'step'        => '4',
					'total_steps' => $this->total_steps(),
					'title'       => 'Performance Tools',
					'body'        => sprintf( __( '%1$s and %2$s will enhance the speed of your website and improve its overall performance.', 'onecom-wp' ), '<b>Performance Cache</b>', '<b>CDN</b>' ),
				),
				self::PARENT  => true,
			);
			$button2           = array(
				self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
				self::GROUPONE_FUNCTION => 'window.location="' . menu_page_url( 'onecom-wp-staging', false ) . '"',
			);
			$performance_cache = array_merge( $performance_cache, $button2 );

			return $performance_cache;
		}

		public function groupone_utility_tour() {
			$utility = array(
				'id'          => '#onecom-utility',
				self::CONTENT => array(
					'step'        => $this->vcache_active() ? '6' : '5',
					'total_steps' => $this->total_steps(),
					'title'       => 'Utility Tools',
					'body'        => sprintf( __( 'Add features such as an %1$s, %2$s or a %3$s to increase the usability of your site.', 'onecom-wp' ), '<b>Error Page</b>', '<b>Cookie Banner</b>', '<b>Maintenance Mode</b>' ),
				),
				self::PARENT  => true,
			);
			$button2 = array(
				self::BUTTON_2    => __( 'Next', 'onecom-wp' ),
				self::GROUPONE_FUNCTION => 'window.location="' . menu_page_url( 'onecom-wp-themes', false ) . '"',
			);
			$utility = array_merge( $utility, $button2 );

			return $utility;
		}

		public function make_pointer_script( $id, $options, $button1, $button2 = false, $function = '', $parent = false, $screen = '' ) {
			$content_data = $options[self::CONTENT];

			// Determine button class: only themes (last step) gets primary, others get cancel
			$is_themes = ($screen === "onecom-wp-themes-tour");
			$button_class = $is_themes ? 'gv-button-primary' : 'gv-button-cancel';

			?>
			<script type="text/javascript">
				(function ($) {
					const is_themes = '<?php echo $is_themes; ?>';
					$(document).ready(function () {
						let id = '<?php echo $id; ?>',
							tabScreenSize = false;
						const screen = '<?php echo $screen; ?>';

						if (window.screen.width < 960) {
							id = '#toplevel_page_onecom-wp';
							tabScreenSize = true;
						}

						// Build new gv-tour HTML structure
						const tourHTML = `
					<div class="gv-tour gv-arrow-top-left">
						<div class="gv-tour-steps">
							<span>Step</span>
							<span><?php echo esc_js( $content_data['step'] ); ?></span>
							<span>of</span>
							<span><?php echo esc_js( $content_data['total_steps'] ); ?></span>
						</div>
						<div class="gv-tour-body gv-pr-0">
							<div class="gv-tour-title"><?php echo wp_kses_post( $content_data['title'] ); ?></div>
							<p><?php echo wp_kses_post( $content_data['body'] ); ?></p>
						</div>
						<div class="gv-button-group">
							<button type="button" class="gv-button <?php echo $button_class; ?>" id="ocwk-back-button"><?php echo esc_js( $button1 ); ?></button>
							<?php if ( $button2 ) : ?>
							<button type="button" class="gv-button gv-button-primary" id="ocwk-next-button"><?php echo esc_js( $button2 ); ?></button>
							<?php endif; ?>
						</div>
						<button type="button" class="gv-tour-close" aria-label="Close" id="oc-close-tour">
							<img src="<?php echo esc_js( GROUPONE_WP_URL . '/modules/home/assets/icons/cross-white.svg' ); ?>"></img>
						</button>
					</div>
				`;

						let wp_pointers_tour_opts = {
							content: tourHTML,
							pointerWidth: 342,
							position: {
								edge: 'left',
								align: 'left'
							},
							pointerClass: 'ocwt_pointer',
							open: function () {

								if(is_themes){
									lockBodyScroll();
								}
							},
							buttons: function() {
								// Return empty to prevent WordPress from generating buttons
								return $();
							},
							close: function () {
								if(is_themes){
									unlockBodyScroll();
								}

								$.post(ajaxurl, {
									pointer: 'oc_walthrough_pointer',
									action: 'dismiss-wp-pointer'
								});

                                // Set onboarding dismissed transient
                                $.post(ajaxurl, {
                                    action: 'oc_set_onboarding_dismissed'
                                });
                                
								$(document).find('.oc-pointer-wrap').remove();
								let args = {
									'event_action': 'close',
									'item_category': 'blog',
									'item_name': 'onecom_tour',
									'referrer': screen,
								}
								if (typeof oc_push_stats_by_js === 'function') {
									oc_push_stats_by_js(args);
								}
							}
						};

						let setup = function () {
							<?php if ( $parent ) : ?>
							$(id).parent().pointer(wp_pointers_tour_opts).pointer('open');
							<?php else : ?>
							$(id).pointer(wp_pointers_tour_opts).pointer('open');
							<?php endif; ?>

							// Remove default WordPress pointer wrapper elements
							setTimeout(function() {
								let $pointer = $('.ocwt_pointer');

								// Remove wp-pointer-buttons if it exists
								$pointer.find('.wp-pointer-buttons').remove();

								// Unwrap content from wp-pointer-content
								let $content = $pointer.find('.wp-pointer-content');
								let $tour = $content.find('.gv-tour');

								// Replace wp-pointer-content with just the gv-tour
								if ($tour.length > 0) {
									$content.replaceWith($tour);
								}

								// Position pointer after DOM manipulation
								let pointerElement = document.getElementsByClassName('ocwt_pointer')[0];
								if (pointerElement) {
									let adminBar = document.getElementById('wpadminbar'),
										posPointer = pointerElement.getBoundingClientRect(),
										height = ($('#wpadminbar').height() * 2);

									if (id == '#onecom-performance-tools') {
										height = height + 35;
									} else if (id == "#onecom_staging") {
										height = height + 55;
									} else if (id == '#onecom-utility') {
										height = height + 75;
									} else if (id == '#onecom_themes') {
										height = height + 165;
									}

									$(pointerElement).css('top', height + 'px');

									let targetElem = $(document).find(id)[0];
									if (targetElem) {
										$(pointerElement).find('.wp-pointer-arrow').css('top', ($(targetElem).offset().top - $(pointerElement).offset().top + 15 + 'px'));
									}

									$(pointerElement).wrap('<div class="oc-pointer-wrap gv-activated"></div>');

									if (id == '#onecom_health_security') {
										$(pointerElement).css('top', ($('#wpadminbar').height() * 2) + 'px');
									}

									if (tabScreenSize) {
										$(pointerElement).css('left', $('#adminmenuback').width() + 'px');
									}
								}
							}, 10);

							// Handle back/cancel button
							$(document).on('click', '#ocwk-back-button', function (e) {
								e.preventDefault();
								if (screen === "onecom-wp-themes-tour" || screen === "onecom-home-tour") {
									// Close the tour for "End tour" and "Got it!" buttons
									wp_pointers_tour_opts.close();
								} else {
									window.history.back();
								}
							});

							<?php if ( $button2 && $function ) : ?>
							// Handle next button
							$(document).on('click', '#ocwk-next-button', function (e) {
								e.preventDefault();
								<?php echo $function; ?>
							});
							<?php endif; ?>

							// Handle close button (only if it exists)
							$(document).on('click', '#oc-close-tour', function (e) {
								e.preventDefault();
								wp_pointers_tour_opts.close();
							});
						};

						if (wp_pointers_tour_opts.position && wp_pointers_tour_opts.position.defer_loading) {
							$(window).bind('load.wp-pointers', setup);
						} else {
							setup();
						}
					});

					// add these helpers at the top or bottom of the file
					function lockBodyScroll() {
						document.body.style.overflow = 'hidden';
						document.body.style.position = 'fixed';
						document.body.style.width = '100%';
					}

					function unlockBodyScroll() {
						document.body.style.overflow = '';
						document.body.style.position = '';
						document.body.style.width = '';
					}
				})(jQuery);
			</script>
			<?php
		}

		public function groupone_walkthrough_reset_tour() {
			$pointers = get_user_meta( get_current_user_id(), self::DISMISSED_POINTERS, true );
			update_user_meta( get_current_user_id(), 'oc-welcome-modal-closed', true );
			$pointersArr = explode( ',', $pointers );
			$pointer_key = array_search( 'oc_walthrough_pointer', $pointersArr );
			if ( $pointer_key !== false ) {
				unset( $pointersArr[ $pointer_key ] );
			}

			$newpointers = join( ',', $pointersArr );
			if ( $newpointers === $pointers ) {
				die( json_encode( array( 'status' => false ) ) );
			} elseif ( update_user_meta( get_current_user_id(), self::DISMISSED_POINTERS, $newpointers ) ) {
				wp_send_json_success( admin_url( 'admin.php?page=onecom-home' ) );
			}
		}

		public function groupone_restart_tour() {
			$current_screen = get_current_screen();
			if ( $current_screen->id === 'one-com_page_onecom-home' || $current_screen->id === 'dashboard' ) {
				?>
				<script type="text/javascript">
					(function ($) {
						jQuery(document).ready(function () {
							jQuery(".oc-reset-wlk-tour a").on('click', function (e) {
								e.preventDefault();
								$(document).find('#oc_hmwidget_spinner').css('visibility', 'visible');
								jQuery.post(ajaxurl,
									{
										'action': 'ocwkt_reset_tour',
										'nonce': 'asdsadsad'
									},
									function (response) {
										$(document).find('#oc_hmwidget_spinner').css('visibility', 'hidden');
										if ("object" === typeof response && response.success && response.data != undefined) {
											window.location.href = response.data;
										} else {
											window.location.href = "<?php echo admin_url( 'admin.php?page=onecom-home' ); ?>";
										}
									}, 'json', false, 0);
							});
						})
					})(jQuery);
				</script>
				<?php
			}
		}
	}
}