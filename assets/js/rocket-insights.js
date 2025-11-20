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
      const source = el.data('source') || column.data('source');
      if (!rowId) {
        return;
      }

      // Retest should only proceed when the user has credit for the test.
      const hasCredit = column.attr('data-has-credit') === '1';
      if (!hasCredit) {
        showLimitMessage(column, el);
        return;
      }
      retestPage(rowId, url, column, source);
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
        page_url: url,
        source: 'post type listing'
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
   * @param {string} source The source of the request.
   */
  function retestPage(rowId, url, column, source) {
    // Show loading spinner immediately before API call
    showLoadingState(column, rowId);
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/' + rowId,
      method: 'PATCH',
      data: {
        source: source
      }
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
    const loadingDiv = jQuery('<div>').addClass('wpr-ri-loading wpr-btn-with-tool-tip');
    const img = jQuery('<img>').addClass('wpr-loading-img').attr({
      src: window.rocket_insights_i18n?.loading_img || '',
      alt: 'Loading...'
    });
    const messageDiv = jQuery('<div>').addClass('wpr-ri-message').css('display', 'none');
    loadingDiv.append(img);
    loadingDiv.append(`<div class="wpr-tooltip"><div class="wpr-tooltip-content">${window.rocket_insights_i18n?.estimated_time_text || 'Analyzing your page (~1 min).'}</div></div>`);
    column.empty().append(loadingDiv).append(messageDiv);
  }

  /**
   * Reload column HTML from server.
   *
   * @param {jQuery} column The column element.
   * @param {string} url    The URL for the column.
   */
  function reloadColumnFromServer(column, url) {
    const postId = column.data('post-id');
    window.wp.apiFetch({
      path: window.wp.url.addQueryArgs('/wp-rocket/v1/rocket-insights/pages', {
        url: url,
        post_id: postId
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFZO0VBQzdCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBVSxDQUFDLEVBQUU7TUFDOUQsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFDOUIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUUvQyxNQUFNLFdBQVcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDLEtBQUssR0FBRztNQUU3RCxJQUFLLENBQUUsV0FBVyxFQUFHO1FBQ3BCLGdCQUFnQixDQUFFLE1BQU0sRUFBRSxNQUFPLENBQUM7UUFDbEM7TUFDRDtNQUVBLFVBQVUsQ0FBQyxHQUFHLEVBQUUsTUFBTSxF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QUNELENBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBSSxLQUFLLElBQU07TUFDdkIsT0FBTyxDQUFDLEtBQUssQ0FBQywwQkFBMEIsRUFBRSxLQUFLLENBQUM7SUFDakQsQ0FBRSxDQUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyx1QkFBdUIsQ0FBQyxNQUFNLEVBQUUsTUFBTSxFQUFFO0lBQ2hEO0lBQ0EsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7SUFDOUIsc0JBQXNCLENBQUMsTUFBTSxFQUFFLEdBQUcsQ0FBQztFQUNwQzs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyx5QkFBeUIsQ0FBQSxFQUFHO0lBQ3BDLE1BQU0sQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxZQUFXO01BQzNDLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQzs7TUFFL0M7TUFDQSxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixFQUFFLEdBQUcsQ0FBQztJQUN2QyxDQUFDLENBQUM7RUFDSDs7RUFFQTtFQUNBLElBQUksUUFBUSxDQUFDLFVBQVUsS0FBSyxTQUFTLEVBQUU7SUFDdEMsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLElBQUksQ0FBQztFQUNwRCxDQUFDLE1BQU07SUFDTixJQUFJLENBQUMsQ0FBQztFQUNQO0VBRUEsT0FBTztJQUNOLElBQUksRUFBRTtFQUNQLENBQUM7QUFDRixDQUFDLENBQUUsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24gKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblxuXHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIGFueSByb3dzIHRoYXQgYXJlIGFscmVhZHkgcnVubmluZy5cblx0XHRzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpIHtcblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXRlc3QtcGFnZScsIGZ1bmN0aW9uIChlKSB7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBidXR0b24uZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblxuXHRcdFx0Y29uc3QgY2FuQWRkUGFnZXMgPSBjb2x1bW4uYXR0cignZGF0YS1jYW4tYWRkLXBhZ2VzJykgPT09ICcxJztcblxuXHRcdFx0aWYgKCAhIGNhbkFkZFBhZ2VzICkge1xuXHRcdFx0XHRzaG93TGltaXRNZXNzYWdlKCBjb2x1bW4sIGJ1dHRvbiApO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBTdXBwb3J0IGJvdGggYnV0dG9uIGFuZCBsaW5rIHN0eWxlcyB3aXRoIG9uZSBoYW5kbGVyLlxuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmV0ZXN0Om5vdCgud3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQpLCAud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24gKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGVsID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgdXJsID0gZWwuZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBlbC5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCBzb3VyY2UgPSBlbC5kYXRhKCdzb3VyY2UnKSB8fCBjb2x1bW4uZGF0YSgnc291cmNlJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBSZXRlc3Qgc2hvdWxkIG9ubHkgcHJvY2VlZCB3aGVuIHRoZSB1c2VyIGhhcyBjcmVkaXQgZm9yIHRoZSB0ZXN0LlxuXHRcdFx0Y29uc3QgaGFzQ3JlZGl0ID0gY29sdW1uLmF0dHIoJ2RhdGEtaGFzLWNyZWRpdCcpID09PSAnMSc7XG5cblx0XHRcdGlmICggISBoYXNDcmVkaXQgKSB7XG5cdFx0XHRcdHNob3dMaW1pdE1lc3NhZ2UoIGNvbHVtbiwgZWwgKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbiwgc291cmNlKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbiAoKSB7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBqUXVlcnkodGhpcykuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXG5cdFx0XHRpZiAocm93SWQgJiYgIWFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBZGQgYSBuZXcgcGFnZSBmb3IgdGVzdGluZy5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIHRvIHRlc3QuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gYnV0dG9uIFRoZSBidXR0b24gdGhhdCB3YXMgY2xpY2tlZC5cblx0ICovXG5cdGZ1bmN0aW9uIGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbikge1xuXHRcdC8vIERpc2FibGUgYnV0dG9uIGFuZCBzaG93IGxvYWRpbmcgc3RhdGUgaW1tZWRpYXRlbHkuXG5cdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSk7XG5cblx0XHQvLyBTaG93IGxvYWRpbmcgc3Bpbm5lciBpbW1lZGlhdGVseSBiZWZvcmUgQVBJIGNhbGxcblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgbnVsbCk7XG5cblx0XHQvLyBVc2UgUkVTVCAoSEVBRCkgYnV0IGtlZXAgZGV2ZWxvcCdzIHJvYnVzdCBoYW5kbGluZy5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goe1xuXHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycsXG5cdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdGRhdGE6IHsgXG5cdFx0XHRcdHBhZ2VfdXJsOiB1cmwsXG5cdFx0XHRcdHNvdXJjZTogJ3Bvc3QgdHlwZSBsaXN0aW5nJ1xuXHRcdFx0fSxcblx0XHR9KS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0Y29uc3Qgc3VjY2VzcyA9IHJlc3BvbnNlPy5zdWNjZXNzID09PSB0cnVlO1xuXHRcdFx0Y29uc3QgaWQgPSByZXNwb25zZT8uaWQgPz8gcmVzcG9uc2U/LmRhdGE/LmlkID8/IG51bGw7XG5cdFx0XHRjb25zdCBjYW5BZGQgPSAocmVzcG9uc2U/LmNhbl9hZGRfcGFnZXMgPz8gcmVzcG9uc2U/LmRhdGE/LmNhbl9hZGRfcGFnZXMpO1xuXHRcdFx0Y29uc3QgbWVzc2FnZSA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXG5cdFx0XHRpZiAoc3VjY2VzcyAmJiBpZCkge1xuXHRcdFx0XHQvLyBVcGRhdGUgY29sdW1uIHdpdGggdGhlIHJvdyBJRCBhbmQgc3RhcnQgcG9sbGluZ1xuXHRcdFx0XHRjb2x1bW4uYXR0cignZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQnLCBpZCk7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhpZCwgdXJsLCBjb2x1bW4pO1xuXG5cdFx0XHRcdC8vIENoZWNrIGlmIHdlJ3ZlIHJlYWNoZWQgdGhlIGxpbWl0IGFuZCBkaXNhYmxlIGFsbCBvdGhlciBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zLlxuXHRcdFx0XHRpZiAoY2FuQWRkID09PSBmYWxzZSB8fCByZXNwb25zZT8uZGF0YT8ucmVtYWluaW5nX3VybHMgPT09IDApIHtcblx0XHRcdFx0XHRkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCk7XG5cdFx0XHRcdH1cblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBJZiBiYWNrZW5kIHNheXMgd2UgY2Fubm90IGFkZCBwYWdlcyBvciBvdGhlciBlcnJvcnMsIHJlc3RvcmUgb3JpZ2luYWwgc3RhdGVcblx0XHRcdC8vIFJlbG9hZCB0aGUgY29sdW1uIEhUTUwgZnJvbSBzZXJ2ZXIgdG8gcmVzdG9yZSB0aGUgYnV0dG9uXG5cdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHR9KS5jYXRjaCgoZXJyb3IpID0+IHtcblx0XHRcdC8vIHdwLmFwaUZldGNoIHRocm93cyBvbiBXUF9FcnJvcjsgcmVsb2FkIGNvbHVtbiB0byByZXN0b3JlIGJ1dHRvblxuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBSZXRlc3QgYW4gZXhpc3RpbmcgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHNvdXJjZSBUaGUgc291cmNlIG9mIHRoZSByZXF1ZXN0LlxuXHQgKi9cblx0ZnVuY3Rpb24gcmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4sIHNvdXJjZSkge1xuXHRcdC8vIFNob3cgbG9hZGluZyBzcGlubmVyIGltbWVkaWF0ZWx5IGJlZm9yZSBBUEkgY2FsbFxuXHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nICsgcm93SWQsXG5cdFx0XHRcdG1ldGhvZDogJ1BBVENIJyxcblx0XHRcdFx0ZGF0YToge1xuXHRcdFx0XHRcdHNvdXJjZTogc291cmNlXG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHQpLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHQvLyBTdGFydCBwb2xsaW5nIGZvciByZXN1bHRzXG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Ly8gSWYgbm90IHN1Y2Nlc3NmdWwsIHJlbG9hZCB0aGUgY29sdW1uIHRvIHJlc3RvcmUgcHJldmlvdXMgc3RhdGVcblx0XHRcdFx0cmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCk7XG5cdFx0XHR9XG5cdFx0fSkuY2F0Y2goKGVycm9yKSA9PiB7XG5cdFx0XHRjb25zb2xlLmVycm9yKGVycm9yKTtcblx0XHRcdC8vIFJlbG9hZCB0aGUgY29sdW1uIHRvIHJlc3RvcmUgcHJldmlvdXMgc3RhdGVcblx0XHRcdHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0Ly8gQ2xlYXIgYW55IGV4aXN0aW5nIHBvbGwgZm9yIHRoaXMgcm93LlxuXHRcdGlmIChhY3RpdmVQb2xsc1tyb3dJZF0pIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHR9XG5cblx0XHQvLyBTZXQgdXAgbmV3IHBvbGxpbmcgaW50ZXJ2YWwuXG5cdFx0YWN0aXZlUG9sbHNbcm93SWRdID0gc2V0SW50ZXJ2YWwoZnVuY3Rpb24gKCkge1xuXHRcdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9LCBQT0xMSU5HX0lOVEVSVkFMKTtcblxuXHRcdC8vIEFsc28gY2hlY2sgaW1tZWRpYXRlbHkuXG5cdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTaG93IHRoZSBwZXItcm93IGxpbWl0IG1lc3NhZ2UgKG9ubHkgaW4gdGhlIGNsaWNrZWQgcm93KS5cblx0ICogRGlzYWJsZXMgdGhlIGNsaWNrZWQgZWxlbWVudCBtb21lbnRhcmlseSB3aGlsZSBzaG93aW5nIHRoZSBtZXNzYWdlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNsaWNrZWRFbCBUaGUgZWxlbWVudCB0aGF0IHRyaWdnZXJlZCB0aGUgYWN0aW9uLlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xpbWl0TWVzc2FnZShjb2x1bW4sIGNsaWNrZWRFbCkge1xuXHRcdGNvbnN0IG1lc3NhZ2VIdG1sID0gY29sdW1uLmZpbmQoJy53cHItcmktbGltaXQtaHRtbCcpLmh0bWwoKSB8fCB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmxpbWl0X3JlYWNoZWQgfHwgJyc7XG5cblx0XHRjb25zdCBtZXNzYWdlRGl2ID0gY29sdW1uLmZpbmQoJy53cHItcmktbWVzc2FnZScpO1xuXHRcdG1lc3NhZ2VEaXYuaHRtbChtZXNzYWdlSHRtbCkuc2hvdygpO1xuXG5cdFx0Ly8gRGlzYWJsZSBvbmx5IHRoZSBjbGlja2VkIGVsZW1lbnQgYnJpZWZseSB0byBwcmV2ZW50IHNwYW0gY2xpY2tzLCB0aGVuIHJlLWVuYWJsZS5cblx0XHRpZiAoY2xpY2tlZEVsICYmIGNsaWNrZWRFbC5wcm9wKSB7XG5cdFx0XHRjbGlja2VkRWwucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblx0XHRcdHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGNsaWNrZWRFbC5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKTtcblx0XHRcdH0sIDMwMDApO1xuXHRcdH1cblx0fVxuXG5cdC8qKlxuXHQgKiBDaGVjayB0aGUgc3RhdHVzIG9mIGEgdGVzdC5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogd2luZG93LndwLnVybC5hZGRRdWVyeUFyZ3MoJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IFtyb3dJZF0gfSksXG5cdFx0XHR9XG5cdFx0KS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgQXJyYXkuaXNBcnJheShyZXNwb25zZS5yZXN1bHRzKSkge1xuXHRcdFx0XHRjb25zdCByZXN1bHQgPSByZXNwb25zZS5yZXN1bHRzWzBdO1xuXG5cdFx0XHRcdGlmIChyZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJyB8fCByZXN1bHQuc3RhdHVzID09PSAnZmFpbGVkJykge1xuXHRcdFx0XHRcdC8vIFN0b3AgcG9sbGluZy5cblx0XHRcdFx0XHRjbGVhckludGVydmFsKGFjdGl2ZVBvbGxzW3Jvd0lkXSk7XG5cdFx0XHRcdFx0ZGVsZXRlIGFjdGl2ZVBvbGxzW3Jvd0lkXTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSB0aGUgY29sdW1uIHdpdGggcmVzdWx0cyAocmVsb2FkIHJlbmRlcmVkIEhUTUwgZnJvbSBzZXJ2ZXIpLlxuXHRcdFx0XHRcdHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgbG9hZGluZyBzdGF0ZSBpbiB0aGUgY29sdW1uLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElEIChjYW4gYmUgbnVsbCB3aGVuIGluaXRpYWxseSBzaG93aW5nIGxvYWRpbmcpLlxuXHQgKi9cblx0ZnVuY3Rpb24gc2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKSB7XG5cdFx0aWYgKHJvd0lkKSB7XG5cdFx0XHRjb2x1bW4uYXR0cignZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQnLCByb3dJZCk7XG5cdFx0fVxuXG5cdFx0Ly8gQ3JlYXRlIGVsZW1lbnRzIHNhZmVseSB0byBwcmV2ZW50IFhTU1xuXHRcdGNvbnN0IGxvYWRpbmdEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1sb2FkaW5nIHdwci1idG4td2l0aC10b29sLXRpcCcpO1xuXHRcdGNvbnN0IGltZyA9IGpRdWVyeSgnPGltZz4nKS5hZGRDbGFzcygnd3ByLWxvYWRpbmctaW1nJykuYXR0cih7XG5cdFx0XHRzcmM6IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubG9hZGluZ19pbWcgfHwgJycsXG5cdFx0XHRhbHQ6ICdMb2FkaW5nLi4uJ1xuXHRcdH0pO1xuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1tZXNzYWdlJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblxuXHRcdGxvYWRpbmdEaXYuYXBwZW5kKGltZyk7XG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoYDxkaXYgY2xhc3M9XCJ3cHItdG9vbHRpcFwiPjxkaXYgY2xhc3M9XCJ3cHItdG9vbHRpcC1jb250ZW50XCI+JHt3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmVzdGltYXRlZF90aW1lX3RleHQgfHwgJ0FuYWx5emluZyB5b3VyIHBhZ2UgKH4xIG1pbikuJ308L2Rpdj48L2Rpdj5gKVxuXHRcdGNvbHVtbi5lbXB0eSgpLmFwcGVuZChsb2FkaW5nRGl2KS5hcHBlbmQobWVzc2FnZURpdik7XG5cdH1cblxuXHQvKipcblx0ICogUmVsb2FkIGNvbHVtbiBIVE1MIGZyb20gc2VydmVyLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGZvciB0aGUgY29sdW1uLlxuXHQgKi9cblx0ZnVuY3Rpb24gcmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCkge1xuXHRcdGNvbnN0IHBvc3RJZCA9IGNvbHVtbi5kYXRhKCdwb3N0LWlkJyk7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncygnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMnLCB7IHVybDogdXJsLCBwb3N0X2lkOiBwb3N0SWQgfSksXG5cdFx0XHR9XG5cdFx0KS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuaHRtbCkge1xuXHRcdFx0XHRjb2x1bW4ucmVwbGFjZVdpdGgocmVzcG9uc2UuaHRtbCk7XG5cblx0XHRcdFx0Ly8gUmUtYXR0YWNoIGxpc3RlbmVycyB0byB0aGUgbmV3IGNvbnRlbnQuXG5cdFx0XHRcdGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCk7XG5cdFx0XHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFx0fVxuXHRcdH0gKS5jYXRjaCggKCBlcnJvciApID0+IHtcblx0XHRcdGNvbnNvbGUuZXJyb3IoJ0ZhaWxlZCB0byByZWxvYWQgY29sdW1uOicsIGVycm9yKTtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlIGNvbHVtbiB3aXRoIHRlc3QgcmVzdWx0cy5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSByZXN1bHQgVGhlIHRlc3QgcmVzdWx0IGRhdGEuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyhjb2x1bW4sIHJlc3VsdCkge1xuXHRcdC8vIFJlbG9hZCB0aGUgZW50aXJlIHJvdyBmcm9tIHRoZSBzZXJ2ZXIgdG8gZ2V0IHByb3Blcmx5IHJlbmRlcmVkIEhUTUwuXG5cdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXHRcdHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpO1xuXHR9XG5cblx0LyoqXG5cdCAqIE1hcmsgYWxsIHJlbWFpbmluZyBcIlRlc3QgdGhlIHBhZ2VcIiBidXR0b25zIGFzIGhhdmluZyByZWFjaGVkIHRoZSBsaW1pdC5cblx0ICogVXBkYXRlcyBkYXRhIGF0dHJpYnV0ZXMgc28gZnV0dXJlIGNsaWNrcyB3aWxsIHNob3cgdGhlIGxpbWl0IG1lc3NhZ2UgcGVyLXJvdy5cblx0ICogRG9lcyBOT1QgZGlzcGxheSBhbnkgbWVzc2FnZSBpbW1lZGlhdGVseSBvbiBhbGwgcm93cy5cblx0ICovXG5cdGZ1bmN0aW9uIGRpc2FibGVBbGxUZXN0UGFnZUJ1dHRvbnMoKSB7XG5cdFx0alF1ZXJ5KCcud3ByLXJpLXRlc3QtcGFnZScpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdFxuXHRcdFx0Ly8gVXBkYXRlIHRoZSBkYXRhIGF0dHJpYnV0ZSBzbyBmdXR1cmUgY2xpY2tzIHdpbGwgdHJpZ2dlciB0aGUgbGltaXQgbWVzc2FnZS5cblx0XHRcdGNvbHVtbi5hdHRyKCdkYXRhLWNhbi1hZGQtcGFnZXMnLCAnMCcpO1xuXHRcdH0pO1xuXHR9XG5cblx0Ly8gQXV0by1pbml0aWFsaXplIG9uIERPTSByZWFkeVxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGluaXQpO1xuXHR9IGVsc2Uge1xuXHRcdGluaXQoKTtcblx0fVxuXG5cdHJldHVybiB7XG5cdFx0aW5pdDogaW5pdFxuXHR9O1xufSkoKTtcbiJdfQ==
