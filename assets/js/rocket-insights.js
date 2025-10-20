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
    // Old button style
    jQuery(document).on('click', '.wpr-ri-retest:not(.wpr-ri-action--disabled)', function (e) {
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
    jQuery(document).on('click', '.wpr-ri-retest-link', function (e) {
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
        if (response.success && response.data.row_id) {
          // Update column with loading state.
          showLoadingState(column, response.data.row_id);

          // Start polling for results.
          startPolling(response.data.row_id, url, column);
        } else {
          // Show error message.
          showMessage(column, response.data?.message || 'Error adding page', 'error');
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
        row_id: rowId
      },
      success: function (response) {
        if (response.success) {
          // Update to loading state.
          showLoadingState(column, rowId);

          // Start polling for results.
          startPolling(rowId, url, column);
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
      success: function (response) {
        if (response.success && response.data && response.data.length > 0) {
          const result = response.data[0];

          // Check if test is complete.
          if (result.status === 'completed' || result.status === 'blurred' || result.status === 'failed') {
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
    column.html('<div class="wpr-ri-loading">' + '<img class="wpr-loading-img" src="' + (window.rocket_insights_i18n?.loading_img || '') + '" alt="Loading..."/>' + '</div>' + '<div class="wpr-ri-message" style="display: none;"></div>');
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
    messageEl.html('<p class="wpr-ri-message-' + type + '">' + message + '</p>').show();

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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKipcbiAqIFJvY2tldCBJbnNpZ2h0cyBmdW5jdGlvbmFsaXR5IGZvciBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqIFRoaXMgc2NyaXB0IGhhbmRsZXMgcGVyZm9ybWFuY2Ugc2NvcmUgZGlzcGxheSBhbmQgdXBkYXRlcyBpbiBhZG1pbiBwb3N0IGxpc3RpbmcgcGFnZXNcbiAqXG4gKiBAc2luY2UgMy4yMC4xXG4gKi9cblxuLy8gRXhwb3J0IGZvciB1c2Ugd2l0aCBicm93c2VyaWZ5L2JhYmVsaWZ5IGluIGd1bHBcbm1vZHVsZS5leHBvcnRzID0gKGZ1bmN0aW9uKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcblx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciBhbnkgcm93cyB0aGF0IGFyZSBhbHJlYWR5IHJ1bm5pbmcuXG5cdFx0c3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKSB7XG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS10ZXN0LXBhZ2UnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRcblx0XHRcdC8vIERvbid0IGFsbG93IGNsaWNrIGlmIG5vIGNyZWRpdFxuXHRcdFx0aWYgKGJ1dHRvbi5oYXNDbGFzcygnd3ByLXJpLW5vLWNyZWRpdCcpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdFxuXHRcdFx0Y29uc3QgdXJsID0gYnV0dG9uLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBPbGQgYnV0dG9uIHN0eWxlXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3Q6bm90KC53cHItcmktYWN0aW9uLS1kaXNhYmxlZCknLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBidXR0b24uZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXG5cdFx0XHRpZiAoIXJvd0lkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0cmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0pO1xuXHRcdFxuXHRcdC8vIE5ldyBsaW5rIHN0eWxlXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3QtbGluaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGxpbmsgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBsaW5rLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gbGluay5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3Igcm93cyB0aGF0IGFyZSBjdXJyZW50bHkgcnVubmluZyB0ZXN0cy5cblx0ICovXG5cdGZ1bmN0aW9uIHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktbG9hZGluZycpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBqUXVlcnkodGhpcykuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXG5cdFx0XHRpZiAocm93SWQgJiYgIWFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBZGQgYSBuZXcgcGFnZSBmb3IgdGVzdGluZy5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIHRvIHRlc3QuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gYnV0dG9uIFRoZSBidXR0b24gdGhhdCB3YXMgY2xpY2tlZC5cblx0ICovXG5cdGZ1bmN0aW9uIGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbikge1xuXHRcdC8vIERpc2FibGUgYnV0dG9uIGFuZCBzaG93IGxvYWRpbmcgc3RhdGUuXG5cdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSkudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmFkZGluZyB8fCAnQWRkaW5nLi4uJyk7XG5cblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfYWRkX25ld19wYWdlJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0cGFnZV91cmw6IHVybFxuXHRcdFx0fSxcblx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmRhdGEucm93X2lkKSB7XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGNvbHVtbiB3aXRoIGxvYWRpbmcgc3RhdGUuXG5cdFx0XHRcdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJlc3BvbnNlLmRhdGEucm93X2lkKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciByZXN1bHRzLlxuXHRcdFx0XHRcdHN0YXJ0UG9sbGluZyhyZXNwb25zZS5kYXRhLnJvd19pZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdC8vIFNob3cgZXJyb3IgbWVzc2FnZS5cblx0XHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHJlc3BvbnNlLmRhdGE/Lm1lc3NhZ2UgfHwgJ0Vycm9yIGFkZGluZyBwYWdlJywgJ2Vycm9yJyk7XG5cdFx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpLnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblx0XHRcdFx0fVxuXHRcdFx0fSxcblx0XHRcdGVycm9yOiBmdW5jdGlvbigpIHtcblx0XHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmVycm9yIHx8ICdBbiBlcnJvciBvY2N1cnJlZCcsICdlcnJvcicpO1xuXHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSkudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFJldGVzdCBhbiBleGlzdGluZyBwYWdlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gcmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfcmVzZXRfcGFnZScsXG5cdFx0XHRcdG5vbmNlOiB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ubm9uY2UgfHwgJycsXG5cdFx0XHRcdHJvd19pZDogcm93SWRcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZSB0byBsb2FkaW5nIHN0YXRlLlxuXHRcdFx0XHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCk7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgcmVzdWx0cy5cblx0XHRcdFx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHJlc3BvbnNlLmRhdGE/Lm1lc3NhZ2UgfHwgJ0Vycm9yIHJldGVzdGluZyBwYWdlJywgJ2Vycm9yJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRlcnJvcjogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fCAnQW4gZXJyb3Igb2NjdXJyZWQnLCAnZXJyb3InKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX2dldF9yZXN1bHRzJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0aWRzOiBbcm93SWRdXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YSAmJiByZXNwb25zZS5kYXRhLmxlbmd0aCA+IDApIHtcblx0XHRcdFx0XHRjb25zdCByZXN1bHQgPSByZXNwb25zZS5kYXRhWzBdO1xuXHRcdFx0XHRcdFxuXHRcdFx0XHRcdC8vIENoZWNrIGlmIHRlc3QgaXMgY29tcGxldGUuXG5cdFx0XHRcdFx0aWYgKHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdibHVycmVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJykge1xuXHRcdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nLlxuXHRcdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbChhY3RpdmVQb2xsc1tyb3dJZF0pO1xuXHRcdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblx0XHRcdFx0XHRcdFxuXHRcdFx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBjb2x1bW4gd2l0aCByZXN1bHRzLlxuXHRcdFx0XHRcdFx0dXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgcm93SWQpO1xuXHRcdGNvbHVtbi5odG1sKFxuXHRcdFx0JzxkaXYgY2xhc3M9XCJ3cHItcmktbG9hZGluZ1wiPicgK1xuXHRcdFx0JzxpbWcgY2xhc3M9XCJ3cHItbG9hZGluZy1pbWdcIiBzcmM9XCInICsgKHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubG9hZGluZ19pbWcgfHwgJycpICsgJ1wiIGFsdD1cIkxvYWRpbmcuLi5cIi8+JyArXG5cdFx0XHQnPC9kaXY+JyArXG5cdFx0XHQnPGRpdiBjbGFzcz1cIndwci1yaS1tZXNzYWdlXCIgc3R5bGU9XCJkaXNwbGF5OiBub25lO1wiPjwvZGl2Pidcblx0XHQpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZSBjb2x1bW4gd2l0aCB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gcmVzdWx0IFRoZSB0ZXN0IHJlc3VsdCBkYXRhLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpIHtcblx0XHQvLyBSZWxvYWQgdGhlIGVudGlyZSByb3cgZnJvbSB0aGUgc2VydmVyIHRvIGdldCBwcm9wZXJseSByZW5kZXJlZCBIVE1MLlxuXHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblx0XHRcblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfZ2V0X2NvbHVtbl9odG1sJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0dXJsOiB1cmxcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5kYXRhLmh0bWwpIHtcblx0XHRcdFx0XHRjb2x1bW4ucmVwbGFjZVdpdGgocmVzcG9uc2UuZGF0YS5odG1sKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBSZS1hdHRhY2ggbGlzdGVuZXJzIHRvIHRoZSBuZXcgY29udGVudC5cblx0XHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBhIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiAgVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gbWVzc2FnZSBUaGUgbWVzc2FnZSB0byBkaXNwbGF5LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdHlwZSAgICBUaGUgbWVzc2FnZSB0eXBlICgnZXJyb3InIG9yICdzdWNjZXNzJykuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TWVzc2FnZShjb2x1bW4sIG1lc3NhZ2UsIHR5cGUpIHtcblx0XHRjb25zdCBtZXNzYWdlRWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0bWVzc2FnZUVsLmh0bWwoJzxwIGNsYXNzPVwid3ByLXJpLW1lc3NhZ2UtJyArIHR5cGUgKyAnXCI+JyArIG1lc3NhZ2UgKyAnPC9wPicpLnNob3coKTtcblx0XHRcblx0XHQvLyBBdXRvLWhpZGUgYWZ0ZXIgNSBzZWNvbmRzLlxuXHRcdHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG5cdFx0XHRtZXNzYWdlRWwuZmFkZU91dCgpO1xuXHRcdH0sIDUwMDApO1xuXHR9XG5cblx0Ly8gQXV0by1pbml0aWFsaXplIG9uIERPTSByZWFkeVxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGluaXQpO1xuXHR9IGVsc2Uge1xuXHRcdGluaXQoKTtcblx0fVxuXG5cdHJldHVybiB7XG5cdFx0aW5pdDogaW5pdFxuXHR9O1xufSkoKTtcbiJdfQ==
