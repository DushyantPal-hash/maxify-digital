=== one.com themes & plugins ===
Contributors: onecom
Donate link: https://www.one.com/
Requires at least: 4.3
Tested up to: 5.3.2
License: GPL v2 or later

Personalize your website with custom made themes and plugins exclusive to one.com customers. You can also find a curated list of plugins that we recommend.

== Description ==
Personalize your website with custom made themes and plugins exclusive to one.com customers. You can also find a curated list of plugins that we recommend.

Note
This plugin works only on WordPress sites hosted on one.com domain

Support
Contact our support

== Installation ==
Automated Installation
To do an automatic install, log in to your WordPress dashboard, navigate to the Plugins menu, click Add New, search for \"one.com themes and plugins\", and click to install

Manual Alternatives
The manual installation method involves downloading our plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains instructions on how to do this here.

== Changelog ==
# v5.3.5 - 28 September 2026
* Fixed installing wp.org themes from wp-admin > One.com > Themes

# v5.3.4 - 25 September 2026
* Security: the theme and plugin install, activate and dismiss actions now require a valid request token and the matching user permission, and packages can only be installed from one.com and WordPress.org

# v5.3.3 - 22 September 2026
* Restored the WP Rocket compatibility constant for Performance Cache

# v5.3.2 - 21 September 2026
* Enabled notice in wp-admin, with minor reliability and per-user dismiss improvements
* Maintenance Mode: "Let logged-in users see the site" and the countdown timer are on by default again on new sites
* Fixed the "Delete now" button and the one.com menu leading to a Not Found page on multisite

# v5.3.1 - 18 Sept 2026
* fix: Exclude module unit test files to prevent false-positive detections by Wordfence

# v5.3.0 - 26 August 2026
* Performance Cache, Spam Protection and Maintenance Mode are now built into this plugin as features rather than separate plugins
* Added a Settings screen to turn each feature on or off, with an icon and description for each
* The plugin takes over from the old standalone plugins automatically, deactivating them and offering to delete them
* Spam Protection is marked as a Managed WordPress feature and cannot be enabled on other plans
* Removed the duplicate Marketplace shortcut from the admin toolbar

# v5.2.2 - 12 August 2026
* Track MCP ability executions in analytics (item_source: MCP)

# v5.2.1 - 10 Aug 2026
* Validator update

# v5.2.0 - 21 July 2026
* Added one.com MCP server to manage the site with AI assistants
* Manage posts, menus, plugins and themes over MCP
* Install premium add-ons (WP Rocket, Rank Math PRO) over MCP when subscribed

# v5.1.7 - 25 June 2026
* Marketplace awareness and other improvements
* Removed WP Rocket menu from a one.com menu
* Unified marketplace module

# v5.1.6 - 05 June 2026 
* Mixpanel endpoint update
* Sync WP Rocket button status with plugin status
* Fixed - VM close notifications issue
* Fixed - PHP 8.5 compatibility warnings

# v5.1.5 - 16 Apr 2026
* HM - upsell banners revamped
* HM - fixed install imagify action

# v5.1.4 - 07 Apr 2026
* Marketplace - Fixed missing text in Key Features section
* Fixed VM email placeholder text issue

# v5.1.3 - 31 Mar 2026
* Fix PHP 8.5 - boolean cast deprecation warning
* Prevent repurchase of RankMath for multiple domain with the same email

# v5.1.2 - 23 Mar 2026
* one.com submenu deprecation error fix
* Marketplace button - minor improvement
* one.com classic themes are no longer available for selection in the plugin

# v5.1.1 - 18 Mar 2026
* Removed unwanted text "Blocked" from admin pages

# v5.1.0 - 18 Mar 2026
* Revamped remaining HM audits & discouraged plugins page added
* MiniCRM plugin install/update from wp.org
* Feature indicator with Marketplace menu
* Minor UI fixes and improvements

# v5.0.9 - 2 Mar 2026
* Added marketplace promotion banner in vm emails
* Skip sending vm emails when FlowPilot addon is active

# v5.0.8 - 23 Feb 2026
* Improved homepage layout and consent banner placement
* Marketplace and My products menu restructuring
* Added survey bubble in Marketplace
* Minor event tracking updates

# v5.0.7 - 17 Feb 2026
* Health Monitor revamp
* Mail status flag added to VM logs
* Marketplace step added to plugin tour flow, and make gravity compatible
* Marketplace CTA - Added Tooltip and minor CSS improvements

# v5.0.6 - 10 Feb 2026
* Marketplace - Added a delete confirmation modal for plugin deletion
* Added conditional rendering of Explore plugins banner
* Premium WP Care - Improved response handling
* Minor update in events

# v5.0.5 - 03 Feb 2026
* Marketplace - Minor UI enhancements and banner update
* Marketplace - Minor update in events properties

# v5.0.4 - 30 Jan 2026
* Fixed PHP warnings

# v5.0.3 - 27 Jan 2026
* Fixed warnings in REST API response
* Marketplace - Minor improvements and translations update

# v5.0.2 - 16 Jan 2026
* Fixed issue with plugin activation from one.com control panel

# v5.0.1 - 12 Jan 2026
* Marketplace - Minor UI improvements and translation updates
* Marketplace - Plugin activation success banner update
* Cleanup of leftover onboarding files
* Multisite minor fixes

# v5.0.0 - 6 Jan 2026
* New Feature: Marketplace - discover curated products more easily, including a wide selection of free plugins and optional premium ones

# v4.7.5 - 1 Dec 2025
* Network calls timeout increased to 10 seconds

# v4.7.4 - 10 Nov 2025
* Blocked public access to onecom_logs
* Gravity re-design: WP Rocket upsell page

# v4.7.3 - 28 Oct 2025
* Updated translations to use the English name ‘Managed WP' for all languages

# v4.7.2 - 08 Oct 2025
* PHP 8.4 compatibility fixes
* Gravity CSS update
* Minor code improvements

# v4.7.1 - 18 Sept 2025
* Added MiniCRM Bridge in one.com plugins list

# v4.7.0 - 01 Sept 2025
* Added new section to request Premium WP Care
* Added new status messages and enhanced error messages in Staging

# v4.6.7 - 14 Aug 2025
* Moved Rank Math from "Recommended plugins" section to "All" section

# v4.6.6 - 11 Aug 2025
* Added buffer in JWT token

# v4.6.5 - 24 Jul 2025
* Staging redesign
* Replaced one.com menu logo with text for better accessibility
* Fixed WP-Rocket button status using updated API

