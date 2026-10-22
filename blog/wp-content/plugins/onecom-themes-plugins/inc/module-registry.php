<?php

defined( 'WPINC' ) or die(); // No Direct Access

/**
 * Registry and loader for the feature modules merged from the standalone
 * one.com plugins (WPIN-8736): Performance Cache, Spam Protection and
 * Maintenance Mode.
 */

if ( ! function_exists( 'groupone_merged_modules_map' ) ) {
	/**
	 * Map of merged module slug => bootstrap file and the legacy standalone
	 * plugin that keeps precedence over the module while it is still active
	 * (WPIN-8737 guides the customer through cleaning the standalone up).
	 *
	 * 'protected_options': wp-options the legacy plugin's own uninstall
	 * routine deletes on `delete_plugins()` (verified by reading each legacy
	 * plugin's source directly) that the merged module keeps reusing under
	 * the same name — WPIN-8737's cleanup flow snapshots these before
	 * deleting the legacy plugin and restores them after, so a customer's
	 * settings survive the cleanup even though the legacy uninstall.php ran.
	 * onecom-vcache's uninstall only pushes a stats event (no options
	 * touched); onecom-spam-protection has no uninstall handler at all —
	 * both are correctly empty here.
	 *
	 * 'cron_hooks': hook names the legacy plugin schedules via
	 * wp_schedule_event() that must be cleared on cleanup. Empty for all
	 * three today (confirmed by grepping each legacy plugin's source for
	 * wp_schedule_event() — none of them schedule any cron), kept as a
	 * per-module list rather than skipped entirely so a future legacy
	 * plugin with real cron jobs only needs a registry entry, not new
	 * cleanup-handler code.
	 */
	function groupone_merged_modules_map(): array {
		return array(
			'performance-cache' => array(
				'bootstrap'         => GROUPONE_WP_PATH . 'modules/performance-cache/performance-cache.php',
				'legacy'            => 'onecom-vcache/vcaching.php',
				'protected_options' => array(),
				'cron_hooks'        => array(),
				'mwp_only'          => false,
				'admin_menu'        => array(
					// The "Performance" heading is registered only when this
					// module (or the legacy plugin) is active, so toggling the
					// module has to create/remove the accordion itself, not
					// just its children.
					'accordion' => array(
						'slug'  => 'onecom-performance-menu',
						'title' => 'Performance',
						'class' => 'oc-performance-accordion',
						'panel' => 'oc-performance-panel',
					),
					'items'     => array(
						array(
							'slug'  => 'onecom-vcache-plugin',
							'title' => 'Performance Cache',
							'panel' => 'oc-performance-panel',
						),
						array(
							'slug'  => 'onecom-cdn',
							'title' => 'CDN',
							'panel' => 'oc-performance-panel',
						),
					),
				),
			),
			'spam-protection'   => array(
				'bootstrap'         => GROUPONE_WP_PATH . 'modules/spam-protection/spam-protection.php',
				'legacy'            => 'onecom-spam-protection/onecom-spam-protection.php',
				'protected_options' => array(),
				'cron_hooks'        => array(),
				// WPIN-8859: this module is an MWP-plan perk — gated on ismWP(),
				// not on any per-module purchase flag of its own.
				'mwp_only'          => true,
				'admin_menu'        => array(
					// "Health and Security" is also gated on
					// onecom_plugin_activated(), which is true whenever the hub
					// is running, so the heading never disappears with this
					// toggle — only its child does.
					'accordion' => null,
					'items'     => array(
						array(
							'slug'  => 'onecom-wp-spam-protection',
							'title' => 'Spam Protection',
							'panel' => 'oc-health-security-panel',
						),
					),
				),
			),
			'maintenance-mode'  => array(
				'bootstrap'         => GROUPONE_WP_PATH . 'modules/maintenance-mode/maintenance-mode.php',
				'legacy'            => 'onecom-under-construction/onecom-under-construction.php',
				'protected_options' => array( 'onecom_under_construction_info' ),
				'cron_hooks'        => array(),
				'mwp_only'          => false,
				'admin_menu'        => array(
					// "Utility" is gated on a wide OR that includes
					// onecom_plugin_activated(), so it too always survives.
					'accordion' => null,
					'items'     => array(
						array(
							'slug'  => 'onecom-wp-under-construction',
							'title' => 'Maintenance Mode',
							'panel' => 'oc-utility-panel',
						),
					),
				),
			),
		);
	}
}

