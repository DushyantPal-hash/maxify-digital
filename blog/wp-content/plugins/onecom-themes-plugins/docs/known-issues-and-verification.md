# Known issues and manual verification notes

Gotchas discovered while working this hub plugin that aren't visible from
reading the code in isolation — either because they only show up when
reproducing an issue live, or because they're an open item with no code
change attached yet.

## Verifying REST-security fixes against wpin10 via `wp eval`

`modules/api/class-onecom-plugins-api.php`'s `create_item()` calls
`validate_token()` (line 503/635) before touching anything else — this is
the fix for WPIN-8797 (an invalid or missing token could previously mutate
`onecom_login_masking` before the token was checked). To reproduce the
original exploit or confirm a similar REST-auth fix live against wpin10
without a browser, dispatch the request in-process from WP-CLI:

```
wp eval 'var_dump( rest_do_request( new WP_REST_Request( "POST", "/onecom/v1/..." ) ) );'
```

This fails before it ever reaches your route handler unless `$_SERVER`
is made to look like a real front-end request first:

- `$_SERVER['HTTP_HOST']`, `$_SERVER['REQUEST_URI']`, and
  `$_REQUEST['rest_route']` all need to match the site's real `siteurl` /
  `home` (get both with `wp option get siteurl` / `wp option get home`).
- Without this, `GroupOne_LAUTH::validateRequestURL()` (called from
  `validate_token()` itself, so it runs even for requests that are
  *supposed* to fail auth) sees a missing/mismatched `HTTP_HOST`, calls
  `wp_redirect()` and `exit`, and the request never reaches `create_item()`
  at all — producing a misleading "it's fixed" result for the wrong reason.

This LAUTH host-check behavior is itself a separate known rough edge
(WPIN-8798) — it's not part of the WPIN-8797 fix, but it will intercept
*any* REST dispatch attempted this way against this plugin's routes, so
expect to work around it for future REST verification here too.

## Vendor bundle guard comment lost on regeneration (WPIN-8492)

`inc/Dependencies/WPMarketplace/Groupone/Marketplace/frontend/build/{addons.js,index.js}`
are vendored (Mozart/Composer) copies of the Marketplace frontend bundle.
WPIN-8492 fixed a real bug where Mixpanel events were sent to the wrong
endpoint, by patching the built JS so the actual `init()` call passes
`api_host: "https://api-eu.mixpanel.com"`. Commit `a52c4441` added an
inline guard comment next to the patch warning against silently
overwriting it on the next vendor regen.

