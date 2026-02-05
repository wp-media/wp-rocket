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
    attachViewDetailsListeners();

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
   * Attach click listeners to "View Details" links.
   */
  function attachViewDetailsListeners() {
    jQuery(document).on('click', '.wpr-ri-view-details-link:not(.wpr-ri-disabled)', function (e) {
      const link = jQuery(this);
      const rowId = link.data('rocket-insights-id');
      if (!rowId) {
        return;
      }

      // Track the View Details click
      trackViewDetailsClick(rowId, 'post type listing');
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

  /**
   * Track View Details click via AJAX.
   *
   * @param {number} rowId  The database row ID.
   * @param {string} context The context (e.g., 'post type listing').
   */
  function trackViewDetailsClick(rowId, context) {
    // Only track if AJAX URL is available
    if (!window.ajaxurl) {
      return;
    }
    jQuery.ajax({
      url: window.ajaxurl,
      type: 'POST',
      data: {
        action: 'rocket_track_view_details',
        row_id: rowId,
        context: context,
        nonce: window.rocket_ajax_data?.nonce || ''
      }
    }).catch(function (error) {
      // Silently fail tracking - don't interrupt user experience
      console.debug('Tracking failed:', error);
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFZO0VBQzdCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7SUFDdkIsMEJBQTBCLENBQUMsQ0FBQzs7SUFFNUI7SUFDQSwyQkFBMkIsQ0FBQyxDQUFDO0VBQzlCOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMsdUJBQXVCLENBQUEsRUFBRztJQUNsQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxtQkFBbUIsRUFBRSxVQUFVLENBQUMsRUFBRTtNQUM5RCxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7TUFDbEIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQztNQUMzQixNQUFNLEdBQUcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztNQUM5QixNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsT0FBTyxDQUFDLGdCQUFnQixDQUFDO01BRS9DLE1BQU0sV0FBVyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsb0JBQW9CLENBQUMsS0FBSyxHQUFHO01BRTdELElBQUssQ0FBRSxXQUFXLEVBQUc7UUFDcEIsZ0JBQWdCLENBQUUsTUFBTSxFQUFFLE1BQU8sQ0FBQztRQUNsQztNQUNEO01BRUEsVUFB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RDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxxQkFBcUIsQ0FBQyxLQUFLLEVBQUUsT0FBTyxFQUFFO0lBQzlDO0lBQ0EsSUFBSSxDQUFDLE1BQU0sQ0FBQyxPQUFPLEVBQUU7TUFDcEI7SUFDRDtJQUVBLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDWCxHQUFHLEVBQUUsTUFBTSxDQUFDLE9BQU87TUFDbkIsSUFBSSxFQUFFLE1BQU07TUFDWixJQUFJLEVBQUU7UUFDTCxNQUFNLEVBQUUsMkJBQTJCO1FBQ25DLE1BQU0sRUFBRSxLQUFLO1FBQ2IsT0FBTyxFQUFFLE9BQU87UUFDaEIsS0FBSyxFQUFFLE1BQU0sQ0FBQyxnQkFBZ0IsRUFBRSxLQUFLLElBQUk7TUFDMUM7SUFDRCxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsVUFBUyxLQUFLLEVBQUU7TUFDeEI7TUFDQSxPQUFPLENBQUMsS0FBSyxDQUFDLGtCQUFrQixFQUFFLEtBQUssQ0FBQztJQUN6QyxDQUFDLENBQUM7RUFDSDs7RUFFQTtFQUNBLElBQUksUUFBUSxDQUFDLFVBQVUsS0FBSyxTQUFTLEVBQUU7SUFDdEMsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLElBQUksQ0FBQztFQUNwRCxDQUFDLE1BQU07SUFDTixJQUFJLENBQUMsQ0FBQztFQUNQO0VBRUEsT0FBTztJQUNOLElBQUksRUFBRTtFQUNQLENBQUM7QUFDRixDQUFDLENBQUUsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24gKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hWaWV3RGV0YWlsc0xpc3RlbmVycygpO1xuXG5cdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgYW55IHJvd3MgdGhhdCBhcmUgYWxyZWFkeSBydW5uaW5nLlxuXHRcdHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdGVzdC1wYWdlJywgZnVuY3Rpb24gKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGJ1dHRvbi5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXG5cdFx0XHRjb25zdCBjYW5BZGRQYWdlcyA9IGNvbHVtbi5hdHRyKCdkYXRhLWNhbi1hZGQtcGFnZXMnKSA9PT0gJzEnO1xuXG5cdFx0XHRpZiAoICEgY2FuQWRkUGFnZXMgKSB7XG5cdFx0XHRcdHNob3dMaW1pdE1lc3NhZ2UoIGNvbHVtbiwgYnV0dG9uICk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiUmUtdGVzdFwiIGJ1dHRvbnMgYW5kIGxpbmtzLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCkge1xuXHRcdC8vIFN1cHBvcnQgYm90aCBidXR0b24gYW5kIGxpbmsgc3R5bGVzIHdpdGggb25lIGhhbmRsZXIuXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3Q6bm90KC53cHItcmktYWN0aW9uLS1kaXNhYmxlZCksIC53cHItcmktcmV0ZXN0LWxpbmsnLCBmdW5jdGlvbiAoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblx0XHRcdGNvbnN0IHNvdXJjZSA9IGVsLmRhdGEoJ3NvdXJjZScpIHx8IGNvbHVtbi5kYXRhKCdzb3VyY2UnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFJldGVzdCBzaG91bGQgb25seSBwcm9jZWVkIHdoZW4gdGhlIHVzZXIgaGFzIGNyZWRpdCBmb3IgdGhlIHRlc3QuXG5cdFx0XHRjb25zdCBoYXNDcmVkaXQgPSBjb2x1bW4uYXR0cignZGF0YS1oYXMtY3JlZGl0JykgPT09ICcxJztcblxuXHRcdFx0aWYgKCAhIGhhc0NyZWRpdCApIHtcblx0XHRcdFx0c2hvd0xpbWl0TWVzc2FnZSggY29sdW1uLCBlbCApO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uLCBzb3VyY2UpO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJWaWV3IERldGFpbHNcIiBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFZpZXdEZXRhaWxzTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdmlldy1kZXRhaWxzLWxpbms6bm90KC53cHItcmktZGlzYWJsZWQpJywgZnVuY3Rpb24gKGUpIHtcblx0XHRcdGNvbnN0IGxpbmsgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGxpbmsuZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBUcmFjayB0aGUgVmlldyBEZXRhaWxzIGNsaWNrXG5cdFx0XHR0cmFja1ZpZXdEZXRhaWxzQ2xpY2socm93SWQsICdwb3N0IHR5cGUgbGlzdGluZycpO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHJvd3MgdGhhdCBhcmUgY3VycmVudGx5IHJ1bm5pbmcgdGVzdHMuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKSB7XG5cdFx0alF1ZXJ5KCcud3ByLXJpLWxvYWRpbmcnKS5lYWNoKGZ1bmN0aW9uICgpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZSBpbW1lZGlhdGVseS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdC8vIFNob3cgbG9hZGluZyBzcGlubmVyIGltbWVkaWF0ZWx5IGJlZm9yZSBBUEkgY2FsbFxuXHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCBudWxsKTtcblxuXHRcdC8vIFVzZSBSRVNUIChIRUFEKSBidXQga2VlcCBkZXZlbG9wJ3Mgcm9idXN0IGhhbmRsaW5nLlxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YTogeyBcblx0XHRcdFx0cGFnZV91cmw6IHVybCxcblx0XHRcdFx0c291cmNlOiAncG9zdCB0eXBlIGxpc3RpbmcnXG5cdFx0XHR9LFxuXHRcdH0pLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG5cdFx0XHRjb25zdCBzdWNjZXNzID0gcmVzcG9uc2U/LnN1Y2Nlc3MgPT09IHRydWU7XG5cdFx0XHRjb25zdCBpZCA9IHJlc3BvbnNlPy5pZCA/PyByZXNwb25zZT8uZGF0YT8uaWQgPz8gbnVsbDtcblx0XHRcdGNvbnN0IGNhbkFkZCA9IChyZXNwb25zZT8uY2FuX2FkZF9wYWdlcyA/PyByZXNwb25zZT8uZGF0YT8uY2FuX2FkZF9wYWdlcyk7XG5cdFx0XHRjb25zdCBtZXNzYWdlID0gcmVzcG9uc2U/Lm1lc3NhZ2UgPz8gcmVzcG9uc2U/LmRhdGE/Lm1lc3NhZ2U7XG5cblx0XHRcdGlmIChzdWNjZXNzICYmIGlkKSB7XG5cdFx0XHRcdC8vIFVwZGF0ZSBjb2x1bW4gd2l0aCB0aGUgcm93IElEIGFuZCBzdGFydCBwb2xsaW5nXG5cdFx0XHRcdGNvbHVtbi5hdHRyKCdkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZCcsIGlkKTtcblx0XHRcdFx0c3RhcnRQb2xsaW5nKGlkLCB1cmwsIGNvbHVtbik7XG5cblx0XHRcdFx0Ly8gQ2hlY2sgaWYgd2UndmUgcmVhY2hlZCB0aGUgbGltaXQgYW5kIGRpc2FibGUgYWxsIG90aGVyIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMuXG5cdFx0XHRcdGlmIChjYW5BZGQgPT09IGZhbHNlIHx8IHJlc3BvbnNlPy5kYXRhPy5yZW1haW5pbmdfdXJscyA9PT0gMCkge1xuXHRcdFx0XHRcdGRpc2FibGVBbGxUZXN0UGFnZUJ1dHRvbnMoKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIGJhY2tlbmQgc2F5cyB3ZSBjYW5ub3QgYWRkIHBhZ2VzIG9yIG90aGVyIGVycm9ycywgcmVzdG9yZSBvcmlnaW5hbCBzdGF0ZVxuXHRcdFx0Ly8gUmVsb2FkIHRoZSBjb2x1bW4gSFRNTCBmcm9tIHNlcnZlciB0byByZXN0b3JlIHRoZSBidXR0b25cblx0XHRcdHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpO1xuXHRcdH0pLmNhdGNoKChlcnJvcikgPT4ge1xuXHRcdFx0Ly8gd3AuYXBpRmV0Y2ggdGhyb3dzIG9uIFdQX0Vycm9yOyByZWxvYWQgY29sdW1uIHRvIHJlc3RvcmUgYnV0dG9uXG5cdFx0XHRjb25zb2xlLmVycm9yKGVycm9yKTtcblx0XHRcdHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFJldGVzdCBhbiBleGlzdGluZyBwYWdlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gc291cmNlIFRoZSBzb3VyY2Ugb2YgdGhlIHJlcXVlc3QuXG5cdCAqL1xuXHRmdW5jdGlvbiByZXRlc3RQYWdlKHJvd0lkLCB1cmwsIGNvbHVtbiwgc291cmNlKSB7XG5cdFx0Ly8gU2hvdyBsb2FkaW5nIHNwaW5uZXIgaW1tZWRpYXRlbHkgYmVmb3JlIEFQSSBjYWxsXG5cdFx0c2hvd0xvYWRpbmdTdGF0ZShjb2x1bW4sIHJvd0lkKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyByb3dJZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0XHRkYXRhOiB7XG5cdFx0XHRcdFx0c291cmNlOiBzb3VyY2Vcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdCkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdC8vIFN0YXJ0IHBvbGxpbmcgZm9yIHJlc3VsdHNcblx0XHRcdFx0c3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBJZiBub3Qgc3VjY2Vzc2Z1bCwgcmVsb2FkIHRoZSBjb2x1bW4gdG8gcmVzdG9yZSBwcmV2aW91cyBzdGF0ZVxuXHRcdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHRcdH1cblx0XHR9KS5jYXRjaCgoZXJyb3IpID0+IHtcblx0XHRcdGNvbnNvbGUuZXJyb3IoZXJyb3IpO1xuXHRcdFx0Ly8gUmVsb2FkIHRoZSBjb2x1bW4gdG8gcmVzdG9yZSBwcmV2aW91cyBzdGF0ZVxuXHRcdFx0cmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHRlc3QgcmVzdWx0cy5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHQvLyBDbGVhciBhbnkgZXhpc3RpbmcgcG9sbCBmb3IgdGhpcyByb3cuXG5cdFx0aWYgKGFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0Y2xlYXJJbnRlcnZhbChhY3RpdmVQb2xsc1tyb3dJZF0pO1xuXHRcdH1cblxuXHRcdC8vIFNldCB1cCBuZXcgcG9sbGluZyBpbnRlcnZhbC5cblx0XHRhY3RpdmVQb2xsc1tyb3dJZF0gPSBzZXRJbnRlcnZhbChmdW5jdGlvbiAoKSB7XG5cdFx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdH0sIFBPTExJTkdfSU5URVJWQUwpO1xuXG5cdFx0Ly8gQWxzbyBjaGVjayBpbW1lZGlhdGVseS5cblx0XHRjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3cgdGhlIHBlci1yb3cgbGltaXQgbWVzc2FnZSAob25seSBpbiB0aGUgY2xpY2tlZCByb3cpLlxuXHQgKiBEaXNhYmxlcyB0aGUgY2xpY2tlZCBlbGVtZW50IG1vbWVudGFyaWx5IHdoaWxlIHNob3dpbmcgdGhlIG1lc3NhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY2xpY2tlZEVsIFRoZSBlbGVtZW50IHRoYXQgdHJpZ2dlcmVkIHRoZSBhY3Rpb24uXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TGltaXRNZXNzYWdlKGNvbHVtbiwgY2xpY2tlZEVsKSB7XG5cdFx0Y29uc3QgbWVzc2FnZUh0bWwgPSBjb2x1bW4uZmluZCgnLndwci1yaS1saW1pdC1odG1sJykuaHRtbCgpIHx8IHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8ubGltaXRfcmVhY2hlZCB8fCAnJztcblxuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0bWVzc2FnZURpdi5odG1sKG1lc3NhZ2VIdG1sKS5zaG93KCk7XG5cblx0XHQvLyBEaXNhYmxlIG9ubHkgdGhlIGNsaWNrZWQgZWxlbWVudCBicmllZmx5IHRvIHByZXZlbnQgc3BhbSBjbGlja3MsIHRoZW4gcmUtZW5hYmxlLlxuXHRcdGlmIChjbGlja2VkRWwgJiYgY2xpY2tlZEVsLnByb3ApIHtcblx0XHRcdGNsaWNrZWRFbC5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuXHRcdFx0c2V0VGltZW91dChmdW5jdGlvbigpIHtcblx0XHRcdFx0Y2xpY2tlZEVsLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpO1xuXHRcdFx0fSwgMzAwMCk7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIENoZWNrIHRoZSBzdGF0dXMgb2YgYSB0ZXN0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gY2hlY2tTdGF0dXMocm93SWQsIHVybCwgY29sdW1uKSB7XG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncygnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvcHJvZ3Jlc3MnLCB7IGlkczogW3Jvd0lkXSB9KSxcblx0XHRcdH1cblx0XHQpLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KHJlc3BvbnNlLnJlc3VsdHMpKSB7XG5cdFx0XHRcdGNvbnN0IHJlc3VsdCA9IHJlc3BvbnNlLnJlc3VsdHNbMF07XG5cblx0XHRcdFx0aWYgKHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnKSB7XG5cdFx0XHRcdFx0Ly8gU3RvcCBwb2xsaW5nLlxuXHRcdFx0XHRcdGNsZWFySW50ZXJ2YWwoYWN0aXZlUG9sbHNbcm93SWRdKTtcblx0XHRcdFx0XHRkZWxldGUgYWN0aXZlUG9sbHNbcm93SWRdO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBjb2x1bW4gd2l0aCByZXN1bHRzIChyZWxvYWQgcmVuZGVyZWQgSFRNTCBmcm9tIHNlcnZlcikuXG5cdFx0XHRcdFx0dXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBsb2FkaW5nIHN0YXRlIGluIHRoZSBjb2x1bW4uXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQgKGNhbiBiZSBudWxsIHdoZW4gaW5pdGlhbGx5IHNob3dpbmcgbG9hZGluZykuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpIHtcblx0XHRpZiAocm93SWQpIHtcblx0XHRcdGNvbHVtbi5hdHRyKCdkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZCcsIHJvd0lkKTtcblx0XHR9XG5cblx0XHQvLyBDcmVhdGUgZWxlbWVudHMgc2FmZWx5IHRvIHByZXZlbnQgWFNTXG5cdFx0Y29uc3QgbG9hZGluZ0RpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLWxvYWRpbmcgd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyk7XG5cdFx0Y29uc3QgaW1nID0galF1ZXJ5KCc8aW1nPicpLmFkZENsYXNzKCd3cHItbG9hZGluZy1pbWcnKS5hdHRyKHtcblx0XHRcdHNyYzogd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5sb2FkaW5nX2ltZyB8fCAnJyxcblx0XHRcdGFsdDogJ0xvYWRpbmcuLi4nXG5cdFx0fSk7XG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLW1lc3NhZ2UnKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXG5cdFx0bG9hZGluZ0Rpdi5hcHBlbmQoaW1nKTtcblx0XHRsb2FkaW5nRGl2LmFwcGVuZChgPGRpdiBjbGFzcz1cIndwci10b29sdGlwXCI+PGRpdiBjbGFzcz1cIndwci10b29sdGlwLWNvbnRlbnRcIj4ke3dpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8uZXN0aW1hdGVkX3RpbWVfdGV4dCB8fCAnQW5hbHl6aW5nIHlvdXIgcGFnZSAofjEgbWluKS4nfTwvZGl2PjwvZGl2PmApXG5cdFx0Y29sdW1uLmVtcHR5KCkuYXBwZW5kKGxvYWRpbmdEaXYpLmFwcGVuZChtZXNzYWdlRGl2KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBSZWxvYWQgY29sdW1uIEhUTUwgZnJvbSBzZXJ2ZXIuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgZm9yIHRoZSBjb2x1bW4uXG5cdCAqL1xuXHRmdW5jdGlvbiByZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKSB7XG5cdFx0Y29uc3QgcG9zdElkID0gY29sdW1uLmRhdGEoJ3Bvc3QtaWQnKTtcblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcycsIHsgdXJsOiB1cmwsIHBvc3RfaWQ6IHBvc3RJZCB9KSxcblx0XHRcdH1cblx0XHQpLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiByZXNwb25zZS5odG1sKSB7XG5cdFx0XHRcdGNvbHVtbi5yZXBsYWNlV2l0aChyZXNwb25zZS5odG1sKTtcblxuXHRcdFx0XHQvLyBSZS1hdHRhY2ggbGlzdGVuZXJzIHRvIHRoZSBuZXcgY29udGVudC5cblx0XHRcdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRcdFx0YXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCk7XG5cdFx0XHR9XG5cdFx0fSApLmNhdGNoKCAoIGVycm9yICkgPT4ge1xuXHRcdFx0Y29uc29sZS5lcnJvcignRmFpbGVkIHRvIHJlbG9hZCBjb2x1bW46JywgZXJyb3IpO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGUgY29sdW1uIHdpdGggdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICogQHBhcmFtIHtPYmplY3R9IHJlc3VsdCBUaGUgdGVzdCByZXN1bHQgZGF0YS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNvbHVtbldpdGhSZXN1bHRzKGNvbHVtbiwgcmVzdWx0KSB7XG5cdFx0Ly8gUmVsb2FkIHRoZSBlbnRpcmUgcm93IGZyb20gdGhlIHNlcnZlciB0byBnZXQgcHJvcGVybHkgcmVuZGVyZWQgSFRNTC5cblx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cdFx0cmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCk7XG5cdH1cblxuXHQvKipcblx0ICogTWFyayBhbGwgcmVtYWluaW5nIFwiVGVzdCB0aGUgcGFnZVwiIGJ1dHRvbnMgYXMgaGF2aW5nIHJlYWNoZWQgdGhlIGxpbWl0LlxuXHQgKiBVcGRhdGVzIGRhdGEgYXR0cmlidXRlcyBzbyBmdXR1cmUgY2xpY2tzIHdpbGwgc2hvdyB0aGUgbGltaXQgbWVzc2FnZSBwZXItcm93LlxuXHQgKiBEb2VzIE5PVCBkaXNwbGF5IGFueSBtZXNzYWdlIGltbWVkaWF0ZWx5IG9uIGFsbCByb3dzLlxuXHQgKi9cblx0ZnVuY3Rpb24gZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpIHtcblx0XHRqUXVlcnkoJy53cHItcmktdGVzdC1wYWdlJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0XG5cdFx0XHQvLyBVcGRhdGUgdGhlIGRhdGEgYXR0cmlidXRlIHNvIGZ1dHVyZSBjbGlja3Mgd2lsbCB0cmlnZ2VyIHRoZSBsaW1pdCBtZXNzYWdlLlxuXHRcdFx0Y29sdW1uLmF0dHIoJ2RhdGEtY2FuLWFkZC1wYWdlcycsICcwJyk7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogVHJhY2sgVmlldyBEZXRhaWxzIGNsaWNrIHZpYSBBSkFYLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBjb250ZXh0IFRoZSBjb250ZXh0IChlLmcuLCAncG9zdCB0eXBlIGxpc3RpbmcnKS5cblx0ICovXG5cdGZ1bmN0aW9uIHRyYWNrVmlld0RldGFpbHNDbGljayhyb3dJZCwgY29udGV4dCkge1xuXHRcdC8vIE9ubHkgdHJhY2sgaWYgQUpBWCBVUkwgaXMgYXZhaWxhYmxlXG5cdFx0aWYgKCF3aW5kb3cuYWpheHVybCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGpRdWVyeS5hamF4KHtcblx0XHRcdHVybDogd2luZG93LmFqYXh1cmwsXG5cdFx0XHR0eXBlOiAnUE9TVCcsXG5cdFx0XHRkYXRhOiB7XG5cdFx0XHRcdGFjdGlvbjogJ3JvY2tldF90cmFja192aWV3X2RldGFpbHMnLFxuXHRcdFx0XHRyb3dfaWQ6IHJvd0lkLFxuXHRcdFx0XHRjb250ZXh0OiBjb250ZXh0LFxuXHRcdFx0XHRub25jZTogd2luZG93LnJvY2tldF9hamF4X2RhdGE/Lm5vbmNlIHx8ICcnXG5cdFx0XHR9XG5cdFx0fSkuY2F0Y2goZnVuY3Rpb24oZXJyb3IpIHtcblx0XHRcdC8vIFNpbGVudGx5IGZhaWwgdHJhY2tpbmcgLSBkb24ndCBpbnRlcnJ1cHQgdXNlciBleHBlcmllbmNlXG5cdFx0XHRjb25zb2xlLmRlYnVnKCdUcmFja2luZyBmYWlsZWQ6JywgZXJyb3IpO1xuXHRcdH0pO1xuXHR9XG5cblx0Ly8gQXV0by1pbml0aWFsaXplIG9uIERPTSByZWFkeVxuXHRpZiAoZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGluaXQpO1xuXHR9IGVsc2Uge1xuXHRcdGluaXQoKTtcblx0fVxuXG5cdHJldHVybiB7XG5cdFx0aW5pdDogaW5pdFxuXHR9O1xufSkoKTtcbiJdfQ==