if ( ! function_exists( 'groupone_legacy_plugin_active' ) ) {
	/**
	 * is_plugin_active() equivalent that is safe at plugin-load time on the
	 * front end, where wp-admin/includes/plugin.php is not loaded yet.
	 */
	function groupone_legacy_plugin_active( string $plugin_file ): bool {
		if ( in_array( $plugin_file, (array) get_option( 'active_plugins', array() ), true ) ) {
			return true;
		}
		if ( is_multisite() ) {
			$sitewide = (array) get_site_option( 'active_sitewide_plugins', array() );
			if ( isset( $sitewide[ $plugin_file ] ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'groupone_module_active' ) ) {
	/**
	 * Whether a merged feature module was loaded for this request. Consumed by
	 * the inc/lib menu classes (oc_feature_active) and the plugins API module.
	 */
	function groupone_module_active( string $module_slug ): bool {
		global $onecom_active_modules;

		return ! empty( $onecom_active_modules[ $module_slug ] );
	}
}

if ( ! function_exists( 'groupone_get_disabled_modules' ) ) {
	/**
	 * Slugs the site owner explicitly turned off via the Features toggle
	 * screen (WPIN-8752), independent of the standalone-plugin precedence
	 * rules above.
	 */
	function groupone_get_disabled_modules(): array {
		return (array) get_option( 'onecom_disabled_modules', array() );
	}
}

if ( ! function_exists( 'groupone_set_module_enabled' ) ) {
	function groupone_set_module_enabled( string $module_slug, bool $enabled ): void {
		$disabled = groupone_get_disabled_modules();

		if ( $enabled ) {
			$disabled = array_diff( $disabled, array( $module_slug ) );
		} elseif ( ! in_array( $module_slug, $disabled, true ) ) {
			$disabled[] = $module_slug;
		}

		update_option( 'onecom_disabled_modules', array_values( $disabled ) );
	}
}

if ( ! function_exists( 'groupone_module_user_choice_recorded' ) ) {
	/**
	 * Whether the site owner has explicitly toggled this module themselves.
	 *
	 * Reconciliation skips these slugs: a module switched off from the Home
	 * page must not be re-enabled behind the owner's back when the legacy
	 * plugin is later deactivated.
	 *
	 * @param string $module_slug
	 *
	 * @return bool
	 */
	function groupone_module_user_choice_recorded( string $module_slug ): bool {
		$choices = get_option( 'onecom_modules_user_choice', array() );

		return is_array( $choices ) && in_array( $module_slug, $choices, true );
	}
}

if ( ! function_exists( 'groupone_record_module_user_choice' ) ) {
	/**
	 * Records that the owner toggled this module themselves.
	 * Only groupone_ajax_toggle_module() may call this - every other writer of
	 * onecom_disabled_modules is a default, not a choice.
	 *
	 * @param string $module_slug
	 *
	 * @return void
	 */
	function groupone_record_module_user_choice( string $module_slug ): void {
		$choices = get_option( 'onecom_modules_user_choice', array() );

		if ( ! is_array( $choices ) ) {
			$choices = array();
		}
		if ( in_array( $module_slug, $choices, true ) ) {
			return;
		}
		$choices[] = $module_slug;

		update_option( 'onecom_modules_user_choice', array_values( $choices ), false );
	}
}

if ( ! function_exists( 'groupone_module_mwp_blocked' ) ) {
	/**
	 * Whether an mwp_only module must stay off because the site is not mWP.
	 *
	 * Uses the cached ismWP(): this runs on arbitrary requests including
	 * WP-CLI, so the forced live variant is reserved for the AJAX toggle.
	 *
	 * @param array $module A single entry from groupone_merged_modules_map().
	 *
	 * @return bool
	 */
	function groupone_module_mwp_blocked( array $module ): bool {
		if ( empty( $module['mwp_only'] ) ) {
			return false;
		}
		if ( ! function_exists( 'ismWP' ) && file_exists( GROUPONE_WP_PATH . 'inc/functions.php' ) ) {
			require_once GROUPONE_WP_PATH . 'inc/functions.php';
		}

		return function_exists( 'ismWP' ) && ! ismWP();
	}
}

if ( ! function_exists( 'groupone_load_merged_modules' ) ) {
	/**
	 * Load every merged module whose legacy standalone plugin is not active
	 * and that the site owner has not explicitly disabled.
	 * Must run at plugin parse time: the module bootstraps register init hooks
	 * at negative priority and front-end hooks from their constructors, which
	 * an init-time load would miss.
	 */
	function groupone_load_merged_modules(): void {
		global $onecom_active_modules;
		$onecom_active_modules = array();

		$disabled_modules = groupone_get_disabled_modules();

		foreach ( groupone_merged_modules_map() as $slug => $module ) {
			if ( ! file_exists( $module['bootstrap'] ) ) {
				continue;
			}
			if ( groupone_legacy_plugin_active( $module['legacy'] ) ) {
				continue;
			}
			if ( in_array( $slug, $disabled_modules, true ) ) {
				continue;
			}
			if ( groupone_module_mwp_blocked( $module ) ) {
				continue;
			}
			require_once $module['bootstrap'];
			$onecom_active_modules[ $slug ] = true;
		}
	}
}

if ( ! function_exists( 'groupone_module_labels' ) ) {
	/**
	 * Human-readable label per merged module slug, kept separate from
	 * groupone_merged_modules_map() so the registry itself stays UI-agnostic.
	 * Consumed by the Home page toggle list (WPIN-8736) and by
	 * inc/legacy-plugin-takeover.php's admin notices.
	 */
	function groupone_module_labels(): array {
		return array(
			'performance-cache' => __( 'Performance Cache', 'onecom-wp' ),
			'spam-protection'   => __( 'Spam Protection', 'onecom-wp' ),
			'maintenance-mode'  => __( 'Maintenance Mode', 'onecom-wp' ),
		);
	}
}

if ( ! function_exists( 'groupone_module_descriptions' ) ) {
	/**
	 * One-line description per merged module slug, shown under the label on
	 * the Home page toggle list (WPIN-8859). Kept separate from the 'note'
	 * field in modules/home/oc-home-sections.php::get_module_toggles(),
	 * which is reserved for state/warning messages (legacy plugin active,
	 * MWP required, etc.) rather than a general feature description.
	 */
	function groupone_module_descriptions(): array {
		return array(
			'performance-cache' => __( 'Speeds up your site with server-side caching.', 'onecom-wp' ),
			'spam-protection'   => __( 'Blocks spam comments and form submissions automatically.', 'onecom-wp' ),
			'maintenance-mode'  => __( 'Shows visitors a maintenance page while you make changes.', 'onecom-wp' ),
		);
	}
}

if ( ! function_exists( 'groupone_module_icons' ) ) {
	/**
	 * Relative path (from GROUPONE_WP_URL) to each merged module's icon,
	 * reusing the icon each module already ships for its own settings page
	 * rather than adding new assets, for the Home page toggle list (WPIN-8859).
	 */
	function groupone_module_icons(): array {
		return array(
			'performance-cache' => 'modules/performance-cache/assets/images/pcache-icon.svg',
			'spam-protection'   => 'modules/spam-protection/assets/images/spam_protection.svg',
			'maintenance-mode'  => 'modules/maintenance-mode/assets/images/mm-icon.svg',
		);
	}
}

if ( ! function_exists( 'groupone_module_menu_entries' ) ) {
	/**
	 * The one.com admin-menu entries a module owns, so the Home page can add or
	 * remove them from the live sidebar the moment the module is toggled,
	 * instead of leaving a stale menu until the next page load (WPIN-8870).
	 *
	 * Returns a shape of:
	 *   array(
	 *     'accordion' => array|null,   // heading that exists only with this module
	 *     'items'     => array,        // child entries, each with slug/title/panel
	 *   )
	 *
	 * These slugs mirror the add_submenu_page() calls in the premium-validator
	 * submodule's inc/lib/onecom-nested-menu.php::onecom_register_menu(), and
	 * the panel/accordion class names mirror the accordion its
	 * onecom_js_assets() builds on DOMContentLoaded. That is a real coupling
	 * across two repos: if a menu slug or a panel class changes there, this map
	 * has to change with it, or the live update will target nothing and the
	 * sidebar will simply stay stale until reload (it cannot corrupt the menu —
	 * a missed selector is a no-op). It is duplicated deliberately rather than
	 * re-deriving it by re-running onecom_register_menu() inside the AJAX
	 * request: that function include_once's the webshop plugin's main file when
	 * that plugin is active, and running a third-party plugin's bootstrap
	 * inside an unrelated AJAX call is a far worse failure mode than a stale
	 * menu.
	 *
	 * @param string $module_slug Merged module slug.
	 * @return array Empty array when the module is unknown or owns no entries.
	 */
	function groupone_module_menu_entries( string $module_slug ): array {
		$map = groupone_merged_modules_map();

		if ( ! isset( $map[ $module_slug ]['admin_menu'] ) ) {
			return array();
		}

		return $map[ $module_slug ]['admin_menu'];
	}
}

if ( ! function_exists( 'groupone_module_page_slugs' ) ) {
	/**
	 * Map of merged module slug => the admin settings-page slug(s) that
	 * module's own bootstrap registers when active (WPIN-8925).
	 *
	 * Consumed by groupone_register_disabled_module_pages() (hidden fallback
	 * page when a module is disabled) and by modules/home/js/main.js (which
	 * sidebar <li> elements to hide immediately after a toggle).
	 *
	 * Sourced from each module's own add_menu_page()/add_submenu_page() call:
	 * class-onecom-sp-settings.php ('onecom-wp-spam-protection'),
	 * class-ocuc-admin-settings.php ('onecom-wp-under-construction'),
	 * class-ocvcaching.php ('onecom-vcache-plugin'), and
	 * inc/lib/onecom-nested-menu.php's own 'onecom-cdn' registration (only
	 * reachable when performance-cache is active — it needs the same
	 * fallback).
	 */
	function groupone_module_page_slugs(): array {
		return array(
			'performance-cache' => array( 'onecom-vcache-plugin', 'onecom-cdn' ),
			'spam-protection'   => array( 'onecom-wp-spam-protection' ),
			'maintenance-mode'  => array( 'onecom-wp-under-construction' ),
		);
	}
}

if ( ! function_exists( 'groupone_register_disabled_module_pages' ) ) {
	/**
	 * Registers a hidden (parent `null`) fallback page hook for every
	 * settings-page slug of a merged module that is not currently loaded,
	 * so a stale bookmark/sidebar link/browser-history entry resolves to a
	 * friendly "this feature is off" notice instead of WordPress core's
	 * generic "Sorry, you are not allowed to access this page." (WPIN-8925).
	 */
	function groupone_register_disabled_module_pages(): void {
		$map = groupone_merged_modules_map();

		foreach ( groupone_module_page_slugs() as $module_slug => $page_slugs ) {
			if ( ! isset( $map[ $module_slug ] ) ) {
				continue;
			}
			// Skip if the module itself, or its legacy standalone plugin,
			// is already serving a real page under this slug this request.
			if ( groupone_module_active( $module_slug )
				|| groupone_legacy_plugin_active( $map[ $module_slug ]['legacy'] ) ) {
				continue;
			}
			foreach ( $page_slugs as $page_slug ) {
				add_submenu_page(
					null, // hidden: registers the page hook without adding a menu item
					groupone_module_labels()[ $module_slug ] ?? $module_slug,
					groupone_module_labels()[ $module_slug ] ?? $module_slug,
					'manage_options',
					$page_slug,
					'groupone_render_disabled_module_notice'
				);
			}
		}
	}
	add_action( 'admin_menu', 'groupone_register_disabled_module_pages' );
	add_action( 'network_admin_menu', 'groupone_register_disabled_module_pages' );
}

if ( ! function_exists( 'groupone_render_disabled_module_notice' ) ) {
	/**
	 * Callback for the hidden fallback page registered by
	 * groupone_register_disabled_module_pages(). Renders a friendly notice
	 * instead of WordPress core's generic denial message (WPIN-8925).
	 */
	function groupone_render_disabled_module_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
		}
		$slug        = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$labels      = groupone_module_labels();
		$module_slug = '';
		foreach ( groupone_module_page_slugs() as $m_slug => $pages ) {
			if ( in_array( $slug, $pages, true ) ) {
				$module_slug = $m_slug;
				break;
			}
		}
		$label = $labels[ $module_slug ] ?? __( 'This feature', 'onecom-wp' );
		printf(
			'<div class="wrap"><h1>%s</h1><p>%s</p><p><a href="%s" class="button button-primary">%s</a></p></div>',
			esc_html( $label ),
			esc_html__( 'This feature is currently switched off.', 'onecom-wp' ),
			esc_url( admin_url( 'admin.php?page=onecom-home' ) ),
			esc_html__( 'Go to Features to turn it back on', 'onecom-wp' )
		);
	}
}

if ( ! function_exists( 'groupone_ajax_toggle_module' ) ) {
	/**
	 * AJAX: toggle a merged module on/off from the Home page toggle list
	 * (WPIN-8736). Mirrors GroupOne_Performance_Column::handle_toggle().
	 */
	function groupone_ajax_toggle_module(): void {
		check_ajax_referer( 'oc_toggle_module', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}

		$slug = isset( $_POST['module'] ) ? sanitize_key( wp_unslash( $_POST['module'] ) ) : '';
		$map  = groupone_merged_modules_map();

		if ( ! isset( $map[ $slug ] ) ) {
			wp_send_json_error( 'unknown_module', 400 );
		}

		if ( groupone_legacy_plugin_active( $map[ $slug ]['legacy'] ) ) {
			wp_send_json_error( 'legacy_plugin_active', 400 );
		}

		$enabled = isset( $_POST['enabled'] ) ? (bool) intval( $_POST['enabled'] ) : false;

		// WPIN-8859: mirror the disabled-toggle UI gate server-side — a
		// disabled checkbox alone doesn't stop a direct POST to this endpoint.
		// Only blocks turning a gated module ON; turning off is always allowed.
		// ismWP() is defined lazily on admin_init (onecom-themes-plugins.php),
		// which has always fired by the time a real admin-ajax.php request
		// reaches this hook — but not in a test calling this function
		// directly, so make the dependency explicit rather than implicit.
		if ( ! function_exists( 'ismWP' ) ) {
			require_once GROUPONE_WP_PATH . 'inc/functions.php';
		}
		if ( $enabled && ! empty( $map[ $slug ]['mwp_only'] ) ) {
			// WPIN-8899: force a live check here rather than reusing the
			// up-to-12h oc_premi_flag cache — a customer who just upgraded
			// shouldn't have to wait out the old cache to enable this module.
			$is_mwp = ismWP( true );

			if ( null === $is_mwp ) {
				// The live call itself failed (network/API error) — fail
				// closed, but tell the front end apart from a confirmed "no"
				// so it doesn't show the upsell banner for a transient error.
				wp_send_json_error( 'mwp_check_failed', 502 );
			}

			if ( ! $is_mwp ) {
				wp_send_json_error( 'mwp_required', 403 );
			}
		}

		groupone_set_module_enabled( $slug, $enabled );
		groupone_record_module_user_choice( $slug );

		wp_send_json_success(
			array(
				'module'  => $slug,
				'enabled' => $enabled,
				// WPIN-8870: lets the Home page bring the admin sidebar in line
				// with the new state without a reload.
				'menu'    => groupone_module_menu_entries( $slug ),
			)
		);
	}
	add_action( 'wp_ajax_oc_toggle_module', 'groupone_ajax_toggle_module' );
}

/*
 * WPIN-8909: REST-context signal.
 *
 * Onecom_Plugins_Api::get_status() announces "answer as JSON, not HTML" to
 * downstream code — health-monitor's send_json(), the health-monitor AJAX
 * handler, the authenticator's validateToken(). It used to do that by
 * define()ing REST_REQUEST, which had two problems:
 *
 *  - REST_REQUEST is core's, and setting it makes core's wp_send_json() emit
 *    _doing_it_wrong() for every later caller. That was 49 of the suite's 122
 *    failures, spread across seven unrelated test classes.
 *  - A constant cannot be undefined, so under PHPUnit the first test through
 *    get_status() pinned the flag on for the rest of the process.
 *
 * These carry the same signal in our own namespace: still true whenever core
 * says it really is a REST request, so production behaviour is unchanged, but
 * ours to set and clear.
 *
 * They live here rather than in inc/functions.php on purpose. functions.php is
 * loaded lazily (admin_init, or an explicit require at each point of use) and
 * so is NOT available during rest_api_init, which is exactly when get_status()
 * runs. This file is require_once'd unconditionally at parse time by
 * onecom-themes-plugins.php, so these are always defined.
 */
if ( ! function_exists( 'groupone_is_rest_context' ) ) {
	function groupone_is_rest_context(): bool {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		return ! empty( $GLOBALS['groupone_rest_context'] );
	}
}

if ( ! function_exists( 'groupone_set_rest_context' ) ) {
	/**
	 * @param bool $is_rest Pass false to clear it. Tests that need the HTML
	 *                      branch of send_json() should reset this in tearDown()
	 *                      rather than depending on execution order.
	 */
	function groupone_set_rest_context( bool $is_rest = true ): void {
		$GLOBALS['groupone_rest_context'] = $is_rest;
	}
}
