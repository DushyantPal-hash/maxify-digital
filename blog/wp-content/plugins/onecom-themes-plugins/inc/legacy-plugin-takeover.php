<?php

defined( 'WPINC' ) or die(); // No Direct Access

/**
 * Legacy standalone-plugin takeover (WPIN-8758).
 *
 * The merged hub absorbed onecom-vcache, onecom-spam-protection and
 * onecom-under-construction as internal modules (WPIN-8736). A site should
 * only ever run one implementation of each feature, so this keeps exactly one
 * of {hub module, legacy plugin} active in both directions:
 *
 * - Hub activates while a legacy plugin is already active: the legacy plugin
 *   is deactivated as part of the hub's own activation routine.
 * - A legacy plugin is activated while the hub is already active: activation
 *   is blocked outright, before WordPress ever loads the legacy plugin's own
 *   code, and an admin notice explains why.
 *
 * This relies on the merged modules' symbols no longer colliding with the
 * legacy plugins' globals (WPIN-8758) — without that, WordPress's own
 * plugin_sandbox_scrape() fatals before groupone_block_legacy_plugin_activation()
 * (the activated_plugin hook below) ever runs.
 *
 * groupone_prevent_legacy_plugin_activation_early() (the init hook below) is
 * the fix for WPIN-8795 — a shared inc/lib/validator.php bootstrap guard that
 * makes whichever of {hub, legacy plugin} loads second in a request skip its
 * own required setup and fatal, independent of WPIN-8758. The obvious hook for
 * "stop this before the legacy plugin's file loads" is the 'activate_plugin'
 * action, but on the WordPress core version this hub actually runs on,
 * activate_plugin() calls plugin_sandbox_scrape() — which is what loads the
 * plugin and triggers the WPIN-8795 fatal — BEFORE firing 'activate_plugin',
 * not after (verified by reading wp-admin/includes/plugin.php directly; this
 * is the reverse of the hook's documented pre-WP-5.x ordering). Listening on
 * 'activate_plugin' is therefore too late to prevent the fatal, so this
 * instead inspects the current request/command as early as this hub's own
 * bootstrap runs (which always happens before wp-admin/plugins.php's own
 * activation handling, or WP-CLI's Plugin_Command::activate(), regardless of
 * hook ordering) to recognize an activation attempt in progress and stop it
 * before plugin_sandbox_scrape() ever runs: WP-CLI gets WP_CLI::error(); the
 * wp-admin path redirects straight back to the plugin list it came from
 * (groupone_redirect_and_exit()) rather than showing a disconnected wp_die()
 * page, queuing the same "blocked" message that groupone_render_legacy_takeover_notice()
 * already renders as a normal admin_notice for the other direction below --
 * one consistent in-context notice for both directions instead of two
 * different UIs.
 *
 * Both deactivate_plugins() call sites below run inside a request that
 * WordPress core itself is still output-buffering (activate_plugin() wraps
 * the whole activation in ob_start()/ob_get_length() to detect "the plugin
 * generated unexpected output"). deactivate_plugins() can trigger the target
 * plugin's own register_deactivation_hook() callback, which this repo does
 * not control, so any stray output from that path is buffered locally and
 * discarded here rather than leaking into core's buffer and turning a
 * successful takeover into a confusing "activation failed" admin message.
 */

if ( ! function_exists( 'groupone_deactivate_legacy_plugins' ) ) {
	/**
	 * Deactivates every legacy standalone plugin that is currently active.
	 * Called from the hub's own activation hook.
	 */
	function groupone_deactivate_legacy_plugins(): void {
		foreach ( groupone_merged_modules_map() as $module ) {
			$legacy = $module['legacy'];

			if ( groupone_legacy_plugin_active( $legacy ) ) {
				ob_start();
				deactivate_plugins( $legacy, false );
				ob_end_clean();

				groupone_push_legacy_plugin_stats( $legacy, 'takeover', 'hub_activation' );
			}
		}
	}
}

