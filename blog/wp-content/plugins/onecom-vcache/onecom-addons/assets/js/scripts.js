// Global variable to store polling interval
if (typeof window.wpRocketIntervalId === 'undefined') {
	window.wpRocketIntervalId = null;
}

if (typeof window.wpRocketAddonIntervalId === 'undefined') {
	window.wpRocketAddonIntervalId = null;
}

jQuery(document).ready(function () {

	// Disable premium fields for non-premium (or downgraded package)
	jQuery(".oc-non-premium #dev_mode_duration").prop('disabled', true);
	jQuery(".oc-non-premium #oc_dev_duration_save").prop('disabled', true);
	jQuery(".oc-non-premium #exclude_cdn_data").prop('disabled', true);
	jQuery(".oc-non-premium .oc_cdn_data_save").prop('disabled', true);

	// enable disable save button based on cdn switches state
	// oc_cdn_save_state_change();

	jQuery('#pc_enable').change(function () {
		ocSetVCState();
	});
	jQuery('.oc_ttl_save').click(function(){
		if (oc_validate_ttl()) {
			oc_update_ttl();
		}
	});
	jQuery('.oc_cdn_data_save').click(function(){
		if (oc_validate_cdn_data()) {
			oc_update_cdn_data();
		}
	});

	jQuery("#pc_enable_settings .oc_vcache_ttl").keypress(function(event) {
		jQuery(this).removeClass('oc_error');
		jQuery('#pc_enable_settings .oc-ttl-error-msg').hide();
	});

	jQuery("#dev_mode_enable_settings #dev_mode_duration").keypress(function(event) {
		jQuery(this).removeClass('oc_error');
		jQuery('#dev_mode_enable_settings .oc-ttl-error-msg').hide();
	});

	jQuery("#exclude_cdn_enable_settings #exclude_cdn_data").keypress(function(event) {
		jQuery(this).removeClass('oc_error');
		jQuery('#exclude_cdn_enable_settings .oc-ttl-error-msg').hide();
	});

	/*jQuery('.oc-activate-wp-rocket-btn').click(function(){
		oc_activate_wp_rocket();
	});*/

	jQuery('#cdn_enable').change(function (){
		ocSetCdnState();
	});
	jQuery('#dev_mode_enable').change(function (){
		jQuery('#dev_mode_duration').removeClass('oc_error');
		ocSetDevMode();
	});
	jQuery('#exclude_cdn_enable').change(function (){
		jQuery('#exclude_cdn_data').removeClass('oc_error');
		ocExcludeCDNState();
	});

	// disable all submit buttons until form changed
	jQuery('#pc_enable_settings form button.oc_ttl_save').attr('disabled', true);

	// Enable save button when form changed
	let settingsForm = jQuery('#pc_enable_settings form');
	settingsForm.each(function () {
		jQuery(this).data('serialized', jQuery(this).serialize());
	}).on('change keyup paste', function () {
		jQuery(this)
			.find('button.oc_ttl_save')
			.attr('disabled', jQuery(this).serialize() == jQuery(this).data('serialized'));
	})

	// disable CDN setting submit button until form changed
	jQuery('#cdn_settings button.oc_cdn_data_save').attr('disabled', true);

	// Enable save button when form changed
	let cdnSettingsForm = jQuery('#cdn_settings form');
	cdnSettingsForm.each(function () {
		jQuery(this).data('cdnSerialized', jQuery(this).serialize());
	}).on('change keyup paste', function () {
		jQuery(this)
			.find('button.oc_cdn_data_save')
			.attr('disabled', jQuery(this).serialize() == jQuery(this).data('cdnSerialized'));
	})

});

function oc_toggle_state(element) {
	var current_icon = element.attr('src');
	var new_icon     = element.attr('data-alt-image');
	element.attr({
		'src': new_icon,
		'data-alt-image': current_icon
	});
}


function oc_change_cdn_icon(){
	if (jQuery('#cdn_enable').prop('checked')) {
		jQuery('#oc-cdn-icon-active').show();
		jQuery('#oc-cdn-icon').hide();
		jQuery('.oc-cdn-feature-box').show();
		// Remove sub features success classes else spinner animate on each switch
		jQuery('.oc-cdn-feature-box .oc_cb_spinner').removeClass('success');
	} else {
		jQuery('#oc-cdn-icon').show();
		jQuery('#oc-cdn-icon-active').hide();
		jQuery('.oc-cdn-feature-box').hide();
		// Remove sub features success classes else spinner animate on each switch
		jQuery('.oc-cdn-feature-box .oc_cb_spinner').removeClass('success');
	}
}

