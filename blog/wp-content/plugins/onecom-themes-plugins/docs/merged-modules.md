# Merged feature modules

Three previously-standalone one.com plugins were folded into this plugin as
feature modules (WPIN-8736): Performance Cache (`onecom-vcache`), Spam
Protection (`onecom-spam-protection`), and Maintenance Mode
(`onecom-under-construction`). Each module lives under `modules/<slug>/` with
its own bootstrap file, and is loaded conditionally so a site that still has
the old standalone plugin active keeps behaving exactly as before (its
module bootstrap simply never loads) — until either side gets deactivated,
at which point `inc/legacy-plugin-takeover.php` (WPIN-8758) keeps exactly one
of the two active, see below.

## Registry: `inc/module-registry.php`

`groupone_merged_modules_map()` is the single source of truth mapping a module
slug to its bootstrap file path and the legacy standalone plugin file that
still takes precedence over it:

- `performance-cache` → `modules/performance-cache/performance-cache.php`, legacy `onecom-vcache/vcaching.php`
- `spam-protection` → `modules/spam-protection/spam-protection.php`, legacy `onecom-spam-protection/onecom-spam-protection.php`
- `maintenance-mode` → `modules/maintenance-mode/maintenance-mode.php`, legacy `onecom-under-construction/onecom-under-construction.php`

`groupone_load_merged_modules()` runs at plugin parse time (not on `init`) and,
for each entry, `require_once`s the module's bootstrap unless either:

- the module's bootstrap file doesn't exist (older release without the module bundled), or
- the legacy standalone plugin is still active (`groupone_legacy_plugin_active()`, safe to call before `wp-admin/includes/plugin.php` loads), or
- the site owner explicitly disabled it via the Home page toggle list (`groupone_get_disabled_modules()`).

It must run at parse time rather than on a hook because the ported modules
register `init`/`wp`/`send_headers` callbacks and front-end hooks directly
from their constructors — an init-time load would miss them.

`groupone_module_active( $slug )` reports whether a module was loaded for the
current request. It's the primary integration point consumed outside this
file: the `inc/lib` submodule's menu classes, `modules/api/class-onecom-plugins-api.php`,
`modules/health-monitor/classes/class-onecom-check-spam.php` and
`modules/health-monitor/classes/class-onecom-check-plugins.php`
call it (via the `oc_feature_active()` / `groupone_feature_active()` helpers described
below) instead of `is_plugin_active()` directly, so a merged module is treated the same
as its former standalone plugin for menu visibility, status reporting and
health scans.

**Any new `is_plugin_active()` call naming one of the three legacy plugin
files is a bug in waiting** — it answers "no" on every site where the merged
module is providing that feature.

- **WPIN-8930** was exactly this for Spam Protection: Health Monitor's spam
  checks decided from `active_plugins` alone, so a site with the Spam Protection
  module enabled was told in the To-do list to install the very plugin the hub
  had absorbed, with a fix button that would have reinstalled it underneath the
  module. `OnecomCheckSpam` now carries the same two private
  `groupone_module_active()` / `groupone_feature_active()` wrappers
  `Onecom_Plugins_Api` uses. Note that neither surface applies a premium/MWP
  gate: `groupone_load_merged_modules()` loads the Spam Protection module
  whenever it isn't disabled, while its filtering classes only instantiate
  behind `groupone_sp_is_premium()` — both surfaces deliberately match the
  legacy standalone plugin's own "active is enough" behaviour rather than
  diverging from each other.
- **WPIN-8960** was the same for Performance Cache: Health Monitor's
  `check_performance_cache()` / `check_cdn()` decided purely from
  `is_plugin_active( 'onecom-vcache/vcaching.php' )`, so a site with the module
  enabled was told to set up caching/CDN it already had, with fix buttons
  (`fix_performance_cache()` / `fix_performance_cdn()`) that would have
  reinstalled the superseded standalone plugin underneath the module.
  `OnecomCheckPlugins` now carries the same two private
  `groupone_module_active()` / `groupone_feature_active()` wrappers
  `Onecom_Plugins_Api` uses. No premium/MWP gate is applied here either, for the
  same reason as elsewhere in this file: `groupone_load_merged_modules()` loads
  Performance Cache whenever it isn't disabled, matching the legacy standalone
  plugin's own "active is enough" behaviour.

`groupone_get_disabled_modules()` / `groupone_set_module_enabled( $slug, $enabled )`
back the Home page toggle list (see below) with a single option,
`onecom_disabled_modules` (array of slugs — the persisted option name itself
was not part of the GroupOne rebrand, so existing sites' saved preferences
keep working unchanged). This is independent of the standalone-plugin
precedence rule above — a module can be present, have no standalone plugin
active, and still be switched off by the site owner.

`groupone_module_labels()` returns the human-readable label per module slug,
consumed by both the Home page toggle list and `groupone_legacy_plugin_labels()`
in `inc/legacy-plugin-takeover.php`.

## Activation: `onecom_merged_modules_activate`

Each module's own `register_activation_hook()` is meaningless once it's a
module rather than a standalone plugin (WordPress never activates a module
file directly), so ported modules hook their one-time setup to the action
`onecom_merged_modules_activate` instead (the hook name itself is unchanged by
the GroupOne rebrand, since renaming a hook name would break anything already
listening for it). The hub fires this action from its own
`groupone_plugin_activation()` callback. Each module's activation callback is
written to be idempotent (checked via `get_option()`/`get_site_option()`
existence, not always-run) since the hub firing this action doesn't
necessarily mean the module is newly active for that specific site.