# v4.6.4 - 07 Jul 2025 
* New theme added in one.com themes
* Fixed ‘Null’ issue with ALP on some custom login pages
* Fixed HM check uploads audit to handle excessive uploads size
* Minor fixes and stats improvements

# v4.6.3 - 26 Jun 2025
* Fixed broken staging site after migration to oneHoP

# v4.6.2 - 19 Jun 2025
* Replace old cdn to s3 cdn

# v4.6.1 - 28 May 2025
* Fixed icons on plugins page
* Misc stats update

# v4.6.0 - 09 May 2025
* Revamped Advance Error Page
* Updated PHP recommended version audit
* Misc translations update and code cleanup

# v4.5.7 - 28 Apr 2025
* Revamped plugins page
* Text formatting improved in CDN HM audit
* Modified VM notifications
* New themes added in one.com themes

# v4.5.6 - 24 Apr 2025
* Added optional preference for sharing anonymous usage data to help improve the plugin
* Translation related warnings fixed

# v4.5.5 - 02 Apr 2025
* Themes page performance improvement
* Purge WP-Optimize cache with Cookie banner consent acceptance
* Added Super Addons plugin in recommended plugin list
* Removed Contact Form 7 from recommended plugin list
* Updated guide link on one.com admin home page
* Background changed in theme previews for better visibility
* Minor fixes and improvements


# v4.5.4 - 18 Mar 2025
* Fixed staging page translations for pt_PT

# v4.5.3 - 28 Feb 2025
* Fixed translation for pt_PT

# v4.5.2 - 07 Feb 2025
* Added stats for themes page
* Misc translations update

# v4.5.1 - 9 Jan 2025
* Added new themes page
* Home layout widescreen support

# v4.5.0 - 6 Dec 2024
* Translation hooks updated as per WP 6.7
* Misc translations update
* Dashboard widget Revamp

# v4.4.9 - 13 Nov 2024
* Repetitive upgrade modal issue fix
* HM Ignored list - html formatting fix
* Minor code structure improvements

# v4.4.8 - 15 Oct 2024
* Code quality fixes and improvements

# v4.4.7 - 14 Aug 2024
* New landing page added as home submenu under one.com menu

# v4.4.6 - 28 June 2024
* Improved user enumeration audit HM and patched it's quick fix
* oneHOP compatibility

# v4.4.5 - 18 June 2024
* Handle the cases in VM where plugins have same version for premium & non-premium versions
* Fixed "Disable user enumeration" audit HM
* Improved logic for "Enable automatic minor core updates" audit HM
* VM mails trigger fix for Auto-update enable

# v4.4.4 - 16 Apr 2024
* Invoke HM scan on WordPress Update & enable scan trigger for Update manager
* Fixed warning on the login screen
* Vulnerability Monitor improvement for theme/plugin detection with capital letter slugs
* Misc translations update

# v4.4.3 - 27 Mar 2024
* Vulnerability Monitor - various fixes and improvements
* Changed VM Email frequency from 24 hours to 7 days 
* VM history log - upgrade banner for non-mwp
* Disable autoload options
* Validator update
* ALP and VM stats update

# v4.4.2 - 12 Mar 2024
* Incorporated limit-login-attempts & limit-login-attempts-reloaded plugins in HM audit Limit login attempts
* Removed - "Enable one.com Advanced Login Protection" audit from HM
* Removed Marketgoo plugin from onecom plugins listing page
* Fixed link in 'Enable automatic minor core updates' HM audit.
* PHPCS - Code Quality improvements
* VM warning fixed for non-supported locales
* Misc text and translations update 

# v4.4.1 - 28 Feb 2024
* Hidden param handling added for plugins page

# v4.4.0 - 09 Feb 2024
* Vulnerability Monitor - History log added
* Vulnerability Monitor scan trigger after auto-update and other improvements
* HM limit login attempts audit fixed
* Multisite not supported banners added

# v4.3.11 - 22 Jan 2024
* Translations and locales fallback update

# v4.3.10 - 04 Jan 2024
* Error loading data in CP - Fixed

# v4.3.9 - 11 Dec 2023
* Vulnerability Monitor - Added "Exploitable" label
* Vulnerability Monitor mailers recipients changed Cc to Bcc
* Vulnerability Monitor - Supporter mails exclusion
* Added error message if API is down
* one.com Plugins list - UI improvements
* Misc stats and translations fix

# v4.3.8 - 18 Oct 2023
* HM improvements
* Translations for recommended plugins

# v4.3.7 - 21 Sep 2023
* New HM Audits Added

# v4.3.6 - 12 Sep 2023
* LearnPress JWT library conflict (Control Panel connection error) fix
* Minor UI improvements
* Code coverage improvements
* PCache auto-install removed

# v4.3.5 - 9 Aug 2023
* Thrive theme whitelisted in HM Delete backups audit
* Fixed CSS @import alert in the performance report

# v4.3.4 - 19 Jul 2023
* Translations update

# v4.3.3 - 05 Jul 2023
* Improved logic for “Use compatible plugins” check in Health Monitor
* Fixed PHP warning & extra space in vulnerability monitor
* Minor improvements with code coverage

# v4.3.2 - 26 Jun 2023
* Advanced login protection - minor fix

# v4.3.1 - 31 May 2023
* Vulnerability detection logic improvement
* VM false core vulnerability notification fix
* VM mobile email layout fix for webmail
* HM Delete Backups check - Fixed
* Misc text, translation and code quality improvements

# v4.3.0 - 12 May 2023
* Added compatibility in authenticator for Learnpress plugin.
* Updated vulnerability monitor subject
* Code quality improvements
* PHP deprecation notice fix for menu
* Health Monitor - last-scan time localisation

# v4.2.8 - 02 May 2023
* PHP 8.2 compatibility fixes
* Fixed error when CVSS score missing in VM scan

# v4.2.7 - 25 Apr 2023
* Vulnerability Monitor revamp launch for all locales

# v4.2.6 - 17 Apr 2023
* Vulnerability Monitor revamp launch for Dutch/Portuguese/Spanish/German locale
* Misc text and translations update

# v4.2.5 - 5 Apr 2023
* Vulnerability Monitor revamp and soft launch for it_IT/fr_FR locale

# v4.2.4 - 23 Mar 2023
* Vulnerability Monitor revamp and soft launch for selected locale

# v4.2.3 - 20 Mar 2023
* Imagify Key check - Fixed
* Removed ALP modal from WP-Admin

