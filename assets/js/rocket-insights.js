(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

/**
 * Rocket Insights functionality for post listing pages
 * This script handles performance score display and updates in admin post listing pages
 *
 * @since 3.20.1
 */

// Export for use with browserify/babelify in gulp
module.exports = function () {
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
    jQuery(document).on('click', '.wpr-ri-test-page', function (e) {
      e.preventDefault();
      const button = jQuery(this);
      const url = button.data('url');
      const column = button.closest('.wpr-ri-column');
      const canAddPages = column.attr('data-can-add-pages') === '1';
      if (!canAddPages) {
        showLimitMessage(column, button);
        return;
      }
      addNewPage(url, column, button);
    });
  }

  /**
   * Attach click listeners to "Re-test" buttons and links.
   */
  function attachRetestListeners() {
    // Support both button and link styles with one handler.
    jQuery(document).on('click', '.wpr-ri-retest:not(.wpr-ri-action--disabled), .wpr-ri-retest-link', function (e) {
      e.preventDefault();
      const el = jQuery(this);
      const url = el.data('url');
      const column = el.closest('.wpr-ri-column');
      const rowId = column.data('rocket-insights-id');
      if (!rowId) {
        return;
      }

      // Retest should only proceed when the user has credit for the test.
      const hasCredit = column.attr('data-has-credit') === '1';
      if (!hasCredit) {
        showLimitMessage(column, el);
        return;
      }
      retestPage(rowId, url, column);
    });
  }

  /**
   * Start polling for rows that are currently running tests.
   */
  function startPollingForRunningTests() {
    jQuery('.wpr-ri-loading').each(function () {
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

    // Hide any previous messages
    const messageDiv = column.find('.wpr-ri-message');
    messageDiv.hide().removeClass('wpr-ri-error').empty();

    // Use REST (HEAD) but keep develop's robust handling.
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/',
      method: 'POST',
      data: {
        page_url: url
      }
    }).then(response => {
      const success = response?.success === true;
      const id = response?.id ?? response?.data?.id ?? null;
      const canAdd = response?.can_add_pages ?? response?.data?.can_add_pages;
      const message = response?.message ?? response?.data?.message;
      if (success && id) {
        // Begin common loading + polling flow.
        beginLoadingAndPoll(column, id, url);

        // Check if we've reached the limit and disable all other "Test the page" buttons.
        if (canAdd === false || response?.data?.remaining_urls === 0) {
          disableAllTestPageButtons();
        }
        return;
      }

      // If backend says we cannot add pages, show error message in the column.
      if (canAdd === false) {
        button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');

        // Display error message (server responses are pre-escaped and may contain intentional HTML)
        if (message) {
          messageDiv.addClass('wpr-ri-error').html(message).show();
        }
        return;
      }

      // Other errors
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');

      // Display error message if available (server responses are pre-escaped and may contain intentional HTML)
      if (message) {
        messageDiv.addClass('wpr-ri-error').html(message).show();
      }
    }).catch(error => {
      // wp.apiFetch throws on WP_Error; try to surface a helpful message.
      console.error(error);
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');

      // Display error message - use text() for untrusted error messages
      const errorMessage = error?.message || 'An error occurred. Please try again.';
      messageDiv.addClass('wpr-ri-error').text(errorMessage).show();
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
    // Hide any previous messages
    const messageDiv = column.find('.wpr-ri-message');
    messageDiv.hide().removeClass('wpr-ri-error').empty();
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/' + rowId,
      method: 'PATCH'
    }).then(response => {
      if (response.success) {
        // Begin common loading + polling flow.
        beginLoadingAndPoll(column, rowId, url);
      } else {
        // Display error message if available (server responses are pre-escaped and may contain intentional HTML)
        const message = response?.message ?? response?.data?.message;
        if (message) {
          messageDiv.addClass('wpr-ri-error').html(message).show();
        }
      }
    }).catch(error => {
      console.error(error);

      // Display error message - use text() for untrusted error messages
      const errorMessage = error?.message || 'An error occurred. Please try again.';
      messageDiv.addClass('wpr-ri-error').text(errorMessage).show();
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
    activePolls[rowId] = setInterval(function () {
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
   * Show the per-row limit message (only in the clicked row).
   * Disables the clicked element momentarily while showing the message.
   *
   * @param {jQuery} column The column element.
   * @param {jQuery} clickedEl The element that triggered the action.
   */
  function showLimitMessage(column, clickedEl) {
    const messageHtml = column.find('.wpr-ri-limit-html').html() || window.rocket_insights_i18n?.limit_reached || '';
    const messageDiv = column.find('.wpr-ri-message');
    messageDiv.html(messageHtml).show();

    // Disable only the clicked element briefly to prevent spam clicks, then re-enable.
    if (clickedEl && clickedEl.prop) {
      clickedEl.prop('disabled', true);
      setTimeout(function () {
        clickedEl.prop('disabled', false);
      }, 3000);
    }
  }

  /**
   * Check the status of a test.
   *
   * @param {number} rowId  The database row ID.
   * @param {string} url    The URL being tested.
   * @param {jQuery} column The column element.
   */
  function checkStatus(rowId, url, column) {
    window.wp.apiFetch({
      path: window.wp.url.addQueryArgs('/wp-rocket/v1/rocket-insights/pages/progress', {
        ids: [rowId]
      })
    }).then(response => {
      if (response.success && Array.isArray(response.results)) {
        const result = response.results[0];
        if (result.status === 'completed' || result.status === 'failed') {
          // Stop polling.
          clearInterval(activePolls[rowId]);
          delete activePolls[rowId];

          // Update the column with results (reload rendered HTML from server).
          updateColumnWithResults(column, result);
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
    window.wp.apiFetch({
      path: window.wp.url.addQueryArgs('/wp-rocket/v1/rocket-insights/pages', {
        url: url
      })
    }).then(response => {
      if (response.success && response.html) {
        column.replaceWith(response.html);

        // Re-attach listeners to the new content.
        attachTestPageListeners();
        attachRetestListeners();
      }
    });
  }

  /**
   * Mark all remaining "Test the page" buttons as having reached the limit.
   * Updates data attributes so future clicks will show the limit message per-row.
   * Does NOT display any message immediately on all rows.
   */
  function disableAllTestPageButtons() {
    jQuery('.wpr-ri-test-page').each(function () {
      const button = jQuery(this);
      const column = button.closest('.wpr-ri-column');

      // Update the data attribute so future clicks will trigger the limit message.
      column.attr('data-can-add-pages', '0');
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
}();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFDOUIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUUvQyxNQUFNLFdBQVcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDLEtBQUssR0FBRztNQUU3RCxJQUFLLENBQUUsV0FBVyxFQUFHO1FBQ3BCLGdCQUFnQixDQUFFLE1BQU0sRUFBRSxNQUFPLENBQUM7UUFDbEM7TUFDRDtNQUVBLFVBQVUsQ0FBQyxHQUFHLEVBQUUsTUFBTSxF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QUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMseUJBQXlCLENBQUEsRUFBRztJQUNwQyxNQUFNLENBQUMsbUJBQW1CLENBQUMsQ0FBQyxJQUFJLENBQUMsWUFBVztNQUMzQyxNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDO01BQzNCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7O01BRS9DO01BQ0EsTUFBTSxDQUFDLElBQUksQ0FBQyxvQkFBb0IsRUFBRSxHQUFHLENBQUM7SUFDdkMsQ0FBQyxDQUFDO0VBQ0g7O0VBRUE7RUFDQSxJQUFJLFFBQVEsQ0FBQyxVQUFVLEtBQUssU0FBUyxFQUFFO0lBQ3RDLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyxrQkFBa0IsRUFBRSxJQUFJLENBQUM7RUFDcEQsQ0FBQyxNQUFNO0lBQ04sSUFBSSxDQUFDLENBQUM7RUFDUDtFQUVBLE9BQU87SUFDTixJQUFJLEVBQUU7RUFDUCxDQUFDO0FBQ0YsQ0FBQyxDQUFFLENBQUMiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKipcbiAqIFJvY2tldCBJbnNpZ2h0cyBmdW5jdGlvbmFsaXR5IGZvciBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqIFRoaXMgc2NyaXB0IGhhbmRsZXMgcGVyZm9ybWFuY2Ugc2NvcmUgZGlzcGxheSBhbmQgdXBkYXRlcyBpbiBhZG1pbiBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqXG4gKiBAc2luY2UgMy4yMC4xXG4gKi9cblxuLy8gRXhwb3J0IGZvciB1c2Ugd2l0aCBicm93c2VyaWZ5L2JhYmVsaWZ5IGluIGd1bHBcbm1vZHVsZS5leHBvcnRzID0gKGZ1bmN0aW9uKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblxuXHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIGFueSByb3dzIHRoYXQgYXJlIGFscmVhZHkgcnVubmluZy5cblx0XHRzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpIHtcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXRlc3QtcGFnZScsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGJ1dHRvbi5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXG5cdFx0XHRjb25zdCBjYW5BZGRQYWdlcyA9IGNvbHVtbi5hdHRyKCdkYXRhLWNhbi1hZGQtcGFnZXMnKSA9PT0gJzEnO1xuXG5cdFx0XHRpZiAoICEgY2FuQWRkUGFnZXMgKSB7XG5cdFx0XHRcdHNob3dMaW1pdE1lc3NhZ2UoIGNvbHVtbiwgYnV0dG9uICk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiUmUtdGVzdFwiIGJ1dHRvbnMgYW5kIGxpbmtzLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCkge1xuXHRcdC8vIFN1cHBvcnQgYm90aCBidXR0b24gYW5kIGxpbmsgc3R5bGVzIHdpdGggb25lIGhhbmRsZXIuXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3Q6bm90KC53cHItcmktYWN0aW9uLS1kaXNhYmxlZCksIC53cHItcmktcmV0ZXN0LWxpbmsnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBlbCA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGVsLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gZWwuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXG5cdFx0XHRpZiAoIXJvd0lkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gUmV0ZXN0IHNob3VsZCBvbmx5IHByb2NlZWQgd2hlbiB0aGUgdXNlciBoYXMgY3JlZGl0IGZvciB0aGUgdGVzdC5cblx0XHRcdGNvbnN0IGhhc0NyZWRpdCA9IGNvbHVtbi5hdHRyKCdkYXRhLWhhcy1jcmVkaXQnKSA9PT0gJzEnO1xuXG5cdFx0XHRpZiAoICEgaGFzQ3JlZGl0ICkge1xuXHRcdFx0XHRzaG93TGltaXRNZXNzYWdlKCBjb2x1bW4sIGVsICk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0cmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHJvd3MgdGhhdCBhcmUgY3VycmVudGx5IHJ1bm5pbmcgdGVzdHMuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKSB7XG5cdFx0alF1ZXJ5KCcud3ByLXJpLWxvYWRpbmcnKS5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgY29sdW1uID0galF1ZXJ5KHRoaXMpLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblx0XHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblxuXHRcdFx0aWYgKHJvd0lkICYmICFhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdFx0c3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQWRkIGEgbmV3IHBhZ2UgZm9yIHRlc3RpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCB0byB0ZXN0LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGJ1dHRvbiBUaGUgYnV0dG9uIHRoYXQgd2FzIGNsaWNrZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBhZGROZXdQYWdlKHVybCwgY29sdW1uLCBidXR0b24pIHtcblx0XHQvLyBEaXNhYmxlIGJ1dHRvbiBhbmQgc2hvdyBsb2FkaW5nIHN0YXRlLlxuXHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXG5cdFx0Ly8gSGlkZSBhbnkgcHJldmlvdXMgbWVzc2FnZXNcblx0XHRjb25zdCBtZXNzYWdlRGl2ID0gY29sdW1uLmZpbmQoJy53cHItcmktbWVzc2FnZScpO1xuXHRcdG1lc3NhZ2VEaXYuaGlkZSgpLnJlbW92ZUNsYXNzKCd3cHItcmktZXJyb3InKS5lbXB0eSgpO1xuXG5cdFx0Ly8gVXNlIFJFU1QgKEhFQUQpIGJ1dCBrZWVwIGRldmVsb3AncyByb2J1c3QgaGFuZGxpbmcuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKHtcblx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nLFxuXHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7IHBhZ2VfdXJsOiB1cmwgfSxcblx0XHR9KS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0Y29uc3Qgc3VjY2VzcyAgID0gcmVzcG9uc2U/LnN1Y2Nlc3MgPT09IHRydWU7XG5cdFx0XHRjb25zdCBpZCAgICAgICAgPSByZXNwb25zZT8uaWQgPz8gcmVzcG9uc2U/LmRhdGE/LmlkID8/IG51bGw7XG5cdFx0XHRjb25zdCBjYW5BZGQgICAgPSAocmVzcG9uc2U/LmNhbl9hZGRfcGFnZXMgPz8gcmVzcG9uc2U/LmRhdGE/LmNhbl9hZGRfcGFnZXMpO1xuXHRcdFx0Y29uc3QgbWVzc2FnZSAgID0gcmVzcG9uc2U/Lm1lc3NhZ2UgPz8gcmVzcG9uc2U/LmRhdGE/Lm1lc3NhZ2U7XG5cblx0XHRcdGlmIChzdWNjZXNzICYmIGlkKSB7XG5cdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgaWQsIHVybCk7XG5cblx0XHRcdFx0Ly8gQ2hlY2sgaWYgd2UndmUgcmVhY2hlZCB0aGUgbGltaXQgYW5kIGRpc2FibGUgYWxsIG90aGVyIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdFx0XHRcdGlmIChjYW5BZGQgPT09IGZhbHNlIHx8IHJlc3BvbnNlPy5kYXRhPy5yZW1haW5pbmdfdXJscyA9PT0gMCkge1xuXHRcdFx0XHRcdGRpc2FibGVBbGxUZXN0UGFnZUJ1dHRvbnMoKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIGJhY2tlbmQgc2F5cyB3ZSBjYW5ub3QgYWRkIHBhZ2VzLCBzaG93IGVycm9yIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0XHRcdGlmIChjYW5BZGQgPT09IGZhbHNlKSB7XG5cdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKVxuXHRcdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cblx0XHRcdFx0Ly8gRGlzcGxheSBlcnJvciBtZXNzYWdlIChzZXJ2ZXIgcmVzcG9uc2VzIGFyZSBwcmUtZXNjYXBlZCBhbmQgbWF5IGNvbnRhaW4gaW50ZW50aW9uYWwgSFRNTClcblx0XHRcdFx0aWYgKG1lc3NhZ2UpIHtcblx0XHRcdFx0XHRtZXNzYWdlRGl2LmFkZENsYXNzKCd3cHItcmktZXJyb3InKS5odG1sKG1lc3NhZ2UpLnNob3coKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIE90aGVyIGVycm9yc1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cblx0XHRcdC8vIERpc3BsYXkgZXJyb3IgbWVzc2FnZSBpZiBhdmFpbGFibGUgKHNlcnZlciByZXNwb25zZXMgYXJlIHByZS1lc2NhcGVkIGFuZCBtYXkgY29udGFpbiBpbnRlbnRpb25hbCBIVE1MKVxuXHRcdFx0aWYgKG1lc3NhZ2UpIHtcblx0XHRcdFx0bWVzc2FnZURpdi5hZGRDbGFzcygnd3ByLXJpLWVycm9yJykuaHRtbChtZXNzYWdlKS5zaG93KCk7XG5cdFx0XHR9XG5cdFx0fSkuY2F0Y2goKGVycm9yKSA9PiB7XG5cdFx0XHQvLyB3cC5hcGlGZXRjaCB0aHJvd3Mgb24gV1BfRXJyb3I7IHRyeSB0byBzdXJmYWNlIGEgaGVscGZ1bCBtZXNzYWdlLlxuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSlcblx0XHRcdFx0LnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblxuXHRcdFx0Ly8gRGlzcGxheSBlcnJvciBtZXNzYWdlIC0gdXNlIHRleHQoKSBmb3IgdW50cnVzdGVkIGVycm9yIG1lc3NhZ2VzXG5cdFx0XHRjb25zdCBlcnJvck1lc3NhZ2UgPSBlcnJvcj8ubWVzc2FnZSB8fCAnQW4gZXJyb3Igb2NjdXJyZWQuIFBsZWFzZSB0cnkgYWdhaW4uJztcblx0XHRcdG1lc3NhZ2VEaXYuYWRkQ2xhc3MoJ3dwci1yaS1lcnJvcicpLnRleHQoZXJyb3JNZXNzYWdlKS5zaG93KCk7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdC8vIEhpZGUgYW55IHByZXZpb3VzIG1lc3NhZ2VzXG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGNvbHVtbi5maW5kKCcud3ByLXJpLW1lc3NhZ2UnKTtcblx0XHRtZXNzYWdlRGl2LmhpZGUoKS5yZW1vdmVDbGFzcygnd3ByLXJpLWVycm9yJykuZW1wdHkoKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyByb3dJZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBEaXNwbGF5IGVycm9yIG1lc3NhZ2UgaWYgYXZhaWxhYmxlIChzZXJ2ZXIgcmVzcG9uc2VzIGFyZSBwcmUtZXNjYXBlZCBhbmQgbWF5IGNvbnRhaW4gaW50ZW50aW9uYWwgSFRNTClcblx0XHRcdFx0Y29uc3QgbWVzc2FnZSA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXHRcdFx0XHRpZiAobWVzc2FnZSkge1xuXHRcdFx0XHRcdG1lc3NhZ2VEaXYuYWRkQ2xhc3MoJ3dwci1yaS1lcnJvcicpLmh0bWwobWVzc2FnZSkuc2hvdygpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSApLmNhdGNoKCAoIGVycm9yICkgPT4ge1xuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cblx0XHRcdC8vIERpc3BsYXkgZXJyb3IgbWVzc2FnZSAtIHVzZSB0ZXh0KCkgZm9yIHVudHJ1c3RlZCBlcnJvciBtZXNzYWdlc1xuXHRcdFx0Y29uc3QgZXJyb3JNZXNzYWdlID0gZXJyb3I/Lm1lc3NhZ2UgfHwgJ0FuIGVycm9yIG9jY3VycmVkLiBQbGVhc2UgdHJ5IGFnYWluLic7XG5cdFx0XHRtZXNzYWdlRGl2LmFkZENsYXNzKCd3cHItcmktZXJyb3InKS50ZXh0KGVycm9yTWVzc2FnZSkuc2hvdygpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENvbW1vbiBoZWxwZXIgdG8gc2V0IGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCkge1xuXHRcdC8vIFVwZGF0ZSBjb2x1bW4gdG8gbG9hZGluZyBzdGF0ZSBhbmQgc3RhcnQgcG9sbGluZy5cblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpO1xuXHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgdGhlIHBlci1yb3cgbGltaXQgbWVzc2FnZSAob25seSBpbiB0aGUgY2xpY2tlZCByb3cpLlxuXHQgKiBEaXNhYmxlcyB0aGUgY2xpY2tlZCBlbGVtZW50IG1vbWVudGFyaWx5IHdoaWxlIHNob3dpbmcgdGhlIG1lc3NhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY2xpY2tlZEVsIFRoZSBlbGVtZW50IHRoYXQgdHJpZ2dlcmVkIHRoZSBhY3Rpb24uXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TGltaXRNZXNzYWdlKGNvbHVtbiwgY2xpY2tlZEVsKSB7XG5cdFx0Y29uc3QgbWVzc2FnZUh0bWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1saW1pdC1odG1sJykuaHRtbCgpIHx8IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubGltaXRfcmVhY2hlZCB8fCAnJztcblxuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0bWVzc2FnZURpdi5odG1sKG1lc3NhZ2VIdG1sKS5zaG93KCk7XG5cblx0XHQvLyBEaXNhYmxlIG9ubHkgdGhlIGNsaWNrZWQgZWxlbWVudCBicmllZmx5IHRvIHByZXZlbnQgc3BhbSBjbGlja3MsIHRoZW4gcmUtZW5hYmxlLlxuXHRcdGlmIChjbGlja2VkRWwgJiYgY2xpY2tlZEVsLnByb3ApIHtcblx0XHRcdGNsaWNrZWRFbC5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXHRcdFx0c2V0VGltZW91dChmdW5jdGlvbigpIHtcblx0XHRcdFx0Y2xpY2tlZEVsLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpO1xuXHRcdFx0fSwgMzAwMCk7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IFtyb3dJZF0gfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KCByZXNwb25zZS5yZXN1bHRzICkgKSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdCA9IHJlc3BvbnNlLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0aWYgKCByZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJyApIHtcblx0XHRcdFx0XHQvLyBTdG9wIHBvbGxpbmcuXG5cdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggYWN0aXZlUG9sbHNbcm93SWRdICk7XG5cdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSB0aGUgY29sdW1uIHdpdGggcmVzdWx0cyAocmVsb2FkIHJlbmRlcmVkIEhUTUwgZnJvbSBzZXJ2ZXIpLlxuXHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKCBjb2x1bW4sIHJlc3VsdCApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgcm93SWQpO1xuXG5cdFx0Ly8gQ3JlYXRlIGVsZW1lbnRzIHNhZmVseSB0byBwcmV2ZW50IFhTU1xuXHRcdGNvbnN0IGxvYWRpbmdEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1sb2FkaW5nJyk7XG5cdFx0Y29uc3QgaW1nID0galF1ZXJ5KCc8aW1nPicpLmFkZENsYXNzKCd3cHItbG9hZGluZy1pbWcnKS5hdHRyKHtcblx0XHRcdHNyYzogd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5sb2FkaW5nX2ltZyB8fCAnJyxcblx0XHRcdGFsdDogJ0xvYWRpbmcuLi4nXG5cdFx0fSk7XG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UnKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoaW1nKTtcblx0XHRjb2x1bW4uZW1wdHkoKS5hcHBlbmQobG9hZGluZ0RpdikuYXBwZW5kKG1lc3NhZ2VEaXYpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZSBjb2x1bW4gd2l0aCB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gcmVzdWx0IFRoZSB0ZXN0IHJlc3VsdCBkYXRhLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpIHtcblx0XHQvLyBSZWxvYWQgdGhlIGVudGlyZSByb3cgZnJvbSB0aGUgc2VydmVyIHRvIGdldCBwcm9wZXJseSByZW5kZXJlZCBIVE1MLlxuXHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogd2luZG93LndwLnVybC5hZGRRdWVyeUFyZ3MoICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcycsIHsgdXJsOiB1cmwgfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmh0bWwpIHtcblx0XHRcdFx0Y29sdW1uLnJlcGxhY2VXaXRoKHJlc3BvbnNlLmh0bWwpO1xuXG5cdFx0XHRcdC8vIFJlLWF0dGFjaCBsaXN0ZW5lcnMgdG8gdGhlIG5ldyBjb250ZW50LlxuXHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogTWFyayBhbGwgcmVtYWluaW5nIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMgYXMgaGF2aW5nIHJlYWNoZWQgdGhlIGxpbWl0LlxuXHQgKiBVcGRhdGVzIGRhdGEgYXR0cmlidXRlcyBzbyBmdXR1cmUgY2xpY2tzIHdpbGwgc2hvdyB0aGUgbGltaXQgbWVzc2FnZSBwZXItcm93LlxuXHQgKiBEb2VzIE5PVCBkaXNwbGF5IGFueSBtZXNzYWdlIGltbWVkaWF0ZWx5IG9uIGFsbCByb3dzLlxuXHQgKi9cblx0ZnVuY3Rpb24gZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktdGVzdC1wYWdlJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0XG5cdFx0XHQvLyBVcGRhdGUgdGhlIGRhdGEgYXR0cmlidXRlIHNvIGZ1dHVyZSBjbGlja3Mgd2lsbCB0cmlnZ2VyIHRoZSBsaW1pdCBtZXNzYWdlLlxuXHRcdFx0Y29sdW1uLmF0dHIoJ2RhdGEtY2FuLWFkZC1wYWdlcycsICcwJyk7XG5cdFx0fSk7XG5cdH1cblxuXHQvLyBBdXRvLWluaXRpYWxpemUgb24gRE9NIHJlYWR5XG5cdGlmIChkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnbG9hZGluZycpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgaW5pdCk7XG5cdH0gZWxzZSB7XG5cdFx0aW5pdCgpO1xuXHR9XG5cblx0cmV0dXJuIHtcblx0XHRpbml0OiBpbml0XG5cdH07XG59KSgpO1xuIl19
