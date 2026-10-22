/* global mp_config, jQuery */

// Global variables to store polling intervals for multiple plugins
window.marketplaceIntervals = window.marketplaceIntervals || {};
window.marketplaceAddonIntervals = window.marketplaceAddonIntervals || {};

// Added the below line to avoid multiple provision ajax request on reload
window.wpenqueued = true;
const ajaxURL = mp_config.ajaxURL;
const MP_ASSETS_URL = mp_config.mp_asset_url;
const MARKETPLACE_PAGE_SLUG = mp_config.marketplace_page_slug;
const MARKETPLACE_PRODUCTS_PAGE_SLUG = mp_config.marketplace_products_page_slug;

const GLOBAL_VAR = {
	'wp-rocket': mp_config.mp_labels["wp_rocket"],
	'rank-math': mp_config.mp_labels["rank_math"],
	'rank-math-pro': mp_config.mp_labels["rank_math"],
	MP_GLOCAL_ID: '#marketplace-root',
	MP_ADDONS_GLOCAL_ID: '#marketplace-addons-root',
	MP_GLOCAL_CLASS: '#marketplace-root .marketplace-container .gv-layout-product > nav',
	MP_ADDON_GLOCAL_CLASS: '#marketplace-addons-root .marketplace-container .addons-header-wrap',
    LOADER: `
        <div class="loading-overlay fullscreen-loader show mp-module">
            <div class="loading-overlay-content">
                <div class="gv-loader-container">
                    <img class="gv-loader custom-spinner" src="${MP_ASSETS_URL}spinner.svg" alt="Loading spinner">
                    <p>INSTALLING_TEXT ...</p>
                </div>
            </div>
        </div>
    `,
    TOAST: `
        <div class="gv-toast-container mp-module">
            <div class="gv-toast gv-toast-success gv-invisible">
                <p class="gv-toast-content">${mp_config.mp_labels["install_success"]}</p>
                <button class="gv-toast-close">
                    <img class="gv-icon" src="${MP_ASSETS_URL}/close.svg" alt="Close" height="14px" width="14px">
                </button>
            </div>
            <div class="gv-toast gv-toast-alert gv-invisible">
                <p class="gv-toast-content">${mp_config.mp_labels["error_installing"]}</p>
                <button class="gv-toast-close">
                    <img class="gv-icon" src="${MP_ASSETS_URL}/close-white.svg" alt="Close" height="14px" width="14px">
                </button>
            </div>
        </div>
    `,
	PLUGIN_SLUGS: ['wp-rocket', 'rank-math'],
	PLUGIN_ACTION_CLASS: 'section.gv-product-table.gv-features-table.gv-area-table .plugin-actions'
}
//[Activate] Listen for a custom event to provision plugin
//Entry point for provisioning via event dispatch
document.addEventListener("onecom-plugin-provision", function (event) {
	let addon_slug = event.detail.slug;

	if(!addon_slug) return;

	if(addon_slug === 'seo-by-rank-math-pro'){
		addon_slug = 'rank-math';
	}

	//Hide activation banner on click
	jQuery('[data-addon-slug="'+addon_slug+'"]').hide();

	// Store plugin slug in localStorage for reload detection (support multiple plugins)
	setPluginInProgress(addon_slug, 'activation');

	// Add loader toast to the page
	addLoaderToast(addon_slug);

	// On the button click event, check addon purchase status
	marketplaceActivatePlugin(addon_slug);
});

//[Subscribe] Listen for a custom event to subscribe addon check
//Entry point for subscription check via button click
document.addEventListener("onecom-subscribe-addon", function (event) {
	let addon_slug = event.detail.slug;

	if(!addon_slug) return;

	if(addon_slug === 'seo-by-rank-math-pro'){
		window.open(mp_config.rank_math_buy_url, "_blank");
		addon_slug = 'rank-math';
	}

	if(addon_slug === 'wp-rocket'){
		window.open(mp_config.wp_rocket_buy_url, "_blank");
	}

	// Store plugin slug in localStorage for reload detection (support multiple plugins)
	setPluginInProgress(addon_slug, 'subscription');

	// On the button click event, check addon purchase status
	marketplaceAddonStatusPolling(addon_slug);
});


/**
 * Helper functions to manage multiple plugins in progress using localStorage
 */

// Set a plugin as in-progress with type (activation or subscription)
function setPluginInProgress(pluginSlug, type) {
	const storageKey = 'marketplace_plugins_in_progress';
	let pluginsInProgress = JSON.parse(localStorage.getItem(storageKey) || '{}');
	pluginsInProgress[pluginSlug] = {
		type: type,
		timestamp: Date.now()
	};
	localStorage.setItem(storageKey, JSON.stringify(pluginsInProgress));
}

