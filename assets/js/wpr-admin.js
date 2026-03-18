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
    var excluded = ['cloudflare_auto_settings', 'cloudflare_devmode', 'analytics_enabled'];
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

  // Track "See Report" clicks in Rocket Insights.
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
require("../global/pageManager.js");
require("../global/main.js");
require("../global/fields.js");
require("../global/beacon.js");
require("../global/ajax.js");
require("../global/recommendations-widget.js");
require("../global/rocketcdn.js");
require("../global/countdown.js");
require("../global/mixpanel.js");

},{"../global/ajax.js":2,"../global/beacon.js":4,"../global/countdown.js":5,"../global/fields.js":6,"../global/main.js":7,"../global/mixpanel.js":8,"../global/pageManager.js":9,"../global/recommendations-widget.js":10,"../global/rocketcdn.js":11,"../lib/greensock/TimelineLite.min.js":12,"../lib/greensock/TweenLite.min.js":13,"../lib/greensock/easing/EasePack.min.js":14,"../lib/greensock/plugins/CSSPlugin.min.js":15,"../lib/greensock/plugins/ScrollToPlugin.min.js":16}],4:[function(require,module,exports){
"use strict";

var $ = jQuery;
$(document).ready(function () {
  if ('Beacon' in window) {
    /**
     * Show beacons on button "help" click
     */
    var $help = $('.wpr-infoAction--help');
    $help.on('click', function (e) {
      var ids = $(this).data('beacon-id');
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
    wprTrackHelpButton('rocket insights see report', 'performance summary');
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
        'path': rocket_mixpanel_data.path
      });
    }
  }

  // Make function globally available
  window.wprTrackHelpButton = wprTrackHelpButton;
});

},{}],5:[function(require,module,exports){
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

},{}],6:[function(require,module,exports){
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
    $(this).parent().slideUp('slow', function () {
      $(this).remove();
    });
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

},{"../custom/custom-select.js":1}],7:[function(require,module,exports){
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
          'path': rocket_mixpanel_data.path
        });
      }
    }
  });
});

},{}],8:[function(require,module,exports){
"use strict";

function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == typeof i ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != typeof t || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != typeof i) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
class RocketMixpanel {
  constructor(config) {
    _defineProperty(this, "trackedTabs", ['dashboard', 'rocket_insights']);
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
      context: rocket_mixpanel_data.context
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

},{}],9:[function(require,module,exports){
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

  // Exception for dashboard
  if (this.pageId == "dashboard") {
    this.$sidebar.style.display = 'none';
    this.$tips.style.display = 'none';
    this.$content.classList.remove('isNotFull');
  }
  if (this.pageId == "imagify") {
    this.$sidebar.style.display = 'none';
    this.$tips.style.display = 'none';
  }
  if (pagesWithoutSubmit.includes(this.pageId)) {
    this.$submitButton.style.display = 'none';
  }
};

},{}],10:[function(require,module,exports){
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

},{}],11:[function(require,module,exports){
"use strict";

/*eslint-env es6, browser*/
/* global MicroModal, mixpanel, rocket_mixpanel_data, rocket_ajax_data, ajaxurl */
((document, window) => {
  'use strict';

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

    // Only auto-open modal if there's no direct button URL
    if (!window.rocketcdnButtonUrl || window.rocketcdnButtonUrl === '') {
      maybeOpenModal();
      maybeOpenModalFromURL();
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

  /**
   * Tracks when a visible RocketCDN upsell banner is viewed.
   * Only tracks once per page session.
   */
  let bannerViewTracked = false;
  function maybeTrackBannerView() {
    if (bannerViewTracked || !isOnCDNTab()) {
      return;
    }
    const smallCTA = document.querySelector('#wpr-rocketcdn-cta-small');
    const bigCTA = document.querySelector('#wpr-rocketcdn-cta');

    // Only track if one of the banners is visible.
    if (bigCTA && !bigCTA.classList.contains('wpr-isHidden')) {
      trackRocketCDNUpsellBannerViewed();
      bannerViewTracked = true;
    } else if (smallCTA && !smallCTA.classList.contains('wpr-isHidden')) {
      trackRocketCDNUpsellBannerViewed();
      bannerViewTracked = true;
    }
  }
  window.addEventListener('load', () => {
    let openCTA = document.querySelector('#wpr-rocketcdn-open-cta'),
      closeCTA = document.querySelector('#wpr-rocketcdn-close-cta'),
      smallCTA = document.querySelector('#wpr-rocketcdn-cta-small'),
      bigCTA = document.querySelector('#wpr-rocketcdn-cta'),
      inputToggle = document.querySelector('.wpr-rocketcdn-toggle--input');

    // Track banner view on page load if banner is visible and user is on CDN tab.
    maybeTrackBannerView();

    // Track banner view when user navigates to CDN tab.
    window.addEventListener('hashchange', () => {
      maybeTrackBannerView();
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
    if (null !== openCTA && null !== smallCTA && null !== bigCTA) {
      openCTA.addEventListener('click', e => {
        e.preventDefault();
        smallCTA.classList.add('wpr-isHidden');
        bigCTA.classList.remove('wpr-isHidden');

        // Track upsell banner view only if on CDN tab.
        if (isOnCDNTab() && !bannerViewTracked) {
          trackRocketCDNUpsellBannerViewed();
          bannerViewTracked = true;
        }
        sendHTTPRequest(getPostData('big'));
      });
    }
    if (null !== closeCTA && null !== smallCTA && null !== bigCTA) {
      closeCTA.addEventListener('click', e => {
        e.preventDefault();
        smallCTA.classList.remove('wpr-isHidden');
        bigCTA.classList.add('wpr-isHidden');
        sendHTTPRequest(getPostData('small'));
      });
    }
    function getPostData(status) {
      let postData = '';
      postData += 'action=toggle_rocketcdn_cta';
      postData += '&status=' + status;
      postData += '&nonce=' + rocket_ajax_data.nonce;
      return postData;
    }

    // Display the correct prices on page based on billing cycle toggle state.
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
  function checkButtonUrlAndOpen(isCTA) {
    // Track CTA click if this is the pricing CTA button.
    if (isCTA) {
      trackRocketCDNUpsellCTAClicked();
    }

    // Check if button URL was injected by PHP
    if (window.rocketcdnButtonUrl && window.rocketcdnButtonUrl !== '') {
      // Small delay to ensure Mixpanel event is sent before navigation
      setTimeout(function () {
        window.location.href = window.rocketcdnButtonUrl;
      }, 100);
    } else {
      // Show iframe modal as usual
      MicroModal.show('wpr-rocketcdn-modal');
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
          MicroModal.show('wpr-rocketcdn-modal');
        }
      }
    };
  }
  function maybeOpenModalFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('rocketcdn_open_iframe') && '1' === urlParams.get('rocketcdn_open_iframe')) {
      // Set hash to page_cdn to show CDN tab behind modal
      window.location.hash = '#page_cdn';
      MicroModal.show('wpr-rocketcdn-modal');

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
      path: rocket_mixpanel_data.path
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
   */
  function trackRocketCDNUpsellBannerViewed() {
    trackRocketCDNUpsellMixpanelEvent('RocketCDN Upsell Banner Viewed');
  }

  /**
   * Tracks RocketCDN upsell CTA click with Mixpanel.
   */
  function trackRocketCDNUpsellCTAClicked() {
    trackRocketCDNUpsellMixpanelEvent('RocketCDN Upsell CTA Clicked');
  }
})(document, window);

},{}],12:[function(require,module,exports){
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

},{}],13:[function(require,module,exports){
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

},{}],14:[function(require,module,exports){
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

},{}],16:[function(require,module,exports){
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvbWl4cGFuZWwuanMiLCJzcmMvanMvZ2xvYmFsL3BhZ2VNYW5hZ2VyLmpzIiwic3JjL2pzL2dsb2JhbC9yZWNvbW1lbmRhdGlvbnMtd2lkZ2V0LmpzIiwic3JjL2pzL2dsb2JhbC9yb2NrZXRjZG4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9UaW1lbGluZUxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svVHdlZW5MaXRlLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL2Vhc2luZy9FYXNlUGFjay5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9wbHVnaW5zL0NTU1BsdWdpbi5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9wbHVnaW5zL1Njcm9sbFRvUGx1Z2luLm1pbi5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUEsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGdCQUFnQixDQUFDLENBQUMsT0FBTyxDQUFDLFlBQVksSUFBSTtFQUNuRSxNQUFNLFNBQVMsR0FBRyxZQUFZLENBQUMsYUFBYSxDQUFDLGdCQUFnQixDQUFDO0VBQzlELE1BQU0sYUFBYSxHQUFHLFlBQVksQ0FBQyxhQUFhLENBQUMsaUJBQWlCLENBQUM7RUFDbkUsTUFBTSxPQUFPLEdBQUcsU0FBQSxDQUFTLEdBQUcsRUFBRTtJQUM3QixNQUFNLGlCQUFpQixHQUFHLElBQUksV0FBVyxDQUFDLHNCQUFzQixFQUFF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QUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsaUJBQWlCLEtBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGFBQWEsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsUUFBUTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxVQUFVO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsT0FBTztNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsT0FBTztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1VBQUMsVUFBVSxFQUFDLENBQUM7VUFBQyxnQkFBZ0IsRUFBQyxDQUFDO1VBQUMsZUFBZSxFQUFDLENBQUM7VUFBQyxpQkFBaUIsRUFBQyxJQUFJLENBQUM7UUFBaUIsQ0FBQyxDQUFDO01BQUMsS0FBSSxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLGVBQWUsS0FBRyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLENBQUMsaUJBQWlCLEtBQUcsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7TUFBQyxLQUFJLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUUsQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxZQUFZLENBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxRQUFRLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7WUFBQyxNQUFNLEVBQUM7VUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLEtBQUcsVUFBVSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLE9BQU8sS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxVQUFVLElBQUUsT0FBTyxDQUFDLEVBQUMsTUFBSyxhQUFhLEdBQUMsQ0FBQyxHQUFDLHVFQUF1RTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxZQUFZLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztRQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sSUFBSTtNQUFBO01BQUMsT0FBTSxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxjQUFjLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7TUFBQyxJQUFHLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsWUFBVyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSTtRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLE9BQU8sSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxjQUFjO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFlBQVksRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsWUFBWSxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLG1CQUFtQixDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksSUFBRSxJQUFJLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksSUFBRSxDQUFDLENBQUMsTUFBSSxDQUFDLEdBQUMsbUJBQW1CLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFlBQVksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLENBQUMsRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsWUFBWSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFHLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUcsTUFBSSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLE1BQUksQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsa0JBQWtCLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQyxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRTtRQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsQ0FBQyxFQUFDLE9BQU0sQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUE7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLFVBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxDQUFDLFlBQVksQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRTtRQUFDLElBQUcsQ0FBQyxLQUFHLElBQUksRUFBQyxPQUFNLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFBO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO01BQUMsS0FBSSxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxHQUFHLEVBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksS0FBRyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDO1FBQUMsSUFBRyxJQUFJLENBQUMsTUFBTSxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsWUFBWSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixLQUFHLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLEVBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxJQUFJLENBQUMsY0FBYztNQUFBO01BQUMsT0FBTyxDQUFDLEtBQUcsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsbUJBQW1CO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFVBQVU7SUFBQSxDQUFDLEVBQUMsQ0FBQztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7Ozs7O0FDWHhyVDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxVQUFTLENBQUMsRUFBQztFQUFDLFlBQVk7O0VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixJQUFFLENBQUM7RUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQztNQUFDLENBQUMsR0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxZQUFVO1FBQUMsSUFBSSxDQUFDLEdBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxRQUFRO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDO1FBQUMsT0FBTyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsRUFBRTtRQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBRSxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztVQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxDQUFDLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLE1BQU0sSUFBRSxNQUFNLENBQUMsR0FBRyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsR0FBRyxHQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBQyxFQUFFLEVBQUMsWUFBVTtZQUFDLE9BQU8sQ0FBQztVQUFBLENBQUMsQ0FBQyxHQUFDLFdBQVcsSUFBRSxPQUFPLE1BQU0sSUFBRSxNQUFNLENBQUMsT0FBTyxLQUFHLE1BQU0sQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLFlBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsWUFBVTtVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSwwQkFBMEIsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUEsQ0FBQztJQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxFQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLENBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTTtRQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxRQUFRLEVBQUMsTUFBTSxFQUFDLE9BQU8sRUFBQyxPQUFPLEVBQUMsY0FBYyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxXQUFXLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFdBQVcsQ0FBQztJQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsU0FBUztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyx3QkFBd0IsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLElBQUUsSUFBSTtJQUFBLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxLQUFJLElBQUksSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLEVBQUUsRUFBQyxDQUFDO1FBQUMsRUFBRSxFQUFDO01BQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQztNQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxxQkFBcUI7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG9CQUFvQjtNQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLFlBQVU7UUFBQyxPQUFPLElBQUksSUFBSSxDQUFELENBQUMsQ0FBRSxPQUFPLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUksRUFBQyxLQUFLLEVBQUMsUUFBUSxFQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLHVCQUF1QixDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsc0JBQXNCLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLDZCQUE2QixDQUFDO0lBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLEdBQUMsR0FBRztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUM7WUFBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsS0FBSyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxNQUFNLENBQUM7UUFBQSxDQUFDO01BQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtRQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFlBQVU7UUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7UUFBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsRUFBRSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsSUFBRSxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxJQUFFLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsWUFBVTtRQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsSUFBSSxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBQyxlQUFlLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLE1BQU07SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxlQUFlLEtBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLENBQUMsTUFBTSxDQUFELENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVU7TUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFlBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtNQUFDLE9BQU0sQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLFFBQVEsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLEtBQUcsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtRQUFDLElBQUcsQ0FBQyxLQUFHLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsTUFBTTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEtBQUcsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxjQUFjO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsVUFBVTtNQUFDLElBQUcsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixFQUFDO1VBQUMsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsT0FBSyxDQUFDLENBQUMsU0FBUyxHQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO1FBQUE7UUFBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxVQUFVLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxNQUFJLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsYUFBYSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLElBQUksQ0FBQyxVQUFVO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxPQUFPLElBQUksQ0FBQyxVQUFVO01BQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxTQUFTLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsS0FBRyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLElBQUksQ0FBQyxTQUFTO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPO01BQUMsSUFBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsU0FBUyxFQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGlCQUFpQixLQUFHLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGtCQUFrQixHQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxJQUFJLEtBQUcsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLGFBQWEsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLE1BQU0sS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU07TUFBQyxLQUFJLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sTUFBSSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFlBQVU7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVTtJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLElBQUksSUFBRSxDQUFDLEVBQUMsTUFBSyw2QkFBNkI7UUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTO1FBQUMsSUFBRyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxHQUFDLEVBQUUsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBSSxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO1FBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsZUFBZSxLQUFHLENBQUMsQ0FBQyxNQUFJLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxJQUFFLFdBQVcsS0FBRyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxJQUFFLE9BQU8sS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyx1QkFBdUIsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLGNBQWMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUM7UUFBQyxTQUFTLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsZ0JBQWdCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDLENBQUM7UUFBQyxZQUFZLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxjQUFjLEVBQUMsQ0FBQztRQUFDLGFBQWEsRUFBQyxDQUFDO1FBQUMsT0FBTyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLFlBQVksRUFBQyxDQUFDO1FBQUMsaUJBQWlCLEVBQUMsQ0FBQztRQUFDLHVCQUF1QixFQUFDLENBQUM7UUFBQyxzQkFBc0IsRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxjQUFjLEVBQUMsQ0FBQztRQUFDLGFBQWEsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUM7UUFBQyxXQUFXLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUM7UUFBQyxRQUFRLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsR0FBRyxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxXQUFXLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsT0FBTyxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVU7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxZQUFVO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLEdBQUcsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQztVQUFDLE9BQUssQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztRQUFBO01BQUM7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsV0FBVyxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVTtRQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQztVQUFDLE1BQU0sRUFBQyxDQUFDO1VBQUMsTUFBTSxFQUFDO1FBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxPQUFLLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTTtNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLElBQUcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQztVQUFNLE9BQU8sQ0FBQztRQUFBO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxTQUFTO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsS0FBSyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBRTtVQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxPQUFNLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztRQUFBO1FBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtNQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sRUFBQztRQUFDLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDO01BQU0sQ0FBQyxNQUFLLElBQUcsQ0FBQyxDQUFDLFlBQVksSUFBRSxDQUFDLEtBQUcsQ0FBQyxFQUFDLElBQUcsSUFBSSxDQUFDLFFBQVEsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsU0FBUyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLGFBQWEsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxFQUFDO1FBQU0sQ0FBQyxNQUFLLElBQUksQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLFlBQVksQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLFlBQVksS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsV0FBVyxFQUFDLElBQUksQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxpQkFBaUIsRUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsSUFBRSxVQUFVLElBQUUsT0FBTyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTSxDQUFDLENBQUM7TUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsSUFBSSxJQUFJLENBQUMsSUFBSSxFQUFDO1FBQUMsSUFBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBRCxDQUFDLEVBQUUsWUFBWSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQztZQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLFVBQVU7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQztZQUFDLEVBQUUsRUFBQyxDQUFDLENBQUM7VUFBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7VUFBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLGVBQWUsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFNBQVMsTUFBSSxJQUFJLENBQUMsdUJBQXVCLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLE1BQUssSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDO1VBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxRQUFRO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDO1VBQUMsRUFBRSxFQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsWUFBWTtNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxZQUFZLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLG1CQUFtQixDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLElBQUksR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLG1CQUFtQixFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsTUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQyxNQUFLLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLElBQUksQ0FBQyxRQUFRLEVBQUM7VUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsR0FBRyxFQUFDO1VBQU8sSUFBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDO1VBQUMsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxLQUFJLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsWUFBWSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLGtCQUFrQixJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxLQUFLLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLElBQUksSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSTtRQUFDLElBQUcsSUFBSSxDQUFDLFFBQVEsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsS0FBSztZQUFDO1VBQUs7UUFBQyxDQUFDLE1BQUk7VUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxFQUFDLE9BQU0sQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixJQUFFLENBQUMsQ0FBQyxHQUFDLEtBQUs7UUFBQTtRQUFDLElBQUcsQ0FBQyxFQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUM7VUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtNQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLHVCQUF1QixJQUFFLENBQUMsQ0FBQyxjQUFjLENBQUMsWUFBWSxFQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyx1QkFBdUIsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxXQUFXLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO1FBQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsdUJBQXVCLElBQUUsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUMsR0FBQyxXQUFXLEdBQUMsWUFBWSxFQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsZ0JBQWdCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDO1FBQUMsaUJBQWlCLEVBQUMsQ0FBQztRQUFDLHVCQUF1QixFQUFDLENBQUM7UUFBQyxzQkFBc0IsRUFBQyxDQUFDO1FBQUMsZUFBZSxFQUFDLENBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDO01BQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxJQUFFLENBQUMsRUFBQyxPQUFNLEVBQUU7TUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxNQUFLLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsa0JBQWtCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxxQkFBcUIsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsZUFBZSxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDO1FBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxRQUFRO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQztRQUFDLENBQUMsRUFBQztNQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxLQUFLLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGVBQWU7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLEdBQUMsRUFBRSxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLE9BQUssQ0FBQyxHQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRO01BQUMsSUFBRyxpQkFBaUIsS0FBRyxDQUFDLEVBQUM7UUFBQyxPQUFLLENBQUMsR0FBRTtVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFLLENBQUMsR0FBRSxDQUFDLENBQUMsRUFBRSxJQUFFLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBRCxDQUFDLENBQUUsU0FBUyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsRUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxHQUFHLENBQUMsRUFBQyxNQUFLLDRCQUE0QjtNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWM7UUFBQyxDQUFDLEdBQUM7VUFBQyxJQUFJLEVBQUMsY0FBYztVQUFDLEdBQUcsRUFBQyxVQUFVO1VBQUMsSUFBSSxFQUFDLE9BQU87VUFBQyxLQUFLLEVBQUMsYUFBYTtVQUFDLE9BQU8sRUFBQztRQUFpQixDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxFQUFDLFlBQVU7VUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsRUFBRTtRQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRztNQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUM7TUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxxREFBcUQsR0FBQyxDQUFDLENBQUM7SUFBQTtJQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7RUFBQTtBQUFDLENBQUMsRUFBRSxNQUFNLENBQUM7Ozs7O0FDWDE0dkI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUMsTUFBTSxDQUFDLFFBQVEsS0FBRyxNQUFNLENBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQyxFQUFFLElBQUksQ0FBQyxZQUFVO0VBQUMsWUFBWTs7RUFBQyxNQUFNLENBQUMsU0FBUyxDQUFDLGFBQWEsRUFBQyxDQUFDLGFBQWEsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDO0lBQUMsSUFBSSxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLGdCQUFnQixJQUFFLE1BQU07TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxTQUFTO01BQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxZQUFVLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUM7VUFBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztVQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO1VBQUMsU0FBUyxFQUFDLElBQUksQ0FBQyxDQUFEO1FBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUM7WUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUc7VUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO01BQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEVBQUU7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG9CQUFvQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsa0JBQWtCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxNQUFNLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxZQUFZLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQztNQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxPQUFLLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO01BQUEsQ0FBQyxNQUFLLE9BQUssQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7TUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxJQUFJLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEtBQUssR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLE1BQU0sSUFBRSxDQUFDLElBQUUsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsTUFBTSxJQUFFLENBQUMsSUFBRSxJQUFJLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsSUFBRSxNQUFNLElBQUUsQ0FBQyxJQUFFLEtBQUssR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLEVBQUU7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFNLEVBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFNLEVBQUUsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUk7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxDQUFDLEVBQUUsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsbUJBQW1CLEVBQUM7TUFBQyxJQUFJLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLFFBQVEsRUFBQyxPQUFPLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFdBQVcsRUFBQyxPQUFPLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGFBQWEsRUFBQyxPQUFPLENBQUMsRUFBQyxDQUFDO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0FBQUEsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxNQUFNLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQzs7Ozs7QUNYdGxKO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxtQkFBbUIsRUFBQyxDQUFDLHFCQUFxQixFQUFDLFdBQVcsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVTtRQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsUUFBUTtNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsS0FBSyxDQUFDO0lBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLDJCQUEyQixHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLGFBQWEsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUM7TUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7TUFBQyxJQUFJLEVBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7TUFBQyxRQUFRLEVBQUMsQ0FBQztNQUFDLE9BQU8sRUFBQyxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7TUFBQyxXQUFXLEVBQUMsQ0FBQztNQUFDLFVBQVUsRUFBQztJQUFFLENBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQywyQkFBMkI7TUFBQyxDQUFDLEdBQUMsc0RBQXNEO01BQUMsQ0FBQyxHQUFDLGtEQUFrRDtNQUFDLENBQUMsR0FBQyxZQUFZO01BQUMsQ0FBQyxHQUFDLHVCQUF1QjtNQUFDLENBQUMsR0FBQyxzQkFBc0I7TUFBQyxDQUFDLEdBQUMsa0JBQWtCO01BQUMsQ0FBQyxHQUFDLHlCQUF5QjtNQUFDLENBQUMsR0FBQyxZQUFZO01BQUMsQ0FBQyxHQUFDLFVBQVU7TUFBQyxDQUFDLEdBQUMsWUFBWTtNQUFDLENBQUMsR0FBQyx3Q0FBd0M7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsdUJBQXVCO01BQUMsQ0FBQyxHQUFDLGdDQUFnQztNQUFDLENBQUMsR0FBQyxxREFBcUQ7TUFBQyxDQUFDLEdBQUMsdUJBQXVCO01BQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsR0FBRztNQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFFBQVE7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxLQUFLLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxLQUFLLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQztRQUFDLGFBQWEsRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBUyxDQUFDLFNBQVM7TUFBQyxDQUFDLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsU0FBUyxDQUFDLEVBQUMsNkJBQTZCLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyx1Q0FBdUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE1BQU0sS0FBRyxFQUFFLENBQUMsR0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsTUFBTSxDQUFDLE9BQU8sSUFBRSxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLElBQUcsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUcsRUFBQyxLQUFLLEVBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUU7UUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUk7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxnQkFBZ0IsR0FBQyxZQUFVLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztRQUFDLE9BQU8sQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBRyxNQUFNLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEtBQUk7VUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsOEJBQThCLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsR0FBQyxpQkFBaUIsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxpQkFBaUIsR0FBQyxnQkFBZ0IsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSTtZQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLEdBQUc7WUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sR0FBQyxRQUFRLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFBO1VBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsYUFBYSxHQUFDLGNBQWMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxVQUFVLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxNQUFNLEdBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsT0FBSyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFLLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxJQUFFLE9BQU8sQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxTQUFTLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsS0FBSyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsR0FBQyxFQUFFLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsV0FBVyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTTtVQUFDLElBQUksRUFBQyxDQUFDO1VBQUMsUUFBUSxFQUFDO1FBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsS0FBSyxFQUFDLFFBQVE7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsWUFBWSxFQUFDLGFBQWEsRUFBQyxXQUFXLEVBQUMsY0FBYyxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLElBQUksSUFBRSxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLFdBQVcsS0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLEtBQUssQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsTUFBTSxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsSUFBRSxLQUFLLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE9BQU8sQ0FBQyxHQUFHLENBQUMsTUFBSSxDQUFDLEdBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU0sUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUk7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsT0FBTyxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxHQUFHLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxXQUFXLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEtBQUs7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLHFEQUFxRDtJQUFDLEtBQUksQ0FBQyxJQUFJLEVBQUUsRUFBQyxFQUFFLElBQUUsR0FBRyxHQUFDLENBQUMsR0FBQyxLQUFLO0lBQUMsRUFBRSxHQUFDLE1BQU0sQ0FBQyxFQUFFLEdBQUMsR0FBRyxFQUFDLElBQUksQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxFQUFFO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO1VBQUE7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEVBQUMsT0FBSyxDQUFDLEdBQUMsRUFBRSxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLFFBQVEsR0FBQyxFQUFFLENBQUM7UUFBQSxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO1VBQUE7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEVBQUMsT0FBSyxDQUFDLEdBQUMsRUFBRSxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1FBQUEsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxDQUFDO1FBQUEsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEtBQUcsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUU7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUM7WUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxFQUFDO2NBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztZQUFBO1VBQUMsQ0FBQyxNQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBO01BQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQztNQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVTtVQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUU7VUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUE7UUFBQyxPQUFNO1VBQUMsS0FBSyxFQUFDLENBQUM7VUFBQyxHQUFHLEVBQUMsQ0FBQztVQUFDLFFBQVEsRUFBQyxDQUFDO1VBQUMsRUFBRSxFQUFDO1FBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLFlBQVksRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUM7TUFBQSxDQUFDLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsRUFBRTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxjQUFjLEdBQUMsYUFBYSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxDQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxNQUFLLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQztJQUFDLEtBQUksQ0FBQyxHQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsRUFBQyxFQUFFLEVBQUUsR0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxFQUFFLENBQUMsR0FBQyxFQUFFO0lBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQztRQUFDLENBQUMsRUFBQyxDQUFDLEdBQUM7TUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsWUFBWSxFQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsMkJBQTJCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUM7VUFBQyxNQUFNLEVBQUM7UUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUTtVQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUM7WUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztjQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsTUFBTSxDQUFDLGdCQUFnQixJQUFFLE1BQU0sRUFBRSxHQUFHLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7Y0FBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLHNCQUFzQixDQUFDLEVBQUMsQ0FBQyxDQUFDO1lBQUE7VUFBQyxDQUFDLENBQUM7UUFBQTtNQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDO01BQUE7TUFBQyxPQUFPLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDO1FBQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7VUFBQyxPQUFPLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO1FBQUEsQ0FBQztRQUFDLFFBQVEsRUFBQztNQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxpRkFBaUYsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLFdBQVc7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLGlCQUFpQixDQUFDO01BQUMsRUFBRSxHQUFDLElBQUksS0FBRyxDQUFDLENBQUMsYUFBYSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFlBQVksSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsWUFBWTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUUsQ0FBRCxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUQsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxLQUFLLENBQUMseUJBQXlCLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1VBQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLFNBQVMsRUFBQztZQUFDLElBQUksQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztjQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDO1lBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFBO1FBQUMsQ0FBQyxNQUFLLElBQUcsRUFBRSxFQUFFLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDO1lBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsRUFBRSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEVBQUUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsR0FBRyxFQUFDLENBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLDJCQUEyQixDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVk7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLEVBQUU7VUFBQyxJQUFJLENBQUM7WUFBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsV0FBVztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVk7WUFBQyxDQUFDLEdBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxRQUFRO1lBQUMsQ0FBQyxHQUFDLCtDQUErQyxHQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLENBQUMsRUFBRSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsSUFBRSwrQkFBK0IsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsb0NBQW9DLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxXQUFXLElBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQztZQUFDLElBQUksQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxFQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsRUFBRSxJQUFFLENBQUMsS0FBRyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSTtVQUFBO1FBQUM7TUFBQyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVztRQUFDLElBQUcsRUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxPQUFPLEVBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQztRQUFDLElBQUcsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFJO1VBQUMsSUFBRyxFQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxFQUFFLENBQUMsRUFBQyxLQUFLLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQTtRQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUc7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxLQUFLLENBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxFQUFFLENBQUMsbVBBQW1QLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFVBQVUsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxTQUFTLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUM7VUFBQyxJQUFHLENBQUMsR0FBQztZQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxXQUFXLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxvQkFBb0IsRUFBQyxDQUFDLENBQUMsV0FBVztVQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixFQUFDLElBQUksSUFBRSxDQUFDLEVBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQyxVQUFVLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsZUFBZSxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFFBQVEsR0FBQyxXQUFXLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxFQUFFLENBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLGdCQUFnQixJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLEVBQUUsQ0FBQyxXQUFXLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsZ0JBQWdCLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssTUFBSSxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQztRQUFBO1FBQUMsS0FBSSxFQUFFLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFdBQVcsRUFBQyxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsRUFBQyxDQUFDLENBQUMsSUFBRSxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLE1BQUksRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLGlCQUFpQixDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsS0FBSyxDQUFDLEdBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxjQUFjLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFdBQVcsRUFBQztNQUFDLFlBQVksRUFBQyxzQkFBc0I7TUFBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFPLEVBQUM7SUFBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsY0FBYyxFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxxQkFBcUIsRUFBQyxzQkFBc0IsRUFBQyx5QkFBeUIsRUFBQyx3QkFBd0IsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLEtBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDO01BQUMsU0FBUyxFQUFDLEVBQUUsQ0FBQyxpQkFBaUIsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsb0JBQW9CLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxxQkFBcUI7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsbUJBQW1CLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsbUJBQW1CLEtBQUcsS0FBSyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxpQkFBaUIsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxHQUFHLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztRQUFBO1FBQUMsT0FBTyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLFNBQVMsRUFBQztJQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsU0FBUyxFQUFDO0lBQUUsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGFBQWEsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsbUJBQW1CLEVBQUM7TUFBQyxZQUFZLEVBQUMsU0FBUztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGdCQUFnQixFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsb0JBQW9CLEVBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxZQUFZLEVBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUM7TUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLCtDQUErQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUM7TUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLG1EQUFtRDtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxNQUFNLEVBQUM7TUFBQyxZQUFZLEVBQUMsdUJBQXVCO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxFQUFDO01BQUMsWUFBWSxFQUFDLGtCQUFrQjtNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyx1QkFBdUIsRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFFBQVEsRUFBQztNQUFDLFlBQVksRUFBQyxnQkFBZ0I7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGdCQUFnQixFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxnQkFBZ0IsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxTQUFTLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxPQUFPLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsYUFBYSxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxtRUFBbUU7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsMkJBQTJCLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxVQUFVLElBQUcsQ0FBQyxHQUFDLFVBQVUsR0FBQyxZQUFZO1FBQUMsT0FBTyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLFFBQVEsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxHQUFHLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxVQUFVLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxnQkFBZ0IsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsaUJBQWlCLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLEVBQUUsQ0FBQyx5QkFBeUIsRUFBQztNQUFDLFlBQVksRUFBQyxHQUFHO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsV0FBVyxLQUFHLENBQUM7UUFBQyxPQUFNLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxnQkFBZ0IsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxRQUFRLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxRQUFRLEdBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLGNBQWMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztVQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxLQUFHLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQztRQUFBLENBQUMsTUFBSyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsS0FBRyxJQUFJLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLEVBQUUsQ0FBQyxXQUFXLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsSUFBRSxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTztRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsRUFBQyxFQUFFLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsY0FBYyxJQUFFLGFBQWEsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxLQUFLO1FBQUMsSUFBRyxLQUFLLEtBQUcsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLGlCQUFpQixLQUFHLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxLQUFHLEVBQUUsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQztNQUFBO0lBQUMsQ0FBQztJQUFDLEtBQUksRUFBRSxDQUFDLFlBQVksRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQywwQ0FBMEMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxFQUFFLEdBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLE9BQU0sQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxlQUFlO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxDQUFDLGNBQWMsRUFBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxFQUFDLEVBQUUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsMEJBQTBCLEVBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyx3QkFBd0IsS0FBRyxDQUFDLEdBQUMsU0FBUyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsV0FBVyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsSUFBRSxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQztNQUFBO01BQUMsSUFBRyxDQUFDLEVBQUM7UUFBQyxPQUFLLENBQUMsR0FBRTtVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUM7TUFBQTtNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsT0FBTyxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsYUFBYSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsTUFBSSxPQUFPLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLElBQUUsRUFBRSxHQUFDLEVBQUUsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLFdBQVcsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxJQUFFLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxLQUFLLElBQUUsQ0FBQyxHQUFDLEVBQUUsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLE1BQU0sS0FBRyxDQUFDLElBQUUsU0FBUyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLElBQUksS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtRQUFDLENBQUMsR0FBQyxJQUFJO01BQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUs7UUFBQyxJQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxZQUFZLEtBQUcsQ0FBQyxJQUFJLEVBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJO1lBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUk7Y0FBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSTtnQkFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7Z0JBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztjQUFBO1lBQUMsT0FBSSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUEsQ0FBQyxNQUFLLE9BQUssQ0FBQyxHQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFLLE9BQUssQ0FBQyxHQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsRUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBQSxFQUFVO01BQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLEtBQUssRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUM7TUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsS0FBSyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsS0FBSyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLE1BQU0sSUFBRSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsYUFBYTtNQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtRQUNseCtCLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7Ozs7O0FDWmhJO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsSUFBSSxDQUFDLEdBQUMsUUFBUSxDQUFDLGVBQWU7SUFBQyxDQUFDLEdBQUMsTUFBTTtJQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLEdBQUMsT0FBTyxHQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUk7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDO01BQUMsUUFBUSxFQUFDLFVBQVU7TUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLE9BQU8sRUFBQyxPQUFPO01BQUMsSUFBSSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUM7VUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksRUFBQyxHQUFHLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksRUFBQyxHQUFHLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsR0FBRyxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtJQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxVQUFVO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtJQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxTQUFTO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0VBQUEsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCJkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKFwiLmN1c3RvbS1zZWxlY3RcIikuZm9yRWFjaChjdXN0b21TZWxlY3QgPT4ge1xuXHRjb25zdCBzZWxlY3RCdG4gPSBjdXN0b21TZWxlY3QucXVlcnlTZWxlY3RvcihcIi5zZWxlY3QtYnV0dG9uXCIpO1xuXHRjb25zdCBzZWxlY3RlZFZhbHVlID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3IoXCIuc2VsZWN0ZWQtdmFsdWVcIik7XG5cdGNvbnN0IGhhbmRsZXIgPSBmdW5jdGlvbihlbG0pIHtcblx0XHRjb25zdCBjdXN0b21DaGFuZ2VFdmVudCA9IG5ldyBDdXN0b21FdmVudCgnY3VzdG9tLXNlbGVjdC1jaGFuZ2UnLCB7XG5cdFx0XHRkZXRhaWw6IHtcblx0XHRcdFx0c2VsZWN0ZWRPcHRpb246IGVsbVxuXHRcdFx0fVxuXHRcdH0pO1xuXHRcdHNlbGVjdGVkVmFsdWUudGV4dENvbnRlbnQgPSBlbG0udGV4dENvbnRlbnQ7XG5cdFx0Y3VzdG9tU2VsZWN0LmNsYXNzTGlzdC5yZW1vdmUoXCJhY3RpdmVcIik7XG5cdFx0Y3VzdG9tU2VsZWN0LmRpc3BhdGNoRXZlbnQoY3VzdG9tQ2hhbmdlRXZlbnQpO1xuXG5cdH1cblx0c2VsZWN0QnRuLmFkZEV2ZW50TGlzdGVuZXIoXCJjbGlja1wiLCAoKSA9PiB7XG5cdFx0Y3VzdG9tU2VsZWN0LmNsYXNzTGlzdC50b2dnbGUoXCJhY3RpdmVcIik7XG5cdFx0c2VsZWN0QnRuLnNldEF0dHJpYnV0ZShcblx0XHRcdFwiYXJpYS1leHBhbmRlZFwiLFxuXHRcdFx0c2VsZWN0QnRuLmdldEF0dHJpYnV0ZShcImFyaWEtZXhwYW5kZWRcIikgPT09IFwidHJ1ZVwiID8gXCJmYWxzZVwiIDogXCJ0cnVlXCJcblx0XHQpO1xuXHR9KTtcblxuXHRjdXN0b21TZWxlY3QuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0aWYgKGUudGFyZ2V0Lm1hdGNoZXMoJ2xhYmVsJykpIHtcblxuXHRcdFx0Y29uc3QgYWxsSXRlbXMgPSBjdXN0b21TZWxlY3QucXVlcnlTZWxlY3RvckFsbCgnbGknKTtcblx0XHRcdGFsbEl0ZW1zLmZvckVhY2goaXRlbSA9PiBpdGVtLmNsYXNzTGlzdC5yZW1vdmUoJ2FjdGl2ZScpKTtcblx0XHRcdGNvbnN0IGNsaWNrZWRQbGFuID0gZS50YXJnZXQuY2xvc2VzdCgnbGknKTtcblxuXHRcdFx0aWYgKGNsaWNrZWRQbGFuKSB7XG5cdFx0XHRcdGNsaWNrZWRQbGFuLmNsYXNzTGlzdC5hZGQoJ2FjdGl2ZScpO1xuXHRcdFx0XHRoYW5kbGVyKGNsaWNrZWRQbGFuKTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKFwiY2xpY2tcIiwgKGUpID0+IHtcblx0XHRpZiAoIWN1c3RvbVNlbGVjdC5jb250YWlucyhlLnRhcmdldCkpIHtcblx0XHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QucmVtb3ZlKFwiYWN0aXZlXCIpO1xuXHRcdFx0c2VsZWN0QnRuLnNldEF0dHJpYnV0ZShcImFyaWEtZXhwYW5kZWRcIiwgXCJmYWxzZVwiKTtcblx0XHR9XG5cdH0pO1xufSk7IiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuICAgIC8qKlxuICAgICAqIFJlZnJlc2ggTGljZW5zZSBkYXRhXG4gICAgICovXG4gICAgdmFyIF9pc1JlZnJlc2hpbmcgPSBmYWxzZTtcbiAgICAkKCcjd3ByLWFjdGlvbi1yZWZyZXNoX2FjY291bnQnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGlmKCFfaXNSZWZyZXNoaW5nKXtcbiAgICAgICAgICAgIHZhciBidXR0b24gPSAkKHRoaXMpO1xuICAgICAgICAgICAgdmFyIGFjY291bnQgPSAkKCcjd3ByLWFjY291bnQtZGF0YScpO1xuICAgICAgICAgICAgdmFyIGV4cGlyZSA9ICQoJyN3cHItZXhwaXJhdGlvbi1kYXRhJyk7XG5cbiAgICAgICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIF9pc1JlZnJlc2hpbmcgPSB0cnVlO1xuICAgICAgICAgICAgYnV0dG9uLnRyaWdnZXIoICdibHVyJyApO1xuXG5cdFx0XHQvLyBTdGFydCBwb2xsaW5nIGlmIG5vdCBhbHJlYWR5IHJ1bm5pbmcuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgIGV4cGlyZS5yZW1vdmVDbGFzcygnd3ByLWlzVmFsaWQgd3ByLWlzSW52YWxpZCcpO1xuXG4gICAgICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9yZWZyZXNoX2N1c3RvbWVyX2RhdGEnLFxuICAgICAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pc0hpZGRlbicpO1xuXG4gICAgICAgICAgICAgICAgICAgIGlmICggdHJ1ZSA9PT0gcmVzcG9uc2Uuc3VjY2VzcyApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGFjY291bnQuaHRtbChyZXNwb25zZS5kYXRhLmxpY2Vuc2VfdHlwZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICBleHBpcmUuYWRkQ2xhc3MocmVzcG9uc2UuZGF0YS5saWNlbnNlX2NsYXNzKS5odG1sKHJlc3BvbnNlLmRhdGEubGljZW5zZV9leHBpcmF0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaWNvbi1yZWZyZXNoIHdwci1pc0hpZGRlbicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWljb24tY2hlY2snKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDI1MCk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaWNvbi1yZWZyZXNoIHdwci1pc0hpZGRlbicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWljb24tY2xvc2UnKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDI1MCk7XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoe29uQ29tcGxldGU6ZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBfaXNSZWZyZXNoaW5nID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgICAgICB9fSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonKz13cHItaXNIaWRkZW4nfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWljb24tY2hlY2snfX0sIDAuMjUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWljb24tY2xvc2UnfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jys9d3ByLWljb24tcmVmcmVzaCd9fSwgMC4yNSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonLT13cHItaXNIaWRkZW4nfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICA7XG4gICAgICAgICAgICAgICAgICAgIH0sIDIwMDApO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogU2F2ZSBUb2dnbGUgb3B0aW9uIHZhbHVlcyBvbiBjaGFuZ2VcbiAgICAgKi9cbiAgICAkKCcud3ByLXJhZGlvIGlucHV0W3R5cGU9Y2hlY2tib3hdJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICB2YXIgbmFtZSAgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG4gICAgICAgIHZhciB2YWx1ZSA9ICQodGhpcykucHJvcCgnY2hlY2tlZCcpID8gMSA6IDA7XG5cblx0XHR2YXIgZXhjbHVkZWQgPSBbICdjbG91ZGZsYXJlX2F1dG9fc2V0dGluZ3MnLCAnY2xvdWRmbGFyZV9kZXZtb2RlJywgJ2FuYWx5dGljc19lbmFibGVkJyBdO1xuXHRcdGlmICggZXhjbHVkZWQuaW5kZXhPZiggbmFtZSApID49IDAgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X3RvZ2dsZV9vcHRpb24nLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuICAgICAgICAgICAgICAgIG9wdGlvbjoge1xuICAgICAgICAgICAgICAgICAgICBuYW1lOiBuYW1lLFxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTogdmFsdWVcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgZnVuY3Rpb24ocmVzcG9uc2UpIHt9XG4gICAgICAgICk7XG5cdH0pO1xuXG5cdC8qKlxuICAgICAqIFNhdmUgZW5hYmxlIENQQ1NTIGZvciBtb2JpbGVzIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIENQQ1NTIGJ0biBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItaGlkZS1vbi1jbGljaycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLXNob3ctb24tY2xpY2snKS5zaG93KCk7XG5cdFx0XHRcdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG5cbiAgICAvKipcbiAgICAgKiBTYXZlIGVuYWJsZSBHb29nbGUgRm9udHMgT3B0aW1pemF0aW9uIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIENQQ1NTIGJ0biBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItaGlkZS1vbi1jbGljaycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLXNob3ctb24tY2xpY2snKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI21pbmlmeV9nb29nbGVfZm9udHMnKS52YWwoMSk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9KTtcblxuICAgICQoICcjcm9ja2V0LWRpc21pc3MtcHJvbW90aW9uJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2Rpc21pc3NfcHJvbW8nLFxuICAgICAgICAgICAgICAgIG5vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdCQoJyNyb2NrZXQtcHJvbW8tYmFubmVyJykuaGlkZSggJ3Nsb3cnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9ICk7XG5cbiAgICAkKCAnI3JvY2tldC1kaXNtaXNzLXJlbmV3YWwnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZGlzbWlzc19yZW5ld2FsJyxcbiAgICAgICAgICAgICAgICBub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQkKCcjcm9ja2V0LXJlbmV3YWwtYmFubmVyJykuaGlkZSggJ3Nsb3cnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9ICk7XG5cdCQoICcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbGlzdCcgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCQoJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1tc2cnKS5odG1sKCcnKTtcblx0XHQkLmFqYXgoe1xuXHRcdFx0dXJsOiByb2NrZXRfYWpheF9kYXRhLnJlc3RfdXJsLFxuXHRcdFx0YmVmb3JlU2VuZDogZnVuY3Rpb24gKCB4aHIgKSB7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnWC1XUC1Ob25jZScsIHJvY2tldF9hamF4X2RhdGEucmVzdF9ub25jZSApO1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ0FjY2VwdCcsICdhcHBsaWNhdGlvbi9qc29uLCAqLyo7cT0wLjEnICk7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnQ29udGVudC1UeXBlJywgJ2FwcGxpY2F0aW9uL2pzb24nICk7XG5cdFx0XHR9LFxuXHRcdFx0bWV0aG9kOiBcIlBVVFwiLFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2VzKSB7XG5cdFx0XHRcdGxldCBleGNsdXNpb25fbXNnX2NvbnRhaW5lciA9ICQoJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1tc2cnKTtcblx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuaHRtbCgnJyk7XG5cdFx0XHRcdGlmICggdW5kZWZpbmVkICE9PSByZXNwb25zZXNbJ3N1Y2Nlc3MnXSApIHtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8ZGl2IGNsYXNzPVwibm90aWNlIG5vdGljZS1lcnJvclwiPicgKyByZXNwb25zZXNbJ21lc3NhZ2UnXSArICc8L2Rpdj4nICk7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cdFx0XHRcdE9iamVjdC5rZXlzKCByZXNwb25zZXMgKS5mb3JFYWNoKCggcmVzcG9uc2Vfa2V5ICkgPT4ge1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxzdHJvbmc+JyArIHJlc3BvbnNlX2tleSArICc6IDwvc3Ryb25nPicgKTtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoIHJlc3BvbnNlc1tyZXNwb25zZV9rZXldWydtZXNzYWdlJ10gKTtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8YnI+JyApO1xuXHRcdFx0XHR9KTtcblx0XHRcdH1cblx0XHR9KTtcblx0fSApO1xuXG4gICAgLyoqXG4gICAgICogRW5hYmxlIG1vYmlsZSBjYWNoZSBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9tb2JpbGVfY2FjaGUnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIGNhY2hlIGVuYWJsZSBidXR0b24gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnI3dwcl9tb2JpbGVfY2FjaGVfZGVmYXVsdCcpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcjd3ByX21vYmlsZV9jYWNoZV9yZXNwb25zZScpLnNob3coKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAgICAgICAgICAgICAvLyBTZXQgdmFsdWVzIG9mIG1vYmlsZSBjYWNoZSBhbmQgc2VwYXJhdGUgY2FjaGUgZmlsZXMgZm9yIG1vYmlsZXMgb3B0aW9uIHRvIDEuXG4gICAgICAgICAgICAgICAgICAgICQoJyNjYWNoZV9tb2JpbGUnKS52YWwoMSk7XG4gICAgICAgICAgICAgICAgICAgICQoJyNkb19jYWNoaW5nX21vYmlsZV9maWxlcycpLnZhbCgxKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xufSk7XG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbigpIHtcblx0Y29uc3QgYW5hbHl0aWNzQ2hlY2tib3ggPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnYW5hbHl0aWNzX2VuYWJsZWQnKTtcblxuXHRpZiAoYW5hbHl0aWNzQ2hlY2tib3gpIHtcblx0XHRhbmFseXRpY3NDaGVja2JveC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGlzQ2hlY2tlZCA9IHRoaXMuY2hlY2tlZDtcblxuXHRcdFx0Ly8gVXBkYXRlIHRoZSBnbG9iYWwgbWl4cGFuZWwgZGF0YSBvcHRpbiBzdGF0ZSBpbW1lZGlhdGVseVxuXHRcdFx0aWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSAhPT0gJ3VuZGVmaW5lZCcpIHtcblx0XHRcdFx0cm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9IGlzQ2hlY2tlZCA/ICcxJyA6ICcwJztcblx0XHRcdH1cblxuXHRcdFx0ZmV0Y2goYWpheHVybCwge1xuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0aGVhZGVyczoge1xuXHRcdFx0XHRcdCdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJyxcblx0XHRcdFx0fSxcblx0XHRcdFx0Ym9keTogbmV3IFVSTFNlYXJjaFBhcmFtcyh7XG5cdFx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3RvZ2dsZV9vcHRpbicsXG5cdFx0XHRcdFx0dmFsdWU6IGlzQ2hlY2tlZCA/IDEgOiAwLFxuXHRcdFx0XHRcdF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuXHRcdFx0XHR9KVxuXHRcdFx0fSk7XG5cdFx0fSk7XG5cdH1cbn0pO1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgZnVuY3Rpb24oKSB7XG5cdC8qKlxuXHQgKiBQZXJmb3JtYW5jZSBNb25pdG9yaW5nIHdpdGggUHJvZ3Jlc3NpdmUgUG9sbGluZy5cblx0ICovXG5cblx0XHQvLyA9PT09IENvbmZpZ3VyYXRpb24gPT09PVxuXHRjb25zdCBQT0xMX0JBU0VfSU5URVJWQUwgPSAyMDAwOyAgIC8vIFN0YXJ0IHBvbGxpbmcgYXQgMiBzZWNvbmRzXG5cdGNvbnN0IFBPTExfTUFYX0lOVEVSVkFMID0gNTAwMDsgICAvLyBNYXggcG9sbGluZyBpbnRlcnZhbCAoNSBzZWNvbmRzKVxuXG5cdC8vID09PT0gU3RhdGUgPT09PVxuXHRsZXQgcm9ja2V0SW5zaWdodHNJZHMgPSBBcnJheS5pc0FycmF5KHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5yb2NrZXRfaW5zaWdodHNfaWRzKSA/IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhLnJvY2tldF9pbnNpZ2h0c19pZHMuc2xpY2UoKSA6IFtdO1xuXHRsZXQgcG9sbEludGVydmFsID0gUE9MTF9CQVNFX0lOVEVSVkFMO1xuXHRsZXQgcG9sbFRpbWVyID0gbnVsbDtcblx0bGV0IGhhc0NyZWRpdCA9IHRydWU7IC8vIFRyYWNrIGNyZWRpdCBzdGF0dXNcbiAgICBsZXQgZ2xvYmFsU2NvcmVEYXRhID0ge1xuICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICBzdGF0dXM6ICcnLFxuICAgICAgICAgICAgc2NvcmU6IDAsXG4gICAgICAgICAgICBwYWdlc19udW06IDBcbiAgICAgICAgfSxcbiAgICAgICAgaHRtbDogJycsXG4gICAgICAgIHJvd19odG1sOiAnJyxcblx0XHRkaXNhYmxlZF9idG5faHRtbDoge1xuXHRcdFx0Z2xvYmFsX3Njb3JlX3dpZGdldDogJycsXG5cdFx0XHRyb2NrZXRfaW5zaWdodHM6ICcnXG5cdFx0fVxuICAgIH07XG5cbiAgICAvLyBJbml0aWFsaXplIGdsb2JhbFNjb3JlRGF0YSBmcm9tIGxvY2FsaXplZCBzY3JpcHQgZGF0YSBpZiBhdmFpbGFibGVcbiAgICBpZiAod2luZG93LnJvY2tldF9hamF4X2RhdGE/Lmdsb2JhbF9zY29yZV9kYXRhKSB7XG4gICAgICAgIGdsb2JhbFNjb3JlRGF0YSA9IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhLmdsb2JhbF9zY29yZV9kYXRhO1xuICAgIH1cblxuXHQvLyA9PT09IERPTSBTZWxlY3RvcnMgPT09PVxuXHRjb25zdCAkcGFnZVVybElucHV0ID0gJCgnI3dwci1zcGVlZC1yYWRhci11cmwtaW5wdXQnKTtcblx0Y29uc3QgJHRhYmxlQm9keSA9ICQoJy53cHItcmktdXJscy10YWJsZSB0Ym9keScpO1xuXHRjb25zdCAkdGFibGUgPSAkKCcud3ByLXJpLXVybHMtdGFibGUnKTtcblxuXHQvLyA9PT09IFV0aWxpdHkgRnVuY3Rpb25zID09PT1cblx0ZnVuY3Rpb24gaXNWYWxpZFVybChpbnB1dCkge1xuXHRcdHRyeSB7XG5cdFx0XHRjb25zdCB1cmwgPSBuZXcgVVJMKGlucHV0KTtcblx0XHRcdHJldHVybiB1cmwuaG9zdG5hbWUuaW5jbHVkZXMoJy4nKSAmJiB1cmwuaG9zdG5hbWUuc3BsaXQoJy4nKS5wb3AoKS5sZW5ndGggPiAwO1xuXHRcdH0gY2F0Y2gge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIGFkZElkcyhuZXdJZCkge1xuXHRcdGlmICghcm9ja2V0SW5zaWdodHNJZHMuaW5jbHVkZXMobmV3SWQpKSB7XG5cdFx0XHRyb2NrZXRJbnNpZ2h0c0lkcy5wdXNoKG5ld0lkKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiBoYXNJZChpZCkge1xuXHRcdHJldHVybiByb2NrZXRJbnNpZ2h0c0lkcy5pbmNsdWRlcyhpZCk7XG5cdH1cblxuXHRmdW5jdGlvbiByZW1vdmVJZChpZCkge1xuXHRcdC8vIEVuc3VyZSB0aGF0IHRoZSBpZCB0byBiZSByZW1vdmVkIGlzIGFuIGludGVnZXIgZm9yIGFjY3VyYXRlIGNvbXBhcmlzb24uXG5cdFx0Y29uc3QgaWRUb1JlbW92ZSA9IHBhcnNlSW50KGlkLCAxMCk7XG5cdFx0cm9ja2V0SW5zaWdodHNJZHMgPSByb2NrZXRJbnNpZ2h0c0lkcy5maWx0ZXIoeCA9PiBwYXJzZUludCh4LCAxMCkgIT09IGlkVG9SZW1vdmUpO1xuXHR9XG5cblx0ZnVuY3Rpb24gdXBkYXRlUXVvdGFCYW5uZXIoY2FuQWRkUGFnZXMpIHtcblx0XHRjb25zdCAkc3VtbWFyeUluZm8gICAgPSAkKCcud3ByLXJpLXN1bW1hcnktaW5mbycpO1xuXHRcdGNvbnN0IGlzRnJlZSAgPSB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8uaXNfZnJlZSA9PT0gJzEnO1xuXHRcdGNvbnN0ICRxdW90YUJhbm5lciA9IGlzRnJlZSA/ICQoJyN3cHItcmktcXVvdGEtYmFubmVyJykgOiAkKCcjcm9ja2V0X2luc2lnaHRzX3N1cnZleScpO1xuXG5cdFx0Ly8gU2hvdyBiYW5uZXIgaWYgVVJMIGxpbWl0IHJlYWNoZWQgT1Igbm8gY3JlZGl0cyBsZWZ0IChtYXRjaGluZyBQSFAgbG9naWMgaW4gU3Vic2NyaWJlci5waHAgbGluZSAzOTgpLlxuXHRcdGNvbnN0IHNob3VsZFNob3dCYW5uZXIgPSBjYW5BZGRQYWdlcyA9PT0gZmFsc2UgfHwgIWhhc0NyZWRpdDtcblxuXHRcdGlmIChzaG91bGRTaG93QmFubmVyKSB7XG5cdFx0XHQkc3VtbWFyeUluZm8uaGlkZSgpO1xuXHRcdFx0JHF1b3RhQmFubmVyLnJlbW92ZUNsYXNzKCdoaWRkZW4nKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0JHN1bW1hcnlJbmZvLnNob3coKTtcblx0XHRcdCRxdW90YUJhbm5lci5hZGRDbGFzcygnaGlkZGVuJyk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gdXBkYXRlQ3JlZGl0U3RhdGUocmVzcG9uc2VIYXNDcmVkaXQpIHtcblx0XHRpZiAocmVzcG9uc2VIYXNDcmVkaXQgIT09IHVuZGVmaW5lZCAmJiBoYXNDcmVkaXQgIT09IHJlc3BvbnNlSGFzQ3JlZGl0KSB7XG5cdFx0XHRoYXNDcmVkaXQgPSByZXNwb25zZUhhc0NyZWRpdDtcblxuXHRcdFx0Ly8gVXBkYXRlIGFsbCByZXRlc3QgYnV0dG9ucyB3aGVuIGNyZWRpdCBzdGF0dXMgY2hhbmdlc1xuXHRcdFx0dXBkYXRlQWxsUmV0ZXN0QnV0dG9ucygpO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZUFsbFJldGVzdEJ1dHRvbnMoKSB7XG5cdFx0Y29uc3QgcmV0ZXN0QnV0dG9ucyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItYWN0aW9uLXNwZWVkX3JhZGFyX3JlZnJlc2gnKTtcblxuXHRcdHJldGVzdEJ1dHRvbnMuZm9yRWFjaChidXR0b24gPT4ge1xuXHRcdFx0Y29uc3Qgcm93ID0gYnV0dG9uLmNsb3Nlc3QoJy53cHItcmktaXRlbScpO1xuXHRcdFx0aWYgKCFyb3cpIHJldHVybjtcblxuXHRcdFx0Ly8gR2V0IHRoZSByb3cgSUQgYW5kIGNoZWNrIGlmIGl0J3MgY3VycmVudGx5IGJlaW5nIHByb2Nlc3NlZFxuXHRcdFx0Y29uc3Qgcm93SWQgPSBwYXJzZUludChyb3cuZGF0YXNldC5yb2NrZXRJbnNpZ2h0c0lkLCAxMCk7XG5cdFx0XHRjb25zdCBpc1J1bm5pbmcgPSByb2NrZXRJbnNpZ2h0c0lkcy5pbmNsdWRlcyhyb3dJZCk7XG5cblx0XHRcdGlmICghaGFzQ3JlZGl0IHx8IGlzUnVubmluZykge1xuXHRcdFx0XHQvLyBEaXNhYmxlIGJ1dHRvblxuXHRcdFx0XHRidXR0b24uY2xhc3NMaXN0LmFkZCgnd3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQnKTtcblx0XHRcdFx0YnV0dG9uLnNldEF0dHJpYnV0ZSgnZGlzYWJsZWQnLCAndHJ1ZScpO1xuXG5cdFx0XHRcdGlmICghaGFzQ3JlZGl0KSB7XG5cdFx0XHRcdFx0Ly8gQWRkIHRvb2x0aXAgZm9yIG5vIGNyZWRpdFxuXHRcdFx0XHRcdGJ1dHRvbi5jbGFzc0xpc3QuYWRkKCd3cHItYnRuLXdpdGgtdG9vbC10aXAnKTtcblx0XHRcdFx0XHRidXR0b24uc2V0QXR0cmlidXRlKCd0aXRsZScsIHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5yb2NrZXRfaW5zaWdodHNfbm9fY3JlZGl0X3Rvb2x0aXAgfHwgJ1VwZ3JhZGUgeW91ciBwbGFuIHRvIGdldCBhY2Nlc3MgdG8gcmUtdGVzdCBwZXJmb3JtYW5jZSBvciBydW4gbmV3IHRlc3RzJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIEVuYWJsZSBidXR0b25cblx0XHRcdFx0YnV0dG9uLmNsYXNzTGlzdC5yZW1vdmUoJ3dwci1yaS1hY3Rpb24tLWRpc2FibGVkJywgJ3dwci1idG4td2l0aC10b29sLXRpcCcpO1xuXHRcdFx0XHRidXR0b24ucmVtb3ZlQXR0cmlidXRlKCdkaXNhYmxlZCcpO1xuXHRcdFx0XHRidXR0b24ucmVtb3ZlQXR0cmlidXRlKCd0aXRsZScpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0ZnVuY3Rpb24gcmVzZXRQb2xsaW5nKCkge1xuXHRcdGlmIChwb2xsVGltZXIpIHtcblx0XHRcdGNsZWFyVGltZW91dChwb2xsVGltZXIpO1xuXHRcdFx0cG9sbFRpbWVyID0gbnVsbDtcblx0XHR9XG5cdFx0cG9sbEludGVydmFsID0gUE9MTF9CQVNFX0lOVEVSVkFMO1xuXHR9XG5cblx0ZnVuY3Rpb24gc2NoZWR1bGVQb2xsaW5nKCkge1xuXHRcdGlmIChyb2NrZXRJbnNpZ2h0c0lkcy5sZW5ndGggPiAwKSB7XG5cdFx0XHRwb2xsVGltZXIgPSBzZXRUaW1lb3V0KCgpID0+IHtcblx0XHRcdFx0Z2V0UmVzdWx0cygpO1xuXHRcdFx0fSwgcG9sbEludGVydmFsKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiBpbmNyZW1lbnRQb2xsaW5nKCkge1xuXHRcdHBvbGxJbnRlcnZhbCA9IE1hdGgubWluKHBvbGxJbnRlcnZhbCAqIDEuMywgUE9MTF9NQVhfSU5URVJWQUwpOyAvLyBFeHBvbmVudGlhbCBiYWNrb2ZmXG5cdH1cblxuICAgIGZ1bmN0aW9uIGlzT25EYXNoYm9hcmQoKSB7XG4gICAgICAgIGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMod2luZG93LmxvY2F0aW9uLnNlYXJjaCk7XG4gICAgICAgIHJldHVybiB1cmxQYXJhbXMuZ2V0KCdwYWdlJykgPT09ICd3cHJvY2tldCcgJiYgd2luZG93LmxvY2F0aW9uLmhhc2ggPT09ICcjZGFzaGJvYXJkJztcbiAgICB9XG5cblx0ZnVuY3Rpb24gaXNPblJvY2tldEluc2lnaHRzKCkge1xuXHRcdGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMod2luZG93LmxvY2F0aW9uLnNlYXJjaCk7XG5cdFx0cmV0dXJuIHVybFBhcmFtcy5nZXQoJ3BhZ2UnKSA9PT0gJ3dwcm9ja2V0JyAmJiB3aW5kb3cubG9jYXRpb24uaGFzaCA9PT0gJyNyb2NrZXRfaW5zaWdodHMnO1xuXHR9XG5cblx0ZnVuY3Rpb24gdXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKXtcblx0XHRjb25zdCAkdGFibGVHbG9iYWxTY29yZSA9ICQoJy53cHItcmktdXJscy10YWJsZSAud3ByLWdsb2JhbC1zY29yZScpO1xuXHRcdGlmICgkdGFibGVHbG9iYWxTY29yZS5sZW5ndGggPiAwKXtcblx0XHRcdCR0YWJsZUdsb2JhbFNjb3JlLnJlcGxhY2VXaXRoKGdsb2JhbFNjb3JlRGF0YS5yb3dfaHRtbCk7XG5cdFx0fWVsc2UgaWYgKCR0YWJsZUJvZHkubGVuZ3RoID4gMCkge1xuXHRcdFx0JHRhYmxlQm9keS5wcmVwZW5kKGdsb2JhbFNjb3JlRGF0YS5yb3dfaHRtbCk7XG5cdFx0fVxuXHRcdGRlY2lkZUdsb2JhbFNjb3JlVG9VcGRhdGUoKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBnbG9iYWwgc2NvcmUgVUkgd2lkZ2V0IG9yIHRhYmxlIHJvdyBiYXNlZCBvbiB0aGUgc2VsZWN0ZWQgbWVudS5cblx0ICogV2hlbiB0aGUgZGFzaGJvYXJkIG9yIHJvY2tldCBpbnNpZ2h0cyBtZW51IGlzIGNsaWNrZWQsIHRoaXMgZnVuY3Rpb24gdXBkYXRlc1xuXHQgKiB0aGUgY29ycmVzcG9uZGluZyBnbG9iYWwgc2NvcmUgZGlzcGxheSBhZnRlciBhIHNob3J0IGRlbGF5LlxuXHQgKi9cblx0ZnVuY3Rpb24gZGVjaWRlR2xvYmFsU2NvcmVUb1VwZGF0ZSgpIHtcblx0XHRpZiAoJycgPT09IGdsb2JhbFNjb3JlRGF0YS5odG1sKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGxldCBnbG9iYWxTY29yZVdpZGdldHMgPSAkKCcud3ByLWdsb2JhbC1zY29yZS13aWRnZXQnKTtcblxuXHRcdGlmIChnbG9iYWxTY29yZVdpZGdldHMubGVuZ3RoID09PSAwKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gVXBkYXRlIGFsbCBnbG9iYWwgc2NvcmUgd2lkZ2V0IGluc3RhbmNlcy5cblx0XHRnbG9iYWxTY29yZVdpZGdldHMuaHRtbChnbG9iYWxTY29yZURhdGEuaHRtbCk7XG5cblx0XHQvLyBEaXNhYmxlIFwiQWRkIFBhZ2VzXCIgYnV0dG9uIG9uIGdsb2JhbCBzY29yZSB3aWRnZXQuXG5cdFx0aWYgKCEoJ2Rpc2FibGVkX2J0bl9odG1sJyBpbiBnbG9iYWxTY29yZURhdGEpKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0JCgnLndwci1nbG9iYWwtc2NvcmUtd2lkZ2V0IC53cHItZ2xvYmFsLXNjb3JlLXdpZGdldC1idG4td3JhcHBlcicpLmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmRpc2FibGVkX2J0bl9odG1sLmdsb2JhbF9zY29yZV93aWRnZXQpO1xuXHR9XG5cblx0Ly8gPT09PSBBSkFYIEhhbmRsZXJzID09PT1cblx0ZnVuY3Rpb24gZ2V0UmVzdWx0cygpIHtcblx0XHRpZiAocm9ja2V0SW5zaWdodHNJZHMubGVuZ3RoID09PSAwKSB7XG5cdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvcHJvZ3Jlc3MnLCB7IGlkczogcm9ja2V0SW5zaWdodHNJZHMgfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIEFycmF5LmlzQXJyYXkocmVzcG9uc2UucmVzdWx0cykpIHtcblx0XHRcdFx0Ly8gVXBkYXRlIGNyZWRpdCBzdGF0dXNcblx0XHRcdFx0dXBkYXRlQ3JlZGl0U3RhdGUocmVzcG9uc2UuaGFzX2NyZWRpdCk7XG5cblx0XHRcdFx0Ly8gVXBkYXRlIHF1b3RhIGJhbm5lciB2aXNpYmlsaXR5XG5cdFx0XHRcdHVwZGF0ZVF1b3RhQmFubmVyKHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YSBhbmQgd2lkZ2V0IHdoZW4gc3RhdHVzIHx8IHBhZ2UgY291bnQgY2hhbmdlcy5cblx0XHRcdFx0aWYgKGdsb2JhbFNjb3JlRGF0YS5kYXRhLnN0YXR1cyAhPT0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuZGF0YS5zdGF0dXMgfHwgZ2xvYmFsU2NvcmVEYXRhLmRhdGEucGFnZXNfbnVtICE9PSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YS5kYXRhLnBhZ2VzX251bSkge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YS5cblx0XHRcdFx0XHRnbG9iYWxTY29yZURhdGEgPSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSBhbGwgZ2xvYmFsIHNjb3JlIHdpZGdldCBpbnN0YW5jZXMuXG5cdFx0XHRcdFx0JCgnLndwci1nbG9iYWwtc2NvcmUtd2lkZ2V0JykuaHRtbChyZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YS5odG1sKTtcblx0XHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIHJvdyBpbiB0YWJsZSBpZiBvbiBSb2NrZXQgSW5zaWdodHMgcGFnZS5cblx0XHRcdFx0XHR1cGRhdGVHbG9iYWxTY29yZVJvdyhnbG9iYWxTY29yZURhdGEpO1xuXG5cdFx0XHRcdFx0Ly8gRmlyZSBjdXN0b20gZXZlbnQgZm9yIG90aGVyIHdpZGdldHMgKGxpa2UgcmVjb21tZW5kYXRpb25zKVxuXHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQobmV3IEN1c3RvbUV2ZW50KCd3cHJHbG9iYWxTY29yZVVwZGF0ZWQnLCB7IGRldGFpbDogZ2xvYmFsU2NvcmVEYXRhLmRhdGEgfSkpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdHJlc3BvbnNlLnJlc3VsdHMuZm9yRWFjaChyZXN1bHQgPT4ge1xuXHRcdFx0XHRcdGNvbnN0ICRyb3cgPSAkKGAud3ByLXJpLWl0ZW1bZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke3Jlc3VsdC5pZH1cIl1gKTtcblx0XHRcdFx0XHQkcm93LnJlcGxhY2VXaXRoKHJlc3VsdC5odG1sKTtcblxuXHRcdFx0XHRcdCQoZG9jdW1lbnQpLnRyaWdnZXIoJ3JvY2tldC1pbnNpZ2h0cy1wYWdlLXRlc3QtcG9sbGluZycsIFtyZXN1bHQuaWRdKTtcblxuXHRcdFx0XHRcdC8vIFRyaWdnZXIgY3VzdG9tIGV2ZW50IG9ubHkgd2hlbiB0ZXN0IGlzIGNvbXBsZXRlZCBhbmQgbm90IGZhaWxlZCwgc28gd2UgZG9uJ3QgdGFyZ2V0IGFuIGVsZW1lbnQgdGhhdCBtaWdodCBiZSByZW1vdmVkIGZyb20gdGhlIERPTSBhZnRlciB0ZXN0IGNvbXBsZXRpb24uXG5cdFx0XHRcdFx0aWYgKHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnKSB7XG5cdFx0XHRcdFx0XHQkKGRvY3VtZW50KS50cmlnZ2VyKCdyb2NrZXQtaW5zaWdodHMtcGFnZS10ZXN0LWNvbXBsZXRlZCcsIFtyZXN1bHQuaWRdKTtcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRpZiAocmVzdWx0LnN0YXR1cyA9PT0gJ2NvbXBsZXRlZCcgfHwgcmVzdWx0LnN0YXR1cyA9PT0gJ2ZhaWxlZCcpIHtcblx0XHRcdFx0XHRcdHJlbW92ZUlkKHJlc3VsdC5pZCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9KTtcblxuXHRcdFx0XHRpbmNyZW1lbnRQb2xsaW5nKCk7XG5cdFx0XHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Ly8gT24gZXJyb3IsIGNsZWFyIElEcyBhbmQgc3RvcCBwb2xsaW5nXG5cdFx0XHRcdHJvY2tldEluc2lnaHRzSWRzID0gW107XG5cdFx0XHRcdHJlc2V0UG9sbGluZygpO1xuXHRcdFx0XHRjb25zb2xlLmVycm9yKCdQb2xsaW5nIGVycm9yOicsIHJlc3BvbnNlLnJlc3VsdHMgfHwgcmVzcG9uc2UpO1xuXHRcdFx0fVxuXHRcdH0gKTtcblx0fVxuXG5cdGZ1bmN0aW9uIGhhbmRsZUFkZFBhZ2UoZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdC8vIGNoZWNrIGlmIGhhcyBhdHRyIGRpc2FibGVkXG5cdFx0aWYgKCQodGhpcykuYXR0cignZGlzYWJsZWQnKSkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IHBhZ2VVcmwgPSAkcGFnZVVybElucHV0LnZhbCgpLnRyaW0oKTtcblxuXHRcdGlmICghaXNWYWxpZFVybChwYWdlVXJsKSkge1xuXHRcdFx0YWxlcnQoJ1BsZWFzZSBlbnRlciBhIHZhbGlkIFVSTCcpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IHNvdXJjZSA9ICQodGhpcykuZGF0YSgnc291cmNlJyk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nLFxuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0ZGF0YToge1xuXHRcdFx0XHRcdHBhZ2VfdXJsOiBwYWdlVXJsLFxuXHRcdFx0XHRcdHNvdXJjZTogc291cmNlXG5cdFx0XHRcdH0sXG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcblx0XHRcdFx0aWYgKCAhIGhhc0lkKHJlc3BvbnNlLmlkKSApIHtcblx0XHRcdFx0XHQkcGFnZVVybElucHV0LnZhbCgnJyk7XG5cdFx0XHRcdFx0JHRhYmxlQm9keS5hcHBlbmQocmVzcG9uc2UuaHRtbCk7XG5cblx0XHRcdFx0XHQvLyBDdXN0b20gZXZlbnQgd2hlbiBuZXcgcGFnZSBpcyBhZGRlZC5cblx0XHRcdFx0XHQkKGRvY3VtZW50KS50cmlnZ2VyKCdyb2NrZXQtaW5zaWdodHMtcGFnZS1hZGRlZCcpO1xuXG5cdFx0XHRcdFx0JHRhYmxlLnJlbW92ZUNsYXNzKCdoaWRkZW4nKTtcblx0XHRcdFx0XHRhZGRJZHMocmVzcG9uc2UuaWQpO1xuXHRcdFx0XHRcdGxldCBwYWdlc19udW1fY29udGFpbmVyID0gJCgnI3JvY2tldF9yb2NrZXRfaW5zaWdodHNfcGFnZXNfbnVtJyk7XG5cdFx0XHRcdFx0cGFnZXNfbnVtX2NvbnRhaW5lci50ZXh0KCBwYXJzZUludCggcGFnZXNfbnVtX2NvbnRhaW5lci50ZXh0KCkgKSArIDEgKTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdHVzXG5cdFx0XHRcdFx0dXBkYXRlQ3JlZGl0U3RhdGUocmVzcG9uc2UuaGFzX2NyZWRpdCk7XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIGRhdGEuXG5cdFx0XHRcdFx0Z2xvYmFsU2NvcmVEYXRhID0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGE7XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIHJvdyBpbiB0YWJsZSBpZiBvbiBSb2NrZXQgSW5zaWdodHMgcGFnZS5cblx0XHRcdFx0XHR1cGRhdGVHbG9iYWxTY29yZVJvdyhnbG9iYWxTY29yZURhdGEpO1xuXG5cdFx0XHRcdFx0aWYgKCdkaXNhYmxlZF9idG5faHRtbCcgaW4gZ2xvYmFsU2NvcmVEYXRhKSB7XG5cdFx0XHRcdFx0XHQkKCcjd3ByX3JvY2tldF9pbnNpZ2h0c19hZGRfcGFnZV9idG5fd3JhcHBlcicpLmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmRpc2FibGVkX2J0bl9odG1sLnJvY2tldF9pbnNpZ2h0cyk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Ly8gU2hvdy9oaWRlIHF1b3RhIGJhbm5lciBiYXNlZCBvbiBjYW5fYWRkX3BhZ2VzXG5cdFx0XHRcdFx0dXBkYXRlUXVvdGFCYW5uZXIocmVzcG9uc2UuY2FuX2FkZF9wYWdlcyk7XG5cblx0XHRcdFx0XHRpZiAocG9sbFRpbWVyKSB7XG5cdFx0XHRcdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0c2NoZWR1bGVQb2xsaW5nKCk7XG5cdFx0XHRcdH1cblxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Ly8gQ2xlYXIgdGhlIGlucHV0IGZpZWxkIG9uIGVycm9yXG5cdFx0XHRcdCRwYWdlVXJsSW5wdXQudmFsKCcnKTtcblxuXHRcdFx0XHQvLyBIYW5kbGUgVVJMIGxpbWl0IHJlYWNoZWQgZXJyb3Jcblx0XHRcdFx0aWYgKHJlc3BvbnNlPy5tZXNzYWdlICYmIHJlc3BvbnNlLm1lc3NhZ2UuaW5jbHVkZXMoJ01heGltdW0gbnVtYmVyIG9mIFVSTHMgcmVhY2hlZCcpKSB7XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIFVJIHN0YXRlIHRvIHJlZmxlY3QgVVJMIGxpbWl0IGhhcyBiZWVuIHJlYWNoZWRcblx0XHRcdFx0XHRkaXNhYmxlQWRkVXJsRWxlbWVudHMoKTtcblx0XHRcdFx0XHQvLyBTaG93IHF1b3RhIGJhbm5lciAoY2FuX2FkZF9wYWdlcyA9IGZhbHNlKVxuXHRcdFx0XHRcdHVwZGF0ZVF1b3RhQmFubmVyKHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMgIT09IHVuZGVmaW5lZCA/IHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMgOiBmYWxzZSk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRjb25zb2xlLmVycm9yKHJlc3BvbnNlPy5tZXNzYWdlIHx8IHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdGZ1bmN0aW9uIGhhbmRsZVJlc2V0UGFnZShlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0Y29uc3QgJGJ1dHRvbiA9ICQodGhpcyk7XG5cdFx0bGV0IGlkID0gJGJ1dHRvbi5wYXJlbnRzKCcud3ByLXJpLWl0ZW0nKS5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblx0XHRpZiAoICEgaWQgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Y29uc3Qgc291cmNlID0gJGJ1dHRvbi5kYXRhKCdzb3VyY2UnKTtcblxuXHRcdHdpbmRvdy53cC5hcGlGZXRjaChcblx0XHRcdHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzLycgKyBpZCxcblx0XHRcdFx0bWV0aG9kOiAnUEFUQ0gnLFxuXHRcdFx0XHRkYXRhOiB7XG5cdFx0XHRcdFx0c291cmNlOiBzb3VyY2Vcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdGFkZElkcyhyZXNwb25zZS5pZCk7XG5cblx0XHRcdFx0JChgI3JpX2RldGFpbHNfJHtyZXNwb25zZS5pZH0gLmRldGFpbHMtc2VjdGlvbi10ZGApLnJlbW92ZSgpO1xuXHRcdFx0XHRjb25zdCAkcm93ID0gJChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtyZXNwb25zZS5pZH1cIl1gKTtcblx0XHRcdFx0JHJvdy5yZXBsYWNlV2l0aChyZXNwb25zZS5odG1sKTtcblxuXHRcdFx0XHQvLyBDdXN0b20gZXZlbnQgd2hlbiBwYWdlIGlzIHJldGVzdGVkLlxuICAgICAgICBcdFx0JChkb2N1bWVudCkudHJpZ2dlcigncm9ja2V0LWluc2lnaHRzLXBhZ2UtcmV0ZXN0JywgW3Jlc3BvbnNlLmlkXSk7XG5cblx0XHRcdFx0Ly8gVXBkYXRlIGNyZWRpdCBzdGF0dXNcblx0XHRcdFx0dXBkYXRlQ3JlZGl0U3RhdGUocmVzcG9uc2UuaGFzX2NyZWRpdCk7XG5cblx0XHRcdFx0Ly8gVXBkYXRlIHF1b3RhIGJhbm5lciB2aXNpYmlsaXR5XG5cdFx0XHRcdHVwZGF0ZVF1b3RhQmFubmVyKHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMpO1xuXG4gICAgICAgICAgICAgICAgLy8gVXBkYXRlIGdsb2JhbCBzY29yZSBkYXRhLlxuICAgICAgICAgICAgICAgIGdsb2JhbFNjb3JlRGF0YSA9IHJlc3BvbnNlLmdsb2JhbF9zY29yZV9kYXRhO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgcm93IGluIHRhYmxlIGlmIG9uIFJvY2tldCBJbnNpZ2h0cyBwYWdlLlxuXHRcdFx0XHR1cGRhdGVHbG9iYWxTY29yZVJvdyhnbG9iYWxTY29yZURhdGEpO1xuXG5cdFx0XHRcdGlmIChwb2xsVGltZXIpIHtcblx0XHRcdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRzY2hlZHVsZVBvbGxpbmcoKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGNvbnNvbGUuZXJyb3IocmVzcG9uc2U/Lm1lc3NhZ2UgfHwgcmVzcG9uc2UpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0Ly8gPT09PSBJbml0aWFsaXphdGlvbiA9PT09XG5cdC8vIEJpbmQgZXZlbnRcblx0JChkb2N1bWVudCkub24oICdjbGljaycsICcjd3ByLWFjdGlvbi1hZGRfcGFnZV9zcGVlZF9yYWRhcicsIGhhbmRsZUFkZFBhZ2UgKTtcblx0JChkb2N1bWVudCkub24oICdjbGljaycsICcud3ByLWFjdGlvbi1zcGVlZF9yYWRhcl9yZWZyZXNoJywgaGFuZGxlUmVzZXRQYWdlICk7XG5cdC8vIEhhbmRsZSBFbnRlciBrZXkgcHJlc3Mgb24gcGFnZSB1cmwgaW5wdXQuXG5cdCQoZG9jdW1lbnQpLm9uKCAna2V5cHJlc3MnLCAnI3dwci1zcGVlZC1yYWRhci11cmwtaW5wdXQnLCBmdW5jdGlvbihlKSB7XG5cdFx0aWYgKGUua2V5ID09PSAnRW50ZXInKSB7XG5cdFx0ICBlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0ICAkKCcjd3ByLWFjdGlvbi1hZGRfcGFnZV9zcGVlZF9yYWRhcicpLmNsaWNrKCk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBPbmx5IHBvbGwgaWYgb24gYSB3cHIgc2VjdGlvbiB0aGF0IHJlcXVpcmVzIHBvbGxpbmcoZGFzaGJvYXJkfHJvY2tldF9pbnNpZ2h0cykgKG1vcmUgcm9idXN0IGNoZWNrKVxuICAgIGZ1bmN0aW9uIGlzVmFsaWRQYWdlRm9yUG9sbGluZygpIHtcbiAgICAgICAgY29uc3QgdXJsUGFyYW1zID0gbmV3IFVSTFNlYXJjaFBhcmFtcyh3aW5kb3cubG9jYXRpb24uc2VhcmNoKTtcblx0XHRyZXR1cm4gJ3dwcm9ja2V0JyA9PT0gdXJsUGFyYW1zLmdldCgncGFnZScpO1xuICAgIH1cblxuXHQvLyBSZXN1bWUgcG9sbGluZyBpZiBuZWVkZWRcblx0aWYgKGlzVmFsaWRQYWdlRm9yUG9sbGluZygpICYmIHJvY2tldEluc2lnaHRzSWRzLmxlbmd0aCA+IDApIHtcblx0XHRpZiAocG9sbFRpbWVyKSB7XG5cdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHR9XG5cdFx0c2NoZWR1bGVQb2xsaW5nKCk7XG5cdH1cblxuXHQvLyBIYW5kbGUgVUkgdXBkYXRlIG9uIHRoZSByb2NrZXQgaW5zaWdodHMgdGFiIHdoZW4gXCJBZGQgUGFnZXNcIiBidXR0b24gb24gdGhlIGdsb2JhbCBzY29yZSB3aWRnZXQgaXMgY2xpY2tlZC5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcGVyY2VudGFnZS1zY29yZS13aWRnZXQgLndwci1yaS1hZGQtdXJsLWJ1dHRvbicsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICghdGhpcy50ZXh0Q29udGVudC5pbmNsdWRlcygnQWRkIFBhZ2VzJykpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBEZWxheSBVSSB1cGRhdGUgYSBiaXQgdGlsbCBlbGVtZW50IGlzIHZpc2libGUuXG5cdFx0c2V0VGltZW91dCgoKSA9PiB7XG5cdFx0XHR1cGRhdGVHbG9iYWxTY29yZVJvdyhnbG9iYWxTY29yZURhdGEpO1xuXHRcdH0sIDMwKTtcblx0fSk7XG5cblx0Ly8gSGFuZGxlIGNvbGxhcHNlZWQgc3R5bGluZyBmb3IgZmlyc3QgbG9hZCBvciBkeW5hbWljIHJvdyBhZGRpdGlvbi5cblx0ZnVuY3Rpb24gYWRkQ29sbGFwc2VkU3R5bGluZ1RvTGFzdFJvdyhvbkxvYWQgPSBmYWxzZSkge1xuXHRcdCQoJy53cHItcmktaXRlbScpLmxhc3QoKS5maW5kKCd0ZCcpLmFkZENsYXNzKCdib3JkZXItYm90dG9tJyk7XG5cblx0XHRpZiAob25Mb2FkKSB7XG5cdFx0XHQvLyBPbiBsb2FkLCByZW1vdmUgd3ByLWxhc3QtZXhwYW5kZWQgZnJvbSBlbGVtZW50cyB0aGF0IGFyZSBub3QgdGhlIGxhc3QgYWZ0ZXIgYmVpbmcgYWRkZWQgZnJvbSBiYWNrZW5kLlxuXHRcdFx0JCgnLndwci1yaS1pdGVtLXRvZ2dsZScpLm5vdCgnOmxhc3QnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtZXhwYW5kZWQnKTtcblx0XHRcdCQoJy53cHItcmktaXRlbS1hY3Rpb25zJykubm90KCc6bGFzdCcpLnJlbW92ZUNsYXNzKCd3cHItbGFzdC1leHBhbmRlZCcpO1xuXHRcdFx0JCgnLmRldGFpbHMtc2VjdGlvbi10ZCcpLm5vdCgnOmxhc3QnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtZXhwYW5kZWQnKTtcblxuXHRcdFx0Ly8gQmFpbCBlYXJseSBpZiBsYXN0IGl0ZW0gaXMgYWxyZWFkeSBleHBhbmRlZCBvbiBsb2FkIHNvIGFzIG5vdCB0byBoYXZlIGNvbmZsaWN0aW5nIHN0eWxlcy5cblx0XHRcdGlmKCQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5sYXN0KCkuaGFzQ2xhc3MoJ3dwci1sYXN0LWV4cGFuZGVkJykpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdH1cblx0XHQkKCcud3ByLXJpLWl0ZW0tdG9nZ2xlJykubGFzdCgpLmFkZENsYXNzKCd3cHItbGFzdC1jb2xsYXBzZWQnKTtcblx0XHQkKCcud3ByLXJpLWl0ZW0tYWN0aW9ucycpLmxhc3QoKS5hZGRDbGFzcygnd3ByLWxhc3QtY29sbGFwc2VkJyk7XG5cdFx0JCgnLmRldGFpbHMtc2VjdGlvbi10ZCcpLmxhc3QoKS5hZGRDbGFzcygnd3ByLWxhc3QtY29sbGFwc2VkJyk7XG5cdH1cblxuXHQvLyBUb2dnbGVzIHRoZSB2aXNpYmlsaXR5IG9mIGEgc2luZ2xlIHRlc3QgZGV0YWlscyByb3csIHN3aXRjaGVzIHRoZSBjYXJldCBpY29uLCBhbmQgdXBkYXRlcyBzdHlsaW5nIGZvciB0aGUgbGFzdCBpdGVtLlxuXHRmdW5jdGlvbiB0b2dnbGVTaW5nbGVSb3dWaXNpYmlsaXR5KGluc2lnaHRzSWQsIHNvdXJjZSkge1xuXHRcdGxldCAkZWxlbWVudCA9ICQoYCNyaV9kZXRhaWxzXyR7aW5zaWdodHNJZH1gKTtcblx0XHRsZXQgaXNWaXNpYmxlID0gJGVsZW1lbnQuaGFzQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdGxldCBpc0xhc3QgPSAkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke2luc2lnaHRzSWR9XCJdIC53cHItcmktaXRlbS10b2dnbGUtc2luZ2xlYCkuaXMoJCgnLndwci1yaS1pdGVtLXRvZ2dsZS1zaW5nbGUnKS5sYXN0KCkpO1xuXG5cdFx0aWYgKCBpc1Zpc2libGUgKSB7XG5cdFx0XHQkZWxlbWVudC5yZW1vdmVDbGFzcygnd3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJyk7XG5cdFx0XHQkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke2luc2lnaHRzSWR9XCJdYCkucmVtb3ZlQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXHRcdFx0Ly8gTWFuaXB1bGF0ZSBzdHlsaW5nIGZvciBsYXN0IGVsZW1lbnRzIHdoZW4gZGV0YWlscyBjZWxsIGlzIG5vdCB2aXNpYmxlLlxuXHRcdFx0aWYgKGlzTGFzdCkge1xuXHRcdFx0XHR1cGRhdGVSb3dTdHlsaW5nRm9yTGFzdEl0ZW0oZmFsc2UpO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0JGVsZW1lbnQuYWRkQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdCQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7aW5zaWdodHNJZH1cIl1gKS5hZGRDbGFzcygnd3ByLXJpLWl0ZW0tLWV4cGFuZGVkJyk7XG5cblx0XHQvLyBUcmFjayBleHBhbmQgb25seSBleHBhbmQgbWV0cmljIGFjdGlvbi5cblx0XHRoYW5kbGVNZXRyaWNBY3Rpb25UcmFja2luZygnZXhwYW5kJywgaW5zaWdodHNJZCwgc291cmNlKTtcblxuXHRcdGlmIChpc0xhc3QpIHtcblx0XHRcdHVwZGF0ZVJvd1N0eWxpbmdGb3JMYXN0SXRlbSgpO1xuXHRcdH1cblx0fVxuXG5cdC8vIFRyYWNrcyB1c2VyIGludGVyYWN0aW9ucyB3aXRoIG1ldHJpYyBhY3Rpb25zIGluIFJvY2tldCBJbnNpZ2h0cyB2aWEgQUpBWC5cblx0ZnVuY3Rpb24gaGFuZGxlTWV0cmljQWN0aW9uVHJhY2tpbmcoZXZlbnQsIHJvd0lkLCBzb3VyY2UpIHtcblx0XHQkLnBvc3QoXG5cdFx0XHRhamF4dXJsLFxuXHRcdFx0e1xuXHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfaW5zaWdodF90cmFja19tZXRyaWNfYWN0aW9ucycsXG5cdFx0XHRcdF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuXHRcdFx0XHRldmVudDogZXZlbnQsXG5cdFx0XHRcdHJvd19pZDogcm93SWQsXG5cdFx0XHRcdHNvdXJjZTogc291cmNlXG5cdFx0XHR9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCFyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcignTWV0cmljIGFjdGlvbiB0cmFja2luZyBmYWlsZWQ6JywgcmVzcG9uc2U/LmRhdGEgfHwgcmVzcG9uc2UpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBib3JkZXIgc3R5bGluZyBmb3IgdGhlIGxhc3Qgcm93IGl0ZW0gaW4gdGhlIFJvY2tldCBJbnNpZ2h0cyB0YWJsZS5cblx0ICpcblx0ICogTWFuYWdlcyBib3JkZXIgcmFkaXVzIGFuZCBib3R0b20gYm9yZGVyIHN0eWxpbmcgYmFzZWQgb24gd2hldGhlciB0aGUgZGV0YWlsc1xuXHQgKiBjZWxsIGlzIGV4cGFuZGVkIG9yIGNvbGxhcHNlZCB0byBtYWludGFpbiBwcm9wZXIgdmlzdWFsIGFwcGVhcmFuY2UuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVSb3dTdHlsaW5nRm9yTGFzdEl0ZW0oaXNFeHBhbmRlZCA9IHRydWUpIHtcblx0XHRjb25zdCBhZGRTdGF0ZSA9IGlzRXhwYW5kZWQgPyAnd3ByLWxhc3QtZXhwYW5kZWQnIDogJ3dwci1sYXN0LWNvbGxhcHNlZCc7XG5cdFx0Y29uc3QgcmVtb3ZlU3RhdGUgPSBpc0V4cGFuZGVkID8gJ3dwci1sYXN0LWNvbGxhcHNlZCcgOiAnd3ByLWxhc3QtZXhwYW5kZWQnO1xuXG5cdFx0dmFyICRzZWxlY3RvcnMgPSB7XG5cdFx0XHRsYXN0VG9nZ2xlOiAkKCcud3ByLXJpLWl0ZW0tdG9nZ2xlJykubGFzdCgpLFxuXHRcdFx0bGFzdEFjdGlvbnM6ICQoJy53cHItcmktaXRlbS1hY3Rpb25zJykubGFzdCgpXG5cdFx0fTtcblxuXHRcdCRzZWxlY3RvcnMubGFzdFRvZ2dsZVxuXHRcdFx0LnJlbW92ZUNsYXNzKHJlbW92ZVN0YXRlKVxuXHRcdFx0LmFkZENsYXNzKGFkZFN0YXRlKTtcblxuXHRcdCRzZWxlY3RvcnMubGFzdEFjdGlvbnNcblx0XHRcdC5yZW1vdmVDbGFzcyhyZW1vdmVTdGF0ZSlcblx0XHRcdC5hZGRDbGFzcyhhZGRTdGF0ZSk7XG5cblxuXHRcdC8vIENoZWNrIGlmIGxhc3QgZGV0YWlsIHJvdyBpcyBub3QgdGhlIGxhc3Qgcm93IGluIHRoZSB0YWJsZSBzbyBhcyBub3QgdG8gYXBwbHkgaW1wcm9wZXIgc3R5bGluZyB3aXRoIGJvcmRlciByYWRpdXMgYmV0d2VlbiByb3dzLlxuXHRcdHZhciAkbGFzdERldGFpbHNDZWxsID0gJCgnLmRldGFpbHMtc2VjdGlvbi10ZCcpLmxhc3QoKTtcblx0XHRpZiAoJGxhc3REZXRhaWxzQ2VsbC5jbG9zZXN0KCd0cicpLm5leHQoJ3RyJykubGVuZ3RoICE9PSAwKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0JGxhc3REZXRhaWxzQ2VsbC5yZW1vdmVDbGFzcyhyZW1vdmVTdGF0ZSlcblx0XHQuYWRkQ2xhc3MoYWRkU3RhdGUpO1xuXHR9XG5cblx0Ly8gVG9nZ2xlIHNpbmdsZSBpdGVtLlxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1pdGVtLXRvZ2dsZS1zaW5nbGUnLCBmdW5jdGlvbigpIHtcblx0XHR2YXIgaW5zaWdodHNJZCA9ICQodGhpcykuY2xvc2VzdCgnLndwci1yaS1pdGVtJykuZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0dG9nZ2xlU2luZ2xlUm93VmlzaWJpbGl0eShpbnNpZ2h0c0lkLCAndXJsX2V4cGFuZCcpO1xuXHR9KTtcblxuXHQvLyBUb2dnbGUgYWxsIGl0ZW1zLlxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1pdGVtLXRvZ2dsZS1hbGwnLCBmdW5jdGlvbiAoKSB7XG5cdFx0aWYgKCQoJy53cHItcmktZGV0YWlscy0tZXhwYW5kZWQnKS5sZW5ndGggPiAwKSB7XG5cdFx0XHQkKCcud3ByLXJpLWRldGFpbHMnKS5yZW1vdmVDbGFzcygnd3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJyk7XG5cdFx0XHQkKCcud3ByLXJpLWl0ZW0nKS5yZW1vdmVDbGFzcygnd3ByLXJpLWl0ZW0tLWV4cGFuZGVkJyk7XG5cdFx0XHQkKHRoaXMpLnJlbW92ZUNsYXNzKCd3cHItcmktaXRlbS10b2dnbGUtYWxsLS1leHBhbmRlZCcpO1xuXHRcdFx0dXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKGZhbHNlKTtcblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCQoJy53cHItcmktZGV0YWlscycpLmFkZENsYXNzKCd3cHItcmktZGV0YWlscy0tZXhwYW5kZWQnKTtcblx0XHQkKCcud3ByLXJpLWl0ZW0nKS5hZGRDbGFzcygnd3ByLXJpLWl0ZW0tLWV4cGFuZGVkJyk7XG5cdFx0JCh0aGlzKS5hZGRDbGFzcygnd3ByLXJpLWl0ZW0tdG9nZ2xlLWFsbC0tZXhwYW5kZWQnKTtcblx0XHR1cGRhdGVSb3dTdHlsaW5nRm9yTGFzdEl0ZW0oKTtcblxuXHRcdC8vIFRyYWNrIHNpbmdsZSBleHBhbmQgZXZlbnQgZm9yIFwiRXhwYW5kIEFsbFwiIGFjdGlvbiB3aXRoIHRlc3RfaWQgYXMgJ2FsbCcuXG5cdFx0aGFuZGxlTWV0cmljQWN0aW9uVHJhY2tpbmcoJ2V4cGFuZCcsICdhbGwnLCAnZ2xvYmFsX2V4cGFuZCcpO1xuXHR9KTtcblxuXHQvLyBUcmFjayBcIlNlZSBSZXBvcnRcIiBjbGlja3MgaW4gUm9ja2V0IEluc2lnaHRzLlxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yaS1yZXBvcnQnLCBmdW5jdGlvbihlKSB7XHQvLyBPbmx5IHRyYWNrIGlmIGxpbmsgaXMgbm90IGRpc2FibGVkIGFuZCBtaXhwYW5lbCBpcyBhdmFpbGFibGUuXG5cdFx0aWYgKCQodGhpcykuaGFzQ2xhc3MoJ3dwci1yaS1hY3Rpb24tLWRpc2FibGVkJykpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR2YXIgaW5zaWdodHNJZCA9ICQodGhpcykuZGF0YSgncm9ja2V0LWluc2lnaHRzLXJvdy1pZCcpO1xuXHRcdGhhbmRsZU1ldHJpY0FjdGlvblRyYWNraW5nKCdzZWVfcmVwb3J0JywgaW5zaWdodHNJZCwgJ3NlZV9yZXBvcnRfYnV0dG9uJyk7XG5cdH0pO1xuXG5cdC8vIFVwZGF0ZSB0YWJsZSBzdHlsaW5nIGFmdGVyIG5ldyBwYWdlIGlzIGFkZGVkLlxuXHQkKGRvY3VtZW50KS5vbigncm9ja2V0LWluc2lnaHRzLXBhZ2UtdGVzdC1jb21wbGV0ZWQnLCBmdW5jdGlvbiAoZSwgaW5zaWdodHNJZCkge1xuXHRcdC8vIEJhaWwgb3V0IGlmIHRoZXJlIGlzIG1vcmUgdGhhbiAxIHJlc3VsdC5cblx0XHRpZiAoJCgnLndwci1yaS1pdGVtLXJlc3VsdCcpLmxlbmd0aCA+IDEpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBSZW1vdmUgZHluYW1pYyBjbGFzcyB3aGVuIG9ubHkgb25lIGl0ZW0gZXhpc3QgaW4gdGFibGUuXG5cdFx0JCgnLndwci1sYXN0LWNvbGxhcHNlZCcpLnJlbW92ZUNsYXNzKCd3cHItbGFzdC1jb2xsYXBzZWQnKTtcblx0fSk7XG5cblx0Ly8gVXBkYXRlIHRhYmxlIHN0eWxpbmcgYWZ0ZXIgbmV3IHBhZ2UgaXMgYWRkZWQuXG5cdCQoZG9jdW1lbnQpLm9uKCdyb2NrZXQtaW5zaWdodHMtcGFnZS1hZGRlZCcsIGZ1bmN0aW9uIChlKSB7XG5cdFx0Ly8gUmVtb3ZlIGR5bmFtaWMgY2xhc3MgZm9yIGxhc3QgaXRlbSBpZiBleGlzdHMgd2hlbiBuZXcgcGFnZSBpcyBhZGRlZC5cblx0XHQkKCcud3ByLWxhc3QtY29sbGFwc2VkJykucmVtb3ZlQ2xhc3MoJ3dwci1sYXN0LWNvbGxhcHNlZCcpO1xuXHRcdCQoJy53cHItbGFzdC1leHBhbmRlZCcpLnJlbW92ZUNsYXNzKCd3cHItbGFzdC1leHBhbmRlZCcpO1xuXHRcdCQoJy5ib3JkZXItYm90dG9tJykucmVtb3ZlQ2xhc3MoJ2JvcmRlci1ib3R0b20nKTtcblx0fSk7XG5cblx0Ly8gVXBkYXRlIHRhYmxlIHN0eWxpbmcgYWZ0ZXIgcmV0ZXN0IG9yIHBvbGxpbmcgdXBkYXRlIGZvciBsYXN0IHJvdy5cblx0JChkb2N1bWVudCkub24oJ3JvY2tldC1pbnNpZ2h0cy1wYWdlLXJldGVzdCByb2NrZXQtaW5zaWdodHMtcGFnZS10ZXN0LXBvbGxpbmcnLCBmdW5jdGlvbiAoZSwgaW5zaWdodHNJZCkge1xuXHRcdC8vIENoZWNrIGlmIGl0ZW0gaXMgdGhlIGxhc3QuXG5cdFx0dmFyIGlzTGFzdCA9ICQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7aW5zaWdodHNJZH1cIl1gKS5pcygkKCcud3ByLXJpLWl0ZW0tcmVzdWx0JykubGFzdCgpKTtcblxuXHRcdGlmIChpc0xhc3QpIHtcblx0XHRcdGFkZENvbGxhcHNlZFN0eWxpbmdUb0xhc3RSb3coKTtcblx0XHR9XG5cdH0pO1xuXG5cdCQoZG9jdW1lbnQpLm9uKCdyb2NrZXQtaW5zaWdodHMtcGFnZS1yZXRlc3QnLCBmdW5jdGlvbiAoZSwgaW5zaWdodHNJZCkge1xuXHRcdCQoYCNyaV9kZXRhaWxzXyR7aW5zaWdodHNJZH1gKS5yZW1vdmVDbGFzcygnd3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJyk7XG5cdFx0JChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtpbnNpZ2h0c0lkfVwiXWApLnJlbW92ZUNsYXNzKCd3cHItcmktaXRlbS0tZXhwYW5kZWQnKTtcblx0fSk7XG5cblx0JCh3aW5kb3cpLmxvYWQoKCkgPT4ge1xuXHRcdGlmICggISBpc09uUm9ja2V0SW5zaWdodHMoKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBBZGQgY29sbGFwc2VkIHN0eWxpbmcgdG8gdGhlIGxhc3Qgcm93IG9uIGluaXRpYWwgbG9hZC5cblx0XHRhZGRDb2xsYXBzZWRTdHlsaW5nVG9MYXN0Um93KHRydWUpO1xuXG5cdFx0Ly8gU2V0IGluaXRpYWwgZXhwYW5kL2NvbGxhcHNlIHN0YXRlLlxuXHRcdGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMod2luZG93LmxvY2F0aW9uLnNlYXJjaCk7XG5cdFx0Y29uc3QgdGVzdElkID0gdXJsUGFyYW1zLmdldCgncmlfaWQnKTtcblxuXHRcdC8vIFNlbmQgbWl4cGFuZWwgZXZlbnQgZm9yIGF1dG8gZXhwYW5kZWQgcm93LlxuXHRcdGxldCBmaXJzdFJvd0lkID0gJCgnLndwci1yaS1pdGVtLS1leHBhbmRlZCcpPy5maXJzdCgpPy5kYXRhKCdyb2NrZXQtaW5zaWdodHMtaWQnKTtcblxuXHRcdC8vIENoZWNrIGlmIHJpX2lkIHdhcyBwYXNzZWQgaW4gcXVlcnkgc3RyaW5nIHRvIG9wZW4gc3BlY2lmaWMgdGVzdC5cblx0XHRpZiAoIXRlc3RJZCB8fCB0ZXN0SWQgPT09ICcnKSB7XG5cdFx0XHRpZiAoZmlyc3RSb3dJZCkge1xuXHRcdFx0XHRoYW5kbGVNZXRyaWNBY3Rpb25UcmFja2luZygnZXhwYW5kJywgZmlyc3RSb3dJZCwgJ2F1dG9fZXhwYW5kX3VybCcpO1xuXHRcdFx0fVxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGhhbmRsZU1ldHJpY0FjdGlvblRyYWNraW5nKCdleHBhbmQnLCB0ZXN0SWQsICdwb3N0IHR5cGUgbGlzdGluZycpO1xuXG5cdFx0JCgnaHRtbCwgYm9keScpLmFuaW1hdGUoe1xuXHRcdFx0c2Nyb2xsVG9wOiAkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke3Rlc3RJZH1cIl1gKS5vZmZzZXQoKS50b3AgLSAxMDBcblx0XHR9LCA1MDApO1xuXG5cdFx0Ly8gUmVtb3ZlIHJpX2lkIGZyb20gVVJMIHdpdGhvdXQgcGFnZSByZWxvYWRcblx0XHR1cmxQYXJhbXMuZGVsZXRlKCdyaV9pZCcpO1xuXHRcdGNvbnN0IG5ld1VybCA9IHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZSArICc/JyArIHVybFBhcmFtcy50b1N0cmluZygpICsgd2luZG93LmxvY2F0aW9uLmhhc2g7XG5cdFx0d2luZG93Lmhpc3RvcnkucmVwbGFjZVN0YXRlKHt9LCAnJywgbmV3VXJsKTtcblx0fSk7XG59KTtcbiIsIi8vIEFkZCBncmVlbnNvY2sgbGliIGZvciBhbmltYXRpb25zXHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9wbHVnaW5zL0NTU1BsdWdpbi5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svcGx1Z2lucy9TY3JvbGxUb1BsdWdpbi5taW4uanMnO1xyXG5cclxuLy8gQWRkIHNjcmlwdHNcclxuaW1wb3J0ICcuLi9nbG9iYWwvcGFnZU1hbmFnZXIuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9tYWluLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvZmllbGRzLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvYmVhY29uLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvYWpheC5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL3JlY29tbWVuZGF0aW9ucy13aWRnZXQuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9yb2NrZXRjZG4uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9jb3VudGRvd24uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9taXhwYW5lbC5qcyciLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG4gICAgaWYgKCdCZWFjb24nIGluIHdpbmRvdykge1xuICAgICAgICAvKipcbiAgICAgICAgICogU2hvdyBiZWFjb25zIG9uIGJ1dHRvbiBcImhlbHBcIiBjbGlja1xuICAgICAgICAgKi9cbiAgICAgICAgdmFyICRoZWxwID0gJCgnLndwci1pbmZvQWN0aW9uLS1oZWxwJyk7XG4gICAgICAgICRoZWxwLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpe1xuICAgICAgICAgICAgdmFyIGlkcyA9ICQodGhpcykuZGF0YSgnYmVhY29uLWlkJyk7XG4gICAgICAgICAgICB2YXIgYnV0dG9uID0gJCh0aGlzKS5kYXRhKCd3cHJfdHJhY2tfYnV0dG9uJykgfHwgJ0JlYWNvbiBIZWxwJztcbiAgICAgICAgICAgIHZhciBjb250ZXh0ID0gJCh0aGlzKS5kYXRhKCd3cHJfdHJhY2tfY29udGV4dCcpIHx8ICdTZXR0aW5ncyc7XG5cbiAgICAgICAgICAgIC8vIFRyYWNrIHdpdGggTWl4UGFuZWwgSlMgU0RLXG4gICAgICAgICAgICB3cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KTtcblxuICAgICAgICAgICAgLy8gQ29udGludWUgd2l0aCBleGlzdGluZyBiZWFjb24gZnVuY3Rpb25hbGl0eVxuICAgICAgICAgICAgd3ByQ2FsbEJlYWNvbihpZHMpO1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9KTtcblxuICAgICAgICBmdW5jdGlvbiB3cHJDYWxsQmVhY29uKGFJRCl7XG4gICAgICAgICAgICBhSUQgPSBhSUQuc3BsaXQoJywnKTtcbiAgICAgICAgICAgIGlmICggYUlELmxlbmd0aCA9PT0gMCApIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICBpZiAoIGFJRC5sZW5ndGggPiAxICkge1xuICAgICAgICAgICAgICAgICAgICB3aW5kb3cuQmVhY29uKFwic3VnZ2VzdFwiLCBhSUQpO1xuXG4gICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB3aW5kb3cuQmVhY29uKFwib3BlblwiKTtcbiAgICAgICAgICAgICAgICAgICAgfSwgMjAwKTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICB3aW5kb3cuQmVhY29uKFwiYXJ0aWNsZVwiLCBhSUQudG9TdHJpbmcoKSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgIH1cbiAgICB9XG5cblx0JCggJy53cHItcmktcmVwb3J0JyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJUcmFja0hlbHBCdXR0b24oICdyb2NrZXQgaW5zaWdodHMgc2VlIHJlcG9ydCcsICdwZXJmb3JtYW5jZSBzdW1tYXJ5JyApO1xuXHR9ICk7XG5cbiAgICAvLyBNaXhQYW5lbCB0cmFja2luZyBmdW5jdGlvblxuICAgIGZ1bmN0aW9uIHdwclRyYWNrSGVscEJ1dHRvbihidXR0b24sIGNvbnRleHQpIHtcbiAgICAgICAgaWYgKHR5cGVvZiBtaXhwYW5lbCAhPT0gJ3VuZGVmaW5lZCcgJiYgbWl4cGFuZWwudHJhY2spIHtcbiAgICAgICAgICAgIC8vIENoZWNrIGlmIHVzZXIgaGFzIG9wdGVkIGluIHVzaW5nIGxvY2FsaXplZCBkYXRhXG4gICAgICAgICAgICBpZiAodHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhID09PSAndW5kZWZpbmVkJyB8fCAhcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCB8fCByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkID09PSAnMCcpIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIElkZW50aWZ5IHVzZXIgd2l0aCBoYXNoZWQgbGljZW5zZSBlbWFpbCBpZiBhdmFpbGFibGVcbiAgICAgICAgICAgIGlmIChyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICYmIHR5cGVvZiBtaXhwYW5lbC5pZGVudGlmeSA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICAgICAgICAgIG1peHBhbmVsLmlkZW50aWZ5KHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBtaXhwYW5lbC50cmFjaygnQnV0dG9uIENsaWNrZWQnLCB7XG4gICAgICAgICAgICAgICAgJ2J1dHRvbic6IGJ1dHRvbixcblx0XHRcdFx0J2J1dHRvbl9jb250ZXh0JzogY29udGV4dCxcblx0XHRcdFx0J3BsdWdpbic6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBsdWdpbixcbiAgICAgICAgICAgICAgICAnYnJhbmQnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5icmFuZCxcbiAgICAgICAgICAgICAgICAnYXBwbGljYXRpb24nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG4gICAgICAgICAgICAgICAgJ2NvbnRleHQnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuICAgICAgICAgICAgICAgICdwYXRoJzogcm9ja2V0X21peHBhbmVsX2RhdGEucGF0aFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBNYWtlIGZ1bmN0aW9uIGdsb2JhbGx5IGF2YWlsYWJsZVxuICAgIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPSB3cHJUcmFja0hlbHBCdXR0b247XG59KTtcbiIsImZ1bmN0aW9uIGdldFRpbWVSZW1haW5pbmcoZW5kdGltZSl7XG4gICAgY29uc3Qgc3RhcnQgPSBEYXRlLm5vdygpO1xuICAgIGNvbnN0IHRvdGFsID0gKGVuZHRpbWUgKiAxMDAwKSAtIHN0YXJ0O1xuICAgIGNvbnN0IHNlY29uZHMgPSBNYXRoLmZsb29yKCAodG90YWwvMTAwMCkgJSA2MCApO1xuICAgIGNvbnN0IG1pbnV0ZXMgPSBNYXRoLmZsb29yKCAodG90YWwvMTAwMC82MCkgJSA2MCApO1xuICAgIGNvbnN0IGhvdXJzID0gTWF0aC5mbG9vciggKHRvdGFsLygxMDAwKjYwKjYwKSkgJSAyNCApO1xuICAgIGNvbnN0IGRheXMgPSBNYXRoLmZsb29yKCB0b3RhbC8oMTAwMCo2MCo2MCoyNCkgKTtcblxuICAgIHJldHVybiB7XG4gICAgICAgIHRvdGFsLFxuICAgICAgICBkYXlzLFxuICAgICAgICBob3VycyxcbiAgICAgICAgbWludXRlcyxcbiAgICAgICAgc2Vjb25kc1xuICAgIH07XG59XG5cbmZ1bmN0aW9uIGluaXRpYWxpemVDbG9jayhpZCwgZW5kdGltZSkge1xuICAgIGNvbnN0IGNsb2NrID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpO1xuXG4gICAgaWYgKGNsb2NrID09PSBudWxsKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBkYXlzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLWRheXMnKTtcbiAgICBjb25zdCBob3Vyc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1ob3VycycpO1xuICAgIGNvbnN0IG1pbnV0ZXNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tbWludXRlcycpO1xuICAgIGNvbnN0IHNlY29uZHNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tc2Vjb25kcycpO1xuXG4gICAgZnVuY3Rpb24gdXBkYXRlQ2xvY2soKSB7XG4gICAgICAgIGNvbnN0IHQgPSBnZXRUaW1lUmVtYWluaW5nKGVuZHRpbWUpO1xuXG4gICAgICAgIGlmICh0LnRvdGFsIDwgMCkge1xuICAgICAgICAgICAgY2xlYXJJbnRlcnZhbCh0aW1laW50ZXJ2YWwpO1xuXG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBkYXlzU3Bhbi5pbm5lckhUTUwgPSB0LmRheXM7XG4gICAgICAgIGhvdXJzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5ob3Vycykuc2xpY2UoLTIpO1xuICAgICAgICBtaW51dGVzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5taW51dGVzKS5zbGljZSgtMik7XG4gICAgICAgIHNlY29uZHNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0LnNlY29uZHMpLnNsaWNlKC0yKTtcbiAgICB9XG5cbiAgICB1cGRhdGVDbG9jaygpO1xuICAgIGNvbnN0IHRpbWVpbnRlcnZhbCA9IHNldEludGVydmFsKHVwZGF0ZUNsb2NrLCAxMDAwKTtcbn1cblxuZnVuY3Rpb24gcnVjc3NUaW1lcihpZCwgZW5kdGltZSkge1xuXHRjb25zdCB0aW1lciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGlkKTtcblx0Y29uc3Qgbm90aWNlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JvY2tldC1ub3RpY2Utc2Fhcy1wcm9jZXNzaW5nJyk7XG5cdGNvbnN0IHN1Y2Nlc3MgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1zYWFzLXN1Y2Nlc3MnKTtcblxuXHRpZiAodGltZXIgPT09IG51bGwpIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVUaW1lcigpIHtcblx0XHRjb25zdCBzdGFydCA9IERhdGUubm93KCk7XG5cdFx0Y29uc3QgcmVtYWluaW5nID0gTWF0aC5mbG9vciggKCAoZW5kdGltZSAqIDEwMDApIC0gc3RhcnQgKSAvIDEwMDAgKTtcblxuXHRcdGlmIChyZW1haW5pbmcgPD0gMCkge1xuXHRcdFx0Y2xlYXJJbnRlcnZhbCh0aW1lckludGVydmFsKTtcblxuXHRcdFx0aWYgKG5vdGljZSAhPT0gbnVsbCkge1xuXHRcdFx0XHRub3RpY2UuY2xhc3NMaXN0LmFkZCgnaGlkZGVuJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmIChzdWNjZXNzICE9PSBudWxsKSB7XG5cdFx0XHRcdHN1Y2Nlc3MuY2xhc3NMaXN0LnJlbW92ZSgnaGlkZGVuJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmICggcm9ja2V0X2FqYXhfZGF0YS5jcm9uX2Rpc2FibGVkICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IGRhdGEgPSBuZXcgRm9ybURhdGEoKTtcblxuXHRcdFx0ZGF0YS5hcHBlbmQoICdhY3Rpb24nLCAncm9ja2V0X3NwYXduX2Nyb24nICk7XG5cdFx0XHRkYXRhLmFwcGVuZCggJ25vbmNlJywgcm9ja2V0X2FqYXhfZGF0YS5ub25jZSApO1xuXG5cdFx0XHRmZXRjaCggYWpheHVybCwge1xuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0Y3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG5cdFx0XHRcdGJvZHk6IGRhdGFcblx0XHRcdH0gKTtcblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRpbWVyLmlubmVySFRNTCA9IHJlbWFpbmluZztcblx0fVxuXG5cdHVwZGF0ZVRpbWVyKCk7XG5cdGNvbnN0IHRpbWVySW50ZXJ2YWwgPSBzZXRJbnRlcnZhbCggdXBkYXRlVGltZXIsIDEwMDApO1xufVxuXG5pZiAoIURhdGUubm93KSB7XG4gICAgRGF0ZS5ub3cgPSBmdW5jdGlvbiBub3coKSB7XG4gICAgICByZXR1cm4gbmV3IERhdGUoKS5nZXRUaW1lKCk7XG4gICAgfTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLnByb21vX2VuZCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBpbml0aWFsaXplQ2xvY2soJ3JvY2tldC1wcm9tby1jb3VudGRvd24nLCByb2NrZXRfYWpheF9kYXRhLnByb21vX2VuZCk7XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5saWNlbnNlX2V4cGlyYXRpb24gIT09ICd1bmRlZmluZWQnKSB7XG4gICAgaW5pdGlhbGl6ZUNsb2NrKCdyb2NrZXQtcmVuZXctY291bnRkb3duJywgcm9ja2V0X2FqYXhfZGF0YS5saWNlbnNlX2V4cGlyYXRpb24pO1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEubm90aWNlX2VuZF90aW1lICE9PSAndW5kZWZpbmVkJykge1xuICAgIHJ1Y3NzVGltZXIoJ3JvY2tldC1ydWNzcy10aW1lcicsIHJvY2tldF9hamF4X2RhdGEubm90aWNlX2VuZF90aW1lKTtcbn0iLCJpbXBvcnQgJy4uL2N1c3RvbS9jdXN0b20tc2VsZWN0LmpzJztcblxudmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXG5cbiAgICAvKioqXG4gICAgKiBDaGVjayBwYXJlbnQgLyBzaG93IGNoaWxkcmVuXG4gICAgKioqL1xuXG5cdGZ1bmN0aW9uIHdwclNob3dDaGlsZHJlbihhRWxlbSl7XG5cdFx0dmFyIHBhcmVudElkLCAkY2hpbGRyZW47XG5cblx0XHRhRWxlbSAgICAgPSAkKCBhRWxlbSApO1xuXHRcdHBhcmVudElkICA9IGFFbGVtLmF0dHIoJ2lkJyk7XG5cdFx0JGNoaWxkcmVuID0gJCgnW2RhdGEtcGFyZW50PVwiJyArIHBhcmVudElkICsgJ1wiXScpO1xuXG5cdFx0Ly8gVGVzdCBjaGVjayBmb3Igc3dpdGNoXG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRpZiAoICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0XHRcdHZhciBpZCA9ICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpO1xuXG5cdFx0XHRcdFx0JCgnW2RhdGEtcGFyZW50PVwiJyArIGlkICsgJ1wiXScpLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXHRcdH1cblx0XHRlbHNle1xuXHRcdFx0JGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR2YXIgaWQgPSAkKHRoaXMpLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKTtcblxuXHRcdFx0XHQkKCdbZGF0YS1wYXJlbnQ9XCInICsgaWQgKyAnXCJdJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdH0pO1xuXHRcdH1cblx0fVxuXG4gICAgLyoqXG4gICAgICogVGVsbCBpZiB0aGUgZ2l2ZW4gY2hpbGQgZmllbGQgaGFzIGFuIGFjdGl2ZSBwYXJlbnQgZmllbGQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0gIG9iamVjdCAkZmllbGQgQSBqUXVlcnkgb2JqZWN0IG9mIGEgXCIud3ByLWZpZWxkXCIgZmllbGQuXG4gICAgICogQHJldHVybiBib29sfG51bGxcbiAgICAgKi9cbiAgICBmdW5jdGlvbiB3cHJJc1BhcmVudEFjdGl2ZSggJGZpZWxkICkge1xuICAgICAgICB2YXIgJHBhcmVudDtcblxuICAgICAgICBpZiAoICEgJGZpZWxkLmxlbmd0aCApIHtcbiAgICAgICAgICAgIC8vIMKvXFxfKOODhClfL8KvXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgICRwYXJlbnQgPSAkZmllbGQuZGF0YSggJ3BhcmVudCcgKTtcblxuICAgICAgICBpZiAoIHR5cGVvZiAkcGFyZW50ICE9PSAnc3RyaW5nJyApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICRwYXJlbnQucmVwbGFjZSggL15cXHMrfFxccyskL2csICcnICk7XG5cbiAgICAgICAgaWYgKCAnJyA9PT0gJHBhcmVudCApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICQoICcjJyArICRwYXJlbnQgKTtcblxuICAgICAgICBpZiAoICEgJHBhcmVudC5sZW5ndGggKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIG1pc3Npbmc6IGxldCdzIGNvbnNpZGVyIGl0J3Mgbm90IGFjdGl2ZSB0aGVuLlxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCAhICRwYXJlbnQuaXMoICc6Y2hlY2tlZCcgKSAmJiAkcGFyZW50LmlzKCdpbnB1dCcpKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIGNoZWNrYm94IGFuZCBub3QgY2hlY2tlZDogZG9uJ3QgZGlzcGxheSB0aGUgZmllbGQgdGhlbi5cbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfVxuXG5cdFx0aWYgKCAhJHBhcmVudC5oYXNDbGFzcygncmFkaW8tYWN0aXZlJykgJiYgJHBhcmVudC5pcygnYnV0dG9uJykpIHtcblx0XHRcdC8vIFRoaXMgZmllbGQncyBwYXJlbnQgYnV0dG9uIGFuZCBpcyBub3QgYWN0aXZlOiBkb24ndCBkaXNwbGF5IHRoZSBmaWVsZCB0aGVuLlxuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cbiAgICAgICAgLy8gR28gcmVjdXJzaXZlIHRvIHRoZSBsYXN0IHBhcmVudC5cbiAgICAgICAgcmV0dXJuIHdwcklzUGFyZW50QWN0aXZlKCAkcGFyZW50LmNsb3Nlc3QoICcud3ByLWZpZWxkJyApICk7XG4gICAgfVxuXG5cdC8qKlxuXHQgKiBNYXNrcyBzZW5zaXRpdmUgaW5mb3JtYXRpb24gaW4gYW4gaW5wdXQgZmllbGQgYnkgcmVwbGFjaW5nIGFsbCBidXQgdGhlIGxhc3QgNCBjaGFyYWN0ZXJzIHdpdGggYXN0ZXJpc2tzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gaWRfc2VsZWN0b3IgLSBUaGUgSUQgb2YgdGhlIGlucHV0IGZpZWxkIHRvIGJlIG1hc2tlZC5cblx0ICogQHJldHVybnMge3ZvaWR9IC0gTW9kaWZpZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIGluLXBsYWNlLlxuXHQgKlxuXHQgKiBAZXhhbXBsZVxuXHQgKiAvLyBIVE1MOiA8aW5wdXQgdHlwZT1cInRleHRcIiBpZD1cImNyZWRpdENhcmRJbnB1dFwiIHZhbHVlPVwiMTIzNDU2Nzg5MDEyMzQ1NlwiPlxuXHQgKiBtYXNrRmllbGQoJ2NyZWRpdENhcmRJbnB1dCcpO1xuXHQgKiAvLyBSZXN1bHQ6IFVwZGF0ZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIHRvICcqKioqKioqKioqKiozNDU2Jy5cblx0ICovXG5cdGZ1bmN0aW9uIG1hc2tGaWVsZChwcm94eV9zZWxlY3RvciwgY29uY3JldGVfc2VsZWN0b3IpIHtcblx0XHR2YXIgY29uY3JldGUgPSB7XG5cdFx0XHQndmFsJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCksXG5cdFx0XHQnbGVuZ3RoJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCkubGVuZ3RoXG5cdFx0fVxuXG5cdFx0aWYgKGNvbmNyZXRlLmxlbmd0aCA+IDQpIHtcblxuXHRcdFx0dmFyIGhpZGRlblBhcnQgPSAnXFx1MjAyMicucmVwZWF0KE1hdGgubWF4KDAsIGNvbmNyZXRlLmxlbmd0aCAtIDQpKTtcblx0XHRcdHZhciB2aXNpYmxlUGFydCA9IGNvbmNyZXRlLnZhbC5zbGljZSgtNCk7XG5cblx0XHRcdC8vIENvbWJpbmUgdGhlIGhpZGRlbiBhbmQgdmlzaWJsZSBwYXJ0c1xuXHRcdFx0dmFyIG1hc2tlZFZhbHVlID0gaGlkZGVuUGFydCArIHZpc2libGVQYXJ0O1xuXG5cdFx0XHRwcm94eV9zZWxlY3Rvci52YWwobWFza2VkVmFsdWUpO1xuXHRcdH1cblx0XHQvLyBFbnN1cmUgZXZlbnRzIGFyZSBub3QgYWRkZWQgbW9yZSB0aGFuIG9uY2Vcblx0XHRpZiAoIXByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJykpIHtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdpbnB1dCcsIGhhbmRsZUlucHV0KTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdmb2N1cycsIGhhbmRsZUZvY3VzKTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJywgdHJ1ZSk7XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlIHRoZSBpbnB1dCBldmVudFxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGhhbmRsZUlucHV0KCkge1xuXHRcdFx0dmFyIHByb3h5VmFsdWUgPSBwcm94eV9zZWxlY3Rvci52YWwoKTtcblx0XHRcdGlmIChwcm94eVZhbHVlLmluZGV4T2YoJ1xcdTIwMjInKSA9PT0gLTEpIHtcblx0XHRcdFx0Y29uY3JldGVfc2VsZWN0b3IudmFsKHByb3h5VmFsdWUpO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIEhhbmRsZSB0aGUgZm9jdXMgZXZlbnRcblx0XHQgKi9cblx0XHRmdW5jdGlvbiBoYW5kbGVGb2N1cygpIHtcblx0XHRcdHZhciBjb25jcmV0ZV92YWx1ZSA9IGNvbmNyZXRlX3NlbGVjdG9yLnZhbCgpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3IudmFsKGNvbmNyZXRlX3ZhbHVlKTtcblx0XHR9XG5cblx0fVxuXG5cdFx0Ly8gVXBkYXRlIHRoZSBjb25jcmV0ZSBmaWVsZCB3aGVuIHRoZSBwcm94eSBpcyB1cGRhdGVkLlxuXG5cblx0bWFza0ZpZWxkKCQoJyNjbG91ZGZsYXJlX2FwaV9rZXlfbWFzaycpLCAkKCcjY2xvdWRmbGFyZV9hcGlfa2V5JykpO1xuXHRtYXNrRmllbGQoJCgnI2Nsb3VkZmxhcmVfem9uZV9pZF9tYXNrJyksICQoJyNjbG91ZGZsYXJlX3pvbmVfaWQnKSk7XG5cblx0Ly8gRGlzcGxheS9IaWRlIGNoaWxkcmVuIGZpZWxkcyBvbiBjaGVja2JveCBjaGFuZ2UuXG4gICAgJCggJy53cHItaXNQYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nICkub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAvLyBPbiBwYWdlIGxvYWQsIGRpc3BsYXkgdGhlIGFjdGl2ZSBmaWVsZHMuXG4gICAgJCggJy53cHItZmllbGQtLWNoaWxkcmVuJyApLmVhY2goIGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgJGZpZWxkID0gJCggdGhpcyApO1xuXG4gICAgICAgIGlmICggd3BySXNQYXJlbnRBY3RpdmUoICRmaWVsZCApICkge1xuICAgICAgICAgICAgJGZpZWxkLmFkZENsYXNzKCAnd3ByLWlzT3BlbicgKTtcbiAgICAgICAgfVxuICAgIH0gKTtcblxuXG5cblxuICAgIC8qKipcbiAgICAqIFdhcm5pbmcgZmllbGRzXG4gICAgKioqL1xuXG4gICAgdmFyICR3YXJuaW5nUGFyZW50ID0gJCgnLndwci1maWVsZC0tcGFyZW50Jyk7XG4gICAgdmFyICR3YXJuaW5nUGFyZW50SW5wdXQgPSAkKCcud3ByLWZpZWxkLS1wYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nKTtcblxuICAgIC8vIElmIGFscmVhZHkgY2hlY2tlZFxuICAgICR3YXJuaW5nUGFyZW50SW5wdXQuZWFjaChmdW5jdGlvbigpe1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAkd2FybmluZ1BhcmVudC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHdwclNob3dXYXJuaW5nKCQodGhpcykpO1xuICAgIH0pO1xuXG4gICAgZnVuY3Rpb24gd3ByU2hvd1dhcm5pbmcoYUVsZW0pe1xuICAgICAgICB2YXIgJHdhcm5pbmdGaWVsZCA9IGFFbGVtLm5leHQoJy53cHItZmllbGRXYXJuaW5nJyksXG4gICAgICAgICAgICAkdGhpc0NoZWNrYm94ID0gYUVsZW0uZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKSxcbiAgICAgICAgICAgICRuZXh0V2FybmluZyA9IGFFbGVtLnBhcmVudCgpLm5leHQoJy53cHItd2FybmluZ0NvbnRhaW5lcicpLFxuICAgICAgICAgICAgJG5leHRGaWVsZHMgPSAkbmV4dFdhcm5pbmcuZmluZCgnLndwci1maWVsZCcpLFxuICAgICAgICAgICAgcGFyZW50SWQgPSBhRWxlbS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyksXG4gICAgICAgICAgICAkY2hpbGRyZW4gPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgcGFyZW50SWQgKyAnXCJdJylcbiAgICAgICAgO1xuXG4gICAgICAgIC8vIENoZWNrIHdhcm5pbmcgcGFyZW50XG4gICAgICAgIGlmKCR0aGlzQ2hlY2tib3guaXMoJzpjaGVja2VkJykpe1xuICAgICAgICAgICAgJHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuICAgICAgICAgICAgJHRoaXNDaGVja2JveC5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgYUVsZW0udHJpZ2dlcignY2hhbmdlJyk7XG5cblxuICAgICAgICAgICAgdmFyICR3YXJuaW5nQnV0dG9uID0gJHdhcm5pbmdGaWVsZC5maW5kKCcud3ByLWJ1dHRvbicpO1xuXG4gICAgICAgICAgICAvLyBWYWxpZGF0ZSB0aGUgd2FybmluZ1xuICAgICAgICAgICAgJHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAkdGhpc0NoZWNrYm94LnByb3AoJ2NoZWNrZWQnLCB0cnVlKTtcbiAgICAgICAgICAgICAgICAkd2FybmluZ0ZpZWxkLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgICAgICAgICAgJGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cbiAgICAgICAgICAgICAgICAvLyBJZiBuZXh0IGVsZW0gPSBkaXNhYmxlZFxuICAgICAgICAgICAgICAgIGlmKCRuZXh0V2FybmluZy5sZW5ndGggPiAwKXtcbiAgICAgICAgICAgICAgICAgICAgJG5leHRGaWVsZHMucmVtb3ZlQ2xhc3MoJ3dwci1pc0Rpc2FibGVkJyk7XG4gICAgICAgICAgICAgICAgICAgICRuZXh0RmllbGRzLmZpbmQoJ2lucHV0JykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICRuZXh0RmllbGRzLmFkZENsYXNzKCd3cHItaXNEaXNhYmxlZCcpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgJGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDTkFNRVMgYWRkL3JlbW92ZSBsaW5lc1xuICAgICAqL1xuICAgICQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLW11bHRpcGxlLWNsb3NlJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKHRoaXMpLnBhcmVudCgpLnNsaWRlVXAoICdzbG93JyAsIGZ1bmN0aW9uKCl7JCh0aGlzKS5yZW1vdmUoKTsgfSApO1xuXHR9ICk7XG5cblx0JCgnLndwci1idXR0b24tLWFkZE11bHRpJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgJCgkKCcjd3ByLWNuYW1lLW1vZGVsJykuaHRtbCgpKS5hcHBlbmRUbygnI3dwci1jbmFtZXMtbGlzdCcpO1xuICAgIH0pO1xuXG5cdC8qKipcblx0ICogV3ByIFJhZGlvIGJ1dHRvblxuXHQgKioqL1xuXHR2YXIgZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRpZigkKHRoaXMpLmhhc0NsYXNzKCdyYWRpby1hY3RpdmUnKSl7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHZhciAkcGFyZW50ID0gJCh0aGlzKS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJykucmVtb3ZlQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItZmllbGRXYXJuaW5nJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkKHRoaXMpLmFkZENsYXNzKCdyYWRpby1hY3RpdmUnKTtcblx0XHR3cHJTaG93UmFkaW9XYXJuaW5nKCQodGhpcykpO1xuXG5cdH0gKTtcblxuXG5cdGZ1bmN0aW9uIHdwclNob3dSYWRpb1dhcm5pbmcoJGVsbSl7XG5cdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cdFx0JGVsbS50cmlnZ2VyKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgWyAkZWxtIF0gKTtcblx0XHRpZiAoISRlbG0uaGFzQ2xhc3MoJ2hhcy13YXJuaW5nJykgfHwgZGlzYWJsZV9yYWRpb193YXJuaW5nKSB7XG5cdFx0XHR3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKTtcblx0XHRcdCRlbG0udHJpZ2dlciggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgWyAkZWxtIF0gKTtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyICR3YXJuaW5nRmllbGQgPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgJGVsbS5hdHRyKCdpZCcpICsgJ1wiXS53cHItZmllbGRXYXJuaW5nJyk7XG5cdFx0JHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdHZhciAkd2FybmluZ0J1dHRvbiA9ICR3YXJuaW5nRmllbGQuZmluZCgnLndwci1idXR0b24nKTtcblxuXHRcdC8vIFZhbGlkYXRlIHRoZSB3YXJuaW5nXG5cdFx0JHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcblx0XHRcdCR3YXJuaW5nRmllbGQucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pO1xuXHRcdFx0JGVsbS50cmlnZ2VyKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBbICRlbG0gXSApO1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH0pO1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSkge1xuXHRcdHZhciAkcGFyZW50ID0gJGVsbS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHR2YXIgJGNoaWxkcmVuID0gJCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyW2RhdGEtcGFyZW50PVwiJyArICRlbG0uYXR0cignaWQnKSArICdcIl0nKTtcblx0XHQkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0fVxuXG5cdC8qKipcblx0ICogV3ByIE9wdGltaXplIENzcyBEZWxpdmVyeSBGaWVsZFxuXHQgKioqL1xuXHR2YXIgcnVjc3NBY3RpdmUgPSBwYXJzZUludCgkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoKSk7XG5cblx0JCggXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCAud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvblwiIClcblx0XHQub24oIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIGZ1bmN0aW9uKCBldmVudCwgJGVsbSApIHtcblx0XHRcdHRvZ2dsZUFjdGl2ZU9wdGltaXplQ3NzRGVsaXZlcnlNZXRob2QoJGVsbSk7XG5cdFx0fSk7XG5cblx0JChcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlcIikub24oXCJjaGFuZ2VcIiwgZnVuY3Rpb24oKXtcblx0XHRpZiggJCh0aGlzKS5pcyhcIjpub3QoOmNoZWNrZWQpXCIpICl7XG5cdFx0XHRkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpO1xuXHRcdH1lbHNle1xuXHRcdFx0dmFyIGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkID0gJyMnKyQoJyNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kJykuZGF0YSggJ2RlZmF1bHQnICk7XG5cdFx0XHQkKGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkKS50cmlnZ2VyKCdjbGljaycpO1xuXHRcdH1cblx0fSk7XG5cblx0ZnVuY3Rpb24gdG9nZ2xlQWN0aXZlT3B0aW1pemVDc3NEZWxpdmVyeU1ldGhvZCgkZWxtKSB7XG5cdFx0dmFyIG9wdGltaXplX21ldGhvZCA9ICRlbG0uZGF0YSgndmFsdWUnKTtcblx0XHRpZigncmVtb3ZlX3VudXNlZF9jc3MnID09PSBvcHRpbWl6ZV9tZXRob2Qpe1xuXHRcdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDEpO1xuXHRcdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0XHR9ZWxzZXtcblx0XHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgwKTtcblx0XHRcdCQoJyNhc3luY19jc3MnKS52YWwoMSk7XG5cdFx0fVxuXG5cdH1cblxuXHRmdW5jdGlvbiBkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpIHtcblx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMCk7XG5cdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0fVxuXG5cdCQoIFwiI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QgLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b25cIiApXG5cdFx0Lm9uKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgZnVuY3Rpb24oIGV2ZW50LCAkZWxtICkge1xuXHRcdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gKCdyZW1vdmVfdW51c2VkX2NzcycgPT09ICRlbG0uZGF0YSgndmFsdWUnKSAmJiAxID09PSBydWNzc0FjdGl2ZSlcblx0XHR9KTtcblxuXHQkKCBcIi53cHItbXVsdGlwbGUtc2VsZWN0IC53cHItbGlzdC1oZWFkZXJcIiApLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0JChlLnRhcmdldCkuY2xvc2VzdCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1saXN0JykudG9nZ2xlQ2xhc3MoJ29wZW4nKTtcblx0fSk7XG5cblx0JCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1jaGVja2JveCcpLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0Y29uc3QgY2hlY2tib3ggPSAkKHRoaXMpLmZpbmQoJ2lucHV0Jyk7XG5cdFx0Y29uc3QgaXNfY2hlY2tlZCA9IGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnKSAhPT0gdW5kZWZpbmVkO1xuXHRcdGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdGNvbnN0IHN1Yl9jaGVja2JveGVzID0gJChjaGVja2JveCkuY2xvc2VzdCgnLndwci1saXN0JykuZmluZCgnLndwci1saXN0LWJvZHkgaW5wdXRbdHlwZT1cImNoZWNrYm94XCJdJyk7XG5cdFx0aWYoY2hlY2tib3guaGFzQ2xhc3MoJ3dwci1tYWluLWNoZWNrYm94JykpIHtcblx0XHRcdCQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdFx0fSk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGNvbnN0IG1haW5fY2hlY2tib3ggPSAkKGNoZWNrYm94KS5jbG9zZXN0KCcud3ByLWxpc3QnKS5maW5kKCcud3ByLW1haW4tY2hlY2tib3gnKTtcblxuXHRcdGNvbnN0IHN1Yl9jaGVja2VkID0gICQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRpZigkKGNoZWNrYm94KS5hdHRyKCdjaGVja2VkJykgPT09IHVuZGVmaW5lZCkge1xuXHRcdFx0XHRyZXR1cm4gO1xuXHRcdFx0fVxuXHRcdFx0cmV0dXJuIGNoZWNrYm94O1xuXHRcdH0pO1xuXHRcdG1haW5fY2hlY2tib3guYXR0cignY2hlY2tlZCcsIHN1Yl9jaGVja2VkLmxlbmd0aCA9PT0gc3ViX2NoZWNrYm94ZXMubGVuZ3RoID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHR9KTtcblxuXHRpZiAoICQoICcud3ByLW1haW4tY2hlY2tib3gnICkubGVuZ3RoID4gMCApIHtcblx0XHQkKCcud3ByLW1haW4tY2hlY2tib3gnKS5lYWNoKChjaGVja2JveF9rZXksIGNoZWNrYm94KSA9PiB7XG5cdFx0XHRsZXQgcGFyZW50X2xpc3QgPSAkKGNoZWNrYm94KS5wYXJlbnRzKCcud3ByLWxpc3QnKTtcblx0XHRcdGxldCBub3RfY2hlY2tlZCA9IHBhcmVudF9saXN0LmZpbmQoICcud3ByLWxpc3QtYm9keSBpbnB1dFt0eXBlPWNoZWNrYm94XTpub3QoOmNoZWNrZWQpJyApLmxlbmd0aDtcblx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBub3RfY2hlY2tlZCA8PSAwID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHRcdH0pO1xuXHR9XG5cblx0bGV0IGNoZWNrQm94Q291bnRlciA9IHtcblx0XHRjaGVja2VkOiB7fSxcblx0XHR0b3RhbDoge31cblx0fTtcblx0JCgnLndwci1maWVsZC0tY2F0ZWdvcml6ZWRtdWx0aXNlbGVjdCAud3ByLWxpc3QnKS5lYWNoKGZ1bmN0aW9uICgpIHtcblx0XHQvLyBHZXQgdGhlIElEIG9mIHRoZSBjdXJyZW50IGVsZW1lbnRcblx0XHRsZXQgaWQgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG5cdFx0aWYgKGlkKSB7XG5cdFx0XHRjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPSAkKGAjJHtpZH0gaW5wdXRbdHlwZT0nY2hlY2tib3gnXTpjaGVja2VkYCkubGVuZ3RoO1xuXHRcdFx0Y2hlY2tCb3hDb3VudGVyLnRvdGFsW2lkXSA9ICQoYCMke2lkfSBpbnB1dFt0eXBlPSdjaGVja2JveCddOm5vdCgud3ByLW1haW4tY2hlY2tib3gpYCkubGVuZ3RoO1xuXHRcdFx0Ly8gVXBkYXRlIHRoZSBjb3VudGVyIHRleHRcblx0XHRcdCQoYCMke2lkfSAud3ByLWJhZGdlLWNvdW50ZXIgc3BhbmApLnRleHQoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdKTtcblx0XHRcdC8vIFNob3cgb3IgaGlkZSB0aGUgY291bnRlciBiYWRnZSBiYXNlZCBvbiB0aGUgY291bnRcblx0XHRcdCQoYCMke2lkfSAud3ByLWJhZGdlLWNvdW50ZXJgKS50b2dnbGUoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID4gMCk7XG5cblx0XHRcdC8vIENoZWNrIHRoZSBzZWxlY3QgYWxsIG9wdGlvbiBpZiBhbGwgZXhjbHVzaW9ucyBhcmUgY2hlY2tlZCBpbiBhIHNlY3Rpb24uXG5cdFx0XHRpZiAoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID09PSBjaGVja0JveENvdW50ZXIudG90YWxbaWRdKSB7XG5cdFx0XHRcdCQoYCMke2lkfSAud3ByLW1haW4tY2hlY2tib3hgKS5hdHRyKCdjaGVja2VkJywgdHJ1ZSk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblxuXHQvKipcblx0ICogRGVsYXkgSlMgRXhlY3V0aW9uIFNhZmUgTW9kZSBGaWVsZFxuXHQgKi9cblx0dmFyICRkamVfc2FmZV9tb2RlX2NoZWNrYm94ID0gJCgnI2RlbGF5X2pzX2V4ZWN1dGlvbl9zYWZlX21vZGUnKTtcblx0JCgnI2RlbGF5X2pzJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHRpZiAoJCh0aGlzKS5pcygnOm5vdCg6Y2hlY2tlZCknKSAmJiAkZGplX3NhZmVfbW9kZV9jaGVja2JveC5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0JGRqZV9zYWZlX21vZGVfY2hlY2tib3gudHJpZ2dlcignY2xpY2snKTtcblx0XHR9XG5cdH0pO1xuXG5cdGxldCBzdGFja2VkX3NlbGVjdCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAncm9ja2V0X3N0YWNrZWRfc2VsZWN0JyApO1xuXHRpZiAoIHN0YWNrZWRfc2VsZWN0ICkge1xuXHRcdHN0YWNrZWRfc2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2N1c3RvbS1zZWxlY3QtY2hhbmdlJyxmdW5jdGlvbihldmVudCl7XG5cblx0XHRcdGxldCBzZWxlY3RlZF9vcHRpb24gPSAkKCBldmVudC5kZXRhaWwuc2VsZWN0ZWRPcHRpb24gKTtcblxuXHRcdFx0bGV0IG5hbWUgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgnbmFtZScpO1xuXG5cdFx0XHRsZXQgc2F2aW5nID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3NhdmluZycpO1xuXHRcdFx0bGV0IHJlZ3VsYXJfcHJpY2UgID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3JlZ3VsYXItcHJpY2UnKTtcblx0XHRcdGxldCBwcmljZSAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgncHJpY2UnKTtcblx0XHRcdGxldCB1cmwgICAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgndXJsJyk7XG5cblx0XHRcdGxldCBwYXJlbnRfaXRlbSA9ICQodGhpcykucGFyZW50cyggJy53cHItdXBncmFkZS1pdGVtJyApO1xuXG5cdFx0XHRpZiAoIHNhdmluZyApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1zYXZpbmcgc3BhbicgKS5odG1sKCBzYXZpbmcgKTtcblx0XHRcdH1cblx0XHRcdGlmICggbmFtZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS10aXRsZScgKS5odG1sKCBuYW1lICk7XG5cdFx0XHR9XG5cdFx0XHRpZiAoIHJlZ3VsYXJfcHJpY2UgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtcHJpY2UtcmVndWxhciBzcGFuJyApLmh0bWwoIHJlZ3VsYXJfcHJpY2UgKTtcblx0XHRcdH1cblx0XHRcdGlmICggcHJpY2UgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtcHJpY2UtdmFsdWUnICkuaHRtbCggcHJpY2UgKTtcblx0XHRcdH1cblx0XHRcdGlmICggdXJsICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLWxpbmsnICkuYXR0ciggJ2hyZWYnLCB1cmwgKTtcblx0XHRcdH1cblxuXHRcdH0gKTtcblx0fVxuXG5cdCQoZG9jdW1lbnQpLm9uKCAnY2xpY2snLCAnLndwci1jb25maXJtLWRlbGV0ZScsIGZ1bmN0aW9uIChlKSB7XG5cdFx0cmV0dXJuIGNvbmZpcm0oICQodGhpcykuZGF0YSgnd3ByX2NvbmZpcm1fbXNnJykgKTtcblx0fSApO1xuXG59KTtcbiIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcblxuXG5cdC8qKipcblx0KiBEYXNoYm9hcmQgbm90aWNlXG5cdCoqKi9cblxuXHR2YXIgJG5vdGljZSA9ICQoJy53cHItbm90aWNlJyk7XG5cdHZhciAkbm90aWNlQ2xvc2UgPSAkKCcjd3ByLWNvbmdyYXR1bGF0aW9ucy1ub3RpY2UnKTtcblxuXHQkbm90aWNlQ2xvc2Uub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VEYXNoYm9hcmROb3RpY2UoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckNsb3NlRGFzaGJvYXJkTm90aWNlKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLnRvKCRub3RpY2UsIDEsIHthdXRvQWxwaGE6MCwgeDo0MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAudG8oJG5vdGljZSwgMC42LCB7aGVpZ2h0OiAwLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS40Jylcblx0XHQgIC5zZXQoJG5vdGljZSwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdC8qKlxuXHQgKiBSb2NrZXQgQW5hbHl0aWNzIG5vdGljZSBpbmZvIGNvbGxlY3Rcblx0ICovXG5cdCQoICcucm9ja2V0LWFuYWx5dGljcy1kYXRhLWNvbnRhaW5lcicgKS5oaWRlKCk7XG5cdCQoICcucm9ja2V0LXByZXZpZXctYW5hbHl0aWNzLWRhdGEnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQodGhpcykucGFyZW50KCkubmV4dCggJy5yb2NrZXQtYW5hbHl0aWNzLWRhdGEtY29udGFpbmVyJyApLnRvZ2dsZSgpO1xuXHR9ICk7XG5cblx0LyoqKlxuXHQqIEhpZGUgLyBzaG93IFJvY2tldCBhZGRvbiB0YWJzLlxuXHQqKiovXG5cblx0JCggJy53cHItdG9nZ2xlLWJ1dHRvbicgKS5lYWNoKCBmdW5jdGlvbigpIHtcblx0XHR2YXIgJGJ1dHRvbiAgID0gJCggdGhpcyApO1xuXHRcdHZhciAkY2hlY2tib3ggPSAkYnV0dG9uLmNsb3Nlc3QoICcud3ByLWZpZWxkc0NvbnRhaW5lci1maWVsZHNldCcgKS5maW5kKCAnLndwci1yYWRpbyA6Y2hlY2tib3gnICk7XG5cdFx0dmFyICRtZW51SXRlbSA9ICQoICdbaHJlZj1cIicgKyAkYnV0dG9uLmF0dHIoICdocmVmJyApICsgJ1wiXS53cHItbWVudUl0ZW0nICk7XG5cblx0XHQkY2hlY2tib3gub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuXHRcdFx0aWYgKCAkY2hlY2tib3guaXMoICc6Y2hlY2tlZCcgKSApIHtcblx0XHRcdFx0JG1lbnVJdGVtLmNzcyggJ2Rpc3BsYXknLCAnYmxvY2snICk7XG5cdFx0XHRcdCRidXR0b24uY3NzKCAnZGlzcGxheScsICdpbmxpbmUtYmxvY2snICk7XG5cdFx0XHR9IGVsc2V7XG5cdFx0XHRcdCRtZW51SXRlbS5jc3MoICdkaXNwbGF5JywgJ25vbmUnICk7XG5cdFx0XHRcdCRidXR0b24uY3NzKCAnZGlzcGxheScsICdub25lJyApO1xuXHRcdFx0fVxuXHRcdH0gKS50cmlnZ2VyKCAnY2hhbmdlJyApO1xuXHR9ICk7XG5cblx0LyoqKlxuXHQqIEhlbHAgQnV0dG9uIFRyYWNraW5nXG5cdCoqKi9cblxuXHQvLyBUcmFjayBjbGlja3Mgb24gdmFyaW91cyBoZWxwIGVsZW1lbnRzIHdpdGggZGF0YSBhdHRyaWJ1dGVzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICdbZGF0YS13cHJfdHJhY2tfaGVscF0nLCBmdW5jdGlvbihlKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgJGVsID0gJCh0aGlzKTtcblx0XHRcdHZhciBidXR0b24gPSAkZWwuZGF0YSgnd3ByX3RyYWNrX2hlbHAnKTtcblx0XHRcdHZhciBjb250ZXh0ID0gJGVsLmRhdGEoJ3dwcl90cmFja19jb250ZXh0JykgfHwgJyc7XG5cblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIHNwZWNpZmljIGhlbHAgcmVzb3VyY2UgY2xpY2tzIHdpdGggZXhwbGljaXQgc2VsZWN0b3JzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud2lzdGlhX2VtYmVkJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgdGl0bGUgPSAkKHRoaXMpLnRleHQoKSB8fCAnR2V0dGluZyBTdGFydGVkIFZpZGVvJztcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24odGl0bGUsICdHZXR0aW5nIFN0YXJ0ZWQnKTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIEZBUSBsaW5rc1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnYVtkYXRhLWJlYWNvbi1hcnRpY2xlXScsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0dmFyIGhyZWYgPSAkKHRoaXMpLmF0dHIoJ2hyZWYnKTtcblx0XHRcdHZhciB0ZXh0ID0gJCh0aGlzKS50ZXh0KCk7XG5cblx0XHRcdC8vIENoZWNrIGlmIGl0J3MgaW4gRkFRIHNlY3Rpb24gb3Igc2lkZWJhciBkb2N1bWVudGF0aW9uXG5cdFx0XHRpZiAoJCh0aGlzKS5jbG9zZXN0KCcud3ByLWZpZWxkc0NvbnRhaW5lci1maWVsZHNldCcpLnByZXYoJy53cHItb3B0aW9uSGVhZGVyJykuZmluZCgnLndwci10aXRsZTInKS50ZXh0KCkuaW5jbHVkZXMoJ0ZyZXF1ZW50bHkgQXNrZWQgUXVlc3Rpb25zJykpIHtcblx0XHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignRkFRIC0gJyArIHRleHQsICdEYXNoYm9hcmQnKTtcblx0XHRcdH0gZWxzZSBpZiAoJCh0aGlzKS5jbG9zZXN0KCcud3ByLWRvY3VtZW50YXRpb24nKS5sZW5ndGggPiAwKSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0RvY3VtZW50YXRpb24nLCAnU2lkZWJhcicpO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignRG9jdW1lbnRhdGlvbiBMaW5rJywgJ0dlbmVyYWwnKTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIFwiSG93IHRvIG1lYXN1cmUgbG9hZGluZyB0aW1lXCIgbGlua1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnYVtocmVmKj1cImhvdy10by10ZXN0LXdvcmRwcmVzcy1zaXRlLXBlcmZvcm1hbmNlXCJdJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdMb2FkaW5nIFRpbWUgR3VpZGUnLCAnU2lkZWJhcicpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gVHJhY2sgXCJOZWVkIGhlbHA/XCIgbGlua3MgKGV4aXN0aW5nIGhlbHAgYnV0dG9ucylcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItaW5mb0FjdGlvbi0taGVscDpub3QoW2RhdGEtYmVhY29uLWlkXSknLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ05lZWQgSGVscCcsICdHZW5lcmFsJyk7XG5cdFx0fVxuXHR9KTtcblxuXG5cdC8qKipcblx0KiBTaG93IHBvcGluIGFuYWx5dGljc1xuXHQqKiovXG5cblx0dmFyICR3cHJBbmFseXRpY3NQb3BpbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzJyksXG5cdFx0JHdwclBvcGluT3ZlcmxheSA9ICQoJy53cHItUG9waW4tb3ZlcmxheScpLFxuXHRcdCR3cHJBbmFseXRpY3NDbG9zZVBvcGluID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MtY2xvc2UnKSxcblx0XHQkd3ByQW5hbHl0aWNzUG9waW5CdXR0b24gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcyAud3ByLWJ1dHRvbicpLFxuXHRcdCR3cHJBbmFseXRpY3NPcGVuUG9waW4gPSAkKCcud3ByLWpzLXBvcGluJylcblx0O1xuXG5cdCR3cHJBbmFseXRpY3NPcGVuUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJPcGVuQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByQW5hbHl0aWNzQ2xvc2VQb3Bpbi5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHdwckNsb3NlQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByQW5hbHl0aWNzUG9waW5CdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJBY3RpdmF0ZUFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByT3BlbkFuYWx5dGljcygpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC5zZXQoJHdwckFuYWx5dGljc1BvcGluLCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdCAgLnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdCAgLmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MH0se2F1dG9BbHBoYToxLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC5mcm9tVG8oJHdwckFuYWx5dGljc1BvcGluLCAwLjYsIHthdXRvQWxwaGE6MCwgbWFyZ2luVG9wOiAtMjR9LCB7YXV0b0FscGhhOjEsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckNsb3NlQW5hbHl0aWNzKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLmZyb21Ubygkd3ByQW5hbHl0aWNzUG9waW4sIDAuNiwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6IDB9LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDotMjQsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MX0se2F1dG9BbHBoYTowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdCAgLnNldCgkd3ByQW5hbHl0aWNzUG9waW4sIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQgIC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckFjdGl2YXRlQW5hbHl0aWNzKCl7XG5cdFx0d3ByQ2xvc2VBbmFseXRpY3MoKTtcblx0XHQkKCcjYW5hbHl0aWNzX2VuYWJsZWQnKS5wcm9wKCdjaGVja2VkJywgdHJ1ZSk7XG5cblx0XHR2YXIgYW5hbHl0aWNzRW5hYmxlZCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdhbmFseXRpY3NfZW5hYmxlZCcpO1xuXG5cdFx0aWYgKCBhbmFseXRpY3NFbmFibGVkICkge1xuXHRcdFx0dmFyIGNoYW5nZUV2ZW50ID0gbmV3IEV2ZW50KCdjaGFuZ2UnLCB7IGJ1YmJsZXM6IHRydWUgfSk7XG5cdFx0XHRhbmFseXRpY3NFbmFibGVkLmRpc3BhdGNoRXZlbnQoY2hhbmdlRXZlbnQpO1xuXHRcdH1cblx0fVxuXG5cdC8vIERpc3BsYXkgQ1RBIHdpdGhpbiB0aGUgcG9waW4gYFdoYXQgaW5mbyB3aWxsIHdlIGNvbGxlY3Q/YFxuXHQkKCcjYW5hbHl0aWNzX2VuYWJsZWQnKS5vbignY2hhbmdlJywgZnVuY3Rpb24gKCkge1xuXHRcdCQoJy53cHItcm9ja2V0LWFuYWx5dGljcy1jdGEnKS50b2dnbGVDbGFzcygnd3ByLWlzSGlkZGVuJyk7XG5cdH0pO1xuXG5cdC8qKipcblx0KiBTaG93IHBvcGluIHVwZ3JhZGVcblx0KioqL1xuXG5cdHZhciAkd3ByVXBncmFkZVBvcGluID0gJCgnLndwci1Qb3Bpbi1VcGdyYWRlJyksXG5cdCR3cHJVcGdyYWRlQ2xvc2VQb3BpbiA9ICQoJy53cHItUG9waW4tVXBncmFkZS1jbG9zZScpLFxuXHQkd3ByVXBncmFkZU9wZW5Qb3BpbiA9ICQoJy53cHItcG9waW4tdXBncmFkZS10b2dnbGUnKTtcblxuXHQkd3ByVXBncmFkZU9wZW5Qb3Bpbi5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHdwck9wZW5VcGdyYWRlUG9waW4oKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdCR3cHJVcGdyYWRlQ2xvc2VQb3Bpbi5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJDbG9zZVVwZ3JhZGVQb3BpbigpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByT3BlblVwZ3JhZGVQb3Bpbigpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKCk7XG5cblx0XHR2VEwuc2V0KCR3cHJVcGdyYWRlUG9waW4sIHsnZGlzcGxheSc6J2Jsb2NrJ30pXG5cdFx0XHQuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J2Jsb2NrJ30pXG5cdFx0XHQuZnJvbVRvKCR3cHJQb3Bpbk92ZXJsYXksIDAuNiwge2F1dG9BbHBoYTowfSx7YXV0b0FscGhhOjEsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdFx0LmZyb21Ubygkd3ByVXBncmFkZVBvcGluLCAwLjYsIHthdXRvQWxwaGE6MCwgbWFyZ2luVG9wOiAtMjR9LCB7YXV0b0FscGhhOjEsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckNsb3NlVXBncmFkZVBvcGluKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKTtcblxuXHRcdHZUTC5mcm9tVG8oJHdwclVwZ3JhZGVQb3BpbiwgMC42LCB7YXV0b0FscGhhOjEsIG1hcmdpblRvcDogMH0sIHthdXRvQWxwaGE6MCwgbWFyZ2luVG9wOi0yNCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0XHQuZnJvbVRvKCR3cHJQb3Bpbk92ZXJsYXksIDAuNiwge2F1dG9BbHBoYToxfSx7YXV0b0FscGhhOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNScpXG5cdFx0XHQuc2V0KCR3cHJVcGdyYWRlUG9waW4sIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHRcdC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdC8qKipcblx0KiBTaWRlYmFyIG9uL29mZlxuXHQqKiovXG5cdHZhciAkd3ByU2lkZWJhciAgICA9ICQoICcud3ByLVNpZGViYXInICk7XG5cdHZhciAkd3ByQnV0dG9uVGlwcyA9ICQoJy53cHItanMtdGlwcycpO1xuXG5cdCR3cHJCdXR0b25UaXBzLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHR3cHJEZXRlY3RUaXBzKCQodGhpcykpO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJEZXRlY3RUaXBzKGFFbGVtKXtcblx0XHRpZihhRWxlbS5pcygnOmNoZWNrZWQnKSl7XG5cdFx0XHQkd3ByU2lkZWJhci5jc3MoJ2Rpc3BsYXknLCdibG9jaycpO1xuXHRcdFx0bG9jYWxTdG9yYWdlLnNldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJywgJ29uJyApO1xuXHRcdH1cblx0XHRlbHNle1xuXHRcdFx0JHdwclNpZGViYXIuY3NzKCdkaXNwbGF5Jywnbm9uZScpO1xuXHRcdFx0bG9jYWxTdG9yYWdlLnNldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJywgJ29mZicgKTtcblx0XHR9XG5cdH1cblxuXG5cblx0LyoqKlxuXHQqIERldGVjdCBBZGJsb2NrXG5cdCoqKi9cblxuXHRpZihkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnTEtnT2NDUnB3bUFqJykpe1xuXHRcdCQoJy53cHItYWRibG9jaycpLmNzcygnZGlzcGxheScsICdub25lJyk7XG5cdH0gZWxzZSB7XG5cdFx0JCgnLndwci1hZGJsb2NrJykuY3NzKCdkaXNwbGF5JywgJ2Jsb2NrJyk7XG5cdH1cblxuXHR2YXIgJGFkYmxvY2sgPSAkKCcud3ByLWFkYmxvY2snKTtcblx0dmFyICRhZGJsb2NrQ2xvc2UgPSAkKCcud3ByLWFkYmxvY2stY2xvc2UnKTtcblxuXHQkYWRibG9ja0Nsb3NlLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlQWRibG9ja05vdGljZSgpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VBZGJsb2NrTm90aWNlKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLnRvKCRhZGJsb2NrLCAxLCB7YXV0b0FscGhhOjAsIHg6NDAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLnRvKCRhZGJsb2NrLCAwLjQsIHtoZWlnaHQ6IDAsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjQnKVxuXHRcdCAgLnNldCgkYWRibG9jaywgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdC8vIEhhbmRsZSBleHBhbmQvY29sbGFwc2Ugb2YgcmVjb21tZW5kYXRpb25zIGxpc3QuXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcjd3ByLXJlY29tbWVuZGF0aW9ucy1sb2FkLW1vcmUnLCBmdW5jdGlvbigpIHtcblx0XHRsZXQgJGxpc3QgPSAkKCcud3ByLXJlY29tbWVuZGF0aW9uc19fbGlzdCcpO1xuXHRcdGxldCAkaGlkZGVuSXRlbXMgPSAkbGlzdC5maW5kKCcud3ByLXJlY29tbWVuZGF0aW9uLWl0ZW06Z3QoMiknKTtcblxuXHRcdC8vIFRyYWNrIExvYWQgTW9yZSBidXR0b24gY2xpY2tcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ3JvY2tldCBpbnNpZ2h0cyByZWNvbW1lbmRhdGlvbnMgbG9hZCBtb3JlJywgJ2xvYWRfbW9yZScpO1xuXHRcdH1cblxuXHRcdGlmICgkbGlzdC5oYXNDbGFzcygnaXMtZXhwYW5kZWQnKSkge1xuXHRcdFx0JGhpZGRlbkl0ZW1zLnNsaWRlVXAoMzAwLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0JGxpc3QucmVtb3ZlQ2xhc3MoJ2lzLWV4cGFuZGVkJyk7XG5cdFx0XHR9KTtcblx0XHRcdCQodGhpcykucmVtb3ZlQ2xhc3MoJ2lzLWV4cGFuZGVkJyk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0JGxpc3QuYWRkQ2xhc3MoJ2lzLWV4cGFuZGVkJyk7XG5cdFx0JGhpZGRlbkl0ZW1zLnNsaWRlRG93bigzMDApO1xuXHRcdCQodGhpcykuYWRkQ2xhc3MoJ2lzLWV4cGFuZGVkJyk7XG5cdH0pO1xuXG5cdC8vIFRyYWNrIFJvY2tldCBJbnNpZ2h0cyBSZWNvbW1lbmRhdGlvbiBBY3RpdmF0ZSBidXR0b24gY2xpY2tzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXJlY29tbWVuZGF0aW9uLWl0ZW1fX2FjdGl2YXRlJywgZnVuY3Rpb24oKSB7XG5cdFx0dmFyIHJlY29tbWVuZGF0aW9uID0gJCh0aGlzKS5kYXRhKCdyZWNvbW1lbmRhdGlvbicpIHx8ICd1bmtub3duJztcblxuXHRcdC8vIElmIHRoZSBlbGVtZW50IGlzIHZpc2libGUsIHNjcm9sbCB0byB0aGUgZWxlbWVudCB3aXRoIGlkIG1hdGNoaW5nIHRoZSByZWNvbW1lbmRhdGlvbiB2YWx1ZS5cblx0XHQvLyBEZWxheSBzY3JvbGwgYnkgMTAwbXMgdG8gYWxsb3cgbmF2aWdhdGlvbiB0byB0aGUgc2VjdGlvbiB0byBjb21wbGV0ZSBhbmQgZW5zdXJlIHRoZSB0YXJnZXQgZWxlbWVudCBpcyBpbiB2aWV3LlxuXHRcdHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG5cdFx0XHR2YXIgJHRhcmdldCA9ICQoYCMke3JlY29tbWVuZGF0aW9ufWApO1xuXHRcdFx0XG5cdFx0XHRpZiAoISR0YXJnZXQubGVuZ3RoICYmICEkdGFyZ2V0LmlzKCc6dmlzaWJsZScpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0JHRhcmdldFswXS5zY3JvbGxJbnRvVmlldyh7IGJlaGF2aW9yOiAnc21vb3RoJywgYmxvY2s6ICdjZW50ZXInIH0pO1xuXHRcdH0sIDEwMCk7XG5cdFx0XG5cdFx0Ly8gVHJhY2sgZGlyZWN0bHkgd2l0aCBNaXhwYW5lbFxuXHRcdGlmICh0eXBlb2YgbWl4cGFuZWwgIT09ICd1bmRlZmluZWQnICYmIG1peHBhbmVsLnRyYWNrKSB7XG5cdFx0XHQvLyBDaGVjayBpZiB1c2VyIGhhcyBvcHRlZCBpblxuXHRcdFx0aWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSAhPT0gJ3VuZGVmaW5lZCcgJiYgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCAmJiByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkICE9PSAnMCcpIHtcblx0XHRcdFx0Ly8gSWRlbnRpZnkgdXNlciBpZiBhdmFpbGFibGVcblx0XHRcdFx0aWYgKHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQgJiYgdHlwZW9mIG1peHBhbmVsLmlkZW50aWZ5ID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRcdFx0bWl4cGFuZWwuaWRlbnRpZnkocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCk7XG5cdFx0XHRcdH1cblx0XHRcdFx0XG5cdFx0XHRcdC8vIFRyYWNrIHRoZSBCdXR0b24gQ2xpY2tlZCBldmVudFxuXHRcdFx0XHRtaXhwYW5lbC50cmFjaygnQnV0dG9uIENsaWNrZWQnLCB7XG5cdFx0XHRcdFx0J2J1dHRvbic6ICdyb2NrZXQgaW5zaWdodHMgcmVjb21tZW5kYXRpb24nLFxuXHRcdFx0XHRcdCdyZWNvbW1lbmRhdGlvbic6IHJlY29tbWVuZGF0aW9uLFxuXHRcdFx0XHRcdCdwbHVnaW4nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG5cdFx0XHRcdFx0J2JyYW5kJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHRcdFx0J2FwcGxpY2F0aW9uJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwLFxuXHRcdFx0XHRcdCdjb250ZXh0Jzogcm9ja2V0X21peHBhbmVsX2RhdGEuY29udGV4dCxcblx0XHRcdFx0XHQncGF0aCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGhcblx0XHRcdFx0fSk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcbn0pO1xuIiwiY2xhc3MgUm9ja2V0TWl4cGFuZWwge1xuXG5cdHRyYWNrZWRUYWJzID0gW1xuXHRcdCdkYXNoYm9hcmQnLFxuXHRcdCdyb2NrZXRfaW5zaWdodHMnXG5cdF07XG5cblx0Y29uc3RydWN0b3IoIGNvbmZpZyApIHtcblx0XHR0aGlzLmNvbmZpZyA9IGNvbmZpZztcblx0fVxuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplcyB0aGUgaGFuZGxlci5cblx0ICovXG5cdGluaXQoKSB7XG5cdFx0aWYgKCB0eXBlb2YgbWl4cGFuZWwgPT09ICd1bmRlZmluZWQnIHx8ICFtaXhwYW5lbC50cmFjayApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0aWYgKCAhdGhpcy5jb25maWcub3B0aW5fZW5hYmxlZCB8fCB0aGlzLmNvbmZpZy5vcHRpbl9lbmFibGVkID09PSAnMCcpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0bWl4cGFuZWwuaWRlbnRpZnkodGhpcy5jb25maWcudXNlcl9pZCk7XG5cdFx0dGhpcy5faW5pdExpc3RlbmVycyggdGhpcyApO1xuXHR9XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIHRoZSBldmVudCBsaXN0ZW5lcnMuXG5cdCAqXG5cdCAqIEBwYXJhbSBzZWxmIGluc3RhbmNlIG9mIHRoaXMgb2JqZWN0LCB1c2VkIGZvciBiaW5kaW5nIFwidGhpc1wiIHRvIHRoZSBsaXN0ZW5lcnMuXG5cdCAqL1xuXHRfaW5pdExpc3RlbmVycyggc2VsZiApIHtcblx0XHR3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lciggJ2hhc2hjaGFuZ2UnLCBzZWxmLl9vbkhhc2hDaGFuZ2UuYmluZCggc2VsZiApICk7XG5cdFx0d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdsb2FkJywgc2VsZi5fb25QYWdlTG9hZC5iaW5kKCBzZWxmICkgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBFdmVudCBsaXN0ZW5lciB3aGVuIHRoZSBoYXNoIGNoYW5nZWQgaW4gYSBwYWdlLlxuXHQgKlxuXHQgKiBAcGFyYW0gRXZlbnQgZXZlbnQgRXZlbnQgaW5zdGFuY2UuXG5cdCAqL1xuXHRfb25IYXNoQ2hhbmdlKCBldmVudCApIHtcblx0XHRjb25zdCBvbGRIYXNoID0gdGhpcy5fY2xlYW5IYXNoKG5ldyBVUkwoZXZlbnQub2xkVVJMKS5oYXNoKTtcblx0XHRjb25zdCBuZXdIYXNoID0gdGhpcy5fY2xlYW5IYXNoKG5ldyBVUkwoZXZlbnQubmV3VVJMKS5oYXNoKTtcblxuXHRcdGlmICggIXRoaXMuX2NhblRyYWNrVGFiKG5ld0hhc2gpICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRoaXMuX3NlbmRQYWdlVmlld2VkRXZlbnQodGhpcy5fZ2V0U291cmNlKCBvbGRIYXNoICksIG5ld0hhc2gpO1xuXHR9XG5cblx0X29uUGFnZUxvYWQoKSB7XG5cdFx0Y29uc3QgbmV3SGFzaCA9IHRoaXMuX2NsZWFuSGFzaCh3aW5kb3cubG9jYXRpb24uaGFzaCk7XG5cdFx0aWYgKCAhdGhpcy5fY2FuVHJhY2tUYWIobmV3SGFzaCkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGhpcy5fc2VuZFBhZ2VWaWV3ZWRFdmVudCh0aGlzLl9nZXRTb3VyY2UoKSwgbmV3SGFzaCk7XG5cdH1cblxuXHRfY2xlYW5IYXNoKCB0YWJIYXNoICkge1xuXHRcdGlmICghdGFiSGFzaCB8fCAhdGFiSGFzaC5zdGFydHNXaXRoKCcjJykpIHtcblx0XHRcdHJldHVybiB0YWJIYXNoO1xuXHRcdH1cblx0XHRyZXR1cm4gdGFiSGFzaC5zdWJzdHJpbmcoMSk7XG5cdH1cblxuXHRfY2FuVHJhY2tUYWIodGFiSGFzaCkge1xuXHRcdHJldHVybiB0aGlzLnRyYWNrZWRUYWJzLmluY2x1ZGVzKHRhYkhhc2gpO1xuXHR9XG5cblx0X2dldFNvdXJjZSggb2xkSGFzaCA9ICcnICkge1xuXHRcdGlmICggb2xkSGFzaCApIHtcblx0XHRcdHJldHVybiBgc2V0dGluZ3NfJHtvbGRIYXNofWA7XG5cdFx0fVxuXG5cdFx0bGV0IHNvdXJjZSA9IHRoaXMuX2dldFNvdXJjZUZyb21RdWVyeVN0cmluZ0FuZFJlbW92ZUl0KCk7XG5cdFx0aWYgKCBzb3VyY2UgKSB7XG5cdFx0XHRyZXR1cm4gc291cmNlO1xuXHRcdH1cblxuXHRcdHJldHVybiB0aGlzLl9nZXRTb3VyY2VGcm9tUmVmZXJyZXIoKTtcblx0fVxuXG5cdF9nZXRTb3VyY2VGcm9tUXVlcnlTdHJpbmdBbmRSZW1vdmVJdCgpIHtcblx0XHRjb25zdCB1cmwgPSBuZXcgVVJMKHdpbmRvdy5sb2NhdGlvbi5ocmVmKTtcblx0XHRjb25zdCB1cmxQYXJhbXMgPSB1cmwuc2VhcmNoUGFyYW1zO1xuXG5cdFx0Ly8gMS4gQ2hlY2sgZm9yIGV4cGxpY2l0IHNvdXJjZSBwYXJhbVxuXHRcdGlmICghdXJsUGFyYW1zLmhhcygncm9ja2V0X3NvdXJjZScpKSB7XG5cdFx0XHRyZXR1cm4gJyc7XG5cdFx0fVxuXG5cdFx0Ly8gMi4gR2V0IHRoZSB2YWx1ZVxuXHRcdGNvbnN0IHNvdXJjZVZhbHVlID0gdXJsUGFyYW1zLmdldCgncm9ja2V0X3NvdXJjZScpO1xuXG5cdFx0Ly8gMy4gRGVsZXRlIHRoZSBwYXJhbWV0ZXIgZnJvbSB0aGUgVVJMU2VhcmNoUGFyYW1zIG9iamVjdFxuXHRcdHVybFBhcmFtcy5kZWxldGUoJ3JvY2tldF9zb3VyY2UnKTtcblxuXHRcdC8vIDQuIFVwZGF0ZSB0aGUgYnJvd3NlcidzIFVSTCB1c2luZyB0aGUgSGlzdG9yeSBBUElcblx0XHQvLyBUaGlzIHJlbW92ZXMgdGhlIHBhcmFtZXRlciBmcm9tIHRoZSBVUkwgYmFyIHdpdGhvdXQgcmVsb2FkaW5nIHRoZSBwYWdlLlxuXHRcdHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZShudWxsLCAnJywgdXJsLnNlYXJjaCA/IHVybC5ocmVmIDogdXJsLnBhdGhuYW1lKTtcblxuXHRcdC8vIDUuIFJldHVybiB0aGUgcmV0cmlldmVkIHZhbHVlXG5cdFx0cmV0dXJuIHNvdXJjZVZhbHVlO1xuXHR9XG5cblx0X2dldFNvdXJjZUZyb21SZWZlcnJlcigpIHtcblx0XHRjb25zdCByZWZlcnJlciA9IGRvY3VtZW50LnJlZmVycmVyO1xuXHRcdGlmICghcmVmZXJyZXIpIHtcblx0XHRcdHJldHVybiAnbm9yZWZlcnJlcic7XG5cdFx0fVxuXHRcdGlmICghcmVmZXJyZXIuaW5jbHVkZXMod2luZG93LmxvY2F0aW9uLmhvc3RuYW1lKSkge1xuXHRcdFx0cmV0dXJuICdleHRlcm5hbCc7XG5cdFx0fVxuXHRcdHJldHVybiAnaW50ZXJuYWwnO1xuXHR9XG5cblx0X3NlbmRQYWdlVmlld2VkRXZlbnQoc291cmNlLCBuZXdIYXNoKSB7XG5cdFx0bWl4cGFuZWwudHJhY2soJ1BhZ2UgVmlld2VkJywge1xuXHRcdFx0cGF0aDogYC93cC1hZG1pbi9vcHRpb25zLWdlbmVyYWwucGhwP3BhZ2U9d3Byb2NrZXQjJHtuZXdIYXNofWAsXG5cdFx0XHRwYWdlX25hbWU6IG5ld0hhc2gucmVwbGFjZSgnXycsICcgJyksXG5cdFx0XHRzb3VyY2U6IHNvdXJjZSxcblx0XHRcdHBsdWdpbjogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0YnJhbmQ6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuXHRcdFx0YXBwbGljYXRpb246IHJvY2tldF9taXhwYW5lbF9kYXRhLmFwcCxcblx0XHRcdGNvbnRleHQ6IHJvY2tldF9taXhwYW5lbF9kYXRhLmNvbnRleHRcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBOYW1lZCBzdGF0aWMgY29uc3RydWN0b3IgdG8gZW5jYXBzdWxhdGUgaG93IHRvIGNyZWF0ZSB0aGUgb2JqZWN0LlxuXHQgKi9cblx0c3RhdGljIHJ1bigpIHtcblx0XHQvLyBCYWlsIG91dCBpZiB0aGUgY29uZmlndXJhdGlvbiBub3QgcGFzc2VkIGZyb20gdGhlIHNlcnZlci5cblx0XHRpZiAoIHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSA9PT0gJ3VuZGVmaW5lZCcgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Y29uc3QgaW5zdGFuY2UgPSBuZXcgUm9ja2V0TWl4cGFuZWwoIHJvY2tldF9taXhwYW5lbF9kYXRhICk7XG5cdFx0aW5zdGFuY2UuaW5pdCgpO1xuXHR9XG59XG5cblJvY2tldE1peHBhbmVsLnJ1bigpO1xuIiwiZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbiAoKSB7XG5cbiAgICB2YXIgJHBhZ2VNYW5hZ2VyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcIi53cHItQ29udGVudFwiKTtcbiAgICBpZigkcGFnZU1hbmFnZXIpe1xuICAgICAgICBuZXcgUGFnZU1hbmFnZXIoJHBhZ2VNYW5hZ2VyKTtcbiAgICB9XG5cbn0pO1xuXG5cbi8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qXFxcblx0XHRDTEFTUyBQQUdFTUFOQUdFUlxuXFwqLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xuLyoqXG4gKiBNYW5hZ2VzIHRoZSBkaXNwbGF5IG9mIHBhZ2VzIC8gc2VjdGlvbiBmb3IgV1AgUm9ja2V0IHBsdWdpblxuICpcbiAqIFB1YmxpYyBtZXRob2QgOlxuICAgICBkZXRlY3RJRCAtIERldGVjdCBJRCB3aXRoIGhhc2hcbiAgICAgZ2V0Qm9keVRvcCAtIEdldCBib2R5IHRvcCBwb3NpdGlvblxuXHQgY2hhbmdlIC0gRGlzcGxheXMgdGhlIGNvcnJlc3BvbmRpbmcgcGFnZVxuICpcbiAqL1xuXG5mdW5jdGlvbiBQYWdlTWFuYWdlcihhRWxlbSkge1xuXG4gICAgdmFyIHJlZlRoaXMgPSB0aGlzO1xuXG4gICAgdGhpcy4kYm9keSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItYm9keScpO1xuICAgIHRoaXMuJG1lbnVJdGVtcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItbWVudUl0ZW0nKTtcbiAgICB0aGlzLiRzdWJtaXRCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQgPiBmb3JtID4gI3dwci1vcHRpb25zLXN1Ym1pdCcpO1xuICAgIHRoaXMuJHBhZ2VzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1QYWdlJyk7XG4gICAgdGhpcy4kc2lkZWJhciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItU2lkZWJhcicpO1xuICAgIHRoaXMuJGNvbnRlbnQgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQnKTtcbiAgICB0aGlzLiR0aXBzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1Db250ZW50LXRpcHMnKTtcbiAgICB0aGlzLiRsaW5rcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItYm9keSBhJyk7XG4gICAgdGhpcy4kbWVudUl0ZW0gPSBudWxsO1xuICAgIHRoaXMuJHBhZ2UgPSBudWxsO1xuICAgIHRoaXMucGFnZUlkID0gbnVsbDtcbiAgICB0aGlzLmJvZHlUb3AgPSAwO1xuICAgIHRoaXMuYnV0dG9uVGV4dCA9IHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZTtcblxuICAgIHJlZlRoaXMuZ2V0Qm9keVRvcCgpO1xuXG4gICAgLy8gSWYgdXJsIHBhZ2UgY2hhbmdlXG4gICAgd2luZG93Lm9uaGFzaGNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuICAgICAgICByZWZUaGlzLmRldGVjdElEKCk7XG4gICAgfVxuXG4gICAgLy8gSWYgaGFzaCBhbHJlYWR5IGV4aXN0IChhZnRlciByZWZyZXNoIHBhZ2UgZm9yIGV4YW1wbGUpXG4gICAgaWYod2luZG93LmxvY2F0aW9uLmhhc2gpe1xuICAgICAgICB0aGlzLmJvZHlUb3AgPSAwO1xuICAgICAgICB0aGlzLmRldGVjdElEKCk7XG4gICAgfVxuICAgIGVsc2V7XG4gICAgICAgIHZhciBzZXNzaW9uID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ3dwci1oYXNoJyk7XG4gICAgICAgIHRoaXMuYm9keVRvcCA9IDA7XG5cbiAgICAgICAgaWYoc2Vzc2lvbil7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24uaGFzaCA9IHNlc3Npb247XG4gICAgICAgICAgICB0aGlzLmRldGVjdElEKCk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgIHRoaXMuJG1lbnVJdGVtc1swXS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgICAgICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgJ2Rhc2hib2FyZCcpO1xuICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLmhhc2ggPSAnI2Rhc2hib2FyZCc7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBDbGljayBsaW5rIHNhbWUgaGFzaFxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kbGlua3MubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kbGlua3NbaV0ub25jbGljayA9IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgcmVmVGhpcy5nZXRCb2R5VG9wKCk7XG4gICAgICAgICAgICB2YXIgaHJlZlNwbGl0ID0gdGhpcy5ocmVmLnNwbGl0KCcjJylbMV07XG4gICAgICAgICAgICBpZihocmVmU3BsaXQgPT0gcmVmVGhpcy5wYWdlSWQgJiYgaHJlZlNwbGl0ICE9IHVuZGVmaW5lZCl7XG4gICAgICAgICAgICAgICAgcmVmVGhpcy5kZXRlY3RJRCgpO1xuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfTtcbiAgICB9XG5cbiAgICAvLyBDbGljayBsaW5rcyBub3QgV1Agcm9ja2V0IHRvIHJlc2V0IGhhc2hcbiAgICB2YXIgJG90aGVybGlua3MgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcjYWRtaW5tZW51bWFpbiBhLCAjd3BhZG1pbmJhciBhJyk7XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCAkb3RoZXJsaW5rcy5sZW5ndGg7IGkrKykge1xuICAgICAgICAkb3RoZXJsaW5rc1tpXS5vbmNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCAnJyk7XG4gICAgICAgIH07XG4gICAgfVxuXG59XG5cblxuLypcbiogUGFnZSBkZXRlY3QgSURcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZGV0ZWN0SUQgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnBhZ2VJZCA9IHdpbmRvdy5sb2NhdGlvbi5oYXNoLnNwbGl0KCcjJylbMV07XG4gICAgdGhpcy5wYWdlSWQgPSB0aGlzLnBhZ2VJZC5pbmNsdWRlcygnPScpID8gdGhpcy5wYWdlSWQuc3BsaXQoJz0nKVswXSA6IHRoaXMucGFnZUlkO1xuICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd3cHItaGFzaCcsIHRoaXMucGFnZUlkKTtcblxuICAgIHRoaXMuJHBhZ2UgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLVBhZ2UjJyArIHRoaXMucGFnZUlkKTtcbiAgICB0aGlzLiRtZW51SXRlbSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3cHItbmF2LScgKyB0aGlzLnBhZ2VJZCk7XG5cbiAgICB0aGlzLmNoYW5nZSgpO1xufVxuXG5cblxuLypcbiogR2V0IGJvZHkgdG9wIHBvc2l0aW9uXG4qL1xuUGFnZU1hbmFnZXIucHJvdG90eXBlLmdldEJvZHlUb3AgPSBmdW5jdGlvbigpIHtcbiAgICB2YXIgYm9keVBvcyA9IHRoaXMuJGJvZHkuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgdGhpcy5ib2R5VG9wID0gYm9keVBvcy50b3AgKyB3aW5kb3cucGFnZVlPZmZzZXQgLSA0NzsgLy8gI3dwYWRtaW5iYXIgKyBwYWRkaW5nLXRvcCAud3ByLXdyYXAgLSAxIC0gNDdcbn1cblxuXG5cbi8qXG4qIFBhZ2UgY2hhbmdlXG4qL1xuUGFnZU1hbmFnZXIucHJvdG90eXBlLmNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuXG4gICAgdmFyIHJlZlRoaXMgPSB0aGlzO1xuICAgIGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5zY3JvbGxUb3AgPSByZWZUaGlzLmJvZHlUb3A7XG5cbiAgICAvLyBIaWRlIG90aGVyIHBhZ2VzXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRwYWdlcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRwYWdlc1tpXS5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuJG1lbnVJdGVtcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRtZW51SXRlbXNbaV0uY2xhc3NMaXN0LnJlbW92ZSgnaXNBY3RpdmUnKTtcbiAgICB9XG5cbiAgICAvLyBTaG93IGN1cnJlbnQgZGVmYXVsdCBwYWdlXG4gICAgdGhpcy4kcGFnZS5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG5cbiAgICBpZiAoIG51bGwgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicgKSApIHtcbiAgICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJywgJ29uJyApO1xuICAgIH1cblxuICAgIGlmICggJ29uJyA9PT0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ3dwci1zaG93LXNpZGViYXInKSApIHtcbiAgICAgICAgdGhpcy4kc2lkZWJhci5zdHlsZS5kaXNwbGF5ID0gJ2ZsZXgnO1xuICAgIH0gZWxzZSBpZiAoICdvZmYnID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyN3cHItanMtdGlwcycpLnJlbW92ZUF0dHJpYnV0ZSggJ2NoZWNrZWQnICk7XG4gICAgfVxuXG4gICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICB0aGlzLiRtZW51SXRlbS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZSA9IHRoaXMuYnV0dG9uVGV4dDtcbiAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5hZGQoJ2lzTm90RnVsbCcpO1xuXG4gICAgY29uc3QgcGFnZXNXaXRob3V0U3VibWl0ID0gW1xuICAgICAgICAnZGFzaGJvYXJkJyxcbiAgICAgICAgJ2FkZG9ucycsXG4gICAgICAgICdkYXRhYmFzZScsXG4gICAgICAgICd0b29scycsXG4gICAgICAgICdhZGRvbnMnLFxuICAgICAgICAnaW1hZ2lmeScsXG4gICAgICAgICd0dXRvcmlhbHMnLFxuICAgICAgICAncGx1Z2lucycsXG4gICAgXTtcblxuICAgIC8vIEV4Y2VwdGlvbiBmb3IgZGFzaGJvYXJkXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXNoYm9hcmRcIil7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5yZW1vdmUoJ2lzTm90RnVsbCcpO1xuICAgIH1cblxuICAgIGlmICh0aGlzLnBhZ2VJZCA9PSBcImltYWdpZnlcIikge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIHRoaXMuJHRpcHMuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICBpZiAocGFnZXNXaXRob3V0U3VibWl0LmluY2x1ZGVzKHRoaXMucGFnZUlkKSkge1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG59O1xuIiwiLyoqXG4gKiBSZWNvbW1lbmRhdGlvbnMgV2lkZ2V0IEhhbmRsZXJcbiAqXG4gKiBMaXN0ZW5zIGZvciBHbG9iYWwgU2NvcmUgdXBkYXRlcyBhbmQgZmV0Y2hlcy91cGRhdGVzIHJlY29tbWVuZGF0aW9ucyBkeW5hbWljYWxseS5cbiAqL1xudmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgcmVjb21tZW5kYXRpb25zIHdpZGdldCBVSSBiYXNlZCBvbiB0aGUgZmV0Y2hlZCBkYXRhLlxuXHQgKlxuXHQgKiBAcGFyYW0ge09iamVjdH0gZGF0YSAtIFRoZSByZWNvbW1lbmRhdGlvbnMgZGF0YSBmcm9tIHRoZSBBUEkuXG5cdCAqIEBwYXJhbSB7QXJyYXl9IGRhdGEucmVjb21tZW5kYXRpb25zIC0gQXJyYXkgb2YgcmVjb21tZW5kYXRpb25zIGRldGFpbHMuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBkYXRhLnJlY29tbWVuZGF0aW9ucy5odG1sIC0gUmVjb21tZW5kYXRpb25zIEhUTUwuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVSZWNvbW1lbmRhdGlvbnNXaWRnZXQoZGF0YSkge1xuXHRcdGNvbnN0IHdpZGdldCA9ICQoJy53cHItcmVjb21tZW5kYXRpb25zJyk7XG5cblx0XHRpZiAoIXdpZGdldCB8fCAhZGF0YT8ucmVjb21tZW5kYXRpb25zPy5odG1sKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gVXBkYXRlIHRoZSB3aWRnZXQgY29udGVudCB3aXRoIHRoZSBuZXcgcmVjb21tZW5kYXRpb25zIEhUTUxcblx0XHR3aWRnZXQucmVwbGFjZVdpdGgoZGF0YT8ucmVjb21tZW5kYXRpb25zPy5odG1sKTtcblx0fVxuXG5cblxuXHQvKipcblx0ICogRmV0Y2hlcyB0aGUgY3VycmVudCByZWNvbW1lbmRhdGlvbnMgc3RhdHVzIGZyb20gdGhlIFJFU1QgQVBJLlxuXHQgKi9cblx0ZnVuY3Rpb24gZmV0Y2hSZWNvbW1lbmRhdGlvbnNTdGF0dXMoKSB7XG5cdFx0Ly8gVXNlIFdvcmRQcmVzcyBSRVNUIEFQSSBjbGllbnQgaWYgYXZhaWxhYmxlXG5cdFx0aWYgKHdpbmRvdy53cCAmJiB3aW5kb3cud3AuYXBpRmV0Y2gpIHtcblx0XHRcdHdpbmRvdy53cC5hcGlGZXRjaCh7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JlY29tbWVuZGF0aW9ucydcblx0XHRcdH0pXG5cdFx0XHRcdC50aGVuKGZ1bmN0aW9uIChkYXRhKSB7XG5cdFx0XHRcdFx0dXBkYXRlUmVjb21tZW5kYXRpb25zV2lkZ2V0KGRhdGEpO1xuXHRcdFx0XHR9KVxuXHRcdFx0XHQuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcignRmFpbGVkIHRvIGZldGNoIHJlY29tbWVuZGF0aW9ucyBzdGF0dXM6JywgZXJyb3IpO1xuXHRcdFx0XHR9KTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0Ly8gRmFsbGJhY2sgdG8gZmV0Y2ggQVBJXG5cdFx0XHRmZXRjaCh3aW5kb3cud3BBcGlTZXR0aW5ncz8ucm9vdCArICd3cC1yb2NrZXQvdjEvcmVjb21tZW5kYXRpb25zJywge1xuXHRcdFx0XHRoZWFkZXJzOiB7XG5cdFx0XHRcdFx0J1gtV1AtTm9uY2UnOiB3aW5kb3cud3BBcGlTZXR0aW5ncz8ubm9uY2UgfHwgJydcblx0XHRcdFx0fVxuXHRcdFx0fSlcblx0XHRcdFx0LnRoZW4oZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG5cdFx0XHRcdFx0aWYgKCFyZXNwb25zZS5vaykge1xuXHRcdFx0XHRcdFx0dGhyb3cgbmV3IEVycm9yKCdOZXR3b3JrIHJlc3BvbnNlIHdhcyBub3Qgb2snKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0cmV0dXJuIHJlc3BvbnNlLmpzb24oKTtcblx0XHRcdFx0fSlcblx0XHRcdFx0LnRoZW4oZnVuY3Rpb24gKGRhdGEpIHtcblx0XHRcdFx0XHR1cGRhdGVSZWNvbW1lbmRhdGlvbnNXaWRnZXQoZGF0YSk7XG5cdFx0XHRcdH0pXG5cdFx0XHRcdC5jYXRjaChmdW5jdGlvbiAoZXJyb3IpIHtcblx0XHRcdFx0XHRjb25zb2xlLmVycm9yKCdGYWlsZWQgdG8gZmV0Y2ggcmVjb21tZW5kYXRpb25zIHN0YXR1czonLCBlcnJvcik7XG5cdFx0XHRcdH0pO1xuXHRcdH1cblx0fVxuXG5cdC8qKlxuXHQgKiBMaXN0ZW4gZm9yIEdsb2JhbCBTY29yZSB1cGRhdGUgZXZlbnQuXG5cdCAqIFRoaXMgaXMgZmlyZWQgYnkgYWpheC5qcyB3aGVuIHRoZSBHbG9iYWwgU2NvcmUgcG9sbGluZyBkZXRlY3RzIGEgY2hhbmdlLlxuXHQgKi9cblx0JChkb2N1bWVudCkub24oJ3dwckdsb2JhbFNjb3JlVXBkYXRlZCByb2NrZXQtaW5zaWdodHMtcGFnZS1hZGRlZCByb2NrZXQtaW5zaWdodHMtcGFnZS1yZXRlc3QnLCAoKSA9PiB7XG5cdFx0ZmV0Y2hSZWNvbW1lbmRhdGlvbnNTdGF0dXMoKTtcblx0fSlcbn0pO1xuIiwiLyplc2xpbnQtZW52IGVzNiwgYnJvd3NlciovXG4vKiBnbG9iYWwgTWljcm9Nb2RhbCwgbWl4cGFuZWwsIHJvY2tldF9taXhwYW5lbF9kYXRhLCByb2NrZXRfYWpheF9kYXRhLCBhamF4dXJsICovXG4oICggZG9jdW1lbnQsIHdpbmRvdyApID0+IHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLXJvY2tldGNkbi1vcGVuJyApLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRlbC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdFx0Y29uc3QgaXNDVEEgPSBlbC5jbGFzc0xpc3QuY29udGFpbnMoICd3cHItcm9ja2V0Y2RuLXByaWNpbmctLWN0YScgKTtcblx0XHRcdFx0Y2hlY2tCdXR0b25VcmxBbmRPcGVuKCBpc0NUQSApO1xuXHRcdFx0fSApO1xuXHRcdH0gKTtcblxuXHRcdC8vIEFsd2F5cyBpbml0aWFsaXplIE1pY3JvTW9kYWwgdG8gc2V0IHVwIGNsb3NlIGhhbmRsZXJzXG5cdFx0TWljcm9Nb2RhbC5pbml0KCB7XG5cdFx0XHRkaXNhYmxlU2Nyb2xsOiB0cnVlXG5cdFx0fSApO1xuXG5cdFx0Ly8gT25seSBhdXRvLW9wZW4gbW9kYWwgaWYgdGhlcmUncyBubyBkaXJlY3QgYnV0dG9uIFVSTFxuXHRcdGlmICggISB3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsIHx8IHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgPT09ICcnICkge1xuXHRcdFx0bWF5YmVPcGVuTW9kYWwoKTtcblx0XHRcdG1heWJlT3Blbk1vZGFsRnJvbVVSTCgpO1xuXG5cdFx0XHRjb25zdCBpZnJhbWUgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0Y2RuLWlmcmFtZScpO1xuXHRcdFx0Y29uc3QgbG9hZGVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwci1yb2NrZXRjZG4tbW9kYWwtbG9hZGVyJyk7XG5cdFx0XHRpZiAoIGlmcmFtZSAmJiBsb2FkZXIgKSB7XG5cdFx0XHRcdGlmcmFtZS5hZGRFdmVudExpc3RlbmVyKCdsb2FkJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRcdFx0bG9hZGVyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG5cdFx0XHRcdH0pO1xuXHRcdFx0fVxuXHRcdH1cblx0fSApO1xuXG5cdC8qKlxuXHQgKiBDaGVja3MgaWYgdGhlIHVzZXIgaXMgY3VycmVudGx5IG9uIHRoZSBDRE4gdGFiLlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIG9uIENETiB0YWIsIGZhbHNlIG90aGVyd2lzZS5cblx0ICovXG5cdGZ1bmN0aW9uIGlzT25DRE5UYWIoKSB7XG5cdFx0cmV0dXJuIHdpbmRvdy5sb2NhdGlvbi5oYXNoID09PSAnI3BhZ2VfY2RuJztcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3Mgd2hlbiBhIHZpc2libGUgUm9ja2V0Q0ROIHVwc2VsbCBiYW5uZXIgaXMgdmlld2VkLlxuXHQgKiBPbmx5IHRyYWNrcyBvbmNlIHBlciBwYWdlIHNlc3Npb24uXG5cdCAqL1xuXHRsZXQgYmFubmVyVmlld1RyYWNrZWQgPSBmYWxzZTtcblxuXHRmdW5jdGlvbiBtYXliZVRyYWNrQmFubmVyVmlldygpIHtcblx0XHRpZiAoIGJhbm5lclZpZXdUcmFja2VkIHx8ICEgaXNPbkNETlRhYigpICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IHNtYWxsQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YS1zbWFsbCcgKTtcblx0XHRjb25zdCBiaWdDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhJyApO1xuXG5cdFx0Ly8gT25seSB0cmFjayBpZiBvbmUgb2YgdGhlIGJhbm5lcnMgaXMgdmlzaWJsZS5cblx0XHRpZiAoIGJpZ0NUQSAmJiAhIGJpZ0NUQS5jbGFzc0xpc3QuY29udGFpbnMoICd3cHItaXNIaWRkZW4nICkgKSB7XG5cdFx0XHR0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCgpO1xuXHRcdFx0YmFubmVyVmlld1RyYWNrZWQgPSB0cnVlO1xuXHRcdH0gZWxzZSBpZiAoIHNtYWxsQ1RBICYmICEgc21hbGxDVEEuY2xhc3NMaXN0LmNvbnRhaW5zKCAnd3ByLWlzSGlkZGVuJyApICkge1xuXHRcdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJWaWV3ZWQoKTtcblx0XHRcdGJhbm5lclZpZXdUcmFja2VkID0gdHJ1ZTtcblx0XHR9XG5cdH1cblxuXHR3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lciggJ2xvYWQnLCAoKSA9PiB7XG5cdFx0bGV0IG9wZW5DVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tb3Blbi1jdGEnICksXG5cdFx0XHRjbG9zZUNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jbG9zZS1jdGEnICksXG5cdFx0XHRzbWFsbENUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jdGEtc21hbGwnICksXG5cdFx0XHRiaWdDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhJyApLFxuXHRcdFx0aW5wdXRUb2dnbGUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLXJvY2tldGNkbi10b2dnbGUtLWlucHV0Jyk7XG5cblx0XHQvLyBUcmFjayBiYW5uZXIgdmlldyBvbiBwYWdlIGxvYWQgaWYgYmFubmVyIGlzIHZpc2libGUgYW5kIHVzZXIgaXMgb24gQ0ROIHRhYi5cblx0XHRtYXliZVRyYWNrQmFubmVyVmlldygpO1xuXG5cdFx0Ly8gVHJhY2sgYmFubmVyIHZpZXcgd2hlbiB1c2VyIG5hdmlnYXRlcyB0byBDRE4gdGFiLlxuXHRcdHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnaGFzaGNoYW5nZScsICgpID0+IHtcblx0XHRcdG1heWJlVHJhY2tCYW5uZXJWaWV3KCk7XG5cdFx0fSApO1xuXG5cdFx0Ly8gUHJpY2VzIHNlbGVjdG9ycyBmb3IgdG9nZ2xpbmcgdmlzaWJpbGl0eSBiYXNlZCBvbiB0aGUgYmlsbGluZyBjeWNsZSB0b2dnbGUgc3RhdGUuXG5cdFx0Y29uc3QgcHJpY2VzID0ge1xuXHRcdFx0bW9udGhseToge1xuXHRcdFx0XHRyZWd1bGFyOiBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLXJvY2tldGNkbi1wcmljaW5nLXJlZ3VsYXItcHJpY2UtLW1vbnRobHknKSxcblx0XHRcdFx0Y3VycmVudDogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy0tbW9udGhseScpLFxuXHRcdFx0XHRwZXJpb2Q6IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItcm9ja2V0Y2RuLXByaWNpbmctLWJpbGxpbmctcGVyaW9kLS1tb250aGx5Jylcblx0XHRcdH0sXG5cdFx0XHR5ZWFybHk6IHtcblx0XHRcdFx0cmVndWxhcjogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy1yZWd1bGFyLXByaWNlLS15ZWFybHknKSxcblx0XHRcdFx0Y3VycmVudDogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy0tYW5udWFsJyksXG5cdFx0XHRcdHBlcmlvZDogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy0tYmlsbGluZy1wZXJpb2QtLXllYXJseScpXG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0aWYgKCBudWxsICE9PSBvcGVuQ1RBICYmIG51bGwgIT09IHNtYWxsQ1RBICYmIG51bGwgIT09IGJpZ0NUQSApIHtcblx0XHRcdG9wZW5DVEEuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCBlICkgPT4ge1xuXHRcdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHRcdFx0c21hbGxDVEEuY2xhc3NMaXN0LmFkZCggJ3dwci1pc0hpZGRlbicgKTtcblx0XHRcdFx0YmlnQ1RBLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItaXNIaWRkZW4nICk7XG5cblx0XHRcdFx0Ly8gVHJhY2sgdXBzZWxsIGJhbm5lciB2aWV3IG9ubHkgaWYgb24gQ0ROIHRhYi5cblx0XHRcdFx0aWYgKCBpc09uQ0ROVGFiKCkgJiYgISBiYW5uZXJWaWV3VHJhY2tlZCApIHtcblx0XHRcdFx0XHR0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCgpO1xuXHRcdFx0XHRcdGJhbm5lclZpZXdUcmFja2VkID0gdHJ1ZTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHNlbmRIVFRQUmVxdWVzdCggZ2V0UG9zdERhdGEoICdiaWcnICkgKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHRpZiAoIG51bGwgIT09IGNsb3NlQ1RBICYmIG51bGwgIT09IHNtYWxsQ1RBICYmIG51bGwgIT09IGJpZ0NUQSApIHtcblx0XHRcdGNsb3NlQ1RBLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0XHRcdHNtYWxsQ1RBLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdGJpZ0NUQS5jbGFzc0xpc3QuYWRkKCAnd3ByLWlzSGlkZGVuJyApO1xuXG5cdFx0XHRcdHNlbmRIVFRQUmVxdWVzdCggZ2V0UG9zdERhdGEoICdzbWFsbCcgKSApO1xuXHRcdFx0fSApO1xuXHRcdH1cblxuXHRcdGZ1bmN0aW9uIGdldFBvc3REYXRhKCBzdGF0dXMgKSB7XG5cdFx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj10b2dnbGVfcm9ja2V0Y2RuX2N0YSc7XG5cdFx0XHRwb3N0RGF0YSArPSAnJnN0YXR1cz0nICsgc3RhdHVzO1xuXHRcdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdFx0cmV0dXJuIHBvc3REYXRhO1xuXHRcdH1cblxuXHRcdC8vIERpc3BsYXkgdGhlIGNvcnJlY3QgcHJpY2VzIG9uIHBhZ2UgYmFzZWQgb24gYmlsbGluZyBjeWNsZSB0b2dnbGUgc3RhdGUuXG5cdFx0aW5wdXRUb2dnbGUuYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBpc1llYXJseSA9IHRoaXMuY2hlY2tlZDtcblxuXHRcdFx0aWYgKGlzWWVhcmx5KSB7XG5cdFx0XHRcdE9iamVjdC52YWx1ZXMocHJpY2VzLm1vbnRobHkpLmZvckVhY2gobGlzdCA9PiBsaXN0LmZvckVhY2goZWwgPT4gZWwuY2xhc3NMaXN0LmFkZCgnd3ByLWlzSGlkZGVuJykpKTtcblx0XHRcdFx0T2JqZWN0LnZhbHVlcyhwcmljZXMueWVhcmx5KS5mb3JFYWNoKGxpc3QgPT4gbGlzdC5mb3JFYWNoKGVsID0+IGVsLmNsYXNzTGlzdC5yZW1vdmUoJ3dwci1pc0hpZGRlbicpKSk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRPYmplY3QudmFsdWVzKHByaWNlcy5tb250aGx5KS5mb3JFYWNoKGxpc3QgPT4gbGlzdC5mb3JFYWNoKGVsID0+IGVsLmNsYXNzTGlzdC5yZW1vdmUoJ3dwci1pc0hpZGRlbicpKSk7XG5cdFx0XHRcdE9iamVjdC52YWx1ZXMocHJpY2VzLnllYXJseSkuZm9yRWFjaChsaXN0ID0+IGxpc3QuZm9yRWFjaChlbCA9PiBlbC5jbGFzc0xpc3QuYWRkKCd3cHItaXNIaWRkZW4nKSkpO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBVcGRhdGUgdGhlIGJ1dHRvbiBVUkwgd2l0aCB0aGUgY29ycmVjdCBpc19tb250aGx5IHBhcmFtZXRlci5cblx0XHRcdHVwZGF0ZUJ1dHRvblVybEJpbGxpbmdDeWNsZShpc1llYXJseSk7XG5cdFx0fSk7XG5cblx0XHQvLyBUcmFjayBSb2NrZXRDRE4gYWN0aXZhdGlvbiBmYWlsZWQgQ1RBIGNsaWNrXG5cdFx0Y29uc3QgYWN0aXZhdGlvbkNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyN3cHItcm9ja2V0Y2RuLWFjdGl2YXRpb24tY3RhJyk7XG5cdFx0aWYgKGFjdGl2YXRpb25DVEEpIHtcblx0XHRcdGFjdGl2YXRpb25DVEEuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PiB7XG5cdFx0XHRcdHRyYWNrUm9ja2V0Q0ROQWN0aXZhdGlvbkNUQSgpO1xuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdH0gKTtcblxuXHR3aW5kb3cub25tZXNzYWdlID0gKCBlICkgPT4ge1xuXHRcdGNvbnN0IGlmcmFtZVVSTCA9IHJvY2tldF9hamF4X2RhdGEub3JpZ2luX3VybDtcblxuXHRcdGlmICggZS5vcmlnaW4gIT09IGlmcmFtZVVSTCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRzZXRDRE5GcmFtZUhlaWdodCggZS5kYXRhICk7XG5cdFx0Y2xvc2VNb2RhbCggZS5kYXRhICk7XG5cdFx0dG9rZW5IYW5kbGVyKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHByb2Nlc3NTdGF0dXMoIGUuZGF0YSApO1xuXHRcdGVuYWJsZUNETiggZS5kYXRhLCBpZnJhbWVVUkwgKTtcblx0XHRkaXNhYmxlQ0ROKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZS5kYXRhICk7XG5cdH07XG5cblx0ZnVuY3Rpb24gY2hlY2tCdXR0b25VcmxBbmRPcGVuKCBpc0NUQSApIHtcblx0XHQvLyBUcmFjayBDVEEgY2xpY2sgaWYgdGhpcyBpcyB0aGUgcHJpY2luZyBDVEEgYnV0dG9uLlxuXHRcdGlmICggaXNDVEEgKSB7XG5cdFx0XHR0cmFja1JvY2tldENETlVwc2VsbENUQUNsaWNrZWQoKTtcblx0XHR9XG5cblx0XHQvLyBDaGVjayBpZiBidXR0b24gVVJMIHdhcyBpbmplY3RlZCBieSBQSFBcblx0XHRpZiAoIHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgJiYgd2luZG93LnJvY2tldGNkbkJ1dHRvblVybCAhPT0gJycgKSB7XG5cdFx0XHQvLyBTbWFsbCBkZWxheSB0byBlbnN1cmUgTWl4cGFuZWwgZXZlbnQgaXMgc2VudCBiZWZvcmUgbmF2aWdhdGlvblxuXHRcdFx0c2V0VGltZW91dCggZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHdpbmRvdy5sb2NhdGlvbi5ocmVmID0gd2luZG93LnJvY2tldGNkbkJ1dHRvblVybDtcblx0XHRcdH0sIDEwMCApO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHQvLyBTaG93IGlmcmFtZSBtb2RhbCBhcyB1c3VhbFxuXHRcdFx0TWljcm9Nb2RhbC5zaG93KCAnd3ByLXJvY2tldGNkbi1tb2RhbCcgKTtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgYnV0dG9uIFVSTCB3aXRoIHRoZSBjb3JyZWN0IGlzX21vbnRobHkgcGFyYW1ldGVyIGJhc2VkIG9uIGJpbGxpbmcgY3ljbGUgdG9nZ2xlLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2Jvb2xlYW59IGlzWWVhcmx5IC0gVHJ1ZSBpZiB5ZWFybHkgYmlsbGluZyBpcyBzZWxlY3RlZCwgZmFsc2UgZm9yIG1vbnRobHkuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVCdXR0b25VcmxCaWxsaW5nQ3ljbGUoIGlzWWVhcmx5ICkge1xuXHRcdGlmICggISB3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsIHx8IHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgPT09ICcnICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgPSBzZXRJc01vbnRobHlQYXJhbSh3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsLCBpc1llYXJseSk7XG5cdH1cblxuXHRmdW5jdGlvbiBtYXliZU9wZW5Nb2RhbCgpIHtcblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc3RhdHVzJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cblx0XHRcdFx0aWYgKCB0cnVlID09PSByZXNwb25zZVR4dC5zdWNjZXNzICkge1xuXHRcdFx0XHRcdE1pY3JvTW9kYWwuc2hvdyggJ3dwci1yb2NrZXRjZG4tbW9kYWwnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gbWF5YmVPcGVuTW9kYWxGcm9tVVJMKCkge1xuXHRcdGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMoIHdpbmRvdy5sb2NhdGlvbi5zZWFyY2ggKTtcblxuXHRcdGlmICggdXJsUGFyYW1zLmhhcyggJ3JvY2tldGNkbl9vcGVuX2lmcmFtZScgKSAmJiAnMScgPT09IHVybFBhcmFtcy5nZXQoICdyb2NrZXRjZG5fb3Blbl9pZnJhbWUnICkgKSB7XG5cdFx0XHQvLyBTZXQgaGFzaCB0byBwYWdlX2NkbiB0byBzaG93IENETiB0YWIgYmVoaW5kIG1vZGFsXG5cdFx0XHR3aW5kb3cubG9jYXRpb24uaGFzaCA9ICcjcGFnZV9jZG4nO1xuXG5cdFx0XHRNaWNyb01vZGFsLnNob3coICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXG5cdFx0XHQvLyBDbGVhbiB1cCB0aGUgVVJMIHRvIHByZXZlbnQgcmUtdHJpZ2dlcmluZyBvbiByZWZyZXNoXG5cdFx0XHR1cmxQYXJhbXMuZGVsZXRlKCAncm9ja2V0Y2RuX29wZW5faWZyYW1lJyApO1xuXHRcdFx0Y29uc3Qgc2VhcmNoID0gdXJsUGFyYW1zLnRvU3RyaW5nKCk7XG5cdFx0XHRjb25zdCBuZXdVUkwgPSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgKyAoIHNlYXJjaCA/ICc/JyArIHNlYXJjaCA6ICcnICkgKyB3aW5kb3cubG9jYXRpb24uaGFzaDtcblx0XHRcdHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSgge30sICcnLCBuZXdVUkwgKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiBjbG9zZU1vZGFsKCBkYXRhICkge1xuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAnY2RuRnJhbWVDbG9zZScgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRNaWNyb01vZGFsLmNsb3NlKCAnd3ByLXJvY2tldGNkbi1tb2RhbCcgKTtcblx0XHQvLyBFbnN1cmUgc2Nyb2xsIGlzIHJlc3RvcmVkXG5cdFx0ZG9jdW1lbnQuYm9keS5zdHlsZS5vdmVyZmxvdyA9ICcnO1xuXG5cdFx0bGV0IHBhZ2VzID0gWyAnaWZyYW1lLXBheW1lbnQtc3VjY2VzcycsICdpZnJhbWUtdW5zdWJzY3JpYmUtc3VjY2VzcycgXTtcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAnY2RuX3BhZ2VfbWVzc2FnZScgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRpZiAoIHBhZ2VzLmluZGV4T2YoIGRhdGEuY2RuX3BhZ2VfbWVzc2FnZSApID09PSAtMSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5sb2NhdGlvbi5yZWxvYWQoKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHByb2Nlc3NTdGF0dXMoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fcHJvY2VzcycgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc2V0Jztcblx0XHRwb3N0RGF0YSArPSAnJnN0YXR1cz0nICsgZGF0YS5yb2NrZXRjZG5fcHJvY2Vzcztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0c2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXHR9XG5cblx0ZnVuY3Rpb24gZW5hYmxlQ0ROKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdXJsJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fZW5hYmxlJztcblx0XHRwb3N0RGF0YSArPSAnJmNkbl91cmw9JyArIGRhdGEucm9ja2V0Y2RuX3VybDtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHNldElzTW9udGhseVBhcmFtKHVybCwgaXNZZWFybHkpIHtcblx0XHQvLyBSZW1vdmUgYW55IGV4aXN0aW5nIGlzX21vbnRobHkgcGFyYW1cblx0XHRsZXQgbmV3VXJsID0gdXJsLnJlcGxhY2UoLyhbPyZdKWlzX21vbnRobHk9W14mXSovZywgJycpO1xuXHRcdC8vIEFkZCB0aGUgbmV3IHBhcmFtXG5cdFx0Y29uc3Qgc2VwID0gbmV3VXJsLmluY2x1ZGVzKCc/JykgPyAnJicgOiAnPyc7XG5cdFx0bmV3VXJsICs9IHNlcCArICdpc19tb250aGx5PScgKyAoaXNZZWFybHkgPyAnMCcgOiAnMScpO1xuXHRcdHJldHVybiBuZXdVcmw7XG5cdH1cblxuXHRmdW5jdGlvbiBkaXNhYmxlQ0ROKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fZGlzYWJsZScgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX2Rpc2FibGUnO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXG5cdFx0cmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSAoKSA9PiB7XG5cdFx0XHRpZiAoIHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gWE1MSHR0cFJlcXVlc3QuRE9ORSAmJiAyMDAgPT09IHJlcXVlc3Quc3RhdHVzICkge1xuXHRcdFx0XHRsZXQgcmVzcG9uc2VUeHQgPSBKU09OLnBhcnNlKHJlcXVlc3QucmVzcG9uc2VUZXh0KTtcblx0XHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdCdzdWNjZXNzJzogcmVzcG9uc2VUeHQuc3VjY2Vzcyxcblx0XHRcdFx0XHRcdCdkYXRhJzogcmVzcG9uc2VUeHQuZGF0YSxcblx0XHRcdFx0XHRcdCdyb2NrZXRjZG4nOiB0cnVlXG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdFx0KTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApIHtcblx0XHRjb25zdCBodHRwUmVxdWVzdCA9IG5ldyBYTUxIdHRwUmVxdWVzdCgpO1xuXG5cdFx0aHR0cFJlcXVlc3Qub3BlbiggJ1BPU1QnLCBhamF4dXJsICk7XG5cdFx0aHR0cFJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlciggJ0NvbnRlbnQtVHlwZScsICdhcHBsaWNhdGlvbi94LXd3dy1mb3JtLXVybGVuY29kZWQnICk7XG5cdFx0aHR0cFJlcXVlc3Quc2VuZCggcG9zdERhdGEgKTtcblxuXHRcdHJldHVybiBodHRwUmVxdWVzdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHNldENETkZyYW1lSGVpZ2h0KCBkYXRhICkge1xuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAnY2RuRnJhbWVIZWlnaHQnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0ZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICdyb2NrZXRjZG4taWZyYW1lJyApLnN0eWxlLmhlaWdodCA9IGAkeyBkYXRhLmNkbkZyYW1lSGVpZ2h0IH1weGA7XG5cdH1cblxuXHRmdW5jdGlvbiB0b2tlbkhhbmRsZXIoIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl90b2tlbicgKSApIHtcblx0XHRcdGxldCBkYXRhID0ge3Byb2Nlc3M6XCJzdWJzY3JpYmVcIiwgbWVzc2FnZTpcInRva2VuX25vdF9yZWNlaXZlZFwifTtcblx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0e1xuXHRcdFx0XHRcdCdzdWNjZXNzJzogZmFsc2UsXG5cdFx0XHRcdFx0J2RhdGEnOiBkYXRhLFxuXHRcdFx0XHRcdCdyb2NrZXRjZG4nOiB0cnVlXG5cdFx0XHRcdH0sXG5cdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0KTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249c2F2ZV9yb2NrZXRjZG5fdG9rZW4nO1xuXHRcdHBvc3REYXRhICs9ICcmdmFsdWU9JyArIGRhdGEucm9ja2V0Y2RuX3Rva2VuO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXG5cdFx0cmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSAoKSA9PiB7XG5cdFx0XHRpZiAoIHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gWE1MSHR0cFJlcXVlc3QuRE9ORSAmJiAyMDAgPT09IHJlcXVlc3Quc3RhdHVzICkge1xuXHRcdFx0XHRsZXQgcmVzcG9uc2VUeHQgPSBKU09OLnBhcnNlKHJlcXVlc3QucmVzcG9uc2VUZXh0KTtcblx0XHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdCdzdWNjZXNzJzogcmVzcG9uc2VUeHQuc3VjY2Vzcyxcblx0XHRcdFx0XHRcdCdkYXRhJzogcmVzcG9uc2VUeHQuZGF0YSxcblx0XHRcdFx0XHRcdCdyb2NrZXRjZG4nOiB0cnVlXG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdFx0KTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gdmFsaWRhdGVUb2tlbkFuZENOQU1FKCBkYXRhICkge1xuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3ZhbGlkYXRlX3Rva2VuJyApIHx8ICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl92YWxpZGF0ZV9jbmFtZScgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3ZhbGlkYXRlX3Rva2VuX2NuYW1lJztcblx0XHRwb3N0RGF0YSArPSAnJmNkbl91cmw9JyArIGRhdGEucm9ja2V0Y2RuX3ZhbGlkYXRlX2NuYW1lO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3Rva2VuPScgKyBkYXRhLnJvY2tldGNkbl92YWxpZGF0ZV90b2tlbjtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3MgUm9ja2V0Q0ROIGFjdGl2YXRpb24gZmFpbGVkIENUQSBjbGljayB3aXRoIE1peHBhbmVsLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5BY3RpdmF0aW9uQ1RBKCkge1xuXHRcdGlmICh0eXBlb2YgbWl4cGFuZWwgPT09ICd1bmRlZmluZWQnIHx8ICFtaXhwYW5lbC50cmFjaykge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIENoZWNrIGlmIHVzZXIgaGFzIG9wdGVkIGluXG5cdFx0aWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSA9PT0gJ3VuZGVmaW5lZCcgfHwgIXJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgfHwgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gSWRlbnRpZnkgdXNlciBpZiBhdmFpbGFibGVcblx0XHRpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdG1peHBhbmVsLmlkZW50aWZ5KHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQpO1xuXHRcdH1cblxuXHRcdG1peHBhbmVsLnRyYWNrKCdSb2NrZXRDRE4gQWN0aXZhdGlvbiBGYWlsZWQgQ1RBIENsaWNrZWQnLCB7XG5cdFx0XHRjb250ZXh0OiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuXHRcdFx0cGx1Z2luOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG5cdFx0XHRicmFuZDogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHRhcHBsaWNhdGlvbjogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwXG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogVHJhY2tzIGEgUm9ja2V0Q0ROIHVwc2VsbCBldmVudCB3aXRoIE1peHBhbmVsLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gZXZlbnROYW1lICAgVGhlIE1peHBhbmVsIGV2ZW50IG5hbWUuXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBbZXh0cmFQcm9wc10gT3B0aW9uYWwgYWRkaXRpb25hbCBwcm9wZXJ0aWVzIHRvIG1lcmdlLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5VcHNlbGxNaXhwYW5lbEV2ZW50KCBldmVudE5hbWUsIGV4dHJhUHJvcHMgKSB7XG5cdFx0aWYgKCB0eXBlb2YgbWl4cGFuZWwgPT09ICd1bmRlZmluZWQnIHx8ICEgbWl4cGFuZWwudHJhY2sgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW4uXG5cdFx0aWYgKCB0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnIHx8ICEgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCB8fCByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkID09PSAnMCcgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gSWRlbnRpZnkgdXNlciBpZiBhdmFpbGFibGUuXG5cdFx0aWYgKCAhIHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQgfHwgdHlwZW9mIG1peHBhbmVsLmlkZW50aWZ5ICE9PSAnZnVuY3Rpb24nICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdG1peHBhbmVsLmlkZW50aWZ5KCByb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICk7XG5cblx0XHR2YXIgcHJvcHMgPSB7XG5cdFx0XHRjb250ZXh0OiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuXHRcdFx0cGx1Z2luOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG5cdFx0XHRicmFuZDogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHRhcHBsaWNhdGlvbjogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwLFxuXHRcdFx0cGF0aDogcm9ja2V0X21peHBhbmVsX2RhdGEucGF0aFxuXHRcdH07XG5cblx0XHQvLyBNZXJnZSBleHRyYSBwcm9wZXJ0aWVzIGlmIHByb3ZpZGVkIGFuZCB2YWxpZC5cblx0XHRpZiAoIGV4dHJhUHJvcHMgJiYgdHlwZW9mIGV4dHJhUHJvcHMgPT09ICdvYmplY3QnICkge1xuXHRcdFx0Zm9yICggdmFyIGtleSBpbiBleHRyYVByb3BzICkge1xuXHRcdFx0XHRpZiAoIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbCggZXh0cmFQcm9wcywga2V5ICkgKSB7XG5cdFx0XHRcdFx0cHJvcHNbIGtleSBdID0gZXh0cmFQcm9wc1sga2V5IF07XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9XG5cblx0XHRtaXhwYW5lbC50cmFjayggZXZlbnROYW1lLCBwcm9wcyApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBSb2NrZXRDRE4gdXBzZWxsIGJhbm5lciB2aWV3IHdpdGggTWl4cGFuZWwuXG5cdCAqL1xuXHRmdW5jdGlvbiB0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCgpIHtcblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIEJhbm5lciBWaWV3ZWQnICk7XG5cdH1cblxuXHQvKipcblx0ICogVHJhY2tzIFJvY2tldENETiB1cHNlbGwgQ1RBIGNsaWNrIHdpdGggTWl4cGFuZWwuXG5cdCAqL1xuXHRmdW5jdGlvbiB0cmFja1JvY2tldENETlVwc2VsbENUQUNsaWNrZWQoKSB7XG5cdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxNaXhwYW5lbEV2ZW50KCAnUm9ja2V0Q0ROIFVwc2VsbCBDVEEgQ2xpY2tlZCcgKTtcblx0fVxufSApKCBkb2N1bWVudCwgd2luZG93ICk7XG4iLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJUaW1lbGluZUxpdGVcIixbXCJjb3JlLkFuaW1hdGlvblwiLFwiY29yZS5TaW1wbGVUaW1lbGluZVwiLFwiVHdlZW5MaXRlXCJdLGZ1bmN0aW9uKHQsZSxpKXt2YXIgcz1mdW5jdGlvbih0KXtlLmNhbGwodGhpcyx0KSx0aGlzLl9sYWJlbHM9e30sdGhpcy5hdXRvUmVtb3ZlQ2hpbGRyZW49dGhpcy52YXJzLmF1dG9SZW1vdmVDaGlsZHJlbj09PSEwLHRoaXMuc21vb3RoQ2hpbGRUaW1pbmc9dGhpcy52YXJzLnNtb290aENoaWxkVGltaW5nPT09ITAsdGhpcy5fc29ydENoaWxkcmVuPSEwLHRoaXMuX29uVXBkYXRlPXRoaXMudmFycy5vblVwZGF0ZTt2YXIgaSxzLHI9dGhpcy52YXJzO2ZvcihzIGluIHIpaT1yW3NdLGEoaSkmJi0xIT09aS5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIikmJihyW3NdPXRoaXMuX3N3YXBTZWxmSW5QYXJhbXMoaSkpO2Eoci50d2VlbnMpJiZ0aGlzLmFkZChyLnR3ZWVucywwLHIuYWxpZ24sci5zdGFnZ2VyKX0scj0xZS0xMCxuPWkuX2ludGVybmFscy5pc1NlbGVjdG9yLGE9aS5faW50ZXJuYWxzLmlzQXJyYXksbz1bXSxoPXdpbmRvdy5fZ3NEZWZpbmUuZ2xvYmFscyxsPWZ1bmN0aW9uKHQpe3ZhciBlLGk9e307Zm9yKGUgaW4gdClpW2VdPXRbZV07cmV0dXJuIGl9LF89ZnVuY3Rpb24odCxlLGkscyl7dC5fdGltZWxpbmUucGF1c2UodC5fc3RhcnRUaW1lKSxlJiZlLmFwcGx5KHN8fHQuX3RpbWVsaW5lLGl8fG8pfSx1PW8uc2xpY2UsZj1zLnByb3RvdHlwZT1uZXcgZTtyZXR1cm4gcy52ZXJzaW9uPVwiMS4xMi4xXCIsZi5jb25zdHJ1Y3Rvcj1zLGYua2lsbCgpLl9nYz0hMSxmLnRvPWZ1bmN0aW9uKHQsZSxzLHIpe3ZhciBuPXMucmVwZWF0JiZoLlR3ZWVuTWF4fHxpO3JldHVybiBlP3RoaXMuYWRkKG5ldyBuKHQsZSxzKSxyKTp0aGlzLnNldCh0LHMscil9LGYuZnJvbT1mdW5jdGlvbih0LGUscyxyKXtyZXR1cm4gdGhpcy5hZGQoKHMucmVwZWF0JiZoLlR3ZWVuTWF4fHxpKS5mcm9tKHQsZSxzKSxyKX0sZi5mcm9tVG89ZnVuY3Rpb24odCxlLHMscixuKXt2YXIgYT1yLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aTtyZXR1cm4gZT90aGlzLmFkZChhLmZyb21Ubyh0LGUscyxyKSxuKTp0aGlzLnNldCh0LHIsbil9LGYuc3RhZ2dlclRvPWZ1bmN0aW9uKHQsZSxyLGEsbyxoLF8sZil7dmFyIHAsYz1uZXcgcyh7b25Db21wbGV0ZTpoLG9uQ29tcGxldGVQYXJhbXM6XyxvbkNvbXBsZXRlU2NvcGU6ZixzbW9vdGhDaGlsZFRpbWluZzp0aGlzLnNtb290aENoaWxkVGltaW5nfSk7Zm9yKFwic3RyaW5nXCI9PXR5cGVvZiB0JiYodD1pLnNlbGVjdG9yKHQpfHx0KSxuKHQpJiYodD11LmNhbGwodCwwKSksYT1hfHwwLHA9MDt0Lmxlbmd0aD5wO3ArKylyLnN0YXJ0QXQmJihyLnN0YXJ0QXQ9bChyLnN0YXJ0QXQpKSxjLnRvKHRbcF0sZSxsKHIpLHAqYSk7cmV0dXJuIHRoaXMuYWRkKGMsbyl9LGYuc3RhZ2dlckZyb209ZnVuY3Rpb24odCxlLGkscyxyLG4sYSxvKXtyZXR1cm4gaS5pbW1lZGlhdGVSZW5kZXI9MCE9aS5pbW1lZGlhdGVSZW5kZXIsaS5ydW5CYWNrd2FyZHM9ITAsdGhpcy5zdGFnZ2VyVG8odCxlLGkscyxyLG4sYSxvKX0sZi5zdGFnZ2VyRnJvbVRvPWZ1bmN0aW9uKHQsZSxpLHMscixuLGEsbyxoKXtyZXR1cm4gcy5zdGFydEF0PWkscy5pbW1lZGlhdGVSZW5kZXI9MCE9cy5pbW1lZGlhdGVSZW5kZXImJjAhPWkuaW1tZWRpYXRlUmVuZGVyLHRoaXMuc3RhZ2dlclRvKHQsZSxzLHIsbixhLG8saCl9LGYuY2FsbD1mdW5jdGlvbih0LGUscyxyKXtyZXR1cm4gdGhpcy5hZGQoaS5kZWxheWVkQ2FsbCgwLHQsZSxzKSxyKX0sZi5zZXQ9ZnVuY3Rpb24odCxlLHMpe3JldHVybiBzPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwocywwLCEwKSxudWxsPT1lLmltbWVkaWF0ZVJlbmRlciYmKGUuaW1tZWRpYXRlUmVuZGVyPXM9PT10aGlzLl90aW1lJiYhdGhpcy5fcGF1c2VkKSx0aGlzLmFkZChuZXcgaSh0LDAsZSkscyl9LHMuZXhwb3J0Um9vdD1mdW5jdGlvbih0LGUpe3Q9dHx8e30sbnVsbD09dC5zbW9vdGhDaGlsZFRpbWluZyYmKHQuc21vb3RoQ2hpbGRUaW1pbmc9ITApO3ZhciByLG4sYT1uZXcgcyh0KSxvPWEuX3RpbWVsaW5lO2ZvcihudWxsPT1lJiYoZT0hMCksby5fcmVtb3ZlKGEsITApLGEuX3N0YXJ0VGltZT0wLGEuX3Jhd1ByZXZUaW1lPWEuX3RpbWU9YS5fdG90YWxUaW1lPW8uX3RpbWUscj1vLl9maXJzdDtyOyluPXIuX25leHQsZSYmciBpbnN0YW5jZW9mIGkmJnIudGFyZ2V0PT09ci52YXJzLm9uQ29tcGxldGV8fGEuYWRkKHIsci5fc3RhcnRUaW1lLXIuX2RlbGF5KSxyPW47cmV0dXJuIG8uYWRkKGEsMCksYX0sZi5hZGQ9ZnVuY3Rpb24ocixuLG8saCl7dmFyIGwsXyx1LGYscCxjO2lmKFwibnVtYmVyXCIhPXR5cGVvZiBuJiYobj10aGlzLl9wYXJzZVRpbWVPckxhYmVsKG4sMCwhMCxyKSksIShyIGluc3RhbmNlb2YgdCkpe2lmKHIgaW5zdGFuY2VvZiBBcnJheXx8ciYmci5wdXNoJiZhKHIpKXtmb3Iobz1vfHxcIm5vcm1hbFwiLGg9aHx8MCxsPW4sXz1yLmxlbmd0aCx1PTA7Xz51O3UrKylhKGY9clt1XSkmJihmPW5ldyBzKHt0d2VlbnM6Zn0pKSx0aGlzLmFkZChmLGwpLFwic3RyaW5nXCIhPXR5cGVvZiBmJiZcImZ1bmN0aW9uXCIhPXR5cGVvZiBmJiYoXCJzZXF1ZW5jZVwiPT09bz9sPWYuX3N0YXJ0VGltZStmLnRvdGFsRHVyYXRpb24oKS9mLl90aW1lU2NhbGU6XCJzdGFydFwiPT09byYmKGYuX3N0YXJ0VGltZS09Zi5kZWxheSgpKSksbCs9aDtyZXR1cm4gdGhpcy5fdW5jYWNoZSghMCl9aWYoXCJzdHJpbmdcIj09dHlwZW9mIHIpcmV0dXJuIHRoaXMuYWRkTGFiZWwocixuKTtpZihcImZ1bmN0aW9uXCIhPXR5cGVvZiByKXRocm93XCJDYW5ub3QgYWRkIFwiK3IrXCIgaW50byB0aGUgdGltZWxpbmU7IGl0IGlzIG5vdCBhIHR3ZWVuLCB0aW1lbGluZSwgZnVuY3Rpb24sIG9yIHN0cmluZy5cIjtyPWkuZGVsYXllZENhbGwoMCxyKX1pZihlLnByb3RvdHlwZS5hZGQuY2FsbCh0aGlzLHIsbiksKHRoaXMuX2djfHx0aGlzLl90aW1lPT09dGhpcy5fZHVyYXRpb24pJiYhdGhpcy5fcGF1c2VkJiZ0aGlzLl9kdXJhdGlvbjx0aGlzLmR1cmF0aW9uKCkpZm9yKHA9dGhpcyxjPXAucmF3VGltZSgpPnIuX3N0YXJ0VGltZTtwLl90aW1lbGluZTspYyYmcC5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmc/cC50b3RhbFRpbWUocC5fdG90YWxUaW1lLCEwKTpwLl9nYyYmcC5fZW5hYmxlZCghMCwhMSkscD1wLl90aW1lbGluZTtyZXR1cm4gdGhpc30sZi5yZW1vdmU9ZnVuY3Rpb24oZSl7aWYoZSBpbnN0YW5jZW9mIHQpcmV0dXJuIHRoaXMuX3JlbW92ZShlLCExKTtpZihlIGluc3RhbmNlb2YgQXJyYXl8fGUmJmUucHVzaCYmYShlKSl7Zm9yKHZhciBpPWUubGVuZ3RoOy0taT4tMTspdGhpcy5yZW1vdmUoZVtpXSk7cmV0dXJuIHRoaXN9cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIGU/dGhpcy5yZW1vdmVMYWJlbChlKTp0aGlzLmtpbGwobnVsbCxlKX0sZi5fcmVtb3ZlPWZ1bmN0aW9uKHQsaSl7ZS5wcm90b3R5cGUuX3JlbW92ZS5jYWxsKHRoaXMsdCxpKTt2YXIgcz10aGlzLl9sYXN0O3JldHVybiBzP3RoaXMuX3RpbWU+cy5fc3RhcnRUaW1lK3MuX3RvdGFsRHVyYXRpb24vcy5fdGltZVNjYWxlJiYodGhpcy5fdGltZT10aGlzLmR1cmF0aW9uKCksdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RvdGFsRHVyYXRpb24pOnRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPXRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249MCx0aGlzfSxmLmFwcGVuZD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLmFkZCh0LHRoaXMuX3BhcnNlVGltZU9yTGFiZWwobnVsbCxlLCEwLHQpKX0sZi5pbnNlcnQ9Zi5pbnNlcnRNdWx0aXBsZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5hZGQodCxlfHwwLGkscyl9LGYuYXBwZW5kTXVsdGlwbGU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuYWRkKHQsdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChudWxsLGUsITAsdCksaSxzKX0sZi5hZGRMYWJlbD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLl9sYWJlbHNbdF09dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChlKSx0aGlzfSxmLmFkZFBhdXNlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmNhbGwoXyxbXCJ7c2VsZn1cIixlLGksc10sdGhpcyx0KX0sZi5yZW1vdmVMYWJlbD1mdW5jdGlvbih0KXtyZXR1cm4gZGVsZXRlIHRoaXMuX2xhYmVsc1t0XSx0aGlzfSxmLmdldExhYmVsVGltZT1mdW5jdGlvbih0KXtyZXR1cm4gbnVsbCE9dGhpcy5fbGFiZWxzW3RdP3RoaXMuX2xhYmVsc1t0XTotMX0sZi5fcGFyc2VUaW1lT3JMYWJlbD1mdW5jdGlvbihlLGkscyxyKXt2YXIgbjtpZihyIGluc3RhbmNlb2YgdCYmci50aW1lbGluZT09PXRoaXMpdGhpcy5yZW1vdmUocik7ZWxzZSBpZihyJiYociBpbnN0YW5jZW9mIEFycmF5fHxyLnB1c2gmJmEocikpKWZvcihuPXIubGVuZ3RoOy0tbj4tMTspcltuXWluc3RhbmNlb2YgdCYmcltuXS50aW1lbGluZT09PXRoaXMmJnRoaXMucmVtb3ZlKHJbbl0pO2lmKFwic3RyaW5nXCI9PXR5cGVvZiBpKXJldHVybiB0aGlzLl9wYXJzZVRpbWVPckxhYmVsKGkscyYmXCJudW1iZXJcIj09dHlwZW9mIGUmJm51bGw9PXRoaXMuX2xhYmVsc1tpXT9lLXRoaXMuZHVyYXRpb24oKTowLHMpO2lmKGk9aXx8MCxcInN0cmluZ1wiIT10eXBlb2YgZXx8IWlzTmFOKGUpJiZudWxsPT10aGlzLl9sYWJlbHNbZV0pbnVsbD09ZSYmKGU9dGhpcy5kdXJhdGlvbigpKTtlbHNle2lmKG49ZS5pbmRleE9mKFwiPVwiKSwtMT09PW4pcmV0dXJuIG51bGw9PXRoaXMuX2xhYmVsc1tlXT9zP3RoaXMuX2xhYmVsc1tlXT10aGlzLmR1cmF0aW9uKCkraTppOnRoaXMuX2xhYmVsc1tlXStpO2k9cGFyc2VJbnQoZS5jaGFyQXQobi0xKStcIjFcIiwxMCkqTnVtYmVyKGUuc3Vic3RyKG4rMSkpLGU9bj4xP3RoaXMuX3BhcnNlVGltZU9yTGFiZWwoZS5zdWJzdHIoMCxuLTEpLDAscyk6dGhpcy5kdXJhdGlvbigpfXJldHVybiBOdW1iZXIoZSkraX0sZi5zZWVrPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMudG90YWxUaW1lKFwibnVtYmVyXCI9PXR5cGVvZiB0P3Q6dGhpcy5fcGFyc2VUaW1lT3JMYWJlbCh0KSxlIT09ITEpfSxmLnN0b3A9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5wYXVzZWQoITApfSxmLmdvdG9BbmRQbGF5PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucGxheSh0LGUpfSxmLmdvdG9BbmRTdG9wPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucGF1c2UodCxlKX0sZi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3RoaXMuX2djJiZ0aGlzLl9lbmFibGVkKCEwLCExKTt2YXIgcyxuLGEsaCxsLF89dGhpcy5fZGlydHk/dGhpcy50b3RhbER1cmF0aW9uKCk6dGhpcy5fdG90YWxEdXJhdGlvbix1PXRoaXMuX3RpbWUsZj10aGlzLl9zdGFydFRpbWUscD10aGlzLl90aW1lU2NhbGUsYz10aGlzLl9wYXVzZWQ7aWYodD49Xz8odGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9Xyx0aGlzLl9yZXZlcnNlZHx8dGhpcy5faGFzUGF1c2VkQ2hpbGQoKXx8KG49ITAsaD1cIm9uQ29tcGxldGVcIiwwPT09dGhpcy5fZHVyYXRpb24mJigwPT09dHx8MD50aGlzLl9yYXdQcmV2VGltZXx8dGhpcy5fcmF3UHJldlRpbWU9PT1yKSYmdGhpcy5fcmF3UHJldlRpbWUhPT10JiZ0aGlzLl9maXJzdCYmKGw9ITAsdGhpcy5fcmF3UHJldlRpbWU+ciYmKGg9XCJvblJldmVyc2VDb21wbGV0ZVwiKSkpLHRoaXMuX3Jhd1ByZXZUaW1lPXRoaXMuX2R1cmF0aW9ufHwhZXx8dHx8dGhpcy5fcmF3UHJldlRpbWU9PT10P3Q6cix0PV8rMWUtNCk6MWUtNz50Pyh0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT0wLCgwIT09dXx8MD09PXRoaXMuX2R1cmF0aW9uJiZ0aGlzLl9yYXdQcmV2VGltZSE9PXImJih0aGlzLl9yYXdQcmV2VGltZT4wfHwwPnQmJnRoaXMuX3Jhd1ByZXZUaW1lPj0wKSkmJihoPVwib25SZXZlcnNlQ29tcGxldGVcIixuPXRoaXMuX3JldmVyc2VkKSwwPnQ/KHRoaXMuX2FjdGl2ZT0hMSwwPT09dGhpcy5fZHVyYXRpb24mJnRoaXMuX3Jhd1ByZXZUaW1lPj0wJiZ0aGlzLl9maXJzdCYmKGw9ITApLHRoaXMuX3Jhd1ByZXZUaW1lPXQpOih0aGlzLl9yYXdQcmV2VGltZT10aGlzLl9kdXJhdGlvbnx8IWV8fHR8fHRoaXMuX3Jhd1ByZXZUaW1lPT09dD90OnIsdD0wLHRoaXMuX2luaXR0ZWR8fChsPSEwKSkpOnRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXRoaXMuX3Jhd1ByZXZUaW1lPXQsdGhpcy5fdGltZSE9PXUmJnRoaXMuX2ZpcnN0fHxpfHxsKXtpZih0aGlzLl9pbml0dGVkfHwodGhpcy5faW5pdHRlZD0hMCksdGhpcy5fYWN0aXZlfHwhdGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lIT09dSYmdD4wJiYodGhpcy5fYWN0aXZlPSEwKSwwPT09dSYmdGhpcy52YXJzLm9uU3RhcnQmJjAhPT10aGlzLl90aW1lJiYoZXx8dGhpcy52YXJzLm9uU3RhcnQuYXBwbHkodGhpcy52YXJzLm9uU3RhcnRTY29wZXx8dGhpcyx0aGlzLnZhcnMub25TdGFydFBhcmFtc3x8bykpLHRoaXMuX3RpbWU+PXUpZm9yKHM9dGhpcy5fZmlyc3Q7cyYmKGE9cy5fbmV4dCwhdGhpcy5fcGF1c2VkfHxjKTspKHMuX2FjdGl2ZXx8cy5fc3RhcnRUaW1lPD10aGlzLl90aW1lJiYhcy5fcGF1c2VkJiYhcy5fZ2MpJiYocy5fcmV2ZXJzZWQ/cy5yZW5kZXIoKHMuX2RpcnR5P3MudG90YWxEdXJhdGlvbigpOnMuX3RvdGFsRHVyYXRpb24pLSh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSk6cy5yZW5kZXIoKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKSkscz1hO2Vsc2UgZm9yKHM9dGhpcy5fbGFzdDtzJiYoYT1zLl9wcmV2LCF0aGlzLl9wYXVzZWR8fGMpOykocy5fYWN0aXZlfHx1Pj1zLl9zdGFydFRpbWUmJiFzLl9wYXVzZWQmJiFzLl9nYykmJihzLl9yZXZlcnNlZD9zLnJlbmRlcigocy5fZGlydHk/cy50b3RhbER1cmF0aW9uKCk6cy5fdG90YWxEdXJhdGlvbiktKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKTpzLnJlbmRlcigodC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpKSxzPWE7dGhpcy5fb25VcGRhdGUmJihlfHx0aGlzLl9vblVwZGF0ZS5hcHBseSh0aGlzLnZhcnMub25VcGRhdGVTY29wZXx8dGhpcyx0aGlzLnZhcnMub25VcGRhdGVQYXJhbXN8fG8pKSxoJiYodGhpcy5fZ2N8fChmPT09dGhpcy5fc3RhcnRUaW1lfHxwIT09dGhpcy5fdGltZVNjYWxlKSYmKDA9PT10aGlzLl90aW1lfHxfPj10aGlzLnRvdGFsRHVyYXRpb24oKSkmJihuJiYodGhpcy5fdGltZWxpbmUuYXV0b1JlbW92ZUNoaWxkcmVuJiZ0aGlzLl9lbmFibGVkKCExLCExKSx0aGlzLl9hY3RpdmU9ITEpLCFlJiZ0aGlzLnZhcnNbaF0mJnRoaXMudmFyc1toXS5hcHBseSh0aGlzLnZhcnNbaCtcIlNjb3BlXCJdfHx0aGlzLHRoaXMudmFyc1toK1wiUGFyYW1zXCJdfHxvKSkpfX0sZi5faGFzUGF1c2VkQ2hpbGQ9ZnVuY3Rpb24oKXtmb3IodmFyIHQ9dGhpcy5fZmlyc3Q7dDspe2lmKHQuX3BhdXNlZHx8dCBpbnN0YW5jZW9mIHMmJnQuX2hhc1BhdXNlZENoaWxkKCkpcmV0dXJuITA7dD10Ll9uZXh0fXJldHVybiExfSxmLmdldENoaWxkcmVuPWZ1bmN0aW9uKHQsZSxzLHIpe3I9cnx8LTk5OTk5OTk5OTk7Zm9yKHZhciBuPVtdLGE9dGhpcy5fZmlyc3Qsbz0wO2E7KXI+YS5fc3RhcnRUaW1lfHwoYSBpbnN0YW5jZW9mIGk/ZSE9PSExJiYobltvKytdPWEpOihzIT09ITEmJihuW28rK109YSksdCE9PSExJiYobj1uLmNvbmNhdChhLmdldENoaWxkcmVuKCEwLGUscykpLG89bi5sZW5ndGgpKSksYT1hLl9uZXh0O3JldHVybiBufSxmLmdldFR3ZWVuc09mPWZ1bmN0aW9uKHQsZSl7dmFyIHMscixuPXRoaXMuX2djLGE9W10sbz0wO2ZvcihuJiZ0aGlzLl9lbmFibGVkKCEwLCEwKSxzPWkuZ2V0VHdlZW5zT2YodCkscj1zLmxlbmd0aDstLXI+LTE7KShzW3JdLnRpbWVsaW5lPT09dGhpc3x8ZSYmdGhpcy5fY29udGFpbnMoc1tyXSkpJiYoYVtvKytdPXNbcl0pO3JldHVybiBuJiZ0aGlzLl9lbmFibGVkKCExLCEwKSxhfSxmLl9jb250YWlucz1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC50aW1lbGluZTtlOyl7aWYoZT09PXRoaXMpcmV0dXJuITA7ZT1lLnRpbWVsaW5lfXJldHVybiExfSxmLnNoaWZ0Q2hpbGRyZW49ZnVuY3Rpb24odCxlLGkpe2k9aXx8MDtmb3IodmFyIHMscj10aGlzLl9maXJzdCxuPXRoaXMuX2xhYmVscztyOylyLl9zdGFydFRpbWU+PWkmJihyLl9zdGFydFRpbWUrPXQpLHI9ci5fbmV4dDtpZihlKWZvcihzIGluIG4pbltzXT49aSYmKG5bc10rPXQpO3JldHVybiB0aGlzLl91bmNhY2hlKCEwKX0sZi5fa2lsbD1mdW5jdGlvbih0LGUpe2lmKCF0JiYhZSlyZXR1cm4gdGhpcy5fZW5hYmxlZCghMSwhMSk7Zm9yKHZhciBpPWU/dGhpcy5nZXRUd2VlbnNPZihlKTp0aGlzLmdldENoaWxkcmVuKCEwLCEwLCExKSxzPWkubGVuZ3RoLHI9ITE7LS1zPi0xOylpW3NdLl9raWxsKHQsZSkmJihyPSEwKTtyZXR1cm4gcn0sZi5jbGVhcj1mdW5jdGlvbih0KXt2YXIgZT10aGlzLmdldENoaWxkcmVuKCExLCEwLCEwKSxpPWUubGVuZ3RoO2Zvcih0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT0wOy0taT4tMTspZVtpXS5fZW5hYmxlZCghMSwhMSk7cmV0dXJuIHQhPT0hMSYmKHRoaXMuX2xhYmVscz17fSksdGhpcy5fdW5jYWNoZSghMCl9LGYuaW52YWxpZGF0ZT1mdW5jdGlvbigpe2Zvcih2YXIgdD10aGlzLl9maXJzdDt0Oyl0LmludmFsaWRhdGUoKSx0PXQuX25leHQ7cmV0dXJuIHRoaXN9LGYuX2VuYWJsZWQ9ZnVuY3Rpb24odCxpKXtpZih0PT09dGhpcy5fZ2MpZm9yKHZhciBzPXRoaXMuX2ZpcnN0O3M7KXMuX2VuYWJsZWQodCwhMCkscz1zLl9uZXh0O3JldHVybiBlLnByb3RvdHlwZS5fZW5hYmxlZC5jYWxsKHRoaXMsdCxpKX0sZi5kdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oMCE9PXRoaXMuZHVyYXRpb24oKSYmMCE9PXQmJnRoaXMudGltZVNjYWxlKHRoaXMuX2R1cmF0aW9uL3QpLHRoaXMpOih0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCksdGhpcy5fZHVyYXRpb24pfSxmLnRvdGFsRHVyYXRpb249ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpe2lmKHRoaXMuX2RpcnR5KXtmb3IodmFyIGUsaSxzPTAscj10aGlzLl9sYXN0LG49OTk5OTk5OTk5OTk5O3I7KWU9ci5fcHJldixyLl9kaXJ0eSYmci50b3RhbER1cmF0aW9uKCksci5fc3RhcnRUaW1lPm4mJnRoaXMuX3NvcnRDaGlsZHJlbiYmIXIuX3BhdXNlZD90aGlzLmFkZChyLHIuX3N0YXJ0VGltZS1yLl9kZWxheSk6bj1yLl9zdGFydFRpbWUsMD5yLl9zdGFydFRpbWUmJiFyLl9wYXVzZWQmJihzLT1yLl9zdGFydFRpbWUsdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJih0aGlzLl9zdGFydFRpbWUrPXIuX3N0YXJ0VGltZS90aGlzLl90aW1lU2NhbGUpLHRoaXMuc2hpZnRDaGlsZHJlbigtci5fc3RhcnRUaW1lLCExLC05OTk5OTk5OTk5KSxuPTApLGk9ci5fc3RhcnRUaW1lK3IuX3RvdGFsRHVyYXRpb24vci5fdGltZVNjYWxlLGk+cyYmKHM9aSkscj1lO3RoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249cyx0aGlzLl9kaXJ0eT0hMX1yZXR1cm4gdGhpcy5fdG90YWxEdXJhdGlvbn1yZXR1cm4gMCE9PXRoaXMudG90YWxEdXJhdGlvbigpJiYwIT09dCYmdGhpcy50aW1lU2NhbGUodGhpcy5fdG90YWxEdXJhdGlvbi90KSx0aGlzfSxmLnVzZXNGcmFtZXM9ZnVuY3Rpb24oKXtmb3IodmFyIGU9dGhpcy5fdGltZWxpbmU7ZS5fdGltZWxpbmU7KWU9ZS5fdGltZWxpbmU7cmV0dXJuIGU9PT10Ll9yb290RnJhbWVzVGltZWxpbmV9LGYucmF3VGltZT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9wYXVzZWQ/dGhpcy5fdG90YWxUaW1lOih0aGlzLl90aW1lbGluZS5yYXdUaW1lKCktdGhpcy5fc3RhcnRUaW1lKSp0aGlzLl90aW1lU2NhbGV9LHN9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbihmdW5jdGlvbih0KXtcInVzZSBzdHJpY3RcIjt2YXIgZT10LkdyZWVuU29ja0dsb2JhbHN8fHQ7aWYoIWUuVHdlZW5MaXRlKXt2YXIgaSxzLG4scixhLG89ZnVuY3Rpb24odCl7dmFyIGkscz10LnNwbGl0KFwiLlwiKSxuPWU7Zm9yKGk9MDtzLmxlbmd0aD5pO2krKyluW3NbaV1dPW49bltzW2ldXXx8e307cmV0dXJuIG59LGw9byhcImNvbS5ncmVlbnNvY2tcIiksaD0xZS0xMCxfPVtdLnNsaWNlLHU9ZnVuY3Rpb24oKXt9LG09ZnVuY3Rpb24oKXt2YXIgdD1PYmplY3QucHJvdG90eXBlLnRvU3RyaW5nLGU9dC5jYWxsKFtdKTtyZXR1cm4gZnVuY3Rpb24oaSl7cmV0dXJuIG51bGwhPWkmJihpIGluc3RhbmNlb2YgQXJyYXl8fFwib2JqZWN0XCI9PXR5cGVvZiBpJiYhIWkucHVzaCYmdC5jYWxsKGkpPT09ZSl9fSgpLGY9e30scD1mdW5jdGlvbihpLHMsbixyKXt0aGlzLnNjPWZbaV0/ZltpXS5zYzpbXSxmW2ldPXRoaXMsdGhpcy5nc0NsYXNzPW51bGwsdGhpcy5mdW5jPW47dmFyIGE9W107dGhpcy5jaGVjaz1mdW5jdGlvbihsKXtmb3IodmFyIGgsXyx1LG0sYz1zLmxlbmd0aCxkPWM7LS1jPi0xOykoaD1mW3NbY11dfHxuZXcgcChzW2NdLFtdKSkuZ3NDbGFzcz8oYVtjXT1oLmdzQ2xhc3MsZC0tKTpsJiZoLnNjLnB1c2godGhpcyk7aWYoMD09PWQmJm4pZm9yKF89KFwiY29tLmdyZWVuc29jay5cIitpKS5zcGxpdChcIi5cIiksdT1fLnBvcCgpLG09byhfLmpvaW4oXCIuXCIpKVt1XT10aGlzLmdzQ2xhc3M9bi5hcHBseShuLGEpLHImJihlW3VdPW0sXCJmdW5jdGlvblwiPT10eXBlb2YgZGVmaW5lJiZkZWZpbmUuYW1kP2RlZmluZSgodC5HcmVlblNvY2tBTURQYXRoP3QuR3JlZW5Tb2NrQU1EUGF0aCtcIi9cIjpcIlwiKStpLnNwbGl0KFwiLlwiKS5qb2luKFwiL1wiKSxbXSxmdW5jdGlvbigpe3JldHVybiBtfSk6XCJ1bmRlZmluZWRcIiE9dHlwZW9mIG1vZHVsZSYmbW9kdWxlLmV4cG9ydHMmJihtb2R1bGUuZXhwb3J0cz1tKSksYz0wO3RoaXMuc2MubGVuZ3RoPmM7YysrKXRoaXMuc2NbY10uY2hlY2soKX0sdGhpcy5jaGVjayghMCl9LGM9dC5fZ3NEZWZpbmU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIG5ldyBwKHQsZSxpLHMpfSxkPWwuX2NsYXNzPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gZT1lfHxmdW5jdGlvbigpe30sYyh0LFtdLGZ1bmN0aW9uKCl7cmV0dXJuIGV9LGkpLGV9O2MuZ2xvYmFscz1lO3ZhciB2PVswLDAsMSwxXSxnPVtdLFQ9ZChcImVhc2luZy5FYXNlXCIsZnVuY3Rpb24odCxlLGkscyl7dGhpcy5fZnVuYz10LHRoaXMuX3R5cGU9aXx8MCx0aGlzLl9wb3dlcj1zfHwwLHRoaXMuX3BhcmFtcz1lP3YuY29uY2F0KGUpOnZ9LCEwKSx5PVQubWFwPXt9LHc9VC5yZWdpc3Rlcj1mdW5jdGlvbih0LGUsaSxzKXtmb3IodmFyIG4scixhLG8saD1lLnNwbGl0KFwiLFwiKSxfPWgubGVuZ3RoLHU9KGl8fFwiZWFzZUluLGVhc2VPdXQsZWFzZUluT3V0XCIpLnNwbGl0KFwiLFwiKTstLV8+LTE7KWZvcihyPWhbX10sbj1zP2QoXCJlYXNpbmcuXCIrcixudWxsLCEwKTpsLmVhc2luZ1tyXXx8e30sYT11Lmxlbmd0aDstLWE+LTE7KW89dVthXSx5W3IrXCIuXCIrb109eVtvK3JdPW5bb109dC5nZXRSYXRpbz90OnRbb118fG5ldyB0fTtmb3Iobj1ULnByb3RvdHlwZSxuLl9jYWxjRW5kPSExLG4uZ2V0UmF0aW89ZnVuY3Rpb24odCl7aWYodGhpcy5fZnVuYylyZXR1cm4gdGhpcy5fcGFyYW1zWzBdPXQsdGhpcy5fZnVuYy5hcHBseShudWxsLHRoaXMuX3BhcmFtcyk7dmFyIGU9dGhpcy5fdHlwZSxpPXRoaXMuX3Bvd2VyLHM9MT09PWU/MS10OjI9PT1lP3Q6LjU+dD8yKnQ6MiooMS10KTtyZXR1cm4gMT09PWk/cyo9czoyPT09aT9zKj1zKnM6Mz09PWk/cyo9cypzKnM6ND09PWkmJihzKj1zKnMqcypzKSwxPT09ZT8xLXM6Mj09PWU/czouNT50P3MvMjoxLXMvMn0saT1bXCJMaW5lYXJcIixcIlF1YWRcIixcIkN1YmljXCIsXCJRdWFydFwiLFwiUXVpbnQsU3Ryb25nXCJdLHM9aS5sZW5ndGg7LS1zPi0xOyluPWlbc10rXCIsUG93ZXJcIitzLHcobmV3IFQobnVsbCxudWxsLDEscyksbixcImVhc2VPdXRcIiwhMCksdyhuZXcgVChudWxsLG51bGwsMixzKSxuLFwiZWFzZUluXCIrKDA9PT1zP1wiLGVhc2VOb25lXCI6XCJcIikpLHcobmV3IFQobnVsbCxudWxsLDMscyksbixcImVhc2VJbk91dFwiKTt5LmxpbmVhcj1sLmVhc2luZy5MaW5lYXIuZWFzZUluLHkuc3dpbmc9bC5lYXNpbmcuUXVhZC5lYXNlSW5PdXQ7dmFyIFA9ZChcImV2ZW50cy5FdmVudERpc3BhdGNoZXJcIixmdW5jdGlvbih0KXt0aGlzLl9saXN0ZW5lcnM9e30sdGhpcy5fZXZlbnRUYXJnZXQ9dHx8dGhpc30pO249UC5wcm90b3R5cGUsbi5hZGRFdmVudExpc3RlbmVyPWZ1bmN0aW9uKHQsZSxpLHMsbil7bj1ufHwwO3ZhciBvLGwsaD10aGlzLl9saXN0ZW5lcnNbdF0sXz0wO2ZvcihudWxsPT1oJiYodGhpcy5fbGlzdGVuZXJzW3RdPWg9W10pLGw9aC5sZW5ndGg7LS1sPi0xOylvPWhbbF0sby5jPT09ZSYmby5zPT09aT9oLnNwbGljZShsLDEpOjA9PT1fJiZuPm8ucHImJihfPWwrMSk7aC5zcGxpY2UoXywwLHtjOmUsczppLHVwOnMscHI6bn0pLHRoaXMhPT1yfHxhfHxyLndha2UoKX0sbi5yZW1vdmVFdmVudExpc3RlbmVyPWZ1bmN0aW9uKHQsZSl7dmFyIGkscz10aGlzLl9saXN0ZW5lcnNbdF07aWYocylmb3IoaT1zLmxlbmd0aDstLWk+LTE7KWlmKHNbaV0uYz09PWUpcmV0dXJuIHMuc3BsaWNlKGksMSksdm9pZCAwfSxuLmRpc3BhdGNoRXZlbnQ9ZnVuY3Rpb24odCl7dmFyIGUsaSxzLG49dGhpcy5fbGlzdGVuZXJzW3RdO2lmKG4pZm9yKGU9bi5sZW5ndGgsaT10aGlzLl9ldmVudFRhcmdldDstLWU+LTE7KXM9bltlXSxzLnVwP3MuYy5jYWxsKHMuc3x8aSx7dHlwZTp0LHRhcmdldDppfSk6cy5jLmNhbGwocy5zfHxpKX07dmFyIGs9dC5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUsYj10LmNhbmNlbEFuaW1hdGlvbkZyYW1lLEE9RGF0ZS5ub3d8fGZ1bmN0aW9uKCl7cmV0dXJuKG5ldyBEYXRlKS5nZXRUaW1lKCl9LFM9QSgpO2ZvcihpPVtcIm1zXCIsXCJtb3pcIixcIndlYmtpdFwiLFwib1wiXSxzPWkubGVuZ3RoOy0tcz4tMSYmIWs7KWs9dFtpW3NdK1wiUmVxdWVzdEFuaW1hdGlvbkZyYW1lXCJdLGI9dFtpW3NdK1wiQ2FuY2VsQW5pbWF0aW9uRnJhbWVcIl18fHRbaVtzXStcIkNhbmNlbFJlcXVlc3RBbmltYXRpb25GcmFtZVwiXTtkKFwiVGlja2VyXCIsZnVuY3Rpb24odCxlKXt2YXIgaSxzLG4sbyxsLF89dGhpcyxtPUEoKSxmPWUhPT0hMSYmayxwPTUwMCxjPTMzLGQ9ZnVuY3Rpb24odCl7dmFyIGUscixhPUEoKS1TO2E+cCYmKG0rPWEtYyksUys9YSxfLnRpbWU9KFMtbSkvMWUzLGU9Xy50aW1lLWwsKCFpfHxlPjB8fHQ9PT0hMCkmJihfLmZyYW1lKyssbCs9ZSsoZT49bz8uMDA0Om8tZSkscj0hMCksdCE9PSEwJiYobj1zKGQpKSxyJiZfLmRpc3BhdGNoRXZlbnQoXCJ0aWNrXCIpfTtQLmNhbGwoXyksXy50aW1lPV8uZnJhbWU9MCxfLnRpY2s9ZnVuY3Rpb24oKXtkKCEwKX0sXy5sYWdTbW9vdGhpbmc9ZnVuY3Rpb24odCxlKXtwPXR8fDEvaCxjPU1hdGgubWluKGUscCwwKX0sXy5zbGVlcD1mdW5jdGlvbigpe251bGwhPW4mJihmJiZiP2Iobik6Y2xlYXJUaW1lb3V0KG4pLHM9dSxuPW51bGwsXz09PXImJihhPSExKSl9LF8ud2FrZT1mdW5jdGlvbigpe251bGwhPT1uP18uc2xlZXAoKTpfLmZyYW1lPjEwJiYoUz1BKCktcCs1KSxzPTA9PT1pP3U6ZiYmaz9rOmZ1bmN0aW9uKHQpe3JldHVybiBzZXRUaW1lb3V0KHQsMHwxZTMqKGwtXy50aW1lKSsxKX0sXz09PXImJihhPSEwKSxkKDIpfSxfLmZwcz1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oaT10LG89MS8oaXx8NjApLGw9dGhpcy50aW1lK28sXy53YWtlKCksdm9pZCAwKTppfSxfLnVzZVJBRj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oXy5zbGVlcCgpLGY9dCxfLmZwcyhpKSx2b2lkIDApOmZ9LF8uZnBzKHQpLHNldFRpbWVvdXQoZnVuY3Rpb24oKXtmJiYoIW58fDU+Xy5mcmFtZSkmJl8udXNlUkFGKCExKX0sMTUwMCl9KSxuPWwuVGlja2VyLnByb3RvdHlwZT1uZXcgbC5ldmVudHMuRXZlbnREaXNwYXRjaGVyLG4uY29uc3RydWN0b3I9bC5UaWNrZXI7dmFyIHg9ZChcImNvcmUuQW5pbWF0aW9uXCIsZnVuY3Rpb24odCxlKXtpZih0aGlzLnZhcnM9ZT1lfHx7fSx0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXR8fDAsdGhpcy5fZGVsYXk9TnVtYmVyKGUuZGVsYXkpfHwwLHRoaXMuX3RpbWVTY2FsZT0xLHRoaXMuX2FjdGl2ZT1lLmltbWVkaWF0ZVJlbmRlcj09PSEwLHRoaXMuZGF0YT1lLmRhdGEsdGhpcy5fcmV2ZXJzZWQ9ZS5yZXZlcnNlZD09PSEwLEIpe2F8fHIud2FrZSgpO3ZhciBpPXRoaXMudmFycy51c2VGcmFtZXM/UTpCO2kuYWRkKHRoaXMsaS5fdGltZSksdGhpcy52YXJzLnBhdXNlZCYmdGhpcy5wYXVzZWQoITApfX0pO3I9eC50aWNrZXI9bmV3IGwuVGlja2VyLG49eC5wcm90b3R5cGUsbi5fZGlydHk9bi5fZ2M9bi5faW5pdHRlZD1uLl9wYXVzZWQ9ITEsbi5fdG90YWxUaW1lPW4uX3RpbWU9MCxuLl9yYXdQcmV2VGltZT0tMSxuLl9uZXh0PW4uX2xhc3Q9bi5fb25VcGRhdGU9bi5fdGltZWxpbmU9bi50aW1lbGluZT1udWxsLG4uX3BhdXNlZD0hMTt2YXIgQz1mdW5jdGlvbigpe2EmJkEoKS1TPjJlMyYmci53YWtlKCksc2V0VGltZW91dChDLDJlMyl9O0MoKSxuLnBsYXk9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5yZXZlcnNlZCghMSkucGF1c2VkKCExKX0sbi5wYXVzZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnBhdXNlZCghMCl9LG4ucmVzdW1lPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucGF1c2VkKCExKX0sbi5zZWVrPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMudG90YWxUaW1lKE51bWJlcih0KSxlIT09ITEpfSxuLnJlc3RhcnQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5yZXZlcnNlZCghMSkucGF1c2VkKCExKS50b3RhbFRpbWUodD8tdGhpcy5fZGVsYXk6MCxlIT09ITEsITApfSxuLnJldmVyc2U9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHR8fHRoaXMudG90YWxEdXJhdGlvbigpLGUpLHRoaXMucmV2ZXJzZWQoITApLnBhdXNlZCghMSl9LG4ucmVuZGVyPWZ1bmN0aW9uKCl7fSxuLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpc30sbi5pc0FjdGl2ZT1mdW5jdGlvbigpe3ZhciB0LGU9dGhpcy5fdGltZWxpbmUsaT10aGlzLl9zdGFydFRpbWU7cmV0dXJuIWV8fCF0aGlzLl9nYyYmIXRoaXMuX3BhdXNlZCYmZS5pc0FjdGl2ZSgpJiYodD1lLnJhd1RpbWUoKSk+PWkmJmkrdGhpcy50b3RhbER1cmF0aW9uKCkvdGhpcy5fdGltZVNjYWxlPnR9LG4uX2VuYWJsZWQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXx8ci53YWtlKCksdGhpcy5fZ2M9IXQsdGhpcy5fYWN0aXZlPXRoaXMuaXNBY3RpdmUoKSxlIT09ITAmJih0JiYhdGhpcy50aW1lbGluZT90aGlzLl90aW1lbGluZS5hZGQodGhpcyx0aGlzLl9zdGFydFRpbWUtdGhpcy5fZGVsYXkpOiF0JiZ0aGlzLnRpbWVsaW5lJiZ0aGlzLl90aW1lbGluZS5fcmVtb3ZlKHRoaXMsITApKSwhMX0sbi5fa2lsbD1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9lbmFibGVkKCExLCExKX0sbi5raWxsPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuX2tpbGwodCxlKSx0aGlzfSxuLl91bmNhY2hlPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10P3RoaXM6dGhpcy50aW1lbGluZTtlOyllLl9kaXJ0eT0hMCxlPWUudGltZWxpbmU7cmV0dXJuIHRoaXN9LG4uX3N3YXBTZWxmSW5QYXJhbXM9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQubGVuZ3RoLGk9dC5jb25jYXQoKTstLWU+LTE7KVwie3NlbGZ9XCI9PT10W2VdJiYoaVtlXT10aGlzKTtyZXR1cm4gaX0sbi5ldmVudENhbGxiYWNrPWZ1bmN0aW9uKHQsZSxpLHMpe2lmKFwib25cIj09PSh0fHxcIlwiKS5zdWJzdHIoMCwyKSl7dmFyIG49dGhpcy52YXJzO2lmKDE9PT1hcmd1bWVudHMubGVuZ3RoKXJldHVybiBuW3RdO251bGw9PWU/ZGVsZXRlIG5bdF06KG5bdF09ZSxuW3QrXCJQYXJhbXNcIl09bShpKSYmLTEhPT1pLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKT90aGlzLl9zd2FwU2VsZkluUGFyYW1zKGkpOmksblt0K1wiU2NvcGVcIl09cyksXCJvblVwZGF0ZVwiPT09dCYmKHRoaXMuX29uVXBkYXRlPWUpfXJldHVybiB0aGlzfSxuLmRlbGF5PWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmdGhpcy5zdGFydFRpbWUodGhpcy5fc3RhcnRUaW1lK3QtdGhpcy5fZGVsYXkpLHRoaXMuX2RlbGF5PXQsdGhpcyk6dGhpcy5fZGVsYXl9LG4uZHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249dCx0aGlzLl91bmNhY2hlKCEwKSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmdGhpcy5fdGltZT4wJiZ0aGlzLl90aW1lPHRoaXMuX2R1cmF0aW9uJiYwIT09dCYmdGhpcy50b3RhbFRpbWUodGhpcy5fdG90YWxUaW1lKih0L3RoaXMuX2R1cmF0aW9uKSwhMCksdGhpcyk6KHRoaXMuX2RpcnR5PSExLHRoaXMuX2R1cmF0aW9uKX0sbi50b3RhbER1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiB0aGlzLl9kaXJ0eT0hMSxhcmd1bWVudHMubGVuZ3RoP3RoaXMuZHVyYXRpb24odCk6dGhpcy5fdG90YWxEdXJhdGlvbn0sbi50aW1lPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKSx0aGlzLnRvdGFsVGltZSh0PnRoaXMuX2R1cmF0aW9uP3RoaXMuX2R1cmF0aW9uOnQsZSkpOnRoaXMuX3RpbWV9LG4udG90YWxUaW1lPWZ1bmN0aW9uKHQsZSxpKXtpZihhfHxyLndha2UoKSwhYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fdG90YWxUaW1lO2lmKHRoaXMuX3RpbWVsaW5lKXtpZigwPnQmJiFpJiYodCs9dGhpcy50b3RhbER1cmF0aW9uKCkpLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nKXt0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCk7dmFyIHM9dGhpcy5fdG90YWxEdXJhdGlvbixuPXRoaXMuX3RpbWVsaW5lO2lmKHQ+cyYmIWkmJih0PXMpLHRoaXMuX3N0YXJ0VGltZT0odGhpcy5fcGF1c2VkP3RoaXMuX3BhdXNlVGltZTpuLl90aW1lKS0odGhpcy5fcmV2ZXJzZWQ/cy10OnQpL3RoaXMuX3RpbWVTY2FsZSxuLl9kaXJ0eXx8dGhpcy5fdW5jYWNoZSghMSksbi5fdGltZWxpbmUpZm9yKDtuLl90aW1lbGluZTspbi5fdGltZWxpbmUuX3RpbWUhPT0obi5fc3RhcnRUaW1lK24uX3RvdGFsVGltZSkvbi5fdGltZVNjYWxlJiZuLnRvdGFsVGltZShuLl90b3RhbFRpbWUsITApLG49bi5fdGltZWxpbmV9dGhpcy5fZ2MmJnRoaXMuX2VuYWJsZWQoITAsITEpLCh0aGlzLl90b3RhbFRpbWUhPT10fHwwPT09dGhpcy5fZHVyYXRpb24pJiYodGhpcy5yZW5kZXIodCxlLCExKSx6Lmxlbmd0aCYmcSgpKX1yZXR1cm4gdGhpc30sbi5wcm9ncmVzcz1uLnRvdGFsUHJvZ3Jlc3M9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD90aGlzLnRvdGFsVGltZSh0aGlzLmR1cmF0aW9uKCkqdCxlKTp0aGlzLl90aW1lL3RoaXMuZHVyYXRpb24oKX0sbi5zdGFydFRpbWU9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHQhPT10aGlzLl9zdGFydFRpbWUmJih0aGlzLl9zdGFydFRpbWU9dCx0aGlzLnRpbWVsaW5lJiZ0aGlzLnRpbWVsaW5lLl9zb3J0Q2hpbGRyZW4mJnRoaXMudGltZWxpbmUuYWRkKHRoaXMsdC10aGlzLl9kZWxheSkpLHRoaXMpOnRoaXMuX3N0YXJ0VGltZX0sbi50aW1lU2NhbGU9ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3RpbWVTY2FsZTtpZih0PXR8fGgsdGhpcy5fdGltZWxpbmUmJnRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nKXt2YXIgZT10aGlzLl9wYXVzZVRpbWUsaT1lfHwwPT09ZT9lOnRoaXMuX3RpbWVsaW5lLnRvdGFsVGltZSgpO3RoaXMuX3N0YXJ0VGltZT1pLShpLXRoaXMuX3N0YXJ0VGltZSkqdGhpcy5fdGltZVNjYWxlL3R9cmV0dXJuIHRoaXMuX3RpbWVTY2FsZT10LHRoaXMuX3VuY2FjaGUoITEpfSxuLnJldmVyc2VkPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0IT10aGlzLl9yZXZlcnNlZCYmKHRoaXMuX3JldmVyc2VkPXQsdGhpcy50b3RhbFRpbWUodGhpcy5fdGltZWxpbmUmJiF0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZz90aGlzLnRvdGFsRHVyYXRpb24oKS10aGlzLl90b3RhbFRpbWU6dGhpcy5fdG90YWxUaW1lLCEwKSksdGhpcyk6dGhpcy5fcmV2ZXJzZWR9LG4ucGF1c2VkPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl9wYXVzZWQ7aWYodCE9dGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lbGluZSl7YXx8dHx8ci53YWtlKCk7dmFyIGU9dGhpcy5fdGltZWxpbmUsaT1lLnJhd1RpbWUoKSxzPWktdGhpcy5fcGF1c2VUaW1lOyF0JiZlLnNtb290aENoaWxkVGltaW5nJiYodGhpcy5fc3RhcnRUaW1lKz1zLHRoaXMuX3VuY2FjaGUoITEpKSx0aGlzLl9wYXVzZVRpbWU9dD9pOm51bGwsdGhpcy5fcGF1c2VkPXQsdGhpcy5fYWN0aXZlPXRoaXMuaXNBY3RpdmUoKSwhdCYmMCE9PXMmJnRoaXMuX2luaXR0ZWQmJnRoaXMuZHVyYXRpb24oKSYmdGhpcy5yZW5kZXIoZS5zbW9vdGhDaGlsZFRpbWluZz90aGlzLl90b3RhbFRpbWU6KGktdGhpcy5fc3RhcnRUaW1lKS90aGlzLl90aW1lU2NhbGUsITAsITApfXJldHVybiB0aGlzLl9nYyYmIXQmJnRoaXMuX2VuYWJsZWQoITAsITEpLHRoaXN9O3ZhciBSPWQoXCJjb3JlLlNpbXBsZVRpbWVsaW5lXCIsZnVuY3Rpb24odCl7eC5jYWxsKHRoaXMsMCx0KSx0aGlzLmF1dG9SZW1vdmVDaGlsZHJlbj10aGlzLnNtb290aENoaWxkVGltaW5nPSEwfSk7bj1SLnByb3RvdHlwZT1uZXcgeCxuLmNvbnN0cnVjdG9yPVIsbi5raWxsKCkuX2djPSExLG4uX2ZpcnN0PW4uX2xhc3Q9bnVsbCxuLl9zb3J0Q2hpbGRyZW49ITEsbi5hZGQ9bi5pbnNlcnQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxzO2lmKHQuX3N0YXJ0VGltZT1OdW1iZXIoZXx8MCkrdC5fZGVsYXksdC5fcGF1c2VkJiZ0aGlzIT09dC5fdGltZWxpbmUmJih0Ll9wYXVzZVRpbWU9dC5fc3RhcnRUaW1lKyh0aGlzLnJhd1RpbWUoKS10Ll9zdGFydFRpbWUpL3QuX3RpbWVTY2FsZSksdC50aW1lbGluZSYmdC50aW1lbGluZS5fcmVtb3ZlKHQsITApLHQudGltZWxpbmU9dC5fdGltZWxpbmU9dGhpcyx0Ll9nYyYmdC5fZW5hYmxlZCghMCwhMCksaT10aGlzLl9sYXN0LHRoaXMuX3NvcnRDaGlsZHJlbilmb3Iocz10Ll9zdGFydFRpbWU7aSYmaS5fc3RhcnRUaW1lPnM7KWk9aS5fcHJldjtyZXR1cm4gaT8odC5fbmV4dD1pLl9uZXh0LGkuX25leHQ9dCk6KHQuX25leHQ9dGhpcy5fZmlyc3QsdGhpcy5fZmlyc3Q9dCksdC5fbmV4dD90Ll9uZXh0Ll9wcmV2PXQ6dGhpcy5fbGFzdD10LHQuX3ByZXY9aSx0aGlzLl90aW1lbGluZSYmdGhpcy5fdW5jYWNoZSghMCksdGhpc30sbi5fcmVtb3ZlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHQudGltZWxpbmU9PT10aGlzJiYoZXx8dC5fZW5hYmxlZCghMSwhMCksdC50aW1lbGluZT1udWxsLHQuX3ByZXY/dC5fcHJldi5fbmV4dD10Ll9uZXh0OnRoaXMuX2ZpcnN0PT09dCYmKHRoaXMuX2ZpcnN0PXQuX25leHQpLHQuX25leHQ/dC5fbmV4dC5fcHJldj10Ll9wcmV2OnRoaXMuX2xhc3Q9PT10JiYodGhpcy5fbGFzdD10Ll9wcmV2KSx0aGlzLl90aW1lbGluZSYmdGhpcy5fdW5jYWNoZSghMCkpLHRoaXN9LG4ucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuPXRoaXMuX2ZpcnN0O2Zvcih0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10aGlzLl9yYXdQcmV2VGltZT10O247KXM9bi5fbmV4dCwobi5fYWN0aXZlfHx0Pj1uLl9zdGFydFRpbWUmJiFuLl9wYXVzZWQpJiYobi5fcmV2ZXJzZWQ/bi5yZW5kZXIoKG4uX2RpcnR5P24udG90YWxEdXJhdGlvbigpOm4uX3RvdGFsRHVyYXRpb24pLSh0LW4uX3N0YXJ0VGltZSkqbi5fdGltZVNjYWxlLGUsaSk6bi5yZW5kZXIoKHQtbi5fc3RhcnRUaW1lKSpuLl90aW1lU2NhbGUsZSxpKSksbj1zfSxuLnJhd1RpbWU9ZnVuY3Rpb24oKXtyZXR1cm4gYXx8ci53YWtlKCksdGhpcy5fdG90YWxUaW1lfTt2YXIgRD1kKFwiVHdlZW5MaXRlXCIsZnVuY3Rpb24oZSxpLHMpe2lmKHguY2FsbCh0aGlzLGkscyksdGhpcy5yZW5kZXI9RC5wcm90b3R5cGUucmVuZGVyLG51bGw9PWUpdGhyb3dcIkNhbm5vdCB0d2VlbiBhIG51bGwgdGFyZ2V0LlwiO3RoaXMudGFyZ2V0PWU9XCJzdHJpbmdcIiE9dHlwZW9mIGU/ZTpELnNlbGVjdG9yKGUpfHxlO3ZhciBuLHIsYSxvPWUuanF1ZXJ5fHxlLmxlbmd0aCYmZSE9PXQmJmVbMF0mJihlWzBdPT09dHx8ZVswXS5ub2RlVHlwZSYmZVswXS5zdHlsZSYmIWUubm9kZVR5cGUpLGw9dGhpcy52YXJzLm92ZXJ3cml0ZTtpZih0aGlzLl9vdmVyd3JpdGU9bD1udWxsPT1sP0dbRC5kZWZhdWx0T3ZlcndyaXRlXTpcIm51bWJlclwiPT10eXBlb2YgbD9sPj4wOkdbbF0sKG98fGUgaW5zdGFuY2VvZiBBcnJheXx8ZS5wdXNoJiZtKGUpKSYmXCJudW1iZXJcIiE9dHlwZW9mIGVbMF0pZm9yKHRoaXMuX3RhcmdldHM9YT1fLmNhbGwoZSwwKSx0aGlzLl9wcm9wTG9va3VwPVtdLHRoaXMuX3NpYmxpbmdzPVtdLG49MDthLmxlbmd0aD5uO24rKylyPWFbbl0scj9cInN0cmluZ1wiIT10eXBlb2Ygcj9yLmxlbmd0aCYmciE9PXQmJnJbMF0mJihyWzBdPT09dHx8clswXS5ub2RlVHlwZSYmclswXS5zdHlsZSYmIXIubm9kZVR5cGUpPyhhLnNwbGljZShuLS0sMSksdGhpcy5fdGFyZ2V0cz1hPWEuY29uY2F0KF8uY2FsbChyLDApKSk6KHRoaXMuX3NpYmxpbmdzW25dPU0ocix0aGlzLCExKSwxPT09bCYmdGhpcy5fc2libGluZ3Nbbl0ubGVuZ3RoPjEmJiQocix0aGlzLG51bGwsMSx0aGlzLl9zaWJsaW5nc1tuXSkpOihyPWFbbi0tXT1ELnNlbGVjdG9yKHIpLFwic3RyaW5nXCI9PXR5cGVvZiByJiZhLnNwbGljZShuKzEsMSkpOmEuc3BsaWNlKG4tLSwxKTtlbHNlIHRoaXMuX3Byb3BMb29rdXA9e30sdGhpcy5fc2libGluZ3M9TShlLHRoaXMsITEpLDE9PT1sJiZ0aGlzLl9zaWJsaW5ncy5sZW5ndGg+MSYmJChlLHRoaXMsbnVsbCwxLHRoaXMuX3NpYmxpbmdzKTsodGhpcy52YXJzLmltbWVkaWF0ZVJlbmRlcnx8MD09PWkmJjA9PT10aGlzLl9kZWxheSYmdGhpcy52YXJzLmltbWVkaWF0ZVJlbmRlciE9PSExKSYmKHRoaXMuX3RpbWU9LWgsdGhpcy5yZW5kZXIoLXRoaXMuX2RlbGF5KSl9LCEwKSxJPWZ1bmN0aW9uKGUpe3JldHVybiBlLmxlbmd0aCYmZSE9PXQmJmVbMF0mJihlWzBdPT09dHx8ZVswXS5ub2RlVHlwZSYmZVswXS5zdHlsZSYmIWUubm9kZVR5cGUpfSxFPWZ1bmN0aW9uKHQsZSl7dmFyIGkscz17fTtmb3IoaSBpbiB0KWpbaV18fGkgaW4gZSYmXCJ0cmFuc2Zvcm1cIiE9PWkmJlwieFwiIT09aSYmXCJ5XCIhPT1pJiZcIndpZHRoXCIhPT1pJiZcImhlaWdodFwiIT09aSYmXCJjbGFzc05hbWVcIiE9PWkmJlwiYm9yZGVyXCIhPT1pfHwhKCFMW2ldfHxMW2ldJiZMW2ldLl9hdXRvQ1NTKXx8KHNbaV09dFtpXSxkZWxldGUgdFtpXSk7dC5jc3M9c307bj1ELnByb3RvdHlwZT1uZXcgeCxuLmNvbnN0cnVjdG9yPUQsbi5raWxsKCkuX2djPSExLG4ucmF0aW89MCxuLl9maXJzdFBUPW4uX3RhcmdldHM9bi5fb3ZlcndyaXR0ZW5Qcm9wcz1uLl9zdGFydEF0PW51bGwsbi5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD1uLl9sYXp5PSExLEQudmVyc2lvbj1cIjEuMTIuMVwiLEQuZGVmYXVsdEVhc2U9bi5fZWFzZT1uZXcgVChudWxsLG51bGwsMSwxKSxELmRlZmF1bHRPdmVyd3JpdGU9XCJhdXRvXCIsRC50aWNrZXI9cixELmF1dG9TbGVlcD0hMCxELmxhZ1Ntb290aGluZz1mdW5jdGlvbih0LGUpe3IubGFnU21vb3RoaW5nKHQsZSl9LEQuc2VsZWN0b3I9dC4kfHx0LmpRdWVyeXx8ZnVuY3Rpb24oZSl7cmV0dXJuIHQuJD8oRC5zZWxlY3Rvcj10LiQsdC4kKGUpKTp0LmRvY3VtZW50P3QuZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCIjXCI9PT1lLmNoYXJBdCgwKT9lLnN1YnN0cigxKTplKTplfTt2YXIgej1bXSxPPXt9LE49RC5faW50ZXJuYWxzPXtpc0FycmF5Om0saXNTZWxlY3RvcjpJLGxhenlUd2VlbnM6en0sTD1ELl9wbHVnaW5zPXt9LFU9Ti50d2Vlbkxvb2t1cD17fSxGPTAsaj1OLnJlc2VydmVkUHJvcHM9e2Vhc2U6MSxkZWxheToxLG92ZXJ3cml0ZToxLG9uQ29tcGxldGU6MSxvbkNvbXBsZXRlUGFyYW1zOjEsb25Db21wbGV0ZVNjb3BlOjEsdXNlRnJhbWVzOjEscnVuQmFja3dhcmRzOjEsc3RhcnRBdDoxLG9uVXBkYXRlOjEsb25VcGRhdGVQYXJhbXM6MSxvblVwZGF0ZVNjb3BlOjEsb25TdGFydDoxLG9uU3RhcnRQYXJhbXM6MSxvblN0YXJ0U2NvcGU6MSxvblJldmVyc2VDb21wbGV0ZToxLG9uUmV2ZXJzZUNvbXBsZXRlUGFyYW1zOjEsb25SZXZlcnNlQ29tcGxldGVTY29wZToxLG9uUmVwZWF0OjEsb25SZXBlYXRQYXJhbXM6MSxvblJlcGVhdFNjb3BlOjEsZWFzZVBhcmFtczoxLHlveW86MSxpbW1lZGlhdGVSZW5kZXI6MSxyZXBlYXQ6MSxyZXBlYXREZWxheToxLGRhdGE6MSxwYXVzZWQ6MSxyZXZlcnNlZDoxLGF1dG9DU1M6MSxsYXp5OjF9LEc9e25vbmU6MCxhbGw6MSxhdXRvOjIsY29uY3VycmVudDozLGFsbE9uU3RhcnQ6NCxwcmVleGlzdGluZzo1LFwidHJ1ZVwiOjEsXCJmYWxzZVwiOjB9LFE9eC5fcm9vdEZyYW1lc1RpbWVsaW5lPW5ldyBSLEI9eC5fcm9vdFRpbWVsaW5lPW5ldyBSLHE9ZnVuY3Rpb24oKXt2YXIgdD16Lmxlbmd0aDtmb3IoTz17fTstLXQ+LTE7KWk9elt0XSxpJiZpLl9sYXp5IT09ITEmJihpLnJlbmRlcihpLl9sYXp5LCExLCEwKSxpLl9sYXp5PSExKTt6Lmxlbmd0aD0wfTtCLl9zdGFydFRpbWU9ci50aW1lLFEuX3N0YXJ0VGltZT1yLmZyYW1lLEIuX2FjdGl2ZT1RLl9hY3RpdmU9ITAsc2V0VGltZW91dChxLDEpLHguX3VwZGF0ZVJvb3Q9RC5yZW5kZXI9ZnVuY3Rpb24oKXt2YXIgdCxlLGk7aWYoei5sZW5ndGgmJnEoKSxCLnJlbmRlcigoci50aW1lLUIuX3N0YXJ0VGltZSkqQi5fdGltZVNjYWxlLCExLCExKSxRLnJlbmRlcigoci5mcmFtZS1RLl9zdGFydFRpbWUpKlEuX3RpbWVTY2FsZSwhMSwhMSksei5sZW5ndGgmJnEoKSwhKHIuZnJhbWUlMTIwKSl7Zm9yKGkgaW4gVSl7Zm9yKGU9VVtpXS50d2VlbnMsdD1lLmxlbmd0aDstLXQ+LTE7KWVbdF0uX2djJiZlLnNwbGljZSh0LDEpOzA9PT1lLmxlbmd0aCYmZGVsZXRlIFVbaV19aWYoaT1CLl9maXJzdCwoIWl8fGkuX3BhdXNlZCkmJkQuYXV0b1NsZWVwJiYhUS5fZmlyc3QmJjE9PT1yLl9saXN0ZW5lcnMudGljay5sZW5ndGgpe2Zvcig7aSYmaS5fcGF1c2VkOylpPWkuX25leHQ7aXx8ci5zbGVlcCgpfX19LHIuYWRkRXZlbnRMaXN0ZW5lcihcInRpY2tcIix4Ll91cGRhdGVSb290KTt2YXIgTT1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbixyPXQuX2dzVHdlZW5JRDtpZihVW3J8fCh0Ll9nc1R3ZWVuSUQ9cj1cInRcIitGKyspXXx8KFVbcl09e3RhcmdldDp0LHR3ZWVuczpbXX0pLGUmJihzPVVbcl0udHdlZW5zLHNbbj1zLmxlbmd0aF09ZSxpKSlmb3IoOy0tbj4tMTspc1tuXT09PWUmJnMuc3BsaWNlKG4sMSk7cmV0dXJuIFVbcl0udHdlZW5zfSwkPWZ1bmN0aW9uKHQsZSxpLHMsbil7dmFyIHIsYSxvLGw7aWYoMT09PXN8fHM+PTQpe2ZvcihsPW4ubGVuZ3RoLHI9MDtsPnI7cisrKWlmKChvPW5bcl0pIT09ZSlvLl9nY3x8by5fZW5hYmxlZCghMSwhMSkmJihhPSEwKTtlbHNlIGlmKDU9PT1zKWJyZWFrO3JldHVybiBhfXZhciBfLHU9ZS5fc3RhcnRUaW1lK2gsbT1bXSxmPTAscD0wPT09ZS5fZHVyYXRpb247Zm9yKHI9bi5sZW5ndGg7LS1yPi0xOykobz1uW3JdKT09PWV8fG8uX2djfHxvLl9wYXVzZWR8fChvLl90aW1lbGluZSE9PWUuX3RpbWVsaW5lPyhfPV98fEsoZSwwLHApLDA9PT1LKG8sXyxwKSYmKG1bZisrXT1vKSk6dT49by5fc3RhcnRUaW1lJiZvLl9zdGFydFRpbWUrby50b3RhbER1cmF0aW9uKCkvby5fdGltZVNjYWxlPnUmJigocHx8IW8uX2luaXR0ZWQpJiYyZS0xMD49dS1vLl9zdGFydFRpbWV8fChtW2YrK109bykpKTtmb3Iocj1mOy0tcj4tMTspbz1tW3JdLDI9PT1zJiZvLl9raWxsKGksdCkmJihhPSEwKSwoMiE9PXN8fCFvLl9maXJzdFBUJiZvLl9pbml0dGVkKSYmby5fZW5hYmxlZCghMSwhMSkmJihhPSEwKTtyZXR1cm4gYX0sSz1mdW5jdGlvbih0LGUsaSl7Zm9yKHZhciBzPXQuX3RpbWVsaW5lLG49cy5fdGltZVNjYWxlLHI9dC5fc3RhcnRUaW1lO3MuX3RpbWVsaW5lOyl7aWYocis9cy5fc3RhcnRUaW1lLG4qPXMuX3RpbWVTY2FsZSxzLl9wYXVzZWQpcmV0dXJuLTEwMDtzPXMuX3RpbWVsaW5lfXJldHVybiByLz1uLHI+ZT9yLWU6aSYmcj09PWV8fCF0Ll9pbml0dGVkJiYyKmg+ci1lP2g6KHIrPXQudG90YWxEdXJhdGlvbigpL3QuX3RpbWVTY2FsZS9uKT5lK2g/MDpyLWUtaH07bi5faW5pdD1mdW5jdGlvbigpe3ZhciB0LGUsaSxzLG4scj10aGlzLnZhcnMsYT10aGlzLl9vdmVyd3JpdHRlblByb3BzLG89dGhpcy5fZHVyYXRpb24sbD0hIXIuaW1tZWRpYXRlUmVuZGVyLGg9ci5lYXNlO2lmKHIuc3RhcnRBdCl7dGhpcy5fc3RhcnRBdCYmKHRoaXMuX3N0YXJ0QXQucmVuZGVyKC0xLCEwKSx0aGlzLl9zdGFydEF0LmtpbGwoKSksbj17fTtmb3IocyBpbiByLnN0YXJ0QXQpbltzXT1yLnN0YXJ0QXRbc107aWYobi5vdmVyd3JpdGU9ITEsbi5pbW1lZGlhdGVSZW5kZXI9ITAsbi5sYXp5PWwmJnIubGF6eSE9PSExLG4uc3RhcnRBdD1uLmRlbGF5PW51bGwsdGhpcy5fc3RhcnRBdD1ELnRvKHRoaXMudGFyZ2V0LDAsbiksbClpZih0aGlzLl90aW1lPjApdGhpcy5fc3RhcnRBdD1udWxsO2Vsc2UgaWYoMCE9PW8pcmV0dXJufWVsc2UgaWYoci5ydW5CYWNrd2FyZHMmJjAhPT1vKWlmKHRoaXMuX3N0YXJ0QXQpdGhpcy5fc3RhcnRBdC5yZW5kZXIoLTEsITApLHRoaXMuX3N0YXJ0QXQua2lsbCgpLHRoaXMuX3N0YXJ0QXQ9bnVsbDtlbHNle2k9e307Zm9yKHMgaW4gcilqW3NdJiZcImF1dG9DU1NcIiE9PXN8fChpW3NdPXJbc10pO2lmKGkub3ZlcndyaXRlPTAsaS5kYXRhPVwiaXNGcm9tU3RhcnRcIixpLmxhenk9bCYmci5sYXp5IT09ITEsaS5pbW1lZGlhdGVSZW5kZXI9bCx0aGlzLl9zdGFydEF0PUQudG8odGhpcy50YXJnZXQsMCxpKSxsKXtpZigwPT09dGhpcy5fdGltZSlyZXR1cm59ZWxzZSB0aGlzLl9zdGFydEF0Ll9pbml0KCksdGhpcy5fc3RhcnRBdC5fZW5hYmxlZCghMSl9aWYodGhpcy5fZWFzZT1oP2ggaW5zdGFuY2VvZiBUP3IuZWFzZVBhcmFtcyBpbnN0YW5jZW9mIEFycmF5P2guY29uZmlnLmFwcGx5KGgsci5lYXNlUGFyYW1zKTpoOlwiZnVuY3Rpb25cIj09dHlwZW9mIGg/bmV3IFQoaCxyLmVhc2VQYXJhbXMpOnlbaF18fEQuZGVmYXVsdEVhc2U6RC5kZWZhdWx0RWFzZSx0aGlzLl9lYXNlVHlwZT10aGlzLl9lYXNlLl90eXBlLHRoaXMuX2Vhc2VQb3dlcj10aGlzLl9lYXNlLl9wb3dlcix0aGlzLl9maXJzdFBUPW51bGwsdGhpcy5fdGFyZ2V0cylmb3IodD10aGlzLl90YXJnZXRzLmxlbmd0aDstLXQ+LTE7KXRoaXMuX2luaXRQcm9wcyh0aGlzLl90YXJnZXRzW3RdLHRoaXMuX3Byb3BMb29rdXBbdF09e30sdGhpcy5fc2libGluZ3NbdF0sYT9hW3RdOm51bGwpJiYoZT0hMCk7ZWxzZSBlPXRoaXMuX2luaXRQcm9wcyh0aGlzLnRhcmdldCx0aGlzLl9wcm9wTG9va3VwLHRoaXMuX3NpYmxpbmdzLGEpO2lmKGUmJkQuX29uUGx1Z2luRXZlbnQoXCJfb25Jbml0QWxsUHJvcHNcIix0aGlzKSxhJiYodGhpcy5fZmlyc3RQVHx8XCJmdW5jdGlvblwiIT10eXBlb2YgdGhpcy50YXJnZXQmJnRoaXMuX2VuYWJsZWQoITEsITEpKSxyLnJ1bkJhY2t3YXJkcylmb3IoaT10aGlzLl9maXJzdFBUO2k7KWkucys9aS5jLGkuYz0taS5jLGk9aS5fbmV4dDt0aGlzLl9vblVwZGF0ZT1yLm9uVXBkYXRlLHRoaXMuX2luaXR0ZWQ9ITB9LG4uX2luaXRQcm9wcz1mdW5jdGlvbihlLGkscyxuKXt2YXIgcixhLG8sbCxoLF87aWYobnVsbD09ZSlyZXR1cm4hMTtPW2UuX2dzVHdlZW5JRF0mJnEoKSx0aGlzLnZhcnMuY3NzfHxlLnN0eWxlJiZlIT09dCYmZS5ub2RlVHlwZSYmTC5jc3MmJnRoaXMudmFycy5hdXRvQ1NTIT09ITEmJkUodGhpcy52YXJzLGUpO2ZvcihyIGluIHRoaXMudmFycyl7aWYoXz10aGlzLnZhcnNbcl0saltyXSlfJiYoXyBpbnN0YW5jZW9mIEFycmF5fHxfLnB1c2gmJm0oXykpJiYtMSE9PV8uam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpJiYodGhpcy52YXJzW3JdPV89dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhfLHRoaXMpKTtlbHNlIGlmKExbcl0mJihsPW5ldyBMW3JdKS5fb25Jbml0VHdlZW4oZSx0aGlzLnZhcnNbcl0sdGhpcykpe2Zvcih0aGlzLl9maXJzdFBUPWg9e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDpsLHA6XCJzZXRSYXRpb1wiLHM6MCxjOjEsZjohMCxuOnIscGc6ITAscHI6bC5fcHJpb3JpdHl9LGE9bC5fb3ZlcndyaXRlUHJvcHMubGVuZ3RoOy0tYT4tMTspaVtsLl9vdmVyd3JpdGVQcm9wc1thXV09dGhpcy5fZmlyc3RQVDsobC5fcHJpb3JpdHl8fGwuX29uSW5pdEFsbFByb3BzKSYmKG89ITApLChsLl9vbkRpc2FibGV8fGwuX29uRW5hYmxlKSYmKHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9ITApfWVsc2UgdGhpcy5fZmlyc3RQVD1pW3JdPWg9e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDplLHA6cixmOlwiZnVuY3Rpb25cIj09dHlwZW9mIGVbcl0sbjpyLHBnOiExLHByOjB9LGgucz1oLmY/ZVtyLmluZGV4T2YoXCJzZXRcIil8fFwiZnVuY3Rpb25cIiE9dHlwZW9mIGVbXCJnZXRcIityLnN1YnN0cigzKV0/cjpcImdldFwiK3Iuc3Vic3RyKDMpXSgpOnBhcnNlRmxvYXQoZVtyXSksaC5jPVwic3RyaW5nXCI9PXR5cGVvZiBfJiZcIj1cIj09PV8uY2hhckF0KDEpP3BhcnNlSW50KF8uY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIoXy5zdWJzdHIoMikpOk51bWJlcihfKS1oLnN8fDA7aCYmaC5fbmV4dCYmKGguX25leHQuX3ByZXY9aCl9cmV0dXJuIG4mJnRoaXMuX2tpbGwobixlKT90aGlzLl9pbml0UHJvcHMoZSxpLHMsbik6dGhpcy5fb3ZlcndyaXRlPjEmJnRoaXMuX2ZpcnN0UFQmJnMubGVuZ3RoPjEmJiQoZSx0aGlzLGksdGhpcy5fb3ZlcndyaXRlLHMpPyh0aGlzLl9raWxsKGksZSksdGhpcy5faW5pdFByb3BzKGUsaSxzLG4pKToodGhpcy5fZmlyc3RQVCYmKHRoaXMudmFycy5sYXp5IT09ITEmJnRoaXMuX2R1cmF0aW9ufHx0aGlzLnZhcnMubGF6eSYmIXRoaXMuX2R1cmF0aW9uKSYmKE9bZS5fZ3NUd2VlbklEXT0hMCksbyl9LG4ucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuLHIsYSxvPXRoaXMuX3RpbWUsbD10aGlzLl9kdXJhdGlvbixfPXRoaXMuX3Jhd1ByZXZUaW1lO2lmKHQ+PWwpdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9bCx0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuX2NhbGNFbmQ/dGhpcy5fZWFzZS5nZXRSYXRpbygxKToxLHRoaXMuX3JldmVyc2VkfHwocz0hMCxuPVwib25Db21wbGV0ZVwiKSwwPT09bCYmKHRoaXMuX2luaXR0ZWR8fCF0aGlzLnZhcnMubGF6eXx8aSkmJih0aGlzLl9zdGFydFRpbWU9PT10aGlzLl90aW1lbGluZS5fZHVyYXRpb24mJih0PTApLCgwPT09dHx8MD5ffHxfPT09aCkmJl8hPT10JiYoaT0hMCxfPmgmJihuPVwib25SZXZlcnNlQ29tcGxldGVcIikpLHRoaXMuX3Jhd1ByZXZUaW1lPWE9IWV8fHR8fF89PT10P3Q6aCk7ZWxzZSBpZigxZS03PnQpdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9MCx0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuX2NhbGNFbmQ/dGhpcy5fZWFzZS5nZXRSYXRpbygwKTowLCgwIT09b3x8MD09PWwmJl8+MCYmXyE9PWgpJiYobj1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIscz10aGlzLl9yZXZlcnNlZCksMD50Pyh0aGlzLl9hY3RpdmU9ITEsMD09PWwmJih0aGlzLl9pbml0dGVkfHwhdGhpcy52YXJzLmxhenl8fGkpJiYoXz49MCYmKGk9ITApLHRoaXMuX3Jhd1ByZXZUaW1lPWE9IWV8fHR8fF89PT10P3Q6aCkpOnRoaXMuX2luaXR0ZWR8fChpPSEwKTtlbHNlIGlmKHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXQsdGhpcy5fZWFzZVR5cGUpe3ZhciB1PXQvbCxtPXRoaXMuX2Vhc2VUeXBlLGY9dGhpcy5fZWFzZVBvd2VyOygxPT09bXx8Mz09PW0mJnU+PS41KSYmKHU9MS11KSwzPT09bSYmKHUqPTIpLDE9PT1mP3UqPXU6Mj09PWY/dSo9dSp1OjM9PT1mP3UqPXUqdSp1OjQ9PT1mJiYodSo9dSp1KnUqdSksdGhpcy5yYXRpbz0xPT09bT8xLXU6Mj09PW0/dTouNT50L2w/dS8yOjEtdS8yfWVsc2UgdGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKHQvbCk7aWYodGhpcy5fdGltZSE9PW98fGkpe2lmKCF0aGlzLl9pbml0dGVkKXtpZih0aGlzLl9pbml0KCksIXRoaXMuX2luaXR0ZWR8fHRoaXMuX2djKXJldHVybjtpZighaSYmdGhpcy5fZmlyc3RQVCYmKHRoaXMudmFycy5sYXp5IT09ITEmJnRoaXMuX2R1cmF0aW9ufHx0aGlzLnZhcnMubGF6eSYmIXRoaXMuX2R1cmF0aW9uKSlyZXR1cm4gdGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9byx0aGlzLl9yYXdQcmV2VGltZT1fLHoucHVzaCh0aGlzKSx0aGlzLl9sYXp5PXQsdm9pZCAwO3RoaXMuX3RpbWUmJiFzP3RoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbyh0aGlzLl90aW1lL2wpOnMmJnRoaXMuX2Vhc2UuX2NhbGNFbmQmJih0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8oMD09PXRoaXMuX3RpbWU/MDoxKSl9Zm9yKHRoaXMuX2xhenkhPT0hMSYmKHRoaXMuX2xhenk9ITEpLHRoaXMuX2FjdGl2ZXx8IXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZSE9PW8mJnQ+PTAmJih0aGlzLl9hY3RpdmU9ITApLDA9PT1vJiYodGhpcy5fc3RhcnRBdCYmKHQ+PTA/dGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpOm58fChuPVwiX2R1bW15R1NcIikpLHRoaXMudmFycy5vblN0YXJ0JiYoMCE9PXRoaXMuX3RpbWV8fDA9PT1sKSYmKGV8fHRoaXMudmFycy5vblN0YXJ0LmFwcGx5KHRoaXMudmFycy5vblN0YXJ0U2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uU3RhcnRQYXJhbXN8fGcpKSkscj10aGlzLl9maXJzdFBUO3I7KXIuZj9yLnRbci5wXShyLmMqdGhpcy5yYXRpbytyLnMpOnIudFtyLnBdPXIuYyp0aGlzLnJhdGlvK3IucyxyPXIuX25leHQ7dGhpcy5fb25VcGRhdGUmJigwPnQmJnRoaXMuX3N0YXJ0QXQmJnRoaXMuX3N0YXJ0VGltZSYmdGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpLGV8fCh0aGlzLl90aW1lIT09b3x8cykmJnRoaXMuX29uVXBkYXRlLmFwcGx5KHRoaXMudmFycy5vblVwZGF0ZVNjb3BlfHx0aGlzLHRoaXMudmFycy5vblVwZGF0ZVBhcmFtc3x8ZykpLG4mJih0aGlzLl9nY3x8KDA+dCYmdGhpcy5fc3RhcnRBdCYmIXRoaXMuX29uVXBkYXRlJiZ0aGlzLl9zdGFydFRpbWUmJnRoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKSxzJiYodGhpcy5fdGltZWxpbmUuYXV0b1JlbW92ZUNoaWxkcmVuJiZ0aGlzLl9lbmFibGVkKCExLCExKSx0aGlzLl9hY3RpdmU9ITEpLCFlJiZ0aGlzLnZhcnNbbl0mJnRoaXMudmFyc1tuXS5hcHBseSh0aGlzLnZhcnNbbitcIlNjb3BlXCJdfHx0aGlzLHRoaXMudmFyc1tuK1wiUGFyYW1zXCJdfHxnKSwwPT09bCYmdGhpcy5fcmF3UHJldlRpbWU9PT1oJiZhIT09aCYmKHRoaXMuX3Jhd1ByZXZUaW1lPTApKSl9fSxuLl9raWxsPWZ1bmN0aW9uKHQsZSl7aWYoXCJhbGxcIj09PXQmJih0PW51bGwpLG51bGw9PXQmJihudWxsPT1lfHxlPT09dGhpcy50YXJnZXQpKXJldHVybiB0aGlzLl9sYXp5PSExLHRoaXMuX2VuYWJsZWQoITEsITEpO2U9XCJzdHJpbmdcIiE9dHlwZW9mIGU/ZXx8dGhpcy5fdGFyZ2V0c3x8dGhpcy50YXJnZXQ6RC5zZWxlY3RvcihlKXx8ZTt2YXIgaSxzLG4scixhLG8sbCxoO2lmKChtKGUpfHxJKGUpKSYmXCJudW1iZXJcIiE9dHlwZW9mIGVbMF0pZm9yKGk9ZS5sZW5ndGg7LS1pPi0xOyl0aGlzLl9raWxsKHQsZVtpXSkmJihvPSEwKTtlbHNle2lmKHRoaXMuX3RhcmdldHMpe2ZvcihpPXRoaXMuX3RhcmdldHMubGVuZ3RoOy0taT4tMTspaWYoZT09PXRoaXMuX3RhcmdldHNbaV0pe2E9dGhpcy5fcHJvcExvb2t1cFtpXXx8e30sdGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz10aGlzLl9vdmVyd3JpdHRlblByb3BzfHxbXSxzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHNbaV09dD90aGlzLl9vdmVyd3JpdHRlblByb3BzW2ldfHx7fTpcImFsbFwiO2JyZWFrfX1lbHNle2lmKGUhPT10aGlzLnRhcmdldClyZXR1cm4hMTthPXRoaXMuX3Byb3BMb29rdXAscz10aGlzLl9vdmVyd3JpdHRlblByb3BzPXQ/dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc3x8e306XCJhbGxcIn1pZihhKXtsPXR8fGEsaD10IT09cyYmXCJhbGxcIiE9PXMmJnQhPT1hJiYoXCJvYmplY3RcIiE9dHlwZW9mIHR8fCF0Ll90ZW1wS2lsbCk7Zm9yKG4gaW4gbCkocj1hW25dKSYmKHIucGcmJnIudC5fa2lsbChsKSYmKG89ITApLHIucGcmJjAhPT1yLnQuX292ZXJ3cml0ZVByb3BzLmxlbmd0aHx8KHIuX3ByZXY/ci5fcHJldi5fbmV4dD1yLl9uZXh0OnI9PT10aGlzLl9maXJzdFBUJiYodGhpcy5fZmlyc3RQVD1yLl9uZXh0KSxyLl9uZXh0JiYoci5fbmV4dC5fcHJldj1yLl9wcmV2KSxyLl9uZXh0PXIuX3ByZXY9bnVsbCksZGVsZXRlIGFbbl0pLGgmJihzW25dPTEpOyF0aGlzLl9maXJzdFBUJiZ0aGlzLl9pbml0dGVkJiZ0aGlzLl9lbmFibGVkKCExLCExKX19cmV0dXJuIG99LG4uaW52YWxpZGF0ZT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkJiZELl9vblBsdWdpbkV2ZW50KFwiX29uRGlzYWJsZVwiLHRoaXMpLHRoaXMuX2ZpcnN0UFQ9bnVsbCx0aGlzLl9vdmVyd3JpdHRlblByb3BzPW51bGwsdGhpcy5fb25VcGRhdGU9bnVsbCx0aGlzLl9zdGFydEF0PW51bGwsdGhpcy5faW5pdHRlZD10aGlzLl9hY3RpdmU9dGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD10aGlzLl9sYXp5PSExLHRoaXMuX3Byb3BMb29rdXA9dGhpcy5fdGFyZ2V0cz97fTpbXSx0aGlzfSxuLl9lbmFibGVkPWZ1bmN0aW9uKHQsZSl7aWYoYXx8ci53YWtlKCksdCYmdGhpcy5fZ2Mpe3ZhciBpLHM9dGhpcy5fdGFyZ2V0cztpZihzKWZvcihpPXMubGVuZ3RoOy0taT4tMTspdGhpcy5fc2libGluZ3NbaV09TShzW2ldLHRoaXMsITApO2Vsc2UgdGhpcy5fc2libGluZ3M9TSh0aGlzLnRhcmdldCx0aGlzLCEwKX1yZXR1cm4geC5wcm90b3R5cGUuX2VuYWJsZWQuY2FsbCh0aGlzLHQsZSksdGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZCYmdGhpcy5fZmlyc3RQVD9ELl9vblBsdWdpbkV2ZW50KHQ/XCJfb25FbmFibGVcIjpcIl9vbkRpc2FibGVcIix0aGlzKTohMX0sRC50bz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIG5ldyBEKHQsZSxpKX0sRC5mcm9tPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gaS5ydW5CYWNrd2FyZHM9ITAsaS5pbW1lZGlhdGVSZW5kZXI9MCE9aS5pbW1lZGlhdGVSZW5kZXIsbmV3IEQodCxlLGkpfSxELmZyb21Ubz1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gcy5zdGFydEF0PWkscy5pbW1lZGlhdGVSZW5kZXI9MCE9cy5pbW1lZGlhdGVSZW5kZXImJjAhPWkuaW1tZWRpYXRlUmVuZGVyLG5ldyBEKHQsZSxzKX0sRC5kZWxheWVkQ2FsbD1mdW5jdGlvbih0LGUsaSxzLG4pe3JldHVybiBuZXcgRChlLDAse2RlbGF5OnQsb25Db21wbGV0ZTplLG9uQ29tcGxldGVQYXJhbXM6aSxvbkNvbXBsZXRlU2NvcGU6cyxvblJldmVyc2VDb21wbGV0ZTplLG9uUmV2ZXJzZUNvbXBsZXRlUGFyYW1zOmksb25SZXZlcnNlQ29tcGxldGVTY29wZTpzLGltbWVkaWF0ZVJlbmRlcjohMSx1c2VGcmFtZXM6bixvdmVyd3JpdGU6MH0pfSxELnNldD1mdW5jdGlvbih0LGUpe3JldHVybiBuZXcgRCh0LDAsZSl9LEQuZ2V0VHdlZW5zT2Y9ZnVuY3Rpb24odCxlKXtpZihudWxsPT10KXJldHVybltdO3Q9XCJzdHJpbmdcIiE9dHlwZW9mIHQ/dDpELnNlbGVjdG9yKHQpfHx0O3ZhciBpLHMsbixyO2lmKChtKHQpfHxJKHQpKSYmXCJudW1iZXJcIiE9dHlwZW9mIHRbMF0pe2ZvcihpPXQubGVuZ3RoLHM9W107LS1pPi0xOylzPXMuY29uY2F0KEQuZ2V0VHdlZW5zT2YodFtpXSxlKSk7Zm9yKGk9cy5sZW5ndGg7LS1pPi0xOylmb3Iocj1zW2ldLG49aTstLW4+LTE7KXI9PT1zW25dJiZzLnNwbGljZShpLDEpfWVsc2UgZm9yKHM9TSh0KS5jb25jYXQoKSxpPXMubGVuZ3RoOy0taT4tMTspKHNbaV0uX2djfHxlJiYhc1tpXS5pc0FjdGl2ZSgpKSYmcy5zcGxpY2UoaSwxKTtyZXR1cm4gc30sRC5raWxsVHdlZW5zT2Y9RC5raWxsRGVsYXllZENhbGxzVG89ZnVuY3Rpb24odCxlLGkpe1wib2JqZWN0XCI9PXR5cGVvZiBlJiYoaT1lLGU9ITEpO2Zvcih2YXIgcz1ELmdldFR3ZWVuc09mKHQsZSksbj1zLmxlbmd0aDstLW4+LTE7KXNbbl0uX2tpbGwoaSx0KX07dmFyIEg9ZChcInBsdWdpbnMuVHdlZW5QbHVnaW5cIixmdW5jdGlvbih0LGUpe3RoaXMuX292ZXJ3cml0ZVByb3BzPSh0fHxcIlwiKS5zcGxpdChcIixcIiksdGhpcy5fcHJvcE5hbWU9dGhpcy5fb3ZlcndyaXRlUHJvcHNbMF0sdGhpcy5fcHJpb3JpdHk9ZXx8MCx0aGlzLl9zdXBlcj1ILnByb3RvdHlwZX0sITApO2lmKG49SC5wcm90b3R5cGUsSC52ZXJzaW9uPVwiMS4xMC4xXCIsSC5BUEk9MixuLl9maXJzdFBUPW51bGwsbi5fYWRkVHdlZW49ZnVuY3Rpb24odCxlLGkscyxuLHIpe3ZhciBhLG87cmV0dXJuIG51bGwhPXMmJihhPVwibnVtYmVyXCI9PXR5cGVvZiBzfHxcIj1cIiE9PXMuY2hhckF0KDEpP051bWJlcihzKS1pOnBhcnNlSW50KHMuY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIocy5zdWJzdHIoMikpKT8odGhpcy5fZmlyc3RQVD1vPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6dCxwOmUsczppLGM6YSxmOlwiZnVuY3Rpb25cIj09dHlwZW9mIHRbZV0sbjpufHxlLHI6cn0sby5fbmV4dCYmKG8uX25leHQuX3ByZXY9byksbyk6dm9pZCAwfSxuLnNldFJhdGlvPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZSxpPXRoaXMuX2ZpcnN0UFQscz0xZS02O2k7KWU9aS5jKnQraS5zLGkucj9lPU1hdGgucm91bmQoZSk6cz5lJiZlPi1zJiYoZT0wKSxpLmY/aS50W2kucF0oZSk6aS50W2kucF09ZSxpPWkuX25leHR9LG4uX2tpbGw9ZnVuY3Rpb24odCl7dmFyIGUsaT10aGlzLl9vdmVyd3JpdGVQcm9wcyxzPXRoaXMuX2ZpcnN0UFQ7aWYobnVsbCE9dFt0aGlzLl9wcm9wTmFtZV0pdGhpcy5fb3ZlcndyaXRlUHJvcHM9W107ZWxzZSBmb3IoZT1pLmxlbmd0aDstLWU+LTE7KW51bGwhPXRbaVtlXV0mJmkuc3BsaWNlKGUsMSk7Zm9yKDtzOyludWxsIT10W3Mubl0mJihzLl9uZXh0JiYocy5fbmV4dC5fcHJldj1zLl9wcmV2KSxzLl9wcmV2PyhzLl9wcmV2Ll9uZXh0PXMuX25leHQscy5fcHJldj1udWxsKTp0aGlzLl9maXJzdFBUPT09cyYmKHRoaXMuX2ZpcnN0UFQ9cy5fbmV4dCkpLHM9cy5fbmV4dDtyZXR1cm4hMX0sbi5fcm91bmRQcm9wcz1mdW5jdGlvbih0LGUpe2Zvcih2YXIgaT10aGlzLl9maXJzdFBUO2k7KSh0W3RoaXMuX3Byb3BOYW1lXXx8bnVsbCE9aS5uJiZ0W2kubi5zcGxpdCh0aGlzLl9wcm9wTmFtZStcIl9cIikuam9pbihcIlwiKV0pJiYoaS5yPWUpLGk9aS5fbmV4dH0sRC5fb25QbHVnaW5FdmVudD1mdW5jdGlvbih0LGUpe3ZhciBpLHMsbixyLGEsbz1lLl9maXJzdFBUO2lmKFwiX29uSW5pdEFsbFByb3BzXCI9PT10KXtmb3IoO287KXtmb3IoYT1vLl9uZXh0LHM9bjtzJiZzLnByPm8ucHI7KXM9cy5fbmV4dDsoby5fcHJldj1zP3MuX3ByZXY6cik/by5fcHJldi5fbmV4dD1vOm49bywoby5fbmV4dD1zKT9zLl9wcmV2PW86cj1vLG89YX1vPWUuX2ZpcnN0UFQ9bn1mb3IoO287KW8ucGcmJlwiZnVuY3Rpb25cIj09dHlwZW9mIG8udFt0XSYmby50W3RdKCkmJihpPSEwKSxvPW8uX25leHQ7cmV0dXJuIGl9LEguYWN0aXZhdGU9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQubGVuZ3RoOy0tZT4tMTspdFtlXS5BUEk9PT1ILkFQSSYmKExbKG5ldyB0W2VdKS5fcHJvcE5hbWVdPXRbZV0pO3JldHVybiEwfSxjLnBsdWdpbj1mdW5jdGlvbih0KXtpZighKHQmJnQucHJvcE5hbWUmJnQuaW5pdCYmdC5BUEkpKXRocm93XCJpbGxlZ2FsIHBsdWdpbiBkZWZpbml0aW9uLlwiO3ZhciBlLGk9dC5wcm9wTmFtZSxzPXQucHJpb3JpdHl8fDAsbj10Lm92ZXJ3cml0ZVByb3BzLHI9e2luaXQ6XCJfb25Jbml0VHdlZW5cIixzZXQ6XCJzZXRSYXRpb1wiLGtpbGw6XCJfa2lsbFwiLHJvdW5kOlwiX3JvdW5kUHJvcHNcIixpbml0QWxsOlwiX29uSW5pdEFsbFByb3BzXCJ9LGE9ZChcInBsdWdpbnMuXCIraS5jaGFyQXQoMCkudG9VcHBlckNhc2UoKStpLnN1YnN0cigxKStcIlBsdWdpblwiLGZ1bmN0aW9uKCl7SC5jYWxsKHRoaXMsaSxzKSx0aGlzLl9vdmVyd3JpdGVQcm9wcz1ufHxbXX0sdC5nbG9iYWw9PT0hMCksbz1hLnByb3RvdHlwZT1uZXcgSChpKTtvLmNvbnN0cnVjdG9yPWEsYS5BUEk9dC5BUEk7Zm9yKGUgaW4gcilcImZ1bmN0aW9uXCI9PXR5cGVvZiB0W2VdJiYob1tyW2VdXT10W2VdKTtyZXR1cm4gYS52ZXJzaW9uPXQudmVyc2lvbixILmFjdGl2YXRlKFthXSksYX0saT10Ll9nc1F1ZXVlKXtmb3Iocz0wO2kubGVuZ3RoPnM7cysrKWlbc10oKTtmb3IobiBpbiBmKWZbbl0uZnVuY3x8dC5jb25zb2xlLmxvZyhcIkdTQVAgZW5jb3VudGVyZWQgbWlzc2luZyBkZXBlbmRlbmN5OiBjb20uZ3JlZW5zb2NrLlwiK24pfWE9ITF9fSkod2luZG93KTsiLCIvKiFcclxuICogVkVSU0lPTjogYmV0YSAxLjkuM1xyXG4gKiBEQVRFOiAyMDEzLTA0LTAyXHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKiovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcImVhc2luZy5CYWNrXCIsW1wiZWFzaW5nLkVhc2VcIl0sZnVuY3Rpb24odCl7dmFyIGUsaSxzLHI9d2luZG93LkdyZWVuU29ja0dsb2JhbHN8fHdpbmRvdyxuPXIuY29tLmdyZWVuc29jayxhPTIqTWF0aC5QSSxvPU1hdGguUEkvMixoPW4uX2NsYXNzLGw9ZnVuY3Rpb24oZSxpKXt2YXIgcz1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24oKXt9LCEwKSxyPXMucHJvdG90eXBlPW5ldyB0O3JldHVybiByLmNvbnN0cnVjdG9yPXMsci5nZXRSYXRpbz1pLHN9LF89dC5yZWdpc3Rlcnx8ZnVuY3Rpb24oKXt9LHU9ZnVuY3Rpb24odCxlLGkscyl7dmFyIHI9aChcImVhc2luZy5cIit0LHtlYXNlT3V0Om5ldyBlLGVhc2VJbjpuZXcgaSxlYXNlSW5PdXQ6bmV3IHN9LCEwKTtyZXR1cm4gXyhyLHQpLHJ9LGM9ZnVuY3Rpb24odCxlLGkpe3RoaXMudD10LHRoaXMudj1lLGkmJih0aGlzLm5leHQ9aSxpLnByZXY9dGhpcyx0aGlzLmM9aS52LWUsdGhpcy5nYXA9aS50LXQpfSxmPWZ1bmN0aW9uKGUsaSl7dmFyIHM9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKHQpe3RoaXMuX3AxPXR8fDA9PT10P3Q6MS43MDE1OCx0aGlzLl9wMj0xLjUyNSp0aGlzLl9wMX0sITApLHI9cy5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIHIuY29uc3RydWN0b3I9cyxyLmdldFJhdGlvPWksci5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBzKHQpfSxzfSxwPXUoXCJCYWNrXCIsZihcIkJhY2tPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4odC09MSkqdCooKHRoaXMuX3AxKzEpKnQrdGhpcy5fcDEpKzF9KSxmKFwiQmFja0luXCIsZnVuY3Rpb24odCl7cmV0dXJuIHQqdCooKHRoaXMuX3AxKzEpKnQtdGhpcy5fcDEpfSksZihcIkJhY2tJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8uNSp0KnQqKCh0aGlzLl9wMisxKSp0LXRoaXMuX3AyKTouNSooKHQtPTIpKnQqKCh0aGlzLl9wMisxKSp0K3RoaXMuX3AyKSsyKX0pKSxtPWgoXCJlYXNpbmcuU2xvd01vXCIsZnVuY3Rpb24odCxlLGkpe2U9ZXx8MD09PWU/ZTouNyxudWxsPT10P3Q9Ljc6dD4xJiYodD0xKSx0aGlzLl9wPTEhPT10P2U6MCx0aGlzLl9wMT0oMS10KS8yLHRoaXMuX3AyPXQsdGhpcy5fcDM9dGhpcy5fcDErdGhpcy5fcDIsdGhpcy5fY2FsY0VuZD1pPT09ITB9LCEwKSxkPW0ucHJvdG90eXBlPW5ldyB0O3JldHVybiBkLmNvbnN0cnVjdG9yPW0sZC5nZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZT10KyguNS10KSp0aGlzLl9wO3JldHVybiB0aGlzLl9wMT50P3RoaXMuX2NhbGNFbmQ/MS0odD0xLXQvdGhpcy5fcDEpKnQ6ZS0odD0xLXQvdGhpcy5fcDEpKnQqdCp0KmU6dD50aGlzLl9wMz90aGlzLl9jYWxjRW5kPzEtKHQ9KHQtdGhpcy5fcDMpL3RoaXMuX3AxKSp0OmUrKHQtZSkqKHQ9KHQtdGhpcy5fcDMpL3RoaXMuX3AxKSp0KnQqdDp0aGlzLl9jYWxjRW5kPzE6ZX0sbS5lYXNlPW5ldyBtKC43LC43KSxkLmNvbmZpZz1tLmNvbmZpZz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIG5ldyBtKHQsZSxpKX0sZT1oKFwiZWFzaW5nLlN0ZXBwZWRFYXNlXCIsZnVuY3Rpb24odCl7dD10fHwxLHRoaXMuX3AxPTEvdCx0aGlzLl9wMj10KzF9LCEwKSxkPWUucHJvdG90eXBlPW5ldyB0LGQuY29uc3RydWN0b3I9ZSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3JldHVybiAwPnQ/dD0wOnQ+PTEmJih0PS45OTk5OTk5OTkpLCh0aGlzLl9wMip0Pj4wKSp0aGlzLl9wMX0sZC5jb25maWc9ZS5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBlKHQpfSxpPWgoXCJlYXNpbmcuUm91Z2hFYXNlXCIsZnVuY3Rpb24oZSl7ZT1lfHx7fTtmb3IodmFyIGkscyxyLG4sYSxvLGg9ZS50YXBlcnx8XCJub25lXCIsbD1bXSxfPTAsdT0wfChlLnBvaW50c3x8MjApLGY9dSxwPWUucmFuZG9taXplIT09ITEsbT1lLmNsYW1wPT09ITAsZD1lLnRlbXBsYXRlIGluc3RhbmNlb2YgdD9lLnRlbXBsYXRlOm51bGwsZz1cIm51bWJlclwiPT10eXBlb2YgZS5zdHJlbmd0aD8uNCplLnN0cmVuZ3RoOi40Oy0tZj4tMTspaT1wP01hdGgucmFuZG9tKCk6MS91KmYscz1kP2QuZ2V0UmF0aW8oaSk6aSxcIm5vbmVcIj09PWg/cj1nOlwib3V0XCI9PT1oPyhuPTEtaSxyPW4qbipnKTpcImluXCI9PT1oP3I9aSppKmc6LjU+aT8obj0yKmkscj0uNSpuKm4qZyk6KG49MiooMS1pKSxyPS41Km4qbipnKSxwP3MrPU1hdGgucmFuZG9tKCkqci0uNSpyOmYlMj9zKz0uNSpyOnMtPS41KnIsbSYmKHM+MT9zPTE6MD5zJiYocz0wKSksbFtfKytdPXt4OmkseTpzfTtmb3IobC5zb3J0KGZ1bmN0aW9uKHQsZSl7cmV0dXJuIHQueC1lLnh9KSxvPW5ldyBjKDEsMSxudWxsKSxmPXU7LS1mPi0xOylhPWxbZl0sbz1uZXcgYyhhLngsYS55LG8pO3RoaXMuX3ByZXY9bmV3IGMoMCwwLDAhPT1vLnQ/bzpvLm5leHQpfSwhMCksZD1pLnByb3RvdHlwZT1uZXcgdCxkLmNvbnN0cnVjdG9yPWksZC5nZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZT10aGlzLl9wcmV2O2lmKHQ+ZS50KXtmb3IoO2UubmV4dCYmdD49ZS50OyllPWUubmV4dDtlPWUucHJldn1lbHNlIGZvcig7ZS5wcmV2JiZlLnQ+PXQ7KWU9ZS5wcmV2O3JldHVybiB0aGlzLl9wcmV2PWUsZS52Kyh0LWUudCkvZS5nYXAqZS5jfSxkLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IGkodCl9LGkuZWFzZT1uZXcgaSx1KFwiQm91bmNlXCIsbChcIkJvdW5jZU91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLzIuNzU+dD83LjU2MjUqdCp0OjIvMi43NT50PzcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1OjIuNS8yLjc1PnQ/Ny41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzU6Ny41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzV9KSxsKFwiQm91bmNlSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gMS8yLjc1Pih0PTEtdCk/MS03LjU2MjUqdCp0OjIvMi43NT50PzEtKDcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1KToyLjUvMi43NT50PzEtKDcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1KToxLSg3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NSl9KSxsKFwiQm91bmNlSW5PdXRcIixmdW5jdGlvbih0KXt2YXIgZT0uNT50O3JldHVybiB0PWU/MS0yKnQ6Mip0LTEsdD0xLzIuNzU+dD83LjU2MjUqdCp0OjIvMi43NT50PzcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1OjIuNS8yLjc1PnQ/Ny41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzU6Ny41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzUsZT8uNSooMS10KTouNSp0Ky41fSkpLHUoXCJDaXJjXCIsbChcIkNpcmNPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5zcXJ0KDEtKHQtPTEpKnQpfSksbChcIkNpcmNJblwiLGZ1bmN0aW9uKHQpe3JldHVybi0oTWF0aC5zcXJ0KDEtdCp0KS0xKX0pLGwoXCJDaXJjSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LS41KihNYXRoLnNxcnQoMS10KnQpLTEpOi41KihNYXRoLnNxcnQoMS0odC09MikqdCkrMSl9KSkscz1mdW5jdGlvbihlLGkscyl7dmFyIHI9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKHQsZSl7dGhpcy5fcDE9dHx8MSx0aGlzLl9wMj1lfHxzLHRoaXMuX3AzPXRoaXMuX3AyL2EqKE1hdGguYXNpbigxL3RoaXMuX3AxKXx8MCl9LCEwKSxuPXIucHJvdG90eXBlPW5ldyB0O3JldHVybiBuLmNvbnN0cnVjdG9yPXIsbi5nZXRSYXRpbz1pLG4uY29uZmlnPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG5ldyByKHQsZSl9LHJ9LHUoXCJFbGFzdGljXCIscyhcIkVsYXN0aWNPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gdGhpcy5fcDEqTWF0aC5wb3coMiwtMTAqdCkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKzF9LC4zKSxzKFwiRWxhc3RpY0luXCIsZnVuY3Rpb24odCl7cmV0dXJuLSh0aGlzLl9wMSpNYXRoLnBvdygyLDEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKX0sLjMpLHMoXCJFbGFzdGljSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LS41KnRoaXMuX3AxKk1hdGgucG93KDIsMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMik6LjUqdGhpcy5fcDEqTWF0aC5wb3coMiwtMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikrMX0sLjQ1KSksdShcIkV4cG9cIixsKFwiRXhwb091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLU1hdGgucG93KDIsLTEwKnQpfSksbChcIkV4cG9JblwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnBvdygyLDEwKih0LTEpKS0uMDAxfSksbChcIkV4cG9Jbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8uNSpNYXRoLnBvdygyLDEwKih0LTEpKTouNSooMi1NYXRoLnBvdygyLC0xMCoodC0xKSkpfSkpLHUoXCJTaW5lXCIsbChcIlNpbmVPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5zaW4odCpvKX0pLGwoXCJTaW5lSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tTWF0aC5jb3ModCpvKSsxfSksbChcIlNpbmVJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybi0uNSooTWF0aC5jb3MoTWF0aC5QSSp0KS0xKX0pKSxoKFwiZWFzaW5nLkVhc2VMb29rdXBcIix7ZmluZDpmdW5jdGlvbihlKXtyZXR1cm4gdC5tYXBbZV19fSwhMCksXyhyLlNsb3dNbyxcIlNsb3dNb1wiLFwiZWFzZSxcIiksXyhpLFwiUm91Z2hFYXNlXCIsXCJlYXNlLFwiKSxfKGUsXCJTdGVwcGVkRWFzZVwiLFwiZWFzZSxcIikscH0sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwicGx1Z2lucy5DU1NQbHVnaW5cIixbXCJwbHVnaW5zLlR3ZWVuUGx1Z2luXCIsXCJUd2VlbkxpdGVcIl0sZnVuY3Rpb24odCxlKXt2YXIgaSxyLHMsbixhPWZ1bmN0aW9uKCl7dC5jYWxsKHRoaXMsXCJjc3NcIiksdGhpcy5fb3ZlcndyaXRlUHJvcHMubGVuZ3RoPTAsdGhpcy5zZXRSYXRpbz1hLnByb3RvdHlwZS5zZXRSYXRpb30sbz17fSxsPWEucHJvdG90eXBlPW5ldyB0KFwiY3NzXCIpO2wuY29uc3RydWN0b3I9YSxhLnZlcnNpb249XCIxLjEyLjFcIixhLkFQST0yLGEuZGVmYXVsdFRyYW5zZm9ybVBlcnNwZWN0aXZlPTAsYS5kZWZhdWx0U2tld1R5cGU9XCJjb21wZW5zYXRlZFwiLGw9XCJweFwiLGEuc3VmZml4TWFwPXt0b3A6bCxyaWdodDpsLGJvdHRvbTpsLGxlZnQ6bCx3aWR0aDpsLGhlaWdodDpsLGZvbnRTaXplOmwscGFkZGluZzpsLG1hcmdpbjpsLHBlcnNwZWN0aXZlOmwsbGluZUhlaWdodDpcIlwifTt2YXIgaCx1LGYsXyxwLGMsZD0vKD86XFxkfFxcLVxcZHxcXC5cXGR8XFwtXFwuXFxkKSsvZyxtPS8oPzpcXGR8XFwtXFxkfFxcLlxcZHxcXC1cXC5cXGR8XFwrPVxcZHxcXC09XFxkfFxcKz0uXFxkfFxcLT1cXC5cXGQpKy9nLGc9Lyg/OlxcKz18XFwtPXxcXC18XFxiKVtcXGRcXC1cXC5dK1thLXpBLVowLTldKig/OiV8XFxiKS9naSx2PS9bXlxcZFxcLVxcLl0vZyx5PS8oPzpcXGR8XFwtfFxcK3w9fCN8XFwuKSovZyxUPS9vcGFjaXR5ICo9ICooW14pXSopL2ksdz0vb3BhY2l0eTooW147XSopL2kseD0vYWxwaGFcXChvcGFjaXR5ICo9Lis/XFwpL2ksYj0vXihyZ2J8aHNsKS8sUD0vKFtBLVpdKS9nLFM9Ly0oW2Etel0pL2dpLEM9LyheKD86dXJsXFwoXFxcInx1cmxcXCgpKXwoPzooXFxcIlxcKSkkfFxcKSQpL2dpLFI9ZnVuY3Rpb24odCxlKXtyZXR1cm4gZS50b1VwcGVyQ2FzZSgpfSxrPS8oPzpMZWZ0fFJpZ2h0fFdpZHRoKS9pLEE9LyhNMTF8TTEyfE0yMXxNMjIpPVtcXGRcXC1cXC5lXSsvZ2ksTz0vcHJvZ2lkXFw6RFhJbWFnZVRyYW5zZm9ybVxcLk1pY3Jvc29mdFxcLk1hdHJpeFxcKC4rP1xcKS9pLEQ9LywoPz1bXlxcKV0qKD86XFwofCQpKS9naSxNPU1hdGguUEkvMTgwLEw9MTgwL01hdGguUEksTj17fSxYPWRvY3VtZW50LHo9WC5jcmVhdGVFbGVtZW50KFwiZGl2XCIpLEk9WC5jcmVhdGVFbGVtZW50KFwiaW1nXCIpLEU9YS5faW50ZXJuYWxzPXtfc3BlY2lhbFByb3BzOm99LEY9bmF2aWdhdG9yLnVzZXJBZ2VudCxZPWZ1bmN0aW9uKCl7dmFyIHQsZT1GLmluZGV4T2YoXCJBbmRyb2lkXCIpLGk9WC5jcmVhdGVFbGVtZW50KFwiZGl2XCIpO3JldHVybiBmPS0xIT09Ri5pbmRleE9mKFwiU2FmYXJpXCIpJiYtMT09PUYuaW5kZXhPZihcIkNocm9tZVwiKSYmKC0xPT09ZXx8TnVtYmVyKEYuc3Vic3RyKGUrOCwxKSk+MykscD1mJiY2Pk51bWJlcihGLnN1YnN0cihGLmluZGV4T2YoXCJWZXJzaW9uL1wiKSs4LDEpKSxfPS0xIT09Ri5pbmRleE9mKFwiRmlyZWZveFwiKSwvTVNJRSAoWzAtOV17MSx9W1xcLjAtOV17MCx9KS8uZXhlYyhGKSYmKGM9cGFyc2VGbG9hdChSZWdFeHAuJDEpKSxpLmlubmVySFRNTD1cIjxhIHN0eWxlPSd0b3A6MXB4O29wYWNpdHk6LjU1Oyc+YTwvYT5cIix0PWkuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJhXCIpWzBdLHQ/L14wLjU1Ly50ZXN0KHQuc3R5bGUub3BhY2l0eSk6ITF9KCksQj1mdW5jdGlvbih0KXtyZXR1cm4gVC50ZXN0KFwic3RyaW5nXCI9PXR5cGVvZiB0P3Q6KHQuY3VycmVudFN0eWxlP3QuY3VycmVudFN0eWxlLmZpbHRlcjp0LnN0eWxlLmZpbHRlcil8fFwiXCIpP3BhcnNlRmxvYXQoUmVnRXhwLiQxKS8xMDA6MX0sVT1mdW5jdGlvbih0KXt3aW5kb3cuY29uc29sZSYmY29uc29sZS5sb2codCl9LFc9XCJcIixqPVwiXCIsVj1mdW5jdGlvbih0LGUpe2U9ZXx8ejt2YXIgaSxyLHM9ZS5zdHlsZTtpZih2b2lkIDAhPT1zW3RdKXJldHVybiB0O2Zvcih0PXQuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkrdC5zdWJzdHIoMSksaT1bXCJPXCIsXCJNb3pcIixcIm1zXCIsXCJNc1wiLFwiV2Via2l0XCJdLHI9NTstLXI+LTEmJnZvaWQgMD09PXNbaVtyXSt0XTspO3JldHVybiByPj0wPyhqPTM9PT1yP1wibXNcIjppW3JdLFc9XCItXCIrai50b0xvd2VyQ2FzZSgpK1wiLVwiLGordCk6bnVsbH0sSD1YLmRlZmF1bHRWaWV3P1guZGVmYXVsdFZpZXcuZ2V0Q29tcHV0ZWRTdHlsZTpmdW5jdGlvbigpe30scT1hLmdldFN0eWxlPWZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG47cmV0dXJuIFl8fFwib3BhY2l0eVwiIT09ZT8oIXImJnQuc3R5bGVbZV0/bj10LnN0eWxlW2VdOihpPWl8fEgodCkpP249aVtlXXx8aS5nZXRQcm9wZXJ0eVZhbHVlKGUpfHxpLmdldFByb3BlcnR5VmFsdWUoZS5yZXBsYWNlKFAsXCItJDFcIikudG9Mb3dlckNhc2UoKSk6dC5jdXJyZW50U3R5bGUmJihuPXQuY3VycmVudFN0eWxlW2VdKSxudWxsPT1zfHxuJiZcIm5vbmVcIiE9PW4mJlwiYXV0b1wiIT09biYmXCJhdXRvIGF1dG9cIiE9PW4/bjpzKTpCKHQpfSxRPUUuY29udmVydFRvUGl4ZWxzPWZ1bmN0aW9uKHQsaSxyLHMsbil7aWYoXCJweFwiPT09c3x8IXMpcmV0dXJuIHI7aWYoXCJhdXRvXCI9PT1zfHwhcilyZXR1cm4gMDt2YXIgbyxsLGgsdT1rLnRlc3QoaSksZj10LF89ei5zdHlsZSxwPTA+cjtpZihwJiYocj0tciksXCIlXCI9PT1zJiYtMSE9PWkuaW5kZXhPZihcImJvcmRlclwiKSlvPXIvMTAwKih1P3QuY2xpZW50V2lkdGg6dC5jbGllbnRIZWlnaHQpO2Vsc2V7aWYoXy5jc3NUZXh0PVwiYm9yZGVyOjAgc29saWQgcmVkO3Bvc2l0aW9uOlwiK3EodCxcInBvc2l0aW9uXCIpK1wiO2xpbmUtaGVpZ2h0OjA7XCIsXCIlXCIhPT1zJiZmLmFwcGVuZENoaWxkKV9bdT9cImJvcmRlckxlZnRXaWR0aFwiOlwiYm9yZGVyVG9wV2lkdGhcIl09citzO2Vsc2V7aWYoZj10LnBhcmVudE5vZGV8fFguYm9keSxsPWYuX2dzQ2FjaGUsaD1lLnRpY2tlci5mcmFtZSxsJiZ1JiZsLnRpbWU9PT1oKXJldHVybiBsLndpZHRoKnIvMTAwO19bdT9cIndpZHRoXCI6XCJoZWlnaHRcIl09citzfWYuYXBwZW5kQ2hpbGQoeiksbz1wYXJzZUZsb2F0KHpbdT9cIm9mZnNldFdpZHRoXCI6XCJvZmZzZXRIZWlnaHRcIl0pLGYucmVtb3ZlQ2hpbGQoeiksdSYmXCIlXCI9PT1zJiZhLmNhY2hlV2lkdGhzIT09ITEmJihsPWYuX2dzQ2FjaGU9Zi5fZ3NDYWNoZXx8e30sbC50aW1lPWgsbC53aWR0aD0xMDAqKG8vcikpLDAhPT1vfHxufHwobz1RKHQsaSxyLHMsITApKX1yZXR1cm4gcD8tbzpvfSxaPUUuY2FsY3VsYXRlT2Zmc2V0PWZ1bmN0aW9uKHQsZSxpKXtpZihcImFic29sdXRlXCIhPT1xKHQsXCJwb3NpdGlvblwiLGkpKXJldHVybiAwO3ZhciByPVwibGVmdFwiPT09ZT9cIkxlZnRcIjpcIlRvcFwiLHM9cSh0LFwibWFyZ2luXCIrcixpKTtyZXR1cm4gdFtcIm9mZnNldFwiK3JdLShRKHQsZSxwYXJzZUZsb2F0KHMpLHMucmVwbGFjZSh5LFwiXCIpKXx8MCl9LCQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxyLHM9e307aWYoZT1lfHxIKHQsbnVsbCkpaWYoaT1lLmxlbmd0aClmb3IoOy0taT4tMTspc1tlW2ldLnJlcGxhY2UoUyxSKV09ZS5nZXRQcm9wZXJ0eVZhbHVlKGVbaV0pO2Vsc2UgZm9yKGkgaW4gZSlzW2ldPWVbaV07ZWxzZSBpZihlPXQuY3VycmVudFN0eWxlfHx0LnN0eWxlKWZvcihpIGluIGUpXCJzdHJpbmdcIj09dHlwZW9mIGkmJnZvaWQgMD09PXNbaV0mJihzW2kucmVwbGFjZShTLFIpXT1lW2ldKTtyZXR1cm4gWXx8KHMub3BhY2l0eT1CKHQpKSxyPVBlKHQsZSwhMSkscy5yb3RhdGlvbj1yLnJvdGF0aW9uLHMuc2tld1g9ci5za2V3WCxzLnNjYWxlWD1yLnNjYWxlWCxzLnNjYWxlWT1yLnNjYWxlWSxzLng9ci54LHMueT1yLnkseGUmJihzLno9ci56LHMucm90YXRpb25YPXIucm90YXRpb25YLHMucm90YXRpb25ZPXIucm90YXRpb25ZLHMuc2NhbGVaPXIuc2NhbGVaKSxzLmZpbHRlcnMmJmRlbGV0ZSBzLmZpbHRlcnMsc30sRz1mdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuLGEsbyxsPXt9LGg9dC5zdHlsZTtmb3IoYSBpbiBpKVwiY3NzVGV4dFwiIT09YSYmXCJsZW5ndGhcIiE9PWEmJmlzTmFOKGEpJiYoZVthXSE9PShuPWlbYV0pfHxzJiZzW2FdKSYmLTE9PT1hLmluZGV4T2YoXCJPcmlnaW5cIikmJihcIm51bWJlclwiPT10eXBlb2Ygbnx8XCJzdHJpbmdcIj09dHlwZW9mIG4pJiYobFthXT1cImF1dG9cIiE9PW58fFwibGVmdFwiIT09YSYmXCJ0b3BcIiE9PWE/XCJcIiE9PW4mJlwiYXV0b1wiIT09biYmXCJub25lXCIhPT1ufHxcInN0cmluZ1wiIT10eXBlb2YgZVthXXx8XCJcIj09PWVbYV0ucmVwbGFjZSh2LFwiXCIpP246MDpaKHQsYSksdm9pZCAwIT09aFthXSYmKG89bmV3IGZlKGgsYSxoW2FdLG8pKSk7aWYocilmb3IoYSBpbiByKVwiY2xhc3NOYW1lXCIhPT1hJiYobFthXT1yW2FdKTtyZXR1cm57ZGlmczpsLGZpcnN0TVBUOm99fSxLPXt3aWR0aDpbXCJMZWZ0XCIsXCJSaWdodFwiXSxoZWlnaHQ6W1wiVG9wXCIsXCJCb3R0b21cIl19LEo9W1wibWFyZ2luTGVmdFwiLFwibWFyZ2luUmlnaHRcIixcIm1hcmdpblRvcFwiLFwibWFyZ2luQm90dG9tXCJdLHRlPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcj1wYXJzZUZsb2F0KFwid2lkdGhcIj09PWU/dC5vZmZzZXRXaWR0aDp0Lm9mZnNldEhlaWdodCkscz1LW2VdLG49cy5sZW5ndGg7Zm9yKGk9aXx8SCh0LG51bGwpOy0tbj4tMTspci09cGFyc2VGbG9hdChxKHQsXCJwYWRkaW5nXCIrc1tuXSxpLCEwKSl8fDAsci09cGFyc2VGbG9hdChxKHQsXCJib3JkZXJcIitzW25dK1wiV2lkdGhcIixpLCEwKSl8fDA7cmV0dXJuIHJ9LGVlPWZ1bmN0aW9uKHQsZSl7KG51bGw9PXR8fFwiXCI9PT10fHxcImF1dG9cIj09PXR8fFwiYXV0byBhdXRvXCI9PT10KSYmKHQ9XCIwIDBcIik7dmFyIGk9dC5zcGxpdChcIiBcIikscj0tMSE9PXQuaW5kZXhPZihcImxlZnRcIik/XCIwJVwiOi0xIT09dC5pbmRleE9mKFwicmlnaHRcIik/XCIxMDAlXCI6aVswXSxzPS0xIT09dC5pbmRleE9mKFwidG9wXCIpP1wiMCVcIjotMSE9PXQuaW5kZXhPZihcImJvdHRvbVwiKT9cIjEwMCVcIjppWzFdO3JldHVybiBudWxsPT1zP3M9XCIwXCI6XCJjZW50ZXJcIj09PXMmJihzPVwiNTAlXCIpLChcImNlbnRlclwiPT09cnx8aXNOYU4ocGFyc2VGbG9hdChyKSkmJi0xPT09KHIrXCJcIikuaW5kZXhPZihcIj1cIikpJiYocj1cIjUwJVwiKSxlJiYoZS5veHA9LTEhPT1yLmluZGV4T2YoXCIlXCIpLGUub3lwPS0xIT09cy5pbmRleE9mKFwiJVwiKSxlLm94cj1cIj1cIj09PXIuY2hhckF0KDEpLGUub3lyPVwiPVwiPT09cy5jaGFyQXQoMSksZS5veD1wYXJzZUZsb2F0KHIucmVwbGFjZSh2LFwiXCIpKSxlLm95PXBhcnNlRmxvYXQocy5yZXBsYWNlKHYsXCJcIikpKSxyK1wiIFwiK3MrKGkubGVuZ3RoPjI/XCIgXCIraVsyXTpcIlwiKX0saWU9ZnVuY3Rpb24odCxlKXtyZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgdCYmXCI9XCI9PT10LmNoYXJBdCgxKT9wYXJzZUludCh0LmNoYXJBdCgwKStcIjFcIiwxMCkqcGFyc2VGbG9hdCh0LnN1YnN0cigyKSk6cGFyc2VGbG9hdCh0KS1wYXJzZUZsb2F0KGUpfSxyZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsPT10P2U6XCJzdHJpbmdcIj09dHlwZW9mIHQmJlwiPVwiPT09dC5jaGFyQXQoMSk/cGFyc2VJbnQodC5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcih0LnN1YnN0cigyKSkrZTpwYXJzZUZsb2F0KHQpfSxzZT1mdW5jdGlvbih0LGUsaSxyKXt2YXIgcyxuLGEsbyxsPTFlLTY7cmV0dXJuIG51bGw9PXQ/bz1lOlwibnVtYmVyXCI9PXR5cGVvZiB0P289dDoocz0zNjAsbj10LnNwbGl0KFwiX1wiKSxhPU51bWJlcihuWzBdLnJlcGxhY2UodixcIlwiKSkqKC0xPT09dC5pbmRleE9mKFwicmFkXCIpPzE6TCktKFwiPVwiPT09dC5jaGFyQXQoMSk/MDplKSxuLmxlbmd0aCYmKHImJihyW2ldPWUrYSksLTEhPT10LmluZGV4T2YoXCJzaG9ydFwiKSYmKGElPXMsYSE9PWElKHMvMikmJihhPTA+YT9hK3M6YS1zKSksLTEhPT10LmluZGV4T2YoXCJfY3dcIikmJjA+YT9hPShhKzk5OTk5OTk5OTkqcyklcy0oMHxhL3MpKnM6LTEhPT10LmluZGV4T2YoXCJjY3dcIikmJmE+MCYmKGE9KGEtOTk5OTk5OTk5OSpzKSVzLSgwfGEvcykqcykpLG89ZSthKSxsPm8mJm8+LWwmJihvPTApLG99LG5lPXthcXVhOlswLDI1NSwyNTVdLGxpbWU6WzAsMjU1LDBdLHNpbHZlcjpbMTkyLDE5MiwxOTJdLGJsYWNrOlswLDAsMF0sbWFyb29uOlsxMjgsMCwwXSx0ZWFsOlswLDEyOCwxMjhdLGJsdWU6WzAsMCwyNTVdLG5hdnk6WzAsMCwxMjhdLHdoaXRlOlsyNTUsMjU1LDI1NV0sZnVjaHNpYTpbMjU1LDAsMjU1XSxvbGl2ZTpbMTI4LDEyOCwwXSx5ZWxsb3c6WzI1NSwyNTUsMF0sb3JhbmdlOlsyNTUsMTY1LDBdLGdyYXk6WzEyOCwxMjgsMTI4XSxwdXJwbGU6WzEyOCwwLDEyOF0sZ3JlZW46WzAsMTI4LDBdLHJlZDpbMjU1LDAsMF0scGluazpbMjU1LDE5MiwyMDNdLGN5YW46WzAsMjU1LDI1NV0sdHJhbnNwYXJlbnQ6WzI1NSwyNTUsMjU1LDBdfSxhZT1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIHQ9MD50P3QrMTp0PjE/dC0xOnQsMHwyNTUqKDE+Nip0P2UrNiooaS1lKSp0Oi41PnQ/aToyPjMqdD9lKzYqKGktZSkqKDIvMy10KTplKSsuNX0sb2U9ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhO3JldHVybiB0JiZcIlwiIT09dD9cIm51bWJlclwiPT10eXBlb2YgdD9bdD4+MTYsMjU1JnQ+PjgsMjU1JnRdOihcIixcIj09PXQuY2hhckF0KHQubGVuZ3RoLTEpJiYodD10LnN1YnN0cigwLHQubGVuZ3RoLTEpKSxuZVt0XT9uZVt0XTpcIiNcIj09PXQuY2hhckF0KDApPyg0PT09dC5sZW5ndGgmJihlPXQuY2hhckF0KDEpLGk9dC5jaGFyQXQoMikscj10LmNoYXJBdCgzKSx0PVwiI1wiK2UrZStpK2krcityKSx0PXBhcnNlSW50KHQuc3Vic3RyKDEpLDE2KSxbdD4+MTYsMjU1JnQ+PjgsMjU1JnRdKTpcImhzbFwiPT09dC5zdWJzdHIoMCwzKT8odD10Lm1hdGNoKGQpLHM9TnVtYmVyKHRbMF0pJTM2MC8zNjAsbj1OdW1iZXIodFsxXSkvMTAwLGE9TnVtYmVyKHRbMl0pLzEwMCxpPS41Pj1hP2EqKG4rMSk6YStuLWEqbixlPTIqYS1pLHQubGVuZ3RoPjMmJih0WzNdPU51bWJlcih0WzNdKSksdFswXT1hZShzKzEvMyxlLGkpLHRbMV09YWUocyxlLGkpLHRbMl09YWUocy0xLzMsZSxpKSx0KToodD10Lm1hdGNoKGQpfHxuZS50cmFuc3BhcmVudCx0WzBdPU51bWJlcih0WzBdKSx0WzFdPU51bWJlcih0WzFdKSx0WzJdPU51bWJlcih0WzJdKSx0Lmxlbmd0aD4zJiYodFszXT1OdW1iZXIodFszXSkpLHQpKTpuZS5ibGFja30sbGU9XCIoPzpcXFxcYig/Oig/OnJnYnxyZ2JhfGhzbHxoc2xhKVxcXFwoLis/XFxcXCkpfFxcXFxCIy4rP1xcXFxiXCI7Zm9yKGwgaW4gbmUpbGUrPVwifFwiK2wrXCJcXFxcYlwiO2xlPVJlZ0V4cChsZStcIilcIixcImdpXCIpO3ZhciBoZT1mdW5jdGlvbih0LGUsaSxyKXtpZihudWxsPT10KXJldHVybiBmdW5jdGlvbih0KXtyZXR1cm4gdH07dmFyIHMsbj1lPyh0Lm1hdGNoKGxlKXx8W1wiXCJdKVswXTpcIlwiLGE9dC5zcGxpdChuKS5qb2luKFwiXCIpLm1hdGNoKGcpfHxbXSxvPXQuc3Vic3RyKDAsdC5pbmRleE9mKGFbMF0pKSxsPVwiKVwiPT09dC5jaGFyQXQodC5sZW5ndGgtMSk/XCIpXCI6XCJcIixoPS0xIT09dC5pbmRleE9mKFwiIFwiKT9cIiBcIjpcIixcIix1PWEubGVuZ3RoLGY9dT4wP2FbMF0ucmVwbGFjZShkLFwiXCIpOlwiXCI7cmV0dXJuIHU/cz1lP2Z1bmN0aW9uKHQpe3ZhciBlLF8scCxjO2lmKFwibnVtYmVyXCI9PXR5cGVvZiB0KXQrPWY7ZWxzZSBpZihyJiZELnRlc3QodCkpe2ZvcihjPXQucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIikscD0wO2MubGVuZ3RoPnA7cCsrKWNbcF09cyhjW3BdKTtyZXR1cm4gYy5qb2luKFwiLFwiKX1pZihlPSh0Lm1hdGNoKGxlKXx8W25dKVswXSxfPXQuc3BsaXQoZSkuam9pbihcIlwiKS5tYXRjaChnKXx8W10scD1fLmxlbmd0aCx1PnAtLSlmb3IoO3U+KytwOylfW3BdPWk/X1swfChwLTEpLzJdOmFbcF07cmV0dXJuIG8rXy5qb2luKGgpK2grZStsKygtMSE9PXQuaW5kZXhPZihcImluc2V0XCIpP1wiIGluc2V0XCI6XCJcIil9OmZ1bmN0aW9uKHQpe3ZhciBlLG4sXztpZihcIm51bWJlclwiPT10eXBlb2YgdCl0Kz1mO2Vsc2UgaWYociYmRC50ZXN0KHQpKXtmb3Iobj10LnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLF89MDtuLmxlbmd0aD5fO18rKyluW19dPXMobltfXSk7cmV0dXJuIG4uam9pbihcIixcIil9aWYoZT10Lm1hdGNoKGcpfHxbXSxfPWUubGVuZ3RoLHU+Xy0tKWZvcig7dT4rK187KWVbX109aT9lWzB8KF8tMSkvMl06YVtfXTtyZXR1cm4gbytlLmpvaW4oaCkrbH06ZnVuY3Rpb24odCl7cmV0dXJuIHR9fSx1ZT1mdW5jdGlvbih0KXtyZXR1cm4gdD10LnNwbGl0KFwiLFwiKSxmdW5jdGlvbihlLGkscixzLG4sYSxvKXt2YXIgbCxoPShpK1wiXCIpLnNwbGl0KFwiIFwiKTtmb3Iobz17fSxsPTA7ND5sO2wrKylvW3RbbF1dPWhbbF09aFtsXXx8aFsobC0xKS8yPj4wXTtyZXR1cm4gcy5wYXJzZShlLG8sbixhKX19LGZlPShFLl9zZXRQbHVnaW5SYXRpbz1mdW5jdGlvbih0KXt0aGlzLnBsdWdpbi5zZXRSYXRpbyh0KTtmb3IodmFyIGUsaSxyLHMsbj10aGlzLmRhdGEsYT1uLnByb3h5LG89bi5maXJzdE1QVCxsPTFlLTY7bzspZT1hW28udl0sby5yP2U9TWF0aC5yb3VuZChlKTpsPmUmJmU+LWwmJihlPTApLG8udFtvLnBdPWUsbz1vLl9uZXh0O2lmKG4uYXV0b1JvdGF0ZSYmKG4uYXV0b1JvdGF0ZS5yb3RhdGlvbj1hLnJvdGF0aW9uKSwxPT09dClmb3Iobz1uLmZpcnN0TVBUO287KXtpZihpPW8udCxpLnR5cGUpe2lmKDE9PT1pLnR5cGUpe2ZvcihzPWkueHMwK2kucytpLnhzMSxyPTE7aS5sPnI7cisrKXMrPWlbXCJ4blwiK3JdK2lbXCJ4c1wiKyhyKzEpXTtpLmU9c319ZWxzZSBpLmU9aS5zK2kueHMwO289by5fbmV4dH19LGZ1bmN0aW9uKHQsZSxpLHIscyl7dGhpcy50PXQsdGhpcy5wPWUsdGhpcy52PWksdGhpcy5yPXMsciYmKHIuX3ByZXY9dGhpcyx0aGlzLl9uZXh0PXIpfSksX2U9KEUuX3BhcnNlVG9Qcm94eT1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGEsbyxsLGgsdSxmPXIsXz17fSxwPXt9LGM9aS5fdHJhbnNmb3JtLGQ9Tjtmb3IoaS5fdHJhbnNmb3JtPW51bGwsTj1lLHI9dT1pLnBhcnNlKHQsZSxyLHMpLE49ZCxuJiYoaS5fdHJhbnNmb3JtPWMsZiYmKGYuX3ByZXY9bnVsbCxmLl9wcmV2JiYoZi5fcHJldi5fbmV4dD1udWxsKSkpO3ImJnIhPT1mOyl7aWYoMT49ci50eXBlJiYobz1yLnAscFtvXT1yLnMrci5jLF9bb109ci5zLG58fChoPW5ldyBmZShyLFwic1wiLG8saCxyLnIpLHIuYz0wKSwxPT09ci50eXBlKSlmb3IoYT1yLmw7LS1hPjA7KWw9XCJ4blwiK2Esbz1yLnArXCJfXCIrbCxwW29dPXIuZGF0YVtsXSxfW29dPXJbbF0sbnx8KGg9bmV3IGZlKHIsbCxvLGgsci5yeHBbbF0pKTtyPXIuX25leHR9cmV0dXJue3Byb3h5Ol8sZW5kOnAsZmlyc3RNUFQ6aCxwdDp1fX0sRS5DU1NQcm9wVHdlZW49ZnVuY3Rpb24odCxlLHIscyxhLG8sbCxoLHUsZixfKXt0aGlzLnQ9dCx0aGlzLnA9ZSx0aGlzLnM9cix0aGlzLmM9cyx0aGlzLm49bHx8ZSx0IGluc3RhbmNlb2YgX2V8fG4ucHVzaCh0aGlzLm4pLHRoaXMucj1oLHRoaXMudHlwZT1vfHwwLHUmJih0aGlzLnByPXUsaT0hMCksdGhpcy5iPXZvaWQgMD09PWY/cjpmLHRoaXMuZT12b2lkIDA9PT1fP3IrczpfLGEmJih0aGlzLl9uZXh0PWEsYS5fcHJldj10aGlzKX0pLHBlPWEucGFyc2VDb21wbGV4PWZ1bmN0aW9uKHQsZSxpLHIscyxuLGEsbyxsLHUpe2k9aXx8bnx8XCJcIixhPW5ldyBfZSh0LGUsMCwwLGEsdT8yOjEsbnVsbCwhMSxvLGkscikscis9XCJcIjt2YXIgZixfLHAsYyxnLHYseSxULHcseCxQLFMsQz1pLnNwbGl0KFwiLCBcIikuam9pbihcIixcIikuc3BsaXQoXCIgXCIpLFI9ci5zcGxpdChcIiwgXCIpLmpvaW4oXCIsXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoLEE9aCE9PSExO2ZvcigoLTEhPT1yLmluZGV4T2YoXCIsXCIpfHwtMSE9PWkuaW5kZXhPZihcIixcIikpJiYoQz1DLmpvaW4oXCIgXCIpLnJlcGxhY2UoRCxcIiwgXCIpLnNwbGl0KFwiIFwiKSxSPVIuam9pbihcIiBcIikucmVwbGFjZShELFwiLCBcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgpLGshPT1SLmxlbmd0aCYmKEM9KG58fFwiXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoKSxhLnBsdWdpbj1sLGEuc2V0UmF0aW89dSxmPTA7az5mO2YrKylpZihjPUNbZl0sZz1SW2ZdLFQ9cGFyc2VGbG9hdChjKSxUfHwwPT09VClhLmFwcGVuZFh0cmEoXCJcIixULGllKGcsVCksZy5yZXBsYWNlKG0sXCJcIiksQSYmLTEhPT1nLmluZGV4T2YoXCJweFwiKSwhMCk7ZWxzZSBpZihzJiYoXCIjXCI9PT1jLmNoYXJBdCgwKXx8bmVbY118fGIudGVzdChjKSkpUz1cIixcIj09PWcuY2hhckF0KGcubGVuZ3RoLTEpP1wiKSxcIjpcIilcIixjPW9lKGMpLGc9b2UoZyksdz1jLmxlbmd0aCtnLmxlbmd0aD42LHcmJiFZJiYwPT09Z1szXT8oYVtcInhzXCIrYS5sXSs9YS5sP1wiIHRyYW5zcGFyZW50XCI6XCJ0cmFuc3BhcmVudFwiLGEuZT1hLmUuc3BsaXQoUltmXSkuam9pbihcInRyYW5zcGFyZW50XCIpKTooWXx8KHc9ITEpLGEuYXBwZW5kWHRyYSh3P1wicmdiYShcIjpcInJnYihcIixjWzBdLGdbMF0tY1swXSxcIixcIiwhMCwhMCkuYXBwZW5kWHRyYShcIlwiLGNbMV0sZ1sxXS1jWzFdLFwiLFwiLCEwKS5hcHBlbmRYdHJhKFwiXCIsY1syXSxnWzJdLWNbMl0sdz9cIixcIjpTLCEwKSx3JiYoYz00PmMubGVuZ3RoPzE6Y1szXSxhLmFwcGVuZFh0cmEoXCJcIixjLCg0PmcubGVuZ3RoPzE6Z1szXSktYyxTLCExKSkpO2Vsc2UgaWYodj1jLm1hdGNoKGQpKXtpZih5PWcubWF0Y2gobSksIXl8fHkubGVuZ3RoIT09di5sZW5ndGgpcmV0dXJuIGE7Zm9yKHA9MCxfPTA7di5sZW5ndGg+XztfKyspUD12W19dLHg9Yy5pbmRleE9mKFAscCksYS5hcHBlbmRYdHJhKGMuc3Vic3RyKHAseC1wKSxOdW1iZXIoUCksaWUoeVtfXSxQKSxcIlwiLEEmJlwicHhcIj09PWMuc3Vic3RyKHgrUC5sZW5ndGgsMiksMD09PV8pLHA9eCtQLmxlbmd0aDthW1wieHNcIithLmxdKz1jLnN1YnN0cihwKX1lbHNlIGFbXCJ4c1wiK2EubF0rPWEubD9cIiBcIitjOmM7aWYoLTEhPT1yLmluZGV4T2YoXCI9XCIpJiZhLmRhdGEpe2ZvcihTPWEueHMwK2EuZGF0YS5zLGY9MTthLmw+ZjtmKyspUys9YVtcInhzXCIrZl0rYS5kYXRhW1wieG5cIitmXTthLmU9UythW1wieHNcIitmXX1yZXR1cm4gYS5sfHwoYS50eXBlPS0xLGEueHMwPWEuZSksYS54Zmlyc3R8fGF9LGNlPTk7Zm9yKGw9X2UucHJvdG90eXBlLGwubD1sLnByPTA7LS1jZT4wOylsW1wieG5cIitjZV09MCxsW1wieHNcIitjZV09XCJcIjtsLnhzMD1cIlwiLGwuX25leHQ9bC5fcHJldj1sLnhmaXJzdD1sLmRhdGE9bC5wbHVnaW49bC5zZXRSYXRpbz1sLnJ4cD1udWxsLGwuYXBwZW5kWHRyYT1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGE9dGhpcyxvPWEubDtyZXR1cm4gYVtcInhzXCIrb10rPW4mJm8/XCIgXCIrdDp0fHxcIlwiLGl8fDA9PT1vfHxhLnBsdWdpbj8oYS5sKyssYS50eXBlPWEuc2V0UmF0aW8/MjoxLGFbXCJ4c1wiK2EubF09cnx8XCJcIixvPjA/KGEuZGF0YVtcInhuXCIrb109ZStpLGEucnhwW1wieG5cIitvXT1zLGFbXCJ4blwiK29dPWUsYS5wbHVnaW58fChhLnhmaXJzdD1uZXcgX2UoYSxcInhuXCIrbyxlLGksYS54Zmlyc3R8fGEsMCxhLm4scyxhLnByKSxhLnhmaXJzdC54czA9MCksYSk6KGEuZGF0YT17czplK2l9LGEucnhwPXt9LGEucz1lLGEuYz1pLGEucj1zLGEpKTooYVtcInhzXCIrb10rPWUrKHJ8fFwiXCIpLGEpfTt2YXIgZGU9ZnVuY3Rpb24odCxlKXtlPWV8fHt9LHRoaXMucD1lLnByZWZpeD9WKHQpfHx0OnQsb1t0XT1vW3RoaXMucF09dGhpcyx0aGlzLmZvcm1hdD1lLmZvcm1hdHRlcnx8aGUoZS5kZWZhdWx0VmFsdWUsZS5jb2xvcixlLmNvbGxhcHNpYmxlLGUubXVsdGkpLGUucGFyc2VyJiYodGhpcy5wYXJzZT1lLnBhcnNlciksdGhpcy5jbHJzPWUuY29sb3IsdGhpcy5tdWx0aT1lLm11bHRpLHRoaXMua2V5d29yZD1lLmtleXdvcmQsdGhpcy5kZmx0PWUuZGVmYXVsdFZhbHVlLHRoaXMucHI9ZS5wcmlvcml0eXx8MH0sbWU9RS5fcmVnaXN0ZXJDb21wbGV4U3BlY2lhbFByb3A9ZnVuY3Rpb24odCxlLGkpe1wib2JqZWN0XCIhPXR5cGVvZiBlJiYoZT17cGFyc2VyOml9KTt2YXIgcixzLG49dC5zcGxpdChcIixcIiksYT1lLmRlZmF1bHRWYWx1ZTtmb3IoaT1pfHxbYV0scj0wO24ubGVuZ3RoPnI7cisrKWUucHJlZml4PTA9PT1yJiZlLnByZWZpeCxlLmRlZmF1bHRWYWx1ZT1pW3JdfHxhLHM9bmV3IGRlKG5bcl0sZSl9LGdlPWZ1bmN0aW9uKHQpe2lmKCFvW3RdKXt2YXIgZT10LmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK3Quc3Vic3RyKDEpK1wiUGx1Z2luXCI7bWUodCx7cGFyc2VyOmZ1bmN0aW9uKHQsaSxyLHMsbixhLGwpe3ZhciBoPSh3aW5kb3cuR3JlZW5Tb2NrR2xvYmFsc3x8d2luZG93KS5jb20uZ3JlZW5zb2NrLnBsdWdpbnNbZV07cmV0dXJuIGg/KGguX2Nzc1JlZ2lzdGVyKCksb1tyXS5wYXJzZSh0LGkscixzLG4sYSxsKSk6KFUoXCJFcnJvcjogXCIrZStcIiBqcyBmaWxlIG5vdCBsb2FkZWQuXCIpLG4pfX0pfX07bD1kZS5wcm90b3R5cGUsbC5wYXJzZUNvbXBsZXg9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhLG8sbCxoLHUsZixfPXRoaXMua2V5d29yZDtpZih0aGlzLm11bHRpJiYoRC50ZXN0KGkpfHxELnRlc3QoZSk/KG89ZS5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxsPWkucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIikpOl8mJihvPVtlXSxsPVtpXSkpLGwpe2ZvcihoPWwubGVuZ3RoPm8ubGVuZ3RoP2wubGVuZ3RoOm8ubGVuZ3RoLGE9MDtoPmE7YSsrKWU9b1thXT1vW2FdfHx0aGlzLmRmbHQsaT1sW2FdPWxbYV18fHRoaXMuZGZsdCxfJiYodT1lLmluZGV4T2YoXyksZj1pLmluZGV4T2YoXyksdSE9PWYmJihpPS0xPT09Zj9sOm8saVthXSs9XCIgXCIrXykpO2U9by5qb2luKFwiLCBcIiksaT1sLmpvaW4oXCIsIFwiKX1yZXR1cm4gcGUodCx0aGlzLnAsZSxpLHRoaXMuY2xycyx0aGlzLmRmbHQscix0aGlzLnByLHMsbil9LGwucGFyc2U9ZnVuY3Rpb24odCxlLGkscixuLGEpe3JldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLHRoaXMuZm9ybWF0KHEodCx0aGlzLnAscywhMSx0aGlzLmRmbHQpKSx0aGlzLmZvcm1hdChlKSxuLGEpfSxhLnJlZ2lzdGVyU3BlY2lhbFByb3A9ZnVuY3Rpb24odCxlLGkpe21lKHQse3BhcnNlcjpmdW5jdGlvbih0LHIscyxuLGEsbyl7dmFyIGw9bmV3IF9lKHQscywwLDAsYSwyLHMsITEsaSk7cmV0dXJuIGwucGx1Z2luPW8sbC5zZXRSYXRpbz1lKHQscixuLl90d2VlbixzKSxsfSxwcmlvcml0eTppfSl9O3ZhciB2ZT1cInNjYWxlWCxzY2FsZVksc2NhbGVaLHgseSx6LHNrZXdYLHNrZXdZLHJvdGF0aW9uLHJvdGF0aW9uWCxyb3RhdGlvblkscGVyc3BlY3RpdmVcIi5zcGxpdChcIixcIikseWU9VihcInRyYW5zZm9ybVwiKSxUZT1XK1widHJhbnNmb3JtXCIsd2U9VihcInRyYW5zZm9ybU9yaWdpblwiKSx4ZT1udWxsIT09VihcInBlcnNwZWN0aXZlXCIpLGJlPUUuVHJhbnNmb3JtPWZ1bmN0aW9uKCl7dGhpcy5za2V3WT0wfSxQZT1FLmdldFRyYW5zZm9ybT1mdW5jdGlvbih0LGUsaSxyKXtpZih0Ll9nc1RyYW5zZm9ybSYmaSYmIXIpcmV0dXJuIHQuX2dzVHJhbnNmb3JtO3ZhciBzLG4sbyxsLGgsdSxmLF8scCxjLGQsbSxnLHY9aT90Ll9nc1RyYW5zZm9ybXx8bmV3IGJlOm5ldyBiZSx5PTA+di5zY2FsZVgsVD0yZS01LHc9MWU1LHg9MTc5Ljk5LGI9eCpNLFA9eGU/cGFyc2VGbG9hdChxKHQsd2UsZSwhMSxcIjAgMCAwXCIpLnNwbGl0KFwiIFwiKVsyXSl8fHYuek9yaWdpbnx8MDowO2Zvcih5ZT9zPXEodCxUZSxlLCEwKTp0LmN1cnJlbnRTdHlsZSYmKHM9dC5jdXJyZW50U3R5bGUuZmlsdGVyLm1hdGNoKEEpLHM9cyYmND09PXMubGVuZ3RoP1tzWzBdLnN1YnN0cig0KSxOdW1iZXIoc1syXS5zdWJzdHIoNCkpLE51bWJlcihzWzFdLnN1YnN0cig0KSksc1szXS5zdWJzdHIoNCksdi54fHwwLHYueXx8MF0uam9pbihcIixcIik6XCJcIiksbj0oc3x8XCJcIikubWF0Y2goLyg/OlxcLXxcXGIpW1xcZFxcLVxcLmVdK1xcYi9naSl8fFtdLG89bi5sZW5ndGg7LS1vPi0xOylsPU51bWJlcihuW29dKSxuW29dPShoPWwtKGx8PTApKT8oMHxoKncrKDA+aD8tLjU6LjUpKS93K2w6bDtpZigxNj09PW4ubGVuZ3RoKXt2YXIgUz1uWzhdLEM9bls5XSxSPW5bMTBdLGs9blsxMl0sTz1uWzEzXSxEPW5bMTRdO2lmKHYuek9yaWdpbiYmKEQ9LXYuek9yaWdpbixrPVMqRC1uWzEyXSxPPUMqRC1uWzEzXSxEPVIqRCt2LnpPcmlnaW4tblsxNF0pLCFpfHxyfHxudWxsPT12LnJvdGF0aW9uWCl7dmFyIE4sWCx6LEksRSxGLFksQj1uWzBdLFU9blsxXSxXPW5bMl0saj1uWzNdLFY9bls0XSxIPW5bNV0sUT1uWzZdLFo9bls3XSwkPW5bMTFdLEc9TWF0aC5hdGFuMihRLFIpLEs9LWI+R3x8Rz5iO3Yucm90YXRpb25YPUcqTCxHJiYoST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksTj1WKkkrUypFLFg9SCpJK0MqRSx6PVEqSStSKkUsUz1WKi1FK1MqSSxDPUgqLUUrQypJLFI9USotRStSKkksJD1aKi1FKyQqSSxWPU4sSD1YLFE9eiksRz1NYXRoLmF0YW4yKFMsQiksdi5yb3RhdGlvblk9RypMLEcmJihGPS1iPkd8fEc+YixJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxOPUIqSS1TKkUsWD1VKkktQypFLHo9VypJLVIqRSxDPVUqRStDKkksUj1XKkUrUipJLCQ9aipFKyQqSSxCPU4sVT1YLFc9eiksRz1NYXRoLmF0YW4yKFUsSCksdi5yb3RhdGlvbj1HKkwsRyYmKFk9LWI+R3x8Rz5iLEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLEI9QipJK1YqRSxYPVUqSStIKkUsSD1VKi1FK0gqSSxRPVcqLUUrUSpJLFU9WCksWSYmSz92LnJvdGF0aW9uPXYucm90YXRpb25YPTA6WSYmRj92LnJvdGF0aW9uPXYucm90YXRpb25ZPTA6RiYmSyYmKHYucm90YXRpb25ZPXYucm90YXRpb25YPTApLHYuc2NhbGVYPSgwfE1hdGguc3FydChCKkIrVSpVKSp3Ky41KS93LHYuc2NhbGVZPSgwfE1hdGguc3FydChIKkgrQypDKSp3Ky41KS93LHYuc2NhbGVaPSgwfE1hdGguc3FydChRKlErUipSKSp3Ky41KS93LHYuc2tld1g9MCx2LnBlcnNwZWN0aXZlPSQ/MS8oMD4kPy0kOiQpOjAsdi54PWssdi55PU8sdi56PUR9fWVsc2UgaWYoISh4ZSYmIXImJm4ubGVuZ3RoJiZ2Lng9PT1uWzRdJiZ2Lnk9PT1uWzVdJiYodi5yb3RhdGlvblh8fHYucm90YXRpb25ZKXx8dm9pZCAwIT09di54JiZcIm5vbmVcIj09PXEodCxcImRpc3BsYXlcIixlKSkpe3ZhciBKPW4ubGVuZ3RoPj02LHRlPUo/blswXToxLGVlPW5bMV18fDAsaWU9blsyXXx8MCxyZT1KP25bM106MTt2Lng9bls0XXx8MCx2Lnk9bls1XXx8MCx1PU1hdGguc3FydCh0ZSp0ZStlZSplZSksZj1NYXRoLnNxcnQocmUqcmUraWUqaWUpLF89dGV8fGVlP01hdGguYXRhbjIoZWUsdGUpKkw6di5yb3RhdGlvbnx8MCxwPWllfHxyZT9NYXRoLmF0YW4yKGllLHJlKSpMK186di5za2V3WHx8MCxjPXUtTWF0aC5hYnModi5zY2FsZVh8fDApLGQ9Zi1NYXRoLmFicyh2LnNjYWxlWXx8MCksTWF0aC5hYnMocCk+OTAmJjI3MD5NYXRoLmFicyhwKSYmKHk/KHUqPS0xLHArPTA+PV8/MTgwOi0xODAsXys9MD49Xz8xODA6LTE4MCk6KGYqPS0xLHArPTA+PXA/MTgwOi0xODApKSxtPShfLXYucm90YXRpb24pJTE4MCxnPShwLXYuc2tld1gpJTE4MCwodm9pZCAwPT09di5za2V3WHx8Yz5UfHwtVD5jfHxkPlR8fC1UPmR8fG0+LXgmJng+bSYmZmFsc2V8bSp3fHxnPi14JiZ4PmcmJmZhbHNlfGcqdykmJih2LnNjYWxlWD11LHYuc2NhbGVZPWYsdi5yb3RhdGlvbj1fLHYuc2tld1g9cCkseGUmJih2LnJvdGF0aW9uWD12LnJvdGF0aW9uWT12Lno9MCx2LnBlcnNwZWN0aXZlPXBhcnNlRmxvYXQoYS5kZWZhdWx0VHJhbnNmb3JtUGVyc3BlY3RpdmUpfHwwLHYuc2NhbGVaPTEpfXYuek9yaWdpbj1QO2ZvcihvIGluIHYpVD52W29dJiZ2W29dPi1UJiYodltvXT0wKTtyZXR1cm4gaSYmKHQuX2dzVHJhbnNmb3JtPXYpLHZ9LFNlPWZ1bmN0aW9uKHQpe3ZhciBlLGkscj10aGlzLmRhdGEscz0tci5yb3RhdGlvbipNLG49cytyLnNrZXdYKk0sYT0xZTUsbz0oMHxNYXRoLmNvcyhzKSpyLnNjYWxlWCphKS9hLGw9KDB8TWF0aC5zaW4ocykqci5zY2FsZVgqYSkvYSxoPSgwfE1hdGguc2luKG4pKi1yLnNjYWxlWSphKS9hLHU9KDB8TWF0aC5jb3Mobikqci5zY2FsZVkqYSkvYSxmPXRoaXMudC5zdHlsZSxfPXRoaXMudC5jdXJyZW50U3R5bGU7aWYoXyl7aT1sLGw9LWgsaD0taSxlPV8uZmlsdGVyLGYuZmlsdGVyPVwiXCI7dmFyIHAsZCxtPXRoaXMudC5vZmZzZXRXaWR0aCxnPXRoaXMudC5vZmZzZXRIZWlnaHQsdj1cImFic29sdXRlXCIhPT1fLnBvc2l0aW9uLHc9XCJwcm9naWQ6RFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuTWF0cml4KE0xMT1cIitvK1wiLCBNMTI9XCIrbCtcIiwgTTIxPVwiK2grXCIsIE0yMj1cIit1LHg9ci54LGI9ci55O2lmKG51bGwhPXIub3gmJihwPShyLm94cD8uMDEqbSpyLm94OnIub3gpLW0vMixkPShyLm95cD8uMDEqZypyLm95OnIub3kpLWcvMix4Kz1wLShwKm8rZCpsKSxiKz1kLShwKmgrZCp1KSksdj8ocD1tLzIsZD1nLzIsdys9XCIsIER4PVwiKyhwLShwKm8rZCpsKSt4KStcIiwgRHk9XCIrKGQtKHAqaCtkKnUpK2IpK1wiKVwiKTp3Kz1cIiwgc2l6aW5nTWV0aG9kPSdhdXRvIGV4cGFuZCcpXCIsZi5maWx0ZXI9LTEhPT1lLmluZGV4T2YoXCJEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5NYXRyaXgoXCIpP2UucmVwbGFjZShPLHcpOncrXCIgXCIrZSwoMD09PXR8fDE9PT10KSYmMT09PW8mJjA9PT1sJiYwPT09aCYmMT09PXUmJih2JiYtMT09PXcuaW5kZXhPZihcIkR4PTAsIER5PTBcIil8fFQudGVzdChlKSYmMTAwIT09cGFyc2VGbG9hdChSZWdFeHAuJDEpfHwtMT09PWUuaW5kZXhPZihcImdyYWRpZW50KFwiJiZlLmluZGV4T2YoXCJBbHBoYVwiKSkmJmYucmVtb3ZlQXR0cmlidXRlKFwiZmlsdGVyXCIpKSwhdil7dmFyIFAsUyxDLFI9OD5jPzE6LTE7Zm9yKHA9ci5pZU9mZnNldFh8fDAsZD1yLmllT2Zmc2V0WXx8MCxyLmllT2Zmc2V0WD1NYXRoLnJvdW5kKChtLSgoMD5vPy1vOm8pKm0rKDA+bD8tbDpsKSpnKSkvMit4KSxyLmllT2Zmc2V0WT1NYXRoLnJvdW5kKChnLSgoMD51Py11OnUpKmcrKDA+aD8taDpoKSptKSkvMitiKSxjZT0wOzQ+Y2U7Y2UrKylTPUpbY2VdLFA9X1tTXSxpPS0xIT09UC5pbmRleE9mKFwicHhcIik/cGFyc2VGbG9hdChQKTpRKHRoaXMudCxTLHBhcnNlRmxvYXQoUCksUC5yZXBsYWNlKHksXCJcIikpfHwwLEM9aSE9PXJbU10/Mj5jZT8tci5pZU9mZnNldFg6LXIuaWVPZmZzZXRZOjI+Y2U/cC1yLmllT2Zmc2V0WDpkLXIuaWVPZmZzZXRZLGZbU109KHJbU109TWF0aC5yb3VuZChpLUMqKDA9PT1jZXx8Mj09PWNlPzE6UikpKStcInB4XCJ9fX0sQ2U9RS5zZXQzRFRyYW5zZm9ybVJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYSxvLGwsaCx1LGYscCxjLGQsbSxnLHYseSxULHcseCxiLFAsUz10aGlzLmRhdGEsQz10aGlzLnQuc3R5bGUsUj1TLnJvdGF0aW9uKk0saz1TLnNjYWxlWCxBPVMuc2NhbGVZLE89Uy5zY2FsZVosRD1TLnBlcnNwZWN0aXZlO2lmKCEoMSE9PXQmJjAhPT10fHxcImF1dG9cIiE9PVMuZm9yY2UzRHx8Uy5yb3RhdGlvbll8fFMucm90YXRpb25YfHwxIT09T3x8RHx8Uy56KSlyZXR1cm4gUmUuY2FsbCh0aGlzLHQpLHZvaWQgMDtpZihfKXt2YXIgTD0xZS00O0w+ayYmaz4tTCYmKGs9Tz0yZS01KSxMPkEmJkE+LUwmJihBPU89MmUtNSksIUR8fFMuenx8Uy5yb3RhdGlvblh8fFMucm90YXRpb25ZfHwoRD0wKX1pZihSfHxTLnNrZXdYKXk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxlPXksbj1ULFMuc2tld1gmJihSLT1TLnNrZXdYKk0seT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLFwic2ltcGxlXCI9PT1TLnNrZXdUeXBlJiYodz1NYXRoLnRhbihTLnNrZXdYKk0pLHc9TWF0aC5zcXJ0KDErdyp3KSx5Kj13LFQqPXcpKSxpPS1ULGE9eTtlbHNle2lmKCEoUy5yb3RhdGlvbll8fFMucm90YXRpb25YfHwxIT09T3x8RCkpcmV0dXJuIENbeWVdPVwidHJhbnNsYXRlM2QoXCIrUy54K1wicHgsXCIrUy55K1wicHgsXCIrUy56K1wicHgpXCIrKDEhPT1rfHwxIT09QT9cIiBzY2FsZShcIitrK1wiLFwiK0ErXCIpXCI6XCJcIiksdm9pZCAwO2U9YT0xLGk9bj0wfWY9MSxyPXM9bz1sPWg9dT1wPWM9ZD0wLG09RD8tMS9EOjAsZz1TLnpPcmlnaW4sdj0xZTUsUj1TLnJvdGF0aW9uWSpNLFImJih5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksaD1mKi1ULGM9bSotVCxyPWUqVCxvPW4qVCxmKj15LG0qPXksZSo9eSxuKj15KSxSPVMucm90YXRpb25YKk0sUiYmKHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSx3PWkqeStyKlQseD1hKnkrbypULGI9dSp5K2YqVCxQPWQqeSttKlQscj1pKi1UK3IqeSxvPWEqLVQrbyp5LGY9dSotVCtmKnksbT1kKi1UK20qeSxpPXcsYT14LHU9YixkPVApLDEhPT1PJiYocio9TyxvKj1PLGYqPU8sbSo9TyksMSE9PUEmJihpKj1BLGEqPUEsdSo9QSxkKj1BKSwxIT09ayYmKGUqPWssbio9ayxoKj1rLGMqPWspLGcmJihwLT1nLHM9cipwLGw9bypwLHA9ZipwK2cpLHM9KHc9KHMrPVMueCktKHN8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K3M6cyxsPSh3PShsKz1TLnkpLShsfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditsOmwscD0odz0ocCs9Uy56KS0ocHw9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrcDpwLENbeWVdPVwibWF0cml4M2QoXCIrWygwfGUqdikvdiwoMHxuKnYpL3YsKDB8aCp2KS92LCgwfGMqdikvdiwoMHxpKnYpL3YsKDB8YSp2KS92LCgwfHUqdikvdiwoMHxkKnYpL3YsKDB8cip2KS92LCgwfG8qdikvdiwoMHxmKnYpL3YsKDB8bSp2KS92LHMsbCxwLEQ/MSstcC9EOjFdLmpvaW4oXCIsXCIpK1wiKVwifSxSZT1FLnNldDJEVHJhbnNmb3JtUmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhPXRoaXMuZGF0YSxvPXRoaXMudCxsPW8uc3R5bGU7cmV0dXJuIGEucm90YXRpb25YfHxhLnJvdGF0aW9uWXx8YS56fHxhLmZvcmNlM0Q9PT0hMHx8XCJhdXRvXCI9PT1hLmZvcmNlM0QmJjEhPT10JiYwIT09dD8odGhpcy5zZXRSYXRpbz1DZSxDZS5jYWxsKHRoaXMsdCksdm9pZCAwKTooYS5yb3RhdGlvbnx8YS5za2V3WD8oZT1hLnJvdGF0aW9uKk0saT1lLWEuc2tld1gqTSxyPTFlNSxzPWEuc2NhbGVYKnIsbj1hLnNjYWxlWSpyLGxbeWVdPVwibWF0cml4KFwiKygwfE1hdGguY29zKGUpKnMpL3IrXCIsXCIrKDB8TWF0aC5zaW4oZSkqcykvcitcIixcIisoMHxNYXRoLnNpbihpKSotbikvcitcIixcIisoMHxNYXRoLmNvcyhpKSpuKS9yK1wiLFwiK2EueCtcIixcIithLnkrXCIpXCIpOmxbeWVdPVwibWF0cml4KFwiK2Euc2NhbGVYK1wiLDAsMCxcIithLnNjYWxlWStcIixcIithLngrXCIsXCIrYS55K1wiKVwiLHZvaWQgMCl9O21lKFwidHJhbnNmb3JtLHNjYWxlLHNjYWxlWCxzY2FsZVksc2NhbGVaLHgseSx6LHJvdGF0aW9uLHJvdGF0aW9uWCxyb3RhdGlvblkscm90YXRpb25aLHNrZXdYLHNrZXdZLHNob3J0Um90YXRpb24sc2hvcnRSb3RhdGlvblgsc2hvcnRSb3RhdGlvblksc2hvcnRSb3RhdGlvblosdHJhbnNmb3JtT3JpZ2luLHRyYW5zZm9ybVBlcnNwZWN0aXZlLGRpcmVjdGlvbmFsUm90YXRpb24scGFyc2VUcmFuc2Zvcm0sZm9yY2UzRCxza2V3VHlwZVwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLG8sbCl7aWYoci5fdHJhbnNmb3JtKXJldHVybiBuO3ZhciBoLHUsZixfLHAsYyxkLG09ci5fdHJhbnNmb3JtPVBlKHQscywhMCxsLnBhcnNlVHJhbnNmb3JtKSxnPXQuc3R5bGUsdj0xZS02LHk9dmUubGVuZ3RoLFQ9bCx3PXt9O2lmKFwic3RyaW5nXCI9PXR5cGVvZiBULnRyYW5zZm9ybSYmeWUpZj16LnN0eWxlLGZbeWVdPVQudHJhbnNmb3JtLGYuZGlzcGxheT1cImJsb2NrXCIsZi5wb3NpdGlvbj1cImFic29sdXRlXCIsWC5ib2R5LmFwcGVuZENoaWxkKHopLGg9UGUoeixudWxsLCExKSxYLmJvZHkucmVtb3ZlQ2hpbGQoeik7ZWxzZSBpZihcIm9iamVjdFwiPT10eXBlb2YgVCl7aWYoaD17c2NhbGVYOnJlKG51bGwhPVQuc2NhbGVYP1Quc2NhbGVYOlQuc2NhbGUsbS5zY2FsZVgpLHNjYWxlWTpyZShudWxsIT1ULnNjYWxlWT9ULnNjYWxlWTpULnNjYWxlLG0uc2NhbGVZKSxzY2FsZVo6cmUoVC5zY2FsZVosbS5zY2FsZVopLHg6cmUoVC54LG0ueCkseTpyZShULnksbS55KSx6OnJlKFQueixtLnopLHBlcnNwZWN0aXZlOnJlKFQudHJhbnNmb3JtUGVyc3BlY3RpdmUsbS5wZXJzcGVjdGl2ZSl9LGQ9VC5kaXJlY3Rpb25hbFJvdGF0aW9uLG51bGwhPWQpaWYoXCJvYmplY3RcIj09dHlwZW9mIGQpZm9yKGYgaW4gZClUW2ZdPWRbZl07ZWxzZSBULnJvdGF0aW9uPWQ7aC5yb3RhdGlvbj1zZShcInJvdGF0aW9uXCJpbiBUP1Qucm90YXRpb246XCJzaG9ydFJvdGF0aW9uXCJpbiBUP1Quc2hvcnRSb3RhdGlvbitcIl9zaG9ydFwiOlwicm90YXRpb25aXCJpbiBUP1Qucm90YXRpb25aOm0ucm90YXRpb24sbS5yb3RhdGlvbixcInJvdGF0aW9uXCIsdykseGUmJihoLnJvdGF0aW9uWD1zZShcInJvdGF0aW9uWFwiaW4gVD9ULnJvdGF0aW9uWDpcInNob3J0Um90YXRpb25YXCJpbiBUP1Quc2hvcnRSb3RhdGlvblgrXCJfc2hvcnRcIjptLnJvdGF0aW9uWHx8MCxtLnJvdGF0aW9uWCxcInJvdGF0aW9uWFwiLHcpLGgucm90YXRpb25ZPXNlKFwicm90YXRpb25ZXCJpbiBUP1Qucm90YXRpb25ZOlwic2hvcnRSb3RhdGlvbllcImluIFQ/VC5zaG9ydFJvdGF0aW9uWStcIl9zaG9ydFwiOm0ucm90YXRpb25ZfHwwLG0ucm90YXRpb25ZLFwicm90YXRpb25ZXCIsdykpLGguc2tld1g9bnVsbD09VC5za2V3WD9tLnNrZXdYOnNlKFQuc2tld1gsbS5za2V3WCksaC5za2V3WT1udWxsPT1ULnNrZXdZP20uc2tld1k6c2UoVC5za2V3WSxtLnNrZXdZKSwodT1oLnNrZXdZLW0uc2tld1kpJiYoaC5za2V3WCs9dSxoLnJvdGF0aW9uKz11KX1mb3IoeGUmJm51bGwhPVQuZm9yY2UzRCYmKG0uZm9yY2UzRD1ULmZvcmNlM0QsYz0hMCksbS5za2V3VHlwZT1ULnNrZXdUeXBlfHxtLnNrZXdUeXBlfHxhLmRlZmF1bHRTa2V3VHlwZSxwPW0uZm9yY2UzRHx8bS56fHxtLnJvdGF0aW9uWHx8bS5yb3RhdGlvbll8fGguenx8aC5yb3RhdGlvblh8fGgucm90YXRpb25ZfHxoLnBlcnNwZWN0aXZlLHB8fG51bGw9PVQuc2NhbGV8fChoLnNjYWxlWj0xKTstLXk+LTE7KWk9dmVbeV0sXz1oW2ldLW1baV0sKF8+dnx8LXY+X3x8bnVsbCE9TltpXSkmJihjPSEwLG49bmV3IF9lKG0saSxtW2ldLF8sbiksaSBpbiB3JiYobi5lPXdbaV0pLG4ueHMwPTAsbi5wbHVnaW49byxyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKG4ubikpO3JldHVybiBfPVQudHJhbnNmb3JtT3JpZ2luLChffHx4ZSYmcCYmbS56T3JpZ2luKSYmKHllPyhjPSEwLGk9d2UsXz0oX3x8cSh0LGkscywhMSxcIjUwJSA1MCVcIikpK1wiXCIsbj1uZXcgX2UoZyxpLDAsMCxuLC0xLFwidHJhbnNmb3JtT3JpZ2luXCIpLG4uYj1nW2ldLG4ucGx1Z2luPW8seGU/KGY9bS56T3JpZ2luLF89Xy5zcGxpdChcIiBcIiksbS56T3JpZ2luPShfLmxlbmd0aD4yJiYoMD09PWZ8fFwiMHB4XCIhPT1fWzJdKT9wYXJzZUZsb2F0KF9bMl0pOmYpfHwwLG4ueHMwPW4uZT1fWzBdK1wiIFwiKyhfWzFdfHxcIjUwJVwiKStcIiAwcHhcIixuPW5ldyBfZShtLFwiek9yaWdpblwiLDAsMCxuLC0xLG4ubiksbi5iPWYsbi54czA9bi5lPW0uek9yaWdpbik6bi54czA9bi5lPV8pOmVlKF8rXCJcIixtKSksYyYmKHIuX3RyYW5zZm9ybVR5cGU9cHx8Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGU/MzoyKSxufSxwcmVmaXg6ITB9KSxtZShcImJveFNoYWRvd1wiLHtkZWZhdWx0VmFsdWU6XCIwcHggMHB4IDBweCAwcHggIzk5OVwiLHByZWZpeDohMCxjb2xvcjohMCxtdWx0aTohMCxrZXl3b3JkOlwiaW5zZXRcIn0pLG1lKFwiYm9yZGVyUmFkaXVzXCIse2RlZmF1bHRWYWx1ZTpcIjBweFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxuLGEpe2U9dGhpcy5mb3JtYXQoZSk7dmFyIG8sbCxoLHUsZixfLHAsYyxkLG0sZyx2LHksVCx3LHgsYj1bXCJib3JkZXJUb3BMZWZ0UmFkaXVzXCIsXCJib3JkZXJUb3BSaWdodFJhZGl1c1wiLFwiYm9yZGVyQm90dG9tUmlnaHRSYWRpdXNcIixcImJvcmRlckJvdHRvbUxlZnRSYWRpdXNcIl0sUD10LnN0eWxlO2ZvcihkPXBhcnNlRmxvYXQodC5vZmZzZXRXaWR0aCksbT1wYXJzZUZsb2F0KHQub2Zmc2V0SGVpZ2h0KSxvPWUuc3BsaXQoXCIgXCIpLGw9MDtiLmxlbmd0aD5sO2wrKyl0aGlzLnAuaW5kZXhPZihcImJvcmRlclwiKSYmKGJbbF09VihiW2xdKSksZj11PXEodCxiW2xdLHMsITEsXCIwcHhcIiksLTEhPT1mLmluZGV4T2YoXCIgXCIpJiYodT1mLnNwbGl0KFwiIFwiKSxmPXVbMF0sdT11WzFdKSxfPWg9b1tsXSxwPXBhcnNlRmxvYXQoZiksdj1mLnN1YnN0cigocCtcIlwiKS5sZW5ndGgpLHk9XCI9XCI9PT1fLmNoYXJBdCgxKSx5PyhjPXBhcnNlSW50KF8uY2hhckF0KDApK1wiMVwiLDEwKSxfPV8uc3Vic3RyKDIpLGMqPXBhcnNlRmxvYXQoXyksZz1fLnN1YnN0cigoYytcIlwiKS5sZW5ndGgtKDA+Yz8xOjApKXx8XCJcIik6KGM9cGFyc2VGbG9hdChfKSxnPV8uc3Vic3RyKChjK1wiXCIpLmxlbmd0aCkpLFwiXCI9PT1nJiYoZz1yW2ldfHx2KSxnIT09diYmKFQ9USh0LFwiYm9yZGVyTGVmdFwiLHAsdiksdz1RKHQsXCJib3JkZXJUb3BcIixwLHYpLFwiJVwiPT09Zz8oZj0xMDAqKFQvZCkrXCIlXCIsdT0xMDAqKHcvbSkrXCIlXCIpOlwiZW1cIj09PWc/KHg9USh0LFwiYm9yZGVyTGVmdFwiLDEsXCJlbVwiKSxmPVQveCtcImVtXCIsdT13L3grXCJlbVwiKTooZj1UK1wicHhcIix1PXcrXCJweFwiKSx5JiYoXz1wYXJzZUZsb2F0KGYpK2MrZyxoPXBhcnNlRmxvYXQodSkrYytnKSksYT1wZShQLGJbbF0sZitcIiBcIit1LF8rXCIgXCIraCwhMSxcIjBweFwiLGEpO3JldHVybiBhfSxwcmVmaXg6ITAsZm9ybWF0dGVyOmhlKFwiMHB4IDBweCAwcHggMHB4XCIsITEsITApfSksbWUoXCJiYWNrZ3JvdW5kUG9zaXRpb25cIix7ZGVmYXVsdFZhbHVlOlwiMCAwXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbyxsLGgsdSxmLF8scD1cImJhY2tncm91bmQtcG9zaXRpb25cIixkPXN8fEgodCxudWxsKSxtPXRoaXMuZm9ybWF0KChkP2M/ZC5nZXRQcm9wZXJ0eVZhbHVlKHArXCIteFwiKStcIiBcIitkLmdldFByb3BlcnR5VmFsdWUocCtcIi15XCIpOmQuZ2V0UHJvcGVydHlWYWx1ZShwKTp0LmN1cnJlbnRTdHlsZS5iYWNrZ3JvdW5kUG9zaXRpb25YK1wiIFwiK3QuY3VycmVudFN0eWxlLmJhY2tncm91bmRQb3NpdGlvblkpfHxcIjAgMFwiKSxnPXRoaXMuZm9ybWF0KGUpO2lmKC0xIT09bS5pbmRleE9mKFwiJVwiKSE9KC0xIT09Zy5pbmRleE9mKFwiJVwiKSkmJihfPXEodCxcImJhY2tncm91bmRJbWFnZVwiKS5yZXBsYWNlKEMsXCJcIiksXyYmXCJub25lXCIhPT1fKSl7Zm9yKG89bS5zcGxpdChcIiBcIiksbD1nLnNwbGl0KFwiIFwiKSxJLnNldEF0dHJpYnV0ZShcInNyY1wiLF8pLGg9MjstLWg+LTE7KW09b1toXSx1PS0xIT09bS5pbmRleE9mKFwiJVwiKSx1IT09KC0xIT09bFtoXS5pbmRleE9mKFwiJVwiKSkmJihmPTA9PT1oP3Qub2Zmc2V0V2lkdGgtSS53aWR0aDp0Lm9mZnNldEhlaWdodC1JLmhlaWdodCxvW2hdPXU/cGFyc2VGbG9hdChtKS8xMDAqZitcInB4XCI6MTAwKihwYXJzZUZsb2F0KG0pL2YpK1wiJVwiKTttPW8uam9pbihcIiBcIil9cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsbSxnLG4sYSl9LGZvcm1hdHRlcjplZX0pLG1lKFwiYmFja2dyb3VuZFNpemVcIix7ZGVmYXVsdFZhbHVlOlwiMCAwXCIsZm9ybWF0dGVyOmVlfSksbWUoXCJwZXJzcGVjdGl2ZVwiLHtkZWZhdWx0VmFsdWU6XCIwcHhcIixwcmVmaXg6ITB9KSxtZShcInBlcnNwZWN0aXZlT3JpZ2luXCIse2RlZmF1bHRWYWx1ZTpcIjUwJSA1MCVcIixwcmVmaXg6ITB9KSxtZShcInRyYW5zZm9ybVN0eWxlXCIse3ByZWZpeDohMH0pLG1lKFwiYmFja2ZhY2VWaXNpYmlsaXR5XCIse3ByZWZpeDohMH0pLG1lKFwidXNlclNlbGVjdFwiLHtwcmVmaXg6ITB9KSxtZShcIm1hcmdpblwiLHtwYXJzZXI6dWUoXCJtYXJnaW5Ub3AsbWFyZ2luUmlnaHQsbWFyZ2luQm90dG9tLG1hcmdpbkxlZnRcIil9KSxtZShcInBhZGRpbmdcIix7cGFyc2VyOnVlKFwicGFkZGluZ1RvcCxwYWRkaW5nUmlnaHQscGFkZGluZ0JvdHRvbSxwYWRkaW5nTGVmdFwiKX0pLG1lKFwiY2xpcFwiLHtkZWZhdWx0VmFsdWU6XCJyZWN0KDBweCwwcHgsMHB4LDBweClcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvLGwsaDtyZXR1cm4gOT5jPyhsPXQuY3VycmVudFN0eWxlLGg9OD5jP1wiIFwiOlwiLFwiLG89XCJyZWN0KFwiK2wuY2xpcFRvcCtoK2wuY2xpcFJpZ2h0K2grbC5jbGlwQm90dG9tK2grbC5jbGlwTGVmdCtcIilcIixlPXRoaXMuZm9ybWF0KGUpLnNwbGl0KFwiLFwiKS5qb2luKGgpKToobz10aGlzLmZvcm1hdChxKHQsdGhpcy5wLHMsITEsdGhpcy5kZmx0KSksZT10aGlzLmZvcm1hdChlKSksdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSxvLGUsbixhKX19KSxtZShcInRleHRTaGFkb3dcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IDBweCAwcHggIzk5OVwiLGNvbG9yOiEwLG11bHRpOiEwfSksbWUoXCJhdXRvUm91bmQsc3RyaWN0VW5pdHNcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIscyl7cmV0dXJuIHN9fSksbWUoXCJib3JkZXJcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IHNvbGlkICMwMDBcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3JldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLHRoaXMuZm9ybWF0KHEodCxcImJvcmRlclRvcFdpZHRoXCIscywhMSxcIjBweFwiKStcIiBcIitxKHQsXCJib3JkZXJUb3BTdHlsZVwiLHMsITEsXCJzb2xpZFwiKStcIiBcIitxKHQsXCJib3JkZXJUb3BDb2xvclwiLHMsITEsXCIjMDAwXCIpKSx0aGlzLmZvcm1hdChlKSxuLGEpfSxjb2xvcjohMCxmb3JtYXR0ZXI6ZnVuY3Rpb24odCl7dmFyIGU9dC5zcGxpdChcIiBcIik7cmV0dXJuIGVbMF0rXCIgXCIrKGVbMV18fFwic29saWRcIikrXCIgXCIrKHQubWF0Y2gobGUpfHxbXCIjMDAwXCJdKVswXX19KSxtZShcImJvcmRlcldpZHRoXCIse3BhcnNlcjp1ZShcImJvcmRlclRvcFdpZHRoLGJvcmRlclJpZ2h0V2lkdGgsYm9yZGVyQm90dG9tV2lkdGgsYm9yZGVyTGVmdFdpZHRoXCIpfSksbWUoXCJmbG9hdCxjc3NGbG9hdCxzdHlsZUZsb2F0XCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuPXQuc3R5bGUsYT1cImNzc0Zsb2F0XCJpbiBuP1wiY3NzRmxvYXRcIjpcInN0eWxlRmxvYXRcIjtyZXR1cm4gbmV3IF9lKG4sYSwwLDAscywtMSxpLCExLDAsblthXSxlKX19KTt2YXIga2U9ZnVuY3Rpb24odCl7dmFyIGUsaT10aGlzLnQscj1pLmZpbHRlcnx8cSh0aGlzLmRhdGEsXCJmaWx0ZXJcIikscz0wfHRoaXMucyt0aGlzLmMqdDsxMDA9PT1zJiYoLTE9PT1yLmluZGV4T2YoXCJhdHJpeChcIikmJi0xPT09ci5pbmRleE9mKFwicmFkaWVudChcIikmJi0xPT09ci5pbmRleE9mKFwib2FkZXIoXCIpPyhpLnJlbW92ZUF0dHJpYnV0ZShcImZpbHRlclwiKSxlPSFxKHRoaXMuZGF0YSxcImZpbHRlclwiKSk6KGkuZmlsdGVyPXIucmVwbGFjZSh4LFwiXCIpLGU9ITApKSxlfHwodGhpcy54bjEmJihpLmZpbHRlcj1yPXJ8fFwiYWxwaGEob3BhY2l0eT1cIitzK1wiKVwiKSwtMT09PXIuaW5kZXhPZihcInBhY2l0eVwiKT8wPT09cyYmdGhpcy54bjF8fChpLmZpbHRlcj1yK1wiIGFscGhhKG9wYWNpdHk9XCIrcytcIilcIik6aS5maWx0ZXI9ci5yZXBsYWNlKFQsXCJvcGFjaXR5PVwiK3MpKX07bWUoXCJvcGFjaXR5LGFscGhhLGF1dG9BbHBoYVwiLHtkZWZhdWx0VmFsdWU6XCIxXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbz1wYXJzZUZsb2F0KHEodCxcIm9wYWNpdHlcIixzLCExLFwiMVwiKSksbD10LnN0eWxlLGg9XCJhdXRvQWxwaGFcIj09PWk7cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIGUmJlwiPVwiPT09ZS5jaGFyQXQoMSkmJihlPShcIi1cIj09PWUuY2hhckF0KDApPy0xOjEpKnBhcnNlRmxvYXQoZS5zdWJzdHIoMikpK28pLGgmJjE9PT1vJiZcImhpZGRlblwiPT09cSh0LFwidmlzaWJpbGl0eVwiLHMpJiYwIT09ZSYmKG89MCksWT9uPW5ldyBfZShsLFwib3BhY2l0eVwiLG8sZS1vLG4pOihuPW5ldyBfZShsLFwib3BhY2l0eVwiLDEwMCpvLDEwMCooZS1vKSxuKSxuLnhuMT1oPzE6MCxsLnpvb209MSxuLnR5cGU9MixuLmI9XCJhbHBoYShvcGFjaXR5PVwiK24ucytcIilcIixuLmU9XCJhbHBoYShvcGFjaXR5PVwiKyhuLnMrbi5jKStcIilcIixuLmRhdGE9dCxuLnBsdWdpbj1hLG4uc2V0UmF0aW89a2UpLGgmJihuPW5ldyBfZShsLFwidmlzaWJpbGl0eVwiLDAsMCxuLC0xLG51bGwsITEsMCwwIT09bz9cImluaGVyaXRcIjpcImhpZGRlblwiLDA9PT1lP1wiaGlkZGVuXCI6XCJpbmhlcml0XCIpLG4ueHMwPVwiaW5oZXJpdFwiLHIuX292ZXJ3cml0ZVByb3BzLnB1c2gobi5uKSxyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKGkpKSxufX0pO3ZhciBBZT1mdW5jdGlvbih0LGUpe2UmJih0LnJlbW92ZVByb3BlcnR5PyhcIm1zXCI9PT1lLnN1YnN0cigwLDIpJiYoZT1cIk1cIitlLnN1YnN0cigxKSksdC5yZW1vdmVQcm9wZXJ0eShlLnJlcGxhY2UoUCxcIi0kMVwiKS50b0xvd2VyQ2FzZSgpKSk6dC5yZW1vdmVBdHRyaWJ1dGUoZSkpfSxPZT1mdW5jdGlvbih0KXtpZih0aGlzLnQuX2dzQ2xhc3NQVD10aGlzLDE9PT10fHwwPT09dCl7dGhpcy50LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsMD09PXQ/dGhpcy5iOnRoaXMuZSk7Zm9yKHZhciBlPXRoaXMuZGF0YSxpPXRoaXMudC5zdHlsZTtlOyllLnY/aVtlLnBdPWUudjpBZShpLGUucCksZT1lLl9uZXh0OzE9PT10JiZ0aGlzLnQuX2dzQ2xhc3NQVD09PXRoaXMmJih0aGlzLnQuX2dzQ2xhc3NQVD1udWxsKX1lbHNlIHRoaXMudC5nZXRBdHRyaWJ1dGUoXCJjbGFzc1wiKSE9PXRoaXMuZSYmdGhpcy50LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsdGhpcy5lKX07bWUoXCJjbGFzc05hbWVcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxyLG4sYSxvLGwpe3ZhciBoLHUsZixfLHAsYz10LmdldEF0dHJpYnV0ZShcImNsYXNzXCIpfHxcIlwiLGQ9dC5zdHlsZS5jc3NUZXh0O2lmKGE9bi5fY2xhc3NOYW1lUFQ9bmV3IF9lKHQsciwwLDAsYSwyKSxhLnNldFJhdGlvPU9lLGEucHI9LTExLGk9ITAsYS5iPWMsdT0kKHQscyksZj10Ll9nc0NsYXNzUFQpe2ZvcihfPXt9LHA9Zi5kYXRhO3A7KV9bcC5wXT0xLHA9cC5fbmV4dDtmLnNldFJhdGlvKDEpfXJldHVybiB0Ll9nc0NsYXNzUFQ9YSxhLmU9XCI9XCIhPT1lLmNoYXJBdCgxKT9lOmMucmVwbGFjZShSZWdFeHAoXCJcXFxccypcXFxcYlwiK2Uuc3Vic3RyKDIpK1wiXFxcXGJcIiksXCJcIikrKFwiK1wiPT09ZS5jaGFyQXQoMCk/XCIgXCIrZS5zdWJzdHIoMik6XCJcIiksbi5fdHdlZW4uX2R1cmF0aW9uJiYodC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLGEuZSksaD1HKHQsdSwkKHQpLGwsXyksdC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLGMpLGEuZGF0YT1oLmZpcnN0TVBULHQuc3R5bGUuY3NzVGV4dD1kLGE9YS54Zmlyc3Q9bi5wYXJzZSh0LGguZGlmcyxhLG8pKSxhfX0pO3ZhciBEZT1mdW5jdGlvbih0KXtpZigoMT09PXR8fDA9PT10KSYmdGhpcy5kYXRhLl90b3RhbFRpbWU9PT10aGlzLmRhdGEuX3RvdGFsRHVyYXRpb24mJlwiaXNGcm9tU3RhcnRcIiE9PXRoaXMuZGF0YS5kYXRhKXt2YXIgZSxpLHIscyxuPXRoaXMudC5zdHlsZSxhPW8udHJhbnNmb3JtLnBhcnNlO2lmKFwiYWxsXCI9PT10aGlzLmUpbi5jc3NUZXh0PVwiXCIscz0hMDtlbHNlIGZvcihlPXRoaXMuZS5zcGxpdChcIixcIikscj1lLmxlbmd0aDstLXI+LTE7KWk9ZVtyXSxvW2ldJiYob1tpXS5wYXJzZT09PWE/cz0hMDppPVwidHJhbnNmb3JtT3JpZ2luXCI9PT1pP3dlOm9baV0ucCksQWUobixpKTtzJiYoQWUobix5ZSksdGhpcy50Ll9nc1RyYW5zZm9ybSYmZGVsZXRlIHRoaXMudC5fZ3NUcmFuc2Zvcm0pfX07Zm9yKG1lKFwiY2xlYXJQcm9wc1wiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLHIscyxuKXtyZXR1cm4gbj1uZXcgX2UodCxyLDAsMCxuLDIpLG4uc2V0UmF0aW89RGUsbi5lPWUsbi5wcj0tMTAsbi5kYXRhPXMuX3R3ZWVuLGk9ITAsbn19KSxsPVwiYmV6aWVyLHRocm93UHJvcHMscGh5c2ljc1Byb3BzLHBoeXNpY3MyRFwiLnNwbGl0KFwiLFwiKSxjZT1sLmxlbmd0aDtjZS0tOylnZShsW2NlXSk7bD1hLnByb3RvdHlwZSxsLl9maXJzdFBUPW51bGwsbC5fb25Jbml0VHdlZW49ZnVuY3Rpb24odCxlLG8pe2lmKCF0Lm5vZGVUeXBlKXJldHVybiExO3RoaXMuX3RhcmdldD10LHRoaXMuX3R3ZWVuPW8sdGhpcy5fdmFycz1lLGg9ZS5hdXRvUm91bmQsaT0hMSxyPWUuc3VmZml4TWFwfHxhLnN1ZmZpeE1hcCxzPUgodCxcIlwiKSxuPXRoaXMuX292ZXJ3cml0ZVByb3BzO3ZhciBsLF8sYyxkLG0sZyx2LHksVCx4PXQuc3R5bGU7aWYodSYmXCJcIj09PXguekluZGV4JiYobD1xKHQsXCJ6SW5kZXhcIixzKSwoXCJhdXRvXCI9PT1sfHxcIlwiPT09bCkmJnRoaXMuX2FkZExhenlTZXQoeCxcInpJbmRleFwiLDApKSxcInN0cmluZ1wiPT10eXBlb2YgZSYmKGQ9eC5jc3NUZXh0LGw9JCh0LHMpLHguY3NzVGV4dD1kK1wiO1wiK2UsbD1HKHQsbCwkKHQpKS5kaWZzLCFZJiZ3LnRlc3QoZSkmJihsLm9wYWNpdHk9cGFyc2VGbG9hdChSZWdFeHAuJDEpKSxlPWwseC5jc3NUZXh0PWQpLHRoaXMuX2ZpcnN0UFQ9Xz10aGlzLnBhcnNlKHQsZSxudWxsKSx0aGlzLl90cmFuc2Zvcm1UeXBlKXtmb3IoVD0zPT09dGhpcy5fdHJhbnNmb3JtVHlwZSx5ZT9mJiYodT0hMCxcIlwiPT09eC56SW5kZXgmJih2PXEodCxcInpJbmRleFwiLHMpLChcImF1dG9cIj09PXZ8fFwiXCI9PT12KSYmdGhpcy5fYWRkTGF6eVNldCh4LFwiekluZGV4XCIsMCkpLHAmJnRoaXMuX2FkZExhenlTZXQoeCxcIldlYmtpdEJhY2tmYWNlVmlzaWJpbGl0eVwiLHRoaXMuX3ZhcnMuV2Via2l0QmFja2ZhY2VWaXNpYmlsaXR5fHwoVD9cInZpc2libGVcIjpcImhpZGRlblwiKSkpOnguem9vbT0xLGM9XztjJiZjLl9uZXh0OyljPWMuX25leHQ7eT1uZXcgX2UodCxcInRyYW5zZm9ybVwiLDAsMCxudWxsLDIpLHRoaXMuX2xpbmtDU1NQKHksbnVsbCxjKSx5LnNldFJhdGlvPVQmJnhlP0NlOnllP1JlOlNlLHkuZGF0YT10aGlzLl90cmFuc2Zvcm18fFBlKHQscywhMCksbi5wb3AoKX1pZihpKXtmb3IoO187KXtmb3IoZz1fLl9uZXh0LGM9ZDtjJiZjLnByPl8ucHI7KWM9Yy5fbmV4dDsoXy5fcHJldj1jP2MuX3ByZXY6bSk/Xy5fcHJldi5fbmV4dD1fOmQ9XywoXy5fbmV4dD1jKT9jLl9wcmV2PV86bT1fLF89Z310aGlzLl9maXJzdFBUPWR9cmV0dXJuITB9LGwucGFyc2U9ZnVuY3Rpb24odCxlLGksbil7dmFyIGEsbCx1LGYsXyxwLGMsZCxtLGcsdj10LnN0eWxlO2ZvcihhIGluIGUpcD1lW2FdLGw9b1thXSxsP2k9bC5wYXJzZSh0LHAsYSx0aGlzLGksbixlKTooXz1xKHQsYSxzKStcIlwiLG09XCJzdHJpbmdcIj09dHlwZW9mIHAsXCJjb2xvclwiPT09YXx8XCJmaWxsXCI9PT1hfHxcInN0cm9rZVwiPT09YXx8LTEhPT1hLmluZGV4T2YoXCJDb2xvclwiKXx8bSYmYi50ZXN0KHApPyhtfHwocD1vZShwKSxwPShwLmxlbmd0aD4zP1wicmdiYShcIjpcInJnYihcIikrcC5qb2luKFwiLFwiKStcIilcIiksaT1wZSh2LGEsXyxwLCEwLFwidHJhbnNwYXJlbnRcIixpLDAsbikpOiFtfHwtMT09PXAuaW5kZXhPZihcIiBcIikmJi0xPT09cC5pbmRleE9mKFwiLFwiKT8odT1wYXJzZUZsb2F0KF8pLGM9dXx8MD09PXU/Xy5zdWJzdHIoKHUrXCJcIikubGVuZ3RoKTpcIlwiLChcIlwiPT09X3x8XCJhdXRvXCI9PT1fKSYmKFwid2lkdGhcIj09PWF8fFwiaGVpZ2h0XCI9PT1hPyh1PXRlKHQsYSxzKSxjPVwicHhcIik6XCJsZWZ0XCI9PT1hfHxcInRvcFwiPT09YT8odT1aKHQsYSxzKSxjPVwicHhcIik6KHU9XCJvcGFjaXR5XCIhPT1hPzA6MSxjPVwiXCIpKSxnPW0mJlwiPVwiPT09cC5jaGFyQXQoMSksZz8oZj1wYXJzZUludChwLmNoYXJBdCgwKStcIjFcIiwxMCkscD1wLnN1YnN0cigyKSxmKj1wYXJzZUZsb2F0KHApLGQ9cC5yZXBsYWNlKHksXCJcIikpOihmPXBhcnNlRmxvYXQocCksZD1tP3Auc3Vic3RyKChmK1wiXCIpLmxlbmd0aCl8fFwiXCI6XCJcIiksXCJcIj09PWQmJihkPWEgaW4gcj9yW2FdOmMpLHA9Znx8MD09PWY/KGc/Zit1OmYpK2Q6ZVthXSxjIT09ZCYmXCJcIiE9PWQmJihmfHwwPT09ZikmJnUmJih1PVEodCxhLHUsYyksXCIlXCI9PT1kPyh1Lz1RKHQsYSwxMDAsXCIlXCIpLzEwMCxlLnN0cmljdFVuaXRzIT09ITAmJihfPXUrXCIlXCIpKTpcImVtXCI9PT1kP3UvPVEodCxhLDEsXCJlbVwiKTpcInB4XCIhPT1kJiYoZj1RKHQsYSxmLGQpLGQ9XCJweFwiKSxnJiYoZnx8MD09PWYpJiYocD1mK3UrZCkpLGcmJihmKz11KSwhdSYmMCE9PXV8fCFmJiYwIT09Zj92b2lkIDAhPT12W2FdJiYocHx8XCJOYU5cIiE9cCtcIlwiJiZudWxsIT1wKT8oaT1uZXcgX2UodixhLGZ8fHV8fDAsMCxpLC0xLGEsITEsMCxfLHApLGkueHMwPVwibm9uZVwiIT09cHx8XCJkaXNwbGF5XCIhPT1hJiYtMT09PWEuaW5kZXhPZihcIlN0eWxlXCIpP3A6Xyk6VShcImludmFsaWQgXCIrYStcIiB0d2VlbiB2YWx1ZTogXCIrZVthXSk6KGk9bmV3IF9lKHYsYSx1LGYtdSxpLDAsYSxoIT09ITEmJihcInB4XCI9PT1kfHxcInpJbmRleFwiPT09YSksMCxfLHApLGkueHMwPWQpKTppPXBlKHYsYSxfLHAsITAsbnVsbCxpLDAsbikpLG4mJmkmJiFpLnBsdWdpbiYmKGkucGx1Z2luPW4pO3JldHVybiBpfSxsLnNldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzPXRoaXMuX2ZpcnN0UFQsbj0xZS02O2lmKDEhPT10fHx0aGlzLl90d2Vlbi5fdGltZSE9PXRoaXMuX3R3ZWVuLl9kdXJhdGlvbiYmMCE9PXRoaXMuX3R3ZWVuLl90aW1lKWlmKHR8fHRoaXMuX3R3ZWVuLl90aW1lIT09dGhpcy5fdHdlZW4uX2R1cmF0aW9uJiYwIT09dGhpcy5fdHdlZW4uX3RpbWV8fHRoaXMuX3R3ZWVuLl9yYXdQcmV2VGltZT09PS0xZS02KWZvcig7czspe2lmKGU9cy5jKnQrcy5zLHMucj9lPU1hdGgucm91bmQoZSk6bj5lJiZlPi1uJiYoZT0wKSxzLnR5cGUpaWYoMT09PXMudHlwZSlpZihyPXMubCwyPT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyO2Vsc2UgaWYoMz09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMztlbHNlIGlmKDQ9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czMrcy54bjMrcy54czQ7ZWxzZSBpZig1PT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzK3MueG4zK3MueHM0K3MueG40K3MueHM1O2Vsc2V7Zm9yKGk9cy54czArZStzLnhzMSxyPTE7cy5sPnI7cisrKWkrPXNbXCJ4blwiK3JdK3NbXCJ4c1wiKyhyKzEpXTtzLnRbcy5wXT1pfWVsc2UtMT09PXMudHlwZT9zLnRbcy5wXT1zLnhzMDpzLnNldFJhdGlvJiZzLnNldFJhdGlvKHQpO2Vsc2Ugcy50W3MucF09ZStzLnhzMDtzPXMuX25leHR9ZWxzZSBmb3IoO3M7KTIhPT1zLnR5cGU/cy50W3MucF09cy5iOnMuc2V0UmF0aW8odCkscz1zLl9uZXh0O2Vsc2UgZm9yKDtzOykyIT09cy50eXBlP3MudFtzLnBdPXMuZTpzLnNldFJhdGlvKHQpLHM9cy5fbmV4dH0sbC5fZW5hYmxlVHJhbnNmb3Jtcz1mdW5jdGlvbih0KXt0aGlzLl90cmFuc2Zvcm1UeXBlPXR8fDM9PT10aGlzLl90cmFuc2Zvcm1UeXBlPzM6Mix0aGlzLl90cmFuc2Zvcm09dGhpcy5fdHJhbnNmb3JtfHxQZSh0aGlzLl90YXJnZXQscywhMCl9O3ZhciBNZT1mdW5jdGlvbigpe3RoaXMudFt0aGlzLnBdPXRoaXMuZSx0aGlzLmRhdGEuX2xpbmtDU1NQKHRoaXMsdGhpcy5fbmV4dCxudWxsLCEwKX07bC5fYWRkTGF6eVNldD1mdW5jdGlvbih0LGUsaSl7dmFyIHI9dGhpcy5fZmlyc3RQVD1uZXcgX2UodCxlLDAsMCx0aGlzLl9maXJzdFBULDIpO3IuZT1pLHIuc2V0UmF0aW89TWUsci5kYXRhPXRoaXN9LGwuX2xpbmtDU1NQPWZ1bmN0aW9uKHQsZSxpLHIpe3JldHVybiB0JiYoZSYmKGUuX3ByZXY9dCksdC5fbmV4dCYmKHQuX25leHQuX3ByZXY9dC5fcHJldiksdC5fcHJldj90Ll9wcmV2Ll9uZXh0PXQuX25leHQ6dGhpcy5fZmlyc3RQVD09PXQmJih0aGlzLl9maXJzdFBUPXQuX25leHQscj0hMCksaT9pLl9uZXh0PXQ6cnx8bnVsbCE9PXRoaXMuX2ZpcnN0UFR8fCh0aGlzLl9maXJzdFBUPXQpLHQuX25leHQ9ZSx0Ll9wcmV2PWkpLHR9LGwuX2tpbGw9ZnVuY3Rpb24oZSl7dmFyIGkscixzLG49ZTtpZihlLmF1dG9BbHBoYXx8ZS5hbHBoYSl7bj17fTtmb3IociBpbiBlKW5bcl09ZVtyXTtuLm9wYWNpdHk9MSxuLmF1dG9BbHBoYSYmKG4udmlzaWJpbGl0eT0xKX1yZXR1cm4gZS5jbGFzc05hbWUmJihpPXRoaXMuX2NsYXNzTmFtZVBUKSYmKHM9aS54Zmlyc3QscyYmcy5fcHJldj90aGlzLl9saW5rQ1NTUChzLl9wcmV2LGkuX25leHQscy5fcHJldi5fcHJldik6cz09PXRoaXMuX2ZpcnN0UFQmJih0aGlzLl9maXJzdFBUPWkuX25leHQpLGkuX25leHQmJnRoaXMuX2xpbmtDU1NQKGkuX25leHQsaS5fbmV4dC5fbmV4dCxzLl9wcmV2KSx0aGlzLl9jbGFzc05hbWVQVD1udWxsKSx0LnByb3RvdHlwZS5fa2lsbC5jYWxsKHRoaXMsbil9O3ZhciBMZT1mdW5jdGlvbih0LGUsaSl7dmFyIHIscyxuLGE7aWYodC5zbGljZSlmb3Iocz10Lmxlbmd0aDstLXM+LTE7KUxlKHRbc10sZSxpKTtlbHNlIGZvcihyPXQuY2hpbGROb2RlcyxzPXIubGVuZ3RoOy0tcz4tMTspbj1yW3NdLGE9bi50eXBlLG4uc3R5bGUmJihlLnB1c2goJChuKSksaSYmaS5wdXNoKG4pKSwxIT09YSYmOSE9PWEmJjExIT09YXx8IW4uY2hpbGROb2Rlcy5sZW5ndGh8fExlKG4sZSxpKX07cmV0dXJuIGEuY2FzY2FkZVRvPWZ1bmN0aW9uKHQsaSxyKXt2YXIgcyxuLGEsbz1lLnRvKHQsaSxyKSxsPVtvXSxoPVtdLHU9W10sZj1bXSxfPWUuX2ludGVybmFscy5yZXNlcnZlZFByb3BzO2Zvcih0PW8uX3RhcmdldHN8fG8udGFyZ2V0LExlKHQsaCxmKSxvLnJlbmRlcihpLCEwKSxMZSh0LHUpLG8ucmVuZGVyKDAsITApLG8uX2VuYWJsZWQoITApLHM9Zi5sZW5ndGg7LS1zPi0xOylpZihuPUcoZltzXSxoW3NdLHVbc10pLG4uZmlyc3RNUFQpe249bi5kaWZzO1xyXG5mb3IoYSBpbiByKV9bYV0mJihuW2FdPXJbYV0pO2wucHVzaChlLnRvKGZbc10saSxuKSl9cmV0dXJuIGx9LHQuYWN0aXZhdGUoW2FdKSxhfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS43LjNcclxuICogREFURTogMjAxNC0wMS0xNFxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICoqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3ZhciB0PWRvY3VtZW50LmRvY3VtZW50RWxlbWVudCxlPXdpbmRvdyxpPWZ1bmN0aW9uKGkscyl7dmFyIHI9XCJ4XCI9PT1zP1wiV2lkdGhcIjpcIkhlaWdodFwiLG49XCJzY3JvbGxcIityLGE9XCJjbGllbnRcIityLG89ZG9jdW1lbnQuYm9keTtyZXR1cm4gaT09PWV8fGk9PT10fHxpPT09bz9NYXRoLm1heCh0W25dLG9bbl0pLShlW1wiaW5uZXJcIityXXx8TWF0aC5tYXgodFthXSxvW2FdKSk6aVtuXS1pW1wib2Zmc2V0XCIrcl19LHM9d2luZG93Ll9nc0RlZmluZS5wbHVnaW4oe3Byb3BOYW1lOlwic2Nyb2xsVG9cIixBUEk6Mix2ZXJzaW9uOlwiMS43LjNcIixpbml0OmZ1bmN0aW9uKHQscyxyKXtyZXR1cm4gdGhpcy5fd2R3PXQ9PT1lLHRoaXMuX3RhcmdldD10LHRoaXMuX3R3ZWVuPXIsXCJvYmplY3RcIiE9dHlwZW9mIHMmJihzPXt5OnN9KSx0aGlzLl9hdXRvS2lsbD1zLmF1dG9LaWxsIT09ITEsdGhpcy54PXRoaXMueFByZXY9dGhpcy5nZXRYKCksdGhpcy55PXRoaXMueVByZXY9dGhpcy5nZXRZKCksbnVsbCE9cy54Pyh0aGlzLl9hZGRUd2Vlbih0aGlzLFwieFwiLHRoaXMueCxcIm1heFwiPT09cy54P2kodCxcInhcIik6cy54LFwic2Nyb2xsVG9feFwiLCEwKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5wdXNoKFwic2Nyb2xsVG9feFwiKSk6dGhpcy5za2lwWD0hMCxudWxsIT1zLnk/KHRoaXMuX2FkZFR3ZWVuKHRoaXMsXCJ5XCIsdGhpcy55LFwibWF4XCI9PT1zLnk/aSh0LFwieVwiKTpzLnksXCJzY3JvbGxUb195XCIsITApLHRoaXMuX292ZXJ3cml0ZVByb3BzLnB1c2goXCJzY3JvbGxUb195XCIpKTp0aGlzLnNraXBZPSEwLCEwfSxzZXQ6ZnVuY3Rpb24odCl7dGhpcy5fc3VwZXIuc2V0UmF0aW8uY2FsbCh0aGlzLHQpO3ZhciBzPXRoaXMuX3dkd3x8IXRoaXMuc2tpcFg/dGhpcy5nZXRYKCk6dGhpcy54UHJldixyPXRoaXMuX3dkd3x8IXRoaXMuc2tpcFk/dGhpcy5nZXRZKCk6dGhpcy55UHJldixuPXItdGhpcy55UHJldixhPXMtdGhpcy54UHJldjt0aGlzLl9hdXRvS2lsbCYmKCF0aGlzLnNraXBYJiYoYT43fHwtNz5hKSYmaSh0aGlzLl90YXJnZXQsXCJ4XCIpPnMmJih0aGlzLnNraXBYPSEwKSwhdGhpcy5za2lwWSYmKG4+N3x8LTc+bikmJmkodGhpcy5fdGFyZ2V0LFwieVwiKT5yJiYodGhpcy5za2lwWT0hMCksdGhpcy5za2lwWCYmdGhpcy5za2lwWSYmdGhpcy5fdHdlZW4ua2lsbCgpKSx0aGlzLl93ZHc/ZS5zY3JvbGxUbyh0aGlzLnNraXBYP3M6dGhpcy54LHRoaXMuc2tpcFk/cjp0aGlzLnkpOih0aGlzLnNraXBZfHwodGhpcy5fdGFyZ2V0LnNjcm9sbFRvcD10aGlzLnkpLHRoaXMuc2tpcFh8fCh0aGlzLl90YXJnZXQuc2Nyb2xsTGVmdD10aGlzLngpKSx0aGlzLnhQcmV2PXRoaXMueCx0aGlzLnlQcmV2PXRoaXMueX19KSxyPXMucHJvdG90eXBlO3MubWF4PWksci5nZXRYPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3dkdz9udWxsIT1lLnBhZ2VYT2Zmc2V0P2UucGFnZVhPZmZzZXQ6bnVsbCE9dC5zY3JvbGxMZWZ0P3Quc2Nyb2xsTGVmdDpkb2N1bWVudC5ib2R5LnNjcm9sbExlZnQ6dGhpcy5fdGFyZ2V0LnNjcm9sbExlZnR9LHIuZ2V0WT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl93ZHc/bnVsbCE9ZS5wYWdlWU9mZnNldD9lLnBhZ2VZT2Zmc2V0Om51bGwhPXQuc2Nyb2xsVG9wP3Quc2Nyb2xsVG9wOmRvY3VtZW50LmJvZHkuc2Nyb2xsVG9wOnRoaXMuX3RhcmdldC5zY3JvbGxUb3B9LHIuX2tpbGw9ZnVuY3Rpb24odCl7cmV0dXJuIHQuc2Nyb2xsVG9feCYmKHRoaXMuc2tpcFg9ITApLHQuc2Nyb2xsVG9feSYmKHRoaXMuc2tpcFk9ITApLHRoaXMuX3N1cGVyLl9raWxsLmNhbGwodGhpcyx0KX19KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiXX0=
