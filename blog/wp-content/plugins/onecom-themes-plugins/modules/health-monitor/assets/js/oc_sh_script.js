(function ($) {
	let HM = {

		isMobile: function () {
			return $(window).width() <= 774;
		},

		showModal: function (check) {
			$('#oc_um_overlay').show();
			$('body').addClass('oc-noscroll');
			let referrer = '';
			ocSetModalData({
				isPremium: true,
				feature: 'health_monitor',
				plugin: 'onecom-themes-plugin',
				featureAction: check,
				referrer: referrer
			});
		},

		validateInput: function (input) {
			if (input.val().length === 0) {
				$(input).parent().find('.oc-error-message').text(oc_constants.error_empty).fadeIn();
				$(input).addClass("ocsh-error-field");
			} else if (input.val().length < 40) {
				$(input).parent().find('.oc-error-message').text(oc_constants.error_length).fadeIn();
				$(input).addClass("ocsh-error-field");
			} else {
				$(input).parent().find('.oc-error-message').text("").fadeOut();
				$(input).removeClass("ocsh-error-field");
			}
		}
	}
	$(document).ready(function () {
		// let noPrevScan = $('.ocsh-wrap').find('.oc-nps');
		// if(noPrevScan.length > 0){
		// 	$('.onecom_head').find('.oc-trigger-hmscan').text('Scanning').addClass( "oc-disabled" );
		// 	$(document).find('.onecom_empty_list').remove();
		// }

		$(document).on("click", ".oc_um_btn", function (e) {
			$('body').removeClass('oc-noscroll');
		});




		$(document).on('click', '.onecom__open-modal', function (e) {
			let check = $(this).parents('li.ocsh-bullet').attr('id') || "ignore_list";
			check = check.replace("ocsh-", '');
			check = check.replace("check_", "");
			HM.showModal(check);
		});
	});
})(jQuery)