if ( ! function_exists( 'groupone_takeover_legacy_plugins_on_update' ) ) {
	/**
	 * The same takeover, for the update path.
	 *
	 * A site that already had the hub active and merely receives a new version
	 * never runs the activation hook, so its legacy plugins kept running
	 * alongside the merged modules. That is the state behind the duplicated
	 * one.com menu, and the reason such a site keeps running the standalone
	 * code instead of the module.
	 *
	 * Every module whose legacy plugin is deactivated here is also enabled, by
	 * the deactivated_plugin listener. The update path disables all modules by
	 * default (see groupone_plugin_upgradation()), so deactivating alone would
	 * leave the feature off entirely rather than handing it to the module —
	 * this must therefore run after that default has been seeded.
	 */
	function groupone_takeover_legacy_plugins_on_update(): void {
		// upgrader_process_complete also fires under WP-CLI, where wp-admin's
		// plugin functions are not guaranteed to be loaded.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( groupone_merged_modules_map() as $module ) {
			if ( ! groupone_legacy_plugin_active( $module['legacy'] ) ) {
				continue;
			}

			// Enabling the module is left to the deactivated_plugin listener
			// groupone_enable_module_for_legacy_plugin(), which deactivate_plugins()
			// fires below - one reconciliation point for every route, rather
			// than this one keeping its own copy of the gates.
			ob_start();
			deactivate_plugins( $module['legacy'], false );
			ob_end_clean();

			groupone_push_legacy_plugin_stats( $module['legacy'], 'takeover', 'hub_update' );
		}
	}
}

if ( ! function_exists( 'groupone_legacy_plugin_labels' ) ) {
	/**
	 * Human-readable feature name per legacy plugin file, for admin notices.
	 * Built from the same registry/labels the rest of the hub already uses
	 * (inc/module-registry.php) rather than a second hardcoded slug => path
	 * list, so the two can never drift apart.
	 */
	function groupone_legacy_plugin_labels(): array {
		$slug_labels = function_exists( 'groupone_module_labels' ) ? groupone_module_labels() : array();
		$labels      = array();

		foreach ( groupone_merged_modules_map() as $slug => $module ) {
			$labels[ $module['legacy'] ] = $slug_labels[ $slug ] ?? $slug;
		}

		return $labels;
	}
}

if ( ! function_exists( 'groupone_push_legacy_plugin_stats' ) ) {
	/**
	 * Fires a GroupOne_PushStats event for a takeover or cleanup outcome
	 * (WPIN-8737 AC: both must be tracked for rollout monitoring), guarded by
	 * class_exists() the same way modules/maintenance-mode's own stats class
	 * guards its calls, so a hub release without the inc/lib submodule never
	 * fatals.
	 */
	function groupone_push_legacy_plugin_stats( string $legacy_plugin_file, string $event_action, string $referrer ): void {
		if ( ! class_exists( 'GroupOne_PushStats' ) ) {
			return;
		}

		\GroupOne_PushStats::push_stats_event_themes_and_plugins(
			$event_action,
			\GroupOne_PushStats::PLUGIN,
			dirname( $legacy_plugin_file ),
			$referrer
		);
	}
}

if ( ! function_exists( 'groupone_is_wp_cli' ) ) {
	/**
	 * True when running under WP-CLI with a usable Runner (needed to inspect
	 * the command currently being executed). Deliberately requires
	 * WP_CLI::get_runner() to actually return something, rather than just
	 * checking the WP_CLI constant/class -- a real WP-CLI process always has
	 * a runner, so this is no less reliable in production, and it means the
	 * WP_CLI constant being defined (e.g. by an unrelated test elsewhere in
	 * the same PHPUnit process) can't by itself make this true.
	 */
	function groupone_is_wp_cli(): bool {
		return defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' )
			&& method_exists( 'WP_CLI', 'get_runner' ) && null !== WP_CLI::get_runner();
	}
}

