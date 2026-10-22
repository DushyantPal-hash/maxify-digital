( function () {
	var data      = window.onecomImagifyPromoter || {};
	var dismissed = false;

	function buildBanner() {
		var banner = document.createElement( 'div' );
		banner.className = 'onecom-imagify-promoter';

		var header = document.createElement( 'div' );
		header.className = 'onecom-imagify-promoter__header';

		var title = document.createElement( 'strong' );
		title.className   = 'onecom-imagify-promoter__title';
		title.textContent = data.title;

		var dismissBtn = document.createElement( 'button' );
		dismissBtn.type        = 'button';
		dismissBtn.className   = 'onecom-imagify-promoter__dismiss';
		dismissBtn.textContent = '×';
		dismissBtn.setAttribute( 'aria-label', data.dismiss_label );
		dismissBtn.addEventListener( 'click', function () {
			dismissed = true;
			document.querySelectorAll( '.onecom-imagify-promoter' ).forEach( function ( el ) {
				el.remove();
			} );
			fetch( data.ajax_url, {
				method:  'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body:    new URLSearchParams( {
					action: 'onecom_dismiss_imagify_promoter',
					nonce:  data.nonce,
				} ),
			} );
		} );

		header.appendChild( title );
		header.appendChild( dismissBtn );

		var desc = document.createElement( 'p' );
		desc.className   = 'onecom-imagify-promoter__desc';
		desc.textContent = data.description;

		var cta = document.createElement( 'a' );
		cta.href        = data.plugins_page;
		cta.className   = 'onecom-imagify-promoter__cta button button-secondary';
		cta.target      = '_blank';
		cta.rel         = 'noopener noreferrer';
		cta.textContent = data.cta_label;

		banner.appendChild( header );
		banner.appendChild( desc );
		banner.appendChild( cta );

		return banner;
	}

	function injectBanner( container ) {
		if ( dismissed ) {
			return;
		}
		if ( container.querySelector( '.onecom-imagify-promoter' ) ) {
			return;
		}
		// Insert before .actions ("View media file | Edit more details | …").
		// Falls back to appending inside the container if .actions is not found.
		var actions = container.querySelector( '.actions' );
		if ( actions ) {
			actions.parentNode.insertBefore( buildBanner(), actions );
		} else {
			container.appendChild( buildBanner() );
		}
	}

	function scanAndInject( root ) {
		root.querySelectorAll( '.attachment-details' ).forEach( injectBanner );
	}

	// Media Library (upload.php): watch for .attachment-details added to the DOM.
	// Skip on media-new.php — that page has its own mediaNewObserver. Without this
	// guard, every completed upload row on media-new.php also contains an
	// .attachment-details element, and the main observer would inject a banner into
	// each one independently (one banner per file).
	if ( ! document.getElementById( 'media-items' ) ) {
		var observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					if ( node.classList && node.classList.contains( 'attachment-details' ) ) {
						injectBanner( node );
					}
					scanAndInject( node );
				} );
			} );
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	}

	// Add New Media (media-new.php): inject ONE banner after the first upload completes.
	// WordPress adds a .media-item child to #media-items for every file being uploaded.
	// Multiple files trigger multiple addedNodes events — we debounce so a single banner
	// is injected regardless of how many files are selected at once.
	( function initMediaNewHandler() {
		var mediaItems = document.getElementById( 'media-items' );
		if ( ! mediaItems ) {
			// Not on media-new.php, nothing to do.
			return;
		}

		var debounceTimer = null;

		function tryShowBanner() {
			if ( dismissed ) {
				return;
			}
			// DOM existence is the single gate — prevents duplicates regardless of timing.
			if ( document.querySelector( '.onecom-imagify-promoter' ) ) {
				return;
			}
			// Insert before #media-items so the banner is clearly visible.
			var parent = mediaItems.parentNode || document.getElementById( 'wpbody-content' ) || document.body;
			parent.insertBefore( buildBanner(), mediaItems );
		}

		// Watch for .media-item children added to #media-items.
		// clearTimeout + setTimeout debounces rapid multi-file additions into one injection.
		var mediaNewObserver = new MutationObserver( function ( mutations ) {
			var hasNewItem = false;
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( node.nodeType === 1 && node.classList && node.classList.contains( 'media-item' ) ) {
						hasNewItem = true;
					}
				} );
			} );

			if ( ! hasNewItem ) {
				return;
			}

			// Reset the timer on every new item so we fire once after the last item lands.
			clearTimeout( debounceTimer );
			debounceTimer = setTimeout( tryShowBanner, 500 );
		} );

		mediaNewObserver.observe( mediaItems, { childList: true } );
	} )();
} )();
