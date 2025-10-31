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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFDOUIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUUvQyxVQUFVLENBQUMsR0FBRyxFQUFFLE1BQU0sRUFBRSxNQUFNLENBQUM7SUFDaEMsQ0FBQyxDQUFDO0VBQ0g7O0VBRUE7QUFDRDtBQUNBO0VBQ0MsU0FBUyxxQkFBcUIsQ0FBQSxFQUFHO0lBQ2hDO0lBQ0EsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUVB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TUFDeEI7SUFDRCxDQUFFLENBQUM7RUFDSjs7RUFFQTtFQUNBLElBQUksUUFBUSxDQUFDLFVBQVUsS0FBSyxTQUFTLEVBQUU7SUFDdEMsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLElBQUksQ0FBQztFQUNwRCxDQUFDLE1BQU07SUFDTixJQUFJLENBQUMsQ0FBQztFQUNQO0VBRUEsT0FBTztJQUNOLElBQUksRUFBRTtFQUNQLENBQUM7QUFDRixDQUFDLENBQUUsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHQvKipcblx0ICogUG9sbGluZyBpbnRlcnZhbCBmb3IgY2hlY2tpbmcgb25nb2luZyB0ZXN0cyAoaW4gbWlsbGlzZWNvbmRzKS5cblx0ICovXG5cdGNvbnN0IFBPTExJTkdfSU5URVJWQUwgPSA1MDAwOyAvLyA1IHNlY29uZHNcblxuXHQvKipcblx0ICogQWN0aXZlIHBvbGxpbmcgaW50ZXJ2YWxzIGJ5IHBvc3QgSUQuXG5cdCAqL1xuXHRjb25zdCBhY3RpdmVQb2xscyA9IHt9O1xuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplIFJvY2tldCBJbnNpZ2h0cyBvbiBwb3N0IGxpc3RpbmcgcGFnZXNcblx0ICovXG5cdGZ1bmN0aW9uIGluaXQoKSB7XG5cdFx0Ly8gQXR0YWNoIGV2ZW50IGxpc3RlbmVycy5cblx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXG5cdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgYW55IHJvd3MgdGhhdCBhcmUgYWxyZWFkeSBydW5uaW5nLlxuXHRcdHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdGVzdC1wYWdlJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgdXJsID0gYnV0dG9uLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBTdXBwb3J0IGJvdGggYnV0dG9uIGFuZCBsaW5rIHN0eWxlcyB3aXRoIG9uZSBoYW5kbGVyLlxuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmV0ZXN0Om5vdCgud3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQpLCAud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdC8vIEhpZGUgYW55IHByZXZpb3VzIG1lc3NhZ2VzXG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGNvbHVtbi5maW5kKCcud3ByLXJpLW1lc3NhZ2UnKTtcblx0XHRtZXNzYWdlRGl2LmhpZGUoKS5yZW1vdmVDbGFzcygnd3ByLXJpLWVycm9yJykuZW1wdHkoKTtcblxuXHRcdC8vIFVzZSBSRVNUIChIRUFEKSBidXQga2VlcCBkZXZlbG9wJ3Mgcm9idXN0IGhhbmRsaW5nLlxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YTogeyBwYWdlX3VybDogdXJsIH0sXG5cdFx0fSkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGNvbnN0IHN1Y2Nlc3MgICA9IHJlc3BvbnNlPy5zdWNjZXNzID09PSB0cnVlO1xuXHRcdFx0Y29uc3QgaWQgICAgICAgID0gcmVzcG9uc2U/LmlkID8/IHJlc3BvbnNlPy5kYXRhPy5pZCA/PyBudWxsO1xuXHRcdFx0Y29uc3QgY2FuQWRkICAgID0gKHJlc3BvbnNlPy5jYW5fYWRkX3BhZ2VzID8/IHJlc3BvbnNlPy5kYXRhPy5jYW5fYWRkX3BhZ2VzKTtcblx0XHRcdGNvbnN0IG1lc3NhZ2UgICA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXG5cdFx0XHRpZiAoc3VjY2VzcyAmJiBpZCkge1xuXHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0YmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIGlkLCB1cmwpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIGJhY2tlbmQgc2F5cyB3ZSBjYW5ub3QgYWRkIHBhZ2VzLCBzaG93IGVycm9yIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0XHRcdGlmIChjYW5BZGQgPT09IGZhbHNlKSB7XG5cdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKVxuXHRcdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cblx0XHRcdFx0Ly8gRGlzcGxheSBlcnJvciBtZXNzYWdlIChzZXJ2ZXIgcmVzcG9uc2VzIGFyZSBwcmUtZXNjYXBlZCBhbmQgbWF5IGNvbnRhaW4gaW50ZW50aW9uYWwgSFRNTClcblx0XHRcdFx0aWYgKG1lc3NhZ2UpIHtcblx0XHRcdFx0XHRtZXNzYWdlRGl2LmFkZENsYXNzKCd3cHItcmktZXJyb3InKS5odG1sKG1lc3NhZ2UpLnNob3coKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIE90aGVyIGVycm9yc1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cblx0XHRcdC8vIERpc3BsYXkgZXJyb3IgbWVzc2FnZSBpZiBhdmFpbGFibGUgKHNlcnZlciByZXNwb25zZXMgYXJlIHByZS1lc2NhcGVkIGFuZCBtYXkgY29udGFpbiBpbnRlbnRpb25hbCBIVE1MKVxuXHRcdFx0aWYgKG1lc3NhZ2UpIHtcblx0XHRcdFx0bWVzc2FnZURpdi5hZGRDbGFzcygnd3ByLXJpLWVycm9yJykuaHRtbChtZXNzYWdlKS5zaG93KCk7XG5cdFx0XHR9XG5cdFx0fSkuY2F0Y2goKGVycm9yKSA9PiB7XG5cdFx0XHQvLyB3cC5hcGlGZXRjaCB0aHJvd3Mgb24gV1BfRXJyb3I7IHRyeSB0byBzdXJmYWNlIGEgaGVscGZ1bCBtZXNzYWdlLlxuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSlcblx0XHRcdFx0LnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblxuXHRcdFx0Ly8gRGlzcGxheSBlcnJvciBtZXNzYWdlIC0gdXNlIHRleHQoKSBmb3IgdW50cnVzdGVkIGVycm9yIG1lc3NhZ2VzXG5cdFx0XHRjb25zdCBlcnJvck1lc3NhZ2UgPSBlcnJvcj8ubWVzc2FnZSB8fCAnQW4gZXJyb3Igb2NjdXJyZWQuIFBsZWFzZSB0cnkgYWdhaW4uJztcblx0XHRcdG1lc3NhZ2VEaXYuYWRkQ2xhc3MoJ3dwci1yaS1lcnJvcicpLnRleHQoZXJyb3JNZXNzYWdlKS5zaG93KCk7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdC8vIEhpZGUgYW55IHByZXZpb3VzIG1lc3NhZ2VzXG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGNvbHVtbi5maW5kKCcud3ByLXJpLW1lc3NhZ2UnKTtcblx0XHRtZXNzYWdlRGl2LmhpZGUoKS5yZW1vdmVDbGFzcygnd3ByLXJpLWVycm9yJykuZW1wdHkoKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyByb3dJZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdC8vIEJlZ2luIGNvbW1vbiBsb2FkaW5nICsgcG9sbGluZyBmbG93LlxuXHRcdFx0XHRiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBEaXNwbGF5IGVycm9yIG1lc3NhZ2UgaWYgYXZhaWxhYmxlIChzZXJ2ZXIgcmVzcG9uc2VzIGFyZSBwcmUtZXNjYXBlZCBhbmQgbWF5IGNvbnRhaW4gaW50ZW50aW9uYWwgSFRNTClcblx0XHRcdFx0Y29uc3QgbWVzc2FnZSA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXHRcdFx0XHRpZiAobWVzc2FnZSkge1xuXHRcdFx0XHRcdG1lc3NhZ2VEaXYuYWRkQ2xhc3MoJ3dwci1yaS1lcnJvcicpLmh0bWwobWVzc2FnZSkuc2hvdygpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSApLmNhdGNoKCAoIGVycm9yICkgPT4ge1xuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cblx0XHRcdC8vIERpc3BsYXkgZXJyb3IgbWVzc2FnZSAtIHVzZSB0ZXh0KCkgZm9yIHVudHJ1c3RlZCBlcnJvciBtZXNzYWdlc1xuXHRcdFx0Y29uc3QgZXJyb3JNZXNzYWdlID0gZXJyb3I/Lm1lc3NhZ2UgfHwgJ0FuIGVycm9yIG9jY3VycmVkLiBQbGVhc2UgdHJ5IGFnYWluLic7XG5cdFx0XHRtZXNzYWdlRGl2LmFkZENsYXNzKCd3cHItcmktZXJyb3InKS50ZXh0KGVycm9yTWVzc2FnZSkuc2hvdygpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENvbW1vbiBoZWxwZXIgdG8gc2V0IGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCkge1xuXHRcdC8vIFVwZGF0ZSBjb2x1bW4gdG8gbG9hZGluZyBzdGF0ZSBhbmQgc3RhcnQgcG9sbGluZy5cblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpO1xuXHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IFtyb3dJZF0gfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KCByZXNwb25zZS5yZXN1bHRzICkgKSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdCA9IHJlc3BvbnNlLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0aWYgKCByZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJyApIHtcblx0XHRcdFx0XHQvLyBTdG9wIHBvbGxpbmcuXG5cdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggYWN0aXZlUG9sbHNbcm93SWRdICk7XG5cdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSB0aGUgY29sdW1uIHdpdGggcmVzdWx0cyAocmVsb2FkIHJlbmRlcmVkIEhUTUwgZnJvbSBzZXJ2ZXIpLlxuXHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKCBjb2x1bW4sIHJlc3VsdCApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgcm93SWQpO1xuXG5cdFx0Ly8gQ3JlYXRlIGVsZW1lbnRzIHNhZmVseSB0byBwcmV2ZW50IFhTU1xuXHRcdGNvbnN0IGxvYWRpbmdEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1sb2FkaW5nJyk7XG5cdFx0Y29uc3QgaW1nID0galF1ZXJ5KCc8aW1nPicpLmFkZENsYXNzKCd3cHItbG9hZGluZy1pbWcnKS5hdHRyKHtcblx0XHRcdHNyYzogd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5sb2FkaW5nX2ltZyB8fCAnJyxcblx0XHRcdGFsdDogJ0xvYWRpbmcuLi4nXG5cdFx0fSk7XG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UnKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoaW1nKTtcblx0XHRjb2x1bW4uZW1wdHkoKS5hcHBlbmQobG9hZGluZ0RpdikuYXBwZW5kKG1lc3NhZ2VEaXYpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZSBjb2x1bW4gd2l0aCB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gcmVzdWx0IFRoZSB0ZXN0IHJlc3VsdCBkYXRhLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpIHtcblx0XHQvLyBSZWxvYWQgdGhlIGVudGlyZSByb3cgZnJvbSB0aGUgc2VydmVyIHRvIGdldCBwcm9wZXJseSByZW5kZXJlZCBIVE1MLlxuXHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogd2luZG93LndwLnVybC5hZGRRdWVyeUFyZ3MoICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcycsIHsgdXJsOiB1cmwgfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmh0bWwpIHtcblx0XHRcdFx0Y29sdW1uLnJlcGxhY2VXaXRoKHJlc3BvbnNlLmh0bWwpO1xuXG5cdFx0XHRcdC8vIFJlLWF0dGFjaCBsaXN0ZW5lcnMgdG8gdGhlIG5ldyBjb250ZW50LlxuXHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHQvLyBBdXRvLWluaXRpYWxpemUgb24gRE9NIHJlYWR5XG5cdGlmIChkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnbG9hZGluZycpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgaW5pdCk7XG5cdH0gZWxzZSB7XG5cdFx0aW5pdCgpO1xuXHR9XG5cblx0cmV0dXJuIHtcblx0XHRpbml0OiBpbml0XG5cdH07XG59KSgpO1xuIl19
