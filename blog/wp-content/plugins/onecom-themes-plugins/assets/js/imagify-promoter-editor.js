( function () {
	var addFilter          = wp.hooks.addFilter;
	var createHOC          = wp.compose.createHigherOrderComponent;
	var createElement      = wp.element.createElement;
	var useState           = wp.element.useState;
	var Fragment           = wp.element.Fragment;
	var InspectorControls  = wp.blockEditor.InspectorControls;
	var PanelBody          = wp.components.PanelBody;
	var data               = window.onecomImagifyPromoter || {};

	var IMAGE_BLOCKS = [
		'core/image',
		'core/cover',
		'core/media-text',
		'core/gallery',
		'core/post-featured-image',
	];

	var withImagifyPromoter = createHOC( function ( BlockEdit ) {
		return function ( props ) {
			if ( IMAGE_BLOCKS.indexOf( props.name ) === -1 ) {
				return createElement( BlockEdit, props );
			}

			var visibleState = useState( true );
			var isVisible    = visibleState[ 0 ];
			var setVisible   = visibleState[ 1 ];

			function handleDismiss() {
				setVisible( false );
				fetch( data.ajax_url, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: new URLSearchParams( {
						action: 'onecom_dismiss_imagify_promoter',
						nonce:  data.nonce,
					} ),
				} );
			}

			return createElement(
				Fragment,
				null,
				createElement( BlockEdit, props ),
				isVisible ? createElement(
					InspectorControls,
					// Inspector tab group is resolved server-side via version_compare() to avoid
					// float-cast bugs (e.g. "6.10" → 6.1 when cast to float).
					{ group: data.inspector_group },
					createElement(
						PanelBody,
						{ className: 'onecom-imagify-promoter-panel', initialOpen: true },
						createElement(
							'div',
							{ className: 'onecom-imagify-promoter' },
							createElement(
								'div',
								{ className: 'onecom-imagify-promoter__header' },
								createElement( 'strong', { className: 'onecom-imagify-promoter__title' }, data.title ),
								createElement(
									'button',
									{
										type:       'button',
										className:  'onecom-imagify-promoter__dismiss',
										onClick:    handleDismiss,
										'aria-label': data.dismiss_label,
									},
									'×'
								)
							),
							createElement( 'p', { className: 'onecom-imagify-promoter__desc' }, data.description ),
							createElement(
								'a',
								{
									href:      data.plugins_page,
									className: 'onecom-imagify-promoter__cta button button-secondary',
									target:    '_blank',
									rel:       'noopener noreferrer',
								},
								data.cta_label
							)
						)
					)
				) : null
			);
		};
	}, 'withImagifyPromoter' );

	// Priority 1 ensures our HOC is innermost (inside WithBlockEditHooks), giving
	// InspectorControls access to BlockEditContext (clientId) required by WP 6.x.
	addFilter( 'editor.BlockEdit', 'onecom/imagify-promoter', withImagifyPromoter, 1 );
} )();