// Get all plugins in progress
function getPluginsInProgress() {
	const storageKey = 'marketplace_plugins_in_progress';
	return JSON.parse(localStorage.getItem(storageKey) || '{}');
}

// Remove a plugin from in-progress list
function removePluginInProgress(pluginSlug) {
	const storageKey = 'marketplace_plugins_in_progress';
	let pluginsInProgress = JSON.parse(localStorage.getItem(storageKey) || '{}');
	delete pluginsInProgress[pluginSlug];
	localStorage.setItem(storageKey, JSON.stringify(pluginsInProgress));
}


function addLoaderToast(addon_slug){

	jQuery('.loading-overlay.fullscreen-loader').remove();
	let loaderText = mp_config.mp_labels["install"].replace("{name}", GLOBAL_VAR[addon_slug] || addon_slug);
	let finalText = GLOBAL_VAR.LOADER.replace("INSTALLING_TEXT", loaderText);

	// Append loader and toast containers if not already present
	jQuery(GLOBAL_VAR.MP_GLOCAL_ID).append(finalText);
	jQuery(GLOBAL_VAR.MP_GLOCAL_ID).append(GLOBAL_VAR.TOAST);

	jQuery(GLOBAL_VAR.MP_ADDONS_GLOCAL_ID).append(finalText);
	jQuery(GLOBAL_VAR.MP_ADDONS_GLOCAL_ID).append(GLOBAL_VAR.TOAST);


	//show loader
	jQuery('.loading-overlay.fullscreen-loader').removeClass('hide').addClass('show');
}

jQuery(document).on('click', '#try-again-wp-rocket, #try-again-rank-math', function(e) {
	e.preventDefault();

	// Extract plugin slug from button ID (removes 'try-again-' prefix)
	const buttonId = jQuery(this).attr('id');
	const pluginSlug = buttonId.replace('try-again-', '');

	// Hide the try-again banner
	jQuery(this).closest('.gv-notice-alert').remove();

	// Dispatch the custom event with slug in details
	const event = new CustomEvent('onecom-plugin-provision', {
		detail: {
			slug: pluginSlug
		}
	});

	document.dispatchEvent(event);
});

// Handle click on activate button in the activate banner
jQuery(document).on('click', '.activate-notice-banner .gv-button', function(e) {
	e.preventDefault();

	// Extract plugin slug from the banner's data attribute
	const banner = jQuery(this).closest('.activate-notice-banner');
	const pluginSlug = banner.data('addon-slug');

	if (!pluginSlug) {
		console.error('No addon slug found in banner');
		return;
	}

	// Hide the activate banner
	banner.remove();

	// Dispatch the custom event with slug in details
	const event = new CustomEvent('onecom-plugin-provision', {
		detail: {
			slug: pluginSlug
		}
	});

	document.dispatchEvent(event);
});


jQuery(document).ready(function () {

	//on page load, check the activation status for all plugins listed in GLOBAL_VAR.PLUGIN_SLUGS
	// And addon purchase status
	checkStatusOnReload();

    // Hide notice on close
	jQuery(document).on('click', '.marketplace-container .gv-notice-close', function () {
		jQuery(this).parent().addClass('gv-hidden');
	});

	jQuery(document).on('click', '.gv-toast-close, .gv-toast-close img', function () {
		jQuery(this)
			.closest('.gv-toast.gv-toast-alert')
			.addClass('gv-invisible')
			.removeClass('gv-visible');
	});

});

/**
 * Check on reload if the plugin is already in the queue and show loader
 */
function checkStatusOnReload() {
	//get url params from the current url
	const params = new URLSearchParams(window.location.search);
	// eslint-disable-next-line no-undef
	const page   = params.get('page');
	// eslint-disable-next-line no-undef
	let plugin = params.get('plugin');

	if(plugin === 'seo-by-rank-math-pro' || plugin === 'seo-by-rank-math'){
		plugin = 'rank-math';
	}

	// Check if we have a valid plugin and it's in the allowed list
	const isMarketplacePage = page === MARKETPLACE_PAGE_SLUG;
	const isProductsPage = page === MARKETPLACE_PRODUCTS_PAGE_SLUG;

	// For MARKETPLACE_PAGE_SLUG with plugin in URL, check that specific plugin
	if (isMarketplacePage && plugin && GLOBAL_VAR.PLUGIN_SLUGS.includes(plugin)) {
		checkPluginStatus(plugin, isMarketplacePage, isProductsPage);
	}

	// For MARKETPLACE_PRODUCTS_PAGE_SLUG, check all plugins in localStorage
	if (isProductsPage) {
		const pluginsInProgress = getPluginsInProgress();
		Object.keys(pluginsInProgress).forEach(pluginSlug => {
			if (GLOBAL_VAR.PLUGIN_SLUGS.includes(pluginSlug)) {
				checkPluginStatus(pluginSlug, isMarketplacePage, isProductsPage);
			}
		});

		// Check if activate banner should be shown (for products page only)
		GLOBAL_VAR.PLUGIN_SLUGS.forEach(pluginSlug => {
			if (!pluginsInProgress[pluginSlug]) {
				jQuery.post(ajaxURL, {
					action: 'marketplace_check_activate_banner',
					plugin_slug: pluginSlug  // Changed from 'plugin' to 'pluginSlug'
				}, function(response){
					if (response.show_banner && response.banner_html) {
						// Remove any existing banner first
						// Show the activate banner
						jQuery(GLOBAL_VAR.MP_ADDON_GLOCAL_CLASS).before().append(response.banner_html);
					}
				});
			}
		});

	}
}

