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
        const strongText = jQuery('<strong>').text(window.rocket_insights_i18n?.limit_reached || "You've reached your free limit.");
        const upgradeText = document.createTextNode(' ' + (window.rocket_insights_i18n?.upgrade_to_continue || 'Upgrade to continue.'));
        messageDiv.append(strongText).append(upgradeText);
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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SUFBSSxRQUFRLENBQUMsVUFBVSxLQUFLLFNBQVMsRUFBRTtJQUN0QyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUUsSUFBSSxDQUFDO0VBQ3BELENBQUMsTUFBTTtJQUNOLElBQUksQ0FBQyxDQUFDO0VBQ1A7RUFFQSxPQUFPO0lBQ04sSUFBSSxFQUFFO0VBQ1AsQ0FBQztBQUNGLENBQUMsQ0FBRSxDQUFDIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiLyoqXG4gKiBSb2NrZXQgSW5zaWdodHMgZnVuY3Rpb25hbGl0eSBmb3IgcG9zdCBsaXN0aW5nIHBhZ2VzXG4gKiBUaGlzIHNjcmlwdCBoYW5kbGVzIHBlcmZvcm1hbmNlIHNjb3JlIGRpc3BsYXkgYW5kIHVwZGF0ZXMgaW4gYWRtaW4gcG9zdCBsaXN0aW5nIHBhZ2VzXG4gKlxuICogQHNpbmNlIDMuMjAuMVxuICovXG5cbi8vIEV4cG9ydCBmb3IgdXNlIHdpdGggYnJvd3NlcmlmeS9iYWJlbGlmeSBpbiBndWxwXG5tb2R1bGUuZXhwb3J0cyA9IChmdW5jdGlvbigpIHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdC8qKlxuXHQgKiBQb2xsaW5nIGludGVydmFsIGZvciBjaGVja2luZyBvbmdvaW5nIHRlc3RzIChpbiBtaWxsaXNlY29uZHMpLlxuXHQgKi9cblx0Y29uc3QgUE9MTElOR19JTlRFUlZBTCA9IDUwMDA7IC8vIDUgc2Vjb25kc1xuXG5cdC8qKlxuXHQgKiBBY3RpdmUgcG9sbGluZyBpbnRlcnZhbHMgYnkgcG9zdCBJRC5cblx0ICovXG5cdGNvbnN0IGFjdGl2ZVBvbGxzID0ge307XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemUgUm9ja2V0IEluc2lnaHRzIG9uIHBvc3QgbGlzdGluZyBwYWdlc1xuXHQgKi9cblx0ZnVuY3Rpb24gaW5pdCgpIHtcblx0XHQvLyBBdHRhY2ggZXZlbnQgbGlzdGVuZXJzLlxuXHRcdGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCk7XG5cdFx0YXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCk7XG5cdFx0XG5cdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgYW55IHJvd3MgdGhhdCBhcmUgYWxyZWFkeSBydW5uaW5nLlxuXHRcdHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdGVzdC1wYWdlJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0XG5cdFx0XHQvLyBEb24ndCBhbGxvdyBjbGljayBpZiBubyBjcmVkaXRcblx0XHRcdGlmIChidXR0b24uaGFzQ2xhc3MoJ3dwci1yaS1uby1jcmVkaXQnKSkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cdFx0XHRcblx0XHRcdGNvbnN0IHVybCA9IGJ1dHRvbi5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXG5cdFx0XHRhZGROZXdQYWdlKHVybCwgY29sdW1uLCBidXR0b24pO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJSZS10ZXN0XCIgYnV0dG9ucyBhbmQgbGlua3MuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKSB7XG5cdFx0Ly8gU3VwcG9ydCBib3RoIGJ1dHRvbiBhbmQgbGluayBzdHlsZXMgd2l0aCBvbmUgaGFuZGxlci5cblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXJldGVzdDpub3QoLndwci1yaS1hY3Rpb24tLWRpc2FibGVkKSwgLndwci1yaS1yZXRlc3QtbGluaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGVsID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgdXJsID0gZWwuZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBlbC5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3Igcm93cyB0aGF0IGFyZSBjdXJyZW50bHkgcnVubmluZyB0ZXN0cy5cblx0ICovXG5cdGZ1bmN0aW9uIHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktbG9hZGluZycpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBqUXVlcnkodGhpcykuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXG5cdFx0XHRpZiAocm93SWQgJiYgIWFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBZGQgYSBuZXcgcGFnZSBmb3IgdGVzdGluZy5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIHRvIHRlc3QuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gYnV0dG9uIFRoZSBidXR0b24gdGhhdCB3YXMgY2xpY2tlZC5cblx0ICovXG5cdGZ1bmN0aW9uIGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbikge1xuXHRcdC8vIERpc2FibGUgYnV0dG9uIGFuZCBzaG93IGxvYWRpbmcgc3RhdGUuXG5cdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSkudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmFkZGluZyB8fCAnQWRkaW5nLi4uJyk7XG5cblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfYWRkX25ld19wYWdlJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0cGFnZV91cmw6IHVybFxuXHRcdFx0fSxcblx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmRhdGEuaWQpIHtcblx0XHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcmVzcG9uc2UuZGF0YS5pZCwgdXJsKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBDaGVjayBpZiB3ZSd2ZSByZWFjaGVkIHRoZSBsaW1pdCBhbmQgZGlzYWJsZSBhbGwgb3RoZXIgXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0XHRcdFx0XHRpZiAocmVzcG9uc2UuZGF0YS5jYW5fYWRkX3BhZ2VzID09PSBmYWxzZSB8fCByZXNwb25zZS5kYXRhLnJlbWFpbmluZ191cmxzID09PSAwKSB7XG5cdFx0XHRcdFx0XHRkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdGlmIChyZXNwb25zZS5kYXRhPy5jYW5fYWRkX3BhZ2VzID09PSBmYWxzZSkge1xuXHRcdFx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpLnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblx0XHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gU2hvdyBlcnJvciBtZXNzYWdlIGZvciBvdGhlciBlcnJvcnNcblx0XHRcdFx0XHRsZXQgZXJyb3JNZXNzYWdlID0gcmVzcG9uc2UuZGF0YT8ubWVzc2FnZSB8fCAnRXJyb3IgYWRkaW5nIHBhZ2UnO1xuXHRcdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgZXJyb3JNZXNzYWdlLCAnZXJyb3InKTtcblx0XHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSkudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdFx0XHR9XG5cdFx0XHR9LFxuXHRcdFx0ZXJyb3I6IGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uZXJyb3IgfHwgJ0FuIGVycm9yIG9jY3VycmVkJywgJ2Vycm9yJyk7XG5cdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKS50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdGpRdWVyeS5hamF4KHtcblx0XHRcdHVybDogYWpheHVybCxcblx0XHRcdHR5cGU6ICdQT1NUJyxcblx0XHRcdGRhdGE6IHtcblx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3JvY2tldF9pbnNpZ2h0c19yZXNldF9wYWdlJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0aWQ6IHJvd0lkXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcblx0XHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCk7XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCByZXNwb25zZS5kYXRhPy5tZXNzYWdlIHx8ICdFcnJvciByZXRlc3RpbmcgcGFnZScsICdlcnJvcicpO1xuXHRcdFx0XHR9XG5cdFx0XHR9LFxuXHRcdFx0ZXJyb3I6IGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uZXJyb3IgfHwgJ0FuIGVycm9yIG9jY3VycmVkJywgJ2Vycm9yJyk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3IgdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdC8vIENsZWFyIGFueSBleGlzdGluZyBwb2xsIGZvciB0aGlzIHJvdy5cblx0XHRpZiAoYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRjbGVhckludGVydmFsKGFjdGl2ZVBvbGxzW3Jvd0lkXSk7XG5cdFx0fVxuXG5cdFx0Ly8gU2V0IHVwIG5ldyBwb2xsaW5nIGludGVydmFsLlxuXHRcdGFjdGl2ZVBvbGxzW3Jvd0lkXSA9IHNldEludGVydmFsKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9LCBQT0xMSU5HX0lOVEVSVkFMKTtcblxuXHRcdC8vIEFsc28gY2hlY2sgaW1tZWRpYXRlbHkuXG5cdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDb21tb24gaGVscGVyIHRvIHNldCBsb2FkaW5nIHN0YXRlIGFuZCBzdGFydCBwb2xsaW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIHJvd0lkLCB1cmwpIHtcblx0XHQvLyBVcGRhdGUgY29sdW1uIHRvIGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKTtcblx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDaGVjayB0aGUgc3RhdHVzIG9mIGEgdGVzdC5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdGpRdWVyeS5hamF4KHtcblx0XHRcdHVybDogYWpheHVybCxcblx0XHRcdHR5cGU6ICdHRVQnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX2dldF9yZXN1bHRzJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0aWRzOiBbcm93SWRdXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmRhdGEgKSB7XG5cdFx0XHRcdFx0Y29uc3QgcmVzdWx0ID0gcmVzcG9uc2UuZGF0YS5yZXN1bHRzWzBdO1xuXG5cdFx0XHRcdFx0aWYgKCByZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJyApIHtcblx0XHRcdFx0XHRcdC8vIFN0b3AgcG9sbGluZy5cblx0XHRcdFx0XHRcdGNsZWFySW50ZXJ2YWwoIGFjdGl2ZVBvbGxzW3Jvd0lkXSApO1xuXHRcdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblxuXHRcdFx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBjb2x1bW4gd2l0aCByZXN1bHRzIChyZWxvYWQgcmVuZGVyZWQgSFRNTCBmcm9tIHNlcnZlcikuXG5cdFx0XHRcdFx0XHR1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyggY29sdW1uLCByZXN1bHQgKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTaG93IGxvYWRpbmcgc3RhdGUgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICovXG5cdGZ1bmN0aW9uIHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCkge1xuXHRcdGNvbHVtbi5hdHRyKCdkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZCcsIHJvd0lkKTtcblxuXHRcdC8vIENyZWF0ZSBlbGVtZW50cyBzYWZlbHkgdG8gcHJldmVudCBYU1Ncblx0XHRjb25zdCBsb2FkaW5nRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbG9hZGluZycpO1xuXHRcdGNvbnN0IGltZyA9IGpRdWVyeSgnPGltZz4nKS5hZGRDbGFzcygnd3ByLWxvYWRpbmctaW1nJykuYXR0cih7XG5cdFx0XHRzcmM6IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubG9hZGluZ19pbWcgfHwgJycsXG5cdFx0XHRhbHQ6ICdMb2FkaW5nLi4uJ1xuXHRcdH0pO1xuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1tZXNzYWdlJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblxuXHRcdGxvYWRpbmdEaXYuYXBwZW5kKGltZyk7XG5cdFx0Y29sdW1uLmVtcHR5KCkuYXBwZW5kKGxvYWRpbmdEaXYpLmFwcGVuZChtZXNzYWdlRGl2KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY29sdW1uIHdpdGggdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtPYmplY3R9IHJlc3VsdCBUaGUgdGVzdCByZXN1bHQgZGF0YS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KSB7XG5cdFx0Ly8gUmVsb2FkIHRoZSBlbnRpcmUgcm93IGZyb20gdGhlIHNlcnZlciB0byBnZXQgcHJvcGVybHkgcmVuZGVyZWQgSFRNTC5cblx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cdFx0XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX2dldF9jb2x1bW5faHRtbCcsXG5cdFx0XHRcdG5vbmNlOiB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ubm9uY2UgfHwgJycsXG5cdFx0XHRcdHVybDogdXJsXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YS5odG1sKSB7XG5cdFx0XHRcdFx0Y29sdW1uLnJlcGxhY2VXaXRoKHJlc3BvbnNlLmRhdGEuaHRtbCk7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gUmUtYXR0YWNoIGxpc3RlbmVycyB0byB0aGUgbmV3IGNvbnRlbnQuXG5cdFx0XHRcdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRcdFx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgYSBtZXNzYWdlIGluIHRoZSBjb2x1bW4uXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IG1lc3NhZ2UgVGhlIG1lc3NhZ2UgdG8gZGlzcGxheS5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHR5cGUgICAgVGhlIG1lc3NhZ2UgdHlwZSAoJ2Vycm9yJyBvciAnc3VjY2VzcycpLlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd01lc3NhZ2UoY29sdW1uLCBtZXNzYWdlLCB0eXBlKSB7XG5cdFx0Y29uc3QgbWVzc2FnZUVsID0gY29sdW1uLmZpbmQoJy53cHItcmktbWVzc2FnZScpO1xuXHRcdC8vIENsZWFyIGFueSBleGlzdGluZyBjb250ZW50IGZpcnN0XG5cdFx0bWVzc2FnZUVsLnN0b3AodHJ1ZSwgdHJ1ZSkuZW1wdHkoKTtcblx0XHRjb25zdCBwID0galF1ZXJ5KCc8cD4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UtJyArIHR5cGUpLnRleHQobWVzc2FnZSk7XG5cdFx0bWVzc2FnZUVsLmFwcGVuZChwKS5zaG93KCk7XG5cdFx0XG5cdFx0Ly8gQXV0by1oaWRlIGFmdGVyIDUgc2Vjb25kcy5cblx0XHRzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuXHRcdFx0bWVzc2FnZUVsLmZhZGVPdXQoKTtcblx0XHR9LCA1MDAwKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBEaXNhYmxlIGFsbCBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zIHdoZW4gdGhlIFVSTCBsaW1pdCBpcyByZWFjaGVkLlxuXHQgKi9cblx0ZnVuY3Rpb24gZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktdGVzdC1wYWdlOm5vdCgud3ByLXJpLW5vLWNyZWRpdCknKS5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0YnV0dG9uLmFkZENsYXNzKCd3cHItcmktbm8tY3JlZGl0JykucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblx0XHRcdFxuXHRcdFx0Ly8gQWRkIHRoZSBcIllvdSd2ZSByZWFjaGVkIHlvdXIgbGltaXRcIiBtZXNzYWdlIGlmIG5vdCBhbHJlYWR5IHByZXNlbnQuXG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IGNyZWRpdE1lc3NhZ2UgPSBjb2x1bW4uZmluZCgnLndwci1yaS1jcmVkaXQtbWVzc2FnZScpO1xuXHRcdFx0XG5cdFx0XHRpZiAoY3JlZGl0TWVzc2FnZS5sZW5ndGggPT09IDApIHtcblx0XHRcdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLWNyZWRpdC1tZXNzYWdlJyk7XG5cdFx0XHRcdGNvbnN0IHN0cm9uZ1RleHQgPSBqUXVlcnkoJzxzdHJvbmc+JykudGV4dChcblx0XHRcdFx0XHR3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmxpbWl0X3JlYWNoZWQgfHwgXCJZb3UndmUgcmVhY2hlZCB5b3VyIGZyZWUgbGltaXQuXCJcblx0XHRcdFx0KTtcblx0XHRcdFx0Y29uc3QgdXBncmFkZVRleHQgPSBkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZSgnICcgKyAod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy51cGdyYWRlX3RvX2NvbnRpbnVlIHx8ICdVcGdyYWRlIHRvIGNvbnRpbnVlLicpKTtcblx0XHRcdFx0XG5cdFx0XHRcdG1lc3NhZ2VEaXYuYXBwZW5kKHN0cm9uZ1RleHQpLmFwcGVuZCh1cGdyYWRlVGV4dCk7XG5cdFx0XHRcdGJ1dHRvbi5hZnRlcihtZXNzYWdlRGl2KTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8vIEF1dG8taW5pdGlhbGl6ZSBvbiBET00gcmVhZHlcblx0aWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdsb2FkaW5nJykge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBpbml0KTtcblx0fSBlbHNlIHtcblx0XHRpbml0KCk7XG5cdH1cblxuXHRyZXR1cm4ge1xuXHRcdGluaXQ6IGluaXRcblx0fTtcbn0pKCk7XG4iXX0=