# v4.2.2 - 23 Feb 2023
* Vulnerability Monitor - Settings button overlapping fix
* Enable CDN button fix for non-mwp customers in HM
* Plugin menu image size conflict fix with GT3 plugin
* Tooltip class UI conflict fix
* Unified logs

# v4.2.1 - 20 Jan 2023
* Vulnerability monitor improvements
* Health Monitor scan improvements
* JWT library update
* Recommended plugins stats
* 1-click-login fix for multisite
* Code improvements with code coverage
* Misc text and translations update

# v4.2.0 - 15 Dec 2022
* Vulnerability scan on plugin activation
* Dashboard widget redesign
* Vulnerability links update to relevant settings
* Misc text and translations update
* Plugins sub-menu in wp-admin getting affected by other plugin's CSS - Fixed

# v4.1.5 - 14 Dec 2022
* Conflict resolved with Imagify v2.1

# v4.1.4 - 16 Nov 2022
* Improvements in HM
* Improved stats for staging
* False positive for child theme in VM - Fixed
* Nested menu observer console error - Fixed
* Improved vendor path inclusion
* Minor UI fixes

# v4.1.3 - 31 Oct 2022
* CDN hosted fonts URL change
* Change in visibility of upsell banners
* Fixed CP WordPress login when user capabilities for user are blank
* Fixed Expired WooCommerce session data alert in CP

# v4.1.2 - 19th Sep 2022
* Self hosted fonts
* Staging login with trailing site URL fixed

# v4.1.1 - 23rd Aug 2022
* Improvements in HM checks- options table check text
* Fixed - updates check for plugins not hosted on WP

# v4.1.0 - 26th July 2022
* VM Patchstack reference added
* WP Rocket CTA linked to translated guide
* WooCommerce HM check - translation fix

# v4.0.0 - 2nd June 2022
* WP Rocket page integration
* WP Rocket entry in one.com plugins list
* Discouraged plugins - UI Improvement
* Fixed - Cookie banner - CSS conflict with themify-ultra theme
* Fixed - Cookie banner compatibility with WP Rocket
* Translations updates

# v3.0.4 - 23rd May 2022
* API calls enhancement

# v3.0.3 - 13th May 2022
* Updated VM to support new API

# v3.0.2 - 23rd Mar 2022
* Added fixed and affected version in VM
* Health Monitor tile Redesign
* Preventing WP default css override
* Improvements in walkthrough
* Minor translations update

# v3.0.1 - 23rd Feb 2022
* Fix - Root domains 1-click-login and missing plugin

# v3.0.0 - 22nd Feb 2022
* Redesigned admin settings UI
* Walkthrough Update

# v2.0.21 - 3rd Feb 2022
* Timezone change on activation fix
* HM - Prevent file execution - htaccess fix

# v2.0.20 - 10th Jan 2022
* PHP 8.1 compatibility fixes
* Fix ALP interferes with native WP page password protection function

# v2.0.19 - 30th Dec 2021
* Removed onephoto importer shortcut

# v2.0.18 - 17th Dec 2021
* Fatal error fixed because of missing functions on older WP core versions

# v2.0.17 - 7th Dec 2021
* Features access updated for Beginner package
* HM scan fix for plugins missing compatibility info
* one.com migrator - 'view details' link fix
* Nudge ALP for each login session
* Translation update

# v2.0.17 - 7th Dec 2021
* Features access updated for Beginner package
* HM scan fix for plugins missing compatibility info
* one.com migrator - 'view details' link fix
* Nudge ALP for each login session
* Translation update

# v2.0.16 - 11th Nov 2021
* Floating save button for cookie-banner
* Upsell banner text/translation update
* Minor CSS improvements

# v2.0.15 - 21st Oct 2021
* Responsive CSS fixes
* Translation update
* Cookie banner - assets load fix

# v2.0.14 - 5th Oct 2021
* CP to Maintenance mode link - API fix

# v2.0.13 - 28th Sep 2021
* Image optimisation check in Health monitor
* Advanced login protection

# v2.0.12 - 9th Sep 2021
* Health Monitor - string update for file count check
* Added marketgoo seo plugin

# v2.0.11 - 18th Aug 2021
* Send WP admin reset password when disabling Advanced Login Protection
* Fixed "Website not protected banner even though Spam Protection plugin exists"
* marketgoo seo - auto enable

# v2.0.10 - 13th Aug 2021
* CP login URL fixed to navigate to installation specific domain

# v2.0.9 - 10th Aug 2021
* Warning fixed when there is no active plugin

# v2.0.8 - 29th Jul 2021
* Login support for all kind of WP installations
* Fixed "Cannot connect to wordpress.org" fake positive detection

# v2.0.7 - 19th Jul 2021
* Fixed minor styling issues

# v2.0.6 - 16th Jul 2021
* Fixed multiple tags being displayed on Revert

# v2.0.5 - 14th Jul 2021
* Health monitor - Increased upper limit on file count

# v2.0.4 - 09th Jul 2021
* Scheduled Health monitor scans

# v2.0.3 - 08th Jul 2021
* Fast API response

# v2.0.2 - 07th Jul 2021
* Added multisite handling in login masking

# v2.0.1 - 06th Jul 2021
* API POST call fixed for subdirectory installations
* Added outdated themes list while reporting theme updates in Health monitor

# v2.0 - 05th Jul 2021
* Login masking fixes
* CP WP Admin login fixes
* Centralised menu

# v1.0.9 - 02nd Jul 2021
* Login masking for all customers

# v1.0.7 - 24th Jun 2021
* Fixed blocked access to premium themes

# v1.0.6 - 23rd Jun 2021
* API optimisation

# v1.0.5 - 21st Jun 2021
* Enhanced login protection

# v1.0.4 - 17th Jun 2021
* Help guide link added for Vulnerability monitor
* Theme listing menu rearrangement on smaller screens

# v1.0.3 - 15th Jun 2021
* Improved features listing

# v1.0.2 - 15th Jun 2021
* Health monitor widget - Todo count fixed

# v1.0.1 - 15th Jun 2021
* CP dashboard service status fixed

# v1.0.0 - 14th Jun 2021
* Updated MWP features
* Localised Health monitor

# v0.9.19 - 14th Jun 2021
* MWP features

# v0.8.6 - 19th May 2021
* Plugin API added

# v0.8.5 - 10th May 2021
* Fixed Imagify partner ID

# v0.8.4 - 5th May 2021
* Added Imagify partner

# v0.8.3 - 24th Feb 2021
* Staging prefix replacement issues fixed

