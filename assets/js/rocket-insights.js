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
    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'rocket_rocket_insights_add_new_page',
        nonce: window.rocket_ajax_data?.nonce || '',
        page_url: url
      },
      success: function (response) {
        if (response.success && response.data.id) {
          // Begin common loading + polling flow.
          beginLoadingAndPoll(column, response.data.id, url);

          // Check if we've reached the limit and disable all other "Test the page" buttons.
          if (response.data.can_add_pages === false || response.data.remaining_urls === 0) {
            disableAllTestPageButtons();
          }
        } else {
          if (response.data?.can_add_pages === false) {
            button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
            return;
          }

          // Show error message for other errors
          let errorMessage = response.data?.message || 'Error adding page';
          showMessage(column, errorMessage, 'error');
          button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
        }
      },
      error: function () {
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
        id: rowId
      },
      success: function (response) {
        if (response.success) {
          // Begin common loading + polling flow.
          beginLoadingAndPoll(column, rowId, url);
        } else {
          showMessage(column, response.data?.message || 'Error retesting page', 'error');
        }
      },
      error: function () {
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
    jQuery.ajax({
      url: ajaxurl,
      type: 'GET',
      data: {
        action: 'rocket_rocket_insights_get_results',
        nonce: window.rocket_ajax_data?.nonce || '',
        ids: [rowId]
      },
      success: function (response) {
        if (response.success && response.data) {
          const result = response.data.results[0];
          if (result.status === 'completed' || result.status === 'failed') {
            // Stop polling.
            clearInterval(activePolls[rowId]);
            delete activePolls[rowId];

            // Update the column with results (reload rendered HTML from server).
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
    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'rocket_rocket_insights_get_column_html',
        nonce: window.rocket_ajax_data?.nonce || '',
        url: url
      },
      success: function (response) {
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
    const p = jQuery('<p>').addClass('wpr-ri-message-' + type).text(message);
    messageEl.append(p).show();

    // Auto-hide after 5 seconds.
    setTimeout(function () {
      messageEl.fadeOut();
    }, 5000);
  }

  /**
   * Disable all "Test the page" buttons when the URL limit is reached.
   */
  function disableAllTestPageButtons() {
    jQuery('.wpr-ri-test-page:not(.wpr-ri-no-credit)').each(function () {
      const button = jQuery(this);
      button.addClass('wpr-ri-no-credit').prop('disabled', true);

      // Add the "You've reached your limit" message if not already present.
      const column = button.closest('.wpr-ri-column');
      const creditMessage = column.find('.wpr-ri-credit-message');
      if (creditMessage.length === 0) {
        const messageDiv = jQuery('<div>').addClass('wpr-ri-credit-message');
        const isFreeUser = window.rocket_ajax_data?.is_free_user || false;
        const limitMessage = isFreeUser ? window.rocket_insights_i18n?.free_limit_reached : window.rocket_insights_i18n?.paid_limit_reached;
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
}();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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RSxJQUFJLENBQUM7RUFDcEQsQ0FBQyxNQUFNO0lBQ04sSUFBSSxDQUFDLENBQUM7RUFDUDtFQUVBLE9BQU87SUFDTixJQUFJLEVBQUU7RUFDUCxDQUFDO0FBQ0YsQ0FBQyxDQUFFLENBQUMiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKipcbiAqIFJvY2tldCBJbnNpZ2h0cyBmdW5jdGlvbmFsaXR5IGZvciBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqIFRoaXMgc2NyaXB0IGhhbmRsZXMgcGVyZm9ybWFuY2Ugc2NvcmUgZGlzcGxheSBhbmQgdXBkYXRlcyBpbiBhZG1pbiBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqXG4gKiBAc2luY2UgMy4yMC4xXG4gKi9cblxuLy8gRXhwb3J0IGZvciB1c2Ugd2l0aCBicm93c2VyaWZ5L2JhYmVsaWZ5IGluIGd1bHBcbm1vZHVsZS5leHBvcnRzID0gKGZ1bmN0aW9uKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcblx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciBhbnkgcm93cyB0aGF0IGFyZSBhbHJlYWR5IHJ1bm5pbmcuXG5cdFx0c3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKSB7XG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS10ZXN0LXBhZ2UnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRcblx0XHRcdC8vIERvbid0IGFsbG93IGNsaWNrIGlmIG5vIGNyZWRpdFxuXHRcdFx0aWYgKGJ1dHRvbi5oYXNDbGFzcygnd3ByLXJpLW5vLWNyZWRpdCcpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdFxuXHRcdFx0Y29uc3QgdXJsID0gYnV0dG9uLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBTdXBwb3J0IGJvdGggYnV0dG9uIGFuZCBsaW5rIHN0eWxlcyB3aXRoIG9uZSBoYW5kbGVyLlxuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmV0ZXN0Om5vdCgud3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQpLCAud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKS50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uYWRkaW5nIHx8ICdBZGRpbmcuLi4nKTtcblxuXHRcdGpRdWVyeS5hamF4KHtcblx0XHRcdHVybDogYWpheHVybCxcblx0XHRcdHR5cGU6ICdQT1NUJyxcblx0XHRcdGRhdGE6IHtcblx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3JvY2tldF9pbnNpZ2h0c19hZGRfbmV3X3BhZ2UnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRwYWdlX3VybDogdXJsXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YS5pZCkge1xuXHRcdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRcdGJlZ2luTG9hZGluZ0FuZFBvbGwoY29sdW1uLCByZXNwb25zZS5kYXRhLmlkLCB1cmwpO1xuXHRcdFx0XHRcdFxuXHRcdFx0XHRcdC8vIENoZWNrIGlmIHdlJ3ZlIHJlYWNoZWQgdGhlIGxpbWl0IGFuZCBkaXNhYmxlIGFsbCBvdGhlciBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHRcdFx0XHRcdGlmIChyZXNwb25zZS5kYXRhLmNhbl9hZGRfcGFnZXMgPT09IGZhbHNlIHx8IHJlc3BvbnNlLmRhdGEucmVtYWluaW5nX3VybHMgPT09IDApIHtcblx0XHRcdFx0XHRcdGRpc2FibGVBbGxUZXN0UGFnZUJ1dHRvbnMoKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0aWYgKHJlc3BvbnNlLmRhdGE/LmNhbl9hZGRfcGFnZXMgPT09IGZhbHNlKSB7XG5cdFx0XHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSkudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBTaG93IGVycm9yIG1lc3NhZ2UgZm9yIG90aGVyIGVycm9yc1xuXHRcdFx0XHRcdGxldCBlcnJvck1lc3NhZ2UgPSByZXNwb25zZS5kYXRhPy5tZXNzYWdlIHx8ICdFcnJvciBhZGRpbmcgcGFnZSc7XG5cdFx0XHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCBlcnJvck1lc3NhZ2UsICdlcnJvcicpO1xuXHRcdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKS50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRlcnJvcjogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fCAnQW4gZXJyb3Igb2NjdXJyZWQnLCAnZXJyb3InKTtcblx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpLnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBSZXRlc3QgYW4gZXhpc3RpbmcgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX3Jlc2V0X3BhZ2UnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRpZDogcm93SWRcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRcdGJlZ2luTG9hZGluZ0FuZFBvbGwoY29sdW1uLCByb3dJZCwgdXJsKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHJlc3BvbnNlLmRhdGE/Lm1lc3NhZ2UgfHwgJ0Vycm9yIHJldGVzdGluZyBwYWdlJywgJ2Vycm9yJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRlcnJvcjogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fCAnQW4gZXJyb3Igb2NjdXJyZWQnLCAnZXJyb3InKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENvbW1vbiBoZWxwZXIgdG8gc2V0IGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCkge1xuXHRcdC8vIFVwZGF0ZSBjb2x1bW4gdG8gbG9hZGluZyBzdGF0ZSBhbmQgc3RhcnQgcG9sbGluZy5cblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpO1xuXHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ0dFVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfZ2V0X3Jlc3VsdHMnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRpZHM6IFtyb3dJZF1cblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YSApIHtcblx0XHRcdFx0XHRjb25zdCByZXN1bHQgPSByZXNwb25zZS5kYXRhLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0XHRpZiAoIHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnICkge1xuXHRcdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nLlxuXHRcdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggYWN0aXZlUG9sbHNbcm93SWRdICk7XG5cdFx0XHRcdFx0XHRkZWxldGUgYWN0aXZlUG9sbHNbcm93SWRdO1xuXG5cdFx0XHRcdFx0XHQvLyBVcGRhdGUgdGhlIGNvbHVtbiB3aXRoIHJlc3VsdHMgKHJlbG9hZCByZW5kZXJlZCBIVE1MIGZyb20gc2VydmVyKS5cblx0XHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKCBjb2x1bW4sIHJlc3VsdCApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgcm93SWQpO1xuXG5cdFx0Ly8gQ3JlYXRlIGVsZW1lbnRzIHNhZmVseSB0byBwcmV2ZW50IFhTU1xuXHRcdGNvbnN0IGxvYWRpbmdEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1sb2FkaW5nJyk7XG5cdFx0Y29uc3QgaW1nID0galF1ZXJ5KCc8aW1nPicpLmFkZENsYXNzKCd3cHItbG9hZGluZy1pbWcnKS5hdHRyKHtcblx0XHRcdHNyYzogd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5sb2FkaW5nX2ltZyB8fCAnJyxcblx0XHRcdGFsdDogJ0xvYWRpbmcuLi4nXG5cdFx0fSk7XG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UnKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoaW1nKTtcblx0XHRjb2x1bW4uZW1wdHkoKS5hcHBlbmQobG9hZGluZ0RpdikuYXBwZW5kKG1lc3NhZ2VEaXYpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZSBjb2x1bW4gd2l0aCB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gcmVzdWx0IFRoZSB0ZXN0IHJlc3VsdCBkYXRhLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpIHtcblx0XHQvLyBSZWxvYWQgdGhlIGVudGlyZSByb3cgZnJvbSB0aGUgc2VydmVyIHRvIGdldCBwcm9wZXJseSByZW5kZXJlZCBIVE1MLlxuXHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblx0XHRcblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfZ2V0X2NvbHVtbl9odG1sJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0dXJsOiB1cmxcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5kYXRhLmh0bWwpIHtcblx0XHRcdFx0XHRjb2x1bW4ucmVwbGFjZVdpdGgocmVzcG9uc2UuZGF0YS5odG1sKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBSZS1hdHRhY2ggbGlzdGVuZXJzIHRvIHRoZSBuZXcgY29udGVudC5cblx0XHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBhIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiAgVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gbWVzc2FnZSBUaGUgbWVzc2FnZSB0byBkaXNwbGF5LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdHlwZSAgICBUaGUgbWVzc2FnZSB0eXBlICgnZXJyb3InIG9yICdzdWNjZXNzJykuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TWVzc2FnZShjb2x1bW4sIG1lc3NhZ2UsIHR5cGUpIHtcblx0XHRjb25zdCBtZXNzYWdlRWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIGNvbnRlbnQgZmlyc3Rcblx0XHRtZXNzYWdlRWwuc3RvcCh0cnVlLCB0cnVlKS5lbXB0eSgpO1xuXHRcdGNvbnN0IHAgPSBqUXVlcnkoJzxwPicpLmFkZENsYXNzKCd3cHItcmktbWVzc2FnZS0nICsgdHlwZSkudGV4dChtZXNzYWdlKTtcblx0XHRtZXNzYWdlRWwuYXBwZW5kKHApLnNob3coKTtcblx0XHRcblx0XHQvLyBBdXRvLWhpZGUgYWZ0ZXIgNSBzZWNvbmRzLlxuXHRcdHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG5cdFx0XHRtZXNzYWdlRWwuZmFkZU91dCgpO1xuXHRcdH0sIDUwMDApO1xuXHR9XG5cblx0LyoqXG5cdCAqIERpc2FibGUgYWxsIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMgd2hlbiB0aGUgVVJMIGxpbWl0IGlzIHJlYWNoZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS10ZXN0LXBhZ2U6bm90KC53cHItcmktbm8tY3JlZGl0KScpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRidXR0b24uYWRkQ2xhc3MoJ3dwci1yaS1uby1jcmVkaXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXHRcdFx0XG5cdFx0XHQvLyBBZGQgdGhlIFwiWW91J3ZlIHJlYWNoZWQgeW91ciBsaW1pdFwiIG1lc3NhZ2UgaWYgbm90IGFscmVhZHkgcHJlc2VudC5cblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3QgY3JlZGl0TWVzc2FnZSA9IGNvbHVtbi5maW5kKCcud3ByLXJpLWNyZWRpdC1tZXNzYWdlJyk7XG5cdFx0XHRcblx0XHRcdGlmIChjcmVkaXRNZXNzYWdlLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0XHRjb25zdCBtZXNzYWdlRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktY3JlZGl0LW1lc3NhZ2UnKTtcblx0XHRcdFx0Y29uc3QgaXNGcmVlVXNlciA9IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5pc19mcmVlX3VzZXIgfHwgZmFsc2U7XG5cdFx0XHRcdGNvbnN0IGxpbWl0TWVzc2FnZSA9IGlzRnJlZVVzZXIgXG5cdFx0XHRcdFx0PyB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmZyZWVfbGltaXRfcmVhY2hlZCBcblx0XHRcdFx0XHQ6IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ucGFpZF9saW1pdF9yZWFjaGVkO1xuXHRcdFx0XHRcblx0XHRcdFx0bWVzc2FnZURpdi5odG1sKGxpbWl0TWVzc2FnZSk7XG5cdFx0XHRcdGJ1dHRvbi5hZnRlcihtZXNzYWdlRGl2KTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8vIEF1dG8taW5pdGlhbGl6ZSBvbiBET00gcmVhZHlcblx0aWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdsb2FkaW5nJykge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBpbml0KTtcblx0fSBlbHNlIHtcblx0XHRpbml0KCk7XG5cdH1cblxuXHRyZXR1cm4ge1xuXHRcdGluaXQ6IGluaXRcblx0fTtcbn0pKCk7XG4iXX0=