## Upgrade behavior for existing customers

A brand-new install has no `onecom_disabled_modules` option, so
`groupone_get_disabled_modules()` defaults to an empty array and every module
loads — new customers get all three by default (WPIN-8736).

An existing customer's hub, on the other hand, is expected to keep behaving
exactly as it did before this feature shipped: not get all three modules
silently switched on with the same update that introduces them.
`groupone_plugin_upgradation()` (hooked to `upgrader_process_complete`) handles
this by seeding `onecom_disabled_modules` with every module slug the first
time it observes an upgrade of an already-installed hub, but only if the
option has never been set (`false === get_option( 'onecom_disabled_modules' )`).
This runs once per site: after that first seed, the option always exists, so
a site owner who re-enables a module (or all of them) via the Home page
toggle list is never overridden again on a later update.

`upgrader_process_complete` alone is not enough to get that seed written,
though. WordPress swaps the plugin files on disk but the request that performed
the update keeps executing the build that was already in memory — the *old*
one — so a site upgrading from any release that predates
`groupone_plugin_upgradation()` never runs it at all. `oc_tp_version` used to be
written by the routine and read nowhere, so nothing let the new code catch up.

The routine body therefore lives in `groupone_run_upgrade_routine()` and has two
entry points: `groupone_plugin_upgradation()` on `upgrader_process_complete`, and
`groupone_maybe_run_upgrade_routine()` on `admin_init`, which compares
`oc_tp_version` against `GROUPONE_WP_VERSION` and runs the routine under the new
build when they differ. The routine is idempotent and still refuses to overwrite
an `onecom_disabled_modules` that already exists.

Two things keep that stamp honest:

- The stamp is **per site** (`get_option`/`update_option`), matching the scope of
  `onecom_disabled_modules`. A network-wide stamp would let the first subsite an
  admin visits mark the whole network done and leave every other subsite
  unseeded with all merged modules silently active.
- `groupone_plugin_activation()` stamps the current version, and
  `groupone_stamp_new_site()` (on `wp_initialize_site`) stamps a subsite created
  while the plugin is already network-active. Without those, a brand-new site's
  missing stamp would read as an upgrade and blanket-disable every merged module,
  which is the opposite of the new-customer default above.

The `admin_init` entry point deliberately does nothing during AJAX, REST or cron
requests: the routine deactivates legacy plugins, and `admin_init` fires inside
`admin-ajax.php` too, so an unguarded run could mutate the active plugin set
underneath a handler that had already booted against the old one. It also
requires `manage_options`, so the routine waits for someone who could have
deactivated a plugin by hand — `manage_options` rather than `activate_plugins`
because on multisite `map_meta_cap()` adds `manage_network_plugins` to
`activate_plugins` unless the network enabled the Plugins menu, which would stop
a site administrator's own subsite from ever being seeded.

## Home page module toggle list (WPIN-8736)

The standalone "Settings" admin page (`onecom-wp-features`, WPIN-8752) was
removed and its toggle list moved onto the one.com Home page instead
(`modules/home/templates/home.php`), next to the existing Preferences /
Imagify / Performance Score toggles in the same footer section.
`OneHomeSections::get_module_toggles()` (`modules/home/oc-home-sections.php`)
shapes the registry data (`groupone_merged_modules_map()`,
`groupone_get_disabled_modules()`, `groupone_module_labels()`,
`groupone_module_descriptions()`, `groupone_module_icons()`) for the
template — one row per module (icon, label, one-line description, toggle),
with a note in place of the toggle when the legacy standalone plugin still
takes precedence, the module's bootstrap isn't bundled in this release, or
the module is MWP-gated (see below) and the current site isn't on an MWP
plan.

Unlike the old Settings page's full-page form POST, toggling a module here
is instant: each checkbox posts to the `oc_toggle_module` AJAX action
(`groupone_ajax_toggle_module()` in `inc/module-registry.php`, nonce action
`oc_toggle_module`), which validates the nonce, capability
(`manage_options`), and the legacy-plugin-active guard before calling
`groupone_set_module_enabled()` — mirroring the AJAX-toggle pattern already
used by `GroupOne_Performance_Column::handle_toggle()` on the same page. The
JS wiring lives in `modules/home/js/main.js`. Because modules load at parse
time, a page reload is still needed for a toggled module's code to actually
stop/start loading; a toast confirms the option was saved but does not imply
a live behavior change until the next page load.

### Live admin-menu sync (WPIN-8870)

The one.com sidebar is built server-side on `admin_menu`, so before this a
toggled module left the menu advertising a module that was off (or hiding one
that was just turned on) until the next page load. `groupone_ajax_toggle_module()`
now returns the toggled module's menu entries alongside the saved state, and
`ocSyncOnecomAdminMenu()` in `modules/home/js/main.js` adds or removes those
`<li>`s in place.

`groupone_module_menu_entries()` (`inc/module-registry.php`) reads a new
`admin_menu` key on each `groupone_merged_modules_map()` entry, holding the
child entries (slug, title, target accordion panel) and, where the module owns
its heading, the accordion itself. Only `performance-cache` does: the
"Performance" heading is registered solely on that module's gate, whereas
"Health and Security" and "Utility" are also gated on
`onecom_plugin_activated()` and therefore outlive any single module.

