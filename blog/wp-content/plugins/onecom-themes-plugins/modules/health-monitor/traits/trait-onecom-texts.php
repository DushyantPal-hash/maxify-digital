<?php
declare( strict_types=1 );

trait OnecomHMTexts {
	public $action_title       = 'action_title';
	public $overview           = 'overview';
	public $fix_button_text    = 'fix_button_text';
	public $ignore_link_text   = 'ignore_link_text';
	public $unignore_link_text = 'unignore_link_text';
	public $how_to_fix         = 'how_to_fix';
	public $how_to_fix_lite    = 'how_to_fix_lite';
	public $fix_confirmation   = 'fix_confirmation';
	public $upsell_text        = 'upsell_text';
	public $text               = array();
	public $revert_text;
	public $ignore_text;
	public $unignore_text;
	public $text_domain = 'onecom-wp';
	public $fix_text;
	public $ignore_critical_text;
	public $status_text;
	public $status_desc     = 'status_desc';
	public $status_resolved = 0;
	public $status_open     = 1;
	public $hm_description;
	public $hm_description_premium;
	public $ignored_lite_text;
	public $get_started;
	public $upgrade_modal_text = array();
	public $open_modal_link    = '';
	public $change_key;
	public $save_key;
	public $quick_fix_messages = array();
	public $table_prefix;
	public $php_version = '8.2';
	public $recommended_php_version ;

	public function init_trait() {
		$this->change_key             = __( 'Change', 'onecom-wp' );
		$this->save_key               = __( 'Save', 'onecom-wp' );
		$this->revert_text            = __( 'Revert', 'onecom-wp' );
		$this->ignore_text            = __( 'Always ignore', 'onecom-wp' );
		$this->unignore_text          = __( 'Unignore', 'onecom-wp' );
		$this->fix_text               = __( 'How to fix', 'onecom-wp' );
		$this->ignore_critical_text   = __( 'Ignore for 24 hours', 'onecom-wp' );
		$this->status_text            = __( 'Status', 'onecom-wp' );
		$this->hm_description         = __( 'Health Monitor lets you monitor the essential security and performance checkpoints and fix them if needed.', 'onecom-wp' );
		$this->hm_description_premium = __( 'Monitor essential security and performance checkpoints, and fix them if needed. With the Pro version, you get the quick fix, ignore, and more functionalities.', 'onecom-wp' );
		$this->ignored_lite_text      = __( 'Get access to ignore functionality and more for free.', 'onecom-wp' );
		$this->get_started            = __( 'Get started', 'onecom-wp' );
		$this->open_modal_link        = '<a  class="onecom__open-modal gv-button gv-button-neutral gv-ml-md gv-max-mob-ml-0"><span>' . __( 'Free upgrade', 'onecom-wp' ) . '</span><gv-icon class="gv-max-mob-hidden" src="' . GROUPONE_WP_URL . '/assets/images/open_in_new.svg" alt="info"></a>';
		global $wpdb;
		$this->table_prefix = $wpdb->prefix ?? '$prefix_';
		$this->recommended_php_version = phpversion();
		$this->init_texts();
		$this->init_fix_messages();
	}