# v0.8.2 - 22th Jan 2020
* Minor translations update

# v0.8.1 - 20th Jan 2020
* Walkthrough improvement

# v0.8.0 - 28th Dec 2020
* PHP8 compatibility
* dismiss wp-admin notice about spam protection

# v0.7.10
* improved translations

# v0.7.9
* 1-click wp stats
* updated translations
* Added module to modify file security settings

# v0.7.8
* updated thumbnail url of recommended plugins

# v0.7.7
* New badge for plugins and themes
* Retired themes removed

# v0.7.6
* walkthrough one.com features

# v0.7.5
* shortcuts added to wp admin

# v0.7.4
* Additional information on fatal error page

# v0.7.3
* PHP Notice fixed

# v0.7.2
* Spam protection notice

# v0.7.1
* Minor improvements

# v0.7.0
* CS, PL & PTBR translations are being discontinued
* Minor internal fixes

# v0.6.5
* Minor improvements in Cookie banner
* Admin menu icon height fix
* Minor translation update

# v0.6.4
* Remove alternatives from discouraged plugins
* Set the default time format to 24 hours
* Minor UI improvements

# v0.6.3
* Minor text update

# v0.6.2
* Logo size fixed

# v0.6.1
* Logo alignment fixed

# v0.6.0
* Rebranding

# v0.5.9
* Domain identification fixed

# v0.5.8
* Code quality improvement

# v0.5.7
* Minor improvements in validator submodule

# v0.5.6
* Disabled Health monitor widget for user roles other than administrator

# v0.5.5
* Improved log features

# v0.5.4
* Staging link added in toolbar
* Added log entry for theme preview
* Forced uploads directory path in staging 

# v0.5.3
* PHP notice fixed

# v0.5.2
* Premium theme handle improvement in customiser

# v0.5.1
* Fixed console errors
* Premium category in theme filters

# v0.5
* Minor improvement

# v0.4.9
* Premium features added

# v0.4.7
* Removed extra space around theme filters
* Fetched thumbnails for recommended plugins from WordPress.org

# v0.4.6
* Added theme listing filters

# v0.4.5
* Added cookie banner

# v0.4.4
* Fixed PHP warnings while checking plugin info.

# v0.4.3
* PHP error fixed

# 0.4.2
* View details link fixed in plugins listing
* en_US as default english fallback

# 0.4.1
* Added Health Monitor feature

# 0.4.0
* Added blog theme feature

# 0.3.9
* Changelog popup content fixed

# 0.3.8
* Pagination added in themes listing

# 0.3.7
* Staging minor fix

# 0.3.6
* Staging fixed for PHP v7.3
* PHP warnings & notices removed
* Functions prefixed

# 0.3.5
* Compatibility information updated
* PCache related fatal error fixed
* Tabs alignment fixed
* Warning added for critical plugins.

# 0.3.4
* Warning added for discouraged plugins for network site.

# 0.3.3
* Staging admin missing string translation
* Minor fixes

# 0.3.2
* Fix in staging admin css for hidden entry

# 0.3.1
* Fix for being silent when doing VCache auto install
* Fix WP admin notice cannot close

# 0.3.0
* one.com VCache auto install

# 0.2.19
* Update check fixed

# 0.2.18
* Api log error flag corrected

# 0.2.17
* Staging log calls improved
* Removed Redundant calls to api about vc-htaccess

# 0.2.16
* Plugin upgradation hook added.

# 0.2.15
* 1-click staging progress bar added
* VC v2.3 htaccess rules updated for uploads directory

# 0.2.14
* Minor fixes & improvements

# 0.2.13
* 1-click staging missing translation

# 0.2.12
* 1-Click Staging integrated

# 0.2.11
* Minor improvements related to promo
* Update script fix

# 0.2.10
* Minor improvements

# 0.2.9
* Helpdesk URLs updated

# 0.2.8
* Minor fixes

# 0.2.7
* Install button in preview of one.com themes

# 0.2.6
* Multiste fixes 
* Activations hooks

# 0.2.5
* Removed grey background from plugins listing icons

# 0.2.4
* Fixed 500 internal server error if PHP errors enabled

# 0.2.3
* Security improvements
* Timeout fixes for downloading plugins and themes

# 0.2.2
* Auto import theme data feature on fresh installs
* External plugins support (Visual Composer)

# 0.2.1
* Pagination for theme listing
* Added fi and sv_SE translations

# 0.2.0
* Discouraged plugins list added
* Few translations

# 0.1.9
* Czech language support added

# 0.1.8
* Minor improvements

# 0.1.7
* Promo/admin notices close button alignment

# 0.1.6
* Minor string improvements

# 0.1.5
* Multiste support
* Minor fixes related to installation of theme while translations are not updated automatically
* Update script v0.0.2

# 0.1.4
* Few strings translations
* Retina supported icons in one.com plugins listing
* Finnish and Polish language supported
* Removed "Jetpack" plugin from recommended plugins

# 0.1.3
* Preview themes layout improvements
* Fixed rotate icon misalignment in preview themes

# 0.1.2
* Responsive fixed - plugins listing page responsiveness fixed for all screen sizes.

# 0.1.1
* Bug fixed - "could not copy" due to WP upgrade is under progress in background

# 0.1.0
* Initial release

== Legacy standalone plugin history (absorbed in v5.2.0) ==

= Performance Cache =

# v4.1.30 [Performance Cache module]
* Removed WP Rocket menu from a performance menu

# v4.1.29 [Performance Cache module]
* Fixed - PHP 8.5 compatibility warnings

# v4.1.28 [Performance Cache module]
* Sync WP Rocket button status with plugin status

# v4.1.27 [Performance Cache module]
* Allowed json file extension in filematch

# v4.1.26 [Performance Cache module]
* Updated gravity library
* MiniCRM plugin install/update from wp.org
* Feature indicator with Marketplace menu

# v4.1.25 [Performance Cache module]
* Marketplace and My products menu restructuring

# v4.1.24 [Performance Cache module]
* Minor updates in validator

# v4.1.23 [Performance Cache module]
* Added conditional rendering of Explore plugins banner
* Minor update in events

# v4.1.22 [Performance Cache module]
* Fixed warnings in REST API response

# v4.1.21 [Performance Cache module]
* Marketplace products menu updated

# v4.1.20 [Performance Cache module]
* Marketplace menu support added

# v4.1.19 [Performance Cache module]
* Gravity re-design: WP Rocket upsell page

