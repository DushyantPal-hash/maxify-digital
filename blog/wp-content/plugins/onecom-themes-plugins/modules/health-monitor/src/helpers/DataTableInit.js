export function initializeVmDataTable(hasInitializedRef) {
	const $ = window.jQuery;
	if (!($ && $.fn && $.fn.dataTable)) return;

	const isMobile = () => $(window).width() <= 774;

	const init = () => {
		const $table = $('#vm-log-table');
		if ($table.length && !$.fn.dataTable.isDataTable($table)) {
			const trueForMobile = isMobile();

			$table.DataTable({
				searching: false,
				scrollX: trueForMobile,
				orderClasses: false,
				autoWidth: trueForMobile,
				pagingType: 'simple_numbers',
				columnDefs: [
					{ targets: 1, orderable: false },
					{ targets: 5, orderable: false },
					{ targets: 6, orderable: false },
				],
				order: [[0, 'desc']],
				language: {
					paginate: {
						previous: "<i class='vm_page_left_arrow'></i>",
						next: "<i class='vm_page_right_arrow'></i>",
					},
					info: `${window.vm_log_strings.showing} _START_ ${window.vm_log_strings.to} _END_ ${window.vm_log_strings.of} _TOTAL_ ${window.vm_log_strings.entries}`,
				},
			});

			const infoContainer = $('<div class="info-paginate-container"></div>');
			$('.dataTables_info, .dataTables_paginate').appendTo(infoContainer);
			$('#vm-log-table_wrapper').append(infoContainer);

			hasInitializedRef.current = true;
			return true;
		}
		return false;
	};

	// Attempt immediate init
	if (init()) return;

	// Fallback: observe DOM for delayed render
	const observer = new MutationObserver(() => {
		if (init()) observer.disconnect();
	});

	const container = document.getElementById('vuln-tab-container') || document.body;
	observer.observe(container, { childList: true, subtree: true });
}