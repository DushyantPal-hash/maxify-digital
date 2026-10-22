/* global MPAdminSurvey */
(function ($) {
	const $bubble = $('#mp-survey-bubble'); //Survey Bubble
	const $popover = $('#mp-survey-popover'); //Survey Popover
	const $cta = $('.mp-survey-cta'); //Survey CTA

	$cta.attr('href', MPAdminSurvey.survey_url);

	$bubble.fadeIn();

	// start 20 sec timer
	let popoverTimer = setTimeout(() => {
		// if the popup is not closed, show it
		if(!MPAdminSurvey.popup_closed){
			$popover.stop(true, true).fadeIn();
		}
	}, MPAdminSurvey.delay);

	// close button click, and cross-button click
	$('.mp-close, .mp-close-btn').on('click', function () {
		$popover.stop(true, true).fadeOut();

		$.post(MPAdminSurvey.ajax_url, {
			action: 'mp_admin_mark_survey_popup_closed',
			nonce: MPAdminSurvey.close_nonce
		});
	});

	/**
	 * Bubble click
	 * - cancel timer
	 * - toggle popover
	 */

	$bubble.on('click', function () {

		// cancel pending 20 sec auto-open
		if (popoverTimer) {
			clearTimeout(popoverTimer);
			popoverTimer = null;
		}

		if($popover.is(':visible')){
			$popover.stop(true, true).fadeOut();
		} else {
			$popover.stop(true, true).fadeIn();
		}
	});

	// CTA click for a survey
	$cta.on('click', function () {

		// cancel timer
		if (popoverTimer) {
			clearTimeout(popoverTimer);
		}

		$.post(MPAdminSurvey.ajax_url, {
			action: 'mp_admin_mark_survey_completed',
			nonce: MPAdminSurvey.nonce
		});

		$popover.remove();
		$bubble.remove();
	});

})(jQuery);