if ( ! function_exists( 'groupone_requested_legacy_plugin_activation' ) ) {
	/**
	 * Returns the legacy plugin basename(s) the current request/command is
	 * trying to activate, recognized from wp-admin's single/bulk activation
	 * query args or, for WP-CLI, the running command's own arguments. Empty
	 * array if nothing is being activated right now.
	 */
	function groupone_requested_legacy_plugin_activation(): array {
		$legacy_map = wp_list_pluck( groupone_merged_modules_map(), 'legacy' );
		$requested  = array();

		if ( groupone_is_wp_cli() ) {
			$runner = WP_CLI::get_runner();
			$args   = $runner->arguments ?? array();

			if ( isset( $args[0], $args[1] ) && 'plugin' === $args[0] && str_starts_with( $args[1], 'activate' ) ) {
				$assoc_args = $runner->assoc_args ?? array();

				if ( ! empty( $assoc_args['all'] ) ) {
					// `wp plugin activate --all` has no positional plugin slug --
					// every installed plugin, including any absorbed legacy
					// plugin, is about to be activated.
					return array_values( $legacy_map );
				}

				$requested = array_slice( $args, 2 );
			}
		} elseif ( is_admin() ) {
			$action = '';

			// WordPress's bulk-action UI always submits BOTH `action` (top
			// dropdown) and `action2` (bottom dropdown) -- whichever one the
			// admin didn't use is submitted as the literal string '-1', not
			// left unset, so a plain `??` fallback misses activations done via
			// the bottom dropdown. Mirror WP_List_Table::current_action()'s
			// own convention instead.
			if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {
				$action = $_REQUEST['action'];
			} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {
				$action = $_REQUEST['action2'];
			}
			$action = wp_unslash( $action );

			if ( 'activate' === $action && ! empty( $_REQUEST['plugin'] ) ) {
				$requested[] = wp_unslash( $_REQUEST['plugin'] );
			} elseif ( 'activate-selected' === $action && ! empty( $_REQUEST['checked'] ) ) {
				$requested = array_map( 'wp_unslash', (array) $_REQUEST['checked'] );
			}
		}

		$matched = array();
		foreach ( $requested as $requested_plugin ) {
			foreach ( $legacy_map as $legacy_file ) {
				// WP-CLI accepts either the full plugin file or just its
				// folder name as the plugin slug (e.g.
				// `wp plugin activate onecom-vcache`).
				if ( $requested_plugin === $legacy_file || $requested_plugin === dirname( $legacy_file ) ) {
					$matched[] = $legacy_file;
				}
			}
		}

		return array_unique( $matched );
	}
}

if ( ! function_exists( 'groupone_redirect_and_exit' ) ) {
	/**
	 * wp_safe_redirect() + exit as a single, separately-definable step (same
	 * function_exists() guard convention as everything else in this file) so
	 * tests can pre-define their own non-exiting version before this file is
	 * required, instead of needing PHPUnit process isolation to survive a
	 * real exit() call.
	 */
	function groupone_redirect_and_exit( string $url ): void {
		wp_safe_redirect( $url );
		exit;
	}
}