Those slugs and panel class names mirror `onecom_register_menu()` and
`onecom_js_assets()` in the premium-validator submodule
(`inc/lib/onecom-nested-menu.php`) — **a real cross-repo coupling**: rename a
menu slug or a panel class there and this map has to follow, or the live sync
targets nothing. The failure mode is deliberately benign — every step is a
no-op when its target is missing, so a drifted slug degrades to "sidebar stays
stale until reload", which is exactly the pre-WPIN-8870 behaviour, never a
corrupted menu. It is duplicated rather than re-derived by re-running
`onecom_register_menu()` inside the AJAX request because that function
`include_once`s the webshop plugin's main file when that plugin is active,
and running a third-party plugin's bootstrap inside an unrelated AJAX call is
a considerably worse failure mode than a stale menu.

This changes the menu only. The caveat above still holds: the module's own code
loads at parse time, so its behaviour still doesn't change until the next page
load. Clicking a freshly added entry is safe — that navigation *is* a new page
load, by which point the module is registered.

#### Disabled-module fallback page (WPIN-8925)

Toggling a module off doesn't reload the page (see above), so the
already-rendered sidebar `<li>` for the just-disabled module stays in the DOM
until the next full navigation — `modules/home/js/main.js`'s `.done()`
handler hides it immediately using `ocSyncOnecomAdminMenu()`. This is a cosmetic
fix only: it doesn't help a stale bookmark, browser-history entry, or an
already-open tab pointing straight at the module's settings URL.

The functional half of the fix is server-side. Each module registers its own
real settings page from its own bootstrap (e.g.
`class-onecom-sp-settings.php` calls `add_menu_page( ..., 'onecom-wp-spam-protection', ... )`),
which `groupone_load_merged_modules()` never `require_once`s while the module
is disabled — so nothing registers that page slug for the request, and a
direct visit falls through to WordPress core's generic
`wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 )`.

`groupone_module_page_slugs()` maps each merged module slug to the settings
page slug(s) its bootstrap would otherwise register:

- `performance-cache` → `onecom-vcache-plugin`, `onecom-cdn`
- `spam-protection` → `onecom-wp-spam-protection`
- `maintenance-mode` → `onecom-wp-under-construction`

`groupone_register_disabled_module_pages()` (hooked to `admin_menu` and
`network_admin_menu`) walks this map and, for every page slug of a module
that is neither active (`groupone_module_active()`) nor superseded by its
active legacy plugin (`groupone_legacy_plugin_active()`), registers a hidden
page hook for it via `add_submenu_page( null, ..., 'groupone_render_disabled_module_notice' )`
— the documented WordPress pattern (since WP 4.4) for a page reachable
directly by URL but absent from any menu. `groupone_render_disabled_module_notice()`
re-checks `current_user_can( 'manage_options' )` (defense in depth; the
`add_submenu_page()` capability argument already enforces this) and renders a
"This feature is currently switched off." notice with a link back to the Home
page's Features list, instead of the generic core denial. If the module gets
re-enabled, the next full page load's `admin_menu` pass registers its real
page again, which supersedes this fallback for that request.

### MWP-only modules (WPIN-8859, WPIN-9027)

`groupone_merged_modules_map()` entries carry an `mwp_only` flag — currently
`true` only for `spam-protection`, since that module is an MWP-plan perk
rather than something gated by a per-module purchase. When `mwp_only` is set
and `ismWP()` (`inc/functions.php`) is false:

1. **Zero compute footprint (WPIN-9027)**: `groupone_load_merged_modules()` (`inc/module-registry.php`)
   skips requiring the module's `bootstrap` file completely. No classes, hooks, comment filters,
   or background crons from the module are loaded or registered in WordPress runtime for non-mWP customers.
2. **Fresh activation default (WPIN-9027)**: `groupone_plugin_activation()` seeds `onecom_disabled_modules`
   to include `mwp_only` modules on non-mWP sites, ensuring the persisted state defaults to disabled.
3. **Home page rendering**: `get_module_toggles()` marks the row `'badge' => 'mWP-only'` (rendered via
   the `gv-badge` component) with an explanatory note and sets `'enabled' => false`.

A module can be gated by more than one thing at once — `spam-protection` is
both `mwp_only` and has a legacy standalone plugin — so the `note` field
accumulates reasons rather than reporting only the first (WPIN-8862). The
reasons aren't peers, so this isn't a blind concatenation: a missing bootstrap
("Not available yet.") makes the other two unactionable, since there is nothing
present to control on any plan, and therefore wins outright. Legacy-active and
`mwp_blocked` are both actionable and independent, so those two do combine —
legacy first, because deactivating the standalone plugin is the step that has
to happen before the MWP gate is even reachable. Before this, a doubly gated
row could render the `mWP-only` badge above text that never mentioned MWP.

