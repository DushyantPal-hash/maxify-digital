<?php
require_once get_template_directory()  . '/class-tgm-plugin-activation.php';

if (!function_exists('ncmaz_theme_register_required_plugins')) :
	function ncmaz_theme_register_required_plugins()
	{
		$plugins = [
			// A WordPress.org plugin repository example.
			[
				'name'     	=> 'Advanced Custom Fields',
				'slug'     	=> 'advanced-custom-fields',
				'required' 	=> true,
			],
			[
				'name'     	=> 'Favorites',
				'slug'     	=> 'favorites',
				'required' 	=> true,
			],
			[
				'name'     	=> 'Email Subscribers & Newsletters',
				'slug'     	=> 'email-subscribers',
				'required' 	=> true,
			],
			[
				'name'     	=> 'Nextend Social Login',
				'slug'     	=> 'nextend-facebook-connect',
				'required' 	=> true,
			],
			[
				'name'     	=> 'Reading Time Wp',
				'slug'     	=> 'reading-time-wp',
				'required' 	=> true,
			],
			[
				'name'     	=> 'Template Library and Redux Framework',
				'slug'     	=> 'redux-framework',
				'required' 	=> true,
			],
			[
				'name'     	=> 'WP GraphQL',
				'slug'     	=> 'wp-graphql',
				'required' 	=> true,
			],
			[
				'name'     	=> 'One click demo import',
				'slug'     	=> 'one-click-demo-import',
				'required' 	=> false,
			],

			// A locally theme bundled plugin example.
			[
				'name'     	=> 'Ncmaz Core',
				'slug'     	=> 'ncmaz-core',
				'source'   	=> get_template_directory() . '/bundled-plugins/ncmaz-core.zip',
				'required' 	=> true,
				'version'	=> '2.1.0'
			],
			[
				'name'     	=> 'Ncmaz Frontend',
				'slug'     	=> 'ncmaz-frontend',
				'source'   	=> get_template_directory() . '/bundled-plugins/ncmaz-frontend.zip',
				'required' 	=> true,
				'version'	=> '2.1.5'
			],

			// WPGraphQL Extensions
			[
				'name'     	=> 'Total Counts for WPGraphQL',
				'slug'     	=> 'total-counts-for-wp-graphql',
				// 'source'   	=> get_template_directory() . '/bundled-plugins/total-counts-for-wp-graphql.zip',
				'source'   	=> 'https://github.com/builtbycactus/total-counts-for-wp-graphql/archive/refs/heads/master.zip',
				'required' 	=> true,
			],
			[
				'name'     	=> 'WPGraphQL for Advanced Custom Fields',
				'slug'     	=> 'wp-graphql-acf',
				// 'source'   	=> get_template_directory() . '/bundled-plugins/wp-graphql-acf.zip',
				'source'   	=> 'https://github.com/wp-graphql/wp-graphql-acf/archive/refs/heads/develop.zip',
				'required' 	=> true,
			],
		];

		/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
		$config = [
			'id'           => 'ncmaztgmpa',
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.
		];
		tgmpa($plugins, $config);
	}
	add_action('tgmpa_register', 'ncmaz_theme_register_required_plugins');
endif;


// DE-ACTIVE ncmaz-demo-importer PLUGIN FOR OLD CLIENTS
// WILL REMOVE ON NEXT UPDATE
add_action('init',  function () {
	if (is_plugin_active('ncmaz-demo-importer/ncmaz-demo-importer.php')) {
		deactivate_plugins('ncmaz-demo-importer/ncmaz-demo-importer.php');
	}
});