if ( ! function_exists( 'groupone_prevent_legacy_plugin_activation_early' ) ) {
	/**
	 * Fires on init — i.e. as early as this hub's own bootstrap runs, which
	 * is always before wp-admin/plugins.php's own activation handling or
	 * WP-CLI's Plugin_Command::activate() (see the file header for why the
	 * activate_plugin/activated_plugin hooks below can't do this in time).
	 * If the current request/command is trying to activate one of the
	 * legacy plugins this hub absorbed, stop it here with a clear message
	 * instead of letting WordPress load that plugin's code at all.
	 */
	function groupone_prevent_legacy_plugin_activation_early(): void {
		$is_wp_cli = groupone_is_wp_cli();

		// init fires before WordPress has authenticated the request or
		// checked the plugins.php nonce, so this must gate itself the same
		// way groupone_render_legacy_takeover_notice() does below -- WP-CLI
		// is exempt since anyone able to run it already has full server
		// access.
		if ( ! $is_wp_cli && ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$matched = groupone_requested_legacy_plugin_activation();

		if ( empty( $matched ) ) {
			return;
		}

		$blocked = get_option( 'onecom_takeover_blocked_plugins', array() );
		update_option( 'onecom_takeover_blocked_plugins', array_values( array_unique( array_merge( $blocked, $matched ) ) ), false );

		if ( $is_wp_cli ) {
			$labels         = groupone_legacy_plugin_labels();
			$matched_labels = array();

			foreach ( $matched as $legacy_file ) {
				$matched_labels[] = $labels[ $legacy_file ] ?? $legacy_file;
			}

			$message = ( count( $matched_labels ) === 1 )
				? sprintf(
					/* translators: %s: legacy plugin feature name, e.g. "Performance Cache" */
					esc_html__( 'The standalone %s plugin is now built into the one.com plugin, so it cannot be activated separately. You can safely delete it.', GROUPONE_PLUGIN_DOMAIN ),
					esc_html( $matched_labels[0] )
				)
				: sprintf(
					/* translators: %s: comma-separated legacy plugin feature names, e.g. "Performance Cache, Spam Protection" */
					esc_html__( 'The standalone %s plugins are now built into the one.com plugin, so they cannot be activated separately. You can safely delete them.', GROUPONE_PLUGIN_DOMAIN ),
					esc_html( implode( ', ', $matched_labels ) )
				);

			WP_CLI::error( wp_strip_all_tags( $message ) );
			return;
		}

		// Rather than wp_die()-ing on a bare, disconnected page, redirect back
		// to the plugin list this request came from (stripping only the
		// activation-specific args) and let groupone_render_legacy_takeover_notice()
		// show the same "blocked" message as a normal dismissible admin_notice
		// there instead -- nothing this hub does here ever lets WordPress reach
		// plugin_sandbox_scrape() or write active_plugins, so exiting after the
		// redirect is safe: the plugin never gets marked active either way.
		groupone_redirect_and_exit(
			remove_query_arg( array( 'action', 'action2', 'checked', 'plugin', '_wpnonce', '_wp_http_referer' ) )
		);
	}
}
add_action( 'init', 'groupone_prevent_legacy_plugin_activation_early', 1 );

if ( ! function_exists( 'groupone_block_legacy_plugin_activation' ) ) {
	/**
	 * Fires right after any plugin activates. If it is one of the legacy
	 * plugins the hub absorbed, deactivate it again immediately and queue an
	 * admin notice explaining why, instead of leaving both implementations
	 * running side by side.
	 */
	function groupone_block_legacy_plugin_activation( $plugin, $network_wide = false ): void {
		$legacy_map = wp_list_pluck( groupone_merged_modules_map(), 'legacy' );

		if ( ! in_array( $plugin, $legacy_map, true ) ) {
			return;
		}

		ob_start();
		deactivate_plugins( $plugin, false, $network_wide );
		ob_end_clean();

		$blocked   = get_option( 'onecom_takeover_blocked_plugins', array() );
		$blocked[] = $plugin;
		update_option( 'onecom_takeover_blocked_plugins', array_values( array_unique( $blocked ) ), false );

		groupone_push_legacy_plugin_stats( $plugin, 'takeover', 'blocked_activation' );
	}
}
add_action( 'activated_plugin', 'groupone_block_legacy_plugin_activation', 10, 2 );

if ( ! function_exists( 'groupone_enable_module_for_legacy_plugin' ) ) {
	/**
	 * Hands a feature to its merged module when the legacy plugin that was
	 * providing it stops being active.
	 *
	 * All four ways a legacy plugin can stop providing - the activation
	 * takeover, the block-on-activation, the cleanup notice's "deactivate &
	 * delete", and the owner deactivating it by hand - funnel through core's
	 * deactivate_plugins(), so this single listener covers every one of them.
	 *
	 * Only ever enables. It never adds a slug to onecom_disabled_modules.
	 *
	 * @param string $plugin_file           Plugin file that was deactivated.
	 * @param bool   $network_deactivating  Unused; the hook passes it.
	 *
	 * @return void
	 */
	function groupone_enable_module_for_legacy_plugin( $plugin_file, $network_deactivating = false ): void {
		$target = null;
		$slug   = '';

		foreach ( groupone_merged_modules_map() as $module_slug => $module ) {
			if ( $module['legacy'] === $plugin_file ) {
				$slug   = $module_slug;
				$target = $module;
				break;
			}
		}

		// Not one of the plugins the hub absorbed - leave the option alone.
		if ( null === $target ) {
			return;
		}

		// A gated module stays disabled where it cannot run anyway.
		if ( groupone_module_mwp_blocked( $target ) ) {
			return;
		}

		// The owner's own toggle wins over reconciliation.
		if ( groupone_module_user_choice_recorded( $slug ) ) {
			return;
		}

		groupone_set_module_enabled( $slug, true );
	}
}
add_action( 'deactivated_plugin', 'groupone_enable_module_for_legacy_plugin', 10, 2 );

if ( ! function_exists( 'groupone_render_legacy_takeover_notice' ) ) {
	/**
	 * Renders (and clears) the "this plugin is retired" notice queued by
	 * groupone_block_legacy_plugin_activation().
	 */
	function groupone_render_legacy_takeover_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$blocked = get_option( 'onecom_takeover_blocked_plugins', array() );

		if ( empty( $blocked ) ) {
			return;
		}

		$labels = groupone_legacy_plugin_labels();

		// Only offer the Delete link where WordPress core's own single-site
		// plugins.php?action=delete-selected handler actually accepts it --
		// on multisite that's network admin only, matching core's own gating
		// for the equivalent per-row "Delete" action link.
		$can_delete = current_user_can( 'delete_plugins' ) && ( ! is_multisite() || is_network_admin() );

		foreach ( $blocked as $plugin ) {
			$label = $labels[ $plugin ] ?? $plugin;

			$message = sprintf(
				/* translators: %s: legacy plugin feature name, e.g. "Performance Cache" */
				esc_html__( 'The standalone %s plugin is now built into the one.com plugin, so it has been kept deactivated to avoid conflicts.', GROUPONE_PLUGIN_DOMAIN ),
				esc_html( $label )
			);

			if ( $can_delete && file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				// Same URL shape WP_Plugins_List_Table itself builds for the
				// per-row "Delete" action link -- routes through plugins.php's
				// own delete-selected handler, which shows its normal "are you
				// sure" confirmation page before anything is actually removed.
				$delete_url = wp_nonce_url(
					self_admin_url( 'plugins.php?action=delete-selected&checked[]=' . rawurlencode( $plugin ) . '&plugin_status=all' ),
					'bulk-plugins'
				);

				$message .= ' <a href="' . esc_url( $delete_url ) . '">' . esc_html__( 'Delete it now', GROUPONE_PLUGIN_DOMAIN ) . '</a>';
			}

			printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
		}

		delete_option( 'onecom_takeover_blocked_plugins' );
	}
}
add_action( 'admin_notices', 'groupone_render_legacy_takeover_notice' );
add_action( 'network_admin_notices', 'groupone_render_legacy_takeover_notice' );

