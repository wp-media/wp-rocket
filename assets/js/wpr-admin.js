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
        'path': rocket_mixpanel_data.path
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
  document.addEventListener('DOMContentLoaded', () => {
    initCdnDriverTabs();
    initCdnPauseToggle();
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

  /**
   * Updates the RocketCDN CTA visibility and expansion state.
   *
   * @param {number} count Current number of free-tier pages.
   * @param {number} limit Free-tier page limit.
   * @returns {void}
   */
  function updateRocketCtaState(count, limit) {
    const cta = document.getElementById('wpr-rocketcdn-cta');
    if (!cta) {
      return;
    }
    const isVisible = count > 0;
    const isExpanded = count >= limit;
    cta.classList.toggle('wpr-isHidden', !isVisible);
    cta.classList.toggle('wpr-rocketcdn-cta--collapsed', isVisible && !isExpanded);
    cta.classList.toggle('wpr-rocketcdn-cta--expanded', isVisible && isExpanded);
    cta.classList.toggle('wpr-rocketcdn-cta---max-limit', isVisible && isExpanded);
  }

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

    // Create polling mechanism to send a request every 10 seconds to get the subscription status and once the subscription is active, we will refresh the page for now.
    document.dispatchEvent(new CustomEvent('rocketCDNSubscriptionLoading', {}));
  }

  /**
   * Initializes CDN driver tab switching behavior.
   *
   * Toggles visibility of CDN driver sections (built-in-cdn / your-own-cdn)
   * based on which tab is clicked.
   */
  function initCdnDriverTabs() {
    const tabs = document.querySelectorAll('.wpr-cdn-tabs__tab');
    const driverSections = document.querySelectorAll('.rocketcdn, .your-own-cdn');
    if (!tabs.length) {
      return;
    }

    /**
     * Toggles visibility of CDN driver sections using the hidden utility class.
     *
     * @param {string} activeDriver Active CDN driver slug.
     */
    function toggleDriverSections(activeDriver) {
      driverSections.forEach(section => {
        section.classList.toggle('wpr-isHidden', !section.classList.contains(activeDriver));
      });
    }

    /**
     * Updates all .rocketcdn-driver-js spans to reflect the active driver label.
     * The label is read from the active tab's data-title attribute, preserving
     * the original capitalisation set by the PHP translation.
     *
     * @param {HTMLElement} activeTab The currently active tab element.
     */
    function updateDriverLabel(activeTab) {
      const label = activeTab.getAttribute('data-title');
      if (!label) {
        return;
      }
      document.querySelectorAll('.rocketcdn-driver-js').forEach(span => {
        // Preserve the original text-transform (uppercase spans stay uppercase via CSS).
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
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const driver = tab.getAttribute('data-cdn-driver');
        if (!driver) {
          return;
        }

        // Update active tab.
        tabs.forEach(t => t.classList.remove('wpr-cdn-tabs__tab--active'));
        tab.classList.add('wpr-cdn-tabs__tab--active');

        // Toggle sections: show matching driver, hide others.
        toggleDriverSections(driver);

        // Update dynamic driver label spans.
        updateDriverLabel(tab);
        updateExcludeCdnHelpUrl(driver);
        notifyCdnStateChange();

        // Initial value of the hidden input is set on page load by PHP based on the active driver.
        const cdnTypeInput = document.getElementById('cdn_type');
        let currentValue = cdnTypeInput.value;

        // Persist the active driver selection.
        const driverValue = 'your-own-cdn' === driver ? 'byocdn' : 'rocketcdn';
        window.wp.apiFetch({
          path: '/wp-rocket/v1/rocketcdn/driver',
          method: 'POST',
          data: {
            driver: driverValue
          }
        }).then(response => {
          // Updated hidden input value on success.
          cdnTypeInput.value = driverValue;

          // Update the state of RocketCDN specific elements based on the selected driver and response from the server.
          updateRocketCDNElementsState(driverValue, response.disable_rocket_cdn_elements);
        }).catch(() => {
          // Revert active tab and sections on failure.
          cdnTypeInput.value = currentValue;
        });
      });
    });

    // Set initial state from active tab, fallback to rocketcdn.
    const activeTab = document.querySelector('.wpr-cdn-tabs__tab--active');
    const activeDriver = activeTab ? activeTab.getAttribute('data-cdn-driver') : 'rocketcdn';
    if (activeDriver) {
      toggleDriverSections(activeDriver);
      notifyCdnStateChange();
    }

    // Set initial label from the active tab.
    if (activeTab) {
      updateDriverLabel(activeTab);
      updateExcludeCdnHelpUrl(activeDriver);
    }
  }

  /**
   * Initializes the CDN pause/resume toggle buttons.
   *
   * Toggles between "PAUSE CDN" and "RESUME CDN" states,
   * swapping the icon via a CSS modifier class.
   */
  function initCdnPauseToggle() {
    document.addEventListener('click', event => {
      const button = event.target.closest('.wpr-cdn-pause');
      if (!button) {
        return;
      }
      const isPaused = button.classList.toggle('wpr-cdn-pause--paused');
      button.setAttribute('aria-pressed', isPaused ? 'true' : 'false');
      button.disabled = true;
      const statusDot = document.querySelector('.rocketcdn .wpr-cdn-indicator__dot');
      if (statusDot) {
        statusDot.className = 'wpr-icon-orange-loader';
      }
      window.wp.apiFetch({
        path: '/wp-rocket/v1/rocketcdn/pause',
        method: 'POST',
        data: {
          paused: isPaused ? 0 : 1
        }
      }).then(() => {
        // Remove the loader.
        if (statusDot) {
          statusDot.className = 'wpr-cdn-indicator__dot';
        }
        button.disabled = false;

        // Simulate real click to prepare checkbox state for form submission.
        document.querySelector('label[for="cdn"]').click();
        updateRocketCDNElementsState('rocketcdn', isPaused);
        const statusContainer = button.closest('.wpr-cdn-status');
        if (!statusContainer) {
          return;
        }
        statusContainer.classList.toggle('wpr-cdn-status--paused', isPaused);
        statusContainer.classList.toggle('wpr-cdn-status--long-details', isPaused && '1' === statusContainer.dataset.longDetails);
        const builtIn = statusContainer.closest('.wpr-cdn-built-in');
        if (builtIn) {
          builtIn.classList.toggle('wpr-cdn-built-in--paused', isPaused);
        }
        notifyCdnStateChange();
        const textKey = isPaused ? 'pausedText' : 'activeText';
        const statusText = statusContainer.querySelector('.wpr-cdn-indicator__text');
        if (statusText && statusContainer.dataset[textKey]) {
          statusText.textContent = statusContainer.dataset[textKey];
        }
        const detailsKey = isPaused ? 'pausedDetails' : 'activeDetails';
        const detailsEl = statusContainer.querySelector('.wpr-cdn-indicator__details');
        if (detailsEl && statusContainer.dataset[detailsKey]) {
          detailsEl.textContent = statusContainer.dataset[detailsKey];
        }
      }).catch(() => {
        // Revert toggle on failure.
        button.classList.toggle('wpr-cdn-pause--paused', !isPaused);
        button.setAttribute('aria-pressed', !isPaused ? 'true' : 'false');
        button.disabled = false;

        // Remove the loader.
        if (statusDot) {
          statusDot.className = 'wpr-cdn-indicator__dot';
        }
      });
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
      window.wp.apiFetch({
        path: '/wp-rocket/v1/rocketcdn/pages/homepage',
        method: 'POST'
      }).then(response => {
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

        // Set subscription loading state when first page is added.
        if (response.is_subscription_creation_loading) {
          setSubscriptionLoadingState();
        }

        // Update status indicator component.
        updateStatusIndicatorComponent(response.status_indicator_html);
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
      window.wp.apiFetch({
        path: '/wp-rocket/v1/rocketcdn/pages',
        method: 'POST',
        data: {
          url
        }
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

        // Set subscription loading state when first page is added.
        if (response.is_subscription_creation_loading) {
          setSubscriptionLoadingState();
        }

        // Update status indicator component.
        updateStatusIndicatorComponent(response.status_indicator_html);
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

        // Show re-add HOMEPAGE button when all pages are deleted.
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
        if (0 === response.count) {
          // Update status inidicator component
          updateStatusIndicatorComponent(response.status_indicator_html);
        }
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
          'path': rocket_mixpanel_data.path
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
      'path': rocket_mixpanel_data.path
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
      cdn_mode: cdnMode
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jZG4tZHJpdmVyLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvbWl4cGFuZWwuanMiLCJzcmMvanMvZ2xvYmFsL3BhZ2VNYW5hZ2VyLmpzIiwic3JjL2pzL2dsb2JhbC9yZWNvbW1lbmRhdGlvbnMtd2lkZ2V0LmpzIiwic3JjL2pzL2dsb2JhbC9yb2NrZXRjZG4tc3Vic2NyaXB0aW9uLXBvbGxpbmcuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldGNkbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxJQUFJO0VBQ25FLE1BQU0sU0FBUyxHQUFHLFlBQVksQ0FBQyxhQUFhLENBQUMsZ0JBQWdCLENBQUM7RUFDOUQsTUFBTSxhQUFhLEdBQUcsWUFBWSxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQztFQUNuRSxNQUFNLE9BQU8sR0FBRyxTQUFBLENBQVMsR0FBRyxFQUFFO0lBQzdCLE1BQU0saUJBQWlCLEdBQUcsSUFBSSxXQUFXLENBQUMsc0JBQXNCLEVBQUU7TUFDakUsTUFBTSxFQUFFO1FBQ1AsY0FBYyxFQUFFO01BQ2pCO0lBQ0QsQ0FBQyxDQUFDO0lBQ0YsYUFBYSxDQUFDLFdBQVcsR0FBRyxHQUFHLENBQUMsV0FBVztJQUMzQyxZQUFZLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUM7SUFDdkMsWUFBWSxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQztFQUU5QyxDQUFDO0VBQ0QsU0FBUyxDQUFDLGdCQUFnQixDQUFDLE9BQU8sRUFBRSxNQUFNO0lBQ3pDLFlBQVksQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQztJQUN2QyxTQUFTLENBQUMsWUFBWSxDQUNyQixlQUFlLEVBQ2YsU0FBUyxDQUFDLFlBQVksQ0FBQyxlQUFlLENBQUMsS0FBSyxNQUFNLEdBQUcsT0FBTyxHQUFHLE1BQ2hFLENBQUM7RUFDRixDQUFDLENBQUM7RUFFRixZQUFZLENBQUMsZ0JBQWdCLENBQUMsT0FBTyxFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQ2xELElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLEVBQUU7TUFFOUIsTUFBTSxRQUFRLEdBQUcsWUFBWSxDQUFDLGdCQUFnQixDQUFDLElBQUksQ0FBQztNQUNwRCxRQUFRLENBQUMsT0FBTyxDQUFDLElBQUksSUFBSSxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsQ0FBQztNQUN6RCxNQUFNLFdBQVcsR0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUM7TUFFMUMsSUFBSSxXQUFXLEVBQUU7UUFDaEIsV0FBVyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsUUFBUSxDQUFDO1FBQ25DLE9BQU8sQ0FBQyxXQUFXLENBQUM7TUFDckI7SUFDRDtFQUNELENBQUMsQ0FBQztFQUNGLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyxPQUFPLEVBQUcsQ0FBQyxJQUFLO0lBQ3pDLElBQUksQ0FBQyxZQUFZLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBRTtNQUNyQyxZQUFZLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUM7TUFDdkMsU0FBUyxDQUFDLFlBQVksQ0FBQyxlQUFlLEVBQUUsT0FBTyxDQUFDO0lBQ2pEO0VBQ0QsQ0FBQyxDQUFDO0FBQ0gsQ0FBQyxDQUFDOzs7OztBQ3pDRixJQUFJLENBQUMsR0FBRyxNQUFNO0FBQ2QsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUssQ0FBQyxZQUFVO0VBQ3hCO0FBQ0o7QUFDQTtFQUNJLElBQUksYUFBYSxHQUFHLEtBQUs7RUFDekIsQ0FBQyxDQUFDLDZCQUE2QixDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxVQUFTLENBQUMsRUFBRTtJQUNyRCxJQUFHLENBQUMsYUFBYSxFQUFDO01BQ2QsSUFBSSxNQUFNLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQztNQUNwQixJQUFJLE9BQU8sR0FBRyxDQUFDLENBQUMsbUJBQW1CLENBQUM7TUFDcEMsSUFBSSxNQUFNLEdBQUcsQ0FBQyxDQUFDLHNCQUFzQixDQUFDO01BRXRDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztNQUNsQixhQUFhLEdBQUcsSUFBSTtNQUNwQixNQUFNLENBQUMsT0FBTyxDQUFFLE1BQU8sQ0FBQzs7TUFFakM7TUFDUyxNQUFNLENBQUMsV0FBVyxDQUFDLDJCQUEyQixDQUFDO01BRS9DLENBQUMsQ0FBQyxJQUFJLENBQ0YsT0FBTyxFQUNQO1FBQ0ksTUFBTSxFQUFFLDhCQUE4QjtRQUN0QyxXQUFXLEVBQUUsZ0JBQWdCLENBQUM7TUFDbEMsQ0FBQyxFQUNELFVBQVMsUUFBUSxFQUFFO1FBQ2YsTUFBTSxDQUFDLFdBQVcsQ0FBQyxlQUFlLENBQUM7UUFDbkMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxjQUFjLENBQUM7UUFFL0IsSUFBSyxJQUFJLEtBQUssUUFBUSxDQUFDLE9BQU8sRUFBRztVQUM3QixPQUFPLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDO1VBQ3hDLE1BQU0sQ0FBQyxRQUFRLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxrQkFBa0IsQ0FBQztVQUNuRixVQUFVLENBQUMsWUFBVztZQUNsQixNQUFNLENBQUMsV0FBVyxDQUFDLCtCQUErQixDQUFDO1lBQ25ELE1BQU0sQ0FBQyxRQUFRLENBQUMsZ0JBQWdCLENBQUM7VUFDckMsQ0FBQyxFQUFFLEdBQUcsQ0FBQztRQUNYLENBQUMsTUFDRztVQUNBLFVBQVUsQ0FBQyxZQUFXO1lBQ2xCLE1BQU0sQ0FBQyxXQUFXLENBQUMsK0JBQStCLENBQUM7WUFDbkQsTUFBTSxDQUFDLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQztVQUNyQyxDQUFDLEVBQUUsR0FBRyxDQUFDO1FBQ1g7UUFFQSxVQUFVLENBQUMsWUFBVztVQUNsQixJQUFJLEdBQUcsR0FBRyxJQUFJLFlBQVksQ0FBQztZQUFDLFVBQVUsRUFBQyxTQUFBLENBQUEsRUFBVTtjQUM3QyxhQUFhLEdBQUcsS0FBSztZQUN6QjtVQUFDLENBQUMsQ0FBQyxDQUNBLEdBQUcsQ0FBQyxNQUFNLEVBQUU7WUFBQyxHQUFHLEVBQUM7Y0FBQyxTQUFTLEVBQUM7WUFBZ0I7VUFBQyxDQUFDLENBQUMsQ0FDL0MsR0FBRyxDQUFDLE1BQU0sRUFBRTtZQUFDLEdBQUcsRUFBQztjQUFDLFNBQVMsRUFBQztZQUFrQjtVQUFDLENBQUMsRUFBRSxJQUFJLENBQUMsQ0FDdkQsR0FBRyxDQUFDLE1BQU0sRUFBRTtZQUFDLEdBQUcsRUFBQztjQUFDLFNBQVMsRUFBQztZQUFrQjtVQUFDLENBQUMsQ0FBQyxDQUNqRCxHQUFHLENBQUMsTUFBTSxFQUFFO1lBQUMsR0FBRyxFQUFDO2NBQUMsU0FBUyxFQUFDO1lBQW9CO1VBQUMsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUN6RCxHQUFHLENBQUMsTUFBTSxFQUFFO1lBQUMsR0FBRyxFQUFDO2NBQUMsU0FBUyxFQUFDO1lBQWdCO1VBQUMsQ0FBQyxDQUFDO1FBRXRELENBQUMsRUFBRSxJQUFJLENBQUM7TUFDWixDQUNKLENBQUM7SUFDTDtJQUNBLE9BQU8sS0FBSztFQUNoQixDQUFDLENBQUM7O0VBRUY7QUFDSjtBQUNBO0VBQ0ksQ0FBQyxDQUFDLGlDQUFpQyxDQUFDLENBQUMsRUFBRSxDQUFDLFFBQVEsRUFBRSxVQUFTLENBQUMsRUFBRTtJQUMxRCxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7SUFDbEIsSUFBSSxJQUFJLEdBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7SUFDOUIsSUFBSSxLQUFLLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLEdBQUcsQ0FBQztJQUVqRCxJQUFJLFFBQVEsR0FBRyxDQUFFLDBCQUEwQixFQUFFLG9CQUFvQixFQUFFLG1CQUFtQixDQUFFO0lBQ3hGLElBQUssUUFBUSxDQUFDLE9BQU8sQ0FBRSxJQUFLLENBQUMsSUFBSSxDQUFDLEVBQUc7TUFDcEM7SUFDRDtJQUVNLENBQUMsQ0FBQyxJQUFJLENBQ0YsT0FBTyxFQUNQO01BQ0ksTUFBTSxFQUFFLHNCQUFzQjtNQUM5QixXQUFXLEVBQUUsZ0JBQWdCLENBQUMsS0FBSztNQUNuQyxNQUFNLEVBQUU7UUFDSixJQUFJLEVBQUUsSUFBSTtRQUNWLEtBQUssRUFBRTtNQUNYO0lBQ0osQ0FBQyxFQUNELFVBQVMsUUFBUSxFQUFFLENBQUMsQ0FDeEIsQ0FBQztFQUNSLENBQUMsQ0FBQzs7RUFFRjtBQUNEO0FBQ0E7RUFDSSxDQUFDLENBQUMsd0NBQXdDLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQ2hFLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUV4QixDQUFDLENBQUMsd0NBQXdDLENBQUMsQ0FBQyxRQUFRLENBQUMsZUFBZSxDQUFDO0lBRS9ELENBQUMsQ0FBQyxJQUFJLENBQ0YsT0FBTyxFQUNQO01BQ0ksTUFBTSxFQUFFLDRCQUE0QjtNQUNwQyxXQUFXLEVBQUUsZ0JBQWdCLENBQUM7SUFDbEMsQ0FBQyxFQUNWLFVBQVMsUUFBUSxFQUFFO01BQ2xCLElBQUssUUFBUSxDQUFDLE9BQU8sRUFBRztRQUN2QjtRQUNBLENBQUMsQ0FBQyx3Q0FBd0MsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQ2xELENBQUMsQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQzlCLENBQUMsQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQzlCLENBQUMsQ0FBQyx3Q0FBd0MsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxlQUFlLENBQUM7TUFDekU7SUFDRCxDQUNLLENBQUM7RUFDTCxDQUFDLENBQUM7O0VBRUY7QUFDSjtBQUNBO0VBQ0ksQ0FBQyxDQUFDLHdDQUF3QyxDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxVQUFTLENBQUMsRUFBRTtJQUNoRSxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7SUFFeEIsQ0FBQyxDQUFDLHdDQUF3QyxDQUFDLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQztJQUUvRCxDQUFDLENBQUMsSUFBSSxDQUNGLE9BQU8sRUFDUDtNQUNJLE1BQU0sRUFBRSw0QkFBNEI7TUFDcEMsV0FBVyxFQUFFLGdCQUFnQixDQUFDO0lBQ2xDLENBQUMsRUFDVixVQUFTLFFBQVEsRUFBRTtNQUNsQixJQUFLLFFBQVEsQ0FBQyxPQUFPLEVBQUc7UUFDdkI7UUFDQSxDQUFDLENBQUMsd0NBQXdDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNsRCxDQUFDLENBQUMsb0JBQW9CLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUM5QixDQUFDLENBQUMsb0JBQW9CLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNmLENBQUMsQ0FBQyx3Q0FBd0MsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxlQUFlLENBQUM7UUFDeEUsQ0FBQyxDQUFDLHNCQUFzQixDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztNQUNoRDtJQUNELENBQ0ssQ0FBQztFQUNMLENBQUMsQ0FBQztFQUVGLENBQUMsQ0FBRSwyQkFBNEIsQ0FBQyxDQUFDLEVBQUUsQ0FBRSxPQUFPLEVBQUUsVUFBVSxDQUFDLEVBQUc7SUFDeEQsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO0lBRWxCLENBQUMsQ0FBQyxJQUFJLENBQ0YsT0FBTyxFQUNQO01BQ0ksTUFBTSxFQUFFLHNCQUFzQjtNQUM5QixLQUFLLEVBQUUsZ0JBQWdCLENBQUM7SUFDNUIsQ0FBQyxFQUNWLFVBQVMsUUFBUSxFQUFFO01BQ2xCLElBQUssUUFBUSxDQUFDLE9BQU8sRUFBRztRQUN2QixDQUFDLENBQUMsc0JBQXNCLENBQUMsQ0FBQyxJQUFJLENBQUUsTUFBTyxDQUFDO01BQ3pDO0lBQ0QsQ0FDSyxDQUFDO0VBQ0wsQ0FBRSxDQUFDO0VBRUgsQ0FBQyxDQUFFLHlCQUEwQixDQUFDLENBQUMsRUFBRSxDQUFFLE9BQU8sRUFBRSxVQUFVLENBQUMsRUFBRztJQUN0RCxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7SUFFbEIsQ0FBQyxDQUFDLElBQUksQ0FDRixPQUFPLEVBQ1A7TUFDSSxNQUFNLEVBQUUsd0JBQXdCO01BQ2hDLEtBQUssRUFBRSxnQkFBZ0IsQ0FBQztJQUM1QixDQUFDLEVBQ1YsVUFBUyxRQUFRLEVBQUU7TUFDbEIsSUFBSyxRQUFRLENBQUMsT0FBTyxFQUFHO1FBQ3ZCLENBQUMsQ0FBQyx3QkFBd0IsQ0FBQyxDQUFDLElBQUksQ0FBRSxNQUFPLENBQUM7TUFDM0M7SUFDRCxDQUNLLENBQUM7RUFDTCxDQUFFLENBQUM7RUFDTixDQUFDLENBQUUsNEJBQTZCLENBQUMsQ0FBQyxFQUFFLENBQUUsT0FBTyxFQUFFLFVBQVUsQ0FBQyxFQUFHO0lBQzVELENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUNsQixDQUFDLENBQUMsMkJBQTJCLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDO0lBQ3ZDLENBQUMsQ0FBQyxJQUFJLENBQUM7TUFDTixHQUFHLEVBQUUsZ0JBQWdCLENBQUMsUUFBUTtNQUM5QixVQUFVLEVBQUUsU0FBQSxDQUFXLEdBQUcsRUFBRztRQUM1QixHQUFHLENBQUMsZ0JBQWdCLENBQUUsWUFBWSxFQUFFLGdCQUFnQixDQUFDLFVBQVcsQ0FBQztRQUNqRSxHQUFHLENBQUMsZ0JBQWdCLENBQUUsUUFBUSxFQUFFLDZCQUE4QixDQUFDO1FBQy9ELEdBQUcsQ0FBQyxnQkFBZ0IsQ0FBRSxjQUFjLEVBQUUsa0JBQW1CLENBQUM7TUFDM0QsQ0FBQztNQUNELE1BQU0sRUFBRSxLQUFLO01BQ2IsT0FBTyxFQUFFLFNBQUEsQ0FBUyxTQUFTLEVBQUU7UUFDNUIsSUFBSSx1QkFBdUIsR0FBRyxDQUFDLENBQUMsMkJBQTJCLENBQUM7UUFDNUQsdUJBQXVCLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQztRQUNoQyxJQUFLLFNBQVMsS0FBSyxTQUFTLENBQUMsU0FBUyxDQUFDLEVBQUc7VUFDekMsdUJBQXVCLENBQUMsTUFBTSxDQUFFLG1DQUFtQyxHQUFHLFNBQVMsQ0FBQyxTQUFTLENBQUMsR0FBRyxRQUFTLENBQUM7VUFDdkc7UUFDRDtRQUNBLE1BQU0sQ0FBQyxJQUFJLENBQUUsU0FBVSxDQUFDLENBQUMsT0FBTyxDQUFHLFlBQVksSUFBTTtVQUNwRCx1QkFBdUIsQ0FBQyxNQUFNLENBQUUsVUFBVSxHQUFHLFlBQVksR0FBRyxhQUFjLENBQUM7VUFDM0UsdUJBQXVCLENBQUMsTUFBTSxDQUFFLFNBQVMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxTQUFTLENBQUUsQ0FBQztVQUNwRSx1QkFBdUIsQ0FBQyxNQUFNLENBQUUsTUFBTyxDQUFDO1FBQ3pDLENBQUMsQ0FBQztNQUNIO0lBQ0QsQ0FBQyxDQUFDO0VBQ0gsQ0FBRSxDQUFDOztFQUVBO0FBQ0o7QUFDQTtFQUNJLENBQUMsQ0FBQywwQkFBMEIsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsVUFBUyxDQUFDLEVBQUU7SUFDbEQsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO0lBRXhCLENBQUMsQ0FBQywwQkFBMEIsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxlQUFlLENBQUM7SUFFakQsQ0FBQyxDQUFDLElBQUksQ0FDRixPQUFPLEVBQ1A7TUFDSSxNQUFNLEVBQUUsNEJBQTRCO01BQ3BDLFdBQVcsRUFBRSxnQkFBZ0IsQ0FBQztJQUNsQyxDQUFDLEVBQ1YsVUFBUyxRQUFRLEVBQUU7TUFDbEIsSUFBSyxRQUFRLENBQUMsT0FBTyxFQUFHO1FBQ3ZCO1FBQ0EsQ0FBQyxDQUFDLDBCQUEwQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDcEMsQ0FBQyxDQUFDLDJCQUEyQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDckMsQ0FBQyxDQUFDLDRCQUE0QixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDdkIsQ0FBQyxDQUFDLDBCQUEwQixDQUFDLENBQUMsV0FBVyxDQUFDLGVBQWUsQ0FBQzs7UUFFMUQ7UUFDQSxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztRQUN6QixDQUFDLENBQUMsMEJBQTBCLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO01BQ3BEO0lBQ0QsQ0FDSyxDQUFDO0VBQ0wsQ0FBQyxDQUFDO0FBQ04sQ0FBQyxDQUFDO0FBRUYsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLFlBQVc7RUFDeEQsTUFBTSxpQkFBaUIsR0FBRyxRQUFRLENBQUMsY0FBYyxDQUFDLG1CQUFtQixDQUFDO0VBRXRFLElBQUksaUJBQWlCLEVBQUU7SUFDdEIsaUJBQWlCLENBQUMsZ0JBQWdCLENBQUMsUUFBUSxFQUFFLFlBQVc7TUFDdkQsTUFBTSxTQUFTLEdBQUcsSUFBSSxDQUFDLE9BQU87O01BRTlCO01BQ0EsSUFBSSxPQUFPLG9CQUFvQixLQUFLLFdBQVcsRUFBRTtRQUNoRCxvQkFBb0IsQ0FBQyxhQUFhLEdBQUcsU0FBUyxHQUFHLEdBQUcsR0FBRyxHQUFHO01BQzNEO01BRUEsS0FBSyxDQUFDLE9BQU8sRUFBRTtRQUNkLE1BQU0sRUFBRSxNQUFNO1FBQ2QsT0FBTyxFQUFFO1VBQ1IsY0FBYyxFQUFFO1FBQ2pCLENBQUM7UUFDRCxJQUFJLEVBQUUsSUFBSSxlQUFlLENBQUM7VUFDekIsTUFBTSxFQUFFLHFCQUFxQjtVQUM3QixLQUFLLEVBQUUsU0FBUyxHQUFHLENBQUMsR0FBRyxDQUFDO1VBQ3hCLFdBQVcsRUFBRSxnQkFBZ0IsQ0FBQztRQUMvQixDQUFDO01BQ0YsQ0FBQyxDQUFDO0lBQ0gsQ0FBQyxDQUFDO0VBQ0g7QUFDRCxDQUFDLENBQUM7QUFFRixRQUFRLENBQUMsZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUUsWUFBVztFQUN4RDtBQUNEO0FBQ0E7O0VBRUU7RUFDRCxNQUFNLGtCQUFrQixHQUFHLElBQUksQ0FBQyxDQUFHO0VBQ25DLE1BQU0saUJBQWlCLEdBQUcsSUFBSSxDQUFDLENBQUc7O0VBRWxDO0VBQ0EsSUFBSSxpQkFBaUIsR0FBRyxLQUFLLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsRUFBRSxtQkFBbUIsQ0FBQyxHQUFHLE1BQU0sQ0FBQyxnQkFBZ0IsQ0FBQyxtQkFBbUIsQ0FBQyxLQUFLLENBQUMsQ0FBQyxHQUFHLEVBQUU7RUFDOUksSUFBSSxZQUFZLEdBQUcsa0JBQWtCO0VBQ3JDLElBQUksU0FBUyxHQUFHLElBQUk7RUFDcEIsSUFBSSxTQUFTLEdBQUcsSUFBSSxDQUFDLENBQUM7RUFDbkIsSUFBSSxlQUFlLEdBQUc7SUFDbEIsSUFBSSxFQUFFO01BQ0YsTUFBTSxFQUFFLEVBQUU7TUFDVixLQUFLLEVBQUUsQ0FBQztNQUNSLFNBQVMsRUFBRTtJQUNmLENBQUM7SUFDRCxJQUFJLEVBQUUsRUFBRTtJQUNSLFFBQVEsRUFBRSxFQUFFO0lBQ2xCLGlCQUFpQixFQUFFO01BQ2xCLG1CQUFtQixFQUFFLEVBQUU7TUFDdkIsZUFBZSxFQUFFO0lBQ2xCO0VBQ0UsQ0FBQzs7RUFFRDtFQUNBLElBQUksTUFBTSxDQUFDLGdCQUFnQixFQUFFLGlCQUFpQixFQUFFO0lBQzVDLGVBQWUsR0FBRyxNQUFNLENBQUMsZ0JBQWdCLENBQUMsaUJBQWlCO0VBQy9EOztFQUVIO0VBQ0EsTUFBTSxhQUFhLEdBQUcsQ0FBQyxDQUFDLDRCQUE0QixDQUFDO0VBQ3JELE1BQU0sVUFBVSxHQUFHLENBQUMsQ0FBQywwQkFBMEIsQ0FBQztFQUNoRCxNQUFNLE1BQU0sR0FBRyxDQUFDLENBQUMsb0JBQW9CLENBQUM7O0VBRXRDO0VBQ0EsU0FBUyxVQUFVLENBQUMsS0FBSyxFQUFFO0lBQzFCLElBQUk7TUFDSCxNQUFNLEdBQUcsR0FBRyxJQUFJLEdBQUcsQ0FBQyxLQUFLLENBQUM7TUFDMUIsT0FBTyxHQUFHLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsSUFBSSxHQUFHLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBRyxDQUFDO0lBQzlFLENBQUMsQ0FBQyxNQUFNO01BQ1AsT0FBTyxLQUFLO0lBQ2I7RUFDRDtFQUVBLFNBQVMsTUFBTSxDQUFDLEtBQUssRUFBRTtJQUN0QixJQUFJLENBQUMsaUJBQWlCLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxFQUFFO01BQ3ZDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxLQUFLLENBQUM7SUFDOUI7RUFDRDtFQUVBLFNBQVMsS0FBSyxDQUFDLEVBQUUsRUFBRTtJQUNsQixPQUFPLGlCQUFpQixDQUFDLFFBQVEsQ0FBQyxFQUFFLENBQUM7RUFDdEM7RUFFQSxTQUFTLFFBQVEsQ0FBQyxFQUFFLEVBQUU7SUFDckI7SUFDQSxNQUFNLFVBQVUsR0FBRyxRQUFRLENBQUMsRUFBRSxFQUFFLEVBQUUsQ0FBQztJQUNuQyxpQkFBaUIsR0FBRyxpQkFBaUIsQ0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFJLFFBQVEsQ0FBQyxDQUFDLEVBQUUsRUFBRSxDQUFDLEtBQUssVUFBVSxDQUFDO0VBQ2xGO0VBRUEsU0FBUyxpQkFBaUIsQ0FBQyxXQUFXLEVBQUU7SUFDdkMsTUFBTSxZQUFZLEdBQU0sQ0FBQyxDQUFDLHNCQUFzQixDQUFDO0lBQ2pELE1BQU0sTUFBTSxHQUFJLE1BQU0sQ0FBQyxnQkFBZ0IsRUFBRSxPQUFPLEtBQUssR0FBRztJQUN4RCxNQUFNLFlBQVksR0FBRyxNQUFNLEdBQUcsQ0FBQyxDQUFDLHNCQUFzQixDQUFDLEdBQUcsQ0FBQyxDQUFDLHlCQUF5QixDQUFDOztJQUV0RjtJQUNBLE1BQU0sZ0JBQWdCLEdBQUcsV0FBVyxLQUFLLEtBQUssSUFBSSxDQUFDLFNBQVM7SUFFNUQsSUFBSSxnQkFBZ0IsRUFBRTtNQUNyQixZQUFZLENBQUMsSUFBSSxDQUFDLENBQUM7TUFDbkIsWUFBWSxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUM7SUFDbkMsQ0FBQyxNQUFNO01BQ04sWUFBWSxDQUFDLElBQUksQ0FBQyxDQUFDO01BQ25CLFlBQVksQ0FBQyxRQUFRLENBQUMsUUFBUSxDQUFDO0lBQ2hDO0VBQ0Q7RUFFQSxTQUFTLGlCQUFpQixDQUFDLGlCQUFpQixFQUFFO0lBQzdDLElBQUksaUJBQWlCLEtBQUssU0FBUyxJQUFJLFNBQVMsS0FBSyxpQkFBaUIsRUFBRTtNQUN2RSxTQUFTLEdBQUcsaUJBQWlCOztNQUU3QjtNQUNBLHNCQUFzQixDQUFDLENBQUM7SUFDekI7RUFDRDtFQUVBLFNBQVMsc0JBQXNCLENBQUEsRUFBRztJQUNqQyxNQUFNLGFBQWEsR0FBRyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsaUNBQWlDLENBQUM7SUFFbEYsYUFBYSxDQUFDLE9BQU8sQ0FBQyxNQUFNLElBQUk7TUFDL0IsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBQyxjQUFjLENBQUM7TUFDMUMsSUFBSSxDQUFDLEdBQUcsRUFBRTs7TUFFVjtNQUNBLE1BQU0sS0FBSyxHQUFHLFFBQVEsQ0FBQyxHQUFHLENBQUMsT0FBTyxDQUFDLGdCQUFnQixFQUFFLEVBQUUsQ0FBQztNQUN4RCxNQUFNLFNBQVMsR0FBRyxpQkFBaUIsQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDO01BRW5ELElBQUksQ0FBQyxTQUFTLElBQUksU0FBUyxFQUFFO1FBQzVCO1FBQ0EsTUFBTSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMseUJBQXlCLENBQUM7UUFDL0MsTUFBTSxDQUFDLFlBQVksQ0FBQyxVQUFVLEVBQUUsTUFBTSxDQUFDO1FBRXZDLElBQUksQ0FBQyxTQUFTLEVBQUU7VUFDZjtVQUNBLE1BQU0sQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLHVCQUF1QixDQUFDO1VBQzdDLE1BQU0sQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFFLE1BQU0sQ0FBQyxnQkFBZ0IsRUFBRSxpQ0FBaUMsSUFBSSx5RUFBeUUsQ0FBQztRQUN0SztNQUNELENBQUMsTUFBTTtRQUNOO1FBQ0EsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUMseUJBQXlCLEVBQUUsdUJBQXVCLENBQUM7UUFDM0UsTUFBTSxDQUFDLGVBQWUsQ0FBQyxVQUFVLENBQUM7UUFDbEMsTUFBTSxDQUFDLGVBQWUsQ0FBQyxPQUFPLENBQUM7TUFDaEM7SUFDRCxDQUFDLENBQUM7RUFDSDtFQUVBLFNBQVMsWUFBWSxDQUFBLEVBQUc7SUFDdkIsSUFBSSxTQUFTLEVBQUU7TUFDZCxZQUFZLENBQUMsU0FBUyxDQUFDO01BQ3ZCLFNBQVMsR0FBRyxJQUFJO0lBQ2pCO0lBQ0EsWUFBWSxHQUFHLGtCQUFrQjtFQUNsQztFQUVBLFNBQVMsZUFBZSxDQUFBLEVBQUc7SUFDMUIsSUFBSSxpQkFBaUIsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO01BQ2pDLFNBQVMsR0FBRyxVQUFVLENBQUMsTUFBTTtRQUM1QixVQUFVLENBQUMsQ0FBQztNQUNiLENBQUMsRUFBRSxZQUFZLENBQUM7SUFDakI7RUFDRDtFQUVBLFNBQVMsZ0JBQWdCLENBQUEsRUFBRztJQUMzQixZQUFZLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQyxZQUFZLEdBQUcsR0FBRyxFQUFFLGlCQUFpQixDQUFDLENBQUMsQ0FBQztFQUNqRTtFQUVHLFNBQVMsYUFBYSxDQUFBLEVBQUc7SUFDckIsTUFBTSxTQUFTLEdBQUcsSUFBSSxlQUFlLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUM7SUFDN0QsT0FBTyxTQUFTLENBQUMsR0FBRyxDQUFDLE1BQU0sQ0FBQyxLQUFLLFVBQVUsSUFBSSxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksS0FBSyxZQUFZO0VBQ3hGO0VBRUgsU0FBUyxrQkFBa0IsQ0FBQSxFQUFHO0lBQzdCLE1BQU0sU0FBUyxHQUFHLElBQUksZUFBZSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDO0lBQzdELE9BQU8sU0FBUyxDQUFDLEdBQUcsQ0FBQyxNQUFNLENBQUMsS0FBSyxVQUFVLElBQUksTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJLEtBQUssa0JBQWtCO0VBQzNGO0VBRUEsU0FBUyxvQkFBb0IsQ0FBQyxlQUFlLEVBQUM7SUFDN0MsTUFBTSxpQkFBaUIsR0FBRyxDQUFDLENBQUMsc0NBQXNDLENBQUM7SUFDbkUsSUFBSSxpQkFBaUIsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFDO01BQ2hDLGlCQUFpQixDQUFDLFdBQVcsQ0FBQyxlQUFlLENBQUMsUUFBUSxDQUFDO0lBQ3hELENBQUMsTUFBSyxJQUFJLFVBQVUsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO01BQ2hDLFVBQVUsQ0FBQyxPQUFPLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQztJQUM3QztJQUNBLHlCQUF5QixDQUFDLENBQUM7RUFDNUI7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMseUJBQXlCLENBQUEsRUFBRztJQUNwQyxJQUFJLEVBQUUsS0FBSyxlQUFlLENBQUMsSUFBSSxFQUFFO01BQ2hDO0lBQ0Q7SUFDQSxJQUFJLGtCQUFrQixHQUFHLENBQUMsQ0FBQywwQkFBMEIsQ0FBQztJQUV0RCxJQUFJLGtCQUFrQixDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7TUFDcEM7SUFDRDs7SUFFQTtJQUNBLGtCQUFrQixDQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDOztJQUU3QztJQUNBLElBQUksRUFBRSxtQkFBbUIsSUFBSSxlQUFlLENBQUMsRUFBRTtNQUM5QztJQUNEO0lBRUEsQ0FBQyxDQUFDLCtEQUErRCxDQUFDLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxpQkFBaUIsQ0FBQyxtQkFBbUIsQ0FBQztFQUMvSDs7RUFFQTtFQUNBLFNBQVMsVUFBVSxDQUFBLEVBQUc7SUFDckIsSUFBSSxpQkFBaUIsQ0FBQyxNQUFNLEtBQUssQ0FBQyxFQUFFO01BQ25DLFlBQVksQ0FBQyxDQUFDO01BQ2Q7SUFDRDtJQUVBLE1BQU0sQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUNqQjtNQUNDLElBQUksRUFBRSxNQUFNLENBQUMsRUFBRSxDQUFDLEdBQUcsQ0FBQyxZQUFZLENBQUUsOENBQThDLEVBQUU7UUFBRSxHQUFHLEVBQUU7TUFBa0IsQ0FBRTtJQUM5RyxDQUNELENBQUMsQ0FBQyxJQUFJLENBQUksUUFBUSxJQUFNO01BQ3ZCLElBQUksUUFBUSxDQUFDLE9BQU8sSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxPQUFPLENBQUMsRUFBRTtRQUN4RDtRQUNBLGlCQUFpQixDQUFDLFFBQVEsQ0FBQyxVQUFVLENBQUM7O1FBRXRDO1FBQ0EsaUJBQWlCLENBQUMsUUFBUSxDQUFDLGFBQWEsQ0FBQzs7UUFFekM7UUFDQSxJQUFJLGVBQWUsQ0FBQyxJQUFJLENBQUMsTUFBTSxLQUFLLFFBQVEsQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLENBQUMsTUFBTSxJQUFJLGVBQWUsQ0FBQyxJQUFJLENBQUMsU0FBUyxLQUFLLFFBQVEsQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLENBQUMsU0FBUyxFQUFFO1VBQzNKO1VBQ0EsZUFBZSxHQUFHLFFBQVEsQ0FBQyxpQkFBaUI7O1VBRTVDO1VBQ0EsQ0FBQyxDQUFDLDBCQUEwQixDQUFDLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLENBQUM7VUFDbkU7VUFDQSxvQkFBb0IsQ0FBQyxlQUFlLENBQUM7O1VBRXJDO1VBQ0EsUUFBUSxDQUFDLGFBQWEsQ0FBQyxJQUFJLFdBQVcsQ0FBQyx1QkFBdUIsRUFBRTtZQUFFLE1BQU0sRUFBRSxlQUFlLENBQUM7VUFBSyxDQUFDLENBQUMsQ0FBQztRQUNuRztRQUNBLFFBQVEsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLE1BQU0sSUFBSTtVQUNsQyxNQUFNLElBQUksR0FBRyxDQUFDLENBQUMseUNBQXlDLE1BQU0sQ0FBQyxFQUFFLElBQUksQ0FBQztVQUN0RSxJQUFJLENBQUMsV0FBVyxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUM7VUFFN0IsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxtQ0FBbUMsRUFBRSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQzs7VUFFckU7VUFDQSxJQUFJLE1BQU0sQ0FBQyxNQUFNLEtBQUssV0FBVyxFQUFFO1lBQ2xDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxPQUFPLENBQUMscUNBQXFDLEVBQUUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUM7VUFDeEU7VUFFQSxJQUFJLE1BQU0sQ0FBQyxNQUFNLEtBQUssV0FBVyxJQUFJLE1BQU0sQ0FBQyxNQUFNLEtBQUssUUFBUSxFQUFFO1lBQ2hFLFFBQVEsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDO1VBQ3BCO1FBQ0QsQ0FBQyxDQUFDO1FBRUYsZ0JBQWdCLENBQUMsQ0FBQztRQUNsQixlQUFlLENBQUMsQ0FBQztNQUNsQixDQUFDLE1BQU07UUFDTjtRQUNBLGlCQUFpQixHQUFHLEVBQUU7UUFDdEIsWUFBWSxDQUFDLENBQUM7UUFDZCxPQUFPLENBQUMsS0FBSyxDQUFDLGdCQUFnQixFQUFFLFFBQVEsQ0FBQyxPQUFPLElBQUksUUFBUSxDQUFDO01BQzlEO0lBQ0QsQ0FBRSxDQUFDO0VBQ0o7RUFFQSxTQUFTLGFBQWEsQ0FBQyxDQUFDLEVBQUU7SUFDekIsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDOztJQUVsQjtJQUNBLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsRUFBRTtNQUM3QjtJQUNEO0lBRUEsTUFBTSxPQUFPLEdBQUcsYUFBYSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7SUFFMUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxPQUFPLENBQUMsRUFBRTtNQUN6QixLQUFLLENBQUMsMEJBQTBCLENBQUM7TUFDakM7SUFDRDtJQUVBLE1BQU0sTUFBTSxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDO0lBRXJDLE1BQU0sQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUNqQjtNQUNDLElBQUksRUFBRSxzQ0FBc0M7TUFDNUMsTUFBTSxFQUFFLE1BQU07TUFDZCxJQUFJLEVBQUU7UUFDTCxRQUFRLEVBQUUsT0FBTztRQUNqQixNQUFNLEVBQUU7TUFDVDtJQUNELENBQ0QsQ0FBQyxDQUFDLElBQUksQ0FBSSxRQUFRLElBQU07TUFDdkIsSUFBSSxRQUFRLENBQUMsT0FBTyxFQUFFO1FBQ3JCLElBQUssQ0FBRSxLQUFLLENBQUMsUUFBUSxDQUFDLEVBQUUsQ0FBQyxFQUFHO1VBQzNCLGFBQWEsQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDO1VBQ3JCLFVBQVUsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQzs7VUFFaEM7VUFDQSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsT0FBTyxDQUFDLDRCQUE0QixDQUFDO1VBRWpELE1BQU0sQ0FBQyxXQUFXLENBQUMsUUFBUSxDQUFDO1VBQzVCLE1BQU0sQ0FBQyxRQUFRLENBQUMsRUFBRSxDQUFDO1VBQ25CLElBQUksbUJBQW1CLEdBQUcsQ0FBQyxDQUFDLG1DQUFtQyxDQUFDO1VBQ2hFLG1CQUFtQixDQUFDLElBQUksQ0FBRSxRQUFRLENBQUUsbUJBQW1CLENBQUMsSUFBSSxDQUFDLENBQUUsQ0FBQyxHQUFHLENBQUUsQ0FBQzs7VUFFdEU7VUFDQSxpQkFBaUIsQ0FBQyxRQUFRLENBQUMsVUFBVSxDQUFDOztVQUV0QztVQUNBLGVBQWUsR0FBRyxRQUFRLENBQUMsaUJBQWlCOztVQUU1QztVQUNBLG9CQUFvQixDQUFDLGVBQWUsQ0FBQztVQUVyQyxJQUFJLG1CQUFtQixJQUFJLGVBQWUsRUFBRTtZQUMzQyxDQUFDLENBQUMsMkNBQTJDLENBQUMsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLGlCQUFpQixDQUFDLGVBQWUsQ0FBQztVQUN2Rzs7VUFFQTtVQUNBLGlCQUFpQixDQUFDLFFBQVEsQ0FBQyxhQUFhLENBQUM7VUFFekMsSUFBSSxTQUFTLEVBQUU7WUFDZCxZQUFZLENBQUMsQ0FBQztVQUNmO1VBQ0EsZUFBZSxDQUFDLENBQUM7UUFDbEI7TUFFRCxDQUFDLE1BQU07UUFDTjtRQUNBLGFBQWEsQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDOztRQUVyQjtRQUNBLElBQUksUUFBUSxFQUFFLE9BQU8sSUFBSSxRQUFRLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxnQ0FBZ0MsQ0FBQyxFQUFFO1VBQ3JGO1VBQ0EscUJBQXFCLENBQUMsQ0FBQztVQUN2QjtVQUNBLGlCQUFpQixDQUFDLFFBQVEsQ0FBQyxhQUFhLEtBQUssU0FBUyxHQUFHLFFBQVEsQ0FBQyxhQUFhLEdBQUcsS0FBSyxDQUFDO1FBQ3pGO1FBRUEsT0FBTyxDQUFDLEtBQUssQ0FBQyxRQUFRLEVBQUUsT0FBTyxJQUFJLFFBQVEsQ0FBQztNQUM3QztJQUNELENBQUMsQ0FBQztFQUNIO0VBRUEsU0FBUyxlQUFlLENBQUMsQ0FBQyxFQUFFO0lBQzNCLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUVsQixNQUFNLE9BQU8sR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDO0lBQ3ZCLElBQUksRUFBRSxHQUFHLE9BQU8sQ0FBQyxPQUFPLENBQUMsY0FBYyxDQUFDLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDO0lBQ25FLElBQUssQ0FBRSxFQUFFLEVBQUc7TUFDWDtJQUNEO0lBRUEsTUFBTSxNQUFNLEdBQUcsT0FBTyxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUM7SUFFckMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQ2pCO01BQ0MsSUFBSSxFQUFFLHNDQUFzQyxHQUFHLEVBQUU7TUFDakQsTUFBTSxFQUFFLE9BQU87TUFDZixJQUFJLEVBQUU7UUFDTCxNQUFNLEVBQUU7TUFDVDtJQUNELENBQ0QsQ0FBQyxDQUFDLElBQUksQ0FBSSxRQUFRLElBQU07TUFDdkIsSUFBSSxRQUFRLENBQUMsT0FBTyxFQUFFO1FBQ3JCLE1BQU0sQ0FBQyxRQUFRLENBQUMsRUFBRSxDQUFDO1FBRW5CLENBQUMsQ0FBQyxlQUFlLFFBQVEsQ0FBQyxFQUFFLHNCQUFzQixDQUFDLENBQUMsTUFBTSxDQUFDLENBQUM7UUFDNUQsTUFBTSxJQUFJLEdBQUcsQ0FBQyxDQUFDLDZCQUE2QixRQUFRLENBQUMsRUFBRSxJQUFJLENBQUM7UUFDNUQsSUFBSSxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDOztRQUUvQjtRQUNNLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxPQUFPLENBQUMsNkJBQTZCLEVBQUUsQ0FBQyxRQUFRLENBQUMsRUFBRSxDQUFDLENBQUM7O1FBRXZFO1FBQ0EsaUJBQWlCLENBQUMsUUFBUSxDQUFDLFVBQVUsQ0FBQzs7UUFFdEM7UUFDQSxpQkFBaUIsQ0FBQyxRQUFRLENBQUMsYUFBYSxDQUFDOztRQUU3QjtRQUNBLGVBQWUsR0FBRyxRQUFRLENBQUMsaUJBQWlCOztRQUV4RDtRQUNBLG9CQUFvQixDQUFDLGVBQWUsQ0FBQztRQUVyQyxJQUFJLFNBQVMsRUFBRTtVQUNkLFlBQVksQ0FBQyxDQUFDO1FBQ2Y7UUFDQSxlQUFlLENBQUMsQ0FBQztNQUNsQixDQUFDLE1BQU07UUFDTixPQUFPLENBQUMsS0FBSyxDQUFDLFFBQVEsRUFBRSxPQUFPLElBQUksUUFBUSxDQUFDO01BQzdDO0lBQ0QsQ0FBQyxDQUFDO0VBQ0g7O0VBRUE7RUFDQTtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUUsT0FBTyxFQUFFLGtDQUFrQyxFQUFFLGFBQWMsQ0FBQztFQUM1RSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFFLE9BQU8sRUFBRSxpQ0FBaUMsRUFBRSxlQUFnQixDQUFDO0VBQzdFO0VBQ0EsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBRSxVQUFVLEVBQUUsNEJBQTRCLEVBQUUsVUFBUyxDQUFDLEVBQUU7SUFDckUsSUFBSSxDQUFDLENBQUMsR0FBRyxLQUFLLE9BQU8sRUFBRTtNQUNyQixDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7TUFDbEIsQ0FBQyxDQUFDLGtDQUFrQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUM7SUFDL0M7RUFDRCxDQUFDLENBQUM7O0VBRUY7RUFDRyxTQUFTLHFCQUFxQixDQUFBLEVBQUc7SUFDN0IsTUFBTSxTQUFTLEdBQUcsSUFBSSxlQUFlLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUM7SUFDbkUsT0FBTyxVQUFVLEtBQUssU0FBUyxDQUFDLEdBQUcsQ0FBQyxNQUFNLENBQUM7RUFDekM7O0VBRUg7RUFDQSxJQUFJLHFCQUFxQixDQUFDLENBQUMsSUFBSSxpQkFBaUIsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO0lBQzVELElBQUksU0FBUyxFQUFFO01BQ2QsWUFBWSxDQUFDLENBQUM7SUFDZjtJQUNBLGVBQWUsQ0FBQyxDQUFDO0VBQ2xCOztFQUVBO0VBQ0EsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUscURBQXFELEVBQUUsWUFBVztJQUN6RixJQUFJLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsV0FBVyxDQUFDLEVBQUU7TUFDNUM7SUFDRDs7SUFFQTtJQUNBLFVBQVUsQ0FBQyxNQUFNO01BQ2hCLG9CQUFvQixDQUFDLGVBQWUsQ0FBQztJQUN0QyxDQUFDLEVBQUUsRUFBRSxDQUFDO0VBQ1AsQ0FBQyxDQUFDOztFQUVGO0VBQ0EsU0FBUyw0QkFBNEIsQ0FBQyxNQUFNLEdBQUcsS0FBSyxFQUFFO0lBQ3JELENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxRQUFRLENBQUMsZUFBZSxDQUFDO0lBRTdELElBQUksTUFBTSxFQUFFO01BQ1g7TUFDQSxDQUFDLENBQUMscUJBQXFCLENBQUMsQ0FBQyxHQUFHLENBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxDQUFDLG1CQUFtQixDQUFDO01BQ3RFLENBQUMsQ0FBQyxzQkFBc0IsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLENBQUMsbUJBQW1CLENBQUM7TUFDdkUsQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUMsR0FBRyxDQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsQ0FBQyxtQkFBbUIsQ0FBQzs7TUFFdEU7TUFDQSxJQUFHLENBQUMsQ0FBQyxxQkFBcUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLG1CQUFtQixDQUFDLEVBQUU7UUFDakU7TUFDRDtJQUNEO0lBQ0EsQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsb0JBQW9CLENBQUM7SUFDOUQsQ0FBQyxDQUFDLHNCQUFzQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsb0JBQW9CLENBQUM7SUFDL0QsQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsb0JBQW9CLENBQUM7RUFDL0Q7O0VBRUE7RUFDQSxTQUFTLHlCQUF5QixDQUFDLFVBQVUsRUFBRSxNQUFNLEVBQUU7SUFDdEQsSUFBSSxRQUFRLEdBQUcsQ0FBQyxDQUFDLGVBQWUsVUFBVSxFQUFFLENBQUM7SUFDN0MsSUFBSSxTQUFTLEdBQUcsUUFBUSxDQUFDLFFBQVEsQ0FBQywwQkFBMEIsQ0FBQztJQUM3RCxJQUFJLE1BQU0sR0FBRyxDQUFDLENBQUMsNkJBQTZCLFVBQVUsK0JBQStCLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLDRCQUE0QixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztJQUVqSSxJQUFLLFNBQVMsRUFBRztNQUNoQixRQUFRLENBQUMsV0FBVyxDQUFDLDBCQUEwQixDQUFDO01BQ2hELENBQUMsQ0FBQyw2QkFBNkIsVUFBVSxJQUFJLENBQUMsQ0FBQyxXQUFXLENBQUMsdUJBQXVCLENBQUM7TUFDbkY7TUFDQSxJQUFJLE1BQU0sRUFBRTtRQUNYLDJCQUEyQixDQUFDLEtBQUssQ0FBQztNQUNuQztNQUVBO0lBQ0Q7SUFFQSxRQUFRLENBQUMsUUFBUSxDQUFDLDBCQUEwQixDQUFDO0lBQzdDLENBQUMsQ0FBQyw2QkFBNkIsVUFBVSxJQUFJLENBQUMsQ0FBQyxRQUFRLENBQUMsdUJBQXVCLENBQUM7O0lBRWhGO0lBQ0EsMEJBQTBCLENBQUMsUUFBUSxFQUFFLFVBQVUsRUFBRSxNQUFNLENBQUM7SUFFeEQsSUFBSSxNQUFNLEVBQUU7TUFDWCwyQkFBMkIsQ0FBQyxDQUFDO0lBQzlCO0VBQ0Q7O0VBRUE7RUFDQSxTQUFTLDBCQUEwQixDQUFDLEtBQUssRUFBRSxLQUFLLEVBQUUsTUFBTSxFQUFFO0lBQ3pELENBQUMsQ0FBQyxJQUFJLENBQ0wsT0FBTyxFQUNQO01BQ0MsTUFBTSxFQUFFLHFDQUFxQztNQUM3QyxXQUFXLEVBQUUsZ0JBQWdCLENBQUMsS0FBSztNQUNuQyxLQUFLLEVBQUUsS0FBSztNQUNaLE1BQU0sRUFBRSxLQUFLO01BQ2IsTUFBTSxFQUFFO0lBQ1QsQ0FBQyxFQUNELFVBQVMsUUFBUSxFQUFFO01BQ2xCLElBQUksQ0FBQyxRQUFRLENBQUMsT0FBTyxFQUFFO1FBQ3RCLE9BQU8sQ0FBQyxLQUFLLENBQUMsZ0NBQWdDLEVBQUUsUUFBUSxFQUFFLElBQUksSUFBSSxRQUFRLENBQUM7TUFDNUU7SUFDRCxDQUNELENBQUM7RUFDRjs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLDJCQUEyQixDQUFDLFVBQVUsR0FBRyxJQUFJLEVBQUU7SUFDdkQsTUFBTSxRQUFRLEdBQUcsVUFBVSxHQUFHLG1CQUFtQixHQUFHLG9CQUFvQjtJQUN4RSxNQUFNLFdBQVcsR0FBRyxVQUFVLEdBQUcsb0JBQW9CLEdBQUcsbUJBQW1CO0lBRTNFLElBQUksVUFBVSxHQUFHO01BQ2hCLFVBQVUsRUFBRSxDQUFDLENBQUMscUJBQXFCLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztNQUMzQyxXQUFXLEVBQUUsQ0FBQyxDQUFDLHNCQUFzQixDQUFDLENBQUMsSUFBSSxDQUFDO0lBQzdDLENBQUM7SUFFRCxVQUFVLENBQUMsVUFBVSxDQUNuQixXQUFXLENBQUMsV0FBVyxDQUFDLENBQ3hCLFFBQVEsQ0FBQyxRQUFRLENBQUM7SUFFcEIsVUFBVSxDQUFDLFdBQVcsQ0FDcEIsV0FBVyxDQUFDLFdBQVcsQ0FBQyxDQUN4QixRQUFRLENBQUMsUUFBUSxDQUFDOztJQUdwQjtJQUNBLElBQUksZ0JBQWdCLEdBQUcsQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7SUFDdEQsSUFBSSxnQkFBZ0IsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7TUFDM0Q7SUFDRDtJQUVBLGdCQUFnQixDQUFDLFdBQVcsQ0FBQyxXQUFXLENBQUMsQ0FDeEMsUUFBUSxDQUFDLFFBQVEsQ0FBQztFQUNwQjs7RUFFQTtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLDRCQUE0QixFQUFFLFlBQVc7SUFDaEUsSUFBSSxVQUFVLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sQ0FBQyxjQUFjLENBQUMsQ0FBQyxJQUFJLENBQUMsb0JBQW9CLENBQUM7SUFDM0UseUJBQXlCLENBQUMsVUFBVSxFQUFFLFlBQVksQ0FBQztFQUNwRCxDQUFDLENBQUM7O0VBRUY7RUFDQSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSx5QkFBeUIsRUFBRSxZQUFZO0lBQzlELElBQUksQ0FBQyxDQUFDLDJCQUEyQixDQUFDLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRTtNQUM5QyxDQUFDLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxXQUFXLENBQUMsMEJBQTBCLENBQUM7TUFDNUQsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyx1QkFBdUIsQ0FBQztNQUN0RCxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsV0FBVyxDQUFDLGtDQUFrQyxDQUFDO01BQ3ZELDJCQUEyQixDQUFDLEtBQUssQ0FBQztNQUVsQztJQUNEO0lBRUEsQ0FBQyxDQUFDLGlCQUFpQixDQUFDLENBQUMsUUFBUSxDQUFDLDBCQUEwQixDQUFDO0lBQ3pELENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxRQUFRLENBQUMsdUJBQXVCLENBQUM7SUFDbkQsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLFFBQVEsQ0FBQyxrQ0FBa0MsQ0FBQztJQUNwRCwyQkFBMkIsQ0FBQyxDQUFDOztJQUU3QjtJQUNBLDBCQUEwQixDQUFDLFFBQVEsRUFBRSxLQUFLLEVBQUUsZUFBZSxDQUFDO0VBQzdELENBQUMsQ0FBQzs7RUFFRjtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLGdCQUFnQixFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQUU7SUFDdkQsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsUUFBUSxDQUFDLHlCQUF5QixDQUFDLEVBQUU7TUFDaEQ7SUFDRDtJQUVBLElBQUksVUFBVSxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsd0JBQXdCLENBQUM7SUFDdkQsMEJBQTBCLENBQUMsWUFBWSxFQUFFLFVBQVUsRUFBRSxtQkFBbUIsQ0FBQztFQUMxRSxDQUFDLENBQUM7O0VBRUY7RUFDQSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFDLHFDQUFxQyxFQUFFLFVBQVUsQ0FBQyxFQUFFLFVBQVUsRUFBRTtJQUM5RTtJQUNBLElBQUksQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRTtNQUN4QztJQUNEOztJQUVBO0lBQ0EsQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUMsV0FBVyxDQUFDLG9CQUFvQixDQUFDO0VBQzNELENBQUMsQ0FBQzs7RUFFRjtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsNEJBQTRCLEVBQUUsVUFBVSxDQUFDLEVBQUU7SUFDekQ7SUFDQSxDQUFDLENBQUMscUJBQXFCLENBQUMsQ0FBQyxXQUFXLENBQUMsb0JBQW9CLENBQUM7SUFDMUQsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLENBQUMsV0FBVyxDQUFDLG1CQUFtQixDQUFDO0lBQ3hELENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxlQUFlLENBQUM7RUFDakQsQ0FBQyxDQUFDOztFQUVGO0VBQ0EsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQywrREFBK0QsRUFBRSxVQUFVLENBQUMsRUFBRSxVQUFVLEVBQUU7SUFDeEc7SUFDQSxJQUFJLE1BQU0sR0FBRyxDQUFDLENBQUMsNkJBQTZCLFVBQVUsSUFBSSxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxxQkFBcUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7SUFFL0YsSUFBSSxNQUFNLEVBQUU7TUFDWCw0QkFBNEIsQ0FBQyxDQUFDO0lBQy9CO0VBQ0QsQ0FBQyxDQUFDO0VBRUYsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyw2QkFBNkIsRUFBRSxVQUFVLENBQUMsRUFBRSxVQUFVLEVBQUU7SUFDdEUsQ0FBQyxDQUFDLGVBQWUsVUFBVSxFQUFFLENBQUMsQ0FBQyxXQUFXLENBQUMsMEJBQTBCLENBQUM7SUFDdEUsQ0FBQyxDQUFDLDZCQUE2QixVQUFVLElBQUksQ0FBQyxDQUFDLFdBQVcsQ0FBQyx1QkFBdUIsQ0FBQztFQUNwRixDQUFDLENBQUM7RUFFRixDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU07SUFDcEIsSUFBSyxDQUFFLGtCQUFrQixDQUFDLENBQUMsRUFBRztNQUM3QjtJQUNEOztJQUVBO0lBQ0EsNEJBQTRCLENBQUMsSUFBSSxDQUFDOztJQUVsQztJQUNBLE1BQU0sU0FBUyxHQUFHLElBQUksZUFBZSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDO0lBQzdELE1BQU0sTUFBTSxHQUFHLFNBQVMsQ0FBQyxHQUFHLENBQUMsT0FBTyxDQUFDOztJQUVyQztJQUNBLElBQUksVUFBVSxHQUFHLENBQUMsQ0FBQyx3QkFBd0IsQ0FBQyxFQUFFLEtBQUssQ0FBQyxDQUFDLEVBQUUsSUFBSSxDQUFDLG9CQUFvQixDQUFDOztJQUVqRjtJQUNBLElBQUksQ0FBQyxNQUFNLElBQUksTUFBTSxLQUFLLEVBQUUsRUFBRTtNQUM3QixJQUFJLFVBQVUsRUFBRTtRQUNmLDBCQUEwQixDQUFDLFFBQVEsRUFBRSxVQUFVLEVBQUUsaUJBQWlCLENBQUM7TUFDcEU7TUFDQTtJQUNEO0lBRUEsMEJBQTBCLENBQUMsUUFBUSxFQUFFLE1BQU0sRUFBRSxtQkFBbUIsQ0FBQztJQUVqRSxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsT0FBTyxDQUFDO01BQ3ZCLFNBQVMsRUFBRSxDQUFDLENBQUMsNkJBQTZCLE1BQU0sSUFBSSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUc7SUFDdEUsQ0FBQyxFQUFFLEdBQUcsQ0FBQzs7SUFFUDtJQUNBLFNBQVMsQ0FBQyxNQUFNLENBQUMsT0FBTyxDQUFDO0lBQ3pCLE1BQU0sTUFBTSxHQUFHLE1BQU0sQ0FBQyxRQUFRLENBQUMsUUFBUSxHQUFHLEdBQUcsR0FBRyxTQUFTLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBRyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUk7SUFDM0YsTUFBTSxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEVBQUUsRUFBRSxFQUFFLE1BQU0sQ0FBQztFQUM1QyxDQUFDLENBQUM7QUFDSCxDQUFDLENBQUM7Ozs7O0FDNzJCRixPQUFBO0FBQ0EsT0FBQTtBQUNBLE9BQUE7QUFDQSxPQUFBO0FBQ0EsT0FBQTtBQUdBLE9BQUE7QUFDQSxPQUFBO0FBQ0EsT0FBQTtBQUNBLE9BQUE7QUFDQSxPQUFBO0FBQ0EsT0FBQTtBQUNBLE9BQUE7QUFDQSxPQUFBO0FBQ0EsT0FBQTtBQUNBLE9BQUE7QUFDQSxPQUFBOzs7OztBQ2xCQSxJQUFJLENBQUMsR0FBRyxNQUFNO0FBQ2QsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUssQ0FBQyxZQUFVO0VBQ3hCLElBQUksUUFBUSxJQUFJLE1BQU0sRUFBRTtJQUNwQjtBQUNSO0FBQ0E7SUFDUSxJQUFJLEtBQUssR0FBRyxDQUFDLENBQUMsdUJBQXVCLENBQUM7SUFDdEMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsVUFBUyxDQUFDLEVBQUM7TUFDekIsSUFBSSxHQUFHLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQztNQUN4QyxJQUFJLE1BQU0sR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLGtCQUFrQixDQUFDLElBQUksYUFBYTtNQUM5RCxJQUFJLE9BQU8sR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLElBQUksVUFBVTs7TUFFN0Q7TUFDQSxrQkFBa0IsQ0FBQyxNQUFNLEVBQUUsT0FBTyxDQUFDOztNQUVuQztNQUNBLGFBQWEsQ0FBQyxHQUFHLENBQUM7TUFDbEIsT0FBTyxLQUFLO0lBQ2hCLENBQUMsQ0FBQztJQUVGLFNBQVMsYUFBYSxDQUFDLEdBQUcsRUFBQztNQUN2QixHQUFHLEdBQUcsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7TUFDcEIsSUFBSyxHQUFHLENBQUMsTUFBTSxLQUFLLENBQUMsRUFBRztRQUNwQjtNQUNKO01BRUksSUFBSyxHQUFHLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRztRQUNsQixNQUFNLENBQUMsTUFBTSxDQUFDLFNBQVMsRUFBRSxHQUFHLENBQUM7UUFFN0IsVUFBVSxDQUFDLFlBQVc7VUFDbEIsTUFBTSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUM7UUFDekIsQ0FBQyxFQUFFLEdBQUcsQ0FBQztNQUNYLENBQUMsTUFBTTtRQUNILE1BQU0sQ0FBQyxNQUFNLENBQUMsU0FBUyxFQUFFLEdBQUcsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO01BQzVDO0lBRVI7RUFDSjtFQUVILENBQUMsQ0FBRSxnQkFBaUIsQ0FBQyxDQUFDLEVBQUUsQ0FBRSxPQUFPLEVBQUUsWUFBVztJQUM3QyxrQkFBa0IsQ0FBRSxxQ0FBcUMsRUFBRSxxQkFBc0IsQ0FBQztFQUNuRixDQUFFLENBQUM7O0VBRUE7RUFDQSxTQUFTLGtCQUFrQixDQUFDLE1BQU0sRUFBRSxPQUFPLEVBQUU7SUFDekMsSUFBSSxPQUFPLFFBQVEsS0FBSyxXQUFXLElBQUksUUFBUSxDQUFDLEtBQUssRUFBRTtNQUNuRDtNQUNBLElBQUksT0FBTyxvQkFBb0IsS0FBSyxXQUFXLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxhQUFhLElBQUksb0JBQW9CLENBQUMsYUFBYSxLQUFLLEdBQUcsRUFBRTtRQUNsSTtNQUNKOztNQUVBO01BQ0EsSUFBSSxvQkFBb0IsQ0FBQyxPQUFPLElBQUksT0FBTyxRQUFRLENBQUMsUUFBUSxLQUFLLFVBQVUsRUFBRTtRQUN6RSxRQUFRLENBQUMsUUFBUSxDQUFDLG9CQUFvQixDQUFDLE9BQU8sQ0FBQztNQUNuRDtNQUVBLFFBQVEsQ0FBQyxLQUFLLENBQUMsZ0JBQWdCLEVBQUU7UUFDN0IsUUFBUSxFQUFFLE1BQU07UUFDNUIsZ0JBQWdCLEVBQUUsT0FBTztRQUN6QixRQUFRLEVBQUUsb0JBQW9CLENBQUMsTUFBTTtRQUN6QixPQUFPLEVBQUUsb0JBQW9CLENBQUMsS0FBSztRQUNuQyxhQUFhLEVBQUUsb0JBQW9CLENBQUMsR0FBRztRQUN2QyxTQUFTLEVBQUUsb0JBQW9CLENBQUMsT0FBTztRQUN2QyxNQUFNLEVBQUUsb0JBQW9CLENBQUM7TUFDakMsQ0FBQyxDQUFDO0lBQ047RUFDSjs7RUFFQTtFQUNBLE1BQU0sQ0FBQyxrQkFBa0IsR0FBRyxrQkFBa0I7QUFDbEQsQ0FBQyxDQUFDOzs7OztBQ3RFRixDQUFJLFFBQVEsSUFBTTtFQUNqQixZQUFZOztFQUVaLFNBQVMsb0JBQW9CLENBQUEsRUFBRztJQUMvQixRQUFRLENBQUMsYUFBYSxDQUFFLElBQUksV0FBVyxDQUFFLHNCQUF1QixDQUFFLENBQUM7RUFDcEU7RUFFQSxRQUFRLENBQUMsZ0JBQWdCLENBQUUsa0JBQWtCLEVBQUUsTUFBTTtJQUNwRCxpQkFBaUIsQ0FBQyxDQUFDO0lBQ25CLGtCQUFrQixDQUFDLENBQUM7SUFDcEIsZUFBZSxDQUFDLENBQUM7SUFDakIsV0FBVyxDQUFDLENBQUM7SUFDYixjQUFjLENBQUMsQ0FBQztJQUNoQiw0Q0FBNEMsQ0FBQyxDQUFDO0VBQy9DLENBQUUsQ0FBQztFQUVILE1BQU0sYUFBYSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUscURBQXNELENBQUM7O0VBRXJHO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsOEJBQThCLENBQUUsSUFBSSxFQUFHO0lBQy9DLE1BQU0sZUFBZSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUNBQW9DLENBQUM7SUFDckYsSUFBSyxlQUFlLElBQUksSUFBSSxFQUFHO01BQzlCLGVBQWUsQ0FBQyxTQUFTLEdBQUcsSUFBSTtJQUNqQztFQUNEOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsNEJBQTRCLENBQUUsTUFBTSxFQUFFLFFBQVEsRUFBRztJQUN6RCxJQUFLLFdBQVcsS0FBSyxNQUFNLEVBQUc7TUFDN0IsSUFBSyxDQUFFLFFBQVEsRUFBRztRQUNqQixRQUFRLENBQUMsZ0JBQWdCLENBQUUsZ0RBQWlELENBQUMsQ0FBQyxPQUFPLENBQUksRUFBRSxJQUFNO1VBQ2hHLEVBQUUsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLGtCQUFtQixDQUFDO1FBQzFDLENBQUUsQ0FBQztRQUVIO01BQ0Q7TUFFQSxRQUFRLENBQUMsZ0JBQWdCLENBQUUsZ0RBQWlELENBQUMsQ0FBQyxPQUFPLENBQUksRUFBRSxJQUFNO1FBQ2hHLEVBQUUsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFFLGtCQUFtQixDQUFDO01BQ3ZDLENBQUUsQ0FBQztNQUVIO0lBQ0Q7SUFFQSxRQUFRLENBQUMsZ0JBQWdCLENBQUUscUJBQXNCLENBQUMsQ0FBQyxPQUFPLENBQUksRUFBRSxJQUFNO01BQ3JFLEVBQUUsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLGtCQUFtQixDQUFDO0lBQzFDLENBQUUsQ0FBQztFQUNKOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsa0JBQWtCLENBQUUsWUFBWSxFQUFHO0lBQzNDLE1BQU0sT0FBTyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsZ0RBQWlELENBQUM7SUFDMUYsSUFBSyxPQUFPLEVBQUc7TUFDZCxPQUFPLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSxjQUFjLEVBQUUsQ0FBRSxZQUFhLENBQUM7SUFDM0Q7RUFDRDs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsb0JBQW9CLENBQUUsS0FBSyxFQUFFLEtBQUssRUFBRztJQUM3QyxNQUFNLEdBQUcsR0FBRyxRQUFRLENBQUMsY0FBYyxDQUFFLG1CQUFvQixDQUFDO0lBRTFELElBQUssQ0FBRSxHQUFHLEVBQUc7TUFDWjtJQUNEO0lBRUEsTUFBTSxTQUFTLEdBQUcsS0FBSyxHQUFHLENBQUM7SUFDM0IsTUFBTSxVQUFVLEdBQUcsS0FBSyxJQUFJLEtBQUs7SUFFakMsR0FBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsY0FBYyxFQUFFLENBQUUsU0FBVSxDQUFDO0lBQ25ELEdBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLDhCQUE4QixFQUFFLFNBQVMsSUFBSSxDQUFFLFVBQVcsQ0FBQztJQUNqRixHQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSw2QkFBNkIsRUFBRSxTQUFTLElBQUksVUFBVyxDQUFDO0lBQzlFLEdBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLCtCQUErQixFQUFFLFNBQVMsSUFBSSxVQUFXLENBQUM7RUFDakY7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLDRDQUE0QyxDQUFBLEVBQUc7SUFDdkQsUUFBUSxDQUFDLGdCQUFnQixDQUFFLDZCQUE2QixFQUFJLENBQUMsSUFBTTtNQUNsRTtNQUNBLElBQUksZ0JBQWdCLENBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxZQUFhLENBQUMsQ0FBQyxPQUFPLEtBQUssTUFBTSxFQUFFO1FBQ2pFO01BQ0Q7TUFFQSxNQUFNLE9BQU8sR0FBRyxDQUNmLHlCQUF5QixFQUN6Qiw2QkFBNkIsQ0FDN0I7TUFFRCxNQUFNLFVBQVUsR0FBRyxPQUFPLENBQUMsS0FBSyxDQUFFLEdBQUcsSUFBSSxRQUFRLENBQUMsYUFBYSxDQUFFLEdBQUksQ0FBQyxLQUFLLElBQUssQ0FBQzs7TUFFakY7TUFDQSxJQUFJLENBQUMsQ0FBQyxNQUFNLENBQUMsTUFBTSxLQUFLLFVBQVUsRUFBRTtRQUNuQyxJQUFJLENBQUMsQ0FBQyxNQUFNLENBQUMsWUFBWSxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUUsa0JBQW1CLENBQUMsRUFBRTtVQUNuRSxDQUFDLENBQUMsTUFBTSxDQUFDLFlBQVksQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLGtCQUFtQixDQUFDO1FBQzdEO1FBRUE7TUFDRDs7TUFFQTtNQUNBLElBQUssQ0FBRSxVQUFVLEVBQUc7UUFDbkI7TUFDRDs7TUFFQTtNQUNBLENBQUMsQ0FBQyxNQUFNLENBQUMsWUFBWSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUUsa0JBQW1CLENBQUM7SUFDMUQsQ0FBRSxDQUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsMkJBQTJCLENBQUEsRUFBRztJQUN0QyxNQUFNLE9BQU8sR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLG1CQUFvQixDQUFDO0lBRTdELElBQUssT0FBTyxFQUFHO01BQ2QsT0FBTyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUUsNEJBQTZCLENBQUM7SUFDdEQ7O0lBRUE7SUFDQSxNQUFNLFlBQVksR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLDBCQUEyQixDQUFDO0lBRXpFLElBQUssWUFBWSxFQUFHO01BQ25CLFlBQVksQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFFLGtCQUFtQixDQUFDO0lBQ2pEOztJQUVBO0lBQ0EsUUFBUSxDQUFDLGdCQUFnQixDQUFFLHFCQUFzQixDQUFDLENBQUMsT0FBTyxDQUFJLEVBQUUsSUFBTTtNQUNyRSxFQUFFLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBRSxrQkFBbUIsQ0FBQztNQUV0QyxNQUFNLFFBQVEsR0FBRyxFQUFFLENBQUMsYUFBYSxDQUFFLFVBQVcsQ0FBQztNQUUvQyxJQUFLLFFBQVEsRUFBRztRQUNmLFFBQVEsQ0FBQyxRQUFRLEdBQUcsSUFBSTtNQUN6QjtJQUNELENBQUUsQ0FBQztJQUVILE1BQU0sWUFBWSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUscUJBQXNCLENBQUM7SUFDcEUsSUFBSyxZQUFZLEVBQUc7TUFDbkIsWUFBWSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUUsa0JBQW1CLENBQUM7SUFDakQ7O0lBRUE7SUFDQSxRQUFRLENBQUMsYUFBYSxDQUFDLElBQUksV0FBVyxDQUFDLDhCQUE4QixFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7RUFDNUU7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxpQkFBaUIsQ0FBQSxFQUFHO0lBQzVCLE1BQU0sSUFBSSxHQUFHLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBRSxvQkFBcUIsQ0FBQztJQUM5RCxNQUFNLGNBQWMsR0FBRyxRQUFRLENBQUMsZ0JBQWdCLENBQUUsMkJBQTRCLENBQUM7SUFFL0UsSUFBSyxDQUFFLElBQUksQ0FBQyxNQUFNLEVBQUc7TUFDcEI7SUFDRDs7SUFFQTtBQUNGO0FBQ0E7QUFDQTtBQUNBO0lBQ0UsU0FBUyxvQkFBb0IsQ0FBRSxZQUFZLEVBQUc7TUFDN0MsY0FBYyxDQUFDLE9BQU8sQ0FBSSxPQUFPLElBQU07UUFDdEMsT0FBTyxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsY0FBYyxFQUFFLENBQUUsT0FBTyxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUUsWUFBYSxDQUFFLENBQUM7TUFDekYsQ0FBRSxDQUFDO0lBQ0o7O0lBRUE7QUFDRjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7SUFDRSxTQUFTLGlCQUFpQixDQUFFLFNBQVMsRUFBRztNQUN2QyxNQUFNLEtBQUssR0FBRyxTQUFTLENBQUMsWUFBWSxDQUFFLFlBQWEsQ0FBQztNQUVwRCxJQUFLLENBQUUsS0FBSyxFQUFHO1FBQ2Q7TUFDRDtNQUVBLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBRSxzQkFBdUIsQ0FBQyxDQUFDLE9BQU8sQ0FBSSxJQUFJLElBQU07UUFDeEU7UUFDQSxJQUFJLENBQUMsV0FBVyxHQUFHLEtBQUs7TUFDekIsQ0FBRSxDQUFDO0lBQ0o7O0lBRUE7QUFDRjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0lBQ0UsU0FBUyx1QkFBdUIsQ0FBRSxNQUFNLEVBQUc7TUFDMUMsTUFBTSxJQUFJLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSxzQkFBdUIsQ0FBQztNQUM3RCxJQUFLLENBQUUsSUFBSSxFQUFHO1FBQ2I7TUFDRDtNQUNBLE1BQU0sV0FBVyxHQUFHLFdBQVcsS0FBSyxNQUFNO01BQzFDLE1BQU0sR0FBRyxHQUFHLFdBQVcsR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLFlBQVksR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLFdBQVc7TUFDOUUsTUFBTSxFQUFFLEdBQUksV0FBVyxHQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsV0FBVyxHQUFJLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVTtNQUM3RSxJQUFLLEdBQUcsRUFBRztRQUNWLElBQUksQ0FBQyxJQUFJLEdBQUcsR0FBRztNQUNoQjtNQUNBLElBQUssRUFBRSxFQUFHO1FBQ1QsSUFBSSxDQUFDLE9BQU8sQ0FBQyxRQUFRLEdBQUcsRUFBRTtNQUMzQjtJQUNEO0lBRUEsSUFBSSxDQUFDLE9BQU8sQ0FBSSxHQUFHLElBQU07TUFDeEIsR0FBRyxDQUFDLGdCQUFnQixDQUFFLE9BQU8sRUFBRSxNQUFNO1FBQ3BDLE1BQU0sTUFBTSxHQUFHLEdBQUcsQ0FBQyxZQUFZLENBQUUsaUJBQWtCLENBQUM7UUFFcEQsSUFBSyxDQUFFLE1BQU0sRUFBRztVQUNmO1FBQ0Q7O1FBRUE7UUFDQSxJQUFJLENBQUMsT0FBTyxDQUFJLENBQUMsSUFBTSxDQUFDLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSwyQkFBNEIsQ0FBRSxDQUFDO1FBQzFFLEdBQUcsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFFLDJCQUE0QixDQUFDOztRQUVoRDtRQUNBLG9CQUFvQixDQUFFLE1BQU8sQ0FBQzs7UUFFOUI7UUFDQSxpQkFBaUIsQ0FBRSxHQUFJLENBQUM7UUFDeEIsdUJBQXVCLENBQUUsTUFBTyxDQUFDO1FBQ2pDLG9CQUFvQixDQUFDLENBQUM7O1FBRXRCO1FBQ0EsTUFBTSxZQUFZLEdBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQyxVQUFVLENBQUM7UUFDeEQsSUFBSSxZQUFZLEdBQUcsWUFBWSxDQUFDLEtBQUs7O1FBRXJDO1FBQ0EsTUFBTSxXQUFXLEdBQUcsY0FBYyxLQUFLLE1BQU0sR0FBRyxRQUFRLEdBQUcsV0FBVztRQUV0RSxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBRTtVQUNuQixJQUFJLEVBQUUsZ0NBQWdDO1VBQ3RDLE1BQU0sRUFBRSxNQUFNO1VBQ2QsSUFBSSxFQUFFO1lBQUUsTUFBTSxFQUFFO1VBQVk7UUFDN0IsQ0FBRSxDQUFDLENBQUMsSUFBSSxDQUFFLFFBQVEsSUFBSztVQUN0QjtVQUNBLFlBQVksQ0FBQyxLQUFLLEdBQUcsV0FBVzs7VUFFaEM7VUFDQSw0QkFBNEIsQ0FBRSxXQUFXLEVBQUUsUUFBUSxDQUFDLDJCQUE0QixDQUFDO1FBQ2xGLENBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxNQUFNO1VBQ2Y7VUFDQSxZQUFZLENBQUMsS0FBSyxHQUFHLFlBQVk7UUFDbEMsQ0FBRSxDQUFDO01BQ0osQ0FBRSxDQUFDO0lBQ0osQ0FBRSxDQUFDOztJQUVIO0lBQ0EsTUFBTSxTQUFTLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSw0QkFBNkIsQ0FBQztJQUN4RSxNQUFNLFlBQVksR0FBRyxTQUFTLEdBQUcsU0FBUyxDQUFDLFlBQVksQ0FBRSxpQkFBa0IsQ0FBQyxHQUFHLFdBQVc7SUFFMUYsSUFBSyxZQUFZLEVBQUc7TUFDbkIsb0JBQW9CLENBQUUsWUFBYSxDQUFDO01BQ3BDLG9CQUFvQixDQUFDLENBQUM7SUFDdkI7O0lBRUE7SUFDQSxJQUFLLFNBQVMsRUFBRztNQUNoQixpQkFBaUIsQ0FBRSxTQUFVLENBQUM7TUFDOUIsdUJBQXVCLENBQUUsWUFBYSxDQUFDO0lBQ3hDO0VBQ0Q7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxrQkFBa0IsQ0FBQSxFQUFHO0lBQzdCLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBRSxPQUFPLEVBQUksS0FBSyxJQUFNO01BQ2hELE1BQU0sTUFBTSxHQUFHLEtBQUssQ0FBQyxNQUFNLENBQUMsT0FBTyxDQUFFLGdCQUFpQixDQUFDO01BQ3ZELElBQUssQ0FBRSxNQUFNLEVBQUc7UUFDZjtNQUNEO01BRUEsTUFBTSxRQUFRLEdBQUcsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsdUJBQXdCLENBQUM7TUFDbkUsTUFBTSxDQUFDLFlBQVksQ0FBRSxjQUFjLEVBQUUsUUFBUSxHQUFHLE1BQU0sR0FBRyxPQUFRLENBQUM7TUFDbEUsTUFBTSxDQUFDLFFBQVEsR0FBRyxJQUFJO01BRXRCLE1BQU0sU0FBUyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsb0NBQXFDLENBQUM7TUFDaEYsSUFBSyxTQUFTLEVBQUc7UUFDaEIsU0FBUyxDQUFDLFNBQVMsR0FBRyx3QkFBd0I7TUFDL0M7TUFFQSxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBRTtRQUNuQixJQUFJLEVBQUUsK0JBQStCO1FBQ3JDLE1BQU0sRUFBRSxNQUFNO1FBQ2QsSUFBSSxFQUFFO1VBQUUsTUFBTSxFQUFFLFFBQVEsR0FBRyxDQUFDLEdBQUc7UUFBRTtNQUNsQyxDQUFFLENBQUMsQ0FBQyxJQUFJLENBQUUsTUFBTTtRQUNmO1FBQ0EsSUFBSyxTQUFTLEVBQUc7VUFDaEIsU0FBUyxDQUFDLFNBQVMsR0FBRyx3QkFBd0I7UUFDL0M7UUFFQSxNQUFNLENBQUMsUUFBUSxHQUFHLEtBQUs7O1FBRXZCO1FBQ0EsUUFBUSxDQUFDLGFBQWEsQ0FBQyxrQkFBa0IsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBRWxELDRCQUE0QixDQUFFLFdBQVcsRUFBRSxRQUFTLENBQUM7UUFFckQsTUFBTSxlQUFlLEdBQUcsTUFBTSxDQUFDLE9BQU8sQ0FBRSxpQkFBa0IsQ0FBQztRQUMzRCxJQUFLLENBQUUsZUFBZSxFQUFHO1VBQ3hCO1FBQ0Q7UUFFQSxlQUFlLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSx3QkFBd0IsRUFBRSxRQUFTLENBQUM7UUFDdEUsZUFBZSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQy9CLDhCQUE4QixFQUM5QixRQUFRLElBQUksR0FBRyxLQUFLLGVBQWUsQ0FBQyxPQUFPLENBQUMsV0FDN0MsQ0FBQztRQUVELE1BQU0sT0FBTyxHQUFHLGVBQWUsQ0FBQyxPQUFPLENBQUUsbUJBQW9CLENBQUM7UUFDOUQsSUFBSyxPQUFPLEVBQUc7VUFDZCxPQUFPLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSwwQkFBMEIsRUFBRSxRQUFTLENBQUM7UUFDakU7UUFFQSxvQkFBb0IsQ0FBQyxDQUFDO1FBRXRCLE1BQU0sT0FBTyxHQUFHLFFBQVEsR0FBRyxZQUFZLEdBQUcsWUFBWTtRQUV0RCxNQUFNLFVBQVUsR0FBRyxlQUFlLENBQUMsYUFBYSxDQUFFLDBCQUEyQixDQUFDO1FBRTlFLElBQUssVUFBVSxJQUFJLGVBQWUsQ0FBQyxPQUFPLENBQUUsT0FBTyxDQUFFLEVBQUc7VUFDdkQsVUFBVSxDQUFDLFdBQVcsR0FBRyxlQUFlLENBQUMsT0FBTyxDQUFFLE9BQU8sQ0FBRTtRQUM1RDtRQUVBLE1BQU0sVUFBVSxHQUFHLFFBQVEsR0FBRyxlQUFlLEdBQUcsZUFBZTtRQUMvRCxNQUFNLFNBQVMsR0FBRyxlQUFlLENBQUMsYUFBYSxDQUFFLDZCQUE4QixDQUFDO1FBRWhGLElBQUssU0FBUyxJQUFJLGVBQWUsQ0FBQyxPQUFPLENBQUUsVUFBVSxDQUFFLEVBQUc7VUFDekQsU0FBUyxDQUFDLFdBQVcsR0FBRyxlQUFlLENBQUMsT0FBTyxDQUFFLFVBQVUsQ0FBRTtRQUM5RDtNQUNELENBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBRSxNQUFNO1FBQ2hCO1FBQ0EsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsdUJBQXVCLEVBQUUsQ0FBRSxRQUFTLENBQUM7UUFDOUQsTUFBTSxDQUFDLFlBQVksQ0FBRSxjQUFjLEVBQUUsQ0FBRSxRQUFRLEdBQUcsTUFBTSxHQUFHLE9BQVEsQ0FBQztRQUNwRSxNQUFNLENBQUMsUUFBUSxHQUFHLEtBQUs7O1FBRXZCO1FBQ0EsSUFBSyxTQUFTLEVBQUc7VUFDaEIsU0FBUyxDQUFDLFNBQVMsR0FBRyx3QkFBd0I7UUFDL0M7TUFDRCxDQUFFLENBQUM7SUFDSixDQUFFLENBQUM7RUFDSjtFQUNBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsZUFBZSxDQUFBLEVBQUc7SUFDMUIsUUFBUSxDQUFDLGdCQUFnQixDQUFFLE9BQU8sRUFBSSxLQUFLLElBQU07TUFDaEQsTUFBTSxNQUFNLEdBQUcsS0FBSyxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUUscURBQXNELENBQUM7TUFDNUYsSUFBSyxDQUFFLE1BQU0sRUFBRztRQUNmO01BQ0Q7TUFFQSxNQUFNLENBQUMsUUFBUSxHQUFHLElBQUk7TUFFdEIsTUFBTSxPQUFPLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSxtQkFBb0IsQ0FBQztNQUU3RCxJQUFLLE9BQU8sRUFBRztRQUNkLE9BQU8sQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFFLDRCQUE2QixDQUFDO01BQ3REO01BRUEsTUFBTSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUU7UUFDbkIsSUFBSSxFQUFFLHdDQUF3QztRQUM5QyxNQUFNLEVBQUU7TUFDVCxDQUFFLENBQUMsQ0FBQyxJQUFJLENBQUksUUFBUSxJQUFNO1FBQ3pCLE1BQU0sQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFFLGNBQWUsQ0FBQztRQUN0QyxvQkFBb0IsQ0FBRSxRQUFRLENBQUMsS0FBSyxFQUFFLFFBQVEsQ0FBQyxLQUFNLENBQUM7UUFFdEQsSUFBSyxPQUFPLEVBQUc7VUFDZCxPQUFPLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSw0QkFBNkIsQ0FBQztRQUN6RDtRQUVBLElBQUssUUFBUSxDQUFDLFVBQVUsRUFBRztVQUMxQixNQUFNLFFBQVEsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLG1DQUFvQyxDQUFDO1VBRTlFLElBQUssUUFBUSxFQUFHO1lBQ2YsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1VBQ2xCO1VBRUEsTUFBTSxjQUFjLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSxtQkFBb0IsQ0FBQztVQUVwRSxJQUFLLGNBQWMsRUFBRztZQUNyQixjQUFjLENBQUMsa0JBQWtCLENBQUUsYUFBYSxFQUFFLFFBQVEsQ0FBQyxVQUFXLENBQUM7VUFDeEU7UUFDRDs7UUFFQTtRQUNBLElBQUssQ0FBQyxLQUFLLFFBQVEsQ0FBQyxLQUFLLEVBQUc7VUFDM0IsUUFBUSxDQUFDLGFBQWEsQ0FBRSxJQUFJLFdBQVcsQ0FBRSw2QkFBOEIsQ0FBRSxDQUFDO1FBQzNFOztRQUVBO1FBQ0EsSUFBSyxRQUFRLENBQUMsZ0NBQWdDLEVBQUc7VUFDaEQsMkJBQTJCLENBQUMsQ0FBQztRQUM5Qjs7UUFFQTtRQUNBLDhCQUE4QixDQUFFLFFBQVEsQ0FBQyxxQkFBc0IsQ0FBQztNQUNqRSxDQUFFLENBQUMsQ0FBQyxLQUFLLENBQUUsTUFBTTtRQUNoQixNQUFNLENBQUMsUUFBUSxHQUFHLEtBQUs7UUFFdkIsSUFBSyxPQUFPLEVBQUc7VUFDZCxPQUFPLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSw0QkFBNkIsQ0FBQztRQUN6RDtNQUNELENBQUUsQ0FBQztJQUNKLENBQUUsQ0FBQztFQUNKO0VBQ0E7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxXQUFXLENBQUEsRUFBRztJQUN0QixNQUFNLEtBQUssR0FBRyxRQUFRLENBQUMsY0FBYyxDQUFFLHdCQUF5QixDQUFDO0lBQ2pFLE1BQU0sTUFBTSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsMkJBQTRCLENBQUM7SUFFcEUsSUFBSyxDQUFFLEtBQUssSUFBSSxDQUFFLE1BQU0sRUFBRztNQUMxQjtJQUNEO0lBRUEsU0FBUyxVQUFVLENBQUMsS0FBSyxFQUFFO01BQzFCLElBQUk7UUFDSCxNQUFNLEdBQUcsR0FBRyxJQUFJLEdBQUcsQ0FBQyxLQUFLLENBQUM7UUFDMUIsT0FBTyxHQUFHLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsSUFBSSxHQUFHLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBRyxDQUFDO01BQzlFLENBQUMsQ0FBQyxNQUFNO1FBQ1AsT0FBTyxLQUFLO01BQ2I7SUFDRDtJQUVBLFNBQVMsVUFBVSxDQUFBLEVBQUc7TUFDckIsTUFBTSxHQUFHLEdBQUcsS0FBSyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsQ0FBQztNQUU5QixJQUFJLENBQUMsVUFBVSxDQUFDLEdBQUcsQ0FBQyxFQUFFO1FBQ3JCLEtBQUssQ0FBQywwQkFBMEIsQ0FBQztRQUNqQztNQUNEOztNQUVBO01BQ0EsS0FBSyxDQUFDLFFBQVEsR0FBRyxJQUFJO01BQ3JCLE1BQU0sQ0FBQyxRQUFRLEdBQUcsSUFBSTtNQUN0QixNQUFNLE9BQU8sR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLG1CQUFvQixDQUFDO01BRTdELElBQUssT0FBTyxFQUFHO1FBQ2QsT0FBTyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUUsNEJBQTZCLENBQUM7TUFDdEQ7TUFFQSxNQUFNLENBQUMsRUFBRSxDQUFDLFFBQVEsQ0FBRTtRQUNuQixJQUFJLEVBQUUsK0JBQStCO1FBQ3JDLE1BQU0sRUFBRSxNQUFNO1FBQ2QsSUFBSSxFQUFFO1VBQUU7UUFBSTtNQUNiLENBQUUsQ0FBQyxDQUFDLElBQUksQ0FBSSxRQUFRLElBQU07UUFDekIsS0FBSyxDQUFDLEtBQUssR0FBRyxFQUFFO1FBQ2hCLEtBQUssQ0FBQyxRQUFRLEdBQUcsS0FBSztRQUN0QixNQUFNLENBQUMsUUFBUSxHQUFHLEtBQUs7UUFDdkIsYUFBYSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUUsY0FBZSxDQUFDO1FBQzdDLG9CQUFvQixDQUFFLFFBQVEsQ0FBQyxLQUFLLEVBQUUsUUFBUSxDQUFDLEtBQU0sQ0FBQztRQUV0RCxJQUFLLE9BQU8sRUFBRztVQUNkLE9BQU8sQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLDRCQUE2QixDQUFDO1FBQ3pEOztRQUVBO1FBQ0EsSUFBSyxRQUFRLENBQUMsVUFBVSxFQUFHO1VBQzFCLE1BQU0sUUFBUSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUNBQW9DLENBQUM7VUFFOUUsSUFBSyxRQUFRLEVBQUc7WUFDZixRQUFRLENBQUMsTUFBTSxDQUFDLENBQUM7VUFDbEI7VUFFQSxNQUFNLGNBQWMsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLG1CQUFvQixDQUFDO1VBRXBFLElBQUssY0FBYyxFQUFHO1lBQ3JCLGNBQWMsQ0FBQyxrQkFBa0IsQ0FBRSxhQUFhLEVBQUUsUUFBUSxDQUFDLFVBQVcsQ0FBQztVQUN4RTtRQUNEOztRQUVBO1FBQ0EsSUFBSyxDQUFDLEtBQUssUUFBUSxDQUFDLEtBQUssRUFBRztVQUMzQixRQUFRLENBQUMsYUFBYSxDQUFFLElBQUksV0FBVyxDQUFFLDZCQUE4QixDQUFFLENBQUM7UUFDM0U7UUFFQSxJQUFLLFFBQVEsQ0FBQyxLQUFLLEtBQUssUUFBUSxDQUFDLEtBQUssRUFBRztVQUN4QztVQUNBLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUJBQW9CLENBQUMsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFFLDRCQUE2QixDQUFDO1VBQzNGLE1BQU0sY0FBYyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUNBQW9DLENBQUM7VUFDcEYsSUFBSyxjQUFjLEVBQUc7WUFDckIsY0FBYyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUUsdUJBQXdCLENBQUM7VUFDeEQ7VUFDQSxNQUFNLFVBQVUsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLDJCQUE0QixDQUFDO1VBQ3hFLElBQUssVUFBVSxFQUFHO1lBQ2pCLFVBQVUsQ0FBQyxRQUFRLEdBQUcsSUFBSTtVQUMzQjtVQUNBLGtCQUFrQixDQUFFLElBQUssQ0FBQztVQUMxQixRQUFRLENBQUMsYUFBYSxDQUFFLElBQUksV0FBVyxDQUFFLDZCQUE4QixDQUFFLENBQUM7UUFDM0U7O1FBRUE7UUFDQSxJQUFLLFFBQVEsQ0FBQyxnQ0FBZ0MsRUFBRztVQUNoRCwyQkFBMkIsQ0FBQyxDQUFDO1FBQzlCOztRQUVBO1FBQ0EsOEJBQThCLENBQUUsUUFBUSxDQUFDLHFCQUFzQixDQUFDO01BQ2pFLENBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBRSxNQUFNO1FBQ2hCLEtBQUssQ0FBQyxRQUFRLEdBQUcsS0FBSztRQUN0QixNQUFNLENBQUMsUUFBUSxHQUFHLEtBQUs7UUFFdkIsSUFBSyxPQUFPLEVBQUc7VUFDZCxPQUFPLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSw0QkFBNkIsQ0FBQztRQUN6RDtNQUNELENBQUUsQ0FBQztJQUNKO0lBRUEsTUFBTSxDQUFDLGdCQUFnQixDQUFFLE9BQU8sRUFBRSxVQUFXLENBQUM7SUFFOUMsS0FBSyxDQUFDLGdCQUFnQixDQUFFLFNBQVMsRUFBSSxDQUFDLElBQU07TUFDM0MsSUFBSyxPQUFPLEtBQUssQ0FBQyxDQUFDLEdBQUcsRUFBRztRQUN4QixDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7UUFDbEIsVUFBVSxDQUFDLENBQUM7TUFDYjtJQUNELENBQUUsQ0FBQztFQUNKOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsY0FBYyxDQUFBLEVBQUc7SUFDekIsTUFBTSxTQUFTLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSx5QkFBMEIsQ0FBQztJQUVyRSxJQUFLLENBQUUsU0FBUyxFQUFHO01BQ2xCO0lBQ0Q7SUFFQSxTQUFTLENBQUMsYUFBYSxDQUFDLGdCQUFnQixDQUFFLE9BQU8sRUFBSSxDQUFDLElBQU07TUFDM0QsTUFBTSxNQUFNLEdBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUUseUJBQTBCLENBQUM7TUFFNUQsSUFBSyxDQUFFLE1BQU0sRUFBRztRQUNmO01BQ0Q7TUFFQSxNQUFNLEVBQUUsR0FBRyxNQUFNLENBQUMsT0FBTyxDQUFDLEVBQUU7TUFFNUIsSUFBSyxDQUFFLEVBQUUsRUFBRztRQUNYO01BQ0Q7TUFFQSxNQUFNLENBQUMsUUFBUSxHQUFHLElBQUk7TUFFdEIsTUFBTSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUU7UUFDbkIsSUFBSSxFQUFFLGlDQUFrQyxFQUFFLEVBQUc7UUFDN0MsTUFBTSxFQUFFO01BQ1QsQ0FBRSxDQUFDLENBQUMsSUFBSSxDQUFJLFFBQVEsSUFBTTtRQUN6QixvQkFBb0IsQ0FBRSxRQUFRLENBQUMsS0FBSyxFQUFFLFFBQVEsQ0FBQyxLQUFNLENBQUM7UUFFdEQsSUFBSyxRQUFRLENBQUMsVUFBVSxFQUFHO1VBQzFCLE1BQU0sUUFBUSxHQUFHLFNBQVMsQ0FBQyxhQUFhLENBQUMsYUFBYSxDQUFFLG1DQUFvQyxDQUFDO1VBRTdGLElBQUssUUFBUSxFQUFHO1lBQ2YsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1VBQ2xCO1VBRUEsTUFBTSxjQUFjLEdBQUcsU0FBUyxDQUFDLGFBQWEsQ0FBQyxhQUFhLENBQUUsbUJBQW9CLENBQUM7VUFFbkYsSUFBSyxjQUFjLEVBQUc7WUFDckIsY0FBYyxDQUFDLGtCQUFrQixDQUFFLGFBQWEsRUFBRSxRQUFRLENBQUMsVUFBVyxDQUFDO1VBQ3hFO1FBQ0Q7O1FBRUE7UUFDQSxJQUFLLENBQUMsS0FBSyxRQUFRLENBQUMsS0FBSyxFQUFHO1VBQzNCO1VBQ0EsUUFBUSxDQUFDLGFBQWEsQ0FBRSxtQ0FBb0MsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDO1VBRXRFLE1BQU0sV0FBVyxHQUFHLFNBQVMsQ0FBQyxhQUFhLENBQUUsNkJBQThCLENBQUM7VUFFNUUsSUFBSyxXQUFXLEVBQUc7WUFDbEIsV0FBVyxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsY0FBZSxDQUFDO1lBQzlDLFdBQVcsQ0FBQyxRQUFRLEdBQUcsS0FBSztVQUM3QjtRQUNEO1FBRUEsSUFBSyxRQUFRLENBQUMsS0FBSyxHQUFHLFFBQVEsQ0FBQyxLQUFLLEVBQUc7VUFDdEM7VUFDQSxRQUFRLENBQUMsYUFBYSxDQUFFLG1CQUFvQixDQUFDLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBRSw0QkFBNkIsQ0FBQztVQUM5RixNQUFNLGNBQWMsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLG1DQUFvQyxDQUFDO1VBQ3BGLElBQUssY0FBYyxFQUFHO1lBQ3JCLGNBQWMsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFFLHVCQUF3QixDQUFDO1VBQzNEO1VBQ0EsTUFBTSxVQUFVLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSwyQkFBNEIsQ0FBQztVQUN4RSxJQUFLLFVBQVUsRUFBRztZQUNqQixVQUFVLENBQUMsUUFBUSxHQUFHLEtBQUs7VUFDNUI7VUFDQSxrQkFBa0IsQ0FBRSxLQUFNLENBQUM7O1VBRTNCO1VBQ0EsSUFBSyxRQUFRLENBQUMsS0FBSyxLQUFLLFFBQVEsQ0FBQyxLQUFLLEdBQUcsQ0FBQyxFQUFHO1lBQzVDLFFBQVEsQ0FBQyxhQUFhLENBQUUsSUFBSSxXQUFXLENBQUUsOEJBQStCLENBQUUsQ0FBQztVQUM1RTtRQUNEO1FBRUEsSUFBSyxDQUFDLEtBQUssUUFBUSxDQUFDLEtBQUssRUFBRztVQUMzQjtVQUNBLDhCQUE4QixDQUFFLFFBQVEsQ0FBQyxxQkFBc0IsQ0FBQztRQUNqRTtNQUVELENBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBRSxNQUFNO1FBQ2hCLE1BQU0sQ0FBQyxRQUFRLEdBQUcsS0FBSztNQUN4QixDQUFFLENBQUM7SUFDSixDQUFFLENBQUM7RUFDSjtBQUNELENBQUMsRUFBSSxRQUFTLENBQUM7Ozs7O0FDdHBCZixTQUFTLGdCQUFnQixDQUFDLE9BQU8sRUFBQztFQUM5QixNQUFNLEtBQUssR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUM7RUFDeEIsTUFBTSxLQUFLLEdBQUksT0FBTyxHQUFHLElBQUksR0FBSSxLQUFLO0VBQ3RDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUcsS0FBSyxHQUFDLElBQUksR0FBSSxFQUFHLENBQUM7RUFDL0MsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBRyxLQUFLLEdBQUMsSUFBSSxHQUFDLEVBQUUsR0FBSSxFQUFHLENBQUM7RUFDbEQsTUFBTSxLQUFLLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBRyxLQUFLLElBQUUsSUFBSSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsR0FBSSxFQUFHLENBQUM7RUFDckQsTUFBTSxJQUFJLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBRSxLQUFLLElBQUUsSUFBSSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFFLENBQUM7RUFFaEQsT0FBTztJQUNILEtBQUs7SUFDTCxJQUFJO0lBQ0osS0FBSztJQUNMLE9BQU87SUFDUDtFQUNKLENBQUM7QUFDTDtBQUVBLFNBQVMsZUFBZSxDQUFDLEVBQUUsRUFBRSxPQUFPLEVBQUU7RUFDbEMsTUFBTSxLQUFLLEdBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQyxFQUFFLENBQUM7RUFFekMsSUFBSSxLQUFLLEtBQUssSUFBSSxFQUFFO0lBQ2hCO0VBQ0o7RUFFQSxNQUFNLFFBQVEsR0FBRyxLQUFLLENBQUMsYUFBYSxDQUFDLHdCQUF3QixDQUFDO0VBQzlELE1BQU0sU0FBUyxHQUFHLEtBQUssQ0FBQyxhQUFhLENBQUMseUJBQXlCLENBQUM7RUFDaEUsTUFBTSxXQUFXLEdBQUcsS0FBSyxDQUFDLGFBQWEsQ0FBQywyQkFBMkIsQ0FBQztFQUNwRSxNQUFNLFdBQVcsR0FBRyxLQUFLLENBQUMsYUFBYSxDQUFDLDJCQUEyQixDQUFDO0VBRXBFLFNBQVMsV0FBVyxDQUFBLEVBQUc7SUFDbkIsTUFBTSxDQUFDLEdBQUcsZ0JBQWdCLENBQUMsT0FBTyxDQUFDO0lBRW5DLElBQUksQ0FBQyxDQUFDLEtBQUssR0FBRyxDQUFDLEVBQUU7TUFDYixhQUFhLENBQUMsWUFBWSxDQUFDO01BRTNCO0lBQ0o7SUFFQSxRQUFRLENBQUMsU0FBUyxHQUFHLENBQUMsQ0FBQyxJQUFJO0lBQzNCLFNBQVMsQ0FBQyxTQUFTLEdBQUcsQ0FBQyxHQUFHLEdBQUcsQ0FBQyxDQUFDLEtBQUssRUFBRSxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDL0MsV0FBVyxDQUFDLFNBQVMsR0FBRyxDQUFDLEdBQUcsR0FBRyxDQUFDLENBQUMsT0FBTyxFQUFFLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUNuRCxXQUFXLENBQUMsU0FBUyxHQUFHLENBQUMsR0FBRyxHQUFHLENBQUMsQ0FBQyxPQUFPLEVBQUUsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO0VBQ3ZEO0VBRUEsV0FBVyxDQUFDLENBQUM7RUFDYixNQUFNLFlBQVksR0FBRyxXQUFXLENBQUMsV0FBVyxFQUFFLElBQUksQ0FBQztBQUN2RDtBQUVBLFNBQVMsVUFBVSxDQUFDLEVBQUUsRUFBRSxPQUFPLEVBQUU7RUFDaEMsTUFBTSxLQUFLLEdBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQyxFQUFFLENBQUM7RUFDekMsTUFBTSxNQUFNLEdBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQywrQkFBK0IsQ0FBQztFQUN2RSxNQUFNLE9BQU8sR0FBRyxRQUFRLENBQUMsY0FBYyxDQUFDLDRCQUE0QixDQUFDO0VBRXJFLElBQUksS0FBSyxLQUFLLElBQUksRUFBRTtJQUNuQjtFQUNEO0VBRUEsU0FBUyxXQUFXLENBQUEsRUFBRztJQUN0QixNQUFNLEtBQUssR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUM7SUFDeEIsTUFBTSxTQUFTLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBRSxDQUFHLE9BQU8sR0FBRyxJQUFJLEdBQUksS0FBSyxJQUFLLElBQUssQ0FBQztJQUVuRSxJQUFJLFNBQVMsSUFBSSxDQUFDLEVBQUU7TUFDbkIsYUFBYSxDQUFDLGFBQWEsQ0FBQztNQUU1QixJQUFJLE1BQU0sS0FBSyxJQUFJLEVBQUU7UUFDcEIsTUFBTSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsUUFBUSxDQUFDO01BQy9CO01BRUEsSUFBSSxPQUFPLEtBQUssSUFBSSxFQUFFO1FBQ3JCLE9BQU8sQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQztNQUNuQztNQUVBLElBQUssZ0JBQWdCLENBQUMsYUFBYSxFQUFHO1FBQ3JDO01BQ0Q7TUFFQSxNQUFNLElBQUksR0FBRyxJQUFJLFFBQVEsQ0FBQyxDQUFDO01BRTNCLElBQUksQ0FBQyxNQUFNLENBQUUsUUFBUSxFQUFFLG1CQUFvQixDQUFDO01BQzVDLElBQUksQ0FBQyxNQUFNLENBQUUsT0FBTyxFQUFFLGdCQUFnQixDQUFDLEtBQU0sQ0FBQztNQUU5QyxLQUFLLENBQUUsT0FBTyxFQUFFO1FBQ2YsTUFBTSxFQUFFLE1BQU07UUFDZCxXQUFXLEVBQUUsYUFBYTtRQUMxQixJQUFJLEVBQUU7TUFDUCxDQUFFLENBQUM7TUFFSDtJQUNEO0lBRUEsS0FBSyxDQUFDLFNBQVMsR0FBRyxTQUFTO0VBQzVCO0VBRUEsV0FBVyxDQUFDLENBQUM7RUFDYixNQUFNLGFBQWEsR0FBRyxXQUFXLENBQUUsV0FBVyxFQUFFLElBQUksQ0FBQztBQUN0RDtBQUVBLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxFQUFFO0VBQ1gsSUFBSSxDQUFDLEdBQUcsR0FBRyxTQUFTLEdBQUcsQ0FBQSxFQUFHO0lBQ3hCLE9BQU8sSUFBSSxJQUFJLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDO0VBQzdCLENBQUM7QUFDTDtBQUVBLElBQUksT0FBTyxnQkFBZ0IsQ0FBQyxTQUFTLEtBQUssV0FBVyxFQUFFO0VBQ25ELGVBQWUsQ0FBQyx3QkFBd0IsRUFBRSxnQkFBZ0IsQ0FBQyxTQUFTLENBQUM7QUFDekU7QUFFQSxJQUFJLE9BQU8sZ0JBQWdCLENBQUMsa0JBQWtCLEtBQUssV0FBVyxFQUFFO0VBQzVELGVBQWUsQ0FBQyx3QkFBd0IsRUFBRSxnQkFBZ0IsQ0FBQyxrQkFBa0IsQ0FBQztBQUNsRjtBQUVBLElBQUksT0FBTyxnQkFBZ0IsQ0FBQyxlQUFlLEtBQUssV0FBVyxFQUFFO0VBQ3pELFVBQVUsQ0FBQyxvQkFBb0IsRUFBRSxnQkFBZ0IsQ0FBQyxlQUFlLENBQUM7QUFDdEU7Ozs7O0FDakhBLE9BQUE7QUFFQSxJQUFJLENBQUMsR0FBRyxNQUFNO0FBQ2QsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUssQ0FBQyxZQUFVO0VBR3hCO0FBQ0o7QUFDQTs7RUFFQyxTQUFTLGVBQWUsQ0FBQyxLQUFLLEVBQUM7SUFDOUIsSUFBSSxRQUFRLEVBQUUsU0FBUztJQUV2QixLQUFLLEdBQU8sQ0FBQyxDQUFFLEtBQU0sQ0FBQztJQUN0QixRQUFRLEdBQUksS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7SUFDNUIsU0FBUyxHQUFHLENBQUMsQ0FBQyxnQkFBZ0IsR0FBRyxRQUFRLEdBQUcsSUFBSSxDQUFDOztJQUVqRDtJQUNBLElBQUcsS0FBSyxDQUFDLEVBQUUsQ0FBQyxVQUFVLENBQUMsRUFBQztNQUN2QixTQUFTLENBQUMsUUFBUSxDQUFDLFlBQVksQ0FBQztNQUVoQyxTQUFTLENBQUMsSUFBSSxDQUFDLFlBQVc7UUFDekIsSUFBSyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLHNCQUFzQixDQUFDLENBQUMsRUFBRSxDQUFDLFVBQVUsQ0FBQyxFQUFFO1VBQ3pELElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsc0JBQXNCLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDO1VBRXhELENBQUMsQ0FBQyxnQkFBZ0IsR0FBRyxFQUFFLEdBQUcsSUFBSSxDQUFDLENBQUMsUUFBUSxDQUFDLFlBQVksQ0FBQztRQUN2RDtNQUNELENBQUMsQ0FBQztJQUNILENBQUMsTUFDRztNQUNILFNBQVMsQ0FBQyxXQUFXLENBQUMsWUFBWSxDQUFDO01BRW5DLFNBQVMsQ0FBQyxJQUFJLENBQUMsWUFBVztRQUN6QixJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLHNCQUFzQixDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztRQUV4RCxDQUFDLENBQUMsZ0JBQWdCLEdBQUcsRUFBRSxHQUFHLElBQUksQ0FBQyxDQUFDLFdBQVcsQ0FBQyxZQUFZLENBQUM7TUFDMUQsQ0FBQyxDQUFDO0lBQ0g7RUFDRDs7RUFFRztBQUNKO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDSSxTQUFTLGlCQUFpQixDQUFFLE1BQU0sRUFBRztJQUNqQyxJQUFJLE9BQU87SUFFWCxJQUFLLENBQUUsTUFBTSxDQUFDLE1BQU0sRUFBRztNQUNuQjtNQUNBLE9BQU8sSUFBSTtJQUNmO0lBRUEsT0FBTyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUUsUUFBUyxDQUFDO0lBRWpDLElBQUssT0FBTyxPQUFPLEtBQUssUUFBUSxFQUFHO01BQy9CO01BQ0EsT0FBTyxJQUFJO0lBQ2Y7SUFFQSxPQUFPLEdBQUcsT0FBTyxDQUFDLE9BQU8sQ0FBRSxZQUFZLEVBQUUsRUFBRyxDQUFDO0lBRTdDLElBQUssRUFBRSxLQUFLLE9BQU8sRUFBRztNQUNsQjtNQUNBLE9BQU8sSUFBSTtJQUNmO0lBRUEsT0FBTyxHQUFHLENBQUMsQ0FBRSxHQUFHLEdBQUcsT0FBUSxDQUFDO0lBRTVCLElBQUssQ0FBRSxPQUFPLENBQUMsTUFBTSxFQUFHO01BQ3BCO01BQ0EsT0FBTyxLQUFLO0lBQ2hCO0lBRUEsSUFBSyxDQUFFLE9BQU8sQ0FBQyxFQUFFLENBQUUsVUFBVyxDQUFDLElBQUksT0FBTyxDQUFDLEVBQUUsQ0FBQyxPQUFPLENBQUMsRUFBRTtNQUNwRDtNQUNBLE9BQU8sS0FBSztJQUNoQjtJQUVOLElBQUssQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLGNBQWMsQ0FBQyxJQUFJLE9BQU8sQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLEVBQUU7TUFDL0Q7TUFDQSxPQUFPLEtBQUs7SUFDYjtJQUNNO0lBQ0EsT0FBTyxpQkFBaUIsQ0FBRSxPQUFPLENBQUMsT0FBTyxDQUFFLFlBQWEsQ0FBRSxDQUFDO0VBQy9EOztFQUVIO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLFNBQVMsQ0FBQyxjQUFjLEVBQUUsaUJBQWlCLEVBQUU7SUFDckQsSUFBSSxRQUFRLEdBQUc7TUFDZCxLQUFLLEVBQUUsaUJBQWlCLENBQUMsR0FBRyxDQUFDLENBQUM7TUFDOUIsUUFBUSxFQUFFLGlCQUFpQixDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7SUFDbkMsQ0FBQztJQUVELElBQUksUUFBUSxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUU7TUFFeEIsSUFBSSxVQUFVLEdBQUcsUUFBUSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBRSxRQUFRLENBQUMsTUFBTSxHQUFHLENBQUMsQ0FBQyxDQUFDO01BQ2xFLElBQUksV0FBVyxHQUFHLFFBQVEsQ0FBQyxHQUFHLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDOztNQUV4QztNQUNBLElBQUksV0FBVyxHQUFHLFVBQVUsR0FBRyxXQUFXO01BRTFDLGNBQWMsQ0FBQyxHQUFHLENBQUMsV0FBVyxDQUFDO0lBQ2hDO0lBQ0E7SUFDQSxJQUFJLENBQUMsY0FBYyxDQUFDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxFQUFFO01BQzNDLGNBQWMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFdBQVcsQ0FBQztNQUN2QyxjQUFjLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxXQUFXLENBQUM7TUFDdkMsY0FBYyxDQUFDLElBQUksQ0FBQyxnQkFBZ0IsRUFBRSxJQUFJLENBQUM7SUFDNUM7O0lBRUE7QUFDRjtBQUNBO0lBQ0UsU0FBUyxXQUFXLENBQUEsRUFBRztNQUN0QixJQUFJLFVBQVUsR0FBRyxjQUFjLENBQUMsR0FBRyxDQUFDLENBQUM7TUFDckMsSUFBSSxVQUFVLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFFO1FBQ3hDLGlCQUFpQixDQUFDLEdBQUcsQ0FBQyxVQUFVLENBQUM7TUFDbEM7SUFDRDs7SUFFQTtBQUNGO0FBQ0E7SUFDRSxTQUFTLFdBQVcsQ0FBQSxFQUFHO01BQ3RCLElBQUksY0FBYyxHQUFHLGlCQUFpQixDQUFDLEdBQUcsQ0FBQyxDQUFDO01BQzVDLGNBQWMsQ0FBQyxHQUFHLENBQUMsY0FBYyxDQUFDO0lBQ25DO0VBRUQ7O0VBRUM7O0VBR0QsU0FBUyxDQUFDLENBQUMsQ0FBQywwQkFBMEIsQ0FBQyxFQUFFLENBQUMsQ0FBQyxxQkFBcUIsQ0FBQyxDQUFDO0VBQ2xFLFNBQVMsQ0FBQyxDQUFDLENBQUMsMEJBQTBCLENBQUMsRUFBRSxDQUFDLENBQUMscUJBQXFCLENBQUMsQ0FBQzs7RUFFbEU7RUFDRyxDQUFDLENBQUUsb0NBQXFDLENBQUMsQ0FBQyxFQUFFLENBQUMsUUFBUSxFQUFFLFlBQVc7SUFDOUQsZUFBZSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztFQUM1QixDQUFDLENBQUM7O0VBRUY7RUFDQSxDQUFDLENBQUUsc0JBQXVCLENBQUMsQ0FBQyxJQUFJLENBQUUsWUFBVztJQUN6QyxJQUFJLE1BQU0sR0FBRyxDQUFDLENBQUUsSUFBSyxDQUFDO0lBRXRCLElBQUssaUJBQWlCLENBQUUsTUFBTyxDQUFDLEVBQUc7TUFDL0IsTUFBTSxDQUFDLFFBQVEsQ0FBRSxZQUFhLENBQUM7SUFDbkM7RUFDSixDQUFFLENBQUM7O0VBS0g7QUFDSjtBQUNBOztFQUVJLElBQUksY0FBYyxHQUFHLENBQUMsQ0FBQyxvQkFBb0IsQ0FBQztFQUM1QyxJQUFJLG1CQUFtQixHQUFHLENBQUMsQ0FBQyx5Q0FBeUMsQ0FBQzs7RUFFdEU7RUFDQSxtQkFBbUIsQ0FBQyxJQUFJLENBQUMsWUFBVTtJQUMvQixlQUFlLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO0VBQzVCLENBQUMsQ0FBQztFQUVGLGNBQWMsQ0FBQyxFQUFFLENBQUMsUUFBUSxFQUFFLFlBQVc7SUFDbkMsY0FBYyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztFQUMzQixDQUFDLENBQUM7RUFFRixTQUFTLGNBQWMsQ0FBQyxLQUFLLEVBQUM7SUFDMUIsSUFBSSxhQUFhLEdBQUcsS0FBSyxDQUFDLElBQUksQ0FBQyxtQkFBbUIsQ0FBQztNQUMvQyxhQUFhLEdBQUcsS0FBSyxDQUFDLElBQUksQ0FBQyxzQkFBc0IsQ0FBQztNQUNsRCxZQUFZLEdBQUcsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLHVCQUF1QixDQUFDO01BQzNELFdBQVcsR0FBRyxZQUFZLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQztNQUM3QyxRQUFRLEdBQUcsS0FBSyxDQUFDLElBQUksQ0FBQyxzQkFBc0IsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7TUFDeEQsU0FBUyxHQUFHLENBQUMsQ0FBQyxnQkFBZ0IsR0FBRyxRQUFRLEdBQUcsSUFBSSxDQUFDOztJQUdyRDtJQUNBLElBQUcsYUFBYSxDQUFDLEVBQUUsQ0FBQyxVQUFVLENBQUMsRUFBQztNQUM1QixhQUFhLENBQUMsUUFBUSxDQUFDLFlBQVksQ0FBQztNQUNwQyxhQUFhLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxLQUFLLENBQUM7TUFDcEMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUM7TUFHdkIsSUFBSSxjQUFjLEdBQUcsYUFBYSxDQUFDLElBQUksQ0FBQyxhQUFhLENBQUM7O01BRXREO01BQ0EsY0FBYyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsWUFBVTtRQUNqQyxhQUFhLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxJQUFJLENBQUM7UUFDbkMsYUFBYSxDQUFDLFdBQVcsQ0FBQyxZQUFZLENBQUM7UUFDdkMsU0FBUyxDQUFDLFFBQVEsQ0FBQyxZQUFZLENBQUM7O1FBRWhDO1FBQ0EsSUFBRyxZQUFZLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBQztVQUN2QixXQUFXLENBQUMsV0FBVyxDQUFDLGdCQUFnQixDQUFDO1VBQ3pDLFdBQVcsQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxLQUFLLENBQUM7UUFDckQ7UUFFQSxPQUFPLEtBQUs7TUFDaEIsQ0FBQyxDQUFDO0lBQ04sQ0FBQyxNQUNHO01BQ0EsV0FBVyxDQUFDLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQztNQUN0QyxXQUFXLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxVQUFVLEVBQUUsSUFBSSxDQUFDO01BQ2hELFdBQVcsQ0FBQyxJQUFJLENBQUMsc0JBQXNCLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxFQUFFLEtBQUssQ0FBQztNQUMvRCxTQUFTLENBQUMsV0FBVyxDQUFDLFlBQVksQ0FBQztJQUN2QztFQUNKOztFQUVBO0FBQ0o7QUFDQTtFQUNJLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLHFCQUFxQixFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQzdELENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUNsQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQztFQUMxQixDQUFFLENBQUM7RUFFSCxDQUFDLENBQUMsdUJBQXVCLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQ2xELENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUNaLENBQUMsQ0FBQyxDQUFDLENBQUMsa0JBQWtCLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDO0VBQ2hFLENBQUMsQ0FBQzs7RUFFTDtBQUNEO0FBQ0E7RUFDQyxJQUFJLHFCQUFxQixHQUFHLEtBQUs7RUFFakMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUscUNBQXFDLEVBQUUsVUFBUyxDQUFDLEVBQUU7SUFDMUUsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO0lBQ2xCLElBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLFFBQVEsQ0FBQyxjQUFjLENBQUMsRUFBQztNQUNuQyxPQUFPLEtBQUs7SUFDYjtJQUNBLElBQUksT0FBTyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxPQUFPLENBQUMsb0JBQW9CLENBQUM7SUFDbkQsT0FBTyxDQUFDLElBQUksQ0FBQyxxQ0FBcUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxjQUFjLENBQUM7SUFDL0UsT0FBTyxDQUFDLElBQUksQ0FBQyw2QkFBNkIsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxZQUFZLENBQUM7SUFDckUsT0FBTyxDQUFDLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxZQUFZLENBQUM7SUFDM0QsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLFFBQVEsQ0FBQyxjQUFjLENBQUM7SUFDaEMsbUJBQW1CLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO0VBRTdCLENBQUUsQ0FBQztFQUdILFNBQVMsbUJBQW1CLENBQUMsSUFBSSxFQUFDO0lBQ2pDLHFCQUFxQixHQUFHLEtBQUs7SUFDN0IsSUFBSSxDQUFDLE9BQU8sQ0FBRSwyQkFBMkIsRUFBRSxDQUFFLElBQUksQ0FBRyxDQUFDO0lBQ3JELElBQUksQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLGFBQWEsQ0FBQyxJQUFJLHFCQUFxQixFQUFFO01BQzNELDBCQUEwQixDQUFDLElBQUksQ0FBQztNQUNoQyxJQUFJLENBQUMsT0FBTyxDQUFFLHVCQUF1QixFQUFFLENBQUUsSUFBSSxDQUFHLENBQUM7TUFDakQsT0FBTyxLQUFLO0lBQ2I7SUFDQSxJQUFJLGFBQWEsR0FBRyxDQUFDLENBQUMsZ0JBQWdCLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxxQkFBcUIsQ0FBQztJQUNqRixhQUFhLENBQUMsUUFBUSxDQUFDLFlBQVksQ0FBQztJQUNwQyxJQUFJLGNBQWMsR0FBRyxhQUFhLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQzs7SUFFdEQ7SUFDQSxjQUFjLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxZQUFVO01BQ3BDLGFBQWEsQ0FBQyxXQUFXLENBQUMsWUFBWSxDQUFDO01BQ3ZDLDBCQUEwQixDQUFDLElBQUksQ0FBQztNQUNoQyxJQUFJLENBQUMsT0FBTyxDQUFFLHVCQUF1QixFQUFFLENBQUUsSUFBSSxDQUFHLENBQUM7TUFDakQsT0FBTyxLQUFLO0lBQ2IsQ0FBQyxDQUFDO0VBQ0g7RUFFQSxTQUFTLDBCQUEwQixDQUFDLElBQUksRUFBRTtJQUN6QyxJQUFJLE9BQU8sR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLG9CQUFvQixDQUFDO0lBQ2hELElBQUksU0FBUyxHQUFHLENBQUMsQ0FBQywyQ0FBMkMsR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLElBQUksQ0FBQztJQUN2RixTQUFTLENBQUMsUUFBUSxDQUFDLFlBQVksQ0FBQztFQUNqQzs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxJQUFJLFdBQVcsR0FBRyxRQUFRLENBQUMsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztFQUV6RCxDQUFDLENBQUUsbUVBQW9FLENBQUMsQ0FDdEUsRUFBRSxDQUFFLHVCQUF1QixFQUFFLFVBQVUsS0FBSyxFQUFFLElBQUksRUFBRztJQUNyRCxxQ0FBcUMsQ0FBQyxJQUFJLENBQUM7RUFDNUMsQ0FBQyxDQUFDO0VBRUgsQ0FBQyxDQUFDLHdCQUF3QixDQUFDLENBQUMsRUFBRSxDQUFDLFFBQVEsRUFBRSxZQUFVO0lBQ2xELElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxFQUFFO01BQ2pDLDBCQUEwQixDQUFDLENBQUM7SUFDN0IsQ0FBQyxNQUFJO01BQ0osSUFBSSx1QkFBdUIsR0FBRyxHQUFHLEdBQUMsQ0FBQyxDQUFDLCtCQUErQixDQUFDLENBQUMsSUFBSSxDQUFFLFNBQVUsQ0FBQztNQUN0RixDQUFDLENBQUMsdUJBQXVCLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDO0lBQzVDO0VBQ0QsQ0FBQyxDQUFDO0VBRUYsU0FBUyxxQ0FBcUMsQ0FBQyxJQUFJLEVBQUU7SUFDcEQsSUFBSSxlQUFlLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUM7SUFDeEMsSUFBRyxtQkFBbUIsS0FBSyxlQUFlLEVBQUM7TUFDMUMsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztNQUM5QixDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztJQUN2QixDQUFDLE1BQUk7TUFDSixDQUFDLENBQUMsb0JBQW9CLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO01BQzlCLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO0lBQ3ZCO0VBRUQ7RUFFQSxTQUFTLDBCQUEwQixDQUFBLEVBQUc7SUFDckMsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztJQUM5QixDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztFQUN2QjtFQUVBLENBQUMsQ0FBRSxtRUFBb0UsQ0FBQyxDQUN0RSxFQUFFLENBQUUsMkJBQTJCLEVBQUUsVUFBVSxLQUFLLEVBQUUsSUFBSSxFQUFHO0lBQ3pELHFCQUFxQixHQUFJLG1CQUFtQixLQUFLLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxLQUFLLFdBQVk7RUFDMUYsQ0FBQyxDQUFDO0VBRUgsQ0FBQyxDQUFFLHVDQUF3QyxDQUFDLENBQUMsS0FBSyxDQUFDLFVBQVUsQ0FBQyxFQUFFO0lBQy9ELENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsT0FBTyxDQUFDLGdDQUFnQyxDQUFDLENBQUMsV0FBVyxDQUFDLE1BQU0sQ0FBQztFQUMxRSxDQUFDLENBQUM7RUFFRixDQUFDLENBQUMsb0NBQW9DLENBQUMsQ0FBQyxLQUFLLENBQUMsVUFBVSxDQUFDLEVBQUU7SUFDMUQsTUFBTSxRQUFRLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUM7SUFDdEMsTUFBTSxVQUFVLEdBQUcsUUFBUSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxTQUFTO0lBQ3pELFFBQVEsQ0FBQyxJQUFJLENBQUMsU0FBUyxFQUFFLFVBQVUsR0FBRyxJQUFJLEdBQUcsU0FBVSxDQUFDO0lBQ3hELE1BQU0sY0FBYyxHQUFHLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxPQUFPLENBQUMsV0FBVyxDQUFDLENBQUMsSUFBSSxDQUFDLHVDQUF1QyxDQUFDO0lBQ3JHLElBQUcsUUFBUSxDQUFDLFFBQVEsQ0FBQyxtQkFBbUIsQ0FBQyxFQUFFO01BQzFDLENBQUMsQ0FBQyxHQUFHLENBQUMsY0FBYyxFQUFFLFFBQVEsSUFBSTtRQUNqQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxVQUFVLEdBQUcsSUFBSSxHQUFHLFNBQVUsQ0FBQztNQUM1RCxDQUFDLENBQUM7TUFDRjtJQUNEO0lBQ0EsTUFBTSxhQUFhLEdBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxXQUFXLENBQUMsQ0FBQyxJQUFJLENBQUMsb0JBQW9CLENBQUM7SUFFakYsTUFBTSxXQUFXLEdBQUksQ0FBQyxDQUFDLEdBQUcsQ0FBQyxjQUFjLEVBQUUsUUFBUSxJQUFJO01BQ3RELElBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxTQUFTLEVBQUU7UUFDN0M7TUFDRDtNQUNBLE9BQU8sUUFBUTtJQUNoQixDQUFDLENBQUM7SUFDRixhQUFhLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxXQUFXLENBQUMsTUFBTSxLQUFLLGNBQWMsQ0FBQyxNQUFNLEdBQUcsU0FBUyxHQUFHLElBQUssQ0FBQztFQUNoRyxDQUFDLENBQUM7RUFFRixJQUFLLENBQUMsQ0FBRSxvQkFBcUIsQ0FBQyxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUc7SUFDM0MsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsWUFBWSxFQUFFLFFBQVEsS0FBSztNQUN4RCxJQUFJLFdBQVcsR0FBRyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsT0FBTyxDQUFDLFdBQVcsQ0FBQztNQUNsRCxJQUFJLFdBQVcsR0FBRyxXQUFXLENBQUMsSUFBSSxDQUFFLG1EQUFvRCxDQUFDLENBQUMsTUFBTTtNQUNoRyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxXQUFXLElBQUksQ0FBQyxHQUFHLFNBQVMsR0FBRyxJQUFLLENBQUM7SUFDbEUsQ0FBQyxDQUFDO0VBQ0g7RUFFQSxJQUFJLGVBQWUsR0FBRztJQUNyQixPQUFPLEVBQUUsQ0FBQyxDQUFDO0lBQ1gsS0FBSyxFQUFFLENBQUM7RUFDVCxDQUFDO0VBQ0QsQ0FBQyxDQUFDLDhDQUE4QyxDQUFDLENBQUMsSUFBSSxDQUFDLFlBQVk7SUFDbEU7SUFDQSxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztJQUMzQixJQUFJLEVBQUUsRUFBRTtNQUNQLGVBQWUsQ0FBQyxPQUFPLENBQUMsRUFBRSxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksRUFBRSxpQ0FBaUMsQ0FBQyxDQUFDLE1BQU07TUFDL0UsZUFBZSxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxFQUFFLGlEQUFpRCxDQUFDLENBQUMsTUFBTTtNQUM3RjtNQUNBLENBQUMsQ0FBQyxJQUFJLEVBQUUsMEJBQTBCLENBQUMsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLE9BQU8sQ0FBQyxFQUFFLENBQUMsQ0FBQztNQUNyRTtNQUNBLENBQUMsQ0FBQyxJQUFJLEVBQUUscUJBQXFCLENBQUMsQ0FBQyxNQUFNLENBQUMsZUFBZSxDQUFDLE9BQU8sQ0FBQyxFQUFFLENBQUMsR0FBRyxDQUFDLENBQUM7O01BRXRFO01BQ0EsSUFBSSxlQUFlLENBQUMsT0FBTyxDQUFDLEVBQUUsQ0FBQyxLQUFLLGVBQWUsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLEVBQUU7UUFDOUQsQ0FBQyxDQUFDLElBQUksRUFBRSxxQkFBcUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLEVBQUUsSUFBSSxDQUFDO01BQ3JEO0lBQ0Q7RUFDRCxDQUFDLENBQUM7O0VBRUY7QUFDRDtBQUNBO0VBQ0MsSUFBSSx1QkFBdUIsR0FBRyxDQUFDLENBQUMsK0JBQStCLENBQUM7RUFDaEUsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUUsWUFBWTtJQUN2QyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFFLENBQUMsZ0JBQWdCLENBQUMsSUFBSSx1QkFBdUIsQ0FBQyxFQUFFLENBQUMsVUFBVSxDQUFDLEVBQUU7TUFDM0UsdUJBQXVCLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQztJQUN6QztFQUNELENBQUMsQ0FBQztFQUVGLElBQUksY0FBYyxHQUFHLFFBQVEsQ0FBQyxjQUFjLENBQUUsdUJBQXdCLENBQUM7RUFDdkUsSUFBSyxjQUFjLEVBQUc7SUFDckIsY0FBYyxDQUFDLGdCQUFnQixDQUFDLHNCQUFzQixFQUFDLFVBQVMsS0FBSyxFQUFDO01BRXJFLElBQUksZUFBZSxHQUFHLENBQUMsQ0FBRSxLQUFLLENBQUMsTUFBTSxDQUFDLGNBQWUsQ0FBQztNQUV0RCxJQUFJLElBQUksR0FBRyxlQUFlLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQztNQUV2QyxJQUFJLE1BQU0sR0FBRyxlQUFlLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQztNQUMzQyxJQUFJLGFBQWEsR0FBSSxlQUFlLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQztNQUMxRCxJQUFJLEtBQUssR0FBSSxlQUFlLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQztNQUMxQyxJQUFJLEdBQUcsR0FBTSxlQUFlLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztNQUV4QyxJQUFJLFdBQVcsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsT0FBTyxDQUFFLG1CQUFvQixDQUFDO01BRXhELElBQUssTUFBTSxFQUFHO1FBQ2IsV0FBVyxDQUFDLElBQUksQ0FBRSwwQkFBMkIsQ0FBQyxDQUFDLElBQUksQ0FBRSxNQUFPLENBQUM7TUFDOUQ7TUFDQSxJQUFLLElBQUksRUFBRztRQUNYLFdBQVcsQ0FBQyxJQUFJLENBQUUsb0JBQXFCLENBQUMsQ0FBQyxJQUFJLENBQUUsSUFBSyxDQUFDO01BQ3REO01BQ0EsSUFBSyxhQUFhLEVBQUc7UUFDcEIsV0FBVyxDQUFDLElBQUksQ0FBRSxpQ0FBa0MsQ0FBQyxDQUFDLElBQUksQ0FBRSxhQUFjLENBQUM7TUFDNUU7TUFDQSxJQUFLLEtBQUssRUFBRztRQUNaLFdBQVcsQ0FBQyxJQUFJLENBQUUsMEJBQTJCLENBQUMsQ0FBQyxJQUFJLENBQUUsS0FBTSxDQUFDO01BQzdEO01BQ0EsSUFBSyxHQUFHLEVBQUc7UUFDVixXQUFXLENBQUMsSUFBSSxDQUFFLG1CQUFvQixDQUFDLENBQUMsSUFBSSxDQUFFLE1BQU0sRUFBRSxHQUFJLENBQUM7TUFDNUQ7SUFFRCxDQUFFLENBQUM7RUFDSjtFQUVBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUUsT0FBTyxFQUFFLHFCQUFxQixFQUFFLFVBQVUsQ0FBQyxFQUFFO0lBQzVELE9BQU8sT0FBTyxDQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUUsQ0FBQztFQUNsRCxDQUFFLENBQUM7QUFFSixDQUFDLENBQUM7Ozs7O0FDM2FGLElBQUksQ0FBQyxHQUFHLE1BQU07QUFDZCxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBSyxDQUFDLFlBQVU7RUFHM0I7QUFDRDtBQUNBOztFQUVDLElBQUksT0FBTyxHQUFHLENBQUMsQ0FBQyxhQUFhLENBQUM7RUFDOUIsSUFBSSxZQUFZLEdBQUcsQ0FBQyxDQUFDLDZCQUE2QixDQUFDO0VBRW5ELFlBQVksQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFlBQVc7SUFDbkMsdUJBQXVCLENBQUMsQ0FBQztJQUN6QixPQUFPLEtBQUs7RUFDYixDQUFDLENBQUM7RUFFRixTQUFTLHVCQUF1QixDQUFBLEVBQUU7SUFDakMsSUFBSSxHQUFHLEdBQUcsSUFBSSxZQUFZLENBQUMsQ0FBQyxDQUN6QixFQUFFLENBQUMsT0FBTyxFQUFFLENBQUMsRUFBRTtNQUFDLFNBQVMsRUFBQyxDQUFDO01BQUUsQ0FBQyxFQUFDLEVBQUU7TUFBRSxJQUFJLEVBQUMsTUFBTSxDQUFDO0lBQU8sQ0FBQyxDQUFDLENBQ3hELEVBQUUsQ0FBQyxPQUFPLEVBQUUsR0FBRyxFQUFFO01BQUMsTUFBTSxFQUFFLENBQUM7TUFBRSxTQUFTLEVBQUMsQ0FBQztNQUFFLElBQUksRUFBQyxNQUFNLENBQUM7SUFBTyxDQUFDLEVBQUUsTUFBTSxDQUFDLENBQ3ZFLEdBQUcsQ0FBQyxPQUFPLEVBQUU7TUFBQyxTQUFTLEVBQUM7SUFBTSxDQUFDLENBQUM7RUFFcEM7O0VBRUE7QUFDRDtBQUNBO0VBQ0MsQ0FBQyxDQUFFLGtDQUFtQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7RUFDOUMsQ0FBQyxDQUFFLGdDQUFpQyxDQUFDLENBQUMsRUFBRSxDQUFFLE9BQU8sRUFBRSxVQUFVLENBQUMsRUFBRztJQUNoRSxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7SUFFbEIsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFFLGtDQUFtQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUM7RUFDckUsQ0FBRSxDQUFDOztFQUVIO0FBQ0Q7QUFDQTs7RUFFQyxDQUFDLENBQUUsb0JBQXFCLENBQUMsQ0FBQyxJQUFJLENBQUUsWUFBVztJQUMxQyxJQUFJLE9BQU8sR0FBSyxDQUFDLENBQUUsSUFBSyxDQUFDO0lBQ3pCLElBQUksU0FBUyxHQUFHLE9BQU8sQ0FBQyxPQUFPLENBQUUsK0JBQWdDLENBQUMsQ0FBQyxJQUFJLENBQUUsc0JBQXVCLENBQUM7SUFDakcsSUFBSSxTQUFTLEdBQUcsQ0FBQyxDQUFFLFNBQVMsR0FBRyxPQUFPLENBQUMsSUFBSSxDQUFFLE1BQU8sQ0FBQyxHQUFHLGlCQUFrQixDQUFDO0lBRTNFLFNBQVMsQ0FBQyxFQUFFLENBQUMsUUFBUSxFQUFFLFlBQVc7TUFDakMsSUFBSyxTQUFTLENBQUMsRUFBRSxDQUFFLFVBQVcsQ0FBQyxFQUFHO1FBQ2pDLFNBQVMsQ0FBQyxHQUFHLENBQUUsU0FBUyxFQUFFLE9BQVEsQ0FBQztRQUNuQyxPQUFPLENBQUMsR0FBRyxDQUFFLFNBQVMsRUFBRSxjQUFlLENBQUM7TUFDekMsQ0FBQyxNQUFLO1FBQ0wsU0FBUyxDQUFDLEdBQUcsQ0FBRSxTQUFTLEVBQUUsTUFBTyxDQUFDO1FBQ2xDLE9BQU8sQ0FBQyxHQUFHLENBQUUsU0FBUyxFQUFFLE1BQU8sQ0FBQztNQUNqQztJQUNELENBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBRSxRQUFTLENBQUM7RUFDeEIsQ0FBRSxDQUFDOztFQUVIO0FBQ0Q7QUFDQTs7RUFFQztFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLHVCQUF1QixFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQzVELElBQUksT0FBTyxNQUFNLENBQUMsa0JBQWtCLEtBQUssVUFBVSxFQUFFO01BQ3BELElBQUksR0FBRyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUM7TUFDakIsSUFBSSxNQUFNLEdBQUcsR0FBRyxDQUFDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQztNQUN2QyxJQUFJLE9BQU8sR0FBRyxHQUFHLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLElBQUksRUFBRTtNQUVqRCxNQUFNLENBQUMsa0JBQWtCLENBQUMsTUFBTSxFQUFFLE9BQU8sQ0FBQztJQUMzQztFQUNELENBQUMsQ0FBQzs7RUFFRjtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLGVBQWUsRUFBRSxZQUFXO0lBQ25ELElBQUksT0FBTyxNQUFNLENBQUMsa0JBQWtCLEtBQUssVUFBVSxFQUFFO01BQ3BELElBQUksS0FBSyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLHVCQUF1QjtNQUNyRCxNQUFNLENBQUMsa0JBQWtCLENBQUMsS0FBSyxFQUFFLGlCQUFpQixDQUFDO0lBQ3BEO0VBQ0QsQ0FBQyxDQUFDOztFQUVGO0VBQ0EsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsd0JBQXdCLEVBQUUsWUFBVztJQUM1RCxJQUFJLE9BQU8sTUFBTSxDQUFDLGtCQUFrQixLQUFLLFVBQVUsRUFBRTtNQUNwRCxJQUFJLElBQUksR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQztNQUMvQixJQUFJLElBQUksR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7O01BRXpCO01BQ0EsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsT0FBTyxDQUFDLCtCQUErQixDQUFDLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLDRCQUE0QixDQUFDLEVBQUU7UUFDakosTUFBTSxDQUFDLGtCQUFrQixDQUFDLFFBQVEsR0FBRyxJQUFJLEVBQUUsV0FBVyxDQUFDO01BQ3hELENBQUMsTUFBTSxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxPQUFPLENBQUMsb0JBQW9CLENBQUMsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO1FBQzVELE1BQU0sQ0FBQyxrQkFBa0IsQ0FBQyxlQUFlLEVBQUUsU0FBUyxDQUFDO01BQ3RELENBQUMsTUFBTTtRQUNOLE1BQU0sQ0FBQyxrQkFBa0IsQ0FBQyxvQkFBb0IsRUFBRSxTQUFTLENBQUM7TUFDM0Q7SUFDRDtFQUNELENBQUMsQ0FBQzs7RUFFRjtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLG1EQUFtRCxFQUFFLFlBQVc7SUFDdkYsSUFBSSxPQUFPLE1BQU0sQ0FBQyxrQkFBa0IsS0FBSyxVQUFVLEVBQUU7TUFDcEQsTUFBTSxDQUFDLGtCQUFrQixDQUFDLG9CQUFvQixFQUFFLFNBQVMsQ0FBQztJQUMzRDtFQUNELENBQUMsQ0FBQzs7RUFFRjtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLDZDQUE2QyxFQUFFLFlBQVc7SUFDakYsSUFBSSxPQUFPLE1BQU0sQ0FBQyxrQkFBa0IsS0FBSyxVQUFVLEVBQUU7TUFDcEQsTUFBTSxDQUFDLGtCQUFrQixDQUFDLFdBQVcsRUFBRSxTQUFTLENBQUM7SUFDbEQ7RUFDRCxDQUFDLENBQUM7O0VBR0Y7QUFDRDtBQUNBOztFQUVDLElBQUksa0JBQWtCLEdBQUcsQ0FBQyxDQUFDLHNCQUFzQixDQUFDO0lBQ2pELGdCQUFnQixHQUFHLENBQUMsQ0FBQyxvQkFBb0IsQ0FBQztJQUMxQyx1QkFBdUIsR0FBRyxDQUFDLENBQUMsNEJBQTRCLENBQUM7SUFDekQsd0JBQXdCLEdBQUcsQ0FBQyxDQUFDLGtDQUFrQyxDQUFDO0lBQ2hFLHNCQUFzQixHQUFHLENBQUMsQ0FBQyxlQUFlLENBQUM7RUFHNUMsc0JBQXNCLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxVQUFTLENBQUMsRUFBRTtJQUM5QyxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUM7SUFDbEIsZ0JBQWdCLENBQUMsQ0FBQztJQUNsQixPQUFPLEtBQUs7RUFDYixDQUFDLENBQUM7RUFFRix1QkFBdUIsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQy9DLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUNsQixpQkFBaUIsQ0FBQyxDQUFDO0lBQ25CLE9BQU8sS0FBSztFQUNiLENBQUMsQ0FBQztFQUVGLHdCQUF3QixDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsVUFBUyxDQUFDLEVBQUU7SUFDaEQsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDO0lBQ2xCLG9CQUFvQixDQUFDLENBQUM7SUFDdEIsT0FBTyxLQUFLO0VBQ2IsQ0FBQyxDQUFDO0VBRUYsU0FBUyxnQkFBZ0IsQ0FBQSxFQUFFO0lBQzFCLElBQUksR0FBRyxHQUFHLElBQUksWUFBWSxDQUFDLENBQUMsQ0FDekIsR0FBRyxDQUFDLGtCQUFrQixFQUFFO01BQUMsU0FBUyxFQUFDO0lBQU8sQ0FBQyxDQUFDLENBQzVDLEdBQUcsQ0FBQyxnQkFBZ0IsRUFBRTtNQUFDLFNBQVMsRUFBQztJQUFPLENBQUMsQ0FBQyxDQUMxQyxNQUFNLENBQUMsZ0JBQWdCLEVBQUUsR0FBRyxFQUFFO01BQUMsU0FBUyxFQUFDO0lBQUMsQ0FBQyxFQUFDO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxJQUFJLEVBQUMsTUFBTSxDQUFDO0lBQU8sQ0FBQyxDQUFDLENBQy9FLE1BQU0sQ0FBQyxrQkFBa0IsRUFBRSxHQUFHLEVBQUU7TUFBQyxTQUFTLEVBQUMsQ0FBQztNQUFFLFNBQVMsRUFBRSxDQUFDO0lBQUUsQ0FBQyxFQUFFO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxTQUFTLEVBQUMsQ0FBQztNQUFFLElBQUksRUFBQyxNQUFNLENBQUM7SUFBTyxDQUFDLEVBQUUsTUFBTSxDQUFDO0VBRTNIO0VBRUEsU0FBUyxpQkFBaUIsQ0FBQSxFQUFFO0lBQzNCLElBQUksR0FBRyxHQUFHLElBQUksWUFBWSxDQUFDLENBQUMsQ0FDekIsTUFBTSxDQUFDLGtCQUFrQixFQUFFLEdBQUcsRUFBRTtNQUFDLFNBQVMsRUFBQyxDQUFDO01BQUUsU0FBUyxFQUFFO0lBQUMsQ0FBQyxFQUFFO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxTQUFTLEVBQUMsQ0FBQyxFQUFFO01BQUUsSUFBSSxFQUFDLE1BQU0sQ0FBQztJQUFPLENBQUMsQ0FBQyxDQUMvRyxNQUFNLENBQUMsZ0JBQWdCLEVBQUUsR0FBRyxFQUFFO01BQUMsU0FBUyxFQUFDO0lBQUMsQ0FBQyxFQUFDO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxJQUFJLEVBQUMsTUFBTSxDQUFDO0lBQU8sQ0FBQyxFQUFFLE1BQU0sQ0FBQyxDQUN2RixHQUFHLENBQUMsa0JBQWtCLEVBQUU7TUFBQyxTQUFTLEVBQUM7SUFBTSxDQUFDLENBQUMsQ0FDM0MsR0FBRyxDQUFDLGdCQUFnQixFQUFFO01BQUMsU0FBUyxFQUFDO0lBQU0sQ0FBQyxDQUFDO0VBRTdDO0VBRUEsU0FBUyxvQkFBb0IsQ0FBQSxFQUFFO0lBQzlCLGlCQUFpQixDQUFDLENBQUM7SUFDbkIsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxJQUFJLENBQUM7SUFFN0MsSUFBSSxnQkFBZ0IsR0FBRyxRQUFRLENBQUMsY0FBYyxDQUFDLG1CQUFtQixDQUFDO0lBRW5FLElBQUssZ0JBQWdCLEVBQUc7TUFDdkIsSUFBSSxXQUFXLEdBQUcsSUFBSSxLQUFLLENBQUMsUUFBUSxFQUFFO1FBQUUsT0FBTyxFQUFFO01BQUssQ0FBQyxDQUFDO01BQ3hELGdCQUFnQixDQUFDLGFBQWEsQ0FBQyxXQUFXLENBQUM7SUFDNUM7RUFDRDs7RUFFQTtFQUNBLENBQUMsQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUUsWUFBWTtJQUNoRCxDQUFDLENBQUMsMkJBQTJCLENBQUMsQ0FBQyxXQUFXLENBQUMsY0FBYyxDQUFDO0VBQzNELENBQUMsQ0FBQzs7RUFFRjtBQUNEO0FBQ0E7O0VBRUMsSUFBSSxnQkFBZ0IsR0FBRyxDQUFDLENBQUMsb0JBQW9CLENBQUM7SUFDOUMscUJBQXFCLEdBQUcsQ0FBQyxDQUFDLDBCQUEwQixDQUFDO0lBQ3JELG9CQUFvQixHQUFHLENBQUMsQ0FBQywyQkFBMkIsQ0FBQztFQUVyRCxvQkFBb0IsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFVBQVMsQ0FBQyxFQUFFO0lBQzVDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztJQUNsQixtQkFBbUIsQ0FBQyxDQUFDO0lBQ3JCLE9BQU8sS0FBSztFQUNiLENBQUMsQ0FBQztFQUVGLHFCQUFxQixDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsWUFBVztJQUM1QyxvQkFBb0IsQ0FBQyxDQUFDO0lBQ3RCLE9BQU8sS0FBSztFQUNiLENBQUMsQ0FBQztFQUVGLFNBQVMsbUJBQW1CLENBQUEsRUFBRTtJQUM3QixJQUFJLEdBQUcsR0FBRyxJQUFJLFlBQVksQ0FBQyxDQUFDO0lBRTVCLEdBQUcsQ0FBQyxHQUFHLENBQUMsZ0JBQWdCLEVBQUU7TUFBQyxTQUFTLEVBQUM7SUFBTyxDQUFDLENBQUMsQ0FDNUMsR0FBRyxDQUFDLGdCQUFnQixFQUFFO01BQUMsU0FBUyxFQUFDO0lBQU8sQ0FBQyxDQUFDLENBQzFDLE1BQU0sQ0FBQyxnQkFBZ0IsRUFBRSxHQUFHLEVBQUU7TUFBQyxTQUFTLEVBQUM7SUFBQyxDQUFDLEVBQUM7TUFBQyxTQUFTLEVBQUMsQ0FBQztNQUFFLElBQUksRUFBQyxNQUFNLENBQUM7SUFBTyxDQUFDLENBQUMsQ0FDL0UsTUFBTSxDQUFDLGdCQUFnQixFQUFFLEdBQUcsRUFBRTtNQUFDLFNBQVMsRUFBQyxDQUFDO01BQUUsU0FBUyxFQUFFLENBQUM7SUFBRSxDQUFDLEVBQUU7TUFBQyxTQUFTLEVBQUMsQ0FBQztNQUFFLFNBQVMsRUFBQyxDQUFDO01BQUUsSUFBSSxFQUFDLE1BQU0sQ0FBQztJQUFPLENBQUMsRUFBRSxNQUFNLENBQUM7RUFFeEg7RUFFQSxTQUFTLG9CQUFvQixDQUFBLEVBQUU7SUFDOUIsSUFBSSxHQUFHLEdBQUcsSUFBSSxZQUFZLENBQUMsQ0FBQztJQUU1QixHQUFHLENBQUMsTUFBTSxDQUFDLGdCQUFnQixFQUFFLEdBQUcsRUFBRTtNQUFDLFNBQVMsRUFBQyxDQUFDO01BQUUsU0FBUyxFQUFFO0lBQUMsQ0FBQyxFQUFFO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxTQUFTLEVBQUMsQ0FBQyxFQUFFO01BQUUsSUFBSSxFQUFDLE1BQU0sQ0FBQztJQUFPLENBQUMsQ0FBQyxDQUMvRyxNQUFNLENBQUMsZ0JBQWdCLEVBQUUsR0FBRyxFQUFFO01BQUMsU0FBUyxFQUFDO0lBQUMsQ0FBQyxFQUFDO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxJQUFJLEVBQUMsTUFBTSxDQUFDO0lBQU8sQ0FBQyxFQUFFLE1BQU0sQ0FBQyxDQUN2RixHQUFHLENBQUMsZ0JBQWdCLEVBQUU7TUFBQyxTQUFTLEVBQUM7SUFBTSxDQUFDLENBQUMsQ0FDekMsR0FBRyxDQUFDLGdCQUFnQixFQUFFO01BQUMsU0FBUyxFQUFDO0lBQU0sQ0FBQyxDQUFDO0VBRTVDOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLElBQUksV0FBVyxHQUFNLENBQUMsQ0FBRSxjQUFlLENBQUM7RUFDeEMsSUFBSSxjQUFjLEdBQUcsQ0FBQyxDQUFDLGNBQWMsQ0FBQztFQUV0QyxjQUFjLENBQUMsRUFBRSxDQUFDLFFBQVEsRUFBRSxZQUFXO0lBQ3RDLGFBQWEsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7RUFDdkIsQ0FBQyxDQUFDO0VBRUYsU0FBUyxhQUFhLENBQUMsS0FBSyxFQUFDO0lBQzVCLElBQUcsS0FBSyxDQUFDLEVBQUUsQ0FBQyxVQUFVLENBQUMsRUFBQztNQUN2QixXQUFXLENBQUMsR0FBRyxDQUFDLFNBQVMsRUFBQyxPQUFPLENBQUM7TUFDbEMsWUFBWSxDQUFDLE9BQU8sQ0FBRSxrQkFBa0IsRUFBRSxJQUFLLENBQUM7SUFDakQsQ0FBQyxNQUNHO01BQ0gsV0FBVyxDQUFDLEdBQUcsQ0FBQyxTQUFTLEVBQUMsTUFBTSxDQUFDO01BQ2pDLFlBQVksQ0FBQyxPQUFPLENBQUUsa0JBQWtCLEVBQUUsS0FBTSxDQUFDO0lBQ2xEO0VBQ0Q7O0VBSUE7QUFDRDtBQUNBOztFQUVDLElBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQyxjQUFjLENBQUMsRUFBQztJQUMxQyxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUMsR0FBRyxDQUFDLFNBQVMsRUFBRSxNQUFNLENBQUM7RUFDekMsQ0FBQyxNQUFNO0lBQ04sQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxTQUFTLEVBQUUsT0FBTyxDQUFDO0VBQzFDO0VBRUEsSUFBSSxRQUFRLEdBQUcsQ0FBQyxDQUFDLGNBQWMsQ0FBQztFQUNoQyxJQUFJLGFBQWEsR0FBRyxDQUFDLENBQUMsb0JBQW9CLENBQUM7RUFFM0MsYUFBYSxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsWUFBVztJQUNwQyxxQkFBcUIsQ0FBQyxDQUFDO0lBQ3ZCLE9BQU8sS0FBSztFQUNiLENBQUMsQ0FBQztFQUVGLFNBQVMscUJBQXFCLENBQUEsRUFBRTtJQUMvQixJQUFJLEdBQUcsR0FBRyxJQUFJLFlBQVksQ0FBQyxDQUFDLENBQ3pCLEVBQUUsQ0FBQyxRQUFRLEVBQUUsQ0FBQyxFQUFFO01BQUMsU0FBUyxFQUFDLENBQUM7TUFBRSxDQUFDLEVBQUMsRUFBRTtNQUFFLElBQUksRUFBQyxNQUFNLENBQUM7SUFBTyxDQUFDLENBQUMsQ0FDekQsRUFBRSxDQUFDLFFBQVEsRUFBRSxHQUFHLEVBQUU7TUFBQyxNQUFNLEVBQUUsQ0FBQztNQUFFLFNBQVMsRUFBQyxDQUFDO01BQUUsSUFBSSxFQUFDLE1BQU0sQ0FBQztJQUFPLENBQUMsRUFBRSxNQUFNLENBQUMsQ0FDeEUsR0FBRyxDQUFDLFFBQVEsRUFBRTtNQUFDLFNBQVMsRUFBQztJQUFNLENBQUMsQ0FBQztFQUVyQzs7RUFFQTtFQUNBLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLGdDQUFnQyxFQUFFLFlBQVc7SUFDcEUsSUFBSSxLQUFLLEdBQUcsQ0FBQyxDQUFDLDRCQUE0QixDQUFDO0lBQzNDLElBQUksWUFBWSxHQUFHLEtBQUssQ0FBQyxJQUFJLENBQUMsZ0NBQWdDLENBQUM7O0lBRS9EO0lBQ0EsSUFBSSxPQUFPLE1BQU0sQ0FBQyxrQkFBa0IsS0FBSyxVQUFVLEVBQUU7TUFDcEQsTUFBTSxDQUFDLGtCQUFrQixDQUFDLDJDQUEyQyxFQUFFLFdBQVcsQ0FBQztJQUNwRjtJQUVBLElBQUksS0FBSyxDQUFDLFFBQVEsQ0FBQyxhQUFhLENBQUMsRUFBRTtNQUNsQyxZQUFZLENBQUMsT0FBTyxDQUFDLEdBQUcsRUFBRSxZQUFXO1FBQ3BDLEtBQUssQ0FBQyxXQUFXLENBQUMsYUFBYSxDQUFDO01BQ2pDLENBQUMsQ0FBQztNQUNGLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxXQUFXLENBQUMsYUFBYSxDQUFDO01BQ2xDO0lBQ0Q7SUFFQSxLQUFLLENBQUMsUUFBUSxDQUFDLGFBQWEsQ0FBQztJQUM3QixZQUFZLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQztJQUMzQixDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsUUFBUSxDQUFDLGFBQWEsQ0FBQztFQUNoQyxDQUFDLENBQUM7O0VBRUY7RUFDQSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxvQ0FBb0MsRUFBRSxZQUFXO0lBQ3hFLElBQUksY0FBYyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsZ0JBQWdCLENBQUMsSUFBSSxTQUFTOztJQUVoRTtJQUNBO0lBQ0EsVUFBVSxDQUFDLFlBQVc7TUFDckIsSUFBSSxPQUFPLEdBQUcsQ0FBQyxDQUFDLElBQUksY0FBYyxFQUFFLENBQUM7TUFFckMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxNQUFNLElBQUksQ0FBQyxPQUFPLENBQUMsRUFBRSxDQUFDLFVBQVUsQ0FBQyxFQUFFO1FBQy9DO01BQ0Q7TUFFQSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsY0FBYyxDQUFDO1FBQUUsUUFBUSxFQUFFLFFBQVE7UUFBRSxLQUFLLEVBQUU7TUFBUyxDQUFDLENBQUM7SUFDbkUsQ0FBQyxFQUFFLEdBQUcsQ0FBQzs7SUFFUDtJQUNBLElBQUksT0FBTyxRQUFRLEtBQUssV0FBVyxJQUFJLFFBQVEsQ0FBQyxLQUFLLEVBQUU7TUFDdEQ7TUFDQSxJQUFJLE9BQU8sb0JBQW9CLEtBQUssV0FBVyxJQUFJLG9CQUFvQixDQUFDLGFBQWEsSUFBSSxvQkFBb0IsQ0FBQyxhQUFhLEtBQUssR0FBRyxFQUFFO1FBQ3BJO1FBQ0EsSUFBSSxvQkFBb0IsQ0FBQyxPQUFPLElBQUksT0FBTyxRQUFRLENBQUMsUUFBUSxLQUFLLFVBQVUsRUFBRTtVQUM1RSxRQUFRLENBQUMsUUFBUSxDQUFDLG9CQUFvQixDQUFDLE9BQU8sQ0FBQztRQUNoRDs7UUFFQTtRQUNBLFFBQVEsQ0FBQyxLQUFLLENBQUMsZ0JBQWdCLEVBQUU7VUFDaEMsUUFBUSxFQUFFLGdDQUFnQztVQUMxQyxnQkFBZ0IsRUFBRSxjQUFjO1VBQ2hDLFFBQVEsRUFBRSxvQkFBb0IsQ0FBQyxNQUFNO1VBQ3JDLE9BQU8sRUFBRSxvQkFBb0IsQ0FBQyxLQUFLO1VBQ25DLGFBQWEsRUFBRSxvQkFBb0IsQ0FBQyxHQUFHO1VBQ3ZDLFNBQVMsRUFBRSxvQkFBb0IsQ0FBQyxPQUFPO1VBQ3ZDLE1BQU0sRUFBRSxvQkFBb0IsQ0FBQztRQUM5QixDQUFDLENBQUM7TUFDSDtJQUNEO0VBQ0QsQ0FBQyxDQUFDOztFQUVGO0VBQ0EsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsd0NBQXdDLEVBQUUsWUFBVztJQUM1RTtJQUNBLElBQUksT0FBTyxRQUFRLEtBQUssV0FBVyxJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssRUFBRTtNQUN2RDtJQUNEOztJQUVBO0lBQ0EsSUFBSSxPQUFPLG9CQUFvQixLQUFLLFdBQVcsSUFBSSxDQUFDLG9CQUFvQixDQUFDLGFBQWEsSUFBSSxvQkFBb0IsQ0FBQyxhQUFhLEtBQUssR0FBRyxFQUFFO01BQ3JJO0lBQ0Q7O0lBRUE7SUFDQSxJQUFJLG9CQUFvQixDQUFDLE9BQU8sSUFBSSxPQUFPLFFBQVEsQ0FBQyxRQUFRLEtBQUssVUFBVSxFQUFFO01BQzVFLFFBQVEsQ0FBQyxRQUFRLENBQUMsb0JBQW9CLENBQUMsT0FBTyxDQUFDO0lBQ2hEOztJQUVBO0lBQ0EsUUFBUSxDQUFDLEtBQUssQ0FBQyxnREFBZ0QsRUFBRTtNQUNoRSxRQUFRLEVBQUUsNkJBQTZCO01BQ3ZDLFFBQVEsRUFBRSx1QkFBdUI7TUFDakMsUUFBUSxFQUFFLG9CQUFvQixDQUFDLE1BQU07TUFDckMsT0FBTyxFQUFFLG9CQUFvQixDQUFDLEtBQUs7TUFDbkMsYUFBYSxFQUFFLG9CQUFvQixDQUFDLEdBQUc7TUFDdkMsU0FBUyxFQUFFLG9CQUFvQixDQUFDLE9BQU87TUFDdkMsTUFBTSxFQUFFLG9CQUFvQixDQUFDO0lBQzlCLENBQUMsQ0FBQztFQUNILENBQUMsQ0FBQztBQUNILENBQUMsQ0FBQzs7Ozs7Ozs7QUMvVkYsTUFBTSxjQUFjLENBQUM7RUFtQnBCLFdBQVcsQ0FBRSxNQUFNLEVBQUc7SUFBQSxlQUFBLHNCQWpCUixDQUNiLFdBQVcsRUFDWCxpQkFBaUIsRUFDakIsVUFBVSxFQUNWLG1CQUFtQixFQUNuQixPQUFPLEVBQ1AsU0FBUyxFQUNULGdCQUFnQixFQUNoQixVQUFVLEVBQ1YsV0FBVyxFQUNYLFFBQVEsRUFDUixTQUFTLEVBQ1QsV0FBVyxFQUNYLFNBQVMsRUFDVCxPQUFPLENBQ1A7SUFHQSxJQUFJLENBQUMsTUFBTSxHQUFHLE1BQU07RUFDckI7O0VBRUE7QUFDRDtBQUNBO0VBQ0MsSUFBSSxDQUFBLEVBQUc7SUFDTixJQUFLLE9BQU8sUUFBUSxLQUFLLFdBQVcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxLQUFLLEVBQUc7TUFDekQ7SUFDRDtJQUNBLElBQUssQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLGFBQWEsSUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDLGFBQWEsS0FBSyxHQUFHLEVBQUU7TUFDckU7SUFDRDtJQUNBLFFBQVEsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUM7SUFDdEMsSUFBSSxDQUFDLGNBQWMsQ0FBRSxJQUFLLENBQUM7RUFDNUI7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtFQUNDLGNBQWMsQ0FBRSxJQUFJLEVBQUc7SUFDdEIsTUFBTSxDQUFDLGdCQUFnQixDQUFFLFlBQVksRUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLElBQUksQ0FBRSxJQUFLLENBQUUsQ0FBQztJQUN4RSxNQUFNLENBQUMsZ0JBQWdCLENBQUUsTUFBTSxFQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFFLElBQUssQ0FBRSxDQUFDO0VBQ2pFOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7RUFDQyxhQUFhLENBQUUsS0FBSyxFQUFHO0lBQ3RCLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxHQUFHLENBQUMsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDLElBQUksQ0FBQztJQUMzRCxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksR0FBRyxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFJLENBQUM7SUFFM0QsSUFBSyxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLEVBQUc7TUFDbEM7SUFDRDtJQUVBLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFFLE9BQVEsQ0FBQyxFQUFFLE9BQU8sQ0FBQztFQUMvRDtFQUVBLFdBQVcsQ0FBQSxFQUFHO0lBQ2IsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQztJQUNyRCxJQUFLLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsRUFBRztNQUNsQztJQUNEO0lBRUEsSUFBSSxDQUFDLG9CQUFvQixDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFFLE9BQU8sQ0FBQztFQUN0RDtFQUVBLFVBQVUsQ0FBRSxPQUFPLEVBQUc7SUFDckIsSUFBSSxDQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxVQUFVLENBQUMsR0FBRyxDQUFDLEVBQUU7TUFDekMsT0FBTyxPQUFPO0lBQ2Y7SUFDQSxPQUFPLE9BQU8sQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDO0VBQzVCO0VBRUEsWUFBWSxDQUFDLE9BQU8sRUFBRTtJQUNyQixPQUFPLElBQUksQ0FBQyxXQUFXLENBQUMsUUFBUSxDQUFDLE9BQU8sQ0FBQztFQUMxQztFQUVBLFVBQVUsQ0FBRSxPQUFPLEdBQUcsRUFBRSxFQUFHO0lBQzFCLElBQUssT0FBTyxFQUFHO01BQ2QsT0FBTyxZQUFZLE9BQU8sRUFBRTtJQUM3QjtJQUVBLElBQUksTUFBTSxHQUFHLElBQUksQ0FBQyxvQ0FBb0MsQ0FBQyxDQUFDO0lBQ3hELElBQUssTUFBTSxFQUFHO01BQ2IsT0FBTyxNQUFNO0lBQ2Q7SUFFQSxPQUFPLElBQUksQ0FBQyxzQkFBc0IsQ0FBQyxDQUFDO0VBQ3JDO0VBRUEsb0NBQW9DLENBQUEsRUFBRztJQUN0QyxNQUFNLEdBQUcsR0FBRyxJQUFJLEdBQUcsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQztJQUN6QyxNQUFNLFNBQVMsR0FBRyxHQUFHLENBQUMsWUFBWTs7SUFFbEM7SUFDQSxJQUFJLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxlQUFlLENBQUMsRUFBRTtNQUNwQyxPQUFPLEVBQUU7SUFDVjs7SUFFQTtJQUNBLE1BQU0sV0FBVyxHQUFHLFNBQVMsQ0FBQyxHQUFHLENBQUMsZUFBZSxDQUFDOztJQUVsRDtJQUNBLFNBQVMsQ0FBQyxNQUFNLENBQUMsZUFBZSxDQUFDOztJQUVqQztJQUNBO0lBQ0EsTUFBTSxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUMsSUFBSSxFQUFFLEVBQUUsRUFBRSxHQUFHLENBQUMsTUFBTSxHQUFHLEdBQUcsQ0FBQyxJQUFJLEdBQUcsR0FBRyxDQUFDLFFBQVEsQ0FBQzs7SUFFM0U7SUFDQSxPQUFPLFdBQVc7RUFDbkI7RUFFQSxzQkFBc0IsQ0FBQSxFQUFHO0lBQ3hCLE1BQU0sUUFBUSxHQUFHLFFBQVEsQ0FBQyxRQUFRO0lBQ2xDLElBQUksQ0FBQyxRQUFRLEVBQUU7TUFDZCxPQUFPLFlBQVk7SUFDcEI7SUFDQSxJQUFJLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxFQUFFO01BQ2pELE9BQU8sVUFBVTtJQUNsQjtJQUNBLE9BQU8sVUFBVTtFQUNsQjtFQUVBLG9CQUFvQixDQUFDLE1BQU0sRUFBRSxPQUFPLEVBQUU7SUFDckMsUUFBUSxDQUFDLEtBQUssQ0FBQyxhQUFhLEVBQUU7TUFDN0IsSUFBSSxFQUFFLCtDQUErQyxPQUFPLEVBQUU7TUFDOUQsU0FBUyxFQUFFLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQztNQUNwQyxNQUFNLEVBQUUsTUFBTTtNQUNkLE1BQU0sRUFBRSxvQkFBb0IsQ0FBQyxNQUFNO01BQ25DLEtBQUssRUFBRSxvQkFBb0IsQ0FBQyxLQUFLO01BQ2pDLFdBQVcsRUFBRSxvQkFBb0IsQ0FBQyxHQUFHO01BQ3JDLE9BQU8sRUFBRSxvQkFBb0IsQ0FBQztJQUMvQixDQUFDLENBQUM7RUFDSDs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxPQUFPLEdBQUcsQ0FBQSxFQUFHO0lBQ1o7SUFDQSxJQUFLLE9BQU8sb0JBQW9CLEtBQUssV0FBVyxFQUFHO01BQ2xEO0lBQ0Q7SUFFQSxNQUFNLFFBQVEsR0FBRyxJQUFJLGNBQWMsQ0FBRSxvQkFBcUIsQ0FBQztJQUMzRCxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUM7RUFDaEI7QUFDRDtBQUVBLGNBQWMsQ0FBQyxHQUFHLENBQUMsQ0FBQzs7Ozs7QUM1SnBCLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBRSxrQkFBa0IsRUFBRSxZQUFZO0VBRXZELElBQUksWUFBWSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUMsY0FBYyxDQUFDO0VBQ3pELElBQUcsWUFBWSxFQUFDO0lBQ1osSUFBSSxXQUFXLENBQUMsWUFBWSxDQUFDO0VBQ2pDO0FBRUosQ0FBQyxDQUFDOztBQUdGO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQSxTQUFTLFdBQVcsQ0FBQyxLQUFLLEVBQUU7RUFFeEIsSUFBSSxPQUFPLEdBQUcsSUFBSTtFQUVsQixJQUFJLENBQUMsS0FBSyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUMsV0FBVyxDQUFDO0VBQ2hELElBQUksQ0FBQyxVQUFVLEdBQUcsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGVBQWUsQ0FBQztFQUM1RCxJQUFJLENBQUMsYUFBYSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUMsMkNBQTJDLENBQUM7RUFDeEYsSUFBSSxDQUFDLE1BQU0sR0FBRyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsV0FBVyxDQUFDO0VBQ3BELElBQUksQ0FBQyxRQUFRLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBQyxjQUFjLENBQUM7RUFDdEQsSUFBSSxDQUFDLFFBQVEsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFDLGNBQWMsQ0FBQztFQUN0RCxJQUFJLENBQUMsS0FBSyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUMsbUJBQW1CLENBQUM7RUFDeEQsSUFBSSxDQUFDLE1BQU0sR0FBRyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsYUFBYSxDQUFDO0VBQ3RELElBQUksQ0FBQyxTQUFTLEdBQUcsSUFBSTtFQUNyQixJQUFJLENBQUMsS0FBSyxHQUFHLElBQUk7RUFDakIsSUFBSSxDQUFDLE1BQU0sR0FBRyxJQUFJO0VBQ2xCLElBQUksQ0FBQyxPQUFPLEdBQUcsQ0FBQztFQUNoQixJQUFJLENBQUMsVUFBVSxHQUFHLElBQUksQ0FBQyxhQUFhLENBQUMsS0FBSztFQUUxQyxPQUFPLENBQUMsVUFBVSxDQUFDLENBQUM7O0VBRXBCO0VBQ0EsTUFBTSxDQUFDLFlBQVksR0FBRyxZQUFXO0lBQzdCLE9BQU8sQ0FBQyxRQUFRLENBQUMsQ0FBQztFQUN0QixDQUFDOztFQUVEO0VBQ0EsSUFBRyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksRUFBQztJQUNwQixJQUFJLENBQUMsT0FBTyxHQUFHLENBQUM7SUFDaEIsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO0VBQ25CLENBQUMsTUFDRztJQUNBLElBQUksT0FBTyxHQUFHLFlBQVksQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDO0lBQzlDLElBQUksQ0FBQyxPQUFPLEdBQUcsQ0FBQztJQUVoQixJQUFHLE9BQU8sRUFBQztNQUNQLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxHQUFHLE9BQU87TUFDOUIsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQ25CLENBQUMsTUFDRztNQUNBLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxVQUFVLENBQUM7TUFDNUMsWUFBWSxDQUFDLE9BQU8sQ0FBQyxVQUFVLEVBQUUsV0FBVyxDQUFDO01BQzdDLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxHQUFHLFlBQVk7SUFDdkM7RUFDSjs7RUFFQTtFQUNBLEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBRSxDQUFDLEVBQUUsRUFBRTtJQUN6QyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBRyxZQUFXO01BQ2hDLE9BQU8sQ0FBQyxVQUFVLENBQUMsQ0FBQztNQUNwQixJQUFJLFNBQVMsR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFDdkMsSUFBRyxTQUFTLElBQUksT0FBTyxDQUFDLE1BQU0sSUFBSSxTQUFTLElBQUksU0FBUyxFQUFDO1FBQ3JELE9BQU8sQ0FBQyxRQUFRLENBQUMsQ0FBQztRQUNsQixPQUFPLEtBQUs7TUFDaEI7SUFDSixDQUFDO0VBQ0w7O0VBRUE7RUFDQSxJQUFJLFdBQVcsR0FBRyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsaUNBQWlDLENBQUM7RUFDOUUsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLFdBQVcsQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7SUFDekMsV0FBVyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBRyxZQUFXO01BQ2hDLFlBQVksQ0FBQyxPQUFPLENBQUMsVUFBVSxFQUFFLEVBQUUsQ0FBQztJQUN4QyxDQUFDO0VBQ0w7RUFFQSxRQUFRLENBQUMsZ0JBQWdCLENBQUUsc0JBQXNCLEVBQUUsWUFBVztJQUMxRCxPQUFPLENBQUMseUJBQXlCLENBQUMsQ0FBQztFQUN2QyxDQUFFLENBQUM7QUFFUDs7QUFHQTtBQUNBO0FBQ0E7QUFDQSxXQUFXLENBQUMsU0FBUyxDQUFDLFFBQVEsR0FBRyxZQUFXO0VBQ3hDLElBQUksQ0FBQyxNQUFNLEdBQUcsTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQztFQUNoRCxJQUFJLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUksQ0FBQyxNQUFNO0VBQ2pGLFlBQVksQ0FBQyxPQUFPLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQyxNQUFNLENBQUM7RUFFN0MsSUFBSSxDQUFDLEtBQUssR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFDLFlBQVksR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDO0VBQy9ELElBQUksQ0FBQyxTQUFTLEdBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQyxVQUFVLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQztFQUVsRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7QUFDakIsQ0FBQzs7QUFJRDtBQUNBO0FBQ0E7QUFDQSxXQUFXLENBQUMsU0FBUyxDQUFDLFVBQVUsR0FBRyxZQUFXO0VBQzFDLElBQUksT0FBTyxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMscUJBQXFCLENBQUMsQ0FBQztFQUNoRCxJQUFJLENBQUMsT0FBTyxHQUFHLE9BQU8sQ0FBQyxHQUFHLEdBQUcsTUFBTSxDQUFDLFdBQVcsR0FBRyxFQUFFLENBQUMsQ0FBQztBQUMxRCxDQUFDOztBQUlEO0FBQ0E7QUFDQTtBQUNBLFdBQVcsQ0FBQyxTQUFTLENBQUMsTUFBTSxHQUFHLFlBQVc7RUFFdEMsSUFBSSxPQUFPLEdBQUcsSUFBSTtFQUNsQixRQUFRLENBQUMsZUFBZSxDQUFDLFNBQVMsR0FBRyxPQUFPLENBQUMsT0FBTzs7RUFFcEQ7RUFDQSxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7SUFDekMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTyxHQUFHLE1BQU07RUFDekM7RUFDQSxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsSUFBSSxDQUFDLFVBQVUsQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7SUFDN0MsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLFVBQVUsQ0FBQztFQUNuRDs7RUFFQTtFQUNBLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBRyxPQUFPO0VBQ2xDLElBQUksQ0FBQyxhQUFhLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBRyxPQUFPO0VBRTFDLElBQUssSUFBSSxLQUFLLFlBQVksQ0FBQyxPQUFPLENBQUUsa0JBQW1CLENBQUMsRUFBRztJQUN2RCxZQUFZLENBQUMsT0FBTyxDQUFFLGtCQUFrQixFQUFFLElBQUssQ0FBQztFQUNwRDtFQUVBLElBQUssSUFBSSxLQUFLLFlBQVksQ0FBQyxPQUFPLENBQUMsa0JBQWtCLENBQUMsRUFBRztJQUNyRCxJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUcsTUFBTTtFQUN4QyxDQUFDLE1BQU0sSUFBSyxLQUFLLEtBQUssWUFBWSxDQUFDLE9BQU8sQ0FBQyxrQkFBa0IsQ0FBQyxFQUFHO0lBQzdELElBQUksQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBRyxNQUFNO0lBQ3BDLFFBQVEsQ0FBQyxhQUFhLENBQUMsY0FBYyxDQUFDLENBQUMsZUFBZSxDQUFFLFNBQVUsQ0FBQztFQUN2RTtFQUVBLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBRyxPQUFPO0VBQ2xDLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxVQUFVLENBQUM7RUFDeEMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxLQUFLLEdBQUcsSUFBSSxDQUFDLFVBQVU7RUFDMUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLFdBQVcsQ0FBQztFQUV4QyxNQUFNLGtCQUFrQixHQUFHLENBQ3ZCLFdBQVcsRUFDWCxRQUFRLEVBQ1IsVUFBVSxFQUNWLE9BQU8sRUFDUCxRQUFRLEVBQ1IsU0FBUyxFQUNULFdBQVcsRUFDWCxTQUFTLENBQ1o7RUFFRCxNQUFNLHlCQUF5QixHQUFHLENBQzlCLFdBQVcsRUFDWCxTQUFTLEVBQ1QsVUFBVSxDQUNiOztFQUVEO0VBQ0EsSUFBRyxJQUFJLENBQUMsTUFBTSxJQUFJLFdBQVcsRUFBQztJQUMxQixJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUcsTUFBTTtJQUNwQyxJQUFJLENBQUMsUUFBUSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUMsV0FBVyxDQUFDO0VBQy9DO0VBRUEsSUFBSSxJQUFJLENBQUMsTUFBTSxJQUFJLFNBQVMsRUFBRTtJQUMxQixJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUcsTUFBTTtFQUN4QztFQUVBLElBQUkseUJBQXlCLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBRTtJQUNqRCxJQUFJLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUcsTUFBTTtFQUNyQztFQUVBLElBQUksa0JBQWtCLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBRTtJQUMxQyxJQUFJLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUcsTUFBTTtFQUM3QztFQUVBLElBQUksQ0FBQyx5QkFBeUIsQ0FBQyxDQUFDOztFQUVuQztFQUNBLFFBQVEsQ0FBQyxhQUFhLENBQUMsSUFBSSxXQUFXLENBQUMsNkJBQTZCLEVBQUU7SUFDckUsTUFBTSxFQUFFO01BQ1AsTUFBTSxFQUFFLElBQUksQ0FBQyxNQUFNO01BQ25CLFlBQVksRUFBRSxJQUFJLENBQUM7SUFDcEI7RUFDRCxDQUFFLENBQUUsQ0FBQztBQUNOLENBQUM7O0FBR0Q7QUFDQTtBQUNBO0FBQ0EsV0FBVyxDQUFDLFNBQVMsQ0FBQyx5QkFBeUIsR0FBRyxZQUFXO0VBQzVELElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFJLE1BQU0sS0FBSyxJQUFJLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQyxPQUFPLEVBQUU7SUFDdkU7RUFDRDtFQUVBLElBQUksU0FBUyxHQUFHLFVBQVUsS0FBSyxJQUFJLENBQUMsTUFBTTtFQUMxQyxJQUFJLG9CQUFvQixHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQ2hELHNFQUNELENBQUM7RUFFRCxJQUFJLENBQUMsYUFBYSxDQUFDLFFBQVEsR0FBRyxTQUFTLElBQUksQ0FBQyxDQUFDLG9CQUFvQjtBQUNsRSxDQUFDOzs7OztBQzFORDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsSUFBSSxDQUFDLEdBQUcsTUFBTTtBQUNkLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxLQUFLLENBQUMsWUFBVTtFQUMzQjtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsMkJBQTJCLENBQUMsSUFBSSxFQUFFO0lBQzFDLE1BQU0sTUFBTSxHQUFHLENBQUMsQ0FBQyxzQkFBc0IsQ0FBQztJQUV4QyxJQUFJLENBQUMsTUFBTSxJQUFJLENBQUMsSUFBSSxFQUFFLGVBQWUsRUFBRSxJQUFJLEVBQUU7TUFDNUM7SUFDRDs7SUFFQTtJQUNBLE1BQU0sQ0FBQyxXQUFXLENBQUMsSUFBSSxFQUFFLGVBQWUsRUFBRSxJQUFJLENBQUM7RUFDaEQ7O0VBSUE7QUFDRDtBQUNBO0VBQ0MsU0FBUywwQkFBMEIsQ0FBQSxFQUFHO0lBQ3JDO0lBQ0EsSUFBSSxNQUFNLENBQUMsRUFBRSxJQUFJLE1BQU0sQ0FBQyxFQUFFLENBQUMsUUFBUSxFQUFFO01BQ3BDLE1BQU0sQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDO1FBQ2xCLElBQUksRUFBRTtNQUNQLENBQUMsQ0FBQyxDQUNBLElBQUksQ0FBQyxVQUFVLElBQUksRUFBRTtRQUNyQiwyQkFBMkIsQ0FBQyxJQUFJLENBQUM7TUFDbEMsQ0FBQyxDQUFDLENBQ0QsS0FBSyxDQUFDLFVBQVUsS0FBSyxFQUFFO1FBQ3ZCLE9BQU8sQ0FBQyxLQUFLLENBQUMseUNBQXlDLEVBQUUsS0FBSyxDQUFDO01BQ2hFLENBQUMsQ0FBQztJQUNKLENBQUMsTUFBTTtNQUNOO01BQ0EsS0FBSyxDQUFDLE1BQU0sQ0FBQyxhQUFhLEVBQUUsSUFBSSxHQUFHLDhCQUE4QixFQUFFO1FBQ2xFLE9BQU8sRUFBRTtVQUNSLFlBQVksRUFBRSxNQUFNLENBQUMsYUFBYSxFQUFFLEtBQUssSUFBSTtRQUM5QztNQUNELENBQUMsQ0FBQyxDQUNBLElBQUksQ0FBQyxVQUFVLFFBQVEsRUFBRTtRQUN6QixJQUFJLENBQUMsUUFBUSxDQUFDLEVBQUUsRUFBRTtVQUNqQixNQUFNLElBQUksS0FBSyxDQUFDLDZCQUE2QixDQUFDO1FBQy9DO1FBQ0EsT0FBTyxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUM7TUFDdkIsQ0FBQyxDQUFDLENBQ0QsSUFBSSxDQUFDLFVBQVUsSUFBSSxFQUFFO1FBQ3JCLDJCQUEyQixDQUFDLElBQUksQ0FBQztNQUNsQyxDQUFDLENBQUMsQ0FDRCxLQUFLLENBQUMsVUFBVSxLQUFLLEVBQUU7UUFDdkIsT0FBTyxDQUFDLEtBQUssQ0FBQyx5Q0FBeUMsRUFBRSxLQUFLLENBQUM7TUFDaEUsQ0FBQyxDQUFDO0lBQ0o7RUFDRDs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtFQUNDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFFLENBQUMsOEVBQThFLEVBQUUsTUFBTTtJQUNwRywwQkFBMEIsQ0FBQyxDQUFDO0VBQzdCLENBQUMsQ0FBQztBQUNILENBQUMsQ0FBQzs7Ozs7QUN2RUYsQ0FBSSxRQUFRLElBQU07RUFDakIsWUFBWTs7RUFFWjtBQUNEO0FBQ0E7RUFDQyxNQUFNLDJCQUEyQixDQUFDO0lBQ2pDLFdBQVcsQ0FBQSxFQUFHO01BQ2IsSUFBSSxDQUFDLElBQUksR0FBRyxzQ0FBc0M7TUFDbEQsSUFBSSxDQUFDLFlBQVksR0FBRyxLQUFLLENBQUMsQ0FBQztNQUMzQixJQUFJLENBQUMsVUFBVSxHQUFHLEVBQUUsQ0FBQyxDQUFDO01BQ3RCLElBQUksQ0FBQyxPQUFPLEdBQUcsSUFBSTtNQUNuQixJQUFJLENBQUMsVUFBVSxHQUFHLENBQUM7TUFFbkIsUUFBUSxDQUFDLGdCQUFnQixDQUFFLDhCQUE4QixFQUFFLE1BQU0sSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFFLENBQUM7O01BRS9FO01BQ0EsTUFBTSxPQUFPLEdBQUcsQ0FDZix5QkFBeUIsRUFDekIsNkJBQTZCLENBQzdCO01BRUQsTUFBTSxVQUFVLEdBQUcsT0FBTyxDQUFDLEtBQUssQ0FBRSxHQUFHLElBQUksUUFBUSxDQUFDLGFBQWEsQ0FBRSxHQUFJLENBQUMsS0FBSyxJQUFLLENBQUM7TUFFakYsSUFBSyxVQUFVLEVBQUc7UUFDakIsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDO01BQ2I7SUFDRDs7SUFFQTtBQUNGO0FBQ0E7SUFDRSxLQUFLLENBQUEsRUFBRztNQUNQLElBQUssSUFBSSxDQUFDLE9BQU8sRUFBRztRQUNuQjtNQUNEO01BRUEsSUFBSSxDQUFDLFVBQVUsR0FBRyxDQUFDO01BQ25CLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUNaOztJQUVBO0FBQ0Y7QUFDQTtJQUNFLElBQUksQ0FBQSxFQUFHO01BQ04sSUFBSyxJQUFJLENBQUMsT0FBTyxFQUFHO1FBQ25CLFlBQVksQ0FBRSxJQUFJLENBQUMsT0FBUSxDQUFDO1FBQzVCLElBQUksQ0FBQyxPQUFPLEdBQUcsSUFBSTtNQUNwQjtJQUNEOztJQUVBO0FBQ0Y7QUFDQTtJQUNFLElBQUksQ0FBQSxFQUFHO01BQ04sSUFBSyxJQUFJLENBQUMsVUFBVSxJQUFJLElBQUksQ0FBQyxVQUFVLEVBQUc7UUFDekMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQ1g7TUFDRDtNQUVBLElBQUksQ0FBQyxVQUFVLEVBQUU7TUFFakIsTUFBTSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUU7UUFDbkIsSUFBSSxFQUFFLElBQUksQ0FBQyxJQUFJO1FBQ2YsTUFBTSxFQUFFO01BQ1QsQ0FBRSxDQUFDLENBQUMsSUFBSSxDQUFJLFFBQVEsSUFBTTtRQUN6QixJQUFLLENBQUUsUUFBUSxJQUFJLENBQUUsUUFBUSxDQUFDLFVBQVUsRUFBRztVQUMxQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7VUFDWCxNQUFNLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1VBQ3hCO1FBQ0Q7UUFFQSxJQUFJLENBQUMsT0FBTyxHQUFHLFVBQVUsQ0FBRSxNQUFNLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFFLElBQUksQ0FBQyxZQUFhLENBQUM7TUFDbEUsQ0FBRSxDQUFDLENBQUMsS0FBSyxDQUFFLE1BQU07UUFDaEIsSUFBSSxDQUFDLE9BQU8sR0FBRyxVQUFVLENBQUUsTUFBTSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBYSxDQUFDO01BQ2xFLENBQUUsQ0FBQztJQUNKO0VBQ0Q7RUFFQSxJQUFJLDJCQUEyQixDQUFDLENBQUM7QUFDbEMsQ0FBQyxFQUFJLFFBQVMsQ0FBQzs7Ozs7QUNoRmY7QUFDQTtBQUNBLENBQUUsQ0FBRSxRQUFRLEVBQUUsTUFBTSxLQUFNO0VBQ3pCLFlBQVk7O0VBRVosTUFBTSxZQUFZLEdBQUc7SUFDcEIsTUFBTSxFQUFFLEtBQUs7SUFBSztJQUNsQixTQUFTLEVBQUUsSUFBSSxDQUFHO0VBQ25CLENBQUM7O0VBRUQ7RUFDQSxRQUFRLENBQUMsZ0JBQWdCLENBQUUsc0JBQXNCLEVBQUUscUJBQXNCLENBQUM7RUFDMUUsUUFBUSxDQUFDLGdCQUFnQixDQUFFLDZCQUE2QixFQUFFLE1BQU0sa0NBQWtDLENBQUMsb0JBQW9CLENBQUUsQ0FBQztFQUMxSCxRQUFRLENBQUMsZ0JBQWdCLENBQUUsOEJBQThCLEVBQUUsTUFBTSxtQ0FBbUMsQ0FBRSxxQkFBc0IsQ0FBRSxDQUFDO0VBQy9ILFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBRSw2QkFBNkIsRUFBRSxNQUFNLGdDQUFnQyxDQUFFLFlBQVksQ0FBQyxTQUFVLENBQUUsQ0FBQztFQUU1SCxRQUFRLENBQUMsZ0JBQWdCLENBQUUsa0JBQWtCLEVBQUUsTUFBTTtJQUNwRCxRQUFRLENBQUMsZ0JBQWdCLENBQUUscUJBQXNCLENBQUMsQ0FBQyxPQUFPLENBQUksRUFBRSxJQUFNO01BQ3JFLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBRSxPQUFPLEVBQUksQ0FBQyxJQUFNO1FBQ3RDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQztRQUNsQixNQUFNLEtBQUssR0FBRyxFQUFFLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBRSw0QkFBNkIsQ0FBQztRQUNuRSxxQkFBcUIsQ0FBRSxLQUFNLENBQUM7TUFDL0IsQ0FBRSxDQUFDO0lBQ0osQ0FBRSxDQUFDOztJQUVIO0lBQ0EsVUFBVSxDQUFDLElBQUksQ0FBRTtNQUNoQixhQUFhLEVBQUU7SUFDaEIsQ0FBRSxDQUFDO0lBRUgscUJBQXFCLENBQUMsQ0FBQzs7SUFFdkI7SUFDQSxJQUFLLENBQUUsTUFBTSxDQUFDLGtCQUFrQixJQUFJLE1BQU0sQ0FBQyxrQkFBa0IsS0FBSyxFQUFFLEVBQUc7TUFDdEUsY0FBYyxDQUFDLENBQUM7TUFFaEIsTUFBTSxNQUFNLEdBQUcsUUFBUSxDQUFDLGNBQWMsQ0FBQyxrQkFBa0IsQ0FBQztNQUMxRCxNQUFNLE1BQU0sR0FBRyxRQUFRLENBQUMsY0FBYyxDQUFDLDRCQUE0QixDQUFDO01BQ3BFLElBQUssTUFBTSxJQUFJLE1BQU0sRUFBRztRQUN2QixNQUFNLENBQUMsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLFlBQVc7VUFDMUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUcsTUFBTTtRQUM5QixDQUFDLENBQUM7TUFDSDtJQUNEO0VBQ0QsQ0FBRSxDQUFDOztFQUVIO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTLFVBQVUsQ0FBQSxFQUFHO0lBQ3JCLE9BQU8sTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJLEtBQUssV0FBVztFQUM1QztFQUVBLFNBQVMsb0JBQW9CLENBQUEsRUFBRztJQUMvQixNQUFNLFFBQVEsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLDBCQUEyQixDQUFDO0lBQ3JFLE1BQU0sTUFBTSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsb0JBQXFCLENBQUM7O0lBRTdEO0lBQ0EsSUFBSyxNQUFNLElBQUksQ0FBRSxNQUFNLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBRSxjQUFlLENBQUMsRUFBRztNQUM5RCxnQ0FBZ0MsQ0FBRSxZQUFZLENBQUMsTUFBTyxDQUFDO0lBQ3hELENBQUMsTUFBTSxJQUFLLFFBQVEsSUFBSSxDQUFFLFFBQVEsQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFFLGNBQWUsQ0FBQyxFQUFHO01BQ3pFLGdDQUFnQyxDQUFFLFlBQVksQ0FBQyxTQUFVLENBQUM7SUFDM0Q7RUFDRDtFQUVBLE1BQU0sQ0FBQyxnQkFBZ0IsQ0FBRSxNQUFNLEVBQUUsTUFBTTtJQUN0QyxJQUFJLE9BQU8sR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLHlCQUEwQixDQUFDO01BQ2hFLFFBQVEsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLDBCQUEyQixDQUFDO01BQy9ELFFBQVEsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLDBCQUEyQixDQUFDO01BQy9ELE1BQU0sR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFFLG9CQUFxQixDQUFDO01BQ3ZELFdBQVcsR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFDLDhCQUE4QixDQUFDO0lBRXJFLE1BQU0sU0FBUyxHQUFHLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBRSwyQkFBNEIsQ0FBQzs7SUFFMUU7QUFDRjtBQUNBO0FBQ0E7QUFDQTtJQUNFLFNBQVMsaUJBQWlCLENBQUEsRUFBRztNQUM1QixJQUFLLENBQUUsTUFBTSxJQUFJLENBQUUsU0FBUyxDQUFDLE1BQU0sRUFBRztRQUNyQztNQUNEO01BRUEsTUFBTSxXQUFXLEdBQUcsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsOEJBQStCLENBQUM7TUFDN0UsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUUsNkJBQTZCLEVBQUUsQ0FBRSxXQUFZLENBQUM7TUFDdkUsU0FBUyxDQUFDLE9BQU8sQ0FBSSxFQUFFLElBQU07UUFDNUIsRUFBRSxDQUFDLFlBQVksQ0FBRSxlQUFlLEVBQUUsV0FBVyxHQUFHLE9BQU8sR0FBRyxNQUFPLENBQUM7TUFDbkUsQ0FBRSxDQUFDO01BRUgsSUFBSSxDQUFFLFdBQVcsRUFBRztRQUNuQixrQ0FBa0MsQ0FBRSxRQUFTLENBQUM7TUFDL0MsQ0FBQyxNQUFNO1FBQ04sbUNBQW1DLENBQUMsQ0FBQztNQUN0QztJQUNEO0lBRUEsSUFBSyxTQUFTLENBQUMsTUFBTSxJQUFJLE1BQU0sRUFBRztNQUNqQyxTQUFTLENBQUMsT0FBTyxDQUFJLEVBQUUsSUFBTTtRQUM1QixFQUFFLENBQUMsZ0JBQWdCLENBQUUsT0FBTyxFQUFFLGlCQUFrQixDQUFDO1FBQ2pELEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBRSxTQUFTLEVBQUksS0FBSyxJQUFNO1VBQzVDLElBQUssT0FBTyxLQUFLLEtBQUssQ0FBQyxHQUFHLElBQUksR0FBRyxLQUFLLEtBQUssQ0FBQyxHQUFHLEVBQUc7WUFDakQsS0FBSyxDQUFDLGNBQWMsQ0FBQyxDQUFDO1lBQ3RCLGlCQUFpQixDQUFDLENBQUM7VUFDcEI7UUFDRCxDQUFFLENBQUM7TUFDSixDQUFFLENBQUM7SUFDSjs7SUFFQTtJQUNBLG9CQUFvQixDQUFDLENBQUM7O0lBRXRCO0lBQ0EsTUFBTSxDQUFDLGdCQUFnQixDQUFFLFlBQVksRUFBRSxNQUFNO01BQzVDLG9CQUFvQixDQUFDLENBQUM7TUFDdEIscUJBQXFCLENBQUMsQ0FBQztJQUN4QixDQUFFLENBQUM7O0lBRUg7SUFDQSxNQUFNLE1BQU0sR0FBRztNQUNkLE9BQU8sRUFBRTtRQUNSLE9BQU8sRUFBRSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsK0NBQStDLENBQUM7UUFDbkYsT0FBTyxFQUFFLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyxpQ0FBaUMsQ0FBQztRQUNyRSxNQUFNLEVBQUUsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGlEQUFpRDtNQUNwRixDQUFDO01BQ0QsTUFBTSxFQUFFO1FBQ1AsT0FBTyxFQUFFLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyw4Q0FBOEMsQ0FBQztRQUNsRixPQUFPLEVBQUUsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGdDQUFnQyxDQUFDO1FBQ3BFLE1BQU0sRUFBRSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsZ0RBQWdEO01BQ25GO0lBQ0QsQ0FBQzs7SUFFRDtJQUNBLElBQUssV0FBVyxFQUFHO01BQ2xCLFdBQVcsQ0FBQyxnQkFBZ0IsQ0FBQyxRQUFRLEVBQUUsWUFBWTtRQUNsRCxNQUFNLFFBQVEsR0FBRyxJQUFJLENBQUMsT0FBTztRQUU3QixJQUFJLFFBQVEsRUFBRTtVQUNiLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sQ0FBQyxJQUFJLElBQUksSUFBSSxDQUFDLE9BQU8sQ0FBQyxFQUFFLElBQUksRUFBRSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsY0FBYyxDQUFDLENBQUMsQ0FBQztVQUNuRyxNQUFNLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxJQUFJLElBQUksQ0FBQyxPQUFPLENBQUMsRUFBRSxJQUFJLEVBQUUsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLGNBQWMsQ0FBQyxDQUFDLENBQUM7UUFDdEcsQ0FBQyxNQUFNO1VBQ04sTUFBTSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksSUFBSSxJQUFJLENBQUMsT0FBTyxDQUFDLEVBQUUsSUFBSSxFQUFFLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxjQUFjLENBQUMsQ0FBQyxDQUFDO1VBQ3RHLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDLE9BQU8sQ0FBQyxJQUFJLElBQUksSUFBSSxDQUFDLE9BQU8sQ0FBQyxFQUFFLElBQUksRUFBRSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsY0FBYyxDQUFDLENBQUMsQ0FBQztRQUNuRzs7UUFFQTtRQUNBLDJCQUEyQixDQUFDLFFBQVEsQ0FBQztNQUN0QyxDQUFDLENBQUM7SUFDSDs7SUFFQTtJQUNBLE1BQU0sYUFBYSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUMsK0JBQStCLENBQUM7SUFDN0UsSUFBSSxhQUFhLEVBQUU7TUFDbEIsYUFBYSxDQUFDLGdCQUFnQixDQUFDLE9BQU8sRUFBRSxNQUFNO1FBQzdDLDJCQUEyQixDQUFDLENBQUM7TUFDOUIsQ0FBQyxDQUFDO0lBQ0g7RUFFRCxDQUFFLENBQUM7RUFFSCxNQUFNLENBQUMsU0FBUyxHQUFLLENBQUMsSUFBTTtJQUMzQixNQUFNLFNBQVMsR0FBRyxnQkFBZ0IsQ0FBQyxVQUFVO0lBRTdDLElBQUssQ0FBQyxDQUFDLE1BQU0sS0FBSyxTQUFTLEVBQUc7TUFDN0I7SUFDRDtJQUVBLGlCQUFpQixDQUFFLENBQUMsQ0FBQyxJQUFLLENBQUM7SUFDM0IsVUFBVSxDQUFFLENBQUMsQ0FBQyxJQUFLLENBQUM7SUFDcEIsWUFBWSxDQUFFLENBQUMsQ0FBQyxJQUFJLEVBQUUsU0FBVSxDQUFDO0lBQ2pDLGFBQWEsQ0FBRSxDQUFDLENBQUMsSUFBSyxDQUFDO0lBQ3ZCLFNBQVMsQ0FBRSxDQUFDLENBQUMsSUFBSSxFQUFFLFNBQVUsQ0FBQztJQUM5QixVQUFVLENBQUUsQ0FBQyxDQUFDLElBQUksRUFBRSxTQUFVLENBQUM7SUFDL0IscUJBQXFCLENBQUUsQ0FBQyxDQUFDLElBQUssQ0FBQztFQUNoQyxDQUFDO0VBRUQsU0FBUyxrQkFBa0IsQ0FBQSxFQUFHO0lBQzdCLE1BQU0sZUFBZSxHQUFHLFFBQVEsQ0FBQyxjQUFjLENBQUMsa0JBQWtCLENBQUM7SUFDbkUsSUFBSyxDQUFDLGVBQWUsSUFBSSxDQUFDLGVBQWUsQ0FBQyxPQUFPLElBQUksQ0FBQyxlQUFlLENBQUMsT0FBTyxDQUFDLEdBQUcsSUFBSSxlQUFlLENBQUMsT0FBTyxDQUFDLEdBQUcsS0FBSyxlQUFlLENBQUMsR0FBRyxFQUFHO01BQzFJO0lBQ0Q7SUFDQSxlQUFlLENBQUMsR0FBRyxHQUFHLGVBQWUsQ0FBQyxPQUFPLENBQUMsR0FBRztJQUNqRCxVQUFVLENBQUMsSUFBSSxDQUFFLHFCQUFzQixDQUFDO0VBQ3pDO0VBRUEsU0FBUyxxQkFBcUIsQ0FBRSxLQUFLLEVBQUc7SUFDdkMsSUFBSSxXQUFXLEdBQUcsQ0FBQyxNQUFNLENBQUMsa0JBQWtCO0lBQzVDO0lBQ0EsSUFBSyxLQUFLLEVBQUc7TUFDWiw4QkFBOEIsQ0FBQyxXQUFXLENBQUM7SUFDNUM7O0lBRUE7SUFDQSxJQUFLLENBQUMsV0FBVyxFQUFHO01BQ25CO01BQ0EsVUFBVSxDQUFFLFlBQVc7UUFDdEIsTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJLEdBQUcsTUFBTSxDQUFDLGtCQUFrQjtNQUNqRCxDQUFDLEVBQUUsR0FBSSxDQUFDO0lBQ1QsQ0FBQyxNQUFNO01BQ047TUFDQSxrQkFBa0IsQ0FBQyxDQUFDO0lBQ3JCO0VBQ0Q7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsMkJBQTJCLENBQUUsUUFBUSxFQUFHO0lBQ2hELElBQUssQ0FBRSxNQUFNLENBQUMsa0JBQWtCLElBQUksTUFBTSxDQUFDLGtCQUFrQixLQUFLLEVBQUUsRUFBRztNQUN0RTtJQUNEO0lBRUEsTUFBTSxDQUFDLGtCQUFrQixHQUFHLGlCQUFpQixDQUFDLE1BQU0sQ0FBQyxrQkFBa0IsRUFBRSxRQUFRLENBQUM7RUFDbkY7RUFFQSxTQUFTLGNBQWMsQ0FBQSxFQUFHO0lBQ3pCLElBQUksUUFBUSxHQUFHLEVBQUU7SUFFakIsUUFBUSxJQUFJLGlDQUFpQztJQUM3QyxRQUFRLElBQUksU0FBUyxHQUFHLGdCQUFnQixDQUFDLEtBQUs7SUFFOUMsTUFBTSxPQUFPLEdBQUcsZUFBZSxDQUFFLFFBQVMsQ0FBQztJQUUzQyxPQUFPLENBQUMsa0JBQWtCLEdBQUcsTUFBTTtNQUNsQyxJQUFLLE9BQU8sQ0FBQyxVQUFVLEtBQUssY0FBYyxDQUFDLElBQUksSUFBSSxHQUFHLEtBQUssT0FBTyxDQUFDLE1BQU0sRUFBRztRQUMzRSxJQUFJLFdBQVcsR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUM7UUFFbEQsSUFBSyxJQUFJLEtBQUssV0FBVyxDQUFDLE9BQU8sRUFBRztVQUNuQyxrQkFBa0IsQ0FBQyxDQUFDO1FBQ3JCO01BQ0Q7SUFDRCxDQUFDO0VBQ0Y7RUFFQSxTQUFTLHFCQUFxQixDQUFBLEVBQUc7SUFDaEMsTUFBTSxTQUFTLEdBQUcsSUFBSSxlQUFlLENBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxNQUFPLENBQUM7SUFFL0QsSUFBSyxTQUFTLENBQUMsR0FBRyxDQUFFLHVCQUF3QixDQUFDLElBQUksR0FBRyxLQUFLLFNBQVMsQ0FBQyxHQUFHLENBQUUsdUJBQXdCLENBQUMsRUFBRztNQUNuRztNQUNBLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxHQUFHLFdBQVc7TUFFbEMsa0JBQWtCLENBQUMsQ0FBQzs7TUFFcEI7TUFDQSxTQUFTLENBQUMsTUFBTSxDQUFFLHVCQUF3QixDQUFDO01BQzNDLE1BQU0sTUFBTSxHQUFHLFNBQVMsQ0FBQyxRQUFRLENBQUMsQ0FBQztNQUNuQyxNQUFNLE1BQU0sR0FBRyxNQUFNLENBQUMsUUFBUSxDQUFDLFFBQVEsSUFBSyxNQUFNLEdBQUcsR0FBRyxHQUFHLE1BQU0sR0FBRyxFQUFFLENBQUUsR0FBRyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUk7TUFDL0YsTUFBTSxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUUsQ0FBQyxDQUFDLEVBQUUsRUFBRSxFQUFFLE1BQU8sQ0FBQztJQUM5QztFQUNEO0VBRUEsU0FBUyxVQUFVLENBQUUsSUFBSSxFQUFHO0lBQzNCLElBQUssQ0FBRSxJQUFJLENBQUMsY0FBYyxDQUFFLGVBQWdCLENBQUMsRUFBRztNQUMvQztJQUNEO0lBRUEsVUFBVSxDQUFDLEtBQUssQ0FBRSxxQkFBc0IsQ0FBQztJQUN6QztJQUNBLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsR0FBRyxFQUFFO0lBRWpDLElBQUksS0FBSyxHQUFHLENBQUUsd0JBQXdCLEVBQUUsNEJBQTRCLENBQUU7SUFFdEUsSUFBSyxDQUFFLElBQUksQ0FBQyxjQUFjLENBQUUsa0JBQW1CLENBQUMsRUFBRztNQUNsRDtJQUNEO0lBRUEsSUFBSyxLQUFLLENBQUMsT0FBTyxDQUFFLElBQUksQ0FBQyxnQkFBaUIsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFHO01BQ3BEO0lBQ0Q7SUFFQSxRQUFRLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDO0VBQzNCO0VBRUEsU0FBUyxhQUFhLENBQUUsSUFBSSxFQUFHO0lBQzlCLElBQUssQ0FBRSxJQUFJLENBQUMsY0FBYyxDQUFFLG1CQUFvQixDQUFDLEVBQUc7TUFDbkQ7SUFDRDtJQUVBLElBQUksUUFBUSxHQUFHLEVBQUU7SUFFakIsUUFBUSxJQUFJLDhCQUE4QjtJQUMxQyxRQUFRLElBQUksVUFBVSxHQUFHLElBQUksQ0FBQyxpQkFBaUI7SUFDL0MsUUFBUSxJQUFJLFNBQVMsR0FBRyxnQkFBZ0IsQ0FBQyxLQUFLO0lBRTlDLGVBQWUsQ0FBRSxRQUFTLENBQUM7RUFDNUI7RUFFQSxTQUFTLFNBQVMsQ0FBRSxJQUFJLEVBQUUsU0FBUyxFQUFHO0lBQ3JDLElBQUksTUFBTSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUJBQW9CLENBQUMsQ0FBQyxhQUFhO0lBRXhFLElBQUssQ0FBRSxJQUFJLENBQUMsY0FBYyxDQUFFLGVBQWdCLENBQUMsRUFBRztNQUMvQztJQUNEO0lBRUEsSUFBSSxRQUFRLEdBQUcsRUFBRTtJQUVqQixRQUFRLElBQUkseUJBQXlCO0lBQ3JDLFFBQVEsSUFBSSxXQUFXLEdBQUcsSUFBSSxDQUFDLGFBQWE7SUFDNUMsUUFBUSxJQUFJLFNBQVMsR0FBRyxnQkFBZ0IsQ0FBQyxLQUFLO0lBRTlDLE1BQU0sT0FBTyxHQUFHLGVBQWUsQ0FBRSxRQUFTLENBQUM7SUFFM0MsT0FBTyxDQUFDLGtCQUFrQixHQUFHLE1BQU07TUFDbEMsSUFBSyxPQUFPLENBQUMsVUFBVSxLQUFLLGNBQWMsQ0FBQyxJQUFJLElBQUksR0FBRyxLQUFLLE9BQU8sQ0FBQyxNQUFNLEVBQUc7UUFDM0UsSUFBSSxXQUFXLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsWUFBWSxDQUFDO1FBQ2xELE1BQU0sQ0FBQyxXQUFXLENBQ2pCO1VBQ0MsU0FBUyxFQUFFLFdBQVcsQ0FBQyxPQUFPO1VBQzlCLE1BQU0sRUFBRSxXQUFXLENBQUMsSUFBSTtVQUN4QixXQUFXLEVBQUU7UUFDZCxDQUFDLEVBQ0QsU0FDRCxDQUFDO01BQ0Y7SUFDRCxDQUFDO0VBQ0Y7RUFFQSxTQUFTLGlCQUFpQixDQUFDLEdBQUcsRUFBRSxRQUFRLEVBQUU7SUFDekM7SUFDQSxJQUFJLE1BQU0sR0FBRyxHQUFHLENBQUMsT0FBTyxDQUFDLHlCQUF5QixFQUFFLEVBQUUsQ0FBQztJQUN2RDtJQUNBLE1BQU0sR0FBRyxHQUFHLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLEdBQUcsR0FBRyxHQUFHLEdBQUc7SUFDNUMsTUFBTSxJQUFJLEdBQUcsR0FBRyxhQUFhLElBQUksUUFBUSxHQUFHLEdBQUcsR0FBRyxHQUFHLENBQUM7SUFDdEQsT0FBTyxNQUFNO0VBQ2Q7RUFFQSxTQUFTLFVBQVUsQ0FBRSxJQUFJLEVBQUUsU0FBUyxFQUFHO0lBQ3RDLElBQUksTUFBTSxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUJBQW9CLENBQUMsQ0FBQyxhQUFhO0lBRXhFLElBQUssQ0FBRSxJQUFJLENBQUMsY0FBYyxDQUFFLG1CQUFvQixDQUFDLEVBQUc7TUFDbkQ7SUFDRDtJQUVBLElBQUksUUFBUSxHQUFHLEVBQUU7SUFFakIsUUFBUSxJQUFJLDBCQUEwQjtJQUN0QyxRQUFRLElBQUksU0FBUyxHQUFHLGdCQUFnQixDQUFDLEtBQUs7SUFFOUMsTUFBTSxPQUFPLEdBQUcsZUFBZSxDQUFFLFFBQVMsQ0FBQztJQUUzQyxPQUFPLENBQUMsa0JBQWtCLEdBQUcsTUFBTTtNQUNsQyxJQUFLLE9BQU8sQ0FBQyxVQUFVLEtBQUssY0FBYyxDQUFDLElBQUksSUFBSSxHQUFHLEtBQUssT0FBTyxDQUFDLE1BQU0sRUFBRztRQUMzRSxJQUFJLFdBQVcsR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUM7UUFDbEQsTUFBTSxDQUFDLFdBQVcsQ0FDakI7VUFDQyxTQUFTLEVBQUUsV0FBVyxDQUFDLE9BQU87VUFDOUIsTUFBTSxFQUFFLFdBQVcsQ0FBQyxJQUFJO1VBQ3hCLFdBQVcsRUFBRTtRQUNkLENBQUMsRUFDRCxTQUNELENBQUM7TUFDRjtJQUNELENBQUM7RUFDRjtFQUVBLFNBQVMsZUFBZSxDQUFFLFFBQVEsRUFBRztJQUNwQyxNQUFNLFdBQVcsR0FBRyxJQUFJLGNBQWMsQ0FBQyxDQUFDO0lBRXhDLFdBQVcsQ0FBQyxJQUFJLENBQUUsTUFBTSxFQUFFLE9BQVEsQ0FBQztJQUNuQyxXQUFXLENBQUMsZ0JBQWdCLENBQUUsY0FBYyxFQUFFLG1DQUFvQyxDQUFDO0lBQ25GLFdBQVcsQ0FBQyxJQUFJLENBQUUsUUFBUyxDQUFDO0lBRTVCLE9BQU8sV0FBVztFQUNuQjtFQUVBLFNBQVMsaUJBQWlCLENBQUUsSUFBSSxFQUFHO0lBQ2xDLElBQUssQ0FBRSxJQUFJLENBQUMsY0FBYyxDQUFFLGdCQUFpQixDQUFDLEVBQUc7TUFDaEQ7SUFDRDtJQUVBLFFBQVEsQ0FBQyxjQUFjLENBQUUsa0JBQW1CLENBQUMsQ0FBQyxLQUFLLENBQUMsTUFBTSxHQUFHLEdBQUksSUFBSSxDQUFDLGNBQWMsSUFBSztFQUMxRjtFQUVBLFNBQVMsWUFBWSxDQUFFLElBQUksRUFBRSxTQUFTLEVBQUc7SUFDeEMsSUFBSSxNQUFNLEdBQUcsUUFBUSxDQUFDLGFBQWEsQ0FBRSxtQkFBb0IsQ0FBQyxDQUFDLGFBQWE7SUFFeEUsSUFBSyxDQUFFLElBQUksQ0FBQyxjQUFjLENBQUUsaUJBQWtCLENBQUMsRUFBRztNQUNqRCxJQUFJLElBQUksR0FBRztRQUFDLE9BQU8sRUFBQyxXQUFXO1FBQUUsT0FBTyxFQUFDO01BQW9CLENBQUM7TUFDOUQsTUFBTSxDQUFDLFdBQVcsQ0FDakI7UUFDQyxTQUFTLEVBQUUsS0FBSztRQUNoQixNQUFNLEVBQUUsSUFBSTtRQUNaLFdBQVcsRUFBRTtNQUNkLENBQUMsRUFDRCxTQUNELENBQUM7TUFDRDtJQUNEO0lBRUEsSUFBSSxRQUFRLEdBQUcsRUFBRTtJQUVqQixRQUFRLElBQUksNkJBQTZCO0lBQ3pDLFFBQVEsSUFBSSxTQUFTLEdBQUcsSUFBSSxDQUFDLGVBQWU7SUFDNUMsUUFBUSxJQUFJLFNBQVMsR0FBRyxnQkFBZ0IsQ0FBQyxLQUFLO0lBRTlDLE1BQU0sT0FBTyxHQUFHLGVBQWUsQ0FBRSxRQUFTLENBQUM7SUFFM0MsT0FBTyxDQUFDLGtCQUFrQixHQUFHLE1BQU07TUFDbEMsSUFBSyxPQUFPLENBQUMsVUFBVSxLQUFLLGNBQWMsQ0FBQyxJQUFJLElBQUksR0FBRyxLQUFLLE9BQU8sQ0FBQyxNQUFNLEVBQUc7UUFDM0UsSUFBSSxXQUFXLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsWUFBWSxDQUFDO1FBQ2xELE1BQU0sQ0FBQyxXQUFXLENBQ2pCO1VBQ0MsU0FBUyxFQUFFLFdBQVcsQ0FBQyxPQUFPO1VBQzlCLE1BQU0sRUFBRSxXQUFXLENBQUMsSUFBSTtVQUN4QixXQUFXLEVBQUU7UUFDZCxDQUFDLEVBQ0QsU0FDRCxDQUFDO01BQ0Y7SUFDRCxDQUFDO0VBQ0Y7RUFFQSxTQUFTLHFCQUFxQixDQUFFLElBQUksRUFBRztJQUN0QyxJQUFLLENBQUUsSUFBSSxDQUFDLGNBQWMsQ0FBRSwwQkFBMkIsQ0FBQyxJQUFJLENBQUUsSUFBSSxDQUFDLGNBQWMsQ0FBRSwwQkFBMkIsQ0FBQyxFQUFHO01BQ2pIO0lBQ0Q7SUFFQSxJQUFJLFFBQVEsR0FBRyxFQUFFO0lBRWpCLFFBQVEsSUFBSSx1Q0FBdUM7SUFDbkQsUUFBUSxJQUFJLFdBQVcsR0FBRyxJQUFJLENBQUMsd0JBQXdCO0lBQ3ZELFFBQVEsSUFBSSxhQUFhLEdBQUcsSUFBSSxDQUFDLHdCQUF3QjtJQUN6RCxRQUFRLElBQUksU0FBUyxHQUFHLGdCQUFnQixDQUFDLEtBQUs7SUFFOUMsTUFBTSxPQUFPLEdBQUcsZUFBZSxDQUFFLFFBQVMsQ0FBQztFQUM1Qzs7RUFFQTtBQUNEO0FBQ0E7RUFDQyxTQUFTLHFCQUFxQixDQUFBLEVBQUc7SUFDaEMsSUFBSyxDQUFFLFVBQVUsQ0FBQyxDQUFDLEVBQUc7TUFDckI7SUFDRDtJQUVBLE1BQU0sU0FBUyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsNEJBQTZCLENBQUM7SUFFeEUsSUFBSyxDQUFFLFNBQVMsRUFBRztNQUNsQjtJQUNEO0lBRUEsTUFBTSxPQUFPLEdBQUcsU0FBUyxDQUFDLFlBQVksQ0FBRSxlQUFnQixDQUFDO0lBRXpELElBQUksQ0FBRSxPQUFPLEVBQUc7TUFDZjtJQUNEO0lBRUEsSUFBSyxPQUFPLFFBQVEsS0FBSyxXQUFXLElBQUksQ0FBQyxRQUFRLENBQUMsS0FBSyxFQUFHO01BQ3pEO0lBQ0Q7O0lBRUE7SUFDQSxJQUFLLE9BQU8sb0JBQW9CLEtBQUssV0FBVyxJQUFJLENBQUMsb0JBQW9CLENBQUMsYUFBYSxJQUFJLG9CQUFvQixDQUFDLGFBQWEsS0FBSyxHQUFHLEVBQUc7TUFDdkk7SUFDRDs7SUFFQTtJQUNBLElBQUksb0JBQW9CLENBQUMsT0FBTyxJQUFJLE9BQU8sUUFBUSxDQUFDLFFBQVEsS0FBSyxVQUFVLEVBQUU7TUFDNUUsUUFBUSxDQUFDLFFBQVEsQ0FBQyxvQkFBb0IsQ0FBQyxPQUFPLENBQUM7SUFDaEQ7SUFFQSxRQUFRLENBQUMsS0FBSyxDQUFDLGdCQUFnQixFQUFFO01BQ2hDLE9BQU8sRUFBRSxvQkFBb0IsQ0FBQyxPQUFPO01BQ3JDLE1BQU0sRUFBRSxvQkFBb0IsQ0FBQyxNQUFNO01BQ25DLEtBQUssRUFBRSxvQkFBb0IsQ0FBQyxLQUFLO01BQ2pDLFdBQVcsRUFBRSxvQkFBb0IsQ0FBQyxHQUFHO01BQ3JDLFFBQVEsRUFBRTtJQUNYLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtFQUNDLFNBQVMsMkJBQTJCLENBQUEsRUFBRztJQUN0QyxJQUFJLE9BQU8sUUFBUSxLQUFLLFdBQVcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxLQUFLLEVBQUU7TUFDdkQ7SUFDRDs7SUFFQTtJQUNBLElBQUksT0FBTyxvQkFBb0IsS0FBSyxXQUFXLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxhQUFhLElBQUksb0JBQW9CLENBQUMsYUFBYSxLQUFLLEdBQUcsRUFBRTtNQUNySTtJQUNEOztJQUVBO0lBQ0EsSUFBSSxvQkFBb0IsQ0FBQyxPQUFPLElBQUksT0FBTyxRQUFRLENBQUMsUUFBUSxLQUFLLFVBQVUsRUFBRTtNQUM1RSxRQUFRLENBQUMsUUFBUSxDQUFDLG9CQUFvQixDQUFDLE9BQU8sQ0FBQztJQUNoRDtJQUVBLFFBQVEsQ0FBQyxLQUFLLENBQUMseUNBQXlDLEVBQUU7TUFDekQsT0FBTyxFQUFFLG9CQUFvQixDQUFDLE9BQU87TUFDckMsTUFBTSxFQUFFLG9CQUFvQixDQUFDLE1BQU07TUFDbkMsS0FBSyxFQUFFLG9CQUFvQixDQUFDLEtBQUs7TUFDakMsV0FBVyxFQUFFLG9CQUFvQixDQUFDO0lBQ25DLENBQUMsQ0FBQztFQUNIOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsaUNBQWlDLENBQUUsU0FBUyxFQUFFLFVBQVUsRUFBRztJQUNuRSxJQUFLLE9BQU8sUUFBUSxLQUFLLFdBQVcsSUFBSSxDQUFFLFFBQVEsQ0FBQyxLQUFLLEVBQUc7TUFDMUQ7SUFDRDs7SUFFQTtJQUNBLElBQUssT0FBTyxvQkFBb0IsS0FBSyxXQUFXLElBQUksQ0FBRSxvQkFBb0IsQ0FBQyxhQUFhLElBQUksb0JBQW9CLENBQUMsYUFBYSxLQUFLLEdBQUcsRUFBRztNQUN4STtJQUNEOztJQUVBO0lBQ0EsSUFBSyxDQUFFLG9CQUFvQixDQUFDLE9BQU8sSUFBSSxPQUFPLFFBQVEsQ0FBQyxRQUFRLEtBQUssVUFBVSxFQUFHO01BQ2hGO0lBQ0Q7SUFFQSxRQUFRLENBQUMsUUFBUSxDQUFFLG9CQUFvQixDQUFDLE9BQVEsQ0FBQztJQUVqRCxJQUFJLEtBQUssR0FBRztNQUNYLE9BQU8sRUFBRSxvQkFBb0IsQ0FBQyxPQUFPO01BQ3JDLE1BQU0sRUFBRSxvQkFBb0IsQ0FBQyxNQUFNO01BQ25DLEtBQUssRUFBRSxvQkFBb0IsQ0FBQyxLQUFLO01BQ2pDLFdBQVcsRUFBRSxvQkFBb0IsQ0FBQyxHQUFHO01BQ3JDLElBQUksRUFBRSxvQkFBb0IsQ0FBQztJQUM1QixDQUFDOztJQUVEO0lBQ0EsSUFBSyxVQUFVLElBQUksT0FBTyxVQUFVLEtBQUssUUFBUSxFQUFHO01BQ25ELEtBQU0sSUFBSSxHQUFHLElBQUksVUFBVSxFQUFHO1FBQzdCLElBQUssTUFBTSxDQUFDLFNBQVMsQ0FBQyxjQUFjLENBQUMsSUFBSSxDQUFFLFVBQVUsRUFBRSxHQUFJLENBQUMsRUFBRztVQUM5RCxLQUFLLENBQUUsR0FBRyxDQUFFLEdBQUcsVUFBVSxDQUFFLEdBQUcsQ0FBRTtRQUNqQztNQUNEO0lBQ0Q7SUFFQSxRQUFRLENBQUMsS0FBSyxDQUFFLFNBQVMsRUFBRSxLQUFNLENBQUM7RUFDbkM7O0VBRUE7QUFDRDtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVMsZ0NBQWdDLENBQUUsWUFBWSxHQUFHLEtBQUssRUFBRztJQUNqRSxJQUFLLENBQUUsVUFBVSxDQUFDLENBQUMsRUFBRztNQUNyQjtJQUNEO0lBQ0EsTUFBTSxJQUFJLEdBQUcsTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJO0lBQ2pDLE1BQU0sUUFBUSxHQUFLLE9BQU8sb0JBQW9CLEtBQUssV0FBVyxJQUFJLG9CQUFvQixDQUFDLElBQUksR0FBSyxvQkFBb0IsQ0FBQyxJQUFJLEdBQUcsRUFBRTtJQUM5SCxpQ0FBaUMsQ0FBRSxnQ0FBZ0MsRUFBRTtNQUNwRSxLQUFLLEVBQU0sWUFBWSxHQUFHLFdBQVcsR0FBRyxRQUFRO01BQ2hELFNBQVMsRUFBRSxJQUFJO01BQ2YsSUFBSSxFQUFPLFFBQVEsR0FBRztJQUN2QixDQUFFLENBQUM7RUFDSjs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxrQ0FBa0MsQ0FBRSxPQUFPLEVBQUc7SUFDdEQsaUNBQWlDLENBQUUsa0NBQWtDLEVBQUU7TUFDdEUsUUFBUSxFQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSTtNQUM5QixPQUFPLEVBQUU7SUFDVixDQUFFLENBQUM7RUFDSjs7RUFFQTtBQUNEO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsU0FBUyxtQ0FBbUMsQ0FBRSxPQUFPLEdBQUcsUUFBUSxFQUFHO0lBQ2xFLGlDQUFpQyxDQUFFLG1DQUFtQyxFQUFFO01BQ3ZFLFFBQVEsRUFBRSxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUk7TUFDOUIsT0FBTyxFQUFFO0lBQ1YsQ0FBRSxDQUFDO0VBQ0o7O0VBRUE7QUFDRDtBQUNBO0VBQ0MsU0FBUyw4QkFBOEIsQ0FBRSxXQUFXLEdBQUcsS0FBSyxFQUFHO0lBQzlELE1BQU0sU0FBUyxHQUFHLFFBQVEsQ0FBQyxhQUFhLENBQUUsbUNBQW9DLENBQUM7SUFDL0UsTUFBTSxVQUFVLEdBQUcsU0FBUyxHQUFHLFNBQVMsQ0FBQyxnQkFBZ0IsQ0FBRSxXQUFZLENBQUMsQ0FBQyxNQUFNLEdBQUcsQ0FBQztJQUVuRixpQ0FBaUMsQ0FBRSw4QkFBOEIsRUFBRTtNQUNsRSxXQUFXLEVBQUUsV0FBVyxHQUFHLFFBQVEsR0FBRyxrQkFBa0I7TUFDeEQsV0FBVyxFQUFFO0lBQ2QsQ0FBRSxDQUFDO0VBQ0o7QUFDRCxDQUFDLEVBQUksUUFBUSxFQUFFLE1BQU8sQ0FBQzs7Ozs7QUNybEJ2QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsY0FBYyxFQUFDLENBQUMsZ0JBQWdCLEVBQUMscUJBQXFCLEVBQUMsV0FBVyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxrQkFBa0IsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGtCQUFrQixLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGlCQUFpQixLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxhQUFhLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsVUFBVTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLE9BQU87TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLE9BQU87TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztVQUFDLFVBQVUsRUFBQyxDQUFDO1VBQUMsZ0JBQWdCLEVBQUMsQ0FBQztVQUFDLGVBQWUsRUFBQyxDQUFDO1VBQUMsaUJBQWlCLEVBQUMsSUFBSSxDQUFDO1FBQWlCLENBQUMsQ0FBQztNQUFDLEtBQUksUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxlQUFlLEtBQUcsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLGlCQUFpQixLQUFHLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsS0FBSSxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFFLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUUsUUFBUSxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1lBQUMsTUFBTSxFQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxLQUFHLFVBQVUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxPQUFPLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsVUFBVSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE1BQUssYUFBYSxHQUFDLENBQUMsR0FBQyx1RUFBdUU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsWUFBWSxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLElBQUk7TUFBQTtNQUFDLE9BQU0sUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsY0FBYyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO01BQUMsSUFBRyxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLFlBQVcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUk7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxPQUFPLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxZQUFZLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxtQkFBbUIsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsSUFBSSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLElBQUUsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLG1CQUFtQixFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxZQUFZLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFlBQVksR0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBRyxNQUFJLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFHLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxNQUFJLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLGtCQUFrQixJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsWUFBVTtNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUU7UUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsRUFBQyxPQUFNLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFBO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxVQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsQ0FBQyxZQUFZLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUc7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUU7UUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLEVBQUMsT0FBTSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQTtNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtNQUFDLEtBQUksSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxFQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEtBQUcsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLE1BQU0sRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLFlBQVksRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxFQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sSUFBSSxDQUFDLGNBQWM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxLQUFHLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLG1CQUFtQjtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVO0lBQUEsQ0FBQyxFQUFDLENBQUM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDOzs7OztBQ1h4clQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUMsVUFBUyxDQUFDLEVBQUM7RUFBQyxZQUFZOztFQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsSUFBRSxDQUFDO0VBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLENBQUM7TUFBQyxDQUFDLEdBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsUUFBUTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQztRQUFDLE9BQU8sVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLEVBQUU7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUUsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7VUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxNQUFNLElBQUUsTUFBTSxDQUFDLEdBQUcsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLEdBQUcsR0FBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxFQUFDLFlBQVU7WUFBQyxPQUFPLENBQUM7VUFBQSxDQUFDLENBQUMsR0FBQyxXQUFXLElBQUUsT0FBTyxNQUFNLElBQUUsTUFBTSxDQUFDLE9BQU8sS0FBRyxNQUFNLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBRSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLFlBQVU7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsMEJBQTBCLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFBLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsT0FBTyxDQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU07UUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsUUFBUSxFQUFDLE1BQU0sRUFBQyxPQUFPLEVBQUMsT0FBTyxFQUFDLGNBQWMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsV0FBVyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxXQUFXLENBQUM7SUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLFNBQVM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsd0JBQXdCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxJQUFFLElBQUk7SUFBQSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxFQUFFLEVBQUMsQ0FBQztRQUFDLEVBQUUsRUFBQztNQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUM7TUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0I7TUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxZQUFVO1FBQUMsT0FBTyxJQUFJLElBQUksQ0FBRCxDQUFDLENBQUUsT0FBTyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFJLEVBQUMsS0FBSyxFQUFDLFFBQVEsRUFBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyx1QkFBdUIsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLHNCQUFzQixDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyw2QkFBNkIsQ0FBQztJQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxHQUFDLEdBQUc7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLEtBQUssRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxhQUFhLENBQUMsTUFBTSxDQUFDO1FBQUEsQ0FBQztNQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7UUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO1FBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO1FBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsSUFBRSxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLFlBQVU7UUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLElBQUksQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBQyxNQUFNLENBQUMsZUFBZSxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxNQUFNO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsZUFBZSxLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBRCxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVO01BQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztJQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFlBQVU7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7TUFBQyxPQUFNLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxRQUFRLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxJQUFHLENBQUMsS0FBRyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsSUFBSSxDQUFDLE1BQU07SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxLQUFHLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sSUFBSSxDQUFDLFVBQVU7TUFBQyxJQUFHLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsRUFBQztVQUFDLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLGNBQWM7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLE9BQUssQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztRQUFBO1FBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsTUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLGFBQWEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsVUFBVTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsVUFBVTtNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLEtBQUcsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsU0FBUztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxpQkFBaUIsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLHFCQUFxQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxrQkFBa0IsR0FBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsTUFBTSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxhQUFhLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNO01BQUMsS0FBSSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxZQUFVO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVU7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxJQUFJLElBQUUsQ0FBQyxFQUFDLE1BQUssNkJBQTZCO1FBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLElBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUksSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFdBQVcsR0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUksQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztRQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxlQUFlLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsS0FBRyxDQUFDLENBQUMsTUFBSSxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxPQUFPLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsdUJBQXVCLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxjQUFjLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLGdCQUFnQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQyxDQUFDO1FBQUMsWUFBWSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsY0FBYyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsYUFBYSxFQUFDLENBQUM7UUFBQyxZQUFZLEVBQUMsQ0FBQztRQUFDLGlCQUFpQixFQUFDLENBQUM7UUFBQyx1QkFBdUIsRUFBQyxDQUFDO1FBQUMsc0JBQXNCLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsY0FBYyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLEdBQUcsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxHQUFHLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUM7VUFBQyxPQUFLLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUM7UUFBQTtNQUFDO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVU7UUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUM7VUFBQyxNQUFNLEVBQUMsQ0FBQztVQUFDLE1BQU0sRUFBQztRQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBSyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU07TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFHLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUM7VUFBTSxPQUFPLENBQUM7UUFBQTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUU7VUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsT0FBTSxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQjtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7TUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLEVBQUM7UUFBQyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQztNQUFNLENBQUMsTUFBSyxJQUFHLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxLQUFHLENBQUMsRUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxhQUFhLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssRUFBQztRQUFNLENBQUMsTUFBSyxJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxZQUFZLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxZQUFZLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLElBQUUsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsUUFBUSxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLFdBQVcsRUFBQyxJQUFJLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxjQUFjLENBQUMsaUJBQWlCLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsVUFBVSxJQUFFLE9BQU8sSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU0sQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUksSUFBSSxDQUFDLElBQUksRUFBQztRQUFDLElBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUQsQ0FBQyxFQUFFLFlBQVksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUM7WUFBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFFBQVE7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxVQUFVO1lBQUMsQ0FBQyxFQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLEVBQUUsRUFBQyxDQUFDLENBQUM7WUFBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDO1VBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO1VBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxlQUFlLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxTQUFTLE1BQUksSUFBSSxDQUFDLHVCQUF1QixHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxNQUFLLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQztVQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQztRQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVk7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsWUFBWSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxJQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxtQkFBbUIsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxJQUFJLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxtQkFBbUIsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLE1BQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsTUFBSyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDO1VBQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLEdBQUcsRUFBQztVQUFPLElBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQztVQUFDLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsS0FBSSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxrQkFBa0IsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsS0FBSyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUk7UUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxpQkFBaUIsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLEtBQUs7WUFBQztVQUFLO1FBQUMsQ0FBQyxNQUFJO1VBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sRUFBQyxPQUFNLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsSUFBRSxDQUFDLENBQUMsR0FBQyxLQUFLO1FBQUE7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDO1VBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7TUFBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyx1QkFBdUIsSUFBRSxDQUFDLENBQUMsY0FBYyxDQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsdUJBQXVCLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtRQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLHVCQUF1QixJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLEdBQUMsV0FBVyxHQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLGdCQUFnQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLGlCQUFpQixFQUFDLENBQUM7UUFBQyx1QkFBdUIsRUFBQyxDQUFDO1FBQUMsc0JBQXNCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDLENBQUM7UUFBQyxTQUFTLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQztNQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTSxFQUFFO01BQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsTUFBSyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLGtCQUFrQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLGVBQWUsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQztRQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsS0FBSyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxlQUFlO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxHQUFDLEVBQUUsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFLLENBQUMsR0FBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFDLElBQUcsaUJBQWlCLEtBQUcsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBSyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEVBQUUsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUQsQ0FBQyxDQUFFLFNBQVMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsR0FBRyxDQUFDLEVBQUMsTUFBSyw0QkFBNEI7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjO1FBQUMsQ0FBQyxHQUFDO1VBQUMsSUFBSSxFQUFDLGNBQWM7VUFBQyxHQUFHLEVBQUMsVUFBVTtVQUFDLElBQUksRUFBQyxPQUFPO1VBQUMsS0FBSyxFQUFDLGFBQWE7VUFBQyxPQUFPLEVBQUM7UUFBaUIsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsRUFBQyxZQUFVO1VBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLEVBQUU7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUc7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDO01BQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMscURBQXFELEdBQUMsQ0FBQyxDQUFDO0lBQUE7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0VBQUE7QUFBQyxDQUFDLEVBQUUsTUFBTSxDQUFDOzs7OztBQ1gxNHZCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxhQUFhLEVBQUMsQ0FBQyxhQUFhLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsSUFBRSxNQUFNO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsU0FBUztNQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsWUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsWUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxDQUFELENBQUM7VUFBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztVQUFDLFNBQVMsRUFBQyxJQUFJLENBQUMsQ0FBRDtRQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDO1lBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFHO1VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU0sQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxFQUFFO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0IsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGtCQUFrQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsTUFBTSxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsWUFBWSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDO01BQUMsS0FBSSxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtNQUFBLENBQUMsTUFBSyxPQUFLLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU87SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLElBQUUsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxFQUFFO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxFQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxFQUFFLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sQ0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLG1CQUFtQixFQUFDO01BQUMsSUFBSSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxRQUFRLEVBQUMsT0FBTyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxXQUFXLEVBQUMsT0FBTyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxhQUFhLEVBQUMsT0FBTyxDQUFDLEVBQUMsQ0FBQztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7Ozs7O0FDWHRsSjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsbUJBQW1CLEVBQUMsQ0FBQyxxQkFBcUIsRUFBQyxXQUFXLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVU7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLFFBQVE7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLEtBQUssQ0FBQztJQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQywyQkFBMkIsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxhQUFhLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDO01BQUMsR0FBRyxFQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQztNQUFDLE1BQU0sRUFBQyxDQUFDO01BQUMsSUFBSSxFQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQztNQUFDLE1BQU0sRUFBQyxDQUFDO01BQUMsUUFBUSxFQUFDLENBQUM7TUFBQyxPQUFPLEVBQUMsQ0FBQztNQUFDLE1BQU0sRUFBQyxDQUFDO01BQUMsV0FBVyxFQUFDLENBQUM7TUFBQyxVQUFVLEVBQUM7SUFBRSxDQUFDO0lBQUMsSUFBSSxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsMkJBQTJCO01BQUMsQ0FBQyxHQUFDLHNEQUFzRDtNQUFDLENBQUMsR0FBQyxrREFBa0Q7TUFBQyxDQUFDLEdBQUMsWUFBWTtNQUFDLENBQUMsR0FBQyx1QkFBdUI7TUFBQyxDQUFDLEdBQUMsc0JBQXNCO01BQUMsQ0FBQyxHQUFDLGtCQUFrQjtNQUFDLENBQUMsR0FBQyx5QkFBeUI7TUFBQyxDQUFDLEdBQUMsWUFBWTtNQUFDLENBQUMsR0FBQyxVQUFVO01BQUMsQ0FBQyxHQUFDLFlBQVk7TUFBQyxDQUFDLEdBQUMsd0NBQXdDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLHVCQUF1QjtNQUFDLENBQUMsR0FBQyxnQ0FBZ0M7TUFBQyxDQUFDLEdBQUMscURBQXFEO01BQUMsQ0FBQyxHQUFDLHVCQUF1QjtNQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLEdBQUc7TUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxRQUFRO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsS0FBSyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsS0FBSyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUM7UUFBQyxhQUFhLEVBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQVMsQ0FBQyxTQUFTO01BQUMsQ0FBQyxHQUFDLFlBQVU7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxLQUFLLENBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQyxFQUFDLDZCQUE2QixDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsdUNBQXVDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0IsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxNQUFNLEtBQUcsRUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE1BQU0sQ0FBQyxPQUFPLElBQUUsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFHLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFHLEVBQUMsS0FBSyxFQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFO1FBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsZ0JBQWdCLEdBQUMsWUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxTQUFTLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUcsTUFBTSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxLQUFJO1VBQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxHQUFDLDhCQUE4QixHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLEdBQUMsaUJBQWlCLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsaUJBQWlCLEdBQUMsZ0JBQWdCLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUk7WUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxHQUFHO1lBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQTtVQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGFBQWEsR0FBQyxjQUFjLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsVUFBVSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsTUFBTSxHQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLE9BQUssRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBSyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLENBQUMsQ0FBQyxLQUFLLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxPQUFPLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsU0FBUyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLEtBQUssQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLEdBQUMsRUFBRSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFdBQVcsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU07VUFBQyxJQUFJLEVBQUMsQ0FBQztVQUFDLFFBQVEsRUFBQztRQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEtBQUssRUFBQyxRQUFRO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLFlBQVksRUFBQyxhQUFhLEVBQUMsV0FBVyxFQUFDLGNBQWMsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsT0FBTyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxLQUFLLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxRQUFRLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLElBQUUsS0FBSyxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFNLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJO1FBQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsR0FBRyxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxLQUFLO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxxREFBcUQ7SUFBQyxLQUFJLENBQUMsSUFBSSxFQUFFLEVBQUMsRUFBRSxJQUFFLEdBQUcsR0FBQyxDQUFDLEdBQUMsS0FBSztJQUFDLEVBQUUsR0FBQyxNQUFNLENBQUMsRUFBRSxHQUFDLEdBQUcsRUFBQyxJQUFJLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU8sVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsRUFBRTtRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztVQUFBO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxFQUFDLE9BQUssQ0FBQyxHQUFDLEVBQUUsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDO1FBQUEsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztVQUFBO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxFQUFDLE9BQUssQ0FBQyxHQUFDLEVBQUUsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFBLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsZUFBZSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBRyxDQUFDLENBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxVQUFVLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxFQUFDO1lBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksRUFBQztjQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7WUFBQTtVQUFDLENBQUMsTUFBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQTtNQUFDLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUM7TUFBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVU7VUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBO1FBQUMsT0FBTTtVQUFDLEtBQUssRUFBQyxDQUFDO1VBQUMsR0FBRyxFQUFDLENBQUM7VUFBQyxRQUFRLEVBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQztRQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxZQUFZLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDO01BQUEsQ0FBQyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEVBQUU7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsY0FBYyxHQUFDLGFBQWEsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsTUFBSyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxFQUFFLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFFLEdBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsRUFBRSxDQUFDLEdBQUMsRUFBRTtJQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDO01BQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLDJCQUEyQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDO1VBQUMsTUFBTSxFQUFDO1FBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVE7VUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDO1lBQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7Y0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsSUFBRSxNQUFNLEVBQUUsR0FBRyxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO2NBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxzQkFBc0IsQ0FBQyxFQUFDLENBQUMsQ0FBQztZQUFBO1VBQUMsQ0FBQyxDQUFDO1FBQUE7TUFBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU87TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztNQUFBO01BQUMsT0FBTyxFQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsRUFBRSxDQUFDLENBQUMsRUFBQztRQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztRQUFBLENBQUM7UUFBQyxRQUFRLEVBQUM7TUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsaUZBQWlGLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxXQUFXO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxpQkFBaUIsQ0FBQztNQUFDLEVBQUUsR0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLGFBQWEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFlBQVU7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLFlBQVk7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsSUFBSSxFQUFFLENBQUQsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFELENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxHQUFDLENBQUM7UUFBQyxLQUFJLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLHlCQUF5QixDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztVQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUM7WUFBQyxJQUFJLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7Y0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQztZQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQTtRQUFDLENBQUMsTUFBSyxJQUFHLEVBQUUsRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxDQUFDLElBQUUsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEVBQUUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxFQUFFLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLEdBQUcsRUFBQyxDQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQywyQkFBMkIsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZO1FBQUMsSUFBRyxDQUFDLEVBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxFQUFFO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFdBQVc7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZO1lBQUMsQ0FBQyxHQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsUUFBUTtZQUFDLENBQUMsR0FBQywrQ0FBK0MsR0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLElBQUUsK0JBQStCLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLG9DQUFvQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsV0FBVyxJQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxJQUFJLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7WUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsRUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLEVBQUUsSUFBRSxDQUFDLEtBQUcsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUk7VUFBQTtRQUFDO01BQUMsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVc7UUFBQyxJQUFHLEVBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUM7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSTtVQUFDLElBQUcsRUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsRUFBRSxDQUFDLEVBQUMsS0FBSyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsS0FBSyxDQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsRUFBRSxDQUFDLG1QQUFtUCxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsU0FBUyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEdBQUM7WUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQztZQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsV0FBVyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsb0JBQW9CLEVBQUMsQ0FBQyxDQUFDLFdBQVc7VUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsRUFBQyxJQUFJLElBQUUsQ0FBQyxFQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsVUFBVSxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLGVBQWUsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxRQUFRLEdBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsRUFBRSxDQUFDLFdBQVcsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxnQkFBZ0IsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxFQUFFLENBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLGdCQUFnQixJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLE1BQUksQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUM7UUFBQTtRQUFDLEtBQUksRUFBRSxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxNQUFJLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxpQkFBaUIsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssQ0FBQyxHQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsY0FBYyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxXQUFXLEVBQUM7TUFBQyxZQUFZLEVBQUMsc0JBQXNCO01BQUMsTUFBTSxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxFQUFDO0lBQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGNBQWMsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMscUJBQXFCLEVBQUMsc0JBQXNCLEVBQUMseUJBQXlCLEVBQUMsd0JBQXdCLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxLQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsV0FBVyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUMsQ0FBQztNQUFDLFNBQVMsRUFBQyxFQUFFLENBQUMsaUJBQWlCLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG9CQUFvQixFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFD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QUMsRUFBQyxFQUFFLENBQUMsWUFBWSxFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsUUFBUSxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQywrQ0FBK0M7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsU0FBUyxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxtREFBbUQ7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsTUFBTSxFQUFDO01BQUMsWUFBWSxFQUFDLHVCQUF1QjtNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFlBQVksRUFBQztNQUFDLFlBQVksRUFBQyxrQkFBa0I7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsdUJBQXVCLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUM7TUFBQyxZQUFZLEVBQUMsZ0JBQWdCO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxnQkFBZ0IsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGdCQUFnQixFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsU0FBUyxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsT0FBTyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGFBQWEsRUFBQztNQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsbUVBQW1FO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLDJCQUEyQixFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsVUFBVSxJQUFHLENBQUMsR0FBQyxVQUFVLEdBQUMsWUFBWTtRQUFDLE9BQU8sSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxRQUFRLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsR0FBRyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsZ0JBQWdCLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLGlCQUFpQixHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxFQUFFLENBQUMseUJBQXlCLEVBQUM7TUFBQyxZQUFZLEVBQUMsR0FBRztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLFdBQVcsS0FBRyxDQUFDO1FBQUMsT0FBTSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsZ0JBQWdCLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxTQUFTLEdBQUMsUUFBUSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsUUFBUSxHQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxjQUFjLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksS0FBRyxJQUFJLENBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUM7UUFBQSxDQUFDLE1BQUssSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLEtBQUcsSUFBSSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxFQUFFLENBQUMsV0FBVyxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLElBQUUsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU87UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEVBQUMsRUFBRSxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxhQUFhLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSztRQUFDLElBQUcsS0FBSyxLQUFHLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxpQkFBaUIsS0FBRyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsS0FBRyxFQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUM7TUFBQTtJQUFDLENBQUM7SUFBQyxLQUFJLEVBQUUsQ0FBQyxZQUFZLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsMENBQTBDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsRUFBRSxHQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxPQUFNLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsZUFBZTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxjQUFjLEVBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLDBCQUEwQixFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsd0JBQXdCLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFdBQVcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE9BQU8sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLGFBQWEsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFFLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLE1BQUksT0FBTyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxJQUFFLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxXQUFXLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsS0FBSyxJQUFFLENBQUMsR0FBQyxFQUFFLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsSUFBSTtNQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLO1FBQUMsSUFBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsWUFBWSxLQUFHLENBQUMsSUFBSSxFQUFDLE9BQUssQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSTtZQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJO2NBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUk7Z0JBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO2dCQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7Y0FBQTtZQUFDLE9BQUksQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7VUFBQyxPQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBLENBQUMsTUFBSyxPQUFLLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBSyxPQUFLLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLEVBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQUEsRUFBVTtNQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUssRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxNQUFNLElBQUUsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLGFBQWE7TUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7UUFDbHgrQixLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDOzs7OztBQ1poSTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLElBQUksQ0FBQyxHQUFDLFFBQVEsQ0FBQyxlQUFlO0lBQUMsQ0FBQyxHQUFDLE1BQU07SUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxHQUFDLE9BQU8sR0FBQyxRQUFRO1FBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFJO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQztNQUFDLFFBQVEsRUFBQyxVQUFVO01BQUMsR0FBRyxFQUFDLENBQUM7TUFBQyxPQUFPLEVBQUMsT0FBTztNQUFDLElBQUksRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDO1VBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEdBQUcsRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7RUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7SUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVTtFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7SUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztFQUFBLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIi5jdXN0b20tc2VsZWN0XCIpLmZvckVhY2goY3VzdG9tU2VsZWN0ID0+IHtcblx0Y29uc3Qgc2VsZWN0QnRuID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3IoXCIuc2VsZWN0LWJ1dHRvblwiKTtcblx0Y29uc3Qgc2VsZWN0ZWRWYWx1ZSA9IGN1c3RvbVNlbGVjdC5xdWVyeVNlbGVjdG9yKFwiLnNlbGVjdGVkLXZhbHVlXCIpO1xuXHRjb25zdCBoYW5kbGVyID0gZnVuY3Rpb24oZWxtKSB7XG5cdFx0Y29uc3QgY3VzdG9tQ2hhbmdlRXZlbnQgPSBuZXcgQ3VzdG9tRXZlbnQoJ2N1c3RvbS1zZWxlY3QtY2hhbmdlJywge1xuXHRcdFx0ZGV0YWlsOiB7XG5cdFx0XHRcdHNlbGVjdGVkT3B0aW9uOiBlbG1cblx0XHRcdH1cblx0XHR9KTtcblx0XHRzZWxlY3RlZFZhbHVlLnRleHRDb250ZW50ID0gZWxtLnRleHRDb250ZW50O1xuXHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QucmVtb3ZlKFwiYWN0aXZlXCIpO1xuXHRcdGN1c3RvbVNlbGVjdC5kaXNwYXRjaEV2ZW50KGN1c3RvbUNoYW5nZUV2ZW50KTtcblxuXHR9XG5cdHNlbGVjdEJ0bi5hZGRFdmVudExpc3RlbmVyKFwiY2xpY2tcIiwgKCkgPT4ge1xuXHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QudG9nZ2xlKFwiYWN0aXZlXCIpO1xuXHRcdHNlbGVjdEJ0bi5zZXRBdHRyaWJ1dGUoXG5cdFx0XHRcImFyaWEtZXhwYW5kZWRcIixcblx0XHRcdHNlbGVjdEJ0bi5nZXRBdHRyaWJ1dGUoXCJhcmlhLWV4cGFuZGVkXCIpID09PSBcInRydWVcIiA/IFwiZmFsc2VcIiA6IFwidHJ1ZVwiXG5cdFx0KTtcblx0fSk7XG5cblx0Y3VzdG9tU2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGlmIChlLnRhcmdldC5tYXRjaGVzKCdsYWJlbCcpKSB7XG5cblx0XHRcdGNvbnN0IGFsbEl0ZW1zID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3JBbGwoJ2xpJyk7XG5cdFx0XHRhbGxJdGVtcy5mb3JFYWNoKGl0ZW0gPT4gaXRlbS5jbGFzc0xpc3QucmVtb3ZlKCdhY3RpdmUnKSk7XG5cdFx0XHRjb25zdCBjbGlja2VkUGxhbiA9IGUudGFyZ2V0LmNsb3Nlc3QoJ2xpJyk7XG5cblx0XHRcdGlmIChjbGlja2VkUGxhbikge1xuXHRcdFx0XHRjbGlja2VkUGxhbi5jbGFzc0xpc3QuYWRkKCdhY3RpdmUnKTtcblx0XHRcdFx0aGFuZGxlcihjbGlja2VkUGxhbik7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImNsaWNrXCIsIChlKSA9PiB7XG5cdFx0aWYgKCFjdXN0b21TZWxlY3QuY29udGFpbnMoZS50YXJnZXQpKSB7XG5cdFx0XHRjdXN0b21TZWxlY3QuY2xhc3NMaXN0LnJlbW92ZShcImFjdGl2ZVwiKTtcblx0XHRcdHNlbGVjdEJ0bi5zZXRBdHRyaWJ1dGUoXCJhcmlhLWV4cGFuZGVkXCIsIFwiZmFsc2VcIik7XG5cdFx0fVxuXHR9KTtcbn0pOyIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcbiAgICAvKipcbiAgICAgKiBSZWZyZXNoIExpY2Vuc2UgZGF0YVxuICAgICAqL1xuICAgIHZhciBfaXNSZWZyZXNoaW5nID0gZmFsc2U7XG4gICAgJCgnI3dwci1hY3Rpb24tcmVmcmVzaF9hY2NvdW50Jykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBpZighX2lzUmVmcmVzaGluZyl7XG4gICAgICAgICAgICB2YXIgYnV0dG9uID0gJCh0aGlzKTtcbiAgICAgICAgICAgIHZhciBhY2NvdW50ID0gJCgnI3dwci1hY2NvdW50LWRhdGEnKTtcbiAgICAgICAgICAgIHZhciBleHBpcmUgPSAkKCcjd3ByLWV4cGlyYXRpb24tZGF0YScpO1xuXG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICBfaXNSZWZyZXNoaW5nID0gdHJ1ZTtcbiAgICAgICAgICAgIGJ1dHRvbi50cmlnZ2VyKCAnYmx1cicgKTtcblxuXHRcdFx0Ly8gU3RhcnQgcG9sbGluZyBpZiBub3QgYWxyZWFkeSBydW5uaW5nLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICBleHBpcmUucmVtb3ZlQ2xhc3MoJ3dwci1pc1ZhbGlkIHdwci1pc0ludmFsaWQnKTtcblxuICAgICAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfcmVmcmVzaF9jdXN0b21lcl9kYXRhJyxcbiAgICAgICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2UsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBmdW5jdGlvbihyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICBidXR0b24ucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uLmFkZENsYXNzKCd3cHItaXNIaWRkZW4nKTtcblxuICAgICAgICAgICAgICAgICAgICBpZiAoIHRydWUgPT09IHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBhY2NvdW50Lmh0bWwocmVzcG9uc2UuZGF0YS5saWNlbnNlX3R5cGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgZXhwaXJlLmFkZENsYXNzKHJlc3BvbnNlLmRhdGEubGljZW5zZV9jbGFzcykuaHRtbChyZXNwb25zZS5kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbik7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWljb24tcmVmcmVzaCB3cHItaXNIaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pY29uLWNoZWNrJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LCAyNTApO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIGVsc2V7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWljb24tcmVmcmVzaCB3cHItaXNIaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pY29uLWNsb3NlJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LCAyNTApO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKHtvbkNvbXBsZXRlOmZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgX2lzUmVmcmVzaGluZyA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jys9d3ByLWlzSGlkZGVuJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pY29uLWNoZWNrJ319LCAwLjI1KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pY29uLWNsb3NlJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOicrPXdwci1pY29uLXJlZnJlc2gnfX0sIDAuMjUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWlzSGlkZGVuJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgO1xuICAgICAgICAgICAgICAgICAgICB9LCAyMDAwKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICApO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9KTtcblxuICAgIC8qKlxuICAgICAqIFNhdmUgVG9nZ2xlIG9wdGlvbiB2YWx1ZXMgb24gY2hhbmdlXG4gICAgICovXG4gICAgJCgnLndwci1yYWRpbyBpbnB1dFt0eXBlPWNoZWNrYm94XScpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgdmFyIG5hbWUgID0gJCh0aGlzKS5hdHRyKCdpZCcpO1xuICAgICAgICB2YXIgdmFsdWUgPSAkKHRoaXMpLnByb3AoJ2NoZWNrZWQnKSA/IDEgOiAwO1xuXG5cdFx0dmFyIGV4Y2x1ZGVkID0gWyAnY2xvdWRmbGFyZV9hdXRvX3NldHRpbmdzJywgJ2Nsb3VkZmxhcmVfZGV2bW9kZScsICdhbmFseXRpY3NfZW5hYmxlZCcgXTtcblx0XHRpZiAoIGV4Y2x1ZGVkLmluZGV4T2YoIG5hbWUgKSA+PSAwICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF90b2dnbGVfb3B0aW9uJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcbiAgICAgICAgICAgICAgICBvcHRpb246IHtcbiAgICAgICAgICAgICAgICAgICAgbmFtZTogbmFtZSxcbiAgICAgICAgICAgICAgICAgICAgdmFsdWU6IHZhbHVlXG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGZ1bmN0aW9uKHJlc3BvbnNlKSB7fVxuICAgICAgICApO1xuXHR9KTtcblxuXHQvKipcbiAgICAgKiBTYXZlIGVuYWJsZSBDUENTUyBmb3IgbW9iaWxlcyBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBDUENTUyBidG4gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLWhpZGUtb24tY2xpY2snKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1zaG93LW9uLWNsaWNrJykuc2hvdygpO1xuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogU2F2ZSBlbmFibGUgR29vZ2xlIEZvbnRzIE9wdGltaXphdGlvbiBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBDUENTUyBidG4gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLWhpZGUtb24tY2xpY2snKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1zaG93LW9uLWNsaWNrJykuc2hvdygpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICAgICAgICAgICQoJyNtaW5pZnlfZ29vZ2xlX2ZvbnRzJykudmFsKDEpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG5cbiAgICAkKCAnI3JvY2tldC1kaXNtaXNzLXByb21vdGlvbicgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9kaXNtaXNzX3Byb21vJyxcbiAgICAgICAgICAgICAgICBub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQkKCcjcm9ja2V0LXByb21vLWJhbm5lcicpLmhpZGUoICdzbG93JyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSApO1xuXG4gICAgJCggJyNyb2NrZXQtZGlzbWlzcy1yZW5ld2FsJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2Rpc21pc3NfcmVuZXdhbCcsXG4gICAgICAgICAgICAgICAgbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0JCgnI3JvY2tldC1yZW5ld2FsLWJhbm5lcicpLmhpZGUoICdzbG93JyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSApO1xuXHQkKCAnI3dwci11cGRhdGUtZXhjbHVzaW9uLWxpc3QnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKCcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbXNnJykuaHRtbCgnJyk7XG5cdFx0JC5hamF4KHtcblx0XHRcdHVybDogcm9ja2V0X2FqYXhfZGF0YS5yZXN0X3VybCxcblx0XHRcdGJlZm9yZVNlbmQ6IGZ1bmN0aW9uICggeGhyICkge1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ1gtV1AtTm9uY2UnLCByb2NrZXRfYWpheF9kYXRhLnJlc3Rfbm9uY2UgKTtcblx0XHRcdFx0eGhyLnNldFJlcXVlc3RIZWFkZXIoICdBY2NlcHQnLCAnYXBwbGljYXRpb24vanNvbiwgKi8qO3E9MC4xJyApO1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ0NvbnRlbnQtVHlwZScsICdhcHBsaWNhdGlvbi9qc29uJyApO1xuXHRcdFx0fSxcblx0XHRcdG1ldGhvZDogXCJQVVRcIixcblx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uKHJlc3BvbnNlcykge1xuXHRcdFx0XHRsZXQgZXhjbHVzaW9uX21zZ19jb250YWluZXIgPSAkKCcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbXNnJyk7XG5cdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmh0bWwoJycpO1xuXHRcdFx0XHRpZiAoIHVuZGVmaW5lZCAhPT0gcmVzcG9uc2VzWydzdWNjZXNzJ10gKSB7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCAnPGRpdiBjbGFzcz1cIm5vdGljZSBub3RpY2UtZXJyb3JcIj4nICsgcmVzcG9uc2VzWydtZXNzYWdlJ10gKyAnPC9kaXY+JyApO1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXHRcdFx0XHRPYmplY3Qua2V5cyggcmVzcG9uc2VzICkuZm9yRWFjaCgoIHJlc3BvbnNlX2tleSApID0+IHtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8c3Ryb25nPicgKyByZXNwb25zZV9rZXkgKyAnOiA8L3N0cm9uZz4nICk7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCByZXNwb25zZXNbcmVzcG9uc2Vfa2V5XVsnbWVzc2FnZSddICk7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCAnPGJyPicgKTtcblx0XHRcdFx0fSk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH0gKTtcblxuICAgIC8qKlxuICAgICAqIEVuYWJsZSBtb2JpbGUgY2FjaGUgb3B0aW9uLlxuICAgICAqL1xuICAgICQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfbW9iaWxlX2NhY2hlJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBjYWNoZSBlbmFibGUgYnV0dG9uIG9uIHN1Y2Nlc3MuXG5cdFx0XHRcdFx0JCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJyN3cHJfbW9iaWxlX2NhY2hlX2RlZmF1bHQnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnI3dwcl9tb2JpbGVfY2FjaGVfcmVzcG9uc2UnKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gU2V0IHZhbHVlcyBvZiBtb2JpbGUgY2FjaGUgYW5kIHNlcGFyYXRlIGNhY2hlIGZpbGVzIGZvciBtb2JpbGVzIG9wdGlvbiB0byAxLlxuICAgICAgICAgICAgICAgICAgICAkKCcjY2FjaGVfbW9iaWxlJykudmFsKDEpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjZG9fY2FjaGluZ19tb2JpbGVfZmlsZXMnKS52YWwoMSk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9KTtcbn0pO1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgZnVuY3Rpb24oKSB7XG5cdGNvbnN0IGFuYWx5dGljc0NoZWNrYm94ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2FuYWx5dGljc19lbmFibGVkJyk7XG5cblx0aWYgKGFuYWx5dGljc0NoZWNrYm94KSB7XG5cdFx0YW5hbHl0aWNzQ2hlY2tib3guYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRjb25zdCBpc0NoZWNrZWQgPSB0aGlzLmNoZWNrZWQ7XG5cblx0XHRcdC8vIFVwZGF0ZSB0aGUgZ2xvYmFsIG1peHBhbmVsIGRhdGEgb3B0aW4gc3RhdGUgaW1tZWRpYXRlbHlcblx0XHRcdGlmICh0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgIT09ICd1bmRlZmluZWQnKSB7XG5cdFx0XHRcdHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgPSBpc0NoZWNrZWQgPyAnMScgOiAnMCc7XG5cdFx0XHR9XG5cblx0XHRcdGZldGNoKGFqYXh1cmwsIHtcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGhlYWRlcnM6IHtcblx0XHRcdFx0XHQnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCcsXG5cdFx0XHRcdH0sXG5cdFx0XHRcdGJvZHk6IG5ldyBVUkxTZWFyY2hQYXJhbXMoe1xuXHRcdFx0XHRcdGFjdGlvbjogJ3JvY2tldF90b2dnbGVfb3B0aW4nLFxuXHRcdFx0XHRcdHZhbHVlOiBpc0NoZWNrZWQgPyAxIDogMCxcblx0XHRcdFx0XHRfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcblx0XHRcdFx0fSlcblx0XHRcdH0pO1xuXHRcdH0pO1xuXHR9XG59KTtcblxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGZ1bmN0aW9uKCkge1xuXHQvKipcblx0ICogUGVyZm9ybWFuY2UgTW9uaXRvcmluZyB3aXRoIFByb2dyZXNzaXZlIFBvbGxpbmcuXG5cdCAqL1xuXG5cdFx0Ly8gPT09PSBDb25maWd1cmF0aW9uID09PT1cblx0Y29uc3QgUE9MTF9CQVNFX0lOVEVSVkFMID0gMjAwMDsgICAvLyBTdGFydCBwb2xsaW5nIGF0IDIgc2Vjb25kc1xuXHRjb25zdCBQT0xMX01BWF9JTlRFUlZBTCA9IDUwMDA7ICAgLy8gTWF4IHBvbGxpbmcgaW50ZXJ2YWwgKDUgc2Vjb25kcylcblxuXHQvLyA9PT09IFN0YXRlID09PT1cblx0bGV0IHJvY2tldEluc2lnaHRzSWRzID0gQXJyYXkuaXNBcnJheSh3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ucm9ja2V0X2luc2lnaHRzX2lkcykgPyB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YS5yb2NrZXRfaW5zaWdodHNfaWRzLnNsaWNlKCkgOiBbXTtcblx0bGV0IHBvbGxJbnRlcnZhbCA9IFBPTExfQkFTRV9JTlRFUlZBTDtcblx0bGV0IHBvbGxUaW1lciA9IG51bGw7XG5cdGxldCBoYXNDcmVkaXQgPSB0cnVlOyAvLyBUcmFjayBjcmVkaXQgc3RhdHVzXG4gICAgbGV0IGdsb2JhbFNjb3JlRGF0YSA9IHtcbiAgICAgICAgZGF0YToge1xuICAgICAgICAgICAgc3RhdHVzOiAnJyxcbiAgICAgICAgICAgIHNjb3JlOiAwLFxuICAgICAgICAgICAgcGFnZXNfbnVtOiAwXG4gICAgICAgIH0sXG4gICAgICAgIGh0bWw6ICcnLFxuICAgICAgICByb3dfaHRtbDogJycsXG5cdFx0ZGlzYWJsZWRfYnRuX2h0bWw6IHtcblx0XHRcdGdsb2JhbF9zY29yZV93aWRnZXQ6ICcnLFxuXHRcdFx0cm9ja2V0X2luc2lnaHRzOiAnJ1xuXHRcdH1cbiAgICB9O1xuXG4gICAgLy8gSW5pdGlhbGl6ZSBnbG9iYWxTY29yZURhdGEgZnJvbSBsb2NhbGl6ZWQgc2NyaXB0IGRhdGEgaWYgYXZhaWxhYmxlXG4gICAgaWYgKHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5nbG9iYWxfc2NvcmVfZGF0YSkge1xuICAgICAgICBnbG9iYWxTY29yZURhdGEgPSB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YS5nbG9iYWxfc2NvcmVfZGF0YTtcbiAgICB9XG5cblx0Ly8gPT09PSBET00gU2VsZWN0b3JzID09PT1cblx0Y29uc3QgJHBhZ2VVcmxJbnB1dCA9ICQoJyN3cHItc3BlZWQtcmFkYXItdXJsLWlucHV0Jyk7XG5cdGNvbnN0ICR0YWJsZUJvZHkgPSAkKCcud3ByLXJpLXVybHMtdGFibGUgdGJvZHknKTtcblx0Y29uc3QgJHRhYmxlID0gJCgnLndwci1yaS11cmxzLXRhYmxlJyk7XG5cblx0Ly8gPT09PSBVdGlsaXR5IEZ1bmN0aW9ucyA9PT09XG5cdGZ1bmN0aW9uIGlzVmFsaWRVcmwoaW5wdXQpIHtcblx0XHR0cnkge1xuXHRcdFx0Y29uc3QgdXJsID0gbmV3IFVSTChpbnB1dCk7XG5cdFx0XHRyZXR1cm4gdXJsLmhvc3RuYW1lLmluY2x1ZGVzKCcuJykgJiYgdXJsLmhvc3RuYW1lLnNwbGl0KCcuJykucG9wKCkubGVuZ3RoID4gMDtcblx0XHR9IGNhdGNoIHtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiBhZGRJZHMobmV3SWQpIHtcblx0XHRpZiAoIXJvY2tldEluc2lnaHRzSWRzLmluY2x1ZGVzKG5ld0lkKSkge1xuXHRcdFx0cm9ja2V0SW5zaWdodHNJZHMucHVzaChuZXdJZCk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gaGFzSWQoaWQpIHtcblx0XHRyZXR1cm4gcm9ja2V0SW5zaWdodHNJZHMuaW5jbHVkZXMoaWQpO1xuXHR9XG5cblx0ZnVuY3Rpb24gcmVtb3ZlSWQoaWQpIHtcblx0XHQvLyBFbnN1cmUgdGhhdCB0aGUgaWQgdG8gYmUgcmVtb3ZlZCBpcyBhbiBpbnRlZ2VyIGZvciBhY2N1cmF0ZSBjb21wYXJpc29uLlxuXHRcdGNvbnN0IGlkVG9SZW1vdmUgPSBwYXJzZUludChpZCwgMTApO1xuXHRcdHJvY2tldEluc2lnaHRzSWRzID0gcm9ja2V0SW5zaWdodHNJZHMuZmlsdGVyKHggPT4gcGFyc2VJbnQoeCwgMTApICE9PSBpZFRvUmVtb3ZlKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZVF1b3RhQmFubmVyKGNhbkFkZFBhZ2VzKSB7XG5cdFx0Y29uc3QgJHN1bW1hcnlJbmZvICAgID0gJCgnLndwci1yaS1zdW1tYXJ5LWluZm8nKTtcblx0XHRjb25zdCBpc0ZyZWUgID0gd2luZG93LnJvY2tldF9hamF4X2RhdGE/LmlzX2ZyZWUgPT09ICcxJztcblx0XHRjb25zdCAkcXVvdGFCYW5uZXIgPSBpc0ZyZWUgPyAkKCcjd3ByLXJpLXF1b3RhLWJhbm5lcicpIDogJCgnI3JvY2tldF9pbnNpZ2h0c19zdXJ2ZXknKTtcblxuXHRcdC8vIFNob3cgYmFubmVyIGlmIFVSTCBsaW1pdCByZWFjaGVkIE9SIG5vIGNyZWRpdHMgbGVmdCAobWF0Y2hpbmcgUEhQIGxvZ2ljIGluIFN1YnNjcmliZXIucGhwIGxpbmUgMzk4KS5cblx0XHRjb25zdCBzaG91bGRTaG93QmFubmVyID0gY2FuQWRkUGFnZXMgPT09IGZhbHNlIHx8ICFoYXNDcmVkaXQ7XG5cblx0XHRpZiAoc2hvdWxkU2hvd0Jhbm5lcikge1xuXHRcdFx0JHN1bW1hcnlJbmZvLmhpZGUoKTtcblx0XHRcdCRxdW90YUJhbm5lci5yZW1vdmVDbGFzcygnaGlkZGVuJyk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdCRzdW1tYXJ5SW5mby5zaG93KCk7XG5cdFx0XHQkcXVvdGFCYW5uZXIuYWRkQ2xhc3MoJ2hpZGRlbicpO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlSGFzQ3JlZGl0KSB7XG5cdFx0aWYgKHJlc3BvbnNlSGFzQ3JlZGl0ICE9PSB1bmRlZmluZWQgJiYgaGFzQ3JlZGl0ICE9PSByZXNwb25zZUhhc0NyZWRpdCkge1xuXHRcdFx0aGFzQ3JlZGl0ID0gcmVzcG9uc2VIYXNDcmVkaXQ7XG5cblx0XHRcdC8vIFVwZGF0ZSBhbGwgcmV0ZXN0IGJ1dHRvbnMgd2hlbiBjcmVkaXQgc3RhdHVzIGNoYW5nZXNcblx0XHRcdHVwZGF0ZUFsbFJldGVzdEJ1dHRvbnMoKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVBbGxSZXRlc3RCdXR0b25zKCkge1xuXHRcdGNvbnN0IHJldGVzdEJ1dHRvbnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLWFjdGlvbi1zcGVlZF9yYWRhcl9yZWZyZXNoJyk7XG5cblx0XHRyZXRlc3RCdXR0b25zLmZvckVhY2goYnV0dG9uID0+IHtcblx0XHRcdGNvbnN0IHJvdyA9IGJ1dHRvbi5jbG9zZXN0KCcud3ByLXJpLWl0ZW0nKTtcblx0XHRcdGlmICghcm93KSByZXR1cm47XG5cblx0XHRcdC8vIEdldCB0aGUgcm93IElEIGFuZCBjaGVjayBpZiBpdCdzIGN1cnJlbnRseSBiZWluZyBwcm9jZXNzZWRcblx0XHRcdGNvbnN0IHJvd0lkID0gcGFyc2VJbnQocm93LmRhdGFzZXQucm9ja2V0SW5zaWdodHNJZCwgMTApO1xuXHRcdFx0Y29uc3QgaXNSdW5uaW5nID0gcm9ja2V0SW5zaWdodHNJZHMuaW5jbHVkZXMocm93SWQpO1xuXG5cdFx0XHRpZiAoIWhhc0NyZWRpdCB8fCBpc1J1bm5pbmcpIHtcblx0XHRcdFx0Ly8gRGlzYWJsZSBidXR0b25cblx0XHRcdFx0YnV0dG9uLmNsYXNzTGlzdC5hZGQoJ3dwci1yaS1hY3Rpb24tLWRpc2FibGVkJyk7XG5cdFx0XHRcdGJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2Rpc2FibGVkJywgJ3RydWUnKTtcblxuXHRcdFx0XHRpZiAoIWhhc0NyZWRpdCkge1xuXHRcdFx0XHRcdC8vIEFkZCB0b29sdGlwIGZvciBubyBjcmVkaXRcblx0XHRcdFx0XHRidXR0b24uY2xhc3NMaXN0LmFkZCgnd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyk7XG5cdFx0XHRcdFx0YnV0dG9uLnNldEF0dHJpYnV0ZSgndGl0bGUnLCB3aW5kb3cucm9ja2V0X2FqYXhfZGF0YT8ucm9ja2V0X2luc2lnaHRzX25vX2NyZWRpdF90b29sdGlwIHx8ICdVcGdyYWRlIHlvdXIgcGxhbiB0byBnZXQgYWNjZXNzIHRvIHJlLXRlc3QgcGVyZm9ybWFuY2Ugb3IgcnVuIG5ldyB0ZXN0cycpO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBFbmFibGUgYnV0dG9uXG5cdFx0XHRcdGJ1dHRvbi5jbGFzc0xpc3QucmVtb3ZlKCd3cHItcmktYWN0aW9uLS1kaXNhYmxlZCcsICd3cHItYnRuLXdpdGgtdG9vbC10aXAnKTtcblx0XHRcdFx0YnV0dG9uLnJlbW92ZUF0dHJpYnV0ZSgnZGlzYWJsZWQnKTtcblx0XHRcdFx0YnV0dG9uLnJlbW92ZUF0dHJpYnV0ZSgndGl0bGUnKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdGZ1bmN0aW9uIHJlc2V0UG9sbGluZygpIHtcblx0XHRpZiAocG9sbFRpbWVyKSB7XG5cdFx0XHRjbGVhclRpbWVvdXQocG9sbFRpbWVyKTtcblx0XHRcdHBvbGxUaW1lciA9IG51bGw7XG5cdFx0fVxuXHRcdHBvbGxJbnRlcnZhbCA9IFBPTExfQkFTRV9JTlRFUlZBTDtcblx0fVxuXG5cdGZ1bmN0aW9uIHNjaGVkdWxlUG9sbGluZygpIHtcblx0XHRpZiAocm9ja2V0SW5zaWdodHNJZHMubGVuZ3RoID4gMCkge1xuXHRcdFx0cG9sbFRpbWVyID0gc2V0VGltZW91dCgoKSA9PiB7XG5cdFx0XHRcdGdldFJlc3VsdHMoKTtcblx0XHRcdH0sIHBvbGxJbnRlcnZhbCk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gaW5jcmVtZW50UG9sbGluZygpIHtcblx0XHRwb2xsSW50ZXJ2YWwgPSBNYXRoLm1pbihwb2xsSW50ZXJ2YWwgKiAxLjMsIFBPTExfTUFYX0lOVEVSVkFMKTsgLy8gRXhwb25lbnRpYWwgYmFja29mZlxuXHR9XG5cbiAgICBmdW5jdGlvbiBpc09uRGFzaGJvYXJkKCkge1xuICAgICAgICBjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHdpbmRvdy5sb2NhdGlvbi5zZWFyY2gpO1xuICAgICAgICByZXR1cm4gdXJsUGFyYW1zLmdldCgncGFnZScpID09PSAnd3Byb2NrZXQnICYmIHdpbmRvdy5sb2NhdGlvbi5oYXNoID09PSAnI2Rhc2hib2FyZCc7XG4gICAgfVxuXG5cdGZ1bmN0aW9uIGlzT25Sb2NrZXRJbnNpZ2h0cygpIHtcblx0XHRjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHdpbmRvdy5sb2NhdGlvbi5zZWFyY2gpO1xuXHRcdHJldHVybiB1cmxQYXJhbXMuZ2V0KCdwYWdlJykgPT09ICd3cHJvY2tldCcgJiYgd2luZG93LmxvY2F0aW9uLmhhc2ggPT09ICcjcm9ja2V0X2luc2lnaHRzJztcblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZUdsb2JhbFNjb3JlUm93KGdsb2JhbFNjb3JlRGF0YSl7XG5cdFx0Y29uc3QgJHRhYmxlR2xvYmFsU2NvcmUgPSAkKCcud3ByLXJpLXVybHMtdGFibGUgLndwci1nbG9iYWwtc2NvcmUnKTtcblx0XHRpZiAoJHRhYmxlR2xvYmFsU2NvcmUubGVuZ3RoID4gMCl7XG5cdFx0XHQkdGFibGVHbG9iYWxTY29yZS5yZXBsYWNlV2l0aChnbG9iYWxTY29yZURhdGEucm93X2h0bWwpO1xuXHRcdH1lbHNlIGlmICgkdGFibGVCb2R5Lmxlbmd0aCA+IDApIHtcblx0XHRcdCR0YWJsZUJvZHkucHJlcGVuZChnbG9iYWxTY29yZURhdGEucm93X2h0bWwpO1xuXHRcdH1cblx0XHRkZWNpZGVHbG9iYWxTY29yZVRvVXBkYXRlKCk7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgZ2xvYmFsIHNjb3JlIFVJIHdpZGdldCBvciB0YWJsZSByb3cgYmFzZWQgb24gdGhlIHNlbGVjdGVkIG1lbnUuXG5cdCAqIFdoZW4gdGhlIGRhc2hib2FyZCBvciByb2NrZXQgaW5zaWdodHMgbWVudSBpcyBjbGlja2VkLCB0aGlzIGZ1bmN0aW9uIHVwZGF0ZXNcblx0ICogdGhlIGNvcnJlc3BvbmRpbmcgZ2xvYmFsIHNjb3JlIGRpc3BsYXkgYWZ0ZXIgYSBzaG9ydCBkZWxheS5cblx0ICovXG5cdGZ1bmN0aW9uIGRlY2lkZUdsb2JhbFNjb3JlVG9VcGRhdGUoKSB7XG5cdFx0aWYgKCcnID09PSBnbG9iYWxTY29yZURhdGEuaHRtbCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRsZXQgZ2xvYmFsU2NvcmVXaWRnZXRzID0gJCgnLndwci1nbG9iYWwtc2NvcmUtd2lkZ2V0Jyk7XG5cblx0XHRpZiAoZ2xvYmFsU2NvcmVXaWRnZXRzLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIFVwZGF0ZSBhbGwgZ2xvYmFsIHNjb3JlIHdpZGdldCBpbnN0YW5jZXMuXG5cdFx0Z2xvYmFsU2NvcmVXaWRnZXRzLmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmh0bWwpO1xuXG5cdFx0Ly8gRGlzYWJsZSBcIkFkZCBQYWdlc1wiIGJ1dHRvbiBvbiBnbG9iYWwgc2NvcmUgd2lkZ2V0LlxuXHRcdGlmICghKCdkaXNhYmxlZF9idG5faHRtbCcgaW4gZ2xvYmFsU2NvcmVEYXRhKSkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCQoJy53cHItZ2xvYmFsLXNjb3JlLXdpZGdldCAud3ByLWdsb2JhbC1zY29yZS13aWRnZXQtYnRuLXdyYXBwZXInKS5odG1sKGdsb2JhbFNjb3JlRGF0YS5kaXNhYmxlZF9idG5faHRtbC5nbG9iYWxfc2NvcmVfd2lkZ2V0KTtcblx0fVxuXG5cdC8vID09PT0gQUpBWCBIYW5kbGVycyA9PT09XG5cdGZ1bmN0aW9uIGdldFJlc3VsdHMoKSB7XG5cdFx0aWYgKHJvY2tldEluc2lnaHRzSWRzLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiB3aW5kb3cud3AudXJsLmFkZFF1ZXJ5QXJncyggJy93cC1yb2NrZXQvdjEvcm9ja2V0LWluc2lnaHRzL3BhZ2VzL3Byb2dyZXNzJywgeyBpZHM6IHJvY2tldEluc2lnaHRzSWRzIH0gKSxcblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2VzcyAmJiBBcnJheS5pc0FycmF5KHJlc3BvbnNlLnJlc3VsdHMpKSB7XG5cdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdHVzXG5cdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBxdW90YSBiYW5uZXIgdmlzaWJpbGl0eVxuXHRcdFx0XHR1cGRhdGVRdW90YUJhbm5lcihyZXNwb25zZS5jYW5fYWRkX3BhZ2VzKTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIGRhdGEgYW5kIHdpZGdldCB3aGVuIHN0YXR1cyB8fCBwYWdlIGNvdW50IGNoYW5nZXMuXG5cdFx0XHRcdGlmIChnbG9iYWxTY29yZURhdGEuZGF0YS5zdGF0dXMgIT09IHJlc3BvbnNlLmdsb2JhbF9zY29yZV9kYXRhLmRhdGEuc3RhdHVzIHx8IGdsb2JhbFNjb3JlRGF0YS5kYXRhLnBhZ2VzX251bSAhPT0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuZGF0YS5wYWdlc19udW0pIHtcblx0XHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIGRhdGEuXG5cdFx0XHRcdFx0Z2xvYmFsU2NvcmVEYXRhID0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGE7XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgYWxsIGdsb2JhbCBzY29yZSB3aWRnZXQgaW5zdGFuY2VzLlxuXHRcdFx0XHRcdCQoJy53cHItZ2xvYmFsLXNjb3JlLXdpZGdldCcpLmh0bWwocmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuaHRtbCk7XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSByb3cgaW4gdGFibGUgaWYgb24gUm9ja2V0IEluc2lnaHRzIHBhZ2UuXG5cdFx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRcdC8vIEZpcmUgY3VzdG9tIGV2ZW50IGZvciBvdGhlciB3aWRnZXRzIChsaWtlIHJlY29tbWVuZGF0aW9ucylcblx0XHRcdFx0XHRkb2N1bWVudC5kaXNwYXRjaEV2ZW50KG5ldyBDdXN0b21FdmVudCgnd3ByR2xvYmFsU2NvcmVVcGRhdGVkJywgeyBkZXRhaWw6IGdsb2JhbFNjb3JlRGF0YS5kYXRhIH0pKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRyZXNwb25zZS5yZXN1bHRzLmZvckVhY2gocmVzdWx0ID0+IHtcblx0XHRcdFx0XHRjb25zdCAkcm93ID0gJChgLndwci1yaS1pdGVtW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtyZXN1bHQuaWR9XCJdYCk7XG5cdFx0XHRcdFx0JHJvdy5yZXBsYWNlV2l0aChyZXN1bHQuaHRtbCk7XG5cblx0XHRcdFx0XHQkKGRvY3VtZW50KS50cmlnZ2VyKCdyb2NrZXQtaW5zaWdodHMtcGFnZS10ZXN0LXBvbGxpbmcnLCBbcmVzdWx0LmlkXSk7XG5cblx0XHRcdFx0XHQvLyBUcmlnZ2VyIGN1c3RvbSBldmVudCBvbmx5IHdoZW4gdGVzdCBpcyBjb21wbGV0ZWQgYW5kIG5vdCBmYWlsZWQsIHNvIHdlIGRvbid0IHRhcmdldCBhbiBlbGVtZW50IHRoYXQgbWlnaHQgYmUgcmVtb3ZlZCBmcm9tIHRoZSBET00gYWZ0ZXIgdGVzdCBjb21wbGV0aW9uLlxuXHRcdFx0XHRcdGlmIChyZXN1bHQuc3RhdHVzID09PSAnY29tcGxldGVkJykge1xuXHRcdFx0XHRcdFx0JChkb2N1bWVudCkudHJpZ2dlcigncm9ja2V0LWluc2lnaHRzLXBhZ2UtdGVzdC1jb21wbGV0ZWQnLCBbcmVzdWx0LmlkXSk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0aWYgKHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnKSB7XG5cdFx0XHRcdFx0XHRyZW1vdmVJZChyZXN1bHQuaWQpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSk7XG5cblx0XHRcdFx0aW5jcmVtZW50UG9sbGluZygpO1xuXHRcdFx0XHRzY2hlZHVsZVBvbGxpbmcoKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIE9uIGVycm9yLCBjbGVhciBJRHMgYW5kIHN0b3AgcG9sbGluZ1xuXHRcdFx0XHRyb2NrZXRJbnNpZ2h0c0lkcyA9IFtdO1xuXHRcdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdFx0Y29uc29sZS5lcnJvcignUG9sbGluZyBlcnJvcjonLCByZXNwb25zZS5yZXN1bHRzIHx8IHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHRmdW5jdGlvbiBoYW5kbGVBZGRQYWdlKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQvLyBjaGVjayBpZiBoYXMgYXR0ciBkaXNhYmxlZFxuXHRcdGlmICgkKHRoaXMpLmF0dHIoJ2Rpc2FibGVkJykpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBwYWdlVXJsID0gJHBhZ2VVcmxJbnB1dC52YWwoKS50cmltKCk7XG5cblx0XHRpZiAoIWlzVmFsaWRVcmwocGFnZVVybCkpIHtcblx0XHRcdGFsZXJ0KCdQbGVhc2UgZW50ZXIgYSB2YWxpZCBVUkwnKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBzb3VyY2UgPSAkKHRoaXMpLmRhdGEoJ3NvdXJjZScpO1xuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyxcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGRhdGE6IHtcblx0XHRcdFx0XHRwYWdlX3VybDogcGFnZVVybCxcblx0XHRcdFx0XHRzb3VyY2U6IHNvdXJjZVxuXHRcdFx0XHR9LFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzKSB7XG5cdFx0XHRcdGlmICggISBoYXNJZChyZXNwb25zZS5pZCkgKSB7XG5cdFx0XHRcdFx0JHBhZ2VVcmxJbnB1dC52YWwoJycpO1xuXHRcdFx0XHRcdCR0YWJsZUJvZHkuYXBwZW5kKHJlc3BvbnNlLmh0bWwpO1xuXG5cdFx0XHRcdFx0Ly8gQ3VzdG9tIGV2ZW50IHdoZW4gbmV3IHBhZ2UgaXMgYWRkZWQuXG5cdFx0XHRcdFx0JChkb2N1bWVudCkudHJpZ2dlcigncm9ja2V0LWluc2lnaHRzLXBhZ2UtYWRkZWQnKTtcblxuXHRcdFx0XHRcdCR0YWJsZS5yZW1vdmVDbGFzcygnaGlkZGVuJyk7XG5cdFx0XHRcdFx0YWRkSWRzKHJlc3BvbnNlLmlkKTtcblx0XHRcdFx0XHRsZXQgcGFnZXNfbnVtX2NvbnRhaW5lciA9ICQoJyNyb2NrZXRfcm9ja2V0X2luc2lnaHRzX3BhZ2VzX251bScpO1xuXHRcdFx0XHRcdHBhZ2VzX251bV9jb250YWluZXIudGV4dCggcGFyc2VJbnQoIHBhZ2VzX251bV9jb250YWluZXIudGV4dCgpICkgKyAxICk7XG5cblx0XHRcdFx0XHQvLyBVcGRhdGUgY3JlZGl0IHN0YXR1c1xuXHRcdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQpO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSBkYXRhLlxuXHRcdFx0XHRcdGdsb2JhbFNjb3JlRGF0YSA9IHJlc3BvbnNlLmdsb2JhbF9zY29yZV9kYXRhO1xuXG5cdFx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSByb3cgaW4gdGFibGUgaWYgb24gUm9ja2V0IEluc2lnaHRzIHBhZ2UuXG5cdFx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRcdGlmICgnZGlzYWJsZWRfYnRuX2h0bWwnIGluIGdsb2JhbFNjb3JlRGF0YSkge1xuXHRcdFx0XHRcdFx0JCgnI3dwcl9yb2NrZXRfaW5zaWdodHNfYWRkX3BhZ2VfYnRuX3dyYXBwZXInKS5odG1sKGdsb2JhbFNjb3JlRGF0YS5kaXNhYmxlZF9idG5faHRtbC5yb2NrZXRfaW5zaWdodHMpO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdC8vIFNob3cvaGlkZSBxdW90YSBiYW5uZXIgYmFzZWQgb24gY2FuX2FkZF9wYWdlc1xuXHRcdFx0XHRcdHVwZGF0ZVF1b3RhQmFubmVyKHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMpO1xuXG5cdFx0XHRcdFx0aWYgKHBvbGxUaW1lcikge1xuXHRcdFx0XHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHRcdFx0XHR9XG5cblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIENsZWFyIHRoZSBpbnB1dCBmaWVsZCBvbiBlcnJvclxuXHRcdFx0XHQkcGFnZVVybElucHV0LnZhbCgnJyk7XG5cblx0XHRcdFx0Ly8gSGFuZGxlIFVSTCBsaW1pdCByZWFjaGVkIGVycm9yXG5cdFx0XHRcdGlmIChyZXNwb25zZT8ubWVzc2FnZSAmJiByZXNwb25zZS5tZXNzYWdlLmluY2x1ZGVzKCdNYXhpbXVtIG51bWJlciBvZiBVUkxzIHJlYWNoZWQnKSkge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZSBVSSBzdGF0ZSB0byByZWZsZWN0IFVSTCBsaW1pdCBoYXMgYmVlbiByZWFjaGVkXG5cdFx0XHRcdFx0ZGlzYWJsZUFkZFVybEVsZW1lbnRzKCk7XG5cdFx0XHRcdFx0Ly8gU2hvdyBxdW90YSBiYW5uZXIgKGNhbl9hZGRfcGFnZXMgPSBmYWxzZSlcblx0XHRcdFx0XHR1cGRhdGVRdW90YUJhbm5lcihyZXNwb25zZS5jYW5fYWRkX3BhZ2VzICE9PSB1bmRlZmluZWQgPyByZXNwb25zZS5jYW5fYWRkX3BhZ2VzIDogZmFsc2UpO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Y29uc29sZS5lcnJvcihyZXNwb25zZT8ubWVzc2FnZSB8fCByZXNwb25zZSk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHRmdW5jdGlvbiBoYW5kbGVSZXNldFBhZ2UoZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdGNvbnN0ICRidXR0b24gPSAkKHRoaXMpO1xuXHRcdGxldCBpZCA9ICRidXR0b24ucGFyZW50cygnLndwci1yaS1pdGVtJykuZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cdFx0aWYgKCAhIGlkICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IHNvdXJjZSA9ICRidXR0b24uZGF0YSgnc291cmNlJyk7XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nICsgaWQsXG5cdFx0XHRcdG1ldGhvZDogJ1BBVENIJyxcblx0XHRcdFx0ZGF0YToge1xuXHRcdFx0XHRcdHNvdXJjZTogc291cmNlXG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHQpLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRpZiAocmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRhZGRJZHMocmVzcG9uc2UuaWQpO1xuXG5cdFx0XHRcdCQoYCNyaV9kZXRhaWxzXyR7cmVzcG9uc2UuaWR9IC5kZXRhaWxzLXNlY3Rpb24tdGRgKS5yZW1vdmUoKTtcblx0XHRcdFx0Y29uc3QgJHJvdyA9ICQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7cmVzcG9uc2UuaWR9XCJdYCk7XG5cdFx0XHRcdCRyb3cucmVwbGFjZVdpdGgocmVzcG9uc2UuaHRtbCk7XG5cblx0XHRcdFx0Ly8gQ3VzdG9tIGV2ZW50IHdoZW4gcGFnZSBpcyByZXRlc3RlZC5cbiAgICAgICAgXHRcdCQoZG9jdW1lbnQpLnRyaWdnZXIoJ3JvY2tldC1pbnNpZ2h0cy1wYWdlLXJldGVzdCcsIFtyZXNwb25zZS5pZF0pO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBjcmVkaXQgc3RhdHVzXG5cdFx0XHRcdHVwZGF0ZUNyZWRpdFN0YXRlKHJlc3BvbnNlLmhhc19jcmVkaXQpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBxdW90YSBiYW5uZXIgdmlzaWJpbGl0eVxuXHRcdFx0XHR1cGRhdGVRdW90YUJhbm5lcihyZXNwb25zZS5jYW5fYWRkX3BhZ2VzKTtcblxuICAgICAgICAgICAgICAgIC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YS5cbiAgICAgICAgICAgICAgICBnbG9iYWxTY29yZURhdGEgPSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIHJvdyBpbiB0YWJsZSBpZiBvbiBSb2NrZXQgSW5zaWdodHMgcGFnZS5cblx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRpZiAocG9sbFRpbWVyKSB7XG5cdFx0XHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0XHRcdH1cblx0XHRcdFx0c2NoZWR1bGVQb2xsaW5nKCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRjb25zb2xlLmVycm9yKHJlc3BvbnNlPy5tZXNzYWdlIHx8IHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fVxuXG5cdC8vID09PT0gSW5pdGlhbGl6YXRpb24gPT09PVxuXHQvLyBCaW5kIGV2ZW50XG5cdCQoZG9jdW1lbnQpLm9uKCAnY2xpY2snLCAnI3dwci1hY3Rpb24tYWRkX3BhZ2Vfc3BlZWRfcmFkYXInLCBoYW5kbGVBZGRQYWdlICk7XG5cdCQoZG9jdW1lbnQpLm9uKCAnY2xpY2snLCAnLndwci1hY3Rpb24tc3BlZWRfcmFkYXJfcmVmcmVzaCcsIGhhbmRsZVJlc2V0UGFnZSApO1xuXHQvLyBIYW5kbGUgRW50ZXIga2V5IHByZXNzIG9uIHBhZ2UgdXJsIGlucHV0LlxuXHQkKGRvY3VtZW50KS5vbiggJ2tleXByZXNzJywgJyN3cHItc3BlZWQtcmFkYXItdXJsLWlucHV0JywgZnVuY3Rpb24oZSkge1xuXHRcdGlmIChlLmtleSA9PT0gJ0VudGVyJykge1xuXHRcdCAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCAgJCgnI3dwci1hY3Rpb24tYWRkX3BhZ2Vfc3BlZWRfcmFkYXInKS5jbGljaygpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gT25seSBwb2xsIGlmIG9uIGEgd3ByIHNlY3Rpb24gdGhhdCByZXF1aXJlcyBwb2xsaW5nKGRhc2hib2FyZHxyb2NrZXRfaW5zaWdodHMpIChtb3JlIHJvYnVzdCBjaGVjaylcbiAgICBmdW5jdGlvbiBpc1ZhbGlkUGFnZUZvclBvbGxpbmcoKSB7XG4gICAgICAgIGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMod2luZG93LmxvY2F0aW9uLnNlYXJjaCk7XG5cdFx0cmV0dXJuICd3cHJvY2tldCcgPT09IHVybFBhcmFtcy5nZXQoJ3BhZ2UnKTtcbiAgICB9XG5cblx0Ly8gUmVzdW1lIHBvbGxpbmcgaWYgbmVlZGVkXG5cdGlmIChpc1ZhbGlkUGFnZUZvclBvbGxpbmcoKSAmJiByb2NrZXRJbnNpZ2h0c0lkcy5sZW5ndGggPiAwKSB7XG5cdFx0aWYgKHBvbGxUaW1lcikge1xuXHRcdFx0cmVzZXRQb2xsaW5nKCk7XG5cdFx0fVxuXHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHR9XG5cblx0Ly8gSGFuZGxlIFVJIHVwZGF0ZSBvbiB0aGUgcm9ja2V0IGluc2lnaHRzIHRhYiB3aGVuIFwiQWRkIFBhZ2VzXCIgYnV0dG9uIG9uIHRoZSBnbG9iYWwgc2NvcmUgd2lkZ2V0IGlzIGNsaWNrZWQuXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXBlcmNlbnRhZ2Utc2NvcmUtd2lkZ2V0IC53cHItcmktYWRkLXVybC1idXR0b24nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAoIXRoaXMudGV4dENvbnRlbnQuaW5jbHVkZXMoJ0FkZCBQYWdlcycpKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gRGVsYXkgVUkgdXBkYXRlIGEgYml0IHRpbGwgZWxlbWVudCBpcyB2aXNpYmxlLlxuXHRcdHNldFRpbWVvdXQoKCkgPT4ge1xuXHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblx0XHR9LCAzMCk7XG5cdH0pO1xuXG5cdC8vIEhhbmRsZSBjb2xsYXBzZWVkIHN0eWxpbmcgZm9yIGZpcnN0IGxvYWQgb3IgZHluYW1pYyByb3cgYWRkaXRpb24uXG5cdGZ1bmN0aW9uIGFkZENvbGxhcHNlZFN0eWxpbmdUb0xhc3RSb3cob25Mb2FkID0gZmFsc2UpIHtcblx0XHQkKCcud3ByLXJpLWl0ZW0nKS5sYXN0KCkuZmluZCgndGQnKS5hZGRDbGFzcygnYm9yZGVyLWJvdHRvbScpO1xuXG5cdFx0aWYgKG9uTG9hZCkge1xuXHRcdFx0Ly8gT24gbG9hZCwgcmVtb3ZlIHdwci1sYXN0LWV4cGFuZGVkIGZyb20gZWxlbWVudHMgdGhhdCBhcmUgbm90IHRoZSBsYXN0IGFmdGVyIGJlaW5nIGFkZGVkIGZyb20gYmFja2VuZC5cblx0XHRcdCQoJy53cHItcmktaXRlbS10b2dnbGUnKS5ub3QoJzpsYXN0JykucmVtb3ZlQ2xhc3MoJ3dwci1sYXN0LWV4cGFuZGVkJyk7XG5cdFx0XHQkKCcud3ByLXJpLWl0ZW0tYWN0aW9ucycpLm5vdCgnOmxhc3QnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtZXhwYW5kZWQnKTtcblx0XHRcdCQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5ub3QoJzpsYXN0JykucmVtb3ZlQ2xhc3MoJ3dwci1sYXN0LWV4cGFuZGVkJyk7XG5cblx0XHRcdC8vIEJhaWwgZWFybHkgaWYgbGFzdCBpdGVtIGlzIGFscmVhZHkgZXhwYW5kZWQgb24gbG9hZCBzbyBhcyBub3QgdG8gaGF2ZSBjb25mbGljdGluZyBzdHlsZXMuXG5cdFx0XHRpZigkKCcuZGV0YWlscy1zZWN0aW9uLXRkJykubGFzdCgpLmhhc0NsYXNzKCd3cHItbGFzdC1leHBhbmRlZCcpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHR9XG5cdFx0JCgnLndwci1yaS1pdGVtLXRvZ2dsZScpLmxhc3QoKS5hZGRDbGFzcygnd3ByLWxhc3QtY29sbGFwc2VkJyk7XG5cdFx0JCgnLndwci1yaS1pdGVtLWFjdGlvbnMnKS5sYXN0KCkuYWRkQ2xhc3MoJ3dwci1sYXN0LWNvbGxhcHNlZCcpO1xuXHRcdCQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5sYXN0KCkuYWRkQ2xhc3MoJ3dwci1sYXN0LWNvbGxhcHNlZCcpO1xuXHR9XG5cblx0Ly8gVG9nZ2xlcyB0aGUgdmlzaWJpbGl0eSBvZiBhIHNpbmdsZSB0ZXN0IGRldGFpbHMgcm93LCBzd2l0Y2hlcyB0aGUgY2FyZXQgaWNvbiwgYW5kIHVwZGF0ZXMgc3R5bGluZyBmb3IgdGhlIGxhc3QgaXRlbS5cblx0ZnVuY3Rpb24gdG9nZ2xlU2luZ2xlUm93VmlzaWJpbGl0eShpbnNpZ2h0c0lkLCBzb3VyY2UpIHtcblx0XHRsZXQgJGVsZW1lbnQgPSAkKGAjcmlfZGV0YWlsc18ke2luc2lnaHRzSWR9YCk7XG5cdFx0bGV0IGlzVmlzaWJsZSA9ICRlbGVtZW50Lmhhc0NsYXNzKCd3cHItcmktZGV0YWlscy0tZXhwYW5kZWQnKTtcblx0XHRsZXQgaXNMYXN0ID0gJChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtpbnNpZ2h0c0lkfVwiXSAud3ByLXJpLWl0ZW0tdG9nZ2xlLXNpbmdsZWApLmlzKCQoJy53cHItcmktaXRlbS10b2dnbGUtc2luZ2xlJykubGFzdCgpKTtcblxuXHRcdGlmICggaXNWaXNpYmxlICkge1xuXHRcdFx0JGVsZW1lbnQucmVtb3ZlQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdFx0JChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtpbnNpZ2h0c0lkfVwiXWApLnJlbW92ZUNsYXNzKCd3cHItcmktaXRlbS0tZXhwYW5kZWQnKTtcblx0XHRcdC8vIE1hbmlwdWxhdGUgc3R5bGluZyBmb3IgbGFzdCBlbGVtZW50cyB3aGVuIGRldGFpbHMgY2VsbCBpcyBub3QgdmlzaWJsZS5cblx0XHRcdGlmIChpc0xhc3QpIHtcblx0XHRcdFx0dXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKGZhbHNlKTtcblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCRlbGVtZW50LmFkZENsYXNzKCd3cHItcmktZGV0YWlscy0tZXhwYW5kZWQnKTtcblx0XHQkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke2luc2lnaHRzSWR9XCJdYCkuYWRkQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXG5cdFx0Ly8gVHJhY2sgZXhwYW5kIG9ubHkgZXhwYW5kIG1ldHJpYyBhY3Rpb24uXG5cdFx0aGFuZGxlTWV0cmljQWN0aW9uVHJhY2tpbmcoJ2V4cGFuZCcsIGluc2lnaHRzSWQsIHNvdXJjZSk7XG5cblx0XHRpZiAoaXNMYXN0KSB7XG5cdFx0XHR1cGRhdGVSb3dTdHlsaW5nRm9yTGFzdEl0ZW0oKTtcblx0XHR9XG5cdH1cblxuXHQvLyBUcmFja3MgdXNlciBpbnRlcmFjdGlvbnMgd2l0aCBtZXRyaWMgYWN0aW9ucyBpbiBSb2NrZXQgSW5zaWdodHMgdmlhIEFKQVguXG5cdGZ1bmN0aW9uIGhhbmRsZU1ldHJpY0FjdGlvblRyYWNraW5nKGV2ZW50LCByb3dJZCwgc291cmNlKSB7XG5cdFx0JC5wb3N0KFxuXHRcdFx0YWpheHVybCxcblx0XHRcdHtcblx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X2luc2lnaHRfdHJhY2tfbWV0cmljX2FjdGlvbnMnLFxuXHRcdFx0XHRfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcblx0XHRcdFx0ZXZlbnQ6IGV2ZW50LFxuXHRcdFx0XHRyb3dfaWQ6IHJvd0lkLFxuXHRcdFx0XHRzb3VyY2U6IHNvdXJjZVxuXHRcdFx0fSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICghcmVzcG9uc2Uuc3VjY2Vzcykge1xuXHRcdFx0XHRcdGNvbnNvbGUuZXJyb3IoJ01ldHJpYyBhY3Rpb24gdHJhY2tpbmcgZmFpbGVkOicsIHJlc3BvbnNlPy5kYXRhIHx8IHJlc3BvbnNlKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdCk7XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgYm9yZGVyIHN0eWxpbmcgZm9yIHRoZSBsYXN0IHJvdyBpdGVtIGluIHRoZSBSb2NrZXQgSW5zaWdodHMgdGFibGUuXG5cdCAqXG5cdCAqIE1hbmFnZXMgYm9yZGVyIHJhZGl1cyBhbmQgYm90dG9tIGJvcmRlciBzdHlsaW5nIGJhc2VkIG9uIHdoZXRoZXIgdGhlIGRldGFpbHNcblx0ICogY2VsbCBpcyBleHBhbmRlZCBvciBjb2xsYXBzZWQgdG8gbWFpbnRhaW4gcHJvcGVyIHZpc3VhbCBhcHBlYXJhbmNlLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKGlzRXhwYW5kZWQgPSB0cnVlKSB7XG5cdFx0Y29uc3QgYWRkU3RhdGUgPSBpc0V4cGFuZGVkID8gJ3dwci1sYXN0LWV4cGFuZGVkJyA6ICd3cHItbGFzdC1jb2xsYXBzZWQnO1xuXHRcdGNvbnN0IHJlbW92ZVN0YXRlID0gaXNFeHBhbmRlZCA/ICd3cHItbGFzdC1jb2xsYXBzZWQnIDogJ3dwci1sYXN0LWV4cGFuZGVkJztcblxuXHRcdHZhciAkc2VsZWN0b3JzID0ge1xuXHRcdFx0bGFzdFRvZ2dsZTogJCgnLndwci1yaS1pdGVtLXRvZ2dsZScpLmxhc3QoKSxcblx0XHRcdGxhc3RBY3Rpb25zOiAkKCcud3ByLXJpLWl0ZW0tYWN0aW9ucycpLmxhc3QoKVxuXHRcdH07XG5cblx0XHQkc2VsZWN0b3JzLmxhc3RUb2dnbGVcblx0XHRcdC5yZW1vdmVDbGFzcyhyZW1vdmVTdGF0ZSlcblx0XHRcdC5hZGRDbGFzcyhhZGRTdGF0ZSk7XG5cblx0XHQkc2VsZWN0b3JzLmxhc3RBY3Rpb25zXG5cdFx0XHQucmVtb3ZlQ2xhc3MocmVtb3ZlU3RhdGUpXG5cdFx0XHQuYWRkQ2xhc3MoYWRkU3RhdGUpO1xuXG5cblx0XHQvLyBDaGVjayBpZiBsYXN0IGRldGFpbCByb3cgaXMgbm90IHRoZSBsYXN0IHJvdyBpbiB0aGUgdGFibGUgc28gYXMgbm90IHRvIGFwcGx5IGltcHJvcGVyIHN0eWxpbmcgd2l0aCBib3JkZXIgcmFkaXVzIGJldHdlZW4gcm93cy5cblx0XHR2YXIgJGxhc3REZXRhaWxzQ2VsbCA9ICQoJy5kZXRhaWxzLXNlY3Rpb24tdGQnKS5sYXN0KCk7XG5cdFx0aWYgKCRsYXN0RGV0YWlsc0NlbGwuY2xvc2VzdCgndHInKS5uZXh0KCd0cicpLmxlbmd0aCAhPT0gMCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdCRsYXN0RGV0YWlsc0NlbGwucmVtb3ZlQ2xhc3MocmVtb3ZlU3RhdGUpXG5cdFx0LmFkZENsYXNzKGFkZFN0YXRlKTtcblx0fVxuXG5cdC8vIFRvZ2dsZSBzaW5nbGUgaXRlbS5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktaXRlbS10b2dnbGUtc2luZ2xlJywgZnVuY3Rpb24oKSB7XG5cdFx0dmFyIGluc2lnaHRzSWQgPSAkKHRoaXMpLmNsb3Nlc3QoJy53cHItcmktaXRlbScpLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdHRvZ2dsZVNpbmdsZVJvd1Zpc2liaWxpdHkoaW5zaWdodHNJZCwgJ3VybF9leHBhbmQnKTtcblx0fSk7XG5cblx0Ly8gVG9nZ2xlIGFsbCBpdGVtcy5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktaXRlbS10b2dnbGUtYWxsJywgZnVuY3Rpb24gKCkge1xuXHRcdGlmICgkKCcud3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJykubGVuZ3RoID4gMCkge1xuXHRcdFx0JCgnLndwci1yaS1kZXRhaWxzJykucmVtb3ZlQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdFx0JCgnLndwci1yaS1pdGVtJykucmVtb3ZlQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXHRcdFx0JCh0aGlzKS5yZW1vdmVDbGFzcygnd3ByLXJpLWl0ZW0tdG9nZ2xlLWFsbC0tZXhwYW5kZWQnKTtcblx0XHRcdHVwZGF0ZVJvd1N0eWxpbmdGb3JMYXN0SXRlbShmYWxzZSk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQkKCcud3ByLXJpLWRldGFpbHMnKS5hZGRDbGFzcygnd3ByLXJpLWRldGFpbHMtLWV4cGFuZGVkJyk7XG5cdFx0JCgnLndwci1yaS1pdGVtJykuYWRkQ2xhc3MoJ3dwci1yaS1pdGVtLS1leHBhbmRlZCcpO1xuXHRcdCQodGhpcykuYWRkQ2xhc3MoJ3dwci1yaS1pdGVtLXRvZ2dsZS1hbGwtLWV4cGFuZGVkJyk7XG5cdFx0dXBkYXRlUm93U3R5bGluZ0Zvckxhc3RJdGVtKCk7XG5cblx0XHQvLyBUcmFjayBzaW5nbGUgZXhwYW5kIGV2ZW50IGZvciBcIkV4cGFuZCBBbGxcIiBhY3Rpb24gd2l0aCB0ZXN0X2lkIGFzICdhbGwnLlxuXHRcdGhhbmRsZU1ldHJpY0FjdGlvblRyYWNraW5nKCdleHBhbmQnLCAnYWxsJywgJ2dsb2JhbF9leHBhbmQnKTtcblx0fSk7XG5cblx0Ly8gVHJhY2sgXCJTZWUgR1RtZXRyaXggUmVwb3J0XCIgY2xpY2tzIGluIFJvY2tldCBJbnNpZ2h0cy5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmktcmVwb3J0JywgZnVuY3Rpb24oZSkge1x0Ly8gT25seSB0cmFjayBpZiBsaW5rIGlzIG5vdCBkaXNhYmxlZCBhbmQgbWl4cGFuZWwgaXMgYXZhaWxhYmxlLlxuXHRcdGlmICgkKHRoaXMpLmhhc0NsYXNzKCd3cHItcmktYWN0aW9uLS1kaXNhYmxlZCcpKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dmFyIGluc2lnaHRzSWQgPSAkKHRoaXMpLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1yb3ctaWQnKTtcblx0XHRoYW5kbGVNZXRyaWNBY3Rpb25UcmFja2luZygnc2VlX3JlcG9ydCcsIGluc2lnaHRzSWQsICdzZWVfcmVwb3J0X2J1dHRvbicpO1xuXHR9KTtcblxuXHQvLyBVcGRhdGUgdGFibGUgc3R5bGluZyBhZnRlciBuZXcgcGFnZSBpcyBhZGRlZC5cblx0JChkb2N1bWVudCkub24oJ3JvY2tldC1pbnNpZ2h0cy1wYWdlLXRlc3QtY29tcGxldGVkJywgZnVuY3Rpb24gKGUsIGluc2lnaHRzSWQpIHtcblx0XHQvLyBCYWlsIG91dCBpZiB0aGVyZSBpcyBtb3JlIHRoYW4gMSByZXN1bHQuXG5cdFx0aWYgKCQoJy53cHItcmktaXRlbS1yZXN1bHQnKS5sZW5ndGggPiAxKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gUmVtb3ZlIGR5bmFtaWMgY2xhc3Mgd2hlbiBvbmx5IG9uZSBpdGVtIGV4aXN0IGluIHRhYmxlLlxuXHRcdCQoJy53cHItbGFzdC1jb2xsYXBzZWQnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtY29sbGFwc2VkJyk7XG5cdH0pO1xuXG5cdC8vIFVwZGF0ZSB0YWJsZSBzdHlsaW5nIGFmdGVyIG5ldyBwYWdlIGlzIGFkZGVkLlxuXHQkKGRvY3VtZW50KS5vbigncm9ja2V0LWluc2lnaHRzLXBhZ2UtYWRkZWQnLCBmdW5jdGlvbiAoZSkge1xuXHRcdC8vIFJlbW92ZSBkeW5hbWljIGNsYXNzIGZvciBsYXN0IGl0ZW0gaWYgZXhpc3RzIHdoZW4gbmV3IHBhZ2UgaXMgYWRkZWQuXG5cdFx0JCgnLndwci1sYXN0LWNvbGxhcHNlZCcpLnJlbW92ZUNsYXNzKCd3cHItbGFzdC1jb2xsYXBzZWQnKTtcblx0XHQkKCcud3ByLWxhc3QtZXhwYW5kZWQnKS5yZW1vdmVDbGFzcygnd3ByLWxhc3QtZXhwYW5kZWQnKTtcblx0XHQkKCcuYm9yZGVyLWJvdHRvbScpLnJlbW92ZUNsYXNzKCdib3JkZXItYm90dG9tJyk7XG5cdH0pO1xuXG5cdC8vIFVwZGF0ZSB0YWJsZSBzdHlsaW5nIGFmdGVyIHJldGVzdCBvciBwb2xsaW5nIHVwZGF0ZSBmb3IgbGFzdCByb3cuXG5cdCQoZG9jdW1lbnQpLm9uKCdyb2NrZXQtaW5zaWdodHMtcGFnZS1yZXRlc3Qgcm9ja2V0LWluc2lnaHRzLXBhZ2UtdGVzdC1wb2xsaW5nJywgZnVuY3Rpb24gKGUsIGluc2lnaHRzSWQpIHtcblx0XHQvLyBDaGVjayBpZiBpdGVtIGlzIHRoZSBsYXN0LlxuXHRcdHZhciBpc0xhc3QgPSAkKGBbZGF0YS1yb2NrZXQtaW5zaWdodHMtaWQ9XCIke2luc2lnaHRzSWR9XCJdYCkuaXMoJCgnLndwci1yaS1pdGVtLXJlc3VsdCcpLmxhc3QoKSk7XG5cblx0XHRpZiAoaXNMYXN0KSB7XG5cdFx0XHRhZGRDb2xsYXBzZWRTdHlsaW5nVG9MYXN0Um93KCk7XG5cdFx0fVxuXHR9KTtcblxuXHQkKGRvY3VtZW50KS5vbigncm9ja2V0LWluc2lnaHRzLXBhZ2UtcmV0ZXN0JywgZnVuY3Rpb24gKGUsIGluc2lnaHRzSWQpIHtcblx0XHQkKGAjcmlfZGV0YWlsc18ke2luc2lnaHRzSWR9YCkucmVtb3ZlQ2xhc3MoJ3dwci1yaS1kZXRhaWxzLS1leHBhbmRlZCcpO1xuXHRcdCQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7aW5zaWdodHNJZH1cIl1gKS5yZW1vdmVDbGFzcygnd3ByLXJpLWl0ZW0tLWV4cGFuZGVkJyk7XG5cdH0pO1xuXG5cdCQod2luZG93KS5sb2FkKCgpID0+IHtcblx0XHRpZiAoICEgaXNPblJvY2tldEluc2lnaHRzKCkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQWRkIGNvbGxhcHNlZCBzdHlsaW5nIHRvIHRoZSBsYXN0IHJvdyBvbiBpbml0aWFsIGxvYWQuXG5cdFx0YWRkQ29sbGFwc2VkU3R5bGluZ1RvTGFzdFJvdyh0cnVlKTtcblxuXHRcdC8vIFNldCBpbml0aWFsIGV4cGFuZC9jb2xsYXBzZSBzdGF0ZS5cblx0XHRjb25zdCB1cmxQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHdpbmRvdy5sb2NhdGlvbi5zZWFyY2gpO1xuXHRcdGNvbnN0IHRlc3RJZCA9IHVybFBhcmFtcy5nZXQoJ3JpX2lkJyk7XG5cblx0XHQvLyBTZW5kIG1peHBhbmVsIGV2ZW50IGZvciBhdXRvIGV4cGFuZGVkIHJvdy5cblx0XHRsZXQgZmlyc3RSb3dJZCA9ICQoJy53cHItcmktaXRlbS0tZXhwYW5kZWQnKT8uZmlyc3QoKT8uZGF0YSgncm9ja2V0LWluc2lnaHRzLWlkJyk7XG5cblx0XHQvLyBDaGVjayBpZiByaV9pZCB3YXMgcGFzc2VkIGluIHF1ZXJ5IHN0cmluZyB0byBvcGVuIHNwZWNpZmljIHRlc3QuXG5cdFx0aWYgKCF0ZXN0SWQgfHwgdGVzdElkID09PSAnJykge1xuXHRcdFx0aWYgKGZpcnN0Um93SWQpIHtcblx0XHRcdFx0aGFuZGxlTWV0cmljQWN0aW9uVHJhY2tpbmcoJ2V4cGFuZCcsIGZpcnN0Um93SWQsICdhdXRvX2V4cGFuZF91cmwnKTtcblx0XHRcdH1cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRoYW5kbGVNZXRyaWNBY3Rpb25UcmFja2luZygnZXhwYW5kJywgdGVzdElkLCAncG9zdCB0eXBlIGxpc3RpbmcnKTtcblxuXHRcdCQoJ2h0bWwsIGJvZHknKS5hbmltYXRlKHtcblx0XHRcdHNjcm9sbFRvcDogJChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHt0ZXN0SWR9XCJdYCkub2Zmc2V0KCkudG9wIC0gMTAwXG5cdFx0fSwgNTAwKTtcblxuXHRcdC8vIFJlbW92ZSByaV9pZCBmcm9tIFVSTCB3aXRob3V0IHBhZ2UgcmVsb2FkXG5cdFx0dXJsUGFyYW1zLmRlbGV0ZSgncmlfaWQnKTtcblx0XHRjb25zdCBuZXdVcmwgPSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgKyAnPycgKyB1cmxQYXJhbXMudG9TdHJpbmcoKSArIHdpbmRvdy5sb2NhdGlvbi5oYXNoO1xuXHRcdHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSh7fSwgJycsIG5ld1VybCk7XG5cdH0pO1xufSk7XG4iLCIvLyBBZGQgZ3JlZW5zb2NrIGxpYiBmb3IgYW5pbWF0aW9uc1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svVHdlZW5MaXRlLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9UaW1lbGluZUxpdGUubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL2Vhc2luZy9FYXNlUGFjay5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svcGx1Z2lucy9DU1NQbHVnaW4ubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzJztcclxuXHJcbi8vIEFkZCBzY3JpcHRzXHJcbmltcG9ydCAnLi4vZ2xvYmFsL2Nkbi1kcml2ZXIuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9wYWdlTWFuYWdlci5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL21haW4uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9maWVsZHMuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9iZWFjb24uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9hamF4LmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvcmVjb21tZW5kYXRpb25zLXdpZGdldC5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL3JvY2tldGNkbi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL3JvY2tldGNkbi1zdWJzY3JpcHRpb24tcG9sbGluZy5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL2NvdW50ZG93bi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL21peHBhbmVsLmpzJyIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcbiAgICBpZiAoJ0JlYWNvbicgaW4gd2luZG93KSB7XG4gICAgICAgIC8qKlxuICAgICAgICAgKiBTaG93IGJlYWNvbnMgb24gYnV0dG9uIFwiaGVscFwiIGNsaWNrXG4gICAgICAgICAqL1xuICAgICAgICB2YXIgJGhlbHAgPSAkKCcud3ByLWluZm9BY3Rpb24tLWhlbHAnKTtcbiAgICAgICAgJGhlbHAub24oJ2NsaWNrJywgZnVuY3Rpb24oZSl7XG4gICAgICAgICAgICB2YXIgaWRzID0gJCh0aGlzKS5hdHRyKCdkYXRhLWJlYWNvbi1pZCcpO1xuICAgICAgICAgICAgdmFyIGJ1dHRvbiA9ICQodGhpcykuZGF0YSgnd3ByX3RyYWNrX2J1dHRvbicpIHx8ICdCZWFjb24gSGVscCc7XG4gICAgICAgICAgICB2YXIgY29udGV4dCA9ICQodGhpcykuZGF0YSgnd3ByX3RyYWNrX2NvbnRleHQnKSB8fCAnU2V0dGluZ3MnO1xuXG4gICAgICAgICAgICAvLyBUcmFjayB3aXRoIE1peFBhbmVsIEpTIFNES1xuICAgICAgICAgICAgd3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCk7XG5cbiAgICAgICAgICAgIC8vIENvbnRpbnVlIHdpdGggZXhpc3RpbmcgYmVhY29uIGZ1bmN0aW9uYWxpdHlcbiAgICAgICAgICAgIHdwckNhbGxCZWFjb24oaWRzKTtcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgZnVuY3Rpb24gd3ByQ2FsbEJlYWNvbihhSUQpe1xuICAgICAgICAgICAgYUlEID0gYUlELnNwbGl0KCcsJyk7XG4gICAgICAgICAgICBpZiAoIGFJRC5sZW5ndGggPT09IDAgKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgaWYgKCBhSUQubGVuZ3RoID4gMSApIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcInN1Z2dlc3RcIiwgYUlEKTtcblxuICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcIm9wZW5cIik7XG4gICAgICAgICAgICAgICAgICAgIH0sIDIwMCk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcImFydGljbGVcIiwgYUlELnRvU3RyaW5nKCkpO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICB9XG4gICAgfVxuXG5cdCQoICcud3ByLXJpLXJlcG9ydCcgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByVHJhY2tIZWxwQnV0dG9uKCAncm9ja2V0IGluc2lnaHRzIHNlZSBndG1ldHJpeCByZXBvcnQnLCAncGVyZm9ybWFuY2Ugc3VtbWFyeScgKTtcblx0fSApO1xuXG4gICAgLy8gTWl4UGFuZWwgdHJhY2tpbmcgZnVuY3Rpb25cbiAgICBmdW5jdGlvbiB3cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KSB7XG4gICAgICAgIGlmICh0eXBlb2YgbWl4cGFuZWwgIT09ICd1bmRlZmluZWQnICYmIG1peHBhbmVsLnRyYWNrKSB7XG4gICAgICAgICAgICAvLyBDaGVjayBpZiB1c2VyIGhhcyBvcHRlZCBpbiB1c2luZyBsb2NhbGl6ZWQgZGF0YVxuICAgICAgICAgICAgaWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSA9PT0gJ3VuZGVmaW5lZCcgfHwgIXJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgfHwgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAvLyBJZGVudGlmeSB1c2VyIHdpdGggaGFzaGVkIGxpY2Vuc2UgZW1haWwgaWYgYXZhaWxhYmxlXG4gICAgICAgICAgICBpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICBtaXhwYW5lbC5pZGVudGlmeShyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkKTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgbWl4cGFuZWwudHJhY2soJ0J1dHRvbiBDbGlja2VkJywge1xuICAgICAgICAgICAgICAgICdidXR0b24nOiBidXR0b24sXG5cdFx0XHRcdCdidXR0b25fY29udGV4dCc6IGNvbnRleHQsXG5cdFx0XHRcdCdwbHVnaW4nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wbHVnaW4sXG4gICAgICAgICAgICAgICAgJ2JyYW5kJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG4gICAgICAgICAgICAgICAgJ2FwcGxpY2F0aW9uJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYXBwLFxuICAgICAgICAgICAgICAgICdjb250ZXh0Jzogcm9ja2V0X21peHBhbmVsX2RhdGEuY29udGV4dCxcbiAgICAgICAgICAgICAgICAncGF0aCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGhcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLy8gTWFrZSBmdW5jdGlvbiBnbG9iYWxseSBhdmFpbGFibGVcbiAgICB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID0gd3ByVHJhY2tIZWxwQnV0dG9uO1xufSk7XG4iLCIoICggZG9jdW1lbnQgKSA9PiB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHRmdW5jdGlvbiBub3RpZnlDZG5TdGF0ZUNoYW5nZSgpIHtcblx0XHRkb2N1bWVudC5kaXNwYXRjaEV2ZW50KCBuZXcgQ3VzdG9tRXZlbnQoICd3cHItY2RuLXN0YXRlLWNoYW5nZScgKSApO1xuXHR9XG5cblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ0RPTUNvbnRlbnRMb2FkZWQnLCAoKSA9PiB7XG5cdFx0aW5pdENkbkRyaXZlclRhYnMoKTtcblx0XHRpbml0Q2RuUGF1c2VUb2dnbGUoKTtcblx0XHRpbml0QWRkSG9tZXBhZ2UoKTtcblx0XHRpbml0QWRkUGFnZSgpO1xuXHRcdGluaXREZWxldGVQYWdlKCk7XG5cdFx0dXBkYXRlU3VibWl0QnV0dG9uU3RhdGVPblN1YnNjcmlwdGlvbkxvYWRpbmcoKTtcblx0fSApO1xuXG5cdGNvbnN0IGFkZEhvbWVCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwcl9hZGRfcGFnZV9jb21wb25lbnQgLndwci1jZG4tYWRkLXBhZ2VfX2hvbWVwYWdlJyApO1xuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBzdGF0dXMgaW5kaWNhdG9yIGNvbXBvbmVudCB3aXRoIG5ldyBIVE1MIGNvbnRlbnQuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBodG1sIC0gVGhlIEhUTUwgc3RyaW5nIHRvIHJlcGxhY2UgdGhlIHN0YXR1cyBpbmRpY2F0b3Igd2l0aC5cblx0ICogQHJldHVybnMge3ZvaWR9XG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVTdGF0dXNJbmRpY2F0b3JDb21wb25lbnQoIGh0bWwgKSB7XG5cdFx0Y29uc3Qgc3RhdHVzSW5kaWNhdG9yID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWJ1aWx0LWluIC53cHItY2RuLXN0YXR1cycgKTtcblx0XHRpZiAoIHN0YXR1c0luZGljYXRvciAmJiBodG1sICkge1xuXHRcdFx0c3RhdHVzSW5kaWNhdG9yLm91dGVySFRNTCA9IGh0bWw7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIFRvZ2dsZXMgdGhlIGRpc2FibGVkIHN0YXRlIG9mIENETi1yZWxhdGVkIFVJIGVsZW1lbnRzIGJhc2VkIG9uIHRoZSBhY3RpdmUgZHJpdmVyLlxuXHQgKlxuXHQgKiBGb3IgdGhlICdyb2NrZXRjZG4nIGRyaXZlciwgdGFyZ2V0cyBib3RoIHNoYXJlZCBDRE4gYW5kIFJvY2tldENETiBzZWN0aW9ucy5cblx0ICogRm9yIGFsbCBvdGhlciBkcml2ZXJzLCBvbmx5IHRhcmdldHMgdGhlIHNoYXJlZCBDRE4gc2VjdGlvbiBhbmQgYWx3YXlzIGVuYWJsZXMgaXQuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSAgZHJpdmVyICAgVGhlIENETiBkcml2ZXIgaWRlbnRpZmllciAoZS5nLiAncm9ja2V0Y2RuJykuXG5cdCAqIEBwYXJhbSB7Ym9vbGVhbn0gZGlzYWJsZWQgV2hldGhlciB0byBkaXNhYmxlIHRoZSBDRE4gVUkgZWxlbWVudHMuXG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVSb2NrZXRDRE5FbGVtZW50c1N0YXRlKCBkcml2ZXIsIGRpc2FibGVkICkge1xuXHRcdGlmICggJ3JvY2tldGNkbicgPT09IGRyaXZlciApIHtcblx0XHRcdGlmICggISBkaXNhYmxlZCApIHtcblx0XHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy5jZG4tc2hhcmVkLXNlY3Rpb24sIC5yb2NrZXRjZG4tc2hhcmVkLXNlY3Rpb24nICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdFx0XHRlbC5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1kaXNhYmxlZCcgKTtcblx0XHRcdFx0fSApO1xuXG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy5jZG4tc2hhcmVkLXNlY3Rpb24sIC5yb2NrZXRjZG4tc2hhcmVkLXNlY3Rpb24nICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdFx0ZWwuY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0XHR9ICk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLmNkbi1zaGFyZWQtc2VjdGlvbicgKS5mb3JFYWNoKCAoIGVsICkgPT4ge1xuXHRcdFx0ZWwuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNob3dzIG9yIGhpZGVzIHRoZSBsaW1pdC1yZWFjaGVkIHRvb2x0aXAgb24gdGhlIEFERCBQQUdFIGJ1dHRvbi5cblx0ICpcblx0ICogQHBhcmFtIHtib29sZWFufSBsaW1pdFJlYWNoZWQgV2hldGhlciB0aGUgZnJlZS10aWVyIHBhZ2UgbGltaXQgaGFzIGJlZW4gcmVhY2hlZC5cblx0ICogQHJldHVybnMge3ZvaWR9XG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVUb29sdGlwU3RhdGUoIGxpbWl0UmVhY2hlZCApIHtcblx0XHRjb25zdCB0b29sdGlwID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19idXR0b24td3JhcHBlciAud3ByLXRvb2x0aXAnICk7XG5cdFx0aWYgKCB0b29sdGlwICkge1xuXHRcdFx0dG9vbHRpcC5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLWlzSGlkZGVuJywgISBsaW1pdFJlYWNoZWQgKTtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgUm9ja2V0Q0ROIENUQSB2aXNpYmlsaXR5IGFuZCBleHBhbnNpb24gc3RhdGUuXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSBjb3VudCBDdXJyZW50IG51bWJlciBvZiBmcmVlLXRpZXIgcGFnZXMuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSBsaW1pdCBGcmVlLXRpZXIgcGFnZSBsaW1pdC5cblx0ICogQHJldHVybnMge3ZvaWR9XG5cdCAqL1xuXHRmdW5jdGlvbiB1cGRhdGVSb2NrZXRDdGFTdGF0ZSggY291bnQsIGxpbWl0ICkge1xuXHRcdGNvbnN0IGN0YSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAnd3ByLXJvY2tldGNkbi1jdGEnICk7XG5cblx0XHRpZiAoICEgY3RhICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IGlzVmlzaWJsZSA9IGNvdW50ID4gMDtcblx0XHRjb25zdCBpc0V4cGFuZGVkID0gY291bnQgPj0gbGltaXQ7XG5cblx0XHRjdGEuY2xhc3NMaXN0LnRvZ2dsZSggJ3dwci1pc0hpZGRlbicsICEgaXNWaXNpYmxlICk7XG5cdFx0Y3RhLmNsYXNzTGlzdC50b2dnbGUoICd3cHItcm9ja2V0Y2RuLWN0YS0tY29sbGFwc2VkJywgaXNWaXNpYmxlICYmICEgaXNFeHBhbmRlZCApO1xuXHRcdGN0YS5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLXJvY2tldGNkbi1jdGEtLWV4cGFuZGVkJywgaXNWaXNpYmxlICYmIGlzRXhwYW5kZWQgKTtcblx0XHRjdGEuY2xhc3NMaXN0LnRvZ2dsZSggJ3dwci1yb2NrZXRjZG4tY3RhLS0tbWF4LWxpbWl0JywgaXNWaXNpYmxlICYmIGlzRXhwYW5kZWQgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBMaXN0ZW5zIGZvciBjdXN0b20gJ3JvY2tldEpzQWZ0ZXJQYWdlTmF2aWdhdGlvbicgZXZlbnQgdG8gdXBkYXRlIHRoZSBzdGF0ZSBvZiB0aGUgc3VibWl0IGJ1dHRvblxuXHQgKiBiYXNlZCBvbiB0aGUgcHJlc2VuY2Ugb2YgYSBDRE4gc3Vic2NyaXB0aW9uIGxvYWRpbmcgaW5kaWNhdG9yIG9uIHRoZSBDRE4gc2V0dGluZ3MgcGFnZS5cblx0ICpcblx0ICogRGlzYWJsZXMgdGhlIHN1Ym1pdCBidXR0b24gd2hlbiBuYXZpZ2F0aW5nIHRvIHRoZSBDRE4gcGFnZSBpZiBhIHN1YnNjcmlwdGlvbiBsb2FkaW5nIGluZGljYXRvciBpcyBwcmVzZW50LFxuXHQgKiBhbmQgcmUtZW5hYmxlcyBpdCB3aGVuIG5hdmlnYXRpbmcgYXdheSBmcm9tIHRoZSBDRE4gcGFnZS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZVN1Ym1pdEJ1dHRvblN0YXRlT25TdWJzY3JpcHRpb25Mb2FkaW5nKCkge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdyb2NrZXRKc0FmdGVyUGFnZU5hdmlnYXRpb24nLCAoIGUgKSA9PiB7XG5cdFx0XHQvLyBCYWlsIG91dCBpZiBzdWJtaXQgYnV0dG9uIGlzIG5vdCB2aXNpYmxlIGZvciB0aGUgY3VycmVudCBwYWdlLlxuXHRcdFx0aWYgKGdldENvbXB1dGVkU3R5bGUoIGUuZGV0YWlsLnN1Ym1pdEJ1dHRvbiApLmRpc3BsYXkgPT09ICdub25lJykge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IGNsYXNzZXMgPSBbXG5cdFx0XHRcdCcud3ByLWljb24tb3JhbmdlLWxvYWRlcicsXG5cdFx0XHRcdCcud3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnLFxuXHRcdFx0XTtcblxuXHRcdFx0Y29uc3QgYWxsUHJlc2VudCA9IGNsYXNzZXMuZXZlcnkoIGNscyA9PiBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCBjbHMgKSAhPT0gbnVsbCApO1xuXG5cdFx0XHQvLyBSZS1lbmFibGUgc3VibWl0IGJ1dHRvbiB3aGVuIHBhZ2UgaXMgbm90IGNkbiBhbmQgYmFpbCBvdXQuXG5cdFx0XHRpZiAoZS5kZXRhaWwucGFnZUlkICE9PSAncGFnZV9jZG4nKSB7XG5cdFx0XHRcdGlmIChlLmRldGFpbC5zdWJtaXRCdXR0b24uY2xhc3NMaXN0LmNvbnRhaW5zKCAnd3ByLWNkbi1kaXNhYmxlZCcgKSkge1xuXHRcdFx0XHRcdGUuZGV0YWlsLnN1Ym1pdEJ1dHRvbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1kaXNhYmxlZCcgKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gQmFpbCBvdXQgaWYgbm8gY2RuIHN1YnNjcmlwdGlvbiBsb2FkZXIgaXMgcHJlc2VudC5cblx0XHRcdGlmICggISBhbGxQcmVzZW50ICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIERpc2FibGUgc3VibWl0IGJ1dHRvbiB3aGVuIG9uIGNkbiBwYWdlIGFuZCBzdWJzY3JpcHRpb24gbG9hZGVyIGlzIHByZXNlbnQuXG5cdFx0XHRlLmRldGFpbC5zdWJtaXRCdXR0b24uY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tZGlzYWJsZWQnICk7XG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFNldHMgdGhlIHN1YnNjcmlwdGlvbiBsb2FkaW5nIHN0YXRlIG9uIHRoZSBDRE4gVUkuXG5cdCAqXG5cdCAqIERpc2FibGVzIHRoZSBidWlsdC1pbiBDRE4gc2VjdGlvbiwgcHVyZ2UgYW5kIGV4Y2x1ZGUgc2VjdGlvbnMuXG5cdCAqL1xuXHRmdW5jdGlvbiBzZXRTdWJzY3JpcHRpb25Mb2FkaW5nU3RhdGUoKSB7XG5cdFx0Y29uc3QgYnVpbHRJbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKTtcblxuXHRcdGlmICggYnVpbHRJbiApIHtcblx0XHRcdGJ1aWx0SW4uY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tYnVpbHQtaW4tLWRpc2FibGVkJyApO1xuXHRcdH1cblxuXHRcdC8vIERpc2FibGUgcHVyZ2UgQ0ROIGNhY2hlIHNlY3Rpb24uXG5cdFx0Y29uc3QgcHVyZ2VTZWN0aW9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLXB1cmdlLnJvY2tldGNkbicgKTtcblxuXHRcdGlmICggcHVyZ2VTZWN0aW9uICkge1xuXHRcdFx0cHVyZ2VTZWN0aW9uLmNsYXNzTGlzdC5hZGQoICd3cHItY2RuLWRpc2FibGVkJyApO1xuXHRcdH1cblxuXHRcdC8vIERpc2FibGUgZXhjbHVzaW9uIGZpZWxkcyBhbmQgc2VjdGlvbiBoZWFkZXIuXG5cdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy53cHItY2RuLWV4Y2x1c2lvbnMnICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdGVsLmNsYXNzTGlzdC5hZGQoICd3cHItY2RuLWRpc2FibGVkJyApO1xuXG5cdFx0XHRjb25zdCB0ZXh0YXJlYSA9IGVsLnF1ZXJ5U2VsZWN0b3IoICd0ZXh0YXJlYScgKTtcblxuXHRcdFx0aWYgKCB0ZXh0YXJlYSApIHtcblx0XHRcdFx0dGV4dGFyZWEuZGlzYWJsZWQgPSB0cnVlO1xuXHRcdFx0fVxuXHRcdH0gKTtcblxuXHRcdGNvbnN0IHN1Ym1pdEJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLW9wdGlvbnMtc3VibWl0JyApO1xuXHRcdGlmICggc3VibWl0QnV0dG9uICkge1xuXHRcdFx0c3VibWl0QnV0dG9uLmNsYXNzTGlzdC5hZGQoICd3cHItY2RuLWRpc2FibGVkJyApO1xuXHRcdH1cblxuXHRcdC8vIENyZWF0ZSBwb2xsaW5nIG1lY2hhbmlzbSB0byBzZW5kIGEgcmVxdWVzdCBldmVyeSAxMCBzZWNvbmRzIHRvIGdldCB0aGUgc3Vic2NyaXB0aW9uIHN0YXR1cyBhbmQgb25jZSB0aGUgc3Vic2NyaXB0aW9uIGlzIGFjdGl2ZSwgd2Ugd2lsbCByZWZyZXNoIHRoZSBwYWdlIGZvciBub3cuXG5cdFx0ZG9jdW1lbnQuZGlzcGF0Y2hFdmVudChuZXcgQ3VzdG9tRXZlbnQoJ3JvY2tldENETlN1YnNjcmlwdGlvbkxvYWRpbmcnLCB7fSkpO1xuXHR9XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIENETiBkcml2ZXIgdGFiIHN3aXRjaGluZyBiZWhhdmlvci5cblx0ICpcblx0ICogVG9nZ2xlcyB2aXNpYmlsaXR5IG9mIENETiBkcml2ZXIgc2VjdGlvbnMgKGJ1aWx0LWluLWNkbiAvIHlvdXItb3duLWNkbilcblx0ICogYmFzZWQgb24gd2hpY2ggdGFiIGlzIGNsaWNrZWQuXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0Q2RuRHJpdmVyVGFicygpIHtcblx0XHRjb25zdCB0YWJzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy53cHItY2RuLXRhYnNfX3RhYicgKTtcblx0XHRjb25zdCBkcml2ZXJTZWN0aW9ucyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcucm9ja2V0Y2RuLCAueW91ci1vd24tY2RuJyApO1xuXG5cdFx0aWYgKCAhIHRhYnMubGVuZ3RoICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIFRvZ2dsZXMgdmlzaWJpbGl0eSBvZiBDRE4gZHJpdmVyIHNlY3Rpb25zIHVzaW5nIHRoZSBoaWRkZW4gdXRpbGl0eSBjbGFzcy5cblx0XHQgKlxuXHRcdCAqIEBwYXJhbSB7c3RyaW5nfSBhY3RpdmVEcml2ZXIgQWN0aXZlIENETiBkcml2ZXIgc2x1Zy5cblx0XHQgKi9cblx0XHRmdW5jdGlvbiB0b2dnbGVEcml2ZXJTZWN0aW9ucyggYWN0aXZlRHJpdmVyICkge1xuXHRcdFx0ZHJpdmVyU2VjdGlvbnMuZm9yRWFjaCggKCBzZWN0aW9uICkgPT4ge1xuXHRcdFx0XHRzZWN0aW9uLmNsYXNzTGlzdC50b2dnbGUoICd3cHItaXNIaWRkZW4nLCAhIHNlY3Rpb24uY2xhc3NMaXN0LmNvbnRhaW5zKCBhY3RpdmVEcml2ZXIgKSApO1xuXHRcdFx0fSApO1xuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIFVwZGF0ZXMgYWxsIC5yb2NrZXRjZG4tZHJpdmVyLWpzIHNwYW5zIHRvIHJlZmxlY3QgdGhlIGFjdGl2ZSBkcml2ZXIgbGFiZWwuXG5cdFx0ICogVGhlIGxhYmVsIGlzIHJlYWQgZnJvbSB0aGUgYWN0aXZlIHRhYidzIGRhdGEtdGl0bGUgYXR0cmlidXRlLCBwcmVzZXJ2aW5nXG5cdFx0ICogdGhlIG9yaWdpbmFsIGNhcGl0YWxpc2F0aW9uIHNldCBieSB0aGUgUEhQIHRyYW5zbGF0aW9uLlxuXHRcdCAqXG5cdFx0ICogQHBhcmFtIHtIVE1MRWxlbWVudH0gYWN0aXZlVGFiIFRoZSBjdXJyZW50bHkgYWN0aXZlIHRhYiBlbGVtZW50LlxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIHVwZGF0ZURyaXZlckxhYmVsKCBhY3RpdmVUYWIgKSB7XG5cdFx0XHRjb25zdCBsYWJlbCA9IGFjdGl2ZVRhYi5nZXRBdHRyaWJ1dGUoICdkYXRhLXRpdGxlJyApO1xuXG5cdFx0XHRpZiAoICEgbGFiZWwgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy5yb2NrZXRjZG4tZHJpdmVyLWpzJyApLmZvckVhY2goICggc3BhbiApID0+IHtcblx0XHRcdFx0Ly8gUHJlc2VydmUgdGhlIG9yaWdpbmFsIHRleHQtdHJhbnNmb3JtICh1cHBlcmNhc2Ugc3BhbnMgc3RheSB1cHBlcmNhc2UgdmlhIENTUykuXG5cdFx0XHRcdHNwYW4udGV4dENvbnRlbnQgPSBsYWJlbDtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBVcGRhdGVzIHRoZSBcIk5lZWQgSGVscD9cIiBsaW5rIGhyZWYgZm9yIHRoZSBDRE4gRXhjbHVzaW9ucyBzZWN0aW9uXG5cdFx0ICogdG8gcG9pbnQgdG8gdGhlIGNvcnJlY3QgZG9jcyBhcnRpY2xlIGZvciB0aGUgYWN0aXZlIGRyaXZlci5cblx0XHQgKlxuXHRcdCAqIEBwYXJhbSB7c3RyaW5nfSBkcml2ZXIgQWN0aXZlIENETiBkcml2ZXIgc2x1ZyAoJ3JvY2tldGNkbicgb3IgJ3lvdXItb3duLWNkbicpLlxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIHVwZGF0ZUV4Y2x1ZGVDZG5IZWxwVXJsKCBkcml2ZXIgKSB7XG5cdFx0XHRjb25zdCBsaW5rID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy5leGNsdWRlLWNkbi1oZWxwLWpzJyApO1xuXHRcdFx0aWYgKCAhIGxpbmsgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdGNvbnN0IGlzUm9ja2V0Q2RuID0gJ3JvY2tldGNkbicgPT09IGRyaXZlcjtcblx0XHRcdGNvbnN0IHVybCA9IGlzUm9ja2V0Q2RuID8gbGluay5kYXRhc2V0LnJvY2tldGNkblVybCA6IGxpbmsuZGF0YXNldC5vdGhlckNkblVybDtcblx0XHRcdGNvbnN0IGlkICA9IGlzUm9ja2V0Q2RuID8gbGluay5kYXRhc2V0LnJvY2tldGNkbklkICA6IGxpbmsuZGF0YXNldC5vdGhlckNkbklkO1xuXHRcdFx0aWYgKCB1cmwgKSB7XG5cdFx0XHRcdGxpbmsuaHJlZiA9IHVybDtcblx0XHRcdH1cblx0XHRcdGlmICggaWQgKSB7XG5cdFx0XHRcdGxpbmsuZGF0YXNldC5iZWFjb25JZCA9IGlkO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdHRhYnMuZm9yRWFjaCggKCB0YWIgKSA9PiB7XG5cdFx0XHR0YWIuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCkgPT4ge1xuXHRcdFx0XHRjb25zdCBkcml2ZXIgPSB0YWIuZ2V0QXR0cmlidXRlKCAnZGF0YS1jZG4tZHJpdmVyJyApO1xuXG5cdFx0XHRcdGlmICggISBkcml2ZXIgKSB7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVXBkYXRlIGFjdGl2ZSB0YWIuXG5cdFx0XHRcdHRhYnMuZm9yRWFjaCggKCB0ICkgPT4gdC5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi10YWJzX190YWItLWFjdGl2ZScgKSApO1xuXHRcdFx0XHR0YWIuY2xhc3NMaXN0LmFkZCggJ3dwci1jZG4tdGFic19fdGFiLS1hY3RpdmUnICk7XG5cblx0XHRcdFx0Ly8gVG9nZ2xlIHNlY3Rpb25zOiBzaG93IG1hdGNoaW5nIGRyaXZlciwgaGlkZSBvdGhlcnMuXG5cdFx0XHRcdHRvZ2dsZURyaXZlclNlY3Rpb25zKCBkcml2ZXIgKTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgZHluYW1pYyBkcml2ZXIgbGFiZWwgc3BhbnMuXG5cdFx0XHRcdHVwZGF0ZURyaXZlckxhYmVsKCB0YWIgKTtcblx0XHRcdFx0dXBkYXRlRXhjbHVkZUNkbkhlbHBVcmwoIGRyaXZlciApO1xuXHRcdFx0XHRub3RpZnlDZG5TdGF0ZUNoYW5nZSgpO1xuXG5cdFx0XHRcdC8vIEluaXRpYWwgdmFsdWUgb2YgdGhlIGhpZGRlbiBpbnB1dCBpcyBzZXQgb24gcGFnZSBsb2FkIGJ5IFBIUCBiYXNlZCBvbiB0aGUgYWN0aXZlIGRyaXZlci5cblx0XHRcdFx0Y29uc3QgY2RuVHlwZUlucHV0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2Nkbl90eXBlJyk7XG5cdFx0XHRcdGxldCBjdXJyZW50VmFsdWUgPSBjZG5UeXBlSW5wdXQudmFsdWU7XG5cblx0XHRcdFx0Ly8gUGVyc2lzdCB0aGUgYWN0aXZlIGRyaXZlciBzZWxlY3Rpb24uXG5cdFx0XHRcdGNvbnN0IGRyaXZlclZhbHVlID0gJ3lvdXItb3duLWNkbicgPT09IGRyaXZlciA/ICdieW9jZG4nIDogJ3JvY2tldGNkbic7XG5cblx0XHRcdFx0d2luZG93LndwLmFwaUZldGNoKCB7XG5cdFx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0Y2RuL2RyaXZlcicsXG5cdFx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdFx0ZGF0YTogeyBkcml2ZXI6IGRyaXZlclZhbHVlIH0sXG5cdFx0XHRcdH0gKS50aGVuKChyZXNwb25zZSkgPT4ge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZWQgaGlkZGVuIGlucHV0IHZhbHVlIG9uIHN1Y2Nlc3MuXG5cdFx0XHRcdFx0Y2RuVHlwZUlucHV0LnZhbHVlID0gZHJpdmVyVmFsdWU7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBzdGF0ZSBvZiBSb2NrZXRDRE4gc3BlY2lmaWMgZWxlbWVudHMgYmFzZWQgb24gdGhlIHNlbGVjdGVkIGRyaXZlciBhbmQgcmVzcG9uc2UgZnJvbSB0aGUgc2VydmVyLlxuXHRcdFx0XHRcdHVwZGF0ZVJvY2tldENETkVsZW1lbnRzU3RhdGUoIGRyaXZlclZhbHVlLCByZXNwb25zZS5kaXNhYmxlX3JvY2tldF9jZG5fZWxlbWVudHMgKTtcblx0XHRcdFx0fSApLmNhdGNoKCgpID0+IHtcblx0XHRcdFx0XHQvLyBSZXZlcnQgYWN0aXZlIHRhYiBhbmQgc2VjdGlvbnMgb24gZmFpbHVyZS5cblx0XHRcdFx0XHRjZG5UeXBlSW5wdXQudmFsdWUgPSBjdXJyZW50VmFsdWU7XG5cdFx0XHRcdH0gKTtcblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cblx0XHQvLyBTZXQgaW5pdGlhbCBzdGF0ZSBmcm9tIGFjdGl2ZSB0YWIsIGZhbGxiYWNrIHRvIHJvY2tldGNkbi5cblx0XHRjb25zdCBhY3RpdmVUYWIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tdGFic19fdGFiLS1hY3RpdmUnICk7XG5cdFx0Y29uc3QgYWN0aXZlRHJpdmVyID0gYWN0aXZlVGFiID8gYWN0aXZlVGFiLmdldEF0dHJpYnV0ZSggJ2RhdGEtY2RuLWRyaXZlcicgKSA6ICdyb2NrZXRjZG4nO1xuXG5cdFx0aWYgKCBhY3RpdmVEcml2ZXIgKSB7XG5cdFx0XHR0b2dnbGVEcml2ZXJTZWN0aW9ucyggYWN0aXZlRHJpdmVyICk7XG5cdFx0XHRub3RpZnlDZG5TdGF0ZUNoYW5nZSgpO1xuXHRcdH1cblxuXHRcdC8vIFNldCBpbml0aWFsIGxhYmVsIGZyb20gdGhlIGFjdGl2ZSB0YWIuXG5cdFx0aWYgKCBhY3RpdmVUYWIgKSB7XG5cdFx0XHR1cGRhdGVEcml2ZXJMYWJlbCggYWN0aXZlVGFiICk7XG5cdFx0XHR1cGRhdGVFeGNsdWRlQ2RuSGVscFVybCggYWN0aXZlRHJpdmVyICk7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIHRoZSBDRE4gcGF1c2UvcmVzdW1lIHRvZ2dsZSBidXR0b25zLlxuXHQgKlxuXHQgKiBUb2dnbGVzIGJldHdlZW4gXCJQQVVTRSBDRE5cIiBhbmQgXCJSRVNVTUUgQ0ROXCIgc3RhdGVzLFxuXHQgKiBzd2FwcGluZyB0aGUgaWNvbiB2aWEgYSBDU1MgbW9kaWZpZXIgY2xhc3MuXG5cdCAqL1xuXHRmdW5jdGlvbiBpbml0Q2RuUGF1c2VUb2dnbGUoKSB7XG5cdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCBldmVudCApID0+IHtcblx0XHRcdGNvbnN0IGJ1dHRvbiA9IGV2ZW50LnRhcmdldC5jbG9zZXN0KCAnLndwci1jZG4tcGF1c2UnICk7XG5cdFx0XHRpZiAoICEgYnV0dG9uICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IGlzUGF1c2VkID0gYnV0dG9uLmNsYXNzTGlzdC50b2dnbGUoICd3cHItY2RuLXBhdXNlLS1wYXVzZWQnICk7XG5cdFx0XHRidXR0b24uc2V0QXR0cmlidXRlKCAnYXJpYS1wcmVzc2VkJywgaXNQYXVzZWQgPyAndHJ1ZScgOiAnZmFsc2UnICk7XG5cdFx0XHRidXR0b24uZGlzYWJsZWQgPSB0cnVlO1xuXG5cdFx0XHRjb25zdCBzdGF0dXNEb3QgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLnJvY2tldGNkbiAud3ByLWNkbi1pbmRpY2F0b3JfX2RvdCcgKTtcblx0XHRcdGlmICggc3RhdHVzRG90ICkge1xuXHRcdFx0XHRzdGF0dXNEb3QuY2xhc3NOYW1lID0gJ3dwci1pY29uLW9yYW5nZS1sb2FkZXInO1xuXHRcdFx0fVxuXG5cdFx0XHR3aW5kb3cud3AuYXBpRmV0Y2goIHtcblx0XHRcdFx0cGF0aDogJy93cC1yb2NrZXQvdjEvcm9ja2V0Y2RuL3BhdXNlJyxcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGRhdGE6IHsgcGF1c2VkOiBpc1BhdXNlZCA/IDAgOiAxIH0sXG5cdFx0XHR9ICkudGhlbiggKCkgPT4ge1xuXHRcdFx0XHQvLyBSZW1vdmUgdGhlIGxvYWRlci5cblx0XHRcdFx0aWYgKCBzdGF0dXNEb3QgKSB7XG5cdFx0XHRcdFx0c3RhdHVzRG90LmNsYXNzTmFtZSA9ICd3cHItY2RuLWluZGljYXRvcl9fZG90Jztcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuXG5cdFx0XHRcdC8vIFNpbXVsYXRlIHJlYWwgY2xpY2sgdG8gcHJlcGFyZSBjaGVja2JveCBzdGF0ZSBmb3IgZm9ybSBzdWJtaXNzaW9uLlxuXHRcdFx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdsYWJlbFtmb3I9XCJjZG5cIl0nKS5jbGljaygpO1xuXG5cdFx0XHRcdHVwZGF0ZVJvY2tldENETkVsZW1lbnRzU3RhdGUoICdyb2NrZXRjZG4nLCBpc1BhdXNlZCApO1xuXG5cdFx0XHRcdGNvbnN0IHN0YXR1c0NvbnRhaW5lciA9IGJ1dHRvbi5jbG9zZXN0KCAnLndwci1jZG4tc3RhdHVzJyApO1xuXHRcdFx0XHRpZiAoICEgc3RhdHVzQ29udGFpbmVyICkge1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHN0YXR1c0NvbnRhaW5lci5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLWNkbi1zdGF0dXMtLXBhdXNlZCcsIGlzUGF1c2VkICk7XG5cdFx0XHRcdHN0YXR1c0NvbnRhaW5lci5jbGFzc0xpc3QudG9nZ2xlKFxuXHRcdFx0XHRcdCd3cHItY2RuLXN0YXR1cy0tbG9uZy1kZXRhaWxzJyxcblx0XHRcdFx0XHRpc1BhdXNlZCAmJiAnMScgPT09IHN0YXR1c0NvbnRhaW5lci5kYXRhc2V0LmxvbmdEZXRhaWxzXG5cdFx0XHRcdCk7XG5cblx0XHRcdFx0Y29uc3QgYnVpbHRJbiA9IHN0YXR1c0NvbnRhaW5lci5jbG9zZXN0KCAnLndwci1jZG4tYnVpbHQtaW4nICk7XG5cdFx0XHRcdGlmICggYnVpbHRJbiApIHtcblx0XHRcdFx0XHRidWlsdEluLmNsYXNzTGlzdC50b2dnbGUoICd3cHItY2RuLWJ1aWx0LWluLS1wYXVzZWQnLCBpc1BhdXNlZCApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0bm90aWZ5Q2RuU3RhdGVDaGFuZ2UoKTtcblxuXHRcdFx0XHRjb25zdCB0ZXh0S2V5ID0gaXNQYXVzZWQgPyAncGF1c2VkVGV4dCcgOiAnYWN0aXZlVGV4dCc7XG5cblx0XHRcdFx0Y29uc3Qgc3RhdHVzVGV4dCA9IHN0YXR1c0NvbnRhaW5lci5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4taW5kaWNhdG9yX190ZXh0JyApO1xuXG5cdFx0XHRcdGlmICggc3RhdHVzVGV4dCAmJiBzdGF0dXNDb250YWluZXIuZGF0YXNldFsgdGV4dEtleSBdICkge1xuXHRcdFx0XHRcdHN0YXR1c1RleHQudGV4dENvbnRlbnQgPSBzdGF0dXNDb250YWluZXIuZGF0YXNldFsgdGV4dEtleSBdO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Y29uc3QgZGV0YWlsc0tleSA9IGlzUGF1c2VkID8gJ3BhdXNlZERldGFpbHMnIDogJ2FjdGl2ZURldGFpbHMnO1xuXHRcdFx0XHRjb25zdCBkZXRhaWxzRWwgPSBzdGF0dXNDb250YWluZXIucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWluZGljYXRvcl9fZGV0YWlscycgKTtcblxuXHRcdFx0XHRpZiAoIGRldGFpbHNFbCAmJiBzdGF0dXNDb250YWluZXIuZGF0YXNldFsgZGV0YWlsc0tleSBdICkge1xuXHRcdFx0XHRcdGRldGFpbHNFbC50ZXh0Q29udGVudCA9IHN0YXR1c0NvbnRhaW5lci5kYXRhc2V0WyBkZXRhaWxzS2V5IF07XG5cdFx0XHRcdH1cblx0XHRcdH0gKS5jYXRjaCggKCkgPT4ge1xuXHRcdFx0XHQvLyBSZXZlcnQgdG9nZ2xlIG9uIGZhaWx1cmUuXG5cdFx0XHRcdGJ1dHRvbi5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLWNkbi1wYXVzZS0tcGF1c2VkJywgISBpc1BhdXNlZCApO1xuXHRcdFx0XHRidXR0b24uc2V0QXR0cmlidXRlKCAnYXJpYS1wcmVzc2VkJywgISBpc1BhdXNlZCA/ICd0cnVlJyA6ICdmYWxzZScgKTtcblx0XHRcdFx0YnV0dG9uLmRpc2FibGVkID0gZmFsc2U7XG5cblx0XHRcdFx0Ly8gUmVtb3ZlIHRoZSBsb2FkZXIuXG5cdFx0XHRcdGlmICggc3RhdHVzRG90ICkge1xuXHRcdFx0XHRcdHN0YXR1c0RvdC5jbGFzc05hbWUgPSAnd3ByLWNkbi1pbmRpY2F0b3JfX2RvdCc7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cdH1cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIHRoZSBcIkFERCBIT01FUEFHRVwiIGJ1dHRvbi5cblx0ICpcblx0ICogU2VuZHMgYSBQT1NUIHJlcXVlc3QgdG8gdGhlIFJvY2tldENETiBSRVNUIGVuZHBvaW50IHRvIGFkZFxuXHQgKiB0aGUgc2l0ZSBob21lcGFnZSBhcyBhIGZyZWUtdGllciBDRE4gcGFnZS5cblx0ICovXG5cdGZ1bmN0aW9uIGluaXRBZGRIb21lcGFnZSgpIHtcblx0XHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGV2ZW50ICkgPT4ge1xuXHRcdFx0Y29uc3QgYnV0dG9uID0gZXZlbnQudGFyZ2V0LmNsb3Nlc3QoICcjd3ByX2FkZF9wYWdlX2NvbXBvbmVudCAud3ByLWNkbi1hZGQtcGFnZV9faG9tZXBhZ2UnICk7XG5cdFx0XHRpZiAoICEgYnV0dG9uICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cblx0XHRcdGNvbnN0IGJ1aWx0SW4gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYnVpbHQtaW4nICk7XG5cblx0XHRcdGlmICggYnVpbHRJbiApIHtcblx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QuYWRkKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHR9XG5cblx0XHRcdHdpbmRvdy53cC5hcGlGZXRjaCgge1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXRjZG4vcGFnZXMvaG9tZXBhZ2UnLFxuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdH0gKS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0XHRidXR0b24uY2xhc3NMaXN0LmFkZCggJ3dwci1pc0hpZGRlbicgKTtcblx0XHRcdFx0dXBkYXRlUm9ja2V0Q3RhU3RhdGUoIHJlc3BvbnNlLmNvdW50LCByZXNwb25zZS5saW1pdCApO1xuXG5cdFx0XHRcdGlmICggYnVpbHRJbiApIHtcblx0XHRcdFx0XHRidWlsdEluLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcgKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGlmICggcmVzcG9uc2UuaXRlbXNfaHRtbCApIHtcblx0XHRcdFx0XHRjb25zdCBleGlzdGluZyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbiAud3ByLXRhYmxlLWxpc3QnICk7XG5cblx0XHRcdFx0XHRpZiAoIGV4aXN0aW5nICkge1xuXHRcdFx0XHRcdFx0ZXhpc3RpbmcucmVtb3ZlKCk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVNlY3Rpb24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2UnICk7XG5cblx0XHRcdFx0XHRpZiAoIGFkZFBhZ2VTZWN0aW9uICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZVNlY3Rpb24uaW5zZXJ0QWRqYWNlbnRIVE1MKCAnYmVmb3JlYmVnaW4nLCByZXNwb25zZS5pdGVtc19odG1sICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVHJhY2sgYmFubmVyIHZpZXcgd2hlbiBmaXJzdCBwYWdlIGlzIGFkZGVkIGFuZCBiYW5uZXIgYmVjb21lcyB2aXNpYmxlLlxuXHRcdFx0XHRpZiAoIDEgPT09IHJlc3BvbnNlLmNvdW50ICkge1xuXHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQoIG5ldyBDdXN0b21FdmVudCggJ3JvY2tldENETkJhbm5lckZpcnN0VmlzaWJsZScgKSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gU2V0IHN1YnNjcmlwdGlvbiBsb2FkaW5nIHN0YXRlIHdoZW4gZmlyc3QgcGFnZSBpcyBhZGRlZC5cblx0XHRcdFx0aWYgKCByZXNwb25zZS5pc19zdWJzY3JpcHRpb25fY3JlYXRpb25fbG9hZGluZyApIHtcblx0XHRcdFx0XHRzZXRTdWJzY3JpcHRpb25Mb2FkaW5nU3RhdGUoKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFVwZGF0ZSBzdGF0dXMgaW5kaWNhdG9yIGNvbXBvbmVudC5cblx0XHRcdFx0dXBkYXRlU3RhdHVzSW5kaWNhdG9yQ29tcG9uZW50KCByZXNwb25zZS5zdGF0dXNfaW5kaWNhdG9yX2h0bWwgKTtcblx0XHRcdH0gKS5jYXRjaCggKCkgPT4ge1xuXHRcdFx0XHRidXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcblxuXHRcdFx0XHRpZiAoIGJ1aWx0SW4gKSB7XG5cdFx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cdH1cblx0LyoqXG5cdCAqIEluaXRpYWxpemVzIHRoZSBcIkFERCBQQUdFXCIgaW5wdXQgYW5kIGJ1dHRvbi5cblx0ICpcblx0ICogU2VuZHMgYSBQT1NUIHJlcXVlc3QgdG8gdGhlIFJvY2tldENETiBSRVNUIGVuZHBvaW50IHRvIGFkZFxuXHQgKiBhIHBhZ2UgVVJMIHRvIHRoZSBmcmVlLXRpZXIgQ0ROIHBhZ2UgbGlzdC5cblx0ICovXG5cdGZ1bmN0aW9uIGluaXRBZGRQYWdlKCkge1xuXHRcdGNvbnN0IGlucHV0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICd3cHJfY2RuX2FkZF9wYWdlX2lucHV0JyApO1xuXHRcdGNvbnN0IGJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1hZGQtcGFnZV9fYnV0dG9uJyApO1xuXG5cdFx0aWYgKCAhIGlucHV0IHx8ICEgYnV0dG9uICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGZ1bmN0aW9uIGlzVmFsaWRVcmwoaW5wdXQpIHtcblx0XHRcdHRyeSB7XG5cdFx0XHRcdGNvbnN0IHVybCA9IG5ldyBVUkwoaW5wdXQpO1xuXHRcdFx0XHRyZXR1cm4gdXJsLmhvc3RuYW1lLmluY2x1ZGVzKCcuJykgJiYgdXJsLmhvc3RuYW1lLnNwbGl0KCcuJykucG9wKCkubGVuZ3RoID4gMDtcblx0XHRcdH0gY2F0Y2gge1xuXHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0ZnVuY3Rpb24gc3VibWl0UGFnZSgpIHtcblx0XHRcdGNvbnN0IHVybCA9IGlucHV0LnZhbHVlLnRyaW0oKTtcblxuXHRcdFx0aWYgKCFpc1ZhbGlkVXJsKHVybCkpIHtcblx0XHRcdFx0YWxlcnQoJ1BsZWFzZSBlbnRlciBhIHZhbGlkIFVSTCcpO1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFByZXZlbnQgZHVwbGljYXRlIHJlcXVlc3Qgd2hpbGUgcmVxdWVzdCBpcyBpbiBmbGlnaHQuXG5cdFx0XHRpbnB1dC5kaXNhYmxlZCA9IHRydWU7XG5cdFx0XHRidXR0b24uZGlzYWJsZWQgPSB0cnVlO1xuXHRcdFx0Y29uc3QgYnVpbHRJbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKTtcblxuXHRcdFx0aWYgKCBidWlsdEluICkge1xuXHRcdFx0XHRidWlsdEluLmNsYXNzTGlzdC5hZGQoICd3cHItY2RuLWJ1aWx0LWluLS1kaXNhYmxlZCcgKTtcblx0XHRcdH1cblxuXHRcdFx0d2luZG93LndwLmFwaUZldGNoKCB7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldGNkbi9wYWdlcycsXG5cdFx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0XHRkYXRhOiB7IHVybCB9LFxuXHRcdFx0fSApLnRoZW4oICggcmVzcG9uc2UgKSA9PiB7XG5cdFx0XHRcdGlucHV0LnZhbHVlID0gJyc7XG5cdFx0XHRcdGlucHV0LmRpc2FibGVkID0gZmFsc2U7XG5cdFx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0XHRhZGRIb21lQnV0dG9uLmNsYXNzTGlzdC5hZGQoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdHVwZGF0ZVJvY2tldEN0YVN0YXRlKCByZXNwb25zZS5jb3VudCwgcmVzcG9uc2UubGltaXQgKTtcblxuXHRcdFx0XHRpZiAoIGJ1aWx0SW4gKSB7XG5cdFx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBVcGRhdGUgcGFnZSBsaXN0IHdpdGggcmVzcG9uc2UuXG5cdFx0XHRcdGlmICggcmVzcG9uc2UuaXRlbXNfaHRtbCApIHtcblx0XHRcdFx0XHRjb25zdCBleGlzdGluZyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbiAud3ByLXRhYmxlLWxpc3QnICk7XG5cblx0XHRcdFx0XHRpZiAoIGV4aXN0aW5nICkge1xuXHRcdFx0XHRcdFx0ZXhpc3RpbmcucmVtb3ZlKCk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVNlY3Rpb24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2UnICk7XG5cblx0XHRcdFx0XHRpZiAoIGFkZFBhZ2VTZWN0aW9uICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZVNlY3Rpb24uaW5zZXJ0QWRqYWNlbnRIVE1MKCAnYmVmb3JlYmVnaW4nLCByZXNwb25zZS5pdGVtc19odG1sICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVHJhY2sgYmFubmVyIHZpZXcgd2hlbiBmaXJzdCBwYWdlIGlzIGFkZGVkIGFuZCBiYW5uZXIgYmVjb21lcyB2aXNpYmxlLlxuXHRcdFx0XHRpZiAoIDEgPT09IHJlc3BvbnNlLmNvdW50ICkge1xuXHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQoIG5ldyBDdXN0b21FdmVudCggJ3JvY2tldENETkJhbm5lckZpcnN0VmlzaWJsZScgKSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0aWYgKCByZXNwb25zZS5saW1pdCA9PT0gcmVzcG9uc2UuY291bnQgKSB7XG5cdFx0XHRcdFx0Ly8gRGlzYWJsZSBpbnB1dCBhbmQgYnV0dG9uIHdoZW4gcGFnZSBsaW1pdCBpcyByZWFjaGVkLlxuXHRcdFx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKS5jbGFzc0xpc3QuYWRkKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVdyYXBwZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2VfX2J1dHRvbi13cmFwcGVyJyApO1xuXHRcdFx0XHRcdGlmICggYWRkUGFnZVdyYXBwZXIgKSB7XG5cdFx0XHRcdFx0XHRhZGRQYWdlV3JhcHBlci5jbGFzc0xpc3QuYWRkKCAnd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRjb25zdCBhZGRQYWdlQnRuID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19idXR0b24nICk7XG5cdFx0XHRcdFx0aWYgKCBhZGRQYWdlQnRuICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZUJ0bi5kaXNhYmxlZCA9IHRydWU7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHVwZGF0ZVRvb2x0aXBTdGF0ZSggdHJ1ZSApO1xuXHRcdFx0XHRcdGRvY3VtZW50LmRpc3BhdGNoRXZlbnQoIG5ldyBDdXN0b21FdmVudCggJ3JvY2tldENETkJhbm5lckF1dG9FeHBhbmRlZCcgKSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gU2V0IHN1YnNjcmlwdGlvbiBsb2FkaW5nIHN0YXRlIHdoZW4gZmlyc3QgcGFnZSBpcyBhZGRlZC5cblx0XHRcdFx0aWYgKCByZXNwb25zZS5pc19zdWJzY3JpcHRpb25fY3JlYXRpb25fbG9hZGluZyApIHtcblx0XHRcdFx0XHRzZXRTdWJzY3JpcHRpb25Mb2FkaW5nU3RhdGUoKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFVwZGF0ZSBzdGF0dXMgaW5kaWNhdG9yIGNvbXBvbmVudC5cblx0XHRcdFx0dXBkYXRlU3RhdHVzSW5kaWNhdG9yQ29tcG9uZW50KCByZXNwb25zZS5zdGF0dXNfaW5kaWNhdG9yX2h0bWwgKTtcblx0XHRcdH0gKS5jYXRjaCggKCkgPT4ge1xuXHRcdFx0XHRpbnB1dC5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0XHRidXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcblxuXHRcdFx0XHRpZiAoIGJ1aWx0SW4gKSB7XG5cdFx0XHRcdFx0YnVpbHRJbi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHRidXR0b24uYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgc3VibWl0UGFnZSApO1xuXG5cdFx0aW5wdXQuYWRkRXZlbnRMaXN0ZW5lciggJ2tleWRvd24nLCAoIGUgKSA9PiB7XG5cdFx0XHRpZiAoICdFbnRlcicgPT09IGUua2V5ICkge1xuXHRcdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHRcdHN1Ym1pdFBhZ2UoKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZXMgZGVsZXRlIGJ1dHRvbnMgZm9yIENETiBwYWdlIHJvd3MuXG5cdCAqXG5cdCAqIFVzZXMgZXZlbnQgZGVsZWdhdGlvbiBvbiB0aGUgYnVpbHQtaW4gQ0ROIGNvbnRhaW5lciB0byBoYW5kbGVcblx0ICogY2xpY2tzIG9uIGR5bmFtaWNhbGx5IGFkZGVkIGRlbGV0ZSBidXR0b25zLlxuXHQgKi9cblx0ZnVuY3Rpb24gaW5pdERlbGV0ZVBhZ2UoKSB7XG5cdFx0Y29uc3QgY29udGFpbmVyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHJfYWRkX3BhZ2VfY29tcG9uZW50JyApO1xuXG5cdFx0aWYgKCAhIGNvbnRhaW5lciApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb250YWluZXIucGFyZW50RWxlbWVudC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRjb25zdCBidXR0b24gPSBlLnRhcmdldC5jbG9zZXN0KCAnLndwci10YWJsZS1saXN0X19kZWxldGUnICk7XG5cblx0XHRcdGlmICggISBidXR0b24gKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgaWQgPSBidXR0b24uZGF0YXNldC5pZDtcblxuXHRcdFx0aWYgKCAhIGlkICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cblx0XHRcdHdpbmRvdy53cC5hcGlGZXRjaCgge1xuXHRcdFx0XHRwYXRoOiBgL3dwLXJvY2tldC92MS9yb2NrZXRjZG4vcGFnZXMvJHsgaWQgfWAsXG5cdFx0XHRcdG1ldGhvZDogJ0RFTEVURScsXG5cdFx0XHR9ICkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdFx0dXBkYXRlUm9ja2V0Q3RhU3RhdGUoIHJlc3BvbnNlLmNvdW50LCByZXNwb25zZS5saW1pdCApO1xuXG5cdFx0XHRcdGlmICggcmVzcG9uc2UuaXRlbXNfaHRtbCApIHtcblx0XHRcdFx0XHRjb25zdCBleGlzdGluZyA9IGNvbnRhaW5lci5wYXJlbnRFbGVtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbiAud3ByLXRhYmxlLWxpc3QnICk7XG5cblx0XHRcdFx0XHRpZiAoIGV4aXN0aW5nICkge1xuXHRcdFx0XHRcdFx0ZXhpc3RpbmcucmVtb3ZlKCk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVNlY3Rpb24gPSBjb250YWluZXIucGFyZW50RWxlbWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2UnICk7XG5cblx0XHRcdFx0XHRpZiAoIGFkZFBhZ2VTZWN0aW9uICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZVNlY3Rpb24uaW5zZXJ0QWRqYWNlbnRIVE1MKCAnYmVmb3JlYmVnaW4nLCByZXNwb25zZS5pdGVtc19odG1sICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gU2hvdyByZS1hZGQgSE9NRVBBR0UgYnV0dG9uIHdoZW4gYWxsIHBhZ2VzIGFyZSBkZWxldGVkLlxuXHRcdFx0XHRpZiAoIDAgPT09IHJlc3BvbnNlLmNvdW50ICkge1xuXHRcdFx0XHRcdC8vIFJlbW92ZSB0YWJsZSBsaXN0IGNvbXBvbmVudC5cblx0XHRcdFx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYnVpbHQtaW4gLndwci10YWJsZS1saXN0JyApLnJlbW92ZSgpO1xuXG5cdFx0XHRcdFx0Y29uc3QgaG9tZXBhZ2VCdG4gPSBjb250YWluZXIucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19ob21lcGFnZScgKTtcblxuXHRcdFx0XHRcdGlmICggaG9tZXBhZ2VCdG4gKSB7XG5cdFx0XHRcdFx0XHRob21lcGFnZUJ0bi5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWlzSGlkZGVuJyApO1xuXHRcdFx0XHRcdFx0aG9tZXBhZ2VCdG4uZGlzYWJsZWQgPSBmYWxzZTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLmxpbWl0ID4gcmVzcG9uc2UuY291bnQgKSB7XG5cdFx0XHRcdFx0Ly8gUmUtZW5hYmxlIGlucHV0IGFuZCBidXR0b24gd2hlbiBwYWdlIGxpbWl0IGlzIG5vdCByZWFjaGVkLlxuXHRcdFx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbicgKS5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnICk7XG5cdFx0XHRcdFx0Y29uc3QgYWRkUGFnZVdyYXBwZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwci1jZG4tYWRkLXBhZ2VfX2J1dHRvbi13cmFwcGVyJyApO1xuXHRcdFx0XHRcdGlmICggYWRkUGFnZVdyYXBwZXIgKSB7XG5cdFx0XHRcdFx0XHRhZGRQYWdlV3JhcHBlci5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRjb25zdCBhZGRQYWdlQnRuID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLWFkZC1wYWdlX19idXR0b24nICk7XG5cdFx0XHRcdFx0aWYgKCBhZGRQYWdlQnRuICkge1xuXHRcdFx0XHRcdFx0YWRkUGFnZUJ0bi5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHR1cGRhdGVUb29sdGlwU3RhdGUoIGZhbHNlICk7XG5cblx0XHRcdFx0XHQvLyBUcmFjayBhdXRvLWNvbGxhcHNlIHdoZW4gZGVsZXRpb24gZHJvcHMgY291bnQganVzdCBiZWxvdyB0aGUgbGltaXQuXG5cdFx0XHRcdFx0aWYgKCByZXNwb25zZS5jb3VudCA9PT0gcmVzcG9uc2UubGltaXQgLSAxICkge1xuXHRcdFx0XHRcdFx0ZG9jdW1lbnQuZGlzcGF0Y2hFdmVudCggbmV3IEN1c3RvbUV2ZW50KCAncm9ja2V0Q0ROQmFubmVyQXV0b0NvbGxhcHNlZCcgKSApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXG5cdFx0XHRcdGlmICggMCA9PT0gcmVzcG9uc2UuY291bnQgKSB7XG5cdFx0XHRcdFx0Ly8gVXBkYXRlIHN0YXR1cyBpbmlkaWNhdG9yIGNvbXBvbmVudFxuXHRcdFx0XHRcdHVwZGF0ZVN0YXR1c0luZGljYXRvckNvbXBvbmVudCggcmVzcG9uc2Uuc3RhdHVzX2luZGljYXRvcl9odG1sICk7XG5cdFx0XHRcdH1cblxuXHRcdFx0fSApLmNhdGNoKCAoKSA9PiB7XG5cdFx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0fSApO1xuXHRcdH0gKTtcblx0fVxufSApKCBkb2N1bWVudCApO1xuIiwiZnVuY3Rpb24gZ2V0VGltZVJlbWFpbmluZyhlbmR0aW1lKXtcbiAgICBjb25zdCBzdGFydCA9IERhdGUubm93KCk7XG4gICAgY29uc3QgdG90YWwgPSAoZW5kdGltZSAqIDEwMDApIC0gc3RhcnQ7XG4gICAgY29uc3Qgc2Vjb25kcyA9IE1hdGguZmxvb3IoICh0b3RhbC8xMDAwKSAlIDYwICk7XG4gICAgY29uc3QgbWludXRlcyA9IE1hdGguZmxvb3IoICh0b3RhbC8xMDAwLzYwKSAlIDYwICk7XG4gICAgY29uc3QgaG91cnMgPSBNYXRoLmZsb29yKCAodG90YWwvKDEwMDAqNjAqNjApKSAlIDI0ICk7XG4gICAgY29uc3QgZGF5cyA9IE1hdGguZmxvb3IoIHRvdGFsLygxMDAwKjYwKjYwKjI0KSApO1xuXG4gICAgcmV0dXJuIHtcbiAgICAgICAgdG90YWwsXG4gICAgICAgIGRheXMsXG4gICAgICAgIGhvdXJzLFxuICAgICAgICBtaW51dGVzLFxuICAgICAgICBzZWNvbmRzXG4gICAgfTtcbn1cblxuZnVuY3Rpb24gaW5pdGlhbGl6ZUNsb2NrKGlkLCBlbmR0aW1lKSB7XG4gICAgY29uc3QgY2xvY2sgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG5cbiAgICBpZiAoY2xvY2sgPT09IG51bGwpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGNvbnN0IGRheXNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tZGF5cycpO1xuICAgIGNvbnN0IGhvdXJzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLWhvdXJzJyk7XG4gICAgY29uc3QgbWludXRlc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1taW51dGVzJyk7XG4gICAgY29uc3Qgc2Vjb25kc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1zZWNvbmRzJyk7XG5cbiAgICBmdW5jdGlvbiB1cGRhdGVDbG9jaygpIHtcbiAgICAgICAgY29uc3QgdCA9IGdldFRpbWVSZW1haW5pbmcoZW5kdGltZSk7XG5cbiAgICAgICAgaWYgKHQudG90YWwgPCAwKSB7XG4gICAgICAgICAgICBjbGVhckludGVydmFsKHRpbWVpbnRlcnZhbCk7XG5cbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGRheXNTcGFuLmlubmVySFRNTCA9IHQuZGF5cztcbiAgICAgICAgaG91cnNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0LmhvdXJzKS5zbGljZSgtMik7XG4gICAgICAgIG1pbnV0ZXNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0Lm1pbnV0ZXMpLnNsaWNlKC0yKTtcbiAgICAgICAgc2Vjb25kc1NwYW4uaW5uZXJIVE1MID0gKCcwJyArIHQuc2Vjb25kcykuc2xpY2UoLTIpO1xuICAgIH1cblxuICAgIHVwZGF0ZUNsb2NrKCk7XG4gICAgY29uc3QgdGltZWludGVydmFsID0gc2V0SW50ZXJ2YWwodXBkYXRlQ2xvY2ssIDEwMDApO1xufVxuXG5mdW5jdGlvbiBydWNzc1RpbWVyKGlkLCBlbmR0aW1lKSB7XG5cdGNvbnN0IHRpbWVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpO1xuXHRjb25zdCBub3RpY2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1zYWFzLXByb2Nlc3NpbmcnKTtcblx0Y29uc3Qgc3VjY2VzcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyb2NrZXQtbm90aWNlLXNhYXMtc3VjY2VzcycpO1xuXG5cdGlmICh0aW1lciA9PT0gbnVsbCkge1xuXHRcdHJldHVybjtcblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZVRpbWVyKCkge1xuXHRcdGNvbnN0IHN0YXJ0ID0gRGF0ZS5ub3coKTtcblx0XHRjb25zdCByZW1haW5pbmcgPSBNYXRoLmZsb29yKCAoIChlbmR0aW1lICogMTAwMCkgLSBzdGFydCApIC8gMTAwMCApO1xuXG5cdFx0aWYgKHJlbWFpbmluZyA8PSAwKSB7XG5cdFx0XHRjbGVhckludGVydmFsKHRpbWVySW50ZXJ2YWwpO1xuXG5cdFx0XHRpZiAobm90aWNlICE9PSBudWxsKSB7XG5cdFx0XHRcdG5vdGljZS5jbGFzc0xpc3QuYWRkKCdoaWRkZW4nKTtcblx0XHRcdH1cblxuXHRcdFx0aWYgKHN1Y2Nlc3MgIT09IG51bGwpIHtcblx0XHRcdFx0c3VjY2Vzcy5jbGFzc0xpc3QucmVtb3ZlKCdoaWRkZW4nKTtcblx0XHRcdH1cblxuXHRcdFx0aWYgKCByb2NrZXRfYWpheF9kYXRhLmNyb25fZGlzYWJsZWQgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgZGF0YSA9IG5ldyBGb3JtRGF0YSgpO1xuXG5cdFx0XHRkYXRhLmFwcGVuZCggJ2FjdGlvbicsICdyb2NrZXRfc3Bhd25fY3JvbicgKTtcblx0XHRcdGRhdGEuYXBwZW5kKCAnbm9uY2UnLCByb2NrZXRfYWpheF9kYXRhLm5vbmNlICk7XG5cblx0XHRcdGZldGNoKCBhamF4dXJsLCB7XG5cdFx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0XHRjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcblx0XHRcdFx0Ym9keTogZGF0YVxuXHRcdFx0fSApO1xuXG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGltZXIuaW5uZXJIVE1MID0gcmVtYWluaW5nO1xuXHR9XG5cblx0dXBkYXRlVGltZXIoKTtcblx0Y29uc3QgdGltZXJJbnRlcnZhbCA9IHNldEludGVydmFsKCB1cGRhdGVUaW1lciwgMTAwMCk7XG59XG5cbmlmICghRGF0ZS5ub3cpIHtcbiAgICBEYXRlLm5vdyA9IGZ1bmN0aW9uIG5vdygpIHtcbiAgICAgIHJldHVybiBuZXcgRGF0ZSgpLmdldFRpbWUoKTtcbiAgICB9O1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEucHJvbW9fZW5kICE9PSAndW5kZWZpbmVkJykge1xuICAgIGluaXRpYWxpemVDbG9jaygncm9ja2V0LXByb21vLWNvdW50ZG93bicsIHJvY2tldF9hamF4X2RhdGEucHJvbW9fZW5kKTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbiAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBpbml0aWFsaXplQ2xvY2soJ3JvY2tldC1yZW5ldy1jb3VudGRvd24nLCByb2NrZXRfYWpheF9kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbik7XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5ub3RpY2VfZW5kX3RpbWUgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgcnVjc3NUaW1lcigncm9ja2V0LXJ1Y3NzLXRpbWVyJywgcm9ja2V0X2FqYXhfZGF0YS5ub3RpY2VfZW5kX3RpbWUpO1xufSIsImltcG9ydCAnLi4vY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMnO1xuXG52YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG5cblxuICAgIC8qKipcbiAgICAqIENoZWNrIHBhcmVudCAvIHNob3cgY2hpbGRyZW5cbiAgICAqKiovXG5cblx0ZnVuY3Rpb24gd3ByU2hvd0NoaWxkcmVuKGFFbGVtKXtcblx0XHR2YXIgcGFyZW50SWQsICRjaGlsZHJlbjtcblxuXHRcdGFFbGVtICAgICA9ICQoIGFFbGVtICk7XG5cdFx0cGFyZW50SWQgID0gYUVsZW0uYXR0cignaWQnKTtcblx0XHQkY2hpbGRyZW4gPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgcGFyZW50SWQgKyAnXCJdJyk7XG5cblx0XHQvLyBUZXN0IGNoZWNrIGZvciBzd2l0Y2hcblx0XHRpZihhRWxlbS5pcygnOmNoZWNrZWQnKSl7XG5cdFx0XHQkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblxuXHRcdFx0JGNoaWxkcmVuLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGlmICggJCh0aGlzKS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmlzKCc6Y2hlY2tlZCcpKSB7XG5cdFx0XHRcdFx0dmFyIGlkID0gJCh0aGlzKS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyk7XG5cblx0XHRcdFx0XHQkKCdbZGF0YS1wYXJlbnQ9XCInICsgaWQgKyAnXCJdJykuYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdFx0fVxuXHRcdFx0fSk7XG5cdFx0fVxuXHRcdGVsc2V7XG5cdFx0XHQkY2hpbGRyZW4ucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblxuXHRcdFx0JGNoaWxkcmVuLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHZhciBpZCA9ICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpO1xuXG5cdFx0XHRcdCQoJ1tkYXRhLXBhcmVudD1cIicgKyBpZCArICdcIl0nKS5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdFx0fSk7XG5cdFx0fVxuXHR9XG5cbiAgICAvKipcbiAgICAgKiBUZWxsIGlmIHRoZSBnaXZlbiBjaGlsZCBmaWVsZCBoYXMgYW4gYWN0aXZlIHBhcmVudCBmaWVsZC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSAgb2JqZWN0ICRmaWVsZCBBIGpRdWVyeSBvYmplY3Qgb2YgYSBcIi53cHItZmllbGRcIiBmaWVsZC5cbiAgICAgKiBAcmV0dXJuIGJvb2x8bnVsbFxuICAgICAqL1xuICAgIGZ1bmN0aW9uIHdwcklzUGFyZW50QWN0aXZlKCAkZmllbGQgKSB7XG4gICAgICAgIHZhciAkcGFyZW50O1xuXG4gICAgICAgIGlmICggISAkZmllbGQubGVuZ3RoICkge1xuICAgICAgICAgICAgLy8gwq9cXF8o44OEKV8vwq9cbiAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICRmaWVsZC5kYXRhKCAncGFyZW50JyApO1xuXG4gICAgICAgIGlmICggdHlwZW9mICRwYXJlbnQgIT09ICdzdHJpbmcnICkge1xuICAgICAgICAgICAgLy8gVGhpcyBmaWVsZCBoYXMgbm8gcGFyZW50IGZpZWxkOiB0aGVuIHdlIGNhbiBkaXNwbGF5IGl0LlxuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICAkcGFyZW50ID0gJHBhcmVudC5yZXBsYWNlKCAvXlxccyt8XFxzKyQvZywgJycgKTtcblxuICAgICAgICBpZiAoICcnID09PSAkcGFyZW50ICkge1xuICAgICAgICAgICAgLy8gVGhpcyBmaWVsZCBoYXMgbm8gcGFyZW50IGZpZWxkOiB0aGVuIHdlIGNhbiBkaXNwbGF5IGl0LlxuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICAkcGFyZW50ID0gJCggJyMnICsgJHBhcmVudCApO1xuXG4gICAgICAgIGlmICggISAkcGFyZW50Lmxlbmd0aCApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQncyBwYXJlbnQgaXMgbWlzc2luZzogbGV0J3MgY29uc2lkZXIgaXQncyBub3QgYWN0aXZlIHRoZW4uXG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoICEgJHBhcmVudC5pcyggJzpjaGVja2VkJyApICYmICRwYXJlbnQuaXMoJ2lucHV0JykpIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQncyBwYXJlbnQgaXMgY2hlY2tib3ggYW5kIG5vdCBjaGVja2VkOiBkb24ndCBkaXNwbGF5IHRoZSBmaWVsZCB0aGVuLlxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cblx0XHRpZiAoICEkcGFyZW50Lmhhc0NsYXNzKCdyYWRpby1hY3RpdmUnKSAmJiAkcGFyZW50LmlzKCdidXR0b24nKSkge1xuXHRcdFx0Ly8gVGhpcyBmaWVsZCdzIHBhcmVudCBidXR0b24gYW5kIGlzIG5vdCBhY3RpdmU6IGRvbid0IGRpc3BsYXkgdGhlIGZpZWxkIHRoZW4uXG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuICAgICAgICAvLyBHbyByZWN1cnNpdmUgdG8gdGhlIGxhc3QgcGFyZW50LlxuICAgICAgICByZXR1cm4gd3BySXNQYXJlbnRBY3RpdmUoICRwYXJlbnQuY2xvc2VzdCggJy53cHItZmllbGQnICkgKTtcbiAgICB9XG5cblx0LyoqXG5cdCAqIE1hc2tzIHNlbnNpdGl2ZSBpbmZvcm1hdGlvbiBpbiBhbiBpbnB1dCBmaWVsZCBieSByZXBsYWNpbmcgYWxsIGJ1dCB0aGUgbGFzdCA0IGNoYXJhY3RlcnMgd2l0aCBhc3Rlcmlza3MuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBpZF9zZWxlY3RvciAtIFRoZSBJRCBvZiB0aGUgaW5wdXQgZmllbGQgdG8gYmUgbWFza2VkLlxuXHQgKiBAcmV0dXJucyB7dm9pZH0gLSBNb2RpZmllcyB0aGUgaW5wdXQgZmllbGQgdmFsdWUgaW4tcGxhY2UuXG5cdCAqXG5cdCAqIEBleGFtcGxlXG5cdCAqIC8vIEhUTUw6IDxpbnB1dCB0eXBlPVwidGV4dFwiIGlkPVwiY3JlZGl0Q2FyZElucHV0XCIgdmFsdWU9XCIxMjM0NTY3ODkwMTIzNDU2XCI+XG5cdCAqIG1hc2tGaWVsZCgnY3JlZGl0Q2FyZElucHV0Jyk7XG5cdCAqIC8vIFJlc3VsdDogVXBkYXRlcyB0aGUgaW5wdXQgZmllbGQgdmFsdWUgdG8gJyoqKioqKioqKioqKjM0NTYnLlxuXHQgKi9cblx0ZnVuY3Rpb24gbWFza0ZpZWxkKHByb3h5X3NlbGVjdG9yLCBjb25jcmV0ZV9zZWxlY3Rvcikge1xuXHRcdHZhciBjb25jcmV0ZSA9IHtcblx0XHRcdCd2YWwnOiBjb25jcmV0ZV9zZWxlY3Rvci52YWwoKSxcblx0XHRcdCdsZW5ndGgnOiBjb25jcmV0ZV9zZWxlY3Rvci52YWwoKS5sZW5ndGhcblx0XHR9XG5cblx0XHRpZiAoY29uY3JldGUubGVuZ3RoID4gNCkge1xuXG5cdFx0XHR2YXIgaGlkZGVuUGFydCA9ICdcXHUyMDIyJy5yZXBlYXQoTWF0aC5tYXgoMCwgY29uY3JldGUubGVuZ3RoIC0gNCkpO1xuXHRcdFx0dmFyIHZpc2libGVQYXJ0ID0gY29uY3JldGUudmFsLnNsaWNlKC00KTtcblxuXHRcdFx0Ly8gQ29tYmluZSB0aGUgaGlkZGVuIGFuZCB2aXNpYmxlIHBhcnRzXG5cdFx0XHR2YXIgbWFza2VkVmFsdWUgPSBoaWRkZW5QYXJ0ICsgdmlzaWJsZVBhcnQ7XG5cblx0XHRcdHByb3h5X3NlbGVjdG9yLnZhbChtYXNrZWRWYWx1ZSk7XG5cdFx0fVxuXHRcdC8vIEVuc3VyZSBldmVudHMgYXJlIG5vdCBhZGRlZCBtb3JlIHRoYW4gb25jZVxuXHRcdGlmICghcHJveHlfc2VsZWN0b3IuZGF0YSgnZXZlbnRzQXR0YWNoZWQnKSkge1xuXHRcdFx0cHJveHlfc2VsZWN0b3Iub24oJ2lucHV0JywgaGFuZGxlSW5wdXQpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3Iub24oJ2ZvY3VzJywgaGFuZGxlRm9jdXMpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3IuZGF0YSgnZXZlbnRzQXR0YWNoZWQnLCB0cnVlKTtcblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBIYW5kbGUgdGhlIGlucHV0IGV2ZW50XG5cdFx0ICovXG5cdFx0ZnVuY3Rpb24gaGFuZGxlSW5wdXQoKSB7XG5cdFx0XHR2YXIgcHJveHlWYWx1ZSA9IHByb3h5X3NlbGVjdG9yLnZhbCgpO1xuXHRcdFx0aWYgKHByb3h5VmFsdWUuaW5kZXhPZignXFx1MjAyMicpID09PSAtMSkge1xuXHRcdFx0XHRjb25jcmV0ZV9zZWxlY3Rvci52YWwocHJveHlWYWx1ZSk7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlIHRoZSBmb2N1cyBldmVudFxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGhhbmRsZUZvY3VzKCkge1xuXHRcdFx0dmFyIGNvbmNyZXRlX3ZhbHVlID0gY29uY3JldGVfc2VsZWN0b3IudmFsKCk7XG5cdFx0XHRwcm94eV9zZWxlY3Rvci52YWwoY29uY3JldGVfdmFsdWUpO1xuXHRcdH1cblxuXHR9XG5cblx0XHQvLyBVcGRhdGUgdGhlIGNvbmNyZXRlIGZpZWxkIHdoZW4gdGhlIHByb3h5IGlzIHVwZGF0ZWQuXG5cblxuXHRtYXNrRmllbGQoJCgnI2Nsb3VkZmxhcmVfYXBpX2tleV9tYXNrJyksICQoJyNjbG91ZGZsYXJlX2FwaV9rZXknKSk7XG5cdG1hc2tGaWVsZCgkKCcjY2xvdWRmbGFyZV96b25lX2lkX21hc2snKSwgJCgnI2Nsb3VkZmxhcmVfem9uZV9pZCcpKTtcblxuXHQvLyBEaXNwbGF5L0hpZGUgY2hpbGRyZW4gZmllbGRzIG9uIGNoZWNrYm94IGNoYW5nZS5cbiAgICAkKCAnLndwci1pc1BhcmVudCBpbnB1dFt0eXBlPWNoZWNrYm94XScgKS5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHdwclNob3dDaGlsZHJlbigkKHRoaXMpKTtcbiAgICB9KTtcblxuICAgIC8vIE9uIHBhZ2UgbG9hZCwgZGlzcGxheSB0aGUgYWN0aXZlIGZpZWxkcy5cbiAgICAkKCAnLndwci1maWVsZC0tY2hpbGRyZW4nICkuZWFjaCggZnVuY3Rpb24oKSB7XG4gICAgICAgIHZhciAkZmllbGQgPSAkKCB0aGlzICk7XG5cbiAgICAgICAgaWYgKCB3cHJJc1BhcmVudEFjdGl2ZSggJGZpZWxkICkgKSB7XG4gICAgICAgICAgICAkZmllbGQuYWRkQ2xhc3MoICd3cHItaXNPcGVuJyApO1xuICAgICAgICB9XG4gICAgfSApO1xuXG5cblxuXG4gICAgLyoqKlxuICAgICogV2FybmluZyBmaWVsZHNcbiAgICAqKiovXG5cbiAgICB2YXIgJHdhcm5pbmdQYXJlbnQgPSAkKCcud3ByLWZpZWxkLS1wYXJlbnQnKTtcbiAgICB2YXIgJHdhcm5pbmdQYXJlbnRJbnB1dCA9ICQoJy53cHItZmllbGQtLXBhcmVudCBpbnB1dFt0eXBlPWNoZWNrYm94XScpO1xuXG4gICAgLy8gSWYgYWxyZWFkeSBjaGVja2VkXG4gICAgJHdhcm5pbmdQYXJlbnRJbnB1dC5lYWNoKGZ1bmN0aW9uKCl7XG4gICAgICAgIHdwclNob3dDaGlsZHJlbigkKHRoaXMpKTtcbiAgICB9KTtcblxuICAgICR3YXJuaW5nUGFyZW50Lm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgd3ByU2hvd1dhcm5pbmcoJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICBmdW5jdGlvbiB3cHJTaG93V2FybmluZyhhRWxlbSl7XG4gICAgICAgIHZhciAkd2FybmluZ0ZpZWxkID0gYUVsZW0ubmV4dCgnLndwci1maWVsZFdhcm5pbmcnKSxcbiAgICAgICAgICAgICR0aGlzQ2hlY2tib3ggPSBhRWxlbS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLFxuICAgICAgICAgICAgJG5leHRXYXJuaW5nID0gYUVsZW0ucGFyZW50KCkubmV4dCgnLndwci13YXJuaW5nQ29udGFpbmVyJyksXG4gICAgICAgICAgICAkbmV4dEZpZWxkcyA9ICRuZXh0V2FybmluZy5maW5kKCcud3ByLWZpZWxkJyksXG4gICAgICAgICAgICBwYXJlbnRJZCA9IGFFbGVtLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKSxcbiAgICAgICAgICAgICRjaGlsZHJlbiA9ICQoJ1tkYXRhLXBhcmVudD1cIicgKyBwYXJlbnRJZCArICdcIl0nKVxuICAgICAgICA7XG5cbiAgICAgICAgLy8gQ2hlY2sgd2FybmluZyBwYXJlbnRcbiAgICAgICAgaWYoJHRoaXNDaGVja2JveC5pcygnOmNoZWNrZWQnKSl7XG4gICAgICAgICAgICAkd2FybmluZ0ZpZWxkLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgICAgICAkdGhpc0NoZWNrYm94LnByb3AoJ2NoZWNrZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICBhRWxlbS50cmlnZ2VyKCdjaGFuZ2UnKTtcblxuXG4gICAgICAgICAgICB2YXIgJHdhcm5pbmdCdXR0b24gPSAkd2FybmluZ0ZpZWxkLmZpbmQoJy53cHItYnV0dG9uJyk7XG5cbiAgICAgICAgICAgIC8vIFZhbGlkYXRlIHRoZSB3YXJuaW5nXG4gICAgICAgICAgICAkd2FybmluZ0J1dHRvbi5vbignY2xpY2snLCBmdW5jdGlvbigpe1xuICAgICAgICAgICAgICAgICR0aGlzQ2hlY2tib3gucHJvcCgnY2hlY2tlZCcsIHRydWUpO1xuICAgICAgICAgICAgICAgICR3YXJuaW5nRmllbGQucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcbiAgICAgICAgICAgICAgICAkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblxuICAgICAgICAgICAgICAgIC8vIElmIG5leHQgZWxlbSA9IGRpc2FibGVkXG4gICAgICAgICAgICAgICAgaWYoJG5leHRXYXJuaW5nLmxlbmd0aCA+IDApe1xuICAgICAgICAgICAgICAgICAgICAkbmV4dEZpZWxkcy5yZW1vdmVDbGFzcygnd3ByLWlzRGlzYWJsZWQnKTtcbiAgICAgICAgICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXQnKS5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgICBlbHNle1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuYWRkQ2xhc3MoJ3dwci1pc0Rpc2FibGVkJyk7XG4gICAgICAgICAgICAkbmV4dEZpZWxkcy5maW5kKCdpbnB1dCcpLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSk7XG4gICAgICAgICAgICAkbmV4dEZpZWxkcy5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLnByb3AoJ2NoZWNrZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAkY2hpbGRyZW4ucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENOQU1FUyBhZGQvcmVtb3ZlIGxpbmVzXG4gICAgICovXG4gICAgJChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItbXVsdGlwbGUtY2xvc2UnLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCQodGhpcykucGFyZW50KCkucmVtb3ZlKCk7XG5cdH0gKTtcblxuXHQkKCcud3ByLWJ1dHRvbi0tYWRkTXVsdGknKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAkKCQoJyN3cHItY25hbWUtbW9kZWwnKS5odG1sKCkpLmFwcGVuZFRvKCcjd3ByLWNuYW1lcy1saXN0Jyk7XG4gICAgfSk7XG5cblx0LyoqKlxuXHQgKiBXcHIgUmFkaW8gYnV0dG9uXG5cdCAqKiovXG5cdHZhciBkaXNhYmxlX3JhZGlvX3dhcm5pbmcgPSBmYWxzZTtcblxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b24nLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdGlmKCQodGhpcykuaGFzQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpKXtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyICRwYXJlbnQgPSAkKHRoaXMpLnBhcmVudHMoJy53cHItcmFkaW8tYnV0dG9ucycpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b24nKS5yZW1vdmVDbGFzcygncmFkaW8tYWN0aXZlJyk7XG5cdFx0JHBhcmVudC5maW5kKCcud3ByLWV4dHJhLWZpZWxkcy1jb250YWluZXInKS5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1maWVsZFdhcm5pbmcnKS5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdCQodGhpcykuYWRkQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpO1xuXHRcdHdwclNob3dSYWRpb1dhcm5pbmcoJCh0aGlzKSk7XG5cblx0fSApO1xuXG5cblx0ZnVuY3Rpb24gd3ByU2hvd1JhZGlvV2FybmluZygkZWxtKXtcblx0XHRkaXNhYmxlX3JhZGlvX3dhcm5pbmcgPSBmYWxzZTtcblx0XHQkZWxtLnRyaWdnZXIoIFwiYmVmb3JlX3Nob3dfcmFkaW9fd2FybmluZ1wiLCBbICRlbG0gXSApO1xuXHRcdGlmICghJGVsbS5oYXNDbGFzcygnaGFzLXdhcm5pbmcnKSB8fCBkaXNhYmxlX3JhZGlvX3dhcm5pbmcpIHtcblx0XHRcdHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pO1xuXHRcdFx0JGVsbS50cmlnZ2VyKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBbICRlbG0gXSApO1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0XHR2YXIgJHdhcm5pbmdGaWVsZCA9ICQoJ1tkYXRhLXBhcmVudD1cIicgKyAkZWxtLmF0dHIoJ2lkJykgKyAnXCJdLndwci1maWVsZFdhcm5pbmcnKTtcblx0XHQkd2FybmluZ0ZpZWxkLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0dmFyICR3YXJuaW5nQnV0dG9uID0gJHdhcm5pbmdGaWVsZC5maW5kKCcud3ByLWJ1dHRvbicpO1xuXG5cdFx0Ly8gVmFsaWRhdGUgdGhlIHdhcm5pbmdcblx0XHQkd2FybmluZ0J1dHRvbi5vbignY2xpY2snLCBmdW5jdGlvbigpe1xuXHRcdFx0JHdhcm5pbmdGaWVsZC5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdFx0d3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSk7XG5cdFx0XHQkZWxtLnRyaWdnZXIoIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIFsgJGVsbSBdICk7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fSk7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKSB7XG5cdFx0dmFyICRwYXJlbnQgPSAkZWxtLnBhcmVudHMoJy53cHItcmFkaW8tYnV0dG9ucycpO1xuXHRcdHZhciAkY2hpbGRyZW4gPSAkKCcud3ByLWV4dHJhLWZpZWxkcy1jb250YWluZXJbZGF0YS1wYXJlbnQ9XCInICsgJGVsbS5hdHRyKCdpZCcpICsgJ1wiXScpO1xuXHRcdCRjaGlsZHJlbi5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHR9XG5cblx0LyoqKlxuXHQgKiBXcHIgT3B0aW1pemUgQ3NzIERlbGl2ZXJ5IEZpZWxkXG5cdCAqKiovXG5cdHZhciBydWNzc0FjdGl2ZSA9IHBhcnNlSW50KCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgpKTtcblxuXHQkKCBcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kIC53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uXCIgKVxuXHRcdC5vbiggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgZnVuY3Rpb24oIGV2ZW50LCAkZWxtICkge1xuXHRcdFx0dG9nZ2xlQWN0aXZlT3B0aW1pemVDc3NEZWxpdmVyeU1ldGhvZCgkZWxtKTtcblx0XHR9KTtcblxuXHQkKFwiI29wdGltaXplX2Nzc19kZWxpdmVyeVwiKS5vbihcImNoYW5nZVwiLCBmdW5jdGlvbigpe1xuXHRcdGlmKCAkKHRoaXMpLmlzKFwiOm5vdCg6Y2hlY2tlZClcIikgKXtcblx0XHRcdGRpc2FibGVPcHRpbWl6ZUNzc0RlbGl2ZXJ5KCk7XG5cdFx0fWVsc2V7XG5cdFx0XHR2YXIgZGVmYXVsdF9yYWRpb19idXR0b25faWQgPSAnIycrJCgnI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QnKS5kYXRhKCAnZGVmYXVsdCcgKTtcblx0XHRcdCQoZGVmYXVsdF9yYWRpb19idXR0b25faWQpLnRyaWdnZXIoJ2NsaWNrJyk7XG5cdFx0fVxuXHR9KTtcblxuXHRmdW5jdGlvbiB0b2dnbGVBY3RpdmVPcHRpbWl6ZUNzc0RlbGl2ZXJ5TWV0aG9kKCRlbG0pIHtcblx0XHR2YXIgb3B0aW1pemVfbWV0aG9kID0gJGVsbS5kYXRhKCd2YWx1ZScpO1xuXHRcdGlmKCdyZW1vdmVfdW51c2VkX2NzcycgPT09IG9wdGltaXplX21ldGhvZCl7XG5cdFx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMSk7XG5cdFx0XHQkKCcjYXN5bmNfY3NzJykudmFsKDApO1xuXHRcdH1lbHNle1xuXHRcdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDApO1xuXHRcdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgxKTtcblx0XHR9XG5cblx0fVxuXG5cdGZ1bmN0aW9uIGRpc2FibGVPcHRpbWl6ZUNzc0RlbGl2ZXJ5KCkge1xuXHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgwKTtcblx0XHQkKCcjYXN5bmNfY3NzJykudmFsKDApO1xuXHR9XG5cblx0JCggXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCAud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvblwiIClcblx0XHQub24oIFwiYmVmb3JlX3Nob3dfcmFkaW9fd2FybmluZ1wiLCBmdW5jdGlvbiggZXZlbnQsICRlbG0gKSB7XG5cdFx0XHRkaXNhYmxlX3JhZGlvX3dhcm5pbmcgPSAoJ3JlbW92ZV91bnVzZWRfY3NzJyA9PT0gJGVsbS5kYXRhKCd2YWx1ZScpICYmIDEgPT09IHJ1Y3NzQWN0aXZlKVxuXHRcdH0pO1xuXG5cdCQoIFwiLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1saXN0LWhlYWRlclwiICkuY2xpY2soZnVuY3Rpb24gKGUpIHtcblx0XHQkKGUudGFyZ2V0KS5jbG9zZXN0KCcud3ByLW11bHRpcGxlLXNlbGVjdCAud3ByLWxpc3QnKS50b2dnbGVDbGFzcygnb3BlbicpO1xuXHR9KTtcblxuXHQkKCcud3ByLW11bHRpcGxlLXNlbGVjdCAud3ByLWNoZWNrYm94JykuY2xpY2soZnVuY3Rpb24gKGUpIHtcblx0XHRjb25zdCBjaGVja2JveCA9ICQodGhpcykuZmluZCgnaW5wdXQnKTtcblx0XHRjb25zdCBpc19jaGVja2VkID0gY2hlY2tib3guYXR0cignY2hlY2tlZCcpICE9PSB1bmRlZmluZWQ7XG5cdFx0Y2hlY2tib3guYXR0cignY2hlY2tlZCcsIGlzX2NoZWNrZWQgPyBudWxsIDogJ2NoZWNrZWQnICk7XG5cdFx0Y29uc3Qgc3ViX2NoZWNrYm94ZXMgPSAkKGNoZWNrYm94KS5jbG9zZXN0KCcud3ByLWxpc3QnKS5maW5kKCcud3ByLWxpc3QtYm9keSBpbnB1dFt0eXBlPVwiY2hlY2tib3hcIl0nKTtcblx0XHRpZihjaGVja2JveC5oYXNDbGFzcygnd3ByLW1haW4tY2hlY2tib3gnKSkge1xuXHRcdFx0JC5tYXAoc3ViX2NoZWNrYm94ZXMsIGNoZWNrYm94ID0+IHtcblx0XHRcdFx0JChjaGVja2JveCkuYXR0cignY2hlY2tlZCcsIGlzX2NoZWNrZWQgPyBudWxsIDogJ2NoZWNrZWQnICk7XG5cdFx0XHR9KTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0Y29uc3QgbWFpbl9jaGVja2JveCA9ICQoY2hlY2tib3gpLmNsb3Nlc3QoJy53cHItbGlzdCcpLmZpbmQoJy53cHItbWFpbi1jaGVja2JveCcpO1xuXG5cdFx0Y29uc3Qgc3ViX2NoZWNrZWQgPSAgJC5tYXAoc3ViX2NoZWNrYm94ZXMsIGNoZWNrYm94ID0+IHtcblx0XHRcdGlmKCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnKSA9PT0gdW5kZWZpbmVkKSB7XG5cdFx0XHRcdHJldHVybiA7XG5cdFx0XHR9XG5cdFx0XHRyZXR1cm4gY2hlY2tib3g7XG5cdFx0fSk7XG5cdFx0bWFpbl9jaGVja2JveC5hdHRyKCdjaGVja2VkJywgc3ViX2NoZWNrZWQubGVuZ3RoID09PSBzdWJfY2hlY2tib3hlcy5sZW5ndGggPyAnY2hlY2tlZCcgOiBudWxsICk7XG5cdH0pO1xuXG5cdGlmICggJCggJy53cHItbWFpbi1jaGVja2JveCcgKS5sZW5ndGggPiAwICkge1xuXHRcdCQoJy53cHItbWFpbi1jaGVja2JveCcpLmVhY2goKGNoZWNrYm94X2tleSwgY2hlY2tib3gpID0+IHtcblx0XHRcdGxldCBwYXJlbnRfbGlzdCA9ICQoY2hlY2tib3gpLnBhcmVudHMoJy53cHItbGlzdCcpO1xuXHRcdFx0bGV0IG5vdF9jaGVja2VkID0gcGFyZW50X2xpc3QuZmluZCggJy53cHItbGlzdC1ib2R5IGlucHV0W3R5cGU9Y2hlY2tib3hdOm5vdCg6Y2hlY2tlZCknICkubGVuZ3RoO1xuXHRcdFx0JChjaGVja2JveCkuYXR0cignY2hlY2tlZCcsIG5vdF9jaGVja2VkIDw9IDAgPyAnY2hlY2tlZCcgOiBudWxsICk7XG5cdFx0fSk7XG5cdH1cblxuXHRsZXQgY2hlY2tCb3hDb3VudGVyID0ge1xuXHRcdGNoZWNrZWQ6IHt9LFxuXHRcdHRvdGFsOiB7fVxuXHR9O1xuXHQkKCcud3ByLWZpZWxkLS1jYXRlZ29yaXplZG11bHRpc2VsZWN0IC53cHItbGlzdCcpLmVhY2goZnVuY3Rpb24gKCkge1xuXHRcdC8vIEdldCB0aGUgSUQgb2YgdGhlIGN1cnJlbnQgZWxlbWVudFxuXHRcdGxldCBpZCA9ICQodGhpcykuYXR0cignaWQnKTtcblx0XHRpZiAoaWQpIHtcblx0XHRcdGNoZWNrQm94Q291bnRlci5jaGVja2VkW2lkXSA9ICQoYCMke2lkfSBpbnB1dFt0eXBlPSdjaGVja2JveCddOmNoZWNrZWRgKS5sZW5ndGg7XG5cdFx0XHRjaGVja0JveENvdW50ZXIudG90YWxbaWRdID0gJChgIyR7aWR9IGlucHV0W3R5cGU9J2NoZWNrYm94J106bm90KC53cHItbWFpbi1jaGVja2JveClgKS5sZW5ndGg7XG5cdFx0XHQvLyBVcGRhdGUgdGhlIGNvdW50ZXIgdGV4dFxuXHRcdFx0JChgIyR7aWR9IC53cHItYmFkZ2UtY291bnRlciBzcGFuYCkudGV4dChjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0pO1xuXHRcdFx0Ly8gU2hvdyBvciBoaWRlIHRoZSBjb3VudGVyIGJhZGdlIGJhc2VkIG9uIHRoZSBjb3VudFxuXHRcdFx0JChgIyR7aWR9IC53cHItYmFkZ2UtY291bnRlcmApLnRvZ2dsZShjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPiAwKTtcblxuXHRcdFx0Ly8gQ2hlY2sgdGhlIHNlbGVjdCBhbGwgb3B0aW9uIGlmIGFsbCBleGNsdXNpb25zIGFyZSBjaGVja2VkIGluIGEgc2VjdGlvbi5cblx0XHRcdGlmIChjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPT09IGNoZWNrQm94Q291bnRlci50b3RhbFtpZF0pIHtcblx0XHRcdFx0JChgIyR7aWR9IC53cHItbWFpbi1jaGVja2JveGApLmF0dHIoJ2NoZWNrZWQnLCB0cnVlKTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXG5cdC8qKlxuXHQgKiBEZWxheSBKUyBFeGVjdXRpb24gU2FmZSBNb2RlIEZpZWxkXG5cdCAqL1xuXHR2YXIgJGRqZV9zYWZlX21vZGVfY2hlY2tib3ggPSAkKCcjZGVsYXlfanNfZXhlY3V0aW9uX3NhZmVfbW9kZScpO1xuXHQkKCcjZGVsYXlfanMnKS5vbignY2hhbmdlJywgZnVuY3Rpb24gKCkge1xuXHRcdGlmICgkKHRoaXMpLmlzKCc6bm90KDpjaGVja2VkKScpICYmICRkamVfc2FmZV9tb2RlX2NoZWNrYm94LmlzKCc6Y2hlY2tlZCcpKSB7XG5cdFx0XHQkZGplX3NhZmVfbW9kZV9jaGVja2JveC50cmlnZ2VyKCdjbGljaycpO1xuXHRcdH1cblx0fSk7XG5cblx0bGV0IHN0YWNrZWRfc2VsZWN0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICdyb2NrZXRfc3RhY2tlZF9zZWxlY3QnICk7XG5cdGlmICggc3RhY2tlZF9zZWxlY3QgKSB7XG5cdFx0c3RhY2tlZF9zZWxlY3QuYWRkRXZlbnRMaXN0ZW5lcignY3VzdG9tLXNlbGVjdC1jaGFuZ2UnLGZ1bmN0aW9uKGV2ZW50KXtcblxuXHRcdFx0bGV0IHNlbGVjdGVkX29wdGlvbiA9ICQoIGV2ZW50LmRldGFpbC5zZWxlY3RlZE9wdGlvbiApO1xuXG5cdFx0XHRsZXQgbmFtZSA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCduYW1lJyk7XG5cblx0XHRcdGxldCBzYXZpbmcgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgnc2F2aW5nJyk7XG5cdFx0XHRsZXQgcmVndWxhcl9wcmljZSAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgncmVndWxhci1wcmljZScpO1xuXHRcdFx0bGV0IHByaWNlICA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCdwcmljZScpO1xuXHRcdFx0bGV0IHVybCAgICA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCd1cmwnKTtcblxuXHRcdFx0bGV0IHBhcmVudF9pdGVtID0gJCh0aGlzKS5wYXJlbnRzKCAnLndwci11cGdyYWRlLWl0ZW0nICk7XG5cblx0XHRcdGlmICggc2F2aW5nICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLXNhdmluZyBzcGFuJyApLmh0bWwoIHNhdmluZyApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCBuYW1lICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLXRpdGxlJyApLmh0bWwoIG5hbWUgKTtcblx0XHRcdH1cblx0XHRcdGlmICggcmVndWxhcl9wcmljZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1wcmljZS1yZWd1bGFyIHNwYW4nICkuaHRtbCggcmVndWxhcl9wcmljZSApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCBwcmljZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1wcmljZS12YWx1ZScgKS5odG1sKCBwcmljZSApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCB1cmwgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtbGluaycgKS5hdHRyKCAnaHJlZicsIHVybCApO1xuXHRcdFx0fVxuXG5cdFx0fSApO1xuXHR9XG5cblx0JChkb2N1bWVudCkub24oICdjbGljaycsICcud3ByLWNvbmZpcm0tZGVsZXRlJywgZnVuY3Rpb24gKGUpIHtcblx0XHRyZXR1cm4gY29uZmlybSggJCh0aGlzKS5kYXRhKCd3cHJfY29uZmlybV9tc2cnKSApO1xuXHR9ICk7XG5cbn0pO1xuIiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXG5cblx0LyoqKlxuXHQqIERhc2hib2FyZCBub3RpY2Vcblx0KioqL1xuXG5cdHZhciAkbm90aWNlID0gJCgnLndwci1ub3RpY2UnKTtcblx0dmFyICRub3RpY2VDbG9zZSA9ICQoJyN3cHItY29uZ3JhdHVsYXRpb25zLW5vdGljZScpO1xuXG5cdCRub3RpY2VDbG9zZS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJDbG9zZURhc2hib2FyZE5vdGljZSgpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VEYXNoYm9hcmROb3RpY2UoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAudG8oJG5vdGljZSwgMSwge2F1dG9BbHBoYTowLCB4OjQwLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC50bygkbm90aWNlLCAwLjYsIHtoZWlnaHQ6IDAsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjQnKVxuXHRcdCAgLnNldCgkbm90aWNlLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0LyoqXG5cdCAqIFJvY2tldCBBbmFseXRpY3Mgbm90aWNlIGluZm8gY29sbGVjdFxuXHQgKi9cblx0JCggJy5yb2NrZXQtYW5hbHl0aWNzLWRhdGEtY29udGFpbmVyJyApLmhpZGUoKTtcblx0JCggJy5yb2NrZXQtcHJldmlldy1hbmFseXRpY3MtZGF0YScgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCh0aGlzKS5wYXJlbnQoKS5uZXh0KCAnLnJvY2tldC1hbmFseXRpY3MtZGF0YS1jb250YWluZXInICkudG9nZ2xlKCk7XG5cdH0gKTtcblxuXHQvKioqXG5cdCogSGlkZSAvIHNob3cgUm9ja2V0IGFkZG9uIHRhYnMuXG5cdCoqKi9cblxuXHQkKCAnLndwci10b2dnbGUtYnV0dG9uJyApLmVhY2goIGZ1bmN0aW9uKCkge1xuXHRcdHZhciAkYnV0dG9uICAgPSAkKCB0aGlzICk7XG5cdFx0dmFyICRjaGVja2JveCA9ICRidXR0b24uY2xvc2VzdCggJy53cHItZmllbGRzQ29udGFpbmVyLWZpZWxkc2V0JyApLmZpbmQoICcud3ByLXJhZGlvIDpjaGVja2JveCcgKTtcblx0XHR2YXIgJG1lbnVJdGVtID0gJCggJ1tocmVmPVwiJyArICRidXR0b24uYXR0ciggJ2hyZWYnICkgKyAnXCJdLndwci1tZW51SXRlbScgKTtcblxuXHRcdCRjaGVja2JveC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRpZiAoICRjaGVja2JveC5pcyggJzpjaGVja2VkJyApICkge1xuXHRcdFx0XHQkbWVudUl0ZW0uY3NzKCAnZGlzcGxheScsICdibG9jaycgKTtcblx0XHRcdFx0JGJ1dHRvbi5jc3MoICdkaXNwbGF5JywgJ2lubGluZS1ibG9jaycgKTtcblx0XHRcdH0gZWxzZXtcblx0XHRcdFx0JG1lbnVJdGVtLmNzcyggJ2Rpc3BsYXknLCAnbm9uZScgKTtcblx0XHRcdFx0JGJ1dHRvbi5jc3MoICdkaXNwbGF5JywgJ25vbmUnICk7XG5cdFx0XHR9XG5cdFx0fSApLnRyaWdnZXIoICdjaGFuZ2UnICk7XG5cdH0gKTtcblxuXHQvKioqXG5cdCogSGVscCBCdXR0b24gVHJhY2tpbmdcblx0KioqL1xuXG5cdC8vIFRyYWNrIGNsaWNrcyBvbiB2YXJpb3VzIGhlbHAgZWxlbWVudHMgd2l0aCBkYXRhIGF0dHJpYnV0ZXNcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJ1tkYXRhLXdwcl90cmFja19oZWxwXScsIGZ1bmN0aW9uKGUpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHZhciAkZWwgPSAkKHRoaXMpO1xuXHRcdFx0dmFyIGJ1dHRvbiA9ICRlbC5kYXRhKCd3cHJfdHJhY2tfaGVscCcpO1xuXHRcdFx0dmFyIGNvbnRleHQgPSAkZWwuZGF0YSgnd3ByX3RyYWNrX2NvbnRleHQnKSB8fCAnJztcblxuXHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbihidXR0b24sIGNvbnRleHQpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gVHJhY2sgc3BlY2lmaWMgaGVscCByZXNvdXJjZSBjbGlja3Mgd2l0aCBleHBsaWNpdCBzZWxlY3RvcnNcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53aXN0aWFfZW1iZWQnLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHZhciB0aXRsZSA9ICQodGhpcykudGV4dCgpIHx8ICdHZXR0aW5nIFN0YXJ0ZWQgVmlkZW8nO1xuXHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbih0aXRsZSwgJ0dldHRpbmcgU3RhcnRlZCcpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gVHJhY2sgRkFRIGxpbmtzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICdhW2RhdGEtYmVhY29uLWFydGljbGVdJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgaHJlZiA9ICQodGhpcykuYXR0cignaHJlZicpO1xuXHRcdFx0dmFyIHRleHQgPSAkKHRoaXMpLnRleHQoKTtcblxuXHRcdFx0Ly8gQ2hlY2sgaWYgaXQncyBpbiBGQVEgc2VjdGlvbiBvciBzaWRlYmFyIGRvY3VtZW50YXRpb25cblx0XHRcdGlmICgkKHRoaXMpLmNsb3Nlc3QoJy53cHItZmllbGRzQ29udGFpbmVyLWZpZWxkc2V0JykucHJldignLndwci1vcHRpb25IZWFkZXInKS5maW5kKCcud3ByLXRpdGxlMicpLnRleHQoKS5pbmNsdWRlcygnRnJlcXVlbnRseSBBc2tlZCBRdWVzdGlvbnMnKSkge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdGQVEgLSAnICsgdGV4dCwgJ0Rhc2hib2FyZCcpO1xuXHRcdFx0fSBlbHNlIGlmICgkKHRoaXMpLmNsb3Nlc3QoJy53cHItZG9jdW1lbnRhdGlvbicpLmxlbmd0aCA+IDApIHtcblx0XHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignRG9jdW1lbnRhdGlvbicsICdTaWRlYmFyJyk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdEb2N1bWVudGF0aW9uIExpbmsnLCAnR2VuZXJhbCcpO1xuXHRcdFx0fVxuXHRcdH1cblx0fSk7XG5cblx0Ly8gVHJhY2sgXCJIb3cgdG8gbWVhc3VyZSBsb2FkaW5nIHRpbWVcIiBsaW5rXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICdhW2hyZWYqPVwiaG93LXRvLXRlc3Qtd29yZHByZXNzLXNpdGUtcGVyZm9ybWFuY2VcIl0nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0xvYWRpbmcgVGltZSBHdWlkZScsICdTaWRlYmFyJyk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBUcmFjayBcIk5lZWQgaGVscD9cIiBsaW5rcyAoZXhpc3RpbmcgaGVscCBidXR0b25zKVxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1pbmZvQWN0aW9uLS1oZWxwOm5vdChbZGF0YS1iZWFjb24taWRdKScsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignTmVlZCBIZWxwJywgJ0dlbmVyYWwnKTtcblx0XHR9XG5cdH0pO1xuXG5cblx0LyoqKlxuXHQqIFNob3cgcG9waW4gYW5hbHl0aWNzXG5cdCoqKi9cblxuXHR2YXIgJHdwckFuYWx5dGljc1BvcGluID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MnKSxcblx0XHQkd3ByUG9waW5PdmVybGF5ID0gJCgnLndwci1Qb3Bpbi1vdmVybGF5JyksXG5cdFx0JHdwckFuYWx5dGljc0Nsb3NlUG9waW4gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcy1jbG9zZScpLFxuXHRcdCR3cHJBbmFseXRpY3NQb3BpbkJ1dHRvbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzIC53cHItYnV0dG9uJyksXG5cdFx0JHdwckFuYWx5dGljc09wZW5Qb3BpbiA9ICQoJy53cHItanMtcG9waW4nKVxuXHQ7XG5cblx0JHdwckFuYWx5dGljc09wZW5Qb3Bpbi5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHdwck9wZW5BbmFseXRpY3MoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdCR3cHJBbmFseXRpY3NDbG9zZVBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByQ2xvc2VBbmFseXRpY3MoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdCR3cHJBbmFseXRpY3NQb3BpbkJ1dHRvbi5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHdwckFjdGl2YXRlQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJPcGVuQW5hbHl0aWNzKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLnNldCgkd3ByQW5hbHl0aWNzUG9waW4sIHsnZGlzcGxheSc6J2Jsb2NrJ30pXG5cdFx0ICAuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J2Jsb2NrJ30pXG5cdFx0ICAuZnJvbVRvKCR3cHJQb3Bpbk92ZXJsYXksIDAuNiwge2F1dG9BbHBoYTowfSx7YXV0b0FscGhhOjEsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLmZyb21Ubygkd3ByQW5hbHl0aWNzUG9waW4sIDAuNiwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6IC0yNH0sIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNScpXG5cdFx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VBbmFseXRpY3MoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAuZnJvbVRvKCR3cHJBbmFseXRpY3NQb3BpbiwgMC42LCB7YXV0b0FscGhhOjEsIG1hcmdpblRvcDogMH0sIHthdXRvQWxwaGE6MCwgbWFyZ2luVG9wOi0yNCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAuZnJvbVRvKCR3cHJQb3Bpbk92ZXJsYXksIDAuNiwge2F1dG9BbHBoYToxfSx7YXV0b0FscGhhOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNScpXG5cdFx0ICAuc2V0KCR3cHJBbmFseXRpY3NQb3BpbiwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdCAgLnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByQWN0aXZhdGVBbmFseXRpY3MoKXtcblx0XHR3cHJDbG9zZUFuYWx5dGljcygpO1xuXHRcdCQoJyNhbmFseXRpY3NfZW5hYmxlZCcpLnByb3AoJ2NoZWNrZWQnLCB0cnVlKTtcblxuXHRcdHZhciBhbmFseXRpY3NFbmFibGVkID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2FuYWx5dGljc19lbmFibGVkJyk7XG5cblx0XHRpZiAoIGFuYWx5dGljc0VuYWJsZWQgKSB7XG5cdFx0XHR2YXIgY2hhbmdlRXZlbnQgPSBuZXcgRXZlbnQoJ2NoYW5nZScsIHsgYnViYmxlczogdHJ1ZSB9KTtcblx0XHRcdGFuYWx5dGljc0VuYWJsZWQuZGlzcGF0Y2hFdmVudChjaGFuZ2VFdmVudCk7XG5cdFx0fVxuXHR9XG5cblx0Ly8gRGlzcGxheSBDVEEgd2l0aGluIHRoZSBwb3BpbiBgV2hhdCBpbmZvIHdpbGwgd2UgY29sbGVjdD9gXG5cdCQoJyNhbmFseXRpY3NfZW5hYmxlZCcpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbiAoKSB7XG5cdFx0JCgnLndwci1yb2NrZXQtYW5hbHl0aWNzLWN0YScpLnRvZ2dsZUNsYXNzKCd3cHItaXNIaWRkZW4nKTtcblx0fSk7XG5cblx0LyoqKlxuXHQqIFNob3cgcG9waW4gdXBncmFkZVxuXHQqKiovXG5cblx0dmFyICR3cHJVcGdyYWRlUG9waW4gPSAkKCcud3ByLVBvcGluLVVwZ3JhZGUnKSxcblx0JHdwclVwZ3JhZGVDbG9zZVBvcGluID0gJCgnLndwci1Qb3Bpbi1VcGdyYWRlLWNsb3NlJyksXG5cdCR3cHJVcGdyYWRlT3BlblBvcGluID0gJCgnLndwci1wb3Bpbi11cGdyYWRlLXRvZ2dsZScpO1xuXG5cdCR3cHJVcGdyYWRlT3BlblBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByT3BlblVwZ3JhZGVQb3BpbigpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwclVwZ3JhZGVDbG9zZVBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlVXBncmFkZVBvcGluKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJPcGVuVXBncmFkZVBvcGluKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKTtcblxuXHRcdHZUTC5zZXQoJHdwclVwZ3JhZGVQb3BpbiwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHRcdC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHRcdC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjB9LHthdXRvQWxwaGE6MSwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0XHQuZnJvbVRvKCR3cHJVcGdyYWRlUG9waW4sIDAuNiwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6IC0yNH0sIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNScpXG5cdFx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VVcGdyYWRlUG9waW4oKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpO1xuXG5cdFx0dlRMLmZyb21Ubygkd3ByVXBncmFkZVBvcGluLCAwLjYsIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOiAwfSwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6LTI0LCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHRcdC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjF9LHthdXRvQWxwaGE6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHRcdC5zZXQoJHdwclVwZ3JhZGVQb3BpbiwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdFx0LnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0LyoqKlxuXHQqIFNpZGViYXIgb24vb2ZmXG5cdCoqKi9cblx0dmFyICR3cHJTaWRlYmFyICAgID0gJCggJy53cHItU2lkZWJhcicgKTtcblx0dmFyICR3cHJCdXR0b25UaXBzID0gJCgnLndwci1qcy10aXBzJyk7XG5cblx0JHdwckJ1dHRvblRpcHMub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckRldGVjdFRpcHMoJCh0aGlzKSk7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckRldGVjdFRpcHMoYUVsZW0pe1xuXHRcdGlmKGFFbGVtLmlzKCc6Y2hlY2tlZCcpKXtcblx0XHRcdCR3cHJTaWRlYmFyLmNzcygnZGlzcGxheScsJ2Jsb2NrJyk7XG5cdFx0XHRsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb24nICk7XG5cdFx0fVxuXHRcdGVsc2V7XG5cdFx0XHQkd3ByU2lkZWJhci5jc3MoJ2Rpc3BsYXknLCdub25lJyk7XG5cdFx0XHRsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb2ZmJyApO1xuXHRcdH1cblx0fVxuXG5cblxuXHQvKioqXG5cdCogRGV0ZWN0IEFkYmxvY2tcblx0KioqL1xuXG5cdGlmKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdMS2dPY0NScHdtQWonKSl7XG5cdFx0JCgnLndwci1hZGJsb2NrJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblx0fSBlbHNlIHtcblx0XHQkKCcud3ByLWFkYmxvY2snKS5jc3MoJ2Rpc3BsYXknLCAnYmxvY2snKTtcblx0fVxuXG5cdHZhciAkYWRibG9jayA9ICQoJy53cHItYWRibG9jaycpO1xuXHR2YXIgJGFkYmxvY2tDbG9zZSA9ICQoJy53cHItYWRibG9jay1jbG9zZScpO1xuXG5cdCRhZGJsb2NrQ2xvc2Uub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VBZGJsb2NrTm90aWNlKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJDbG9zZUFkYmxvY2tOb3RpY2UoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAudG8oJGFkYmxvY2ssIDEsIHthdXRvQWxwaGE6MCwgeDo0MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAudG8oJGFkYmxvY2ssIDAuNCwge2hlaWdodDogMCwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNCcpXG5cdFx0ICAuc2V0KCRhZGJsb2NrLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0Ly8gSGFuZGxlIGV4cGFuZC9jb2xsYXBzZSBvZiByZWNvbW1lbmRhdGlvbnMgbGlzdC5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJyN3cHItcmVjb21tZW5kYXRpb25zLWxvYWQtbW9yZScsIGZ1bmN0aW9uKCkge1xuXHRcdGxldCAkbGlzdCA9ICQoJy53cHItcmVjb21tZW5kYXRpb25zX19saXN0Jyk7XG5cdFx0bGV0ICRoaWRkZW5JdGVtcyA9ICRsaXN0LmZpbmQoJy53cHItcmVjb21tZW5kYXRpb24taXRlbTpndCgyKScpO1xuXG5cdFx0Ly8gVHJhY2sgTG9hZCBNb3JlIGJ1dHRvbiBjbGlja1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbigncm9ja2V0IGluc2lnaHRzIHJlY29tbWVuZGF0aW9ucyBsb2FkIG1vcmUnLCAnbG9hZF9tb3JlJyk7XG5cdFx0fVxuXG5cdFx0aWYgKCRsaXN0Lmhhc0NsYXNzKCdpcy1leHBhbmRlZCcpKSB7XG5cdFx0XHQkaGlkZGVuSXRlbXMuc2xpZGVVcCgzMDAsIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHQkbGlzdC5yZW1vdmVDbGFzcygnaXMtZXhwYW5kZWQnKTtcblx0XHRcdH0pO1xuXHRcdFx0JCh0aGlzKS5yZW1vdmVDbGFzcygnaXMtZXhwYW5kZWQnKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQkbGlzdC5hZGRDbGFzcygnaXMtZXhwYW5kZWQnKTtcblx0XHQkaGlkZGVuSXRlbXMuc2xpZGVEb3duKDMwMCk7XG5cdFx0JCh0aGlzKS5hZGRDbGFzcygnaXMtZXhwYW5kZWQnKTtcblx0fSk7XG5cblx0Ly8gVHJhY2sgUm9ja2V0IEluc2lnaHRzIFJlY29tbWVuZGF0aW9uIEFjdGl2YXRlIGJ1dHRvbiBjbGlja3Ncblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmVjb21tZW5kYXRpb24taXRlbV9fYWN0aXZhdGUnLCBmdW5jdGlvbigpIHtcblx0XHR2YXIgcmVjb21tZW5kYXRpb24gPSAkKHRoaXMpLmRhdGEoJ3JlY29tbWVuZGF0aW9uJykgfHwgJ3Vua25vd24nO1xuXG5cdFx0Ly8gSWYgdGhlIGVsZW1lbnQgaXMgdmlzaWJsZSwgc2Nyb2xsIHRvIHRoZSBlbGVtZW50IHdpdGggaWQgbWF0Y2hpbmcgdGhlIHJlY29tbWVuZGF0aW9uIHZhbHVlLlxuXHRcdC8vIERlbGF5IHNjcm9sbCBieSAxMDBtcyB0byBhbGxvdyBuYXZpZ2F0aW9uIHRvIHRoZSBzZWN0aW9uIHRvIGNvbXBsZXRlIGFuZCBlbnN1cmUgdGhlIHRhcmdldCBlbGVtZW50IGlzIGluIHZpZXcuXG5cdFx0c2V0VGltZW91dChmdW5jdGlvbigpIHtcblx0XHRcdHZhciAkdGFyZ2V0ID0gJChgIyR7cmVjb21tZW5kYXRpb259YCk7XG5cdFx0XHRcblx0XHRcdGlmICghJHRhcmdldC5sZW5ndGggJiYgISR0YXJnZXQuaXMoJzp2aXNpYmxlJykpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQkdGFyZ2V0WzBdLnNjcm9sbEludG9WaWV3KHsgYmVoYXZpb3I6ICdzbW9vdGgnLCBibG9jazogJ2NlbnRlcicgfSk7XG5cdFx0fSwgMTAwKTtcblx0XHRcblx0XHQvLyBUcmFjayBkaXJlY3RseSB3aXRoIE1peHBhbmVsXG5cdFx0aWYgKHR5cGVvZiBtaXhwYW5lbCAhPT0gJ3VuZGVmaW5lZCcgJiYgbWl4cGFuZWwudHJhY2spIHtcblx0XHRcdC8vIENoZWNrIGlmIHVzZXIgaGFzIG9wdGVkIGluXG5cdFx0XHRpZiAodHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhICE9PSAndW5kZWZpbmVkJyAmJiByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkICYmIHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgIT09ICcwJykge1xuXHRcdFx0XHQvLyBJZGVudGlmeSB1c2VyIGlmIGF2YWlsYWJsZVxuXHRcdFx0XHRpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdFx0XHRtaXhwYW5lbC5pZGVudGlmeShyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRcblx0XHRcdFx0Ly8gVHJhY2sgdGhlIEJ1dHRvbiBDbGlja2VkIGV2ZW50XG5cdFx0XHRcdG1peHBhbmVsLnRyYWNrKCdCdXR0b24gQ2xpY2tlZCcsIHtcblx0XHRcdFx0XHQnYnV0dG9uJzogJ3JvY2tldCBpbnNpZ2h0cyByZWNvbW1lbmRhdGlvbicsXG5cdFx0XHRcdFx0J3JlY29tbWVuZGF0aW9uJzogcmVjb21tZW5kYXRpb24sXG5cdFx0XHRcdFx0J3BsdWdpbic6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBsdWdpbixcblx0XHRcdFx0XHQnYnJhbmQnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5icmFuZCxcblx0XHRcdFx0XHQnYXBwbGljYXRpb24nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG5cdFx0XHRcdFx0J2NvbnRleHQnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0LFxuXHRcdFx0XHRcdCdwYXRoJzogcm9ja2V0X21peHBhbmVsX2RhdGEucGF0aFxuXHRcdFx0XHR9KTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIENUQSBjbGlja3MgZm9yIFJvY2tldCBJbnNpZ2h0cyBpbiB0aGUgc2V0dGluZ3Mgc2F2ZWQgbm90aWNlLlxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnI3JvY2tldF9yaV9uZXdfdGVzdF9zYXZlX3NldHRpbmdzX2xpbmsnLCBmdW5jdGlvbigpIHtcblx0XHQvLyBUcmFjayBkaXJlY3RseSB3aXRoIE1peHBhbmVsXG5cdFx0aWYgKHR5cGVvZiBtaXhwYW5lbCA9PT0gJ3VuZGVmaW5lZCcgfHwgIW1peHBhbmVsLnRyYWNrKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW5cblx0XHRpZiAodHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhID09PSAndW5kZWZpbmVkJyB8fCAhcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCB8fCByb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkID09PSAnMCcpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBJZGVudGlmeSB1c2VyIGlmIGF2YWlsYWJsZVxuXHRcdGlmIChyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICYmIHR5cGVvZiBtaXhwYW5lbC5pZGVudGlmeSA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0bWl4cGFuZWwuaWRlbnRpZnkocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCk7XG5cdFx0fVxuXG5cdFx0Ly8gVHJhY2sgdGhlIEJ1dHRvbiBDbGlja2VkIGV2ZW50XG5cdFx0bWl4cGFuZWwudHJhY2soJ1JvY2tldCBJbnNpZ2h0cyBDVEEgY2xpY2sgZnJvbSBzZXR0aW5ncyBub3RpY2UnLCB7XG5cdFx0XHQnYnV0dG9uJzogJ0NUQSBvbiBzYXZlIHNldHRpbmdzIG5vdGljZScsXG5cdFx0XHQnc291cmNlJzogJ1NldHRpbmdzIFNhdmVkIE5vdGljZScsXG5cdFx0XHQncGx1Z2luJzogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0J2JyYW5kJzogcm9ja2V0X21peHBhbmVsX2RhdGEuYnJhbmQsXG5cdFx0XHQnYXBwbGljYXRpb24nOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG5cdFx0XHQnY29udGV4dCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmNvbnRleHQsXG5cdFx0XHQncGF0aCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGhcblx0XHR9KTtcblx0fSk7XG59KTtcbiIsImNsYXNzIFJvY2tldE1peHBhbmVsIHtcblxuXHR0cmFja2VkVGFicyA9IFtcblx0XHQnZGFzaGJvYXJkJyxcblx0XHQncm9ja2V0X2luc2lnaHRzJyxcblx0XHQncGFnZV9jZG4nLFxuXHRcdCdmaWxlX29wdGltaXphdGlvbicsXG5cdFx0J21lZGlhJyxcblx0XHQncHJlbG9hZCcsXG5cdFx0J2FkdmFuY2VkX2NhY2hlJyxcblx0XHQnZGF0YWJhc2UnLFxuXHRcdCdoZWFydGJlYXQnLFxuXHRcdCdhZGRvbnMnLFxuXHRcdCdpbWFnaWZ5Jyxcblx0XHQndHV0b3JpYWxzJyxcblx0XHQncGx1Z2lucycsXG5cdFx0J3Rvb2xzJ1xuXHRdO1xuXG5cdGNvbnN0cnVjdG9yKCBjb25maWcgKSB7XG5cdFx0dGhpcy5jb25maWcgPSBjb25maWc7XG5cdH1cblxuXHQvKipcblx0ICogSW5pdGlhbGl6ZXMgdGhlIGhhbmRsZXIuXG5cdCAqL1xuXHRpbml0KCkge1xuXHRcdGlmICggdHlwZW9mIG1peHBhbmVsID09PSAndW5kZWZpbmVkJyB8fCAhbWl4cGFuZWwudHJhY2sgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGlmICggIXRoaXMuY29uZmlnLm9wdGluX2VuYWJsZWQgfHwgdGhpcy5jb25maWcub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdG1peHBhbmVsLmlkZW50aWZ5KHRoaXMuY29uZmlnLnVzZXJfaWQpO1xuXHRcdHRoaXMuX2luaXRMaXN0ZW5lcnMoIHRoaXMgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBJbml0aWFsaXplcyB0aGUgZXZlbnQgbGlzdGVuZXJzLlxuXHQgKlxuXHQgKiBAcGFyYW0gc2VsZiBpbnN0YW5jZSBvZiB0aGlzIG9iamVjdCwgdXNlZCBmb3IgYmluZGluZyBcInRoaXNcIiB0byB0aGUgbGlzdGVuZXJzLlxuXHQgKi9cblx0X2luaXRMaXN0ZW5lcnMoIHNlbGYgKSB7XG5cdFx0d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdoYXNoY2hhbmdlJywgc2VsZi5fb25IYXNoQ2hhbmdlLmJpbmQoIHNlbGYgKSApO1xuXHRcdHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsIHNlbGYuX29uUGFnZUxvYWQuYmluZCggc2VsZiApICk7XG5cdH1cblxuXHQvKipcblx0ICogRXZlbnQgbGlzdGVuZXIgd2hlbiB0aGUgaGFzaCBjaGFuZ2VkIGluIGEgcGFnZS5cblx0ICpcblx0ICogQHBhcmFtIEV2ZW50IGV2ZW50IEV2ZW50IGluc3RhbmNlLlxuXHQgKi9cblx0X29uSGFzaENoYW5nZSggZXZlbnQgKSB7XG5cdFx0Y29uc3Qgb2xkSGFzaCA9IHRoaXMuX2NsZWFuSGFzaChuZXcgVVJMKGV2ZW50Lm9sZFVSTCkuaGFzaCk7XG5cdFx0Y29uc3QgbmV3SGFzaCA9IHRoaXMuX2NsZWFuSGFzaChuZXcgVVJMKGV2ZW50Lm5ld1VSTCkuaGFzaCk7XG5cblx0XHRpZiAoICF0aGlzLl9jYW5UcmFja1RhYihuZXdIYXNoKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aGlzLl9zZW5kUGFnZVZpZXdlZEV2ZW50KHRoaXMuX2dldFNvdXJjZSggb2xkSGFzaCApLCBuZXdIYXNoKTtcblx0fVxuXG5cdF9vblBhZ2VMb2FkKCkge1xuXHRcdGNvbnN0IG5ld0hhc2ggPSB0aGlzLl9jbGVhbkhhc2god2luZG93LmxvY2F0aW9uLmhhc2gpO1xuXHRcdGlmICggIXRoaXMuX2NhblRyYWNrVGFiKG5ld0hhc2gpICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRoaXMuX3NlbmRQYWdlVmlld2VkRXZlbnQodGhpcy5fZ2V0U291cmNlKCksIG5ld0hhc2gpO1xuXHR9XG5cblx0X2NsZWFuSGFzaCggdGFiSGFzaCApIHtcblx0XHRpZiAoIXRhYkhhc2ggfHwgIXRhYkhhc2guc3RhcnRzV2l0aCgnIycpKSB7XG5cdFx0XHRyZXR1cm4gdGFiSGFzaDtcblx0XHR9XG5cdFx0cmV0dXJuIHRhYkhhc2guc3Vic3RyaW5nKDEpO1xuXHR9XG5cblx0X2NhblRyYWNrVGFiKHRhYkhhc2gpIHtcblx0XHRyZXR1cm4gdGhpcy50cmFja2VkVGFicy5pbmNsdWRlcyh0YWJIYXNoKTtcblx0fVxuXG5cdF9nZXRTb3VyY2UoIG9sZEhhc2ggPSAnJyApIHtcblx0XHRpZiAoIG9sZEhhc2ggKSB7XG5cdFx0XHRyZXR1cm4gYHNldHRpbmdzXyR7b2xkSGFzaH1gO1xuXHRcdH1cblxuXHRcdGxldCBzb3VyY2UgPSB0aGlzLl9nZXRTb3VyY2VGcm9tUXVlcnlTdHJpbmdBbmRSZW1vdmVJdCgpO1xuXHRcdGlmICggc291cmNlICkge1xuXHRcdFx0cmV0dXJuIHNvdXJjZTtcblx0XHR9XG5cblx0XHRyZXR1cm4gdGhpcy5fZ2V0U291cmNlRnJvbVJlZmVycmVyKCk7XG5cdH1cblxuXHRfZ2V0U291cmNlRnJvbVF1ZXJ5U3RyaW5nQW5kUmVtb3ZlSXQoKSB7XG5cdFx0Y29uc3QgdXJsID0gbmV3IFVSTCh3aW5kb3cubG9jYXRpb24uaHJlZik7XG5cdFx0Y29uc3QgdXJsUGFyYW1zID0gdXJsLnNlYXJjaFBhcmFtcztcblxuXHRcdC8vIDEuIENoZWNrIGZvciBleHBsaWNpdCBzb3VyY2UgcGFyYW1cblx0XHRpZiAoIXVybFBhcmFtcy5oYXMoJ3JvY2tldF9zb3VyY2UnKSkge1xuXHRcdFx0cmV0dXJuICcnO1xuXHRcdH1cblxuXHRcdC8vIDIuIEdldCB0aGUgdmFsdWVcblx0XHRjb25zdCBzb3VyY2VWYWx1ZSA9IHVybFBhcmFtcy5nZXQoJ3JvY2tldF9zb3VyY2UnKTtcblxuXHRcdC8vIDMuIERlbGV0ZSB0aGUgcGFyYW1ldGVyIGZyb20gdGhlIFVSTFNlYXJjaFBhcmFtcyBvYmplY3Rcblx0XHR1cmxQYXJhbXMuZGVsZXRlKCdyb2NrZXRfc291cmNlJyk7XG5cblx0XHQvLyA0LiBVcGRhdGUgdGhlIGJyb3dzZXIncyBVUkwgdXNpbmcgdGhlIEhpc3RvcnkgQVBJXG5cdFx0Ly8gVGhpcyByZW1vdmVzIHRoZSBwYXJhbWV0ZXIgZnJvbSB0aGUgVVJMIGJhciB3aXRob3V0IHJlbG9hZGluZyB0aGUgcGFnZS5cblx0XHR3aW5kb3cuaGlzdG9yeS5yZXBsYWNlU3RhdGUobnVsbCwgJycsIHVybC5zZWFyY2ggPyB1cmwuaHJlZiA6IHVybC5wYXRobmFtZSk7XG5cblx0XHQvLyA1LiBSZXR1cm4gdGhlIHJldHJpZXZlZCB2YWx1ZVxuXHRcdHJldHVybiBzb3VyY2VWYWx1ZTtcblx0fVxuXG5cdF9nZXRTb3VyY2VGcm9tUmVmZXJyZXIoKSB7XG5cdFx0Y29uc3QgcmVmZXJyZXIgPSBkb2N1bWVudC5yZWZlcnJlcjtcblx0XHRpZiAoIXJlZmVycmVyKSB7XG5cdFx0XHRyZXR1cm4gJ25vcmVmZXJyZXInO1xuXHRcdH1cblx0XHRpZiAoIXJlZmVycmVyLmluY2x1ZGVzKHdpbmRvdy5sb2NhdGlvbi5ob3N0bmFtZSkpIHtcblx0XHRcdHJldHVybiAnZXh0ZXJuYWwnO1xuXHRcdH1cblx0XHRyZXR1cm4gJ2ludGVybmFsJztcblx0fVxuXG5cdF9zZW5kUGFnZVZpZXdlZEV2ZW50KHNvdXJjZSwgbmV3SGFzaCkge1xuXHRcdG1peHBhbmVsLnRyYWNrKCdQYWdlIFZpZXdlZCcsIHtcblx0XHRcdHBhdGg6IGAvd3AtYWRtaW4vb3B0aW9ucy1nZW5lcmFsLnBocD9wYWdlPXdwcm9ja2V0IyR7bmV3SGFzaH1gLFxuXHRcdFx0cGFnZV9uYW1lOiBuZXdIYXNoLnJlcGxhY2UoJ18nLCAnICcpLFxuXHRcdFx0c291cmNlOiBzb3VyY2UsXG5cdFx0XHRwbHVnaW46IHJvY2tldF9taXhwYW5lbF9kYXRhLnBsdWdpbixcblx0XHRcdGJyYW5kOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5icmFuZCxcblx0XHRcdGFwcGxpY2F0aW9uOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5hcHAsXG5cdFx0XHRjb250ZXh0OiByb2NrZXRfbWl4cGFuZWxfZGF0YS5jb250ZXh0XG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogTmFtZWQgc3RhdGljIGNvbnN0cnVjdG9yIHRvIGVuY2Fwc3VsYXRlIGhvdyB0byBjcmVhdGUgdGhlIG9iamVjdC5cblx0ICovXG5cdHN0YXRpYyBydW4oKSB7XG5cdFx0Ly8gQmFpbCBvdXQgaWYgdGhlIGNvbmZpZ3VyYXRpb24gbm90IHBhc3NlZCBmcm9tIHRoZSBzZXJ2ZXIuXG5cdFx0aWYgKCB0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0IGluc3RhbmNlID0gbmV3IFJvY2tldE1peHBhbmVsKCByb2NrZXRfbWl4cGFuZWxfZGF0YSApO1xuXHRcdGluc3RhbmNlLmluaXQoKTtcblx0fVxufVxuXG5Sb2NrZXRNaXhwYW5lbC5ydW4oKTtcbiIsImRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgZnVuY3Rpb24gKCkge1xuXG4gICAgdmFyICRwYWdlTWFuYWdlciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXCIud3ByLUNvbnRlbnRcIik7XG4gICAgaWYoJHBhZ2VNYW5hZ2VyKXtcbiAgICAgICAgbmV3IFBhZ2VNYW5hZ2VyKCRwYWdlTWFuYWdlcik7XG4gICAgfVxuXG59KTtcblxuXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXG5cdFx0Q0xBU1MgUEFHRU1BTkFHRVJcblxcKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cbi8qKlxuICogTWFuYWdlcyB0aGUgZGlzcGxheSBvZiBwYWdlcyAvIHNlY3Rpb24gZm9yIFdQIFJvY2tldCBwbHVnaW5cbiAqXG4gKiBQdWJsaWMgbWV0aG9kIDpcbiAgICAgZGV0ZWN0SUQgLSBEZXRlY3QgSUQgd2l0aCBoYXNoXG4gICAgIGdldEJvZHlUb3AgLSBHZXQgYm9keSB0b3AgcG9zaXRpb25cblx0IGNoYW5nZSAtIERpc3BsYXlzIHRoZSBjb3JyZXNwb25kaW5nIHBhZ2VcbiAqXG4gKi9cblxuZnVuY3Rpb24gUGFnZU1hbmFnZXIoYUVsZW0pIHtcblxuICAgIHZhciByZWZUaGlzID0gdGhpcztcblxuICAgIHRoaXMuJGJvZHkgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLWJvZHknKTtcbiAgICB0aGlzLiRtZW51SXRlbXMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLW1lbnVJdGVtJyk7XG4gICAgdGhpcy4kc3VibWl0QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1Db250ZW50ID4gZm9ybSA+ICN3cHItb3B0aW9ucy1zdWJtaXQnKTtcbiAgICB0aGlzLiRwYWdlcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItUGFnZScpO1xuICAgIHRoaXMuJHNpZGViYXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLVNpZGViYXInKTtcbiAgICB0aGlzLiRjb250ZW50ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1Db250ZW50Jyk7XG4gICAgdGhpcy4kdGlwcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudC10aXBzJyk7XG4gICAgdGhpcy4kbGlua3MgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLWJvZHkgYScpO1xuICAgIHRoaXMuJG1lbnVJdGVtID0gbnVsbDtcbiAgICB0aGlzLiRwYWdlID0gbnVsbDtcbiAgICB0aGlzLnBhZ2VJZCA9IG51bGw7XG4gICAgdGhpcy5ib2R5VG9wID0gMDtcbiAgICB0aGlzLmJ1dHRvblRleHQgPSB0aGlzLiRzdWJtaXRCdXR0b24udmFsdWU7XG5cbiAgICByZWZUaGlzLmdldEJvZHlUb3AoKTtcblxuICAgIC8vIElmIHVybCBwYWdlIGNoYW5nZVxuICAgIHdpbmRvdy5vbmhhc2hjaGFuZ2UgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgcmVmVGhpcy5kZXRlY3RJRCgpO1xuICAgIH1cblxuICAgIC8vIElmIGhhc2ggYWxyZWFkeSBleGlzdCAoYWZ0ZXIgcmVmcmVzaCBwYWdlIGZvciBleGFtcGxlKVxuICAgIGlmKHdpbmRvdy5sb2NhdGlvbi5oYXNoKXtcbiAgICAgICAgdGhpcy5ib2R5VG9wID0gMDtcbiAgICAgICAgdGhpcy5kZXRlY3RJRCgpO1xuICAgIH1cbiAgICBlbHNle1xuICAgICAgICB2YXIgc2Vzc2lvbiA9IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd3cHItaGFzaCcpO1xuICAgICAgICB0aGlzLmJvZHlUb3AgPSAwO1xuXG4gICAgICAgIGlmKHNlc3Npb24pe1xuICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLmhhc2ggPSBzZXNzaW9uO1xuICAgICAgICAgICAgdGhpcy5kZXRlY3RJRCgpO1xuICAgICAgICB9XG4gICAgICAgIGVsc2V7XG4gICAgICAgICAgICB0aGlzLiRtZW51SXRlbXNbMF0uY2xhc3NMaXN0LmFkZCgnaXNBY3RpdmUnKTtcbiAgICAgICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd3cHItaGFzaCcsICdkYXNoYm9hcmQnKTtcbiAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gJyNkYXNoYm9hcmQnO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLy8gQ2xpY2sgbGluayBzYW1lIGhhc2hcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuJGxpbmtzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJGxpbmtzW2ldLm9uY2xpY2sgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIHJlZlRoaXMuZ2V0Qm9keVRvcCgpO1xuICAgICAgICAgICAgdmFyIGhyZWZTcGxpdCA9IHRoaXMuaHJlZi5zcGxpdCgnIycpWzFdO1xuICAgICAgICAgICAgaWYoaHJlZlNwbGl0ID09IHJlZlRoaXMucGFnZUlkICYmIGhyZWZTcGxpdCAhPSB1bmRlZmluZWQpe1xuICAgICAgICAgICAgICAgIHJlZlRoaXMuZGV0ZWN0SUQoKTtcbiAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICB9XG4gICAgICAgIH07XG4gICAgfVxuXG4gICAgLy8gQ2xpY2sgbGlua3Mgbm90IFdQIHJvY2tldCB0byByZXNldCBoYXNoXG4gICAgdmFyICRvdGhlcmxpbmtzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnI2FkbWlubWVudW1haW4gYSwgI3dwYWRtaW5iYXIgYScpO1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgJG90aGVybGlua3MubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgJG90aGVybGlua3NbaV0ub25jbGljayA9IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgJycpO1xuICAgICAgICB9O1xuICAgIH1cblxuICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICd3cHItY2RuLXN0YXRlLWNoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICByZWZUaGlzLnVwZGF0ZVN1Ym1pdERpc2FibGVkU3RhdGUoKTtcbiAgICB9ICk7XG5cbn1cblxuXG4vKlxuKiBQYWdlIGRldGVjdCBJRFxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5kZXRlY3RJRCA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMucGFnZUlkID0gd2luZG93LmxvY2F0aW9uLmhhc2guc3BsaXQoJyMnKVsxXTtcbiAgICB0aGlzLnBhZ2VJZCA9IHRoaXMucGFnZUlkLmluY2x1ZGVzKCc9JykgPyB0aGlzLnBhZ2VJZC5zcGxpdCgnPScpWzBdIDogdGhpcy5wYWdlSWQ7XG4gICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgdGhpcy5wYWdlSWQpO1xuXG4gICAgdGhpcy4kcGFnZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItUGFnZSMnICsgdGhpcy5wYWdlSWQpO1xuICAgIHRoaXMuJG1lbnVJdGVtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwci1uYXYtJyArIHRoaXMucGFnZUlkKTtcblxuICAgIHRoaXMuY2hhbmdlKCk7XG59XG5cblxuXG4vKlxuKiBHZXQgYm9keSB0b3AgcG9zaXRpb25cbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZ2V0Qm9keVRvcCA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBib2R5UG9zID0gdGhpcy4kYm9keS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICB0aGlzLmJvZHlUb3AgPSBib2R5UG9zLnRvcCArIHdpbmRvdy5wYWdlWU9mZnNldCAtIDQ3OyAvLyAjd3BhZG1pbmJhciArIHBhZGRpbmctdG9wIC53cHItd3JhcCAtIDEgLSA0N1xufVxuXG5cblxuLypcbiogUGFnZSBjaGFuZ2VcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuY2hhbmdlID0gZnVuY3Rpb24oKSB7XG5cbiAgICB2YXIgcmVmVGhpcyA9IHRoaXM7XG4gICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNjcm9sbFRvcCA9IHJlZlRoaXMuYm9keVRvcDtcblxuICAgIC8vIEhpZGUgb3RoZXIgcGFnZXNcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuJHBhZ2VzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJHBhZ2VzW2ldLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kbWVudUl0ZW1zLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJG1lbnVJdGVtc1tpXS5jbGFzc0xpc3QucmVtb3ZlKCdpc0FjdGl2ZScpO1xuICAgIH1cblxuICAgIC8vIFNob3cgY3VycmVudCBkZWZhdWx0IHBhZ2VcbiAgICB0aGlzLiRwYWdlLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcblxuICAgIGlmICggbnVsbCA9PT0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJyApICkge1xuICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb24nICk7XG4gICAgfVxuXG4gICAgaWYgKCAnb24nID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnZmxleCc7XG4gICAgfSBlbHNlIGlmICggJ29mZicgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd3cHItc2hvdy1zaWRlYmFyJykgKSB7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3dwci1qcy10aXBzJykucmVtb3ZlQXR0cmlidXRlKCAnY2hlY2tlZCcgKTtcbiAgICB9XG5cbiAgICB0aGlzLiR0aXBzLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIHRoaXMuJG1lbnVJdGVtLmNsYXNzTGlzdC5hZGQoJ2lzQWN0aXZlJyk7XG4gICAgdGhpcy4kc3VibWl0QnV0dG9uLnZhbHVlID0gdGhpcy5idXR0b25UZXh0O1xuICAgIHRoaXMuJGNvbnRlbnQuY2xhc3NMaXN0LmFkZCgnaXNOb3RGdWxsJyk7XG5cbiAgICBjb25zdCBwYWdlc1dpdGhvdXRTdWJtaXQgPSBbXG4gICAgICAgICdkYXNoYm9hcmQnLFxuICAgICAgICAnYWRkb25zJyxcbiAgICAgICAgJ2RhdGFiYXNlJyxcbiAgICAgICAgJ3Rvb2xzJyxcbiAgICAgICAgJ2FkZG9ucycsXG4gICAgICAgICdpbWFnaWZ5JyxcbiAgICAgICAgJ3R1dG9yaWFscycsXG4gICAgICAgICdwbHVnaW5zJyxcbiAgICBdO1xuXG4gICAgY29uc3QgcGFnZXNXaXRob3V0U2lkZWJhclRvZ2dsZSA9IFtcbiAgICAgICAgJ2Rhc2hib2FyZCcsXG4gICAgICAgICdpbWFnaWZ5JyxcbiAgICAgICAgJ3BhZ2VfY2RuJyxcbiAgICBdO1xuXG4gICAgLy8gRXhjZXB0aW9uIGZvciBkYXNoYm9hcmRcbiAgICBpZih0aGlzLnBhZ2VJZCA9PSBcImRhc2hib2FyZFwiKXtcbiAgICAgICAgdGhpcy4kc2lkZWJhci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5yZW1vdmUoJ2lzTm90RnVsbCcpO1xuICAgIH1cblxuICAgIGlmICh0aGlzLnBhZ2VJZCA9PSBcImltYWdpZnlcIikge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgaWYgKHBhZ2VzV2l0aG91dFNpZGViYXJUb2dnbGUuaW5jbHVkZXModGhpcy5wYWdlSWQpKSB7XG4gICAgICAgIHRoaXMuJHRpcHMuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICBpZiAocGFnZXNXaXRob3V0U3VibWl0LmluY2x1ZGVzKHRoaXMucGFnZUlkKSkge1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICB0aGlzLnVwZGF0ZVN1Ym1pdERpc2FibGVkU3RhdGUoKTtcblxuXHQvLyBEaXNwYXRjaCBjdXN0b20gZXZlbnQgYWZ0ZXIgcGFnZSBuYXZpZ2F0aW9uIGZvciBvdGhlciBzY3JpcHRzIHRvIGhvb2sgaW50by5cblx0ZG9jdW1lbnQuZGlzcGF0Y2hFdmVudChuZXcgQ3VzdG9tRXZlbnQoJ3JvY2tldEpzQWZ0ZXJQYWdlTmF2aWdhdGlvbicsIHtcblx0XHRkZXRhaWw6IHtcblx0XHRcdHBhZ2VJZDogdGhpcy5wYWdlSWQsXG5cdFx0XHRzdWJtaXRCdXR0b246IHRoaXMuJHN1Ym1pdEJ1dHRvbixcblx0XHR9XG5cdH0gKSApO1xufTtcblxuXG4vKlxuKiBVcGRhdGUgc3VibWl0IGJ1dHRvbiBkaXNhYmxlZCBzdGF0ZVxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS51cGRhdGVTdWJtaXREaXNhYmxlZFN0YXRlID0gZnVuY3Rpb24oKSB7XG5cdGlmICghdGhpcy4kc3VibWl0QnV0dG9uIHx8ICdub25lJyA9PT0gdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkpIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHR2YXIgaXNDZG5QYWdlID0gJ3BhZ2VfY2RuJyA9PT0gdGhpcy5wYWdlSWQ7XG5cdHZhciBwYXVzZWRSb2NrZXRDZG5CbG9jayA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXG5cdFx0Jy53cHItUGFnZSNwYWdlX2NkbiAud3ByLW5vdGljZS53cHItcmktbm90aWNlLndwci1jZG4tZXhwaXJlZF9fbm90aWNlJ1xuXHQpO1xuXG5cdHRoaXMuJHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGlzQ2RuUGFnZSAmJiAhIXBhdXNlZFJvY2tldENkbkJsb2NrO1xufTtcbiIsIi8qKlxuICogUmVjb21tZW5kYXRpb25zIFdpZGdldCBIYW5kbGVyXG4gKlxuICogTGlzdGVucyBmb3IgR2xvYmFsIFNjb3JlIHVwZGF0ZXMgYW5kIGZldGNoZXMvdXBkYXRlcyByZWNvbW1lbmRhdGlvbnMgZHluYW1pY2FsbHkuXG4gKi9cbnZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcblx0LyoqXG5cdCAqIFVwZGF0ZXMgdGhlIHJlY29tbWVuZGF0aW9ucyB3aWRnZXQgVUkgYmFzZWQgb24gdGhlIGZldGNoZWQgZGF0YS5cblx0ICpcblx0ICogQHBhcmFtIHtPYmplY3R9IGRhdGEgLSBUaGUgcmVjb21tZW5kYXRpb25zIGRhdGEgZnJvbSB0aGUgQVBJLlxuXHQgKiBAcGFyYW0ge0FycmF5fSBkYXRhLnJlY29tbWVuZGF0aW9ucyAtIEFycmF5IG9mIHJlY29tbWVuZGF0aW9ucyBkZXRhaWxzLlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gZGF0YS5yZWNvbW1lbmRhdGlvbnMuaHRtbCAtIFJlY29tbWVuZGF0aW9ucyBIVE1MLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlUmVjb21tZW5kYXRpb25zV2lkZ2V0KGRhdGEpIHtcblx0XHRjb25zdCB3aWRnZXQgPSAkKCcud3ByLXJlY29tbWVuZGF0aW9ucycpO1xuXG5cdFx0aWYgKCF3aWRnZXQgfHwgIWRhdGE/LnJlY29tbWVuZGF0aW9ucz8uaHRtbCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIFVwZGF0ZSB0aGUgd2lkZ2V0IGNvbnRlbnQgd2l0aCB0aGUgbmV3IHJlY29tbWVuZGF0aW9ucyBIVE1MXG5cdFx0d2lkZ2V0LnJlcGxhY2VXaXRoKGRhdGE/LnJlY29tbWVuZGF0aW9ucz8uaHRtbCk7XG5cdH1cblxuXG5cblx0LyoqXG5cdCAqIEZldGNoZXMgdGhlIGN1cnJlbnQgcmVjb21tZW5kYXRpb25zIHN0YXR1cyBmcm9tIHRoZSBSRVNUIEFQSS5cblx0ICovXG5cdGZ1bmN0aW9uIGZldGNoUmVjb21tZW5kYXRpb25zU3RhdHVzKCkge1xuXHRcdC8vIFVzZSBXb3JkUHJlc3MgUkVTVCBBUEkgY2xpZW50IGlmIGF2YWlsYWJsZVxuXHRcdGlmICh3aW5kb3cud3AgJiYgd2luZG93LndwLmFwaUZldGNoKSB7XG5cdFx0XHR3aW5kb3cud3AuYXBpRmV0Y2goe1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yZWNvbW1lbmRhdGlvbnMnXG5cdFx0XHR9KVxuXHRcdFx0XHQudGhlbihmdW5jdGlvbiAoZGF0YSkge1xuXHRcdFx0XHRcdHVwZGF0ZVJlY29tbWVuZGF0aW9uc1dpZGdldChkYXRhKTtcblx0XHRcdFx0fSlcblx0XHRcdFx0LmNhdGNoKGZ1bmN0aW9uIChlcnJvcikge1xuXHRcdFx0XHRcdGNvbnNvbGUuZXJyb3IoJ0ZhaWxlZCB0byBmZXRjaCByZWNvbW1lbmRhdGlvbnMgc3RhdHVzOicsIGVycm9yKTtcblx0XHRcdFx0fSk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdC8vIEZhbGxiYWNrIHRvIGZldGNoIEFQSVxuXHRcdFx0ZmV0Y2god2luZG93LndwQXBpU2V0dGluZ3M/LnJvb3QgKyAnd3Atcm9ja2V0L3YxL3JlY29tbWVuZGF0aW9ucycsIHtcblx0XHRcdFx0aGVhZGVyczoge1xuXHRcdFx0XHRcdCdYLVdQLU5vbmNlJzogd2luZG93LndwQXBpU2V0dGluZ3M/Lm5vbmNlIHx8ICcnXG5cdFx0XHRcdH1cblx0XHRcdH0pXG5cdFx0XHRcdC50aGVuKGZ1bmN0aW9uIChyZXNwb25zZSkge1xuXHRcdFx0XHRcdGlmICghcmVzcG9uc2Uub2spIHtcblx0XHRcdFx0XHRcdHRocm93IG5ldyBFcnJvcignTmV0d29yayByZXNwb25zZSB3YXMgbm90IG9rJyk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdHJldHVybiByZXNwb25zZS5qc29uKCk7XG5cdFx0XHRcdH0pXG5cdFx0XHRcdC50aGVuKGZ1bmN0aW9uIChkYXRhKSB7XG5cdFx0XHRcdFx0dXBkYXRlUmVjb21tZW5kYXRpb25zV2lkZ2V0KGRhdGEpO1xuXHRcdFx0XHR9KVxuXHRcdFx0XHQuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcignRmFpbGVkIHRvIGZldGNoIHJlY29tbWVuZGF0aW9ucyBzdGF0dXM6JywgZXJyb3IpO1xuXHRcdFx0XHR9KTtcblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogTGlzdGVuIGZvciBHbG9iYWwgU2NvcmUgdXBkYXRlIGV2ZW50LlxuXHQgKiBUaGlzIGlzIGZpcmVkIGJ5IGFqYXguanMgd2hlbiB0aGUgR2xvYmFsIFNjb3JlIHBvbGxpbmcgZGV0ZWN0cyBhIGNoYW5nZS5cblx0ICovXG5cdCQoZG9jdW1lbnQpLm9uKCd3cHJHbG9iYWxTY29yZVVwZGF0ZWQgcm9ja2V0LWluc2lnaHRzLXBhZ2UtYWRkZWQgcm9ja2V0LWluc2lnaHRzLXBhZ2UtcmV0ZXN0JywgKCkgPT4ge1xuXHRcdGZldGNoUmVjb21tZW5kYXRpb25zU3RhdHVzKCk7XG5cdH0pXG59KTtcbiIsIiggKCBkb2N1bWVudCApID0+IHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdC8qKlxuXHQgKiBQb2xscyB0aGUgUm9ja2V0Q0ROIHN1YnNjcmlwdGlvbiBlbmRwb2ludCB1bnRpbCBpc19sb2FkaW5nIGJlY29tZXMgZmFsc2UuXG5cdCAqL1xuXHRjbGFzcyBSb2NrZXRDRE5TdWJzY3JpcHRpb25Qb2xsZXIge1xuXHRcdGNvbnN0cnVjdG9yKCkge1xuXHRcdFx0dGhpcy5wYXRoID0gJy93cC1yb2NrZXQvdjEvcm9ja2V0Y2RuL3N1YnNjcmlwdGlvbic7XG5cdFx0XHR0aGlzLnBvbGxJbnRlcnZhbCA9IDEwMDAwOyAvLyAxMCBzZWNvbmRzLlxuXHRcdFx0dGhpcy5tYXhSZXRyaWVzID0gNjA7IC8vIDEwIG1pbnV0ZXMgdG90YWwuXG5cdFx0XHR0aGlzLnRpbWVySWQgPSBudWxsO1xuXHRcdFx0dGhpcy5yZXRyeUNvdW50ID0gMDtcblxuXHRcdFx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ3JvY2tldENETlN1YnNjcmlwdGlvbkxvYWRpbmcnLCAoKSA9PiB0aGlzLnN0YXJ0KCkgKTtcblxuXHRcdFx0Ly8gUmUtdHJpZ2dlciBwb2xsaW5nIGFmdGVyIHBhZ2UgcmVmcmVzaC5cblx0XHRcdGNvbnN0IGNsYXNzZXMgPSBbXG5cdFx0XHRcdCcud3ByLWljb24tb3JhbmdlLWxvYWRlcicsXG5cdFx0XHRcdCcud3ByLWNkbi1idWlsdC1pbi0tZGlzYWJsZWQnLFxuXHRcdFx0XTtcblxuXHRcdFx0Y29uc3QgYWxsUHJlc2VudCA9IGNsYXNzZXMuZXZlcnkoIGNscyA9PiBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCBjbHMgKSAhPT0gbnVsbCApO1xuXG5cdFx0XHRpZiAoIGFsbFByZXNlbnQgKSB7XG5cdFx0XHRcdHRoaXMuc3RhcnQoKVxuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIFN0YXJ0cyBwb2xsaW5nLlxuXHRcdCAqL1xuXHRcdHN0YXJ0KCkge1xuXHRcdFx0aWYgKCB0aGlzLnRpbWVySWQgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0dGhpcy5yZXRyeUNvdW50ID0gMDtcblx0XHRcdHRoaXMucG9sbCgpO1xuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIFN0b3BzIHBvbGxpbmcuXG5cdFx0ICovXG5cdFx0c3RvcCgpIHtcblx0XHRcdGlmICggdGhpcy50aW1lcklkICkge1xuXHRcdFx0XHRjbGVhclRpbWVvdXQoIHRoaXMudGltZXJJZCApO1xuXHRcdFx0XHR0aGlzLnRpbWVySWQgPSBudWxsO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIFBlcmZvcm1zIGEgc2luZ2xlIHBvbGwgYW5kIHNjaGVkdWxlcyB0aGUgbmV4dCBvbmUgaWYgc3RpbGwgbG9hZGluZy5cblx0XHQgKi9cblx0XHRwb2xsKCkge1xuXHRcdFx0aWYgKCB0aGlzLnJldHJ5Q291bnQgPj0gdGhpcy5tYXhSZXRyaWVzICkge1xuXHRcdFx0XHR0aGlzLnN0b3AoKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHR0aGlzLnJldHJ5Q291bnQrKztcblxuXHRcdFx0d2luZG93LndwLmFwaUZldGNoKCB7XG5cdFx0XHRcdHBhdGg6IHRoaXMucGF0aCxcblx0XHRcdFx0bWV0aG9kOiAnR0VUJyxcblx0XHRcdH0gKS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0XHRpZiAoICEgcmVzcG9uc2UgfHwgISByZXNwb25zZS5pc19sb2FkaW5nICkge1xuXHRcdFx0XHRcdHRoaXMuc3RvcCgpO1xuXHRcdFx0XHRcdHdpbmRvdy5sb2NhdGlvbi5yZWxvYWQoKTtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblxuXHRcdFx0XHR0aGlzLnRpbWVySWQgPSBzZXRUaW1lb3V0KCAoKSA9PiB0aGlzLnBvbGwoKSwgdGhpcy5wb2xsSW50ZXJ2YWwgKTtcblx0XHRcdH0gKS5jYXRjaCggKCkgPT4ge1xuXHRcdFx0XHR0aGlzLnRpbWVySWQgPSBzZXRUaW1lb3V0KCAoKSA9PiB0aGlzLnBvbGwoKSwgdGhpcy5wb2xsSW50ZXJ2YWwgKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cdH1cblxuXHRuZXcgUm9ja2V0Q0ROU3Vic2NyaXB0aW9uUG9sbGVyKCk7XG59ICkoIGRvY3VtZW50ICk7IiwiLyplc2xpbnQtZW52IGVzNiwgYnJvd3NlciovXG4vKiBnbG9iYWwgTWljcm9Nb2RhbCwgbWl4cGFuZWwsIHJvY2tldF9taXhwYW5lbF9kYXRhLCByb2NrZXRfYWpheF9kYXRhLCBhamF4dXJsICovXG4oICggZG9jdW1lbnQsIHdpbmRvdyApID0+IHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdGNvbnN0IEJBTk5FUl9TVEFURSA9IHtcblx0XHRPUEVORUQ6IGZhbHNlLCAgICAvLyBCaWcgQ1RBIC0gb3BlbmVkIHN0YXRlXG5cdFx0Q09MTEFQU0VEOiB0cnVlICAgLy8gU21hbGwgQ1RBIC0gY29sbGFwc2VkIHN0YXRlXG5cdH07XG5cblx0Ly8gUmVnaXN0ZXIgZWFybHkgc28gd2UgY2F0Y2ggdGhlIHdwci1jZG4tc3RhdGUtY2hhbmdlIGV2ZW50LlxuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnd3ByLWNkbi1zdGF0ZS1jaGFuZ2UnLCB0cmFja0NETk1vZGVTZWxlY3Rpb24gKTtcblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ3JvY2tldENETkJhbm5lckF1dG9FeHBhbmRlZCcsICgpID0+IHRyYWNrUm9ja2V0Q0ROVXBzZWxsQmFubmVyRXhwYW5kZWQoJ2F1dG9fbGltaXRfcmVhY2hlZCcpICk7XG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdyb2NrZXRDRE5CYW5uZXJBdXRvQ29sbGFwc2VkJywgKCkgPT4gdHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJDb2xsYXBzZWQoICdhdXRvX2xpbWl0X3JlbGVhc2VkJyApICk7XG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdyb2NrZXRDRE5CYW5uZXJGaXJzdFZpc2libGUnLCAoKSA9PiB0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCggQkFOTkVSX1NUQVRFLkNPTExBUFNFRCApICk7XG5cblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ0RPTUNvbnRlbnRMb2FkZWQnLCAoKSA9PiB7XG5cdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggJy53cHItcm9ja2V0Y2RuLW9wZW4nICkuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdGVsLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0XHRjb25zdCBpc0NUQSA9IGVsLmNsYXNzTGlzdC5jb250YWlucyggJ3dwci1yb2NrZXRjZG4tcHJpY2luZy0tY3RhJyApO1xuXHRcdFx0XHRjaGVja0J1dHRvblVybEFuZE9wZW4oIGlzQ1RBICk7XG5cdFx0XHR9ICk7XG5cdFx0fSApO1xuXG5cdFx0Ly8gQWx3YXlzIGluaXRpYWxpemUgTWljcm9Nb2RhbCB0byBzZXQgdXAgY2xvc2UgaGFuZGxlcnNcblx0XHRNaWNyb01vZGFsLmluaXQoIHtcblx0XHRcdGRpc2FibGVTY3JvbGw6IHRydWVcblx0XHR9ICk7XG5cblx0XHRtYXliZU9wZW5Nb2RhbEZyb21VUkwoKTtcblxuXHRcdC8vIE9ubHkgYXV0by1vcGVuIG1vZGFsIGlmIHRoZXJlJ3Mgbm8gZGlyZWN0IGJ1dHRvbiBVUkxcblx0XHRpZiAoICEgd2luZG93LnJvY2tldGNkbkJ1dHRvblVybCB8fCB3aW5kb3cucm9ja2V0Y2RuQnV0dG9uVXJsID09PSAnJyApIHtcblx0XHRcdG1heWJlT3Blbk1vZGFsKCk7XG5cblx0XHRcdGNvbnN0IGlmcmFtZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyb2NrZXRjZG4taWZyYW1lJyk7XG5cdFx0XHRjb25zdCBsb2FkZXIgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3ByLXJvY2tldGNkbi1tb2RhbC1sb2FkZXInKTtcblx0XHRcdGlmICggaWZyYW1lICYmIGxvYWRlciApIHtcblx0XHRcdFx0aWZyYW1lLmFkZEV2ZW50TGlzdGVuZXIoJ2xvYWQnLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHRsb2FkZXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0XHRcdFx0fSk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9ICk7XG5cblx0LyoqXG5cdCAqIENoZWNrcyBpZiB0aGUgdXNlciBpcyBjdXJyZW50bHkgb24gdGhlIENETiB0YWIuXG5cdCAqXG5cdCAqIEByZXR1cm4ge2Jvb2xlYW59IFRydWUgaWYgb24gQ0ROIHRhYiwgZmFsc2Ugb3RoZXJ3aXNlLlxuXHQgKi9cblx0ZnVuY3Rpb24gaXNPbkNETlRhYigpIHtcblx0XHRyZXR1cm4gd2luZG93LmxvY2F0aW9uLmhhc2ggPT09ICcjcGFnZV9jZG4nO1xuXHR9XG5cblx0ZnVuY3Rpb24gbWF5YmVUcmFja0Jhbm5lclZpZXcoKSB7XG5cdFx0Y29uc3Qgc21hbGxDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhLXNtYWxsJyApO1xuXHRcdGNvbnN0IGJpZ0NUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jdGEnICk7XG5cblx0XHQvLyBPbmx5IHRyYWNrIGlmIG9uZSBvZiB0aGUgYmFubmVycyBpcyB2aXNpYmxlLlxuXHRcdGlmICggYmlnQ1RBICYmICEgYmlnQ1RBLmNsYXNzTGlzdC5jb250YWlucyggJ3dwci1pc0hpZGRlbicgKSApIHtcblx0XHRcdHRyYWNrUm9ja2V0Q0ROVXBzZWxsQmFubmVyVmlld2VkKCBCQU5ORVJfU1RBVEUuT1BFTkVEICk7XG5cdFx0fSBlbHNlIGlmICggc21hbGxDVEEgJiYgISBzbWFsbENUQS5jbGFzc0xpc3QuY29udGFpbnMoICd3cHItaXNIaWRkZW4nICkgKSB7XG5cdFx0XHR0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCggQkFOTkVSX1NUQVRFLkNPTExBUFNFRCApO1xuXHRcdH1cblx0fVxuXG5cdHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsICgpID0+IHtcblx0XHRsZXQgb3BlbkNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1vcGVuLWN0YScgKSxcblx0XHRcdGNsb3NlQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWNsb3NlLWN0YScgKSxcblx0XHRcdHNtYWxsQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YS1zbWFsbCcgKSxcblx0XHRcdGJpZ0NUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jdGEnICksXG5cdFx0XHRpbnB1dFRvZ2dsZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItcm9ja2V0Y2RuLXRvZ2dsZS0taW5wdXQnKTtcblxuXHRcdGNvbnN0IGN0YVRvZ2dsZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLXJvY2tldGNkbi1jdGEtdG9nZ2xlJyApO1xuXG5cdFx0LyoqXG5cdFx0ICogVG9nZ2xlcyBSb2NrZXRDRE4gQ1RBIGludGVybmFsIGNvbGxhcHNlZC9leHBhbmRlZCBzdGF0ZS5cblx0XHQgKlxuXHRcdCAqIEByZXR1cm4ge3ZvaWR9XG5cdFx0ICovXG5cdFx0ZnVuY3Rpb24gdG9nZ2xlQmlnQ1RBU3RhdGUoKSB7XG5cdFx0XHRpZiAoICEgYmlnQ1RBIHx8ICEgY3RhVG9nZ2xlLmxlbmd0aCApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBpc0NvbGxhcHNlZCA9IGJpZ0NUQS5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLXJvY2tldGNkbi1jdGEtLWNvbGxhcHNlZCcgKTtcblx0XHRcdGJpZ0NUQS5jbGFzc0xpc3QudG9nZ2xlKCAnd3ByLXJvY2tldGNkbi1jdGEtLWV4cGFuZGVkJywgISBpc0NvbGxhcHNlZCApO1xuXHRcdFx0Y3RhVG9nZ2xlLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRcdGVsLnNldEF0dHJpYnV0ZSggJ2FyaWEtZXhwYW5kZWQnLCBpc0NvbGxhcHNlZCA/ICdmYWxzZScgOiAndHJ1ZScgKTtcblx0XHRcdH0gKTtcblxuXHRcdFx0aWYoICEgaXNDb2xsYXBzZWQgKSB7XG5cdFx0XHRcdHRyYWNrUm9ja2V0Q0ROVXBzZWxsQmFubmVyRXhwYW5kZWQoICdtYW51YWwnICk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHR0cmFja1JvY2tldENETlVwc2VsbEJhbm5lckNvbGxhcHNlZCgpO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdGlmICggY3RhVG9nZ2xlLmxlbmd0aCAmJiBiaWdDVEEgKSB7XG5cdFx0XHRjdGFUb2dnbGUuZm9yRWFjaCggKCBlbCApID0+IHtcblx0XHRcdFx0ZWwuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgdG9nZ2xlQmlnQ1RBU3RhdGUgKTtcblx0XHRcdFx0ZWwuYWRkRXZlbnRMaXN0ZW5lciggJ2tleWRvd24nLCAoIGV2ZW50ICkgPT4ge1xuXHRcdFx0XHRcdGlmICggJ0VudGVyJyA9PT0gZXZlbnQua2V5IHx8ICcgJyA9PT0gZXZlbnQua2V5ICkge1xuXHRcdFx0XHRcdFx0ZXZlbnQucHJldmVudERlZmF1bHQoKTtcblx0XHRcdFx0XHRcdHRvZ2dsZUJpZ0NUQVN0YXRlKCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9ICk7XG5cdFx0XHR9ICk7XG5cdFx0fVxuXG5cdFx0Ly8gVHJhY2sgYmFubmVyIHZpZXcgb24gcGFnZSBsb2FkIGlmIGJhbm5lciBpcyB2aXNpYmxlIGFuZCB1c2VyIGlzIG9uIENETiB0YWIuXG5cdFx0bWF5YmVUcmFja0Jhbm5lclZpZXcoKTtcblxuXHRcdC8vIFRyYWNrIGJhbm5lciB2aWV3IHdoZW4gdXNlciBuYXZpZ2F0ZXMgdG8gQ0ROIHRhYi5cblx0XHR3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lciggJ2hhc2hjaGFuZ2UnLCAoKSA9PiB7XG5cdFx0XHRtYXliZVRyYWNrQmFubmVyVmlldygpO1xuXHRcdFx0dHJhY2tDRE5Nb2RlU2VsZWN0aW9uKCk7XG5cdFx0fSApO1xuXG5cdFx0Ly8gUHJpY2VzIHNlbGVjdG9ycyBmb3IgdG9nZ2xpbmcgdmlzaWJpbGl0eSBiYXNlZCBvbiB0aGUgYmlsbGluZyBjeWNsZSB0b2dnbGUgc3RhdGUuXG5cdFx0Y29uc3QgcHJpY2VzID0ge1xuXHRcdFx0bW9udGhseToge1xuXHRcdFx0XHRyZWd1bGFyOiBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLXJvY2tldGNkbi1wcmljaW5nLXJlZ3VsYXItcHJpY2UtLW1vbnRobHknKSxcblx0XHRcdFx0Y3VycmVudDogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy0tbW9udGhseScpLFxuXHRcdFx0XHRwZXJpb2Q6IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItcm9ja2V0Y2RuLXByaWNpbmctLWJpbGxpbmctcGVyaW9kLS1tb250aGx5Jylcblx0XHRcdH0sXG5cdFx0XHR5ZWFybHk6IHtcblx0XHRcdFx0cmVndWxhcjogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy1yZWd1bGFyLXByaWNlLS15ZWFybHknKSxcblx0XHRcdFx0Y3VycmVudDogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy0tYW5udWFsJyksXG5cdFx0XHRcdHBlcmlvZDogZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1yb2NrZXRjZG4tcHJpY2luZy0tYmlsbGluZy1wZXJpb2QtLXllYXJseScpXG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0Ly8gRGlzcGxheSB0aGUgY29ycmVjdCBwcmljZXMgb24gcGFnZSBiYXNlZCBvbiBiaWxsaW5nIGN5Y2xlIHRvZ2dsZSBzdGF0ZS5cblx0XHRpZiAoIGlucHV0VG9nZ2xlICkge1xuXHRcdFx0aW5wdXRUb2dnbGUuYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRjb25zdCBpc1llYXJseSA9IHRoaXMuY2hlY2tlZDtcblxuXHRcdFx0XHRpZiAoaXNZZWFybHkpIHtcblx0XHRcdFx0XHRPYmplY3QudmFsdWVzKHByaWNlcy5tb250aGx5KS5mb3JFYWNoKGxpc3QgPT4gbGlzdC5mb3JFYWNoKGVsID0+IGVsLmNsYXNzTGlzdC5hZGQoJ3dwci1pc0hpZGRlbicpKSk7XG5cdFx0XHRcdFx0T2JqZWN0LnZhbHVlcyhwcmljZXMueWVhcmx5KS5mb3JFYWNoKGxpc3QgPT4gbGlzdC5mb3JFYWNoKGVsID0+IGVsLmNsYXNzTGlzdC5yZW1vdmUoJ3dwci1pc0hpZGRlbicpKSk7XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0T2JqZWN0LnZhbHVlcyhwcmljZXMubW9udGhseSkuZm9yRWFjaChsaXN0ID0+IGxpc3QuZm9yRWFjaChlbCA9PiBlbC5jbGFzc0xpc3QucmVtb3ZlKCd3cHItaXNIaWRkZW4nKSkpO1xuXHRcdFx0XHRcdE9iamVjdC52YWx1ZXMocHJpY2VzLnllYXJseSkuZm9yRWFjaChsaXN0ID0+IGxpc3QuZm9yRWFjaChlbCA9PiBlbC5jbGFzc0xpc3QuYWRkKCd3cHItaXNIaWRkZW4nKSkpO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBidXR0b24gVVJMIHdpdGggdGhlIGNvcnJlY3QgaXNfbW9udGhseSBwYXJhbWV0ZXIuXG5cdFx0XHRcdHVwZGF0ZUJ1dHRvblVybEJpbGxpbmdDeWNsZShpc1llYXJseSk7XG5cdFx0XHR9KTtcblx0XHR9XG5cblx0XHQvLyBUcmFjayBSb2NrZXRDRE4gYWN0aXZhdGlvbiBmYWlsZWQgQ1RBIGNsaWNrXG5cdFx0Y29uc3QgYWN0aXZhdGlvbkNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyN3cHItcm9ja2V0Y2RuLWFjdGl2YXRpb24tY3RhJyk7XG5cdFx0aWYgKGFjdGl2YXRpb25DVEEpIHtcblx0XHRcdGFjdGl2YXRpb25DVEEuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PiB7XG5cdFx0XHRcdHRyYWNrUm9ja2V0Q0ROQWN0aXZhdGlvbkNUQSgpO1xuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdH0gKTtcblxuXHR3aW5kb3cub25tZXNzYWdlID0gKCBlICkgPT4ge1xuXHRcdGNvbnN0IGlmcmFtZVVSTCA9IHJvY2tldF9hamF4X2RhdGEub3JpZ2luX3VybDtcblxuXHRcdGlmICggZS5vcmlnaW4gIT09IGlmcmFtZVVSTCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRzZXRDRE5GcmFtZUhlaWdodCggZS5kYXRhICk7XG5cdFx0Y2xvc2VNb2RhbCggZS5kYXRhICk7XG5cdFx0dG9rZW5IYW5kbGVyKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHByb2Nlc3NTdGF0dXMoIGUuZGF0YSApO1xuXHRcdGVuYWJsZUNETiggZS5kYXRhLCBpZnJhbWVVUkwgKTtcblx0XHRkaXNhYmxlQ0ROKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZS5kYXRhICk7XG5cdH07XG5cblx0ZnVuY3Rpb24gb3BlblJvY2tldENETk1vZGFsKCkge1xuXHRcdGNvbnN0IHJvY2tldGNkbklmcmFtZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyb2NrZXRjZG4taWZyYW1lJyk7XG5cdFx0aWYgKCAhcm9ja2V0Y2RuSWZyYW1lIHx8ICFyb2NrZXRjZG5JZnJhbWUuZGF0YXNldCB8fCAhcm9ja2V0Y2RuSWZyYW1lLmRhdGFzZXQuc3JjIHx8IHJvY2tldGNkbklmcmFtZS5kYXRhc2V0LnNyYyA9PT0gcm9ja2V0Y2RuSWZyYW1lLnNyYyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0cm9ja2V0Y2RuSWZyYW1lLnNyYyA9IHJvY2tldGNkbklmcmFtZS5kYXRhc2V0LnNyYztcblx0XHRNaWNyb01vZGFsLnNob3coICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXHR9XG5cblx0ZnVuY3Rpb24gY2hlY2tCdXR0b25VcmxBbmRPcGVuKCBpc0NUQSApIHtcblx0XHRsZXQgaWZyYW1lVmlzaXQgPSAhd2luZG93LnJvY2tldGNkbkJ1dHRvblVybDtcblx0XHQvLyBUcmFjayBDVEEgY2xpY2sgaWYgdGhpcyBpcyB0aGUgcHJpY2luZyBDVEEgYnV0dG9uLlxuXHRcdGlmICggaXNDVEEgKSB7XG5cdFx0XHR0cmFja1JvY2tldENETlVwc2VsbENUQUNsaWNrZWQoaWZyYW1lVmlzaXQpO1xuXHRcdH1cblxuXHRcdC8vIENoZWNrIGlmIGJ1dHRvbiBVUkwgd2FzIGluamVjdGVkIGJ5IFBIUFxuXHRcdGlmICggIWlmcmFtZVZpc2l0ICkge1xuXHRcdFx0Ly8gU21hbGwgZGVsYXkgdG8gZW5zdXJlIE1peHBhbmVsIGV2ZW50IGlzIHNlbnQgYmVmb3JlIG5hdmlnYXRpb25cblx0XHRcdHNldFRpbWVvdXQoIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR3aW5kb3cubG9jYXRpb24uaHJlZiA9IHdpbmRvdy5yb2NrZXRjZG5CdXR0b25Vcmw7XG5cdFx0XHR9LCAxMDAgKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0Ly8gU2hvdyBpZnJhbWUgbW9kYWwgYXMgdXN1YWxcblx0XHRcdG9wZW5Sb2NrZXRDRE5Nb2RhbCgpO1xuXHRcdH1cblx0fVxuXG5cdC8qKlxuXHQgKiBVcGRhdGVzIHRoZSBidXR0b24gVVJMIHdpdGggdGhlIGNvcnJlY3QgaXNfbW9udGhseSBwYXJhbWV0ZXIgYmFzZWQgb24gYmlsbGluZyBjeWNsZSB0b2dnbGUuXG5cdCAqXG5cdCAqIEBwYXJhbSB7Ym9vbGVhbn0gaXNZZWFybHkgLSBUcnVlIGlmIHllYXJseSBiaWxsaW5nIGlzIHNlbGVjdGVkLCBmYWxzZSBmb3IgbW9udGhseS5cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUJ1dHRvblVybEJpbGxpbmdDeWNsZSggaXNZZWFybHkgKSB7XG5cdFx0aWYgKCAhIHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwgfHwgd2luZG93LnJvY2tldGNkbkJ1dHRvblVybCA9PT0gJycgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0d2luZG93LnJvY2tldGNkbkJ1dHRvblVybCA9IHNldElzTW9udGhseVBhcmFtKHdpbmRvdy5yb2NrZXRjZG5CdXR0b25VcmwsIGlzWWVhcmx5KTtcblx0fVxuXG5cdGZ1bmN0aW9uIG1heWJlT3Blbk1vZGFsKCkge1xuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fcHJvY2Vzc19zdGF0dXMnO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXG5cdFx0cmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSAoKSA9PiB7XG5cdFx0XHRpZiAoIHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gWE1MSHR0cFJlcXVlc3QuRE9ORSAmJiAyMDAgPT09IHJlcXVlc3Quc3RhdHVzICkge1xuXHRcdFx0XHRsZXQgcmVzcG9uc2VUeHQgPSBKU09OLnBhcnNlKHJlcXVlc3QucmVzcG9uc2VUZXh0KTtcblxuXHRcdFx0XHRpZiAoIHRydWUgPT09IHJlc3BvbnNlVHh0LnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0b3BlblJvY2tldENETk1vZGFsKCk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gbWF5YmVPcGVuTW9kYWxGcm9tVVJMKCkge1xuXHRcdGNvbnN0IHVybFBhcmFtcyA9IG5ldyBVUkxTZWFyY2hQYXJhbXMoIHdpbmRvdy5sb2NhdGlvbi5zZWFyY2ggKTtcblxuXHRcdGlmICggdXJsUGFyYW1zLmhhcyggJ3JvY2tldGNkbl9vcGVuX2lmcmFtZScgKSAmJiAnMScgPT09IHVybFBhcmFtcy5nZXQoICdyb2NrZXRjZG5fb3Blbl9pZnJhbWUnICkgKSB7XG5cdFx0XHQvLyBTZXQgaGFzaCB0byBwYWdlX2NkbiB0byBzaG93IENETiB0YWIgYmVoaW5kIG1vZGFsXG5cdFx0XHR3aW5kb3cubG9jYXRpb24uaGFzaCA9ICcjcGFnZV9jZG4nO1xuXG5cdFx0XHRvcGVuUm9ja2V0Q0ROTW9kYWwoKTtcblxuXHRcdFx0Ly8gQ2xlYW4gdXAgdGhlIFVSTCB0byBwcmV2ZW50IHJlLXRyaWdnZXJpbmcgb24gcmVmcmVzaFxuXHRcdFx0dXJsUGFyYW1zLmRlbGV0ZSggJ3JvY2tldGNkbl9vcGVuX2lmcmFtZScgKTtcblx0XHRcdGNvbnN0IHNlYXJjaCA9IHVybFBhcmFtcy50b1N0cmluZygpO1xuXHRcdFx0Y29uc3QgbmV3VVJMID0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICsgKCBzZWFyY2ggPyAnPycgKyBzZWFyY2ggOiAnJyApICsgd2luZG93LmxvY2F0aW9uLmhhc2g7XG5cdFx0XHR3aW5kb3cuaGlzdG9yeS5yZXBsYWNlU3RhdGUoIHt9LCAnJywgbmV3VVJMICk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gY2xvc2VNb2RhbCggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2NkbkZyYW1lQ2xvc2UnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0TWljcm9Nb2RhbC5jbG9zZSggJ3dwci1yb2NrZXRjZG4tbW9kYWwnICk7XG5cdFx0Ly8gRW5zdXJlIHNjcm9sbCBpcyByZXN0b3JlZFxuXHRcdGRvY3VtZW50LmJvZHkuc3R5bGUub3ZlcmZsb3cgPSAnJztcblxuXHRcdGxldCBwYWdlcyA9IFsgJ2lmcmFtZS1wYXltZW50LXN1Y2Nlc3MnLCAnaWZyYW1lLXVuc3Vic2NyaWJlLXN1Y2Nlc3MnIF07XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2Nkbl9wYWdlX21lc3NhZ2UnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0aWYgKCBwYWdlcy5pbmRleE9mKCBkYXRhLmNkbl9wYWdlX21lc3NhZ2UgKSA9PT0gLTEgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0ZG9jdW1lbnQubG9jYXRpb24ucmVsb2FkKCk7XG5cdH1cblxuXHRmdW5jdGlvbiBwcm9jZXNzU3RhdHVzKCBkYXRhICkge1xuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3Byb2Nlc3MnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9wcm9jZXNzX3NldCc7XG5cdFx0cG9zdERhdGEgKz0gJyZzdGF0dXM9JyArIGRhdGEucm9ja2V0Y2RuX3Byb2Nlc3M7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblx0fVxuXG5cdGZ1bmN0aW9uIGVuYWJsZUNETiggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3VybCcgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX2VuYWJsZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdXJsPScgKyBkYXRhLnJvY2tldGNkbl91cmw7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiBzZXRJc01vbnRobHlQYXJhbSh1cmwsIGlzWWVhcmx5KSB7XG5cdFx0Ly8gUmVtb3ZlIGFueSBleGlzdGluZyBpc19tb250aGx5IHBhcmFtXG5cdFx0bGV0IG5ld1VybCA9IHVybC5yZXBsYWNlKC8oWz8mXSlpc19tb250aGx5PVteJl0qL2csICcnKTtcblx0XHQvLyBBZGQgdGhlIG5ldyBwYXJhbVxuXHRcdGNvbnN0IHNlcCA9IG5ld1VybC5pbmNsdWRlcygnPycpID8gJyYnIDogJz8nO1xuXHRcdG5ld1VybCArPSBzZXAgKyAnaXNfbW9udGhseT0nICsgKGlzWWVhcmx5ID8gJzAnIDogJzEnKTtcblx0XHRyZXR1cm4gbmV3VXJsO1xuXHR9XG5cblx0ZnVuY3Rpb24gZGlzYWJsZUNETiggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX2Rpc2FibGUnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9kaXNhYmxlJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKSB7XG5cdFx0Y29uc3QgaHR0cFJlcXVlc3QgPSBuZXcgWE1MSHR0cFJlcXVlc3QoKTtcblxuXHRcdGh0dHBSZXF1ZXN0Lm9wZW4oICdQT1NUJywgYWpheHVybCApO1xuXHRcdGh0dHBSZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoICdDb250ZW50LVR5cGUnLCAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJyApO1xuXHRcdGh0dHBSZXF1ZXN0LnNlbmQoIHBvc3REYXRhICk7XG5cblx0XHRyZXR1cm4gaHR0cFJlcXVlc3Q7XG5cdH1cblxuXHRmdW5jdGlvbiBzZXRDRE5GcmFtZUhlaWdodCggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2NkbkZyYW1lSGVpZ2h0JyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAncm9ja2V0Y2RuLWlmcmFtZScgKS5zdHlsZS5oZWlnaHQgPSBgJHsgZGF0YS5jZG5GcmFtZUhlaWdodCB9cHhgO1xuXHR9XG5cblx0ZnVuY3Rpb24gdG9rZW5IYW5kbGVyKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdG9rZW4nICkgKSB7XG5cdFx0XHRsZXQgZGF0YSA9IHtwcm9jZXNzOlwic3Vic2NyaWJlXCIsIG1lc3NhZ2U6XCJ0b2tlbl9ub3RfcmVjZWl2ZWRcIn07XG5cdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdHtcblx0XHRcdFx0XHQnc3VjY2Vzcyc6IGZhbHNlLFxuXHRcdFx0XHRcdCdkYXRhJzogZGF0YSxcblx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHR9LFxuXHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdCk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXNhdmVfcm9ja2V0Y2RuX3Rva2VuJztcblx0XHRwb3N0RGF0YSArPSAnJnZhbHVlPScgKyBkYXRhLnJvY2tldGNkbl90b2tlbjtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl92YWxpZGF0ZV90b2tlbicgKSB8fCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdmFsaWRhdGVfY25hbWUnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl92YWxpZGF0ZV90b2tlbl9jbmFtZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdXJsPScgKyBkYXRhLnJvY2tldGNkbl92YWxpZGF0ZV9jbmFtZTtcblx0XHRwb3N0RGF0YSArPSAnJmNkbl90b2tlbj0nICsgZGF0YS5yb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW47XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cdH1cblxuXHQvKipcblx0ICogVHJhY2tzIENETiBtb2RlIHNlbGVjdGlvbiB3aXRoIE1peHBhbmVsLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tDRE5Nb2RlU2VsZWN0aW9uKCkge1xuXHRcdGlmICggISBpc09uQ0ROVGFiKCkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Y29uc3QgYWN0aXZlVGFiID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cHItY2RuLXRhYnNfX3RhYi0tYWN0aXZlJyApO1xuXHRcdFxuXHRcdGlmICggISBhY3RpdmVUYWIgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Y29uc3QgY2RuTW9kZSA9IGFjdGl2ZVRhYi5nZXRBdHRyaWJ1dGUoICdkYXRhLWNkbi1tb2RlJyApXG5cblx0XHRpZiggISBjZG5Nb2RlICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH0gXG5cblx0XHRpZiAoIHR5cGVvZiBtaXhwYW5lbCA9PT0gJ3VuZGVmaW5lZCcgfHwgIW1peHBhbmVsLnRyYWNrICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIENoZWNrIGlmIHVzZXIgaGFzIG9wdGVkIGluXG5cdFx0aWYgKCB0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnIHx8ICFyb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkIHx8IHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgPT09ICcwJyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBJZGVudGlmeSB1c2VyIGlmIGF2YWlsYWJsZVxuXHRcdGlmIChyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkICYmIHR5cGVvZiBtaXhwYW5lbC5pZGVudGlmeSA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0bWl4cGFuZWwuaWRlbnRpZnkocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCk7XG5cdFx0fVxuXG5cdFx0bWl4cGFuZWwudHJhY2soJ1JvY2tldENETiBNb2RlJywge1xuXHRcdFx0Y29udGV4dDogcm9ja2V0X21peHBhbmVsX2RhdGEuY29udGV4dCxcblx0XHRcdHBsdWdpbjogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0YnJhbmQ6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuXHRcdFx0YXBwbGljYXRpb246IHJvY2tldF9taXhwYW5lbF9kYXRhLmFwcCxcblx0XHRcdGNkbl9tb2RlOiBjZG5Nb2RlXG5cdFx0fSk7XG5cdH1cblxuXHQvKipcblx0ICogVHJhY2tzIFJvY2tldENETiBhY3RpdmF0aW9uIGZhaWxlZCBDVEEgY2xpY2sgd2l0aCBNaXhwYW5lbC5cblx0ICovXG5cdGZ1bmN0aW9uIHRyYWNrUm9ja2V0Q0ROQWN0aXZhdGlvbkNUQSgpIHtcblx0XHRpZiAodHlwZW9mIG1peHBhbmVsID09PSAndW5kZWZpbmVkJyB8fCAhbWl4cGFuZWwudHJhY2spIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHQvLyBDaGVjayBpZiB1c2VyIGhhcyBvcHRlZCBpblxuXHRcdGlmICh0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnIHx8ICFyb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkIHx8IHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgPT09ICcwJykge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIElkZW50aWZ5IHVzZXIgaWYgYXZhaWxhYmxlXG5cdFx0aWYgKHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQgJiYgdHlwZW9mIG1peHBhbmVsLmlkZW50aWZ5ID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRtaXhwYW5lbC5pZGVudGlmeShyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkKTtcblx0XHR9XG5cblx0XHRtaXhwYW5lbC50cmFjaygnUm9ja2V0Q0ROIEFjdGl2YXRpb24gRmFpbGVkIENUQSBDbGlja2VkJywge1xuXHRcdFx0Y29udGV4dDogcm9ja2V0X21peHBhbmVsX2RhdGEuY29udGV4dCxcblx0XHRcdHBsdWdpbjogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0YnJhbmQ6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuXHRcdFx0YXBwbGljYXRpb246IHJvY2tldF9taXhwYW5lbF9kYXRhLmFwcFxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBhIFJvY2tldENETiB1cHNlbGwgZXZlbnQgd2l0aCBNaXhwYW5lbC5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IGV2ZW50TmFtZSAgIFRoZSBNaXhwYW5lbCBldmVudCBuYW1lLlxuXHQgKiBAcGFyYW0ge09iamVjdH0gW2V4dHJhUHJvcHNdIE9wdGlvbmFsIGFkZGl0aW9uYWwgcHJvcGVydGllcyB0byBtZXJnZS5cblx0ICovXG5cdGZ1bmN0aW9uIHRyYWNrUm9ja2V0Q0ROVXBzZWxsTWl4cGFuZWxFdmVudCggZXZlbnROYW1lLCBleHRyYVByb3BzICkge1xuXHRcdGlmICggdHlwZW9mIG1peHBhbmVsID09PSAndW5kZWZpbmVkJyB8fCAhIG1peHBhbmVsLnRyYWNrICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIENoZWNrIGlmIHVzZXIgaGFzIG9wdGVkIGluLlxuXHRcdGlmICggdHlwZW9mIHJvY2tldF9taXhwYW5lbF9kYXRhID09PSAndW5kZWZpbmVkJyB8fCAhIHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgfHwgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9PT0gJzAnICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIElkZW50aWZ5IHVzZXIgaWYgYXZhaWxhYmxlLlxuXHRcdGlmICggISByb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkIHx8IHR5cGVvZiBtaXhwYW5lbC5pZGVudGlmeSAhPT0gJ2Z1bmN0aW9uJyApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRtaXhwYW5lbC5pZGVudGlmeSggcm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCApO1xuXG5cdFx0dmFyIHByb3BzID0ge1xuXHRcdFx0Y29udGV4dDogcm9ja2V0X21peHBhbmVsX2RhdGEuY29udGV4dCxcblx0XHRcdHBsdWdpbjogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuXHRcdFx0YnJhbmQ6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuXHRcdFx0YXBwbGljYXRpb246IHJvY2tldF9taXhwYW5lbF9kYXRhLmFwcCxcblx0XHRcdHBhdGg6IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGhcblx0XHR9O1xuXG5cdFx0Ly8gTWVyZ2UgZXh0cmEgcHJvcGVydGllcyBpZiBwcm92aWRlZCBhbmQgdmFsaWQuXG5cdFx0aWYgKCBleHRyYVByb3BzICYmIHR5cGVvZiBleHRyYVByb3BzID09PSAnb2JqZWN0JyApIHtcblx0XHRcdGZvciAoIHZhciBrZXkgaW4gZXh0cmFQcm9wcyApIHtcblx0XHRcdFx0aWYgKCBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwoIGV4dHJhUHJvcHMsIGtleSApICkge1xuXHRcdFx0XHRcdHByb3BzWyBrZXkgXSA9IGV4dHJhUHJvcHNbIGtleSBdO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0bWl4cGFuZWwudHJhY2soIGV2ZW50TmFtZSwgcHJvcHMgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3MgUm9ja2V0Q0ROIHVwc2VsbCBiYW5uZXIgdmlldyB3aXRoIE1peHBhbmVsLlxuXHQgKlxuXHQgKiBAcGFyYW0ge2Jvb2xlYW59IFtpc19jb2xsYXBzZWQ9ZmFsc2VdIFdoZXRoZXIgdGhlIHNtYWxsIGJhbm5lciB2YXJpYW50IGlzIGRpc3BsYXllZCwgU2VuZHMgYGNvbGxhcHNlZGAgd2hlbiB0cnVlLCBvdGhlcndpc2UgYG9wZW5lZGAuXG5cdCAqL1xuXHRmdW5jdGlvbiB0cmFja1JvY2tldENETlVwc2VsbEJhbm5lclZpZXdlZCggaXNfY29sbGFwc2VkID0gZmFsc2UgKSB7XG5cdFx0aWYgKCAhIGlzT25DRE5UYWIoKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0Y29uc3QgaGFzaCA9IHdpbmRvdy5sb2NhdGlvbi5oYXNoO1xuXHRcdGNvbnN0IGJhc2VQYXRoID0gKCB0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgIT09ICd1bmRlZmluZWQnICYmIHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGggKSA/IHJvY2tldF9taXhwYW5lbF9kYXRhLnBhdGggOiAnJztcblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIEJhbm5lciBWaWV3ZWQnLCB7XG5cdFx0XHRzdGF0ZTogICAgIGlzX2NvbGxhcHNlZCA/ICdjb2xsYXBzZWQnIDogJ29wZW5lZCcsXG5cdFx0XHRwYWdlX25hbWU6IGhhc2gsXG5cdFx0XHRwYXRoOiAgICAgIGJhc2VQYXRoICsgaGFzaFxuXHRcdH0gKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBUcmFja3MgUm9ja2V0Q0ROIHVwc2VsbCBiYW5uZXIgZXhwYW5kZWQgd2l0aCBNaXhwYW5lbC5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHRyaWdnZXIgJ21hbnVhbCcgYnkgZGVmYXVsdC5cblx0ICovXG5cdGZ1bmN0aW9uIHRyYWNrUm9ja2V0Q0ROVXBzZWxsQmFubmVyRXhwYW5kZWQoIHRyaWdnZXIgKSB7XG5cdFx0dHJhY2tSb2NrZXRDRE5VcHNlbGxNaXhwYW5lbEV2ZW50KCAnUm9ja2V0Q0ROIFVwc2VsbCBCYW5uZXIgRXhwYW5kZWQnLCB7XG5cdFx0XHRsb2NhdGlvbjogd2luZG93LmxvY2F0aW9uLmhhc2gsXG5cdFx0XHR0cmlnZ2VyOiB0cmlnZ2VyXG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBSb2NrZXRDRE4gdXBzZWxsIGJhbm5lciBjb2xsYXBzZWQgd2l0aCBNaXhwYW5lbC5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IFt0cmlnZ2VyPSdtYW51YWwnXSAnbWFudWFsJyB3aGVuIHVzZXIgY2xpY2tzIHRvZ2dsZSwgJ2F1dG9fbGltaXRfcmVsZWFzZWQnIHdoZW4gYSBwYWdlIGRlbGV0aW9uIGRyb3BzIGNvdW50IGJlbG93IGxpbWl0LlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5VcHNlbGxCYW5uZXJDb2xsYXBzZWQoIHRyaWdnZXIgPSAnbWFudWFsJyApIHtcblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIEJhbm5lciBDb2xsYXBzZWQnLCB7XG5cdFx0XHRsb2NhdGlvbjogd2luZG93LmxvY2F0aW9uLmhhc2gsXG5cdFx0XHR0cmlnZ2VyOiB0cmlnZ2VyXG5cdFx0fSApO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRyYWNrcyBSb2NrZXRDRE4gdXBzZWxsIENUQSBjbGljayB3aXRoIE1peHBhbmVsLlxuXHQgKi9cblx0ZnVuY3Rpb24gdHJhY2tSb2NrZXRDRE5VcHNlbGxDVEFDbGlja2VkKCBpZnJhbWVWaXNpdCA9IGZhbHNlICkge1xuXHRcdGNvbnN0IHRhYmxlTGlzdCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3ByLWNkbi1idWlsdC1pbiAud3ByLXRhYmxlLWxpc3QnICk7XG5cdFx0Y29uc3QgcGFnZXNDb3VudCA9IHRhYmxlTGlzdCA/IHRhYmxlTGlzdC5xdWVyeVNlbGVjdG9yQWxsKCAnW2RhdGEtaWRdJyApLmxlbmd0aCA6IDA7XG5cblx0XHR0cmFja1JvY2tldENETlVwc2VsbE1peHBhbmVsRXZlbnQoICdSb2NrZXRDRE4gVXBzZWxsIENUQSBDbGlja2VkJywge1xuXHRcdFx0ZGVzdGluYXRpb246IGlmcmFtZVZpc2l0ID8gJ2lmcmFtZScgOiAnZXhwcmVzcy1jaGVja291dCcsXG5cdFx0XHRwYWdlc19jb3VudDogcGFnZXNDb3VudFxuXHRcdH0gKTtcblx0fVxufSApKCBkb2N1bWVudCwgd2luZG93ICk7XG4iLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJUaW1lbGluZUxpdGVcIixbXCJjb3JlLkFuaW1hdGlvblwiLFwiY29yZS5TaW1wbGVUaW1lbGluZVwiLFwiVHdlZW5MaXRlXCJdLGZ1bmN0aW9uKHQsZSxpKXt2YXIgcz1mdW5jdGlvbih0KXtlLmNhbGwodGhpcyx0KSx0aGlzLl9sYWJlbHM9e30sdGhpcy5hdXRvUmVtb3ZlQ2hpbGRyZW49dGhpcy52YXJzLmF1dG9SZW1vdmVDaGlsZHJlbj09PSEwLHRoaXMuc21vb3RoQ2hpbGRUaW1pbmc9dGhpcy52YXJzLnNtb290aENoaWxkVGltaW5nPT09ITAsdGhpcy5fc29ydENoaWxkcmVuPSEwLHRoaXMuX29uVXBkYXRlPXRoaXMudmFycy5vblVwZGF0ZTt2YXIgaSxzLHI9dGhpcy52YXJzO2ZvcihzIGluIHIpaT1yW3NdLGEoaSkmJi0xIT09aS5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIikmJihyW3NdPXRoaXMuX3N3YXBTZWxmSW5QYXJhbXMoaSkpO2Eoci50d2VlbnMpJiZ0aGlzLmFkZChyLnR3ZWVucywwLHIuYWxpZ24sci5zdGFnZ2VyKX0scj0xZS0xMCxuPWkuX2ludGVybmFscy5pc1NlbGVjdG9yLGE9aS5faW50ZXJuYWxzLmlzQXJyYXksbz1bXSxoPXdpbmRvdy5fZ3NEZWZpbmUuZ2xvYmFscyxsPWZ1bmN0aW9uKHQpe3ZhciBlLGk9e307Zm9yKGUgaW4gdClpW2VdPXRbZV07cmV0dXJuIGl9LF89ZnVuY3Rpb24odCxlLGkscyl7dC5fdGltZWxpbmUucGF1c2UodC5fc3RhcnRUaW1lKSxlJiZlLmFwcGx5KHN8fHQuX3RpbWVsaW5lLGl8fG8pfSx1PW8uc2xpY2UsZj1zLnByb3RvdHlwZT1uZXcgZTtyZXR1cm4gcy52ZXJzaW9uPVwiMS4xMi4xXCIsZi5jb25zdHJ1Y3Rvcj1zLGYua2lsbCgpLl9nYz0hMSxmLnRvPWZ1bmN0aW9uKHQsZSxzLHIpe3ZhciBuPXMucmVwZWF0JiZoLlR3ZWVuTWF4fHxpO3JldHVybiBlP3RoaXMuYWRkKG5ldyBuKHQsZSxzKSxyKTp0aGlzLnNldCh0LHMscil9LGYuZnJvbT1mdW5jdGlvbih0LGUscyxyKXtyZXR1cm4gdGhpcy5hZGQoKHMucmVwZWF0JiZoLlR3ZWVuTWF4fHxpKS5mcm9tKHQsZSxzKSxyKX0sZi5mcm9tVG89ZnVuY3Rpb24odCxlLHMscixuKXt2YXIgYT1yLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aTtyZXR1cm4gZT90aGlzLmFkZChhLmZyb21Ubyh0LGUscyxyKSxuKTp0aGlzLnNldCh0LHIsbil9LGYuc3RhZ2dlclRvPWZ1bmN0aW9uKHQsZSxyLGEsbyxoLF8sZil7dmFyIHAsYz1uZXcgcyh7b25Db21wbGV0ZTpoLG9uQ29tcGxldGVQYXJhbXM6XyxvbkNvbXBsZXRlU2NvcGU6ZixzbW9vdGhDaGlsZFRpbWluZzp0aGlzLnNtb290aENoaWxkVGltaW5nfSk7Zm9yKFwic3RyaW5nXCI9PXR5cGVvZiB0JiYodD1pLnNlbGVjdG9yKHQpfHx0KSxuKHQpJiYodD11LmNhbGwodCwwKSksYT1hfHwwLHA9MDt0Lmxlbmd0aD5wO3ArKylyLnN0YXJ0QXQmJihyLnN0YXJ0QXQ9bChyLnN0YXJ0QXQpKSxjLnRvKHRbcF0sZSxsKHIpLHAqYSk7cmV0dXJuIHRoaXMuYWRkKGMsbyl9LGYuc3RhZ2dlckZyb209ZnVuY3Rpb24odCxlLGkscyxyLG4sYSxvKXtyZXR1cm4gaS5pbW1lZGlhdGVSZW5kZXI9MCE9aS5pbW1lZGlhdGVSZW5kZXIsaS5ydW5CYWNrd2FyZHM9ITAsdGhpcy5zdGFnZ2VyVG8odCxlLGkscyxyLG4sYSxvKX0sZi5zdGFnZ2VyRnJvbVRvPWZ1bmN0aW9uKHQsZSxpLHMscixuLGEsbyxoKXtyZXR1cm4gcy5zdGFydEF0PWkscy5pbW1lZGlhdGVSZW5kZXI9MCE9cy5pbW1lZGlhdGVSZW5kZXImJjAhPWkuaW1tZWRpYXRlUmVuZGVyLHRoaXMuc3RhZ2dlclRvKHQsZSxzLHIsbixhLG8saCl9LGYuY2FsbD1mdW5jdGlvbih0LGUscyxyKXtyZXR1cm4gdGhpcy5hZGQoaS5kZWxheWVkQ2FsbCgwLHQsZSxzKSxyKX0sZi5zZXQ9ZnVuY3Rpb24odCxlLHMpe3JldHVybiBzPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwocywwLCEwKSxudWxsPT1lLmltbWVkaWF0ZVJlbmRlciYmKGUuaW1tZWRpYXRlUmVuZGVyPXM9PT10aGlzLl90aW1lJiYhdGhpcy5fcGF1c2VkKSx0aGlzLmFkZChuZXcgaSh0LDAsZSkscyl9LHMuZXhwb3J0Um9vdD1mdW5jdGlvbih0LGUpe3Q9dHx8e30sbnVsbD09dC5zbW9vdGhDaGlsZFRpbWluZyYmKHQuc21vb3RoQ2hpbGRUaW1pbmc9ITApO3ZhciByLG4sYT1uZXcgcyh0KSxvPWEuX3RpbWVsaW5lO2ZvcihudWxsPT1lJiYoZT0hMCksby5fcmVtb3ZlKGEsITApLGEuX3N0YXJ0VGltZT0wLGEuX3Jhd1ByZXZUaW1lPWEuX3RpbWU9YS5fdG90YWxUaW1lPW8uX3RpbWUscj1vLl9maXJzdDtyOyluPXIuX25leHQsZSYmciBpbnN0YW5jZW9mIGkmJnIudGFyZ2V0PT09ci52YXJzLm9uQ29tcGxldGV8fGEuYWRkKHIsci5fc3RhcnRUaW1lLXIuX2RlbGF5KSxyPW47cmV0dXJuIG8uYWRkKGEsMCksYX0sZi5hZGQ9ZnVuY3Rpb24ocixuLG8saCl7dmFyIGwsXyx1LGYscCxjO2lmKFwibnVtYmVyXCIhPXR5cGVvZiBuJiYobj10aGlzLl9wYXJzZVRpbWVPckxhYmVsKG4sMCwhMCxyKSksIShyIGluc3RhbmNlb2YgdCkpe2lmKHIgaW5zdGFuY2VvZiBBcnJheXx8ciYmci5wdXNoJiZhKHIpKXtmb3Iobz1vfHxcIm5vcm1hbFwiLGg9aHx8MCxsPW4sXz1yLmxlbmd0aCx1PTA7Xz51O3UrKylhKGY9clt1XSkmJihmPW5ldyBzKHt0d2VlbnM6Zn0pKSx0aGlzLmFkZChmLGwpLFwic3RyaW5nXCIhPXR5cGVvZiBmJiZcImZ1bmN0aW9uXCIhPXR5cGVvZiBmJiYoXCJzZXF1ZW5jZVwiPT09bz9sPWYuX3N0YXJ0VGltZStmLnRvdGFsRHVyYXRpb24oKS9mLl90aW1lU2NhbGU6XCJzdGFydFwiPT09byYmKGYuX3N0YXJ0VGltZS09Zi5kZWxheSgpKSksbCs9aDtyZXR1cm4gdGhpcy5fdW5jYWNoZSghMCl9aWYoXCJzdHJpbmdcIj09dHlwZW9mIHIpcmV0dXJuIHRoaXMuYWRkTGFiZWwocixuKTtpZihcImZ1bmN0aW9uXCIhPXR5cGVvZiByKXRocm93XCJDYW5ub3QgYWRkIFwiK3IrXCIgaW50byB0aGUgdGltZWxpbmU7IGl0IGlzIG5vdCBhIHR3ZWVuLCB0aW1lbGluZSwgZnVuY3Rpb24sIG9yIHN0cmluZy5cIjtyPWkuZGVsYXllZENhbGwoMCxyKX1pZihlLnByb3RvdHlwZS5hZGQuY2FsbCh0aGlzLHIsbiksKHRoaXMuX2djfHx0aGlzLl90aW1lPT09dGhpcy5fZHVyYXRpb24pJiYhdGhpcy5fcGF1c2VkJiZ0aGlzLl9kdXJhdGlvbjx0aGlzLmR1cmF0aW9uKCkpZm9yKHA9dGhpcyxjPXAucmF3VGltZSgpPnIuX3N0YXJ0VGltZTtwLl90aW1lbGluZTspYyYmcC5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmc/cC50b3RhbFRpbWUocC5fdG90YWxUaW1lLCEwKTpwLl9nYyYmcC5fZW5hYmxlZCghMCwhMSkscD1wLl90aW1lbGluZTtyZXR1cm4gdGhpc30sZi5yZW1vdmU9ZnVuY3Rpb24oZSl7aWYoZSBpbnN0YW5jZW9mIHQpcmV0dXJuIHRoaXMuX3JlbW92ZShlLCExKTtpZihlIGluc3RhbmNlb2YgQXJyYXl8fGUmJmUucHVzaCYmYShlKSl7Zm9yKHZhciBpPWUubGVuZ3RoOy0taT4tMTspdGhpcy5yZW1vdmUoZVtpXSk7cmV0dXJuIHRoaXN9cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIGU/dGhpcy5yZW1vdmVMYWJlbChlKTp0aGlzLmtpbGwobnVsbCxlKX0sZi5fcmVtb3ZlPWZ1bmN0aW9uKHQsaSl7ZS5wcm90b3R5cGUuX3JlbW92ZS5jYWxsKHRoaXMsdCxpKTt2YXIgcz10aGlzLl9sYXN0O3JldHVybiBzP3RoaXMuX3RpbWU+cy5fc3RhcnRUaW1lK3MuX3RvdGFsRHVyYXRpb24vcy5fdGltZVNjYWxlJiYodGhpcy5fdGltZT10aGlzLmR1cmF0aW9uKCksdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RvdGFsRHVyYXRpb24pOnRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPXRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249MCx0aGlzfSxmLmFwcGVuZD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLmFkZCh0LHRoaXMuX3BhcnNlVGltZU9yTGFiZWwobnVsbCxlLCEwLHQpKX0sZi5pbnNlcnQ9Zi5pbnNlcnRNdWx0aXBsZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5hZGQodCxlfHwwLGkscyl9LGYuYXBwZW5kTXVsdGlwbGU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuYWRkKHQsdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChudWxsLGUsITAsdCksaSxzKX0sZi5hZGRMYWJlbD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLl9sYWJlbHNbdF09dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChlKSx0aGlzfSxmLmFkZFBhdXNlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmNhbGwoXyxbXCJ7c2VsZn1cIixlLGksc10sdGhpcyx0KX0sZi5yZW1vdmVMYWJlbD1mdW5jdGlvbih0KXtyZXR1cm4gZGVsZXRlIHRoaXMuX2xhYmVsc1t0XSx0aGlzfSxmLmdldExhYmVsVGltZT1mdW5jdGlvbih0KXtyZXR1cm4gbnVsbCE9dGhpcy5fbGFiZWxzW3RdP3RoaXMuX2xhYmVsc1t0XTotMX0sZi5fcGFyc2VUaW1lT3JMYWJlbD1mdW5jdGlvbihlLGkscyxyKXt2YXIgbjtpZihyIGluc3RhbmNlb2YgdCYmci50aW1lbGluZT09PXRoaXMpdGhpcy5yZW1vdmUocik7ZWxzZSBpZihyJiYociBpbnN0YW5jZW9mIEFycmF5fHxyLnB1c2gmJmEocikpKWZvcihuPXIubGVuZ3RoOy0tbj4tMTspcltuXWluc3RhbmNlb2YgdCYmcltuXS50aW1lbGluZT09PXRoaXMmJnRoaXMucmVtb3ZlKHJbbl0pO2lmKFwic3RyaW5nXCI9PXR5cGVvZiBpKXJldHVybiB0aGlzLl9wYXJzZVRpbWVPckxhYmVsKGkscyYmXCJudW1iZXJcIj09dHlwZW9mIGUmJm51bGw9PXRoaXMuX2xhYmVsc1tpXT9lLXRoaXMuZHVyYXRpb24oKTowLHMpO2lmKGk9aXx8MCxcInN0cmluZ1wiIT10eXBlb2YgZXx8IWlzTmFOKGUpJiZudWxsPT10aGlzLl9sYWJlbHNbZV0pbnVsbD09ZSYmKGU9dGhpcy5kdXJhdGlvbigpKTtlbHNle2lmKG49ZS5pbmRleE9mKFwiPVwiKSwtMT09PW4pcmV0dXJuIG51bGw9PXRoaXMuX2xhYmVsc1tlXT9zP3RoaXMuX2xhYmVsc1tlXT10aGlzLmR1cmF0aW9uKCkraTppOnRoaXMuX2xhYmVsc1tlXStpO2k9cGFyc2VJbnQoZS5jaGFyQXQobi0xKStcIjFcIiwxMCkqTnVtYmVyKGUuc3Vic3RyKG4rMSkpLGU9bj4xP3RoaXMuX3BhcnNlVGltZU9yTGFiZWwoZS5zdWJzdHIoMCxuLTEpLDAscyk6dGhpcy5kdXJhdGlvbigpfXJldHVybiBOdW1iZXIoZSkraX0sZi5zZWVrPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMudG90YWxUaW1lKFwibnVtYmVyXCI9PXR5cGVvZiB0P3Q6dGhpcy5fcGFyc2VUaW1lT3JMYWJlbCh0KSxlIT09ITEpfSxmLnN0b3A9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5wYXVzZWQoITApfSxmLmdvdG9BbmRQbGF5PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucGxheSh0LGUpfSxmLmdvdG9BbmRTdG9wPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucGF1c2UodCxlKX0sZi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3RoaXMuX2djJiZ0aGlzLl9lbmFibGVkKCEwLCExKTt2YXIgcyxuLGEsaCxsLF89dGhpcy5fZGlydHk/dGhpcy50b3RhbER1cmF0aW9uKCk6dGhpcy5fdG90YWxEdXJhdGlvbix1PXRoaXMuX3RpbWUsZj10aGlzLl9zdGFydFRpbWUscD10aGlzLl90aW1lU2NhbGUsYz10aGlzLl9wYXVzZWQ7aWYodD49Xz8odGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9Xyx0aGlzLl9yZXZlcnNlZHx8dGhpcy5faGFzUGF1c2VkQ2hpbGQoKXx8KG49ITAsaD1cIm9uQ29tcGxldGVcIiwwPT09dGhpcy5fZHVyYXRpb24mJigwPT09dHx8MD50aGlzLl9yYXdQcmV2VGltZXx8dGhpcy5fcmF3UHJldlRpbWU9PT1yKSYmdGhpcy5fcmF3UHJldlRpbWUhPT10JiZ0aGlzLl9maXJzdCYmKGw9ITAsdGhpcy5fcmF3UHJldlRpbWU+ciYmKGg9XCJvblJldmVyc2VDb21wbGV0ZVwiKSkpLHRoaXMuX3Jhd1ByZXZUaW1lPXRoaXMuX2R1cmF0aW9ufHwhZXx8dHx8dGhpcy5fcmF3UHJldlRpbWU9PT10P3Q6cix0PV8rMWUtNCk6MWUtNz50Pyh0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT0wLCgwIT09dXx8MD09PXRoaXMuX2R1cmF0aW9uJiZ0aGlzLl9yYXdQcmV2VGltZSE9PXImJih0aGlzLl9yYXdQcmV2VGltZT4wfHwwPnQmJnRoaXMuX3Jhd1ByZXZUaW1lPj0wKSkmJihoPVwib25SZXZlcnNlQ29tcGxldGVcIixuPXRoaXMuX3JldmVyc2VkKSwwPnQ/KHRoaXMuX2FjdGl2ZT0hMSwwPT09dGhpcy5fZHVyYXRpb24mJnRoaXMuX3Jhd1ByZXZUaW1lPj0wJiZ0aGlzLl9maXJzdCYmKGw9ITApLHRoaXMuX3Jhd1ByZXZUaW1lPXQpOih0aGlzLl9yYXdQcmV2VGltZT10aGlzLl9kdXJhdGlvbnx8IWV8fHR8fHRoaXMuX3Jhd1ByZXZUaW1lPT09dD90OnIsdD0wLHRoaXMuX2luaXR0ZWR8fChsPSEwKSkpOnRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXRoaXMuX3Jhd1ByZXZUaW1lPXQsdGhpcy5fdGltZSE9PXUmJnRoaXMuX2ZpcnN0fHxpfHxsKXtpZih0aGlzLl9pbml0dGVkfHwodGhpcy5faW5pdHRlZD0hMCksdGhpcy5fYWN0aXZlfHwhdGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lIT09dSYmdD4wJiYodGhpcy5fYWN0aXZlPSEwKSwwPT09dSYmdGhpcy52YXJzLm9uU3RhcnQmJjAhPT10aGlzLl90aW1lJiYoZXx8dGhpcy52YXJzLm9uU3RhcnQuYXBwbHkodGhpcy52YXJzLm9uU3RhcnRTY29wZXx8dGhpcyx0aGlzLnZhcnMub25TdGFydFBhcmFtc3x8bykpLHRoaXMuX3RpbWU+PXUpZm9yKHM9dGhpcy5fZmlyc3Q7cyYmKGE9cy5fbmV4dCwhdGhpcy5fcGF1c2VkfHxjKTspKHMuX2FjdGl2ZXx8cy5fc3RhcnRUaW1lPD10aGlzLl90aW1lJiYhcy5fcGF1c2VkJiYhcy5fZ2MpJiYocy5fcmV2ZXJzZWQ/cy5yZW5kZXIoKHMuX2RpcnR5P3MudG90YWxEdXJhdGlvbigpOnMuX3RvdGFsRHVyYXRpb24pLSh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSk6cy5yZW5kZXIoKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKSkscz1hO2Vsc2UgZm9yKHM9dGhpcy5fbGFzdDtzJiYoYT1zLl9wcmV2LCF0aGlzLl9wYXVzZWR8fGMpOykocy5fYWN0aXZlfHx1Pj1zLl9zdGFydFRpbWUmJiFzLl9wYXVzZWQmJiFzLl9nYykmJihzLl9yZXZlcnNlZD9zLnJlbmRlcigocy5fZGlydHk/cy50b3RhbER1cmF0aW9uKCk6cy5fdG90YWxEdXJhdGlvbiktKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKTpzLnJlbmRlcigodC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpKSxzPWE7dGhpcy5fb25VcGRhdGUmJihlfHx0aGlzLl9vblVwZGF0ZS5hcHBseSh0aGlzLnZhcnMub25VcGRhdGVTY29wZXx8dGhpcyx0aGlzLnZhcnMub25VcGRhdGVQYXJhbXN8fG8pKSxoJiYodGhpcy5fZ2N8fChmPT09dGhpcy5fc3RhcnRUaW1lfHxwIT09dGhpcy5fdGltZVNjYWxlKSYmKDA9PT10aGlzLl90aW1lfHxfPj10aGlzLnRvdGFsRHVyYXRpb24oKSkmJihuJiYodGhpcy5fdGltZWxpbmUuYXV0b1JlbW92ZUNoaWxkcmVuJiZ0aGlzLl9lbmFibGVkKCExLCExKSx0aGlzLl9hY3RpdmU9ITEpLCFlJiZ0aGlzLnZhcnNbaF0mJnRoaXMudmFyc1toXS5hcHBseSh0aGlzLnZhcnNbaCtcIlNjb3BlXCJdfHx0aGlzLHRoaXMudmFyc1toK1wiUGFyYW1zXCJdfHxvKSkpfX0sZi5faGFzUGF1c2VkQ2hpbGQ9ZnVuY3Rpb24oKXtmb3IodmFyIHQ9dGhpcy5fZmlyc3Q7dDspe2lmKHQuX3BhdXNlZHx8dCBpbnN0YW5jZW9mIHMmJnQuX2hhc1BhdXNlZENoaWxkKCkpcmV0dXJuITA7dD10Ll9uZXh0fXJldHVybiExfSxmLmdldENoaWxkcmVuPWZ1bmN0aW9uKHQsZSxzLHIpe3I9cnx8LTk5OTk5OTk5OTk7Zm9yKHZhciBuPVtdLGE9dGhpcy5fZmlyc3Qsbz0wO2E7KXI+YS5fc3RhcnRUaW1lfHwoYSBpbnN0YW5jZW9mIGk/ZSE9PSExJiYobltvKytdPWEpOihzIT09ITEmJihuW28rK109YSksdCE9PSExJiYobj1uLmNvbmNhdChhLmdldENoaWxkcmVuKCEwLGUscykpLG89bi5sZW5ndGgpKSksYT1hLl9uZXh0O3JldHVybiBufSxmLmdldFR3ZWVuc09mPWZ1bmN0aW9uKHQsZSl7dmFyIHMscixuPXRoaXMuX2djLGE9W10sbz0wO2ZvcihuJiZ0aGlzLl9lbmFibGVkKCEwLCEwKSxzPWkuZ2V0VHdlZW5zT2YodCkscj1zLmxlbmd0aDstLXI+LTE7KShzW3JdLnRpbWVsaW5lPT09dGhpc3x8ZSYmdGhpcy5fY29udGFpbnMoc1tyXSkpJiYoYVtvKytdPXNbcl0pO3JldHVybiBuJiZ0aGlzLl9lbmFibGVkKCExLCEwKSxhfSxmLl9jb250YWlucz1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC50aW1lbGluZTtlOyl7aWYoZT09PXRoaXMpcmV0dXJuITA7ZT1lLnRpbWVsaW5lfXJldHVybiExfSxmLnNoaWZ0Q2hpbGRyZW49ZnVuY3Rpb24odCxlLGkpe2k9aXx8MDtmb3IodmFyIHMscj10aGlzLl9maXJzdCxuPXRoaXMuX2xhYmVscztyOylyLl9zdGFydFRpbWU+PWkmJihyLl9zdGFydFRpbWUrPXQpLHI9ci5fbmV4dDtpZihlKWZvcihzIGluIG4pbltzXT49aSYmKG5bc10rPXQpO3JldHVybiB0aGlzLl91bmNhY2hlKCEwKX0sZi5fa2lsbD1mdW5jdGlvbih0LGUpe2lmKCF0JiYhZSlyZXR1cm4gdGhpcy5fZW5hYmxlZCghMSwhMSk7Zm9yKHZhciBpPWU/dGhpcy5nZXRUd2VlbnNPZihlKTp0aGlzLmdldENoaWxkcmVuKCEwLCEwLCExKSxzPWkubGVuZ3RoLHI9ITE7LS1zPi0xOylpW3NdLl9raWxsKHQsZSkmJihyPSEwKTtyZXR1cm4gcn0sZi5jbGVhcj1mdW5jdGlvbih0KXt2YXIgZT10aGlzLmdldENoaWxkcmVuKCExLCEwLCEwKSxpPWUubGVuZ3RoO2Zvcih0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT0wOy0taT4tMTspZVtpXS5fZW5hYmxlZCghMSwhMSk7cmV0dXJuIHQhPT0hMSYmKHRoaXMuX2xhYmVscz17fSksdGhpcy5fdW5jYWNoZSghMCl9LGYuaW52YWxpZGF0ZT1mdW5jdGlvbigpe2Zvcih2YXIgdD10aGlzLl9maXJzdDt0Oyl0LmludmFsaWRhdGUoKSx0PXQuX25leHQ7cmV0dXJuIHRoaXN9LGYuX2VuYWJsZWQ9ZnVuY3Rpb24odCxpKXtpZih0PT09dGhpcy5fZ2MpZm9yKHZhciBzPXRoaXMuX2ZpcnN0O3M7KXMuX2VuYWJsZWQodCwhMCkscz1zLl9uZXh0O3JldHVybiBlLnByb3RvdHlwZS5fZW5hYmxlZC5jYWxsKHRoaXMsdCxpKX0sZi5kdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oMCE9PXRoaXMuZHVyYXRpb24oKSYmMCE9PXQmJnRoaXMudGltZVNjYWxlKHRoaXMuX2R1cmF0aW9uL3QpLHRoaXMpOih0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCksdGhpcy5fZHVyYXRpb24pfSxmLnRvdGFsRHVyYXRpb249ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpe2lmKHRoaXMuX2RpcnR5KXtmb3IodmFyIGUsaSxzPTAscj10aGlzLl9sYXN0LG49OTk5OTk5OTk5OTk5O3I7KWU9ci5fcHJldixyLl9kaXJ0eSYmci50b3RhbER1cmF0aW9uKCksci5fc3RhcnRUaW1lPm4mJnRoaXMuX3NvcnRDaGlsZHJlbiYmIXIuX3BhdXNlZD90aGlzLmFkZChyLHIuX3N0YXJ0VGltZS1yLl9kZWxheSk6bj1yLl9zdGFydFRpbWUsMD5yLl9zdGFydFRpbWUmJiFyLl9wYXVzZWQmJihzLT1yLl9zdGFydFRpbWUsdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJih0aGlzLl9zdGFydFRpbWUrPXIuX3N0YXJ0VGltZS90aGlzLl90aW1lU2NhbGUpLHRoaXMuc2hpZnRDaGlsZHJlbigtci5fc3RhcnRUaW1lLCExLC05OTk5OTk5OTk5KSxuPTApLGk9ci5fc3RhcnRUaW1lK3IuX3RvdGFsRHVyYXRpb24vci5fdGltZVNjYWxlLGk+cyYmKHM9aSkscj1lO3RoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249cyx0aGlzLl9kaXJ0eT0hMX1yZXR1cm4gdGhpcy5fdG90YWxEdXJhdGlvbn1yZXR1cm4gMCE9PXRoaXMudG90YWxEdXJhdGlvbigpJiYwIT09dCYmdGhpcy50aW1lU2NhbGUodGhpcy5fdG90YWxEdXJhdGlvbi90KSx0aGlzfSxmLnVzZXNGcmFtZXM9ZnVuY3Rpb24oKXtmb3IodmFyIGU9dGhpcy5fdGltZWxpbmU7ZS5fdGltZWxpbmU7KWU9ZS5fdGltZWxpbmU7cmV0dXJuIGU9PT10Ll9yb290RnJhbWVzVGltZWxpbmV9LGYucmF3VGltZT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9wYXVzZWQ/dGhpcy5fdG90YWxUaW1lOih0aGlzLl90aW1lbGluZS5yYXdUaW1lKCktdGhpcy5fc3RhcnRUaW1lKSp0aGlzLl90aW1lU2NhbGV9LHN9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbihmdW5jdGlvbih0KXtcInVzZSBzdHJpY3RcIjt2YXIgZT10LkdyZWVuU29ja0dsb2JhbHN8fHQ7aWYoIWUuVHdlZW5MaXRlKXt2YXIgaSxzLG4scixhLG89ZnVuY3Rpb24odCl7dmFyIGkscz10LnNwbGl0KFwiLlwiKSxuPWU7Zm9yKGk9MDtzLmxlbmd0aD5pO2krKyluW3NbaV1dPW49bltzW2ldXXx8e307cmV0dXJuIG59LGw9byhcImNvbS5ncmVlbnNvY2tcIiksaD0xZS0xMCxfPVtdLnNsaWNlLHU9ZnVuY3Rpb24oKXt9LG09ZnVuY3Rpb24oKXt2YXIgdD1PYmplY3QucHJvdG90eXBlLnRvU3RyaW5nLGU9dC5jYWxsKFtdKTtyZXR1cm4gZnVuY3Rpb24oaSl7cmV0dXJuIG51bGwhPWkmJihpIGluc3RhbmNlb2YgQXJyYXl8fFwib2JqZWN0XCI9PXR5cGVvZiBpJiYhIWkucHVzaCYmdC5jYWxsKGkpPT09ZSl9fSgpLGY9e30scD1mdW5jdGlvbihpLHMsbixyKXt0aGlzLnNjPWZbaV0/ZltpXS5zYzpbXSxmW2ldPXRoaXMsdGhpcy5nc0NsYXNzPW51bGwsdGhpcy5mdW5jPW47dmFyIGE9W107dGhpcy5jaGVjaz1mdW5jdGlvbihsKXtmb3IodmFyIGgsXyx1LG0sYz1zLmxlbmd0aCxkPWM7LS1jPi0xOykoaD1mW3NbY11dfHxuZXcgcChzW2NdLFtdKSkuZ3NDbGFzcz8oYVtjXT1oLmdzQ2xhc3MsZC0tKTpsJiZoLnNjLnB1c2godGhpcyk7aWYoMD09PWQmJm4pZm9yKF89KFwiY29tLmdyZWVuc29jay5cIitpKS5zcGxpdChcIi5cIiksdT1fLnBvcCgpLG09byhfLmpvaW4oXCIuXCIpKVt1XT10aGlzLmdzQ2xhc3M9bi5hcHBseShuLGEpLHImJihlW3VdPW0sXCJmdW5jdGlvblwiPT10eXBlb2YgZGVmaW5lJiZkZWZpbmUuYW1kP2RlZmluZSgodC5HcmVlblNvY2tBTURQYXRoP3QuR3JlZW5Tb2NrQU1EUGF0aCtcIi9cIjpcIlwiKStpLnNwbGl0KFwiLlwiKS5qb2luKFwiL1wiKSxbXSxmdW5jdGlvbigpe3JldHVybiBtfSk6XCJ1bmRlZmluZWRcIiE9dHlwZW9mIG1vZHVsZSYmbW9kdWxlLmV4cG9ydHMmJihtb2R1bGUuZXhwb3J0cz1tKSksYz0wO3RoaXMuc2MubGVuZ3RoPmM7YysrKXRoaXMuc2NbY10uY2hlY2soKX0sdGhpcy5jaGVjayghMCl9LGM9dC5fZ3NEZWZpbmU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIG5ldyBwKHQsZSxpLHMpfSxkPWwuX2NsYXNzPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gZT1lfHxmdW5jdGlvbigpe30sYyh0LFtdLGZ1bmN0aW9uKCl7cmV0dXJuIGV9LGkpLGV9O2MuZ2xvYmFscz1lO3ZhciB2PVswLDAsMSwxXSxnPVtdLFQ9ZChcImVhc2luZy5FYXNlXCIsZnVuY3Rpb24odCxlLGkscyl7dGhpcy5fZnVuYz10LHRoaXMuX3R5cGU9aXx8MCx0aGlzLl9wb3dlcj1zfHwwLHRoaXMuX3BhcmFtcz1lP3YuY29uY2F0KGUpOnZ9LCEwKSx5PVQubWFwPXt9LHc9VC5yZWdpc3Rlcj1mdW5jdGlvbih0LGUsaSxzKXtmb3IodmFyIG4scixhLG8saD1lLnNwbGl0KFwiLFwiKSxfPWgubGVuZ3RoLHU9KGl8fFwiZWFzZUluLGVhc2VPdXQsZWFzZUluT3V0XCIpLnNwbGl0KFwiLFwiKTstLV8+LTE7KWZvcihyPWhbX10sbj1zP2QoXCJlYXNpbmcuXCIrcixudWxsLCEwKTpsLmVhc2luZ1tyXXx8e30sYT11Lmxlbmd0aDstLWE+LTE7KW89dVthXSx5W3IrXCIuXCIrb109eVtvK3JdPW5bb109dC5nZXRSYXRpbz90OnRbb118fG5ldyB0fTtmb3Iobj1ULnByb3RvdHlwZSxuLl9jYWxjRW5kPSExLG4uZ2V0UmF0aW89ZnVuY3Rpb24odCl7aWYodGhpcy5fZnVuYylyZXR1cm4gdGhpcy5fcGFyYW1zWzBdPXQsdGhpcy5fZnVuYy5hcHBseShudWxsLHRoaXMuX3BhcmFtcyk7dmFyIGU9dGhpcy5fdHlwZSxpPXRoaXMuX3Bvd2VyLHM9MT09PWU/MS10OjI9PT1lP3Q6LjU+dD8yKnQ6MiooMS10KTtyZXR1cm4gMT09PWk/cyo9czoyPT09aT9zKj1zKnM6Mz09PWk/cyo9cypzKnM6ND09PWkmJihzKj1zKnMqcypzKSwxPT09ZT8xLXM6Mj09PWU/czouNT50P3MvMjoxLXMvMn0saT1bXCJMaW5lYXJcIixcIlF1YWRcIixcIkN1YmljXCIsXCJRdWFydFwiLFwiUXVpbnQsU3Ryb25nXCJdLHM9aS5sZW5ndGg7LS1zPi0xOyluPWlbc10rXCIsUG93ZXJcIitzLHcobmV3IFQobnVsbCxudWxsLDEscyksbixcImVhc2VPdXRcIiwhMCksdyhuZXcgVChudWxsLG51bGwsMixzKSxuLFwiZWFzZUluXCIrKDA9PT1zP1wiLGVhc2VOb25lXCI6XCJcIikpLHcobmV3IFQobnVsbCxudWxsLDMscyksbixcImVhc2VJbk91dFwiKTt5LmxpbmVhcj1sLmVhc2luZy5MaW5lYXIuZWFzZUluLHkuc3dpbmc9bC5lYXNpbmcuUXVhZC5lYXNlSW5PdXQ7dmFyIFA9ZChcImV2ZW50cy5FdmVudERpc3BhdGNoZXJcIixmdW5jdGlvbih0KXt0aGlzLl9saXN0ZW5lcnM9e30sdGhpcy5fZXZlbnRUYXJnZXQ9dHx8dGhpc30pO249UC5wcm90b3R5cGUsbi5hZGRFdmVudExpc3RlbmVyPWZ1bmN0aW9uKHQsZSxpLHMsbil7bj1ufHwwO3ZhciBvLGwsaD10aGlzLl9saXN0ZW5lcnNbdF0sXz0wO2ZvcihudWxsPT1oJiYodGhpcy5fbGlzdGVuZXJzW3RdPWg9W10pLGw9aC5sZW5ndGg7LS1sPi0xOylvPWhbbF0sby5jPT09ZSYmby5zPT09aT9oLnNwbGljZShsLDEpOjA9PT1fJiZuPm8ucHImJihfPWwrMSk7aC5zcGxpY2UoXywwLHtjOmUsczppLHVwOnMscHI6bn0pLHRoaXMhPT1yfHxhfHxyLndha2UoKX0sbi5yZW1vdmVFdmVudExpc3RlbmVyPWZ1bmN0aW9uKHQsZSl7dmFyIGkscz10aGlzLl9saXN0ZW5lcnNbdF07aWYocylmb3IoaT1zLmxlbmd0aDstLWk+LTE7KWlmKHNbaV0uYz09PWUpcmV0dXJuIHMuc3BsaWNlKGksMSksdm9pZCAwfSxuLmRpc3BhdGNoRXZlbnQ9ZnVuY3Rpb24odCl7dmFyIGUsaSxzLG49dGhpcy5fbGlzdGVuZXJzW3RdO2lmKG4pZm9yKGU9bi5sZW5ndGgsaT10aGlzLl9ldmVudFRhcmdldDstLWU+LTE7KXM9bltlXSxzLnVwP3MuYy5jYWxsKHMuc3x8aSx7dHlwZTp0LHRhcmdldDppfSk6cy5jLmNhbGwocy5zfHxpKX07dmFyIGs9dC5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUsYj10LmNhbmNlbEFuaW1hdGlvbkZyYW1lLEE9RGF0ZS5ub3d8fGZ1bmN0aW9uKCl7cmV0dXJuKG5ldyBEYXRlKS5nZXRUaW1lKCl9LFM9QSgpO2ZvcihpPVtcIm1zXCIsXCJtb3pcIixcIndlYmtpdFwiLFwib1wiXSxzPWkubGVuZ3RoOy0tcz4tMSYmIWs7KWs9dFtpW3NdK1wiUmVxdWVzdEFuaW1hdGlvbkZyYW1lXCJdLGI9dFtpW3NdK1wiQ2FuY2VsQW5pbWF0aW9uRnJhbWVcIl18fHRbaVtzXStcIkNhbmNlbFJlcXVlc3RBbmltYXRpb25GcmFtZVwiXTtkKFwiVGlja2VyXCIsZnVuY3Rpb24odCxlKXt2YXIgaSxzLG4sbyxsLF89dGhpcyxtPUEoKSxmPWUhPT0hMSYmayxwPTUwMCxjPTMzLGQ9ZnVuY3Rpb24odCl7dmFyIGUscixhPUEoKS1TO2E+cCYmKG0rPWEtYyksUys9YSxfLnRpbWU9KFMtbSkvMWUzLGU9Xy50aW1lLWwsKCFpfHxlPjB8fHQ9PT0hMCkmJihfLmZyYW1lKyssbCs9ZSsoZT49bz8uMDA0Om8tZSkscj0hMCksdCE9PSEwJiYobj1zKGQpKSxyJiZfLmRpc3BhdGNoRXZlbnQoXCJ0aWNrXCIpfTtQLmNhbGwoXyksXy50aW1lPV8uZnJhbWU9MCxfLnRpY2s9ZnVuY3Rpb24oKXtkKCEwKX0sXy5sYWdTbW9vdGhpbmc9ZnVuY3Rpb24odCxlKXtwPXR8fDEvaCxjPU1hdGgubWluKGUscCwwKX0sXy5zbGVlcD1mdW5jdGlvbigpe251bGwhPW4mJihmJiZiP2Iobik6Y2xlYXJUaW1lb3V0KG4pLHM9dSxuPW51bGwsXz09PXImJihhPSExKSl9LF8ud2FrZT1mdW5jdGlvbigpe251bGwhPT1uP18uc2xlZXAoKTpfLmZyYW1lPjEwJiYoUz1BKCktcCs1KSxzPTA9PT1pP3U6ZiYmaz9rOmZ1bmN0aW9uKHQpe3JldHVybiBzZXRUaW1lb3V0KHQsMHwxZTMqKGwtXy50aW1lKSsxKX0sXz09PXImJihhPSEwKSxkKDIpfSxfLmZwcz1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oaT10LG89MS8oaXx8NjApLGw9dGhpcy50aW1lK28sXy53YWtlKCksdm9pZCAwKTppfSxfLnVzZVJBRj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8oXy5zbGVlcCgpLGY9dCxfLmZwcyhpKSx2b2lkIDApOmZ9LF8uZnBzKHQpLHNldFRpbWVvdXQoZnVuY3Rpb24oKXtmJiYoIW58fDU+Xy5mcmFtZSkmJl8udXNlUkFGKCExKX0sMTUwMCl9KSxuPWwuVGlja2VyLnByb3RvdHlwZT1uZXcgbC5ldmVudHMuRXZlbnREaXNwYXRjaGVyLG4uY29uc3RydWN0b3I9bC5UaWNrZXI7dmFyIHg9ZChcImNvcmUuQW5pbWF0aW9uXCIsZnVuY3Rpb24odCxlKXtpZih0aGlzLnZhcnM9ZT1lfHx7fSx0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXR8fDAsdGhpcy5fZGVsYXk9TnVtYmVyKGUuZGVsYXkpfHwwLHRoaXMuX3RpbWVTY2FsZT0xLHRoaXMuX2FjdGl2ZT1lLmltbWVkaWF0ZVJlbmRlcj09PSEwLHRoaXMuZGF0YT1lLmRhdGEsdGhpcy5fcmV2ZXJzZWQ9ZS5yZXZlcnNlZD09PSEwLEIpe2F8fHIud2FrZSgpO3ZhciBpPXRoaXMudmFycy51c2VGcmFtZXM/UTpCO2kuYWRkKHRoaXMsaS5fdGltZSksdGhpcy52YXJzLnBhdXNlZCYmdGhpcy5wYXVzZWQoITApfX0pO3I9eC50aWNrZXI9bmV3IGwuVGlja2VyLG49eC5wcm90b3R5cGUsbi5fZGlydHk9bi5fZ2M9bi5faW5pdHRlZD1uLl9wYXVzZWQ9ITEsbi5fdG90YWxUaW1lPW4uX3RpbWU9MCxuLl9yYXdQcmV2VGltZT0tMSxuLl9uZXh0PW4uX2xhc3Q9bi5fb25VcGRhdGU9bi5fdGltZWxpbmU9bi50aW1lbGluZT1udWxsLG4uX3BhdXNlZD0hMTt2YXIgQz1mdW5jdGlvbigpe2EmJkEoKS1TPjJlMyYmci53YWtlKCksc2V0VGltZW91dChDLDJlMyl9O0MoKSxuLnBsYXk9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5yZXZlcnNlZCghMSkucGF1c2VkKCExKX0sbi5wYXVzZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnBhdXNlZCghMCl9LG4ucmVzdW1lPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucGF1c2VkKCExKX0sbi5zZWVrPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMudG90YWxUaW1lKE51bWJlcih0KSxlIT09ITEpfSxuLnJlc3RhcnQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5yZXZlcnNlZCghMSkucGF1c2VkKCExKS50b3RhbFRpbWUodD8tdGhpcy5fZGVsYXk6MCxlIT09ITEsITApfSxuLnJldmVyc2U9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHR8fHRoaXMudG90YWxEdXJhdGlvbigpLGUpLHRoaXMucmV2ZXJzZWQoITApLnBhdXNlZCghMSl9LG4ucmVuZGVyPWZ1bmN0aW9uKCl7fSxuLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpc30sbi5pc0FjdGl2ZT1mdW5jdGlvbigpe3ZhciB0LGU9dGhpcy5fdGltZWxpbmUsaT10aGlzLl9zdGFydFRpbWU7cmV0dXJuIWV8fCF0aGlzLl9nYyYmIXRoaXMuX3BhdXNlZCYmZS5pc0FjdGl2ZSgpJiYodD1lLnJhd1RpbWUoKSk+PWkmJmkrdGhpcy50b3RhbER1cmF0aW9uKCkvdGhpcy5fdGltZVNjYWxlPnR9LG4uX2VuYWJsZWQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXx8ci53YWtlKCksdGhpcy5fZ2M9IXQsdGhpcy5fYWN0aXZlPXRoaXMuaXNBY3RpdmUoKSxlIT09ITAmJih0JiYhdGhpcy50aW1lbGluZT90aGlzLl90aW1lbGluZS5hZGQodGhpcyx0aGlzLl9zdGFydFRpbWUtdGhpcy5fZGVsYXkpOiF0JiZ0aGlzLnRpbWVsaW5lJiZ0aGlzLl90aW1lbGluZS5fcmVtb3ZlKHRoaXMsITApKSwhMX0sbi5fa2lsbD1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9lbmFibGVkKCExLCExKX0sbi5raWxsPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuX2tpbGwodCxlKSx0aGlzfSxuLl91bmNhY2hlPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10P3RoaXM6dGhpcy50aW1lbGluZTtlOyllLl9kaXJ0eT0hMCxlPWUudGltZWxpbmU7cmV0dXJuIHRoaXN9LG4uX3N3YXBTZWxmSW5QYXJhbXM9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQubGVuZ3RoLGk9dC5jb25jYXQoKTstLWU+LTE7KVwie3NlbGZ9XCI9PT10W2VdJiYoaVtlXT10aGlzKTtyZXR1cm4gaX0sbi5ldmVudENhbGxiYWNrPWZ1bmN0aW9uKHQsZSxpLHMpe2lmKFwib25cIj09PSh0fHxcIlwiKS5zdWJzdHIoMCwyKSl7dmFyIG49dGhpcy52YXJzO2lmKDE9PT1hcmd1bWVudHMubGVuZ3RoKXJldHVybiBuW3RdO251bGw9PWU/ZGVsZXRlIG5bdF06KG5bdF09ZSxuW3QrXCJQYXJhbXNcIl09bShpKSYmLTEhPT1pLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKT90aGlzLl9zd2FwU2VsZkluUGFyYW1zKGkpOmksblt0K1wiU2NvcGVcIl09cyksXCJvblVwZGF0ZVwiPT09dCYmKHRoaXMuX29uVXBkYXRlPWUpfXJldHVybiB0aGlzfSxuLmRlbGF5PWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmdGhpcy5zdGFydFRpbWUodGhpcy5fc3RhcnRUaW1lK3QtdGhpcy5fZGVsYXkpLHRoaXMuX2RlbGF5PXQsdGhpcyk6dGhpcy5fZGVsYXl9LG4uZHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249dCx0aGlzLl91bmNhY2hlKCEwKSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmdGhpcy5fdGltZT4wJiZ0aGlzLl90aW1lPHRoaXMuX2R1cmF0aW9uJiYwIT09dCYmdGhpcy50b3RhbFRpbWUodGhpcy5fdG90YWxUaW1lKih0L3RoaXMuX2R1cmF0aW9uKSwhMCksdGhpcyk6KHRoaXMuX2RpcnR5PSExLHRoaXMuX2R1cmF0aW9uKX0sbi50b3RhbER1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiB0aGlzLl9kaXJ0eT0hMSxhcmd1bWVudHMubGVuZ3RoP3RoaXMuZHVyYXRpb24odCk6dGhpcy5fdG90YWxEdXJhdGlvbn0sbi50aW1lPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKSx0aGlzLnRvdGFsVGltZSh0PnRoaXMuX2R1cmF0aW9uP3RoaXMuX2R1cmF0aW9uOnQsZSkpOnRoaXMuX3RpbWV9LG4udG90YWxUaW1lPWZ1bmN0aW9uKHQsZSxpKXtpZihhfHxyLndha2UoKSwhYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fdG90YWxUaW1lO2lmKHRoaXMuX3RpbWVsaW5lKXtpZigwPnQmJiFpJiYodCs9dGhpcy50b3RhbER1cmF0aW9uKCkpLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nKXt0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCk7dmFyIHM9dGhpcy5fdG90YWxEdXJhdGlvbixuPXRoaXMuX3RpbWVsaW5lO2lmKHQ+cyYmIWkmJih0PXMpLHRoaXMuX3N0YXJ0VGltZT0odGhpcy5fcGF1c2VkP3RoaXMuX3BhdXNlVGltZTpuLl90aW1lKS0odGhpcy5fcmV2ZXJzZWQ/cy10OnQpL3RoaXMuX3RpbWVTY2FsZSxuLl9kaXJ0eXx8dGhpcy5fdW5jYWNoZSghMSksbi5fdGltZWxpbmUpZm9yKDtuLl90aW1lbGluZTspbi5fdGltZWxpbmUuX3RpbWUhPT0obi5fc3RhcnRUaW1lK24uX3RvdGFsVGltZSkvbi5fdGltZVNjYWxlJiZuLnRvdGFsVGltZShuLl90b3RhbFRpbWUsITApLG49bi5fdGltZWxpbmV9dGhpcy5fZ2MmJnRoaXMuX2VuYWJsZWQoITAsITEpLCh0aGlzLl90b3RhbFRpbWUhPT10fHwwPT09dGhpcy5fZHVyYXRpb24pJiYodGhpcy5yZW5kZXIodCxlLCExKSx6Lmxlbmd0aCYmcSgpKX1yZXR1cm4gdGhpc30sbi5wcm9ncmVzcz1uLnRvdGFsUHJvZ3Jlc3M9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD90aGlzLnRvdGFsVGltZSh0aGlzLmR1cmF0aW9uKCkqdCxlKTp0aGlzLl90aW1lL3RoaXMuZHVyYXRpb24oKX0sbi5zdGFydFRpbWU9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHQhPT10aGlzLl9zdGFydFRpbWUmJih0aGlzLl9zdGFydFRpbWU9dCx0aGlzLnRpbWVsaW5lJiZ0aGlzLnRpbWVsaW5lLl9zb3J0Q2hpbGRyZW4mJnRoaXMudGltZWxpbmUuYWRkKHRoaXMsdC10aGlzLl9kZWxheSkpLHRoaXMpOnRoaXMuX3N0YXJ0VGltZX0sbi50aW1lU2NhbGU9ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3RpbWVTY2FsZTtpZih0PXR8fGgsdGhpcy5fdGltZWxpbmUmJnRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nKXt2YXIgZT10aGlzLl9wYXVzZVRpbWUsaT1lfHwwPT09ZT9lOnRoaXMuX3RpbWVsaW5lLnRvdGFsVGltZSgpO3RoaXMuX3N0YXJ0VGltZT1pLShpLXRoaXMuX3N0YXJ0VGltZSkqdGhpcy5fdGltZVNjYWxlL3R9cmV0dXJuIHRoaXMuX3RpbWVTY2FsZT10LHRoaXMuX3VuY2FjaGUoITEpfSxuLnJldmVyc2VkPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0IT10aGlzLl9yZXZlcnNlZCYmKHRoaXMuX3JldmVyc2VkPXQsdGhpcy50b3RhbFRpbWUodGhpcy5fdGltZWxpbmUmJiF0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZz90aGlzLnRvdGFsRHVyYXRpb24oKS10aGlzLl90b3RhbFRpbWU6dGhpcy5fdG90YWxUaW1lLCEwKSksdGhpcyk6dGhpcy5fcmV2ZXJzZWR9LG4ucGF1c2VkPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl9wYXVzZWQ7aWYodCE9dGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lbGluZSl7YXx8dHx8ci53YWtlKCk7dmFyIGU9dGhpcy5fdGltZWxpbmUsaT1lLnJhd1RpbWUoKSxzPWktdGhpcy5fcGF1c2VUaW1lOyF0JiZlLnNtb290aENoaWxkVGltaW5nJiYodGhpcy5fc3RhcnRUaW1lKz1zLHRoaXMuX3VuY2FjaGUoITEpKSx0aGlzLl9wYXVzZVRpbWU9dD9pOm51bGwsdGhpcy5fcGF1c2VkPXQsdGhpcy5fYWN0aXZlPXRoaXMuaXNBY3RpdmUoKSwhdCYmMCE9PXMmJnRoaXMuX2luaXR0ZWQmJnRoaXMuZHVyYXRpb24oKSYmdGhpcy5yZW5kZXIoZS5zbW9vdGhDaGlsZFRpbWluZz90aGlzLl90b3RhbFRpbWU6KGktdGhpcy5fc3RhcnRUaW1lKS90aGlzLl90aW1lU2NhbGUsITAsITApfXJldHVybiB0aGlzLl9nYyYmIXQmJnRoaXMuX2VuYWJsZWQoITAsITEpLHRoaXN9O3ZhciBSPWQoXCJjb3JlLlNpbXBsZVRpbWVsaW5lXCIsZnVuY3Rpb24odCl7eC5jYWxsKHRoaXMsMCx0KSx0aGlzLmF1dG9SZW1vdmVDaGlsZHJlbj10aGlzLnNtb290aENoaWxkVGltaW5nPSEwfSk7bj1SLnByb3RvdHlwZT1uZXcgeCxuLmNvbnN0cnVjdG9yPVIsbi5raWxsKCkuX2djPSExLG4uX2ZpcnN0PW4uX2xhc3Q9bnVsbCxuLl9zb3J0Q2hpbGRyZW49ITEsbi5hZGQ9bi5pbnNlcnQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxzO2lmKHQuX3N0YXJ0VGltZT1OdW1iZXIoZXx8MCkrdC5fZGVsYXksdC5fcGF1c2VkJiZ0aGlzIT09dC5fdGltZWxpbmUmJih0Ll9wYXVzZVRpbWU9dC5fc3RhcnRUaW1lKyh0aGlzLnJhd1RpbWUoKS10Ll9zdGFydFRpbWUpL3QuX3RpbWVTY2FsZSksdC50aW1lbGluZSYmdC50aW1lbGluZS5fcmVtb3ZlKHQsITApLHQudGltZWxpbmU9dC5fdGltZWxpbmU9dGhpcyx0Ll9nYyYmdC5fZW5hYmxlZCghMCwhMCksaT10aGlzLl9sYXN0LHRoaXMuX3NvcnRDaGlsZHJlbilmb3Iocz10Ll9zdGFydFRpbWU7aSYmaS5fc3RhcnRUaW1lPnM7KWk9aS5fcHJldjtyZXR1cm4gaT8odC5fbmV4dD1pLl9uZXh0LGkuX25leHQ9dCk6KHQuX25leHQ9dGhpcy5fZmlyc3QsdGhpcy5fZmlyc3Q9dCksdC5fbmV4dD90Ll9uZXh0Ll9wcmV2PXQ6dGhpcy5fbGFzdD10LHQuX3ByZXY9aSx0aGlzLl90aW1lbGluZSYmdGhpcy5fdW5jYWNoZSghMCksdGhpc30sbi5fcmVtb3ZlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHQudGltZWxpbmU9PT10aGlzJiYoZXx8dC5fZW5hYmxlZCghMSwhMCksdC50aW1lbGluZT1udWxsLHQuX3ByZXY/dC5fcHJldi5fbmV4dD10Ll9uZXh0OnRoaXMuX2ZpcnN0PT09dCYmKHRoaXMuX2ZpcnN0PXQuX25leHQpLHQuX25leHQ/dC5fbmV4dC5fcHJldj10Ll9wcmV2OnRoaXMuX2xhc3Q9PT10JiYodGhpcy5fbGFzdD10Ll9wcmV2KSx0aGlzLl90aW1lbGluZSYmdGhpcy5fdW5jYWNoZSghMCkpLHRoaXN9LG4ucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuPXRoaXMuX2ZpcnN0O2Zvcih0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10aGlzLl9yYXdQcmV2VGltZT10O247KXM9bi5fbmV4dCwobi5fYWN0aXZlfHx0Pj1uLl9zdGFydFRpbWUmJiFuLl9wYXVzZWQpJiYobi5fcmV2ZXJzZWQ/bi5yZW5kZXIoKG4uX2RpcnR5P24udG90YWxEdXJhdGlvbigpOm4uX3RvdGFsRHVyYXRpb24pLSh0LW4uX3N0YXJ0VGltZSkqbi5fdGltZVNjYWxlLGUsaSk6bi5yZW5kZXIoKHQtbi5fc3RhcnRUaW1lKSpuLl90aW1lU2NhbGUsZSxpKSksbj1zfSxuLnJhd1RpbWU9ZnVuY3Rpb24oKXtyZXR1cm4gYXx8ci53YWtlKCksdGhpcy5fdG90YWxUaW1lfTt2YXIgRD1kKFwiVHdlZW5MaXRlXCIsZnVuY3Rpb24oZSxpLHMpe2lmKHguY2FsbCh0aGlzLGkscyksdGhpcy5yZW5kZXI9RC5wcm90b3R5cGUucmVuZGVyLG51bGw9PWUpdGhyb3dcIkNhbm5vdCB0d2VlbiBhIG51bGwgdGFyZ2V0LlwiO3RoaXMudGFyZ2V0PWU9XCJzdHJpbmdcIiE9dHlwZW9mIGU/ZTpELnNlbGVjdG9yKGUpfHxlO3ZhciBuLHIsYSxvPWUuanF1ZXJ5fHxlLmxlbmd0aCYmZSE9PXQmJmVbMF0mJihlWzBdPT09dHx8ZVswXS5ub2RlVHlwZSYmZVswXS5zdHlsZSYmIWUubm9kZVR5cGUpLGw9dGhpcy52YXJzLm92ZXJ3cml0ZTtpZih0aGlzLl9vdmVyd3JpdGU9bD1udWxsPT1sP0dbRC5kZWZhdWx0T3ZlcndyaXRlXTpcIm51bWJlclwiPT10eXBlb2YgbD9sPj4wOkdbbF0sKG98fGUgaW5zdGFuY2VvZiBBcnJheXx8ZS5wdXNoJiZtKGUpKSYmXCJudW1iZXJcIiE9dHlwZW9mIGVbMF0pZm9yKHRoaXMuX3RhcmdldHM9YT1fLmNhbGwoZSwwKSx0aGlzLl9wcm9wTG9va3VwPVtdLHRoaXMuX3NpYmxpbmdzPVtdLG49MDthLmxlbmd0aD5uO24rKylyPWFbbl0scj9cInN0cmluZ1wiIT10eXBlb2Ygcj9yLmxlbmd0aCYmciE9PXQmJnJbMF0mJihyWzBdPT09dHx8clswXS5ub2RlVHlwZSYmclswXS5zdHlsZSYmIXIubm9kZVR5cGUpPyhhLnNwbGljZShuLS0sMSksdGhpcy5fdGFyZ2V0cz1hPWEuY29uY2F0KF8uY2FsbChyLDApKSk6KHRoaXMuX3NpYmxpbmdzW25dPU0ocix0aGlzLCExKSwxPT09bCYmdGhpcy5fc2libGluZ3Nbbl0ubGVuZ3RoPjEmJiQocix0aGlzLG51bGwsMSx0aGlzLl9zaWJsaW5nc1tuXSkpOihyPWFbbi0tXT1ELnNlbGVjdG9yKHIpLFwic3RyaW5nXCI9PXR5cGVvZiByJiZhLnNwbGljZShuKzEsMSkpOmEuc3BsaWNlKG4tLSwxKTtlbHNlIHRoaXMuX3Byb3BMb29rdXA9e30sdGhpcy5fc2libGluZ3M9TShlLHRoaXMsITEpLDE9PT1sJiZ0aGlzLl9zaWJsaW5ncy5sZW5ndGg+MSYmJChlLHRoaXMsbnVsbCwxLHRoaXMuX3NpYmxpbmdzKTsodGhpcy52YXJzLmltbWVkaWF0ZVJlbmRlcnx8MD09PWkmJjA9PT10aGlzLl9kZWxheSYmdGhpcy52YXJzLmltbWVkaWF0ZVJlbmRlciE9PSExKSYmKHRoaXMuX3RpbWU9LWgsdGhpcy5yZW5kZXIoLXRoaXMuX2RlbGF5KSl9LCEwKSxJPWZ1bmN0aW9uKGUpe3JldHVybiBlLmxlbmd0aCYmZSE9PXQmJmVbMF0mJihlWzBdPT09dHx8ZVswXS5ub2RlVHlwZSYmZVswXS5zdHlsZSYmIWUubm9kZVR5cGUpfSxFPWZ1bmN0aW9uKHQsZSl7dmFyIGkscz17fTtmb3IoaSBpbiB0KWpbaV18fGkgaW4gZSYmXCJ0cmFuc2Zvcm1cIiE9PWkmJlwieFwiIT09aSYmXCJ5XCIhPT1pJiZcIndpZHRoXCIhPT1pJiZcImhlaWdodFwiIT09aSYmXCJjbGFzc05hbWVcIiE9PWkmJlwiYm9yZGVyXCIhPT1pfHwhKCFMW2ldfHxMW2ldJiZMW2ldLl9hdXRvQ1NTKXx8KHNbaV09dFtpXSxkZWxldGUgdFtpXSk7dC5jc3M9c307bj1ELnByb3RvdHlwZT1uZXcgeCxuLmNvbnN0cnVjdG9yPUQsbi5raWxsKCkuX2djPSExLG4ucmF0aW89MCxuLl9maXJzdFBUPW4uX3RhcmdldHM9bi5fb3ZlcndyaXR0ZW5Qcm9wcz1uLl9zdGFydEF0PW51bGwsbi5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD1uLl9sYXp5PSExLEQudmVyc2lvbj1cIjEuMTIuMVwiLEQuZGVmYXVsdEVhc2U9bi5fZWFzZT1uZXcgVChudWxsLG51bGwsMSwxKSxELmRlZmF1bHRPdmVyd3JpdGU9XCJhdXRvXCIsRC50aWNrZXI9cixELmF1dG9TbGVlcD0hMCxELmxhZ1Ntb290aGluZz1mdW5jdGlvbih0LGUpe3IubGFnU21vb3RoaW5nKHQsZSl9LEQuc2VsZWN0b3I9dC4kfHx0LmpRdWVyeXx8ZnVuY3Rpb24oZSl7cmV0dXJuIHQuJD8oRC5zZWxlY3Rvcj10LiQsdC4kKGUpKTp0LmRvY3VtZW50P3QuZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCIjXCI9PT1lLmNoYXJBdCgwKT9lLnN1YnN0cigxKTplKTplfTt2YXIgej1bXSxPPXt9LE49RC5faW50ZXJuYWxzPXtpc0FycmF5Om0saXNTZWxlY3RvcjpJLGxhenlUd2VlbnM6en0sTD1ELl9wbHVnaW5zPXt9LFU9Ti50d2Vlbkxvb2t1cD17fSxGPTAsaj1OLnJlc2VydmVkUHJvcHM9e2Vhc2U6MSxkZWxheToxLG92ZXJ3cml0ZToxLG9uQ29tcGxldGU6MSxvbkNvbXBsZXRlUGFyYW1zOjEsb25Db21wbGV0ZVNjb3BlOjEsdXNlRnJhbWVzOjEscnVuQmFja3dhcmRzOjEsc3RhcnRBdDoxLG9uVXBkYXRlOjEsb25VcGRhdGVQYXJhbXM6MSxvblVwZGF0ZVNjb3BlOjEsb25TdGFydDoxLG9uU3RhcnRQYXJhbXM6MSxvblN0YXJ0U2NvcGU6MSxvblJldmVyc2VDb21wbGV0ZToxLG9uUmV2ZXJzZUNvbXBsZXRlUGFyYW1zOjEsb25SZXZlcnNlQ29tcGxldGVTY29wZToxLG9uUmVwZWF0OjEsb25SZXBlYXRQYXJhbXM6MSxvblJlcGVhdFNjb3BlOjEsZWFzZVBhcmFtczoxLHlveW86MSxpbW1lZGlhdGVSZW5kZXI6MSxyZXBlYXQ6MSxyZXBlYXREZWxheToxLGRhdGE6MSxwYXVzZWQ6MSxyZXZlcnNlZDoxLGF1dG9DU1M6MSxsYXp5OjF9LEc9e25vbmU6MCxhbGw6MSxhdXRvOjIsY29uY3VycmVudDozLGFsbE9uU3RhcnQ6NCxwcmVleGlzdGluZzo1LFwidHJ1ZVwiOjEsXCJmYWxzZVwiOjB9LFE9eC5fcm9vdEZyYW1lc1RpbWVsaW5lPW5ldyBSLEI9eC5fcm9vdFRpbWVsaW5lPW5ldyBSLHE9ZnVuY3Rpb24oKXt2YXIgdD16Lmxlbmd0aDtmb3IoTz17fTstLXQ+LTE7KWk9elt0XSxpJiZpLl9sYXp5IT09ITEmJihpLnJlbmRlcihpLl9sYXp5LCExLCEwKSxpLl9sYXp5PSExKTt6Lmxlbmd0aD0wfTtCLl9zdGFydFRpbWU9ci50aW1lLFEuX3N0YXJ0VGltZT1yLmZyYW1lLEIuX2FjdGl2ZT1RLl9hY3RpdmU9ITAsc2V0VGltZW91dChxLDEpLHguX3VwZGF0ZVJvb3Q9RC5yZW5kZXI9ZnVuY3Rpb24oKXt2YXIgdCxlLGk7aWYoei5sZW5ndGgmJnEoKSxCLnJlbmRlcigoci50aW1lLUIuX3N0YXJ0VGltZSkqQi5fdGltZVNjYWxlLCExLCExKSxRLnJlbmRlcigoci5mcmFtZS1RLl9zdGFydFRpbWUpKlEuX3RpbWVTY2FsZSwhMSwhMSksei5sZW5ndGgmJnEoKSwhKHIuZnJhbWUlMTIwKSl7Zm9yKGkgaW4gVSl7Zm9yKGU9VVtpXS50d2VlbnMsdD1lLmxlbmd0aDstLXQ+LTE7KWVbdF0uX2djJiZlLnNwbGljZSh0LDEpOzA9PT1lLmxlbmd0aCYmZGVsZXRlIFVbaV19aWYoaT1CLl9maXJzdCwoIWl8fGkuX3BhdXNlZCkmJkQuYXV0b1NsZWVwJiYhUS5fZmlyc3QmJjE9PT1yLl9saXN0ZW5lcnMudGljay5sZW5ndGgpe2Zvcig7aSYmaS5fcGF1c2VkOylpPWkuX25leHQ7aXx8ci5zbGVlcCgpfX19LHIuYWRkRXZlbnRMaXN0ZW5lcihcInRpY2tcIix4Ll91cGRhdGVSb290KTt2YXIgTT1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbixyPXQuX2dzVHdlZW5JRDtpZihVW3J8fCh0Ll9nc1R3ZWVuSUQ9cj1cInRcIitGKyspXXx8KFVbcl09e3RhcmdldDp0LHR3ZWVuczpbXX0pLGUmJihzPVVbcl0udHdlZW5zLHNbbj1zLmxlbmd0aF09ZSxpKSlmb3IoOy0tbj4tMTspc1tuXT09PWUmJnMuc3BsaWNlKG4sMSk7cmV0dXJuIFVbcl0udHdlZW5zfSwkPWZ1bmN0aW9uKHQsZSxpLHMsbil7dmFyIHIsYSxvLGw7aWYoMT09PXN8fHM+PTQpe2ZvcihsPW4ubGVuZ3RoLHI9MDtsPnI7cisrKWlmKChvPW5bcl0pIT09ZSlvLl9nY3x8by5fZW5hYmxlZCghMSwhMSkmJihhPSEwKTtlbHNlIGlmKDU9PT1zKWJyZWFrO3JldHVybiBhfXZhciBfLHU9ZS5fc3RhcnRUaW1lK2gsbT1bXSxmPTAscD0wPT09ZS5fZHVyYXRpb247Zm9yKHI9bi5sZW5ndGg7LS1yPi0xOykobz1uW3JdKT09PWV8fG8uX2djfHxvLl9wYXVzZWR8fChvLl90aW1lbGluZSE9PWUuX3RpbWVsaW5lPyhfPV98fEsoZSwwLHApLDA9PT1LKG8sXyxwKSYmKG1bZisrXT1vKSk6dT49by5fc3RhcnRUaW1lJiZvLl9zdGFydFRpbWUrby50b3RhbER1cmF0aW9uKCkvby5fdGltZVNjYWxlPnUmJigocHx8IW8uX2luaXR0ZWQpJiYyZS0xMD49dS1vLl9zdGFydFRpbWV8fChtW2YrK109bykpKTtmb3Iocj1mOy0tcj4tMTspbz1tW3JdLDI9PT1zJiZvLl9raWxsKGksdCkmJihhPSEwKSwoMiE9PXN8fCFvLl9maXJzdFBUJiZvLl9pbml0dGVkKSYmby5fZW5hYmxlZCghMSwhMSkmJihhPSEwKTtyZXR1cm4gYX0sSz1mdW5jdGlvbih0LGUsaSl7Zm9yKHZhciBzPXQuX3RpbWVsaW5lLG49cy5fdGltZVNjYWxlLHI9dC5fc3RhcnRUaW1lO3MuX3RpbWVsaW5lOyl7aWYocis9cy5fc3RhcnRUaW1lLG4qPXMuX3RpbWVTY2FsZSxzLl9wYXVzZWQpcmV0dXJuLTEwMDtzPXMuX3RpbWVsaW5lfXJldHVybiByLz1uLHI+ZT9yLWU6aSYmcj09PWV8fCF0Ll9pbml0dGVkJiYyKmg+ci1lP2g6KHIrPXQudG90YWxEdXJhdGlvbigpL3QuX3RpbWVTY2FsZS9uKT5lK2g/MDpyLWUtaH07bi5faW5pdD1mdW5jdGlvbigpe3ZhciB0LGUsaSxzLG4scj10aGlzLnZhcnMsYT10aGlzLl9vdmVyd3JpdHRlblByb3BzLG89dGhpcy5fZHVyYXRpb24sbD0hIXIuaW1tZWRpYXRlUmVuZGVyLGg9ci5lYXNlO2lmKHIuc3RhcnRBdCl7dGhpcy5fc3RhcnRBdCYmKHRoaXMuX3N0YXJ0QXQucmVuZGVyKC0xLCEwKSx0aGlzLl9zdGFydEF0LmtpbGwoKSksbj17fTtmb3IocyBpbiByLnN0YXJ0QXQpbltzXT1yLnN0YXJ0QXRbc107aWYobi5vdmVyd3JpdGU9ITEsbi5pbW1lZGlhdGVSZW5kZXI9ITAsbi5sYXp5PWwmJnIubGF6eSE9PSExLG4uc3RhcnRBdD1uLmRlbGF5PW51bGwsdGhpcy5fc3RhcnRBdD1ELnRvKHRoaXMudGFyZ2V0LDAsbiksbClpZih0aGlzLl90aW1lPjApdGhpcy5fc3RhcnRBdD1udWxsO2Vsc2UgaWYoMCE9PW8pcmV0dXJufWVsc2UgaWYoci5ydW5CYWNrd2FyZHMmJjAhPT1vKWlmKHRoaXMuX3N0YXJ0QXQpdGhpcy5fc3RhcnRBdC5yZW5kZXIoLTEsITApLHRoaXMuX3N0YXJ0QXQua2lsbCgpLHRoaXMuX3N0YXJ0QXQ9bnVsbDtlbHNle2k9e307Zm9yKHMgaW4gcilqW3NdJiZcImF1dG9DU1NcIiE9PXN8fChpW3NdPXJbc10pO2lmKGkub3ZlcndyaXRlPTAsaS5kYXRhPVwiaXNGcm9tU3RhcnRcIixpLmxhenk9bCYmci5sYXp5IT09ITEsaS5pbW1lZGlhdGVSZW5kZXI9bCx0aGlzLl9zdGFydEF0PUQudG8odGhpcy50YXJnZXQsMCxpKSxsKXtpZigwPT09dGhpcy5fdGltZSlyZXR1cm59ZWxzZSB0aGlzLl9zdGFydEF0Ll9pbml0KCksdGhpcy5fc3RhcnRBdC5fZW5hYmxlZCghMSl9aWYodGhpcy5fZWFzZT1oP2ggaW5zdGFuY2VvZiBUP3IuZWFzZVBhcmFtcyBpbnN0YW5jZW9mIEFycmF5P2guY29uZmlnLmFwcGx5KGgsci5lYXNlUGFyYW1zKTpoOlwiZnVuY3Rpb25cIj09dHlwZW9mIGg/bmV3IFQoaCxyLmVhc2VQYXJhbXMpOnlbaF18fEQuZGVmYXVsdEVhc2U6RC5kZWZhdWx0RWFzZSx0aGlzLl9lYXNlVHlwZT10aGlzLl9lYXNlLl90eXBlLHRoaXMuX2Vhc2VQb3dlcj10aGlzLl9lYXNlLl9wb3dlcix0aGlzLl9maXJzdFBUPW51bGwsdGhpcy5fdGFyZ2V0cylmb3IodD10aGlzLl90YXJnZXRzLmxlbmd0aDstLXQ+LTE7KXRoaXMuX2luaXRQcm9wcyh0aGlzLl90YXJnZXRzW3RdLHRoaXMuX3Byb3BMb29rdXBbdF09e30sdGhpcy5fc2libGluZ3NbdF0sYT9hW3RdOm51bGwpJiYoZT0hMCk7ZWxzZSBlPXRoaXMuX2luaXRQcm9wcyh0aGlzLnRhcmdldCx0aGlzLl9wcm9wTG9va3VwLHRoaXMuX3NpYmxpbmdzLGEpO2lmKGUmJkQuX29uUGx1Z2luRXZlbnQoXCJfb25Jbml0QWxsUHJvcHNcIix0aGlzKSxhJiYodGhpcy5fZmlyc3RQVHx8XCJmdW5jdGlvblwiIT10eXBlb2YgdGhpcy50YXJnZXQmJnRoaXMuX2VuYWJsZWQoITEsITEpKSxyLnJ1bkJhY2t3YXJkcylmb3IoaT10aGlzLl9maXJzdFBUO2k7KWkucys9aS5jLGkuYz0taS5jLGk9aS5fbmV4dDt0aGlzLl9vblVwZGF0ZT1yLm9uVXBkYXRlLHRoaXMuX2luaXR0ZWQ9ITB9LG4uX2luaXRQcm9wcz1mdW5jdGlvbihlLGkscyxuKXt2YXIgcixhLG8sbCxoLF87aWYobnVsbD09ZSlyZXR1cm4hMTtPW2UuX2dzVHdlZW5JRF0mJnEoKSx0aGlzLnZhcnMuY3NzfHxlLnN0eWxlJiZlIT09dCYmZS5ub2RlVHlwZSYmTC5jc3MmJnRoaXMudmFycy5hdXRvQ1NTIT09ITEmJkUodGhpcy52YXJzLGUpO2ZvcihyIGluIHRoaXMudmFycyl7aWYoXz10aGlzLnZhcnNbcl0saltyXSlfJiYoXyBpbnN0YW5jZW9mIEFycmF5fHxfLnB1c2gmJm0oXykpJiYtMSE9PV8uam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpJiYodGhpcy52YXJzW3JdPV89dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhfLHRoaXMpKTtlbHNlIGlmKExbcl0mJihsPW5ldyBMW3JdKS5fb25Jbml0VHdlZW4oZSx0aGlzLnZhcnNbcl0sdGhpcykpe2Zvcih0aGlzLl9maXJzdFBUPWg9e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDpsLHA6XCJzZXRSYXRpb1wiLHM6MCxjOjEsZjohMCxuOnIscGc6ITAscHI6bC5fcHJpb3JpdHl9LGE9bC5fb3ZlcndyaXRlUHJvcHMubGVuZ3RoOy0tYT4tMTspaVtsLl9vdmVyd3JpdGVQcm9wc1thXV09dGhpcy5fZmlyc3RQVDsobC5fcHJpb3JpdHl8fGwuX29uSW5pdEFsbFByb3BzKSYmKG89ITApLChsLl9vbkRpc2FibGV8fGwuX29uRW5hYmxlKSYmKHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9ITApfWVsc2UgdGhpcy5fZmlyc3RQVD1pW3JdPWg9e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDplLHA6cixmOlwiZnVuY3Rpb25cIj09dHlwZW9mIGVbcl0sbjpyLHBnOiExLHByOjB9LGgucz1oLmY/ZVtyLmluZGV4T2YoXCJzZXRcIil8fFwiZnVuY3Rpb25cIiE9dHlwZW9mIGVbXCJnZXRcIityLnN1YnN0cigzKV0/cjpcImdldFwiK3Iuc3Vic3RyKDMpXSgpOnBhcnNlRmxvYXQoZVtyXSksaC5jPVwic3RyaW5nXCI9PXR5cGVvZiBfJiZcIj1cIj09PV8uY2hhckF0KDEpP3BhcnNlSW50KF8uY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIoXy5zdWJzdHIoMikpOk51bWJlcihfKS1oLnN8fDA7aCYmaC5fbmV4dCYmKGguX25leHQuX3ByZXY9aCl9cmV0dXJuIG4mJnRoaXMuX2tpbGwobixlKT90aGlzLl9pbml0UHJvcHMoZSxpLHMsbik6dGhpcy5fb3ZlcndyaXRlPjEmJnRoaXMuX2ZpcnN0UFQmJnMubGVuZ3RoPjEmJiQoZSx0aGlzLGksdGhpcy5fb3ZlcndyaXRlLHMpPyh0aGlzLl9raWxsKGksZSksdGhpcy5faW5pdFByb3BzKGUsaSxzLG4pKToodGhpcy5fZmlyc3RQVCYmKHRoaXMudmFycy5sYXp5IT09ITEmJnRoaXMuX2R1cmF0aW9ufHx0aGlzLnZhcnMubGF6eSYmIXRoaXMuX2R1cmF0aW9uKSYmKE9bZS5fZ3NUd2VlbklEXT0hMCksbyl9LG4ucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuLHIsYSxvPXRoaXMuX3RpbWUsbD10aGlzLl9kdXJhdGlvbixfPXRoaXMuX3Jhd1ByZXZUaW1lO2lmKHQ+PWwpdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9bCx0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuX2NhbGNFbmQ/dGhpcy5fZWFzZS5nZXRSYXRpbygxKToxLHRoaXMuX3JldmVyc2VkfHwocz0hMCxuPVwib25Db21wbGV0ZVwiKSwwPT09bCYmKHRoaXMuX2luaXR0ZWR8fCF0aGlzLnZhcnMubGF6eXx8aSkmJih0aGlzLl9zdGFydFRpbWU9PT10aGlzLl90aW1lbGluZS5fZHVyYXRpb24mJih0PTApLCgwPT09dHx8MD5ffHxfPT09aCkmJl8hPT10JiYoaT0hMCxfPmgmJihuPVwib25SZXZlcnNlQ29tcGxldGVcIikpLHRoaXMuX3Jhd1ByZXZUaW1lPWE9IWV8fHR8fF89PT10P3Q6aCk7ZWxzZSBpZigxZS03PnQpdGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9MCx0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuX2NhbGNFbmQ/dGhpcy5fZWFzZS5nZXRSYXRpbygwKTowLCgwIT09b3x8MD09PWwmJl8+MCYmXyE9PWgpJiYobj1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIscz10aGlzLl9yZXZlcnNlZCksMD50Pyh0aGlzLl9hY3RpdmU9ITEsMD09PWwmJih0aGlzLl9pbml0dGVkfHwhdGhpcy52YXJzLmxhenl8fGkpJiYoXz49MCYmKGk9ITApLHRoaXMuX3Jhd1ByZXZUaW1lPWE9IWV8fHR8fF89PT10P3Q6aCkpOnRoaXMuX2luaXR0ZWR8fChpPSEwKTtlbHNlIGlmKHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXQsdGhpcy5fZWFzZVR5cGUpe3ZhciB1PXQvbCxtPXRoaXMuX2Vhc2VUeXBlLGY9dGhpcy5fZWFzZVBvd2VyOygxPT09bXx8Mz09PW0mJnU+PS41KSYmKHU9MS11KSwzPT09bSYmKHUqPTIpLDE9PT1mP3UqPXU6Mj09PWY/dSo9dSp1OjM9PT1mP3UqPXUqdSp1OjQ9PT1mJiYodSo9dSp1KnUqdSksdGhpcy5yYXRpbz0xPT09bT8xLXU6Mj09PW0/dTouNT50L2w/dS8yOjEtdS8yfWVsc2UgdGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKHQvbCk7aWYodGhpcy5fdGltZSE9PW98fGkpe2lmKCF0aGlzLl9pbml0dGVkKXtpZih0aGlzLl9pbml0KCksIXRoaXMuX2luaXR0ZWR8fHRoaXMuX2djKXJldHVybjtpZighaSYmdGhpcy5fZmlyc3RQVCYmKHRoaXMudmFycy5sYXp5IT09ITEmJnRoaXMuX2R1cmF0aW9ufHx0aGlzLnZhcnMubGF6eSYmIXRoaXMuX2R1cmF0aW9uKSlyZXR1cm4gdGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9byx0aGlzLl9yYXdQcmV2VGltZT1fLHoucHVzaCh0aGlzKSx0aGlzLl9sYXp5PXQsdm9pZCAwO3RoaXMuX3RpbWUmJiFzP3RoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbyh0aGlzLl90aW1lL2wpOnMmJnRoaXMuX2Vhc2UuX2NhbGNFbmQmJih0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8oMD09PXRoaXMuX3RpbWU/MDoxKSl9Zm9yKHRoaXMuX2xhenkhPT0hMSYmKHRoaXMuX2xhenk9ITEpLHRoaXMuX2FjdGl2ZXx8IXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZSE9PW8mJnQ+PTAmJih0aGlzLl9hY3RpdmU9ITApLDA9PT1vJiYodGhpcy5fc3RhcnRBdCYmKHQ+PTA/dGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpOm58fChuPVwiX2R1bW15R1NcIikpLHRoaXMudmFycy5vblN0YXJ0JiYoMCE9PXRoaXMuX3RpbWV8fDA9PT1sKSYmKGV8fHRoaXMudmFycy5vblN0YXJ0LmFwcGx5KHRoaXMudmFycy5vblN0YXJ0U2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uU3RhcnRQYXJhbXN8fGcpKSkscj10aGlzLl9maXJzdFBUO3I7KXIuZj9yLnRbci5wXShyLmMqdGhpcy5yYXRpbytyLnMpOnIudFtyLnBdPXIuYyp0aGlzLnJhdGlvK3IucyxyPXIuX25leHQ7dGhpcy5fb25VcGRhdGUmJigwPnQmJnRoaXMuX3N0YXJ0QXQmJnRoaXMuX3N0YXJ0VGltZSYmdGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpLGV8fCh0aGlzLl90aW1lIT09b3x8cykmJnRoaXMuX29uVXBkYXRlLmFwcGx5KHRoaXMudmFycy5vblVwZGF0ZVNjb3BlfHx0aGlzLHRoaXMudmFycy5vblVwZGF0ZVBhcmFtc3x8ZykpLG4mJih0aGlzLl9nY3x8KDA+dCYmdGhpcy5fc3RhcnRBdCYmIXRoaXMuX29uVXBkYXRlJiZ0aGlzLl9zdGFydFRpbWUmJnRoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKSxzJiYodGhpcy5fdGltZWxpbmUuYXV0b1JlbW92ZUNoaWxkcmVuJiZ0aGlzLl9lbmFibGVkKCExLCExKSx0aGlzLl9hY3RpdmU9ITEpLCFlJiZ0aGlzLnZhcnNbbl0mJnRoaXMudmFyc1tuXS5hcHBseSh0aGlzLnZhcnNbbitcIlNjb3BlXCJdfHx0aGlzLHRoaXMudmFyc1tuK1wiUGFyYW1zXCJdfHxnKSwwPT09bCYmdGhpcy5fcmF3UHJldlRpbWU9PT1oJiZhIT09aCYmKHRoaXMuX3Jhd1ByZXZUaW1lPTApKSl9fSxuLl9raWxsPWZ1bmN0aW9uKHQsZSl7aWYoXCJhbGxcIj09PXQmJih0PW51bGwpLG51bGw9PXQmJihudWxsPT1lfHxlPT09dGhpcy50YXJnZXQpKXJldHVybiB0aGlzLl9sYXp5PSExLHRoaXMuX2VuYWJsZWQoITEsITEpO2U9XCJzdHJpbmdcIiE9dHlwZW9mIGU/ZXx8dGhpcy5fdGFyZ2V0c3x8dGhpcy50YXJnZXQ6RC5zZWxlY3RvcihlKXx8ZTt2YXIgaSxzLG4scixhLG8sbCxoO2lmKChtKGUpfHxJKGUpKSYmXCJudW1iZXJcIiE9dHlwZW9mIGVbMF0pZm9yKGk9ZS5sZW5ndGg7LS1pPi0xOyl0aGlzLl9raWxsKHQsZVtpXSkmJihvPSEwKTtlbHNle2lmKHRoaXMuX3RhcmdldHMpe2ZvcihpPXRoaXMuX3RhcmdldHMubGVuZ3RoOy0taT4tMTspaWYoZT09PXRoaXMuX3RhcmdldHNbaV0pe2E9dGhpcy5fcHJvcExvb2t1cFtpXXx8e30sdGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz10aGlzLl9vdmVyd3JpdHRlblByb3BzfHxbXSxzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHNbaV09dD90aGlzLl9vdmVyd3JpdHRlblByb3BzW2ldfHx7fTpcImFsbFwiO2JyZWFrfX1lbHNle2lmKGUhPT10aGlzLnRhcmdldClyZXR1cm4hMTthPXRoaXMuX3Byb3BMb29rdXAscz10aGlzLl9vdmVyd3JpdHRlblByb3BzPXQ/dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc3x8e306XCJhbGxcIn1pZihhKXtsPXR8fGEsaD10IT09cyYmXCJhbGxcIiE9PXMmJnQhPT1hJiYoXCJvYmplY3RcIiE9dHlwZW9mIHR8fCF0Ll90ZW1wS2lsbCk7Zm9yKG4gaW4gbCkocj1hW25dKSYmKHIucGcmJnIudC5fa2lsbChsKSYmKG89ITApLHIucGcmJjAhPT1yLnQuX292ZXJ3cml0ZVByb3BzLmxlbmd0aHx8KHIuX3ByZXY/ci5fcHJldi5fbmV4dD1yLl9uZXh0OnI9PT10aGlzLl9maXJzdFBUJiYodGhpcy5fZmlyc3RQVD1yLl9uZXh0KSxyLl9uZXh0JiYoci5fbmV4dC5fcHJldj1yLl9wcmV2KSxyLl9uZXh0PXIuX3ByZXY9bnVsbCksZGVsZXRlIGFbbl0pLGgmJihzW25dPTEpOyF0aGlzLl9maXJzdFBUJiZ0aGlzLl9pbml0dGVkJiZ0aGlzLl9lbmFibGVkKCExLCExKX19cmV0dXJuIG99LG4uaW52YWxpZGF0ZT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkJiZELl9vblBsdWdpbkV2ZW50KFwiX29uRGlzYWJsZVwiLHRoaXMpLHRoaXMuX2ZpcnN0UFQ9bnVsbCx0aGlzLl9vdmVyd3JpdHRlblByb3BzPW51bGwsdGhpcy5fb25VcGRhdGU9bnVsbCx0aGlzLl9zdGFydEF0PW51bGwsdGhpcy5faW5pdHRlZD10aGlzLl9hY3RpdmU9dGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD10aGlzLl9sYXp5PSExLHRoaXMuX3Byb3BMb29rdXA9dGhpcy5fdGFyZ2V0cz97fTpbXSx0aGlzfSxuLl9lbmFibGVkPWZ1bmN0aW9uKHQsZSl7aWYoYXx8ci53YWtlKCksdCYmdGhpcy5fZ2Mpe3ZhciBpLHM9dGhpcy5fdGFyZ2V0cztpZihzKWZvcihpPXMubGVuZ3RoOy0taT4tMTspdGhpcy5fc2libGluZ3NbaV09TShzW2ldLHRoaXMsITApO2Vsc2UgdGhpcy5fc2libGluZ3M9TSh0aGlzLnRhcmdldCx0aGlzLCEwKX1yZXR1cm4geC5wcm90b3R5cGUuX2VuYWJsZWQuY2FsbCh0aGlzLHQsZSksdGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZCYmdGhpcy5fZmlyc3RQVD9ELl9vblBsdWdpbkV2ZW50KHQ/XCJfb25FbmFibGVcIjpcIl9vbkRpc2FibGVcIix0aGlzKTohMX0sRC50bz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIG5ldyBEKHQsZSxpKX0sRC5mcm9tPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gaS5ydW5CYWNrd2FyZHM9ITAsaS5pbW1lZGlhdGVSZW5kZXI9MCE9aS5pbW1lZGlhdGVSZW5kZXIsbmV3IEQodCxlLGkpfSxELmZyb21Ubz1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gcy5zdGFydEF0PWkscy5pbW1lZGlhdGVSZW5kZXI9MCE9cy5pbW1lZGlhdGVSZW5kZXImJjAhPWkuaW1tZWRpYXRlUmVuZGVyLG5ldyBEKHQsZSxzKX0sRC5kZWxheWVkQ2FsbD1mdW5jdGlvbih0LGUsaSxzLG4pe3JldHVybiBuZXcgRChlLDAse2RlbGF5OnQsb25Db21wbGV0ZTplLG9uQ29tcGxldGVQYXJhbXM6aSxvbkNvbXBsZXRlU2NvcGU6cyxvblJldmVyc2VDb21wbGV0ZTplLG9uUmV2ZXJzZUNvbXBsZXRlUGFyYW1zOmksb25SZXZlcnNlQ29tcGxldGVTY29wZTpzLGltbWVkaWF0ZVJlbmRlcjohMSx1c2VGcmFtZXM6bixvdmVyd3JpdGU6MH0pfSxELnNldD1mdW5jdGlvbih0LGUpe3JldHVybiBuZXcgRCh0LDAsZSl9LEQuZ2V0VHdlZW5zT2Y9ZnVuY3Rpb24odCxlKXtpZihudWxsPT10KXJldHVybltdO3Q9XCJzdHJpbmdcIiE9dHlwZW9mIHQ/dDpELnNlbGVjdG9yKHQpfHx0O3ZhciBpLHMsbixyO2lmKChtKHQpfHxJKHQpKSYmXCJudW1iZXJcIiE9dHlwZW9mIHRbMF0pe2ZvcihpPXQubGVuZ3RoLHM9W107LS1pPi0xOylzPXMuY29uY2F0KEQuZ2V0VHdlZW5zT2YodFtpXSxlKSk7Zm9yKGk9cy5sZW5ndGg7LS1pPi0xOylmb3Iocj1zW2ldLG49aTstLW4+LTE7KXI9PT1zW25dJiZzLnNwbGljZShpLDEpfWVsc2UgZm9yKHM9TSh0KS5jb25jYXQoKSxpPXMubGVuZ3RoOy0taT4tMTspKHNbaV0uX2djfHxlJiYhc1tpXS5pc0FjdGl2ZSgpKSYmcy5zcGxpY2UoaSwxKTtyZXR1cm4gc30sRC5raWxsVHdlZW5zT2Y9RC5raWxsRGVsYXllZENhbGxzVG89ZnVuY3Rpb24odCxlLGkpe1wib2JqZWN0XCI9PXR5cGVvZiBlJiYoaT1lLGU9ITEpO2Zvcih2YXIgcz1ELmdldFR3ZWVuc09mKHQsZSksbj1zLmxlbmd0aDstLW4+LTE7KXNbbl0uX2tpbGwoaSx0KX07dmFyIEg9ZChcInBsdWdpbnMuVHdlZW5QbHVnaW5cIixmdW5jdGlvbih0LGUpe3RoaXMuX292ZXJ3cml0ZVByb3BzPSh0fHxcIlwiKS5zcGxpdChcIixcIiksdGhpcy5fcHJvcE5hbWU9dGhpcy5fb3ZlcndyaXRlUHJvcHNbMF0sdGhpcy5fcHJpb3JpdHk9ZXx8MCx0aGlzLl9zdXBlcj1ILnByb3RvdHlwZX0sITApO2lmKG49SC5wcm90b3R5cGUsSC52ZXJzaW9uPVwiMS4xMC4xXCIsSC5BUEk9MixuLl9maXJzdFBUPW51bGwsbi5fYWRkVHdlZW49ZnVuY3Rpb24odCxlLGkscyxuLHIpe3ZhciBhLG87cmV0dXJuIG51bGwhPXMmJihhPVwibnVtYmVyXCI9PXR5cGVvZiBzfHxcIj1cIiE9PXMuY2hhckF0KDEpP051bWJlcihzKS1pOnBhcnNlSW50KHMuY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIocy5zdWJzdHIoMikpKT8odGhpcy5fZmlyc3RQVD1vPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6dCxwOmUsczppLGM6YSxmOlwiZnVuY3Rpb25cIj09dHlwZW9mIHRbZV0sbjpufHxlLHI6cn0sby5fbmV4dCYmKG8uX25leHQuX3ByZXY9byksbyk6dm9pZCAwfSxuLnNldFJhdGlvPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZSxpPXRoaXMuX2ZpcnN0UFQscz0xZS02O2k7KWU9aS5jKnQraS5zLGkucj9lPU1hdGgucm91bmQoZSk6cz5lJiZlPi1zJiYoZT0wKSxpLmY/aS50W2kucF0oZSk6aS50W2kucF09ZSxpPWkuX25leHR9LG4uX2tpbGw9ZnVuY3Rpb24odCl7dmFyIGUsaT10aGlzLl9vdmVyd3JpdGVQcm9wcyxzPXRoaXMuX2ZpcnN0UFQ7aWYobnVsbCE9dFt0aGlzLl9wcm9wTmFtZV0pdGhpcy5fb3ZlcndyaXRlUHJvcHM9W107ZWxzZSBmb3IoZT1pLmxlbmd0aDstLWU+LTE7KW51bGwhPXRbaVtlXV0mJmkuc3BsaWNlKGUsMSk7Zm9yKDtzOyludWxsIT10W3Mubl0mJihzLl9uZXh0JiYocy5fbmV4dC5fcHJldj1zLl9wcmV2KSxzLl9wcmV2PyhzLl9wcmV2Ll9uZXh0PXMuX25leHQscy5fcHJldj1udWxsKTp0aGlzLl9maXJzdFBUPT09cyYmKHRoaXMuX2ZpcnN0UFQ9cy5fbmV4dCkpLHM9cy5fbmV4dDtyZXR1cm4hMX0sbi5fcm91bmRQcm9wcz1mdW5jdGlvbih0LGUpe2Zvcih2YXIgaT10aGlzLl9maXJzdFBUO2k7KSh0W3RoaXMuX3Byb3BOYW1lXXx8bnVsbCE9aS5uJiZ0W2kubi5zcGxpdCh0aGlzLl9wcm9wTmFtZStcIl9cIikuam9pbihcIlwiKV0pJiYoaS5yPWUpLGk9aS5fbmV4dH0sRC5fb25QbHVnaW5FdmVudD1mdW5jdGlvbih0LGUpe3ZhciBpLHMsbixyLGEsbz1lLl9maXJzdFBUO2lmKFwiX29uSW5pdEFsbFByb3BzXCI9PT10KXtmb3IoO287KXtmb3IoYT1vLl9uZXh0LHM9bjtzJiZzLnByPm8ucHI7KXM9cy5fbmV4dDsoby5fcHJldj1zP3MuX3ByZXY6cik/by5fcHJldi5fbmV4dD1vOm49bywoby5fbmV4dD1zKT9zLl9wcmV2PW86cj1vLG89YX1vPWUuX2ZpcnN0UFQ9bn1mb3IoO287KW8ucGcmJlwiZnVuY3Rpb25cIj09dHlwZW9mIG8udFt0XSYmby50W3RdKCkmJihpPSEwKSxvPW8uX25leHQ7cmV0dXJuIGl9LEguYWN0aXZhdGU9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQubGVuZ3RoOy0tZT4tMTspdFtlXS5BUEk9PT1ILkFQSSYmKExbKG5ldyB0W2VdKS5fcHJvcE5hbWVdPXRbZV0pO3JldHVybiEwfSxjLnBsdWdpbj1mdW5jdGlvbih0KXtpZighKHQmJnQucHJvcE5hbWUmJnQuaW5pdCYmdC5BUEkpKXRocm93XCJpbGxlZ2FsIHBsdWdpbiBkZWZpbml0aW9uLlwiO3ZhciBlLGk9dC5wcm9wTmFtZSxzPXQucHJpb3JpdHl8fDAsbj10Lm92ZXJ3cml0ZVByb3BzLHI9e2luaXQ6XCJfb25Jbml0VHdlZW5cIixzZXQ6XCJzZXRSYXRpb1wiLGtpbGw6XCJfa2lsbFwiLHJvdW5kOlwiX3JvdW5kUHJvcHNcIixpbml0QWxsOlwiX29uSW5pdEFsbFByb3BzXCJ9LGE9ZChcInBsdWdpbnMuXCIraS5jaGFyQXQoMCkudG9VcHBlckNhc2UoKStpLnN1YnN0cigxKStcIlBsdWdpblwiLGZ1bmN0aW9uKCl7SC5jYWxsKHRoaXMsaSxzKSx0aGlzLl9vdmVyd3JpdGVQcm9wcz1ufHxbXX0sdC5nbG9iYWw9PT0hMCksbz1hLnByb3RvdHlwZT1uZXcgSChpKTtvLmNvbnN0cnVjdG9yPWEsYS5BUEk9dC5BUEk7Zm9yKGUgaW4gcilcImZ1bmN0aW9uXCI9PXR5cGVvZiB0W2VdJiYob1tyW2VdXT10W2VdKTtyZXR1cm4gYS52ZXJzaW9uPXQudmVyc2lvbixILmFjdGl2YXRlKFthXSksYX0saT10Ll9nc1F1ZXVlKXtmb3Iocz0wO2kubGVuZ3RoPnM7cysrKWlbc10oKTtmb3IobiBpbiBmKWZbbl0uZnVuY3x8dC5jb25zb2xlLmxvZyhcIkdTQVAgZW5jb3VudGVyZWQgbWlzc2luZyBkZXBlbmRlbmN5OiBjb20uZ3JlZW5zb2NrLlwiK24pfWE9ITF9fSkod2luZG93KTsiLCIvKiFcclxuICogVkVSU0lPTjogYmV0YSAxLjkuM1xyXG4gKiBEQVRFOiAyMDEzLTA0LTAyXHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKiovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcImVhc2luZy5CYWNrXCIsW1wiZWFzaW5nLkVhc2VcIl0sZnVuY3Rpb24odCl7dmFyIGUsaSxzLHI9d2luZG93LkdyZWVuU29ja0dsb2JhbHN8fHdpbmRvdyxuPXIuY29tLmdyZWVuc29jayxhPTIqTWF0aC5QSSxvPU1hdGguUEkvMixoPW4uX2NsYXNzLGw9ZnVuY3Rpb24oZSxpKXt2YXIgcz1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24oKXt9LCEwKSxyPXMucHJvdG90eXBlPW5ldyB0O3JldHVybiByLmNvbnN0cnVjdG9yPXMsci5nZXRSYXRpbz1pLHN9LF89dC5yZWdpc3Rlcnx8ZnVuY3Rpb24oKXt9LHU9ZnVuY3Rpb24odCxlLGkscyl7dmFyIHI9aChcImVhc2luZy5cIit0LHtlYXNlT3V0Om5ldyBlLGVhc2VJbjpuZXcgaSxlYXNlSW5PdXQ6bmV3IHN9LCEwKTtyZXR1cm4gXyhyLHQpLHJ9LGM9ZnVuY3Rpb24odCxlLGkpe3RoaXMudD10LHRoaXMudj1lLGkmJih0aGlzLm5leHQ9aSxpLnByZXY9dGhpcyx0aGlzLmM9aS52LWUsdGhpcy5nYXA9aS50LXQpfSxmPWZ1bmN0aW9uKGUsaSl7dmFyIHM9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKHQpe3RoaXMuX3AxPXR8fDA9PT10P3Q6MS43MDE1OCx0aGlzLl9wMj0xLjUyNSp0aGlzLl9wMX0sITApLHI9cy5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIHIuY29uc3RydWN0b3I9cyxyLmdldFJhdGlvPWksci5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBzKHQpfSxzfSxwPXUoXCJCYWNrXCIsZihcIkJhY2tPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4odC09MSkqdCooKHRoaXMuX3AxKzEpKnQrdGhpcy5fcDEpKzF9KSxmKFwiQmFja0luXCIsZnVuY3Rpb24odCl7cmV0dXJuIHQqdCooKHRoaXMuX3AxKzEpKnQtdGhpcy5fcDEpfSksZihcIkJhY2tJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8uNSp0KnQqKCh0aGlzLl9wMisxKSp0LXRoaXMuX3AyKTouNSooKHQtPTIpKnQqKCh0aGlzLl9wMisxKSp0K3RoaXMuX3AyKSsyKX0pKSxtPWgoXCJlYXNpbmcuU2xvd01vXCIsZnVuY3Rpb24odCxlLGkpe2U9ZXx8MD09PWU/ZTouNyxudWxsPT10P3Q9Ljc6dD4xJiYodD0xKSx0aGlzLl9wPTEhPT10P2U6MCx0aGlzLl9wMT0oMS10KS8yLHRoaXMuX3AyPXQsdGhpcy5fcDM9dGhpcy5fcDErdGhpcy5fcDIsdGhpcy5fY2FsY0VuZD1pPT09ITB9LCEwKSxkPW0ucHJvdG90eXBlPW5ldyB0O3JldHVybiBkLmNvbnN0cnVjdG9yPW0sZC5nZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZT10KyguNS10KSp0aGlzLl9wO3JldHVybiB0aGlzLl9wMT50P3RoaXMuX2NhbGNFbmQ/MS0odD0xLXQvdGhpcy5fcDEpKnQ6ZS0odD0xLXQvdGhpcy5fcDEpKnQqdCp0KmU6dD50aGlzLl9wMz90aGlzLl9jYWxjRW5kPzEtKHQ9KHQtdGhpcy5fcDMpL3RoaXMuX3AxKSp0OmUrKHQtZSkqKHQ9KHQtdGhpcy5fcDMpL3RoaXMuX3AxKSp0KnQqdDp0aGlzLl9jYWxjRW5kPzE6ZX0sbS5lYXNlPW5ldyBtKC43LC43KSxkLmNvbmZpZz1tLmNvbmZpZz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIG5ldyBtKHQsZSxpKX0sZT1oKFwiZWFzaW5nLlN0ZXBwZWRFYXNlXCIsZnVuY3Rpb24odCl7dD10fHwxLHRoaXMuX3AxPTEvdCx0aGlzLl9wMj10KzF9LCEwKSxkPWUucHJvdG90eXBlPW5ldyB0LGQuY29uc3RydWN0b3I9ZSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3JldHVybiAwPnQ/dD0wOnQ+PTEmJih0PS45OTk5OTk5OTkpLCh0aGlzLl9wMip0Pj4wKSp0aGlzLl9wMX0sZC5jb25maWc9ZS5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBlKHQpfSxpPWgoXCJlYXNpbmcuUm91Z2hFYXNlXCIsZnVuY3Rpb24oZSl7ZT1lfHx7fTtmb3IodmFyIGkscyxyLG4sYSxvLGg9ZS50YXBlcnx8XCJub25lXCIsbD1bXSxfPTAsdT0wfChlLnBvaW50c3x8MjApLGY9dSxwPWUucmFuZG9taXplIT09ITEsbT1lLmNsYW1wPT09ITAsZD1lLnRlbXBsYXRlIGluc3RhbmNlb2YgdD9lLnRlbXBsYXRlOm51bGwsZz1cIm51bWJlclwiPT10eXBlb2YgZS5zdHJlbmd0aD8uNCplLnN0cmVuZ3RoOi40Oy0tZj4tMTspaT1wP01hdGgucmFuZG9tKCk6MS91KmYscz1kP2QuZ2V0UmF0aW8oaSk6aSxcIm5vbmVcIj09PWg/cj1nOlwib3V0XCI9PT1oPyhuPTEtaSxyPW4qbipnKTpcImluXCI9PT1oP3I9aSppKmc6LjU+aT8obj0yKmkscj0uNSpuKm4qZyk6KG49MiooMS1pKSxyPS41Km4qbipnKSxwP3MrPU1hdGgucmFuZG9tKCkqci0uNSpyOmYlMj9zKz0uNSpyOnMtPS41KnIsbSYmKHM+MT9zPTE6MD5zJiYocz0wKSksbFtfKytdPXt4OmkseTpzfTtmb3IobC5zb3J0KGZ1bmN0aW9uKHQsZSl7cmV0dXJuIHQueC1lLnh9KSxvPW5ldyBjKDEsMSxudWxsKSxmPXU7LS1mPi0xOylhPWxbZl0sbz1uZXcgYyhhLngsYS55LG8pO3RoaXMuX3ByZXY9bmV3IGMoMCwwLDAhPT1vLnQ/bzpvLm5leHQpfSwhMCksZD1pLnByb3RvdHlwZT1uZXcgdCxkLmNvbnN0cnVjdG9yPWksZC5nZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZT10aGlzLl9wcmV2O2lmKHQ+ZS50KXtmb3IoO2UubmV4dCYmdD49ZS50OyllPWUubmV4dDtlPWUucHJldn1lbHNlIGZvcig7ZS5wcmV2JiZlLnQ+PXQ7KWU9ZS5wcmV2O3JldHVybiB0aGlzLl9wcmV2PWUsZS52Kyh0LWUudCkvZS5nYXAqZS5jfSxkLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IGkodCl9LGkuZWFzZT1uZXcgaSx1KFwiQm91bmNlXCIsbChcIkJvdW5jZU91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLzIuNzU+dD83LjU2MjUqdCp0OjIvMi43NT50PzcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1OjIuNS8yLjc1PnQ/Ny41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzU6Ny41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzV9KSxsKFwiQm91bmNlSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gMS8yLjc1Pih0PTEtdCk/MS03LjU2MjUqdCp0OjIvMi43NT50PzEtKDcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1KToyLjUvMi43NT50PzEtKDcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1KToxLSg3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NSl9KSxsKFwiQm91bmNlSW5PdXRcIixmdW5jdGlvbih0KXt2YXIgZT0uNT50O3JldHVybiB0PWU/MS0yKnQ6Mip0LTEsdD0xLzIuNzU+dD83LjU2MjUqdCp0OjIvMi43NT50PzcuNTYyNSoodC09MS41LzIuNzUpKnQrLjc1OjIuNS8yLjc1PnQ/Ny41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzU6Ny41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzUsZT8uNSooMS10KTouNSp0Ky41fSkpLHUoXCJDaXJjXCIsbChcIkNpcmNPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5zcXJ0KDEtKHQtPTEpKnQpfSksbChcIkNpcmNJblwiLGZ1bmN0aW9uKHQpe3JldHVybi0oTWF0aC5zcXJ0KDEtdCp0KS0xKX0pLGwoXCJDaXJjSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LS41KihNYXRoLnNxcnQoMS10KnQpLTEpOi41KihNYXRoLnNxcnQoMS0odC09MikqdCkrMSl9KSkscz1mdW5jdGlvbihlLGkscyl7dmFyIHI9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKHQsZSl7dGhpcy5fcDE9dHx8MSx0aGlzLl9wMj1lfHxzLHRoaXMuX3AzPXRoaXMuX3AyL2EqKE1hdGguYXNpbigxL3RoaXMuX3AxKXx8MCl9LCEwKSxuPXIucHJvdG90eXBlPW5ldyB0O3JldHVybiBuLmNvbnN0cnVjdG9yPXIsbi5nZXRSYXRpbz1pLG4uY29uZmlnPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG5ldyByKHQsZSl9LHJ9LHUoXCJFbGFzdGljXCIscyhcIkVsYXN0aWNPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gdGhpcy5fcDEqTWF0aC5wb3coMiwtMTAqdCkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKzF9LC4zKSxzKFwiRWxhc3RpY0luXCIsZnVuY3Rpb24odCl7cmV0dXJuLSh0aGlzLl9wMSpNYXRoLnBvdygyLDEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKX0sLjMpLHMoXCJFbGFzdGljSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LS41KnRoaXMuX3AxKk1hdGgucG93KDIsMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMik6LjUqdGhpcy5fcDEqTWF0aC5wb3coMiwtMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikrMX0sLjQ1KSksdShcIkV4cG9cIixsKFwiRXhwb091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLU1hdGgucG93KDIsLTEwKnQpfSksbChcIkV4cG9JblwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnBvdygyLDEwKih0LTEpKS0uMDAxfSksbChcIkV4cG9Jbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8uNSpNYXRoLnBvdygyLDEwKih0LTEpKTouNSooMi1NYXRoLnBvdygyLC0xMCoodC0xKSkpfSkpLHUoXCJTaW5lXCIsbChcIlNpbmVPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5zaW4odCpvKX0pLGwoXCJTaW5lSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tTWF0aC5jb3ModCpvKSsxfSksbChcIlNpbmVJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybi0uNSooTWF0aC5jb3MoTWF0aC5QSSp0KS0xKX0pKSxoKFwiZWFzaW5nLkVhc2VMb29rdXBcIix7ZmluZDpmdW5jdGlvbihlKXtyZXR1cm4gdC5tYXBbZV19fSwhMCksXyhyLlNsb3dNbyxcIlNsb3dNb1wiLFwiZWFzZSxcIiksXyhpLFwiUm91Z2hFYXNlXCIsXCJlYXNlLFwiKSxfKGUsXCJTdGVwcGVkRWFzZVwiLFwiZWFzZSxcIikscH0sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwicGx1Z2lucy5DU1NQbHVnaW5cIixbXCJwbHVnaW5zLlR3ZWVuUGx1Z2luXCIsXCJUd2VlbkxpdGVcIl0sZnVuY3Rpb24odCxlKXt2YXIgaSxyLHMsbixhPWZ1bmN0aW9uKCl7dC5jYWxsKHRoaXMsXCJjc3NcIiksdGhpcy5fb3ZlcndyaXRlUHJvcHMubGVuZ3RoPTAsdGhpcy5zZXRSYXRpbz1hLnByb3RvdHlwZS5zZXRSYXRpb30sbz17fSxsPWEucHJvdG90eXBlPW5ldyB0KFwiY3NzXCIpO2wuY29uc3RydWN0b3I9YSxhLnZlcnNpb249XCIxLjEyLjFcIixhLkFQST0yLGEuZGVmYXVsdFRyYW5zZm9ybVBlcnNwZWN0aXZlPTAsYS5kZWZhdWx0U2tld1R5cGU9XCJjb21wZW5zYXRlZFwiLGw9XCJweFwiLGEuc3VmZml4TWFwPXt0b3A6bCxyaWdodDpsLGJvdHRvbTpsLGxlZnQ6bCx3aWR0aDpsLGhlaWdodDpsLGZvbnRTaXplOmwscGFkZGluZzpsLG1hcmdpbjpsLHBlcnNwZWN0aXZlOmwsbGluZUhlaWdodDpcIlwifTt2YXIgaCx1LGYsXyxwLGMsZD0vKD86XFxkfFxcLVxcZHxcXC5cXGR8XFwtXFwuXFxkKSsvZyxtPS8oPzpcXGR8XFwtXFxkfFxcLlxcZHxcXC1cXC5cXGR8XFwrPVxcZHxcXC09XFxkfFxcKz0uXFxkfFxcLT1cXC5cXGQpKy9nLGc9Lyg/OlxcKz18XFwtPXxcXC18XFxiKVtcXGRcXC1cXC5dK1thLXpBLVowLTldKig/OiV8XFxiKS9naSx2PS9bXlxcZFxcLVxcLl0vZyx5PS8oPzpcXGR8XFwtfFxcK3w9fCN8XFwuKSovZyxUPS9vcGFjaXR5ICo9ICooW14pXSopL2ksdz0vb3BhY2l0eTooW147XSopL2kseD0vYWxwaGFcXChvcGFjaXR5ICo9Lis/XFwpL2ksYj0vXihyZ2J8aHNsKS8sUD0vKFtBLVpdKS9nLFM9Ly0oW2Etel0pL2dpLEM9LyheKD86dXJsXFwoXFxcInx1cmxcXCgpKXwoPzooXFxcIlxcKSkkfFxcKSQpL2dpLFI9ZnVuY3Rpb24odCxlKXtyZXR1cm4gZS50b1VwcGVyQ2FzZSgpfSxrPS8oPzpMZWZ0fFJpZ2h0fFdpZHRoKS9pLEE9LyhNMTF8TTEyfE0yMXxNMjIpPVtcXGRcXC1cXC5lXSsvZ2ksTz0vcHJvZ2lkXFw6RFhJbWFnZVRyYW5zZm9ybVxcLk1pY3Jvc29mdFxcLk1hdHJpeFxcKC4rP1xcKS9pLEQ9LywoPz1bXlxcKV0qKD86XFwofCQpKS9naSxNPU1hdGguUEkvMTgwLEw9MTgwL01hdGguUEksTj17fSxYPWRvY3VtZW50LHo9WC5jcmVhdGVFbGVtZW50KFwiZGl2XCIpLEk9WC5jcmVhdGVFbGVtZW50KFwiaW1nXCIpLEU9YS5faW50ZXJuYWxzPXtfc3BlY2lhbFByb3BzOm99LEY9bmF2aWdhdG9yLnVzZXJBZ2VudCxZPWZ1bmN0aW9uKCl7dmFyIHQsZT1GLmluZGV4T2YoXCJBbmRyb2lkXCIpLGk9WC5jcmVhdGVFbGVtZW50KFwiZGl2XCIpO3JldHVybiBmPS0xIT09Ri5pbmRleE9mKFwiU2FmYXJpXCIpJiYtMT09PUYuaW5kZXhPZihcIkNocm9tZVwiKSYmKC0xPT09ZXx8TnVtYmVyKEYuc3Vic3RyKGUrOCwxKSk+MykscD1mJiY2Pk51bWJlcihGLnN1YnN0cihGLmluZGV4T2YoXCJWZXJzaW9uL1wiKSs4LDEpKSxfPS0xIT09Ri5pbmRleE9mKFwiRmlyZWZveFwiKSwvTVNJRSAoWzAtOV17MSx9W1xcLjAtOV17MCx9KS8uZXhlYyhGKSYmKGM9cGFyc2VGbG9hdChSZWdFeHAuJDEpKSxpLmlubmVySFRNTD1cIjxhIHN0eWxlPSd0b3A6MXB4O29wYWNpdHk6LjU1Oyc+YTwvYT5cIix0PWkuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJhXCIpWzBdLHQ/L14wLjU1Ly50ZXN0KHQuc3R5bGUub3BhY2l0eSk6ITF9KCksQj1mdW5jdGlvbih0KXtyZXR1cm4gVC50ZXN0KFwic3RyaW5nXCI9PXR5cGVvZiB0P3Q6KHQuY3VycmVudFN0eWxlP3QuY3VycmVudFN0eWxlLmZpbHRlcjp0LnN0eWxlLmZpbHRlcil8fFwiXCIpP3BhcnNlRmxvYXQoUmVnRXhwLiQxKS8xMDA6MX0sVT1mdW5jdGlvbih0KXt3aW5kb3cuY29uc29sZSYmY29uc29sZS5sb2codCl9LFc9XCJcIixqPVwiXCIsVj1mdW5jdGlvbih0LGUpe2U9ZXx8ejt2YXIgaSxyLHM9ZS5zdHlsZTtpZih2b2lkIDAhPT1zW3RdKXJldHVybiB0O2Zvcih0PXQuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkrdC5zdWJzdHIoMSksaT1bXCJPXCIsXCJNb3pcIixcIm1zXCIsXCJNc1wiLFwiV2Via2l0XCJdLHI9NTstLXI+LTEmJnZvaWQgMD09PXNbaVtyXSt0XTspO3JldHVybiByPj0wPyhqPTM9PT1yP1wibXNcIjppW3JdLFc9XCItXCIrai50b0xvd2VyQ2FzZSgpK1wiLVwiLGordCk6bnVsbH0sSD1YLmRlZmF1bHRWaWV3P1guZGVmYXVsdFZpZXcuZ2V0Q29tcHV0ZWRTdHlsZTpmdW5jdGlvbigpe30scT1hLmdldFN0eWxlPWZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG47cmV0dXJuIFl8fFwib3BhY2l0eVwiIT09ZT8oIXImJnQuc3R5bGVbZV0/bj10LnN0eWxlW2VdOihpPWl8fEgodCkpP249aVtlXXx8aS5nZXRQcm9wZXJ0eVZhbHVlKGUpfHxpLmdldFByb3BlcnR5VmFsdWUoZS5yZXBsYWNlKFAsXCItJDFcIikudG9Mb3dlckNhc2UoKSk6dC5jdXJyZW50U3R5bGUmJihuPXQuY3VycmVudFN0eWxlW2VdKSxudWxsPT1zfHxuJiZcIm5vbmVcIiE9PW4mJlwiYXV0b1wiIT09biYmXCJhdXRvIGF1dG9cIiE9PW4/bjpzKTpCKHQpfSxRPUUuY29udmVydFRvUGl4ZWxzPWZ1bmN0aW9uKHQsaSxyLHMsbil7aWYoXCJweFwiPT09c3x8IXMpcmV0dXJuIHI7aWYoXCJhdXRvXCI9PT1zfHwhcilyZXR1cm4gMDt2YXIgbyxsLGgsdT1rLnRlc3QoaSksZj10LF89ei5zdHlsZSxwPTA+cjtpZihwJiYocj0tciksXCIlXCI9PT1zJiYtMSE9PWkuaW5kZXhPZihcImJvcmRlclwiKSlvPXIvMTAwKih1P3QuY2xpZW50V2lkdGg6dC5jbGllbnRIZWlnaHQpO2Vsc2V7aWYoXy5jc3NUZXh0PVwiYm9yZGVyOjAgc29saWQgcmVkO3Bvc2l0aW9uOlwiK3EodCxcInBvc2l0aW9uXCIpK1wiO2xpbmUtaGVpZ2h0OjA7XCIsXCIlXCIhPT1zJiZmLmFwcGVuZENoaWxkKV9bdT9cImJvcmRlckxlZnRXaWR0aFwiOlwiYm9yZGVyVG9wV2lkdGhcIl09citzO2Vsc2V7aWYoZj10LnBhcmVudE5vZGV8fFguYm9keSxsPWYuX2dzQ2FjaGUsaD1lLnRpY2tlci5mcmFtZSxsJiZ1JiZsLnRpbWU9PT1oKXJldHVybiBsLndpZHRoKnIvMTAwO19bdT9cIndpZHRoXCI6XCJoZWlnaHRcIl09citzfWYuYXBwZW5kQ2hpbGQoeiksbz1wYXJzZUZsb2F0KHpbdT9cIm9mZnNldFdpZHRoXCI6XCJvZmZzZXRIZWlnaHRcIl0pLGYucmVtb3ZlQ2hpbGQoeiksdSYmXCIlXCI9PT1zJiZhLmNhY2hlV2lkdGhzIT09ITEmJihsPWYuX2dzQ2FjaGU9Zi5fZ3NDYWNoZXx8e30sbC50aW1lPWgsbC53aWR0aD0xMDAqKG8vcikpLDAhPT1vfHxufHwobz1RKHQsaSxyLHMsITApKX1yZXR1cm4gcD8tbzpvfSxaPUUuY2FsY3VsYXRlT2Zmc2V0PWZ1bmN0aW9uKHQsZSxpKXtpZihcImFic29sdXRlXCIhPT1xKHQsXCJwb3NpdGlvblwiLGkpKXJldHVybiAwO3ZhciByPVwibGVmdFwiPT09ZT9cIkxlZnRcIjpcIlRvcFwiLHM9cSh0LFwibWFyZ2luXCIrcixpKTtyZXR1cm4gdFtcIm9mZnNldFwiK3JdLShRKHQsZSxwYXJzZUZsb2F0KHMpLHMucmVwbGFjZSh5LFwiXCIpKXx8MCl9LCQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxyLHM9e307aWYoZT1lfHxIKHQsbnVsbCkpaWYoaT1lLmxlbmd0aClmb3IoOy0taT4tMTspc1tlW2ldLnJlcGxhY2UoUyxSKV09ZS5nZXRQcm9wZXJ0eVZhbHVlKGVbaV0pO2Vsc2UgZm9yKGkgaW4gZSlzW2ldPWVbaV07ZWxzZSBpZihlPXQuY3VycmVudFN0eWxlfHx0LnN0eWxlKWZvcihpIGluIGUpXCJzdHJpbmdcIj09dHlwZW9mIGkmJnZvaWQgMD09PXNbaV0mJihzW2kucmVwbGFjZShTLFIpXT1lW2ldKTtyZXR1cm4gWXx8KHMub3BhY2l0eT1CKHQpKSxyPVBlKHQsZSwhMSkscy5yb3RhdGlvbj1yLnJvdGF0aW9uLHMuc2tld1g9ci5za2V3WCxzLnNjYWxlWD1yLnNjYWxlWCxzLnNjYWxlWT1yLnNjYWxlWSxzLng9ci54LHMueT1yLnkseGUmJihzLno9ci56LHMucm90YXRpb25YPXIucm90YXRpb25YLHMucm90YXRpb25ZPXIucm90YXRpb25ZLHMuc2NhbGVaPXIuc2NhbGVaKSxzLmZpbHRlcnMmJmRlbGV0ZSBzLmZpbHRlcnMsc30sRz1mdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuLGEsbyxsPXt9LGg9dC5zdHlsZTtmb3IoYSBpbiBpKVwiY3NzVGV4dFwiIT09YSYmXCJsZW5ndGhcIiE9PWEmJmlzTmFOKGEpJiYoZVthXSE9PShuPWlbYV0pfHxzJiZzW2FdKSYmLTE9PT1hLmluZGV4T2YoXCJPcmlnaW5cIikmJihcIm51bWJlclwiPT10eXBlb2Ygbnx8XCJzdHJpbmdcIj09dHlwZW9mIG4pJiYobFthXT1cImF1dG9cIiE9PW58fFwibGVmdFwiIT09YSYmXCJ0b3BcIiE9PWE/XCJcIiE9PW4mJlwiYXV0b1wiIT09biYmXCJub25lXCIhPT1ufHxcInN0cmluZ1wiIT10eXBlb2YgZVthXXx8XCJcIj09PWVbYV0ucmVwbGFjZSh2LFwiXCIpP246MDpaKHQsYSksdm9pZCAwIT09aFthXSYmKG89bmV3IGZlKGgsYSxoW2FdLG8pKSk7aWYocilmb3IoYSBpbiByKVwiY2xhc3NOYW1lXCIhPT1hJiYobFthXT1yW2FdKTtyZXR1cm57ZGlmczpsLGZpcnN0TVBUOm99fSxLPXt3aWR0aDpbXCJMZWZ0XCIsXCJSaWdodFwiXSxoZWlnaHQ6W1wiVG9wXCIsXCJCb3R0b21cIl19LEo9W1wibWFyZ2luTGVmdFwiLFwibWFyZ2luUmlnaHRcIixcIm1hcmdpblRvcFwiLFwibWFyZ2luQm90dG9tXCJdLHRlPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcj1wYXJzZUZsb2F0KFwid2lkdGhcIj09PWU/dC5vZmZzZXRXaWR0aDp0Lm9mZnNldEhlaWdodCkscz1LW2VdLG49cy5sZW5ndGg7Zm9yKGk9aXx8SCh0LG51bGwpOy0tbj4tMTspci09cGFyc2VGbG9hdChxKHQsXCJwYWRkaW5nXCIrc1tuXSxpLCEwKSl8fDAsci09cGFyc2VGbG9hdChxKHQsXCJib3JkZXJcIitzW25dK1wiV2lkdGhcIixpLCEwKSl8fDA7cmV0dXJuIHJ9LGVlPWZ1bmN0aW9uKHQsZSl7KG51bGw9PXR8fFwiXCI9PT10fHxcImF1dG9cIj09PXR8fFwiYXV0byBhdXRvXCI9PT10KSYmKHQ9XCIwIDBcIik7dmFyIGk9dC5zcGxpdChcIiBcIikscj0tMSE9PXQuaW5kZXhPZihcImxlZnRcIik/XCIwJVwiOi0xIT09dC5pbmRleE9mKFwicmlnaHRcIik/XCIxMDAlXCI6aVswXSxzPS0xIT09dC5pbmRleE9mKFwidG9wXCIpP1wiMCVcIjotMSE9PXQuaW5kZXhPZihcImJvdHRvbVwiKT9cIjEwMCVcIjppWzFdO3JldHVybiBudWxsPT1zP3M9XCIwXCI6XCJjZW50ZXJcIj09PXMmJihzPVwiNTAlXCIpLChcImNlbnRlclwiPT09cnx8aXNOYU4ocGFyc2VGbG9hdChyKSkmJi0xPT09KHIrXCJcIikuaW5kZXhPZihcIj1cIikpJiYocj1cIjUwJVwiKSxlJiYoZS5veHA9LTEhPT1yLmluZGV4T2YoXCIlXCIpLGUub3lwPS0xIT09cy5pbmRleE9mKFwiJVwiKSxlLm94cj1cIj1cIj09PXIuY2hhckF0KDEpLGUub3lyPVwiPVwiPT09cy5jaGFyQXQoMSksZS5veD1wYXJzZUZsb2F0KHIucmVwbGFjZSh2LFwiXCIpKSxlLm95PXBhcnNlRmxvYXQocy5yZXBsYWNlKHYsXCJcIikpKSxyK1wiIFwiK3MrKGkubGVuZ3RoPjI/XCIgXCIraVsyXTpcIlwiKX0saWU9ZnVuY3Rpb24odCxlKXtyZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgdCYmXCI9XCI9PT10LmNoYXJBdCgxKT9wYXJzZUludCh0LmNoYXJBdCgwKStcIjFcIiwxMCkqcGFyc2VGbG9hdCh0LnN1YnN0cigyKSk6cGFyc2VGbG9hdCh0KS1wYXJzZUZsb2F0KGUpfSxyZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsPT10P2U6XCJzdHJpbmdcIj09dHlwZW9mIHQmJlwiPVwiPT09dC5jaGFyQXQoMSk/cGFyc2VJbnQodC5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcih0LnN1YnN0cigyKSkrZTpwYXJzZUZsb2F0KHQpfSxzZT1mdW5jdGlvbih0LGUsaSxyKXt2YXIgcyxuLGEsbyxsPTFlLTY7cmV0dXJuIG51bGw9PXQ/bz1lOlwibnVtYmVyXCI9PXR5cGVvZiB0P289dDoocz0zNjAsbj10LnNwbGl0KFwiX1wiKSxhPU51bWJlcihuWzBdLnJlcGxhY2UodixcIlwiKSkqKC0xPT09dC5pbmRleE9mKFwicmFkXCIpPzE6TCktKFwiPVwiPT09dC5jaGFyQXQoMSk/MDplKSxuLmxlbmd0aCYmKHImJihyW2ldPWUrYSksLTEhPT10LmluZGV4T2YoXCJzaG9ydFwiKSYmKGElPXMsYSE9PWElKHMvMikmJihhPTA+YT9hK3M6YS1zKSksLTEhPT10LmluZGV4T2YoXCJfY3dcIikmJjA+YT9hPShhKzk5OTk5OTk5OTkqcyklcy0oMHxhL3MpKnM6LTEhPT10LmluZGV4T2YoXCJjY3dcIikmJmE+MCYmKGE9KGEtOTk5OTk5OTk5OSpzKSVzLSgwfGEvcykqcykpLG89ZSthKSxsPm8mJm8+LWwmJihvPTApLG99LG5lPXthcXVhOlswLDI1NSwyNTVdLGxpbWU6WzAsMjU1LDBdLHNpbHZlcjpbMTkyLDE5MiwxOTJdLGJsYWNrOlswLDAsMF0sbWFyb29uOlsxMjgsMCwwXSx0ZWFsOlswLDEyOCwxMjhdLGJsdWU6WzAsMCwyNTVdLG5hdnk6WzAsMCwxMjhdLHdoaXRlOlsyNTUsMjU1LDI1NV0sZnVjaHNpYTpbMjU1LDAsMjU1XSxvbGl2ZTpbMTI4LDEyOCwwXSx5ZWxsb3c6WzI1NSwyNTUsMF0sb3JhbmdlOlsyNTUsMTY1LDBdLGdyYXk6WzEyOCwxMjgsMTI4XSxwdXJwbGU6WzEyOCwwLDEyOF0sZ3JlZW46WzAsMTI4LDBdLHJlZDpbMjU1LDAsMF0scGluazpbMjU1LDE5MiwyMDNdLGN5YW46WzAsMjU1LDI1NV0sdHJhbnNwYXJlbnQ6WzI1NSwyNTUsMjU1LDBdfSxhZT1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIHQ9MD50P3QrMTp0PjE/dC0xOnQsMHwyNTUqKDE+Nip0P2UrNiooaS1lKSp0Oi41PnQ/aToyPjMqdD9lKzYqKGktZSkqKDIvMy10KTplKSsuNX0sb2U9ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhO3JldHVybiB0JiZcIlwiIT09dD9cIm51bWJlclwiPT10eXBlb2YgdD9bdD4+MTYsMjU1JnQ+PjgsMjU1JnRdOihcIixcIj09PXQuY2hhckF0KHQubGVuZ3RoLTEpJiYodD10LnN1YnN0cigwLHQubGVuZ3RoLTEpKSxuZVt0XT9uZVt0XTpcIiNcIj09PXQuY2hhckF0KDApPyg0PT09dC5sZW5ndGgmJihlPXQuY2hhckF0KDEpLGk9dC5jaGFyQXQoMikscj10LmNoYXJBdCgzKSx0PVwiI1wiK2UrZStpK2krcityKSx0PXBhcnNlSW50KHQuc3Vic3RyKDEpLDE2KSxbdD4+MTYsMjU1JnQ+PjgsMjU1JnRdKTpcImhzbFwiPT09dC5zdWJzdHIoMCwzKT8odD10Lm1hdGNoKGQpLHM9TnVtYmVyKHRbMF0pJTM2MC8zNjAsbj1OdW1iZXIodFsxXSkvMTAwLGE9TnVtYmVyKHRbMl0pLzEwMCxpPS41Pj1hP2EqKG4rMSk6YStuLWEqbixlPTIqYS1pLHQubGVuZ3RoPjMmJih0WzNdPU51bWJlcih0WzNdKSksdFswXT1hZShzKzEvMyxlLGkpLHRbMV09YWUocyxlLGkpLHRbMl09YWUocy0xLzMsZSxpKSx0KToodD10Lm1hdGNoKGQpfHxuZS50cmFuc3BhcmVudCx0WzBdPU51bWJlcih0WzBdKSx0WzFdPU51bWJlcih0WzFdKSx0WzJdPU51bWJlcih0WzJdKSx0Lmxlbmd0aD4zJiYodFszXT1OdW1iZXIodFszXSkpLHQpKTpuZS5ibGFja30sbGU9XCIoPzpcXFxcYig/Oig/OnJnYnxyZ2JhfGhzbHxoc2xhKVxcXFwoLis/XFxcXCkpfFxcXFxCIy4rP1xcXFxiXCI7Zm9yKGwgaW4gbmUpbGUrPVwifFwiK2wrXCJcXFxcYlwiO2xlPVJlZ0V4cChsZStcIilcIixcImdpXCIpO3ZhciBoZT1mdW5jdGlvbih0LGUsaSxyKXtpZihudWxsPT10KXJldHVybiBmdW5jdGlvbih0KXtyZXR1cm4gdH07dmFyIHMsbj1lPyh0Lm1hdGNoKGxlKXx8W1wiXCJdKVswXTpcIlwiLGE9dC5zcGxpdChuKS5qb2luKFwiXCIpLm1hdGNoKGcpfHxbXSxvPXQuc3Vic3RyKDAsdC5pbmRleE9mKGFbMF0pKSxsPVwiKVwiPT09dC5jaGFyQXQodC5sZW5ndGgtMSk/XCIpXCI6XCJcIixoPS0xIT09dC5pbmRleE9mKFwiIFwiKT9cIiBcIjpcIixcIix1PWEubGVuZ3RoLGY9dT4wP2FbMF0ucmVwbGFjZShkLFwiXCIpOlwiXCI7cmV0dXJuIHU/cz1lP2Z1bmN0aW9uKHQpe3ZhciBlLF8scCxjO2lmKFwibnVtYmVyXCI9PXR5cGVvZiB0KXQrPWY7ZWxzZSBpZihyJiZELnRlc3QodCkpe2ZvcihjPXQucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIikscD0wO2MubGVuZ3RoPnA7cCsrKWNbcF09cyhjW3BdKTtyZXR1cm4gYy5qb2luKFwiLFwiKX1pZihlPSh0Lm1hdGNoKGxlKXx8W25dKVswXSxfPXQuc3BsaXQoZSkuam9pbihcIlwiKS5tYXRjaChnKXx8W10scD1fLmxlbmd0aCx1PnAtLSlmb3IoO3U+KytwOylfW3BdPWk/X1swfChwLTEpLzJdOmFbcF07cmV0dXJuIG8rXy5qb2luKGgpK2grZStsKygtMSE9PXQuaW5kZXhPZihcImluc2V0XCIpP1wiIGluc2V0XCI6XCJcIil9OmZ1bmN0aW9uKHQpe3ZhciBlLG4sXztpZihcIm51bWJlclwiPT10eXBlb2YgdCl0Kz1mO2Vsc2UgaWYociYmRC50ZXN0KHQpKXtmb3Iobj10LnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLF89MDtuLmxlbmd0aD5fO18rKyluW19dPXMobltfXSk7cmV0dXJuIG4uam9pbihcIixcIil9aWYoZT10Lm1hdGNoKGcpfHxbXSxfPWUubGVuZ3RoLHU+Xy0tKWZvcig7dT4rK187KWVbX109aT9lWzB8KF8tMSkvMl06YVtfXTtyZXR1cm4gbytlLmpvaW4oaCkrbH06ZnVuY3Rpb24odCl7cmV0dXJuIHR9fSx1ZT1mdW5jdGlvbih0KXtyZXR1cm4gdD10LnNwbGl0KFwiLFwiKSxmdW5jdGlvbihlLGkscixzLG4sYSxvKXt2YXIgbCxoPShpK1wiXCIpLnNwbGl0KFwiIFwiKTtmb3Iobz17fSxsPTA7ND5sO2wrKylvW3RbbF1dPWhbbF09aFtsXXx8aFsobC0xKS8yPj4wXTtyZXR1cm4gcy5wYXJzZShlLG8sbixhKX19LGZlPShFLl9zZXRQbHVnaW5SYXRpbz1mdW5jdGlvbih0KXt0aGlzLnBsdWdpbi5zZXRSYXRpbyh0KTtmb3IodmFyIGUsaSxyLHMsbj10aGlzLmRhdGEsYT1uLnByb3h5LG89bi5maXJzdE1QVCxsPTFlLTY7bzspZT1hW28udl0sby5yP2U9TWF0aC5yb3VuZChlKTpsPmUmJmU+LWwmJihlPTApLG8udFtvLnBdPWUsbz1vLl9uZXh0O2lmKG4uYXV0b1JvdGF0ZSYmKG4uYXV0b1JvdGF0ZS5yb3RhdGlvbj1hLnJvdGF0aW9uKSwxPT09dClmb3Iobz1uLmZpcnN0TVBUO287KXtpZihpPW8udCxpLnR5cGUpe2lmKDE9PT1pLnR5cGUpe2ZvcihzPWkueHMwK2kucytpLnhzMSxyPTE7aS5sPnI7cisrKXMrPWlbXCJ4blwiK3JdK2lbXCJ4c1wiKyhyKzEpXTtpLmU9c319ZWxzZSBpLmU9aS5zK2kueHMwO289by5fbmV4dH19LGZ1bmN0aW9uKHQsZSxpLHIscyl7dGhpcy50PXQsdGhpcy5wPWUsdGhpcy52PWksdGhpcy5yPXMsciYmKHIuX3ByZXY9dGhpcyx0aGlzLl9uZXh0PXIpfSksX2U9KEUuX3BhcnNlVG9Qcm94eT1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGEsbyxsLGgsdSxmPXIsXz17fSxwPXt9LGM9aS5fdHJhbnNmb3JtLGQ9Tjtmb3IoaS5fdHJhbnNmb3JtPW51bGwsTj1lLHI9dT1pLnBhcnNlKHQsZSxyLHMpLE49ZCxuJiYoaS5fdHJhbnNmb3JtPWMsZiYmKGYuX3ByZXY9bnVsbCxmLl9wcmV2JiYoZi5fcHJldi5fbmV4dD1udWxsKSkpO3ImJnIhPT1mOyl7aWYoMT49ci50eXBlJiYobz1yLnAscFtvXT1yLnMrci5jLF9bb109ci5zLG58fChoPW5ldyBmZShyLFwic1wiLG8saCxyLnIpLHIuYz0wKSwxPT09ci50eXBlKSlmb3IoYT1yLmw7LS1hPjA7KWw9XCJ4blwiK2Esbz1yLnArXCJfXCIrbCxwW29dPXIuZGF0YVtsXSxfW29dPXJbbF0sbnx8KGg9bmV3IGZlKHIsbCxvLGgsci5yeHBbbF0pKTtyPXIuX25leHR9cmV0dXJue3Byb3h5Ol8sZW5kOnAsZmlyc3RNUFQ6aCxwdDp1fX0sRS5DU1NQcm9wVHdlZW49ZnVuY3Rpb24odCxlLHIscyxhLG8sbCxoLHUsZixfKXt0aGlzLnQ9dCx0aGlzLnA9ZSx0aGlzLnM9cix0aGlzLmM9cyx0aGlzLm49bHx8ZSx0IGluc3RhbmNlb2YgX2V8fG4ucHVzaCh0aGlzLm4pLHRoaXMucj1oLHRoaXMudHlwZT1vfHwwLHUmJih0aGlzLnByPXUsaT0hMCksdGhpcy5iPXZvaWQgMD09PWY/cjpmLHRoaXMuZT12b2lkIDA9PT1fP3IrczpfLGEmJih0aGlzLl9uZXh0PWEsYS5fcHJldj10aGlzKX0pLHBlPWEucGFyc2VDb21wbGV4PWZ1bmN0aW9uKHQsZSxpLHIscyxuLGEsbyxsLHUpe2k9aXx8bnx8XCJcIixhPW5ldyBfZSh0LGUsMCwwLGEsdT8yOjEsbnVsbCwhMSxvLGkscikscis9XCJcIjt2YXIgZixfLHAsYyxnLHYseSxULHcseCxQLFMsQz1pLnNwbGl0KFwiLCBcIikuam9pbihcIixcIikuc3BsaXQoXCIgXCIpLFI9ci5zcGxpdChcIiwgXCIpLmpvaW4oXCIsXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoLEE9aCE9PSExO2ZvcigoLTEhPT1yLmluZGV4T2YoXCIsXCIpfHwtMSE9PWkuaW5kZXhPZihcIixcIikpJiYoQz1DLmpvaW4oXCIgXCIpLnJlcGxhY2UoRCxcIiwgXCIpLnNwbGl0KFwiIFwiKSxSPVIuam9pbihcIiBcIikucmVwbGFjZShELFwiLCBcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgpLGshPT1SLmxlbmd0aCYmKEM9KG58fFwiXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoKSxhLnBsdWdpbj1sLGEuc2V0UmF0aW89dSxmPTA7az5mO2YrKylpZihjPUNbZl0sZz1SW2ZdLFQ9cGFyc2VGbG9hdChjKSxUfHwwPT09VClhLmFwcGVuZFh0cmEoXCJcIixULGllKGcsVCksZy5yZXBsYWNlKG0sXCJcIiksQSYmLTEhPT1nLmluZGV4T2YoXCJweFwiKSwhMCk7ZWxzZSBpZihzJiYoXCIjXCI9PT1jLmNoYXJBdCgwKXx8bmVbY118fGIudGVzdChjKSkpUz1cIixcIj09PWcuY2hhckF0KGcubGVuZ3RoLTEpP1wiKSxcIjpcIilcIixjPW9lKGMpLGc9b2UoZyksdz1jLmxlbmd0aCtnLmxlbmd0aD42LHcmJiFZJiYwPT09Z1szXT8oYVtcInhzXCIrYS5sXSs9YS5sP1wiIHRyYW5zcGFyZW50XCI6XCJ0cmFuc3BhcmVudFwiLGEuZT1hLmUuc3BsaXQoUltmXSkuam9pbihcInRyYW5zcGFyZW50XCIpKTooWXx8KHc9ITEpLGEuYXBwZW5kWHRyYSh3P1wicmdiYShcIjpcInJnYihcIixjWzBdLGdbMF0tY1swXSxcIixcIiwhMCwhMCkuYXBwZW5kWHRyYShcIlwiLGNbMV0sZ1sxXS1jWzFdLFwiLFwiLCEwKS5hcHBlbmRYdHJhKFwiXCIsY1syXSxnWzJdLWNbMl0sdz9cIixcIjpTLCEwKSx3JiYoYz00PmMubGVuZ3RoPzE6Y1szXSxhLmFwcGVuZFh0cmEoXCJcIixjLCg0PmcubGVuZ3RoPzE6Z1szXSktYyxTLCExKSkpO2Vsc2UgaWYodj1jLm1hdGNoKGQpKXtpZih5PWcubWF0Y2gobSksIXl8fHkubGVuZ3RoIT09di5sZW5ndGgpcmV0dXJuIGE7Zm9yKHA9MCxfPTA7di5sZW5ndGg+XztfKyspUD12W19dLHg9Yy5pbmRleE9mKFAscCksYS5hcHBlbmRYdHJhKGMuc3Vic3RyKHAseC1wKSxOdW1iZXIoUCksaWUoeVtfXSxQKSxcIlwiLEEmJlwicHhcIj09PWMuc3Vic3RyKHgrUC5sZW5ndGgsMiksMD09PV8pLHA9eCtQLmxlbmd0aDthW1wieHNcIithLmxdKz1jLnN1YnN0cihwKX1lbHNlIGFbXCJ4c1wiK2EubF0rPWEubD9cIiBcIitjOmM7aWYoLTEhPT1yLmluZGV4T2YoXCI9XCIpJiZhLmRhdGEpe2ZvcihTPWEueHMwK2EuZGF0YS5zLGY9MTthLmw+ZjtmKyspUys9YVtcInhzXCIrZl0rYS5kYXRhW1wieG5cIitmXTthLmU9UythW1wieHNcIitmXX1yZXR1cm4gYS5sfHwoYS50eXBlPS0xLGEueHMwPWEuZSksYS54Zmlyc3R8fGF9LGNlPTk7Zm9yKGw9X2UucHJvdG90eXBlLGwubD1sLnByPTA7LS1jZT4wOylsW1wieG5cIitjZV09MCxsW1wieHNcIitjZV09XCJcIjtsLnhzMD1cIlwiLGwuX25leHQ9bC5fcHJldj1sLnhmaXJzdD1sLmRhdGE9bC5wbHVnaW49bC5zZXRSYXRpbz1sLnJ4cD1udWxsLGwuYXBwZW5kWHRyYT1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGE9dGhpcyxvPWEubDtyZXR1cm4gYVtcInhzXCIrb10rPW4mJm8/XCIgXCIrdDp0fHxcIlwiLGl8fDA9PT1vfHxhLnBsdWdpbj8oYS5sKyssYS50eXBlPWEuc2V0UmF0aW8/MjoxLGFbXCJ4c1wiK2EubF09cnx8XCJcIixvPjA/KGEuZGF0YVtcInhuXCIrb109ZStpLGEucnhwW1wieG5cIitvXT1zLGFbXCJ4blwiK29dPWUsYS5wbHVnaW58fChhLnhmaXJzdD1uZXcgX2UoYSxcInhuXCIrbyxlLGksYS54Zmlyc3R8fGEsMCxhLm4scyxhLnByKSxhLnhmaXJzdC54czA9MCksYSk6KGEuZGF0YT17czplK2l9LGEucnhwPXt9LGEucz1lLGEuYz1pLGEucj1zLGEpKTooYVtcInhzXCIrb10rPWUrKHJ8fFwiXCIpLGEpfTt2YXIgZGU9ZnVuY3Rpb24odCxlKXtlPWV8fHt9LHRoaXMucD1lLnByZWZpeD9WKHQpfHx0OnQsb1t0XT1vW3RoaXMucF09dGhpcyx0aGlzLmZvcm1hdD1lLmZvcm1hdHRlcnx8aGUoZS5kZWZhdWx0VmFsdWUsZS5jb2xvcixlLmNvbGxhcHNpYmxlLGUubXVsdGkpLGUucGFyc2VyJiYodGhpcy5wYXJzZT1lLnBhcnNlciksdGhpcy5jbHJzPWUuY29sb3IsdGhpcy5tdWx0aT1lLm11bHRpLHRoaXMua2V5d29yZD1lLmtleXdvcmQsdGhpcy5kZmx0PWUuZGVmYXVsdFZhbHVlLHRoaXMucHI9ZS5wcmlvcml0eXx8MH0sbWU9RS5fcmVnaXN0ZXJDb21wbGV4U3BlY2lhbFByb3A9ZnVuY3Rpb24odCxlLGkpe1wib2JqZWN0XCIhPXR5cGVvZiBlJiYoZT17cGFyc2VyOml9KTt2YXIgcixzLG49dC5zcGxpdChcIixcIiksYT1lLmRlZmF1bHRWYWx1ZTtmb3IoaT1pfHxbYV0scj0wO24ubGVuZ3RoPnI7cisrKWUucHJlZml4PTA9PT1yJiZlLnByZWZpeCxlLmRlZmF1bHRWYWx1ZT1pW3JdfHxhLHM9bmV3IGRlKG5bcl0sZSl9LGdlPWZ1bmN0aW9uKHQpe2lmKCFvW3RdKXt2YXIgZT10LmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK3Quc3Vic3RyKDEpK1wiUGx1Z2luXCI7bWUodCx7cGFyc2VyOmZ1bmN0aW9uKHQsaSxyLHMsbixhLGwpe3ZhciBoPSh3aW5kb3cuR3JlZW5Tb2NrR2xvYmFsc3x8d2luZG93KS5jb20uZ3JlZW5zb2NrLnBsdWdpbnNbZV07cmV0dXJuIGg/KGguX2Nzc1JlZ2lzdGVyKCksb1tyXS5wYXJzZSh0LGkscixzLG4sYSxsKSk6KFUoXCJFcnJvcjogXCIrZStcIiBqcyBmaWxlIG5vdCBsb2FkZWQuXCIpLG4pfX0pfX07bD1kZS5wcm90b3R5cGUsbC5wYXJzZUNvbXBsZXg9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhLG8sbCxoLHUsZixfPXRoaXMua2V5d29yZDtpZih0aGlzLm11bHRpJiYoRC50ZXN0KGkpfHxELnRlc3QoZSk/KG89ZS5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxsPWkucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIikpOl8mJihvPVtlXSxsPVtpXSkpLGwpe2ZvcihoPWwubGVuZ3RoPm8ubGVuZ3RoP2wubGVuZ3RoOm8ubGVuZ3RoLGE9MDtoPmE7YSsrKWU9b1thXT1vW2FdfHx0aGlzLmRmbHQsaT1sW2FdPWxbYV18fHRoaXMuZGZsdCxfJiYodT1lLmluZGV4T2YoXyksZj1pLmluZGV4T2YoXyksdSE9PWYmJihpPS0xPT09Zj9sOm8saVthXSs9XCIgXCIrXykpO2U9by5qb2luKFwiLCBcIiksaT1sLmpvaW4oXCIsIFwiKX1yZXR1cm4gcGUodCx0aGlzLnAsZSxpLHRoaXMuY2xycyx0aGlzLmRmbHQscix0aGlzLnByLHMsbil9LGwucGFyc2U9ZnVuY3Rpb24odCxlLGkscixuLGEpe3JldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLHRoaXMuZm9ybWF0KHEodCx0aGlzLnAscywhMSx0aGlzLmRmbHQpKSx0aGlzLmZvcm1hdChlKSxuLGEpfSxhLnJlZ2lzdGVyU3BlY2lhbFByb3A9ZnVuY3Rpb24odCxlLGkpe21lKHQse3BhcnNlcjpmdW5jdGlvbih0LHIscyxuLGEsbyl7dmFyIGw9bmV3IF9lKHQscywwLDAsYSwyLHMsITEsaSk7cmV0dXJuIGwucGx1Z2luPW8sbC5zZXRSYXRpbz1lKHQscixuLl90d2VlbixzKSxsfSxwcmlvcml0eTppfSl9O3ZhciB2ZT1cInNjYWxlWCxzY2FsZVksc2NhbGVaLHgseSx6LHNrZXdYLHNrZXdZLHJvdGF0aW9uLHJvdGF0aW9uWCxyb3RhdGlvblkscGVyc3BlY3RpdmVcIi5zcGxpdChcIixcIikseWU9VihcInRyYW5zZm9ybVwiKSxUZT1XK1widHJhbnNmb3JtXCIsd2U9VihcInRyYW5zZm9ybU9yaWdpblwiKSx4ZT1udWxsIT09VihcInBlcnNwZWN0aXZlXCIpLGJlPUUuVHJhbnNmb3JtPWZ1bmN0aW9uKCl7dGhpcy5za2V3WT0wfSxQZT1FLmdldFRyYW5zZm9ybT1mdW5jdGlvbih0LGUsaSxyKXtpZih0Ll9nc1RyYW5zZm9ybSYmaSYmIXIpcmV0dXJuIHQuX2dzVHJhbnNmb3JtO3ZhciBzLG4sbyxsLGgsdSxmLF8scCxjLGQsbSxnLHY9aT90Ll9nc1RyYW5zZm9ybXx8bmV3IGJlOm5ldyBiZSx5PTA+di5zY2FsZVgsVD0yZS01LHc9MWU1LHg9MTc5Ljk5LGI9eCpNLFA9eGU/cGFyc2VGbG9hdChxKHQsd2UsZSwhMSxcIjAgMCAwXCIpLnNwbGl0KFwiIFwiKVsyXSl8fHYuek9yaWdpbnx8MDowO2Zvcih5ZT9zPXEodCxUZSxlLCEwKTp0LmN1cnJlbnRTdHlsZSYmKHM9dC5jdXJyZW50U3R5bGUuZmlsdGVyLm1hdGNoKEEpLHM9cyYmND09PXMubGVuZ3RoP1tzWzBdLnN1YnN0cig0KSxOdW1iZXIoc1syXS5zdWJzdHIoNCkpLE51bWJlcihzWzFdLnN1YnN0cig0KSksc1szXS5zdWJzdHIoNCksdi54fHwwLHYueXx8MF0uam9pbihcIixcIik6XCJcIiksbj0oc3x8XCJcIikubWF0Y2goLyg/OlxcLXxcXGIpW1xcZFxcLVxcLmVdK1xcYi9naSl8fFtdLG89bi5sZW5ndGg7LS1vPi0xOylsPU51bWJlcihuW29dKSxuW29dPShoPWwtKGx8PTApKT8oMHxoKncrKDA+aD8tLjU6LjUpKS93K2w6bDtpZigxNj09PW4ubGVuZ3RoKXt2YXIgUz1uWzhdLEM9bls5XSxSPW5bMTBdLGs9blsxMl0sTz1uWzEzXSxEPW5bMTRdO2lmKHYuek9yaWdpbiYmKEQ9LXYuek9yaWdpbixrPVMqRC1uWzEyXSxPPUMqRC1uWzEzXSxEPVIqRCt2LnpPcmlnaW4tblsxNF0pLCFpfHxyfHxudWxsPT12LnJvdGF0aW9uWCl7dmFyIE4sWCx6LEksRSxGLFksQj1uWzBdLFU9blsxXSxXPW5bMl0saj1uWzNdLFY9bls0XSxIPW5bNV0sUT1uWzZdLFo9bls3XSwkPW5bMTFdLEc9TWF0aC5hdGFuMihRLFIpLEs9LWI+R3x8Rz5iO3Yucm90YXRpb25YPUcqTCxHJiYoST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksTj1WKkkrUypFLFg9SCpJK0MqRSx6PVEqSStSKkUsUz1WKi1FK1MqSSxDPUgqLUUrQypJLFI9USotRStSKkksJD1aKi1FKyQqSSxWPU4sSD1YLFE9eiksRz1NYXRoLmF0YW4yKFMsQiksdi5yb3RhdGlvblk9RypMLEcmJihGPS1iPkd8fEc+YixJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxOPUIqSS1TKkUsWD1VKkktQypFLHo9VypJLVIqRSxDPVUqRStDKkksUj1XKkUrUipJLCQ9aipFKyQqSSxCPU4sVT1YLFc9eiksRz1NYXRoLmF0YW4yKFUsSCksdi5yb3RhdGlvbj1HKkwsRyYmKFk9LWI+R3x8Rz5iLEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLEI9QipJK1YqRSxYPVUqSStIKkUsSD1VKi1FK0gqSSxRPVcqLUUrUSpJLFU9WCksWSYmSz92LnJvdGF0aW9uPXYucm90YXRpb25YPTA6WSYmRj92LnJvdGF0aW9uPXYucm90YXRpb25ZPTA6RiYmSyYmKHYucm90YXRpb25ZPXYucm90YXRpb25YPTApLHYuc2NhbGVYPSgwfE1hdGguc3FydChCKkIrVSpVKSp3Ky41KS93LHYuc2NhbGVZPSgwfE1hdGguc3FydChIKkgrQypDKSp3Ky41KS93LHYuc2NhbGVaPSgwfE1hdGguc3FydChRKlErUipSKSp3Ky41KS93LHYuc2tld1g9MCx2LnBlcnNwZWN0aXZlPSQ/MS8oMD4kPy0kOiQpOjAsdi54PWssdi55PU8sdi56PUR9fWVsc2UgaWYoISh4ZSYmIXImJm4ubGVuZ3RoJiZ2Lng9PT1uWzRdJiZ2Lnk9PT1uWzVdJiYodi5yb3RhdGlvblh8fHYucm90YXRpb25ZKXx8dm9pZCAwIT09di54JiZcIm5vbmVcIj09PXEodCxcImRpc3BsYXlcIixlKSkpe3ZhciBKPW4ubGVuZ3RoPj02LHRlPUo/blswXToxLGVlPW5bMV18fDAsaWU9blsyXXx8MCxyZT1KP25bM106MTt2Lng9bls0XXx8MCx2Lnk9bls1XXx8MCx1PU1hdGguc3FydCh0ZSp0ZStlZSplZSksZj1NYXRoLnNxcnQocmUqcmUraWUqaWUpLF89dGV8fGVlP01hdGguYXRhbjIoZWUsdGUpKkw6di5yb3RhdGlvbnx8MCxwPWllfHxyZT9NYXRoLmF0YW4yKGllLHJlKSpMK186di5za2V3WHx8MCxjPXUtTWF0aC5hYnModi5zY2FsZVh8fDApLGQ9Zi1NYXRoLmFicyh2LnNjYWxlWXx8MCksTWF0aC5hYnMocCk+OTAmJjI3MD5NYXRoLmFicyhwKSYmKHk/KHUqPS0xLHArPTA+PV8/MTgwOi0xODAsXys9MD49Xz8xODA6LTE4MCk6KGYqPS0xLHArPTA+PXA/MTgwOi0xODApKSxtPShfLXYucm90YXRpb24pJTE4MCxnPShwLXYuc2tld1gpJTE4MCwodm9pZCAwPT09di5za2V3WHx8Yz5UfHwtVD5jfHxkPlR8fC1UPmR8fG0+LXgmJng+bSYmZmFsc2V8bSp3fHxnPi14JiZ4PmcmJmZhbHNlfGcqdykmJih2LnNjYWxlWD11LHYuc2NhbGVZPWYsdi5yb3RhdGlvbj1fLHYuc2tld1g9cCkseGUmJih2LnJvdGF0aW9uWD12LnJvdGF0aW9uWT12Lno9MCx2LnBlcnNwZWN0aXZlPXBhcnNlRmxvYXQoYS5kZWZhdWx0VHJhbnNmb3JtUGVyc3BlY3RpdmUpfHwwLHYuc2NhbGVaPTEpfXYuek9yaWdpbj1QO2ZvcihvIGluIHYpVD52W29dJiZ2W29dPi1UJiYodltvXT0wKTtyZXR1cm4gaSYmKHQuX2dzVHJhbnNmb3JtPXYpLHZ9LFNlPWZ1bmN0aW9uKHQpe3ZhciBlLGkscj10aGlzLmRhdGEscz0tci5yb3RhdGlvbipNLG49cytyLnNrZXdYKk0sYT0xZTUsbz0oMHxNYXRoLmNvcyhzKSpyLnNjYWxlWCphKS9hLGw9KDB8TWF0aC5zaW4ocykqci5zY2FsZVgqYSkvYSxoPSgwfE1hdGguc2luKG4pKi1yLnNjYWxlWSphKS9hLHU9KDB8TWF0aC5jb3Mobikqci5zY2FsZVkqYSkvYSxmPXRoaXMudC5zdHlsZSxfPXRoaXMudC5jdXJyZW50U3R5bGU7aWYoXyl7aT1sLGw9LWgsaD0taSxlPV8uZmlsdGVyLGYuZmlsdGVyPVwiXCI7dmFyIHAsZCxtPXRoaXMudC5vZmZzZXRXaWR0aCxnPXRoaXMudC5vZmZzZXRIZWlnaHQsdj1cImFic29sdXRlXCIhPT1fLnBvc2l0aW9uLHc9XCJwcm9naWQ6RFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuTWF0cml4KE0xMT1cIitvK1wiLCBNMTI9XCIrbCtcIiwgTTIxPVwiK2grXCIsIE0yMj1cIit1LHg9ci54LGI9ci55O2lmKG51bGwhPXIub3gmJihwPShyLm94cD8uMDEqbSpyLm94OnIub3gpLW0vMixkPShyLm95cD8uMDEqZypyLm95OnIub3kpLWcvMix4Kz1wLShwKm8rZCpsKSxiKz1kLShwKmgrZCp1KSksdj8ocD1tLzIsZD1nLzIsdys9XCIsIER4PVwiKyhwLShwKm8rZCpsKSt4KStcIiwgRHk9XCIrKGQtKHAqaCtkKnUpK2IpK1wiKVwiKTp3Kz1cIiwgc2l6aW5nTWV0aG9kPSdhdXRvIGV4cGFuZCcpXCIsZi5maWx0ZXI9LTEhPT1lLmluZGV4T2YoXCJEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5NYXRyaXgoXCIpP2UucmVwbGFjZShPLHcpOncrXCIgXCIrZSwoMD09PXR8fDE9PT10KSYmMT09PW8mJjA9PT1sJiYwPT09aCYmMT09PXUmJih2JiYtMT09PXcuaW5kZXhPZihcIkR4PTAsIER5PTBcIil8fFQudGVzdChlKSYmMTAwIT09cGFyc2VGbG9hdChSZWdFeHAuJDEpfHwtMT09PWUuaW5kZXhPZihcImdyYWRpZW50KFwiJiZlLmluZGV4T2YoXCJBbHBoYVwiKSkmJmYucmVtb3ZlQXR0cmlidXRlKFwiZmlsdGVyXCIpKSwhdil7dmFyIFAsUyxDLFI9OD5jPzE6LTE7Zm9yKHA9ci5pZU9mZnNldFh8fDAsZD1yLmllT2Zmc2V0WXx8MCxyLmllT2Zmc2V0WD1NYXRoLnJvdW5kKChtLSgoMD5vPy1vOm8pKm0rKDA+bD8tbDpsKSpnKSkvMit4KSxyLmllT2Zmc2V0WT1NYXRoLnJvdW5kKChnLSgoMD51Py11OnUpKmcrKDA+aD8taDpoKSptKSkvMitiKSxjZT0wOzQ+Y2U7Y2UrKylTPUpbY2VdLFA9X1tTXSxpPS0xIT09UC5pbmRleE9mKFwicHhcIik/cGFyc2VGbG9hdChQKTpRKHRoaXMudCxTLHBhcnNlRmxvYXQoUCksUC5yZXBsYWNlKHksXCJcIikpfHwwLEM9aSE9PXJbU10/Mj5jZT8tci5pZU9mZnNldFg6LXIuaWVPZmZzZXRZOjI+Y2U/cC1yLmllT2Zmc2V0WDpkLXIuaWVPZmZzZXRZLGZbU109KHJbU109TWF0aC5yb3VuZChpLUMqKDA9PT1jZXx8Mj09PWNlPzE6UikpKStcInB4XCJ9fX0sQ2U9RS5zZXQzRFRyYW5zZm9ybVJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYSxvLGwsaCx1LGYscCxjLGQsbSxnLHYseSxULHcseCxiLFAsUz10aGlzLmRhdGEsQz10aGlzLnQuc3R5bGUsUj1TLnJvdGF0aW9uKk0saz1TLnNjYWxlWCxBPVMuc2NhbGVZLE89Uy5zY2FsZVosRD1TLnBlcnNwZWN0aXZlO2lmKCEoMSE9PXQmJjAhPT10fHxcImF1dG9cIiE9PVMuZm9yY2UzRHx8Uy5yb3RhdGlvbll8fFMucm90YXRpb25YfHwxIT09T3x8RHx8Uy56KSlyZXR1cm4gUmUuY2FsbCh0aGlzLHQpLHZvaWQgMDtpZihfKXt2YXIgTD0xZS00O0w+ayYmaz4tTCYmKGs9Tz0yZS01KSxMPkEmJkE+LUwmJihBPU89MmUtNSksIUR8fFMuenx8Uy5yb3RhdGlvblh8fFMucm90YXRpb25ZfHwoRD0wKX1pZihSfHxTLnNrZXdYKXk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxlPXksbj1ULFMuc2tld1gmJihSLT1TLnNrZXdYKk0seT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLFwic2ltcGxlXCI9PT1TLnNrZXdUeXBlJiYodz1NYXRoLnRhbihTLnNrZXdYKk0pLHc9TWF0aC5zcXJ0KDErdyp3KSx5Kj13LFQqPXcpKSxpPS1ULGE9eTtlbHNle2lmKCEoUy5yb3RhdGlvbll8fFMucm90YXRpb25YfHwxIT09T3x8RCkpcmV0dXJuIENbeWVdPVwidHJhbnNsYXRlM2QoXCIrUy54K1wicHgsXCIrUy55K1wicHgsXCIrUy56K1wicHgpXCIrKDEhPT1rfHwxIT09QT9cIiBzY2FsZShcIitrK1wiLFwiK0ErXCIpXCI6XCJcIiksdm9pZCAwO2U9YT0xLGk9bj0wfWY9MSxyPXM9bz1sPWg9dT1wPWM9ZD0wLG09RD8tMS9EOjAsZz1TLnpPcmlnaW4sdj0xZTUsUj1TLnJvdGF0aW9uWSpNLFImJih5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksaD1mKi1ULGM9bSotVCxyPWUqVCxvPW4qVCxmKj15LG0qPXksZSo9eSxuKj15KSxSPVMucm90YXRpb25YKk0sUiYmKHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSx3PWkqeStyKlQseD1hKnkrbypULGI9dSp5K2YqVCxQPWQqeSttKlQscj1pKi1UK3IqeSxvPWEqLVQrbyp5LGY9dSotVCtmKnksbT1kKi1UK20qeSxpPXcsYT14LHU9YixkPVApLDEhPT1PJiYocio9TyxvKj1PLGYqPU8sbSo9TyksMSE9PUEmJihpKj1BLGEqPUEsdSo9QSxkKj1BKSwxIT09ayYmKGUqPWssbio9ayxoKj1rLGMqPWspLGcmJihwLT1nLHM9cipwLGw9bypwLHA9ZipwK2cpLHM9KHc9KHMrPVMueCktKHN8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K3M6cyxsPSh3PShsKz1TLnkpLShsfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditsOmwscD0odz0ocCs9Uy56KS0ocHw9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrcDpwLENbeWVdPVwibWF0cml4M2QoXCIrWygwfGUqdikvdiwoMHxuKnYpL3YsKDB8aCp2KS92LCgwfGMqdikvdiwoMHxpKnYpL3YsKDB8YSp2KS92LCgwfHUqdikvdiwoMHxkKnYpL3YsKDB8cip2KS92LCgwfG8qdikvdiwoMHxmKnYpL3YsKDB8bSp2KS92LHMsbCxwLEQ/MSstcC9EOjFdLmpvaW4oXCIsXCIpK1wiKVwifSxSZT1FLnNldDJEVHJhbnNmb3JtUmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhPXRoaXMuZGF0YSxvPXRoaXMudCxsPW8uc3R5bGU7cmV0dXJuIGEucm90YXRpb25YfHxhLnJvdGF0aW9uWXx8YS56fHxhLmZvcmNlM0Q9PT0hMHx8XCJhdXRvXCI9PT1hLmZvcmNlM0QmJjEhPT10JiYwIT09dD8odGhpcy5zZXRSYXRpbz1DZSxDZS5jYWxsKHRoaXMsdCksdm9pZCAwKTooYS5yb3RhdGlvbnx8YS5za2V3WD8oZT1hLnJvdGF0aW9uKk0saT1lLWEuc2tld1gqTSxyPTFlNSxzPWEuc2NhbGVYKnIsbj1hLnNjYWxlWSpyLGxbeWVdPVwibWF0cml4KFwiKygwfE1hdGguY29zKGUpKnMpL3IrXCIsXCIrKDB8TWF0aC5zaW4oZSkqcykvcitcIixcIisoMHxNYXRoLnNpbihpKSotbikvcitcIixcIisoMHxNYXRoLmNvcyhpKSpuKS9yK1wiLFwiK2EueCtcIixcIithLnkrXCIpXCIpOmxbeWVdPVwibWF0cml4KFwiK2Euc2NhbGVYK1wiLDAsMCxcIithLnNjYWxlWStcIixcIithLngrXCIsXCIrYS55K1wiKVwiLHZvaWQgMCl9O21lKFwidHJhbnNmb3JtLHNjYWxlLHNjYWxlWCxzY2FsZVksc2NhbGVaLHgseSx6LHJvdGF0aW9uLHJvdGF0aW9uWCxyb3RhdGlvblkscm90YXRpb25aLHNrZXdYLHNrZXdZLHNob3J0Um90YXRpb24sc2hvcnRSb3RhdGlvblgsc2hvcnRSb3RhdGlvblksc2hvcnRSb3RhdGlvblosdHJhbnNmb3JtT3JpZ2luLHRyYW5zZm9ybVBlcnNwZWN0aXZlLGRpcmVjdGlvbmFsUm90YXRpb24scGFyc2VUcmFuc2Zvcm0sZm9yY2UzRCxza2V3VHlwZVwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLG8sbCl7aWYoci5fdHJhbnNmb3JtKXJldHVybiBuO3ZhciBoLHUsZixfLHAsYyxkLG09ci5fdHJhbnNmb3JtPVBlKHQscywhMCxsLnBhcnNlVHJhbnNmb3JtKSxnPXQuc3R5bGUsdj0xZS02LHk9dmUubGVuZ3RoLFQ9bCx3PXt9O2lmKFwic3RyaW5nXCI9PXR5cGVvZiBULnRyYW5zZm9ybSYmeWUpZj16LnN0eWxlLGZbeWVdPVQudHJhbnNmb3JtLGYuZGlzcGxheT1cImJsb2NrXCIsZi5wb3NpdGlvbj1cImFic29sdXRlXCIsWC5ib2R5LmFwcGVuZENoaWxkKHopLGg9UGUoeixudWxsLCExKSxYLmJvZHkucmVtb3ZlQ2hpbGQoeik7ZWxzZSBpZihcIm9iamVjdFwiPT10eXBlb2YgVCl7aWYoaD17c2NhbGVYOnJlKG51bGwhPVQuc2NhbGVYP1Quc2NhbGVYOlQuc2NhbGUsbS5zY2FsZVgpLHNjYWxlWTpyZShudWxsIT1ULnNjYWxlWT9ULnNjYWxlWTpULnNjYWxlLG0uc2NhbGVZKSxzY2FsZVo6cmUoVC5zY2FsZVosbS5zY2FsZVopLHg6cmUoVC54LG0ueCkseTpyZShULnksbS55KSx6OnJlKFQueixtLnopLHBlcnNwZWN0aXZlOnJlKFQudHJhbnNmb3JtUGVyc3BlY3RpdmUsbS5wZXJzcGVjdGl2ZSl9LGQ9VC5kaXJlY3Rpb25hbFJvdGF0aW9uLG51bGwhPWQpaWYoXCJvYmplY3RcIj09dHlwZW9mIGQpZm9yKGYgaW4gZClUW2ZdPWRbZl07ZWxzZSBULnJvdGF0aW9uPWQ7aC5yb3RhdGlvbj1zZShcInJvdGF0aW9uXCJpbiBUP1Qucm90YXRpb246XCJzaG9ydFJvdGF0aW9uXCJpbiBUP1Quc2hvcnRSb3RhdGlvbitcIl9zaG9ydFwiOlwicm90YXRpb25aXCJpbiBUP1Qucm90YXRpb25aOm0ucm90YXRpb24sbS5yb3RhdGlvbixcInJvdGF0aW9uXCIsdykseGUmJihoLnJvdGF0aW9uWD1zZShcInJvdGF0aW9uWFwiaW4gVD9ULnJvdGF0aW9uWDpcInNob3J0Um90YXRpb25YXCJpbiBUP1Quc2hvcnRSb3RhdGlvblgrXCJfc2hvcnRcIjptLnJvdGF0aW9uWHx8MCxtLnJvdGF0aW9uWCxcInJvdGF0aW9uWFwiLHcpLGgucm90YXRpb25ZPXNlKFwicm90YXRpb25ZXCJpbiBUP1Qucm90YXRpb25ZOlwic2hvcnRSb3RhdGlvbllcImluIFQ/VC5zaG9ydFJvdGF0aW9uWStcIl9zaG9ydFwiOm0ucm90YXRpb25ZfHwwLG0ucm90YXRpb25ZLFwicm90YXRpb25ZXCIsdykpLGguc2tld1g9bnVsbD09VC5za2V3WD9tLnNrZXdYOnNlKFQuc2tld1gsbS5za2V3WCksaC5za2V3WT1udWxsPT1ULnNrZXdZP20uc2tld1k6c2UoVC5za2V3WSxtLnNrZXdZKSwodT1oLnNrZXdZLW0uc2tld1kpJiYoaC5za2V3WCs9dSxoLnJvdGF0aW9uKz11KX1mb3IoeGUmJm51bGwhPVQuZm9yY2UzRCYmKG0uZm9yY2UzRD1ULmZvcmNlM0QsYz0hMCksbS5za2V3VHlwZT1ULnNrZXdUeXBlfHxtLnNrZXdUeXBlfHxhLmRlZmF1bHRTa2V3VHlwZSxwPW0uZm9yY2UzRHx8bS56fHxtLnJvdGF0aW9uWHx8bS5yb3RhdGlvbll8fGguenx8aC5yb3RhdGlvblh8fGgucm90YXRpb25ZfHxoLnBlcnNwZWN0aXZlLHB8fG51bGw9PVQuc2NhbGV8fChoLnNjYWxlWj0xKTstLXk+LTE7KWk9dmVbeV0sXz1oW2ldLW1baV0sKF8+dnx8LXY+X3x8bnVsbCE9TltpXSkmJihjPSEwLG49bmV3IF9lKG0saSxtW2ldLF8sbiksaSBpbiB3JiYobi5lPXdbaV0pLG4ueHMwPTAsbi5wbHVnaW49byxyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKG4ubikpO3JldHVybiBfPVQudHJhbnNmb3JtT3JpZ2luLChffHx4ZSYmcCYmbS56T3JpZ2luKSYmKHllPyhjPSEwLGk9d2UsXz0oX3x8cSh0LGkscywhMSxcIjUwJSA1MCVcIikpK1wiXCIsbj1uZXcgX2UoZyxpLDAsMCxuLC0xLFwidHJhbnNmb3JtT3JpZ2luXCIpLG4uYj1nW2ldLG4ucGx1Z2luPW8seGU/KGY9bS56T3JpZ2luLF89Xy5zcGxpdChcIiBcIiksbS56T3JpZ2luPShfLmxlbmd0aD4yJiYoMD09PWZ8fFwiMHB4XCIhPT1fWzJdKT9wYXJzZUZsb2F0KF9bMl0pOmYpfHwwLG4ueHMwPW4uZT1fWzBdK1wiIFwiKyhfWzFdfHxcIjUwJVwiKStcIiAwcHhcIixuPW5ldyBfZShtLFwiek9yaWdpblwiLDAsMCxuLC0xLG4ubiksbi5iPWYsbi54czA9bi5lPW0uek9yaWdpbik6bi54czA9bi5lPV8pOmVlKF8rXCJcIixtKSksYyYmKHIuX3RyYW5zZm9ybVR5cGU9cHx8Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGU/MzoyKSxufSxwcmVmaXg6ITB9KSxtZShcImJveFNoYWRvd1wiLHtkZWZhdWx0VmFsdWU6XCIwcHggMHB4IDBweCAwcHggIzk5OVwiLHByZWZpeDohMCxjb2xvcjohMCxtdWx0aTohMCxrZXl3b3JkOlwiaW5zZXRcIn0pLG1lKFwiYm9yZGVyUmFkaXVzXCIse2RlZmF1bHRWYWx1ZTpcIjBweFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxuLGEpe2U9dGhpcy5mb3JtYXQoZSk7dmFyIG8sbCxoLHUsZixfLHAsYyxkLG0sZyx2LHksVCx3LHgsYj1bXCJib3JkZXJUb3BMZWZ0UmFkaXVzXCIsXCJib3JkZXJUb3BSaWdodFJhZGl1c1wiLFwiYm9yZGVyQm90dG9tUmlnaHRSYWRpdXNcIixcImJvcmRlckJvdHRvbUxlZnRSYWRpdXNcIl0sUD10LnN0eWxlO2ZvcihkPXBhcnNlRmxvYXQodC5vZmZzZXRXaWR0aCksbT1wYXJzZUZsb2F0KHQub2Zmc2V0SGVpZ2h0KSxvPWUuc3BsaXQoXCIgXCIpLGw9MDtiLmxlbmd0aD5sO2wrKyl0aGlzLnAuaW5kZXhPZihcImJvcmRlclwiKSYmKGJbbF09VihiW2xdKSksZj11PXEodCxiW2xdLHMsITEsXCIwcHhcIiksLTEhPT1mLmluZGV4T2YoXCIgXCIpJiYodT1mLnNwbGl0KFwiIFwiKSxmPXVbMF0sdT11WzFdKSxfPWg9b1tsXSxwPXBhcnNlRmxvYXQoZiksdj1mLnN1YnN0cigocCtcIlwiKS5sZW5ndGgpLHk9XCI9XCI9PT1fLmNoYXJBdCgxKSx5PyhjPXBhcnNlSW50KF8uY2hhckF0KDApK1wiMVwiLDEwKSxfPV8uc3Vic3RyKDIpLGMqPXBhcnNlRmxvYXQoXyksZz1fLnN1YnN0cigoYytcIlwiKS5sZW5ndGgtKDA+Yz8xOjApKXx8XCJcIik6KGM9cGFyc2VGbG9hdChfKSxnPV8uc3Vic3RyKChjK1wiXCIpLmxlbmd0aCkpLFwiXCI9PT1nJiYoZz1yW2ldfHx2KSxnIT09diYmKFQ9USh0LFwiYm9yZGVyTGVmdFwiLHAsdiksdz1RKHQsXCJib3JkZXJUb3BcIixwLHYpLFwiJVwiPT09Zz8oZj0xMDAqKFQvZCkrXCIlXCIsdT0xMDAqKHcvbSkrXCIlXCIpOlwiZW1cIj09PWc/KHg9USh0LFwiYm9yZGVyTGVmdFwiLDEsXCJlbVwiKSxmPVQveCtcImVtXCIsdT13L3grXCJlbVwiKTooZj1UK1wicHhcIix1PXcrXCJweFwiKSx5JiYoXz1wYXJzZUZsb2F0KGYpK2MrZyxoPXBhcnNlRmxvYXQodSkrYytnKSksYT1wZShQLGJbbF0sZitcIiBcIit1LF8rXCIgXCIraCwhMSxcIjBweFwiLGEpO3JldHVybiBhfSxwcmVmaXg6ITAsZm9ybWF0dGVyOmhlKFwiMHB4IDBweCAwcHggMHB4XCIsITEsITApfSksbWUoXCJiYWNrZ3JvdW5kUG9zaXRpb25cIix7ZGVmYXVsdFZhbHVlOlwiMCAwXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbyxsLGgsdSxmLF8scD1cImJhY2tncm91bmQtcG9zaXRpb25cIixkPXN8fEgodCxudWxsKSxtPXRoaXMuZm9ybWF0KChkP2M/ZC5nZXRQcm9wZXJ0eVZhbHVlKHArXCIteFwiKStcIiBcIitkLmdldFByb3BlcnR5VmFsdWUocCtcIi15XCIpOmQuZ2V0UHJvcGVydHlWYWx1ZShwKTp0LmN1cnJlbnRTdHlsZS5iYWNrZ3JvdW5kUG9zaXRpb25YK1wiIFwiK3QuY3VycmVudFN0eWxlLmJhY2tncm91bmRQb3NpdGlvblkpfHxcIjAgMFwiKSxnPXRoaXMuZm9ybWF0KGUpO2lmKC0xIT09bS5pbmRleE9mKFwiJVwiKSE9KC0xIT09Zy5pbmRleE9mKFwiJVwiKSkmJihfPXEodCxcImJhY2tncm91bmRJbWFnZVwiKS5yZXBsYWNlKEMsXCJcIiksXyYmXCJub25lXCIhPT1fKSl7Zm9yKG89bS5zcGxpdChcIiBcIiksbD1nLnNwbGl0KFwiIFwiKSxJLnNldEF0dHJpYnV0ZShcInNyY1wiLF8pLGg9MjstLWg+LTE7KW09b1toXSx1PS0xIT09bS5pbmRleE9mKFwiJVwiKSx1IT09KC0xIT09bFtoXS5pbmRleE9mKFwiJVwiKSkmJihmPTA9PT1oP3Qub2Zmc2V0V2lkdGgtSS53aWR0aDp0Lm9mZnNldEhlaWdodC1JLmhlaWdodCxvW2hdPXU/cGFyc2VGbG9hdChtKS8xMDAqZitcInB4XCI6MTAwKihwYXJzZUZsb2F0KG0pL2YpK1wiJVwiKTttPW8uam9pbihcIiBcIil9cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsbSxnLG4sYSl9LGZvcm1hdHRlcjplZX0pLG1lKFwiYmFja2dyb3VuZFNpemVcIix7ZGVmYXVsdFZhbHVlOlwiMCAwXCIsZm9ybWF0dGVyOmVlfSksbWUoXCJwZXJzcGVjdGl2ZVwiLHtkZWZhdWx0VmFsdWU6XCIwcHhcIixwcmVmaXg6ITB9KSxtZShcInBlcnNwZWN0aXZlT3JpZ2luXCIse2RlZmF1bHRWYWx1ZTpcIjUwJSA1MCVcIixwcmVmaXg6ITB9KSxtZShcInRyYW5zZm9ybVN0eWxlXCIse3ByZWZpeDohMH0pLG1lKFwiYmFja2ZhY2VWaXNpYmlsaXR5XCIse3ByZWZpeDohMH0pLG1lKFwidXNlclNlbGVjdFwiLHtwcmVmaXg6ITB9KSxtZShcIm1hcmdpblwiLHtwYXJzZXI6dWUoXCJtYXJnaW5Ub3AsbWFyZ2luUmlnaHQsbWFyZ2luQm90dG9tLG1hcmdpbkxlZnRcIil9KSxtZShcInBhZGRpbmdcIix7cGFyc2VyOnVlKFwicGFkZGluZ1RvcCxwYWRkaW5nUmlnaHQscGFkZGluZ0JvdHRvbSxwYWRkaW5nTGVmdFwiKX0pLG1lKFwiY2xpcFwiLHtkZWZhdWx0VmFsdWU6XCJyZWN0KDBweCwwcHgsMHB4LDBweClcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvLGwsaDtyZXR1cm4gOT5jPyhsPXQuY3VycmVudFN0eWxlLGg9OD5jP1wiIFwiOlwiLFwiLG89XCJyZWN0KFwiK2wuY2xpcFRvcCtoK2wuY2xpcFJpZ2h0K2grbC5jbGlwQm90dG9tK2grbC5jbGlwTGVmdCtcIilcIixlPXRoaXMuZm9ybWF0KGUpLnNwbGl0KFwiLFwiKS5qb2luKGgpKToobz10aGlzLmZvcm1hdChxKHQsdGhpcy5wLHMsITEsdGhpcy5kZmx0KSksZT10aGlzLmZvcm1hdChlKSksdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSxvLGUsbixhKX19KSxtZShcInRleHRTaGFkb3dcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IDBweCAwcHggIzk5OVwiLGNvbG9yOiEwLG11bHRpOiEwfSksbWUoXCJhdXRvUm91bmQsc3RyaWN0VW5pdHNcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIscyl7cmV0dXJuIHN9fSksbWUoXCJib3JkZXJcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IHNvbGlkICMwMDBcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3JldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLHRoaXMuZm9ybWF0KHEodCxcImJvcmRlclRvcFdpZHRoXCIscywhMSxcIjBweFwiKStcIiBcIitxKHQsXCJib3JkZXJUb3BTdHlsZVwiLHMsITEsXCJzb2xpZFwiKStcIiBcIitxKHQsXCJib3JkZXJUb3BDb2xvclwiLHMsITEsXCIjMDAwXCIpKSx0aGlzLmZvcm1hdChlKSxuLGEpfSxjb2xvcjohMCxmb3JtYXR0ZXI6ZnVuY3Rpb24odCl7dmFyIGU9dC5zcGxpdChcIiBcIik7cmV0dXJuIGVbMF0rXCIgXCIrKGVbMV18fFwic29saWRcIikrXCIgXCIrKHQubWF0Y2gobGUpfHxbXCIjMDAwXCJdKVswXX19KSxtZShcImJvcmRlcldpZHRoXCIse3BhcnNlcjp1ZShcImJvcmRlclRvcFdpZHRoLGJvcmRlclJpZ2h0V2lkdGgsYm9yZGVyQm90dG9tV2lkdGgsYm9yZGVyTGVmdFdpZHRoXCIpfSksbWUoXCJmbG9hdCxjc3NGbG9hdCxzdHlsZUZsb2F0XCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuPXQuc3R5bGUsYT1cImNzc0Zsb2F0XCJpbiBuP1wiY3NzRmxvYXRcIjpcInN0eWxlRmxvYXRcIjtyZXR1cm4gbmV3IF9lKG4sYSwwLDAscywtMSxpLCExLDAsblthXSxlKX19KTt2YXIga2U9ZnVuY3Rpb24odCl7dmFyIGUsaT10aGlzLnQscj1pLmZpbHRlcnx8cSh0aGlzLmRhdGEsXCJmaWx0ZXJcIikscz0wfHRoaXMucyt0aGlzLmMqdDsxMDA9PT1zJiYoLTE9PT1yLmluZGV4T2YoXCJhdHJpeChcIikmJi0xPT09ci5pbmRleE9mKFwicmFkaWVudChcIikmJi0xPT09ci5pbmRleE9mKFwib2FkZXIoXCIpPyhpLnJlbW92ZUF0dHJpYnV0ZShcImZpbHRlclwiKSxlPSFxKHRoaXMuZGF0YSxcImZpbHRlclwiKSk6KGkuZmlsdGVyPXIucmVwbGFjZSh4LFwiXCIpLGU9ITApKSxlfHwodGhpcy54bjEmJihpLmZpbHRlcj1yPXJ8fFwiYWxwaGEob3BhY2l0eT1cIitzK1wiKVwiKSwtMT09PXIuaW5kZXhPZihcInBhY2l0eVwiKT8wPT09cyYmdGhpcy54bjF8fChpLmZpbHRlcj1yK1wiIGFscGhhKG9wYWNpdHk9XCIrcytcIilcIik6aS5maWx0ZXI9ci5yZXBsYWNlKFQsXCJvcGFjaXR5PVwiK3MpKX07bWUoXCJvcGFjaXR5LGFscGhhLGF1dG9BbHBoYVwiLHtkZWZhdWx0VmFsdWU6XCIxXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbz1wYXJzZUZsb2F0KHEodCxcIm9wYWNpdHlcIixzLCExLFwiMVwiKSksbD10LnN0eWxlLGg9XCJhdXRvQWxwaGFcIj09PWk7cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIGUmJlwiPVwiPT09ZS5jaGFyQXQoMSkmJihlPShcIi1cIj09PWUuY2hhckF0KDApPy0xOjEpKnBhcnNlRmxvYXQoZS5zdWJzdHIoMikpK28pLGgmJjE9PT1vJiZcImhpZGRlblwiPT09cSh0LFwidmlzaWJpbGl0eVwiLHMpJiYwIT09ZSYmKG89MCksWT9uPW5ldyBfZShsLFwib3BhY2l0eVwiLG8sZS1vLG4pOihuPW5ldyBfZShsLFwib3BhY2l0eVwiLDEwMCpvLDEwMCooZS1vKSxuKSxuLnhuMT1oPzE6MCxsLnpvb209MSxuLnR5cGU9MixuLmI9XCJhbHBoYShvcGFjaXR5PVwiK24ucytcIilcIixuLmU9XCJhbHBoYShvcGFjaXR5PVwiKyhuLnMrbi5jKStcIilcIixuLmRhdGE9dCxuLnBsdWdpbj1hLG4uc2V0UmF0aW89a2UpLGgmJihuPW5ldyBfZShsLFwidmlzaWJpbGl0eVwiLDAsMCxuLC0xLG51bGwsITEsMCwwIT09bz9cImluaGVyaXRcIjpcImhpZGRlblwiLDA9PT1lP1wiaGlkZGVuXCI6XCJpbmhlcml0XCIpLG4ueHMwPVwiaW5oZXJpdFwiLHIuX292ZXJ3cml0ZVByb3BzLnB1c2gobi5uKSxyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKGkpKSxufX0pO3ZhciBBZT1mdW5jdGlvbih0LGUpe2UmJih0LnJlbW92ZVByb3BlcnR5PyhcIm1zXCI9PT1lLnN1YnN0cigwLDIpJiYoZT1cIk1cIitlLnN1YnN0cigxKSksdC5yZW1vdmVQcm9wZXJ0eShlLnJlcGxhY2UoUCxcIi0kMVwiKS50b0xvd2VyQ2FzZSgpKSk6dC5yZW1vdmVBdHRyaWJ1dGUoZSkpfSxPZT1mdW5jdGlvbih0KXtpZih0aGlzLnQuX2dzQ2xhc3NQVD10aGlzLDE9PT10fHwwPT09dCl7dGhpcy50LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsMD09PXQ/dGhpcy5iOnRoaXMuZSk7Zm9yKHZhciBlPXRoaXMuZGF0YSxpPXRoaXMudC5zdHlsZTtlOyllLnY/aVtlLnBdPWUudjpBZShpLGUucCksZT1lLl9uZXh0OzE9PT10JiZ0aGlzLnQuX2dzQ2xhc3NQVD09PXRoaXMmJih0aGlzLnQuX2dzQ2xhc3NQVD1udWxsKX1lbHNlIHRoaXMudC5nZXRBdHRyaWJ1dGUoXCJjbGFzc1wiKSE9PXRoaXMuZSYmdGhpcy50LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsdGhpcy5lKX07bWUoXCJjbGFzc05hbWVcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxyLG4sYSxvLGwpe3ZhciBoLHUsZixfLHAsYz10LmdldEF0dHJpYnV0ZShcImNsYXNzXCIpfHxcIlwiLGQ9dC5zdHlsZS5jc3NUZXh0O2lmKGE9bi5fY2xhc3NOYW1lUFQ9bmV3IF9lKHQsciwwLDAsYSwyKSxhLnNldFJhdGlvPU9lLGEucHI9LTExLGk9ITAsYS5iPWMsdT0kKHQscyksZj10Ll9nc0NsYXNzUFQpe2ZvcihfPXt9LHA9Zi5kYXRhO3A7KV9bcC5wXT0xLHA9cC5fbmV4dDtmLnNldFJhdGlvKDEpfXJldHVybiB0Ll9nc0NsYXNzUFQ9YSxhLmU9XCI9XCIhPT1lLmNoYXJBdCgxKT9lOmMucmVwbGFjZShSZWdFeHAoXCJcXFxccypcXFxcYlwiK2Uuc3Vic3RyKDIpK1wiXFxcXGJcIiksXCJcIikrKFwiK1wiPT09ZS5jaGFyQXQoMCk/XCIgXCIrZS5zdWJzdHIoMik6XCJcIiksbi5fdHdlZW4uX2R1cmF0aW9uJiYodC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLGEuZSksaD1HKHQsdSwkKHQpLGwsXyksdC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLGMpLGEuZGF0YT1oLmZpcnN0TVBULHQuc3R5bGUuY3NzVGV4dD1kLGE9YS54Zmlyc3Q9bi5wYXJzZSh0LGguZGlmcyxhLG8pKSxhfX0pO3ZhciBEZT1mdW5jdGlvbih0KXtpZigoMT09PXR8fDA9PT10KSYmdGhpcy5kYXRhLl90b3RhbFRpbWU9PT10aGlzLmRhdGEuX3RvdGFsRHVyYXRpb24mJlwiaXNGcm9tU3RhcnRcIiE9PXRoaXMuZGF0YS5kYXRhKXt2YXIgZSxpLHIscyxuPXRoaXMudC5zdHlsZSxhPW8udHJhbnNmb3JtLnBhcnNlO2lmKFwiYWxsXCI9PT10aGlzLmUpbi5jc3NUZXh0PVwiXCIscz0hMDtlbHNlIGZvcihlPXRoaXMuZS5zcGxpdChcIixcIikscj1lLmxlbmd0aDstLXI+LTE7KWk9ZVtyXSxvW2ldJiYob1tpXS5wYXJzZT09PWE/cz0hMDppPVwidHJhbnNmb3JtT3JpZ2luXCI9PT1pP3dlOm9baV0ucCksQWUobixpKTtzJiYoQWUobix5ZSksdGhpcy50Ll9nc1RyYW5zZm9ybSYmZGVsZXRlIHRoaXMudC5fZ3NUcmFuc2Zvcm0pfX07Zm9yKG1lKFwiY2xlYXJQcm9wc1wiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLHIscyxuKXtyZXR1cm4gbj1uZXcgX2UodCxyLDAsMCxuLDIpLG4uc2V0UmF0aW89RGUsbi5lPWUsbi5wcj0tMTAsbi5kYXRhPXMuX3R3ZWVuLGk9ITAsbn19KSxsPVwiYmV6aWVyLHRocm93UHJvcHMscGh5c2ljc1Byb3BzLHBoeXNpY3MyRFwiLnNwbGl0KFwiLFwiKSxjZT1sLmxlbmd0aDtjZS0tOylnZShsW2NlXSk7bD1hLnByb3RvdHlwZSxsLl9maXJzdFBUPW51bGwsbC5fb25Jbml0VHdlZW49ZnVuY3Rpb24odCxlLG8pe2lmKCF0Lm5vZGVUeXBlKXJldHVybiExO3RoaXMuX3RhcmdldD10LHRoaXMuX3R3ZWVuPW8sdGhpcy5fdmFycz1lLGg9ZS5hdXRvUm91bmQsaT0hMSxyPWUuc3VmZml4TWFwfHxhLnN1ZmZpeE1hcCxzPUgodCxcIlwiKSxuPXRoaXMuX292ZXJ3cml0ZVByb3BzO3ZhciBsLF8sYyxkLG0sZyx2LHksVCx4PXQuc3R5bGU7aWYodSYmXCJcIj09PXguekluZGV4JiYobD1xKHQsXCJ6SW5kZXhcIixzKSwoXCJhdXRvXCI9PT1sfHxcIlwiPT09bCkmJnRoaXMuX2FkZExhenlTZXQoeCxcInpJbmRleFwiLDApKSxcInN0cmluZ1wiPT10eXBlb2YgZSYmKGQ9eC5jc3NUZXh0LGw9JCh0LHMpLHguY3NzVGV4dD1kK1wiO1wiK2UsbD1HKHQsbCwkKHQpKS5kaWZzLCFZJiZ3LnRlc3QoZSkmJihsLm9wYWNpdHk9cGFyc2VGbG9hdChSZWdFeHAuJDEpKSxlPWwseC5jc3NUZXh0PWQpLHRoaXMuX2ZpcnN0UFQ9Xz10aGlzLnBhcnNlKHQsZSxudWxsKSx0aGlzLl90cmFuc2Zvcm1UeXBlKXtmb3IoVD0zPT09dGhpcy5fdHJhbnNmb3JtVHlwZSx5ZT9mJiYodT0hMCxcIlwiPT09eC56SW5kZXgmJih2PXEodCxcInpJbmRleFwiLHMpLChcImF1dG9cIj09PXZ8fFwiXCI9PT12KSYmdGhpcy5fYWRkTGF6eVNldCh4LFwiekluZGV4XCIsMCkpLHAmJnRoaXMuX2FkZExhenlTZXQoeCxcIldlYmtpdEJhY2tmYWNlVmlzaWJpbGl0eVwiLHRoaXMuX3ZhcnMuV2Via2l0QmFja2ZhY2VWaXNpYmlsaXR5fHwoVD9cInZpc2libGVcIjpcImhpZGRlblwiKSkpOnguem9vbT0xLGM9XztjJiZjLl9uZXh0OyljPWMuX25leHQ7eT1uZXcgX2UodCxcInRyYW5zZm9ybVwiLDAsMCxudWxsLDIpLHRoaXMuX2xpbmtDU1NQKHksbnVsbCxjKSx5LnNldFJhdGlvPVQmJnhlP0NlOnllP1JlOlNlLHkuZGF0YT10aGlzLl90cmFuc2Zvcm18fFBlKHQscywhMCksbi5wb3AoKX1pZihpKXtmb3IoO187KXtmb3IoZz1fLl9uZXh0LGM9ZDtjJiZjLnByPl8ucHI7KWM9Yy5fbmV4dDsoXy5fcHJldj1jP2MuX3ByZXY6bSk/Xy5fcHJldi5fbmV4dD1fOmQ9XywoXy5fbmV4dD1jKT9jLl9wcmV2PV86bT1fLF89Z310aGlzLl9maXJzdFBUPWR9cmV0dXJuITB9LGwucGFyc2U9ZnVuY3Rpb24odCxlLGksbil7dmFyIGEsbCx1LGYsXyxwLGMsZCxtLGcsdj10LnN0eWxlO2ZvcihhIGluIGUpcD1lW2FdLGw9b1thXSxsP2k9bC5wYXJzZSh0LHAsYSx0aGlzLGksbixlKTooXz1xKHQsYSxzKStcIlwiLG09XCJzdHJpbmdcIj09dHlwZW9mIHAsXCJjb2xvclwiPT09YXx8XCJmaWxsXCI9PT1hfHxcInN0cm9rZVwiPT09YXx8LTEhPT1hLmluZGV4T2YoXCJDb2xvclwiKXx8bSYmYi50ZXN0KHApPyhtfHwocD1vZShwKSxwPShwLmxlbmd0aD4zP1wicmdiYShcIjpcInJnYihcIikrcC5qb2luKFwiLFwiKStcIilcIiksaT1wZSh2LGEsXyxwLCEwLFwidHJhbnNwYXJlbnRcIixpLDAsbikpOiFtfHwtMT09PXAuaW5kZXhPZihcIiBcIikmJi0xPT09cC5pbmRleE9mKFwiLFwiKT8odT1wYXJzZUZsb2F0KF8pLGM9dXx8MD09PXU/Xy5zdWJzdHIoKHUrXCJcIikubGVuZ3RoKTpcIlwiLChcIlwiPT09X3x8XCJhdXRvXCI9PT1fKSYmKFwid2lkdGhcIj09PWF8fFwiaGVpZ2h0XCI9PT1hPyh1PXRlKHQsYSxzKSxjPVwicHhcIik6XCJsZWZ0XCI9PT1hfHxcInRvcFwiPT09YT8odT1aKHQsYSxzKSxjPVwicHhcIik6KHU9XCJvcGFjaXR5XCIhPT1hPzA6MSxjPVwiXCIpKSxnPW0mJlwiPVwiPT09cC5jaGFyQXQoMSksZz8oZj1wYXJzZUludChwLmNoYXJBdCgwKStcIjFcIiwxMCkscD1wLnN1YnN0cigyKSxmKj1wYXJzZUZsb2F0KHApLGQ9cC5yZXBsYWNlKHksXCJcIikpOihmPXBhcnNlRmxvYXQocCksZD1tP3Auc3Vic3RyKChmK1wiXCIpLmxlbmd0aCl8fFwiXCI6XCJcIiksXCJcIj09PWQmJihkPWEgaW4gcj9yW2FdOmMpLHA9Znx8MD09PWY/KGc/Zit1OmYpK2Q6ZVthXSxjIT09ZCYmXCJcIiE9PWQmJihmfHwwPT09ZikmJnUmJih1PVEodCxhLHUsYyksXCIlXCI9PT1kPyh1Lz1RKHQsYSwxMDAsXCIlXCIpLzEwMCxlLnN0cmljdFVuaXRzIT09ITAmJihfPXUrXCIlXCIpKTpcImVtXCI9PT1kP3UvPVEodCxhLDEsXCJlbVwiKTpcInB4XCIhPT1kJiYoZj1RKHQsYSxmLGQpLGQ9XCJweFwiKSxnJiYoZnx8MD09PWYpJiYocD1mK3UrZCkpLGcmJihmKz11KSwhdSYmMCE9PXV8fCFmJiYwIT09Zj92b2lkIDAhPT12W2FdJiYocHx8XCJOYU5cIiE9cCtcIlwiJiZudWxsIT1wKT8oaT1uZXcgX2UodixhLGZ8fHV8fDAsMCxpLC0xLGEsITEsMCxfLHApLGkueHMwPVwibm9uZVwiIT09cHx8XCJkaXNwbGF5XCIhPT1hJiYtMT09PWEuaW5kZXhPZihcIlN0eWxlXCIpP3A6Xyk6VShcImludmFsaWQgXCIrYStcIiB0d2VlbiB2YWx1ZTogXCIrZVthXSk6KGk9bmV3IF9lKHYsYSx1LGYtdSxpLDAsYSxoIT09ITEmJihcInB4XCI9PT1kfHxcInpJbmRleFwiPT09YSksMCxfLHApLGkueHMwPWQpKTppPXBlKHYsYSxfLHAsITAsbnVsbCxpLDAsbikpLG4mJmkmJiFpLnBsdWdpbiYmKGkucGx1Z2luPW4pO3JldHVybiBpfSxsLnNldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzPXRoaXMuX2ZpcnN0UFQsbj0xZS02O2lmKDEhPT10fHx0aGlzLl90d2Vlbi5fdGltZSE9PXRoaXMuX3R3ZWVuLl9kdXJhdGlvbiYmMCE9PXRoaXMuX3R3ZWVuLl90aW1lKWlmKHR8fHRoaXMuX3R3ZWVuLl90aW1lIT09dGhpcy5fdHdlZW4uX2R1cmF0aW9uJiYwIT09dGhpcy5fdHdlZW4uX3RpbWV8fHRoaXMuX3R3ZWVuLl9yYXdQcmV2VGltZT09PS0xZS02KWZvcig7czspe2lmKGU9cy5jKnQrcy5zLHMucj9lPU1hdGgucm91bmQoZSk6bj5lJiZlPi1uJiYoZT0wKSxzLnR5cGUpaWYoMT09PXMudHlwZSlpZihyPXMubCwyPT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyO2Vsc2UgaWYoMz09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMztlbHNlIGlmKDQ9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czMrcy54bjMrcy54czQ7ZWxzZSBpZig1PT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzK3MueG4zK3MueHM0K3MueG40K3MueHM1O2Vsc2V7Zm9yKGk9cy54czArZStzLnhzMSxyPTE7cy5sPnI7cisrKWkrPXNbXCJ4blwiK3JdK3NbXCJ4c1wiKyhyKzEpXTtzLnRbcy5wXT1pfWVsc2UtMT09PXMudHlwZT9zLnRbcy5wXT1zLnhzMDpzLnNldFJhdGlvJiZzLnNldFJhdGlvKHQpO2Vsc2Ugcy50W3MucF09ZStzLnhzMDtzPXMuX25leHR9ZWxzZSBmb3IoO3M7KTIhPT1zLnR5cGU/cy50W3MucF09cy5iOnMuc2V0UmF0aW8odCkscz1zLl9uZXh0O2Vsc2UgZm9yKDtzOykyIT09cy50eXBlP3MudFtzLnBdPXMuZTpzLnNldFJhdGlvKHQpLHM9cy5fbmV4dH0sbC5fZW5hYmxlVHJhbnNmb3Jtcz1mdW5jdGlvbih0KXt0aGlzLl90cmFuc2Zvcm1UeXBlPXR8fDM9PT10aGlzLl90cmFuc2Zvcm1UeXBlPzM6Mix0aGlzLl90cmFuc2Zvcm09dGhpcy5fdHJhbnNmb3JtfHxQZSh0aGlzLl90YXJnZXQscywhMCl9O3ZhciBNZT1mdW5jdGlvbigpe3RoaXMudFt0aGlzLnBdPXRoaXMuZSx0aGlzLmRhdGEuX2xpbmtDU1NQKHRoaXMsdGhpcy5fbmV4dCxudWxsLCEwKX07bC5fYWRkTGF6eVNldD1mdW5jdGlvbih0LGUsaSl7dmFyIHI9dGhpcy5fZmlyc3RQVD1uZXcgX2UodCxlLDAsMCx0aGlzLl9maXJzdFBULDIpO3IuZT1pLHIuc2V0UmF0aW89TWUsci5kYXRhPXRoaXN9LGwuX2xpbmtDU1NQPWZ1bmN0aW9uKHQsZSxpLHIpe3JldHVybiB0JiYoZSYmKGUuX3ByZXY9dCksdC5fbmV4dCYmKHQuX25leHQuX3ByZXY9dC5fcHJldiksdC5fcHJldj90Ll9wcmV2Ll9uZXh0PXQuX25leHQ6dGhpcy5fZmlyc3RQVD09PXQmJih0aGlzLl9maXJzdFBUPXQuX25leHQscj0hMCksaT9pLl9uZXh0PXQ6cnx8bnVsbCE9PXRoaXMuX2ZpcnN0UFR8fCh0aGlzLl9maXJzdFBUPXQpLHQuX25leHQ9ZSx0Ll9wcmV2PWkpLHR9LGwuX2tpbGw9ZnVuY3Rpb24oZSl7dmFyIGkscixzLG49ZTtpZihlLmF1dG9BbHBoYXx8ZS5hbHBoYSl7bj17fTtmb3IociBpbiBlKW5bcl09ZVtyXTtuLm9wYWNpdHk9MSxuLmF1dG9BbHBoYSYmKG4udmlzaWJpbGl0eT0xKX1yZXR1cm4gZS5jbGFzc05hbWUmJihpPXRoaXMuX2NsYXNzTmFtZVBUKSYmKHM9aS54Zmlyc3QscyYmcy5fcHJldj90aGlzLl9saW5rQ1NTUChzLl9wcmV2LGkuX25leHQscy5fcHJldi5fcHJldik6cz09PXRoaXMuX2ZpcnN0UFQmJih0aGlzLl9maXJzdFBUPWkuX25leHQpLGkuX25leHQmJnRoaXMuX2xpbmtDU1NQKGkuX25leHQsaS5fbmV4dC5fbmV4dCxzLl9wcmV2KSx0aGlzLl9jbGFzc05hbWVQVD1udWxsKSx0LnByb3RvdHlwZS5fa2lsbC5jYWxsKHRoaXMsbil9O3ZhciBMZT1mdW5jdGlvbih0LGUsaSl7dmFyIHIscyxuLGE7aWYodC5zbGljZSlmb3Iocz10Lmxlbmd0aDstLXM+LTE7KUxlKHRbc10sZSxpKTtlbHNlIGZvcihyPXQuY2hpbGROb2RlcyxzPXIubGVuZ3RoOy0tcz4tMTspbj1yW3NdLGE9bi50eXBlLG4uc3R5bGUmJihlLnB1c2goJChuKSksaSYmaS5wdXNoKG4pKSwxIT09YSYmOSE9PWEmJjExIT09YXx8IW4uY2hpbGROb2Rlcy5sZW5ndGh8fExlKG4sZSxpKX07cmV0dXJuIGEuY2FzY2FkZVRvPWZ1bmN0aW9uKHQsaSxyKXt2YXIgcyxuLGEsbz1lLnRvKHQsaSxyKSxsPVtvXSxoPVtdLHU9W10sZj1bXSxfPWUuX2ludGVybmFscy5yZXNlcnZlZFByb3BzO2Zvcih0PW8uX3RhcmdldHN8fG8udGFyZ2V0LExlKHQsaCxmKSxvLnJlbmRlcihpLCEwKSxMZSh0LHUpLG8ucmVuZGVyKDAsITApLG8uX2VuYWJsZWQoITApLHM9Zi5sZW5ndGg7LS1zPi0xOylpZihuPUcoZltzXSxoW3NdLHVbc10pLG4uZmlyc3RNUFQpe249bi5kaWZzO1xyXG5mb3IoYSBpbiByKV9bYV0mJihuW2FdPXJbYV0pO2wucHVzaChlLnRvKGZbc10saSxuKSl9cmV0dXJuIGx9LHQuYWN0aXZhdGUoW2FdKSxhfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS43LjNcclxuICogREFURTogMjAxNC0wMS0xNFxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICoqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3ZhciB0PWRvY3VtZW50LmRvY3VtZW50RWxlbWVudCxlPXdpbmRvdyxpPWZ1bmN0aW9uKGkscyl7dmFyIHI9XCJ4XCI9PT1zP1wiV2lkdGhcIjpcIkhlaWdodFwiLG49XCJzY3JvbGxcIityLGE9XCJjbGllbnRcIityLG89ZG9jdW1lbnQuYm9keTtyZXR1cm4gaT09PWV8fGk9PT10fHxpPT09bz9NYXRoLm1heCh0W25dLG9bbl0pLShlW1wiaW5uZXJcIityXXx8TWF0aC5tYXgodFthXSxvW2FdKSk6aVtuXS1pW1wib2Zmc2V0XCIrcl19LHM9d2luZG93Ll9nc0RlZmluZS5wbHVnaW4oe3Byb3BOYW1lOlwic2Nyb2xsVG9cIixBUEk6Mix2ZXJzaW9uOlwiMS43LjNcIixpbml0OmZ1bmN0aW9uKHQscyxyKXtyZXR1cm4gdGhpcy5fd2R3PXQ9PT1lLHRoaXMuX3RhcmdldD10LHRoaXMuX3R3ZWVuPXIsXCJvYmplY3RcIiE9dHlwZW9mIHMmJihzPXt5OnN9KSx0aGlzLl9hdXRvS2lsbD1zLmF1dG9LaWxsIT09ITEsdGhpcy54PXRoaXMueFByZXY9dGhpcy5nZXRYKCksdGhpcy55PXRoaXMueVByZXY9dGhpcy5nZXRZKCksbnVsbCE9cy54Pyh0aGlzLl9hZGRUd2Vlbih0aGlzLFwieFwiLHRoaXMueCxcIm1heFwiPT09cy54P2kodCxcInhcIik6cy54LFwic2Nyb2xsVG9feFwiLCEwKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5wdXNoKFwic2Nyb2xsVG9feFwiKSk6dGhpcy5za2lwWD0hMCxudWxsIT1zLnk/KHRoaXMuX2FkZFR3ZWVuKHRoaXMsXCJ5XCIsdGhpcy55LFwibWF4XCI9PT1zLnk/aSh0LFwieVwiKTpzLnksXCJzY3JvbGxUb195XCIsITApLHRoaXMuX292ZXJ3cml0ZVByb3BzLnB1c2goXCJzY3JvbGxUb195XCIpKTp0aGlzLnNraXBZPSEwLCEwfSxzZXQ6ZnVuY3Rpb24odCl7dGhpcy5fc3VwZXIuc2V0UmF0aW8uY2FsbCh0aGlzLHQpO3ZhciBzPXRoaXMuX3dkd3x8IXRoaXMuc2tpcFg/dGhpcy5nZXRYKCk6dGhpcy54UHJldixyPXRoaXMuX3dkd3x8IXRoaXMuc2tpcFk/dGhpcy5nZXRZKCk6dGhpcy55UHJldixuPXItdGhpcy55UHJldixhPXMtdGhpcy54UHJldjt0aGlzLl9hdXRvS2lsbCYmKCF0aGlzLnNraXBYJiYoYT43fHwtNz5hKSYmaSh0aGlzLl90YXJnZXQsXCJ4XCIpPnMmJih0aGlzLnNraXBYPSEwKSwhdGhpcy5za2lwWSYmKG4+N3x8LTc+bikmJmkodGhpcy5fdGFyZ2V0LFwieVwiKT5yJiYodGhpcy5za2lwWT0hMCksdGhpcy5za2lwWCYmdGhpcy5za2lwWSYmdGhpcy5fdHdlZW4ua2lsbCgpKSx0aGlzLl93ZHc/ZS5zY3JvbGxUbyh0aGlzLnNraXBYP3M6dGhpcy54LHRoaXMuc2tpcFk/cjp0aGlzLnkpOih0aGlzLnNraXBZfHwodGhpcy5fdGFyZ2V0LnNjcm9sbFRvcD10aGlzLnkpLHRoaXMuc2tpcFh8fCh0aGlzLl90YXJnZXQuc2Nyb2xsTGVmdD10aGlzLngpKSx0aGlzLnhQcmV2PXRoaXMueCx0aGlzLnlQcmV2PXRoaXMueX19KSxyPXMucHJvdG90eXBlO3MubWF4PWksci5nZXRYPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3dkdz9udWxsIT1lLnBhZ2VYT2Zmc2V0P2UucGFnZVhPZmZzZXQ6bnVsbCE9dC5zY3JvbGxMZWZ0P3Quc2Nyb2xsTGVmdDpkb2N1bWVudC5ib2R5LnNjcm9sbExlZnQ6dGhpcy5fdGFyZ2V0LnNjcm9sbExlZnR9LHIuZ2V0WT1mdW5jdGlvbigpe3JldHVybiB0aGlzLl93ZHc/bnVsbCE9ZS5wYWdlWU9mZnNldD9lLnBhZ2VZT2Zmc2V0Om51bGwhPXQuc2Nyb2xsVG9wP3Quc2Nyb2xsVG9wOmRvY3VtZW50LmJvZHkuc2Nyb2xsVG9wOnRoaXMuX3RhcmdldC5zY3JvbGxUb3B9LHIuX2tpbGw9ZnVuY3Rpb24odCl7cmV0dXJuIHQuc2Nyb2xsVG9feCYmKHRoaXMuc2tpcFg9ITApLHQuc2Nyb2xsVG9feSYmKHRoaXMuc2tpcFk9ITApLHRoaXMuX3N1cGVyLl9raWxsLmNhbGwodGhpcyx0KX19KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiXX0=
