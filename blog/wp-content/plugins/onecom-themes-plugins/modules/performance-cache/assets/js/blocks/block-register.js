( function () {
	'use strict';

	// Hand-written, build-free replacement for the old webpack bundle: no JSX,
	// no transpile step, resolves everything off the wp global at execution time.
	var PanelSlot = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PanelSlot ) {
		return;
	}

	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var ToggleControl = wp.components.ToggleControl;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var registerPlugin = wp.plugins.registerPlugin;

	var strings = window.blockObject || {};
	var labelText = strings.label || 'Exclude from cache';
	var excludeText = strings.excludeText || 'This %s will be excluded from cache.';
	var includeText = strings.includeText || 'This %s will be cached.';

	function ExcludeCachePanel() {
		// useSelect/useDispatch run unconditionally, before any branching, so the
		// post-type guard below cannot change the hook count between renders.
		var editorState = useSelect( function ( select ) {
			return {
				meta: select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {},
				postType: select( 'core/editor' ).getCurrentPostType(),
			};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		var postType = editorState.postType;
		var meta = editorState.meta;

		if ( 'post' !== postType && 'page' !== postType ) {
			return null;
		}

		var excluded = !! meta._oct_exclude_from_cache;

		return el(
			PanelSlot,
			{ title: __( 'Performance Cache', 'vcaching' ), icon: 'performance', initialOpen: true },
			el( ToggleControl, {
				label: labelText,
				help: excluded ? sprintf( excludeText, postType ) : sprintf( includeText, postType ),
				checked: excluded,
				onChange: function ( value ) {
					editPost( { meta: { _oct_exclude_from_cache: !! value } } );
				},
			} )
		);
	}

	registerPlugin( 'onecom-exclude-cache-plugin', {
		render: ExcludeCachePanel,
	} );
} )();