/**
 * Cleanup flow (WPIN-8737): once a legacy plugin has been taken over above
 * (deactivated, whichever direction), its files can still be sitting on
 * disk indefinitely. Unlike the one-shot notice above (queued only at the
 * moment an activation attempt was blocked), this persistently prompts the
 * admin to remove those files entirely for as long as any are still
 * present, with a one-click deactivate-and-delete action and a per-user
 * snooze -- independent of whether a plugin is currently active, blocked,
 * or was simply left deactivated by a previous version of this hub.
 */

if ( ! function_exists( 'groupone_detected_legacy_plugins' ) ) {
	/**
	 * Legacy plugin files still present on disk, independent of active
	 * state. A plugin groupone_deactivate_legacy_plugins() already turned
	 * off still needs a cleanup prompt until its files are actually removed.
	 */
	function groupone_detected_legacy_plugins(): array {
		$detected = array();

		foreach ( groupone_merged_modules_map() as $module ) {
			$legacy = $module['legacy'];

			if ( file_exists( WP_PLUGIN_DIR . '/' . $legacy ) ) {
				$detected[] = $legacy;
			}
		}

		return $detected;
	}
}

if ( ! function_exists( 'groupone_legacy_cleanup_notice_dismissed' ) ) {
	/**
	 * Per-admin-user snooze for the persistent cleanup notice below (WPIN-8737
	 * AC: dismissible, does not nag). A plain user meta timestamp rather than
	 * an option, so each admin gets their own snooze instead of one dismissal
	 * hiding it site-wide.
	 */
	function groupone_legacy_cleanup_notice_dismissed(): bool {
		$dismissed_until = (int) get_user_meta( get_current_user_id(), 'onecom_legacy_cleanup_dismissed_until', true );

		return $dismissed_until > time();
	}
}

