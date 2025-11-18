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
  function removeId(id) {
    rocketInsightsIds = rocketInsightsIds.filter(x => x !== parseInt(id, 10));
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
    if (isOnRocketInsights()) {
      const $tableGlobalScore = $('.wpr-ri-urls-table .wpr-global-score');
      if ($tableGlobalScore.length > 0) {
        $tableGlobalScore.replaceWith(globalScoreData.row_html);
      } else {
        $tableBody.prepend(globalScoreData.row_html);
      }
    }
  }

  /**
   * Updates the global score UI widget or table row based on the selected menu.
   * When the dashboard or rocket insights menu is clicked, this function updates
   * the corresponding global score display after a short delay.
   *
   * @param {string} id - The ID of the clicked menu item.
   */
  function decideGlobalScoreToUpdate(id) {
    // Delay UI update a bit till element is visible.
    setTimeout(() => {
      switch (id) {
        // Handle action when dashboard menu is clicked.
        case 'wpr-nav-dashboard':
          if ('' === globalScoreData.html) {
            return;
          }
          let globalScoreWidget = $('#wpr_global_score_widget');
          if (!globalScoreWidget.is(':visible')) {
            return;
          }

          // Update global score widget.
          globalScoreWidget.html(globalScoreData.html);

          // Disable "Add Pages" button on global score widget.
          if (!('disabled_btn_html' in globalScoreData)) {
            return;
          }
          $('#wpr_global_score_widget_add_page_btn_wrapper').html(globalScoreData.disabled_btn_html.global_score_widget);
          break;

        // Handle action when rocket insights menu is clicked.
        case 'wpr-nav-rocket_insights':
          if ('' === globalScoreData.row_html) {
            return;
          }
          updateGlobalScoreRow(globalScoreData);
          break;
      }
    }, 30);
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

          // Update global score widget if on dashboard.
          if (isOnDashboard()) {
            $('#wpr_global_score_widget').html(response.global_score_data.html);
          }
          // Update global score row in table if on Rocket Insights page.
          updateGlobalScoreRow(globalScoreData);
        }
        response.results.forEach(result => {
          const $row = $(`[data-rocket-insights-id="${result.id}"]`);
          $row.replaceWith(result.html);
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
    window.wp.apiFetch({
      path: '/wp-rocket/v1/rocket-insights/pages/',
      method: 'POST',
      data: {
        page_url: pageUrl
      }
    }).then(response => {
      if (response.success) {
        $pageUrlInput.val('');
        $tableBody.append(response.html);
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

        // Start polling if not already running
        if (!pollTimer) {
          pollInterval = POLL_BASE_INTERVAL;
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
        const $row = $(`[data-rocket-insights-id="${response.id}"]`);
        $row.replaceWith(response.html);

        // Update credit status
        updateCreditState(response.has_credit);

        // Update quota banner visibility
        updateQuotaBanner(response.can_add_pages);

        // Update global score data.
        globalScoreData = response.global_score_data;

        // Update global score row in table if on Rocket Insights page.
        updateGlobalScoreRow(globalScoreData);
        // Start polling if not already running
        if (!pollTimer) {
          pollInterval = POLL_BASE_INTERVAL;
          schedulePolling();
        }
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
      handleAddPage(e);
    }
  });

  // Only poll if on a wpr section that requires polling(dashboard|rocket_insights) (more robust check)
  function isValidPageForPolling() {
    const urlParams = new URLSearchParams(window.location.search);
    switch (window.location.hash) {
      case '#dashboard':
      case '#rocket_insights':
        return urlParams.get('page') === 'wprocket';
      default:
        return false;
    }
  }

  // Resume polling if needed
  if (isValidPageForPolling() && rocketInsightsIds.length > 0) {
    pollInterval = POLL_BASE_INTERVAL;
    schedulePolling();
  }

  // Handle UI update when menu(dashboard|rocket_insights) is clicked.
  $('.wpr-Header-nav a').on('click', function () {
    const id = this.id;
    decideGlobalScoreToUpdate(id);
  });

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
require("../global/rocketcdn.js");
require("../global/countdown.js");

},{"../global/ajax.js":2,"../global/beacon.js":4,"../global/countdown.js":5,"../global/fields.js":6,"../global/main.js":7,"../global/pageManager.js":8,"../global/rocketcdn.js":9,"../lib/greensock/TimelineLite.min.js":10,"../lib/greensock/TweenLite.min.js":11,"../lib/greensock/easing/EasePack.min.js":12,"../lib/greensock/plugins/CSSPlugin.min.js":13,"../lib/greensock/plugins/ScrollToPlugin.min.js":14}],4:[function(require,module,exports){
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
    $('#analytics_enabled').trigger('change');
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
});

},{}],8:[function(require,module,exports){
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
    this.$sidebar.style.display = 'block';
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

},{}],9:[function(require,module,exports){
"use strict";

/*eslint-env es6*/
((document, window) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wpr-rocketcdn-open').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
      });
    });
    maybeOpenModal();
    MicroModal.init({
      disableScroll: true
    });
    const iframe = document.getElementById('rocketcdn-iframe');
    const loader = document.getElementById('wpr-rocketcdn-modal-loader');
    if (iframe && loader) {
      iframe.addEventListener('load', function () {
        loader.style.display = 'none';
      });
    }
  });
  window.addEventListener('load', () => {
    let openCTA = document.querySelector('#wpr-rocketcdn-open-cta'),
      closeCTA = document.querySelector('#wpr-rocketcdn-close-cta'),
      smallCTA = document.querySelector('#wpr-rocketcdn-cta-small'),
      bigCTA = document.querySelector('#wpr-rocketcdn-cta');
    if (null !== openCTA && null !== smallCTA && null !== bigCTA) {
      openCTA.addEventListener('click', e => {
        e.preventDefault();
        smallCTA.classList.add('wpr-isHidden');
        bigCTA.classList.remove('wpr-isHidden');
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
  function closeModal(data) {
    if (!data.hasOwnProperty('cdnFrameClose')) {
      return;
    }
    MicroModal.close('wpr-rocketcdn-modal');
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
})(document, window);

},{}],10:[function(require,module,exports){
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

},{}],11:[function(require,module,exports){
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

},{}],12:[function(require,module,exports){
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

},{}],14:[function(require,module,exports){
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvcGFnZU1hbmFnZXIuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldGNkbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxJQUFJO0VBQ25FLE1BQU0sU0FBUyxHQUFHLFlBQVksQ0FBQyxhQUFhLENBQUMsZ0JBQWdCLENBQUM7RUFDOUQsTUFBTSxhQUFhLEdBQUcsWUFBWSxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQztFQUNuRSxNQUFNLE9BQU8sR0FBRyxTQUFBLENBQVMsR0FBRyxFQUFFO0lBQzdCLE1BQU0saUJBQWlCLEdBQUcsSUFBSSxXQUFXLENBQUMsc0JBQXNCLEVBQUU7TUFDakUsTUFBTSxFQUFFO1FBQ1AsY0FBYyxFQUFFO01BQ2pCO0lBQ0QsQ0FBQyxDQUFDO0lBQ0YsYUFBYSxDQUFDLFdB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QUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxVQUFVO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsT0FBTztNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsT0FBTztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1VBQUMsVUFBVSxFQUFDLENBQUM7VUFBQyxnQkFBZ0IsRUFBQyxDQUFDO1VBQUMsZUFBZSxFQUFDLENBQUM7VUFBQyxpQkFBaUIsRUFBQyxJQUFJLENBQUM7UUFBaUIsQ0FBQyxDQUFDO01BQUMsS0FBSSxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLGVBQWUsS0FBRyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLENBQUMsaUJBQWlCLEtBQUcsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7TUFBQyxLQUFJLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUUsQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxZQUFZLENBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxRQUFRLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7WUFBQyxNQUFNLEVBQUM7VUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLEtBQUcsVUFBVSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLE9BQU8sS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxVQUFVLElBQUUsT0FBTyxDQUFDLEVBQUMsTUFBSyxhQUFhLEdBQUMsQ0FBQyxHQUFDLHVFQUF1RTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxZQUFZLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztRQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sSUFBSTtNQUFBO01BQUMsT0FBTSxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxjQUFjLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7TUFBQyxJQUFHLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsWUFBVyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSTtRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLE9BQU8sSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxjQUFjO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFlBQVksRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsWUFBWSxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLG1CQUFtQixDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksSUFBRSxJQUFJLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksSUFBRSxDQUFDLENBQUMsTUFBSSxDQUFDLEdBQUMsbUJBQW1CLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFlBQVksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLENBQUMsRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsWUFBWSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFHLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUcsTUFBSSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLE1BQUksQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsa0JBQWtCLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQyxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRTtRQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsQ0FBQyxFQUFDLE9BQU0sQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUE7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLFVBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxDQUFDLFlBQVksQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRTtRQUFDLElBQUcsQ0FBQyxLQUFHLElBQUksRUFBQyxPQUFNLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFBO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO01BQUMsS0FBSSxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxHQUFHLEVBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksS0FBRyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDO1FBQUMsSUFBRyxJQUFJLENBQUMsTUFBTSxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsWUFBWSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixLQUFHLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLEVBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxJQUFJLENBQUMsY0FBYztNQUFBO01BQUMsT0FBTyxDQUFDLEtBQUcsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsbUJBQW1CO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFVBQVU7SUFBQSxDQUFDLEVBQUMsQ0FBQztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7Ozs7O0FDWHhyVDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxVQUFTLENBQUMsRUFBQztFQUFDLFlBQVk7O0VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixJQUFFLENBQUM7RUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQztNQUFDLENBQUMsR0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxZQUFVO1FBQUMsSUFBSSxDQUFDLEdBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxRQUFRO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDO1FBQUMsT0FBTyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsRUFBRTtRQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBRSxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztVQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxDQUFDLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLE1BQU0sSUFBRSxNQUFNLENBQUMsR0FBRyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsR0FBRyxHQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBQyxFQUFFLEVBQUMsWUFBVTtZQUFDLE9BQU8sQ0FBQztVQUFBLENBQUMsQ0FBQyxHQUFDLFdBQVcsSUFBRSxPQUFPLE1BQU0sSUFBRSxNQUFNLENBQUMsT0FBTyxLQUFHLE1BQU0sQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLFlBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsWUFBVTtVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSwwQkFBMEIsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUEsQ0FBQztJQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxFQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLENBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTTtRQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxRQUFRLEVBQUMsTUFBTSxFQUFDLE9BQU8sRUFBQyxPQUFPLEVBQUMsY0FBYyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxXQUFXLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFdBQVcsQ0FBQztJQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsU0FBUztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyx3QkFBd0IsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLElBQUUsSUFBSTtJQUFBLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxLQUFJLElBQUksSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLEVBQUUsRUFBQyxDQUFDO1FBQUMsRUFBRSxFQUFDO01BQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQztNQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxxQkFBcUI7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG9CQUFvQjtNQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLFlBQVU7UUFBQyxPQUFPLElBQUksSUFBSSxDQUFELENBQUMsQ0FBRSxPQUFPLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUksRUFBQyxLQUFLLEVBQUMsUUFBUSxFQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLHVCQUF1QixDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsc0JBQXNCLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLDZCQUE2QixDQUFDO0lBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLEdBQUMsR0FBRztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUM7WUFBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsS0FBSyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxNQUFNLENBQUM7UUFBQSxDQUFDO01BQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtRQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFlBQVU7UUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7UUFBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsRUFBRSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsSUFBRSxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxJQUFFLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsWUFBVTtRQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsSUFBSSxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBQyxlQUFlLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLE1BQU07SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxlQUFlLEtBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLENBQUMsTUFBTSxDQUFELENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVU7TUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFlBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtNQUFDLE9BQU0sQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLFFBQVEsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLEtBQUcsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtRQUFDLElBQUcsQ0FBQyxLQUFHLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsTUFBTTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEtBQUcsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxjQUFjO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsVUFBVTtNQUFDLElBQUcsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixFQUFDO1VBQUMsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsT0FBSyxDQUFDLENBQUMsU0FBUyxHQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO1FBQUE7UUFBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxVQUFVLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxNQUFJLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsYUFBYSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLElBQUksQ0FBQyxVQUFVO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxPQUFPLElBQUksQ0FBQyxVQUFVO01BQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxTQUFTLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsS0FBRyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLElBQUksQ0FBQyxTQUFTO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPO01BQUMsSUFBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsU0FBUyxFQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGlCQUFpQixLQUFHLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGtCQUFrQixHQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxJQUFJLEtBQUcsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLGFBQWEsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLE1BQU0sS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU07TUFBQyxLQUFJLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sTUFBSSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFlBQVU7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVTtJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLElBQUksSUFBRSxDQUFDLEVBQUMsTUFBSyw2QkFBNkI7UUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTO1FBQUMsSUFBRyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxHQUFDLEVBQUUsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBSSxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO1FBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsZUFBZSxLQUFHLENBQUMsQ0FBQyxNQUFJLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxJQUFFLFdBQVcsS0FBRyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxJQUFFLE9BQU8sS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyx1QkFBdUIsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLGNBQWMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUM7UUFBQyxTQUFTLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsZ0JBQWdCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDLENBQUM7UUFBQyxZQUFZLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxjQUFjLEVBQUMsQ0FBQztRQUFDLGFBQWEsRUFBQyxDQUFDO1FBQUMsT0FBTyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLFlBQVksRUFBQyxDQUFDO1FBQUMsaUJBQWlCLEVBQUMsQ0FBQztRQUFDLHVCQUF1QixFQUFDLENBQUM7UUFBQyxzQkFBc0IsRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxjQUFjLEVBQUMsQ0FBQztRQUFDLGFBQWEsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUM7UUFBQyxXQUFXLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUM7UUFBQyxRQUFRLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsR0FBRyxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxXQUFXLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsT0FBTyxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVU7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxZQUFVO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLEdBQUcsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQztVQUFDLE9BQUssQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztRQUFBO01BQUM7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsV0FBVyxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVTtRQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQztVQUFDLE1BQU0sRUFBQyxDQUFDO1VBQUMsTUFBTSxFQUFDO1FBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxPQUFLLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTTtNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLElBQUcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQztVQUFNLE9BQU8sQ0FBQztRQUFBO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxTQUFTO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsS0FBSyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBRTtVQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxPQUFNLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztRQUFBO1FBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtNQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sRUFBQztRQUFDLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDO01BQU0sQ0FBQyxNQUFLLElBQUcsQ0FBQyxDQUFDLFlBQVksSUFBRSxDQUFDLEtBQUcsQ0FBQyxFQUFDLElBQUcsSUFBSSxDQUFDLFFBQVEsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsU0FBUyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLGFBQWEsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxFQUFDO1FBQU0sQ0FBQyxNQUFLLElBQUksQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLFlBQVksQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLFlBQVksS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsV0FBVyxFQUFDLElBQUksQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxpQkFBaUIsRUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsSUFBRSxVQUFVLElBQUUsT0FBTyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTSxDQUFDLENBQUM7TUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsSUFBSSxJQUFJLENBQUMsSUFBSSxFQUFDO1FBQUMsSUFBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBRCxDQUFDLEVBQUUsWUFBWSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQztZQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLFVBQVU7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQztZQUFDLEVBQUUsRUFBQyxDQUFDLENBQUM7VUFBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7VUFBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLGVBQWUsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFNBQVMsTUFBSSxJQUFJLENBQUMsdUJBQXVCLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLE1BQUssSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDO1VBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxRQUFRO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDO1VBQUMsRUFBRSxFQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsWUFBWTtNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxZQUFZLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLG1CQUFtQixDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLElBQUksR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLG1CQUFtQixFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1FBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsTUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQyxNQUFLLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLElBQUksQ0FBQyxRQUFRLEVBQUM7VUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsR0FBRyxFQUFDO1VBQU8sSUFBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDO1VBQUMsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxLQUFJLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsWUFBWSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLGtCQUFrQixJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxLQUFLLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLElBQUksSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSTtRQUFDLElBQUcsSUFBSSxDQUFDLFFBQVEsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsS0FBSztZQUFDO1VBQUs7UUFBQyxDQUFDLE1BQUk7VUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxFQUFDLE9BQU0sQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixJQUFFLENBQUMsQ0FBQyxHQUFDLEtBQUs7UUFBQTtRQUFDLElBQUcsQ0FBQyxFQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUM7VUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtNQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLHVCQUF1QixJQUFFLENBQUMsQ0FBQyxjQUFjLENBQUMsWUFBWSxFQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyx1QkFBdUIsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxXQUFXLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO1FBQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsdUJBQXVCLElBQUUsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUMsR0FBQyxXQUFXLEdBQUMsWUFBWSxFQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsZ0JBQWdCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDO1FBQUMsaUJBQWlCLEVBQUMsQ0FBQztRQUFDLHVCQUF1QixFQUFDLENBQUM7UUFBQyxzQkFBc0IsRUFBQyxDQUFDO1FBQUMsZUFBZSxFQUFDLENBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDO01BQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxJQUFFLENBQUMsRUFBQyxPQUFNLEVBQUU7TUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxNQUFLLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsa0JBQWtCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxxQkFBcUIsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsZUFBZSxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDO1FBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxRQUFRO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQztRQUFDLENBQUMsRUFBQztNQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxLQUFLLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGVBQWU7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLEdBQUMsRUFBRSxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLE9BQUssQ0FBQyxHQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRO01BQUMsSUFBRyxpQkFBaUIsS0FBRyxDQUFDLEVBQUM7UUFBQyxPQUFLLENBQUMsR0FBRTtVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFLLENBQUMsR0FBRSxDQUFDLENBQUMsRUFBRSxJQUFFLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBRCxDQUFDLENBQUUsU0FBUyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsRUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxHQUFHLENBQUMsRUFBQyxNQUFLLDRCQUE0QjtNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWM7UUFBQyxDQUFDLEdBQUM7VUFBQyxJQUFJLEVBQUMsY0FBYztVQUFDLEdBQUcsRUFBQyxVQUFVO1VBQUMsSUFBSSxFQUFDLE9BQU87VUFBQyxLQUFLLEVBQUMsYUFBYTtVQUFDLE9BQU8sRUFBQztRQUFpQixDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxFQUFDLFlBQVU7VUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsRUFBRTtRQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRztNQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUM7TUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxxREFBcUQsR0FBQyxDQUFDLENBQUM7SUFBQTtJQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7RUFBQTtBQUFDLENBQUMsRUFBRSxNQUFNLENBQUM7Ozs7O0FDWDE0dkI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUMsTUFBTSxDQUFDLFFBQVEsS0FBRyxNQUFNLENBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQyxFQUFFLElBQUksQ0FBQyxZQUFVO0VBQUMsWUFBWTs7RUFBQyxNQUFNLENBQUMsU0FBUyxDQUFDLGFBQWEsRUFBQyxDQUFDLGFBQWEsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDO0lBQUMsSUFBSSxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLGdCQUFnQixJQUFFLE1BQU07TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxTQUFTO01BQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxZQUFVLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUM7VUFBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztVQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO1VBQUMsU0FBUyxFQUFDLElBQUksQ0FBQyxDQUFEO1FBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUM7WUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUc7VUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO01BQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEVBQUU7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG9CQUFvQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsa0JBQWtCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxNQUFNLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxZQUFZLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQztNQUFDLENBQUM7TUFBQyxLQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxPQUFLLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO01BQUEsQ0FBQyxNQUFLLE9BQUssQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7TUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxJQUFJLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEtBQUssR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLE1BQU0sSUFBRSxDQUFDLElBQUUsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsTUFBTSxJQUFFLENBQUMsSUFBRSxJQUFJLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsSUFBRSxNQUFNLElBQUUsQ0FBQyxJQUFFLEtBQUssR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLEVBQUU7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFNLEVBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFNLEVBQUUsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUk7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxDQUFDLEVBQUUsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsbUJBQW1CLEVBQUM7TUFBQyxJQUFJLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLFFBQVEsRUFBQyxPQUFPLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFdBQVcsRUFBQyxPQUFPLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGFBQWEsRUFBQyxPQUFPLENBQUMsRUFBQyxDQUFDO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0FBQUEsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxNQUFNLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQzs7Ozs7QUNYdGxKO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxtQkFBbUIsRUFBQyxDQUFDLHFCQUFxQixFQUFDLFdBQVcsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVTtRQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsUUFBUTtNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsS0FBSyxDQUFDO0lBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLDJCQUEyQixHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLGFBQWEsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUM7TUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7TUFBQyxJQUFJLEVBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7TUFBQyxRQUFRLEVBQUMsQ0FBQztNQUFDLE9BQU8sRUFBQyxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7TUFBQyxXQUFXLEVBQUMsQ0FBQztNQUFDLFVBQVUsRUFBQztJQUFFLENBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQywyQkFBMkI7TUFBQyxDQUFDLEdBQUMsc0RBQXNEO01BQUMsQ0FBQyxHQUFDLGtEQUFrRDtNQUFDLENBQUMsR0FBQyxZQUFZO01BQUMsQ0FBQyxHQUFDLHVCQUF1QjtNQUFDLENBQUMsR0FBQyxzQkFBc0I7TUFBQyxDQUFDLEdBQUMsa0JBQWtCO01BQUMsQ0FBQyxHQUFDLHlCQUF5QjtNQUFDLENBQUMsR0FBQyxZQUFZO01BQUMsQ0FBQyxHQUFDLFVBQVU7TUFBQyxDQUFDLEdBQUMsWUFBWTtNQUFDLENBQUMsR0FBQyx3Q0FBd0M7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsdUJBQXVCO01BQUMsQ0FBQyxHQUFDLGdDQUFnQztNQUFDLENBQUMsR0FBQyxxREFBcUQ7TUFBQyxDQUFDLEdBQUMsdUJBQXVCO01BQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsR0FBRztNQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFFBQVE7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxLQUFLLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxLQUFLLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQztRQUFDLGFBQWEsRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBUyxDQUFDLFNBQVM7TUFBQyxDQUFDLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsU0FBUyxDQUFDLEVBQUMsNkJBQTZCLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyx1Q0FBdUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG9CQUFvQixDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE1BQU0sS0FBRyxFQUFFLENBQUMsR0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsTUFBTSxDQUFDLE9BQU8sSUFBRSxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLElBQUcsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUcsRUFBQyxLQUFLLEVBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUU7UUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUk7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxnQkFBZ0IsR0FBQyxZQUFVLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztRQUFDLE9BQU8sQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBRyxNQUFNLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEtBQUk7VUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsOEJBQThCLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsR0FBQyxpQkFBaUIsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxpQkFBaUIsR0FBQyxnQkFBZ0IsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSTtZQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLEdBQUc7WUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sR0FBQyxRQUFRLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFBO1VBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsYUFBYSxHQUFDLGNBQWMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxVQUFVLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxNQUFNLEdBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsT0FBSyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFLLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxJQUFFLE9BQU8sQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxTQUFTLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsS0FBSyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsR0FBQyxFQUFFLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsV0FBVyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTTtVQUFDLElBQUksRUFBQyxDQUFDO1VBQUMsUUFBUSxFQUFDO1FBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsS0FBSyxFQUFDLFFBQVE7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsWUFBWSxFQUFDLGFBQWEsRUFBQyxXQUFXLEVBQUMsY0FBYyxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLElBQUksSUFBRSxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLFdBQVcsS0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLEtBQUssQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsTUFBTSxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsSUFBRSxLQUFLLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE9BQU8sQ0FBQyxHQUFHLENBQUMsTUFBSSxDQUFDLEdBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU0sUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUk7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsT0FBTyxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxHQUFHLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxXQUFXLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEtBQUs7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLHFEQUFxRDtJQUFDLEtBQUksQ0FBQyxJQUFJLEVBQUUsRUFBQyxFQUFFLElBQUUsR0FBRyxHQUFDLENBQUMsR0FBQyxLQUFLO0lBQUMsRUFBRSxHQUFDLE1BQU0sQ0FBQyxFQUFFLEdBQUMsR0FBRyxFQUFDLElBQUksQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxFQUFFO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO1VBQUE7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEVBQUMsT0FBSyxDQUFDLEdBQUMsRUFBRSxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLFFBQVEsR0FBQyxFQUFFLENBQUM7UUFBQSxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO1VBQUE7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEVBQUMsT0FBSyxDQUFDLEdBQUMsRUFBRSxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1FBQUEsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxDQUFDO1FBQUEsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEtBQUcsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUU7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUM7WUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxFQUFDO2NBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztZQUFBO1VBQUMsQ0FBQyxNQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBO01BQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQztNQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVTtVQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUU7VUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUE7UUFBQyxPQUFNO1VBQUMsS0FBSyxFQUFDLENBQUM7VUFBQyxHQUFHLEVBQUMsQ0FBQztVQUFDLFFBQVEsRUFBQyxDQUFDO1VBQUMsRUFBRSxFQUFDO1FBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLFlBQVksRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUM7TUFBQSxDQUFDLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsRUFBRTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxjQUFjLEdBQUMsYUFBYSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxDQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxNQUFLLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQztJQUFDLEtBQUksQ0FBQyxHQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsRUFBQyxFQUFFLEVBQUUsR0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxFQUFFLENBQUMsR0FBQyxFQUFFO0lBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQztRQUFDLENBQUMsRUFBQyxDQUFDLEdBQUM7TUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsWUFBWSxFQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsMkJBQTJCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUM7VUFBQyxNQUFNLEVBQUM7UUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUTtVQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUM7WUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztjQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsTUFBTSxDQUFDLGdCQUFnQixJQUFFLE1BQU0sRUFBRSxHQUFHLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7Y0FBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLHNCQUFzQixDQUFDLEVBQUMsQ0FBQyxDQUFDO1lBQUE7VUFBQyxDQUFDLENBQUM7UUFBQTtNQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDO01BQUE7TUFBQyxPQUFPLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDO1FBQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7VUFBQyxPQUFPLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO1FBQUEsQ0FBQztRQUFDLFFBQVEsRUFBQztNQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxpRkFBaUYsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLFdBQVc7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLGlCQUFpQixDQUFDO01BQUMsRUFBRSxHQUFDLElBQUksS0FBRyxDQUFDLENBQUMsYUFBYSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFlBQVksSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsWUFBWTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUUsQ0FBRCxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUQsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxLQUFLLENBQUMseUJBQXlCLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1VBQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLFNBQVMsRUFBQztZQUFDLElBQUksQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztjQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDO1lBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFBO1FBQUMsQ0FBQyxNQUFLLElBQUcsRUFBRSxFQUFFLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDO1lBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsRUFBRSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEVBQUUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsR0FBRyxFQUFDLENBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLDJCQUEyQixDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVk7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLEVBQUU7VUFBQyxJQUFJLENBQUM7WUFBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsV0FBVztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVk7WUFBQyxDQUFDLEdBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxRQUFRO1lBQUMsQ0FBQyxHQUFDLCtDQUErQyxHQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLENBQUMsRUFBRSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsSUFBRSwrQkFBK0IsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsb0NBQW9DLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxXQUFXLElBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQztZQUFDLElBQUksQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxFQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsRUFBRSxJQUFFLENBQUMsS0FBRyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSTtVQUFBO1FBQUM7TUFBQyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVztRQUFDLElBQUcsRUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxPQUFPLEVBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQztRQUFDLElBQUcsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFJO1VBQUMsSUFBRyxFQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxFQUFFLENBQUMsRUFBQyxLQUFLLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQTtRQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUc7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxLQUFLLENBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxFQUFFLENBQUMsbVBBQW1QLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFVBQVUsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxTQUFTLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUM7VUFBQyxJQUFHLENBQUMsR0FBQztZQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxXQUFXLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxvQkFBb0IsRUFBQyxDQUFDLENBQUMsV0FBVztVQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixFQUFDLElBQUksSUFBRSxDQUFDLEVBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQyxVQUFVLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsZUFBZSxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFFBQVEsR0FBQyxXQUFXLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxFQUFFLENBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLGdCQUFnQixJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLEVBQUUsQ0FBQyxXQUFXLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsZ0JBQWdCLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssTUFBSSxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQztRQUFBO1FBQUMsS0FBSSxFQUFFLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFdBQVcsRUFBQyxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsRUFBQyxDQUFDLENBQUMsSUFBRSxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLE1BQUksRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLGlCQUFpQixDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsS0FBSyxDQUFDLEdBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxjQUFjLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFdBQVcsRUFBQztNQUFDLFlBQVksRUFBQyxzQkFBc0I7TUFBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFPLEVBQUM7SUFBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsY0FBYyxFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxxQkFBcUIsRUFBQyxzQkFBc0IsRUFBQyx5QkFBeUIsRUFBQyx3QkFBd0IsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLEtBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDO01BQUMsU0FBUyxFQUFDLEVBQUUsQ0FBQyxpQkFBaUIsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsb0JBQW9CLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxxQkFBcUI7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsbUJBQW1CLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsbUJBQW1CLEtBQUcsS0FBSyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxpQkFBaUIsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxHQUFHLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztRQUFBO1FBQUMsT0FBTyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLFNBQVMsRUFBQztJQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsU0FBUyxFQUFDO0lBQUUsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGFBQWEsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsbUJBQW1CLEVBQUM7TUFBQyxZQUFZLEVBQUMsU0FBUztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGdCQUFnQixFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsb0JBQW9CLEVBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxZQUFZLEVBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUM7TUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLCtDQUErQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUM7TUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLG1EQUFtRDtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxNQUFNLEVBQUM7TUFBQyxZQUFZLEVBQUMsdUJBQXVCO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxFQUFDO01BQUMsWUFBWSxFQUFDLGtCQUFrQjtNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyx1QkFBdUIsRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFFBQVEsRUFBQztNQUFDLFlBQVksRUFBQyxnQkFBZ0I7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGdCQUFnQixFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxnQkFBZ0IsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxTQUFTLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxPQUFPLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsYUFBYSxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxtRUFBbUU7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsMkJBQTJCLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxVQUFVLElBQUcsQ0FBQyxHQUFDLFVBQVUsR0FBQyxZQUFZO1FBQUMsT0FBTyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLFFBQVEsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxHQUFHLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxVQUFVLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxnQkFBZ0IsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsaUJBQWlCLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLEVBQUUsQ0FBQyx5QkFBeUIsRUFBQztNQUFDLFlBQVksRUFBQyxHQUFHO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsV0FBVyxLQUFHLENBQUM7UUFBQyxPQUFNLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxnQkFBZ0IsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxRQUFRLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxRQUFRLEdBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLGNBQWMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztVQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxLQUFHLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQztRQUFBLENBQUMsTUFBSyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsS0FBRyxJQUFJLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLEVBQUUsQ0FBQyxXQUFXLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsSUFBRSxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTztRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsRUFBQyxFQUFFLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsY0FBYyxJQUFFLGFBQWEsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxLQUFLO1FBQUMsSUFBRyxLQUFLLEtBQUcsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLGlCQUFpQixLQUFHLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxLQUFHLEVBQUUsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQztNQUFBO0lBQUMsQ0FBQztJQUFDLEtBQUksRUFBRSxDQUFDLFlBQVksRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQywwQ0FBMEMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxFQUFFLEdBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLE9BQU0sQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxlQUFlO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxDQUFDLGNBQWMsRUFBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxFQUFDLEVBQUUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsMEJBQTBCLEVBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyx3QkFBd0IsS0FBRyxDQUFDLEdBQUMsU0FBUyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsV0FBVyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsSUFBRSxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQztNQUFBO01BQUMsSUFBRyxDQUFDLEVBQUM7UUFBQyxPQUFLLENBQUMsR0FBRTtVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUM7TUFBQTtNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsT0FBTyxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsYUFBYSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsTUFBSSxPQUFPLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLElBQUUsRUFBRSxHQUFDLEVBQUUsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLFdBQVcsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxJQUFFLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxLQUFLLElBQUUsQ0FBQyxHQUFDLEVBQUUsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLE1BQU0sS0FBRyxDQUFDLElBQUUsU0FBUyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLElBQUksS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtRQUFDLENBQUMsR0FBQyxJQUFJO01BQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUs7UUFBQyxJQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxZQUFZLEtBQUcsQ0FBQyxJQUFJLEVBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJO1lBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUk7Y0FBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSTtnQkFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7Z0JBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztjQUFBO1lBQUMsT0FBSSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUEsQ0FBQyxNQUFLLE9BQUssQ0FBQyxHQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFLLE9BQUssQ0FBQyxHQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsRUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBQSxFQUFVO01BQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLEtBQUssRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUM7TUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsS0FBSyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsS0FBSyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLE1BQU0sSUFBRSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsYUFBYTtNQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtRQUNseCtCLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7Ozs7O0FDWmhJO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsSUFBSSxDQUFDLEdBQUMsUUFBUSxDQUFDLGVBQWU7SUFBQyxDQUFDLEdBQUMsTUFBTTtJQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLEdBQUMsT0FBTyxHQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUk7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDO01BQUMsUUFBUSxFQUFDLFVBQVU7TUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLE9BQU8sRUFBQyxPQUFPO01BQUMsSUFBSSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUM7VUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksRUFBQyxHQUFHLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksRUFBQyxHQUFHLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsR0FBRyxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE9BQU8sQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtJQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxVQUFVO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtJQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxTQUFTO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0VBQUEsQ0FBQztBQUFBLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCJkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKFwiLmN1c3RvbS1zZWxlY3RcIikuZm9yRWFjaChjdXN0b21TZWxlY3QgPT4ge1xuXHRjb25zdCBzZWxlY3RCdG4gPSBjdXN0b21TZWxlY3QucXVlcnlTZWxlY3RvcihcIi5zZWxlY3QtYnV0dG9uXCIpO1xuXHRjb25zdCBzZWxlY3RlZFZhbHVlID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3IoXCIuc2VsZWN0ZWQtdmFsdWVcIik7XG5cdGNvbnN0IGhhbmRsZXIgPSBmdW5jdGlvbihlbG0pIHtcblx0XHRjb25zdCBjdXN0b21DaGFuZ2VFdmVudCA9IG5ldyBDdXN0b21FdmVudCgnY3VzdG9tLXNlbGVjdC1jaGFuZ2UnLCB7XG5cdFx0XHRkZXRhaWw6IHtcblx0XHRcdFx0c2VsZWN0ZWRPcHRpb246IGVsbVxuXHRcdFx0fVxuXHRcdH0pO1xuXHRcdHNlbGVjdGVkVmFsdWUudGV4dENvbnRlbnQgPSBlbG0udGV4dENvbnRlbnQ7XG5cdFx0Y3VzdG9tU2VsZWN0LmNsYXNzTGlzdC5yZW1vdmUoXCJhY3RpdmVcIik7XG5cdFx0Y3VzdG9tU2VsZWN0LmRpc3BhdGNoRXZlbnQoY3VzdG9tQ2hhbmdlRXZlbnQpO1xuXG5cdH1cblx0c2VsZWN0QnRuLmFkZEV2ZW50TGlzdGVuZXIoXCJjbGlja1wiLCAoKSA9PiB7XG5cdFx0Y3VzdG9tU2VsZWN0LmNsYXNzTGlzdC50b2dnbGUoXCJhY3RpdmVcIik7XG5cdFx0c2VsZWN0QnRuLnNldEF0dHJpYnV0ZShcblx0XHRcdFwiYXJpYS1leHBhbmRlZFwiLFxuXHRcdFx0c2VsZWN0QnRuLmdldEF0dHJpYnV0ZShcImFyaWEtZXhwYW5kZWRcIikgPT09IFwidHJ1ZVwiID8gXCJmYWxzZVwiIDogXCJ0cnVlXCJcblx0XHQpO1xuXHR9KTtcblxuXHRjdXN0b21TZWxlY3QuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0aWYgKGUudGFyZ2V0Lm1hdGNoZXMoJ2xhYmVsJykpIHtcblxuXHRcdFx0Y29uc3QgYWxsSXRlbXMgPSBjdXN0b21TZWxlY3QucXVlcnlTZWxlY3RvckFsbCgnbGknKTtcblx0XHRcdGFsbEl0ZW1zLmZvckVhY2goaXRlbSA9PiBpdGVtLmNsYXNzTGlzdC5yZW1vdmUoJ2FjdGl2ZScpKTtcblx0XHRcdGNvbnN0IGNsaWNrZWRQbGFuID0gZS50YXJnZXQuY2xvc2VzdCgnbGknKTtcblxuXHRcdFx0aWYgKGNsaWNrZWRQbGFuKSB7XG5cdFx0XHRcdGNsaWNrZWRQbGFuLmNsYXNzTGlzdC5hZGQoJ2FjdGl2ZScpO1xuXHRcdFx0XHRoYW5kbGVyKGNsaWNrZWRQbGFuKTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKFwiY2xpY2tcIiwgKGUpID0+IHtcblx0XHRpZiAoIWN1c3RvbVNlbGVjdC5jb250YWlucyhlLnRhcmdldCkpIHtcblx0XHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QucmVtb3ZlKFwiYWN0aXZlXCIpO1xuXHRcdFx0c2VsZWN0QnRuLnNldEF0dHJpYnV0ZShcImFyaWEtZXhwYW5kZWRcIiwgXCJmYWxzZVwiKTtcblx0XHR9XG5cdH0pO1xufSk7IiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuICAgIC8qKlxuICAgICAqIFJlZnJlc2ggTGljZW5zZSBkYXRhXG4gICAgICovXG4gICAgdmFyIF9pc1JlZnJlc2hpbmcgPSBmYWxzZTtcbiAgICAkKCcjd3ByLWFjdGlvbi1yZWZyZXNoX2FjY291bnQnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGlmKCFfaXNSZWZyZXNoaW5nKXtcbiAgICAgICAgICAgIHZhciBidXR0b24gPSAkKHRoaXMpO1xuICAgICAgICAgICAgdmFyIGFjY291bnQgPSAkKCcjd3ByLWFjY291bnQtZGF0YScpO1xuICAgICAgICAgICAgdmFyIGV4cGlyZSA9ICQoJyN3cHItZXhwaXJhdGlvbi1kYXRhJyk7XG5cbiAgICAgICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIF9pc1JlZnJlc2hpbmcgPSB0cnVlO1xuICAgICAgICAgICAgYnV0dG9uLnRyaWdnZXIoICdibHVyJyApO1xuXG5cdFx0XHQvLyBTdGFydCBwb2xsaW5nIGlmIG5vdCBhbHJlYWR5IHJ1bm5pbmcuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgIGV4cGlyZS5yZW1vdmVDbGFzcygnd3ByLWlzVmFsaWQgd3ByLWlzSW52YWxpZCcpO1xuXG4gICAgICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9yZWZyZXNoX2N1c3RvbWVyX2RhdGEnLFxuICAgICAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pc0hpZGRlbicpO1xuXG4gICAgICAgICAgICAgICAgICAgIGlmICggdHJ1ZSA9PT0gcmVzcG9uc2Uuc3VjY2VzcyApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGFjY291bnQuaHRtbChyZXNwb25zZS5kYXRhLmxpY2Vuc2VfdHlwZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICBleHBpcmUuYWRkQ2xhc3MocmVzcG9uc2UuZGF0YS5saWNlbnNlX2NsYXNzKS5odG1sKHJlc3BvbnNlLmRhdGEubGljZW5zZV9leHBpcmF0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaWNvbi1yZWZyZXNoIHdwci1pc0hpZGRlbicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWljb24tY2hlY2snKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDI1MCk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaWNvbi1yZWZyZXNoIHdwci1pc0hpZGRlbicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWljb24tY2xvc2UnKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDI1MCk7XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoe29uQ29tcGxldGU6ZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBfaXNSZWZyZXNoaW5nID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgICAgICB9fSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonKz13cHItaXNIaWRkZW4nfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWljb24tY2hlY2snfX0sIDAuMjUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWljb24tY2xvc2UnfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jys9d3ByLWljb24tcmVmcmVzaCd9fSwgMC4yNSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonLT13cHItaXNIaWRkZW4nfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICA7XG4gICAgICAgICAgICAgICAgICAgIH0sIDIwMDApO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogU2F2ZSBUb2dnbGUgb3B0aW9uIHZhbHVlcyBvbiBjaGFuZ2VcbiAgICAgKi9cbiAgICAkKCcud3ByLXJhZGlvIGlucHV0W3R5cGU9Y2hlY2tib3hdJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICB2YXIgbmFtZSAgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG4gICAgICAgIHZhciB2YWx1ZSA9ICQodGhpcykucHJvcCgnY2hlY2tlZCcpID8gMSA6IDA7XG5cblx0XHR2YXIgZXhjbHVkZWQgPSBbICdjbG91ZGZsYXJlX2F1dG9fc2V0dGluZ3MnLCAnY2xvdWRmbGFyZV9kZXZtb2RlJywgJ2FuYWx5dGljc19lbmFibGVkJyBdO1xuXHRcdGlmICggZXhjbHVkZWQuaW5kZXhPZiggbmFtZSApID49IDAgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X3RvZ2dsZV9vcHRpb24nLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuICAgICAgICAgICAgICAgIG9wdGlvbjoge1xuICAgICAgICAgICAgICAgICAgICBuYW1lOiBuYW1lLFxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTogdmFsdWVcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgZnVuY3Rpb24ocmVzcG9uc2UpIHt9XG4gICAgICAgICk7XG5cdH0pO1xuXG5cdC8qKlxuICAgICAqIFNhdmUgZW5hYmxlIENQQ1NTIGZvciBtb2JpbGVzIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIENQQ1NTIGJ0biBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItaGlkZS1vbi1jbGljaycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLXNob3ctb24tY2xpY2snKS5zaG93KCk7XG5cdFx0XHRcdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG5cbiAgICAvKipcbiAgICAgKiBTYXZlIGVuYWJsZSBHb29nbGUgRm9udHMgT3B0aW1pemF0aW9uIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIENQQ1NTIGJ0biBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItaGlkZS1vbi1jbGljaycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLXNob3ctb24tY2xpY2snKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI21pbmlmeV9nb29nbGVfZm9udHMnKS52YWwoMSk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9KTtcblxuICAgICQoICcjcm9ja2V0LWRpc21pc3MtcHJvbW90aW9uJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2Rpc21pc3NfcHJvbW8nLFxuICAgICAgICAgICAgICAgIG5vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdCQoJyNyb2NrZXQtcHJvbW8tYmFubmVyJykuaGlkZSggJ3Nsb3cnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9ICk7XG5cbiAgICAkKCAnI3JvY2tldC1kaXNtaXNzLXJlbmV3YWwnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZGlzbWlzc19yZW5ld2FsJyxcbiAgICAgICAgICAgICAgICBub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQkKCcjcm9ja2V0LXJlbmV3YWwtYmFubmVyJykuaGlkZSggJ3Nsb3cnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9ICk7XG5cdCQoICcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbGlzdCcgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCQoJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1tc2cnKS5odG1sKCcnKTtcblx0XHQkLmFqYXgoe1xuXHRcdFx0dXJsOiByb2NrZXRfYWpheF9kYXRhLnJlc3RfdXJsLFxuXHRcdFx0YmVmb3JlU2VuZDogZnVuY3Rpb24gKCB4aHIgKSB7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnWC1XUC1Ob25jZScsIHJvY2tldF9hamF4X2RhdGEucmVzdF9ub25jZSApO1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ0FjY2VwdCcsICdhcHBsaWNhdGlvbi9qc29uLCAqLyo7cT0wLjEnICk7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnQ29udGVudC1UeXBlJywgJ2FwcGxpY2F0aW9uL2pzb24nICk7XG5cdFx0XHR9LFxuXHRcdFx0bWV0aG9kOiBcIlBVVFwiLFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2VzKSB7XG5cdFx0XHRcdGxldCBleGNsdXNpb25fbXNnX2NvbnRhaW5lciA9ICQoJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1tc2cnKTtcblx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuaHRtbCgnJyk7XG5cdFx0XHRcdGlmICggdW5kZWZpbmVkICE9PSByZXNwb25zZXNbJ3N1Y2Nlc3MnXSApIHtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8ZGl2IGNsYXNzPVwibm90aWNlIG5vdGljZS1lcnJvclwiPicgKyByZXNwb25zZXNbJ21lc3NhZ2UnXSArICc8L2Rpdj4nICk7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cdFx0XHRcdE9iamVjdC5rZXlzKCByZXNwb25zZXMgKS5mb3JFYWNoKCggcmVzcG9uc2Vfa2V5ICkgPT4ge1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxzdHJvbmc+JyArIHJlc3BvbnNlX2tleSArICc6IDwvc3Ryb25nPicgKTtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoIHJlc3BvbnNlc1tyZXNwb25zZV9rZXldWydtZXNzYWdlJ10gKTtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8YnI+JyApO1xuXHRcdFx0XHR9KTtcblx0XHRcdH1cblx0XHR9KTtcblx0fSApO1xuXG4gICAgLyoqXG4gICAgICogRW5hYmxlIG1vYmlsZSBjYWNoZSBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9tb2JpbGVfY2FjaGUnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIGNhY2hlIGVuYWJsZSBidXR0b24gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnI3dwcl9tb2JpbGVfY2FjaGVfZGVmYXVsdCcpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcjd3ByX21vYmlsZV9jYWNoZV9yZXNwb25zZScpLnNob3coKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAgICAgICAgICAgICAvLyBTZXQgdmFsdWVzIG9mIG1vYmlsZSBjYWNoZSBhbmQgc2VwYXJhdGUgY2FjaGUgZmlsZXMgZm9yIG1vYmlsZXMgb3B0aW9uIHRvIDEuXG4gICAgICAgICAgICAgICAgICAgICQoJyNjYWNoZV9tb2JpbGUnKS52YWwoMSk7XG4gICAgICAgICAgICAgICAgICAgICQoJyNkb19jYWNoaW5nX21vYmlsZV9maWxlcycpLnZhbCgxKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xufSk7XG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbigpIHtcblx0Y29uc3QgYW5hbHl0aWNzQ2hlY2tib3ggPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnYW5hbHl0aWNzX2VuYWJsZWQnKTtcblxuXHRpZiAoYW5hbHl0aWNzQ2hlY2tib3gpIHtcblx0XHRhbmFseXRpY3NDaGVja2JveC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGlzQ2hlY2tlZCA9IHRoaXMuY2hlY2tlZDtcblxuXHRcdFx0ZmV0Y2goYWpheHVybCwge1xuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0aGVhZGVyczoge1xuXHRcdFx0XHRcdCdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJyxcblx0XHRcdFx0fSxcblx0XHRcdFx0Ym9keTogbmV3IFVSTFNlYXJjaFBhcmFtcyh7XG5cdFx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3RvZ2dsZV9vcHRpbicsXG5cdFx0XHRcdFx0dmFsdWU6IGlzQ2hlY2tlZCA/IDEgOiAwLFxuXHRcdFx0XHRcdF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuXHRcdFx0XHR9KVxuXHRcdFx0fSk7XG5cdFx0fSk7XG5cdH1cbn0pO1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgZnVuY3Rpb24oKSB7XG5cdC8qKlxuXHQgKiBQZXJmb3JtYW5jZSBNb25pdG9yaW5nIHdpdGggUHJvZ3Jlc3NpdmUgUG9sbGluZy5cblx0ICovXG5cblx0XHQvLyA9PT09IENvbmZpZ3VyYXRpb24gPT09PVxuXHRjb25zdCBQT0xMX0JBU0VfSU5URVJWQUwgPSAyMDAwOyAgIC8vIFN0YXJ0IHBvbGxpbmcgYXQgMiBzZWNvbmRzXG5cdGNvbnN0IFBPTExfTUFYX0lOVEVSVkFMID0gNTAwMDsgICAvLyBNYXggcG9sbGluZyBpbnRlcnZhbCAoNSBzZWNvbmRzKVxuXG5cdC8vID09PT0gU3RhdGUgPT09PVxuXHRsZXQgcm9ja2V0SW5zaWdodHNJZHMgPSBBcnJheS5pc0FycmF5KHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5yb2NrZXRfaW5zaWdodHNfaWRzKSA/IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhLnJvY2tldF9pbnNpZ2h0c19pZHMuc2xpY2UoKSA6IFtdO1xuXHRsZXQgcG9sbEludGVydmFsID0gUE9MTF9CQVNFX0lOVEVSVkFMO1xuXHRsZXQgcG9sbFRpbWVyID0gbnVsbDtcblx0bGV0IGhhc0NyZWRpdCA9IHRydWU7IC8vIFRyYWNrIGNyZWRpdCBzdGF0dXNcbiAgICBsZXQgZ2xvYmFsU2NvcmVEYXRhID0ge1xuICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICBzdGF0dXM6ICcnLFxuICAgICAgICAgICAgc2NvcmU6IDAsXG4gICAgICAgICAgICBwYWdlc19udW06IDBcbiAgICAgICAgfSxcbiAgICAgICAgaHRtbDogJycsXG4gICAgICAgIHJvd19odG1sOiAnJyxcblx0XHRkaXNhYmxlZF9idG5faHRtbDoge1xuXHRcdFx0Z2xvYmFsX3Njb3JlX3dpZGdldDogJycsXG5cdFx0XHRyb2NrZXRfaW5zaWdodHM6ICcnXG5cdFx0fVxuICAgIH07XG5cbiAgICAvLyBJbml0aWFsaXplIGdsb2JhbFNjb3JlRGF0YSBmcm9tIGxvY2FsaXplZCBzY3JpcHQgZGF0YSBpZiBhdmFpbGFibGVcbiAgICBpZiAod2luZG93LnJvY2tldF9hamF4X2RhdGE/Lmdsb2JhbF9zY29yZV9kYXRhKSB7XG4gICAgICAgIGdsb2JhbFNjb3JlRGF0YSA9IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhLmdsb2JhbF9zY29yZV9kYXRhO1xuICAgIH1cblxuXHQvLyA9PT09IERPTSBTZWxlY3RvcnMgPT09PVxuXHRjb25zdCAkcGFnZVVybElucHV0ID0gJCgnI3dwci1zcGVlZC1yYWRhci11cmwtaW5wdXQnKTtcblx0Y29uc3QgJHRhYmxlQm9keSA9ICQoJy53cHItcmktdXJscy10YWJsZSB0Ym9keScpO1xuXHRjb25zdCAkdGFibGUgPSAkKCcud3ByLXJpLXVybHMtdGFibGUnKTtcblxuXHQvLyA9PT09IFV0aWxpdHkgRnVuY3Rpb25zID09PT1cblx0ZnVuY3Rpb24gaXNWYWxpZFVybChpbnB1dCkge1xuXHRcdHRyeSB7XG5cdFx0XHRjb25zdCB1cmwgPSBuZXcgVVJMKGlucHV0KTtcblx0XHRcdHJldHVybiB1cmwuaG9zdG5hbWUuaW5jbHVkZXMoJy4nKSAmJiB1cmwuaG9zdG5hbWUuc3BsaXQoJy4nKS5wb3AoKS5sZW5ndGggPiAwO1xuXHRcdH0gY2F0Y2gge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIGFkZElkcyhuZXdJZCkge1xuXHRcdGlmICghcm9ja2V0SW5zaWdodHNJZHMuaW5jbHVkZXMobmV3SWQpKSB7XG5cdFx0XHRyb2NrZXRJbnNpZ2h0c0lkcy5wdXNoKG5ld0lkKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiByZW1vdmVJZChpZCkge1xuXHRcdHJvY2tldEluc2lnaHRzSWRzID0gcm9ja2V0SW5zaWdodHNJZHMuZmlsdGVyKHggPT4geCAhPT0gcGFyc2VJbnQoaWQsIDEwKSk7XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVRdW90YUJhbm5lcihjYW5BZGRQYWdlcykge1xuXHRcdGNvbnN0ICRzdW1tYXJ5SW5mbyAgICA9ICQoJy53cHItcmktc3VtbWFyeS1pbmZvJyk7XG5cdFx0Y29uc3QgaXNGcmVlICA9IHdpbmRvdy5yb2NrZXRfYWpheF9kYXRhPy5pc19mcmVlID09PSAnMSc7XG5cdFx0Y29uc3QgJHF1b3RhQmFubmVyID0gaXNGcmVlID8gJCgnI3dwci1yaS1xdW90YS1iYW5uZXInKSA6ICQoJyNyb2NrZXRfaW5zaWdodHNfc3VydmV5Jyk7XG5cblx0XHQvLyBTaG93IGJhbm5lciBpZiBVUkwgbGltaXQgcmVhY2hlZCBPUiBubyBjcmVkaXRzIGxlZnQgKG1hdGNoaW5nIFBIUCBsb2dpYyBpbiBTdWJzY3JpYmVyLnBocCBsaW5lIDM5OCkuXG5cdFx0Y29uc3Qgc2hvdWxkU2hvd0Jhbm5lciA9IGNhbkFkZFBhZ2VzID09PSBmYWxzZSB8fCAhaGFzQ3JlZGl0O1xuXG5cdFx0aWYgKHNob3VsZFNob3dCYW5uZXIpIHtcblx0XHRcdCRzdW1tYXJ5SW5mby5oaWRlKCk7XG5cdFx0XHQkcXVvdGFCYW5uZXIucmVtb3ZlQ2xhc3MoJ2hpZGRlbicpO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHQkc3VtbWFyeUluZm8uc2hvdygpO1xuXHRcdFx0JHF1b3RhQmFubmVyLmFkZENsYXNzKCdoaWRkZW4nKTtcblx0XHR9XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVDcmVkaXRTdGF0ZShyZXNwb25zZUhhc0NyZWRpdCkge1xuXHRcdGlmIChyZXNwb25zZUhhc0NyZWRpdCAhPT0gdW5kZWZpbmVkICYmIGhhc0NyZWRpdCAhPT0gcmVzcG9uc2VIYXNDcmVkaXQpIHtcblx0XHRcdGhhc0NyZWRpdCA9IHJlc3BvbnNlSGFzQ3JlZGl0O1xuXG5cdFx0XHQvLyBVcGRhdGUgYWxsIHJldGVzdCBidXR0b25zIHdoZW4gY3JlZGl0IHN0YXR1cyBjaGFuZ2VzXG5cdFx0XHR1cGRhdGVBbGxSZXRlc3RCdXR0b25zKCk7XG5cdFx0fVxuXHR9XG5cblx0ZnVuY3Rpb24gdXBkYXRlQWxsUmV0ZXN0QnV0dG9ucygpIHtcblx0XHRjb25zdCByZXRlc3RCdXR0b25zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1hY3Rpb24tc3BlZWRfcmFkYXJfcmVmcmVzaCcpO1xuXG5cdFx0cmV0ZXN0QnV0dG9ucy5mb3JFYWNoKGJ1dHRvbiA9PiB7XG5cdFx0XHRjb25zdCByb3cgPSBidXR0b24uY2xvc2VzdCgnLndwci1yaS1pdGVtJyk7XG5cdFx0XHRpZiAoIXJvdykgcmV0dXJuO1xuXG5cdFx0XHQvLyBHZXQgdGhlIHJvdyBJRCBhbmQgY2hlY2sgaWYgaXQncyBjdXJyZW50bHkgYmVpbmcgcHJvY2Vzc2VkXG5cdFx0XHRjb25zdCByb3dJZCA9IHBhcnNlSW50KHJvdy5kYXRhc2V0LnJvY2tldEluc2lnaHRzSWQsIDEwKTtcblx0XHRcdGNvbnN0IGlzUnVubmluZyA9IHJvY2tldEluc2lnaHRzSWRzLmluY2x1ZGVzKHJvd0lkKTtcblxuXHRcdFx0aWYgKCFoYXNDcmVkaXQgfHwgaXNSdW5uaW5nKSB7XG5cdFx0XHRcdC8vIERpc2FibGUgYnV0dG9uXG5cdFx0XHRcdGJ1dHRvbi5jbGFzc0xpc3QuYWRkKCd3cHItcmktYWN0aW9uLS1kaXNhYmxlZCcpO1xuXHRcdFx0XHRidXR0b24uc2V0QXR0cmlidXRlKCdkaXNhYmxlZCcsICd0cnVlJyk7XG5cblx0XHRcdFx0aWYgKCFoYXNDcmVkaXQpIHtcblx0XHRcdFx0XHQvLyBBZGQgdG9vbHRpcCBmb3Igbm8gY3JlZGl0XG5cdFx0XHRcdFx0YnV0dG9uLmNsYXNzTGlzdC5hZGQoJ3dwci1idG4td2l0aC10b29sLXRpcCcpO1xuXHRcdFx0XHRcdGJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ3RpdGxlJywgd2luZG93LnJvY2tldF9hamF4X2RhdGE/LnJvY2tldF9pbnNpZ2h0c19ub19jcmVkaXRfdG9vbHRpcCB8fCAnVXBncmFkZSB5b3VyIHBsYW4gdG8gZ2V0IGFjY2VzcyB0byByZS10ZXN0IHBlcmZvcm1hbmNlIG9yIHJ1biBuZXcgdGVzdHMnKTtcblx0XHRcdFx0fVxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Ly8gRW5hYmxlIGJ1dHRvblxuXHRcdFx0XHRidXR0b24uY2xhc3NMaXN0LnJlbW92ZSgnd3ByLXJpLWFjdGlvbi0tZGlzYWJsZWQnLCAnd3ByLWJ0bi13aXRoLXRvb2wtdGlwJyk7XG5cdFx0XHRcdGJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2Rpc2FibGVkJyk7XG5cdFx0XHRcdGJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ3RpdGxlJyk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHRmdW5jdGlvbiByZXNldFBvbGxpbmcoKSB7XG5cdFx0aWYgKHBvbGxUaW1lcikge1xuXHRcdFx0Y2xlYXJUaW1lb3V0KHBvbGxUaW1lcik7XG5cdFx0XHRwb2xsVGltZXIgPSBudWxsO1xuXHRcdH1cblx0XHRwb2xsSW50ZXJ2YWwgPSBQT0xMX0JBU0VfSU5URVJWQUw7XG5cdH1cblxuXHRmdW5jdGlvbiBzY2hlZHVsZVBvbGxpbmcoKSB7XG5cdFx0aWYgKHJvY2tldEluc2lnaHRzSWRzLmxlbmd0aCA+IDApIHtcblx0XHRcdHBvbGxUaW1lciA9IHNldFRpbWVvdXQoKCkgPT4ge1xuXHRcdFx0XHRnZXRSZXN1bHRzKCk7XG5cdFx0XHR9LCBwb2xsSW50ZXJ2YWwpO1xuXHRcdH1cblx0fVxuXG5cdGZ1bmN0aW9uIGluY3JlbWVudFBvbGxpbmcoKSB7XG5cdFx0cG9sbEludGVydmFsID0gTWF0aC5taW4ocG9sbEludGVydmFsICogMS4zLCBQT0xMX01BWF9JTlRFUlZBTCk7IC8vIEV4cG9uZW50aWFsIGJhY2tvZmZcblx0fVxuXG4gICAgZnVuY3Rpb24gaXNPbkRhc2hib2FyZCgpIHtcbiAgICAgICAgY29uc3QgdXJsUGFyYW1zID0gbmV3IFVSTFNlYXJjaFBhcmFtcyh3aW5kb3cubG9jYXRpb24uc2VhcmNoKTtcbiAgICAgICAgcmV0dXJuIHVybFBhcmFtcy5nZXQoJ3BhZ2UnKSA9PT0gJ3dwcm9ja2V0JyAmJiB3aW5kb3cubG9jYXRpb24uaGFzaCA9PT0gJyNkYXNoYm9hcmQnO1xuICAgIH1cblxuXHRmdW5jdGlvbiBpc09uUm9ja2V0SW5zaWdodHMoKSB7XG5cdFx0Y29uc3QgdXJsUGFyYW1zID0gbmV3IFVSTFNlYXJjaFBhcmFtcyh3aW5kb3cubG9jYXRpb24uc2VhcmNoKTtcblx0XHRyZXR1cm4gdXJsUGFyYW1zLmdldCgncGFnZScpID09PSAnd3Byb2NrZXQnICYmIHdpbmRvdy5sb2NhdGlvbi5oYXNoID09PSAnI3JvY2tldF9pbnNpZ2h0cyc7XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVHbG9iYWxTY29yZVJvdyhnbG9iYWxTY29yZURhdGEpe1xuXHRcdGlmICggaXNPblJvY2tldEluc2lnaHRzKCkgKSB7XG5cdFx0XHRjb25zdCAkdGFibGVHbG9iYWxTY29yZSA9ICQoJy53cHItcmktdXJscy10YWJsZSAud3ByLWdsb2JhbC1zY29yZScpO1xuXHRcdFx0aWYgKCR0YWJsZUdsb2JhbFNjb3JlLmxlbmd0aCA+IDApe1xuXHRcdFx0XHQkdGFibGVHbG9iYWxTY29yZS5yZXBsYWNlV2l0aChnbG9iYWxTY29yZURhdGEucm93X2h0bWwpO1xuXHRcdFx0fWVsc2Uge1xuXHRcdFx0XHQkdGFibGVCb2R5LnByZXBlbmQoZ2xvYmFsU2NvcmVEYXRhLnJvd19odG1sKTtcblx0XHRcdH1cblx0XHR9XG5cdH1cblxuXHQvKipcblx0ICogVXBkYXRlcyB0aGUgZ2xvYmFsIHNjb3JlIFVJIHdpZGdldCBvciB0YWJsZSByb3cgYmFzZWQgb24gdGhlIHNlbGVjdGVkIG1lbnUuXG5cdCAqIFdoZW4gdGhlIGRhc2hib2FyZCBvciByb2NrZXQgaW5zaWdodHMgbWVudSBpcyBjbGlja2VkLCB0aGlzIGZ1bmN0aW9uIHVwZGF0ZXNcblx0ICogdGhlIGNvcnJlc3BvbmRpbmcgZ2xvYmFsIHNjb3JlIGRpc3BsYXkgYWZ0ZXIgYSBzaG9ydCBkZWxheS5cblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IGlkIC0gVGhlIElEIG9mIHRoZSBjbGlja2VkIG1lbnUgaXRlbS5cblx0ICovXG5cdGZ1bmN0aW9uIGRlY2lkZUdsb2JhbFNjb3JlVG9VcGRhdGUoaWQpIHtcblx0XHQvLyBEZWxheSBVSSB1cGRhdGUgYSBiaXQgdGlsbCBlbGVtZW50IGlzIHZpc2libGUuXG5cdFx0c2V0VGltZW91dCgoKSA9PiB7XG5cdFx0XHRzd2l0Y2ggKGlkKSB7XG5cdFx0XHRcdC8vIEhhbmRsZSBhY3Rpb24gd2hlbiBkYXNoYm9hcmQgbWVudSBpcyBjbGlja2VkLlxuXHRcdFx0XHRjYXNlICd3cHItbmF2LWRhc2hib2FyZCc6XG5cblx0XHRcdFx0XHRpZiAoJycgPT09IGdsb2JhbFNjb3JlRGF0YS5odG1sKSB7XG5cdFx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdGxldCBnbG9iYWxTY29yZVdpZGdldCA9ICQoJyN3cHJfZ2xvYmFsX3Njb3JlX3dpZGdldCcpO1xuXG5cdFx0XHRcdFx0aWYgKCFnbG9iYWxTY29yZVdpZGdldC5pcygnOnZpc2libGUnKSkge1xuXHRcdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgd2lkZ2V0LlxuXHRcdFx0XHRcdGdsb2JhbFNjb3JlV2lkZ2V0Lmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmh0bWwpO1xuXG5cdFx0XHRcdFx0Ly8gRGlzYWJsZSBcIkFkZCBQYWdlc1wiIGJ1dHRvbiBvbiBnbG9iYWwgc2NvcmUgd2lkZ2V0LlxuXHRcdFx0XHRcdGlmICghKCdkaXNhYmxlZF9idG5faHRtbCcgaW4gZ2xvYmFsU2NvcmVEYXRhKSkge1xuXHRcdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdCQoJyN3cHJfZ2xvYmFsX3Njb3JlX3dpZGdldF9hZGRfcGFnZV9idG5fd3JhcHBlcicpLmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmRpc2FibGVkX2J0bl9odG1sLmdsb2JhbF9zY29yZV93aWRnZXQpO1xuXHRcdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHRcdC8vIEhhbmRsZSBhY3Rpb24gd2hlbiByb2NrZXQgaW5zaWdodHMgbWVudSBpcyBjbGlja2VkLlxuXHRcdFx0XHRjYXNlICd3cHItbmF2LXJvY2tldF9pbnNpZ2h0cyc6XG5cblx0XHRcdFx0XHRpZiAoJycgPT09IGdsb2JhbFNjb3JlRGF0YS5yb3dfaHRtbCkge1xuXHRcdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdHVwZGF0ZUdsb2JhbFNjb3JlUm93KGdsb2JhbFNjb3JlRGF0YSk7XG5cdFx0XHRcdFx0YnJlYWs7XG5cdFx0XHR9XG5cdFx0fSwgMzApO1xuXHR9XG5cblx0Ly8gPT09PSBBSkFYIEhhbmRsZXJzID09PT1cblx0ZnVuY3Rpb24gZ2V0UmVzdWx0cygpIHtcblx0XHRpZiAocm9ja2V0SW5zaWdodHNJZHMubGVuZ3RoID09PSAwKSB7XG5cdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6IHdpbmRvdy53cC51cmwuYWRkUXVlcnlBcmdzKCAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvcHJvZ3Jlc3MnLCB7IGlkczogcm9ja2V0SW5zaWdodHNJZHMgfSApLFxuXHRcdFx0fVxuXHRcdCkudGhlbiggKCByZXNwb25zZSApID0+IHtcblx0XHRcdGlmIChyZXNwb25zZS5zdWNjZXNzICYmIEFycmF5LmlzQXJyYXkocmVzcG9uc2UucmVzdWx0cykpIHtcblx0XHRcdFx0Ly8gVXBkYXRlIGNyZWRpdCBzdGF0dXNcblx0XHRcdFx0dXBkYXRlQ3JlZGl0U3RhdGUocmVzcG9uc2UuaGFzX2NyZWRpdCk7XG5cblx0XHRcdFx0Ly8gVXBkYXRlIHF1b3RhIGJhbm5lciB2aXNpYmlsaXR5XG5cdFx0XHRcdHVwZGF0ZVF1b3RhQmFubmVyKHJlc3BvbnNlLmNhbl9hZGRfcGFnZXMpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YSBhbmQgd2lkZ2V0IHdoZW4gc3RhdHVzIHx8IHBhZ2UgY291bnQgY2hhbmdlcy5cblx0XHRcdFx0aWYgKGdsb2JhbFNjb3JlRGF0YS5kYXRhLnN0YXR1cyAhPT0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuZGF0YS5zdGF0dXMgfHwgZ2xvYmFsU2NvcmVEYXRhLmRhdGEucGFnZXNfbnVtICE9PSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YS5kYXRhLnBhZ2VzX251bSkge1xuXHRcdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YS5cblx0XHRcdFx0XHRnbG9iYWxTY29yZURhdGEgPSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YTtcblxuXHRcdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgd2lkZ2V0IGlmIG9uIGRhc2hib2FyZC5cblx0XHRcdFx0XHRpZiAoIGlzT25EYXNoYm9hcmQoKSApIHtcblx0XHRcdFx0XHRcdCQoJyN3cHJfZ2xvYmFsX3Njb3JlX3dpZGdldCcpLmh0bWwocmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGEuaHRtbCk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgcm93IGluIHRhYmxlIGlmIG9uIFJvY2tldCBJbnNpZ2h0cyBwYWdlLlxuXHRcdFx0XHRcdHVwZGF0ZUdsb2JhbFNjb3JlUm93KGdsb2JhbFNjb3JlRGF0YSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0cmVzcG9uc2UucmVzdWx0cy5mb3JFYWNoKHJlc3VsdCA9PiB7XG5cdFx0XHRcdFx0Y29uc3QgJHJvdyA9ICQoYFtkYXRhLXJvY2tldC1pbnNpZ2h0cy1pZD1cIiR7cmVzdWx0LmlkfVwiXWApO1xuXHRcdFx0XHRcdCRyb3cucmVwbGFjZVdpdGgocmVzdWx0Lmh0bWwpO1xuXG5cdFx0XHRcdFx0aWYgKHJlc3VsdC5zdGF0dXMgPT09ICdjb21wbGV0ZWQnIHx8IHJlc3VsdC5zdGF0dXMgPT09ICdmYWlsZWQnKSB7XG5cdFx0XHRcdFx0XHRyZW1vdmVJZChyZXN1bHQuaWQpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSk7XG5cblx0XHRcdFx0aW5jcmVtZW50UG9sbGluZygpO1xuXHRcdFx0XHRzY2hlZHVsZVBvbGxpbmcoKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIE9uIGVycm9yLCBjbGVhciBJRHMgYW5kIHN0b3AgcG9sbGluZ1xuXHRcdFx0XHRyb2NrZXRJbnNpZ2h0c0lkcyA9IFtdO1xuXHRcdFx0XHRyZXNldFBvbGxpbmcoKTtcblx0XHRcdFx0Y29uc29sZS5lcnJvcignUG9sbGluZyBlcnJvcjonLCByZXNwb25zZS5yZXN1bHRzIHx8IHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9ICk7XG5cdH1cblxuXHRmdW5jdGlvbiBoYW5kbGVBZGRQYWdlKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQvLyBjaGVjayBpZiBoYXMgYXR0ciBkaXNhYmxlZFxuXHRcdGlmICgkKHRoaXMpLmF0dHIoJ2Rpc2FibGVkJykpIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBwYWdlVXJsID0gJHBhZ2VVcmxJbnB1dC52YWwoKS50cmltKCk7XG5cblx0XHRpZiAoIWlzVmFsaWRVcmwocGFnZVVybCkpIHtcblx0XHRcdGFsZXJ0KCdQbGVhc2UgZW50ZXIgYSB2YWxpZCBVUkwnKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR3aW5kb3cud3AuYXBpRmV0Y2goXG5cdFx0XHR7XG5cdFx0XHRcdHBhdGg6ICcvd3Atcm9ja2V0L3YxL3JvY2tldC1pbnNpZ2h0cy9wYWdlcy8nLFxuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0ZGF0YToge1xuXHRcdFx0XHRcdHBhZ2VfdXJsOiBwYWdlVXJsXG5cdFx0XHRcdH0sXG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcblx0XHRcdFx0JHBhZ2VVcmxJbnB1dC52YWwoJycpO1xuXHRcdFx0XHQkdGFibGVCb2R5LmFwcGVuZChyZXNwb25zZS5odG1sKTtcblx0XHRcdFx0JHRhYmxlLnJlbW92ZUNsYXNzKCdoaWRkZW4nKTtcblx0XHRcdFx0YWRkSWRzKHJlc3BvbnNlLmlkKTtcblx0XHRcdFx0bGV0IHBhZ2VzX251bV9jb250YWluZXIgPSAkKCcjcm9ja2V0X3JvY2tldF9pbnNpZ2h0c19wYWdlc19udW0nKTtcblx0XHRcdFx0cGFnZXNfbnVtX2NvbnRhaW5lci50ZXh0KCBwYXJzZUludCggcGFnZXNfbnVtX2NvbnRhaW5lci50ZXh0KCkgKSArIDEgKTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgY3JlZGl0IHN0YXR1c1xuXHRcdFx0XHR1cGRhdGVDcmVkaXRTdGF0ZShyZXNwb25zZS5oYXNfY3JlZGl0KTtcblxuICAgICAgICAgICAgICAgIC8vIFVwZGF0ZSBnbG9iYWwgc2NvcmUgZGF0YS5cbiAgICAgICAgICAgICAgICBnbG9iYWxTY29yZURhdGEgPSByZXNwb25zZS5nbG9iYWxfc2NvcmVfZGF0YTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIHJvdyBpbiB0YWJsZSBpZiBvbiBSb2NrZXQgSW5zaWdodHMgcGFnZS5cblx0XHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblxuXHRcdFx0XHRpZiAoJ2Rpc2FibGVkX2J0bl9odG1sJyBpbiBnbG9iYWxTY29yZURhdGEpIHtcblx0XHRcdFx0XHQkKCcjd3ByX3JvY2tldF9pbnNpZ2h0c19hZGRfcGFnZV9idG5fd3JhcHBlcicpLmh0bWwoZ2xvYmFsU2NvcmVEYXRhLmRpc2FibGVkX2J0bl9odG1sLnJvY2tldF9pbnNpZ2h0cyk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHQvLyBTaG93L2hpZGUgcXVvdGEgYmFubmVyIGJhc2VkIG9uIGNhbl9hZGRfcGFnZXNcblx0XHRcdFx0dXBkYXRlUXVvdGFCYW5uZXIocmVzcG9uc2UuY2FuX2FkZF9wYWdlcyk7XG5cblx0XHRcdFx0Ly8gU3RhcnQgcG9sbGluZyBpZiBub3QgYWxyZWFkeSBydW5uaW5nXG5cdFx0XHRcdGlmICghcG9sbFRpbWVyKSB7XG5cdFx0XHRcdFx0cG9sbEludGVydmFsID0gUE9MTF9CQVNFX0lOVEVSVkFMO1xuXHRcdFx0XHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHRcdFx0XHR9XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQvLyBDbGVhciB0aGUgaW5wdXQgZmllbGQgb24gZXJyb3Jcblx0XHRcdFx0JHBhZ2VVcmxJbnB1dC52YWwoJycpO1xuXG5cdFx0XHRcdC8vIEhhbmRsZSBVUkwgbGltaXQgcmVhY2hlZCBlcnJvclxuXHRcdFx0XHRpZiAocmVzcG9uc2U/Lm1lc3NhZ2UgJiYgcmVzcG9uc2UubWVzc2FnZS5pbmNsdWRlcygnTWF4aW11bSBudW1iZXIgb2YgVVJMcyByZWFjaGVkJykpIHtcblx0XHRcdFx0XHQvLyBVcGRhdGUgVUkgc3RhdGUgdG8gcmVmbGVjdCBVUkwgbGltaXQgaGFzIGJlZW4gcmVhY2hlZFxuXHRcdFx0XHRcdGRpc2FibGVBZGRVcmxFbGVtZW50cygpO1xuXHRcdFx0XHRcdC8vIFNob3cgcXVvdGEgYmFubmVyIChjYW5fYWRkX3BhZ2VzID0gZmFsc2UpXG5cdFx0XHRcdFx0dXBkYXRlUXVvdGFCYW5uZXIocmVzcG9uc2UuY2FuX2FkZF9wYWdlcyAhPT0gdW5kZWZpbmVkID8gcmVzcG9uc2UuY2FuX2FkZF9wYWdlcyA6IGZhbHNlKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGNvbnNvbGUuZXJyb3IocmVzcG9uc2U/Lm1lc3NhZ2UgfHwgcmVzcG9uc2UpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0ZnVuY3Rpb24gaGFuZGxlUmVzZXRQYWdlKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHRjb25zdCAkYnV0dG9uID0gJCh0aGlzKTtcblx0XHRsZXQgaWQgPSAkYnV0dG9uLnBhcmVudHMoJy53cHItcmktaXRlbScpLmRhdGEoJ3JvY2tldC1pbnNpZ2h0cy1pZCcpO1xuXHRcdGlmICggISBpZCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBzb3VyY2UgPSAkYnV0dG9uLmRhdGEoJ3NvdXJjZScpO1xuXG5cdFx0d2luZG93LndwLmFwaUZldGNoKFxuXHRcdFx0e1xuXHRcdFx0XHRwYXRoOiAnL3dwLXJvY2tldC92MS9yb2NrZXQtaW5zaWdodHMvcGFnZXMvJyArIGlkLFxuXHRcdFx0XHRtZXRob2Q6ICdQQVRDSCcsXG5cdFx0XHRcdGRhdGE6IHtcblx0XHRcdFx0XHRzb3VyY2U6IHNvdXJjZVxuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0KS50aGVuKCAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcblx0XHRcdFx0YWRkSWRzKHJlc3BvbnNlLmlkKTtcblxuXHRcdFx0XHRjb25zdCAkcm93ID0gJChgW2RhdGEtcm9ja2V0LWluc2lnaHRzLWlkPVwiJHtyZXNwb25zZS5pZH1cIl1gKTtcblx0XHRcdFx0JHJvdy5yZXBsYWNlV2l0aChyZXNwb25zZS5odG1sKTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgY3JlZGl0IHN0YXR1c1xuXHRcdFx0XHR1cGRhdGVDcmVkaXRTdGF0ZShyZXNwb25zZS5oYXNfY3JlZGl0KTtcblxuXHRcdFx0XHQvLyBVcGRhdGUgcXVvdGEgYmFubmVyIHZpc2liaWxpdHlcblx0XHRcdFx0dXBkYXRlUXVvdGFCYW5uZXIocmVzcG9uc2UuY2FuX2FkZF9wYWdlcyk7XG5cbiAgICAgICAgICAgICAgICAvLyBVcGRhdGUgZ2xvYmFsIHNjb3JlIGRhdGEuXG4gICAgICAgICAgICAgICAgZ2xvYmFsU2NvcmVEYXRhID0gcmVzcG9uc2UuZ2xvYmFsX3Njb3JlX2RhdGE7XG5cblx0XHRcdFx0Ly8gVXBkYXRlIGdsb2JhbCBzY29yZSByb3cgaW4gdGFibGUgaWYgb24gUm9ja2V0IEluc2lnaHRzIHBhZ2UuXG5cdFx0XHRcdHVwZGF0ZUdsb2JhbFNjb3JlUm93KGdsb2JhbFNjb3JlRGF0YSk7XG5cdFx0XHRcdC8vIFN0YXJ0IHBvbGxpbmcgaWYgbm90IGFscmVhZHkgcnVubmluZ1xuXHRcdFx0XHRpZiAoIXBvbGxUaW1lcikge1xuXHRcdFx0XHRcdHBvbGxJbnRlcnZhbCA9IFBPTExfQkFTRV9JTlRFUlZBTDtcblx0XHRcdFx0XHRzY2hlZHVsZVBvbGxpbmcoKTtcblx0XHRcdFx0fVxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0Y29uc29sZS5lcnJvcihyZXNwb25zZT8ubWVzc2FnZSB8fCByZXNwb25zZSk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH1cblxuXHQvLyA9PT09IEluaXRpYWxpemF0aW9uID09PT1cblx0Ly8gQmluZCBldmVudFxuXHQkKGRvY3VtZW50KS5vbiggJ2NsaWNrJywgJyN3cHItYWN0aW9uLWFkZF9wYWdlX3NwZWVkX3JhZGFyJywgaGFuZGxlQWRkUGFnZSApO1xuXHQkKGRvY3VtZW50KS5vbiggJ2NsaWNrJywgJy53cHItYWN0aW9uLXNwZWVkX3JhZGFyX3JlZnJlc2gnLCBoYW5kbGVSZXNldFBhZ2UgKTtcblx0Ly8gSGFuZGxlIEVudGVyIGtleSBwcmVzcyBvbiBwYWdlIHVybCBpbnB1dC5cblx0JChkb2N1bWVudCkub24oICdrZXlwcmVzcycsICcjd3ByLXNwZWVkLXJhZGFyLXVybC1pbnB1dCcsIGZ1bmN0aW9uKGUpIHtcblx0XHRpZiAoZS5rZXkgPT09ICdFbnRlcicpIHtcblx0XHQgIGUucHJldmVudERlZmF1bHQoKTtcblx0XHQgIGhhbmRsZUFkZFBhZ2UoZSk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBPbmx5IHBvbGwgaWYgb24gYSB3cHIgc2VjdGlvbiB0aGF0IHJlcXVpcmVzIHBvbGxpbmcoZGFzaGJvYXJkfHJvY2tldF9pbnNpZ2h0cykgKG1vcmUgcm9idXN0IGNoZWNrKVxuICAgIGZ1bmN0aW9uIGlzVmFsaWRQYWdlRm9yUG9sbGluZygpIHtcbiAgICAgICAgY29uc3QgdXJsUGFyYW1zID0gbmV3IFVSTFNlYXJjaFBhcmFtcyh3aW5kb3cubG9jYXRpb24uc2VhcmNoKTtcbiAgICAgICAgc3dpdGNoICh3aW5kb3cubG9jYXRpb24uaGFzaCkge1xuICAgICAgICAgICAgY2FzZSAnI2Rhc2hib2FyZCc6XG4gICAgICAgICAgICBjYXNlICcjcm9ja2V0X2luc2lnaHRzJzpcbiAgICAgICAgICAgICAgICByZXR1cm4gdXJsUGFyYW1zLmdldCgncGFnZScpID09PSAnd3Byb2NrZXQnO1xuICAgICAgICAgICAgZGVmYXVsdDpcbiAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cbiAgICB9XG5cblx0Ly8gUmVzdW1lIHBvbGxpbmcgaWYgbmVlZGVkXG5cdGlmIChpc1ZhbGlkUGFnZUZvclBvbGxpbmcoKSAmJiByb2NrZXRJbnNpZ2h0c0lkcy5sZW5ndGggPiAwKSB7XG5cdFx0cG9sbEludGVydmFsID0gUE9MTF9CQVNFX0lOVEVSVkFMO1xuXHRcdHNjaGVkdWxlUG9sbGluZygpO1xuXHR9XG5cbiAgICAvLyBIYW5kbGUgVUkgdXBkYXRlIHdoZW4gbWVudShkYXNoYm9hcmR8cm9ja2V0X2luc2lnaHRzKSBpcyBjbGlja2VkLlxuXHQkKCcud3ByLUhlYWRlci1uYXYgYScpLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdGNvbnN0IGlkID0gdGhpcy5pZDtcblx0XHRkZWNpZGVHbG9iYWxTY29yZVRvVXBkYXRlKGlkKTtcblx0fSk7XG5cblx0Ly8gSGFuZGxlIFVJIHVwZGF0ZSBvbiB0aGUgcm9ja2V0IGluc2lnaHRzIHRhYiB3aGVuIFwiQWRkIFBhZ2VzXCIgYnV0dG9uIG9uIHRoZSBnbG9iYWwgc2NvcmUgd2lkZ2V0IGlzIGNsaWNrZWQuXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLXBlcmNlbnRhZ2Utc2NvcmUtd2lkZ2V0IC53cHItcmktYWRkLXVybC1idXR0b24nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAoIXRoaXMudGV4dENvbnRlbnQuaW5jbHVkZXMoJ0FkZCBQYWdlcycpKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0Ly8gRGVsYXkgVUkgdXBkYXRlIGEgYml0IHRpbGwgZWxlbWVudCBpcyB2aXNpYmxlLlxuXHRcdHNldFRpbWVvdXQoKCkgPT4ge1xuXHRcdFx0dXBkYXRlR2xvYmFsU2NvcmVSb3coZ2xvYmFsU2NvcmVEYXRhKTtcblx0XHR9LCAzMCk7XG5cdH0pO1xufSk7XG4iLCIvLyBBZGQgZ3JlZW5zb2NrIGxpYiBmb3IgYW5pbWF0aW9uc1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svVHdlZW5MaXRlLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9UaW1lbGluZUxpdGUubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL2Vhc2luZy9FYXNlUGFjay5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svcGx1Z2lucy9DU1NQbHVnaW4ubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzJztcclxuXHJcbi8vIEFkZCBzY3JpcHRzXHJcbmltcG9ydCAnLi4vZ2xvYmFsL3BhZ2VNYW5hZ2VyLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvbWFpbi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL2ZpZWxkcy5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL2JlYWNvbi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL2FqYXguanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9yb2NrZXRjZG4uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9jb3VudGRvd24uanMnOyIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcbiAgICBpZiAoJ0JlYWNvbicgaW4gd2luZG93KSB7XG4gICAgICAgIC8qKlxuICAgICAgICAgKiBTaG93IGJlYWNvbnMgb24gYnV0dG9uIFwiaGVscFwiIGNsaWNrXG4gICAgICAgICAqL1xuICAgICAgICB2YXIgJGhlbHAgPSAkKCcud3ByLWluZm9BY3Rpb24tLWhlbHAnKTtcbiAgICAgICAgJGhlbHAub24oJ2NsaWNrJywgZnVuY3Rpb24oZSl7XG4gICAgICAgICAgICB2YXIgaWRzID0gJCh0aGlzKS5kYXRhKCdiZWFjb24taWQnKTtcbiAgICAgICAgICAgIHZhciBidXR0b24gPSAkKHRoaXMpLmRhdGEoJ3dwcl90cmFja19idXR0b24nKSB8fCAnQmVhY29uIEhlbHAnO1xuICAgICAgICAgICAgdmFyIGNvbnRleHQgPSAkKHRoaXMpLmRhdGEoJ3dwcl90cmFja19jb250ZXh0JykgfHwgJ1NldHRpbmdzJztcblxuICAgICAgICAgICAgLy8gVHJhY2sgd2l0aCBNaXhQYW5lbCBKUyBTREtcbiAgICAgICAgICAgIHdwclRyYWNrSGVscEJ1dHRvbihidXR0b24sIGNvbnRleHQpO1xuXG4gICAgICAgICAgICAvLyBDb250aW51ZSB3aXRoIGV4aXN0aW5nIGJlYWNvbiBmdW5jdGlvbmFsaXR5XG4gICAgICAgICAgICB3cHJDYWxsQmVhY29uKGlkcyk7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGZ1bmN0aW9uIHdwckNhbGxCZWFjb24oYUlEKXtcbiAgICAgICAgICAgIGFJRCA9IGFJRC5zcGxpdCgnLCcpO1xuICAgICAgICAgICAgaWYgKCBhSUQubGVuZ3RoID09PSAwICkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIGlmICggYUlELmxlbmd0aCA+IDEgKSB7XG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5CZWFjb24oXCJzdWdnZXN0XCIsIGFJRCk7XG5cbiAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHdpbmRvdy5CZWFjb24oXCJvcGVuXCIpO1xuICAgICAgICAgICAgICAgICAgICB9LCAyMDApO1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5CZWFjb24oXCJhcnRpY2xlXCIsIGFJRC50b1N0cmluZygpKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgfVxuICAgIH1cblxuXHQkKCAnLndwci1yaS1yZXBvcnQnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwclRyYWNrSGVscEJ1dHRvbiggJ3JvY2tldCBpbnNpZ2h0cyBzZWUgcmVwb3J0JywgJ3BlcmZvcm1hbmNlIHN1bW1hcnknICk7XG5cdH0gKTtcblxuICAgIC8vIE1peFBhbmVsIHRyYWNraW5nIGZ1bmN0aW9uXG4gICAgZnVuY3Rpb24gd3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCkge1xuICAgICAgICBpZiAodHlwZW9mIG1peHBhbmVsICE9PSAndW5kZWZpbmVkJyAmJiBtaXhwYW5lbC50cmFjaykge1xuICAgICAgICAgICAgLy8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW4gdXNpbmcgbG9jYWxpemVkIGRhdGFcbiAgICAgICAgICAgIGlmICh0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnIHx8ICFyb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkIHx8IHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgPT09ICcwJykge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgLy8gSWRlbnRpZnkgdXNlciB3aXRoIGhhc2hlZCBsaWNlbnNlIGVtYWlsIGlmIGF2YWlsYWJsZVxuICAgICAgICAgICAgaWYgKHJvY2tldF9taXhwYW5lbF9kYXRhLnVzZXJfaWQgJiYgdHlwZW9mIG1peHBhbmVsLmlkZW50aWZ5ID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICAgICAgbWl4cGFuZWwuaWRlbnRpZnkocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIG1peHBhbmVsLnRyYWNrKCdCdXR0b24gQ2xpY2tlZCcsIHtcbiAgICAgICAgICAgICAgICAnYnV0dG9uJzogYnV0dG9uLFxuXHRcdFx0XHQnYnV0dG9uX2NvbnRleHQnOiBjb250ZXh0LFxuXHRcdFx0XHQncGx1Z2luJzogcm9ja2V0X21peHBhbmVsX2RhdGEucGx1Z2luLFxuICAgICAgICAgICAgICAgICdicmFuZCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuICAgICAgICAgICAgICAgICdhcHBsaWNhdGlvbic6IHJvY2tldF9taXhwYW5lbF9kYXRhLmFwcCxcbiAgICAgICAgICAgICAgICAnY29udGV4dCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmNvbnRleHQsXG4gICAgICAgICAgICAgICAgJ3BhdGgnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wYXRoXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIE1ha2UgZnVuY3Rpb24gZ2xvYmFsbHkgYXZhaWxhYmxlXG4gICAgd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9IHdwclRyYWNrSGVscEJ1dHRvbjtcbn0pO1xuIiwiZnVuY3Rpb24gZ2V0VGltZVJlbWFpbmluZyhlbmR0aW1lKXtcbiAgICBjb25zdCBzdGFydCA9IERhdGUubm93KCk7XG4gICAgY29uc3QgdG90YWwgPSAoZW5kdGltZSAqIDEwMDApIC0gc3RhcnQ7XG4gICAgY29uc3Qgc2Vjb25kcyA9IE1hdGguZmxvb3IoICh0b3RhbC8xMDAwKSAlIDYwICk7XG4gICAgY29uc3QgbWludXRlcyA9IE1hdGguZmxvb3IoICh0b3RhbC8xMDAwLzYwKSAlIDYwICk7XG4gICAgY29uc3QgaG91cnMgPSBNYXRoLmZsb29yKCAodG90YWwvKDEwMDAqNjAqNjApKSAlIDI0ICk7XG4gICAgY29uc3QgZGF5cyA9IE1hdGguZmxvb3IoIHRvdGFsLygxMDAwKjYwKjYwKjI0KSApO1xuXG4gICAgcmV0dXJuIHtcbiAgICAgICAgdG90YWwsXG4gICAgICAgIGRheXMsXG4gICAgICAgIGhvdXJzLFxuICAgICAgICBtaW51dGVzLFxuICAgICAgICBzZWNvbmRzXG4gICAgfTtcbn1cblxuZnVuY3Rpb24gaW5pdGlhbGl6ZUNsb2NrKGlkLCBlbmR0aW1lKSB7XG4gICAgY29uc3QgY2xvY2sgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG5cbiAgICBpZiAoY2xvY2sgPT09IG51bGwpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGNvbnN0IGRheXNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tZGF5cycpO1xuICAgIGNvbnN0IGhvdXJzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLWhvdXJzJyk7XG4gICAgY29uc3QgbWludXRlc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1taW51dGVzJyk7XG4gICAgY29uc3Qgc2Vjb25kc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1zZWNvbmRzJyk7XG5cbiAgICBmdW5jdGlvbiB1cGRhdGVDbG9jaygpIHtcbiAgICAgICAgY29uc3QgdCA9IGdldFRpbWVSZW1haW5pbmcoZW5kdGltZSk7XG5cbiAgICAgICAgaWYgKHQudG90YWwgPCAwKSB7XG4gICAgICAgICAgICBjbGVhckludGVydmFsKHRpbWVpbnRlcnZhbCk7XG5cbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGRheXNTcGFuLmlubmVySFRNTCA9IHQuZGF5cztcbiAgICAgICAgaG91cnNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0LmhvdXJzKS5zbGljZSgtMik7XG4gICAgICAgIG1pbnV0ZXNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0Lm1pbnV0ZXMpLnNsaWNlKC0yKTtcbiAgICAgICAgc2Vjb25kc1NwYW4uaW5uZXJIVE1MID0gKCcwJyArIHQuc2Vjb25kcykuc2xpY2UoLTIpO1xuICAgIH1cblxuICAgIHVwZGF0ZUNsb2NrKCk7XG4gICAgY29uc3QgdGltZWludGVydmFsID0gc2V0SW50ZXJ2YWwodXBkYXRlQ2xvY2ssIDEwMDApO1xufVxuXG5mdW5jdGlvbiBydWNzc1RpbWVyKGlkLCBlbmR0aW1lKSB7XG5cdGNvbnN0IHRpbWVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpO1xuXHRjb25zdCBub3RpY2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1zYWFzLXByb2Nlc3NpbmcnKTtcblx0Y29uc3Qgc3VjY2VzcyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyb2NrZXQtbm90aWNlLXNhYXMtc3VjY2VzcycpO1xuXG5cdGlmICh0aW1lciA9PT0gbnVsbCkge1xuXHRcdHJldHVybjtcblx0fVxuXG5cdGZ1bmN0aW9uIHVwZGF0ZVRpbWVyKCkge1xuXHRcdGNvbnN0IHN0YXJ0ID0gRGF0ZS5ub3coKTtcblx0XHRjb25zdCByZW1haW5pbmcgPSBNYXRoLmZsb29yKCAoIChlbmR0aW1lICogMTAwMCkgLSBzdGFydCApIC8gMTAwMCApO1xuXG5cdFx0aWYgKHJlbWFpbmluZyA8PSAwKSB7XG5cdFx0XHRjbGVhckludGVydmFsKHRpbWVySW50ZXJ2YWwpO1xuXG5cdFx0XHRpZiAobm90aWNlICE9PSBudWxsKSB7XG5cdFx0XHRcdG5vdGljZS5jbGFzc0xpc3QuYWRkKCdoaWRkZW4nKTtcblx0XHRcdH1cblxuXHRcdFx0aWYgKHN1Y2Nlc3MgIT09IG51bGwpIHtcblx0XHRcdFx0c3VjY2Vzcy5jbGFzc0xpc3QucmVtb3ZlKCdoaWRkZW4nKTtcblx0XHRcdH1cblxuXHRcdFx0aWYgKCByb2NrZXRfYWpheF9kYXRhLmNyb25fZGlzYWJsZWQgKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Y29uc3QgZGF0YSA9IG5ldyBGb3JtRGF0YSgpO1xuXG5cdFx0XHRkYXRhLmFwcGVuZCggJ2FjdGlvbicsICdyb2NrZXRfc3Bhd25fY3JvbicgKTtcblx0XHRcdGRhdGEuYXBwZW5kKCAnbm9uY2UnLCByb2NrZXRfYWpheF9kYXRhLm5vbmNlICk7XG5cblx0XHRcdGZldGNoKCBhamF4dXJsLCB7XG5cdFx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0XHRjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcblx0XHRcdFx0Ym9keTogZGF0YVxuXHRcdFx0fSApO1xuXG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0dGltZXIuaW5uZXJIVE1MID0gcmVtYWluaW5nO1xuXHR9XG5cblx0dXBkYXRlVGltZXIoKTtcblx0Y29uc3QgdGltZXJJbnRlcnZhbCA9IHNldEludGVydmFsKCB1cGRhdGVUaW1lciwgMTAwMCk7XG59XG5cbmlmICghRGF0ZS5ub3cpIHtcbiAgICBEYXRlLm5vdyA9IGZ1bmN0aW9uIG5vdygpIHtcbiAgICAgIHJldHVybiBuZXcgRGF0ZSgpLmdldFRpbWUoKTtcbiAgICB9O1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEucHJvbW9fZW5kICE9PSAndW5kZWZpbmVkJykge1xuICAgIGluaXRpYWxpemVDbG9jaygncm9ja2V0LXByb21vLWNvdW50ZG93bicsIHJvY2tldF9hamF4X2RhdGEucHJvbW9fZW5kKTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbiAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBpbml0aWFsaXplQ2xvY2soJ3JvY2tldC1yZW5ldy1jb3VudGRvd24nLCByb2NrZXRfYWpheF9kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbik7XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5ub3RpY2VfZW5kX3RpbWUgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgcnVjc3NUaW1lcigncm9ja2V0LXJ1Y3NzLXRpbWVyJywgcm9ja2V0X2FqYXhfZGF0YS5ub3RpY2VfZW5kX3RpbWUpO1xufSIsImltcG9ydCAnLi4vY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMnO1xuXG52YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG5cblxuICAgIC8qKipcbiAgICAqIENoZWNrIHBhcmVudCAvIHNob3cgY2hpbGRyZW5cbiAgICAqKiovXG5cblx0ZnVuY3Rpb24gd3ByU2hvd0NoaWxkcmVuKGFFbGVtKXtcblx0XHR2YXIgcGFyZW50SWQsICRjaGlsZHJlbjtcblxuXHRcdGFFbGVtICAgICA9ICQoIGFFbGVtICk7XG5cdFx0cGFyZW50SWQgID0gYUVsZW0uYXR0cignaWQnKTtcblx0XHQkY2hpbGRyZW4gPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgcGFyZW50SWQgKyAnXCJdJyk7XG5cblx0XHQvLyBUZXN0IGNoZWNrIGZvciBzd2l0Y2hcblx0XHRpZihhRWxlbS5pcygnOmNoZWNrZWQnKSl7XG5cdFx0XHQkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblxuXHRcdFx0JGNoaWxkcmVuLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGlmICggJCh0aGlzKS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmlzKCc6Y2hlY2tlZCcpKSB7XG5cdFx0XHRcdFx0dmFyIGlkID0gJCh0aGlzKS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyk7XG5cblx0XHRcdFx0XHQkKCdbZGF0YS1wYXJlbnQ9XCInICsgaWQgKyAnXCJdJykuYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdFx0fVxuXHRcdFx0fSk7XG5cdFx0fVxuXHRcdGVsc2V7XG5cdFx0XHQkY2hpbGRyZW4ucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblxuXHRcdFx0JGNoaWxkcmVuLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHZhciBpZCA9ICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpO1xuXG5cdFx0XHRcdCQoJ1tkYXRhLXBhcmVudD1cIicgKyBpZCArICdcIl0nKS5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdFx0fSk7XG5cdFx0fVxuXHR9XG5cbiAgICAvKipcbiAgICAgKiBUZWxsIGlmIHRoZSBnaXZlbiBjaGlsZCBmaWVsZCBoYXMgYW4gYWN0aXZlIHBhcmVudCBmaWVsZC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSAgb2JqZWN0ICRmaWVsZCBBIGpRdWVyeSBvYmplY3Qgb2YgYSBcIi53cHItZmllbGRcIiBmaWVsZC5cbiAgICAgKiBAcmV0dXJuIGJvb2x8bnVsbFxuICAgICAqL1xuICAgIGZ1bmN0aW9uIHdwcklzUGFyZW50QWN0aXZlKCAkZmllbGQgKSB7XG4gICAgICAgIHZhciAkcGFyZW50O1xuXG4gICAgICAgIGlmICggISAkZmllbGQubGVuZ3RoICkge1xuICAgICAgICAgICAgLy8gwq9cXF8o44OEKV8vwq9cbiAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICRmaWVsZC5kYXRhKCAncGFyZW50JyApO1xuXG4gICAgICAgIGlmICggdHlwZW9mICRwYXJlbnQgIT09ICdzdHJpbmcnICkge1xuICAgICAgICAgICAgLy8gVGhpcyBmaWVsZCBoYXMgbm8gcGFyZW50IGZpZWxkOiB0aGVuIHdlIGNhbiBkaXNwbGF5IGl0LlxuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICAkcGFyZW50ID0gJHBhcmVudC5yZXBsYWNlKCAvXlxccyt8XFxzKyQvZywgJycgKTtcblxuICAgICAgICBpZiAoICcnID09PSAkcGFyZW50ICkge1xuICAgICAgICAgICAgLy8gVGhpcyBmaWVsZCBoYXMgbm8gcGFyZW50IGZpZWxkOiB0aGVuIHdlIGNhbiBkaXNwbGF5IGl0LlxuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICAkcGFyZW50ID0gJCggJyMnICsgJHBhcmVudCApO1xuXG4gICAgICAgIGlmICggISAkcGFyZW50Lmxlbmd0aCApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQncyBwYXJlbnQgaXMgbWlzc2luZzogbGV0J3MgY29uc2lkZXIgaXQncyBub3QgYWN0aXZlIHRoZW4uXG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoICEgJHBhcmVudC5pcyggJzpjaGVja2VkJyApICYmICRwYXJlbnQuaXMoJ2lucHV0JykpIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQncyBwYXJlbnQgaXMgY2hlY2tib3ggYW5kIG5vdCBjaGVja2VkOiBkb24ndCBkaXNwbGF5IHRoZSBmaWVsZCB0aGVuLlxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cblx0XHRpZiAoICEkcGFyZW50Lmhhc0NsYXNzKCdyYWRpby1hY3RpdmUnKSAmJiAkcGFyZW50LmlzKCdidXR0b24nKSkge1xuXHRcdFx0Ly8gVGhpcyBmaWVsZCdzIHBhcmVudCBidXR0b24gYW5kIGlzIG5vdCBhY3RpdmU6IGRvbid0IGRpc3BsYXkgdGhlIGZpZWxkIHRoZW4uXG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuICAgICAgICAvLyBHbyByZWN1cnNpdmUgdG8gdGhlIGxhc3QgcGFyZW50LlxuICAgICAgICByZXR1cm4gd3BySXNQYXJlbnRBY3RpdmUoICRwYXJlbnQuY2xvc2VzdCggJy53cHItZmllbGQnICkgKTtcbiAgICB9XG5cblx0LyoqXG5cdCAqIE1hc2tzIHNlbnNpdGl2ZSBpbmZvcm1hdGlvbiBpbiBhbiBpbnB1dCBmaWVsZCBieSByZXBsYWNpbmcgYWxsIGJ1dCB0aGUgbGFzdCA0IGNoYXJhY3RlcnMgd2l0aCBhc3Rlcmlza3MuXG5cdCAqXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSBpZF9zZWxlY3RvciAtIFRoZSBJRCBvZiB0aGUgaW5wdXQgZmllbGQgdG8gYmUgbWFza2VkLlxuXHQgKiBAcmV0dXJucyB7dm9pZH0gLSBNb2RpZmllcyB0aGUgaW5wdXQgZmllbGQgdmFsdWUgaW4tcGxhY2UuXG5cdCAqXG5cdCAqIEBleGFtcGxlXG5cdCAqIC8vIEhUTUw6IDxpbnB1dCB0eXBlPVwidGV4dFwiIGlkPVwiY3JlZGl0Q2FyZElucHV0XCIgdmFsdWU9XCIxMjM0NTY3ODkwMTIzNDU2XCI+XG5cdCAqIG1hc2tGaWVsZCgnY3JlZGl0Q2FyZElucHV0Jyk7XG5cdCAqIC8vIFJlc3VsdDogVXBkYXRlcyB0aGUgaW5wdXQgZmllbGQgdmFsdWUgdG8gJyoqKioqKioqKioqKjM0NTYnLlxuXHQgKi9cblx0ZnVuY3Rpb24gbWFza0ZpZWxkKHByb3h5X3NlbGVjdG9yLCBjb25jcmV0ZV9zZWxlY3Rvcikge1xuXHRcdHZhciBjb25jcmV0ZSA9IHtcblx0XHRcdCd2YWwnOiBjb25jcmV0ZV9zZWxlY3Rvci52YWwoKSxcblx0XHRcdCdsZW5ndGgnOiBjb25jcmV0ZV9zZWxlY3Rvci52YWwoKS5sZW5ndGhcblx0XHR9XG5cblx0XHRpZiAoY29uY3JldGUubGVuZ3RoID4gNCkge1xuXG5cdFx0XHR2YXIgaGlkZGVuUGFydCA9ICdcXHUyMDIyJy5yZXBlYXQoTWF0aC5tYXgoMCwgY29uY3JldGUubGVuZ3RoIC0gNCkpO1xuXHRcdFx0dmFyIHZpc2libGVQYXJ0ID0gY29uY3JldGUudmFsLnNsaWNlKC00KTtcblxuXHRcdFx0Ly8gQ29tYmluZSB0aGUgaGlkZGVuIGFuZCB2aXNpYmxlIHBhcnRzXG5cdFx0XHR2YXIgbWFza2VkVmFsdWUgPSBoaWRkZW5QYXJ0ICsgdmlzaWJsZVBhcnQ7XG5cblx0XHRcdHByb3h5X3NlbGVjdG9yLnZhbChtYXNrZWRWYWx1ZSk7XG5cdFx0fVxuXHRcdC8vIEVuc3VyZSBldmVudHMgYXJlIG5vdCBhZGRlZCBtb3JlIHRoYW4gb25jZVxuXHRcdGlmICghcHJveHlfc2VsZWN0b3IuZGF0YSgnZXZlbnRzQXR0YWNoZWQnKSkge1xuXHRcdFx0cHJveHlfc2VsZWN0b3Iub24oJ2lucHV0JywgaGFuZGxlSW5wdXQpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3Iub24oJ2ZvY3VzJywgaGFuZGxlRm9jdXMpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3IuZGF0YSgnZXZlbnRzQXR0YWNoZWQnLCB0cnVlKTtcblx0XHR9XG5cblx0XHQvKipcblx0XHQgKiBIYW5kbGUgdGhlIGlucHV0IGV2ZW50XG5cdFx0ICovXG5cdFx0ZnVuY3Rpb24gaGFuZGxlSW5wdXQoKSB7XG5cdFx0XHR2YXIgcHJveHlWYWx1ZSA9IHByb3h5X3NlbGVjdG9yLnZhbCgpO1xuXHRcdFx0aWYgKHByb3h5VmFsdWUuaW5kZXhPZignXFx1MjAyMicpID09PSAtMSkge1xuXHRcdFx0XHRjb25jcmV0ZV9zZWxlY3Rvci52YWwocHJveHlWYWx1ZSk7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlIHRoZSBmb2N1cyBldmVudFxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGhhbmRsZUZvY3VzKCkge1xuXHRcdFx0dmFyIGNvbmNyZXRlX3ZhbHVlID0gY29uY3JldGVfc2VsZWN0b3IudmFsKCk7XG5cdFx0XHRwcm94eV9zZWxlY3Rvci52YWwoY29uY3JldGVfdmFsdWUpO1xuXHRcdH1cblxuXHR9XG5cblx0XHQvLyBVcGRhdGUgdGhlIGNvbmNyZXRlIGZpZWxkIHdoZW4gdGhlIHByb3h5IGlzIHVwZGF0ZWQuXG5cblxuXHRtYXNrRmllbGQoJCgnI2Nsb3VkZmxhcmVfYXBpX2tleV9tYXNrJyksICQoJyNjbG91ZGZsYXJlX2FwaV9rZXknKSk7XG5cdG1hc2tGaWVsZCgkKCcjY2xvdWRmbGFyZV96b25lX2lkX21hc2snKSwgJCgnI2Nsb3VkZmxhcmVfem9uZV9pZCcpKTtcblxuXHQvLyBEaXNwbGF5L0hpZGUgY2hpbGRyZW4gZmllbGRzIG9uIGNoZWNrYm94IGNoYW5nZS5cbiAgICAkKCAnLndwci1pc1BhcmVudCBpbnB1dFt0eXBlPWNoZWNrYm94XScgKS5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHdwclNob3dDaGlsZHJlbigkKHRoaXMpKTtcbiAgICB9KTtcblxuICAgIC8vIE9uIHBhZ2UgbG9hZCwgZGlzcGxheSB0aGUgYWN0aXZlIGZpZWxkcy5cbiAgICAkKCAnLndwci1maWVsZC0tY2hpbGRyZW4nICkuZWFjaCggZnVuY3Rpb24oKSB7XG4gICAgICAgIHZhciAkZmllbGQgPSAkKCB0aGlzICk7XG5cbiAgICAgICAgaWYgKCB3cHJJc1BhcmVudEFjdGl2ZSggJGZpZWxkICkgKSB7XG4gICAgICAgICAgICAkZmllbGQuYWRkQ2xhc3MoICd3cHItaXNPcGVuJyApO1xuICAgICAgICB9XG4gICAgfSApO1xuXG5cblxuXG4gICAgLyoqKlxuICAgICogV2FybmluZyBmaWVsZHNcbiAgICAqKiovXG5cbiAgICB2YXIgJHdhcm5pbmdQYXJlbnQgPSAkKCcud3ByLWZpZWxkLS1wYXJlbnQnKTtcbiAgICB2YXIgJHdhcm5pbmdQYXJlbnRJbnB1dCA9ICQoJy53cHItZmllbGQtLXBhcmVudCBpbnB1dFt0eXBlPWNoZWNrYm94XScpO1xuXG4gICAgLy8gSWYgYWxyZWFkeSBjaGVja2VkXG4gICAgJHdhcm5pbmdQYXJlbnRJbnB1dC5lYWNoKGZ1bmN0aW9uKCl7XG4gICAgICAgIHdwclNob3dDaGlsZHJlbigkKHRoaXMpKTtcbiAgICB9KTtcblxuICAgICR3YXJuaW5nUGFyZW50Lm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgd3ByU2hvd1dhcm5pbmcoJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICBmdW5jdGlvbiB3cHJTaG93V2FybmluZyhhRWxlbSl7XG4gICAgICAgIHZhciAkd2FybmluZ0ZpZWxkID0gYUVsZW0ubmV4dCgnLndwci1maWVsZFdhcm5pbmcnKSxcbiAgICAgICAgICAgICR0aGlzQ2hlY2tib3ggPSBhRWxlbS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLFxuICAgICAgICAgICAgJG5leHRXYXJuaW5nID0gYUVsZW0ucGFyZW50KCkubmV4dCgnLndwci13YXJuaW5nQ29udGFpbmVyJyksXG4gICAgICAgICAgICAkbmV4dEZpZWxkcyA9ICRuZXh0V2FybmluZy5maW5kKCcud3ByLWZpZWxkJyksXG4gICAgICAgICAgICBwYXJlbnRJZCA9IGFFbGVtLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKSxcbiAgICAgICAgICAgICRjaGlsZHJlbiA9ICQoJ1tkYXRhLXBhcmVudD1cIicgKyBwYXJlbnRJZCArICdcIl0nKVxuICAgICAgICA7XG5cbiAgICAgICAgLy8gQ2hlY2sgd2FybmluZyBwYXJlbnRcbiAgICAgICAgaWYoJHRoaXNDaGVja2JveC5pcygnOmNoZWNrZWQnKSl7XG4gICAgICAgICAgICAkd2FybmluZ0ZpZWxkLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgICAgICAkdGhpc0NoZWNrYm94LnByb3AoJ2NoZWNrZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICBhRWxlbS50cmlnZ2VyKCdjaGFuZ2UnKTtcblxuXG4gICAgICAgICAgICB2YXIgJHdhcm5pbmdCdXR0b24gPSAkd2FybmluZ0ZpZWxkLmZpbmQoJy53cHItYnV0dG9uJyk7XG5cbiAgICAgICAgICAgIC8vIFZhbGlkYXRlIHRoZSB3YXJuaW5nXG4gICAgICAgICAgICAkd2FybmluZ0J1dHRvbi5vbignY2xpY2snLCBmdW5jdGlvbigpe1xuICAgICAgICAgICAgICAgICR0aGlzQ2hlY2tib3gucHJvcCgnY2hlY2tlZCcsIHRydWUpO1xuICAgICAgICAgICAgICAgICR3YXJuaW5nRmllbGQucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcbiAgICAgICAgICAgICAgICAkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblxuICAgICAgICAgICAgICAgIC8vIElmIG5leHQgZWxlbSA9IGRpc2FibGVkXG4gICAgICAgICAgICAgICAgaWYoJG5leHRXYXJuaW5nLmxlbmd0aCA+IDApe1xuICAgICAgICAgICAgICAgICAgICAkbmV4dEZpZWxkcy5yZW1vdmVDbGFzcygnd3ByLWlzRGlzYWJsZWQnKTtcbiAgICAgICAgICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXQnKS5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgICBlbHNle1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuYWRkQ2xhc3MoJ3dwci1pc0Rpc2FibGVkJyk7XG4gICAgICAgICAgICAkbmV4dEZpZWxkcy5maW5kKCdpbnB1dCcpLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSk7XG4gICAgICAgICAgICAkbmV4dEZpZWxkcy5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLnByb3AoJ2NoZWNrZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAkY2hpbGRyZW4ucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENOQU1FUyBhZGQvcmVtb3ZlIGxpbmVzXG4gICAgICovXG4gICAgJChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItbXVsdGlwbGUtY2xvc2UnLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCQodGhpcykucGFyZW50KCkuc2xpZGVVcCggJ3Nsb3cnICwgZnVuY3Rpb24oKXskKHRoaXMpLnJlbW92ZSgpOyB9ICk7XG5cdH0gKTtcblxuXHQkKCcud3ByLWJ1dHRvbi0tYWRkTXVsdGknKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAkKCQoJyN3cHItY25hbWUtbW9kZWwnKS5odG1sKCkpLmFwcGVuZFRvKCcjd3ByLWNuYW1lcy1saXN0Jyk7XG4gICAgfSk7XG5cblx0LyoqKlxuXHQgKiBXcHIgUmFkaW8gYnV0dG9uXG5cdCAqKiovXG5cdHZhciBkaXNhYmxlX3JhZGlvX3dhcm5pbmcgPSBmYWxzZTtcblxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b24nLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdGlmKCQodGhpcykuaGFzQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpKXtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyICRwYXJlbnQgPSAkKHRoaXMpLnBhcmVudHMoJy53cHItcmFkaW8tYnV0dG9ucycpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b24nKS5yZW1vdmVDbGFzcygncmFkaW8tYWN0aXZlJyk7XG5cdFx0JHBhcmVudC5maW5kKCcud3ByLWV4dHJhLWZpZWxkcy1jb250YWluZXInKS5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1maWVsZFdhcm5pbmcnKS5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdCQodGhpcykuYWRkQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpO1xuXHRcdHdwclNob3dSYWRpb1dhcm5pbmcoJCh0aGlzKSk7XG5cblx0fSApO1xuXG5cblx0ZnVuY3Rpb24gd3ByU2hvd1JhZGlvV2FybmluZygkZWxtKXtcblx0XHRkaXNhYmxlX3JhZGlvX3dhcm5pbmcgPSBmYWxzZTtcblx0XHQkZWxtLnRyaWdnZXIoIFwiYmVmb3JlX3Nob3dfcmFkaW9fd2FybmluZ1wiLCBbICRlbG0gXSApO1xuXHRcdGlmICghJGVsbS5oYXNDbGFzcygnaGFzLXdhcm5pbmcnKSB8fCBkaXNhYmxlX3JhZGlvX3dhcm5pbmcpIHtcblx0XHRcdHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pO1xuXHRcdFx0JGVsbS50cmlnZ2VyKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBbICRlbG0gXSApO1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0XHR2YXIgJHdhcm5pbmdGaWVsZCA9ICQoJ1tkYXRhLXBhcmVudD1cIicgKyAkZWxtLmF0dHIoJ2lkJykgKyAnXCJdLndwci1maWVsZFdhcm5pbmcnKTtcblx0XHQkd2FybmluZ0ZpZWxkLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0dmFyICR3YXJuaW5nQnV0dG9uID0gJHdhcm5pbmdGaWVsZC5maW5kKCcud3ByLWJ1dHRvbicpO1xuXG5cdFx0Ly8gVmFsaWRhdGUgdGhlIHdhcm5pbmdcblx0XHQkd2FybmluZ0J1dHRvbi5vbignY2xpY2snLCBmdW5jdGlvbigpe1xuXHRcdFx0JHdhcm5pbmdGaWVsZC5yZW1vdmVDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdFx0d3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSk7XG5cdFx0XHQkZWxtLnRyaWdnZXIoIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIFsgJGVsbSBdICk7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fSk7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKSB7XG5cdFx0dmFyICRwYXJlbnQgPSAkZWxtLnBhcmVudHMoJy53cHItcmFkaW8tYnV0dG9ucycpO1xuXHRcdHZhciAkY2hpbGRyZW4gPSAkKCcud3ByLWV4dHJhLWZpZWxkcy1jb250YWluZXJbZGF0YS1wYXJlbnQ9XCInICsgJGVsbS5hdHRyKCdpZCcpICsgJ1wiXScpO1xuXHRcdCRjaGlsZHJlbi5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHR9XG5cblx0LyoqKlxuXHQgKiBXcHIgT3B0aW1pemUgQ3NzIERlbGl2ZXJ5IEZpZWxkXG5cdCAqKiovXG5cdHZhciBydWNzc0FjdGl2ZSA9IHBhcnNlSW50KCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgpKTtcblxuXHQkKCBcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kIC53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uXCIgKVxuXHRcdC5vbiggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgZnVuY3Rpb24oIGV2ZW50LCAkZWxtICkge1xuXHRcdFx0dG9nZ2xlQWN0aXZlT3B0aW1pemVDc3NEZWxpdmVyeU1ldGhvZCgkZWxtKTtcblx0XHR9KTtcblxuXHQkKFwiI29wdGltaXplX2Nzc19kZWxpdmVyeVwiKS5vbihcImNoYW5nZVwiLCBmdW5jdGlvbigpe1xuXHRcdGlmKCAkKHRoaXMpLmlzKFwiOm5vdCg6Y2hlY2tlZClcIikgKXtcblx0XHRcdGRpc2FibGVPcHRpbWl6ZUNzc0RlbGl2ZXJ5KCk7XG5cdFx0fWVsc2V7XG5cdFx0XHR2YXIgZGVmYXVsdF9yYWRpb19idXR0b25faWQgPSAnIycrJCgnI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QnKS5kYXRhKCAnZGVmYXVsdCcgKTtcblx0XHRcdCQoZGVmYXVsdF9yYWRpb19idXR0b25faWQpLnRyaWdnZXIoJ2NsaWNrJyk7XG5cdFx0fVxuXHR9KTtcblxuXHRmdW5jdGlvbiB0b2dnbGVBY3RpdmVPcHRpbWl6ZUNzc0RlbGl2ZXJ5TWV0aG9kKCRlbG0pIHtcblx0XHR2YXIgb3B0aW1pemVfbWV0aG9kID0gJGVsbS5kYXRhKCd2YWx1ZScpO1xuXHRcdGlmKCdyZW1vdmVfdW51c2VkX2NzcycgPT09IG9wdGltaXplX21ldGhvZCl7XG5cdFx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMSk7XG5cdFx0XHQkKCcjYXN5bmNfY3NzJykudmFsKDApO1xuXHRcdH1lbHNle1xuXHRcdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDApO1xuXHRcdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgxKTtcblx0XHR9XG5cblx0fVxuXG5cdGZ1bmN0aW9uIGRpc2FibGVPcHRpbWl6ZUNzc0RlbGl2ZXJ5KCkge1xuXHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgwKTtcblx0XHQkKCcjYXN5bmNfY3NzJykudmFsKDApO1xuXHR9XG5cblx0JCggXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCAud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvblwiIClcblx0XHQub24oIFwiYmVmb3JlX3Nob3dfcmFkaW9fd2FybmluZ1wiLCBmdW5jdGlvbiggZXZlbnQsICRlbG0gKSB7XG5cdFx0XHRkaXNhYmxlX3JhZGlvX3dhcm5pbmcgPSAoJ3JlbW92ZV91bnVzZWRfY3NzJyA9PT0gJGVsbS5kYXRhKCd2YWx1ZScpICYmIDEgPT09IHJ1Y3NzQWN0aXZlKVxuXHRcdH0pO1xuXG5cdCQoIFwiLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1saXN0LWhlYWRlclwiICkuY2xpY2soZnVuY3Rpb24gKGUpIHtcblx0XHQkKGUudGFyZ2V0KS5jbG9zZXN0KCcud3ByLW11bHRpcGxlLXNlbGVjdCAud3ByLWxpc3QnKS50b2dnbGVDbGFzcygnb3BlbicpO1xuXHR9KTtcblxuXHQkKCcud3ByLW11bHRpcGxlLXNlbGVjdCAud3ByLWNoZWNrYm94JykuY2xpY2soZnVuY3Rpb24gKGUpIHtcblx0XHRjb25zdCBjaGVja2JveCA9ICQodGhpcykuZmluZCgnaW5wdXQnKTtcblx0XHRjb25zdCBpc19jaGVja2VkID0gY2hlY2tib3guYXR0cignY2hlY2tlZCcpICE9PSB1bmRlZmluZWQ7XG5cdFx0Y2hlY2tib3guYXR0cignY2hlY2tlZCcsIGlzX2NoZWNrZWQgPyBudWxsIDogJ2NoZWNrZWQnICk7XG5cdFx0Y29uc3Qgc3ViX2NoZWNrYm94ZXMgPSAkKGNoZWNrYm94KS5jbG9zZXN0KCcud3ByLWxpc3QnKS5maW5kKCcud3ByLWxpc3QtYm9keSBpbnB1dFt0eXBlPVwiY2hlY2tib3hcIl0nKTtcblx0XHRpZihjaGVja2JveC5oYXNDbGFzcygnd3ByLW1haW4tY2hlY2tib3gnKSkge1xuXHRcdFx0JC5tYXAoc3ViX2NoZWNrYm94ZXMsIGNoZWNrYm94ID0+IHtcblx0XHRcdFx0JChjaGVja2JveCkuYXR0cignY2hlY2tlZCcsIGlzX2NoZWNrZWQgPyBudWxsIDogJ2NoZWNrZWQnICk7XG5cdFx0XHR9KTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0Y29uc3QgbWFpbl9jaGVja2JveCA9ICQoY2hlY2tib3gpLmNsb3Nlc3QoJy53cHItbGlzdCcpLmZpbmQoJy53cHItbWFpbi1jaGVja2JveCcpO1xuXG5cdFx0Y29uc3Qgc3ViX2NoZWNrZWQgPSAgJC5tYXAoc3ViX2NoZWNrYm94ZXMsIGNoZWNrYm94ID0+IHtcblx0XHRcdGlmKCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnKSA9PT0gdW5kZWZpbmVkKSB7XG5cdFx0XHRcdHJldHVybiA7XG5cdFx0XHR9XG5cdFx0XHRyZXR1cm4gY2hlY2tib3g7XG5cdFx0fSk7XG5cdFx0bWFpbl9jaGVja2JveC5hdHRyKCdjaGVja2VkJywgc3ViX2NoZWNrZWQubGVuZ3RoID09PSBzdWJfY2hlY2tib3hlcy5sZW5ndGggPyAnY2hlY2tlZCcgOiBudWxsICk7XG5cdH0pO1xuXG5cdGlmICggJCggJy53cHItbWFpbi1jaGVja2JveCcgKS5sZW5ndGggPiAwICkge1xuXHRcdCQoJy53cHItbWFpbi1jaGVja2JveCcpLmVhY2goKGNoZWNrYm94X2tleSwgY2hlY2tib3gpID0+IHtcblx0XHRcdGxldCBwYXJlbnRfbGlzdCA9ICQoY2hlY2tib3gpLnBhcmVudHMoJy53cHItbGlzdCcpO1xuXHRcdFx0bGV0IG5vdF9jaGVja2VkID0gcGFyZW50X2xpc3QuZmluZCggJy53cHItbGlzdC1ib2R5IGlucHV0W3R5cGU9Y2hlY2tib3hdOm5vdCg6Y2hlY2tlZCknICkubGVuZ3RoO1xuXHRcdFx0JChjaGVja2JveCkuYXR0cignY2hlY2tlZCcsIG5vdF9jaGVja2VkIDw9IDAgPyAnY2hlY2tlZCcgOiBudWxsICk7XG5cdFx0fSk7XG5cdH1cblxuXHRsZXQgY2hlY2tCb3hDb3VudGVyID0ge1xuXHRcdGNoZWNrZWQ6IHt9LFxuXHRcdHRvdGFsOiB7fVxuXHR9O1xuXHQkKCcud3ByLWZpZWxkLS1jYXRlZ29yaXplZG11bHRpc2VsZWN0IC53cHItbGlzdCcpLmVhY2goZnVuY3Rpb24gKCkge1xuXHRcdC8vIEdldCB0aGUgSUQgb2YgdGhlIGN1cnJlbnQgZWxlbWVudFxuXHRcdGxldCBpZCA9ICQodGhpcykuYXR0cignaWQnKTtcblx0XHRpZiAoaWQpIHtcblx0XHRcdGNoZWNrQm94Q291bnRlci5jaGVja2VkW2lkXSA9ICQoYCMke2lkfSBpbnB1dFt0eXBlPSdjaGVja2JveCddOmNoZWNrZWRgKS5sZW5ndGg7XG5cdFx0XHRjaGVja0JveENvdW50ZXIudG90YWxbaWRdID0gJChgIyR7aWR9IGlucHV0W3R5cGU9J2NoZWNrYm94J106bm90KC53cHItbWFpbi1jaGVja2JveClgKS5sZW5ndGg7XG5cdFx0XHQvLyBVcGRhdGUgdGhlIGNvdW50ZXIgdGV4dFxuXHRcdFx0JChgIyR7aWR9IC53cHItYmFkZ2UtY291bnRlciBzcGFuYCkudGV4dChjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0pO1xuXHRcdFx0Ly8gU2hvdyBvciBoaWRlIHRoZSBjb3VudGVyIGJhZGdlIGJhc2VkIG9uIHRoZSBjb3VudFxuXHRcdFx0JChgIyR7aWR9IC53cHItYmFkZ2UtY291bnRlcmApLnRvZ2dsZShjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPiAwKTtcblxuXHRcdFx0Ly8gQ2hlY2sgdGhlIHNlbGVjdCBhbGwgb3B0aW9uIGlmIGFsbCBleGNsdXNpb25zIGFyZSBjaGVja2VkIGluIGEgc2VjdGlvbi5cblx0XHRcdGlmIChjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPT09IGNoZWNrQm94Q291bnRlci50b3RhbFtpZF0pIHtcblx0XHRcdFx0JChgIyR7aWR9IC53cHItbWFpbi1jaGVja2JveGApLmF0dHIoJ2NoZWNrZWQnLCB0cnVlKTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xuXG5cdC8qKlxuXHQgKiBEZWxheSBKUyBFeGVjdXRpb24gU2FmZSBNb2RlIEZpZWxkXG5cdCAqL1xuXHR2YXIgJGRqZV9zYWZlX21vZGVfY2hlY2tib3ggPSAkKCcjZGVsYXlfanNfZXhlY3V0aW9uX3NhZmVfbW9kZScpO1xuXHQkKCcjZGVsYXlfanMnKS5vbignY2hhbmdlJywgZnVuY3Rpb24gKCkge1xuXHRcdGlmICgkKHRoaXMpLmlzKCc6bm90KDpjaGVja2VkKScpICYmICRkamVfc2FmZV9tb2RlX2NoZWNrYm94LmlzKCc6Y2hlY2tlZCcpKSB7XG5cdFx0XHQkZGplX3NhZmVfbW9kZV9jaGVja2JveC50cmlnZ2VyKCdjbGljaycpO1xuXHRcdH1cblx0fSk7XG5cblx0bGV0IHN0YWNrZWRfc2VsZWN0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICdyb2NrZXRfc3RhY2tlZF9zZWxlY3QnICk7XG5cdGlmICggc3RhY2tlZF9zZWxlY3QgKSB7XG5cdFx0c3RhY2tlZF9zZWxlY3QuYWRkRXZlbnRMaXN0ZW5lcignY3VzdG9tLXNlbGVjdC1jaGFuZ2UnLGZ1bmN0aW9uKGV2ZW50KXtcblxuXHRcdFx0bGV0IHNlbGVjdGVkX29wdGlvbiA9ICQoIGV2ZW50LmRldGFpbC5zZWxlY3RlZE9wdGlvbiApO1xuXG5cdFx0XHRsZXQgbmFtZSA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCduYW1lJyk7XG5cblx0XHRcdGxldCBzYXZpbmcgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgnc2F2aW5nJyk7XG5cdFx0XHRsZXQgcmVndWxhcl9wcmljZSAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgncmVndWxhci1wcmljZScpO1xuXHRcdFx0bGV0IHByaWNlICA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCdwcmljZScpO1xuXHRcdFx0bGV0IHVybCAgICA9IHNlbGVjdGVkX29wdGlvbi5kYXRhKCd1cmwnKTtcblxuXHRcdFx0bGV0IHBhcmVudF9pdGVtID0gJCh0aGlzKS5wYXJlbnRzKCAnLndwci11cGdyYWRlLWl0ZW0nICk7XG5cblx0XHRcdGlmICggc2F2aW5nICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLXNhdmluZyBzcGFuJyApLmh0bWwoIHNhdmluZyApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCBuYW1lICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLXRpdGxlJyApLmh0bWwoIG5hbWUgKTtcblx0XHRcdH1cblx0XHRcdGlmICggcmVndWxhcl9wcmljZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1wcmljZS1yZWd1bGFyIHNwYW4nICkuaHRtbCggcmVndWxhcl9wcmljZSApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCBwcmljZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1wcmljZS12YWx1ZScgKS5odG1sKCBwcmljZSApO1xuXHRcdFx0fVxuXHRcdFx0aWYgKCB1cmwgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtbGluaycgKS5hdHRyKCAnaHJlZicsIHVybCApO1xuXHRcdFx0fVxuXG5cdFx0fSApO1xuXHR9XG5cblx0JChkb2N1bWVudCkub24oICdjbGljaycsICcud3ByLWNvbmZpcm0tZGVsZXRlJywgZnVuY3Rpb24gKGUpIHtcblx0XHRyZXR1cm4gY29uZmlybSggJCh0aGlzKS5kYXRhKCd3cHJfY29uZmlybV9tc2cnKSApO1xuXHR9ICk7XG5cbn0pO1xuIiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXG5cblx0LyoqKlxuXHQqIERhc2hib2FyZCBub3RpY2Vcblx0KioqL1xuXG5cdHZhciAkbm90aWNlID0gJCgnLndwci1ub3RpY2UnKTtcblx0dmFyICRub3RpY2VDbG9zZSA9ICQoJyN3cHItY29uZ3JhdHVsYXRpb25zLW5vdGljZScpO1xuXG5cdCRub3RpY2VDbG9zZS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJDbG9zZURhc2hib2FyZE5vdGljZSgpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VEYXNoYm9hcmROb3RpY2UoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAudG8oJG5vdGljZSwgMSwge2F1dG9BbHBoYTowLCB4OjQwLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC50bygkbm90aWNlLCAwLjYsIHtoZWlnaHQ6IDAsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjQnKVxuXHRcdCAgLnNldCgkbm90aWNlLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0LyoqXG5cdCAqIFJvY2tldCBBbmFseXRpY3Mgbm90aWNlIGluZm8gY29sbGVjdFxuXHQgKi9cblx0JCggJy5yb2NrZXQtYW5hbHl0aWNzLWRhdGEtY29udGFpbmVyJyApLmhpZGUoKTtcblx0JCggJy5yb2NrZXQtcHJldmlldy1hbmFseXRpY3MtZGF0YScgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCh0aGlzKS5wYXJlbnQoKS5uZXh0KCAnLnJvY2tldC1hbmFseXRpY3MtZGF0YS1jb250YWluZXInICkudG9nZ2xlKCk7XG5cdH0gKTtcblxuXHQvKioqXG5cdCogSGlkZSAvIHNob3cgUm9ja2V0IGFkZG9uIHRhYnMuXG5cdCoqKi9cblxuXHQkKCAnLndwci10b2dnbGUtYnV0dG9uJyApLmVhY2goIGZ1bmN0aW9uKCkge1xuXHRcdHZhciAkYnV0dG9uICAgPSAkKCB0aGlzICk7XG5cdFx0dmFyICRjaGVja2JveCA9ICRidXR0b24uY2xvc2VzdCggJy53cHItZmllbGRzQ29udGFpbmVyLWZpZWxkc2V0JyApLmZpbmQoICcud3ByLXJhZGlvIDpjaGVja2JveCcgKTtcblx0XHR2YXIgJG1lbnVJdGVtID0gJCggJ1tocmVmPVwiJyArICRidXR0b24uYXR0ciggJ2hyZWYnICkgKyAnXCJdLndwci1tZW51SXRlbScgKTtcblxuXHRcdCRjaGVja2JveC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRpZiAoICRjaGVja2JveC5pcyggJzpjaGVja2VkJyApICkge1xuXHRcdFx0XHQkbWVudUl0ZW0uY3NzKCAnZGlzcGxheScsICdibG9jaycgKTtcblx0XHRcdFx0JGJ1dHRvbi5jc3MoICdkaXNwbGF5JywgJ2lubGluZS1ibG9jaycgKTtcblx0XHRcdH0gZWxzZXtcblx0XHRcdFx0JG1lbnVJdGVtLmNzcyggJ2Rpc3BsYXknLCAnbm9uZScgKTtcblx0XHRcdFx0JGJ1dHRvbi5jc3MoICdkaXNwbGF5JywgJ25vbmUnICk7XG5cdFx0XHR9XG5cdFx0fSApLnRyaWdnZXIoICdjaGFuZ2UnICk7XG5cdH0gKTtcblxuXHQvKioqXG5cdCogSGVscCBCdXR0b24gVHJhY2tpbmdcblx0KioqL1xuXHRcblx0Ly8gVHJhY2sgY2xpY2tzIG9uIHZhcmlvdXMgaGVscCBlbGVtZW50cyB3aXRoIGRhdGEgYXR0cmlidXRlc1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnW2RhdGEtd3ByX3RyYWNrX2hlbHBdJywgZnVuY3Rpb24oZSkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0dmFyICRlbCA9ICQodGhpcyk7XG5cdFx0XHR2YXIgYnV0dG9uID0gJGVsLmRhdGEoJ3dwcl90cmFja19oZWxwJyk7XG5cdFx0XHR2YXIgY29udGV4dCA9ICRlbC5kYXRhKCd3cHJfdHJhY2tfY29udGV4dCcpIHx8ICcnO1xuXHRcdFx0XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBUcmFjayBzcGVjaWZpYyBoZWxwIHJlc291cmNlIGNsaWNrcyB3aXRoIGV4cGxpY2l0IHNlbGVjdG9yc1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLndpc3RpYV9lbWJlZCcsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0dmFyIHRpdGxlID0gJCh0aGlzKS50ZXh0KCkgfHwgJ0dldHRpbmcgU3RhcnRlZCBWaWRlbyc7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKHRpdGxlLCAnR2V0dGluZyBTdGFydGVkJyk7XG5cdFx0fVxuXHR9KTtcblxuXHQvLyBUcmFjayBGQVEgbGlua3MgXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICdhW2RhdGEtYmVhY29uLWFydGljbGVdJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgaHJlZiA9ICQodGhpcykuYXR0cignaHJlZicpO1xuXHRcdFx0dmFyIHRleHQgPSAkKHRoaXMpLnRleHQoKTtcblx0XHRcdFxuXHRcdFx0Ly8gQ2hlY2sgaWYgaXQncyBpbiBGQVEgc2VjdGlvbiBvciBzaWRlYmFyIGRvY3VtZW50YXRpb25cblx0XHRcdGlmICgkKHRoaXMpLmNsb3Nlc3QoJy53cHItZmllbGRzQ29udGFpbmVyLWZpZWxkc2V0JykucHJldignLndwci1vcHRpb25IZWFkZXInKS5maW5kKCcud3ByLXRpdGxlMicpLnRleHQoKS5pbmNsdWRlcygnRnJlcXVlbnRseSBBc2tlZCBRdWVzdGlvbnMnKSkge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdGQVEgLSAnICsgdGV4dCwgJ0Rhc2hib2FyZCcpO1xuXHRcdFx0fSBlbHNlIGlmICgkKHRoaXMpLmNsb3Nlc3QoJy53cHItZG9jdW1lbnRhdGlvbicpLmxlbmd0aCA+IDApIHtcblx0XHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignRG9jdW1lbnRhdGlvbicsICdTaWRlYmFyJyk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdEb2N1bWVudGF0aW9uIExpbmsnLCAnR2VuZXJhbCcpO1xuXHRcdFx0fVxuXHRcdH1cblx0fSk7XG5cdFxuXHQvLyBUcmFjayBcIkhvdyB0byBtZWFzdXJlIGxvYWRpbmcgdGltZVwiIGxpbmtcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJ2FbaHJlZio9XCJob3ctdG8tdGVzdC13b3JkcHJlc3Mtc2l0ZS1wZXJmb3JtYW5jZVwiXScsIGZ1bmN0aW9uKCkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93LndwclRyYWNrSGVscEJ1dHRvbiA9PT0gJ2Z1bmN0aW9uJykge1xuXHRcdFx0d2luZG93LndwclRyYWNrSGVscEJ1dHRvbignTG9hZGluZyBUaW1lIEd1aWRlJywgJ1NpZGViYXInKTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIFwiTmVlZCBoZWxwP1wiIGxpbmtzIChleGlzdGluZyBoZWxwIGJ1dHRvbnMpXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLWluZm9BY3Rpb24tLWhlbHA6bm90KFtkYXRhLWJlYWNvbi1pZF0pJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdOZWVkIEhlbHAnLCAnR2VuZXJhbCcpO1xuXHRcdH1cblx0fSk7XG5cblxuXHQvKioqXG5cdCogU2hvdyBwb3BpbiBhbmFseXRpY3Ncblx0KioqL1xuXG5cdHZhciAkd3ByQW5hbHl0aWNzUG9waW4gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcycpLFxuXHRcdCR3cHJQb3Bpbk92ZXJsYXkgPSAkKCcud3ByLVBvcGluLW92ZXJsYXknKSxcblx0XHQkd3ByQW5hbHl0aWNzQ2xvc2VQb3BpbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzLWNsb3NlJyksXG5cdFx0JHdwckFuYWx5dGljc1BvcGluQnV0dG9uID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MgLndwci1idXR0b24nKSxcblx0XHQkd3ByQW5hbHl0aWNzT3BlblBvcGluID0gJCgnLndwci1qcy1wb3BpbicpXG5cdDtcblxuXHQkd3ByQW5hbHl0aWNzT3BlblBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByT3BlbkFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwckFuYWx5dGljc0Nsb3NlUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJDbG9zZUFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwckFuYWx5dGljc1BvcGluQnV0dG9uLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByQWN0aXZhdGVBbmFseXRpY3MoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwck9wZW5BbmFseXRpY3MoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAuc2V0KCR3cHJBbmFseXRpY3NQb3BpbiwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHQgIC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHQgIC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjB9LHthdXRvQWxwaGE6MSwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAuZnJvbVRvKCR3cHJBbmFseXRpY3NQb3BpbiwgMC42LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDogLTI0fSwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJDbG9zZUFuYWx5dGljcygpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC5mcm9tVG8oJHdwckFuYWx5dGljc1BvcGluLCAwLjYsIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOiAwfSwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6LTI0LCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjF9LHthdXRvQWxwaGE6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQgIC5zZXQoJHdwckFuYWx5dGljc1BvcGluLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0ICAuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJBY3RpdmF0ZUFuYWx5dGljcygpe1xuXHRcdHdwckNsb3NlQW5hbHl0aWNzKCk7XG5cdFx0JCgnI2FuYWx5dGljc19lbmFibGVkJykucHJvcCgnY2hlY2tlZCcsIHRydWUpO1xuXHRcdCQoJyNhbmFseXRpY3NfZW5hYmxlZCcpLnRyaWdnZXIoJ2NoYW5nZScpO1xuXHR9XG5cblx0Ly8gRGlzcGxheSBDVEEgd2l0aGluIHRoZSBwb3BpbiBgV2hhdCBpbmZvIHdpbGwgd2UgY29sbGVjdD9gXG5cdCQoJyNhbmFseXRpY3NfZW5hYmxlZCcpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbiAoKSB7XG5cdFx0JCgnLndwci1yb2NrZXQtYW5hbHl0aWNzLWN0YScpLnRvZ2dsZUNsYXNzKCd3cHItaXNIaWRkZW4nKTtcblx0fSk7XG5cblx0LyoqKlxuXHQqIFNob3cgcG9waW4gdXBncmFkZVxuXHQqKiovXG5cblx0dmFyICR3cHJVcGdyYWRlUG9waW4gPSAkKCcud3ByLVBvcGluLVVwZ3JhZGUnKSxcblx0JHdwclVwZ3JhZGVDbG9zZVBvcGluID0gJCgnLndwci1Qb3Bpbi1VcGdyYWRlLWNsb3NlJyksXG5cdCR3cHJVcGdyYWRlT3BlblBvcGluID0gJCgnLndwci1wb3Bpbi11cGdyYWRlLXRvZ2dsZScpO1xuXG5cdCR3cHJVcGdyYWRlT3BlblBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByT3BlblVwZ3JhZGVQb3BpbigpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwclVwZ3JhZGVDbG9zZVBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlVXBncmFkZVBvcGluKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJPcGVuVXBncmFkZVBvcGluKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKTtcblxuXHRcdHZUTC5zZXQoJHdwclVwZ3JhZGVQb3BpbiwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHRcdC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHRcdC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjB9LHthdXRvQWxwaGE6MSwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0XHQuZnJvbVRvKCR3cHJVcGdyYWRlUG9waW4sIDAuNiwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6IC0yNH0sIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNScpXG5cdFx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VVcGdyYWRlUG9waW4oKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpO1xuXG5cdFx0dlRMLmZyb21Ubygkd3ByVXBncmFkZVBvcGluLCAwLjYsIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOiAwfSwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6LTI0LCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHRcdC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjF9LHthdXRvQWxwaGE6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHRcdC5zZXQoJHdwclVwZ3JhZGVQb3BpbiwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdFx0LnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0LyoqKlxuXHQqIFNpZGViYXIgb24vb2ZmXG5cdCoqKi9cblx0dmFyICR3cHJTaWRlYmFyICAgID0gJCggJy53cHItU2lkZWJhcicgKTtcblx0dmFyICR3cHJCdXR0b25UaXBzID0gJCgnLndwci1qcy10aXBzJyk7XG5cblx0JHdwckJ1dHRvblRpcHMub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckRldGVjdFRpcHMoJCh0aGlzKSk7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckRldGVjdFRpcHMoYUVsZW0pe1xuXHRcdGlmKGFFbGVtLmlzKCc6Y2hlY2tlZCcpKXtcblx0XHRcdCR3cHJTaWRlYmFyLmNzcygnZGlzcGxheScsJ2Jsb2NrJyk7XG5cdFx0XHRsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb24nICk7XG5cdFx0fVxuXHRcdGVsc2V7XG5cdFx0XHQkd3ByU2lkZWJhci5jc3MoJ2Rpc3BsYXknLCdub25lJyk7XG5cdFx0XHRsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb2ZmJyApO1xuXHRcdH1cblx0fVxuXG5cblxuXHQvKioqXG5cdCogRGV0ZWN0IEFkYmxvY2tcblx0KioqL1xuXG5cdGlmKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdMS2dPY0NScHdtQWonKSl7XG5cdFx0JCgnLndwci1hZGJsb2NrJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblx0fSBlbHNlIHtcblx0XHQkKCcud3ByLWFkYmxvY2snKS5jc3MoJ2Rpc3BsYXknLCAnYmxvY2snKTtcblx0fVxuXG5cdHZhciAkYWRibG9jayA9ICQoJy53cHItYWRibG9jaycpO1xuXHR2YXIgJGFkYmxvY2tDbG9zZSA9ICQoJy53cHItYWRibG9jay1jbG9zZScpO1xuXG5cdCRhZGJsb2NrQ2xvc2Uub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VBZGJsb2NrTm90aWNlKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJDbG9zZUFkYmxvY2tOb3RpY2UoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAudG8oJGFkYmxvY2ssIDEsIHthdXRvQWxwaGE6MCwgeDo0MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAudG8oJGFkYmxvY2ssIDAuNCwge2hlaWdodDogMCwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNCcpXG5cdFx0ICAuc2V0KCRhZGJsb2NrLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cbn0pO1xuIiwiZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbiAoKSB7XG5cbiAgICB2YXIgJHBhZ2VNYW5hZ2VyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcIi53cHItQ29udGVudFwiKTtcbiAgICBpZigkcGFnZU1hbmFnZXIpe1xuICAgICAgICBuZXcgUGFnZU1hbmFnZXIoJHBhZ2VNYW5hZ2VyKTtcbiAgICB9XG5cbn0pO1xuXG5cbi8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qXFxcblx0XHRDTEFTUyBQQUdFTUFOQUdFUlxuXFwqLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xuLyoqXG4gKiBNYW5hZ2VzIHRoZSBkaXNwbGF5IG9mIHBhZ2VzIC8gc2VjdGlvbiBmb3IgV1AgUm9ja2V0IHBsdWdpblxuICpcbiAqIFB1YmxpYyBtZXRob2QgOlxuICAgICBkZXRlY3RJRCAtIERldGVjdCBJRCB3aXRoIGhhc2hcbiAgICAgZ2V0Qm9keVRvcCAtIEdldCBib2R5IHRvcCBwb3NpdGlvblxuXHQgY2hhbmdlIC0gRGlzcGxheXMgdGhlIGNvcnJlc3BvbmRpbmcgcGFnZVxuICpcbiAqL1xuXG5mdW5jdGlvbiBQYWdlTWFuYWdlcihhRWxlbSkge1xuXG4gICAgdmFyIHJlZlRoaXMgPSB0aGlzO1xuXG4gICAgdGhpcy4kYm9keSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItYm9keScpO1xuICAgIHRoaXMuJG1lbnVJdGVtcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItbWVudUl0ZW0nKTtcbiAgICB0aGlzLiRzdWJtaXRCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQgPiBmb3JtID4gI3dwci1vcHRpb25zLXN1Ym1pdCcpO1xuICAgIHRoaXMuJHBhZ2VzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1QYWdlJyk7XG4gICAgdGhpcy4kc2lkZWJhciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItU2lkZWJhcicpO1xuICAgIHRoaXMuJGNvbnRlbnQgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQnKTtcbiAgICB0aGlzLiR0aXBzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1Db250ZW50LXRpcHMnKTtcbiAgICB0aGlzLiRsaW5rcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItYm9keSBhJyk7XG4gICAgdGhpcy4kbWVudUl0ZW0gPSBudWxsO1xuICAgIHRoaXMuJHBhZ2UgPSBudWxsO1xuICAgIHRoaXMucGFnZUlkID0gbnVsbDtcbiAgICB0aGlzLmJvZHlUb3AgPSAwO1xuICAgIHRoaXMuYnV0dG9uVGV4dCA9IHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZTtcblxuICAgIHJlZlRoaXMuZ2V0Qm9keVRvcCgpO1xuXG4gICAgLy8gSWYgdXJsIHBhZ2UgY2hhbmdlXG4gICAgd2luZG93Lm9uaGFzaGNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuICAgICAgICByZWZUaGlzLmRldGVjdElEKCk7XG4gICAgfVxuXG4gICAgLy8gSWYgaGFzaCBhbHJlYWR5IGV4aXN0IChhZnRlciByZWZyZXNoIHBhZ2UgZm9yIGV4YW1wbGUpXG4gICAgaWYod2luZG93LmxvY2F0aW9uLmhhc2gpe1xuICAgICAgICB0aGlzLmJvZHlUb3AgPSAwO1xuICAgICAgICB0aGlzLmRldGVjdElEKCk7XG4gICAgfVxuICAgIGVsc2V7XG4gICAgICAgIHZhciBzZXNzaW9uID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ3dwci1oYXNoJyk7XG4gICAgICAgIHRoaXMuYm9keVRvcCA9IDA7XG5cbiAgICAgICAgaWYoc2Vzc2lvbil7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24uaGFzaCA9IHNlc3Npb247XG4gICAgICAgICAgICB0aGlzLmRldGVjdElEKCk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgIHRoaXMuJG1lbnVJdGVtc1swXS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgICAgICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgJ2Rhc2hib2FyZCcpO1xuICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLmhhc2ggPSAnI2Rhc2hib2FyZCc7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBDbGljayBsaW5rIHNhbWUgaGFzaFxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kbGlua3MubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kbGlua3NbaV0ub25jbGljayA9IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgcmVmVGhpcy5nZXRCb2R5VG9wKCk7XG4gICAgICAgICAgICB2YXIgaHJlZlNwbGl0ID0gdGhpcy5ocmVmLnNwbGl0KCcjJylbMV07XG4gICAgICAgICAgICBpZihocmVmU3BsaXQgPT0gcmVmVGhpcy5wYWdlSWQgJiYgaHJlZlNwbGl0ICE9IHVuZGVmaW5lZCl7XG4gICAgICAgICAgICAgICAgcmVmVGhpcy5kZXRlY3RJRCgpO1xuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfTtcbiAgICB9XG5cbiAgICAvLyBDbGljayBsaW5rcyBub3QgV1Agcm9ja2V0IHRvIHJlc2V0IGhhc2hcbiAgICB2YXIgJG90aGVybGlua3MgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcjYWRtaW5tZW51bWFpbiBhLCAjd3BhZG1pbmJhciBhJyk7XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCAkb3RoZXJsaW5rcy5sZW5ndGg7IGkrKykge1xuICAgICAgICAkb3RoZXJsaW5rc1tpXS5vbmNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCAnJyk7XG4gICAgICAgIH07XG4gICAgfVxuXG59XG5cblxuLypcbiogUGFnZSBkZXRlY3QgSURcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZGV0ZWN0SUQgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnBhZ2VJZCA9IHdpbmRvdy5sb2NhdGlvbi5oYXNoLnNwbGl0KCcjJylbMV07XG4gICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgdGhpcy5wYWdlSWQpO1xuXG4gICAgdGhpcy4kcGFnZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItUGFnZSMnICsgdGhpcy5wYWdlSWQpO1xuICAgIHRoaXMuJG1lbnVJdGVtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwci1uYXYtJyArIHRoaXMucGFnZUlkKTtcblxuICAgIHRoaXMuY2hhbmdlKCk7XG59XG5cblxuXG4vKlxuKiBHZXQgYm9keSB0b3AgcG9zaXRpb25cbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZ2V0Qm9keVRvcCA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBib2R5UG9zID0gdGhpcy4kYm9keS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICB0aGlzLmJvZHlUb3AgPSBib2R5UG9zLnRvcCArIHdpbmRvdy5wYWdlWU9mZnNldCAtIDQ3OyAvLyAjd3BhZG1pbmJhciArIHBhZGRpbmctdG9wIC53cHItd3JhcCAtIDEgLSA0N1xufVxuXG5cblxuLypcbiogUGFnZSBjaGFuZ2VcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuY2hhbmdlID0gZnVuY3Rpb24oKSB7XG5cbiAgICB2YXIgcmVmVGhpcyA9IHRoaXM7XG4gICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNjcm9sbFRvcCA9IHJlZlRoaXMuYm9keVRvcDtcblxuICAgIC8vIEhpZGUgb3RoZXIgcGFnZXNcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuJHBhZ2VzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJHBhZ2VzW2ldLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kbWVudUl0ZW1zLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJG1lbnVJdGVtc1tpXS5jbGFzc0xpc3QucmVtb3ZlKCdpc0FjdGl2ZScpO1xuICAgIH1cblxuICAgIC8vIFNob3cgY3VycmVudCBkZWZhdWx0IHBhZ2VcbiAgICB0aGlzLiRwYWdlLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcblxuICAgIGlmICggbnVsbCA9PT0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJyApICkge1xuICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb24nICk7XG4gICAgfVxuXG4gICAgaWYgKCAnb24nID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIH0gZWxzZSBpZiAoICdvZmYnID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyN3cHItanMtdGlwcycpLnJlbW92ZUF0dHJpYnV0ZSggJ2NoZWNrZWQnICk7XG4gICAgfVxuXG4gICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICB0aGlzLiRtZW51SXRlbS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZSA9IHRoaXMuYnV0dG9uVGV4dDtcbiAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5hZGQoJ2lzTm90RnVsbCcpO1xuXG4gICAgY29uc3QgcGFnZXNXaXRob3V0U3VibWl0ID0gW1xuICAgICAgICAnZGFzaGJvYXJkJyxcbiAgICAgICAgJ2FkZG9ucycsXG4gICAgICAgICdkYXRhYmFzZScsXG4gICAgICAgICd0b29scycsXG4gICAgICAgICdhZGRvbnMnLFxuICAgICAgICAnaW1hZ2lmeScsXG4gICAgICAgICd0dXRvcmlhbHMnLFxuICAgICAgICAncGx1Z2lucycsXG4gICAgXTtcblxuICAgIC8vIEV4Y2VwdGlvbiBmb3IgZGFzaGJvYXJkXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXNoYm9hcmRcIil7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5yZW1vdmUoJ2lzTm90RnVsbCcpO1xuICAgIH1cblxuICAgIGlmICh0aGlzLnBhZ2VJZCA9PSBcImltYWdpZnlcIikge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIHRoaXMuJHRpcHMuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICBpZiAocGFnZXNXaXRob3V0U3VibWl0LmluY2x1ZGVzKHRoaXMucGFnZUlkKSkge1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG59O1xuIiwiLyplc2xpbnQtZW52IGVzNiovXG4oICggZG9jdW1lbnQsIHdpbmRvdyApID0+IHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLXJvY2tldGNkbi1vcGVuJyApLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRlbC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cblx0XHRtYXliZU9wZW5Nb2RhbCgpO1xuXG5cdFx0TWljcm9Nb2RhbC5pbml0KCB7XG5cdFx0XHRkaXNhYmxlU2Nyb2xsOiB0cnVlXG5cdFx0fSApO1xuXG5cdFx0Y29uc3QgaWZyYW1lID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JvY2tldGNkbi1pZnJhbWUnKTtcblx0XHRjb25zdCBsb2FkZXIgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3ByLXJvY2tldGNkbi1tb2RhbC1sb2FkZXInKTtcblx0XHRpZiAoIGlmcmFtZSAmJiBsb2FkZXIgKSB7XG5cdFx0XHRpZnJhbWUuYWRkRXZlbnRMaXN0ZW5lcignbG9hZCcsIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRsb2FkZXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0XHRcdH0pO1xuXHRcdH1cblx0fSApO1xuXG5cdHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsICgpID0+IHtcblx0XHRsZXQgb3BlbkNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1vcGVuLWN0YScgKSxcblx0XHRcdGNsb3NlQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWNsb3NlLWN0YScgKSxcblx0XHRcdHNtYWxsQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YS1zbWFsbCcgKSxcblx0XHRcdGJpZ0NUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jdGEnICk7XG5cblx0XHRpZiAoIG51bGwgIT09IG9wZW5DVEEgJiYgbnVsbCAhPT0gc21hbGxDVEEgJiYgbnVsbCAhPT0gYmlnQ1RBICkge1xuXHRcdFx0b3BlbkNUQS5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdFx0XHRzbWFsbENUQS5jbGFzc0xpc3QuYWRkKCAnd3ByLWlzSGlkZGVuJyApO1xuXHRcdFx0XHRiaWdDVEEuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1pc0hpZGRlbicgKTtcblxuXHRcdFx0XHRzZW5kSFRUUFJlcXVlc3QoIGdldFBvc3REYXRhKCAnYmlnJyApICk7XG5cdFx0XHR9ICk7XG5cdFx0fVxuXG5cdFx0aWYgKCBudWxsICE9PSBjbG9zZUNUQSAmJiBudWxsICE9PSBzbWFsbENUQSAmJiBudWxsICE9PSBiaWdDVEEgKSB7XG5cdFx0XHRjbG9zZUNUQS5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdFx0XHRzbWFsbENUQS5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWlzSGlkZGVuJyApO1xuXHRcdFx0XHRiaWdDVEEuY2xhc3NMaXN0LmFkZCggJ3dwci1pc0hpZGRlbicgKTtcblxuXHRcdFx0XHRzZW5kSFRUUFJlcXVlc3QoIGdldFBvc3REYXRhKCAnc21hbGwnICkgKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHRmdW5jdGlvbiBnZXRQb3N0RGF0YSggc3RhdHVzICkge1xuXHRcdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRcdHBvc3REYXRhICs9ICdhY3Rpb249dG9nZ2xlX3JvY2tldGNkbl9jdGEnO1xuXHRcdFx0cG9zdERhdGEgKz0gJyZzdGF0dXM9JyArIHN0YXR1cztcblx0XHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRcdHJldHVybiBwb3N0RGF0YTtcblx0XHR9XG5cdH0gKTtcblxuXHR3aW5kb3cub25tZXNzYWdlID0gKCBlICkgPT4ge1xuXHRcdGNvbnN0IGlmcmFtZVVSTCA9IHJvY2tldF9hamF4X2RhdGEub3JpZ2luX3VybDtcblxuXHRcdGlmICggZS5vcmlnaW4gIT09IGlmcmFtZVVSTCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRzZXRDRE5GcmFtZUhlaWdodCggZS5kYXRhICk7XG5cdFx0Y2xvc2VNb2RhbCggZS5kYXRhICk7XG5cdFx0dG9rZW5IYW5kbGVyKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHByb2Nlc3NTdGF0dXMoIGUuZGF0YSApO1xuXHRcdGVuYWJsZUNETiggZS5kYXRhLCBpZnJhbWVVUkwgKTtcblx0XHRkaXNhYmxlQ0ROKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZS5kYXRhICk7XG5cdH07XG5cblx0ZnVuY3Rpb24gbWF5YmVPcGVuTW9kYWwoKSB7XG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9wcm9jZXNzX3N0YXR1cyc7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXG5cdFx0XHRcdGlmICggdHJ1ZSA9PT0gcmVzcG9uc2VUeHQuc3VjY2VzcyApIHtcblx0XHRcdFx0XHRNaWNyb01vZGFsLnNob3coICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIGNsb3NlTW9kYWwoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5GcmFtZUNsb3NlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdE1pY3JvTW9kYWwuY2xvc2UoICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXG5cdFx0bGV0IHBhZ2VzID0gWyAnaWZyYW1lLXBheW1lbnQtc3VjY2VzcycsICdpZnJhbWUtdW5zdWJzY3JpYmUtc3VjY2VzcycgXTtcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAnY2RuX3BhZ2VfbWVzc2FnZScgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRpZiAoIHBhZ2VzLmluZGV4T2YoIGRhdGEuY2RuX3BhZ2VfbWVzc2FnZSApID09PSAtMSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5sb2NhdGlvbi5yZWxvYWQoKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHByb2Nlc3NTdGF0dXMoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fcHJvY2VzcycgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc2V0Jztcblx0XHRwb3N0RGF0YSArPSAnJnN0YXR1cz0nICsgZGF0YS5yb2NrZXRjZG5fcHJvY2Vzcztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0c2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXHR9XG5cblx0ZnVuY3Rpb24gZW5hYmxlQ0ROKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdXJsJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fZW5hYmxlJztcblx0XHRwb3N0RGF0YSArPSAnJmNkbl91cmw9JyArIGRhdGEucm9ja2V0Y2RuX3VybDtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIGRpc2FibGVDRE4oIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl9kaXNhYmxlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fZGlzYWJsZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICkge1xuXHRcdGNvbnN0IGh0dHBSZXF1ZXN0ID0gbmV3IFhNTEh0dHBSZXF1ZXN0KCk7XG5cblx0XHRodHRwUmVxdWVzdC5vcGVuKCAnUE9TVCcsIGFqYXh1cmwgKTtcblx0XHRodHRwUmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKCAnQ29udGVudC1UeXBlJywgJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCcgKTtcblx0XHRodHRwUmVxdWVzdC5zZW5kKCBwb3N0RGF0YSApO1xuXG5cdFx0cmV0dXJuIGh0dHBSZXF1ZXN0O1xuXHR9XG5cblx0ZnVuY3Rpb24gc2V0Q0RORnJhbWVIZWlnaHQoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5GcmFtZUhlaWdodCcgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ3JvY2tldGNkbi1pZnJhbWUnICkuc3R5bGUuaGVpZ2h0ID0gYCR7IGRhdGEuY2RuRnJhbWVIZWlnaHQgfXB4YDtcblx0fVxuXG5cdGZ1bmN0aW9uIHRva2VuSGFuZGxlciggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3Rva2VuJyApICkge1xuXHRcdFx0bGV0IGRhdGEgPSB7cHJvY2VzczpcInN1YnNjcmliZVwiLCBtZXNzYWdlOlwidG9rZW5fbm90X3JlY2VpdmVkXCJ9O1xuXHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHR7XG5cdFx0XHRcdFx0J3N1Y2Nlc3MnOiBmYWxzZSxcblx0XHRcdFx0XHQnZGF0YSc6IGRhdGEsXG5cdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0fSxcblx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHQpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1zYXZlX3JvY2tldGNkbl90b2tlbic7XG5cdFx0cG9zdERhdGEgKz0gJyZ2YWx1ZT0nICsgZGF0YS5yb2NrZXRjZG5fdG9rZW47XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiB2YWxpZGF0ZVRva2VuQW5kQ05BTUUoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW4nICkgfHwgISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3ZhbGlkYXRlX2NuYW1lJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW5fY25hbWUnO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3VybD0nICsgZGF0YS5yb2NrZXRjZG5fdmFsaWRhdGVfY25hbWU7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdG9rZW49JyArIGRhdGEucm9ja2V0Y2RuX3ZhbGlkYXRlX3Rva2VuO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXHR9XG59ICkoIGRvY3VtZW50LCB3aW5kb3cgKTtcbiIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcIlRpbWVsaW5lTGl0ZVwiLFtcImNvcmUuQW5pbWF0aW9uXCIsXCJjb3JlLlNpbXBsZVRpbWVsaW5lXCIsXCJUd2VlbkxpdGVcIl0sZnVuY3Rpb24odCxlLGkpe3ZhciBzPWZ1bmN0aW9uKHQpe2UuY2FsbCh0aGlzLHQpLHRoaXMuX2xhYmVscz17fSx0aGlzLmF1dG9SZW1vdmVDaGlsZHJlbj10aGlzLnZhcnMuYXV0b1JlbW92ZUNoaWxkcmVuPT09ITAsdGhpcy5zbW9vdGhDaGlsZFRpbWluZz10aGlzLnZhcnMuc21vb3RoQ2hpbGRUaW1pbmc9PT0hMCx0aGlzLl9zb3J0Q2hpbGRyZW49ITAsdGhpcy5fb25VcGRhdGU9dGhpcy52YXJzLm9uVXBkYXRlO3ZhciBpLHMscj10aGlzLnZhcnM7Zm9yKHMgaW4gcilpPXJbc10sYShpKSYmLTEhPT1pLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKSYmKHJbc109dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhpKSk7YShyLnR3ZWVucykmJnRoaXMuYWRkKHIudHdlZW5zLDAsci5hbGlnbixyLnN0YWdnZXIpfSxyPTFlLTEwLG49aS5faW50ZXJuYWxzLmlzU2VsZWN0b3IsYT1pLl9pbnRlcm5hbHMuaXNBcnJheSxvPVtdLGg9d2luZG93Ll9nc0RlZmluZS5nbG9iYWxzLGw9ZnVuY3Rpb24odCl7dmFyIGUsaT17fTtmb3IoZSBpbiB0KWlbZV09dFtlXTtyZXR1cm4gaX0sXz1mdW5jdGlvbih0LGUsaSxzKXt0Ll90aW1lbGluZS5wYXVzZSh0Ll9zdGFydFRpbWUpLGUmJmUuYXBwbHkoc3x8dC5fdGltZWxpbmUsaXx8byl9LHU9by5zbGljZSxmPXMucHJvdG90eXBlPW5ldyBlO3JldHVybiBzLnZlcnNpb249XCIxLjEyLjFcIixmLmNvbnN0cnVjdG9yPXMsZi5raWxsKCkuX2djPSExLGYudG89ZnVuY3Rpb24odCxlLHMscil7dmFyIG49cy5yZXBlYXQmJmguVHdlZW5NYXh8fGk7cmV0dXJuIGU/dGhpcy5hZGQobmV3IG4odCxlLHMpLHIpOnRoaXMuc2V0KHQscyxyKX0sZi5mcm9tPWZ1bmN0aW9uKHQsZSxzLHIpe3JldHVybiB0aGlzLmFkZCgocy5yZXBlYXQmJmguVHdlZW5NYXh8fGkpLmZyb20odCxlLHMpLHIpfSxmLmZyb21Ubz1mdW5jdGlvbih0LGUscyxyLG4pe3ZhciBhPXIucmVwZWF0JiZoLlR3ZWVuTWF4fHxpO3JldHVybiBlP3RoaXMuYWRkKGEuZnJvbVRvKHQsZSxzLHIpLG4pOnRoaXMuc2V0KHQscixuKX0sZi5zdGFnZ2VyVG89ZnVuY3Rpb24odCxlLHIsYSxvLGgsXyxmKXt2YXIgcCxjPW5ldyBzKHtvbkNvbXBsZXRlOmgsb25Db21wbGV0ZVBhcmFtczpfLG9uQ29tcGxldGVTY29wZTpmLHNtb290aENoaWxkVGltaW5nOnRoaXMuc21vb3RoQ2hpbGRUaW1pbmd9KTtmb3IoXCJzdHJpbmdcIj09dHlwZW9mIHQmJih0PWkuc2VsZWN0b3IodCl8fHQpLG4odCkmJih0PXUuY2FsbCh0LDApKSxhPWF8fDAscD0wO3QubGVuZ3RoPnA7cCsrKXIuc3RhcnRBdCYmKHIuc3RhcnRBdD1sKHIuc3RhcnRBdCkpLGMudG8odFtwXSxlLGwocikscCphKTtyZXR1cm4gdGhpcy5hZGQoYyxvKX0sZi5zdGFnZ2VyRnJvbT1mdW5jdGlvbih0LGUsaSxzLHIsbixhLG8pe3JldHVybiBpLmltbWVkaWF0ZVJlbmRlcj0wIT1pLmltbWVkaWF0ZVJlbmRlcixpLnJ1bkJhY2t3YXJkcz0hMCx0aGlzLnN0YWdnZXJUbyh0LGUsaSxzLHIsbixhLG8pfSxmLnN0YWdnZXJGcm9tVG89ZnVuY3Rpb24odCxlLGkscyxyLG4sYSxvLGgpe3JldHVybiBzLnN0YXJ0QXQ9aSxzLmltbWVkaWF0ZVJlbmRlcj0wIT1zLmltbWVkaWF0ZVJlbmRlciYmMCE9aS5pbW1lZGlhdGVSZW5kZXIsdGhpcy5zdGFnZ2VyVG8odCxlLHMscixuLGEsbyxoKX0sZi5jYWxsPWZ1bmN0aW9uKHQsZSxzLHIpe3JldHVybiB0aGlzLmFkZChpLmRlbGF5ZWRDYWxsKDAsdCxlLHMpLHIpfSxmLnNldD1mdW5jdGlvbih0LGUscyl7cmV0dXJuIHM9dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChzLDAsITApLG51bGw9PWUuaW1tZWRpYXRlUmVuZGVyJiYoZS5pbW1lZGlhdGVSZW5kZXI9cz09PXRoaXMuX3RpbWUmJiF0aGlzLl9wYXVzZWQpLHRoaXMuYWRkKG5ldyBpKHQsMCxlKSxzKX0scy5leHBvcnRSb290PWZ1bmN0aW9uKHQsZSl7dD10fHx7fSxudWxsPT10LnNtb290aENoaWxkVGltaW5nJiYodC5zbW9vdGhDaGlsZFRpbWluZz0hMCk7dmFyIHIsbixhPW5ldyBzKHQpLG89YS5fdGltZWxpbmU7Zm9yKG51bGw9PWUmJihlPSEwKSxvLl9yZW1vdmUoYSwhMCksYS5fc3RhcnRUaW1lPTAsYS5fcmF3UHJldlRpbWU9YS5fdGltZT1hLl90b3RhbFRpbWU9by5fdGltZSxyPW8uX2ZpcnN0O3I7KW49ci5fbmV4dCxlJiZyIGluc3RhbmNlb2YgaSYmci50YXJnZXQ9PT1yLnZhcnMub25Db21wbGV0ZXx8YS5hZGQocixyLl9zdGFydFRpbWUtci5fZGVsYXkpLHI9bjtyZXR1cm4gby5hZGQoYSwwKSxhfSxmLmFkZD1mdW5jdGlvbihyLG4sbyxoKXt2YXIgbCxfLHUsZixwLGM7aWYoXCJudW1iZXJcIiE9dHlwZW9mIG4mJihuPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwobiwwLCEwLHIpKSwhKHIgaW5zdGFuY2VvZiB0KSl7aWYociBpbnN0YW5jZW9mIEFycmF5fHxyJiZyLnB1c2gmJmEocikpe2ZvcihvPW98fFwibm9ybWFsXCIsaD1ofHwwLGw9bixfPXIubGVuZ3RoLHU9MDtfPnU7dSsrKWEoZj1yW3VdKSYmKGY9bmV3IHMoe3R3ZWVuczpmfSkpLHRoaXMuYWRkKGYsbCksXCJzdHJpbmdcIiE9dHlwZW9mIGYmJlwiZnVuY3Rpb25cIiE9dHlwZW9mIGYmJihcInNlcXVlbmNlXCI9PT1vP2w9Zi5fc3RhcnRUaW1lK2YudG90YWxEdXJhdGlvbigpL2YuX3RpbWVTY2FsZTpcInN0YXJ0XCI9PT1vJiYoZi5fc3RhcnRUaW1lLT1mLmRlbGF5KCkpKSxsKz1oO3JldHVybiB0aGlzLl91bmNhY2hlKCEwKX1pZihcInN0cmluZ1wiPT10eXBlb2YgcilyZXR1cm4gdGhpcy5hZGRMYWJlbChyLG4pO2lmKFwiZnVuY3Rpb25cIiE9dHlwZW9mIHIpdGhyb3dcIkNhbm5vdCBhZGQgXCIrcitcIiBpbnRvIHRoZSB0aW1lbGluZTsgaXQgaXMgbm90IGEgdHdlZW4sIHRpbWVsaW5lLCBmdW5jdGlvbiwgb3Igc3RyaW5nLlwiO3I9aS5kZWxheWVkQ2FsbCgwLHIpfWlmKGUucHJvdG90eXBlLmFkZC5jYWxsKHRoaXMscixuKSwodGhpcy5fZ2N8fHRoaXMuX3RpbWU9PT10aGlzLl9kdXJhdGlvbikmJiF0aGlzLl9wYXVzZWQmJnRoaXMuX2R1cmF0aW9uPHRoaXMuZHVyYXRpb24oKSlmb3IocD10aGlzLGM9cC5yYXdUaW1lKCk+ci5fc3RhcnRUaW1lO3AuX3RpbWVsaW5lOyljJiZwLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZz9wLnRvdGFsVGltZShwLl90b3RhbFRpbWUsITApOnAuX2djJiZwLl9lbmFibGVkKCEwLCExKSxwPXAuX3RpbWVsaW5lO3JldHVybiB0aGlzfSxmLnJlbW92ZT1mdW5jdGlvbihlKXtpZihlIGluc3RhbmNlb2YgdClyZXR1cm4gdGhpcy5fcmVtb3ZlKGUsITEpO2lmKGUgaW5zdGFuY2VvZiBBcnJheXx8ZSYmZS5wdXNoJiZhKGUpKXtmb3IodmFyIGk9ZS5sZW5ndGg7LS1pPi0xOyl0aGlzLnJlbW92ZShlW2ldKTtyZXR1cm4gdGhpc31yZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgZT90aGlzLnJlbW92ZUxhYmVsKGUpOnRoaXMua2lsbChudWxsLGUpfSxmLl9yZW1vdmU9ZnVuY3Rpb24odCxpKXtlLnByb3RvdHlwZS5fcmVtb3ZlLmNhbGwodGhpcyx0LGkpO3ZhciBzPXRoaXMuX2xhc3Q7cmV0dXJuIHM/dGhpcy5fdGltZT5zLl9zdGFydFRpbWUrcy5fdG90YWxEdXJhdGlvbi9zLl90aW1lU2NhbGUmJih0aGlzLl90aW1lPXRoaXMuZHVyYXRpb24oKSx0aGlzLl90b3RhbFRpbWU9dGhpcy5fdG90YWxEdXJhdGlvbik6dGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9dGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj0wLHRoaXN9LGYuYXBwZW5kPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuYWRkKHQsdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChudWxsLGUsITAsdCkpfSxmLmluc2VydD1mLmluc2VydE11bHRpcGxlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmFkZCh0LGV8fDAsaSxzKX0sZi5hcHBlbmRNdWx0aXBsZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5hZGQodCx0aGlzLl9wYXJzZVRpbWVPckxhYmVsKG51bGwsZSwhMCx0KSxpLHMpfSxmLmFkZExhYmVsPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuX2xhYmVsc1t0XT10aGlzLl9wYXJzZVRpbWVPckxhYmVsKGUpLHRoaXN9LGYuYWRkUGF1c2U9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuY2FsbChfLFtcIntzZWxmfVwiLGUsaSxzXSx0aGlzLHQpfSxmLnJlbW92ZUxhYmVsPWZ1bmN0aW9uKHQpe3JldHVybiBkZWxldGUgdGhpcy5fbGFiZWxzW3RdLHRoaXN9LGYuZ2V0TGFiZWxUaW1lPWZ1bmN0aW9uKHQpe3JldHVybiBudWxsIT10aGlzLl9sYWJlbHNbdF0/dGhpcy5fbGFiZWxzW3RdOi0xfSxmLl9wYXJzZVRpbWVPckxhYmVsPWZ1bmN0aW9uKGUsaSxzLHIpe3ZhciBuO2lmKHIgaW5zdGFuY2VvZiB0JiZyLnRpbWVsaW5lPT09dGhpcyl0aGlzLnJlbW92ZShyKTtlbHNlIGlmKHImJihyIGluc3RhbmNlb2YgQXJyYXl8fHIucHVzaCYmYShyKSkpZm9yKG49ci5sZW5ndGg7LS1uPi0xOylyW25daW5zdGFuY2VvZiB0JiZyW25dLnRpbWVsaW5lPT09dGhpcyYmdGhpcy5yZW1vdmUocltuXSk7aWYoXCJzdHJpbmdcIj09dHlwZW9mIGkpcmV0dXJuIHRoaXMuX3BhcnNlVGltZU9yTGFiZWwoaSxzJiZcIm51bWJlclwiPT10eXBlb2YgZSYmbnVsbD09dGhpcy5fbGFiZWxzW2ldP2UtdGhpcy5kdXJhdGlvbigpOjAscyk7aWYoaT1pfHwwLFwic3RyaW5nXCIhPXR5cGVvZiBlfHwhaXNOYU4oZSkmJm51bGw9PXRoaXMuX2xhYmVsc1tlXSludWxsPT1lJiYoZT10aGlzLmR1cmF0aW9uKCkpO2Vsc2V7aWYobj1lLmluZGV4T2YoXCI9XCIpLC0xPT09bilyZXR1cm4gbnVsbD09dGhpcy5fbGFiZWxzW2VdP3M/dGhpcy5fbGFiZWxzW2VdPXRoaXMuZHVyYXRpb24oKStpOmk6dGhpcy5fbGFiZWxzW2VdK2k7aT1wYXJzZUludChlLmNoYXJBdChuLTEpK1wiMVwiLDEwKSpOdW1iZXIoZS5zdWJzdHIobisxKSksZT1uPjE/dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChlLnN1YnN0cigwLG4tMSksMCxzKTp0aGlzLmR1cmF0aW9uKCl9cmV0dXJuIE51bWJlcihlKStpfSxmLnNlZWs9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy50b3RhbFRpbWUoXCJudW1iZXJcIj09dHlwZW9mIHQ/dDp0aGlzLl9wYXJzZVRpbWVPckxhYmVsKHQpLGUhPT0hMSl9LGYuc3RvcD1mdW5jdGlvbigpe3JldHVybiB0aGlzLnBhdXNlZCghMCl9LGYuZ290b0FuZFBsYXk9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5wbGF5KHQsZSl9LGYuZ290b0FuZFN0b3A9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5wYXVzZSh0LGUpfSxmLnJlbmRlcj1mdW5jdGlvbih0LGUsaSl7dGhpcy5fZ2MmJnRoaXMuX2VuYWJsZWQoITAsITEpO3ZhciBzLG4sYSxoLGwsXz10aGlzLl9kaXJ0eT90aGlzLnRvdGFsRHVyYXRpb24oKTp0aGlzLl90b3RhbER1cmF0aW9uLHU9dGhpcy5fdGltZSxmPXRoaXMuX3N0YXJ0VGltZSxwPXRoaXMuX3RpbWVTY2FsZSxjPXRoaXMuX3BhdXNlZDtpZih0Pj1fPyh0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT1fLHRoaXMuX3JldmVyc2VkfHx0aGlzLl9oYXNQYXVzZWRDaGlsZCgpfHwobj0hMCxoPVwib25Db21wbGV0ZVwiLDA9PT10aGlzLl9kdXJhdGlvbiYmKDA9PT10fHwwPnRoaXMuX3Jhd1ByZXZUaW1lfHx0aGlzLl9yYXdQcmV2VGltZT09PXIpJiZ0aGlzLl9yYXdQcmV2VGltZSE9PXQmJnRoaXMuX2ZpcnN0JiYobD0hMCx0aGlzLl9yYXdQcmV2VGltZT5yJiYoaD1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIpKSksdGhpcy5fcmF3UHJldlRpbWU9dGhpcy5fZHVyYXRpb258fCFlfHx0fHx0aGlzLl9yYXdQcmV2VGltZT09PXQ/dDpyLHQ9XysxZS00KToxZS03PnQ/KHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPTAsKDAhPT11fHwwPT09dGhpcy5fZHVyYXRpb24mJnRoaXMuX3Jhd1ByZXZUaW1lIT09ciYmKHRoaXMuX3Jhd1ByZXZUaW1lPjB8fDA+dCYmdGhpcy5fcmF3UHJldlRpbWU+PTApKSYmKGg9XCJvblJldmVyc2VDb21wbGV0ZVwiLG49dGhpcy5fcmV2ZXJzZWQpLDA+dD8odGhpcy5fYWN0aXZlPSExLDA9PT10aGlzLl9kdXJhdGlvbiYmdGhpcy5fcmF3UHJldlRpbWU+PTAmJnRoaXMuX2ZpcnN0JiYobD0hMCksdGhpcy5fcmF3UHJldlRpbWU9dCk6KHRoaXMuX3Jhd1ByZXZUaW1lPXRoaXMuX2R1cmF0aW9ufHwhZXx8dHx8dGhpcy5fcmF3UHJldlRpbWU9PT10P3Q6cix0PTAsdGhpcy5faW5pdHRlZHx8KGw9ITApKSk6dGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9dGhpcy5fcmF3UHJldlRpbWU9dCx0aGlzLl90aW1lIT09dSYmdGhpcy5fZmlyc3R8fGl8fGwpe2lmKHRoaXMuX2luaXR0ZWR8fCh0aGlzLl9pbml0dGVkPSEwKSx0aGlzLl9hY3RpdmV8fCF0aGlzLl9wYXVzZWQmJnRoaXMuX3RpbWUhPT11JiZ0PjAmJih0aGlzLl9hY3RpdmU9ITApLDA9PT11JiZ0aGlzLnZhcnMub25TdGFydCYmMCE9PXRoaXMuX3RpbWUmJihlfHx0aGlzLnZhcnMub25TdGFydC5hcHBseSh0aGlzLnZhcnMub25TdGFydFNjb3BlfHx0aGlzLHRoaXMudmFycy5vblN0YXJ0UGFyYW1zfHxvKSksdGhpcy5fdGltZT49dSlmb3Iocz10aGlzLl9maXJzdDtzJiYoYT1zLl9uZXh0LCF0aGlzLl9wYXVzZWR8fGMpOykocy5fYWN0aXZlfHxzLl9zdGFydFRpbWU8PXRoaXMuX3RpbWUmJiFzLl9wYXVzZWQmJiFzLl9nYykmJihzLl9yZXZlcnNlZD9zLnJlbmRlcigocy5fZGlydHk/cy50b3RhbER1cmF0aW9uKCk6cy5fdG90YWxEdXJhdGlvbiktKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKTpzLnJlbmRlcigodC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpKSxzPWE7ZWxzZSBmb3Iocz10aGlzLl9sYXN0O3MmJihhPXMuX3ByZXYsIXRoaXMuX3BhdXNlZHx8Yyk7KShzLl9hY3RpdmV8fHU+PXMuX3N0YXJ0VGltZSYmIXMuX3BhdXNlZCYmIXMuX2djKSYmKHMuX3JldmVyc2VkP3MucmVuZGVyKChzLl9kaXJ0eT9zLnRvdGFsRHVyYXRpb24oKTpzLl90b3RhbER1cmF0aW9uKS0odC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpOnMucmVuZGVyKCh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSkpLHM9YTt0aGlzLl9vblVwZGF0ZSYmKGV8fHRoaXMuX29uVXBkYXRlLmFwcGx5KHRoaXMudmFycy5vblVwZGF0ZVNjb3BlfHx0aGlzLHRoaXMudmFycy5vblVwZGF0ZVBhcmFtc3x8bykpLGgmJih0aGlzLl9nY3x8KGY9PT10aGlzLl9zdGFydFRpbWV8fHAhPT10aGlzLl90aW1lU2NhbGUpJiYoMD09PXRoaXMuX3RpbWV8fF8+PXRoaXMudG90YWxEdXJhdGlvbigpKSYmKG4mJih0aGlzLl90aW1lbGluZS5hdXRvUmVtb3ZlQ2hpbGRyZW4mJnRoaXMuX2VuYWJsZWQoITEsITEpLHRoaXMuX2FjdGl2ZT0hMSksIWUmJnRoaXMudmFyc1toXSYmdGhpcy52YXJzW2hdLmFwcGx5KHRoaXMudmFyc1toK1wiU2NvcGVcIl18fHRoaXMsdGhpcy52YXJzW2grXCJQYXJhbXNcIl18fG8pKSl9fSxmLl9oYXNQYXVzZWRDaGlsZD1mdW5jdGlvbigpe2Zvcih2YXIgdD10aGlzLl9maXJzdDt0Oyl7aWYodC5fcGF1c2VkfHx0IGluc3RhbmNlb2YgcyYmdC5faGFzUGF1c2VkQ2hpbGQoKSlyZXR1cm4hMDt0PXQuX25leHR9cmV0dXJuITF9LGYuZ2V0Q2hpbGRyZW49ZnVuY3Rpb24odCxlLHMscil7cj1yfHwtOTk5OTk5OTk5OTtmb3IodmFyIG49W10sYT10aGlzLl9maXJzdCxvPTA7YTspcj5hLl9zdGFydFRpbWV8fChhIGluc3RhbmNlb2YgaT9lIT09ITEmJihuW28rK109YSk6KHMhPT0hMSYmKG5bbysrXT1hKSx0IT09ITEmJihuPW4uY29uY2F0KGEuZ2V0Q2hpbGRyZW4oITAsZSxzKSksbz1uLmxlbmd0aCkpKSxhPWEuX25leHQ7cmV0dXJuIG59LGYuZ2V0VHdlZW5zT2Y9ZnVuY3Rpb24odCxlKXt2YXIgcyxyLG49dGhpcy5fZ2MsYT1bXSxvPTA7Zm9yKG4mJnRoaXMuX2VuYWJsZWQoITAsITApLHM9aS5nZXRUd2VlbnNPZih0KSxyPXMubGVuZ3RoOy0tcj4tMTspKHNbcl0udGltZWxpbmU9PT10aGlzfHxlJiZ0aGlzLl9jb250YWlucyhzW3JdKSkmJihhW28rK109c1tyXSk7cmV0dXJuIG4mJnRoaXMuX2VuYWJsZWQoITEsITApLGF9LGYuX2NvbnRhaW5zPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10LnRpbWVsaW5lO2U7KXtpZihlPT09dGhpcylyZXR1cm4hMDtlPWUudGltZWxpbmV9cmV0dXJuITF9LGYuc2hpZnRDaGlsZHJlbj1mdW5jdGlvbih0LGUsaSl7aT1pfHwwO2Zvcih2YXIgcyxyPXRoaXMuX2ZpcnN0LG49dGhpcy5fbGFiZWxzO3I7KXIuX3N0YXJ0VGltZT49aSYmKHIuX3N0YXJ0VGltZSs9dCkscj1yLl9uZXh0O2lmKGUpZm9yKHMgaW4gbiluW3NdPj1pJiYobltzXSs9dCk7cmV0dXJuIHRoaXMuX3VuY2FjaGUoITApfSxmLl9raWxsPWZ1bmN0aW9uKHQsZSl7aWYoIXQmJiFlKXJldHVybiB0aGlzLl9lbmFibGVkKCExLCExKTtmb3IodmFyIGk9ZT90aGlzLmdldFR3ZWVuc09mKGUpOnRoaXMuZ2V0Q2hpbGRyZW4oITAsITAsITEpLHM9aS5sZW5ndGgscj0hMTstLXM+LTE7KWlbc10uX2tpbGwodCxlKSYmKHI9ITApO3JldHVybiByfSxmLmNsZWFyPWZ1bmN0aW9uKHQpe3ZhciBlPXRoaXMuZ2V0Q2hpbGRyZW4oITEsITAsITApLGk9ZS5sZW5ndGg7Zm9yKHRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPTA7LS1pPi0xOyllW2ldLl9lbmFibGVkKCExLCExKTtyZXR1cm4gdCE9PSExJiYodGhpcy5fbGFiZWxzPXt9KSx0aGlzLl91bmNhY2hlKCEwKX0sZi5pbnZhbGlkYXRlPWZ1bmN0aW9uKCl7Zm9yKHZhciB0PXRoaXMuX2ZpcnN0O3Q7KXQuaW52YWxpZGF0ZSgpLHQ9dC5fbmV4dDtyZXR1cm4gdGhpc30sZi5fZW5hYmxlZD1mdW5jdGlvbih0LGkpe2lmKHQ9PT10aGlzLl9nYylmb3IodmFyIHM9dGhpcy5fZmlyc3Q7czspcy5fZW5hYmxlZCh0LCEwKSxzPXMuX25leHQ7cmV0dXJuIGUucHJvdG90eXBlLl9lbmFibGVkLmNhbGwodGhpcyx0LGkpfSxmLmR1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPygwIT09dGhpcy5kdXJhdGlvbigpJiYwIT09dCYmdGhpcy50aW1lU2NhbGUodGhpcy5fZHVyYXRpb24vdCksdGhpcyk6KHRoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKSx0aGlzLl9kdXJhdGlvbil9LGYudG90YWxEdXJhdGlvbj1mdW5jdGlvbih0KXtpZighYXJndW1lbnRzLmxlbmd0aCl7aWYodGhpcy5fZGlydHkpe2Zvcih2YXIgZSxpLHM9MCxyPXRoaXMuX2xhc3Qsbj05OTk5OTk5OTk5OTk7cjspZT1yLl9wcmV2LHIuX2RpcnR5JiZyLnRvdGFsRHVyYXRpb24oKSxyLl9zdGFydFRpbWU+biYmdGhpcy5fc29ydENoaWxkcmVuJiYhci5fcGF1c2VkP3RoaXMuYWRkKHIsci5fc3RhcnRUaW1lLXIuX2RlbGF5KTpuPXIuX3N0YXJ0VGltZSwwPnIuX3N0YXJ0VGltZSYmIXIuX3BhdXNlZCYmKHMtPXIuX3N0YXJ0VGltZSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmKHRoaXMuX3N0YXJ0VGltZSs9ci5fc3RhcnRUaW1lL3RoaXMuX3RpbWVTY2FsZSksdGhpcy5zaGlmdENoaWxkcmVuKC1yLl9zdGFydFRpbWUsITEsLTk5OTk5OTk5OTkpLG49MCksaT1yLl9zdGFydFRpbWUrci5fdG90YWxEdXJhdGlvbi9yLl90aW1lU2NhbGUsaT5zJiYocz1pKSxyPWU7dGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj1zLHRoaXMuX2RpcnR5PSExfXJldHVybiB0aGlzLl90b3RhbER1cmF0aW9ufXJldHVybiAwIT09dGhpcy50b3RhbER1cmF0aW9uKCkmJjAhPT10JiZ0aGlzLnRpbWVTY2FsZSh0aGlzLl90b3RhbER1cmF0aW9uL3QpLHRoaXN9LGYudXNlc0ZyYW1lcz1mdW5jdGlvbigpe2Zvcih2YXIgZT10aGlzLl90aW1lbGluZTtlLl90aW1lbGluZTspZT1lLl90aW1lbGluZTtyZXR1cm4gZT09PXQuX3Jvb3RGcmFtZXNUaW1lbGluZX0sZi5yYXdUaW1lPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3BhdXNlZD90aGlzLl90b3RhbFRpbWU6KHRoaXMuX3RpbWVsaW5lLnJhd1RpbWUoKS10aGlzLl9zdGFydFRpbWUpKnRoaXMuX3RpbWVTY2FsZX0sc30sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKGZ1bmN0aW9uKHQpe1widXNlIHN0cmljdFwiO3ZhciBlPXQuR3JlZW5Tb2NrR2xvYmFsc3x8dDtpZighZS5Ud2VlbkxpdGUpe3ZhciBpLHMsbixyLGEsbz1mdW5jdGlvbih0KXt2YXIgaSxzPXQuc3BsaXQoXCIuXCIpLG49ZTtmb3IoaT0wO3MubGVuZ3RoPmk7aSsrKW5bc1tpXV09bj1uW3NbaV1dfHx7fTtyZXR1cm4gbn0sbD1vKFwiY29tLmdyZWVuc29ja1wiKSxoPTFlLTEwLF89W10uc2xpY2UsdT1mdW5jdGlvbigpe30sbT1mdW5jdGlvbigpe3ZhciB0PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcsZT10LmNhbGwoW10pO3JldHVybiBmdW5jdGlvbihpKXtyZXR1cm4gbnVsbCE9aSYmKGkgaW5zdGFuY2VvZiBBcnJheXx8XCJvYmplY3RcIj09dHlwZW9mIGkmJiEhaS5wdXNoJiZ0LmNhbGwoaSk9PT1lKX19KCksZj17fSxwPWZ1bmN0aW9uKGkscyxuLHIpe3RoaXMuc2M9ZltpXT9mW2ldLnNjOltdLGZbaV09dGhpcyx0aGlzLmdzQ2xhc3M9bnVsbCx0aGlzLmZ1bmM9bjt2YXIgYT1bXTt0aGlzLmNoZWNrPWZ1bmN0aW9uKGwpe2Zvcih2YXIgaCxfLHUsbSxjPXMubGVuZ3RoLGQ9YzstLWM+LTE7KShoPWZbc1tjXV18fG5ldyBwKHNbY10sW10pKS5nc0NsYXNzPyhhW2NdPWguZ3NDbGFzcyxkLS0pOmwmJmguc2MucHVzaCh0aGlzKTtpZigwPT09ZCYmbilmb3IoXz0oXCJjb20uZ3JlZW5zb2NrLlwiK2kpLnNwbGl0KFwiLlwiKSx1PV8ucG9wKCksbT1vKF8uam9pbihcIi5cIikpW3VdPXRoaXMuZ3NDbGFzcz1uLmFwcGx5KG4sYSksciYmKGVbdV09bSxcImZ1bmN0aW9uXCI9PXR5cGVvZiBkZWZpbmUmJmRlZmluZS5hbWQ/ZGVmaW5lKCh0LkdyZWVuU29ja0FNRFBhdGg/dC5HcmVlblNvY2tBTURQYXRoK1wiL1wiOlwiXCIpK2kuc3BsaXQoXCIuXCIpLmpvaW4oXCIvXCIpLFtdLGZ1bmN0aW9uKCl7cmV0dXJuIG19KTpcInVuZGVmaW5lZFwiIT10eXBlb2YgbW9kdWxlJiZtb2R1bGUuZXhwb3J0cyYmKG1vZHVsZS5leHBvcnRzPW0pKSxjPTA7dGhpcy5zYy5sZW5ndGg+YztjKyspdGhpcy5zY1tjXS5jaGVjaygpfSx0aGlzLmNoZWNrKCEwKX0sYz10Ll9nc0RlZmluZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gbmV3IHAodCxlLGkscyl9LGQ9bC5fY2xhc3M9ZnVuY3Rpb24odCxlLGkpe3JldHVybiBlPWV8fGZ1bmN0aW9uKCl7fSxjKHQsW10sZnVuY3Rpb24oKXtyZXR1cm4gZX0saSksZX07Yy5nbG9iYWxzPWU7dmFyIHY9WzAsMCwxLDFdLGc9W10sVD1kKFwiZWFzaW5nLkVhc2VcIixmdW5jdGlvbih0LGUsaSxzKXt0aGlzLl9mdW5jPXQsdGhpcy5fdHlwZT1pfHwwLHRoaXMuX3Bvd2VyPXN8fDAsdGhpcy5fcGFyYW1zPWU/di5jb25jYXQoZSk6dn0sITApLHk9VC5tYXA9e30sdz1ULnJlZ2lzdGVyPWZ1bmN0aW9uKHQsZSxpLHMpe2Zvcih2YXIgbixyLGEsbyxoPWUuc3BsaXQoXCIsXCIpLF89aC5sZW5ndGgsdT0oaXx8XCJlYXNlSW4sZWFzZU91dCxlYXNlSW5PdXRcIikuc3BsaXQoXCIsXCIpOy0tXz4tMTspZm9yKHI9aFtfXSxuPXM/ZChcImVhc2luZy5cIityLG51bGwsITApOmwuZWFzaW5nW3JdfHx7fSxhPXUubGVuZ3RoOy0tYT4tMTspbz11W2FdLHlbcitcIi5cIitvXT15W28rcl09bltvXT10LmdldFJhdGlvP3Q6dFtvXXx8bmV3IHR9O2ZvcihuPVQucHJvdG90eXBlLG4uX2NhbGNFbmQ9ITEsbi5nZXRSYXRpbz1mdW5jdGlvbih0KXtpZih0aGlzLl9mdW5jKXJldHVybiB0aGlzLl9wYXJhbXNbMF09dCx0aGlzLl9mdW5jLmFwcGx5KG51bGwsdGhpcy5fcGFyYW1zKTt2YXIgZT10aGlzLl90eXBlLGk9dGhpcy5fcG93ZXIscz0xPT09ZT8xLXQ6Mj09PWU/dDouNT50PzIqdDoyKigxLXQpO3JldHVybiAxPT09aT9zKj1zOjI9PT1pP3MqPXMqczozPT09aT9zKj1zKnMqczo0PT09aSYmKHMqPXMqcypzKnMpLDE9PT1lPzEtczoyPT09ZT9zOi41PnQ/cy8yOjEtcy8yfSxpPVtcIkxpbmVhclwiLFwiUXVhZFwiLFwiQ3ViaWNcIixcIlF1YXJ0XCIsXCJRdWludCxTdHJvbmdcIl0scz1pLmxlbmd0aDstLXM+LTE7KW49aVtzXStcIixQb3dlclwiK3MsdyhuZXcgVChudWxsLG51bGwsMSxzKSxuLFwiZWFzZU91dFwiLCEwKSx3KG5ldyBUKG51bGwsbnVsbCwyLHMpLG4sXCJlYXNlSW5cIisoMD09PXM/XCIsZWFzZU5vbmVcIjpcIlwiKSksdyhuZXcgVChudWxsLG51bGwsMyxzKSxuLFwiZWFzZUluT3V0XCIpO3kubGluZWFyPWwuZWFzaW5nLkxpbmVhci5lYXNlSW4seS5zd2luZz1sLmVhc2luZy5RdWFkLmVhc2VJbk91dDt2YXIgUD1kKFwiZXZlbnRzLkV2ZW50RGlzcGF0Y2hlclwiLGZ1bmN0aW9uKHQpe3RoaXMuX2xpc3RlbmVycz17fSx0aGlzLl9ldmVudFRhcmdldD10fHx0aGlzfSk7bj1QLnByb3RvdHlwZSxuLmFkZEV2ZW50TGlzdGVuZXI9ZnVuY3Rpb24odCxlLGkscyxuKXtuPW58fDA7dmFyIG8sbCxoPXRoaXMuX2xpc3RlbmVyc1t0XSxfPTA7Zm9yKG51bGw9PWgmJih0aGlzLl9saXN0ZW5lcnNbdF09aD1bXSksbD1oLmxlbmd0aDstLWw+LTE7KW89aFtsXSxvLmM9PT1lJiZvLnM9PT1pP2guc3BsaWNlKGwsMSk6MD09PV8mJm4+by5wciYmKF89bCsxKTtoLnNwbGljZShfLDAse2M6ZSxzOmksdXA6cyxwcjpufSksdGhpcyE9PXJ8fGF8fHIud2FrZSgpfSxuLnJlbW92ZUV2ZW50TGlzdGVuZXI9ZnVuY3Rpb24odCxlKXt2YXIgaSxzPXRoaXMuX2xpc3RlbmVyc1t0XTtpZihzKWZvcihpPXMubGVuZ3RoOy0taT4tMTspaWYoc1tpXS5jPT09ZSlyZXR1cm4gcy5zcGxpY2UoaSwxKSx2b2lkIDB9LG4uZGlzcGF0Y2hFdmVudD1mdW5jdGlvbih0KXt2YXIgZSxpLHMsbj10aGlzLl9saXN0ZW5lcnNbdF07aWYobilmb3IoZT1uLmxlbmd0aCxpPXRoaXMuX2V2ZW50VGFyZ2V0Oy0tZT4tMTspcz1uW2VdLHMudXA/cy5jLmNhbGwocy5zfHxpLHt0eXBlOnQsdGFyZ2V0Oml9KTpzLmMuY2FsbChzLnN8fGkpfTt2YXIgaz10LnJlcXVlc3RBbmltYXRpb25GcmFtZSxiPXQuY2FuY2VsQW5pbWF0aW9uRnJhbWUsQT1EYXRlLm5vd3x8ZnVuY3Rpb24oKXtyZXR1cm4obmV3IERhdGUpLmdldFRpbWUoKX0sUz1BKCk7Zm9yKGk9W1wibXNcIixcIm1velwiLFwid2Via2l0XCIsXCJvXCJdLHM9aS5sZW5ndGg7LS1zPi0xJiYhazspaz10W2lbc10rXCJSZXF1ZXN0QW5pbWF0aW9uRnJhbWVcIl0sYj10W2lbc10rXCJDYW5jZWxBbmltYXRpb25GcmFtZVwiXXx8dFtpW3NdK1wiQ2FuY2VsUmVxdWVzdEFuaW1hdGlvbkZyYW1lXCJdO2QoXCJUaWNrZXJcIixmdW5jdGlvbih0LGUpe3ZhciBpLHMsbixvLGwsXz10aGlzLG09QSgpLGY9ZSE9PSExJiZrLHA9NTAwLGM9MzMsZD1mdW5jdGlvbih0KXt2YXIgZSxyLGE9QSgpLVM7YT5wJiYobSs9YS1jKSxTKz1hLF8udGltZT0oUy1tKS8xZTMsZT1fLnRpbWUtbCwoIWl8fGU+MHx8dD09PSEwKSYmKF8uZnJhbWUrKyxsKz1lKyhlPj1vPy4wMDQ6by1lKSxyPSEwKSx0IT09ITAmJihuPXMoZCkpLHImJl8uZGlzcGF0Y2hFdmVudChcInRpY2tcIil9O1AuY2FsbChfKSxfLnRpbWU9Xy5mcmFtZT0wLF8udGljaz1mdW5jdGlvbigpe2QoITApfSxfLmxhZ1Ntb290aGluZz1mdW5jdGlvbih0LGUpe3A9dHx8MS9oLGM9TWF0aC5taW4oZSxwLDApfSxfLnNsZWVwPWZ1bmN0aW9uKCl7bnVsbCE9biYmKGYmJmI/YihuKTpjbGVhclRpbWVvdXQobikscz11LG49bnVsbCxfPT09ciYmKGE9ITEpKX0sXy53YWtlPWZ1bmN0aW9uKCl7bnVsbCE9PW4/Xy5zbGVlcCgpOl8uZnJhbWU+MTAmJihTPUEoKS1wKzUpLHM9MD09PWk/dTpmJiZrP2s6ZnVuY3Rpb24odCl7cmV0dXJuIHNldFRpbWVvdXQodCwwfDFlMyoobC1fLnRpbWUpKzEpfSxfPT09ciYmKGE9ITApLGQoMil9LF8uZnBzPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyhpPXQsbz0xLyhpfHw2MCksbD10aGlzLnRpbWUrbyxfLndha2UoKSx2b2lkIDApOml9LF8udXNlUkFGPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyhfLnNsZWVwKCksZj10LF8uZnBzKGkpLHZvaWQgMCk6Zn0sXy5mcHModCksc2V0VGltZW91dChmdW5jdGlvbigpe2YmJighbnx8NT5fLmZyYW1lKSYmXy51c2VSQUYoITEpfSwxNTAwKX0pLG49bC5UaWNrZXIucHJvdG90eXBlPW5ldyBsLmV2ZW50cy5FdmVudERpc3BhdGNoZXIsbi5jb25zdHJ1Y3Rvcj1sLlRpY2tlcjt2YXIgeD1kKFwiY29yZS5BbmltYXRpb25cIixmdW5jdGlvbih0LGUpe2lmKHRoaXMudmFycz1lPWV8fHt9LHRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249dHx8MCx0aGlzLl9kZWxheT1OdW1iZXIoZS5kZWxheSl8fDAsdGhpcy5fdGltZVNjYWxlPTEsdGhpcy5fYWN0aXZlPWUuaW1tZWRpYXRlUmVuZGVyPT09ITAsdGhpcy5kYXRhPWUuZGF0YSx0aGlzLl9yZXZlcnNlZD1lLnJldmVyc2VkPT09ITAsQil7YXx8ci53YWtlKCk7dmFyIGk9dGhpcy52YXJzLnVzZUZyYW1lcz9ROkI7aS5hZGQodGhpcyxpLl90aW1lKSx0aGlzLnZhcnMucGF1c2VkJiZ0aGlzLnBhdXNlZCghMCl9fSk7cj14LnRpY2tlcj1uZXcgbC5UaWNrZXIsbj14LnByb3RvdHlwZSxuLl9kaXJ0eT1uLl9nYz1uLl9pbml0dGVkPW4uX3BhdXNlZD0hMSxuLl90b3RhbFRpbWU9bi5fdGltZT0wLG4uX3Jhd1ByZXZUaW1lPS0xLG4uX25leHQ9bi5fbGFzdD1uLl9vblVwZGF0ZT1uLl90aW1lbGluZT1uLnRpbWVsaW5lPW51bGwsbi5fcGF1c2VkPSExO3ZhciBDPWZ1bmN0aW9uKCl7YSYmQSgpLVM+MmUzJiZyLndha2UoKSxzZXRUaW1lb3V0KEMsMmUzKX07QygpLG4ucGxheT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnJldmVyc2VkKCExKS5wYXVzZWQoITEpfSxuLnBhdXNlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucGF1c2VkKCEwKX0sbi5yZXN1bWU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5wYXVzZWQoITEpfSxuLnNlZWs9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy50b3RhbFRpbWUoTnVtYmVyKHQpLGUhPT0hMSl9LG4ucmVzdGFydD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnJldmVyc2VkKCExKS5wYXVzZWQoITEpLnRvdGFsVGltZSh0Py10aGlzLl9kZWxheTowLGUhPT0hMSwhMCl9LG4ucmV2ZXJzZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodHx8dGhpcy50b3RhbER1cmF0aW9uKCksZSksdGhpcy5yZXZlcnNlZCghMCkucGF1c2VkKCExKX0sbi5yZW5kZXI9ZnVuY3Rpb24oKXt9LG4uaW52YWxpZGF0ZT1mdW5jdGlvbigpe3JldHVybiB0aGlzfSxuLmlzQWN0aXZlPWZ1bmN0aW9uKCl7dmFyIHQsZT10aGlzLl90aW1lbGluZSxpPXRoaXMuX3N0YXJ0VGltZTtyZXR1cm4hZXx8IXRoaXMuX2djJiYhdGhpcy5fcGF1c2VkJiZlLmlzQWN0aXZlKCkmJih0PWUucmF3VGltZSgpKT49aSYmaSt0aGlzLnRvdGFsRHVyYXRpb24oKS90aGlzLl90aW1lU2NhbGU+dH0sbi5fZW5hYmxlZD1mdW5jdGlvbih0LGUpe3JldHVybiBhfHxyLndha2UoKSx0aGlzLl9nYz0hdCx0aGlzLl9hY3RpdmU9dGhpcy5pc0FjdGl2ZSgpLGUhPT0hMCYmKHQmJiF0aGlzLnRpbWVsaW5lP3RoaXMuX3RpbWVsaW5lLmFkZCh0aGlzLHRoaXMuX3N0YXJ0VGltZS10aGlzLl9kZWxheSk6IXQmJnRoaXMudGltZWxpbmUmJnRoaXMuX3RpbWVsaW5lLl9yZW1vdmUodGhpcywhMCkpLCExfSxuLl9raWxsPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX2VuYWJsZWQoITEsITEpfSxuLmtpbGw9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5fa2lsbCh0LGUpLHRoaXN9LG4uX3VuY2FjaGU9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQ/dGhpczp0aGlzLnRpbWVsaW5lO2U7KWUuX2RpcnR5PSEwLGU9ZS50aW1lbGluZTtyZXR1cm4gdGhpc30sbi5fc3dhcFNlbGZJblBhcmFtcz1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC5sZW5ndGgsaT10LmNvbmNhdCgpOy0tZT4tMTspXCJ7c2VsZn1cIj09PXRbZV0mJihpW2VdPXRoaXMpO3JldHVybiBpfSxuLmV2ZW50Q2FsbGJhY2s9ZnVuY3Rpb24odCxlLGkscyl7aWYoXCJvblwiPT09KHR8fFwiXCIpLnN1YnN0cigwLDIpKXt2YXIgbj10aGlzLnZhcnM7aWYoMT09PWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIG5bdF07bnVsbD09ZT9kZWxldGUgblt0XTooblt0XT1lLG5bdCtcIlBhcmFtc1wiXT1tKGkpJiYtMSE9PWkuam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpP3RoaXMuX3N3YXBTZWxmSW5QYXJhbXMoaSk6aSxuW3QrXCJTY29wZVwiXT1zKSxcIm9uVXBkYXRlXCI9PT10JiYodGhpcy5fb25VcGRhdGU9ZSl9cmV0dXJuIHRoaXN9LG4uZGVsYXk9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nJiZ0aGlzLnN0YXJ0VGltZSh0aGlzLl9zdGFydFRpbWUrdC10aGlzLl9kZWxheSksdGhpcy5fZGVsYXk9dCx0aGlzKTp0aGlzLl9kZWxheX0sbi5kdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj10LHRoaXMuX3VuY2FjaGUoITApLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nJiZ0aGlzLl90aW1lPjAmJnRoaXMuX3RpbWU8dGhpcy5fZHVyYXRpb24mJjAhPT10JiZ0aGlzLnRvdGFsVGltZSh0aGlzLl90b3RhbFRpbWUqKHQvdGhpcy5fZHVyYXRpb24pLCEwKSx0aGlzKToodGhpcy5fZGlydHk9ITEsdGhpcy5fZHVyYXRpb24pfSxuLnRvdGFsRHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIHRoaXMuX2RpcnR5PSExLGFyZ3VtZW50cy5sZW5ndGg/dGhpcy5kdXJhdGlvbih0KTp0aGlzLl90b3RhbER1cmF0aW9ufSxuLnRpbWU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odGhpcy5fZGlydHkmJnRoaXMudG90YWxEdXJhdGlvbigpLHRoaXMudG90YWxUaW1lKHQ+dGhpcy5fZHVyYXRpb24/dGhpcy5fZHVyYXRpb246dCxlKSk6dGhpcy5fdGltZX0sbi50b3RhbFRpbWU9ZnVuY3Rpb24odCxlLGkpe2lmKGF8fHIud2FrZSgpLCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl90b3RhbFRpbWU7aWYodGhpcy5fdGltZWxpbmUpe2lmKDA+dCYmIWkmJih0Kz10aGlzLnRvdGFsRHVyYXRpb24oKSksdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcpe3RoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKTt2YXIgcz10aGlzLl90b3RhbER1cmF0aW9uLG49dGhpcy5fdGltZWxpbmU7aWYodD5zJiYhaSYmKHQ9cyksdGhpcy5fc3RhcnRUaW1lPSh0aGlzLl9wYXVzZWQ/dGhpcy5fcGF1c2VUaW1lOm4uX3RpbWUpLSh0aGlzLl9yZXZlcnNlZD9zLXQ6dCkvdGhpcy5fdGltZVNjYWxlLG4uX2RpcnR5fHx0aGlzLl91bmNhY2hlKCExKSxuLl90aW1lbGluZSlmb3IoO24uX3RpbWVsaW5lOyluLl90aW1lbGluZS5fdGltZSE9PShuLl9zdGFydFRpbWUrbi5fdG90YWxUaW1lKS9uLl90aW1lU2NhbGUmJm4udG90YWxUaW1lKG4uX3RvdGFsVGltZSwhMCksbj1uLl90aW1lbGluZX10aGlzLl9nYyYmdGhpcy5fZW5hYmxlZCghMCwhMSksKHRoaXMuX3RvdGFsVGltZSE9PXR8fDA9PT10aGlzLl9kdXJhdGlvbikmJih0aGlzLnJlbmRlcih0LGUsITEpLHoubGVuZ3RoJiZxKCkpfXJldHVybiB0aGlzfSxuLnByb2dyZXNzPW4udG90YWxQcm9ncmVzcz1mdW5jdGlvbih0LGUpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoP3RoaXMudG90YWxUaW1lKHRoaXMuZHVyYXRpb24oKSp0LGUpOnRoaXMuX3RpbWUvdGhpcy5kdXJhdGlvbigpfSxuLnN0YXJ0VGltZT1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odCE9PXRoaXMuX3N0YXJ0VGltZSYmKHRoaXMuX3N0YXJ0VGltZT10LHRoaXMudGltZWxpbmUmJnRoaXMudGltZWxpbmUuX3NvcnRDaGlsZHJlbiYmdGhpcy50aW1lbGluZS5hZGQodGhpcyx0LXRoaXMuX2RlbGF5KSksdGhpcyk6dGhpcy5fc3RhcnRUaW1lfSxuLnRpbWVTY2FsZT1mdW5jdGlvbih0KXtpZighYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fdGltZVNjYWxlO2lmKHQ9dHx8aCx0aGlzLl90aW1lbGluZSYmdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcpe3ZhciBlPXRoaXMuX3BhdXNlVGltZSxpPWV8fDA9PT1lP2U6dGhpcy5fdGltZWxpbmUudG90YWxUaW1lKCk7dGhpcy5fc3RhcnRUaW1lPWktKGktdGhpcy5fc3RhcnRUaW1lKSp0aGlzLl90aW1lU2NhbGUvdH1yZXR1cm4gdGhpcy5fdGltZVNjYWxlPXQsdGhpcy5fdW5jYWNoZSghMSl9LG4ucmV2ZXJzZWQ9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHQhPXRoaXMuX3JldmVyc2VkJiYodGhpcy5fcmV2ZXJzZWQ9dCx0aGlzLnRvdGFsVGltZSh0aGlzLl90aW1lbGluZSYmIXRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nP3RoaXMudG90YWxEdXJhdGlvbigpLXRoaXMuX3RvdGFsVGltZTp0aGlzLl90b3RhbFRpbWUsITApKSx0aGlzKTp0aGlzLl9yZXZlcnNlZH0sbi5wYXVzZWQ9ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3BhdXNlZDtpZih0IT10aGlzLl9wYXVzZWQmJnRoaXMuX3RpbWVsaW5lKXthfHx0fHxyLndha2UoKTt2YXIgZT10aGlzLl90aW1lbGluZSxpPWUucmF3VGltZSgpLHM9aS10aGlzLl9wYXVzZVRpbWU7IXQmJmUuc21vb3RoQ2hpbGRUaW1pbmcmJih0aGlzLl9zdGFydFRpbWUrPXMsdGhpcy5fdW5jYWNoZSghMSkpLHRoaXMuX3BhdXNlVGltZT10P2k6bnVsbCx0aGlzLl9wYXVzZWQ9dCx0aGlzLl9hY3RpdmU9dGhpcy5pc0FjdGl2ZSgpLCF0JiYwIT09cyYmdGhpcy5faW5pdHRlZCYmdGhpcy5kdXJhdGlvbigpJiZ0aGlzLnJlbmRlcihlLnNtb290aENoaWxkVGltaW5nP3RoaXMuX3RvdGFsVGltZTooaS10aGlzLl9zdGFydFRpbWUpL3RoaXMuX3RpbWVTY2FsZSwhMCwhMCl9cmV0dXJuIHRoaXMuX2djJiYhdCYmdGhpcy5fZW5hYmxlZCghMCwhMSksdGhpc307dmFyIFI9ZChcImNvcmUuU2ltcGxlVGltZWxpbmVcIixmdW5jdGlvbih0KXt4LmNhbGwodGhpcywwLHQpLHRoaXMuYXV0b1JlbW92ZUNoaWxkcmVuPXRoaXMuc21vb3RoQ2hpbGRUaW1pbmc9ITB9KTtuPVIucHJvdG90eXBlPW5ldyB4LG4uY29uc3RydWN0b3I9UixuLmtpbGwoKS5fZ2M9ITEsbi5fZmlyc3Q9bi5fbGFzdD1udWxsLG4uX3NvcnRDaGlsZHJlbj0hMSxuLmFkZD1uLmluc2VydD1mdW5jdGlvbih0LGUpe3ZhciBpLHM7aWYodC5fc3RhcnRUaW1lPU51bWJlcihlfHwwKSt0Ll9kZWxheSx0Ll9wYXVzZWQmJnRoaXMhPT10Ll90aW1lbGluZSYmKHQuX3BhdXNlVGltZT10Ll9zdGFydFRpbWUrKHRoaXMucmF3VGltZSgpLXQuX3N0YXJ0VGltZSkvdC5fdGltZVNjYWxlKSx0LnRpbWVsaW5lJiZ0LnRpbWVsaW5lLl9yZW1vdmUodCwhMCksdC50aW1lbGluZT10Ll90aW1lbGluZT10aGlzLHQuX2djJiZ0Ll9lbmFibGVkKCEwLCEwKSxpPXRoaXMuX2xhc3QsdGhpcy5fc29ydENoaWxkcmVuKWZvcihzPXQuX3N0YXJ0VGltZTtpJiZpLl9zdGFydFRpbWU+czspaT1pLl9wcmV2O3JldHVybiBpPyh0Ll9uZXh0PWkuX25leHQsaS5fbmV4dD10KToodC5fbmV4dD10aGlzLl9maXJzdCx0aGlzLl9maXJzdD10KSx0Ll9uZXh0P3QuX25leHQuX3ByZXY9dDp0aGlzLl9sYXN0PXQsdC5fcHJldj1pLHRoaXMuX3RpbWVsaW5lJiZ0aGlzLl91bmNhY2hlKCEwKSx0aGlzfSxuLl9yZW1vdmU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdC50aW1lbGluZT09PXRoaXMmJihlfHx0Ll9lbmFibGVkKCExLCEwKSx0LnRpbWVsaW5lPW51bGwsdC5fcHJldj90Ll9wcmV2Ll9uZXh0PXQuX25leHQ6dGhpcy5fZmlyc3Q9PT10JiYodGhpcy5fZmlyc3Q9dC5fbmV4dCksdC5fbmV4dD90Ll9uZXh0Ll9wcmV2PXQuX3ByZXY6dGhpcy5fbGFzdD09PXQmJih0aGlzLl9sYXN0PXQuX3ByZXYpLHRoaXMuX3RpbWVsaW5lJiZ0aGlzLl91bmNhY2hlKCEwKSksdGhpc30sbi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3ZhciBzLG49dGhpcy5fZmlyc3Q7Zm9yKHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXRoaXMuX3Jhd1ByZXZUaW1lPXQ7bjspcz1uLl9uZXh0LChuLl9hY3RpdmV8fHQ+PW4uX3N0YXJ0VGltZSYmIW4uX3BhdXNlZCkmJihuLl9yZXZlcnNlZD9uLnJlbmRlcigobi5fZGlydHk/bi50b3RhbER1cmF0aW9uKCk6bi5fdG90YWxEdXJhdGlvbiktKHQtbi5fc3RhcnRUaW1lKSpuLl90aW1lU2NhbGUsZSxpKTpuLnJlbmRlcigodC1uLl9zdGFydFRpbWUpKm4uX3RpbWVTY2FsZSxlLGkpKSxuPXN9LG4ucmF3VGltZT1mdW5jdGlvbigpe3JldHVybiBhfHxyLndha2UoKSx0aGlzLl90b3RhbFRpbWV9O3ZhciBEPWQoXCJUd2VlbkxpdGVcIixmdW5jdGlvbihlLGkscyl7aWYoeC5jYWxsKHRoaXMsaSxzKSx0aGlzLnJlbmRlcj1ELnByb3RvdHlwZS5yZW5kZXIsbnVsbD09ZSl0aHJvd1wiQ2Fubm90IHR3ZWVuIGEgbnVsbCB0YXJnZXQuXCI7dGhpcy50YXJnZXQ9ZT1cInN0cmluZ1wiIT10eXBlb2YgZT9lOkQuc2VsZWN0b3IoZSl8fGU7dmFyIG4scixhLG89ZS5qcXVlcnl8fGUubGVuZ3RoJiZlIT09dCYmZVswXSYmKGVbMF09PT10fHxlWzBdLm5vZGVUeXBlJiZlWzBdLnN0eWxlJiYhZS5ub2RlVHlwZSksbD10aGlzLnZhcnMub3ZlcndyaXRlO2lmKHRoaXMuX292ZXJ3cml0ZT1sPW51bGw9PWw/R1tELmRlZmF1bHRPdmVyd3JpdGVdOlwibnVtYmVyXCI9PXR5cGVvZiBsP2w+PjA6R1tsXSwob3x8ZSBpbnN0YW5jZW9mIEFycmF5fHxlLnB1c2gmJm0oZSkpJiZcIm51bWJlclwiIT10eXBlb2YgZVswXSlmb3IodGhpcy5fdGFyZ2V0cz1hPV8uY2FsbChlLDApLHRoaXMuX3Byb3BMb29rdXA9W10sdGhpcy5fc2libGluZ3M9W10sbj0wO2EubGVuZ3RoPm47bisrKXI9YVtuXSxyP1wic3RyaW5nXCIhPXR5cGVvZiByP3IubGVuZ3RoJiZyIT09dCYmclswXSYmKHJbMF09PT10fHxyWzBdLm5vZGVUeXBlJiZyWzBdLnN0eWxlJiYhci5ub2RlVHlwZSk/KGEuc3BsaWNlKG4tLSwxKSx0aGlzLl90YXJnZXRzPWE9YS5jb25jYXQoXy5jYWxsKHIsMCkpKToodGhpcy5fc2libGluZ3Nbbl09TShyLHRoaXMsITEpLDE9PT1sJiZ0aGlzLl9zaWJsaW5nc1tuXS5sZW5ndGg+MSYmJChyLHRoaXMsbnVsbCwxLHRoaXMuX3NpYmxpbmdzW25dKSk6KHI9YVtuLS1dPUQuc2VsZWN0b3IociksXCJzdHJpbmdcIj09dHlwZW9mIHImJmEuc3BsaWNlKG4rMSwxKSk6YS5zcGxpY2Uobi0tLDEpO2Vsc2UgdGhpcy5fcHJvcExvb2t1cD17fSx0aGlzLl9zaWJsaW5ncz1NKGUsdGhpcywhMSksMT09PWwmJnRoaXMuX3NpYmxpbmdzLmxlbmd0aD4xJiYkKGUsdGhpcyxudWxsLDEsdGhpcy5fc2libGluZ3MpOyh0aGlzLnZhcnMuaW1tZWRpYXRlUmVuZGVyfHwwPT09aSYmMD09PXRoaXMuX2RlbGF5JiZ0aGlzLnZhcnMuaW1tZWRpYXRlUmVuZGVyIT09ITEpJiYodGhpcy5fdGltZT0taCx0aGlzLnJlbmRlcigtdGhpcy5fZGVsYXkpKX0sITApLEk9ZnVuY3Rpb24oZSl7cmV0dXJuIGUubGVuZ3RoJiZlIT09dCYmZVswXSYmKGVbMF09PT10fHxlWzBdLm5vZGVUeXBlJiZlWzBdLnN0eWxlJiYhZS5ub2RlVHlwZSl9LEU9ZnVuY3Rpb24odCxlKXt2YXIgaSxzPXt9O2ZvcihpIGluIHQpaltpXXx8aSBpbiBlJiZcInRyYW5zZm9ybVwiIT09aSYmXCJ4XCIhPT1pJiZcInlcIiE9PWkmJlwid2lkdGhcIiE9PWkmJlwiaGVpZ2h0XCIhPT1pJiZcImNsYXNzTmFtZVwiIT09aSYmXCJib3JkZXJcIiE9PWl8fCEoIUxbaV18fExbaV0mJkxbaV0uX2F1dG9DU1MpfHwoc1tpXT10W2ldLGRlbGV0ZSB0W2ldKTt0LmNzcz1zfTtuPUQucHJvdG90eXBlPW5ldyB4LG4uY29uc3RydWN0b3I9RCxuLmtpbGwoKS5fZ2M9ITEsbi5yYXRpbz0wLG4uX2ZpcnN0UFQ9bi5fdGFyZ2V0cz1uLl9vdmVyd3JpdHRlblByb3BzPW4uX3N0YXJ0QXQ9bnVsbCxuLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkPW4uX2xhenk9ITEsRC52ZXJzaW9uPVwiMS4xMi4xXCIsRC5kZWZhdWx0RWFzZT1uLl9lYXNlPW5ldyBUKG51bGwsbnVsbCwxLDEpLEQuZGVmYXVsdE92ZXJ3cml0ZT1cImF1dG9cIixELnRpY2tlcj1yLEQuYXV0b1NsZWVwPSEwLEQubGFnU21vb3RoaW5nPWZ1bmN0aW9uKHQsZSl7ci5sYWdTbW9vdGhpbmcodCxlKX0sRC5zZWxlY3Rvcj10LiR8fHQualF1ZXJ5fHxmdW5jdGlvbihlKXtyZXR1cm4gdC4kPyhELnNlbGVjdG9yPXQuJCx0LiQoZSkpOnQuZG9jdW1lbnQ/dC5kb2N1bWVudC5nZXRFbGVtZW50QnlJZChcIiNcIj09PWUuY2hhckF0KDApP2Uuc3Vic3RyKDEpOmUpOmV9O3ZhciB6PVtdLE89e30sTj1ELl9pbnRlcm5hbHM9e2lzQXJyYXk6bSxpc1NlbGVjdG9yOkksbGF6eVR3ZWVuczp6fSxMPUQuX3BsdWdpbnM9e30sVT1OLnR3ZWVuTG9va3VwPXt9LEY9MCxqPU4ucmVzZXJ2ZWRQcm9wcz17ZWFzZToxLGRlbGF5OjEsb3ZlcndyaXRlOjEsb25Db21wbGV0ZToxLG9uQ29tcGxldGVQYXJhbXM6MSxvbkNvbXBsZXRlU2NvcGU6MSx1c2VGcmFtZXM6MSxydW5CYWNrd2FyZHM6MSxzdGFydEF0OjEsb25VcGRhdGU6MSxvblVwZGF0ZVBhcmFtczoxLG9uVXBkYXRlU2NvcGU6MSxvblN0YXJ0OjEsb25TdGFydFBhcmFtczoxLG9uU3RhcnRTY29wZToxLG9uUmV2ZXJzZUNvbXBsZXRlOjEsb25SZXZlcnNlQ29tcGxldGVQYXJhbXM6MSxvblJldmVyc2VDb21wbGV0ZVNjb3BlOjEsb25SZXBlYXQ6MSxvblJlcGVhdFBhcmFtczoxLG9uUmVwZWF0U2NvcGU6MSxlYXNlUGFyYW1zOjEseW95bzoxLGltbWVkaWF0ZVJlbmRlcjoxLHJlcGVhdDoxLHJlcGVhdERlbGF5OjEsZGF0YToxLHBhdXNlZDoxLHJldmVyc2VkOjEsYXV0b0NTUzoxLGxhenk6MX0sRz17bm9uZTowLGFsbDoxLGF1dG86Mixjb25jdXJyZW50OjMsYWxsT25TdGFydDo0LHByZWV4aXN0aW5nOjUsXCJ0cnVlXCI6MSxcImZhbHNlXCI6MH0sUT14Ll9yb290RnJhbWVzVGltZWxpbmU9bmV3IFIsQj14Ll9yb290VGltZWxpbmU9bmV3IFIscT1mdW5jdGlvbigpe3ZhciB0PXoubGVuZ3RoO2ZvcihPPXt9Oy0tdD4tMTspaT16W3RdLGkmJmkuX2xhenkhPT0hMSYmKGkucmVuZGVyKGkuX2xhenksITEsITApLGkuX2xhenk9ITEpO3oubGVuZ3RoPTB9O0IuX3N0YXJ0VGltZT1yLnRpbWUsUS5fc3RhcnRUaW1lPXIuZnJhbWUsQi5fYWN0aXZlPVEuX2FjdGl2ZT0hMCxzZXRUaW1lb3V0KHEsMSkseC5fdXBkYXRlUm9vdD1ELnJlbmRlcj1mdW5jdGlvbigpe3ZhciB0LGUsaTtpZih6Lmxlbmd0aCYmcSgpLEIucmVuZGVyKChyLnRpbWUtQi5fc3RhcnRUaW1lKSpCLl90aW1lU2NhbGUsITEsITEpLFEucmVuZGVyKChyLmZyYW1lLVEuX3N0YXJ0VGltZSkqUS5fdGltZVNjYWxlLCExLCExKSx6Lmxlbmd0aCYmcSgpLCEoci5mcmFtZSUxMjApKXtmb3IoaSBpbiBVKXtmb3IoZT1VW2ldLnR3ZWVucyx0PWUubGVuZ3RoOy0tdD4tMTspZVt0XS5fZ2MmJmUuc3BsaWNlKHQsMSk7MD09PWUubGVuZ3RoJiZkZWxldGUgVVtpXX1pZihpPUIuX2ZpcnN0LCghaXx8aS5fcGF1c2VkKSYmRC5hdXRvU2xlZXAmJiFRLl9maXJzdCYmMT09PXIuX2xpc3RlbmVycy50aWNrLmxlbmd0aCl7Zm9yKDtpJiZpLl9wYXVzZWQ7KWk9aS5fbmV4dDtpfHxyLnNsZWVwKCl9fX0sci5hZGRFdmVudExpc3RlbmVyKFwidGlja1wiLHguX3VwZGF0ZVJvb3QpO3ZhciBNPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuLHI9dC5fZ3NUd2VlbklEO2lmKFVbcnx8KHQuX2dzVHdlZW5JRD1yPVwidFwiK0YrKyldfHwoVVtyXT17dGFyZ2V0OnQsdHdlZW5zOltdfSksZSYmKHM9VVtyXS50d2VlbnMsc1tuPXMubGVuZ3RoXT1lLGkpKWZvcig7LS1uPi0xOylzW25dPT09ZSYmcy5zcGxpY2UobiwxKTtyZXR1cm4gVVtyXS50d2VlbnN9LCQ9ZnVuY3Rpb24odCxlLGkscyxuKXt2YXIgcixhLG8sbDtpZigxPT09c3x8cz49NCl7Zm9yKGw9bi5sZW5ndGgscj0wO2w+cjtyKyspaWYoKG89bltyXSkhPT1lKW8uX2djfHxvLl9lbmFibGVkKCExLCExKSYmKGE9ITApO2Vsc2UgaWYoNT09PXMpYnJlYWs7cmV0dXJuIGF9dmFyIF8sdT1lLl9zdGFydFRpbWUraCxtPVtdLGY9MCxwPTA9PT1lLl9kdXJhdGlvbjtmb3Iocj1uLmxlbmd0aDstLXI+LTE7KShvPW5bcl0pPT09ZXx8by5fZ2N8fG8uX3BhdXNlZHx8KG8uX3RpbWVsaW5lIT09ZS5fdGltZWxpbmU/KF89X3x8SyhlLDAscCksMD09PUsobyxfLHApJiYobVtmKytdPW8pKTp1Pj1vLl9zdGFydFRpbWUmJm8uX3N0YXJ0VGltZStvLnRvdGFsRHVyYXRpb24oKS9vLl90aW1lU2NhbGU+dSYmKChwfHwhby5faW5pdHRlZCkmJjJlLTEwPj11LW8uX3N0YXJ0VGltZXx8KG1bZisrXT1vKSkpO2ZvcihyPWY7LS1yPi0xOylvPW1bcl0sMj09PXMmJm8uX2tpbGwoaSx0KSYmKGE9ITApLCgyIT09c3x8IW8uX2ZpcnN0UFQmJm8uX2luaXR0ZWQpJiZvLl9lbmFibGVkKCExLCExKSYmKGE9ITApO3JldHVybiBhfSxLPWZ1bmN0aW9uKHQsZSxpKXtmb3IodmFyIHM9dC5fdGltZWxpbmUsbj1zLl90aW1lU2NhbGUscj10Ll9zdGFydFRpbWU7cy5fdGltZWxpbmU7KXtpZihyKz1zLl9zdGFydFRpbWUsbio9cy5fdGltZVNjYWxlLHMuX3BhdXNlZClyZXR1cm4tMTAwO3M9cy5fdGltZWxpbmV9cmV0dXJuIHIvPW4scj5lP3ItZTppJiZyPT09ZXx8IXQuX2luaXR0ZWQmJjIqaD5yLWU/aDoocis9dC50b3RhbER1cmF0aW9uKCkvdC5fdGltZVNjYWxlL24pPmUraD8wOnItZS1ofTtuLl9pbml0PWZ1bmN0aW9uKCl7dmFyIHQsZSxpLHMsbixyPXRoaXMudmFycyxhPXRoaXMuX292ZXJ3cml0dGVuUHJvcHMsbz10aGlzLl9kdXJhdGlvbixsPSEhci5pbW1lZGlhdGVSZW5kZXIsaD1yLmVhc2U7aWYoci5zdGFydEF0KXt0aGlzLl9zdGFydEF0JiYodGhpcy5fc3RhcnRBdC5yZW5kZXIoLTEsITApLHRoaXMuX3N0YXJ0QXQua2lsbCgpKSxuPXt9O2ZvcihzIGluIHIuc3RhcnRBdCluW3NdPXIuc3RhcnRBdFtzXTtpZihuLm92ZXJ3cml0ZT0hMSxuLmltbWVkaWF0ZVJlbmRlcj0hMCxuLmxhenk9bCYmci5sYXp5IT09ITEsbi5zdGFydEF0PW4uZGVsYXk9bnVsbCx0aGlzLl9zdGFydEF0PUQudG8odGhpcy50YXJnZXQsMCxuKSxsKWlmKHRoaXMuX3RpbWU+MCl0aGlzLl9zdGFydEF0PW51bGw7ZWxzZSBpZigwIT09bylyZXR1cm59ZWxzZSBpZihyLnJ1bkJhY2t3YXJkcyYmMCE9PW8paWYodGhpcy5fc3RhcnRBdCl0aGlzLl9zdGFydEF0LnJlbmRlcigtMSwhMCksdGhpcy5fc3RhcnRBdC5raWxsKCksdGhpcy5fc3RhcnRBdD1udWxsO2Vsc2V7aT17fTtmb3IocyBpbiByKWpbc10mJlwiYXV0b0NTU1wiIT09c3x8KGlbc109cltzXSk7aWYoaS5vdmVyd3JpdGU9MCxpLmRhdGE9XCJpc0Zyb21TdGFydFwiLGkubGF6eT1sJiZyLmxhenkhPT0hMSxpLmltbWVkaWF0ZVJlbmRlcj1sLHRoaXMuX3N0YXJ0QXQ9RC50byh0aGlzLnRhcmdldCwwLGkpLGwpe2lmKDA9PT10aGlzLl90aW1lKXJldHVybn1lbHNlIHRoaXMuX3N0YXJ0QXQuX2luaXQoKSx0aGlzLl9zdGFydEF0Ll9lbmFibGVkKCExKX1pZih0aGlzLl9lYXNlPWg/aCBpbnN0YW5jZW9mIFQ/ci5lYXNlUGFyYW1zIGluc3RhbmNlb2YgQXJyYXk/aC5jb25maWcuYXBwbHkoaCxyLmVhc2VQYXJhbXMpOmg6XCJmdW5jdGlvblwiPT10eXBlb2YgaD9uZXcgVChoLHIuZWFzZVBhcmFtcyk6eVtoXXx8RC5kZWZhdWx0RWFzZTpELmRlZmF1bHRFYXNlLHRoaXMuX2Vhc2VUeXBlPXRoaXMuX2Vhc2UuX3R5cGUsdGhpcy5fZWFzZVBvd2VyPXRoaXMuX2Vhc2UuX3Bvd2VyLHRoaXMuX2ZpcnN0UFQ9bnVsbCx0aGlzLl90YXJnZXRzKWZvcih0PXRoaXMuX3RhcmdldHMubGVuZ3RoOy0tdD4tMTspdGhpcy5faW5pdFByb3BzKHRoaXMuX3RhcmdldHNbdF0sdGhpcy5fcHJvcExvb2t1cFt0XT17fSx0aGlzLl9zaWJsaW5nc1t0XSxhP2FbdF06bnVsbCkmJihlPSEwKTtlbHNlIGU9dGhpcy5faW5pdFByb3BzKHRoaXMudGFyZ2V0LHRoaXMuX3Byb3BMb29rdXAsdGhpcy5fc2libGluZ3MsYSk7aWYoZSYmRC5fb25QbHVnaW5FdmVudChcIl9vbkluaXRBbGxQcm9wc1wiLHRoaXMpLGEmJih0aGlzLl9maXJzdFBUfHxcImZ1bmN0aW9uXCIhPXR5cGVvZiB0aGlzLnRhcmdldCYmdGhpcy5fZW5hYmxlZCghMSwhMSkpLHIucnVuQmFja3dhcmRzKWZvcihpPXRoaXMuX2ZpcnN0UFQ7aTspaS5zKz1pLmMsaS5jPS1pLmMsaT1pLl9uZXh0O3RoaXMuX29uVXBkYXRlPXIub25VcGRhdGUsdGhpcy5faW5pdHRlZD0hMH0sbi5faW5pdFByb3BzPWZ1bmN0aW9uKGUsaSxzLG4pe3ZhciByLGEsbyxsLGgsXztpZihudWxsPT1lKXJldHVybiExO09bZS5fZ3NUd2VlbklEXSYmcSgpLHRoaXMudmFycy5jc3N8fGUuc3R5bGUmJmUhPT10JiZlLm5vZGVUeXBlJiZMLmNzcyYmdGhpcy52YXJzLmF1dG9DU1MhPT0hMSYmRSh0aGlzLnZhcnMsZSk7Zm9yKHIgaW4gdGhpcy52YXJzKXtpZihfPXRoaXMudmFyc1tyXSxqW3JdKV8mJihfIGluc3RhbmNlb2YgQXJyYXl8fF8ucHVzaCYmbShfKSkmJi0xIT09Xy5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIikmJih0aGlzLnZhcnNbcl09Xz10aGlzLl9zd2FwU2VsZkluUGFyYW1zKF8sdGhpcykpO2Vsc2UgaWYoTFtyXSYmKGw9bmV3IExbcl0pLl9vbkluaXRUd2VlbihlLHRoaXMudmFyc1tyXSx0aGlzKSl7Zm9yKHRoaXMuX2ZpcnN0UFQ9aD17X25leHQ6dGhpcy5fZmlyc3RQVCx0OmwscDpcInNldFJhdGlvXCIsczowLGM6MSxmOiEwLG46cixwZzohMCxwcjpsLl9wcmlvcml0eX0sYT1sLl9vdmVyd3JpdGVQcm9wcy5sZW5ndGg7LS1hPi0xOylpW2wuX292ZXJ3cml0ZVByb3BzW2FdXT10aGlzLl9maXJzdFBUOyhsLl9wcmlvcml0eXx8bC5fb25Jbml0QWxsUHJvcHMpJiYobz0hMCksKGwuX29uRGlzYWJsZXx8bC5fb25FbmFibGUpJiYodGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD0hMCl9ZWxzZSB0aGlzLl9maXJzdFBUPWlbcl09aD17X25leHQ6dGhpcy5fZmlyc3RQVCx0OmUscDpyLGY6XCJmdW5jdGlvblwiPT10eXBlb2YgZVtyXSxuOnIscGc6ITEscHI6MH0saC5zPWguZj9lW3IuaW5kZXhPZihcInNldFwiKXx8XCJmdW5jdGlvblwiIT10eXBlb2YgZVtcImdldFwiK3Iuc3Vic3RyKDMpXT9yOlwiZ2V0XCIrci5zdWJzdHIoMyldKCk6cGFyc2VGbG9hdChlW3JdKSxoLmM9XCJzdHJpbmdcIj09dHlwZW9mIF8mJlwiPVwiPT09Xy5jaGFyQXQoMSk/cGFyc2VJbnQoXy5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcihfLnN1YnN0cigyKSk6TnVtYmVyKF8pLWguc3x8MDtoJiZoLl9uZXh0JiYoaC5fbmV4dC5fcHJldj1oKX1yZXR1cm4gbiYmdGhpcy5fa2lsbChuLGUpP3RoaXMuX2luaXRQcm9wcyhlLGkscyxuKTp0aGlzLl9vdmVyd3JpdGU+MSYmdGhpcy5fZmlyc3RQVCYmcy5sZW5ndGg+MSYmJChlLHRoaXMsaSx0aGlzLl9vdmVyd3JpdGUscyk/KHRoaXMuX2tpbGwoaSxlKSx0aGlzLl9pbml0UHJvcHMoZSxpLHMsbikpOih0aGlzLl9maXJzdFBUJiYodGhpcy52YXJzLmxhenkhPT0hMSYmdGhpcy5fZHVyYXRpb258fHRoaXMudmFycy5sYXp5JiYhdGhpcy5fZHVyYXRpb24pJiYoT1tlLl9nc1R3ZWVuSURdPSEwKSxvKX0sbi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3ZhciBzLG4scixhLG89dGhpcy5fdGltZSxsPXRoaXMuX2R1cmF0aW9uLF89dGhpcy5fcmF3UHJldlRpbWU7aWYodD49bCl0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT1sLHRoaXMucmF0aW89dGhpcy5fZWFzZS5fY2FsY0VuZD90aGlzLl9lYXNlLmdldFJhdGlvKDEpOjEsdGhpcy5fcmV2ZXJzZWR8fChzPSEwLG49XCJvbkNvbXBsZXRlXCIpLDA9PT1sJiYodGhpcy5faW5pdHRlZHx8IXRoaXMudmFycy5sYXp5fHxpKSYmKHRoaXMuX3N0YXJ0VGltZT09PXRoaXMuX3RpbWVsaW5lLl9kdXJhdGlvbiYmKHQ9MCksKDA9PT10fHwwPl98fF89PT1oKSYmXyE9PXQmJihpPSEwLF8+aCYmKG49XCJvblJldmVyc2VDb21wbGV0ZVwiKSksdGhpcy5fcmF3UHJldlRpbWU9YT0hZXx8dHx8Xz09PXQ/dDpoKTtlbHNlIGlmKDFlLTc+dCl0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT0wLHRoaXMucmF0aW89dGhpcy5fZWFzZS5fY2FsY0VuZD90aGlzLl9lYXNlLmdldFJhdGlvKDApOjAsKDAhPT1vfHwwPT09bCYmXz4wJiZfIT09aCkmJihuPVwib25SZXZlcnNlQ29tcGxldGVcIixzPXRoaXMuX3JldmVyc2VkKSwwPnQ/KHRoaXMuX2FjdGl2ZT0hMSwwPT09bCYmKHRoaXMuX2luaXR0ZWR8fCF0aGlzLnZhcnMubGF6eXx8aSkmJihfPj0wJiYoaT0hMCksdGhpcy5fcmF3UHJldlRpbWU9YT0hZXx8dHx8Xz09PXQ/dDpoKSk6dGhpcy5faW5pdHRlZHx8KGk9ITApO2Vsc2UgaWYodGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9dCx0aGlzLl9lYXNlVHlwZSl7dmFyIHU9dC9sLG09dGhpcy5fZWFzZVR5cGUsZj10aGlzLl9lYXNlUG93ZXI7KDE9PT1tfHwzPT09bSYmdT49LjUpJiYodT0xLXUpLDM9PT1tJiYodSo9MiksMT09PWY/dSo9dToyPT09Zj91Kj11KnU6Mz09PWY/dSo9dSp1KnU6ND09PWYmJih1Kj11KnUqdSp1KSx0aGlzLnJhdGlvPTE9PT1tPzEtdToyPT09bT91Oi41PnQvbD91LzI6MS11LzJ9ZWxzZSB0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8odC9sKTtpZih0aGlzLl90aW1lIT09b3x8aSl7aWYoIXRoaXMuX2luaXR0ZWQpe2lmKHRoaXMuX2luaXQoKSwhdGhpcy5faW5pdHRlZHx8dGhpcy5fZ2MpcmV0dXJuO2lmKCFpJiZ0aGlzLl9maXJzdFBUJiYodGhpcy52YXJzLmxhenkhPT0hMSYmdGhpcy5fZHVyYXRpb258fHRoaXMudmFycy5sYXp5JiYhdGhpcy5fZHVyYXRpb24pKXJldHVybiB0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT1vLHRoaXMuX3Jhd1ByZXZUaW1lPV8sei5wdXNoKHRoaXMpLHRoaXMuX2xhenk9dCx2b2lkIDA7dGhpcy5fdGltZSYmIXM/dGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKHRoaXMuX3RpbWUvbCk6cyYmdGhpcy5fZWFzZS5fY2FsY0VuZCYmKHRoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbygwPT09dGhpcy5fdGltZT8wOjEpKX1mb3IodGhpcy5fbGF6eSE9PSExJiYodGhpcy5fbGF6eT0hMSksdGhpcy5fYWN0aXZlfHwhdGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lIT09byYmdD49MCYmKHRoaXMuX2FjdGl2ZT0hMCksMD09PW8mJih0aGlzLl9zdGFydEF0JiYodD49MD90aGlzLl9zdGFydEF0LnJlbmRlcih0LGUsaSk6bnx8KG49XCJfZHVtbXlHU1wiKSksdGhpcy52YXJzLm9uU3RhcnQmJigwIT09dGhpcy5fdGltZXx8MD09PWwpJiYoZXx8dGhpcy52YXJzLm9uU3RhcnQuYXBwbHkodGhpcy52YXJzLm9uU3RhcnRTY29wZXx8dGhpcyx0aGlzLnZhcnMub25TdGFydFBhcmFtc3x8ZykpKSxyPXRoaXMuX2ZpcnN0UFQ7cjspci5mP3IudFtyLnBdKHIuYyp0aGlzLnJhdGlvK3Iucyk6ci50W3IucF09ci5jKnRoaXMucmF0aW8rci5zLHI9ci5fbmV4dDt0aGlzLl9vblVwZGF0ZSYmKDA+dCYmdGhpcy5fc3RhcnRBdCYmdGhpcy5fc3RhcnRUaW1lJiZ0aGlzLl9zdGFydEF0LnJlbmRlcih0LGUsaSksZXx8KHRoaXMuX3RpbWUhPT1vfHxzKSYmdGhpcy5fb25VcGRhdGUuYXBwbHkodGhpcy52YXJzLm9uVXBkYXRlU2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uVXBkYXRlUGFyYW1zfHxnKSksbiYmKHRoaXMuX2djfHwoMD50JiZ0aGlzLl9zdGFydEF0JiYhdGhpcy5fb25VcGRhdGUmJnRoaXMuX3N0YXJ0VGltZSYmdGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpLHMmJih0aGlzLl90aW1lbGluZS5hdXRvUmVtb3ZlQ2hpbGRyZW4mJnRoaXMuX2VuYWJsZWQoITEsITEpLHRoaXMuX2FjdGl2ZT0hMSksIWUmJnRoaXMudmFyc1tuXSYmdGhpcy52YXJzW25dLmFwcGx5KHRoaXMudmFyc1tuK1wiU2NvcGVcIl18fHRoaXMsdGhpcy52YXJzW24rXCJQYXJhbXNcIl18fGcpLDA9PT1sJiZ0aGlzLl9yYXdQcmV2VGltZT09PWgmJmEhPT1oJiYodGhpcy5fcmF3UHJldlRpbWU9MCkpKX19LG4uX2tpbGw9ZnVuY3Rpb24odCxlKXtpZihcImFsbFwiPT09dCYmKHQ9bnVsbCksbnVsbD09dCYmKG51bGw9PWV8fGU9PT10aGlzLnRhcmdldCkpcmV0dXJuIHRoaXMuX2xhenk9ITEsdGhpcy5fZW5hYmxlZCghMSwhMSk7ZT1cInN0cmluZ1wiIT10eXBlb2YgZT9lfHx0aGlzLl90YXJnZXRzfHx0aGlzLnRhcmdldDpELnNlbGVjdG9yKGUpfHxlO3ZhciBpLHMsbixyLGEsbyxsLGg7aWYoKG0oZSl8fEkoZSkpJiZcIm51bWJlclwiIT10eXBlb2YgZVswXSlmb3IoaT1lLmxlbmd0aDstLWk+LTE7KXRoaXMuX2tpbGwodCxlW2ldKSYmKG89ITApO2Vsc2V7aWYodGhpcy5fdGFyZ2V0cyl7Zm9yKGk9dGhpcy5fdGFyZ2V0cy5sZW5ndGg7LS1pPi0xOylpZihlPT09dGhpcy5fdGFyZ2V0c1tpXSl7YT10aGlzLl9wcm9wTG9va3VwW2ldfHx7fSx0aGlzLl9vdmVyd3JpdHRlblByb3BzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHN8fFtdLHM9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc1tpXT10P3RoaXMuX292ZXJ3cml0dGVuUHJvcHNbaV18fHt9OlwiYWxsXCI7YnJlYWt9fWVsc2V7aWYoZSE9PXRoaXMudGFyZ2V0KXJldHVybiExO2E9dGhpcy5fcHJvcExvb2t1cCxzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHM9dD90aGlzLl9vdmVyd3JpdHRlblByb3BzfHx7fTpcImFsbFwifWlmKGEpe2w9dHx8YSxoPXQhPT1zJiZcImFsbFwiIT09cyYmdCE9PWEmJihcIm9iamVjdFwiIT10eXBlb2YgdHx8IXQuX3RlbXBLaWxsKTtmb3IobiBpbiBsKShyPWFbbl0pJiYoci5wZyYmci50Ll9raWxsKGwpJiYobz0hMCksci5wZyYmMCE9PXIudC5fb3ZlcndyaXRlUHJvcHMubGVuZ3RofHwoci5fcHJldj9yLl9wcmV2Ll9uZXh0PXIuX25leHQ6cj09PXRoaXMuX2ZpcnN0UFQmJih0aGlzLl9maXJzdFBUPXIuX25leHQpLHIuX25leHQmJihyLl9uZXh0Ll9wcmV2PXIuX3ByZXYpLHIuX25leHQ9ci5fcHJldj1udWxsKSxkZWxldGUgYVtuXSksaCYmKHNbbl09MSk7IXRoaXMuX2ZpcnN0UFQmJnRoaXMuX2luaXR0ZWQmJnRoaXMuX2VuYWJsZWQoITEsITEpfX1yZXR1cm4gb30sbi5pbnZhbGlkYXRlPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQmJkQuX29uUGx1Z2luRXZlbnQoXCJfb25EaXNhYmxlXCIsdGhpcyksdGhpcy5fZmlyc3RQVD1udWxsLHRoaXMuX292ZXJ3cml0dGVuUHJvcHM9bnVsbCx0aGlzLl9vblVwZGF0ZT1udWxsLHRoaXMuX3N0YXJ0QXQ9bnVsbCx0aGlzLl9pbml0dGVkPXRoaXMuX2FjdGl2ZT10aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkPXRoaXMuX2xhenk9ITEsdGhpcy5fcHJvcExvb2t1cD10aGlzLl90YXJnZXRzP3t9OltdLHRoaXN9LG4uX2VuYWJsZWQ9ZnVuY3Rpb24odCxlKXtpZihhfHxyLndha2UoKSx0JiZ0aGlzLl9nYyl7dmFyIGkscz10aGlzLl90YXJnZXRzO2lmKHMpZm9yKGk9cy5sZW5ndGg7LS1pPi0xOyl0aGlzLl9zaWJsaW5nc1tpXT1NKHNbaV0sdGhpcywhMCk7ZWxzZSB0aGlzLl9zaWJsaW5ncz1NKHRoaXMudGFyZ2V0LHRoaXMsITApfXJldHVybiB4LnByb3RvdHlwZS5fZW5hYmxlZC5jYWxsKHRoaXMsdCxlKSx0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkJiZ0aGlzLl9maXJzdFBUP0QuX29uUGx1Z2luRXZlbnQodD9cIl9vbkVuYWJsZVwiOlwiX29uRGlzYWJsZVwiLHRoaXMpOiExfSxELnRvPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gbmV3IEQodCxlLGkpfSxELmZyb209ZnVuY3Rpb24odCxlLGkpe3JldHVybiBpLnJ1bkJhY2t3YXJkcz0hMCxpLmltbWVkaWF0ZVJlbmRlcj0wIT1pLmltbWVkaWF0ZVJlbmRlcixuZXcgRCh0LGUsaSl9LEQuZnJvbVRvPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiBzLnN0YXJ0QXQ9aSxzLmltbWVkaWF0ZVJlbmRlcj0wIT1zLmltbWVkaWF0ZVJlbmRlciYmMCE9aS5pbW1lZGlhdGVSZW5kZXIsbmV3IEQodCxlLHMpfSxELmRlbGF5ZWRDYWxsPWZ1bmN0aW9uKHQsZSxpLHMsbil7cmV0dXJuIG5ldyBEKGUsMCx7ZGVsYXk6dCxvbkNvbXBsZXRlOmUsb25Db21wbGV0ZVBhcmFtczppLG9uQ29tcGxldGVTY29wZTpzLG9uUmV2ZXJzZUNvbXBsZXRlOmUsb25SZXZlcnNlQ29tcGxldGVQYXJhbXM6aSxvblJldmVyc2VDb21wbGV0ZVNjb3BlOnMsaW1tZWRpYXRlUmVuZGVyOiExLHVzZUZyYW1lczpuLG92ZXJ3cml0ZTowfSl9LEQuc2V0PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG5ldyBEKHQsMCxlKX0sRC5nZXRUd2VlbnNPZj1mdW5jdGlvbih0LGUpe2lmKG51bGw9PXQpcmV0dXJuW107dD1cInN0cmluZ1wiIT10eXBlb2YgdD90OkQuc2VsZWN0b3IodCl8fHQ7dmFyIGkscyxuLHI7aWYoKG0odCl8fEkodCkpJiZcIm51bWJlclwiIT10eXBlb2YgdFswXSl7Zm9yKGk9dC5sZW5ndGgscz1bXTstLWk+LTE7KXM9cy5jb25jYXQoRC5nZXRUd2VlbnNPZih0W2ldLGUpKTtmb3IoaT1zLmxlbmd0aDstLWk+LTE7KWZvcihyPXNbaV0sbj1pOy0tbj4tMTspcj09PXNbbl0mJnMuc3BsaWNlKGksMSl9ZWxzZSBmb3Iocz1NKHQpLmNvbmNhdCgpLGk9cy5sZW5ndGg7LS1pPi0xOykoc1tpXS5fZ2N8fGUmJiFzW2ldLmlzQWN0aXZlKCkpJiZzLnNwbGljZShpLDEpO3JldHVybiBzfSxELmtpbGxUd2VlbnNPZj1ELmtpbGxEZWxheWVkQ2FsbHNUbz1mdW5jdGlvbih0LGUsaSl7XCJvYmplY3RcIj09dHlwZW9mIGUmJihpPWUsZT0hMSk7Zm9yKHZhciBzPUQuZ2V0VHdlZW5zT2YodCxlKSxuPXMubGVuZ3RoOy0tbj4tMTspc1tuXS5fa2lsbChpLHQpfTt2YXIgSD1kKFwicGx1Z2lucy5Ud2VlblBsdWdpblwiLGZ1bmN0aW9uKHQsZSl7dGhpcy5fb3ZlcndyaXRlUHJvcHM9KHR8fFwiXCIpLnNwbGl0KFwiLFwiKSx0aGlzLl9wcm9wTmFtZT10aGlzLl9vdmVyd3JpdGVQcm9wc1swXSx0aGlzLl9wcmlvcml0eT1lfHwwLHRoaXMuX3N1cGVyPUgucHJvdG90eXBlfSwhMCk7aWYobj1ILnByb3RvdHlwZSxILnZlcnNpb249XCIxLjEwLjFcIixILkFQST0yLG4uX2ZpcnN0UFQ9bnVsbCxuLl9hZGRUd2Vlbj1mdW5jdGlvbih0LGUsaSxzLG4scil7dmFyIGEsbztyZXR1cm4gbnVsbCE9cyYmKGE9XCJudW1iZXJcIj09dHlwZW9mIHN8fFwiPVwiIT09cy5jaGFyQXQoMSk/TnVtYmVyKHMpLWk6cGFyc2VJbnQocy5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcihzLnN1YnN0cigyKSkpPyh0aGlzLl9maXJzdFBUPW89e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDp0LHA6ZSxzOmksYzphLGY6XCJmdW5jdGlvblwiPT10eXBlb2YgdFtlXSxuOm58fGUscjpyfSxvLl9uZXh0JiYoby5fbmV4dC5fcHJldj1vKSxvKTp2b2lkIDB9LG4uc2V0UmF0aW89ZnVuY3Rpb24odCl7Zm9yKHZhciBlLGk9dGhpcy5fZmlyc3RQVCxzPTFlLTY7aTspZT1pLmMqdCtpLnMsaS5yP2U9TWF0aC5yb3VuZChlKTpzPmUmJmU+LXMmJihlPTApLGkuZj9pLnRbaS5wXShlKTppLnRbaS5wXT1lLGk9aS5fbmV4dH0sbi5fa2lsbD1mdW5jdGlvbih0KXt2YXIgZSxpPXRoaXMuX292ZXJ3cml0ZVByb3BzLHM9dGhpcy5fZmlyc3RQVDtpZihudWxsIT10W3RoaXMuX3Byb3BOYW1lXSl0aGlzLl9vdmVyd3JpdGVQcm9wcz1bXTtlbHNlIGZvcihlPWkubGVuZ3RoOy0tZT4tMTspbnVsbCE9dFtpW2VdXSYmaS5zcGxpY2UoZSwxKTtmb3IoO3M7KW51bGwhPXRbcy5uXSYmKHMuX25leHQmJihzLl9uZXh0Ll9wcmV2PXMuX3ByZXYpLHMuX3ByZXY/KHMuX3ByZXYuX25leHQ9cy5fbmV4dCxzLl9wcmV2PW51bGwpOnRoaXMuX2ZpcnN0UFQ9PT1zJiYodGhpcy5fZmlyc3RQVD1zLl9uZXh0KSkscz1zLl9uZXh0O3JldHVybiExfSxuLl9yb3VuZFByb3BzPWZ1bmN0aW9uKHQsZSl7Zm9yKHZhciBpPXRoaXMuX2ZpcnN0UFQ7aTspKHRbdGhpcy5fcHJvcE5hbWVdfHxudWxsIT1pLm4mJnRbaS5uLnNwbGl0KHRoaXMuX3Byb3BOYW1lK1wiX1wiKS5qb2luKFwiXCIpXSkmJihpLnI9ZSksaT1pLl9uZXh0fSxELl9vblBsdWdpbkV2ZW50PWZ1bmN0aW9uKHQsZSl7dmFyIGkscyxuLHIsYSxvPWUuX2ZpcnN0UFQ7aWYoXCJfb25Jbml0QWxsUHJvcHNcIj09PXQpe2Zvcig7bzspe2ZvcihhPW8uX25leHQscz1uO3MmJnMucHI+by5wcjspcz1zLl9uZXh0OyhvLl9wcmV2PXM/cy5fcHJldjpyKT9vLl9wcmV2Ll9uZXh0PW86bj1vLChvLl9uZXh0PXMpP3MuX3ByZXY9bzpyPW8sbz1hfW89ZS5fZmlyc3RQVD1ufWZvcig7bzspby5wZyYmXCJmdW5jdGlvblwiPT10eXBlb2Ygby50W3RdJiZvLnRbdF0oKSYmKGk9ITApLG89by5fbmV4dDtyZXR1cm4gaX0sSC5hY3RpdmF0ZT1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC5sZW5ndGg7LS1lPi0xOyl0W2VdLkFQST09PUguQVBJJiYoTFsobmV3IHRbZV0pLl9wcm9wTmFtZV09dFtlXSk7cmV0dXJuITB9LGMucGx1Z2luPWZ1bmN0aW9uKHQpe2lmKCEodCYmdC5wcm9wTmFtZSYmdC5pbml0JiZ0LkFQSSkpdGhyb3dcImlsbGVnYWwgcGx1Z2luIGRlZmluaXRpb24uXCI7dmFyIGUsaT10LnByb3BOYW1lLHM9dC5wcmlvcml0eXx8MCxuPXQub3ZlcndyaXRlUHJvcHMscj17aW5pdDpcIl9vbkluaXRUd2VlblwiLHNldDpcInNldFJhdGlvXCIsa2lsbDpcIl9raWxsXCIscm91bmQ6XCJfcm91bmRQcm9wc1wiLGluaXRBbGw6XCJfb25Jbml0QWxsUHJvcHNcIn0sYT1kKFwicGx1Z2lucy5cIitpLmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK2kuc3Vic3RyKDEpK1wiUGx1Z2luXCIsZnVuY3Rpb24oKXtILmNhbGwodGhpcyxpLHMpLHRoaXMuX292ZXJ3cml0ZVByb3BzPW58fFtdfSx0Lmdsb2JhbD09PSEwKSxvPWEucHJvdG90eXBlPW5ldyBIKGkpO28uY29uc3RydWN0b3I9YSxhLkFQST10LkFQSTtmb3IoZSBpbiByKVwiZnVuY3Rpb25cIj09dHlwZW9mIHRbZV0mJihvW3JbZV1dPXRbZV0pO3JldHVybiBhLnZlcnNpb249dC52ZXJzaW9uLEguYWN0aXZhdGUoW2FdKSxhfSxpPXQuX2dzUXVldWUpe2ZvcihzPTA7aS5sZW5ndGg+cztzKyspaVtzXSgpO2ZvcihuIGluIGYpZltuXS5mdW5jfHx0LmNvbnNvbGUubG9nKFwiR1NBUCBlbmNvdW50ZXJlZCBtaXNzaW5nIGRlcGVuZGVuY3k6IGNvbS5ncmVlbnNvY2suXCIrbil9YT0hMX19KSh3aW5kb3cpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiBiZXRhIDEuOS4zXHJcbiAqIERBVEU6IDIwMTMtMDQtMDJcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwiZWFzaW5nLkJhY2tcIixbXCJlYXNpbmcuRWFzZVwiXSxmdW5jdGlvbih0KXt2YXIgZSxpLHMscj13aW5kb3cuR3JlZW5Tb2NrR2xvYmFsc3x8d2luZG93LG49ci5jb20uZ3JlZW5zb2NrLGE9MipNYXRoLlBJLG89TWF0aC5QSS8yLGg9bi5fY2xhc3MsbD1mdW5jdGlvbihlLGkpe3ZhciBzPWgoXCJlYXNpbmcuXCIrZSxmdW5jdGlvbigpe30sITApLHI9cy5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIHIuY29uc3RydWN0b3I9cyxyLmdldFJhdGlvPWksc30sXz10LnJlZ2lzdGVyfHxmdW5jdGlvbigpe30sdT1mdW5jdGlvbih0LGUsaSxzKXt2YXIgcj1oKFwiZWFzaW5nLlwiK3Qse2Vhc2VPdXQ6bmV3IGUsZWFzZUluOm5ldyBpLGVhc2VJbk91dDpuZXcgc30sITApO3JldHVybiBfKHIsdCkscn0sYz1mdW5jdGlvbih0LGUsaSl7dGhpcy50PXQsdGhpcy52PWUsaSYmKHRoaXMubmV4dD1pLGkucHJldj10aGlzLHRoaXMuYz1pLnYtZSx0aGlzLmdhcD1pLnQtdCl9LGY9ZnVuY3Rpb24oZSxpKXt2YXIgcz1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24odCl7dGhpcy5fcDE9dHx8MD09PXQ/dDoxLjcwMTU4LHRoaXMuX3AyPTEuNTI1KnRoaXMuX3AxfSwhMCkscj1zLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gci5jb25zdHJ1Y3Rvcj1zLHIuZ2V0UmF0aW89aSxyLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IHModCl9LHN9LHA9dShcIkJhY2tcIixmKFwiQmFja091dFwiLGZ1bmN0aW9uKHQpe3JldHVybih0LT0xKSp0KigodGhpcy5fcDErMSkqdCt0aGlzLl9wMSkrMX0pLGYoXCJCYWNrSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gdCp0KigodGhpcy5fcDErMSkqdC10aGlzLl9wMSl9KSxmKFwiQmFja0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy41KnQqdCooKHRoaXMuX3AyKzEpKnQtdGhpcy5fcDIpOi41KigodC09MikqdCooKHRoaXMuX3AyKzEpKnQrdGhpcy5fcDIpKzIpfSkpLG09aChcImVhc2luZy5TbG93TW9cIixmdW5jdGlvbih0LGUsaSl7ZT1lfHwwPT09ZT9lOi43LG51bGw9PXQ/dD0uNzp0PjEmJih0PTEpLHRoaXMuX3A9MSE9PXQ/ZTowLHRoaXMuX3AxPSgxLXQpLzIsdGhpcy5fcDI9dCx0aGlzLl9wMz10aGlzLl9wMSt0aGlzLl9wMix0aGlzLl9jYWxjRW5kPWk9PT0hMH0sITApLGQ9bS5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIGQuY29uc3RydWN0b3I9bSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlPXQrKC41LXQpKnRoaXMuX3A7cmV0dXJuIHRoaXMuX3AxPnQ/dGhpcy5fY2FsY0VuZD8xLSh0PTEtdC90aGlzLl9wMSkqdDplLSh0PTEtdC90aGlzLl9wMSkqdCp0KnQqZTp0PnRoaXMuX3AzP3RoaXMuX2NhbGNFbmQ/MS0odD0odC10aGlzLl9wMykvdGhpcy5fcDEpKnQ6ZSsodC1lKSoodD0odC10aGlzLl9wMykvdGhpcy5fcDEpKnQqdCp0OnRoaXMuX2NhbGNFbmQ/MTplfSxtLmVhc2U9bmV3IG0oLjcsLjcpLGQuY29uZmlnPW0uY29uZmlnPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gbmV3IG0odCxlLGkpfSxlPWgoXCJlYXNpbmcuU3RlcHBlZEVhc2VcIixmdW5jdGlvbih0KXt0PXR8fDEsdGhpcy5fcDE9MS90LHRoaXMuX3AyPXQrMX0sITApLGQ9ZS5wcm90b3R5cGU9bmV3IHQsZC5jb25zdHJ1Y3Rvcj1lLGQuZ2V0UmF0aW89ZnVuY3Rpb24odCl7cmV0dXJuIDA+dD90PTA6dD49MSYmKHQ9Ljk5OTk5OTk5OSksKHRoaXMuX3AyKnQ+PjApKnRoaXMuX3AxfSxkLmNvbmZpZz1lLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IGUodCl9LGk9aChcImVhc2luZy5Sb3VnaEVhc2VcIixmdW5jdGlvbihlKXtlPWV8fHt9O2Zvcih2YXIgaSxzLHIsbixhLG8saD1lLnRhcGVyfHxcIm5vbmVcIixsPVtdLF89MCx1PTB8KGUucG9pbnRzfHwyMCksZj11LHA9ZS5yYW5kb21pemUhPT0hMSxtPWUuY2xhbXA9PT0hMCxkPWUudGVtcGxhdGUgaW5zdGFuY2VvZiB0P2UudGVtcGxhdGU6bnVsbCxnPVwibnVtYmVyXCI9PXR5cGVvZiBlLnN0cmVuZ3RoPy40KmUuc3RyZW5ndGg6LjQ7LS1mPi0xOylpPXA/TWF0aC5yYW5kb20oKToxL3UqZixzPWQ/ZC5nZXRSYXRpbyhpKTppLFwibm9uZVwiPT09aD9yPWc6XCJvdXRcIj09PWg/KG49MS1pLHI9bipuKmcpOlwiaW5cIj09PWg/cj1pKmkqZzouNT5pPyhuPTIqaSxyPS41Km4qbipnKToobj0yKigxLWkpLHI9LjUqbipuKmcpLHA/cys9TWF0aC5yYW5kb20oKSpyLS41KnI6ZiUyP3MrPS41KnI6cy09LjUqcixtJiYocz4xP3M9MTowPnMmJihzPTApKSxsW18rK109e3g6aSx5OnN9O2ZvcihsLnNvcnQoZnVuY3Rpb24odCxlKXtyZXR1cm4gdC54LWUueH0pLG89bmV3IGMoMSwxLG51bGwpLGY9dTstLWY+LTE7KWE9bFtmXSxvPW5ldyBjKGEueCxhLnksbyk7dGhpcy5fcHJldj1uZXcgYygwLDAsMCE9PW8udD9vOm8ubmV4dCl9LCEwKSxkPWkucHJvdG90eXBlPW5ldyB0LGQuY29uc3RydWN0b3I9aSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlPXRoaXMuX3ByZXY7aWYodD5lLnQpe2Zvcig7ZS5uZXh0JiZ0Pj1lLnQ7KWU9ZS5uZXh0O2U9ZS5wcmV2fWVsc2UgZm9yKDtlLnByZXYmJmUudD49dDspZT1lLnByZXY7cmV0dXJuIHRoaXMuX3ByZXY9ZSxlLnYrKHQtZS50KS9lLmdhcCplLmN9LGQuY29uZmlnPWZ1bmN0aW9uKHQpe3JldHVybiBuZXcgaSh0KX0saS5lYXNlPW5ldyBpLHUoXCJCb3VuY2VcIixsKFwiQm91bmNlT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDEvMi43NT50PzcuNTYyNSp0KnQ6Mi8yLjc1PnQ/Ny41NjI1Kih0LT0xLjUvMi43NSkqdCsuNzU6Mi41LzIuNzU+dD83LjU2MjUqKHQtPTIuMjUvMi43NSkqdCsuOTM3NTo3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NX0pLGwoXCJCb3VuY2VJblwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLzIuNzU+KHQ9MS10KT8xLTcuNTYyNSp0KnQ6Mi8yLjc1PnQ/MS0oNy41NjI1Kih0LT0xLjUvMi43NSkqdCsuNzUpOjIuNS8yLjc1PnQ/MS0oNy41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzUpOjEtKDcuNTYyNSoodC09Mi42MjUvMi43NSkqdCsuOTg0Mzc1KX0pLGwoXCJCb3VuY2VJbk91dFwiLGZ1bmN0aW9uKHQpe3ZhciBlPS41PnQ7cmV0dXJuIHQ9ZT8xLTIqdDoyKnQtMSx0PTEvMi43NT50PzcuNTYyNSp0KnQ6Mi8yLjc1PnQ/Ny41NjI1Kih0LT0xLjUvMi43NSkqdCsuNzU6Mi41LzIuNzU+dD83LjU2MjUqKHQtPTIuMjUvMi43NSkqdCsuOTM3NTo3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NSxlPy41KigxLXQpOi41KnQrLjV9KSksdShcIkNpcmNcIixsKFwiQ2lyY091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnNxcnQoMS0odC09MSkqdCl9KSxsKFwiQ2lyY0luXCIsZnVuY3Rpb24odCl7cmV0dXJuLShNYXRoLnNxcnQoMS10KnQpLTEpfSksbChcIkNpcmNJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8tLjUqKE1hdGguc3FydCgxLXQqdCktMSk6LjUqKE1hdGguc3FydCgxLSh0LT0yKSp0KSsxKX0pKSxzPWZ1bmN0aW9uKGUsaSxzKXt2YXIgcj1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24odCxlKXt0aGlzLl9wMT10fHwxLHRoaXMuX3AyPWV8fHMsdGhpcy5fcDM9dGhpcy5fcDIvYSooTWF0aC5hc2luKDEvdGhpcy5fcDEpfHwwKX0sITApLG49ci5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIG4uY29uc3RydWN0b3I9cixuLmdldFJhdGlvPWksbi5jb25maWc9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbmV3IHIodCxlKX0scn0sdShcIkVsYXN0aWNcIixzKFwiRWxhc3RpY091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiB0aGlzLl9wMSpNYXRoLnBvdygyLC0xMCp0KSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikrMX0sLjMpLHMoXCJFbGFzdGljSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tKHRoaXMuX3AxKk1hdGgucG93KDIsMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikpfSwuMykscyhcIkVsYXN0aWNJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8tLjUqdGhpcy5fcDEqTWF0aC5wb3coMiwxMCoodC09MSkpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKTouNSp0aGlzLl9wMSpNYXRoLnBvdygyLC0xMCoodC09MSkpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKSsxfSwuNDUpKSx1KFwiRXhwb1wiLGwoXCJFeHBvT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDEtTWF0aC5wb3coMiwtMTAqdCl9KSxsKFwiRXhwb0luXCIsZnVuY3Rpb24odCl7cmV0dXJuIE1hdGgucG93KDIsMTAqKHQtMSkpLS4wMDF9KSxsKFwiRXhwb0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy41Kk1hdGgucG93KDIsMTAqKHQtMSkpOi41KigyLU1hdGgucG93KDIsLTEwKih0LTEpKSl9KSksdShcIlNpbmVcIixsKFwiU2luZU91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnNpbih0Km8pfSksbChcIlNpbmVJblwiLGZ1bmN0aW9uKHQpe3JldHVybi1NYXRoLmNvcyh0Km8pKzF9KSxsKFwiU2luZUluT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuLS41KihNYXRoLmNvcyhNYXRoLlBJKnQpLTEpfSkpLGgoXCJlYXNpbmcuRWFzZUxvb2t1cFwiLHtmaW5kOmZ1bmN0aW9uKGUpe3JldHVybiB0Lm1hcFtlXX19LCEwKSxfKHIuU2xvd01vLFwiU2xvd01vXCIsXCJlYXNlLFwiKSxfKGksXCJSb3VnaEVhc2VcIixcImVhc2UsXCIpLF8oZSxcIlN0ZXBwZWRFYXNlXCIsXCJlYXNlLFwiKSxwfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJwbHVnaW5zLkNTU1BsdWdpblwiLFtcInBsdWdpbnMuVHdlZW5QbHVnaW5cIixcIlR3ZWVuTGl0ZVwiXSxmdW5jdGlvbih0LGUpe3ZhciBpLHIscyxuLGE9ZnVuY3Rpb24oKXt0LmNhbGwodGhpcyxcImNzc1wiKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5sZW5ndGg9MCx0aGlzLnNldFJhdGlvPWEucHJvdG90eXBlLnNldFJhdGlvfSxvPXt9LGw9YS5wcm90b3R5cGU9bmV3IHQoXCJjc3NcIik7bC5jb25zdHJ1Y3Rvcj1hLGEudmVyc2lvbj1cIjEuMTIuMVwiLGEuQVBJPTIsYS5kZWZhdWx0VHJhbnNmb3JtUGVyc3BlY3RpdmU9MCxhLmRlZmF1bHRTa2V3VHlwZT1cImNvbXBlbnNhdGVkXCIsbD1cInB4XCIsYS5zdWZmaXhNYXA9e3RvcDpsLHJpZ2h0OmwsYm90dG9tOmwsbGVmdDpsLHdpZHRoOmwsaGVpZ2h0OmwsZm9udFNpemU6bCxwYWRkaW5nOmwsbWFyZ2luOmwscGVyc3BlY3RpdmU6bCxsaW5lSGVpZ2h0OlwiXCJ9O3ZhciBoLHUsZixfLHAsYyxkPS8oPzpcXGR8XFwtXFxkfFxcLlxcZHxcXC1cXC5cXGQpKy9nLG09Lyg/OlxcZHxcXC1cXGR8XFwuXFxkfFxcLVxcLlxcZHxcXCs9XFxkfFxcLT1cXGR8XFwrPS5cXGR8XFwtPVxcLlxcZCkrL2csZz0vKD86XFwrPXxcXC09fFxcLXxcXGIpW1xcZFxcLVxcLl0rW2EtekEtWjAtOV0qKD86JXxcXGIpL2dpLHY9L1teXFxkXFwtXFwuXS9nLHk9Lyg/OlxcZHxcXC18XFwrfD18I3xcXC4pKi9nLFQ9L29wYWNpdHkgKj0gKihbXildKikvaSx3PS9vcGFjaXR5OihbXjtdKikvaSx4PS9hbHBoYVxcKG9wYWNpdHkgKj0uKz9cXCkvaSxiPS9eKHJnYnxoc2wpLyxQPS8oW0EtWl0pL2csUz0vLShbYS16XSkvZ2ksQz0vKF4oPzp1cmxcXChcXFwifHVybFxcKCkpfCg/OihcXFwiXFwpKSR8XFwpJCkvZ2ksUj1mdW5jdGlvbih0LGUpe3JldHVybiBlLnRvVXBwZXJDYXNlKCl9LGs9Lyg/OkxlZnR8UmlnaHR8V2lkdGgpL2ksQT0vKE0xMXxNMTJ8TTIxfE0yMik9W1xcZFxcLVxcLmVdKy9naSxPPS9wcm9naWRcXDpEWEltYWdlVHJhbnNmb3JtXFwuTWljcm9zb2Z0XFwuTWF0cml4XFwoLis/XFwpL2ksRD0vLCg/PVteXFwpXSooPzpcXCh8JCkpL2dpLE09TWF0aC5QSS8xODAsTD0xODAvTWF0aC5QSSxOPXt9LFg9ZG9jdW1lbnQsej1YLmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiksST1YLmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiksRT1hLl9pbnRlcm5hbHM9e19zcGVjaWFsUHJvcHM6b30sRj1uYXZpZ2F0b3IudXNlckFnZW50LFk9ZnVuY3Rpb24oKXt2YXIgdCxlPUYuaW5kZXhPZihcIkFuZHJvaWRcIiksaT1YLmNyZWF0ZUVsZW1lbnQoXCJkaXZcIik7cmV0dXJuIGY9LTEhPT1GLmluZGV4T2YoXCJTYWZhcmlcIikmJi0xPT09Ri5pbmRleE9mKFwiQ2hyb21lXCIpJiYoLTE9PT1lfHxOdW1iZXIoRi5zdWJzdHIoZSs4LDEpKT4zKSxwPWYmJjY+TnVtYmVyKEYuc3Vic3RyKEYuaW5kZXhPZihcIlZlcnNpb24vXCIpKzgsMSkpLF89LTEhPT1GLmluZGV4T2YoXCJGaXJlZm94XCIpLC9NU0lFIChbMC05XXsxLH1bXFwuMC05XXswLH0pLy5leGVjKEYpJiYoYz1wYXJzZUZsb2F0KFJlZ0V4cC4kMSkpLGkuaW5uZXJIVE1MPVwiPGEgc3R5bGU9J3RvcDoxcHg7b3BhY2l0eTouNTU7Jz5hPC9hPlwiLHQ9aS5nZXRFbGVtZW50c0J5VGFnTmFtZShcImFcIilbMF0sdD8vXjAuNTUvLnRlc3QodC5zdHlsZS5vcGFjaXR5KTohMX0oKSxCPWZ1bmN0aW9uKHQpe3JldHVybiBULnRlc3QoXCJzdHJpbmdcIj09dHlwZW9mIHQ/dDoodC5jdXJyZW50U3R5bGU/dC5jdXJyZW50U3R5bGUuZmlsdGVyOnQuc3R5bGUuZmlsdGVyKXx8XCJcIik/cGFyc2VGbG9hdChSZWdFeHAuJDEpLzEwMDoxfSxVPWZ1bmN0aW9uKHQpe3dpbmRvdy5jb25zb2xlJiZjb25zb2xlLmxvZyh0KX0sVz1cIlwiLGo9XCJcIixWPWZ1bmN0aW9uKHQsZSl7ZT1lfHx6O3ZhciBpLHIscz1lLnN0eWxlO2lmKHZvaWQgMCE9PXNbdF0pcmV0dXJuIHQ7Zm9yKHQ9dC5jaGFyQXQoMCkudG9VcHBlckNhc2UoKSt0LnN1YnN0cigxKSxpPVtcIk9cIixcIk1velwiLFwibXNcIixcIk1zXCIsXCJXZWJraXRcIl0scj01Oy0tcj4tMSYmdm9pZCAwPT09c1tpW3JdK3RdOyk7cmV0dXJuIHI+PTA/KGo9Mz09PXI/XCJtc1wiOmlbcl0sVz1cIi1cIitqLnRvTG93ZXJDYXNlKCkrXCItXCIsait0KTpudWxsfSxIPVguZGVmYXVsdFZpZXc/WC5kZWZhdWx0Vmlldy5nZXRDb21wdXRlZFN0eWxlOmZ1bmN0aW9uKCl7fSxxPWEuZ2V0U3R5bGU9ZnVuY3Rpb24odCxlLGkscixzKXt2YXIgbjtyZXR1cm4gWXx8XCJvcGFjaXR5XCIhPT1lPyghciYmdC5zdHlsZVtlXT9uPXQuc3R5bGVbZV06KGk9aXx8SCh0KSk/bj1pW2VdfHxpLmdldFByb3BlcnR5VmFsdWUoZSl8fGkuZ2V0UHJvcGVydHlWYWx1ZShlLnJlcGxhY2UoUCxcIi0kMVwiKS50b0xvd2VyQ2FzZSgpKTp0LmN1cnJlbnRTdHlsZSYmKG49dC5jdXJyZW50U3R5bGVbZV0pLG51bGw9PXN8fG4mJlwibm9uZVwiIT09biYmXCJhdXRvXCIhPT1uJiZcImF1dG8gYXV0b1wiIT09bj9uOnMpOkIodCl9LFE9RS5jb252ZXJ0VG9QaXhlbHM9ZnVuY3Rpb24odCxpLHIscyxuKXtpZihcInB4XCI9PT1zfHwhcylyZXR1cm4gcjtpZihcImF1dG9cIj09PXN8fCFyKXJldHVybiAwO3ZhciBvLGwsaCx1PWsudGVzdChpKSxmPXQsXz16LnN0eWxlLHA9MD5yO2lmKHAmJihyPS1yKSxcIiVcIj09PXMmJi0xIT09aS5pbmRleE9mKFwiYm9yZGVyXCIpKW89ci8xMDAqKHU/dC5jbGllbnRXaWR0aDp0LmNsaWVudEhlaWdodCk7ZWxzZXtpZihfLmNzc1RleHQ9XCJib3JkZXI6MCBzb2xpZCByZWQ7cG9zaXRpb246XCIrcSh0LFwicG9zaXRpb25cIikrXCI7bGluZS1oZWlnaHQ6MDtcIixcIiVcIiE9PXMmJmYuYXBwZW5kQ2hpbGQpX1t1P1wiYm9yZGVyTGVmdFdpZHRoXCI6XCJib3JkZXJUb3BXaWR0aFwiXT1yK3M7ZWxzZXtpZihmPXQucGFyZW50Tm9kZXx8WC5ib2R5LGw9Zi5fZ3NDYWNoZSxoPWUudGlja2VyLmZyYW1lLGwmJnUmJmwudGltZT09PWgpcmV0dXJuIGwud2lkdGgqci8xMDA7X1t1P1wid2lkdGhcIjpcImhlaWdodFwiXT1yK3N9Zi5hcHBlbmRDaGlsZCh6KSxvPXBhcnNlRmxvYXQoelt1P1wib2Zmc2V0V2lkdGhcIjpcIm9mZnNldEhlaWdodFwiXSksZi5yZW1vdmVDaGlsZCh6KSx1JiZcIiVcIj09PXMmJmEuY2FjaGVXaWR0aHMhPT0hMSYmKGw9Zi5fZ3NDYWNoZT1mLl9nc0NhY2hlfHx7fSxsLnRpbWU9aCxsLndpZHRoPTEwMCooby9yKSksMCE9PW98fG58fChvPVEodCxpLHIscywhMCkpfXJldHVybiBwPy1vOm99LFo9RS5jYWxjdWxhdGVPZmZzZXQ9ZnVuY3Rpb24odCxlLGkpe2lmKFwiYWJzb2x1dGVcIiE9PXEodCxcInBvc2l0aW9uXCIsaSkpcmV0dXJuIDA7dmFyIHI9XCJsZWZ0XCI9PT1lP1wiTGVmdFwiOlwiVG9wXCIscz1xKHQsXCJtYXJnaW5cIityLGkpO3JldHVybiB0W1wib2Zmc2V0XCIrcl0tKFEodCxlLHBhcnNlRmxvYXQocykscy5yZXBsYWNlKHksXCJcIikpfHwwKX0sJD1mdW5jdGlvbih0LGUpe3ZhciBpLHIscz17fTtpZihlPWV8fEgodCxudWxsKSlpZihpPWUubGVuZ3RoKWZvcig7LS1pPi0xOylzW2VbaV0ucmVwbGFjZShTLFIpXT1lLmdldFByb3BlcnR5VmFsdWUoZVtpXSk7ZWxzZSBmb3IoaSBpbiBlKXNbaV09ZVtpXTtlbHNlIGlmKGU9dC5jdXJyZW50U3R5bGV8fHQuc3R5bGUpZm9yKGkgaW4gZSlcInN0cmluZ1wiPT10eXBlb2YgaSYmdm9pZCAwPT09c1tpXSYmKHNbaS5yZXBsYWNlKFMsUildPWVbaV0pO3JldHVybiBZfHwocy5vcGFjaXR5PUIodCkpLHI9UGUodCxlLCExKSxzLnJvdGF0aW9uPXIucm90YXRpb24scy5za2V3WD1yLnNrZXdYLHMuc2NhbGVYPXIuc2NhbGVYLHMuc2NhbGVZPXIuc2NhbGVZLHMueD1yLngscy55PXIueSx4ZSYmKHMuej1yLnoscy5yb3RhdGlvblg9ci5yb3RhdGlvblgscy5yb3RhdGlvblk9ci5yb3RhdGlvblkscy5zY2FsZVo9ci5zY2FsZVopLHMuZmlsdGVycyYmZGVsZXRlIHMuZmlsdGVycyxzfSxHPWZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG4sYSxvLGw9e30saD10LnN0eWxlO2ZvcihhIGluIGkpXCJjc3NUZXh0XCIhPT1hJiZcImxlbmd0aFwiIT09YSYmaXNOYU4oYSkmJihlW2FdIT09KG49aVthXSl8fHMmJnNbYV0pJiYtMT09PWEuaW5kZXhPZihcIk9yaWdpblwiKSYmKFwibnVtYmVyXCI9PXR5cGVvZiBufHxcInN0cmluZ1wiPT10eXBlb2YgbikmJihsW2FdPVwiYXV0b1wiIT09bnx8XCJsZWZ0XCIhPT1hJiZcInRvcFwiIT09YT9cIlwiIT09biYmXCJhdXRvXCIhPT1uJiZcIm5vbmVcIiE9PW58fFwic3RyaW5nXCIhPXR5cGVvZiBlW2FdfHxcIlwiPT09ZVthXS5yZXBsYWNlKHYsXCJcIik/bjowOloodCxhKSx2b2lkIDAhPT1oW2FdJiYobz1uZXcgZmUoaCxhLGhbYV0sbykpKTtpZihyKWZvcihhIGluIHIpXCJjbGFzc05hbWVcIiE9PWEmJihsW2FdPXJbYV0pO3JldHVybntkaWZzOmwsZmlyc3RNUFQ6b319LEs9e3dpZHRoOltcIkxlZnRcIixcIlJpZ2h0XCJdLGhlaWdodDpbXCJUb3BcIixcIkJvdHRvbVwiXX0sSj1bXCJtYXJnaW5MZWZ0XCIsXCJtYXJnaW5SaWdodFwiLFwibWFyZ2luVG9wXCIsXCJtYXJnaW5Cb3R0b21cIl0sdGU9ZnVuY3Rpb24odCxlLGkpe3ZhciByPXBhcnNlRmxvYXQoXCJ3aWR0aFwiPT09ZT90Lm9mZnNldFdpZHRoOnQub2Zmc2V0SGVpZ2h0KSxzPUtbZV0sbj1zLmxlbmd0aDtmb3IoaT1pfHxIKHQsbnVsbCk7LS1uPi0xOylyLT1wYXJzZUZsb2F0KHEodCxcInBhZGRpbmdcIitzW25dLGksITApKXx8MCxyLT1wYXJzZUZsb2F0KHEodCxcImJvcmRlclwiK3Nbbl0rXCJXaWR0aFwiLGksITApKXx8MDtyZXR1cm4gcn0sZWU9ZnVuY3Rpb24odCxlKXsobnVsbD09dHx8XCJcIj09PXR8fFwiYXV0b1wiPT09dHx8XCJhdXRvIGF1dG9cIj09PXQpJiYodD1cIjAgMFwiKTt2YXIgaT10LnNwbGl0KFwiIFwiKSxyPS0xIT09dC5pbmRleE9mKFwibGVmdFwiKT9cIjAlXCI6LTEhPT10LmluZGV4T2YoXCJyaWdodFwiKT9cIjEwMCVcIjppWzBdLHM9LTEhPT10LmluZGV4T2YoXCJ0b3BcIik/XCIwJVwiOi0xIT09dC5pbmRleE9mKFwiYm90dG9tXCIpP1wiMTAwJVwiOmlbMV07cmV0dXJuIG51bGw9PXM/cz1cIjBcIjpcImNlbnRlclwiPT09cyYmKHM9XCI1MCVcIiksKFwiY2VudGVyXCI9PT1yfHxpc05hTihwYXJzZUZsb2F0KHIpKSYmLTE9PT0ocitcIlwiKS5pbmRleE9mKFwiPVwiKSkmJihyPVwiNTAlXCIpLGUmJihlLm94cD0tMSE9PXIuaW5kZXhPZihcIiVcIiksZS5veXA9LTEhPT1zLmluZGV4T2YoXCIlXCIpLGUub3hyPVwiPVwiPT09ci5jaGFyQXQoMSksZS5veXI9XCI9XCI9PT1zLmNoYXJBdCgxKSxlLm94PXBhcnNlRmxvYXQoci5yZXBsYWNlKHYsXCJcIikpLGUub3k9cGFyc2VGbG9hdChzLnJlcGxhY2UodixcIlwiKSkpLHIrXCIgXCIrcysoaS5sZW5ndGg+Mj9cIiBcIitpWzJdOlwiXCIpfSxpZT1mdW5jdGlvbih0LGUpe3JldHVyblwic3RyaW5nXCI9PXR5cGVvZiB0JiZcIj1cIj09PXQuY2hhckF0KDEpP3BhcnNlSW50KHQuY2hhckF0KDApK1wiMVwiLDEwKSpwYXJzZUZsb2F0KHQuc3Vic3RyKDIpKTpwYXJzZUZsb2F0KHQpLXBhcnNlRmxvYXQoZSl9LHJlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGw9PXQ/ZTpcInN0cmluZ1wiPT10eXBlb2YgdCYmXCI9XCI9PT10LmNoYXJBdCgxKT9wYXJzZUludCh0LmNoYXJBdCgwKStcIjFcIiwxMCkqTnVtYmVyKHQuc3Vic3RyKDIpKStlOnBhcnNlRmxvYXQodCl9LHNlPWZ1bmN0aW9uKHQsZSxpLHIpe3ZhciBzLG4sYSxvLGw9MWUtNjtyZXR1cm4gbnVsbD09dD9vPWU6XCJudW1iZXJcIj09dHlwZW9mIHQ/bz10OihzPTM2MCxuPXQuc3BsaXQoXCJfXCIpLGE9TnVtYmVyKG5bMF0ucmVwbGFjZSh2LFwiXCIpKSooLTE9PT10LmluZGV4T2YoXCJyYWRcIik/MTpMKS0oXCI9XCI9PT10LmNoYXJBdCgxKT8wOmUpLG4ubGVuZ3RoJiYociYmKHJbaV09ZSthKSwtMSE9PXQuaW5kZXhPZihcInNob3J0XCIpJiYoYSU9cyxhIT09YSUocy8yKSYmKGE9MD5hP2ErczphLXMpKSwtMSE9PXQuaW5kZXhPZihcIl9jd1wiKSYmMD5hP2E9KGErOTk5OTk5OTk5OSpzKSVzLSgwfGEvcykqczotMSE9PXQuaW5kZXhPZihcImNjd1wiKSYmYT4wJiYoYT0oYS05OTk5OTk5OTk5KnMpJXMtKDB8YS9zKSpzKSksbz1lK2EpLGw+byYmbz4tbCYmKG89MCksb30sbmU9e2FxdWE6WzAsMjU1LDI1NV0sbGltZTpbMCwyNTUsMF0sc2lsdmVyOlsxOTIsMTkyLDE5Ml0sYmxhY2s6WzAsMCwwXSxtYXJvb246WzEyOCwwLDBdLHRlYWw6WzAsMTI4LDEyOF0sYmx1ZTpbMCwwLDI1NV0sbmF2eTpbMCwwLDEyOF0sd2hpdGU6WzI1NSwyNTUsMjU1XSxmdWNoc2lhOlsyNTUsMCwyNTVdLG9saXZlOlsxMjgsMTI4LDBdLHllbGxvdzpbMjU1LDI1NSwwXSxvcmFuZ2U6WzI1NSwxNjUsMF0sZ3JheTpbMTI4LDEyOCwxMjhdLHB1cnBsZTpbMTI4LDAsMTI4XSxncmVlbjpbMCwxMjgsMF0scmVkOlsyNTUsMCwwXSxwaW5rOlsyNTUsMTkyLDIwM10sY3lhbjpbMCwyNTUsMjU1XSx0cmFuc3BhcmVudDpbMjU1LDI1NSwyNTUsMF19LGFlPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gdD0wPnQ/dCsxOnQ+MT90LTE6dCwwfDI1NSooMT42KnQ/ZSs2KihpLWUpKnQ6LjU+dD9pOjI+Myp0P2UrNiooaS1lKSooMi8zLXQpOmUpKy41fSxvZT1mdW5jdGlvbih0KXt2YXIgZSxpLHIscyxuLGE7cmV0dXJuIHQmJlwiXCIhPT10P1wibnVtYmVyXCI9PXR5cGVvZiB0P1t0Pj4xNiwyNTUmdD4+OCwyNTUmdF06KFwiLFwiPT09dC5jaGFyQXQodC5sZW5ndGgtMSkmJih0PXQuc3Vic3RyKDAsdC5sZW5ndGgtMSkpLG5lW3RdP25lW3RdOlwiI1wiPT09dC5jaGFyQXQoMCk/KDQ9PT10Lmxlbmd0aCYmKGU9dC5jaGFyQXQoMSksaT10LmNoYXJBdCgyKSxyPXQuY2hhckF0KDMpLHQ9XCIjXCIrZStlK2kraStyK3IpLHQ9cGFyc2VJbnQodC5zdWJzdHIoMSksMTYpLFt0Pj4xNiwyNTUmdD4+OCwyNTUmdF0pOlwiaHNsXCI9PT10LnN1YnN0cigwLDMpPyh0PXQubWF0Y2goZCkscz1OdW1iZXIodFswXSklMzYwLzM2MCxuPU51bWJlcih0WzFdKS8xMDAsYT1OdW1iZXIodFsyXSkvMTAwLGk9LjU+PWE/YSoobisxKTphK24tYSpuLGU9MiphLWksdC5sZW5ndGg+MyYmKHRbM109TnVtYmVyKHRbM10pKSx0WzBdPWFlKHMrMS8zLGUsaSksdFsxXT1hZShzLGUsaSksdFsyXT1hZShzLTEvMyxlLGkpLHQpOih0PXQubWF0Y2goZCl8fG5lLnRyYW5zcGFyZW50LHRbMF09TnVtYmVyKHRbMF0pLHRbMV09TnVtYmVyKHRbMV0pLHRbMl09TnVtYmVyKHRbMl0pLHQubGVuZ3RoPjMmJih0WzNdPU51bWJlcih0WzNdKSksdCkpOm5lLmJsYWNrfSxsZT1cIig/OlxcXFxiKD86KD86cmdifHJnYmF8aHNsfGhzbGEpXFxcXCguKz9cXFxcKSl8XFxcXEIjLis/XFxcXGJcIjtmb3IobCBpbiBuZSlsZSs9XCJ8XCIrbCtcIlxcXFxiXCI7bGU9UmVnRXhwKGxlK1wiKVwiLFwiZ2lcIik7dmFyIGhlPWZ1bmN0aW9uKHQsZSxpLHIpe2lmKG51bGw9PXQpcmV0dXJuIGZ1bmN0aW9uKHQpe3JldHVybiB0fTt2YXIgcyxuPWU/KHQubWF0Y2gobGUpfHxbXCJcIl0pWzBdOlwiXCIsYT10LnNwbGl0KG4pLmpvaW4oXCJcIikubWF0Y2goZyl8fFtdLG89dC5zdWJzdHIoMCx0LmluZGV4T2YoYVswXSkpLGw9XCIpXCI9PT10LmNoYXJBdCh0Lmxlbmd0aC0xKT9cIilcIjpcIlwiLGg9LTEhPT10LmluZGV4T2YoXCIgXCIpP1wiIFwiOlwiLFwiLHU9YS5sZW5ndGgsZj11PjA/YVswXS5yZXBsYWNlKGQsXCJcIik6XCJcIjtyZXR1cm4gdT9zPWU/ZnVuY3Rpb24odCl7dmFyIGUsXyxwLGM7aWYoXCJudW1iZXJcIj09dHlwZW9mIHQpdCs9ZjtlbHNlIGlmKHImJkQudGVzdCh0KSl7Zm9yKGM9dC5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxwPTA7Yy5sZW5ndGg+cDtwKyspY1twXT1zKGNbcF0pO3JldHVybiBjLmpvaW4oXCIsXCIpfWlmKGU9KHQubWF0Y2gobGUpfHxbbl0pWzBdLF89dC5zcGxpdChlKS5qb2luKFwiXCIpLm1hdGNoKGcpfHxbXSxwPV8ubGVuZ3RoLHU+cC0tKWZvcig7dT4rK3A7KV9bcF09aT9fWzB8KHAtMSkvMl06YVtwXTtyZXR1cm4gbytfLmpvaW4oaCkraCtlK2wrKC0xIT09dC5pbmRleE9mKFwiaW5zZXRcIik/XCIgaW5zZXRcIjpcIlwiKX06ZnVuY3Rpb24odCl7dmFyIGUsbixfO2lmKFwibnVtYmVyXCI9PXR5cGVvZiB0KXQrPWY7ZWxzZSBpZihyJiZELnRlc3QodCkpe2ZvcihuPXQucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIiksXz0wO24ubGVuZ3RoPl87XysrKW5bX109cyhuW19dKTtyZXR1cm4gbi5qb2luKFwiLFwiKX1pZihlPXQubWF0Y2goZyl8fFtdLF89ZS5sZW5ndGgsdT5fLS0pZm9yKDt1PisrXzspZVtfXT1pP2VbMHwoXy0xKS8yXTphW19dO3JldHVybiBvK2Uuam9pbihoKStsfTpmdW5jdGlvbih0KXtyZXR1cm4gdH19LHVlPWZ1bmN0aW9uKHQpe3JldHVybiB0PXQuc3BsaXQoXCIsXCIpLGZ1bmN0aW9uKGUsaSxyLHMsbixhLG8pe3ZhciBsLGg9KGkrXCJcIikuc3BsaXQoXCIgXCIpO2ZvcihvPXt9LGw9MDs0Pmw7bCsrKW9bdFtsXV09aFtsXT1oW2xdfHxoWyhsLTEpLzI+PjBdO3JldHVybiBzLnBhcnNlKGUsbyxuLGEpfX0sZmU9KEUuX3NldFBsdWdpblJhdGlvPWZ1bmN0aW9uKHQpe3RoaXMucGx1Z2luLnNldFJhdGlvKHQpO2Zvcih2YXIgZSxpLHIscyxuPXRoaXMuZGF0YSxhPW4ucHJveHksbz1uLmZpcnN0TVBULGw9MWUtNjtvOyllPWFbby52XSxvLnI/ZT1NYXRoLnJvdW5kKGUpOmw+ZSYmZT4tbCYmKGU9MCksby50W28ucF09ZSxvPW8uX25leHQ7aWYobi5hdXRvUm90YXRlJiYobi5hdXRvUm90YXRlLnJvdGF0aW9uPWEucm90YXRpb24pLDE9PT10KWZvcihvPW4uZmlyc3RNUFQ7bzspe2lmKGk9by50LGkudHlwZSl7aWYoMT09PWkudHlwZSl7Zm9yKHM9aS54czAraS5zK2kueHMxLHI9MTtpLmw+cjtyKyspcys9aVtcInhuXCIrcl0raVtcInhzXCIrKHIrMSldO2kuZT1zfX1lbHNlIGkuZT1pLnMraS54czA7bz1vLl9uZXh0fX0sZnVuY3Rpb24odCxlLGkscixzKXt0aGlzLnQ9dCx0aGlzLnA9ZSx0aGlzLnY9aSx0aGlzLnI9cyxyJiYoci5fcHJldj10aGlzLHRoaXMuX25leHQ9cil9KSxfZT0oRS5fcGFyc2VUb1Byb3h5PWZ1bmN0aW9uKHQsZSxpLHIscyxuKXt2YXIgYSxvLGwsaCx1LGY9cixfPXt9LHA9e30sYz1pLl90cmFuc2Zvcm0sZD1OO2ZvcihpLl90cmFuc2Zvcm09bnVsbCxOPWUscj11PWkucGFyc2UodCxlLHIscyksTj1kLG4mJihpLl90cmFuc2Zvcm09YyxmJiYoZi5fcHJldj1udWxsLGYuX3ByZXYmJihmLl9wcmV2Ll9uZXh0PW51bGwpKSk7ciYmciE9PWY7KXtpZigxPj1yLnR5cGUmJihvPXIucCxwW29dPXIucytyLmMsX1tvXT1yLnMsbnx8KGg9bmV3IGZlKHIsXCJzXCIsbyxoLHIuciksci5jPTApLDE9PT1yLnR5cGUpKWZvcihhPXIubDstLWE+MDspbD1cInhuXCIrYSxvPXIucCtcIl9cIitsLHBbb109ci5kYXRhW2xdLF9bb109cltsXSxufHwoaD1uZXcgZmUocixsLG8saCxyLnJ4cFtsXSkpO3I9ci5fbmV4dH1yZXR1cm57cHJveHk6XyxlbmQ6cCxmaXJzdE1QVDpoLHB0OnV9fSxFLkNTU1Byb3BUd2Vlbj1mdW5jdGlvbih0LGUscixzLGEsbyxsLGgsdSxmLF8pe3RoaXMudD10LHRoaXMucD1lLHRoaXMucz1yLHRoaXMuYz1zLHRoaXMubj1sfHxlLHQgaW5zdGFuY2VvZiBfZXx8bi5wdXNoKHRoaXMubiksdGhpcy5yPWgsdGhpcy50eXBlPW98fDAsdSYmKHRoaXMucHI9dSxpPSEwKSx0aGlzLmI9dm9pZCAwPT09Zj9yOmYsdGhpcy5lPXZvaWQgMD09PV8/citzOl8sYSYmKHRoaXMuX25leHQ9YSxhLl9wcmV2PXRoaXMpfSkscGU9YS5wYXJzZUNvbXBsZXg9ZnVuY3Rpb24odCxlLGkscixzLG4sYSxvLGwsdSl7aT1pfHxufHxcIlwiLGE9bmV3IF9lKHQsZSwwLDAsYSx1PzI6MSxudWxsLCExLG8saSxyKSxyKz1cIlwiO3ZhciBmLF8scCxjLGcsdix5LFQsdyx4LFAsUyxDPWkuc3BsaXQoXCIsIFwiKS5qb2luKFwiLFwiKS5zcGxpdChcIiBcIiksUj1yLnNwbGl0KFwiLCBcIikuam9pbihcIixcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgsQT1oIT09ITE7Zm9yKCgtMSE9PXIuaW5kZXhPZihcIixcIil8fC0xIT09aS5pbmRleE9mKFwiLFwiKSkmJihDPUMuam9pbihcIiBcIikucmVwbGFjZShELFwiLCBcIikuc3BsaXQoXCIgXCIpLFI9Ui5qb2luKFwiIFwiKS5yZXBsYWNlKEQsXCIsIFwiKS5zcGxpdChcIiBcIiksaz1DLmxlbmd0aCksayE9PVIubGVuZ3RoJiYoQz0obnx8XCJcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgpLGEucGx1Z2luPWwsYS5zZXRSYXRpbz11LGY9MDtrPmY7ZisrKWlmKGM9Q1tmXSxnPVJbZl0sVD1wYXJzZUZsb2F0KGMpLFR8fDA9PT1UKWEuYXBwZW5kWHRyYShcIlwiLFQsaWUoZyxUKSxnLnJlcGxhY2UobSxcIlwiKSxBJiYtMSE9PWcuaW5kZXhPZihcInB4XCIpLCEwKTtlbHNlIGlmKHMmJihcIiNcIj09PWMuY2hhckF0KDApfHxuZVtjXXx8Yi50ZXN0KGMpKSlTPVwiLFwiPT09Zy5jaGFyQXQoZy5sZW5ndGgtMSk/XCIpLFwiOlwiKVwiLGM9b2UoYyksZz1vZShnKSx3PWMubGVuZ3RoK2cubGVuZ3RoPjYsdyYmIVkmJjA9PT1nWzNdPyhhW1wieHNcIithLmxdKz1hLmw/XCIgdHJhbnNwYXJlbnRcIjpcInRyYW5zcGFyZW50XCIsYS5lPWEuZS5zcGxpdChSW2ZdKS5qb2luKFwidHJhbnNwYXJlbnRcIikpOihZfHwodz0hMSksYS5hcHBlbmRYdHJhKHc/XCJyZ2JhKFwiOlwicmdiKFwiLGNbMF0sZ1swXS1jWzBdLFwiLFwiLCEwLCEwKS5hcHBlbmRYdHJhKFwiXCIsY1sxXSxnWzFdLWNbMV0sXCIsXCIsITApLmFwcGVuZFh0cmEoXCJcIixjWzJdLGdbMl0tY1syXSx3P1wiLFwiOlMsITApLHcmJihjPTQ+Yy5sZW5ndGg/MTpjWzNdLGEuYXBwZW5kWHRyYShcIlwiLGMsKDQ+Zy5sZW5ndGg/MTpnWzNdKS1jLFMsITEpKSk7ZWxzZSBpZih2PWMubWF0Y2goZCkpe2lmKHk9Zy5tYXRjaChtKSwheXx8eS5sZW5ndGghPT12Lmxlbmd0aClyZXR1cm4gYTtmb3IocD0wLF89MDt2Lmxlbmd0aD5fO18rKylQPXZbX10seD1jLmluZGV4T2YoUCxwKSxhLmFwcGVuZFh0cmEoYy5zdWJzdHIocCx4LXApLE51bWJlcihQKSxpZSh5W19dLFApLFwiXCIsQSYmXCJweFwiPT09Yy5zdWJzdHIoeCtQLmxlbmd0aCwyKSwwPT09XykscD14K1AubGVuZ3RoO2FbXCJ4c1wiK2EubF0rPWMuc3Vic3RyKHApfWVsc2UgYVtcInhzXCIrYS5sXSs9YS5sP1wiIFwiK2M6YztpZigtMSE9PXIuaW5kZXhPZihcIj1cIikmJmEuZGF0YSl7Zm9yKFM9YS54czArYS5kYXRhLnMsZj0xO2EubD5mO2YrKylTKz1hW1wieHNcIitmXSthLmRhdGFbXCJ4blwiK2ZdO2EuZT1TK2FbXCJ4c1wiK2ZdfXJldHVybiBhLmx8fChhLnR5cGU9LTEsYS54czA9YS5lKSxhLnhmaXJzdHx8YX0sY2U9OTtmb3IobD1fZS5wcm90b3R5cGUsbC5sPWwucHI9MDstLWNlPjA7KWxbXCJ4blwiK2NlXT0wLGxbXCJ4c1wiK2NlXT1cIlwiO2wueHMwPVwiXCIsbC5fbmV4dD1sLl9wcmV2PWwueGZpcnN0PWwuZGF0YT1sLnBsdWdpbj1sLnNldFJhdGlvPWwucnhwPW51bGwsbC5hcHBlbmRYdHJhPWZ1bmN0aW9uKHQsZSxpLHIscyxuKXt2YXIgYT10aGlzLG89YS5sO3JldHVybiBhW1wieHNcIitvXSs9biYmbz9cIiBcIit0OnR8fFwiXCIsaXx8MD09PW98fGEucGx1Z2luPyhhLmwrKyxhLnR5cGU9YS5zZXRSYXRpbz8yOjEsYVtcInhzXCIrYS5sXT1yfHxcIlwiLG8+MD8oYS5kYXRhW1wieG5cIitvXT1lK2ksYS5yeHBbXCJ4blwiK29dPXMsYVtcInhuXCIrb109ZSxhLnBsdWdpbnx8KGEueGZpcnN0PW5ldyBfZShhLFwieG5cIitvLGUsaSxhLnhmaXJzdHx8YSwwLGEubixzLGEucHIpLGEueGZpcnN0LnhzMD0wKSxhKTooYS5kYXRhPXtzOmUraX0sYS5yeHA9e30sYS5zPWUsYS5jPWksYS5yPXMsYSkpOihhW1wieHNcIitvXSs9ZSsocnx8XCJcIiksYSl9O3ZhciBkZT1mdW5jdGlvbih0LGUpe2U9ZXx8e30sdGhpcy5wPWUucHJlZml4P1YodCl8fHQ6dCxvW3RdPW9bdGhpcy5wXT10aGlzLHRoaXMuZm9ybWF0PWUuZm9ybWF0dGVyfHxoZShlLmRlZmF1bHRWYWx1ZSxlLmNvbG9yLGUuY29sbGFwc2libGUsZS5tdWx0aSksZS5wYXJzZXImJih0aGlzLnBhcnNlPWUucGFyc2VyKSx0aGlzLmNscnM9ZS5jb2xvcix0aGlzLm11bHRpPWUubXVsdGksdGhpcy5rZXl3b3JkPWUua2V5d29yZCx0aGlzLmRmbHQ9ZS5kZWZhdWx0VmFsdWUsdGhpcy5wcj1lLnByaW9yaXR5fHwwfSxtZT1FLl9yZWdpc3RlckNvbXBsZXhTcGVjaWFsUHJvcD1mdW5jdGlvbih0LGUsaSl7XCJvYmplY3RcIiE9dHlwZW9mIGUmJihlPXtwYXJzZXI6aX0pO3ZhciByLHMsbj10LnNwbGl0KFwiLFwiKSxhPWUuZGVmYXVsdFZhbHVlO2ZvcihpPWl8fFthXSxyPTA7bi5sZW5ndGg+cjtyKyspZS5wcmVmaXg9MD09PXImJmUucHJlZml4LGUuZGVmYXVsdFZhbHVlPWlbcl18fGEscz1uZXcgZGUobltyXSxlKX0sZ2U9ZnVuY3Rpb24odCl7aWYoIW9bdF0pe3ZhciBlPXQuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkrdC5zdWJzdHIoMSkrXCJQbHVnaW5cIjttZSh0LHtwYXJzZXI6ZnVuY3Rpb24odCxpLHIscyxuLGEsbCl7dmFyIGg9KHdpbmRvdy5HcmVlblNvY2tHbG9iYWxzfHx3aW5kb3cpLmNvbS5ncmVlbnNvY2sucGx1Z2luc1tlXTtyZXR1cm4gaD8oaC5fY3NzUmVnaXN0ZXIoKSxvW3JdLnBhcnNlKHQsaSxyLHMsbixhLGwpKTooVShcIkVycm9yOiBcIitlK1wiIGpzIGZpbGUgbm90IGxvYWRlZC5cIiksbil9fSl9fTtsPWRlLnByb3RvdHlwZSxsLnBhcnNlQ29tcGxleD1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGEsbyxsLGgsdSxmLF89dGhpcy5rZXl3b3JkO2lmKHRoaXMubXVsdGkmJihELnRlc3QoaSl8fEQudGVzdChlKT8obz1lLnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLGw9aS5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSk6XyYmKG89W2VdLGw9W2ldKSksbCl7Zm9yKGg9bC5sZW5ndGg+by5sZW5ndGg/bC5sZW5ndGg6by5sZW5ndGgsYT0wO2g+YTthKyspZT1vW2FdPW9bYV18fHRoaXMuZGZsdCxpPWxbYV09bFthXXx8dGhpcy5kZmx0LF8mJih1PWUuaW5kZXhPZihfKSxmPWkuaW5kZXhPZihfKSx1IT09ZiYmKGk9LTE9PT1mP2w6byxpW2FdKz1cIiBcIitfKSk7ZT1vLmpvaW4oXCIsIFwiKSxpPWwuam9pbihcIiwgXCIpfXJldHVybiBwZSh0LHRoaXMucCxlLGksdGhpcy5jbHJzLHRoaXMuZGZsdCxyLHRoaXMucHIscyxuKX0sbC5wYXJzZT1mdW5jdGlvbih0LGUsaSxyLG4sYSl7cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsdGhpcy5mb3JtYXQocSh0LHRoaXMucCxzLCExLHRoaXMuZGZsdCkpLHRoaXMuZm9ybWF0KGUpLG4sYSl9LGEucmVnaXN0ZXJTcGVjaWFsUHJvcD1mdW5jdGlvbih0LGUsaSl7bWUodCx7cGFyc2VyOmZ1bmN0aW9uKHQscixzLG4sYSxvKXt2YXIgbD1uZXcgX2UodCxzLDAsMCxhLDIscywhMSxpKTtyZXR1cm4gbC5wbHVnaW49byxsLnNldFJhdGlvPWUodCxyLG4uX3R3ZWVuLHMpLGx9LHByaW9yaXR5Oml9KX07dmFyIHZlPVwic2NhbGVYLHNjYWxlWSxzY2FsZVoseCx5LHosc2tld1gsc2tld1kscm90YXRpb24scm90YXRpb25YLHJvdGF0aW9uWSxwZXJzcGVjdGl2ZVwiLnNwbGl0KFwiLFwiKSx5ZT1WKFwidHJhbnNmb3JtXCIpLFRlPVcrXCJ0cmFuc2Zvcm1cIix3ZT1WKFwidHJhbnNmb3JtT3JpZ2luXCIpLHhlPW51bGwhPT1WKFwicGVyc3BlY3RpdmVcIiksYmU9RS5UcmFuc2Zvcm09ZnVuY3Rpb24oKXt0aGlzLnNrZXdZPTB9LFBlPUUuZ2V0VHJhbnNmb3JtPWZ1bmN0aW9uKHQsZSxpLHIpe2lmKHQuX2dzVHJhbnNmb3JtJiZpJiYhcilyZXR1cm4gdC5fZ3NUcmFuc2Zvcm07dmFyIHMsbixvLGwsaCx1LGYsXyxwLGMsZCxtLGcsdj1pP3QuX2dzVHJhbnNmb3JtfHxuZXcgYmU6bmV3IGJlLHk9MD52LnNjYWxlWCxUPTJlLTUsdz0xZTUseD0xNzkuOTksYj14Kk0sUD14ZT9wYXJzZUZsb2F0KHEodCx3ZSxlLCExLFwiMCAwIDBcIikuc3BsaXQoXCIgXCIpWzJdKXx8di56T3JpZ2lufHwwOjA7Zm9yKHllP3M9cSh0LFRlLGUsITApOnQuY3VycmVudFN0eWxlJiYocz10LmN1cnJlbnRTdHlsZS5maWx0ZXIubWF0Y2goQSkscz1zJiY0PT09cy5sZW5ndGg/W3NbMF0uc3Vic3RyKDQpLE51bWJlcihzWzJdLnN1YnN0cig0KSksTnVtYmVyKHNbMV0uc3Vic3RyKDQpKSxzWzNdLnN1YnN0cig0KSx2Lnh8fDAsdi55fHwwXS5qb2luKFwiLFwiKTpcIlwiKSxuPShzfHxcIlwiKS5tYXRjaCgvKD86XFwtfFxcYilbXFxkXFwtXFwuZV0rXFxiL2dpKXx8W10sbz1uLmxlbmd0aDstLW8+LTE7KWw9TnVtYmVyKG5bb10pLG5bb109KGg9bC0obHw9MCkpPygwfGgqdysoMD5oPy0uNTouNSkpL3crbDpsO2lmKDE2PT09bi5sZW5ndGgpe3ZhciBTPW5bOF0sQz1uWzldLFI9blsxMF0saz1uWzEyXSxPPW5bMTNdLEQ9blsxNF07aWYodi56T3JpZ2luJiYoRD0tdi56T3JpZ2luLGs9UypELW5bMTJdLE89QypELW5bMTNdLEQ9UipEK3Yuek9yaWdpbi1uWzE0XSksIWl8fHJ8fG51bGw9PXYucm90YXRpb25YKXt2YXIgTixYLHosSSxFLEYsWSxCPW5bMF0sVT1uWzFdLFc9blsyXSxqPW5bM10sVj1uWzRdLEg9bls1XSxRPW5bNl0sWj1uWzddLCQ9blsxMV0sRz1NYXRoLmF0YW4yKFEsUiksSz0tYj5HfHxHPmI7di5yb3RhdGlvblg9RypMLEcmJihJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxOPVYqSStTKkUsWD1IKkkrQypFLHo9USpJK1IqRSxTPVYqLUUrUypJLEM9SCotRStDKkksUj1RKi1FK1IqSSwkPVoqLUUrJCpJLFY9TixIPVgsUT16KSxHPU1hdGguYXRhbjIoUyxCKSx2LnJvdGF0aW9uWT1HKkwsRyYmKEY9LWI+R3x8Rz5iLEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLE49QipJLVMqRSxYPVUqSS1DKkUsej1XKkktUipFLEM9VSpFK0MqSSxSPVcqRStSKkksJD1qKkUrJCpJLEI9TixVPVgsVz16KSxHPU1hdGguYXRhbjIoVSxIKSx2LnJvdGF0aW9uPUcqTCxHJiYoWT0tYj5HfHxHPmIsST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksQj1CKkkrVipFLFg9VSpJK0gqRSxIPVUqLUUrSCpJLFE9VyotRStRKkksVT1YKSxZJiZLP3Yucm90YXRpb249di5yb3RhdGlvblg9MDpZJiZGP3Yucm90YXRpb249di5yb3RhdGlvblk9MDpGJiZLJiYodi5yb3RhdGlvblk9di5yb3RhdGlvblg9MCksdi5zY2FsZVg9KDB8TWF0aC5zcXJ0KEIqQitVKlUpKncrLjUpL3csdi5zY2FsZVk9KDB8TWF0aC5zcXJ0KEgqSCtDKkMpKncrLjUpL3csdi5zY2FsZVo9KDB8TWF0aC5zcXJ0KFEqUStSKlIpKncrLjUpL3csdi5za2V3WD0wLHYucGVyc3BlY3RpdmU9JD8xLygwPiQ/LSQ6JCk6MCx2Lng9ayx2Lnk9Tyx2Lno9RH19ZWxzZSBpZighKHhlJiYhciYmbi5sZW5ndGgmJnYueD09PW5bNF0mJnYueT09PW5bNV0mJih2LnJvdGF0aW9uWHx8di5yb3RhdGlvblkpfHx2b2lkIDAhPT12LngmJlwibm9uZVwiPT09cSh0LFwiZGlzcGxheVwiLGUpKSl7dmFyIEo9bi5sZW5ndGg+PTYsdGU9Sj9uWzBdOjEsZWU9blsxXXx8MCxpZT1uWzJdfHwwLHJlPUo/blszXToxO3YueD1uWzRdfHwwLHYueT1uWzVdfHwwLHU9TWF0aC5zcXJ0KHRlKnRlK2VlKmVlKSxmPU1hdGguc3FydChyZSpyZStpZSppZSksXz10ZXx8ZWU/TWF0aC5hdGFuMihlZSx0ZSkqTDp2LnJvdGF0aW9ufHwwLHA9aWV8fHJlP01hdGguYXRhbjIoaWUscmUpKkwrXzp2LnNrZXdYfHwwLGM9dS1NYXRoLmFicyh2LnNjYWxlWHx8MCksZD1mLU1hdGguYWJzKHYuc2NhbGVZfHwwKSxNYXRoLmFicyhwKT45MCYmMjcwPk1hdGguYWJzKHApJiYoeT8odSo9LTEscCs9MD49Xz8xODA6LTE4MCxfKz0wPj1fPzE4MDotMTgwKTooZio9LTEscCs9MD49cD8xODA6LTE4MCkpLG09KF8tdi5yb3RhdGlvbiklMTgwLGc9KHAtdi5za2V3WCklMTgwLCh2b2lkIDA9PT12LnNrZXdYfHxjPlR8fC1UPmN8fGQ+VHx8LVQ+ZHx8bT4teCYmeD5tJiZmYWxzZXxtKnd8fGc+LXgmJng+ZyYmZmFsc2V8Zyp3KSYmKHYuc2NhbGVYPXUsdi5zY2FsZVk9Zix2LnJvdGF0aW9uPV8sdi5za2V3WD1wKSx4ZSYmKHYucm90YXRpb25YPXYucm90YXRpb25ZPXYuej0wLHYucGVyc3BlY3RpdmU9cGFyc2VGbG9hdChhLmRlZmF1bHRUcmFuc2Zvcm1QZXJzcGVjdGl2ZSl8fDAsdi5zY2FsZVo9MSl9di56T3JpZ2luPVA7Zm9yKG8gaW4gdilUPnZbb10mJnZbb10+LVQmJih2W29dPTApO3JldHVybiBpJiYodC5fZ3NUcmFuc2Zvcm09diksdn0sU2U9ZnVuY3Rpb24odCl7dmFyIGUsaSxyPXRoaXMuZGF0YSxzPS1yLnJvdGF0aW9uKk0sbj1zK3Iuc2tld1gqTSxhPTFlNSxvPSgwfE1hdGguY29zKHMpKnIuc2NhbGVYKmEpL2EsbD0oMHxNYXRoLnNpbihzKSpyLnNjYWxlWCphKS9hLGg9KDB8TWF0aC5zaW4obikqLXIuc2NhbGVZKmEpL2EsdT0oMHxNYXRoLmNvcyhuKSpyLnNjYWxlWSphKS9hLGY9dGhpcy50LnN0eWxlLF89dGhpcy50LmN1cnJlbnRTdHlsZTtpZihfKXtpPWwsbD0taCxoPS1pLGU9Xy5maWx0ZXIsZi5maWx0ZXI9XCJcIjt2YXIgcCxkLG09dGhpcy50Lm9mZnNldFdpZHRoLGc9dGhpcy50Lm9mZnNldEhlaWdodCx2PVwiYWJzb2x1dGVcIiE9PV8ucG9zaXRpb24sdz1cInByb2dpZDpEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5NYXRyaXgoTTExPVwiK28rXCIsIE0xMj1cIitsK1wiLCBNMjE9XCIraCtcIiwgTTIyPVwiK3UseD1yLngsYj1yLnk7aWYobnVsbCE9ci5veCYmKHA9KHIub3hwPy4wMSptKnIub3g6ci5veCktbS8yLGQ9KHIub3lwPy4wMSpnKnIub3k6ci5veSktZy8yLHgrPXAtKHAqbytkKmwpLGIrPWQtKHAqaCtkKnUpKSx2PyhwPW0vMixkPWcvMix3Kz1cIiwgRHg9XCIrKHAtKHAqbytkKmwpK3gpK1wiLCBEeT1cIisoZC0ocCpoK2QqdSkrYikrXCIpXCIpOncrPVwiLCBzaXppbmdNZXRob2Q9J2F1dG8gZXhwYW5kJylcIixmLmZpbHRlcj0tMSE9PWUuaW5kZXhPZihcIkRYSW1hZ2VUcmFuc2Zvcm0uTWljcm9zb2Z0Lk1hdHJpeChcIik/ZS5yZXBsYWNlKE8sdyk6dytcIiBcIitlLCgwPT09dHx8MT09PXQpJiYxPT09byYmMD09PWwmJjA9PT1oJiYxPT09dSYmKHYmJi0xPT09dy5pbmRleE9mKFwiRHg9MCwgRHk9MFwiKXx8VC50ZXN0KGUpJiYxMDAhPT1wYXJzZUZsb2F0KFJlZ0V4cC4kMSl8fC0xPT09ZS5pbmRleE9mKFwiZ3JhZGllbnQoXCImJmUuaW5kZXhPZihcIkFscGhhXCIpKSYmZi5yZW1vdmVBdHRyaWJ1dGUoXCJmaWx0ZXJcIikpLCF2KXt2YXIgUCxTLEMsUj04PmM/MTotMTtmb3IocD1yLmllT2Zmc2V0WHx8MCxkPXIuaWVPZmZzZXRZfHwwLHIuaWVPZmZzZXRYPU1hdGgucm91bmQoKG0tKCgwPm8/LW86bykqbSsoMD5sPy1sOmwpKmcpKS8yK3gpLHIuaWVPZmZzZXRZPU1hdGgucm91bmQoKGctKCgwPnU/LXU6dSkqZysoMD5oPy1oOmgpKm0pKS8yK2IpLGNlPTA7ND5jZTtjZSsrKVM9SltjZV0sUD1fW1NdLGk9LTEhPT1QLmluZGV4T2YoXCJweFwiKT9wYXJzZUZsb2F0KFApOlEodGhpcy50LFMscGFyc2VGbG9hdChQKSxQLnJlcGxhY2UoeSxcIlwiKSl8fDAsQz1pIT09cltTXT8yPmNlPy1yLmllT2Zmc2V0WDotci5pZU9mZnNldFk6Mj5jZT9wLXIuaWVPZmZzZXRYOmQtci5pZU9mZnNldFksZltTXT0ocltTXT1NYXRoLnJvdW5kKGktQyooMD09PWNlfHwyPT09Y2U/MTpSKSkpK1wicHhcIn19fSxDZT1FLnNldDNEVHJhbnNmb3JtUmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhLG8sbCxoLHUsZixwLGMsZCxtLGcsdix5LFQsdyx4LGIsUCxTPXRoaXMuZGF0YSxDPXRoaXMudC5zdHlsZSxSPVMucm90YXRpb24qTSxrPVMuc2NhbGVYLEE9Uy5zY2FsZVksTz1TLnNjYWxlWixEPVMucGVyc3BlY3RpdmU7aWYoISgxIT09dCYmMCE9PXR8fFwiYXV0b1wiIT09Uy5mb3JjZTNEfHxTLnJvdGF0aW9uWXx8Uy5yb3RhdGlvblh8fDEhPT1PfHxEfHxTLnopKXJldHVybiBSZS5jYWxsKHRoaXMsdCksdm9pZCAwO2lmKF8pe3ZhciBMPTFlLTQ7TD5rJiZrPi1MJiYoaz1PPTJlLTUpLEw+QSYmQT4tTCYmKEE9Tz0yZS01KSwhRHx8Uy56fHxTLnJvdGF0aW9uWHx8Uy5yb3RhdGlvbll8fChEPTApfWlmKFJ8fFMuc2tld1gpeT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLGU9eSxuPVQsUy5za2V3WCYmKFItPVMuc2tld1gqTSx5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksXCJzaW1wbGVcIj09PVMuc2tld1R5cGUmJih3PU1hdGgudGFuKFMuc2tld1gqTSksdz1NYXRoLnNxcnQoMSt3KncpLHkqPXcsVCo9dykpLGk9LVQsYT15O2Vsc2V7aWYoIShTLnJvdGF0aW9uWXx8Uy5yb3RhdGlvblh8fDEhPT1PfHxEKSlyZXR1cm4gQ1t5ZV09XCJ0cmFuc2xhdGUzZChcIitTLngrXCJweCxcIitTLnkrXCJweCxcIitTLnorXCJweClcIisoMSE9PWt8fDEhPT1BP1wiIHNjYWxlKFwiK2srXCIsXCIrQStcIilcIjpcIlwiKSx2b2lkIDA7ZT1hPTEsaT1uPTB9Zj0xLHI9cz1vPWw9aD11PXA9Yz1kPTAsbT1EPy0xL0Q6MCxnPVMuek9yaWdpbix2PTFlNSxSPVMucm90YXRpb25ZKk0sUiYmKHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxoPWYqLVQsYz1tKi1ULHI9ZSpULG89bipULGYqPXksbSo9eSxlKj15LG4qPXkpLFI9Uy5yb3RhdGlvblgqTSxSJiYoeT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLHc9aSp5K3IqVCx4PWEqeStvKlQsYj11KnkrZipULFA9ZCp5K20qVCxyPWkqLVQrcip5LG89YSotVCtvKnksZj11Ki1UK2YqeSxtPWQqLVQrbSp5LGk9dyxhPXgsdT1iLGQ9UCksMSE9PU8mJihyKj1PLG8qPU8sZio9TyxtKj1PKSwxIT09QSYmKGkqPUEsYSo9QSx1Kj1BLGQqPUEpLDEhPT1rJiYoZSo9ayxuKj1rLGgqPWssYyo9ayksZyYmKHAtPWcscz1yKnAsbD1vKnAscD1mKnArZykscz0odz0ocys9Uy54KS0oc3w9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrczpzLGw9KHc9KGwrPVMueSktKGx8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K2w6bCxwPSh3PShwKz1TLnopLShwfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditwOnAsQ1t5ZV09XCJtYXRyaXgzZChcIitbKDB8ZSp2KS92LCgwfG4qdikvdiwoMHxoKnYpL3YsKDB8Yyp2KS92LCgwfGkqdikvdiwoMHxhKnYpL3YsKDB8dSp2KS92LCgwfGQqdikvdiwoMHxyKnYpL3YsKDB8byp2KS92LCgwfGYqdikvdiwoMHxtKnYpL3YscyxsLHAsRD8xKy1wL0Q6MV0uam9pbihcIixcIikrXCIpXCJ9LFJlPUUuc2V0MkRUcmFuc2Zvcm1SYXRpbz1mdW5jdGlvbih0KXt2YXIgZSxpLHIscyxuLGE9dGhpcy5kYXRhLG89dGhpcy50LGw9by5zdHlsZTtyZXR1cm4gYS5yb3RhdGlvblh8fGEucm90YXRpb25ZfHxhLnp8fGEuZm9yY2UzRD09PSEwfHxcImF1dG9cIj09PWEuZm9yY2UzRCYmMSE9PXQmJjAhPT10Pyh0aGlzLnNldFJhdGlvPUNlLENlLmNhbGwodGhpcyx0KSx2b2lkIDApOihhLnJvdGF0aW9ufHxhLnNrZXdYPyhlPWEucm90YXRpb24qTSxpPWUtYS5za2V3WCpNLHI9MWU1LHM9YS5zY2FsZVgqcixuPWEuc2NhbGVZKnIsbFt5ZV09XCJtYXRyaXgoXCIrKDB8TWF0aC5jb3MoZSkqcykvcitcIixcIisoMHxNYXRoLnNpbihlKSpzKS9yK1wiLFwiKygwfE1hdGguc2luKGkpKi1uKS9yK1wiLFwiKygwfE1hdGguY29zKGkpKm4pL3IrXCIsXCIrYS54K1wiLFwiK2EueStcIilcIik6bFt5ZV09XCJtYXRyaXgoXCIrYS5zY2FsZVgrXCIsMCwwLFwiK2Euc2NhbGVZK1wiLFwiK2EueCtcIixcIithLnkrXCIpXCIsdm9pZCAwKX07bWUoXCJ0cmFuc2Zvcm0sc2NhbGUsc2NhbGVYLHNjYWxlWSxzY2FsZVoseCx5LHoscm90YXRpb24scm90YXRpb25YLHJvdGF0aW9uWSxyb3RhdGlvblosc2tld1gsc2tld1ksc2hvcnRSb3RhdGlvbixzaG9ydFJvdGF0aW9uWCxzaG9ydFJvdGF0aW9uWSxzaG9ydFJvdGF0aW9uWix0cmFuc2Zvcm1PcmlnaW4sdHJhbnNmb3JtUGVyc3BlY3RpdmUsZGlyZWN0aW9uYWxSb3RhdGlvbixwYXJzZVRyYW5zZm9ybSxmb3JjZTNELHNrZXdUeXBlXCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sbyxsKXtpZihyLl90cmFuc2Zvcm0pcmV0dXJuIG47dmFyIGgsdSxmLF8scCxjLGQsbT1yLl90cmFuc2Zvcm09UGUodCxzLCEwLGwucGFyc2VUcmFuc2Zvcm0pLGc9dC5zdHlsZSx2PTFlLTYseT12ZS5sZW5ndGgsVD1sLHc9e307aWYoXCJzdHJpbmdcIj09dHlwZW9mIFQudHJhbnNmb3JtJiZ5ZSlmPXouc3R5bGUsZlt5ZV09VC50cmFuc2Zvcm0sZi5kaXNwbGF5PVwiYmxvY2tcIixmLnBvc2l0aW9uPVwiYWJzb2x1dGVcIixYLmJvZHkuYXBwZW5kQ2hpbGQoeiksaD1QZSh6LG51bGwsITEpLFguYm9keS5yZW1vdmVDaGlsZCh6KTtlbHNlIGlmKFwib2JqZWN0XCI9PXR5cGVvZiBUKXtpZihoPXtzY2FsZVg6cmUobnVsbCE9VC5zY2FsZVg/VC5zY2FsZVg6VC5zY2FsZSxtLnNjYWxlWCksc2NhbGVZOnJlKG51bGwhPVQuc2NhbGVZP1Quc2NhbGVZOlQuc2NhbGUsbS5zY2FsZVkpLHNjYWxlWjpyZShULnNjYWxlWixtLnNjYWxlWikseDpyZShULngsbS54KSx5OnJlKFQueSxtLnkpLHo6cmUoVC56LG0ueikscGVyc3BlY3RpdmU6cmUoVC50cmFuc2Zvcm1QZXJzcGVjdGl2ZSxtLnBlcnNwZWN0aXZlKX0sZD1ULmRpcmVjdGlvbmFsUm90YXRpb24sbnVsbCE9ZClpZihcIm9iamVjdFwiPT10eXBlb2YgZClmb3IoZiBpbiBkKVRbZl09ZFtmXTtlbHNlIFQucm90YXRpb249ZDtoLnJvdGF0aW9uPXNlKFwicm90YXRpb25cImluIFQ/VC5yb3RhdGlvbjpcInNob3J0Um90YXRpb25cImluIFQ/VC5zaG9ydFJvdGF0aW9uK1wiX3Nob3J0XCI6XCJyb3RhdGlvblpcImluIFQ/VC5yb3RhdGlvblo6bS5yb3RhdGlvbixtLnJvdGF0aW9uLFwicm90YXRpb25cIix3KSx4ZSYmKGgucm90YXRpb25YPXNlKFwicm90YXRpb25YXCJpbiBUP1Qucm90YXRpb25YOlwic2hvcnRSb3RhdGlvblhcImluIFQ/VC5zaG9ydFJvdGF0aW9uWCtcIl9zaG9ydFwiOm0ucm90YXRpb25YfHwwLG0ucm90YXRpb25YLFwicm90YXRpb25YXCIsdyksaC5yb3RhdGlvblk9c2UoXCJyb3RhdGlvbllcImluIFQ/VC5yb3RhdGlvblk6XCJzaG9ydFJvdGF0aW9uWVwiaW4gVD9ULnNob3J0Um90YXRpb25ZK1wiX3Nob3J0XCI6bS5yb3RhdGlvbll8fDAsbS5yb3RhdGlvblksXCJyb3RhdGlvbllcIix3KSksaC5za2V3WD1udWxsPT1ULnNrZXdYP20uc2tld1g6c2UoVC5za2V3WCxtLnNrZXdYKSxoLnNrZXdZPW51bGw9PVQuc2tld1k/bS5za2V3WTpzZShULnNrZXdZLG0uc2tld1kpLCh1PWguc2tld1ktbS5za2V3WSkmJihoLnNrZXdYKz11LGgucm90YXRpb24rPXUpfWZvcih4ZSYmbnVsbCE9VC5mb3JjZTNEJiYobS5mb3JjZTNEPVQuZm9yY2UzRCxjPSEwKSxtLnNrZXdUeXBlPVQuc2tld1R5cGV8fG0uc2tld1R5cGV8fGEuZGVmYXVsdFNrZXdUeXBlLHA9bS5mb3JjZTNEfHxtLnp8fG0ucm90YXRpb25YfHxtLnJvdGF0aW9uWXx8aC56fHxoLnJvdGF0aW9uWHx8aC5yb3RhdGlvbll8fGgucGVyc3BlY3RpdmUscHx8bnVsbD09VC5zY2FsZXx8KGguc2NhbGVaPTEpOy0teT4tMTspaT12ZVt5XSxfPWhbaV0tbVtpXSwoXz52fHwtdj5ffHxudWxsIT1OW2ldKSYmKGM9ITAsbj1uZXcgX2UobSxpLG1baV0sXyxuKSxpIGluIHcmJihuLmU9d1tpXSksbi54czA9MCxuLnBsdWdpbj1vLHIuX292ZXJ3cml0ZVByb3BzLnB1c2gobi5uKSk7cmV0dXJuIF89VC50cmFuc2Zvcm1PcmlnaW4sKF98fHhlJiZwJiZtLnpPcmlnaW4pJiYoeWU/KGM9ITAsaT13ZSxfPShffHxxKHQsaSxzLCExLFwiNTAlIDUwJVwiKSkrXCJcIixuPW5ldyBfZShnLGksMCwwLG4sLTEsXCJ0cmFuc2Zvcm1PcmlnaW5cIiksbi5iPWdbaV0sbi5wbHVnaW49byx4ZT8oZj1tLnpPcmlnaW4sXz1fLnNwbGl0KFwiIFwiKSxtLnpPcmlnaW49KF8ubGVuZ3RoPjImJigwPT09Znx8XCIwcHhcIiE9PV9bMl0pP3BhcnNlRmxvYXQoX1syXSk6Zil8fDAsbi54czA9bi5lPV9bMF0rXCIgXCIrKF9bMV18fFwiNTAlXCIpK1wiIDBweFwiLG49bmV3IF9lKG0sXCJ6T3JpZ2luXCIsMCwwLG4sLTEsbi5uKSxuLmI9ZixuLnhzMD1uLmU9bS56T3JpZ2luKTpuLnhzMD1uLmU9Xyk6ZWUoXytcIlwiLG0pKSxjJiYoci5fdHJhbnNmb3JtVHlwZT1wfHwzPT09dGhpcy5fdHJhbnNmb3JtVHlwZT8zOjIpLG59LHByZWZpeDohMH0pLG1lKFwiYm94U2hhZG93XCIse2RlZmF1bHRWYWx1ZTpcIjBweCAwcHggMHB4IDBweCAjOTk5XCIscHJlZml4OiEwLGNvbG9yOiEwLG11bHRpOiEwLGtleXdvcmQ6XCJpbnNldFwifSksbWUoXCJib3JkZXJSYWRpdXNcIix7ZGVmYXVsdFZhbHVlOlwiMHB4XCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLG4sYSl7ZT10aGlzLmZvcm1hdChlKTt2YXIgbyxsLGgsdSxmLF8scCxjLGQsbSxnLHYseSxULHcseCxiPVtcImJvcmRlclRvcExlZnRSYWRpdXNcIixcImJvcmRlclRvcFJpZ2h0UmFkaXVzXCIsXCJib3JkZXJCb3R0b21SaWdodFJhZGl1c1wiLFwiYm9yZGVyQm90dG9tTGVmdFJhZGl1c1wiXSxQPXQuc3R5bGU7Zm9yKGQ9cGFyc2VGbG9hdCh0Lm9mZnNldFdpZHRoKSxtPXBhcnNlRmxvYXQodC5vZmZzZXRIZWlnaHQpLG89ZS5zcGxpdChcIiBcIiksbD0wO2IubGVuZ3RoPmw7bCsrKXRoaXMucC5pbmRleE9mKFwiYm9yZGVyXCIpJiYoYltsXT1WKGJbbF0pKSxmPXU9cSh0LGJbbF0scywhMSxcIjBweFwiKSwtMSE9PWYuaW5kZXhPZihcIiBcIikmJih1PWYuc3BsaXQoXCIgXCIpLGY9dVswXSx1PXVbMV0pLF89aD1vW2xdLHA9cGFyc2VGbG9hdChmKSx2PWYuc3Vic3RyKChwK1wiXCIpLmxlbmd0aCkseT1cIj1cIj09PV8uY2hhckF0KDEpLHk/KGM9cGFyc2VJbnQoXy5jaGFyQXQoMCkrXCIxXCIsMTApLF89Xy5zdWJzdHIoMiksYyo9cGFyc2VGbG9hdChfKSxnPV8uc3Vic3RyKChjK1wiXCIpLmxlbmd0aC0oMD5jPzE6MCkpfHxcIlwiKTooYz1wYXJzZUZsb2F0KF8pLGc9Xy5zdWJzdHIoKGMrXCJcIikubGVuZ3RoKSksXCJcIj09PWcmJihnPXJbaV18fHYpLGchPT12JiYoVD1RKHQsXCJib3JkZXJMZWZ0XCIscCx2KSx3PVEodCxcImJvcmRlclRvcFwiLHAsdiksXCIlXCI9PT1nPyhmPTEwMCooVC9kKStcIiVcIix1PTEwMCoody9tKStcIiVcIik6XCJlbVwiPT09Zz8oeD1RKHQsXCJib3JkZXJMZWZ0XCIsMSxcImVtXCIpLGY9VC94K1wiZW1cIix1PXcveCtcImVtXCIpOihmPVQrXCJweFwiLHU9dytcInB4XCIpLHkmJihfPXBhcnNlRmxvYXQoZikrYytnLGg9cGFyc2VGbG9hdCh1KStjK2cpKSxhPXBlKFAsYltsXSxmK1wiIFwiK3UsXytcIiBcIitoLCExLFwiMHB4XCIsYSk7cmV0dXJuIGF9LHByZWZpeDohMCxmb3JtYXR0ZXI6aGUoXCIwcHggMHB4IDBweCAwcHhcIiwhMSwhMCl9KSxtZShcImJhY2tncm91bmRQb3NpdGlvblwiLHtkZWZhdWx0VmFsdWU6XCIwIDBcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvLGwsaCx1LGYsXyxwPVwiYmFja2dyb3VuZC1wb3NpdGlvblwiLGQ9c3x8SCh0LG51bGwpLG09dGhpcy5mb3JtYXQoKGQ/Yz9kLmdldFByb3BlcnR5VmFsdWUocCtcIi14XCIpK1wiIFwiK2QuZ2V0UHJvcGVydHlWYWx1ZShwK1wiLXlcIik6ZC5nZXRQcm9wZXJ0eVZhbHVlKHApOnQuY3VycmVudFN0eWxlLmJhY2tncm91bmRQb3NpdGlvblgrXCIgXCIrdC5jdXJyZW50U3R5bGUuYmFja2dyb3VuZFBvc2l0aW9uWSl8fFwiMCAwXCIpLGc9dGhpcy5mb3JtYXQoZSk7aWYoLTEhPT1tLmluZGV4T2YoXCIlXCIpIT0oLTEhPT1nLmluZGV4T2YoXCIlXCIpKSYmKF89cSh0LFwiYmFja2dyb3VuZEltYWdlXCIpLnJlcGxhY2UoQyxcIlwiKSxfJiZcIm5vbmVcIiE9PV8pKXtmb3Iobz1tLnNwbGl0KFwiIFwiKSxsPWcuc3BsaXQoXCIgXCIpLEkuc2V0QXR0cmlidXRlKFwic3JjXCIsXyksaD0yOy0taD4tMTspbT1vW2hdLHU9LTEhPT1tLmluZGV4T2YoXCIlXCIpLHUhPT0oLTEhPT1sW2hdLmluZGV4T2YoXCIlXCIpKSYmKGY9MD09PWg/dC5vZmZzZXRXaWR0aC1JLndpZHRoOnQub2Zmc2V0SGVpZ2h0LUkuaGVpZ2h0LG9baF09dT9wYXJzZUZsb2F0KG0pLzEwMCpmK1wicHhcIjoxMDAqKHBhcnNlRmxvYXQobSkvZikrXCIlXCIpO209by5qb2luKFwiIFwiKX1yZXR1cm4gdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSxtLGcsbixhKX0sZm9ybWF0dGVyOmVlfSksbWUoXCJiYWNrZ3JvdW5kU2l6ZVwiLHtkZWZhdWx0VmFsdWU6XCIwIDBcIixmb3JtYXR0ZXI6ZWV9KSxtZShcInBlcnNwZWN0aXZlXCIse2RlZmF1bHRWYWx1ZTpcIjBweFwiLHByZWZpeDohMH0pLG1lKFwicGVyc3BlY3RpdmVPcmlnaW5cIix7ZGVmYXVsdFZhbHVlOlwiNTAlIDUwJVwiLHByZWZpeDohMH0pLG1lKFwidHJhbnNmb3JtU3R5bGVcIix7cHJlZml4OiEwfSksbWUoXCJiYWNrZmFjZVZpc2liaWxpdHlcIix7cHJlZml4OiEwfSksbWUoXCJ1c2VyU2VsZWN0XCIse3ByZWZpeDohMH0pLG1lKFwibWFyZ2luXCIse3BhcnNlcjp1ZShcIm1hcmdpblRvcCxtYXJnaW5SaWdodCxtYXJnaW5Cb3R0b20sbWFyZ2luTGVmdFwiKX0pLG1lKFwicGFkZGluZ1wiLHtwYXJzZXI6dWUoXCJwYWRkaW5nVG9wLHBhZGRpbmdSaWdodCxwYWRkaW5nQm90dG9tLHBhZGRpbmdMZWZ0XCIpfSksbWUoXCJjbGlwXCIse2RlZmF1bHRWYWx1ZTpcInJlY3QoMHB4LDBweCwwcHgsMHB4KVwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7dmFyIG8sbCxoO3JldHVybiA5PmM/KGw9dC5jdXJyZW50U3R5bGUsaD04PmM/XCIgXCI6XCIsXCIsbz1cInJlY3QoXCIrbC5jbGlwVG9wK2grbC5jbGlwUmlnaHQraCtsLmNsaXBCb3R0b20raCtsLmNsaXBMZWZ0K1wiKVwiLGU9dGhpcy5mb3JtYXQoZSkuc3BsaXQoXCIsXCIpLmpvaW4oaCkpOihvPXRoaXMuZm9ybWF0KHEodCx0aGlzLnAscywhMSx0aGlzLmRmbHQpKSxlPXRoaXMuZm9ybWF0KGUpKSx0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLG8sZSxuLGEpfX0pLG1lKFwidGV4dFNoYWRvd1wiLHtkZWZhdWx0VmFsdWU6XCIwcHggMHB4IDBweCAjOTk5XCIsY29sb3I6ITAsbXVsdGk6ITB9KSxtZShcImF1dG9Sb3VuZCxzdHJpY3RVbml0c1wiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixzKXtyZXR1cm4gc319KSxtZShcImJvcmRlclwiLHtkZWZhdWx0VmFsdWU6XCIwcHggc29saWQgIzAwMFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsdGhpcy5mb3JtYXQocSh0LFwiYm9yZGVyVG9wV2lkdGhcIixzLCExLFwiMHB4XCIpK1wiIFwiK3EodCxcImJvcmRlclRvcFN0eWxlXCIscywhMSxcInNvbGlkXCIpK1wiIFwiK3EodCxcImJvcmRlclRvcENvbG9yXCIscywhMSxcIiMwMDBcIikpLHRoaXMuZm9ybWF0KGUpLG4sYSl9LGNvbG9yOiEwLGZvcm1hdHRlcjpmdW5jdGlvbih0KXt2YXIgZT10LnNwbGl0KFwiIFwiKTtyZXR1cm4gZVswXStcIiBcIisoZVsxXXx8XCJzb2xpZFwiKStcIiBcIisodC5tYXRjaChsZSl8fFtcIiMwMDBcIl0pWzBdfX0pLG1lKFwiYm9yZGVyV2lkdGhcIix7cGFyc2VyOnVlKFwiYm9yZGVyVG9wV2lkdGgsYm9yZGVyUmlnaHRXaWR0aCxib3JkZXJCb3R0b21XaWR0aCxib3JkZXJMZWZ0V2lkdGhcIil9KSxtZShcImZsb2F0LGNzc0Zsb2F0LHN0eWxlRmxvYXRcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG49dC5zdHlsZSxhPVwiY3NzRmxvYXRcImluIG4/XCJjc3NGbG9hdFwiOlwic3R5bGVGbG9hdFwiO3JldHVybiBuZXcgX2UobixhLDAsMCxzLC0xLGksITEsMCxuW2FdLGUpfX0pO3ZhciBrZT1mdW5jdGlvbih0KXt2YXIgZSxpPXRoaXMudCxyPWkuZmlsdGVyfHxxKHRoaXMuZGF0YSxcImZpbHRlclwiKSxzPTB8dGhpcy5zK3RoaXMuYyp0OzEwMD09PXMmJigtMT09PXIuaW5kZXhPZihcImF0cml4KFwiKSYmLTE9PT1yLmluZGV4T2YoXCJyYWRpZW50KFwiKSYmLTE9PT1yLmluZGV4T2YoXCJvYWRlcihcIik/KGkucmVtb3ZlQXR0cmlidXRlKFwiZmlsdGVyXCIpLGU9IXEodGhpcy5kYXRhLFwiZmlsdGVyXCIpKTooaS5maWx0ZXI9ci5yZXBsYWNlKHgsXCJcIiksZT0hMCkpLGV8fCh0aGlzLnhuMSYmKGkuZmlsdGVyPXI9cnx8XCJhbHBoYShvcGFjaXR5PVwiK3MrXCIpXCIpLC0xPT09ci5pbmRleE9mKFwicGFjaXR5XCIpPzA9PT1zJiZ0aGlzLnhuMXx8KGkuZmlsdGVyPXIrXCIgYWxwaGEob3BhY2l0eT1cIitzK1wiKVwiKTppLmZpbHRlcj1yLnJlcGxhY2UoVCxcIm9wYWNpdHk9XCIrcykpfTttZShcIm9wYWNpdHksYWxwaGEsYXV0b0FscGhhXCIse2RlZmF1bHRWYWx1ZTpcIjFcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvPXBhcnNlRmxvYXQocSh0LFwib3BhY2l0eVwiLHMsITEsXCIxXCIpKSxsPXQuc3R5bGUsaD1cImF1dG9BbHBoYVwiPT09aTtyZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgZSYmXCI9XCI9PT1lLmNoYXJBdCgxKSYmKGU9KFwiLVwiPT09ZS5jaGFyQXQoMCk/LTE6MSkqcGFyc2VGbG9hdChlLnN1YnN0cigyKSkrbyksaCYmMT09PW8mJlwiaGlkZGVuXCI9PT1xKHQsXCJ2aXNpYmlsaXR5XCIscykmJjAhPT1lJiYobz0wKSxZP249bmV3IF9lKGwsXCJvcGFjaXR5XCIsbyxlLW8sbik6KG49bmV3IF9lKGwsXCJvcGFjaXR5XCIsMTAwKm8sMTAwKihlLW8pLG4pLG4ueG4xPWg/MTowLGwuem9vbT0xLG4udHlwZT0yLG4uYj1cImFscGhhKG9wYWNpdHk9XCIrbi5zK1wiKVwiLG4uZT1cImFscGhhKG9wYWNpdHk9XCIrKG4ucytuLmMpK1wiKVwiLG4uZGF0YT10LG4ucGx1Z2luPWEsbi5zZXRSYXRpbz1rZSksaCYmKG49bmV3IF9lKGwsXCJ2aXNpYmlsaXR5XCIsMCwwLG4sLTEsbnVsbCwhMSwwLDAhPT1vP1wiaW5oZXJpdFwiOlwiaGlkZGVuXCIsMD09PWU/XCJoaWRkZW5cIjpcImluaGVyaXRcIiksbi54czA9XCJpbmhlcml0XCIsci5fb3ZlcndyaXRlUHJvcHMucHVzaChuLm4pLHIuX292ZXJ3cml0ZVByb3BzLnB1c2goaSkpLG59fSk7dmFyIEFlPWZ1bmN0aW9uKHQsZSl7ZSYmKHQucmVtb3ZlUHJvcGVydHk/KFwibXNcIj09PWUuc3Vic3RyKDAsMikmJihlPVwiTVwiK2Uuc3Vic3RyKDEpKSx0LnJlbW92ZVByb3BlcnR5KGUucmVwbGFjZShQLFwiLSQxXCIpLnRvTG93ZXJDYXNlKCkpKTp0LnJlbW92ZUF0dHJpYnV0ZShlKSl9LE9lPWZ1bmN0aW9uKHQpe2lmKHRoaXMudC5fZ3NDbGFzc1BUPXRoaXMsMT09PXR8fDA9PT10KXt0aGlzLnQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIiwwPT09dD90aGlzLmI6dGhpcy5lKTtmb3IodmFyIGU9dGhpcy5kYXRhLGk9dGhpcy50LnN0eWxlO2U7KWUudj9pW2UucF09ZS52OkFlKGksZS5wKSxlPWUuX25leHQ7MT09PXQmJnRoaXMudC5fZ3NDbGFzc1BUPT09dGhpcyYmKHRoaXMudC5fZ3NDbGFzc1BUPW51bGwpfWVsc2UgdGhpcy50LmdldEF0dHJpYnV0ZShcImNsYXNzXCIpIT09dGhpcy5lJiZ0aGlzLnQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIix0aGlzLmUpfTttZShcImNsYXNzTmFtZVwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLHIsbixhLG8sbCl7dmFyIGgsdSxmLF8scCxjPXQuZ2V0QXR0cmlidXRlKFwiY2xhc3NcIil8fFwiXCIsZD10LnN0eWxlLmNzc1RleHQ7aWYoYT1uLl9jbGFzc05hbWVQVD1uZXcgX2UodCxyLDAsMCxhLDIpLGEuc2V0UmF0aW89T2UsYS5wcj0tMTEsaT0hMCxhLmI9Yyx1PSQodCxzKSxmPXQuX2dzQ2xhc3NQVCl7Zm9yKF89e30scD1mLmRhdGE7cDspX1twLnBdPTEscD1wLl9uZXh0O2Yuc2V0UmF0aW8oMSl9cmV0dXJuIHQuX2dzQ2xhc3NQVD1hLGEuZT1cIj1cIiE9PWUuY2hhckF0KDEpP2U6Yy5yZXBsYWNlKFJlZ0V4cChcIlxcXFxzKlxcXFxiXCIrZS5zdWJzdHIoMikrXCJcXFxcYlwiKSxcIlwiKSsoXCIrXCI9PT1lLmNoYXJBdCgwKT9cIiBcIitlLnN1YnN0cigyKTpcIlwiKSxuLl90d2Vlbi5fZHVyYXRpb24mJih0LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsYS5lKSxoPUcodCx1LCQodCksbCxfKSx0LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsYyksYS5kYXRhPWguZmlyc3RNUFQsdC5zdHlsZS5jc3NUZXh0PWQsYT1hLnhmaXJzdD1uLnBhcnNlKHQsaC5kaWZzLGEsbykpLGF9fSk7dmFyIERlPWZ1bmN0aW9uKHQpe2lmKCgxPT09dHx8MD09PXQpJiZ0aGlzLmRhdGEuX3RvdGFsVGltZT09PXRoaXMuZGF0YS5fdG90YWxEdXJhdGlvbiYmXCJpc0Zyb21TdGFydFwiIT09dGhpcy5kYXRhLmRhdGEpe3ZhciBlLGkscixzLG49dGhpcy50LnN0eWxlLGE9by50cmFuc2Zvcm0ucGFyc2U7aWYoXCJhbGxcIj09PXRoaXMuZSluLmNzc1RleHQ9XCJcIixzPSEwO2Vsc2UgZm9yKGU9dGhpcy5lLnNwbGl0KFwiLFwiKSxyPWUubGVuZ3RoOy0tcj4tMTspaT1lW3JdLG9baV0mJihvW2ldLnBhcnNlPT09YT9zPSEwOmk9XCJ0cmFuc2Zvcm1PcmlnaW5cIj09PWk/d2U6b1tpXS5wKSxBZShuLGkpO3MmJihBZShuLHllKSx0aGlzLnQuX2dzVHJhbnNmb3JtJiZkZWxldGUgdGhpcy50Ll9nc1RyYW5zZm9ybSl9fTtmb3IobWUoXCJjbGVhclByb3BzXCIse3BhcnNlcjpmdW5jdGlvbih0LGUscixzLG4pe3JldHVybiBuPW5ldyBfZSh0LHIsMCwwLG4sMiksbi5zZXRSYXRpbz1EZSxuLmU9ZSxuLnByPS0xMCxuLmRhdGE9cy5fdHdlZW4saT0hMCxufX0pLGw9XCJiZXppZXIsdGhyb3dQcm9wcyxwaHlzaWNzUHJvcHMscGh5c2ljczJEXCIuc3BsaXQoXCIsXCIpLGNlPWwubGVuZ3RoO2NlLS07KWdlKGxbY2VdKTtsPWEucHJvdG90eXBlLGwuX2ZpcnN0UFQ9bnVsbCxsLl9vbkluaXRUd2Vlbj1mdW5jdGlvbih0LGUsbyl7aWYoIXQubm9kZVR5cGUpcmV0dXJuITE7dGhpcy5fdGFyZ2V0PXQsdGhpcy5fdHdlZW49byx0aGlzLl92YXJzPWUsaD1lLmF1dG9Sb3VuZCxpPSExLHI9ZS5zdWZmaXhNYXB8fGEuc3VmZml4TWFwLHM9SCh0LFwiXCIpLG49dGhpcy5fb3ZlcndyaXRlUHJvcHM7dmFyIGwsXyxjLGQsbSxnLHYseSxULHg9dC5zdHlsZTtpZih1JiZcIlwiPT09eC56SW5kZXgmJihsPXEodCxcInpJbmRleFwiLHMpLChcImF1dG9cIj09PWx8fFwiXCI9PT1sKSYmdGhpcy5fYWRkTGF6eVNldCh4LFwiekluZGV4XCIsMCkpLFwic3RyaW5nXCI9PXR5cGVvZiBlJiYoZD14LmNzc1RleHQsbD0kKHQscykseC5jc3NUZXh0PWQrXCI7XCIrZSxsPUcodCxsLCQodCkpLmRpZnMsIVkmJncudGVzdChlKSYmKGwub3BhY2l0eT1wYXJzZUZsb2F0KFJlZ0V4cC4kMSkpLGU9bCx4LmNzc1RleHQ9ZCksdGhpcy5fZmlyc3RQVD1fPXRoaXMucGFyc2UodCxlLG51bGwpLHRoaXMuX3RyYW5zZm9ybVR5cGUpe2ZvcihUPTM9PT10aGlzLl90cmFuc2Zvcm1UeXBlLHllP2YmJih1PSEwLFwiXCI9PT14LnpJbmRleCYmKHY9cSh0LFwiekluZGV4XCIscyksKFwiYXV0b1wiPT09dnx8XCJcIj09PXYpJiZ0aGlzLl9hZGRMYXp5U2V0KHgsXCJ6SW5kZXhcIiwwKSkscCYmdGhpcy5fYWRkTGF6eVNldCh4LFwiV2Via2l0QmFja2ZhY2VWaXNpYmlsaXR5XCIsdGhpcy5fdmFycy5XZWJraXRCYWNrZmFjZVZpc2liaWxpdHl8fChUP1widmlzaWJsZVwiOlwiaGlkZGVuXCIpKSk6eC56b29tPTEsYz1fO2MmJmMuX25leHQ7KWM9Yy5fbmV4dDt5PW5ldyBfZSh0LFwidHJhbnNmb3JtXCIsMCwwLG51bGwsMiksdGhpcy5fbGlua0NTU1AoeSxudWxsLGMpLHkuc2V0UmF0aW89VCYmeGU/Q2U6eWU/UmU6U2UseS5kYXRhPXRoaXMuX3RyYW5zZm9ybXx8UGUodCxzLCEwKSxuLnBvcCgpfWlmKGkpe2Zvcig7Xzspe2ZvcihnPV8uX25leHQsYz1kO2MmJmMucHI+Xy5wcjspYz1jLl9uZXh0OyhfLl9wcmV2PWM/Yy5fcHJldjptKT9fLl9wcmV2Ll9uZXh0PV86ZD1fLChfLl9uZXh0PWMpP2MuX3ByZXY9XzptPV8sXz1nfXRoaXMuX2ZpcnN0UFQ9ZH1yZXR1cm4hMH0sbC5wYXJzZT1mdW5jdGlvbih0LGUsaSxuKXt2YXIgYSxsLHUsZixfLHAsYyxkLG0sZyx2PXQuc3R5bGU7Zm9yKGEgaW4gZSlwPWVbYV0sbD1vW2FdLGw/aT1sLnBhcnNlKHQscCxhLHRoaXMsaSxuLGUpOihfPXEodCxhLHMpK1wiXCIsbT1cInN0cmluZ1wiPT10eXBlb2YgcCxcImNvbG9yXCI9PT1hfHxcImZpbGxcIj09PWF8fFwic3Ryb2tlXCI9PT1hfHwtMSE9PWEuaW5kZXhPZihcIkNvbG9yXCIpfHxtJiZiLnRlc3QocCk/KG18fChwPW9lKHApLHA9KHAubGVuZ3RoPjM/XCJyZ2JhKFwiOlwicmdiKFwiKStwLmpvaW4oXCIsXCIpK1wiKVwiKSxpPXBlKHYsYSxfLHAsITAsXCJ0cmFuc3BhcmVudFwiLGksMCxuKSk6IW18fC0xPT09cC5pbmRleE9mKFwiIFwiKSYmLTE9PT1wLmluZGV4T2YoXCIsXCIpPyh1PXBhcnNlRmxvYXQoXyksYz11fHwwPT09dT9fLnN1YnN0cigodStcIlwiKS5sZW5ndGgpOlwiXCIsKFwiXCI9PT1ffHxcImF1dG9cIj09PV8pJiYoXCJ3aWR0aFwiPT09YXx8XCJoZWlnaHRcIj09PWE/KHU9dGUodCxhLHMpLGM9XCJweFwiKTpcImxlZnRcIj09PWF8fFwidG9wXCI9PT1hPyh1PVoodCxhLHMpLGM9XCJweFwiKToodT1cIm9wYWNpdHlcIiE9PWE/MDoxLGM9XCJcIikpLGc9bSYmXCI9XCI9PT1wLmNoYXJBdCgxKSxnPyhmPXBhcnNlSW50KHAuY2hhckF0KDApK1wiMVwiLDEwKSxwPXAuc3Vic3RyKDIpLGYqPXBhcnNlRmxvYXQocCksZD1wLnJlcGxhY2UoeSxcIlwiKSk6KGY9cGFyc2VGbG9hdChwKSxkPW0/cC5zdWJzdHIoKGYrXCJcIikubGVuZ3RoKXx8XCJcIjpcIlwiKSxcIlwiPT09ZCYmKGQ9YSBpbiByP3JbYV06YykscD1mfHwwPT09Zj8oZz9mK3U6ZikrZDplW2FdLGMhPT1kJiZcIlwiIT09ZCYmKGZ8fDA9PT1mKSYmdSYmKHU9USh0LGEsdSxjKSxcIiVcIj09PWQ/KHUvPVEodCxhLDEwMCxcIiVcIikvMTAwLGUuc3RyaWN0VW5pdHMhPT0hMCYmKF89dStcIiVcIikpOlwiZW1cIj09PWQ/dS89USh0LGEsMSxcImVtXCIpOlwicHhcIiE9PWQmJihmPVEodCxhLGYsZCksZD1cInB4XCIpLGcmJihmfHwwPT09ZikmJihwPWYrdStkKSksZyYmKGYrPXUpLCF1JiYwIT09dXx8IWYmJjAhPT1mP3ZvaWQgMCE9PXZbYV0mJihwfHxcIk5hTlwiIT1wK1wiXCImJm51bGwhPXApPyhpPW5ldyBfZSh2LGEsZnx8dXx8MCwwLGksLTEsYSwhMSwwLF8scCksaS54czA9XCJub25lXCIhPT1wfHxcImRpc3BsYXlcIiE9PWEmJi0xPT09YS5pbmRleE9mKFwiU3R5bGVcIik/cDpfKTpVKFwiaW52YWxpZCBcIithK1wiIHR3ZWVuIHZhbHVlOiBcIitlW2FdKTooaT1uZXcgX2UodixhLHUsZi11LGksMCxhLGghPT0hMSYmKFwicHhcIj09PWR8fFwiekluZGV4XCI9PT1hKSwwLF8scCksaS54czA9ZCkpOmk9cGUodixhLF8scCwhMCxudWxsLGksMCxuKSksbiYmaSYmIWkucGx1Z2luJiYoaS5wbHVnaW49bik7cmV0dXJuIGl9LGwuc2V0UmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHM9dGhpcy5fZmlyc3RQVCxuPTFlLTY7aWYoMSE9PXR8fHRoaXMuX3R3ZWVuLl90aW1lIT09dGhpcy5fdHdlZW4uX2R1cmF0aW9uJiYwIT09dGhpcy5fdHdlZW4uX3RpbWUpaWYodHx8dGhpcy5fdHdlZW4uX3RpbWUhPT10aGlzLl90d2Vlbi5fZHVyYXRpb24mJjAhPT10aGlzLl90d2Vlbi5fdGltZXx8dGhpcy5fdHdlZW4uX3Jhd1ByZXZUaW1lPT09LTFlLTYpZm9yKDtzOyl7aWYoZT1zLmMqdCtzLnMscy5yP2U9TWF0aC5yb3VuZChlKTpuPmUmJmU+LW4mJihlPTApLHMudHlwZSlpZigxPT09cy50eXBlKWlmKHI9cy5sLDI9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czI7ZWxzZSBpZigzPT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzO2Vsc2UgaWYoND09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMytzLnhuMytzLnhzNDtlbHNlIGlmKDU9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czMrcy54bjMrcy54czQrcy54bjQrcy54czU7ZWxzZXtmb3IoaT1zLnhzMCtlK3MueHMxLHI9MTtzLmw+cjtyKyspaSs9c1tcInhuXCIrcl0rc1tcInhzXCIrKHIrMSldO3MudFtzLnBdPWl9ZWxzZS0xPT09cy50eXBlP3MudFtzLnBdPXMueHMwOnMuc2V0UmF0aW8mJnMuc2V0UmF0aW8odCk7ZWxzZSBzLnRbcy5wXT1lK3MueHMwO3M9cy5fbmV4dH1lbHNlIGZvcig7czspMiE9PXMudHlwZT9zLnRbcy5wXT1zLmI6cy5zZXRSYXRpbyh0KSxzPXMuX25leHQ7ZWxzZSBmb3IoO3M7KTIhPT1zLnR5cGU/cy50W3MucF09cy5lOnMuc2V0UmF0aW8odCkscz1zLl9uZXh0fSxsLl9lbmFibGVUcmFuc2Zvcm1zPWZ1bmN0aW9uKHQpe3RoaXMuX3RyYW5zZm9ybVR5cGU9dHx8Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGU/MzoyLHRoaXMuX3RyYW5zZm9ybT10aGlzLl90cmFuc2Zvcm18fFBlKHRoaXMuX3RhcmdldCxzLCEwKX07dmFyIE1lPWZ1bmN0aW9uKCl7dGhpcy50W3RoaXMucF09dGhpcy5lLHRoaXMuZGF0YS5fbGlua0NTU1AodGhpcyx0aGlzLl9uZXh0LG51bGwsITApfTtsLl9hZGRMYXp5U2V0PWZ1bmN0aW9uKHQsZSxpKXt2YXIgcj10aGlzLl9maXJzdFBUPW5ldyBfZSh0LGUsMCwwLHRoaXMuX2ZpcnN0UFQsMik7ci5lPWksci5zZXRSYXRpbz1NZSxyLmRhdGE9dGhpc30sbC5fbGlua0NTU1A9ZnVuY3Rpb24odCxlLGkscil7cmV0dXJuIHQmJihlJiYoZS5fcHJldj10KSx0Ll9uZXh0JiYodC5fbmV4dC5fcHJldj10Ll9wcmV2KSx0Ll9wcmV2P3QuX3ByZXYuX25leHQ9dC5fbmV4dDp0aGlzLl9maXJzdFBUPT09dCYmKHRoaXMuX2ZpcnN0UFQ9dC5fbmV4dCxyPSEwKSxpP2kuX25leHQ9dDpyfHxudWxsIT09dGhpcy5fZmlyc3RQVHx8KHRoaXMuX2ZpcnN0UFQ9dCksdC5fbmV4dD1lLHQuX3ByZXY9aSksdH0sbC5fa2lsbD1mdW5jdGlvbihlKXt2YXIgaSxyLHMsbj1lO2lmKGUuYXV0b0FscGhhfHxlLmFscGhhKXtuPXt9O2ZvcihyIGluIGUpbltyXT1lW3JdO24ub3BhY2l0eT0xLG4uYXV0b0FscGhhJiYobi52aXNpYmlsaXR5PTEpfXJldHVybiBlLmNsYXNzTmFtZSYmKGk9dGhpcy5fY2xhc3NOYW1lUFQpJiYocz1pLnhmaXJzdCxzJiZzLl9wcmV2P3RoaXMuX2xpbmtDU1NQKHMuX3ByZXYsaS5fbmV4dCxzLl9wcmV2Ll9wcmV2KTpzPT09dGhpcy5fZmlyc3RQVCYmKHRoaXMuX2ZpcnN0UFQ9aS5fbmV4dCksaS5fbmV4dCYmdGhpcy5fbGlua0NTU1AoaS5fbmV4dCxpLl9uZXh0Ll9uZXh0LHMuX3ByZXYpLHRoaXMuX2NsYXNzTmFtZVBUPW51bGwpLHQucHJvdG90eXBlLl9raWxsLmNhbGwodGhpcyxuKX07dmFyIExlPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcixzLG4sYTtpZih0LnNsaWNlKWZvcihzPXQubGVuZ3RoOy0tcz4tMTspTGUodFtzXSxlLGkpO2Vsc2UgZm9yKHI9dC5jaGlsZE5vZGVzLHM9ci5sZW5ndGg7LS1zPi0xOyluPXJbc10sYT1uLnR5cGUsbi5zdHlsZSYmKGUucHVzaCgkKG4pKSxpJiZpLnB1c2gobikpLDEhPT1hJiY5IT09YSYmMTEhPT1hfHwhbi5jaGlsZE5vZGVzLmxlbmd0aHx8TGUobixlLGkpfTtyZXR1cm4gYS5jYXNjYWRlVG89ZnVuY3Rpb24odCxpLHIpe3ZhciBzLG4sYSxvPWUudG8odCxpLHIpLGw9W29dLGg9W10sdT1bXSxmPVtdLF89ZS5faW50ZXJuYWxzLnJlc2VydmVkUHJvcHM7Zm9yKHQ9by5fdGFyZ2V0c3x8by50YXJnZXQsTGUodCxoLGYpLG8ucmVuZGVyKGksITApLExlKHQsdSksby5yZW5kZXIoMCwhMCksby5fZW5hYmxlZCghMCkscz1mLmxlbmd0aDstLXM+LTE7KWlmKG49RyhmW3NdLGhbc10sdVtzXSksbi5maXJzdE1QVCl7bj1uLmRpZnM7XHJcbmZvcihhIGluIHIpX1thXSYmKG5bYV09clthXSk7bC5wdXNoKGUudG8oZltzXSxpLG4pKX1yZXR1cm4gbH0sdC5hY3RpdmF0ZShbYV0pLGF9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjcuM1xyXG4gKiBEQVRFOiAyMDE0LTAxLTE0XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKiovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7dmFyIHQ9ZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LGU9d2luZG93LGk9ZnVuY3Rpb24oaSxzKXt2YXIgcj1cInhcIj09PXM/XCJXaWR0aFwiOlwiSGVpZ2h0XCIsbj1cInNjcm9sbFwiK3IsYT1cImNsaWVudFwiK3Isbz1kb2N1bWVudC5ib2R5O3JldHVybiBpPT09ZXx8aT09PXR8fGk9PT1vP01hdGgubWF4KHRbbl0sb1tuXSktKGVbXCJpbm5lclwiK3JdfHxNYXRoLm1heCh0W2FdLG9bYV0pKTppW25dLWlbXCJvZmZzZXRcIityXX0scz13aW5kb3cuX2dzRGVmaW5lLnBsdWdpbih7cHJvcE5hbWU6XCJzY3JvbGxUb1wiLEFQSToyLHZlcnNpb246XCIxLjcuM1wiLGluaXQ6ZnVuY3Rpb24odCxzLHIpe3JldHVybiB0aGlzLl93ZHc9dD09PWUsdGhpcy5fdGFyZ2V0PXQsdGhpcy5fdHdlZW49cixcIm9iamVjdFwiIT10eXBlb2YgcyYmKHM9e3k6c30pLHRoaXMuX2F1dG9LaWxsPXMuYXV0b0tpbGwhPT0hMSx0aGlzLng9dGhpcy54UHJldj10aGlzLmdldFgoKSx0aGlzLnk9dGhpcy55UHJldj10aGlzLmdldFkoKSxudWxsIT1zLng/KHRoaXMuX2FkZFR3ZWVuKHRoaXMsXCJ4XCIsdGhpcy54LFwibWF4XCI9PT1zLng/aSh0LFwieFwiKTpzLngsXCJzY3JvbGxUb194XCIsITApLHRoaXMuX292ZXJ3cml0ZVByb3BzLnB1c2goXCJzY3JvbGxUb194XCIpKTp0aGlzLnNraXBYPSEwLG51bGwhPXMueT8odGhpcy5fYWRkVHdlZW4odGhpcyxcInlcIix0aGlzLnksXCJtYXhcIj09PXMueT9pKHQsXCJ5XCIpOnMueSxcInNjcm9sbFRvX3lcIiwhMCksdGhpcy5fb3ZlcndyaXRlUHJvcHMucHVzaChcInNjcm9sbFRvX3lcIikpOnRoaXMuc2tpcFk9ITAsITB9LHNldDpmdW5jdGlvbih0KXt0aGlzLl9zdXBlci5zZXRSYXRpby5jYWxsKHRoaXMsdCk7dmFyIHM9dGhpcy5fd2R3fHwhdGhpcy5za2lwWD90aGlzLmdldFgoKTp0aGlzLnhQcmV2LHI9dGhpcy5fd2R3fHwhdGhpcy5za2lwWT90aGlzLmdldFkoKTp0aGlzLnlQcmV2LG49ci10aGlzLnlQcmV2LGE9cy10aGlzLnhQcmV2O3RoaXMuX2F1dG9LaWxsJiYoIXRoaXMuc2tpcFgmJihhPjd8fC03PmEpJiZpKHRoaXMuX3RhcmdldCxcInhcIik+cyYmKHRoaXMuc2tpcFg9ITApLCF0aGlzLnNraXBZJiYobj43fHwtNz5uKSYmaSh0aGlzLl90YXJnZXQsXCJ5XCIpPnImJih0aGlzLnNraXBZPSEwKSx0aGlzLnNraXBYJiZ0aGlzLnNraXBZJiZ0aGlzLl90d2Vlbi5raWxsKCkpLHRoaXMuX3dkdz9lLnNjcm9sbFRvKHRoaXMuc2tpcFg/czp0aGlzLngsdGhpcy5za2lwWT9yOnRoaXMueSk6KHRoaXMuc2tpcFl8fCh0aGlzLl90YXJnZXQuc2Nyb2xsVG9wPXRoaXMueSksdGhpcy5za2lwWHx8KHRoaXMuX3RhcmdldC5zY3JvbGxMZWZ0PXRoaXMueCkpLHRoaXMueFByZXY9dGhpcy54LHRoaXMueVByZXY9dGhpcy55fX0pLHI9cy5wcm90b3R5cGU7cy5tYXg9aSxyLmdldFg9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fd2R3P251bGwhPWUucGFnZVhPZmZzZXQ/ZS5wYWdlWE9mZnNldDpudWxsIT10LnNjcm9sbExlZnQ/dC5zY3JvbGxMZWZ0OmRvY3VtZW50LmJvZHkuc2Nyb2xsTGVmdDp0aGlzLl90YXJnZXQuc2Nyb2xsTGVmdH0sci5nZXRZPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3dkdz9udWxsIT1lLnBhZ2VZT2Zmc2V0P2UucGFnZVlPZmZzZXQ6bnVsbCE9dC5zY3JvbGxUb3A/dC5zY3JvbGxUb3A6ZG9jdW1lbnQuYm9keS5zY3JvbGxUb3A6dGhpcy5fdGFyZ2V0LnNjcm9sbFRvcH0sci5fa2lsbD1mdW5jdGlvbih0KXtyZXR1cm4gdC5zY3JvbGxUb194JiYodGhpcy5za2lwWD0hMCksdC5zY3JvbGxUb195JiYodGhpcy5za2lwWT0hMCksdGhpcy5fc3VwZXIuX2tpbGwuY2FsbCh0aGlzLHQpfX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyJdfQ==
