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
      if (success && id) {
        // Begin common loading + polling flow.
        beginLoadingAndPoll(column, id, url);

        // Check if we've reached the limit and disable all other "Test the page" buttons.
        if (canAdd === false || response?.data?.remaining_urls === 0) {
          disableAllTestPageButtons();
        }
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7O01BRTNCO01BQ0EsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDLEVBQUU7UUFDeEM7TUFDRDtNQUVBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzlCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFFL0MsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEs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QUFFLElBQUksQ0FBQztFQUNwRCxDQUFDLE1BQU07SUFDTixJQUFJLENBQUMsQ0FBQztFQUNQO0VBRUEsT0FBTztJQUNOLElBQUksRUFBRTtFQUNQLENBQUM7QUFDRixDQUFDLENBQUUsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHQvKipcblx0ICogUG9sbGluZyBpbnRlcnZhbCBmb3IgY2hlY2tpbmcgb25nb2luZyB0ZXN0cyAoaW4gbWlsbGlzZWNvbmRzKS5cblx0ICovXG5cdGNvbnN0IFBPTExJTkdfSU5URVJWQUwgPSA1MDAwOyAvLyA1IHNlY29uZHNcblxuXHQvKipcblx0ICogQWN0aXZlIHBvbGxpbmcgaW50ZXJ2YWxzIGJ5IHBvc3QgSUQuXG5cdCAqL1xuXHRjb25zdCBhY3RpdmVQb2xscyA9IHt9O1xuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplIFJvY2tldCBJbnNpZ2h0cyBvbiBwb3N0IGxpc3RpbmcgcGFnZXNcblx0ICovXG5cdGZ1bmN0aW9uIGluaXQoKSB7XG5cdFx0Ly8gQXR0YWNoIGV2ZW50IGxpc3RlbmVycy5cblx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXG5cdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgYW55IHJvd3MgdGhhdCBhcmUgYWxyZWFkeSBydW5uaW5nLlxuXHRcdHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdGVzdC1wYWdlJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXG5cdFx0XHQvLyBEb24ndCBhbGxvdyBjbGljayBpZiBubyBjcmVkaXRcblx0XHRcdGlmIChidXR0b24uaGFzQ2xhc3MoJ3dwci1yaS1uby1jcmVkaXQnKSkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IHVybCA9IGJ1dHRvbi5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXG5cdFx0XHRhZGROZXdQYWdlKHVybCwgY29sdW1uLCBidXR0b24pO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJSZS10ZXN0XCIgYnV0dG9ucyBhbmQgbGlua3MuXG5cdCAqL1xuXHRmdW5jdGlvbiBhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKSB7XG5cdFx0Ly8gU3VwcG9ydCBib3RoIGJ1dHRvbiBhbmQgbGluayBzdHlsZXMgd2l0aCBvbmUgaGFuZGxlci5cblx0XHRqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJpLXJldGVzdDpub3QoLndwci1yaS1hY3Rpb24tLWRpc2FibGVkKSwgLndwci1yaS1yZXRlc3QtbGluaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGVsID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgdXJsID0gZWwuZGF0YSgndXJsJyk7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBlbC5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3Igcm93cyB0aGF0IGFyZSBjdXJyZW50bHkgcnVubmluZyB0ZXN0cy5cblx0ICovXG5cdGZ1bmN0aW9uIHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktbG9hZGluZycpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBjb2x1bW4gPSBqUXVlcnkodGhpcykuY2xvc2VzdCgnLndwci1yaS1jb2x1bW4nKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gY29sdW1uLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXG5cdFx0XHRpZiAocm93SWQgJiYgIWFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBZGQgYSBuZXcgcGFnZSBmb3IgdGVzdGluZy5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIHRvIHRlc3QuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gYnV0dG9uIFRoZSBidXR0b24gdGhhdCB3YXMgY2xpY2tlZC5cblx0ICovXG5cdGZ1bmN0aW9uIGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbikge1xuXHRcdC8vIERpc2FibGUgYnV0dG9uIGFuZCBzaG93IGxvYWRpbmcgc3RhdGUuXG5cdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSk7XG5cblx0XHQvLyBVc2UgUkVTVCAoSEVBRCkgYnV0IGtlZXAgZGV2ZWxvcCdzIHJvYnVzdCBoYW5kbGluZy5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goe1xuXHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycsXG5cdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdGRhdGE6IHsgcGFnZV91cmw6IHVybCB9LFxuXHRcdH0pLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG5cdFx0XHRjb25zdCBzdWNjZXNzICAgPSByZXNwb25zZT8uc3VjY2VzcyA9PT0gdHJ1ZTtcblx0XHRcdGNvbnN0IGlkICAgICAgICA9IHJlc3BvbnNlPy5pZCA/PyByZXNwb25zZT8uZGF0YT8uaWQgPz8gbnVsbDtcblx0XHRcdGNvbnN0IGNhbkFkZCAgICA9IChyZXNwb25zZT8uY2FuX2FkZF9wYWdlcyA/PyByZXNwb25zZT8uZGF0YT8uY2FuX2FkZF9wYWdlcyk7XG5cdFx0XHRjb25zdCBtZXNzYWdlICAgPSByZXNwb25zZT8ubWVzc2FnZSA/PyByZXNwb25zZT8uZGF0YT8ubWVzc2FnZTtcblxuXHRcdFx0aWYgKHN1Y2Nlc3MgJiYgaWQpIHtcblx0XHRcdFx0Ly8gQmVnaW4gY29tbW9uIGxvYWRpbmcgKyBwb2xsaW5nIGZsb3cuXG5cdFx0XHRcdGJlZ2luTG9hZGluZ0FuZFBvbGwoY29sdW1uLCBpZCwgdXJsKTtcblxuXHRcdFx0XHQvLyBDaGVjayBpZiB3ZSd2ZSByZWFjaGVkIHRoZSBsaW1pdCBhbmQgZGlzYWJsZSBhbGwgb3RoZXIgXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0XHRcdFx0aWYgKGNhbkFkZCA9PT0gZmFsc2UgfHwgcmVzcG9uc2U/LmRhdGE/LnJlbWFpbmluZ191cmxzID09PSAwKSB7XG5cdFx0XHRcdFx0ZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gSWYgYmFja2VuZCBzYXlzIHdlIGNhbm5vdCBhZGQgcGFnZXMsIHJlLWVuYWJsZSBhbmQgcmVzZXQgbGFiZWwgd2l0aG91dCBlcnJvciBiYW5uZXIuXG5cdFx0XHRpZiAoY2FuQWRkID09PSBmYWxzZSkge1xuXHRcdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSlcblx0XHRcdFx0XHQudGV4dCh3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnRlc3RfcGFnZSB8fCAnVGVzdCB0aGUgcGFnZScpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIE90aGVyIGVycm9yc1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0fSkuY2F0Y2goKGVycm9yKSA9PiB7XG5cdFx0XHQvLyB3cC5hcGlGZXRjaCB0aHJvd3Mgb24gV1BfRXJyb3I7IHRyeSB0byBzdXJmYWNlIGEgaGVscGZ1bCBtZXNzYWdlLlxuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cdFx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSlcblx0XHRcdFx0LnRleHQod2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy50ZXN0X3BhZ2UgfHwgJ1Rlc3QgdGhlIHBhZ2UnKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBSZXRlc3QgYW4gZXhpc3RpbmcgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyArIHJvd0lkLFxuXHRcdFx0XHRtZXRob2Q6ICdQQVRDSCcsXG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcblx0XHRcdFx0Ly8gQmVnaW4gY29tbW9uIGxvYWRpbmcgKyBwb2xsaW5nIGZsb3cuXG5cdFx0XHRcdGJlZ2luTG9hZGluZ0FuZFBvbGwoY29sdW1uLCByb3dJZCwgdXJsKTtcblx0XHRcdH1cblx0XHR9ICkuY2F0Y2goICggZXJyb3IgKSA9PiB7XG5cdFx0XHRjb25zb2xlLmVycm9yKGVycm9yKTtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3IgdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdC8vIENsZWFyIGFueSBleGlzdGluZyBwb2xsIGZvciB0aGlzIHJvdy5cblx0XHRpZiAoYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRjbGVhckludGVydmFsKGFjdGl2ZVBvbGxzW3Jvd0lkXSk7XG5cdFx0fVxuXG5cdFx0Ly8gU2V0IHVwIG5ldyBwb2xsaW5nIGludGVydmFsLlxuXHRcdGFjdGl2ZVBvbGxzW3Jvd0lkXSA9IHNldEludGVydmFsKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9LCBQT0xMSU5HX0lOVEVSVkFMKTtcblxuXHRcdC8vIEFsc28gY2hlY2sgaW1tZWRpYXRlbHkuXG5cdFx0Y2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDb21tb24gaGVscGVyIHRvIHNldCBsb2FkaW5nIHN0YXRlIGFuZCBzdGFydCBwb2xsaW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIHJvd0lkLCB1cmwpIHtcblx0XHQvLyBVcGRhdGUgY29sdW1uIHRvIGxvYWRpbmcgc3RhdGUgYW5kIHN0YXJ0IHBvbGxpbmcuXG5cdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKTtcblx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDaGVjayB0aGUgc3RhdHVzIG9mIGEgdGVzdC5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogd2luZG93LndwLnVybC5hZGRRdWVyeUFyZ3MoICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy9wcm9ncmVzcycsIHsgaWRzOiBbcm93SWRdIH0gKSxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgJiYgQXJyYXkuaXNBcnJheSggcmVzcG9uc2UucmVzdWx0cyApICkge1xuXHRcdFx0XHRjb25zdCByZXN1bHQgPSByZXNwb25zZS5yZXN1bHRzWzBdO1xuXG5cdFx0XHRcdGlmICggcmVzdWx0LnN0YXR1cyA9PT0gJ2NvbXBsZXRlZCcgfHwgcmVzdWx0LnN0YXR1cyA9PT0gJ2ZhaWxlZCcgKSB7XG5cdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nLlxuXHRcdFx0XHRcdGNsZWFySW50ZXJ2YWwoIGFjdGl2ZVBvbGxzW3Jvd0lkXSApO1xuXHRcdFx0XHRcdGRlbGV0ZSBhY3RpdmVQb2xsc1tyb3dJZF07XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgdGhlIGNvbHVtbiB3aXRoIHJlc3VsdHMgKHJlbG9hZCByZW5kZXJlZCBIVE1MIGZyb20gc2VydmVyKS5cblx0XHRcdFx0XHR1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyggY29sdW1uLCByZXN1bHQgKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTaG93IGxvYWRpbmcgc3RhdGUgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICovXG5cdGZ1bmN0aW9uIHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCkge1xuXHRcdGNvbHVtbi5hdHRyKCdkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZCcsIHJvd0lkKTtcblxuXHRcdC8vIENyZWF0ZSBlbGVtZW50cyBzYWZlbHkgdG8gcHJldmVudCBYU1Ncblx0XHRjb25zdCBsb2FkaW5nRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbG9hZGluZycpO1xuXHRcdGNvbnN0IGltZyA9IGpRdWVyeSgnPGltZz4nKS5hZGRDbGFzcygnd3ByLWxvYWRpbmctaW1nJykuYXR0cih7XG5cdFx0XHRzcmM6IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubG9hZGluZ19pbWcgfHwgJycsXG5cdFx0XHRhbHQ6ICdMb2FkaW5nLi4uJ1xuXHRcdH0pO1xuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBqUXVlcnkoJzxkaXY+JykuYWRkQ2xhc3MoJ3dwci1yaS1tZXNzYWdlJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblxuXHRcdGxvYWRpbmdEaXYuYXBwZW5kKGltZyk7XG5cdFx0Y29sdW1uLmVtcHR5KCkuYXBwZW5kKGxvYWRpbmdEaXYpLmFwcGVuZChtZXNzYWdlRGl2KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY29sdW1uIHdpdGggdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtPYmplY3R9IHJlc3VsdCBUaGUgdGVzdCByZXN1bHQgZGF0YS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KSB7XG5cdFx0Ly8gUmVsb2FkIHRoZSBlbnRpcmUgcm93IGZyb20gdGhlIHNlcnZlciB0byBnZXQgcHJvcGVybHkgcmVuZGVyZWQgSFRNTC5cblx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMnLCB7IHVybDogdXJsIH0gKSxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5odG1sKSB7XG5cdFx0XHRcdGNvbHVtbi5yZXBsYWNlV2l0aChyZXNwb25zZS5odG1sKTtcblxuXHRcdFx0XHQvLyBSZS1hdHRhY2ggbGlzdGVuZXJzIHRvIHRoZSBuZXcgY29udGVudC5cblx0XHRcdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRcdFx0YXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCk7XG5cdFx0XHR9XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIERpc2FibGUgYWxsIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMgd2hlbiB0aGUgVVJMIGxpbWl0IGlzIHJlYWNoZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS10ZXN0LXBhZ2U6bm90KC53cHItcmktbm8tY3JlZGl0KScpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRidXR0b24uYWRkQ2xhc3MoJ3dwci1yaS1uby1jcmVkaXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXG5cdFx0XHQvLyBBZGQgdGhlIFwiWW91J3ZlIHJlYWNoZWQgeW91ciBsaW1pdFwiIG1lc3NhZ2UgaWYgbm90IGFscmVhZHkgcHJlc2VudC5cblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3QgY3JlZGl0TWVzc2FnZSA9IGNvbHVtbi5maW5kKCcud3ByLXJpLWNyZWRpdC1tZXNzYWdlJyk7XG5cblx0XHRcdGlmIChjcmVkaXRNZXNzYWdlLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0XHRjb25zdCBtZXNzYWdlRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktY3JlZGl0LW1lc3NhZ2UnKTtcblx0XHRcdFx0Y29uc3QgaXNGcmVlVXNlciA9IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5pc19mcmVlX3VzZXIgfHwgZmFsc2U7XG5cdFx0XHRcdGNvbnN0IGxpbWl0TWVzc2FnZSA9IGlzRnJlZVVzZXJcblx0XHRcdFx0XHQ/IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uZnJlZV9saW1pdF9yZWFjaGVkXG5cdFx0XHRcdFx0OiB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LnBhaWRfbGltaXRfcmVhY2hlZDtcblxuXHRcdFx0XHRtZXNzYWdlRGl2Lmh0bWwobGltaXRNZXNzYWdlKTtcblx0XHRcdFx0YnV0dG9uLmFmdGVyKG1lc3NhZ2VEaXYpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0Ly8gQXV0by1pbml0aWFsaXplIG9uIERPTSByZWFkeVxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGluaXQpO1xuXHR9IGVsc2Uge1xuXHRcdGluaXQoKTtcblx0fVxuXG5cdHJldHVybiB7XG5cdFx0aW5pdDogaW5pdFxuXHR9O1xufSkoKTtcbiJdfQ==