# v4.1.18 [Performance Cache module]
* Skip CDN rewrite for script schema
* Updated translations to use the English name ‘Managed WP' for all languages

# v4.1.17 [Performance Cache module]
* PHP 8.4 compatibility fixes
* Gravity CSS update
* Minor code improvements

# v4.1.16 [Performance Cache module]
* Misc stats update

# v4.1.15 [Performance Cache module]
* Removed logger

# v4.1.14 [Performance Cache module]
* Skip CDN rewrite in Rank math breadcrumbs schema

# v4.1.13 [Performance Cache module]
* Replaced one.com menu logo with text for better accessibility
* Fixed WP-Rocket button status using updated API

# v4.1.12 [Performance Cache module]
* Minor improvements in stats

# v4.1.11 [Performance Cache module]
* Icons fixed for subdirectory installations
* Dynamic versions added to enqueued scripts
* Replace old cdn to s3 cdn

# v4.1.10 [Performance Cache module]
* Misc stats update

# v4.1.9 [Performance Cache module]
* Minor code cleanup

# v4.1.8 [Performance Cache module]
* Re-designed plugin admin pages
* Fixed wp-cli related warnings

# v4.1.7 [Performance Cache module]
* Added optional preference for sharing anonymous usage data to help improve the plugin

# v4.1.6 [Performance Cache module]
* Minor updates in validator

# v4.1.5 [Performance Cache module]
* Deprecated warning on login - fixed
* Misc translations update

# v4.1.4 [Performance Cache module]
* Translation hooks updated as per WP 6.7

# v4.1.3 [Performance Cache module]
* CDN URL edge cases fix - Revert regex to previous stable version

# v4.1.2 [Performance Cache module]
* Improved RegEx for minified CSS URLs detection for CDN purge
* Repetitive upgrade modal issue fix

# v4.1.1 [Performance Cache module]
* Login issue in some cases - Fixed

# v4.1.0 [Performance Cache module]
* SAST vuln fixed
* Code quality Improvements

# v4.0.1 [Performance Cache module]
* menu restructure

# v4.0.0 [Performance Cache module]
* oneHOP compatibility

# v3.1.0 [Performance Cache module]
* Add CDN urls for cluster model

# v3.0.23 [Performance Cache module]
* Improved condition for validating htaccess rules
* Misc Translations update

# v3.0.22 [Performance Cache module]
* PHPCS fixes
* Validator update
* Disable autoload options

# v3.0.21 [Performance Cache module]
* Multisite compatibility added for CDN & Pcache

# v3.0.20 [Performance Cache module]
* Translations and locales fallback update
* CDN purge string conflict with .elementor class - fixed

# v3.0.19 [Performance Cache module]
* CDN validation improvements
* stats fix in validator

# v3.0.18 [Performance Cache module]
* Settings UI & validation improvements
* Validator Update

# v3.0.17 [Performance Cache module]
* Code Cleanup

# v3.0.16 [Performance Cache module]
* Translations update

# v3.0.15 [Performance Cache module]
* Code cleanup

# v3.0.14 [Performance Cache module]
* Vulnerability Monitor text and translation update

# v3.0.13 [Performance Cache module]
* PHP deprecation notice fix

# v3.0.12 [Performance Cache module]
* PHP 8.2 compatibility fixes

# v3.0.11 [Performance Cache module]
* Update htaccess rules in case of subdirectories and remove duplication from htaccess files

# v3.0.10 [Performance Cache module]
* Update vulnerability monitor modal button text
* Added condition for locale fi, pt_BR

# v3.0.9 [Performance Cache module]
* Duplication of htaccess rules - Fixed

# v3.0.8 [Performance Cache module]
* CDN Dev mode - validation improvement
* Tooltip class UI conflict fix
* Misc text and translations update
* Unified logs

# v3.0.7 [Performance Cache module]
* Update Htaccess rules in case of subdirectories

# v3.0.6 [Performance Cache module]
* Intro banner - Improvement

# v3.0.5 [Performance Cache module]
* Warning on onecom plugin deletion - Fix
* Nested menu observer console error - Fix

# v3.0.4 [Performance Cache module]
* CDN hosted fonts URL change
* Change in visibility of upsell banners
* Skip CDN rewrite in yoast-schema-graph

# v3.0.3 [Performance Cache module]
* Removed brand name constant

# v3.0.2 [Performance Cache module]
* Self hosted fonts
* CDN purge added

# v3.0.1 [Performance Cache module]
* Security Fix
* CDN Purge- query string in URLS fixed

# v3.0.0 [Performance Cache module]
* Redesign PC, CDN & WP-rocket page

# v2.0.4 [Performance Cache module]
* API calls enhancement

# v2.0.3 [Performance Cache module]
* Htaccess rules update for existing file

# v2.0.2 [Performance Cache module]
* Fix purge cache for beginners

# v2.0.1 [Performance Cache module]
* WP CLI Error Fix
* Access-Control-Allow-Origin header allows origin instead of wildcard

# v2.0.0 [Performance Cache module]
* Redesigned admin settings UI

# v1.0.13 [Performance Cache module]
* Favicon http call fix
* Features access updated for Beginner package
* one.com migrator - 'view details' link fix

# v1.0.12 [Performance Cache module]
* Exclude page/post from cache feature

# v1.0.11 [Performance Cache module]
* CDN Purge compatiblity fix with brizy builder

# v1.0.10 [Performance Cache module]
* Fatal error fixed

# v1.0.9 [Performance Cache module]
* Improvements related to Login Protection

# v1.0.8 [Performance Cache module]
* Code refactoring

# v1.0.7 [Performance Cache module]
* Added multisite handling in login masking

# v1.0.6 [Performance Cache module]
* UTM tracking links

# v1.0.5 [Performance Cache module]
* One.com restructured menu

# v1.0.4 [Performance Cache module]
* Fixed blocked access to premium themes

# v1.0.3 [Performance Cache module]
* API performance optimisation

# v1.0.2 [Performance Cache module]
* Premium validator udpate

# v1.0.1 [Performance Cache module]
* Premium settings updated

# v0.3.1 [Performance Cache module]
* Premium settings added

# v0.2.11 [Performance Cache module]
* Plugin name translation fix

# v0.2.10 [Performance Cache module]
* Logger class conflict fix

# v0.2.9 [Performance Cache module]
* PHP8 compatibility
* Improved stats

# v0.2.8 [Performance Cache module]
* 1-click wp stats

# v0.2.7 [Performance Cache module]
* Fixed minor issues related to theme switching

# v0.2.6 [Performance Cache module]
* Shortcuts added to wp admin