`'enabled'` (the checkbox's checked state) factors in this gate: it's
`persisted_enabled && ! $mwp_blocked`, not just the persisted
`onecom_disabled_modules` option (WPIN-8907). A brand-new, never-toggled
non-MWP site has nothing in that option yet, so without this the checkbox
rendered `checked disabled` at once — looked on, but the module's actual
filtering classes never load (gated separately, and correctly, by
`groupone_sp_is_premium()` inside `modules/spam-protection/spam-protection.php`)
and the checkbox couldn't be clicked to discover why. The persisted option
itself is untouched by this: a site that had `spam-protection` enabled and
later loses MWP status keeps `spam-protection` out of
`onecom_disabled_modules`, so the checkbox displays enabled again the moment
MWP status returns, without the customer having to re-toggle it.

The checkbox stays `'toggleable' => true` (not HTML-`disabled`) even while
`mwp_blocked`, specifically so a customer attempting to turn it on reaches
the live AJAX gate below instead of hitting a dead control. The gate is
enforced server-side too, not just by what the checkbox shows:
`groupone_ajax_toggle_module()` rejects an `enabled=1` POST for an
`mwp_only` module when the MWP check fails (error code `mwp_required`) — the
checkbox's displayed state alone doesn't stop a direct POST to
`admin-ajax.php`. Disabling an `mwp_only` module is always allowed.

The row's badge/note (whether the checkbox even looks enabled) and the
AJAX gate deliberately use `ismWP()` differently:

- **Row rendering** (`get_module_toggles()`) calls plain `ismWP()`, which
  reads a 12h-cached site transient (`inc/lib/validator.php`) — an upgrade to
  MWP can take up to that long to visibly check the checkbox, an existing,
  accepted staleness window already relied on elsewhere on this same
  template.
- **The AJAX gate** (WPIN-8899) calls `ismWP( true )` instead, forcing a live
  call to one.com at the exact moment a customer tries to turn the module on
  — a customer who just upgraded doesn't have to wait out the stale cache
  from the row rendering, and a subscription that lapsed seconds ago is
  still caught even if the cache hasn't expired yet. On `mwp_required`, the
  Home page shows the same "upgrade to Managed WordPress" banner used on the
  Spam Protection settings page (`groupone_sp_mwp_upsell_banner_html()` in
  `inc/functions.php`, shared by both surfaces) instead of the generic
  save-failure toast. If the live call itself fails (network/API error),
  `ismWP( true )` returns `null` rather than `false`, and the gate fails
  closed with a distinct `mwp_check_failed` error — a transient outage
  shows a "couldn't verify, try again" message, not the upsell banner, and
  never silently falls back to a stale cached answer.

### Spam Protection fails open when the API gives no verdict (WPIN-8949)

`GroupOne_Sp::sp_api_check()` has **three** return shapes, and only one of
them carries a verdict:

1. **No usable response** — transport error (`curl_error()`), or `json_decode()`
   didn't produce an array (HTTP 500 with an HTML body, a truncated response,
   any non-JSON payload). Returns
   `array( 'data' => null, 'error' => …, 'success' => false )`.
2. **API answered, but not successfully** (`empty( $response['success'] )`) —
   returns the API's own body verbatim. Not an outage at all.
3. **API answered successfully** — returns `$response['data'] ?? null`, which
   carries `is_spam` and `reason`, but is `null` if the payload had no `data`.

Only shape 3 has an `is_spam` key, and even then not guaranteed. Shapes 1, 2,
and a dataless 3 have **no `is_spam` key at all**.

`GroupOne_SpApiCheck::oc_form_submission_check()` requires
`isset( $oc_check['is_spam'] )` on both verdict branches, so a response with
no verdict falls through to the `else` and returns `false` — **the submission
is allowed through**.

This is a deliberate, confirmed product decision, not an accident of control
flow. It is documented here because the previous behaviour was the opposite
and looked intentional: the original conditions were `false !== $oc_check['is_spam']`
and `false === $oc_check['is_spam']`, which are exhaustive over every possible
value, so the `else` was unreachable. On a missing key the read yielded `null`,
`false !== null` is true, and the first branch ran — meaning **every API
outage was treated as a positive spam verdict and served legitimate visitors
`wp_die( 'Spam detected', 403 )`**. Anyone re-reading that code without this
note could plausibly restore the old conditions thinking the guard was
redundant.

Trade-off, stated explicitly: while the API is unreachable, form submissions
are not spam-filtered. That is preferred over denying service to every real
visitor for the duration of an outage.

**Note the deliberate asymmetry with the MWP gate above**, which fails
*closed* (`mwp_check_failed`) when its live check fails. The two are opposite
on purpose, because the cost of being wrong is not symmetric:

- The MWP gate guards a **paid entitlement**. Failing open hands out an
  MWP-plan perk to a site that may not be entitled to it, and the cost of
  failing closed is a retryable "couldn't verify" message.
- The spam check guards against **abuse**. Failing closed blocks paying
  customers' real visitors from submitting anything at all, and the cost of
  failing open is unfiltered submissions during a window that is already
  degraded.

Neither should be "made consistent" with the other without revisiting that
reasoning.

## Global symbol namespacing (WPIN-8758, shortened under the GroupOne rebrand)

Each module was ported from its legacy standalone plugin with most global
class/function/constant names kept as-is. `groupone_load_merged_modules()`
prevents the module's own bootstrap from ever running while the legacy
plugin is active, but that guard cannot help against a name collision at the
point a legacy plugin gets *activated* while the hub is already active and
already declared those same names: WordPress's `plugin_sandbox_scrape()`
directly includes the legacy plugin's file to check for fatals, regardless
of the registry's deferred-loading logic, and if both sides declare the same
global name the result is `PHP Fatal error: Cannot redeclare ...`.

