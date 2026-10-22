# One.com premium validator

## Integration
Simply include validator.php file in api-hooks.php in themes or plugins.

## Known gotchas

`validator.php` bundles a vendor tree (OTPHP/Base32) that's byte-identical
across this hub plugin and all three legacy standalone one.com plugins. Its
`vendor/autoload.php` include is guarded against being loaded twice by a
sibling plugin in the same request (WPIN-8795) — the guard's reasoning and a
regression test simulating the two-plugin scenario live inline in
`validator.php` and `tests/test-validator-vendor-guard.php`; read those
before touching the guard condition, especially before "simplifying" it to a
generic `Composer\Autoload\ClassLoader` check (already tried once, reverted
for false-positiving against unrelated autoloaders).

This repo has no PHPUnit suite — tests under `tests/` are framework-free
scripts run via `php tests/test-*.php`, checked by exit code.

## Changelog
### v0.6.2 = 24 Aug 2026
* Fixed float accumulation drift and loose comparison in groupone_get_free_menu_position():
  accumulating raw floats (2.1 + 0.3 + 0.3 = 2.6999999999999997) caused collisions with
  string menu keys (e.g. '2.7') to be missed and loose in_array() caused false matches
  (e.g. '2.1' with '2.10'). The collision walk now converts keys to string, rounds step offsets
  to 4 decimal places, and uses strict in_array() checks (WPIN-9035).

### v0.6.1 = 24 Aug 2026
* Fixed a fatal in groupone_get_free_menu_position(): when $GLOBALS['menu'] was
  unset or an empty array the position list was never initialised and in_array()
  raised "Argument #2 ($haystack) must be of type array, null given" on PHP 8.
  Now initialised up front and guarded with isset() + is_array(), returning the
  requested position unchanged when nothing is claimed (WPIN-9033).

### v0.6.0 = 18 Jul 2026
* Made the sibling-plugin submenu gates (Performance Cache, Spam Protection,
  Maintenance Mode) module-aware via oc_feature_active(), so they light up
  for onecom-themes-plugins' merged modules (WPIN-8736) as well as the
  legacy standalone plugins. Falls back to today's is_plugin_active()
  behavior when the consuming plugin doesn't provide onecom_module_active().

### v0.5.0 = 27 Mar 2024
* ALP status added in VM Stats
* Disable autoload from plugin options
* Fix for VM stats corrupt json

### v0.4.0 = 20 Jan 2023
* Translations update

### v0.3.1 = 27 Nov 2023
* Stats - failed to decode fix for wp version when version hide by plugin

### v0.3.0 = 18 Oct 2023
* Stats for partner plugins added
* Premium theme switch modal added

### v0.2.9 = 30 May 2023
* VM upsell modal - minor text/ui fixes

### v0.2.8 = 23 Mar 2023
* Update vulnerability monitor modal button text
* Added condition for locale fi, pt_BR - Fix

### v0.2.7 = 15 Nov 2022
* Nested menu observer console error - Fix

### v0.2.6 = 10 Jan 2022
* PHP 8.1 compatibility fixes

### v0.2.5 = 11 Nov 2021
* Upsell banner message fix

### v0.2.4 = 28 Apr 2021
* Minor fixes for mWP

### v0.2.3 = 18 Dec 2020
* PHP8 fixes 

### v0.2.2 = 18 Dec 2020
* General Stats

### v0.2.1 = 28 Sept 2020
* Minor Improvements

### v0.2.0 = 9 Sept 2020
* Internal fixes and Improvements

### v0.1.8 = 14 Jul 2020
* Premium Theme Check Fixed

### v0.1.7 = 5 May 2020
* PHP Error fixed

### v0.1.6 = 30 Apr 2020
* SonarQube code improvement

### v0.1.5 = 02 Mar 2020
* Fixed the API url

### v0.1.4 = 13 Jan 2020
* Fixed the API url

### v0.1.3 = 12 Dec 2019
* Prevented theme activaion in customiser

### v0.1.2 = 10 Dec 2019
* Console error fixed

### v0.1.1 = 03 Dec 2019
* PHP warning fixed

### v0.1 = 02 Dec 2019
* Initial release
