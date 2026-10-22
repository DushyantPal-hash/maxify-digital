(function($){
	$(document).ready(function(){
		$('.onecom-notice').append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
		$(document).on( 'click', '.onecom-notice.is-dismissible > .notice-dismiss', function() {
			var data  = {
				'action' : 'onecom_dismiss_notice',
				'nonce'  : ( typeof onecom_promo_vars !== 'undefined' ) ? onecom_promo_vars.ajaxNonce : ''
			}
			var close = $(this);
			$.post(ajaxurl, data, function( response ) {
				console.log(response);
				$(close).parent().remove();
			});
		} );
	});
} )(jQuery);