The fix is to rename the module's copy of every symbol that collides with
its legacy plugin. This was first done (WPIN-8758) with a `Onecom_Themes_Plugins_`
(classes) / `onecom_themes_plugins_` (functions/constants) prefix, and was
later shortened as part of the GroupOne whitelabel initiative to a plain
`GroupOne_` (classes/constants) / `groupone_` (functions) prefix across the
whole hub plugin and the `inc/lib` submodule — never touch the legacy
plugin's code, since those repos are being phased out. WordPress hook name
strings, AJAX `action` values, and WP option/meta keys are untouched by this
rename — only actual PHP symbol declarations and their in-code references.

A few call sites outside the renamed module itself hardcode the class name
and need updating whenever a symbol is renamed: check `modules/cookie-banner/`
(a `class_exists()` check gating a "Purge Performance Cache" link, which now
reads `class_exists( 'GroupOne_VCaching' ) || class_exists( 'VCachingOC' )` —
the first operand is the hub's own renamed class, the second, bare name is
the separate standalone legacy plugin and must never be touched) and the
`inc/lib` submodule's `onecom-nested-menu.php` (an `add_submenu_page()`
callback array referencing the same two class names by string) before
assuming a rename is complete.

## Legacy-plugin takeover: `inc/legacy-plugin-takeover.php` (WPIN-8758)