/**
 * Check status for a specific plugin (activation and subscription)
 */
function checkPluginStatus(plugin, isMarketplacePage, isProductsPage) {
	const page = get_page_slug_from_url();
	// Check plugin activation in progress on reload
	jQuery.post(ajaxURL, {
		action: 'marketplace_plugin_activate_reload',
		plugin_slug: plugin,
		page: page
	}, function(response){
		handleMarketplacePluginActivationResponse(response, window.marketplaceIntervals[plugin]);
		if (response.status === 'already_in_queue') {
			setPluginInProgress(plugin, 'activation');
			//Add loader and toast to the page
			addLoaderToast(plugin);
			marketplacePluginStatusPolling(plugin);
		}
	});

	//Addon check only on marketplace products page
	if(isMarketplacePage){
		//Addon purchase status in progress on reload
		// Check addon purchase status in progress on reload
		jQuery.post(ajaxURL, {
			action: 'marketplace_addon_purchase_check_onload',
			plugin_slug: plugin
		}, function(response){
			handleMarketplaceAddonPurchaseResponse(response, window.marketplaceAddonIntervals[plugin]);
			if (response.status === 'already_in_queue') {
				marketplaceAddonStatusPolling(plugin);
			}
		});
	}
}

// [Activate] Poll plugin activation status for a given slug
function marketplacePluginStatusPolling(pluginSlug) {
    if (window.marketplaceIntervals[pluginSlug]) return;
    function pollStatus() {
		const page = get_page_slug_from_url();
        jQuery.post(ajaxURL, {
            action: 'marketplace_plugin_activate',
            plugin_slug: pluginSlug,
			page: page
        }, function(response){
            handleMarketplacePluginActivationResponse(response, pluginSlug);
        });
    }

	//pool immediately and then at intervals
    pollStatus();
	//pool at intervals 5 seconds
    window.marketplaceIntervals[pluginSlug] = setInterval(pollStatus, 5000);
}

//[Subscribe] Poll addon purchase status for a given slug
function marketplaceAddonStatusPolling(pluginSlug) {
    if (window.marketplaceAddonIntervals[pluginSlug]) return;
    function pollAddonStatus() {
        jQuery.post(ajaxURL, {
            action: 'marketplace_addon_purchase_check',
            plugin_slug: pluginSlug
        }, function(response){
            handleMarketplaceAddonPurchaseResponse(response, pluginSlug);
        });
    }
	//pool immediately and then at intervals
    pollAddonStatus();
	//pool at intervals 25 seconds
    window.marketplaceAddonIntervals[pluginSlug] = setInterval(pollAddonStatus, 25000);
}

/**
 * Add timeout function
 * @param seconds
 * @returns {Promise<unknown>}
 */
function csTimeout(seconds) {
    return new Promise(resolve => setTimeout(resolve, seconds * 1000));
}

// [Activate] [Subscription] Stop polling for a given slug and type [ plugin | addon
function stopMarketplacePolling(pluginSlug, type) {
    if (type === 'plugin' && window.marketplaceIntervals[pluginSlug]) {
        clearInterval(window.marketplaceIntervals[pluginSlug]);
        window.marketplaceIntervals[pluginSlug] = null;
    } else if (type === 'addon' && window.marketplaceAddonIntervals[pluginSlug]) {
        clearInterval(window.marketplaceAddonIntervals[pluginSlug]);
        window.marketplaceAddonIntervals[pluginSlug] = null;
    }
}

