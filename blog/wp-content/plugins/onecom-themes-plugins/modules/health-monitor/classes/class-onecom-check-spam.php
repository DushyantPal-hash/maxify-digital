<?php

/**
 * Class OnecomCheckSpam
 * Deals with spam related checks and fixes
 */
declare(strict_types=1);

class OnecomCheckSpam extends OnecomHealthMonitor {

	private $plugin_list_url     = MIDDLEWARE_URL . '/antispam-plugins';
	private $plugin              = 'onecom-spam-protection/onecom-spam-protection.php';
	private $plugin_download_url = MIDDLEWARE_URL . '/plugins/onecom-spam-protection/download';

	/**
	 * Check if spam protection is enabled using any of the commonly used plugins.
	 * @return array
	 */
	public function check_spam_protection( $is_login_check = false ): array {
		/**
		 * when $is_login_check is empty string, it gives a false positive in tests below.
		 */
		$is_login_check     = (bool) $is_login_check;
		$plugin_object      = new OnecomCheckPlugins();
		$response           = wp_remote_get( $this->plugin_list_url );
		$list               = wp_remote_retrieve_body( $response );
		$list_decoded       = json_decode( $list, true );
		$remote_plugin_list = array();
		if ( isset( $list_decoded['data'] ) ) {
			$remote_plugin_list = $list_decoded['data'];
		}
		$remote_plugin_list[] = 'onecom-spam-protection';
		$active_plugins       = get_option( 'active_plugins' );
		$active_plugin_slugs  = array();
		if ( empty( $remote_plugin_list ) ) {
			$remote_plugin_list = array();
		}
		foreach ( $active_plugins as $plugin ) {
			$active_plugin_slugs[] = $plugin_object->plugin_slug( $plugin );
		}
		$plugin_intersection = array_intersect( $active_plugin_slugs, $remote_plugin_list );

		/**
		 * WPIN-8930: Spam Protection now also ships as a merged module of this
		 * plugin (WPIN-8736). A module is not a plugin, so it never appears in
		 * the `active_plugins` option and the slug intersection above cannot
		 * see it — a site running the module was told to install the very
		 * feature it already had, with an "Install the plugin" button that
		 * would have pulled the superseded standalone plugin back in.
		 * The intersection still covers the legacy standalone plugin and every
		 * third-party anti-spam plugin the middleware list names.
		 */
		$has_spam_protection = ! empty( $plugin_intersection ) || $this->groupone_module_active( 'spam-protection' );

		// if this is not a login_attempts check, the value of theme mod doesn't matter
		if ( isset( $_POST['action'] ) ) {
			$login_condition = ( $_POST['action'] === 'ocsh_check_login_attempts' || $is_login_check );
		} else {
			$login_condition = false;
		}

		if ( ! $has_spam_protection && ( $login_condition || ( ! get_theme_mod( 'oc_checkbox' ) ) ) ) {
			return $this->format_result( $this->flag_open );
		} else {
			return $this->format_result( $this->flag_resolved );
		}
	}

	public function check_login_attempts() {
		// if any plugin from this array is active then this audit will be passed.
		$plugins_to_check = array(
			'login-lockdown/loginlockdown.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'limit-login-attempts/limit-login-attempts.php',
		);

		foreach ( $plugins_to_check as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return $this->format_result( $this->flag_resolved );
			}
		}