With the symbol collisions gone (see the note on load-order-dependent
`inc/lib` collisions in
[`known-issues-and-verification.md`](known-issues-and-verification.md)
for the one class of collision that isn't just a rename away), both the
hub and a legacy plugin *can* be active at once without fataling — but a
site should still only ever run one implementation of each feature. This
file keeps exactly one active, in both directions:

- **Hub activates while a legacy plugin is already active**:
  `groupone_deactivate_legacy_plugins()` runs from the hub's own
  `groupone_plugin_activation()` callback and deactivates every legacy plugin
  found active, before the modules below it seed their defaults.
- **A legacy plugin activates while the hub is already active**:
  `groupone_block_legacy_plugin_activation()`, hooked to WordPress's
  `activated_plugin` action, detects the plugin is one of the three the hub
  absorbed, deactivates it again immediately, and queues its file path in the
  `onecom_takeover_blocked_plugins` option. `groupone_render_legacy_takeover_notice()`
  (hooked to `admin_notices`) renders a dismissible notice for each queued
  plugin on the next admin page load and clears the option, so it appears
  exactly once.

- **The hub is updated while a legacy plugin is active**:
  `groupone_takeover_legacy_plugins_on_update()` runs from the upgrade routine
  above, deliberately *after* the blanket module disable — it deactivates each
  active legacy plugin and hands the feature to the matching module with
  `groupone_set_module_enabled( $slug, true )`, which the blanket disable would
  otherwise have left off on both sides.

## Reconciliation: handing a feature to its module (WPIN-9113)

The rule: **whenever the hub takes a legacy plugin's feature away, it hands that
feature to the merged module.** A legacy plugin that stops being the provider has
its module slug removed from `onecom_disabled_modules`, so the module takes over
on the next page load.

There are four ways a legacy plugin can stop providing — the activation takeover,
the block-on-activation, the cleanup notice's "Deactivate & delete now", and the
owner deactivating it by hand from Plugins. All four go through core's
`deactivate_plugins()`, so a single listener covers every one of them:
`groupone_enable_module_for_legacy_plugin()`, hooked to `deactivated_plugin`.
That is the only reconciliation point; no call site keeps its own copy.

Three deliberate properties:

- **It only ever enables.** Reconciliation never adds a slug to
  `onecom_disabled_modules`. A site with no legacy plugin at all keeps the
  fresh-install default above.
- **Detection is `active_plugins` only**, via `groupone_legacy_plugin_active()`.
  Leftover option rows (`varnish_caching_enable`, `onecom_under_construction_info`,
  …) are deliberately not sniffed: they cannot tell "was using the feature" apart
  from "installed it once and turned it off".
- **The owner's own toggle wins.** `groupone_ajax_toggle_module()` records the
  slug in `onecom_modules_user_choice` — it is the only writer of that option —
  and reconciliation skips any slug listed there, so a module switched off from
  the Home page is not re-enabled behind the owner's back.

  Handing over is gated by `groupone_module_mwp_blocked()`: an `mwp_only` module
  stays disabled on a non-mWP site even though the legacy plugin it replaces was
  active. The module loader applies the same gate, so enabling the slug there
  would only leave the persisted option claiming a module that never loads.

One ordering consequence on activation: `groupone_deactivate_legacy_plugins()`
runs before the WPIN-9027 `mwp_only` seeding, and reconciliation writes
`onecom_disabled_modules` as it goes — so that seeding can no longer read the
option's absence as "this site is fresh". `groupone_plugin_activation()` captures
whether the option existed *before* the takeover and seeds against that, merging
rather than overwriting.

This guarantees the site never ends up running both implementations of a
feature at once, and never shows a raw fatal error while doing so. The
cleanup layer built on top of it (WPIN-8737, below) handles what happens to
the legacy plugin's files and settings afterward.

## Legacy-plugin cleanup: `inc/legacy-plugin-takeover.php` (WPIN-8737)

Deactivating a legacy plugin (above) leaves its files on disk indefinitely.
This layer prompts the admin to remove them entirely, independent of the
takeover direction that got them deactivated:

- `groupone_detected_legacy_plugins()` returns every legacy plugin file
  `groupone_merged_modules_map()` knows about that still exists on disk
  (`file_exists()`), regardless of active state — unlike the one-shot notice
  above, this is recomputed live on every admin page load.
- `groupone_render_legacy_cleanup_notice()` (hooked to `admin_notices` /
  `network_admin_notices`) renders a persistent notice listing every detected
  plugin with a "Delete now" action, for as long as the
  detected list is non-empty. It never renders once nothing remains, and
  respects a per-admin-user 7-day snooze (`onecom_legacy_cleanup_dismissed_until`
  user meta, set by the notice's "Remind me later" link /
  `groupone_dismiss_legacy_cleanup_notice()`) rather than a site-wide dismissal.
- `groupone_handle_cleanup_legacy_plugins()` (the `admin-post.php` action
  behind "Delete now") does the actual removal. Per requested
  plugin: snapshots any `protected_options` declared for that module in
  `groupone_merged_modules_map()`, calls WordPress's own `delete_plugins()`,
  restores any snapshotted option that came back empty (the legacy plugin's
  own uninstall routine — e.g. `onecom-under-construction`'s `uninstall.php`
  unconditionally deletes `onecom_under_construction_info`, confirmed by
  reading that file directly — runs as part of `delete_plugins()`, before
  this restore step), clears any `cron_hooks` declared for that module (empty
  for all three today — none of the three legacy plugins schedule any
  `wp_schedule_event()`, confirmed by reading each one's source), and pushes
  a `cleanup_success`/`cleanup_failure` stats event per plugin. Per-plugin
  results are shown as a one-time notice on the next page load
  (`groupone_render_legacy_cleanup_results_notice()`, via a transient) since
  the admin-post handler itself ends in a redirect.
- `groupone_push_legacy_plugin_stats()` is the shared helper (guarded by
  `class_exists( 'GroupOne_PushStats' )`, same convention as
  `modules/maintenance-mode/classes/class-ocuc-stats.php`) both this cleanup
  flow and the takeover functions above use to push `takeover` /
  `cleanup_success` / `cleanup_failure` events via
  `GroupOne_PushStats::push_stats_event_themes_and_plugins()`.
- `delete_plugins()` requires direct filesystem write access to actually
  delete anything; if a hosting environment ever lacks it, WordPress prints
  its own credentials-request page and exits from inside that call before
  reaching this file's restore/stats logic at all. That is an acceptable
  failure mode rather than a data-loss risk — the plugin's uninstall routine
  and file removal both happen only *after* `WP_Filesystem()` connects
  successfully, so nothing has been touched yet if a request exits at the
  credentials prompt.
- Multisite: `deactivate_plugins( $legacy, false )` is called with
  `$network_wide` left at its default (`null`) deliberately — WordPress core
  detects network-wide activation itself in that case
  (`is_plugin_active_for_network()`) and clears `active_sitewide_plugins`
  correctly either way. An earlier draft of this cleanup flow passed an
  explicit `network_wide` argument here to "fix" a suspected multisite gap;
  reading `wp-admin/includes/plugin.php` directly showed the gap never
  existed (the default already handles it), and explicitly passing `true`
  would have skipped a defensive fallback core also runs, so the explicit
  argument was removed again.
- Rollback safety: the hub's own `register_deactivation_hook()`
  (`groupone_plugin_deactivation()` in `onecom-themes-plugins.php`) purges
  Performance Cache via `GroupOne_VCaching::purge_theme_cache()` — the same
  public method WordPress's own `switch_theme` action already triggers for
  the same "site config changed, cached output may be stale" reasoning.
  Maintenance Mode needs no equivalent: its front-end override only runs
  from the module's own bootstrap, which stops loading the moment the hub
  deactivates.

## Menu gating in the `inc/lib` submodule (one.com-wp-premium-validator)

`onecom-nested-menu.php` and `class-onecom-centralized-menu.php` — the
classes that build the `onecom-wp` admin menu and its submenus — gate the
Performance Cache / Spam Protection / Maintenance Mode submenu entries with
an `oc_feature_active( $module_slug, $legacy_plugin_file )` helper instead of
a bare `is_plugin_active( $legacy_plugin_file )` call. `oc_feature_active()`
is true if either `groupone_module_active()` (guarded with
`function_exists()`, since this submodule ships independently of the hub) or
`is_plugin_active()` on the legacy plugin file is true. This makes the
submodule safe to release independently in both directions: an older hub
without the module registry still gets the previous `is_plugin_active()`
behavior, and the module-aware hub gets menu items for merged modules with
no standalone plugin involved at all.

Note: `oc_feature_active()` / `oc_module_active()` are class methods, not
free functions, so they were kept as-is by the GroupOne rebrand (which only
covers `define()`d constants, free functions, and classes) — do not confuse
these submodule *methods* with the hub's own free functions
`groupone_module_active()` / `groupone_get_disabled_modules()` in
`inc/module-registry.php`, which they call into.

See also [`known-issues-and-verification.md`](known-issues-and-verification.md)
for gotchas around manually verifying REST-security fixes on wpin10, an open
vendor-bundle issue (WPIN-8492), and a PHP function-hoisting pitfall in this
repo's framework-free tests.

### Marketplace "new" dot dismissal (WPIN-8866)

The blue dot next to the sidebar **Marketplace** entry is not rendered by this
repo at all. It is a bare `<div class="gv-indicator"></div>` inside the submenu
*title* that `GroupOne_Nested_Menu::onecom_register_menu()` builds in
`inc/lib/onecom-nested-menu.php` (added by validator MR !348, WPIN-7899). The
hub registers its own `onecom-marketplace` entry on `admin_menu` at priority
`-1`, but the submodule removes it again at priority `14` and re-adds its own at
`15`, so the submodule's markup is what customers actually see — grep the hub
for `gv-indicator` and you will find nothing.

The dot is now dismissed per user:

- `GroupOne_Nested_Menu::onecom_dismiss_marketplace_dot()` runs on `admin_init`
  and writes the `onecom_marketplace_dot_seen` user meta when
  `$_GET['page'] === 'onecom-marketplace'`.
- `onecom_register_menu()` omits the indicator once
  `onecom_marketplace_dot_seen()` is true.
- `admin_init` fires **before** `admin_menu`, so the request that dismisses the
  dot already paints the menu without it — the dot does not linger for one extra
  page load.

Only the inner `.gv-indicator` element is dropped. The outer
`#onecom_marketplace` wrapper and its `.gv-text-indicator` span always stay:
`inc/class-onecom-walkthrough.php` anchors a walkthrough pointer on
`'#onecom_marketplace'`, and the submodule's own `onecom_css_assets()` styles
the label through that id.

Because the change lives in the submodule, it reaches customers only once the
`inc/lib` gitlink here is bumped. The validator's `update-submodules-in-plugins`
CI job opens that bump automatically on every push to validator `master`.

## Legacy plugin changelog history (WPIN-8789)

`info/changelog.html` and `readme.txt` each carry a "Legacy standalone plugin
history (absorbed in v5.2.0)" section appended after the hub's own changelog,
containing every historical version entry from the three absorbed plugins
(`onecom-vcache`, `onecom-spam-protection`, `onecom-under-construction`),
grouped by module and suffixed `[Module module]` on each version header for
traceability. Entries are not interleaved chronologically with the hub's own
history or with each other: `onecom-vcache`'s changelog carries no dates at
all, in neither `info/changelog.html` nor `readme.txt` (unlike
`onecom-spam-protection` and `onecom-under-construction`, which both date
every entry in their `readme.txt`), which makes a safe date-based interleave
across all four histories impossible without fabricating provenance for
`onecom-vcache`, so each legacy plugin's history is kept as its own block in
its own original (undated or dated, newest-first) order instead.

## Installable build artifact: `package-plugin-zip` CI job (WPIN-8903)

Every pipeline run (MR pipelines, branch pushes and tags) runs a
`package-plugin-zip` job that publishes an `onecom-themes-plugins/` directory
as a GitLab job artifact. Download it from the job's page (pipeline →
`package-plugin-zip` → Job artifacts → Download), unzip the downloaded file
**once**, and drop the resulting `onecom-themes-plugins/` folder straight
into `wp-content/plugins/` (or zip it yourself first if you want to use
wp-admin → Plugins → Add New → Upload Plugin instead).

That single unzip is unavoidable — GitLab always wraps a downloaded job
artifact in one zip of its own — but it's also the *only* one: the job hands
GitLab the assembled directory itself, not a zip file, so there's nothing
nested inside GitLab's wrapper to unzip a second time. (An earlier version of
this job built its own zip first and declared *that* as the artifact, which
meant downloaders had to unzip GitLab's wrapper, then unzip the file that
came out of it, before reaching the actual plugin folder — reported after
the first real install attempt.)