# v0.2.5 [Performance Cache module]
* CS, PL & PTBR translations are being discontinued
* Minor improvements

# v0.2.4 [Performance Cache module]
* Minor performance improvements

# v0.2.3 [Performance Cache module]
* Rebranding text update

# v0.2.2 [Performance Cache module]
* Rebranding

# v0.2.1 [Performance Cache module]
* Fix for turning the cache OFF

# v0.2 [Performance Cache module]
* Domain identification fixed

# v0.1.29 [Performance Cache module]
* Added .pdf in the list of extensions allowed for CDN

# v0.1.28 [Performance Cache module]
* Fixed auto deactivation on package downgrade
* Enabled CDN for uploads folder

# v0.1.27 [Performance Cache module]
* Fixed CDN and CORS conflict

# v0.1.26 [Performance Cache module]
* Minor performance improvements

# v0.1.25 [Performance Cache module]
* Admin notice fixed for non-one.com domain
* Added logs
* Disabled CDN for .css files until Varnish rules are updated
* Fixed undefined variable warning

# v0.1.24 [Performance Cache module]
* CDN added
* CDN + Performance cache auto enabled by default
* Name changed from Performance cache to Performance Tools

# v0.1.23 [Performance Cache module]
* Fixed console errors due to validator

# v0.1.22 [Performance Cache module]
* Minor improvement

# v0.1.21 [Performance Cache module]
* Premium features added

# v0.1.20 [Performance Cache module]
* Fixed admin notice for non one.com domains

# v0.1.19 [Performance Cache module]
* Fixed PHP warnings while checking plugin info.

# v0.1.18 [Performance Cache module]
* Plugin link fixed
* update script updated

# v0.1.17 [Performance Cache module]
* Removed potential issues with WordPress Site Health feature
* Caching improved by periodically validation
* update.php fixes
* en_US as default english fallback

# v0.1.16 [Performance Cache module]
* Caching improved to recover itself

# v0.1.15 [Performance Cache module]
* Multiple PURGE requests combined into one

# v0.1.14 [Performance Cache module]
* Changelog popup content fixed

# v0.1.13 [Performance Cache module]
* Minor fixes

# v0.1.12 [Performance Cache module]
* Fixed PHP warning on PHP v7.3

# v0.1.11 [Performance Cache module]
* Fixed the guide link for Swedish locale.

# v0.1.10 [Performance Cache module]
* Cookie name fixed

# v0.1.9 [Performance Cache module]
* Compatibility information updated

# v0.1.8 [Performance Cache module]
* Minor css fixes

# v0.1.7 [Performance Cache module]
* Renamed the plugin

# v0.1.6 [Performance Cache module]
* Renamed log handles

# v0.1.5 [Performance Cache module]
* Minor fixes

# v0.1.4 [Performance Cache module]
* Help-desk urls are not working for different languages from WP dashboard
* Minor fixes

# v0.1.3 [Performance Cache module]
* Permalink specific messages
* Missing translations
* Minor fixes

# v0.1.2 [Performance Cache module]
* Console error on non WP packages

# v0.1.1 [Performance Cache module]
* Truncate long purge messages
* Other minor fixes

# v0.1.0 [Performance Cache module]
* Initial release.

= Spam Protection =

# v1.0.48 - 25 Jul 2026 [Spam Protection module]
* Tour guide and validator update

# v1.0.47 - 11 Jun 2026 [Spam Protection module]
* Fixed - PHP 8.5 compatibility warnings

# v1.0.46 - 10 Mar 2026 [Spam Protection module]
* MiniCRM plugin install/update from wp.org
* Feature indicator with Marketplace menu

# v1.0.45 - 23 Feb 2026 [Spam Protection module]
* Marketplace and My products menu restructuring

# v1.0.44 - 17 Feb 2026 [Spam Protection module]
* Minor update in validator

# v1.0.43 - 10 Feb 2026 [Spam Protection module]
* Added conditional rendering of Explore plugins banner
* Minor update in events

# v1.0.42 - 23 Jan 2026 [Spam Protection module]
* Fixed warnings

# v1.0.40 - 12 Jan 2026 [Spam Protection module]
* Marketplace products menu updated

# v1.0.39 - 5 Dec 2025 [Spam Protection module]
* Marketplace menu support added

# v1.0.38 - 27 Oct 2025 [Spam Protection module]
* Updated translations to use the English name 'Managed WP' for all languages

# v1.0.37 - 08 Oct 2025 [Spam Protection module]
* PHP 8.4 compatibility fixes
* Minor code improvements

# v1.0.36 - 22 Sep 2025 [Spam Protection module]
* Misc stats update

# v1.0.35 - 30 Jul 2025 [Spam Protection module]
* Replaced one.com menu logo with text for better accessibility

# v1.0.34 - 26 Jun 2025 [Spam Protection module]
* Minor improvements in stats

# v1.0.33 - 19 Jun 2025 [Spam Protection module]
* Replace old cdn to s3 cdn

# v1.0.32 - 28 May 2025 [Spam Protection module]
* Misc stats update

# v1.0.31 - 09 May 2025 [Spam Protection module]
* Minor code cleanup

# v1.0.30 - 24 Apr 2025 [Spam Protection module]
* Added optional preference for sharing anonymous usage data to help improve the plugin

# v1.0.29 - 02 Apr 2025 [Spam Protection module]
* Minor updates in validator

# v1.0.28 - 07 Feb 2025 [Spam Protection module]
* Misc translations update

# v1.0.27 - 06 Dec 2024 [Spam Protection module]
* Bypassed Divi form field from checks
* Translation hooks updated as per WP 6.7
* Code quality fixes and improvements

# v1.0.26 - 13 Nov 2024 [Spam Protection module]
* Repetitive upgrade modal issue fix

# v1.0.25 - 15 Oct 2024 [Spam Protection module]
* Code quality fixes and improvements

# v1.0.24 - 21 Aug 2024 [Spam Protection module]
* menu restructure

# v1.0.23 - 28 June 2024 [Spam Protection module]
* oneHOP compatibility

# v1.0.22 - 27 Mar 2024 [Spam Protection module]
* PHPCS fixes
* Disable autoload options
* Validator update

# v1.0.21 - 12 Mar 2024 [Spam Protection module]
* Avada theme form field "hidden_field_names" bypassed from spam protection checks

# v1.0.20 - 08 Feb 2024 [Spam Protection module]
* Multisite not supported banner added