if ( ! function_exists( 'groupone_dismiss_legacy_cleanup_notice' ) ) {
	/**
	 * admin-post handler for the notice's "Remind me later" link. Snoozes for
	 * 7 days rather than permanently -- the plugin files are still sitting on
	 * disk, so this should come back rather than go silent forever.
	 */
	function groupone_dismiss_legacy_cleanup_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', GROUPONE_PLUGIN_DOMAIN ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'groupone_dismiss_legacy_cleanup' );

		update_user_meta( get_current_user_id(), 'onecom_legacy_cleanup_dismissed_until', time() + 7 * DAY_IN_SECONDS );

		groupone_redirect_and_exit( wp_get_referer() ?: self_admin_url( 'plugins.php' ) );
	}
}
add_action( 'admin_post_groupone_dismiss_legacy_cleanup', 'groupone_dismiss_legacy_cleanup_notice' );

if ( ! function_exists( 'groupone_render_legacy_cleanup_notice' ) ) {
	/**
	 * Renders the persistent "clean up the old plugin(s)" notice. Reappears
	 * on every admin page load for as long as groupone_detected_legacy_plugins()
	 * finds anything, and never renders once nothing remains -- satisfying
	 * the WPIN-8737 AC that it "never appears when no standalone plugin is
	 * present" without needing its own separate queued-option bookkeeping
	 * the way the one-shot notice above does.
	 */
	function groupone_render_legacy_cleanup_notice(): void {
		// Same gating as the one-shot notice's Delete link above: only offer
		// this where WordPress core's own deletion flow is actually reachable
		// (network admin only for a network-active plugin on multisite).
		$can_delete = current_user_can( 'delete_plugins' ) && ( ! is_multisite() || is_network_admin() );

		if ( ! $can_delete || groupone_legacy_cleanup_notice_dismissed() ) {
			return;
		}

		$detected = groupone_detected_legacy_plugins();

		if ( empty( $detected ) ) {
			return;
		}

		$labels = groupone_legacy_plugin_labels();
		$names  = array_map(
			function ( $plugin ) use ( $labels ) {
				return $labels[ $plugin ] ?? $plugin;
			},
			$detected
		);

		$cleanup_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'groupone_cleanup_legacy_plugins',
					// add_query_arg()/build_query() do NOT encode newly-supplied
					// values (only pre-existing query-string params on the base
					// URL get re-encoded) -- rawurlencode() each plugin path
					// before joining so the query string stays well-formed even
					// if a path ever contains a reserved character. The comma
					// separator itself is added after encoding, so it stays a
					// literal delimiter; PHP's own single urldecode() pass when
					// populating $_GET reverses this on the read side, so no
					// matching rawurldecode() is needed there.
					'plugins' => implode( ',', array_map( 'rawurlencode', $detected ) ),
				),
				// Core ships no wp-admin/network/admin-post.php, so
				// self_admin_url() would 404 this link in network admin.
				admin_url( 'admin-post.php' )
			),
			'groupone_cleanup_legacy_plugins'
		);

		$dismiss_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=groupone_dismiss_legacy_cleanup' ),
			'groupone_dismiss_legacy_cleanup'
		);

		printf(
			'<div class="notice notice-info is-dismissible"><p>%1$s</p><p><a href="%2$s" class="button button-primary">%3$s</a> <a href="%4$s">%5$s</a></p></div>',
			sprintf(
				/* translators: %s: comma-separated legacy plugin feature names, e.g. "Performance Cache, Spam Protection" */
				esc_html__( 'The %s feature(s) are now built into the one.com plugin, and the old standalone plugin(s) have been deactivated. You can safely delete them.', GROUPONE_PLUGIN_DOMAIN ),
				esc_html( implode( ', ', $names ) )
			),
			esc_url( $cleanup_url ),
			esc_html__( 'Delete now', GROUPONE_PLUGIN_DOMAIN ),
			esc_url( $dismiss_url ),
			esc_html__( 'Remind me later', GROUPONE_PLUGIN_DOMAIN )
		);
	}
}
add_action( 'admin_notices', 'groupone_render_legacy_cleanup_notice' );
add_action( 'network_admin_notices', 'groupone_render_legacy_cleanup_notice' );

