(function ($) {
	$(document).ready(function () {
		$('#oc-restart-tour').click(function () {
			console.info('Restart tour')
		})


		$("#oc-start-tour, #oc_login_masking_overlay_wrap .oc_welcome_modal_close").on('click', function (e) {
			e.preventDefault();
			$("#oc_login_masking_overlay").hide();
			$(".loading-overlay.fullscreen-loader").removeClass('show');
			let redirect = true;
			console.log($(this));
			if($(this).hasClass('oc_welcome_modal_close')){
				redirect = false;
			}
			const nonce = 'asdsadsad';

			$.post(oc_home_ajax_obj.ajax_url, {
				'action': 'oc_close_welcome_modal',
				'nonce': nonce
			})
				.done(function (response) {
					if (response && redirect) {
						window.location.href = oc_home_ajax_obj.home_url;
					}else{
						console.log('modal closed');
					}
				})
				.fail(function () {
					console.error("Failed to close the welcome modal.");
				});
		});

		// Show data consent modal
		$(".oc_consent_modal_show").on('click', function (e) {
			e.preventDefault();
			$("#oc_data_consent_overlay").show();
		});

		// Hide data consent modal
		$("#oc-consent-modal-close, #oc_data_consent_overlay .oc_welcome_modal_close").on('click', function (e) {
			e.preventDefault();
			$("#oc_data_consent_overlay").hide();
			$(".loading-overlay.fullscreen-loader").removeClass('show');
		});

		// Calculate top padding for pages having floating consent banner
		window.addEventListener('load', function() {
			const banner = document.getElementById('oc-data-consent-banner');

			if (!banner) return;

			// Fallback: if PHP hook missed adding body class, add it via JS
			if (!document.body.classList.contains('oc-consent-banner-active')) {
				document.body.classList.add('oc-consent-banner-active');
			}

			updateBannerHeight();

			let resizeTimer;
			window.addEventListener('resize', function() {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(updateBannerHeight, 300);
			});

			function updateBannerHeight() {
				// No top spacing needed on mobile (banner is fixed bottom there)
				if (window.innerWidth <= 599) {
					document.documentElement.style.setProperty('--consent-banner-height', '0px');
					return;
				}

				const height = banner.offsetHeight;

				// Safety check — if height is 0, banner not rendered yet, try again
				if (height === 0) {
					requestAnimationFrame(updateBannerHeight);
					return;
				}

				document.documentElement.style.setProperty('--consent-banner-height', height + 'px');
			}
		});

		// Update data consent status based on actions
		function ocShowConsentToast(containerId, type, message) {
			// Hide any already visible toast on the page
			$('.gv-toast-container .gv-toast.gv-visible').removeClass('gv-visible').addClass('gv-invisible');

			var toastClass = type === 'success' ? 'gv-toast-success' : 'gv-toast-alert';
			var closeIcon = oc_home_ajax_obj.close_icon || '';
			var html = '<div class="gv-toast ' + toastClass + ' gv-visible">' +
				'<div class="gv-toast-content"><div>' + message + '</div></div>' +
				'<button class="gv-toast-close"><gv-icon src="' + closeIcon + '"></gv-icon></button>' +
				'</div>';

			var $container = $(containerId);
			$container.html(html);
			$('.oc-consent-toast-container').show();

			$container.find('.gv-toast-close').on('click', function () {
				$container.find('.gv-toast').removeClass('gv-visible');
			});

			setTimeout(function () {
				$container.find('.gv-toast').removeClass('gv-visible');
			}, 5000);
		}

		var consentUpdateInProgress = false;
		function ocUpdateConsentStatus(status) {
			if (consentUpdateInProgress) return;
			consentUpdateInProgress = true;

			const data = {
				action: 'oc_update_consent_status',
				consent_status: status
			};

			fetch(oc_home_ajax_obj.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams(data),
			})
				.then(response => response.json())
				.then(result => {
					if (result.success) {
						$('body').removeClass('oc-consent-banner-active');
						ocShowConsentToast('#oc-consent-toast-success', 'success', oc_home_ajax_obj.toast_success_msg);

						$("#oc-data-consent-banner").hide();
						if (status === 1) {
							$('#oc-data-consent-toggle').prop('checked', true);
						}

						// Store consent status in localStorage for cross-page communication
						localStorage.setItem('onecom_data_consent_status', result.data.consent_status);

						//Dispatch custom event for other scripts to listen to (same page)
						//Dispatching to marketplace service to track consent status change
						const event = new CustomEvent('onConsentStatusChanged', { detail: { data_consent_status: result.data.consent_status } });
						window.dispatchEvent(event);
					} else {
						$('body').removeClass('oc-consent-banner-active');
						ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
					}
				})
				.catch(error => {
					ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
				})
				.finally(function () {
					consentUpdateInProgress = false;
				});
		}
		$("#oc-consent-settings input[type='checkbox']").on('click', function () {
			const status = $(this).is(':checked') ? 1 : 0;
			ocUpdateConsentStatus(status);
		});

		// Imagify promoter toggle
		var imagifyPromoterUpdateInProgress = false;
		function ocUpdateImagifyPromoterStatus(status) {
			if (imagifyPromoterUpdateInProgress) return;
			imagifyPromoterUpdateInProgress = true;

			fetch(oc_home_ajax_obj.ajax_url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'oc_update_imagify_promoter_status',
					imagify_promoter_status: status,
					nonce: oc_home_ajax_obj.nonce,
				}),
			})
				.then(response => response.json())
				.then(result => {
					if (result.success) {
						ocShowConsentToast('#oc-consent-toast-success', 'success', oc_home_ajax_obj.toast_success_msg);
					} else {
						ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
						$('#oc-imagify-promoter-toggle').prop('checked', status !== 1);
					}
				})
				.catch(() => {
					ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
					$('#oc-imagify-promoter-toggle').prop('checked', status !== 1);
				})
				.finally(() => {
					imagifyPromoterUpdateInProgress = false;
				});
		}
		$("#oc-imagify-settings input[type='checkbox']").on('click', function () {
			const status = $(this).is(':checked') ? 1 : 0;
			ocUpdateImagifyPromoterStatus(status);
		});

		$('.oc-data-consent-decline').on('click', function () {
			ocUpdateConsentStatus(0);
		});
		$('.oc-data-consent-accept').on('click', function () {
			ocUpdateConsentStatus(1);
		});
		$('.oc-data-consent-dismiss').on('click', function () {
			ocUpdateConsentStatus('dismissed');
		});

		// Show premium WP care form
		$(".oc_premium_care_modal_show").on('click', function (e) {
			e.preventDefault();
			$("#oc_premium_care_overlay").show();
		});

		// Open premium WP care modal with direct URL, and remove hash to load modal once
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('page') === "onecom-home" && urlParams.get('request-premium-wp-care') === "1") {
			$("#oc_premium_care_overlay").show();
			history.replaceState(null, null, window.location.pathname + "?page=onecom-home");
		}

		// Hide premium WP care modal
		$("#oc-premium-care-modal-close, #oc_premium_care_overlay .oc_welcome_modal_close").on('click', function (e) {
			e.preventDefault();
			$("#oc_premium_care_overlay").hide();
			$(".loading-overlay.fullscreen-loader").removeClass('show');
			$("#premium-care-request-error").hide();
		});

		$(".oc-premium-care-box .gv-notice-close").on('click', function (e) {
			$.post(oc_home_ajax_obj.ajax_url, {
					_ajax_nonce: oc_home_ajax_obj.nonce,
					action: "oc_home_premium_care_dismiss"
				}, function (data) {
					if (data.status === 'success') {
						$(".oc-premium-care-box").hide();
					} else {
						console.error( 'Error: Failed to dismiss premium wp care tile.' );
					}
				}
			);
		});

		$("#oc-premium-care-request-notice .gv-notice-close").on('click', function (e) {
			$("#oc-premium-care-request-notice").hide();
		});

		$('#oc-premium-care-request-action').on('click', function () {
			ocRequestPremiumCareRequest();
		});

		// Request premium wp care
		function ocRequestPremiumCareRequest() {
			let text = $("#oc-premium-care-description").val();
			const data = {
				action: 'oc_request_premium_care',
				premium_wp_request: 1,
				text: text
			};
			$('#modal-loader-overlay').css('display', 'flex');

			fetch(oc_home_ajax_obj.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams(data),
			})
				.then(response => response.json())
				.then(result => {
					$('#modal-loader-overlay').hide();
					if (result.success) {
						$(".gv-notice-premium-care").hide();
						$(".gv-notice-premium-care-requested").removeClass('gv-hidden');
						$("#oc-premium-care-request-notice").removeClass('gv-hidden');
						$("#oc_premium_care_overlay").hide();
						$(".loading-overlay.fullscreen-loader").removeClass('show');
						$('html, body').animate({
							scrollTop: $('.inner-wrapper').offset().top
						}, 500);
					} else {
						$('#modal-loader-overlay').hide();
						$("#premium-care-request-error").css('display', 'flex');
					}
				})
				.catch(error => {
					$('#modal-loader-overlay').hide();
					$("#premium-care-request-error").css('display', 'flex');
				});
		}

		// Performance score column toggle (WPIN-8528)
		$('#oc-perf-column-toggle').on('change', function () {
			var $toggle = $(this);
			var $wrap   = $('#oc-perf-column-settings');
			var enabled = $toggle.is(':checked') ? 1 : 0;
			$.post(oc_home_ajax_obj.ajax_url, {
				action: $wrap.data('ajax-action'),
				nonce:  $wrap.data('nonce'),
				enabled: enabled
			})
				.done(function (result) {
					if (result && result.success) {
						ocShowConsentToast('#oc-consent-toast-success', 'success', oc_home_ajax_obj.toast_success_msg);
					} else {
						ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
						$toggle.prop('checked', enabled !== 1);
					}
				})
				.fail(function () {
					ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
					$toggle.prop('checked', enabled !== 1);
				});
		});

		/**
		 * WPIN-8870: bring the one.com admin sidebar in line with a module that
		 * was just toggled, so the menu stops advertising a disabled module (or
		 * hiding a freshly enabled one) until the next page load.
		 *
		 * `menu` is the payload from groupone_module_menu_entries() in
		 * inc/module-registry.php. The accordion structure this walks is built
		 * on DOMContentLoaded by onecom_js_assets() in the premium-validator
		 * submodule: each heading is an `li.accordion` immediately followed by
		 * a `div.panel` holding that heading's child `li`s.
		 *
		 * Every step is a no-op if its target is missing, so a stale slug or a
		 * menu that was never rendered (multisite returns early, and a legacy
		 * standalone plugin can own the entry instead) degrades to "sidebar
		 * stays as it was" rather than to a broken menu.
		 */
		function ocSyncOnecomAdminMenu(menu, enabled) {
			if (!menu || !menu.items) {
				return;
			}

			var $onecomMenu = $('#adminmenu li#toplevel_page_onecom-wp');

			if (!$onecomMenu.length) {
				return;
			}

			// Slugs come from our own PHP map, but they are interpolated into a
			// selector - keep anything unexpected out of it.
			function safeSlug(slug) {
				return typeof slug === 'string' && /^[a-z0-9_-]+$/i.test(slug) ? slug : '';
			}

			function findItem(slug) {
				return slug ? $onecomMenu.find("a[href$='admin.php?page=" + slug + "']").closest('li') : $();
			}

			// A panel that is currently expanded has an explicit max-height in
			// px; adding or removing an li inside it would clip or over-pad it.
			function resizePanel($panel) {
				if ($panel.length && $panel.prev('.accordion').hasClass('active')) {
					$panel.css('max-height', $panel.prop('scrollHeight') + 'px');
				}
			}

			var accordion = menu.accordion || null;

			if (enabled) {
				// The heading may need to exist before its children can be hung
				// off it (Performance only exists while its module is on).
				if (accordion && safeSlug(accordion.slug) && !$onecomMenu.find('li.' + accordion.class).length) {
					var $accordion = $('<li>')
						.addClass('accordion ' + accordion.class)
						.append($('<a>').text(accordion.title));
					var $panel = $('<div>').addClass('panel ' + accordion.panel);

					// Mirrors the accordion click handler onecom_js_assets()
					// attaches at load - one added now has none of its own.
					// Deliberately the same inline-style check the original
					// uses rather than a computed-style one: reading the
					// computed value gets stuck expanding when scrollHeight
					// measures 0 (a collapsed or off-screen sidebar).
					$accordion.on('click', function () {
						this.classList.toggle('active');

						var panel = this.nextElementSibling;

						if ( ! panel ) {
							return;
						}

						panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + 'px';
					});

					// Keep the original ordering: Performance sits above
					// Utility. Falls back to the end of the submenu list.
					var $utility = $onecomMenu.find('li.oc-utility-accordion');

					if ($utility.length) {
						$accordion.insertBefore($utility);
					} else {
						$onecomMenu.find('ul.wp-submenu').append($accordion);
					}

					$panel.insertAfter($accordion);
				}

				$.each(menu.items, function (index, item) {
					var slug = safeSlug(item.slug);

					if (!slug || findItem(slug).length) {
						return; // Unknown slug, or the entry is already there.
					}

					var $panel = $onecomMenu.find('div.' + item.panel);

					if (!$panel.length) {
						return;
					}

					$('<li>')
						.append($('<a>').attr('href', 'admin.php?page=' + slug).text(item.title))
						.appendTo($panel);

					resizePanel($panel);
				});
			} else {
				$.each(menu.items, function (index, item) {
					var $panel = $onecomMenu.find('div.' + item.panel);

					findItem(safeSlug(item.slug)).remove();
					resizePanel($panel);
				});

				if (accordion && accordion.class) {
					$onecomMenu.find('div.' + accordion.panel).remove();
					$onecomMenu.find('li.' + accordion.class).remove();
				}
			}
		}

		/**
		 * Brings a module-backed home tile in line with a module that was just
		 * toggled, so its arrow navigates straight away.
		 *
		 * The tile's href is decided server-side when the page renders, so a tile
		 * whose module was off stays a scroll-to-toggle affordance
		 * (href="javascript:void(0);" + oc-scroll-to-module) until the page is
		 * reloaded — clicking it after enabling the module went nowhere. The
		 * target comes from data-module-url, which the template emits for every
		 * module-backed tile regardless of state.
		 */
		function ocSyncModuleTile(moduleSlug, enabled) {
			if (!moduleSlug) {
				return;
			}

			var $tile = $('a[data-module="' + moduleSlug + '"][data-module-url]');
			var url   = $tile.attr('data-module-url');

			if (!$tile.length) {
				return;
			}

			if (enabled && url) {
				$tile.attr('href', url).removeClass('oc-scroll-to-module');
			} else {
				$tile.attr('href', 'javascript:void(0);').addClass('oc-scroll-to-module');
			}
		}

		/**
		 * WPIN-9029: Dynamic admin toolbar menu sync.
		 * Keeps the one.com dropdown in the top admin toolbar in sync with module toggles
		 * without requiring a full page reload.
		 */
		function ocSyncOnecomToolbarMenu(moduleSlug, enabled) {
			if (!moduleSlug) {
				return;
			}
			var $toolbarDefault = $('#wp-admin-bar-onecom-wp-default');
			if (!$toolbarDefault.length) {
				var $toolbarMenu = $('#wp-admin-bar-onecom-wp');
				if (!$toolbarMenu.length) {
					return; // Admin toolbar or one.com toolbar menu not present in DOM.
				}
				$toolbarDefault = $toolbarMenu.find('.ab-submenu').first();
				if (!$toolbarDefault.length) {
					return;
				}
			}

			function insertToolbarNode($node) {
				var $controlPanel = $toolbarDefault.find('#wp-admin-bar-one-cp');
				if ($controlPanel.length) {
					$node.insertBefore($controlPanel);
				} else {
					$toolbarDefault.append($node);
				}
			}

			if (moduleSlug === 'performance-cache') {
				var $pcNode = $('#wp-admin-bar-purge-all-varnish-cache');
				if (enabled) {
					if (!$pcNode.length) {
						var pcHtml = '<li id="wp-admin-bar-purge-all-varnish-cache" class="menupop">' +
							'<a class="ab-item" aria-haspopup="true" href="admin.php?page=onecom-vcache-plugin">' +
							'Clear Performance Cache' +
							'</a>' +
							'<div class="ab-sub-wrapper">' +
							'<ul id="wp-admin-bar-purge-all-varnish-cache-default" class="ab-submenu">' +
							'<li id="wp-admin-bar-purge-onecom-cache-only">' +
							'<a class="ab-item" href="admin.php?page=onecom-vcache-plugin">Clear Cache</a>' +
							'</li>' +
							'<li id="wp-admin-bar-purge-onecom-cdn-only">' +
							'<a class="ab-item" href="admin.php?page=onecom-cdn">Clear CDN</a>' +
							'</li>' +
							'</ul>' +
							'</div>' +
							'</li>';
						insertToolbarNode($(pcHtml));
					}
				} else {
					$pcNode.remove();
				}
			} else if (moduleSlug === 'maintenance-mode') {
				var $mmNode = $('#wp-admin-bar-ocuc_options');
				if (enabled) {
					if (!$mmNode.length) {
						var mmHtml = '<li id="wp-admin-bar-ocuc_options" class="ocuc_toggle_settings ocuc_setting_is_off ocwp_ocmm_toolbar_status_link_clicked_event">' +
							'<a class="ab-item" href="admin.php?page=onecom-wp-under-construction" title="Maintenance Mode is OFF">Maintenance Mode is OFF</a>' +
							'</li>';
						insertToolbarNode($(mmHtml));
					}
				} else {
					$mmNode.remove();
				}
			} else if (moduleSlug === 'spam-protection') {
				var $spNode = $('#wp-admin-bar-onecom-wp-spam-protection');
				if (enabled) {
					if (!$spNode.length) {
						var spHtml = '<li id="wp-admin-bar-onecom-wp-spam-protection">' +
							'<a class="ab-item" href="admin.php?page=onecom-wp-spam-protection" title="Spam Protection">Spam Protection</a>' +
							'</li>';
						insertToolbarNode($(spHtml));
					}
				} else {
					$spNode.remove();
				}
			}
		}

		// Module toggle list, moved here from the former Settings page (WPIN-8736)
		$('#oc-module-toggles').on('change', '.oc-module-toggle-input', function () {
			var $toggle  = $(this);
			var $wrap    = $('#oc-module-toggles');
			var $spinner = $toggle.closest('.oc-toggle-wrap').find('.oc-module-toggle-spinner');
			var enabled  = $toggle.is(':checked') ? 1 : 0;
			var moduleSlug = $toggle.data('module') || $toggle.attr('id').replace('oc-module-toggle-', '');

			// WPIN-8899: any successful/other-reason toggle attempt hides a
			// banner left over from a previous mwp_required rejection.
			$('#oc-sp-mwp-upsell-banner').addClass('gv-hidden');

			// wp_send_json_error()'s 403/502 status codes route here via jQuery
			// (non-2xx), not .done() — inspect the reason to show the reused
			// upsell banner (mwp_required) or a distinct retry message
			// (mwp_check_failed) instead of the generic failure toast.
			function handleRejection(reason) {
				if (reason === 'mwp_required') {
					// WPIN-9043: fetch the Spam-Protection-specific popup content
					// FIRST and only reveal #oc_um_overlay once that content has
					// landed — matching the CDN "Upgrade Now" button and
					// oc-sp-admin.js's own .oc-show-modal handler, which both
					// populate-then-show as a single atomic step. The previous
					// fix here (WPIN-9030 / c046f486) called .show() immediately,
					// synchronously, before the AJAX round-trip completed — that
					// revealed the overlay with whatever generic content
					// admin_footer had already printed, then visibly swapped it
					// out from under the user a moment later once the real
					// content arrived, reading as a shaky/broken re-render.
					//
					// The #oc-sp-mwp-upsell-banner reveal that used to fire in
					// lockstep with the overlay has been dropped: the overlay
					// itself (now shown atomically, fully populated) already
					// carries the same upgrade message as a full-page surface,
					// so unhiding the banner at the same instant was pure
					// redundancy — and the two animating in together is exactly
					// what read as "the modal is broken/rendered inside the
					// toggle section".
					var spPopupContent = (typeof oc_home_ajax_obj !== 'undefined' && oc_home_ajax_obj.sp_popup_content) ? oc_home_ajax_obj.sp_popup_content : {
						title: 'Get Spam Protection plugin with \n Managed WordPress',
						'top-desc': 'Spend less time worrying about your site’s safety and more time growing your business with one.com Managed WordPress.',
						'footer-desc': 'Managed WordPress includes other great features such as Vulnerability Monitor and Upgrade Manager',
						bodylist: [
							'Protects the website from spam comments, spam registration, spambots, and other spammers.',
							'Customizable protection settings, spam logs, and other options.'
						]
					};
					var ajaxUrl = (typeof oc_home_ajax_obj !== 'undefined' && oc_home_ajax_obj.ajax_url) ? oc_home_ajax_obj.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
					$.ajax({
						url: ajaxUrl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'show_plugin_dependent_popup',
							popupContent: spPopupContent
						},
						success: function (response) {
							if (response && response.success && response.data) {
								$('#oc_um_overlay').html(response.data);
							}
							$('#oc_um_overlay').show();
							if (typeof ocSetModalData === 'function') {
								ocSetModalData({
									isPremium: true,
									feature: 'spam_protection',
									featureAction: 'getStarted',
									referrer: location.search
								});
							}
						},
						error: function () {
							// Still show the overlay even if the content fetch
							// fails — it falls back to whatever generic upgrade
							// copy admin_footer already printed on page load
							// rather than leaving the rejection silent.
							$('#oc_um_overlay').show();
						}
					});
				} else if (reason === 'mwp_check_failed') {
					ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_mwp_check_failed_msg);
				} else {
					ocShowConsentToast('#oc-consent-toast-failure', 'alert', oc_home_ajax_obj.toast_failure_msg);
				}
				$toggle.prop('checked', enabled !== 1);
			}

			// WPIN-8924 / WPIN-9031: this request is not fast. Enabling an mWP-gated module
			// runs a forced live ismWP() check (WPIN-8899) that deliberately
			// bypasses the 12h cache, so it can take several seconds. During
			// that window the checkbox already reads ON — and if the gate then
			// says no, it snaps back, which reads as "the toggle reverted after
			// it said it saved" rather than as a considered rejection.
			//
			// Show WordPress-style AJAX loading spinner and disable the input for
			// the duration to make it a legible transition and prevent duplicate clicks.
			$spinner.addClass('is-active');
			$toggle.prop('disabled', true);

			$.post(oc_home_ajax_obj.ajax_url, {
				action:  'oc_toggle_module',
				nonce:   $wrap.data('nonce'),
				module:  moduleSlug,
				enabled: enabled
			})
				.always(function () {
					$spinner.removeClass('is-active');
					$toggle.prop('disabled', false);
				})
				.done(function (result) {
					if (result && result.success) {
						ocShowConsentToast('#oc-consent-toast-success', 'success', oc_home_ajax_obj.toast_success_msg);
						// WPIN-8870: the option is saved, so the sidebar is now
						// out of date - bring it along without a reload.
						var mod = (result.data && result.data.module) || moduleSlug;
						ocSyncOnecomAdminMenu(result.data && result.data.menu, enabled === 1);
						// WPIN-9029: sync top admin toolbar menu dynamically.
						ocSyncOnecomToolbarMenu(mod, enabled === 1);
						// The home tile's link is rendered server-side, so it has
						// to be brought along too or it keeps pointing nowhere
						// until a reload.
						ocSyncModuleTile(mod, enabled === 1);
					} else {
						// Defensive only: every rejection from this endpoint uses a
						// non-2xx status (403/502), which jQuery routes to .fail()
						// below, not here — kept in case that ever changes.
						handleRejection(result && result.data);
					}
				})
				.fail(function (xhr) {
					var data;
					if (xhr && xhr.responseJSON && typeof xhr.responseJSON.data !== 'undefined') {
						data = xhr.responseJSON.data;
					} else if (xhr && xhr.responseText) {
						try {
							var parsed = JSON.parse(xhr.responseText);
							data = parsed.data;
						} catch (e) {
							data = null;
						}
					}
					handleRejection(data);
				});
		});

		// WPIN-8936: a home-page tile whose module is off links here instead
		// of to a settings page that doesn't exist yet — scroll to and
		// highlight that module's toggle row so it's clear why the link
		// didn't go anywhere.
		// Delegated on purpose: ocSyncModuleTile() adds and removes
		// oc-scroll-to-module as a module is toggled, and a direct binding would
		// stay attached to a tile that is no longer a scroll target — swallowing
		// the click with preventDefault() instead of letting it navigate.
		$(document).on('click', '.oc-scroll-to-module', function (e) {
			e.preventDefault();
			var slug = $(this).data('module');
			var $row = $('#oc-module-toggle-' + slug).closest('.gv-grid-cols-12');
			if (!$row.length) {
				return;
			}
			$row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
			$row.addClass('oc-module-row-highlight');
			setTimeout(function () {
				$row.removeClass('oc-module-row-highlight');
			}, 2200);
		});

	});
})(jQuery)