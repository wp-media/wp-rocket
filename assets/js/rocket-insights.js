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
    // Disable button and show loading state immediately.
    button.prop('disabled', true);

    // Show loading spinner immediately before API call
    showLoadingState(column, null);

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
        // Update column with the row ID and start polling
        column.attr('data-rocket-insights-id', id);
        startPolling(id, url, column);

        // Check if we've reached the limit and disable all other "Test the page" buttons.
        if (canAdd === false || response?.data?.remaining_urls === 0) {
          disableAllTestPageButtons();
        }
        return;
      }

      // If backend says we cannot add pages or other errors, restore original state
      // Reload the column HTML from server to restore the button
      reloadColumnFromServer(column, url);
    }).catch(error => {
      // wp.apiFetch throws on WP_Error; reload column to restore button
      console.error(error);
      reloadColumnFromServer(column, url);
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
    // Show loading spinner immediately before API call
    showLoadingState(column, rowId);
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/' + rowId,
      method: 'PATCH'
    }).then(response => {
      if (response.success) {
        // Start polling for results
        startPolling(rowId, url, column);
      } else {
        // If not successful, reload the column to restore previous state
        reloadColumnFromServer(column, url);
      }
    }).catch(error => {
      console.error(error);
      // Reload the column to restore previous state
      reloadColumnFromServer(column, url);
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
   * @param {number} rowId  The database row ID (can be null when initially showing loading).
   */
  function showLoadingState(column, rowId) {
    if (rowId) {
      column.attr('data-rocket-insights-id', rowId);
    }

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
   * Reload column HTML from server.
   *
   * @param {jQuery} column The column element.
   * @param {string} url    The URL for the column.
   */
  function reloadColumnFromServer(column, url) {
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
    }).catch(error => {
      console.error('Failed to reload column:', error);
    });
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
    reloadColumnFromServer(column, url);
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFDOUIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUUvQyxNQUFNLFdBQVcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDLEtBQUssR0FBRztNQUU3RCxJQUFLLENBQUUsV0FBVyxFQUFHO1FBQ3BCLGdCQUFnQixDQUFFLE1BQU0sRUFBRSxNQUFPLENBQUM7UUFDbEM7TUFDRDtNQUVBLFVBQVUsQ0FBQyxHQUFHLEVBQUUsTUFBTSxF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QUFBLEVBQUc7SUFDcEMsTUFBTSxDQUFDLG1CQUFtQixDQUFDLENBQUMsSUFBSSxDQUFDLFlBQVc7TUFDM0MsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQztNQUMzQixNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsT0FBTyxDQUFDLGdCQUFnQixDQUFDOztNQUUvQztNQUNBLE1BQU0sQ0FBQyxJQUFJLENBQUMsb0JBQW9CLEVBQUUsR0FBRyxDQUFDO0lBQ3ZDLENBQUMsQ0FBQztFQUNIOztFQUVBO0VBQ0EsSUFBSSxRQUFRLENBQUMsVUFBVSxLQUFLLFNBQVMsRUFBRTtJQUN0QyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUUsSUFBSSxDQUFDO0VBQ3BELENBQUMsTUFBTTtJQUNOLElBQUksQ0FBQyxDQUFDO0VBQ1A7RUFFQSxPQUFPO0lBQ04sSUFBSSxFQUFFO0VBQ1AsQ0FBQztBQUNGLENBQUMsQ0FBRSxDQUFDIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiLyoqXG4gKiBSb2NrZXQgSW5zaWdodHMgZnVuY3Rpb25hbGl0eSBmb3IgcG9zdCBsaXN0aW5nIHBhZ2VzXG4gKiBUaGlzIHNjcmlwdCBoYW5kbGVzIHBlcmZvcm1hbmNlIHNjb3JlIGRpc3BsYXkgYW5kIHVwZGF0ZXMgaW4gYWRtaW4gcG9zdCBsaXN0aW5nIHBhZ2VzXG4gKlxuICogQHNpbmNlIDMuMjAuMVxuICovXG5cbi8vIEV4cG9ydCBmb3IgdXNlIHdpdGggYnJvd3NlcmlmeS9iYWJlbGlmeSBpbiBndWxwXG5tb2R1bGUuZXhwb3J0cyA9IChmdW5jdGlvbigpIHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdC8qKlxuXHQgKiBQb2xsaW5nIGludGVydmFsIGZvciBjaGVja2luZyBvbmdvaW5nIHRlc3RzIChpbiBtaWxsaXNlY29uZHMpLlxuXHQgKi9cblx0Y29uc3QgUE9MTElOR19JTlRFUlZBTCA9IDUwMDA7IC8vIDUgc2Vjb25kc1xuXG5cdC8qKlxuXHQgKiBBY3RpdmUgcG9sbGluZyBpbnRlcnZhbHMgYnkgcG9zdCBJRC5cblx0ICovXG5cdGNvbnN0IGFjdGl2ZVBvbGxzID0ge307XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemUgUm9ja2V0IEluc2lnaHRzIG9uIHBvc3QgbGlzdGluZyBwYWdlc1xuXHQgKi9cblx0ZnVuY3Rpb24gaW5pdCgpIHtcblx0XHQvLyBBdHRhY2ggZXZlbnQgbGlzdGVuZXJzLlxuXHRcdGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCk7XG5cdFx0YXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCk7XG5cblx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciBhbnkgcm93cyB0aGF0IGFyZSBhbHJlYWR5IHJ1bm5pbmcuXG5cdFx0c3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKSB7XG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS10ZXN0LXBhZ2UnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBidXR0b24uZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblxuXHRcdFx0Y29uc3QgY2FuQWRkUGFnZXMgPSBjb2x1bW4uYXR0cignZGF0YS1jYW4tYWRkLXBhZ2VzJykgPT09ICcxJztcblxuXHRcdFx0aWYgKCAhIGNhbkFkZFBhZ2VzICkge1xuXHRcdFx0XHRzaG93TGltaXRNZXNzYWdlKCBjb2x1bW4sIGJ1dHRvbiApO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBTdXBwb3J0IGJvdGggYnV0dG9uIGFuZCBsaW5rIHN0eWxlcyB3aXRoIG9uZSBoYW5kbGVyLlxuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmV0ZXN0Om5vdCgud3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQpLCAud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFJldGVzdCBzaG91bGQgb25seSBwcm9jZWVkIHdoZW4gdGhlIHVzZXIgaGFzIGNyZWRpdCBmb3IgdGhlIHRlc3QuXG5cdFx0XHRjb25zdCBoYXNDcmVkaXQgPSBjb2x1bW4uYXR0cignZGF0YS1oYXMtY3JlZGl0JykgPT09ICcxJztcblxuXHRcdFx0aWYgKCAhIGhhc0NyZWRpdCApIHtcblx0XHRcdFx0c2hvd0xpbWl0TWVzc2FnZSggY29sdW1uLCBlbCApO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZSBpbW1lZGlhdGVseS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdC8vIFNob3cgbG9hZGluZyBzcGlubmVyIGltbWVkaWF0ZWx5IGJlZm9yZSBBUEkgY2FsbFxuXHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCBudWxsKTtcblxuXHRcdC8vIFVzZSBSRVNUIChIRUFEKSBidXQga2VlcCBkZXZlbG9wJ3Mgcm9idXN0IGhhbmRsaW5nLlxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YTogeyBwYWdlX3VybDogdXJsIH0sXG5cdFx0fSkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGNvbnN0IHN1Y2Nlc3MgICA9IHJlc3BvbnNlPy5zdWNjZXNzID09PSB0cnVlO1xuXHRcdFx0Y29uc3QgaWQgICAgICAgID0gcmVzcG9uc2U/LmlkID8/IHJlc3BvbnNlPy5kYXRhPy5pZCA/PyBudWxsO1xuXHRcdFx0Y29uc3QgY2FuQWRkICAgID0gKHJlc3BvbnNlPy5jYW5fYWRkX3BhZ2VzID8/IHJlc3BvbnNlPy5kYXRhPy5jYW5fYWRkX3BhZ2VzKTtcblx0XHRcdGNvbnN0IG1lc3NhZ2UgICA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXG5cdFx0XHRpZiAoc3VjY2VzcyAmJiBpZCkge1xuXHRcdFx0XHQvLyBVcGRhdGUgY29sdW1uIHdpdGggdGhlIHJvdyBJRCBhbmQgc3RhcnQgcG9sbGluZ1xuXHRcdFx0XHRjb2x1bW4uYXR0cignZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQnLCBpZCk7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhpZCwgdXJsLCBjb2x1bW4pO1xuXG5cdFx0XHRcdC8vIENoZWNrIGlmIHdlJ3ZlIHJlYWNoZWQgdGhlIGxpbWl0IGFuZCBkaXNhYmxlIGFsbCBvdGhlciBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHRcdFx0XHRpZiAoY2FuQWRkID09PSBmYWxzZSB8fCByZXNwb25zZT8uZGF0YT8ucmVtYWluaW5nX3VybHMgPT09IDApIHtcblx0XHRcdFx0XHRkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCk7XG5cdFx0XHRcdH1cblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBJZiBiYWNrZW5kIHNheXMgd2UgY2Fubm90IGFkZCBwYWdlcyBvciBvdGhlciBlcnJvcnMsIHJlc3RvcmUgb3JpZ2luYWwgc3RhdGVcblx0XHRcdC8vIFJlbG9hZCB0aGUgY29sdW1uIEhUTUwgZnJvbSBzZXJ2ZXIgdG8gcmVzdG9yZSB0aGUgYnV0dG9uXG5cdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHR9KS5jYXRjaCgoZXJyb3IpID0+IHtcblx0XHRcdC8vIHdwLmFwaUZldGNoIHRocm93cyBvbiBXUF9FcnJvcjsgcmVsb2FkIGNvbHVtbiB0byByZXN0b3JlIGJ1dHRvblxuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBSZXRlc3QgYW4gZXhpc3RpbmcgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gU2hvdyBsb2FkaW5nIHNwaW5uZXIgaW1tZWRpYXRlbHkgYmVmb3JlIEFQSSBjYWxsXG5cdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyByb3dJZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIHJlc3VsdHNcblx0XHRcdFx0c3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBJZiBub3Qgc3VjY2Vzc2Z1bCwgcmVsb2FkIHRoZSBjb2x1bW4gdG8gcmVzdG9yZSBwcmV2aW91cyBzdGF0ZVxuXHRcdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHRcdH1cblx0XHR9ICkuY2F0Y2goICggZXJyb3IgKSA9PiB7XG5cdFx0XHRjb25zb2xlLmVycm9yKGVycm9yKTtcblx0XHRcdC8vIFJlbG9hZCB0aGUgY29sdW1uIHRvIHJlc3RvcmUgcHJldmlvdXMgc3RhdGVcblx0XHRcdHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgdGhlIHBlci1yb3cgbGltaXQgbWVzc2FnZSAob25seSBpbiB0aGUgY2xpY2tlZCByb3cpLlxuXHQgKiBEaXNhYmxlcyB0aGUgY2xpY2tlZCBlbGVtZW50IG1vbWVudGFyaWx5IHdoaWxlIHNob3dpbmcgdGhlIG1lc3NhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY2xpY2tlZEVsIFRoZSBlbGVtZW50IHRoYXQgdHJpZ2dlcmVkIHRoZSBhY3Rpb24uXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TGltaXRNZXNzYWdlKGNvbHVtbiwgY2xpY2tlZEVsKSB7XG5cdFx0Y29uc3QgbWVzc2FnZUh0bWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1saW1pdC1odG1sJykuaHRtbCgpIHx8IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubGltaXRfcmVhY2hlZCB8fCAnJztcblxuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0bWVzc2FnZURpdi5odG1sKG1lc3NhZ2VIdG1sKS5zaG93KCk7XG5cblx0XHQvLyBEaXNhYmxlIG9ubHkgdGhlIGNsaWNrZWQgZWxlbWVudCBicmllZmx5IHRvIHByZXZlbnQgc3BhbSBjbGlja3MsIHRoZW4gcmUtZW5hYmxlLlxuXHRcdGlmIChjbGlja2VkRWwgJiYgY2xpY2tlZEVsLnByb3ApIHtcblx0XHRcdGNsaWNrZWRFbC5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXHRcdFx0c2V0VGltZW91dChmdW5jdGlvbigpIHtcblx0XHRcdFx0Y2xpY2tlZEVsLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpO1xuXHRcdFx0fSwgMzAwMCk7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IFtyb3dJZF0gfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KCByZXNwb25zZS5yZXN1bHRzICkgKSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdCA9IHJlc3BvbnNlLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0aWYgKCByZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJyApIHtcblx0XHRcdFx0XHQvLyBTdG9wIHBvbGxpbmcuXG5cdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggYWN0aXZlUG9sbHNbcm93SWRdICk7XG5cdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSB0aGUgY29sdW1uIHdpdGggcmVzdWx0cyAocmVsb2FkIHJlbmRlcmVkIEhUTUwgZnJvbSBzZXJ2ZXIpLlxuXHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKCBjb2x1bW4sIHJlc3VsdCApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElEIChjYW4gYmUgbnVsbCB3aGVuIGluaXRpYWxseSBzaG93aW5nIGxvYWRpbmcpLlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0aWYgKHJvd0lkKSB7XG5cdFx0XHRjb2x1bW4uYXR0cignZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQnLCByb3dJZCk7XG5cdFx0fVxuXG5cdFx0Ly8gQ3JlYXRlIGVsZW1lbnRzIHNhZmVseSB0byBwcmV2ZW50IFhTU1xuXHRcdGNvbnN0IGxvYWRpbmdEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1sb2FkaW5nJyk7XG5cdFx0Y29uc3QgaW1nID0galF1ZXJ5KCc8aW1nPicpLmFkZENsYXNzKCd3cHItbG9hZGluZy1pbWcnKS5hdHRyKHtcblx0XHRcdHNyYzogd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5sb2FkaW5nX2ltZyB8fCAnJyxcblx0XHRcdGFsdDogJ0xvYWRpbmcuLi4nXG5cdFx0fSk7XG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UnKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoaW1nKTtcblx0XHRjb2x1bW4uZW1wdHkoKS5hcHBlbmQobG9hZGluZ0RpdikuYXBwZW5kKG1lc3NhZ2VEaXYpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFJlbG9hZCBjb2x1bW4gSFRNTCBmcm9tIHNlcnZlci5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBmb3IgdGhlIGNvbHVtbi5cblx0ICovXG5cdGZ1bmN0aW9uIHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpIHtcblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMnLCB7IHVybDogdXJsIH0gKSxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5odG1sKSB7XG5cdFx0XHRcdGNvbHVtbi5yZXBsYWNlV2l0aChyZXNwb25zZS5odG1sKTtcblxuXHRcdFx0XHQvLyBSZS1hdHRhY2ggbGlzdGVuZXJzIHRvIHRoZSBuZXcgY29udGVudC5cblx0XHRcdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRcdFx0YXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCk7XG5cdFx0XHR9XG5cdFx0fSApLmNhdGNoKCAoIGVycm9yICkgPT4ge1xuXHRcdFx0Y29uc29sZS5lcnJvcignRmFpbGVkIHRvIHJlbG9hZCBjb2x1bW46JywgZXJyb3IpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY29sdW1uIHdpdGggdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtPYmplY3R9IHJlc3VsdCBUaGUgdGVzdCByZXN1bHQgZGF0YS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KSB7XG5cdFx0Ly8gUmVsb2FkIHRoZSBlbnRpcmUgcm93IGZyb20gdGhlIHNlcnZlciB0byBnZXQgcHJvcGVybHkgcmVuZGVyZWQgSFRNTC5cblx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cdFx0cmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCk7XG5cdH1cblxuXHQvKipcblx0ICogTWFyayBhbGwgcmVtYWluaW5nIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMgYXMgaGF2aW5nIHJlYWNoZWQgdGhlIGxpbWl0LlxuXHQgKiBVcGRhdGVzIGRhdGEgYXR0cmlidXRlcyBzbyBmdXR1cmUgY2xpY2tzIHdpbGwgc2hvdyB0aGUgbGltaXQgbWVzc2FnZSBwZXItcm93LlxuXHQgKiBEb2VzIE5PVCBkaXNwbGF5IGFueSBtZXNzYWdlIGltbWVkaWF0ZWx5IG9uIGFsbCByb3dzLlxuXHQgKi9cblx0ZnVuY3Rpb24gZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktdGVzdC1wYWdlJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0XG5cdFx0XHQvLyBVcGRhdGUgdGhlIGRhdGEgYXR0cmlidXRlIHNvIGZ1dHVyZSBjbGlja3Mgd2lsbCB0cmlnZ2VyIHRoZSBsaW1pdCBtZXNzYWdlLlxuXHRcdFx0Y29sdW1uLmF0dHIoJ2RhdGEtY2FuLWFkZC1wYWdlcycsICcwJyk7XG5cdFx0fSk7XG5cdH1cblxuXHQvLyBBdXRvLWluaXRpYWxpemUgb24gRE9NIHJlYWR5XG5cdGlmIChkb2N1bWVudC5yZWFkeVN0YXRlID09PSAnbG9hZGluZycpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgaW5pdCk7XG5cdH0gZWxzZSB7XG5cdFx0aW5pdCgpO1xuXHR9XG5cblx0cmV0dXJuIHtcblx0XHRpbml0OiBpbml0XG5cdH07XG59KSgpO1xuIl19