// activate wp rocket button action
/*function oc_activate_wp_rocket(){
	jQuery('.oc_activate_wp_rocket_spinner').removeClass('success').addClass('is_active');
	jQuery.post(ajaxurl, {
		action: 'oc_activate_wp_rocket'
	}, function(response){
		jQuery('.oc_activate_wp_rocket_spinner').removeClass('is_active');
		if (response.status === true) {
			jQuery('.oc_activate_wp_rocket_spinner').addClass('success');
			window.location.href = "options-general.php?page=wprocket";
		} else {
			console.log("Error: Could not activate plugin")
		}
	});
}*/

function oc_show_more_less(){
	if (jQuery(".oc-hidden-content").css('display') === 'none') {
		jQuery(".oc-show-hide a").text("Show less");
		jQuery(".oc-hidden-content").show();
	} else {
		jQuery(".oc-show-hide a").text("Show more");
		jQuery(".oc-hidden-content").hide();
	}
}

//WP Rocket addon activation and purchase status check polling
jQuery(document).ready(function () {

	//check plugin activation in progress on reload:
	//different ajax action onclick and onload
	//only enqueue on the wp-rocket page
	if(!window.wpenqueued) {
		jQuery.post(ajaxurl, {
			action: 'on_reload_plugin_activate',
			addon_slug: 'wp-rocket'
		}, function (response) {
			handle_plugin_activation_response(response, window.wpRocketIntervalId);

			// If queue in progress, start polling
			if (response.status === 'already_in_queue') {
				console.log("Polling on reload");
				oc_plugin_status_polling();
			}
		});

		//check addon purchase status in progress on reload:
		//different ajax action onclick and onload
		//only enqueue on the wp-rocket page
		jQuery.post(ajaxurl, {
			action: 'check_addon_purchase_status_onload',
			addon_slug: 'wp-rocket'
		}, function (response) {
			handle_addon_purchase_response(response, window.wpRocketAddonIntervalId);

			// If queue in progress, start polling
			if (response.status === 'already_in_queue') {
				console.log("Polling on reload");
				oc_addon_status_polling();
			}
		});
	}

	//hide notice on close
	jQuery('.wpr-container .gv-notice-close').click(function(){
		jQuery(this).parent().addClass('gv-hidden');
	});

	//on the button click check plugin activation
	jQuery('.oc-activate-wp-rocket-btn').click(function(){
		oc_activate_wp_plugin();
	});

	//on the button click check addon status
	jQuery('.gv-button.get-wpr-btn').click(function(){
		oc_addon_purchase_status();
	});
});

// Common function to poll plugin activation status
function oc_plugin_status_polling(addon_slug = 'wp-rocket') {
	// If already polling, do nothing
	if (window.wpRocketIntervalId) return;

	// Define the polling function
	function pollStatus() {
		jQuery.post(ajaxurl, {
			action: 'activate_onclick_wp_plugin',
			addon_slug: addon_slug
		}, function(response){
			handle_plugin_activation_response(response, window.wpRocketIntervalId);
		});
	}

	// Call immediately at once
	pollStatus();

	//Then schedule it to repeat every 20 seconds
	window.wpRocketIntervalId = setInterval(pollStatus, 5000);
}

// Common function to poll addon activation status
//call on a twenty-seconds interval
function oc_addon_status_polling(addon_slug = 'wp-rocket') {
	// If already polling, do nothing
	if (window.wpRocketAddonIntervalId) return;

	// Define the polling function
	function pollAddonStatus() {
		jQuery.post(ajaxurl, {
			action: 'check_addon_purchase_status',
			addon_slug: addon_slug
		}, function(response){
			handle_addon_purchase_response(response, window.wpRocketAddonIntervalId);
		});
	}

	// Call immediately at once
	pollAddonStatus();

	//Then schedule it to repeat every 20 seconds
	window.wpRocketAddonIntervalId = setInterval(pollAddonStatus, 25000);
}

