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
    button.prop('disabled', true);

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
        button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
        return;
      }

      // Other errors
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');
    }).catch(error => {
      // wp.apiFetch throws on WP_Error; try to surface a helpful message.
      console.error(error);
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
        // Update credit state from response
        updateCreditState(response.has_credit, response.can_add_pages);

        // Begin common loading + polling flow.
        beginLoadingAndPoll(column, rowId, url);
      }
    }).catch(error => {
      console.error(error);
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

          // Update credit state from response
          updateCreditState(response.has_credit, response.can_add_pages);

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
    jQuery('.wpr-ri-retest-link').each(function () {
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
          const newButton = jQuery('<button>').attr('type', 'button').attr('class', button.attr('class')).attr('data-url', button.data('url')).removeClass('wpr-ri-disabled').html(button.html());
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
    jQuery('.wpr-ri-test-page').each(function () {
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
}();

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxJQUFJLFNBQVMsR0FBRyxJQUFJOztFQUVwQjtBQUNEO0FBQ0E7RUFDQyxJQUFJLFdBQVcsR0FBRyxJQUFJOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcs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YXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiLyoqXG4gKiBSb2NrZXQgSW5zaWdodHMgZnVuY3Rpb25hbGl0eSBmb3IgcG9zdCBsaXN0aW5nIHBhZ2VzXG4gKiBUaGlzIHNjcmlwdCBoYW5kbGVzIHBlcmZvcm1hbmNlIHNjb3JlIGRpc3BsYXkgYW5kIHVwZGF0ZXMgaW4gYWRtaW4gcG9zdCBsaXN0aW5nIHBhZ2VzXG4gKlxuICogQHNpbmNlIDMuMjAuMVxuICovXG5cbi8vIEV4cG9ydCBmb3IgdXNlIHdpdGggYnJvd3NlcmlmeS9iYWJlbGlmeSBpbiBndWxwXG5tb2R1bGUuZXhwb3J0cyA9IChmdW5jdGlvbigpIHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdC8qKlxuXHQgKiBQb2xsaW5nIGludGVydmFsIGZvciBjaGVja2luZyBvbmdvaW5nIHRlc3RzIChpbiBtaWxsaXNlY29uZHMpLlxuXHQgKi9cblx0Y29uc3QgUE9MTElOR19JTlRFUlZBTCA9IDUwMDA7IC8vIDUgc2Vjb25kc1xuXG5cdC8qKlxuXHQgKiBBY3RpdmUgcG9sbGluZyBpbnRlcnZhbHMgYnkgcG9zdCBJRC5cblx0ICovXG5cdGNvbnN0IGFjdGl2ZVBvbGxzID0ge307XG5cblx0LyoqXG5cdCAqIFRyYWNrIGNyZWRpdCBhdmFpbGFiaWxpdHkgc3RhdGUuXG5cdCAqL1xuXHRsZXQgaGFzQ3JlZGl0ID0gdHJ1ZTtcblxuXHQvKipcblx0ICogVHJhY2sgd2hldGhlciBhZGRpbmcgcGFnZXMgaXMgYWxsb3dlZC5cblx0ICovXG5cdGxldCBjYW5BZGRQYWdlcyA9IHRydWU7XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemUgUm9ja2V0IEluc2lnaHRzIG9uIHBvc3QgbGlzdGluZyBwYWdlc1xuXHQgKi9cblx0ZnVuY3Rpb24gaW5pdCgpIHtcblx0XHQvLyBBdHRhY2ggZXZlbnQgbGlzdGVuZXJzLlxuXHRcdGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCk7XG5cdFx0YXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCk7XG5cblx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciBhbnkgcm93cyB0aGF0IGFyZSBhbHJlYWR5IHJ1bm5pbmcuXG5cdFx0c3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKSB7XG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS10ZXN0LXBhZ2UnLCBmdW5jdGlvbihlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cblx0XHRcdC8vIERvbid0IGFsbG93IGNsaWNrIGlmIG5vIGNyZWRpdFxuXHRcdFx0aWYgKGJ1dHRvbi5oYXNDbGFzcygnd3ByLXJpLW5vLWNyZWRpdCcpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgdXJsID0gYnV0dG9uLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBTdXBwb3J0IGJvdGggYnV0dG9uIGFuZCBsaW5rIHN0eWxlcyB3aXRoIG9uZSBoYW5kbGVyLlxuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmV0ZXN0Om5vdCgud3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQpLCAud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdC8vIFVzZSBSRVNUIChIRUFEKSBidXQga2VlcCBkZXZlbG9wJ3Mgcm9idXN0IGhhbmRsaW5nLlxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YTogeyBwYWdlX3VybDogdXJsIH0sXG5cdFx0fSkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGNvbnN0IHN1Y2Nlc3MgICA9IHJlc3BvbnNlPy5zdWNjZXNzID09PSB0cnVlO1xuXHRcdFx0Y29uc3QgaWQgICAgICAgID0gcmVzcG9uc2U/LmlkID8/IHJlc3BvbnNlPy5kYXRhPy5pZCA/PyBudWxsO1xuXHRcdFx0Y29uc3QgY2FuQWRkICAgID0gKHJlc3BvbnNlPy5jYW5fYWRkX3BhZ2VzID8/IHJlc3BvbnNlPy5kYXRhPy5jYW5fYWRkX3BhZ2VzKTtcblx0XHRcdGNvbnN0IG1lc3NhZ2UgICA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXHRcdFx0Y29uc3QgaGFzQ3JlZGl0ID0gcmVzcG9uc2U/Lmhhc19jcmVkaXQgPz8gcmVzcG9uc2U/LmRhdGE/Lmhhc19jcmVkaXQ7XG5cblx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdGUgZnJvbSByZXNwb25zZVxuXHRcdFx0dXBkYXRlQ3JlZGl0U3RhdGUoaGFzQ3JlZGl0LCBjYW5BZGQpO1xuXG5cdFx0XHRpZiAoc3VjY2VzcyAmJiBpZCkge1xuXHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0YmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIGlkLCB1cmwpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIGJhY2tlbmQgc2F5cyB3ZSBjYW5ub3QgYWRkIHBhZ2VzLCByZS1lbmFibGUgYW5kIHJlc2V0IGxhYmVsIHdpdGhvdXQgZXJyb3IgYmFubmVyLlxuXHRcdFx0aWYgKGNhbkFkZCA9PT0gZmFsc2UpIHtcblx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdFx0LnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBPdGhlciBlcnJvcnNcblx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKVxuXHRcdFx0XHQudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdH0pLmNhdGNoKChlcnJvcikgPT4ge1xuXHRcdFx0Ly8gd3AuYXBpRmV0Y2ggdGhyb3dzIG9uIFdQX0Vycm9yOyB0cnkgdG8gc3VyZmFjZSBhIGhlbHBmdWwgbWVzc2FnZS5cblx0XHRcdGNvbnNvbGUuZXJyb3IoZXJyb3IpO1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyByb3dJZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdGUgZnJvbSByZXNwb25zZVxuXHRcdFx0XHR1cGRhdGVDcmVkaXRTdGF0ZShyZXNwb25zZS5oYXNfY3JlZGl0LCByZXNwb25zZS5jYW5fYWRkX3BhZ2VzKTtcblxuXHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0YmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIHJvd0lkLCB1cmwpO1xuXHRcdFx0fVxuXHRcdH0gKS5jYXRjaCggKCBlcnJvciApID0+IHtcblx0XHRcdGNvbnNvbGUuZXJyb3IoZXJyb3IpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENvbW1vbiBoZWxwZXIgdG8gc2V0IGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBiZWdpbkxvYWRpbmdBbmRQb2xsKGNvbHVtbiwgcm93SWQsIHVybCkge1xuXHRcdC8vIFVwZGF0ZSBjb2x1bW4gdG8gbG9hZGluZyBzdGF0ZSBhbmQgc3RhcnQgcG9sbGluZy5cblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpO1xuXHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IFtyb3dJZF0gfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KCByZXNwb25zZS5yZXN1bHRzICkgKSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdCA9IHJlc3BvbnNlLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0aWYgKCByZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJyApIHtcblx0XHRcdFx0XHQvLyBTdG9wIHBvbGxpbmcuXG5cdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggYWN0aXZlUG9sbHNbcm93SWRdICk7XG5cdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdGUgZnJvbSByZXNwb25zZVxuXHRcdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQsIHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMpO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBjb2x1bW4gd2l0aCByZXN1bHRzIChyZWxvYWQgcmVuZGVyZWQgSFRNTCBmcm9tIHNlcnZlcikuXG5cdFx0XHRcdFx0dXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoIGNvbHVtbiwgcmVzdWx0ICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBsb2FkaW5nIHN0YXRlIGluIHRoZSBjb2x1bW4uXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpIHtcblx0XHRjb2x1bW4uYXR0cignZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQnLCByb3dJZCk7XG5cblx0XHQvLyBDcmVhdGUgZWxlbWVudHMgc2FmZWx5IHRvIHByZXZlbnQgWFNTXG5cdFx0Y29uc3QgbG9hZGluZ0RpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLWxvYWRpbmcnKTtcblx0XHRjb25zdCBpbWcgPSBqUXVlcnkoJzxpbWc+JykuYWRkQ2xhc3MoJ3dwci1sb2FkaW5nLWltZycpLmF0dHIoe1xuXHRcdFx0c3JjOiB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmxvYWRpbmdfaW1nIHx8ICcnLFxuXHRcdFx0YWx0OiAnTG9hZGluZy4uLidcblx0XHR9KTtcblx0XHRjb25zdCBtZXNzYWdlRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbWVzc2FnZScpLmNzcygnZGlzcGxheScsICdub25lJyk7XG5cblx0XHRsb2FkaW5nRGl2LmFwcGVuZChpbWcpO1xuXHRcdGNvbHVtbi5lbXB0eSgpLmFwcGVuZChsb2FkaW5nRGl2KS5hcHBlbmQobWVzc2FnZURpdik7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlIGNvbHVtbiB3aXRoIHRlc3QgcmVzdWx0cy5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSByZXN1bHQgVGhlIHRlc3QgcmVzdWx0IGRhdGEuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyhjb2x1bW4sIHJlc3VsdCkge1xuXHRcdC8vIFJlbG9hZCB0aGUgZW50aXJlIHJvdyBmcm9tIHRoZSBzZXJ2ZXIgdG8gZ2V0IHByb3Blcmx5IHJlbmRlcmVkIEhUTUwuXG5cdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzJywgeyB1cmw6IHVybCB9ICksXG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuaHRtbCkge1xuXHRcdFx0XHRjb2x1bW4ucmVwbGFjZVdpdGgocmVzcG9uc2UuaHRtbCk7XG5cblx0XHRcdFx0Ly8gUmUtYXR0YWNoIGxpc3RlbmVycyB0byB0aGUgbmV3IGNvbnRlbnQuXG5cdFx0XHRcdGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCk7XG5cdFx0XHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFx0fVxuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY3JlZGl0IGFuZCBwYWdlIGxpbWl0IHN0YXRlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2Jvb2xlYW59IHJlc3BvbnNlSGFzQ3JlZGl0IFdoZXRoZXIgdGhlIHVzZXIgaGFzIGNyZWRpdC5cblx0ICogQHBhcmFtIHtib29sZWFufSByZXNwb25zZUNhbkFkZFBhZ2VzIFdoZXRoZXIgdGhlIHVzZXIgY2FuIGFkZCBwYWdlcy5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlSGFzQ3JlZGl0LCByZXNwb25zZUNhbkFkZFBhZ2VzKSB7XG5cdFx0Ly8gVHJhY2sgaWYgc3RhdGUgYWN0dWFsbHkgY2hhbmdlZFxuXHRcdGNvbnN0IGNyZWRpdENoYW5nZWQgPSByZXNwb25zZUhhc0NyZWRpdCAhPT0gdW5kZWZpbmVkICYmIGhhc0NyZWRpdCAhPT0gcmVzcG9uc2VIYXNDcmVkaXQ7XG5cdFx0Y29uc3QgY2FuQWRkQ2hhbmdlZCA9IHJlc3BvbnNlQ2FuQWRkUGFnZXMgIT09IHVuZGVmaW5lZCAmJiBjYW5BZGRQYWdlcyAhPT0gcmVzcG9uc2VDYW5BZGRQYWdlcztcblxuXHRcdGlmIChjcmVkaXRDaGFuZ2VkKSB7XG5cdFx0XHRoYXNDcmVkaXQgPSByZXNwb25zZUhhc0NyZWRpdDtcblx0XHR9XG5cblx0XHRpZiAoY2FuQWRkQ2hhbmdlZCkge1xuXHRcdFx0Y2FuQWRkUGFnZXMgPSByZXNwb25zZUNhbkFkZFBhZ2VzO1xuXHRcdH1cblxuXHRcdC8vIElmIGNyZWRpdCBvciBwYWdlIGxpbWl0IHN0YXRlIGNoYW5nZWQsIHVwZGF0ZSBhbGwgYnV0dG9uc1xuXHRcdGlmIChjcmVkaXRDaGFuZ2VkIHx8IGNhbkFkZENoYW5nZWQpIHtcblx0XHRcdHVwZGF0ZUFsbFJldGVzdEJ1dHRvbnMoKTtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlIGFsbCBSZS10ZXN0IGJ1dHRvbnMgYmFzZWQgb24gY3VycmVudCBjcmVkaXQgc3RhdGUuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVBbGxSZXRlc3RCdXR0b25zKCkge1xuXHRcdC8vIFVwZGF0ZSBhbGwgUmUtdGVzdCBidXR0b24gbGlua3Ncblx0XHRqUXVlcnkoJy53cHItcmktcmV0ZXN0LWxpbmsnKS5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0Ly8gQ2hlY2sgaWYgdGhpcyByb3cgaXMgY3VycmVudGx5IGJlaW5nIHByb2Nlc3NlZFxuXHRcdFx0Y29uc3QgaXNSdW5uaW5nID0gcm93SWQgJiYgYWN0aXZlUG9sbHNbcm93SWRdO1xuXG5cdFx0XHRpZiAoIWhhc0NyZWRpdCB8fCBpc1J1bm5pbmcpIHtcblx0XHRcdFx0Ly8gRGlzYWJsZSB0aGUgYnV0dG9uXG5cdFx0XHRcdGlmIChidXR0b24uaXMoJ2J1dHRvbicpKSB7XG5cdFx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSk7XG5cdFx0XHRcdFx0YnV0dG9uLmFkZENsYXNzKCd3cHItcmktZGlzYWJsZWQnKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHQvLyBJdCdzIGEgc3BhbiwgYWxyZWFkeSBzdHlsZWQgYXMgZGlzYWJsZWQgaW4gUEhQXG5cdFx0XHRcdFx0YnV0dG9uLmFkZENsYXNzKCd3cHItcmktZGlzYWJsZWQnKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFNob3cgdGhlIG5vLWNyZWRpdCBtZXNzYWdlIGlmIGl0IGV4aXN0cyBpbiB0aGUgc2FtZSBhY3Rpb25zIHdyYXBwZXJcblx0XHRcdFx0Y29uc3QgYWN0aW9uc1dyYXBwZXIgPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1hY3Rpb25zLXdyYXBwZXInKTtcblx0XHRcdFx0Y29uc3Qgbm9DcmVkaXRUZXh0ID0gYWN0aW9uc1dyYXBwZXIuZmluZCgnLndwci1yaS1uby1jcmVkaXQtdGV4dCcpO1xuXHRcdFx0XHRpZiAobm9DcmVkaXRUZXh0Lmxlbmd0aCAmJiAhaGFzQ3JlZGl0KSB7XG5cdFx0XHRcdFx0bm9DcmVkaXRUZXh0LnNob3coKTtcblx0XHRcdFx0fVxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Ly8gUmUtZW5hYmxlIHRoZSBidXR0b25cblx0XHRcdFx0aWYgKGJ1dHRvbi5pcygnYnV0dG9uJykpIHtcblx0XHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG5cdFx0XHRcdFx0YnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItcmktZGlzYWJsZWQnKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHQvLyBDb252ZXJ0IHNwYW4gdG8gYnV0dG9uIGlmIGNyZWRpdCBpcyByZXN0b3JlZFxuXHRcdFx0XHRcdGNvbnN0IG5ld0J1dHRvbiA9IGpRdWVyeSgnPGJ1dHRvbj4nKVxuXHRcdFx0XHRcdFx0LmF0dHIoJ3R5cGUnLCAnYnV0dG9uJylcblx0XHRcdFx0XHRcdC5hdHRyKCdjbGFzcycsIGJ1dHRvbi5hdHRyKCdjbGFzcycpKVxuXHRcdFx0XHRcdFx0LmF0dHIoJ2RhdGEtdXJsJywgYnV0dG9uLmRhdGEoJ3VybCcpKVxuXHRcdFx0XHRcdFx0LnJlbW92ZUNsYXNzKCd3cHItcmktZGlzYWJsZWQnKVxuXHRcdFx0XHRcdFx0Lmh0bWwoYnV0dG9uLmh0bWwoKSk7XG5cdFx0XHRcdFx0YnV0dG9uLnJlcGxhY2VXaXRoKG5ld0J1dHRvbik7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBIaWRlIHRoZSBuby1jcmVkaXQgbWVzc2FnZVxuXHRcdFx0XHRjb25zdCBhY3Rpb25zV3JhcHBlciA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWFjdGlvbnMtd3JhcHBlcicpO1xuXHRcdFx0XHRjb25zdCBub0NyZWRpdFRleHQgPSBhY3Rpb25zV3JhcHBlci5maW5kKCcud3ByLXJpLW5vLWNyZWRpdC10ZXh0Jyk7XG5cdFx0XHRcdGlmIChub0NyZWRpdFRleHQubGVuZ3RoKSB7XG5cdFx0XHRcdFx0bm9DcmVkaXRUZXh0LmhpZGUoKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXG5cdFx0Ly8gVXBkYXRlIGFsbCBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zXG5cdFx0alF1ZXJ5KCcud3ByLXJpLXRlc3QtcGFnZScpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cblx0XHRcdGlmICghaGFzQ3JlZGl0IHx8ICFjYW5BZGRQYWdlcykge1xuXHRcdFx0XHQvLyBEaXNhYmxlIHRlc3QgYnV0dG9uIGFuZCBhZGQgbm8tY3JlZGl0IGNsYXNzXG5cdFx0XHRcdGJ1dHRvbi5hZGRDbGFzcygnd3ByLXJpLW5vLWNyZWRpdCcpO1xuXHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIEVuYWJsZSB0ZXN0IGJ1dHRvblxuXHRcdFx0XHRidXR0b24ucmVtb3ZlQ2xhc3MoJ3dwci1yaS1uby1jcmVkaXQnKTtcblx0XHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0Ly8gQXV0by1pbml0aWFsaXplIG9uIERPTSByZWFkeVxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGluaXQpO1xuXHR9IGVsc2Uge1xuXHRcdGluaXQoKTtcblx0fVxuXG5cdHJldHVybiB7XG5cdFx0aW5pdDogaW5pdFxuXHR9O1xufSkoKTtcbiJdfQ==