if ( ! function_exists( 'groupone_handle_cleanup_legacy_plugins' ) ) {
	/**
	 * admin-post handler for the cleanup notice's "Delete now"
	 * action. Per requested legacy plugin: snapshots any wp-option its own
	 * uninstall routine deletes (protected_options in groupone_merged_modules_map()
	 * -- confirmed by reading each legacy plugin's actual uninstall.php/
	 * register_uninstall_hook() source, e.g. onecom-under-construction's
	 * uninstall.php unconditionally deletes onecom_under_construction_info),
	 * deletes the plugin via delete_plugins(), restores the option if it was
	 * wiped, clears any cron the plugin registered, and pushes a stats event.
	 *
	 * delete_plugins() itself may -- if this environment ever lacks direct
	 * filesystem write access -- print WordPress's own credentials-request
	 * page and exit() from inside that call before returning here at all.
	 * That is an acceptable failure mode (breaks the one-click UX for that
	 * request, but nothing is lost): uninstall_plugin() and the actual file
	 * removal both happen only after WP_Filesystem() connects successfully,
	 * so if the request exits at the credentials prompt, neither the plugin
	 * files nor its options have been touched yet -- the customer simply
	 * sees core's normal credentials form instead of the redirect-back below.
	 */
	function groupone_handle_cleanup_legacy_plugins(): void {
		check_admin_referer( 'groupone_cleanup_legacy_plugins' );

		// Core maps delete_plugins to do_not_allow for anyone who is not a
		// super admin on multisite (capabilities.php), so the capability
		// alone is the whole guard. An is_network_admin() clause would only
		// ever reject: this runs in admin-post.php, which has no network
		// counterpart and therefore always answers false.
		if ( ! current_user_can( 'delete_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', GROUPONE_PLUGIN_DOMAIN ), '', array( 'response' => 403 ) );
		}

		// $_GET['plugins'] must be a plain string -- explode() throws a
		// TypeError on PHP 8+ if handed anything else (e.g. a hand-crafted
		// plugins[]=a&plugins[]=b request reaching this URL with a valid,
		// value-agnostic nonce).
		$plugins_param = ( isset( $_GET['plugins'] ) && is_string( $_GET['plugins'] ) ) ? wp_unslash( $_GET['plugins'] ) : '';
		$requested     = ( '' !== $plugins_param ) ? explode( ',', $plugins_param ) : array();
		$module_map    = groupone_merged_modules_map();

		$modules_by_legacy = array();
		foreach ( $module_map as $module ) {
			$modules_by_legacy[ $module['legacy'] ] = $module;
		}

		$results = array();

		foreach ( $requested as $plugin ) {
			if ( ! isset( $modules_by_legacy[ $plugin ] ) || ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				continue;
			}

			$module   = $modules_by_legacy[ $plugin ];
			$snapshot = array();

			foreach ( $module['protected_options'] as $option ) {
				$snapshot[ $option ] = get_option( $option );
			}

			if ( groupone_legacy_plugin_active( $plugin ) ) {
				ob_start();
				deactivate_plugins( $plugin, false );
				ob_end_clean();
			}

			// Buffered for the same reason as deactivate_plugins() above, and
			// then some: delete_plugins() runs the target plugin's own
			// uninstall.php (code this repo does not control), fires
			// deleted_plugin, and finally calls set_site_transient(
			// 'update_plugins' ), which re-enters every third-party
			// pre_set_site_transient_update_plugins filter on the site. Any
			// byte any of that emits would land ahead of the wp_safe_redirect()
			// below on a request that has not sent its headers yet, turning
			// the whole cleanup into a "headers already sent" dead end. If
			// delete_plugins() instead exits inside request_filesystem_credentials(),
			// PHP flushes this buffer at shutdown, so core's credentials page
			// still reaches the browser intact.
			ob_start();
			$deleted = delete_plugins( array( $plugin ) );
			ob_end_clean();

			foreach ( $snapshot as $option => $value ) {
				if ( false !== $value && false === get_option( $option ) ) {
					update_option( $option, $value );
				}
			}

			foreach ( $module['cron_hooks'] as $hook ) {
				wp_clear_scheduled_hook( $hook );
			}

			$success            = ( true === $deleted );
			$results[ $plugin ] = $success;

			groupone_push_legacy_plugin_stats( $plugin, $success ? 'cleanup_success' : 'cleanup_failure', 'legacy_cleanup_notice' );
		}

		set_transient( 'onecom_legacy_cleanup_results_' . get_current_user_id(), $results, 5 * MINUTE_IN_SECONDS );

		groupone_redirect_and_exit( wp_get_referer() ?: self_admin_url( 'plugins.php' ) );
	}
}
add_action( 'admin_post_groupone_cleanup_legacy_plugins', 'groupone_handle_cleanup_legacy_plugins' );

