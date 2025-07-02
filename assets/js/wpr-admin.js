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
      button.addClass('wpr-isLoading');
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

  // MixPanel tracking function
  function wprTrackHelpButton(button, context) {
    if (typeof mixpanel !== 'undefined' && mixpanel.track) {
      // Check if user has opted in using localized data
      if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
        return;
      }
      mixpanel.track('WPM Button Clicked', {
        'button': button,
        'brand': rocket_mixpanel_data.brand,
        'product': rocket_mixpanel_data.product,
        'context': context,
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

  // Exception for dashboard
  if (this.pageId == "dashboard") {
    this.$sidebar.style.display = 'none';
    this.$tips.style.display = 'none';
    this.$submitButton.style.display = 'none';
    this.$content.classList.remove('isNotFull');
  }

  // Exception for addons
  if (this.pageId == "addons") {
    this.$submitButton.style.display = 'none';
  }

  // Exception for database
  if (this.pageId == "database") {
    this.$submitButton.style.display = 'none';
  }

  // Exception for tools and addons
  if (this.pageId == "tools" || this.pageId == "addons") {
    this.$submitButton.style.display = 'none';
  }
  if (this.pageId == "imagify") {
    this.$sidebar.style.display = 'none';
    this.$tips.style.display = 'none';
    this.$submitButton.style.display = 'none';
  }
  if (this.pageId == "tutorials") {
    this.$submitButton.style.display = 'none';
  }
  if (this.pageId == "plugins") {
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvcGFnZU1hbmFnZXIuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldGNkbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxJQUFJO0VBQ25FLE1BQU0sU0FBUyxHQUFHLFlBQVksQ0FBQyxhQUFhLENBQUMsZ0JBQWdCLENBQUM7RUFDOUQsTUFBTSxhQUFhLEdBQUcsWUFBWSxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQztFQUNuRSxNQUFNLE9BQU8sR0FBRyxTQUFBLENBQVMsR0FBRyxFQUFFO0lBQzdCLE1BQU0saUJBQWlCLEdBQUcsSUFBSSxXQUFXLENBQUMsc0JBQXNCLEVBQUU7TUFDakUsTUFBTSxFQUFFO1FBQ1AsY0FBYyxFQUFFO01BQ2pCO0lBQ0QsQ0FBQyxDQUFDO0lBQ0YsYUFBYSxDQUFDLFdB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SUFBSSxDQUFDLElBQUksQ0FBQyxRQUFRO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLFVBQVU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxPQUFPO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxPQUFPO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7VUFBQyxVQUFVLEVBQUMsQ0FBQztVQUFDLGdCQUFnQixFQUFDLENBQUM7VUFBQyxlQUFlLEVBQUMsQ0FBQztVQUFDLGlCQUFpQixFQUFDLElBQUksQ0FBQztRQUFpQixDQUFDLENBQUM7TUFBQyxLQUFJLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLENBQUMsZUFBZSxLQUFHLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxpQkFBaUIsS0FBRyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztNQUFDLEtBQUksSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBRSxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFFLFFBQVEsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztZQUFDLE1BQU0sRUFBQztVQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLFVBQVUsSUFBRSxPQUFPLENBQUMsS0FBRyxVQUFVLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsT0FBTyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQztVQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFHLFVBQVUsSUFBRSxPQUFPLENBQUMsRUFBQyxNQUFLLGFBQWEsR0FBQyxDQUFDLEdBQUMsdUVBQXVFO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxDQUFDLGlCQUFpQixHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFlBQVksQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxJQUFJO01BQUE7TUFBQyxPQUFNLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLGNBQWMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztNQUFDLElBQUcsQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxLQUFHLElBQUksRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxZQUFXLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLElBQUksSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFJO1FBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsT0FBTyxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFNBQVMsQ0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGNBQWM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU87TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxlQUFlLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsWUFBWSxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsbUJBQW1CLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLElBQUksR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxJQUFFLENBQUMsQ0FBQyxNQUFJLENBQUMsR0FBQyxtQkFBbUIsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsWUFBWSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxZQUFZLEdBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxZQUFZLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUcsTUFBSSxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBRyxNQUFJLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsTUFBSSxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxrQkFBa0IsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLFlBQVU7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFFO1FBQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLEVBQUMsT0FBTSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQTtNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsVUFBVTtNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLENBQUMsWUFBWSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLEtBQUksQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFO1FBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxFQUFDLE9BQU0sQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRO01BQUE7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07TUFBQyxLQUFJLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsRUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxLQUFHLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxNQUFNLEVBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxZQUFZLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLGFBQWEsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEtBQUcsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsRUFBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLFVBQVUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxPQUFPLElBQUksQ0FBQyxjQUFjO01BQUE7TUFBQyxPQUFPLENBQUMsS0FBRyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsQ0FBQyxtQkFBbUI7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsVUFBVTtJQUFBLENBQUMsRUFBQyxDQUFDO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0FBQUEsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxNQUFNLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQzs7Ozs7QUNYeHJUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLFVBQVMsQ0FBQyxFQUFDO0VBQUMsWUFBWTs7RUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLElBQUUsQ0FBQztFQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDO0lBQUMsSUFBSSxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDO01BQUMsQ0FBQyxHQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFBLEVBQVUsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFlBQVU7UUFBQyxJQUFJLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLFFBQVE7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUM7UUFBQyxPQUFPLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsWUFBWSxLQUFLLElBQUUsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDO1FBQUEsQ0FBQztNQUFBLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxFQUFFO1FBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFFLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDO1VBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLGdCQUFnQixHQUFDLENBQUMsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sTUFBTSxJQUFFLE1BQU0sQ0FBQyxHQUFHLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxHQUFHLEdBQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsRUFBQyxZQUFVO1lBQUMsT0FBTyxDQUFDO1VBQUEsQ0FBQyxDQUFDLEdBQUMsV0FBVyxJQUFFLE9BQU8sTUFBTSxJQUFFLE1BQU0sQ0FBQyxPQUFPLEtBQUcsTUFBTSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsWUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxZQUFVO1VBQUMsT0FBTyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLDBCQUEwQixFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQSxDQUFDO0lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNO1FBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLFFBQVEsRUFBQyxNQUFNLEVBQUMsT0FBTyxFQUFDLE9BQU8sRUFBQyxjQUFjLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFdBQVcsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsV0FBVyxDQUFDO0lBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxTQUFTO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLHdCQUF3QixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsSUFBRSxJQUFJO0lBQUEsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLEtBQUksSUFBSSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsRUFBRSxFQUFDLENBQUM7UUFBQyxFQUFFLEVBQUM7TUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsWUFBWSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsTUFBTSxFQUFDO01BQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLHFCQUFxQjtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsb0JBQW9CO01BQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsWUFBVTtRQUFDLE9BQU8sSUFBSSxJQUFJLENBQUQsQ0FBQyxDQUFFLE9BQU8sQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBSSxFQUFDLEtBQUssRUFBQyxRQUFRLEVBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsdUJBQXVCLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxzQkFBc0IsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsNkJBQTZCLENBQUM7SUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLENBQUMsR0FBQyxHQUFHO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQztZQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxLQUFLLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsYUFBYSxDQUFDLE1BQU0sQ0FBQztRQUFBLENBQUM7TUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO1FBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsWUFBVTtRQUFDLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsWUFBVTtRQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxFQUFFLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxJQUFFLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxZQUFVO1FBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxJQUFJLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUMsTUFBTSxDQUFDLGVBQWUsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsTUFBTTtJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLGVBQWUsS0FBRyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsQ0FBQyxNQUFNLENBQUQsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVTtNQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7SUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsWUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxZQUFVO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO01BQUMsT0FBTSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsUUFBUSxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLElBQUksS0FBRyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1FBQUMsSUFBRyxDQUFDLEtBQUcsU0FBUyxDQUFDLE1BQU0sRUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLFVBQVUsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLElBQUksQ0FBQyxNQUFNO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsSUFBRSxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksS0FBRyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGNBQWM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxPQUFPLElBQUksQ0FBQyxVQUFVO01BQUMsSUFBRyxJQUFJLENBQUMsU0FBUyxFQUFDO1FBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEVBQUM7VUFBQyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQztVQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxjQUFjO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxPQUFLLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7UUFBQTtRQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLFVBQVUsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLE1BQUksSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxhQUFhLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsSUFBSSxDQUFDLFVBQVU7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sSUFBSSxDQUFDLFVBQVU7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVO1VBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLFNBQVMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxLQUFHLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsSUFBSSxDQUFDLFNBQVM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU87TUFBQyxJQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7UUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsaUJBQWlCLEtBQUcsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxxQkFBcUIsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsa0JBQWtCLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsVUFBVSxHQUFDLE1BQU0sQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsT0FBTyxJQUFFLElBQUksS0FBRyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsYUFBYSxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsUUFBUSxLQUFHLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsTUFBTSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTTtNQUFDLEtBQUksSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxNQUFJLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsWUFBVTtNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsSUFBSSxJQUFFLENBQUMsRUFBQyxNQUFLLDZCQUE2QjtRQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxJQUFHLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFJLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxXQUFXLEdBQUMsRUFBRSxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFJLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUM7UUFBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsZUFBZSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxlQUFlLEtBQUcsQ0FBQyxDQUFDLE1BQUksSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsT0FBTyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLFdBQVcsS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLHVCQUF1QixHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxnQkFBZ0IsR0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsY0FBYyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDO1FBQUMsT0FBTyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxHQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxnQkFBZ0IsRUFBQyxDQUFDO1FBQUMsZUFBZSxFQUFDLENBQUM7UUFBQyxTQUFTLEVBQUMsQ0FBQztRQUFDLFlBQVksRUFBQyxDQUFDO1FBQUMsT0FBTyxFQUFDLENBQUM7UUFBQyxRQUFRLEVBQUMsQ0FBQztRQUFDLGNBQWMsRUFBQyxDQUFDO1FBQUMsYUFBYSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLGFBQWEsRUFBQyxDQUFDO1FBQUMsWUFBWSxFQUFDLENBQUM7UUFBQyxpQkFBaUIsRUFBQyxDQUFDO1FBQUMsdUJBQXVCLEVBQUMsQ0FBQztRQUFDLHNCQUFzQixFQUFDLENBQUM7UUFBQyxRQUFRLEVBQUMsQ0FBQztRQUFDLGNBQWMsRUFBQyxDQUFDO1FBQUMsYUFBYSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsZUFBZSxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQztRQUFDLFdBQVcsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsT0FBTyxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxHQUFHLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLFdBQVcsRUFBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVTtRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFlBQVU7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsR0FBRyxDQUFDLEVBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDO1VBQUMsT0FBSyxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBQUE7TUFBQztJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxXQUFXLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVO1FBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDO1VBQUMsTUFBTSxFQUFDLENBQUM7VUFBQyxNQUFNLEVBQUM7UUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQUssRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBRyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDO1VBQU0sT0FBTyxDQUFDO1FBQUE7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFFO1VBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsT0FBTyxFQUFDLE9BQU0sQ0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO1FBQUE7UUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFlBQVU7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUI7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxlQUFlO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO01BQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxFQUFDO1FBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUM7TUFBTSxDQUFDLE1BQUssSUFBRyxDQUFDLENBQUMsWUFBWSxJQUFFLENBQUMsS0FBRyxDQUFDLEVBQUMsSUFBRyxJQUFJLENBQUMsUUFBUSxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLEtBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxTQUFTLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsYUFBYSxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEVBQUM7UUFBTSxDQUFDLE1BQUssSUFBSSxDQUFDLFFBQVEsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsWUFBWSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsWUFBWSxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLElBQUksQ0FBQyxXQUFXLEVBQUMsSUFBSSxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsY0FBYyxDQUFDLGlCQUFpQixFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxJQUFFLFVBQVUsSUFBRSxPQUFPLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsSUFBSSxJQUFFLENBQUMsRUFBQyxPQUFNLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUksQ0FBQyxJQUFJLElBQUksQ0FBQyxJQUFJLEVBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFELENBQUMsRUFBRSxZQUFZLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDO1lBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxRQUFRO1lBQUMsQ0FBQyxFQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsVUFBVTtZQUFDLENBQUMsRUFBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLENBQUM7WUFBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDO1lBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQztVQUFTLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtVQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsZUFBZSxNQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsU0FBUyxNQUFJLElBQUksQ0FBQyx1QkFBdUIsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsTUFBSyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUM7VUFBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFFBQVE7VUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEVBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQyxDQUFDLENBQUM7VUFBQyxFQUFFLEVBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFFLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFlBQVksQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsSUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsbUJBQW1CLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsSUFBSSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsTUFBSSxDQUFDLEdBQUMsbUJBQW1CLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxJQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7UUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsRUFBRSxNQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDLE1BQUssSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQztVQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxHQUFHLEVBQUM7VUFBTyxJQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxLQUFLLENBQUM7VUFBQyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLEtBQUksSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxZQUFZLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsYUFBYSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxHQUFHLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsa0JBQWtCLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQyxJQUFFLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLEtBQUssS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLEtBQUcsSUFBSSxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFJO1FBQUMsSUFBRyxJQUFJLENBQUMsUUFBUSxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsaUJBQWlCLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxLQUFLO1lBQUM7VUFBSztRQUFDLENBQUMsTUFBSTtVQUFDLElBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLEVBQUMsT0FBTSxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLElBQUUsQ0FBQyxDQUFDLEdBQUMsS0FBSztRQUFBO1FBQUMsSUFBRyxDQUFDLEVBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQztVQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFBO01BQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJLENBQUMsdUJBQXVCLElBQUUsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxZQUFZLEVBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLHVCQUF1QixHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFdBQVcsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxJQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyx1QkFBdUIsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxHQUFDLFdBQVcsR0FBQyxZQUFZLEVBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLEtBQUssRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDLENBQUM7UUFBQyxnQkFBZ0IsRUFBQyxDQUFDO1FBQUMsZUFBZSxFQUFDLENBQUM7UUFBQyxpQkFBaUIsRUFBQyxDQUFDO1FBQUMsdUJBQXVCLEVBQUMsQ0FBQztRQUFDLHNCQUFzQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDO1FBQUMsU0FBUyxFQUFDLENBQUM7UUFBQyxTQUFTLEVBQUM7TUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU0sRUFBRTtNQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLE1BQUssS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxrQkFBa0IsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLHFCQUFxQixFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxlQUFlLEdBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFNBQVM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUM7UUFBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEtBQUssQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsZUFBZTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtNQUFDLElBQUcsSUFBSSxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsSUFBSSxDQUFDLGVBQWUsR0FBQyxFQUFFLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBSyxDQUFDLEdBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxJQUFFLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQyxJQUFHLGlCQUFpQixLQUFHLENBQUMsRUFBQztRQUFDLE9BQUssQ0FBQyxHQUFFO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7TUFBQTtNQUFDLE9BQUssQ0FBQyxHQUFFLENBQUMsQ0FBQyxFQUFFLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFELENBQUMsQ0FBRSxTQUFTLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxFQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxFQUFDLE1BQUssNEJBQTRCO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYztRQUFDLENBQUMsR0FBQztVQUFDLElBQUksRUFBQyxjQUFjO1VBQUMsR0FBRyxFQUFDLFVBQVU7VUFBQyxJQUFJLEVBQUMsT0FBTztVQUFDLEtBQUssRUFBQyxhQUFhO1VBQUMsT0FBTyxFQUFDO1FBQWlCLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLEVBQUMsWUFBVTtVQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxFQUFFO1FBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQztNQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLHFEQUFxRCxHQUFDLENBQUMsQ0FBQztJQUFBO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztFQUFBO0FBQUMsQ0FBQyxFQUFFLE1BQU0sQ0FBQzs7Ozs7QUNYMTR2QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsYUFBYSxFQUFDLENBQUMsYUFBYSxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsZ0JBQWdCLElBQUUsTUFBTTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLFNBQVM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFlBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLFlBQVUsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQztVQUFDLE9BQU8sRUFBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO1VBQUMsTUFBTSxFQUFDLElBQUksQ0FBQyxDQUFELENBQUM7VUFBQyxTQUFTLEVBQUMsSUFBSSxDQUFDLENBQUQ7UUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQztZQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU8sRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsR0FBRztVQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFNLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUM7TUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGVBQWUsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsRUFBRTtNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsb0JBQW9CLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUc7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxrQkFBa0IsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLE1BQU0sRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLFlBQVksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQztNQUFDLEtBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztRQUFDLE9BQUssQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7TUFBQSxDQUFDLE1BQUssT0FBSyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtNQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsTUFBTSxJQUFFLENBQUMsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEdBQUMsQ0FBQyxJQUFFLE1BQU0sSUFBRSxDQUFDLElBQUUsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxJQUFJLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEtBQUssR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsRUFBRTtJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sRUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsWUFBWSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sRUFBRSxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSTtJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFNLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFNLENBQUMsRUFBRSxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxtQkFBbUIsRUFBQztNQUFDLElBQUksRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsUUFBUSxFQUFDLE9BQU8sQ0FBQyxFQUFDLENB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UUFBUTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDO1FBQUMsYUFBYSxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFTLENBQUMsU0FBUztNQUFDLENBQUMsR0FBQyxZQUFVO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsU0FBUyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsS0FBSyxDQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUMsRUFBQyw2QkFBNkIsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLHVDQUF1QyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsb0JBQW9CLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsTUFBTSxLQUFHLEVBQUUsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxNQUFNLENBQUMsT0FBTyxJQUFFLE9BQU8sQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBRyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBRyxFQUFDLEtBQUssRUFBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRTtRQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSTtNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLGdCQUFnQixHQUFDLFlBQVUsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1FBQUMsT0FBTyxDQUFDLElBQUUsU0FBUyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLFdBQVcsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFHLE1BQU0sS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMsS0FBSTtVQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyw4QkFBOEIsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxHQUFDLGlCQUFpQixFQUFDLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGlCQUFpQixHQUFDLGdCQUFnQixDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFJO1lBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEtBQUcsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsR0FBRztZQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLFFBQVEsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1VBQUE7VUFBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxhQUFhLEdBQUMsY0FBYyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxXQUFXLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLFVBQVUsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLE1BQU0sR0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxPQUFLLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQUssSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksSUFBRSxDQUFDLENBQUMsS0FBSyxFQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsT0FBTyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFNBQVMsS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsSUFBRSxLQUFLLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxHQUFDLEVBQUUsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxXQUFXLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFNO1VBQUMsSUFBSSxFQUFDLENBQUM7VUFBQyxRQUFRLEVBQUM7UUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLE1BQU0sRUFBQyxPQUFPLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxLQUFLLEVBQUMsUUFBUTtNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxZQUFZLEVBQUMsYUFBYSxFQUFDLFdBQVcsRUFBQyxjQUFjLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsVUFBVSxDQUFDLE9BQU8sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsTUFBSSxDQUFDLEdBQUMsS0FBSyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsUUFBUSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxJQUFFLEtBQUssQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsT0FBTyxDQUFDLEdBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSTtRQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLFdBQVcsRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRTtNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxDQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsS0FBSztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMscURBQXFEO0lBQUMsS0FBSSxDQUFDLElBQUksRUFBRSxFQUFDLEVBQUUsSUFBRSxHQUFHLEdBQUMsQ0FBQyxHQUFDLEtBQUs7SUFBQyxFQUFFLEdBQUMsTUFBTSxDQUFDLEVBQUUsR0FBQyxHQUFHLEVBQUMsSUFBSSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsSUFBSSxJQUFFLENBQUMsRUFBQyxPQUFPLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxDQUFDO1FBQUEsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsRUFBRSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLEVBQUU7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUM7VUFBQTtVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsRUFBQyxPQUFLLENBQUMsR0FBQyxFQUFFLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLEdBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQztRQUFBLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUM7VUFBQTtVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsRUFBQyxPQUFLLENBQUMsR0FBQyxFQUFFLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQSxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUM7VUFBQyxPQUFPLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLGVBQWUsR0FBQyxVQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLElBQUcsQ0FBQyxDQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsVUFBVSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRTtVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksRUFBQztZQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEVBQUM7Y0FBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1lBQUE7VUFBQyxDQUFDLE1BQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUE7TUFBQyxDQUFDLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxDQUFDO01BQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVO1VBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBRTtVQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQTtRQUFDLE9BQU07VUFBQyxLQUFLLEVBQUMsQ0FBQztVQUFDLEdBQUcsRUFBQyxDQUFDO1VBQUMsUUFBUSxFQUFDLENBQUM7VUFBQyxFQUFFLEVBQUM7UUFBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsWUFBWSxFQUFFLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQztNQUFBLENBQUMsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxFQUFFO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUUsRUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLGNBQWMsR0FBQyxhQUFhLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxHQUFDLE9BQU8sR0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLE1BQU0sRUFBQyxPQUFPLENBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLElBQUksS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLE1BQUssQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDO0lBQUMsS0FBSSxDQUFDLEdBQUMsRUFBRSxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBRSxHQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsSUFBSSxHQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLEVBQUUsQ0FBQyxHQUFDLEVBQUU7SUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxHQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQztNQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQywyQkFBMkIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQztVQUFDLE1BQU0sRUFBQztRQUFDLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVk7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRO1VBQUMsRUFBRSxDQUFDLENBQUMsRUFBQztZQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO2NBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxNQUFNLENBQUMsZ0JBQWdCLElBQUUsTUFBTSxFQUFFLEdBQUcsQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQztjQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsc0JBQXNCLENBQUMsRUFBQyxDQUFDLENBQUM7WUFBQTtVQUFDLENBQUMsQ0FBQztRQUFBO01BQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPO01BQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7TUFBQTtNQUFDLE9BQU8sRUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUM7UUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQSxDQUFDO1FBQUMsUUFBUSxFQUFDO01BQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLGlGQUFpRixDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsV0FBVztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsaUJBQWlCLENBQUM7TUFBQyxFQUFFLEdBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxhQUFhLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxZQUFVO1FBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsWUFBWSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxZQUFZO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLElBQUksRUFBRSxDQUFELENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBRCxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsR0FBQyxDQUFDO1FBQUMsS0FBSSxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLEtBQUssQ0FBQyx5QkFBeUIsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFHLEVBQUUsS0FBRyxDQUFDLENBQUMsTUFBTSxFQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7VUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsU0FBUyxFQUFDO1lBQUMsSUFBSSxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO2NBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUM7WUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1VBQUE7UUFBQyxDQUFDLE1BQUssSUFBRyxFQUFFLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxJQUFFLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1lBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1lBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1lBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxFQUFFLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsRUFBRSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsR0FBRyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxHQUFHLEVBQUMsQ0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsMkJBQTJCLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWTtRQUFDLElBQUcsQ0FBQyxFQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsRUFBRTtVQUFDLElBQUksQ0FBQztZQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxXQUFXO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWTtZQUFDLENBQUMsR0FBQyxVQUFVLEtBQUcsQ0FBQyxDQUFDLFFBQVE7WUFBQyxDQUFDLEdBQUMsK0NBQStDLEdBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLElBQUcsSUFBSSxJQUFFLENBQUMsQ0FBQyxFQUFFLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxJQUFFLCtCQUErQixFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxvQ0FBb0MsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxHQUFHLEtBQUcsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFdBQVcsSUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsSUFBSSxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLEVBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxFQUFFLElBQUUsQ0FBQyxLQUFHLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJO1VBQUE7UUFBQztNQUFDLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXO1FBQUMsSUFBRyxFQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sRUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDO1FBQUMsSUFBRyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsS0FBRyxDQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUk7VUFBQyxJQUFHLEVBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsS0FBSyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEVBQUUsQ0FBQyxFQUFDLEtBQUssQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEtBQUssQ0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLEVBQUUsQ0FBQyxtUEFBbVAsRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsVUFBVSxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsSUFBSTtVQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsTUFBTTtVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLFNBQVMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQztVQUFDLElBQUcsQ0FBQyxHQUFDO1lBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQztZQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQztZQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLFdBQVcsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLG9CQUFvQixFQUFDLENBQUMsQ0FBQyxXQUFXO1VBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEVBQUMsSUFBSSxJQUFFLENBQUMsRUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztVQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLFVBQVUsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxlQUFlLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsUUFBUSxHQUFDLFdBQVcsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLEVBQUUsQ0FBQyxXQUFXLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsZ0JBQWdCLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsRUFBRSxDQUFDLFdBQVcsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxnQkFBZ0IsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxNQUFJLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDO1FBQUE7UUFBQyxLQUFJLEVBQUUsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxFQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sTUFBSSxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxTQUFTLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsaUJBQWlCLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLENBQUMsR0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsRUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLGNBQWMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsV0FBVyxFQUFDO01BQUMsWUFBWSxFQUFDLHNCQUFzQjtNQUFDLE1BQU0sRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sRUFBQztJQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxjQUFjLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLHFCQUFxQixFQUFDLHNCQUFzQixFQUFDLHlCQUF5QixFQUFDLHdCQUF3QixDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsS0FBSSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFdBQVcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLE1BQU0sRUFBQyxDQUFDLENBQUM7TUFBQyxTQUFTLEVBQUMsRUFBRSxDQUFDLGlCQUFpQixFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxvQkFBb0IsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLHFCQUFxQjtVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxtQkFBbUIsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxtQkFBbUIsS0FBRyxLQUFLLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGlCQUFpQixDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsQ0FBQyxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLEdBQUcsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO1FBQUE7UUFBQyxPQUFPLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsU0FBUyxFQUFDO0lBQUUsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGdCQUFnQixFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxTQUFTLEVBQUM7SUFBRSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsYUFBYSxFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxtQkFBbUIsRUFBQztNQUFDLFlBQVksRUFBQyxTQUFTO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsZ0JBQWdCLEVBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxvQkFBb0IsRUFBQztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFlBQVksRUFBQztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFFBQVEsRUFBQztNQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsK0NBQStDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFNBQVMsRUFBQztNQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsbURBQW1EO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLE1BQU0sRUFBQztNQUFDLFlBQVksRUFBQyx1QkFBdUI7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxZQUFZLEVBQUM7TUFBQyxZQUFZLEVBQUMsa0JBQWtCO01BQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLHVCQUF1QixFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsUUFBUSxFQUFDO01BQUMsWUFBWSxFQUFDLGdCQUFnQjtNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGdCQUFnQixFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxnQkFBZ0IsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUMsQ0FBQztNQUFDLFNBQVMsRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLE9BQU8sQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxhQUFhLEVBQUM7TUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLG1FQUFtRTtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQywyQkFBMkIsRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLFVBQVUsSUFBRyxDQUFDLEdBQUMsVUFBVSxHQUFDLFlBQVk7UUFBQyxPQUFPLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsUUFBUSxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLEdBQUcsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLGdCQUFnQixHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxpQkFBaUIsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsRUFBRSxDQUFDLHlCQUF5QixFQUFDO01BQUMsWUFBWSxFQUFDLEdBQUc7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsU0FBUyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxXQUFXLEtBQUcsQ0FBQztRQUFDLE9BQU0sUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxnQkFBZ0IsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGdCQUFnQixJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsU0FBUyxHQUFDLFFBQVEsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFFBQVEsR0FBQyxTQUFTLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsY0FBYyxJQUFFLElBQUksS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsY0FBYyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLENBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxJQUFJLEtBQUcsSUFBSSxDQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDO1FBQUEsQ0FBQyxNQUFLLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sQ0FBQyxLQUFHLElBQUksQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsRUFBRSxDQUFDLFdBQVcsRUFBQztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sQ0FBQyxJQUFFLEVBQUU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxPQUFPO1FBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxPQUFPLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsTUFBTSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEtBQUssQ0FBQyxFQUFDLEVBQUUsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsYUFBYSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUs7UUFBQyxJQUFHLEtBQUssS0FBRyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsaUJBQWlCLEtBQUcsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEtBQUcsRUFBRSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksSUFBRSxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDO01BQUE7SUFBQyxDQUFDO0lBQUMsS0FBSSxFQUFFLENBQUMsWUFBWSxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLDBDQUEwQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLEVBQUUsR0FBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsT0FBTSxDQUFDLENBQUM7TUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGVBQWU7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLElBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQyxJQUFJLENBQUMsY0FBYyxFQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxjQUFjLEVBQUMsRUFBRSxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsRUFBQywwQkFBMEIsRUFBQyxJQUFJLENBQUMsS0FBSyxDQUFDLHdCQUF3QixLQUFHLENBQUMsR0FBQyxTQUFTLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDO01BQUE7TUFBQyxJQUFHLENBQUMsRUFBQztRQUFDLE9BQUssQ0FBQyxHQUFFO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQTtRQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxPQUFPLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLE9BQU8sR0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxhQUFhLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLEdBQUMsRUFBRSxFQUFDLENBQUMsRUFBRSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxNQUFJLE9BQU8sS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsSUFBRSxFQUFFLEdBQUMsRUFBRSxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsV0FBVyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFDLElBQUksS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEtBQUssSUFBRSxDQUFDLEdBQUMsRUFBRSxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxTQUFTLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxnQkFBZ0IsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsSUFBSSxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO1FBQUMsQ0FBQyxHQUFDLElBQUk7TUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSztRQUFDLElBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsU0FBUyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLFlBQVksS0FBRyxDQUFDLElBQUksRUFBQyxPQUFLLENBQUMsR0FBRTtVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUk7WUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSTtjQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFJO2dCQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztnQkFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO2NBQUE7WUFBQyxPQUFJLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQSxDQUFDLE1BQUssT0FBSyxDQUFDLEdBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQUssT0FBSyxDQUFDLEdBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsaUJBQWlCLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxFQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsU0FBQSxDQUFBLEVBQVU7TUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsS0FBSyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxLQUFLLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxLQUFLLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsTUFBTSxJQUFFLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxhQUFhO01BQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO1FBQ2x4K0IsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0FBQUEsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxNQUFNLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQzs7Ozs7QUNaaEk7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUMsTUFBTSxDQUFDLFFBQVEsS0FBRyxNQUFNLENBQUMsUUFBUSxHQUFDLEVBQUUsQ0FBQyxFQUFFLElBQUksQ0FBQyxZQUFVO0VBQUMsWUFBWTs7RUFBQyxJQUFJLENBQUMsR0FBQyxRQUFRLENBQUMsZUFBZTtJQUFDLENBQUMsR0FBQyxNQUFNO0lBQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsR0FBQyxPQUFPLEdBQUMsUUFBUTtRQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsSUFBSTtNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxNQUFNLENBQUM7TUFBQyxRQUFRLEVBQUMsVUFBVTtNQUFDLEdBQUcsRUFBQyxDQUFDO01BQUMsT0FBTyxFQUFDLE9BQU87TUFBQyxJQUFJLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQztVQUFDLENBQUMsRUFBQztRQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxFQUFDLEdBQUcsRUFBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxFQUFDLEdBQUcsRUFBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxHQUFHLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO0VBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO0lBQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLFVBQVU7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO0lBQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxHQUFDLElBQUksSUFBRSxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLFNBQVM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7RUFBQSxDQUFDO0FBQUEsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxNQUFNLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsImRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoXCIuY3VzdG9tLXNlbGVjdFwiKS5mb3JFYWNoKGN1c3RvbVNlbGVjdCA9PiB7XG5cdGNvbnN0IHNlbGVjdEJ0biA9IGN1c3RvbVNlbGVjdC5xdWVyeVNlbGVjdG9yKFwiLnNlbGVjdC1idXR0b25cIik7XG5cdGNvbnN0IHNlbGVjdGVkVmFsdWUgPSBjdXN0b21TZWxlY3QucXVlcnlTZWxlY3RvcihcIi5zZWxlY3RlZC12YWx1ZVwiKTtcblx0Y29uc3QgaGFuZGxlciA9IGZ1bmN0aW9uKGVsbSkge1xuXHRcdGNvbnN0IGN1c3RvbUNoYW5nZUV2ZW50ID0gbmV3IEN1c3RvbUV2ZW50KCdjdXN0b20tc2VsZWN0LWNoYW5nZScsIHtcblx0XHRcdGRldGFpbDoge1xuXHRcdFx0XHRzZWxlY3RlZE9wdGlvbjogZWxtXG5cdFx0XHR9XG5cdFx0fSk7XG5cdFx0c2VsZWN0ZWRWYWx1ZS50ZXh0Q29udGVudCA9IGVsbS50ZXh0Q29udGVudDtcblx0XHRjdXN0b21TZWxlY3QuY2xhc3NMaXN0LnJlbW92ZShcImFjdGl2ZVwiKTtcblx0XHRjdXN0b21TZWxlY3QuZGlzcGF0Y2hFdmVudChjdXN0b21DaGFuZ2VFdmVudCk7XG5cblx0fVxuXHRzZWxlY3RCdG4uYWRkRXZlbnRMaXN0ZW5lcihcImNsaWNrXCIsICgpID0+IHtcblx0XHRjdXN0b21TZWxlY3QuY2xhc3NMaXN0LnRvZ2dsZShcImFjdGl2ZVwiKTtcblx0XHRzZWxlY3RCdG4uc2V0QXR0cmlidXRlKFxuXHRcdFx0XCJhcmlhLWV4cGFuZGVkXCIsXG5cdFx0XHRzZWxlY3RCdG4uZ2V0QXR0cmlidXRlKFwiYXJpYS1leHBhbmRlZFwiKSA9PT0gXCJ0cnVlXCIgPyBcImZhbHNlXCIgOiBcInRydWVcIlxuXHRcdCk7XG5cdH0pO1xuXG5cdGN1c3RvbVNlbGVjdC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRpZiAoZS50YXJnZXQubWF0Y2hlcygnbGFiZWwnKSkge1xuXG5cdFx0XHRjb25zdCBhbGxJdGVtcyA9IGN1c3RvbVNlbGVjdC5xdWVyeVNlbGVjdG9yQWxsKCdsaScpO1xuXHRcdFx0YWxsSXRlbXMuZm9yRWFjaChpdGVtID0+IGl0ZW0uY2xhc3NMaXN0LnJlbW92ZSgnYWN0aXZlJykpO1xuXHRcdFx0Y29uc3QgY2xpY2tlZFBsYW4gPSBlLnRhcmdldC5jbG9zZXN0KCdsaScpO1xuXG5cdFx0XHRpZiAoY2xpY2tlZFBsYW4pIHtcblx0XHRcdFx0Y2xpY2tlZFBsYW4uY2xhc3NMaXN0LmFkZCgnYWN0aXZlJyk7XG5cdFx0XHRcdGhhbmRsZXIoY2xpY2tlZFBsYW4pO1xuXHRcdFx0fVxuXHRcdH1cblx0fSk7XG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoXCJjbGlja1wiLCAoZSkgPT4ge1xuXHRcdGlmICghY3VzdG9tU2VsZWN0LmNvbnRhaW5zKGUudGFyZ2V0KSkge1xuXHRcdFx0Y3VzdG9tU2VsZWN0LmNsYXNzTGlzdC5yZW1vdmUoXCJhY3RpdmVcIik7XG5cdFx0XHRzZWxlY3RCdG4uc2V0QXR0cmlidXRlKFwiYXJpYS1leHBhbmRlZFwiLCBcImZhbHNlXCIpO1xuXHRcdH1cblx0fSk7XG59KTsiLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG4gICAgLyoqXG4gICAgICogUmVmcmVzaCBMaWNlbnNlIGRhdGFcbiAgICAgKi9cbiAgICB2YXIgX2lzUmVmcmVzaGluZyA9IGZhbHNlO1xuICAgICQoJyN3cHItYWN0aW9uLXJlZnJlc2hfYWNjb3VudCcpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgaWYoIV9pc1JlZnJlc2hpbmcpe1xuICAgICAgICAgICAgdmFyIGJ1dHRvbiA9ICQodGhpcyk7XG4gICAgICAgICAgICB2YXIgYWNjb3VudCA9ICQoJyN3cHItYWNjb3VudC1kYXRhJyk7XG4gICAgICAgICAgICB2YXIgZXhwaXJlID0gJCgnI3dwci1leHBpcmF0aW9uLWRhdGEnKTtcblxuICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgX2lzUmVmcmVzaGluZyA9IHRydWU7XG4gICAgICAgICAgICBidXR0b24udHJpZ2dlciggJ2JsdXInICk7XG4gICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgIGV4cGlyZS5yZW1vdmVDbGFzcygnd3ByLWlzVmFsaWQgd3ByLWlzSW52YWxpZCcpO1xuXG4gICAgICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9yZWZyZXNoX2N1c3RvbWVyX2RhdGEnLFxuICAgICAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pc0hpZGRlbicpO1xuXG4gICAgICAgICAgICAgICAgICAgIGlmICggdHJ1ZSA9PT0gcmVzcG9uc2Uuc3VjY2VzcyApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGFjY291bnQuaHRtbChyZXNwb25zZS5kYXRhLmxpY2Vuc2VfdHlwZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICBleHBpcmUuYWRkQ2xhc3MocmVzcG9uc2UuZGF0YS5saWNlbnNlX2NsYXNzKS5odG1sKHJlc3BvbnNlLmRhdGEubGljZW5zZV9leHBpcmF0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaWNvbi1yZWZyZXNoIHdwci1pc0hpZGRlbicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWljb24tY2hlY2snKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDI1MCk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaWNvbi1yZWZyZXNoIHdwci1pc0hpZGRlbicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWljb24tY2xvc2UnKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0sIDI1MCk7XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoe29uQ29tcGxldGU6ZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBfaXNSZWZyZXNoaW5nID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgICAgICB9fSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonKz13cHItaXNIaWRkZW4nfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWljb24tY2hlY2snfX0sIDAuMjUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWljb24tY2xvc2UnfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jys9d3ByLWljb24tcmVmcmVzaCd9fSwgMC4yNSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonLT13cHItaXNIaWRkZW4nfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICA7XG4gICAgICAgICAgICAgICAgICAgIH0sIDIwMDApO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogU2F2ZSBUb2dnbGUgb3B0aW9uIHZhbHVlcyBvbiBjaGFuZ2VcbiAgICAgKi9cbiAgICAkKCcud3ByLXJhZGlvIGlucHV0W3R5cGU9Y2hlY2tib3hdJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICB2YXIgbmFtZSAgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG4gICAgICAgIHZhciB2YWx1ZSA9ICQodGhpcykucHJvcCgnY2hlY2tlZCcpID8gMSA6IDA7XG5cblx0XHR2YXIgZXhjbHVkZWQgPSBbICdjbG91ZGZsYXJlX2F1dG9fc2V0dGluZ3MnLCAnY2xvdWRmbGFyZV9kZXZtb2RlJywgJ2FuYWx5dGljc19lbmFibGVkJyBdO1xuXHRcdGlmICggZXhjbHVkZWQuaW5kZXhPZiggbmFtZSApID49IDAgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X3RvZ2dsZV9vcHRpb24nLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuICAgICAgICAgICAgICAgIG9wdGlvbjoge1xuICAgICAgICAgICAgICAgICAgICBuYW1lOiBuYW1lLFxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTogdmFsdWVcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgZnVuY3Rpb24ocmVzcG9uc2UpIHt9XG4gICAgICAgICk7XG5cdH0pO1xuXG5cdC8qKlxuICAgICAqIFNhdmUgZW5hYmxlIENQQ1NTIGZvciBtb2JpbGVzIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIENQQ1NTIGJ0biBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItaGlkZS1vbi1jbGljaycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLXNob3ctb24tY2xpY2snKS5zaG93KCk7XG5cdFx0XHRcdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG5cbiAgICAvKipcbiAgICAgKiBTYXZlIGVuYWJsZSBHb29nbGUgRm9udHMgT3B0aW1pemF0aW9uIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIENQQ1NTIGJ0biBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItaGlkZS1vbi1jbGljaycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLXNob3ctb24tY2xpY2snKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI21pbmlmeV9nb29nbGVfZm9udHMnKS52YWwoMSk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9KTtcblxuICAgICQoICcjcm9ja2V0LWRpc21pc3MtcHJvbW90aW9uJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2Rpc21pc3NfcHJvbW8nLFxuICAgICAgICAgICAgICAgIG5vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdCQoJyNyb2NrZXQtcHJvbW8tYmFubmVyJykuaGlkZSggJ3Nsb3cnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9ICk7XG5cbiAgICAkKCAnI3JvY2tldC1kaXNtaXNzLXJlbmV3YWwnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZGlzbWlzc19yZW5ld2FsJyxcbiAgICAgICAgICAgICAgICBub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQkKCcjcm9ja2V0LXJlbmV3YWwtYmFubmVyJykuaGlkZSggJ3Nsb3cnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9ICk7XG5cdCQoICcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbGlzdCcgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdCQoJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1tc2cnKS5odG1sKCcnKTtcblx0XHQkLmFqYXgoe1xuXHRcdFx0dXJsOiByb2NrZXRfYWpheF9kYXRhLnJlc3RfdXJsLFxuXHRcdFx0YmVmb3JlU2VuZDogZnVuY3Rpb24gKCB4aHIgKSB7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnWC1XUC1Ob25jZScsIHJvY2tldF9hamF4X2RhdGEucmVzdF9ub25jZSApO1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ0FjY2VwdCcsICdhcHBsaWNhdGlvbi9qc29uLCAqLyo7cT0wLjEnICk7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnQ29udGVudC1UeXBlJywgJ2FwcGxpY2F0aW9uL2pzb24nICk7XG5cdFx0XHR9LFxuXHRcdFx0bWV0aG9kOiBcIlBVVFwiLFxuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2VzKSB7XG5cdFx0XHRcdGxldCBleGNsdXNpb25fbXNnX2NvbnRhaW5lciA9ICQoJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1tc2cnKTtcblx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuaHRtbCgnJyk7XG5cdFx0XHRcdGlmICggdW5kZWZpbmVkICE9PSByZXNwb25zZXNbJ3N1Y2Nlc3MnXSApIHtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8ZGl2IGNsYXNzPVwibm90aWNlIG5vdGljZS1lcnJvclwiPicgKyByZXNwb25zZXNbJ21lc3NhZ2UnXSArICc8L2Rpdj4nICk7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cdFx0XHRcdE9iamVjdC5rZXlzKCByZXNwb25zZXMgKS5mb3JFYWNoKCggcmVzcG9uc2Vfa2V5ICkgPT4ge1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxzdHJvbmc+JyArIHJlc3BvbnNlX2tleSArICc6IDwvc3Ryb25nPicgKTtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoIHJlc3BvbnNlc1tyZXNwb25zZV9rZXldWydtZXNzYWdlJ10gKTtcblx0XHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5hcHBlbmQoICc8YnI+JyApO1xuXHRcdFx0XHR9KTtcblx0XHRcdH1cblx0XHR9KTtcblx0fSApO1xuXG4gICAgLyoqXG4gICAgICogRW5hYmxlIG1vYmlsZSBjYWNoZSBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2VuYWJsZV9tb2JpbGVfY2FjaGUnLFxuICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdC8vIEhpZGUgTW9iaWxlIGNhY2hlIGVuYWJsZSBidXR0b24gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnI3dwcl9tb2JpbGVfY2FjaGVfZGVmYXVsdCcpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcjd3ByX21vYmlsZV9jYWNoZV9yZXNwb25zZScpLnNob3coKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI3dwcl9lbmFibGVfbW9iaWxlX2NhY2hlJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAgICAgICAgICAgICAvLyBTZXQgdmFsdWVzIG9mIG1vYmlsZSBjYWNoZSBhbmQgc2VwYXJhdGUgY2FjaGUgZmlsZXMgZm9yIG1vYmlsZXMgb3B0aW9uIHRvIDEuXG4gICAgICAgICAgICAgICAgICAgICQoJyNjYWNoZV9tb2JpbGUnKS52YWwoMSk7XG4gICAgICAgICAgICAgICAgICAgICQoJyNkb19jYWNoaW5nX21vYmlsZV9maWxlcycpLnZhbCgxKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xufSk7XG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbigpIHtcblx0Y29uc3QgYW5hbHl0aWNzQ2hlY2tib3ggPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnYW5hbHl0aWNzX2VuYWJsZWQnKTtcblxuXHRpZiAoYW5hbHl0aWNzQ2hlY2tib3gpIHtcblx0XHRhbmFseXRpY3NDaGVja2JveC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHRcdGNvbnN0IGlzQ2hlY2tlZCA9IHRoaXMuY2hlY2tlZDtcblxuXHRcdFx0ZmV0Y2goYWpheHVybCwge1xuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0aGVhZGVyczoge1xuXHRcdFx0XHRcdCdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJyxcblx0XHRcdFx0fSxcblx0XHRcdFx0Ym9keTogbmV3IFVSTFNlYXJjaFBhcmFtcyh7XG5cdFx0XHRcdFx0YWN0aW9uOiAncm9ja2V0X3RvZ2dsZV9vcHRpbicsXG5cdFx0XHRcdFx0dmFsdWU6IGlzQ2hlY2tlZCA/IDEgOiAwLFxuXHRcdFx0XHRcdF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuXHRcdFx0XHR9KVxuXHRcdFx0fSk7XG5cdFx0fSk7XG5cdH1cbn0pOyIsIi8vIEFkZCBncmVlbnNvY2sgbGliIGZvciBhbmltYXRpb25zXHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9wbHVnaW5zL0NTU1BsdWdpbi5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svcGx1Z2lucy9TY3JvbGxUb1BsdWdpbi5taW4uanMnO1xyXG5cclxuLy8gQWRkIHNjcmlwdHNcclxuaW1wb3J0ICcuLi9nbG9iYWwvcGFnZU1hbmFnZXIuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9tYWluLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvZmllbGRzLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvYmVhY29uLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvYWpheC5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL3JvY2tldGNkbi5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL2NvdW50ZG93bi5qcyc7IiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuICAgIGlmICgnQmVhY29uJyBpbiB3aW5kb3cpIHtcbiAgICAgICAgLyoqXG4gICAgICAgICAqIFNob3cgYmVhY29ucyBvbiBidXR0b24gXCJoZWxwXCIgY2xpY2tcbiAgICAgICAgICovXG4gICAgICAgIHZhciAkaGVscCA9ICQoJy53cHItaW5mb0FjdGlvbi0taGVscCcpO1xuICAgICAgICAkaGVscC5vbignY2xpY2snLCBmdW5jdGlvbihlKXtcbiAgICAgICAgICAgIHZhciBpZHMgPSAkKHRoaXMpLmRhdGEoJ2JlYWNvbi1pZCcpO1xuICAgICAgICAgICAgdmFyIGJ1dHRvbiA9ICQodGhpcykuZGF0YSgnd3ByX3RyYWNrX2J1dHRvbicpIHx8ICdCZWFjb24gSGVscCc7XG4gICAgICAgICAgICB2YXIgY29udGV4dCA9ICQodGhpcykuZGF0YSgnd3ByX3RyYWNrX2NvbnRleHQnKSB8fCAnU2V0dGluZ3MnO1xuICAgICAgICAgICAgXG4gICAgICAgICAgICAvLyBUcmFjayB3aXRoIE1peFBhbmVsIEpTIFNES1xuICAgICAgICAgICAgd3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCk7XG4gICAgICAgICAgICBcbiAgICAgICAgICAgIC8vIENvbnRpbnVlIHdpdGggZXhpc3RpbmcgYmVhY29uIGZ1bmN0aW9uYWxpdHlcbiAgICAgICAgICAgIHdwckNhbGxCZWFjb24oaWRzKTtcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgZnVuY3Rpb24gd3ByQ2FsbEJlYWNvbihhSUQpe1xuICAgICAgICAgICAgYUlEID0gYUlELnNwbGl0KCcsJyk7XG4gICAgICAgICAgICBpZiAoIGFJRC5sZW5ndGggPT09IDAgKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgaWYgKCBhSUQubGVuZ3RoID4gMSApIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcInN1Z2dlc3RcIiwgYUlEKTtcblxuICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcIm9wZW5cIik7XG4gICAgICAgICAgICAgICAgICAgIH0sIDIwMCk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LkJlYWNvbihcImFydGljbGVcIiwgYUlELnRvU3RyaW5nKCkpO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICB9XG4gICAgfVxuICAgIFxuICAgIC8vIE1peFBhbmVsIHRyYWNraW5nIGZ1bmN0aW9uXG4gICAgZnVuY3Rpb24gd3ByVHJhY2tIZWxwQnV0dG9uKGJ1dHRvbiwgY29udGV4dCkge1xuICAgICAgICBpZiAodHlwZW9mIG1peHBhbmVsICE9PSAndW5kZWZpbmVkJyAmJiBtaXhwYW5lbC50cmFjaykge1xuICAgICAgICAgICAgLy8gQ2hlY2sgaWYgdXNlciBoYXMgb3B0ZWQgaW4gdXNpbmcgbG9jYWxpemVkIGRhdGFcbiAgICAgICAgICAgIGlmICh0eXBlb2Ygcm9ja2V0X21peHBhbmVsX2RhdGEgPT09ICd1bmRlZmluZWQnIHx8ICFyb2NrZXRfbWl4cGFuZWxfZGF0YS5vcHRpbl9lbmFibGVkIHx8IHJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgPT09ICcwJykge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIFxuICAgICAgICAgICAgbWl4cGFuZWwudHJhY2soJ1dQTSBCdXR0b24gQ2xpY2tlZCcsIHtcbiAgICAgICAgICAgICAgICAnYnV0dG9uJzogYnV0dG9uLFxuICAgICAgICAgICAgICAgICdicmFuZCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuICAgICAgICAgICAgICAgICdwcm9kdWN0Jzogcm9ja2V0X21peHBhbmVsX2RhdGEucHJvZHVjdCxcbiAgICAgICAgICAgICAgICAnY29udGV4dCc6IGNvbnRleHQsXG4gICAgICAgICAgICAgICAgJ3BhdGgnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wYXRoXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBcbiAgICAvLyBNYWtlIGZ1bmN0aW9uIGdsb2JhbGx5IGF2YWlsYWJsZVxuICAgIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPSB3cHJUcmFja0hlbHBCdXR0b247XG59KTtcbiIsImZ1bmN0aW9uIGdldFRpbWVSZW1haW5pbmcoZW5kdGltZSl7XG4gICAgY29uc3Qgc3RhcnQgPSBEYXRlLm5vdygpO1xuICAgIGNvbnN0IHRvdGFsID0gKGVuZHRpbWUgKiAxMDAwKSAtIHN0YXJ0O1xuICAgIGNvbnN0IHNlY29uZHMgPSBNYXRoLmZsb29yKCAodG90YWwvMTAwMCkgJSA2MCApO1xuICAgIGNvbnN0IG1pbnV0ZXMgPSBNYXRoLmZsb29yKCAodG90YWwvMTAwMC82MCkgJSA2MCApO1xuICAgIGNvbnN0IGhvdXJzID0gTWF0aC5mbG9vciggKHRvdGFsLygxMDAwKjYwKjYwKSkgJSAyNCApO1xuICAgIGNvbnN0IGRheXMgPSBNYXRoLmZsb29yKCB0b3RhbC8oMTAwMCo2MCo2MCoyNCkgKTtcblxuICAgIHJldHVybiB7XG4gICAgICAgIHRvdGFsLFxuICAgICAgICBkYXlzLFxuICAgICAgICBob3VycyxcbiAgICAgICAgbWludXRlcyxcbiAgICAgICAgc2Vjb25kc1xuICAgIH07XG59XG5cbmZ1bmN0aW9uIGluaXRpYWxpemVDbG9jayhpZCwgZW5kdGltZSkge1xuICAgIGNvbnN0IGNsb2NrID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpO1xuXG4gICAgaWYgKGNsb2NrID09PSBudWxsKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBkYXlzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLWRheXMnKTtcbiAgICBjb25zdCBob3Vyc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1ob3VycycpO1xuICAgIGNvbnN0IG1pbnV0ZXNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tbWludXRlcycpO1xuICAgIGNvbnN0IHNlY29uZHNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tc2Vjb25kcycpO1xuXG4gICAgZnVuY3Rpb24gdXBkYXRlQ2xvY2soKSB7XG4gICAgICAgIGNvbnN0IHQgPSBnZXRUaW1lUmVtYWluaW5nKGVuZHRpbWUpO1xuXG4gICAgICAgIGlmICh0LnRvdGFsIDwgMCkge1xuICAgICAgICAgICAgY2xlYXJJbnRlcnZhbCh0aW1laW50ZXJ2YWwpO1xuXG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBkYXlzU3Bhbi5pbm5lckhUTUwgPSB0LmRheXM7XG4gICAgICAgIGhvdXJzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5ob3Vycykuc2xpY2UoLTIpO1xuICAgICAgICBtaW51dGVzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5taW51dGVzKS5zbGljZSgtMik7XG4gICAgICAgIHNlY29uZHNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0LnNlY29uZHMpLnNsaWNlKC0yKTtcbiAgICB9XG5cbiAgICB1cGRhdGVDbG9jaygpO1xuICAgIGNvbnN0IHRpbWVpbnRlcnZhbCA9IHNldEludGVydmFsKHVwZGF0ZUNsb2NrLCAxMDAwKTtcbn1cblxuZnVuY3Rpb24gcnVjc3NUaW1lcihpZCwgZW5kdGltZSkge1xuXHRjb25zdCB0aW1lciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGlkKTtcblx0Y29uc3Qgbm90aWNlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JvY2tldC1ub3RpY2Utc2Fhcy1wcm9jZXNzaW5nJyk7XG5cdGNvbnN0IHN1Y2Nlc3MgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1zYWFzLXN1Y2Nlc3MnKTtcblxuXHRpZiAodGltZXIgPT09IG51bGwpIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVUaW1lcigpIHtcblx0XHRjb25zdCBzdGFydCA9IERhdGUubm93KCk7XG5cdFx0Y29uc3QgcmVtYWluaW5nID0gTWF0aC5mbG9vciggKCAoZW5kdGltZSAqIDEwMDApIC0gc3RhcnQgKSAvIDEwMDAgKTtcblxuXHRcdGlmIChyZW1haW5pbmcgPD0gMCkge1xuXHRcdFx0Y2xlYXJJbnRlcnZhbCh0aW1lckludGVydmFsKTtcblxuXHRcdFx0aWYgKG5vdGljZSAhPT0gbnVsbCkge1xuXHRcdFx0XHRub3RpY2UuY2xhc3NMaXN0LmFkZCgnaGlkZGVuJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmIChzdWNjZXNzICE9PSBudWxsKSB7XG5cdFx0XHRcdHN1Y2Nlc3MuY2xhc3NMaXN0LnJlbW92ZSgnaGlkZGVuJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmICggcm9ja2V0X2FqYXhfZGF0YS5jcm9uX2Rpc2FibGVkICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IGRhdGEgPSBuZXcgRm9ybURhdGEoKTtcblxuXHRcdFx0ZGF0YS5hcHBlbmQoICdhY3Rpb24nLCAncm9ja2V0X3NwYXduX2Nyb24nICk7XG5cdFx0XHRkYXRhLmFwcGVuZCggJ25vbmNlJywgcm9ja2V0X2FqYXhfZGF0YS5ub25jZSApO1xuXG5cdFx0XHRmZXRjaCggYWpheHVybCwge1xuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0Y3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG5cdFx0XHRcdGJvZHk6IGRhdGFcblx0XHRcdH0gKTtcblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRpbWVyLmlubmVySFRNTCA9IHJlbWFpbmluZztcblx0fVxuXG5cdHVwZGF0ZVRpbWVyKCk7XG5cdGNvbnN0IHRpbWVySW50ZXJ2YWwgPSBzZXRJbnRlcnZhbCggdXBkYXRlVGltZXIsIDEwMDApO1xufVxuXG5pZiAoIURhdGUubm93KSB7XG4gICAgRGF0ZS5ub3cgPSBmdW5jdGlvbiBub3coKSB7XG4gICAgICByZXR1cm4gbmV3IERhdGUoKS5nZXRUaW1lKCk7XG4gICAgfTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLnByb21vX2VuZCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBpbml0aWFsaXplQ2xvY2soJ3JvY2tldC1wcm9tby1jb3VudGRvd24nLCByb2NrZXRfYWpheF9kYXRhLnByb21vX2VuZCk7XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5saWNlbnNlX2V4cGlyYXRpb24gIT09ICd1bmRlZmluZWQnKSB7XG4gICAgaW5pdGlhbGl6ZUNsb2NrKCdyb2NrZXQtcmVuZXctY291bnRkb3duJywgcm9ja2V0X2FqYXhfZGF0YS5saWNlbnNlX2V4cGlyYXRpb24pO1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEubm90aWNlX2VuZF90aW1lICE9PSAndW5kZWZpbmVkJykge1xuICAgIHJ1Y3NzVGltZXIoJ3JvY2tldC1ydWNzcy10aW1lcicsIHJvY2tldF9hamF4X2RhdGEubm90aWNlX2VuZF90aW1lKTtcbn0iLCJpbXBvcnQgJy4uL2N1c3RvbS9jdXN0b20tc2VsZWN0LmpzJztcblxudmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXG5cbiAgICAvKioqXG4gICAgKiBDaGVjayBwYXJlbnQgLyBzaG93IGNoaWxkcmVuXG4gICAgKioqL1xuXG5cdGZ1bmN0aW9uIHdwclNob3dDaGlsZHJlbihhRWxlbSl7XG5cdFx0dmFyIHBhcmVudElkLCAkY2hpbGRyZW47XG5cblx0XHRhRWxlbSAgICAgPSAkKCBhRWxlbSApO1xuXHRcdHBhcmVudElkICA9IGFFbGVtLmF0dHIoJ2lkJyk7XG5cdFx0JGNoaWxkcmVuID0gJCgnW2RhdGEtcGFyZW50PVwiJyArIHBhcmVudElkICsgJ1wiXScpO1xuXG5cdFx0Ly8gVGVzdCBjaGVjayBmb3Igc3dpdGNoXG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRpZiAoICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0XHRcdHZhciBpZCA9ICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpO1xuXG5cdFx0XHRcdFx0JCgnW2RhdGEtcGFyZW50PVwiJyArIGlkICsgJ1wiXScpLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXHRcdH1cblx0XHRlbHNle1xuXHRcdFx0JGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR2YXIgaWQgPSAkKHRoaXMpLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKTtcblxuXHRcdFx0XHQkKCdbZGF0YS1wYXJlbnQ9XCInICsgaWQgKyAnXCJdJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdH0pO1xuXHRcdH1cblx0fVxuXG4gICAgLyoqXG4gICAgICogVGVsbCBpZiB0aGUgZ2l2ZW4gY2hpbGQgZmllbGQgaGFzIGFuIGFjdGl2ZSBwYXJlbnQgZmllbGQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0gIG9iamVjdCAkZmllbGQgQSBqUXVlcnkgb2JqZWN0IG9mIGEgXCIud3ByLWZpZWxkXCIgZmllbGQuXG4gICAgICogQHJldHVybiBib29sfG51bGxcbiAgICAgKi9cbiAgICBmdW5jdGlvbiB3cHJJc1BhcmVudEFjdGl2ZSggJGZpZWxkICkge1xuICAgICAgICB2YXIgJHBhcmVudDtcblxuICAgICAgICBpZiAoICEgJGZpZWxkLmxlbmd0aCApIHtcbiAgICAgICAgICAgIC8vIMKvXFxfKOODhClfL8KvXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgICRwYXJlbnQgPSAkZmllbGQuZGF0YSggJ3BhcmVudCcgKTtcblxuICAgICAgICBpZiAoIHR5cGVvZiAkcGFyZW50ICE9PSAnc3RyaW5nJyApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICRwYXJlbnQucmVwbGFjZSggL15cXHMrfFxccyskL2csICcnICk7XG5cbiAgICAgICAgaWYgKCAnJyA9PT0gJHBhcmVudCApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICQoICcjJyArICRwYXJlbnQgKTtcblxuICAgICAgICBpZiAoICEgJHBhcmVudC5sZW5ndGggKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIG1pc3Npbmc6IGxldCdzIGNvbnNpZGVyIGl0J3Mgbm90IGFjdGl2ZSB0aGVuLlxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCAhICRwYXJlbnQuaXMoICc6Y2hlY2tlZCcgKSAmJiAkcGFyZW50LmlzKCdpbnB1dCcpKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIGNoZWNrYm94IGFuZCBub3QgY2hlY2tlZDogZG9uJ3QgZGlzcGxheSB0aGUgZmllbGQgdGhlbi5cbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfVxuXG5cdFx0aWYgKCAhJHBhcmVudC5oYXNDbGFzcygncmFkaW8tYWN0aXZlJykgJiYgJHBhcmVudC5pcygnYnV0dG9uJykpIHtcblx0XHRcdC8vIFRoaXMgZmllbGQncyBwYXJlbnQgYnV0dG9uIGFuZCBpcyBub3QgYWN0aXZlOiBkb24ndCBkaXNwbGF5IHRoZSBmaWVsZCB0aGVuLlxuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cbiAgICAgICAgLy8gR28gcmVjdXJzaXZlIHRvIHRoZSBsYXN0IHBhcmVudC5cbiAgICAgICAgcmV0dXJuIHdwcklzUGFyZW50QWN0aXZlKCAkcGFyZW50LmNsb3Nlc3QoICcud3ByLWZpZWxkJyApICk7XG4gICAgfVxuXG5cdC8qKlxuXHQgKiBNYXNrcyBzZW5zaXRpdmUgaW5mb3JtYXRpb24gaW4gYW4gaW5wdXQgZmllbGQgYnkgcmVwbGFjaW5nIGFsbCBidXQgdGhlIGxhc3QgNCBjaGFyYWN0ZXJzIHdpdGggYXN0ZXJpc2tzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gaWRfc2VsZWN0b3IgLSBUaGUgSUQgb2YgdGhlIGlucHV0IGZpZWxkIHRvIGJlIG1hc2tlZC5cblx0ICogQHJldHVybnMge3ZvaWR9IC0gTW9kaWZpZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIGluLXBsYWNlLlxuXHQgKlxuXHQgKiBAZXhhbXBsZVxuXHQgKiAvLyBIVE1MOiA8aW5wdXQgdHlwZT1cInRleHRcIiBpZD1cImNyZWRpdENhcmRJbnB1dFwiIHZhbHVlPVwiMTIzNDU2Nzg5MDEyMzQ1NlwiPlxuXHQgKiBtYXNrRmllbGQoJ2NyZWRpdENhcmRJbnB1dCcpO1xuXHQgKiAvLyBSZXN1bHQ6IFVwZGF0ZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIHRvICcqKioqKioqKioqKiozNDU2Jy5cblx0ICovXG5cdGZ1bmN0aW9uIG1hc2tGaWVsZChwcm94eV9zZWxlY3RvciwgY29uY3JldGVfc2VsZWN0b3IpIHtcblx0XHR2YXIgY29uY3JldGUgPSB7XG5cdFx0XHQndmFsJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCksXG5cdFx0XHQnbGVuZ3RoJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCkubGVuZ3RoXG5cdFx0fVxuXG5cdFx0aWYgKGNvbmNyZXRlLmxlbmd0aCA+IDQpIHtcblxuXHRcdFx0dmFyIGhpZGRlblBhcnQgPSAnXFx1MjAyMicucmVwZWF0KE1hdGgubWF4KDAsIGNvbmNyZXRlLmxlbmd0aCAtIDQpKTtcblx0XHRcdHZhciB2aXNpYmxlUGFydCA9IGNvbmNyZXRlLnZhbC5zbGljZSgtNCk7XG5cblx0XHRcdC8vIENvbWJpbmUgdGhlIGhpZGRlbiBhbmQgdmlzaWJsZSBwYXJ0c1xuXHRcdFx0dmFyIG1hc2tlZFZhbHVlID0gaGlkZGVuUGFydCArIHZpc2libGVQYXJ0O1xuXG5cdFx0XHRwcm94eV9zZWxlY3Rvci52YWwobWFza2VkVmFsdWUpO1xuXHRcdH1cblx0XHQvLyBFbnN1cmUgZXZlbnRzIGFyZSBub3QgYWRkZWQgbW9yZSB0aGFuIG9uY2Vcblx0XHRpZiAoIXByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJykpIHtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdpbnB1dCcsIGhhbmRsZUlucHV0KTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdmb2N1cycsIGhhbmRsZUZvY3VzKTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJywgdHJ1ZSk7XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlIHRoZSBpbnB1dCBldmVudFxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGhhbmRsZUlucHV0KCkge1xuXHRcdFx0dmFyIHByb3h5VmFsdWUgPSBwcm94eV9zZWxlY3Rvci52YWwoKTtcblx0XHRcdGlmIChwcm94eVZhbHVlLmluZGV4T2YoJ1xcdTIwMjInKSA9PT0gLTEpIHtcblx0XHRcdFx0Y29uY3JldGVfc2VsZWN0b3IudmFsKHByb3h5VmFsdWUpO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIEhhbmRsZSB0aGUgZm9jdXMgZXZlbnRcblx0XHQgKi9cblx0XHRmdW5jdGlvbiBoYW5kbGVGb2N1cygpIHtcblx0XHRcdHZhciBjb25jcmV0ZV92YWx1ZSA9IGNvbmNyZXRlX3NlbGVjdG9yLnZhbCgpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3IudmFsKGNvbmNyZXRlX3ZhbHVlKTtcblx0XHR9XG5cblx0fVxuXG5cdFx0Ly8gVXBkYXRlIHRoZSBjb25jcmV0ZSBmaWVsZCB3aGVuIHRoZSBwcm94eSBpcyB1cGRhdGVkLlxuXG5cblx0bWFza0ZpZWxkKCQoJyNjbG91ZGZsYXJlX2FwaV9rZXlfbWFzaycpLCAkKCcjY2xvdWRmbGFyZV9hcGlfa2V5JykpO1xuXHRtYXNrRmllbGQoJCgnI2Nsb3VkZmxhcmVfem9uZV9pZF9tYXNrJyksICQoJyNjbG91ZGZsYXJlX3pvbmVfaWQnKSk7XG5cblx0Ly8gRGlzcGxheS9IaWRlIGNoaWxkcmVuIGZpZWxkcyBvbiBjaGVja2JveCBjaGFuZ2UuXG4gICAgJCggJy53cHItaXNQYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nICkub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAvLyBPbiBwYWdlIGxvYWQsIGRpc3BsYXkgdGhlIGFjdGl2ZSBmaWVsZHMuXG4gICAgJCggJy53cHItZmllbGQtLWNoaWxkcmVuJyApLmVhY2goIGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgJGZpZWxkID0gJCggdGhpcyApO1xuXG4gICAgICAgIGlmICggd3BySXNQYXJlbnRBY3RpdmUoICRmaWVsZCApICkge1xuICAgICAgICAgICAgJGZpZWxkLmFkZENsYXNzKCAnd3ByLWlzT3BlbicgKTtcbiAgICAgICAgfVxuICAgIH0gKTtcblxuXG5cblxuICAgIC8qKipcbiAgICAqIFdhcm5pbmcgZmllbGRzXG4gICAgKioqL1xuXG4gICAgdmFyICR3YXJuaW5nUGFyZW50ID0gJCgnLndwci1maWVsZC0tcGFyZW50Jyk7XG4gICAgdmFyICR3YXJuaW5nUGFyZW50SW5wdXQgPSAkKCcud3ByLWZpZWxkLS1wYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nKTtcblxuICAgIC8vIElmIGFscmVhZHkgY2hlY2tlZFxuICAgICR3YXJuaW5nUGFyZW50SW5wdXQuZWFjaChmdW5jdGlvbigpe1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAkd2FybmluZ1BhcmVudC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHdwclNob3dXYXJuaW5nKCQodGhpcykpO1xuICAgIH0pO1xuXG4gICAgZnVuY3Rpb24gd3ByU2hvd1dhcm5pbmcoYUVsZW0pe1xuICAgICAgICB2YXIgJHdhcm5pbmdGaWVsZCA9IGFFbGVtLm5leHQoJy53cHItZmllbGRXYXJuaW5nJyksXG4gICAgICAgICAgICAkdGhpc0NoZWNrYm94ID0gYUVsZW0uZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKSxcbiAgICAgICAgICAgICRuZXh0V2FybmluZyA9IGFFbGVtLnBhcmVudCgpLm5leHQoJy53cHItd2FybmluZ0NvbnRhaW5lcicpLFxuICAgICAgICAgICAgJG5leHRGaWVsZHMgPSAkbmV4dFdhcm5pbmcuZmluZCgnLndwci1maWVsZCcpLFxuICAgICAgICAgICAgcGFyZW50SWQgPSBhRWxlbS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyksXG4gICAgICAgICAgICAkY2hpbGRyZW4gPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgcGFyZW50SWQgKyAnXCJdJylcbiAgICAgICAgO1xuXG4gICAgICAgIC8vIENoZWNrIHdhcm5pbmcgcGFyZW50XG4gICAgICAgIGlmKCR0aGlzQ2hlY2tib3guaXMoJzpjaGVja2VkJykpe1xuICAgICAgICAgICAgJHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuICAgICAgICAgICAgJHRoaXNDaGVja2JveC5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgYUVsZW0udHJpZ2dlcignY2hhbmdlJyk7XG5cblxuICAgICAgICAgICAgdmFyICR3YXJuaW5nQnV0dG9uID0gJHdhcm5pbmdGaWVsZC5maW5kKCcud3ByLWJ1dHRvbicpO1xuXG4gICAgICAgICAgICAvLyBWYWxpZGF0ZSB0aGUgd2FybmluZ1xuICAgICAgICAgICAgJHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAkdGhpc0NoZWNrYm94LnByb3AoJ2NoZWNrZWQnLCB0cnVlKTtcbiAgICAgICAgICAgICAgICAkd2FybmluZ0ZpZWxkLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgICAgICAgICAgJGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cbiAgICAgICAgICAgICAgICAvLyBJZiBuZXh0IGVsZW0gPSBkaXNhYmxlZFxuICAgICAgICAgICAgICAgIGlmKCRuZXh0V2FybmluZy5sZW5ndGggPiAwKXtcbiAgICAgICAgICAgICAgICAgICAgJG5leHRGaWVsZHMucmVtb3ZlQ2xhc3MoJ3dwci1pc0Rpc2FibGVkJyk7XG4gICAgICAgICAgICAgICAgICAgICRuZXh0RmllbGRzLmZpbmQoJ2lucHV0JykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICRuZXh0RmllbGRzLmFkZENsYXNzKCd3cHItaXNEaXNhYmxlZCcpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgJGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDTkFNRVMgYWRkL3JlbW92ZSBsaW5lc1xuICAgICAqL1xuICAgICQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLW11bHRpcGxlLWNsb3NlJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKHRoaXMpLnBhcmVudCgpLnNsaWRlVXAoICdzbG93JyAsIGZ1bmN0aW9uKCl7JCh0aGlzKS5yZW1vdmUoKTsgfSApO1xuXHR9ICk7XG5cblx0JCgnLndwci1idXR0b24tLWFkZE11bHRpJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgJCgkKCcjd3ByLWNuYW1lLW1vZGVsJykuaHRtbCgpKS5hcHBlbmRUbygnI3dwci1jbmFtZXMtbGlzdCcpO1xuICAgIH0pO1xuXG5cdC8qKipcblx0ICogV3ByIFJhZGlvIGJ1dHRvblxuXHQgKioqL1xuXHR2YXIgZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRpZigkKHRoaXMpLmhhc0NsYXNzKCdyYWRpby1hY3RpdmUnKSl7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHZhciAkcGFyZW50ID0gJCh0aGlzKS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJykucmVtb3ZlQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItZmllbGRXYXJuaW5nJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkKHRoaXMpLmFkZENsYXNzKCdyYWRpby1hY3RpdmUnKTtcblx0XHR3cHJTaG93UmFkaW9XYXJuaW5nKCQodGhpcykpO1xuXG5cdH0gKTtcblxuXG5cdGZ1bmN0aW9uIHdwclNob3dSYWRpb1dhcm5pbmcoJGVsbSl7XG5cdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cdFx0JGVsbS50cmlnZ2VyKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgWyAkZWxtIF0gKTtcblx0XHRpZiAoISRlbG0uaGFzQ2xhc3MoJ2hhcy13YXJuaW5nJykgfHwgZGlzYWJsZV9yYWRpb193YXJuaW5nKSB7XG5cdFx0XHR3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKTtcblx0XHRcdCRlbG0udHJpZ2dlciggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgWyAkZWxtIF0gKTtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyICR3YXJuaW5nRmllbGQgPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgJGVsbS5hdHRyKCdpZCcpICsgJ1wiXS53cHItZmllbGRXYXJuaW5nJyk7XG5cdFx0JHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdHZhciAkd2FybmluZ0J1dHRvbiA9ICR3YXJuaW5nRmllbGQuZmluZCgnLndwci1idXR0b24nKTtcblxuXHRcdC8vIFZhbGlkYXRlIHRoZSB3YXJuaW5nXG5cdFx0JHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcblx0XHRcdCR3YXJuaW5nRmllbGQucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pO1xuXHRcdFx0JGVsbS50cmlnZ2VyKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBbICRlbG0gXSApO1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH0pO1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSkge1xuXHRcdHZhciAkcGFyZW50ID0gJGVsbS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHR2YXIgJGNoaWxkcmVuID0gJCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyW2RhdGEtcGFyZW50PVwiJyArICRlbG0uYXR0cignaWQnKSArICdcIl0nKTtcblx0XHQkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0fVxuXG5cdC8qKipcblx0ICogV3ByIE9wdGltaXplIENzcyBEZWxpdmVyeSBGaWVsZFxuXHQgKioqL1xuXHR2YXIgcnVjc3NBY3RpdmUgPSBwYXJzZUludCgkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoKSk7XG5cblx0JCggXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCAud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvblwiIClcblx0XHQub24oIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIGZ1bmN0aW9uKCBldmVudCwgJGVsbSApIHtcblx0XHRcdHRvZ2dsZUFjdGl2ZU9wdGltaXplQ3NzRGVsaXZlcnlNZXRob2QoJGVsbSk7XG5cdFx0fSk7XG5cblx0JChcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlcIikub24oXCJjaGFuZ2VcIiwgZnVuY3Rpb24oKXtcblx0XHRpZiggJCh0aGlzKS5pcyhcIjpub3QoOmNoZWNrZWQpXCIpICl7XG5cdFx0XHRkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpO1xuXHRcdH1lbHNle1xuXHRcdFx0dmFyIGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkID0gJyMnKyQoJyNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kJykuZGF0YSggJ2RlZmF1bHQnICk7XG5cdFx0XHQkKGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkKS50cmlnZ2VyKCdjbGljaycpO1xuXHRcdH1cblx0fSk7XG5cblx0ZnVuY3Rpb24gdG9nZ2xlQWN0aXZlT3B0aW1pemVDc3NEZWxpdmVyeU1ldGhvZCgkZWxtKSB7XG5cdFx0dmFyIG9wdGltaXplX21ldGhvZCA9ICRlbG0uZGF0YSgndmFsdWUnKTtcblx0XHRpZigncmVtb3ZlX3VudXNlZF9jc3MnID09PSBvcHRpbWl6ZV9tZXRob2Qpe1xuXHRcdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDEpO1xuXHRcdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0XHR9ZWxzZXtcblx0XHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgwKTtcblx0XHRcdCQoJyNhc3luY19jc3MnKS52YWwoMSk7XG5cdFx0fVxuXG5cdH1cblxuXHRmdW5jdGlvbiBkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpIHtcblx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMCk7XG5cdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0fVxuXG5cdCQoIFwiI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QgLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b25cIiApXG5cdFx0Lm9uKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgZnVuY3Rpb24oIGV2ZW50LCAkZWxtICkge1xuXHRcdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gKCdyZW1vdmVfdW51c2VkX2NzcycgPT09ICRlbG0uZGF0YSgndmFsdWUnKSAmJiAxID09PSBydWNzc0FjdGl2ZSlcblx0XHR9KTtcblxuXHQkKCBcIi53cHItbXVsdGlwbGUtc2VsZWN0IC53cHItbGlzdC1oZWFkZXJcIiApLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0JChlLnRhcmdldCkuY2xvc2VzdCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1saXN0JykudG9nZ2xlQ2xhc3MoJ29wZW4nKTtcblx0fSk7XG5cblx0JCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1jaGVja2JveCcpLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0Y29uc3QgY2hlY2tib3ggPSAkKHRoaXMpLmZpbmQoJ2lucHV0Jyk7XG5cdFx0Y29uc3QgaXNfY2hlY2tlZCA9IGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnKSAhPT0gdW5kZWZpbmVkO1xuXHRcdGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdGNvbnN0IHN1Yl9jaGVja2JveGVzID0gJChjaGVja2JveCkuY2xvc2VzdCgnLndwci1saXN0JykuZmluZCgnLndwci1saXN0LWJvZHkgaW5wdXRbdHlwZT1cImNoZWNrYm94XCJdJyk7XG5cdFx0aWYoY2hlY2tib3guaGFzQ2xhc3MoJ3dwci1tYWluLWNoZWNrYm94JykpIHtcblx0XHRcdCQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdFx0fSk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGNvbnN0IG1haW5fY2hlY2tib3ggPSAkKGNoZWNrYm94KS5jbG9zZXN0KCcud3ByLWxpc3QnKS5maW5kKCcud3ByLW1haW4tY2hlY2tib3gnKTtcblxuXHRcdGNvbnN0IHN1Yl9jaGVja2VkID0gICQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRpZigkKGNoZWNrYm94KS5hdHRyKCdjaGVja2VkJykgPT09IHVuZGVmaW5lZCkge1xuXHRcdFx0XHRyZXR1cm4gO1xuXHRcdFx0fVxuXHRcdFx0cmV0dXJuIGNoZWNrYm94O1xuXHRcdH0pO1xuXHRcdG1haW5fY2hlY2tib3guYXR0cignY2hlY2tlZCcsIHN1Yl9jaGVja2VkLmxlbmd0aCA9PT0gc3ViX2NoZWNrYm94ZXMubGVuZ3RoID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHR9KTtcblxuXHRpZiAoICQoICcud3ByLW1haW4tY2hlY2tib3gnICkubGVuZ3RoID4gMCApIHtcblx0XHQkKCcud3ByLW1haW4tY2hlY2tib3gnKS5lYWNoKChjaGVja2JveF9rZXksIGNoZWNrYm94KSA9PiB7XG5cdFx0XHRsZXQgcGFyZW50X2xpc3QgPSAkKGNoZWNrYm94KS5wYXJlbnRzKCcud3ByLWxpc3QnKTtcblx0XHRcdGxldCBub3RfY2hlY2tlZCA9IHBhcmVudF9saXN0LmZpbmQoICcud3ByLWxpc3QtYm9keSBpbnB1dFt0eXBlPWNoZWNrYm94XTpub3QoOmNoZWNrZWQpJyApLmxlbmd0aDtcblx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBub3RfY2hlY2tlZCA8PSAwID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHRcdH0pO1xuXHR9XG5cblx0bGV0IGNoZWNrQm94Q291bnRlciA9IHtcblx0XHRjaGVja2VkOiB7fSxcblx0XHR0b3RhbDoge31cblx0fTtcblx0JCgnLndwci1maWVsZC0tY2F0ZWdvcml6ZWRtdWx0aXNlbGVjdCAud3ByLWxpc3QnKS5lYWNoKGZ1bmN0aW9uICgpIHtcblx0XHQvLyBHZXQgdGhlIElEIG9mIHRoZSBjdXJyZW50IGVsZW1lbnRcblx0XHRsZXQgaWQgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG5cdFx0aWYgKGlkKSB7XG5cdFx0XHRjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPSAkKGAjJHtpZH0gaW5wdXRbdHlwZT0nY2hlY2tib3gnXTpjaGVja2VkYCkubGVuZ3RoO1xuXHRcdFx0Y2hlY2tCb3hDb3VudGVyLnRvdGFsW2lkXSA9ICQoYCMke2lkfSBpbnB1dFt0eXBlPSdjaGVja2JveCddOm5vdCgud3ByLW1haW4tY2hlY2tib3gpYCkubGVuZ3RoO1xuXHRcdFx0Ly8gVXBkYXRlIHRoZSBjb3VudGVyIHRleHRcblx0XHRcdCQoYCMke2lkfSAud3ByLWJhZGdlLWNvdW50ZXIgc3BhbmApLnRleHQoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdKTtcblx0XHRcdC8vIFNob3cgb3IgaGlkZSB0aGUgY291bnRlciBiYWRnZSBiYXNlZCBvbiB0aGUgY291bnRcblx0XHRcdCQoYCMke2lkfSAud3ByLWJhZGdlLWNvdW50ZXJgKS50b2dnbGUoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID4gMCk7XG5cblx0XHRcdC8vIENoZWNrIHRoZSBzZWxlY3QgYWxsIG9wdGlvbiBpZiBhbGwgZXhjbHVzaW9ucyBhcmUgY2hlY2tlZCBpbiBhIHNlY3Rpb24uXG5cdFx0XHRpZiAoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID09PSBjaGVja0JveENvdW50ZXIudG90YWxbaWRdKSB7XG5cdFx0XHRcdCQoYCMke2lkfSAud3ByLW1haW4tY2hlY2tib3hgKS5hdHRyKCdjaGVja2VkJywgdHJ1ZSk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblxuXHQvKipcblx0ICogRGVsYXkgSlMgRXhlY3V0aW9uIFNhZmUgTW9kZSBGaWVsZFxuXHQgKi9cblx0dmFyICRkamVfc2FmZV9tb2RlX2NoZWNrYm94ID0gJCgnI2RlbGF5X2pzX2V4ZWN1dGlvbl9zYWZlX21vZGUnKTtcblx0JCgnI2RlbGF5X2pzJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHRpZiAoJCh0aGlzKS5pcygnOm5vdCg6Y2hlY2tlZCknKSAmJiAkZGplX3NhZmVfbW9kZV9jaGVja2JveC5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0JGRqZV9zYWZlX21vZGVfY2hlY2tib3gudHJpZ2dlcignY2xpY2snKTtcblx0XHR9XG5cdH0pO1xuXG5cdGxldCBzdGFja2VkX3NlbGVjdCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAncm9ja2V0X3N0YWNrZWRfc2VsZWN0JyApO1xuXHRpZiAoIHN0YWNrZWRfc2VsZWN0ICkge1xuXHRcdHN0YWNrZWRfc2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2N1c3RvbS1zZWxlY3QtY2hhbmdlJyxmdW5jdGlvbihldmVudCl7XG5cblx0XHRcdGxldCBzZWxlY3RlZF9vcHRpb24gPSAkKCBldmVudC5kZXRhaWwuc2VsZWN0ZWRPcHRpb24gKTtcblxuXHRcdFx0bGV0IG5hbWUgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgnbmFtZScpO1xuXG5cdFx0XHRsZXQgc2F2aW5nID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3NhdmluZycpO1xuXHRcdFx0bGV0IHJlZ3VsYXJfcHJpY2UgID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3JlZ3VsYXItcHJpY2UnKTtcblx0XHRcdGxldCBwcmljZSAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgncHJpY2UnKTtcblx0XHRcdGxldCB1cmwgICAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgndXJsJyk7XG5cblx0XHRcdGxldCBwYXJlbnRfaXRlbSA9ICQodGhpcykucGFyZW50cyggJy53cHItdXBncmFkZS1pdGVtJyApO1xuXG5cdFx0XHRpZiAoIHNhdmluZyApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1zYXZpbmcgc3BhbicgKS5odG1sKCBzYXZpbmcgKTtcblx0XHRcdH1cblx0XHRcdGlmICggbmFtZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS10aXRsZScgKS5odG1sKCBuYW1lICk7XG5cdFx0XHR9XG5cdFx0XHRpZiAoIHJlZ3VsYXJfcHJpY2UgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtcHJpY2UtcmVndWxhciBzcGFuJyApLmh0bWwoIHJlZ3VsYXJfcHJpY2UgKTtcblx0XHRcdH1cblx0XHRcdGlmICggcHJpY2UgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtcHJpY2UtdmFsdWUnICkuaHRtbCggcHJpY2UgKTtcblx0XHRcdH1cblx0XHRcdGlmICggdXJsICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLWxpbmsnICkuYXR0ciggJ2hyZWYnLCB1cmwgKTtcblx0XHRcdH1cblxuXHRcdH0gKTtcblx0fVxufSk7XG4iLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG5cblxuXHQvKioqXG5cdCogRGFzaGJvYXJkIG5vdGljZVxuXHQqKiovXG5cblx0dmFyICRub3RpY2UgPSAkKCcud3ByLW5vdGljZScpO1xuXHR2YXIgJG5vdGljZUNsb3NlID0gJCgnI3dwci1jb25ncmF0dWxhdGlvbnMtbm90aWNlJyk7XG5cblx0JG5vdGljZUNsb3NlLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlRGFzaGJvYXJkTm90aWNlKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJDbG9zZURhc2hib2FyZE5vdGljZSgpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC50bygkbm90aWNlLCAxLCB7YXV0b0FscGhhOjAsIHg6NDAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLnRvKCRub3RpY2UsIDAuNiwge2hlaWdodDogMCwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNCcpXG5cdFx0ICAuc2V0KCRub3RpY2UsIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvKipcblx0ICogUm9ja2V0IEFuYWx5dGljcyBub3RpY2UgaW5mbyBjb2xsZWN0XG5cdCAqL1xuXHQkKCAnLnJvY2tldC1hbmFseXRpY3MtZGF0YS1jb250YWluZXInICkuaGlkZSgpO1xuXHQkKCAnLnJvY2tldC1wcmV2aWV3LWFuYWx5dGljcy1kYXRhJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKHRoaXMpLnBhcmVudCgpLm5leHQoICcucm9ja2V0LWFuYWx5dGljcy1kYXRhLWNvbnRhaW5lcicgKS50b2dnbGUoKTtcblx0fSApO1xuXG5cdC8qKipcblx0KiBIaWRlIC8gc2hvdyBSb2NrZXQgYWRkb24gdGFicy5cblx0KioqL1xuXG5cdCQoICcud3ByLXRvZ2dsZS1idXR0b24nICkuZWFjaCggZnVuY3Rpb24oKSB7XG5cdFx0dmFyICRidXR0b24gICA9ICQoIHRoaXMgKTtcblx0XHR2YXIgJGNoZWNrYm94ID0gJGJ1dHRvbi5jbG9zZXN0KCAnLndwci1maWVsZHNDb250YWluZXItZmllbGRzZXQnICkuZmluZCggJy53cHItcmFkaW8gOmNoZWNrYm94JyApO1xuXHRcdHZhciAkbWVudUl0ZW0gPSAkKCAnW2hyZWY9XCInICsgJGJ1dHRvbi5hdHRyKCAnaHJlZicgKSArICdcIl0ud3ByLW1lbnVJdGVtJyApO1xuXG5cdFx0JGNoZWNrYm94Lm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHRcdGlmICggJGNoZWNrYm94LmlzKCAnOmNoZWNrZWQnICkgKSB7XG5cdFx0XHRcdCRtZW51SXRlbS5jc3MoICdkaXNwbGF5JywgJ2Jsb2NrJyApO1xuXHRcdFx0XHQkYnV0dG9uLmNzcyggJ2Rpc3BsYXknLCAnaW5saW5lLWJsb2NrJyApO1xuXHRcdFx0fSBlbHNle1xuXHRcdFx0XHQkbWVudUl0ZW0uY3NzKCAnZGlzcGxheScsICdub25lJyApO1xuXHRcdFx0XHQkYnV0dG9uLmNzcyggJ2Rpc3BsYXknLCAnbm9uZScgKTtcblx0XHRcdH1cblx0XHR9ICkudHJpZ2dlciggJ2NoYW5nZScgKTtcblx0fSApO1xuXG5cdC8qKipcblx0KiBIZWxwIEJ1dHRvbiBUcmFja2luZ1xuXHQqKiovXG5cdFxuXHQvLyBUcmFjayBjbGlja3Mgb24gdmFyaW91cyBoZWxwIGVsZW1lbnRzIHdpdGggZGF0YSBhdHRyaWJ1dGVzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICdbZGF0YS13cHJfdHJhY2tfaGVscF0nLCBmdW5jdGlvbihlKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgJGVsID0gJCh0aGlzKTtcblx0XHRcdHZhciBidXR0b24gPSAkZWwuZGF0YSgnd3ByX3RyYWNrX2hlbHAnKTtcblx0XHRcdHZhciBjb250ZXh0ID0gJGVsLmRhdGEoJ3dwcl90cmFja19jb250ZXh0JykgfHwgJyc7XG5cdFx0XHRcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIHNwZWNpZmljIGhlbHAgcmVzb3VyY2UgY2xpY2tzIHdpdGggZXhwbGljaXQgc2VsZWN0b3JzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud2lzdGlhX2VtYmVkJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgdGl0bGUgPSAkKHRoaXMpLnRleHQoKSB8fCAnR2V0dGluZyBTdGFydGVkIFZpZGVvJztcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24odGl0bGUsICdHZXR0aW5nIFN0YXJ0ZWQnKTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIEZBUSBsaW5rcyBcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJ2FbZGF0YS1iZWFjb24tYXJ0aWNsZV0nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHZhciBocmVmID0gJCh0aGlzKS5hdHRyKCdocmVmJyk7XG5cdFx0XHR2YXIgdGV4dCA9ICQodGhpcykudGV4dCgpO1xuXHRcdFx0XG5cdFx0XHQvLyBDaGVjayBpZiBpdCdzIGluIEZBUSBzZWN0aW9uIG9yIHNpZGViYXIgZG9jdW1lbnRhdGlvblxuXHRcdFx0aWYgKCQodGhpcykuY2xvc2VzdCgnLndwci1maWVsZHNDb250YWluZXItZmllbGRzZXQnKS5wcmV2KCcud3ByLW9wdGlvbkhlYWRlcicpLmZpbmQoJy53cHItdGl0bGUyJykudGV4dCgpLmluY2x1ZGVzKCdGcmVxdWVudGx5IEFza2VkIFF1ZXN0aW9ucycpKSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0ZBUSAtICcgKyB0ZXh0LCAnRGFzaGJvYXJkJyk7XG5cdFx0XHR9IGVsc2UgaWYgKCQodGhpcykuY2xvc2VzdCgnLndwci1kb2N1bWVudGF0aW9uJykubGVuZ3RoID4gMCkge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdEb2N1bWVudGF0aW9uJywgJ1NpZGViYXInKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0RvY3VtZW50YXRpb24gTGluaycsICdHZW5lcmFsJyk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblx0XG5cdC8vIFRyYWNrIFwiSG93IHRvIG1lYXN1cmUgbG9hZGluZyB0aW1lXCIgbGlua1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnYVtocmVmKj1cImhvdy10by10ZXN0LXdvcmRwcmVzcy1zaXRlLXBlcmZvcm1hbmNlXCJdJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdMb2FkaW5nIFRpbWUgR3VpZGUnLCAnU2lkZWJhcicpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gVHJhY2sgXCJOZWVkIGhlbHA/XCIgbGlua3MgKGV4aXN0aW5nIGhlbHAgYnV0dG9ucylcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItaW5mb0FjdGlvbi0taGVscDpub3QoW2RhdGEtYmVhY29uLWlkXSknLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ05lZWQgSGVscCcsICdHZW5lcmFsJyk7XG5cdFx0fVxuXHR9KTtcblxuXG5cdC8qKipcblx0KiBTaG93IHBvcGluIGFuYWx5dGljc1xuXHQqKiovXG5cblx0dmFyICR3cHJBbmFseXRpY3NQb3BpbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzJyksXG5cdFx0JHdwclBvcGluT3ZlcmxheSA9ICQoJy53cHItUG9waW4tb3ZlcmxheScpLFxuXHRcdCR3cHJBbmFseXRpY3NDbG9zZVBvcGluID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MtY2xvc2UnKSxcblx0XHQkd3ByQW5hbHl0aWNzUG9waW5CdXR0b24gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcyAud3ByLWJ1dHRvbicpLFxuXHRcdCR3cHJBbmFseXRpY3NPcGVuUG9waW4gPSAkKCcud3ByLWpzLXBvcGluJylcblx0O1xuXG5cdCR3cHJBbmFseXRpY3NPcGVuUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJPcGVuQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByQW5hbHl0aWNzQ2xvc2VQb3Bpbi5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHdwckNsb3NlQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByQW5hbHl0aWNzUG9waW5CdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJBY3RpdmF0ZUFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByT3BlbkFuYWx5dGljcygpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC5zZXQoJHdwckFuYWx5dGljc1BvcGluLCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdCAgLnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdCAgLmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MH0se2F1dG9BbHBoYToxLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC5mcm9tVG8oJHdwckFuYWx5dGljc1BvcGluLCAwLjYsIHthdXRvQWxwaGE6MCwgbWFyZ2luVG9wOiAtMjR9LCB7YXV0b0FscGhhOjEsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckNsb3NlQW5hbHl0aWNzKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLmZyb21Ubygkd3ByQW5hbHl0aWNzUG9waW4sIDAuNiwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6IDB9LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDotMjQsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MX0se2F1dG9BbHBoYTowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdCAgLnNldCgkd3ByQW5hbHl0aWNzUG9waW4sIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQgIC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckFjdGl2YXRlQW5hbHl0aWNzKCl7XG5cdFx0d3ByQ2xvc2VBbmFseXRpY3MoKTtcblx0XHQkKCcjYW5hbHl0aWNzX2VuYWJsZWQnKS5wcm9wKCdjaGVja2VkJywgdHJ1ZSk7XG5cdFx0JCgnI2FuYWx5dGljc19lbmFibGVkJykudHJpZ2dlcignY2hhbmdlJyk7XG5cdH1cblxuXHQvLyBEaXNwbGF5IENUQSB3aXRoaW4gdGhlIHBvcGluIGBXaGF0IGluZm8gd2lsbCB3ZSBjb2xsZWN0P2Bcblx0JCgnI2FuYWx5dGljc19lbmFibGVkJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHQkKCcud3ByLXJvY2tldC1hbmFseXRpY3MtY3RhJykudG9nZ2xlQ2xhc3MoJ3dwci1pc0hpZGRlbicpO1xuXHR9KTtcblxuXHQvKioqXG5cdCogU2hvdyBwb3BpbiB1cGdyYWRlXG5cdCoqKi9cblxuXHR2YXIgJHdwclVwZ3JhZGVQb3BpbiA9ICQoJy53cHItUG9waW4tVXBncmFkZScpLFxuXHQkd3ByVXBncmFkZUNsb3NlUG9waW4gPSAkKCcud3ByLVBvcGluLVVwZ3JhZGUtY2xvc2UnKSxcblx0JHdwclVwZ3JhZGVPcGVuUG9waW4gPSAkKCcud3ByLXBvcGluLXVwZ3JhZGUtdG9nZ2xlJyk7XG5cblx0JHdwclVwZ3JhZGVPcGVuUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJPcGVuVXBncmFkZVBvcGluKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByVXBncmFkZUNsb3NlUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VVcGdyYWRlUG9waW4oKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwck9wZW5VcGdyYWRlUG9waW4oKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpO1xuXG5cdFx0dlRMLnNldCgkd3ByVXBncmFkZVBvcGluLCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdFx0LnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdFx0LmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MH0se2F1dG9BbHBoYToxLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHRcdC5mcm9tVG8oJHdwclVwZ3JhZGVQb3BpbiwgMC42LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDogLTI0fSwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJDbG9zZVVwZ3JhZGVQb3Bpbigpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKCk7XG5cblx0XHR2VEwuZnJvbVRvKCR3cHJVcGdyYWRlUG9waW4sIDAuNiwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6IDB9LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDotMjQsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdFx0LmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MX0se2F1dG9BbHBoYTowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdFx0LnNldCgkd3ByVXBncmFkZVBvcGluLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0XHQuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvKioqXG5cdCogU2lkZWJhciBvbi9vZmZcblx0KioqL1xuXHR2YXIgJHdwclNpZGViYXIgICAgPSAkKCAnLndwci1TaWRlYmFyJyApO1xuXHR2YXIgJHdwckJ1dHRvblRpcHMgPSAkKCcud3ByLWpzLXRpcHMnKTtcblxuXHQkd3ByQnV0dG9uVGlwcy5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByRGV0ZWN0VGlwcygkKHRoaXMpKTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByRGV0ZWN0VGlwcyhhRWxlbSl7XG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JHdwclNpZGViYXIuY3NzKCdkaXNwbGF5JywnYmxvY2snKTtcblx0XHRcdGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvbicgKTtcblx0XHR9XG5cdFx0ZWxzZXtcblx0XHRcdCR3cHJTaWRlYmFyLmNzcygnZGlzcGxheScsJ25vbmUnKTtcblx0XHRcdGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvZmYnICk7XG5cdFx0fVxuXHR9XG5cblxuXG5cdC8qKipcblx0KiBEZXRlY3QgQWRibG9ja1xuXHQqKiovXG5cblx0aWYoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ0xLZ09jQ1Jwd21BaicpKXtcblx0XHQkKCcud3ByLWFkYmxvY2snKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXHR9IGVsc2Uge1xuXHRcdCQoJy53cHItYWRibG9jaycpLmNzcygnZGlzcGxheScsICdibG9jaycpO1xuXHR9XG5cblx0dmFyICRhZGJsb2NrID0gJCgnLndwci1hZGJsb2NrJyk7XG5cdHZhciAkYWRibG9ja0Nsb3NlID0gJCgnLndwci1hZGJsb2NrLWNsb3NlJyk7XG5cblx0JGFkYmxvY2tDbG9zZS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJDbG9zZUFkYmxvY2tOb3RpY2UoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckNsb3NlQWRibG9ja05vdGljZSgpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC50bygkYWRibG9jaywgMSwge2F1dG9BbHBoYTowLCB4OjQwLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC50bygkYWRibG9jaywgMC40LCB7aGVpZ2h0OiAwLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS40Jylcblx0XHQgIC5zZXQoJGFkYmxvY2ssIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxufSk7XG4iLCJkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsIGZ1bmN0aW9uICgpIHtcblxuICAgIHZhciAkcGFnZU1hbmFnZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiLndwci1Db250ZW50XCIpO1xuICAgIGlmKCRwYWdlTWFuYWdlcil7XG4gICAgICAgIG5ldyBQYWdlTWFuYWdlcigkcGFnZU1hbmFnZXIpO1xuICAgIH1cblxufSk7XG5cblxuLyotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSpcXFxuXHRcdENMQVNTIFBBR0VNQU5BR0VSXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXG4vKipcbiAqIE1hbmFnZXMgdGhlIGRpc3BsYXkgb2YgcGFnZXMgLyBzZWN0aW9uIGZvciBXUCBSb2NrZXQgcGx1Z2luXG4gKlxuICogUHVibGljIG1ldGhvZCA6XG4gICAgIGRldGVjdElEIC0gRGV0ZWN0IElEIHdpdGggaGFzaFxuICAgICBnZXRCb2R5VG9wIC0gR2V0IGJvZHkgdG9wIHBvc2l0aW9uXG5cdCBjaGFuZ2UgLSBEaXNwbGF5cyB0aGUgY29ycmVzcG9uZGluZyBwYWdlXG4gKlxuICovXG5cbmZ1bmN0aW9uIFBhZ2VNYW5hZ2VyKGFFbGVtKSB7XG5cbiAgICB2YXIgcmVmVGhpcyA9IHRoaXM7XG5cbiAgICB0aGlzLiRib2R5ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1ib2R5Jyk7XG4gICAgdGhpcy4kbWVudUl0ZW1zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1tZW51SXRlbScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudCA+IGZvcm0gPiAjd3ByLW9wdGlvbnMtc3VibWl0Jyk7XG4gICAgdGhpcy4kcGFnZXMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLVBhZ2UnKTtcbiAgICB0aGlzLiRzaWRlYmFyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1TaWRlYmFyJyk7XG4gICAgdGhpcy4kY29udGVudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudCcpO1xuICAgIHRoaXMuJHRpcHMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQtdGlwcycpO1xuICAgIHRoaXMuJGxpbmtzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1ib2R5IGEnKTtcbiAgICB0aGlzLiRtZW51SXRlbSA9IG51bGw7XG4gICAgdGhpcy4kcGFnZSA9IG51bGw7XG4gICAgdGhpcy5wYWdlSWQgPSBudWxsO1xuICAgIHRoaXMuYm9keVRvcCA9IDA7XG4gICAgdGhpcy5idXR0b25UZXh0ID0gdGhpcy4kc3VibWl0QnV0dG9uLnZhbHVlO1xuXG4gICAgcmVmVGhpcy5nZXRCb2R5VG9wKCk7XG5cbiAgICAvLyBJZiB1cmwgcGFnZSBjaGFuZ2VcbiAgICB3aW5kb3cub25oYXNoY2hhbmdlID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIHJlZlRoaXMuZGV0ZWN0SUQoKTtcbiAgICB9XG5cbiAgICAvLyBJZiBoYXNoIGFscmVhZHkgZXhpc3QgKGFmdGVyIHJlZnJlc2ggcGFnZSBmb3IgZXhhbXBsZSlcbiAgICBpZih3aW5kb3cubG9jYXRpb24uaGFzaCl7XG4gICAgICAgIHRoaXMuYm9keVRvcCA9IDA7XG4gICAgICAgIHRoaXMuZGV0ZWN0SUQoKTtcbiAgICB9XG4gICAgZWxzZXtcbiAgICAgICAgdmFyIHNlc3Npb24gPSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLWhhc2gnKTtcbiAgICAgICAgdGhpcy5ib2R5VG9wID0gMDtcblxuICAgICAgICBpZihzZXNzaW9uKXtcbiAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gc2Vzc2lvbjtcbiAgICAgICAgICAgIHRoaXMuZGV0ZWN0SUQoKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNle1xuICAgICAgICAgICAgdGhpcy4kbWVudUl0ZW1zWzBdLmNsYXNzTGlzdC5hZGQoJ2lzQWN0aXZlJyk7XG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCAnZGFzaGJvYXJkJyk7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24uaGFzaCA9ICcjZGFzaGJvYXJkJztcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIENsaWNrIGxpbmsgc2FtZSBoYXNoXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRsaW5rcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRsaW5rc1tpXS5vbmNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICByZWZUaGlzLmdldEJvZHlUb3AoKTtcbiAgICAgICAgICAgIHZhciBocmVmU3BsaXQgPSB0aGlzLmhyZWYuc3BsaXQoJyMnKVsxXTtcbiAgICAgICAgICAgIGlmKGhyZWZTcGxpdCA9PSByZWZUaGlzLnBhZ2VJZCAmJiBocmVmU3BsaXQgIT0gdW5kZWZpbmVkKXtcbiAgICAgICAgICAgICAgICByZWZUaGlzLmRldGVjdElEKCk7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfVxuICAgICAgICB9O1xuICAgIH1cblxuICAgIC8vIENsaWNrIGxpbmtzIG5vdCBXUCByb2NrZXQgdG8gcmVzZXQgaGFzaFxuICAgIHZhciAkb3RoZXJsaW5rcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJyNhZG1pbm1lbnVtYWluIGEsICN3cGFkbWluYmFyIGEnKTtcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8ICRvdGhlcmxpbmtzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICRvdGhlcmxpbmtzW2ldLm9uY2xpY2sgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd3cHItaGFzaCcsICcnKTtcbiAgICAgICAgfTtcbiAgICB9XG5cbn1cblxuXG4vKlxuKiBQYWdlIGRldGVjdCBJRFxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5kZXRlY3RJRCA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMucGFnZUlkID0gd2luZG93LmxvY2F0aW9uLmhhc2guc3BsaXQoJyMnKVsxXTtcbiAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCB0aGlzLnBhZ2VJZCk7XG5cbiAgICB0aGlzLiRwYWdlID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1QYWdlIycgKyB0aGlzLnBhZ2VJZCk7XG4gICAgdGhpcy4kbWVudUl0ZW0gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3ByLW5hdi0nICsgdGhpcy5wYWdlSWQpO1xuXG4gICAgdGhpcy5jaGFuZ2UoKTtcbn1cblxuXG5cbi8qXG4qIEdldCBib2R5IHRvcCBwb3NpdGlvblxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5nZXRCb2R5VG9wID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGJvZHlQb3MgPSB0aGlzLiRib2R5LmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgIHRoaXMuYm9keVRvcCA9IGJvZHlQb3MudG9wICsgd2luZG93LnBhZ2VZT2Zmc2V0IC0gNDc7IC8vICN3cGFkbWluYmFyICsgcGFkZGluZy10b3AgLndwci13cmFwIC0gMSAtIDQ3XG59XG5cblxuXG4vKlxuKiBQYWdlIGNoYW5nZVxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5jaGFuZ2UgPSBmdW5jdGlvbigpIHtcblxuICAgIHZhciByZWZUaGlzID0gdGhpcztcbiAgICBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuc2Nyb2xsVG9wID0gcmVmVGhpcy5ib2R5VG9wO1xuXG4gICAgLy8gSGlkZSBvdGhlciBwYWdlc1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kcGFnZXMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kcGFnZXNbaV0uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRtZW51SXRlbXMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kbWVudUl0ZW1zW2ldLmNsYXNzTGlzdC5yZW1vdmUoJ2lzQWN0aXZlJyk7XG4gICAgfVxuXG4gICAgLy8gU2hvdyBjdXJyZW50IGRlZmF1bHQgcGFnZVxuICAgIHRoaXMuJHBhZ2Uuc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG4gICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuXG4gICAgaWYgKCBudWxsID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInICkgKSB7XG4gICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvbicgKTtcbiAgICB9XG5cbiAgICBpZiAoICdvbicgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd3cHItc2hvdy1zaWRlYmFyJykgKSB7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG4gICAgfSBlbHNlIGlmICggJ29mZicgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd3cHItc2hvdy1zaWRlYmFyJykgKSB7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3dwci1qcy10aXBzJykucmVtb3ZlQXR0cmlidXRlKCAnY2hlY2tlZCcgKTtcbiAgICB9XG5cbiAgICB0aGlzLiR0aXBzLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIHRoaXMuJG1lbnVJdGVtLmNsYXNzTGlzdC5hZGQoJ2lzQWN0aXZlJyk7XG4gICAgdGhpcy4kc3VibWl0QnV0dG9uLnZhbHVlID0gdGhpcy5idXR0b25UZXh0O1xuICAgIHRoaXMuJGNvbnRlbnQuY2xhc3NMaXN0LmFkZCgnaXNOb3RGdWxsJyk7XG5cblxuICAgIC8vIEV4Y2VwdGlvbiBmb3IgZGFzaGJvYXJkXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXNoYm9hcmRcIil7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kY29udGVudC5jbGFzc0xpc3QucmVtb3ZlKCdpc05vdEZ1bGwnKTtcbiAgICB9XG5cbiAgICAvLyBFeGNlcHRpb24gZm9yIGFkZG9uc1xuICAgIGlmKHRoaXMucGFnZUlkID09IFwiYWRkb25zXCIpe1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICAvLyBFeGNlcHRpb24gZm9yIGRhdGFiYXNlXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXRhYmFzZVwiKXtcbiAgICAgICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgLy8gRXhjZXB0aW9uIGZvciB0b29scyBhbmQgYWRkb25zXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJ0b29sc1wiIHx8IHRoaXMucGFnZUlkID09IFwiYWRkb25zXCIpe1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICBpZiAodGhpcy5wYWdlSWQgPT0gXCJpbWFnaWZ5XCIpIHtcbiAgICAgICAgdGhpcy4kc2lkZWJhci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiR0aXBzLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cblxuICAgIGlmICh0aGlzLnBhZ2VJZCA9PSBcInR1dG9yaWFsc1wiKSB7XG4gICAgICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cblxuXHRpZiAodGhpcy5wYWdlSWQgPT0gXCJwbHVnaW5zXCIpIHtcblx0XHR0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0fVxufTtcbiIsIi8qZXNsaW50LWVudiBlczYqL1xuKCAoIGRvY3VtZW50LCB3aW5kb3cgKSA9PiB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLndwci1yb2NrZXRjZG4tb3BlbicgKS5mb3JFYWNoKCAoIGVsICkgPT4ge1xuXHRcdFx0ZWwuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCBlICkgPT4ge1xuXHRcdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHR9ICk7XG5cdFx0fSApO1xuXG5cdFx0bWF5YmVPcGVuTW9kYWwoKTtcblxuXHRcdE1pY3JvTW9kYWwuaW5pdCgge1xuXHRcdFx0ZGlzYWJsZVNjcm9sbDogdHJ1ZVxuXHRcdH0gKTtcblx0fSApO1xuXG5cdHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsICgpID0+IHtcblx0XHRsZXQgb3BlbkNUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1vcGVuLWN0YScgKSxcblx0XHRcdGNsb3NlQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWNsb3NlLWN0YScgKSxcblx0XHRcdHNtYWxsQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YS1zbWFsbCcgKSxcblx0XHRcdGJpZ0NUQSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjd3ByLXJvY2tldGNkbi1jdGEnICk7XG5cblx0XHRpZiAoIG51bGwgIT09IG9wZW5DVEEgJiYgbnVsbCAhPT0gc21hbGxDVEEgJiYgbnVsbCAhPT0gYmlnQ1RBICkge1xuXHRcdFx0b3BlbkNUQS5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdFx0XHRzbWFsbENUQS5jbGFzc0xpc3QuYWRkKCAnd3ByLWlzSGlkZGVuJyApO1xuXHRcdFx0XHRiaWdDVEEuY2xhc3NMaXN0LnJlbW92ZSggJ3dwci1pc0hpZGRlbicgKTtcblxuXHRcdFx0XHRzZW5kSFRUUFJlcXVlc3QoIGdldFBvc3REYXRhKCAnYmlnJyApICk7XG5cdFx0XHR9ICk7XG5cdFx0fVxuXG5cdFx0aWYgKCBudWxsICE9PSBjbG9zZUNUQSAmJiBudWxsICE9PSBzbWFsbENUQSAmJiBudWxsICE9PSBiaWdDVEEgKSB7XG5cdFx0XHRjbG9zZUNUQS5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdFx0XHRzbWFsbENUQS5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWlzSGlkZGVuJyApO1xuXHRcdFx0XHRiaWdDVEEuY2xhc3NMaXN0LmFkZCggJ3dwci1pc0hpZGRlbicgKTtcblxuXHRcdFx0XHRzZW5kSFRUUFJlcXVlc3QoIGdldFBvc3REYXRhKCAnc21hbGwnICkgKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHRmdW5jdGlvbiBnZXRQb3N0RGF0YSggc3RhdHVzICkge1xuXHRcdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRcdHBvc3REYXRhICs9ICdhY3Rpb249dG9nZ2xlX3JvY2tldGNkbl9jdGEnO1xuXHRcdFx0cG9zdERhdGEgKz0gJyZzdGF0dXM9JyArIHN0YXR1cztcblx0XHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRcdHJldHVybiBwb3N0RGF0YTtcblx0XHR9XG5cdH0gKTtcblxuXHR3aW5kb3cub25tZXNzYWdlID0gKCBlICkgPT4ge1xuXHRcdGNvbnN0IGlmcmFtZVVSTCA9IHJvY2tldF9hamF4X2RhdGEub3JpZ2luX3VybDtcblxuXHRcdGlmICggZS5vcmlnaW4gIT09IGlmcmFtZVVSTCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRzZXRDRE5GcmFtZUhlaWdodCggZS5kYXRhICk7XG5cdFx0Y2xvc2VNb2RhbCggZS5kYXRhICk7XG5cdFx0dG9rZW5IYW5kbGVyKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHByb2Nlc3NTdGF0dXMoIGUuZGF0YSApO1xuXHRcdGVuYWJsZUNETiggZS5kYXRhLCBpZnJhbWVVUkwgKTtcblx0XHRkaXNhYmxlQ0ROKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZS5kYXRhICk7XG5cdH07XG5cblx0ZnVuY3Rpb24gbWF5YmVPcGVuTW9kYWwoKSB7XG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9wcm9jZXNzX3N0YXR1cyc7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXG5cdFx0XHRcdGlmICggdHJ1ZSA9PT0gcmVzcG9uc2VUeHQuc3VjY2VzcyApIHtcblx0XHRcdFx0XHRNaWNyb01vZGFsLnNob3coICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIGNsb3NlTW9kYWwoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5GcmFtZUNsb3NlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdE1pY3JvTW9kYWwuY2xvc2UoICd3cHItcm9ja2V0Y2RuLW1vZGFsJyApO1xuXG5cdFx0bGV0IHBhZ2VzID0gWyAnaWZyYW1lLXBheW1lbnQtc3VjY2VzcycsICdpZnJhbWUtdW5zdWJzY3JpYmUtc3VjY2VzcycgXTtcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAnY2RuX3BhZ2VfbWVzc2FnZScgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRpZiAoIHBhZ2VzLmluZGV4T2YoIGRhdGEuY2RuX3BhZ2VfbWVzc2FnZSApID09PSAtMSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5sb2NhdGlvbi5yZWxvYWQoKTtcblx0fVxuXG5cdGZ1bmN0aW9uIHByb2Nlc3NTdGF0dXMoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fcHJvY2VzcycgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc2V0Jztcblx0XHRwb3N0RGF0YSArPSAnJnN0YXR1cz0nICsgZGF0YS5yb2NrZXRjZG5fcHJvY2Vzcztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0c2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXHR9XG5cblx0ZnVuY3Rpb24gZW5hYmxlQ0ROKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdXJsJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fZW5hYmxlJztcblx0XHRwb3N0RGF0YSArPSAnJmNkbl91cmw9JyArIGRhdGEucm9ja2V0Y2RuX3VybDtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIGRpc2FibGVDRE4oIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl9kaXNhYmxlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fZGlzYWJsZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICkge1xuXHRcdGNvbnN0IGh0dHBSZXF1ZXN0ID0gbmV3IFhNTEh0dHBSZXF1ZXN0KCk7XG5cblx0XHRodHRwUmVxdWVzdC5vcGVuKCAnUE9TVCcsIGFqYXh1cmwgKTtcblx0XHRodHRwUmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKCAnQ29udGVudC1UeXBlJywgJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCcgKTtcblx0XHRodHRwUmVxdWVzdC5zZW5kKCBwb3N0RGF0YSApO1xuXG5cdFx0cmV0dXJuIGh0dHBSZXF1ZXN0O1xuXHR9XG5cblx0ZnVuY3Rpb24gc2V0Q0RORnJhbWVIZWlnaHQoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5GcmFtZUhlaWdodCcgKSApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ3JvY2tldGNkbi1pZnJhbWUnICkuc3R5bGUuaGVpZ2h0ID0gYCR7IGRhdGEuY2RuRnJhbWVIZWlnaHQgfXB4YDtcblx0fVxuXG5cdGZ1bmN0aW9uIHRva2VuSGFuZGxlciggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3Rva2VuJyApICkge1xuXHRcdFx0bGV0IGRhdGEgPSB7cHJvY2VzczpcInN1YnNjcmliZVwiLCBtZXNzYWdlOlwidG9rZW5fbm90X3JlY2VpdmVkXCJ9O1xuXHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHR7XG5cdFx0XHRcdFx0J3N1Y2Nlc3MnOiBmYWxzZSxcblx0XHRcdFx0XHQnZGF0YSc6IGRhdGEsXG5cdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0fSxcblx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHQpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1zYXZlX3JvY2tldGNkbl90b2tlbic7XG5cdFx0cG9zdERhdGEgKz0gJyZ2YWx1ZT0nICsgZGF0YS5yb2NrZXRjZG5fdG9rZW47XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cblx0XHRyZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9ICgpID0+IHtcblx0XHRcdGlmICggcmVxdWVzdC5yZWFkeVN0YXRlID09PSBYTUxIdHRwUmVxdWVzdC5ET05FICYmIDIwMCA9PT0gcmVxdWVzdC5zdGF0dXMgKSB7XG5cdFx0XHRcdGxldCByZXNwb25zZVR4dCA9IEpTT04ucGFyc2UocmVxdWVzdC5yZXNwb25zZVRleHQpO1xuXHRcdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0J3N1Y2Nlc3MnOiByZXNwb25zZVR4dC5zdWNjZXNzLFxuXHRcdFx0XHRcdFx0J2RhdGEnOiByZXNwb25zZVR4dC5kYXRhLFxuXHRcdFx0XHRcdFx0J3JvY2tldGNkbic6IHRydWVcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGlmcmFtZVVSTFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXHRcdH07XG5cdH1cblxuXHRmdW5jdGlvbiB2YWxpZGF0ZVRva2VuQW5kQ05BTUUoIGRhdGEgKSB7XG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW4nICkgfHwgISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX3ZhbGlkYXRlX2NuYW1lJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW5fY25hbWUnO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3VybD0nICsgZGF0YS5yb2NrZXRjZG5fdmFsaWRhdGVfY25hbWU7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdG9rZW49JyArIGRhdGEucm9ja2V0Y2RuX3ZhbGlkYXRlX3Rva2VuO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXHR9XG59ICkoIGRvY3VtZW50LCB3aW5kb3cgKTtcbiIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcIlRpbWVsaW5lTGl0ZVwiLFtcImNvcmUuQW5pbWF0aW9uXCIsXCJjb3JlLlNpbXBsZVRpbWVsaW5lXCIsXCJUd2VlbkxpdGVcIl0sZnVuY3Rpb24odCxlLGkpe3ZhciBzPWZ1bmN0aW9uKHQpe2UuY2FsbCh0aGlzLHQpLHRoaXMuX2xhYmVscz17fSx0aGlzLmF1dG9SZW1vdmVDaGlsZHJlbj10aGlzLnZhcnMuYXV0b1JlbW92ZUNoaWxkcmVuPT09ITAsdGhpcy5zbW9vdGhDaGlsZFRpbWluZz10aGlzLnZhcnMuc21vb3RoQ2hpbGRUaW1pbmc9PT0hMCx0aGlzLl9zb3J0Q2hpbGRyZW49ITAsdGhpcy5fb25VcGRhdGU9dGhpcy52YXJzLm9uVXBkYXRlO3ZhciBpLHMscj10aGlzLnZhcnM7Zm9yKHMgaW4gcilpPXJbc10sYShpKSYmLTEhPT1pLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKSYmKHJbc109dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhpKSk7YShyLnR3ZWVucykmJnRoaXMuYWRkKHIudHdlZW5zLDAsci5hbGlnbixyLnN0YWdnZXIpfSxyPTFlLTEwLG49aS5faW50ZXJuYWxzLmlzU2VsZWN0b3IsYT1pLl9pbnRlcm5hbHMuaXNBcnJheSxvPVtdLGg9d2luZG93Ll9nc0RlZmluZS5nbG9iYWxzLGw9ZnVuY3Rpb24odCl7dmFyIGUsaT17fTtmb3IoZSBpbiB0KWlbZV09dFtlXTtyZXR1cm4gaX0sXz1mdW5jdGlvbih0LGUsaSxzKXt0Ll90aW1lbGluZS5wYXVzZSh0Ll9zdGFydFRpbWUpLGUmJmUuYXBwbHkoc3x8dC5fdGltZWxpbmUsaXx8byl9LHU9by5zbGljZSxmPXMucHJvdG90eXBlPW5ldyBlO3JldHVybiBzLnZlcnNpb249XCIxLjEyLjFcIixmLmNvbnN0cnVjdG9yPXMsZi5raWxsKCkuX2djPSExLGYudG89ZnVuY3Rpb24odCxlLHMscil7dmFyIG49cy5yZXBlYXQmJmguVHdlZW5NYXh8fGk7cmV0dXJuIGU/dGhpcy5hZGQobmV3IG4odCxlLHMpLHIpOnRoaXMuc2V0KHQscyxyKX0sZi5mcm9tPWZ1bmN0aW9uKHQsZSxzLHIpe3JldHVybiB0aGlzLmFkZCgocy5yZXBlYXQmJmguVHdlZW5NYXh8fGkpLmZyb20odCxlLHMpLHIpfSxmLmZyb21Ubz1mdW5jdGlvbih0LGUscyxyLG4pe3ZhciBhPXIucmVwZWF0JiZoLlR3ZWVuTWF4fHxpO3JldHVybiBlP3RoaXMuYWRkKGEuZnJvbVRvKHQsZSxzLHIpLG4pOnRoaXMuc2V0KHQscixuKX0sZi5zdGFnZ2VyVG89ZnVuY3Rpb24odCxlLHIsYSxvLGgsXyxmKXt2YXIgcCxjPW5ldyBzKHtvbkNvbXBsZXRlOmgsb25Db21wbGV0ZVBhcmFtczpfLG9uQ29tcGxldGVTY29wZTpmLHNtb290aENoaWxkVGltaW5nOnRoaXMuc21vb3RoQ2hpbGRUaW1pbmd9KTtmb3IoXCJzdHJpbmdcIj09dHlwZW9mIHQmJih0PWkuc2VsZWN0b3IodCl8fHQpLG4odCkmJih0PXUuY2FsbCh0LDApKSxhPWF8fDAscD0wO3QubGVuZ3RoPnA7cCsrKXIuc3RhcnRBdCYmKHIuc3RhcnRBdD1sKHIuc3RhcnRBdCkpLGMudG8odFtwXSxlLGwocikscCphKTtyZXR1cm4gdGhpcy5hZGQoYyxvKX0sZi5zdGFnZ2VyRnJvbT1mdW5jdGlvbih0LGUsaSxzLHIsbixhLG8pe3JldHVybiBpLmltbWVkaWF0ZVJlbmRlcj0wIT1pLmltbWVkaWF0ZVJlbmRlcixpLnJ1bkJhY2t3YXJkcz0hMCx0aGlzLnN0YWdnZXJUbyh0LGUsaSxzLHIsbixhLG8pfSxmLnN0YWdnZXJGcm9tVG89ZnVuY3Rpb24odCxlLGkscyxyLG4sYSxvLGgpe3JldHVybiBzLnN0YXJ0QXQ9aSxzLmltbWVkaWF0ZVJlbmRlcj0wIT1zLmltbWVkaWF0ZVJlbmRlciYmMCE9aS5pbW1lZGlhdGVSZW5kZXIsdGhpcy5zdGFnZ2VyVG8odCxlLHMscixuLGEsbyxoKX0sZi5jYWxsPWZ1bmN0aW9uKHQsZSxzLHIpe3JldHVybiB0aGlzLmFkZChpLmRlbGF5ZWRDYWxsKDAsdCxlLHMpLHIpfSxmLnNldD1mdW5jdGlvbih0LGUscyl7cmV0dXJuIHM9dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChzLDAsITApLG51bGw9PWUuaW1tZWRpYXRlUmVuZGVyJiYoZS5pbW1lZGlhdGVSZW5kZXI9cz09PXRoaXMuX3RpbWUmJiF0aGlzLl9wYXVzZWQpLHRoaXMuYWRkKG5ldyBpKHQsMCxlKSxzKX0scy5leHBvcnRSb290PWZ1bmN0aW9uKHQsZSl7dD10fHx7fSxudWxsPT10LnNtb290aENoaWxkVGltaW5nJiYodC5zbW9vdGhDaGlsZFRpbWluZz0hMCk7dmFyIHIsbixhPW5ldyBzKHQpLG89YS5fdGltZWxpbmU7Zm9yKG51bGw9PWUmJihlPSEwKSxvLl9yZW1vdmUoYSwhMCksYS5fc3RhcnRUaW1lPTAsYS5fcmF3UHJldlRpbWU9YS5fdGltZT1hLl90b3RhbFRpbWU9by5fdGltZSxyPW8uX2ZpcnN0O3I7KW49ci5fbmV4dCxlJiZyIGluc3RhbmNlb2YgaSYmci50YXJnZXQ9PT1yLnZhcnMub25Db21wbGV0ZXx8YS5hZGQocixyLl9zdGFydFRpbWUtci5fZGVsYXkpLHI9bjtyZXR1cm4gby5hZGQoYSwwKSxhfSxmLmFkZD1mdW5jdGlvbihyLG4sbyxoKXt2YXIgbCxfLHUsZixwLGM7aWYoXCJudW1iZXJcIiE9dHlwZW9mIG4mJihuPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwobiwwLCEwLHIpKSwhKHIgaW5zdGFuY2VvZiB0KSl7aWYociBpbnN0YW5jZW9mIEFycmF5fHxyJiZyLnB1c2gmJmEocikpe2ZvcihvPW98fFwibm9ybWFsXCIsaD1ofHwwLGw9bixfPXIubGVuZ3RoLHU9MDtfPnU7dSsrKWEoZj1yW3VdKSYmKGY9bmV3IHMoe3R3ZWVuczpmfSkpLHRoaXMuYWRkKGYsbCksXCJzdHJpbmdcIiE9dHlwZW9mIGYmJlwiZnVuY3Rpb25cIiE9dHlwZW9mIGYmJihcInNlcXVlbmNlXCI9PT1vP2w9Zi5fc3RhcnRUaW1lK2YudG90YWxEdXJhdGlvbigpL2YuX3RpbWVTY2FsZTpcInN0YXJ0XCI9PT1vJiYoZi5fc3RhcnRUaW1lLT1mLmRlbGF5KCkpKSxsKz1oO3JldHVybiB0aGlzLl91bmNhY2hlKCEwKX1pZihcInN0cmluZ1wiPT10eXBlb2YgcilyZXR1cm4gdGhpcy5hZGRMYWJlbChyLG4pO2lmKFwiZnVuY3Rpb25cIiE9dHlwZW9mIHIpdGhyb3dcIkNhbm5vdCBhZGQgXCIrcitcIiBpbnRvIHRoZSB0aW1lbGluZTsgaXQgaXMgbm90IGEgdHdlZW4sIHRpbWVsaW5lLCBmdW5jdGlvbiwgb3Igc3RyaW5nLlwiO3I9aS5kZWxheWVkQ2FsbCgwLHIpfWlmKGUucHJvdG90eXBlLmFkZC5jYWxsKHRoaXMscixuKSwodGhpcy5fZ2N8fHRoaXMuX3RpbWU9PT10aGlzLl9kdXJhdGlvbikmJiF0aGlzLl9wYXVzZWQmJnRoaXMuX2R1cmF0aW9uPHRoaXMuZHVyYXRpb24oKSlmb3IocD10aGlzLGM9cC5yYXdUaW1lKCk+ci5fc3RhcnRUaW1lO3AuX3RpbWVsaW5lOyljJiZwLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZz9wLnRvdGFsVGltZShwLl90b3RhbFRpbWUsITApOnAuX2djJiZwLl9lbmFibGVkKCEwLCExKSxwPXAuX3RpbWVsaW5lO3JldHVybiB0aGlzfSxmLnJlbW92ZT1mdW5jdGlvbihlKXtpZihlIGluc3RhbmNlb2YgdClyZXR1cm4gdGhpcy5fcmVtb3ZlKGUsITEpO2lmKGUgaW5zdGFuY2VvZiBBcnJheXx8ZSYmZS5wdXNoJiZhKGUpKXtmb3IodmFyIGk9ZS5sZW5ndGg7LS1pPi0xOyl0aGlzLnJlbW92ZShlW2ldKTtyZXR1cm4gdGhpc31yZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgZT90aGlzLnJlbW92ZUxhYmVsKGUpOnRoaXMua2lsbChudWxsLGUpfSxmLl9yZW1vdmU9ZnVuY3Rpb24odCxpKXtlLnByb3RvdHlwZS5fcmVtb3ZlLmNhbGwodGhpcyx0LGkpO3ZhciBzPXRoaXMuX2xhc3Q7cmV0dXJuIHM/dGhpcy5fdGltZT5zLl9zdGFydFRpbWUrcy5fdG90YWxEdXJhdGlvbi9zLl90aW1lU2NhbGUmJih0aGlzLl90aW1lPXRoaXMuZHVyYXRpb24oKSx0aGlzLl90b3RhbFRpbWU9dGhpcy5fdG90YWxEdXJhdGlvbik6dGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9dGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj0wLHRoaXN9LGYuYXBwZW5kPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuYWRkKHQsdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChudWxsLGUsITAsdCkpfSxmLmluc2VydD1mLmluc2VydE11bHRpcGxlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmFkZCh0LGV8fDAsaSxzKX0sZi5hcHBlbmRNdWx0aXBsZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5hZGQodCx0aGlzLl9wYXJzZVRpbWVPckxhYmVsKG51bGwsZSwhMCx0KSxpLHMpfSxmLmFkZExhYmVsPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMuX2xhYmVsc1t0XT10aGlzLl9wYXJzZVRpbWVPckxhYmVsKGUpLHRoaXN9LGYuYWRkUGF1c2U9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuY2FsbChfLFtcIntzZWxmfVwiLGUsaSxzXSx0aGlzLHQpfSxmLnJlbW92ZUxhYmVsPWZ1bmN0aW9uKHQpe3JldHVybiBkZWxldGUgdGhpcy5fbGFiZWxzW3RdLHRoaXN9LGYuZ2V0TGFiZWxUaW1lPWZ1bmN0aW9uKHQpe3JldHVybiBudWxsIT10aGlzLl9sYWJlbHNbdF0/dGhpcy5fbGFiZWxzW3RdOi0xfSxmLl9wYXJzZVRpbWVPckxhYmVsPWZ1bmN0aW9uKGUsaSxzLHIpe3ZhciBuO2lmKHIgaW5zdGFuY2VvZiB0JiZyLnRpbWVsaW5lPT09dGhpcyl0aGlzLnJlbW92ZShyKTtlbHNlIGlmKHImJihyIGluc3RhbmNlb2YgQXJyYXl8fHIucHVzaCYmYShyKSkpZm9yKG49ci5sZW5ndGg7LS1uPi0xOylyW25daW5zdGFuY2VvZiB0JiZyW25dLnRpbWVsaW5lPT09dGhpcyYmdGhpcy5yZW1vdmUocltuXSk7aWYoXCJzdHJpbmdcIj09dHlwZW9mIGkpcmV0dXJuIHRoaXMuX3BhcnNlVGltZU9yTGFiZWwoaSxzJiZcIm51bWJlclwiPT10eXBlb2YgZSYmbnVsbD09dGhpcy5fbGFiZWxzW2ldP2UtdGhpcy5kdXJhdGlvbigpOjAscyk7aWYoaT1pfHwwLFwic3RyaW5nXCIhPXR5cGVvZiBlfHwhaXNOYU4oZSkmJm51bGw9PXRoaXMuX2xhYmVsc1tlXSludWxsPT1lJiYoZT10aGlzLmR1cmF0aW9uKCkpO2Vsc2V7aWYobj1lLmluZGV4T2YoXCI9XCIpLC0xPT09bilyZXR1cm4gbnVsbD09dGhpcy5fbGFiZWxzW2VdP3M/dGhpcy5fbGFiZWxzW2VdPXRoaXMuZHVyYXRpb24oKStpOmk6dGhpcy5fbGFiZWxzW2VdK2k7aT1wYXJzZUludChlLmNoYXJBdChuLTEpK1wiMVwiLDEwKSpOdW1iZXIoZS5zdWJzdHIobisxKSksZT1uPjE/dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChlLnN1YnN0cigwLG4tMSksMCxzKTp0aGlzLmR1cmF0aW9uKCl9cmV0dXJuIE51bWJlcihlKStpfSxmLnNlZWs9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy50b3RhbFRpbWUoXCJudW1iZXJcIj09dHlwZW9mIHQ/dDp0aGlzLl9wYXJzZVRpbWVPckxhYmVsKHQpLGUhPT0hMSl9LGYuc3RvcD1mdW5jdGlvbigpe3JldHVybiB0aGlzLnBhdXNlZCghMCl9LGYuZ290b0FuZFBsYXk9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5wbGF5KHQsZSl9LGYuZ290b0FuZFN0b3A9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5wYXVzZSh0LGUpfSxmLnJlbmRlcj1mdW5jdGlvbih0LGUsaSl7dGhpcy5fZ2MmJnRoaXMuX2VuYWJsZWQoITAsITEpO3ZhciBzLG4sYSxoLGwsXz10aGlzLl9kaXJ0eT90aGlzLnRvdGFsRHVyYXRpb24oKTp0aGlzLl90b3RhbER1cmF0aW9uLHU9dGhpcy5fdGltZSxmPXRoaXMuX3N0YXJ0VGltZSxwPXRoaXMuX3RpbWVTY2FsZSxjPXRoaXMuX3BhdXNlZDtpZih0Pj1fPyh0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT1fLHRoaXMuX3JldmVyc2VkfHx0aGlzLl9oYXNQYXVzZWRDaGlsZCgpfHwobj0hMCxoPVwib25Db21wbGV0ZVwiLDA9PT10aGlzLl9kdXJhdGlvbiYmKDA9PT10fHwwPnRoaXMuX3Jhd1ByZXZUaW1lfHx0aGlzLl9yYXdQcmV2VGltZT09PXIpJiZ0aGlzLl9yYXdQcmV2VGltZSE9PXQmJnRoaXMuX2ZpcnN0JiYobD0hMCx0aGlzLl9yYXdQcmV2VGltZT5yJiYoaD1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIpKSksdGhpcy5fcmF3UHJldlRpbWU9dGhpcy5fZHVyYXRpb258fCFlfHx0fHx0aGlzLl9yYXdQcmV2VGltZT09PXQ/dDpyLHQ9XysxZS00KToxZS03PnQ/KHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPTAsKDAhPT11fHwwPT09dGhpcy5fZHVyYXRpb24mJnRoaXMuX3Jhd1ByZXZUaW1lIT09ciYmKHRoaXMuX3Jhd1ByZXZUaW1lPjB8fDA+dCYmdGhpcy5fcmF3UHJldlRpbWU+PTApKSYmKGg9XCJvblJldmVyc2VDb21wbGV0ZVwiLG49dGhpcy5fcmV2ZXJzZWQpLDA+dD8odGhpcy5fYWN0aXZlPSExLDA9PT10aGlzLl9kdXJhdGlvbiYmdGhpcy5fcmF3UHJldlRpbWU+PTAmJnRoaXMuX2ZpcnN0JiYobD0hMCksdGhpcy5fcmF3UHJldlRpbWU9dCk6KHRoaXMuX3Jhd1ByZXZUaW1lPXRoaXMuX2R1cmF0aW9ufHwhZXx8dHx8dGhpcy5fcmF3UHJldlRpbWU9PT10P3Q6cix0PTAsdGhpcy5faW5pdHRlZHx8KGw9ITApKSk6dGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9dGhpcy5fcmF3UHJldlRpbWU9dCx0aGlzLl90aW1lIT09dSYmdGhpcy5fZmlyc3R8fGl8fGwpe2lmKHRoaXMuX2luaXR0ZWR8fCh0aGlzLl9pbml0dGVkPSEwKSx0aGlzLl9hY3RpdmV8fCF0aGlzLl9wYXVzZWQmJnRoaXMuX3RpbWUhPT11JiZ0PjAmJih0aGlzLl9hY3RpdmU9ITApLDA9PT11JiZ0aGlzLnZhcnMub25TdGFydCYmMCE9PXRoaXMuX3RpbWUmJihlfHx0aGlzLnZhcnMub25TdGFydC5hcHBseSh0aGlzLnZhcnMub25TdGFydFNjb3BlfHx0aGlzLHRoaXMudmFycy5vblN0YXJ0UGFyYW1zfHxvKSksdGhpcy5fdGltZT49dSlmb3Iocz10aGlzLl9maXJzdDtzJiYoYT1zLl9uZXh0LCF0aGlzLl9wYXVzZWR8fGMpOykocy5fYWN0aXZlfHxzLl9zdGFydFRpbWU8PXRoaXMuX3RpbWUmJiFzLl9wYXVzZWQmJiFzLl9nYykmJihzLl9yZXZlcnNlZD9zLnJlbmRlcigocy5fZGlydHk/cy50b3RhbER1cmF0aW9uKCk6cy5fdG90YWxEdXJhdGlvbiktKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKTpzLnJlbmRlcigodC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpKSxzPWE7ZWxzZSBmb3Iocz10aGlzLl9sYXN0O3MmJihhPXMuX3ByZXYsIXRoaXMuX3BhdXNlZHx8Yyk7KShzLl9hY3RpdmV8fHU+PXMuX3N0YXJ0VGltZSYmIXMuX3BhdXNlZCYmIXMuX2djKSYmKHMuX3JldmVyc2VkP3MucmVuZGVyKChzLl9kaXJ0eT9zLnRvdGFsRHVyYXRpb24oKTpzLl90b3RhbER1cmF0aW9uKS0odC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpOnMucmVuZGVyKCh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSkpLHM9YTt0aGlzLl9vblVwZGF0ZSYmKGV8fHRoaXMuX29uVXBkYXRlLmFwcGx5KHRoaXMudmFycy5vblVwZGF0ZVNjb3BlfHx0aGlzLHRoaXMudmFycy5vblVwZGF0ZVBhcmFtc3x8bykpLGgmJih0aGlzLl9nY3x8KGY9PT10aGlzLl9zdGFydFRpbWV8fHAhPT10aGlzLl90aW1lU2NhbGUpJiYoMD09PXRoaXMuX3RpbWV8fF8+PXRoaXMudG90YWxEdXJhdGlvbigpKSYmKG4mJih0aGlzLl90aW1lbGluZS5hdXRvUmVtb3ZlQ2hpbGRyZW4mJnRoaXMuX2VuYWJsZWQoITEsITEpLHRoaXMuX2FjdGl2ZT0hMSksIWUmJnRoaXMudmFyc1toXSYmdGhpcy52YXJzW2hdLmFwcGx5KHRoaXMudmFyc1toK1wiU2NvcGVcIl18fHRoaXMsdGhpcy52YXJzW2grXCJQYXJhbXNcIl18fG8pKSl9fSxmLl9oYXNQYXVzZWRDaGlsZD1mdW5jdGlvbigpe2Zvcih2YXIgdD10aGlzLl9maXJzdDt0Oyl7aWYodC5fcGF1c2VkfHx0IGluc3RhbmNlb2YgcyYmdC5faGFzUGF1c2VkQ2hpbGQoKSlyZXR1cm4hMDt0PXQuX25leHR9cmV0dXJuITF9LGYuZ2V0Q2hpbGRyZW49ZnVuY3Rpb24odCxlLHMscil7cj1yfHwtOTk5OTk5OTk5OTtmb3IodmFyIG49W10sYT10aGlzLl9maXJzdCxvPTA7YTspcj5hLl9zdGFydFRpbWV8fChhIGluc3RhbmNlb2YgaT9lIT09ITEmJihuW28rK109YSk6KHMhPT0hMSYmKG5bbysrXT1hKSx0IT09ITEmJihuPW4uY29uY2F0KGEuZ2V0Q2hpbGRyZW4oITAsZSxzKSksbz1uLmxlbmd0aCkpKSxhPWEuX25leHQ7cmV0dXJuIG59LGYuZ2V0VHdlZW5zT2Y9ZnVuY3Rpb24odCxlKXt2YXIgcyxyLG49dGhpcy5fZ2MsYT1bXSxvPTA7Zm9yKG4mJnRoaXMuX2VuYWJsZWQoITAsITApLHM9aS5nZXRUd2VlbnNPZih0KSxyPXMubGVuZ3RoOy0tcj4tMTspKHNbcl0udGltZWxpbmU9PT10aGlzfHxlJiZ0aGlzLl9jb250YWlucyhzW3JdKSkmJihhW28rK109c1tyXSk7cmV0dXJuIG4mJnRoaXMuX2VuYWJsZWQoITEsITApLGF9LGYuX2NvbnRhaW5zPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10LnRpbWVsaW5lO2U7KXtpZihlPT09dGhpcylyZXR1cm4hMDtlPWUudGltZWxpbmV9cmV0dXJuITF9LGYuc2hpZnRDaGlsZHJlbj1mdW5jdGlvbih0LGUsaSl7aT1pfHwwO2Zvcih2YXIgcyxyPXRoaXMuX2ZpcnN0LG49dGhpcy5fbGFiZWxzO3I7KXIuX3N0YXJ0VGltZT49aSYmKHIuX3N0YXJ0VGltZSs9dCkscj1yLl9uZXh0O2lmKGUpZm9yKHMgaW4gbiluW3NdPj1pJiYobltzXSs9dCk7cmV0dXJuIHRoaXMuX3VuY2FjaGUoITApfSxmLl9raWxsPWZ1bmN0aW9uKHQsZSl7aWYoIXQmJiFlKXJldHVybiB0aGlzLl9lbmFibGVkKCExLCExKTtmb3IodmFyIGk9ZT90aGlzLmdldFR3ZWVuc09mKGUpOnRoaXMuZ2V0Q2hpbGRyZW4oITAsITAsITEpLHM9aS5sZW5ndGgscj0hMTstLXM+LTE7KWlbc10uX2tpbGwodCxlKSYmKHI9ITApO3JldHVybiByfSxmLmNsZWFyPWZ1bmN0aW9uKHQpe3ZhciBlPXRoaXMuZ2V0Q2hpbGRyZW4oITEsITAsITApLGk9ZS5sZW5ndGg7Zm9yKHRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPTA7LS1pPi0xOyllW2ldLl9lbmFibGVkKCExLCExKTtyZXR1cm4gdCE9PSExJiYodGhpcy5fbGFiZWxzPXt9KSx0aGlzLl91bmNhY2hlKCEwKX0sZi5pbnZhbGlkYXRlPWZ1bmN0aW9uKCl7Zm9yKHZhciB0PXRoaXMuX2ZpcnN0O3Q7KXQuaW52YWxpZGF0ZSgpLHQ9dC5fbmV4dDtyZXR1cm4gdGhpc30sZi5fZW5hYmxlZD1mdW5jdGlvbih0LGkpe2lmKHQ9PT10aGlzLl9nYylmb3IodmFyIHM9dGhpcy5fZmlyc3Q7czspcy5fZW5hYmxlZCh0LCEwKSxzPXMuX25leHQ7cmV0dXJuIGUucHJvdG90eXBlLl9lbmFibGVkLmNhbGwodGhpcyx0LGkpfSxmLmR1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPygwIT09dGhpcy5kdXJhdGlvbigpJiYwIT09dCYmdGhpcy50aW1lU2NhbGUodGhpcy5fZHVyYXRpb24vdCksdGhpcyk6KHRoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKSx0aGlzLl9kdXJhdGlvbil9LGYudG90YWxEdXJhdGlvbj1mdW5jdGlvbih0KXtpZighYXJndW1lbnRzLmxlbmd0aCl7aWYodGhpcy5fZGlydHkpe2Zvcih2YXIgZSxpLHM9MCxyPXRoaXMuX2xhc3Qsbj05OTk5OTk5OTk5OTk7cjspZT1yLl9wcmV2LHIuX2RpcnR5JiZyLnRvdGFsRHVyYXRpb24oKSxyLl9zdGFydFRpbWU+biYmdGhpcy5fc29ydENoaWxkcmVuJiYhci5fcGF1c2VkP3RoaXMuYWRkKHIsci5fc3RhcnRUaW1lLXIuX2RlbGF5KTpuPXIuX3N0YXJ0VGltZSwwPnIuX3N0YXJ0VGltZSYmIXIuX3BhdXNlZCYmKHMtPXIuX3N0YXJ0VGltZSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyYmKHRoaXMuX3N0YXJ0VGltZSs9ci5fc3RhcnRUaW1lL3RoaXMuX3RpbWVTY2FsZSksdGhpcy5zaGlmdENoaWxkcmVuKC1yLl9zdGFydFRpbWUsITEsLTk5OTk5OTk5OTkpLG49MCksaT1yLl9zdGFydFRpbWUrci5fdG90YWxEdXJhdGlvbi9yLl90aW1lU2NhbGUsaT5zJiYocz1pKSxyPWU7dGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj1zLHRoaXMuX2RpcnR5PSExfXJldHVybiB0aGlzLl90b3RhbER1cmF0aW9ufXJldHVybiAwIT09dGhpcy50b3RhbER1cmF0aW9uKCkmJjAhPT10JiZ0aGlzLnRpbWVTY2FsZSh0aGlzLl90b3RhbER1cmF0aW9uL3QpLHRoaXN9LGYudXNlc0ZyYW1lcz1mdW5jdGlvbigpe2Zvcih2YXIgZT10aGlzLl90aW1lbGluZTtlLl90aW1lbGluZTspZT1lLl90aW1lbGluZTtyZXR1cm4gZT09PXQuX3Jvb3RGcmFtZXNUaW1lbGluZX0sZi5yYXdUaW1lPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3BhdXNlZD90aGlzLl90b3RhbFRpbWU6KHRoaXMuX3RpbWVsaW5lLnJhd1RpbWUoKS10aGlzLl9zdGFydFRpbWUpKnRoaXMuX3RpbWVTY2FsZX0sc30sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKGZ1bmN0aW9uKHQpe1widXNlIHN0cmljdFwiO3ZhciBlPXQuR3JlZW5Tb2NrR2xvYmFsc3x8dDtpZighZS5Ud2VlbkxpdGUpe3ZhciBpLHMsbixyLGEsbz1mdW5jdGlvbih0KXt2YXIgaSxzPXQuc3BsaXQoXCIuXCIpLG49ZTtmb3IoaT0wO3MubGVuZ3RoPmk7aSsrKW5bc1tpXV09bj1uW3NbaV1dfHx7fTtyZXR1cm4gbn0sbD1vKFwiY29tLmdyZWVuc29ja1wiKSxoPTFlLTEwLF89W10uc2xpY2UsdT1mdW5jdGlvbigpe30sbT1mdW5jdGlvbigpe3ZhciB0PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcsZT10LmNhbGwoW10pO3JldHVybiBmdW5jdGlvbihpKXtyZXR1cm4gbnVsbCE9aSYmKGkgaW5zdGFuY2VvZiBBcnJheXx8XCJvYmplY3RcIj09dHlwZW9mIGkmJiEhaS5wdXNoJiZ0LmNhbGwoaSk9PT1lKX19KCksZj17fSxwPWZ1bmN0aW9uKGkscyxuLHIpe3RoaXMuc2M9ZltpXT9mW2ldLnNjOltdLGZbaV09dGhpcyx0aGlzLmdzQ2xhc3M9bnVsbCx0aGlzLmZ1bmM9bjt2YXIgYT1bXTt0aGlzLmNoZWNrPWZ1bmN0aW9uKGwpe2Zvcih2YXIgaCxfLHUsbSxjPXMubGVuZ3RoLGQ9YzstLWM+LTE7KShoPWZbc1tjXV18fG5ldyBwKHNbY10sW10pKS5nc0NsYXNzPyhhW2NdPWguZ3NDbGFzcyxkLS0pOmwmJmguc2MucHVzaCh0aGlzKTtpZigwPT09ZCYmbilmb3IoXz0oXCJjb20uZ3JlZW5zb2NrLlwiK2kpLnNwbGl0KFwiLlwiKSx1PV8ucG9wKCksbT1vKF8uam9pbihcIi5cIikpW3VdPXRoaXMuZ3NDbGFzcz1uLmFwcGx5KG4sYSksciYmKGVbdV09bSxcImZ1bmN0aW9uXCI9PXR5cGVvZiBkZWZpbmUmJmRlZmluZS5hbWQ/ZGVmaW5lKCh0LkdyZWVuU29ja0FNRFBhdGg/dC5HcmVlblNvY2tBTURQYXRoK1wiL1wiOlwiXCIpK2kuc3BsaXQoXCIuXCIpLmpvaW4oXCIvXCIpLFtdLGZ1bmN0aW9uKCl7cmV0dXJuIG19KTpcInVuZGVmaW5lZFwiIT10eXBlb2YgbW9kdWxlJiZtb2R1bGUuZXhwb3J0cyYmKG1vZHVsZS5leHBvcnRzPW0pKSxjPTA7dGhpcy5zYy5sZW5ndGg+YztjKyspdGhpcy5zY1tjXS5jaGVjaygpfSx0aGlzLmNoZWNrKCEwKX0sYz10Ll9nc0RlZmluZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gbmV3IHAodCxlLGkscyl9LGQ9bC5fY2xhc3M9ZnVuY3Rpb24odCxlLGkpe3JldHVybiBlPWV8fGZ1bmN0aW9uKCl7fSxjKHQsW10sZnVuY3Rpb24oKXtyZXR1cm4gZX0saSksZX07Yy5nbG9iYWxzPWU7dmFyIHY9WzAsMCwxLDFdLGc9W10sVD1kKFwiZWFzaW5nLkVhc2VcIixmdW5jdGlvbih0LGUsaSxzKXt0aGlzLl9mdW5jPXQsdGhpcy5fdHlwZT1pfHwwLHRoaXMuX3Bvd2VyPXN8fDAsdGhpcy5fcGFyYW1zPWU/di5jb25jYXQoZSk6dn0sITApLHk9VC5tYXA9e30sdz1ULnJlZ2lzdGVyPWZ1bmN0aW9uKHQsZSxpLHMpe2Zvcih2YXIgbixyLGEsbyxoPWUuc3BsaXQoXCIsXCIpLF89aC5sZW5ndGgsdT0oaXx8XCJlYXNlSW4sZWFzZU91dCxlYXNlSW5PdXRcIikuc3BsaXQoXCIsXCIpOy0tXz4tMTspZm9yKHI9aFtfXSxuPXM/ZChcImVhc2luZy5cIityLG51bGwsITApOmwuZWFzaW5nW3JdfHx7fSxhPXUubGVuZ3RoOy0tYT4tMTspbz11W2FdLHlbcitcIi5cIitvXT15W28rcl09bltvXT10LmdldFJhdGlvP3Q6dFtvXXx8bmV3IHR9O2ZvcihuPVQucHJvdG90eXBlLG4uX2NhbGNFbmQ9ITEsbi5nZXRSYXRpbz1mdW5jdGlvbih0KXtpZih0aGlzLl9mdW5jKXJldHVybiB0aGlzLl9wYXJhbXNbMF09dCx0aGlzLl9mdW5jLmFwcGx5KG51bGwsdGhpcy5fcGFyYW1zKTt2YXIgZT10aGlzLl90eXBlLGk9dGhpcy5fcG93ZXIscz0xPT09ZT8xLXQ6Mj09PWU/dDouNT50PzIqdDoyKigxLXQpO3JldHVybiAxPT09aT9zKj1zOjI9PT1pP3MqPXMqczozPT09aT9zKj1zKnMqczo0PT09aSYmKHMqPXMqcypzKnMpLDE9PT1lPzEtczoyPT09ZT9zOi41PnQ/cy8yOjEtcy8yfSxpPVtcIkxpbmVhclwiLFwiUXVhZFwiLFwiQ3ViaWNcIixcIlF1YXJ0XCIsXCJRdWludCxTdHJvbmdcIl0scz1pLmxlbmd0aDstLXM+LTE7KW49aVtzXStcIixQb3dlclwiK3MsdyhuZXcgVChudWxsLG51bGwsMSxzKSxuLFwiZWFzZU91dFwiLCEwKSx3KG5ldyBUKG51bGwsbnVsbCwyLHMpLG4sXCJlYXNlSW5cIisoMD09PXM/XCIsZWFzZU5vbmVcIjpcIlwiKSksdyhuZXcgVChudWxsLG51bGwsMyxzKSxuLFwiZWFzZUluT3V0XCIpO3kubGluZWFyPWwuZWFzaW5nLkxpbmVhci5lYXNlSW4seS5zd2luZz1sLmVhc2luZy5RdWFkLmVhc2VJbk91dDt2YXIgUD1kKFwiZXZlbnRzLkV2ZW50RGlzcGF0Y2hlclwiLGZ1bmN0aW9uKHQpe3RoaXMuX2xpc3RlbmVycz17fSx0aGlzLl9ldmVudFRhcmdldD10fHx0aGlzfSk7bj1QLnByb3RvdHlwZSxuLmFkZEV2ZW50TGlzdGVuZXI9ZnVuY3Rpb24odCxlLGkscyxuKXtuPW58fDA7dmFyIG8sbCxoPXRoaXMuX2xpc3RlbmVyc1t0XSxfPTA7Zm9yKG51bGw9PWgmJih0aGlzLl9saXN0ZW5lcnNbdF09aD1bXSksbD1oLmxlbmd0aDstLWw+LTE7KW89aFtsXSxvLmM9PT1lJiZvLnM9PT1pP2guc3BsaWNlKGwsMSk6MD09PV8mJm4+by5wciYmKF89bCsxKTtoLnNwbGljZShfLDAse2M6ZSxzOmksdXA6cyxwcjpufSksdGhpcyE9PXJ8fGF8fHIud2FrZSgpfSxuLnJlbW92ZUV2ZW50TGlzdGVuZXI9ZnVuY3Rpb24odCxlKXt2YXIgaSxzPXRoaXMuX2xpc3RlbmVyc1t0XTtpZihzKWZvcihpPXMubGVuZ3RoOy0taT4tMTspaWYoc1tpXS5jPT09ZSlyZXR1cm4gcy5zcGxpY2UoaSwxKSx2b2lkIDB9LG4uZGlzcGF0Y2hFdmVudD1mdW5jdGlvbih0KXt2YXIgZSxpLHMsbj10aGlzLl9saXN0ZW5lcnNbdF07aWYobilmb3IoZT1uLmxlbmd0aCxpPXRoaXMuX2V2ZW50VGFyZ2V0Oy0tZT4tMTspcz1uW2VdLHMudXA/cy5jLmNhbGwocy5zfHxpLHt0eXBlOnQsdGFyZ2V0Oml9KTpzLmMuY2FsbChzLnN8fGkpfTt2YXIgaz10LnJlcXVlc3RBbmltYXRpb25GcmFtZSxiPXQuY2FuY2VsQW5pbWF0aW9uRnJhbWUsQT1EYXRlLm5vd3x8ZnVuY3Rpb24oKXtyZXR1cm4obmV3IERhdGUpLmdldFRpbWUoKX0sUz1BKCk7Zm9yKGk9W1wibXNcIixcIm1velwiLFwid2Via2l0XCIsXCJvXCJdLHM9aS5sZW5ndGg7LS1zPi0xJiYhazspaz10W2lbc10rXCJSZXF1ZXN0QW5pbWF0aW9uRnJhbWVcIl0sYj10W2lbc10rXCJDYW5jZWxBbmltYXRpb25GcmFtZVwiXXx8dFtpW3NdK1wiQ2FuY2VsUmVxdWVzdEFuaW1hdGlvbkZyYW1lXCJdO2QoXCJUaWNrZXJcIixmdW5jdGlvbih0LGUpe3ZhciBpLHMsbixvLGwsXz10aGlzLG09QSgpLGY9ZSE9PSExJiZrLHA9NTAwLGM9MzMsZD1mdW5jdGlvbih0KXt2YXIgZSxyLGE9QSgpLVM7YT5wJiYobSs9YS1jKSxTKz1hLF8udGltZT0oUy1tKS8xZTMsZT1fLnRpbWUtbCwoIWl8fGU+MHx8dD09PSEwKSYmKF8uZnJhbWUrKyxsKz1lKyhlPj1vPy4wMDQ6by1lKSxyPSEwKSx0IT09ITAmJihuPXMoZCkpLHImJl8uZGlzcGF0Y2hFdmVudChcInRpY2tcIil9O1AuY2FsbChfKSxfLnRpbWU9Xy5mcmFtZT0wLF8udGljaz1mdW5jdGlvbigpe2QoITApfSxfLmxhZ1Ntb290aGluZz1mdW5jdGlvbih0LGUpe3A9dHx8MS9oLGM9TWF0aC5taW4oZSxwLDApfSxfLnNsZWVwPWZ1bmN0aW9uKCl7bnVsbCE9biYmKGYmJmI/YihuKTpjbGVhclRpbWVvdXQobikscz11LG49bnVsbCxfPT09ciYmKGE9ITEpKX0sXy53YWtlPWZ1bmN0aW9uKCl7bnVsbCE9PW4/Xy5zbGVlcCgpOl8uZnJhbWU+MTAmJihTPUEoKS1wKzUpLHM9MD09PWk/dTpmJiZrP2s6ZnVuY3Rpb24odCl7cmV0dXJuIHNldFRpbWVvdXQodCwwfDFlMyoobC1fLnRpbWUpKzEpfSxfPT09ciYmKGE9ITApLGQoMil9LF8uZnBzPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyhpPXQsbz0xLyhpfHw2MCksbD10aGlzLnRpbWUrbyxfLndha2UoKSx2b2lkIDApOml9LF8udXNlUkFGPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyhfLnNsZWVwKCksZj10LF8uZnBzKGkpLHZvaWQgMCk6Zn0sXy5mcHModCksc2V0VGltZW91dChmdW5jdGlvbigpe2YmJighbnx8NT5fLmZyYW1lKSYmXy51c2VSQUYoITEpfSwxNTAwKX0pLG49bC5UaWNrZXIucHJvdG90eXBlPW5ldyBsLmV2ZW50cy5FdmVudERpc3BhdGNoZXIsbi5jb25zdHJ1Y3Rvcj1sLlRpY2tlcjt2YXIgeD1kKFwiY29yZS5BbmltYXRpb25cIixmdW5jdGlvbih0LGUpe2lmKHRoaXMudmFycz1lPWV8fHt9LHRoaXMuX2R1cmF0aW9uPXRoaXMuX3RvdGFsRHVyYXRpb249dHx8MCx0aGlzLl9kZWxheT1OdW1iZXIoZS5kZWxheSl8fDAsdGhpcy5fdGltZVNjYWxlPTEsdGhpcy5fYWN0aXZlPWUuaW1tZWRpYXRlUmVuZGVyPT09ITAsdGhpcy5kYXRhPWUuZGF0YSx0aGlzLl9yZXZlcnNlZD1lLnJldmVyc2VkPT09ITAsQil7YXx8ci53YWtlKCk7dmFyIGk9dGhpcy52YXJzLnVzZUZyYW1lcz9ROkI7aS5hZGQodGhpcyxpLl90aW1lKSx0aGlzLnZhcnMucGF1c2VkJiZ0aGlzLnBhdXNlZCghMCl9fSk7cj14LnRpY2tlcj1uZXcgbC5UaWNrZXIsbj14LnByb3RvdHlwZSxuLl9kaXJ0eT1uLl9nYz1uLl9pbml0dGVkPW4uX3BhdXNlZD0hMSxuLl90b3RhbFRpbWU9bi5fdGltZT0wLG4uX3Jhd1ByZXZUaW1lPS0xLG4uX25leHQ9bi5fbGFzdD1uLl9vblVwZGF0ZT1uLl90aW1lbGluZT1uLnRpbWVsaW5lPW51bGwsbi5fcGF1c2VkPSExO3ZhciBDPWZ1bmN0aW9uKCl7YSYmQSgpLVM+MmUzJiZyLndha2UoKSxzZXRUaW1lb3V0KEMsMmUzKX07QygpLG4ucGxheT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnJldmVyc2VkKCExKS5wYXVzZWQoITEpfSxuLnBhdXNlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucGF1c2VkKCEwKX0sbi5yZXN1bWU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5wYXVzZWQoITEpfSxuLnNlZWs9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy50b3RhbFRpbWUoTnVtYmVyKHQpLGUhPT0hMSl9LG4ucmVzdGFydD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnJldmVyc2VkKCExKS5wYXVzZWQoITEpLnRvdGFsVGltZSh0Py10aGlzLl9kZWxheTowLGUhPT0hMSwhMCl9LG4ucmV2ZXJzZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodHx8dGhpcy50b3RhbER1cmF0aW9uKCksZSksdGhpcy5yZXZlcnNlZCghMCkucGF1c2VkKCExKX0sbi5yZW5kZXI9ZnVuY3Rpb24oKXt9LG4uaW52YWxpZGF0ZT1mdW5jdGlvbigpe3JldHVybiB0aGlzfSxuLmlzQWN0aXZlPWZ1bmN0aW9uKCl7dmFyIHQsZT10aGlzLl90aW1lbGluZSxpPXRoaXMuX3N0YXJ0VGltZTtyZXR1cm4hZXx8IXRoaXMuX2djJiYhdGhpcy5fcGF1c2VkJiZlLmlzQWN0aXZlKCkmJih0PWUucmF3VGltZSgpKT49aSYmaSt0aGlzLnRvdGFsRHVyYXRpb24oKS90aGlzLl90aW1lU2NhbGU+dH0sbi5fZW5hYmxlZD1mdW5jdGlvbih0LGUpe3JldHVybiBhfHxyLndha2UoKSx0aGlzLl9nYz0hdCx0aGlzLl9hY3RpdmU9dGhpcy5pc0FjdGl2ZSgpLGUhPT0hMCYmKHQmJiF0aGlzLnRpbWVsaW5lP3RoaXMuX3RpbWVsaW5lLmFkZCh0aGlzLHRoaXMuX3N0YXJ0VGltZS10aGlzLl9kZWxheSk6IXQmJnRoaXMudGltZWxpbmUmJnRoaXMuX3RpbWVsaW5lLl9yZW1vdmUodGhpcywhMCkpLCExfSxuLl9raWxsPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX2VuYWJsZWQoITEsITEpfSxuLmtpbGw9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5fa2lsbCh0LGUpLHRoaXN9LG4uX3VuY2FjaGU9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQ/dGhpczp0aGlzLnRpbWVsaW5lO2U7KWUuX2RpcnR5PSEwLGU9ZS50aW1lbGluZTtyZXR1cm4gdGhpc30sbi5fc3dhcFNlbGZJblBhcmFtcz1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC5sZW5ndGgsaT10LmNvbmNhdCgpOy0tZT4tMTspXCJ7c2VsZn1cIj09PXRbZV0mJihpW2VdPXRoaXMpO3JldHVybiBpfSxuLmV2ZW50Q2FsbGJhY2s9ZnVuY3Rpb24odCxlLGkscyl7aWYoXCJvblwiPT09KHR8fFwiXCIpLnN1YnN0cigwLDIpKXt2YXIgbj10aGlzLnZhcnM7aWYoMT09PWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIG5bdF07bnVsbD09ZT9kZWxldGUgblt0XTooblt0XT1lLG5bdCtcIlBhcmFtc1wiXT1tKGkpJiYtMSE9PWkuam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpP3RoaXMuX3N3YXBTZWxmSW5QYXJhbXMoaSk6aSxuW3QrXCJTY29wZVwiXT1zKSxcIm9uVXBkYXRlXCI9PT10JiYodGhpcy5fb25VcGRhdGU9ZSl9cmV0dXJuIHRoaXN9LG4uZGVsYXk9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nJiZ0aGlzLnN0YXJ0VGltZSh0aGlzLl9zdGFydFRpbWUrdC10aGlzLl9kZWxheSksdGhpcy5fZGVsYXk9dCx0aGlzKTp0aGlzLl9kZWxheX0sbi5kdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj10LHRoaXMuX3VuY2FjaGUoITApLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nJiZ0aGlzLl90aW1lPjAmJnRoaXMuX3RpbWU8dGhpcy5fZHVyYXRpb24mJjAhPT10JiZ0aGlzLnRvdGFsVGltZSh0aGlzLl90b3RhbFRpbWUqKHQvdGhpcy5fZHVyYXRpb24pLCEwKSx0aGlzKToodGhpcy5fZGlydHk9ITEsdGhpcy5fZHVyYXRpb24pfSxuLnRvdGFsRHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIHRoaXMuX2RpcnR5PSExLGFyZ3VtZW50cy5sZW5ndGg/dGhpcy5kdXJhdGlvbih0KTp0aGlzLl90b3RhbER1cmF0aW9ufSxuLnRpbWU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odGhpcy5fZGlydHkmJnRoaXMudG90YWxEdXJhdGlvbigpLHRoaXMudG90YWxUaW1lKHQ+dGhpcy5fZHVyYXRpb24/dGhpcy5fZHVyYXRpb246dCxlKSk6dGhpcy5fdGltZX0sbi50b3RhbFRpbWU9ZnVuY3Rpb24odCxlLGkpe2lmKGF8fHIud2FrZSgpLCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl90b3RhbFRpbWU7aWYodGhpcy5fdGltZWxpbmUpe2lmKDA+dCYmIWkmJih0Kz10aGlzLnRvdGFsRHVyYXRpb24oKSksdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcpe3RoaXMuX2RpcnR5JiZ0aGlzLnRvdGFsRHVyYXRpb24oKTt2YXIgcz10aGlzLl90b3RhbER1cmF0aW9uLG49dGhpcy5fdGltZWxpbmU7aWYodD5zJiYhaSYmKHQ9cyksdGhpcy5fc3RhcnRUaW1lPSh0aGlzLl9wYXVzZWQ/dGhpcy5fcGF1c2VUaW1lOm4uX3RpbWUpLSh0aGlzLl9yZXZlcnNlZD9zLXQ6dCkvdGhpcy5fdGltZVNjYWxlLG4uX2RpcnR5fHx0aGlzLl91bmNhY2hlKCExKSxuLl90aW1lbGluZSlmb3IoO24uX3RpbWVsaW5lOyluLl90aW1lbGluZS5fdGltZSE9PShuLl9zdGFydFRpbWUrbi5fdG90YWxUaW1lKS9uLl90aW1lU2NhbGUmJm4udG90YWxUaW1lKG4uX3RvdGFsVGltZSwhMCksbj1uLl90aW1lbGluZX10aGlzLl9nYyYmdGhpcy5fZW5hYmxlZCghMCwhMSksKHRoaXMuX3RvdGFsVGltZSE9PXR8fDA9PT10aGlzLl9kdXJhdGlvbikmJih0aGlzLnJlbmRlcih0LGUsITEpLHoubGVuZ3RoJiZxKCkpfXJldHVybiB0aGlzfSxuLnByb2dyZXNzPW4udG90YWxQcm9ncmVzcz1mdW5jdGlvbih0LGUpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoP3RoaXMudG90YWxUaW1lKHRoaXMuZHVyYXRpb24oKSp0LGUpOnRoaXMuX3RpbWUvdGhpcy5kdXJhdGlvbigpfSxuLnN0YXJ0VGltZT1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odCE9PXRoaXMuX3N0YXJ0VGltZSYmKHRoaXMuX3N0YXJ0VGltZT10LHRoaXMudGltZWxpbmUmJnRoaXMudGltZWxpbmUuX3NvcnRDaGlsZHJlbiYmdGhpcy50aW1lbGluZS5hZGQodGhpcyx0LXRoaXMuX2RlbGF5KSksdGhpcyk6dGhpcy5fc3RhcnRUaW1lfSxuLnRpbWVTY2FsZT1mdW5jdGlvbih0KXtpZighYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fdGltZVNjYWxlO2lmKHQ9dHx8aCx0aGlzLl90aW1lbGluZSYmdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcpe3ZhciBlPXRoaXMuX3BhdXNlVGltZSxpPWV8fDA9PT1lP2U6dGhpcy5fdGltZWxpbmUudG90YWxUaW1lKCk7dGhpcy5fc3RhcnRUaW1lPWktKGktdGhpcy5fc3RhcnRUaW1lKSp0aGlzLl90aW1lU2NhbGUvdH1yZXR1cm4gdGhpcy5fdGltZVNjYWxlPXQsdGhpcy5fdW5jYWNoZSghMSl9LG4ucmV2ZXJzZWQ9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KHQhPXRoaXMuX3JldmVyc2VkJiYodGhpcy5fcmV2ZXJzZWQ9dCx0aGlzLnRvdGFsVGltZSh0aGlzLl90aW1lbGluZSYmIXRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nP3RoaXMudG90YWxEdXJhdGlvbigpLXRoaXMuX3RvdGFsVGltZTp0aGlzLl90b3RhbFRpbWUsITApKSx0aGlzKTp0aGlzLl9yZXZlcnNlZH0sbi5wYXVzZWQ9ZnVuY3Rpb24odCl7aWYoIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3BhdXNlZDtpZih0IT10aGlzLl9wYXVzZWQmJnRoaXMuX3RpbWVsaW5lKXthfHx0fHxyLndha2UoKTt2YXIgZT10aGlzLl90aW1lbGluZSxpPWUucmF3VGltZSgpLHM9aS10aGlzLl9wYXVzZVRpbWU7IXQmJmUuc21vb3RoQ2hpbGRUaW1pbmcmJih0aGlzLl9zdGFydFRpbWUrPXMsdGhpcy5fdW5jYWNoZSghMSkpLHRoaXMuX3BhdXNlVGltZT10P2k6bnVsbCx0aGlzLl9wYXVzZWQ9dCx0aGlzLl9hY3RpdmU9dGhpcy5pc0FjdGl2ZSgpLCF0JiYwIT09cyYmdGhpcy5faW5pdHRlZCYmdGhpcy5kdXJhdGlvbigpJiZ0aGlzLnJlbmRlcihlLnNtb290aENoaWxkVGltaW5nP3RoaXMuX3RvdGFsVGltZTooaS10aGlzLl9zdGFydFRpbWUpL3RoaXMuX3RpbWVTY2FsZSwhMCwhMCl9cmV0dXJuIHRoaXMuX2djJiYhdCYmdGhpcy5fZW5hYmxlZCghMCwhMSksdGhpc307dmFyIFI9ZChcImNvcmUuU2ltcGxlVGltZWxpbmVcIixmdW5jdGlvbih0KXt4LmNhbGwodGhpcywwLHQpLHRoaXMuYXV0b1JlbW92ZUNoaWxkcmVuPXRoaXMuc21vb3RoQ2hpbGRUaW1pbmc9ITB9KTtuPVIucHJvdG90eXBlPW5ldyB4LG4uY29uc3RydWN0b3I9UixuLmtpbGwoKS5fZ2M9ITEsbi5fZmlyc3Q9bi5fbGFzdD1udWxsLG4uX3NvcnRDaGlsZHJlbj0hMSxuLmFkZD1uLmluc2VydD1mdW5jdGlvbih0LGUpe3ZhciBpLHM7aWYodC5fc3RhcnRUaW1lPU51bWJlcihlfHwwKSt0Ll9kZWxheSx0Ll9wYXVzZWQmJnRoaXMhPT10Ll90aW1lbGluZSYmKHQuX3BhdXNlVGltZT10Ll9zdGFydFRpbWUrKHRoaXMucmF3VGltZSgpLXQuX3N0YXJ0VGltZSkvdC5fdGltZVNjYWxlKSx0LnRpbWVsaW5lJiZ0LnRpbWVsaW5lLl9yZW1vdmUodCwhMCksdC50aW1lbGluZT10Ll90aW1lbGluZT10aGlzLHQuX2djJiZ0Ll9lbmFibGVkKCEwLCEwKSxpPXRoaXMuX2xhc3QsdGhpcy5fc29ydENoaWxkcmVuKWZvcihzPXQuX3N0YXJ0VGltZTtpJiZpLl9zdGFydFRpbWU+czspaT1pLl9wcmV2O3JldHVybiBpPyh0Ll9uZXh0PWkuX25leHQsaS5fbmV4dD10KToodC5fbmV4dD10aGlzLl9maXJzdCx0aGlzLl9maXJzdD10KSx0Ll9uZXh0P3QuX25leHQuX3ByZXY9dDp0aGlzLl9sYXN0PXQsdC5fcHJldj1pLHRoaXMuX3RpbWVsaW5lJiZ0aGlzLl91bmNhY2hlKCEwKSx0aGlzfSxuLl9yZW1vdmU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdC50aW1lbGluZT09PXRoaXMmJihlfHx0Ll9lbmFibGVkKCExLCEwKSx0LnRpbWVsaW5lPW51bGwsdC5fcHJldj90Ll9wcmV2Ll9uZXh0PXQuX25leHQ6dGhpcy5fZmlyc3Q9PT10JiYodGhpcy5fZmlyc3Q9dC5fbmV4dCksdC5fbmV4dD90Ll9uZXh0Ll9wcmV2PXQuX3ByZXY6dGhpcy5fbGFzdD09PXQmJih0aGlzLl9sYXN0PXQuX3ByZXYpLHRoaXMuX3RpbWVsaW5lJiZ0aGlzLl91bmNhY2hlKCEwKSksdGhpc30sbi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3ZhciBzLG49dGhpcy5fZmlyc3Q7Zm9yKHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPXRoaXMuX3Jhd1ByZXZUaW1lPXQ7bjspcz1uLl9uZXh0LChuLl9hY3RpdmV8fHQ+PW4uX3N0YXJ0VGltZSYmIW4uX3BhdXNlZCkmJihuLl9yZXZlcnNlZD9uLnJlbmRlcigobi5fZGlydHk/bi50b3RhbER1cmF0aW9uKCk6bi5fdG90YWxEdXJhdGlvbiktKHQtbi5fc3RhcnRUaW1lKSpuLl90aW1lU2NhbGUsZSxpKTpuLnJlbmRlcigodC1uLl9zdGFydFRpbWUpKm4uX3RpbWVTY2FsZSxlLGkpKSxuPXN9LG4ucmF3VGltZT1mdW5jdGlvbigpe3JldHVybiBhfHxyLndha2UoKSx0aGlzLl90b3RhbFRpbWV9O3ZhciBEPWQoXCJUd2VlbkxpdGVcIixmdW5jdGlvbihlLGkscyl7aWYoeC5jYWxsKHRoaXMsaSxzKSx0aGlzLnJlbmRlcj1ELnByb3RvdHlwZS5yZW5kZXIsbnVsbD09ZSl0aHJvd1wiQ2Fubm90IHR3ZWVuIGEgbnVsbCB0YXJnZXQuXCI7dGhpcy50YXJnZXQ9ZT1cInN0cmluZ1wiIT10eXBlb2YgZT9lOkQuc2VsZWN0b3IoZSl8fGU7dmFyIG4scixhLG89ZS5qcXVlcnl8fGUubGVuZ3RoJiZlIT09dCYmZVswXSYmKGVbMF09PT10fHxlWzBdLm5vZGVUeXBlJiZlWzBdLnN0eWxlJiYhZS5ub2RlVHlwZSksbD10aGlzLnZhcnMub3ZlcndyaXRlO2lmKHRoaXMuX292ZXJ3cml0ZT1sPW51bGw9PWw/R1tELmRlZmF1bHRPdmVyd3JpdGVdOlwibnVtYmVyXCI9PXR5cGVvZiBsP2w+PjA6R1tsXSwob3x8ZSBpbnN0YW5jZW9mIEFycmF5fHxlLnB1c2gmJm0oZSkpJiZcIm51bWJlclwiIT10eXBlb2YgZVswXSlmb3IodGhpcy5fdGFyZ2V0cz1hPV8uY2FsbChlLDApLHRoaXMuX3Byb3BMb29rdXA9W10sdGhpcy5fc2libGluZ3M9W10sbj0wO2EubGVuZ3RoPm47bisrKXI9YVtuXSxyP1wic3RyaW5nXCIhPXR5cGVvZiByP3IubGVuZ3RoJiZyIT09dCYmclswXSYmKHJbMF09PT10fHxyWzBdLm5vZGVUeXBlJiZyWzBdLnN0eWxlJiYhci5ub2RlVHlwZSk/KGEuc3BsaWNlKG4tLSwxKSx0aGlzLl90YXJnZXRzPWE9YS5jb25jYXQoXy5jYWxsKHIsMCkpKToodGhpcy5fc2libGluZ3Nbbl09TShyLHRoaXMsITEpLDE9PT1sJiZ0aGlzLl9zaWJsaW5nc1tuXS5sZW5ndGg+MSYmJChyLHRoaXMsbnVsbCwxLHRoaXMuX3NpYmxpbmdzW25dKSk6KHI9YVtuLS1dPUQuc2VsZWN0b3IociksXCJzdHJpbmdcIj09dHlwZW9mIHImJmEuc3BsaWNlKG4rMSwxKSk6YS5zcGxpY2Uobi0tLDEpO2Vsc2UgdGhpcy5fcHJvcExvb2t1cD17fSx0aGlzLl9zaWJsaW5ncz1NKGUsdGhpcywhMSksMT09PWwmJnRoaXMuX3NpYmxpbmdzLmxlbmd0aD4xJiYkKGUsdGhpcyxudWxsLDEsdGhpcy5fc2libGluZ3MpOyh0aGlzLnZhcnMuaW1tZWRpYXRlUmVuZGVyfHwwPT09aSYmMD09PXRoaXMuX2RlbGF5JiZ0aGlzLnZhcnMuaW1tZWRpYXRlUmVuZGVyIT09ITEpJiYodGhpcy5fdGltZT0taCx0aGlzLnJlbmRlcigtdGhpcy5fZGVsYXkpKX0sITApLEk9ZnVuY3Rpb24oZSl7cmV0dXJuIGUubGVuZ3RoJiZlIT09dCYmZVswXSYmKGVbMF09PT10fHxlWzBdLm5vZGVUeXBlJiZlWzBdLnN0eWxlJiYhZS5ub2RlVHlwZSl9LEU9ZnVuY3Rpb24odCxlKXt2YXIgaSxzPXt9O2ZvcihpIGluIHQpaltpXXx8aSBpbiBlJiZcInRyYW5zZm9ybVwiIT09aSYmXCJ4XCIhPT1pJiZcInlcIiE9PWkmJlwid2lkdGhcIiE9PWkmJlwiaGVpZ2h0XCIhPT1pJiZcImNsYXNzTmFtZVwiIT09aSYmXCJib3JkZXJcIiE9PWl8fCEoIUxbaV18fExbaV0mJkxbaV0uX2F1dG9DU1MpfHwoc1tpXT10W2ldLGRlbGV0ZSB0W2ldKTt0LmNzcz1zfTtuPUQucHJvdG90eXBlPW5ldyB4LG4uY29uc3RydWN0b3I9RCxuLmtpbGwoKS5fZ2M9ITEsbi5yYXRpbz0wLG4uX2ZpcnN0UFQ9bi5fdGFyZ2V0cz1uLl9vdmVyd3JpdHRlblByb3BzPW4uX3N0YXJ0QXQ9bnVsbCxuLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkPW4uX2xhenk9ITEsRC52ZXJzaW9uPVwiMS4xMi4xXCIsRC5kZWZhdWx0RWFzZT1uLl9lYXNlPW5ldyBUKG51bGwsbnVsbCwxLDEpLEQuZGVmYXVsdE92ZXJ3cml0ZT1cImF1dG9cIixELnRpY2tlcj1yLEQuYXV0b1NsZWVwPSEwLEQubGFnU21vb3RoaW5nPWZ1bmN0aW9uKHQsZSl7ci5sYWdTbW9vdGhpbmcodCxlKX0sRC5zZWxlY3Rvcj10LiR8fHQualF1ZXJ5fHxmdW5jdGlvbihlKXtyZXR1cm4gdC4kPyhELnNlbGVjdG9yPXQuJCx0LiQoZSkpOnQuZG9jdW1lbnQ/dC5kb2N1bWVudC5nZXRFbGVtZW50QnlJZChcIiNcIj09PWUuY2hhckF0KDApP2Uuc3Vic3RyKDEpOmUpOmV9O3ZhciB6PVtdLE89e30sTj1ELl9pbnRlcm5hbHM9e2lzQXJyYXk6bSxpc1NlbGVjdG9yOkksbGF6eVR3ZWVuczp6fSxMPUQuX3BsdWdpbnM9e30sVT1OLnR3ZWVuTG9va3VwPXt9LEY9MCxqPU4ucmVzZXJ2ZWRQcm9wcz17ZWFzZToxLGRlbGF5OjEsb3ZlcndyaXRlOjEsb25Db21wbGV0ZToxLG9uQ29tcGxldGVQYXJhbXM6MSxvbkNvbXBsZXRlU2NvcGU6MSx1c2VGcmFtZXM6MSxydW5CYWNrd2FyZHM6MSxzdGFydEF0OjEsb25VcGRhdGU6MSxvblVwZGF0ZVBhcmFtczoxLG9uVXBkYXRlU2NvcGU6MSxvblN0YXJ0OjEsb25TdGFydFBhcmFtczoxLG9uU3RhcnRTY29wZToxLG9uUmV2ZXJzZUNvbXBsZXRlOjEsb25SZXZlcnNlQ29tcGxldGVQYXJhbXM6MSxvblJldmVyc2VDb21wbGV0ZVNjb3BlOjEsb25SZXBlYXQ6MSxvblJlcGVhdFBhcmFtczoxLG9uUmVwZWF0U2NvcGU6MSxlYXNlUGFyYW1zOjEseW95bzoxLGltbWVkaWF0ZVJlbmRlcjoxLHJlcGVhdDoxLHJlcGVhdERlbGF5OjEsZGF0YToxLHBhdXNlZDoxLHJldmVyc2VkOjEsYXV0b0NTUzoxLGxhenk6MX0sRz17bm9uZTowLGFsbDoxLGF1dG86Mixjb25jdXJyZW50OjMsYWxsT25TdGFydDo0LHByZWV4aXN0aW5nOjUsXCJ0cnVlXCI6MSxcImZhbHNlXCI6MH0sUT14Ll9yb290RnJhbWVzVGltZWxpbmU9bmV3IFIsQj14Ll9yb290VGltZWxpbmU9bmV3IFIscT1mdW5jdGlvbigpe3ZhciB0PXoubGVuZ3RoO2ZvcihPPXt9Oy0tdD4tMTspaT16W3RdLGkmJmkuX2xhenkhPT0hMSYmKGkucmVuZGVyKGkuX2xhenksITEsITApLGkuX2xhenk9ITEpO3oubGVuZ3RoPTB9O0IuX3N0YXJ0VGltZT1yLnRpbWUsUS5fc3RhcnRUaW1lPXIuZnJhbWUsQi5fYWN0aXZlPVEuX2FjdGl2ZT0hMCxzZXRUaW1lb3V0KHEsMSkseC5fdXBkYXRlUm9vdD1ELnJlbmRlcj1mdW5jdGlvbigpe3ZhciB0LGUsaTtpZih6Lmxlbmd0aCYmcSgpLEIucmVuZGVyKChyLnRpbWUtQi5fc3RhcnRUaW1lKSpCLl90aW1lU2NhbGUsITEsITEpLFEucmVuZGVyKChyLmZyYW1lLVEuX3N0YXJ0VGltZSkqUS5fdGltZVNjYWxlLCExLCExKSx6Lmxlbmd0aCYmcSgpLCEoci5mcmFtZSUxMjApKXtmb3IoaSBpbiBVKXtmb3IoZT1VW2ldLnR3ZWVucyx0PWUubGVuZ3RoOy0tdD4tMTspZVt0XS5fZ2MmJmUuc3BsaWNlKHQsMSk7MD09PWUubGVuZ3RoJiZkZWxldGUgVVtpXX1pZihpPUIuX2ZpcnN0LCghaXx8aS5fcGF1c2VkKSYmRC5hdXRvU2xlZXAmJiFRLl9maXJzdCYmMT09PXIuX2xpc3RlbmVycy50aWNrLmxlbmd0aCl7Zm9yKDtpJiZpLl9wYXVzZWQ7KWk9aS5fbmV4dDtpfHxyLnNsZWVwKCl9fX0sci5hZGRFdmVudExpc3RlbmVyKFwidGlja1wiLHguX3VwZGF0ZVJvb3QpO3ZhciBNPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcyxuLHI9dC5fZ3NUd2VlbklEO2lmKFVbcnx8KHQuX2dzVHdlZW5JRD1yPVwidFwiK0YrKyldfHwoVVtyXT17dGFyZ2V0OnQsdHdlZW5zOltdfSksZSYmKHM9VVtyXS50d2VlbnMsc1tuPXMubGVuZ3RoXT1lLGkpKWZvcig7LS1uPi0xOylzW25dPT09ZSYmcy5zcGxpY2UobiwxKTtyZXR1cm4gVVtyXS50d2VlbnN9LCQ9ZnVuY3Rpb24odCxlLGkscyxuKXt2YXIgcixhLG8sbDtpZigxPT09c3x8cz49NCl7Zm9yKGw9bi5sZW5ndGgscj0wO2w+cjtyKyspaWYoKG89bltyXSkhPT1lKW8uX2djfHxvLl9lbmFibGVkKCExLCExKSYmKGE9ITApO2Vsc2UgaWYoNT09PXMpYnJlYWs7cmV0dXJuIGF9dmFyIF8sdT1lLl9zdGFydFRpbWUraCxtPVtdLGY9MCxwPTA9PT1lLl9kdXJhdGlvbjtmb3Iocj1uLmxlbmd0aDstLXI+LTE7KShvPW5bcl0pPT09ZXx8by5fZ2N8fG8uX3BhdXNlZHx8KG8uX3RpbWVsaW5lIT09ZS5fdGltZWxpbmU/KF89X3x8SyhlLDAscCksMD09PUsobyxfLHApJiYobVtmKytdPW8pKTp1Pj1vLl9zdGFydFRpbWUmJm8uX3N0YXJ0VGltZStvLnRvdGFsRHVyYXRpb24oKS9vLl90aW1lU2NhbGU+dSYmKChwfHwhby5faW5pdHRlZCkmJjJlLTEwPj11LW8uX3N0YXJ0VGltZXx8KG1bZisrXT1vKSkpO2ZvcihyPWY7LS1yPi0xOylvPW1bcl0sMj09PXMmJm8uX2tpbGwoaSx0KSYmKGE9ITApLCgyIT09c3x8IW8uX2ZpcnN0UFQmJm8uX2luaXR0ZWQpJiZvLl9lbmFibGVkKCExLCExKSYmKGE9ITApO3JldHVybiBhfSxLPWZ1bmN0aW9uKHQsZSxpKXtmb3IodmFyIHM9dC5fdGltZWxpbmUsbj1zLl90aW1lU2NhbGUscj10Ll9zdGFydFRpbWU7cy5fdGltZWxpbmU7KXtpZihyKz1zLl9zdGFydFRpbWUsbio9cy5fdGltZVNjYWxlLHMuX3BhdXNlZClyZXR1cm4tMTAwO3M9cy5fdGltZWxpbmV9cmV0dXJuIHIvPW4scj5lP3ItZTppJiZyPT09ZXx8IXQuX2luaXR0ZWQmJjIqaD5yLWU/aDoocis9dC50b3RhbER1cmF0aW9uKCkvdC5fdGltZVNjYWxlL24pPmUraD8wOnItZS1ofTtuLl9pbml0PWZ1bmN0aW9uKCl7dmFyIHQsZSxpLHMsbixyPXRoaXMudmFycyxhPXRoaXMuX292ZXJ3cml0dGVuUHJvcHMsbz10aGlzLl9kdXJhdGlvbixsPSEhci5pbW1lZGlhdGVSZW5kZXIsaD1yLmVhc2U7aWYoci5zdGFydEF0KXt0aGlzLl9zdGFydEF0JiYodGhpcy5fc3RhcnRBdC5yZW5kZXIoLTEsITApLHRoaXMuX3N0YXJ0QXQua2lsbCgpKSxuPXt9O2ZvcihzIGluIHIuc3RhcnRBdCluW3NdPXIuc3RhcnRBdFtzXTtpZihuLm92ZXJ3cml0ZT0hMSxuLmltbWVkaWF0ZVJlbmRlcj0hMCxuLmxhenk9bCYmci5sYXp5IT09ITEsbi5zdGFydEF0PW4uZGVsYXk9bnVsbCx0aGlzLl9zdGFydEF0PUQudG8odGhpcy50YXJnZXQsMCxuKSxsKWlmKHRoaXMuX3RpbWU+MCl0aGlzLl9zdGFydEF0PW51bGw7ZWxzZSBpZigwIT09bylyZXR1cm59ZWxzZSBpZihyLnJ1bkJhY2t3YXJkcyYmMCE9PW8paWYodGhpcy5fc3RhcnRBdCl0aGlzLl9zdGFydEF0LnJlbmRlcigtMSwhMCksdGhpcy5fc3RhcnRBdC5raWxsKCksdGhpcy5fc3RhcnRBdD1udWxsO2Vsc2V7aT17fTtmb3IocyBpbiByKWpbc10mJlwiYXV0b0NTU1wiIT09c3x8KGlbc109cltzXSk7aWYoaS5vdmVyd3JpdGU9MCxpLmRhdGE9XCJpc0Zyb21TdGFydFwiLGkubGF6eT1sJiZyLmxhenkhPT0hMSxpLmltbWVkaWF0ZVJlbmRlcj1sLHRoaXMuX3N0YXJ0QXQ9RC50byh0aGlzLnRhcmdldCwwLGkpLGwpe2lmKDA9PT10aGlzLl90aW1lKXJldHVybn1lbHNlIHRoaXMuX3N0YXJ0QXQuX2luaXQoKSx0aGlzLl9zdGFydEF0Ll9lbmFibGVkKCExKX1pZih0aGlzLl9lYXNlPWg/aCBpbnN0YW5jZW9mIFQ/ci5lYXNlUGFyYW1zIGluc3RhbmNlb2YgQXJyYXk/aC5jb25maWcuYXBwbHkoaCxyLmVhc2VQYXJhbXMpOmg6XCJmdW5jdGlvblwiPT10eXBlb2YgaD9uZXcgVChoLHIuZWFzZVBhcmFtcyk6eVtoXXx8RC5kZWZhdWx0RWFzZTpELmRlZmF1bHRFYXNlLHRoaXMuX2Vhc2VUeXBlPXRoaXMuX2Vhc2UuX3R5cGUsdGhpcy5fZWFzZVBvd2VyPXRoaXMuX2Vhc2UuX3Bvd2VyLHRoaXMuX2ZpcnN0UFQ9bnVsbCx0aGlzLl90YXJnZXRzKWZvcih0PXRoaXMuX3RhcmdldHMubGVuZ3RoOy0tdD4tMTspdGhpcy5faW5pdFByb3BzKHRoaXMuX3RhcmdldHNbdF0sdGhpcy5fcHJvcExvb2t1cFt0XT17fSx0aGlzLl9zaWJsaW5nc1t0XSxhP2FbdF06bnVsbCkmJihlPSEwKTtlbHNlIGU9dGhpcy5faW5pdFByb3BzKHRoaXMudGFyZ2V0LHRoaXMuX3Byb3BMb29rdXAsdGhpcy5fc2libGluZ3MsYSk7aWYoZSYmRC5fb25QbHVnaW5FdmVudChcIl9vbkluaXRBbGxQcm9wc1wiLHRoaXMpLGEmJih0aGlzLl9maXJzdFBUfHxcImZ1bmN0aW9uXCIhPXR5cGVvZiB0aGlzLnRhcmdldCYmdGhpcy5fZW5hYmxlZCghMSwhMSkpLHIucnVuQmFja3dhcmRzKWZvcihpPXRoaXMuX2ZpcnN0UFQ7aTspaS5zKz1pLmMsaS5jPS1pLmMsaT1pLl9uZXh0O3RoaXMuX29uVXBkYXRlPXIub25VcGRhdGUsdGhpcy5faW5pdHRlZD0hMH0sbi5faW5pdFByb3BzPWZ1bmN0aW9uKGUsaSxzLG4pe3ZhciByLGEsbyxsLGgsXztpZihudWxsPT1lKXJldHVybiExO09bZS5fZ3NUd2VlbklEXSYmcSgpLHRoaXMudmFycy5jc3N8fGUuc3R5bGUmJmUhPT10JiZlLm5vZGVUeXBlJiZMLmNzcyYmdGhpcy52YXJzLmF1dG9DU1MhPT0hMSYmRSh0aGlzLnZhcnMsZSk7Zm9yKHIgaW4gdGhpcy52YXJzKXtpZihfPXRoaXMudmFyc1tyXSxqW3JdKV8mJihfIGluc3RhbmNlb2YgQXJyYXl8fF8ucHVzaCYmbShfKSkmJi0xIT09Xy5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIikmJih0aGlzLnZhcnNbcl09Xz10aGlzLl9zd2FwU2VsZkluUGFyYW1zKF8sdGhpcykpO2Vsc2UgaWYoTFtyXSYmKGw9bmV3IExbcl0pLl9vbkluaXRUd2VlbihlLHRoaXMudmFyc1tyXSx0aGlzKSl7Zm9yKHRoaXMuX2ZpcnN0UFQ9aD17X25leHQ6dGhpcy5fZmlyc3RQVCx0OmwscDpcInNldFJhdGlvXCIsczowLGM6MSxmOiEwLG46cixwZzohMCxwcjpsLl9wcmlvcml0eX0sYT1sLl9vdmVyd3JpdGVQcm9wcy5sZW5ndGg7LS1hPi0xOylpW2wuX292ZXJ3cml0ZVByb3BzW2FdXT10aGlzLl9maXJzdFBUOyhsLl9wcmlvcml0eXx8bC5fb25Jbml0QWxsUHJvcHMpJiYobz0hMCksKGwuX29uRGlzYWJsZXx8bC5fb25FbmFibGUpJiYodGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZD0hMCl9ZWxzZSB0aGlzLl9maXJzdFBUPWlbcl09aD17X25leHQ6dGhpcy5fZmlyc3RQVCx0OmUscDpyLGY6XCJmdW5jdGlvblwiPT10eXBlb2YgZVtyXSxuOnIscGc6ITEscHI6MH0saC5zPWguZj9lW3IuaW5kZXhPZihcInNldFwiKXx8XCJmdW5jdGlvblwiIT10eXBlb2YgZVtcImdldFwiK3Iuc3Vic3RyKDMpXT9yOlwiZ2V0XCIrci5zdWJzdHIoMyldKCk6cGFyc2VGbG9hdChlW3JdKSxoLmM9XCJzdHJpbmdcIj09dHlwZW9mIF8mJlwiPVwiPT09Xy5jaGFyQXQoMSk/cGFyc2VJbnQoXy5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcihfLnN1YnN0cigyKSk6TnVtYmVyKF8pLWguc3x8MDtoJiZoLl9uZXh0JiYoaC5fbmV4dC5fcHJldj1oKX1yZXR1cm4gbiYmdGhpcy5fa2lsbChuLGUpP3RoaXMuX2luaXRQcm9wcyhlLGkscyxuKTp0aGlzLl9vdmVyd3JpdGU+MSYmdGhpcy5fZmlyc3RQVCYmcy5sZW5ndGg+MSYmJChlLHRoaXMsaSx0aGlzLl9vdmVyd3JpdGUscyk/KHRoaXMuX2tpbGwoaSxlKSx0aGlzLl9pbml0UHJvcHMoZSxpLHMsbikpOih0aGlzLl9maXJzdFBUJiYodGhpcy52YXJzLmxhenkhPT0hMSYmdGhpcy5fZHVyYXRpb258fHRoaXMudmFycy5sYXp5JiYhdGhpcy5fZHVyYXRpb24pJiYoT1tlLl9nc1R3ZWVuSURdPSEwKSxvKX0sbi5yZW5kZXI9ZnVuY3Rpb24odCxlLGkpe3ZhciBzLG4scixhLG89dGhpcy5fdGltZSxsPXRoaXMuX2R1cmF0aW9uLF89dGhpcy5fcmF3UHJldlRpbWU7aWYodD49bCl0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT1sLHRoaXMucmF0aW89dGhpcy5fZWFzZS5fY2FsY0VuZD90aGlzLl9lYXNlLmdldFJhdGlvKDEpOjEsdGhpcy5fcmV2ZXJzZWR8fChzPSEwLG49XCJvbkNvbXBsZXRlXCIpLDA9PT1sJiYodGhpcy5faW5pdHRlZHx8IXRoaXMudmFycy5sYXp5fHxpKSYmKHRoaXMuX3N0YXJ0VGltZT09PXRoaXMuX3RpbWVsaW5lLl9kdXJhdGlvbiYmKHQ9MCksKDA9PT10fHwwPl98fF89PT1oKSYmXyE9PXQmJihpPSEwLF8+aCYmKG49XCJvblJldmVyc2VDb21wbGV0ZVwiKSksdGhpcy5fcmF3UHJldlRpbWU9YT0hZXx8dHx8Xz09PXQ/dDpoKTtlbHNlIGlmKDFlLTc+dCl0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT0wLHRoaXMucmF0aW89dGhpcy5fZWFzZS5fY2FsY0VuZD90aGlzLl9lYXNlLmdldFJhdGlvKDApOjAsKDAhPT1vfHwwPT09bCYmXz4wJiZfIT09aCkmJihuPVwib25SZXZlcnNlQ29tcGxldGVcIixzPXRoaXMuX3JldmVyc2VkKSwwPnQ/KHRoaXMuX2FjdGl2ZT0hMSwwPT09bCYmKHRoaXMuX2luaXR0ZWR8fCF0aGlzLnZhcnMubGF6eXx8aSkmJihfPj0wJiYoaT0hMCksdGhpcy5fcmF3UHJldlRpbWU9YT0hZXx8dHx8Xz09PXQ/dDpoKSk6dGhpcy5faW5pdHRlZHx8KGk9ITApO2Vsc2UgaWYodGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9dCx0aGlzLl9lYXNlVHlwZSl7dmFyIHU9dC9sLG09dGhpcy5fZWFzZVR5cGUsZj10aGlzLl9lYXNlUG93ZXI7KDE9PT1tfHwzPT09bSYmdT49LjUpJiYodT0xLXUpLDM9PT1tJiYodSo9MiksMT09PWY/dSo9dToyPT09Zj91Kj11KnU6Mz09PWY/dSo9dSp1KnU6ND09PWYmJih1Kj11KnUqdSp1KSx0aGlzLnJhdGlvPTE9PT1tPzEtdToyPT09bT91Oi41PnQvbD91LzI6MS11LzJ9ZWxzZSB0aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8odC9sKTtpZih0aGlzLl90aW1lIT09b3x8aSl7aWYoIXRoaXMuX2luaXR0ZWQpe2lmKHRoaXMuX2luaXQoKSwhdGhpcy5faW5pdHRlZHx8dGhpcy5fZ2MpcmV0dXJuO2lmKCFpJiZ0aGlzLl9maXJzdFBUJiYodGhpcy52YXJzLmxhenkhPT0hMSYmdGhpcy5fZHVyYXRpb258fHRoaXMudmFycy5sYXp5JiYhdGhpcy5fZHVyYXRpb24pKXJldHVybiB0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT1vLHRoaXMuX3Jhd1ByZXZUaW1lPV8sei5wdXNoKHRoaXMpLHRoaXMuX2xhenk9dCx2b2lkIDA7dGhpcy5fdGltZSYmIXM/dGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKHRoaXMuX3RpbWUvbCk6cyYmdGhpcy5fZWFzZS5fY2FsY0VuZCYmKHRoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbygwPT09dGhpcy5fdGltZT8wOjEpKX1mb3IodGhpcy5fbGF6eSE9PSExJiYodGhpcy5fbGF6eT0hMSksdGhpcy5fYWN0aXZlfHwhdGhpcy5fcGF1c2VkJiZ0aGlzLl90aW1lIT09byYmdD49MCYmKHRoaXMuX2FjdGl2ZT0hMCksMD09PW8mJih0aGlzLl9zdGFydEF0JiYodD49MD90aGlzLl9zdGFydEF0LnJlbmRlcih0LGUsaSk6bnx8KG49XCJfZHVtbXlHU1wiKSksdGhpcy52YXJzLm9uU3RhcnQmJigwIT09dGhpcy5fdGltZXx8MD09PWwpJiYoZXx8dGhpcy52YXJzLm9uU3RhcnQuYXBwbHkodGhpcy52YXJzLm9uU3RhcnRTY29wZXx8dGhpcyx0aGlzLnZhcnMub25TdGFydFBhcmFtc3x8ZykpKSxyPXRoaXMuX2ZpcnN0UFQ7cjspci5mP3IudFtyLnBdKHIuYyp0aGlzLnJhdGlvK3Iucyk6ci50W3IucF09ci5jKnRoaXMucmF0aW8rci5zLHI9ci5fbmV4dDt0aGlzLl9vblVwZGF0ZSYmKDA+dCYmdGhpcy5fc3RhcnRBdCYmdGhpcy5fc3RhcnRUaW1lJiZ0aGlzLl9zdGFydEF0LnJlbmRlcih0LGUsaSksZXx8KHRoaXMuX3RpbWUhPT1vfHxzKSYmdGhpcy5fb25VcGRhdGUuYXBwbHkodGhpcy52YXJzLm9uVXBkYXRlU2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uVXBkYXRlUGFyYW1zfHxnKSksbiYmKHRoaXMuX2djfHwoMD50JiZ0aGlzLl9zdGFydEF0JiYhdGhpcy5fb25VcGRhdGUmJnRoaXMuX3N0YXJ0VGltZSYmdGhpcy5fc3RhcnRBdC5yZW5kZXIodCxlLGkpLHMmJih0aGlzLl90aW1lbGluZS5hdXRvUmVtb3ZlQ2hpbGRyZW4mJnRoaXMuX2VuYWJsZWQoITEsITEpLHRoaXMuX2FjdGl2ZT0hMSksIWUmJnRoaXMudmFyc1tuXSYmdGhpcy52YXJzW25dLmFwcGx5KHRoaXMudmFyc1tuK1wiU2NvcGVcIl18fHRoaXMsdGhpcy52YXJzW24rXCJQYXJhbXNcIl18fGcpLDA9PT1sJiZ0aGlzLl9yYXdQcmV2VGltZT09PWgmJmEhPT1oJiYodGhpcy5fcmF3UHJldlRpbWU9MCkpKX19LG4uX2tpbGw9ZnVuY3Rpb24odCxlKXtpZihcImFsbFwiPT09dCYmKHQ9bnVsbCksbnVsbD09dCYmKG51bGw9PWV8fGU9PT10aGlzLnRhcmdldCkpcmV0dXJuIHRoaXMuX2xhenk9ITEsdGhpcy5fZW5hYmxlZCghMSwhMSk7ZT1cInN0cmluZ1wiIT10eXBlb2YgZT9lfHx0aGlzLl90YXJnZXRzfHx0aGlzLnRhcmdldDpELnNlbGVjdG9yKGUpfHxlO3ZhciBpLHMsbixyLGEsbyxsLGg7aWYoKG0oZSl8fEkoZSkpJiZcIm51bWJlclwiIT10eXBlb2YgZVswXSlmb3IoaT1lLmxlbmd0aDstLWk+LTE7KXRoaXMuX2tpbGwodCxlW2ldKSYmKG89ITApO2Vsc2V7aWYodGhpcy5fdGFyZ2V0cyl7Zm9yKGk9dGhpcy5fdGFyZ2V0cy5sZW5ndGg7LS1pPi0xOylpZihlPT09dGhpcy5fdGFyZ2V0c1tpXSl7YT10aGlzLl9wcm9wTG9va3VwW2ldfHx7fSx0aGlzLl9vdmVyd3JpdHRlblByb3BzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHN8fFtdLHM9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc1tpXT10P3RoaXMuX292ZXJ3cml0dGVuUHJvcHNbaV18fHt9OlwiYWxsXCI7YnJlYWt9fWVsc2V7aWYoZSE9PXRoaXMudGFyZ2V0KXJldHVybiExO2E9dGhpcy5fcHJvcExvb2t1cCxzPXRoaXMuX292ZXJ3cml0dGVuUHJvcHM9dD90aGlzLl9vdmVyd3JpdHRlblByb3BzfHx7fTpcImFsbFwifWlmKGEpe2w9dHx8YSxoPXQhPT1zJiZcImFsbFwiIT09cyYmdCE9PWEmJihcIm9iamVjdFwiIT10eXBlb2YgdHx8IXQuX3RlbXBLaWxsKTtmb3IobiBpbiBsKShyPWFbbl0pJiYoci5wZyYmci50Ll9raWxsKGwpJiYobz0hMCksci5wZyYmMCE9PXIudC5fb3ZlcndyaXRlUHJvcHMubGVuZ3RofHwoci5fcHJldj9yLl9wcmV2Ll9uZXh0PXIuX25leHQ6cj09PXRoaXMuX2ZpcnN0UFQmJih0aGlzLl9maXJzdFBUPXIuX25leHQpLHIuX25leHQmJihyLl9uZXh0Ll9wcmV2PXIuX3ByZXYpLHIuX25leHQ9ci5fcHJldj1udWxsKSxkZWxldGUgYVtuXSksaCYmKHNbbl09MSk7IXRoaXMuX2ZpcnN0UFQmJnRoaXMuX2luaXR0ZWQmJnRoaXMuX2VuYWJsZWQoITEsITEpfX1yZXR1cm4gb30sbi5pbnZhbGlkYXRlPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQmJkQuX29uUGx1Z2luRXZlbnQoXCJfb25EaXNhYmxlXCIsdGhpcyksdGhpcy5fZmlyc3RQVD1udWxsLHRoaXMuX292ZXJ3cml0dGVuUHJvcHM9bnVsbCx0aGlzLl9vblVwZGF0ZT1udWxsLHRoaXMuX3N0YXJ0QXQ9bnVsbCx0aGlzLl9pbml0dGVkPXRoaXMuX2FjdGl2ZT10aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkPXRoaXMuX2xhenk9ITEsdGhpcy5fcHJvcExvb2t1cD10aGlzLl90YXJnZXRzP3t9OltdLHRoaXN9LG4uX2VuYWJsZWQ9ZnVuY3Rpb24odCxlKXtpZihhfHxyLndha2UoKSx0JiZ0aGlzLl9nYyl7dmFyIGkscz10aGlzLl90YXJnZXRzO2lmKHMpZm9yKGk9cy5sZW5ndGg7LS1pPi0xOyl0aGlzLl9zaWJsaW5nc1tpXT1NKHNbaV0sdGhpcywhMCk7ZWxzZSB0aGlzLl9zaWJsaW5ncz1NKHRoaXMudGFyZ2V0LHRoaXMsITApfXJldHVybiB4LnByb3RvdHlwZS5fZW5hYmxlZC5jYWxsKHRoaXMsdCxlKSx0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkJiZ0aGlzLl9maXJzdFBUP0QuX29uUGx1Z2luRXZlbnQodD9cIl9vbkVuYWJsZVwiOlwiX29uRGlzYWJsZVwiLHRoaXMpOiExfSxELnRvPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gbmV3IEQodCxlLGkpfSxELmZyb209ZnVuY3Rpb24odCxlLGkpe3JldHVybiBpLnJ1bkJhY2t3YXJkcz0hMCxpLmltbWVkaWF0ZVJlbmRlcj0wIT1pLmltbWVkaWF0ZVJlbmRlcixuZXcgRCh0LGUsaSl9LEQuZnJvbVRvPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiBzLnN0YXJ0QXQ9aSxzLmltbWVkaWF0ZVJlbmRlcj0wIT1zLmltbWVkaWF0ZVJlbmRlciYmMCE9aS5pbW1lZGlhdGVSZW5kZXIsbmV3IEQodCxlLHMpfSxELmRlbGF5ZWRDYWxsPWZ1bmN0aW9uKHQsZSxpLHMsbil7cmV0dXJuIG5ldyBEKGUsMCx7ZGVsYXk6dCxvbkNvbXBsZXRlOmUsb25Db21wbGV0ZVBhcmFtczppLG9uQ29tcGxldGVTY29wZTpzLG9uUmV2ZXJzZUNvbXBsZXRlOmUsb25SZXZlcnNlQ29tcGxldGVQYXJhbXM6aSxvblJldmVyc2VDb21wbGV0ZVNjb3BlOnMsaW1tZWRpYXRlUmVuZGVyOiExLHVzZUZyYW1lczpuLG92ZXJ3cml0ZTowfSl9LEQuc2V0PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG5ldyBEKHQsMCxlKX0sRC5nZXRUd2VlbnNPZj1mdW5jdGlvbih0LGUpe2lmKG51bGw9PXQpcmV0dXJuW107dD1cInN0cmluZ1wiIT10eXBlb2YgdD90OkQuc2VsZWN0b3IodCl8fHQ7dmFyIGkscyxuLHI7aWYoKG0odCl8fEkodCkpJiZcIm51bWJlclwiIT10eXBlb2YgdFswXSl7Zm9yKGk9dC5sZW5ndGgscz1bXTstLWk+LTE7KXM9cy5jb25jYXQoRC5nZXRUd2VlbnNPZih0W2ldLGUpKTtmb3IoaT1zLmxlbmd0aDstLWk+LTE7KWZvcihyPXNbaV0sbj1pOy0tbj4tMTspcj09PXNbbl0mJnMuc3BsaWNlKGksMSl9ZWxzZSBmb3Iocz1NKHQpLmNvbmNhdCgpLGk9cy5sZW5ndGg7LS1pPi0xOykoc1tpXS5fZ2N8fGUmJiFzW2ldLmlzQWN0aXZlKCkpJiZzLnNwbGljZShpLDEpO3JldHVybiBzfSxELmtpbGxUd2VlbnNPZj1ELmtpbGxEZWxheWVkQ2FsbHNUbz1mdW5jdGlvbih0LGUsaSl7XCJvYmplY3RcIj09dHlwZW9mIGUmJihpPWUsZT0hMSk7Zm9yKHZhciBzPUQuZ2V0VHdlZW5zT2YodCxlKSxuPXMubGVuZ3RoOy0tbj4tMTspc1tuXS5fa2lsbChpLHQpfTt2YXIgSD1kKFwicGx1Z2lucy5Ud2VlblBsdWdpblwiLGZ1bmN0aW9uKHQsZSl7dGhpcy5fb3ZlcndyaXRlUHJvcHM9KHR8fFwiXCIpLnNwbGl0KFwiLFwiKSx0aGlzLl9wcm9wTmFtZT10aGlzLl9vdmVyd3JpdGVQcm9wc1swXSx0aGlzLl9wcmlvcml0eT1lfHwwLHRoaXMuX3N1cGVyPUgucHJvdG90eXBlfSwhMCk7aWYobj1ILnByb3RvdHlwZSxILnZlcnNpb249XCIxLjEwLjFcIixILkFQST0yLG4uX2ZpcnN0UFQ9bnVsbCxuLl9hZGRUd2Vlbj1mdW5jdGlvbih0LGUsaSxzLG4scil7dmFyIGEsbztyZXR1cm4gbnVsbCE9cyYmKGE9XCJudW1iZXJcIj09dHlwZW9mIHN8fFwiPVwiIT09cy5jaGFyQXQoMSk/TnVtYmVyKHMpLWk6cGFyc2VJbnQocy5jaGFyQXQoMCkrXCIxXCIsMTApKk51bWJlcihzLnN1YnN0cigyKSkpPyh0aGlzLl9maXJzdFBUPW89e19uZXh0OnRoaXMuX2ZpcnN0UFQsdDp0LHA6ZSxzOmksYzphLGY6XCJmdW5jdGlvblwiPT10eXBlb2YgdFtlXSxuOm58fGUscjpyfSxvLl9uZXh0JiYoby5fbmV4dC5fcHJldj1vKSxvKTp2b2lkIDB9LG4uc2V0UmF0aW89ZnVuY3Rpb24odCl7Zm9yKHZhciBlLGk9dGhpcy5fZmlyc3RQVCxzPTFlLTY7aTspZT1pLmMqdCtpLnMsaS5yP2U9TWF0aC5yb3VuZChlKTpzPmUmJmU+LXMmJihlPTApLGkuZj9pLnRbaS5wXShlKTppLnRbaS5wXT1lLGk9aS5fbmV4dH0sbi5fa2lsbD1mdW5jdGlvbih0KXt2YXIgZSxpPXRoaXMuX292ZXJ3cml0ZVByb3BzLHM9dGhpcy5fZmlyc3RQVDtpZihudWxsIT10W3RoaXMuX3Byb3BOYW1lXSl0aGlzLl9vdmVyd3JpdGVQcm9wcz1bXTtlbHNlIGZvcihlPWkubGVuZ3RoOy0tZT4tMTspbnVsbCE9dFtpW2VdXSYmaS5zcGxpY2UoZSwxKTtmb3IoO3M7KW51bGwhPXRbcy5uXSYmKHMuX25leHQmJihzLl9uZXh0Ll9wcmV2PXMuX3ByZXYpLHMuX3ByZXY/KHMuX3ByZXYuX25leHQ9cy5fbmV4dCxzLl9wcmV2PW51bGwpOnRoaXMuX2ZpcnN0UFQ9PT1zJiYodGhpcy5fZmlyc3RQVD1zLl9uZXh0KSkscz1zLl9uZXh0O3JldHVybiExfSxuLl9yb3VuZFByb3BzPWZ1bmN0aW9uKHQsZSl7Zm9yKHZhciBpPXRoaXMuX2ZpcnN0UFQ7aTspKHRbdGhpcy5fcHJvcE5hbWVdfHxudWxsIT1pLm4mJnRbaS5uLnNwbGl0KHRoaXMuX3Byb3BOYW1lK1wiX1wiKS5qb2luKFwiXCIpXSkmJihpLnI9ZSksaT1pLl9uZXh0fSxELl9vblBsdWdpbkV2ZW50PWZ1bmN0aW9uKHQsZSl7dmFyIGkscyxuLHIsYSxvPWUuX2ZpcnN0UFQ7aWYoXCJfb25Jbml0QWxsUHJvcHNcIj09PXQpe2Zvcig7bzspe2ZvcihhPW8uX25leHQscz1uO3MmJnMucHI+by5wcjspcz1zLl9uZXh0OyhvLl9wcmV2PXM/cy5fcHJldjpyKT9vLl9wcmV2Ll9uZXh0PW86bj1vLChvLl9uZXh0PXMpP3MuX3ByZXY9bzpyPW8sbz1hfW89ZS5fZmlyc3RQVD1ufWZvcig7bzspby5wZyYmXCJmdW5jdGlvblwiPT10eXBlb2Ygby50W3RdJiZvLnRbdF0oKSYmKGk9ITApLG89by5fbmV4dDtyZXR1cm4gaX0sSC5hY3RpdmF0ZT1mdW5jdGlvbih0KXtmb3IodmFyIGU9dC5sZW5ndGg7LS1lPi0xOyl0W2VdLkFQST09PUguQVBJJiYoTFsobmV3IHRbZV0pLl9wcm9wTmFtZV09dFtlXSk7cmV0dXJuITB9LGMucGx1Z2luPWZ1bmN0aW9uKHQpe2lmKCEodCYmdC5wcm9wTmFtZSYmdC5pbml0JiZ0LkFQSSkpdGhyb3dcImlsbGVnYWwgcGx1Z2luIGRlZmluaXRpb24uXCI7dmFyIGUsaT10LnByb3BOYW1lLHM9dC5wcmlvcml0eXx8MCxuPXQub3ZlcndyaXRlUHJvcHMscj17aW5pdDpcIl9vbkluaXRUd2VlblwiLHNldDpcInNldFJhdGlvXCIsa2lsbDpcIl9raWxsXCIscm91bmQ6XCJfcm91bmRQcm9wc1wiLGluaXRBbGw6XCJfb25Jbml0QWxsUHJvcHNcIn0sYT1kKFwicGx1Z2lucy5cIitpLmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK2kuc3Vic3RyKDEpK1wiUGx1Z2luXCIsZnVuY3Rpb24oKXtILmNhbGwodGhpcyxpLHMpLHRoaXMuX292ZXJ3cml0ZVByb3BzPW58fFtdfSx0Lmdsb2JhbD09PSEwKSxvPWEucHJvdG90eXBlPW5ldyBIKGkpO28uY29uc3RydWN0b3I9YSxhLkFQST10LkFQSTtmb3IoZSBpbiByKVwiZnVuY3Rpb25cIj09dHlwZW9mIHRbZV0mJihvW3JbZV1dPXRbZV0pO3JldHVybiBhLnZlcnNpb249dC52ZXJzaW9uLEguYWN0aXZhdGUoW2FdKSxhfSxpPXQuX2dzUXVldWUpe2ZvcihzPTA7aS5sZW5ndGg+cztzKyspaVtzXSgpO2ZvcihuIGluIGYpZltuXS5mdW5jfHx0LmNvbnNvbGUubG9nKFwiR1NBUCBlbmNvdW50ZXJlZCBtaXNzaW5nIGRlcGVuZGVuY3k6IGNvbS5ncmVlbnNvY2suXCIrbil9YT0hMX19KSh3aW5kb3cpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiBiZXRhIDEuOS4zXHJcbiAqIERBVEU6IDIwMTMtMDQtMDJcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwiZWFzaW5nLkJhY2tcIixbXCJlYXNpbmcuRWFzZVwiXSxmdW5jdGlvbih0KXt2YXIgZSxpLHMscj13aW5kb3cuR3JlZW5Tb2NrR2xvYmFsc3x8d2luZG93LG49ci5jb20uZ3JlZW5zb2NrLGE9MipNYXRoLlBJLG89TWF0aC5QSS8yLGg9bi5fY2xhc3MsbD1mdW5jdGlvbihlLGkpe3ZhciBzPWgoXCJlYXNpbmcuXCIrZSxmdW5jdGlvbigpe30sITApLHI9cy5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIHIuY29uc3RydWN0b3I9cyxyLmdldFJhdGlvPWksc30sXz10LnJlZ2lzdGVyfHxmdW5jdGlvbigpe30sdT1mdW5jdGlvbih0LGUsaSxzKXt2YXIgcj1oKFwiZWFzaW5nLlwiK3Qse2Vhc2VPdXQ6bmV3IGUsZWFzZUluOm5ldyBpLGVhc2VJbk91dDpuZXcgc30sITApO3JldHVybiBfKHIsdCkscn0sYz1mdW5jdGlvbih0LGUsaSl7dGhpcy50PXQsdGhpcy52PWUsaSYmKHRoaXMubmV4dD1pLGkucHJldj10aGlzLHRoaXMuYz1pLnYtZSx0aGlzLmdhcD1pLnQtdCl9LGY9ZnVuY3Rpb24oZSxpKXt2YXIgcz1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24odCl7dGhpcy5fcDE9dHx8MD09PXQ/dDoxLjcwMTU4LHRoaXMuX3AyPTEuNTI1KnRoaXMuX3AxfSwhMCkscj1zLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gci5jb25zdHJ1Y3Rvcj1zLHIuZ2V0UmF0aW89aSxyLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IHModCl9LHN9LHA9dShcIkJhY2tcIixmKFwiQmFja091dFwiLGZ1bmN0aW9uKHQpe3JldHVybih0LT0xKSp0KigodGhpcy5fcDErMSkqdCt0aGlzLl9wMSkrMX0pLGYoXCJCYWNrSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gdCp0KigodGhpcy5fcDErMSkqdC10aGlzLl9wMSl9KSxmKFwiQmFja0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy41KnQqdCooKHRoaXMuX3AyKzEpKnQtdGhpcy5fcDIpOi41KigodC09MikqdCooKHRoaXMuX3AyKzEpKnQrdGhpcy5fcDIpKzIpfSkpLG09aChcImVhc2luZy5TbG93TW9cIixmdW5jdGlvbih0LGUsaSl7ZT1lfHwwPT09ZT9lOi43LG51bGw9PXQ/dD0uNzp0PjEmJih0PTEpLHRoaXMuX3A9MSE9PXQ/ZTowLHRoaXMuX3AxPSgxLXQpLzIsdGhpcy5fcDI9dCx0aGlzLl9wMz10aGlzLl9wMSt0aGlzLl9wMix0aGlzLl9jYWxjRW5kPWk9PT0hMH0sITApLGQ9bS5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIGQuY29uc3RydWN0b3I9bSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlPXQrKC41LXQpKnRoaXMuX3A7cmV0dXJuIHRoaXMuX3AxPnQ/dGhpcy5fY2FsY0VuZD8xLSh0PTEtdC90aGlzLl9wMSkqdDplLSh0PTEtdC90aGlzLl9wMSkqdCp0KnQqZTp0PnRoaXMuX3AzP3RoaXMuX2NhbGNFbmQ/MS0odD0odC10aGlzLl9wMykvdGhpcy5fcDEpKnQ6ZSsodC1lKSoodD0odC10aGlzLl9wMykvdGhpcy5fcDEpKnQqdCp0OnRoaXMuX2NhbGNFbmQ/MTplfSxtLmVhc2U9bmV3IG0oLjcsLjcpLGQuY29uZmlnPW0uY29uZmlnPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gbmV3IG0odCxlLGkpfSxlPWgoXCJlYXNpbmcuU3RlcHBlZEVhc2VcIixmdW5jdGlvbih0KXt0PXR8fDEsdGhpcy5fcDE9MS90LHRoaXMuX3AyPXQrMX0sITApLGQ9ZS5wcm90b3R5cGU9bmV3IHQsZC5jb25zdHJ1Y3Rvcj1lLGQuZ2V0UmF0aW89ZnVuY3Rpb24odCl7cmV0dXJuIDA+dD90PTA6dD49MSYmKHQ9Ljk5OTk5OTk5OSksKHRoaXMuX3AyKnQ+PjApKnRoaXMuX3AxfSxkLmNvbmZpZz1lLmNvbmZpZz1mdW5jdGlvbih0KXtyZXR1cm4gbmV3IGUodCl9LGk9aChcImVhc2luZy5Sb3VnaEVhc2VcIixmdW5jdGlvbihlKXtlPWV8fHt9O2Zvcih2YXIgaSxzLHIsbixhLG8saD1lLnRhcGVyfHxcIm5vbmVcIixsPVtdLF89MCx1PTB8KGUucG9pbnRzfHwyMCksZj11LHA9ZS5yYW5kb21pemUhPT0hMSxtPWUuY2xhbXA9PT0hMCxkPWUudGVtcGxhdGUgaW5zdGFuY2VvZiB0P2UudGVtcGxhdGU6bnVsbCxnPVwibnVtYmVyXCI9PXR5cGVvZiBlLnN0cmVuZ3RoPy40KmUuc3RyZW5ndGg6LjQ7LS1mPi0xOylpPXA/TWF0aC5yYW5kb20oKToxL3UqZixzPWQ/ZC5nZXRSYXRpbyhpKTppLFwibm9uZVwiPT09aD9yPWc6XCJvdXRcIj09PWg/KG49MS1pLHI9bipuKmcpOlwiaW5cIj09PWg/cj1pKmkqZzouNT5pPyhuPTIqaSxyPS41Km4qbipnKToobj0yKigxLWkpLHI9LjUqbipuKmcpLHA/cys9TWF0aC5yYW5kb20oKSpyLS41KnI6ZiUyP3MrPS41KnI6cy09LjUqcixtJiYocz4xP3M9MTowPnMmJihzPTApKSxsW18rK109e3g6aSx5OnN9O2ZvcihsLnNvcnQoZnVuY3Rpb24odCxlKXtyZXR1cm4gdC54LWUueH0pLG89bmV3IGMoMSwxLG51bGwpLGY9dTstLWY+LTE7KWE9bFtmXSxvPW5ldyBjKGEueCxhLnksbyk7dGhpcy5fcHJldj1uZXcgYygwLDAsMCE9PW8udD9vOm8ubmV4dCl9LCEwKSxkPWkucHJvdG90eXBlPW5ldyB0LGQuY29uc3RydWN0b3I9aSxkLmdldFJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlPXRoaXMuX3ByZXY7aWYodD5lLnQpe2Zvcig7ZS5uZXh0JiZ0Pj1lLnQ7KWU9ZS5uZXh0O2U9ZS5wcmV2fWVsc2UgZm9yKDtlLnByZXYmJmUudD49dDspZT1lLnByZXY7cmV0dXJuIHRoaXMuX3ByZXY9ZSxlLnYrKHQtZS50KS9lLmdhcCplLmN9LGQuY29uZmlnPWZ1bmN0aW9uKHQpe3JldHVybiBuZXcgaSh0KX0saS5lYXNlPW5ldyBpLHUoXCJCb3VuY2VcIixsKFwiQm91bmNlT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDEvMi43NT50PzcuNTYyNSp0KnQ6Mi8yLjc1PnQ/Ny41NjI1Kih0LT0xLjUvMi43NSkqdCsuNzU6Mi41LzIuNzU+dD83LjU2MjUqKHQtPTIuMjUvMi43NSkqdCsuOTM3NTo3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NX0pLGwoXCJCb3VuY2VJblwiLGZ1bmN0aW9uKHQpe3JldHVybiAxLzIuNzU+KHQ9MS10KT8xLTcuNTYyNSp0KnQ6Mi8yLjc1PnQ/MS0oNy41NjI1Kih0LT0xLjUvMi43NSkqdCsuNzUpOjIuNS8yLjc1PnQ/MS0oNy41NjI1Kih0LT0yLjI1LzIuNzUpKnQrLjkzNzUpOjEtKDcuNTYyNSoodC09Mi42MjUvMi43NSkqdCsuOTg0Mzc1KX0pLGwoXCJCb3VuY2VJbk91dFwiLGZ1bmN0aW9uKHQpe3ZhciBlPS41PnQ7cmV0dXJuIHQ9ZT8xLTIqdDoyKnQtMSx0PTEvMi43NT50PzcuNTYyNSp0KnQ6Mi8yLjc1PnQ/Ny41NjI1Kih0LT0xLjUvMi43NSkqdCsuNzU6Mi41LzIuNzU+dD83LjU2MjUqKHQtPTIuMjUvMi43NSkqdCsuOTM3NTo3LjU2MjUqKHQtPTIuNjI1LzIuNzUpKnQrLjk4NDM3NSxlPy41KigxLXQpOi41KnQrLjV9KSksdShcIkNpcmNcIixsKFwiQ2lyY091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnNxcnQoMS0odC09MSkqdCl9KSxsKFwiQ2lyY0luXCIsZnVuY3Rpb24odCl7cmV0dXJuLShNYXRoLnNxcnQoMS10KnQpLTEpfSksbChcIkNpcmNJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8tLjUqKE1hdGguc3FydCgxLXQqdCktMSk6LjUqKE1hdGguc3FydCgxLSh0LT0yKSp0KSsxKX0pKSxzPWZ1bmN0aW9uKGUsaSxzKXt2YXIgcj1oKFwiZWFzaW5nLlwiK2UsZnVuY3Rpb24odCxlKXt0aGlzLl9wMT10fHwxLHRoaXMuX3AyPWV8fHMsdGhpcy5fcDM9dGhpcy5fcDIvYSooTWF0aC5hc2luKDEvdGhpcy5fcDEpfHwwKX0sITApLG49ci5wcm90b3R5cGU9bmV3IHQ7cmV0dXJuIG4uY29uc3RydWN0b3I9cixuLmdldFJhdGlvPWksbi5jb25maWc9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbmV3IHIodCxlKX0scn0sdShcIkVsYXN0aWNcIixzKFwiRWxhc3RpY091dFwiLGZ1bmN0aW9uKHQpe3JldHVybiB0aGlzLl9wMSpNYXRoLnBvdygyLC0xMCp0KSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikrMX0sLjMpLHMoXCJFbGFzdGljSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tKHRoaXMuX3AxKk1hdGgucG93KDIsMTAqKHQtPTEpKSpNYXRoLnNpbigodC10aGlzLl9wMykqYS90aGlzLl9wMikpfSwuMykscyhcIkVsYXN0aWNJbk91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiAxPih0Kj0yKT8tLjUqdGhpcy5fcDEqTWF0aC5wb3coMiwxMCoodC09MSkpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKTouNSp0aGlzLl9wMSpNYXRoLnBvdygyLC0xMCoodC09MSkpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKSsxfSwuNDUpKSx1KFwiRXhwb1wiLGwoXCJFeHBvT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDEtTWF0aC5wb3coMiwtMTAqdCl9KSxsKFwiRXhwb0luXCIsZnVuY3Rpb24odCl7cmV0dXJuIE1hdGgucG93KDIsMTAqKHQtMSkpLS4wMDF9KSxsKFwiRXhwb0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy41Kk1hdGgucG93KDIsMTAqKHQtMSkpOi41KigyLU1hdGgucG93KDIsLTEwKih0LTEpKSl9KSksdShcIlNpbmVcIixsKFwiU2luZU91dFwiLGZ1bmN0aW9uKHQpe3JldHVybiBNYXRoLnNpbih0Km8pfSksbChcIlNpbmVJblwiLGZ1bmN0aW9uKHQpe3JldHVybi1NYXRoLmNvcyh0Km8pKzF9KSxsKFwiU2luZUluT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuLS41KihNYXRoLmNvcyhNYXRoLlBJKnQpLTEpfSkpLGgoXCJlYXNpbmcuRWFzZUxvb2t1cFwiLHtmaW5kOmZ1bmN0aW9uKGUpe3JldHVybiB0Lm1hcFtlXX19LCEwKSxfKHIuU2xvd01vLFwiU2xvd01vXCIsXCJlYXNlLFwiKSxfKGksXCJSb3VnaEVhc2VcIixcImVhc2UsXCIpLF8oZSxcIlN0ZXBwZWRFYXNlXCIsXCJlYXNlLFwiKSxwfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJwbHVnaW5zLkNTU1BsdWdpblwiLFtcInBsdWdpbnMuVHdlZW5QbHVnaW5cIixcIlR3ZWVuTGl0ZVwiXSxmdW5jdGlvbih0LGUpe3ZhciBpLHIscyxuLGE9ZnVuY3Rpb24oKXt0LmNhbGwodGhpcyxcImNzc1wiKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5sZW5ndGg9MCx0aGlzLnNldFJhdGlvPWEucHJvdG90eXBlLnNldFJhdGlvfSxvPXt9LGw9YS5wcm90b3R5cGU9bmV3IHQoXCJjc3NcIik7bC5jb25zdHJ1Y3Rvcj1hLGEudmVyc2lvbj1cIjEuMTIuMVwiLGEuQVBJPTIsYS5kZWZhdWx0VHJhbnNmb3JtUGVyc3BlY3RpdmU9MCxhLmRlZmF1bHRTa2V3VHlwZT1cImNvbXBlbnNhdGVkXCIsbD1cInB4XCIsYS5zdWZmaXhNYXA9e3RvcDpsLHJpZ2h0OmwsYm90dG9tOmwsbGVmdDpsLHdpZHRoOmwsaGVpZ2h0OmwsZm9udFNpemU6bCxwYWRkaW5nOmwsbWFyZ2luOmwscGVyc3BlY3RpdmU6bCxsaW5lSGVpZ2h0OlwiXCJ9O3ZhciBoLHUsZixfLHAsYyxkPS8oPzpcXGR8XFwtXFxkfFxcLlxcZHxcXC1cXC5cXGQpKy9nLG09Lyg/OlxcZHxcXC1cXGR8XFwuXFxkfFxcLVxcLlxcZHxcXCs9XFxkfFxcLT1cXGR8XFwrPS5cXGR8XFwtPVxcLlxcZCkrL2csZz0vKD86XFwrPXxcXC09fFxcLXxcXGIpW1xcZFxcLVxcLl0rW2EtekEtWjAtOV0qKD86JXxcXGIpL2dpLHY9L1teXFxkXFwtXFwuXS9nLHk9Lyg/OlxcZHxcXC18XFwrfD18I3xcXC4pKi9nLFQ9L29wYWNpdHkgKj0gKihbXildKikvaSx3PS9vcGFjaXR5OihbXjtdKikvaSx4PS9hbHBoYVxcKG9wYWNpdHkgKj0uKz9cXCkvaSxiPS9eKHJnYnxoc2wpLyxQPS8oW0EtWl0pL2csUz0vLShbYS16XSkvZ2ksQz0vKF4oPzp1cmxcXChcXFwifHVybFxcKCkpfCg/OihcXFwiXFwpKSR8XFwpJCkvZ2ksUj1mdW5jdGlvbih0LGUpe3JldHVybiBlLnRvVXBwZXJDYXNlKCl9LGs9Lyg/OkxlZnR8UmlnaHR8V2lkdGgpL2ksQT0vKE0xMXxNMTJ8TTIxfE0yMik9W1xcZFxcLVxcLmVdKy9naSxPPS9wcm9naWRcXDpEWEltYWdlVHJhbnNmb3JtXFwuTWljcm9zb2Z0XFwuTWF0cml4XFwoLis/XFwpL2ksRD0vLCg/PVteXFwpXSooPzpcXCh8JCkpL2dpLE09TWF0aC5QSS8xODAsTD0xODAvTWF0aC5QSSxOPXt9LFg9ZG9jdW1lbnQsej1YLmNyZWF0ZUVsZW1lbnQoXCJkaXZcIiksST1YLmNyZWF0ZUVsZW1lbnQoXCJpbWdcIiksRT1hLl9pbnRlcm5hbHM9e19zcGVjaWFsUHJvcHM6b30sRj1uYXZpZ2F0b3IudXNlckFnZW50LFk9ZnVuY3Rpb24oKXt2YXIgdCxlPUYuaW5kZXhPZihcIkFuZHJvaWRcIiksaT1YLmNyZWF0ZUVsZW1lbnQoXCJkaXZcIik7cmV0dXJuIGY9LTEhPT1GLmluZGV4T2YoXCJTYWZhcmlcIikmJi0xPT09Ri5pbmRleE9mKFwiQ2hyb21lXCIpJiYoLTE9PT1lfHxOdW1iZXIoRi5zdWJzdHIoZSs4LDEpKT4zKSxwPWYmJjY+TnVtYmVyKEYuc3Vic3RyKEYuaW5kZXhPZihcIlZlcnNpb24vXCIpKzgsMSkpLF89LTEhPT1GLmluZGV4T2YoXCJGaXJlZm94XCIpLC9NU0lFIChbMC05XXsxLH1bXFwuMC05XXswLH0pLy5leGVjKEYpJiYoYz1wYXJzZUZsb2F0KFJlZ0V4cC4kMSkpLGkuaW5uZXJIVE1MPVwiPGEgc3R5bGU9J3RvcDoxcHg7b3BhY2l0eTouNTU7Jz5hPC9hPlwiLHQ9aS5nZXRFbGVtZW50c0J5VGFnTmFtZShcImFcIilbMF0sdD8vXjAuNTUvLnRlc3QodC5zdHlsZS5vcGFjaXR5KTohMX0oKSxCPWZ1bmN0aW9uKHQpe3JldHVybiBULnRlc3QoXCJzdHJpbmdcIj09dHlwZW9mIHQ/dDoodC5jdXJyZW50U3R5bGU/dC5jdXJyZW50U3R5bGUuZmlsdGVyOnQuc3R5bGUuZmlsdGVyKXx8XCJcIik/cGFyc2VGbG9hdChSZWdFeHAuJDEpLzEwMDoxfSxVPWZ1bmN0aW9uKHQpe3dpbmRvdy5jb25zb2xlJiZjb25zb2xlLmxvZyh0KX0sVz1cIlwiLGo9XCJcIixWPWZ1bmN0aW9uKHQsZSl7ZT1lfHx6O3ZhciBpLHIscz1lLnN0eWxlO2lmKHZvaWQgMCE9PXNbdF0pcmV0dXJuIHQ7Zm9yKHQ9dC5jaGFyQXQoMCkudG9VcHBlckNhc2UoKSt0LnN1YnN0cigxKSxpPVtcIk9cIixcIk1velwiLFwibXNcIixcIk1zXCIsXCJXZWJraXRcIl0scj01Oy0tcj4tMSYmdm9pZCAwPT09c1tpW3JdK3RdOyk7cmV0dXJuIHI+PTA/KGo9Mz09PXI/XCJtc1wiOmlbcl0sVz1cIi1cIitqLnRvTG93ZXJDYXNlKCkrXCItXCIsait0KTpudWxsfSxIPVguZGVmYXVsdFZpZXc/WC5kZWZhdWx0Vmlldy5nZXRDb21wdXRlZFN0eWxlOmZ1bmN0aW9uKCl7fSxxPWEuZ2V0U3R5bGU9ZnVuY3Rpb24odCxlLGkscixzKXt2YXIgbjtyZXR1cm4gWXx8XCJvcGFjaXR5XCIhPT1lPyghciYmdC5zdHlsZVtlXT9uPXQuc3R5bGVbZV06KGk9aXx8SCh0KSk/bj1pW2VdfHxpLmdldFByb3BlcnR5VmFsdWUoZSl8fGkuZ2V0UHJvcGVydHlWYWx1ZShlLnJlcGxhY2UoUCxcIi0kMVwiKS50b0xvd2VyQ2FzZSgpKTp0LmN1cnJlbnRTdHlsZSYmKG49dC5jdXJyZW50U3R5bGVbZV0pLG51bGw9PXN8fG4mJlwibm9uZVwiIT09biYmXCJhdXRvXCIhPT1uJiZcImF1dG8gYXV0b1wiIT09bj9uOnMpOkIodCl9LFE9RS5jb252ZXJ0VG9QaXhlbHM9ZnVuY3Rpb24odCxpLHIscyxuKXtpZihcInB4XCI9PT1zfHwhcylyZXR1cm4gcjtpZihcImF1dG9cIj09PXN8fCFyKXJldHVybiAwO3ZhciBvLGwsaCx1PWsudGVzdChpKSxmPXQsXz16LnN0eWxlLHA9MD5yO2lmKHAmJihyPS1yKSxcIiVcIj09PXMmJi0xIT09aS5pbmRleE9mKFwiYm9yZGVyXCIpKW89ci8xMDAqKHU/dC5jbGllbnRXaWR0aDp0LmNsaWVudEhlaWdodCk7ZWxzZXtpZihfLmNzc1RleHQ9XCJib3JkZXI6MCBzb2xpZCByZWQ7cG9zaXRpb246XCIrcSh0LFwicG9zaXRpb25cIikrXCI7bGluZS1oZWlnaHQ6MDtcIixcIiVcIiE9PXMmJmYuYXBwZW5kQ2hpbGQpX1t1P1wiYm9yZGVyTGVmdFdpZHRoXCI6XCJib3JkZXJUb3BXaWR0aFwiXT1yK3M7ZWxzZXtpZihmPXQucGFyZW50Tm9kZXx8WC5ib2R5LGw9Zi5fZ3NDYWNoZSxoPWUudGlja2VyLmZyYW1lLGwmJnUmJmwudGltZT09PWgpcmV0dXJuIGwud2lkdGgqci8xMDA7X1t1P1wid2lkdGhcIjpcImhlaWdodFwiXT1yK3N9Zi5hcHBlbmRDaGlsZCh6KSxvPXBhcnNlRmxvYXQoelt1P1wib2Zmc2V0V2lkdGhcIjpcIm9mZnNldEhlaWdodFwiXSksZi5yZW1vdmVDaGlsZCh6KSx1JiZcIiVcIj09PXMmJmEuY2FjaGVXaWR0aHMhPT0hMSYmKGw9Zi5fZ3NDYWNoZT1mLl9nc0NhY2hlfHx7fSxsLnRpbWU9aCxsLndpZHRoPTEwMCooby9yKSksMCE9PW98fG58fChvPVEodCxpLHIscywhMCkpfXJldHVybiBwPy1vOm99LFo9RS5jYWxjdWxhdGVPZmZzZXQ9ZnVuY3Rpb24odCxlLGkpe2lmKFwiYWJzb2x1dGVcIiE9PXEodCxcInBvc2l0aW9uXCIsaSkpcmV0dXJuIDA7dmFyIHI9XCJsZWZ0XCI9PT1lP1wiTGVmdFwiOlwiVG9wXCIscz1xKHQsXCJtYXJnaW5cIityLGkpO3JldHVybiB0W1wib2Zmc2V0XCIrcl0tKFEodCxlLHBhcnNlRmxvYXQocykscy5yZXBsYWNlKHksXCJcIikpfHwwKX0sJD1mdW5jdGlvbih0LGUpe3ZhciBpLHIscz17fTtpZihlPWV8fEgodCxudWxsKSlpZihpPWUubGVuZ3RoKWZvcig7LS1pPi0xOylzW2VbaV0ucmVwbGFjZShTLFIpXT1lLmdldFByb3BlcnR5VmFsdWUoZVtpXSk7ZWxzZSBmb3IoaSBpbiBlKXNbaV09ZVtpXTtlbHNlIGlmKGU9dC5jdXJyZW50U3R5bGV8fHQuc3R5bGUpZm9yKGkgaW4gZSlcInN0cmluZ1wiPT10eXBlb2YgaSYmdm9pZCAwPT09c1tpXSYmKHNbaS5yZXBsYWNlKFMsUildPWVbaV0pO3JldHVybiBZfHwocy5vcGFjaXR5PUIodCkpLHI9UGUodCxlLCExKSxzLnJvdGF0aW9uPXIucm90YXRpb24scy5za2V3WD1yLnNrZXdYLHMuc2NhbGVYPXIuc2NhbGVYLHMuc2NhbGVZPXIuc2NhbGVZLHMueD1yLngscy55PXIueSx4ZSYmKHMuej1yLnoscy5yb3RhdGlvblg9ci5yb3RhdGlvblgscy5yb3RhdGlvblk9ci5yb3RhdGlvblkscy5zY2FsZVo9ci5zY2FsZVopLHMuZmlsdGVycyYmZGVsZXRlIHMuZmlsdGVycyxzfSxHPWZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG4sYSxvLGw9e30saD10LnN0eWxlO2ZvcihhIGluIGkpXCJjc3NUZXh0XCIhPT1hJiZcImxlbmd0aFwiIT09YSYmaXNOYU4oYSkmJihlW2FdIT09KG49aVthXSl8fHMmJnNbYV0pJiYtMT09PWEuaW5kZXhPZihcIk9yaWdpblwiKSYmKFwibnVtYmVyXCI9PXR5cGVvZiBufHxcInN0cmluZ1wiPT10eXBlb2YgbikmJihsW2FdPVwiYXV0b1wiIT09bnx8XCJsZWZ0XCIhPT1hJiZcInRvcFwiIT09YT9cIlwiIT09biYmXCJhdXRvXCIhPT1uJiZcIm5vbmVcIiE9PW58fFwic3RyaW5nXCIhPXR5cGVvZiBlW2FdfHxcIlwiPT09ZVthXS5yZXBsYWNlKHYsXCJcIik/bjowOloodCxhKSx2b2lkIDAhPT1oW2FdJiYobz1uZXcgZmUoaCxhLGhbYV0sbykpKTtpZihyKWZvcihhIGluIHIpXCJjbGFzc05hbWVcIiE9PWEmJihsW2FdPXJbYV0pO3JldHVybntkaWZzOmwsZmlyc3RNUFQ6b319LEs9e3dpZHRoOltcIkxlZnRcIixcIlJpZ2h0XCJdLGhlaWdodDpbXCJUb3BcIixcIkJvdHRvbVwiXX0sSj1bXCJtYXJnaW5MZWZ0XCIsXCJtYXJnaW5SaWdodFwiLFwibWFyZ2luVG9wXCIsXCJtYXJnaW5Cb3R0b21cIl0sdGU9ZnVuY3Rpb24odCxlLGkpe3ZhciByPXBhcnNlRmxvYXQoXCJ3aWR0aFwiPT09ZT90Lm9mZnNldFdpZHRoOnQub2Zmc2V0SGVpZ2h0KSxzPUtbZV0sbj1zLmxlbmd0aDtmb3IoaT1pfHxIKHQsbnVsbCk7LS1uPi0xOylyLT1wYXJzZUZsb2F0KHEodCxcInBhZGRpbmdcIitzW25dLGksITApKXx8MCxyLT1wYXJzZUZsb2F0KHEodCxcImJvcmRlclwiK3Nbbl0rXCJXaWR0aFwiLGksITApKXx8MDtyZXR1cm4gcn0sZWU9ZnVuY3Rpb24odCxlKXsobnVsbD09dHx8XCJcIj09PXR8fFwiYXV0b1wiPT09dHx8XCJhdXRvIGF1dG9cIj09PXQpJiYodD1cIjAgMFwiKTt2YXIgaT10LnNwbGl0KFwiIFwiKSxyPS0xIT09dC5pbmRleE9mKFwibGVmdFwiKT9cIjAlXCI6LTEhPT10LmluZGV4T2YoXCJyaWdodFwiKT9cIjEwMCVcIjppWzBdLHM9LTEhPT10LmluZGV4T2YoXCJ0b3BcIik/XCIwJVwiOi0xIT09dC5pbmRleE9mKFwiYm90dG9tXCIpP1wiMTAwJVwiOmlbMV07cmV0dXJuIG51bGw9PXM/cz1cIjBcIjpcImNlbnRlclwiPT09cyYmKHM9XCI1MCVcIiksKFwiY2VudGVyXCI9PT1yfHxpc05hTihwYXJzZUZsb2F0KHIpKSYmLTE9PT0ocitcIlwiKS5pbmRleE9mKFwiPVwiKSkmJihyPVwiNTAlXCIpLGUmJihlLm94cD0tMSE9PXIuaW5kZXhPZihcIiVcIiksZS5veXA9LTEhPT1zLmluZGV4T2YoXCIlXCIpLGUub3hyPVwiPVwiPT09ci5jaGFyQXQoMSksZS5veXI9XCI9XCI9PT1zLmNoYXJBdCgxKSxlLm94PXBhcnNlRmxvYXQoci5yZXBsYWNlKHYsXCJcIikpLGUub3k9cGFyc2VGbG9hdChzLnJlcGxhY2UodixcIlwiKSkpLHIrXCIgXCIrcysoaS5sZW5ndGg+Mj9cIiBcIitpWzJdOlwiXCIpfSxpZT1mdW5jdGlvbih0LGUpe3JldHVyblwic3RyaW5nXCI9PXR5cGVvZiB0JiZcIj1cIj09PXQuY2hhckF0KDEpP3BhcnNlSW50KHQuY2hhckF0KDApK1wiMVwiLDEwKSpwYXJzZUZsb2F0KHQuc3Vic3RyKDIpKTpwYXJzZUZsb2F0KHQpLXBhcnNlRmxvYXQoZSl9LHJlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGw9PXQ/ZTpcInN0cmluZ1wiPT10eXBlb2YgdCYmXCI9XCI9PT10LmNoYXJBdCgxKT9wYXJzZUludCh0LmNoYXJBdCgwKStcIjFcIiwxMCkqTnVtYmVyKHQuc3Vic3RyKDIpKStlOnBhcnNlRmxvYXQodCl9LHNlPWZ1bmN0aW9uKHQsZSxpLHIpe3ZhciBzLG4sYSxvLGw9MWUtNjtyZXR1cm4gbnVsbD09dD9vPWU6XCJudW1iZXJcIj09dHlwZW9mIHQ/bz10OihzPTM2MCxuPXQuc3BsaXQoXCJfXCIpLGE9TnVtYmVyKG5bMF0ucmVwbGFjZSh2LFwiXCIpKSooLTE9PT10LmluZGV4T2YoXCJyYWRcIik/MTpMKS0oXCI9XCI9PT10LmNoYXJBdCgxKT8wOmUpLG4ubGVuZ3RoJiYociYmKHJbaV09ZSthKSwtMSE9PXQuaW5kZXhPZihcInNob3J0XCIpJiYoYSU9cyxhIT09YSUocy8yKSYmKGE9MD5hP2ErczphLXMpKSwtMSE9PXQuaW5kZXhPZihcIl9jd1wiKSYmMD5hP2E9KGErOTk5OTk5OTk5OSpzKSVzLSgwfGEvcykqczotMSE9PXQuaW5kZXhPZihcImNjd1wiKSYmYT4wJiYoYT0oYS05OTk5OTk5OTk5KnMpJXMtKDB8YS9zKSpzKSksbz1lK2EpLGw+byYmbz4tbCYmKG89MCksb30sbmU9e2FxdWE6WzAsMjU1LDI1NV0sbGltZTpbMCwyNTUsMF0sc2lsdmVyOlsxOTIsMTkyLDE5Ml0sYmxhY2s6WzAsMCwwXSxtYXJvb246WzEyOCwwLDBdLHRlYWw6WzAsMTI4LDEyOF0sYmx1ZTpbMCwwLDI1NV0sbmF2eTpbMCwwLDEyOF0sd2hpdGU6WzI1NSwyNTUsMjU1XSxmdWNoc2lhOlsyNTUsMCwyNTVdLG9saXZlOlsxMjgsMTI4LDBdLHllbGxvdzpbMjU1LDI1NSwwXSxvcmFuZ2U6WzI1NSwxNjUsMF0sZ3JheTpbMTI4LDEyOCwxMjhdLHB1cnBsZTpbMTI4LDAsMTI4XSxncmVlbjpbMCwxMjgsMF0scmVkOlsyNTUsMCwwXSxwaW5rOlsyNTUsMTkyLDIwM10sY3lhbjpbMCwyNTUsMjU1XSx0cmFuc3BhcmVudDpbMjU1LDI1NSwyNTUsMF19LGFlPWZ1bmN0aW9uKHQsZSxpKXtyZXR1cm4gdD0wPnQ/dCsxOnQ+MT90LTE6dCwwfDI1NSooMT42KnQ/ZSs2KihpLWUpKnQ6LjU+dD9pOjI+Myp0P2UrNiooaS1lKSooMi8zLXQpOmUpKy41fSxvZT1mdW5jdGlvbih0KXt2YXIgZSxpLHIscyxuLGE7cmV0dXJuIHQmJlwiXCIhPT10P1wibnVtYmVyXCI9PXR5cGVvZiB0P1t0Pj4xNiwyNTUmdD4+OCwyNTUmdF06KFwiLFwiPT09dC5jaGFyQXQodC5sZW5ndGgtMSkmJih0PXQuc3Vic3RyKDAsdC5sZW5ndGgtMSkpLG5lW3RdP25lW3RdOlwiI1wiPT09dC5jaGFyQXQoMCk/KDQ9PT10Lmxlbmd0aCYmKGU9dC5jaGFyQXQoMSksaT10LmNoYXJBdCgyKSxyPXQuY2hhckF0KDMpLHQ9XCIjXCIrZStlK2kraStyK3IpLHQ9cGFyc2VJbnQodC5zdWJzdHIoMSksMTYpLFt0Pj4xNiwyNTUmdD4+OCwyNTUmdF0pOlwiaHNsXCI9PT10LnN1YnN0cigwLDMpPyh0PXQubWF0Y2goZCkscz1OdW1iZXIodFswXSklMzYwLzM2MCxuPU51bWJlcih0WzFdKS8xMDAsYT1OdW1iZXIodFsyXSkvMTAwLGk9LjU+PWE/YSoobisxKTphK24tYSpuLGU9MiphLWksdC5sZW5ndGg+MyYmKHRbM109TnVtYmVyKHRbM10pKSx0WzBdPWFlKHMrMS8zLGUsaSksdFsxXT1hZShzLGUsaSksdFsyXT1hZShzLTEvMyxlLGkpLHQpOih0PXQubWF0Y2goZCl8fG5lLnRyYW5zcGFyZW50LHRbMF09TnVtYmVyKHRbMF0pLHRbMV09TnVtYmVyKHRbMV0pLHRbMl09TnVtYmVyKHRbMl0pLHQubGVuZ3RoPjMmJih0WzNdPU51bWJlcih0WzNdKSksdCkpOm5lLmJsYWNrfSxsZT1cIig/OlxcXFxiKD86KD86cmdifHJnYmF8aHNsfGhzbGEpXFxcXCguKz9cXFxcKSl8XFxcXEIjLis/XFxcXGJcIjtmb3IobCBpbiBuZSlsZSs9XCJ8XCIrbCtcIlxcXFxiXCI7bGU9UmVnRXhwKGxlK1wiKVwiLFwiZ2lcIik7dmFyIGhlPWZ1bmN0aW9uKHQsZSxpLHIpe2lmKG51bGw9PXQpcmV0dXJuIGZ1bmN0aW9uKHQpe3JldHVybiB0fTt2YXIgcyxuPWU/KHQubWF0Y2gobGUpfHxbXCJcIl0pWzBdOlwiXCIsYT10LnNwbGl0KG4pLmpvaW4oXCJcIikubWF0Y2goZyl8fFtdLG89dC5zdWJzdHIoMCx0LmluZGV4T2YoYVswXSkpLGw9XCIpXCI9PT10LmNoYXJBdCh0Lmxlbmd0aC0xKT9cIilcIjpcIlwiLGg9LTEhPT10LmluZGV4T2YoXCIgXCIpP1wiIFwiOlwiLFwiLHU9YS5sZW5ndGgsZj11PjA/YVswXS5yZXBsYWNlKGQsXCJcIik6XCJcIjtyZXR1cm4gdT9zPWU/ZnVuY3Rpb24odCl7dmFyIGUsXyxwLGM7aWYoXCJudW1iZXJcIj09dHlwZW9mIHQpdCs9ZjtlbHNlIGlmKHImJkQudGVzdCh0KSl7Zm9yKGM9dC5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxwPTA7Yy5sZW5ndGg+cDtwKyspY1twXT1zKGNbcF0pO3JldHVybiBjLmpvaW4oXCIsXCIpfWlmKGU9KHQubWF0Y2gobGUpfHxbbl0pWzBdLF89dC5zcGxpdChlKS5qb2luKFwiXCIpLm1hdGNoKGcpfHxbXSxwPV8ubGVuZ3RoLHU+cC0tKWZvcig7dT4rK3A7KV9bcF09aT9fWzB8KHAtMSkvMl06YVtwXTtyZXR1cm4gbytfLmpvaW4oaCkraCtlK2wrKC0xIT09dC5pbmRleE9mKFwiaW5zZXRcIik/XCIgaW5zZXRcIjpcIlwiKX06ZnVuY3Rpb24odCl7dmFyIGUsbixfO2lmKFwibnVtYmVyXCI9PXR5cGVvZiB0KXQrPWY7ZWxzZSBpZihyJiZELnRlc3QodCkpe2ZvcihuPXQucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIiksXz0wO24ubGVuZ3RoPl87XysrKW5bX109cyhuW19dKTtyZXR1cm4gbi5qb2luKFwiLFwiKX1pZihlPXQubWF0Y2goZyl8fFtdLF89ZS5sZW5ndGgsdT5fLS0pZm9yKDt1PisrXzspZVtfXT1pP2VbMHwoXy0xKS8yXTphW19dO3JldHVybiBvK2Uuam9pbihoKStsfTpmdW5jdGlvbih0KXtyZXR1cm4gdH19LHVlPWZ1bmN0aW9uKHQpe3JldHVybiB0PXQuc3BsaXQoXCIsXCIpLGZ1bmN0aW9uKGUsaSxyLHMsbixhLG8pe3ZhciBsLGg9KGkrXCJcIikuc3BsaXQoXCIgXCIpO2ZvcihvPXt9LGw9MDs0Pmw7bCsrKW9bdFtsXV09aFtsXT1oW2xdfHxoWyhsLTEpLzI+PjBdO3JldHVybiBzLnBhcnNlKGUsbyxuLGEpfX0sZmU9KEUuX3NldFBsdWdpblJhdGlvPWZ1bmN0aW9uKHQpe3RoaXMucGx1Z2luLnNldFJhdGlvKHQpO2Zvcih2YXIgZSxpLHIscyxuPXRoaXMuZGF0YSxhPW4ucHJveHksbz1uLmZpcnN0TVBULGw9MWUtNjtvOyllPWFbby52XSxvLnI/ZT1NYXRoLnJvdW5kKGUpOmw+ZSYmZT4tbCYmKGU9MCksby50W28ucF09ZSxvPW8uX25leHQ7aWYobi5hdXRvUm90YXRlJiYobi5hdXRvUm90YXRlLnJvdGF0aW9uPWEucm90YXRpb24pLDE9PT10KWZvcihvPW4uZmlyc3RNUFQ7bzspe2lmKGk9by50LGkudHlwZSl7aWYoMT09PWkudHlwZSl7Zm9yKHM9aS54czAraS5zK2kueHMxLHI9MTtpLmw+cjtyKyspcys9aVtcInhuXCIrcl0raVtcInhzXCIrKHIrMSldO2kuZT1zfX1lbHNlIGkuZT1pLnMraS54czA7bz1vLl9uZXh0fX0sZnVuY3Rpb24odCxlLGkscixzKXt0aGlzLnQ9dCx0aGlzLnA9ZSx0aGlzLnY9aSx0aGlzLnI9cyxyJiYoci5fcHJldj10aGlzLHRoaXMuX25leHQ9cil9KSxfZT0oRS5fcGFyc2VUb1Byb3h5PWZ1bmN0aW9uKHQsZSxpLHIscyxuKXt2YXIgYSxvLGwsaCx1LGY9cixfPXt9LHA9e30sYz1pLl90cmFuc2Zvcm0sZD1OO2ZvcihpLl90cmFuc2Zvcm09bnVsbCxOPWUscj11PWkucGFyc2UodCxlLHIscyksTj1kLG4mJihpLl90cmFuc2Zvcm09YyxmJiYoZi5fcHJldj1udWxsLGYuX3ByZXYmJihmLl9wcmV2Ll9uZXh0PW51bGwpKSk7ciYmciE9PWY7KXtpZigxPj1yLnR5cGUmJihvPXIucCxwW29dPXIucytyLmMsX1tvXT1yLnMsbnx8KGg9bmV3IGZlKHIsXCJzXCIsbyxoLHIuciksci5jPTApLDE9PT1yLnR5cGUpKWZvcihhPXIubDstLWE+MDspbD1cInhuXCIrYSxvPXIucCtcIl9cIitsLHBbb109ci5kYXRhW2xdLF9bb109cltsXSxufHwoaD1uZXcgZmUocixsLG8saCxyLnJ4cFtsXSkpO3I9ci5fbmV4dH1yZXR1cm57cHJveHk6XyxlbmQ6cCxmaXJzdE1QVDpoLHB0OnV9fSxFLkNTU1Byb3BUd2Vlbj1mdW5jdGlvbih0LGUscixzLGEsbyxsLGgsdSxmLF8pe3RoaXMudD10LHRoaXMucD1lLHRoaXMucz1yLHRoaXMuYz1zLHRoaXMubj1sfHxlLHQgaW5zdGFuY2VvZiBfZXx8bi5wdXNoKHRoaXMubiksdGhpcy5yPWgsdGhpcy50eXBlPW98fDAsdSYmKHRoaXMucHI9dSxpPSEwKSx0aGlzLmI9dm9pZCAwPT09Zj9yOmYsdGhpcy5lPXZvaWQgMD09PV8/citzOl8sYSYmKHRoaXMuX25leHQ9YSxhLl9wcmV2PXRoaXMpfSkscGU9YS5wYXJzZUNvbXBsZXg9ZnVuY3Rpb24odCxlLGkscixzLG4sYSxvLGwsdSl7aT1pfHxufHxcIlwiLGE9bmV3IF9lKHQsZSwwLDAsYSx1PzI6MSxudWxsLCExLG8saSxyKSxyKz1cIlwiO3ZhciBmLF8scCxjLGcsdix5LFQsdyx4LFAsUyxDPWkuc3BsaXQoXCIsIFwiKS5qb2luKFwiLFwiKS5zcGxpdChcIiBcIiksUj1yLnNwbGl0KFwiLCBcIikuam9pbihcIixcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgsQT1oIT09ITE7Zm9yKCgtMSE9PXIuaW5kZXhPZihcIixcIil8fC0xIT09aS5pbmRleE9mKFwiLFwiKSkmJihDPUMuam9pbihcIiBcIikucmVwbGFjZShELFwiLCBcIikuc3BsaXQoXCIgXCIpLFI9Ui5qb2luKFwiIFwiKS5yZXBsYWNlKEQsXCIsIFwiKS5zcGxpdChcIiBcIiksaz1DLmxlbmd0aCksayE9PVIubGVuZ3RoJiYoQz0obnx8XCJcIikuc3BsaXQoXCIgXCIpLGs9Qy5sZW5ndGgpLGEucGx1Z2luPWwsYS5zZXRSYXRpbz11LGY9MDtrPmY7ZisrKWlmKGM9Q1tmXSxnPVJbZl0sVD1wYXJzZUZsb2F0KGMpLFR8fDA9PT1UKWEuYXBwZW5kWHRyYShcIlwiLFQsaWUoZyxUKSxnLnJlcGxhY2UobSxcIlwiKSxBJiYtMSE9PWcuaW5kZXhPZihcInB4XCIpLCEwKTtlbHNlIGlmKHMmJihcIiNcIj09PWMuY2hhckF0KDApfHxuZVtjXXx8Yi50ZXN0KGMpKSlTPVwiLFwiPT09Zy5jaGFyQXQoZy5sZW5ndGgtMSk/XCIpLFwiOlwiKVwiLGM9b2UoYyksZz1vZShnKSx3PWMubGVuZ3RoK2cubGVuZ3RoPjYsdyYmIVkmJjA9PT1nWzNdPyhhW1wieHNcIithLmxdKz1hLmw/XCIgdHJhbnNwYXJlbnRcIjpcInRyYW5zcGFyZW50XCIsYS5lPWEuZS5zcGxpdChSW2ZdKS5qb2luKFwidHJhbnNwYXJlbnRcIikpOihZfHwodz0hMSksYS5hcHBlbmRYdHJhKHc/XCJyZ2JhKFwiOlwicmdiKFwiLGNbMF0sZ1swXS1jWzBdLFwiLFwiLCEwLCEwKS5hcHBlbmRYdHJhKFwiXCIsY1sxXSxnWzFdLWNbMV0sXCIsXCIsITApLmFwcGVuZFh0cmEoXCJcIixjWzJdLGdbMl0tY1syXSx3P1wiLFwiOlMsITApLHcmJihjPTQ+Yy5sZW5ndGg/MTpjWzNdLGEuYXBwZW5kWHRyYShcIlwiLGMsKDQ+Zy5sZW5ndGg/MTpnWzNdKS1jLFMsITEpKSk7ZWxzZSBpZih2PWMubWF0Y2goZCkpe2lmKHk9Zy5tYXRjaChtKSwheXx8eS5sZW5ndGghPT12Lmxlbmd0aClyZXR1cm4gYTtmb3IocD0wLF89MDt2Lmxlbmd0aD5fO18rKylQPXZbX10seD1jLmluZGV4T2YoUCxwKSxhLmFwcGVuZFh0cmEoYy5zdWJzdHIocCx4LXApLE51bWJlcihQKSxpZSh5W19dLFApLFwiXCIsQSYmXCJweFwiPT09Yy5zdWJzdHIoeCtQLmxlbmd0aCwyKSwwPT09XykscD14K1AubGVuZ3RoO2FbXCJ4c1wiK2EubF0rPWMuc3Vic3RyKHApfWVsc2UgYVtcInhzXCIrYS5sXSs9YS5sP1wiIFwiK2M6YztpZigtMSE9PXIuaW5kZXhPZihcIj1cIikmJmEuZGF0YSl7Zm9yKFM9YS54czArYS5kYXRhLnMsZj0xO2EubD5mO2YrKylTKz1hW1wieHNcIitmXSthLmRhdGFbXCJ4blwiK2ZdO2EuZT1TK2FbXCJ4c1wiK2ZdfXJldHVybiBhLmx8fChhLnR5cGU9LTEsYS54czA9YS5lKSxhLnhmaXJzdHx8YX0sY2U9OTtmb3IobD1fZS5wcm90b3R5cGUsbC5sPWwucHI9MDstLWNlPjA7KWxbXCJ4blwiK2NlXT0wLGxbXCJ4c1wiK2NlXT1cIlwiO2wueHMwPVwiXCIsbC5fbmV4dD1sLl9wcmV2PWwueGZpcnN0PWwuZGF0YT1sLnBsdWdpbj1sLnNldFJhdGlvPWwucnhwPW51bGwsbC5hcHBlbmRYdHJhPWZ1bmN0aW9uKHQsZSxpLHIscyxuKXt2YXIgYT10aGlzLG89YS5sO3JldHVybiBhW1wieHNcIitvXSs9biYmbz9cIiBcIit0OnR8fFwiXCIsaXx8MD09PW98fGEucGx1Z2luPyhhLmwrKyxhLnR5cGU9YS5zZXRSYXRpbz8yOjEsYVtcInhzXCIrYS5sXT1yfHxcIlwiLG8+MD8oYS5kYXRhW1wieG5cIitvXT1lK2ksYS5yeHBbXCJ4blwiK29dPXMsYVtcInhuXCIrb109ZSxhLnBsdWdpbnx8KGEueGZpcnN0PW5ldyBfZShhLFwieG5cIitvLGUsaSxhLnhmaXJzdHx8YSwwLGEubixzLGEucHIpLGEueGZpcnN0LnhzMD0wKSxhKTooYS5kYXRhPXtzOmUraX0sYS5yeHA9e30sYS5zPWUsYS5jPWksYS5yPXMsYSkpOihhW1wieHNcIitvXSs9ZSsocnx8XCJcIiksYSl9O3ZhciBkZT1mdW5jdGlvbih0LGUpe2U9ZXx8e30sdGhpcy5wPWUucHJlZml4P1YodCl8fHQ6dCxvW3RdPW9bdGhpcy5wXT10aGlzLHRoaXMuZm9ybWF0PWUuZm9ybWF0dGVyfHxoZShlLmRlZmF1bHRWYWx1ZSxlLmNvbG9yLGUuY29sbGFwc2libGUsZS5tdWx0aSksZS5wYXJzZXImJih0aGlzLnBhcnNlPWUucGFyc2VyKSx0aGlzLmNscnM9ZS5jb2xvcix0aGlzLm11bHRpPWUubXVsdGksdGhpcy5rZXl3b3JkPWUua2V5d29yZCx0aGlzLmRmbHQ9ZS5kZWZhdWx0VmFsdWUsdGhpcy5wcj1lLnByaW9yaXR5fHwwfSxtZT1FLl9yZWdpc3RlckNvbXBsZXhTcGVjaWFsUHJvcD1mdW5jdGlvbih0LGUsaSl7XCJvYmplY3RcIiE9dHlwZW9mIGUmJihlPXtwYXJzZXI6aX0pO3ZhciByLHMsbj10LnNwbGl0KFwiLFwiKSxhPWUuZGVmYXVsdFZhbHVlO2ZvcihpPWl8fFthXSxyPTA7bi5sZW5ndGg+cjtyKyspZS5wcmVmaXg9MD09PXImJmUucHJlZml4LGUuZGVmYXVsdFZhbHVlPWlbcl18fGEscz1uZXcgZGUobltyXSxlKX0sZ2U9ZnVuY3Rpb24odCl7aWYoIW9bdF0pe3ZhciBlPXQuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkrdC5zdWJzdHIoMSkrXCJQbHVnaW5cIjttZSh0LHtwYXJzZXI6ZnVuY3Rpb24odCxpLHIscyxuLGEsbCl7dmFyIGg9KHdpbmRvdy5HcmVlblNvY2tHbG9iYWxzfHx3aW5kb3cpLmNvbS5ncmVlbnNvY2sucGx1Z2luc1tlXTtyZXR1cm4gaD8oaC5fY3NzUmVnaXN0ZXIoKSxvW3JdLnBhcnNlKHQsaSxyLHMsbixhLGwpKTooVShcIkVycm9yOiBcIitlK1wiIGpzIGZpbGUgbm90IGxvYWRlZC5cIiksbil9fSl9fTtsPWRlLnByb3RvdHlwZSxsLnBhcnNlQ29tcGxleD1mdW5jdGlvbih0LGUsaSxyLHMsbil7dmFyIGEsbyxsLGgsdSxmLF89dGhpcy5rZXl3b3JkO2lmKHRoaXMubXVsdGkmJihELnRlc3QoaSl8fEQudGVzdChlKT8obz1lLnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLGw9aS5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSk6XyYmKG89W2VdLGw9W2ldKSksbCl7Zm9yKGg9bC5sZW5ndGg+by5sZW5ndGg/bC5sZW5ndGg6by5sZW5ndGgsYT0wO2g+YTthKyspZT1vW2FdPW9bYV18fHRoaXMuZGZsdCxpPWxbYV09bFthXXx8dGhpcy5kZmx0LF8mJih1PWUuaW5kZXhPZihfKSxmPWkuaW5kZXhPZihfKSx1IT09ZiYmKGk9LTE9PT1mP2w6byxpW2FdKz1cIiBcIitfKSk7ZT1vLmpvaW4oXCIsIFwiKSxpPWwuam9pbihcIiwgXCIpfXJldHVybiBwZSh0LHRoaXMucCxlLGksdGhpcy5jbHJzLHRoaXMuZGZsdCxyLHRoaXMucHIscyxuKX0sbC5wYXJzZT1mdW5jdGlvbih0LGUsaSxyLG4sYSl7cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsdGhpcy5mb3JtYXQocSh0LHRoaXMucCxzLCExLHRoaXMuZGZsdCkpLHRoaXMuZm9ybWF0KGUpLG4sYSl9LGEucmVnaXN0ZXJTcGVjaWFsUHJvcD1mdW5jdGlvbih0LGUsaSl7bWUodCx7cGFyc2VyOmZ1bmN0aW9uKHQscixzLG4sYSxvKXt2YXIgbD1uZXcgX2UodCxzLDAsMCxhLDIscywhMSxpKTtyZXR1cm4gbC5wbHVnaW49byxsLnNldFJhdGlvPWUodCxyLG4uX3R3ZWVuLHMpLGx9LHByaW9yaXR5Oml9KX07dmFyIHZlPVwic2NhbGVYLHNjYWxlWSxzY2FsZVoseCx5LHosc2tld1gsc2tld1kscm90YXRpb24scm90YXRpb25YLHJvdGF0aW9uWSxwZXJzcGVjdGl2ZVwiLnNwbGl0KFwiLFwiKSx5ZT1WKFwidHJhbnNmb3JtXCIpLFRlPVcrXCJ0cmFuc2Zvcm1cIix3ZT1WKFwidHJhbnNmb3JtT3JpZ2luXCIpLHhlPW51bGwhPT1WKFwicGVyc3BlY3RpdmVcIiksYmU9RS5UcmFuc2Zvcm09ZnVuY3Rpb24oKXt0aGlzLnNrZXdZPTB9LFBlPUUuZ2V0VHJhbnNmb3JtPWZ1bmN0aW9uKHQsZSxpLHIpe2lmKHQuX2dzVHJhbnNmb3JtJiZpJiYhcilyZXR1cm4gdC5fZ3NUcmFuc2Zvcm07dmFyIHMsbixvLGwsaCx1LGYsXyxwLGMsZCxtLGcsdj1pP3QuX2dzVHJhbnNmb3JtfHxuZXcgYmU6bmV3IGJlLHk9MD52LnNjYWxlWCxUPTJlLTUsdz0xZTUseD0xNzkuOTksYj14Kk0sUD14ZT9wYXJzZUZsb2F0KHEodCx3ZSxlLCExLFwiMCAwIDBcIikuc3BsaXQoXCIgXCIpWzJdKXx8di56T3JpZ2lufHwwOjA7Zm9yKHllP3M9cSh0LFRlLGUsITApOnQuY3VycmVudFN0eWxlJiYocz10LmN1cnJlbnRTdHlsZS5maWx0ZXIubWF0Y2goQSkscz1zJiY0PT09cy5sZW5ndGg/W3NbMF0uc3Vic3RyKDQpLE51bWJlcihzWzJdLnN1YnN0cig0KSksTnVtYmVyKHNbMV0uc3Vic3RyKDQpKSxzWzNdLnN1YnN0cig0KSx2Lnh8fDAsdi55fHwwXS5qb2luKFwiLFwiKTpcIlwiKSxuPShzfHxcIlwiKS5tYXRjaCgvKD86XFwtfFxcYilbXFxkXFwtXFwuZV0rXFxiL2dpKXx8W10sbz1uLmxlbmd0aDstLW8+LTE7KWw9TnVtYmVyKG5bb10pLG5bb109KGg9bC0obHw9MCkpPygwfGgqdysoMD5oPy0uNTouNSkpL3crbDpsO2lmKDE2PT09bi5sZW5ndGgpe3ZhciBTPW5bOF0sQz1uWzldLFI9blsxMF0saz1uWzEyXSxPPW5bMTNdLEQ9blsxNF07aWYodi56T3JpZ2luJiYoRD0tdi56T3JpZ2luLGs9UypELW5bMTJdLE89QypELW5bMTNdLEQ9UipEK3Yuek9yaWdpbi1uWzE0XSksIWl8fHJ8fG51bGw9PXYucm90YXRpb25YKXt2YXIgTixYLHosSSxFLEYsWSxCPW5bMF0sVT1uWzFdLFc9blsyXSxqPW5bM10sVj1uWzRdLEg9bls1XSxRPW5bNl0sWj1uWzddLCQ9blsxMV0sRz1NYXRoLmF0YW4yKFEsUiksSz0tYj5HfHxHPmI7di5yb3RhdGlvblg9RypMLEcmJihJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxOPVYqSStTKkUsWD1IKkkrQypFLHo9USpJK1IqRSxTPVYqLUUrUypJLEM9SCotRStDKkksUj1RKi1FK1IqSSwkPVoqLUUrJCpJLFY9TixIPVgsUT16KSxHPU1hdGguYXRhbjIoUyxCKSx2LnJvdGF0aW9uWT1HKkwsRyYmKEY9LWI+R3x8Rz5iLEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLE49QipJLVMqRSxYPVUqSS1DKkUsej1XKkktUipFLEM9VSpFK0MqSSxSPVcqRStSKkksJD1qKkUrJCpJLEI9TixVPVgsVz16KSxHPU1hdGguYXRhbjIoVSxIKSx2LnJvdGF0aW9uPUcqTCxHJiYoWT0tYj5HfHxHPmIsST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksQj1CKkkrVipFLFg9VSpJK0gqRSxIPVUqLUUrSCpJLFE9VyotRStRKkksVT1YKSxZJiZLP3Yucm90YXRpb249di5yb3RhdGlvblg9MDpZJiZGP3Yucm90YXRpb249di5yb3RhdGlvblk9MDpGJiZLJiYodi5yb3RhdGlvblk9di5yb3RhdGlvblg9MCksdi5zY2FsZVg9KDB8TWF0aC5zcXJ0KEIqQitVKlUpKncrLjUpL3csdi5zY2FsZVk9KDB8TWF0aC5zcXJ0KEgqSCtDKkMpKncrLjUpL3csdi5zY2FsZVo9KDB8TWF0aC5zcXJ0KFEqUStSKlIpKncrLjUpL3csdi5za2V3WD0wLHYucGVyc3BlY3RpdmU9JD8xLygwPiQ/LSQ6JCk6MCx2Lng9ayx2Lnk9Tyx2Lno9RH19ZWxzZSBpZighKHhlJiYhciYmbi5sZW5ndGgmJnYueD09PW5bNF0mJnYueT09PW5bNV0mJih2LnJvdGF0aW9uWHx8di5yb3RhdGlvblkpfHx2b2lkIDAhPT12LngmJlwibm9uZVwiPT09cSh0LFwiZGlzcGxheVwiLGUpKSl7dmFyIEo9bi5sZW5ndGg+PTYsdGU9Sj9uWzBdOjEsZWU9blsxXXx8MCxpZT1uWzJdfHwwLHJlPUo/blszXToxO3YueD1uWzRdfHwwLHYueT1uWzVdfHwwLHU9TWF0aC5zcXJ0KHRlKnRlK2VlKmVlKSxmPU1hdGguc3FydChyZSpyZStpZSppZSksXz10ZXx8ZWU/TWF0aC5hdGFuMihlZSx0ZSkqTDp2LnJvdGF0aW9ufHwwLHA9aWV8fHJlP01hdGguYXRhbjIoaWUscmUpKkwrXzp2LnNrZXdYfHwwLGM9dS1NYXRoLmFicyh2LnNjYWxlWHx8MCksZD1mLU1hdGguYWJzKHYuc2NhbGVZfHwwKSxNYXRoLmFicyhwKT45MCYmMjcwPk1hdGguYWJzKHApJiYoeT8odSo9LTEscCs9MD49Xz8xODA6LTE4MCxfKz0wPj1fPzE4MDotMTgwKTooZio9LTEscCs9MD49cD8xODA6LTE4MCkpLG09KF8tdi5yb3RhdGlvbiklMTgwLGc9KHAtdi5za2V3WCklMTgwLCh2b2lkIDA9PT12LnNrZXdYfHxjPlR8fC1UPmN8fGQ+VHx8LVQ+ZHx8bT4teCYmeD5tJiZmYWxzZXxtKnd8fGc+LXgmJng+ZyYmZmFsc2V8Zyp3KSYmKHYuc2NhbGVYPXUsdi5zY2FsZVk9Zix2LnJvdGF0aW9uPV8sdi5za2V3WD1wKSx4ZSYmKHYucm90YXRpb25YPXYucm90YXRpb25ZPXYuej0wLHYucGVyc3BlY3RpdmU9cGFyc2VGbG9hdChhLmRlZmF1bHRUcmFuc2Zvcm1QZXJzcGVjdGl2ZSl8fDAsdi5zY2FsZVo9MSl9di56T3JpZ2luPVA7Zm9yKG8gaW4gdilUPnZbb10mJnZbb10+LVQmJih2W29dPTApO3JldHVybiBpJiYodC5fZ3NUcmFuc2Zvcm09diksdn0sU2U9ZnVuY3Rpb24odCl7dmFyIGUsaSxyPXRoaXMuZGF0YSxzPS1yLnJvdGF0aW9uKk0sbj1zK3Iuc2tld1gqTSxhPTFlNSxvPSgwfE1hdGguY29zKHMpKnIuc2NhbGVYKmEpL2EsbD0oMHxNYXRoLnNpbihzKSpyLnNjYWxlWCphKS9hLGg9KDB8TWF0aC5zaW4obikqLXIuc2NhbGVZKmEpL2EsdT0oMHxNYXRoLmNvcyhuKSpyLnNjYWxlWSphKS9hLGY9dGhpcy50LnN0eWxlLF89dGhpcy50LmN1cnJlbnRTdHlsZTtpZihfKXtpPWwsbD0taCxoPS1pLGU9Xy5maWx0ZXIsZi5maWx0ZXI9XCJcIjt2YXIgcCxkLG09dGhpcy50Lm9mZnNldFdpZHRoLGc9dGhpcy50Lm9mZnNldEhlaWdodCx2PVwiYWJzb2x1dGVcIiE9PV8ucG9zaXRpb24sdz1cInByb2dpZDpEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5NYXRyaXgoTTExPVwiK28rXCIsIE0xMj1cIitsK1wiLCBNMjE9XCIraCtcIiwgTTIyPVwiK3UseD1yLngsYj1yLnk7aWYobnVsbCE9ci5veCYmKHA9KHIub3hwPy4wMSptKnIub3g6ci5veCktbS8yLGQ9KHIub3lwPy4wMSpnKnIub3k6ci5veSktZy8yLHgrPXAtKHAqbytkKmwpLGIrPWQtKHAqaCtkKnUpKSx2PyhwPW0vMixkPWcvMix3Kz1cIiwgRHg9XCIrKHAtKHAqbytkKmwpK3gpK1wiLCBEeT1cIisoZC0ocCpoK2QqdSkrYikrXCIpXCIpOncrPVwiLCBzaXppbmdNZXRob2Q9J2F1dG8gZXhwYW5kJylcIixmLmZpbHRlcj0tMSE9PWUuaW5kZXhPZihcIkRYSW1hZ2VUcmFuc2Zvcm0uTWljcm9zb2Z0Lk1hdHJpeChcIik/ZS5yZXBsYWNlKE8sdyk6dytcIiBcIitlLCgwPT09dHx8MT09PXQpJiYxPT09byYmMD09PWwmJjA9PT1oJiYxPT09dSYmKHYmJi0xPT09dy5pbmRleE9mKFwiRHg9MCwgRHk9MFwiKXx8VC50ZXN0KGUpJiYxMDAhPT1wYXJzZUZsb2F0KFJlZ0V4cC4kMSl8fC0xPT09ZS5pbmRleE9mKFwiZ3JhZGllbnQoXCImJmUuaW5kZXhPZihcIkFscGhhXCIpKSYmZi5yZW1vdmVBdHRyaWJ1dGUoXCJmaWx0ZXJcIikpLCF2KXt2YXIgUCxTLEMsUj04PmM/MTotMTtmb3IocD1yLmllT2Zmc2V0WHx8MCxkPXIuaWVPZmZzZXRZfHwwLHIuaWVPZmZzZXRYPU1hdGgucm91bmQoKG0tKCgwPm8/LW86bykqbSsoMD5sPy1sOmwpKmcpKS8yK3gpLHIuaWVPZmZzZXRZPU1hdGgucm91bmQoKGctKCgwPnU/LXU6dSkqZysoMD5oPy1oOmgpKm0pKS8yK2IpLGNlPTA7ND5jZTtjZSsrKVM9SltjZV0sUD1fW1NdLGk9LTEhPT1QLmluZGV4T2YoXCJweFwiKT9wYXJzZUZsb2F0KFApOlEodGhpcy50LFMscGFyc2VGbG9hdChQKSxQLnJlcGxhY2UoeSxcIlwiKSl8fDAsQz1pIT09cltTXT8yPmNlPy1yLmllT2Zmc2V0WDotci5pZU9mZnNldFk6Mj5jZT9wLXIuaWVPZmZzZXRYOmQtci5pZU9mZnNldFksZltTXT0ocltTXT1NYXRoLnJvdW5kKGktQyooMD09PWNlfHwyPT09Y2U/MTpSKSkpK1wicHhcIn19fSxDZT1FLnNldDNEVHJhbnNmb3JtUmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHMsbixhLG8sbCxoLHUsZixwLGMsZCxtLGcsdix5LFQsdyx4LGIsUCxTPXRoaXMuZGF0YSxDPXRoaXMudC5zdHlsZSxSPVMucm90YXRpb24qTSxrPVMuc2NhbGVYLEE9Uy5zY2FsZVksTz1TLnNjYWxlWixEPVMucGVyc3BlY3RpdmU7aWYoISgxIT09dCYmMCE9PXR8fFwiYXV0b1wiIT09Uy5mb3JjZTNEfHxTLnJvdGF0aW9uWXx8Uy5yb3RhdGlvblh8fDEhPT1PfHxEfHxTLnopKXJldHVybiBSZS5jYWxsKHRoaXMsdCksdm9pZCAwO2lmKF8pe3ZhciBMPTFlLTQ7TD5rJiZrPi1MJiYoaz1PPTJlLTUpLEw+QSYmQT4tTCYmKEE9Tz0yZS01KSwhRHx8Uy56fHxTLnJvdGF0aW9uWHx8Uy5yb3RhdGlvbll8fChEPTApfWlmKFJ8fFMuc2tld1gpeT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLGU9eSxuPVQsUy5za2V3WCYmKFItPVMuc2tld1gqTSx5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksXCJzaW1wbGVcIj09PVMuc2tld1R5cGUmJih3PU1hdGgudGFuKFMuc2tld1gqTSksdz1NYXRoLnNxcnQoMSt3KncpLHkqPXcsVCo9dykpLGk9LVQsYT15O2Vsc2V7aWYoIShTLnJvdGF0aW9uWXx8Uy5yb3RhdGlvblh8fDEhPT1PfHxEKSlyZXR1cm4gQ1t5ZV09XCJ0cmFuc2xhdGUzZChcIitTLngrXCJweCxcIitTLnkrXCJweCxcIitTLnorXCJweClcIisoMSE9PWt8fDEhPT1BP1wiIHNjYWxlKFwiK2srXCIsXCIrQStcIilcIjpcIlwiKSx2b2lkIDA7ZT1hPTEsaT1uPTB9Zj0xLHI9cz1vPWw9aD11PXA9Yz1kPTAsbT1EPy0xL0Q6MCxnPVMuek9yaWdpbix2PTFlNSxSPVMucm90YXRpb25ZKk0sUiYmKHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxoPWYqLVQsYz1tKi1ULHI9ZSpULG89bipULGYqPXksbSo9eSxlKj15LG4qPXkpLFI9Uy5yb3RhdGlvblgqTSxSJiYoeT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLHc9aSp5K3IqVCx4PWEqeStvKlQsYj11KnkrZipULFA9ZCp5K20qVCxyPWkqLVQrcip5LG89YSotVCtvKnksZj11Ki1UK2YqeSxtPWQqLVQrbSp5LGk9dyxhPXgsdT1iLGQ9UCksMSE9PU8mJihyKj1PLG8qPU8sZio9TyxtKj1PKSwxIT09QSYmKGkqPUEsYSo9QSx1Kj1BLGQqPUEpLDEhPT1rJiYoZSo9ayxuKj1rLGgqPWssYyo9ayksZyYmKHAtPWcscz1yKnAsbD1vKnAscD1mKnArZykscz0odz0ocys9Uy54KS0oc3w9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrczpzLGw9KHc9KGwrPVMueSktKGx8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K2w6bCxwPSh3PShwKz1TLnopLShwfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditwOnAsQ1t5ZV09XCJtYXRyaXgzZChcIitbKDB8ZSp2KS92LCgwfG4qdikvdiwoMHxoKnYpL3YsKDB8Yyp2KS92LCgwfGkqdikvdiwoMHxhKnYpL3YsKDB8dSp2KS92LCgwfGQqdikvdiwoMHxyKnYpL3YsKDB8byp2KS92LCgwfGYqdikvdiwoMHxtKnYpL3YscyxsLHAsRD8xKy1wL0Q6MV0uam9pbihcIixcIikrXCIpXCJ9LFJlPUUuc2V0MkRUcmFuc2Zvcm1SYXRpbz1mdW5jdGlvbih0KXt2YXIgZSxpLHIscyxuLGE9dGhpcy5kYXRhLG89dGhpcy50LGw9by5zdHlsZTtyZXR1cm4gYS5yb3RhdGlvblh8fGEucm90YXRpb25ZfHxhLnp8fGEuZm9yY2UzRD09PSEwfHxcImF1dG9cIj09PWEuZm9yY2UzRCYmMSE9PXQmJjAhPT10Pyh0aGlzLnNldFJhdGlvPUNlLENlLmNhbGwodGhpcyx0KSx2b2lkIDApOihhLnJvdGF0aW9ufHxhLnNrZXdYPyhlPWEucm90YXRpb24qTSxpPWUtYS5za2V3WCpNLHI9MWU1LHM9YS5zY2FsZVgqcixuPWEuc2NhbGVZKnIsbFt5ZV09XCJtYXRyaXgoXCIrKDB8TWF0aC5jb3MoZSkqcykvcitcIixcIisoMHxNYXRoLnNpbihlKSpzKS9yK1wiLFwiKygwfE1hdGguc2luKGkpKi1uKS9yK1wiLFwiKygwfE1hdGguY29zKGkpKm4pL3IrXCIsXCIrYS54K1wiLFwiK2EueStcIilcIik6bFt5ZV09XCJtYXRyaXgoXCIrYS5zY2FsZVgrXCIsMCwwLFwiK2Euc2NhbGVZK1wiLFwiK2EueCtcIixcIithLnkrXCIpXCIsdm9pZCAwKX07bWUoXCJ0cmFuc2Zvcm0sc2NhbGUsc2NhbGVYLHNjYWxlWSxzY2FsZVoseCx5LHoscm90YXRpb24scm90YXRpb25YLHJvdGF0aW9uWSxyb3RhdGlvblosc2tld1gsc2tld1ksc2hvcnRSb3RhdGlvbixzaG9ydFJvdGF0aW9uWCxzaG9ydFJvdGF0aW9uWSxzaG9ydFJvdGF0aW9uWix0cmFuc2Zvcm1PcmlnaW4sdHJhbnNmb3JtUGVyc3BlY3RpdmUsZGlyZWN0aW9uYWxSb3RhdGlvbixwYXJzZVRyYW5zZm9ybSxmb3JjZTNELHNrZXdUeXBlXCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sbyxsKXtpZihyLl90cmFuc2Zvcm0pcmV0dXJuIG47dmFyIGgsdSxmLF8scCxjLGQsbT1yLl90cmFuc2Zvcm09UGUodCxzLCEwLGwucGFyc2VUcmFuc2Zvcm0pLGc9dC5zdHlsZSx2PTFlLTYseT12ZS5sZW5ndGgsVD1sLHc9e307aWYoXCJzdHJpbmdcIj09dHlwZW9mIFQudHJhbnNmb3JtJiZ5ZSlmPXouc3R5bGUsZlt5ZV09VC50cmFuc2Zvcm0sZi5kaXNwbGF5PVwiYmxvY2tcIixmLnBvc2l0aW9uPVwiYWJzb2x1dGVcIixYLmJvZHkuYXBwZW5kQ2hpbGQoeiksaD1QZSh6LG51bGwsITEpLFguYm9keS5yZW1vdmVDaGlsZCh6KTtlbHNlIGlmKFwib2JqZWN0XCI9PXR5cGVvZiBUKXtpZihoPXtzY2FsZVg6cmUobnVsbCE9VC5zY2FsZVg/VC5zY2FsZVg6VC5zY2FsZSxtLnNjYWxlWCksc2NhbGVZOnJlKG51bGwhPVQuc2NhbGVZP1Quc2NhbGVZOlQuc2NhbGUsbS5zY2FsZVkpLHNjYWxlWjpyZShULnNjYWxlWixtLnNjYWxlWikseDpyZShULngsbS54KSx5OnJlKFQueSxtLnkpLHo6cmUoVC56LG0ueikscGVyc3BlY3RpdmU6cmUoVC50cmFuc2Zvcm1QZXJzcGVjdGl2ZSxtLnBlcnNwZWN0aXZlKX0sZD1ULmRpcmVjdGlvbmFsUm90YXRpb24sbnVsbCE9ZClpZihcIm9iamVjdFwiPT10eXBlb2YgZClmb3IoZiBpbiBkKVRbZl09ZFtmXTtlbHNlIFQucm90YXRpb249ZDtoLnJvdGF0aW9uPXNlKFwicm90YXRpb25cImluIFQ/VC5yb3RhdGlvbjpcInNob3J0Um90YXRpb25cImluIFQ/VC5zaG9ydFJvdGF0aW9uK1wiX3Nob3J0XCI6XCJyb3RhdGlvblpcImluIFQ/VC5yb3RhdGlvblo6bS5yb3RhdGlvbixtLnJvdGF0aW9uLFwicm90YXRpb25cIix3KSx4ZSYmKGgucm90YXRpb25YPXNlKFwicm90YXRpb25YXCJpbiBUP1Qucm90YXRpb25YOlwic2hvcnRSb3RhdGlvblhcImluIFQ/VC5zaG9ydFJvdGF0aW9uWCtcIl9zaG9ydFwiOm0ucm90YXRpb25YfHwwLG0ucm90YXRpb25YLFwicm90YXRpb25YXCIsdyksaC5yb3RhdGlvblk9c2UoXCJyb3RhdGlvbllcImluIFQ/VC5yb3RhdGlvblk6XCJzaG9ydFJvdGF0aW9uWVwiaW4gVD9ULnNob3J0Um90YXRpb25ZK1wiX3Nob3J0XCI6bS5yb3RhdGlvbll8fDAsbS5yb3RhdGlvblksXCJyb3RhdGlvbllcIix3KSksaC5za2V3WD1udWxsPT1ULnNrZXdYP20uc2tld1g6c2UoVC5za2V3WCxtLnNrZXdYKSxoLnNrZXdZPW51bGw9PVQuc2tld1k/bS5za2V3WTpzZShULnNrZXdZLG0uc2tld1kpLCh1PWguc2tld1ktbS5za2V3WSkmJihoLnNrZXdYKz11LGgucm90YXRpb24rPXUpfWZvcih4ZSYmbnVsbCE9VC5mb3JjZTNEJiYobS5mb3JjZTNEPVQuZm9yY2UzRCxjPSEwKSxtLnNrZXdUeXBlPVQuc2tld1R5cGV8fG0uc2tld1R5cGV8fGEuZGVmYXVsdFNrZXdUeXBlLHA9bS5mb3JjZTNEfHxtLnp8fG0ucm90YXRpb25YfHxtLnJvdGF0aW9uWXx8aC56fHxoLnJvdGF0aW9uWHx8aC5yb3RhdGlvbll8fGgucGVyc3BlY3RpdmUscHx8bnVsbD09VC5zY2FsZXx8KGguc2NhbGVaPTEpOy0teT4tMTspaT12ZVt5XSxfPWhbaV0tbVtpXSwoXz52fHwtdj5ffHxudWxsIT1OW2ldKSYmKGM9ITAsbj1uZXcgX2UobSxpLG1baV0sXyxuKSxpIGluIHcmJihuLmU9d1tpXSksbi54czA9MCxuLnBsdWdpbj1vLHIuX292ZXJ3cml0ZVByb3BzLnB1c2gobi5uKSk7cmV0dXJuIF89VC50cmFuc2Zvcm1PcmlnaW4sKF98fHhlJiZwJiZtLnpPcmlnaW4pJiYoeWU/KGM9ITAsaT13ZSxfPShffHxxKHQsaSxzLCExLFwiNTAlIDUwJVwiKSkrXCJcIixuPW5ldyBfZShnLGksMCwwLG4sLTEsXCJ0cmFuc2Zvcm1PcmlnaW5cIiksbi5iPWdbaV0sbi5wbHVnaW49byx4ZT8oZj1tLnpPcmlnaW4sXz1fLnNwbGl0KFwiIFwiKSxtLnpPcmlnaW49KF8ubGVuZ3RoPjImJigwPT09Znx8XCIwcHhcIiE9PV9bMl0pP3BhcnNlRmxvYXQoX1syXSk6Zil8fDAsbi54czA9bi5lPV9bMF0rXCIgXCIrKF9bMV18fFwiNTAlXCIpK1wiIDBweFwiLG49bmV3IF9lKG0sXCJ6T3JpZ2luXCIsMCwwLG4sLTEsbi5uKSxuLmI9ZixuLnhzMD1uLmU9bS56T3JpZ2luKTpuLnhzMD1uLmU9Xyk6ZWUoXytcIlwiLG0pKSxjJiYoci5fdHJhbnNmb3JtVHlwZT1wfHwzPT09dGhpcy5fdHJhbnNmb3JtVHlwZT8zOjIpLG59LHByZWZpeDohMH0pLG1lKFwiYm94U2hhZG93XCIse2RlZmF1bHRWYWx1ZTpcIjBweCAwcHggMHB4IDBweCAjOTk5XCIscHJlZml4OiEwLGNvbG9yOiEwLG11bHRpOiEwLGtleXdvcmQ6XCJpbnNldFwifSksbWUoXCJib3JkZXJSYWRpdXNcIix7ZGVmYXVsdFZhbHVlOlwiMHB4XCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLG4sYSl7ZT10aGlzLmZvcm1hdChlKTt2YXIgbyxsLGgsdSxmLF8scCxjLGQsbSxnLHYseSxULHcseCxiPVtcImJvcmRlclRvcExlZnRSYWRpdXNcIixcImJvcmRlclRvcFJpZ2h0UmFkaXVzXCIsXCJib3JkZXJCb3R0b21SaWdodFJhZGl1c1wiLFwiYm9yZGVyQm90dG9tTGVmdFJhZGl1c1wiXSxQPXQuc3R5bGU7Zm9yKGQ9cGFyc2VGbG9hdCh0Lm9mZnNldFdpZHRoKSxtPXBhcnNlRmxvYXQodC5vZmZzZXRIZWlnaHQpLG89ZS5zcGxpdChcIiBcIiksbD0wO2IubGVuZ3RoPmw7bCsrKXRoaXMucC5pbmRleE9mKFwiYm9yZGVyXCIpJiYoYltsXT1WKGJbbF0pKSxmPXU9cSh0LGJbbF0scywhMSxcIjBweFwiKSwtMSE9PWYuaW5kZXhPZihcIiBcIikmJih1PWYuc3BsaXQoXCIgXCIpLGY9dVswXSx1PXVbMV0pLF89aD1vW2xdLHA9cGFyc2VGbG9hdChmKSx2PWYuc3Vic3RyKChwK1wiXCIpLmxlbmd0aCkseT1cIj1cIj09PV8uY2hhckF0KDEpLHk/KGM9cGFyc2VJbnQoXy5jaGFyQXQoMCkrXCIxXCIsMTApLF89Xy5zdWJzdHIoMiksYyo9cGFyc2VGbG9hdChfKSxnPV8uc3Vic3RyKChjK1wiXCIpLmxlbmd0aC0oMD5jPzE6MCkpfHxcIlwiKTooYz1wYXJzZUZsb2F0KF8pLGc9Xy5zdWJzdHIoKGMrXCJcIikubGVuZ3RoKSksXCJcIj09PWcmJihnPXJbaV18fHYpLGchPT12JiYoVD1RKHQsXCJib3JkZXJMZWZ0XCIscCx2KSx3PVEodCxcImJvcmRlclRvcFwiLHAsdiksXCIlXCI9PT1nPyhmPTEwMCooVC9kKStcIiVcIix1PTEwMCoody9tKStcIiVcIik6XCJlbVwiPT09Zz8oeD1RKHQsXCJib3JkZXJMZWZ0XCIsMSxcImVtXCIpLGY9VC94K1wiZW1cIix1PXcveCtcImVtXCIpOihmPVQrXCJweFwiLHU9dytcInB4XCIpLHkmJihfPXBhcnNlRmxvYXQoZikrYytnLGg9cGFyc2VGbG9hdCh1KStjK2cpKSxhPXBlKFAsYltsXSxmK1wiIFwiK3UsXytcIiBcIitoLCExLFwiMHB4XCIsYSk7cmV0dXJuIGF9LHByZWZpeDohMCxmb3JtYXR0ZXI6aGUoXCIwcHggMHB4IDBweCAwcHhcIiwhMSwhMCl9KSxtZShcImJhY2tncm91bmRQb3NpdGlvblwiLHtkZWZhdWx0VmFsdWU6XCIwIDBcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvLGwsaCx1LGYsXyxwPVwiYmFja2dyb3VuZC1wb3NpdGlvblwiLGQ9c3x8SCh0LG51bGwpLG09dGhpcy5mb3JtYXQoKGQ/Yz9kLmdldFByb3BlcnR5VmFsdWUocCtcIi14XCIpK1wiIFwiK2QuZ2V0UHJvcGVydHlWYWx1ZShwK1wiLXlcIik6ZC5nZXRQcm9wZXJ0eVZhbHVlKHApOnQuY3VycmVudFN0eWxlLmJhY2tncm91bmRQb3NpdGlvblgrXCIgXCIrdC5jdXJyZW50U3R5bGUuYmFja2dyb3VuZFBvc2l0aW9uWSl8fFwiMCAwXCIpLGc9dGhpcy5mb3JtYXQoZSk7aWYoLTEhPT1tLmluZGV4T2YoXCIlXCIpIT0oLTEhPT1nLmluZGV4T2YoXCIlXCIpKSYmKF89cSh0LFwiYmFja2dyb3VuZEltYWdlXCIpLnJlcGxhY2UoQyxcIlwiKSxfJiZcIm5vbmVcIiE9PV8pKXtmb3Iobz1tLnNwbGl0KFwiIFwiKSxsPWcuc3BsaXQoXCIgXCIpLEkuc2V0QXR0cmlidXRlKFwic3JjXCIsXyksaD0yOy0taD4tMTspbT1vW2hdLHU9LTEhPT1tLmluZGV4T2YoXCIlXCIpLHUhPT0oLTEhPT1sW2hdLmluZGV4T2YoXCIlXCIpKSYmKGY9MD09PWg/dC5vZmZzZXRXaWR0aC1JLndpZHRoOnQub2Zmc2V0SGVpZ2h0LUkuaGVpZ2h0LG9baF09dT9wYXJzZUZsb2F0KG0pLzEwMCpmK1wicHhcIjoxMDAqKHBhcnNlRmxvYXQobSkvZikrXCIlXCIpO209by5qb2luKFwiIFwiKX1yZXR1cm4gdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSxtLGcsbixhKX0sZm9ybWF0dGVyOmVlfSksbWUoXCJiYWNrZ3JvdW5kU2l6ZVwiLHtkZWZhdWx0VmFsdWU6XCIwIDBcIixmb3JtYXR0ZXI6ZWV9KSxtZShcInBlcnNwZWN0aXZlXCIse2RlZmF1bHRWYWx1ZTpcIjBweFwiLHByZWZpeDohMH0pLG1lKFwicGVyc3BlY3RpdmVPcmlnaW5cIix7ZGVmYXVsdFZhbHVlOlwiNTAlIDUwJVwiLHByZWZpeDohMH0pLG1lKFwidHJhbnNmb3JtU3R5bGVcIix7cHJlZml4OiEwfSksbWUoXCJiYWNrZmFjZVZpc2liaWxpdHlcIix7cHJlZml4OiEwfSksbWUoXCJ1c2VyU2VsZWN0XCIse3ByZWZpeDohMH0pLG1lKFwibWFyZ2luXCIse3BhcnNlcjp1ZShcIm1hcmdpblRvcCxtYXJnaW5SaWdodCxtYXJnaW5Cb3R0b20sbWFyZ2luTGVmdFwiKX0pLG1lKFwicGFkZGluZ1wiLHtwYXJzZXI6dWUoXCJwYWRkaW5nVG9wLHBhZGRpbmdSaWdodCxwYWRkaW5nQm90dG9tLHBhZGRpbmdMZWZ0XCIpfSksbWUoXCJjbGlwXCIse2RlZmF1bHRWYWx1ZTpcInJlY3QoMHB4LDBweCwwcHgsMHB4KVwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7dmFyIG8sbCxoO3JldHVybiA5PmM/KGw9dC5jdXJyZW50U3R5bGUsaD04PmM/XCIgXCI6XCIsXCIsbz1cInJlY3QoXCIrbC5jbGlwVG9wK2grbC5jbGlwUmlnaHQraCtsLmNsaXBCb3R0b20raCtsLmNsaXBMZWZ0K1wiKVwiLGU9dGhpcy5mb3JtYXQoZSkuc3BsaXQoXCIsXCIpLmpvaW4oaCkpOihvPXRoaXMuZm9ybWF0KHEodCx0aGlzLnAscywhMSx0aGlzLmRmbHQpKSxlPXRoaXMuZm9ybWF0KGUpKSx0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLG8sZSxuLGEpfX0pLG1lKFwidGV4dFNoYWRvd1wiLHtkZWZhdWx0VmFsdWU6XCIwcHggMHB4IDBweCAjOTk5XCIsY29sb3I6ITAsbXVsdGk6ITB9KSxtZShcImF1dG9Sb3VuZCxzdHJpY3RVbml0c1wiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixzKXtyZXR1cm4gc319KSxtZShcImJvcmRlclwiLHtkZWZhdWx0VmFsdWU6XCIwcHggc29saWQgIzAwMFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7cmV0dXJuIHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsdGhpcy5mb3JtYXQocSh0LFwiYm9yZGVyVG9wV2lkdGhcIixzLCExLFwiMHB4XCIpK1wiIFwiK3EodCxcImJvcmRlclRvcFN0eWxlXCIscywhMSxcInNvbGlkXCIpK1wiIFwiK3EodCxcImJvcmRlclRvcENvbG9yXCIscywhMSxcIiMwMDBcIikpLHRoaXMuZm9ybWF0KGUpLG4sYSl9LGNvbG9yOiEwLGZvcm1hdHRlcjpmdW5jdGlvbih0KXt2YXIgZT10LnNwbGl0KFwiIFwiKTtyZXR1cm4gZVswXStcIiBcIisoZVsxXXx8XCJzb2xpZFwiKStcIiBcIisodC5tYXRjaChsZSl8fFtcIiMwMDBcIl0pWzBdfX0pLG1lKFwiYm9yZGVyV2lkdGhcIix7cGFyc2VyOnVlKFwiYm9yZGVyVG9wV2lkdGgsYm9yZGVyUmlnaHRXaWR0aCxib3JkZXJCb3R0b21XaWR0aCxib3JkZXJMZWZ0V2lkdGhcIil9KSxtZShcImZsb2F0LGNzc0Zsb2F0LHN0eWxlRmxvYXRcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIscyl7dmFyIG49dC5zdHlsZSxhPVwiY3NzRmxvYXRcImluIG4/XCJjc3NGbG9hdFwiOlwic3R5bGVGbG9hdFwiO3JldHVybiBuZXcgX2UobixhLDAsMCxzLC0xLGksITEsMCxuW2FdLGUpfX0pO3ZhciBrZT1mdW5jdGlvbih0KXt2YXIgZSxpPXRoaXMudCxyPWkuZmlsdGVyfHxxKHRoaXMuZGF0YSxcImZpbHRlclwiKSxzPTB8dGhpcy5zK3RoaXMuYyp0OzEwMD09PXMmJigtMT09PXIuaW5kZXhPZihcImF0cml4KFwiKSYmLTE9PT1yLmluZGV4T2YoXCJyYWRpZW50KFwiKSYmLTE9PT1yLmluZGV4T2YoXCJvYWRlcihcIik/KGkucmVtb3ZlQXR0cmlidXRlKFwiZmlsdGVyXCIpLGU9IXEodGhpcy5kYXRhLFwiZmlsdGVyXCIpKTooaS5maWx0ZXI9ci5yZXBsYWNlKHgsXCJcIiksZT0hMCkpLGV8fCh0aGlzLnhuMSYmKGkuZmlsdGVyPXI9cnx8XCJhbHBoYShvcGFjaXR5PVwiK3MrXCIpXCIpLC0xPT09ci5pbmRleE9mKFwicGFjaXR5XCIpPzA9PT1zJiZ0aGlzLnhuMXx8KGkuZmlsdGVyPXIrXCIgYWxwaGEob3BhY2l0eT1cIitzK1wiKVwiKTppLmZpbHRlcj1yLnJlcGxhY2UoVCxcIm9wYWNpdHk9XCIrcykpfTttZShcIm9wYWNpdHksYWxwaGEsYXV0b0FscGhhXCIse2RlZmF1bHRWYWx1ZTpcIjFcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixuLGEpe3ZhciBvPXBhcnNlRmxvYXQocSh0LFwib3BhY2l0eVwiLHMsITEsXCIxXCIpKSxsPXQuc3R5bGUsaD1cImF1dG9BbHBoYVwiPT09aTtyZXR1cm5cInN0cmluZ1wiPT10eXBlb2YgZSYmXCI9XCI9PT1lLmNoYXJBdCgxKSYmKGU9KFwiLVwiPT09ZS5jaGFyQXQoMCk/LTE6MSkqcGFyc2VGbG9hdChlLnN1YnN0cigyKSkrbyksaCYmMT09PW8mJlwiaGlkZGVuXCI9PT1xKHQsXCJ2aXNpYmlsaXR5XCIscykmJjAhPT1lJiYobz0wKSxZP249bmV3IF9lKGwsXCJvcGFjaXR5XCIsbyxlLW8sbik6KG49bmV3IF9lKGwsXCJvcGFjaXR5XCIsMTAwKm8sMTAwKihlLW8pLG4pLG4ueG4xPWg/MTowLGwuem9vbT0xLG4udHlwZT0yLG4uYj1cImFscGhhKG9wYWNpdHk9XCIrbi5zK1wiKVwiLG4uZT1cImFscGhhKG9wYWNpdHk9XCIrKG4ucytuLmMpK1wiKVwiLG4uZGF0YT10LG4ucGx1Z2luPWEsbi5zZXRSYXRpbz1rZSksaCYmKG49bmV3IF9lKGwsXCJ2aXNpYmlsaXR5XCIsMCwwLG4sLTEsbnVsbCwhMSwwLDAhPT1vP1wiaW5oZXJpdFwiOlwiaGlkZGVuXCIsMD09PWU/XCJoaWRkZW5cIjpcImluaGVyaXRcIiksbi54czA9XCJpbmhlcml0XCIsci5fb3ZlcndyaXRlUHJvcHMucHVzaChuLm4pLHIuX292ZXJ3cml0ZVByb3BzLnB1c2goaSkpLG59fSk7dmFyIEFlPWZ1bmN0aW9uKHQsZSl7ZSYmKHQucmVtb3ZlUHJvcGVydHk/KFwibXNcIj09PWUuc3Vic3RyKDAsMikmJihlPVwiTVwiK2Uuc3Vic3RyKDEpKSx0LnJlbW92ZVByb3BlcnR5KGUucmVwbGFjZShQLFwiLSQxXCIpLnRvTG93ZXJDYXNlKCkpKTp0LnJlbW92ZUF0dHJpYnV0ZShlKSl9LE9lPWZ1bmN0aW9uKHQpe2lmKHRoaXMudC5fZ3NDbGFzc1BUPXRoaXMsMT09PXR8fDA9PT10KXt0aGlzLnQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIiwwPT09dD90aGlzLmI6dGhpcy5lKTtmb3IodmFyIGU9dGhpcy5kYXRhLGk9dGhpcy50LnN0eWxlO2U7KWUudj9pW2UucF09ZS52OkFlKGksZS5wKSxlPWUuX25leHQ7MT09PXQmJnRoaXMudC5fZ3NDbGFzc1BUPT09dGhpcyYmKHRoaXMudC5fZ3NDbGFzc1BUPW51bGwpfWVsc2UgdGhpcy50LmdldEF0dHJpYnV0ZShcImNsYXNzXCIpIT09dGhpcy5lJiZ0aGlzLnQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIix0aGlzLmUpfTttZShcImNsYXNzTmFtZVwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLHIsbixhLG8sbCl7dmFyIGgsdSxmLF8scCxjPXQuZ2V0QXR0cmlidXRlKFwiY2xhc3NcIil8fFwiXCIsZD10LnN0eWxlLmNzc1RleHQ7aWYoYT1uLl9jbGFzc05hbWVQVD1uZXcgX2UodCxyLDAsMCxhLDIpLGEuc2V0UmF0aW89T2UsYS5wcj0tMTEsaT0hMCxhLmI9Yyx1PSQodCxzKSxmPXQuX2dzQ2xhc3NQVCl7Zm9yKF89e30scD1mLmRhdGE7cDspX1twLnBdPTEscD1wLl9uZXh0O2Yuc2V0UmF0aW8oMSl9cmV0dXJuIHQuX2dzQ2xhc3NQVD1hLGEuZT1cIj1cIiE9PWUuY2hhckF0KDEpP2U6Yy5yZXBsYWNlKFJlZ0V4cChcIlxcXFxzKlxcXFxiXCIrZS5zdWJzdHIoMikrXCJcXFxcYlwiKSxcIlwiKSsoXCIrXCI9PT1lLmNoYXJBdCgwKT9cIiBcIitlLnN1YnN0cigyKTpcIlwiKSxuLl90d2Vlbi5fZHVyYXRpb24mJih0LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsYS5lKSxoPUcodCx1LCQodCksbCxfKSx0LnNldEF0dHJpYnV0ZShcImNsYXNzXCIsYyksYS5kYXRhPWguZmlyc3RNUFQsdC5zdHlsZS5jc3NUZXh0PWQsYT1hLnhmaXJzdD1uLnBhcnNlKHQsaC5kaWZzLGEsbykpLGF9fSk7dmFyIERlPWZ1bmN0aW9uKHQpe2lmKCgxPT09dHx8MD09PXQpJiZ0aGlzLmRhdGEuX3RvdGFsVGltZT09PXRoaXMuZGF0YS5fdG90YWxEdXJhdGlvbiYmXCJpc0Zyb21TdGFydFwiIT09dGhpcy5kYXRhLmRhdGEpe3ZhciBlLGkscixzLG49dGhpcy50LnN0eWxlLGE9by50cmFuc2Zvcm0ucGFyc2U7aWYoXCJhbGxcIj09PXRoaXMuZSluLmNzc1RleHQ9XCJcIixzPSEwO2Vsc2UgZm9yKGU9dGhpcy5lLnNwbGl0KFwiLFwiKSxyPWUubGVuZ3RoOy0tcj4tMTspaT1lW3JdLG9baV0mJihvW2ldLnBhcnNlPT09YT9zPSEwOmk9XCJ0cmFuc2Zvcm1PcmlnaW5cIj09PWk/d2U6b1tpXS5wKSxBZShuLGkpO3MmJihBZShuLHllKSx0aGlzLnQuX2dzVHJhbnNmb3JtJiZkZWxldGUgdGhpcy50Ll9nc1RyYW5zZm9ybSl9fTtmb3IobWUoXCJjbGVhclByb3BzXCIse3BhcnNlcjpmdW5jdGlvbih0LGUscixzLG4pe3JldHVybiBuPW5ldyBfZSh0LHIsMCwwLG4sMiksbi5zZXRSYXRpbz1EZSxuLmU9ZSxuLnByPS0xMCxuLmRhdGE9cy5fdHdlZW4saT0hMCxufX0pLGw9XCJiZXppZXIsdGhyb3dQcm9wcyxwaHlzaWNzUHJvcHMscGh5c2ljczJEXCIuc3BsaXQoXCIsXCIpLGNlPWwubGVuZ3RoO2NlLS07KWdlKGxbY2VdKTtsPWEucHJvdG90eXBlLGwuX2ZpcnN0UFQ9bnVsbCxsLl9vbkluaXRUd2Vlbj1mdW5jdGlvbih0LGUsbyl7aWYoIXQubm9kZVR5cGUpcmV0dXJuITE7dGhpcy5fdGFyZ2V0PXQsdGhpcy5fdHdlZW49byx0aGlzLl92YXJzPWUsaD1lLmF1dG9Sb3VuZCxpPSExLHI9ZS5zdWZmaXhNYXB8fGEuc3VmZml4TWFwLHM9SCh0LFwiXCIpLG49dGhpcy5fb3ZlcndyaXRlUHJvcHM7dmFyIGwsXyxjLGQsbSxnLHYseSxULHg9dC5zdHlsZTtpZih1JiZcIlwiPT09eC56SW5kZXgmJihsPXEodCxcInpJbmRleFwiLHMpLChcImF1dG9cIj09PWx8fFwiXCI9PT1sKSYmdGhpcy5fYWRkTGF6eVNldCh4LFwiekluZGV4XCIsMCkpLFwic3RyaW5nXCI9PXR5cGVvZiBlJiYoZD14LmNzc1RleHQsbD0kKHQscykseC5jc3NUZXh0PWQrXCI7XCIrZSxsPUcodCxsLCQodCkpLmRpZnMsIVkmJncudGVzdChlKSYmKGwub3BhY2l0eT1wYXJzZUZsb2F0KFJlZ0V4cC4kMSkpLGU9bCx4LmNzc1RleHQ9ZCksdGhpcy5fZmlyc3RQVD1fPXRoaXMucGFyc2UodCxlLG51bGwpLHRoaXMuX3RyYW5zZm9ybVR5cGUpe2ZvcihUPTM9PT10aGlzLl90cmFuc2Zvcm1UeXBlLHllP2YmJih1PSEwLFwiXCI9PT14LnpJbmRleCYmKHY9cSh0LFwiekluZGV4XCIscyksKFwiYXV0b1wiPT09dnx8XCJcIj09PXYpJiZ0aGlzLl9hZGRMYXp5U2V0KHgsXCJ6SW5kZXhcIiwwKSkscCYmdGhpcy5fYWRkTGF6eVNldCh4LFwiV2Via2l0QmFja2ZhY2VWaXNpYmlsaXR5XCIsdGhpcy5fdmFycy5XZWJraXRCYWNrZmFjZVZpc2liaWxpdHl8fChUP1widmlzaWJsZVwiOlwiaGlkZGVuXCIpKSk6eC56b29tPTEsYz1fO2MmJmMuX25leHQ7KWM9Yy5fbmV4dDt5PW5ldyBfZSh0LFwidHJhbnNmb3JtXCIsMCwwLG51bGwsMiksdGhpcy5fbGlua0NTU1AoeSxudWxsLGMpLHkuc2V0UmF0aW89VCYmeGU/Q2U6eWU/UmU6U2UseS5kYXRhPXRoaXMuX3RyYW5zZm9ybXx8UGUodCxzLCEwKSxuLnBvcCgpfWlmKGkpe2Zvcig7Xzspe2ZvcihnPV8uX25leHQsYz1kO2MmJmMucHI+Xy5wcjspYz1jLl9uZXh0OyhfLl9wcmV2PWM/Yy5fcHJldjptKT9fLl9wcmV2Ll9uZXh0PV86ZD1fLChfLl9uZXh0PWMpP2MuX3ByZXY9XzptPV8sXz1nfXRoaXMuX2ZpcnN0UFQ9ZH1yZXR1cm4hMH0sbC5wYXJzZT1mdW5jdGlvbih0LGUsaSxuKXt2YXIgYSxsLHUsZixfLHAsYyxkLG0sZyx2PXQuc3R5bGU7Zm9yKGEgaW4gZSlwPWVbYV0sbD1vW2FdLGw/aT1sLnBhcnNlKHQscCxhLHRoaXMsaSxuLGUpOihfPXEodCxhLHMpK1wiXCIsbT1cInN0cmluZ1wiPT10eXBlb2YgcCxcImNvbG9yXCI9PT1hfHxcImZpbGxcIj09PWF8fFwic3Ryb2tlXCI9PT1hfHwtMSE9PWEuaW5kZXhPZihcIkNvbG9yXCIpfHxtJiZiLnRlc3QocCk/KG18fChwPW9lKHApLHA9KHAubGVuZ3RoPjM/XCJyZ2JhKFwiOlwicmdiKFwiKStwLmpvaW4oXCIsXCIpK1wiKVwiKSxpPXBlKHYsYSxfLHAsITAsXCJ0cmFuc3BhcmVudFwiLGksMCxuKSk6IW18fC0xPT09cC5pbmRleE9mKFwiIFwiKSYmLTE9PT1wLmluZGV4T2YoXCIsXCIpPyh1PXBhcnNlRmxvYXQoXyksYz11fHwwPT09dT9fLnN1YnN0cigodStcIlwiKS5sZW5ndGgpOlwiXCIsKFwiXCI9PT1ffHxcImF1dG9cIj09PV8pJiYoXCJ3aWR0aFwiPT09YXx8XCJoZWlnaHRcIj09PWE/KHU9dGUodCxhLHMpLGM9XCJweFwiKTpcImxlZnRcIj09PWF8fFwidG9wXCI9PT1hPyh1PVoodCxhLHMpLGM9XCJweFwiKToodT1cIm9wYWNpdHlcIiE9PWE/MDoxLGM9XCJcIikpLGc9bSYmXCI9XCI9PT1wLmNoYXJBdCgxKSxnPyhmPXBhcnNlSW50KHAuY2hhckF0KDApK1wiMVwiLDEwKSxwPXAuc3Vic3RyKDIpLGYqPXBhcnNlRmxvYXQocCksZD1wLnJlcGxhY2UoeSxcIlwiKSk6KGY9cGFyc2VGbG9hdChwKSxkPW0/cC5zdWJzdHIoKGYrXCJcIikubGVuZ3RoKXx8XCJcIjpcIlwiKSxcIlwiPT09ZCYmKGQ9YSBpbiByP3JbYV06YykscD1mfHwwPT09Zj8oZz9mK3U6ZikrZDplW2FdLGMhPT1kJiZcIlwiIT09ZCYmKGZ8fDA9PT1mKSYmdSYmKHU9USh0LGEsdSxjKSxcIiVcIj09PWQ/KHUvPVEodCxhLDEwMCxcIiVcIikvMTAwLGUuc3RyaWN0VW5pdHMhPT0hMCYmKF89dStcIiVcIikpOlwiZW1cIj09PWQ/dS89USh0LGEsMSxcImVtXCIpOlwicHhcIiE9PWQmJihmPVEodCxhLGYsZCksZD1cInB4XCIpLGcmJihmfHwwPT09ZikmJihwPWYrdStkKSksZyYmKGYrPXUpLCF1JiYwIT09dXx8IWYmJjAhPT1mP3ZvaWQgMCE9PXZbYV0mJihwfHxcIk5hTlwiIT1wK1wiXCImJm51bGwhPXApPyhpPW5ldyBfZSh2LGEsZnx8dXx8MCwwLGksLTEsYSwhMSwwLF8scCksaS54czA9XCJub25lXCIhPT1wfHxcImRpc3BsYXlcIiE9PWEmJi0xPT09YS5pbmRleE9mKFwiU3R5bGVcIik/cDpfKTpVKFwiaW52YWxpZCBcIithK1wiIHR3ZWVuIHZhbHVlOiBcIitlW2FdKTooaT1uZXcgX2UodixhLHUsZi11LGksMCxhLGghPT0hMSYmKFwicHhcIj09PWR8fFwiekluZGV4XCI9PT1hKSwwLF8scCksaS54czA9ZCkpOmk9cGUodixhLF8scCwhMCxudWxsLGksMCxuKSksbiYmaSYmIWkucGx1Z2luJiYoaS5wbHVnaW49bik7cmV0dXJuIGl9LGwuc2V0UmF0aW89ZnVuY3Rpb24odCl7dmFyIGUsaSxyLHM9dGhpcy5fZmlyc3RQVCxuPTFlLTY7aWYoMSE9PXR8fHRoaXMuX3R3ZWVuLl90aW1lIT09dGhpcy5fdHdlZW4uX2R1cmF0aW9uJiYwIT09dGhpcy5fdHdlZW4uX3RpbWUpaWYodHx8dGhpcy5fdHdlZW4uX3RpbWUhPT10aGlzLl90d2Vlbi5fZHVyYXRpb24mJjAhPT10aGlzLl90d2Vlbi5fdGltZXx8dGhpcy5fdHdlZW4uX3Jhd1ByZXZUaW1lPT09LTFlLTYpZm9yKDtzOyl7aWYoZT1zLmMqdCtzLnMscy5yP2U9TWF0aC5yb3VuZChlKTpuPmUmJmU+LW4mJihlPTApLHMudHlwZSlpZigxPT09cy50eXBlKWlmKHI9cy5sLDI9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czI7ZWxzZSBpZigzPT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzO2Vsc2UgaWYoND09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMytzLnhuMytzLnhzNDtlbHNlIGlmKDU9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czMrcy54bjMrcy54czQrcy54bjQrcy54czU7ZWxzZXtmb3IoaT1zLnhzMCtlK3MueHMxLHI9MTtzLmw+cjtyKyspaSs9c1tcInhuXCIrcl0rc1tcInhzXCIrKHIrMSldO3MudFtzLnBdPWl9ZWxzZS0xPT09cy50eXBlP3MudFtzLnBdPXMueHMwOnMuc2V0UmF0aW8mJnMuc2V0UmF0aW8odCk7ZWxzZSBzLnRbcy5wXT1lK3MueHMwO3M9cy5fbmV4dH1lbHNlIGZvcig7czspMiE9PXMudHlwZT9zLnRbcy5wXT1zLmI6cy5zZXRSYXRpbyh0KSxzPXMuX25leHQ7ZWxzZSBmb3IoO3M7KTIhPT1zLnR5cGU/cy50W3MucF09cy5lOnMuc2V0UmF0aW8odCkscz1zLl9uZXh0fSxsLl9lbmFibGVUcmFuc2Zvcm1zPWZ1bmN0aW9uKHQpe3RoaXMuX3RyYW5zZm9ybVR5cGU9dHx8Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGU/MzoyLHRoaXMuX3RyYW5zZm9ybT10aGlzLl90cmFuc2Zvcm18fFBlKHRoaXMuX3RhcmdldCxzLCEwKX07dmFyIE1lPWZ1bmN0aW9uKCl7dGhpcy50W3RoaXMucF09dGhpcy5lLHRoaXMuZGF0YS5fbGlua0NTU1AodGhpcyx0aGlzLl9uZXh0LG51bGwsITApfTtsLl9hZGRMYXp5U2V0PWZ1bmN0aW9uKHQsZSxpKXt2YXIgcj10aGlzLl9maXJzdFBUPW5ldyBfZSh0LGUsMCwwLHRoaXMuX2ZpcnN0UFQsMik7ci5lPWksci5zZXRSYXRpbz1NZSxyLmRhdGE9dGhpc30sbC5fbGlua0NTU1A9ZnVuY3Rpb24odCxlLGkscil7cmV0dXJuIHQmJihlJiYoZS5fcHJldj10KSx0Ll9uZXh0JiYodC5fbmV4dC5fcHJldj10Ll9wcmV2KSx0Ll9wcmV2P3QuX3ByZXYuX25leHQ9dC5fbmV4dDp0aGlzLl9maXJzdFBUPT09dCYmKHRoaXMuX2ZpcnN0UFQ9dC5fbmV4dCxyPSEwKSxpP2kuX25leHQ9dDpyfHxudWxsIT09dGhpcy5fZmlyc3RQVHx8KHRoaXMuX2ZpcnN0UFQ9dCksdC5fbmV4dD1lLHQuX3ByZXY9aSksdH0sbC5fa2lsbD1mdW5jdGlvbihlKXt2YXIgaSxyLHMsbj1lO2lmKGUuYXV0b0FscGhhfHxlLmFscGhhKXtuPXt9O2ZvcihyIGluIGUpbltyXT1lW3JdO24ub3BhY2l0eT0xLG4uYXV0b0FscGhhJiYobi52aXNpYmlsaXR5PTEpfXJldHVybiBlLmNsYXNzTmFtZSYmKGk9dGhpcy5fY2xhc3NOYW1lUFQpJiYocz1pLnhmaXJzdCxzJiZzLl9wcmV2P3RoaXMuX2xpbmtDU1NQKHMuX3ByZXYsaS5fbmV4dCxzLl9wcmV2Ll9wcmV2KTpzPT09dGhpcy5fZmlyc3RQVCYmKHRoaXMuX2ZpcnN0UFQ9aS5fbmV4dCksaS5fbmV4dCYmdGhpcy5fbGlua0NTU1AoaS5fbmV4dCxpLl9uZXh0Ll9uZXh0LHMuX3ByZXYpLHRoaXMuX2NsYXNzTmFtZVBUPW51bGwpLHQucHJvdG90eXBlLl9raWxsLmNhbGwodGhpcyxuKX07dmFyIExlPWZ1bmN0aW9uKHQsZSxpKXt2YXIgcixzLG4sYTtpZih0LnNsaWNlKWZvcihzPXQubGVuZ3RoOy0tcz4tMTspTGUodFtzXSxlLGkpO2Vsc2UgZm9yKHI9dC5jaGlsZE5vZGVzLHM9ci5sZW5ndGg7LS1zPi0xOyluPXJbc10sYT1uLnR5cGUsbi5zdHlsZSYmKGUucHVzaCgkKG4pKSxpJiZpLnB1c2gobikpLDEhPT1hJiY5IT09YSYmMTEhPT1hfHwhbi5jaGlsZE5vZGVzLmxlbmd0aHx8TGUobixlLGkpfTtyZXR1cm4gYS5jYXNjYWRlVG89ZnVuY3Rpb24odCxpLHIpe3ZhciBzLG4sYSxvPWUudG8odCxpLHIpLGw9W29dLGg9W10sdT1bXSxmPVtdLF89ZS5faW50ZXJuYWxzLnJlc2VydmVkUHJvcHM7Zm9yKHQ9by5fdGFyZ2V0c3x8by50YXJnZXQsTGUodCxoLGYpLG8ucmVuZGVyKGksITApLExlKHQsdSksby5yZW5kZXIoMCwhMCksby5fZW5hYmxlZCghMCkscz1mLmxlbmd0aDstLXM+LTE7KWlmKG49RyhmW3NdLGhbc10sdVtzXSksbi5maXJzdE1QVCl7bj1uLmRpZnM7XHJcbmZvcihhIGluIHIpX1thXSYmKG5bYV09clthXSk7bC5wdXNoKGUudG8oZltzXSxpLG4pKX1yZXR1cm4gbH0sdC5hY3RpdmF0ZShbYV0pLGF9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjcuM1xyXG4gKiBEQVRFOiAyMDE0LTAxLTE0XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKiovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7dmFyIHQ9ZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LGU9d2luZG93LGk9ZnVuY3Rpb24oaSxzKXt2YXIgcj1cInhcIj09PXM/XCJXaWR0aFwiOlwiSGVpZ2h0XCIsbj1cInNjcm9sbFwiK3IsYT1cImNsaWVudFwiK3Isbz1kb2N1bWVudC5ib2R5O3JldHVybiBpPT09ZXx8aT09PXR8fGk9PT1vP01hdGgubWF4KHRbbl0sb1tuXSktKGVbXCJpbm5lclwiK3JdfHxNYXRoLm1heCh0W2FdLG9bYV0pKTppW25dLWlbXCJvZmZzZXRcIityXX0scz13aW5kb3cuX2dzRGVmaW5lLnBsdWdpbih7cHJvcE5hbWU6XCJzY3JvbGxUb1wiLEFQSToyLHZlcnNpb246XCIxLjcuM1wiLGluaXQ6ZnVuY3Rpb24odCxzLHIpe3JldHVybiB0aGlzLl93ZHc9dD09PWUsdGhpcy5fdGFyZ2V0PXQsdGhpcy5fdHdlZW49cixcIm9iamVjdFwiIT10eXBlb2YgcyYmKHM9e3k6c30pLHRoaXMuX2F1dG9LaWxsPXMuYXV0b0tpbGwhPT0hMSx0aGlzLng9dGhpcy54UHJldj10aGlzLmdldFgoKSx0aGlzLnk9dGhpcy55UHJldj10aGlzLmdldFkoKSxudWxsIT1zLng/KHRoaXMuX2FkZFR3ZWVuKHRoaXMsXCJ4XCIsdGhpcy54LFwibWF4XCI9PT1zLng/aSh0LFwieFwiKTpzLngsXCJzY3JvbGxUb194XCIsITApLHRoaXMuX292ZXJ3cml0ZVByb3BzLnB1c2goXCJzY3JvbGxUb194XCIpKTp0aGlzLnNraXBYPSEwLG51bGwhPXMueT8odGhpcy5fYWRkVHdlZW4odGhpcyxcInlcIix0aGlzLnksXCJtYXhcIj09PXMueT9pKHQsXCJ5XCIpOnMueSxcInNjcm9sbFRvX3lcIiwhMCksdGhpcy5fb3ZlcndyaXRlUHJvcHMucHVzaChcInNjcm9sbFRvX3lcIikpOnRoaXMuc2tpcFk9ITAsITB9LHNldDpmdW5jdGlvbih0KXt0aGlzLl9zdXBlci5zZXRSYXRpby5jYWxsKHRoaXMsdCk7dmFyIHM9dGhpcy5fd2R3fHwhdGhpcy5za2lwWD90aGlzLmdldFgoKTp0aGlzLnhQcmV2LHI9dGhpcy5fd2R3fHwhdGhpcy5za2lwWT90aGlzLmdldFkoKTp0aGlzLnlQcmV2LG49ci10aGlzLnlQcmV2LGE9cy10aGlzLnhQcmV2O3RoaXMuX2F1dG9LaWxsJiYoIXRoaXMuc2tpcFgmJihhPjd8fC03PmEpJiZpKHRoaXMuX3RhcmdldCxcInhcIik+cyYmKHRoaXMuc2tpcFg9ITApLCF0aGlzLnNraXBZJiYobj43fHwtNz5uKSYmaSh0aGlzLl90YXJnZXQsXCJ5XCIpPnImJih0aGlzLnNraXBZPSEwKSx0aGlzLnNraXBYJiZ0aGlzLnNraXBZJiZ0aGlzLl90d2Vlbi5raWxsKCkpLHRoaXMuX3dkdz9lLnNjcm9sbFRvKHRoaXMuc2tpcFg/czp0aGlzLngsdGhpcy5za2lwWT9yOnRoaXMueSk6KHRoaXMuc2tpcFl8fCh0aGlzLl90YXJnZXQuc2Nyb2xsVG9wPXRoaXMueSksdGhpcy5za2lwWHx8KHRoaXMuX3RhcmdldC5zY3JvbGxMZWZ0PXRoaXMueCkpLHRoaXMueFByZXY9dGhpcy54LHRoaXMueVByZXY9dGhpcy55fX0pLHI9cy5wcm90b3R5cGU7cy5tYXg9aSxyLmdldFg9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fd2R3P251bGwhPWUucGFnZVhPZmZzZXQ/ZS5wYWdlWE9mZnNldDpudWxsIT10LnNjcm9sbExlZnQ/dC5zY3JvbGxMZWZ0OmRvY3VtZW50LmJvZHkuc2Nyb2xsTGVmdDp0aGlzLl90YXJnZXQuc2Nyb2xsTGVmdH0sci5nZXRZPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMuX3dkdz9udWxsIT1lLnBhZ2VZT2Zmc2V0P2UucGFnZVlPZmZzZXQ6bnVsbCE9dC5zY3JvbGxUb3A/dC5zY3JvbGxUb3A6ZG9jdW1lbnQuYm9keS5zY3JvbGxUb3A6dGhpcy5fdGFyZ2V0LnNjcm9sbFRvcH0sci5fa2lsbD1mdW5jdGlvbih0KXtyZXR1cm4gdC5zY3JvbGxUb194JiYodGhpcy5za2lwWD0hMCksdC5zY3JvbGxUb195JiYodGhpcy5za2lwWT0hMCksdGhpcy5fc3VwZXIuX2tpbGwuY2FsbCh0aGlzLHQpfX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyJdfQ==
