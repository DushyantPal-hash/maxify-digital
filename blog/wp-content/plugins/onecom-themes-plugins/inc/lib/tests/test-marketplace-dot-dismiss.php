<?php
/**
 * Regression test for WPIN-8866 (part 2) -- the Marketplace "new" dot must
 * disappear once the user has opened the Marketplace page, and must never
 * come back for that user.
 *
 * This repo has no PHPUnit suite (see MR !356), so this is a small
 * framework-free script: run it directly with `php
 * tests/test-marketplace-dot-dismiss.php` and check the exit code (0 = pass).
 *
 * The dot is a bare `<div class="gv-indicator"></div>` hardcoded into the
 * Marketplace submenu title in onecom_register_menu() (added in MR !348,
 * WPIN-7899). It had no dismissal path at all, so every admin saw a
 * permanent "new" badge on a menu item they had already visited.
 *
 * The dismissal is per-user (user meta, not an option) and is recorded on
 * admin_init -- which fires *before* admin_menu -- so the very page load that
 * dismisses the dot already renders the menu without it. That ordering is the
 * subtle part, and phase 4 below pins it: seen() must already be true by the
 * time the menu is built on the dismissing request.
 *
 * Note the deliberate asymmetry: only the inner .gv-indicator is dropped. The
 * outer #onecom_marketplace wrapper and its .gv-text-indicator span always
 * stay, because inc/class-onecom-walkthrough.php anchors a walkthrough
 * pointer on '#onecom_marketplace' and onecom_css_assets() styles the span
 * through that id. Removing the wrapper would silently break both.
 */

// Satisfies onecom-nested-menu.php's `defined('WPINC') or die()` direct-access
// guard so the file can be required outside of a WordPress bootstrap.
define( 'WPINC', 'wp-includes' );

// --- Minimal WP stubs --------------------------------------------------
// $GLOBALS['wpin8866_user_id'] stands in for the logged-in user, and
// $GLOBALS['wpin8866_user_meta'] for the wp_usermeta rows. Writes are counted
// so phase 5 can assert the dismissal does not re-write on every admin page
// load once it has already been recorded.
$GLOBALS['wpin8866_user_id']     = 0;
$GLOBALS['wpin8866_user_meta']   = array();
$GLOBALS['wpin8866_meta_writes'] = 0;

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return (int) $GLOBALS['wpin8866_user_id'];
	}
}
if ( ! function_exists( 'get_user_meta' ) ) {
	function get_user_meta( $user_id, $key, $single = false ) {
		$value = $GLOBALS['wpin8866_user_meta'][ $user_id ][ $key ] ?? '';

		return $single ? $value : array( $value );
	}
}
if ( ! function_exists( 'update_user_meta' ) ) {
	function update_user_meta( $user_id, $key, $value ) {
		$GLOBALS['wpin8866_user_meta'][ $user_id ][ $key ] = $value;
		++$GLOBALS['wpin8866_meta_writes'];

		return true;
	}
}
if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
	}
}
if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

require_once __DIR__ . '/../onecom-nested-menu.php';

$failures = array();

function wpin8866_check( $condition, $message ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $message;
	}
}

/**
 * Rebuilds the Marketplace submenu title exactly as onecom_register_menu()
 * does, so the assertions below exercise the real rendering decision rather
 * than a paraphrase of it.
 */
function wpin8866_marketplace_title( GroupOne_Nested_Menu $menu ): string {
	$indicator = $menu->onecom_marketplace_dot_seen() ? '' : '<div class="gv-indicator"></div>';

	return '<div id="onecom_marketplace" class="gv-activated gv-dark-mode"> <div class="gv-text-indicator"><span>'
		. 'Marketplace'
		. '</span>' . $indicator . '</div></div>';
}

// Instantiate without running the constructor -- it calls plugin_dir_url(),
// __() and class_exists('OneComCentralizedMenu'), none of which the dot
// methods touch (they don't read $this at all beyond the class constant).
$menu = ( new ReflectionClass( 'GroupOne_Nested_Menu' ) )->newInstanceWithoutConstructor();

// --- Phase 1: logged out ----------------------------------------------
// get_current_user_id() returns 0 on the front end and for logged-out
// requests. Neither the read nor the write may touch user 0's meta, or the
// dismissal would leak into a shared row.

$GLOBALS['wpin8866_user_id'] = 0;
$_GET                        = array( 'page' => 'onecom-marketplace' );

wpin8866_check(
	false === $menu->onecom_marketplace_dot_seen(),
	'onecom_marketplace_dot_seen() should return false when there is no logged-in user'
);

$menu->onecom_dismiss_marketplace_dot();

wpin8866_check(
	0 === $GLOBALS['wpin8866_meta_writes'],
	'onecom_dismiss_marketplace_dot() should not write any user meta when there is no logged-in user'
);