// [Activate] Handle AJAX response for plugin activation on button click or reload
function handleMarketplacePluginActivationResponse(response, pluginSlug) {

    if (response.status === 'normal_reload') return false;
    if (response.status === 'added_to_queue' || response.status === 'already_in_queue') {
		//continue showing loader
    } else if (response.status === 'activated') {
        stopMarketplacePolling(pluginSlug, 'plugin');
		//Clear localStorage for plugin activation
		removePluginInProgress(pluginSlug);
		//hide loader
		jQuery('.loading-overlay.fullscreen-loader').removeClass('show').addClass('hide');
		const page = get_page_slug_from_url();

		if (page === MARKETPLACE_PAGE_SLUG) {
			if (jQuery('.gv-notice.gv-notice-success.wpr-notice').length === 0) {
				jQuery(GLOBAL_VAR.MP_GLOCAL_CLASS).after().append(response.notice_html);
			}

			//Update the manage button
			let getManageButton = jQuery('.mp-primary-manage-button').html();
			jQuery(GLOBAL_VAR.PLUGIN_ACTION_CLASS).html(getManageButton);
		}

		if( page === MARKETPLACE_PRODUCTS_PAGE_SLUG ) {
			if (jQuery('.gv-notice.gv-notice-success.wpr-notice').length === 0) {
				jQuery(GLOBAL_VAR.MP_ADDON_GLOCAL_CLASS).before().append(response.notice_html);
			}

			//Update the plugin row to show active status
			const $row = jQuery("#marketplace-addons-root .marketplace-container table tbody tr#"+mp_config.mp_labels[pluginSlug]);

			// 1. Remove "Install and activate" text/link (5th td)
			$row.find("td:nth-child(5)").empty();

			// 2. Change "Not active" to "Active"
			$row.find(".gv-text-indicator span:last")
				.text(mp_config.mp_labels["active"]);

			// 3. Change indicator class to positive
			$row.find(".gv-indicator")
				.removeClass("gv-state-informative")
				.addClass("gv-state-positive");

		}

		//Reload page after 5 seconds
		csTimeout(5).then(() => {
			location.reload();
			}
		);

    } else if (response.status === 'expired_queue' || response.status === 'activation_failed' || response.status === 'addon_not_subscribed') {
        stopMarketplacePolling(pluginSlug, 'plugin');
		//Clear localStorage for plugin activation
		removePluginInProgress(pluginSlug);
		hideLoaderAndShowTryAgain(response);
    } else {
        stopMarketplacePolling(pluginSlug, 'plugin');
		//Clear localStorage for plugin activation
		removePluginInProgress(pluginSlug);
		hideLoaderAndShowTryAgain(response);
    }
}

// [Activate] Hide loader and show toast message
function hideLoaderAndShowTryAgain(response) {
	// Hide loader
	jQuery('.loading-overlay.fullscreen-loader').removeClass('show').addClass('hide');

	//Decide page slug from url and append try again notice
	if(response?.data?.try_again_banner){
		const page = get_page_slug_from_url();

		if (page === MARKETPLACE_PAGE_SLUG) {
			jQuery(GLOBAL_VAR.MP_GLOCAL_CLASS).after().append(response.data.try_again_banner);
		}

		if( page === MARKETPLACE_PRODUCTS_PAGE_SLUG ) {
			jQuery(GLOBAL_VAR.MP_ADDON_GLOCAL_CLASS).before().append(response.data.try_again_banner);
		}
	}

}

function get_page_slug_from_url() {
	const params = new URLSearchParams(window.location.search);
	return params.get('page');
}

//[Subscribe] Handle AJAX response for addon purchase status
function handleMarketplaceAddonPurchaseResponse(response, pluginSlug) {
    if (response.status === 'normal_reload') return false;
    if (response.status === 'already_in_queue' || response.status === 'added_in_queue') {
        // Queue in progress → keep polling
    } else if (response.status === 'addon_purchased') {
        stopMarketplacePolling(pluginSlug, 'addon');
		//Clear localStorage for addon purchase
		removePluginInProgress(pluginSlug);
        location.reload();
    } else if (response.status === 'already_plugin_active') {
        stopMarketplacePolling(pluginSlug, 'addon');
		//Clear localStorage for addon purchase
		removePluginInProgress(pluginSlug);
    } else if (response.status === 'expired_queue' || response.status === 'addon_not_purchased' || response.status === 'addon_not_subscribed') {
        stopMarketplacePolling(pluginSlug, 'addon');
		//Clear localStorage for addon purchase
		removePluginInProgress(pluginSlug);
    } else {
        stopMarketplacePolling(pluginSlug, 'addon');
		//Clear localStorage for addon purchase
		removePluginInProgress(pluginSlug);
    }
}

// [Activate] On the button click → start polling for plugin activation
function marketplaceActivatePlugin(pluginSlug) {
	// Start polling for activation status
    marketplacePluginStatusPolling(pluginSlug);
}