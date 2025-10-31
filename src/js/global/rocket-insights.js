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
		// Disable button and show loading state.
		button.prop('disabled', true);

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
				// Begin common loading + polling flow.
				beginLoadingAndPoll(column, id, url);
				return;
			}

			// If backend says we cannot add pages, re-enable button and show error message.
			if (canAdd === false) {
				button.prop('disabled', false)
					.text(window.rocket_insights_i18n?.test_page || 'Test the page');
				showLimitReachedMessage(column);
				return;
			}

			// Other errors
			button.prop('disabled', false)
				.text(window.rocket_insights_i18n?.test_page || 'Test the page');
		}).catch((error) => {
			// wp.apiFetch throws on WP_Error; try to surface a helpful message.
			console.error(error);
			
			// Check if this is a limit reached error
			if (error?.data?.can_add_pages === false) {
				button.prop('disabled', false)
					.text(window.rocket_insights_i18n?.test_page || 'Test the page');
				showLimitReachedMessage(column);
				return;
			}
			
			button.prop('disabled', false)
				.text(window.rocket_insights_i18n?.test_page || 'Test the page');
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
		window.wp.apiFetch(
			{
				path: '/wp-rocket/v1/rocket-insights/pages/' + rowId,
				method: 'PATCH',
			}
		).then( ( response ) => {
			if (response.success) {
				// Begin common loading + polling flow.
				beginLoadingAndPoll(column, rowId, url);
			}
		} ).catch( ( error ) => {
			console.error(error);
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
	 * Common helper to set loading state and start polling.
	 *
	 * @param {jQuery} column The column element.
	 * @param {number} rowId  The database row ID.
	 * @param {string} url    The URL being tested.
	 */
	function beginLoadingAndPoll(column, rowId, url) {
		// Update column to loading state and start polling.
		showLoadingState(column, rowId);
		startPolling(rowId, url, column);
	}

	/**
	 * Show limit reached message in the column.
	 *
	 * @param {jQuery} column The column element.
	 */
	function showLimitReachedMessage(column) {
		const isFree = column.data('is-free') === 1 || column.data('is-free') === '1';
		const messageDiv = column.find('.wpr-ri-message');
		
		// Determine which message to show based on user type
		let message;
		if (isFree) {
			message = window.rocket_insights_i18n?.free_limit_reached || "You've reached your free limit. Upgrade to continue.";
		} else {
			message = window.rocket_insights_i18n?.paid_limit_reached || "You've reached the page limit. Please remove at least one page to continue.";
		}
		
		messageDiv
			.addClass('wpr-ri-message--error')
			.html(message)
			.show();
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
	 * @param {number} rowId  The database row ID.
	 */
	function showLoadingState(column, rowId) {
		column.attr('data-rocket-insights-id', rowId);

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
	 * Update column with test results.
	 *
	 * @param {jQuery} column The column element.
	 * @param {Object} result The test result data.
	 */
	function updateColumnWithResults(column, result) {
		// Reload the entire row from the server to get properly rendered HTML.
		const url = column.data('url');

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
		} );
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