# v1.0.19 - 22 Jan 2024 [Spam Protection module]
* Translations and locales fallback update

# v1.0.18 - 27 Nov 2023 [Spam Protection module]
* Fixed login protection
* Stats fix in validator

# v1.0.17 - 03 Nov 2023 [Spam Protection module]
* Fixed issue with view details link

# v1.0.16 - 18 Oct 2023 [Spam Protection module]
* Validator Update

# v1.0.15 - 24 Aug 2023 [Spam Protection module]
* Bypass the form input values from spam checks if they are in array format

# v1.0.14 - 09 Aug 2023 [Spam Protection module]
* Ignored empty values in Too short username & email check
* Minor UI fixes

# v1.0.13 - 19 Jul 2023 [Spam Protection module]
* Settings form validation improvements
* Translations update

# v1.0.12 - 31 May 2023 [Spam Protection module]
* Vulnerability Monitor text and translation update

# v1.0.11 - 12 May 2023 [Spam Protection module]
* Warnings fixed

# v1.0.10 - 23 Mar 2023 [Spam Protection module]
* Update vulnerability monitor modal button text
* Added condition for locale fi, pt_BRn

# v1.0.9 - 23 Feb 2023 [Spam Protection module]
* User agent String Added in logs
* Translations correction

# v1.0.8 - 15 Dec 2022 [Spam Protection module]
* Intro banner - Improvement

# v1.0.7 - 15 Nov 2022 [Spam Protection module]
* Warning on onecom plugin deletion - Fix
* Nested menu observer console error - Fix

# v1.0.6 - 31 Oct 2022 [Spam Protection module]
* CDN hosted fonts URL change
* Change in visibility of upsell banners

# v1.0.5 - 16 Sep 2022 [Spam Protection module]
* Self hosted fonts
* Paypal IPN requests whitelisted from HTTP_REFERRER check

# v1.0.4 - 23 Aug 2022 [Spam Protection module]
* Exception added in username check for specific plugin

# v1.0.3 - 27 May 2022 [Spam Protection module]
* IP whitelisting
* Fix horizontal scroll in logs on smaller screens

# v1.0.2 - 23 May 2022 [Spam Protection module]
* API calls enhancement

# v1.0.1 - 23 Mar 2022 [Spam Protection module]
* Preventing WP default css override
* Translations for upsell modal.

# v1.0.0 - 22 Feb 2022 [Spam Protection module]
* Redesigned admin settings UI

# v0.1.17 - 10 Jan 2022 [Spam Protection module]
* Validator updated

# v0.1.16 - 06 Jan 2022 [Spam Protection module]
* Username check disabled to resolve false positives
* 'address' field removed from list to skip from spam protection checks

# v0.1.15 - 07 Dec 2021 [Spam Protection module]
* Conflict with ocean elementor widgets plugin resolved
* one.com migrator - 'view details' link fix

# v0.1.14 - 14 Nov 2021 [Spam Protection module]
* Upsell banner text/translation (validator) update

# v0.1.13 - 21 Oct 2021 [Spam Protection module]
* Conflict resolved with WP native comment moderation feature

# v0.1.12 - 05 Oct 2021 [Spam Protection module]
* Option to whitelist users
* Floating button in settings page
* Options data updated to autoload false

# v0.1.11 - 16 Aug 2021 [Spam Protection module]
* Improved check to resolve blocking woocommerce ajax submissions

# v0.1.10 - 9 Aug 2021 [Spam Protection module]
* Improvements and refactoring related to Login Protection

# v0.1.9 - 15 Jul 2021 [Spam Protection module]
* Fixed issue with contact form submission

# v0.1.8 - 09 Jul 2021 [Spam Protection module]
* Corner case fix added for 404 probing

# v0.1.7 - 07 Jul 2021 [Spam Protection module]
* Added multisite handling in login masking

# v0.1.6 - 05 Jul 2021 [Spam Protection module]
* UTM tracking links
* Fixed warning

# v0.1.5 - 05 Jul 2021 [Spam Protection module]
* one.com centralised menu

# v0.1.4 - 23rd June 2021 [Spam Protection module]
* API performance optimisation

# v0.1.3 - 23rd March 2021 [Spam Protection module]
* Updated validator

# v0.1.2 - 23rd March 2021 [Spam Protection module]
* Updated menu position

# v0.1.1 - 23rd March 2021 [Spam Protection module]
* Improvements

# v0.1.0 - 23rd March 2021 [Spam Protection module]
* Initial release

= Maintenance Mode =

# v3.1.16 - 25 Jul 2026 [Maintenance Mode module]
* Tour guide and validator update

# v3.1.15 - 11 Jun 2026 [Maintenance Mode module]
* Skip whitelisted pages check if MWP addon is not active

# v3.1.14 - 08 Apr 2026 [Maintenance Mode module]
* Fix: Undefined array warning when default settings are missing in db

# v3.1.13 - 09 Mar 2026 [Maintenance Mode module]
* Updated gravity library
* MiniCRM plugin install/update from wp.org
* Feature indicator with Marketplace menu

# v3.1.12 - 23 Feb 2026 [Maintenance Mode module]
* Marketplace and My products menu restructuring
* fix: handle missing Under Construction option to prevent PHP warning

# v3.1.11 - 17 Feb 2026 [Maintenance Mode module]
* Minor updates in validator

# v3.1.10 - 10 Feb 2026 [Maintenance Mode module]
* Added conditional rendering of Explore plugins banner
* Minor update in events

# v3.1.9 - 20 Jan 2026 [Maintenance Mode module]
* Fixed warnings in REST API response

# v3.1.8 - 12 Jan 2026 [Maintenance Mode module]
* Marketplace products menu updated

# v3.1.7 - 5 Dec 2025 [Maintenance Mode module]
* Marketplace menu support added

# v3.1.6 - 27 Oct 2025 [Maintenance Mode module]
* Updated translations to use the English name 'Managed WP' for all languages

# v3.1.5 - 08 Oct 2025 [Maintenance Mode module]
* PHP 8.4 compatibility fixes
* Gravity CSS update
* Minor code improvements

# v3.1.4 - 22 Sep 2025 [Maintenance Mode module]
Misc stats update

# v3.1.3 - 01 Sep 2025 [Maintenance Mode module]
Replaced select box by Radio buttons in countdown timer

# v3.1.2 - 14 Aug 2025 [Maintenance Mode module]
Image overlay height adjustments

# v3.1.1 - 23 Jul 2025 [Maintenance Mode module]
Improvement in countdown timer's dropdown text
Allowlist users and discourage search engine options enabled by default
Replaced one.com menu logo with text for better accessibility

