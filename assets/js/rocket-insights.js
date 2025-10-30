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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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ZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHQvKipcblx0ICogUG9sbGluZyBpbnRlcnZhbCBmb3IgY2hlY2tpbmcgb25nb2luZyB0ZXN0cyAoaW4gbWlsbGlzZWNvbmRzKS5cblx0ICovXG5cdGNvbnN0IFBPTExJTkdfSU5URVJWQUwgPSA1MDAwOyAvLyA1IHNlY29uZHNcblxuXHQvKipcblx0ICogQWN0aXZlIHBvbGxpbmcgaW50ZXJ2YWxzIGJ5IHBvc3QgSUQuXG5cdCAqL1xuXHRjb25zdCBhY3RpdmVQb2xscyA9IHt9O1xuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplIFJvY2tldCBJbnNpZ2h0cyBvbiBwb3N0IGxpc3RpbmcgcGFnZXNcblx0ICovXG5cdGZ1bmN0aW9uIGluaXQoKSB7XG5cdFx0Ly8gQXR0YWNoIGV2ZW50IGxpc3RlbmVycy5cblx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFxuXHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIGFueSByb3dzIHRoYXQgYXJlIGFscmVhZHkgcnVubmluZy5cblx0XHRzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpIHtcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXRlc3QtcGFnZScsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdFxuXHRcdFx0Ly8gRG9uJ3QgYWxsb3cgY2xpY2sgaWYgbm8gY3JlZGl0XG5cdFx0XHRpZiAoYnV0dG9uLmhhc0NsYXNzKCd3cHItcmktbm8tY3JlZGl0JykpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdFx0XG5cdFx0XHRjb25zdCB1cmwgPSBidXR0b24uZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblxuXHRcdFx0YWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiUmUtdGVzdFwiIGJ1dHRvbnMgYW5kIGxpbmtzLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCkge1xuXHRcdC8vIFN1cHBvcnQgYm90aCBidXR0b24gYW5kIGxpbmsgc3R5bGVzIHdpdGggb25lIGhhbmRsZXIuXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3Q6bm90KC53cHItcmktYWN0aW9uLS1kaXNhYmxlZCksIC53cHItcmktcmV0ZXN0LWxpbmsnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBlbCA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGVsLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gZWwuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXG5cdFx0XHRpZiAoIXJvd0lkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0cmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHJvd3MgdGhhdCBhcmUgY3VycmVudGx5IHJ1bm5pbmcgdGVzdHMuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKSB7XG5cdFx0alF1ZXJ5KCcud3ByLXJpLWxvYWRpbmcnKS5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgY29sdW1uID0galF1ZXJ5KHRoaXMpLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblx0XHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblxuXHRcdFx0aWYgKHJvd0lkICYmICFhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdFx0c3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQWRkIGEgbmV3IHBhZ2UgZm9yIHRlc3RpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCB0byB0ZXN0LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGJ1dHRvbiBUaGUgYnV0dG9uIHRoYXQgd2FzIGNsaWNrZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBhZGROZXdQYWdlKHVybCwgY29sdW1uLCBidXR0b24pIHtcblx0XHQvLyBEaXNhYmxlIGJ1dHRvbiBhbmQgc2hvdyBsb2FkaW5nIHN0YXRlLlxuXHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIHRydWUpLnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5hZGRpbmcgfHwgJ0FkZGluZy4uLicpO1xuXG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX2FkZF9uZXdfcGFnZScsXG5cdFx0XHRcdG5vbmNlOiB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ubm9uY2UgfHwgJycsXG5cdFx0XHRcdHBhZ2VfdXJsOiB1cmxcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5kYXRhLmlkKSB7XG5cdFx0XHRcdFx0Ly8gQmVnaW4gY29tbW9uIGxvYWRpbmcgKyBwb2xsaW5nIGZsb3cuXG5cdFx0XHRcdFx0YmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIHJlc3BvbnNlLmRhdGEuaWQsIHVybCk7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gQ2hlY2sgaWYgd2UndmUgcmVhY2hlZCB0aGUgbGltaXQgYW5kIGRpc2FibGUgYWxsIG90aGVyIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdFx0XHRcdFx0aWYgKHJlc3BvbnNlLmRhdGEuY2FuX2FkZF9wYWdlcyA9PT0gZmFsc2UgfHwgcmVzcG9uc2UuZGF0YS5yZW1haW5pbmdfdXJscyA9PT0gMCkge1xuXHRcdFx0XHRcdFx0ZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHQvLyBTaG93IGVycm9yIG1lc3NhZ2UuXG5cdFx0XHRcdFx0c2hvd01lc3NhZ2UoY29sdW1uLCByZXNwb25zZS5kYXRhPy5tZXNzYWdlIHx8ICdFcnJvciBhZGRpbmcgcGFnZScsICdlcnJvcicpO1xuXHRcdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKS50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRlcnJvcjogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fCAnQW4gZXJyb3Igb2NjdXJyZWQnLCAnZXJyb3InKTtcblx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpLnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBSZXRlc3QgYW4gZXhpc3RpbmcgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfcm9ja2V0X2luc2lnaHRzX3Jlc2V0X3BhZ2UnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRpZDogcm93SWRcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRcdGJlZ2luTG9hZGluZ0FuZFBvbGwoY29sdW1uLCByb3dJZCwgdXJsKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRzaG93TWVzc2FnZShjb2x1bW4sIHJlc3BvbnNlLmRhdGE/Lm1lc3NhZ2UgfHwgJ0Vycm9yIHJldGVzdGluZyBwYWdlJywgJ2Vycm9yJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRlcnJvcjogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHNob3dNZXNzYWdlKGNvbHVtbiwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lcnJvciB8fCAnQW4gZXJyb3Igb2NjdXJyZWQnLCAnZXJyb3InKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENvbW1vbiBoZWxwZXIgdG8gc2V0IGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCkge1xuXHRcdC8vIFVwZGF0ZSBjb2x1bW4gdG8gbG9hZGluZyBzdGF0ZSBhbmQgc3RhcnQgcG9sbGluZy5cblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpO1xuXHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0alF1ZXJ5LmFqYXgoe1xuXHRcdFx0dXJsOiBhamF4dXJsLFxuXHRcdFx0dHlwZTogJ0dFVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfZ2V0X3Jlc3VsdHMnLFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnLFxuXHRcdFx0XHRpZHM6IFtyb3dJZF1cblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuZGF0YSApIHtcblx0XHRcdFx0XHRjb25zdCByZXN1bHQgPSByZXNwb25zZS5kYXRhLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0XHRpZiAoIHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnICkge1xuXHRcdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nLlxuXHRcdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggYWN0aXZlUG9sbHNbcm93SWRdICk7XG5cdFx0XHRcdFx0XHRkZWxldGUgYWN0aXZlUG9sbHNbcm93SWRdO1xuXG5cdFx0XHRcdFx0XHQvLyBVcGRhdGUgdGhlIGNvbHVtbiB3aXRoIHJlc3VsdHMgKHJlbG9hZCByZW5kZXJlZCBIVE1MIGZyb20gc2VydmVyKS5cblx0XHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKCBjb2x1bW4sIHJlc3VsdCApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgcm93SWQpO1xuXG5cdFx0Ly8gQ3JlYXRlIGVsZW1lbnRzIHNhZmVseSB0byBwcmV2ZW50IFhTU1xuXHRcdGNvbnN0IGxvYWRpbmdEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1sb2FkaW5nJyk7XG5cdFx0Y29uc3QgaW1nID0galF1ZXJ5KCc8aW1nPicpLmFkZENsYXNzKCd3cHItbG9hZGluZy1pbWcnKS5hdHRyKHtcblx0XHRcdHNyYzogd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5sb2FkaW5nX2ltZyB8fCAnJyxcblx0XHRcdGFsdDogJ0xvYWRpbmcuLi4nXG5cdFx0fSk7XG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UnKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoaW1nKTtcblx0XHRjb2x1bW4uZW1wdHkoKS5hcHBlbmQobG9hZGluZ0RpdikuYXBwZW5kKG1lc3NhZ2VEaXYpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZSBjb2x1bW4gd2l0aCB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gcmVzdWx0IFRoZSB0ZXN0IHJlc3VsdCBkYXRhLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpIHtcblx0XHQvLyBSZWxvYWQgdGhlIGVudGlyZSByb3cgZnJvbSB0aGUgc2VydmVyIHRvIGdldCBwcm9wZXJseSByZW5kZXJlZCBIVE1MLlxuXHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblx0XHRcblx0XHRqUXVlcnkuYWpheCh7XG5cdFx0XHR1cmw6IGFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF9yb2NrZXRfaW5zaWdodHNfZ2V0X2NvbHVtbl9odG1sJyxcblx0XHRcdFx0bm9uY2U6IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5ub25jZSB8fCAnJyxcblx0XHRcdFx0dXJsOiB1cmxcblx0XHRcdH0sXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5kYXRhLmh0bWwpIHtcblx0XHRcdFx0XHRjb2x1bW4ucmVwbGFjZVdpdGgocmVzcG9uc2UuZGF0YS5odG1sKTtcblx0XHRcdFx0XHRcblx0XHRcdFx0XHQvLyBSZS1hdHRhY2ggbGlzdGVuZXJzIHRvIHRoZSBuZXcgY29udGVudC5cblx0XHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBhIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiAgVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gbWVzc2FnZSBUaGUgbWVzc2FnZSB0byBkaXNwbGF5LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdHlwZSAgICBUaGUgbWVzc2FnZSB0eXBlICgnZXJyb3InIG9yICdzdWNjZXNzJykuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TWVzc2FnZShjb2x1bW4sIG1lc3NhZ2UsIHR5cGUpIHtcblx0XHRjb25zdCBtZXNzYWdlRWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIGNvbnRlbnQgZmlyc3Rcblx0XHRtZXNzYWdlRWwuc3RvcCh0cnVlLCB0cnVlKS5lbXB0eSgpO1xuXHRcdGNvbnN0IHAgPSBqUXVlcnkoJzxwPicpLmFkZENsYXNzKCd3cHItcmktbWVzc2FnZS0nICsgdHlwZSkudGV4dChtZXNzYWdlKTtcblx0XHRtZXNzYWdlRWwuYXBwZW5kKHApLnNob3coKTtcblx0XHRcblx0XHQvLyBBdXRvLWhpZGUgYWZ0ZXIgNSBzZWNvbmRzLlxuXHRcdHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG5cdFx0XHRtZXNzYWdlRWwuZmFkZU91dCgpO1xuXHRcdH0sIDUwMDApO1xuXHR9XG5cblx0LyoqXG5cdCAqIERpc2FibGUgYWxsIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMgd2hlbiB0aGUgVVJMIGxpbWl0IGlzIHJlYWNoZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS10ZXN0LXBhZ2U6bm90KC53cHItcmktbm8tY3JlZGl0KScpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRidXR0b24uYWRkQ2xhc3MoJ3dwci1yaS1uby1jcmVkaXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXHRcdFx0XG5cdFx0XHQvLyBBZGQgdGhlIFwiWW91J3ZlIHJlYWNoZWQgeW91ciBsaW1pdFwiIG1lc3NhZ2UgaWYgbm90IGFscmVhZHkgcHJlc2VudC5cblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3QgY3JlZGl0TWVzc2FnZSA9IGNvbHVtbi5maW5kKCcud3ByLXJpLWNyZWRpdC1tZXNzYWdlJyk7XG5cdFx0XHRcblx0XHRcdGlmIChjcmVkaXRNZXNzYWdlLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0XHRjb25zdCBtZXNzYWdlRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktY3JlZGl0LW1lc3NhZ2UnKTtcblx0XHRcdFx0Y29uc3Qgc3Ryb25nVGV4dCA9IGpRdWVyeSgnPHN0cm9uZz4nKS50ZXh0KFxuXHRcdFx0XHRcdHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubGltaXRfcmVhY2hlZCB8fCBcIllvdSd2ZSByZWFjaGVkIHlvdXIgZnJlZSBsaW1pdC5cIlxuXHRcdFx0XHQpO1xuXHRcdFx0XHRjb25zdCB1cGdyYWRlVGV4dCA9IGRvY3VtZW50LmNyZWF0ZVRleHROb2RlKCcgJyArICh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnVwZ3JhZGVfdG9fY29udGludWUgfHwgJ1VwZ3JhZGUgdG8gY29udGludWUuJykpO1xuXHRcdFx0XHRcblx0XHRcdFx0bWVzc2FnZURpdi5hcHBlbmQoc3Ryb25nVGV4dCkuYXBwZW5kKHVwZ3JhZGVUZXh0KTtcblx0XHRcdFx0YnV0dG9uLmFmdGVyKG1lc3NhZ2VEaXYpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0Ly8gQXV0by1pbml0aWFsaXplIG9uIERPTSByZWFkeVxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGluaXQpO1xuXHR9IGVsc2Uge1xuXHRcdGluaXQoKTtcblx0fVxuXG5cdHJldHVybiB7XG5cdFx0aW5pdDogaW5pdFxuXHR9O1xufSkoKTtcbiJdfQ==