function stop_polling(intervalId, type) {
	if (intervalId) clearInterval(intervalId);

	if (type === 'plugin') {
		window.wpRocketIntervalId = null;
	} else if (type === 'addon') {
		window.wpRocketAddonIntervalId = null;
	}
}

// Common function to handle AJAX response
function handle_plugin_activation_response(response, intervalId = null) {

	if (response.status === 'normal_reload') {
		//do nothing on normal reload
		return false;
	}

	if (response.status === 'added_to_queue') {
		// Push stats
		let args = { 'event_action': 'click_wp_activate_button', 'item_category': 'plugin', 'item_name': 'wp_rocket' };

		oc_push_stats_by_js(args);
		jQuery('.gv-notice.gv-notice-warning.wpr-notice').removeClass('gv-hidden');

	} else if (response.status === 'already_in_queue') {
		// Queue in progress → keep polling
		jQuery('.gv-notice.wpr-notice').addClass('gv-hidden');
		jQuery('.gv-notice.gv-notice-warning.wpr-notice').removeClass('gv-hidden');

	} else if (response.status === 'activated') {
		stop_polling(intervalId, 'plugin');
		let btn = '<a class="gv-button gv-button-secondary goto-wpr wpr-btn" href="'+response.url+'">'+response.btn_text+'</a>';
		let insertAfter = '<div class="gv-bottom wpr-pricing">'+btn+'</div>';
		//append or add HTML for the go-to button
		(jQuery('.gv-bottom.wpr-pricing').length) ? jQuery('.gv-bottom.wpr-pricing').html(btn) : jQuery(insertAfter).insertAfter('.gv-content.wpr-pricing-content');

		console.log("WP Rocket activated successfully.");
		jQuery('.gv-notice.gv-notice-warning.wpr-notice').addClass('gv-hidden');
		jQuery('.gv-notice.gv-notice-success.wpr-notice').removeClass('gv-hidden');
	} else if (response.status === 'expired_queue' || response.status === 'activation_failed') {
		stop_polling(intervalId, 'plugin');
		console.log("Activation queue status: "+response.status);
		jQuery('.gv-notice.gv-notice-warning.wpr-notice').addClass('gv-hidden');
		jQuery('.gv-notice.gv-notice-alert.wpr-notice').removeClass('gv-hidden');

	} else if(response.status === 'addon_not_subscribed'){
		//special case very rare to exist
		stop_polling(intervalId, 'plugin');
	} else {
		stop_polling(intervalId, 'plugin');
		jQuery('.gv-notice.gv-notice-warning.wpr-notice').addClass('gv-hidden');
		jQuery('.gv-notice.gv-notice-alert.wpr-notice').removeClass('gv-hidden');
		console.log("Error: Could not activate plugin");
	}
}

// Common function to handle AJAX response for addon purchase status
function handle_addon_purchase_response(response, intervalId = null) {

	if (response.status === 'normal_reload') {
		//do nothing on normal reload
		return false;
	}

	if (response.status === 'already_in_queue' || response.status === 'added_in_queue') {
		// Queue in progress → keep polling
	} else if (response.status === 'addon_purchased') {
		stop_polling(intervalId, 'addon');
		// Push stats
		let args = { 'event_action': 'wpr_addon_purchased', 'item_category': 'plugin', 'item_name': 'wp_rocket' };
		oc_push_stats_by_js(args);
		// reload the page
		location.reload();
		console.log("WP Rocket addon purchased successfully.");
	} else if (response.status === 'already_plugin_active' ) {
		stop_polling(intervalId, 'addon');
		console.log("Already plugin activated.");
	}else if (response.status === 'expired_queue' || response.status === 'addon_not_purchased') {
		stop_polling(intervalId, 'addon');
		console.log("Activation queue expired or not purchased."+response.status);
	} else {
		stop_polling(intervalId, 'addon');
		console.log("Error: Could not activate plugin");
	}
}

// On the button click → start polling
function oc_activate_wp_plugin() {
	jQuery('.gv-notice.gv-notice-alert.wpr-notice').addClass('gv-hidden');
	jQuery('.gv-notice.gv-notice-info.wpr-notice').addClass('gv-hidden');
	jQuery('.gv-notice.gv-notice-warning.wpr-notice').removeClass('gv-hidden');
	oc_plugin_status_polling('wp-rocket');
}

function oc_addon_purchase_status() {
	oc_addon_status_polling('wp-rocket');
}