if ( ! function_exists( 'groupone_render_legacy_cleanup_results_notice' ) ) {
	/**
	 * One-time per-plugin success/failure feedback after
	 * groupone_handle_cleanup_legacy_plugins() redirects back (WPIN-8737 AC:
	 * "clear success/failure feedback").
	 */
	function groupone_render_legacy_cleanup_results_notice(): void {
		$key     = 'onecom_legacy_cleanup_results_' . get_current_user_id();
		$results = get_transient( $key );

		if ( false === $results ) {
			return;
		}

		delete_transient( $key );

		$labels = groupone_legacy_plugin_labels();

		foreach ( $results as $plugin => $success ) {
			$label = $labels[ $plugin ] ?? $plugin;

			if ( $success ) {
				printf(
					'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
					sprintf(
						/* translators: %s: legacy plugin feature name */
						esc_html__( '%s cleanup complete — the old standalone plugin was deactivated and removed.', GROUPONE_PLUGIN_DOMAIN ),
						esc_html( $label )
					)
				);
			} else {
				printf(
					'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
					sprintf(
						/* translators: %s: legacy plugin feature name */
						esc_html__( 'Could not remove the old standalone %s plugin automatically. Please delete it manually from the Plugins page.', GROUPONE_PLUGIN_DOMAIN ),
						esc_html( $label )
					)
				);
			}
		}
	}
}
add_action( 'admin_notices', 'groupone_render_legacy_cleanup_results_notice' );
add_action( 'network_admin_notices', 'groupone_render_legacy_cleanup_results_notice' );