Before this job existed, the only way to try an MR's changes on a test site
was a manual rsync deploy — the hub's own release tooling
(`deploy_onecom_plugin_goclick.sh`) downloads GitLab's `repository/archive.zip`
API, and `git archive` (which that API wraps) never descends into git
submodules, so a zip built that way is always missing `inc/lib`
(`one.com-wp-premium-validator`) entirely.

The job works around this by running `git archive HEAD` twice — once for the
hub, once inside `inc/lib` after an explicit `git submodule update --init`
(submodule checkout isn't automatic here; `GIT_SUBMODULE_STRATEGY` isn't set
repo-wide, so this mirrors the same workaround the `unit-test` job already
needs) — extracting both directly into `onecom-themes-plugins/`. Each side's
own `.gitattributes` `export-ignore` rules apply as normal, so `tests/`,
`src/`, `node_modules/` etc. stay out of the artifact on both the hub and
submodule sides.

## Admin Toolbar Dropdown Menu Integration (WPIN-8996)

To avoid cluttering the WordPress admin toolbar, standalone top-level items from merged modules are nested inside the main `one.com` dropdown (`GROUPONE_PLUGIN_DOMAIN` / `onecom-wp`):
- **Maintenance Mode** (`ocuc_options`): Added via `GroupOne_UC_Toolbar::add_toolbar_link()` as a child of `onecom-wp` at priority `101`.
- **Performance Cache** (`purge-all-varnish-cache`): Added via `GroupOne_VCaching_Base::purge_varnish_cache_all_adminbar()` as a child of `onecom-wp` at priority `101`. Child purge options (`Clear cache` and `Clear CDN`) remain attached under `purge-all-varnish-cache`.
- **Hook Priority Ordering**: The hub registers `add_one_bar_items` on `admin_bar_menu` at priority `100` to instantiate the parent `onecom-wp` node. Merged modules register at priority `101` to guarantee the parent node exists before children are appended.

### Sub-menu contents and order (WPIN-9015)

`WP_Admin_Bar::_bind()` appends children strictly in insertion order, so a node's
position in the dropdown is decided by the `admin_bar_menu` priority of whichever
callback registered it. The dropdown is therefore built in three passes:

| Priority | Callback | Nodes |
| --- | --- | --- |
| `100` | `add_one_bar_items()` (hub) | `onecom-marketplace`, `onecom-wp-staging` |
| `101` | `GroupOne_UC_Toolbar::add_toolbar_link()` | `ocuc_options` |
| `101` | `GroupOne_VCaching_Base::purge_varnish_cache_all_adminbar()` | `purge-all-varnish-cache` (+ its own children) |
| `110` | `OneStaging::stagingToolbar()` | `onecom-staging` — *root-level node, not a child of this dropdown* |
| `999` | `add_one_bar_footer_items()` (hub) | `one-cp`, `one-wp-support` |

- **Control Panel and Guides & FAQ run last.** `one-cp` and `one-wp-support` live
  in `add_one_bar_footer_items()` at priority `999` rather than alongside the rest
  at `100`. At `100` they were registered before the module callbacks and so ended
  up stranded in the middle of the list. Priority `999` keeps them at the bottom
  regardless of which optional modules are active, and leaves headroom for future
  modules to register in between without another reshuffle.
- **Marketplace, not Plugins.** The `onecom-marketplace` node is labelled
  `Marketplace` so it matches the Marketplace entry in the left-hand admin sidebar
  (both point at `admin.php?page=onecom-marketplace`).
- **No Themes entry.** The `onecom-wp-themes` toolbar node was removed as
  redundant. The sidebar Themes entry and the `onecom-wp-themes` admin page itself
  are unaffected.
- **Shared visibility guard**: both hub callbacks gate on `one_bar_items_visible()`
  (single-site-or-network-admin plus `manage_options`) so their rules cannot drift.
  `add_one_bar_footer_items()` additionally no-ops when the parent `onecom-wp` node
  is absent.
- **No separate top-right shortcut.** The `onecom-marketplace-shortcut` node
  (WPIN-8525) was parented to `top-secondary` rather than to this dropdown. It was
  removed in WPIN-9103 because it overlapped and wrapped over the other top-right
  toolbar items on narrower screens (WPIN-9099); the `onecom-marketplace` entry
  above is now the only Marketplace route in the toolbar.
- Priority `999` also carries `GroupOne_VCaching::remove_toolbar_node()`, but it only
  calls `remove_node()` behind a `$_REQUEST` gate, so relative order with
  `add_one_bar_footer_items()` does not matter.
- **Translations:** `Marketplace`, `My products`, `Clear Performance Cache`, and `Maintenance Mode is ON/OFF` are included in `languages/onecom-wp.pot` (WPIN-9032).

## MCP Server & Abilities Module Integration (WPIN-8616, WPIN-8751)

The one.com hub plugin includes an integrated Model Context Protocol (MCP) server that exposes WordPress core capabilities and curated add-on features to AI assistants:
- **Server Registration**: `Onecom_Mcp::init()` registers the branded `onecom-mcp` server using bundled `wordpress/mcp-adapter` and `wordpress/php-mcp-schema` libraries.
- **Core Site Abilities** (`modules/abilities/`):
  - **Posts**: `site/get-post`, `site/list-posts`, `site/create-post`, `site/update-post`, `site/delete-post`
  - **Menus**: `site/list-menus`, `site/get-menu`, `site/create-menu`, `site/delete-menu`, `site/add-menu-item`, `site/remove-menu-item`
  - **Plugins**: `site/list-plugins`, `site/activate-plugin`, `site/deactivate-plugin`, `site/update-plugin` (with automatic `wp_update_plugins()` cache refresh on sequential runs)
  - **Themes**: `site/list-themes`, `site/activate-theme`, `site/update-theme`
- **Marketplace & Premium Add-on Abilities**: Exposes subscription-gated capabilities (WP Rocket, Rank Math PRO) and marketplace catalogue actions filtered dynamically via `onecom_mcp_ability_ids`.

## Contextual Recommendation Screen Rules (WPIN-8787, WPIN-8751)

Marketplace awareness banner notices rendered by `GroupOne_Contextual_Recommendations` are restricted strictly to:
- `plugins.php` (`$screen->base === 'plugins'`)
- `themes.php` (`$screen->base === 'themes'`)

Notices are intentionally suppressed on the WordPress dashboard (`dashboard`), post/page edit screens, and one.com plugin pages to avoid banner fatigue.




## Translation Catalogue & String Extraction (WPIN-9032)

`languages/onecom-wp.pot` is the canonical Gettext template for the merged one.com hub plugin (domain `onecom-wp`).

### Generating the POT File

To regenerate `languages/onecom-wp.pot` from source code across the hub, `inc/`, and all merged `modules/`:

```bash
wp i18n make-pot . languages/onecom-wp.pot --domain=onecom-wp --skip-js --exclude="vendor,node_modules,tests"
```

### Merging and Compiling Localized Catalogues

When `onecom-wp.pot` is updated:
1. Merge new translatable strings into existing locale `.po` files without losing existing translations:
   ```bash
   msgmerge --no-fuzzy-matching languages/onecom-wp-<locale>.po languages/onecom-wp.pot -o languages/onecom-wp-<locale>.po
   ```
2. Recompile binary `.mo` files loaded by WordPress runtime:
   ```bash
   msgfmt -v languages/onecom-wp-<locale>.po -o languages/onecom-wp-<locale>.mo
   ```
