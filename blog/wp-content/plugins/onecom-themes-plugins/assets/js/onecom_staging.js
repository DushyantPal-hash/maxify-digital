(function ($) {

	var FLAG = {
		anyProcess: {
			'type': '',
			'active': false,
			'timer': ''
		},
		jobFinished: false,
		getLogs: false,
		rebuild: false
	};

	window.onbeforeunload = '';

	function nested_dirs() {
		if ($('#dir_list').length) {
			$('#dir_list').find('.wpstg-expand-dirs').bind('click', function (e) {
				e.preventDefault();
				if (!$(this).hasClass('disabled')) {
					$(this).siblings(".wpstg-subdir").slideToggle();
				}
			});
		}
	}

	function onecom_get_staging() {

		var data = {
			action: 'onestg_get_staging',
			nonce: wpstg.nonce
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				console.log(xhr.status + ' ' + xhr.statusText + ' --- ' + textStatus);
				showGeneralError();
			},
			success: function (data) {
				$(document).find('#staging_entry').slideUp().remove();
				$('#staging-create').slideUp('fast').before(data);
				getButtonsAfterStaging();
				// setTimeout(function () {
				// 	window.location.reload();
				// }, 7000);
			}
		});

	}

	function onecom_scanfiles() {

		var data = {
			action: 'onestg_scanning',
			nonce: wpstg.nonce
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);
				showGeneralError(wpstg.scanFail);
			},
			success: function (data) {

				if (data.error === true) {
					showGeneralError(wpstg.scanFail);
					return;
				}

				$('#dir_list').html('').html(data);

				// Bind Dir Tree Parent-Child slide animation
				nested_dirs();

				//Check free disk space
				checkDiskSpace();
			}
		});

	}

	function checkDiskSpace() {

		var data = {
			action: 'onestg_check_disk_space',
			nonce: wpstg.nonce
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: 'JSON',
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);
				showGeneralError(wpstg.diskspaceFail);
			},
			success: function (data) {

				if (!data || !data.freespace) {
					showGeneralError(wpstg.diskspaceFail);
					return;
				}
//todo add a comparison for the used and free space to decide enough diskspace or not

				// Get ready to start cloning.
				prepare_clone_data();

			},
		});
	}

	/**
	 * Scroll the window log to bottom
	 */
	function logscroll() {
		var $div = $("#console_log");
		if ("undefined" !== typeof ($div[0])) {
			$div.scrollTop($div[0].scrollHeight);
		}
	}

	/**
	 * Append the log to the logging window
	 */
	function getLogs(log) {
		if (log != null && "undefined" !== typeof (log)) {
			if (log.constructor === Array) {
				$.each(log, function (index, value) {
					if (value === null) {
						return;
					}
				})
			}
		}
		logscroll();
	}

	/* Helper Functions */

	function getExcludedTables() {
		var excludedTables = [];

		$(".onestaging-db-table input:not(:checked)").each(function () {
			excludedTables.push(this.name);
		});

		return excludedTables;
	}

	function getIncludedDirectories() {
		var includedDirectories = [];

		$(".wpstg-dir input:checked").each(function () {
			var $this = $(this);
			if (!$this.parent(".wpstg-dir").parents(".wpstg-dir").children(".wpstg-expand-dirs").hasClass("disabled")) {
				includedDirectories.push($this.val());
			}
		});

		return includedDirectories;
	}


	function getExcludedDirectories() {
		var excludedDirectories = [];

		$(".wpstg-dir input:not(:checked)").each(function () {
			var $this = $(this);
			if (!$this.parent(".wpstg-dir").parents(".wpstg-dir").children(".wpstg-expand-dirs").hasClass("disabled")) {
				excludedDirectories.push($this.val());
			}
		});

		return excludedDirectories;
	}

	function getIncludedExtraDirectories() {
		var extraDirectories = [];

		if (!$("#wpstg_extraDirectories").val()) {
			return extraDirectories;
		}

		var extraDirectories = $("#wpstg_extraDirectories").val().split(/\r?\n/);
		console.log(extraDirectories);

		//excludedDirectories.push($this.val());

		return extraDirectories;
	}


	/*#############################
	* ## Delete Existing Staging ##
	* ###########################*/
	function delete_staging(staging_id) {

		let totalTime = (new Date().getTime() - FLAG.anyProcess.timer).toFixed(2);
		totalTime = (totalTime / 1000).toFixed(2);

		data = {
			action: "onestg_delete_clone",
			clone: staging_id,
			nonce: wpstg.nonce,
			excludedTables: getExcludedTables(),
			deleteDir: $("#deleteDirectory:checked").val(),
			totalTime: totalTime
		};

		onecom_staging_progress(wpstg.msgDelStg, 0, 0);

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				showGeneralError(wpstg.deletestagingFail);
			},
			success: function (data) {

				if (data) {
					// Error
					if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
						showGeneralError(wpstg.deletestagingFail);
						return;
					}

					// Finished
					if ("undefined" !== typeof data.delete && data.delete === 'finished') {
						onecom_staging_progress(wpstg.msgDelStg, 100, 1);
						$("#entry_" + staging_id).fadeIn(5000, function () {
							$(this).remove();
						});
						FLAG.anyProcess.type = '';
						FLAG.anyProcess.active = false;
						window.onbeforeunload = '';
						// setTimeout(function () {
						// 	window.location.reload();
						// }, 7000); // delay to show notice

						// $('.oc-close-modal').trigger('click');
						// $( '.loading-overlay' ).removeClass( 'show' );

						$('.one-staging-details').hide();
						$('#staging-create').slideDown().fadeIn(300);
						$('.ocwp_ocp_staging_logged_in_event, .ocwp_ocp_staging_viewed_event').each(function () {
							const parent = $(this).parent();

							// Remove the button
							$(this).remove();

							// If both buttons existed in same parent, only insert once
							if (!parent.find('.one-button-create-staging').length) {
								const createBtn = `
			<button type="button" class="gv-button gv-button-primary one-button-create-staging ocwp_ocp_staging_created_event oc-create-stg-header" data-modal-target=".oc-copy-staging-modal">
				<gv-icon src="${wpstg.imageDir}/add.svg"></gv-icon>
				<span>${wpstg.createStagingLabel}</span>
			</button>
		`;
								parent.append(createBtn);
							}
						});
						$('.one-button-create-staging').removeClass('gv-hidden');
						$('.loading-overlay-content .gv-loader-container p').html('');

						setTimeout(function () {
							showAjaxNotice('success', wpstg.stgDeleted);
						}, 5000);
						return;
					}
				}
				// continue
				if (true !== data) {
					delete_staging(staging_id);
					return;
				}
			}
		});

	}


	/* ############################
	* ##  Deploy Staging to Live ##
	* ########################## */
	function prepare_staging_to_live(live_directory) {
		if (!live_directory) return;


		/* Existing staging ID in the below statement to update  */
		var liveID = live_directory;
		var excludedTables = getExcludedTables();
		var includedDirectories = getIncludedDirectories();
		var excludedDirectories = getExcludedDirectories();
		var extraDirectories = getIncludedExtraDirectories();
		console.log(includedDirectories);

		data = {
			action: "onestg_deploy",
			nonce: wpstg.nonce,
			liveDirectory: liveID,
			excludedTables: excludedTables,
			includedDirectories: includedDirectories,
			excludedDirectories: excludedDirectories,
			extraDirectories: extraDirectories
		};

		//console.log('Preparing for UPDATING clone...');


		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: data,
			error: function (xhr, textStatus, errorThrown) {

				console.log(xhr.status + ' ' + xhr.statusText);

				showGeneralError(wpstg.preparestagingtoliveFail);
			},
			success: function (data) {
				// Error
				if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
					showGeneralError(wpstg.preparestagingtoliveFail);
					return;
				}

				// Start cloning
				start_clone();
			}
		});

	}


	/* ############################
	* ## Update Existing Staging ##
	* ########################## */
	function prepare_update_staging(staging_id) {
		if (!staging_id) return;


		/* Existing staging ID in the below statement to update  */
		var cloneID = staging_id;
		var excludedTables = getExcludedTables();
		var includedDirectories = getIncludedDirectories();
		var excludedDirectories = getExcludedDirectories();
		var extraDirectories = getIncludedExtraDirectories();

		data = {
			action: "onestg_update",
			nonce: wpstg.nonce,
			cloneID: cloneID,
			excludedTables: excludedTables,
			includedDirectories: includedDirectories,
			excludedDirectories: excludedDirectories,
			extraDirectories: extraDirectories
		};

		console.log('Preparing for UPDATING cloning...');

		$.ajax({
			url: ajaxurl,
			type: "POST",
			/*dataType: "JSON",*/
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				showGeneralError(wpstg.prepareupdatestagingFail);
			},
			success: function (data) {
				// Error
				if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
					showGeneralError(wpstg.prepareupdatestagingFail);
					return;
				}

				// Start cloning
				start_clone();
			}
		});

	}


	/*#######################*/
	/* Start Cloning Process */

	/*#######################*/
	function prepare_clone_data() {
		onecom_staging_progress(wpstg.msgPrep, 0, 0);

		/* Set new ID for the staging in the below statement */
		var cloneID = wpstg.stgID;
		var stgPrefix = wpstg.stgPrefix;
		var excludedTables = getExcludedTables();
		var includedDirectories = getIncludedDirectories();
		var excludedDirectories = getExcludedDirectories();
		var extraDirectories = getIncludedExtraDirectories();

		data = {
			action: "onestg_cloning",
			nonce: wpstg.nonce,
			cloneID: cloneID,
			stgPrefix: stgPrefix,
			excludedTables: excludedTables,
			includedDirectories: includedDirectories,
			excludedDirectories: excludedDirectories,
			extraDirectories: extraDirectories
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				console.log(xhr.status + ' ' + xhr.statusText);
				showGeneralError(wpstg.prepareclonedataFail);
			},
			success: function (data) {
				// Error
				if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
					showGeneralError(wpstg.prepareclonedataFail);
					return;
				}
				onecom_staging_progress(wpstg.msgPrep, 100, 0);

				// Start cloning
				start_clone();
			},
		});

		//return data;
	}


	function start_clone() {
		onecom_staging_progress(wpstg.msgNewStg, 0, 0);
		/*show spinner here*/

		// Clone Database
		setTimeout(function () {
			onecom_staging_progress(wpstg.msgCopyDB, 0, 0);
			cloneDatabase();
		}, wpstg.cpuLoad);
	}

	// Step 1: Clone Database
	function cloneDatabase() {
		if (true === FLAG.getLogs) {
			getLogs();
		}


		setTimeout(
			function () {

				var data = {
					action: "onestg_clone_database",
					nonce: wpstg.nonce
				};

				$.ajax({
					url: ajaxurl,
					type: "POST",
					dataType: "JSON",
					data: data,
					error: function (xhr, textStatus, errorThrown) {
						console.log(xhr.status + ' ' + xhr.statusText);
						showGeneralError(wpstg.cloneDatabaseFail);
					},
					success: function (data) {

						// Error
						if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
							showGeneralError(wpstg.cloneDatabaseFail);
							return;
						}

						onecom_staging_progress(wpstg.msgCopyDB, data.percentage, 0);

						// Add Log
						if ("undefined" !== typeof (data.last_msg)) {
							getLogs(data.last_msg);
						}

						// Continue clone DB
						if (false === data.status) {
							setTimeout(function () {
								cloneDatabase();
							}, wpstg.cpuLoad);
						}
						// Next Step
						else if (true === data.status) {
							setTimeout(function () {
								onecom_staging_progress(wpstg.msgPrepDirs, 0, 0);
								prepareDirectories();
							}, wpstg.cpuLoad);
						}
					},
				});
			},
			500
		);
	}

	// Step 2: Prepare Directories
	function prepareDirectories() {
		if (true === FLAG.jobFinished) {
			return false;
		}

		if (true === FLAG.getLogs) {
			getLogs();
		}

		$('#clone_log').append('Creating Directory Tree...<hr>');

		setTimeout(
			function () {

				var data = {
					action: "onestg_clone_prepare_directories",
					nonce: wpstg.nonce
				};

				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: data,
					error: function (xhr, textStatus, errorThrown) {
						showGeneralError(wpstg.prepareDirectoriesFail);
					},
					success: function (data) {
						// Error
						if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
							showGeneralError(wpstg.prepareDirectoriesFail);
							return;
						}

						onecom_staging_progress(wpstg.msgPrepDirs, data.percentage, 0);

						// Add Log
						if ("undefined" !== typeof (data.last_msg)) {
							getLogs(data.last_msg);
						}

						if (false === data.status) {
							setTimeout(function () {
								prepareDirectories();
							}, wpstg.cpuLoad);
						} else if (true === data.status) {
							onecom_staging_progress(wpstg.msgCopyFiles, 0, 0);
							cloneFiles();
						}
					},
					statusCode: {
						404: function () {
							$('#onme_errors').html("").html("Something went wrong; can't find ajax request URL!");
						},
						500: function () {
							$('#onme_errors').html("").html("Something went wrong; internal server error while processing the request!");
						}
					}
				});
			},
			500
		);
	}

	// Step 3: Clone Files
	function cloneFiles() {
		if (true === FLAG.jobFinished) {
			return false;
		}


		if (true === FLAG.getLogs) {
			getLogs();
		}

		var data = {
			action: "onestg_clone_files",
			nonce: wpstg.nonce
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				showGeneralError(wpstg.cloneFilesFail);
			},
			success: function (data) {
				// Error
				if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
					showGeneralError(wpstg.cloneFilesFail);
					return;
				}

				if ("undefined" !== typeof (data.percentage)) {
					onecom_staging_progress(wpstg.msgCopyFiles, data.percentage, 0);
				}

				// Add Log
				if ("undefined" !== typeof (data.last_msg)) {
					getLogs(data.last_msg);
				}

				if (false === data.status) {
					setTimeout(function () {
						cloneFiles();
					}, wpstg.cpuLoad);
				} else if (true === data.status) {
					setTimeout(function () {
						replaceData();
					}, wpstg.cpuLoad);
				}
			},
			statusCode: {
				404: function () {
					$('#onme_errors').html("").html("Something went wrong; can't find ajax request URL!");
				},
				500: function () {
					$('#onme_errors').html("").html("Something went wrong; internal server error while processing the request!");
				}
			}
		});

	}

	// Step 4: Replace Data
	function replaceData() {
		if (true === FLAG.jobFinished) {
			return false;
		}

		if (true === FLAG.getLogs) {
			getLogs();
		}

		var data = {
			action: "onestg_clone_replace_data",
			nonce: wpstg.nonce
		};

		onecom_staging_progress(wpstg.msgUpdateDB, 0, 0);

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				showGeneralError(wpstg.replaceDataFail);
			},
			success: function (data) {
				// Error
				if ("undefined" !== typeof data.error && "undefined" !== typeof data.message) {
					showGeneralError(wpstg.replaceDataFail);
					return;
				}
				onecom_staging_progress(wpstg.msgUpdateDB, data.percentage, 0);

				// Add Log
				if ("undefined" !== typeof (data.last_msg)) {
					getLogs(data.last_msg);
				}

				if (false === data.status) {
					setTimeout(function () {

						replaceData();

					}, wpstg.cpuLoad);
				} else if (true === data.status) {
					finish();
				}
			},
			statusCode: {
				404: function () {
					$('#onme_errors').html("").html("Something went wrong; can't find ajax request URL!");
				},
				500: function () {
					$('#onme_errors').html("").html("Something went wrong; internal server error while processing the request!");
				}
			}
		});


	}

	// Finish
	function finish(status) {
		if (true === FLAG.jobFinished) {
			showControls(status);
			return false;

		}

		onecom_staging_progress(wpstg.msgFinalize, 0, 0);

		var totalTime = (new Date().getTime() - FLAG.anyProcess.timer).toFixed(0);
		totalTime = (totalTime / 1000).toFixed(2);

		var data = {
			action: "onestg_clone_finish",
			nonce: wpstg.nonce,
			totalTime: totalTime
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				if(FLAG.anyProcess.type === 'staging_deploy' && FLAG.rebuild === false) {
					showGeneralError(wpstg.copyStagingToLiveErr);
				}else if (FLAG.rebuild === true) {
					showGeneralError(wpstg.stgRebuiltError);

				} else {

					showGeneralError(wpstg.stgCreatedErr);
				}
				FLAG.anyProcess.timer = '';
			},
			success: function (data) {
				// Invalid data
				if ("object" !== typeof (data)) {
					console.log(
						"Couldn't finish the cloning process properly. " +
						"Your snapshot has been copied but failed to do clean up and " +
						"saving its records to the database."
					);
					FLAG.anyProcess.timer = '';
					showGeneralError(wpstg.finishFail);

					return;
				}

				FLAG.anyProcess.active = false;
				FLAG.anyProcess.timer = '';
				window.onbeforeunload = '';
				onecom_get_staging();
				$('.ocwp_ocp_staging_created_event').addClass('gv-hidden');
				if(FLAG.anyProcess.type === 'staging_deploy' && FLAG.rebuild === false){
					showAjaxNotice('success', wpstg.copyStagingToLive);
				}else if (FLAG.rebuild === true) {
					showAjaxNotice('success', wpstg.stgRebuilt);
				} else {
					showAjaxNotice('success', wpstg.stgCreated);
				}

				FLAG.anyProcess.type = '';
				// Add Log

				if ("undefined" !== typeof (data.last_msg)) {
					getLogs(data.last_msg);
				}
				onecom_staging_progress(wpstg.msgFinished, 100, 1);

				$('.loading-overlay-content .gv-loader-container p').html('');
				logscroll();


				// Finished
				FLAG.jobFinished = true;
				finish(data);

			}
		});
	}

	function getButtonsAfterStaging() {
		// Make sure this runs after the AJAX content is fully inserted
		const stagingId = $('#staging_entry').data('staging-id'); // assumes this exists in AJAX-loaded HTML
		const cloneUrl = `${wpstg.siteUrl}${stagingId}/`;

		const replacementButtons = `
 <a href="${cloneUrl}" target="_blank" class="gv-button gv-button-secondary ocwp_ocp_staging_viewed_event gv-mr-md gv-max-mob-mr-0">
        <span>${wpstg.labelView}</span>
        <gv-icon src="${wpstg.imageDir}/open_in_new.svg"></gv-icon>
    </a>
    <a href="javascript:void(0);"
       data-loginUrl="${cloneUrl}wp-login.php"
       data-stgUrl="${cloneUrl}"
       class="gv-button gv-max-mob-order-first gv-button-primary loginStaging ocwp_ocp_staging_logged_in_event gv-max-mob-mb-sm">
        <span>${wpstg.loginStaging}</span>
        <gv-icon src="${wpstg.imageDir}/open_in_new.svg"></gv-icon>
    </a>

`;

// Replace all relevant "Create Staging" buttons
		$('.oc-create-stg-header.one-button-create-staging').each(function () {
			$(this).replaceWith(replacementButtons);
		});
	}

	/* Display controls of newly created staging site */
	function showControls(data) {
		$('#clone_area_head').find('.spinner').removeClass('is-active');
		if ('undefined' != data && 'undefined' != data.url) {
			$('#onme_errors').html(
				"Staging site created : " + data.url + '/wp-admin/'
			)
				.addClass('green').slideDown();
		}
		//Clone.Timer.toggle();
		$('.stopwatch._clone').addClass('done');
		return;
	}

	/*
	* Bind Button Event-handlers
	*/

	// Create LIVE to STAGING
	$(document).on('click', '.one-button-create-staging', function () {
		FLAG.anyProcess.type = 'staging_create';
		FLAG.anyProcess.active = true;
		FLAG.anyProcess.timer = new Date().getTime();
		window.onbeforeunload = onecomConfirmExit;
		$('.loading-overlay.fullscreen-loader').not('.new-staging').addClass('show forced-center');
		oc_validate_action('stg').then(function (response) {
			if (response.status === 'success') {
				$('.loading-overlay.new-staging').addClass('show');
				FLAG.rebuild = false ;
				onecom_scanfiles();
				//send log request
				oc_trigger_log({
					actionType: 'wppremium_click_feature',
					isPremium: 'true',
					feature: 'STAGING_ENV',
					featureAction: 'create'
				});
			} else if (response.status === 'failed') {
				jQuery('#oc_um_overlay').show();
				ocSetModalData({
					isPremium: 'true',
					feature: 'STAGING_ENV',
					featureAction: 'create'
				});
			} else if (response.msg) {
				oc_alert(response.msg, 'error', 5000);
			}
			$('.loading-overlay.fullscreen-loader').not('.new-staging').removeClass('show forced-center');
		});
	});

	$(document).on('click', '.oc-open-modal', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		const targetModal = $(this).data('modal-target');
		$(targetModal).removeClass('gv-hidden');
	});

	// Close any modal
	$(document).on('click', '.oc-close-modal', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		const targetModal = $(this).data('modal-target');
		$(targetModal).addClass('gv-hidden');
	});

	// Rebuild staging
	$('.one-button-update-staging-confirm').click(function () {
		// Show fullscreen loader initially
		// $('.loading-overlay.fullscreen-loader').not('.update-loader').addClass('show forced-center');

		oc_validate_action('stg').then(function (response) {

				$('.loading-overlay.update-loader').addClass('show');

			if (response.status === 'success') {
				$('.oc-close-modal').trigger('click');
				let staging_id = $('#staging_entry').data('staging-id');
				if (staging_id) {
					FLAG.anyProcess.type = 'staging_rebuild';
					FLAG.anyProcess.active = true;
					FLAG.anyProcess.timer = new Date().getTime();
					FLAG.rebuild = true;
					FLAG.jobFinished = false;
					window.onbeforeunload = onecomConfirmExit;
					prepare_update_staging(staging_id);
					oc_trigger_log({
						actionType: 'wppremium_click_feature',
						isPremium: 'true',
						feature: 'STAGING_ENV',
						featureAction: 'rebuild'
					});
				} else {
					showGeneralError(wpstg.stgRebuiltError);
				}
			} else if (response.status === 'failed') {
				$('.oc-close-modal').trigger('click');
				$('#oc_um_overlay').show();
				ocSetModalData({
					isPremium: 'true',
					feature: 'STAGING_ENV',
					featureAction: 'rebuild'
				});
			} else if (response.msg) {
				$('.oc-close-modal').trigger('click');
				oc_alert(response.msg, 'error', 5000);
			}

			$('.loading-overlay.fullscreen-loader').not('.update-loader').removeClass('show forced-center');
		});
	});


	// Delete Staging button -- Confirmation
	$('.one-button-delete-staging-confirm').click(function () {
		$('.loading-overlay').first().addClass('show');
		var staging_id = $('#staging_entry').data('staging-id');
		if (staging_id) {
			// Call delete function
			$('.oc-close-modal').trigger('click');
			FLAG.anyProcess.type = 'staging_delete';
			FLAG.anyProcess.active = true;
			FLAG.anyProcess.timer = new Date().getTime();
			FLAG.jobFinished = false;
			FLAG.rebuild = false;
			window.onbeforeunload = onecomConfirmExit;
			delete_staging(staging_id);
		} else {
			setTimeout(function () {
				showGeneralError();
				$('.oc-close-modal').trigger('click');
			}, 5000);
		}
	});


	$('.one-button-copy-to-live-cancel, .one-button-delete-staging-cancel').click(function () {
		$('.oc-close-modal').trigger('click');
	});

	/* *********************** */
	/* COPY STAGING TO LIVE  */
	/* *********************** */


	$('#one-button-copy-to-live-confirm').on('click', function () {
		$('.loading-overlay.deploy-loader').addClass('show');
		$('.oc-close-modal').trigger('click');
		var liveID = $('#deploy_to_live').data('live-id');
		console.log("Starting deployment Staging-to-Live : " + liveID);

		FLAG.anyProcess.type = 'staging_deploy';
		FLAG.anyProcess.active = true;
		FLAG.anyProcess.timer = new Date().getTime();
		window.onbeforeunload = onecomConfirmExit;
		prepare_staging_to_live(liveID);

	});

	/* ############# Warning on window leave ########### */
	function onecomConfirmExit() {
		if (FLAG.anyProcess.active === true) {
			switch (FLAG.anyProcess.type) {
				case 'stg':
					return "Staging creation will get interrupted if you leave or reload this page.";

				case 'live':
					return "Copy staging to live will get interrupted if you leave or reload this page.";

				case 'delete':
					return "Staging will not get deleted properly if you leave or reload this page.";

				case 'update':
					return "Staging updation will get interrupted if you leave or reload this page.";

				default:
					return "Please do not leave the page. The current process will get interrupted.";
			}
		}
		return true;
	}

	function showGeneralError(msg) {
		var cmsg = wpstg.error_msg;
		if ('undefined' !== typeof (msg) && msg.length) {
			cmsg = msg;
		}
		showAjaxNotice('error', cmsg);
	}

	function onecom_log_staging_err(stg_action, msg) {
		if (!stg_action.length)
			return;

		var _msg = msg;
		var _action = stg_action;

		var data = {
			action: "onestg_clone_log_error",
			nonce: wpstg.nonce,
			stg_action: _action,
			msg: _msg
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: "JSON",
			data: data,
			error: function (xhr, textStatus, errorThrown) {
				console.log('Could not log the error');
				FLAG.anyProcess.type = '';
				FLAG.anyProcess.active = false;
				window.onbeforeunload = '';
			},
			success: function (data) {
				FLAG.anyProcess.type = '';
				FLAG.anyProcess.active = false;
				window.onbeforeunload = '';
			}
		});
	}

	function onecom_staging_progress(msg, count, hide) {
		if (!msg.length)
			return;

		$('.loading-overlay-content .gv-loader-container p').html(msg);
	}

	//stg login attempt
	$(document).on('click', '.loginStaging', function () {

		var data = {
			action: "onestg_log_attempt",
			nonce: wpstg.nonce,
		};

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: data,
			error: function (xhr, textStatus, errorThrown) {

			},
			success: function (response) {
				// Invalid data
				if ("object" !== typeof (response)) {
					return;
				}

				//if error exist
				if (response.success === false) {
					$('.onecom-notifier').html("Unable to do login into staging").attr('type', 'error').addClass('show');
					setTimeout(function () {
						$('.onecom-notifier').removeClass('show');
						$('.oc-close-modal').trigger('click');
					}, 5000);
					return;
				}

				//if success
				if (response.success === true) {
					let timelog = response.data.timelog;
					let stgUrl = response.data.stgUrl;

					let stglog = stgUrl + '/wp-admin/?timelog=' + timelog + '&stgUrl=' + stgUrl;
					window.open(stglog, '_blank');
				}
			}
		});
	});

	function showAjaxNotice(type, message) {
		const notice = $('#ajax-response-notice');
		const icon = $('#ajax-response-icon');
		const content = $('#ajax-response-content');
		if($('#oc-staging-broken').length) {
			$('#oc-staging-broken').remove();
			$('.oc-stg-btns').removeClass('gv-hidden');
		}

		// Reset classes
		notice.removeClass('gv-notice-success gv-notice-alert gv-hidden');

		//close oerlay and modal
		$('.loading-overlay').removeClass('show');
		$('.oc-close-modal').trigger('click');


		// Apply based on type
		if (type === 'success') {
			notice.addClass('gv-notice-success');
			icon.attr('src', wpstg.imageDir + '/check_circle.svg');
		} else if (type === 'error') {
			notice.addClass('gv-notice-alert');
			icon.attr('src', wpstg.imageDir + '/notice-error.svg');
		}

		content.text(message);
	}

	$('.gv-notice-close').on('click', function () {
		$('#ajax-response-notice').addClass('gv-hidden');
	});

	$('.oc-mwp-modal').on('click', function () {
		jQuery('#oc_um_overlay').show();
		ocSetModalData({
			isPremium: 'true',
			feature: 'STAGING_ENV',
			featureAction: 'rebuild'
		});
	})

})(jQuery);