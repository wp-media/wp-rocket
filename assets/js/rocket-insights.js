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

        // Display error message
        if (message) {
          messageDiv.addClass('wpr-ri-error').html(message).show();
        }
        return;
      }

      // Other errors
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');

      // Display error message if available
      if (message) {
        messageDiv.addClass('wpr-ri-error').html(message).show();
      }
    }).catch(error => {
      // wp.apiFetch throws on WP_Error; try to surface a helpful message.
      console.error(error);
      button.prop('disabled', false).text(window.rocket_insights_i18n?.test_page || 'Test the page');

      // Display error message
      const errorMessage = error?.message || 'An error occurred. Please try again.';
      messageDiv.addClass('wpr-ri-error').html(errorMessage).show();
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
        // Display error message if available
        const message = response?.message ?? response?.data?.message;
        if (message) {
          messageDiv.addClass('wpr-ri-error').html(message).show();
        }
      }
    }).catch(error => {
      console.error(error);

      // Display error message
      const errorMessage = error?.message || 'An error occurred. Please try again.';
      messageDiv.addClass('wpr-ri-error').html(errorMessage).show();
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldC1pbnNpZ2h0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0EsTUFBTSxDQUFDLE9BQU8sR0FBSSxZQUFXO0VBQzVCLFlBQVk7O0VBRVo7QUFDRDtBQUNBO0VBQ0MsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsQ0FBQzs7RUFFL0I7QUFDRDtBQUNBO0VBQ0MsTUFBTSxXQUFXLEdBQUcsQ0FBQyxDQUFDOztFQUV0QjtBQUNEO0FBQ0E7RUFDQyxTQUFTLElBQUksQ0FBQSxFQUFHO0lBQ2Y7SUFDQSx1QkFBdUIsQ0FBQyxDQUFDO0lBQ3pCLHFCQUFxQixDQUFDLENBQUM7O0lBRXZCO0lBQ0EsMkJBQTJCLENBQUMsQ0FBQztFQUM5Qjs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFBLEVBQUc7SUFDbEMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUJBQW1CLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0QsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDM0IsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFDOUIsTUFBTSxNQUFNLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUUvQyxVQUFVLENBQUMsR0FBRyxFQUFFLE1BQU0sRUFBRSxNQUFNLENBQUM7SUFDaEMsQ0FBQyxDQUFDO0VBQ0g7O0VBRUE7QUFDRDtBQUNBO0VBQ0MsU0FBUyxxQkFBcUIsQ0FBQSxFQUFHO0lBQ2hDO0lBQ0EsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsbUVBQW1FLEVBQUUsVUFBUyxDQUFDLEVBQUU7TUFDN0csQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO01BQ2xCLE1BQU0sRUFBRSxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUM7TUFDdkIsTUFBTSxHQUFHLEdBQUcsRUFBRSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFDMUIsTUFBTSxNQUFNLEdBQUcsRUFBRSxDQUFDLE9BQU8sQ0FBQyxnQkFBZ0IsQ0FBQztNQUMzQyxNQUFNLEtBQUssR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDO01BRS9DLElBQUksQ0FBQyxLQUFLLEVBQUU7UUFDWDtNQUNEO01BRUEsVUFBVSxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsTUFBTSxDQUFDO0lBQy9CLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMsMkJBQTJCLENBQUEsRUFBRztJQUN0QyxNQUFNLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxJQUFJLENBQUMsWUFBVztNQUN6QyxNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsT0FBTyxDQUFDLGdCQUFnQixDQUFDO01BQ3JELE1BQU0sS0FBSyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsb0JBQW9CLENBQUM7TUFDL0MsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7TUFFOUIsSUFBSSxLQUFLLElBQUksQ0FBQyxXQUFXLENBQUMsS0FBSyxDQUFDLEVBQUU7UUFDakMsWUFBWSxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsTUFBTSxDQUFDO01BQ2pDO0lBQ0QsQ0FBQyxDQUFDO0VBQ0g7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLFVBQVUsQ0FBQyxHQUFHLEVBQUUsTUFBTSxFQUFFLE1BQU0sRUFBRTtJQUN4QztJQUNBLE1BQU0sQ0FBQyxJQUFJLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQzs7SUFFN0I7SUFDQSxNQUFNLFVBQVUsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDO0lBQ2pELFVBQVUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxjQUFjLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQzs7SUFFckQ7SUFDQSxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBQztNQUNsQixJQUFJLEVBQUUsc0NBQXNDO01BQzVDLE1BQU0sRUFBRSxNQUFNO01BQ2QsSUFBSSxFQUFFO1FBQUUsUUFBUSxFQUFFO01BQUk7SUFDdkIsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFFLFFBQVEsSUFBSztNQUNyQixNQUFNLE9BQU8sR0FBSyxRQUFRLEVBQUUsT0FBTyxLQUFLLElBQUk7TUFDNUMsTUFBTSxFQUFFLEdBQVUsUUFBUSxFQUFFLEVBQUUsSUFBSSxRQUFRLEVBQUUsSUFBSSxFQUFFLEVBQUUsSUFBSSxJQUFJO01BQzVELE1BQU0sTUFBTSxHQUFPLFFBQVEsRUFBRSxhQUFhLElBQUksUUFBUSxFQUFFLElBQUksRUFBRSxhQUFjO01BQzVFLE1BQU0sT0FBTyxHQUFLLFFBQVEsRUFBRSxPQUFPLElBQUksUUFBUSxFQUFFLElBQUksRUFBRSxPQUFPO01BRTlELElBQUksT0FBTyxJQUFJLEVBQUUsRUFBRTtRQUNsQjtRQUNBLG1CQUFtQixDQUFDLE1BQU0sRUFBRSxFQUFFLEVBQUUsR0FBRyxDQUFDO1FBQ3BDO01BQ0Q7O01BRUE7TUFDQSxJQUFJLE1BQU0sS0FBSyxLQUFLLEVBQUU7UUFDckIsTUFBTSxDQUFDLElBQUksQ0FBQyxVQUFVLEVBQUUsS0FBSyxDQUFDLENBQzVCLElBQUksQ0FBQyxNQUFNLENBQUMsb0JBQW9CLEVBQUUsU0FBUyxJQUFJLGVBQWUsQ0FBQzs7UUFFakU7UUFDQSxJQUFJLE9BQU8sRUFBRTtVQUNaLFVBQVUsQ0FBQyxRQUFRLENBQUMsY0FBYyxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQ3pEO1FBQ0E7TUFDRDs7TUFFQTtNQUNBLE1BQU0sQ0FBQyxJQUFJLENBQUMsVUFBVSxFQUFFLEtBQUssQ0FBQyxDQUM1QixJQUFJLENBQUMsTUFBTSxDQUFDLG9CQUFvQixFQUFFLFNBQVMsSUFBSSxlQUFlLENBQUM7O01BRWpFO01BQ0EsSUFBSSxPQUFPLEVBQUU7UUFDWixVQUFVLENBQUMsUUFBUSxDQUFDLGNBQWMsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztNQUN6RDtJQUNELENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBRSxLQUFLLElBQUs7TUFDbkI7TUFDQSxPQUFPLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQztNQUNwQixNQUFNLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxLQUFLLENBQUMsQ0FDNUIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxvQkFBb0IsRUFBRSxTQUFTLElBQUksZUFBZSxDQUFDOztNQUVqRTtNQUNBLE1BQU0sWUFBWSxHQUFHLEtBQUssRUFBRSxPQUFPLElBQUksc0NBQXNDO01BQzdFLFVBQVUsQ0FBQyxRQUFRLENBQUMsY0FBYyxDQUFDLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQzlELENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxVQUFVLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxNQUFNLEVBQUU7SUFDdkM7SUFDQSxNQUFNLFVBQVUsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDO0lBQ2pELFVBQVUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxjQUFjLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztJQUVyRCxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FDakI7TUFDQyxJQUFJLEVBQUUsc0NBQXNDLEdBQUcsS0FBSztNQUNwRCxNQUFNLEVBQUU7SUFDVCxDQUNELENBQUMsQ0FBQyxJQUFJLENBQUksUUFBUSxJQUFNO01BQ3ZCLElBQUksUUFBUSxDQUFDLE9BQU8sRUFBRTtRQUNyQjtRQUNBLG1CQUFtQixDQUFDLE1BQU0sRUFBRSxLQUFLLEVBQUUsR0FBRyxDQUFDO01BQ3hDLENBQUMsTUFBTTtRQUNOO1FBQ0EsTUFBTSxPQUFPLEdBQUcsUUFBUSxFQUFFLE9BQU8sSUFBSSxRQUFRLEVBQUUsSUFBSSxFQUFFLE9BQU87UUFDNUQsSUFBSSxPQUFPLEVBQUU7VUFDWixVQUFVLENBQUMsUUFBUSxDQUFDLGNBQWMsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUN6RDtNQUNEO0lBQ0QsQ0FBRSxDQUFDLENBQUMsS0FBSyxDQUFJLEtBQUssSUFBTTtNQUN2QixPQUFPLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQzs7TUFFcEI7TUFDQSxNQUFNLFlBQVksR0FBRyxLQUFLLEVBQUUsT0FBTyxJQUFJLHNDQUFzQztNQUM3RSxVQUFVLENBQUMsUUFBUSxDQUFDLGNBQWMsQ0FBQyxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUM5RCxDQUFFLENBQUM7RUFDSjs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsWUFBWSxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsTUFBTSxFQUFFO0lBQ3pDO0lBQ0EsSUFBSSxXQUFXLENBQUMsS0FBSyxDQUFDLEVBQUU7TUFDdkIsYUFBYSxDQUFDLFdBQVcsQ0FBQyxLQUFLLENBQUMsQ0FBQztJQUNsQzs7SUFFQTtJQUNBLFdBQVcsQ0FBQyxLQUFLLENBQUMsR0FBRyxXQUFXLENBQUMsWUFBVztNQUMzQyxXQUFXLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxNQUFNLENBQUM7SUFDaEMsQ0FBQyxFQUFFLGdCQUFnQixDQUFDOztJQUVwQjtJQUNBLFdBQVcsQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLE1BQU0sQ0FBQztFQUNoQzs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsbUJBQW1CLENBQUMsTUFBTSxFQUFFLEtBQUssRUFBRSxHQUFHLEVBQUU7SUFDaEQ7SUFDQSxnQkFBZ0IsQ0FBQyxNQUFNLEVBQUUsS0FBSyxDQUFDO0lBQy9CLFlBQVksQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLE1BQU0sQ0FBQztFQUNqQzs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsV0FBVyxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsTUFBTSxFQUFFO0lBQ3hDLE1BQU0sQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUNqQjtNQUNDLElBQUksRUFBRSxNQUFNLENBQUMsRUFBRSxDQUFDLEdBQUcsQ0FBQyxZQUFZLENBQUUsOENBQThDLEVBQUU7UUFBRSxHQUFHLEVBQUUsQ0FBQyxLQUFLO01BQUUsQ0FBRTtJQUNwRyxDQUNELENBQUMsQ0FBQyxJQUFJLENBQUksUUFBUSxJQUFNO01BQ3ZCLElBQUssUUFBUSxDQUFDLE9BQU8sSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFFLFFBQVEsQ0FBQyxPQUFRLENBQUMsRUFBRztRQUM1RCxNQUFNLE1BQU0sR0FBRyxRQUFRLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQztRQUVsQyxJQUFLLE1BQU0sQ0FBQyxNQUFNLEtBQUssV0FBVyxJQUFJLE1BQU0sQ0FBQyxNQUFNLEtBQUssUUFBUSxFQUFHO1VBQ2xFO1VBQ0EsYUFBYSxDQUFFLFdBQVcsQ0FBQyxLQUFLLENBQUUsQ0FBQztVQUNuQyxPQUFPLFdBQVcsQ0FBQyxLQUFLLENBQUM7O1VBRXpCO1VBQ0EsdUJBQXVCLENBQUUsTUFBTSxFQUFFLE1BQU8sQ0FBQztRQUMxQztNQUNEO0lBQ0QsQ0FBRSxDQUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxnQkFBZ0IsQ0FBQyxNQUFNLEVBQUUsS0FBSyxFQUFFO0lBQ3hDLE1BQU0sQ0FBQyxJQUFJLENBQUMseUJBQXlCLEVBQUUsS0FBSyxDQUFDOztJQUU3QztJQUNBLE1BQU0sVUFBVSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsQ0FBQyxRQUFRLENBQUMsZ0JBQWdCLENBQUM7SUFDN0QsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLElBQUksQ0FBQztNQUM1RCxHQUFHLEVBQUUsTUFBTSxDQUFDLG9CQUFvQixFQUFFLFdBQVcsSUFBSSxFQUFFO01BQ25ELEdBQUcsRUFBRTtJQUNOLENBQUMsQ0FBQztJQUNGLE1BQU0sVUFBVSxHQUFHLE1BQU0sQ0FBQyxPQUFPLENBQUMsQ0FBQyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxHQUFHLENBQUMsU0FBUyxFQUFFLE1BQU0sQ0FBQztJQUVwRixVQUFVLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQztJQUN0QixNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsVUFBVSxDQUFDLENBQUMsTUFBTSxDQUFDLFVBQVUsQ0FBQztFQUNyRDs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLHVCQUF1QixDQUFDLE1BQU0sRUFBRSxNQUFNLEVBQUU7SUFDaEQ7SUFDQSxNQUFNLEdBQUcsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztJQUU5QixNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FDakI7TUFDQyxJQUFJLEVBQUUsTUFBTSxDQUFDLEVBQUUsQ0FBQyxHQUFHLENBQUMsWUFBWSxDQUFFLHFDQUFxQyxFQUFFO1FBQUUsR0FBRyxFQUFFO01BQUksQ0FBRTtJQUN2RixDQUNELENBQUMsQ0FBQyxJQUFJLENBQUksUUFBUSxJQUFNO01BQ3ZCLElBQUksUUFBUSxDQUFDLE9BQU8sSUFBSSxRQUFRLENBQUMsSUFBSSxFQUFFO1FBQ3RDLE1BQU0sQ0FBQyxXQUFXLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQzs7UUFFakM7UUFDQSx1QkFBdUIsQ0FBQyxDQUFDO1FBQ3pCLHFCQUFxQixDQUFDLENBQUM7TUFDeEI7SUFDRCxDQUFFLENBQUM7RUFDSjs7RUFFQTtFQUNBLElBQUksUUFBUSxDQUFDLFVBQVUsS0FBSyxTQUFTLEVBQUU7SUFDdEMsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLElBQUksQ0FBQztFQUNwRCxDQUFDLE1BQU07SUFDTixJQUFJLENBQUMsQ0FBQztFQUNQO0VBRUEsT0FBTztJQUNOLElBQUksRUFBRTtFQUNQLENBQUM7QUFDRixDQUFDLENBQUUsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIi8qKlxuICogUm9ja2V0IEluc2lnaHRzIGZ1bmN0aW9uYWxpdHkgZm9yIHBvc3QgbGlzdGluZyBwYWdlc1xuICogVGhpcyBzY3JpcHQgaGFuZGxlcyBwZXJmb3JtYW5jZSBzY29yZSBkaXNwbGF5IGFuZCB1cGRhdGVzIGluIGFkbWluIHBvc3QgbGlzdGluZyBwYWdlc1xuICpcbiAqIEBzaW5jZSAzLjIwLjFcbiAqL1xuXG4vLyBFeHBvcnQgZm9yIHVzZSB3aXRoIGJyb3dzZXJpZnkvYmFiZWxpZnkgaW4gZ3VscFxubW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHQvKipcblx0ICogUG9sbGluZyBpbnRlcnZhbCBmb3IgY2hlY2tpbmcgb25nb2luZyB0ZXN0cyAoaW4gbWlsbGlzZWNvbmRzKS5cblx0ICovXG5cdGNvbnN0IFBPTExJTkdfSU5URVJWQUwgPSA1MDAwOyAvLyA1IHNlY29uZHNcblxuXHQvKipcblx0ICogQWN0aXZlIHBvbGxpbmcgaW50ZXJ2YWxzIGJ5IHBvc3QgSUQuXG5cdCAqL1xuXHRjb25zdCBhY3RpdmVQb2xscyA9IHt9O1xuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplIFJvY2tldCBJbnNpZ2h0cyBvbiBwb3N0IGxpc3RpbmcgcGFnZXNcblx0ICovXG5cdGZ1bmN0aW9uIGluaXQoKSB7XG5cdFx0Ly8gQXR0YWNoIGV2ZW50IGxpc3RlbmVycy5cblx0XHRhdHRhY2hUZXN0UGFnZUxpc3RlbmVycygpO1xuXHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXG5cdFx0Ly8gU3RhcnQgcG9sbGluZyBmb3IgYW55IHJvd3MgdGhhdCBhcmUgYWxyZWFkeSBydW5uaW5nLlxuXHRcdHN0YXJ0UG9sbGluZ0ZvclJ1bm5pbmdUZXN0cygpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEF0dGFjaCBjbGljayBsaXN0ZW5lcnMgdG8gXCJUZXN0IHRoZSBwYWdlXCIgYnV0dG9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCkge1xuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktdGVzdC1wYWdlJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgYnV0dG9uID0galF1ZXJ5KHRoaXMpO1xuXHRcdFx0Y29uc3QgdXJsID0gYnV0dG9uLmRhdGEoJ3VybCcpO1xuXHRcdFx0Y29uc3QgY29sdW1uID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cblx0XHRcdGFkZE5ld1BhZ2UodXJsLCBjb2x1bW4sIGJ1dHRvbik7XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogQXR0YWNoIGNsaWNrIGxpc3RlbmVycyB0byBcIlJlLXRlc3RcIiBidXR0b25zIGFuZCBsaW5rcy5cblx0ICovXG5cdGZ1bmN0aW9uIGF0dGFjaFJldGVzdExpc3RlbmVycygpIHtcblx0XHQvLyBTdXBwb3J0IGJvdGggYnV0dG9uIGFuZCBsaW5rIHN0eWxlcyB3aXRoIG9uZSBoYW5kbGVyLlxuXHRcdGpRdWVyeShkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmV0ZXN0Om5vdCgud3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQpLCAud3ByLXJpLXJldGVzdC1saW5rJywgZnVuY3Rpb24oZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgZWwgPSBqUXVlcnkodGhpcyk7XG5cdFx0XHRjb25zdCB1cmwgPSBlbC5kYXRhKCd1cmwnKTtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGVsLmNsb3Nlc3QoJy53cHItcmktY29sdW1uJyk7XG5cdFx0XHRjb25zdCByb3dJZCA9IGNvbHVtbi5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdFx0aWYgKCFyb3dJZCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHJldGVzdFBhZ2Uocm93SWQsIHVybCwgY29sdW1uKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTdGFydCBwb2xsaW5nIGZvciByb3dzIHRoYXQgYXJlIGN1cnJlbnRseSBydW5uaW5nIHRlc3RzLlxuXHQgKi9cblx0ZnVuY3Rpb24gc3RhcnRQb2xsaW5nRm9yUnVubmluZ1Rlc3RzKCkge1xuXHRcdGpRdWVyeSgnLndwci1yaS1sb2FkaW5nJykuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGNvbHVtbiA9IGpRdWVyeSh0aGlzKS5jbG9zZXN0KCcud3ByLXJpLWNvbHVtbicpO1xuXHRcdFx0Y29uc3Qgcm93SWQgPSBjb2x1bW4uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0XHRjb25zdCB1cmwgPSBjb2x1bW4uZGF0YSgndXJsJyk7XG5cblx0XHRcdGlmIChyb3dJZCAmJiAhYWN0aXZlUG9sbHNbcm93SWRdKSB7XG5cdFx0XHRcdHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZCBhIG5ldyBwYWdlIGZvciB0ZXN0aW5nLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgdG8gdGVzdC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBidXR0b24gVGhlIGJ1dHRvbiB0aGF0IHdhcyBjbGlja2VkLlxuXHQgKi9cblx0ZnVuY3Rpb24gYWRkTmV3UGFnZSh1cmwsIGNvbHVtbiwgYnV0dG9uKSB7XG5cdFx0Ly8gRGlzYWJsZSBidXR0b24gYW5kIHNob3cgbG9hZGluZyBzdGF0ZS5cblx0XHRidXR0b24ucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdC8vIEhpZGUgYW55IHByZXZpb3VzIG1lc3NhZ2VzXG5cdFx0Y29uc3QgbWVzc2FnZURpdiA9IGNvbHVtbi5maW5kKCcud3ByLXJpLW1lc3NhZ2UnKTtcblx0XHRtZXNzYWdlRGl2LmhpZGUoKS5yZW1vdmVDbGFzcygnd3ByLXJpLWVycm9yJykuZW1wdHkoKTtcblxuXHRcdC8vIFVzZSBSRVNUIChIRUFEKSBidXQga2VlcCBkZXZlbG9wJ3Mgcm9idXN0IGhhbmRsaW5nLlxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YTogeyBwYWdlX3VybDogdXJsIH0sXG5cdFx0fSkudGhlbigocmVzcG9uc2UpID0+IHtcblx0XHRcdGNvbnN0IHN1Y2Nlc3MgICA9IHJlc3BvbnNlPy5zdWNjZXNzID09PSB0cnVlO1xuXHRcdFx0Y29uc3QgaWQgICAgICAgID0gcmVzcG9uc2U/LmlkID8/IHJlc3BvbnNlPy5kYXRhPy5pZCA/PyBudWxsO1xuXHRcdFx0Y29uc3QgY2FuQWRkICAgID0gKHJlc3BvbnNlPy5jYW5fYWRkX3BhZ2VzID8/IHJlc3BvbnNlPy5kYXRhPy5jYW5fYWRkX3BhZ2VzKTtcblx0XHRcdGNvbnN0IG1lc3NhZ2UgICA9IHJlc3BvbnNlPy5tZXNzYWdlID8/IHJlc3BvbnNlPy5kYXRhPy5tZXNzYWdlO1xuXG5cdFx0XHRpZiAoc3VjY2VzcyAmJiBpZCkge1xuXHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0YmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIGlkLCB1cmwpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIElmIGJhY2tlbmQgc2F5cyB3ZSBjYW5ub3QgYWRkIHBhZ2VzLCBzaG93IGVycm9yIG1lc3NhZ2UgaW4gdGhlIGNvbHVtbi5cblx0XHRcdGlmIChjYW5BZGQgPT09IGZhbHNlKSB7XG5cdFx0XHRcdGJ1dHRvbi5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKVxuXHRcdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHRcdFxuXHRcdFx0XHQvLyBEaXNwbGF5IGVycm9yIG1lc3NhZ2Vcblx0XHRcdFx0aWYgKG1lc3NhZ2UpIHtcblx0XHRcdFx0XHRtZXNzYWdlRGl2LmFkZENsYXNzKCd3cHItcmktZXJyb3InKS5odG1sKG1lc3NhZ2UpLnNob3coKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIE90aGVyIGVycm9yc1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHRcblx0XHRcdC8vIERpc3BsYXkgZXJyb3IgbWVzc2FnZSBpZiBhdmFpbGFibGVcblx0XHRcdGlmIChtZXNzYWdlKSB7XG5cdFx0XHRcdG1lc3NhZ2VEaXYuYWRkQ2xhc3MoJ3dwci1yaS1lcnJvcicpLmh0bWwobWVzc2FnZSkuc2hvdygpO1xuXHRcdFx0fVxuXHRcdH0pLmNhdGNoKChlcnJvcikgPT4ge1xuXHRcdFx0Ly8gd3AuYXBpRmV0Y2ggdGhyb3dzIG9uIFdQX0Vycm9yOyB0cnkgdG8gc3VyZmFjZSBhIGhlbHBmdWwgbWVzc2FnZS5cblx0XHRcdGNvbnNvbGUuZXJyb3IoZXJyb3IpO1xuXHRcdFx0YnV0dG9uLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpXG5cdFx0XHRcdC50ZXh0KHdpbmRvdy5yb2NrZXRfaW5zaWdodHNfaTE4bj8udGVzdF9wYWdlIHx8ICdUZXN0IHRoZSBwYWdlJyk7XG5cdFx0XHRcblx0XHRcdC8vIERpc3BsYXkgZXJyb3IgbWVzc2FnZVxuXHRcdFx0Y29uc3QgZXJyb3JNZXNzYWdlID0gZXJyb3I/Lm1lc3NhZ2UgfHwgJ0FuIGVycm9yIG9jY3VycmVkLiBQbGVhc2UgdHJ5IGFnYWluLic7XG5cdFx0XHRtZXNzYWdlRGl2LmFkZENsYXNzKCd3cHItcmktZXJyb3InKS5odG1sKGVycm9yTWVzc2FnZSkuc2hvdygpO1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFJldGVzdCBhbiBleGlzdGluZyBwYWdlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSB1cmwgICAgVGhlIFVSTCBiZWluZyB0ZXN0ZWQuXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKi9cblx0ZnVuY3Rpb24gcmV0ZXN0UGFnZShyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHQvLyBIaWRlIGFueSBwcmV2aW91cyBtZXNzYWdlc1xuXHRcdGNvbnN0IG1lc3NhZ2VEaXYgPSBjb2x1bW4uZmluZCgnLndwci1yaS1tZXNzYWdlJyk7XG5cdFx0bWVzc2FnZURpdi5oaWRlKCkucmVtb3ZlQ2xhc3MoJ3dwci1yaS1lcnJvcicpLmVtcHR5KCk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nICsgcm93SWQsXG5cdFx0XHRcdG1ldGhvZDogJ1BBVENIJyxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHQvLyBCZWdpbiBjb21tb24gbG9hZGluZyArIHBvbGxpbmcgZmxvdy5cblx0XHRcdFx0YmVnaW5Mb2FkaW5nQW5kUG9sbChjb2x1bW4sIHJvd0lkLCB1cmwpO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Ly8gRGlzcGxheSBlcnJvciBtZXNzYWdlIGlmIGF2YWlsYWJsZVxuXHRcdFx0XHRjb25zdCBtZXNzYWdlID0gcmVzcG9uc2U/Lm1lc3NhZ2UgPz8gcmVzcG9uc2U/LmRhdGE/Lm1lc3NhZ2U7XG5cdFx0XHRcdGlmIChtZXNzYWdlKSB7XG5cdFx0XHRcdFx0bWVzc2FnZURpdi5hZGRDbGFzcygnd3ByLXJpLWVycm9yJykuaHRtbChtZXNzYWdlKS5zaG93KCk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9ICkuY2F0Y2goICggZXJyb3IgKSA9PiB7XG5cdFx0XHRjb25zb2xlLmVycm9yKGVycm9yKTtcblxuXHRcdFx0Ly8gRGlzcGxheSBlcnJvciBtZXNzYWdlXG5cdFx0XHRjb25zdCBlcnJvck1lc3NhZ2UgPSBlcnJvcj8ubWVzc2FnZSB8fCAnQW4gZXJyb3Igb2NjdXJyZWQuIFBsZWFzZSB0cnkgYWdhaW4uJztcblx0XHRcdG1lc3NhZ2VEaXYuYWRkQ2xhc3MoJ3dwci1yaS1lcnJvcicpLmh0bWwoZXJyb3JNZXNzYWdlKS5zaG93KCk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFN0YXJ0IHBvbGxpbmcgZm9yIHRlc3QgcmVzdWx0cy5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IHJvd0lkICBUaGUgZGF0YWJhc2Ugcm93IElELlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gdXJsICAgIFRoZSBVUkwgYmVpbmcgdGVzdGVkLlxuXHQgKiBAcGFyYW0ge2pRdWVyeX0gY29sdW1uIFRoZSBjb2x1bW4gZWxlbWVudC5cblx0ICovXG5cdGZ1bmN0aW9uIHN0YXJ0UG9sbGluZyhyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHQvLyBDbGVhciBhbnkgZXhpc3RpbmcgcG9sbCBmb3IgdGhpcyByb3cuXG5cdFx0aWYgKGFjdGl2ZVBvbGxzW3Jvd0lkXSkge1xuXHRcdFx0Y2xlYXJJbnRlcnZhbChhY3RpdmVQb2xsc1tyb3dJZF0pO1xuXHRcdH1cblxuXHRcdC8vIFNldCB1cCBuZXcgcG9sbGluZyBpbnRlcnZhbC5cblx0XHRhY3RpdmVQb2xsc1tyb3dJZF0gPSBzZXRJbnRlcnZhbChmdW5jdGlvbigpIHtcblx0XHRcdGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdFx0fSwgUE9MTElOR19JTlRFUlZBTCk7XG5cblx0XHQvLyBBbHNvIGNoZWNrIGltbWVkaWF0ZWx5LlxuXHRcdGNoZWNrU3RhdHVzKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdH1cblxuXHQvKipcblx0ICogQ29tbW9uIGhlbHBlciB0byBzZXQgbG9hZGluZyBzdGF0ZSBhbmQgc3RhcnQgcG9sbGluZy5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICovXG5cdGZ1bmN0aW9uIGJlZ2luTG9hZGluZ0FuZFBvbGwoY29sdW1uLCByb3dJZCwgdXJsKSB7XG5cdFx0Ly8gVXBkYXRlIGNvbHVtbiB0byBsb2FkaW5nIHN0YXRlIGFuZCBzdGFydCBwb2xsaW5nLlxuXHRcdHNob3dMb2FkaW5nU3RhdGUoY29sdW1uLCByb3dJZCk7XG5cdFx0c3RhcnRQb2xsaW5nKHJvd0lkLCB1cmwsIGNvbHVtbik7XG5cdH1cblxuXHQvKipcblx0ICogQ2hlY2sgdGhlIHN0YXR1cyBvZiBhIHRlc3QuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSByb3dJZCAgVGhlIGRhdGFiYXNlIHJvdyBJRC5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHVybCAgICBUaGUgVVJMIGJlaW5nIHRlc3RlZC5cblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBjaGVja1N0YXR1cyhyb3dJZCwgdXJsLCBjb2x1bW4pIHtcblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvcHJvZ3Jlc3MnLCB7IGlkczogW3Jvd0lkXSB9ICksXG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICYmIEFycmF5LmlzQXJyYXkoIHJlc3BvbnNlLnJlc3VsdHMgKSApIHtcblx0XHRcdFx0Y29uc3QgcmVzdWx0ID0gcmVzcG9uc2UucmVzdWx0c1swXTtcblxuXHRcdFx0XHRpZiAoIHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnICkge1xuXHRcdFx0XHRcdC8vIFN0b3AgcG9sbGluZy5cblx0XHRcdFx0XHRjbGVhckludGVydmFsKCBhY3RpdmVQb2xsc1tyb3dJZF0gKTtcblx0XHRcdFx0XHRkZWxldGUgYWN0aXZlUG9sbHNbcm93SWRdO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBjb2x1bW4gd2l0aCByZXN1bHRzIChyZWxvYWQgcmVuZGVyZWQgSFRNTCBmcm9tIHNlcnZlcikuXG5cdFx0XHRcdFx0dXBkYXRlQ29sdW1uV2l0aFJlc3VsdHMoIGNvbHVtbiwgcmVzdWx0ICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU2hvdyBsb2FkaW5nIHN0YXRlIGluIHRoZSBjb2x1bW4uXG5cdCAqXG5cdCAqIEBwYXJhbSB7alF1ZXJ5fSBjb2x1bW4gVGhlIGNvbHVtbiBlbGVtZW50LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gcm93SWQgIFRoZSBkYXRhYmFzZSByb3cgSUQuXG5cdCAqL1xuXHRmdW5jdGlvbiBzaG93TG9hZGluZ1N0YXRlKGNvbHVtbiwgcm93SWQpIHtcblx0XHRjb2x1bW4uYXR0cignZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQnLCByb3dJZCk7XG5cblx0XHQvLyBDcmVhdGUgZWxlbWVudHMgc2FmZWx5IHRvIHByZXZlbnQgWFNTXG5cdFx0Y29uc3QgbG9hZGluZ0RpdiA9IGpRdWVyeSgnPGRpdj4nKS5hZGRDbGFzcygnd3ByLXJpLWxvYWRpbmcnKTtcblx0XHRjb25zdCBpbWcgPSBqUXVlcnkoJzxpbWc+JykuYWRkQ2xhc3MoJ3dwci1sb2FkaW5nLWltZycpLmF0dHIoe1xuXHRcdFx0c3JjOiB3aW5kb3cucm9ja2V0X2luc2lnaHRzX2kxOG4/LmxvYWRpbmdfaW1nIHx8ICcnLFxuXHRcdFx0YWx0OiAnTG9hZGluZy4uLidcblx0XHR9KTtcblx0XHRjb25zdCBtZXNzYWdlRGl2ID0galF1ZXJ5KCc8ZGl2PicpLmFkZENsYXNzKCd3cHItcmktbWVzc2FnZScpLmNzcygnZGlzcGxheScsICdub25lJyk7XG5cblx0XHRsb2FkaW5nRGl2LmFwcGVuZChpbWcpO1xuXHRcdGNvbHVtbi5lbXB0eSgpLmFwcGVuZChsb2FkaW5nRGl2KS5hcHBlbmQobWVzc2FnZURpdik7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlIGNvbHVtbiB3aXRoIHRlc3QgcmVzdWx0cy5cblx0ICpcblx0ICogQHBhcmFtIHtqUXVlcnl9IGNvbHVtbiBUaGUgY29sdW1uIGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSByZXN1bHQgVGhlIHRlc3QgcmVzdWx0IGRhdGEuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVDb2x1bW5XaXRoUmVzdWx0cyhjb2x1bW4sIHJlc3VsdCkge1xuXHRcdC8vIFJlbG9hZCB0aGUgZW50aXJlIHJvdyBmcm9tIHRoZSBzZXJ2ZXIgdG8gZ2V0IHByb3Blcmx5IHJlbmRlcmVkIEhUTUwuXG5cdFx0Y29uc3QgdXJsID0gY29sdW1uLmRhdGEoJ3VybCcpO1xuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzJywgeyB1cmw6IHVybCB9ICksXG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgJiYgcmVzcG9uc2UuaHRtbCkge1xuXHRcdFx0XHRjb2x1bW4ucmVwbGFjZVdpdGgocmVzcG9uc2UuaHRtbCk7XG5cblx0XHRcdFx0Ly8gUmUtYXR0YWNoIGxpc3RlbmVycyB0byB0aGUgbmV3IGNvbnRlbnQuXG5cdFx0XHRcdGF0dGFjaFRlc3RQYWdlTGlzdGVuZXJzKCk7XG5cdFx0XHRcdGF0dGFjaFJldGVzdExpc3RlbmVycygpO1xuXHRcdFx0fVxuXHRcdH0gKTtcblx0fVxuXG5cdC8vIEF1dG8taW5pdGlhbGl6ZSBvbiBET00gcmVhZHlcblx0aWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdsb2FkaW5nJykge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBpbml0KTtcblx0fSBlbHNlIHtcblx0XHRpbml0KCk7XG5cdH1cblxuXHRyZXR1cm4ge1xuXHRcdGluaXQ6IGluaXRcblx0fTtcbn0pKCk7XG4iXX0=
