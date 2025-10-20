/**
 * Rocket Insights functionality for post listing pages
 * This script handles performance score display and updates in admin post listing pages
 *
 * @since 3.20.1
 */

// Export for use with browserify/babelify in gulp
module.exports = (function() {
	'use strict';

	/**
	 * Polling interval for checking ongoing tests (in milliseconds).
	 */
	const POLLING_INTERVAL = 5000; // 5 seconds

	/**
	 * Active polling intervals by post ID.
	 */
	const activePolls = {};

	/**
	 * Initialize Rocket Insights on post listing pages
	 */
	function init() {
		// Attach event listeners.
		attachTestPageListeners();
		attachRetestListeners();
		
		// Start polling for any rows that are already running.
		startPollingForRunningTests();
	}

	/**
	 * Attach click listeners to "Test the page" buttons.
	 */
	function attachTestPageListeners() {
		jQuery(document).on('click', '.wpr-ri-test-page', function(e) {
			e.preventDefault();
			const button = jQuery(this);
			
			// Don't allow click if no credit
			if (button.hasClass('wpr-ri-no-credit')) {
				return;
			}
			
			const url = button.data('url');
			const column = button.closest('.wpr-ri-column');

			addNewPage(url, column, button);
		});
	}

	/**
	 * Attach click listeners to "Re-test" buttons and links.
	 */
	function attachRetestListeners() {
		// Old button style
		jQuery(document).on('click', '.wpr-ri-retest:not(.wpr-ri-action--disabled)', function(e) {
			e.preventDefault();
			const button = jQuery(this);
			const url = button.data('url');
			const column = button.closest('.wpr-ri-column');
			const rowId = column.data('rocket-insights-id');

			if (!rowId) {
				return;
			}

			retestPage(rowId, url, column);
		});
		
		// New link style
		jQuery(document).on('click', '.wpr-ri-retest-link', function(e) {
			e.preventDefault();
			const link = jQuery(this);
			const url = link.data('url');
			const column = link.closest('.wpr-ri-column');
			const rowId = column.data('rocket-insights-id');

			if (!rowId) {
				return;
			}

			retestPage(rowId, url, column);
		});
	}

	/**
	 * Start polling for rows that are currently running tests.
	 */
	function startPollingForRunningTests() {
		jQuery('.wpr-ri-loading').each(function() {
			const column = jQuery(this).closest('.wpr-ri-column');
			const rowId = column.data('rocket-insights-id');
			const url = column.data('url');

			if (rowId && !activePolls[rowId]) {
				startPolling(rowId, url, column);
			}
		});
	}

	/**
	 * Add a new page for testing.
	 *
	 * @param {string} url    The URL to test.
	 * @param {jQuery} column The column element.
	 * @param {jQuery} button The button that was clicked.
	 */
	function addNewPage(url, column, button) {
		// Disable button and show loading state.
		button.prop('disabled', true).text(window.rocket_insights_i18n?.adding || 'Adding...');

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'rocket_rocket_insights_add_new_page',
				nonce: window.rocket_ajax_data?.nonce || '',
				page_url: url
			},
			success: function(response) {
				if (response.success && response.data.id) {
					// Update column with loading state.
					showLoadingState(column, response.data.id);
					
					// Start polling for results.
					startPolling(response.data.id, url, column);
				} else {
					// Show error message.
					showMessage(column, response.data?.message || 'Error adding page', 'error');
					button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
				}
			},
			error: function() {
				showMessage(column, window.rocket_insights_i18n?.error || 'An error occurred', 'error');
				button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
			}
		});
	}

	/**
	 * Retest an existing page.
	 *
	 * @param {number} rowId  The database row ID.
	 * @param {string} url    The URL being tested.
	 * @param {jQuery} column The column element.
	 */
	function retestPage(rowId, url, column) {
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'rocket_rocket_insights_reset_page',
				nonce: window.rocket_ajax_data?.nonce || '',
				row_id: rowId
			},
			success: function(response) {
				if (response.success) {
					// Update to loading state.
					showLoadingState(column, rowId);
					
					// Start polling for results.
					startPolling(rowId, url, column);
				} else {
					showMessage(column, response.data?.message || 'Error retesting page', 'error');
				}
			},
			error: function() {
				showMessage(column, window.rocket_insights_i18n?.error || 'An error occurred', 'error');
			}
		});
	}

	/**
	 * Start polling for test results.
	 *
	 * @param {number} rowId  The database row ID.
	 * @param {string} url    The URL being tested.
	 * @param {jQuery} column The column element.
	 */
	function startPolling(rowId, url, column) {
		// Clear any existing poll for this row.
		if (activePolls[rowId]) {
			clearInterval(activePolls[rowId]);
		}

		// Set up new polling interval.
		activePolls[rowId] = setInterval(function() {
			checkStatus(rowId, url, column);
		}, POLLING_INTERVAL);

		// Also check immediately.
		checkStatus(rowId, url, column);
	}

	/**
	 * Check the status of a test.
	 *
	 * @param {number} rowId  The database row ID.
	 * @param {string} url    The URL being tested.
	 * @param {jQuery} column The column element.
	 */
	function checkStatus(rowId, url, column) {
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'rocket_rocket_insights_get_results',
				nonce: window.rocket_ajax_data?.nonce || '',
				ids: [rowId]
			},
			success: function(response) {
				if (response.success && response.data && response.data.length > 0) {
					const result = response.data[0];
					
					// Check if test is complete or failed.
					// Stop polling for any status that's not 'in-progress' or explicitly running
					if (result.status !== 'in-progress' && !result.is_running) {
						// Stop polling.
						clearInterval(activePolls[rowId]);
						delete activePolls[rowId];
						
						// Update the column with results.
						updateColumnWithResults(column, result);
					}
				}
			}
		});
	}

	/**
	 * Show loading state in the column.
	 *
	 * @param {jQuery} column The column element.
	 * @param {number} rowId  The database row ID.
	 */
	function showLoadingState(column, rowId) {
		column.attr('data-rocket-insights-id', rowId);
		column.html(
			'<div class="wpr-ri-loading">' +
			'<img class="wpr-loading-img" src="' + (window.rocket_insights_i18n?.loading_img || '') + '" alt="Loading..."/>' +
			'</div>' +
			'<div class="wpr-ri-message" style="display: none;"></div>'
		);
	}

	/**
	 * Update column with test results.
	 *
	 * @param {jQuery} column The column element.
	 * @param {Object} result The test result data.
	 */
	function updateColumnWithResults(column, result) {
		// Reload the entire row from the server to get properly rendered HTML.
		const url = column.data('url');
		
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'rocket_rocket_insights_get_column_html',
				nonce: window.rocket_ajax_data?.nonce || '',
				url: url
			},
			success: function(response) {
				if (response.success && response.data.html) {
					column.replaceWith(response.data.html);
					
					// Re-attach listeners to the new content.
					attachTestPageListeners();
					attachRetestListeners();
				}
			}
		});
	}

	/**
	 * Show a message in the column.
	 *
	 * @param {jQuery} column  The column element.
	 * @param {string} message The message to display.
	 * @param {string} type    The message type ('error' or 'success').
	 */
	function showMessage(column, message, type) {
		const messageEl = column.find('.wpr-ri-message');
		// Clear any existing content first
		messageEl.stop(true, true).empty();
		messageEl.html('<p class="wpr-ri-message-' + type + '">' + message + '</p>').show();
		
		// Auto-hide after 5 seconds.
		setTimeout(function() {
			messageEl.fadeOut();
		}, 5000);
	}

	// Auto-initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	return {
		init: init
	};
})();