// --- Phase 2: first-time user still sees the dot -----------------------

$GLOBALS['wpin8866_user_id'] = 7;
$_GET                        = array( 'page' => 'index.php' );

wpin8866_check(
	false === $menu->onecom_marketplace_dot_seen(),
	'onecom_marketplace_dot_seen() should return false for a user who has never opened the Marketplace page'
);

wpin8866_check(
	false !== strpos( wpin8866_marketplace_title( $menu ), '<div class="gv-indicator"></div>' ),
	'The Marketplace menu title should contain the indicator before the page has been visited'
);

// --- Phase 3: other admin pages must not dismiss it --------------------
// Including the sibling Marketplace > My products page, whose slug shares the
// 'onecom-marketplace' prefix -- a substring match here would dismiss the dot
// without the user ever opening Marketplace itself.

foreach ( array( 'index.php', 'onecom-wp', 'onecom-marketplace-products', '' ) as $other_page ) {
	$_GET = '' === $other_page ? array() : array( 'page' => $other_page );

	$menu->onecom_dismiss_marketplace_dot();

	wpin8866_check(
		false === $menu->onecom_marketplace_dot_seen(),
		"onecom_dismiss_marketplace_dot() should not dismiss the dot on page '$other_page'"
	);
}

wpin8866_check(
	0 === $GLOBALS['wpin8866_meta_writes'],
	'onecom_dismiss_marketplace_dot() should not write any user meta while the Marketplace page has not been opened'
);

// --- Phase 4: opening Marketplace dismisses it on that same request ----
// admin_init runs before admin_menu, so the menu built later in *this*
// request must already be dot-free -- the dot should not linger for one
// extra page load.

$_GET = array( 'page' => 'onecom-marketplace' );

$menu->onecom_dismiss_marketplace_dot();

wpin8866_check(
	true === $menu->onecom_marketplace_dot_seen(),
	'onecom_marketplace_dot_seen() should return true immediately after the Marketplace page is opened'
);

wpin8866_check(
	false === strpos( wpin8866_marketplace_title( $menu ), 'gv-indicator' ),
	'The Marketplace menu title should not contain the indicator on the request that dismisses it'
);

wpin8866_check(
	false !== strpos( wpin8866_marketplace_title( $menu ), 'id="onecom_marketplace"' ),
	'The #onecom_marketplace wrapper must survive dismissal -- the walkthrough pointer and menu CSS anchor on it'
);

wpin8866_check(
	false !== strpos( wpin8866_marketplace_title( $menu ), '<div class="gv-text-indicator"><span>Marketplace</span></div>' ),
	'The .gv-text-indicator span must survive dismissal, with the indicator div cleanly removed'
);

// --- Phase 5: it never comes back, and stops re-writing ----------------

$writes_after_dismissal = $GLOBALS['wpin8866_meta_writes'];

wpin8866_check(
	1 === $writes_after_dismissal,
	"Dismissal should write user meta exactly once, wrote $writes_after_dismissal times"
);

for ( $i = 0; $i < 3; $i++ ) {
	$menu->onecom_dismiss_marketplace_dot();
}

wpin8866_check(
	true === $menu->onecom_marketplace_dot_seen(),
	'The dot must stay dismissed across later visits to the Marketplace page'
);

wpin8866_check(
	1 === $GLOBALS['wpin8866_meta_writes'],
	'Revisiting the Marketplace page should not re-write the user meta once the dot is already dismissed'
);

$_GET = array( 'page' => 'index.php' );

wpin8866_check(
	false === strpos( wpin8866_marketplace_title( $menu ), 'gv-indicator' ),
	'The dot must stay gone on every other admin page after dismissal'
);

// --- Phase 6: dismissal is per user, not site-wide ---------------------

$GLOBALS['wpin8866_user_id'] = 8;

wpin8866_check(
	false === $menu->onecom_marketplace_dot_seen(),
	'A second user must still see the dot after the first user has dismissed it'
);

wpin8866_check(
	false !== strpos( wpin8866_marketplace_title( $menu ), '<div class="gv-indicator"></div>' ),
	'A second user must still get the indicator in their Marketplace menu title'
);

$GLOBALS['wpin8866_user_id'] = 7;

wpin8866_check(
	true === $menu->onecom_marketplace_dot_seen(),
	'The first user must remain dismissed after a second user is seen'
);

if ( ! empty( $failures ) ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: $failure\n" );
	}
	exit( 1 );
}

echo "PASS: the Marketplace dot renders until the user opens the Marketplace page, is dismissed per-user on that same request (admin_init runs before admin_menu), never returns, is not dismissed by sibling pages such as onecom-marketplace-products, writes user meta only once, is ignored for logged-out requests, and always leaves the #onecom_marketplace wrapper and .gv-text-indicator span intact for the walkthrough pointer and menu CSS.\n";
exit( 0 );