A later regen (`5b686f4c`, 2026-07-06) overwrote both files again —
the functional fix survived (the `api-eu.mixpanel.com` override is still
present and still wins, since it's the last `init()` call the SDK sees),
but the guard comment did not. Both files still also contain a residual
`"https://api-js.mixpanel.com"` string — that's the Mixpanel SDK's own
internal default object, not a live bug; it's inert because the real
`init()` call overrides it.

**Still open**: the guard comment needs restoring, and whoever owns the
`groupone/marketplace` Composer dependency should confirm whether the
upstream package has since baked in the correct endpoint permanently (in
which case the local patch + comment can be retired instead of restored).
Do not "fix" this by editing the built JS directly without that
confirmation — it will just get overwritten again next regen.

## Legacy standalone plugins can fatal depending on plugin load order (WPIN-8736)

The three legacy standalone plugins this hub absorbed (`onecom-vcache`,
`onecom-spam-protection`, `onecom-under-construction`) each still bundle
their own full copy of `inc/lib` under the pre-rename `oc_`/`onecom_`
names, and each guards its own `require` of that copy with:

```php
if ( ! ( class_exists( 'OTPHP\TOTP' ) && class_exists( 'ParagonIE\ConstantTime\Base32' ) ) ) {
    require_once ... '/inc/lib/validator.php';
}
```

— an optimization to avoid loading the same shared vendor library twice
when more than one one.com plugin is active. It assumed every one.com
plugin's copy of `inc/lib` declares the *same* global names, which was
true until the GroupOne_/groupone_ rename (WPIN-8739) gave this hub's
copy different names.

This hub's own bootstrap requires its `inc/lib/validator.php`
*unconditionally* (see the comment above that `require_once` in
`onecom-themes-plugins.php`), so it always runs regardless of load
order. If it happens to run **before** a still-active legacy plugin,
that legacy plugin sees the TOTP/Base32 classes already loaded, skips
its own `require`, and fatals the moment it touches one of its own old
constants/functions — reproduced live on `11april-ftp2-4june.mwp.1prod.one`
by reordering the site's `active_plugins` option: e.g.
`Uncaught Error: Undefined constant "OC_VALIDATOR_DOMAIN"` from
`onecom-under-construction/inc/lib/onecom-nested-menu.php`, or
`Call to undefined function oc_set_premi_flag()` from
`onecom-spam-protection/inc/functions.php`. Not deactivating a legacy
plugin, and not anything specific to one plugin — purely which plugin's
`inc/lib` copy happens to load first.

The fix (in the `inc/lib` submodule, not the legacy plugins — never
touch those, they're being phased out) is a backward-compat alias for
every renamed symbol: `if (!defined('OC_X')) define('OC_X', GROUPONE_X)`
for constants, a variadic forwarding wrapper for functions. This is now
comprehensive (all ~30 renamed constants, ~34 renamed functions) rather
than fixed one-by-one as each gets reproduced — see that commit's
message for the one exception (`OC_PUSH_STATS`/`GROUPONE_PUSH_STATS`
alias to a literal, not to the renamed constant's value, since the
underlying legacy class name actually changed, unlike every other
aliased symbol).

If a *new* free function or constant gets added to `inc/lib` in the
future and happens to share a name with something the legacy plugins'
own `inc/lib` copy also declares, this same class of bug can reappear —
worth a lint/CI check eventually (WPIN-8737 territory), not solved here.

## PHP function-hoisting in framework-free tests

This repo has a real PHPUnit suite (`phpunit.xml.dist`, one testsuite per
module). The `inc/lib` submodule does not — its tests are plain scripts run
via `php tests/test-*.php`, checked by exit code (see
`inc/lib/tests/test-validator-vendor-guard.php`). PHP hoists *unconditional*
top-level `function foo() {}` declarations at compile time, regardless of
where they appear in the file — so a test that declares a fallback/stub
function later in the same file to simulate "not yet defined" behavior
earlier in that same file will not see the "undefined" state it expects,
even though isolated `php -r` snippets of the same production code behave
correctly. Wrap any such test-only declaration in
`if ( ! function_exists( 'foo' ) ) { function foo() {...} }` — see
`modules/maintenance-mode/tests/test-OCUC_ZThemes.php`'s `mock_getallheaders()`
helper for a worked example with the reasoning left in place (WPIN-8860).

## `$GLOBALS['menu']` is not guaranteed on `admin_menu` (WPIN-9033)

`groupone_get_free_menu_position()` (`onecom-themes-plugins.php`) picks the
first unclaimed admin-menu slot by scanning `$GLOBALS['menu']`. That global
is populated by `wp-admin/menu.php`, which only runs during a real wp-admin
page render — so it is *not* a safe assumption inside an `admin_menu`
callback.

Hook priority is *not* the route in. `wp-admin/menu.php` builds the menu and
then, on its last line, requires `wp-admin/includes/menu.php` — which is what
fires `do_action( 'admin_menu', '' )`. So by the time `admin_menu`
runs at *any* priority, `-1` included, the global is already fully populated.
`one_core_admin()`'s priority **-1** is therefore harmless here; an earlier
version of this note claimed otherwise and was wrong.

What actually reaches the function without a menu is `admin_menu` firing
outside a wp-admin page render at all — WP-CLI, cron, the PHPUnit harness, or
any caller doing `do_action( 'admin_menu' )` — because `menu.php` never ran.

Before WPIN-9033 the function built its `$menus_positions` list only inside
the `foreach`, so an unset *or empty* global left the variable undeclared and
the following `in_array()` raised a fatal `TypeError` on PHP 8
(`in_array(): Argument #2 ($haystack) must be of type array, null given`).
The empty-array case is the easier one to hit and the harder one to spot: it
produces no "undefined global" warning to point at the cause.

### The fix had to go in the submodule, not here

This function is declared **twice**, and the copy in this repo is not the one
that runs. `onecom-themes-plugins.php` requires `inc/lib/validator.php`
unconditionally at line 152, and that file declares
`groupone_get_free_menu_position()` too (`// function moved from generic plugin
to validator //`). By the time execution reaches this repo's own
`if ( ! function_exists( … ) )` guard around line 797, the name is already
taken — so the body here **never runs**, and patching it alone has no runtime
effect at all.

That is easy to miss locally: `inc/lib` is a git submodule, and if it is not
checked out, `grep` across this repo finds only one definition and the code
reads as if it were canonical. CI does check it out, which is why the stack
traces in the failing `unit-test` job pointed at `validator.php:2145` rather
than at anything in this repo.

The real fix therefore landed in `wp-in/one.com-wp-premium-validator`
(v0.6.1), with the submodule pointer bumped here. It initialises
`$menus_positions` up front and guards the read with `isset()` + `is_array()`,
returning `$start` unchanged when nothing is claimed — with no positions taken,
the requested one is free by definition.

**The copy in this repo has been deleted**, and a comment left where it stood.
It was first kept "in sync as a fallback", on the theory that it would become
live if a sibling one.com plugin's older `validator.php` won the load race. That
theory was wrong: the legacy plugins declare `oc_`/`onecom_`-prefixed symbols,
not `groupone_` ones, so nothing else can define this name first. There is no
load order in which the hub's copy wins — it was dead in all of them.

Sonar is what forced the issue. `sonar-project.properties` excludes
`**/lib/**/*` from coverage, so the validator's copy — the one that actually
executes, and the one the tests exercise — contributes nothing to the coverage
measure, while `onecom-themes-plugins.php` is counted. Lines added to a
never-executed function in a coverage-counted file are uncoverable by
construction, and the quality gate failed on them. A coverage exemption would
have been the wrong answer to a correct signal.

**If you need to change this function, change it in the validator.** WPIN-9036
adds a CI guard against reintroducing a duplicate declaration here.

Two consequences worth remembering:

- If you add another caller of `groupone_get_free_menu_position()`, you no
  longer need to pre-check the global yourself. The existing guard in
  `modules/performance-cache/classes/class-ocvcaching.php`
  (`! empty( $onecom_generic_menu_position )`) is about the *position* being
  known, not about the menu global, so it stays.
- In tests, seeding `$GLOBALS['menu']` is now a fixture rather than a crash
  workaround — `test_one_core_admin_fx()` still seeds a representative core
  menu because it asserts our items are appended to a realistic structure.

## Menu-position collision walk returns an occupied slot (WPIN-9035)

Fixed in `validator.php` v0.6.2 (WPIN-9035).

When the requested position was already taken,
`groupone_get_free_menu_position()` walked forward in `$increment` (`0.3`)
steps. The walk accumulated **floats**, but `$GLOBALS['menu']` keys were
**strings**, and the `in_array()` comparisons were loose. Only the first hop
survived that round trip:

```
'2.1' + 0.3  ->  2.4                  in_array vs '2.4' ?  true   <- caught
       + 0.3  ->  2.6999999999999997   in_array vs '2.7' ?  false  <- missed
```

The first hop landed bit-exactly on the same double as literal `2.4`, but the
second hop (`2.6999999999999997`) failed loose numeric equality against `'2.7'`.
The walk exited early and returned `'2.7'` (an occupied slot) instead of `'3'`.
Additionally, loose comparison meant `'2.1'` falsely matched `'2.10'`.

**Fix (WPIN-9035)**:
- `$GLOBALS['menu']` keys are mapped to strings (`array_map('strval', array_keys(...))`).
- The collision walk computes step-based offset from base and rounds to 4 decimal places before string conversion.
- `in_array()` uses strict type comparison (`true`).
- `test_free_menu_position_walk_stops_early_on_multiple_collisions()` in `tests/test-main-plugin-file.php` asserts `'3'`.

## Duplicate symbols between Hub and inc/lib/ submodule (WPIN-9036)

`onecom-themes-plugins.php` requires `inc/lib/validator.php` (from the `wp-in/one.com-wp-premium-validator` submodule) unconditionally at line 152. Because conditional declarations (e.g. `if ( ! function_exists( ... ) )`) in PHP are evaluated at runtime rather than compile-time hoisted, whichever copy executes first defines the symbol permanently.

If a developer defines a function in the hub with a `! function_exists()` guard, and the same function is declared in `inc/lib/validator.php`, the hub definition is **silent dead code**:
- `inc/lib/validator.php` always runs first and defines the function.
- The hub's guarded definition is bypassed.
- Any edits made to the hub copy have zero runtime effect, creating a confusing debugging trap (as occurred during WPIN-9033).

### CI Guard & Local Verification

`bin/check-duplicate-symbols.php` scans all PHP files in the repository and `inc/lib/` using `token_get_all()` to extract top-level functions and constants.

To run the check locally:
```bash
php bin/check-duplicate-symbols.php
```

In CI, the `check-duplicate-symbols` job runs on all MRs and branches to prevent accidental reintroduction of duplicate symbols across the hub and submodule.

## Maintenance Mode does not hide the site from search engines (WPIN-8866)

The Maintenance Mode screen's first toggle writes `uc_http_mode` in the
`onecom_under_construction_info` option, and the only thing that setting does is
choose the status code of the maintenance page in
`modules/maintenance-mode/classes/class-ocuc-themes.php`: `503 Service
Unavailable` when it is on, `200 OK` when it is off. Nothing in
`modules/maintenance-mode/` emits a `noindex` directive, hooks `wp_robots`, or
touches the `blog_public` option — grep for all three and you get no matches.

Until WPIN-8866 the toggle was labelled *"Hide website from search results"* and
described as *"Your site won't be indexed by search engines and won't show up in
search results."* That was the reverse of the truth: a 503 tells crawlers the
outage is temporary, so they retry later and **keep** the pages they already
have indexed. The copy now says the site is reported as temporarily unavailable.

`test_maintenance_mode_never_deindexes_the_site()` in
`modules/maintenance-mode/tests/test-OCUC_Assets.php` fails if any module file
starts referencing `noindex` / `wp_robots` / `blog_public`, so real de-indexing
cannot be added without the copy being revisited at the same time.

The second toggle (`uc_whitelisted_roles`) was similarly labelled *"Allowlist
users"*, which implies a per-role allowlist. `GroupOne_UC_Themes::under_construction()`
returns early for **any** `is_user_logged_in()` request when it is on, with no
capability or role check at all (the `@todo checkif capabilities needs to be
added here` comment sits on that exact line). The copy now says so explicitly.

Note that `uc_whitelist()` — same file, similar name — is unrelated to this
toggle: it exempts one.com's own probes (`User-Agent: ocm-client`, or
`X-Forwarded-For: 46.30.212.65`) from the 503 so monitoring does not read the
maintenance page as an outage.


## Translation Catalogue Staleness (WPIN-9032)

`languages/onecom-wp.pot` previously carried `POT-Creation-Date: 2024-01-12`. As new merged modules (`modules/home`, `modules/marketplace`, `modules/maintenance-mode`, `modules/performance-cache`, etc.) and toolbar entries were added, strings were not extracted into the `.pot` template, causing them to render in English across all non-English locales.

**Fix (WPIN-9032)**:
- Wrapped menu entries (`My products`, `Marketplace`) with `__()` in `inc/lib/onecom-nested-menu.php`.
- Unified toolbar items (`Maintenance Mode is OFF/ON`, `Clear Performance Cache`) to domain `onecom-wp`.
- Regenerated `languages/onecom-wp.pot` via `wp i18n make-pot . languages/onecom-wp.pot --domain=onecom-wp --skip-js --exclude="vendor,node_modules,tests"`.
- `msgmerge` merged forward all 11 locale `.po` files with 100% preservation of existing translations, adding 228 new untranslated strings per locale.
- Recompiled all 11 `.mo` binary catalogues in `languages/`.
