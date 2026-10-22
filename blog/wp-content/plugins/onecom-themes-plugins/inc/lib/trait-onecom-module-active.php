<?php

declare(strict_types=1);

if ( ! trait_exists( 'Onecom_Module_Active_Trait' ) ) {
	/**
	 * Trait Onecom_Module_Active_Trait
	 *
	 * Shared module-awareness and feature-gating helpers across admin menu classes.
	 */
	trait Onecom_Module_Active_Trait {

		/**
		 * True when the feature ships as a merged module of the one.com plugin.
		 * The hub (onecom-themes-plugins >= 6.0) provides this via
		 * inc/module-registry.php, still named onecom_module_active() there
		 * as of WPIN-8736 — check groupone_module_active() first in case the
		 * hub renames to match this submodule's rebrand later, but fall back
		 * to the name the hub actually defines today. On older/standalone
		 * installs neither exists, so this stays false, unchanged.
		 *
		 * @param string $module_slug Module slug (e.g. 'performance-cache', 'spam-protection', 'maintenance-mode').
		 * @return bool True if the module is active in the hub plugin, false otherwise.
		 */
		public function oc_module_active( string $module_slug ): bool {
			if ( function_exists( 'groupone_module_active' ) ) {
				return groupone_module_active( $module_slug );
			}
			return function_exists( 'onecom_module_active' ) && onecom_module_active( $module_slug );
		}

		/**
		 * A feature is available either as a merged module or via its legacy
		 * standalone plugin.
		 *
		 * @param string $module_slug Module slug.
		 * @param string $plugin_file Relative path to standalone plugin file.
		 * @return bool True if either the module or legacy standalone plugin is active.
		 */
		public function oc_feature_active( string $module_slug, string $plugin_file ): bool {
			return $this->oc_module_active( $module_slug ) || is_plugin_active( $plugin_file );
		}
	}
}
