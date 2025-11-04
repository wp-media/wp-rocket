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
		// Support both button and link styles with one handler.
		jQuery(document).on('click', '.wpr-ri-retest:not(.wpr-ri-action--disabled), .wpr-ri-retest-link', function(e) {
			e.preventDefault();
			const el = jQuery(this);
			const url = el.data('url');
			const column = el.closest('.wpr-ri-column');
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
		// Disable button and show loading state immediately.
		button.prop('disabled', true);

		// Show loading spinner immediately before API call
		showLoadingState(column, null);

		// Use REST (HEAD) but keep develop's robust handling.
		window.wp.apiFetch({
			path: '/wp-rocket/v1/rocket-insights/pages/',
			method: 'POST',
			data: { page_url: url },
		}).then((response) => {
			const success   = response?.success === true;
			const id        = response?.id ?? response?.data?.id ?? null;
			const canAdd    = (response?.can_add_pages ?? response?.data?.can_add_pages);
			const message   = response?.message ?? response?.data?.message;

			if (success && id) {
				// Update column with the row ID and start polling
				column.attr('data-rocket-insights-id', id);
				startPolling(id, url, column);

				// Check if we've reached the limit and disable all other "Test the page" buttons.
				if (canAdd === false || response?.data?.remaining_urls === 0) {
					disableAllTestPageButtons();
				}
				return;
			}

			// If backend says we cannot add pages or other errors, restore original state
			// Reload the column HTML from server to restore the button
			reloadColumnFromServer(column, url);
		}).catch((error) => {
			// wp.apiFetch throws on WP_Error; reload column to restore button
			console.error(error);
			reloadColumnFromServer(column, url);
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
		// Show loading spinner immediately before API call
		showLoadingState(column, rowId);

		window.wp.apiFetch(
			{
				path: '/wp-rocket/v1/rocket-insights/pages/' + rowId,
				method: 'PATCH',
			}
		).then( ( response ) => {
			if (response.success) {
				// Start polling for results
				startPolling(rowId, url, column);
			} else {
				// If not successful, reload the column to restore previous state
				reloadColumnFromServer(column, url);
			}
		} ).catch( ( error ) => {
			console.error(error);
			// Reload the column to restore previous state
			reloadColumnFromServer(column, url);
		} );
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
		window.wp.apiFetch(
			{
				path: window.wp.url.addQueryArgs( '/wp-rocket/v1/rocket-insights/pages/progress', { ids: [rowId] } ),
			}
		).then( ( response ) => {
			if ( response.success && Array.isArray( response.results ) ) {
				const result = response.results[0];

				if ( result.status === 'completed' || result.status === 'failed' ) {
					// Stop polling.
					clearInterval( activePolls[rowId] );
					delete activePolls[rowId];

					// Update the column with results (reload rendered HTML from server).
					updateColumnWithResults( column, result );
				}
			}
		} );
	}

	/**
	 * Show loading state in the column.
	 *
	 * @param {jQuery} column The column element.
	 * @param {number} rowId  The database row ID (can be null when initially showing loading).
	 */
	function showLoadingState(column, rowId) {
		if (rowId) {
			column.attr('data-rocket-insights-id', rowId);
		}

		// Create elements safely to prevent XSS
		const loadingDiv = jQuery('<div>').addClass('wpr-ri-loading');
		const img = jQuery('<img>').addClass('wpr-loading-img').attr({
			src: window.rocket_insights_i18n?.loading_img || '',
			alt: 'Loading...'
		});
		const messageDiv = jQuery('<div>').addClass('wpr-ri-message').css('display', 'none');

		loadingDiv.append(img);
		column.empty().append(loadingDiv).append(messageDiv);
	}

	/**
	 * Reload column HTML from server.
	 *
	 * @param {jQuery} column The column element.
	 * @param {string} url    The URL for the column.
	 */
	function reloadColumnFromServer(column, url) {
		window.wp.apiFetch(
			{
				path: window.wp.url.addQueryArgs( '/wp-rocket/v1/rocket-insights/pages', { url: url } ),
			}
		).then( ( response ) => {
			if (response.success && response.html) {
				column.replaceWith(response.html);

				// Re-attach listeners to the new content.
				attachTestPageListeners();
				attachRetestListeners();
			}
		} ).catch( ( error ) => {
			console.error('Failed to reload column:', error);
		} );
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
		reloadColumnFromServer(column, url);
	}

	/**
	 * Disable all "Test the page" buttons when the URL limit is reached.
	 */
	function disableAllTestPageButtons() {
		jQuery('.wpr-ri-test-page:not(.wpr-ri-no-credit)').each(function() {
			const button = jQuery(this);
			button.addClass('wpr-ri-no-credit').prop('disabled', true);

			// Add the "You've reached your limit" message if not already present.
			const column = button.closest('.wpr-ri-column');
			const creditMessage = column.find('.wpr-ri-credit-message');

			if (creditMessage.length === 0) {
				const messageDiv = jQuery('<div>').addClass('wpr-ri-credit-message');
				const isFreeUser = window.rocket_ajax_data?.is_free_user || false;
				const limitMessage = isFreeUser
					? window.rocket_insights_i18n?.free_limit_reached
					: window.rocket_insights_i18n?.paid_limit_reached;

				messageDiv.html(limitMessage);
				button.after(messageDiv);
			}
		});
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