		// WPIN-8930: module or legacy standalone plugin — both read and honour
		// the same `onecom_sp_protect_options` option below.
		if ( $this->groupone_feature_active( 'spam-protection', $this->plugin ) ) {
			$raw_sp_options = get_option( 'onecom_sp_protect_options' );

			/**
			 * WPIN-8930: this option is seeded by
			 * groupone_spam_protection_activate(), which fires on the hub's
			 * activation — not when a site switches the merged module on from
			 * the Home page toggle list. Until then get_option() returns false,
			 * and json_decode( false ) is a TypeError on PHP 8. Reaching this
			 * branch at all is new (it used to be gated on the legacy plugin
			 * being active, which implied the option existed), so treat "no
			 * settings saved yet" as "the check is not switched on" rather than
			 * letting it reach json_decode() or reporting malformed JSON.
			 */
			if ( ! is_string( $raw_sp_options ) || '' === $raw_sp_options ) {
				return $this->format_result( $this->flag_open );
			}

			$sp_options = json_decode( $raw_sp_options, true );
			// Check for JSON decoding errors
			$json_rrror    = json_last_error();
			$login_enabled = ( isset( $sp_options['checks']['oc_sp_quickres'] ) && $sp_options['checks']['oc_sp_quickres'] !== null ) ? $sp_options['checks']['oc_sp_quickres'] : null;
			if ( $json_rrror === JSON_ERROR_NONE && filter_var( $login_enabled, FILTER_VALIDATE_BOOLEAN ) === true ) {
				return $this->format_result( $this->flag_resolved );
			} elseif ( $json_rrror !== JSON_ERROR_NONE ) {
				error_log( 'JSON decoding error for spam protection options' );
				return $this->format_result( $this->flag_open );
			} else {
				return $this->format_result( $this->flag_open );
			}
		}
		return $this->format_result( $this->flag_open );
	}

	/**
	 * @return array
	 */
	public function is_onecom_theme(): array {
		$theme  = wp_get_theme();
		$author = $theme->get( 'Author' );
		if ( in_array( $author, array( 'one.com', 'onecom' ) ) ) {
			return array(
				'onecom_theme' => true,
				'url'          => admin_url( '/customize.php?autofocus[section]=oc_spam_checkbox' ),
			);
		}

		return array(
			'onecom_theme' => false,
			'url'          => '',
		);
	}

	/**
	 * Install the spam protection plugin
	 * @return array
	 */
	public function fix_spam_protection(): array {
		$check = 'spam_protection';
		if ( $_POST['action'] === 'ocsh_fix_login_attempts' ) {
			$check = 'login_attempts';
		}
		// WPIN-8930: with the merged module providing the feature there is
		// nothing to install — flipping the setting is the whole fix. Without
		// this, the fix button downloaded and activated the superseded
		// standalone plugin, which inc/legacy-plugin-takeover.php then
		// immediately deactivates again.
		if ( $this->groupone_feature_active( 'spam-protection', $this->plugin ) ) {
			$this->update_spam_protection_setting();
			return $this->format_result(
				$this->flag_resolved,
				$this->text[ $check ][ $this->fix_confirmation ],
				$this->text[ $check ][ $this->status_desc ][ $this->status_resolved ]
			);
		}

		// WPIN-9055: groupone_feature_active() is also false when this build
		// ships the module but the site owner switched it off via the
		// Features toggle screen. Only a build with no module at all (an old
		// standalone-only install) should fall through to installing the
		// legacy plugin below — a registered-but-disabled module just needs
		// enabling, exactly like the Home page toggle does.
		if ( $this->merged_module_registered( 'spam-protection' ) ) {
			groupone_set_module_enabled( 'spam-protection', true );
			$this->update_spam_protection_setting();
			return $this->format_result(
				$this->flag_resolved,
				$this->text[ $check ][ $this->fix_confirmation ],
				$this->text[ $check ][ $this->status_desc ][ $this->status_resolved ]
			);
		}

		if ( $this->install_plugin() ) {
			$this->update_spam_protection_setting();
			return $this->format_result(
				$this->flag_resolved,
				$this->text[ $check ][ $this->fix_confirmation ],
				$this->text[ $check ][ $this->status_desc ][ $this->status_resolved ]
			);
		} else {
			return $this->format_result( $this->flag_open );
		}
	}

	public function update_spam_protection_setting( $status = true ) {
		// WPIN-8930: same missing/!malformed option guard as check_login_attempts()
		// — json_decode( false ) is a TypeError on PHP 8, and writing a key into
		// a non-array is not a valid starting point either. Seed a fresh array
		// instead so the setting is still recorded on a site whose module was
		// enabled without the hub being re-activated.
		$raw_sp_options = get_option( 'onecom_sp_protect_options' );
		$sp_options     = is_string( $raw_sp_options ) ? json_decode( $raw_sp_options, true ) : null;

		if ( ! is_array( $sp_options ) ) {
			$sp_options = array();
		}

		$sp_options['checks']['oc_sp_quickres'] = $status;
		update_option( 'onecom_sp_protect_options', json_encode( $sp_options ), false );
	}

	public function undo_spam_protection(): array {
		$check = 'spam_protection';
		if ( $_POST['action'] === 'ocsh_undo_login_attempts' ) {
			$check = 'login_attempts';
			// WPIN-8930: same as above — on a module site this branch is the
			// correct undo (turn the check back off). Falling through to the
			// deactivate_plugins() below instead would have been a silent
			// no-op, leaving oc_sp_quickres on while reporting it undone.
			if ( $this->groupone_feature_active( 'spam-protection', $this->plugin ) ) {
				$this->update_spam_protection_setting( false );
				return array(
					$this->status_key      => $this->flag_resolved,
					$this->fix_button_text => $this->text[ $check ][ $this->fix_button_text ],
					$this->desc_key        => $this->text[ $check ][ $this->status_desc ][ $this->status_open ],
					$this->how_to_fix      => $this->text[ $check ][ $this->how_to_fix ],
					'ignore_text'          => $this->ignore_text,
				);
			}
		}

		deactivate_plugins( $this->plugin );

		return array(
			$this->status_key      => $this->flag_resolved,
			$this->fix_button_text => $this->text[ $check ][ $this->fix_button_text ],
			$this->desc_key        => $this->text[ $check ][ $this->status_desc ][ $this->status_open ],
			$this->how_to_fix      => $this->text[ $check ][ $this->how_to_fix ],
			'ignore_text'          => $this->ignore_text,
		);
	}

	/**
	 * True when the feature ships as a merged module of this plugin (WPIN-8736).
	 *
	 * Mirrors Onecom_Plugins_Api::groupone_module_active(), guard included.
	 * inc/module-registry.php is require_once'd unconditionally at parse time
	 * by onecom-themes-plugins.php, so in practice the function is always
	 * there; the guard keeps this class degrading to the plain
	 * is_plugin_active() behaviour it always had rather than fataling if this
	 * file is ever loaded without the registry (a hub predating WPIN-8736, or
	 * a test harness bootstrapping the health-monitor classes on their own).
	 */
	private function groupone_module_active( string $module_slug ): bool {
		return function_exists( 'groupone_module_active' ) && groupone_module_active( $module_slug );
	}

	/**
	 * A feature is available either as a merged module or via its legacy
	 * standalone plugin. Mirrors Onecom_Plugins_Api::groupone_feature_active().
	 */
	private function groupone_feature_active( string $module_slug, string $plugin_file ): bool {
		return $this->groupone_module_active( $module_slug ) || is_plugin_active( $plugin_file );
	}

	/**
	 * True when this build ships the merged module's bootstrap file at all —
	 * whether currently enabled or switched off via the Features toggle —
	 * as opposed to an old standalone-only install where the module doesn't
	 * exist in the codebase and the legacy plugin is the only option
	 * (mirrors OnecomCheckPlugins::merged_module_registered(), WPIN-9040/WPIN-9055).
	 */
	private function merged_module_registered( string $module_slug ): bool {
		if ( ! function_exists( 'groupone_merged_modules_map' ) || ! function_exists( 'groupone_set_module_enabled' ) ) {
			return false;
		}
		$map = groupone_merged_modules_map();

		return isset( $map[ $module_slug ]['bootstrap'] ) && file_exists( $map[ $module_slug ]['bootstrap'] );
	}

	/**
	 * Install a plguin
	 * @return bool
	 * @todo    remove hardcoded values
	 */
	private function install_plugin(): bool {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$upgrader->install( $this->plugin_download_url );
		return is_null( activate_plugin( $this->plugin ) );
	}
}
