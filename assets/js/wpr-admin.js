(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

document.querySelectorAll(".custom-select").forEach(customSelect => {
  const selectBtn = customSelect.querySelector(".select-button");
  const selectedValue = customSelect.querySelector(".selected-value");
  const handler = function (elm) {
    const customChangeEvent = new CustomEvent('custom-select-change', {
      detail: {
        selectedOption: elm
      }
    });
    selectedValue.textContent = elm.textContent;
    customSelect.classList.remove("active");
    customSelect.dispatchEvent(customChangeEvent);
  };
  selectBtn.addEventListener("click", () => {
    customSelect.classList.toggle("active");
    selectBtn.setAttribute("aria-expanded", selectBtn.getAttribute("aria-expanded") === "true" ? "false" : "true");
  });
  customSelect.addEventListener('click', function (e) {
    if (e.target.matches('label')) {
      const allItems = customSelect.querySelectorAll('li');
      allItems.forEach(item => item.classList.remove('active'));
      const clickedPlan = e.target.closest('li');
      if (clickedPlan) {
        clickedPlan.classList.add('active');
        handler(clickedPlan);
      }
    }
  });
  document.addEventListener("click", e => {
    if (!customSelect.contains(e.target)) {
      customSelect.classList.remove("active");
      selectBtn.setAttribute("aria-expanded", "false");
    }
  });
});

},{}],2:[function(require,module,exports){
"use strict";

var $ = jQuery;
$(document).ready(function () {
  /**
   * Refresh License data
   */
  var _isRefreshing = false;
  $('#wpr-action-refresh_account').on('click', function (e) {
    if (!_isRefreshing) {
      var button = $(this);
      var account = $('#wpr-account-data');
      var expire = $('#wpr-expiration-data');
      e.preventDefault();
      _isRefreshing = true;
      button.trigger('blur');

      // Start polling if not already running.addClass('wpr-isLoading');
      expire.removeClass('wpr-isValid wpr-isInvalid');
      $.post(ajaxurl, {
        action: 'rocket_refresh_customer_data',
        _ajax_nonce: rocket_ajax_data.nonce
      }, function (response) {
        button.removeClass('wpr-isLoading');
        button.addClass('wpr-isHidden');
        if (true === response.success) {
          account.html(response.data.license_type);
          expire.addClass(response.data.license_class).html(response.data.license_expiration);
          setTimeout(function () {
            button.removeClass('wpr-icon-refresh wpr-isHidden');
            button.addClass('wpr-icon-check');
          }, 250);
        } else {
          setTimeout(function () {
            button.removeClass('wpr-icon-refresh wpr-isHidden');
            button.addClass('wpr-icon-close');
          }, 250);
        }
        setTimeout(function () {
          var vTL = new TimelineLite({
            onComplete: function () {
              _isRefreshing = false;
            }
          }).set(button, {
            css: {
              className: '+=wpr-isHidden'
            }
          }).set(button, {
            css: {
              className: '-=wpr-icon-check'
            }
          }, 0.25).set(button, {
            css: {
              className: '-=wpr-icon-close'
            }
          }).set(button, {
            css: {
              className: '+=wpr-icon-refresh'
            }
          }, 0.25).set(button, {
            css: {
              className: '-=wpr-isHidden'
            }
          });
        }, 2000);
      });
    }
    return false;
  });

  /**
   * Save Toggle option values on change
   */
  $('.wpr-radio input[type=checkbox]').on('change', function (e) {
    e.preventDefault();
    var name = $(this).attr('id');
    var value = $(this).prop('checked') ? 1 : 0;
    var excluded = ['cloudflare_auto_settings', 'cloudflare_devmode', 'analytics_enabled', 'wpr-rocketcdn-free-toggle', 'wpr-byocdn-toggle', 'wpr-rocketcdn-paid-toggle'];
    if (excluded.indexOf(name) >= 0) {
      return;
    }
    $.post(ajaxurl, {
      action: 'rocket_toggle_option',
      _ajax_nonce: rocket_ajax_data.nonce,
      option: {
        name: name,
        value: value
      }
    }, function (response) {});
  });

  /**
      * Save enable CPCSS for mobiles option.
      */
  $('#wpr-action-rocket_enable_mobile_cpcss').on('click', function (e) {
    e.preventDefault();
    $('#wpr-action-rocket_enable_mobile_cpcss').addClass('wpr-isLoading');
    $.post(ajaxurl, {
      action: 'rocket_enable_mobile_cpcss',
      _ajax_nonce: rocket_ajax_data.nonce
    }, function (response) {
      if (response.success) {
        // Hide Mobile CPCSS btn on success.
        $('#wpr-action-rocket_enable_mobile_cpcss').hide();
        $('.wpr-hide-on-click').hide();
        $('.wpr-show-on-click').show();
        $('#wpr-action-rocket_enable_mobile_cpcss').removeClass('wpr-isLoading');
      }
    });
  });

  /**
   * Save enable Google Fonts Optimization option.
   */
  $('#wpr-action-rocket_enable_google_fonts').on('click', function (e) {
    e.preventDefault();
    $('#wpr-action-rocket_enable_google_fonts').addClass('wpr-isLoading');
    $.post(ajaxurl, {
      action: 'rocket_enable_google_fonts',
      _ajax_nonce: rocket_ajax_data.nonce
    }, function (response) {
      if (response.success) {
        // Hide Mobile CPCSS btn on success.
        $('#wpr-action-rocket_enable_google_fonts').hide();
        $('.wpr-hide-on-click').hide();
        $('.wpr-show-on-click').show();
        $('#wpr-action-rocket_enable_google_fonts').removeClass('wpr-isLoading');
        $('#minify_google_fonts').val(1);
      }
    });
  });
  $('#rocket-dismiss-promotion').on('click', function (e) {
    e.preventDefault();
    $.post(ajaxurl, {
      action: 'rocket_dismiss_promo',
      nonce: rocket_ajax_data.nonce
    }, function (response) {
      if (response.success) {
        $('#rocket-promo-banner').hide('slow');
      }
    });
  });
  $('#rocket-dismiss-renewal').on('click', function (e) {
    e.preventDefault();
    $.post(ajaxurl, {
      action: 'rocket_dismiss_renewal',
      nonce: rocket_ajax_data.nonce
    }, function (response) {
      if (response.success) {
        $('#rocket-renewal-banner').hide('slow');
      }
    });
  });
  $('#wpr-update-exclusion-list').on('click', function (e) {
    e.preventDefault();
    $('#wpr-update-exclusion-msg').html('');
    $.ajax({
      url: rocket_ajax_data.rest_url,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', rocket_ajax_data.rest_nonce);
        xhr.setRequestHeader('Accept', 'application/json, */*;q=0.1');
        xhr.setRequestHeader('Content-Type', 'application/json');
      },
      method: "PUT",
      success: function (responses) {
        let exclusion_msg_container = $('#wpr-update-exclusion-msg');
        exclusion_msg_container.html('');
        if (undefined !== responses['success']) {
          exclusion_msg_container.append('<div class="notice notice-error">' + responses['message'] + '</div>');
          return;
        }
        Object.keys(responses).forEach(response_key => {
          exclusion_msg_container.append('<strong>' + response_key + ': </strong>');
          exclusion_msg_container.append(responses[response_key]['message']);
          exclusion_msg_container.append('<br>');
        });
      }
    });
  });

  /**
   * Enable mobile cache option.
   */
  $('#wpr_enable_mobile_cache').on('click', function (e) {
    e.preventDefault();
    $('#wpr_enable_mobile_cache').addClass('wpr-isLoading');
    $.post(ajaxurl, {
      action: 'rocket_enable_mobile_cache',
      _ajax_nonce: rocket_ajax_data.nonce
    }, function (response) {
      if (response.success) {
        // Hide Mobile cache enable button on success.
        $('#wpr_enable_mobile_cache').hide();
        $('#wpr_mobile_cache_default').hide();
        $('#wpr_mobile_cache_response').show();
        $('#wpr_enable_mobile_cache').removeClass('wpr-isLoading');

        // Set values of mobile cache and separate cache files for mobiles option to 1.
        $('#cache_mobile').val(1);
        $('#do_caching_mobile_files').val(1);
      }
    });
  });
});
document.addEventListener('DOMContentLoaded', function () {
  const analyticsCheckbox = document.getElementById('analytics_enabled');
  if (analyticsCheckbox) {
    analyticsCheckbox.addEventListener('change', function () {
      const isChecked = this.checked;

      // Update the global mixpanel data optin state immediately
      if (typeof rocket_mixpanel_data !== 'undefined') {
        rocket_mixpanel_data.optin_enabled = isChecked ? '1' : '0';
      }
      fetch(ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'rocket_toggle_optin',
          value: isChecked ? 1 : 0,
          _ajax_nonce: rocket_ajax_data.nonce
        })
      });
    });
  }
});
document.addEventListener('DOMContentLoaded', function () {
  /**
   * Performance Monitoring with Progressive Polling.
   */

  // ==== Configuration ====
  const POLL_BASE_INTERVAL = 2000; // Start polling at 2 seconds
  const POLL_MAX_INTERVAL = 5000; // Max polling interval (5 seconds)

  // ==== State ====
  let rocketInsightsIds = Array.isArray(window.rocket_ajax_data?.rocket_insights_ids) ? window.rocket_ajax_data.rocket_insights_ids.slice() : [];
  let pollInterval = POLL_BASE_INTERVAL;
  let pollTimer = null;
  let hasCredit = true; // Track credit status
  let globalScoreData = {
    data: {
      status: '',
      score: 0,
      pages_num: 0
    },
    html: '',
    row_html: '',
    disabled_btn_html: {
      global_score_widget: '',
      rocket_insights: ''
    }
  };

  // Initialize globalScoreData from localized script data if available
  if (window.rocket_ajax_data?.global_score_data) {
    globalScoreData = window.rocket_ajax_data.global_score_data;
  }

  // ==== DOM Selectors ====
  const $pageUrlInput = $('#wpr-speed-radar-url-input');
  const $tableBody = $('.wpr-ri-urls-table tbody');
  const $table = $('.wpr-ri-urls-table');

  // ==== Utility Functions ====
  function isValidUrl(input) {
    try {
      const url = new URL(input);
      return url.hostname.includes('.') && url.hostname.split('.').pop().length > 0;
    } catch {
      return false;
    }
  }
  function addIds(newId) {
    if (!rocketInsightsIds.includes(newId)) {
      rocketInsightsIds.push(newId);
    }
  }
  function hasId(id) {
    return rocketInsightsIds.includes(id);
  }
  function removeId(id) {
    // Ensure that the id to be removed is an integer for accurate comparison.
    const idToRemove = parseInt(id, 10);
    rocketInsightsIds = rocketInsightsIds.filter(x => parseInt(x, 10) !== idToRemove);
  }
  function updateQuotaBanner(canAddPages) {
    const $summaryInfo = $('.wpr-ri-summary-info');
    const isFree = window.rocket_ajax_data?.is_free === '1';
    const $quotaBanner = isFree ? $('#wpr-ri-quota-banner') : $('#rocket_insights_survey');

    // Show banner if URL limit reached OR no credits left (matching PHP logic in Subscriber.php line 398).
    const shouldShowBanner = canAddPages === false || !hasCredit;
    if (shouldShowBanner) {
      $summaryInfo.hide();
      $quotaBanner.removeClass('hidden');
    } else {
      $summaryInfo.show();
      $quotaBanner.addClass('hidden');
    }
  }
  function updateCreditState(responseHasCredit) {
    if (responseHasCredit !== undefined && hasCredit !== responseHasCredit) {
      hasCredit = responseHasCredit;

      // Update all retest buttons when credit status changes
      updateAllRetestButtons();
    }
  }
  function updateAllRetestButtons() {
    const retestButtons = document.querySelectorAll('.wpr-action-speed_radar_refresh');
    retestButtons.forEach(button => {
      const row = button.closest('.wpr-ri-item');
      if (!row) return;

      // Get the row ID and check if it's currently being processed
      const rowId = parseInt(row.dataset.rocketInsightsId, 10);
      const isRunning = rocketInsightsIds.includes(rowId);
      if (!hasCredit || isRunning) {
        // Disable button
        button.classList.add('wpr-ri-action--disabled');
        button.setAttribute('disabled', 'true');
        if (!hasCredit) {
          // Add tooltip for no credit
          button.classList.add('wpr-btn-with-tool-tip');
          button.setAttribute('title', window.rocket_ajax_data?.rocket_insights_no_credit_tooltip || 'Upgrade your plan to get access to re-test performance or run new tests');
        }
      } else {
        // Enable button
        button.classList.remove('wpr-ri-action--disabled', 'wpr-btn-with-tool-tip');
        button.removeAttribute('disabled');
        button.removeAttribute('title');
      }
    });
  }
  function resetPolling() {
    if (pollTimer) {
      clearTimeout(pollTimer);
      pollTimer = null;
    }
    pollInterval = POLL_BASE_INTERVAL;
  }
  function schedulePolling() {
    if (rocketInsightsIds.length > 0) {
      pollTimer = setTimeout(() => {
        getResults();
      }, pollInterval);
    }
  }
  function incrementPolling() {
    pollInterval = Math.min(pollInterval * 1.3, POLL_MAX_INTERVAL); // Exponential backoff
  }
  function isOnDashboard() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('page') === 'wprocket' && window.location.hash === '#dashboard';
  }
  function isOnRocketInsights() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('page') === 'wprocket' && window.location.hash === '#rocket_insights';
  }
  function updateGlobalScoreRow(globalScoreData) {
    const $tableGlobalScore = $('.wpr-ri-urls-table .wpr-global-score');
    if ($tableGlobalScore.length > 0) {
      $tableGlobalScore.replaceWith(globalScoreData.row_html);
    } else if ($tableBody.length > 0) {
      $tableBody.prepend(globalScoreData.row_html);
    }
    decideGlobalScoreToUpdate();
  }

  /**
   * Updates the global score UI widget or table row based on the selected menu.
   * When the dashboard or rocket insights menu is clicked, this function updates
   * the corresponding global score display after a short delay.
   */
  function decideGlobalScoreToUpdate() {
    if ('' === globalScoreData.html) {
      return;
    }
    let globalScoreWidgets = $('.wpr-global-score-widget');
    if (globalScoreWidgets.length === 0) {
      return;
    }

    // Update all global score widget instances.
    globalScoreWidgets.html(globalScoreData.html);

    // Disable "Add Pages" button on global score widget.
    if (!('disabled_btn_html' in globalScoreData)) {
      return;
    }
    $('.wpr-global-score-widget .wpr-global-score-widget-btn-wrapper').html(globalScoreData.disabled_btn_html.global_score_widget);
  }

  // ==== AJAX Handlers ====
  function getResults() {
    if (rocketInsightsIds.length === 0) {
      resetPolling();
      return;
    }
    window.wp.apiFetch({
      path: window.wp.url.addQueryArgs('/wp-rocket/v1/rocket-insights/pages/progress', {
        ids: rocketInsightsIds
      })
    }).then(response => {
      if (response.success && Array.isArray(response.results)) {
        // Update credit status
        updateCreditState(response.has_credit);

        // Update quota banner visibility
        updateQuotaBanner(response.can_add_pages);

        // Update global score data and widget when status || page count changes.
        if (globalScoreData.data.status !== response.global_score_data.data.status || globalScoreData.data.pages_num !== response.global_score_data.data.pages_num) {
          // Update global score data.
          globalScoreData = response.global_score_data;

          // Update all global score widget instances.
          $('.wpr-global-score-widget').html(response.global_score_data.html);
          // Update global score row in table if on Rocket Insights page.
          updateGlobalScoreRow(globalScoreData);

          // Fire custom event for other widgets (like recommendations)
          document.dispatchEvent(new CustomEvent('wprGlobalScoreUpdated', {
            detail: globalScoreData.data
          }));
        }
        response.results.forEach(result => {
          const $row = $(`.wpr-ri-item[data-rocket-insights-id="${result.id}"]`);
          $row.replaceWith(result.html);
          $(document).trigger('rocket-insights-page-test-polling', [result.id]);

          // Trigger custom event only when test is completed and not failed, so we don't target an element that might be removed from the DOM after test completion.
          if (result.status === 'completed') {
            $(document).trigger('rocket-insights-page-test-completed', [result.id]);
          }
          if (result.status === 'completed' || result.status === 'failed') {
            removeId(result.id);
          }
        });
        incrementPolling();
        schedulePolling();
      } else {
        // On error, clear IDs and stop polling
        rocketInsightsIds = [];
        resetPolling();
        console.error('Polling error:', response.results || response);
      }
    });
  }
  function handleAddPage(e) {
    e.preventDefault();

    // check if has attr disabled
    if ($(this).attr('disabled')) {
      return;
    }
    const pageUrl = $pageUrlInput.val().trim();
    if (!isValidUrl(pageUrl)) {
      alert('Please enter a valid URL');
      return;
    }
    const source = $(this).data('source');
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/',
      method: 'POST',
      data: {
        page_url: pageUrl,
        source: source
      }
    }).then(response => {
      if (response.success) {
        if (!hasId(response.id)) {
          $pageUrlInput.val('');
          $tableBody.append(response.html);

          // Custom event when new page is added.
          $(document).trigger('rocket-insights-page-added');
          $table.removeClass('hidden');
          addIds(response.id);
          let pages_num_container = $('#rocket_rocket_insights_pages_num');
          pages_num_container.text(parseInt(pages_num_container.text()) + 1);

          // Update credit status
          updateCreditState(response.has_credit);

          // Update global score data.
          globalScoreData = response.global_score_data;

          // Update global score row in table if on Rocket Insights page.
          updateGlobalScoreRow(globalScoreData);
          if ('disabled_btn_html' in globalScoreData) {
            $('#wpr_rocket_insights_add_page_btn_wrapper').html(globalScoreData.disabled_btn_html.rocket_insights);
          }

          // Show/hide quota banner based on can_add_pages
          updateQuotaBanner(response.can_add_pages);
          if (pollTimer) {
            resetPolling();
          }
          schedulePolling();
        }
      } else {
        // Clear the input field on error
        $pageUrlInput.val('');

        // Handle URL limit reached error
        if (response?.message && response.message.includes('Maximum number of URLs reached')) {
          // Update UI state to reflect URL limit has been reached
          disableAddUrlElements();
          // Show quota banner (can_add_pages = false)
          updateQuotaBanner(response.can_add_pages !== undefined ? response.can_add_pages : false);
        }
        console.error(response?.message || response);
      }
    });
  }
  function handleResetPage(e) {
    e.preventDefault();
    const $button = $(this);
    let id = $button.parents('.wpr-ri-item').data('rocket-insights-id');
    if (!id) {
      return;
    }
    const source = $button.data('source');
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/' + id,
      method: 'PATCH',
      data: {
        source: source
      }
    }).then(response => {
      if (response.success) {
        addIds(response.id);
        $(`#ri_details_${response.id} .details-section-td`).remove();
        const $row = $(`[data-rocket-insights-id="${response.id}"]`);
        $row.replaceWith(response.html);

        // Custom event when page is retested.
        $(document).trigger('rocket-insights-page-retest', [response.id]);

        // Update credit status
        updateCreditState(response.has_credit);

        // Update quota banner visibility
        updateQuotaBanner(response.can_add_pages);

        // Update global score data.
        globalScoreData = response.global_score_data;

        // Update global score row in table if on Rocket Insights page.
        updateGlobalScoreRow(globalScoreData);
        if (pollTimer) {
          resetPolling();
        }
        schedulePolling();
      } else {
        console.error(response?.message || response);
      }
    });
  }

  // ==== Initialization ====
  // Bind event
  $(document).on('click', '#wpr-action-add_page_speed_radar', handleAddPage);
  $(document).on('click', '.wpr-action-speed_radar_refresh', handleResetPage);
  // Handle Enter key press on page url input.
  $(document).on('keypress', '#wpr-speed-radar-url-input', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      $('#wpr-action-add_page_speed_radar').click();
    }
  });

  // Only poll if on a wpr section that requires polling(dashboard|rocket_insights) (more robust check)
  function isValidPageForPolling() {
    const urlParams = new URLSearchParams(window.location.search);
    return 'wprocket' === urlParams.get('page');
  }

  // Resume polling if needed
  if (isValidPageForPolling() && rocketInsightsIds.length > 0) {
    if (pollTimer) {
      resetPolling();
    }
    schedulePolling();
  }

  // Handle UI update on the rocket insights tab when "Add Pages" button on the global score widget is clicked.
  $(document).on('click', '.wpr-percentage-score-widget .wpr-ri-add-url-button', function () {
    if (!this.textContent.includes('Add Pages')) {
      return;
    }

    // Delay UI update a bit till element is visible.
    setTimeout(() => {
      updateGlobalScoreRow(globalScoreData);
    }, 30);
  });

  // Handle collapseed styling for first load or dynamic row addition.
  function addCollapsedStylingToLastRow(onLoad = false) {
    $('.wpr-ri-item').last().find('td').addClass('border-bottom');
    if (onLoad) {
      // On load, remove wpr-last-expanded from elements that are not the last after being added from backend.
      $('.wpr-ri-item-toggle').not(':last').removeClass('wpr-last-expanded');
      $('.wpr-ri-item-actions').not(':last').removeClass('wpr-last-expanded');
      $('.details-section-td').not(':last').removeClass('wpr-last-expanded');

      // Bail early if last item is already expanded on load so as not to have conflicting styles.
      if ($('.details-section-td').last().hasClass('wpr-last-expanded')) {
        return;
      }
    }
    $('.wpr-ri-item-toggle').last().addClass('wpr-last-collapsed');
    $('.wpr-ri-item-actions').last().addClass('wpr-last-collapsed');
    $('.details-section-td').last().addClass('wpr-last-collapsed');
  }

  // Toggles the visibility of a single test details row, switches the caret icon, and updates styling for the last item.
  function toggleSingleRowVisibility(insightsId, source) {
    let $element = $(`#ri_details_${insightsId}`);
    let isVisible = $element.hasClass('wpr-ri-details--expanded');
    let isLast = $(`[data-rocket-insights-id="${insightsId}"] .wpr-ri-item-toggle-single`).is($('.wpr-ri-item-toggle-single').last());
    if (isVisible) {
      $element.removeClass('wpr-ri-details--expanded');
      $(`[data-rocket-insights-id="${insightsId}"]`).removeClass('wpr-ri-item--expanded');
      // Manipulate styling for last elements when details cell is not visible.
      if (isLast) {
        updateRowStylingForLastItem(false);
      }
      return;
    }
    $element.addClass('wpr-ri-details--expanded');
    $(`[data-rocket-insights-id="${insightsId}"]`).addClass('wpr-ri-item--expanded');

    // Track expand only expand metric action.
    handleMetricActionTracking('expand', insightsId, source);
    if (isLast) {
      updateRowStylingForLastItem();
    }
  }

  // Tracks user interactions with metric actions in Rocket Insights via AJAX.
  function handleMetricActionTracking(event, rowId, source) {
    $.post(ajaxurl, {
      action: 'rocket_insight_track_metric_actions',
      _ajax_nonce: rocket_ajax_data.nonce,
      event: event,
      row_id: rowId,
      source: source
    }, function (response) {
      if (!response.success) {
        console.error('Metric action tracking failed:', response?.data || response);
      }
    });
  }

  /**
   * Updates the border styling for the last row item in the Rocket Insights table.
   *
   * Manages border radius and bottom border styling based on whether the details
   * cell is expanded or collapsed to maintain proper visual appearance.
   */
  function updateRowStylingForLastItem(isExpanded = true) {
    const addState = isExpanded ? 'wpr-last-expanded' : 'wpr-last-collapsed';
    const removeState = isExpanded ? 'wpr-last-collapsed' : 'wpr-last-expanded';
    var $selectors = {
      lastToggle: $('.wpr-ri-item-toggle').last(),
      lastActions: $('.wpr-ri-item-actions').last()
    };
    $selectors.lastToggle.removeClass(removeState).addClass(addState);
    $selectors.lastActions.removeClass(removeState).addClass(addState);

    // Check if last detail row is not the last row in the table so as not to apply improper styling with border radius between rows.
    var $lastDetailsCell = $('.details-section-td').last();
    if ($lastDetailsCell.closest('tr').next('tr').length !== 0) {
      return;
    }
    $lastDetailsCell.removeClass(removeState).addClass(addState);
  }

  // Toggle single item.
  $(document).on('click', '.wpr-ri-item-toggle-single', function () {
    var insightsId = $(this).closest('.wpr-ri-item').data('rocket-insights-id');
    toggleSingleRowVisibility(insightsId, 'url_expand');
  });

  // Toggle all items.
  $(document).on('click', '.wpr-ri-item-toggle-all', function () {
    if ($('.wpr-ri-details--expanded').length > 0) {
      $('.wpr-ri-details').removeClass('wpr-ri-details--expanded');
      $('.wpr-ri-item').removeClass('wpr-ri-item--expanded');
      $(this).removeClass('wpr-ri-item-toggle-all--expanded');
      updateRowStylingForLastItem(false);
      return;
    }
    $('.wpr-ri-details').addClass('wpr-ri-details--expanded');
    $('.wpr-ri-item').addClass('wpr-ri-item--expanded');
    $(this).addClass('wpr-ri-item-toggle-all--expanded');
    updateRowStylingForLastItem();

    // Track single expand event for "Expand All" action with test_id as 'all'.
    handleMetricActionTracking('expand', 'all', 'global_expand');
  });

  // Track "See GTmetrix Report" clicks in Rocket Insights.
  $(document).on('click', '.wpr-ri-report', function (e) {
    // Only track if link is not disabled and mixpanel is available.
    if ($(this).hasClass('wpr-ri-action--disabled')) {
      return;
    }
    var insightsId = $(this).data('rocket-insights-row-id');
    handleMetricActionTracking('see_report', insightsId, 'see_report_button');
  });

  // Update table styling after new page is added.
  $(document).on('rocket-insights-page-test-completed', function (e, insightsId) {
    // Bail out if there is more than 1 result.
    if ($('.wpr-ri-item-result').length > 1) {
      return;
    }

    // Remove dynamic class when only one item exist in table.
    $('.wpr-last-collapsed').removeClass('wpr-last-collapsed');
  });

  // Update table styling after new page is added.
  $(document).on('rocket-insights-page-added', function (e) {
    // Remove dynamic class for last item if exists when new page is added.
    $('.wpr-last-collapsed').removeClass('wpr-last-collapsed');
    $('.wpr-last-expanded').removeClass('wpr-last-expanded');
    $('.border-bottom').removeClass('border-bottom');
  });

  // Update table styling after retest or polling update for last row.
  $(document).on('rocket-insights-page-retest rocket-insights-page-test-polling', function (e, insightsId) {
    // Check if item is the last.
    var isLast = $(`[data-rocket-insights-id="${insightsId}"]`).is($('.wpr-ri-item-result').last());
    if (isLast) {
      addCollapsedStylingToLastRow();
    }
  });
  $(document).on('rocket-insights-page-retest', function (e, insightsId) {
    $(`#ri_details_${insightsId}`).removeClass('wpr-ri-details--expanded');
    $(`[data-rocket-insights-id="${insightsId}"]`).removeClass('wpr-ri-item--expanded');
  });
  $(window).load(() => {
    if (!isOnRocketInsights()) {
      return;
    }

    // Add collapsed styling to the last row on initial load.
    addCollapsedStylingToLastRow(true);

    // Set initial expand/collapse state.
    const urlParams = new URLSearchParams(window.location.search);
    const testId = urlParams.get('ri_id');

    // Send mixpanel event for auto expanded row.
    let firstRowId = $('.wpr-ri-item--expanded')?.first()?.data('rocket-insights-id');

    // Check if ri_id was passed in query string to open specific test.
    if (!testId || testId === '') {
      if (firstRowId) {
        handleMetricActionTracking('expand', firstRowId, 'auto_expand_url');
      }
      return;
    }
    handleMetricActionTracking('expand', testId, 'post type listing');
    $('html, body').animate({
      scrollTop: $(`[data-rocket-insights-id="${testId}"]`).offset().top - 100
    }, 500);

    // Remove ri_id from URL without page reload
    urlParams.delete('ri_id');
    const newUrl = window.location.pathname + '?' + urlParams.toString() + window.location.hash;
    window.history.replaceState({}, '', newUrl);
  });
});

},{}],3:[function(require,module,exports){
"use strict";

require("../lib/greensock/TweenLite.min.js");
require("../lib/greensock/TimelineLite.min.js");
require("../lib/greensock/easing/EasePack.min.js");
require("../lib/greensock/plugins/CSSPlugin.min.js");
require("../lib/greensock/plugins/ScrollToPlugin.min.js");
require("../global/cdn-driver.js");
require("../global/pageManager.js");
require("../global/main.js");
require("../global/fields.js");
require("../global/beacon.js");
require("../global/ajax.js");
require("../global/recommendations-widget.js");
require("../global/rocketcdn.js");
require("../global/rocketcdn-subscription-polling.js");
require("../global/countdown.js");
require("../global/mixpanel.js");

},{"../global/ajax.js":2,"../global/beacon.js":4,"../global/cdn-driver.js":5,"../global/countdown.js":6,"../global/fields.js":7,"../global/main.js":8,"../global/mixpanel.js":9,"../global/pageManager.js":10,"../global/recommendations-widget.js":11,"../global/rocketcdn-subscription-polling.js":12,"../global/rocketcdn.js":13,"../lib/greensock/TimelineLite.min.js":14,"../lib/greensock/TweenLite.min.js":15,"../lib/greensock/easing/EasePack.min.js":16,"../lib/greensock/plugins/CSSPlugin.min.js":17,"../lib/greensock/plugins/ScrollToPlugin.min.js":18}],4:[function(require,module,exports){
"use strict";

var $ = jQuery;
$(document).ready(function () {
  if ('Beacon' in window) {
    /**
     * Show beacons on button "help" click
     */
    var $help = $('.wpr-infoAction--help');
    $help.on('click', function (e) {
      var ids = $(this).attr('data-beacon-id');
      var button = $(this).data('wpr_track_button') || 'Beacon Help';
      var context = $(this).data('wpr_track_context') || 'Settings';

      // Track with MixPanel JS SDK
      wprTrackHelpButton(button, context);

      // Continue with existing beacon functionality
      wprCallBeacon(ids);
      return false;
    });
    function wprCallBeacon(aID) {
      aID = aID.split(',');
      if (aID.length === 0) {
        return;
      }
      if (aID.length > 1) {
        window.Beacon("suggest", aID);
        setTimeout(function () {
          window.Beacon("open");
        }, 200);
      } else {
        window.Beacon("article", aID.toString());
      }
    }
  }
  $('.wpr-ri-report').on('click', function () {
    wprTrackHelpButton('rocket insights see gtmetrix report', 'performance summary');
  });

  // MixPanel tracking function
  function wprTrackHelpButton(button, context) {
    if (typeof mixpanel !== 'undefined' && mixpanel.track) {
      // Check if user has opted in using localized data
      if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
        return;
      }

      // Identify user with hashed license email if available
      if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
        mixpanel.identify(rocket_mixpanel_data.user_id);
      }
      mixpanel.track('Button Clicked', {
        'button': button,
        'button_context': context,
        'plugin': rocket_mixpanel_data.plugin,
        'brand': rocket_mixpanel_data.brand,
        'application': rocket_mixpanel_data.app,
        'context': rocket_mixpanel_data.context,
        'path': rocket_mixpanel_data.path,
        'interaction_channel': 'UI'
      });
    }
  }

  // Make function globally available
  window.wprTrackHelpButton = wprTrackHelpButton;
});

},{}],5:[function(require,module,exports){
"use strict";

(document => {
  'use strict';

  function notifyCdnStateChange() {
    document.dispatchEvent(new CustomEvent('wpr-cdn-state-change'));
  }

  /**
   * Keeps the hidden cdn_type and cdn_state form inputs in sync after a REST-driven
   * mode change so a subsequent form save doesn't send stale values and trigger
   * CdnStateBridge::reconcile() to recompute the wrong state.
   *
   * Mirrors the mapping in Rest::apply_cdn_mode():
   *   byocdn → cdn_type=byocdn; everything else → cdn_type=rocketcdn.
   *
   * @param {string} mode New cdn_state value ('rocketcdn_free', 'byocdn', 'nothing', etc.).
   */
  function syncCdnHiddenInputs(mode) {
    const stateInput = document.getElementById('cdn_state');
    if (stateInput) {
      stateInput.value = mode;
    }
    const typeInput = document.getElementById('cdn_type');
    if (typeInput) {
      typeInput.value = 'byocdn' === mode ? 'byocdn' : 'rocketcdn';
    }
  }
  document.addEventListener('DOMContentLoaded', () => {
    initCdnDriverTabs();
    initCdnModeToggle();
    initAddHomepage();
    initAddPage();
    initDeletePage();
    updateSubmitButtonStateOnSubscriptionLoading();
  });
  const addHomeButton = document.querySelector('#wpr_add_page_component .wpr-cdn-add-page__homepage');

  /**
   * Updates the status indicator component with new HTML content.
   *
   * @param {string} html - The HTML string to replace the status indicator with.
   * @returns {void}
   */
  function updateStatusIndicatorComponent(html) {
    const statusIndicator = document.querySelector('.wpr-cdn-built-in .wpr-cdn-status');
    if (statusIndicator && html) {
      statusIndicator.outerHTML = html;
    }
  }

  /**
   * Toggles the disabled state of CDN-related UI elements based on the active driver.
   *
   * For the 'rocketcdn' driver, targets both shared CDN and RocketCDN sections.
   * For all other drivers, only targets the shared CDN section and always enables it.
   *
   * @param {string}  driver   The CDN driver identifier (e.g. 'rocketcdn').
   * @param {boolean} disabled Whether to disable the CDN UI elements.
   */
  function updateRocketCDNElementsState(driver, disabled) {
    if ('rocketcdn' === driver) {
      if (!disabled) {
        document.querySelectorAll('.cdn-shared-section, .rocketcdn-shared-section').forEach(el => {
          el.classList.remove('wpr-cdn-disabled');
        });
        return;
      }
      document.querySelectorAll('.cdn-shared-section, .rocketcdn-shared-section').forEach(el => {
        el.classList.add('wpr-cdn-disabled');
      });
      return;
    }
    document.querySelectorAll('.cdn-shared-section').forEach(el => {
      el.classList.remove('wpr-cdn-disabled');
    });
  }

  /**
   * Shows or hides the limit-reached tooltip on the ADD PAGE button.
   *
   * @param {boolean} limitReached Whether the free-tier page limit has been reached.
   * @returns {void}
   */
  function updateTooltipState(limitReached) {
    const tooltip = document.querySelector('.wpr-cdn-add-page__button-wrapper .wpr-tooltip');
    if (tooltip) {
      tooltip.classList.toggle('wpr-isHidden', !limitReached);
    }
  }

  /** Pending banner auto-expand timer ID, or null when no timer is active. */
  let autoExpandTimer = null;

  /**
   * Updates the RocketCDN CTA visibility and expansion state.
   *
   * When the page count reaches the limit, expansion is deferred by 15 seconds so the
   * user has time to react before the upsell banner opens. Any in-flight timer is
   * cancelled whenever this function is called (e.g. on page deletion), ensuring stale
   * expands never fire after the state has changed.
   *
   * @param {number} count Current number of free-tier pages.
   * @param {number} limit Free-tier page limit.
   * @returns {void}
   */
  function updateRocketCtaState(count, limit) {
    const cta = document.getElementById('wpr-rocketcdn-cta');
    const resellerBanner = document.getElementById('wpr-rocketcdn-reseller-limit-cta');
    if (!cta && !resellerBanner) {
      return;
    }

    // Cancel any pending expand — state has changed.
    if (autoExpandTimer !== null) {
      clearTimeout(autoExpandTimer);
      autoExpandTimer = null;
    }
    const atLimit = count >= limit;
    if (cta) {
      cta.classList.toggle('wpr-isHidden', count === 0);
    }
    if (resellerBanner) {
      resellerBanner.classList.toggle('wpr-isHidden', !atLimit);
    }
    if (cta) {
      if (atLimit) {
        // Always show "Nice work!" text immediately.
        cta.classList.add('wpr-rocketcdn-cta---max-limit');
        if (!cta.classList.contains('wpr-rocketcdn-cta--expanded')) {
          // Banner is collapsed — keep it collapsed for 15s then expand.
          cta.classList.add('wpr-rocketcdn-cta--collapsed');
          cta.classList.remove('wpr-rocketcdn-cta--expanded');
          autoExpandTimer = setTimeout(() => {
            autoExpandTimer = null;
            cta.classList.remove('wpr-rocketcdn-cta--collapsed');
            cta.classList.add('wpr-rocketcdn-cta--expanded');
            document.dispatchEvent(new CustomEvent('rocketCDNBannerAutoExpanded'));
          }, 15000);
        }
      } else {
        cta.classList.toggle('wpr-rocketcdn-cta--collapsed', count > 0);
        cta.classList.remove('wpr-rocketcdn-cta--expanded', 'wpr-rocketcdn-cta---max-limit');
      }
    }
  }

  // Cancel any pending banner expand when the user navigates away from the CDN tab.
  document.addEventListener('rocketJsAfterPageNavigation', e => {
    if (e.detail.pageId !== 'page_cdn' && autoExpandTimer !== null) {
      clearTimeout(autoExpandTimer);
      autoExpandTimer = null;
    }
  });

  /**
   * Listens for custom 'rocketJsAfterPageNavigation' event to update the state of the submit button
   * based on the presence of a CDN subscription loading indicator on the CDN settings page.
   *
   * Disables the submit button when navigating to the CDN page if a subscription loading indicator is present,
   * and re-enables it when navigating away from the CDN page.
   */
  function updateSubmitButtonStateOnSubscriptionLoading() {
    document.addEventListener('rocketJsAfterPageNavigation', e => {
      // Bail out if submit button is not visible for the current page.
      if (getComputedStyle(e.detail.submitButton).display === 'none') {
        return;
      }
      const classes = ['.wpr-icon-orange-loader', '.wpr-cdn-built-in--disabled'];
      const allPresent = classes.every(cls => document.querySelector(cls) !== null);

      // Re-enable submit button when page is not cdn and bail out.
      if (e.detail.pageId !== 'page_cdn') {
        if (e.detail.submitButton.classList.contains('wpr-cdn-disabled')) {
          e.detail.submitButton.classList.remove('wpr-cdn-disabled');
        }
        return;
      }

      // Bail out if no cdn subscription loader is present.
      if (!allPresent) {
        return;
      }

      // Disable submit button when on cdn page and subscription loader is present.
      e.detail.submitButton.classList.add('wpr-cdn-disabled');
    });
  }

  /**
   * Sets the subscription loading state on the CDN UI.
   *
   * Disables the built-in CDN section, purge and exclude sections.
   */
  function revertSubscriptionLoadingState() {
    const builtIn = document.querySelector('.wpr-cdn-built-in');
    if (builtIn) {
      builtIn.classList.remove('wpr-cdn-built-in--disabled');
    }
    const purgeSection = document.querySelector('.wpr-cdn-purge.rocketcdn');
    if (purgeSection) {
      purgeSection.classList.remove('wpr-cdn-disabled');
    }
    document.querySelectorAll('.wpr-cdn-exclusions').forEach(el => {
      el.classList.remove('wpr-cdn-disabled');
      const textarea = el.querySelector('textarea');
      if (textarea) {
        textarea.disabled = false;
      }
    });
    const submitButton = document.querySelector('#wpr-options-submit');
    if (submitButton) {
      submitButton.classList.remove('wpr-cdn-disabled');
    }
    document.querySelectorAll('.wpr-cdn-mode-toggle__input').forEach(modeToggle => {
      modeToggle.disabled = false;
    });
  }
  function setSubscriptionLoadingState() {
    const builtIn = document.querySelector('.wpr-cdn-built-in');
    if (builtIn) {
      builtIn.classList.add('wpr-cdn-built-in--disabled');
    }

    // Disable purge CDN cache section.
    const purgeSection = document.querySelector('.wpr-cdn-purge.rocketcdn');
    if (purgeSection) {
      purgeSection.classList.add('wpr-cdn-disabled');
    }

    // Disable exclusion fields and section header.
    document.querySelectorAll('.wpr-cdn-exclusions').forEach(el => {
      el.classList.add('wpr-cdn-disabled');
      const textarea = el.querySelector('textarea');
      if (textarea) {
        textarea.disabled = true;
      }
    });
    const submitButton = document.querySelector('#wpr-options-submit');
    if (submitButton) {
      submitButton.classList.add('wpr-cdn-disabled');
    }

    // Disable mode toggles to prevent state changes during subscription creation.
    document.querySelectorAll('.wpr-cdn-mode-toggle__input').forEach(modeToggle => {
      modeToggle.disabled = true;
    });

    // Create polling mechanism to send a request every 10 seconds to get the subscription status and once the subscription is active, we will refresh the page for now.
    document.dispatchEvent(new CustomEvent('rocketCDNSubscriptionLoading', {}));
  }

  /**
   * Updates the `wpr-cdn-active-indicator` class to reflect which CDN driver header
   * and tab are active.
   *
   * @param {Element|null} activeToggle Toggle whose parent header and matching tab should
   *                                    receive the class, or null to clear all.
   */
  function updateCdnActiveIndicator(activeToggle) {
    document.querySelectorAll('.wpr-cdn-active-indicator').forEach(el => {
      el.classList.remove('wpr-cdn-active-indicator');
    });
    if (activeToggle) {
      const header = activeToggle.closest('.wpr-optionHeader');
      if (header) {
        header.classList.add('wpr-cdn-active-indicator');
      }
      const mode = activeToggle.getAttribute('data-cdn-mode');
      const tabDriver = 'byocdn' === mode ? 'your-own-cdn' : 'rocketcdn';
      const tab = document.querySelector(`.wpr-cdn-tabs__tab[data-cdn-driver="${tabDriver}"]`);
      if (tab) {
        tab.classList.add('wpr-cdn-active-indicator');
      }
    }
  }

  /**
   * Updates the toggle checkboxes and active indicators to reflect RocketCDN Free
   * having just been activated server-side (auto-activation from the "nothing active"
   * state, or after a confirmed activation prompt), without a full page reload.
   */
  function activateFreeModeUI() {
    const freeToggle = document.querySelector('.wpr-cdn-mode-toggle__input[data-cdn-mode="rocketcdn_free"]');
    if (!freeToggle) {
      return;
    }
    document.querySelectorAll('.wpr-cdn-mode-toggle__input').forEach(other => {
      other.checked = other === freeToggle;
    });
    updateCdnActiveIndicator(freeToggle);
    toggleDriverSections('rocketcdn');
    setActiveTab('rocketcdn');
    notifyCdnStateChange();
    syncCdnHiddenInputs('rocketcdn_free');
  }

  /**
   * Initializes the CDN mode toggle checkboxes.
   *
   * Checking activates that mode; unchecking leaves all modes inactive ('nothing').
   * Only one mode can be active at a time — checking one unchecks the others.
   * The request fires immediately on toggle change.
   */
  function initCdnModeToggle() {
    document.addEventListener('change', event => {
      const toggle = event.target.closest('.wpr-cdn-mode-toggle__input');
      if (!toggle) {
        return;
      }
      const mode = toggle.getAttribute('data-cdn-mode');
      if (!mode) {
        return;
      }

      // Capture the previously active toggle for rollback on failure.
      // Read from wpr-cdn-active-indicator (maintained by PHP + this function) rather than
      // :checked, because the change event fires after the checkbox state has already flipped.
      const previouslyActiveHeader = document.querySelector('.wpr-optionHeader.wpr-cdn-active-indicator');
      const previouslyActive = previouslyActiveHeader ? previouslyActiveHeader.querySelector('.wpr-cdn-mode-toggle__input') : null;

      // mode sent to the server: the toggle's mode when checking, 'nothing' when unchecking.
      const requestedMode = toggle.checked ? mode : 'nothing';
      const sectionDriver = 'byocdn' === mode ? 'your-own-cdn' : 'rocketcdn';
      if (toggle.checked) {
        // Uncheck all other mode toggles (mutually exclusive).
        document.querySelectorAll('.wpr-cdn-mode-toggle__input').forEach(other => {
          if (other !== toggle) {
            other.checked = false;
          }
        });
        toggleDriverSections(sectionDriver);
        setActiveTab(sectionDriver);
      }

      // Optimistically update the active indicator before the request completes.
      updateCdnActiveIndicator(toggle.checked ? toggle : null);
      notifyCdnStateChange();
      toggle.disabled = true;
      if ('rocketcdn_free' === requestedMode) {
        setSubscriptionLoadingState();
      }
      window.wp.apiFetch({
        path: '/wp-rocket/v1/rocketcdn/mode',
        method: 'POST',
        data: {
          mode: requestedMode
        }
      }).then(response => {
        updateRocketCDNElementsState('byocdn' === mode ? 'byocdn' : 'rocketcdn', response.disable_rocket_cdn_elements);
        syncCdnHiddenInputs(requestedMode);
        refreshUIElements(response);
        if (response.is_subscription_creation_loading) {
          // Loading state confirmed — poller started by refreshUIElements.
        } else {
          if ('rocketcdn_free' === requestedMode) {
            revertSubscriptionLoadingState();
          }
          toggle.disabled = false;
        }
      }).catch(() => {
        // Revert to previous state on failure.
        toggle.checked = !toggle.checked;
        toggle.disabled = false;
        if ('rocketcdn_free' === requestedMode) {
          revertSubscriptionLoadingState();
        }
        updateCdnActiveIndicator(previouslyActive);
        if (previouslyActive && previouslyActive !== toggle) {
          previouslyActive.checked = true;
          const prevMode = previouslyActive.getAttribute('data-cdn-mode');
          const prevDriver = 'byocdn' === prevMode ? 'your-own-cdn' : 'rocketcdn';
          toggleDriverSections(prevDriver);
        }
      });
    });
  }

  /**
   * Toggles visibility of CDN driver sections using the hidden utility class.
   *
   * @param {string} activeDriver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
   */
  function toggleDriverSections(activeDriver) {
    document.querySelectorAll('.rocketcdn, .your-own-cdn').forEach(section => {
      section.classList.toggle('wpr-isHidden', !section.classList.contains(activeDriver));
    });
  }

  /**
   * Updates all .rocketcdn-driver-js spans to reflect the active driver label.
   *
   * @param {string} driver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
   */
  function updateDriverLabel(driver) {
    const tab = document.querySelector(`.wpr-cdn-tabs__tab[data-cdn-driver="${driver}"]`);
    if (!tab) {
      return;
    }
    const label = tab.getAttribute('data-title');
    if (!label) {
      return;
    }
    document.querySelectorAll('.rocketcdn-driver-js').forEach(span => {
      span.textContent = label;
    });
  }

  /**
   * Updates the "Need Help?" link href for the CDN Exclusions section
   * to point to the correct docs article for the active driver.
   *
   * @param {string} driver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
   */
  function updateExcludeCdnHelpUrl(driver) {
    const link = document.querySelector('.exclude-cdn-help-js');
    if (!link) {
      return;
    }
    const isRocketCdn = 'rocketcdn' === driver;
    const url = isRocketCdn ? link.dataset.rocketcdnUrl : link.dataset.otherCdnUrl;
    const id = isRocketCdn ? link.dataset.rocketcdnId : link.dataset.otherCdnId;
    if (url) {
      link.href = url;
    }
    if (id) {
      link.dataset.beaconId = id;
    }
  }

  /**
   * Sets the active driver tab and syncs all tab-dependent UI (label spans, help URL).
   *
   * @param {string} driver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
   */
  function setActiveTab(driver) {
    document.querySelectorAll('.wpr-cdn-tabs__tab').forEach(t => {
      t.classList.toggle('wpr-cdn-tabs__tab--active', t.getAttribute('data-cdn-driver') === driver);
    });
    updateDriverLabel(driver);
    updateExcludeCdnHelpUrl(driver);
  }

  /**
   * Initializes CDN driver tab switching behavior.
   *
   * Tabs are navigation only — no backend call on click.
   * Initial driver is derived from the PHP-rendered active-state header
   * (wpr-cdn-active-indicator), which reflects cdn_state even when the mode
   * toggle itself is hidden (e.g. rocket_display_cdn_mode_toggle returning
   * false for a hosting compatibility layer). Falls back to whichever
   * toggle is checked when no header is marked active (e.g. cdn_state is
   * 'nothing').
   */
  function initCdnDriverTabs() {
    const tabs = document.querySelectorAll('.wpr-cdn-tabs__tab');
    if (!tabs.length) {
      return;
    }
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const driver = tab.getAttribute('data-cdn-driver');
        if (!driver) {
          return;
        }
        setActiveTab(driver);
        toggleDriverSections(driver);
        notifyCdnStateChange();
      });
    });
    const activeHeader = document.querySelector('.wpr-optionHeader.wpr-cdn-active-indicator');
    let initialDriver;
    if (activeHeader) {
      initialDriver = activeHeader.classList.contains('your-own-cdn') ? 'your-own-cdn' : 'rocketcdn';
    } else {
      const checkedToggle = document.querySelector('.wpr-cdn-mode-toggle__input:checked');
      initialDriver = checkedToggle && 'byocdn' === checkedToggle.getAttribute('data-cdn-mode') ? 'your-own-cdn' : 'rocketcdn';
    }
    setActiveTab(initialDriver);
    toggleDriverSections(initialDriver);
    notifyCdnStateChange();
  }

  /**
   * Adds a page (or the homepage) to RocketCDN free-tier delivery, transparently
   * handling the "RocketCDN Free is inactive" activation prompt: on a 409
   * confirm-required error, shows a native confirmation dialog and retries with
   * `confirm_activation` if the user accepts. If the server auto-activated Free
   * (no mode was active at all), updates the toggle UI to reflect it.
   *
   * @param {string} path REST path to call ('/wp-rocket/v1/rocketcdn/pages' or '.../pages/homepage').
   * @param {Object} data Request body data (e.g. { url }).
   * @returns {Promise} Resolves with the REST response; rejects on final failure or cancellation.
   */
  function requestAddPage(path, data) {
    return window.wp.apiFetch({
      path,
      method: 'POST',
      data
    }).then(response => {
      if (response.free_activated) {
        activateFreeModeUI();
      }
      return response;
    }).catch(error => {
      if ('rocketcdn_free_inactive_confirm_required' === error.code && window.confirm(error.message)) {
        return requestAddPage(path, Object.assign({}, data, {
          confirm_activation: true
        }));
      }
      throw error;
    });
  }

  /**
   * Initializes the "ADD HOMEPAGE" button.
   *
   * Sends a POST request to the RocketCDN REST endpoint to add
   * the site homepage as a free-tier CDN page.
   */
  function initAddHomepage() {
    document.addEventListener('click', event => {
      const button = event.target.closest('#wpr_add_page_component .wpr-cdn-add-page__homepage');
      if (!button) {
        return;
      }
      button.disabled = true;
      const builtIn = document.querySelector('.wpr-cdn-built-in');
      if (builtIn) {
        builtIn.classList.add('wpr-cdn-built-in--disabled');
      }
      requestAddPage('/wp-rocket/v1/rocketcdn/pages/homepage', {}).then(response => {
        button.classList.add('wpr-isHidden');
        updateRocketCtaState(response.count, response.limit);
        if (builtIn) {
          builtIn.classList.remove('wpr-cdn-built-in--disabled');
        }
        if (response.items_html) {
          const existing = document.querySelector('.wpr-cdn-built-in .wpr-table-list');
          if (existing) {
            existing.remove();
          }
          const addPageSection = document.querySelector('.wpr-cdn-add-page');
          if (addPageSection) {
            addPageSection.insertAdjacentHTML('beforebegin', response.items_html);
          }
        }

        // Track banner view when first page is added and banner becomes visible.
        if (1 === response.count) {
          document.dispatchEvent(new CustomEvent('rocketCDNBannerFirstVisible'));
        }
        refreshUIElements(response);
      }).catch(() => {
        button.disabled = false;
        if (builtIn) {
          builtIn.classList.remove('wpr-cdn-built-in--disabled');
        }
      });
    });
  }
  /**
   * Initializes the "ADD PAGE" input and button.
   *
   * Sends a POST request to the RocketCDN REST endpoint to add
   * a page URL to the free-tier CDN page list.
   */
  function initAddPage() {
    const input = document.getElementById('wpr_cdn_add_page_input');
    const button = document.querySelector('.wpr-cdn-add-page__button');
    if (!input || !button) {
      return;
    }
    function isValidUrl(input) {
      try {
        const url = new URL(input);
        return url.hostname.includes('.') && url.hostname.split('.').pop().length > 0;
      } catch {
        return false;
      }
    }
    function submitPage() {
      const url = input.value.trim();
      if (!isValidUrl(url)) {
        alert('Please enter a valid URL');
        return;
      }

      // Prevent duplicate request while request is in flight.
      input.disabled = true;
      button.disabled = true;
      const builtIn = document.querySelector('.wpr-cdn-built-in');
      if (builtIn) {
        builtIn.classList.add('wpr-cdn-built-in--disabled');
      }
      requestAddPage('/wp-rocket/v1/rocketcdn/pages', {
        url
      }).then(response => {
        input.value = '';
        input.disabled = false;
        button.disabled = false;
        addHomeButton.classList.add('wpr-isHidden');
        updateRocketCtaState(response.count, response.limit);
        if (builtIn) {
          builtIn.classList.remove('wpr-cdn-built-in--disabled');
        }

        // Update page list with response.
        if (response.items_html) {
          const existing = document.querySelector('.wpr-cdn-built-in .wpr-table-list');
          if (existing) {
            existing.remove();
          }
          const addPageSection = document.querySelector('.wpr-cdn-add-page');
          if (addPageSection) {
            addPageSection.insertAdjacentHTML('beforebegin', response.items_html);
          }
        }

        // Track banner view when first page is added and banner becomes visible.
        if (1 === response.count) {
          document.dispatchEvent(new CustomEvent('rocketCDNBannerFirstVisible'));
        }
        if (response.limit === response.count) {
          // Disable input and button when page limit is reached.
          document.querySelector('.wpr-cdn-built-in').classList.add('wpr-cdn-built-in--disabled');
          const addPageWrapper = document.querySelector('.wpr-cdn-add-page__button-wrapper');
          if (addPageWrapper) {
            addPageWrapper.classList.add('wpr-btn-with-tool-tip');
          }
          const addPageBtn = document.querySelector('.wpr-cdn-add-page__button');
          if (addPageBtn) {
            addPageBtn.disabled = true;
          }
          updateTooltipState(true);
          document.dispatchEvent(new CustomEvent('rocketCDNBannerAutoExpanded'));
        }
        refreshUIElements(response);
      }).catch(() => {
        input.disabled = false;
        button.disabled = false;
        if (builtIn) {
          builtIn.classList.remove('wpr-cdn-built-in--disabled');
        }
      });
    }
    button.addEventListener('click', submitPage);
    input.addEventListener('keydown', e => {
      if ('Enter' === e.key) {
        e.preventDefault();
        submitPage();
      }
    });
  }
  function refreshUIElements(response) {
    // Set subscription loading state when first page is added.
    if (response.is_subscription_creation_loading) {
      setSubscriptionLoadingState();
    }

    // Update status indicator component.
    updateStatusIndicatorComponent(response.status_indicator_html);
  }

  /**
   * Initializes delete buttons for CDN page rows.
   *
   * Uses event delegation on the built-in CDN container to handle
   * clicks on dynamically added delete buttons.
   */
  function initDeletePage() {
    const container = document.querySelector('#wpr_add_page_component');
    if (!container) {
      return;
    }
    container.parentElement.addEventListener('click', e => {
      const button = e.target.closest('.wpr-table-list__delete');
      if (!button) {
        return;
      }
      const id = button.dataset.id;
      if (!id) {
        return;
      }
      button.disabled = true;
      window.wp.apiFetch({
        path: `/wp-rocket/v1/rocketcdn/pages/${id}`,
        method: 'DELETE'
      }).then(response => {
        updateRocketCtaState(response.count, response.limit);
        if (response.items_html) {
          const existing = container.parentElement.querySelector('.wpr-cdn-built-in .wpr-table-list');
          if (existing) {
            existing.remove();
          }
          const addPageSection = container.parentElement.querySelector('.wpr-cdn-add-page');
          if (addPageSection) {
            addPageSection.insertAdjacentHTML('beforebegin', response.items_html);
          }
        }

        // Show re-add HOMEPAGE button and no-page notice when all pages are deleted.
        if (0 === response.count) {
          // Remove table list component.
          document.querySelector('.wpr-cdn-built-in .wpr-table-list').remove();
          const homepageBtn = container.querySelector('.wpr-cdn-add-page__homepage');
          if (homepageBtn) {
            homepageBtn.classList.remove('wpr-isHidden');
            homepageBtn.disabled = false;
          }
        }
        if (response.limit > response.count) {
          // Re-enable input and button when page limit is not reached.
          document.querySelector('.wpr-cdn-built-in').classList.remove('wpr-cdn-built-in--disabled');
          const addPageWrapper = document.querySelector('.wpr-cdn-add-page__button-wrapper');
          if (addPageWrapper) {
            addPageWrapper.classList.remove('wpr-btn-with-tool-tip');
          }
          const addPageBtn = document.querySelector('.wpr-cdn-add-page__button');
          if (addPageBtn) {
            addPageBtn.disabled = false;
          }
          updateTooltipState(false);

          // Track auto-collapse when deletion drops count just below the limit.
          if (response.count === response.limit - 1) {
            document.dispatchEvent(new CustomEvent('rocketCDNBannerAutoCollapsed'));
          }
        }

        // Update status indicator component.
        updateStatusIndicatorComponent(response.status_indicator_html);
      }).catch(() => {
        button.disabled = false;
      });
    });
  }
})(document);

},{}],6:[function(require,module,exports){
"use strict";

function getTimeRemaining(endtime) {
  const start = Date.now();
  const total = endtime * 1000 - start;
  const seconds = Math.floor(total / 1000 % 60);
  const minutes = Math.floor(total / 1000 / 60 % 60);
  const hours = Math.floor(total / (1000 * 60 * 60) % 24);
  const days = Math.floor(total / (1000 * 60 * 60 * 24));
  return {
    total,
    days,
    hours,
    minutes,
    seconds
  };
}
function initializeClock(id, endtime) {
  const clock = document.getElementById(id);
  if (clock === null) {
    return;
  }
  const daysSpan = clock.querySelector('.rocket-countdown-days');
  const hoursSpan = clock.querySelector('.rocket-countdown-hours');
  const minutesSpan = clock.querySelector('.rocket-countdown-minutes');
  const secondsSpan = clock.querySelector('.rocket-countdown-seconds');
  function updateClock() {
    const t = getTimeRemaining(endtime);
    if (t.total < 0) {
      clearInterval(timeinterval);
      return;
    }
    daysSpan.innerHTML = t.days;
    hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
    minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
    secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
  }
  updateClock();
  const timeinterval = setInterval(updateClock, 1000);
}
function rucssTimer(id, endtime) {
  const timer = document.getElementById(id);
  const notice = document.getElementById('rocket-notice-saas-processing');
  const success = document.getElementById('rocket-notice-saas-success');
  if (timer === null) {
    return;
  }
  function updateTimer() {
    const start = Date.now();
    const remaining = Math.floor((endtime * 1000 - start) / 1000);
    if (remaining <= 0) {
      clearInterval(timerInterval);
      if (notice !== null) {
        notice.classList.add('hidden');
      }
      if (success !== null) {
        success.classList.remove('hidden');
      }
      if (rocket_ajax_data.cron_disabled) {
        return;
      }
      const data = new FormData();
      data.append('action', 'rocket_spawn_cron');
      data.append('nonce', rocket_ajax_data.nonce);
      fetch(ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      });
      return;
    }
    timer.innerHTML = remaining;
  }
  updateTimer();
  const timerInterval = setInterval(updateTimer, 1000);
}
if (!Date.now) {
  Date.now = function now() {
    return new Date().getTime();
  };
}
if (typeof rocket_ajax_data.promo_end !== 'undefined') {
  initializeClock('rocket-promo-countdown', rocket_ajax_data.promo_end);
}
if (typeof rocket_ajax_data.license_expiration !== 'undefined') {
  initializeClock('rocket-renew-countdown', rocket_ajax_data.license_expiration);
}
if (typeof rocket_ajax_data.notice_end_time !== 'undefined') {
  rucssTimer('rocket-rucss-timer', rocket_ajax_data.notice_end_time);
}

},{}],7:[function(require,module,exports){
"use strict";

require("../custom/custom-select.js");
var $ = jQuery;
$(document).ready(function () {
  /***
  * Check parent / show children
  ***/

  function wprShowChildren(aElem) {
    var parentId, $children;
    aElem = $(aElem);
    parentId = aElem.attr('id');
    $children = $('[data-parent="' + parentId + '"]');

    // Test check for switch
    if (aElem.is(':checked')) {
      $children.addClass('wpr-isOpen');
      $children.each(function () {
        if ($(this).find('input[type=checkbox]').is(':checked')) {
          var id = $(this).find('input[type=checkbox]').attr('id');
          $('[data-parent="' + id + '"]').addClass('wpr-isOpen');
        }
      });
    } else {
      $children.removeClass('wpr-isOpen');
      $children.each(function () {
        var id = $(this).find('input[type=checkbox]').attr('id');
        $('[data-parent="' + id + '"]').removeClass('wpr-isOpen');
      });
    }
  }

  /**
   * Tell if the given child field has an active parent field.
   *
   * @param  object $field A jQuery object of a ".wpr-field" field.
   * @return bool|null
   */
  function wprIsParentActive($field) {
    var $parent;
    if (!$field.length) {
      // ¯\_(ツ)_/¯
      return null;
    }
    $parent = $field.data('parent');
    if (typeof $parent !== 'string') {
      // This field has no parent field: then we can display it.
      return true;
    }
    $parent = $parent.replace(/^\s+|\s+$/g, '');
    if ('' === $parent) {
      // This field has no parent field: then we can display it.
      return true;
    }
    $parent = $('#' + $parent);
    if (!$parent.length) {
      // This field's parent is missing: let's consider it's not active then.
      return false;
    }
    if (!$parent.is(':checked') && $parent.is('input')) {
      // This field's parent is checkbox and not checked: don't display the field then.
      return false;
    }
    if (!$parent.hasClass('radio-active') && $parent.is('button')) {
      // This field's parent button and is not active: don't display the field then.
      return false;
    }
    // Go recursive to the last parent.
    return wprIsParentActive($parent.closest('.wpr-field'));
  }

  /**
   * Masks sensitive information in an input field by replacing all but the last 4 characters with asterisks.
   *
   * @param {string} id_selector - The ID of the input field to be masked.
   * @returns {void} - Modifies the input field value in-place.
   *
   * @example
   * // HTML: <input type="text" id="creditCardInput" value="1234567890123456">
   * maskField('creditCardInput');
   * // Result: Updates the input field value to '************3456'.
   */
  function maskField(proxy_selector, concrete_selector) {
    var concrete = {
      'val': concrete_selector.val(),
      'length': concrete_selector.val().length
    };
    if (concrete.length > 4) {
      var hiddenPart = '\u2022'.repeat(Math.max(0, concrete.length - 4));
      var visiblePart = concrete.val.slice(-4);

      // Combine the hidden and visible parts
      var maskedValue = hiddenPart + visiblePart;
      proxy_selector.val(maskedValue);
    }
    // Ensure events are not added more than once
    if (!proxy_selector.data('eventsAttached')) {
      proxy_selector.on('input', handleInput);
      proxy_selector.on('focus', handleFocus);
      proxy_selector.data('eventsAttached', true);
    }

    /**
     * Handle the input event
     */
    function handleInput() {
      var proxyValue = proxy_selector.val();
      if (proxyValue.indexOf('\u2022') === -1) {
        concrete_selector.val(proxyValue);
      }
    }

    /**
     * Handle the focus event
     */
    function handleFocus() {
      var concrete_value = concrete_selector.val();
      proxy_selector.val(concrete_value);
    }
  }

  // Update the concrete field when the proxy is updated.

  maskField($('#cloudflare_api_key_mask'), $('#cloudflare_api_key'));
  maskField($('#cloudflare_zone_id_mask'), $('#cloudflare_zone_id'));

  // Display/Hide children fields on checkbox change.
  $('.wpr-isParent input[type=checkbox]').on('change', function () {
    wprShowChildren($(this));
  });

  // On page load, display the active fields.
  $('.wpr-field--children').each(function () {
    var $field = $(this);
    if (wprIsParentActive($field)) {
      $field.addClass('wpr-isOpen');
    }
  });

  /***
  * Warning fields
  ***/

  var $warningParent = $('.wpr-field--parent');
  var $warningParentInput = $('.wpr-field--parent input[type=checkbox]');

  // If already checked
  $warningParentInput.each(function () {
    wprShowChildren($(this));
  });
  $warningParent.on('change', function () {
    wprShowWarning($(this));
  });
  function wprShowWarning(aElem) {
    var $warningField = aElem.next('.wpr-fieldWarning'),
      $thisCheckbox = aElem.find('input[type=checkbox]'),
      $nextWarning = aElem.parent().next('.wpr-warningContainer'),
      $nextFields = $nextWarning.find('.wpr-field'),
      parentId = aElem.find('input[type=checkbox]').attr('id'),
      $children = $('[data-parent="' + parentId + '"]');

    // Check warning parent
    if ($thisCheckbox.is(':checked')) {
      $warningField.addClass('wpr-isOpen');
      $thisCheckbox.prop('checked', false);
      aElem.trigger('change');
      var $warningButton = $warningField.find('.wpr-button');

      // Validate the warning
      $warningButton.on('click', function () {
        $thisCheckbox.prop('checked', true);
        $warningField.removeClass('wpr-isOpen');
        $children.addClass('wpr-isOpen');

        // If next elem = disabled
        if ($nextWarning.length > 0) {
          $nextFields.removeClass('wpr-isDisabled');
          $nextFields.find('input').prop('disabled', false);
        }
        return false;
      });
    } else {
      $nextFields.addClass('wpr-isDisabled');
      $nextFields.find('input').prop('disabled', true);
      $nextFields.find('input[type=checkbox]').prop('checked', false);
      $children.removeClass('wpr-isOpen');
    }
  }

  /**
   * CNAMES add/remove lines
   */
  $(document).on('click', '.wpr-multiple-close', function (e) {
    e.preventDefault();
    $(this).parent().remove();
  });
  $('.wpr-button--addMulti').on('click', function (e) {
    e.preventDefault();
    $($('#wpr-cname-model').html()).appendTo('#wpr-cnames-list');
  });

  /***
   * Wpr Radio button
   ***/
  var disable_radio_warning = false;
  $(document).on('click', '.wpr-radio-buttons-container button', function (e) {
    e.preventDefault();
    if ($(this).hasClass('radio-active')) {
      return false;
    }
    var $parent = $(this).parents('.wpr-radio-buttons');
    $parent.find('.wpr-radio-buttons-container button').removeClass('radio-active');
    $parent.find('.wpr-extra-fields-container').removeClass('wpr-isOpen');
    $parent.find('.wpr-fieldWarning').removeClass('wpr-isOpen');
    $(this).addClass('radio-active');
    wprShowRadioWarning($(this));
  });
  function wprShowRadioWarning($elm) {
    disable_radio_warning = false;
    $elm.trigger("before_show_radio_warning", [$elm]);
    if (!$elm.hasClass('has-warning') || disable_radio_warning) {
      wprShowRadioButtonChildren($elm);
      $elm.trigger("radio_button_selected", [$elm]);
      return false;
    }
    var $warningField = $('[data-parent="' + $elm.attr('id') + '"].wpr-fieldWarning');
    $warningField.addClass('wpr-isOpen');
    var $warningButton = $warningField.find('.wpr-button');

    // Validate the warning
    $warningButton.on('click', function () {
      $warningField.removeClass('wpr-isOpen');
      wprShowRadioButtonChildren($elm);
      $elm.trigger("radio_button_selected", [$elm]);
      return false;
    });
  }
  function wprShowRadioButtonChildren($elm) {
    var $parent = $elm.parents('.wpr-radio-buttons');
    var $children = $('.wpr-extra-fields-container[data-parent="' + $elm.attr('id') + '"]');
    $children.addClass('wpr-isOpen');
  }

  /***
   * Wpr Optimize Css Delivery Field
   ***/
  var rucssActive = parseInt($('#remove_unused_css').val());
  $("#optimize_css_delivery_method .wpr-radio-buttons-container button").on("radio_button_selected", function (event, $elm) {
    toggleActiveOptimizeCssDeliveryMethod($elm);
  });
  $("#optimize_css_delivery").on("change", function () {
    if ($(this).is(":not(:checked)")) {
      disableOptimizeCssDelivery();
    } else {
      var default_radio_button_id = '#' + $('#optimize_css_delivery_method').data('default');
      $(default_radio_button_id).trigger('click');
    }
  });
  function toggleActiveOptimizeCssDeliveryMethod($elm) {
    var optimize_method = $elm.data('value');
    if ('remove_unused_css' === optimize_method) {
      $('#remove_unused_css').val(1);
      $('#async_css').val(0);
    } else {
      $('#remove_unused_css').val(0);
      $('#async_css').val(1);
    }
  }
  function disableOptimizeCssDelivery() {
    $('#remove_unused_css').val(0);
    $('#async_css').val(0);
  }
  $("#optimize_css_delivery_method .wpr-radio-buttons-container button").on("before_show_radio_warning", function (event, $elm) {
    disable_radio_warning = 'remove_unused_css' === $elm.data('value') && 1 === rucssActive;
  });
  $(".wpr-multiple-select .wpr-list-header").click(function (e) {
    $(e.target).closest('.wpr-multiple-select .wpr-list').toggleClass('open');
  });
  $('.wpr-multiple-select .wpr-checkbox').click(function (e) {
    const checkbox = $(this).find('input');
    const is_checked = checkbox.attr('checked') !== undefined;
    checkbox.attr('checked', is_checked ? null : 'checked');
    const sub_checkboxes = $(checkbox).closest('.wpr-list').find('.wpr-list-body input[type="checkbox"]');
    if (checkbox.hasClass('wpr-main-checkbox')) {
      $.map(sub_checkboxes, checkbox => {
        $(checkbox).attr('checked', is_checked ? null : 'checked');
      });
      return;
    }
    const main_checkbox = $(checkbox).closest('.wpr-list').find('.wpr-main-checkbox');
    const sub_checked = $.map(sub_checkboxes, checkbox => {
      if ($(checkbox).attr('checked') === undefined) {
        return;
      }
      return checkbox;
    });
    main_checkbox.attr('checked', sub_checked.length === sub_checkboxes.length ? 'checked' : null);
  });
  if ($('.wpr-main-checkbox').length > 0) {
    $('.wpr-main-checkbox').each((checkbox_key, checkbox) => {
      let parent_list = $(checkbox).parents('.wpr-list');
      let not_checked = parent_list.find('.wpr-list-body input[type=checkbox]:not(:checked)').length;
      $(checkbox).attr('checked', not_checked <= 0 ? 'checked' : null);
    });
  }
  let checkBoxCounter = {
    checked: {},
    total: {}
  };
  $('.wpr-field--categorizedmultiselect .wpr-list').each(function () {
    // Get the ID of the current element
    let id = $(this).attr('id');
    if (id) {
      checkBoxCounter.checked[id] = $(`#${id} input[type='checkbox']:checked`).length;
      checkBoxCounter.total[id] = $(`#${id} input[type='checkbox']:not(.wpr-main-checkbox)`).length;
      // Update the counter text
      $(`#${id} .wpr-badge-counter span`).text(checkBoxCounter.checked[id]);
      // Show or hide the counter badge based on the count
      $(`#${id} .wpr-badge-counter`).toggle(checkBoxCounter.checked[id] > 0);

      // Check the select all option if all exclusions are checked in a section.
      if (checkBoxCounter.checked[id] === checkBoxCounter.total[id]) {
        $(`#${id} .wpr-main-checkbox`).attr('checked', true);
      }
    }
  });

  /**
   * Delay JS Execution Safe Mode Field
   */
  var $dje_safe_mode_checkbox = $('#delay_js_execution_safe_mode');
  $('#delay_js').on('change', function () {
    if ($(this).is(':not(:checked)') && $dje_safe_mode_checkbox.is(':checked')) {
      $dje_safe_mode_checkbox.trigger('click');
    }
  });
  let stacked_select = document.getElementById('rocket_stacked_select');
  if (stacked_select) {
    stacked_select.addEventListener('custom-select-change', function (event) {
      let selected_option = $(event.detail.selectedOption);
      let name = selected_option.data('name');
      let saving = selected_option.data('saving');
      let regular_price = selected_option.data('regular-price');
      let price = selected_option.data('price');
      let url = selected_option.data('url');
      let parent_item = $(this).parents('.wpr-upgrade-item');
      if (saving) {
        parent_item.find('.wpr-upgrade-saving span').html(saving);
      }
      if (name) {
        parent_item.find('.wpr-upgrade-title').html(name);
      }
      if (regular_price) {
        parent_item.find('.wpr-upgrade-price-regular span').html(regular_price);
      }
      if (price) {
        parent_item.find('.wpr-upgrade-price-value').html(price);
      }
      if (url) {
        parent_item.find('.wpr-upgrade-link').attr('href', url);
      }
    });
  }
  $(document).on('click', '.wpr-confirm-delete', function (e) {
    return confirm($(this).data('wpr_confirm_msg'));
  });
});

},{"../custom/custom-select.js":1}],8:[function(require,module,exports){
"use strict";

var $ = jQuery;
$(document).ready(function () {
  /***
  * Dashboard notice
  ***/

  var $notice = $('.wpr-notice');
  var $noticeClose = $('#wpr-congratulations-notice');
  $noticeClose.on('click', function () {
    wprCloseDashboardNotice();
    return false;
  });
  function wprCloseDashboardNotice() {
    var vTL = new TimelineLite().to($notice, 1, {
      autoAlpha: 0,
      x: 40,
      ease: Power4.easeOut
    }).to($notice, 0.6, {
      height: 0,
      marginTop: 0,
      ease: Power4.easeOut
    }, '=-.4').set($notice, {
      'display': 'none'
    });
  }

  /**
   * Rocket Analytics notice info collect
   */
  $('.rocket-analytics-data-container').hide();
  $('.rocket-preview-analytics-data').on('click', function (e) {
    e.preventDefault();
    $(this).parent().next('.rocket-analytics-data-container').toggle();
  });

  /***
  * Hide / show Rocket addon tabs.
  ***/

  $('.wpr-toggle-button').each(function () {
    var $button = $(this);
    var $checkbox = $button.closest('.wpr-fieldsContainer-fieldset').find('.wpr-radio :checkbox');
    var $menuItem = $('[href="' + $button.attr('href') + '"].wpr-menuItem');
    $checkbox.on('change', function () {
      if ($checkbox.is(':checked')) {
        $menuItem.css('display', 'block');
        $button.css('display', 'inline-block');
      } else {
        $menuItem.css('display', 'none');
        $button.css('display', 'none');
      }
    }).trigger('change');
  });

  /***
  * Help Button Tracking
  ***/

  // Track clicks on various help elements with data attributes
  $(document).on('click', '[data-wpr_track_help]', function (e) {
    if (typeof window.wprTrackHelpButton === 'function') {
      var $el = $(this);
      var button = $el.data('wpr_track_help');
      var context = $el.data('wpr_track_context') || '';
      window.wprTrackHelpButton(button, context);
    }
  });

  // Track specific help resource clicks with explicit selectors
  $(document).on('click', '.wistia_embed', function () {
    if (typeof window.wprTrackHelpButton === 'function') {
      var title = $(this).text() || 'Getting Started Video';
      window.wprTrackHelpButton(title, 'Getting Started');
    }
  });

  // Track FAQ links
  $(document).on('click', 'a[data-beacon-article]', function () {
    if (typeof window.wprTrackHelpButton === 'function') {
      var href = $(this).attr('href');
      var text = $(this).text();

      // Check if it's in FAQ section or sidebar documentation
      if ($(this).closest('.wpr-fieldsContainer-fieldset').prev('.wpr-optionHeader').find('.wpr-title2').text().includes('Frequently Asked Questions')) {
        window.wprTrackHelpButton('FAQ - ' + text, 'Dashboard');
      } else if ($(this).closest('.wpr-documentation').length > 0) {
        window.wprTrackHelpButton('Documentation', 'Sidebar');
      } else {
        window.wprTrackHelpButton('Documentation Link', 'General');
      }
    }
  });

  // Track "How to measure loading time" link
  $(document).on('click', 'a[href*="how-to-test-wordpress-site-performance"]', function () {
    if (typeof window.wprTrackHelpButton === 'function') {
      window.wprTrackHelpButton('Loading Time Guide', 'Sidebar');
    }
  });

  // Track "Need help?" links (existing help buttons)
  $(document).on('click', '.wpr-infoAction--help:not([data-beacon-id])', function () {
    if (typeof window.wprTrackHelpButton === 'function') {
      window.wprTrackHelpButton('Need Help', 'General');
    }
  });

  /***
  * Show popin analytics
  ***/

  var $wprAnalyticsPopin = $('.wpr-Popin-Analytics'),
    $wprPopinOverlay = $('.wpr-Popin-overlay'),
    $wprAnalyticsClosePopin = $('.wpr-Popin-Analytics-close'),
    $wprAnalyticsPopinButton = $('.wpr-Popin-Analytics .wpr-button'),
    $wprAnalyticsOpenPopin = $('.wpr-js-popin');
  $wprAnalyticsOpenPopin.on('click', function (e) {
    e.preventDefault();
    wprOpenAnalytics();
    return false;
  });
  $wprAnalyticsClosePopin.on('click', function (e) {
    e.preventDefault();
    wprCloseAnalytics();
    return false;
  });
  $wprAnalyticsPopinButton.on('click', function (e) {
    e.preventDefault();
    wprActivateAnalytics();
    return false;
  });
  function wprOpenAnalytics() {
    var vTL = new TimelineLite().set($wprAnalyticsPopin, {
      'display': 'block'
    }).set($wprPopinOverlay, {
      'display': 'block'
    }).fromTo($wprPopinOverlay, 0.6, {
      autoAlpha: 0
    }, {
      autoAlpha: 1,
      ease: Power4.easeOut
    }).fromTo($wprAnalyticsPopin, 0.6, {
      autoAlpha: 0,
      marginTop: -24
    }, {
      autoAlpha: 1,
      marginTop: 0,
      ease: Power4.easeOut
    }, '=-.5');
  }
  function wprCloseAnalytics() {
    var vTL = new TimelineLite().fromTo($wprAnalyticsPopin, 0.6, {
      autoAlpha: 1,
      marginTop: 0
    }, {
      autoAlpha: 0,
      marginTop: -24,
      ease: Power4.easeOut
    }).fromTo($wprPopinOverlay, 0.6, {
      autoAlpha: 1
    }, {
      autoAlpha: 0,
      ease: Power4.easeOut
    }, '=-.5').set($wprAnalyticsPopin, {
      'display': 'none'
    }).set($wprPopinOverlay, {
      'display': 'none'
    });
  }
  function wprActivateAnalytics() {
    wprCloseAnalytics();
    $('#analytics_enabled').prop('checked', true);
    var analyticsEnabled = document.getElementById('analytics_enabled');
    if (analyticsEnabled) {
      var changeEvent = new Event('change', {
        bubbles: true
      });
      analyticsEnabled.dispatchEvent(changeEvent);
    }
  }

  // Display CTA within the popin `What info will we collect?`
  $('#analytics_enabled').on('change', function () {
    $('.wpr-rocket-analytics-cta').toggleClass('wpr-isHidden');
  });

  /***
  * Show popin upgrade
  ***/

  var $wprUpgradePopin = $('.wpr-Popin-Upgrade'),
    $wprUpgradeClosePopin = $('.wpr-Popin-Upgrade-close'),
    $wprUpgradeOpenPopin = $('.wpr-popin-upgrade-toggle');
  $wprUpgradeOpenPopin.on('click', function (e) {
    e.preventDefault();
    wprOpenUpgradePopin();
    return false;
  });
  $wprUpgradeClosePopin.on('click', function () {
    wprCloseUpgradePopin();
    return false;
  });
  function wprOpenUpgradePopin() {
    var vTL = new TimelineLite();
    vTL.set($wprUpgradePopin, {
      'display': 'block'
    }).set($wprPopinOverlay, {
      'display': 'block'
    }).fromTo($wprPopinOverlay, 0.6, {
      autoAlpha: 0
    }, {
      autoAlpha: 1,
      ease: Power4.easeOut
    }).fromTo($wprUpgradePopin, 0.6, {
      autoAlpha: 0,
      marginTop: -24
    }, {
      autoAlpha: 1,
      marginTop: 0,
      ease: Power4.easeOut
    }, '=-.5');
  }
  function wprCloseUpgradePopin() {
    var vTL = new TimelineLite();
    vTL.fromTo($wprUpgradePopin, 0.6, {
      autoAlpha: 1,
      marginTop: 0
    }, {
      autoAlpha: 0,
      marginTop: -24,
      ease: Power4.easeOut
    }).fromTo($wprPopinOverlay, 0.6, {
      autoAlpha: 1
    }, {
      autoAlpha: 0,
      ease: Power4.easeOut
    }, '=-.5').set($wprUpgradePopin, {
      'display': 'none'
    }).set($wprPopinOverlay, {
      'display': 'none'
    });
  }

  /***
  * Sidebar on/off
  ***/
  var $wprSidebar = $('.wpr-Sidebar');
  var $wprButtonTips = $('.wpr-js-tips');
  $wprButtonTips.on('change', function () {
    wprDetectTips($(this));
  });
  function wprDetectTips(aElem) {
    if (aElem.is(':checked')) {
      $wprSidebar.css('display', 'block');
      localStorage.setItem('wpr-show-sidebar', 'on');
    } else {
      $wprSidebar.css('display', 'none');
      localStorage.setItem('wpr-show-sidebar', 'off');
    }
  }

  /***
  * Detect Adblock
  ***/

  if (document.getElementById('LKgOcCRpwmAj')) {
    $('.wpr-adblock').css('display', 'none');
  } else {
    $('.wpr-adblock').css('display', 'block');
  }
  var $adblock = $('.wpr-adblock');
  var $adblockClose = $('.wpr-adblock-close');
  $adblockClose.on('click', function () {
    wprCloseAdblockNotice();
    return false;
  });
  function wprCloseAdblockNotice() {
    var vTL = new TimelineLite().to($adblock, 1, {
      autoAlpha: 0,
      x: 40,
      ease: Power4.easeOut
    }).to($adblock, 0.4, {
      height: 0,
      marginTop: 0,
      ease: Power4.easeOut
    }, '=-.4').set($adblock, {
      'display': 'none'
    });
  }

  // Handle expand/collapse of recommendations list.
  $(document).on('click', '#wpr-recommendations-load-more', function () {
    let $list = $('.wpr-recommendations__list');
    let $hiddenItems = $list.find('.wpr-recommendation-item:gt(2)');

    // Track Load More button click
    if (typeof window.wprTrackHelpButton === 'function') {
      window.wprTrackHelpButton('rocket insights recommendations load more', 'load_more');
    }
    if ($list.hasClass('is-expanded')) {
      $hiddenItems.slideUp(300, function () {
        $list.removeClass('is-expanded');
      });
      $(this).removeClass('is-expanded');
      return;
    }
    $list.addClass('is-expanded');
    $hiddenItems.slideDown(300);
    $(this).addClass('is-expanded');
  });

  // Track Rocket Insights Recommendation Activate button clicks
  $(document).on('click', '.wpr-recommendation-item__activate', function () {
    var recommendation = $(this).data('recommendation') || 'unknown';

    // If the element is visible, scroll to the element with id matching the recommendation value.
    // Delay scroll by 100ms to allow navigation to the section to complete and ensure the target element is in view.
    setTimeout(function () {
      var $target = $(`#${recommendation}`);
      if (!$target.length && !$target.is(':visible')) {
        return;
      }
      $target[0].scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }, 100);

    // Track directly with Mixpanel
    if (typeof mixpanel !== 'undefined' && mixpanel.track) {
      // Check if user has opted in
      if (typeof rocket_mixpanel_data !== 'undefined' && rocket_mixpanel_data.optin_enabled && rocket_mixpanel_data.optin_enabled !== '0') {
        // Identify user if available
        if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
          mixpanel.identify(rocket_mixpanel_data.user_id);
        }

        // Track the Button Clicked event
        mixpanel.track('Button Clicked', {
          'button': 'rocket insights recommendation',
          'recommendation': recommendation,
          'plugin': rocket_mixpanel_data.plugin,
          'brand': rocket_mixpanel_data.brand,
          'application': rocket_mixpanel_data.app,
          'context': rocket_mixpanel_data.context,
          'path': rocket_mixpanel_data.path,
          'interaction_channel': 'UI'
        });
      }
    }
  });

  // Track CTA clicks for Rocket Insights in the settings saved notice.
  $(document).on('click', '#rocket_ri_new_test_save_settings_link', function () {
    // Track directly with Mixpanel
    if (typeof mixpanel === 'undefined' || !mixpanel.track) {
      return;
    }

    // Check if user has opted in
    if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
      return;
    }

    // Identify user if available
    if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
      mixpanel.identify(rocket_mixpanel_data.user_id);
    }

    // Track the Button Clicked event
    mixpanel.track('Rocket Insights CTA click from settings notice', {
      'button': 'CTA on save settings notice',
      'source': 'Settings Saved Notice',
      'plugin': rocket_mixpanel_data.plugin,
      'brand': rocket_mixpanel_data.brand,
      'application': rocket_mixpanel_data.app,
      'context': rocket_mixpanel_data.context,
      'path': rocket_mixpanel_data.path,
      'interaction_channel': 'UI'
    });
  });
});

},{}],9:[function(require,module,exports){
"use strict";

function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == typeof i ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != typeof t || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != typeof i) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
class RocketMixpanel {
  constructor(config) {
    _defineProperty(this, "trackedTabs", ['dashboard', 'rocket_insights', 'page_cdn', 'file_optimization', 'media', 'preload', 'advanced_cache', 'database', 'heartbeat', 'addons', 'imagify', 'tutorials', 'plugins', 'tools']);
    this.config = config;
  }

  /**
   * Initializes the handler.
   */
  init() {
    if (typeof mixpanel === 'undefined' || !mixpanel.track) {
      return;
    }
    if (!this.config.optin_enabled || this.config.optin_enabled === '0') {
      return;
    }
    mixpanel.identify(this.config.user_id);
    this._initListeners(this);
  }

  /**
   * Initializes the event listeners.
   *
   * @param self instance of this object, used for binding "this" to the listeners.
   */
  _initListeners(self) {
    window.addEventListener('hashchange', self._onHashChange.bind(self));
    window.addEventListener('load', self._onPageLoad.bind(self));
  }

  /**
   * Event listener when the hash changed in a page.
   *
   * @param Event event Event instance.
   */
  _onHashChange(event) {
    const oldHash = this._cleanHash(new URL(event.oldURL).hash);
    const newHash = this._cleanHash(new URL(event.newURL).hash);
    if (!this._canTrackTab(newHash)) {
      return;
    }
    this._sendPageViewedEvent(this._getSource(oldHash), newHash);
  }
  _onPageLoad() {
    const newHash = this._cleanHash(window.location.hash);
    if (!this._canTrackTab(newHash)) {
      return;
    }
    this._sendPageViewedEvent(this._getSource(), newHash);
  }
  _cleanHash(tabHash) {
    if (!tabHash || !tabHash.startsWith('#')) {
      return tabHash;
    }
    return tabHash.substring(1);
  }
  _canTrackTab(tabHash) {
    return this.trackedTabs.includes(tabHash);
  }
  _getSource(oldHash = '') {
    if (oldHash) {
      return `settings_${oldHash}`;
    }
    let source = this._getSourceFromQueryStringAndRemoveIt();
    if (source) {
      return source;
    }
    return this._getSourceFromReferrer();
  }
  _getSourceFromQueryStringAndRemoveIt() {
    const url = new URL(window.location.href);
    const urlParams = url.searchParams;

    // 1. Check for explicit source param
    if (!urlParams.has('rocket_source')) {
      return '';
    }

    // 2. Get the value
    const sourceValue = urlParams.get('rocket_source');

    // 3. Delete the parameter from the URLSearchParams object
    urlParams.delete('rocket_source');

    // 4. Update the browser's URL using the History API
    // This removes the parameter from the URL bar without reloading the page.
    window.history.replaceState(null, '', url.search ? url.href : url.pathname);

    // 5. Return the retrieved value
    return sourceValue;
  }
  _getSourceFromReferrer() {
    const referrer = document.referrer;
    if (!referrer) {
      return 'noreferrer';
    }
    if (!referrer.includes(window.location.hostname)) {
      return 'external';
    }
    return 'internal';
  }
  _sendPageViewedEvent(source, newHash) {
    mixpanel.track('Page Viewed', {
      path: `/wp-admin/options-general.php?page=wprocket#${newHash}`,
      page_name: newHash.replace('_', ' '),
      source: source,
      plugin: rocket_mixpanel_data.plugin,
      brand: rocket_mixpanel_data.brand,
      application: rocket_mixpanel_data.app,
      context: rocket_mixpanel_data.context,
      interaction_channel: 'UI'
    });
  }

  /**
   * Named static constructor to encapsulate how to create the object.
   */
  static run() {
    // Bail out if the configuration not passed from the server.
    if (typeof rocket_mixpanel_data === 'undefined') {
      return;
    }
    const instance = new RocketMixpanel(rocket_mixpanel_data);
    instance.init();
  }
}
RocketMixpanel.run();

},{}],10:[function(require,module,exports){
"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var $pageManager = document.querySelector(".wpr-Content");
  if ($pageManager) {
    new PageManager($pageManager);
  }
});

/*-----------------------------------------------*\
		CLASS PAGEMANAGER
\*-----------------------------------------------*/
/**
 * Manages the display of pages / section for WP Rocket plugin
 *
 * Public method :
     detectID - Detect ID with hash
     getBodyTop - Get body top position
	 change - Displays the corresponding page
 *
 */

function PageManager(aElem) {
  var refThis = this;
  this.$body = document.querySelector('.wpr-body');
  this.$menuItems = document.querySelectorAll('.wpr-menuItem');
  this.$submitButton = document.querySelector('.wpr-Content > form > #wpr-options-submit');
  this.$pages = document.querySelectorAll('.wpr-Page');
  this.$sidebar = document.querySelector('.wpr-Sidebar');
  this.$content = document.querySelector('.wpr-Content');
  this.$tips = document.querySelector('.wpr-Content-tips');
  this.$links = document.querySelectorAll('.wpr-body a');
  this.$menuItem = null;
  this.$page = null;
  this.pageId = null;
  this.bodyTop = 0;
  this.buttonText = this.$submitButton.value;
  refThis.getBodyTop();

  // If url page change
  window.onhashchange = function () {
    refThis.detectID();
  };

  // If hash already exist (after refresh page for example)
  if (window.location.hash) {
    this.bodyTop = 0;
    this.detectID();
  } else {
    var session = localStorage.getItem('wpr-hash');
    this.bodyTop = 0;
    if (session) {
      window.location.hash = session;
      this.detectID();
    } else {
      this.$menuItems[0].classList.add('isActive');
      localStorage.setItem('wpr-hash', 'dashboard');
      window.location.hash = '#dashboard';
    }
  }

  // Click link same hash
  for (var i = 0; i < this.$links.length; i++) {
    this.$links[i].onclick = function () {
      refThis.getBodyTop();
      var hrefSplit = this.href.split('#')[1];
      if (hrefSplit == refThis.pageId && hrefSplit != undefined) {
        refThis.detectID();
        return false;
      }
    };
  }

  // Click links not WP rocket to reset hash
  var $otherlinks = document.querySelectorAll('#adminmenumain a, #wpadminbar a');
  for (var i = 0; i < $otherlinks.length; i++) {
    $otherlinks[i].onclick = function () {
      localStorage.setItem('wpr-hash', '');
    };
  }
  document.addEventListener('wpr-cdn-state-change', function () {
    refThis.updateSubmitDisabledState();
  });
}

/*
* Page detect ID
*/
PageManager.prototype.detectID = function () {
  this.pageId = window.location.hash.split('#')[1];
  this.pageId = this.pageId.includes('=') ? this.pageId.split('=')[0] : this.pageId;
  localStorage.setItem('wpr-hash', this.pageId);
  this.$page = document.querySelector('.wpr-Page#' + this.pageId);
  this.$menuItem = document.getElementById('wpr-nav-' + this.pageId);
  this.change();
};

/*
* Get body top position
*/
PageManager.prototype.getBodyTop = function () {
  var bodyPos = this.$body.getBoundingClientRect();
  this.bodyTop = bodyPos.top + window.pageYOffset - 47; // #wpadminbar + padding-top .wpr-wrap - 1 - 47
};

/*
* Page change
*/
PageManager.prototype.change = function () {
  var refThis = this;
  document.documentElement.scrollTop = refThis.bodyTop;

  // Hide other pages
  for (var i = 0; i < this.$pages.length; i++) {
    this.$pages[i].style.display = 'none';
  }
  for (var i = 0; i < this.$menuItems.length; i++) {
    this.$menuItems[i].classList.remove('isActive');
  }

  // Show current default page
  this.$page.style.display = 'block';
  this.$submitButton.style.display = 'block';
  if (null === localStorage.getItem('wpr-show-sidebar')) {
    localStorage.setItem('wpr-show-sidebar', 'on');
  }
  if ('on' === localStorage.getItem('wpr-show-sidebar')) {
    this.$sidebar.style.display = 'flex';
  } else if ('off' === localStorage.getItem('wpr-show-sidebar')) {
    this.$sidebar.style.display = 'none';
    document.querySelector('#wpr-js-tips').removeAttribute('checked');
  }
  this.$tips.style.display = 'block';
  this.$menuItem.classList.add('isActive');
  this.$submitButton.value = this.buttonText;
  this.$content.classList.add('isNotFull');
  const pagesWithoutSubmit = ['dashboard', 'addons', 'database', 'tools', 'addons', 'imagify', 'tutorials', 'plugins'];
  const pagesWithoutSidebarToggle = ['dashboard', 'imagify', 'page_cdn'];

  // Exception for dashboard
  if (this.pageId == "dashboard") {
    this.$sidebar.style.display = 'none';
    this.$content.classList.remove('isNotFull');
  }
  if (this.pageId == "imagify") {
    this.$sidebar.style.display = 'none';
  }
  if (pagesWithoutSidebarToggle.includes(this.pageId)) {
    this.$tips.style.display = 'none';
  }
  if (pagesWithoutSubmit.includes(this.pageId)) {
    this.$submitButton.style.display = 'none';
  }
  this.updateSubmitDisabledState();

  // Dispatch custom event after page navigation for other scripts to hook into.
  document.dispatchEvent(new CustomEvent('rocketJsAfterPageNavigation', {
    detail: {
      pageId: this.pageId,
      submitButton: this.$submitButton
    }
  }));
};

/*
* Update submit button disabled state
*/
PageManager.prototype.updateSubmitDisabledState = function () {
  if (!this.$submitButton || 'none' === this.$submitButton.style.display) {
    return;
  }
  var isCdnPage = 'page_cdn' === this.pageId;
  var pausedRocketCdnBlock = document.querySelector('.wpr-Page#page_cdn .wpr-notice.wpr-ri-notice.wpr-cdn-expired__notice');
  this.$submitButton.disabled = isCdnPage && !!pausedRocketCdnBlock;
};

},{}],11:[function(require,module,exports){
"use strict";

/**
 * Recommendations Widget Handler
 *
 * Listens for Global Score updates and fetches/updates recommendations dynamically.
 */
var $ = jQuery;
$(document).ready(function () {
  /**
   * Updates the recommendations widget UI based on the fetched data.
   *
   * @param {Object} data - The recommendations data from the API.
   * @param {Array} data.recommendations - Array of recommendations details.
   * @param {string} data.recommendations.html - Recommendations HTML.
   */
  function updateRecommendationsWidget(data) {
    const widget = $('.wpr-recommendations');
    if (!widget || !data?.recommendations?.html) {
      return;
    }

    // Update the widget content with the new recommendations HTML
    widget.replaceWith(data?.recommendations?.html);
  }

  /**
   * Fetches the current recommendations status from the REST API.
   */
  function fetchRecommendationsStatus() {
    // Use WordPress REST API client if available
    if (window.wp && window.wp.apiFetch) {
      window.wp.apiFetch({
        path: '/wp-rocket/v1/recommendations'
      }).then(function (data) {
        updateRecommendationsWidget(data);
      }).catch(function (error) {
        console.error('Failed to fetch recommendations status:', error);
      });
    } else {
      // Fallback to fetch API
      fetch(window.wpApiSettings?.root + 'wp-rocket/v1/recommendations', {
        headers: {
          'X-WP-Nonce': window.wpApiSettings?.nonce || ''
        }
      }).then(function (response) {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      }).then(function (data) {
        updateRecommendationsWidget(data);
      }).catch(function (error) {
        console.error('Failed to fetch recommendations status:', error);
      });
    }
  }

  /**
   * Listen for Global Score update event.
   * This is fired by ajax.js when the Global Score polling detects a change.
   */
  $(document).on('wprGlobalScoreUpdated rocket-insights-page-added rocket-insights-page-retest', () => {
    fetchRecommendationsStatus();
  });
});

},{}],12:[function(require,module,exports){
"use strict";

(document => {
  'use strict';

  /**
   * Polls the RocketCDN subscription endpoint until is_loading becomes false.
   */
  class RocketCDNSubscriptionPoller {
    constructor() {
      this.path = '/wp-rocket/v1/rocketcdn/subscription';
      this.pollInterval = 10000; // 10 seconds.
      this.maxRetries = 60; // 10 minutes total.
      this.timerId = null;
      this.retryCount = 0;
      document.addEventListener('rocketCDNSubscriptionLoading', () => this.start());

      // Re-trigger polling after page refresh.
      const classes = ['.wpr-icon-orange-loader', '.wpr-cdn-built-in--disabled'];
      const allPresent = classes.every(cls => document.querySelector(cls) !== null);
      if (allPresent) {
        this.start();
      }
    }

    /**
     * Starts polling.
     */
    start() {
      if (this.timerId) {
        return;
      }
      this.retryCount = 0;
      this.poll();
    }

    /**
     * Stops polling.
     */
    stop() {
      if (this.timerId) {
        clearTimeout(this.timerId);
        this.timerId = null;
      }
    }

    /**
     * Performs a single poll and schedules the next one if still loading.
     */
    poll() {
      if (this.retryCount >= this.maxRetries) {
        this.stop();
        return;
      }
      this.retryCount++;
      window.wp.apiFetch({
        path: this.path,
        method: 'GET'
      }).then(response => {
        if (!response || !response.is_loading) {
          this.stop();
          window.location.reload();
          return;
        }
        this.timerId = setTimeout(() => this.poll(), this.pollInterval);
      }).catch(() => {
        this.timerId = setTimeout(() => this.poll(), this.pollInterval);
      });
    }
  }
  new RocketCDNSubscriptionPoller();
})(document);

},{}],13:[function(require,module,exports){
"use strict";

/*eslint-env es6, browser*/
/* global MicroModal, mixpanel, rocket_mixpanel_data, rocket_ajax_data, ajaxurl */
((document, window) => {
  'use strict';

  const BANNER_STATE = {
    OPENED: false,
    // Big CTA - opened state
    COLLAPSED: true // Small CTA - collapsed state
  };

  // Register early so we catch the wpr-cdn-state-change event.
  document.addEventListener('wpr-cdn-state-change', trackCDNModeSelection);
  document.addEventListener('rocketCDNBannerAutoExpanded', () => trackRocketCDNUpsellBannerExpanded('auto_limit_reached'));
  document.addEventListener('rocketCDNBannerAutoCollapsed', () => trackRocketCDNUpsellBannerCollapsed('auto_limit_released'));
  document.addEventListener('rocketCDNBannerFirstVisible', () => trackRocketCDNUpsellBannerViewed(BANNER_STATE.COLLAPSED));
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wpr-rocketcdn-open').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
        const isCTA = el.classList.contains('wpr-rocketcdn-pricing--cta');
        checkButtonUrlAndOpen(isCTA);
      });
    });

    // Always initialize MicroModal to set up close handlers
    MicroModal.init({
      disableScroll: true
    });
    maybeOpenModalFromURL();

    // Only auto-open modal if there's no direct button URL
    if (!window.rocketcdnButtonUrl || window.rocketcdnButtonUrl === '') {
      maybeOpenModal();
      const iframe = document.getElementById('rocketcdn-iframe');
      const loader = document.getElementById('wpr-rocketcdn-modal-loader');
      if (iframe && loader) {
        iframe.addEventListener('load', function () {
          loader.style.display = 'none';
        });
      }
    }
  });

  /**
   * Checks if the user is currently on the CDN tab.
   *
   * @return {boolean} True if on CDN tab, false otherwise.
   */
  function isOnCDNTab() {
    return window.location.hash === '#page_cdn';
  }
  function maybeTrackBannerView() {
    const smallCTA = document.querySelector('#wpr-rocketcdn-cta-small');
    const bigCTA = document.querySelector('#wpr-rocketcdn-cta');

    // Only track if one of the banners is visible.
    if (bigCTA && !bigCTA.classList.contains('wpr-isHidden')) {
      trackRocketCDNUpsellBannerViewed(BANNER_STATE.OPENED);
    } else if (smallCTA && !smallCTA.classList.contains('wpr-isHidden')) {
      trackRocketCDNUpsellBannerViewed(BANNER_STATE.COLLAPSED);
    }
  }
  window.addEventListener('load', () => {
    let openCTA = document.querySelector('#wpr-rocketcdn-open-cta'),
      closeCTA = document.querySelector('#wpr-rocketcdn-close-cta'),
      smallCTA = document.querySelector('#wpr-rocketcdn-cta-small'),
      bigCTA = document.querySelector('#wpr-rocketcdn-cta'),
      inputToggle = document.querySelector('.wpr-rocketcdn-toggle--input');
    const ctaToggle = document.querySelectorAll('.wpr-rocketcdn-cta-toggle');

    /**
     * Toggles RocketCDN CTA internal collapsed/expanded state.
     *
     * @return {void}
     */
    function toggleBigCTAState() {
      if (!bigCTA || !ctaToggle.length) {
        return;
      }
      const isCollapsed = bigCTA.classList.toggle('wpr-rocketcdn-cta--collapsed');
      bigCTA.classList.toggle('wpr-rocketcdn-cta--expanded', !isCollapsed);
      ctaToggle.forEach(el => {
        el.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
      });
      if (!isCollapsed) {
        trackRocketCDNUpsellBannerExpanded('manual');
      } else {
        trackRocketCDNUpsellBannerCollapsed();
      }
    }
    if (ctaToggle.length && bigCTA) {
      ctaToggle.forEach(el => {
        el.addEventListener('click', toggleBigCTAState);
        el.addEventListener('keydown', event => {
          if ('Enter' === event.key || ' ' === event.key) {
            event.preventDefault();
            toggleBigCTAState();
          }
        });
      });
    }

    // Track banner view on page load if banner is visible and user is on CDN tab.
    maybeTrackBannerView();

    // Track banner view when user navigates to CDN tab.
    window.addEventListener('hashchange', () => {
      maybeTrackBannerView();
      trackCDNModeSelection();
    });

    // Prices selectors for toggling visibility based on the billing cycle toggle state.
    const prices = {
      monthly: {
        regular: document.querySelectorAll('.wpr-rocketcdn-pricing-regular-price--monthly'),
        current: document.querySelectorAll('.wpr-rocketcdn-pricing--monthly'),
        period: document.querySelectorAll('.wpr-rocketcdn-pricing--billing-period--monthly')
      },
      yearly: {
        regular: document.querySelectorAll('.wpr-rocketcdn-pricing-regular-price--yearly'),
        current: document.querySelectorAll('.wpr-rocketcdn-pricing--annual'),
        period: document.querySelectorAll('.wpr-rocketcdn-pricing--billing-period--yearly')
      }
    };

    // Display the correct prices on page based on billing cycle toggle state.
    if (inputToggle) {
      inputToggle.addEventListener('change', function () {
        const isYearly = this.checked;
        if (isYearly) {
          Object.values(prices.monthly).forEach(list => list.forEach(el => el.classList.add('wpr-isHidden')));
          Object.values(prices.yearly).forEach(list => list.forEach(el => el.classList.remove('wpr-isHidden')));
        } else {
          Object.values(prices.monthly).forEach(list => list.forEach(el => el.classList.remove('wpr-isHidden')));
          Object.values(prices.yearly).forEach(list => list.forEach(el => el.classList.add('wpr-isHidden')));
        }

        // Update the button URL with the correct is_monthly parameter.
        updateButtonUrlBillingCycle(isYearly);
      });
    }

    // Track RocketCDN activation failed CTA click
    const activationCTA = document.querySelector('#wpr-rocketcdn-activation-cta');
    if (activationCTA) {
      activationCTA.addEventListener('click', () => {
        trackRocketCDNActivationCTA();
      });
    }
  });
  window.onmessage = e => {
    const iframeURL = rocket_ajax_data.origin_url;
    if (e.origin !== iframeURL) {
      return;
    }
    setCDNFrameHeight(e.data);
    closeModal(e.data);
    tokenHandler(e.data, iframeURL);
    processStatus(e.data);
    enableCDN(e.data, iframeURL);
    disableCDN(e.data, iframeURL);
    validateTokenAndCNAME(e.data);
  };
  function openRocketCDNModal() {
    const rocketcdnIframe = document.getElementById('rocketcdn-iframe');
    if (!rocketcdnIframe || !rocketcdnIframe.dataset || !rocketcdnIframe.dataset.src || rocketcdnIframe.dataset.src === rocketcdnIframe.src) {
      return;
    }
    rocketcdnIframe.src = rocketcdnIframe.dataset.src;
    MicroModal.show('wpr-rocketcdn-modal');
  }
  function checkButtonUrlAndOpen(isCTA) {
    let iframeVisit = !window.rocketcdnButtonUrl;
    // Track CTA click if this is the pricing CTA button.
    if (isCTA) {
      trackRocketCDNUpsellCTAClicked(iframeVisit);
    }

    // Check if button URL was injected by PHP
    if (!iframeVisit) {
      // Small delay to ensure Mixpanel event is sent before navigation
      setTimeout(function () {
        window.location.href = window.rocketcdnButtonUrl;
      }, 100);
    } else {
      // Show iframe modal as usual
      openRocketCDNModal();
    }
  }

  /**
   * Updates the button URL with the correct is_monthly parameter based on billing cycle toggle.
   *
   * @param {boolean} isYearly - True if yearly billing is selected, false for monthly.
   */
  function updateButtonUrlBillingCycle(isYearly) {
    if (!window.rocketcdnButtonUrl || window.rocketcdnButtonUrl === '') {
      return;
    }
    window.rocketcdnButtonUrl = setIsMonthlyParam(window.rocketcdnButtonUrl, isYearly);
  }
  function maybeOpenModal() {
    let postData = '';
    postData += 'action=rocketcdn_process_status';
    postData += '&nonce=' + rocket_ajax_data.nonce;
    const request = sendHTTPRequest(postData);
    request.onreadystatechange = () => {
      if (request.readyState === XMLHttpRequest.DONE && 200 === request.status) {
        let responseTxt = JSON.parse(request.responseText);
        if (true === responseTxt.success) {
          openRocketCDNModal();
        }
      }
    };
  }
  function maybeOpenModalFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('rocketcdn_open_iframe') && '1' === urlParams.get('rocketcdn_open_iframe')) {
      // Set hash to page_cdn to show CDN tab behind modal
      window.location.hash = '#page_cdn';
      openRocketCDNModal();

      // Clean up the URL to prevent re-triggering on refresh
      urlParams.delete('rocketcdn_open_iframe');
      const search = urlParams.toString();
      const newURL = window.location.pathname + (search ? '?' + search : '') + window.location.hash;
      window.history.replaceState({}, '', newURL);
    }
  }
  function closeModal(data) {
    if (!data.hasOwnProperty('cdnFrameClose')) {
      return;
    }
    MicroModal.close('wpr-rocketcdn-modal');
    // Ensure scroll is restored
    document.body.style.overflow = '';
    let pages = ['iframe-payment-success', 'iframe-unsubscribe-success'];
    if (!data.hasOwnProperty('cdn_page_message')) {
      return;
    }
    if (pages.indexOf(data.cdn_page_message) === -1) {
      return;
    }
    document.location.reload();
  }
  function processStatus(data) {
    if (!data.hasOwnProperty('rocketcdn_process')) {
      return;
    }
    let postData = '';
    postData += 'action=rocketcdn_process_set';
    postData += '&status=' + data.rocketcdn_process;
    postData += '&nonce=' + rocket_ajax_data.nonce;
    sendHTTPRequest(postData);
  }
  function enableCDN(data, iframeURL) {
    let iframe = document.querySelector('#rocketcdn-iframe').contentWindow;
    if (!data.hasOwnProperty('rocketcdn_url')) {
      return;
    }
    let postData = '';
    postData += 'action=rocketcdn_enable';
    postData += '&cdn_url=' + data.rocketcdn_url;
    postData += '&nonce=' + rocket_ajax_data.nonce;
    const request = sendHTTPRequest(postData);
    request.onreadystatechange = () => {
      if (request.readyState === XMLHttpRequest.DONE && 200 === request.status) {
        let responseTxt = JSON.parse(request.responseText);
        iframe.postMessage({
          'success': responseTxt.success,
          'data': responseTxt.data,
          'rocketcdn': true
        }, iframeURL);
      }
    };
  }
  function setIsMonthlyParam(url, isYearly) {
    // Remove any existing is_monthly param
    let newUrl = url.replace(/([?&])is_monthly=[^&]*/g, '');
    // Add the new param
    const sep = newUrl.includes('?') ? '&' : '?';
    newUrl += sep + 'is_monthly=' + (isYearly ? '0' : '1');
    return newUrl;
  }
  function disableCDN(data, iframeURL) {
    let iframe = document.querySelector('#rocketcdn-iframe').contentWindow;
    if (!data.hasOwnProperty('rocketcdn_disable')) {
      return;
    }
    let postData = '';
    postData += 'action=rocketcdn_disable';
    postData += '&nonce=' + rocket_ajax_data.nonce;
    const request = sendHTTPRequest(postData);
    request.onreadystatechange = () => {
      if (request.readyState === XMLHttpRequest.DONE && 200 === request.status) {
        let responseTxt = JSON.parse(request.responseText);
        iframe.postMessage({
          'success': responseTxt.success,
          'data': responseTxt.data,
          'rocketcdn': true
        }, iframeURL);
      }
    };
  }
  function sendHTTPRequest(postData) {
    const httpRequest = new XMLHttpRequest();
    httpRequest.open('POST', ajaxurl);
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    httpRequest.send(postData);
    return httpRequest;
  }
  function setCDNFrameHeight(data) {
    if (!data.hasOwnProperty('cdnFrameHeight')) {
      return;
    }
    document.getElementById('rocketcdn-iframe').style.height = `${data.cdnFrameHeight}px`;
  }
  function tokenHandler(data, iframeURL) {
    let iframe = document.querySelector('#rocketcdn-iframe').contentWindow;
    if (!data.hasOwnProperty('rocketcdn_token')) {
      let data = {
        process: "subscribe",
        message: "token_not_received"
      };
      iframe.postMessage({
        'success': false,
        'data': data,
        'rocketcdn': true
      }, iframeURL);
      return;
    }
    let postData = '';
    postData += 'action=save_rocketcdn_token';
    postData += '&value=' + data.rocketcdn_token;
    postData += '&nonce=' + rocket_ajax_data.nonce;
    const request = sendHTTPRequest(postData);
    request.onreadystatechange = () => {
      if (request.readyState === XMLHttpRequest.DONE && 200 === request.status) {
        let responseTxt = JSON.parse(request.responseText);
        iframe.postMessage({
          'success': responseTxt.success,
          'data': responseTxt.data,
          'rocketcdn': true
        }, iframeURL);
      }
    };
  }
  function validateTokenAndCNAME(data) {
    if (!data.hasOwnProperty('rocketcdn_validate_token') || !data.hasOwnProperty('rocketcdn_validate_cname')) {
      return;
    }
    let postData = '';
    postData += 'action=rocketcdn_validate_token_cname';
    postData += '&cdn_url=' + data.rocketcdn_validate_cname;
    postData += '&cdn_token=' + data.rocketcdn_validate_token;
    postData += '&nonce=' + rocket_ajax_data.nonce;
    const request = sendHTTPRequest(postData);
  }

  /**
   * Tracks CDN mode selection with Mixpanel.
   */
  function trackCDNModeSelection() {
    if (!isOnCDNTab()) {
      return;
    }
    const activeTab = document.querySelector('.wpr-cdn-tabs__tab--active');
    if (!activeTab) {
      return;
    }
    const cdnMode = activeTab.getAttribute('data-cdn-mode');
    if (!cdnMode) {
      return;
    }
    if (typeof mixpanel === 'undefined' || !mixpanel.track) {
      return;
    }

    // Check if user has opted in
    if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
      return;
    }

    // Identify user if available
    if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
      mixpanel.identify(rocket_mixpanel_data.user_id);
    }
    mixpanel.track('RocketCDN Mode', {
      context: rocket_mixpanel_data.context,
      plugin: rocket_mixpanel_data.plugin,
      brand: rocket_mixpanel_data.brand,
      application: rocket_mixpanel_data.app,
      cdn_mode: cdnMode,
      interaction_channel: 'UI'
    });
  }

  /**
   * Tracks RocketCDN activation failed CTA click with Mixpanel.
   */
  function trackRocketCDNActivationCTA() {
    if (typeof mixpanel === 'undefined' || !mixpanel.track) {
      return;
    }

    // Check if user has opted in
    if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
      return;
    }

    // Identify user if available
    if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
      mixpanel.identify(rocket_mixpanel_data.user_id);
    }
    mixpanel.track('RocketCDN Activation Failed CTA Clicked', {
      context: rocket_mixpanel_data.context,
      plugin: rocket_mixpanel_data.plugin,
      brand: rocket_mixpanel_data.brand,
      application: rocket_mixpanel_data.app
    });
  }

  /**
   * Tracks a RocketCDN upsell event with Mixpanel.
   *
   * @param {string} eventName   The Mixpanel event name.
   * @param {Object} [extraProps] Optional additional properties to merge.
   */
  function trackRocketCDNUpsellMixpanelEvent(eventName, extraProps) {
    if (typeof mixpanel === 'undefined' || !mixpanel.track) {
      return;
    }

    // Check if user has opted in.
    if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
      return;
    }

    // Identify user if available.
    if (!rocket_mixpanel_data.user_id || typeof mixpanel.identify !== 'function') {
      return;
    }
    mixpanel.identify(rocket_mixpanel_data.user_id);
    var props = {
      context: rocket_mixpanel_data.context,
      plugin: rocket_mixpanel_data.plugin,
      brand: rocket_mixpanel_data.brand,
      application: rocket_mixpanel_data.app,
      path: rocket_mixpanel_data.path,
      interaction_channel: 'UI'
    };

    // Merge extra properties if provided and valid.
    if (extraProps && typeof extraProps === 'object') {
      for (var key in extraProps) {
        if (Object.prototype.hasOwnProperty.call(extraProps, key)) {
          props[key] = extraProps[key];
        }
      }
    }
    mixpanel.track(eventName, props);
  }

  /**
   * Tracks RocketCDN upsell banner view with Mixpanel.
   *
   * @param {boolean} [is_collapsed=false] Whether the small banner variant is displayed, Sends `collapsed` when true, otherwise `opened`.
   */
  function trackRocketCDNUpsellBannerViewed(is_collapsed = false) {
    if (!isOnCDNTab()) {
      return;
    }
    const hash = window.location.hash;
    const basePath = typeof rocket_mixpanel_data !== 'undefined' && rocket_mixpanel_data.path ? rocket_mixpanel_data.path : '';
    trackRocketCDNUpsellMixpanelEvent('RocketCDN Upsell Banner Viewed', {
      state: is_collapsed ? 'collapsed' : 'opened',
      page_name: hash,
      path: basePath + hash
    });
  }

  /**
   * Tracks RocketCDN upsell banner expanded with Mixpanel.
   *
   * @param {string} trigger 'manual' by default.
   */
  function trackRocketCDNUpsellBannerExpanded(trigger) {
    trackRocketCDNUpsellMixpanelEvent('RocketCDN Upsell Banner Expanded', {
      location: window.location.hash,
      trigger: trigger
    });
  }

  /**
   * Tracks RocketCDN upsell banner collapsed with Mixpanel.
   *
   * @param {string} [trigger='manual'] 'manual' when user clicks toggle, 'auto_limit_released' when a page deletion drops count below limit.
   */
  function trackRocketCDNUpsellBannerCollapsed(trigger = 'manual') {
    trackRocketCDNUpsellMixpanelEvent('RocketCDN Upsell Banner Collapsed', {
      location: window.location.hash,
      trigger: trigger
    });
  }

  /**
   * Tracks RocketCDN upsell CTA click with Mixpanel.
   */
  function trackRocketCDNUpsellCTAClicked(iframeVisit = false) {
    const tableList = document.querySelector('.wpr-cdn-built-in .wpr-table-list');
    const pagesCount = tableList ? tableList.querySelectorAll('[data-id]').length : 0;
    trackRocketCDNUpsellMixpanelEvent('RocketCDN Upsell CTA Clicked', {
      destination: iframeVisit ? 'iframe' : 'express-checkout',
      pages_count: pagesCount
    });
  }
})(document, window);

},{}],14:[function(require,module,exports){
"use strict";

/*!
 * VERSION: 1.12.1
 * DATE: 2014-06-26
 * UPDATES AND DOCS AT: http://www.greensock.com
 *
 * @license Copyright (c) 2008-2014, GreenSock. All rights reserved.
 * This work is subject to the terms at http://www.greensock.com/terms_of_use.html or for
 * Club GreenSock members, the software agreement that was issued with your membership.
 * 
 * @author: Jack Doyle, jack@greensock.com
 */
(window._gsQueue || (window._gsQueue = [])).push(function () {
  "use strict";

  window._gsDefine("TimelineLite", ["core.Animation", "core.SimpleTimeline", "TweenLite"], function (t, e, i) {
    var s = function (t) {
        e.call(this, t), this._labels = {}, this.autoRemoveChildren = this.vars.autoRemoveChildren === !0, this.smoothChildTiming = this.vars.smoothChildTiming === !0, this._sortChildren = !0, this._onUpdate = this.vars.onUpdate;
        var i,
          s,
          r = this.vars;
        for (s in r) i = r[s], a(i) && -1 !== i.join("").indexOf("{self}") && (r[s] = this._swapSelfInParams(i));
        a(r.tweens) && this.add(r.tweens, 0, r.align, r.stagger);
      },
      r = 1e-10,
      n = i._internals.isSelector,
      a = i._internals.isArray,
      o = [],
      h = window._gsDefine.globals,
      l = function (t) {
        var e,
          i = {};
        for (e in t) i[e] = t[e];
        return i;
      },
      _ = function (t, e, i, s) {
        t._timeline.pause(t._startTime), e && e.apply(s || t._timeline, i || o);
      },
      u = o.slice,
      f = s.prototype = new e();
    return s.version = "1.12.1", f.constructor = s, f.kill()._gc = !1, f.to = function (t, e, s, r) {
      var n = s.repeat && h.TweenMax || i;
      return e ? this.add(new n(t, e, s), r) : this.set(t, s, r);
    }, f.from = function (t, e, s, r) {
      return this.add((s.repeat && h.TweenMax || i).from(t, e, s), r);
    }, f.fromTo = function (t, e, s, r, n) {
      var a = r.repeat && h.TweenMax || i;
      return e ? this.add(a.fromTo(t, e, s, r), n) : this.set(t, r, n);
    }, f.staggerTo = function (t, e, r, a, o, h, _, f) {
      var p,
        c = new s({
          onComplete: h,
          onCompleteParams: _,
          onCompleteScope: f,
          smoothChildTiming: this.smoothChildTiming
        });
      for ("string" == typeof t && (t = i.selector(t) || t), n(t) && (t = u.call(t, 0)), a = a || 0, p = 0; t.length > p; p++) r.startAt && (r.startAt = l(r.startAt)), c.to(t[p], e, l(r), p * a);
      return this.add(c, o);
    }, f.staggerFrom = function (t, e, i, s, r, n, a, o) {
      return i.immediateRender = 0 != i.immediateRender, i.runBackwards = !0, this.staggerTo(t, e, i, s, r, n, a, o);
    }, f.staggerFromTo = function (t, e, i, s, r, n, a, o, h) {
      return s.startAt = i, s.immediateRender = 0 != s.immediateRender && 0 != i.immediateRender, this.staggerTo(t, e, s, r, n, a, o, h);
    }, f.call = function (t, e, s, r) {
      return this.add(i.delayedCall(0, t, e, s), r);
    }, f.set = function (t, e, s) {
      return s = this._parseTimeOrLabel(s, 0, !0), null == e.immediateRender && (e.immediateRender = s === this._time && !this._paused), this.add(new i(t, 0, e), s);
    }, s.exportRoot = function (t, e) {
      t = t || {}, null == t.smoothChildTiming && (t.smoothChildTiming = !0);
      var r,
        n,
        a = new s(t),
        o = a._timeline;
      for (null == e && (e = !0), o._remove(a, !0), a._startTime = 0, a._rawPrevTime = a._time = a._totalTime = o._time, r = o._first; r;) n = r._next, e && r instanceof i && r.target === r.vars.onComplete || a.add(r, r._startTime - r._delay), r = n;
      return o.add(a, 0), a;
    }, f.add = function (r, n, o, h) {
      var l, _, u, f, p, c;
      if ("number" != typeof n && (n = this._parseTimeOrLabel(n, 0, !0, r)), !(r instanceof t)) {
        if (r instanceof Array || r && r.push && a(r)) {
          for (o = o || "normal", h = h || 0, l = n, _ = r.length, u = 0; _ > u; u++) a(f = r[u]) && (f = new s({
            tweens: f
          })), this.add(f, l), "string" != typeof f && "function" != typeof f && ("sequence" === o ? l = f._startTime + f.totalDuration() / f._timeScale : "start" === o && (f._startTime -= f.delay())), l += h;
          return this._uncache(!0);
        }
        if ("string" == typeof r) return this.addLabel(r, n);
        if ("function" != typeof r) throw "Cannot add " + r + " into the timeline; it is not a tween, timeline, function, or string.";
        r = i.delayedCall(0, r);
      }
      if (e.prototype.add.call(this, r, n), (this._gc || this._time === this._duration) && !this._paused && this._duration < this.duration()) for (p = this, c = p.rawTime() > r._startTime; p._timeline;) c && p._timeline.smoothChildTiming ? p.totalTime(p._totalTime, !0) : p._gc && p._enabled(!0, !1), p = p._timeline;
      return this;
    }, f.remove = function (e) {
      if (e instanceof t) return this._remove(e, !1);
      if (e instanceof Array || e && e.push && a(e)) {
        for (var i = e.length; --i > -1;) this.remove(e[i]);
        return this;
      }
      return "string" == typeof e ? this.removeLabel(e) : this.kill(null, e);
    }, f._remove = function (t, i) {
      e.prototype._remove.call(this, t, i);
      var s = this._last;
      return s ? this._time > s._startTime + s._totalDuration / s._timeScale && (this._time = this.duration(), this._totalTime = this._totalDuration) : this._time = this._totalTime = this._duration = this._totalDuration = 0, this;
    }, f.append = function (t, e) {
      return this.add(t, this._parseTimeOrLabel(null, e, !0, t));
    }, f.insert = f.insertMultiple = function (t, e, i, s) {
      return this.add(t, e || 0, i, s);
    }, f.appendMultiple = function (t, e, i, s) {
      return this.add(t, this._parseTimeOrLabel(null, e, !0, t), i, s);
    }, f.addLabel = function (t, e) {
      return this._labels[t] = this._parseTimeOrLabel(e), this;
    }, f.addPause = function (t, e, i, s) {
      return this.call(_, ["{self}", e, i, s], this, t);
    }, f.removeLabel = function (t) {
      return delete this._labels[t], this;
    }, f.getLabelTime = function (t) {
      return null != this._labels[t] ? this._labels[t] : -1;
    }, f._parseTimeOrLabel = function (e, i, s, r) {
      var n;
      if (r instanceof t && r.timeline === this) this.remove(r);else if (r && (r instanceof Array || r.push && a(r))) for (n = r.length; --n > -1;) r[n] instanceof t && r[n].timeline === this && this.remove(r[n]);
      if ("string" == typeof i) return this._parseTimeOrLabel(i, s && "number" == typeof e && null == this._labels[i] ? e - this.duration() : 0, s);
      if (i = i || 0, "string" != typeof e || !isNaN(e) && null == this._labels[e]) null == e && (e = this.duration());else {
        if (n = e.indexOf("="), -1 === n) return null == this._labels[e] ? s ? this._labels[e] = this.duration() + i : i : this._labels[e] + i;
        i = parseInt(e.charAt(n - 1) + "1", 10) * Number(e.substr(n + 1)), e = n > 1 ? this._parseTimeOrLabel(e.substr(0, n - 1), 0, s) : this.duration();
      }
      return Number(e) + i;
    }, f.seek = function (t, e) {
      return this.totalTime("number" == typeof t ? t : this._parseTimeOrLabel(t), e !== !1);
    }, f.stop = function () {
      return this.paused(!0);
    }, f.gotoAndPlay = function (t, e) {
      return this.play(t, e);
    }, f.gotoAndStop = function (t, e) {
      return this.pause(t, e);
    }, f.render = function (t, e, i) {
      this._gc && this._enabled(!0, !1);
      var s,
        n,
        a,
        h,
        l,
        _ = this._dirty ? this.totalDuration() : this._totalDuration,
        u = this._time,
        f = this._startTime,
        p = this._timeScale,
        c = this._paused;
      if (t >= _ ? (this._totalTime = this._time = _, this._reversed || this._hasPausedChild() || (n = !0, h = "onComplete", 0 === this._duration && (0 === t || 0 > this._rawPrevTime || this._rawPrevTime === r) && this._rawPrevTime !== t && this._first && (l = !0, this._rawPrevTime > r && (h = "onReverseComplete"))), this._rawPrevTime = this._duration || !e || t || this._rawPrevTime === t ? t : r, t = _ + 1e-4) : 1e-7 > t ? (this._totalTime = this._time = 0, (0 !== u || 0 === this._duration && this._rawPrevTime !== r && (this._rawPrevTime > 0 || 0 > t && this._rawPrevTime >= 0)) && (h = "onReverseComplete", n = this._reversed), 0 > t ? (this._active = !1, 0 === this._duration && this._rawPrevTime >= 0 && this._first && (l = !0), this._rawPrevTime = t) : (this._rawPrevTime = this._duration || !e || t || this._rawPrevTime === t ? t : r, t = 0, this._initted || (l = !0))) : this._totalTime = this._time = this._rawPrevTime = t, this._time !== u && this._first || i || l) {
        if (this._initted || (this._initted = !0), this._active || !this._paused && this._time !== u && t > 0 && (this._active = !0), 0 === u && this.vars.onStart && 0 !== this._time && (e || this.vars.onStart.apply(this.vars.onStartScope || this, this.vars.onStartParams || o)), this._time >= u) for (s = this._first; s && (a = s._next, !this._paused || c);) (s._active || s._startTime <= this._time && !s._paused && !s._gc) && (s._reversed ? s.render((s._dirty ? s.totalDuration() : s._totalDuration) - (t - s._startTime) * s._timeScale, e, i) : s.render((t - s._startTime) * s._timeScale, e, i)), s = a;else for (s = this._last; s && (a = s._prev, !this._paused || c);) (s._active || u >= s._startTime && !s._paused && !s._gc) && (s._reversed ? s.render((s._dirty ? s.totalDuration() : s._totalDuration) - (t - s._startTime) * s._timeScale, e, i) : s.render((t - s._startTime) * s._timeScale, e, i)), s = a;
        this._onUpdate && (e || this._onUpdate.apply(this.vars.onUpdateScope || this, this.vars.onUpdateParams || o)), h && (this._gc || (f === this._startTime || p !== this._timeScale) && (0 === this._time || _ >= this.totalDuration()) && (n && (this._timeline.autoRemoveChildren && this._enabled(!1, !1), this._active = !1), !e && this.vars[h] && this.vars[h].apply(this.vars[h + "Scope"] || this, this.vars[h + "Params"] || o)));
      }
    }, f._hasPausedChild = function () {
      for (var t = this._first; t;) {
        if (t._paused || t instanceof s && t._hasPausedChild()) return !0;
        t = t._next;
      }
      return !1;
    }, f.getChildren = function (t, e, s, r) {
      r = r || -9999999999;
      for (var n = [], a = this._first, o = 0; a;) r > a._startTime || (a instanceof i ? e !== !1 && (n[o++] = a) : (s !== !1 && (n[o++] = a), t !== !1 && (n = n.concat(a.getChildren(!0, e, s)), o = n.length))), a = a._next;
      return n;
    }, f.getTweensOf = function (t, e) {
      var s,
        r,
        n = this._gc,
        a = [],
        o = 0;
      for (n && this._enabled(!0, !0), s = i.getTweensOf(t), r = s.length; --r > -1;) (s[r].timeline === this || e && this._contains(s[r])) && (a[o++] = s[r]);
      return n && this._enabled(!1, !0), a;
    }, f._contains = function (t) {
      for (var e = t.timeline; e;) {
        if (e === this) return !0;
        e = e.timeline;
      }
      return !1;
    }, f.shiftChildren = function (t, e, i) {
      i = i || 0;
      for (var s, r = this._first, n = this._labels; r;) r._startTime >= i && (r._startTime += t), r = r._next;
      if (e) for (s in n) n[s] >= i && (n[s] += t);
      return this._uncache(!0);
    }, f._kill = function (t, e) {
      if (!t && !e) return this._enabled(!1, !1);
      for (var i = e ? this.getTweensOf(e) : this.getChildren(!0, !0, !1), s = i.length, r = !1; --s > -1;) i[s]._kill(t, e) && (r = !0);
      return r;
    }, f.clear = function (t) {
      var e = this.getChildren(!1, !0, !0),
        i = e.length;
      for (this._time = this._totalTime = 0; --i > -1;) e[i]._enabled(!1, !1);
      return t !== !1 && (this._labels = {}), this._uncache(!0);
    }, f.invalidate = function () {
      for (var t = this._first; t;) t.invalidate(), t = t._next;
      return this;
    }, f._enabled = function (t, i) {
      if (t === this._gc) for (var s = this._first; s;) s._enabled(t, !0), s = s._next;
      return e.prototype._enabled.call(this, t, i);
    }, f.duration = function (t) {
      return arguments.length ? (0 !== this.duration() && 0 !== t && this.timeScale(this._duration / t), this) : (this._dirty && this.totalDuration(), this._duration);
    }, f.totalDuration = function (t) {
      if (!arguments.length) {
        if (this._dirty) {
          for (var e, i, s = 0, r = this._last, n = 999999999999; r;) e = r._prev, r._dirty && r.totalDuration(), r._startTime > n && this._sortChildren && !r._paused ? this.add(r, r._startTime - r._delay) : n = r._startTime, 0 > r._startTime && !r._paused && (s -= r._startTime, this._timeline.smoothChildTiming && (this._startTime += r._startTime / this._timeScale), this.shiftChildren(-r._startTime, !1, -9999999999), n = 0), i = r._startTime + r._totalDuration / r._timeScale, i > s && (s = i), r = e;
          this._duration = this._totalDuration = s, this._dirty = !1;
        }
        return this._totalDuration;
      }
      return 0 !== this.totalDuration() && 0 !== t && this.timeScale(this._totalDuration / t), this;
    }, f.usesFrames = function () {
      for (var e = this._timeline; e._timeline;) e = e._timeline;
      return e === t._rootFramesTimeline;
    }, f.rawTime = function () {
      return this._paused ? this._totalTime : (this._timeline.rawTime() - this._startTime) * this._timeScale;
    }, s;
  }, !0);
}), window._gsDefine && window._gsQueue.pop()();

},{}],15:[function(require,module,exports){
"use strict";

/*!
 * VERSION: 1.12.1
 * DATE: 2014-06-26
 * UPDATES AND DOCS AT: http://www.greensock.com
 *
 * @license Copyright (c) 2008-2014, GreenSock. All rights reserved.
 * This work is subject to the terms at http://www.greensock.com/terms_of_use.html or for
 * Club GreenSock members, the software agreement that was issued with your membership.
 * 
 * @author: Jack Doyle, jack@greensock.com
 */
(function (t) {
  "use strict";

  var e = t.GreenSockGlobals || t;
  if (!e.TweenLite) {
    var i,
      s,
      n,
      r,
      a,
      o = function (t) {
        var i,
          s = t.split("."),
          n = e;
        for (i = 0; s.length > i; i++) n[s[i]] = n = n[s[i]] || {};
        return n;
      },
      l = o("com.greensock"),
      h = 1e-10,
      _ = [].slice,
      u = function () {},
      m = function () {
        var t = Object.prototype.toString,
          e = t.call([]);
        return function (i) {
          return null != i && (i instanceof Array || "object" == typeof i && !!i.push && t.call(i) === e);
        };
      }(),
      f = {},
      p = function (i, s, n, r) {
        this.sc = f[i] ? f[i].sc : [], f[i] = this, this.gsClass = null, this.func = n;
        var a = [];
        this.check = function (l) {
          for (var h, _, u, m, c = s.length, d = c; --c > -1;) (h = f[s[c]] || new p(s[c], [])).gsClass ? (a[c] = h.gsClass, d--) : l && h.sc.push(this);
          if (0 === d && n) for (_ = ("com.greensock." + i).split("."), u = _.pop(), m = o(_.join("."))[u] = this.gsClass = n.apply(n, a), r && (e[u] = m, "function" == typeof define && define.amd ? define((t.GreenSockAMDPath ? t.GreenSockAMDPath + "/" : "") + i.split(".").join("/"), [], function () {
            return m;
          }) : "undefined" != typeof module && module.exports && (module.exports = m)), c = 0; this.sc.length > c; c++) this.sc[c].check();
        }, this.check(!0);
      },
      c = t._gsDefine = function (t, e, i, s) {
        return new p(t, e, i, s);
      },
      d = l._class = function (t, e, i) {
        return e = e || function () {}, c(t, [], function () {
          return e;
        }, i), e;
      };
    c.globals = e;
    var v = [0, 0, 1, 1],
      g = [],
      T = d("easing.Ease", function (t, e, i, s) {
        this._func = t, this._type = i || 0, this._power = s || 0, this._params = e ? v.concat(e) : v;
      }, !0),
      y = T.map = {},
      w = T.register = function (t, e, i, s) {
        for (var n, r, a, o, h = e.split(","), _ = h.length, u = (i || "easeIn,easeOut,easeInOut").split(","); --_ > -1;) for (r = h[_], n = s ? d("easing." + r, null, !0) : l.easing[r] || {}, a = u.length; --a > -1;) o = u[a], y[r + "." + o] = y[o + r] = n[o] = t.getRatio ? t : t[o] || new t();
      };
    for (n = T.prototype, n._calcEnd = !1, n.getRatio = function (t) {
      if (this._func) return this._params[0] = t, this._func.apply(null, this._params);
      var e = this._type,
        i = this._power,
        s = 1 === e ? 1 - t : 2 === e ? t : .5 > t ? 2 * t : 2 * (1 - t);
      return 1 === i ? s *= s : 2 === i ? s *= s * s : 3 === i ? s *= s * s * s : 4 === i && (s *= s * s * s * s), 1 === e ? 1 - s : 2 === e ? s : .5 > t ? s / 2 : 1 - s / 2;
    }, i = ["Linear", "Quad", "Cubic", "Quart", "Quint,Strong"], s = i.length; --s > -1;) n = i[s] + ",Power" + s, w(new T(null, null, 1, s), n, "easeOut", !0), w(new T(null, null, 2, s), n, "easeIn" + (0 === s ? ",easeNone" : "")), w(new T(null, null, 3, s), n, "easeInOut");
    y.linear = l.easing.Linear.easeIn, y.swing = l.easing.Quad.easeInOut;
    var P = d("events.EventDispatcher", function (t) {
      this._listeners = {}, this._eventTarget = t || this;
    });
    n = P.prototype, n.addEventListener = function (t, e, i, s, n) {
      n = n || 0;
      var o,
        l,
        h = this._listeners[t],
        _ = 0;
      for (null == h && (this._listeners[t] = h = []), l = h.length; --l > -1;) o = h[l], o.c === e && o.s === i ? h.splice(l, 1) : 0 === _ && n > o.pr && (_ = l + 1);
      h.splice(_, 0, {
        c: e,
        s: i,
        up: s,
        pr: n
      }), this !== r || a || r.wake();
    }, n.removeEventListener = function (t, e) {
      var i,
        s = this._listeners[t];
      if (s) for (i = s.length; --i > -1;) if (s[i].c === e) return s.splice(i, 1), void 0;
    }, n.dispatchEvent = function (t) {
      var e,
        i,
        s,
        n = this._listeners[t];
      if (n) for (e = n.length, i = this._eventTarget; --e > -1;) s = n[e], s.up ? s.c.call(s.s || i, {
        type: t,
        target: i
      }) : s.c.call(s.s || i);
    };
    var k = t.requestAnimationFrame,
      b = t.cancelAnimationFrame,
      A = Date.now || function () {
        return new Date().getTime();
      },
      S = A();
    for (i = ["ms", "moz", "webkit", "o"], s = i.length; --s > -1 && !k;) k = t[i[s] + "RequestAnimationFrame"], b = t[i[s] + "CancelAnimationFrame"] || t[i[s] + "CancelRequestAnimationFrame"];
    d("Ticker", function (t, e) {
      var i,
        s,
        n,
        o,
        l,
        _ = this,
        m = A(),
        f = e !== !1 && k,
        p = 500,
        c = 33,
        d = function (t) {
          var e,
            r,
            a = A() - S;
          a > p && (m += a - c), S += a, _.time = (S - m) / 1e3, e = _.time - l, (!i || e > 0 || t === !0) && (_.frame++, l += e + (e >= o ? .004 : o - e), r = !0), t !== !0 && (n = s(d)), r && _.dispatchEvent("tick");
        };
      P.call(_), _.time = _.frame = 0, _.tick = function () {
        d(!0);
      }, _.lagSmoothing = function (t, e) {
        p = t || 1 / h, c = Math.min(e, p, 0);
      }, _.sleep = function () {
        null != n && (f && b ? b(n) : clearTimeout(n), s = u, n = null, _ === r && (a = !1));
      }, _.wake = function () {
        null !== n ? _.sleep() : _.frame > 10 && (S = A() - p + 5), s = 0 === i ? u : f && k ? k : function (t) {
          return setTimeout(t, 0 | 1e3 * (l - _.time) + 1);
        }, _ === r && (a = !0), d(2);
      }, _.fps = function (t) {
        return arguments.length ? (i = t, o = 1 / (i || 60), l = this.time + o, _.wake(), void 0) : i;
      }, _.useRAF = function (t) {
        return arguments.length ? (_.sleep(), f = t, _.fps(i), void 0) : f;
      }, _.fps(t), setTimeout(function () {
        f && (!n || 5 > _.frame) && _.useRAF(!1);
      }, 1500);
    }), n = l.Ticker.prototype = new l.events.EventDispatcher(), n.constructor = l.Ticker;
    var x = d("core.Animation", function (t, e) {
      if (this.vars = e = e || {}, this._duration = this._totalDuration = t || 0, this._delay = Number(e.delay) || 0, this._timeScale = 1, this._active = e.immediateRender === !0, this.data = e.data, this._reversed = e.reversed === !0, B) {
        a || r.wake();
        var i = this.vars.useFrames ? Q : B;
        i.add(this, i._time), this.vars.paused && this.paused(!0);
      }
    });
    r = x.ticker = new l.Ticker(), n = x.prototype, n._dirty = n._gc = n._initted = n._paused = !1, n._totalTime = n._time = 0, n._rawPrevTime = -1, n._next = n._last = n._onUpdate = n._timeline = n.timeline = null, n._paused = !1;
    var C = function () {
      a && A() - S > 2e3 && r.wake(), setTimeout(C, 2e3);
    };
    C(), n.play = function (t, e) {
      return null != t && this.seek(t, e), this.reversed(!1).paused(!1);
    }, n.pause = function (t, e) {
      return null != t && this.seek(t, e), this.paused(!0);
    }, n.resume = function (t, e) {
      return null != t && this.seek(t, e), this.paused(!1);
    }, n.seek = function (t, e) {
      return this.totalTime(Number(t), e !== !1);
    }, n.restart = function (t, e) {
      return this.reversed(!1).paused(!1).totalTime(t ? -this._delay : 0, e !== !1, !0);
    }, n.reverse = function (t, e) {
      return null != t && this.seek(t || this.totalDuration(), e), this.reversed(!0).paused(!1);
    }, n.render = function () {}, n.invalidate = function () {
      return this;
    }, n.isActive = function () {
      var t,
        e = this._timeline,
        i = this._startTime;
      return !e || !this._gc && !this._paused && e.isActive() && (t = e.rawTime()) >= i && i + this.totalDuration() / this._timeScale > t;
    }, n._enabled = function (t, e) {
      return a || r.wake(), this._gc = !t, this._active = this.isActive(), e !== !0 && (t && !this.timeline ? this._timeline.add(this, this._startTime - this._delay) : !t && this.timeline && this._timeline._remove(this, !0)), !1;
    }, n._kill = function () {
      return this._enabled(!1, !1);
    }, n.kill = function (t, e) {
      return this._kill(t, e), this;
    }, n._uncache = function (t) {
      for (var e = t ? this : this.timeline; e;) e._dirty = !0, e = e.timeline;
      return this;
    }, n._swapSelfInParams = function (t) {
      for (var e = t.length, i = t.concat(); --e > -1;) "{self}" === t[e] && (i[e] = this);
      return i;
    }, n.eventCallback = function (t, e, i, s) {
      if ("on" === (t || "").substr(0, 2)) {
        var n = this.vars;
        if (1 === arguments.length) return n[t];
        null == e ? delete n[t] : (n[t] = e, n[t + "Params"] = m(i) && -1 !== i.join("").indexOf("{self}") ? this._swapSelfInParams(i) : i, n[t + "Scope"] = s), "onUpdate" === t && (this._onUpdate = e);
      }
      return this;
    }, n.delay = function (t) {
      return arguments.length ? (this._timeline.smoothChildTiming && this.startTime(this._startTime + t - this._delay), this._delay = t, this) : this._delay;
    }, n.duration = function (t) {
      return arguments.length ? (this._duration = this._totalDuration = t, this._uncache(!0), this._timeline.smoothChildTiming && this._time > 0 && this._time < this._duration && 0 !== t && this.totalTime(this._totalTime * (t / this._duration), !0), this) : (this._dirty = !1, this._duration);
    }, n.totalDuration = function (t) {
      return this._dirty = !1, arguments.length ? this.duration(t) : this._totalDuration;
    }, n.time = function (t, e) {
      return arguments.length ? (this._dirty && this.totalDuration(), this.totalTime(t > this._duration ? this._duration : t, e)) : this._time;
    }, n.totalTime = function (t, e, i) {
      if (a || r.wake(), !arguments.length) return this._totalTime;
      if (this._timeline) {
        if (0 > t && !i && (t += this.totalDuration()), this._timeline.smoothChildTiming) {
          this._dirty && this.totalDuration();
          var s = this._totalDuration,
            n = this._timeline;
          if (t > s && !i && (t = s), this._startTime = (this._paused ? this._pauseTime : n._time) - (this._reversed ? s - t : t) / this._timeScale, n._dirty || this._uncache(!1), n._timeline) for (; n._timeline;) n._timeline._time !== (n._startTime + n._totalTime) / n._timeScale && n.totalTime(n._totalTime, !0), n = n._timeline;
        }
        this._gc && this._enabled(!0, !1), (this._totalTime !== t || 0 === this._duration) && (this.render(t, e, !1), z.length && q());
      }
      return this;
    }, n.progress = n.totalProgress = function (t, e) {
      return arguments.length ? this.totalTime(this.duration() * t, e) : this._time / this.duration();
    }, n.startTime = function (t) {
      return arguments.length ? (t !== this._startTime && (this._startTime = t, this.timeline && this.timeline._sortChildren && this.timeline.add(this, t - this._delay)), this) : this._startTime;
    }, n.timeScale = function (t) {
      if (!arguments.length) return this._timeScale;
      if (t = t || h, this._timeline && this._timeline.smoothChildTiming) {
        var e = this._pauseTime,
          i = e || 0 === e ? e : this._timeline.totalTime();
        this._startTime = i - (i - this._startTime) * this._timeScale / t;
      }
      return this._timeScale = t, this._uncache(!1);
    }, n.reversed = function (t) {
      return arguments.length ? (t != this._reversed && (this._reversed = t, this.totalTime(this._timeline && !this._timeline.smoothChildTiming ? this.totalDuration() - this._totalTime : this._totalTime, !0)), this) : this._reversed;
    }, n.paused = function (t) {
      if (!arguments.length) return this._paused;
      if (t != this._paused && this._timeline) {
        a || t || r.wake();
        var e = this._timeline,
          i = e.rawTime(),
          s = i - this._pauseTime;
        !t && e.smoothChildTiming && (this._startTime += s, this._uncache(!1)), this._pauseTime = t ? i : null, this._paused = t, this._active = this.isActive(), !t && 0 !== s && this._initted && this.duration() && this.render(e.smoothChildTiming ? this._totalTime : (i - this._startTime) / this._timeScale, !0, !0);
      }
      return this._gc && !t && this._enabled(!0, !1), this;
    };
    var R = d("core.SimpleTimeline", function (t) {
      x.call(this, 0, t), this.autoRemoveChildren = this.smoothChildTiming = !0;
    });
    n = R.prototype = new x(), n.constructor = R, n.kill()._gc = !1, n._first = n._last = null, n._sortChildren = !1, n.add = n.insert = function (t, e) {
      var i, s;
      if (t._startTime = Number(e || 0) + t._delay, t._paused && this !== t._timeline && (t._pauseTime = t._startTime + (this.rawTime() - t._startTime) / t._timeScale), t.timeline && t.timeline._remove(t, !0), t.timeline = t._timeline = this, t._gc && t._enabled(!0, !0), i = this._last, this._sortChildren) for (s = t._startTime; i && i._startTime > s;) i = i._prev;
      return i ? (t._next = i._next, i._next = t) : (t._next = this._first, this._first = t), t._next ? t._next._prev = t : this._last = t, t._prev = i, this._timeline && this._uncache(!0), this;
    }, n._remove = function (t, e) {
      return t.timeline === this && (e || t._enabled(!1, !0), t.timeline = null, t._prev ? t._prev._next = t._next : this._first === t && (this._first = t._next), t._next ? t._next._prev = t._prev : this._last === t && (this._last = t._prev), this._timeline && this._uncache(!0)), this;
    }, n.render = function (t, e, i) {
      var s,
        n = this._first;
      for (this._totalTime = this._time = this._rawPrevTime = t; n;) s = n._next, (n._active || t >= n._startTime && !n._paused) && (n._reversed ? n.render((n._dirty ? n.totalDuration() : n._totalDuration) - (t - n._startTime) * n._timeScale, e, i) : n.render((t - n._startTime) * n._timeScale, e, i)), n = s;
    }, n.rawTime = function () {
      return a || r.wake(), this._totalTime;
    };
    var D = d("TweenLite", function (e, i, s) {
        if (x.call(this, i, s), this.render = D.prototype.render, null == e) throw "Cannot tween a null target.";
        this.target = e = "string" != typeof e ? e : D.selector(e) || e;
        var n,
          r,
          a,
          o = e.jquery || e.length && e !== t && e[0] && (e[0] === t || e[0].nodeType && e[0].style && !e.nodeType),
          l = this.vars.overwrite;
        if (this._overwrite = l = null == l ? G[D.defaultOverwrite] : "number" == typeof l ? l >> 0 : G[l], (o || e instanceof Array || e.push && m(e)) && "number" != typeof e[0]) for (this._targets = a = _.call(e, 0), this._propLookup = [], this._siblings = [], n = 0; a.length > n; n++) r = a[n], r ? "string" != typeof r ? r.length && r !== t && r[0] && (r[0] === t || r[0].nodeType && r[0].style && !r.nodeType) ? (a.splice(n--, 1), this._targets = a = a.concat(_.call(r, 0))) : (this._siblings[n] = M(r, this, !1), 1 === l && this._siblings[n].length > 1 && $(r, this, null, 1, this._siblings[n])) : (r = a[n--] = D.selector(r), "string" == typeof r && a.splice(n + 1, 1)) : a.splice(n--, 1);else this._propLookup = {}, this._siblings = M(e, this, !1), 1 === l && this._siblings.length > 1 && $(e, this, null, 1, this._siblings);
        (this.vars.immediateRender || 0 === i && 0 === this._delay && this.vars.immediateRender !== !1) && (this._time = -h, this.render(-this._delay));
      }, !0),
      I = function (e) {
        return e.length && e !== t && e[0] && (e[0] === t || e[0].nodeType && e[0].style && !e.nodeType);
      },
      E = function (t, e) {
        var i,
          s = {};
        for (i in t) j[i] || i in e && "transform" !== i && "x" !== i && "y" !== i && "width" !== i && "height" !== i && "className" !== i && "border" !== i || !(!L[i] || L[i] && L[i]._autoCSS) || (s[i] = t[i], delete t[i]);
        t.css = s;
      };
    n = D.prototype = new x(), n.constructor = D, n.kill()._gc = !1, n.ratio = 0, n._firstPT = n._targets = n._overwrittenProps = n._startAt = null, n._notifyPluginsOfEnabled = n._lazy = !1, D.version = "1.12.1", D.defaultEase = n._ease = new T(null, null, 1, 1), D.defaultOverwrite = "auto", D.ticker = r, D.autoSleep = !0, D.lagSmoothing = function (t, e) {
      r.lagSmoothing(t, e);
    }, D.selector = t.$ || t.jQuery || function (e) {
      return t.$ ? (D.selector = t.$, t.$(e)) : t.document ? t.document.getElementById("#" === e.charAt(0) ? e.substr(1) : e) : e;
    };
    var z = [],
      O = {},
      N = D._internals = {
        isArray: m,
        isSelector: I,
        lazyTweens: z
      },
      L = D._plugins = {},
      U = N.tweenLookup = {},
      F = 0,
      j = N.reservedProps = {
        ease: 1,
        delay: 1,
        overwrite: 1,
        onComplete: 1,
        onCompleteParams: 1,
        onCompleteScope: 1,
        useFrames: 1,
        runBackwards: 1,
        startAt: 1,
        onUpdate: 1,
        onUpdateParams: 1,
        onUpdateScope: 1,
        onStart: 1,
        onStartParams: 1,
        onStartScope: 1,
        onReverseComplete: 1,
        onReverseCompleteParams: 1,
        onReverseCompleteScope: 1,
        onRepeat: 1,
        onRepeatParams: 1,
        onRepeatScope: 1,
        easeParams: 1,
        yoyo: 1,
        immediateRender: 1,
        repeat: 1,
        repeatDelay: 1,
        data: 1,
        paused: 1,
        reversed: 1,
        autoCSS: 1,
        lazy: 1
      },
      G = {
        none: 0,
        all: 1,
        auto: 2,
        concurrent: 3,
        allOnStart: 4,
        preexisting: 5,
        "true": 1,
        "false": 0
      },
      Q = x._rootFramesTimeline = new R(),
      B = x._rootTimeline = new R(),
      q = function () {
        var t = z.length;
        for (O = {}; --t > -1;) i = z[t], i && i._lazy !== !1 && (i.render(i._lazy, !1, !0), i._lazy = !1);
        z.length = 0;
      };
    B._startTime = r.time, Q._startTime = r.frame, B._active = Q._active = !0, setTimeout(q, 1), x._updateRoot = D.render = function () {
      var t, e, i;
      if (z.length && q(), B.render((r.time - B._startTime) * B._timeScale, !1, !1), Q.render((r.frame - Q._startTime) * Q._timeScale, !1, !1), z.length && q(), !(r.frame % 120)) {
        for (i in U) {
          for (e = U[i].tweens, t = e.length; --t > -1;) e[t]._gc && e.splice(t, 1);
          0 === e.length && delete U[i];
        }
        if (i = B._first, (!i || i._paused) && D.autoSleep && !Q._first && 1 === r._listeners.tick.length) {
          for (; i && i._paused;) i = i._next;
          i || r.sleep();
        }
      }
    }, r.addEventListener("tick", x._updateRoot);
    var M = function (t, e, i) {
        var s,
          n,
          r = t._gsTweenID;
        if (U[r || (t._gsTweenID = r = "t" + F++)] || (U[r] = {
          target: t,
          tweens: []
        }), e && (s = U[r].tweens, s[n = s.length] = e, i)) for (; --n > -1;) s[n] === e && s.splice(n, 1);
        return U[r].tweens;
      },
      $ = function (t, e, i, s, n) {
        var r, a, o, l;
        if (1 === s || s >= 4) {
          for (l = n.length, r = 0; l > r; r++) if ((o = n[r]) !== e) o._gc || o._enabled(!1, !1) && (a = !0);else if (5 === s) break;
          return a;
        }
        var _,
          u = e._startTime + h,
          m = [],
          f = 0,
          p = 0 === e._duration;
        for (r = n.length; --r > -1;) (o = n[r]) === e || o._gc || o._paused || (o._timeline !== e._timeline ? (_ = _ || K(e, 0, p), 0 === K(o, _, p) && (m[f++] = o)) : u >= o._startTime && o._startTime + o.totalDuration() / o._timeScale > u && ((p || !o._initted) && 2e-10 >= u - o._startTime || (m[f++] = o)));
        for (r = f; --r > -1;) o = m[r], 2 === s && o._kill(i, t) && (a = !0), (2 !== s || !o._firstPT && o._initted) && o._enabled(!1, !1) && (a = !0);
        return a;
      },
      K = function (t, e, i) {
        for (var s = t._timeline, n = s._timeScale, r = t._startTime; s._timeline;) {
          if (r += s._startTime, n *= s._timeScale, s._paused) return -100;
          s = s._timeline;
        }
        return r /= n, r > e ? r - e : i && r === e || !t._initted && 2 * h > r - e ? h : (r += t.totalDuration() / t._timeScale / n) > e + h ? 0 : r - e - h;
      };
    n._init = function () {
      var t,
        e,
        i,
        s,
        n,
        r = this.vars,
        a = this._overwrittenProps,
        o = this._duration,
        l = !!r.immediateRender,
        h = r.ease;
      if (r.startAt) {
        this._startAt && (this._startAt.render(-1, !0), this._startAt.kill()), n = {};
        for (s in r.startAt) n[s] = r.startAt[s];
        if (n.overwrite = !1, n.immediateRender = !0, n.lazy = l && r.lazy !== !1, n.startAt = n.delay = null, this._startAt = D.to(this.target, 0, n), l) if (this._time > 0) this._startAt = null;else if (0 !== o) return;
      } else if (r.runBackwards && 0 !== o) if (this._startAt) this._startAt.render(-1, !0), this._startAt.kill(), this._startAt = null;else {
        i = {};
        for (s in r) j[s] && "autoCSS" !== s || (i[s] = r[s]);
        if (i.overwrite = 0, i.data = "isFromStart", i.lazy = l && r.lazy !== !1, i.immediateRender = l, this._startAt = D.to(this.target, 0, i), l) {
          if (0 === this._time) return;
        } else this._startAt._init(), this._startAt._enabled(!1);
      }
      if (this._ease = h ? h instanceof T ? r.easeParams instanceof Array ? h.config.apply(h, r.easeParams) : h : "function" == typeof h ? new T(h, r.easeParams) : y[h] || D.defaultEase : D.defaultEase, this._easeType = this._ease._type, this._easePower = this._ease._power, this._firstPT = null, this._targets) for (t = this._targets.length; --t > -1;) this._initProps(this._targets[t], this._propLookup[t] = {}, this._siblings[t], a ? a[t] : null) && (e = !0);else e = this._initProps(this.target, this._propLookup, this._siblings, a);
      if (e && D._onPluginEvent("_onInitAllProps", this), a && (this._firstPT || "function" != typeof this.target && this._enabled(!1, !1)), r.runBackwards) for (i = this._firstPT; i;) i.s += i.c, i.c = -i.c, i = i._next;
      this._onUpdate = r.onUpdate, this._initted = !0;
    }, n._initProps = function (e, i, s, n) {
      var r, a, o, l, h, _;
      if (null == e) return !1;
      O[e._gsTweenID] && q(), this.vars.css || e.style && e !== t && e.nodeType && L.css && this.vars.autoCSS !== !1 && E(this.vars, e);
      for (r in this.vars) {
        if (_ = this.vars[r], j[r]) _ && (_ instanceof Array || _.push && m(_)) && -1 !== _.join("").indexOf("{self}") && (this.vars[r] = _ = this._swapSelfInParams(_, this));else if (L[r] && (l = new L[r]())._onInitTween(e, this.vars[r], this)) {
          for (this._firstPT = h = {
            _next: this._firstPT,
            t: l,
            p: "setRatio",
            s: 0,
            c: 1,
            f: !0,
            n: r,
            pg: !0,
            pr: l._priority
          }, a = l._overwriteProps.length; --a > -1;) i[l._overwriteProps[a]] = this._firstPT;
          (l._priority || l._onInitAllProps) && (o = !0), (l._onDisable || l._onEnable) && (this._notifyPluginsOfEnabled = !0);
        } else this._firstPT = i[r] = h = {
          _next: this._firstPT,
          t: e,
          p: r,
          f: "function" == typeof e[r],
          n: r,
          pg: !1,
          pr: 0
        }, h.s = h.f ? e[r.indexOf("set") || "function" != typeof e["get" + r.substr(3)] ? r : "get" + r.substr(3)]() : parseFloat(e[r]), h.c = "string" == typeof _ && "=" === _.charAt(1) ? parseInt(_.charAt(0) + "1", 10) * Number(_.substr(2)) : Number(_) - h.s || 0;
        h && h._next && (h._next._prev = h);
      }
      return n && this._kill(n, e) ? this._initProps(e, i, s, n) : this._overwrite > 1 && this._firstPT && s.length > 1 && $(e, this, i, this._overwrite, s) ? (this._kill(i, e), this._initProps(e, i, s, n)) : (this._firstPT && (this.vars.lazy !== !1 && this._duration || this.vars.lazy && !this._duration) && (O[e._gsTweenID] = !0), o);
    }, n.render = function (t, e, i) {
      var s,
        n,
        r,
        a,
        o = this._time,
        l = this._duration,
        _ = this._rawPrevTime;
      if (t >= l) this._totalTime = this._time = l, this.ratio = this._ease._calcEnd ? this._ease.getRatio(1) : 1, this._reversed || (s = !0, n = "onComplete"), 0 === l && (this._initted || !this.vars.lazy || i) && (this._startTime === this._timeline._duration && (t = 0), (0 === t || 0 > _ || _ === h) && _ !== t && (i = !0, _ > h && (n = "onReverseComplete")), this._rawPrevTime = a = !e || t || _ === t ? t : h);else if (1e-7 > t) this._totalTime = this._time = 0, this.ratio = this._ease._calcEnd ? this._ease.getRatio(0) : 0, (0 !== o || 0 === l && _ > 0 && _ !== h) && (n = "onReverseComplete", s = this._reversed), 0 > t ? (this._active = !1, 0 === l && (this._initted || !this.vars.lazy || i) && (_ >= 0 && (i = !0), this._rawPrevTime = a = !e || t || _ === t ? t : h)) : this._initted || (i = !0);else if (this._totalTime = this._time = t, this._easeType) {
        var u = t / l,
          m = this._easeType,
          f = this._easePower;
        (1 === m || 3 === m && u >= .5) && (u = 1 - u), 3 === m && (u *= 2), 1 === f ? u *= u : 2 === f ? u *= u * u : 3 === f ? u *= u * u * u : 4 === f && (u *= u * u * u * u), this.ratio = 1 === m ? 1 - u : 2 === m ? u : .5 > t / l ? u / 2 : 1 - u / 2;
      } else this.ratio = this._ease.getRatio(t / l);
      if (this._time !== o || i) {
        if (!this._initted) {
          if (this._init(), !this._initted || this._gc) return;
          if (!i && this._firstPT && (this.vars.lazy !== !1 && this._duration || this.vars.lazy && !this._duration)) return this._time = this._totalTime = o, this._rawPrevTime = _, z.push(this), this._lazy = t, void 0;
          this._time && !s ? this.ratio = this._ease.getRatio(this._time / l) : s && this._ease._calcEnd && (this.ratio = this._ease.getRatio(0 === this._time ? 0 : 1));
        }
        for (this._lazy !== !1 && (this._lazy = !1), this._active || !this._paused && this._time !== o && t >= 0 && (this._active = !0), 0 === o && (this._startAt && (t >= 0 ? this._startAt.render(t, e, i) : n || (n = "_dummyGS")), this.vars.onStart && (0 !== this._time || 0 === l) && (e || this.vars.onStart.apply(this.vars.onStartScope || this, this.vars.onStartParams || g))), r = this._firstPT; r;) r.f ? r.t[r.p](r.c * this.ratio + r.s) : r.t[r.p] = r.c * this.ratio + r.s, r = r._next;
        this._onUpdate && (0 > t && this._startAt && this._startTime && this._startAt.render(t, e, i), e || (this._time !== o || s) && this._onUpdate.apply(this.vars.onUpdateScope || this, this.vars.onUpdateParams || g)), n && (this._gc || (0 > t && this._startAt && !this._onUpdate && this._startTime && this._startAt.render(t, e, i), s && (this._timeline.autoRemoveChildren && this._enabled(!1, !1), this._active = !1), !e && this.vars[n] && this.vars[n].apply(this.vars[n + "Scope"] || this, this.vars[n + "Params"] || g), 0 === l && this._rawPrevTime === h && a !== h && (this._rawPrevTime = 0)));
      }
    }, n._kill = function (t, e) {
      if ("all" === t && (t = null), null == t && (null == e || e === this.target)) return this._lazy = !1, this._enabled(!1, !1);
      e = "string" != typeof e ? e || this._targets || this.target : D.selector(e) || e;
      var i, s, n, r, a, o, l, h;
      if ((m(e) || I(e)) && "number" != typeof e[0]) for (i = e.length; --i > -1;) this._kill(t, e[i]) && (o = !0);else {
        if (this._targets) {
          for (i = this._targets.length; --i > -1;) if (e === this._targets[i]) {
            a = this._propLookup[i] || {}, this._overwrittenProps = this._overwrittenProps || [], s = this._overwrittenProps[i] = t ? this._overwrittenProps[i] || {} : "all";
            break;
          }
        } else {
          if (e !== this.target) return !1;
          a = this._propLookup, s = this._overwrittenProps = t ? this._overwrittenProps || {} : "all";
        }
        if (a) {
          l = t || a, h = t !== s && "all" !== s && t !== a && ("object" != typeof t || !t._tempKill);
          for (n in l) (r = a[n]) && (r.pg && r.t._kill(l) && (o = !0), r.pg && 0 !== r.t._overwriteProps.length || (r._prev ? r._prev._next = r._next : r === this._firstPT && (this._firstPT = r._next), r._next && (r._next._prev = r._prev), r._next = r._prev = null), delete a[n]), h && (s[n] = 1);
          !this._firstPT && this._initted && this._enabled(!1, !1);
        }
      }
      return o;
    }, n.invalidate = function () {
      return this._notifyPluginsOfEnabled && D._onPluginEvent("_onDisable", this), this._firstPT = null, this._overwrittenProps = null, this._onUpdate = null, this._startAt = null, this._initted = this._active = this._notifyPluginsOfEnabled = this._lazy = !1, this._propLookup = this._targets ? {} : [], this;
    }, n._enabled = function (t, e) {
      if (a || r.wake(), t && this._gc) {
        var i,
          s = this._targets;
        if (s) for (i = s.length; --i > -1;) this._siblings[i] = M(s[i], this, !0);else this._siblings = M(this.target, this, !0);
      }
      return x.prototype._enabled.call(this, t, e), this._notifyPluginsOfEnabled && this._firstPT ? D._onPluginEvent(t ? "_onEnable" : "_onDisable", this) : !1;
    }, D.to = function (t, e, i) {
      return new D(t, e, i);
    }, D.from = function (t, e, i) {
      return i.runBackwards = !0, i.immediateRender = 0 != i.immediateRender, new D(t, e, i);
    }, D.fromTo = function (t, e, i, s) {
      return s.startAt = i, s.immediateRender = 0 != s.immediateRender && 0 != i.immediateRender, new D(t, e, s);
    }, D.delayedCall = function (t, e, i, s, n) {
      return new D(e, 0, {
        delay: t,
        onComplete: e,
        onCompleteParams: i,
        onCompleteScope: s,
        onReverseComplete: e,
        onReverseCompleteParams: i,
        onReverseCompleteScope: s,
        immediateRender: !1,
        useFrames: n,
        overwrite: 0
      });
    }, D.set = function (t, e) {
      return new D(t, 0, e);
    }, D.getTweensOf = function (t, e) {
      if (null == t) return [];
      t = "string" != typeof t ? t : D.selector(t) || t;
      var i, s, n, r;
      if ((m(t) || I(t)) && "number" != typeof t[0]) {
        for (i = t.length, s = []; --i > -1;) s = s.concat(D.getTweensOf(t[i], e));
        for (i = s.length; --i > -1;) for (r = s[i], n = i; --n > -1;) r === s[n] && s.splice(i, 1);
      } else for (s = M(t).concat(), i = s.length; --i > -1;) (s[i]._gc || e && !s[i].isActive()) && s.splice(i, 1);
      return s;
    }, D.killTweensOf = D.killDelayedCallsTo = function (t, e, i) {
      "object" == typeof e && (i = e, e = !1);
      for (var s = D.getTweensOf(t, e), n = s.length; --n > -1;) s[n]._kill(i, t);
    };
    var H = d("plugins.TweenPlugin", function (t, e) {
      this._overwriteProps = (t || "").split(","), this._propName = this._overwriteProps[0], this._priority = e || 0, this._super = H.prototype;
    }, !0);
    if (n = H.prototype, H.version = "1.10.1", H.API = 2, n._firstPT = null, n._addTween = function (t, e, i, s, n, r) {
      var a, o;
      return null != s && (a = "number" == typeof s || "=" !== s.charAt(1) ? Number(s) - i : parseInt(s.charAt(0) + "1", 10) * Number(s.substr(2))) ? (this._firstPT = o = {
        _next: this._firstPT,
        t: t,
        p: e,
        s: i,
        c: a,
        f: "function" == typeof t[e],
        n: n || e,
        r: r
      }, o._next && (o._next._prev = o), o) : void 0;
    }, n.setRatio = function (t) {
      for (var e, i = this._firstPT, s = 1e-6; i;) e = i.c * t + i.s, i.r ? e = Math.round(e) : s > e && e > -s && (e = 0), i.f ? i.t[i.p](e) : i.t[i.p] = e, i = i._next;
    }, n._kill = function (t) {
      var e,
        i = this._overwriteProps,
        s = this._firstPT;
      if (null != t[this._propName]) this._overwriteProps = [];else for (e = i.length; --e > -1;) null != t[i[e]] && i.splice(e, 1);
      for (; s;) null != t[s.n] && (s._next && (s._next._prev = s._prev), s._prev ? (s._prev._next = s._next, s._prev = null) : this._firstPT === s && (this._firstPT = s._next)), s = s._next;
      return !1;
    }, n._roundProps = function (t, e) {
      for (var i = this._firstPT; i;) (t[this._propName] || null != i.n && t[i.n.split(this._propName + "_").join("")]) && (i.r = e), i = i._next;
    }, D._onPluginEvent = function (t, e) {
      var i,
        s,
        n,
        r,
        a,
        o = e._firstPT;
      if ("_onInitAllProps" === t) {
        for (; o;) {
          for (a = o._next, s = n; s && s.pr > o.pr;) s = s._next;
          (o._prev = s ? s._prev : r) ? o._prev._next = o : n = o, (o._next = s) ? s._prev = o : r = o, o = a;
        }
        o = e._firstPT = n;
      }
      for (; o;) o.pg && "function" == typeof o.t[t] && o.t[t]() && (i = !0), o = o._next;
      return i;
    }, H.activate = function (t) {
      for (var e = t.length; --e > -1;) t[e].API === H.API && (L[new t[e]()._propName] = t[e]);
      return !0;
    }, c.plugin = function (t) {
      if (!(t && t.propName && t.init && t.API)) throw "illegal plugin definition.";
      var e,
        i = t.propName,
        s = t.priority || 0,
        n = t.overwriteProps,
        r = {
          init: "_onInitTween",
          set: "setRatio",
          kill: "_kill",
          round: "_roundProps",
          initAll: "_onInitAllProps"
        },
        a = d("plugins." + i.charAt(0).toUpperCase() + i.substr(1) + "Plugin", function () {
          H.call(this, i, s), this._overwriteProps = n || [];
        }, t.global === !0),
        o = a.prototype = new H(i);
      o.constructor = a, a.API = t.API;
      for (e in r) "function" == typeof t[e] && (o[r[e]] = t[e]);
      return a.version = t.version, H.activate([a]), a;
    }, i = t._gsQueue) {
      for (s = 0; i.length > s; s++) i[s]();
      for (n in f) f[n].func || t.console.log("GSAP encountered missing dependency: com.greensock." + n);
    }
    a = !1;
  }
})(window);

},{}],16:[function(require,module,exports){
"use strict";

/*!
 * VERSION: beta 1.9.3
 * DATE: 2013-04-02
 * UPDATES AND DOCS AT: http://www.greensock.com
 *
 * @license Copyright (c) 2008-2014, GreenSock. All rights reserved.
 * This work is subject to the terms at http://www.greensock.com/terms_of_use.html or for
 * Club GreenSock members, the software agreement that was issued with your membership.
 * 
 * @author: Jack Doyle, jack@greensock.com
 **/
(window._gsQueue || (window._gsQueue = [])).push(function () {
  "use strict";

  window._gsDefine("easing.Back", ["easing.Ease"], function (t) {
    var e,
      i,
      s,
      r = window.GreenSockGlobals || window,
      n = r.com.greensock,
      a = 2 * Math.PI,
      o = Math.PI / 2,
      h = n._class,
      l = function (e, i) {
        var s = h("easing." + e, function () {}, !0),
          r = s.prototype = new t();
        return r.constructor = s, r.getRatio = i, s;
      },
      _ = t.register || function () {},
      u = function (t, e, i, s) {
        var r = h("easing." + t, {
          easeOut: new e(),
          easeIn: new i(),
          easeInOut: new s()
        }, !0);
        return _(r, t), r;
      },
      c = function (t, e, i) {
        this.t = t, this.v = e, i && (this.next = i, i.prev = this, this.c = i.v - e, this.gap = i.t - t);
      },
      f = function (e, i) {
        var s = h("easing." + e, function (t) {
            this._p1 = t || 0 === t ? t : 1.70158, this._p2 = 1.525 * this._p1;
          }, !0),
          r = s.prototype = new t();
        return r.constructor = s, r.getRatio = i, r.config = function (t) {
          return new s(t);
        }, s;
      },
      p = u("Back", f("BackOut", function (t) {
        return (t -= 1) * t * ((this._p1 + 1) * t + this._p1) + 1;
      }), f("BackIn", function (t) {
        return t * t * ((this._p1 + 1) * t - this._p1);
      }), f("BackInOut", function (t) {
        return 1 > (t *= 2) ? .5 * t * t * ((this._p2 + 1) * t - this._p2) : .5 * ((t -= 2) * t * ((this._p2 + 1) * t + this._p2) + 2);
      })),
      m = h("easing.SlowMo", function (t, e, i) {
        e = e || 0 === e ? e : .7, null == t ? t = .7 : t > 1 && (t = 1), this._p = 1 !== t ? e : 0, this._p1 = (1 - t) / 2, this._p2 = t, this._p3 = this._p1 + this._p2, this._calcEnd = i === !0;
      }, !0),
      d = m.prototype = new t();
    return d.constructor = m, d.getRatio = function (t) {
      var e = t + (.5 - t) * this._p;
      return this._p1 > t ? this._calcEnd ? 1 - (t = 1 - t / this._p1) * t : e - (t = 1 - t / this._p1) * t * t * t * e : t > this._p3 ? this._calcEnd ? 1 - (t = (t - this._p3) / this._p1) * t : e + (t - e) * (t = (t - this._p3) / this._p1) * t * t * t : this._calcEnd ? 1 : e;
    }, m.ease = new m(.7, .7), d.config = m.config = function (t, e, i) {
      return new m(t, e, i);
    }, e = h("easing.SteppedEase", function (t) {
      t = t || 1, this._p1 = 1 / t, this._p2 = t + 1;
    }, !0), d = e.prototype = new t(), d.constructor = e, d.getRatio = function (t) {
      return 0 > t ? t = 0 : t >= 1 && (t = .999999999), (this._p2 * t >> 0) * this._p1;
    }, d.config = e.config = function (t) {
      return new e(t);
    }, i = h("easing.RoughEase", function (e) {
      e = e || {};
      for (var i, s, r, n, a, o, h = e.taper || "none", l = [], _ = 0, u = 0 | (e.points || 20), f = u, p = e.randomize !== !1, m = e.clamp === !0, d = e.template instanceof t ? e.template : null, g = "number" == typeof e.strength ? .4 * e.strength : .4; --f > -1;) i = p ? Math.random() : 1 / u * f, s = d ? d.getRatio(i) : i, "none" === h ? r = g : "out" === h ? (n = 1 - i, r = n * n * g) : "in" === h ? r = i * i * g : .5 > i ? (n = 2 * i, r = .5 * n * n * g) : (n = 2 * (1 - i), r = .5 * n * n * g), p ? s += Math.random() * r - .5 * r : f % 2 ? s += .5 * r : s -= .5 * r, m && (s > 1 ? s = 1 : 0 > s && (s = 0)), l[_++] = {
        x: i,
        y: s
      };
      for (l.sort(function (t, e) {
        return t.x - e.x;
      }), o = new c(1, 1, null), f = u; --f > -1;) a = l[f], o = new c(a.x, a.y, o);
      this._prev = new c(0, 0, 0 !== o.t ? o : o.next);
    }, !0), d = i.prototype = new t(), d.constructor = i, d.getRatio = function (t) {
      var e = this._prev;
      if (t > e.t) {
        for (; e.next && t >= e.t;) e = e.next;
        e = e.prev;
      } else for (; e.prev && e.t >= t;) e = e.prev;
      return this._prev = e, e.v + (t - e.t) / e.gap * e.c;
    }, d.config = function (t) {
      return new i(t);
    }, i.ease = new i(), u("Bounce", l("BounceOut", function (t) {
      return 1 / 2.75 > t ? 7.5625 * t * t : 2 / 2.75 > t ? 7.5625 * (t -= 1.5 / 2.75) * t + .75 : 2.5 / 2.75 > t ? 7.5625 * (t -= 2.25 / 2.75) * t + .9375 : 7.5625 * (t -= 2.625 / 2.75) * t + .984375;
    }), l("BounceIn", function (t) {
      return 1 / 2.75 > (t = 1 - t) ? 1 - 7.5625 * t * t : 2 / 2.75 > t ? 1 - (7.5625 * (t -= 1.5 / 2.75) * t + .75) : 2.5 / 2.75 > t ? 1 - (7.5625 * (t -= 2.25 / 2.75) * t + .9375) : 1 - (7.5625 * (t -= 2.625 / 2.75) * t + .984375);
    }), l("BounceInOut", function (t) {
      var e = .5 > t;
      return t = e ? 1 - 2 * t : 2 * t - 1, t = 1 / 2.75 > t ? 7.5625 * t * t : 2 / 2.75 > t ? 7.5625 * (t -= 1.5 / 2.75) * t + .75 : 2.5 / 2.75 > t ? 7.5625 * (t -= 2.25 / 2.75) * t + .9375 : 7.5625 * (t -= 2.625 / 2.75) * t + .984375, e ? .5 * (1 - t) : .5 * t + .5;
    })), u("Circ", l("CircOut", function (t) {
      return Math.sqrt(1 - (t -= 1) * t);
    }), l("CircIn", function (t) {
      return -(Math.sqrt(1 - t * t) - 1);
    }), l("CircInOut", function (t) {
      return 1 > (t *= 2) ? -.5 * (Math.sqrt(1 - t * t) - 1) : .5 * (Math.sqrt(1 - (t -= 2) * t) + 1);
    })), s = function (e, i, s) {
      var r = h("easing." + e, function (t, e) {
          this._p1 = t || 1, this._p2 = e || s, this._p3 = this._p2 / a * (Math.asin(1 / this._p1) || 0);
        }, !0),
        n = r.prototype = new t();
      return n.constructor = r, n.getRatio = i, n.config = function (t, e) {
        return new r(t, e);
      }, r;
    }, u("Elastic", s("ElasticOut", function (t) {
      return this._p1 * Math.pow(2, -10 * t) * Math.sin((t - this._p3) * a / this._p2) + 1;
    }, .3), s("ElasticIn", function (t) {
      return -(this._p1 * Math.pow(2, 10 * (t -= 1)) * Math.sin((t - this._p3) * a / this._p2));
    }, .3), s("ElasticInOut", function (t) {
      return 1 > (t *= 2) ? -.5 * this._p1 * Math.pow(2, 10 * (t -= 1)) * Math.sin((t - this._p3) * a / this._p2) : .5 * this._p1 * Math.pow(2, -10 * (t -= 1)) * Math.sin((t - this._p3) * a / this._p2) + 1;
    }, .45)), u("Expo", l("ExpoOut", function (t) {
      return 1 - Math.pow(2, -10 * t);
    }), l("ExpoIn", function (t) {
      return Math.pow(2, 10 * (t - 1)) - .001;
    }), l("ExpoInOut", function (t) {
      return 1 > (t *= 2) ? .5 * Math.pow(2, 10 * (t - 1)) : .5 * (2 - Math.pow(2, -10 * (t - 1)));
    })), u("Sine", l("SineOut", function (t) {
      return Math.sin(t * o);
    }), l("SineIn", function (t) {
      return -Math.cos(t * o) + 1;
    }), l("SineInOut", function (t) {
      return -.5 * (Math.cos(Math.PI * t) - 1);
    })), h("easing.EaseLookup", {
      find: function (e) {
        return t.map[e];
      }
    }, !0), _(r.SlowMo, "SlowMo", "ease,"), _(i, "RoughEase", "ease,"), _(e, "SteppedEase", "ease,"), p;
  }, !0);
}), window._gsDefine && window._gsQueue.pop()();

},{}],17:[function(require,module,exports){
"use strict";

/*!
 * VERSION: 1.12.1
 * DATE: 2014-06-26
 * UPDATES AND DOCS AT: http://www.greensock.com
 *
 * @license Copyright (c) 2008-2014, GreenSock. All rights reserved.
 * This work is subject to the terms at http://www.greensock.com/terms_of_use.html or for
 * Club GreenSock members, the software agreement that was issued with your membership.
 * 
 * @author: Jack Doyle, jack@greensock.com
 */
(window._gsQueue || (window._gsQueue = [])).push(function () {
  "use strict";

  window._gsDefine("plugins.CSSPlugin", ["plugins.TweenPlugin", "TweenLite"], function (t, e) {
    var i,
      r,
      s,
      n,
      a = function () {
        t.call(this, "css"), this._overwriteProps.length = 0, this.setRatio = a.prototype.setRatio;
      },
      o = {},
      l = a.prototype = new t("css");
    l.constructor = a, a.version = "1.12.1", a.API = 2, a.defaultTransformPerspective = 0, a.defaultSkewType = "compensated", l = "px", a.suffixMap = {
      top: l,
      right: l,
      bottom: l,
      left: l,
      width: l,
      height: l,
      fontSize: l,
      padding: l,
      margin: l,
      perspective: l,
      lineHeight: ""
    };
    var h,
      u,
      f,
      _,
      p,
      c,
      d = /(?:\d|\-\d|\.\d|\-\.\d)+/g,
      m = /(?:\d|\-\d|\.\d|\-\.\d|\+=\d|\-=\d|\+=.\d|\-=\.\d)+/g,
      g = /(?:\+=|\-=|\-|\b)[\d\-\.]+[a-zA-Z0-9]*(?:%|\b)/gi,
      v = /[^\d\-\.]/g,
      y = /(?:\d|\-|\+|=|#|\.)*/g,
      T = /opacity *= *([^)]*)/i,
      w = /opacity:([^;]*)/i,
      x = /alpha\(opacity *=.+?\)/i,
      b = /^(rgb|hsl)/,
      P = /([A-Z])/g,
      S = /-([a-z])/gi,
      C = /(^(?:url\(\"|url\())|(?:(\"\))$|\)$)/gi,
      R = function (t, e) {
        return e.toUpperCase();
      },
      k = /(?:Left|Right|Width)/i,
      A = /(M11|M12|M21|M22)=[\d\-\.e]+/gi,
      O = /progid\:DXImageTransform\.Microsoft\.Matrix\(.+?\)/i,
      D = /,(?=[^\)]*(?:\(|$))/gi,
      M = Math.PI / 180,
      L = 180 / Math.PI,
      N = {},
      X = document,
      z = X.createElement("div"),
      I = X.createElement("img"),
      E = a._internals = {
        _specialProps: o
      },
      F = navigator.userAgent,
      Y = function () {
        var t,
          e = F.indexOf("Android"),
          i = X.createElement("div");
        return f = -1 !== F.indexOf("Safari") && -1 === F.indexOf("Chrome") && (-1 === e || Number(F.substr(e + 8, 1)) > 3), p = f && 6 > Number(F.substr(F.indexOf("Version/") + 8, 1)), _ = -1 !== F.indexOf("Firefox"), /MSIE ([0-9]{1,}[\.0-9]{0,})/.exec(F) && (c = parseFloat(RegExp.$1)), i.innerHTML = "<a style='top:1px;opacity:.55;'>a</a>", t = i.getElementsByTagName("a")[0], t ? /^0.55/.test(t.style.opacity) : !1;
      }(),
      B = function (t) {
        return T.test("string" == typeof t ? t : (t.currentStyle ? t.currentStyle.filter : t.style.filter) || "") ? parseFloat(RegExp.$1) / 100 : 1;
      },
      U = function (t) {
        window.console && console.log(t);
      },
      W = "",
      j = "",
      V = function (t, e) {
        e = e || z;
        var i,
          r,
          s = e.style;
        if (void 0 !== s[t]) return t;
        for (t = t.charAt(0).toUpperCase() + t.substr(1), i = ["O", "Moz", "ms", "Ms", "Webkit"], r = 5; --r > -1 && void 0 === s[i[r] + t];);
        return r >= 0 ? (j = 3 === r ? "ms" : i[r], W = "-" + j.toLowerCase() + "-", j + t) : null;
      },
      H = X.defaultView ? X.defaultView.getComputedStyle : function () {},
      q = a.getStyle = function (t, e, i, r, s) {
        var n;
        return Y || "opacity" !== e ? (!r && t.style[e] ? n = t.style[e] : (i = i || H(t)) ? n = i[e] || i.getPropertyValue(e) || i.getPropertyValue(e.replace(P, "-$1").toLowerCase()) : t.currentStyle && (n = t.currentStyle[e]), null == s || n && "none" !== n && "auto" !== n && "auto auto" !== n ? n : s) : B(t);
      },
      Q = E.convertToPixels = function (t, i, r, s, n) {
        if ("px" === s || !s) return r;
        if ("auto" === s || !r) return 0;
        var o,
          l,
          h,
          u = k.test(i),
          f = t,
          _ = z.style,
          p = 0 > r;
        if (p && (r = -r), "%" === s && -1 !== i.indexOf("border")) o = r / 100 * (u ? t.clientWidth : t.clientHeight);else {
          if (_.cssText = "border:0 solid red;position:" + q(t, "position") + ";line-height:0;", "%" !== s && f.appendChild) _[u ? "borderLeftWidth" : "borderTopWidth"] = r + s;else {
            if (f = t.parentNode || X.body, l = f._gsCache, h = e.ticker.frame, l && u && l.time === h) return l.width * r / 100;
            _[u ? "width" : "height"] = r + s;
          }
          f.appendChild(z), o = parseFloat(z[u ? "offsetWidth" : "offsetHeight"]), f.removeChild(z), u && "%" === s && a.cacheWidths !== !1 && (l = f._gsCache = f._gsCache || {}, l.time = h, l.width = 100 * (o / r)), 0 !== o || n || (o = Q(t, i, r, s, !0));
        }
        return p ? -o : o;
      },
      Z = E.calculateOffset = function (t, e, i) {
        if ("absolute" !== q(t, "position", i)) return 0;
        var r = "left" === e ? "Left" : "Top",
          s = q(t, "margin" + r, i);
        return t["offset" + r] - (Q(t, e, parseFloat(s), s.replace(y, "")) || 0);
      },
      $ = function (t, e) {
        var i,
          r,
          s = {};
        if (e = e || H(t, null)) {
          if (i = e.length) for (; --i > -1;) s[e[i].replace(S, R)] = e.getPropertyValue(e[i]);else for (i in e) s[i] = e[i];
        } else if (e = t.currentStyle || t.style) for (i in e) "string" == typeof i && void 0 === s[i] && (s[i.replace(S, R)] = e[i]);
        return Y || (s.opacity = B(t)), r = Pe(t, e, !1), s.rotation = r.rotation, s.skewX = r.skewX, s.scaleX = r.scaleX, s.scaleY = r.scaleY, s.x = r.x, s.y = r.y, xe && (s.z = r.z, s.rotationX = r.rotationX, s.rotationY = r.rotationY, s.scaleZ = r.scaleZ), s.filters && delete s.filters, s;
      },
      G = function (t, e, i, r, s) {
        var n,
          a,
          o,
          l = {},
          h = t.style;
        for (a in i) "cssText" !== a && "length" !== a && isNaN(a) && (e[a] !== (n = i[a]) || s && s[a]) && -1 === a.indexOf("Origin") && ("number" == typeof n || "string" == typeof n) && (l[a] = "auto" !== n || "left" !== a && "top" !== a ? "" !== n && "auto" !== n && "none" !== n || "string" != typeof e[a] || "" === e[a].replace(v, "") ? n : 0 : Z(t, a), void 0 !== h[a] && (o = new fe(h, a, h[a], o)));
        if (r) for (a in r) "className" !== a && (l[a] = r[a]);
        return {
          difs: l,
          firstMPT: o
        };
      },
      K = {
        width: ["Left", "Right"],
        height: ["Top", "Bottom"]
      },
      J = ["marginLeft", "marginRight", "marginTop", "marginBottom"],
      te = function (t, e, i) {
        var r = parseFloat("width" === e ? t.offsetWidth : t.offsetHeight),
          s = K[e],
          n = s.length;
        for (i = i || H(t, null); --n > -1;) r -= parseFloat(q(t, "padding" + s[n], i, !0)) || 0, r -= parseFloat(q(t, "border" + s[n] + "Width", i, !0)) || 0;
        return r;
      },
      ee = function (t, e) {
        (null == t || "" === t || "auto" === t || "auto auto" === t) && (t = "0 0");
        var i = t.split(" "),
          r = -1 !== t.indexOf("left") ? "0%" : -1 !== t.indexOf("right") ? "100%" : i[0],
          s = -1 !== t.indexOf("top") ? "0%" : -1 !== t.indexOf("bottom") ? "100%" : i[1];
        return null == s ? s = "0" : "center" === s && (s = "50%"), ("center" === r || isNaN(parseFloat(r)) && -1 === (r + "").indexOf("=")) && (r = "50%"), e && (e.oxp = -1 !== r.indexOf("%"), e.oyp = -1 !== s.indexOf("%"), e.oxr = "=" === r.charAt(1), e.oyr = "=" === s.charAt(1), e.ox = parseFloat(r.replace(v, "")), e.oy = parseFloat(s.replace(v, ""))), r + " " + s + (i.length > 2 ? " " + i[2] : "");
      },
      ie = function (t, e) {
        return "string" == typeof t && "=" === t.charAt(1) ? parseInt(t.charAt(0) + "1", 10) * parseFloat(t.substr(2)) : parseFloat(t) - parseFloat(e);
      },
      re = function (t, e) {
        return null == t ? e : "string" == typeof t && "=" === t.charAt(1) ? parseInt(t.charAt(0) + "1", 10) * Number(t.substr(2)) + e : parseFloat(t);
      },
      se = function (t, e, i, r) {
        var s,
          n,
          a,
          o,
          l = 1e-6;
        return null == t ? o = e : "number" == typeof t ? o = t : (s = 360, n = t.split("_"), a = Number(n[0].replace(v, "")) * (-1 === t.indexOf("rad") ? 1 : L) - ("=" === t.charAt(1) ? 0 : e), n.length && (r && (r[i] = e + a), -1 !== t.indexOf("short") && (a %= s, a !== a % (s / 2) && (a = 0 > a ? a + s : a - s)), -1 !== t.indexOf("_cw") && 0 > a ? a = (a + 9999999999 * s) % s - (0 | a / s) * s : -1 !== t.indexOf("ccw") && a > 0 && (a = (a - 9999999999 * s) % s - (0 | a / s) * s)), o = e + a), l > o && o > -l && (o = 0), o;
      },
      ne = {
        aqua: [0, 255, 255],
        lime: [0, 255, 0],
        silver: [192, 192, 192],
        black: [0, 0, 0],
        maroon: [128, 0, 0],
        teal: [0, 128, 128],
        blue: [0, 0, 255],
        navy: [0, 0, 128],
        white: [255, 255, 255],
        fuchsia: [255, 0, 255],
        olive: [128, 128, 0],
        yellow: [255, 255, 0],
        orange: [255, 165, 0],
        gray: [128, 128, 128],
        purple: [128, 0, 128],
        green: [0, 128, 0],
        red: [255, 0, 0],
        pink: [255, 192, 203],
        cyan: [0, 255, 255],
        transparent: [255, 255, 255, 0]
      },
      ae = function (t, e, i) {
        return t = 0 > t ? t + 1 : t > 1 ? t - 1 : t, 0 | 255 * (1 > 6 * t ? e + 6 * (i - e) * t : .5 > t ? i : 2 > 3 * t ? e + 6 * (i - e) * (2 / 3 - t) : e) + .5;
      },
      oe = function (t) {
        var e, i, r, s, n, a;
        return t && "" !== t ? "number" == typeof t ? [t >> 16, 255 & t >> 8, 255 & t] : ("," === t.charAt(t.length - 1) && (t = t.substr(0, t.length - 1)), ne[t] ? ne[t] : "#" === t.charAt(0) ? (4 === t.length && (e = t.charAt(1), i = t.charAt(2), r = t.charAt(3), t = "#" + e + e + i + i + r + r), t = parseInt(t.substr(1), 16), [t >> 16, 255 & t >> 8, 255 & t]) : "hsl" === t.substr(0, 3) ? (t = t.match(d), s = Number(t[0]) % 360 / 360, n = Number(t[1]) / 100, a = Number(t[2]) / 100, i = .5 >= a ? a * (n + 1) : a + n - a * n, e = 2 * a - i, t.length > 3 && (t[3] = Number(t[3])), t[0] = ae(s + 1 / 3, e, i), t[1] = ae(s, e, i), t[2] = ae(s - 1 / 3, e, i), t) : (t = t.match(d) || ne.transparent, t[0] = Number(t[0]), t[1] = Number(t[1]), t[2] = Number(t[2]), t.length > 3 && (t[3] = Number(t[3])), t)) : ne.black;
      },
      le = "(?:\\b(?:(?:rgb|rgba|hsl|hsla)\\(.+?\\))|\\B#.+?\\b";
    for (l in ne) le += "|" + l + "\\b";
    le = RegExp(le + ")", "gi");
    var he = function (t, e, i, r) {
        if (null == t) return function (t) {
          return t;
        };
        var s,
          n = e ? (t.match(le) || [""])[0] : "",
          a = t.split(n).join("").match(g) || [],
          o = t.substr(0, t.indexOf(a[0])),
          l = ")" === t.charAt(t.length - 1) ? ")" : "",
          h = -1 !== t.indexOf(" ") ? " " : ",",
          u = a.length,
          f = u > 0 ? a[0].replace(d, "") : "";
        return u ? s = e ? function (t) {
          var e, _, p, c;
          if ("number" == typeof t) t += f;else if (r && D.test(t)) {
            for (c = t.replace(D, "|").split("|"), p = 0; c.length > p; p++) c[p] = s(c[p]);
            return c.join(",");
          }
          if (e = (t.match(le) || [n])[0], _ = t.split(e).join("").match(g) || [], p = _.length, u > p--) for (; u > ++p;) _[p] = i ? _[0 | (p - 1) / 2] : a[p];
          return o + _.join(h) + h + e + l + (-1 !== t.indexOf("inset") ? " inset" : "");
        } : function (t) {
          var e, n, _;
          if ("number" == typeof t) t += f;else if (r && D.test(t)) {
            for (n = t.replace(D, "|").split("|"), _ = 0; n.length > _; _++) n[_] = s(n[_]);
            return n.join(",");
          }
          if (e = t.match(g) || [], _ = e.length, u > _--) for (; u > ++_;) e[_] = i ? e[0 | (_ - 1) / 2] : a[_];
          return o + e.join(h) + l;
        } : function (t) {
          return t;
        };
      },
      ue = function (t) {
        return t = t.split(","), function (e, i, r, s, n, a, o) {
          var l,
            h = (i + "").split(" ");
          for (o = {}, l = 0; 4 > l; l++) o[t[l]] = h[l] = h[l] || h[(l - 1) / 2 >> 0];
          return s.parse(e, o, n, a);
        };
      },
      fe = (E._setPluginRatio = function (t) {
        this.plugin.setRatio(t);
        for (var e, i, r, s, n = this.data, a = n.proxy, o = n.firstMPT, l = 1e-6; o;) e = a[o.v], o.r ? e = Math.round(e) : l > e && e > -l && (e = 0), o.t[o.p] = e, o = o._next;
        if (n.autoRotate && (n.autoRotate.rotation = a.rotation), 1 === t) for (o = n.firstMPT; o;) {
          if (i = o.t, i.type) {
            if (1 === i.type) {
              for (s = i.xs0 + i.s + i.xs1, r = 1; i.l > r; r++) s += i["xn" + r] + i["xs" + (r + 1)];
              i.e = s;
            }
          } else i.e = i.s + i.xs0;
          o = o._next;
        }
      }, function (t, e, i, r, s) {
        this.t = t, this.p = e, this.v = i, this.r = s, r && (r._prev = this, this._next = r);
      }),
      _e = (E._parseToProxy = function (t, e, i, r, s, n) {
        var a,
          o,
          l,
          h,
          u,
          f = r,
          _ = {},
          p = {},
          c = i._transform,
          d = N;
        for (i._transform = null, N = e, r = u = i.parse(t, e, r, s), N = d, n && (i._transform = c, f && (f._prev = null, f._prev && (f._prev._next = null))); r && r !== f;) {
          if (1 >= r.type && (o = r.p, p[o] = r.s + r.c, _[o] = r.s, n || (h = new fe(r, "s", o, h, r.r), r.c = 0), 1 === r.type)) for (a = r.l; --a > 0;) l = "xn" + a, o = r.p + "_" + l, p[o] = r.data[l], _[o] = r[l], n || (h = new fe(r, l, o, h, r.rxp[l]));
          r = r._next;
        }
        return {
          proxy: _,
          end: p,
          firstMPT: h,
          pt: u
        };
      }, E.CSSPropTween = function (t, e, r, s, a, o, l, h, u, f, _) {
        this.t = t, this.p = e, this.s = r, this.c = s, this.n = l || e, t instanceof _e || n.push(this.n), this.r = h, this.type = o || 0, u && (this.pr = u, i = !0), this.b = void 0 === f ? r : f, this.e = void 0 === _ ? r + s : _, a && (this._next = a, a._prev = this);
      }),
      pe = a.parseComplex = function (t, e, i, r, s, n, a, o, l, u) {
        i = i || n || "", a = new _e(t, e, 0, 0, a, u ? 2 : 1, null, !1, o, i, r), r += "";
        var f,
          _,
          p,
          c,
          g,
          v,
          y,
          T,
          w,
          x,
          P,
          S,
          C = i.split(", ").join(",").split(" "),
          R = r.split(", ").join(",").split(" "),
          k = C.length,
          A = h !== !1;
        for ((-1 !== r.indexOf(",") || -1 !== i.indexOf(",")) && (C = C.join(" ").replace(D, ", ").split(" "), R = R.join(" ").replace(D, ", ").split(" "), k = C.length), k !== R.length && (C = (n || "").split(" "), k = C.length), a.plugin = l, a.setRatio = u, f = 0; k > f; f++) if (c = C[f], g = R[f], T = parseFloat(c), T || 0 === T) a.appendXtra("", T, ie(g, T), g.replace(m, ""), A && -1 !== g.indexOf("px"), !0);else if (s && ("#" === c.charAt(0) || ne[c] || b.test(c))) S = "," === g.charAt(g.length - 1) ? ")," : ")", c = oe(c), g = oe(g), w = c.length + g.length > 6, w && !Y && 0 === g[3] ? (a["xs" + a.l] += a.l ? " transparent" : "transparent", a.e = a.e.split(R[f]).join("transparent")) : (Y || (w = !1), a.appendXtra(w ? "rgba(" : "rgb(", c[0], g[0] - c[0], ",", !0, !0).appendXtra("", c[1], g[1] - c[1], ",", !0).appendXtra("", c[2], g[2] - c[2], w ? "," : S, !0), w && (c = 4 > c.length ? 1 : c[3], a.appendXtra("", c, (4 > g.length ? 1 : g[3]) - c, S, !1)));else if (v = c.match(d)) {
          if (y = g.match(m), !y || y.length !== v.length) return a;
          for (p = 0, _ = 0; v.length > _; _++) P = v[_], x = c.indexOf(P, p), a.appendXtra(c.substr(p, x - p), Number(P), ie(y[_], P), "", A && "px" === c.substr(x + P.length, 2), 0 === _), p = x + P.length;
          a["xs" + a.l] += c.substr(p);
        } else a["xs" + a.l] += a.l ? " " + c : c;
        if (-1 !== r.indexOf("=") && a.data) {
          for (S = a.xs0 + a.data.s, f = 1; a.l > f; f++) S += a["xs" + f] + a.data["xn" + f];
          a.e = S + a["xs" + f];
        }
        return a.l || (a.type = -1, a.xs0 = a.e), a.xfirst || a;
      },
      ce = 9;
    for (l = _e.prototype, l.l = l.pr = 0; --ce > 0;) l["xn" + ce] = 0, l["xs" + ce] = "";
    l.xs0 = "", l._next = l._prev = l.xfirst = l.data = l.plugin = l.setRatio = l.rxp = null, l.appendXtra = function (t, e, i, r, s, n) {
      var a = this,
        o = a.l;
      return a["xs" + o] += n && o ? " " + t : t || "", i || 0 === o || a.plugin ? (a.l++, a.type = a.setRatio ? 2 : 1, a["xs" + a.l] = r || "", o > 0 ? (a.data["xn" + o] = e + i, a.rxp["xn" + o] = s, a["xn" + o] = e, a.plugin || (a.xfirst = new _e(a, "xn" + o, e, i, a.xfirst || a, 0, a.n, s, a.pr), a.xfirst.xs0 = 0), a) : (a.data = {
        s: e + i
      }, a.rxp = {}, a.s = e, a.c = i, a.r = s, a)) : (a["xs" + o] += e + (r || ""), a);
    };
    var de = function (t, e) {
        e = e || {}, this.p = e.prefix ? V(t) || t : t, o[t] = o[this.p] = this, this.format = e.formatter || he(e.defaultValue, e.color, e.collapsible, e.multi), e.parser && (this.parse = e.parser), this.clrs = e.color, this.multi = e.multi, this.keyword = e.keyword, this.dflt = e.defaultValue, this.pr = e.priority || 0;
      },
      me = E._registerComplexSpecialProp = function (t, e, i) {
        "object" != typeof e && (e = {
          parser: i
        });
        var r,
          s,
          n = t.split(","),
          a = e.defaultValue;
        for (i = i || [a], r = 0; n.length > r; r++) e.prefix = 0 === r && e.prefix, e.defaultValue = i[r] || a, s = new de(n[r], e);
      },
      ge = function (t) {
        if (!o[t]) {
          var e = t.charAt(0).toUpperCase() + t.substr(1) + "Plugin";
          me(t, {
            parser: function (t, i, r, s, n, a, l) {
              var h = (window.GreenSockGlobals || window).com.greensock.plugins[e];
              return h ? (h._cssRegister(), o[r].parse(t, i, r, s, n, a, l)) : (U("Error: " + e + " js file not loaded."), n);
            }
          });
        }
      };
    l = de.prototype, l.parseComplex = function (t, e, i, r, s, n) {
      var a,
        o,
        l,
        h,
        u,
        f,
        _ = this.keyword;
      if (this.multi && (D.test(i) || D.test(e) ? (o = e.replace(D, "|").split("|"), l = i.replace(D, "|").split("|")) : _ && (o = [e], l = [i])), l) {
        for (h = l.length > o.length ? l.length : o.length, a = 0; h > a; a++) e = o[a] = o[a] || this.dflt, i = l[a] = l[a] || this.dflt, _ && (u = e.indexOf(_), f = i.indexOf(_), u !== f && (i = -1 === f ? l : o, i[a] += " " + _));
        e = o.join(", "), i = l.join(", ");
      }
      return pe(t, this.p, e, i, this.clrs, this.dflt, r, this.pr, s, n);
    }, l.parse = function (t, e, i, r, n, a) {
      return this.parseComplex(t.style, this.format(q(t, this.p, s, !1, this.dflt)), this.format(e), n, a);
    }, a.registerSpecialProp = function (t, e, i) {
      me(t, {
        parser: function (t, r, s, n, a, o) {
          var l = new _e(t, s, 0, 0, a, 2, s, !1, i);
          return l.plugin = o, l.setRatio = e(t, r, n._tween, s), l;
        },
        priority: i
      });
    };
    var ve = "scaleX,scaleY,scaleZ,x,y,z,skewX,skewY,rotation,rotationX,rotationY,perspective".split(","),
      ye = V("transform"),
      Te = W + "transform",
      we = V("transformOrigin"),
      xe = null !== V("perspective"),
      be = E.Transform = function () {
        this.skewY = 0;
      },
      Pe = E.getTransform = function (t, e, i, r) {
        if (t._gsTransform && i && !r) return t._gsTransform;
        var s,
          n,
          o,
          l,
          h,
          u,
          f,
          _,
          p,
          c,
          d,
          m,
          g,
          v = i ? t._gsTransform || new be() : new be(),
          y = 0 > v.scaleX,
          T = 2e-5,
          w = 1e5,
          x = 179.99,
          b = x * M,
          P = xe ? parseFloat(q(t, we, e, !1, "0 0 0").split(" ")[2]) || v.zOrigin || 0 : 0;
        for (ye ? s = q(t, Te, e, !0) : t.currentStyle && (s = t.currentStyle.filter.match(A), s = s && 4 === s.length ? [s[0].substr(4), Number(s[2].substr(4)), Number(s[1].substr(4)), s[3].substr(4), v.x || 0, v.y || 0].join(",") : ""), n = (s || "").match(/(?:\-|\b)[\d\-\.e]+\b/gi) || [], o = n.length; --o > -1;) l = Number(n[o]), n[o] = (h = l - (l |= 0)) ? (0 | h * w + (0 > h ? -.5 : .5)) / w + l : l;
        if (16 === n.length) {
          var S = n[8],
            C = n[9],
            R = n[10],
            k = n[12],
            O = n[13],
            D = n[14];
          if (v.zOrigin && (D = -v.zOrigin, k = S * D - n[12], O = C * D - n[13], D = R * D + v.zOrigin - n[14]), !i || r || null == v.rotationX) {
            var N,
              X,
              z,
              I,
              E,
              F,
              Y,
              B = n[0],
              U = n[1],
              W = n[2],
              j = n[3],
              V = n[4],
              H = n[5],
              Q = n[6],
              Z = n[7],
              $ = n[11],
              G = Math.atan2(Q, R),
              K = -b > G || G > b;
            v.rotationX = G * L, G && (I = Math.cos(-G), E = Math.sin(-G), N = V * I + S * E, X = H * I + C * E, z = Q * I + R * E, S = V * -E + S * I, C = H * -E + C * I, R = Q * -E + R * I, $ = Z * -E + $ * I, V = N, H = X, Q = z), G = Math.atan2(S, B), v.rotationY = G * L, G && (F = -b > G || G > b, I = Math.cos(-G), E = Math.sin(-G), N = B * I - S * E, X = U * I - C * E, z = W * I - R * E, C = U * E + C * I, R = W * E + R * I, $ = j * E + $ * I, B = N, U = X, W = z), G = Math.atan2(U, H), v.rotation = G * L, G && (Y = -b > G || G > b, I = Math.cos(-G), E = Math.sin(-G), B = B * I + V * E, X = U * I + H * E, H = U * -E + H * I, Q = W * -E + Q * I, U = X), Y && K ? v.rotation = v.rotationX = 0 : Y && F ? v.rotation = v.rotationY = 0 : F && K && (v.rotationY = v.rotationX = 0), v.scaleX = (0 | Math.sqrt(B * B + U * U) * w + .5) / w, v.scaleY = (0 | Math.sqrt(H * H + C * C) * w + .5) / w, v.scaleZ = (0 | Math.sqrt(Q * Q + R * R) * w + .5) / w, v.skewX = 0, v.perspective = $ ? 1 / (0 > $ ? -$ : $) : 0, v.x = k, v.y = O, v.z = D;
          }
        } else if (!(xe && !r && n.length && v.x === n[4] && v.y === n[5] && (v.rotationX || v.rotationY) || void 0 !== v.x && "none" === q(t, "display", e))) {
          var J = n.length >= 6,
            te = J ? n[0] : 1,
            ee = n[1] || 0,
            ie = n[2] || 0,
            re = J ? n[3] : 1;
          v.x = n[4] || 0, v.y = n[5] || 0, u = Math.sqrt(te * te + ee * ee), f = Math.sqrt(re * re + ie * ie), _ = te || ee ? Math.atan2(ee, te) * L : v.rotation || 0, p = ie || re ? Math.atan2(ie, re) * L + _ : v.skewX || 0, c = u - Math.abs(v.scaleX || 0), d = f - Math.abs(v.scaleY || 0), Math.abs(p) > 90 && 270 > Math.abs(p) && (y ? (u *= -1, p += 0 >= _ ? 180 : -180, _ += 0 >= _ ? 180 : -180) : (f *= -1, p += 0 >= p ? 180 : -180)), m = (_ - v.rotation) % 180, g = (p - v.skewX) % 180, (void 0 === v.skewX || c > T || -T > c || d > T || -T > d || m > -x && x > m && false | m * w || g > -x && x > g && false | g * w) && (v.scaleX = u, v.scaleY = f, v.rotation = _, v.skewX = p), xe && (v.rotationX = v.rotationY = v.z = 0, v.perspective = parseFloat(a.defaultTransformPerspective) || 0, v.scaleZ = 1);
        }
        v.zOrigin = P;
        for (o in v) T > v[o] && v[o] > -T && (v[o] = 0);
        return i && (t._gsTransform = v), v;
      },
      Se = function (t) {
        var e,
          i,
          r = this.data,
          s = -r.rotation * M,
          n = s + r.skewX * M,
          a = 1e5,
          o = (0 | Math.cos(s) * r.scaleX * a) / a,
          l = (0 | Math.sin(s) * r.scaleX * a) / a,
          h = (0 | Math.sin(n) * -r.scaleY * a) / a,
          u = (0 | Math.cos(n) * r.scaleY * a) / a,
          f = this.t.style,
          _ = this.t.currentStyle;
        if (_) {
          i = l, l = -h, h = -i, e = _.filter, f.filter = "";
          var p,
            d,
            m = this.t.offsetWidth,
            g = this.t.offsetHeight,
            v = "absolute" !== _.position,
            w = "progid:DXImageTransform.Microsoft.Matrix(M11=" + o + ", M12=" + l + ", M21=" + h + ", M22=" + u,
            x = r.x,
            b = r.y;
          if (null != r.ox && (p = (r.oxp ? .01 * m * r.ox : r.ox) - m / 2, d = (r.oyp ? .01 * g * r.oy : r.oy) - g / 2, x += p - (p * o + d * l), b += d - (p * h + d * u)), v ? (p = m / 2, d = g / 2, w += ", Dx=" + (p - (p * o + d * l) + x) + ", Dy=" + (d - (p * h + d * u) + b) + ")") : w += ", sizingMethod='auto expand')", f.filter = -1 !== e.indexOf("DXImageTransform.Microsoft.Matrix(") ? e.replace(O, w) : w + " " + e, (0 === t || 1 === t) && 1 === o && 0 === l && 0 === h && 1 === u && (v && -1 === w.indexOf("Dx=0, Dy=0") || T.test(e) && 100 !== parseFloat(RegExp.$1) || -1 === e.indexOf("gradient(" && e.indexOf("Alpha")) && f.removeAttribute("filter")), !v) {
            var P,
              S,
              C,
              R = 8 > c ? 1 : -1;
            for (p = r.ieOffsetX || 0, d = r.ieOffsetY || 0, r.ieOffsetX = Math.round((m - ((0 > o ? -o : o) * m + (0 > l ? -l : l) * g)) / 2 + x), r.ieOffsetY = Math.round((g - ((0 > u ? -u : u) * g + (0 > h ? -h : h) * m)) / 2 + b), ce = 0; 4 > ce; ce++) S = J[ce], P = _[S], i = -1 !== P.indexOf("px") ? parseFloat(P) : Q(this.t, S, parseFloat(P), P.replace(y, "")) || 0, C = i !== r[S] ? 2 > ce ? -r.ieOffsetX : -r.ieOffsetY : 2 > ce ? p - r.ieOffsetX : d - r.ieOffsetY, f[S] = (r[S] = Math.round(i - C * (0 === ce || 2 === ce ? 1 : R))) + "px";
          }
        }
      },
      Ce = E.set3DTransformRatio = function (t) {
        var e,
          i,
          r,
          s,
          n,
          a,
          o,
          l,
          h,
          u,
          f,
          p,
          c,
          d,
          m,
          g,
          v,
          y,
          T,
          w,
          x,
          b,
          P,
          S = this.data,
          C = this.t.style,
          R = S.rotation * M,
          k = S.scaleX,
          A = S.scaleY,
          O = S.scaleZ,
          D = S.perspective;
        if (!(1 !== t && 0 !== t || "auto" !== S.force3D || S.rotationY || S.rotationX || 1 !== O || D || S.z)) return Re.call(this, t), void 0;
        if (_) {
          var L = 1e-4;
          L > k && k > -L && (k = O = 2e-5), L > A && A > -L && (A = O = 2e-5), !D || S.z || S.rotationX || S.rotationY || (D = 0);
        }
        if (R || S.skewX) y = Math.cos(R), T = Math.sin(R), e = y, n = T, S.skewX && (R -= S.skewX * M, y = Math.cos(R), T = Math.sin(R), "simple" === S.skewType && (w = Math.tan(S.skewX * M), w = Math.sqrt(1 + w * w), y *= w, T *= w)), i = -T, a = y;else {
          if (!(S.rotationY || S.rotationX || 1 !== O || D)) return C[ye] = "translate3d(" + S.x + "px," + S.y + "px," + S.z + "px)" + (1 !== k || 1 !== A ? " scale(" + k + "," + A + ")" : ""), void 0;
          e = a = 1, i = n = 0;
        }
        f = 1, r = s = o = l = h = u = p = c = d = 0, m = D ? -1 / D : 0, g = S.zOrigin, v = 1e5, R = S.rotationY * M, R && (y = Math.cos(R), T = Math.sin(R), h = f * -T, c = m * -T, r = e * T, o = n * T, f *= y, m *= y, e *= y, n *= y), R = S.rotationX * M, R && (y = Math.cos(R), T = Math.sin(R), w = i * y + r * T, x = a * y + o * T, b = u * y + f * T, P = d * y + m * T, r = i * -T + r * y, o = a * -T + o * y, f = u * -T + f * y, m = d * -T + m * y, i = w, a = x, u = b, d = P), 1 !== O && (r *= O, o *= O, f *= O, m *= O), 1 !== A && (i *= A, a *= A, u *= A, d *= A), 1 !== k && (e *= k, n *= k, h *= k, c *= k), g && (p -= g, s = r * p, l = o * p, p = f * p + g), s = (w = (s += S.x) - (s |= 0)) ? (0 | w * v + (0 > w ? -.5 : .5)) / v + s : s, l = (w = (l += S.y) - (l |= 0)) ? (0 | w * v + (0 > w ? -.5 : .5)) / v + l : l, p = (w = (p += S.z) - (p |= 0)) ? (0 | w * v + (0 > w ? -.5 : .5)) / v + p : p, C[ye] = "matrix3d(" + [(0 | e * v) / v, (0 | n * v) / v, (0 | h * v) / v, (0 | c * v) / v, (0 | i * v) / v, (0 | a * v) / v, (0 | u * v) / v, (0 | d * v) / v, (0 | r * v) / v, (0 | o * v) / v, (0 | f * v) / v, (0 | m * v) / v, s, l, p, D ? 1 + -p / D : 1].join(",") + ")";
      },
      Re = E.set2DTransformRatio = function (t) {
        var e,
          i,
          r,
          s,
          n,
          a = this.data,
          o = this.t,
          l = o.style;
        return a.rotationX || a.rotationY || a.z || a.force3D === !0 || "auto" === a.force3D && 1 !== t && 0 !== t ? (this.setRatio = Ce, Ce.call(this, t), void 0) : (a.rotation || a.skewX ? (e = a.rotation * M, i = e - a.skewX * M, r = 1e5, s = a.scaleX * r, n = a.scaleY * r, l[ye] = "matrix(" + (0 | Math.cos(e) * s) / r + "," + (0 | Math.sin(e) * s) / r + "," + (0 | Math.sin(i) * -n) / r + "," + (0 | Math.cos(i) * n) / r + "," + a.x + "," + a.y + ")") : l[ye] = "matrix(" + a.scaleX + ",0,0," + a.scaleY + "," + a.x + "," + a.y + ")", void 0);
      };
    me("transform,scale,scaleX,scaleY,scaleZ,x,y,z,rotation,rotationX,rotationY,rotationZ,skewX,skewY,shortRotation,shortRotationX,shortRotationY,shortRotationZ,transformOrigin,transformPerspective,directionalRotation,parseTransform,force3D,skewType", {
      parser: function (t, e, i, r, n, o, l) {
        if (r._transform) return n;
        var h,
          u,
          f,
          _,
          p,
          c,
          d,
          m = r._transform = Pe(t, s, !0, l.parseTransform),
          g = t.style,
          v = 1e-6,
          y = ve.length,
          T = l,
          w = {};
        if ("string" == typeof T.transform && ye) f = z.style, f[ye] = T.transform, f.display = "block", f.position = "absolute", X.body.appendChild(z), h = Pe(z, null, !1), X.body.removeChild(z);else if ("object" == typeof T) {
          if (h = {
            scaleX: re(null != T.scaleX ? T.scaleX : T.scale, m.scaleX),
            scaleY: re(null != T.scaleY ? T.scaleY : T.scale, m.scaleY),
            scaleZ: re(T.scaleZ, m.scaleZ),
            x: re(T.x, m.x),
            y: re(T.y, m.y),
            z: re(T.z, m.z),
            perspective: re(T.transformPerspective, m.perspective)
          }, d = T.directionalRotation, null != d) if ("object" == typeof d) for (f in d) T[f] = d[f];else T.rotation = d;
          h.rotation = se("rotation" in T ? T.rotation : "shortRotation" in T ? T.shortRotation + "_short" : "rotationZ" in T ? T.rotationZ : m.rotation, m.rotation, "rotation", w), xe && (h.rotationX = se("rotationX" in T ? T.rotationX : "shortRotationX" in T ? T.shortRotationX + "_short" : m.rotationX || 0, m.rotationX, "rotationX", w), h.rotationY = se("rotationY" in T ? T.rotationY : "shortRotationY" in T ? T.shortRotationY + "_short" : m.rotationY || 0, m.rotationY, "rotationY", w)), h.skewX = null == T.skewX ? m.skewX : se(T.skewX, m.skewX), h.skewY = null == T.skewY ? m.skewY : se(T.skewY, m.skewY), (u = h.skewY - m.skewY) && (h.skewX += u, h.rotation += u);
        }
        for (xe && null != T.force3D && (m.force3D = T.force3D, c = !0), m.skewType = T.skewType || m.skewType || a.defaultSkewType, p = m.force3D || m.z || m.rotationX || m.rotationY || h.z || h.rotationX || h.rotationY || h.perspective, p || null == T.scale || (h.scaleZ = 1); --y > -1;) i = ve[y], _ = h[i] - m[i], (_ > v || -v > _ || null != N[i]) && (c = !0, n = new _e(m, i, m[i], _, n), i in w && (n.e = w[i]), n.xs0 = 0, n.plugin = o, r._overwriteProps.push(n.n));
        return _ = T.transformOrigin, (_ || xe && p && m.zOrigin) && (ye ? (c = !0, i = we, _ = (_ || q(t, i, s, !1, "50% 50%")) + "", n = new _e(g, i, 0, 0, n, -1, "transformOrigin"), n.b = g[i], n.plugin = o, xe ? (f = m.zOrigin, _ = _.split(" "), m.zOrigin = (_.length > 2 && (0 === f || "0px" !== _[2]) ? parseFloat(_[2]) : f) || 0, n.xs0 = n.e = _[0] + " " + (_[1] || "50%") + " 0px", n = new _e(m, "zOrigin", 0, 0, n, -1, n.n), n.b = f, n.xs0 = n.e = m.zOrigin) : n.xs0 = n.e = _) : ee(_ + "", m)), c && (r._transformType = p || 3 === this._transformType ? 3 : 2), n;
      },
      prefix: !0
    }), me("boxShadow", {
      defaultValue: "0px 0px 0px 0px #999",
      prefix: !0,
      color: !0,
      multi: !0,
      keyword: "inset"
    }), me("borderRadius", {
      defaultValue: "0px",
      parser: function (t, e, i, n, a) {
        e = this.format(e);
        var o,
          l,
          h,
          u,
          f,
          _,
          p,
          c,
          d,
          m,
          g,
          v,
          y,
          T,
          w,
          x,
          b = ["borderTopLeftRadius", "borderTopRightRadius", "borderBottomRightRadius", "borderBottomLeftRadius"],
          P = t.style;
        for (d = parseFloat(t.offsetWidth), m = parseFloat(t.offsetHeight), o = e.split(" "), l = 0; b.length > l; l++) this.p.indexOf("border") && (b[l] = V(b[l])), f = u = q(t, b[l], s, !1, "0px"), -1 !== f.indexOf(" ") && (u = f.split(" "), f = u[0], u = u[1]), _ = h = o[l], p = parseFloat(f), v = f.substr((p + "").length), y = "=" === _.charAt(1), y ? (c = parseInt(_.charAt(0) + "1", 10), _ = _.substr(2), c *= parseFloat(_), g = _.substr((c + "").length - (0 > c ? 1 : 0)) || "") : (c = parseFloat(_), g = _.substr((c + "").length)), "" === g && (g = r[i] || v), g !== v && (T = Q(t, "borderLeft", p, v), w = Q(t, "borderTop", p, v), "%" === g ? (f = 100 * (T / d) + "%", u = 100 * (w / m) + "%") : "em" === g ? (x = Q(t, "borderLeft", 1, "em"), f = T / x + "em", u = w / x + "em") : (f = T + "px", u = w + "px"), y && (_ = parseFloat(f) + c + g, h = parseFloat(u) + c + g)), a = pe(P, b[l], f + " " + u, _ + " " + h, !1, "0px", a);
        return a;
      },
      prefix: !0,
      formatter: he("0px 0px 0px 0px", !1, !0)
    }), me("backgroundPosition", {
      defaultValue: "0 0",
      parser: function (t, e, i, r, n, a) {
        var o,
          l,
          h,
          u,
          f,
          _,
          p = "background-position",
          d = s || H(t, null),
          m = this.format((d ? c ? d.getPropertyValue(p + "-x") + " " + d.getPropertyValue(p + "-y") : d.getPropertyValue(p) : t.currentStyle.backgroundPositionX + " " + t.currentStyle.backgroundPositionY) || "0 0"),
          g = this.format(e);
        if (-1 !== m.indexOf("%") != (-1 !== g.indexOf("%")) && (_ = q(t, "backgroundImage").replace(C, ""), _ && "none" !== _)) {
          for (o = m.split(" "), l = g.split(" "), I.setAttribute("src", _), h = 2; --h > -1;) m = o[h], u = -1 !== m.indexOf("%"), u !== (-1 !== l[h].indexOf("%")) && (f = 0 === h ? t.offsetWidth - I.width : t.offsetHeight - I.height, o[h] = u ? parseFloat(m) / 100 * f + "px" : 100 * (parseFloat(m) / f) + "%");
          m = o.join(" ");
        }
        return this.parseComplex(t.style, m, g, n, a);
      },
      formatter: ee
    }), me("backgroundSize", {
      defaultValue: "0 0",
      formatter: ee
    }), me("perspective", {
      defaultValue: "0px",
      prefix: !0
    }), me("perspectiveOrigin", {
      defaultValue: "50% 50%",
      prefix: !0
    }), me("transformStyle", {
      prefix: !0
    }), me("backfaceVisibility", {
      prefix: !0
    }), me("userSelect", {
      prefix: !0
    }), me("margin", {
      parser: ue("marginTop,marginRight,marginBottom,marginLeft")
    }), me("padding", {
      parser: ue("paddingTop,paddingRight,paddingBottom,paddingLeft")
    }), me("clip", {
      defaultValue: "rect(0px,0px,0px,0px)",
      parser: function (t, e, i, r, n, a) {
        var o, l, h;
        return 9 > c ? (l = t.currentStyle, h = 8 > c ? " " : ",", o = "rect(" + l.clipTop + h + l.clipRight + h + l.clipBottom + h + l.clipLeft + ")", e = this.format(e).split(",").join(h)) : (o = this.format(q(t, this.p, s, !1, this.dflt)), e = this.format(e)), this.parseComplex(t.style, o, e, n, a);
      }
    }), me("textShadow", {
      defaultValue: "0px 0px 0px #999",
      color: !0,
      multi: !0
    }), me("autoRound,strictUnits", {
      parser: function (t, e, i, r, s) {
        return s;
      }
    }), me("border", {
      defaultValue: "0px solid #000",
      parser: function (t, e, i, r, n, a) {
        return this.parseComplex(t.style, this.format(q(t, "borderTopWidth", s, !1, "0px") + " " + q(t, "borderTopStyle", s, !1, "solid") + " " + q(t, "borderTopColor", s, !1, "#000")), this.format(e), n, a);
      },
      color: !0,
      formatter: function (t) {
        var e = t.split(" ");
        return e[0] + " " + (e[1] || "solid") + " " + (t.match(le) || ["#000"])[0];
      }
    }), me("borderWidth", {
      parser: ue("borderTopWidth,borderRightWidth,borderBottomWidth,borderLeftWidth")
    }), me("float,cssFloat,styleFloat", {
      parser: function (t, e, i, r, s) {
        var n = t.style,
          a = "cssFloat" in n ? "cssFloat" : "styleFloat";
        return new _e(n, a, 0, 0, s, -1, i, !1, 0, n[a], e);
      }
    });
    var ke = function (t) {
      var e,
        i = this.t,
        r = i.filter || q(this.data, "filter"),
        s = 0 | this.s + this.c * t;
      100 === s && (-1 === r.indexOf("atrix(") && -1 === r.indexOf("radient(") && -1 === r.indexOf("oader(") ? (i.removeAttribute("filter"), e = !q(this.data, "filter")) : (i.filter = r.replace(x, ""), e = !0)), e || (this.xn1 && (i.filter = r = r || "alpha(opacity=" + s + ")"), -1 === r.indexOf("pacity") ? 0 === s && this.xn1 || (i.filter = r + " alpha(opacity=" + s + ")") : i.filter = r.replace(T, "opacity=" + s));
    };
    me("opacity,alpha,autoAlpha", {
      defaultValue: "1",
      parser: function (t, e, i, r, n, a) {
        var o = parseFloat(q(t, "opacity", s, !1, "1")),
          l = t.style,
          h = "autoAlpha" === i;
        return "string" == typeof e && "=" === e.charAt(1) && (e = ("-" === e.charAt(0) ? -1 : 1) * parseFloat(e.substr(2)) + o), h && 1 === o && "hidden" === q(t, "visibility", s) && 0 !== e && (o = 0), Y ? n = new _e(l, "opacity", o, e - o, n) : (n = new _e(l, "opacity", 100 * o, 100 * (e - o), n), n.xn1 = h ? 1 : 0, l.zoom = 1, n.type = 2, n.b = "alpha(opacity=" + n.s + ")", n.e = "alpha(opacity=" + (n.s + n.c) + ")", n.data = t, n.plugin = a, n.setRatio = ke), h && (n = new _e(l, "visibility", 0, 0, n, -1, null, !1, 0, 0 !== o ? "inherit" : "hidden", 0 === e ? "hidden" : "inherit"), n.xs0 = "inherit", r._overwriteProps.push(n.n), r._overwriteProps.push(i)), n;
      }
    });
    var Ae = function (t, e) {
        e && (t.removeProperty ? ("ms" === e.substr(0, 2) && (e = "M" + e.substr(1)), t.removeProperty(e.replace(P, "-$1").toLowerCase())) : t.removeAttribute(e));
      },
      Oe = function (t) {
        if (this.t._gsClassPT = this, 1 === t || 0 === t) {
          this.t.setAttribute("class", 0 === t ? this.b : this.e);
          for (var e = this.data, i = this.t.style; e;) e.v ? i[e.p] = e.v : Ae(i, e.p), e = e._next;
          1 === t && this.t._gsClassPT === this && (this.t._gsClassPT = null);
        } else this.t.getAttribute("class") !== this.e && this.t.setAttribute("class", this.e);
      };
    me("className", {
      parser: function (t, e, r, n, a, o, l) {
        var h,
          u,
          f,
          _,
          p,
          c = t.getAttribute("class") || "",
          d = t.style.cssText;
        if (a = n._classNamePT = new _e(t, r, 0, 0, a, 2), a.setRatio = Oe, a.pr = -11, i = !0, a.b = c, u = $(t, s), f = t._gsClassPT) {
          for (_ = {}, p = f.data; p;) _[p.p] = 1, p = p._next;
          f.setRatio(1);
        }
        return t._gsClassPT = a, a.e = "=" !== e.charAt(1) ? e : c.replace(RegExp("\\s*\\b" + e.substr(2) + "\\b"), "") + ("+" === e.charAt(0) ? " " + e.substr(2) : ""), n._tween._duration && (t.setAttribute("class", a.e), h = G(t, u, $(t), l, _), t.setAttribute("class", c), a.data = h.firstMPT, t.style.cssText = d, a = a.xfirst = n.parse(t, h.difs, a, o)), a;
      }
    });
    var De = function (t) {
      if ((1 === t || 0 === t) && this.data._totalTime === this.data._totalDuration && "isFromStart" !== this.data.data) {
        var e,
          i,
          r,
          s,
          n = this.t.style,
          a = o.transform.parse;
        if ("all" === this.e) n.cssText = "", s = !0;else for (e = this.e.split(","), r = e.length; --r > -1;) i = e[r], o[i] && (o[i].parse === a ? s = !0 : i = "transformOrigin" === i ? we : o[i].p), Ae(n, i);
        s && (Ae(n, ye), this.t._gsTransform && delete this.t._gsTransform);
      }
    };
    for (me("clearProps", {
      parser: function (t, e, r, s, n) {
        return n = new _e(t, r, 0, 0, n, 2), n.setRatio = De, n.e = e, n.pr = -10, n.data = s._tween, i = !0, n;
      }
    }), l = "bezier,throwProps,physicsProps,physics2D".split(","), ce = l.length; ce--;) ge(l[ce]);
    l = a.prototype, l._firstPT = null, l._onInitTween = function (t, e, o) {
      if (!t.nodeType) return !1;
      this._target = t, this._tween = o, this._vars = e, h = e.autoRound, i = !1, r = e.suffixMap || a.suffixMap, s = H(t, ""), n = this._overwriteProps;
      var l,
        _,
        c,
        d,
        m,
        g,
        v,
        y,
        T,
        x = t.style;
      if (u && "" === x.zIndex && (l = q(t, "zIndex", s), ("auto" === l || "" === l) && this._addLazySet(x, "zIndex", 0)), "string" == typeof e && (d = x.cssText, l = $(t, s), x.cssText = d + ";" + e, l = G(t, l, $(t)).difs, !Y && w.test(e) && (l.opacity = parseFloat(RegExp.$1)), e = l, x.cssText = d), this._firstPT = _ = this.parse(t, e, null), this._transformType) {
        for (T = 3 === this._transformType, ye ? f && (u = !0, "" === x.zIndex && (v = q(t, "zIndex", s), ("auto" === v || "" === v) && this._addLazySet(x, "zIndex", 0)), p && this._addLazySet(x, "WebkitBackfaceVisibility", this._vars.WebkitBackfaceVisibility || (T ? "visible" : "hidden"))) : x.zoom = 1, c = _; c && c._next;) c = c._next;
        y = new _e(t, "transform", 0, 0, null, 2), this._linkCSSP(y, null, c), y.setRatio = T && xe ? Ce : ye ? Re : Se, y.data = this._transform || Pe(t, s, !0), n.pop();
      }
      if (i) {
        for (; _;) {
          for (g = _._next, c = d; c && c.pr > _.pr;) c = c._next;
          (_._prev = c ? c._prev : m) ? _._prev._next = _ : d = _, (_._next = c) ? c._prev = _ : m = _, _ = g;
        }
        this._firstPT = d;
      }
      return !0;
    }, l.parse = function (t, e, i, n) {
      var a,
        l,
        u,
        f,
        _,
        p,
        c,
        d,
        m,
        g,
        v = t.style;
      for (a in e) p = e[a], l = o[a], l ? i = l.parse(t, p, a, this, i, n, e) : (_ = q(t, a, s) + "", m = "string" == typeof p, "color" === a || "fill" === a || "stroke" === a || -1 !== a.indexOf("Color") || m && b.test(p) ? (m || (p = oe(p), p = (p.length > 3 ? "rgba(" : "rgb(") + p.join(",") + ")"), i = pe(v, a, _, p, !0, "transparent", i, 0, n)) : !m || -1 === p.indexOf(" ") && -1 === p.indexOf(",") ? (u = parseFloat(_), c = u || 0 === u ? _.substr((u + "").length) : "", ("" === _ || "auto" === _) && ("width" === a || "height" === a ? (u = te(t, a, s), c = "px") : "left" === a || "top" === a ? (u = Z(t, a, s), c = "px") : (u = "opacity" !== a ? 0 : 1, c = "")), g = m && "=" === p.charAt(1), g ? (f = parseInt(p.charAt(0) + "1", 10), p = p.substr(2), f *= parseFloat(p), d = p.replace(y, "")) : (f = parseFloat(p), d = m ? p.substr((f + "").length) || "" : ""), "" === d && (d = a in r ? r[a] : c), p = f || 0 === f ? (g ? f + u : f) + d : e[a], c !== d && "" !== d && (f || 0 === f) && u && (u = Q(t, a, u, c), "%" === d ? (u /= Q(t, a, 100, "%") / 100, e.strictUnits !== !0 && (_ = u + "%")) : "em" === d ? u /= Q(t, a, 1, "em") : "px" !== d && (f = Q(t, a, f, d), d = "px"), g && (f || 0 === f) && (p = f + u + d)), g && (f += u), !u && 0 !== u || !f && 0 !== f ? void 0 !== v[a] && (p || "NaN" != p + "" && null != p) ? (i = new _e(v, a, f || u || 0, 0, i, -1, a, !1, 0, _, p), i.xs0 = "none" !== p || "display" !== a && -1 === a.indexOf("Style") ? p : _) : U("invalid " + a + " tween value: " + e[a]) : (i = new _e(v, a, u, f - u, i, 0, a, h !== !1 && ("px" === d || "zIndex" === a), 0, _, p), i.xs0 = d)) : i = pe(v, a, _, p, !0, null, i, 0, n)), n && i && !i.plugin && (i.plugin = n);
      return i;
    }, l.setRatio = function (t) {
      var e,
        i,
        r,
        s = this._firstPT,
        n = 1e-6;
      if (1 !== t || this._tween._time !== this._tween._duration && 0 !== this._tween._time) {
        if (t || this._tween._time !== this._tween._duration && 0 !== this._tween._time || this._tween._rawPrevTime === -1e-6) for (; s;) {
          if (e = s.c * t + s.s, s.r ? e = Math.round(e) : n > e && e > -n && (e = 0), s.type) {
            if (1 === s.type) {
              if (r = s.l, 2 === r) s.t[s.p] = s.xs0 + e + s.xs1 + s.xn1 + s.xs2;else if (3 === r) s.t[s.p] = s.xs0 + e + s.xs1 + s.xn1 + s.xs2 + s.xn2 + s.xs3;else if (4 === r) s.t[s.p] = s.xs0 + e + s.xs1 + s.xn1 + s.xs2 + s.xn2 + s.xs3 + s.xn3 + s.xs4;else if (5 === r) s.t[s.p] = s.xs0 + e + s.xs1 + s.xn1 + s.xs2 + s.xn2 + s.xs3 + s.xn3 + s.xs4 + s.xn4 + s.xs5;else {
                for (i = s.xs0 + e + s.xs1, r = 1; s.l > r; r++) i += s["xn" + r] + s["xs" + (r + 1)];
                s.t[s.p] = i;
              }
            } else -1 === s.type ? s.t[s.p] = s.xs0 : s.setRatio && s.setRatio(t);
          } else s.t[s.p] = e + s.xs0;
          s = s._next;
        } else for (; s;) 2 !== s.type ? s.t[s.p] = s.b : s.setRatio(t), s = s._next;
      } else for (; s;) 2 !== s.type ? s.t[s.p] = s.e : s.setRatio(t), s = s._next;
    }, l._enableTransforms = function (t) {
      this._transformType = t || 3 === this._transformType ? 3 : 2, this._transform = this._transform || Pe(this._target, s, !0);
    };
    var Me = function () {
      this.t[this.p] = this.e, this.data._linkCSSP(this, this._next, null, !0);
    };
    l._addLazySet = function (t, e, i) {
      var r = this._firstPT = new _e(t, e, 0, 0, this._firstPT, 2);
      r.e = i, r.setRatio = Me, r.data = this;
    }, l._linkCSSP = function (t, e, i, r) {
      return t && (e && (e._prev = t), t._next && (t._next._prev = t._prev), t._prev ? t._prev._next = t._next : this._firstPT === t && (this._firstPT = t._next, r = !0), i ? i._next = t : r || null !== this._firstPT || (this._firstPT = t), t._next = e, t._prev = i), t;
    }, l._kill = function (e) {
      var i,
        r,
        s,
        n = e;
      if (e.autoAlpha || e.alpha) {
        n = {};
        for (r in e) n[r] = e[r];
        n.opacity = 1, n.autoAlpha && (n.visibility = 1);
      }
      return e.className && (i = this._classNamePT) && (s = i.xfirst, s && s._prev ? this._linkCSSP(s._prev, i._next, s._prev._prev) : s === this._firstPT && (this._firstPT = i._next), i._next && this._linkCSSP(i._next, i._next._next, s._prev), this._classNamePT = null), t.prototype._kill.call(this, n);
    };
    var Le = function (t, e, i) {
      var r, s, n, a;
      if (t.slice) for (s = t.length; --s > -1;) Le(t[s], e, i);else for (r = t.childNodes, s = r.length; --s > -1;) n = r[s], a = n.type, n.style && (e.push($(n)), i && i.push(n)), 1 !== a && 9 !== a && 11 !== a || !n.childNodes.length || Le(n, e, i);
    };
    return a.cascadeTo = function (t, i, r) {
      var s,
        n,
        a,
        o = e.to(t, i, r),
        l = [o],
        h = [],
        u = [],
        f = [],
        _ = e._internals.reservedProps;
      for (t = o._targets || o.target, Le(t, h, f), o.render(i, !0), Le(t, u), o.render(0, !0), o._enabled(!0), s = f.length; --s > -1;) if (n = G(f[s], h[s], u[s]), n.firstMPT) {
        n = n.difs;
        for (a in r) _[a] && (n[a] = r[a]);
        l.push(e.to(f[s], i, n));
      }
      return l;
    }, t.activate([a]), a;
  }, !0);
}), window._gsDefine && window._gsQueue.pop()();

},{}],18:[function(require,module,exports){
"use strict";

/*!
 * VERSION: 1.7.3
 * DATE: 2014-01-14
 * UPDATES AND DOCS AT: http://www.greensock.com
 *
 * @license Copyright (c) 2008-2014, GreenSock. All rights reserved.
 * This work is subject to the terms at http://www.greensock.com/terms_of_use.html or for
 * Club GreenSock members, the software agreement that was issued with your membership.
 * 
 * @author: Jack Doyle, jack@greensock.com
 **/
(window._gsQueue || (window._gsQueue = [])).push(function () {
  "use strict";

  var t = document.documentElement,
    e = window,
    i = function (i, s) {
      var r = "x" === s ? "Width" : "Height",
        n = "scroll" + r,
        a = "client" + r,
        o = document.body;
      return i === e || i === t || i === o ? Math.max(t[n], o[n]) - (e["inner" + r] || Math.max(t[a], o[a])) : i[n] - i["offset" + r];
    },
    s = window._gsDefine.plugin({
      propName: "scrollTo",
      API: 2,
      version: "1.7.3",
      init: function (t, s, r) {
        return this._wdw = t === e, this._target = t, this._tween = r, "object" != typeof s && (s = {
          y: s
        }), this._autoKill = s.autoKill !== !1, this.x = this.xPrev = this.getX(), this.y = this.yPrev = this.getY(), null != s.x ? (this._addTween(this, "x", this.x, "max" === s.x ? i(t, "x") : s.x, "scrollTo_x", !0), this._overwriteProps.push("scrollTo_x")) : this.skipX = !0, null != s.y ? (this._addTween(this, "y", this.y, "max" === s.y ? i(t, "y") : s.y, "scrollTo_y", !0), this._overwriteProps.push("scrollTo_y")) : this.skipY = !0, !0;
      },
      set: function (t) {
        this._super.setRatio.call(this, t);
        var s = this._wdw || !this.skipX ? this.getX() : this.xPrev,
          r = this._wdw || !this.skipY ? this.getY() : this.yPrev,
          n = r - this.yPrev,
          a = s - this.xPrev;
        this._autoKill && (!this.skipX && (a > 7 || -7 > a) && i(this._target, "x") > s && (this.skipX = !0), !this.skipY && (n > 7 || -7 > n) && i(this._target, "y") > r && (this.skipY = !0), this.skipX && this.skipY && this._tween.kill()), this._wdw ? e.scrollTo(this.skipX ? s : this.x, this.skipY ? r : this.y) : (this.skipY || (this._target.scrollTop = this.y), this.skipX || (this._target.scrollLeft = this.x)), this.xPrev = this.x, this.yPrev = this.y;
      }
    }),
    r = s.prototype;
  s.max = i, r.getX = function () {
    return this._wdw ? null != e.pageXOffset ? e.pageXOffset : null != t.scrollLeft ? t.scrollLeft : document.body.scrollLeft : this._target.scrollLeft;
  }, r.getY = function () {
    return this._wdw ? null != e.pageYOffset ? e.pageYOffset : null != t.scrollTop ? t.scrollTop : document.body.scrollTop : this._target.scrollTop;
  }, r._kill = function (t) {
    return t.scrollTo_x && (this.skipX = !0), t.scrollTo_y && (this.skipY = !0), this._super._kill.call(this, t);
  };
}), window._gsDefine && window._gsQueue.pop()();

},{}]},{},[3])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jZG4tZHJpdmVyLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvbWl4cGFuZWwuanMiLCJzcmMvanMvZ2xvYmFsL3BhZ2VNYW5hZ2VyLmpzIiwic3JjL2pzL2dsb2JhbC9yZWNvbW1lbmRhdGlvbnMtd2lkZ2V0LmpzIiwic3JjL2pzL2dsb2JhbC9yb2NrZXRjZG4tc3Vic2NyaXB0aW9uLXBvbGxpbmcuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldGNkbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxJQUFJO0VBQ25FLE1BQU0sU0FBUyxHQUFHLFlBQVksQ0FBQyxhQUFhLENBQUMsZ0JBQWdCLENBQUM7RUFDOUQsTUFBTSxhQUFhLEdBQUcsWUFBWSxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQztFQUNuRSxNQUFN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QUFDLElBQUksQ0FBQyxJQUFJO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsVUFBVTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLE9BQU87TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLE9BQU87TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztVQUFDLFVBQVUsRUFBQyxDQUFDO1VBQUMsZ0JBQWdCLEVBQUMsQ0FBQztVQUFDLGVBQWUsRUFBQyxDQUFDO1VBQUMsaUJBQWlCLEVBQUMsSUFBSSxDQUFDO1FBQWlCLENBQUMsQ0FBQztNQUFDLEtBQUksUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxlQUFlLEtBQUcsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLGlCQUFpQixLQUFHLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsS0FBSSxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFFLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUUsUUFBUSxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1lBQUMsTUFBTSxFQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxLQUFHLFVBQVUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxPQUFPLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsVUFBVSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE1BQUssYUFBYSxHQUFDLENBQUMsR0FBQyx1RUFBdUU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsWUFBWSxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLElBQUk7TUFBQTtNQUFDLE9BQU0sUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsY0FBYyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO01BQUMsSUFBRyxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLFlBQVcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUk7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxPQUFPLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxZQUFZLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxtQkFBbUIsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsSUFBSSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLElBQUUsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLG1CQUFtQixFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxZQUFZLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFlBQVksR0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBRyxNQUFJLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFHLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxNQUFJLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLGtCQUFrQixJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsWUFBVTtNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUU7UUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsRUFBQyxPQUFNLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFBO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxVQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsQ0FBQyxZQUFZLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUc7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUU7UUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLEVBQUMsT0FBTSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQTtNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtNQUFDLEtBQUksSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxFQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEtBQUcsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLE1BQU0sRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLFlBQVksRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxFQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sSUFBSSxDQUFDLGNBQWM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxLQUFHLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLG1CQUFtQjtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVO0lBQUEsQ0FBQyxFQUFDLENBQUM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDOzs7OztBQ1h4clQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUMsVUFBUyxDQUFDLEVBQUM7RUFBQyxZQUFZOztFQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsSUFBRSxDQUFDO0VBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLENBQUM7TUFBQyxDQUFDLEdBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsUUFBUTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQztRQUFDLE9BQU8sVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLEVBQUU7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUUsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7VUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxNQUFNLElBQUUsTUFBTSxDQUFDLEdBQUcsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLEdBQUcsR0FBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxFQUFDLFlBQVU7WUFBQyxPQUFPLENBQUM7VUFBQSxDQUFDLENBQUMsR0FBQyxXQUFXLElBQUUsT0FBTyxNQUFNLElBQUUsTUFBTSxDQUFDLE9BQU8sS0FBRyxNQUFNLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBRSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLFlBQVU7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsMEJBQTBCLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFBLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsT0FBTyxDQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU07UUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsUUFBUSxFQUFDLE1BQU0sRUFBQyxPQUFPLEVBQUMsT0FBTyxFQUFDLGNBQWMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsV0FBVyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxXQUFXLENBQUM7SUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLFNBQVM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsd0JBQXdCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxJQUFFLElBQUk7SUFBQSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxFQUFFLEVBQUMsQ0FBQztRQUFDLEVBQUUsRUFBQztNQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUM7TUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0I7TUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxZQUFVO1FBQUMsT0FBTyxJQUFJLElBQUksQ0FBRCxDQUFDLENBQUUsT0FBTyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFJLEVBQUMsS0FBSyxFQUFDLFFBQVEsRUFBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyx1QkFBdUIsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLHNCQUFzQixDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyw2QkFBNkIsQ0FBQztJQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxHQUFDLEdBQUc7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLEtBQUssRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxhQUFhLENBQUMsTUFBTSxDQUFDO1FBQUEsQ0FBQztNQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7UUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO1FBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO1FBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsSUFBRSxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLFlBQVU7UUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLElBQUksQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBQyxNQUFNLENBQUMsZUFBZSxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxNQUFNO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsZUFBZSxLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBRCxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVO01BQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztJQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFlBQVU7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7TUFBQyxPQUFNLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxRQUFRLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxJQUFHLENBQUMsS0FBRyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsSUFBSSxDQUFDLE1BQU07SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxLQUFHLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sSUFBSSxDQUFDLFVBQVU7TUFBQyxJQUFHLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsRUFBQztVQUFDLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLGNBQWM7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLE9BQUssQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztRQUFBO1FBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsTUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLGFBQWEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsVUFBVTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsVUFBVTtNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLEtBQUcsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsU0FBUztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxpQkFBaUIsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLHFCQUFxQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxrQkFBa0IsR0FBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsTUFBTSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxhQUFhLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNO01BQUMsS0FBSSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxZQUFVO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVU7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxJQUFJLElBQUUsQ0FBQyxFQUFDLE1BQUssNkJBQTZCO1FBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLElBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUksSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFdBQVcsR0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUksQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztRQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxlQUFlLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsS0FBRyxDQUFDLENBQUMsTUFBSSxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxPQUFPLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsdUJBQXVCLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxjQUFjLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLGdCQUFnQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQyxDQUFDO1FBQUMsWUFBWSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsY0FBYyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsYUFBYSxFQUFDLENBQUM7UUFBQyxZQUFZLEVBQUMsQ0FBQztRQUFDLGlCQUFpQixFQUFDLENBQUM7UUFBQyx1QkFBdUIsRUFBQyxDQUFDO1FBQUMsc0JBQXNCLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsY0FBYyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLEdBQUcsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxHQUFHLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUM7VUFBQyxPQUFLLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUM7UUFBQTtNQUFDO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVU7UUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUM7VUFBQyxNQUFNLEVBQUMsQ0FBQztVQUFDLE1BQU0sRUFBQztRQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBSyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU07TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFHLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUM7VUFBTSxPQUFPLENBQUM7UUFBQTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUU7VUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsT0FBTSxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQjtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7TUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLEVBQUM7UUFBQyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQztNQUFNLENBQUMsTUFBSyxJQUFHLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxLQUFHLENBQUMsRUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxhQUFhLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssRUFBQztRQUFNLENBQUMsTUFBSyxJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxZQUFZLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxZQUFZLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLElBQUUsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsUUFBUSxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLFdBQVcsRUFBQyxJQUFJLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxjQUFjLENBQUMsaUJBQWlCLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsVUFBVSxJQUFFLE9BQU8sSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU0sQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUksSUFBSSxDQUFDLElBQUksRUFBQztRQUFDLElBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUQsQ0FBQyxFQUFFLFlBQVksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUM7WUFBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFFBQVE7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxVQUFVO1lBQUMsQ0FBQyxFQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLEVBQUUsRUFBQyxDQUFDLENBQUM7WUFBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDO1VBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO1VBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxlQUFlLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxTQUFTLE1BQUksSUFBSSxDQUFDLHVCQUF1QixHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxNQUFLLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQztVQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQztRQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVk7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsWUFBWSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxJQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxtQkFBbUIsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxJQUFJLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxtQkFBbUIsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLE1BQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsTUFBSyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDO1VBQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLEdBQUcsRUFBQztVQUFPLElBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQztVQUFDLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsS0FBSSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxrQkFBa0IsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsS0FBSyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUk7UUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxpQkFBaUIsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLEtBQUs7WUFBQztVQUFLO1FBQUMsQ0FBQyxNQUFJO1VBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sRUFBQyxPQUFNLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsSUFBRSxDQUFDLENBQUMsR0FBQyxLQUFLO1FBQUE7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDO1VBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7TUFBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyx1QkFBdUIsSUFBRSxDQUFDLENBQUMsY0FBYyxDQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsdUJBQXVCLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtRQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLHVCQUF1QixJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLEdBQUMsV0FBVyxHQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLGdCQUFnQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLGlCQUFpQixFQUFDLENBQUM7UUFBQyx1QkFBdUIsRUFBQyxDQUFDO1FBQUMsc0JBQXNCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDLENBQUM7UUFBQyxTQUFTLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQztNQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTSxFQUFFO01BQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsTUFBSyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLGtCQUFrQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLGVBQWUsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQztRQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsS0FBSyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxlQUFlO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxHQUFDLEVBQUUsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFLLENBQUMsR0FBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFDLElBQUcsaUJBQWlCLEtBQUcsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBSyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEVBQUUsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUQsQ0FBQyxDQUFFLFNBQVMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsR0FBRyxDQUFDLEVBQUMsTUFBSyw0QkFBNEI7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjO1FBQUMsQ0FBQyxHQUFDO1VBQUMsSUFBSSxFQUFDLGNBQWM7VUFBQyxHQUFHLEVBQUMsVUFBVTtVQUFDLElBQUksRUFBQyxPQUFPO1VBQUMsS0FBSyxFQUFDLGFBQWE7VUFBQyxPQUFPLEVBQUM7UUFBaUIsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsRUFBQyxZQUFVO1VBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLEVBQUU7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUc7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDO01BQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMscURBQXFELEdBQUMsQ0FBQyxDQUFDO0lBQUE7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0VBQUE7QUFBQyxDQUFDLEVBQUUsTUFBTSxDQUFDOzs7OztBQ1gxNHZCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxhQUFhLEVBQUMsQ0FBQyxhQUFhLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsSUFBRSxNQUFNO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsU0FBUztNQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsWUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsWUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxDQUFELENBQUM7VUFBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztVQUFDLFNBQVMsRUFBQyxJQUFJLENBQUMsQ0FBRDtRQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDO1lBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFHO1VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU0sQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxFQUFFO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0IsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGtCQUFrQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsTUFBTSxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsWUFBWSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDO01BQUMsS0FBSSxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtNQUFBLENBQUMsTUFBSyxPQUFLLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU87SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLElBQUUsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxFQUFFO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxFQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxFQUFFLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sQ0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLG1CQUFtQixFQUFDO01BQUMsSUFBSSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxRQUFRLEVBQUMsT0FBTyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxXQUFXLEVBQUMsT0FBTyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxhQUFhLEVBQUMsT0FBTyxDQUFDLEVBQUMsQ0FBQztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7Ozs7O0FDWHRsSjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsbUJBQW1CLEVBQUMsQ0FBQyxxQkFBcUIsRUFBQyxXQUFXLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVU7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLFFBQVE7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLEtBQUssQ0FBQztJQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQywyQkFBMkIsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxhQUFhLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDO01BQUMs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Q0FBQyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxNQUFNLEtBQUcsRUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE1BQU0sQ0FBQyxPQUFPLElBQUUsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFHLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFHLEVBQUMsS0FBSyxFQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFO1FBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsZ0JBQWdCLEdBQUMsWUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxTQUFTLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUcsTUFBTSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxLQUFJO1VBQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxHQUFDLDhCQUE4QixHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLEdBQUMsaUJBQWlCLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsaUJBQWlCLEdBQUMsZ0JBQWdCLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUk7WUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxHQUFHO1lBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQTtVQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGFBQWEsR0FBQyxjQUFjLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsVUFBVSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsTUFBTSxHQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLE9BQUssRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBSyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLENBQUMsQ0FBQyxLQUFLLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxPQUFPLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsU0FBUyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLEtBQUssQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLEdBQUMsRUFBRSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFdBQVcsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU07VUFBQyxJQUFJLEVBQUMsQ0FBQztVQUFDLFFBQVEsRUFBQztRQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEtBQUssRUFBQyxRQUFRO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLFlBQVksRUFBQyxhQUFhLEVBQUMsV0FBVyxFQUFDLGNBQWMsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsT0FBTyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxLQUFLLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxRQUFRLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLElBQUUsS0FBSyxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFNLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJO1FBQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsR0FBRyxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxLQUFLO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxxREFBcUQ7SUFBQyxLQUFJLENBQUMsSUFBSSxFQUFFLEVBQUMsRUFBRSxJQUFFLEdBQUcsR0FBQyxDQUFDLEdBQUMsS0FBSztJQUFDLEVBQUUsR0FBQyxNQUFNLENBQUMsRUFBRSxHQUFDLEdBQUcsRUFBQyxJQUFJLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU8sVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsRUFBRTtRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztVQUFBO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxFQUFDLE9BQUssQ0FBQyxHQUFDLEVBQUUsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDO1FBQUEsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztVQUFBO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxFQUFDLE9BQUssQ0FBQyxHQUFDLEVBQUUsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFBLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsZUFBZSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBRyxDQUFDLENBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxVQUFVLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxFQUFDO1lBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksRUFBQztjQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7WUFBQTtVQUFDLENBQUMsTUFBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQTtNQUFDLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUM7TUFBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVU7VUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBO1FBQUMsT0FBTTtVQUFDLEtBQUssRUFBQyxDQUFDO1VBQUMsR0FBRyxFQUFDLENBQUM7VUFBQyxRQUFRLEVBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQztRQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxZQUFZLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDO01BQUEsQ0FBQyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEVBQUU7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsY0FBYyxHQUFDLGFBQWEsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsTUFBSyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxFQUFFLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFFLEdBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsRUFBRSxDQUFDLEdBQUMsRUFBRTtJQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDO01BQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLDJCQUEyQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDO1VBQUMsTUFBTSxFQUFDO1FBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVE7VUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDO1lBQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7Y0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsSUFBRSxNQUFNLEVBQUUsR0FBRyxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO2NBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxzQkFBc0IsQ0FBQyxFQUFDLENBQUMsQ0FBQztZQUFBO1VBQUMsQ0FBQyxDQUFDO1FBQUE7TUFBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU87TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztNQUFBO01BQUMsT0FBTyxFQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsRUFBRSxDQUFDLENBQUMsRUFBQztRQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztRQUFBLENBQUM7UUFBQyxRQUFRLEVBQUM7TUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsaUZBQWlGLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxXQUFXO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxpQkFBaUIsQ0FBQztNQUFDLEVBQUUsR0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLGFBQWEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFlBQVU7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLFlBQVk7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsSUFBSSxFQUFFLENBQUQsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFELENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxHQUFDLENBQUM7UUFBQyxLQUFJLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLHlCQUF5QixDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztVQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUM7WUFBQyxJQUFJLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7Y0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQztZQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQTtRQUFDLENBQUMsTUFBSyxJQUFHLEVBQUUsRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxDQUFDLElBQUUsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEVBQUUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxFQUFFLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLEdBQUcsRUFBQyxDQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQywyQkFBMkIsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZO1FBQUMsSUFBRyxDQUFDLEVBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxFQUFFO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFdBQVc7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZO1lBQUMsQ0FBQyxHQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsUUFBUTtZQUFDLENBQUMsR0FBQywrQ0FBK0MsR0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLElBQUUsK0JBQStCLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLG9DQUFvQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsV0FBVyxJQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxJQUFJLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7WUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsRUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLEVBQUUsSUFBRSxDQUFDLEtBQUcsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUk7VUFBQTtRQUFDO01BQUMsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVc7UUFBQyxJQUFHLEVBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUM7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSTtVQUFDLElBQUcsRUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsRUFBRSxDQUFDLEVBQUMsS0FBSyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsS0FBSyxDQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsRUFBRSxDQUFDLG1QQUFtUCxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsU0FBUyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEdBQUM7WUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQztZQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsV0FBVyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsb0JBQW9CLEVBQUMsQ0FBQyxDQUFDLFdBQVc7VUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsRUFBQyxJQUFJLElBQUUsQ0FBQyxFQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsVUFBVSxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLGVBQWUsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxRQUFRLEdBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsRUFBRSxDQUFDLFdBQVcsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxnQkFBZ0IsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxFQUFFLENBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLGdCQUFnQixJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLE1BQUksQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUM7UUFBQTtRQUFDLEtBQUksRUFBRSxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxNQUFJLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxpQkFBaUIsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssQ0FBQyxHQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsY0FBYyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxXQUFXLEVBQUM7TUFBQyxZQUFZLEVBQUMsc0JBQXNCO01BQUMsTUFBTSxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxFQUFDO0lBQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGNBQWMsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMscUJBQXFCLEVBQUMsc0JBQXNCLEVBQUMseUJBQXlCLEVBQUMsd0JBQXdCLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxLQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsV0FBVyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUMsQ0FBQztNQUFDLFNBQVMsRUFBQyxFQUFFLENBQUMsaUJBQWlCLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG9CQUFvQixFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMscUJBQXFCO1VBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLG1CQUFtQixHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLG1CQUFtQixLQUFHLEtBQUssQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsaUJBQWlCLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsR0FBRyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUM7UUFBQTtRQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxTQUFTLEVBQUM7SUFBRSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsZ0JBQWdCLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLFNBQVMsRUFBQztJQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxhQUFhLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG1CQUFtQixFQUFDO01BQUMsWUFBWSxFQUFDLFNBQVM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsRUFBQztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG9CQUFvQixFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsUUFBUSxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQywrQ0FBK0M7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsU0FBUyxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxtREFBbUQ7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsTUFBTSxFQUFDO01BQUMsWUFBWSxFQUFDLHVCQUF1QjtNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFlBQVksRUFBQztNQUFDLFlBQVksRUFBQyxrQkFBa0I7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsdUJBQXVCLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUM7TUFBQyxZQUFZLEVBQUMsZ0JBQWdCO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxnQkFBZ0IsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGdCQUFnQixFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsU0FBUyxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsT0FBTyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGFBQWEsRUFBQztNQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsbUVBQW1FO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLDJCQUEyQixFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsVUFBVSxJQUFHLENBQUMsR0FBQyxVQUFVLEdBQUMsWUFBWTtRQUFDLE9BQU8sSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxRQUFRLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsR0FBRyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsZ0JBQWdCLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLGlCQUFpQixHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxFQUFFLENBQUMseUJBQXlCLEVBQUM7TUFBQyxZQUFZLEVBQUMsR0FBRztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLFdBQVcsS0FBRyxDQUFDO1FBQUMsT0FBTSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsZ0JBQWdCLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxTQUFTLEdBQUMsUUFBUSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsUUFBUSxHQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxjQUFjLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksS0FBRyxJQUFJLENBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUM7UUFBQSxDQUFDLE1BQUssSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLEtBQUcsSUFBSSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxFQUFFLENBQUMsV0FBVyxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLElBQUUsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU87UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEVBQUMsRUFBRSxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxhQUFhLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSztRQUFDLElBQUcsS0FBSyxLQUFHLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxpQkFBaUIsS0FBRyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsS0FBRyxFQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUM7TUFBQTtJQUFDLENBQUM7SUFBQyxLQUFJLEVBQUUsQ0FBQyxZQUFZLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsMENBQTBDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsRUFBRSxHQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxPQUFNLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsZUFBZTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxjQUFjLEVBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLDBCQUEwQixFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsd0JBQXdCLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFdBQVcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE9BQU8sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLGFBQWEsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFFLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLE1BQUksT0FBTyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxJQUFFLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxXQUFXLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsS0FBSyxJQUFFLENBQUMsR0FBQyxFQUFFLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsSUFBSTtNQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLO1FBQUMsSUFBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsWUFBWSxLQUFHLENBQUMsSUFBSSxFQUFDLE9BQUssQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSTtZQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJO2NBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUk7Z0JBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO2dCQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7Y0FBQTtZQUFDLE9BQUksQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7VUFBQyxPQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBLENBQUMsTUFBSyxPQUFLLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBSyxPQUFLLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLEVBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQUEsRUFBVTtNQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUssRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxNQUFNLElBQUUsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLGFBQWE7TUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7UUFDbHgrQixLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDOzs7OztBQ1poSTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLElBQUksQ0FBQyxHQUFDLFFBQVEsQ0FBQyxlQUFlO0lBQUMsQ0FBQyxHQUFDLE1BQU07SUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxHQUFDLE9BQU8sR0FBQyxRQUFRO1FBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFJO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQztNQUFDLFFBQVEsRUFBQyxVQUFVO01BQUMsR0FBRyxFQUFDLENBQUM7TUFBQyxPQUFPLEVBQUMsT0FBTztNQUFDLElBQUksRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDO1VBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEdBQUcsRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7RUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7SUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVTtFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7SUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztFQUFBLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIi5jdXN0b20tc2VsZWN0XCIpLmZvckVhY2goY3VzdG9tU2VsZWN0ID0+IHtcblx0Y29uc3Qgc2VsZWN0QnRuID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3IoXCIuc2VsZWN0LWJ1dHRvblwiKTtcblx0Y29uc3Qgc2VsZWN0ZWRWYWx1ZSA9IGN1c3RvbVNlbGVjdC5xdWVyeVNlbGVjdG9yKFwiLnNlbGVjdGVkLXZhbHVlXCIpO1xuXHRjb25zdCBoYW5kbGVyID0gZnVuY3Rpb24oZWxtKSB7XG5cdFx0Y29uc3QgY3VzdG9tQ2hhbmdlRXZlbnQgPSBuZXcgQ3VzdG9tRXZlbnQoJ2N1c3RvbS1zZWxlY3QtY2hhbmdlJywge1xuXHRcdFx0ZGV0YWlsOiB7XG5cdFx0XHRcdHNlbGVjdGVkT3B0aW9uOiBlbG1cblx0XHRcdH1cblx0XHR9KTtcblx0XHRzZWxlY3RlZFZhbHVlLnRleHRDb250ZW50ID0gZWxtLnRleHRDb250ZW50O1xuXHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QucmVtb3ZlKFwiYWN0aXZlXCIpO1xuXHRcdGN1c3RvbVNlbGVjdC5kaXNwYXRjaEV2ZW50KGN1c3RvbUNoYW5nZUV2ZW50KTtcblxuXHR9XG5cdHNlbGVjdEJ0bi5hZGRFdmVudExpc3RlbmVyKFwiY2xpY2tcIiwgKCkgPT4ge1xuXHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QudG9nZ2xlKFwiYWN0aXZlXCIpO1xuXHRcdHNlbGVjdEJ0bi5zZXRBdHRyaWJ1dGUoXG5cdFx0XHRcImFyaWEtZXhwYW5kZWRcIixcblx0XHRcdHNlbGVjdEJ0bi5nZXRBdHRyaWJ1dGUoXCJhcmlhLWV4cGFuZGVkXCIpID09PSBcInRydWVcIiA/IFwiZmFsc2VcIiA6IFwidHJ1ZVwiXG5cdFx0KTtcblx0fSk7XG5cblx0Y3VzdG9tU2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGlmIChlLnRhcmdldC5tYXRjaGVzKCdsYWJlbCcpKSB7XG5cblx0XHRcdGNvbnN0IGFsbEl0ZW1zID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3JBbGwoJ2xpJyk7XG5cdFx0XHRhbGxJdGVtcy5mb3JFYWNoKGl0ZW0gPT4gaXRlbS5jbGFzc0xpc3QucmVtb3ZlKCdhY3RpdmUnKSk7XG5cdFx0XHRjb25zdCBjbGlja2VkUGxhbiA9IGUudGFyZ2V0LmNsb3Nlc3QoJ2xpJyk7XG5cblx0XHRcdGlmIChjbGlja2VkUGxhbikge1xuXHRcdFx0XHRjbGlja2VkUGxhbi5jbGFzc0xpc3QuYWRkKCdhY3RpdmUnKTtcblx0XHRcdFx0aGFuZGxlcihjbGlja2VkUGxhbik7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImNsaWNrXCIsIChlKSA9PiB7XG5cdFx0aWYgKCFjdXN0b21TZWxlY3QuY29udGFpbnMoZS50YXJnZXQpKSB7XG5cdFx0XHRjdXN0b21TZWxlY3QuY2xhc3NMaXN0LnJlbW92ZShcImFjdGl2ZVwiKTtcblx0XHRcdHNlbGVjdEJ0bi5zZXRBdHRyaWJ1dGUoXCJhcmlhLWV4cGFuZGVkXCIsIFwiZmFsc2VcIik7XG5cdFx0fVxuXHR9KTtcbn0pOyIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcbiAgICAvKipcbiAgICAgKiBSZWZyZXNoIExpY2Vuc2UgZGF0YVxuICAgICAqL1xuICAgIHZhciBfaXNSZWZyZXNoaW5nID0gZmFsc2U7XG4gICAgJCgnI3dwci1hY3Rpb24tcmVmcmVzaF9hY2NvdW50Jykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBpZighX2lzUmVmcmVzaGluZyl7XG4gICAgICAgICAgICB2YXIgYnV0dG9uID0gJCh0aGlzKTtcbiAgICAgICAgICAgIHZhciBhY2NvdW50ID0gJCgnI3dwci1hY2NvdW50LWRhdGEnKTtcbiAgICAgICAgICAgIHZhciBleHBpcmUgPSAkKCcjd3ByLWV4cGlyYXRpb24tZGF0YScpO1xuXG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICBfaXNSZWZyZXNoaW5nID0gdHJ1ZTtcbiAgICAgICAgICAgIGJ1dHRvbi50cmlnZ2VyKCAnYmx1cicgKTtcblxuXHRcdFx0Ly8gU3RhcnQgcG9sbGluZyBpZiBub3QgYWxyZWFkeSBydW5uaW5nLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICBleHBpcmUucmVtb3ZlQ2xhc3MoJ3dwci1pc1ZhbGlkIHdwci1pc0ludmFsaWQnKTtcblxuICAgICAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfcmVmcmVzaF9jdXN0b21lcl9kYXRhJyxcbiAgICAgICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2UsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBmdW5jdGlvbihyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICBidXR0b24ucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uLmFkZENsYXNzKCd3cHItaXNIaWRkZW4nKTtcblxuICAgICAgICAgICAgICAgICAgICBpZiAoIHRydWUgPT09IHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBhY2NvdW50Lmh0bWwocmVzcG9uc2UuZGF0YS5saWNlbnNlX3R5cGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgZXhwaXJlLmFkZENsYXNzKHJlc3BvbnNlLmRhdGEubGljZW5zZV9jbGFzcykuaHRtbChyZXNwb25zZS5kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbik7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWljb24tcmVmcmVzaCB3cHItaXNIaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pY29uLWNoZWNrJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LCAyNTApO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIGVsc2V7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWljb24tcmVmcmVzaCB3cHItaXNIaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pY29uLWNsb3NlJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LCAyNTApO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKHtvbkNvbXBsZXRlOmZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgX2lzUmVmcmVzaGluZyA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jys9d3ByLWlzSGlkZGVuJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pY29uLWNoZWNrJ319LCAwLjI1KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pY29uLWNsb3NlJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOicrPXdwci1pY29uLXJlZnJlc2gnfX0sIDAuMjUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWlzSGlkZGVuJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgO1xuICAgICAgICAgICAgICAgICAgICB9LCAyMDAwKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICApO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9KTtcblxuICAgIC8qKlxuICAgICAqIFNhdmUgVG9nZ2xlIG9wdGlvbiB2YWx1ZXMgb24gY2hhbmdlXG4gICAgICovXG4gICAgJCgnLndwci1yYWRpbyBpbnB1dFt0eXBlPWNoZWNrYm94XScpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgdmFyIG5hbWUgID0gJCh0aGlzKS5hdHRyKCdpZCcpO1xuICAgICAgICB2YXIgdmFsdWUgPSAkKHRoaXMpLnByb3AoJ2NoZWNrZWQnKSA/IDEgOiAwO1xuXG5cdFx0dmFyIGV4Y2x1ZGVkID0gW1xuXHRcdFx0J2Nsb3VkZmxhcmVfYXV0b19zZXR0aW5ncycsXG5cdFx0XHQnY2xvdWRmbGFyZV9kZXZtb2RlJyxcblx0XHRcdCdhbmFseXRpY3NfZW5hYmxlZCcsXG5cdFx0XHQnd3ByLXJvY2tldGNkbi1mcmVlLXRvZ2dsZScsXG5cdFx0XHQnd3ByLWJ5b2Nkbi10b2dnbGUnLFxuXHRcdFx0J3dwci1yb2NrZXRjZG4tcGFpZC10b2dnbGUnXG5cdFx0XTtcblx0XHRpZiAoIGV4Y2x1ZGVkLmluZGV4T2YoIG5hbWUgKSA+PSAwICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF90b2dnbGVfb3B0aW9uJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcbiAgICAgICAgICAgICAgICBvcHRpb246IHtcbiAgICAgICAgICAgICAgICAgICAgbmFtZTogbmFtZSxcbiAgICAgICAgICAgICAgICAgICAgdmFsdWU6IHZhbHVlXG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGZ1bmN0aW9uKHJlc3BvbnNlKSB7fVxuICAgICAgICApO1xuXHR9KTtcblxuXHQvKipcbiAgICAgKiBTYXZlIGVuYWJsZSBDUENTUyBmb3IgbW9iaWxlcyBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBDUENTUyBidG4gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLWhpZGUtb24tY2xpY2snKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1zaG93LW9uLWNsaWNrJykuc2hvdygpO1xuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogU2F2ZSBlbmFibGUgR29vZ2xlIEZvbnRzIE9wdGltaXphdGlvbiBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBDUENTUyBidG4gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLWhpZGUtb24tY2xpY2snKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1zaG93LW9uLWNsaWNrJykuc2hvdygpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICAgICAgICAgICQoJyNtaW5pZnlfZ29vZ2xlX2ZvbnRzJykudmFsKDEpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG5cbiAgICAkKCAnI3JvY2tldC1kaXNtaXNzLXByb21vdGlvbicgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9kaXNtaXNzX3Byb21vJyxcbiAgICAgICAgICAgICAgICBub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQkKCcjcm9ja2V0LXByb21vLWJhbm5lcicpLmhpZGUoICdzbG93JyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSApO1xuXG4gICAgJCggJyNyb2NrZXQtZGlzbWlzcy1yZW5ld2FsJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2Rpc21pc3NfcmVuZXdhbCcsXG4gICAgICAgICAgICAgICAgbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0JCgnI3JvY2tldC1yZW5ld2FsLWJhbm5lcicpLmhpZGUoICdzbG93JyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSApO1xuXHQkKCAnI3dwci11cGRhdGUtZXhjbHVzaW9uLWxpc3QnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKCcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbXNnJykuaHRtbCgnJyk7XG5cdFx0JC5hamF4KHtcblx0XHRcdHVybDogcm9ja2V0X2FqYXhfZGF0YS5yZXN0X3VybCxcblx0XHRcdGJlZm9yZVNlbmQ6IGZ1bmN0aW9uICggeGhyICkge1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ1gtV1AtTm9uY2UnLCByb2NrZXRfYWpheF9kYXRhLnJlc3Rfbm9uY2UgKTtcblx0XHRcdFx0eGhyLnNldFJlcXVlc3RIZWFkZXIoICdBY2NlcHQnLCAnYXBwbGljYXRpb24vanNvbiwgKi8qO3E9MC4xJyApO1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ0NvbnRlbnQtVHlwZScsICdhcHBsaWNhdGlvbi9qc29uJyApO1xuXHRcdFx0fSxcblx0XHRcdG1ldGhvZDogXCJQVVRcIixcblx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uKHJlc3BvbnNlcykge1xuXHRcdFx0XHRsZXQgZXhjbHVzaW9uX21zZ19jb250YWluZXIgPSAkKCcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbXNnJyk7XG5cdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmh0bWwoJycpO1xuXHRcdFx0XHRpZiAoIHVuZGVmaW5lZCAhPT0gcmVzcG9uc2VzWydzdWNjZXNzJ10gKSB7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCAnPGRpdiBjbGFzcz1cIm5vdGljZSBub3RpY2UtZXJyb3JcIj4nICsgcmVzcG9uc2VzWydtZXNzYWdlJ10gKyAnPC9kaXY+JyApO1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXHRcdFx0XHRPYmplY3Qua2V5cyggcmVzcG9uc2VzICkuZm9yRWFjaCgoIHJlc3BvbnNlX2tleSApID0+IHtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8c3Ryb25nPicgKyByZXNwb25zZV9rZXkgKyAnOiA8L3N0cm9uZz4nICk7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCByZXNwb25zZXNbcmVzcG9uc2Vfa2V5XVsnbWVzc2FnZSddICk7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCAnPGJyPicgKTtcblx0XHRcdFx0fSk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH0gKTtcblxuICAgIC8qKlxuICAgICAqIEVuYWJsZSBtb2JpbGUgY2FjaGUgb3B0aW9uLlxuICAgICAqL1xuICAgICQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfbW9iaWxlX2NhY2hlJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBjYWNoZSBlbmFibGUgYnV0dG9uIG9uIHN1Y2Nlc3MuXG5cdFx0XHRcdFx0JCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJyN3cHJfbW9iaWxlX2NhY2hlX2RlZmF1bHQnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnI3dwcl9tb2JpbGVfY2FjaGVfcmVzcG9uc2UnKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gU2V0IHZhbHVlcyBvZiBtb2JpbGUgY2FjaGUgYW5kIHNlcGFyYXRlIGNhY2hlIGZpbGVzIGZvciBtb2JpbGVzIG9wdGlvbiB0byAxLlxuICAgICAgICAgICAgICAgICAgICAkKCcjY2FjaGVfbW9iaWxlJykudmFsKDEpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjZG9fY2FjaGluZ19tb2JpbGVfZmlsZXMnKS52YWwoMSk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9KTtcbn0pO1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgZnVuY3Rpb24oKSB7XG5cdGNvbnN0IGFuYWx5dGljc0NoZWNrYm94ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2FuYWx5dGljc19lbmFibGVkJyk7XG5cblx0aWYgKGFuYWx5dGljc0NoZWNrYm94KSB7XG5cdFx0YW5hbHl0aWNzQ2hlY2tib3guYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBpc0NoZWNrZWQgPSB0aGlzLmNoZWNrZWQ7XG5cblx0XHRcdC8vIFVwZGF0ZSB0aGUgZ2xvYmFsIG1peHBhbmVsIGRhdGEgb3B0aW4gc3RhdGUgaW1tZWRpYXRlbHlcblx0XHRcdGlmICh0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgIT09ICd1bmRlZmluZWQnKSB7XG5cdFx0XHRcdHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgPSBpc0NoZWNrZWQgPyAnMScgOiAnMCc7XG5cdFx0XHR9XG5cblx0XHRcdGZldGNoKGFqYXh1cmwsIHtcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGhlYWRlcnM6IHtcblx0XHRcdFx0XHQnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCcsXG5cdFx0XHRcdH0sXG5cdFx0XHRcdGJvZHk6IG5ldyBVUkxTZWFyY2hQYXJhbXMoe1xuXHRcdFx0XHRcdGFjdGlvbjogJ3JvY2tldF90b2dnbGVfb3B0aW4nLFxuXHRcdFx0XHRcdHZhbHVlOiBpc0NoZWNrZWQgPyAxIDogMCxcblx0XHRcdFx0XHRfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcblx0XHRcdFx0fSlcblx0XHRcdH0pO1xuXHRcdH0pO1xuXHR9XG59KTtcblxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGZ1bmN0aW9uKCkge1xuXHQvKipcblx0ICogUGVyZm9ybWFuY2UgTW9uaXRvcmluZyB3aXRoIFByb2dyZXNzaXZlIFBvbGxpbmcuXG5cdCAqL1xuXG5cdFx0Ly8gPT09PSBDb25maWd1cmF0aW9uID09PT1cblx0Y29uc3QgUE9MTF9CQVNFX0lOVEVSVkFMID0gMjAwMDsgICAvLyBTdGFydCBwb2xsaW5nIGF0IDIgc2Vjb25kc1xuXHRjb25zdCBQT0xMX01BWF9JTlRFUlZBTCA9IDUwMDA7ICAgLy8gTWF4IHBvbGxpbmcgaW50ZXJ2YWwgKDUgc2Vjb25kcylcblxuXHQvLyA9PT09IFN0YXRlID09PT1cblx0bGV0IHJvY2tldEluc2lnaHRzSWRzID0gQXJyYXkuaXNBcnJheSh3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ucm9ja2V0X2luc2lnaHRzX2lkcykgPyB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YS5yb2NrZXRfaW5zaWdodHNfaWRzLnNsaWNlKCkgOiBbXTtcblx0bGV0IHBvbGxJbnRlcnZhbCA9IFBPTExfQkFTRV9JTlRFUlZBTDtcblx0bGV0IHBvbGxUaW1lciA9IG51bGw7XG5cdGxldCBoYXNDcmVkaXQgPSB0cnVlOyAvLyBUcmFjayBjcmVkaXQgc3RhdHVzXG4gICAgbGV0IGdsb2JhbFNjb3JlRGF0YSA9IHtcbiAgICAgICAgZGF0YToge1xuICAgICAgICAgICAgc3RhdHVzOiAnJyxcbiAgICAgICAgICAgIHNjb3JlOiAwLFxuICAgICAgICAgICAgcGFnZXNfbnVtOiAwXG4gICAgICAgIH0sXG4gICAgICAgIGh0bWw6ICcnLFxuICAgICAgICByb3dfaHRtbDogJycsXG5cdFx0ZGlzYWJsZWRfYnRuX2h0bWw6IHtcblx0XHRcdGdsb2JhbF9zY29yZV93aWRnZXQ6ICcnLFxuXHRcdFx0cm9ja2V0X2luc2lnaHRzOiAnJ1xuXHRcdH1cbiAgICB9O1xuXG4gICAgLy8gSW5pdGlhbGl6ZSBnbG9iYWxTY29yZURhdGEgZnJvbSBsb2NhbGl6ZWQgc2NyaXB0IGRhdGEgaWYgYXZhaWxhYmxlXG4gICAgaWYgKHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5nbG9iYWxfc2NvcmVfZGF0YSkge1xuICAgICAgICBnbG9iYWxTY29yZURhdGEgPSB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YS5nbG9iYWxfc2NvcmVfZGF0YTtcbiAgICB9XG5cblx0Ly8gPT09PSBET00gU2VsZWN0b3JzID09PT1cblx0Y29uc3QgJHBhZ2VVcmxJbnB1dCA9ICQoJyN3cHItc3BlZWQtcmFkYXItdXJsLWlucHV0Jyk7XG5cdGNvbnN0ICR0YWJsZUJvZHkgPSAkKCcud3ByLXJpLXVybHMtdGFibGUgdGJvZHknKTtcblx0Y29uc3QgJHRhYmxlID0gJCgnLndwci1yaS11cmxzLXRhYmxlJyk7XG5cblx0Ly8gPT09PSBVdGlsaXR5IEZ1bmN0aW9ucyA9PT09XG5cdGZ1bmN0aW9uIGlzVmFsaWRVcmwoaW5wdXQpIHtcblx0XHR0cnkge1xuXHRcdFx0Y29uc3QgdXJsID0gbmV3IFVSTChpbnB1dCk7XG5cdFx0XHRyZXR1cm4gdXJsLmhvc3RuYW1lLmluY2x1ZGVzKCcuJykgJiYgdXJsLmhvc3RuYW1lLnNwbGl0KCcuJykucG9wKCkubGVuZ3RoID4gMDtcblx0XHR9IGNhdGNoIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiBhZGRJZHMobmV3SWQpIHtcblx0XHRpZiAoIXJvY2tldEluc2lnaHRzSWRzLmluY2x1ZGVzKG5ld0lkKSkge1xuXHRcdFx0cm9ja2V0SW5zaWdodHNJZHMucHVzaChuZXdJZCk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gaGFzSWQoaWQpIHtcblx0XHRyZXR1cm4gcm9ja2V0SW5zaWdodHNJZHMuaW5jbHVkZXMoaWQpO1xuXHR9XG5cblx0ZnVuY3Rpb24gcmVtb3ZlSWQoaWQpIHtcblx0XHQvLyBFbnN1cmUgdGhhdCB0aGUgaWQgdG8gYmUgcmVtb3ZlZCBpcyBhbiBpbnRlZ2VyIGZvciBhY2N1cmF0ZSBjb21wYXJpc29uLlxuXHRcdGNvbnN0IGlkVG9SZW1vdmUgPSBwYXJzZUludChpZCwgMTApO1xuXHRcdHJvY2tldEluc2lnaHRzSWRzID0gcm9ja2V0SW5zaWdodHNJZHMuZmlsdGVyKHggPT4gcGFyc2VJbnQoeCwgMTApICE9PSBpZFRvUmVtb3ZlKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZVF1b3RhQmFubmVyKGNhbkFkZFBhZ2VzKSB7XG5cdFx0Y29uc3QgJHN1bW1hcnlJbmZvICAgID0gJCgnLndwci1yaS1zdW1tYXJ5LWluZm8nKTtcblx0XHRjb25zdCBpc0ZyZWUgID0gd2luZG93LnJvY2tldF9hamF4X2RhdGE/LmlzX2ZyZWUgPT09ICcxJztcblx0XHRjb25zdCAkcXVvdGFCYW5uZXIgPSBpc0ZyZWUgPyAkKCcjd3ByLXJpLXF1b3RhLWJhbm5lcicpIDogJCgnI3JvY2tldF9pbnNpZ2h0c19zdXJ2ZXknKTtcblxuXHRcdC8vIFNob3cgYmFubmVyIGlmIFVSTCBsaW1pdCByZWFjaGVkIE9SIG5vIGNyZWRpdHMgbGVmdCAobWF0Y2hpbmcgUEhQIGxvZ2ljIGluIFN1YnNjcmliZXIucGhwIGxpbmUgMzk4KS5cblx0XHRjb25zdCBzaG91bGRTaG93QmFubmVyID0gY2FuQWRkUGFnZXMgPT09IGZhbHNlIHx8ICFoYXNDcmVkaXQ7XG5cblx0XHRpZiAoc2hvdWxkU2hvd0Jhbm5lcikge1xuXHRcdFx0JHN1bW1hcnlJbmZvLmhpZGUoKTtcblx0XHRcdCRxdW90YUJhbm5lci5yZW1vdmVDbGFzcygnaGlkZGVuJyk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdCRzdW1tYXJ5SW5mby5zaG93KCk7XG5cdFx0XHQkcXVvdGFCYW5uZXIuYWRkQ2xhc3MoJ2hpZGRlbicpO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlSGFzQ3JlZGl0KSB7XG5cdFx0aWYgKHJlc3BvbnNlSGFzQ3JlZGl0ICE9PSB1bmRlZmluZWQgJiYgaGFzQ3JlZGl0ICE9PSByZXNwb25zZUhhc0NyZWRpdCkge1xuXHRcdFx0aGFzQ3JlZGl0ID0gcmVzcG9uc2VIYXNDcmVkaXQ7XG5cblx0XHRcdC8vIFVwZGF0ZSBhbGwgcmV0ZXN0IGJ1dHRvbnMgd2hlbiBjcmVkaXQgc3RhdHVzIGNoYW5nZXNcblx0XHRcdHVwZGF0ZUFsbFJldGVzdEJ1dHRvbnMoKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVBbGxSZXRlc3RCdXR0b25zKCkge1xuXHRcdGNvbnN0IHJldGVzdEJ1dHRvbnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLWFjdGlvbi1zcGVlZF9yYWRhcl9yZWZyZXNoJyk7XG5cblx0XHRyZXRlc3RCdXR0b25zLmZvckVhY2goYnV0dG9uID0+IHtcblx0XHRcdGNvbnN0IHJvdyA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWl0ZW0nKTtcblx0XHRcdGlmICghcm93KSByZXR1cm47XG5cblx0XHRcdC8vIEdldCB0aGUgcm93IElEIGFuZCBjaGVjayBpZiBpdCdzIGN1cnJlbnRseSBiZWluZyBwcm9jZXNzZWRcblx0XHRcdGNvbnN0IHJvd0lkID0gcGFyc2VJbnQocm93LmRhdGFzZXQucm9ja2V0SW5zaWdodHNJZCwgMTApO1xuXHRcdFx0Y29uc3QgaXNSdW5uaW5nID0gcm9ja2V0SW5zaWdodHNJZHMuaW5jbHVkZXMocm93SWQpO1xuXG5cdFx0XHRpZiAoIWhhc0NyZWRpdCB8fCBpc1J1bm5pbmcpIHtcblx0XHRcdFx0Ly8gRGlzYWJsZSBidXR0b25cblx0XHRcdFx0YnV0dG9uLmNsYXNzTGlzdC5hZGQoJ3dwci1yaS1hY3Rpb24tLWRpc2FibGVkJyk7XG5cdFx0XHRcdGJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2Rpc2FibGVkJywgJ3RydWUnKTtcblxuXHRcdFx0XHRpZiAoIWhhc0NyZWRpdCkge1xuXHRcdFx0XHRcdC8vIEFkZCB0b29sdGlwIGZvciBubyBjcmVkaXRcblx0XHRcdFx0XHRidXR0b24uY2xhc3NMaXN0LmFkZCgnd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyk7XG5cdFx0XHRcdFx0YnV0dG9uLnNldEF0dHJpYnV0ZSgndGl0bGUnLCB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ucm9ja2V0X2luc2lnaHRzX25vX2NyZWRpdF90b29sdGlwIHx8ICdVcGdyYWRlIHlvdXIgcGxhbiB0byBnZXQgYWNjZXNzIHRvIHJlLXRlc3QgcGVyZm9ybWFuY2Ugb3IgcnVuIG5ldyB0ZXN0cycpO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBFbmFibGUgYnV0dG9uXG5cdFx0XHRcdGJ1dHRvbi5jbGFzc0xpc3QucmVtb3ZlKCd3cHItcmktYWN0aW9uLS1kaXNhYmxlZCcsICd3cHItYnRuLXdpdGgtdG9vbC10aXAnKTtcblx0XHRcdFx0YnV0dG9uLnJlbW92ZUF0dHJpYnV0ZSgnZGlzYWJsZWQnKTtcblx0XHRcdFx0YnV0dG9uLnJlbW92ZUF0dHJpYnV0ZSgndGl0bGUnKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdGZ1bmN0aW9uIHJlc2V0UG9sbGluZygpIHtcblx0XHRpZiAocG9sbFRpbWVyKSB7XG5cdFx0XHRjbGVhclRpbWVvdXQocG9sbFRpbWVyKTtcblx0XHRcdHBvbGxUaW1lciA9IG51bGw7XG5cdFx0fVxuXHRcdHBvbGxJbnRlcnZhbCA9IFBPTExfQkFTRV9JTlRFUlZBTDtcblx0fVxuXG5cdGZ1bmN0aW9uIHNjaGVkdWxlUG9sbGluZygpIHtcblx0XHRpZiAocm9ja2V0SW5zaWdodHNJZHMubGVuZ3RoID4gMCkge1xuXHRcdFx0cG9sbFRpbWVyID0gc2V0VGltZW91dCgoKSA9PiB7XG5cdFx0XHRcdGdldFJlc3VsdHMoKTtcblx0XHRcdH0sIHBvbGxJbnRlcnZhbCk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gaW5jcmVtZW50UG9sbGluZygpIHtcblx0XHRwb2xsSW50ZXJ2YWwgPSBNYXRoLm1pbihwb2xsSW50ZXJ2YWwgKiAxLjMsIFBPTExfTUFYX0lOVEVSVkFMKTsgLy8gRXhwb25lbnRpYWwgYmFja29mZlxuXHR9XG5cbiAgICBmdW5jdGlvbiBpc09uRGFzaGJvYXJkKCkge1xuICAgICAgICBjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHdpbmRvdy5sb2NhdGlvbi5zZWFyY2gpO1xuICAgICAgICByZXR1cm4gdXJsUGFyYW1zLmdldCgncGFnZScpID09PSAnd3Byb2NrZXQnICYmIHdpbmRvdy5sb2NhdGlvbi5oYXNoID09PSAnI2Rhc2hib2FyZCc7XG4gICAgfVxuXG5cdGZ1bmN0aW9uIGlzT25Sb2NrZXRJbnNpZ2h0cygpIHtcblx0XHRjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHdpbmRvdy5sb2NhdGlvbi5zZWFyY2gpO1xuXHRcdHJldHVybiB1cmxQYXJhbXMuZ2V0KCdwYWdlJykgPT09ICd3cHJvY2tldCcgJiYgd2luZG93LmxvY2F0aW9uLmhhc2ggPT09ICcjcm9ja2V0X2luc2lnaHRzJztcblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZUdsb2JhbFNjb3JlUm93KGdsb2JhbFNjb3JlRGF0YSl7XG5cdFx0Y29uc3QgJHRhYmxlR2xvYmFsU2NvcmUgPSAkKCcud3ByLXJpLXVybHMtdGFibGUgLndwci1nbG9iYWwtc2NvcmUnKTtcblx0XHRpZiAoJHRhYmxlR2xvYmFsU2NvcmUubGVuZ3RoID4gMCl7XG5cdFx0XHQkdGFibGVHbG9iYWxTY29yZS5yZXBsYWNlV2l0aChnbG9iYWxTY29yZURhdGEucm93X2h0bWwpO1xuXHRcdH1lbHNlIGlmICgkdGFibGVCb2R5Lmxlbmd0aCA+IDApIHtcblx0XHRcdCR0YWJsZUJvZHkucHJlcGVuZChnbG9iYWxTY29yZURhdGEucm93X2h0bWwpO1xuXHRcdH1cblx0XHRkZWNpZGVHbG9iYWxTY29yZVRvVXBkYXRlKCk7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgZ2xvYmFsIHNjb3JlIFVJIHdpZGdldCBvciB0YWJsZSByb3cgYmFzZWQgb24gdGhlIHNlbGVjdGVkIG1lbnUuXG5cdCAqIFdoZW4gdGhlIGRhc2hib2FyZCBvciByb2NrZXQgaW5zaWdodHMgbWVudSBpcyBjbGlja2VkLCB0aGlzIGZ1bmN0aW9uIHVwZGF0ZXNcblx0ICogdGhlIGNvcnJlc3BvbmRpbmcgZ2xvYmFsIHNjb3JlIGRpc3BsYXkgYWZ0ZXIgYSBzaG9ydCBkZWxheS5cblx0ICovXG5cdGZ1bmN0aW9uIGRlY2lkZUdsb2JhbFNjb3JlVG9VcGRhdGUoKSB7XG5cdFx0aWYgKCcnID09PSBnbG9iYWxTY29yZURhdGEuaHRtbCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRsZXQgZ2xvYmFsU2NvcmVXaWRnZXRzID0gJCgnLndwci1nbG9iYWwtc2NvcmUtd2lkZ2V0Jyk7XG5cblx0XHRpZiAoZ2xvYmFsU2NvcmVXaWRnZXRzLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIFVwZGF0ZSBhbGwgZ2xvYmFsIHNjb3JlIHdpZGdldCBpbnN0YW5jZXMuXG5cdFx0Z2xvYmFsU2NvcmVXaWRnZXRzLmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmh0bWwpO1xuXG5cdFx0Ly8gRGlzYWJsZSBcIkFkZCBQYWdlc1wiIGJ1dHRvbiBvbiBnbG9iYWwgc2NvcmUgd2lkZ2V0LlxuXHRcdGlmICghKCdkaXNhYmxlZF9idG5faHRtbCcgaW4gZ2xvYmFsU2NvcmVEYXRhKSkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCQoJy53cHItZ2xvYmFsLXNjb3JlLXdpZGdldCAud3ByLWdsb2JhbC1zY29yZS13aWRnZXQtYnRuLXdyYXBwZXInKS5odG1sKGdsb2JhbFNjb3JlRGF0YS5kaXNhYmxlZF9idG5faHRtbC5nbG9iYWxfc2NvcmVfd2lkZ2V0KTtcblx0fVxuXG5cdC8vID09PT0gQUpBWCBIYW5kbGVycyA9PT09XG5cdGZ1bmN0aW9uIGdldFJlc3VsdHMoKSB7XG5cdFx0aWYgKHJvY2tldEluc2lnaHRzSWRzLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IHJvY2tldEluc2lnaHRzSWRzIH0gKSxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KHJlc3BvbnNlLnJlc3VsdHMpKSB7XG5cdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdHVzXG5cdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBxdW90YSBiYW5uZXIgdmlzaWJpbGl0eVxuXHRcdFx0XHR1cGRhdGVRdW90YUJhbm5lcihyZXNwb25zZS5jYW5fYWRkX3BhZ2VzKTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIGRhdGEgYW5kIHdpZGdldCB3aGVuIHN0YXR1cyB8fCBwYWdlIGNvdW50IGNoYW5nZXMuXG5cdFx0XHRcdGlmIChnbG9iYWxTY29yZURhdGEuZGF0YS5zdGF0dXMgIT09IHJlc3BvbnNlLmdsb2JhbF9zY29yZV9kYXRhLmRhdGEuc3RhdHVzIHx8IGdsb2JhbFNjb3JlRGF0YS5kYXRhLnBhZ2VzX251bSAhPT0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuZGF0YS5wYWdlc19udW0pIHtcblx0XHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIGRhdGEuXG5cdFx0XHRcdFx0Z2xvYmFsU2NvcmVEYXRhID0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGE7XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgYWxsIGdsb2JhbCBzY29yZSB3aWRnZXQgaW5zdGFuY2VzLlxuXHRcdFx0XHRcdCQoJy53cHItZ2xvYmFsLXNjb3JlLXdpZGdldCcpLmh0bWwocmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuaHRtbCk7XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSByb3cgaW4gdGFibGUgaWYgb24gUm9ja2V0IEluc2lnaHRzIHBhZ2UuXG5cdFx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRcdC8vIEZpcmUgY3VzdG9tIGV2ZW50IGZvciBvdGhlciB3aWRnZXRzIChsaWtlIHJlY29tbWVuZGF0aW9ucylcblx0XHRcdFx0XHRkb2N1bWVudC5kaXNwYXRjaEV2ZW50KG5ldyBDdXN0b21FdmVudCgnd3ByR2xvYmFsU2NvcmVVcGRhdGVkJywgeyBkZXRhaWw6IGdsb2JhbFNjb3JlRGF0YS5kYXRhIH0pKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXNwb25zZS5yZXN1bHRzLmZvckVhY2gocmVzdWx0ID0+IHtcblx0XHRcdFx0XHRjb25zdCAkcm93ID0gJChgLndwci1yaS1pdGVtW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtyZXN1bHQuaWR9XCJdYCk7XG5cdFx0XHRcdFx0JHJvdy5yZXBsYWNlV2l0aChyZXN1bHQuaHRtbCk7XG5cblx0XHRcdFx0XHQkKGRvY3VtZW50KS50cmlnZ2VyKCdyb2NrZXQtaW5zaWdodHMtcGFnZS10ZXN0LXBvbGxpbmcnLCBbcmVzdWx0LmlkXSk7XG5cblx0XHRcdFx0XHQvLyBUcmlnZ2VyIGN1c3RvbSBldmVudCBvbmx5IHdoZW4gdGVzdCBpcyBjb21wbGV0ZWQgYW5kIG5vdCBmYWlsZWQsIHNvIHdlIGRvbid0IHRhcmdldCBhbiBlbGVtZW50IHRoYXQgbWlnaHQgYmUgcmVtb3ZlZCBmcm9tIHRoZSBET00gYWZ0ZXIgdGVzdCBjb21wbGV0aW9uLlxuXHRcdFx0XHRcdGlmIChyZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJykge1xuXHRcdFx0XHRcdFx0JChkb2N1bWVudCkudHJpZ2dlcigncm9ja2V0LWluc2lnaHRzLXBhZ2UtdGVzdC1jb21wbGV0ZWQnLCBbcmVzdWx0LmlkXSk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0aWYgKHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnKSB7XG5cdFx0XHRcdFx0XHRyZW1vdmVJZChyZXN1bHQuaWQpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSk7XG5cblx0XHRcdFx0aW5jcmVtZW50UG9sbGluZygpO1xuXHRcdFx0XHRzY2hlZHVsZVBvbGxpbmcoKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIE9uIGVycm9yLCBjbGVhciBJRHMgYW5kIHN0b3AgcG9sbGluZ1xuXHRcdFx0XHRyb2NrZXRJbnNpZ2h0c0lkcyA9IFtdO1xuXHRcdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdFx0Y29uc29sZS5lcnJvcignUG9sbGluZyBlcnJvcjonLCByZXNwb25zZS5yZXN1bHRzIHx8IHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHRmdW5jdGlvbiBoYW5kbGVBZGRQYWdlKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQvLyBjaGVjayBpZiBoYXMgYXR0ciBkaXNhYmxlZFxuXHRcdGlmICgkKHRoaXMpLmF0dHIoJ2Rpc2FibGVkJykpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBwYWdlVXJsID0gJHBhZ2VVcmxJbnB1dC52YWwoKS50cmltKCk7XG5cblx0XHRpZiAoIWlzVmFsaWRVcmwocGFnZVVybCkpIHtcblx0XHRcdGFsZXJ0KCdQbGVhc2UgZW50ZXIgYSB2YWxpZCBVUkwnKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBzb3VyY2UgPSAkKHRoaXMpLmRhdGEoJ3NvdXJjZScpO1xuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGRhdGE6IHtcblx0XHRcdFx0XHRwYWdlX3VybDogcGFnZVVybCxcblx0XHRcdFx0XHRzb3VyY2U6IHNvdXJjZVxuXHRcdFx0XHR9LFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdGlmICggISBoYXNJZChyZXNwb25zZS5pZCkgKSB7XG5cdFx0XHRcdFx0JHBhZ2VVcmxJbnB1dC52YWwoJycpO1xuXHRcdFx0XHRcdCR0YWJsZUJvZHkuYXBwZW5kKHJlc3BvbnNlLmh0bWwpO1xuXG5cdFx0XHRcdFx0Ly8gQ3VzdG9tIGV2ZW50IHdoZW4gbmV3IHBhZ2UgaXMgYWRkZWQuXG5cdFx0XHRcdFx0JChkb2N1bWVudCkudHJpZ2dlcigncm9ja2V0LWluc2lnaHRzLXBhZ2UtYWRkZWQnKTtcblxuXHRcdFx0XHRcdCR0YWJsZS5yZW1vdmVDbGFzcygnaGlkZGVuJyk7XG5cdFx0XHRcdFx0YWRkSWRzKHJlc3BvbnNlLmlkKTtcblx0XHRcdFx0XHRsZXQgcGFnZXNfbnVtX2NvbnRhaW5lciA9ICQoJyNyb2NrZXRfcm9ja2V0X2luc2lnaHRzX3BhZ2VzX251bScpO1xuXHRcdFx0XHRcdHBhZ2VzX251bV9jb250YWluZXIudGV4dCggcGFyc2VJbnQoIHBhZ2VzX251bV9jb250YWluZXIudGV4dCgpICkgKyAxICk7XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgY3JlZGl0IHN0YXR1c1xuXHRcdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQpO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSBkYXRhLlxuXHRcdFx0XHRcdGdsb2JhbFNjb3JlRGF0YSA9IHJlc3BvbnNlLmdsb2JhbF9zY29yZV9kYXRhO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSByb3cgaW4gdGFibGUgaWYgb24gUm9ja2V0IEluc2lnaHRzIHBhZ2UuXG5cdFx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRcdGlmICgnZGlzYWJsZWRfYnRuX2h0bWwnIGluIGdsb2JhbFNjb3JlRGF0YSkge1xuXHRcdFx0XHRcdFx0JCgnI3dwcl9yb2NrZXRfaW5zaWdodHNfYWRkX3BhZ2VfYnRuX3dyYXBwZXInKS5odG1sKGdsb2JhbFNjb3JlRGF0YS5kaXNhYmxlZF9idG5faHRtbC5yb2NrZXRfaW5zaWdodHMpO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdC8vIFNob3cvaGlkZSBxdW90YSBiYW5uZXIgYmFzZWQgb24gY2FuX2FkZF9wYWdlc1xuXHRcdFx0XHRcdHVwZGF0ZVF1b3RhQmFubmVyKHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMpO1xuXG5cdFx0XHRcdFx0aWYgKHBvbGxUaW1lcikge1xuXHRcdFx0XHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHRcdFx0XHR9XG5cblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIENsZWFyIHRoZSBpbnB1dCBmaWVsZCBvbiBlcnJvclxuXHRcdFx0XHQkcGFnZVVybElucHV0LnZhbCgnJyk7XG5cblx0XHRcdFx0Ly8gSGFuZGxlIFVSTCBsaW1pdCByZWFjaGVkIGVycm9yXG5cdFx0XHRcdGlmIChyZXNwb25zZT8ubWVzc2FnZSAmJiByZXNwb25zZS5tZXNzYWdlLmluY2x1ZGVzKCdNYXhpbXVtIG51bWJlciBvZiBVUkxzIHJlYWNoZWQnKSkge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZSBVSSBzdGF0ZSB0byByZWZsZWN0IFVSTCBsaW1pdCBoYXMgYmVlbiByZWFjaGVkXG5cdFx0XHRcdFx0ZGlzYWJsZUFkZFVybEVsZW1lbnRzKCk7XG5cdFx0XHRcdFx0Ly8gU2hvdyBxdW90YSBiYW5uZXIgKGNhbl9hZGRfcGFnZXMgPSBmYWxzZSlcblx0XHRcdFx0XHR1cGRhdGVRdW90YUJhbm5lcihyZXNwb25zZS5jYW5fYWRkX3BhZ2VzICE9PSB1bmRlZmluZWQgPyByZXNwb25zZS5jYW5fYWRkX3BhZ2VzIDogZmFsc2UpO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Y29uc29sZS5lcnJvcihyZXNwb25zZT8ubWVzc2FnZSB8fCByZXNwb25zZSk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHRmdW5jdGlvbiBoYW5kbGVSZXNldFBhZ2UoZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdGNvbnN0ICRidXR0b24gPSAkKHRoaXMpO1xuXHRcdGxldCBpZCA9ICRidXR0b24ucGFyZW50cygnLndwci1yaS1pdGVtJykuZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0aWYgKCAhIGlkICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IHNvdXJjZSA9ICRidXR0b24uZGF0YSgnc291cmNlJyk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nICsgaWQsXG5cdFx0XHRcdG1ldGhvZDogJ1BBVENIJyxcblx0XHRcdFx0ZGF0YToge1xuXHRcdFx0XHRcdHNvdXJjZTogc291cmNlXG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRhZGRJZHMocmVzcG9uc2UuaWQpO1xuXG5cdFx0XHRcdCQoYCNyaV9kZXRhaWxzXyR7cmVzcG9uc2UuaWR9IC5kZXRhaWxzLXNlY3Rpb24tdGRgKS5yZW1vdmUoKTtcblx0XHRcdFx0Y29uc3QgJHJvdyA9ICQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7cmVzcG9uc2UuaWR9XCJdYCk7XG5cdFx0XHRcdCRyb3cucmVwbGFjZVdpdGgocmVzcG9uc2UuaHRtbCk7XG5cblx0XHRcdFx0Ly8gQ3VzdG9tIGV2ZW50IHdoZW4gcGFnZSBpcyByZXRlc3RlZC5cbiAgICAgICAgXHRcdCQoZG9jdW1lbnQpLnRyaWdnZXIoJ3JvY2tldC1pbnNpZ2h0cy1wYWdlLXJldGVzdCcsIFtyZXNwb25zZS5pZF0pO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdHVzXG5cdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBxdW90YSBiYW5uZXIgdmlzaWJpbGl0eVxuXHRcdFx0XHR1cGRhdGVRdW90YUJhbm5lcihyZXNwb25zZS5jYW5fYWRkX3BhZ2VzKTtcblxuICAgICAgICAgICAgICAgIC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YS5cbiAgICAgICAgICAgICAgICBnbG9iYWxTY29yZURhdGEgPSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIHJvdyBpbiB0YWJsZSBpZiBvbiBSb2NrZXQgSW5zaWdodHMgcGFnZS5cblx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRpZiAocG9sbFRpbWVyKSB7XG5cdFx0XHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0XHRcdH1cblx0XHRcdFx0c2NoZWR1bGVQb2xsaW5nKCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRjb25zb2xlLmVycm9yKHJlc3BvbnNlPy5tZXNzYWdlIHx8IHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8vID09PT0gSW5pdGlhbGl6YXRpb24gPT09PVxuXHQvLyBCaW5kIGV2ZW50XG5cdCQoZG9jdW1lbnQpLm9uKCAnY2xpY2snLCAnI3dwci1hY3Rpb24tYWRkX3BhZ2Vfc3BlZWRfcmFkYXInLCBoYW5kbGVBZGRQYWdlICk7XG5cdCQoZG9jdW1lbnQpLm9uKCAnY2xpY2snLCAnLndwci1hY3Rpb24tc3BlZWRfcmFkYXJfcmVmcmVzaCcsIGhhbmRsZVJlc2V0UGFnZSApO1xuXHQvLyBIYW5kbGUgRW50ZXIga2V5IHByZXNzIG9uIHBhZ2UgdXJsIGlucHV0LlxuXHQkKGRvY3VtZW50KS5vbiggJ2tleXByZXNzJywgJyN3cHItc3BlZWQtcmFkYXItdXJsLWlucHV0JywgZnVuY3Rpb24oZSkge1xuXHRcdGlmIChlLmtleSA9PT0gJ0VudGVyJykge1xuXHRcdCAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCAgJCgnI3dwci1hY3Rpb24tYWRkX3BhZ2Vfc3BlZWRfcmFkYXInKS5jbGljaygpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gT25seSBwb2xsIGlmIG9uIGEgd3ByIHNlY3Rpb24gdGhhdCByZXF1aXJlcyBwb2xsaW5nKGRhc2hib2FyZHxyb2NrZXRfaW5zaWdodHMpIChtb3JlIHJvYnVzdCBjaGVjaylcbiAgICBmdW5jdGlvbiBpc1ZhbGlkUGFnZUZvclBvbGxpbmcoKSB7XG4gICAgICAgIGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMod2luZG93LmxvY2F0aW9uLnNlYXJjaCk7XG5cdFx0cmV0dXJuICd3cHJvY2tldCcgPT09IHVybFBhcmFtcy5nZXQoJ3BhZ2UnKTtcbiAgICB9XG5cblx0Ly8gUmVzdW1lIHBvbGxpbmcgaWYgbmVlZGVkXG5cdGlmIChpc1ZhbGlkUGFnZUZvclBvbGxpbmcoKSAmJiByb2NrZXRJbnNpZ2h0c0lkcy5sZW5ndGggPiAwKSB7XG5cdFx0aWYgKHBvbGxUaW1lcikge1xuXHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0fVxuXHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHR9XG5cblx0Ly8gSGFuZGxlIFVJIHVwZGF0ZSBvbiB0aGUgcm9ja2V0IGluc2lnaHRzIHRhYiB3aGVuIFwiQWRkIFBhZ2VzXCIgYnV0dG9uIG9uIHRoZSBnbG9iYWwgc2NvcmUgd2lkZ2V0IGlzIGNsaWNrZWQuXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXBlcmNlbnRhZ2Utc2NvcmUtd2lkZ2V0IC53cHItcmktYWRkLXVybC1idXR0b24nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAoIXRoaXMudGV4dENvbnRlbnQuaW5jbHVkZXMoJ0FkZCBQYWdlcycpKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gRGVsYXkgVUkgdXBkYXRlIGEgYml0IHRpbGwgZWxlbWVudCBpcyB2aXNpYmxlLlxuXHRcdHNldFRpbWVvdXQoKCkgPT4ge1xuXHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblx0XHR9LCAzMCk7XG5cdH0pO1xuXG5cdC8vIEhhbmRsZSBjb2xsYXBzZWVkIHN0eWxpbmcgZm9yIGZpcnN0IGxvYWQgb3IgZHluYW1pYyByb3cgYWRkaXRpb24uXG5cdGZ1bmN0aW9uIGFkZENvbGxhcHNlZFN0eWxpbmdUb0xhc3RSb3cob25Mb2FkID0gZmFsc2UpIHtcblx0XHQkKCcud3ByLXJpLWl0ZW0nKS5sYXN0KCkuZmluZCgndGQnKS5hZGRDbGFzcygnYm9yZGVyLWJvdHRvbScpO1xuXG5cdFx0aWYgKG9uTG9hZCkge1xuXHRcdFx0Ly8gT24gbG9hZCwgcmVtb3ZlIHdwci1sYXN0LWV4cGFuZGVkIGZyb20gZWxlbWVudHMgdGhhdCBhcmUgbm90IHRoZSBsYXN0IGFmdGVyIGJlaW5nIGFkZGVkIGZyb20gYmFja2VuZC5cblx0XHRcdCQoJy53cHItcmktaXRlbS10b2dnbGUnKS5ub3QoJzpsYXN0JykucmVtb3ZlQ2xhc3MoJ3dwci1sYXN0LWV4cGFuZGVkJyk7XG5cdFx0XHQkKCcud3ByLXJpLWl0ZW0tYWN0aW9ucycpLm5vdCgnOmxhc3QnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtZXhwYW5kZWQnKTtcblx0XHRcdCQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5ub3QoJzpsYXN0JykucmVtb3ZlQ2xhc3MoJ3dwci1sYXN0LWV4cGFuZGVkJyk7XG5cblx0XHRcdC8vIEJhaWwgZWFybHkgaWYgbGFzdCBpdGVtIGlzIGFscmVhZHkgZXhwYW5kZWQgb24gbG9hZCBzbyBhcyBub3QgdG8gaGF2ZSBjb25mbGljdGluZyBzdHlsZXMuXG5cdFx0XHRpZigkKCcuZGV0YWlscy1zZWN0aW9uLXRkJykubGFzdCgpLmhhc0NsYXNzKCd3cHItbGFzdC1leHBhbmRlZCcpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHR9XG5cdFx0JCgnLndwci1yaS1pdGVtLXRvZ2dsZScpLmxhc3QoKS5hZGRDbGFzcygnd3ByLWxhc3QtY29sbGFwc2VkJyk7XG5cdFx0JCgnLndwci1yaS1pdGVtLWFjdGlvbnMnKS5sYXN0KCkuYWRkQ2xhc3MoJ3dwci1sYXN0LWNvbGxhcHNlZCcpO1xuXHRcdCQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5sYXN0KCkuYWRkQ2xhc3MoJ3dwci1sYXN0LWNvbGxhcHNlZCcpO1xuXHR9XG5cblx0Ly8gVG9nZ2xlcyB0aGUgdmlzaWJpbGl0eSBvZiBhIHNpbmdsZSB0ZXN0IGRldGFpbHMgcm93LCBzd2l0Y2hlcyB0aGUgY2FyZXQgaWNvbiwgYW5kIHVwZGF0ZXMgc3R5bGluZyBmb3IgdGhlIGxhc3QgaXRlbS5cblx0ZnVuY3Rpb24gdG9nZ2xlU2luZ2xlUm93VmlzaWJpbGl0eShpbnNpZ2h0c0lkLCBzb3VyY2UpIHtcblx0XHRsZXQgJGVsZW1lbnQgPSAkKGAjcmlfZGV0YWlsc18ke2luc2lnaHRzSWR9YCk7XG5cdFx0bGV0IGlzVmlzaWJsZSA9ICRlbGVtZW50Lmhhc0NsYXNzKCd3cHItcmktZGV0YWlscy0tZXhwYW5kZWQnKTtcblx0XHRsZXQgaXNMYXN0ID0gJChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtpbnNpZ2h0c0lkfVwiXSAud3ByLXJpLWl0ZW0tdG9nZ2xlLXNpbmdsZWApLmlzKCQoJy53cHItcmktaXRlbS10b2dnbGUtc2luZ2xlJykubGFzdCgpKTtcblxuXHRcdGlmICggaXNWaXNpYmxlICkge1xuXHRcdFx0JGVsZW1lbnQucmVtb3ZlQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdFx0JChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtpbnNpZ2h0c0lkfVwiXWApLnJlbW92ZUNsYXNzKCd3cHItcmktaXRlbS0tZXhwYW5kZWQnKTtcblx0XHRcdC8vIE1hbmlwdWxhdGUgc3R5bGluZyBmb3IgbGFzdCBlbGVtZW50cyB3aGVuIGRldGFpbHMgY2VsbCBpcyBub3QgdmlzaWJsZS5cblx0XHRcdGlmIChpc0xhc3QpIHtcblx0XHRcdFx0dXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKGZhbHNlKTtcblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCRlbGVtZW50LmFkZENsYXNzKCd3cHItcmktZGV0YWlscy0tZXhwYW5kZWQnKTtcblx0XHQkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke2luc2lnaHRzSWR9XCJdYCkuYWRkQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXG5cdFx0Ly8gVHJhY2sgZXhwYW5kIG9ubHkgZXhwYW5kIG1ldHJpYyBhY3Rpb24uXG5cdFx0aGFuZGxlTWV0cmljQWN0aW9uVHJhY2tpbmcoJ2V4cGFuZCcsIGluc2lnaHRzSWQsIHNvdXJjZSk7XG5cblx0XHRpZiAoaXNMYXN0KSB7XG5cdFx0XHR1cGRhdGVSb3dTdHlsaW5nRm9yTGFzdEl0ZW0oKTtcblx0XHR9XG5cdH1cblxuXHQvLyBUcmFja3MgdXNlciBpbnRlcmFjdGlvbnMgd2l0aCBtZXRyaWMgYWN0aW9ucyBpbiBSb2NrZXQgSW5zaWdodHMgdmlhIEFKQVguXG5cdGZ1bmN0aW9uIGhhbmRsZU1ldHJpY0FjdGlvblRyYWNraW5nKGV2ZW50LCByb3dJZCwgc291cmNlKSB7XG5cdFx0JC5wb3N0KFxuXHRcdFx0YWpheHVybCxcblx0XHRcdHtcblx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X2luc2lnaHRfdHJhY2tfbWV0cmljX2FjdGlvbnMnLFxuXHRcdFx0XHRfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcblx0XHRcdFx0ZXZlbnQ6IGV2ZW50LFxuXHRcdFx0XHRyb3dfaWQ6IHJvd0lkLFxuXHRcdFx0XHRzb3VyY2U6IHNvdXJjZVxuXHRcdFx0fSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICghcmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRcdGNvbnNvbGUuZXJyb3IoJ01ldHJpYyBhY3Rpb24gdHJhY2tpbmcgZmFpbGVkOicsIHJlc3BvbnNlPy5kYXRhIHx8IHJlc3BvbnNlKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdCk7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgYm9yZGVyIHN0eWxpbmcgZm9yIHRoZSBsYXN0IHJvdyBpdGVtIGluIHRoZSBSb2NrZXQgSW5zaWdodHMgdGFibGUuXG5cdCAqXG5cdCAqIE1hbmFnZXMgYm9yZGVyIHJhZGl1cyBhbmQgYm90dG9tIGJvcmRlciBzdHlsaW5nIGJhc2VkIG9uIHdoZXRoZXIgdGhlIGRldGFpbHNcblx0ICogY2VsbCBpcyBleHBhbmRlZCBvciBjb2xsYXBzZWQgdG8gbWFpbnRhaW4gcHJvcGVyIHZpc3VhbCBhcHBlYXJhbmNlLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKGlzRXhwYW5kZWQgPSB0cnVlKSB7XG5cdFx0Y29uc3QgYWRkU3RhdGUgPSBpc0V4cGFuZGVkID8gJ3dwci1sYXN0LWV4cGFuZGVkJyA6ICd3cHItbGFzdC1jb2xsYXBzZWQnO1xuXHRcdGNvbnN0IHJlbW92ZVN0YXRlID0gaXNFeHBhbmRlZCA/ICd3cHItbGFzdC1jb2xsYXBzZWQnIDogJ3dwci1sYXN0LWV4cGFuZGVkJztcblxuXHRcdHZhciAkc2VsZWN0b3JzID0ge1xuXHRcdFx0bGFzdFRvZ2dsZTogJCgnLndwci1yaS1pdGVtLXRvZ2dsZScpLmxhc3QoKSxcblx0XHRcdGxhc3RBY3Rpb25zOiAkKCcud3ByLXJpLWl0ZW0tYWN0aW9ucycpLmxhc3QoKVxuXHRcdH07XG5cblx0XHQkc2VsZWN0b3JzLmxhc3RUb2dnbGVcblx0XHRcdC5yZW1vdmVDbGFzcyhyZW1vdmVTdGF0ZSlcblx0XHRcdC5hZGRDbGFzcyhhZGRTdGF0ZSk7XG5cblx0XHQkc2VsZWN0b3JzLmxhc3RBY3Rpb25zXG5cdFx0XHQucmVtb3ZlQ2xhc3MocmVtb3ZlU3RhdGUpXG5cdFx0XHQuYWRkQ2xhc3MoYWRkU3RhdGUpO1xuXG5cblx0XHQvLyBDaGVjayBpZiBsYXN0IGRldGFpbCByb3cgaXMgbm90IHRoZSBsYXN0IHJvdyBpbiB0aGUgdGFibGUgc28gYXMgbm90IHRvIGFwcGx5IGltcHJvcGVyIHN0eWxpbmcgd2l0aCBib3JkZXIgcmFkaXVzIGJldHdlZW4gcm93cy5cblx0XHR2YXIgJGxhc3REZXRhaWxzQ2VsbCA9ICQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5sYXN0KCk7XG5cdFx0aWYgKCRsYXN0RGV0YWlsc0NlbGwuY2xvc2VzdCgndHInKS5uZXh0KCd0cicpLmxlbmd0aCAhPT0gMCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCRsYXN0RGV0YWlsc0NlbGwucmVtb3ZlQ2xhc3MocmVtb3ZlU3RhdGUpXG5cdFx0LmFkZENsYXNzKGFkZFN0YXRlKTtcblx0fVxuXG5cdC8vIFRvZ2dsZSBzaW5nbGUgaXRlbS5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktaXRlbS10b2dnbGUtc2luZ2xlJywgZnVuY3Rpb24oKSB7XG5cdFx0dmFyIGluc2lnaHRzSWQgPSAkKHRoaXMpLmNsb3Nlc3QoJy53cHItcmktaXRlbScpLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdHRvZ2dsZVNpbmdsZVJvd1Zpc2liaWxpdHkoaW5zaWdodHNJZCwgJ3VybF9leHBhbmQnKTtcblx0fSk7XG5cblx0Ly8gVG9nZ2xlIGFsbCBpdGVtcy5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktaXRlbS10b2dnbGUtYWxsJywgZnVuY3Rpb24gKCkge1xuXHRcdGlmICgkKCcud3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJykubGVuZ3RoID4gMCkge1xuXHRcdFx0JCgnLndwci1yaS1kZXRhaWxzJykucmVtb3ZlQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdFx0JCgnLndwci1yaS1pdGVtJykucmVtb3ZlQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXHRcdFx0JCh0aGlzKS5yZW1vdmVDbGFzcygnd3ByLXJpLWl0ZW0tdG9nZ2xlLWFsbC0tZXhwYW5kZWQnKTtcblx0XHRcdHVwZGF0ZVJvd1N0eWxpbmdGb3JMYXN0SXRlbShmYWxzZSk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQkKCcud3ByLXJpLWRldGFpbHMnKS5hZGRDbGFzcygnd3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJyk7XG5cdFx0JCgnLndwci1yaS1pdGVtJykuYWRkQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXHRcdCQodGhpcykuYWRkQ2xhc3MoJ3dwci1yaS1pdGVtLXRvZ2dsZS1hbGwtLWV4cGFuZGVkJyk7XG5cdFx0dXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKCk7XG5cblx0XHQvLyBUcmFjayBzaW5nbGUgZXhwYW5kIGV2ZW50IGZvciBcIkV4cGFuZCBBbGxcIiBhY3Rpb24gd2l0aCB0ZXN0X2lkIGFzICdhbGwnLlxuXHRcdGhhbmRsZU1ldHJpY0FjdGlvblRyYWNraW5nKCdleHBhbmQnLCAnYWxsJywgJ2dsb2JhbF9leHBhbmQnKTtcblx0fSk7XG5cblx0Ly8gVHJhY2sgXCJTZWUgR1RtZXRyaXggUmVwb3J0XCIgY2xpY2tzIGluIFJvY2tldCBJbnNpZ2h0cy5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmVwb3J0JywgZnVuY3Rpb24oZSkge1x0Ly8gT25seSB0cmFjayBpZiBsaW5rIGlzIG5vdCBkaXNhYmxlZCBhbmQgbWl4cGFuZWwgaXMgYXZhaWxhYmxlLlxuXHRcdGlmICgkKHRoaXMpLmhhc0NsYXNzKCd3cHItcmktYWN0aW9uLS1kaXNhYmxlZCcpKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dmFyIGluc2lnaHRzSWQgPSAkKHRoaXMpLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1yb3ctaWQnKTtcblx0XHRoYW5kbGVNZXRyaWNBY3Rpb25UcmFja2luZygnc2VlX3JlcG9ydCcsIGluc2lnaHRzSWQsICdzZWVfcmVwb3J0X2J1dHRvbicpO1xuXHR9KTtcblxuXHQvLyBVcGRhdGUgdGFibGUgc3R5bGluZyBhZnRlciBuZXcgcGFnZSBpcyBhZGRlZC5cblx0JChkb2N1bWVudCkub24oJ3JvY2tldC1pbnNpZ2h0cy1wYWdlLXRlc3QtY29tcGxldGVkJywgZnVuY3Rpb24gKGUsIGluc2lnaHRzSWQpIHtcblx0XHQvLyBCYWlsIG91dCBpZiB0aGVyZSBpcyBtb3JlIHRoYW4gMSByZXN1bHQuXG5cdFx0aWYgKCQoJy53cHItcmktaXRlbS1yZXN1bHQnKS5sZW5ndGggPiAxKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gUmVtb3ZlIGR5bmFtaWMgY2xhc3Mgd2hlbiBvbmx5IG9uZSBpdGVtIGV4aXN0IGluIHRhYmxlLlxuXHRcdCQoJy53cHItbGFzdC1jb2xsYXBzZWQnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtY29sbGFwc2VkJyk7XG5cdH0pO1xuXG5cdC8vIFVwZGF0ZSB0YWJsZSBzdHlsaW5nIGFmdGVyIG5ldyBwYWdlIGlzIGFkZGVkLlxuXHQkKGRvY3VtZW50KS5vbigncm9ja2V0LWluc2lnaHRzLXBhZ2UtYWRkZWQnLCBmdW5jdGlvbiAoZSkge1xuXHRcdC8vIFJlbW92ZSBkeW5hbWljIGNsYXNzIGZvciBsYXN0IGl0ZW0gaWYgZXhpc3RzIHdoZW4gbmV3IHBhZ2UgaXMgYWRkZWQuXG5cdFx0JCgnLndwci1sYXN0LWNvbGxhcHNlZCcpLnJlbW92ZUNsYXNzKCd3cHItbGFzdC1jb2xsYXBzZWQnKTtcblx0XHQkKCcud3ByLWxhc3QtZXhwYW5kZWQnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtZXhwYW5kZWQnKTtcblx0XHQkKCcuYm9yZGVyLWJvdHRvbScpLnJlbW92ZUNsYXNzKCdib3JkZXItYm90dG9tJyk7XG5cdH0pO1xuXG5cdC8vIFVwZGF0ZSB0YWJsZSBzdHlsaW5nIGFmdGVyIHJldGVzdCBvciBwb2xsaW5nIHVwZGF0ZSBmb3IgbGFzdCByb3cuXG5cdCQoZG9jdW1lbnQpLm9uKCdyb2NrZXQtaW5zaWdodHMtcGFnZS1yZXRlc3Qgcm9ja2V0LWluc2lnaHRzLXBhZ2UtdGVzdC1wb2xsaW5nJywgZnVuY3Rpb24gKGUsIGluc2lnaHRzSWQpIHtcblx0XHQvLyBDaGVjayBpZiBpdGVtIGlzIHRoZSBsYXN0LlxuXHRcdHZhciBpc0xhc3QgPSAkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke2luc2lnaHRzSWR9XCJdYCkuaXMoJCgnLndwci1yaS1pdGVtLXJlc3VsdCcpLmxhc3QoKSk7XG5cblx0XHRpZiAoaXNMYXN0KSB7XG5cdFx0XHRhZGRDb2xsYXBzZWRTdHlsaW5nVG9MYXN0Um93KCk7XG5cdFx0fVxuXHR9KTtcblxuXHQkKGRvY3VtZW50KS5vbigncm9ja2V0LWluc2lnaHRzLXBhZ2UtcmV0ZXN0JywgZnVuY3Rpb24gKGUsIGluc2lnaHRzSWQpIHtcblx0XHQkKGAjcmlfZGV0YWlsc18ke2luc2lnaHRzSWR9YCkucmVtb3ZlQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdCQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7aW5zaWdodHNJZH1cIl1gKS5yZW1vdmVDbGFzcygnd3ByLXJpLWl0ZW0tLWV4cGFuZGVkJyk7XG5cdH0pO1xuXG5cdCQod2luZG93KS5sb2FkKCgpID0+IHtcblx0XHRpZiAoICEgaXNPblJvY2tldEluc2lnaHRzKCkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQWRkIGNvbGxhcHNlZCBzdHlsaW5nIHRvIHRoZSBsYXN0IHJvdyBvbiBpbml0aWFsIGxvYWQuXG5cdFx0YWRkQ29sbGFwc2VkU3R5bGluZ1RvTGFzdFJvdyh0cnVlKTtcblxuXHRcdC8vIFNldCBpbml0aWFsIGV4cGFuZC9jb2xsYXBzZSBzdGF0ZS5cblx0XHRjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHdpbmRvdy5sb2NhdGlvbi5zZWFyY2gpO1xuXHRcdGNvbnN0IHRlc3RJZCA9IHVybFBhcmFtcy5nZXQoJ3JpX2lkJyk7XG5cblx0XHQvLyBTZW5kIG1peHBhbmVsIGV2ZW50IGZvciBhdXRvIGV4cGFuZGVkIHJvdy5cblx0XHRsZXQgZmlyc3RSb3dJZCA9ICQoJy53cHItcmktaXRlbS0tZXhwYW5kZWQnKT8uZmlyc3QoKT8uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHQvLyBDaGVjayBpZiByaV9pZCB3YXMgcGFzc2VkIGluIHF1ZXJ5IHN0cmluZyB0byBvcGVuIHNwZWNpZmljIHRlc3QuXG5cdFx0aWYgKCF0ZXN0SWQgfHwgdGVzdElkID09PSAnJykge1xuXHRcdFx0aWYgKGZpcnN0Um93SWQpIHtcblx0XHRcdFx0aGFuZGxlTWV0cmljQWN0aW9uVHJhY2tpbmcoJ2V4cGFuZCcsIGZpcnN0Um93SWQsICdhdXRvX2V4cGFuZF91cmwnKTtcblx0XHRcdH1cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRoYW5kbGVNZXRyaWNBY3Rpb25UcmFja2luZygnZXhwYW5kJywgdGVzdElkLCAncG9zdCB0eXBlIGxpc3RpbmcnKTtcblxuXHRcdCQoJ2h0bWwsIGJvZHknKS5hbmltYXRlKHtcblx0XHRcdHNjcm9sbFRvcDogJChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHt0ZXN0SWR9XCJdYCkub2Zmc2V0KCkudG9wIC0gMTAwXG5cdFx0fSwgNTAwKTtcblxuXHRcdC8vIFJlbW92ZSByaV9pZCBmcm9tIFVSTCB3aXRob3V0IHBhZ2UgcmVsb2FkXG5cdFx0dXJsUGFyYW1zLmRlbGV0ZSgncmlfaWQnKTtcblx0XHRjb25zdCBuZXdVcmwgPSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgKyAnPycgKyB1cmxQYXJhbXMudG9TdHJpbmcoKSArIHdpbmRvdy5sb2NhdGlvbi5oYXNoO1xuXHRcdHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSh7fSwgJycsIG5ld1VybCk7XG5cdH0pO1xufSk7XG4iLCIvLyBBZGQgZ3JlZW5zb2NrIGxpYiBmb3IgYW5pbWF0aW9uc1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svVHdlZW5MaXRlLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9UaW1lbGluZUxpdGUubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL2Vhc2luZy9FYXNlUGFjay5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svcGx1Z2lucy9DU1NQbHVnaW4ubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzJztcclxuXHJcbi8vIEFkZCBzY3JpcHRzXHJcbmltcG9ydCAnLi4vZ2xvYmFsL2Nkbi1kcml2ZXIuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9wYWdlTWFuYWdlci5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL21haW4uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9maWVsZHMuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9iZWFjb24uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9hamF4LmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvcmVjb21tZW5kYXRpb25zLXdpZGdldC5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL3JvY2tldGNkbi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL3JvY2tldGNkbi1zdWJzY3JpcHRpb24tcG9sbGluZy5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL2NvdW50ZG93bi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL21peHBhbmVsLmpzJyIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcbiAgICBpZiAoJ0JlYWNvbicgaW4gd2luZG93KSB7XG4gICAgICAgIC8qKlxuICAgICAgICAgKiBTaG93IGJlYWNvbnMgb24gYnV0dG9uIFwiaGVscFwiIGNsaWNrXG4gICAgICAgICAqL1xuICAgICAgICB2YXIgJGhlbHAgPSAkKCcud3ByLWluZm9BY3Rpb24tLWhlbHAnKTtcbiAgICAgICAgJGhlbHAub24oJ2NsaWNrJywgZnVuY3Rpb24oZSl7XG4gICAgICAgICAgICB2YXIgaWRzID0gJCh0aGlzKS5hdHRyKCdkYXRhLWJlYWNvbi1pZCcpO1xuICAgICAgICAgICAgdmFyIGJ1dHRvbiA9ICQodGhpcykuZGF0YSgnd3ByX3RyYWNrX2J1dHRvbicpIHx8ICdCZWFjb24gSGVscCc7XG4gICAgICAgICAgICB2YXIgY29udGV4dCA9ICQodGhpcykuZGF0YSgnd3ByX3RyYWNrX2NvbnRleHQnKSB8fCAnU2V0dGluZ3MnO1xuXG4gICAgICAgICAgICAvLyBUcmFjayB3aXRoIE1peFBhbmVsIEpTIFNES1xuICAgICAgICAgICAgd3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCk7XG5cbiAgICAgICAgICAgIC8vIENvbnRpbnVlIHdpdGggZXhpc3RpbmcgYmVhY29uIGZ1bmN0aW9uYWxpdHlcbiAgICAgICAgICAgIHdwckNhbGxCZWFjb24oaWRzKTtcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgZnVuY3Rpb24gd3ByQ2FsbEJlYWNvbihhSUQpe1xuICAgICAgICAgICAgYUlEID0gYUlELnNwbGl0KCcsJyk7XG4gICAgICAgICAgICBpZiAoIGFJRC5sZW5ndGggPT09IDAgKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgaWYgKCBhSUQubGVuZ3RoID4gMSApIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcInN1Z2dlc3RcIiwgYUlEKTtcblxuICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcIm9wZW5cIik7XG4gICAgICAgICAgICAgICAgICAgIH0sIDIwMCk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcImFydGljbGVcIiwgYUlELnRvU3RyaW5nKCkpO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICB9XG4gICAgfVxuXG5cdCQoICcud3ByLXJpLXJlcG9ydCcgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByVHJhY2tIZWxwQnV0dG9uKCAncm9ja2V0IGluc2lnaHRzIHNlZSBndG1ldHJpeCByZXBvcnQnLCAncGVyZm9ybWFuY2Ugc3VtbWFyeScgKTtcblx0fSApO1xuXG4gICAgLy8gTWl4UGFuZWwgdHJhY2tpbmcgZnVuY3Rpb25cbiAgICBmdW5jdGlvbiB3cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KSB7XG4gICAgICAgIGlmICh0eXBlb2YgbWl4cGFuZWwgIT09ICd1bmRlZmluZWQnICYmIG1peHBhbmVsLnRyYWNrKSB7XG4gICAgICAgICAgICAvLyBDaGVjayBpZiB1c2VyIGhhcyBvcHRlZCBpbiB1c2luZyBsb2NhbGl6ZWQgZGF0YVxuICAgICAgICAgICAgaWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSA9PT0gJ3VuZGVmaW5lZCcgfHwgIXJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgfHwgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAvLyBJZGVudGlmeSB1c2VyIHdpdGggaGFzaGVkIGxpY2Vuc2UgZW1haWwgaWYgYXZhaWxhYmxlXG4gICAgICAgICAgICBpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICBtaXhwYW5lbC5pZGVudGlmeShyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkKTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgbWl4cGFuZWwudHJhY2soJ0J1dHRvbiBDbGlja2VkJywge1xuICAgICAgICAgICAgICAgICdidXR0b24nOiBidXR0b24sXG5cdFx0XHRcdCdidXR0b25fY29udGV4dCc6IGNvbnRleHQsXG5cdFx0XHRcdCdwbHVnaW4nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG4gICAgICAgICAgICAgICAgJ2JyYW5kJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG4gICAgICAgICAgICAgICAgJ2FwcGxpY2F0aW9uJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwLFxuICAgICAgICAgICAgICAgICdjb250ZXh0Jzogcm9ja2V0X21peHBhbmVsX2RhdGEuY29udGV4dCxcbiAgICAgICAgICAgICAgICAncGF0aCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGgsXG4gICAgICAgICAgICAgICAgJ2ludGVyYWN0aW9uX2NoYW5uZWwnOiAnVUknXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIE1ha2UgZnVuY3Rpb24gZ2xvYmFsbHkgYXZhaWxhYmxlXG4gICAgd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9IHdwclRyYWNrSGVscEJ1dHRvbjtcbn0pO1xuIiwiKCAoIGRvY3VtZW50ICkgPT4ge1xuXHQndXNlIHN0cmljdCc7XG5cblx0ZnVuY3Rpb24gbm90aWZ5Q2RuU3RhdGVDaGFuZ2UoKSB7XG5cdFx0ZG9jdW1lbnQuZGlzcGF0Y2hFdmVudCggbmV3IEN1c3RvbUV2ZW50KCAnd3ByLWNkbi1zdGF0ZS1jaGFuZ2UnICkgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBLZWVwcyB0aGUgaGlkZGVuIGNkbl90eXBlIGFuZCBjZG5fc3RhdGUgZm9ybSBpbnB1dHMgaW4gc3luYyBhZnRlciBhIFJFU1QtZHJpdmVuXG5cdCAqIG1vZGUgY2hhbmdlIHNvIGEgc3Vic2VxdWVudCBmb3JtIHNhdmUgZG9lc24ndCBzZW5kIHN0YWxlIHZhbHVlcyBhbmQgdHJpZ2dlclxuXHQgKiBDZG5TdGF0ZUJyaWRnZTo6cmVjb25jaWxlKCkgdG8gcmVjb21wdXRlIHRoZSB3cm9uZyBzdGF0ZS5cblx0ICpcblx0ICogTWlycm9ycyB0aGUgbWFwcGluZyBpbiBSZXN0OjphcHBseV9jZG5fbW9kZSgpOlxuXHQgKiAgIGJ5b2NkbiDihpIgY2RuX3R5cGU9YnlvY2RuOyBldmVyeXRoaW5nIGVsc2Ug4oaSIGNkbl90eXBlPXJvY2tldGNkbi5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IG1vZGUgTmV3IGNkbl9zdGF0ZSB2YWx1ZSAoJ3JvY2tldGNkbl9mcmVlJywgJ2J5b2NkbicsICdub3RoaW5nJywgZXRjLikuXG5cdCAqL1xuXHRmdW5jdGlvbiBzeW5jQ2RuSGlkZGVuSW5wdXRzKCBtb2RlICkge1xuXHRcdGNvbnN0IHN0YXRlSW5wdXQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ2Nkbl9zdGF0ZScgKTtcblx0XHRpZiAoIHN0YXRlSW5wdXQgKSB7XG5cdFx0XHRzdGF0ZUlucHV0LnZhbHVlID0gbW9kZTtcblx0XHR9XG5cblx0XHRjb25zdCB0eXBlSW5wdXQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ2Nkbl90eXBlJyApO1xuXHRcdGlmICggdHlwZUlucHV0ICkge1xuXHRcdFx0dHlwZUlucHV0LnZhbHVlID0gJ2J5b2NkbicgPT09IG1vZGUgPyAnYnlvY2RuJyA6ICdyb2NrZXRjZG4nO1xuXHRcdH1cblx0fVxuXG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXHRcdGluaXRDZG5Ecml2ZXJUYWJzKCk7XG5cdFx0aW5pdENkbk1vZGVUb2dnbGUoKTtcblx0XHRpbml0QWRkSG9tZXBhZ2UoKTtcblx0XHRpbml0QWRkUGFnZSgpO1xuXHRcdGluaXREZWxldGVQYWdlKCk7XG5cdFx0dXBkYXRlU3VibWl0QnV0dG9uU3RhdGVPblN1YnNjcmlwdGlvbkxvYWRpbmcoKTtcblx0fSApO1xuXG5cdGNvbnN0IGFkZEhvbWVCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwcl9hZGRfcGFnZV9jb21wb25lbnQgLndwci1jZG4tYWRkLXBhZ2VfX2hvbWVwYWdlJyApO1xuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBzdGF0dXMgaW5kaWNhdG9yIGNvbXBvbmVudCB3aXRoIG5ldyBIVE1MIGNvbnRlbnQuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBodG1sIC0gVGhlIEhUTUwgc3RyaW5nIHRvIHJlcGxhY2UgdGhlIHN0YXR1cyBpbmRpY2F0b3Igd2l0aC5cblx0ICogQHJldHVybnMge3ZvaWR9XG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVTdGF0dXNJbmRpY2F0b3JDb21wb25lbnQoIGh0bWwgKSB7XG5cdFx0Y29uc3Qgc3RhdHVzSW5kaWNhdG9yID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWJ1aWx0LWluIC53cHItY2RuLXN0YXR1cycgKTtcblx0XHRpZiAoIHN0YXR1c0luZGljYXRvciAmJiBodG1sICkge1xuXHRcdFx0c3RhdHVzSW5kaWNhdG9yLm91dGVySFRNTCA9IGh0bWw7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIFRvZ2dsZXMgdGhlIGRpc2FibGVkIHN0YXRlIG9mIENETi1yZWxhdGVkIFVJIGVsZW1lbnRzIGJhc2VkIG9uIHRoZSBhY3RpdmUgZHJpdmVyLlxuXHQgKlxuXHQgKiBGb3IgdGhlICdyb2NrZXRjZG4nIGRyaXZlciwgdGFyZ2V0cyBib3RoIHNoYXJlZCBDRE4gYW5kIFJvY2tldENETiBzZWN0aW9ucy5cblx0ICogRm9yIGFsbCBvdGhlciBkcml2ZXJzLCBvbmx5IHRhcmdldHMgdGhlIHNoYXJlZCBDRE4gc2VjdGlvbiBhbmQgYWx3YXlzIGVuYWJsZXMgaXQuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSAgZHJpdmVyICAgVGhlIENETiBkcml2ZXIgaWRlbnRpZmllciAoZS5nLiAncm9ja2V0Y2RuJykuXG5cdCAqIEBwYXJhbSB7Ym9vbGVhbn0gZGlzYWJsZWQgV2hldGhlciB0byBkaXNhYmxlIHRoZSBDRE4gVUkgZWxlbWVudHMuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVSb2NrZXRDRE5FbGVtZW50c1N0YXRlKCBkcml2ZXIsIGRpc2FibGVkICkge1xuXHRcdGlmICggJ3JvY2tldGNkbicgPT09IGRyaXZlciApIHtcblx0XHRcdGlmICggISBkaXNhYmxlZCApIHtcblx0XHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy5jZG4tc2hhcmVkLXNlY3Rpb24sIC5yb2NrZXRjZG4tc2hhcmVkLXNlY3Rpb24nICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdFx0XHRlbC5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1kaXNhYmxlZCcgKTtcblx0XHRcdFx0fSApO1xuXG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy5jZG4tc2hhcmVkLXNlY3Rpb24sIC5yb2NrZXRjZG4tc2hhcmVkLXNlY3Rpb24nICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdFx0ZWwuY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0XHR9ICk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLmNkbi1zaGFyZWQtc2VjdGlvbicgKS5mb3JFYWNoKCAoIGVsICkgPT4ge1xuXHRcdFx0ZWwuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3dzIG9yIGhpZGVzIHRoZSBsaW1pdC1yZWFjaGVkIHRvb2x0aXAgb24gdGhlIEFERCBQQUdFIGJ1dHRvbi5cblx0ICpcblx0ICogQHBhcmFtIHtib29sZWFufSBsaW1pdFJlYWNoZWQgV2hldGhlciB0aGUgZnJlZS10aWVyIHBhZ2UgbGltaXQgaGFzIGJlZW4gcmVhY2hlZC5cblx0ICogQHJldHVybnMge3ZvaWR9XG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVUb29sdGlwU3RhdGUoIGxpbWl0UmVhY2hlZCApIHtcblx0XHRjb25zdCB0b29sdGlwID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19idXR0b24td3JhcHBlciAud3ByLXRvb2x0aXAnICk7XG5cdFx0aWYgKCB0b29sdGlwICkge1xuXHRcdFx0dG9vbHRpcC5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLWlzSGlkZGVuJywgISBsaW1pdFJlYWNoZWQgKTtcblx0XHR9XG5cdH1cblxuXHQvKiogUGVuZGluZyBiYW5uZXIgYXV0by1leHBhbmQgdGltZXIgSUQsIG9yIG51bGwgd2hlbiBubyB0aW1lciBpcyBhY3RpdmUuICovXG5cdGxldCBhdXRvRXhwYW5kVGltZXIgPSBudWxsO1xuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBSb2NrZXRDRE4gQ1RBIHZpc2liaWxpdHkgYW5kIGV4cGFuc2lvbiBzdGF0ZS5cblx0ICpcblx0ICogV2hlbiB0aGUgcGFnZSBjb3VudCByZWFjaGVzIHRoZSBsaW1pdCwgZXhwYW5zaW9uIGlzIGRlZmVycmVkIGJ5IDE1IHNlY29uZHMgc28gdGhlXG5cdCAqIHVzZXIgaGFzIHRpbWUgdG8gcmVhY3QgYmVmb3JlIHRoZSB1cHNlbGwgYmFubmVyIG9wZW5zLiBBbnkgaW4tZmxpZ2h0IHRpbWVyIGlzXG5cdCAqIGNhbmNlbGxlZCB3aGVuZXZlciB0aGlzIGZ1bmN0aW9uIGlzIGNhbGxlZCAoZS5nLiBvbiBwYWdlIGRlbGV0aW9uKSwgZW5zdXJpbmcgc3RhbGVcblx0ICogZXhwYW5kcyBuZXZlciBmaXJlIGFmdGVyIHRoZSBzdGF0ZSBoYXMgY2hhbmdlZC5cblx0ICpcblx0ICogQHBhcmFtIHtudW1iZXJ9IGNvdW50IEN1cnJlbnQgbnVtYmVyIG9mIGZyZWUtdGllciBwYWdlcy5cblx0ICogQHBhcmFtIHtudW1iZXJ9IGxpbWl0IEZyZWUtdGllciBwYWdlIGxpbWl0LlxuXHQgKiBAcmV0dXJucyB7dm9pZH1cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZVJvY2tldEN0YVN0YXRlKCBjb3VudCwgbGltaXQgKSB7XG5cdFx0Y29uc3QgY3RhID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICd3cHItcm9ja2V0Y2RuLWN0YScgKTtcblx0XHRjb25zdCByZXNlbGxlckJhbm5lciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAnd3ByLXJvY2tldGNkbi1yZXNlbGxlci1saW1pdC1jdGEnICk7XG5cblx0XHRpZiAoICEgY3RhICYmICEgcmVzZWxsZXJCYW5uZXIgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQ2FuY2VsIGFueSBwZW5kaW5nIGV4cGFuZCDigJQgc3RhdGUgaGFzIGNoYW5nZWQuXG5cdFx0aWYgKCBhdXRvRXhwYW5kVGltZXIgIT09IG51bGwgKSB7XG5cdFx0XHRjbGVhclRpbWVvdXQoIGF1dG9FeHBhbmRUaW1lciApO1xuXHRcdFx0YXV0b0V4cGFuZFRpbWVyID0gbnVsbDtcblx0XHR9XG5cblx0XHRjb25zdCBhdExpbWl0ICA9IGNvdW50ID49IGxpbWl0O1xuXG5cdFx0aWYgKCBjdGEgKSB7XG5cdFx0XHRjdGEuY2xhc3NMaXN0LnRvZ2dsZSggJ3dwci1pc0hpZGRlbicsIGNvdW50ID09PSAwICk7XG5cdFx0fVxuXG5cdFx0aWYgKCByZXNlbGxlckJhbm5lciApIHtcblx0XHRcdHJlc2VsbGVyQmFubmVyLmNsYXNzTGlzdC50b2dnbGUoICd3cHItaXNIaWRkZW4nLCAhIGF0TGltaXQgKTtcblx0XHR9XG5cblx0XHRpZiAoIGN0YSApIHtcblx0XHRcdGlmICggYXRMaW1pdCApIHtcblx0XHRcdFx0Ly8gQWx3YXlzIHNob3cgXCJOaWNlIHdvcmshXCIgdGV4dCBpbW1lZGlhdGVseS5cblx0XHRcdFx0Y3RhLmNsYXNzTGlzdC5hZGQoICd3cHItcm9ja2V0Y2RuLWN0YS0tLW1heC1saW1pdCcgKTtcblxuXHRcdFx0XHRpZiAoICEgY3RhLmNsYXNzTGlzdC5jb250YWlucyggJ3dwci1yb2NrZXRjZG4tY3RhLS1leHBhbmRlZCcgKSApIHtcblx0XHRcdFx0XHQvLyBCYW5uZXIgaXMgY29sbGFwc2VkIOKAlCBrZWVwIGl0IGNvbGxhcHNlZCBmb3IgMTVzIHRoZW4gZXhwYW5kLlxuXHRcdFx0XHRcdGN0YS5jbGFzc0xpc3QuYWRkKCAnd3ByLXJvY2tldGNkbi1jdGEtLWNvbGxhcHNlZCcgKTtcblx0XHRcdFx0XHRjdGEuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1yb2NrZXRjZG4tY3RhLS1leHBhbmRlZCcgKTtcblxuXHRcdFx0XHRcdGF1dG9FeHBhbmRUaW1lciA9IHNldFRpbWVvdXQoICgpID0+IHtcblx0XHRcdFx0XHRcdGF1dG9FeHBhbmRUaW1lciA9IG51bGw7XG5cdFx0XHRcdFx0XHRjdGEuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1yb2NrZXRjZG4tY3RhLS1jb2xsYXBzZWQnICk7XG5cdFx0XHRcdFx0XHRjdGEuY2xhc3NMaXN0LmFkZCggJ3dwci1yb2NrZXRjZG4tY3RhLS1leHBhbmRlZCcgKTtcblx0XHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQoIG5ldyBDdXN0b21FdmVudCggJ3JvY2tldENETkJhbm5lckF1dG9FeHBhbmRlZCcgKSApO1xuXHRcdFx0XHRcdH0sIDE1MDAwICk7XG5cdFx0XHRcdH1cblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGN0YS5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLXJvY2tldGNkbi1jdGEtLWNvbGxhcHNlZCcsIGNvdW50ID4gMCApO1xuXHRcdFx0XHRjdGEuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1yb2NrZXRjZG4tY3RhLS1leHBhbmRlZCcsICd3cHItcm9ja2V0Y2RuLWN0YS0tLW1heC1saW1pdCcgKTtcblx0XHRcdH1cblx0XHR9XG5cdH1cblxuXHQvLyBDYW5jZWwgYW55IHBlbmRpbmcgYmFubmVyIGV4cGFuZCB3aGVuIHRoZSB1c2VyIG5hdmlnYXRlcyBhd2F5IGZyb20gdGhlIENETiB0YWIuXG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdyb2NrZXRKc0FmdGVyUGFnZU5hdmlnYXRpb24nLCAoIGUgKSA9PiB7XG5cdFx0aWYgKCBlLmRldGFpbC5wYWdlSWQgIT09ICdwYWdlX2NkbicgJiYgYXV0b0V4cGFuZFRpbWVyICE9PSBudWxsICkge1xuXHRcdFx0Y2xlYXJUaW1lb3V0KCBhdXRvRXhwYW5kVGltZXIgKTtcblx0XHRcdGF1dG9FeHBhbmRUaW1lciA9IG51bGw7XG5cdFx0fVxuXHR9ICk7XG5cblx0LyoqXG5cdCAqIExpc3RlbnMgZm9yIGN1c3RvbSAncm9ja2V0SnNBZnRlclBhZ2VOYXZpZ2F0aW9uJyBldmVudCB0byB1cGRhdGUgdGhlIHN0YXRlIG9mIHRoZSBzdWJtaXQgYnV0dG9uXG5cdCAqIGJhc2VkIG9uIHRoZSBwcmVzZW5jZSBvZiBhIENETiBzdWJzY3JpcHRpb24gbG9hZGluZyBpbmRpY2F0b3Igb24gdGhlIENETiBzZXR0aW5ncyBwYWdlLlxuXHQgKlxuXHQgKiBEaXNhYmxlcyB0aGUgc3VibWl0IGJ1dHRvbiB3aGVuIG5hdmlnYXRpbmcgdG8gdGhlIENETiBwYWdlIGlmIGEgc3Vic2NyaXB0aW9uIGxvYWRpbmcgaW5kaWNhdG9yIGlzIHByZXNlbnQsXG5cdCAqIGFuZCByZS1lbmFibGVzIGl0IHdoZW4gbmF2aWdhdGluZyBhd2F5IGZyb20gdGhlIENETiBwYWdlLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlU3VibWl0QnV0dG9uU3RhdGVPblN1YnNjcmlwdGlvbkxvYWRpbmcoKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ3JvY2tldEpzQWZ0ZXJQYWdlTmF2aWdhdGlvbicsICggZSApID0+IHtcblx0XHRcdC8vIEJhaWwgb3V0IGlmIHN1Ym1pdCBidXR0b24gaXMgbm90IHZpc2libGUgZm9yIHRoZSBjdXJyZW50IHBhZ2UuXG5cdFx0XHRpZiAoZ2V0Q29tcHV0ZWRTdHlsZSggZS5kZXRhaWwuc3VibWl0QnV0dG9uICkuZGlzcGxheSA9PT0gJ25vbmUnKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgY2xhc3NlcyA9IFtcblx0XHRcdFx0Jy53cHItaWNvbi1vcmFuZ2UtbG9hZGVyJyxcblx0XHRcdFx0Jy53cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcsXG5cdFx0XHRdO1xuXG5cdFx0XHRjb25zdCBhbGxQcmVzZW50ID0gY2xhc3Nlcy5ldmVyeSggY2xzID0+IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoIGNscyApICE9PSBudWxsICk7XG5cblx0XHRcdC8vIFJlLWVuYWJsZSBzdWJtaXQgYnV0dG9uIHdoZW4gcGFnZSBpcyBub3QgY2RuIGFuZCBiYWlsIG91dC5cblx0XHRcdGlmIChlLmRldGFpbC5wYWdlSWQgIT09ICdwYWdlX2NkbicpIHtcblx0XHRcdFx0aWYgKGUuZGV0YWlsLnN1Ym1pdEJ1dHRvbi5jbGFzc0xpc3QuY29udGFpbnMoICd3cHItY2RuLWRpc2FibGVkJyApKSB7XG5cdFx0XHRcdFx0ZS5kZXRhaWwuc3VibWl0QnV0dG9uLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItY2RuLWRpc2FibGVkJyApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBCYWlsIG91dCBpZiBubyBjZG4gc3Vic2NyaXB0aW9uIGxvYWRlciBpcyBwcmVzZW50LlxuXHRcdFx0aWYgKCAhIGFsbFByZXNlbnQgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gRGlzYWJsZSBzdWJtaXQgYnV0dG9uIHdoZW4gb24gY2RuIHBhZ2UgYW5kIHN1YnNjcmlwdGlvbiBsb2FkZXIgaXMgcHJlc2VudC5cblx0XHRcdGUuZGV0YWlsLnN1Ym1pdEJ1dHRvbi5jbGFzc0xpc3QuYWRkKCAnd3ByLWNkbi1kaXNhYmxlZCcgKTtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogU2V0cyB0aGUgc3Vic2NyaXB0aW9uIGxvYWRpbmcgc3RhdGUgb24gdGhlIENETiBVSS5cblx0ICpcblx0ICogRGlzYWJsZXMgdGhlIGJ1aWx0LWluIENETiBzZWN0aW9uLCBwdXJnZSBhbmQgZXhjbHVkZSBzZWN0aW9ucy5cblx0ICovXG5cdGZ1bmN0aW9uIHJldmVydFN1YnNjcmlwdGlvbkxvYWRpbmdTdGF0ZSgpIHtcblx0XHRjb25zdCBidWlsdEluID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWJ1aWx0LWluJyApO1xuXHRcdGlmICggYnVpbHRJbiApIHtcblx0XHRcdGJ1aWx0SW4uY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1jZG4tYnVpbHQtaW4tLWRpc2FibGVkJyApO1xuXHRcdH1cblxuXHRcdGNvbnN0IHB1cmdlU2VjdGlvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1wdXJnZS5yb2NrZXRjZG4nICk7XG5cdFx0aWYgKCBwdXJnZVNlY3Rpb24gKSB7XG5cdFx0XHRwdXJnZVNlY3Rpb24uY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0fVxuXG5cdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy53cHItY2RuLWV4Y2x1c2lvbnMnICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdGVsLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItY2RuLWRpc2FibGVkJyApO1xuXHRcdFx0Y29uc3QgdGV4dGFyZWEgPSBlbC5xdWVyeVNlbGVjdG9yKCAndGV4dGFyZWEnICk7XG5cdFx0XHRpZiAoIHRleHRhcmVhICkge1xuXHRcdFx0XHR0ZXh0YXJlYS5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0fVxuXHRcdH0gKTtcblxuXHRcdGNvbnN0IHN1Ym1pdEJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLW9wdGlvbnMtc3VibWl0JyApO1xuXHRcdGlmICggc3VibWl0QnV0dG9uICkge1xuXHRcdFx0c3VibWl0QnV0dG9uLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItY2RuLWRpc2FibGVkJyApO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLWNkbi1tb2RlLXRvZ2dsZV9faW5wdXQnICkuZm9yRWFjaCggKCBtb2RlVG9nZ2xlICkgPT4ge1xuXHRcdFx0bW9kZVRvZ2dsZS5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdH0gKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHNldFN1YnNjcmlwdGlvbkxvYWRpbmdTdGF0ZSgpIHtcblx0XHRjb25zdCBidWlsdEluID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWJ1aWx0LWluJyApO1xuXG5cdFx0aWYgKCBidWlsdEluICkge1xuXHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QuYWRkKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0fVxuXG5cdFx0Ly8gRGlzYWJsZSBwdXJnZSBDRE4gY2FjaGUgc2VjdGlvbi5cblx0XHRjb25zdCBwdXJnZVNlY3Rpb24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tcHVyZ2Uucm9ja2V0Y2RuJyApO1xuXG5cdFx0aWYgKCBwdXJnZVNlY3Rpb24gKSB7XG5cdFx0XHRwdXJnZVNlY3Rpb24uY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0fVxuXG5cdFx0Ly8gRGlzYWJsZSBleGNsdXNpb24gZmllbGRzIGFuZCBzZWN0aW9uIGhlYWRlci5cblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLndwci1jZG4tZXhjbHVzaW9ucycgKS5mb3JFYWNoKCAoIGVsICkgPT4ge1xuXHRcdFx0ZWwuY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cblx0XHRcdGNvbnN0IHRleHRhcmVhID0gZWwucXVlcnlTZWxlY3RvciggJ3RleHRhcmVhJyApO1xuXG5cdFx0XHRpZiAoIHRleHRhcmVhICkge1xuXHRcdFx0XHR0ZXh0YXJlYS5kaXNhYmxlZCA9IHRydWU7XG5cdFx0XHR9XG5cdFx0fSApO1xuXG5cdFx0Y29uc3Qgc3VibWl0QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItb3B0aW9ucy1zdWJtaXQnICk7XG5cdFx0aWYgKCBzdWJtaXRCdXR0b24gKSB7XG5cdFx0XHRzdWJtaXRCdXR0b24uY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0fVxuXG5cdFx0Ly8gRGlzYWJsZSBtb2RlIHRvZ2dsZXMgdG8gcHJldmVudCBzdGF0ZSBjaGFuZ2VzIGR1cmluZyBzdWJzY3JpcHRpb24gY3JlYXRpb24uXG5cdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy53cHItY2RuLW1vZGUtdG9nZ2xlX19pbnB1dCcgKS5mb3JFYWNoKCAoIG1vZGVUb2dnbGUgKSA9PiB7XG5cdFx0XHRtb2RlVG9nZ2xlLmRpc2FibGVkID0gdHJ1ZTtcblx0XHR9ICk7XG5cblx0XHQvLyBDcmVhdGUgcG9sbGluZyBtZWNoYW5pc20gdG8gc2VuZCBhIHJlcXVlc3QgZXZlcnkgMTAgc2Vjb25kcyB0byBnZXQgdGhlIHN1YnNjcmlwdGlvbiBzdGF0dXMgYW5kIG9uY2UgdGhlIHN1YnNjcmlwdGlvbiBpcyBhY3RpdmUsIHdlIHdpbGwgcmVmcmVzaCB0aGUgcGFnZSBmb3Igbm93LlxuXHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQobmV3IEN1c3RvbUV2ZW50KCdyb2NrZXRDRE5TdWJzY3JpcHRpb25Mb2FkaW5nJywge30pKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBgd3ByLWNkbi1hY3RpdmUtaW5kaWNhdG9yYCBjbGFzcyB0byByZWZsZWN0IHdoaWNoIENETiBkcml2ZXIgaGVhZGVyXG5cdCAqIGFuZCB0YWIgYXJlIGFjdGl2ZS5cblx0ICpcblx0ICogQHBhcmFtIHtFbGVtZW50fG51bGx9IGFjdGl2ZVRvZ2dsZSBUb2dnbGUgd2hvc2UgcGFyZW50IGhlYWRlciBhbmQgbWF0Y2hpbmcgdGFiIHNob3VsZFxuXHQgKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlY2VpdmUgdGhlIGNsYXNzLCBvciBudWxsIHRvIGNsZWFyIGFsbC5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUNkbkFjdGl2ZUluZGljYXRvciggYWN0aXZlVG9nZ2xlICkge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLWNkbi1hY3RpdmUtaW5kaWNhdG9yJyApLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRlbC5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1hY3RpdmUtaW5kaWNhdG9yJyApO1xuXHRcdH0gKTtcblxuXHRcdGlmICggYWN0aXZlVG9nZ2xlICkge1xuXHRcdFx0Y29uc3QgaGVhZGVyID0gYWN0aXZlVG9nZ2xlLmNsb3Nlc3QoICcud3ByLW9wdGlvbkhlYWRlcicgKTtcblx0XHRcdGlmICggaGVhZGVyICkge1xuXHRcdFx0XHRoZWFkZXIuY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tYWN0aXZlLWluZGljYXRvcicgKTtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgbW9kZSA9IGFjdGl2ZVRvZ2dsZS5nZXRBdHRyaWJ1dGUoICdkYXRhLWNkbi1tb2RlJyApO1xuXHRcdFx0Y29uc3QgdGFiRHJpdmVyID0gJ2J5b2NkbicgPT09IG1vZGUgPyAneW91ci1vd24tY2RuJyA6ICdyb2NrZXRjZG4nO1xuXHRcdFx0Y29uc3QgdGFiID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggYC53cHItY2RuLXRhYnNfX3RhYltkYXRhLWNkbi1kcml2ZXI9XCIkeyB0YWJEcml2ZXIgfVwiXWAgKTtcblxuXHRcdFx0aWYgKCB0YWIgKSB7XG5cdFx0XHRcdHRhYi5jbGFzc0xpc3QuYWRkKCAnd3ByLWNkbi1hY3RpdmUtaW5kaWNhdG9yJyApO1xuXHRcdFx0fVxuXHRcdH1cblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSB0b2dnbGUgY2hlY2tib3hlcyBhbmQgYWN0aXZlIGluZGljYXRvcnMgdG8gcmVmbGVjdCBSb2NrZXRDRE4gRnJlZVxuXHQgKiBoYXZpbmcganVzdCBiZWVuIGFjdGl2YXRlZCBzZXJ2ZXItc2lkZSAoYXV0by1hY3RpdmF0aW9uIGZyb20gdGhlIFwibm90aGluZyBhY3RpdmVcIlxuXHQgKiBzdGF0ZSwgb3IgYWZ0ZXIgYSBjb25maXJtZWQgYWN0aXZhdGlvbiBwcm9tcHQpLCB3aXRob3V0IGEgZnVsbCBwYWdlIHJlbG9hZC5cblx0ICovXG5cdGZ1bmN0aW9uIGFjdGl2YXRlRnJlZU1vZGVVSSgpIHtcblx0XHRjb25zdCBmcmVlVG9nZ2xlID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLW1vZGUtdG9nZ2xlX19pbnB1dFtkYXRhLWNkbi1tb2RlPVwicm9ja2V0Y2RuX2ZyZWVcIl0nICk7XG5cblx0XHRpZiAoICEgZnJlZVRvZ2dsZSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLndwci1jZG4tbW9kZS10b2dnbGVfX2lucHV0JyApLmZvckVhY2goICggb3RoZXIgKSA9PiB7XG5cdFx0XHRvdGhlci5jaGVja2VkID0gKCBvdGhlciA9PT0gZnJlZVRvZ2dsZSApO1xuXHRcdH0gKTtcblxuXHRcdHVwZGF0ZUNkbkFjdGl2ZUluZGljYXRvciggZnJlZVRvZ2dsZSApO1xuXHRcdHRvZ2dsZURyaXZlclNlY3Rpb25zKCAncm9ja2V0Y2RuJyApO1xuXHRcdHNldEFjdGl2ZVRhYiggJ3JvY2tldGNkbicgKTtcblx0XHRub3RpZnlDZG5TdGF0ZUNoYW5nZSgpO1xuXHRcdHN5bmNDZG5IaWRkZW5JbnB1dHMoICdyb2NrZXRjZG5fZnJlZScgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplcyB0aGUgQ0ROIG1vZGUgdG9nZ2xlIGNoZWNrYm94ZXMuXG5cdCAqXG5cdCAqIENoZWNraW5nIGFjdGl2YXRlcyB0aGF0IG1vZGU7IHVuY2hlY2tpbmcgbGVhdmVzIGFsbCBtb2RlcyBpbmFjdGl2ZSAoJ25vdGhpbmcnKS5cblx0ICogT25seSBvbmUgbW9kZSBjYW4gYmUgYWN0aXZlIGF0IGEgdGltZSDigJQgY2hlY2tpbmcgb25lIHVuY2hlY2tzIHRoZSBvdGhlcnMuXG5cdCAqIFRoZSByZXF1ZXN0IGZpcmVzIGltbWVkaWF0ZWx5IG9uIHRvZ2dsZSBjaGFuZ2UuXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0Q2RuTW9kZVRvZ2dsZSgpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnY2hhbmdlJywgKCBldmVudCApID0+IHtcblx0XHRcdGNvbnN0IHRvZ2dsZSA9IGV2ZW50LnRhcmdldC5jbG9zZXN0KCAnLndwci1jZG4tbW9kZS10b2dnbGVfX2lucHV0JyApO1xuXG5cdFx0XHRpZiAoICEgdG9nZ2xlICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IG1vZGUgPSB0b2dnbGUuZ2V0QXR0cmlidXRlKCAnZGF0YS1jZG4tbW9kZScgKTtcblxuXHRcdFx0aWYgKCAhIG1vZGUgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gQ2FwdHVyZSB0aGUgcHJldmlvdXNseSBhY3RpdmUgdG9nZ2xlIGZvciByb2xsYmFjayBvbiBmYWlsdXJlLlxuXHRcdFx0Ly8gUmVhZCBmcm9tIHdwci1jZG4tYWN0aXZlLWluZGljYXRvciAobWFpbnRhaW5lZCBieSBQSFAgKyB0aGlzIGZ1bmN0aW9uKSByYXRoZXIgdGhhblxuXHRcdFx0Ly8gOmNoZWNrZWQsIGJlY2F1c2UgdGhlIGNoYW5nZSBldmVudCBmaXJlcyBhZnRlciB0aGUgY2hlY2tib3ggc3RhdGUgaGFzIGFscmVhZHkgZmxpcHBlZC5cblx0XHRcdGNvbnN0IHByZXZpb3VzbHlBY3RpdmVIZWFkZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1vcHRpb25IZWFkZXIud3ByLWNkbi1hY3RpdmUtaW5kaWNhdG9yJyApO1xuXHRcdFx0Y29uc3QgcHJldmlvdXNseUFjdGl2ZSA9IHByZXZpb3VzbHlBY3RpdmVIZWFkZXJcblx0XHRcdFx0PyBwcmV2aW91c2x5QWN0aXZlSGVhZGVyLnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1tb2RlLXRvZ2dsZV9faW5wdXQnIClcblx0XHRcdFx0OiBudWxsO1xuXG5cdFx0XHQvLyBtb2RlIHNlbnQgdG8gdGhlIHNlcnZlcjogdGhlIHRvZ2dsZSdzIG1vZGUgd2hlbiBjaGVja2luZywgJ25vdGhpbmcnIHdoZW4gdW5jaGVja2luZy5cblx0XHRcdGNvbnN0IHJlcXVlc3RlZE1vZGUgPSB0b2dnbGUuY2hlY2tlZCA/IG1vZGUgOiAnbm90aGluZyc7XG5cdFx0XHRjb25zdCBzZWN0aW9uRHJpdmVyID0gJ2J5b2NkbicgPT09IG1vZGUgPyAneW91ci1vd24tY2RuJyA6ICdyb2NrZXRjZG4nO1xuXG5cdFx0XHRpZiAoIHRvZ2dsZS5jaGVja2VkICkge1xuXHRcdFx0XHQvLyBVbmNoZWNrIGFsbCBvdGhlciBtb2RlIHRvZ2dsZXMgKG11dHVhbGx5IGV4Y2x1c2l2ZSkuXG5cdFx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLWNkbi1tb2RlLXRvZ2dsZV9faW5wdXQnICkuZm9yRWFjaCggKCBvdGhlciApID0+IHtcblx0XHRcdFx0XHRpZiAoIG90aGVyICE9PSB0b2dnbGUgKSB7XG5cdFx0XHRcdFx0XHRvdGhlci5jaGVja2VkID0gZmFsc2U7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9ICk7XG5cdFx0XHRcdHRvZ2dsZURyaXZlclNlY3Rpb25zKCBzZWN0aW9uRHJpdmVyICk7XG5cdFx0XHRcdHNldEFjdGl2ZVRhYiggc2VjdGlvbkRyaXZlciApO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBPcHRpbWlzdGljYWxseSB1cGRhdGUgdGhlIGFjdGl2ZSBpbmRpY2F0b3IgYmVmb3JlIHRoZSByZXF1ZXN0IGNvbXBsZXRlcy5cblx0XHRcdHVwZGF0ZUNkbkFjdGl2ZUluZGljYXRvciggdG9nZ2xlLmNoZWNrZWQgPyB0b2dnbGUgOiBudWxsICk7XG5cblx0XHRcdG5vdGlmeUNkblN0YXRlQ2hhbmdlKCk7XG5cblx0XHRcdHRvZ2dsZS5kaXNhYmxlZCA9IHRydWU7XG5cblx0XHRcdGlmICggJ3JvY2tldGNkbl9mcmVlJyA9PT0gcmVxdWVzdGVkTW9kZSApIHtcblx0XHRcdFx0c2V0U3Vic2NyaXB0aW9uTG9hZGluZ1N0YXRlKCk7XG5cdFx0XHR9XG5cblx0XHRcdHdpbmRvdy53cC5hcGlGZXRjaCgge1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXRjZG4vbW9kZScsXG5cdFx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0XHRkYXRhOiB7IG1vZGU6IHJlcXVlc3RlZE1vZGUgfSxcblx0XHRcdH0gKS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0XHR1cGRhdGVSb2NrZXRDRE5FbGVtZW50c1N0YXRlKFxuXHRcdFx0XHRcdCdieW9jZG4nID09PSBtb2RlID8gJ2J5b2NkbicgOiAncm9ja2V0Y2RuJyxcblx0XHRcdFx0XHRyZXNwb25zZS5kaXNhYmxlX3JvY2tldF9jZG5fZWxlbWVudHNcblx0XHRcdFx0KTtcblx0XHRcdFx0c3luY0NkbkhpZGRlbklucHV0cyggcmVxdWVzdGVkTW9kZSApO1xuXHRcdFx0XHRyZWZyZXNoVUlFbGVtZW50cyggcmVzcG9uc2UgKTtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5pc19zdWJzY3JpcHRpb25fY3JlYXRpb25fbG9hZGluZyApIHtcblx0XHRcdFx0XHQvLyBMb2FkaW5nIHN0YXRlIGNvbmZpcm1lZCDigJQgcG9sbGVyIHN0YXJ0ZWQgYnkgcmVmcmVzaFVJRWxlbWVudHMuXG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0aWYgKCAncm9ja2V0Y2RuX2ZyZWUnID09PSByZXF1ZXN0ZWRNb2RlICkge1xuXHRcdFx0XHRcdFx0cmV2ZXJ0U3Vic2NyaXB0aW9uTG9hZGluZ1N0YXRlKCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHRvZ2dsZS5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0XHR9XG5cdFx0XHR9ICkuY2F0Y2goICgpID0+IHtcblx0XHRcdFx0Ly8gUmV2ZXJ0IHRvIHByZXZpb3VzIHN0YXRlIG9uIGZhaWx1cmUuXG5cdFx0XHRcdHRvZ2dsZS5jaGVja2VkID0gISB0b2dnbGUuY2hlY2tlZDtcblx0XHRcdFx0dG9nZ2xlLmRpc2FibGVkID0gZmFsc2U7XG5cdFx0XHRcdGlmICggJ3JvY2tldGNkbl9mcmVlJyA9PT0gcmVxdWVzdGVkTW9kZSApIHtcblx0XHRcdFx0XHRyZXZlcnRTdWJzY3JpcHRpb25Mb2FkaW5nU3RhdGUoKTtcblx0XHRcdFx0fVxuXHRcdFx0XHR1cGRhdGVDZG5BY3RpdmVJbmRpY2F0b3IoIHByZXZpb3VzbHlBY3RpdmUgKTtcblxuXHRcdFx0XHRpZiAoIHByZXZpb3VzbHlBY3RpdmUgJiYgcHJldmlvdXNseUFjdGl2ZSAhPT0gdG9nZ2xlICkge1xuXHRcdFx0XHRcdHByZXZpb3VzbHlBY3RpdmUuY2hlY2tlZCA9IHRydWU7XG5cdFx0XHRcdFx0Y29uc3QgcHJldk1vZGUgICA9IHByZXZpb3VzbHlBY3RpdmUuZ2V0QXR0cmlidXRlKCAnZGF0YS1jZG4tbW9kZScgKTtcblx0XHRcdFx0XHRjb25zdCBwcmV2RHJpdmVyID0gJ2J5b2NkbicgPT09IHByZXZNb2RlID8gJ3lvdXItb3duLWNkbicgOiAncm9ja2V0Y2RuJztcblx0XHRcdFx0XHR0b2dnbGVEcml2ZXJTZWN0aW9ucyggcHJldkRyaXZlciApO1xuXHRcdFx0XHR9XG5cdFx0XHR9ICk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRvZ2dsZXMgdmlzaWJpbGl0eSBvZiBDRE4gZHJpdmVyIHNlY3Rpb25zIHVzaW5nIHRoZSBoaWRkZW4gdXRpbGl0eSBjbGFzcy5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IGFjdGl2ZURyaXZlciBBY3RpdmUgQ0ROIGRyaXZlciBzbHVnICgncm9ja2V0Y2RuJyBvciAneW91ci1vd24tY2RuJykuXG5cdCAqL1xuXHRmdW5jdGlvbiB0b2dnbGVEcml2ZXJTZWN0aW9ucyggYWN0aXZlRHJpdmVyICkge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcucm9ja2V0Y2RuLCAueW91ci1vd24tY2RuJyApLmZvckVhY2goICggc2VjdGlvbiApID0+IHtcblx0XHRcdHNlY3Rpb24uY2xhc3NMaXN0LnRvZ2dsZSggJ3dwci1pc0hpZGRlbicsICEgc2VjdGlvbi5jbGFzc0xpc3QuY29udGFpbnMoIGFjdGl2ZURyaXZlciApICk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFVwZGF0ZXMgYWxsIC5yb2NrZXRjZG4tZHJpdmVyLWpzIHNwYW5zIHRvIHJlZmxlY3QgdGhlIGFjdGl2ZSBkcml2ZXIgbGFiZWwuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBkcml2ZXIgQWN0aXZlIENETiBkcml2ZXIgc2x1ZyAoJ3JvY2tldGNkbicgb3IgJ3lvdXItb3duLWNkbicpLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlRHJpdmVyTGFiZWwoIGRyaXZlciApIHtcblx0XHRjb25zdCB0YWIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCBgLndwci1jZG4tdGFic19fdGFiW2RhdGEtY2RuLWRyaXZlcj1cIiR7ZHJpdmVyfVwiXWAgKTtcblxuXHRcdGlmICggISB0YWIgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Y29uc3QgbGFiZWwgPSB0YWIuZ2V0QXR0cmlidXRlKCAnZGF0YS10aXRsZScgKTtcblxuXHRcdGlmICggISBsYWJlbCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLnJvY2tldGNkbi1kcml2ZXItanMnICkuZm9yRWFjaCggKCBzcGFuICkgPT4ge1xuXHRcdFx0c3Bhbi50ZXh0Q29udGVudCA9IGxhYmVsO1xuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBcIk5lZWQgSGVscD9cIiBsaW5rIGhyZWYgZm9yIHRoZSBDRE4gRXhjbHVzaW9ucyBzZWN0aW9uXG5cdCAqIHRvIHBvaW50IHRvIHRoZSBjb3JyZWN0IGRvY3MgYXJ0aWNsZSBmb3IgdGhlIGFjdGl2ZSBkcml2ZXIuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBkcml2ZXIgQWN0aXZlIENETiBkcml2ZXIgc2x1ZyAoJ3JvY2tldGNkbicgb3IgJ3lvdXItb3duLWNkbicpLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlRXhjbHVkZUNkbkhlbHBVcmwoIGRyaXZlciApIHtcblx0XHRjb25zdCBsaW5rID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy5leGNsdWRlLWNkbi1oZWxwLWpzJyApO1xuXG5cdFx0aWYgKCAhIGxpbmsgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Y29uc3QgaXNSb2NrZXRDZG4gPSAncm9ja2V0Y2RuJyA9PT0gZHJpdmVyO1xuXHRcdGNvbnN0IHVybCA9IGlzUm9ja2V0Q2RuID8gbGluay5kYXRhc2V0LnJvY2tldGNkblVybCA6IGxpbmsuZGF0YXNldC5vdGhlckNkblVybDtcblx0XHRjb25zdCBpZCAgPSBpc1JvY2tldENkbiA/IGxpbmsuZGF0YXNldC5yb2NrZXRjZG5JZCAgOiBsaW5rLmRhdGFzZXQub3RoZXJDZG5JZDtcblxuXHRcdGlmICggdXJsICkge1xuXHRcdFx0bGluay5ocmVmID0gdXJsO1xuXHRcdH1cblxuXHRcdGlmICggaWQgKSB7XG5cdFx0XHRsaW5rLmRhdGFzZXQuYmVhY29uSWQgPSBpZDtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogU2V0cyB0aGUgYWN0aXZlIGRyaXZlciB0YWIgYW5kIHN5bmNzIGFsbCB0YWItZGVwZW5kZW50IFVJIChsYWJlbCBzcGFucywgaGVscCBVUkwpLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gZHJpdmVyIEFjdGl2ZSBDRE4gZHJpdmVyIHNsdWcgKCdyb2NrZXRjZG4nIG9yICd5b3VyLW93bi1jZG4nKS5cblx0ICovXG5cdGZ1bmN0aW9uIHNldEFjdGl2ZVRhYiggZHJpdmVyICkge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLWNkbi10YWJzX190YWInICkuZm9yRWFjaCggKCB0ICkgPT4ge1xuXHRcdFx0dC5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLWNkbi10YWJzX190YWItLWFjdGl2ZScsIHQuZ2V0QXR0cmlidXRlKCAnZGF0YS1jZG4tZHJpdmVyJyApID09PSBkcml2ZXIgKTtcblx0XHR9ICk7XG5cblx0XHR1cGRhdGVEcml2ZXJMYWJlbCggZHJpdmVyICk7XG5cdFx0dXBkYXRlRXhjbHVkZUNkbkhlbHBVcmwoIGRyaXZlciApO1xuXHR9XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIENETiBkcml2ZXIgdGFiIHN3aXRjaGluZyBiZWhhdmlvci5cblx0ICpcblx0ICogVGFicyBhcmUgbmF2aWdhdGlvbiBvbmx5IOKAlCBubyBiYWNrZW5kIGNhbGwgb24gY2xpY2suXG5cdCAqIEluaXRpYWwgZHJpdmVyIGlzIGRlcml2ZWQgZnJvbSB0aGUgUEhQLXJlbmRlcmVkIGFjdGl2ZS1zdGF0ZSBoZWFkZXJcblx0ICogKHdwci1jZG4tYWN0aXZlLWluZGljYXRvciksIHdoaWNoIHJlZmxlY3RzIGNkbl9zdGF0ZSBldmVuIHdoZW4gdGhlIG1vZGVcblx0ICogdG9nZ2xlIGl0c2VsZiBpcyBoaWRkZW4gKGUuZy4gcm9ja2V0X2Rpc3BsYXlfY2RuX21vZGVfdG9nZ2xlIHJldHVybmluZ1xuXHQgKiBmYWxzZSBmb3IgYSBob3N0aW5nIGNvbXBhdGliaWxpdHkgbGF5ZXIpLiBGYWxscyBiYWNrIHRvIHdoaWNoZXZlclxuXHQgKiB0b2dnbGUgaXMgY2hlY2tlZCB3aGVuIG5vIGhlYWRlciBpcyBtYXJrZWQgYWN0aXZlIChlLmcuIGNkbl9zdGF0ZSBpc1xuXHQgKiAnbm90aGluZycpLlxuXHQgKi9cblx0ZnVuY3Rpb24gaW5pdENkbkRyaXZlclRhYnMoKSB7XG5cdFx0Y29uc3QgdGFicyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLWNkbi10YWJzX190YWInICk7XG5cblx0XHRpZiAoICEgdGFicy5sZW5ndGggKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGFicy5mb3JFYWNoKCAoIHRhYiApID0+IHtcblx0XHRcdHRhYi5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoKSA9PiB7XG5cdFx0XHRcdGNvbnN0IGRyaXZlciA9IHRhYi5nZXRBdHRyaWJ1dGUoICdkYXRhLWNkbi1kcml2ZXInICk7XG5cblx0XHRcdFx0aWYgKCAhIGRyaXZlciApIHtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRzZXRBY3RpdmVUYWIoIGRyaXZlciApO1xuXHRcdFx0XHR0b2dnbGVEcml2ZXJTZWN0aW9ucyggZHJpdmVyICk7XG5cdFx0XHRcdG5vdGlmeUNkblN0YXRlQ2hhbmdlKCk7XG5cdFx0XHR9ICk7XG5cdFx0fSApO1xuXG5cdFx0Y29uc3QgYWN0aXZlSGVhZGVyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItb3B0aW9uSGVhZGVyLndwci1jZG4tYWN0aXZlLWluZGljYXRvcicgKTtcblx0XHRsZXQgaW5pdGlhbERyaXZlcjtcblxuXHRcdGlmICggYWN0aXZlSGVhZGVyICkge1xuXHRcdFx0aW5pdGlhbERyaXZlciA9IGFjdGl2ZUhlYWRlci5jbGFzc0xpc3QuY29udGFpbnMoICd5b3VyLW93bi1jZG4nICkgPyAneW91ci1vd24tY2RuJyA6ICdyb2NrZXRjZG4nO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRjb25zdCBjaGVja2VkVG9nZ2xlID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLW1vZGUtdG9nZ2xlX19pbnB1dDpjaGVja2VkJyApO1xuXHRcdFx0aW5pdGlhbERyaXZlciA9IGNoZWNrZWRUb2dnbGUgJiYgJ2J5b2NkbicgPT09IGNoZWNrZWRUb2dnbGUuZ2V0QXR0cmlidXRlKCAnZGF0YS1jZG4tbW9kZScgKVxuXHRcdFx0XHQ/ICd5b3VyLW93bi1jZG4nXG5cdFx0XHRcdDogJ3JvY2tldGNkbic7XG5cdFx0fVxuXG5cdFx0c2V0QWN0aXZlVGFiKCBpbml0aWFsRHJpdmVyICk7XG5cdFx0dG9nZ2xlRHJpdmVyU2VjdGlvbnMoIGluaXRpYWxEcml2ZXIgKTtcblx0XHRub3RpZnlDZG5TdGF0ZUNoYW5nZSgpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEFkZHMgYSBwYWdlIChvciB0aGUgaG9tZXBhZ2UpIHRvIFJvY2tldENETiBmcmVlLXRpZXIgZGVsaXZlcnksIHRyYW5zcGFyZW50bHlcblx0ICogaGFuZGxpbmcgdGhlIFwiUm9ja2V0Q0ROIEZyZWUgaXMgaW5hY3RpdmVcIiBhY3RpdmF0aW9uIHByb21wdDogb24gYSA0MDlcblx0ICogY29uZmlybS1yZXF1aXJlZCBlcnJvciwgc2hvd3MgYSBuYXRpdmUgY29uZmlybWF0aW9uIGRpYWxvZyBhbmQgcmV0cmllcyB3aXRoXG5cdCAqIGBjb25maXJtX2FjdGl2YXRpb25gIGlmIHRoZSB1c2VyIGFjY2VwdHMuIElmIHRoZSBzZXJ2ZXIgYXV0by1hY3RpdmF0ZWQgRnJlZVxuXHQgKiAobm8gbW9kZSB3YXMgYWN0aXZlIGF0IGFsbCksIHVwZGF0ZXMgdGhlIHRvZ2dsZSBVSSB0byByZWZsZWN0IGl0LlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gcGF0aCBSRVNUIHBhdGggdG8gY2FsbCAoJy93cC1yb2NrZXQvdjEvcm9ja2V0Y2RuL3BhZ2VzJyBvciAnLi4uL3BhZ2VzL2hvbWVwYWdlJykuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBkYXRhIFJlcXVlc3QgYm9keSBkYXRhIChlLmcuIHsgdXJsIH0pLlxuXHQgKiBAcmV0dXJucyB7UHJvbWlzZX0gUmVzb2x2ZXMgd2l0aCB0aGUgUkVTVCByZXNwb25zZTsgcmVqZWN0cyBvbiBmaW5hbCBmYWlsdXJlIG9yIGNhbmNlbGxhdGlvbi5cblx0ICovXG5cdGZ1bmN0aW9uIHJlcXVlc3RBZGRQYWdlKCBwYXRoLCBkYXRhICkge1xuXHRcdHJldHVybiB3aW5kb3cud3AuYXBpRmV0Y2goIHtcblx0XHRcdHBhdGgsXG5cdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdGRhdGEsXG5cdFx0fSApLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAoIHJlc3BvbnNlLmZyZWVfYWN0aXZhdGVkICkge1xuXHRcdFx0XHRhY3RpdmF0ZUZyZWVNb2RlVUkoKTtcblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuIHJlc3BvbnNlO1xuXHRcdH0gKS5jYXRjaCggKCBlcnJvciApID0+IHtcblx0XHRcdGlmICggJ3JvY2tldGNkbl9mcmVlX2luYWN0aXZlX2NvbmZpcm1fcmVxdWlyZWQnID09PSBlcnJvci5jb2RlICYmIHdpbmRvdy5jb25maXJtKCBlcnJvci5tZXNzYWdlICkgKSB7XG5cdFx0XHRcdHJldHVybiByZXF1ZXN0QWRkUGFnZSggcGF0aCwgT2JqZWN0LmFzc2lnbigge30sIGRhdGEsIHsgY29uZmlybV9hY3RpdmF0aW9uOiB0cnVlIH0gKSApO1xuXHRcdFx0fVxuXG5cdFx0XHR0aHJvdyBlcnJvcjtcblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZXMgdGhlIFwiQUREIEhPTUVQQUdFXCIgYnV0dG9uLlxuXHQgKlxuXHQgKiBTZW5kcyBhIFBPU1QgcmVxdWVzdCB0byB0aGUgUm9ja2V0Q0ROIFJFU1QgZW5kcG9pbnQgdG8gYWRkXG5cdCAqIHRoZSBzaXRlIGhvbWVwYWdlIGFzIGEgZnJlZS10aWVyIENETiBwYWdlLlxuXHQgKi9cblx0ZnVuY3Rpb24gaW5pdEFkZEhvbWVwYWdlKCkge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZXZlbnQgKSA9PiB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBldmVudC50YXJnZXQuY2xvc2VzdCggJyN3cHJfYWRkX3BhZ2VfY29tcG9uZW50IC53cHItY2RuLWFkZC1wYWdlX19ob21lcGFnZScgKTtcblx0XHRcdGlmICggISBidXR0b24gKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YnV0dG9uLmRpc2FibGVkID0gdHJ1ZTtcblxuXHRcdFx0Y29uc3QgYnVpbHRJbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKTtcblxuXHRcdFx0aWYgKCBidWlsdEluICkge1xuXHRcdFx0XHRidWlsdEluLmNsYXNzTGlzdC5hZGQoICd3cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcgKTtcblx0XHRcdH1cblxuXHRcdFx0cmVxdWVzdEFkZFBhZ2UoICcvd3Atcm9ja2V0L3YxL3JvY2tldGNkbi9wYWdlcy9ob21lcGFnZScsIHt9ICkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdFx0YnV0dG9uLmNsYXNzTGlzdC5hZGQoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdHVwZGF0ZVJvY2tldEN0YVN0YXRlKCByZXNwb25zZS5jb3VudCwgcmVzcG9uc2UubGltaXQgKTtcblxuXHRcdFx0XHRpZiAoIGJ1aWx0SW4gKSB7XG5cdFx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLml0ZW1zX2h0bWwgKSB7XG5cdFx0XHRcdFx0Y29uc3QgZXhpc3RpbmcgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYnVpbHQtaW4gLndwci10YWJsZS1saXN0JyApO1xuXG5cdFx0XHRcdFx0aWYgKCBleGlzdGluZyApIHtcblx0XHRcdFx0XHRcdGV4aXN0aW5nLnJlbW92ZSgpO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdGNvbnN0IGFkZFBhZ2VTZWN0aW9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlJyApO1xuXG5cdFx0XHRcdFx0aWYgKCBhZGRQYWdlU2VjdGlvbiApIHtcblx0XHRcdFx0XHRcdGFkZFBhZ2VTZWN0aW9uLmluc2VydEFkamFjZW50SFRNTCggJ2JlZm9yZWJlZ2luJywgcmVzcG9uc2UuaXRlbXNfaHRtbCApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFRyYWNrIGJhbm5lciB2aWV3IHdoZW4gZmlyc3QgcGFnZSBpcyBhZGRlZCBhbmQgYmFubmVyIGJlY29tZXMgdmlzaWJsZS5cblx0XHRcdFx0aWYgKCAxID09PSByZXNwb25zZS5jb3VudCApIHtcblx0XHRcdFx0XHRkb2N1bWVudC5kaXNwYXRjaEV2ZW50KCBuZXcgQ3VzdG9tRXZlbnQoICdyb2NrZXRDRE5CYW5uZXJGaXJzdFZpc2libGUnICkgKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHJlZnJlc2hVSUVsZW1lbnRzKHJlc3BvbnNlKTtcblx0XHRcdH0gKS5jYXRjaCggKCkgPT4ge1xuXHRcdFx0XHRidXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcblxuXHRcdFx0XHRpZiAoIGJ1aWx0SW4gKSB7XG5cdFx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cdH1cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIHRoZSBcIkFERCBQQUdFXCIgaW5wdXQgYW5kIGJ1dHRvbi5cblx0ICpcblx0ICogU2VuZHMgYSBQT1NUIHJlcXVlc3QgdG8gdGhlIFJvY2tldENETiBSRVNUIGVuZHBvaW50IHRvIGFkZFxuXHQgKiBhIHBhZ2UgVVJMIHRvIHRoZSBmcmVlLXRpZXIgQ0ROIHBhZ2UgbGlzdC5cblx0ICovXG5cdGZ1bmN0aW9uIGluaXRBZGRQYWdlKCkge1xuXHRcdGNvbnN0IGlucHV0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICd3cHJfY2RuX2FkZF9wYWdlX2lucHV0JyApO1xuXHRcdGNvbnN0IGJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1hZGQtcGFnZV9fYnV0dG9uJyApO1xuXG5cdFx0aWYgKCAhIGlucHV0IHx8ICEgYnV0dG9uICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGZ1bmN0aW9uIGlzVmFsaWRVcmwoaW5wdXQpIHtcblx0XHRcdHRyeSB7XG5cdFx0XHRcdGNvbnN0IHVybCA9IG5ldyBVUkwoaW5wdXQpO1xuXHRcdFx0XHRyZXR1cm4gdXJsLmhvc3RuYW1lLmluY2x1ZGVzKCcuJykgJiYgdXJsLmhvc3RuYW1lLnNwbGl0KCcuJykucG9wKCkubGVuZ3RoID4gMDtcblx0XHRcdH0gY2F0Y2gge1xuXHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0ZnVuY3Rpb24gc3VibWl0UGFnZSgpIHtcblx0XHRcdGNvbnN0IHVybCA9IGlucHV0LnZhbHVlLnRyaW0oKTtcblxuXHRcdFx0aWYgKCFpc1ZhbGlkVXJsKHVybCkpIHtcblx0XHRcdFx0YWxlcnQoJ1BsZWFzZSBlbnRlciBhIHZhbGlkIFVSTCcpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFByZXZlbnQgZHVwbGljYXRlIHJlcXVlc3Qgd2hpbGUgcmVxdWVzdCBpcyBpbiBmbGlnaHQuXG5cdFx0XHRpbnB1dC5kaXNhYmxlZCA9IHRydWU7XG5cdFx0XHRidXR0b24uZGlzYWJsZWQgPSB0cnVlO1xuXHRcdFx0Y29uc3QgYnVpbHRJbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKTtcblxuXHRcdFx0aWYgKCBidWlsdEluICkge1xuXHRcdFx0XHRidWlsdEluLmNsYXNzTGlzdC5hZGQoICd3cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcgKTtcblx0XHRcdH1cblxuXHRcdFx0cmVxdWVzdEFkZFBhZ2UoICcvd3Atcm9ja2V0L3YxL3JvY2tldGNkbi9wYWdlcycsIHsgdXJsIH0gKS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0XHRpbnB1dC52YWx1ZSA9ICcnO1xuXHRcdFx0XHRpbnB1dC5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0XHRidXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcblx0XHRcdFx0YWRkSG9tZUJ1dHRvbi5jbGFzc0xpc3QuYWRkKCAnd3ByLWlzSGlkZGVuJyApO1xuXG5cdFx0XHRcdHVwZGF0ZVJvY2tldEN0YVN0YXRlKCByZXNwb25zZS5jb3VudCwgcmVzcG9uc2UubGltaXQgKTtcblxuXHRcdFx0XHRpZiAoIGJ1aWx0SW4gKSB7XG5cdFx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBVcGRhdGUgcGFnZSBsaXN0IHdpdGggcmVzcG9uc2UuXG5cdFx0XHRcdGlmICggcmVzcG9uc2UuaXRlbXNfaHRtbCApIHtcblx0XHRcdFx0XHRjb25zdCBleGlzdGluZyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbiAud3ByLXRhYmxlLWxpc3QnICk7XG5cblx0XHRcdFx0XHRpZiAoIGV4aXN0aW5nICkge1xuXHRcdFx0XHRcdFx0ZXhpc3RpbmcucmVtb3ZlKCk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVNlY3Rpb24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2UnICk7XG5cblx0XHRcdFx0XHRpZiAoIGFkZFBhZ2VTZWN0aW9uICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZVNlY3Rpb24uaW5zZXJ0QWRqYWNlbnRIVE1MKCAnYmVmb3JlYmVnaW4nLCByZXNwb25zZS5pdGVtc19odG1sICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVHJhY2sgYmFubmVyIHZpZXcgd2hlbiBmaXJzdCBwYWdlIGlzIGFkZGVkIGFuZCBiYW5uZXIgYmVjb21lcyB2aXNpYmxlLlxuXHRcdFx0XHRpZiAoIDEgPT09IHJlc3BvbnNlLmNvdW50ICkge1xuXHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQoIG5ldyBDdXN0b21FdmVudCggJ3JvY2tldENETkJhbm5lckZpcnN0VmlzaWJsZScgKSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0aWYgKCByZXNwb25zZS5saW1pdCA9PT0gcmVzcG9uc2UuY291bnQgKSB7XG5cdFx0XHRcdFx0Ly8gRGlzYWJsZSBpbnB1dCBhbmQgYnV0dG9uIHdoZW4gcGFnZSBsaW1pdCBpcyByZWFjaGVkLlxuXHRcdFx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKS5jbGFzc0xpc3QuYWRkKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVdyYXBwZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2VfX2J1dHRvbi13cmFwcGVyJyApO1xuXHRcdFx0XHRcdGlmICggYWRkUGFnZVdyYXBwZXIgKSB7XG5cdFx0XHRcdFx0XHRhZGRQYWdlV3JhcHBlci5jbGFzc0xpc3QuYWRkKCAnd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRjb25zdCBhZGRQYWdlQnRuID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19idXR0b24nICk7XG5cdFx0XHRcdFx0aWYgKCBhZGRQYWdlQnRuICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZUJ0bi5kaXNhYmxlZCA9IHRydWU7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHVwZGF0ZVRvb2x0aXBTdGF0ZSggdHJ1ZSApO1xuXHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQoIG5ldyBDdXN0b21FdmVudCggJ3JvY2tldENETkJhbm5lckF1dG9FeHBhbmRlZCcgKSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0cmVmcmVzaFVJRWxlbWVudHMocmVzcG9uc2UpO1xuXHRcdFx0fSApLmNhdGNoKCAoKSA9PiB7XG5cdFx0XHRcdGlucHV0LmRpc2FibGVkID0gZmFsc2U7XG5cdFx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuXG5cdFx0XHRcdGlmICggYnVpbHRJbiApIHtcblx0XHRcdFx0XHRidWlsdEluLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcgKTtcblx0XHRcdFx0fVxuXHRcdFx0fSApO1xuXHRcdH1cblxuXHRcdGJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCBzdWJtaXRQYWdlICk7XG5cblx0XHRpbnB1dC5hZGRFdmVudExpc3RlbmVyKCAna2V5ZG93bicsICggZSApID0+IHtcblx0XHRcdGlmICggJ0VudGVyJyA9PT0gZS5rZXkgKSB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdFx0c3VibWl0UGFnZSgpO1xuXHRcdFx0fVxuXHRcdH0gKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHJlZnJlc2hVSUVsZW1lbnRzKCByZXNwb25zZSApIHtcblx0XHQvLyBTZXQgc3Vic2NyaXB0aW9uIGxvYWRpbmcgc3RhdGUgd2hlbiBmaXJzdCBwYWdlIGlzIGFkZGVkLlxuXHRcdGlmICggcmVzcG9uc2UuaXNfc3Vic2NyaXB0aW9uX2NyZWF0aW9uX2xvYWRpbmcgKSB7XG5cdFx0XHRzZXRTdWJzY3JpcHRpb25Mb2FkaW5nU3RhdGUoKTtcblx0XHR9XG5cblx0XHQvLyBVcGRhdGUgc3RhdHVzIGluZGljYXRvciBjb21wb25lbnQuXG5cdFx0dXBkYXRlU3RhdHVzSW5kaWNhdG9yQ29tcG9uZW50KCByZXNwb25zZS5zdGF0dXNfaW5kaWNhdG9yX2h0bWwgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplcyBkZWxldGUgYnV0dG9ucyBmb3IgQ0ROIHBhZ2Ugcm93cy5cblx0ICpcblx0ICogVXNlcyBldmVudCBkZWxlZ2F0aW9uIG9uIHRoZSBidWlsdC1pbiBDRE4gY29udGFpbmVyIHRvIGhhbmRsZVxuXHQgKiBjbGlja3Mgb24gZHluYW1pY2FsbHkgYWRkZWQgZGVsZXRlIGJ1dHRvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0RGVsZXRlUGFnZSgpIHtcblx0XHRjb25zdCBjb250YWluZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwcl9hZGRfcGFnZV9jb21wb25lbnQnICk7XG5cblx0XHRpZiAoICEgY29udGFpbmVyICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnRhaW5lci5wYXJlbnRFbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGUudGFyZ2V0LmNsb3Nlc3QoICcud3ByLXRhYmxlLWxpc3RfX2RlbGV0ZScgKTtcblxuXHRcdFx0aWYgKCAhIGJ1dHRvbiApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBpZCA9IGJ1dHRvbi5kYXRhc2V0LmlkO1xuXG5cdFx0XHRpZiAoICEgaWQgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YnV0dG9uLmRpc2FibGVkID0gdHJ1ZTtcblxuXHRcdFx0d2luZG93LndwLmFwaUZldGNoKCB7XG5cdFx0XHRcdHBhdGg6IGAvd3Atcm9ja2V0L3YxL3JvY2tldGNkbi9wYWdlcy8keyBpZCB9YCxcblx0XHRcdFx0bWV0aG9kOiAnREVMRVRFJyxcblx0XHRcdH0gKS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0XHR1cGRhdGVSb2NrZXRDdGFTdGF0ZSggcmVzcG9uc2UuY291bnQsIHJlc3BvbnNlLmxpbWl0ICk7XG5cblx0XHRcdFx0aWYgKCByZXNwb25zZS5pdGVtc19odG1sICkge1xuXHRcdFx0XHRcdGNvbnN0IGV4aXN0aW5nID0gY29udGFpbmVyLnBhcmVudEVsZW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWJ1aWx0LWluIC53cHItdGFibGUtbGlzdCcgKTtcblxuXHRcdFx0XHRcdGlmICggZXhpc3RpbmcgKSB7XG5cdFx0XHRcdFx0XHRleGlzdGluZy5yZW1vdmUoKTtcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRjb25zdCBhZGRQYWdlU2VjdGlvbiA9IGNvbnRhaW5lci5wYXJlbnRFbGVtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1hZGQtcGFnZScgKTtcblxuXHRcdFx0XHRcdGlmICggYWRkUGFnZVNlY3Rpb24gKSB7XG5cdFx0XHRcdFx0XHRhZGRQYWdlU2VjdGlvbi5pbnNlcnRBZGphY2VudEhUTUwoICdiZWZvcmViZWdpbicsIHJlc3BvbnNlLml0ZW1zX2h0bWwgKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBTaG93IHJlLWFkZCBIT01FUEFHRSBidXR0b24gYW5kIG5vLXBhZ2Ugbm90aWNlIHdoZW4gYWxsIHBhZ2VzIGFyZSBkZWxldGVkLlxuXHRcdFx0XHRpZiAoIDAgPT09IHJlc3BvbnNlLmNvdW50ICkge1xuXHRcdFx0XHRcdC8vIFJlbW92ZSB0YWJsZSBsaXN0IGNvbXBvbmVudC5cblx0XHRcdFx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYnVpbHQtaW4gLndwci10YWJsZS1saXN0JyApLnJlbW92ZSgpO1xuXG5cdFx0XHRcdFx0Y29uc3QgaG9tZXBhZ2VCdG4gPSBjb250YWluZXIucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19ob21lcGFnZScgKTtcblxuXHRcdFx0XHRcdGlmICggaG9tZXBhZ2VCdG4gKSB7XG5cdFx0XHRcdFx0XHRob21lcGFnZUJ0bi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWlzSGlkZGVuJyApO1xuXHRcdFx0XHRcdFx0aG9tZXBhZ2VCdG4uZGlzYWJsZWQgPSBmYWxzZTtcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0fVxuXG5cdFx0XHRcdGlmICggcmVzcG9uc2UubGltaXQgPiByZXNwb25zZS5jb3VudCApIHtcblx0XHRcdFx0XHQvLyBSZS1lbmFibGUgaW5wdXQgYW5kIGJ1dHRvbiB3aGVuIHBhZ2UgbGltaXQgaXMgbm90IHJlYWNoZWQuXG5cdFx0XHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWJ1aWx0LWluJyApLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcgKTtcblx0XHRcdFx0XHRjb25zdCBhZGRQYWdlV3JhcHBlciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1hZGQtcGFnZV9fYnV0dG9uLXdyYXBwZXInICk7XG5cdFx0XHRcdFx0aWYgKCBhZGRQYWdlV3JhcHBlciApIHtcblx0XHRcdFx0XHRcdGFkZFBhZ2VXcmFwcGVyLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItYnRuLXdpdGgtdG9vbC10aXAnICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdGNvbnN0IGFkZFBhZ2VCdG4gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2VfX2J1dHRvbicgKTtcblx0XHRcdFx0XHRpZiAoIGFkZFBhZ2VCdG4gKSB7XG5cdFx0XHRcdFx0XHRhZGRQYWdlQnRuLmRpc2FibGVkID0gZmFsc2U7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHVwZGF0ZVRvb2x0aXBTdGF0ZSggZmFsc2UgKTtcblxuXHRcdFx0XHRcdC8vIFRyYWNrIGF1dG8tY29sbGFwc2Ugd2hlbiBkZWxldGlvbiBkcm9wcyBjb3VudCBqdXN0IGJlbG93IHRoZSBsaW1pdC5cblx0XHRcdFx0XHRpZiAoIHJlc3BvbnNlLmNvdW50ID09PSByZXNwb25zZS5saW1pdCAtIDEgKSB7XG5cdFx0XHRcdFx0XHRkb2N1bWVudC5kaXNwYXRjaEV2ZW50KCBuZXcgQ3VzdG9tRXZlbnQoICdyb2NrZXRDRE5CYW5uZXJBdXRvQ29sbGFwc2VkJyApICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVXBkYXRlIHN0YXR1cyBpbmRpY2F0b3IgY29tcG9uZW50LlxuXHRcdFx0XHR1cGRhdGVTdGF0dXNJbmRpY2F0b3JDb21wb25lbnQoIHJlc3BvbnNlLnN0YXR1c19pbmRpY2F0b3JfaHRtbCApO1xuXG5cdFx0XHR9ICkuY2F0Y2goICgpID0+IHtcblx0XHRcdFx0YnV0dG9uLmRpc2FibGVkID0gZmFsc2U7XG5cdFx0XHR9ICk7XG5cdFx0fSApO1xuXHR9XG59ICkoIGRvY3VtZW50ICk7XG4iLCJmdW5jdGlvbiBnZXRUaW1lUmVtYWluaW5nKGVuZHRpbWUpe1xuICAgIGNvbnN0IHN0YXJ0ID0gRGF0ZS5ub3coKTtcbiAgICBjb25zdCB0b3RhbCA9IChlbmR0aW1lICogMTAwMCkgLSBzdGFydDtcbiAgICBjb25zdCBzZWNvbmRzID0gTWF0aC5mbG9vciggKHRvdGFsLzEwMDApICUgNjAgKTtcbiAgICBjb25zdCBtaW51dGVzID0gTWF0aC5mbG9vciggKHRvdGFsLzEwMDAvNjApICUgNjAgKTtcbiAgICBjb25zdCBob3VycyA9IE1hdGguZmxvb3IoICh0b3RhbC8oMTAwMCo2MCo2MCkpICUgMjQgKTtcbiAgICBjb25zdCBkYXlzID0gTWF0aC5mbG9vciggdG90YWwvKDEwMDAqNjAqNjAqMjQpICk7XG5cbiAgICByZXR1cm4ge1xuICAgICAgICB0b3RhbCxcbiAgICAgICAgZGF5cyxcbiAgICAgICAgaG91cnMsXG4gICAgICAgIG1pbnV0ZXMsXG4gICAgICAgIHNlY29uZHNcbiAgICB9O1xufVxuXG5mdW5jdGlvbiBpbml0aWFsaXplQ2xvY2soaWQsIGVuZHRpbWUpIHtcbiAgICBjb25zdCBjbG9jayA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGlkKTtcblxuICAgIGlmIChjbG9jayA9PT0gbnVsbCkge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3QgZGF5c1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1kYXlzJyk7XG4gICAgY29uc3QgaG91cnNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24taG91cnMnKTtcbiAgICBjb25zdCBtaW51dGVzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLW1pbnV0ZXMnKTtcbiAgICBjb25zdCBzZWNvbmRzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLXNlY29uZHMnKTtcblxuICAgIGZ1bmN0aW9uIHVwZGF0ZUNsb2NrKCkge1xuICAgICAgICBjb25zdCB0ID0gZ2V0VGltZVJlbWFpbmluZyhlbmR0aW1lKTtcblxuICAgICAgICBpZiAodC50b3RhbCA8IDApIHtcbiAgICAgICAgICAgIGNsZWFySW50ZXJ2YWwodGltZWludGVydmFsKTtcblxuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgZGF5c1NwYW4uaW5uZXJIVE1MID0gdC5kYXlzO1xuICAgICAgICBob3Vyc1NwYW4uaW5uZXJIVE1MID0gKCcwJyArIHQuaG91cnMpLnNsaWNlKC0yKTtcbiAgICAgICAgbWludXRlc1NwYW4uaW5uZXJIVE1MID0gKCcwJyArIHQubWludXRlcykuc2xpY2UoLTIpO1xuICAgICAgICBzZWNvbmRzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5zZWNvbmRzKS5zbGljZSgtMik7XG4gICAgfVxuXG4gICAgdXBkYXRlQ2xvY2soKTtcbiAgICBjb25zdCB0aW1laW50ZXJ2YWwgPSBzZXRJbnRlcnZhbCh1cGRhdGVDbG9jaywgMTAwMCk7XG59XG5cbmZ1bmN0aW9uIHJ1Y3NzVGltZXIoaWQsIGVuZHRpbWUpIHtcblx0Y29uc3QgdGltZXIgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG5cdGNvbnN0IG5vdGljZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyb2NrZXQtbm90aWNlLXNhYXMtcHJvY2Vzc2luZycpO1xuXHRjb25zdCBzdWNjZXNzID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JvY2tldC1ub3RpY2Utc2Fhcy1zdWNjZXNzJyk7XG5cblx0aWYgKHRpbWVyID09PSBudWxsKSB7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0ZnVuY3Rpb24gdXBkYXRlVGltZXIoKSB7XG5cdFx0Y29uc3Qgc3RhcnQgPSBEYXRlLm5vdygpO1xuXHRcdGNvbnN0IHJlbWFpbmluZyA9IE1hdGguZmxvb3IoICggKGVuZHRpbWUgKiAxMDAwKSAtIHN0YXJ0ICkgLyAxMDAwICk7XG5cblx0XHRpZiAocmVtYWluaW5nIDw9IDApIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwodGltZXJJbnRlcnZhbCk7XG5cblx0XHRcdGlmIChub3RpY2UgIT09IG51bGwpIHtcblx0XHRcdFx0bm90aWNlLmNsYXNzTGlzdC5hZGQoJ2hpZGRlbicpO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoc3VjY2VzcyAhPT0gbnVsbCkge1xuXHRcdFx0XHRzdWNjZXNzLmNsYXNzTGlzdC5yZW1vdmUoJ2hpZGRlbicpO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoIHJvY2tldF9hamF4X2RhdGEuY3Jvbl9kaXNhYmxlZCApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBkYXRhID0gbmV3IEZvcm1EYXRhKCk7XG5cblx0XHRcdGRhdGEuYXBwZW5kKCAnYWN0aW9uJywgJ3JvY2tldF9zcGF3bl9jcm9uJyApO1xuXHRcdFx0ZGF0YS5hcHBlbmQoICdub25jZScsIHJvY2tldF9hamF4X2RhdGEubm9uY2UgKTtcblxuXHRcdFx0ZmV0Y2goIGFqYXh1cmwsIHtcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGNyZWRlbnRpYWxzOiAnc2FtZS1vcmlnaW4nLFxuXHRcdFx0XHRib2R5OiBkYXRhXG5cdFx0XHR9ICk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aW1lci5pbm5lckhUTUwgPSByZW1haW5pbmc7XG5cdH1cblxuXHR1cGRhdGVUaW1lcigpO1xuXHRjb25zdCB0aW1lckludGVydmFsID0gc2V0SW50ZXJ2YWwoIHVwZGF0ZVRpbWVyLCAxMDAwKTtcbn1cblxuaWYgKCFEYXRlLm5vdykge1xuICAgIERhdGUubm93ID0gZnVuY3Rpb24gbm93KCkge1xuICAgICAgcmV0dXJuIG5ldyBEYXRlKCkuZ2V0VGltZSgpO1xuICAgIH07XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5wcm9tb19lbmQgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgaW5pdGlhbGl6ZUNsb2NrKCdyb2NrZXQtcHJvbW8tY291bnRkb3duJywgcm9ja2V0X2FqYXhfZGF0YS5wcm9tb19lbmQpO1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEubGljZW5zZV9leHBpcmF0aW9uICE9PSAndW5kZWZpbmVkJykge1xuICAgIGluaXRpYWxpemVDbG9jaygncm9ja2V0LXJlbmV3LWNvdW50ZG93bicsIHJvY2tldF9hamF4X2RhdGEubGljZW5zZV9leHBpcmF0aW9uKTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLm5vdGljZV9lbmRfdGltZSAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBydWNzc1RpbWVyKCdyb2NrZXQtcnVjc3MtdGltZXInLCByb2NrZXRfYWpheF9kYXRhLm5vdGljZV9lbmRfdGltZSk7XG59IiwiaW1wb3J0ICcuLi9jdXN0b20vY3VzdG9tLXNlbGVjdC5qcyc7XG5cbnZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcblxuXG4gICAgLyoqKlxuICAgICogQ2hlY2sgcGFyZW50IC8gc2hvdyBjaGlsZHJlblxuICAgICoqKi9cblxuXHRmdW5jdGlvbiB3cHJTaG93Q2hpbGRyZW4oYUVsZW0pe1xuXHRcdHZhciBwYXJlbnRJZCwgJGNoaWxkcmVuO1xuXG5cdFx0YUVsZW0gICAgID0gJCggYUVsZW0gKTtcblx0XHRwYXJlbnRJZCAgPSBhRWxlbS5hdHRyKCdpZCcpO1xuXHRcdCRjaGlsZHJlbiA9ICQoJ1tkYXRhLXBhcmVudD1cIicgKyBwYXJlbnRJZCArICdcIl0nKTtcblxuXHRcdC8vIFRlc3QgY2hlY2sgZm9yIHN3aXRjaFxuXHRcdGlmKGFFbGVtLmlzKCc6Y2hlY2tlZCcpKXtcblx0XHRcdCRjaGlsZHJlbi5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXG5cdFx0XHQkY2hpbGRyZW4uZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdFx0aWYgKCAkKHRoaXMpLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuaXMoJzpjaGVja2VkJykpIHtcblx0XHRcdFx0XHR2YXIgaWQgPSAkKHRoaXMpLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKTtcblxuXHRcdFx0XHRcdCQoJ1tkYXRhLXBhcmVudD1cIicgKyBpZCArICdcIl0nKS5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdFx0XHR9XG5cdFx0XHR9KTtcblx0XHR9XG5cdFx0ZWxzZXtcblx0XHRcdCRjaGlsZHJlbi5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXG5cdFx0XHQkY2hpbGRyZW4uZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdFx0dmFyIGlkID0gJCh0aGlzKS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyk7XG5cblx0XHRcdFx0JCgnW2RhdGEtcGFyZW50PVwiJyArIGlkICsgJ1wiXScpLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0XHR9KTtcblx0XHR9XG5cdH1cblxuICAgIC8qKlxuICAgICAqIFRlbGwgaWYgdGhlIGdpdmVuIGNoaWxkIGZpZWxkIGhhcyBhbiBhY3RpdmUgcGFyZW50IGZpZWxkLlxuICAgICAqXG4gICAgICogQHBhcmFtICBvYmplY3QgJGZpZWxkIEEgalF1ZXJ5IG9iamVjdCBvZiBhIFwiLndwci1maWVsZFwiIGZpZWxkLlxuICAgICAqIEByZXR1cm4gYm9vbHxudWxsXG4gICAgICovXG4gICAgZnVuY3Rpb24gd3BySXNQYXJlbnRBY3RpdmUoICRmaWVsZCApIHtcbiAgICAgICAgdmFyICRwYXJlbnQ7XG5cbiAgICAgICAgaWYgKCAhICRmaWVsZC5sZW5ndGggKSB7XG4gICAgICAgICAgICAvLyDCr1xcXyjjg4QpXy/Cr1xuICAgICAgICAgICAgcmV0dXJuIG51bGw7XG4gICAgICAgIH1cblxuICAgICAgICAkcGFyZW50ID0gJGZpZWxkLmRhdGEoICdwYXJlbnQnICk7XG5cbiAgICAgICAgaWYgKCB0eXBlb2YgJHBhcmVudCAhPT0gJ3N0cmluZycgKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkIGhhcyBubyBwYXJlbnQgZmllbGQ6IHRoZW4gd2UgY2FuIGRpc3BsYXkgaXQuXG4gICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgfVxuXG4gICAgICAgICRwYXJlbnQgPSAkcGFyZW50LnJlcGxhY2UoIC9eXFxzK3xcXHMrJC9nLCAnJyApO1xuXG4gICAgICAgIGlmICggJycgPT09ICRwYXJlbnQgKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkIGhhcyBubyBwYXJlbnQgZmllbGQ6IHRoZW4gd2UgY2FuIGRpc3BsYXkgaXQuXG4gICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgfVxuXG4gICAgICAgICRwYXJlbnQgPSAkKCAnIycgKyAkcGFyZW50ICk7XG5cbiAgICAgICAgaWYgKCAhICRwYXJlbnQubGVuZ3RoICkge1xuICAgICAgICAgICAgLy8gVGhpcyBmaWVsZCdzIHBhcmVudCBpcyBtaXNzaW5nOiBsZXQncyBjb25zaWRlciBpdCdzIG5vdCBhY3RpdmUgdGhlbi5cbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICggISAkcGFyZW50LmlzKCAnOmNoZWNrZWQnICkgJiYgJHBhcmVudC5pcygnaW5wdXQnKSkge1xuICAgICAgICAgICAgLy8gVGhpcyBmaWVsZCdzIHBhcmVudCBpcyBjaGVja2JveCBhbmQgbm90IGNoZWNrZWQ6IGRvbid0IGRpc3BsYXkgdGhlIGZpZWxkIHRoZW4uXG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuXHRcdGlmICggISRwYXJlbnQuaGFzQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpICYmICRwYXJlbnQuaXMoJ2J1dHRvbicpKSB7XG5cdFx0XHQvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGJ1dHRvbiBhbmQgaXMgbm90IGFjdGl2ZTogZG9uJ3QgZGlzcGxheSB0aGUgZmllbGQgdGhlbi5cblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG4gICAgICAgIC8vIEdvIHJlY3Vyc2l2ZSB0byB0aGUgbGFzdCBwYXJlbnQuXG4gICAgICAgIHJldHVybiB3cHJJc1BhcmVudEFjdGl2ZSggJHBhcmVudC5jbG9zZXN0KCAnLndwci1maWVsZCcgKSApO1xuICAgIH1cblxuXHQvKipcblx0ICogTWFza3Mgc2Vuc2l0aXZlIGluZm9ybWF0aW9uIGluIGFuIGlucHV0IGZpZWxkIGJ5IHJlcGxhY2luZyBhbGwgYnV0IHRoZSBsYXN0IDQgY2hhcmFjdGVycyB3aXRoIGFzdGVyaXNrcy5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IGlkX3NlbGVjdG9yIC0gVGhlIElEIG9mIHRoZSBpbnB1dCBmaWVsZCB0byBiZSBtYXNrZWQuXG5cdCAqIEByZXR1cm5zIHt2b2lkfSAtIE1vZGlmaWVzIHRoZSBpbnB1dCBmaWVsZCB2YWx1ZSBpbi1wbGFjZS5cblx0ICpcblx0ICogQGV4YW1wbGVcblx0ICogLy8gSFRNTDogPGlucHV0IHR5cGU9XCJ0ZXh0XCIgaWQ9XCJjcmVkaXRDYXJkSW5wdXRcIiB2YWx1ZT1cIjEyMzQ1Njc4OTAxMjM0NTZcIj5cblx0ICogbWFza0ZpZWxkKCdjcmVkaXRDYXJkSW5wdXQnKTtcblx0ICogLy8gUmVzdWx0OiBVcGRhdGVzIHRoZSBpbnB1dCBmaWVsZCB2YWx1ZSB0byAnKioqKioqKioqKioqMzQ1NicuXG5cdCAqL1xuXHRmdW5jdGlvbiBtYXNrRmllbGQocHJveHlfc2VsZWN0b3IsIGNvbmNyZXRlX3NlbGVjdG9yKSB7XG5cdFx0dmFyIGNvbmNyZXRlID0ge1xuXHRcdFx0J3ZhbCc6IGNvbmNyZXRlX3NlbGVjdG9yLnZhbCgpLFxuXHRcdFx0J2xlbmd0aCc6IGNvbmNyZXRlX3NlbGVjdG9yLnZhbCgpLmxlbmd0aFxuXHRcdH1cblxuXHRcdGlmIChjb25jcmV0ZS5sZW5ndGggPiA0KSB7XG5cblx0XHRcdHZhciBoaWRkZW5QYXJ0ID0gJ1xcdTIwMjInLnJlcGVhdChNYXRoLm1heCgwLCBjb25jcmV0ZS5sZW5ndGggLSA0KSk7XG5cdFx0XHR2YXIgdmlzaWJsZVBhcnQgPSBjb25jcmV0ZS52YWwuc2xpY2UoLTQpO1xuXG5cdFx0XHQvLyBDb21iaW5lIHRoZSBoaWRkZW4gYW5kIHZpc2libGUgcGFydHNcblx0XHRcdHZhciBtYXNrZWRWYWx1ZSA9IGhpZGRlblBhcnQgKyB2aXNpYmxlUGFydDtcblxuXHRcdFx0cHJveHlfc2VsZWN0b3IudmFsKG1hc2tlZFZhbHVlKTtcblx0XHR9XG5cdFx0Ly8gRW5zdXJlIGV2ZW50cyBhcmUgbm90IGFkZGVkIG1vcmUgdGhhbiBvbmNlXG5cdFx0aWYgKCFwcm94eV9zZWxlY3Rvci5kYXRhKCdldmVudHNBdHRhY2hlZCcpKSB7XG5cdFx0XHRwcm94eV9zZWxlY3Rvci5vbignaW5wdXQnLCBoYW5kbGVJbnB1dCk7XG5cdFx0XHRwcm94eV9zZWxlY3Rvci5vbignZm9jdXMnLCBoYW5kbGVGb2N1cyk7XG5cdFx0XHRwcm94eV9zZWxlY3Rvci5kYXRhKCdldmVudHNBdHRhY2hlZCcsIHRydWUpO1xuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIEhhbmRsZSB0aGUgaW5wdXQgZXZlbnRcblx0XHQgKi9cblx0XHRmdW5jdGlvbiBoYW5kbGVJbnB1dCgpIHtcblx0XHRcdHZhciBwcm94eVZhbHVlID0gcHJveHlfc2VsZWN0b3IudmFsKCk7XG5cdFx0XHRpZiAocHJveHlWYWx1ZS5pbmRleE9mKCdcXHUyMDIyJykgPT09IC0xKSB7XG5cdFx0XHRcdGNvbmNyZXRlX3NlbGVjdG9yLnZhbChwcm94eVZhbHVlKTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBIYW5kbGUgdGhlIGZvY3VzIGV2ZW50XG5cdFx0ICovXG5cdFx0ZnVuY3Rpb24gaGFuZGxlRm9jdXMoKSB7XG5cdFx0XHR2YXIgY29uY3JldGVfdmFsdWUgPSBjb25jcmV0ZV9zZWxlY3Rvci52YWwoKTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLnZhbChjb25jcmV0ZV92YWx1ZSk7XG5cdFx0fVxuXG5cdH1cblxuXHRcdC8vIFVwZGF0ZSB0aGUgY29uY3JldGUgZmllbGQgd2hlbiB0aGUgcHJveHkgaXMgdXBkYXRlZC5cblxuXG5cdG1hc2tGaWVsZCgkKCcjY2xvdWRmbGFyZV9hcGlfa2V5X21hc2snKSwgJCgnI2Nsb3VkZmxhcmVfYXBpX2tleScpKTtcblx0bWFza0ZpZWxkKCQoJyNjbG91ZGZsYXJlX3pvbmVfaWRfbWFzaycpLCAkKCcjY2xvdWRmbGFyZV96b25lX2lkJykpO1xuXG5cdC8vIERpc3BsYXkvSGlkZSBjaGlsZHJlbiBmaWVsZHMgb24gY2hlY2tib3ggY2hhbmdlLlxuICAgICQoICcud3ByLWlzUGFyZW50IGlucHV0W3R5cGU9Y2hlY2tib3hdJyApLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgd3ByU2hvd0NoaWxkcmVuKCQodGhpcykpO1xuICAgIH0pO1xuXG4gICAgLy8gT24gcGFnZSBsb2FkLCBkaXNwbGF5IHRoZSBhY3RpdmUgZmllbGRzLlxuICAgICQoICcud3ByLWZpZWxkLS1jaGlsZHJlbicgKS5lYWNoKCBmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyICRmaWVsZCA9ICQoIHRoaXMgKTtcblxuICAgICAgICBpZiAoIHdwcklzUGFyZW50QWN0aXZlKCAkZmllbGQgKSApIHtcbiAgICAgICAgICAgICRmaWVsZC5hZGRDbGFzcyggJ3dwci1pc09wZW4nICk7XG4gICAgICAgIH1cbiAgICB9ICk7XG5cblxuXG5cbiAgICAvKioqXG4gICAgKiBXYXJuaW5nIGZpZWxkc1xuICAgICoqKi9cblxuICAgIHZhciAkd2FybmluZ1BhcmVudCA9ICQoJy53cHItZmllbGQtLXBhcmVudCcpO1xuICAgIHZhciAkd2FybmluZ1BhcmVudElucHV0ID0gJCgnLndwci1maWVsZC0tcGFyZW50IGlucHV0W3R5cGU9Y2hlY2tib3hdJyk7XG5cbiAgICAvLyBJZiBhbHJlYWR5IGNoZWNrZWRcbiAgICAkd2FybmluZ1BhcmVudElucHV0LmVhY2goZnVuY3Rpb24oKXtcbiAgICAgICAgd3ByU2hvd0NoaWxkcmVuKCQodGhpcykpO1xuICAgIH0pO1xuXG4gICAgJHdhcm5pbmdQYXJlbnQub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICB3cHJTaG93V2FybmluZygkKHRoaXMpKTtcbiAgICB9KTtcblxuICAgIGZ1bmN0aW9uIHdwclNob3dXYXJuaW5nKGFFbGVtKXtcbiAgICAgICAgdmFyICR3YXJuaW5nRmllbGQgPSBhRWxlbS5uZXh0KCcud3ByLWZpZWxkV2FybmluZycpLFxuICAgICAgICAgICAgJHRoaXNDaGVja2JveCA9IGFFbGVtLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJyksXG4gICAgICAgICAgICAkbmV4dFdhcm5pbmcgPSBhRWxlbS5wYXJlbnQoKS5uZXh0KCcud3ByLXdhcm5pbmdDb250YWluZXInKSxcbiAgICAgICAgICAgICRuZXh0RmllbGRzID0gJG5leHRXYXJuaW5nLmZpbmQoJy53cHItZmllbGQnKSxcbiAgICAgICAgICAgIHBhcmVudElkID0gYUVsZW0uZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpLFxuICAgICAgICAgICAgJGNoaWxkcmVuID0gJCgnW2RhdGEtcGFyZW50PVwiJyArIHBhcmVudElkICsgJ1wiXScpXG4gICAgICAgIDtcblxuICAgICAgICAvLyBDaGVjayB3YXJuaW5nIHBhcmVudFxuICAgICAgICBpZigkdGhpc0NoZWNrYm94LmlzKCc6Y2hlY2tlZCcpKXtcbiAgICAgICAgICAgICR3YXJuaW5nRmllbGQuYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcbiAgICAgICAgICAgICR0aGlzQ2hlY2tib3gucHJvcCgnY2hlY2tlZCcsIGZhbHNlKTtcbiAgICAgICAgICAgIGFFbGVtLnRyaWdnZXIoJ2NoYW5nZScpO1xuXG5cbiAgICAgICAgICAgIHZhciAkd2FybmluZ0J1dHRvbiA9ICR3YXJuaW5nRmllbGQuZmluZCgnLndwci1idXR0b24nKTtcblxuICAgICAgICAgICAgLy8gVmFsaWRhdGUgdGhlIHdhcm5pbmdcbiAgICAgICAgICAgICR3YXJuaW5nQnV0dG9uLm9uKCdjbGljaycsIGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAgICAgJHRoaXNDaGVja2JveC5wcm9wKCdjaGVja2VkJywgdHJ1ZSk7XG4gICAgICAgICAgICAgICAgJHdhcm5pbmdGaWVsZC5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuICAgICAgICAgICAgICAgICRjaGlsZHJlbi5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXG4gICAgICAgICAgICAgICAgLy8gSWYgbmV4dCBlbGVtID0gZGlzYWJsZWRcbiAgICAgICAgICAgICAgICBpZigkbmV4dFdhcm5pbmcubGVuZ3RoID4gMCl7XG4gICAgICAgICAgICAgICAgICAgICRuZXh0RmllbGRzLnJlbW92ZUNsYXNzKCd3cHItaXNEaXNhYmxlZCcpO1xuICAgICAgICAgICAgICAgICAgICAkbmV4dEZpZWxkcy5maW5kKCdpbnB1dCcpLnByb3AoJ2Rpc2FibGVkJywgZmFsc2UpO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2V7XG4gICAgICAgICAgICAkbmV4dEZpZWxkcy5hZGRDbGFzcygnd3ByLWlzRGlzYWJsZWQnKTtcbiAgICAgICAgICAgICRuZXh0RmllbGRzLmZpbmQoJ2lucHV0JykucHJvcCgnZGlzYWJsZWQnLCB0cnVlKTtcbiAgICAgICAgICAgICRuZXh0RmllbGRzLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykucHJvcCgnY2hlY2tlZCcsIGZhbHNlKTtcbiAgICAgICAgICAgICRjaGlsZHJlbi5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQ05BTUVTIGFkZC9yZW1vdmUgbGluZXNcbiAgICAgKi9cbiAgICAkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1tdWx0aXBsZS1jbG9zZScsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0JCh0aGlzKS5wYXJlbnQoKS5yZW1vdmUoKTtcblx0fSApO1xuXG5cdCQoJy53cHItYnV0dG9uLS1hZGRNdWx0aScpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICQoJCgnI3dwci1jbmFtZS1tb2RlbCcpLmh0bWwoKSkuYXBwZW5kVG8oJyN3cHItY25hbWVzLWxpc3QnKTtcbiAgICB9KTtcblxuXHQvKioqXG5cdCAqIFdwciBSYWRpbyBidXR0b25cblx0ICoqKi9cblx0dmFyIGRpc2FibGVfcmFkaW9fd2FybmluZyA9IGZhbHNlO1xuXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvbicsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0aWYoJCh0aGlzKS5oYXNDbGFzcygncmFkaW8tYWN0aXZlJykpe1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0XHR2YXIgJHBhcmVudCA9ICQodGhpcykucGFyZW50cygnLndwci1yYWRpby1idXR0b25zJyk7XG5cdFx0JHBhcmVudC5maW5kKCcud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvbicpLnJlbW92ZUNsYXNzKCdyYWRpby1hY3RpdmUnKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItZXh0cmEtZmllbGRzLWNvbnRhaW5lcicpLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0JHBhcmVudC5maW5kKCcud3ByLWZpZWxkV2FybmluZycpLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0JCh0aGlzKS5hZGRDbGFzcygncmFkaW8tYWN0aXZlJyk7XG5cdFx0d3ByU2hvd1JhZGlvV2FybmluZygkKHRoaXMpKTtcblxuXHR9ICk7XG5cblxuXHRmdW5jdGlvbiB3cHJTaG93UmFkaW9XYXJuaW5nKCRlbG0pe1xuXHRcdGRpc2FibGVfcmFkaW9fd2FybmluZyA9IGZhbHNlO1xuXHRcdCRlbG0udHJpZ2dlciggXCJiZWZvcmVfc2hvd19yYWRpb193YXJuaW5nXCIsIFsgJGVsbSBdICk7XG5cdFx0aWYgKCEkZWxtLmhhc0NsYXNzKCdoYXMtd2FybmluZycpIHx8IGRpc2FibGVfcmFkaW9fd2FybmluZykge1xuXHRcdFx0d3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSk7XG5cdFx0XHQkZWxtLnRyaWdnZXIoIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIFsgJGVsbSBdICk7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHZhciAkd2FybmluZ0ZpZWxkID0gJCgnW2RhdGEtcGFyZW50PVwiJyArICRlbG0uYXR0cignaWQnKSArICdcIl0ud3ByLWZpZWxkV2FybmluZycpO1xuXHRcdCR3YXJuaW5nRmllbGQuYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHR2YXIgJHdhcm5pbmdCdXR0b24gPSAkd2FybmluZ0ZpZWxkLmZpbmQoJy53cHItYnV0dG9uJyk7XG5cblx0XHQvLyBWYWxpZGF0ZSB0aGUgd2FybmluZ1xuXHRcdCR3YXJuaW5nQnV0dG9uLm9uKCdjbGljaycsIGZ1bmN0aW9uKCl7XG5cdFx0XHQkd2FybmluZ0ZpZWxkLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0XHR3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKTtcblx0XHRcdCRlbG0udHJpZ2dlciggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgWyAkZWxtIF0gKTtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9KTtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pIHtcblx0XHR2YXIgJHBhcmVudCA9ICRlbG0ucGFyZW50cygnLndwci1yYWRpby1idXR0b25zJyk7XG5cdFx0dmFyICRjaGlsZHJlbiA9ICQoJy53cHItZXh0cmEtZmllbGRzLWNvbnRhaW5lcltkYXRhLXBhcmVudD1cIicgKyAkZWxtLmF0dHIoJ2lkJykgKyAnXCJdJyk7XG5cdFx0JGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdH1cblxuXHQvKioqXG5cdCAqIFdwciBPcHRpbWl6ZSBDc3MgRGVsaXZlcnkgRmllbGRcblx0ICoqKi9cblx0dmFyIHJ1Y3NzQWN0aXZlID0gcGFyc2VJbnQoJCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKCkpO1xuXG5cdCQoIFwiI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QgLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b25cIiApXG5cdFx0Lm9uKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBmdW5jdGlvbiggZXZlbnQsICRlbG0gKSB7XG5cdFx0XHR0b2dnbGVBY3RpdmVPcHRpbWl6ZUNzc0RlbGl2ZXJ5TWV0aG9kKCRlbG0pO1xuXHRcdH0pO1xuXG5cdCQoXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5XCIpLm9uKFwiY2hhbmdlXCIsIGZ1bmN0aW9uKCl7XG5cdFx0aWYoICQodGhpcykuaXMoXCI6bm90KDpjaGVja2VkKVwiKSApe1xuXHRcdFx0ZGlzYWJsZU9wdGltaXplQ3NzRGVsaXZlcnkoKTtcblx0XHR9ZWxzZXtcblx0XHRcdHZhciBkZWZhdWx0X3JhZGlvX2J1dHRvbl9pZCA9ICcjJyskKCcjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCcpLmRhdGEoICdkZWZhdWx0JyApO1xuXHRcdFx0JChkZWZhdWx0X3JhZGlvX2J1dHRvbl9pZCkudHJpZ2dlcignY2xpY2snKTtcblx0XHR9XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHRvZ2dsZUFjdGl2ZU9wdGltaXplQ3NzRGVsaXZlcnlNZXRob2QoJGVsbSkge1xuXHRcdHZhciBvcHRpbWl6ZV9tZXRob2QgPSAkZWxtLmRhdGEoJ3ZhbHVlJyk7XG5cdFx0aWYoJ3JlbW92ZV91bnVzZWRfY3NzJyA9PT0gb3B0aW1pemVfbWV0aG9kKXtcblx0XHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgxKTtcblx0XHRcdCQoJyNhc3luY19jc3MnKS52YWwoMCk7XG5cdFx0fWVsc2V7XG5cdFx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMCk7XG5cdFx0XHQkKCcjYXN5bmNfY3NzJykudmFsKDEpO1xuXHRcdH1cblxuXHR9XG5cblx0ZnVuY3Rpb24gZGlzYWJsZU9wdGltaXplQ3NzRGVsaXZlcnkoKSB7XG5cdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDApO1xuXHRcdCQoJyNhc3luY19jc3MnKS52YWwoMCk7XG5cdH1cblxuXHQkKCBcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kIC53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uXCIgKVxuXHRcdC5vbiggXCJiZWZvcmVfc2hvd19yYWRpb193YXJuaW5nXCIsIGZ1bmN0aW9uKCBldmVudCwgJGVsbSApIHtcblx0XHRcdGRpc2FibGVfcmFkaW9fd2FybmluZyA9ICgncmVtb3ZlX3VudXNlZF9jc3MnID09PSAkZWxtLmRhdGEoJ3ZhbHVlJykgJiYgMSA9PT0gcnVjc3NBY3RpdmUpXG5cdFx0fSk7XG5cblx0JCggXCIud3ByLW11bHRpcGxlLXNlbGVjdCAud3ByLWxpc3QtaGVhZGVyXCIgKS5jbGljayhmdW5jdGlvbiAoZSkge1xuXHRcdCQoZS50YXJnZXQpLmNsb3Nlc3QoJy53cHItbXVsdGlwbGUtc2VsZWN0IC53cHItbGlzdCcpLnRvZ2dsZUNsYXNzKCdvcGVuJyk7XG5cdH0pO1xuXG5cdCQoJy53cHItbXVsdGlwbGUtc2VsZWN0IC53cHItY2hlY2tib3gnKS5jbGljayhmdW5jdGlvbiAoZSkge1xuXHRcdGNvbnN0IGNoZWNrYm94ID0gJCh0aGlzKS5maW5kKCdpbnB1dCcpO1xuXHRcdGNvbnN0IGlzX2NoZWNrZWQgPSBjaGVja2JveC5hdHRyKCdjaGVja2VkJykgIT09IHVuZGVmaW5lZDtcblx0XHRjaGVja2JveC5hdHRyKCdjaGVja2VkJywgaXNfY2hlY2tlZCA/IG51bGwgOiAnY2hlY2tlZCcgKTtcblx0XHRjb25zdCBzdWJfY2hlY2tib3hlcyA9ICQoY2hlY2tib3gpLmNsb3Nlc3QoJy53cHItbGlzdCcpLmZpbmQoJy53cHItbGlzdC1ib2R5IGlucHV0W3R5cGU9XCJjaGVja2JveFwiXScpO1xuXHRcdGlmKGNoZWNrYm94Lmhhc0NsYXNzKCd3cHItbWFpbi1jaGVja2JveCcpKSB7XG5cdFx0XHQkLm1hcChzdWJfY2hlY2tib3hlcywgY2hlY2tib3ggPT4ge1xuXHRcdFx0XHQkKGNoZWNrYm94KS5hdHRyKCdjaGVja2VkJywgaXNfY2hlY2tlZCA/IG51bGwgOiAnY2hlY2tlZCcgKTtcblx0XHRcdH0pO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRjb25zdCBtYWluX2NoZWNrYm94ID0gJChjaGVja2JveCkuY2xvc2VzdCgnLndwci1saXN0JykuZmluZCgnLndwci1tYWluLWNoZWNrYm94Jyk7XG5cblx0XHRjb25zdCBzdWJfY2hlY2tlZCA9ICAkLm1hcChzdWJfY2hlY2tib3hlcywgY2hlY2tib3ggPT4ge1xuXHRcdFx0aWYoJChjaGVja2JveCkuYXR0cignY2hlY2tlZCcpID09PSB1bmRlZmluZWQpIHtcblx0XHRcdFx0cmV0dXJuIDtcblx0XHRcdH1cblx0XHRcdHJldHVybiBjaGVja2JveDtcblx0XHR9KTtcblx0XHRtYWluX2NoZWNrYm94LmF0dHIoJ2NoZWNrZWQnLCBzdWJfY2hlY2tlZC5sZW5ndGggPT09IHN1Yl9jaGVja2JveGVzLmxlbmd0aCA/ICdjaGVja2VkJyA6IG51bGwgKTtcblx0fSk7XG5cblx0aWYgKCAkKCAnLndwci1tYWluLWNoZWNrYm94JyApLmxlbmd0aCA+IDAgKSB7XG5cdFx0JCgnLndwci1tYWluLWNoZWNrYm94JykuZWFjaCgoY2hlY2tib3hfa2V5LCBjaGVja2JveCkgPT4ge1xuXHRcdFx0bGV0IHBhcmVudF9saXN0ID0gJChjaGVja2JveCkucGFyZW50cygnLndwci1saXN0Jyk7XG5cdFx0XHRsZXQgbm90X2NoZWNrZWQgPSBwYXJlbnRfbGlzdC5maW5kKCAnLndwci1saXN0LWJvZHkgaW5wdXRbdHlwZT1jaGVja2JveF06bm90KDpjaGVja2VkKScgKS5sZW5ndGg7XG5cdFx0XHQkKGNoZWNrYm94KS5hdHRyKCdjaGVja2VkJywgbm90X2NoZWNrZWQgPD0gMCA/ICdjaGVja2VkJyA6IG51bGwgKTtcblx0XHR9KTtcblx0fVxuXG5cdGxldCBjaGVja0JveENvdW50ZXIgPSB7XG5cdFx0Y2hlY2tlZDoge30sXG5cdFx0dG90YWw6IHt9XG5cdH07XG5cdCQoJy53cHItZmllbGQtLWNhdGVnb3JpemVkbXVsdGlzZWxlY3QgLndwci1saXN0JykuZWFjaChmdW5jdGlvbiAoKSB7XG5cdFx0Ly8gR2V0IHRoZSBJRCBvZiB0aGUgY3VycmVudCBlbGVtZW50XG5cdFx0bGV0IGlkID0gJCh0aGlzKS5hdHRyKCdpZCcpO1xuXHRcdGlmIChpZCkge1xuXHRcdFx0Y2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID0gJChgIyR7aWR9IGlucHV0W3R5cGU9J2NoZWNrYm94J106Y2hlY2tlZGApLmxlbmd0aDtcblx0XHRcdGNoZWNrQm94Q291bnRlci50b3RhbFtpZF0gPSAkKGAjJHtpZH0gaW5wdXRbdHlwZT0nY2hlY2tib3gnXTpub3QoLndwci1tYWluLWNoZWNrYm94KWApLmxlbmd0aDtcblx0XHRcdC8vIFVwZGF0ZSB0aGUgY291bnRlciB0ZXh0XG5cdFx0XHQkKGAjJHtpZH0gLndwci1iYWRnZS1jb3VudGVyIHNwYW5gKS50ZXh0KGNoZWNrQm94Q291bnRlci5jaGVja2VkW2lkXSk7XG5cdFx0XHQvLyBTaG93IG9yIGhpZGUgdGhlIGNvdW50ZXIgYmFkZ2UgYmFzZWQgb24gdGhlIGNvdW50XG5cdFx0XHQkKGAjJHtpZH0gLndwci1iYWRnZS1jb3VudGVyYCkudG9nZ2xlKGNoZWNrQm94Q291bnRlci5jaGVja2VkW2lkXSA+IDApO1xuXG5cdFx0XHQvLyBDaGVjayB0aGUgc2VsZWN0IGFsbCBvcHRpb24gaWYgYWxsIGV4Y2x1c2lvbnMgYXJlIGNoZWNrZWQgaW4gYSBzZWN0aW9uLlxuXHRcdFx0aWYgKGNoZWNrQm94Q291bnRlci5jaGVja2VkW2lkXSA9PT0gY2hlY2tCb3hDb3VudGVyLnRvdGFsW2lkXSkge1xuXHRcdFx0XHQkKGAjJHtpZH0gLndwci1tYWluLWNoZWNrYm94YCkuYXR0cignY2hlY2tlZCcsIHRydWUpO1xuXHRcdFx0fVxuXHRcdH1cblx0fSk7XG5cblx0LyoqXG5cdCAqIERlbGF5IEpTIEV4ZWN1dGlvbiBTYWZlIE1vZGUgRmllbGRcblx0ICovXG5cdHZhciAkZGplX3NhZmVfbW9kZV9jaGVja2JveCA9ICQoJyNkZWxheV9qc19leGVjdXRpb25fc2FmZV9tb2RlJyk7XG5cdCQoJyNkZWxheV9qcycpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbiAoKSB7XG5cdFx0aWYgKCQodGhpcykuaXMoJzpub3QoOmNoZWNrZWQpJykgJiYgJGRqZV9zYWZlX21vZGVfY2hlY2tib3guaXMoJzpjaGVja2VkJykpIHtcblx0XHRcdCRkamVfc2FmZV9tb2RlX2NoZWNrYm94LnRyaWdnZXIoJ2NsaWNrJyk7XG5cdFx0fVxuXHR9KTtcblxuXHRsZXQgc3RhY2tlZF9zZWxlY3QgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ3JvY2tldF9zdGFja2VkX3NlbGVjdCcgKTtcblx0aWYgKCBzdGFja2VkX3NlbGVjdCApIHtcblx0XHRzdGFja2VkX3NlbGVjdC5hZGRFdmVudExpc3RlbmVyKCdjdXN0b20tc2VsZWN0LWNoYW5nZScsZnVuY3Rpb24oZXZlbnQpe1xuXG5cdFx0XHRsZXQgc2VsZWN0ZWRfb3B0aW9uID0gJCggZXZlbnQuZGV0YWlsLnNlbGVjdGVkT3B0aW9uICk7XG5cblx0XHRcdGxldCBuYW1lID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ25hbWUnKTtcblxuXHRcdFx0bGV0IHNhdmluZyA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCdzYXZpbmcnKTtcblx0XHRcdGxldCByZWd1bGFyX3ByaWNlICA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCdyZWd1bGFyLXByaWNlJyk7XG5cdFx0XHRsZXQgcHJpY2UgID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3ByaWNlJyk7XG5cdFx0XHRsZXQgdXJsICAgID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3VybCcpO1xuXG5cdFx0XHRsZXQgcGFyZW50X2l0ZW0gPSAkKHRoaXMpLnBhcmVudHMoICcud3ByLXVwZ3JhZGUtaXRlbScgKTtcblxuXHRcdFx0aWYgKCBzYXZpbmcgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtc2F2aW5nIHNwYW4nICkuaHRtbCggc2F2aW5nICk7XG5cdFx0XHR9XG5cdFx0XHRpZiAoIG5hbWUgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtdGl0bGUnICkuaHRtbCggbmFtZSApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCByZWd1bGFyX3ByaWNlICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLXByaWNlLXJlZ3VsYXIgc3BhbicgKS5odG1sKCByZWd1bGFyX3ByaWNlICk7XG5cdFx0XHR9XG5cdFx0XHRpZiAoIHByaWNlICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLXByaWNlLXZhbHVlJyApLmh0bWwoIHByaWNlICk7XG5cdFx0XHR9XG5cdFx0XHRpZiAoIHVybCApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1saW5rJyApLmF0dHIoICdocmVmJywgdXJsICk7XG5cdFx0XHR9XG5cblx0XHR9ICk7XG5cdH1cblxuXHQkKGRvY3VtZW50KS5vbiggJ2NsaWNrJywgJy53cHItY29uZmlybS1kZWxldGUnLCBmdW5jdGlvbiAoZSkge1xuXHRcdHJldHVybiBjb25maXJtKCAkKHRoaXMpLmRhdGEoJ3dwcl9jb25maXJtX21zZycpICk7XG5cdH0gKTtcblxufSk7XG4iLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG5cblxuXHQvKioqXG5cdCogRGFzaGJvYXJkIG5vdGljZVxuXHQqKiovXG5cblx0dmFyICRub3RpY2UgPSAkKCcud3ByLW5vdGljZScpO1xuXHR2YXIgJG5vdGljZUNsb3NlID0gJCgnI3dwci1jb25ncmF0dWxhdGlvbnMtbm90aWNlJyk7XG5cblx0JG5vdGljZUNsb3NlLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlRGFzaGJvYXJkTm90aWNlKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJDbG9zZURhc2hib2FyZE5vdGljZSgpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC50bygkbm90aWNlLCAxLCB7YXV0b0FscGhhOjAsIHg6NDAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLnRvKCRub3RpY2UsIDAuNiwge2hlaWdodDogMCwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNCcpXG5cdFx0ICAuc2V0KCRub3RpY2UsIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvKipcblx0ICogUm9ja2V0IEFuYWx5dGljcyBub3RpY2UgaW5mbyBjb2xsZWN0XG5cdCAqL1xuXHQkKCAnLnJvY2tldC1hbmFseXRpY3MtZGF0YS1jb250YWluZXInICkuaGlkZSgpO1xuXHQkKCAnLnJvY2tldC1wcmV2aWV3LWFuYWx5dGljcy1kYXRhJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKHRoaXMpLnBhcmVudCgpLm5leHQoICcucm9ja2V0LWFuYWx5dGljcy1kYXRhLWNvbnRhaW5lcicgKS50b2dnbGUoKTtcblx0fSApO1xuXG5cdC8qKipcblx0KiBIaWRlIC8gc2hvdyBSb2NrZXQgYWRkb24gdGFicy5cblx0KioqL1xuXG5cdCQoICcud3ByLXRvZ2dsZS1idXR0b24nICkuZWFjaCggZnVuY3Rpb24oKSB7XG5cdFx0dmFyICRidXR0b24gICA9ICQoIHRoaXMgKTtcblx0XHR2YXIgJGNoZWNrYm94ID0gJGJ1dHRvbi5jbG9zZXN0KCAnLndwci1maWVsZHNDb250YWluZXItZmllbGRzZXQnICkuZmluZCggJy53cHItcmFkaW8gOmNoZWNrYm94JyApO1xuXHRcdHZhciAkbWVudUl0ZW0gPSAkKCAnW2hyZWY9XCInICsgJGJ1dHRvbi5hdHRyKCAnaHJlZicgKSArICdcIl0ud3ByLW1lbnVJdGVtJyApO1xuXG5cdFx0JGNoZWNrYm94Lm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHRcdGlmICggJGNoZWNrYm94LmlzKCAnOmNoZWNrZWQnICkgKSB7XG5cdFx0XHRcdCRtZW51SXRlbS5jc3MoICdkaXNwbGF5JywgJ2Jsb2NrJyApO1xuXHRcdFx0XHQkYnV0dG9uLmNzcyggJ2Rpc3BsYXknLCAnaW5saW5lLWJsb2NrJyApO1xuXHRcdFx0fSBlbHNle1xuXHRcdFx0XHQkbWVudUl0ZW0uY3NzKCAnZGlzcGxheScsICdub25lJyApO1xuXHRcdFx0XHQkYnV0dG9uLmNzcyggJ2Rpc3BsYXknLCAnbm9uZScgKTtcblx0XHRcdH1cblx0XHR9ICkudHJpZ2dlciggJ2NoYW5nZScgKTtcblx0fSApO1xuXG5cdC8qKipcblx0KiBIZWxwIEJ1dHRvbiBUcmFja2luZ1xuXHQqKiovXG5cblx0Ly8gVHJhY2sgY2xpY2tzIG9uIHZhcmlvdXMgaGVscCBlbGVtZW50cyB3aXRoIGRhdGEgYXR0cmlidXRlc1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnW2RhdGEtd3ByX3RyYWNrX2hlbHBdJywgZnVuY3Rpb24oZSkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0dmFyICRlbCA9ICQodGhpcyk7XG5cdFx0XHR2YXIgYnV0dG9uID0gJGVsLmRhdGEoJ3dwcl90cmFja19oZWxwJyk7XG5cdFx0XHR2YXIgY29udGV4dCA9ICRlbC5kYXRhKCd3cHJfdHJhY2tfY29udGV4dCcpIHx8ICcnO1xuXG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBUcmFjayBzcGVjaWZpYyBoZWxwIHJlc291cmNlIGNsaWNrcyB3aXRoIGV4cGxpY2l0IHNlbGVjdG9yc1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndpc3RpYV9lbWJlZCcsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0dmFyIHRpdGxlID0gJCh0aGlzKS50ZXh0KCkgfHwgJ0dldHRpbmcgU3RhcnRlZCBWaWRlbyc7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKHRpdGxlLCAnR2V0dGluZyBTdGFydGVkJyk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBUcmFjayBGQVEgbGlua3Ncblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJ2FbZGF0YS1iZWFjb24tYXJ0aWNsZV0nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHZhciBocmVmID0gJCh0aGlzKS5hdHRyKCdocmVmJyk7XG5cdFx0XHR2YXIgdGV4dCA9ICQodGhpcykudGV4dCgpO1xuXG5cdFx0XHQvLyBDaGVjayBpZiBpdCdzIGluIEZBUSBzZWN0aW9uIG9yIHNpZGViYXIgZG9jdW1lbnRhdGlvblxuXHRcdFx0aWYgKCQodGhpcykuY2xvc2VzdCgnLndwci1maWVsZHNDb250YWluZXItZmllbGRzZXQnKS5wcmV2KCcud3ByLW9wdGlvbkhlYWRlcicpLmZpbmQoJy53cHItdGl0bGUyJykudGV4dCgpLmluY2x1ZGVzKCdGcmVxdWVudGx5IEFza2VkIFF1ZXN0aW9ucycpKSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0ZBUSAtICcgKyB0ZXh0LCAnRGFzaGJvYXJkJyk7XG5cdFx0XHR9IGVsc2UgaWYgKCQodGhpcykuY2xvc2VzdCgnLndwci1kb2N1bWVudGF0aW9uJykubGVuZ3RoID4gMCkge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdEb2N1bWVudGF0aW9uJywgJ1NpZGViYXInKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0RvY3VtZW50YXRpb24gTGluaycsICdHZW5lcmFsJyk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBUcmFjayBcIkhvdyB0byBtZWFzdXJlIGxvYWRpbmcgdGltZVwiIGxpbmtcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJ2FbaHJlZio9XCJob3ctdG8tdGVzdC13b3JkcHJlc3Mtc2l0ZS1wZXJmb3JtYW5jZVwiXScsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignTG9hZGluZyBUaW1lIEd1aWRlJywgJ1NpZGViYXInKTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIFwiTmVlZCBoZWxwP1wiIGxpbmtzIChleGlzdGluZyBoZWxwIGJ1dHRvbnMpXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLWluZm9BY3Rpb24tLWhlbHA6bm90KFtkYXRhLWJlYWNvbi1pZF0pJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdOZWVkIEhlbHAnLCAnR2VuZXJhbCcpO1xuXHRcdH1cblx0fSk7XG5cblxuXHQvKioqXG5cdCogU2hvdyBwb3BpbiBhbmFseXRpY3Ncblx0KioqL1xuXG5cdHZhciAkd3ByQW5hbHl0aWNzUG9waW4gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcycpLFxuXHRcdCR3cHJQb3Bpbk92ZXJsYXkgPSAkKCcud3ByLVBvcGluLW92ZXJsYXknKSxcblx0XHQkd3ByQW5hbHl0aWNzQ2xvc2VQb3BpbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzLWNsb3NlJyksXG5cdFx0JHdwckFuYWx5dGljc1BvcGluQnV0dG9uID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MgLndwci1idXR0b24nKSxcblx0XHQkd3ByQW5hbHl0aWNzT3BlblBvcGluID0gJCgnLndwci1qcy1wb3BpbicpXG5cdDtcblxuXHQkd3ByQW5hbHl0aWNzT3BlblBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByT3BlbkFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwckFuYWx5dGljc0Nsb3NlUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJDbG9zZUFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwckFuYWx5dGljc1BvcGluQnV0dG9uLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByQWN0aXZhdGVBbmFseXRpY3MoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwck9wZW5BbmFseXRpY3MoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAuc2V0KCR3cHJBbmFseXRpY3NQb3BpbiwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHQgIC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHQgIC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjB9LHthdXRvQWxwaGE6MSwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAuZnJvbVRvKCR3cHJBbmFseXRpY3NQb3BpbiwgMC42LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDogLTI0fSwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJDbG9zZUFuYWx5dGljcygpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC5mcm9tVG8oJHdwckFuYWx5dGljc1BvcGluLCAwLjYsIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOiAwfSwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6LTI0LCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjF9LHthdXRvQWxwaGE6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQgIC5zZXQoJHdwckFuYWx5dGljc1BvcGluLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0ICAuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJBY3RpdmF0ZUFuYWx5dGljcygpe1xuXHRcdHdwckNsb3NlQW5hbHl0aWNzKCk7XG5cdFx0JCgnI2FuYWx5dGljc19lbmFibGVkJykucHJvcCgnY2hlY2tlZCcsIHRydWUpO1xuXG5cdFx0dmFyIGFuYWx5dGljc0VuYWJsZWQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnYW5hbHl0aWNzX2VuYWJsZWQnKTtcblxuXHRcdGlmICggYW5hbHl0aWNzRW5hYmxlZCApIHtcblx0XHRcdHZhciBjaGFuZ2VFdmVudCA9IG5ldyBFdmVudCgnY2hhbmdlJywgeyBidWJibGVzOiB0cnVlIH0pO1xuXHRcdFx0YW5hbHl0aWNzRW5hYmxlZC5kaXNwYXRjaEV2ZW50KGNoYW5nZUV2ZW50KTtcblx0XHR9XG5cdH1cblxuXHQvLyBEaXNwbGF5IENUQSB3aXRoaW4gdGhlIHBvcGluIGBXaGF0IGluZm8gd2lsbCB3ZSBjb2xsZWN0P2Bcblx0JCgnI2FuYWx5dGljc19lbmFibGVkJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHQkKCcud3ByLXJvY2tldC1hbmFseXRpY3MtY3RhJykudG9nZ2xlQ2xhc3MoJ3dwci1pc0hpZGRlbicpO1xuXHR9KTtcblxuXHQvKioqXG5cdCogU2hvdyBwb3BpbiB1cGdyYWRlXG5cdCoqKi9cblxuXHR2YXIgJHdwclVwZ3JhZGVQb3BpbiA9ICQoJy53cHItUG9waW4tVXBncmFkZScpLFxuXHQkd3ByVXBncmFkZUNsb3NlUG9waW4gPSAkKCcud3ByLVBvcGluLVVwZ3JhZGUtY2xvc2UnKSxcblx0JHdwclVwZ3JhZGVPcGVuUG9waW4gPSAkKCcud3ByLXBvcGluLXVwZ3JhZGUtdG9nZ2xlJyk7XG5cblx0JHdwclVwZ3JhZGVPcGVuUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJPcGVuVXBncmFkZVBvcGluKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByVXBncmFkZUNsb3NlUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VVcGdyYWRlUG9waW4oKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwck9wZW5VcGdyYWRlUG9waW4oKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpO1xuXG5cdFx0dlRMLnNldCgkd3ByVXBncmFkZVBvcGluLCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdFx0LnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdFx0LmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MH0se2F1dG9BbHBoYToxLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHRcdC5mcm9tVG8oJHdwclVwZ3JhZGVQb3BpbiwgMC42LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDogLTI0fSwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJDbG9zZVVwZ3JhZGVQb3Bpbigpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKCk7XG5cblx0XHR2VEwuZnJvbVRvKCR3cHJVcGdyYWRlUG9waW4sIDAuNiwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6IDB9LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDotMjQsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdFx0LmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MX0se2F1dG9BbHBoYTowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdFx0LnNldCgkd3ByVXBncmFkZVBvcGluLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0XHQuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvKioqXG5cdCogU2lkZWJhciBvbi9vZmZcblx0KioqL1xuXHR2YXIgJHdwclNpZGViYXIgICAgPSAkKCAnLndwci1TaWRlYmFyJyApO1xuXHR2YXIgJHdwckJ1dHRvblRpcHMgPSAkKCcud3ByLWpzLXRpcHMnKTtcblxuXHQkd3ByQnV0dG9uVGlwcy5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByRGV0ZWN0VGlwcygkKHRoaXMpKTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByRGV0ZWN0VGlwcyhhRWxlbSl7XG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JHdwclNpZGViYXIuY3NzKCdkaXNwbGF5JywnYmxvY2snKTtcblx0XHRcdGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvbicgKTtcblx0XHR9XG5cdFx0ZWxzZXtcblx0XHRcdCR3cHJTaWRlYmFyLmNzcygnZGlzcGxheScsJ25vbmUnKTtcblx0XHRcdGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvZmYnICk7XG5cdFx0fVxuXHR9XG5cblxuXG5cdC8qKipcblx0KiBEZXRlY3QgQWRibG9ja1xuXHQqKiovXG5cblx0aWYoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ0xLZ09jQ1Jwd21BaicpKXtcblx0XHQkKCcud3ByLWFkYmxvY2snKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXHR9IGVsc2Uge1xuXHRcdCQoJy53cHItYWRibG9jaycpLmNzcygnZGlzcGxheScsICdibG9jaycpO1xuXHR9XG5cblx0dmFyICRhZGJsb2NrID0gJCgnLndwci1hZGJsb2NrJyk7XG5cdHZhciAkYWRibG9ja0Nsb3NlID0gJCgnLndwci1hZGJsb2NrLWNsb3NlJyk7XG5cblx0JGFkYmxvY2tDbG9zZS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJDbG9zZUFkYmxvY2tOb3RpY2UoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckNsb3NlQWRibG9ja05vdGljZSgpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC50bygkYWRibG9jaywgMSwge2F1dG9BbHBoYTowLCB4OjQwLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC50bygkYWRibG9jaywgMC40LCB7aGVpZ2h0OiAwLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS40Jylcblx0XHQgIC5zZXQoJGFkYmxvY2ssIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvLyBIYW5kbGUgZXhwYW5kL2NvbGxhcHNlIG9mIHJlY29tbWVuZGF0aW9ucyBsaXN0LlxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnI3dwci1yZWNvbW1lbmRhdGlvbnMtbG9hZC1tb3JlJywgZnVuY3Rpb24oKSB7XG5cdFx0bGV0ICRsaXN0ID0gJCgnLndwci1yZWNvbW1lbmRhdGlvbnNfX2xpc3QnKTtcblx0XHRsZXQgJGhpZGRlbkl0ZW1zID0gJGxpc3QuZmluZCgnLndwci1yZWNvbW1lbmRhdGlvbi1pdGVtOmd0KDIpJyk7XG5cblx0XHQvLyBUcmFjayBMb2FkIE1vcmUgYnV0dG9uIGNsaWNrXG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdyb2NrZXQgaW5zaWdodHMgcmVjb21tZW5kYXRpb25zIGxvYWQgbW9yZScsICdsb2FkX21vcmUnKTtcblx0XHR9XG5cblx0XHRpZiAoJGxpc3QuaGFzQ2xhc3MoJ2lzLWV4cGFuZGVkJykpIHtcblx0XHRcdCRoaWRkZW5JdGVtcy5zbGlkZVVwKDMwMCwgZnVuY3Rpb24oKSB7XG5cdFx0XHRcdCRsaXN0LnJlbW92ZUNsYXNzKCdpcy1leHBhbmRlZCcpO1xuXHRcdFx0fSk7XG5cdFx0XHQkKHRoaXMpLnJlbW92ZUNsYXNzKCdpcy1leHBhbmRlZCcpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCRsaXN0LmFkZENsYXNzKCdpcy1leHBhbmRlZCcpO1xuXHRcdCRoaWRkZW5JdGVtcy5zbGlkZURvd24oMzAwKTtcblx0XHQkKHRoaXMpLmFkZENsYXNzKCdpcy1leHBhbmRlZCcpO1xuXHR9KTtcblxuXHQvLyBUcmFjayBSb2NrZXQgSW5zaWdodHMgUmVjb21tZW5kYXRpb24gQWN0aXZhdGUgYnV0dG9uIGNsaWNrc1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yZWNvbW1lbmRhdGlvbi1pdGVtX19hY3RpdmF0ZScsIGZ1bmN0aW9uKCkge1xuXHRcdHZhciByZWNvbW1lbmRhdGlvbiA9ICQodGhpcykuZGF0YSgncmVjb21tZW5kYXRpb24nKSB8fCAndW5rbm93bic7XG5cblx0XHQvLyBJZiB0aGUgZWxlbWVudCBpcyB2aXNpYmxlLCBzY3JvbGwgdG8gdGhlIGVsZW1lbnQgd2l0aCBpZCBtYXRjaGluZyB0aGUgcmVjb21tZW5kYXRpb24gdmFsdWUuXG5cdFx0Ly8gRGVsYXkgc2Nyb2xsIGJ5IDEwMG1zIHRvIGFsbG93IG5hdmlnYXRpb24gdG8gdGhlIHNlY3Rpb24gdG8gY29tcGxldGUgYW5kIGVuc3VyZSB0aGUgdGFyZ2V0IGVsZW1lbnQgaXMgaW4gdmlldy5cblx0XHRzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuXHRcdFx0dmFyICR0YXJnZXQgPSAkKGAjJHtyZWNvbW1lbmRhdGlvbn1gKTtcblx0XHRcdFxuXHRcdFx0aWYgKCEkdGFyZ2V0Lmxlbmd0aCAmJiAhJHRhcmdldC5pcygnOnZpc2libGUnKSkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdCR0YXJnZXRbMF0uc2Nyb2xsSW50b1ZpZXcoeyBiZWhhdmlvcjogJ3Ntb290aCcsIGJsb2NrOiAnY2VudGVyJyB9KTtcblx0XHR9LCAxMDApO1xuXHRcdFxuXHRcdC8vIFRyYWNrIGRpcmVjdGx5IHdpdGggTWl4cGFuZWxcblx0XHRpZiAodHlwZW9mIG1peHBhbmVsICE9PSAndW5kZWZpbmVkJyAmJiBtaXhwYW5lbC50cmFjaykge1xuXHRcdFx0Ly8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW5cblx0XHRcdGlmICh0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgIT09ICd1bmRlZmluZWQnICYmIHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgJiYgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCAhPT0gJzAnKSB7XG5cdFx0XHRcdC8vIElkZW50aWZ5IHVzZXIgaWYgYXZhaWxhYmxlXG5cdFx0XHRcdGlmIChyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICYmIHR5cGVvZiBtaXhwYW5lbC5pZGVudGlmeSA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0XHRcdG1peHBhbmVsLmlkZW50aWZ5KHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdFxuXHRcdFx0XHQvLyBUcmFjayB0aGUgQnV0dG9uIENsaWNrZWQgZXZlbnRcblx0XHRcdFx0bWl4cGFuZWwudHJhY2soJ0J1dHRvbiBDbGlja2VkJywge1xuXHRcdFx0XHRcdCdidXR0b24nOiAncm9ja2V0IGluc2lnaHRzIHJlY29tbWVuZGF0aW9uJyxcblx0XHRcdFx0XHQncmVjb21tZW5kYXRpb24nOiByZWNvbW1lbmRhdGlvbixcblx0XHRcdFx0XHQncGx1Z2luJzogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0XHRcdCdicmFuZCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuXHRcdFx0XHRcdCdhcHBsaWNhdGlvbic6IHJvY2tldF9taXhwYW5lbF9kYXRhLmFwcCxcblx0XHRcdFx0XHQnY29udGV4dCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmNvbnRleHQsXG5cdFx0XHRcdFx0J3BhdGgnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wYXRoLFxuXHRcdFx0XHRcdCdpbnRlcmFjdGlvbl9jaGFubmVsJzogJ1VJJ1xuXHRcdFx0XHR9KTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIENUQSBjbGlja3MgZm9yIFJvY2tldCBJbnNpZ2h0cyBpbiB0aGUgc2V0dGluZ3Mgc2F2ZWQgbm90aWNlLlxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnI3JvY2tldF9yaV9uZXdfdGVzdF9zYXZlX3NldHRpbmdzX2xpbmsnLCBmdW5jdGlvbigpIHtcblx0XHQvLyBUcmFjayBkaXJlY3RseSB3aXRoIE1peHBhbmVsXG5cdFx0aWYgKHR5cGVvZiBtaXhwYW5lbCA9PT0gJ3VuZGVmaW5lZCcgfHwgIW1peHBhbmVsLnRyYWNrKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW5cblx0XHRpZiAodHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhID09PSAndW5kZWZpbmVkJyB8fCAhcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCB8fCByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkID09PSAnMCcpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBJZGVudGlmeSB1c2VyIGlmIGF2YWlsYWJsZVxuXHRcdGlmIChyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICYmIHR5cGVvZiBtaXhwYW5lbC5pZGVudGlmeSA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0bWl4cGFuZWwuaWRlbnRpZnkocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCk7XG5cdFx0fVxuXG5cdFx0Ly8gVHJhY2sgdGhlIEJ1dHRvbiBDbGlja2VkIGV2ZW50XG5cdFx0bWl4cGFuZWwudHJhY2soJ1JvY2tldCBJbnNpZ2h0cyBDVEEgY2xpY2sgZnJvbSBzZXR0aW5ncyBub3RpY2UnLCB7XG5cdFx0XHQnYnV0dG9uJzogJ0NUQSBvbiBzYXZlIHNldHRpbmdzIG5vdGljZScsXG5cdFx0XHQnc291cmNlJzogJ1NldHRpbmdzIFNhdmVkIE5vdGljZScsXG5cdFx0XHQncGx1Z2luJzogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0J2JyYW5kJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHQnYXBwbGljYXRpb24nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG5cdFx0XHQnY29udGV4dCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmNvbnRleHQsXG5cdFx0XHQncGF0aCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGgsXG5cdFx0XHQnaW50ZXJhY3Rpb25fY2hhbm5lbCc6ICdVSSdcblx0XHR9KTtcblx0fSk7XG59KTtcbiIsImNsYXNzIFJvY2tldE1peHBhbmVsIHtcblxuXHR0cmFja2VkVGFicyA9IFtcblx0XHQnZGFzaGJvYXJkJyxcblx0XHQncm9ja2V0X2luc2lnaHRzJyxcblx0XHQncGFnZV9jZG4nLFxuXHRcdCdmaWxlX29wdGltaXphdGlvbicsXG5cdFx0J21lZGlhJyxcblx0XHQncHJlbG9hZCcsXG5cdFx0J2FkdmFuY2VkX2NhY2hlJyxcblx0XHQnZGF0YWJhc2UnLFxuXHRcdCdoZWFydGJlYXQnLFxuXHRcdCdhZGRvbnMnLFxuXHRcdCdpbWFnaWZ5Jyxcblx0XHQndHV0b3JpYWxzJyxcblx0XHQncGx1Z2lucycsXG5cdFx0J3Rvb2xzJ1xuXHRdO1xuXG5cdGNvbnN0cnVjdG9yKCBjb25maWcgKSB7XG5cdFx0dGhpcy5jb25maWcgPSBjb25maWc7XG5cdH1cblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZXMgdGhlIGhhbmRsZXIuXG5cdCAqL1xuXHRpbml0KCkge1xuXHRcdGlmICggdHlwZW9mIG1peHBhbmVsID09PSAndW5kZWZpbmVkJyB8fCAhbWl4cGFuZWwudHJhY2sgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGlmICggIXRoaXMuY29uZmlnLm9wdGluX2VuYWJsZWQgfHwgdGhpcy5jb25maWcub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdG1peHBhbmVsLmlkZW50aWZ5KHRoaXMuY29uZmlnLnVzZXJfaWQpO1xuXHRcdHRoaXMuX2luaXRMaXN0ZW5lcnMoIHRoaXMgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplcyB0aGUgZXZlbnQgbGlzdGVuZXJzLlxuXHQgKlxuXHQgKiBAcGFyYW0gc2VsZiBpbnN0YW5jZSBvZiB0aGlzIG9iamVjdCwgdXNlZCBmb3IgYmluZGluZyBcInRoaXNcIiB0byB0aGUgbGlzdGVuZXJzLlxuXHQgKi9cblx0X2luaXRMaXN0ZW5lcnMoIHNlbGYgKSB7XG5cdFx0d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdoYXNoY2hhbmdlJywgc2VsZi5fb25IYXNoQ2hhbmdlLmJpbmQoIHNlbGYgKSApO1xuXHRcdHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsIHNlbGYuX29uUGFnZUxvYWQuYmluZCggc2VsZiApICk7XG5cdH1cblxuXHQvKipcblx0ICogRXZlbnQgbGlzdGVuZXIgd2hlbiB0aGUgaGFzaCBjaGFuZ2VkIGluIGEgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIEV2ZW50IGV2ZW50IEV2ZW50IGluc3RhbmNlLlxuXHQgKi9cblx0X29uSGFzaENoYW5nZSggZXZlbnQgKSB7XG5cdFx0Y29uc3Qgb2xkSGFzaCA9IHRoaXMuX2NsZWFuSGFzaChuZXcgVVJMKGV2ZW50Lm9sZFVSTCkuaGFzaCk7XG5cdFx0Y29uc3QgbmV3SGFzaCA9IHRoaXMuX2NsZWFuSGFzaChuZXcgVVJMKGV2ZW50Lm5ld1VSTCkuaGFzaCk7XG5cblx0XHRpZiAoICF0aGlzLl9jYW5UcmFja1RhYihuZXdIYXNoKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aGlzLl9zZW5kUGFnZVZpZXdlZEV2ZW50KHRoaXMuX2dldFNvdXJjZSggb2xkSGFzaCApLCBuZXdIYXNoKTtcblx0fVxuXG5cdF9vblBhZ2VMb2FkKCkge1xuXHRcdGNvbnN0IG5ld0hhc2ggPSB0aGlzLl9jbGVhbkhhc2god2luZG93LmxvY2F0aW9uLmhhc2gpO1xuXHRcdGlmICggIXRoaXMuX2NhblRyYWNrVGFiKG5ld0hhc2gpICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRoaXMuX3NlbmRQYWdlVmlld2VkRXZlbnQodGhpcy5fZ2V0U291cmNlKCksIG5ld0hhc2gpO1xuXHR9XG5cblx0X2NsZWFuSGFzaCggdGFiSGFzaCApIHtcblx0XHRpZiAoIXRhYkhhc2ggfHwgIXRhYkhhc2guc3RhcnRzV2l0aCgnIycpKSB7XG5cdFx0XHRyZXR1cm4gdGFiSGFzaDtcblx0XHR9XG5cdFx0cmV0dXJuIHRhYkhhc2guc3Vic3RyaW5nKDEpO1xuXHR9XG5cblx0X2NhblRyYWNrVGFiKHRhYkhhc2gpIHtcblx0XHRyZXR1cm4gdGhpcy50cmFja2VkVGFicy5pbmNsdWRlcyh0YWJIYXNoKTtcblx0fVxuXG5cdF9nZXRTb3VyY2UoIG9sZEhhc2ggPSAnJyApIHtcblx0XHRpZiAoIG9sZEhhc2ggKSB7XG5cdFx0XHRyZXR1cm4gYHNldHRpbmdzXyR7b2xkSGFzaH1gO1xuXHRcdH1cblxuXHRcdGxldCBzb3VyY2UgPSB0aGlzLl9nZXRTb3VyY2VGcm9tUXVlcnlTdHJpbmdBbmRSZW1vdmVJdCgpO1xuXHRcdGlmICggc291cmNlICkge1xuXHRcdFx0cmV0dXJuIHNvdXJjZTtcblx0XHR9XG5cblx0XHRyZXR1cm4gdGhpcy5fZ2V0U291cmNlRnJvbVJlZmVycmVyKCk7XG5cdH1cblxuXHRfZ2V0U291cmNlRnJvbVF1ZXJ5U3RyaW5nQW5kUmVtb3ZlSXQoKSB7XG5cdFx0Y29uc3QgdXJsID0gbmV3IFVSTCh3aW5kb3cubG9jYXRpb24uaHJlZik7XG5cdFx0Y29uc3QgdXJsUGFyYW1zID0gdXJsLnNlYXJjaFBhcmFtcztcblxuXHRcdC8vIDEuIENoZWNrIGZvciBleHBsaWNpdCBzb3VyY2UgcGFyYW1cblx0XHRpZiAoIXVybFBhcmFtcy5oYXMoJ3JvY2tldF9zb3VyY2UnKSkge1xuXHRcdFx0cmV0dXJuICcnO1xuXHRcdH1cblxuXHRcdC8vIDIuIEdldCB0aGUgdmFsdWVcblx0XHRjb25zdCBzb3VyY2VWYWx1ZSA9IHVybFBhcmFtcy5nZXQoJ3JvY2tldF9zb3VyY2UnKTtcblxuXHRcdC8vIDMuIERlbGV0ZSB0aGUgcGFyYW1ldGVyIGZyb20gdGhlIFVSTFNlYXJjaFBhcmFtcyBvYmplY3Rcblx0XHR1cmxQYXJhbXMuZGVsZXRlKCdyb2NrZXRfc291cmNlJyk7XG5cblx0XHQvLyA0LiBVcGRhdGUgdGhlIGJyb3dzZXIncyBVUkwgdXNpbmcgdGhlIEhpc3RvcnkgQVBJXG5cdFx0Ly8gVGhpcyByZW1vdmVzIHRoZSBwYXJhbWV0ZXIgZnJvbSB0aGUgVVJMIGJhciB3aXRob3V0IHJlbG9hZGluZyB0aGUgcGFnZS5cblx0XHR3aW5kb3cuaGlzdG9yeS5yZXBsYWNlU3RhdGUobnVsbCwgJycsIHVybC5zZWFyY2ggPyB1cmwuaHJlZiA6IHVybC5wYXRobmFtZSk7XG5cblx0XHQvLyA1LiBSZXR1cm4gdGhlIHJldHJpZXZlZCB2YWx1ZVxuXHRcdHJldHVybiBzb3VyY2VWYWx1ZTtcblx0fVxuXG5cdF9nZXRTb3VyY2VGcm9tUmVmZXJyZXIoKSB7XG5cdFx0Y29uc3QgcmVmZXJyZXIgPSBkb2N1bWVudC5yZWZlcnJlcjtcblx0XHRpZiAoIXJlZmVycmVyKSB7XG5cdFx0XHRyZXR1cm4gJ25vcmVmZXJyZXInO1xuXHRcdH1cblx0XHRpZiAoIXJlZmVycmVyLmluY2x1ZGVzKHdpbmRvdy5sb2NhdGlvbi5ob3N0bmFtZSkpIHtcblx0XHRcdHJldHVybiAnZXh0ZXJuYWwnO1xuXHRcdH1cblx0XHRyZXR1cm4gJ2ludGVybmFsJztcblx0fVxuXG5cdF9zZW5kUGFnZVZpZXdlZEV2ZW50KHNvdXJjZSwgbmV3SGFzaCkge1xuXHRcdG1peHBhbmVsLnRyYWNrKCdQYWdlIFZpZXdlZCcsIHtcblx0XHRcdHBhdGg6IGAvd3AtYWRtaW4vb3B0aW9ucy1nZW5lcmFsLnBocD9wYWdlPXdwcm9ja2V0IyR7bmV3SGFzaH1gLFxuXHRcdFx0cGFnZV9uYW1lOiBuZXdIYXNoLnJlcGxhY2UoJ18nLCAnICcpLFxuXHRcdFx0c291cmNlOiBzb3VyY2UsXG5cdFx0XHRwbHVnaW46IHJvY2tldF9taXhwYW5lbF9kYXRhLnBsdWdpbixcblx0XHRcdGJyYW5kOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5icmFuZCxcblx0XHRcdGFwcGxpY2F0aW9uOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG5cdFx0XHRjb250ZXh0OiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuXHRcdFx0aW50ZXJhY3Rpb25fY2hhbm5lbDogJ1VJJ1xuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIE5hbWVkIHN0YXRpYyBjb25zdHJ1Y3RvciB0byBlbmNhcHN1bGF0ZSBob3cgdG8gY3JlYXRlIHRoZSBvYmplY3QuXG5cdCAqL1xuXHRzdGF0aWMgcnVuKCkge1xuXHRcdC8vIEJhaWwgb3V0IGlmIHRoZSBjb25maWd1cmF0aW9uIG5vdCBwYXNzZWQgZnJvbSB0aGUgc2VydmVyLlxuXHRcdGlmICggdHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhID09PSAndW5kZWZpbmVkJyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBpbnN0YW5jZSA9IG5ldyBSb2NrZXRNaXhwYW5lbCggcm9ja2V0X21peHBhbmVsX2RhdGEgKTtcblx0XHRpbnN0YW5jZS5pbml0KCk7XG5cdH1cbn1cblxuUm9ja2V0TWl4cGFuZWwucnVuKCk7XG4iLCJkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsIGZ1bmN0aW9uICgpIHtcblxuICAgIHZhciAkcGFnZU1hbmFnZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiLndwci1Db250ZW50XCIpO1xuICAgIGlmKCRwYWdlTWFuYWdlcil7XG4gICAgICAgIG5ldyBQYWdlTWFuYWdlcigkcGFnZU1hbmFnZXIpO1xuICAgIH1cblxufSk7XG5cblxuLyotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSpcXFxuXHRcdENMQVNTIFBBR0VNQU5BR0VSXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXG4vKipcbiAqIE1hbmFnZXMgdGhlIGRpc3BsYXkgb2YgcGFnZXMgLyBzZWN0aW9uIGZvciBXUCBSb2NrZXQgcGx1Z2luXG4gKlxuICogUHVibGljIG1ldGhvZCA6XG4gICAgIGRldGVjdElEIC0gRGV0ZWN0IElEIHdpdGggaGFzaFxuICAgICBnZXRCb2R5VG9wIC0gR2V0IGJvZHkgdG9wIHBvc2l0aW9uXG5cdCBjaGFuZ2UgLSBEaXNwbGF5cyB0aGUgY29ycmVzcG9uZGluZyBwYWdlXG4gKlxuICovXG5cbmZ1bmN0aW9uIFBhZ2VNYW5hZ2VyKGFFbGVtKSB7XG5cbiAgICB2YXIgcmVmVGhpcyA9IHRoaXM7XG5cbiAgICB0aGlzLiRib2R5ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1ib2R5Jyk7XG4gICAgdGhpcy4kbWVudUl0ZW1zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1tZW51SXRlbScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudCA+IGZvcm0gPiAjd3ByLW9wdGlvbnMtc3VibWl0Jyk7XG4gICAgdGhpcy4kcGFnZXMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLVBhZ2UnKTtcbiAgICB0aGlzLiRzaWRlYmFyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1TaWRlYmFyJyk7XG4gICAgdGhpcy4kY29udGVudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudCcpO1xuICAgIHRoaXMuJHRpcHMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQtdGlwcycpO1xuICAgIHRoaXMuJGxpbmtzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1ib2R5IGEnKTtcbiAgICB0aGlzLiRtZW51SXRlbSA9IG51bGw7XG4gICAgdGhpcy4kcGFnZSA9IG51bGw7XG4gICAgdGhpcy5wYWdlSWQgPSBudWxsO1xuICAgIHRoaXMuYm9keVRvcCA9IDA7XG4gICAgdGhpcy5idXR0b25UZXh0ID0gdGhpcy4kc3VibWl0QnV0dG9uLnZhbHVlO1xuXG4gICAgcmVmVGhpcy5nZXRCb2R5VG9wKCk7XG5cbiAgICAvLyBJZiB1cmwgcGFnZSBjaGFuZ2VcbiAgICB3aW5kb3cub25oYXNoY2hhbmdlID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIHJlZlRoaXMuZGV0ZWN0SUQoKTtcbiAgICB9XG5cbiAgICAvLyBJZiBoYXNoIGFscmVhZHkgZXhpc3QgKGFmdGVyIHJlZnJlc2ggcGFnZSBmb3IgZXhhbXBsZSlcbiAgICBpZih3aW5kb3cubG9jYXRpb24uaGFzaCl7XG4gICAgICAgIHRoaXMuYm9keVRvcCA9IDA7XG4gICAgICAgIHRoaXMuZGV0ZWN0SUQoKTtcbiAgICB9XG4gICAgZWxzZXtcbiAgICAgICAgdmFyIHNlc3Npb24gPSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLWhhc2gnKTtcbiAgICAgICAgdGhpcy5ib2R5VG9wID0gMDtcblxuICAgICAgICBpZihzZXNzaW9uKXtcbiAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gc2Vzc2lvbjtcbiAgICAgICAgICAgIHRoaXMuZGV0ZWN0SUQoKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNle1xuICAgICAgICAgICAgdGhpcy4kbWVudUl0ZW1zWzBdLmNsYXNzTGlzdC5hZGQoJ2lzQWN0aXZlJyk7XG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCAnZGFzaGJvYXJkJyk7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24uaGFzaCA9ICcjZGFzaGJvYXJkJztcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIENsaWNrIGxpbmsgc2FtZSBoYXNoXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRsaW5rcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRsaW5rc1tpXS5vbmNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICByZWZUaGlzLmdldEJvZHlUb3AoKTtcbiAgICAgICAgICAgIHZhciBocmVmU3BsaXQgPSB0aGlzLmhyZWYuc3BsaXQoJyMnKVsxXTtcbiAgICAgICAgICAgIGlmKGhyZWZTcGxpdCA9PSByZWZUaGlzLnBhZ2VJZCAmJiBocmVmU3BsaXQgIT0gdW5kZWZpbmVkKXtcbiAgICAgICAgICAgICAgICByZWZUaGlzLmRldGVjdElEKCk7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfVxuICAgICAgICB9O1xuICAgIH1cblxuICAgIC8vIENsaWNrIGxpbmtzIG5vdCBXUCByb2NrZXQgdG8gcmVzZXQgaGFzaFxuICAgIHZhciAkb3RoZXJsaW5rcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJyNhZG1pbm1lbnVtYWluIGEsICN3cGFkbWluYmFyIGEnKTtcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8ICRvdGhlcmxpbmtzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICRvdGhlcmxpbmtzW2ldLm9uY2xpY2sgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd3cHItaGFzaCcsICcnKTtcbiAgICAgICAgfTtcbiAgICB9XG5cbiAgICBkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnd3ByLWNkbi1zdGF0ZS1jaGFuZ2UnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgcmVmVGhpcy51cGRhdGVTdWJtaXREaXNhYmxlZFN0YXRlKCk7XG4gICAgfSApO1xuXG59XG5cblxuLypcbiogUGFnZSBkZXRlY3QgSURcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZGV0ZWN0SUQgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnBhZ2VJZCA9IHdpbmRvdy5sb2NhdGlvbi5oYXNoLnNwbGl0KCcjJylbMV07XG4gICAgdGhpcy5wYWdlSWQgPSB0aGlzLnBhZ2VJZC5pbmNsdWRlcygnPScpID8gdGhpcy5wYWdlSWQuc3BsaXQoJz0nKVswXSA6IHRoaXMucGFnZUlkO1xuICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd3cHItaGFzaCcsIHRoaXMucGFnZUlkKTtcblxuICAgIHRoaXMuJHBhZ2UgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLVBhZ2UjJyArIHRoaXMucGFnZUlkKTtcbiAgICB0aGlzLiRtZW51SXRlbSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3cHItbmF2LScgKyB0aGlzLnBhZ2VJZCk7XG5cbiAgICB0aGlzLmNoYW5nZSgpO1xufVxuXG5cblxuLypcbiogR2V0IGJvZHkgdG9wIHBvc2l0aW9uXG4qL1xuUGFnZU1hbmFnZXIucHJvdG90eXBlLmdldEJvZHlUb3AgPSBmdW5jdGlvbigpIHtcbiAgICB2YXIgYm9keVBvcyA9IHRoaXMuJGJvZHkuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgdGhpcy5ib2R5VG9wID0gYm9keVBvcy50b3AgKyB3aW5kb3cucGFnZVlPZmZzZXQgLSA0NzsgLy8gI3dwYWRtaW5iYXIgKyBwYWRkaW5nLXRvcCAud3ByLXdyYXAgLSAxIC0gNDdcbn1cblxuXG5cbi8qXG4qIFBhZ2UgY2hhbmdlXG4qL1xuUGFnZU1hbmFnZXIucHJvdG90eXBlLmNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuXG4gICAgdmFyIHJlZlRoaXMgPSB0aGlzO1xuICAgIGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5zY3JvbGxUb3AgPSByZWZUaGlzLmJvZHlUb3A7XG5cbiAgICAvLyBIaWRlIG90aGVyIHBhZ2VzXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRwYWdlcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRwYWdlc1tpXS5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuJG1lbnVJdGVtcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRtZW51SXRlbXNbaV0uY2xhc3NMaXN0LnJlbW92ZSgnaXNBY3RpdmUnKTtcbiAgICB9XG5cbiAgICAvLyBTaG93IGN1cnJlbnQgZGVmYXVsdCBwYWdlXG4gICAgdGhpcy4kcGFnZS5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG5cbiAgICBpZiAoIG51bGwgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicgKSApIHtcbiAgICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJywgJ29uJyApO1xuICAgIH1cblxuICAgIGlmICggJ29uJyA9PT0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ3dwci1zaG93LXNpZGViYXInKSApIHtcbiAgICAgICAgdGhpcy4kc2lkZWJhci5zdHlsZS5kaXNwbGF5ID0gJ2ZsZXgnO1xuICAgIH0gZWxzZSBpZiAoICdvZmYnID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyN3cHItanMtdGlwcycpLnJlbW92ZUF0dHJpYnV0ZSggJ2NoZWNrZWQnICk7XG4gICAgfVxuXG4gICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICB0aGlzLiRtZW51SXRlbS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZSA9IHRoaXMuYnV0dG9uVGV4dDtcbiAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5hZGQoJ2lzTm90RnVsbCcpO1xuXG4gICAgY29uc3QgcGFnZXNXaXRob3V0U3VibWl0ID0gW1xuICAgICAgICAnZGFzaGJvYXJkJyxcbiAgICAgICAgJ2FkZG9ucycsXG4gICAgICAgICdkYXRhYmFzZScsXG4gICAgICAgICd0b29scycsXG4gICAgICAgICdhZGRvbnMnLFxuICAgICAgICAnaW1hZ2lmeScsXG4gICAgICAgICd0dXRvcmlhbHMnLFxuICAgICAgICAncGx1Z2lucycsXG4gICAgXTtcblxuICAgIGNvbnN0IHBhZ2VzV2l0aG91dFNpZGViYXJUb2dnbGUgPSBbXG4gICAgICAgICdkYXNoYm9hcmQnLFxuICAgICAgICAnaW1hZ2lmeScsXG4gICAgICAgICdwYWdlX2NkbicsXG4gICAgXTtcblxuICAgIC8vIEV4Y2VwdGlvbiBmb3IgZGFzaGJvYXJkXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXNoYm9hcmRcIil7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kY29udGVudC5jbGFzc0xpc3QucmVtb3ZlKCdpc05vdEZ1bGwnKTtcbiAgICB9XG5cbiAgICBpZiAodGhpcy5wYWdlSWQgPT0gXCJpbWFnaWZ5XCIpIHtcbiAgICAgICAgdGhpcy4kc2lkZWJhci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cblxuICAgIGlmIChwYWdlc1dpdGhvdXRTaWRlYmFyVG9nZ2xlLmluY2x1ZGVzKHRoaXMucGFnZUlkKSkge1xuICAgICAgICB0aGlzLiR0aXBzLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgaWYgKHBhZ2VzV2l0aG91dFN1Ym1pdC5pbmNsdWRlcyh0aGlzLnBhZ2VJZCkpIHtcbiAgICAgICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgdGhpcy51cGRhdGVTdWJtaXREaXNhYmxlZFN0YXRlKCk7XG5cblx0Ly8gRGlzcGF0Y2ggY3VzdG9tIGV2ZW50IGFmdGVyIHBhZ2UgbmF2aWdhdGlvbiBmb3Igb3RoZXIgc2NyaXB0cyB0byBob29rIGludG8uXG5cdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQobmV3IEN1c3RvbUV2ZW50KCdyb2NrZXRKc0FmdGVyUGFnZU5hdmlnYXRpb24nLCB7XG5cdFx0ZGV0YWlsOiB7XG5cdFx0XHRwYWdlSWQ6IHRoaXMucGFnZUlkLFxuXHRcdFx0c3VibWl0QnV0dG9uOiB0aGlzLiRzdWJtaXRCdXR0b24sXG5cdFx0fVxuXHR9ICkgKTtcbn07XG5cblxuLypcbiogVXBkYXRlIHN1Ym1pdCBidXR0b24gZGlzYWJsZWQgc3RhdGVcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUudXBkYXRlU3VibWl0RGlzYWJsZWRTdGF0ZSA9IGZ1bmN0aW9uKCkge1xuXHRpZiAoIXRoaXMuJHN1Ym1pdEJ1dHRvbiB8fCAnbm9uZScgPT09IHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5KSB7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0dmFyIGlzQ2RuUGFnZSA9ICdwYWdlX2NkbicgPT09IHRoaXMucGFnZUlkO1xuXHR2YXIgcGF1c2VkUm9ja2V0Q2RuQmxvY2sgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFxuXHRcdCcud3ByLVBhZ2UjcGFnZV9jZG4gLndwci1ub3RpY2Uud3ByLXJpLW5vdGljZS53cHItY2RuLWV4cGlyZWRfX25vdGljZSdcblx0KTtcblxuXHR0aGlzLiRzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBpc0NkblBhZ2UgJiYgISFwYXVzZWRSb2NrZXRDZG5CbG9jaztcbn07XG4iLCIvKipcbiAqIFJlY29tbWVuZGF0aW9ucyBXaWRnZXQgSGFuZGxlclxuICpcbiAqIExpc3RlbnMgZm9yIEdsb2JhbCBTY29yZSB1cGRhdGVzIGFuZCBmZXRjaGVzL3VwZGF0ZXMgcmVjb21tZW5kYXRpb25zIGR5bmFtaWNhbGx5LlxuICovXG52YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSByZWNvbW1lbmRhdGlvbnMgd2lkZ2V0IFVJIGJhc2VkIG9uIHRoZSBmZXRjaGVkIGRhdGEuXG5cdCAqXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBkYXRhIC0gVGhlIHJlY29tbWVuZGF0aW9ucyBkYXRhIGZyb20gdGhlIEFQSS5cblx0ICogQHBhcmFtIHtBcnJheX0gZGF0YS5yZWNvbW1lbmRhdGlvbnMgLSBBcnJheSBvZiByZWNvbW1lbmRhdGlvbnMgZGV0YWlscy5cblx0ICogQHBhcmFtIHtzdHJpbmd9IGRhdGEucmVjb21tZW5kYXRpb25zLmh0bWwgLSBSZWNvbW1lbmRhdGlvbnMgSFRNTC5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZVJlY29tbWVuZGF0aW9uc1dpZGdldChkYXRhKSB7XG5cdFx0Y29uc3Qgd2lkZ2V0ID0gJCgnLndwci1yZWNvbW1lbmRhdGlvbnMnKTtcblxuXHRcdGlmICghd2lkZ2V0IHx8ICFkYXRhPy5yZWNvbW1lbmRhdGlvbnM/Lmh0bWwpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBVcGRhdGUgdGhlIHdpZGdldCBjb250ZW50IHdpdGggdGhlIG5ldyByZWNvbW1lbmRhdGlvbnMgSFRNTFxuXHRcdHdpZGdldC5yZXBsYWNlV2l0aChkYXRhPy5yZWNvbW1lbmRhdGlvbnM/Lmh0bWwpO1xuXHR9XG5cblxuXG5cdC8qKlxuXHQgKiBGZXRjaGVzIHRoZSBjdXJyZW50IHJlY29tbWVuZGF0aW9ucyBzdGF0dXMgZnJvbSB0aGUgUkVTVCBBUEkuXG5cdCAqL1xuXHRmdW5jdGlvbiBmZXRjaFJlY29tbWVuZGF0aW9uc1N0YXR1cygpIHtcblx0XHQvLyBVc2UgV29yZFByZXNzIFJFU1QgQVBJIGNsaWVudCBpZiBhdmFpbGFibGVcblx0XHRpZiAod2luZG93LndwICYmIHdpbmRvdy53cC5hcGlGZXRjaCkge1xuXHRcdFx0d2luZG93LndwLmFwaUZldGNoKHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcmVjb21tZW5kYXRpb25zJ1xuXHRcdFx0fSlcblx0XHRcdFx0LnRoZW4oZnVuY3Rpb24gKGRhdGEpIHtcblx0XHRcdFx0XHR1cGRhdGVSZWNvbW1lbmRhdGlvbnNXaWRnZXQoZGF0YSk7XG5cdFx0XHRcdH0pXG5cdFx0XHRcdC5jYXRjaChmdW5jdGlvbiAoZXJyb3IpIHtcblx0XHRcdFx0XHRjb25zb2xlLmVycm9yKCdGYWlsZWQgdG8gZmV0Y2ggcmVjb21tZW5kYXRpb25zIHN0YXR1czonLCBlcnJvcik7XG5cdFx0XHRcdH0pO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHQvLyBGYWxsYmFjayB0byBmZXRjaCBBUElcblx0XHRcdGZldGNoKHdpbmRvdy53cEFwaVNldHRpbmdzPy5yb290ICsgJ3dwLXJvY2tldC92MS9yZWNvbW1lbmRhdGlvbnMnLCB7XG5cdFx0XHRcdGhlYWRlcnM6IHtcblx0XHRcdFx0XHQnWC1XUC1Ob25jZSc6IHdpbmRvdy53cEFwaVNldHRpbmdzPy5ub25jZSB8fCAnJ1xuXHRcdFx0XHR9XG5cdFx0XHR9KVxuXHRcdFx0XHQudGhlbihmdW5jdGlvbiAocmVzcG9uc2UpIHtcblx0XHRcdFx0XHRpZiAoIXJlc3BvbnNlLm9rKSB7XG5cdFx0XHRcdFx0XHR0aHJvdyBuZXcgRXJyb3IoJ05ldHdvcmsgcmVzcG9uc2Ugd2FzIG5vdCBvaycpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRyZXR1cm4gcmVzcG9uc2UuanNvbigpO1xuXHRcdFx0XHR9KVxuXHRcdFx0XHQudGhlbihmdW5jdGlvbiAoZGF0YSkge1xuXHRcdFx0XHRcdHVwZGF0ZVJlY29tbWVuZGF0aW9uc1dpZGdldChkYXRhKTtcblx0XHRcdFx0fSlcblx0XHRcdFx0LmNhdGNoKGZ1bmN0aW9uIChlcnJvcikge1xuXHRcdFx0XHRcdGNvbnNvbGUuZXJyb3IoJ0ZhaWxlZCB0byBmZXRjaCByZWNvbW1lbmRhdGlvbnMgc3RhdHVzOicsIGVycm9yKTtcblx0XHRcdFx0fSk7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIExpc3RlbiBmb3IgR2xvYmFsIFNjb3JlIHVwZGF0ZSBldmVudC5cblx0ICogVGhpcyBpcyBmaXJlZCBieSBhamF4LmpzIHdoZW4gdGhlIEdsb2JhbCBTY29yZSBwb2xsaW5nIGRldGVjdHMgYSBjaGFuZ2UuXG5cdCAqL1xuXHQkKGRvY3VtZW50KS5vbignd3ByR2xvYmFsU2NvcmVVcGRhdGVkIHJvY2tldC1pbnNpZ2h0cy1wYWdlLWFkZGVkIHJvY2tldC1pbnNpZ2h0cy1wYWdlLXJldGVzdCcsICgpID0+IHtcblx0XHRmZXRjaFJlY29tbWVuZGF0aW9uc1N0YXR1cygpO1xuXHR9KVxufSk7XG4iLCIoICggZG9jdW1lbnQgKSA9PiB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHQvKipcblx0ICogUG9sbHMgdGhlIFJvY2tldENETiBzdWJzY3JpcHRpb24gZW5kcG9pbnQgdW50aWwgaXNfbG9hZGluZyBiZWNvbWVzIGZhbHNlLlxuXHQgKi9cblx0Y2xhc3MgUm9ja2V0Q0ROU3Vic2NyaXB0aW9uUG9sbGVyIHtcblx0XHRjb25zdHJ1Y3RvcigpIHtcblx0XHRcdHRoaXMucGF0aCA9ICcvd3Atcm9ja2V0L3YxL3JvY2tldGNkbi9zdWJzY3JpcHRpb24nO1xuXHRcdFx0dGhpcy5wb2xsSW50ZXJ2YWwgPSAxMDAwMDsgLy8gMTAgc2Vjb25kcy5cblx0XHRcdHRoaXMubWF4UmV0cmllcyA9IDYwOyAvLyAxMCBtaW51dGVzIHRvdGFsLlxuXHRcdFx0dGhpcy50aW1lcklkID0gbnVsbDtcblx0XHRcdHRoaXMucmV0cnlDb3VudCA9IDA7XG5cblx0XHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdyb2NrZXRDRE5TdWJzY3JpcHRpb25Mb2FkaW5nJywgKCkgPT4gdGhpcy5zdGFydCgpICk7XG5cblx0XHRcdC8vIFJlLXRyaWdnZXIgcG9sbGluZyBhZnRlciBwYWdlIHJlZnJlc2guXG5cdFx0XHRjb25zdCBjbGFzc2VzID0gW1xuXHRcdFx0XHQnLndwci1pY29uLW9yYW5nZS1sb2FkZXInLFxuXHRcdFx0XHQnLndwci1jZG4tYnVpbHQtaW4tLWRpc2FibGVkJyxcblx0XHRcdF07XG5cblx0XHRcdGNvbnN0IGFsbFByZXNlbnQgPSBjbGFzc2VzLmV2ZXJ5KCBjbHMgPT4gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggY2xzICkgIT09IG51bGwgKTtcblxuXHRcdFx0aWYgKCBhbGxQcmVzZW50ICkge1xuXHRcdFx0XHR0aGlzLnN0YXJ0KClcblx0XHRcdH1cblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBTdGFydHMgcG9sbGluZy5cblx0XHQgKi9cblx0XHRzdGFydCgpIHtcblx0XHRcdGlmICggdGhpcy50aW1lcklkICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdHRoaXMucmV0cnlDb3VudCA9IDA7XG5cdFx0XHR0aGlzLnBvbGwoKTtcblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBTdG9wcyBwb2xsaW5nLlxuXHRcdCAqL1xuXHRcdHN0b3AoKSB7XG5cdFx0XHRpZiAoIHRoaXMudGltZXJJZCApIHtcblx0XHRcdFx0Y2xlYXJUaW1lb3V0KCB0aGlzLnRpbWVySWQgKTtcblx0XHRcdFx0dGhpcy50aW1lcklkID0gbnVsbDtcblx0XHRcdH1cblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBQZXJmb3JtcyBhIHNpbmdsZSBwb2xsIGFuZCBzY2hlZHVsZXMgdGhlIG5leHQgb25lIGlmIHN0aWxsIGxvYWRpbmcuXG5cdFx0ICovXG5cdFx0cG9sbCgpIHtcblx0XHRcdGlmICggdGhpcy5yZXRyeUNvdW50ID49IHRoaXMubWF4UmV0cmllcyApIHtcblx0XHRcdFx0dGhpcy5zdG9wKCk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0dGhpcy5yZXRyeUNvdW50Kys7XG5cblx0XHRcdHdpbmRvdy53cC5hcGlGZXRjaCgge1xuXHRcdFx0XHRwYXRoOiB0aGlzLnBhdGgsXG5cdFx0XHRcdG1ldGhvZDogJ0dFVCcsXG5cdFx0XHR9ICkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdFx0aWYgKCAhIHJlc3BvbnNlIHx8ICEgcmVzcG9uc2UuaXNfbG9hZGluZyApIHtcblx0XHRcdFx0XHR0aGlzLnN0b3AoKTtcblx0XHRcdFx0XHR3aW5kb3cubG9jYXRpb24ucmVsb2FkKCk7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0dGhpcy50aW1lcklkID0gc2V0VGltZW91dCggKCkgPT4gdGhpcy5wb2xsKCksIHRoaXMucG9sbEludGVydmFsICk7XG5cdFx0XHR9ICkuY2F0Y2goICgpID0+IHtcblx0XHRcdFx0dGhpcy50aW1lcklkID0gc2V0VGltZW91dCggKCkgPT4gdGhpcy5wb2xsKCksIHRoaXMucG9sbEludGVydmFsICk7XG5cdFx0XHR9ICk7XG5cdFx0fVxuXHR9XG5cblx0bmV3IFJvY2tldENETlN1YnNjcmlwdGlvblBvbGxlcigpO1xufSApKCBkb2N1bWVudCApOyIsIi8qZXNsaW50LWVudiBlczYsIGJyb3dzZXIqL1xuLyogZ2xvYmFsIE1pY3JvTW9kYWwsIG1peHBhbmVsLCByb2NrZXRfbWl4cGFuZWxfZGF0YSwgcm9ja2V0X2FqYXhfZGF0YSwgYWpheHVybCAqL1xuKCAoIGRvY3VtZW50LCB3aW5kb3cgKSA9PiB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHRjb25zdCBCQU5ORVJfU1RBVEUgPSB7XG5cdFx0T1BFTkVEOiBmYWxzZSwgICAgLy8gQmlnIENUQSAtIG9wZW5lZCBzdGF0ZVxuXHRcdENPTExBUFNFRDogdHJ1ZSAgIC8vIFNtYWxsIENUQSAtIGNvbGxhcHNlZCBzdGF0ZVxuXHR9O1xuXG5cdC8vIFJlZ2lzdGVyIGVhcmx5IHNvIHdlIGNhdGNoIHRoZSB3cHItY2RuLXN0YXRlLWNoYW5nZSBldmVudC5cblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ3dwci1jZG4tc3RhdGUtY2hhbmdlJywgdHJhY2tDRE5Nb2RlU2VsZWN0aW9uICk7XG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdyb2NrZXRDRE5CYW5uZXJBdXRvRXhwYW5kZWQnLCAoKSA9PiB0cmFja1JvY2tldENETlVwc2VsbEJhbm5lckV4cGFuZGVkKCdhdXRvX2xpbWl0X3JlYWNoZWQnKSApO1xuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAncm9ja2V0Q0ROQmFubmVyQXV0b0NvbGxhcHNlZCcsICgpID0+IHRyYWNrUm9ja2V0Q0ROVXBzZWxsQmFubmVyQ29sbGFwc2VkKCAnYXV0b19saW1pdF9yZWxlYXNlZCcgKSApO1xuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAncm9ja2V0Q0ROQmFubmVyRmlyc3RWaXNpYmxlJywgKCkgPT4gdHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJWaWV3ZWQoIEJBTk5FUl9TVEFURS5DT0xMQVBTRUQgKSApO1xuXG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLXJvY2tldGNkbi1vcGVuJyApLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRlbC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdFx0Y29uc3QgaXNDVEEgPSBlbC5jbGFzc0xpc3QuY29udGFpbnMoICd3cHItcm9ja2V0Y2RuLXByaWNpbmctLWN0YScgKTtcblx0XHRcdFx0Y2hlY2tCdXR0b25VcmxBbmRPcGVuKCBpc0NUQSApO1xuXHRcdFx0fSApO1xuXHRcdH0gKTtcblxuXHRcdC8vIEFsd2F5cyBpbml0aWFsaXplIE1pY3JvTW9kYWwgdG8gc2V0IHVwIGNsb3NlIGhhbmRsZXJzXG5cdFx0TWljcm9Nb2RhbC5pbml0KCB7XG5cdFx0XHRkaXNhYmxlU2Nyb2xsOiB0cnVlXG5cdFx0fSApO1xuXG5cdFx0bWF5YmVPcGVuTW9kYWxGcm9tVVJMKCk7XG5cblx0XHQvLyBPbmx5IGF1dG8tb3BlbiBtb2RhbCBpZiB0aGVyZSdzIG5vIGRpcmVjdCBidXR0b24gVVJMXG5cdFx0aWYgKCAhIHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgfHwgd2luZG93LnJvY2tldGNkbkJ1dHRvblVybCA9PT0gJycgKSB7XG5cdFx0XHRtYXliZU9wZW5Nb2RhbCgpO1xuXG5cdFx0XHRjb25zdCBpZnJhbWUgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0Y2RuLWlmcmFtZScpO1xuXHRcdFx0Y29uc3QgbG9hZGVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwci1yb2NrZXRjZG4tbW9kYWwtbG9hZGVyJyk7XG5cdFx0XHRpZiAoIGlmcmFtZSAmJiBsb2FkZXIgKSB7XG5cdFx0XHRcdGlmcmFtZS5hZGRFdmVudExpc3RlbmVyKCdsb2FkJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRcdFx0bG9hZGVyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG5cdFx0XHRcdH0pO1xuXHRcdFx0fVxuXHRcdH1cblx0fSApO1xuXG5cdC8qKlxuXHQgKiBDaGVja3MgaWYgdGhlIHVzZXIgaXMgY3VycmVudGx5IG9uIHRoZSBDRE4gdGFiLlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIG9uIENETiB0YWIsIGZhbHNlIG90aGVyd2lzZS5cblx0ICovXG5cdGZ1bmN0aW9uIGlzT25DRE5UYWIoKSB7XG5cdFx0cmV0dXJuIHdpbmRvdy5sb2NhdGlvbi5oYXNoID09PSAnI3BhZ2VfY2RuJztcblx0fVxuXG5cdGZ1bmN0aW9uIG1heWJlVHJhY2tCYW5uZXJWaWV3KCkge1xuXHRcdGNvbnN0IHNtYWxsQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YS1zbWFsbCcgKTtcblx0XHRjb25zdCBiaWdDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhJyApO1xuXG5cdFx0Ly8gT25seSB0cmFjayBpZiBvbmUgb2YgdGhlIGJhbm5lcnMgaXMgdmlzaWJsZS5cblx0XHRpZiAoIGJpZ0NUQSAmJiAhIGJpZ0NUQS5jbGFzc0xpc3QuY29udGFpbnMoICd3cHItaXNIaWRkZW4nICkgKSB7XG5cdFx0XHR0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCggQkFOTkVSX1NUQVRFLk9QRU5FRCApO1xuXHRcdH0gZWxzZSBpZiAoIHNtYWxsQ1RBICYmICEgc21hbGxDVEEuY2xhc3NMaXN0LmNvbnRhaW5zKCAnd3ByLWlzSGlkZGVuJyApICkge1xuXHRcdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJWaWV3ZWQoIEJBTk5FUl9TVEFURS5DT0xMQVBTRUQgKTtcblx0XHR9XG5cdH1cblxuXHR3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lciggJ2xvYWQnLCAoKSA9PiB7XG5cdFx0bGV0IG9wZW5DVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tb3Blbi1jdGEnICksXG5cdFx0XHRjbG9zZUNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jbG9zZS1jdGEnICksXG5cdFx0XHRzbWFsbENUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jdGEtc21hbGwnICksXG5cdFx0XHRiaWdDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhJyApLFxuXHRcdFx0aW5wdXRUb2dnbGUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLXJvY2tldGNkbi10b2dnbGUtLWlucHV0Jyk7XG5cblx0XHRjb25zdCBjdGFUb2dnbGUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLndwci1yb2NrZXRjZG4tY3RhLXRvZ2dsZScgKTtcblxuXHRcdC8qKlxuXHRcdCAqIFRvZ2dsZXMgUm9ja2V0Q0ROIENUQSBpbnRlcm5hbCBjb2xsYXBzZWQvZXhwYW5kZWQgc3RhdGUuXG5cdFx0ICpcblx0XHQgKiBAcmV0dXJuIHt2b2lkfVxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIHRvZ2dsZUJpZ0NUQVN0YXRlKCkge1xuXHRcdFx0aWYgKCAhIGJpZ0NUQSB8fCAhIGN0YVRvZ2dsZS5sZW5ndGggKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgaXNDb2xsYXBzZWQgPSBiaWdDVEEuY2xhc3NMaXN0LnRvZ2dsZSggJ3dwci1yb2NrZXRjZG4tY3RhLS1jb2xsYXBzZWQnICk7XG5cdFx0XHRiaWdDVEEuY2xhc3NMaXN0LnRvZ2dsZSggJ3dwci1yb2NrZXRjZG4tY3RhLS1leHBhbmRlZCcsICEgaXNDb2xsYXBzZWQgKTtcblx0XHRcdGN0YVRvZ2dsZS5mb3JFYWNoKCAoIGVsICkgPT4ge1xuXHRcdFx0XHRlbC5zZXRBdHRyaWJ1dGUoICdhcmlhLWV4cGFuZGVkJywgaXNDb2xsYXBzZWQgPyAnZmFsc2UnIDogJ3RydWUnICk7XG5cdFx0XHR9ICk7XG5cblx0XHRcdGlmKCAhIGlzQ29sbGFwc2VkICkge1xuXHRcdFx0XHR0cmFja1JvY2tldENETlVwc2VsbEJhbm5lckV4cGFuZGVkKCAnbWFudWFsJyApO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJDb2xsYXBzZWQoKTtcblx0XHRcdH1cblx0XHR9XG5cblx0XHRpZiAoIGN0YVRvZ2dsZS5sZW5ndGggJiYgYmlnQ1RBICkge1xuXHRcdFx0Y3RhVG9nZ2xlLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRcdGVsLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsIHRvZ2dsZUJpZ0NUQVN0YXRlICk7XG5cdFx0XHRcdGVsLmFkZEV2ZW50TGlzdGVuZXIoICdrZXlkb3duJywgKCBldmVudCApID0+IHtcblx0XHRcdFx0XHRpZiAoICdFbnRlcicgPT09IGV2ZW50LmtleSB8fCAnICcgPT09IGV2ZW50LmtleSApIHtcblx0XHRcdFx0XHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRcdFx0XHR0b2dnbGVCaWdDVEFTdGF0ZSgpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSApO1xuXHRcdFx0fSApO1xuXHRcdH1cblxuXHRcdC8vIFRyYWNrIGJhbm5lciB2aWV3IG9uIHBhZ2UgbG9hZCBpZiBiYW5uZXIgaXMgdmlzaWJsZSBhbmQgdXNlciBpcyBvbiBDRE4gdGFiLlxuXHRcdG1heWJlVHJhY2tCYW5uZXJWaWV3KCk7XG5cblx0XHQvLyBUcmFjayBiYW5uZXIgdmlldyB3aGVuIHVzZXIgbmF2aWdhdGVzIHRvIENETiB0YWIuXG5cdFx0d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdoYXNoY2hhbmdlJywgKCkgPT4ge1xuXHRcdFx0bWF5YmVUcmFja0Jhbm5lclZpZXcoKTtcblx0XHRcdHRyYWNrQ0ROTW9kZVNlbGVjdGlvbigpO1xuXHRcdH0gKTtcblxuXHRcdC8vIFByaWNlcyBzZWxlY3RvcnMgZm9yIHRvZ2dsaW5nIHZpc2liaWxpdHkgYmFzZWQgb24gdGhlIGJpbGxpbmcgY3ljbGUgdG9nZ2xlIHN0YXRlLlxuXHRcdGNvbnN0IHByaWNlcyA9IHtcblx0XHRcdG1vbnRobHk6IHtcblx0XHRcdFx0cmVndWxhcjogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy1yZWd1bGFyLXByaWNlLS1tb250aGx5JyksXG5cdFx0XHRcdGN1cnJlbnQ6IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItcm9ja2V0Y2RuLXByaWNpbmctLW1vbnRobHknKSxcblx0XHRcdFx0cGVyaW9kOiBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLXJvY2tldGNkbi1wcmljaW5nLS1iaWxsaW5nLXBlcmlvZC0tbW9udGhseScpXG5cdFx0XHR9LFxuXHRcdFx0eWVhcmx5OiB7XG5cdFx0XHRcdHJlZ3VsYXI6IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItcm9ja2V0Y2RuLXByaWNpbmctcmVndWxhci1wcmljZS0teWVhcmx5JyksXG5cdFx0XHRcdGN1cnJlbnQ6IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItcm9ja2V0Y2RuLXByaWNpbmctLWFubnVhbCcpLFxuXHRcdFx0XHRwZXJpb2Q6IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItcm9ja2V0Y2RuLXByaWNpbmctLWJpbGxpbmctcGVyaW9kLS15ZWFybHknKVxuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8vIERpc3BsYXkgdGhlIGNvcnJlY3QgcHJpY2VzIG9uIHBhZ2UgYmFzZWQgb24gYmlsbGluZyBjeWNsZSB0b2dnbGUgc3RhdGUuXG5cdFx0aWYgKCBpbnB1dFRvZ2dsZSApIHtcblx0XHRcdGlucHV0VG9nZ2xlLmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHRcdFx0Y29uc3QgaXNZZWFybHkgPSB0aGlzLmNoZWNrZWQ7XG5cblx0XHRcdFx0aWYgKGlzWWVhcmx5KSB7XG5cdFx0XHRcdFx0T2JqZWN0LnZhbHVlcyhwcmljZXMubW9udGhseSkuZm9yRWFjaChsaXN0ID0+IGxpc3QuZm9yRWFjaChlbCA9PiBlbC5jbGFzc0xpc3QuYWRkKCd3cHItaXNIaWRkZW4nKSkpO1xuXHRcdFx0XHRcdE9iamVjdC52YWx1ZXMocHJpY2VzLnllYXJseSkuZm9yRWFjaChsaXN0ID0+IGxpc3QuZm9yRWFjaChlbCA9PiBlbC5jbGFzc0xpc3QucmVtb3ZlKCd3cHItaXNIaWRkZW4nKSkpO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdE9iamVjdC52YWx1ZXMocHJpY2VzLm1vbnRobHkpLmZvckVhY2gobGlzdCA9PiBsaXN0LmZvckVhY2goZWwgPT4gZWwuY2xhc3NMaXN0LnJlbW92ZSgnd3ByLWlzSGlkZGVuJykpKTtcblx0XHRcdFx0XHRPYmplY3QudmFsdWVzKHByaWNlcy55ZWFybHkpLmZvckVhY2gobGlzdCA9PiBsaXN0LmZvckVhY2goZWwgPT4gZWwuY2xhc3NMaXN0LmFkZCgnd3ByLWlzSGlkZGVuJykpKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFVwZGF0ZSB0aGUgYnV0dG9uIFVSTCB3aXRoIHRoZSBjb3JyZWN0IGlzX21vbnRobHkgcGFyYW1ldGVyLlxuXHRcdFx0XHR1cGRhdGVCdXR0b25VcmxCaWxsaW5nQ3ljbGUoaXNZZWFybHkpO1xuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0Ly8gVHJhY2sgUm9ja2V0Q0ROIGFjdGl2YXRpb24gZmFpbGVkIENUQSBjbGlja1xuXHRcdGNvbnN0IGFjdGl2YXRpb25DVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjd3ByLXJvY2tldGNkbi1hY3RpdmF0aW9uLWN0YScpO1xuXHRcdGlmIChhY3RpdmF0aW9uQ1RBKSB7XG5cdFx0XHRhY3RpdmF0aW9uQ1RBLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgKCkgPT4ge1xuXHRcdFx0XHR0cmFja1JvY2tldENETkFjdGl2YXRpb25DVEEoKTtcblx0XHRcdH0pO1xuXHRcdH1cblxuXHR9ICk7XG5cblx0d2luZG93Lm9ubWVzc2FnZSA9ICggZSApID0+IHtcblx0XHRjb25zdCBpZnJhbWVVUkwgPSByb2NrZXRfYWpheF9kYXRhLm9yaWdpbl91cmw7XG5cblx0XHRpZiAoIGUub3JpZ2luICE9PSBpZnJhbWVVUkwgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0c2V0Q0RORnJhbWVIZWlnaHQoIGUuZGF0YSApO1xuXHRcdGNsb3NlTW9kYWwoIGUuZGF0YSApO1xuXHRcdHRva2VuSGFuZGxlciggZS5kYXRhLCBpZnJhbWVVUkwgKTtcblx0XHRwcm9jZXNzU3RhdHVzKCBlLmRhdGEgKTtcblx0XHRlbmFibGVDRE4oIGUuZGF0YSwgaWZyYW1lVVJMICk7XG5cdFx0ZGlzYWJsZUNETiggZS5kYXRhLCBpZnJhbWVVUkwgKTtcblx0XHR2YWxpZGF0ZVRva2VuQW5kQ05BTUUoIGUuZGF0YSApO1xuXHR9O1xuXG5cdGZ1bmN0aW9uIG9wZW5Sb2NrZXRDRE5Nb2RhbCgpIHtcblx0XHRjb25zdCByb2NrZXRjZG5JZnJhbWUgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0Y2RuLWlmcmFtZScpO1xuXHRcdGlmICggIXJvY2tldGNkbklmcmFtZSB8fCAhcm9ja2V0Y2RuSWZyYW1lLmRhdGFzZXQgfHwgIXJvY2tldGNkbklmcmFtZS5kYXRhc2V0LnNyYyB8fCByb2NrZXRjZG5JZnJhbWUuZGF0YXNldC5zcmMgPT09IHJvY2tldGNkbklmcmFtZS5zcmMgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdHJvY2tldGNkbklmcmFtZS5zcmMgPSByb2NrZXRjZG5JZnJhbWUuZGF0YXNldC5zcmM7XG5cdFx0TWljcm9Nb2RhbC5zaG93KCAnd3ByLXJvY2tldGNkbi1tb2RhbCcgKTtcblx0fVxuXG5cdGZ1bmN0aW9uIGNoZWNrQnV0dG9uVXJsQW5kT3BlbiggaXNDVEEgKSB7XG5cdFx0bGV0IGlmcmFtZVZpc2l0ID0gIXdpbmRvdy5yb2NrZXRjZG5CdXR0b25Vcmw7XG5cdFx0Ly8gVHJhY2sgQ1RBIGNsaWNrIGlmIHRoaXMgaXMgdGhlIHByaWNpbmcgQ1RBIGJ1dHRvbi5cblx0XHRpZiAoIGlzQ1RBICkge1xuXHRcdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxDVEFDbGlja2VkKGlmcmFtZVZpc2l0KTtcblx0XHR9XG5cblx0XHQvLyBDaGVjayBpZiBidXR0b24gVVJMIHdhcyBpbmplY3RlZCBieSBQSFBcblx0XHRpZiAoICFpZnJhbWVWaXNpdCApIHtcblx0XHRcdC8vIFNtYWxsIGRlbGF5IHRvIGVuc3VyZSBNaXhwYW5lbCBldmVudCBpcyBzZW50IGJlZm9yZSBuYXZpZ2F0aW9uXG5cdFx0XHRzZXRUaW1lb3V0KCBmdW5jdGlvbigpIHtcblx0XHRcdFx0d2luZG93LmxvY2F0aW9uLmhyZWYgPSB3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsO1xuXHRcdFx0fSwgMTAwICk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdC8vIFNob3cgaWZyYW1lIG1vZGFsIGFzIHVzdWFsXG5cdFx0XHRvcGVuUm9ja2V0Q0ROTW9kYWwoKTtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgYnV0dG9uIFVSTCB3aXRoIHRoZSBjb3JyZWN0IGlzX21vbnRobHkgcGFyYW1ldGVyIGJhc2VkIG9uIGJpbGxpbmcgY3ljbGUgdG9nZ2xlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2Jvb2xlYW59IGlzWWVhcmx5IC0gVHJ1ZSBpZiB5ZWFybHkgYmlsbGluZyBpcyBzZWxlY3RlZCwgZmFsc2UgZm9yIG1vbnRobHkuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVCdXR0b25VcmxCaWxsaW5nQ3ljbGUoIGlzWWVhcmx5ICkge1xuXHRcdGlmICggISB3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsIHx8IHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgPT09ICcnICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgPSBzZXRJc01vbnRobHlQYXJhbSh3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsLCBpc1llYXJseSk7XG5cdH1cblxuXHRmdW5jdGlvbiBtYXliZU9wZW5Nb2RhbCgpIHtcblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc3RhdHVzJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cblx0XHRcdFx0aWYgKCB0cnVlID09PSByZXNwb25zZVR4dC5zdWNjZXNzICkge1xuXHRcdFx0XHRcdG9wZW5Sb2NrZXRDRE5Nb2RhbCgpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIG1heWJlT3Blbk1vZGFsRnJvbVVSTCgpIHtcblx0XHRjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKCB3aW5kb3cubG9jYXRpb24uc2VhcmNoICk7XG5cblx0XHRpZiAoIHVybFBhcmFtcy5oYXMoICdyb2NrZXRjZG5fb3Blbl9pZnJhbWUnICkgJiYgJzEnID09PSB1cmxQYXJhbXMuZ2V0KCAncm9ja2V0Y2RuX29wZW5faWZyYW1lJyApICkge1xuXHRcdFx0Ly8gU2V0IGhhc2ggdG8gcGFnZV9jZG4gdG8gc2hvdyBDRE4gdGFiIGJlaGluZCBtb2RhbFxuXHRcdFx0d2luZG93LmxvY2F0aW9uLmhhc2ggPSAnI3BhZ2VfY2RuJztcblxuXHRcdFx0b3BlblJvY2tldENETk1vZGFsKCk7XG5cblx0XHRcdC8vIENsZWFuIHVwIHRoZSBVUkwgdG8gcHJldmVudCByZS10cmlnZ2VyaW5nIG9uIHJlZnJlc2hcblx0XHRcdHVybFBhcmFtcy5kZWxldGUoICdyb2NrZXRjZG5fb3Blbl9pZnJhbWUnICk7XG5cdFx0XHRjb25zdCBzZWFyY2ggPSB1cmxQYXJhbXMudG9TdHJpbmcoKTtcblx0XHRcdGNvbnN0IG5ld1VSTCA9IHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZSArICggc2VhcmNoID8gJz8nICsgc2VhcmNoIDogJycgKSArIHdpbmRvdy5sb2NhdGlvbi5oYXNoO1xuXHRcdFx0d2luZG93Lmhpc3RvcnkucmVwbGFjZVN0YXRlKCB7fSwgJycsIG5ld1VSTCApO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIGNsb3NlTW9kYWwoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5GcmFtZUNsb3NlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdE1pY3JvTW9kYWwuY2xvc2UoICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXHRcdC8vIEVuc3VyZSBzY3JvbGwgaXMgcmVzdG9yZWRcblx0XHRkb2N1bWVudC5ib2R5LnN0eWxlLm92ZXJmbG93ID0gJyc7XG5cblx0XHRsZXQgcGFnZXMgPSBbICdpZnJhbWUtcGF5bWVudC1zdWNjZXNzJywgJ2lmcmFtZS11bnN1YnNjcmliZS1zdWNjZXNzJyBdO1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5fcGFnZV9tZXNzYWdlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGlmICggcGFnZXMuaW5kZXhPZiggZGF0YS5jZG5fcGFnZV9tZXNzYWdlICkgPT09IC0xICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LmxvY2F0aW9uLnJlbG9hZCgpO1xuXHR9XG5cblx0ZnVuY3Rpb24gcHJvY2Vzc1N0YXR1cyggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl9wcm9jZXNzJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fcHJvY2Vzc19zZXQnO1xuXHRcdHBvc3REYXRhICs9ICcmc3RhdHVzPScgKyBkYXRhLnJvY2tldGNkbl9wcm9jZXNzO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cdH1cblxuXHRmdW5jdGlvbiBlbmFibGVDRE4oIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl91cmwnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9lbmFibGUnO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3VybD0nICsgZGF0YS5yb2NrZXRjZG5fdXJsO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXG5cdFx0cmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSAoKSA9PiB7XG5cdFx0XHRpZiAoIHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gWE1MSHR0cFJlcXVlc3QuRE9ORSAmJiAyMDAgPT09IHJlcXVlc3Quc3RhdHVzICkge1xuXHRcdFx0XHRsZXQgcmVzcG9uc2VUeHQgPSBKU09OLnBhcnNlKHJlcXVlc3QucmVzcG9uc2VUZXh0KTtcblx0XHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdCdzdWNjZXNzJzogcmVzcG9uc2VUeHQuc3VjY2Vzcyxcblx0XHRcdFx0XHRcdCdkYXRhJzogcmVzcG9uc2VUeHQuZGF0YSxcblx0XHRcdFx0XHRcdCdyb2NrZXRjZG4nOiB0cnVlXG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdFx0KTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gc2V0SXNNb250aGx5UGFyYW0odXJsLCBpc1llYXJseSkge1xuXHRcdC8vIFJlbW92ZSBhbnkgZXhpc3RpbmcgaXNfbW9udGhseSBwYXJhbVxuXHRcdGxldCBuZXdVcmwgPSB1cmwucmVwbGFjZSgvKFs/Jl0paXNfbW9udGhseT1bXiZdKi9nLCAnJyk7XG5cdFx0Ly8gQWRkIHRoZSBuZXcgcGFyYW1cblx0XHRjb25zdCBzZXAgPSBuZXdVcmwuaW5jbHVkZXMoJz8nKSA/ICcmJyA6ICc/Jztcblx0XHRuZXdVcmwgKz0gc2VwICsgJ2lzX21vbnRobHk9JyArIChpc1llYXJseSA/ICcwJyA6ICcxJyk7XG5cdFx0cmV0dXJuIG5ld1VybDtcblx0fVxuXG5cdGZ1bmN0aW9uIGRpc2FibGVDRE4oIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl9kaXNhYmxlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fZGlzYWJsZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICkge1xuXHRcdGNvbnN0IGh0dHBSZXF1ZXN0ID0gbmV3IFhNTEh0dHBSZXF1ZXN0KCk7XG5cblx0XHRodHRwUmVxdWVzdC5vcGVuKCAnUE9TVCcsIGFqYXh1cmwgKTtcblx0XHRodHRwUmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKCAnQ29udGVudC1UeXBlJywgJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCcgKTtcblx0XHRodHRwUmVxdWVzdC5zZW5kKCBwb3N0RGF0YSApO1xuXG5cdFx0cmV0dXJuIGh0dHBSZXF1ZXN0O1xuXHR9XG5cblx0ZnVuY3Rpb24gc2V0Q0RORnJhbWVIZWlnaHQoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5GcmFtZUhlaWdodCcgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ3JvY2tldGNkbi1pZnJhbWUnICkuc3R5bGUuaGVpZ2h0ID0gYCR7IGRhdGEuY2RuRnJhbWVIZWlnaHQgfXB4YDtcblx0fVxuXG5cdGZ1bmN0aW9uIHRva2VuSGFuZGxlciggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3Rva2VuJyApICkge1xuXHRcdFx0bGV0IGRhdGEgPSB7cHJvY2VzczpcInN1YnNjcmliZVwiLCBtZXNzYWdlOlwidG9rZW5fbm90X3JlY2VpdmVkXCJ9O1xuXHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHR7XG5cdFx0XHRcdFx0J3N1Y2Nlc3MnOiBmYWxzZSxcblx0XHRcdFx0XHQnZGF0YSc6IGRhdGEsXG5cdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0fSxcblx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHQpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1zYXZlX3JvY2tldGNkbl90b2tlbic7XG5cdFx0cG9zdERhdGEgKz0gJyZ2YWx1ZT0nICsgZGF0YS5yb2NrZXRjZG5fdG9rZW47XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiB2YWxpZGF0ZVRva2VuQW5kQ05BTUUoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW4nICkgfHwgISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3ZhbGlkYXRlX2NuYW1lJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW5fY25hbWUnO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3VybD0nICsgZGF0YS5yb2NrZXRjZG5fdmFsaWRhdGVfY25hbWU7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdG9rZW49JyArIGRhdGEucm9ja2V0Y2RuX3ZhbGlkYXRlX3Rva2VuO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBDRE4gbW9kZSBzZWxlY3Rpb24gd2l0aCBNaXhwYW5lbC5cblx0ICovXG5cdGZ1bmN0aW9uIHRyYWNrQ0ROTW9kZVNlbGVjdGlvbigpIHtcblx0XHRpZiAoICEgaXNPbkNETlRhYigpICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IGFjdGl2ZVRhYiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi10YWJzX190YWItLWFjdGl2ZScgKTtcblx0XHRcblx0XHRpZiAoICEgYWN0aXZlVGFiICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IGNkbk1vZGUgPSBhY3RpdmVUYWIuZ2V0QXR0cmlidXRlKCAnZGF0YS1jZG4tbW9kZScgKVxuXG5cdFx0aWYoICEgY2RuTW9kZSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9IFxuXG5cdFx0aWYgKCB0eXBlb2YgbWl4cGFuZWwgPT09ICd1bmRlZmluZWQnIHx8ICFtaXhwYW5lbC50cmFjayApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBDaGVjayBpZiB1c2VyIGhhcyBvcHRlZCBpblxuXHRcdGlmICggdHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhID09PSAndW5kZWZpbmVkJyB8fCAhcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCB8fCByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkID09PSAnMCcgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gSWRlbnRpZnkgdXNlciBpZiBhdmFpbGFibGVcblx0XHRpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdG1peHBhbmVsLmlkZW50aWZ5KHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQpO1xuXHRcdH1cblxuXHRcdG1peHBhbmVsLnRyYWNrKCdSb2NrZXRDRE4gTW9kZScsIHtcblx0XHRcdGNvbnRleHQ6IHJvY2tldF9taXhwYW5lbF9kYXRhLmNvbnRleHQsXG5cdFx0XHRwbHVnaW46IHJvY2tldF9taXhwYW5lbF9kYXRhLnBsdWdpbixcblx0XHRcdGJyYW5kOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5icmFuZCxcblx0XHRcdGFwcGxpY2F0aW9uOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG5cdFx0XHRjZG5fbW9kZTogY2RuTW9kZSxcblx0XHRcdGludGVyYWN0aW9uX2NoYW5uZWw6ICdVSSdcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3MgUm9ja2V0Q0ROIGFjdGl2YXRpb24gZmFpbGVkIENUQSBjbGljayB3aXRoIE1peHBhbmVsLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5BY3RpdmF0aW9uQ1RBKCkge1xuXHRcdGlmICh0eXBlb2YgbWl4cGFuZWwgPT09ICd1bmRlZmluZWQnIHx8ICFtaXhwYW5lbC50cmFjaykge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIENoZWNrIGlmIHVzZXIgaGFzIG9wdGVkIGluXG5cdFx0aWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSA9PT0gJ3VuZGVmaW5lZCcgfHwgIXJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgfHwgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gSWRlbnRpZnkgdXNlciBpZiBhdmFpbGFibGVcblx0XHRpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdG1peHBhbmVsLmlkZW50aWZ5KHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQpO1xuXHRcdH1cblxuXHRcdG1peHBhbmVsLnRyYWNrKCdSb2NrZXRDRE4gQWN0aXZhdGlvbiBGYWlsZWQgQ1RBIENsaWNrZWQnLCB7XG5cdFx0XHRjb250ZXh0OiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuXHRcdFx0cGx1Z2luOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG5cdFx0XHRicmFuZDogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHRhcHBsaWNhdGlvbjogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwXG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogVHJhY2tzIGEgUm9ja2V0Q0ROIHVwc2VsbCBldmVudCB3aXRoIE1peHBhbmVsLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gZXZlbnROYW1lICAgVGhlIE1peHBhbmVsIGV2ZW50IG5hbWUuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBbZXh0cmFQcm9wc10gT3B0aW9uYWwgYWRkaXRpb25hbCBwcm9wZXJ0aWVzIHRvIG1lcmdlLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5VcHNlbGxNaXhwYW5lbEV2ZW50KCBldmVudE5hbWUsIGV4dHJhUHJvcHMgKSB7XG5cdFx0aWYgKCB0eXBlb2YgbWl4cGFuZWwgPT09ICd1bmRlZmluZWQnIHx8ICEgbWl4cGFuZWwudHJhY2sgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW4uXG5cdFx0aWYgKCB0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnIHx8ICEgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCB8fCByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkID09PSAnMCcgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gSWRlbnRpZnkgdXNlciBpZiBhdmFpbGFibGUuXG5cdFx0aWYgKCAhIHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQgfHwgdHlwZW9mIG1peHBhbmVsLmlkZW50aWZ5ICE9PSAnZnVuY3Rpb24nICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdG1peHBhbmVsLmlkZW50aWZ5KCByb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICk7XG5cblx0XHR2YXIgcHJvcHMgPSB7XG5cdFx0XHRjb250ZXh0OiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuXHRcdFx0cGx1Z2luOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG5cdFx0XHRicmFuZDogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHRhcHBsaWNhdGlvbjogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwLFxuXHRcdFx0cGF0aDogcm9ja2V0X21peHBhbmVsX2RhdGEucGF0aCxcblx0XHRcdGludGVyYWN0aW9uX2NoYW5uZWw6ICdVSSdcblx0XHR9O1xuXG5cdFx0Ly8gTWVyZ2UgZXh0cmEgcHJvcGVydGllcyBpZiBwcm92aWRlZCBhbmQgdmFsaWQuXG5cdFx0aWYgKCBleHRyYVByb3BzICYmIHR5cGVvZiBleHRyYVByb3BzID09PSAnb2JqZWN0JyApIHtcblx0XHRcdGZvciAoIHZhciBrZXkgaW4gZXh0cmFQcm9wcyApIHtcblx0XHRcdFx0aWYgKCBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwoIGV4dHJhUHJvcHMsIGtleSApICkge1xuXHRcdFx0XHRcdHByb3BzWyBrZXkgXSA9IGV4dHJhUHJvcHNbIGtleSBdO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0bWl4cGFuZWwudHJhY2soIGV2ZW50TmFtZSwgcHJvcHMgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3MgUm9ja2V0Q0ROIHVwc2VsbCBiYW5uZXIgdmlldyB3aXRoIE1peHBhbmVsLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2Jvb2xlYW59IFtpc19jb2xsYXBzZWQ9ZmFsc2VdIFdoZXRoZXIgdGhlIHNtYWxsIGJhbm5lciB2YXJpYW50IGlzIGRpc3BsYXllZCwgU2VuZHMgYGNvbGxhcHNlZGAgd2hlbiB0cnVlLCBvdGhlcndpc2UgYG9wZW5lZGAuXG5cdCAqL1xuXHRmdW5jdGlvbiB0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCggaXNfY29sbGFwc2VkID0gZmFsc2UgKSB7XG5cdFx0aWYgKCAhIGlzT25DRE5UYWIoKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0Y29uc3QgaGFzaCA9IHdpbmRvdy5sb2NhdGlvbi5oYXNoO1xuXHRcdGNvbnN0IGJhc2VQYXRoID0gKCB0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgIT09ICd1bmRlZmluZWQnICYmIHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGggKSA/IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGggOiAnJztcblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIEJhbm5lciBWaWV3ZWQnLCB7XG5cdFx0XHRzdGF0ZTogICAgIGlzX2NvbGxhcHNlZCA/ICdjb2xsYXBzZWQnIDogJ29wZW5lZCcsXG5cdFx0XHRwYWdlX25hbWU6IGhhc2gsXG5cdFx0XHRwYXRoOiAgICAgIGJhc2VQYXRoICsgaGFzaFxuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3MgUm9ja2V0Q0ROIHVwc2VsbCBiYW5uZXIgZXhwYW5kZWQgd2l0aCBNaXhwYW5lbC5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHRyaWdnZXIgJ21hbnVhbCcgYnkgZGVmYXVsdC5cblx0ICovXG5cdGZ1bmN0aW9uIHRyYWNrUm9ja2V0Q0ROVXBzZWxsQmFubmVyRXhwYW5kZWQoIHRyaWdnZXIgKSB7XG5cdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxNaXhwYW5lbEV2ZW50KCAnUm9ja2V0Q0ROIFVwc2VsbCBCYW5uZXIgRXhwYW5kZWQnLCB7XG5cdFx0XHRsb2NhdGlvbjogd2luZG93LmxvY2F0aW9uLmhhc2gsXG5cdFx0XHR0cmlnZ2VyOiB0cmlnZ2VyXG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBSb2NrZXRDRE4gdXBzZWxsIGJhbm5lciBjb2xsYXBzZWQgd2l0aCBNaXhwYW5lbC5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IFt0cmlnZ2VyPSdtYW51YWwnXSAnbWFudWFsJyB3aGVuIHVzZXIgY2xpY2tzIHRvZ2dsZSwgJ2F1dG9fbGltaXRfcmVsZWFzZWQnIHdoZW4gYSBwYWdlIGRlbGV0aW9uIGRyb3BzIGNvdW50IGJlbG93IGxpbWl0LlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJDb2xsYXBzZWQoIHRyaWdnZXIgPSAnbWFudWFsJyApIHtcblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIEJhbm5lciBDb2xsYXBzZWQnLCB7XG5cdFx0XHRsb2NhdGlvbjogd2luZG93LmxvY2F0aW9uLmhhc2gsXG5cdFx0XHR0cmlnZ2VyOiB0cmlnZ2VyXG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBSb2NrZXRDRE4gdXBzZWxsIENUQSBjbGljayB3aXRoIE1peHBhbmVsLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5VcHNlbGxDVEFDbGlja2VkKCBpZnJhbWVWaXNpdCA9IGZhbHNlICkge1xuXHRcdGNvbnN0IHRhYmxlTGlzdCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbiAud3ByLXRhYmxlLWxpc3QnICk7XG5cdFx0Y29uc3QgcGFnZXNDb3VudCA9IHRhYmxlTGlzdCA/IHRhYmxlTGlzdC5xdWVyeVNlbGVjdG9yQWxsKCAnW2RhdGEtaWRdJyApLmxlbmd0aCA6IDA7XG5cblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIENUQSBDbGlja2VkJywge1xuXHRcdFx0ZGVzdGluYXRpb246IGlmcmFtZVZpc2l0ID8gJ2lmcmFtZScgOiAnZXhwcmVzcy1jaGVja291dCcsXG5cdFx0XHRwYWdlc19jb3VudDogcGFnZXNDb3VudFxuXHRcdH0gKTtcblx0fVxufSApKCBkb2N1bWVudCwgd2luZG93ICk7XG4iLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJUaW1lbGluZUxpdGVcIixbXCJjb3JlLkFuaW1hdGlvblwiLFwiY29yZS5TaW1wbGVUaW1lbGluZVwiLFwiVHdlZW5MaXRlXCJdLGZ1bmN0aW9uKHQsZSxpKXt2YXIgcz1mdW5jdGlvbih0KXtlLmNhbGwodGhpcyx0KSx0aGlzLl9sYWJlbHM9e30sdGhpcy5hdXRvUmVtb3ZlQ2hpbGRyZW49dGhpcy52YXJzLmF1dG9SZW1vdmVDaGlsZHJlbj09PSEwLHRoaXMuc21vb3RoQ2hpbGRUaW1pbmc9dGhpcy52YXJzLnNtb290aENoaWxkVGltaW5nPT09ITAsdGhpcy5fc29ydENoaWxkcmVuPSEwLHRoaXMuX29uVXBkYXRlPXRoaXMudmFycy5vblVwZGF0ZTt2YXIgaSxzLHI9dGhpcy52YXJzO2ZvcihzIGluIHIpaT1yW3NdLGEoaSkmJi0xIT09aS5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIikmJihyW3NdPXRoaXMuX3N3YXBTZWxmSW5QYXJhbXMoaSkpO2Eoci50d2VlbnMpJiZ0aGlzLmFkZChyLnR3ZWVucywwLHIuYWxpZ24sci5zdGFnZ2VyKX0scj0xZS0xMCxuPWkuX2ludGVybmFscy5pc1NlbGVjdG9yLGE9aS5faW50ZXJuYWxzLmlzQXJyYXksbz1bXSxoPXdpbmRvdy5fZ3NEZWZpbmUuZ2xvYmFscyxsPWZ1bmN0aW9uKHQpe3ZhciBlLGk9e307Zm9yKGUgaW4gdClpW2VdPXRbZV07cmV0dXJuIGl9LF89ZnVuY3Rpb24odCxlLGkscyl7dC5fdGltZWxpbmUucGF1c2UodC5fc3RhcnRUaW1lKSxlJiZlLmFwcGx5KHN8fHQuX3RpbWVsaW5lLGl8fG8pfSx1PW8uc2xpY2UsZj1zLnByb3RvdHlwZT1uZXcgZTtyZXR1cm4gcy52ZXJzaW9uPVwiMS4xMi4xXCIsZi5jb25zdHJ1Y3Rvcj1zLGYua2lsbCgpLl9nYz0hMSxmLnRvPWZ1bmN0aW9uKHQsZSxzLHIpe3ZhciBuPXMucmVwZWF0JiZoLlR3ZWVuTWF4fHxpO3JldHVybiBlP3RoaXMuYWRkKG5ldyBuKHQsZSxzKSxyKTp0aGlzLnNldCh0LHMscil9LGYuZnJvbT1mdW5jdGlvbih0LGUscyxyKXtyZXR1cm4gdGhpcy5hZGQoKHMucmVwZWF0JiZoLlR3ZWVuTWF4fHxpKS5mcm9tKHQsZSxzKSxyKX0sZi5mcm9tVG89ZnVuY3Rpb24odCxlLHMscixuKXt2YXIgYT1yLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aTtyZXR1cm4gZT90aGlzLmFkZChhLmZyb21Ubyh0LGUscyxyKSxuKTp0aGlzLnNldCh0LHIsbil9LGYuc3RhZ2dlclRvPWZ1bmN0aW9uKHQsZSxyLGEsbyxoLF8sZil7dmFyIHAsYz1uZXcgcyh7b25Db21wbGV0ZTpoLG9uQ29tcGxldGVQYXJhbXM6XyxvbkNvbXBsZXRlU2NvcGU6ZixzbW9vdGhDaGlsZFRpbWluZzp0aGlzLnNtb290aENoaWxkVGltaW5nfSk7Zm9yKFwic3RyaW5nXCI9PXR5cGVvZiB0JiYodD1pLnNlbGVjdG9yKHQpfHx0KSxuKHQpJiYodD11LmNhbGwodCwwKSksYT1hfHwwLHA9MDt0Lmxlbmd0aD5wO3ArKylyLnN0YXJ0QXQmJihyLnN0YXJ0QXQ9bChyLnN0YXJ0QXQpKSxjLnRvKHRbcF0sZSxsKHIpLHAqYSk7cmV0dXJuIHRoaXMuYWRkKGMsbyl9LGYuc3RhZ2dlckZyb209ZnVuY3Rpb24odCxlLGkscyxyLG4sYSxvKXtyZXR1cm4gaS5pbW1lZGlhdGVSZW5kZXI9MCE9aS5pbW1lZGlhdGVSZW5kZXIsaS5ydW5CYWNrd2FyZHM9ITAsdGhpcy5zdGFnZ2VyVG8odCxlLGkscyxyLG4sYSxvKX0sZi5zdGFnZ2VyRnJvbVRvPWZ1bmN0aW9uKHQsZSxpLHMscixuLGEsbyxoKXtyZXR1cm4gcy5zdGFydEF0PWkscy5pbW1lZGlhdGVSZW5kZXI9MCE9cy5pbW1lZGlhdGVSZW5kZXImJjAhPWkuaW1tZWRpYXRlUmVuZGVyLHRoaXMuc3RhZ2dlclRvKHQsZSxzLHIsbixhLG8saCl9LGYuY2FsbD1mdW5jdGlvbih0LGUscyxyKXtyZXR1cm4gdGhpcy5hZGQoaS5kZWxheWVkQ2FsbCgwLHQsZSxzKSxyKX0sZi5zZXQ9ZnVuY3Rpb24odCxlLHMpe3JldHVybiBzPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwocywwLCEwKSxudWxsPT1lLmltbWVkaWF0ZVJlbmRlciYmKGUuaW1tZWRpYXRlUmVuZGVyPXM9PT10aGlzLl90aW1lJiYhdGhpcy5fcGF1c2VkKSx0aGlzLmFkZChuZXcgaSh0LDAsZSkscyl9LHMuZXhwb3J0Um9vdD1mdW5jdGlvbih0LGUpe3Q9dHx8e30sbnVsbD09dC5zbW9vdGhDaGlsZFRpbWluZyYmKHQuc21vb3RoQ2hpbGRUaW1pbmc9ITApO3ZhciByLG4sYT1uZXcgcyh0KSxvPWEuX3RpbWVsaW5lO2ZvcihudWxsPT1lJiYoZT0hMCksby5fcmVtb3ZlKGEsITApLGEuX3N0YXJ0VGltZT0wLGEuX3Jhd1ByZXZUaW1lPWEuX3RpbWU9YS5fdG90YWxUaW1lPW8uX3RpbWUscj1vLl9maXJzdDtyOyluPXIuX25leHQsZSYmciBpbnN0YW5jZW9mIGkmJnIudGFyZ2V0PT09ci52YXJzLm9uQ29tcGxldGV8fGEuYWRkKHIsci5fc3RhcnRUaW1lLXIuX2RlbGF5KSxyPW47cmV0dXJuIG8uYWRkKGEsMCksYX0sZi5hZGQ9ZnVuY3Rpb24ocixuLG8saCl7dmFyIGwsXyx1LGYscCxjO2lmKFwibnVtYmVyXCIhPXR5cGVvZiBuJiYobj10aGlzLl9wYXJzZVRpbWVPckxhYmVsKG4sMCwhMCxyKSksIShyIGluc3RhbmNlb2YgdCkpe2lmKHIgaW5zdGFuY2VvZiBBcnJheXx8ciYmci5wdXNoJiZhKHIpKXtmb3Iobz1vfHxcIm5vcm1hbFwiLGg9aHx8MCxsPW4sXz1yLmxlbmd0aCx1PTA7Xz51O3UrKylhKGY9clt1XSkmJihmPW5ldyBzKHt0d2VlbnM6Zn0pKSx0aGlzLmFkZChmLGwpLFwic3RyaW5nXCIhPXR5cGVvZiBmJiZcImZ1bmN0aW9uXCIhPXR5cGVvZiBmJiYoXCJzZXF1ZW5jZVwiPT09bz9sPWYuX3N0YXJ0VGltZStmLnRvdGFsRHVyYXRpb24oKS9mLl90aW1lU2NhbGU6XCJzdGFydFwiPT09byYmKGYuX3N0YXJ0VGltZS09Zi5kZWxheSgpKSksbCs9aDtyZXR1cm4gdGhpcy5fdW5jYWNoZSghMCl9aWYoXCJzdHJpbmdcIj09dHlwZW9mIHIpcmV0dXJuIHRoaXMuYWRkTGFiZWwocixuKTtpZihcImZ1bmN0aW9uXCIhPXR5cGVvZiByKXRocm93XCJDYW5ub3QgYWRkIFwiK3IrXCIgaW50byB0aGUgdGltZWxpbmU7IGl0IGlzIG5vdCBhIHR3ZWVuLCB0aW1lbGluZSwgZnVuY3Rpb24sIG9yIHN0cmluZy5cIjtyPWkuZGVsYXllZENhbGwoMCxyKX1pZihlLnByb3RvdHlwZS5hZGQuY2FsbCh0aGlzLHIsbiksKHRoaXMuX2djfHx0aGlzLl90aW1lPT09dGhpcy5fZHVyYXRpb24pJiYhdGhpcy5fcGF1c2VkJiZ0aGlzLl9kdXJhdGlvbjx0aGlzLmR1cmF0aW9uKCkpZm9yKHA9dGhpcyxjPXAucmF3VGltZSgpPnIuX3N0YXJ0VGltZTtwLl90aW1lbGluZTspYyYmcC5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmc/cC50b3RhbFRpbWUocC5fdG90YWxUaW1lLCEwKTpwLl9nYyYmcC5fZW5hYmxlZCghMCwhMSkscD1wLl90aW1lbGluZTtyZXR1cm4gdGhpc30sZi5yZW1vdmU9ZnVuY3Rpb24oZSl7aWYoZSBpbnN0YW5jZW9mIHQpcmV0dXJuIHRoaXMuX3JlbW92ZShlLCExKTtpZihlIGluc3RhbmNlb2YgQXJyYXl8fGUmJmUucHVzaCYmYShlKSl7Zm9yKHZhciBpPWUubGVuZ3RoOy0taT4tMTspdGhpcy5yZW1vdmUoZVtpXSk7cmV0dXJuIHRoaXN9cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIGU/dGhpcy5yZW1vdmVMYWJlbChlKTp0aGlzLmtpbGwobnVsbCxlKX0sZi5fcmVtb3ZlPWZ1bmN0aW9uKHQsaSl7ZS5wcm90b3R5cGUuX3JlbW92ZS5jYWxsKHRoaXMsdCxpKTt2YXIgcz10aGlzLl9sYXN0O3JldHVybiBzP3RoaXMuX3RpbWU+cy5fc3RhcnRUaW1lK3MuX3RvdGFsRHVyYXRpb24vcy5fdGltZVNjYWxlJiYodGhpcy5fdGltZT10aGlzLmR1cmF0aW9uKCksdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RvdGFsRHVyYXRpb24pOnRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPXRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249MCx0aGlzfSxmLmFwcGVuZD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLmFkZCh0LHRoaXMuX3BhcnNlVGltZU9yTGFiZWwobnVsbCxlLCEwLHQpKX0sZi5pbnNlcnQ9Zi5pbnNlcnRNdWx0aXBsZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5hZGQodCxlfHwwLGkscyl9LGYuYXBwZW5kTXVsdGlwbGU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuYWRkKHQsdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChudWxsLGUsITAsdCksaSxzKX0sZi5hZGRMYWJlbD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLl9sYWJlbHNbdF09dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChlKSx0aGlzfSxmLmFkZFBhdXNlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmNhbGwoXyxbXCJ7c2VsZn1cIixlLGksc10sdGhpcyx0KX0sZi5yZW1vdmVMYWJlbD1mdW5jdGlvbih0KXtyZXR1cm4gZGVsZXRlIHRoaXMuX2xhYmVsc1t0XSx0aGlzfSxmLmdldExhYmVsVGltZT1mdW5jdGlvbih0KXtyZXR1cm4gbnVsbCE9dGhpcy5fbGFiZWxzW3RdP3RoaXMuX2xhYmVsc1t0XTotMX0sZi5fcGFyc2VUaW1lT3JMYWJlbD1mdW5jdGlvbihlLGkscyxyKXt2YXIgbjtpZihyIGluc3RhbmNlb2YgdCYmci50aW1lbGluZT09PXRoaXMpdGhpcy5yZW1vdmUocik7ZWxzZSBpZihyJiYociBpbnN0YW5jZW9mIEFycmF5fHxyLnB1c2gmJmEocikpKWZvcihuPXIubGVuZ3RoOy0tbj4tMTspcltuXWluc3RhbmNlb2YgdCYmcltuXS50aW1lbGluZT09PXRoaXMmJnRoaXMucmVtb3ZlKHJbbl0pO2lmKFwic3RyaW5nXCI9PXR5cGVvZiBpKXJldHVybiB0aGlzLl9wYXJzZVRpbWVPckxhYmVsKGkscyYmXCJudW1iZXJcIj09dHlwZW9mIGUmJm51bGw9PXRoaXMuX2xhYmVsc1tpXT9lLXRoaXMuZHVyYXRpb24oKTowLHMpO2lmKGk9aXx8MCxcInN0cmluZ1wiIT10eXBlb2YgZXx8IWlzTmFOKGUpJiZudWxsPT10aGlzLl9sYWJlbHNbZV0pbnVsbD09ZSYmKGU9dGhpcy5kdXJhdGlvbigpKTtlbHNle2lmKG49ZS5pbmRleE9mKFwiPVwiKSwtMT09PW4pcmV0dXJuIG51bGw9PXRoaXMuX2xhYmVsc1tlXT9zP3RoaXMuX2xhYmVsc1tlXT10aGlzLmR1cmF0aW9uKCkraTppOnRoaXMuX2xhYmVsc1tlXStpO2k9cGFyc2VJbnQoZS5jaGFyQXQobi0xKStcIjFcIiwxMCkqTnVtYmVyKGUuc3Vic3RyKG4rMSkpLGU9bj4xP3RoaXMuX3BhcnNlVGltZU9yTGFiZWwoZS5zdWJzdHIoMCxuLTEpLDAscyk6dGhpcy5kdXJhdGlvbigpfXJldHVybiBOdW1iZXIoZSkraX0sZi5zZWVrPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMudG90YWxUaW1lKFwibnVtYmVyXCI9PXR5cGVvZiB0P3Q6dGhpcy5fcGFyc2VUaW1lT3JMYWJlbCh0KSxlIT09ITEpfSxmLnN0b3A9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5wYXVzZWQoITApfSxmLmdvdG9BbmRQbGF5PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucGxheSh0LGUpfSxmLmdvdG9BbmRTdG9wPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucGF1c2UodCxlKX0sZi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3RoaXMuX2djJiZ0aGlzLl9lbmFibGVkKCEwLCExKTt2YXIgcyxuLGEsaCxsLF89dGhpcy5fZGlydHk/dGhpcy50b3RhbER1cmF0aW9uKCk6dGhpcy5fdG90YWxEdXJhdGlvbix1PXRoaXMuX3RpbWUsZj10aGlzLl9zdGFydFRpbWUscD10aGlzLl90aW1lU2NhbGUsYz10aGlzLl9wYXVzZWQ7aWYodD49Xz8odGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9Xyx0aGlzLl9yZXZlcnNlZHx8dGhpcy5faGFzUGF1c2VkQ2hpbGQoKXx8KG49ITAsaD1cIm9uQ29tcGxldGVcIiwwPT09dGhpcy5fZHVyYXRpb24mJigwPT09dHx8MD50aGlzLl9yYXdQcmV2VGltZXx8dGhpcy5fcmF3UHJldlRpbWU9PT1yKSYmdGhpcy5fcmF3UHJldlRpbWUhPT10JiZ0aGlzLl9maXJzdCYmKGw9ITAsdGhpcy5fcmF3UHJldlRpbWU+ciYmKGg9XCJvblJldmVyc2VDb21wbGV0ZVwiKSkpLHRoaXMuX3Jhd1ByZXZUaW1lPXRoaXMuX2R1cmF0aW9ufHwhZXx8dHx8dGhpcy5fcmF3UHJldlRpbWU9PT10P3Q6cix0PV8rMWUtNCk6MWUtNz50Pyh0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT0wLCgwIT09dXx8MD09PXRoaXMuX2R1cmF0aW9uJiZ0aGlzLl9yYXdQcmV2VGltZSE9PXImJih0aGlzLl9yYXdQcmV2VGltZT4wfHwwPnQmJnRoaXMuX3Jhd1ByZXZUaW1lPj0wKSkmJihoPVwib25SZXZlcnNlQ29tcGxldGVcIixuPXRoaXMuX3JldmVyc2VkKSwwPnQ/KHRoaXMuX2FjdGl2ZT0hMSwwPT09dGhpcy5fZHVyYXRpb24mJnRoaXMuX3Jhd1ByZXZUaW1lPj0wJiZ0aGlzLl9maXJzdCYmKGw9ITApLHRoaXMuX3Jhd1ByZXZUaW1lPXQpOih0aGlzLl9yYXdQcmV2VGltZT10aGlzLl9kdXJhdGlvbnx8IWV8fHR8fHRoaXMuX3Jhd1ByZXZUaW1lPT09dD90OnIsdD0wLHRoaXMuX2luaXR0ZWR8fChsPSEwKSkpOnRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXRoaXMuX3Jhd1ByZXZUaW1lPXQsdGhpcy5fdGltZSE9PXUmJnRoaXMuX2ZpcnN0fHxpfHxsKXtpZih0aGlzLl9pbml0dGVkfHwodGhpcy5faW5pdHRlZD0hMCksdGhpcy5fYWN0aXZlfHwhdGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lIT09dSYmdD4wJiYodGhpcy5fYWN0aXZlPSEwKSwwPT09dSYmdGhpcy52YXJzLm9uU3RhcnQmJjAhPT10aGlzLl90aW1lJiYoZXx8dGhpcy52YXJzLm9uU3RhcnQuYXBwbHkodGhpcy52YXJzLm9uU3RhcnRTY29wZXx8dGhpcyx0aGlzLnZhcnMub25TdGFydFBhcmFtc3x8bykpLHRoaXMuX3RpbWU+PXUpZm9yKHM9dGhpcy5fZmlyc3Q7cyYmKGE9cy5fbmV4dCwhdGhpcy5fcGF1c2VkfHxjKTspKHMuX2FjdGl2ZXx8cy5fc3RhcnRUaW1lPD10aGlzLl90aW1lJiYhcy5fcGF1c2VkJiYhcy5fZ2MpJiYocy5fcmV2ZXJzZWQ/cy5yZW5kZXIoKHMuX2RpcnR5P3MudG90YWxEdXJhdGlvbigpOnMuX3RvdGFsRHVyYXRpb24pLSh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSk6cy5yZW5kZXIoKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKSkscz1hO2Vsc2UgZm9yKHM9dGhpcy5fbGFzdDtzJiYoYT1zLl9wcmV2LCF0aGlzLl9wYXVzZWR8fGMpOykocy5fYWN0aXZlfHx1Pj1zLl9zdGFydFRpbWUmJiFzLl9wYXVzZWQmJiFzLl9nYykmJihzLl9yZXZlcnNlZD9zLnJlbmRlcigocy5fZGlydHk/cy50b3RhbER1cmF0aW9uKCk6cy5fdG90YWxEdXJhdGlvbiktKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKTpzLnJlbmRlcigodC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpKSxzPWE7dGhpcy5fb25VcGRhdGUmJihlfHx0aGlzLl9vblVwZGF0ZS5hcHBseSh0aGlzLnZhcnMub25VcGRhdGVTY29wZXx8dGhpcyx0aGlzLnZhcnMub25VcGRhdGVQYXJhbXN8fG8pKSxoJiYodGhpcy5fZ2N8fChmPT09dGhpcy5fc3RhcnRUaW1lfHxwIT09dGhpcy5fdGltZVNjYWxlKSYmKDA9PT10aGlzLl90aW1lfHxfPj10aGlzLnRvdGFsRHVyYXRpb24oKSkmJihuJiYodGhpcy5fdGltZWxpbmUuYXV0b1JlbW92ZUNoaWxkcmVuJiZ0aGlzLl9lbmFibGVkKCExLCExKSx0aGlzLl9hY3RpdmU9ITEpLCFlJiZ0aGlzLnZhcnNbaF0mJnRoaXMudmFyc1toXS5hcHBseSh0aGlzLnZhcnNbaCtcIlNjb3BlXCJdfHx0aGlzLHRoaXMudmFyc1toK1wiUGFyYW1zXCJdfHxvKSkpfX0sZi5faGFzUGF1c2VkQ2hpbGQ9ZnVuY3Rpb24oKXtmb3IodmFyIHQ9dGhpcy5fZmlyc3Q7dDspe2lmKHQuX3BhdXNlZHx8dCBpbnN0YW5jZW9mIHMmJnQuX2hhc1BhdXNlZENoaWxkKCkpcmV0dXJuITA7dD10Ll9uZXh0fXJldHVybiExfSxmLmdldENoaWxkcmVuPWZ1bmN0aW9uKHQsZSxzLHIpe3I9cnx8LTk5OTk5OTk5OTk7Zm9yKHZhciBuPVtdLGE9dGhpcy5fZmlyc3Qsbz0wO2E7KXI+YS5fc3RhcnRUaW1lfHwoYSBpbnN0YW5jZW9mIGk/ZSE9PSExJiYobltvKytdPWEpOihzIT09ITEmJihuW28rK109YSksdCE9PSExJiYobj1uLmNvbmNhdChhLmdldENoaWxkcmVuKCEwLGUscykpLG89bi5sZW5ndGgpKSksYT1hLl9uZXh0O3JldHVybiBufSxmLmdldFR3ZWVuc09mPWZ1bmN0aW9uKHQsZSl7dmFyIHMscixuPXRoaXMuX2djLGE9W10sbz0wO2ZvcihuJiZ0aGlzLl9lbmFibGVkKCEwLCEwKSxzPWkuZ2V0VHdlZW5zT2YodCkscj1zLmxlbmd0aDstLXI+LTE7KShzW3JdLnRpbWVsaW5lPT09dGhpc3x8ZSYmdGhpcy5fY29udGFpbnMoc1tyXSkpJiYoYVtvKytdPXNbcl0pO3JldHVybiBuJiZ0aGlzLl9lbmFibGVkKCExLCEwKSxhfSxmLl9jb250YWlucz1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC50aW1lbGluZTtlOyl7aWYoZT09PXRoaXMpcmV0dXJuITA7ZT1lLnRpbWVsaW5lfXJldHVybiExfSxmLnNoaWZ0Q2hpbGRyZW49ZnVuY3Rpb24odCxlLGkpe2k9aXx8MDtmb3IodmFyIHMscj10aGlzLl9maXJzdCxuPXRoaXMuX2xhYmVscztyOylyLl9zdGFydFRpbWU+PWkmJihyLl9zdGFydFRpbWUrPXQpLHI9ci5fbmV4dDtpZihlKWZvcihzIGluIG4pbltzXT49aSYmKG5bc10rPXQpO3JldHVybiB0aGlzLl91bmNhY2hlKCEwKX0sZi5fa2lsbD1mdW5jdGlvbih0LGUpe2lmKCF0JiYhZSlyZXR1cm4gdGhpcy5fZW5hYmxlZCghMSwhMSk7Zm9yKHZhciBpPWU/dGhpcy5nZXRUd2VlbnNPZihlKTp0aGlzLmdldENoaWxkcmVuKCEwLCEwLCExKSxzPWkubGVuZ3RoLHI9ITE7LS1zPi0xOylpW3NdLl9raWxsKHQsZSkmJihyPSEwKTtyZXR1cm4gcn0sZi5jbGVhcj1mdW5jdGlvbih0KXt2YXIgZT10aGlzLmdldENoaWxkcmVuKCExLCEwLCEwKSxpPWUubGVuZ3RoO2Zvcih0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT0wOy0taT4tMTspZVtpXS5fZW5hYmxlZCghMSwhMSk7cmV0dXJuIHQhPT0hMSYmKHRoaXMuX2xhYmVscz17fSksdGhpcy5fdW5jYWNoZSghMCl9LGYuaW52YWxpZGF0ZT1mdW5jdGlvbigpe2Zvcih2YXIgdD10aGlzLl9maXJzdDt0Oyl0LmludmFsaWRhdGUoKSx0PXQuX25leHQ7cmV0dXJuIHRoaXN9LGYuX2VuYWJsZWQ9ZnVuY3Rpb24odCxpKXtpZih0PT09dGhpcy5fZ2MpZm9yKHZhciBzPXRoaXMuX2ZpcnN0O3M7KXMuX2VuYWJsZWQodCwhMCkscz1zLl9uZXh0O3JldHVybiBlLnByb3RvdHlwZS5fZW5hYmxlZC5jYWxsKHRoaXMsdCxpKX0sZi5kdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oMCE9PXRoaXMuZHVyYXRpb24oKSYmMCE9PXQmJnRoaXMudGltZVNjYWxlKHRoaXMuX2R1cmF0aW9uL3QpLHRoaXMpOih0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCksdGhpcy5fZHVyYXRpb24pfSxmLnRvdGFsRHVyYXRpb249ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpe2lmKHRoaXMuX2RpcnR5KXtmb3IodmFyIGUsaSxzPTAscj10aGlzLl9sYXN0LG49OTk5OTk5OTk5OTk5O3I7KWU9ci5fcHJldixyLl9kaXJ0eSYmci50b3RhbER1cmF0aW9uKCksci5fc3RhcnRUaW1lPm4mJnRoaXMuX3NvcnRDaGlsZHJlbiYmIXIuX3BhdXNlZD90aGlzLmFkZChyLHIuX3N0YXJ0VGltZS1yLl9kZWxheSk6bj1yLl9zdGFydFRpbWUsMD5yLl9zdGFydFRpbWUmJiFyLl9wYXVzZWQmJihzLT1yLl9zdGFydFRpbWUsdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJih0aGlzLl9zdGFydFRpbWUrPXIuX3N0YXJ0VGltZS90aGlzLl90aW1lU2NhbGUpLHRoaXMuc2hpZnRDaGlsZHJlbigtci5fc3RhcnRUaW1lLCExLC05OTk5OTk5OTk5KSxuPTApLGk9ci5fc3RhcnRUaW1lK3IuX3RvdGFsRHVyYXRpb24vci5fdGltZVNjYWxlLGk+cyYmKHM9aSkscj1lO3RoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249cyx0aGlzLl9kaXJ0eT0hMX1yZXR1cm4gdGhpcy5fdG90YWxEdXJhdGlvbn1yZXR1cm4gMCE9PXRoaXMudG90YWxEdXJhdGlvbigpJiYwIT09dCYmdGhpcy50aW1lU2NhbGUodGhpcy5fdG90YWxEdXJhdGlvbi90KSx0aGlzfSxmLnVzZXNGcmFtZXM9ZnVuY3Rpb24oKXtmb3IodmFyIGU9dGhpcy5fdGltZWxpbmU7ZS5fdGltZWxpbmU7KWU9ZS5fdGltZWxpbmU7cmV0dXJuIGU9PT10Ll9yb290RnJhbWVzVGltZWxpbmV9LGYucmF3VGltZT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9wYXVzZWQ/dGhpcy5fdG90YWxUaW1lOih0aGlzLl90aW1lbGluZS5yYXdUaW1lKCktdGhpcy5fc3RhcnRUaW1lKSp0aGlzLl90aW1lU2NhbGV9LHN9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbihmdW5jdGlvbih0KXtcInVzZSBzdHJpY3RcIjt2YXIgZT10LkdyZWVuU29ja0dsb2JhbHN8fHQ7aWYoIWUuVHdlZW5MaXRlKXt2YXIgaSxzLG4scixhLG89ZnVuY3Rpb24odCl7dmFyIGkscz10LnNwbGl0KFwiLlwiKSxuPWU7Zm9yKGk9MDtzLmxlbmd0aD5pO2krKyluW3NbaV1dPW49bltzW2ldXXx8e307cmV0dXJuIG59LGw9byhcImNvbS5ncmVlbnNvY2tcIiksaD0xZS0xMCxfPVtdLnNsaWNlLHU9ZnVuY3Rpb24oKXt9LG09ZnVuY3Rpb24oKXt2YXIgdD1PYmplY3QucHJvdG90eXBlLnRvU3RyaW5nLGU9dC5jYWxsKFtdKTtyZXR1cm4gZnVuY3Rpb24oaSl7cmV0dXJuIG51bGwhPWkmJihpIGluc3RhbmNlb2YgQXJyYXl8fFwib2JqZWN0XCI9PXR5cGVvZiBpJiYhIWkucHVzaCYmdC5jYWxsKGkpPT09ZSl9fSgpLGY9e30scD1mdW5jdGlvbihpLHMsbixyKXt0aGlzLnNjPWZbaV0/ZltpXS5zYzpbXSxmW2ldPXRoaXMsdGhpcy5nc0NsYXNzPW51bGwsdGhpcy5mdW5jPW47dmFyIGE9W107dGhpcy5jaGVjaz1mdW5jdGlvbihsKXtmb3IodmFyIGgsXyx1LG0sYz1zLmxlbmd0aCxkPWM7LS1jPi0xOykoaD1mW3NbY11dfHxuZXcgcChzW2NdLFtdKSkuZ3NDbGFzcz8oYVtjXT1oLmdzQ2xhc3MsZC0tKTpsJiZoLnNjLnB1c2godGhpcyk7aWYoMD09PWQmJm4pZm9yKF89KFwiY29tLmdyZWVuc29jay5cIitpKS5zcGxpdChcIi5cIiksdT1fLnBvcCgpLG09byhfLmpvaW4oXCIuXCIpKVt1XT10aGlzLmdzQ2xhc3M9bi5hcHBseShuLGEpLHImJihlW3VdPW0sXCJmdW5jdGlvblwiPT10eXBlb2YgZGVmaW5lJiZkZWZpbmUuYW1kP2RlZmluZSgodC5HcmVlblNvY2tBTURQYXRoP3QuR3JlZW5Tb2NrQU1EUGF0aCtcIi9cIjpcIlwiKStpLnNwbGl0KFwiLlwiKS5qb2luKFwiL1wiKSxbXSxmdW5jdGlvbigpe3JldHVybiBtfSk6XCJ1bmRlZmluZWRcIiE9dHlwZW9mIG1vZHVsZSYmbW9kdWxlLmV4cG9ydHMmJihtb2R1bGUuZXhwb3J0cz1tKSksYz0wO3RoaXMuc2MubGVuZ3RoPmM7YysrKXRoaXMuc2NbY10uY2hlY2soKX0sdGhpcy5jaGVjayghMCl9LGM9dC5fZ3NEZWZpbmU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIG5ldyBwKHQsZSxpLHMpfSxkPWwuX2NsYXNzPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gZT1lfHxmdW5jdGlvbigpe30sYyh0LFtdLGZ1bmN0aW9uKCl7cmV0dXJuIGV9LGkpLGV9O2MuZ2xvYmFscz1lO3ZhciB2PVswLDAsMSwxXSxnPVtdLFQ9ZChcImVhc2luZy5FYXNlXCIsZnVuY3Rpb24odCxlLGkscyl7dGhpcy5fZnVuYz10LHRoaXMuX3R5cGU9aXx8MCx0aGlzLl9wb3dlcj1zfHwwLHRoaXMuX3BhcmFtcz1lP3YuY29uY2F0KGUpOnZ9LCEwKSx5PVQubWFwPXt9LHc9VC5yZWdpc3Rlcj1mdW5jdGlvbih0LGUsaSxzKXtmb3IodmFyIG4scixhLG8saD1lLnNwbGl0KFwiLFwiKSxfPWgubGVuZ3RoLHU9KGl8fFwiZWFzZUluLGVhc2VPdXQsZWFzZUluT3V0XCIpLnNwbGl0KFwiLFwiKTstLV8+LTE7KWZvcihyPWhbX10sbj1zP2QoXCJlYXNpbmcuXCIrcixudWxsLCEwKTpsLmVhc2luZ1tyXXx8e30sYT11Lmxlbmd0aDstLWE+LTE7KW89dVthXSx5W3IrXCIuXCIrb109eVtvK3JdPW5bb109dC5nZXRSYXRpbz90OnRbb118fG5ldyB0fTtmb3Iobj1ULnByb3RvdHlwZSxuLl9jYWxjRW5kPSExLG4uZ2V0UmF0aW89ZnVuY3Rpb24odCl7aWYodGhpcy5fZnVuYylyZXR1cm4gdGhpcy5fcGFyYW1zWzBdPXQsdGhpcy5fZnVuYy5hcHBseShudWxsLHRoaXMuX3BhcmFtcyk7dmFyIGU9dGhpcy5fdHlwZSxpPXRoaXMuX3Bvd2VyLHM9MT09PWU/MS10OjI9PT1lP3Q6LjU+dD8yKnQ6MiooMS10KTtyZXR1cm4gMT09PWk/cyo9czoyPT09aT9zKj1zKnM6Mz09PWk/cyo9cypzKnM6ND09PWkmJihzKj1zKnMqcypzKSwxPT09ZT8xLXM6Mj09PWU/czouNT50P3MvMjoxLXMvMn0saT1bXCJMaW5lYXJcIixcIlF1YWRcIixcIkN1YmljXCIsXCJRdWFydFwiLFwiUXVpbnQsU3Ryb25nXCJdLHM9aS5sZW5ndGg7LS1zPi0xOyluPWlbc10rXCIsUG93ZXJcIitzLHcobmV3IFQobnVsbCxudWxsLDEscyksbixcImVhc2VPdXRcIiwhMCksdyhuZXcgVChudWxsLG51bGwsMixzKSxuLFwiZWFzZUluXCIrKDA9PT1zP1wiLGVhc2VOb25lXCI6XCJcIikpLHcobmV3IFQobnVsbCxudWxsLDMscyksbixcImVhc2VJbk91dFwiKTt5LmxpbmVhcj1sLmVhc2luZy5MaW5lYXIuZWFzZUluLHkuc3dpbmc9bC5lYXNpbmcuUXVhZC5lYXNlSW5PdXQ7dmFyIFA9ZChcImV2ZW50cy5FdmVudERpc3BhdGNoZXJcIixmdW5jdGlvbih0KXt0aGlzLl9saXN0ZW5lcnM9e30sdGhpcy5fZXZlbnRUYXJnZXQ9dHx8dGhpc30pO249UC5wcm90b3R5cGUsbi5hZGRFdmVudExpc3RlbmVyPWZ1bmN0aW9uKHQsZSxpLHMsbil7bj1ufHwwO3ZhciBvLGwsaD10aGlzLl9saXN0ZW5lcnNbdF0sXz0wO2ZvcihudWxsPT1oJiYodGhpcy5fbGlzdGVuZXJzW3RdPWg9W10pLGw9aC5sZW5ndGg7LS1sPi0xOylvPWhbbF0sby5jPT09ZSYmby5zPT09aT9oLnNwbGljZShsLDEpOjA9PT1fJiZuPm8ucHImJihfPWwrMSk7aC5zcGxpY2UoXywwLHtjOmUsczppLHVwOnMscHI6bn0pLHRoaXMhPT1yfHxhfHxyLndha2UoKX0sbi5yZW1vdmVFdmVudExpc3RlbmVyPWZ1bmN0aW9uKHQsZSl7dmFyIGkscz10aGlzLl9saXN0ZW5lcnNbdF07aWYocylmb3IoaT1zLmxlbmd0aDstLWk+LTE7KWlmKHNbaV0uYz09PWUpcmV0dXJuIHMuc3BsaWNlKGksMSksdm9pZCAwfSxuLmRpc3BhdGNoRXZlbnQ9ZnVuY3Rpb24odCl7dmFyIGUsaSxzLG49dGhpcy5fbGlzdGVuZXJzW3RdO2lmKG4pZm9yKGU9bi5sZW5ndGgsaT10aGlzLl9ldmVudFRhcmdldDstLWU+LTE7KXM9bltlXSxzLnVwP3MuYy5jYWxsKHMuc3x8aSx7dHlwZTp0LHRhcmdldDppfSk6cy5jLmNhbGwocy5zfHxpKX07dmFyIGs9dC5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUsYj10LmNhbmNlbEFuaW1hdGlvbkZyYW1lLEE9RGF0ZS5ub3d8fGZ1bmN0aW9uKCl7cmV0dXJuKG5ldyBEYXRlKS5nZXRUaW1lKCl9LFM9QSgpO2ZvcihpPVtcIm1zXCIsXCJtb3pcIixcIndlYmtpdFwiLFwib1wiXSxzPWkubGVuZ3RoOy0tcz4tMSYmIWs7KWs9dFtpW3NdK1wiUmVxdWVzdEFuaW1hdGlvbkZyYW1lXCJdLGI9dFtpW3NdK1wiQ2FuY2VsQW5pbWF0aW9uRnJhbWVcIl18fHRbaVtzXStcIkNhbmNlbFJlcXVlc3RBbmltYXRpb25GcmFtZVwiXTtkKFwiVGlja2VyXCIsZnVuY3Rpb24odCxlKXt2YXIgaSxzLG4sbyxsLF89dGhpcyxtPUEoKSxmPWUhPT0hMSYmayxwPTUwMCxjPTMzLGQ9ZnVuY3Rpb24odCl7dmFyIGUscixhPUEoKS1TO2E+cCYmKG0rPWEtYyksUys9YSxfLnRpbWU9KFMtbSkvMWUzLGU9Xy50aW1lLWwsKCFpfHxlPjB8fHQ9PT0hMCkmJihfLmZyYW1lKyssbCs9ZSsoZT49bz8uMDA0Om8tZSkscj0hMCksdCE9PSEwJiYobj1zKGQpKSxyJiZfLmRpc3BhdGNoRXZlbnQoXCJ0aWNrXCIpfTtQLmNhbGwoXyksXy50aW1lPV8uZnJhbWU9MCxfLnRpY2s9ZnVuY3Rpb24oKXtkKCEwKX0sXy5sYWdTbW9vdGhpbmc9ZnVuY3Rpb24odCxlKXtwPXR8fDEvaCxjPU1hdGgubWluKGUscCwwKX0sXy5zbGVlcD1mdW5jdGlvbigpe251bGwhPW4mJihmJiZiP2Iobik6Y2xlYXJUaW1lb3V0KG4pLHM9dSxuPW51bGwsXz09PXImJihhPSExKSl9LF8ud2FrZT1mdW5jdGlvbigpe251bGwhPT1uP18uc2xlZXAoKTpfLmZyYW1lPjEwJiYoUz1BKCktcCs1KSxzPTA9PT1pP3U6ZiYmaz9rOmZ1bmN0aW9uKHQpe3JldHVybiBzZXRUaW1lb3V0KHQsMHwxZTMqKGwtXy50aW1lKSsxKX0sXz09PXImJihhPSEwKSxkKDIpfSxfLmZwcz1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oaT10LG89MS8oaXx8NjApLGw9dGhpcy50aW1lK28sXy53YWtlKCksdm9pZCAwKTppfSxfLnVzZVJBRj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oXy5zbGVlcCgpLGY9dCxfLmZwcyhpKSx2b2lkIDApOmZ9LF8uZnBzKHQpLHNldFRpbWVvdXQoZnVuY3Rpb24oKXtmJiYoIW58fDU+Xy5mcmFtZSkmJl8udXNlUkFGKCExKX0sMTUwMCl9KSxuPWwuVGlja2VyLnByb3RvdHlwZT1uZXcgbC5ldmVudHMuRXZlbnREaXNwYXRjaGVyLG4uY29uc3RydWN0b3I9bC5UaWNrZXI7dmFyIHg9ZChcImNvcmUuQW5pbWF0aW9uXCIsZnVuY3Rpb24odCxlKXtpZih0aGlzLnZhcnM9ZT1lfHx7fSx0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXR8fDAsdGhpcy5fZGVsYXk9TnVtYmVyKGUuZGVsYXkpfHwwLHRoaXMuX3RpbWVTY2FsZT0xLHRoaXMuX2FjdGl2ZT1lLmltbWVkaWF0ZVJlbmRlcj09PSEwLHRoaXMuZGF0YT1lLmRhdGEsdGhpcy5fcmV2ZXJzZWQ9ZS5yZXZlcnNlZD09PSEwLEIpe2F8fHIud2FrZSgpO3ZhciBpPXRoaXMudmFycy51c2VGcmFtZXM/UTpCO2kuYWRkKHRoaXMsaS5fdGltZSksdGhpcy52YXJzLnBhdXNlZCYmdGhpcy5wYXVzZWQoITApfX0pO3I9eC50aWNrZXI9bmV3IGwuVGlja2VyLG49eC5wcm90b3R5cGUsbi5fZGlydHk9bi5fZ2M9bi5faW5pdHRlZD1uLl9wYXVzZWQ9ITEsbi5fdG90YWxUaW1lPW4uX3RpbWU9MCxuLl9yYXdQcmV2VGltZT0tMSxuLl9uZXh0PW4uX2xhc3Q9bi5fb25VcGRhdGU9bi5fdGltZWxpbmU9bi50aW1lbGluZT1udWxsLG4uX3BhdXNlZD0hMTt2YXIgQz1mdW5jdGlvbigpe2EmJkEoKS1TPjJlMyYmci53YWtlKCksc2V0VGltZW91dChDLDJlMyl9O0MoKSxuLnBsYXk9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5yZXZlcnNlZCghMSkucGF1c2VkKCExKX0sbi5wYXVzZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnBhdXNlZCghMCl9LG4ucmVzdW1lPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucGF1c2VkKCExKX0sbi5zZWVrPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMudG90YWxUaW1lKE51bWJlcih0KSxlIT09ITEpfSxuLnJlc3RhcnQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5yZXZlcnNlZCghMSkucGF1c2VkKCExKS50b3RhbFRpbWUodD8tdGhpcy5fZGVsYXk6MCxlIT09ITEsITApfSxuLnJldmVyc2U9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHR8fHRoaXMudG90YWxEdXJhdGlvbigpLGUpLHRoaXMucmV2ZXJzZWQoITApLnBhdXNlZCghMSl9LG4ucmVuZGVyPWZ1bmN0aW9uKCl7fSxuLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpc30sbi5pc0FjdGl2ZT1mdW5jdGlvbigpe3ZhciB0LGU9dGhpcy5fdGltZWxpbmUsaT10aGlzLl9zdGFydFRpbWU7cmV0dXJuIWV8fCF0aGlzLl9nYyYmIXRoaXMuX3BhdXNlZCYmZS5pc0FjdGl2ZSgpJiYodD1lLnJhd1RpbWUoKSk+PWkmJmkrdGhpcy50b3RhbER1cmF0aW9uKCkvdGhpcy5fdGltZVNjYWxlPnR9LG4uX2VuYWJsZWQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXx8ci53YWtlKCksdGhpcy5fZ2M9IXQsdGhpcy5fYWN0aXZlPXRoaXMuaXNBY3RpdmUoKSxlIT09ITAmJih0JiYhdGhpcy50aW1lbGluZT90aGlzLl90aW1lbGluZS5hZGQodGhpcyx0aGlzLl9zdGFydFRpbWUtdGhpcy5fZGVsYXkpOiF0JiZ0aGlzLnRpbWVsaW5lJiZ0aGlzLl90aW1lbGluZS5fcmVtb3ZlKHRoaXMsITApKSwhMX0sbi5fa2lsbD1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9lbmFibGVkKCExLCExKX0sbi5raWxsPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuX2tpbGwodCxlKSx0aGlzfSxuLl91bmNhY2hlPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10P3RoaXM6dGhpcy50aW1lbGluZTtlOyllLl9kaXJ0eT0hMCxlPWUudGltZWxpbmU7cmV0dXJuIHRoaXN9LG4uX3N3YXBTZWxmSW5QYXJhbXM9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQubGVuZ3RoLGk9dC5jb25jYXQoKTstLWU+LTE7KVwie3NlbGZ9XCI9PT10W2VdJiYoaVtlXT10aGlzKTtyZXR1cm4gaX0sbi5ldmVudENhbGxiYWNrPWZ1bmN0aW9uKHQsZSxpLHMpe2lmKFwib25cIj09PSh0fHxcIlwiKS5zdWJzdHIoMCwyKSl7dmFyIG49dGhpcy52YXJzO2lmKDE9PT1hcmd1bWVudHMubGVuZ3RoKXJldHVybiBuW3RdO251bGw9PWU/ZGVsZXRlIG5bdF06KG5bdF09ZSxuW3QrXCJQYXJhbXNcIl09bShpKSYmLTEhPT1pLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKT90aGlzLl9zd2FwU2VsZkluUGFyYW1zKGkpOmksblt0K1wiU2NvcGVcIl09cyksXCJvblVwZGF0ZVwiPT09dCYmKHRoaXMuX29uVXBkYXRlPWUpfXJldHVybiB0aGlzfSxuLmRlbGF5PWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmdGhpcy5zdGFydFRpbWUodGhpcy5fc3RhcnRUaW1lK3QtdGhpcy5fZGVsYXkpLHRoaXMuX2RlbGF5PXQsdGhpcyk6dGhpcy5fZGVsYXl9LG4uZHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249dCx0aGlzLl91bmNhY2hlKCEwKSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmdGhpcy5fdGltZT4wJiZ0aGlzLl90aW1lPHRoaXMuX2R1cmF0aW9uJiYwIT09dCYmdGhpcy50b3RhbFRpbWUodGhpcy5fdG90YWxUaW1lKih0L3RoaXMuX2R1cmF0aW9uKSwhMCksdGhpcyk6KHRoaXMuX2RpcnR5PSExLHRoaXMuX2R1cmF0aW9uKX0sbi50b3RhbER1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiB0aGlzLl9kaXJ0eT0hMSxhcmd1bWVudHMubGVuZ3RoP3RoaXMuZHVyYXRpb24odCk6dGhpcy5fdG90YWxEdXJhdGlvbn0sbi50aW1lPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKSx0aGlzLnRvdGFsVGltZSh0PnRoaXMuX2R1cmF0aW9uP3RoaXMuX2R1cmF0aW9uOnQsZSkpOnRoaXMuX3RpbWV9LG4udG90YWxUaW1lPWZ1bmN0aW9uKHQsZSxpKXtpZihhfHxyLndha2UoKSwhYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fdG90YWxUaW1lO2lmKHRoaXMuX3RpbWVsaW5lKXtpZigwPnQmJiFpJiYodCs9dGhpcy50b3RhbER1cmF0aW9uKCkpLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nKXt0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCk7dmFyIHM9dGhpcy5fdG90YWxEdXJhdGlvbixuPXRoaXMuX3RpbWVsaW5lO2lmKHQ+cyYmIWkmJih0PXMpLHRoaXMuX3N0YXJ0VGltZT0odGhpcy5fcGF1c2VkP3RoaXMuX3BhdXNlVGltZTpuLl90aW1lKS0odGhpcy5fcmV2ZXJzZWQ/cy10OnQpL3RoaXMuX3RpbWVTY2FsZSxuLl9kaXJ0eXx8dGhpcy5fdW5jYWNoZSghMSksbi5fdGltZWxpbmUpZm9yKDtuLl90aW1lbGluZTspbi5fdGltZWxpbmUuX3RpbWUhPT0obi5fc3RhcnRUaW1lK24uX3RvdGFsVGltZSkvbi5fdGltZVNjYWxlJiZuLnRvdGFsVGltZShuLl90b3RhbFRpbWUsITApLG49bi5fdGltZWxpbmV9dGhpcy5fZ2MmJnRoaXMuX2VuYWJsZWQoITAsITEpLCh0aGlzLl90b3RhbFRpbWUhPT10fHwwPT09dGhpcy5fZHVyYXRpb24pJiYodGhpcy5yZW5kZXIodCxlLCExKSx6Lmxlbmd0aCYmcSgpKX1yZXR1cm4gdGhpc30sbi5wcm9ncmVzcz1uLnRvdGFsUHJvZ3Jlc3M9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD90aGlzLnRvdGFsVGltZSh0aGlzLmR1cmF0aW9uKCkqdCxlKTp0aGlzLl90aW1lL3RoaXMuZHVyYXRpb24oKX0sbi5zdGFydFRpbWU9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHQhPT10aGlzLl9zdGFydFRpbWUmJih0aGlzLl9zdGFydFRpbWU9dCx0aGlzLnRpbWVsaW5lJiZ0aGlzLnRpbWVsaW5lLl9zb3J0Q2hpbGRyZW4mJnRoaXMudGltZWxpbmUuYWRkKHRoaXMsdC10aGlzLl9kZWxheSkpLHRoaXMpOnRoaXMuX3N0YXJ0VGltZX0sbi50aW1lU2NhbGU9ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3RpbWVTY2FsZTtpZih0PXR8fGgsdGhpcy5fdGltZWxpbmUmJnRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nKXt2YXIgZT10aGlzLl9wYXVzZVRpbWUsaT1lfHwwPT09ZT9lOnRoaXMuX3RpbWVsaW5lLnRvdGFsVGltZSgpO3RoaXMuX3N0YXJ0VGltZT1pLShpLXRoaXMuX3N0YXJ0VGltZSkqdGhpcy5fdGltZVNjYWxlL3R9cmV0dXJuIHRoaXMuX3RpbWVTY2FsZT10LHRoaXMuX3VuY2FjaGUoITEpfSxuLnJldmVyc2VkPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0IT10aGlzLl9yZXZlcnNlZCYmKHRoaXMuX3JldmVyc2VkPXQsdGhpcy50b3RhbFRpbWUodGhpcy5fdGltZWxpbmUmJiF0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZz90aGlzLnRvdGFsRHVyYXRpb24oKS10aGlzLl90b3RhbFRpbWU6dGhpcy5fdG90YWxUaW1lLCEwKSksdGhpcyk6dGhpcy5fcmV2ZXJzZWR9LG4ucGF1c2VkPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl9wYXVzZWQ7aWYodCE9dGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lbGluZSl7YXx8dHx8ci53YWtlKCk7dmFyIGU9dGhpcy5fdGltZWxpbmUsaT1lLnJhd1RpbWUoKSxzPWktdGhpcy5fcGF1c2VUaW1lOyF0JiZlLnNtb290aENoaWxkVGltaW5nJiYodGhpcy5fc3RhcnRUaW1lKz1zLHRoaXMuX3VuY2FjaGUoITEpKSx0aGlzLl9wYXVzZVRpbWU9dD9pOm51bGwsdGhpcy5fcGF1c2VkPXQsdGhpcy5fYWN0aXZlPXRoaXMuaXNBY3RpdmUoKSwhdCYmMCE9PXMmJnRoaXMuX2luaXR0ZWQmJnRoaXMuZHVyYXRpb24oKSYmdGhpcy5yZW5kZXIoZS5zbW9vdGhDaGlsZFRpbWluZz90aGlzLl90b3RhbFRpbWU6KGktdGhpcy5fc3RhcnRUaW1lKS90aGlzLl90aW1lU2NhbGUsITAsITApfXJldHVybiB0aGlzLl9nYyYmIXQmJnRoaXMuX2VuYWJsZWQoITAsITEpLHRoaXN9O3ZhciBSPWQoXCJjb3JlLlNpbXBsZVRpbWVsaW5lXCIsZnVuY3Rpb24odCl7eC5jYWxsKHRoaXMsMCx0KSx0aGlzLmF1dG9SZW1vdmVDaGlsZHJlbj10aGlzLnNtb290aENoaWxkVGltaW5nPSEwfSk7bj1SLnByb3RvdHlwZT1uZXcgeCxuLmNvbnN0cnVjdG9yPVIsbi5raWxsKCkuX2djPSExLG4uX2ZpcnN0PW4uX2xhc3Q9bnVsbCxuLl9zb3J0Q2hpbGRyZW49ITEsbi5hZGQ9bi5pbnNlcnQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxzO2lmKHQuX3N0YXJ0VGltZT1OdW1iZXIoZXx8MCkrdC5fZGVsYXksdC5fcGF1c2VkJiZ0aGlzIT09dC5fdGltZWxpbmUmJih0Ll9wYXVzZVRpbWU9dC5fc3RhcnRUaW1lKyh0aGlzLnJhd1RpbWUoKS10Ll9zdGFydFRpbWUpL3QuX3RpbWVTY2FsZSksdC50aW1lbGluZSYmdC50aW1lbGluZS5fcmVtb3ZlKHQsITApLHQudGltZWxpbmU9dC5fdGltZWxpbmU9dGhpcyx0Ll9nYyYmdC5fZW5hYmxlZCghMCwhMCksaT10aGlzLl9sYXN0LHRoaXMuX3NvcnRDaGlsZHJlbilmb3Iocz10Ll9zdGFydFRpbWU7aSYmaS5fc3RhcnRUaW1lPnM7KWk9aS5fcHJldjtyZXR1cm4gaT8odC5fbmV4dD1pLl9uZXh0LGkuX25leHQ9dCk6KHQuX25leHQ9dGhpcy5fZmlyc3QsdGhpcy5fZmlyc3Q9dCksdC5fbmV4dD90Ll9uZXh0Ll9wcmV2PXQ6dGhpcy5fbGFzdD10LHQuX3ByZXY9aSx0aGlzLl90aW1lbGluZSYmdGhpcy5fdW5jYWNoZSghMCksdGhpc30sbi5fcmVtb3ZlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHQudGltZWxpbmU9PT10aGlzJiYoZXx8dC5fZW5hYmxlZCghMSwhMCksdC50aW1lbGluZT1udWxsLHQuX3ByZXY/dC5fcHJldi5fbmV4dD10Ll9uZXh0OnRoaXMuX2ZpcnN0PT09dCYmKHRoaXMuX2ZpcnN0PXQuX25leHQpLHQuX25leHQ/dC5fbmV4dC5fcHJldj10Ll9wcmV2OnRoaXMuX2xhc3Q9PT10JiYodGhpcy5fbGFzdD10Ll9wcmV2KSx0aGlzLl90aW1lbGluZSYmdGhpcy5fdW5jYWNoZSghMCkpLHRoaXN9LG4ucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuPXRoaXMuX2ZpcnN0O2Zvcih0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10aGlzLl9yYXdQcmV2VGltZT10O247KXM9bi5fbmV4dCwobi5fYWN0aXZlfHx0Pj1uLl9zdGFydFRpbWUmJiFuLl9wYXVzZWQpJiYobi5fcmV2ZXJzZWQ/bi5yZW5kZXIoKG4uX2RpcnR5P24udG90YWxEdXJhdGlvbigpOm4uX3RvdGFsRHVyYXRpb24pLSh0LW4uX3N0YXJ0VGltZSkqbi5fdGltZVNjYWxlLGUsaSk6bi5yZW5kZXIoKHQtbi5fc3RhcnRUaW1lKSpuLl90aW1lU2NhbGUsZSxpKSksbj1zfSxuLnJhd1RpbWU9ZnVuY3Rpb24oKXtyZXR1cm4gYXx8ci53YWtlKCksdGhpcy5fdG90YWxUaW1lfTt2YXIgRD1kKFwiVHdlZW5MaXRlXCIsZnVuY3Rpb24oZSxpLHMpe2lmKHguY2FsbCh0aGlzLGkscyksdGhpcy5yZW5kZXI9RC5wcm90b3R5cGUucmVuZGVyLG51bGw9PWUpdGhyb3dcIkNhbm5vdCB0d2VlbiBhIG51bGwgdGFyZ2V0LlwiO3RoaXMudGFyZ2V0PWU9XCJzdHJpbmdcIiE9dHlwZW9mIGU/ZTpELnNlbGVjdG9yKGUpfHxlO3ZhciBuLHIsYSxvPWUuanF1ZXJ5fHxlLmxlbmd0aCYmZSE9PXQmJmVbMF0mJihlWzBdPT09dHx8ZVswXS5ub2RlVHlwZSYmZVswXS5zdHlsZSYmIWUubm9kZVR5cGUpLGw9dGhpcy52YXJzLm92ZXJ3cml0ZTtpZih0aGlzLl9vdmVyd3JpdGU9bD1udWxsPT1sP0dbRC5kZWZhdWx0T3ZlcndyaXRlXTpcIm51bWJlclwiPT10eXBlb2YgbD9sPj4wOkdbbF0sKG98fGUgaW5zdGFuY2VvZiBBcnJheXx8ZS5wdXNoJiZtKGUpKSYmXCJudW1iZXJcIiE9dHlwZW9mIGVbMF0pZm9yKHRoaXMuX3RhcmdldHM9YT1fLmNhbGwoZSwwKSx0aGlzLl9wcm9wTG9va3VwPVtdLHRoaXMuX3NpYmxpbmdzPVtdLG49MDthLmxlbmd0aD5uO24rKylyPWFbbl0scj9cInN0cmluZ1wiIT10eXBlb2Ygcj9yLmxlbmd0aCYmciE9PXQmJnJbMF0mJihyWzBdPT09dHx8clswXS5ub2RlVHlwZSYmclswXS5zdHlsZSYmIXIubm9kZVR5cGUpPyhhLnNwbGljZShuLS0sMSksdGhpcy5fdGFyZ2V0cz1hPWEuY29uY2F0KF8uY2FsbChyLDApKSk6KHRoaXMuX3NpYmxpbmdzW25dPU0ocix0aGlzLCExKSwxPT09bCYmdGhpcy5fc2libGluZ3Nbbl0ubGVuZ3RoPjEmJiQocix0aGlzLG51bGwsMSx0aGlzLl9zaWJsaW5nc1tuXSkpOihyPWFbbi0tXT1ELnNlbGVjdG9yKHIpLFwic3RyaW5nXCI9PXR5cGVvZiByJiZhLnNwbGljZShuKzEsMSkpOmEuc3BsaWNlKG4tLSwxKTtlbHNlIHRoaXMuX3Byb3BMb29rdXA9e30sdGhpcy5fc2libGluZ3M9TShlLHRoaXMsITEpLDE9PT1sJiZ0aGlzLl9zaWJsaW5ncy5sZW5ndGg+MSYmJChlLHRoaXMsbnVsbCwxLHRoaXMuX3NpYmxpbmdzKTsodGhpcy52YXJzLmltbWVkaWF0ZVJlbmRlcnx8MD09PWkmJjA9PT10aGlzLl9kZWxheSYmdGhpcy52YXJzLmltbWVkaWF0ZVJlbmRlciE9PSExKSYmKHRoaXMuX3RpbWU9LWgsdGhpcy5yZW5kZXIoLXRoaXMuX2RlbGF5KSl9LCEwKSxJPWZ1bmN0aW9uKGUpe3JldHVybiBlLmxlbmd0aCYmZSE9PXQmJmVbMF0mJihlWzBdPT09dHx8ZVswXS5ub2RlVHlwZSYmZVswXS5zdHlsZSYmIWUubm9kZVR5cGUpfSxFPWZ1bmN0aW9uKHQsZSl7dmFyIGkscz17fTtmb3IoaSBpbiB0KWpbaV18fGkgaW4gZSYmXCJ0cmFuc2Zvcm1cIiE9PWkmJlwieFwiIT09aSYmXCJ5XCIhPT1pJiZcIndpZHRoXCIhPT1pJiZcImhlaWdodFwiIT09aSYmXCJjbGFzc05hbWVcIiE9PWkmJlwiYm9yZGVyXCIhPT1pfHwhKCFMW2ldfHxMW2ldJiZMW2ldLl9hdXRvQ1NTKXx8KHNbaV09dFtpXSxkZWxldGUgdFtpXSk7dC5jc3M9c307bj1ELnByb3RvdHlwZT1uZXcgeCxuLmNvbnN0cnVjdG9yPUQsbi5raWxsKCkuX2djPSExLG4ucmF0aW89MCxuLl9maXJzdFBUPW4uX3RhcmdldHM9bi5fb3ZlcndyaXR0ZW5Qcm9wcz1uLl9zdGFydEF0PW51bGwsbi5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD1uLl9sYXp5PSExLEQudmVyc2lvbj1cIjEuMTIuMVwiLEQuZGVmYXVsdEVhc2U9bi5fZWFzZT1uZXcgVChudWxsLG51bGwsMSwxKSxELmRlZmF1bHRPdmVyd3JpdGU9XCJhdXRvXCIsRC50aWNrZXI9cixELmF1dG9TbGVlcD0hMCxELmxhZ1Ntb290aGluZz1mdW5jdGlvbih0LGUpe3IubGFnU21vb3RoaW5nKHQsZSl9LEQuc2VsZWN0b3I9dC4kfHx0LmpRdWVyeXx8ZnVuY3Rpb24oZSl7cmV0dXJuIHQuJD8oRC5zZWxlY3Rvcj10LiQsdC4kKGUpKTp0LmRvY3VtZW50P3QuZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCIjXCI9PT1lLmNoYXJBdCgwKT9lLnN1YnN0cigxKTplKTplfTt2YXIgej1bXSxPPXt9LE49RC5faW50ZXJuYWxzPXtpc0FycmF5Om0saXNTZWxlY3RvcjpJLGxhenlUd2VlbnM6en0sTD1ELl9wbHVnaW5zPXt9LFU9Ti50d2Vlbkxvb2t1cD17fSxGPTAsaj1OLnJlc2VydmVkUHJvcHM9e2Vhc2U6MSxkZWxheToxLG92ZXJ3cml0ZToxLG9uQ29tcGxldGU6MSxvbkNvbXBsZXRlUGFyYW1zOjEsb25Db21wbGV0ZVNjb3BlOjEsdXNlRnJhbWVzOjEscnVuQmFja3dhcmRzOjEsc3RhcnRBdDoxLG9uVXBkYXRlOjEsb25VcGRhdGVQYXJhbXM6MSxvblVwZGF0ZVNjb3BlOjEsb25TdGFydDoxLG9uU3RhcnRQYXJhbXM6MSxvblN0YXJ0U2NvcGU6MSxvblJldmVyc2VDb21wbGV0ZToxLG9uUmV2ZXJzZUNvbXBsZXRlUGFyYW1zOjEsb25SZXZlcnNlQ29tcGxldGVTY29wZToxLG9uUmVwZWF0OjEsb25SZXBlYXRQYXJhbXM6MSxvblJlcGVhdFNjb3BlOjEsZWFzZVBhcmFtczoxLHlveW86MSxpbW1lZGlhdGVSZW5kZXI6MSxyZXBlYXQ6MSxyZXBlYXREZWxheToxLGRhdGE6MSxwYXVzZWQ6MSxyZXZlcnNlZDoxLGF1dG9DU1M6MSxsYXp5OjF9LEc9e25vbmU6MCxhbGw6MSxhdXRvOjIsY29uY3VycmVudDozLGFsbE9uU3RhcnQ6NCxwcmVleGlzdGluZzo1LFwidHJ1ZVwiOjEsXCJmYWxzZVwiOjB9LFE9eC5fcm9vdEZyYW1lc1RpbWVsaW5lPW5ldyBSLEI9eC5fcm9vdFRpbWVsaW5lPW5ldyBSLHE9ZnVuY3Rpb24oKXt2YXIgdD16Lmxlbmd0aDtmb3IoTz17fTstLXQ+LTE7KWk9elt0XSxpJiZpLl9sYXp5IT09ITEmJihpLnJlbmRlcihpLl9sYXp5LCExLCEwKSxpLl9sYXp5PSExKTt6Lmxlbmd0aD0wfTtCLl9zdGFydFRpbWU9ci50aW1lLFEuX3N0YXJ0VGltZT1yLmZyYW1lLEIuX2FjdGl2ZT1RLl9hY3RpdmU9ITAsc2V0VGltZW91dChxLDEpLHguX3VwZGF0ZVJvb3Q9RC5yZW5kZXI9ZnVuY3Rpb24oKXt2YXIgdCxlLGk7aWYoei5sZW5ndGgmJnEoKSxCLnJlbmRlcigoci50aW1lLUIuX3N0YXJ0VGltZSkqQi5fdGltZVNjYWxlLCExLCExKSxRLnJlbmRlcigoci5mcmFtZS1RLl9zdGFydFRpbWUpKlEuX3RpbWVTY2FsZSwhMSwhMSksei5sZW5ndGgmJnEoKSwhKHIuZnJhbWUlMTIwKSl7Zm9yKGkgaW4gVSl7Zm9yKGU9VVtpXS50d2VlbnMsdD1lLmxlbmd0aDstLXQ+LTE7KWVbdF0uX2djJiZlLnNwbGljZSh0LDEpOzA9PT1lLmxlbmd0aCYmZGVsZXRlIFVbaV19aWYoaT1CLl9maXJzdCwoIWl8fGkuX3BhdXNlZCkmJkQuYXV0b1NsZWVwJiYhUS5fZmlyc3QmJjE9PT1yLl9saXN0ZW5lcnMudGljay5sZW5ndGgpe2Zvcig7aSYmaS5fcGF1c2VkOylpPWkuX25leHQ7aXx8ci5zbGVlcCgpfX19LHIuYWRkRXZlbnRMaXN0ZW5lcihcInRpY2tcIix4Ll91cGRhdGVSb290KTt2YXIgTT1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbixyPXQuX2dzVHdlZW5JRDtpZihVW3J8fCh0Ll9nc1R3ZWVuSUQ9cj1cInRcIitGKyspXXx8KFVbcl09e3RhcmdldDp0LHR3ZWVuczpbXX0pLGUmJihzPVVbcl0udHdlZW5zLHNbbj1zLmxlbmd0aF09ZSxpKSlmb3IoOy0tbj4tMTspc1tuXT09PWUmJnMuc3BsaWNlKG4sMSk7cmV0dXJuIFVbcl0udHdlZW5zfSwkPWZ1bmN0aW9uKHQsZSxpLHMsbil7dmFyIHIsYSxvLGw7aWYoMT09PXN8fHM+PTQpe2ZvcihsPW4ubGVuZ3RoLHI9MDtsPnI7cisrKWlmKChvPW5bcl0pIT09ZSlvLl9nY3x8by5fZW5hYmxlZCghMSwhMSkmJihhPSEwKTtlbHNlIGlmKDU9PT1zKWJyZWFrO3JldHVybiBhfXZhciBfLHU9ZS5fc3RhcnRUaW1lK2gsbT1bXSxmPTAscD0wPT09ZS5fZHVyYXRpb247Zm9yKHI9bi5sZW5ndGg7LS1yPi0xOykobz1uW3JdKT09PWV8fG8uX2djfHxvLl9wYXVzZWR8fChvLl90aW1lbGluZSE9PWUuX3RpbWVsaW5lPyhfPV98fEsoZSwwLHApLDA9PT1LKG8sXyxwKSYmKG1bZisrXT1vKSk6dT49by5fc3RhcnRUaW1lJiZvLl9zdGFydFRpbWUrby50b3RhbER1cmF0aW9uKCkvby5fdGltZVNjYWxlPnUmJigocHx8IW8uX2luaXR0ZWQpJiYyZS0xMD49dS1vLl9zdGFydFRpbWV8fChtW2YrK109bykpKTtmb3Iocj1mOy0tcj4tMTspbz1tW3JdLDI9PT1zJiZvLl9raWxsKGksdCkmJihhPSEwKSwoMiE9PXN8fCFvLl9maXJzdFBUJiZvLl9pbml0dGVkKSYmby5fZW5hYmxlZCghMSwhMSkmJihhPSEwKTtyZXR1cm4gYX0sSz1mdW5jdGlvbih0LGUsaSl7Zm9yKHZhciBzPXQuX3RpbWVsaW5lLG49cy5fdGltZVNjYWxlLHI9dC5fc3RhcnRUaW1lO3MuX3RpbWVsaW5lOyl7aWYocis9cy5fc3RhcnRUaW1lLG4qPXMuX3RpbWVTY2FsZSxzLl9wYXVzZWQpcmV0dXJuLTEwMDtzPXMuX3RpbWVsaW5lfXJldHVybiByLz1uLHI+ZT9yLWU6aSYmcj09PWV8fCF0Ll9pbml0dGVkJiYyKmg+ci1lP2g6KHIrPXQudG90YWxEdXJhdGlvbigpL3QuX3RpbWVTY2FsZS9uKT5lK2g/MDpyLWUtaH07bi5faW5pdD1mdW5jdGlvbigpe3ZhciB0LGUsaSxzLG4scj10aGlzLnZhcnMsYT10aGlzLl9vdmVyd3JpdHRlblByb3BzLG89dGhpcy5fZHVyYXRpb24sbD0hIXIuaW1tZWRpYXRlUmVuZGVyLGg9ci5lYXNlO2lmKHIuc3RhcnRBdCl7dGhpcy5fc3RhcnRBdCYmKHRoaXMuX3N0YXJ0QXQucmVuZGVyKC0xLCEwKSx0aGlzLl9zdGFydEF0LmtpbGwoKSksbj17fTtmb3IocyBpbiByLnN0YXJ0QXQpbltzXT1yLnN0YXJ0QXRbc107aWYobi5vdmVyd3JpdGU9ITEsbi5pbW1lZGlhdGVSZW5kZXI9ITAsbi5sYXp5PWwmJnIubGF6eSE9PSExLG4uc3RhcnRBdD1uLmRlbGF5PW51bGwsdGhpcy5fc3RhcnRBdD1ELnRvKHRoaXMudGFyZ2V0LDAsbiksbClpZih0aGlzLl90aW1lPjApdGhpcy5fc3RhcnRBdD1udWxsO2Vsc2UgaWYoMCE9PW8pcmV0dXJufWVsc2UgaWYoci5ydW5CYWNrd2FyZHMmJjAhPT1vKWlmKHRoaXMuX3N0YXJ0QXQpdGhpcy5fc3RhcnRBdC5yZW5kZXIoLTEsITApLHRoaXMuX3N0YXJ0QXQua2lsbCgpLHRoaXMuX3N0YXJ0QXQ9bnVsbDtlbHNle2k9e307Zm9yKHMgaW4gcilqW3NdJiZcImF1dG9DU1NcIiE9PXN8fChpW3NdPXJbc10pO2lmKGkub3ZlcndyaXRlPTAsaS5kYXRhPVwiaXNGcm9tU3RhcnRcIixpLmxhenk9bCYmci5sYXp5IT09ITEsaS5pbW1lZGlhdGVSZW5kZXI9bCx0aGlzLl9zdGFydEF0PUQudG8odGhpcy50YXJnZXQsMCxpKSxsKXtpZigwPT09dGhpcy5fdGltZSlyZXR1cm59ZWxzZSB0aGlzLl9zdGFydEF0Ll9pbml0KCksdGhpcy5fc3RhcnRBdC5fZW5hYmxlZCghMSl9aWYodGhpcy5fZWFzZT1oP2ggaW5zdGFuY2VvZiBUP3IuZWFzZVBhcmFtcyBpbnN0YW5jZW9mIEFycmF5P2guY29uZmlnLmFwcGx5KGgsci5lYXNlUGFyYW1zKTpoOlwiZnVuY3Rpb25cIj09dHlwZW9mIGg/bmV3IFQoaCxyLmVhc2VQYXJhbXMpOnlbaF18fEQuZGVmYXVsdEVhc2U6RC5kZWZhdWx0RWFzZSx0aGlzLl9lYXNlVHlwZT10aGlzLl9lYXNlLl90eXBlLHRoaXMuX2Vhc2VQb3dlcj10aGlzLl9lYXNlLl9wb3dlcix0aGlzLl9maXJzdFBUPW51bGwsdGhpcy5fdGFyZ2V0cylmb3IodD10aGlzLl90YXJnZXRzLmxlbmd0aDstLXQ+LTE7KXRoaXMuX2luaXRQcm9wcyh0aGlzLl90YXJnZXRzW3RdLHRoaXMuX3Byb3BMb29rdXBbdF09e30sdGhpcy5fc2libGluZ3NbdF0sYT9hW3RdOm51bGwpJiYoZT0hMCk7ZWxzZSBlPXRoaXMuX2luaXRQcm9wcyh0aGlzLnRhcmdldCx0aGlzLl9wcm9wTG9va3VwLHRoaXMuX3NpYmxpbmdzLGEpO2lmKGUmJkQuX29uUGx1Z2luRXZlbnQoXCJfb25Jbml0QWxsUHJvcHNcIix0aGlzKSxhJiYodGhpcy5fZmlyc3RQVHx8XCJmdW5jdGlvblwiIT10eXBlb2YgdGhpcy50YXJnZXQmJnRoaXMuX2VuYWJsZWQoITEsITEpKSxyLnJ1bkJhY2t3YXJkcylmb3IoaT10aGlzLl9maXJzdFBUO2k7KWkucys9aS5jLGkuYz0taS5jLGk9aS5fbmV4dDt0aGlzLl9vblVwZGF0ZT1yLm9uVXBkYXRlLHRoaXMuX2luaXR0ZWQ9ITB9LG4uX2luaXRQcm9wcz1mdW5jdGlvbihlLGkscyxuKXt2YXIgcixhLG8sbCxoLF87aWYobnVsbD09ZSlyZXR1cm4hMTtPW2UuX2dzVHdlZW5JRF0mJnEoKSx0aGlzLnZhcnMuY3NzfHxlLnN0eWxlJiZlIT09dCYmZS5ub2RlVHlwZSYmTC5jc3MmJnRoaXMudmFycy5hdXRvQ1NTIT09ITEmJkUodGhpcy52YXJzLGUpO2ZvcihyIGluIHRoaXMudmFycyl7aWYoXz10aGlzLnZhcnNbcl0saltyXSlfJiYoXyBpbnN0YW5jZW9mIEFycmF5fHxfLnB1c2gmJm0oXykpJiYtMSE9PV8uam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpJiYodGhpcy52YXJzW3JdPV89dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhfLHRoaXMpKTtlbHNlIGlmKExbcl0mJihsPW5ldyBMW3JdKS5fb25Jbml0VHdlZW4oZSx0aGlzLnZhcnNbcl0sdGhpcykpe2Zvcih0aGlzLl9maXJzdFBUPWg9e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDpsLHA6XCJzZXRSYXRpb1wiLHM6MCxjOjEsZjohMCxuOnIscGc6ITAscHI6bC5fcHJpb3JpdHl9LGE9bC5fb3ZlcndyaXRlUHJvcHMubGVuZ3RoOy0tYT4tMTspaVtsLl9vdmVyd3JpdGVQcm9wc1thXV09dGhpcy5fZmlyc3RQVDsobC5fcHJpb3JpdHl8fGwuX29uSW5pdEFsbFByb3BzKSYmKG89ITApLChsLl9vbkRpc2FibGV8fGwuX29uRW5hYmxlKSYmKHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9ITApfWVsc2UgdGhpcy5fZmlyc3RQVD1pW3JdPWg9e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDplLHA6cixmOlwiZnVuY3Rpb25cIj09dHlwZW9mIGVbcl0sbjpyLHBnOiExLHByOjB9LGgucz1oLmY/ZVtyLmluZGV4T2YoXCJzZXRcIil8fFwiZnVuY3Rpb25cIiE9dHlwZW9mIGVbXCJnZXRcIityLnN1YnN0cigzKV0/cjpcImdldFwiK3Iuc3Vic3RyKDMpXSgpOnBhcnNlRmxvYXQoZVtyXSksaC5jPVwic3RyaW5nXCI9PXR5cGVvZiBfJiZcIj1cIj09PV8uY2hhckF0KDEpP3BhcnNlSW50KF8uY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIoXy5zdWJzdHIoMikpOk51bWJlcihfKS1oLnN8fDA7aCYmaC5fbmV4dCYmKGguX25leHQuX3ByZXY9aCl9cmV0dXJuIG4mJnRoaXMuX2tpbGwobixlKT90aGlzLl9pbml0UHJvcHMoZSxpLHMsbik6dGhpcy5fb3ZlcndyaXRlPjEmJnRoaXMuX2ZpcnN0UFQmJnMubGVuZ3RoPjEmJiQoZSx0aGlzLGksdGhpcy5fb3ZlcndyaXRlLHMpPyh0aGlzLl9raWxsKGksZSksdGhpcy5faW5pdFByb3BzKGUsaSxzLG4pKToodGhpcy5fZmlyc3RQVCYmKHRoaXMudmFycy5sYXp5IT09ITEmJnRoaXMuX2R1cmF0aW9ufHx0aGlzLnZhcnMubGF6eSYmIXRoaXMuX2R1cmF0aW9uKSYmKE9bZS5fZ3NUd2VlbklEXT0hMCksbyl9LG4ucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuLHIsYSxvPXRoaXMuX3RpbWUsbD10aGlzLl9kdXJhdGlvbixfPXRoaXMuX3Jhd1ByZXZUaW1lO2lmKHQ+PWwpdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9bCx0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuX2NhbGNFbmQ/dGhpcy5fZWFzZS5nZXRSYXRpbygxKToxLHRoaXMuX3JldmVyc2VkfHwocz0hMCxuPVwib25Db21wbGV0ZVwiKSwwPT09bCYmKHRoaXMuX2luaXR0ZWR8fCF0aGlzLnZhcnMubGF6eXx8aSkmJih0aGlzLl9zdGFydFRpbWU9PT10aGlzLl90aW1lbGluZS5fZHVyYXRpb24mJih0PTApLCgwPT09dHx8MD5ffHxfPT09aCkmJl8hPT10JiYoaT0hMCxfPmgmJihuPVwib25SZXZlcnNlQ29tcGxldGVcIikpLHRoaXMuX3Jhd1ByZXZUaW1lPWE9IWV8fHR8fF89PT10P3Q6aCk7ZWxzZSBpZigxZS03PnQpdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9MCx0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuX2NhbGNFbmQ/dGhpcy5fZWFzZS5nZXRSYXRpbygwKTowLCgwIT09b3x8MD09PWwmJl8+MCYmXyE9PWgpJiYobj1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIscz10aGlzLl9yZXZlcnNlZCksMD50Pyh0aGlzLl9hY3RpdmU9ITEsMD09PWwmJih0aGlzLl9pbml0dGVkfHwhdGhpcy52YXJzLmxhenl8fGkpJiYoXz49MCYmKGk9ITApLHRoaXMuX3Jhd1ByZXZUaW1lPWE9IWV8fHR8fF89PT10P3Q6aCkpOnRoaXMuX2luaXR0ZWR8fChpPSEwKTtlbHNlIGlmKHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXQsdGhpcy5fZWFzZVR5cGUpe3ZhciB1PXQvbCxtPXRoaXMuX2Vhc2VUeXBlLGY9dGhpcy5fZWFzZVBvd2VyOygxPT09bXx8Mz09PW0mJnU+PS41KSYmKHU9MS11KSwzPT09bSYmKHUqPTIpLDE9PT1mP3UqPXU6Mj09PWY/dSo9dSp1OjM9PT1mP3UqPXUqdSp1OjQ9PT1mJiYodSo9dSp1KnUqdSksdGhpcy5yYXRpbz0xPT09bT8xLXU6Mj09PW0/dTouNT50L2w/dS8yOjEtdS8yfWVsc2UgdGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKHQvbCk7aWYodGhpcy5fdGltZSE9PW98fGkpe2lmKCF0aGlzLl9pbml0dGVkKXtpZih0aGlzLl9pbml0KCksIXRoaXMuX2luaXR0ZWR8fHRoaXMuX2djKXJldHVybjtpZighaSYmdGhpcy5fZmlyc3RQVCYmKHRoaXMudmFycy5sYXp5IT09ITEmJnRoaXMuX2R1cmF0aW9ufHx0aGlzLnZhcnMubGF6eSYmIXRoaXMuX2R1cmF0aW9uKSlyZXR1cm4gdGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9byx0aGlzLl9yYXdQcmV2VGltZT1fLHoucHVzaCh0aGlzKSx0aGlzLl9sYXp5PXQsdm9pZCAwO3RoaXMuX3RpbWUmJiFzP3RoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbyh0aGlzLl90aW1lL2wpOnMmJnRoaXMuX2Vhc2UuX2NhbGNFbmQmJih0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8oMD09PXRoaXMuX3RpbWU/MDoxKSl9Zm9yKHRoaXMuX2xhenkhPT0hMSYmKHRoaXMuX2xhenk9ITEpLHRoaXMuX2FjdGl2ZXx8IXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZSE9PW8mJnQ+PTAmJih0aGlzLl9hY3RpdmU9ITApLDA9PT1vJiYodGhpcy5fc3RhcnRBdCYmKHQ+PTA/dGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpOm58fChuPVwiX2R1bW15R1NcIikpLHRoaXMudmFycy5vblN0YXJ0JiYoMCE9PXRoaXMuX3RpbWV8fDA9PT1sKSYmKGV8fHRoaXMudmFycy5vblN0YXJ0LmFwcGx5KHRoaXMudmFycy5vblN0YXJ0U2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uU3RhcnRQYXJhbXN8fGcpKSkscj10aGlzLl9maXJzdFBUO3I7KXIuZj9yLnRbci5wXShyLmMqdGhpcy5yYXRpbytyLnMpOnIudFtyLnBdPXIuYyp0aGlzLnJhdGlvK3IucyxyPXIuX25leHQ7dGhpcy5fb25VcGRhdGUmJigwPnQmJnRoaXMuX3N0YXJ0QXQmJnRoaXMuX3N0YXJ0VGltZSYmdGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpLGV8fCh0aGlzLl90aW1lIT09b3x8cykmJnRoaXMuX29uVXBkYXRlLmFwcGx5KHRoaXMudmFycy5vblVwZGF0ZVNjb3BlfHx0aGlzLHRoaXMudmFycy5vblVwZGF0ZVBhcmFtc3x8ZykpLG4mJih0aGlzLl9nY3x8KDA+dCYmdGhpcy5fc3RhcnRBdCYmIXRoaXMuX29uVXBkYXRlJiZ0aGlzLl9zdGFydFRpbWUmJnRoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKSxzJiYodGhpcy5fdGltZWxpbmUuYXV0b1JlbW92ZUNoaWxkcmVuJiZ0aGlzLl9lbmFibGVkKCExLCExKSx0aGlzLl9hY3RpdmU9ITEpLCFlJiZ0aGlzLnZhcnNbbl0mJnRoaXMudmFyc1tuXS5hcHBseSh0aGlzLnZhcnNbbitcIlNjb3BlXCJdfHx0aGlzLHRoaXMudmFyc1tuK1wiUGFyYW1zXCJdfHxnKSwwPT09bCYmdGhpcy5fcmF3UHJldlRpbWU9PT1oJiZhIT09aCYmKHRoaXMuX3Jhd1ByZXZUaW1lPTApKSl9fSxuLl9raWxsPWZ1bmN0aW9uKHQsZSl7aWYoXCJhbGxcIj09PXQmJih0PW51bGwpLG51bGw9PXQmJihudWxsPT1lfHxlPT09dGhpcy50YXJnZXQpKXJldHVybiB0aGlzLl9sYXp5PSExLHRoaXMuX2VuYWJsZWQoITEsITEpO2U9XCJzdHJpbmdcIiE9dHlwZW9mIGU/ZXx8dGhpcy5fdGFyZ2V0c3x8dGhpcy50YXJnZXQ6RC5zZWxlY3RvcihlKXx8ZTt2YXIgaSxzLG4scixhLG8sbCxoO2lmKChtKGUpfHxJKGUpKSYmXCJudW1iZXJcIiE9dHlwZW9mIGVbMF0pZm9yKGk9ZS5sZW5ndGg7LS1pPi0xOyl0aGlzLl9raWxsKHQsZVtpXSkmJihvPSEwKTtlbHNle2lmKHRoaXMuX3RhcmdldHMpe2ZvcihpPXRoaXMuX3RhcmdldHMubGVuZ3RoOy0taT4tMTspaWYoZT09PXRoaXMuX3RhcmdldHNbaV0pe2E9dGhpcy5fcHJvcExvb2t1cFtpXXx8e30sdGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz10aGlzLl9vdmVyd3JpdHRlblByb3BzfHxbXSxzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHNbaV09dD90aGlzLl9vdmVyd3JpdHRlblByb3BzW2ldfHx7fTpcImFsbFwiO2JyZWFrfX1lbHNle2lmKGUhPT10aGlzLnRhcmdldClyZXR1cm4hMTthPXRoaXMuX3Byb3BMb29rdXAscz10aGlzLl9vdmVyd3JpdHRlblByb3BzPXQ/dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc3x8e306XCJhbGxcIn1pZihhKXtsPXR8fGEsaD10IT09cyYmXCJhbGxcIiE9PXMmJnQhPT1hJiYoXCJvYmplY3RcIiE9dHlwZW9mIHR8fCF0Ll90ZW1wS2lsbCk7Zm9yKG4gaW4gbCkocj1hW25dKSYmKHIucGcmJnIudC5fa2lsbChsKSYmKG89ITApLHIucGcmJjAhPT1yLnQuX292ZXJ3cml0ZVByb3BzLmxlbmd0aHx8KHIuX3ByZXY/ci5fcHJldi5fbmV4dD1yLl9uZXh0OnI9PT10aGlzLl9maXJzdFBUJiYodGhpcy5fZmlyc3RQVD1yLl9uZXh0KSxyLl9uZXh0JiYoci5fbmV4dC5fcHJldj1yLl9wcmV2KSxyLl9uZXh0PXIuX3ByZXY9bnVsbCksZGVsZXRlIGFbbl0pLGgmJihzW25dPTEpOyF0aGlzLl9maXJzdFBUJiZ0aGlzLl9pbml0dGVkJiZ0aGlzLl9lbmFibGVkKCExLCExKX19cmV0dXJuIG99LG4uaW52YWxpZGF0ZT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkJiZELl9vblBsdWdpbkV2ZW50KFwiX29uRGlzYWJsZVwiLHRoaXMpLHRoaXMuX2ZpcnN0UFQ9bnVsbCx0aGlzLl9vdmVyd3JpdHRlblByb3BzPW51bGwsdGhpcy5fb25VcGRhdGU9bnVsbCx0aGlzLl9zdGFydEF0PW51bGwsdGhpcy5faW5pdHRlZD10aGlzLl9hY3RpdmU9dGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD10aGlzLl9sYXp5PSExLHRoaXMuX3Byb3BMb29rdXA9dGhpcy5fdGFyZ2V0cz97fTpbXSx0aGlzfSxuLl9lbmFibGVkPWZ1bmN0aW9uKHQsZSl7aWYoYXx8ci53YWtlKCksdCYmdGhpcy5fZ2Mpe3ZhciBpLHM9dGhpcy5fdGFyZ2V0cztpZihzKWZvcihpPXMubGVuZ3RoOy0taT4tMTspdGhpcy5fc2libGluZ3NbaV09TShzW2ldLHRoaXMsITApO2Vsc2UgdGhpcy5fc2libGluZ3M9TSh0aGlzLnRhcmdldCx0aGlzLCEwKX1yZXR1cm4geC5wcm90b3R5cGUuX2VuYWJsZWQuY2FsbCh0aGlzLHQsZSksdGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZCYmdGhpcy5fZmlyc3RQVD9ELl9vblBsdWdpbkV2ZW50KHQ/XCJfb25FbmFibGVcIjpcIl9vbkRpc2FibGVcIix0aGlzKTohMX0sRC50bz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIG5ldyBEKHQsZSxpKX0sRC5mcm9tPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gaS5ydW5CYWNrd2FyZHM9ITAsaS5pbW1lZGlhdGVSZW5kZXI9MCE9aS5pbW1lZGlhdGVSZW5kZXIsbmV3IEQodCxlLGkpfSxELmZyb21Ubz1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gcy5zdGFydEF0PWkscy5pbW1lZGlhdGVSZW5kZXI9MCE9cy5pbW1lZGlhdGVSZW5kZXImJjAhPWkuaW1tZWRpYXRlUmVuZGVyLG5ldyBEKHQsZSxzKX0sRC5kZWxheWVkQ2FsbD1mdW5jdGlvbih0LGUsaSxzLG4pe3JldHVybiBuZXcgRChlLDAse2RlbGF5OnQsb25Db21wbGV0ZTplLG9uQ29tcGxldGVQYXJhbXM6aSxvbkNvbXBsZXRlU2NvcGU6cyxvblJldmVyc2VDb21wbGV0ZTplLG9uUmV2ZXJzZUNvbXBsZXRlUGFyYW1zOmksb25SZXZlcnNlQ29tcGxldGVTY29wZTpzLGltbWVkaWF0ZVJlbmRlcjohMSx1c2VGcmFtZXM6bixvdmVyd3JpdGU6MH0pfSxELnNldD1mdW5jdGlvbih0LGUpe3JldHVybiBuZXcgRCh0LDAsZSl9LEQuZ2V0VHdlZW5zT2Y9ZnVuY3Rpb24odCxlKXtpZihudWxsPT10KXJldHVybltdO3Q9XCJzdHJpbmdcIiE9dHlwZW9mIHQ/dDpELnNlbGVjdG9yKHQpfHx0O3ZhciBpLHMsbixyO2lmKChtKHQpfHxJKHQpKSYmXCJudW1iZXJcIiE9dHlwZW9mIHRbMF0pe2ZvcihpPXQubGVuZ3RoLHM9W107LS1pPi0xOylzPXMuY29uY2F0KEQuZ2V0VHdlZW5zT2YodFtpXSxlKSk7Zm9yKGk9cy5sZW5ndGg7LS1pPi0xOylmb3Iocj1zW2ldLG49aTstLW4+LTE7KXI9PT1zW25dJiZzLnNwbGljZShpLDEpfWVsc2UgZm9yKHM9TSh0KS5jb25jYXQoKSxpPXMubGVuZ3RoOy0taT4tMTspKHNbaV0uX2djfHxlJiYhc1tpXS5pc0FjdGl2ZSgpKSYmcy5zcGxpY2UoaSwxKTtyZXR1cm4gc30sRC5raWxsVHdlZW5zT2Y9RC5raWxsRGVsYXllZENhbGxzVG89ZnVuY3Rpb24odCxlLGkpe1wib2JqZWN0XCI9PXR5cGVvZiBlJiYoaT1lLGU9ITEpO2Zvcih2YXIgcz1ELmdldFR3ZWVuc09mKHQsZSksbj1zLmxlbmd0aDstLW4+LTE7KXNbbl0uX2tpbGwoaSx0KX07dmFyIEg9ZChcInBsdWdpbnMuVHdlZW5QbHVnaW5cIixmdW5jdGlvbih0LGUpe3RoaXMuX292ZXJ3cml0ZVByb3BzPSh0fHxcIlwiKS5zcGxpdChcIixcIiksdGhpcy5fcHJvcE5hbWU9dGhpcy5fb3ZlcndyaXRlUHJvcHNbMF0sdGhpcy5fcHJpb3JpdHk9ZXx8MCx0aGlzLl9zdXBlcj1ILnByb3RvdHlwZX0sITApO2lmKG49SC5wcm90b3R5cGUsSC52ZXJzaW9uPVwiMS4xMC4xXCIsSC5BUEk9MixuLl9maXJzdFBUPW51bGwsbi5fYWRkVHdlZW49ZnVuY3Rpb24odCxlLGkscyxuLHIpe3ZhciBhLG87cmV0dXJuIG51bGwhPXMmJihhPVwibnVtYmVyXCI9PXR5cGVvZiBzfHxcIj1cIiE9PXMuY2hhckF0KDEpP051bWJlcihzKS1pOnBhcnNlSW50KHMuY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIocy5zdWJzdHIoMikpKT8odGhpcy5fZmlyc3RQVD1vPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6dCxwOmUsczppLGM6YSxmOlwiZnVuY3Rpb25cIj09dHlwZW9mIHRbZV0sbjpufHxlLHI6cn0sby5fbmV4dCYmKG8uX25leHQuX3ByZXY9byksbyk6dm9pZCAwfSxuLnNldFJhdGlvPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZSxpPXRoaXMuX2ZpcnN0UFQscz0xZS02O2k7KWU9aS5jKnQraS5zLGkucj9lPU1hdGgucm91bmQoZSk6cz5lJiZlPi1zJiYoZT0wKSxpLmY/aS50W2kucF0oZSk6aS50W2kucF09ZSxpPWkuX25leHR9LG4uX2tpbGw9ZnVuY3Rpb24odCl7dmFyIGUsaT10aGlzLl9vdmVyd3JpdGVQcm9wcyxzPXRoaXMuX2ZpcnN0UFQ7aWYobnVsbCE9dFt0aGlzLl9wcm9wTmFtZV0pdGhpcy5fb3ZlcndyaXRlUHJvcHM9W107ZWxzZSBmb3IoZT1pLmxlbmd0aDstLWU+LTE7KW51bGwhPXRbaVtlXV0mJmkuc3BsaWNlKGUsMSk7Zm9yKDtzOyludWxsIT10W3Mubl0mJihzLl9uZXh0JiYocy5fbmV4dC5fcHJldj1zLl9wcmV2KSxzLl9wcmV2PyhzLl9wcmV2Ll9uZXh0PXMuX25leHQscy5fcHJldj1udWxsKTp0aGlzLl9maXJzdFBUPT09cyYmKHRoaXMuX2ZpcnN0UFQ9cy5fbmV4dCkpLHM9cy5fbmV4dDtyZXR1cm4hMX0sbi5fcm91bmRQcm9wcz1mdW5jdGlvbih0LGUpe2Zvcih2YXIgaT10aGlzLl9maXJzdFBUO2k7KSh0W3RoaXMuX3Byb3BOYW1lXXx8bnVsbCE9aS5uJiZ0W2kubi5zcGxpdCh0aGlzLl9wcm9wTmFtZStcIl9cIikuam9pbihcIlwiKV0pJiYoaS5yPWUpLGk9aS5fbmV4dH0sRC5fb25QbHVnaW5FdmVudD1mdW5jdGlvbih0LGUpe3ZhciBpLHMsbixyLGEsbz1lLl9maXJzdFBUO2lmKFwiX29uSW5pdEFsbFByb3BzXCI9PT10KXtmb3IoO287KXtmb3IoYT1vLl9uZXh0LHM9bjtzJiZzLnByPm8ucHI7KXM9cy5fbmV4dDsoby5fcHJldj1zP3MuX3ByZXY6cik/by5fcHJldi5fbmV4dD1vOm49bywoby5fbmV4dD1zKT9zLl9wcmV2PW86cj1vLG89YX1vPWUuX2ZpcnN0UFQ9bn1mb3IoO287KW8ucGcmJlwiZnVuY3Rpb25cIj09dHlwZW9mIG8udFt0XSYmby50W3RdKCkmJihpPSEwKSxvPW8uX25leHQ7cmV0dXJuIGl9LEguYWN0aXZhdGU9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQubGVuZ3RoOy0tZT4tMTspdFtlXS5BUEk9PT1ILkFQSSYmKExbKG5ldyB0W2VdKS5fcHJvcE5hbWVdPXRbZV0pO3JldHVybiEwfSxjLnBsdWdpbj1mdW5jdGlvbih0KXtpZighKHQmJnQucHJvcE5hbWUmJnQuaW5pdCYmdC5BUEkpKXRocm93XCJpbGxlZ2FsIHBsdWdpbiBkZWZpbml0aW9uLlwiO3ZhciBlLGk9dC5wcm9wTmFtZSxzPXQucHJpb3JpdHl8fDAsbj10Lm92ZXJ3cml0ZVByb3BzLHI9e2luaXQ6XCJfb25Jbml0VHdlZW5cIixzZXQ6XCJzZXRSYXRpb1wiLGtpbGw6XCJfa2lsbFwiLHJvdW5kOlwiX3JvdW5kUHJvcHNcIixpbml0QWxsOlwiX29uSW5pdEFsbFByb3BzXCJ9LGE9ZChcInBsdWdpbnMuXCIraS5jaGFyQXQoMCkudG9VcHBlckNhc2UoKStpLnN1YnN0cigxKStcIlBsdWdpblwiLGZ1bmN0aW9uKCl7SC5jYWxsKHRoaXMsaSxzKSx0aGlzLl9vdmVyd3JpdGVQcm9wcz1ufHxbXX0sdC5nbG9iYWw9PT0hMCksbz1hLnByb3RvdHlwZT1uZXcgSChpKTtvLmNvbnN0cnVjdG9yPWEsYS5BUEk9dC5BUEk7Zm9yKGUgaW4gcilcImZ1bmN0aW9uXCI9PXR5cGVvZiB0W2VdJiYob1tyW2VdXT10W2VdKTtyZXR1cm4gYS52ZXJzaW9uPXQudmVyc2lvbixILmFjdGl2YXRlKFthXSksYX0saT10Ll9nc1F1ZXVlKXtmb3Iocz0wO2kubGVuZ3RoPnM7cysrKWlbc10oKTtmb3IobiBpbiBmKWZbbl0uZnVuY3x8dC5jb25zb2xlLmxvZyhcIkdTQVAgZW5jb3VudGVyZWQgbWlzc2luZyBkZXBlbmRlbmN5OiBjb20uZ3JlZW5zb2NrLlwiK24pfWE9ITF9fSkod2luZG93KTsiLCIvKiFcclxuICogVkVSU0lPTjogYmV0YSAxLjkuM1xyXG4gKiBEQVRFOiAyMDEzLTA0LTAyXHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKiovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcImVhc2luZy5CYWNrXCIsW1wiZWFzaW5nLkVhc2VcIl0sZnVuY3Rpb24odCl7dmFyIGUsaSxzLHI9d2luZG93LkdyZWVuU29ja0dsb2JhbHN8fHdpbmRvdyxuPXIuY29tLmdyZWVuc29jayxhPTIqTWF0aC5QSSxvPU1hdGguUEkvMixoPW4uX2NsYXNzLGw9ZnVuY3Rpb24oZSxpKXt2YXIgcz1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24oKXt9LCEwKSxyPXMucHJvdG90eXBlPW5ldyB0O3JldHVybiByLmNvbnN0cnVjdG9yPXMsci5nZXRSYXRpbz1pLHN9LF89dC5yZWdpc3Rlcnx8ZnVuY3Rpb24oKXt9LHU9ZnVuY3Rpb24odCxlLGkscyl7dmFyIHI9aChcImVhc2luZy5cIit0LHtlYXNlT3V0Om5ldyBlLGVhc2VJbjpuZXcgaSxlYXNlSW5PdXQ6bmV3IHN9LCEwKTtyZXR1cm4gXyhyLHQpLHJ9LGM9ZnVuY3Rpb24odCxlLGkpe3RoaXMudD10LHRoaXMudj1lLGkmJih0aGlzLm5leHQ9aSxpLnByZXY9dGhpcyx0aGlzLmM9aS52LWUsdGhpcy5nYXA9aS50LXQpfSxmPWZ1bmN0aW9uKGUsaSl7dmFyIHM9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKHQpe3RoaXMuX3AxPXR8fDA9PT10P3Q6MS43MDE1OCx0aGlzLl9wMj0xLjUyNSp0aGlzLl9wMX0sITApLHI9cy5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIHIuY29uc3RydWN0b3I9cyxyLmdldFJhdGlvPWksci5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBzKHQpfSxzfSxwPXUoXCJCYWNrXCIsZihcIkJhY2tPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4odC09MSkqdCooKHRoaXMuX3AxKzEpKnQrdGhpcy5fcDEpKzF9KSxmKFwiQmFja0luXCIsZnVuY3Rpb24odCl7cmV0dXJuIHQqdCooKHRoaXMuX3AxKzEpKnQtdGhpcy5fcDEpfSksZihcIkJhY2tJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8uNSp0KnQqKCh0aGlzLl9wMisxKSp0LXRoaXMuX3AyKTouNSooKHQtPTIpKnQqKCh0aGlzLl9wMisxKSp0K3RoaXMuX3AyKSsyKX0pKSxtPWgoXCJlYXNpbmcuU2xvd01vXCIsZnVuY3Rpb24odCxlLGkpe2U9ZXx8MD09PWU/ZTouNyxudWxsPT10P3Q9Ljc6dD4xJiYodD0xKSx0aGlzLl9wPTEhPT10P2U6MCx0aGlzLl9wMT0oMS10KS8yLHRoaXMuX3AyPXQsdGhpcy5fcDM9dGhpcy5fcDErdGhpcy5fcDIsdGhpcy5fY2FsY0VuZD1pPT09ITB9LCEwKSxkPW0ucHJvdG90eXBlPW5ldyB0O3JldHVybiBkLmNvbnN0cnVjdG9yPW0sZC5nZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZT10KyguNS10KSp0aGlzLl9wO3JldHVybiB0aGlzLl9wMT50P3RoaXMuX2NhbGNFbmQ/MS0odD0xLXQvdGhpcy5fcDEpKnQ6ZS0odD0xLXQvdGhpcy5fcDEpKnQqdCp0KmU6dD50aGlzLl9wMz90aGlzLl9jYWxjRW5kPzEtKHQ9KHQtdGhpcy5fcDMpL3RoaXMuX3AxKSp0OmUrKHQtZSkqKHQ9KHQtdGhpcy5fcDMpL3RoaXMuX3AxKSp0KnQqdDp0aGlzLl9jYWxjRW5kPzE6ZX0sbS5lYXNlPW5ldyBtKC43LC43KSxkLmNvbmZpZz1tLmNvbmZpZz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIG5ldyBtKHQsZSxpKX0sZT1oKFwiZWFzaW5nLlN0ZXBwZWRFYXNlXCIsZnVuY3Rpb24odCl7dD10fHwxLHRoaXMuX3AxPTEvdCx0aGlzLl9wMj10KzF9LCEwKSxkPWUucHJvdG90eXBlPW5ldyB0LGQuY29uc3RydWN0b3I9ZSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3JldHVybiAwPnQ/dD0wOnQ+PTEmJih0PS45OTk5OTk5OTkpLCh0aGlzLl9wMip0Pj4wKSp0aGlzLl9wMX0sZC5jb25maWc9ZS5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBlKHQpfSxpPWgoXCJlYXNpbmcuUm91Z2hFYXNlXCIsZnVuY3Rpb24oZSl7ZT1lfHx7fTtmb3IodmFyIGkscyxyLG4sYSxvLGg9ZS50YXBlcnx8XCJub25lXCIsbD1bXSxfPTAsdT0wfChlLnBvaW50c3x8MjApLGY9dSxwPWUucmFuZG9taXplIT09ITEsbT1lLmNsYW1wPT09ITAsZD1lLnRlbXBsYXRlIGluc3RhbmNlb2YgdD9lLnRlbXBsYXRlOm51bGwsZz1cIm51bWJlclwiPT10eXBlb2YgZS5zdHJlbmd0aD8uNCplLnN0cmVuZ3RoOi40Oy0tZj4tMTspaT1wP01hdGgucmFuZG9tKCk6MS91KmYscz1kP2QuZ2V0UmF0aW8oaSk6aSxcIm5vbmVcIj09PWg/cj1nOlwib3V0XCI9PT1oPyhuPTEtaSxyPW4qbipnKTpcImluXCI9PT1oP3I9aSppKmc6LjU+aT8obj0yKmkscj0uNSpuKm4qZyk6KG49MiooMS1pKSxyPS41Km4qbipnKSxwP3MrPU1hdGgucmFuZG9tKCkqci0uNSpyOmYlMj9zKz0uNSpyOnMtPS41KnIsbSYmKHM+MT9zPTE6MD5zJiYocz0wKSksbFtfKytdPXt4OmkseTpzfTtmb3IobC5zb3J0KGZ1bmN0aW9uKHQsZSl7cmV0dXJuIHQueC1lLnh9KSxvPW5ldyBjKDEsMSxudWxsKSxmPXU7LS1mPi0xOylhPWxbZl0sbz1uZXcgYyhhLngsYS55LG8pO3RoaXMuX3ByZXY9bmV3IGMoMCwwLDAhPT1vLnQ/bzpvLm5leHQpfSwhMCksZD1pLnByb3RvdHlwZT1uZXcgdCxkLmNvbnN0cnVjdG9yPWksZC5nZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZT10aGlzLl9wcmV2O2lmKHQ+ZS50KXtmb3IoO2UubmV4dCYmdD49ZS50OyllPWUubmV4dDtlPWUucHJldn1lbHNlIGZvcig7ZS5wcmV2JiZlLnQ+PXQ7KWU9ZS5wcmV2O3JldHVybiB0aGlzLl9wcmV2PWUsZS52Kyh0LWUudCkvZS5nYXAqZS5jfSxkLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IGkodCl9LGkuZWFzZT1uZXcgaSx1KFwiQm91bmNlXCIsbChcIkJvdW5jZU91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLzIuNzU+dD83LjU2MjUqdCp0OjIvMi43NT50PzcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1OjIuNS8yLjc1PnQ/Ny41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzU6Ny41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzV9KSxsKFwiQm91bmNlSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gMS8yLjc1Pih0PTEtdCk/MS03LjU2MjUqdCp0OjIvMi43NT50PzEtKDcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1KToyLjUvMi43NT50PzEtKDcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1KToxLSg3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NSl9KSxsKFwiQm91bmNlSW5PdXRcIixmdW5jdGlvbih0KXt2YXIgZT0uNT50O3JldHVybiB0PWU/MS0yKnQ6Mip0LTEsdD0xLzIuNzU+dD83LjU2MjUqdCp0OjIvMi43NT50PzcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1OjIuNS8yLjc1PnQ/Ny41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzU6Ny41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzUsZT8uNSooMS10KTouNSp0Ky41fSkpLHUoXCJDaXJjXCIsbChcIkNpcmNPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5zcXJ0KDEtKHQtPTEpKnQpfSksbChcIkNpcmNJblwiLGZ1bmN0aW9uKHQpe3JldHVybi0oTWF0aC5zcXJ0KDEtdCp0KS0xKX0pLGwoXCJDaXJjSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LS41KihNYXRoLnNxcnQoMS10KnQpLTEpOi41KihNYXRoLnNxcnQoMS0odC09MikqdCkrMSl9KSkscz1mdW5jdGlvbihlLGkscyl7dmFyIHI9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKHQsZSl7dGhpcy5fcDE9dHx8MSx0aGlzLl9wMj1lfHxzLHRoaXMuX3AzPXRoaXMuX3AyL2EqKE1hdGguYXNpbigxL3RoaXMuX3AxKXx8MCl9LCEwKSxuPXIucHJvdG90eXBlPW5ldyB0O3JldHVybiBuLmNvbnN0cnVjdG9yPXIsbi5nZXRSYXRpbz1pLG4uY29uZmlnPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG5ldyByKHQsZSl9LHJ9LHUoXCJFbGFzdGljXCIscyhcIkVsYXN0aWNPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gdGhpcy5fcDEqTWF0aC5wb3coMiwtMTAqdCkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKzF9LC4zKSxzKFwiRWxhc3RpY0luXCIsZnVuY3Rpb24odCl7cmV0dXJuLSh0aGlzLl9wMSpNYXRoLnBvdygyLDEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKX0sLjMpLHMoXCJFbGFzdGljSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LS41KnRoaXMuX3AxKk1hdGgucG93KDIsMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMik6LjUqdGhpcy5fcDEqTWF0aC5wb3coMiwtMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikrMX0sLjQ1KSksdShcIkV4cG9cIixsKFwiRXhwb091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLU1hdGgucG93KDIsLTEwKnQpfSksbChcIkV4cG9JblwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnBvdygyLDEwKih0LTEpKS0uMDAxfSksbChcIkV4cG9Jbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8uNSpNYXRoLnBvdygyLDEwKih0LTEpKTouNSooMi1NYXRoLnBvdygyLC0xMCoodC0xKSkpfSkpLHUoXCJTaW5lXCIsbChcIlNpbmVPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5zaW4odCpvKX0pLGwoXCJTaW5lSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tTWF0aC5jb3ModCpvKSsxfSksbChcIlNpbmVJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybi0uNSooTWF0aC5jb3MoTWF0aC5QSSp0KS0xKX0pKSxoKFwiZWFzaW5nLkVhc2VMb29rdXBcIix7ZmluZDpmdW5jdGlvbihlKXtyZXR1cm4gdC5tYXBbZV19fSwhMCksXyhyLlNsb3dNbyxcIlNsb3dNb1wiLFwiZWFzZSxcIiksXyhpLFwiUm91Z2hFYXNlXCIsXCJlYXNlLFwiKSxfKGUsXCJTdGVwcGVkRWFzZVwiLFwiZWFzZSxcIikscH0sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwicGx1Z2lucy5DU1NQbHVnaW5cIixbXCJwbHVnaW5zLlR3ZWVuUGx1Z2luXCIsXCJUd2VlbkxpdGVcIl0sZnVuY3Rpb24odCxlKXt2YXIgaSxyLHMsbixhPWZ1bmN0aW9uKCl7dC5jYWxsKHRoaXMsXCJjc3NcIiksdGhpcy5fb3ZlcndyaXRlUHJvcHMubGVuZ3RoPTAsdGhpcy5zZXRSYXRpbz1hLnByb3RvdHlwZS5zZXRSYXRpb30sbz17fSxsPWEucHJvdG90eXBlPW5ldyB0KFwiY3NzXCIpO2wuY29uc3RydWN0b3I9YSxhLnZlcnNpb249XCIxLjEyLjFcIixhLkFQST0yLGEuZGVmYXVsdFRyYW5zZm9ybVBlcnNwZWN0aXZlPTAsYS5kZWZhdWx0U2tld1R5cGU9XCJjb21wZW5zYXRlZFwiLGw9XCJweFwiLGEuc3VmZml4TWFwPXt0b3A6bCxyaWdodDpsLGJvdHRvbTpsLGxlZnQ6bCx3aWR0aDpsLGhlaWdodDpsLGZvbnRTaXplOmwscGFkZGluZzpsLG1hcmdpbjpsLHBlcnNwZWN0aXZlOmwsbGluZUhlaWdodDpcIlwifTt2YXIgaCx1LGYsXyxwLGMsZD0vKD86XFxkfFxcLVxcZHxcXC5cXGR8XFwtXFwuXFxkKSsvZyxtPS8oPzpcXGR8XFwtXFxkfFxcLlxcZHxcXC1cXC5cXGR8XFwrPVxcZHxcXC09XFxkfFxcKz0uXFxkfFxcLT1cXC5cXGQpKy9nLGc9Lyg/OlxcKz18XFwtPXxcXC18XFxiKVtcXGRcXC1cXC5dK1thLXpBLVowLTldKig/OiV8XFxiKS9naSx2PS9bXlxcZFxcLVxcLl0vZyx5PS8oPzpcXGR8XFwtfFxcK3w9fCN8XFwuKSovZyxUPS9vcGFjaXR5ICo9ICooW14pXSopL2ksdz0vb3BhY2l0eTooW147XSopL2kseD0vYWxwaGFcXChvcGFjaXR5ICo9Lis/XFwpL2ksYj0vXihyZ2J8aHNsKS8sUD0vKFtBLVpdKS9nLFM9Ly0oW2Etel0pL2dpLEM9LyheKD86dXJsXFwoXFxcInx1cmxcXCgpKXwoPzooXFxcIlxcKSkkfFxcKSQpL2dpLFI9ZnVuY3Rpb24odCxlKXtyZXR1cm4gZS50b1VwcGVyQ2FzZSgpfSxrPS8oPzpMZWZ0fFJpZ2h0fFdpZHRoKS9pLEE9LyhNMTF8TTEyfE0yMXxNMjIpPVtcXGRcXC1cXC5lXSsvZ2ksTz0vcHJvZ2lkXFw6RFhJbWFnZVRyYW5zZm9ybVxcLk1pY3Jvc29mdFxcLk1hdHJpeFxcKC4rP1xcKS9pLEQ9LywoPz1bXlxcKV0qKD86XFwofCQpKS9naSxNPU1hdGguUEkvMTgwLEw9MTgwL01hdGguUEksTj17fSxYPWRvY3VtZW50LHo9WC5jcmVhdGVFbGVtZW50KFwiZGl2XCIpLEk9WC5jcmVhdGVFbGVtZW50KFwiaW1nXCIpLEU9YS5faW50ZXJuYWxzPXtfc3BlY2lhbFByb3BzOm99LEY9bmF2aWdhdG9yLnVzZXJBZ2VudCxZPWZ1bmN0aW9uKCl7dmFyIHQsZT1GLmluZGV4T2YoXCJBbmRyb2lkXCIpLGk9WC5jcmVhdGVFbGVtZW50KFwiZGl2XCIpO3JldHVybiBmPS0xIT09Ri5pbmRleE9mKFwiU2FmYXJpXCIpJiYtMT09PUYuaW5kZXhPZihcIkNocm9tZVwiKSYmKC0xPT09ZXx8TnVtYmVyKEYuc3Vic3RyKGUrOCwxKSk+MykscD1mJiY2Pk51bWJlcihGLnN1YnN0cihGLmluZGV4T2YoXCJWZXJzaW9uL1wiKSs4LDEpKSxfPS0xIT09Ri5pbmRleE9mKFwiRmlyZWZveFwiKSwvTVNJRSAoWzAtOV17MSx9W1xcLjAtOV17MCx9KS8uZXhlYyhGKSYmKGM9cGFyc2VGbG9hdChSZWdFeHAuJDEpKSxpLmlubmVySFRNTD1cIjxhIHN0eWxlPSd0b3A6MXB4O29wYWNpdHk6LjU1Oyc+YTwvYT5cIix0PWkuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJhXCIpWzBdLHQ/L14wLjU1Ly50ZXN0KHQuc3R5bGUub3BhY2l0eSk6ITF9KCksQj1mdW5jdGlvbih0KXtyZXR1cm4gVC50ZXN0KFwic3RyaW5nXCI9PXR5cGVvZiB0P3Q6KHQuY3VycmVudFN0eWxlP3QuY3VycmVudFN0eWxlLmZpbHRlcjp0LnN0eWxlLmZpbHRlcil8fFwiXCIpP3BhcnNlRmxvYXQoUmVnRXhwLiQxKS8xMDA6MX0sVT1mdW5jdGlvbih0KXt3aW5kb3cuY29uc29sZSYmY29uc29sZS5sb2codCl9LFc9XCJcIixqPVwiXCIsVj1mdW5jdGlvbih0LGUpe2U9ZXx8ejt2YXIgaSxyLHM9ZS5zdHlsZTtpZih2b2lkIDAhPT1zW3RdKXJldHVybiB0O2Zvcih0PXQuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkrdC5zdWJzdHIoMSksaT1bXCJPXCIsXCJNb3pcIixcIm1zXCIsXCJNc1wiLFwiV2Via2l0XCJdLHI9NTstLXI+LTEmJnZvaWQgMD09PXNbaVtyXSt0XTspO3JldHVybiByPj0wPyhqPTM9PT1yP1wibXNcIjppW3JdLFc9XCItXCIrai50b0xvd2VyQ2FzZSgpK1wiLVwiLGordCk6bnVsbH0sSD1YLmRlZmF1bHRWaWV3P1guZGVmYXVsdFZpZXcuZ2V0Q29tcHV0ZWRTdHlsZTpmdW5jdGlvbigpe30scT1hLmdldFN0eWxlPWZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG47cmV0dXJuIFl8fFwib3BhY2l0eVwiIT09ZT8oIXImJnQuc3R5bGVbZV0/bj10LnN0eWxlW2VdOihpPWl8fEgodCkpP249aVtlXXx8aS5nZXRQcm9wZXJ0eVZhbHVlKGUpfHxpLmdldFByb3BlcnR5VmFsdWUoZS5yZXBsYWNlKFAsXCItJDFcIikudG9Mb3dlckNhc2UoKSk6dC5jdXJyZW50U3R5bGUmJihuPXQuY3VycmVudFN0eWxlW2VdKSxudWxsPT1zfHxuJiZcIm5vbmVcIiE9PW4mJlwiYXV0b1wiIT09biYmXCJhdXRvIGF1dG9cIiE9PW4/bjpzKTpCKHQpfSxRPUUuY29udmVydFRvUGl4ZWxzPWZ1bmN0aW9uKHQsaSxyLHMsbil7aWYoXCJweFwiPT09c3x8IXMpcmV0dXJuIHI7aWYoXCJhdXRvXCI9PT1zfHwhcilyZXR1cm4gMDt2YXIgbyxsLGgsdT1rLnRlc3QoaSksZj10LF89ei5zdHlsZSxwPTA+cjtpZihwJiYocj0tciksXCIlXCI9PT1zJiYtMSE9PWkuaW5kZXhPZihcImJvcmRlclwiKSlvPXIvMTAwKih1P3QuY2xpZW50V2lkdGg6dC5jbGllbnRIZWlnaHQpO2Vsc2V7aWYoXy5jc3NUZXh0PVwiYm9yZGVyOjAgc29saWQgcmVkO3Bvc2l0aW9uOlwiK3EodCxcInBvc2l0aW9uXCIpK1wiO2xpbmUtaGVpZ2h0OjA7XCIsXCIlXCIhPT1zJiZmLmFwcGVuZENoaWxkKV9bdT9cImJvcmRlckxlZnRXaWR0aFwiOlwiYm9yZGVyVG9wV2lkdGhcIl09citzO2Vsc2V7aWYoZj10LnBhcmVudE5vZGV8fFguYm9keSxsPWYuX2dzQ2FjaGUsaD1lLnRpY2tlci5mcmFtZSxsJiZ1JiZsLnRpbWU9PT1oKXJldHVybiBsLndpZHRoKnIvMTAwO19bdT9cIndpZHRoXCI6XCJoZWlnaHRcIl09citzfWYuYXBwZW5kQ2hpbGQoeiksbz1wYXJzZUZsb2F0KHpbdT9cIm9mZnNldFdpZHRoXCI6XCJvZmZzZXRIZWlnaHRcIl0pLGYucmVtb3ZlQ2hpbGQoeiksdSYmXCIlXCI9PT1zJiZhLmNhY2hlV2lkdGhzIT09ITEmJihsPWYuX2dzQ2FjaGU9Zi5fZ3NDYWNoZXx8e30sbC50aW1lPWgsbC53aWR0aD0xMDAqKG8vcikpLDAhPT1vfHxufHwobz1RKHQsaSxyLHMsITApKX1yZXR1cm4gcD8tbzpvfSxaPUUuY2FsY3VsYXRlT2Zmc2V0PWZ1bmN0aW9uKHQsZSxpKXtpZihcImFic29sdXRlXCIhPT1xKHQsXCJwb3NpdGlvblwiLGkpKXJldHVybiAwO3ZhciByPVwibGVmdFwiPT09ZT9cIkxlZnRcIjpcIlRvcFwiLHM9cSh0LFwibWFyZ2luXCIrcixpKTtyZXR1cm4gdFtcIm9mZnNldFwiK3JdLShRKHQsZSxwYXJzZUZsb2F0KHMpLHMucmVwbGFjZSh5LFwiXCIpKXx8MCl9LCQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxyLHM9e307aWYoZT1lfHxIKHQsbnVsbCkpaWYoaT1lLmxlbmd0aClmb3IoOy0taT4tMTspc1tlW2ldLnJlcGxhY2UoUyxSKV09ZS5nZXRQcm9wZXJ0eVZhbHVlKGVbaV0pO2Vsc2UgZm9yKGkgaW4gZSlzW2ldPWVbaV07ZWxzZSBpZihlPXQuY3VycmVudFN0eWxlfHx0LnN0eWxlKWZvcihpIGluIGUpXCJzdHJpbmdcIj09dHlwZW9mIGkmJnZvaWQgMD09PXNbaV0mJihzW2kucmVwbGFjZShTLFIpXT1lW2ldKTtyZXR1cm4gWXx8KHMub3BhY2l0eT1CKHQpKSxyPVBlKHQsZSwhMSkscy5yb3RhdGlvbj1yLnJvdGF0aW9uLHMuc2tld1g9ci5za2V3WCxzLnNjYWxlWD1yLnNjYWxlWCxzLnNjYWxlWT1yLnNjYWxlWSxzLng9ci54LHMueT1yLnkseGUmJihzLno9ci56LHMucm90YXRpb25YPXIucm90YXRpb25YLHMucm90YXRpb25ZPXIucm90YXRpb25ZLHMuc2NhbGVaPXIuc2NhbGVaKSxzLmZpbHRlcnMmJmRlbGV0ZSBzLmZpbHRlcnMsc30sRz1mdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuLGEsbyxsPXt9LGg9dC5zdHlsZTtmb3IoYSBpbiBpKVwiY3NzVGV4dFwiIT09YSYmXCJsZW5ndGhcIiE9PWEmJmlzTmFOKGEpJiYoZVthXSE9PShuPWlbYV0pfHxzJiZzW2FdKSYmLTE9PT1hLmluZGV4T2YoXCJPcmlnaW5cIikmJihcIm51bWJlclwiPT10eXBlb2Ygbnx8XCJzdHJpbmdcIj09dHlwZW9mIG4pJiYobFthXT1cImF1dG9cIiE9PW58fFwibGVmdFwiIT09YSYmXCJ0b3BcIiE9PWE/XCJcIiE9PW4mJlwiYXV0b1wiIT09biYmXCJub25lXCIhPT1ufHxcInN0cmluZ1wiIT10eXBlb2YgZVthXXx8XCJcIj09PWVbYV0ucmVwbGFjZSh2LFwiXCIpP246MDpaKHQsYSksdm9pZCAwIT09aFthXSYmKG89bmV3IGZlKGgsYSxoW2FdLG8pKSk7aWYocilmb3IoYSBpbiByKVwiY2xhc3NOYW1lXCIhPT1hJiYobFthXT1yW2FdKTtyZXR1cm57ZGlmczpsLGZpcnN0TVBUOm99fSxLPXt3aWR0aDpbXCJMZWZ0XCIsXCJSaWdodFwiXSxoZWlnaHQ6W1wiVG9wXCIsXCJCb3R0b21cIl19LEo9W1wibWFyZ2luTGVmdFwiLFwibWFyZ2luUmlnaHRcIixcIm1hcmdpblRvcFwiLFwibWFyZ2luQm90dG9tXCJdLHRlPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcj1wYXJzZUZsb2F0KFwid2lkdGhcIj09PWU/dC5vZmZzZXRXaWR0aDp0Lm9mZnNldEhlaWdodCkscz1LW2VdLG49cy5sZW5ndGg7Zm9yKGk9aXx8SCh0LG51bGwpOy0tbj4tMTspci09cGFyc2VGbG9hdChxKHQsXCJwYWRkaW5nXCIrc1tuXSxpLCEwKSl8fDAsci09cGFyc2VGbG9hdChxKHQsXCJib3JkZXJcIitzW25dK1wiV2lkdGhcIixpLCEwKSl8fDA7cmV0dXJuIHJ9LGVlPWZ1bmN0aW9uKHQsZSl7KG51bGw9PXR8fFwiXCI9PT10fHxcImF1dG9cIj09PXR8fFwiYXV0byBhdXRvXCI9PT10KSYmKHQ9XCIwIDBcIik7dmFyIGk9dC5zcGxpdChcIiBcIikscj0tMSE9PXQuaW5kZXhPZihcImxlZnRcIik/XCIwJVwiOi0xIT09dC5pbmRleE9mKFwicmlnaHRcIik/XCIxMDAlXCI6aVswXSxzPS0xIT09dC5pbmRleE9mKFwidG9wXCIpP1wiMCVcIjotMSE9PXQuaW5kZXhPZihcImJvdHRvbVwiKT9cIjEwMCVcIjppWzFdO3JldHVybiBudWxsPT1zP3M9XCIwXCI6XCJjZW50ZXJcIj09PXMmJihzPVwiNTAlXCIpLChcImNlbnRlclwiPT09cnx8aXNOYU4ocGFyc2VGbG9hdChyKSkmJi0xPT09KHIrXCJcIikuaW5kZXhPZihcIj1cIikpJiYocj1cIjUwJVwiKSxlJiYoZS5veHA9LTEhPT1yLmluZGV4T2YoXCIlXCIpLGUub3lwPS0xIT09cy5pbmRleE9mKFwiJVwiKSxlLm94cj1cIj1cIj09PXIuY2hhckF0KDEpLGUub3lyPVwiPVwiPT09cy5jaGFyQXQoMSksZS5veD1wYXJzZUZsb2F0KHIucmVwbGFjZSh2LFwiXCIpKSxlLm95PXBhcnNlRmxvYXQocy5yZXBsYWNlKHYsXCJcIikpKSxyK1wiIFwiK3MrKGkubGVuZ3RoPjI/XCIgXCIraVsyXTpcIlwiKX0saWU9ZnVuY3Rpb24odCxlKXtyZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgdCYmXCI9XCI9PT10LmNoYXJBdCgxKT9wYXJzZUludCh0LmNoYXJBdCgwKStcIjFcIiwxMCkqcGFyc2VGbG9hdCh0LnN1YnN0cigyKSk6cGFyc2VGbG9hdCh0KS1wYXJzZUZsb2F0KGUpfSxyZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsPT10P2U6XCJzdHJpbmdcIj09dHlwZW9mIHQmJlwiPVwiPT09dC5jaGFyQXQoMSk/cGFyc2VJbnQodC5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcih0LnN1YnN0cigyKSkrZTpwYXJzZUZsb2F0KHQpfSxzZT1mdW5jdGlvbih0LGUsaSxyKXt2YXIgcyxuLGEsbyxsPTFlLTY7cmV0dXJuIG51bGw9PXQ/bz1lOlwibnVtYmVyXCI9PXR5cGVvZiB0P289dDoocz0zNjAsbj10LnNwbGl0KFwiX1wiKSxhPU51bWJlcihuWzBdLnJlcGxhY2UodixcIlwiKSkqKC0xPT09dC5pbmRleE9mKFwicmFkXCIpPzE6TCktKFwiPVwiPT09dC5jaGFyQXQoMSk/MDplKSxuLmxlbmd0aCYmKHImJihyW2ldPWUrYSksLTEhPT10LmluZGV4T2YoXCJzaG9ydFwiKSYmKGElPXMsYSE9PWElKHMvMikmJihhPTA+YT9hK3M6YS1zKSksLTEhPT10LmluZGV4T2YoXCJfY3dcIikmJjA+YT9hPShhKzk5OTk5OTk5OTkqcyklcy0oMHxhL3MpKnM6LTEhPT10LmluZGV4T2YoXCJjY3dcIikmJmE+MCYmKGE9KGEtOTk5OTk5OTk5OSpzKSVzLSgwfGEvcykqcykpLG89ZSthKSxsPm8mJm8+LWwmJihvPTApLG99LG5lPXthcXVhOlswLDI1NSwyNTVdLGxpbWU6WzAsMjU1LDBdLHNpbHZlcjpbMTkyLDE5MiwxOTJdLGJsYWNrOlswLDAsMF0sbWFyb29uOlsxMjgsMCwwXSx0ZWFsOlswLDEyOCwxMjhdLGJsdWU6WzAsMCwyNTVdLG5hdnk6WzAsMCwxMjhdLHdoaXRlOlsyNTUsMjU1LDI1NV0sZnVjaHNpYTpbMjU1LDAsMjU1XSxvbGl2ZTpbMTI4LDEyOCwwXSx5ZWxsb3c6WzI1NSwyNTUsMF0sb3JhbmdlOlsyNTUsMTY1LDBdLGdyYXk6WzEyOCwxMjgsMTI4XSxwdXJwbGU6WzEyOCwwLDEyOF0sZ3JlZW46WzAsMTI4LDBdLHJlZDpbMjU1LDAsMF0scGluazpbMjU1LDE5MiwyMDNdLGN5YW46WzAsMjU1LDI1NV0sdHJhbnNwYXJlbnQ6WzI1NSwyNTUsMjU1LDBdfSxhZT1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIHQ9MD50P3QrMTp0PjE/dC0xOnQsMHwyNTUqKDE+Nip0P2UrNiooaS1lKSp0Oi41PnQ/aToyPjMqdD9lKzYqKGktZSkqKDIvMy10KTplKSsuNX0sb2U9ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhO3JldHVybiB0JiZcIlwiIT09dD9cIm51bWJlclwiPT10eXBlb2YgdD9bdD4+MTYsMjU1JnQ+PjgsMjU1JnRdOihcIixcIj09PXQuY2hhckF0KHQubGVuZ3RoLTEpJiYodD10LnN1YnN0cigwLHQubGVuZ3RoLTEpKSxuZVt0XT9uZVt0XTpcIiNcIj09PXQuY2hhckF0KDApPyg0PT09dC5sZW5ndGgmJihlPXQuY2hhckF0KDEpLGk9dC5jaGFyQXQoMikscj10LmNoYXJBdCgzKSx0PVwiI1wiK2UrZStpK2krcityKSx0PXBhcnNlSW50KHQuc3Vic3RyKDEpLDE2KSxbdD4+MTYsMjU1JnQ+PjgsMjU1JnRdKTpcImhzbFwiPT09dC5zdWJzdHIoMCwzKT8odD10Lm1hdGNoKGQpLHM9TnVtYmVyKHRbMF0pJTM2MC8zNjAsbj1OdW1iZXIodFsxXSkvMTAwLGE9TnVtYmVyKHRbMl0pLzEwMCxpPS41Pj1hP2EqKG4rMSk6YStuLWEqbixlPTIqYS1pLHQubGVuZ3RoPjMmJih0WzNdPU51bWJlcih0WzNdKSksdFswXT1hZShzKzEvMyxlLGkpLHRbMV09YWUocyxlLGkpLHRbMl09YWUocy0xLzMsZSxpKSx0KToodD10Lm1hdGNoKGQpfHxuZS50cmFuc3BhcmVudCx0WzBdPU51bWJlcih0WzBdKSx0WzFdPU51bWJlcih0WzFdKSx0WzJdPU51bWJlcih0WzJdKSx0Lmxlbmd0aD4zJiYodFszXT1OdW1iZXIodFszXSkpLHQpKTpuZS5ibGFja30sbGU9XCIoPzpcXFxcYig/Oig/OnJnYnxyZ2JhfGhzbHxoc2xhKVxcXFwoLis/XFxcXCkpfFxcXFxCIy4rP1xcXFxiXCI7Zm9yKGwgaW4gbmUpbGUrPVwifFwiK2wrXCJcXFxcYlwiO2xlPVJlZ0V4cChsZStcIilcIixcImdpXCIpO3ZhciBoZT1mdW5jdGlvbih0LGUsaSxyKXtpZihudWxsPT10KXJldHVybiBmdW5jdGlvbih0KXtyZXR1cm4gdH07dmFyIHMsbj1lPyh0Lm1hdGNoKGxlKXx8W1wiXCJdKVswXTpcIlwiLGE9dC5zcGxpdChuKS5qb2luKFwiXCIpLm1hdGNoKGcpfHxbXSxvPXQuc3Vic3RyKDAsdC5pbmRleE9mKGFbMF0pKSxsPVwiKVwiPT09dC5jaGFyQXQodC5sZW5ndGgtMSk/XCIpXCI6XCJcIixoPS0xIT09dC5pbmRleE9mKFwiIFwiKT9cIiBcIjpcIixcIix1PWEubGVuZ3RoLGY9dT4wP2FbMF0ucmVwbGFjZShkLFwiXCIpOlwiXCI7cmV0dXJuIHU/cz1lP2Z1bmN0aW9uKHQpe3ZhciBlLF8scCxjO2lmKFwibnVtYmVyXCI9PXR5cGVvZiB0KXQrPWY7ZWxzZSBpZihyJiZELnRlc3QodCkpe2ZvcihjPXQucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIikscD0wO2MubGVuZ3RoPnA7cCsrKWNbcF09cyhjW3BdKTtyZXR1cm4gYy5qb2luKFwiLFwiKX1pZihlPSh0Lm1hdGNoKGxlKXx8W25dKVswXSxfPXQuc3BsaXQoZSkuam9pbihcIlwiKS5tYXRjaChnKXx8W10scD1fLmxlbmd0aCx1PnAtLSlmb3IoO3U+KytwOylfW3BdPWk/X1swfChwLTEpLzJdOmFbcF07cmV0dXJuIG8rXy5qb2luKGgpK2grZStsKygtMSE9PXQuaW5kZXhPZihcImluc2V0XCIpP1wiIGluc2V0XCI6XCJcIil9OmZ1bmN0aW9uKHQpe3ZhciBlLG4sXztpZihcIm51bWJlclwiPT10eXBlb2YgdCl0Kz1mO2Vsc2UgaWYociYmRC50ZXN0KHQpKXtmb3Iobj10LnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLF89MDtuLmxlbmd0aD5fO18rKyluW19dPXMobltfXSk7cmV0dXJuIG4uam9pbihcIixcIil9aWYoZT10Lm1hdGNoKGcpfHxbXSxfPWUubGVuZ3RoLHU+Xy0tKWZvcig7dT4rK187KWVbX109aT9lWzB8KF8tMSkvMl06YVtfXTtyZXR1cm4gbytlLmpvaW4oaCkrbH06ZnVuY3Rpb24odCl7cmV0dXJuIHR9fSx1ZT1mdW5jdGlvbih0KXtyZXR1cm4gdD10LnNwbGl0KFwiLFwiKSxmdW5jdGlvbihlLGkscixzLG4sYSxvKXt2YXIgbCxoPShpK1wiXCIpLnNwbGl0KFwiIFwiKTtmb3Iobz17fSxsPTA7ND5sO2wrKylvW3RbbF1dPWhbbF09aFtsXXx8aFsobC0xKS8yPj4wXTtyZXR1cm4gcy5wYXJzZShlLG8sbixhKX19LGZlPShFLl9zZXRQbHVnaW5SYXRpbz1mdW5jdGlvbih0KXt0aGlzLnBsdWdpbi5zZXRSYXRpbyh0KTtmb3IodmFyIGUsaSxyLHMsbj10aGlzLmRhdGEsYT1uLnByb3h5LG89bi5maXJzdE1QVCxsPTFlLTY7bzspZT1hW28udl0sby5yP2U9TWF0aC5yb3VuZChlKTpsPmUmJmU+LWwmJihlPTApLG8udFtvLnBdPWUsbz1vLl9uZXh0O2lmKG4uYXV0b1JvdGF0ZSYmKG4uYXV0b1JvdGF0ZS5yb3RhdGlvbj1hLnJvdGF0aW9uKSwxPT09dClmb3Iobz1uLmZpcnN0TVBUO287KXtpZihpPW8udCxpLnR5cGUpe2lmKDE9PT1pLnR5cGUpe2ZvcihzPWkueHMwK2kucytpLnhzMSxyPTE7aS5sPnI7cisrKXMrPWlbXCJ4blwiK3JdK2lbXCJ4c1wiKyhyKzEpXTtpLmU9c319ZWxzZSBpLmU9aS5zK2kueHMwO289by5fbmV4dH19LGZ1bmN0aW9uKHQsZSxpLHIscyl7dGhpcy50PXQsdGhpcy5wPWUsdGhpcy52PWksdGhpcy5yPXMsciYmKHIuX3ByZXY9dGhpcyx0aGlzLl9uZXh0PXIpfSksX2U9KEUuX3BhcnNlVG9Qcm94eT1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGEsbyxsLGgsdSxmPXIsXz17fSxwPXt9LGM9aS5fdHJhbnNmb3JtLGQ9Tjtmb3IoaS5fdHJhbnNmb3JtPW51bGwsTj1lLHI9dT1pLnBhcnNlKHQsZSxyLHMpLE49ZCxuJiYoaS5fdHJhbnNmb3JtPWMsZiYmKGYuX3ByZXY9bnVsbCxmLl9wcmV2JiYoZi5fcHJldi5fbmV4dD1udWxsKSkpO3ImJnIhPT1mOyl7aWYoMT49ci50eXBlJiYobz1yLnAscFtvXT1yLnMrci5jLF9bb109ci5zLG58fChoPW5ldyBmZShyLFwic1wiLG8saCxyLnIpLHIuYz0wKSwxPT09ci50eXBlKSlmb3IoYT1yLmw7LS1hPjA7KWw9XCJ4blwiK2Esbz1yLnArXCJfXCIrbCxwW29dPXIuZGF0YVtsXSxfW29dPXJbbF0sbnx8KGg9bmV3IGZlKHIsbCxvLGgsci5yeHBbbF0pKTtyPXIuX25leHR9cmV0dXJue3Byb3h5Ol8sZW5kOnAsZmlyc3RNUFQ6aCxwdDp1fX0sRS5DU1NQcm9wVHdlZW49ZnVuY3Rpb24odCxlLHIscyxhLG8sbCxoLHUsZixfKXt0aGlzLnQ9dCx0aGlzLnA9ZSx0aGlzLnM9cix0aGlzLmM9cyx0aGlzLm49bHx8ZSx0IGluc3RhbmNlb2YgX2V8fG4ucHVzaCh0aGlzLm4pLHRoaXMucj1oLHRoaXMudHlwZT1vfHwwLHUmJih0aGlzLnByPXUsaT0hMCksdGhpcy5iPXZvaWQgMD09PWY/cjpmLHRoaXMuZT12b2lkIDA9PT1fP3IrczpfLGEmJih0aGlzLl9uZXh0PWEsYS5fcHJldj10aGlzKX0pLHBlPWEucGFyc2VDb21wbGV4PWZ1bmN0aW9uKHQsZSxpLHIscyxuLGEsbyxsLHUpe2k9aXx8bnx8XCJcIixhPW5ldyBfZSh0LGUsMCwwLGEsdT8yOjEsbnVsbCwhMSxvLGkscikscis9XCJcIjt2YXIgZixfLHAsYyxnLHYseSxULHcseCxQLFMsQz1pLnNwbGl0KFwiLCBcIikuam9pbihcIixcIikuc3BsaXQoXCIgXCIpLFI9ci5zcGxpdChcIiwgXCIpLmpvaW4oXCIsXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoLEE9aCE9PSExO2ZvcigoLTEhPT1yLmluZGV4T2YoXCIsXCIpfHwtMSE9PWkuaW5kZXhPZihcIixcIikpJiYoQz1DLmpvaW4oXCIgXCIpLnJlcGxhY2UoRCxcIiwgXCIpLnNwbGl0KFwiIFwiKSxSPVIuam9pbihcIiBcIikucmVwbGFjZShELFwiLCBcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgpLGshPT1SLmxlbmd0aCYmKEM9KG58fFwiXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoKSxhLnBsdWdpbj1sLGEuc2V0UmF0aW89dSxmPTA7az5mO2YrKylpZihjPUNbZl0sZz1SW2ZdLFQ9cGFyc2VGbG9hdChjKSxUfHwwPT09VClhLmFwcGVuZFh0cmEoXCJcIixULGllKGcsVCksZy5yZXBsYWNlKG0sXCJcIiksQSYmLTEhPT1nLmluZGV4T2YoXCJweFwiKSwhMCk7ZWxzZSBpZihzJiYoXCIjXCI9PT1jLmNoYXJBdCgwKXx8bmVbY118fGIudGVzdChjKSkpUz1cIixcIj09PWcuY2hhckF0KGcubGVuZ3RoLTEpP1wiKSxcIjpcIilcIixjPW9lKGMpLGc9b2UoZyksdz1jLmxlbmd0aCtnLmxlbmd0aD42LHcmJiFZJiYwPT09Z1szXT8oYVtcInhzXCIrYS5sXSs9YS5sP1wiIHRyYW5zcGFyZW50XCI6XCJ0cmFuc3BhcmVudFwiLGEuZT1hLmUuc3BsaXQoUltmXSkuam9pbihcInRyYW5zcGFyZW50XCIpKTooWXx8KHc9ITEpLGEuYXBwZW5kWHRyYSh3P1wicmdiYShcIjpcInJnYihcIixjWzBdLGdbMF0tY1swXSxcIixcIiwhMCwhMCkuYXBwZW5kWHRyYShcIlwiLGNbMV0sZ1sxXS1jWzFdLFwiLFwiLCEwKS5hcHBlbmRYdHJhKFwiXCIsY1syXSxnWzJdLWNbMl0sdz9cIixcIjpTLCEwKSx3JiYoYz00PmMubGVuZ3RoPzE6Y1szXSxhLmFwcGVuZFh0cmEoXCJcIixjLCg0PmcubGVuZ3RoPzE6Z1szXSktYyxTLCExKSkpO2Vsc2UgaWYodj1jLm1hdGNoKGQpKXtpZih5PWcubWF0Y2gobSksIXl8fHkubGVuZ3RoIT09di5sZW5ndGgpcmV0dXJuIGE7Zm9yKHA9MCxfPTA7di5sZW5ndGg+XztfKyspUD12W19dLHg9Yy5pbmRleE9mKFAscCksYS5hcHBlbmRYdHJhKGMuc3Vic3RyKHAseC1wKSxOdW1iZXIoUCksaWUoeVtfXSxQKSxcIlwiLEEmJlwicHhcIj09PWMuc3Vic3RyKHgrUC5sZW5ndGgsMiksMD09PV8pLHA9eCtQLmxlbmd0aDthW1wieHNcIithLmxdKz1jLnN1YnN0cihwKX1lbHNlIGFbXCJ4c1wiK2EubF0rPWEubD9cIiBcIitjOmM7aWYoLTEhPT1yLmluZGV4T2YoXCI9XCIpJiZhLmRhdGEpe2ZvcihTPWEueHMwK2EuZGF0YS5zLGY9MTthLmw+ZjtmKyspUys9YVtcInhzXCIrZl0rYS5kYXRhW1wieG5cIitmXTthLmU9UythW1wieHNcIitmXX1yZXR1cm4gYS5sfHwoYS50eXBlPS0xLGEueHMwPWEuZSksYS54Zmlyc3R8fGF9LGNlPTk7Zm9yKGw9X2UucHJvdG90eXBlLGwubD1sLnByPTA7LS1jZT4wOylsW1wieG5cIitjZV09MCxsW1wieHNcIitjZV09XCJcIjtsLnhzMD1cIlwiLGwuX25leHQ9bC5fcHJldj1sLnhmaXJzdD1sLmRhdGE9bC5wbHVnaW49bC5zZXRSYXRpbz1sLnJ4cD1udWxsLGwuYXBwZW5kWHRyYT1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGE9dGhpcyxvPWEubDtyZXR1cm4gYVtcInhzXCIrb10rPW4mJm8/XCIgXCIrdDp0fHxcIlwiLGl8fDA9PT1vfHxhLnBsdWdpbj8oYS5sKyssYS50eXBlPWEuc2V0UmF0aW8/MjoxLGFbXCJ4c1wiK2EubF09cnx8XCJcIixvPjA/KGEuZGF0YVtcInhuXCIrb109ZStpLGEucnhwW1wieG5cIitvXT1zLGFbXCJ4blwiK29dPWUsYS5wbHVnaW58fChhLnhmaXJzdD1uZXcgX2UoYSxcInhuXCIrbyxlLGksYS54Zmlyc3R8fGEsMCxhLm4scyxhLnByKSxhLnhmaXJzdC54czA9MCksYSk6KGEuZGF0YT17czplK2l9LGEucnhwPXt9LGEucz1lLGEuYz1pLGEucj1zLGEpKTooYVtcInhzXCIrb10rPWUrKHJ8fFwiXCIpLGEpfTt2YXIgZGU9ZnVuY3Rpb24odCxlKXtlPWV8fHt9LHRoaXMucD1lLnByZWZpeD9WKHQpfHx0OnQsb1t0XT1vW3RoaXMucF09dGhpcyx0aGlzLmZvcm1hdD1lLmZvcm1hdHRlcnx8aGUoZS5kZWZhdWx0VmFsdWUsZS5jb2xvcixlLmNvbGxhcHNpYmxlLGUubXVsdGkpLGUucGFyc2VyJiYodGhpcy5wYXJzZT1lLnBhcnNlciksdGhpcy5jbHJzPWUuY29sb3IsdGhpcy5tdWx0aT1lLm11bHRpLHRoaXMua2V5d29yZD1lLmtleXdvcmQsdGhpcy5kZmx0PWUuZGVmYXVsdFZhbHVlLHRoaXMucHI9ZS5wcmlvcml0eXx8MH0sbWU9RS5fcmVnaXN0ZXJDb21wbGV4U3BlY2lhbFByb3A9ZnVuY3Rpb24odCxlLGkpe1wib2JqZWN0XCIhPXR5cGVvZiBlJiYoZT17cGFyc2VyOml9KTt2YXIgcixzLG49dC5zcGxpdChcIixcIiksYT1lLmRlZmF1bHRWYWx1ZTtmb3IoaT1pfHxbYV0scj0wO24ubGVuZ3RoPnI7cisrKWUucHJlZml4PTA9PT1yJiZlLnByZWZpeCxlLmRlZmF1bHRWYWx1ZT1pW3JdfHxhLHM9bmV3IGRlKG5bcl0sZSl9LGdlPWZ1bmN0aW9uKHQpe2lmKCFvW3RdKXt2YXIgZT10LmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK3Quc3Vic3RyKDEpK1wiUGx1Z2luXCI7bWUodCx7cGFyc2VyOmZ1bmN0aW9uKHQsaSxyLHMsbixhLGwpe3ZhciBoPSh3aW5kb3cuR3JlZW5Tb2NrR2xvYmFsc3x8d2luZG93KS5jb20uZ3JlZW5zb2NrLnBsdWdpbnNbZV07cmV0dXJuIGg/KGguX2Nzc1JlZ2lzdGVyKCksb1tyXS5wYXJzZSh0LGkscixzLG4sYSxsKSk6KFUoXCJFcnJvcjogXCIrZStcIiBqcyBmaWxlIG5vdCBsb2FkZWQuXCIpLG4pfX0pfX07bD1kZS5wcm90b3R5cGUsbC5wYXJzZUNvbXBsZXg9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhLG8sbCxoLHUsZixfPXRoaXMua2V5d29yZDtpZih0aGlzLm11bHRpJiYoRC50ZXN0KGkpfHxELnRlc3QoZSk/KG89ZS5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxsPWkucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIikpOl8mJihvPVtlXSxsPVtpXSkpLGwpe2ZvcihoPWwubGVuZ3RoPm8ubGVuZ3RoP2wubGVuZ3RoOm8ubGVuZ3RoLGE9MDtoPmE7YSsrKWU9b1thXT1vW2FdfHx0aGlzLmRmbHQsaT1sW2FdPWxbYV18fHRoaXMuZGZsdCxfJiYodT1lLmluZGV4T2YoXyksZj1pLmluZGV4T2YoXyksdSE9PWYmJihpPS0xPT09Zj9sOm8saVthXSs9XCIgXCIrXykpO2U9by5qb2luKFwiLCBcIiksaT1sLmpvaW4oXCIsIFwiKX1yZXR1cm4gcGUodCx0aGlzLnAsZSxpLHRoaXMuY2xycyx0aGlzLmRmbHQscix0aGlzLnByLHMsbil9LGwucGFyc2U9ZnVuY3Rpb24odCxlLGkscixuLGEpe3JldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLHRoaXMuZm9ybWF0KHEodCx0aGlzLnAscywhMSx0aGlzLmRmbHQpKSx0aGlzLmZvcm1hdChlKSxuLGEpfSxhLnJlZ2lzdGVyU3BlY2lhbFByb3A9ZnVuY3Rpb24odCxlLGkpe21lKHQse3BhcnNlcjpmdW5jdGlvbih0LHIscyxuLGEsbyl7dmFyIGw9bmV3IF9lKHQscywwLDAsYSwyLHMsITEsaSk7cmV0dXJuIGwucGx1Z2luPW8sbC5zZXRSYXRpbz1lKHQscixuLl90d2VlbixzKSxsfSxwcmlvcml0eTppfSl9O3ZhciB2ZT1cInNjYWxlWCxzY2FsZVksc2NhbGVaLHgseSx6LHNrZXdYLHNrZXdZLHJvdGF0aW9uLHJvdGF0aW9uWCxyb3RhdGlvblkscGVyc3BlY3RpdmVcIi5zcGxpdChcIixcIikseWU9VihcInRyYW5zZm9ybVwiKSxUZT1XK1widHJhbnNmb3JtXCIsd2U9VihcInRyYW5zZm9ybU9yaWdpblwiKSx4ZT1udWxsIT09VihcInBlcnNwZWN0aXZlXCIpLGJlPUUuVHJhbnNmb3JtPWZ1bmN0aW9uKCl7dGhpcy5za2V3WT0wfSxQZT1FLmdldFRyYW5zZm9ybT1mdW5jdGlvbih0LGUsaSxyKXtpZih0Ll9nc1RyYW5zZm9ybSYmaSYmIXIpcmV0dXJuIHQuX2dzVHJhbnNmb3JtO3ZhciBzLG4sbyxsLGgsdSxmLF8scCxjLGQsbSxnLHY9aT90Ll9nc1RyYW5zZm9ybXx8bmV3IGJlOm5ldyBiZSx5PTA+di5zY2FsZVgsVD0yZS01LHc9MWU1LHg9MTc5Ljk5LGI9eCpNLFA9eGU/cGFyc2VGbG9hdChxKHQsd2UsZSwhMSxcIjAgMCAwXCIpLnNwbGl0KFwiIFwiKVsyXSl8fHYuek9yaWdpbnx8MDowO2Zvcih5ZT9zPXEodCxUZSxlLCEwKTp0LmN1cnJlbnRTdHlsZSYmKHM9dC5jdXJyZW50U3R5bGUuZmlsdGVyLm1hdGNoKEEpLHM9cyYmND09PXMubGVuZ3RoP1tzWzBdLnN1YnN0cig0KSxOdW1iZXIoc1syXS5zdWJzdHIoNCkpLE51bWJlcihzWzFdLnN1YnN0cig0KSksc1szXS5zdWJzdHIoNCksdi54fHwwLHYueXx8MF0uam9pbihcIixcIik6XCJcIiksbj0oc3x8XCJcIikubWF0Y2goLyg/OlxcLXxcXGIpW1xcZFxcLVxcLmVdK1xcYi9naSl8fFtdLG89bi5sZW5ndGg7LS1vPi0xOylsPU51bWJlcihuW29dKSxuW29dPShoPWwtKGx8PTApKT8oMHxoKncrKDA+aD8tLjU6LjUpKS93K2w6bDtpZigxNj09PW4ubGVuZ3RoKXt2YXIgUz1uWzhdLEM9bls5XSxSPW5bMTBdLGs9blsxMl0sTz1uWzEzXSxEPW5bMTRdO2lmKHYuek9yaWdpbiYmKEQ9LXYuek9yaWdpbixrPVMqRC1uWzEyXSxPPUMqRC1uWzEzXSxEPVIqRCt2LnpPcmlnaW4tblsxNF0pLCFpfHxyfHxudWxsPT12LnJvdGF0aW9uWCl7dmFyIE4sWCx6LEksRSxGLFksQj1uWzBdLFU9blsxXSxXPW5bMl0saj1uWzNdLFY9bls0XSxIPW5bNV0sUT1uWzZdLFo9bls3XSwkPW5bMTFdLEc9TWF0aC5hdGFuMihRLFIpLEs9LWI+R3x8Rz5iO3Yucm90YXRpb25YPUcqTCxHJiYoST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksTj1WKkkrUypFLFg9SCpJK0MqRSx6PVEqSStSKkUsUz1WKi1FK1MqSSxDPUgqLUUrQypJLFI9USotRStSKkksJD1aKi1FKyQqSSxWPU4sSD1YLFE9eiksRz1NYXRoLmF0YW4yKFMsQiksdi5yb3RhdGlvblk9RypMLEcmJihGPS1iPkd8fEc+YixJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxOPUIqSS1TKkUsWD1VKkktQypFLHo9VypJLVIqRSxDPVUqRStDKkksUj1XKkUrUipJLCQ9aipFKyQqSSxCPU4sVT1YLFc9eiksRz1NYXRoLmF0YW4yKFUsSCksdi5yb3RhdGlvbj1HKkwsRyYmKFk9LWI+R3x8Rz5iLEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLEI9QipJK1YqRSxYPVUqSStIKkUsSD1VKi1FK0gqSSxRPVcqLUUrUSpJLFU9WCksWSYmSz92LnJvdGF0aW9uPXYucm90YXRpb25YPTA6WSYmRj92LnJvdGF0aW9uPXYucm90YXRpb25ZPTA6RiYmSyYmKHYucm90YXRpb25ZPXYucm90YXRpb25YPTApLHYuc2NhbGVYPSgwfE1hdGguc3FydChCKkIrVSpVKSp3Ky41KS93LHYuc2NhbGVZPSgwfE1hdGguc3FydChIKkgrQypDKSp3Ky41KS93LHYuc2NhbGVaPSgwfE1hdGguc3FydChRKlErUipSKSp3Ky41KS93LHYuc2tld1g9MCx2LnBlcnNwZWN0aXZlPSQ/MS8oMD4kPy0kOiQpOjAsdi54PWssdi55PU8sdi56PUR9fWVsc2UgaWYoISh4ZSYmIXImJm4ubGVuZ3RoJiZ2Lng9PT1uWzRdJiZ2Lnk9PT1uWzVdJiYodi5yb3RhdGlvblh8fHYucm90YXRpb25ZKXx8dm9pZCAwIT09di54JiZcIm5vbmVcIj09PXEodCxcImRpc3BsYXlcIixlKSkpe3ZhciBKPW4ubGVuZ3RoPj02LHRlPUo/blswXToxLGVlPW5bMV18fDAsaWU9blsyXXx8MCxyZT1KP25bM106MTt2Lng9bls0XXx8MCx2Lnk9bls1XXx8MCx1PU1hdGguc3FydCh0ZSp0ZStlZSplZSksZj1NYXRoLnNxcnQocmUqcmUraWUqaWUpLF89dGV8fGVlP01hdGguYXRhbjIoZWUsdGUpKkw6di5yb3RhdGlvbnx8MCxwPWllfHxyZT9NYXRoLmF0YW4yKGllLHJlKSpMK186di5za2V3WHx8MCxjPXUtTWF0aC5hYnModi5zY2FsZVh8fDApLGQ9Zi1NYXRoLmFicyh2LnNjYWxlWXx8MCksTWF0aC5hYnMocCk+OTAmJjI3MD5NYXRoLmFicyhwKSYmKHk/KHUqPS0xLHArPTA+PV8/MTgwOi0xODAsXys9MD49Xz8xODA6LTE4MCk6KGYqPS0xLHArPTA+PXA/MTgwOi0xODApKSxtPShfLXYucm90YXRpb24pJTE4MCxnPShwLXYuc2tld1gpJTE4MCwodm9pZCAwPT09di5za2V3WHx8Yz5UfHwtVD5jfHxkPlR8fC1UPmR8fG0+LXgmJng+bSYmZmFsc2V8bSp3fHxnPi14JiZ4PmcmJmZhbHNlfGcqdykmJih2LnNjYWxlWD11LHYuc2NhbGVZPWYsdi5yb3RhdGlvbj1fLHYuc2tld1g9cCkseGUmJih2LnJvdGF0aW9uWD12LnJvdGF0aW9uWT12Lno9MCx2LnBlcnNwZWN0aXZlPXBhcnNlRmxvYXQoYS5kZWZhdWx0VHJhbnNmb3JtUGVyc3BlY3RpdmUpfHwwLHYuc2NhbGVaPTEpfXYuek9yaWdpbj1QO2ZvcihvIGluIHYpVD52W29dJiZ2W29dPi1UJiYodltvXT0wKTtyZXR1cm4gaSYmKHQuX2dzVHJhbnNmb3JtPXYpLHZ9LFNlPWZ1bmN0aW9uKHQpe3ZhciBlLGkscj10aGlzLmRhdGEscz0tci5yb3RhdGlvbipNLG49cytyLnNrZXdYKk0sYT0xZTUsbz0oMHxNYXRoLmNvcyhzKSpyLnNjYWxlWCphKS9hLGw9KDB8TWF0aC5zaW4ocykqci5zY2FsZVgqYSkvYSxoPSgwfE1hdGguc2luKG4pKi1yLnNjYWxlWSphKS9hLHU9KDB8TWF0aC5jb3Mobikqci5zY2FsZVkqYSkvYSxmPXRoaXMudC5zdHlsZSxfPXRoaXMudC5jdXJyZW50U3R5bGU7aWYoXyl7aT1sLGw9LWgsaD0taSxlPV8uZmlsdGVyLGYuZmlsdGVyPVwiXCI7dmFyIHAsZCxtPXRoaXMudC5vZmZzZXRXaWR0aCxnPXRoaXMudC5vZmZzZXRIZWlnaHQsdj1cImFic29sdXRlXCIhPT1fLnBvc2l0aW9uLHc9XCJwcm9naWQ6RFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuTWF0cml4KE0xMT1cIitvK1wiLCBNMTI9XCIrbCtcIiwgTTIxPVwiK2grXCIsIE0yMj1cIit1LHg9ci54LGI9ci55O2lmKG51bGwhPXIub3gmJihwPShyLm94cD8uMDEqbSpyLm94OnIub3gpLW0vMixkPShyLm95cD8uMDEqZypyLm95OnIub3kpLWcvMix4Kz1wLShwKm8rZCpsKSxiKz1kLShwKmgrZCp1KSksdj8ocD1tLzIsZD1nLzIsdys9XCIsIER4PVwiKyhwLShwKm8rZCpsKSt4KStcIiwgRHk9XCIrKGQtKHAqaCtkKnUpK2IpK1wiKVwiKTp3Kz1cIiwgc2l6aW5nTWV0aG9kPSdhdXRvIGV4cGFuZCcpXCIsZi5maWx0ZXI9LTEhPT1lLmluZGV4T2YoXCJEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5NYXRyaXgoXCIpP2UucmVwbGFjZShPLHcpOncrXCIgXCIrZSwoMD09PXR8fDE9PT10KSYmMT09PW8mJjA9PT1sJiYwPT09aCYmMT09PXUmJih2JiYtMT09PXcuaW5kZXhPZihcIkR4PTAsIER5PTBcIil8fFQudGVzdChlKSYmMTAwIT09cGFyc2VGbG9hdChSZWdFeHAuJDEpfHwtMT09PWUuaW5kZXhPZihcImdyYWRpZW50KFwiJiZlLmluZGV4T2YoXCJBbHBoYVwiKSkmJmYucmVtb3ZlQXR0cmlidXRlKFwiZmlsdGVyXCIpKSwhdil7dmFyIFAsUyxDLFI9OD5jPzE6LTE7Zm9yKHA9ci5pZU9mZnNldFh8fDAsZD1yLmllT2Zmc2V0WXx8MCxyLmllT2Zmc2V0WD1NYXRoLnJvdW5kKChtLSgoMD5vPy1vOm8pKm0rKDA+bD8tbDpsKSpnKSkvMit4KSxyLmllT2Zmc2V0WT1NYXRoLnJvdW5kKChnLSgoMD51Py11OnUpKmcrKDA+aD8taDpoKSptKSkvMitiKSxjZT0wOzQ+Y2U7Y2UrKylTPUpbY2VdLFA9X1tTXSxpPS0xIT09UC5pbmRleE9mKFwicHhcIik/cGFyc2VGbG9hdChQKTpRKHRoaXMudCxTLHBhcnNlRmxvYXQoUCksUC5yZXBsYWNlKHksXCJcIikpfHwwLEM9aSE9PXJbU10/Mj5jZT8tci5pZU9mZnNldFg6LXIuaWVPZmZzZXRZOjI+Y2U/cC1yLmllT2Zmc2V0WDpkLXIuaWVPZmZzZXRZLGZbU109KHJbU109TWF0aC5yb3VuZChpLUMqKDA9PT1jZXx8Mj09PWNlPzE6UikpKStcInB4XCJ9fX0sQ2U9RS5zZXQzRFRyYW5zZm9ybVJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYSxvLGwsaCx1LGYscCxjLGQsbSxnLHYseSxULHcseCxiLFAsUz10aGlzLmRhdGEsQz10aGlzLnQuc3R5bGUsUj1TLnJvdGF0aW9uKk0saz1TLnNjYWxlWCxBPVMuc2NhbGVZLE89Uy5zY2FsZVosRD1TLnBlcnNwZWN0aXZlO2lmKCEoMSE9PXQmJjAhPT10fHxcImF1dG9cIiE9PVMuZm9yY2UzRHx8Uy5yb3RhdGlvbll8fFMucm90YXRpb25YfHwxIT09T3x8RHx8Uy56KSlyZXR1cm4gUmUuY2FsbCh0aGlzLHQpLHZvaWQgMDtpZihfKXt2YXIgTD0xZS00O0w+ayYmaz4tTCYmKGs9Tz0yZS01KSxMPkEmJkE+LUwmJihBPU89MmUtNSksIUR8fFMuenx8Uy5yb3RhdGlvblh8fFMucm90YXRpb25ZfHwoRD0wKX1pZihSfHxTLnNrZXdYKXk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxlPXksbj1ULFMuc2tld1gmJihSLT1TLnNrZXdYKk0seT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLFwic2ltcGxlXCI9PT1TLnNrZXdUeXBlJiYodz1NYXRoLnRhbihTLnNrZXdYKk0pLHc9TWF0aC5zcXJ0KDErdyp3KSx5Kj13LFQqPXcpKSxpPS1ULGE9eTtlbHNle2lmKCEoUy5yb3RhdGlvbll8fFMucm90YXRpb25YfHwxIT09T3x8RCkpcmV0dXJuIENbeWVdPVwidHJhbnNsYXRlM2QoXCIrUy54K1wicHgsXCIrUy55K1wicHgsXCIrUy56K1wicHgpXCIrKDEhPT1rfHwxIT09QT9cIiBzY2FsZShcIitrK1wiLFwiK0ErXCIpXCI6XCJcIiksdm9pZCAwO2U9YT0xLGk9bj0wfWY9MSxyPXM9bz1sPWg9dT1wPWM9ZD0wLG09RD8tMS9EOjAsZz1TLnpPcmlnaW4sdj0xZTUsUj1TLnJvdGF0aW9uWSpNLFImJih5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksaD1mKi1ULGM9bSotVCxyPWUqVCxvPW4qVCxmKj15LG0qPXksZSo9eSxuKj15KSxSPVMucm90YXRpb25YKk0sUiYmKHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSx3PWkqeStyKlQseD1hKnkrbypULGI9dSp5K2YqVCxQPWQqeSttKlQscj1pKi1UK3IqeSxvPWEqLVQrbyp5LGY9dSotVCtmKnksbT1kKi1UK20qeSxpPXcsYT14LHU9YixkPVApLDEhPT1PJiYocio9TyxvKj1PLGYqPU8sbSo9TyksMSE9PUEmJihpKj1BLGEqPUEsdSo9QSxkKj1BKSwxIT09ayYmKGUqPWssbio9ayxoKj1rLGMqPWspLGcmJihwLT1nLHM9cipwLGw9bypwLHA9ZipwK2cpLHM9KHc9KHMrPVMueCktKHN8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K3M6cyxsPSh3PShsKz1TLnkpLShsfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditsOmwscD0odz0ocCs9Uy56KS0ocHw9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrcDpwLENbeWVdPVwibWF0cml4M2QoXCIrWygwfGUqdikvdiwoMHxuKnYpL3YsKDB8aCp2KS92LCgwfGMqdikvdiwoMHxpKnYpL3YsKDB8YSp2KS92LCgwfHUqdikvdiwoMHxkKnYpL3YsKDB8cip2KS92LCgwfG8qdikvdiwoMHxmKnYpL3YsKDB8bSp2KS92LHMsbCxwLEQ/MSstcC9EOjFdLmpvaW4oXCIsXCIpK1wiKVwifSxSZT1FLnNldDJEVHJhbnNmb3JtUmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhPXRoaXMuZGF0YSxvPXRoaXMudCxsPW8uc3R5bGU7cmV0dXJuIGEucm90YXRpb25YfHxhLnJvdGF0aW9uWXx8YS56fHxhLmZvcmNlM0Q9PT0hMHx8XCJhdXRvXCI9PT1hLmZvcmNlM0QmJjEhPT10JiYwIT09dD8odGhpcy5zZXRSYXRpbz1DZSxDZS5jYWxsKHRoaXMsdCksdm9pZCAwKTooYS5yb3RhdGlvbnx8YS5za2V3WD8oZT1hLnJvdGF0aW9uKk0saT1lLWEuc2tld1gqTSxyPTFlNSxzPWEuc2NhbGVYKnIsbj1hLnNjYWxlWSpyLGxbeWVdPVwibWF0cml4KFwiKygwfE1hdGguY29zKGUpKnMpL3IrXCIsXCIrKDB8TWF0aC5zaW4oZSkqcykvcitcIixcIisoMHxNYXRoLnNpbihpKSotbikvcitcIixcIisoMHxNYXRoLmNvcyhpKSpuKS9yK1wiLFwiK2EueCtcIixcIithLnkrXCIpXCIpOmxbeWVdPVwibWF0cml4KFwiK2Euc2NhbGVYK1wiLDAsMCxcIithLnNjYWxlWStcIixcIithLngrXCIsXCIrYS55K1wiKVwiLHZvaWQgMCl9O21lKFwidHJhbnNmb3JtLHNjYWxlLHNjYWxlWCxzY2FsZVksc2NhbGVaLHgseSx6LHJvdGF0aW9uLHJvdGF0aW9uWCxyb3RhdGlvblkscm90YXRpb25aLHNrZXdYLHNrZXdZLHNob3J0Um90YXRpb24sc2hvcnRSb3RhdGlvblgsc2hvcnRSb3RhdGlvblksc2hvcnRSb3RhdGlvblosdHJhbnNmb3JtT3JpZ2luLHRyYW5zZm9ybVBlcnNwZWN0aXZlLGRpcmVjdGlvbmFsUm90YXRpb24scGFyc2VUcmFuc2Zvcm0sZm9yY2UzRCxza2V3VHlwZVwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLG8sbCl7aWYoci5fdHJhbnNmb3JtKXJldHVybiBuO3ZhciBoLHUsZixfLHAsYyxkLG09ci5fdHJhbnNmb3JtPVBlKHQscywhMCxsLnBhcnNlVHJhbnNmb3JtKSxnPXQuc3R5bGUsdj0xZS02LHk9dmUubGVuZ3RoLFQ9bCx3PXt9O2lmKFwic3RyaW5nXCI9PXR5cGVvZiBULnRyYW5zZm9ybSYmeWUpZj16LnN0eWxlLGZbeWVdPVQudHJhbnNmb3JtLGYuZGlzcGxheT1cImJsb2NrXCIsZi5wb3NpdGlvbj1cImFic29sdXRlXCIsWC5ib2R5LmFwcGVuZENoaWxkKHopLGg9UGUoeixudWxsLCExKSxYLmJvZHkucmVtb3ZlQ2hpbGQoeik7ZWxzZSBpZihcIm9iamVjdFwiPT10eXBlb2YgVCl7aWYoaD17c2NhbGVYOnJlKG51bGwhPVQuc2NhbGVYP1Quc2NhbGVYOlQuc2NhbGUsbS5zY2FsZVgpLHNjYWxlWTpyZShudWxsIT1ULnNjYWxlWT9ULnNjYWxlWTpULnNjYWxlLG0uc2NhbGVZKSxzY2FsZVo6cmUoVC5zY2FsZVosbS5zY2FsZVopLHg6cmUoVC54LG0ueCkseTpyZShULnksbS55KSx6OnJlKFQueixtLnopLHBlcnNwZWN0aXZlOnJlKFQudHJhbnNmb3JtUGVyc3BlY3RpdmUsbS5wZXJzcGVjdGl2ZSl9LGQ9VC5kaXJlY3Rpb25hbFJvdGF0aW9uLG51bGwhPWQpaWYoXCJvYmplY3RcIj09dHlwZW9mIGQpZm9yKGYgaW4gZClUW2ZdPWRbZl07ZWxzZSBULnJvdGF0aW9uPWQ7aC5yb3RhdGlvbj1zZShcInJvdGF0aW9uXCJpbiBUP1Qucm90YXRpb246XCJzaG9ydFJvdGF0aW9uXCJpbiBUP1Quc2hvcnRSb3RhdGlvbitcIl9zaG9ydFwiOlwicm90YXRpb25aXCJpbiBUP1Qucm90YXRpb25aOm0ucm90YXRpb24sbS5yb3RhdGlvbixcInJvdGF0aW9uXCIsdykseGUmJihoLnJvdGF0aW9uWD1zZShcInJvdGF0aW9uWFwiaW4gVD9ULnJvdGF0aW9uWDpcInNob3J0Um90YXRpb25YXCJpbiBUP1Quc2hvcnRSb3RhdGlvblgrXCJfc2hvcnRcIjptLnJvdGF0aW9uWHx8MCxtLnJvdGF0aW9uWCxcInJvdGF0aW9uWFwiLHcpLGgucm90YXRpb25ZPXNlKFwicm90YXRpb25ZXCJpbiBUP1Qucm90YXRpb25ZOlwic2hvcnRSb3RhdGlvbllcImluIFQ/VC5zaG9ydFJvdGF0aW9uWStcIl9zaG9ydFwiOm0ucm90YXRpb25ZfHwwLG0ucm90YXRpb25ZLFwicm90YXRpb25ZXCIsdykpLGguc2tld1g9bnVsbD09VC5za2V3WD9tLnNrZXdYOnNlKFQuc2tld1gsbS5za2V3WCksaC5za2V3WT1udWxsPT1ULnNrZXdZP20uc2tld1k6c2UoVC5za2V3WSxtLnNrZXdZKSwodT1oLnNrZXdZLW0uc2tld1kpJiYoaC5za2V3WCs9dSxoLnJvdGF0aW9uKz11KX1mb3IoeGUmJm51bGwhPVQuZm9yY2UzRCYmKG0uZm9yY2UzRD1ULmZvcmNlM0QsYz0hMCksbS5za2V3VHlwZT1ULnNrZXdUeXBlfHxtLnNrZXdUeXBlfHxhLmRlZmF1bHRTa2V3VHlwZSxwPW0uZm9yY2UzRHx8bS56fHxtLnJvdGF0aW9uWHx8bS5yb3RhdGlvbll8fGguenx8aC5yb3RhdGlvblh8fGgucm90YXRpb25ZfHxoLnBlcnNwZWN0aXZlLHB8fG51bGw9PVQuc2NhbGV8fChoLnNjYWxlWj0xKTstLXk+LTE7KWk9dmVbeV0sXz1oW2ldLW1baV0sKF8+dnx8LXY+X3x8bnVsbCE9TltpXSkmJihjPSEwLG49bmV3IF9lKG0saSxtW2ldLF8sbiksaSBpbiB3JiYobi5lPXdbaV0pLG4ueHMwPTAsbi5wbHVnaW49byxyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKG4ubikpO3JldHVybiBfPVQudHJhbnNmb3JtT3JpZ2luLChffHx4ZSYmcCYmbS56T3JpZ2luKSYmKHllPyhjPSEwLGk9d2UsXz0oX3x8cSh0LGkscywhMSxcIjUwJSA1MCVcIikpK1wiXCIsbj1uZXcgX2UoZyxpLDAsMCxuLC0xLFwidHJhbnNmb3JtT3JpZ2luXCIpLG4uYj1nW2ldLG4ucGx1Z2luPW8seGU/KGY9bS56T3JpZ2luLF89Xy5zcGxpdChcIiBcIiksbS56T3JpZ2luPShfLmxlbmd0aD4yJiYoMD09PWZ8fFwiMHB4XCIhPT1fWzJdKT9wYXJzZUZsb2F0KF9bMl0pOmYpfHwwLG4ueHMwPW4uZT1fWzBdK1wiIFwiKyhfWzFdfHxcIjUwJVwiKStcIiAwcHhcIixuPW5ldyBfZShtLFwiek9yaWdpblwiLDAsMCxuLC0xLG4ubiksbi5iPWYsbi54czA9bi5lPW0uek9yaWdpbik6bi54czA9bi5lPV8pOmVlKF8rXCJcIixtKSksYyYmKHIuX3RyYW5zZm9ybVR5cGU9cHx8Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGU/MzoyKSxufSxwcmVmaXg6ITB9KSxtZShcImJveFNoYWRvd1wiLHtkZWZhdWx0VmFsdWU6XCIwcHggMHB4IDBweCAwcHggIzk5OVwiLHByZWZpeDohMCxjb2xvcjohMCxtdWx0aTohMCxrZXl3b3JkOlwiaW5zZXRcIn0pLG1lKFwiYm9yZGVyUmFkaXVzXCIse2RlZmF1bHRWYWx1ZTpcIjBweFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxuLGEpe2U9dGhpcy5mb3JtYXQoZSk7dmFyIG8sbCxoLHUsZixfLHAsYyxkLG0sZyx2LHksVCx3LHgsYj1bXCJib3JkZXJUb3BMZWZ0UmFkaXVzXCIsXCJib3JkZXJUb3BSaWdodFJhZGl1c1wiLFwiYm9yZGVyQm90dG9tUmlnaHRSYWRpdXNcIixcImJvcmRlckJvdHRvbUxlZnRSYWRpdXNcIl0sUD10LnN0eWxlO2ZvcihkPXBhcnNlRmxvYXQodC5vZmZzZXRXaWR0aCksbT1wYXJzZUZsb2F0KHQub2Zmc2V0SGVpZ2h0KSxvPWUuc3BsaXQoXCIgXCIpLGw9MDtiLmxlbmd0aD5sO2wrKyl0aGlzLnAuaW5kZXhPZihcImJvcmRlclwiKSYmKGJbbF09VihiW2xdKSksZj11PXEodCxiW2xdLHMsITEsXCIwcHhcIiksLTEhPT1mLmluZGV4T2YoXCIgXCIpJiYodT1mLnNwbGl0KFwiIFwiKSxmPXVbMF0sdT11WzFdKSxfPWg9b1tsXSxwPXBhcnNlRmxvYXQoZiksdj1mLnN1YnN0cigocCtcIlwiKS5sZW5ndGgpLHk9XCI9XCI9PT1fLmNoYXJBdCgxKSx5PyhjPXBhcnNlSW50KF8uY2hhckF0KDApK1wiMVwiLDEwKSxfPV8uc3Vic3RyKDIpLGMqPXBhcnNlRmxvYXQoXyksZz1fLnN1YnN0cigoYytcIlwiKS5sZW5ndGgtKDA+Yz8xOjApKXx8XCJcIik6KGM9cGFyc2VGbG9hdChfKSxnPV8uc3Vic3RyKChjK1wiXCIpLmxlbmd0aCkpLFwiXCI9PT1nJiYoZz1yW2ldfHx2KSxnIT09diYmKFQ9USh0LFwiYm9yZGVyTGVmdFwiLHAsdiksdz1RKHQsXCJib3JkZXJUb3BcIixwLHYpLFwiJVwiPT09Zz8oZj0xMDAqKFQvZCkrXCIlXCIsdT0xMDAqKHcvbSkrXCIlXCIpOlwiZW1cIj09PWc/KHg9USh0LFwiYm9yZGVyTGVmdFwiLDEsXCJlbVwiKSxmPVQveCtcImVtXCIsdT13L3grXCJlbVwiKTooZj1UK1wicHhcIix1PXcrXCJweFwiKSx5JiYoXz1wYXJzZUZsb2F0KGYpK2MrZyxoPXBhcnNlRmxvYXQodSkrYytnKSksYT1wZShQLGJbbF0sZitcIiBcIit1LF8rXCIgXCIraCwhMSxcIjBweFwiLGEpO3JldHVybiBhfSxwcmVmaXg6ITAsZm9ybWF0dGVyOmhlKFwiMHB4IDBweCAwcHggMHB4XCIsITEsITApfSksbWUoXCJiYWNrZ3JvdW5kUG9zaXRpb25cIix7ZGVmYXVsdFZhbHVlOlwiMCAwXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbyxsLGgsdSxmLF8scD1cImJhY2tncm91bmQtcG9zaXRpb25cIixkPXN8fEgodCxudWxsKSxtPXRoaXMuZm9ybWF0KChkP2M/ZC5nZXRQcm9wZXJ0eVZhbHVlKHArXCIteFwiKStcIiBcIitkLmdldFByb3BlcnR5VmFsdWUocCtcIi15XCIpOmQuZ2V0UHJvcGVydHlWYWx1ZShwKTp0LmN1cnJlbnRTdHlsZS5iYWNrZ3JvdW5kUG9zaXRpb25YK1wiIFwiK3QuY3VycmVudFN0eWxlLmJhY2tncm91bmRQb3NpdGlvblkpfHxcIjAgMFwiKSxnPXRoaXMuZm9ybWF0KGUpO2lmKC0xIT09bS5pbmRleE9mKFwiJVwiKSE9KC0xIT09Zy5pbmRleE9mKFwiJVwiKSkmJihfPXEodCxcImJhY2tncm91bmRJbWFnZVwiKS5yZXBsYWNlKEMsXCJcIiksXyYmXCJub25lXCIhPT1fKSl7Zm9yKG89bS5zcGxpdChcIiBcIiksbD1nLnNwbGl0KFwiIFwiKSxJLnNldEF0dHJpYnV0ZShcInNyY1wiLF8pLGg9MjstLWg+LTE7KW09b1toXSx1PS0xIT09bS5pbmRleE9mKFwiJVwiKSx1IT09KC0xIT09bFtoXS5pbmRleE9mKFwiJVwiKSkmJihmPTA9PT1oP3Qub2Zmc2V0V2lkdGgtSS53aWR0aDp0Lm9mZnNldEhlaWdodC1JLmhlaWdodCxvW2hdPXU/cGFyc2VGbG9hdChtKS8xMDAqZitcInB4XCI6MTAwKihwYXJzZUZsb2F0KG0pL2YpK1wiJVwiKTttPW8uam9pbihcIiBcIil9cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsbSxnLG4sYSl9LGZvcm1hdHRlcjplZX0pLG1lKFwiYmFja2dyb3VuZFNpemVcIix7ZGVmYXVsdFZhbHVlOlwiMCAwXCIsZm9ybWF0dGVyOmVlfSksbWUoXCJwZXJzcGVjdGl2ZVwiLHtkZWZhdWx0VmFsdWU6XCIwcHhcIixwcmVmaXg6ITB9KSxtZShcInBlcnNwZWN0aXZlT3JpZ2luXCIse2RlZmF1bHRWYWx1ZTpcIjUwJSA1MCVcIixwcmVmaXg6ITB9KSxtZShcInRyYW5zZm9ybVN0eWxlXCIse3ByZWZpeDohMH0pLG1lKFwiYmFja2ZhY2VWaXNpYmlsaXR5XCIse3ByZWZpeDohMH0pLG1lKFwidXNlclNlbGVjdFwiLHtwcmVmaXg6ITB9KSxtZShcIm1hcmdpblwiLHtwYXJzZXI6dWUoXCJtYXJnaW5Ub3AsbWFyZ2luUmlnaHQsbWFyZ2luQm90dG9tLG1hcmdpbkxlZnRcIil9KSxtZShcInBhZGRpbmdcIix7cGFyc2VyOnVlKFwicGFkZGluZ1RvcCxwYWRkaW5nUmlnaHQscGFkZGluZ0JvdHRvbSxwYWRkaW5nTGVmdFwiKX0pLG1lKFwiY2xpcFwiLHtkZWZhdWx0VmFsdWU6XCJyZWN0KDBweCwwcHgsMHB4LDBweClcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvLGwsaDtyZXR1cm4gOT5jPyhsPXQuY3VycmVudFN0eWxlLGg9OD5jP1wiIFwiOlwiLFwiLG89XCJyZWN0KFwiK2wuY2xpcFRvcCtoK2wuY2xpcFJpZ2h0K2grbC5jbGlwQm90dG9tK2grbC5jbGlwTGVmdCtcIilcIixlPXRoaXMuZm9ybWF0KGUpLnNwbGl0KFwiLFwiKS5qb2luKGgpKToobz10aGlzLmZvcm1hdChxKHQsdGhpcy5wLHMsITEsdGhpcy5kZmx0KSksZT10aGlzLmZvcm1hdChlKSksdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSxvLGUsbixhKX19KSxtZShcInRleHRTaGFkb3dcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IDBweCAwcHggIzk5OVwiLGNvbG9yOiEwLG11bHRpOiEwfSksbWUoXCJhdXRvUm91bmQsc3RyaWN0VW5pdHNcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIscyl7cmV0dXJuIHN9fSksbWUoXCJib3JkZXJcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IHNvbGlkICMwMDBcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3JldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLHRoaXMuZm9ybWF0KHEodCxcImJvcmRlclRvcFdpZHRoXCIscywhMSxcIjBweFwiKStcIiBcIitxKHQsXCJib3JkZXJUb3BTdHlsZVwiLHMsITEsXCJzb2xpZFwiKStcIiBcIitxKHQsXCJib3JkZXJUb3BDb2xvclwiLHMsITEsXCIjMDAwXCIpKSx0aGlzLmZvcm1hdChlKSxuLGEpfSxjb2xvcjohMCxmb3JtYXR0ZXI6ZnVuY3Rpb24odCl7dmFyIGU9dC5zcGxpdChcIiBcIik7cmV0dXJuIGVbMF0rXCIgXCIrKGVbMV18fFwic29saWRcIikrXCIgXCIrKHQubWF0Y2gobGUpfHxbXCIjMDAwXCJdKVswXX19KSxtZShcImJvcmRlcldpZHRoXCIse3BhcnNlcjp1ZShcImJvcmRlclRvcFdpZHRoLGJvcmRlclJpZ2h0V2lkdGgsYm9yZGVyQm90dG9tV2lkdGgsYm9yZGVyTGVmdFdpZHRoXCIpfSksbWUoXCJmbG9hdCxjc3NGbG9hdCxzdHlsZUZsb2F0XCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuPXQuc3R5bGUsYT1cImNzc0Zsb2F0XCJpbiBuP1wiY3NzRmxvYXRcIjpcInN0eWxlRmxvYXRcIjtyZXR1cm4gbmV3IF9lKG4sYSwwLDAscywtMSxpLCExLDAsblthXSxlKX19KTt2YXIga2U9ZnVuY3Rpb24odCl7dmFyIGUsaT10aGlzLnQscj1pLmZpbHRlcnx8cSh0aGlzLmRhdGEsXCJmaWx0ZXJcIikscz0wfHRoaXMucyt0aGlzLmMqdDsxMDA9PT1zJiYoLTE9PT1yLmluZGV4T2YoXCJhdHJpeChcIikmJi0xPT09ci5pbmRleE9mKFwicmFkaWVudChcIikmJi0xPT09ci5pbmRleE9mKFwib2FkZXIoXCIpPyhpLnJlbW92ZUF0dHJpYnV0ZShcImZpbHRlclwiKSxlPSFxKHRoaXMuZGF0YSxcImZpbHRlclwiKSk6KGkuZmlsdGVyPXIucmVwbGFjZSh4LFwiXCIpLGU9ITApKSxlfHwodGhpcy54bjEmJihpLmZpbHRlcj1yPXJ8fFwiYWxwaGEob3BhY2l0eT1cIitzK1wiKVwiKSwtMT09PXIuaW5kZXhPZihcInBhY2l0eVwiKT8wPT09cyYmdGhpcy54bjF8fChpLmZpbHRlcj1yK1wiIGFscGhhKG9wYWNpdHk9XCIrcytcIilcIik6aS5maWx0ZXI9ci5yZXBsYWNlKFQsXCJvcGFjaXR5PVwiK3MpKX07bWUoXCJvcGFjaXR5LGFscGhhLGF1dG9BbHBoYVwiLHtkZWZhdWx0VmFsdWU6XCIxXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbz1wYXJzZUZsb2F0KHEodCxcIm9wYWNpdHlcIixzLCExLFwiMVwiKSksbD10LnN0eWxlLGg9XCJhdXRvQWxwaGFcIj09PWk7cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIGUmJlwiPVwiPT09ZS5jaGFyQXQoMSkmJihlPShcIi1cIj09PWUuY2hhckF0KDApPy0xOjEpKnBhcnNlRmxvYXQoZS5zdWJzdHIoMikpK28pLGgmJjE9PT1vJiZcImhpZGRlblwiPT09cSh0LFwidmlzaWJpbGl0eVwiLHMpJiYwIT09ZSYmKG89MCksWT9uPW5ldyBfZShsLFwib3BhY2l0eVwiLG8sZS1vLG4pOihuPW5ldyBfZShsLFwib3BhY2l0eVwiLDEwMCpvLDEwMCooZS1vKSxuKSxuLnhuMT1oPzE6MCxsLnpvb209MSxuLnR5cGU9MixuLmI9XCJhbHBoYShvcGFjaXR5PVwiK24ucytcIilcIixuLmU9XCJhbHBoYShvcGFjaXR5PVwiKyhuLnMrbi5jKStcIilcIixuLmRhdGE9dCxuLnBsdWdpbj1hLG4uc2V0UmF0aW89a2UpLGgmJihuPW5ldyBfZShsLFwidmlzaWJpbGl0eVwiLDAsMCxuLC0xLG51bGwsITEsMCwwIT09bz9cImluaGVyaXRcIjpcImhpZGRlblwiLDA9PT1lP1wiaGlkZGVuXCI6XCJpbmhlcml0XCIpLG4ueHMwPVwiaW5oZXJpdFwiLHIuX292ZXJ3cml0ZVByb3BzLnB1c2gobi5uKSxyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKGkpKSxufX0pO3ZhciBBZT1mdW5jdGlvbih0LGUpe2UmJih0LnJlbW92ZVByb3BlcnR5PyhcIm1zXCI9PT1lLnN1YnN0cigwLDIpJiYoZT1cIk1cIitlLnN1YnN0cigxKSksdC5yZW1vdmVQcm9wZXJ0eShlLnJlcGxhY2UoUCxcIi0kMVwiKS50b0xvd2VyQ2FzZSgpKSk6dC5yZW1vdmVBdHRyaWJ1dGUoZSkpfSxPZT1mdW5jdGlvbih0KXtpZih0aGlzLnQuX2dzQ2xhc3NQVD10aGlzLDE9PT10fHwwPT09dCl7dGhpcy50LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsMD09PXQ/dGhpcy5iOnRoaXMuZSk7Zm9yKHZhciBlPXRoaXMuZGF0YSxpPXRoaXMudC5zdHlsZTtlOyllLnY/aVtlLnBdPWUudjpBZShpLGUucCksZT1lLl9uZXh0OzE9PT10JiZ0aGlzLnQuX2dzQ2xhc3NQVD09PXRoaXMmJih0aGlzLnQuX2dzQ2xhc3NQVD1udWxsKX1lbHNlIHRoaXMudC5nZXRBdHRyaWJ1dGUoXCJjbGFzc1wiKSE9PXRoaXMuZSYmdGhpcy50LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsdGhpcy5lKX07bWUoXCJjbGFzc05hbWVcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxyLG4sYSxvLGwpe3ZhciBoLHUsZixfLHAsYz10LmdldEF0dHJpYnV0ZShcImNsYXNzXCIpfHxcIlwiLGQ9dC5zdHlsZS5jc3NUZXh0O2lmKGE9bi5fY2xhc3NOYW1lUFQ9bmV3IF9lKHQsciwwLDAsYSwyKSxhLnNldFJhdGlvPU9lLGEucHI9LTExLGk9ITAsYS5iPWMsdT0kKHQscyksZj10Ll9nc0NsYXNzUFQpe2ZvcihfPXt9LHA9Zi5kYXRhO3A7KV9bcC5wXT0xLHA9cC5fbmV4dDtmLnNldFJhdGlvKDEpfXJldHVybiB0Ll9nc0NsYXNzUFQ9YSxhLmU9XCI9XCIhPT1lLmNoYXJBdCgxKT9lOmMucmVwbGFjZShSZWdFeHAoXCJcXFxccypcXFxcYlwiK2Uuc3Vic3RyKDIpK1wiXFxcXGJcIiksXCJcIikrKFwiK1wiPT09ZS5jaGFyQXQoMCk/XCIgXCIrZS5zdWJzdHIoMik6XCJcIiksbi5fdHdlZW4uX2R1cmF0aW9uJiYodC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLGEuZSksaD1HKHQsdSwkKHQpLGwsXyksdC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLGMpLGEuZGF0YT1oLmZpcnN0TVBULHQuc3R5bGUuY3NzVGV4dD1kLGE9YS54Zmlyc3Q9bi5wYXJzZSh0LGguZGlmcyxhLG8pKSxhfX0pO3ZhciBEZT1mdW5jdGlvbih0KXtpZigoMT09PXR8fDA9PT10KSYmdGhpcy5kYXRhLl90b3RhbFRpbWU9PT10aGlzLmRhdGEuX3RvdGFsRHVyYXRpb24mJlwiaXNGcm9tU3RhcnRcIiE9PXRoaXMuZGF0YS5kYXRhKXt2YXIgZSxpLHIscyxuPXRoaXMudC5zdHlsZSxhPW8udHJhbnNmb3JtLnBhcnNlO2lmKFwiYWxsXCI9PT10aGlzLmUpbi5jc3NUZXh0PVwiXCIscz0hMDtlbHNlIGZvcihlPXRoaXMuZS5zcGxpdChcIixcIikscj1lLmxlbmd0aDstLXI+LTE7KWk9ZVtyXSxvW2ldJiYob1tpXS5wYXJzZT09PWE/cz0hMDppPVwidHJhbnNmb3JtT3JpZ2luXCI9PT1pP3dlOm9baV0ucCksQWUobixpKTtzJiYoQWUobix5ZSksdGhpcy50Ll9nc1RyYW5zZm9ybSYmZGVsZXRlIHRoaXMudC5fZ3NUcmFuc2Zvcm0pfX07Zm9yKG1lKFwiY2xlYXJQcm9wc1wiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLHIscyxuKXtyZXR1cm4gbj1uZXcgX2UodCxyLDAsMCxuLDIpLG4uc2V0UmF0aW89RGUsbi5lPWUsbi5wcj0tMTAsbi5kYXRhPXMuX3R3ZWVuLGk9ITAsbn19KSxsPVwiYmV6aWVyLHRocm93UHJvcHMscGh5c2ljc1Byb3BzLHBoeXNpY3MyRFwiLnNwbGl0KFwiLFwiKSxjZT1sLmxlbmd0aDtjZS0tOylnZShsW2NlXSk7bD1hLnByb3RvdHlwZSxsLl9maXJzdFBUPW51bGwsbC5fb25Jbml0VHdlZW49ZnVuY3Rpb24odCxlLG8pe2lmKCF0Lm5vZGVUeXBlKXJldHVybiExO3RoaXMuX3RhcmdldD10LHRoaXMuX3R3ZWVuPW8sdGhpcy5fdmFycz1lLGg9ZS5hdXRvUm91bmQsaT0hMSxyPWUuc3VmZml4TWFwfHxhLnN1ZmZpeE1hcCxzPUgodCxcIlwiKSxuPXRoaXMuX292ZXJ3cml0ZVByb3BzO3ZhciBsLF8sYyxkLG0sZyx2LHksVCx4PXQuc3R5bGU7aWYodSYmXCJcIj09PXguekluZGV4JiYobD1xKHQsXCJ6SW5kZXhcIixzKSwoXCJhdXRvXCI9PT1sfHxcIlwiPT09bCkmJnRoaXMuX2FkZExhenlTZXQoeCxcInpJbmRleFwiLDApKSxcInN0cmluZ1wiPT10eXBlb2YgZSYmKGQ9eC5jc3NUZXh0LGw9JCh0LHMpLHguY3NzVGV4dD1kK1wiO1wiK2UsbD1HKHQsbCwkKHQpKS5kaWZzLCFZJiZ3LnRlc3QoZSkmJihsLm9wYWNpdHk9cGFyc2VGbG9hdChSZWdFeHAuJDEpKSxlPWwseC5jc3NUZXh0PWQpLHRoaXMuX2ZpcnN0UFQ9Xz10aGlzLnBhcnNlKHQsZSxudWxsKSx0aGlzLl90cmFuc2Zvcm1UeXBlKXtmb3IoVD0zPT09dGhpcy5fdHJhbnNmb3JtVHlwZSx5ZT9mJiYodT0hMCxcIlwiPT09eC56SW5kZXgmJih2PXEodCxcInpJbmRleFwiLHMpLChcImF1dG9cIj09PXZ8fFwiXCI9PT12KSYmdGhpcy5fYWRkTGF6eVNldCh4LFwiekluZGV4XCIsMCkpLHAmJnRoaXMuX2FkZExhenlTZXQoeCxcIldlYmtpdEJhY2tmYWNlVmlzaWJpbGl0eVwiLHRoaXMuX3ZhcnMuV2Via2l0QmFja2ZhY2VWaXNpYmlsaXR5fHwoVD9cInZpc2libGVcIjpcImhpZGRlblwiKSkpOnguem9vbT0xLGM9XztjJiZjLl9uZXh0OyljPWMuX25leHQ7eT1uZXcgX2UodCxcInRyYW5zZm9ybVwiLDAsMCxudWxsLDIpLHRoaXMuX2xpbmtDU1NQKHksbnVsbCxjKSx5LnNldFJhdGlvPVQmJnhlP0NlOnllP1JlOlNlLHkuZGF0YT10aGlzLl90cmFuc2Zvcm18fFBlKHQscywhMCksbi5wb3AoKX1pZihpKXtmb3IoO187KXtmb3IoZz1fLl9uZXh0LGM9ZDtjJiZjLnByPl8ucHI7KWM9Yy5fbmV4dDsoXy5fcHJldj1jP2MuX3ByZXY6bSk/Xy5fcHJldi5fbmV4dD1fOmQ9XywoXy5fbmV4dD1jKT9jLl9wcmV2PV86bT1fLF89Z310aGlzLl9maXJzdFBUPWR9cmV0dXJuITB9LGwucGFyc2U9ZnVuY3Rpb24odCxlLGksbil7dmFyIGEsbCx1LGYsXyxwLGMsZCxtLGcsdj10LnN0eWxlO2ZvcihhIGluIGUpcD1lW2FdLGw9b1thXSxsP2k9bC5wYXJzZSh0LHAsYSx0aGlzLGksbixlKTooXz1xKHQsYSxzKStcIlwiLG09XCJzdHJpbmdcIj09dHlwZW9mIHAsXCJjb2xvclwiPT09YXx8XCJmaWxsXCI9PT1hfHxcInN0cm9rZVwiPT09YXx8LTEhPT1hLmluZGV4T2YoXCJDb2xvclwiKXx8bSYmYi50ZXN0KHApPyhtfHwocD1vZShwKSxwPShwLmxlbmd0aD4zP1wicmdiYShcIjpcInJnYihcIikrcC5qb2luKFwiLFwiKStcIilcIiksaT1wZSh2LGEsXyxwLCEwLFwidHJhbnNwYXJlbnRcIixpLDAsbikpOiFtfHwtMT09PXAuaW5kZXhPZihcIiBcIikmJi0xPT09cC5pbmRleE9mKFwiLFwiKT8odT1wYXJzZUZsb2F0KF8pLGM9dXx8MD09PXU/Xy5zdWJzdHIoKHUrXCJcIikubGVuZ3RoKTpcIlwiLChcIlwiPT09X3x8XCJhdXRvXCI9PT1fKSYmKFwid2lkdGhcIj09PWF8fFwiaGVpZ2h0XCI9PT1hPyh1PXRlKHQsYSxzKSxjPVwicHhcIik6XCJsZWZ0XCI9PT1hfHxcInRvcFwiPT09YT8odT1aKHQsYSxzKSxjPVwicHhcIik6KHU9XCJvcGFjaXR5XCIhPT1hPzA6MSxjPVwiXCIpKSxnPW0mJlwiPVwiPT09cC5jaGFyQXQoMSksZz8oZj1wYXJzZUludChwLmNoYXJBdCgwKStcIjFcIiwxMCkscD1wLnN1YnN0cigyKSxmKj1wYXJzZUZsb2F0KHApLGQ9cC5yZXBsYWNlKHksXCJcIikpOihmPXBhcnNlRmxvYXQocCksZD1tP3Auc3Vic3RyKChmK1wiXCIpLmxlbmd0aCl8fFwiXCI6XCJcIiksXCJcIj09PWQmJihkPWEgaW4gcj9yW2FdOmMpLHA9Znx8MD09PWY/KGc/Zit1OmYpK2Q6ZVthXSxjIT09ZCYmXCJcIiE9PWQmJihmfHwwPT09ZikmJnUmJih1PVEodCxhLHUsYyksXCIlXCI9PT1kPyh1Lz1RKHQsYSwxMDAsXCIlXCIpLzEwMCxlLnN0cmljdFVuaXRzIT09ITAmJihfPXUrXCIlXCIpKTpcImVtXCI9PT1kP3UvPVEodCxhLDEsXCJlbVwiKTpcInB4XCIhPT1kJiYoZj1RKHQsYSxmLGQpLGQ9XCJweFwiKSxnJiYoZnx8MD09PWYpJiYocD1mK3UrZCkpLGcmJihmKz11KSwhdSYmMCE9PXV8fCFmJiYwIT09Zj92b2lkIDAhPT12W2FdJiYocHx8XCJOYU5cIiE9cCtcIlwiJiZudWxsIT1wKT8oaT1uZXcgX2UodixhLGZ8fHV8fDAsMCxpLC0xLGEsITEsMCxfLHApLGkueHMwPVwibm9uZVwiIT09cHx8XCJkaXNwbGF5XCIhPT1hJiYtMT09PWEuaW5kZXhPZihcIlN0eWxlXCIpP3A6Xyk6VShcImludmFsaWQgXCIrYStcIiB0d2VlbiB2YWx1ZTogXCIrZVthXSk6KGk9bmV3IF9lKHYsYSx1LGYtdSxpLDAsYSxoIT09ITEmJihcInB4XCI9PT1kfHxcInpJbmRleFwiPT09YSksMCxfLHApLGkueHMwPWQpKTppPXBlKHYsYSxfLHAsITAsbnVsbCxpLDAsbikpLG4mJmkmJiFpLnBsdWdpbiYmKGkucGx1Z2luPW4pO3JldHVybiBpfSxsLnNldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzPXRoaXMuX2ZpcnN0UFQsbj0xZS02O2lmKDEhPT10fHx0aGlzLl90d2Vlbi5fdGltZSE9PXRoaXMuX3R3ZWVuLl9kdXJhdGlvbiYmMCE9PXRoaXMuX3R3ZWVuLl90aW1lKWlmKHR8fHRoaXMuX3R3ZWVuLl90aW1lIT09dGhpcy5fdHdlZW4uX2R1cmF0aW9uJiYwIT09dGhpcy5fdHdlZW4uX3RpbWV8fHRoaXMuX3R3ZWVuLl9yYXdQcmV2VGltZT09PS0xZS02KWZvcig7czspe2lmKGU9cy5jKnQrcy5zLHMucj9lPU1hdGgucm91bmQoZSk6bj5lJiZlPi1uJiYoZT0wKSxzLnR5cGUpaWYoMT09PXMudHlwZSlpZihyPXMubCwyPT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyO2Vsc2UgaWYoMz09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMztlbHNlIGlmKDQ9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czMrcy54bjMrcy54czQ7ZWxzZSBpZig1PT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzK3MueG4zK3MueHM0K3MueG40K3MueHM1O2Vsc2V7Zm9yKGk9cy54czArZStzLnhzMSxyPTE7cy5sPnI7cisrKWkrPXNbXCJ4blwiK3JdK3NbXCJ4c1wiKyhyKzEpXTtzLnRbcy5wXT1pfWVsc2UtMT09PXMudHlwZT9zLnRbcy5wXT1zLnhzMDpzLnNldFJhdGlvJiZzLnNldFJhdGlvKHQpO2Vsc2Ugcy50W3MucF09ZStzLnhzMDtzPXMuX25leHR9ZWxzZSBmb3IoO3M7KTIhPT1zLnR5cGU/cy50W3MucF09cy5iOnMuc2V0UmF0aW8odCkscz1zLl9uZXh0O2Vsc2UgZm9yKDtzOykyIT09cy50eXBlP3MudFtzLnBdPXMuZTpzLnNldFJhdGlvKHQpLHM9cy5fbmV4dH0sbC5fZW5hYmxlVHJhbnNmb3Jtcz1mdW5jdGlvbih0KXt0aGlzLl90cmFuc2Zvcm1UeXBlPXR8fDM9PT10aGlzLl90cmFuc2Zvcm1UeXBlPzM6Mix0aGlzLl90cmFuc2Zvcm09dGhpcy5fdHJhbnNmb3JtfHxQZSh0aGlzLl90YXJnZXQscywhMCl9O3ZhciBNZT1mdW5jdGlvbigpe3RoaXMudFt0aGlzLnBdPXRoaXMuZSx0aGlzLmRhdGEuX2xpbmtDU1NQKHRoaXMsdGhpcy5fbmV4dCxudWxsLCEwKX07bC5fYWRkTGF6eVNldD1mdW5jdGlvbih0LGUsaSl7dmFyIHI9dGhpcy5fZmlyc3RQVD1uZXcgX2UodCxlLDAsMCx0aGlzLl9maXJzdFBULDIpO3IuZT1pLHIuc2V0UmF0aW89TWUsci5kYXRhPXRoaXN9LGwuX2xpbmtDU1NQPWZ1bmN0aW9uKHQsZSxpLHIpe3JldHVybiB0JiYoZSYmKGUuX3ByZXY9dCksdC5fbmV4dCYmKHQuX25leHQuX3ByZXY9dC5fcHJldiksdC5fcHJldj90Ll9wcmV2Ll9uZXh0PXQuX25leHQ6dGhpcy5fZmlyc3RQVD09PXQmJih0aGlzLl9maXJzdFBUPXQuX25leHQscj0hMCksaT9pLl9uZXh0PXQ6cnx8bnVsbCE9PXRoaXMuX2ZpcnN0UFR8fCh0aGlzLl9maXJzdFBUPXQpLHQuX25leHQ9ZSx0Ll9wcmV2PWkpLHR9LGwuX2tpbGw9ZnVuY3Rpb24oZSl7dmFyIGkscixzLG49ZTtpZihlLmF1dG9BbHBoYXx8ZS5hbHBoYSl7bj17fTtmb3IociBpbiBlKW5bcl09ZVtyXTtuLm9wYWNpdHk9MSxuLmF1dG9BbHBoYSYmKG4udmlzaWJpbGl0eT0xKX1yZXR1cm4gZS5jbGFzc05hbWUmJihpPXRoaXMuX2NsYXNzTmFtZVBUKSYmKHM9aS54Zmlyc3QscyYmcy5fcHJldj90aGlzLl9saW5rQ1NTUChzLl9wcmV2LGkuX25leHQscy5fcHJldi5fcHJldik6cz09PXRoaXMuX2ZpcnN0UFQmJih0aGlzLl9maXJzdFBUPWkuX25leHQpLGkuX25leHQmJnRoaXMuX2xpbmtDU1NQKGkuX25leHQsaS5fbmV4dC5fbmV4dCxzLl9wcmV2KSx0aGlzLl9jbGFzc05hbWVQVD1udWxsKSx0LnByb3RvdHlwZS5fa2lsbC5jYWxsKHRoaXMsbil9O3ZhciBMZT1mdW5jdGlvbih0LGUsaSl7dmFyIHIscyxuLGE7aWYodC5zbGljZSlmb3Iocz10Lmxlbmd0aDstLXM+LTE7KUxlKHRbc10sZSxpKTtlbHNlIGZvcihyPXQuY2hpbGROb2RlcyxzPXIubGVuZ3RoOy0tcz4tMTspbj1yW3NdLGE9bi50eXBlLG4uc3R5bGUmJihlLnB1c2goJChuKSksaSYmaS5wdXNoKG4pKSwxIT09YSYmOSE9PWEmJjExIT09YXx8IW4uY2hpbGROb2Rlcy5sZW5ndGh8fExlKG4sZSxpKX07cmV0dXJuIGEuY2FzY2FkZVRvPWZ1bmN0aW9uKHQsaSxyKXt2YXIgcyxuLGEsbz1lLnRvKHQsaSxyKSxsPVtvXSxoPVtdLHU9W10sZj1bXSxfPWUuX2ludGVybmFscy5yZXNlcnZlZFByb3BzO2Zvcih0PW8uX3RhcmdldHN8fG8udGFyZ2V0LExlKHQsaCxmKSxvLnJlbmRlcihpLCEwKSxMZSh0LHUpLG8ucmVuZGVyKDAsITApLG8uX2VuYWJsZWQoITApLHM9Zi5sZW5ndGg7LS1zPi0xOylpZihuPUcoZltzXSxoW3NdLHVbc10pLG4uZmlyc3RNUFQpe249bi5kaWZzO1xyXG5mb3IoYSBpbiByKV9bYV0mJihuW2FdPXJbYV0pO2wucHVzaChlLnRvKGZbc10saSxuKSl9cmV0dXJuIGx9LHQuYWN0aXZhdGUoW2FdKSxhfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS43LjNcclxuICogREFURTogMjAxNC0wMS0xNFxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICoqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3ZhciB0PWRvY3VtZW50LmRvY3VtZW50RWxlbWVudCxlPXdpbmRvdyxpPWZ1bmN0aW9uKGkscyl7dmFyIHI9XCJ4XCI9PT1zP1wiV2lkdGhcIjpcIkhlaWdodFwiLG49XCJzY3JvbGxcIityLGE9XCJjbGllbnRcIityLG89ZG9jdW1lbnQuYm9keTtyZXR1cm4gaT09PWV8fGk9PT10fHxpPT09bz9NYXRoLm1heCh0W25dLG9bbl0pLShlW1wiaW5uZXJcIityXXx8TWF0aC5tYXgodFthXSxvW2FdKSk6aVtuXS1pW1wib2Zmc2V0XCIrcl19LHM9d2luZG93Ll9nc0RlZmluZS5wbHVnaW4oe3Byb3BOYW1lOlwic2Nyb2xsVG9cIixBUEk6Mix2ZXJzaW9uOlwiMS43LjNcIixpbml0OmZ1bmN0aW9uKHQscyxyKXtyZXR1cm4gdGhpcy5fd2R3PXQ9PT1lLHRoaXMuX3RhcmdldD10LHRoaXMuX3R3ZWVuPXIsXCJvYmplY3RcIiE9dHlwZW9mIHMmJihzPXt5OnN9KSx0aGlzLl9hdXRvS2lsbD1zLmF1dG9LaWxsIT09ITEsdGhpcy54PXRoaXMueFByZXY9dGhpcy5nZXRYKCksdGhpcy55PXRoaXMueVByZXY9dGhpcy5nZXRZKCksbnVsbCE9cy54Pyh0aGlzLl9hZGRUd2Vlbih0aGlzLFwieFwiLHRoaXMueCxcIm1heFwiPT09cy54P2kodCxcInhcIik6cy54LFwic2Nyb2xsVG9feFwiLCEwKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5wdXNoKFwic2Nyb2xsVG9feFwiKSk6dGhpcy5za2lwWD0hMCxudWxsIT1zLnk/KHRoaXMuX2FkZFR3ZWVuKHRoaXMsXCJ5XCIsdGhpcy55LFwibWF4XCI9PT1zLnk/aSh0LFwieVwiKTpzLnksXCJzY3JvbGxUb195XCIsITApLHRoaXMuX292ZXJ3cml0ZVByb3BzLnB1c2goXCJzY3JvbGxUb195XCIpKTp0aGlzLnNraXBZPSEwLCEwfSxzZXQ6ZnVuY3Rpb24odCl7dGhpcy5fc3VwZXIuc2V0UmF0aW8uY2FsbCh0aGlzLHQpO3ZhciBzPXRoaXMuX3dkd3x8IXRoaXMuc2tpcFg/dGhpcy5nZXRYKCk6dGhpcy54UHJldixyPXRoaXMuX3dkd3x8IXRoaXMuc2tpcFk/dGhpcy5nZXRZKCk6dGhpcy55UHJldixuPXItdGhpcy55UHJldixhPXMtdGhpcy54UHJldjt0aGlzLl9hdXRvS2lsbCYmKCF0aGlzLnNraXBYJiYoYT43fHwtNz5hKSYmaSh0aGlzLl90YXJnZXQsXCJ4XCIpPnMmJih0aGlzLnNraXBYPSEwKSwhdGhpcy5za2lwWSYmKG4+N3x8LTc+bikmJmkodGhpcy5fdGFyZ2V0LFwieVwiKT5yJiYodGhpcy5za2lwWT0hMCksdGhpcy5za2lwWCYmdGhpcy5za2lwWSYmdGhpcy5fdHdlZW4ua2lsbCgpKSx0aGlzLl93ZHc/ZS5zY3JvbGxUbyh0aGlzLnNraXBYP3M6dGhpcy54LHRoaXMuc2tpcFk/cjp0aGlzLnkpOih0aGlzLnNraXBZfHwodGhpcy5fdGFyZ2V0LnNjcm9sbFRvcD10aGlzLnkpLHRoaXMuc2tpcFh8fCh0aGlzLl90YXJnZXQuc2Nyb2xsTGVmdD10aGlzLngpKSx0aGlzLnhQcmV2PXRoaXMueCx0aGlzLnlQcmV2PXRoaXMueX19KSxyPXMucHJvdG90eXBlO3MubWF4PWksci5nZXRYPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3dkdz9udWxsIT1lLnBhZ2VYT2Zmc2V0P2UucGFnZVhPZmZzZXQ6bnVsbCE9dC5zY3JvbGxMZWZ0P3Quc2Nyb2xsTGVmdDpkb2N1bWVudC5ib2R5LnNjcm9sbExlZnQ6dGhpcy5fdGFyZ2V0LnNjcm9sbExlZnR9LHIuZ2V0WT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl93ZHc/bnVsbCE9ZS5wYWdlWU9mZnNldD9lLnBhZ2VZT2Zmc2V0Om51bGwhPXQuc2Nyb2xsVG9wP3Quc2Nyb2xsVG9wOmRvY3VtZW50LmJvZHkuc2Nyb2xsVG9wOnRoaXMuX3RhcmdldC5zY3JvbGxUb3B9LHIuX2tpbGw9ZnVuY3Rpb24odCl7cmV0dXJuIHQuc2Nyb2xsVG9feCYmKHRoaXMuc2tpcFg9ITApLHQuc2Nyb2xsVG9feSYmKHRoaXMuc2tpcFk9ITApLHRoaXMuX3N1cGVyLl9raWxsLmNhbGwodGhpcyx0KX19KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiXX0=
