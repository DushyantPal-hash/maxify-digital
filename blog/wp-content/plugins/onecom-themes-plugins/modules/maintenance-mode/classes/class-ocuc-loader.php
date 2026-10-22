<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      0.1.0
 *
 * @package    Under_Construction
 * @subpackage GroupOne_UC_Loader
 *
 * Ported into modules/maintenance-mode (WPIN-8745). Changes vs. the standalone
 * onecom-under-construction plugin:
 * - includes() no longer pulls in private copies of validator.php,
 *   onecom-nested-menu.php, GroupOne_PushStats.php or class-onecomupdater.php: the hub
 *   (onecom-themes-plugins.php) already loads its own inc/lib submodule copy of
 *   all of these once, so requiring/instantiating them again here would either
 *   duplicate GroupOne_Nested_Menu registration or fatal on a missing inc/lib
 *   folder under this module.
 * - require paths for sibling classes were rewritten from 'inc/classes/...' to
 *   'classes/...' to match this module's flattened directory layout.
 * - ocuc_wp_load_textdomain() no longer relies on ONECOM_UC_PLUGIN_SLUG (which
 *   is derived from plugin_basename() of this module's bootstrap file and is
 *   not guaranteed to resolve to this module's own folder once nested under
 *   the hub); it now computes the languages path explicitly from
 *   GROUPONE_UC_PLUGIN_URL so translations keep loading from
 *   modules/maintenance-mode/languages/ regardless of how that slug resolves.
 */

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GroupOne_UC_Loader {


	// Constructor
	public function init_loader() {
		$this->define_constants();
		$this->includes();
		add_action( 'init', array( $this, 'ocuc_wp_load_textdomain' ), -1 );
	}

	// Define required constants.
	public function define_constants() {

        $this->maybe_define( 'ONECOM_UC_VERSION', '3.1.16' );

        $this->maybe_define( 'ONECOM_UC_TEXT_DOMAIN', 'onecom-uc' );
        $this->maybe_define( 'ONECOM_UC_PLUGIN_NAME', 'Maintenance Mode' );
        $this->maybe_define( 'ONECOM_UC_PLUGIN_SLUG', dirname( plugin_basename( GROUPONE_UC_PLUGIN_MAIN_FILE ) ) );
        $this->maybe_define( 'ONECOM_UC_OPTION_FIELD', 'onecom_under_construction_info' );
	}

    public function maybe_define( $name, $value ) {
        if ( ! defined( $name ) ) {
            return define( $name, $value );
        }
        return false;
    }

	// load text domain for language translation
	public function ocuc_wp_load_textdomain() {
		$current_locale           = get_locale();
		$locales_with_translation = array(
			'da_DK',
			'de_DE',
			'es_ES',
			'fr_FR',
			'it_IT',
			'pt_PT',
			'nl_NL',
			'sv_SE',
		);

		// Locales fallback and load english translations [as] if selected unsupported language in WP-Admin
		if ( 'fi' === $current_locale ) {
			$result = load_textdomain( 'onecom-uc', GROUPONE_UC_PLUGIN_URL . '/languages/onecom-uc-fi_FI.mo' );
		} elseif ( 'nb_NO' === $current_locale ) {
			$result = load_textdomain( 'onecom-uc', GROUPONE_UC_PLUGIN_URL . '/languages/onecom-uc-no_NO.mo' );
		} elseif ( in_array( get_locale(), $locales_with_translation, true ) ) {
			// Explicitly resolve the languages folder from this module's own
			// path instead of ONECOM_UC_PLUGIN_SLUG (see class docblock).
			$languages_rel_path = trim( str_replace( wp_normalize_path( WP_PLUGIN_DIR ), '', wp_normalize_path( GROUPONE_UC_PLUGIN_URL ) ), '/' ) . '/languages';
			$result             = load_plugin_textdomain( 'onecom-uc', false, $languages_rel_path );
		} else {
			$result = load_textdomain( 'onecom-uc', GROUPONE_UC_PLUGIN_URL . '/languages/onecom-uc-en_GB.mo' );
		}

		return $result;
	}

	// Include assets, hooks, admin, public files
	public function includes() {
		// General functions (validator, GroupOne_Nested_Menu, GroupOne_PushStats, GroupOne_Updater) are
		// already loaded once by the hub's own inc/lib submodule - do not require private
		// copies here (this module ships no inc/lib of its own).

		// include plugin specific admin & frontend assets
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-assets.php';

		// include admin related files
		if ( is_admin() ) {
			include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-admin-settings-api.php';
			include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-admin-settings.php';
		}

		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-activator.php';
		// GroupOne_UC_Deactivator is defined for parity/tests but intentionally never
		// hooked anywhere: a module has no deactivation event to hook (unlike
		// register_deactivation_hook() for the standalone plugin), so its
		// uc_deactivate_actions() cache-purge logic is presently dead code.
		// See maintenance-mode.php and the WPIN-8745 port summary.
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-deactivator.php';
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-stats.php';
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-cache-purge.php';
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-toolbar.php';

		// include frontend related files
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-captcha.php';
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-newsletter.php';
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-render-views.php';
		include_once GROUPONE_UC_PLUGIN_URL . 'classes/class-ocuc-themes.php';

		return null;
	}
}
