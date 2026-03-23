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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFZO0VBQzdCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7SUFDdkIsMEJBQTBCLENBQUMsQ0FBQzs7SUFFNUI7SUFDQSwyQkFBMkIsQ0FBQyxDQUFDO0VBQzlCOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMsdUJBQXVCLENBQUEsRUFBRztJQUNsQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxtQkFBbUIsRUFBRSxVQUFVLENBQUMsRUFBRTtNQUM5RCxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7TUFDbEIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQztNQUMzQixNQUFNLEdBQUcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztNQUM5QixNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsT0FBTyxDQUFDLGdCQUFnQixDQUFDO01BRS9DLE1BQU0sV0FBVyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsb0JBQW9CLENBQUMsS0FBSyxHQUFHO01BRTdELElBQUssQ0FBRSxXQUFXLEVBQUc7UUFDcEIsZ0JBQWdCLENBQUUsTUFBTSxFQUFFLE1BQU8sQ0FBQztRQUNsQztNQUNEO01BRUEsVUFBVSxDQUFDLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMscUJBQXFCLENBQUEsRUFBRztJQUNoQztJQUNBLE1BQU0sQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLG1FQUFtRSxFQUFFLFVBQVUsQ0FBQyxFQUFFO01BQzlHLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztNQUNsQixNQUFNLEVBQUUsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDO01BQ3ZCLE1BQU0sR0FBRyxHQUFHLEVBQUUsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BQzFCLE1BQU0sTUFBTSxHQUFHLEVBQUUsQ0FBQyxPQUFPLENBQUMsZ0JBQWdCLENBQUM7TUFDM0MsTUFBTSxLQUFLLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxvQkFBb0IsQ0FBQztNQUMvQyxNQUFNLE1BQU0sR0FBRyxFQUFFLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLE1BQU0sQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDO01BRXpELElBQUksQ0FBQyxLQUFLLEVBQUU7UUFDWDtNQUNEOztNQUVBO01BQ0EsTUFBTSxTQUFTLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxLQUFLLEdBQUc7TUFFeEQsSUFBSyxDQUFFLFNBQVMsRUFBRztRQUNsQixnQkFBZ0IsQ0FBRSxNQUFNLEVBQUUsRUFBRyxDQUFDO1FBQzlCO01BQ0Q7TUFFQSxVQUFVLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDO0lBQ3ZDLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMsMEJBQTBCLENBQUEsRUFBRztJQUNyQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxpREFBaUQsRUFBRSxVQUFVLENBQUMsRUFBRTtNQUM1RixNQUFNLElBQUksR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDO01BQ3pCLE1BQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsb0JBQW9CLENBQUM7TUFFN0MsSUFBSSxDQUFDLEtBQUssRUFBRTtRQUNYO01BQ0Q7SUFDRCxDQUFDLENBQUM7RUFDSDs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLDJCQUEyQixDQUFBLEVBQUc7SUFDdEMsTUFBTSxDQUFDLGlCQUFpQixDQUFDLENBQUMsSUFBSSxDQUFDLFlBQVk7TUFDMUMsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUNyRCxNQUFNLEtBQUssR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDO01BQy9DLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO01BRTlCLElBQUksS0FBSyxJQUFJLENBQUMsV0FBVyxDQUFDLEtBQUssQ0FBQyxFQUFFO1FBQ2pDLFlBQVksQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLE1BQU0sQ0FBQztNQUNqQztJQUNELENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxVQUFVLENBQUMsR0FBRyxFQUFFLE1BQU0sRUFBRSxNQUFNLEVBQUU7SUFDeEM7SUFDQSxNQUFNLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxJQUFJLENBQUM7O0lBRTdCO0lBQ0EsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLElBQUksQ0FBQzs7SUFFOUI7SUFDQSxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBQztNQUNsQixJQUFJLEVBQUUsc0NBQXNDO01BQzVDLE1BQU0sRUFBRSxNQUFNO01BQ2QsSUFBSSxFQUFFO1FBQ0wsUUFBUSxFQUFFLEdBQUc7UUFDYixNQUFNLEVBQUU7TUFDVDtJQUNELENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBRSxRQUFRLElBQUs7TUFDckIsTUFBTSxPQUFPLEdBQUcsUUFBUSxFQUFFLE9BQU8sS0FBSyxJQUFJO01BQzFDLE1BQU0sRUFBRSxHQUFHLFFBQVEsRUFBRSxFQUFFLElBQUksUUFBUSxFQUFFLElBQUksRUFBRSxFQUFFLElBQUksSUFBSTtNQUNyRCxNQUFNLE1BQU0sR0FBSSxRQUFRLEVBQUUsYUFBYSxJQUFJLFFBQVEsRUFBRSxJQUFJLEVBQUUsYUFBYztNQUN6RSxNQUFNLE9BQU8sR0FBRyxRQUFRLEVBQUUsT0FBTyxJQUFJLFFBQVEsRUFBRSxJQUFJLEVBQUUsT0FBTztNQUU1RCxJQUFJLE9BQU8sSUFBSSxFQUFFLEVBQUU7UUFDbEI7UUFDQSxNQUFNLENBQUMsSUFBSSxDQUFDLHlCQUF5QixFQUFFLEVBQUUsQ0FBQztRQUMxQyxZQUFZLENBQUMsRUFBRSxFQUFFLEdBQUcsRUFBRSxNQUFNLENBQUM7O1FBRTdCO1FBQ0EsSUFBSSxNQUFNLEtBQUssS0FBSyxJQUFJLFFBQVEsRUFBRSxJQUFJLEVBQUUsY0FBYyxLQUFLLENBQUMsRUFBRTtVQUM3RCx5QkFBeUIsQ0FBQyxDQUFDO1FBQzVCO1FBQ0E7TUFDRDs7TUFFQTtNQUNBO01BQ0Esc0JBQXNCLENBQUMsTUFBTSxFQUFFLEdBQUcsQ0FBQztJQUNwQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUUsS0FBSyxJQUFLO01BQ25CO01BQ0EsT0FBTyxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUM7TUFDcEIsc0JBQXNCLENBQUMsTUFBTSxFQUFFLEdBQUcsQ0FBQztJQUNwQyxDQUFDLENBQUM7RUFDSDs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxVQUFVLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxNQUFNLEVBQUUsTUFBTSxFQUFFO0lBQy9DO0lBQ0EsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLEtBQUssQ0FBQztJQUUvQixNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FDakI7TUFDQyxJQUFJLEVBQUUsc0NBQXNDLEdBQUcsS0FBSztNQUNwRCxNQUFNLEVBQUUsT0FBTztNQUNmLElBQUksRUFBRTtRQUNMLE1BQU0sRUFBRTtNQUNUO0lBQ0QsQ0FDRCxDQUFDLENBQUMsSUFBSSxDQUFFLFFBQVEsSUFBSztNQUNwQixJQUFJLFFBQVEsQ0FBQyxPQUFPLEVBQUU7UUFDckI7UUFDQSxZQUFZLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxNQUFNLENBQUM7TUFDakMsQ0FBQyxNQUFNO1FBQ047UUFDQSxzQkFBc0IsQ0FBQyxNQUFNLEVBQUUsR0FBRyxDQUFDO01BQ3BDO0lBQ0QsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFFLEtBQUssSUFBSztNQUNuQixPQUFPLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQztNQUNwQjtNQUNBLHNCQUFzQixDQUFDLE1BQU0sRUFBRSxHQUFHLENBQUM7SUFDcEMsQ0FBRSxDQUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLFlBQVksQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLE1BQU0sRUFBRTtJQUN6QztJQUNBLElBQUksV0FBVyxDQUFDLEtBQUssQ0FBQyxFQUFFO01BQ3ZCLGFBQWEsQ0FBQyxXQUFXLENBQUMsS0FBSyxDQUFDLENBQUM7SUFDbEM7O0lBRUE7SUFDQSxXQUFXLENBQUMsS0FBSyxDQUFDLEdBQUcsV0FBVyxDQUFDLFlBQVk7TUFDNUMsV0FBVyxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsTUFBTSxDQUFDO0lBQ2hDLENBQUMsRUFBRSxnQkFBZ0IsQ0FBQzs7SUFFcEI7SUFDQSxXQUFXLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxNQUFNLENBQUM7RUFDaEM7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLGdCQUFnQixDQUFDLE1BQU0sRUFBRSxTQUFTLEVBQUU7SUFDNUMsTUFBTSxXQUFXLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksTUFBTSxDQUFDLG9CQUFvQixFQUFFLGFBQWEsSUFBSSxFQUFFO0lBRWhILE1BQU0sVUFBVSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUM7SUFDakQsVUFBVSxDQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQzs7SUFFbkM7SUFDQSxJQUFJLFNBQVMsSUFBSSxTQUFTLENBQUMsSUFBSSxFQUFFO01BQ2hDLFNBQVMsQ0FBQyxJQUFJLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQztNQUNoQyxVQUFVLENBQUMsWUFBVztRQUNyQixTQUFTLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxLQUFLLENBQUM7TUFDbEMsQ0FBQyxFQUFFLElBQUksQ0FBQztJQUNUO0VBQ0Q7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLFdBQVcsQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLE1BQU0sRUFBRTtJQUN4QyxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FDakI7TUFDQyxJQUFJLEVBQUUsTUFBTSxDQUFDLEVBQUUsQ0FBQyxHQUFHLENBQUMsWUFBWSxDQUFDLDhDQUE4QyxFQUFFO1FBQUUsR0FBRyxFQUFFLENBQUMsS0FBSztNQUFFLENBQUM7SUFDbEcsQ0FDRCxDQUFDLENBQUMsSUFBSSxDQUFFLFFBQVEsSUFBSztNQUNwQixJQUFJLFFBQVEsQ0FBQyxPQUFPLElBQUksS0FBSyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsT0FBTyxDQUFDLEVBQUU7UUFDeEQsTUFBTSxNQUFNLEdBQUcsUUFBUSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7UUFFbEMsSUFBSSxNQUFNLENBQUMsTUFBTSxLQUFLLFdBQVcsSUFBSSxNQUFNLENBQUMsTUFBTSxLQUFLLFFBQVEsRUFBRTtVQUNoRTtVQUNBLGFBQWEsQ0FBQyxXQUFXLENBQUMsS0FBSyxDQUFDLENBQUM7VUFDakMsT0FBTyxXQUFXLENBQUMsS0FBSyxDQUFDOztVQUV6QjtVQUNBLHVCQUF1QixDQUFDLE1BQU0sRUFBRSxNQUFNLENBQUM7UUFDeEM7TUFDRDtJQUNELENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLEtBQUssRUFBRTtJQUN4QyxJQUFJLEtBQUssRUFBRTtNQUNWLE1BQU0sQ0FBQyxJQUFJLENBQUMseUJBQXlCLEVBQUUsS0FBSyxDQUFDO0lBQzlDOztJQUVBO0lBQ0EsTUFBTSxVQUFVLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsQ0FBQyxzQ0FBc0MsQ0FBQztJQUNuRixNQUFNLEdBQUcsR0FBRyxNQUFNLENBQUMsT0FBTyxDQUFDLENBQUMsUUFBUSxDQUFDLGlCQUFpQixDQUFDLENBQUMsSUFBSSxDQUFDO01BQzVELEdBQUcsRUFBRSxNQUFNLENBQUMsb0JBQW9CLEVBQUUsV0FBVyxJQUFJLEVBQUU7TUFDbkQsR0FBRyxFQUFFO0lBQ04sQ0FBQyxDQUFDO0lBQ0YsTUFBTSxVQUFVLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxTQUFTLEVBQUUsTUFBTSxDQUFDO0lBRXBGLFVBQVUsQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDO0lBQ3RCLFVBQVUsQ0FBQyxNQUFNLENBQUMsNkRBQTZELE1BQU0sQ0FBQyxvQkFBb0IsRUFBRSxtQkFBbUIsSUFBSSwrQkFBK0IsY0FBYyxDQUFDO0lBQ2pMLE1BQU0sQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxVQUFVLENBQUMsQ0FBQyxNQUFNLENBQUMsVUFBVSxDQUFDO0VBQ3JEOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsc0JBQXNCLENBQUMsTUFBTSxFQUFFLEdBQUcsRUFBRTtJQUM1QyxNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztJQUNyQyxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FDakI7TUFDQyxJQUFJLEVBQUUsTUFBTSxDQUFDLEVBQUUsQ0FBQyxHQUFHLENBQUMsWUFBWSxDQUFDLHFDQUFxQyxFQUFFO1FBQUUsR0FBRyxFQUFFLEdBQUc7UUFBRSxPQUFPLEVBQUU7TUFBTyxDQUFDO0lBQ3RHLENBQ0QsQ0FBQyxDQUFDLElBQUksQ0FBRSxRQUFRLElBQUs7TUFDcEIsSUFBSSxRQUFRLENBQUMsT0FBTyxJQUFJLFFBQVEsQ0FBQyxJQUFJLEVBQUU7UUFDdEMsTUFBTSxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDOztRQUVqQztRQUNBLHVCQUF1QixDQUFDLENBQUM7UUFDekIscUJBQXFCLENBQUMsQ0FBQztNQUN4QjtJQUNELENBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBSSxLQUFLLElBQU07TUFDdkIsT0FBTyxDQUFDLEtBQUssQ0FBQywwQkFBMEIsRUFBRSxLQUFLLENBQUM7SUFDakQsQ0FBRSxDQUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyx1QkFBdUIsQ0FBQyxNQUFNLEVBQUUsTUFBTSxFQUFFO0lBQ2hEO0lBQ0EsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7SUFDOUIsc0JBQXNCLENBQUMsTUFBTSxFQUFFLEdBQUcsQ0FBQztFQUNwQzs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyx5QkFBeUIsQ0FBQSxFQUFHO0lBQ3BDLE1BQU0sQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxZQUFXO01BQzNDLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQzs7TUFFL0M7TUFDQSxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixFQUFFLEdBQUcsQ0FBQztJQUN2QyxDQUFDLENBQUM7RUFDSDs7RUFFQTtFQUNBLElBQUksUUFBUSxDQUFDLFVBQVUsS0FBSyxTQUFTLEVBQUU7SUFDdEMsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLElBQUksQ0FBQztFQUNwRCxDQUFDLE1BQU07SUFDTixJQUFJLENBQUMsQ0FBQztFQUNQO0VBRUEsT0FBTztJQUNOLElBQUksRUFBRTtFQUNQLENBQUM7QUFDRixDQUFDLENBQUUsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24gKCkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIFBvbGxpbmcgaW50ZXJ2YWwgZm9yIGNoZWNraW5nIG9uZ29pbmcgdGVzdHMgKGluIG1pbGxpc2Vjb25kcykuXG5cdCAqL1xuXHRjb25zdCBQT0xMSU5HX0lOVEVSVkFMID0gNTAwMDsgLy8gNSBzZWNvbmRzXG5cblx0LyoqXG5cdCAqIEFjdGl2ZSBwb2xsaW5nIGludGVydmFscyBieSBwb3N0IElELlxuXHQgKi9cblx0Y29uc3QgYWN0aXZlUG9sbHMgPSB7fTtcblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZSBSb2NrZXQgSW5zaWdodHMgb24gcG9zdCBsaXN0aW5nIHBhZ2VzXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0KCkge1xuXHRcdC8vIEF0dGFjaCBldmVudCBsaXN0ZW5lcnMuXG5cdFx0YXR0YWNoVGVzdFBhZ2VMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRhdHRhY2hWaWV3RGV0YWlsc0xpc3RlbmVycygpO1xuXG5cdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgYW55IHJvd3MgdGhhdCBhcmUgYWxyZWFkeSBydW5uaW5nLlxuXHRcdHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdGVzdC1wYWdlJywgZnVuY3Rpb24gKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGpRdWVyeSh0aGlzKTtcblx0XHRcdGNvbnN0IHVybCA9IGJ1dHRvbi5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXG5cdFx0XHRjb25zdCBjYW5BZGRQYWdlcyA9IGNvbHVtbi5hdHRyKCdkYXRhLWNhbi1hZGQtcGFnZXMnKSA9PT0gJzEnO1xuXG5cdFx0XHRpZiAoICEgY2FuQWRkUGFnZXMgKSB7XG5cdFx0XHRcdHNob3dMaW1pdE1lc3NhZ2UoIGNvbHVtbiwgYnV0dG9uICk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBdHRhY2ggY2xpY2sgbGlzdGVuZXJzIHRvIFwiUmUtdGVzdFwiIGJ1dHRvbnMgYW5kIGxpbmtzLlxuXHQgKi9cblx0ZnVuY3Rpb24gYXR0YWNoUmV0ZXN0TGlzdGVuZXJzKCkge1xuXHRcdC8vIFN1cHBvcnQgYm90aCBidXR0b24gYW5kIGxpbmsgc3R5bGVzIHdpdGggb25lIGhhbmRsZXIuXG5cdFx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXRlc3Q6bm90KC53cHItcmktYWN0aW9uLS1kaXNhYmxlZCksIC53cHItcmktcmV0ZXN0LWxpbmsnLCBmdW5jdGlvbiAoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblx0XHRcdGNvbnN0IHNvdXJjZSA9IGVsLmRhdGEoJ3NvdXJjZScpIHx8IGNvbHVtbi5kYXRhKCdzb3VyY2UnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFJldGVzdCBzaG91bGQgb25seSBwcm9jZWVkIHdoZW4gdGhlIHVzZXIgaGFzIGNyZWRpdCBmb3IgdGhlIHRlc3QuXG5cdFx0XHRjb25zdCBoYXNDcmVkaXQgPSBjb2x1bW4uYXR0cignZGF0YS1oYXMtY3JlZGl0JykgPT09ICcxJztcblxuXHRcdFx0aWYgKCAhIGhhc0NyZWRpdCApIHtcblx0XHRcdFx0c2hvd0xpbWl0TWVzc2FnZSggY29sdW1uLCBlbCApO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uLCBzb3VyY2UpO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJWaWV3IERldGFpbHNcIiBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFZpZXdEZXRhaWxzTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdmlldy1kZXRhaWxzLWxpbms6bm90KC53cHItcmktZGlzYWJsZWQpJywgZnVuY3Rpb24gKGUpIHtcblx0XHRcdGNvbnN0IGxpbmsgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGxpbmsuZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHRcdGlmICghcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHJvd3MgdGhhdCBhcmUgY3VycmVudGx5IHJ1bm5pbmcgdGVzdHMuXG5cdCAqL1xuXHRmdW5jdGlvbiBzdGFydFBvbGxpbmdGb3JSdW5uaW5nVGVzdHMoKSB7XG5cdFx0alF1ZXJ5KCcud3ByLXJpLWxvYWRpbmcnKS5lYWNoKGZ1bmN0aW9uICgpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZSBpbW1lZGlhdGVseS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdC8vIFNob3cgbG9hZGluZyBzcGlubmVyIGltbWVkaWF0ZWx5IGJlZm9yZSBBUEkgY2FsbFxuXHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCBudWxsKTtcblxuXHRcdC8vIFVzZSBSRVNUIChIRUFEKSBidXQga2VlcCBkZXZlbG9wJ3Mgcm9idXN0IGhhbmRsaW5nLlxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YToge1xuXHRcdFx0XHRwYWdlX3VybDogdXJsLFxuXHRcdFx0XHRzb3VyY2U6ICdwb3N0IHR5cGUgbGlzdGluZydcblx0XHRcdH0sXG5cdFx0fSkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGNvbnN0IHN1Y2Nlc3MgPSByZXNwb25zZT8uc3VjY2VzcyA9PT0gdHJ1ZTtcblx0XHRcdGNvbnN0IGlkID0gcmVzcG9uc2U/LmlkID8/IHJlc3BvbnNlPy5kYXRhPy5pZCA/PyBudWxsO1xuXHRcdFx0Y29uc3QgY2FuQWRkID0gKHJlc3BvbnNlPy5jYW5fYWRkX3BhZ2VzID8/IHJlc3BvbnNlPy5kYXRhPy5jYW5fYWRkX3BhZ2VzKTtcblx0XHRcdGNvbnN0IG1lc3NhZ2UgPSByZXNwb25zZT8ubWVzc2FnZSA/PyByZXNwb25zZT8uZGF0YT8ubWVzc2FnZTtcblxuXHRcdFx0aWYgKHN1Y2Nlc3MgJiYgaWQpIHtcblx0XHRcdFx0Ly8gVXBkYXRlIGNvbHVtbiB3aXRoIHRoZSByb3cgSUQgYW5kIHN0YXJ0IHBvbGxpbmdcblx0XHRcdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgaWQpO1xuXHRcdFx0XHRzdGFydFBvbGxpbmcoaWQsIHVybCwgY29sdW1uKTtcblxuXHRcdFx0XHQvLyBDaGVjayBpZiB3ZSd2ZSByZWFjaGVkIHRoZSBsaW1pdCBhbmQgZGlzYWJsZSBhbGwgb3RoZXIgXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0XHRcdFx0aWYgKGNhbkFkZCA9PT0gZmFsc2UgfHwgcmVzcG9uc2U/LmRhdGE/LnJlbWFpbmluZ191cmxzID09PSAwKSB7XG5cdFx0XHRcdFx0ZGlzYWJsZUFsbFRlc3RQYWdlQnV0dG9ucygpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gSWYgYmFja2VuZCBzYXlzIHdlIGNhbm5vdCBhZGQgcGFnZXMgb3Igb3RoZXIgZXJyb3JzLCByZXN0b3JlIG9yaWdpbmFsIHN0YXRlXG5cdFx0XHQvLyBSZWxvYWQgdGhlIGNvbHVtbiBIVE1MIGZyb20gc2VydmVyIHRvIHJlc3RvcmUgdGhlIGJ1dHRvblxuXHRcdFx0cmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCk7XG5cdFx0fSkuY2F0Y2goKGVycm9yKSA9PiB7XG5cdFx0XHQvLyB3cC5hcGlGZXRjaCB0aHJvd3Mgb24gV1BfRXJyb3I7IHJlbG9hZCBjb2x1bW4gdG8gcmVzdG9yZSBidXR0b25cblx0XHRcdGNvbnNvbGUuZXJyb3IoZXJyb3IpO1xuXHRcdFx0cmVsb2FkQ29sdW1uRnJvbVNlcnZlcihjb2x1bW4sIHVybCk7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogUmV0ZXN0IGFuIGV4aXN0aW5nIHBhZ2UuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBzb3VyY2UgVGhlIHNvdXJjZSBvZiB0aGUgcmVxdWVzdC5cblx0ICovXG5cdGZ1bmN0aW9uIHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uLCBzb3VyY2UpIHtcblx0XHQvLyBTaG93IGxvYWRpbmcgc3Bpbm5lciBpbW1lZGlhdGVseSBiZWZvcmUgQVBJIGNhbGxcblx0XHRzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpO1xuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyArIHJvd0lkLFxuXHRcdFx0XHRtZXRob2Q6ICdQQVRDSCcsXG5cdFx0XHRcdGRhdGE6IHtcblx0XHRcdFx0XHRzb3VyY2U6IHNvdXJjZVxuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0KS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcblx0XHRcdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgcmVzdWx0c1xuXHRcdFx0XHRzdGFydFBvbGxpbmcocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIElmIG5vdCBzdWNjZXNzZnVsLCByZWxvYWQgdGhlIGNvbHVtbiB0byByZXN0b3JlIHByZXZpb3VzIHN0YXRlXG5cdFx0XHRcdHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpO1xuXHRcdFx0fVxuXHRcdH0pLmNhdGNoKChlcnJvcikgPT4ge1xuXHRcdFx0Y29uc29sZS5lcnJvcihlcnJvcik7XG5cdFx0XHQvLyBSZWxvYWQgdGhlIGNvbHVtbiB0byByZXN0b3JlIHByZXZpb3VzIHN0YXRlXG5cdFx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU3RhcnQgcG9sbGluZyBmb3IgdGVzdCByZXN1bHRzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbikge1xuXHRcdC8vIENsZWFyIGFueSBleGlzdGluZyBwb2xsIGZvciB0aGlzIHJvdy5cblx0XHRpZiAoYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRjbGVhckludGVydmFsKGFjdGl2ZVBvbGxzW3Jvd0lkXSk7XG5cdFx0fVxuXG5cdFx0Ly8gU2V0IHVwIG5ldyBwb2xsaW5nIGludGVydmFsLlxuXHRcdGFjdGl2ZVBvbGxzW3Jvd0lkXSA9IHNldEludGVydmFsKGZ1bmN0aW9uICgpIHtcblx0XHRcdGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSwgUE9MTElOR19JTlRFUlZBTCk7XG5cblx0XHQvLyBBbHNvIGNoZWNrIGltbWVkaWF0ZWx5LlxuXHRcdGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyB0aGUgcGVyLXJvdyBsaW1pdCBtZXNzYWdlIChvbmx5IGluIHRoZSBjbGlja2VkIHJvdykuXG5cdCAqIERpc2FibGVzIHRoZSBjbGlja2VkIGVsZW1lbnQgbW9tZW50YXJpbHkgd2hpbGUgc2hvd2luZyB0aGUgbWVzc2FnZS5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjbGlja2VkRWwgVGhlIGVsZW1lbnQgdGhhdCB0cmlnZ2VyZWQgdGhlIGFjdGlvbi5cblx0ICovXG5cdGZ1bmN0aW9uIHNob3dMaW1pdE1lc3NhZ2UoY29sdW1uLCBjbGlja2VkRWwpIHtcblx0XHRjb25zdCBtZXNzYWdlSHRtbCA9IGNvbHVtbi5maW5kKCcud3ByLXJpLWxpbWl0LWh0bWwnKS5odG1sKCkgfHwgd2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5saW1pdF9yZWFjaGVkIHx8ICcnO1xuXG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGNvbHVtbi5maW5kKCcud3ByLXJpLW1lc3NhZ2UnKTtcblx0XHRtZXNzYWdlRGl2Lmh0bWwobWVzc2FnZUh0bWwpLnNob3coKTtcblxuXHRcdC8vIERpc2FibGUgb25seSB0aGUgY2xpY2tlZCBlbGVtZW50IGJyaWVmbHkgdG8gcHJldmVudCBzcGFtIGNsaWNrcywgdGhlbiByZS1lbmFibGUuXG5cdFx0aWYgKGNsaWNrZWRFbCAmJiBjbGlja2VkRWwucHJvcCkge1xuXHRcdFx0Y2xpY2tlZEVsLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSk7XG5cdFx0XHRzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRjbGlja2VkRWwucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG5cdFx0XHR9LCAzMDAwKTtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogQ2hlY2sgdGhlIHN0YXR1cyBvZiBhIHRlc3QuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy9wcm9ncmVzcycsIHsgaWRzOiBbcm93SWRdIH0pLFxuXHRcdFx0fVxuXHRcdCkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIEFycmF5LmlzQXJyYXkocmVzcG9uc2UucmVzdWx0cykpIHtcblx0XHRcdFx0Y29uc3QgcmVzdWx0ID0gcmVzcG9uc2UucmVzdWx0c1swXTtcblxuXHRcdFx0XHRpZiAocmVzdWx0LnN0YXR1cyA9PT0gJ2NvbXBsZXRlZCcgfHwgcmVzdWx0LnN0YXR1cyA9PT0gJ2ZhaWxlZCcpIHtcblx0XHRcdFx0XHQvLyBTdG9wIHBvbGxpbmcuXG5cdFx0XHRcdFx0Y2xlYXJJbnRlcnZhbChhY3RpdmVQb2xsc1tyb3dJZF0pO1xuXHRcdFx0XHRcdGRlbGV0ZSBhY3RpdmVQb2xsc1tyb3dJZF07XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgdGhlIGNvbHVtbiB3aXRoIHJlc3VsdHMgKHJlbG9hZCByZW5kZXJlZCBIVE1MIGZyb20gc2VydmVyKS5cblx0XHRcdFx0XHR1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyhjb2x1bW4sIHJlc3VsdCk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTaG93IGxvYWRpbmcgc3RhdGUgaW4gdGhlIGNvbHVtbi5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRCAoY2FuIGJlIG51bGwgd2hlbiBpbml0aWFsbHkgc2hvd2luZyBsb2FkaW5nKS5cblx0ICovXG5cdGZ1bmN0aW9uIHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCkge1xuXHRcdGlmIChyb3dJZCkge1xuXHRcdFx0Y29sdW1uLmF0dHIoJ2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkJywgcm93SWQpO1xuXHRcdH1cblxuXHRcdC8vIENyZWF0ZSBlbGVtZW50cyBzYWZlbHkgdG8gcHJldmVudCBYU1Ncblx0XHRjb25zdCBsb2FkaW5nRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbG9hZGluZyB3cHItYnRuLXdpdGgtdG9vbC10aXAnKTtcblx0XHRjb25zdCBpbWcgPSBqUXVlcnkoJzxpbWc+JykuYWRkQ2xhc3MoJ3dwci1sb2FkaW5nLWltZycpLmF0dHIoe1xuXHRcdFx0c3JjOiB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmxvYWRpbmdfaW1nIHx8ICcnLFxuXHRcdFx0YWx0OiAnTG9hZGluZy4uLidcblx0XHR9KTtcblx0XHRjb25zdCBtZXNzYWdlRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbWVzc2FnZScpLmNzcygnZGlzcGxheScsICdub25lJyk7XG5cblx0XHRsb2FkaW5nRGl2LmFwcGVuZChpbWcpO1xuXHRcdGxvYWRpbmdEaXYuYXBwZW5kKGA8ZGl2IGNsYXNzPVwid3ByLXRvb2x0aXBcIj48ZGl2IGNsYXNzPVwid3ByLXRvb2x0aXAtY29udGVudFwiPiR7d2luZG93LnJvY2tldF9pbnNpZ2h0c19pMThuPy5lc3RpbWF0ZWRfdGltZV90ZXh0IHx8ICdBbmFseXppbmcgeW91ciBwYWdlICh+MSBtaW4pLid9PC9kaXY+PC9kaXY+YClcblx0XHRjb2x1bW4uZW1wdHkoKS5hcHBlbmQobG9hZGluZ0RpdikuYXBwZW5kKG1lc3NhZ2VEaXYpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFJlbG9hZCBjb2x1bW4gSFRNTCBmcm9tIHNlcnZlci5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBmb3IgdGhlIGNvbHVtbi5cblx0ICovXG5cdGZ1bmN0aW9uIHJlbG9hZENvbHVtbkZyb21TZXJ2ZXIoY29sdW1uLCB1cmwpIHtcblx0XHRjb25zdCBwb3N0SWQgPSBjb2x1bW4uZGF0YSgncG9zdC1pZCcpO1xuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogd2luZG93LndwLnVybC5hZGRRdWVyeUFyZ3MoJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzJywgeyB1cmw6IHVybCwgcG9zdF9pZDogcG9zdElkIH0pLFxuXHRcdFx0fVxuXHRcdCkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIHJlc3BvbnNlLmh0bWwpIHtcblx0XHRcdFx0Y29sdW1uLnJlcGxhY2VXaXRoKHJlc3BvbnNlLmh0bWwpO1xuXG5cdFx0XHRcdC8vIFJlLWF0dGFjaCBsaXN0ZW5lcnMgdG8gdGhlIG5ldyBjb250ZW50LlxuXHRcdFx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdFx0XHRhdHRhY2hSZXRlc3RMaXN0ZW5lcnMoKTtcblx0XHRcdH1cblx0XHR9ICkuY2F0Y2goICggZXJyb3IgKSA9PiB7XG5cdFx0XHRjb25zb2xlLmVycm9yKCdGYWlsZWQgdG8gcmVsb2FkIGNvbHVtbjonLCBlcnJvcik7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZSBjb2x1bW4gd2l0aCB0ZXN0IHJlc3VsdHMuXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge09iamVjdH0gcmVzdWx0IFRoZSB0ZXN0IHJlc3VsdCBkYXRhLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoY29sdW1uLCByZXN1bHQpIHtcblx0XHQvLyBSZWxvYWQgdGhlIGVudGlyZSByb3cgZnJvbSB0aGUgc2VydmVyIHRvIGdldCBwcm9wZXJseSByZW5kZXJlZCBIVE1MLlxuXHRcdGNvbnN0IHVybCA9IGNvbHVtbi5kYXRhKCd1cmwnKTtcblx0XHRyZWxvYWRDb2x1bW5Gcm9tU2VydmVyKGNvbHVtbiwgdXJsKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBNYXJrIGFsbCByZW1haW5pbmcgXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucyBhcyBoYXZpbmcgcmVhY2hlZCB0aGUgbGltaXQuXG5cdCAqIFVwZGF0ZXMgZGF0YSBhdHRyaWJ1dGVzIHNvIGZ1dHVyZSBjbGlja3Mgd2lsbCBzaG93IHRoZSBsaW1pdCBtZXNzYWdlIHBlci1yb3cuXG5cdCAqIERvZXMgTk9UIGRpc3BsYXkgYW55IG1lc3NhZ2UgaW1tZWRpYXRlbHkgb24gYWxsIHJvd3MuXG5cdCAqL1xuXHRmdW5jdGlvbiBkaXNhYmxlQWxsVGVzdFBhZ2VCdXR0b25zKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS10ZXN0LXBhZ2UnKS5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cblx0XHRcdC8vIFVwZGF0ZSB0aGUgZGF0YSBhdHRyaWJ1dGUgc28gZnV0dXJlIGNsaWNrcyB3aWxsIHRyaWdnZXIgdGhlIGxpbWl0IG1lc3NhZ2UuXG5cdFx0XHRjb2x1bW4uYXR0cignZGF0YS1jYW4tYWRkLXBhZ2VzJywgJzAnKTtcblx0XHR9KTtcblx0fVxuXG5cdC8vIEF1dG8taW5pdGlhbGl6ZSBvbiBET00gcmVhZHlcblx0aWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdsb2FkaW5nJykge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBpbml0KTtcblx0fSBlbHNlIHtcblx0XHRpbml0KCk7XG5cdH1cblxuXHRyZXR1cm4ge1xuXHRcdGluaXQ6IGluaXRcblx0fTtcbn0pKCk7XG4iXX0=