# v3.1.0 - 26 June 2025 [Maintenance Mode module]
Gravity Redesign
Minor improvements in stats

# v3.0.11 - 19 June 2025 [Maintenance Mode module]
Replace old cdn to s3 cdn

# v3.0.10 - 28 May 2025 [Maintenance Mode module]
Misc stats update

# v3.0.9 - 09 May 2025 [Maintenance Mode module]
Misc translations update and minor code cleanup

# v3.0.8 - 24 Apr 2025 [Maintenance Mode module]
Added optional preference for sharing anonymous usage data to help improve the plugin

# v3.0.7 - 02 Apr 2025 [Maintenance Mode module]
Subscribe form placeholder text alignment fix
Minor updates in validator

# v3.0.6 - 07 Feb 2025 [Maintenance Mode module]
Performance monitor scan is whitelisted for http 503 mode
Misc translations update

# v3.0.5 - 10 Jan 2025 [Maintenance Mode module]
Code quality fixes and improvements

# v3.0.4 - 6 Dec 2024 [Maintenance Mode module]
Translation hooks updated as per WP 6.7

# v3.0.3 - 13 Nov 2024 [Maintenance Mode module]
Repetitive upgrade modal issue fix

# v3.0.2 - 15 Oct 2024 [Maintenance Mode module]
Code quality fixes and improvements

# v3.0.1 - 21 Aug 2024 [Maintenance Mode module]
menu restructure

# v3.0.0 - 28 Jun 2024 [Maintenance Mode module]
oneHOP compatibility

# v2.2.8 - 27 Mar 2024 [Maintenance Mode module]
PHPCS fixes
Validator update
Disable autoload options

# v2.2.7 - 08 Feb 2024 [Maintenance Mode module]
Multisite not supported banner added

# v2.2.6 - 22 Jan 2024 [Maintenance Mode module]
Translations and locales fallback update

# v2.2.5 - 27 Nov 2023 [Maintenance Mode module]
Stats - decode error fix

# v2.2.4 - 03 Nov 2023 [Maintenance Mode module]
Fixed issue with view details link

# v2.2.3 - 18 Oct 2023 [Maintenance Mode module]
Validator Update

# v2.2.2 - 19 Jul 2023 [Maintenance Mode module]
Translations update

# v2.2.1 - 30 May 2023 [Maintenance Mode module]
Admin bar menu tooltip fixed
Vulnerability Monitor text and translation update

# v2.2.0 - 12 May 2023 [Maintenance Mode module]
* Topbar status indicator on frontend
* Excluded "/login" URL from being displayed as under construction
* Purge cache upon plugin activation/deactivation
* Minor UI fixes and improvements

# v2.1.8 - 28 Apr 2023 [Maintenance Mode module]
* PHP 8.2 compatibility fixes

# v2.1.7 - 23 Mar 2023 [Maintenance Mode module]
* Update vulnerability monitor modal button text
* Added condition for locale fi, pt_BR

# v2.1.6 - 23 Feb 2023 [Maintenance Mode module]
* Plugin menu image size conflict fix with GT3 plugin

# v2.1.5 - 18 Jan 2023 [Maintenance Mode module]
* Top bar indicator visual update - ON/OFF
* Vulnerable JS Library issue - fixed
* Updated keys for NL

# v2.1.4 - 14 Dec 2022 [Maintenance Mode module]
* Top bar indicator font size revertedL

# v2.1.3 - 14 Dec 2022 [Maintenance Mode module]
* Top bar indicator visual update - ON/OFF
* Vulnerable JS Library issue - fixed
* Updated keys for NL

# v2.1.2 - 15 Nov 2022 [Maintenance Mode module]
* Admin scripts enqueue conflict - fix

# v2.1.1 - 31 Oct 2022 [Maintenance Mode module]
* CDN hosted fonts URL change
* Change in visibility of upsell banners

# v2.1.0 - 26 Jul 2022 [Maintenance Mode module]
* Performance improvement for admin settings page

# v2.0.3 - 29 May 2022 [Maintenance Mode module]
* API calls enhancement

# v2.0.2 - 23 May 2022 [Maintenance Mode module]
* API calls enhancement

# v2.0.1 - 24 Mar 2022 [Maintenance Mode module]
* Preventing WP default css override

# v2.0.0 - 22 Feb 2022 [Maintenance Mode module]
* Redesigned admin settings UI
* Admin pages redesigns

# v1.4.0 - 03 Feb 2022 [Maintenance Mode module]
* Google fonts load from self hosted CDN
* Improved default settings
* Migration support with http 503

# v1.3.3 - 10 Jan 2022 [Maintenance Mode module]
* Update validator
* Removed extra api-hooks

# v1.3.2 - 7 Dec 2021 [Maintenance Mode module]
* one.com migrator - 'view details' link fix
* Features access updated for Beginner package

# v1.3.1 - 12 Nov 2021 [Maintenance Mode module]
* PHP notice fix

# v1.3.0 - 26 Oct 2021 [Maintenance Mode module]
* Exclude pages feature added

# v1.2.0 - 5 Oct 2021 [Maintenance Mode module]
* Added floating save button

# v1.0.9 - 9 Aug 2021 [Maintenance Mode module]
* Improvements related to Login Protection

# v1.0.8 [Maintenance Mode module]
* Code refactoring

# v1.0.7 [Maintenance Mode module]
* Added multisite handling in login masking

# v1.0.6 [Maintenance Mode module]
* UTM tracking links

# v1.0.5 [Maintenance Mode module]
* One.com restructured menu

# v1.0.4 [Maintenance Mode module]
* Fixed blocked access to premium themes

# v1.0.3 [Maintenance Mode module]
* API optimisation

# v1.0.2 [Maintenance Mode module]
* Premium validator update

# v1.0.1 [Maintenance Mode module]
* Features condition updated

# v1.0.0 [Maintenance Mode module]
* Premium features added

# v0.3.0 [Maintenance Mode module]
* Cache clear on settings change
* Translations update
* Minor fixes & improvements

# v0.2.2 [Maintenance Mode module]
* PHP8 compatibility
* Improved Stats

# v0.2.1 [Maintenance Mode module]
* Minor code improvements

# v0.2.0 [Maintenance Mode module]
* Added option to whitelist user roles
* Added shortcut and uc status in toolbar
* Translations updated
* Minor fixes and improvements

# v0.1.3 [Maintenance Mode module]
* Initial release
