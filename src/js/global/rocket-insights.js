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
	 * Track credit availability state.
	 */
	let hasCredit = true;

	/**
	 * Track whether adding pages is allowed.
	 */
	let canAddPages = true;

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
			const hasCredit = response?.has_credit ?? response?.data?.has_credit;

			// Update credit state from response
			updateCreditState(hasCredit, canAdd);

			if (success && id) {
				// Begin common loading + polling flow.
				beginLoadingAndPoll(column, id, url);
				return;
			}

			// If backend says we cannot add pages, re-enable and reset label without error banner.
			if (canAdd === false) {
				button.prop('disabled', false)
					.text(window.rocket_insights_i18n?.test_page || 'Test the page');
				return;
			}

			// Other errors
			button.prop('disabled', false)
				.text(window.rocket_insights_i18n?.test_page || 'Test the page');
		}).catch((error) => {
			// wp.apiFetch throws on WP_Error; try to surface a helpful message.
			console.error(error);
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
				// Update credit state from response
				updateCreditState(response.has_credit, response.can_add_pages);

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

					// Update credit state from response
					updateCreditState(response.has_credit, response.can_add_pages);

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

	/**
	 * Update credit and page limit state.
	 *
	 * @param {boolean} responseHasCredit Whether the user has credit.
	 * @param {boolean} responseCanAddPages Whether the user can add pages.
	 */
	function updateCreditState(responseHasCredit, responseCanAddPages) {
		// Track if state actually changed
		const creditChanged = responseHasCredit !== undefined && hasCredit !== responseHasCredit;
		const canAddChanged = responseCanAddPages !== undefined && canAddPages !== responseCanAddPages;

		if (creditChanged) {
			hasCredit = responseHasCredit;
		}

		if (canAddChanged) {
			canAddPages = responseCanAddPages;
		}

		// If credit or page limit state changed, update all buttons
		if (creditChanged || canAddChanged) {
			updateAllRetestButtons();
		}
	}

	/**
	 * Update all Re-test buttons based on current credit state.
	 */
	function updateAllRetestButtons() {
		// Update all Re-test button links
		jQuery('.wpr-ri-retest-link').each(function() {
			const button = jQuery(this);
			const column = button.closest('.wpr-ri-column');
			const rowId = column.data('rocket-insights-id');

			// Check if this row is currently being processed
			const isRunning = rowId && activePolls[rowId];

			if (!hasCredit || isRunning) {
				// Disable the button
				if (button.is('button')) {
					button.prop('disabled', true);
					button.addClass('wpr-ri-disabled');
				} else {
					// It's a span, already styled as disabled in PHP
					button.addClass('wpr-ri-disabled');
				}

				// Show the no-credit message if it exists in the same actions wrapper
				const actionsWrapper = button.closest('.wpr-ri-actions-wrapper');
				const noCreditText = actionsWrapper.find('.wpr-ri-no-credit-text');
				if (noCreditText.length && !hasCredit) {
					noCreditText.show();
				}
			} else {
				// Re-enable the button
				if (button.is('button')) {
					button.prop('disabled', false);
					button.removeClass('wpr-ri-disabled');
				} else {
					// Convert span to button if credit is restored
					const newButton = jQuery('<button>')
						.attr('type', 'button')
						.attr('class', button.attr('class'))
						.attr('data-url', button.data('url'))
						.removeClass('wpr-ri-disabled')
						.html(button.html());
					button.replaceWith(newButton);
				}

				// Hide the no-credit message
				const actionsWrapper = button.closest('.wpr-ri-actions-wrapper');
				const noCreditText = actionsWrapper.find('.wpr-ri-no-credit-text');
				if (noCreditText.length) {
					noCreditText.hide();
				}
			}
		});

		// Update all "Test the page" buttons
		jQuery('.wpr-ri-test-page').each(function() {
			const button = jQuery(this);

			if (!hasCredit || !canAddPages) {
				// Disable test button and add no-credit class
				button.addClass('wpr-ri-no-credit');
				button.prop('disabled', true);
			} else {
				// Enable test button
				button.removeClass('wpr-ri-no-credit');
				button.prop('disabled', false);
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
