<?php
if (!function_exists('ncmaz_ocdi_import_files')) :
	function ncmaz_ocdi_import_files()
	{
		return [
			[
				'import_file_name'             => 'Ncmaz Demo 1',
				'categories'                   => ['Demo 1'],
				'local_import_file'            => get_template_directory() . '/ocdi-demos/demo1/ncmaz.xml',
				'local_import_widget_file'     => get_template_directory() .  '/ocdi-demos/demo1/widgets.wie',
				'local_import_customizer_file' => get_template_directory() . '/ocdi-demos/demo1/customize.dat',
				'local_import_redux'           => [
					[
						'file_path'   => get_template_directory() . '/ocdi-demos/demo1/redux.json',
						'option_name' => 'ncmaz_redux_demo',
					],
				],
				'import_preview_image_url'     =>  'https://chisnghiax.com/__TF__DESC__IMGS/1_ncmaz/12.png',
				'preview_url'                  => 'https://ncmaz.chisnghiax.com/',
			],
			[
				'import_file_name'             => 'Ncmaz Demo 1 - No Data',
				'categories'                   => ['Demo 1'],
				'local_import_file'            => get_template_directory() . '/ocdi-demos/demo1-no-data/ncmaz.xml',
				'local_import_widget_file'     => get_template_directory() .  '/ocdi-demos/demo1-no-data/widgets.wie',
				'local_import_customizer_file' => get_template_directory() . '/ocdi-demos/demo1-no-data/customize.dat',
				'local_import_redux'           => [
					[
						'file_path'   => get_template_directory() . '/ocdi-demos/demo1-no-data/redux.json',
						'option_name' => 'ncmaz_redux_demo',
					],
				],
				'import_preview_image_url'     =>  'https://chisnghiax.com/__TF__DESC__IMGS/1_ncmaz/12.png',
				'preview_url'                  => 'https://ncmaz.chisnghiax.com/',
			],
		];
	}
endif;
add_filter('ocdi/import_files', 'ncmaz_ocdi_import_files');


// AFTER IMPORT DEMO -- SETTING MENU, FRONT-PAGE, WIDGET ...
if (!function_exists('ncmaz_ocdi_after_import_setup')) :
	function ncmaz_ocdi_after_import_setup()
	{
		// Assign menus to their locations.
		$main_menu = get_term_by('name', 'Ncmaz Main Menu', 'nav_menu');
		set_theme_mod(
			'nav_menu_locations',
			[
				'primary' => $main_menu->term_id,
			]
		);

		// Assign front page and posts page (blog page).
		$front_page_id = get_page_by_title('Home 1');

		update_option('show_on_front', 'page');
		update_option('page_on_front', $front_page_id->ID);

		// UPDATE OPTION READING TIME WP
		global $reading_time_wp;
		if (!empty($reading_time_wp)) :
			$update_options = array(
				'label'              => "",
				'postfix'            => __('minutes', 'ncmaz'),
				'postfix_singular'   => __('minute', 'ncmaz'),
				'wpm'                => 300,
				'before_content'     => false,
				'before_excerpt'     => false,
				'exclude_images'     => false,
				'post_types'         => ['post' => true],
				'include_shortcodes' => false,
			);
			update_option('rt_reading_time_options', $update_options);
		endif;

		// UPDATE FAVORITES_PLUGIN_
		if (defined('FAVORITES_PLUGIN_FILE')) :
			update_option('simplefavorites_dependencies', [
				'css' => 'false',
				'js' => 'true'
			]);
			update_option('simplefavorites_display', [
				'button_element_type' 	=> 'button',
				'buttontype' 			=> 'custom',
				'buttontext' 			=> __('<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.995 7.23319C10.5455 5.60999 8.12832 5.17335 6.31215 6.65972C4.49599 8.14609 4.2403 10.6312 5.66654 12.3892L11.995 18.25L18.3235 12.3892C19.7498 10.6312 19.5253 8.13046 17.6779 6.65972C15.8305 5.18899 13.4446 5.60999 11.995 7.23319Z" clip-rule="evenodd"></path></svg>', 'ncmaz'),
				'buttontextfavorited' 	=> __('<svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.995 7.23319C10.5455 5.60999 8.12832 5.17335 6.31215 6.65972C4.49599 8.14609 4.2403 10.6312 5.66654 12.3892L11.995 18.25L18.3235 12.3892C19.7498 10.6312 19.5253 8.13046 17.6779 6.65972C15.8305 5.18899 13.4446 5.60999 11.995 7.23319Z" clip-rule="evenodd"></path></svg>', 'ncmaz'),
				'buttoncount'			=> 'true',
				'posttypes' 			=> [
					'post' => [
						'display'	=> 'true',
					]
				]
			]);
		endif;
	}
endif;
add_action('ocdi/after_import', 'ncmaz_ocdi_after_import_setup');
