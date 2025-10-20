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
    // Clear any existing content first
    messageEl.stop(true, true).empty();
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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ZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHQvKipcblx0ICogUG9sbGluZyBpbnRlcnZhbCBmb3IgY2hlY2tpbmcgb25nb2luZyB0ZXN0cyAoaW4gbWlsbGlzZWNvbmRzKS5cblx0ICovXG5cdGNvbnN0IFBPTExJTkdfSU5URVJWQUwgPSA1MDAwOyAvLyA1IHNlY29uZHNcblxuXHQvKipcblx0ICogQWN0aXZlIHBvbGxpbmcgaW50ZXJ2YWxzIGJ5IHBvc3QgSUQuXG5cdCAqL1xuXHRjb25zdCBhY3RpdmVQb2xscyA9IHt9O1xuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplIFJvY2tldCBJbnNpZ2h0cyBvbiBwb3N0IGxpc3RpbmcgcGFnZXNcblx0ICovXG5cdGZ1bmN0aW9uIGluaXQoKSB7XG5cdFx0Ly8gQXR0YWNoIGV2ZW50IGxpc3RlbmVycy5cblx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFxuXHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIGFueSByb3dzIHRoYXQgYXJlIGFscmVhZHkgcnVubmluZy5cblx0XHRzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpIHtcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXRlc3QtcGFnZScsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdFxuXHRcdFx0Ly8gRG9uJ3QgYWxsb3cgY2xpY2sgaWYgbm8gY3JlZGl0XG5cdFx0XHRpZiAoYnV0dG9uLmhhc0NsYXNzKCd3cHItcmktbm8tY3JlZGl0JykpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdFx0XG5cdFx0XHRjb25zdCB1cmwgPSBidXR0b24uZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblxuXHRcdFx0YWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiUmUtdGVzdFwiIGJ1dHRvbnMgYW5kIGxpbmtzLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCkge1xuXHRcdC8vIE9sZCBidXR0b24gc3R5bGVcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXJldGVzdDpub3QoLndwci1yaS1hY3Rpb24tLWRpc2FibGVkKScsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGJ1dHRvbi5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSk7XG5cdFx0XG5cdFx0Ly8gTmV3IGxpbmsgc3R5bGVcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgbGluayA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGxpbmsuZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBsaW5rLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKS50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uYWRkaW5nIHx8ICdBZGRpbmcuLi4nKTtcblxuXHRcdGpRdWVyeS5hamF4KHtcblx0XHRcdHVybDogYWpheHVybCxcblx0XHRcdHR5cGU6ICdQT1NUJyxcblx0XHRcdGRhdGE6IHtcblx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3JvY2tldF9pbnNpZ2h0c19hZGRfbmV3X3BhZ2UnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRwYWdlX3VybDogdXJsXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YS5pZCkge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZSBjb2x1bW4gd2l0aCBsb2FkaW5nIHN0YXRlLlxuXHRcdFx0XHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByZXNwb25zZS5kYXRhLmlkKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciByZXN1bHRzLlxuXHRcdFx0XHRcdHN0YXJ0UG9sbGluZyhyZXNwb25zZS5kYXRhLmlkLCB1cmwsIGNvbHVtbik7XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0Ly8gU2hvdyBlcnJvciBtZXNzYWdlLlxuXHRcdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgcmVzcG9uc2UuZGF0YT8ubWVzc2FnZSB8fCAnRXJyb3IgYWRkaW5nIHBhZ2UnLCAnZXJyb3InKTtcblx0XHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSkudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdFx0XHR9XG5cdFx0XHR9LFxuXHRcdFx0ZXJyb3I6IGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uZXJyb3IgfHwgJ0FuIGVycm9yIG9jY3VycmVkJywgJ2Vycm9yJyk7XG5cdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKS50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdGpRdWVyeS5hamF4KHtcblx0XHRcdHVybDogYWpheHVybCxcblx0XHRcdHR5cGU6ICdQT1NUJyxcblx0XHRcdGRhdGE6IHtcblx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3JvY2tldF9pbnNpZ2h0c19yZXNldF9wYWdlJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0cm93X2lkOiByb3dJZFxuXHRcdFx0fSxcblx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIHRvIGxvYWRpbmcgc3RhdGUuXG5cdFx0XHRcdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciByZXN1bHRzLlxuXHRcdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgcmVzcG9uc2UuZGF0YT8ubWVzc2FnZSB8fCAnRXJyb3IgcmV0ZXN0aW5nIHBhZ2UnLCAnZXJyb3InKTtcblx0XHRcdFx0fVxuXHRcdFx0fSxcblx0XHRcdGVycm9yOiBmdW5jdGlvbigpIHtcblx0XHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmVycm9yIHx8ICdBbiBlcnJvciBvY2N1cnJlZCcsICdlcnJvcicpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHRlc3QgcmVzdWx0cy5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHQvLyBDbGVhciBhbnkgZXhpc3RpbmcgcG9sbCBmb3IgdGhpcyByb3cuXG5cdFx0aWYgKGFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0Y2xlYXJJbnRlcnZhbChhY3RpdmVQb2xsc1tyb3dJZF0pO1xuXHRcdH1cblxuXHRcdC8vIFNldCB1cCBuZXcgcG9sbGluZyBpbnRlcnZhbC5cblx0XHRhY3RpdmVQb2xsc1tyb3dJZF0gPSBzZXRJbnRlcnZhbChmdW5jdGlvbigpIHtcblx0XHRcdGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSwgUE9MTElOR19JTlRFUlZBTCk7XG5cblx0XHQvLyBBbHNvIGNoZWNrIGltbWVkaWF0ZWx5LlxuXHRcdGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdH1cblxuXHQvKipcblx0ICogQ2hlY2sgdGhlIHN0YXR1cyBvZiBhIHRlc3QuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfZ2V0X3Jlc3VsdHMnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRpZHM6IFtyb3dJZF1cblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5kYXRhICYmIHJlc3BvbnNlLmRhdGEubGVuZ3RoID4gMCkge1xuXHRcdFx0XHRcdGNvbnN0IHJlc3VsdCA9IHJlc3BvbnNlLmRhdGFbMF07XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gQ2hlY2sgaWYgdGVzdCBpcyBjb21wbGV0ZSBvciBmYWlsZWQuXG5cdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nIGZvciBhbnkgc3RhdHVzIHRoYXQncyBub3QgJ2luLXByb2dyZXNzJyBvciBleHBsaWNpdGx5IHJ1bm5pbmdcblx0XHRcdFx0XHRpZiAocmVzdWx0LnN0YXR1cyAhPT0gJ2luLXByb2dyZXNzJyAmJiAhcmVzdWx0LmlzX3J1bm5pbmcpIHtcblx0XHRcdFx0XHRcdC8vIFN0b3AgcG9sbGluZy5cblx0XHRcdFx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHRcdFx0XHRcdGRlbGV0ZSBhY3RpdmVQb2xsc1tyb3dJZF07XG5cdFx0XHRcdFx0XHRcblx0XHRcdFx0XHRcdC8vIFVwZGF0ZSB0aGUgY29sdW1uIHdpdGggcmVzdWx0cy5cblx0XHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTaG93IGxvYWRpbmcgc3RhdGUgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICovXG5cdGZ1bmN0aW9uIHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCkge1xuXHRcdGNvbHVtbi5hdHRyKCdkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZCcsIHJvd0lkKTtcblx0XHRjb2x1bW4uaHRtbChcblx0XHRcdCc8ZGl2IGNsYXNzPVwid3ByLXJpLWxvYWRpbmdcIj4nICtcblx0XHRcdCc8aW1nIGNsYXNzPVwid3ByLWxvYWRpbmctaW1nXCIgc3JjPVwiJyArICh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmxvYWRpbmdfaW1nIHx8ICcnKSArICdcIiBhbHQ9XCJMb2FkaW5nLi4uXCIvPicgK1xuXHRcdFx0JzwvZGl2PicgK1xuXHRcdFx0JzxkaXYgY2xhc3M9XCJ3cHItcmktbWVzc2FnZVwiIHN0eWxlPVwiZGlzcGxheTogbm9uZTtcIj48L2Rpdj4nXG5cdFx0KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY29sdW1uIHdpdGggdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtPYmplY3R9IHJlc3VsdCBUaGUgdGVzdCByZXN1bHQgZGF0YS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KSB7XG5cdFx0Ly8gUmVsb2FkIHRoZSBlbnRpcmUgcm93IGZyb20gdGhlIHNlcnZlciB0byBnZXQgcHJvcGVybHkgcmVuZGVyZWQgSFRNTC5cblx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cdFx0XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX2dldF9jb2x1bW5faHRtbCcsXG5cdFx0XHRcdG5vbmNlOiB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ubm9uY2UgfHwgJycsXG5cdFx0XHRcdHVybDogdXJsXG5cdFx0XHR9LFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YS5odG1sKSB7XG5cdFx0XHRcdFx0Y29sdW1uLnJlcGxhY2VXaXRoKHJlc3BvbnNlLmRhdGEuaHRtbCk7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gUmUtYXR0YWNoIGxpc3RlbmVycyB0byB0aGUgbmV3IGNvbnRlbnQuXG5cdFx0XHRcdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRcdFx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgYSBtZXNzYWdlIGluIHRoZSBjb2x1bW4uXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IG1lc3NhZ2UgVGhlIG1lc3NhZ2UgdG8gZGlzcGxheS5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHR5cGUgICAgVGhlIG1lc3NhZ2UgdHlwZSAoJ2Vycm9yJyBvciAnc3VjY2VzcycpLlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd01lc3NhZ2UoY29sdW1uLCBtZXNzYWdlLCB0eXBlKSB7XG5cdFx0Y29uc3QgbWVzc2FnZUVsID0gY29sdW1uLmZpbmQoJy53cHItcmktbWVzc2FnZScpO1xuXHRcdC8vIENsZWFyIGFueSBleGlzdGluZyBjb250ZW50IGZpcnN0XG5cdFx0bWVzc2FnZUVsLnN0b3AodHJ1ZSwgdHJ1ZSkuZW1wdHkoKTtcblx0XHRtZXNzYWdlRWwuaHRtbCgnPHAgY2xhc3M9XCJ3cHItcmktbWVzc2FnZS0nICsgdHlwZSArICdcIj4nICsgbWVzc2FnZSArICc8L3A+Jykuc2hvdygpO1xuXHRcdFxuXHRcdC8vIEF1dG8taGlkZSBhZnRlciA1IHNlY29uZHMuXG5cdFx0c2V0VGltZW91dChmdW5jdGlvbigpIHtcblx0XHRcdG1lc3NhZ2VFbC5mYWRlT3V0KCk7XG5cdFx0fSwgNTAwMCk7XG5cdH1cblxuXHQvLyBBdXRvLWluaXRpYWxpemUgb24gRE9NIHJlYWR5XG5cdGlmIChkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnbG9hZGluZycpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgaW5pdCk7XG5cdH0gZWxzZSB7XG5cdFx0aW5pdCgpO1xuXHR9XG5cblx0cmV0dXJuIHtcblx0XHRpbml0OiBpbml0XG5cdH07XG59KSgpO1xuIl19
