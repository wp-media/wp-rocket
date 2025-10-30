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
    jQuery(document).on('click', '.wpr-ri-retest:not(.wpr-ri-action--disabled), .wpr-ri-retest-link', function (e) {
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
    button.prop('disabled', true).text(window.rocket_insights_i18n?.adding || 'Adding...');

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
        return;
      }

      // If backend says we cannot add pages, re-enable and reset label without error banner.
      if (canAdd === false) {
        button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
        return;
      }

      // Other errors
      showMessage(column, message || 'Error adding page', 'error');
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
    }).catch(error => {
      // wp.apiFetch throws on WP_Error; try to surface a helpful message.
      const errMsg = error?.message || error?.data?.message || window.rocket_insights_i18n?.error || 'An error occurred';
      showMessage(column, errMsg, 'error');
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
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
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/' + rowId,
      method: 'PATCH'
    }).then(response => {
      if (response.success) {
        // Begin common loading + polling flow.
        beginLoadingAndPoll(column, rowId, url);
      } else {
        showMessage(column, response?.message || 'Error retesting page', 'error');
      }
    }).catch(error => {
      showMessage(column, window.rocket_insights_i18n?.error || 'An error occurred', 'error');
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
      path: '/wp-rocket/v1/rocket-insights/pages/' + url
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
    const p = jQuery('<p>').addClass('wpr-ri-message-' + type).text(message);
    messageEl.append(p).show();

    // Auto-hide after 5 seconds.
    setTimeout(function () {
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
}();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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QUFNO0lBQ04sSUFBSSxDQUFDLENBQUM7RUFDUDtFQUVBLE9BQU87SUFDTixJQUFJLEVBQUU7RUFDUCxDQUFDO0FBQ0YsQ0FBQyxDQUFFLENBQUMiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKipcbiAqIFJvY2tldCBJbnNpZ2h0cyBmdW5jdGlvbmFsaXR5IGZvciBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqIFRoaXMgc2NyaXB0IGhhbmRsZXMgcGVyZm9ybWFuY2Ugc2NvcmUgZGlzcGxheSBhbmQgdXBkYXRlcyBpbiBhZG1pbiBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqXG4gKiBAc2luY2UgMy4yMC4xXG4gKi9cblxuLy8gRXhwb3J0IGZvciB1c2Ugd2l0aCBicm93c2VyaWZ5L2JhYmVsaWZ5IGluIGd1bHBcbm1vZHVsZS5leHBvcnRzID0gKGZ1bmN0aW9uKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblxuXHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIGFueSByb3dzIHRoYXQgYXJlIGFscmVhZHkgcnVubmluZy5cblx0XHRzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpIHtcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXRlc3QtcGFnZScsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblxuXHRcdFx0Ly8gRG9uJ3QgYWxsb3cgY2xpY2sgaWYgbm8gY3JlZGl0XG5cdFx0XHRpZiAoYnV0dG9uLmhhc0NsYXNzKCd3cHItcmktbm8tY3JlZGl0JykpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCB1cmwgPSBidXR0b24uZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblxuXHRcdFx0YWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiUmUtdGVzdFwiIGJ1dHRvbnMgYW5kIGxpbmtzLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCkge1xuXHRcdC8vIFN1cHBvcnQgYm90aCBidXR0b24gYW5kIGxpbmsgc3R5bGVzIHdpdGggb25lIGhhbmRsZXIuXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3Q6bm90KC53cHItcmktYWN0aW9uLS1kaXNhYmxlZCksIC53cHItcmktcmV0ZXN0LWxpbmsnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBlbCA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGVsLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gZWwuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXG5cdFx0XHRpZiAoIXJvd0lkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0cmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHJvd3MgdGhhdCBhcmUgY3VycmVudGx5IHJ1bm5pbmcgdGVzdHMuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKSB7XG5cdFx0alF1ZXJ5KCcud3ByLXJpLWxvYWRpbmcnKS5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgY29sdW1uID0galF1ZXJ5KHRoaXMpLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblx0XHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblxuXHRcdFx0aWYgKHJvd0lkICYmICFhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdFx0c3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQWRkIGEgbmV3IHBhZ2UgZm9yIHRlc3RpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCB0byB0ZXN0LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGJ1dHRvbiBUaGUgYnV0dG9uIHRoYXQgd2FzIGNsaWNrZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBhZGROZXdQYWdlKHVybCwgY29sdW1uLCBidXR0b24pIHtcblx0XHQvLyBEaXNhYmxlIGJ1dHRvbiBhbmQgc2hvdyBsb2FkaW5nIHN0YXRlLlxuXHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIHRydWUpLnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5hZGRpbmcgfHwgJ0FkZGluZy4uLicpO1xuXG5cdFx0Ly8gVXNlIFJFU1QgKEhFQUQpIGJ1dCBrZWVwIGRldmVsb3AncyByb2J1c3QgaGFuZGxpbmcuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKHtcblx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nLFxuXHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7IHBhZ2VfdXJsOiB1cmwgfSxcblx0XHR9KS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0Y29uc3Qgc3VjY2VzcyAgID0gcmVzcG9uc2U/LnN1Y2Nlc3MgPT09IHRydWU7XG5cdFx0XHRjb25zdCBpZCAgICAgICAgPSByZXNwb25zZT8uaWQgPz8gcmVzcG9uc2U/LmRhdGE/LmlkID8/IG51bGw7XG5cdFx0XHRjb25zdCBjYW5BZGQgICAgPSAocmVzcG9uc2U/LmNhbl9hZGRfcGFnZXMgPz8gcmVzcG9uc2U/LmRhdGE/LmNhbl9hZGRfcGFnZXMpO1xuXHRcdFx0Y29uc3QgbWVzc2FnZSAgID0gcmVzcG9uc2U/Lm1lc3NhZ2UgPz8gcmVzcG9uc2U/LmRhdGE/Lm1lc3NhZ2U7XG5cblx0XHRcdGlmIChzdWNjZXNzICYmIGlkKSB7XG5cdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgaWQsIHVybCk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gSWYgYmFja2VuZCBzYXlzIHdlIGNhbm5vdCBhZGQgcGFnZXMsIHJlLWVuYWJsZSBhbmQgcmVzZXQgbGFiZWwgd2l0aG91dCBlcnJvciBiYW5uZXIuXG5cdFx0XHRpZiAoY2FuQWRkID09PSBmYWxzZSkge1xuXHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSlcblx0XHRcdFx0XHQudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIE90aGVyIGVycm9yc1xuXHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCBtZXNzYWdlIHx8ICdFcnJvciBhZGRpbmcgcGFnZScsICdlcnJvcicpO1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0fSkuY2F0Y2goKGVycm9yKSA9PiB7XG5cdFx0XHQvLyB3cC5hcGlGZXRjaCB0aHJvd3Mgb24gV1BfRXJyb3I7IHRyeSB0byBzdXJmYWNlIGEgaGVscGZ1bCBtZXNzYWdlLlxuXHRcdFx0Y29uc3QgZXJyTXNnID1cblx0XHRcdFx0ZXJyb3I/Lm1lc3NhZ2UgfHxcblx0XHRcdFx0ZXJyb3I/LmRhdGE/Lm1lc3NhZ2UgfHxcblx0XHRcdFx0d2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fFxuXHRcdFx0XHQnQW4gZXJyb3Igb2NjdXJyZWQnO1xuXHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCBlcnJNc2csICdlcnJvcicpO1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyByb3dJZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHJlc3BvbnNlPy5tZXNzYWdlIHx8ICdFcnJvciByZXRlc3RpbmcgcGFnZScsICdlcnJvcicpO1xuXHRcdFx0fVxuXHRcdH0gKS5jYXRjaCggKCBlcnJvciApID0+IHtcblx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fCAnQW4gZXJyb3Igb2NjdXJyZWQnLCAnZXJyb3InKTtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3IgdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdC8vIENsZWFyIGFueSBleGlzdGluZyBwb2xsIGZvciB0aGlzIHJvdy5cblx0XHRpZiAoYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRjbGVhckludGVydmFsKGFjdGl2ZVBvbGxzW3Jvd0lkXSk7XG5cdFx0fVxuXG5cdFx0Ly8gU2V0IHVwIG5ldyBwb2xsaW5nIGludGVydmFsLlxuXHRcdGFjdGl2ZVBvbGxzW3Jvd0lkXSA9IHNldEludGVydmFsKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9LCBQT0xMSU5HX0lOVEVSVkFMKTtcblxuXHRcdC8vIEFsc28gY2hlY2sgaW1tZWRpYXRlbHkuXG5cdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDb21tb24gaGVscGVyIHRvIHNldCBsb2FkaW5nIHN0YXRlIGFuZCBzdGFydCBwb2xsaW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIHJvd0lkLCB1cmwpIHtcblx0XHQvLyBVcGRhdGUgY29sdW1uIHRvIGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKTtcblx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDaGVjayB0aGUgc3RhdHVzIG9mIGEgdGVzdC5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogd2luZG93LndwLnVybC5hZGRRdWVyeUFyZ3MoICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy9wcm9ncmVzcycsIHsgaWRzOiBbcm93SWRdIH0gKSxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgJiYgQXJyYXkuaXNBcnJheSggcmVzcG9uc2UucmVzdWx0cyApICkge1xuXHRcdFx0XHRjb25zdCByZXN1bHQgPSByZXNwb25zZS5yZXN1bHRzWzBdO1xuXG5cdFx0XHRcdGlmICggcmVzdWx0LnN0YXR1cyA9PT0gJ2NvbXBsZXRlZCcgfHwgcmVzdWx0LnN0YXR1cyA9PT0gJ2ZhaWxlZCcgKSB7XG5cdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nLlxuXHRcdFx0XHRcdGNsZWFySW50ZXJ2YWwoIGFjdGl2ZVBvbGxzW3Jvd0lkXSApO1xuXHRcdFx0XHRcdGRlbGV0ZSBhY3RpdmVQb2xsc1tyb3dJZF07XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgdGhlIGNvbHVtbiB3aXRoIHJlc3VsdHMgKHJlbG9hZCByZW5kZXJlZCBIVE1MIGZyb20gc2VydmVyKS5cblx0XHRcdFx0XHR1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyggY29sdW1uLCByZXN1bHQgKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTaG93IGxvYWRpbmcgc3RhdGUgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICovXG5cdGZ1bmN0aW9uIHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCkge1xuXHRcdGNvbHVtbi5hdHRyKCdkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZCcsIHJvd0lkKTtcblxuXHRcdC8vIENyZWF0ZSBlbGVtZW50cyBzYWZlbHkgdG8gcHJldmVudCBYU1Ncblx0XHRjb25zdCBsb2FkaW5nRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbG9hZGluZycpO1xuXHRcdGNvbnN0IGltZyA9IGpRdWVyeSgnPGltZz4nKS5hZGRDbGFzcygnd3ByLWxvYWRpbmctaW1nJykuYXR0cih7XG5cdFx0XHRzcmM6IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubG9hZGluZ19pbWcgfHwgJycsXG5cdFx0XHRhbHQ6ICdMb2FkaW5nLi4uJ1xuXHRcdH0pO1xuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1tZXNzYWdlJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblxuXHRcdGxvYWRpbmdEaXYuYXBwZW5kKGltZyk7XG5cdFx0Y29sdW1uLmVtcHR5KCkuYXBwZW5kKGxvYWRpbmdEaXYpLmFwcGVuZChtZXNzYWdlRGl2KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY29sdW1uIHdpdGggdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtPYmplY3R9IHJlc3VsdCBUaGUgdGVzdCByZXN1bHQgZGF0YS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KSB7XG5cdFx0Ly8gUmVsb2FkIHRoZSBlbnRpcmUgcm93IGZyb20gdGhlIHNlcnZlciB0byBnZXQgcHJvcGVybHkgcmVuZGVyZWQgSFRNTC5cblx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nICsgdXJsLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmh0bWwpIHtcblx0XHRcdFx0Y29sdW1uLnJlcGxhY2VXaXRoKHJlc3BvbnNlLmh0bWwpO1xuXG5cdFx0XHRcdC8vIFJlLWF0dGFjaCBsaXN0ZW5lcnMgdG8gdGhlIG5ldyBjb250ZW50LlxuXHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBhIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiAgVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gbWVzc2FnZSBUaGUgbWVzc2FnZSB0byBkaXNwbGF5LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdHlwZSAgICBUaGUgbWVzc2FnZSB0eXBlICgnZXJyb3InIG9yICdzdWNjZXNzJykuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TWVzc2FnZShjb2x1bW4sIG1lc3NhZ2UsIHR5cGUpIHtcblx0XHRjb25zdCBtZXNzYWdlRWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIGNvbnRlbnQgZmlyc3Rcblx0XHRtZXNzYWdlRWwuc3RvcCh0cnVlLCB0cnVlKS5lbXB0eSgpO1xuXHRcdGNvbnN0IHAgPSBqUXVlcnkoJzxwPicpLmFkZENsYXNzKCd3cHItcmktbWVzc2FnZS0nICsgdHlwZSkudGV4dChtZXNzYWdlKTtcblx0XHRtZXNzYWdlRWwuYXBwZW5kKHApLnNob3coKTtcblxuXHRcdC8vIEF1dG8taGlkZSBhZnRlciA1IHNlY29uZHMuXG5cdFx0c2V0VGltZW91dChmdW5jdGlvbigpIHtcblx0XHRcdG1lc3NhZ2VFbC5mYWRlT3V0KCk7XG5cdFx0fSwgNTAwMCk7XG5cdH1cblxuXHQvLyBBdXRvLWluaXRpYWxpemUgb24gRE9NIHJlYWR5XG5cdGlmIChkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnbG9hZGluZycpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgaW5pdCk7XG5cdH0gZWxzZSB7XG5cdFx0aW5pdCgpO1xuXHR9XG5cblx0cmV0dXJuIHtcblx0XHRpbml0OiBpbml0XG5cdH07XG59KSgpO1xuIl19