	public function init_texts() {
		$this->text['uploads_index']       = array(
			$this->action_title     => __( 'Reduce the amount of files in the uploads folder', 'onecom-wp' ),
			$this->overview         =>  __( 'When you upload an image, video or other files to your WordPress Media library, they are saved in the /wp-content/uploads/ folder on your web space. In addition, WordPress adds information about the uploads in the wp_posts and wp_postmeta tables in your database. If you upload a lot of files, the uploads folder can take up so much disk space that it slows down your site. In the worst case, it could affect the performance of our server and force us to temporarily suspend the website.', 'onecom-wp' ),
			$this->fix_button_text  => '',
			$this->how_to_fix       => sprintf( __( 'Reduce the size of the uploads folder by cleaning up your Media Library in WP Admin. Check out %sour guide%s to learn more about the WordPress Media Library and how to clean it up.', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/en-us/articles/4402376353425-Clean-up-the-WordPress-media-library">', '</a>' ),
			$this->how_to_fix_lite  => '',
			$this->fix_confirmation => '',
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! Your uploads folder is optimal.', 'onecom-wp' ),
				$this->status_open     => __( 'Your uploads folder is too big:', 'onecom-wp' ),
			),
		);
		$this->text['options_table_count'] = array(
			$this->action_title     => __( 'Clean your options table', 'onecom-wp' ),
			$this->overview         => __( 'Whenever you install a plugin or change a setting, data is added to your options table. Over time, this file may accumulate a lot of unnecessary data, which can slow down your site.', 'onecom-wp' ),
			$this->fix_button_text  => '',
			$this->how_to_fix       => sprintf( __( 'Reduce the size of the "%s_options" table by deleting obsolete data in phpMyAdmin. %sFollow our guide%s for instructions. If you need help, contact our support.', 'onecom-wp' ), $this->table_prefix, '<a href="https://help.one.com/hc/en-us/articles/360012045457-How-to-optimise-the-WordPress-database" target="_blank">', '</a>' ),
			$this->how_to_fix_lite  => '',
			$this->fix_confirmation => '',

			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! Your options table is optimal.', 'onecom-wp' ),
				$this->status_open     => __( 'The options table in your database contains more than 20000 rows and needs to be cleaned.', 'onecom-wp' ),
			),
		);
		$this->text['staging_time']        = array(
			$this->action_title     => __( 'Delete old staging websites', 'onecom-wp' ),
			$this->overview         => __( "A staging site is a copy of your website that is used for testing. Even though it isn't visible to your customers, the staging site is still available online, which makes it a potential target for hackers. That's why we recommend deleting old staging sites when you no longer need them.", 'onecom-wp' ),
			$this->how_to_fix       => __( 'In WP Admin, go to the Staging section of the one.com plugin and delete your old staging sites.', 'onecom-wp' ),
			$this->how_to_fix_lite  => '',
			$this->fix_confirmation => '',
			$this->fix_button_text  => __( 'Go to Staging section', 'onecom-wp' ),

			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! You don’t have old staging websites.', 'onecom-wp' ),
				$this->status_open     => __( "We've detected one or more staging sites on your web space which haven't been used in the past six months.", 'onecom-wp' ),
			),
		);
		$this->text['backup_zip']          = array(
			$this->action_title     => __( 'Remove backups from your web space', 'onecom-wp' ),
			$this->overview         => __( 'We detected one or more .zip files on your web space. They were likely created as backups. If you no longer need these files, we recommend deleting them or saving them somewhere else. Zip files can be downloaded by anyone and pose a potential security risk.', 'onecom-wp' ),
			$this->how_to_fix       => __( 'Remove the .zip files listed above from your web space and save them in another location if you still need them.', 'onecom-wp' ),
			$this->how_to_fix_lite  => '',
			$this->fix_confirmation => __( 'File %s deleted', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Fix by deleting', 'onecom-wp' ),

			$this->status_desc      => array(
				$this->status_resolved => __( 'No old .zip files (backups) detected.', 'onecom-wp' ),
				$this->status_open     => __( 'We detected the following .zip files on your web space. They were likely created as backups.', 'onecom-wp' ),
			),
		);
		$this->text['performance_cache']   = array(
			$this->action_title     => __( 'Activate Performance cache', 'onecom-wp' ),
			$this->overview         => __( 'When the Performance Cache is active your website loads a lot faster. The Varnish server saves a cached copy of your website, which will then be shown to your next visitors. <br/>This is especially useful if you have a lot of visitors. It may also help to improve your SEO ranking. If you would like to learn more, please read our help article: <a href="https://help.one.com/hc/en-us/articles/360000080458" target="_blank">How to use the One.com Performance Cache for WordPress</a>.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Activate Performance Cache', 'onecom-wp' ),
			$this->how_to_fix       => __( 'Activate the Performance Cache to improve the loading time of your site.', 'onecom-wp' ),
			$this->how_to_fix_lite  => '', //__( 'Go to the <a target="_blank" href="' . admin_url( 'admin.php?page=onecom-wp-plugins' ) . '">Plugins section</a> of the one.com plugin and make sure one.com Performance Cache plugin is installed and Cache activated.', 'onecom-wp' ),
			$this->fix_confirmation => __( 'Performance cache enabled.', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Performance cache is enabled', 'onecom-wp' ),
				$this->status_open     => __( 'Performance Cache is inactive.', 'onecom-wp' ),
			),
		);
		$this->text['updated_long_ago']    = array(
			$this->action_title    => __( 'Use compatible plugins', 'onecom-wp' ),
			$this->overview        => __( 'If a plugin is not compatible with the last two major WordPress versions, we recommend deleting it and using an alternative plugin instead.', 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix      => __( 'Delete  incompatible plugins and replace them with alternative plugins with similar functions.', 'onecom-wp' ),
			$this->upsell_text     => __( 'Need help? Upgrade to one.com Managed WordPress for free and get specialised WordPress Support.', 'onecom-wp' ) . '<a  class="onecom__open-modal gv-button gv-button-neutral gv-ml-md gv-max-mob-ml-0"><span>' . __( 'Free upgrade', 'onecom-wp' ) . '</span><gv-icon class="gv-max-mob-hidden" src="' . GROUPONE_WP_URL . '/assets/images/open_in_new.svg" alt="info"></a>',
			$this->how_to_fix_lite => __( 'Delete  incompatible plugins and replace them with alternative plugins with similar functions.', 'onecom-wp' ),
			$this->status_desc     => array(
				$this->status_resolved => __( 'All installed plugins are compatible with the last two major releases of WordPress.', 'onecom-wp' ),
				$this->status_open     => __( 'The following plugins are not compatible with the last two major WordPress versions:', 'onecom-wp' ),
			),
		);
		$this->text['pingbacks']           = array(
			$this->action_title     => __( 'Turn off pingbacks and trackbacks', 'onecom-wp' ),
			$this->overview         => __( 'Pingbacks and trackbacks were meant to notify sites of backlinks, which means when content on their site is being linked to from another site. However, they are nowadays mostly used for spam. Turning them off reduces spam without affecting SEO.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Turn off pingbacks', 'onecom-wp' ),
			$this->how_to_fix       => __( 'Turn off pingbacks and trackbacks to reduce spam.', 'onecom-wp' ),
			$instruction_intro = __('Go to WordPress admin > Settings > Discussion and uncheck the boxes that say', 'onecom-wp'),

		$instruction_first = '<li>' . __('Attempt to notify any blogs linked to from the post', 'onecom-wp') . '</li>',

		$instruction_second = '<li>' . __('Allow link notifications from other blogs (pingbacks and trackbacks) on new posts', 'onecom-wp') . '</li>',
			$this->how_to_fix_lite  => $instruction_intro . '<ol>' . $instruction_first . $instruction_second . '</ol>' ,
			$this->fix_confirmation => __( 'You have successfuly disabled pingbacks and trackbacks.', 'onecom-wp' ),
			$this->upsell_text      => '<span>' . __( 'Fix this in one click with one.com\'s Managed WP add-on. Including 1-click fixes, automatic updates, and more, Managed WP helps you keep your website secure and save time.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
			$this->fix_confirmation => __( 'Pingbacks are disabled.', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Pingbacks are disabled.', 'onecom-wp' ),
				$this->status_open     => __( 'Pingbacks are active on your site.', 'onecom-wp' ),
			),
		);
		$this->text['logout_duration']     = array(
			$this->action_title     => __( 'Logout duration', 'onecom-wp' ),
			$this->overview         => __( 'By default, WordPress allows users to be logged in for 14 days. This can create security issues if a User logs in on a public computer and forgets to logout. To prevent this, you can reduce the duration for which a user session is remembered.', 'onecom-wp' ),
			$this->fix_button_text  => sprintf( __( 'Change logout time to %s hours', 'onecom-wp' ), '4' ),
			$this->fix_confirmation => sprintf( __( 'Logout time changed to %s hours', 'onecom-wp' ), '4' ),
			$this->how_to_fix       => __( 'Click on fix now below', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'You are using optimal logout duration.', 'onecom-wp' ),
				$this->status_open     => __( 'You are using the default login expiration.', 'onecom-wp' ),
			),
		);
		$this->text['xmlrpc']              = array(
			$this->action_title     => __( 'Deactivate XML-RPC if you don’t need it', 'onecom-wp' ),

			$this->overview         => __( "XML-RPC is an old feature that allows WordPress to communicate with other systems. It's still used by the Jetpack plugin and the WordPress mobile application. Unless you're using either of these features, we recommend deactivating XML-RPC, as it can make your site more vulnerable to cyber attacks.", 'onecom-wp' ),
			$this->fix_button_text  => __( 'Deactivate XML-RPC', 'onecom-wp' ),
			$this->how_to_fix       => __( 'If you’re not using the Jetpack plugin or the WordPress mobile application, click the button below to deactivate XML-RPC.', 'onecom-wp' ),
			$this->how_to_fix_lite  => sprintf( __( 'If you’re not using the Jetpack plugin or the WordPress mobile application, deactivate XML-RPC by pasting the following code snippet in your .htaccess file. Read more about how to find and edit this file in %sour guide%s.', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/en-us/articles/115005586169-What-is-htaccess">', '</a>' ) . '<code>
<p>#one.com block xmlrpc</p>
<p>&lt;Files xmlrpc.php&gt;</p>
<p>order deny,allow</p>
<p>deny from all</p>
<p>&lt;/Files&gt;</p>
<p>#one.com block xmlrpc END</p></code>',
			$this->fix_confirmation => __( 'XML RPC disabled.', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! XML-RPC is not active on your site.', 'onecom-wp' ),
				$this->status_open     => __( 'XML-RPC is currently active on your site, making it less secure. Hackers can exploit XML-RPC to target your site with brute force and DDos attacks.', 'onecom-wp' ),
			),
			$this->upsell_text      => '<span>' . __( 'Fix this in one click with one.com\'s Managed WP add-on. Including 1-click fixes, automatic updates, and more, Managed WP helps you keep your website secure and save time.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
		);
		$this->text['spam_protection']     = array(
			$this->action_title     => __( 'Install a spam protection plugin', 'onecom-wp' ),
			$this->overview         => __( 'Unprotected forms on your website are the biggest source of spam registrations and comments. We recommend using a spam protection plugin to save time in handling spam manually.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Install the plugin', 'onecom-wp' ),
			$this->how_to_fix       => __( 'Install and activate the one.com Spam Protection plugin.', 'onecom-wp' ),
			$this->how_to_fix_lite  => sprintf(__( 'Install and activate a spam protection plugin. See the options in our %srecommended%s plugins section.','onecom-wp'),'<a target="_blank" href="' . admin_url( 'admin.php?page=onecom-wp-plugins&tab=recommended' ) . '">','</a>'),
			$this->fix_confirmation => __( 'one.com spam plugin is now installed and activated.', 'onecom-wp' ),
			$this->upsell_text      => '<span>' . __( 'Protect your website with one.com\'s Managed WP add-on, which includes a Spam Protection plugin, automatic updates, and more.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! A spam protection plugin is active on this site.', 'onecom-wp' ),
				$this->status_open     => __( "Your website doesn’t have an active spam protection plugin.", 'onecom-wp' ),
			),
		);
		$this->text['login_attempts']      = array(
			$this->action_title     => __( 'Limit failed logins', 'onecom-wp' ),
			$this->overview         => __( 'By default, WordPress allows unlimited login attempts. Hackers can exploit this by using automated scripts to guess your username and password. You can block login attempts after multiple failed entries to prevent this.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Activate Spam Protection', 'onecom-wp' ),
			$this->upsell_text      => '<span>' . __( 'Protect your website with one.com\'s Managed WP add-on, which includes a Spam Protection plugin, automatic updates, and more.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
			$this->how_to_fix       => __( 'Activate the Spam Protection plugin and block users from trying to log in after multiple failed login attempts.', 'onecom-wp' ),
			$this->how_to_fix_lite  =>  __( 'Install a plugin, such as Spam Protection (included in Managed WP) or Login Lockdown to block users after multiple failed login attempts.', 'onecom-wp' ),
			$this->fix_confirmation => __( 'Failed login attempts limited', 'onecom-wp' ),

			$this->status_desc      => array(
				$this->status_resolved => __( 'Failed login attempts are limited.', 'onecom-wp' ),
				$this->status_open     => __( 'There is no limit on failed login attempts.', 'onecom-wp' ),
			),
		);
		$this->text['login_recaptcha']     = array(
			$this->action_title     => __( 'Protect your login-form', 'onecom-wp' ),
			$this->overview         => __( 'By default, WordPress does not have any feature to protect the login form against brute force attacks.<br/>To address this, you can use Google reCaptcha in login form.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Enable reCaptcha', 'onecom-wp' ),
			$this->how_to_fix       => __( "The login form can be protected by entering Site key and Site secret obtained from <a target='_blank' href='https://www.google.com/recaptcha/admin/create'>Google's dashboard</a>.<br/>Go to Google ReCaptcha Dasboard and follow these steps:", 'onecom-wp' ) . '<ol><li>' . __( "Get the Site key and Site secret from <a target='_blank' href='https://www.google.com/recaptcha/admin/create'>Google's ReCaptcha Dashboard</a>.", 'onecom-wp' ) . '</li><li>' . __( 'Click Enable reCaptcha below.', 'onecom-wp' ) . '</li><li>' . __( 'Enter the Site key and Site secret values and click enter', 'onecom-wp' ) . '</li></ol>',
			$this->how_to_fix_lite  => __( 'You can install a suitable plugin from WordPress plugin repo to fix this', 'onecom-wp' ),
			$this->fix_confirmation => __( 'Login form protected with reCaptcha', 'onecom-wp' ),
			$this->upsell_text      => __( 'one.com Managed WordPress comes with login protection included and more.', 'onecom-wp' ) . $this->open_modal_link,
			$this->status_desc      => array(
				$this->status_resolved => __( 'Your login form is protected.', 'onecom-wp' ),
				$this->status_open     => __( 'Your login form is unprotected', 'onecom-wp' ),
			),
		);
		$this->text['asset_minification']  = array(
			$this->action_title    => __( 'Asset minification Title', 'onecom-wp' ),
			$this->overview        => '',
			$this->fix_button_text => '',

			$this->status_desc     => array(
				$this->status_resolved => '',
				$this->status_open     => '',
			),
		);
		$this->text['php_updates']         = array(
			$this->action_title => __( 'Update to latest PHP version', 'onecom-wp' ),
			$this->overview     => __( "To keep your site fast and secure, it's important to use the recommended PHP version. Newer versions offer enhanced security features and improved performance that help your website run more efficiently. That's why one.com updates PHP regularly and encourages updating to current versions.", 'onecom-wp' ),
			$this->how_to_fix   => sprintf( __( 'You can update PHP from the one.com control panel, under PHP & Database - MariaDB. Check our guide for more information: <a target="_blank" href="https://help.one.com/hc/en/articles/360000449117-How-do-I-update-PHP-for-my-WordPress-site-">How do I update PHP for my WordPress site?</a>', 'onecom-wp' ), '<a href="' . GROUPONE_CP_LOGIN_URL . '" target="_blank">', '</a>', '<a target="_blank" href="https://help.one.com/hc/en/articles/360000449117-How-do-I-update-PHP-for-my-WordPress-site-">', '</a>' ),

			$this->status_desc  => array(
				$this->status_resolved => sprintf(__( 'Your site uses the recommended PHP version %s.', 'onecom-wp' ),$this->recommended_php_version),
				$this->status_open     => sprintf(__( 'Your site doesn’t use the recommended PHP version %s.', 'onecom-wp' ),$this->php_version),
			),
		);
		$this->text['plugin_updates']      = array(
			$this->action_title    => __( 'Update plugins', 'onecom-wp' ),
			$this->overview        => __( 'Plugins that are not updated to the latest version can make your site vulnerable to attacks. We recommend keeping your plugins up to date and  removing any plugins that you don’t use.', 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix      => sprintf( __( "Update your plugins to their latest version on the %sPlugins%s page in WP Admin, and uninstall any plugins you don't need.", 'onecom-wp' ), '<a target="_blank" href="' . admin_url( 'plugins.php' ) . '">', '</a>' ),
			$this->status_desc     => array(
				$this->status_resolved => __( 'Good work! All your plugins are up to date.', 'onecom-wp' ),
				$this->status_open     => __( 'These plugins are not up to date:', 'onecom-wp' ),
			),
		);
		$this->text['theme_updates']       = array(
			$this->action_title    => __( 'Update theme(s)', 'onecom-wp' ),
			$this->overview        => __( 'Just like WordPress core and plugins, themes need to be updated regularly. Outdated themes can become incompatible with the newest WordPress version and vulnerable to hackers. We recommend  recommend keeping your themes up to date and uninstalling any themes that you don’t need.', 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix      => sprintf( __( "Update you themes to their latest version on the %sThemes%s page in WP Admin, and uninstall any themes you don't need.", 'onecom-wp' ), '<a target="_blank" href="' . admin_url( 'themes.php' ) . '">', '</a>' ),

			$this->status_desc     => array(
				$this->status_resolved => __( 'All your themes are up to date.', 'onecom-wp' ),
				$this->status_open     => __( 'These themes are not up to date', 'onecom-wp' ),
			),
		);
		$this->text['inactive_plugins']    = array(
			$this->action_title    => __( 'Delete inactive plugins', 'onecom-wp' ),
			$this->overview        => __( 'Inactive plugins may contain vulnerabilities that hackers could exploit. We recommend deleting any plugins that you don’t need.', 'onecom-wp' ),
			$this->fix_button_text => __( 'Manage inactive plugins', 'onecom-wp' ),
			$this->how_to_fix      => __( "Go to 'Plugins' in the left-hand menu and delete any plugins that you don't need. You can see all unused plugins in the 'Inactive' tab.", 'onecom-wp' ),
			$this->how_to_fix_lite => __( "Go to 'Plugins' in the left-hand menu and delete any plugins that you don't need. You can see all unused plugins in the 'Inactive' tab.", 'onecom-wp' ),

			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! Your site has no inactive plugins.', 'onecom-wp' ),
				$this->status_open     => __( 'We detected one or more inactive plugins on this site:', 'onecom-wp' ),
			),
		);

		$this->text['inactive_themes'] = array(
			$this->action_title    => __( 'Remove inactive themes', 'onecom-wp' ),
			$this->overview        => __( "Inactive themes may contain vulnerabilities that hackers could exploit. To improve your site’s security, we recommend deleting any themes that you don’t need, except for the default WordPress themes (called 'Twenty Twenty-Three' and similar) and the theme you're currently using.", 'onecom-wp' ),
			$this->fix_button_text => __( 'Manage themes', 'onecom-wp' ),
			$instruction_1 = __( 'Go to “Appearance” > “Themes” in the left-hand menu.', 'onecom-wp' ),
			$instruction_2 = __( 'Hover over the theme you want to delete and select \'Theme Details\'.', 'onecom-wp' ),
			$instruction_3 = __( 'Click "Delete" in the bottom-right corner.', 'onecom-wp' ),
			$this->how_to_fix      => sprintf(
				'<ol><li>%s</li><li>%s</li><li>%s</li></ol>',
				$instruction_1,
				$instruction_2,
				$instruction_3
			),
			$this->how_to_fix_lite => sprintf(
				'<ol><li>%s</li><li>%s</li><li>%s</li></ol>',
				$instruction_1,
				$instruction_2,
				$instruction_3
			),

			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! Your site has no inactive themes.', 'onecom-wp' ),
				$this->status_open     => __( 'We detected one or more inactive themes on this site:', 'onecom-wp' ),
			),
		);
		$this->text['wp_updates']    = array(
			$this->action_title    => __( 'Update WordPress to the latest version', 'onecom-wp' ),
			$this->overview        =>  __( 'Due to its popularity, WordPress is a popular target for hackers. WordPress updates often include security fixes which are listed for everyone in the update’s release notes. If your WordPress installation is not updated to the latest version, it may include known vulnerabilities which hackers could exploit.', 'onecom-wp' ) ,
			$this->fix_button_text => '',
			$this->how_to_fix      => str_replace( '\n', '', sprintf( __( 'Update WordPress to the latest version. Minor updates are especially important as they usually include security fixes. Read our guide for instructions: %sHow do I update a CMS like WordPress?%s', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/en/articles/360001621938-How-do-I-update-a-CMS-like-WordPress-and-Joomla-">', '</a>' ) ),
			$this->how_to_fix_lite => str_replace( '\n', '', sprintf( __( 'Update WordPress to the latest version. Minor updates are especially important as they usually include security fixes. Read our guide for instructions: %sHow do I update a CMS like WordPress?%s', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/en/articles/360001621938-How-do-I-update-a-CMS-like-WordPress-and-Joomla-">', '</a>' ) ),
			$this->status_desc     => array(
				$this->status_resolved => __( 'You are using the latest WordPress version', 'onecom-wp' ),
				$this->status_open     => __( "You are not using the latest WordPress version. This can make your site vulnerable hackers.", 'onecom-wp' ),
			),
		);
		$this->text['wp_connection'] = array(
			$this->action_title    => __( 'Connect to wordpress.org to get update information', 'onecom-wp' ),
			$this->overview        => __( 'Your site is unable to connect to wordpress.org and receive the latest update information. This can compromise your site’s security.', 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix      => sprintf( __( "Deactivate all plugins and themes and run a new scan to check if the connection to wordpress.org  is restored. If this worked, reactivate your plugins one by one to find out which one caused the issue. If this didn't work, %splease contact our support%s.", 'onecom-wp' ), "<a target='_blank'  href='https://help.one.com/hc/en-us'>", '</a>' ),
			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! Your site is connected to wordpress.org.', 'onecom-wp' ),
				$this->status_open     => __( 'Your site’s connection to wordpress.org  failed, and the latest update information cannot be fetched.', 'onecom-wp' ),
			),
		);
		$this->text['core_updates']  = array(
			$this->action_title    => __( 'Activate automatic minor core updates', 'onecom-wp' ),
			$this->overview        => __( 'Minor WordPress core updates can include important security patches, without which your site may be more vulnerable to hackers. Activate automatic minor core updates to make sure that your site is always up to date.', 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix      => sprintf(
				__( 'Activate automatic minor WordPress core updates again, either by changing the settings in the plugin you use to manage updates, or in the wp-config file. For more information, read our guide: %sWhy you should always update WordPress%s.', 'onecom-wp' ),
				"<a target='_blank' href=" . $this->get_supported_locales() . '>',
				'</a>'
			),

			$this->how_to_fix_lite => sprintf(
				__( 'Activate automatic minor WordPress core updates again, either by changing the settings in the plugin you use to manage updates, or in the wp-config file. For more information, read our guide: %sWhy you should always update WordPress%s.', 'onecom-wp' ),
				"<a target='_blank' href=" . $this->get_supported_locales() . '>',
				'</a>'
			),
			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! Automatic minor core updates are active for your site.', 'onecom-wp' ),
				$this->status_open     => __( 'Automatic minor core updates are not active for your site. This can make it vulnerable to hackers.', 'onecom-wp' ),
			),
		);

		$this->text['ssl']                  = array(
			$this->action_title    => __( 'Use a valid SSL certificate', 'onecom-wp' ),
			$this->overview        => __('An SSL certificate encrypts the connection between your browser and the server, protecting your visitor’s data against hackers and showing them that your site is trustworthy.', 'onecom-wp')
				. sprintf( __( "All domains hosted with %sone.com%s include an SSL certificate, so this state means that something is wrong with its configuration.", 'onecom-wp' ), "<a target='_blank' href='https://www.one.com'>", '</a>' ),

			$this->fix_button_text => '',
			$this->how_to_fix      => sprintf(__( 'Please %scontact our support%s, so we can check what is wrong and fix it.', 'onecom-wp' ),'<a href="https://help.one.com/hc/en-us" target="_blank">', '</a>'),
			$this->how_to_fix_lite => sprintf( __( 'Please %scontact our support%s, so we can check what is wrong and fix it.', 'onecom-wp' ), '<a href="https://help.one.com/hc/en-us" target="_blank">', '</a>' ),
			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! Your site’s SSL certificate is working. ', 'onecom-wp' ),
				$this->status_open     => __( "Your site doesn't have a working SSL certificate.", 'onecom-wp' ),
			),
		);
		$this->text['file_execution']       = array(
			$this->action_title     => __( 'Prevent file execution in uploads folder', 'onecom-wp' ),
			$this->overview         => __( "Your WordPress site’s images and other media files are stored in the uploads folder. If file execution is allowed in the uploads folder, hackers could abuse it by uploading and executing malware. Prevent this by blocking file execution in this folder.", 'onecom-wp' ),
			$this->fix_button_text  => __( 'Protect uploads folder', 'onecom-wp' ),
			$this->fix_confirmation => __( 'Uploads folder is protected', 'onecom-wp' ),
			$this->how_to_fix       => __( 'Block file execution in the uploads folder by clicking the button below.', 'onecom-wp' ),
			$this->how_to_fix_lite  => sprintf(__( 'Block file execution in the uploads folder by following %sour guides%s.', 'onecom-wp'),'<a target="_blank" href="https://help.one.com/hc/en/articles/360002102258-Disable-file-execution-in-the-WordPress-uploads-folder">','</a>' ),
			$this->upsell_text      => '<span>' . __( 'Fix this in one click with one.com\'s Managed WP add-on. Including 1-click fixes, automatic updates, and more, Managed WP helps you keep your website secure and save time.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! File execution is blocked in your uploads folder. This prevents hackers from exploiting it.', 'onecom-wp' ),
				$this->upsell_text     => __( 'one.com Managed WordPress comes with an easy fix and more.<br/><a>Free Upgrade</a>', 'onecom-wp' ),
				$this->status_open     => __( 'File execution is allowed in your uploads folder, making it vulnerable to hackers.', 'onecom-wp' ),
			),
		);
		$this->text['file_permissions']     = array(
			$this->action_title    => __( 'Reduce File Permissions as recommended by wordpress.org', 'onecom-wp' ),
			$this->overview        => __( 'File and folder permissions define what different users can do on your site. While too strict permissions can cause issues on your site, too loose ones may allow hackers to exploit your files. Prevent this by using the file permissions recommended by wordpress.org.', 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix_lite => sprintf( __( 'To fix this, use an FTP client to change the permissions of your files to 644, and of your folders to 755. Read  %sour guide%s for step-by-step instructions.', 'onecom-wp' ), '<a href="https://help.one.com/hc/en-us/articles/360002087097-Change-the-file-permissions-via-an-FTP-client" target="_blank">', '</a>' ),
			$this->how_to_fix      => sprintf( __( 'To fix this, use an FTP client to change the permissions of your files to 644, and of your folders to 755. Read  %sour guide%s for step-by-step instructions.', 'onecom-wp' ), '<a href="https://help.one.com/hc/en-us/articles/360002087097-Change-the-file-permissions-via-an-FTP-client" target="_blank">', '</a>' ),

			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! Your site uses the recommended file permissions.', 'onecom-wp' ),
				$this->status_open     => __( 'Your site does not use the file permissions recommended by wordpress.org.', 'onecom-wp' ),
			),
		);
		$this->text['DB']                   = array(
			$this->action_title    => __( 'Some title', 'onecom-wp' ),
			$this->overview        => __( 'Some overview', 'onecom-wp' ),
			$this->fix_button_text => __( 'Fix', 'onecom-wp' ),

			$this->status_desc     => array(
				$this->status_resolved => __( 'Resolved', 'onecom-wp' ),
				$this->status_open     => __( 'Open', 'onecom-wp' ),
			),
		);
		$this->text['file_edit']            = array(
			$this->action_title    => __( 'Disallow file editing', 'onecom-wp' ),
			$this->overview        => __( "If file editing is allowed, administrators can edit the code of themes and plugins directly in WP Admin. Editing the code in a wrong way can cause issues on your website, and if your website gets hacked while file editing is allowed, the hacker could access all your data. That's why we recommend disallowing this feature.", 'onecom-wp' ),
			$this->fix_button_text => '',
			$this->how_to_fix_lite => sprintf( __( 'Disallow file editing in WP Admin by adding a line to your wp-config.php file. Read step-by-step instructions from our %sguide%s.', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/articles/360002104398">', '</a>' ),
			$this->how_to_fix      => sprintf( __( 'Disallow file editing in WP Admin by adding a line to your wp-config.php file. Read step-by-step instructions from our %sguide%s.', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/articles/360002104398">', '</a>' ),
			$this->status_desc     => array(
				$this->status_resolved => __( 'Great! File editing in WP admin is not allowed.', 'onecom-wp' ),
				$this->status_open     => __( 'File editing in WP admin is allowed for your site, making it less secure.', 'onecom-wp' ),
			),
		);
		$vulnerable_usernames = [ 'admin', 'user', 'usr', 'wp', 'wordpress' ];
		$current_user         = wp_get_current_user();
		$how_to_fix_usernames = __( 'Change from a common username to a personal one, for example, based on your name or nickname.', 'onecom-wp' ) . ' ' . __( 'Ask the other users listed above to do the same.', 'onecom-wp' );
		if ( ! in_array( $current_user->user_login, $vulnerable_usernames, true ) ) {
			$how_to_fix_usernames = __( 'Ask these users to change from a common username to a personal one, for example, based on their name or nickname.', 'onecom-wp' );
		}

		$this->text['usernames']            = array(
			$this->action_title     => __( 'Use a personal username', 'onecom-wp' ),
			$this->overview         => __( 'Common usernames like “admin” are easy for hackers to guess. They use bots that try millions of password combinations - a personal username makes it much harder to break into your site.', 'onecom-wp' ),
			$this->how_to_fix       => $how_to_fix_usernames,
			$this->fix_button_text  => 'Change username',
			$this->fix_confirmation => __( 'User name is changed', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! You are using personal usernames for your login.', 'onecom-wp' ),
				$this->status_open     => __( 'The following username(s) for this WordPress installation are easy to guess:', 'onecom-wp' ),
			),
		);
		$this->text['dis_plugin']           = array(
			$this->action_title     => __( "You're using a plugin which we advice against", 'onecom-wp' ),
			$this->overview         => sprintf( __( 'Certain plugins can harm your website by making it slower or vulnerable to hackers, or because they don’t work as intended. That’s why we recommend deactivating them. You can find more information on discouraged plugins in %sour guide%s.', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/en/articles/115005586029-Discouraged-WordPress-plugins">', '</a>' ),
			$this->fix_button_text  => __( 'Deactivate plugin(s)', 'onecom-wp' ),
			$this->fix_confirmation => __( 'These plugins are now deactivated:', 'onecom-wp' ),
			$this->how_to_fix       => sprintf( __( 'Go to %sthis page%s to deactivate the plugins listed above.', 'onecom-wp' ), '<a href="' . admin_url( 'admin.php?page=onecom-wp-discouraged-plugins' ) . '">', '</a>' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! You are not using any discouraged plugins.', 'onecom-wp' ),
				$this->status_open     => __( 'You are using one or more plugins that we advise against using:', 'onecom-wp' ),
			),
		);
		$this->text['woocommerce_sessions'] = array(
			$this->action_title     => __( 'Delete expired WooCommerce sessions', 'onecom-wp' ),
			$this->overview         => __( 'When a user puts something in their cart on your site, it’s saved as a session in your database. Normally, these sessions expire and are automatically deleted. If this doesn’t happen, it could be because you\'ve changed the expiration time in your settings.', 'onecom-wp' ). '</br>'.
				__('Over time, this can fill up the woocommerce_sessions table in your database, which can potentially slow down your site. That\'s why we recommend clearing all expired WooCommerce sessions.', 'onecom-wp'),
			$this->fix_button_text  => __( 'Fix now', 'onecom-wp' ),
			$this->how_to_fix       => __( 'Click the "Fix now" button below to delete all expired session data in the woocommerce_sessions table in your database.', 'onecom-wp' ),
			$this->how_to_fix_lite  => sprintf( __( 'Clean expired WooCommerce session data and make sure that expired sessions are scheduled to be cleaned up, and that the scheduled action is working correctly. You can do this in WooCommerce > Status > Scheduled actions. Check %sour guide%s for more information.', 'onecom-wp' ), '<a target="_blank" href="https://help.one.com/hc/en-us/articles/360012045457-How-to-optimise-the-WordPress-database#step-6">', '</a>' ),
			$this->upsell_text      => '<span>' . __( 'Fix this in one click with one.com\'s Managed WP add-on. Including 1-click fixes, automatic updates, and more, Managed WP helps you keep your website secure and save time.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
			$this->fix_confirmation => __( 'The expired woocommerce session data is deleted.', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! The expired WooCommerce session data is now deleted.', 'onecom-wp' ),
				$this->status_open     => __( 'The woocommerce_sessions table in your database contains a lot of expired session data and needs to be cleaned up.', 'onecom-wp' ),
			),
		);
		$this->text['error_reporting']      = array(
			$this->action_title     => __( 'Deactivate error reporting', 'onecom-wp' ),
			$this->overview         => __( "Error messages can be useful for development purposes and troubleshooting. However, they can also give hackers informationa bout vulnerabilities on your site, as well as harm its user experience. We recommend deactivating  error messages when you don’t need them.", 'onecom-wp' ),
			$this->fix_button_text  => __( 'Fix now', 'onecom-wp' ),
			$this->how_to_fix => __('You can deactivate PHP error messages  in the one.com Control Panel, and WordPress debugging in the wp.config.php file. Read more about how to manage these settings:', 'onecom-wp') . '</br>' .
				sprintf(__('%sHow do I enable error messages for PHP?%s', 'onecom-wp'), '<a target="_blank" href="https://help.one.com/hc/en-us/articles/115005593705-How-do-I-enable-error-messages-for-PHP-">', '</a>') . '</br>' .
				sprintf(__('%sHow do I manage debugging in WordPress?%s', 'onecom-wp'), '<a target="_blank" href="https://help.one.com/hc/en-us/articles/115005594045-How-do-I-enable-debugging-in-WordPress-">', '</a>'),
			$this->fix_confirmation => '',
			$this->status_desc      => array(
				$this->status_resolved => __( 'Error reporting is not active on your site, and your visitors cannot  see error messages.', 'onecom-wp' ),
				$this->status_open     => __( 'Error reporting is active on your site, and error messages may be shown to its visitors.', 'onecom-wp' ),
			),
		);

		$this->text['debug_enabled'] = array(
			$this->action_title     => __( 'Deactivate debug mode to hide your error log', 'onecom-wp' ),
			$this->overview         => __( 'Debug mode is active on your website. It can help with troubleshooting by providing information about errors, but the file where this information is collected may be publicly available to all users. A public error log can make your website vulnerable to hackers. To improve security, we recommend deactivating debug mode as soon as you don’t need it anymore, and removing the public error log from your web space ', 'onecom-wp' ),
			$instruction_1 = __( 'Go to your File Manager and open the file called " wp - config.php "', 'onecom-wp' ),
			$instruction_2 = __( "Scroll down to the line that says: define( 'WP_DEBUG_LOG', true ); in that file.", 'onecom-wp' ),
			$instruction_3 = __( "Change it to: define( 'WP_DEBUG_LOG', false ); and then click “Save” at the top of the page.", 'onecom-wp' ),
			$instruction_4 = __( "In File Manager, remove the existing error log from your WordPress site’s directory. It can be found in wp-content/debug.log. If you still need the information in it, copy it to a private location before deleting it from your public wp-content directory.", 'onecom-wp' ),
			$this->how_to_fix       => sprintf( __('Deactivate debug mode if you don’t need it anymore. Read more in our guide: %sHow do I manage debugging in WordPress?%s ', 'onecom-wp'). '</br>' .
				__('If you still need debug mode, deactivate  error logging in this way:','onecom-wp') . '</br>' .
				'<ol><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ol>',
				'<a href="#">','</a>',
				$instruction_1,
				$instruction_2,
				$instruction_3,
				$instruction_4
			),
			$this->fix_confirmation => '',
			$this->status_desc      => array(
				$this->status_resolved => __( 'Debug mode is inactive.', 'onecom-wp' ),
				$this->status_open     => __( 'Debug mode is active on this site, potentially making the error log publicly accessible.', 'onecom-wp' ),
			),
		);
		$this->text['debug_log_size']           = array(
			$this->action_title     => __( 'Delete your debug.log file  to free up disk space', 'onecom-wp' ),
			$this->overview         => __( 'When debug mode is active, your website’s errors can be logged to a file called debug.log. This file can occupy a significant amount of your disk space. We recommend deleting it if you no longer need it.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Delete file', 'onecom-wp' ),
			$this->upsell_text      => __( 'one.com Managed WordPress comes with a quick fix so you can spend more time on your website, less on security', 'onecom-wp' ) . $this->open_modal_link,
			$this->how_to_fix       => __( 'If you don’t need the debug.log file any more, click the "Delete file" button below to delete it.', 'onecom-wp' ),
			$this->how_to_fix_lite  => __( 'Delete the debug.log file using File Manager or an SFTP client. In most cases, the debug.log file can be found in a folder called "wp-content".', 'onecom-wp' ),
			$this->fix_confirmation => 'Debug.log file deleted',
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! You’ve deleted your debug.log file.', 'onecom-wp' ),
				$this->status_open     => __( 'Your debug.log file takes up over 100 MB in disk space.', 'onecom-wp' ),
			),
		);
		$this->text['user_enumeration']         = array(
			$this->action_title     => __( 'Deactivate user enumeration', 'onecom-wp' ),
			$this->overview         => __( "When user enumeration is active, anyone can find out the list of usernames on your website. This makes it more vulnerable to brute force attacks in which hackers try millions of username and password combinations in order to gain access to a site. Deactivate user enumeration to improve your website’s security.", 'onecom-wp' ),
			$this->fix_button_text  => __( 'Deactivate user enumeration', 'onecom-wp' ),
			$this->upsell_text      => '<span>' . __( 'Fix this in one click with one.com\'s Managed WP add-on. Including 1-click fixes, automatic updates, and more, Managed WP helps you keep your website secure and save time.', 'onecom-wp' ) . '</span>' . $this->open_modal_link,
			$this->how_to_fix       => __( 'Deactivate user enumeration by clicking the button below.', 'onecom-wp' ),
			$this->how_to_fix_lite  => sprintf( __( 'Deactivate user enumeration with a plugin such as %sStop User Enumeration%s.', 'onecom-wp' ), '<a target="_blank" href="https://wordpress.org/plugins/stop-user-enumeration/">', '</a>' ),
			$this->fix_confirmation => __( 'User enumeration is disabled.', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Great! User enumeration is not active on your site.', 'onecom-wp' ),
				$this->status_open     => __( 'User enumeration is active on your site, making it more vulnerable to hackers.', 'onecom-wp' ),
			),
		);
		$this->text['optimize_uploaded_images'] = array(
			$this->action_title     => __( 'Optimize uploaded images', 'onecom-wp' ),
			$this->overview         => __( 'By default, WordPress does not optimize images very well. We recommend using the Imagify plugin to increase performance and visitor experience on your website with faster image loading speed.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Go to Imagify', 'onecom-wp' ),
			$this->upsell_text      => '',
			$this->how_to_fix       => ( ! is_plugin_active( 'imagify/imagify.php' ) ) ? __( 'Install & activate the Imagify plugin, go to Imagify settings, and set up the plugin following the instructions on the page.', 'onecom-wp' ) : sprintf( __( 'Go to %sImagify settings%s and set up the plugin following the instructions on the page.', 'onecom-wp' ), '<a target="_blank" href="' . admin_url( 'options-general.php?page=imagify' ) . '">', '</a>' ),
			$this->how_to_fix_lite  => ( ! is_plugin_active( 'imagify/imagify.php' ) ) ? __( 'Install & activate the Imagify plugin, go to Imagify settings, and set up the plugin following the instructions on the page.', 'onecom-wp' ) : sprintf( __( 'Go to %sImagify settings%s and set up the plugin following the instructions on the page.', 'onecom-wp' ), '<a target="_blank" href="' . admin_url( 'options-general.php?page=imagify' ) . '">', '</a>' ),
			$this->fix_confirmation => '',
			$this->status_desc      => array(
				$this->status_resolved => ( is_plugin_active( 'imagify/imagify.php' ) ) ? __( 'Imagify is now set up. The images you upload will be optimized.', 'onecom-wp' ) : __( 'The images you upload will be optimized.', 'onecom-wp' ),
				$this->status_open     => __( 'Imagify is not set up', 'onecom-wp' ),
			),
		);
		$this->text['enable_cdn']               = array(
			$this->action_title     => __( 'Activate CDN', 'onecom-wp' ),
			$this->overview         => __( 'A content delivery network (CDN) is a system of distributed servers that deliver your website content from the server closest to each visitor. This improves loading times, especially for global audiences.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Activate CDN', 'onecom-wp' ),
			$this->upsell_text      => '',
			$this->how_to_fix       => __( 'Activate the CDN to improve loading times.', 'onecom-wp' ),
			$this->how_to_fix_lite  => '', //str_replace('<a target="_blank" href="' . admin_url( 'admin.php?page=onecom-wp-plugins' ) . '">one.com</a>', 'one.com', __( 'Go to the <a target="_blank" href="' . admin_url( 'admin.php?page=onecom-wp-plugins' ) . '">Plugins section</a> of the <a target="_blank" href="' . admin_url( 'admin.php?page=onecom-wp-plugins' ) . '">one.com</a> plugin and make sure one.com Performance Cache plugin is installed and CDN activated.', 'onecom-wp' )),
			$this->fix_confirmation => __( 'CDN is enabled', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'CDN is enabled', 'onecom-wp' ),
				$this->status_open     => __( 'CDN is inactive.', 'onecom-wp' ),
			),
		);
		$this->text['login_protection']         = array(
			$this->action_title     => __( 'Enable one.com Advanced Login Protection', 'onecom-wp' ),
			$this->overview         => __( 'We recommend that you enable the Advanced login Protection in the one.com control panel. This means you won’t need to remember passwords for your WordPress sites and your login will be more protected.', 'onecom-wp' ),
			$this->fix_button_text  => __( 'Go to one.com control panel', 'onecom-wp' ),
			$this->upsell_text      => '',
			$this->how_to_fix       => __( 'Click the button below.', 'onecom-wp' ),
			$this->how_to_fix_lite  => __( 'Click the button below.', 'onecom-wp' ),
			$this->fix_confirmation => __( 'Advanced login protection is enabled.', 'onecom-wp' ),
			$this->status_desc      => array(
				$this->status_resolved => __( 'Advanced login protection is enabled.', 'onecom-wp' ),
				$this->status_open     => __( 'Advanced login protection is disabled.', 'onecom-wp' ),
			),
		);
	}

	public function setStatusDesc( $newStatusDesc ) {
		$this->text['inactive_plugins'][ $this->status_desc ][ $this->status_open ] = $newStatusDesc;
	}

	public function get_text( $check ): array {
		$refined_check = str_replace( 'check_', '', $check );
		return $this->text[ $refined_check ];
	}

	public function init_fix_messages() {
		$this->quick_fix_messages = array(
			'error'   => array(
				'username_invalid'     => __( 'Please enter a valid username', 'onecom-wp' ),
				'username_not_changed' => __( 'User name could not be changed', 'onecom-wp' ),
			),
			'success' => array(
				'username_changed' => __( 'User name is changed', 'onecom-wp' ),
			),
		);
	}

	public function get_supported_locales(): string {
		$locale = get_locale();
		//      $language_part = substr($locale, 0, 2);

		$supported_locales = array(
			'en_US' => 'https://help.one.com/hc/en-us/articles/360000110977-Why-you-should-always-update-WordPress',
			'da_DK' => 'https://help.one.com/hc/da/articles/360000110977-Derfor-skal-du-altid-opdatere-WordPress',
			'de_DE' => 'https://help.one.com/hc/de/articles/360000110977-Warum-Sie-WordPress-immer-aktuell-halten-sollten',
			'es_ES' => 'https://help.one.com/hc/es/articles/360000110977--Por-qu%C3%A9-deber%C3%ADa-mantener-WordPress-siempre-actualizado',
			'fr_FR' => 'https://help.one.com/hc/fr/articles/360000110977-Pourquoi-vous-devez-toujours-mettre-%C3%A0-jour-WordPress',
			'it_IT' => 'https://help.one.com/hc/it/articles/360000110977-Perch%C3%A9-dovresti-sempre-aggiornare-WordPress',
			'pt_PT' => 'https://help.one.com/hc/pt/articles/360000110977-Raz%C3%B5es-para-manter-o-seu-WordPress-atualizado',
			'nl_NL' => 'https://help.one.com/hc/nl/articles/360000110977-Waarom-je-WordPress-altijd-moet-updaten',
			'sv_SE' => 'https://help.one.com/hc/sv/articles/360000110977-Varf%C3%B6r-du-alltid-b%C3%B6r-h%C3%A5lla-WordPress-uppdaterat',
			'fi'    => 'https://help.one.com/hc/fi/articles/360000110977-Miksi-WordPress-kannattaa-aina-p%C3%A4ivitt%C3%A4%C3%A4-uusimpaan-versioon',
			'nb_NO' => 'https://help.one.com/hc/no/articles/360000110977-Hvorfor-du-alltid-b%C3%B8r-oppdatere-WordPress',
		);
		if ( ! array_key_exists( $locale, $supported_locales ) ) {
			// Language not supported, return default locale
			return $supported_locales['en_US'];
		} else {
			// Language is supported
			return $supported_locales[ $locale ];
		}
	}
}
