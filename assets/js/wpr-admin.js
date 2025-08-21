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

      // Identify user with hashed license email if available
      if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
        mixpanel.identify(rocket_mixpanel_data.user_id);
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
    const iframe = document.getElementById('rocketcdn-iframe');
    const loader = document.getElementById('wpr-rocketcdn-modal-loader');
    iframe.addEventListener('load', function () {
      loader.style.display = 'none';
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvY3VzdG9tL2N1c3RvbS1zZWxlY3QuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvcGFnZU1hbmFnZXIuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldGNkbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQSxRQUFRLENBQUMsZ0JBQWdCLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxJQUFJO0VBQ25FLE1BQU0sU0FBUyxHQUFHLFlBQVksQ0FBQyxhQUFhLENBQUMsZ0JBQWdCLENBQUM7RUFDOUQsTUFBTSxhQUFhLEdBQUcsWUFBWSxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQztFQUNuRSxNQUFNLE9BQU8sR0FBRyxTQUFBLENBQVMsR0FBRyxFQUFFO0lBQzdCLE1BQU0saUJBQWlCLEdBQUcsSUFBSSxXQUFXLENBQUMsc0JBQXNCLEVBQUU7TUFDakUsTUFBTSxFQUFFO1FBQ1AsY0FBYyxFQUFFO01BQ2pCO0lBQ0QsQ0FBQyxDQUFDO0lBQ0YsYUFBYSxDQUFDLFdB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QUFDLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxrQkFBa0IsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGtCQUFrQixLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGlCQUFpQixLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxhQUFhLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsT0FBTyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxLQUFLO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsVUFBVTtNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLE9BQU87TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLE9BQU87TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQztVQUFDLFVBQVUsRUFBQyxDQUFDO1VBQUMsZ0JBQWdCLEVBQUMsQ0FBQztVQUFDLGVBQWUsRUFBQyxDQUFDO1VBQUMsaUJBQWlCLEVBQUMsSUFBSSxDQUFDO1FBQWlCLENBQUMsQ0FBQztNQUFDLEtBQUksUUFBUSxJQUFFLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxlQUFlLEtBQUcsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLGlCQUFpQixLQUFHLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsS0FBSSxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxJQUFFLENBQUMsWUFBWSxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxDQUFDLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLElBQUUsUUFBUSxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1lBQUMsTUFBTSxFQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxLQUFHLFVBQVUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxPQUFPLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsVUFBVSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE1BQUssYUFBYSxHQUFDLENBQUMsR0FBQyx1RUFBdUU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsWUFBWSxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUM7UUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLElBQUk7TUFBQTtNQUFDLE9BQU0sUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsY0FBYyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO01BQUMsSUFBRyxDQUFDLFlBQVksQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLFlBQVcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsSUFBSSxJQUFFLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLElBQUUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUk7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxPQUFPLElBQUksSUFBRSxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxZQUFZLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxTQUFTLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxtQkFBbUIsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLElBQUUsSUFBSSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxZQUFZLElBQUUsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLG1CQUFtQixFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxZQUFZLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFlBQVksR0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsWUFBWSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxJQUFFLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBRyxNQUFJLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFHLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsVUFBVSxNQUFJLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLGtCQUFrQixJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxPQUFPLENBQUMsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsWUFBVTtNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUU7UUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLENBQUMsRUFBQyxPQUFNLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFBO01BQUMsT0FBTSxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxVQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUcsQ0FBQyxZQUFZLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUc7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsS0FBRyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUU7UUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLEVBQUMsT0FBTSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQTtNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztNQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtNQUFDLEtBQUksSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsR0FBRyxFQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEtBQUcsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQztRQUFDLElBQUcsSUFBSSxDQUFDLE1BQU0sRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsQ0FBQyxHQUFDLFlBQVksRUFBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsYUFBYSxJQUFFLENBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxFQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sSUFBSSxDQUFDLGNBQWM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxLQUFHLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxZQUFVO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLG1CQUFtQjtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVO0lBQUEsQ0FBQyxFQUFDLENBQUM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDOzs7OztBQ1h4clQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUMsVUFBUyxDQUFDLEVBQUM7RUFBQyxZQUFZOztFQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsSUFBRSxDQUFDO0VBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUM7SUFBQyxJQUFJLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLENBQUM7TUFBQyxDQUFDLEdBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsS0FBSztNQUFDLENBQUMsR0FBQyxTQUFBLENBQUEsRUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsWUFBVTtRQUFDLElBQUksQ0FBQyxHQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsUUFBUTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQztRQUFDLE9BQU8sVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEtBQUcsQ0FBQyxZQUFZLEtBQUssSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLEVBQUU7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUUsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7VUFBQyxJQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxVQUFVLElBQUUsT0FBTyxNQUFNLElBQUUsTUFBTSxDQUFDLEdBQUcsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLEdBQUcsR0FBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUMsRUFBRSxFQUFDLFlBQVU7WUFBQyxPQUFPLENBQUM7VUFBQSxDQUFDLENBQUMsR0FBQyxXQUFXLElBQUUsT0FBTyxNQUFNLElBQUUsTUFBTSxDQUFDLE9BQU8sS0FBRyxNQUFNLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBRSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztRQUFBLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFDLFlBQVU7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsMEJBQTBCLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFBLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssRUFBQyxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsT0FBTyxDQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU07UUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsUUFBUSxFQUFDLE1BQU0sRUFBQyxPQUFPLEVBQUMsT0FBTyxFQUFDLGNBQWMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsUUFBUSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsV0FBVyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxXQUFXLENBQUM7SUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLFNBQVM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsd0JBQXdCLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxJQUFFLElBQUk7SUFBQSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsZ0JBQWdCLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsS0FBSSxJQUFJLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxFQUFFLEVBQUMsQ0FBQztRQUFDLEVBQUUsRUFBQztNQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDO01BQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUM7TUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0I7TUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxZQUFVO1FBQUMsT0FBTyxJQUFJLElBQUksQ0FBRCxDQUFDLENBQUUsT0FBTyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxJQUFJLEVBQUMsS0FBSyxFQUFDLFFBQVEsRUFBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyx1QkFBdUIsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLHNCQUFzQixDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyw2QkFBNkIsQ0FBQztJQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxJQUFJO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxHQUFDLEdBQUc7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLEtBQUssRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxhQUFhLENBQUMsTUFBTSxDQUFDO1FBQUEsQ0FBQztNQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7UUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO1FBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxZQUFVO1FBQUMsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxVQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLElBQUUsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsSUFBRSxDQUFDO01BQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLFlBQVU7UUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQyxFQUFDLElBQUksQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsQ0FBQyxNQUFNLENBQUMsZUFBZSxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxNQUFNO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsZUFBZSxLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxDQUFDLE1BQU0sQ0FBRCxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVO01BQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztJQUFBLENBQUM7SUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxZQUFVLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsWUFBVTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFlBQVU7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7TUFBQyxPQUFNLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxZQUFVO01BQUMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7TUFBQyxPQUFPLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxRQUFRLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUM7TUFBQyxPQUFPLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxJQUFHLENBQUMsS0FBRyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsSUFBSSxDQUFDLE1BQU07SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxLQUFHLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsYUFBYSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsY0FBYztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sU0FBUyxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUs7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFDLE9BQU8sSUFBSSxDQUFDLFVBQVU7TUFBQyxJQUFHLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsRUFBQztVQUFDLElBQUksQ0FBQyxNQUFNLElBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDO1VBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLGNBQWM7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLE9BQUssQ0FBQyxDQUFDLFNBQVMsR0FBRSxDQUFDLENBQUMsU0FBUyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUztRQUFBO1FBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsTUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sSUFBSTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxTQUFTLENBQUMsTUFBTSxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxVQUFVLEtBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLGFBQWEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsVUFBVTtJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsVUFBVTtNQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLGlCQUFpQixFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVU7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLFNBQVMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLEtBQUcsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxpQkFBaUIsR0FBQyxJQUFJLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksSUFBRSxJQUFJLENBQUMsU0FBUztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLFNBQVMsQ0FBQyxNQUFNLEVBQUMsT0FBTyxJQUFJLENBQUMsT0FBTztNQUFDLElBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxPQUFPLElBQUUsSUFBSSxDQUFDLFNBQVMsRUFBQztRQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxpQkFBaUIsS0FBRyxJQUFJLENBQUMsVUFBVSxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLHFCQUFxQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxrQkFBa0IsR0FBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsTUFBTSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxhQUFhLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxRQUFRLEtBQUcsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxNQUFNLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNO01BQUMsS0FBSSxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsQ0FBQyxPQUFPLE1BQUksQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsYUFBYSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLElBQUUsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxZQUFVO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVU7SUFBQSxDQUFDO0lBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBQyxJQUFJLElBQUUsQ0FBQyxFQUFDLE1BQUssNkJBQTZCO1FBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLElBQUcsSUFBSSxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsWUFBWSxLQUFLLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUksSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFdBQVcsR0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUksQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQztRQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxlQUFlLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsS0FBRyxDQUFDLENBQUMsTUFBSSxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsSUFBRSxPQUFPLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsdUJBQXVCLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxFQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGdCQUFnQixHQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxJQUFFLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxjQUFjLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsVUFBVSxFQUFDO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDO1FBQUMsU0FBUyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLGdCQUFnQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQyxDQUFDO1FBQUMsWUFBWSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsY0FBYyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDO1FBQUMsYUFBYSxFQUFDLENBQUM7UUFBQyxZQUFZLEVBQUMsQ0FBQztRQUFDLGlCQUFpQixFQUFDLENBQUM7UUFBQyx1QkFBdUIsRUFBQyxDQUFDO1FBQUMsc0JBQXNCLEVBQUMsQ0FBQztRQUFDLFFBQVEsRUFBQyxDQUFDO1FBQUMsY0FBYyxFQUFDLENBQUM7UUFBQyxhQUFhLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDO1FBQUMsUUFBUSxFQUFDLENBQUM7UUFBQyxPQUFPLEVBQUMsQ0FBQztRQUFDLElBQUksRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQztRQUFDLEdBQUcsRUFBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLFVBQVUsRUFBQyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQztRQUFDLE9BQU8sRUFBQztNQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLG1CQUFtQixHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBQSxFQUFVO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxHQUFHLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUM7VUFBQyxPQUFLLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUM7UUFBQTtNQUFDO0lBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQztJQUFDLElBQUksQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVU7UUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUM7VUFBQyxNQUFNLEVBQUMsQ0FBQztVQUFDLE1BQU0sRUFBQztRQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBSyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU07TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFHLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxDQUFDLEVBQUM7VUFBTSxPQUFPLENBQUM7UUFBQTtRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFJLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLEtBQUssSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUU7VUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsT0FBTSxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO01BQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsWUFBVTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQjtRQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWU7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7TUFBQyxJQUFHLENBQUMsQ0FBQyxPQUFPLEVBQUM7UUFBQyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsTUFBTSxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQztNQUFNLENBQUMsTUFBSyxJQUFHLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxLQUFHLENBQUMsRUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxhQUFhLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssRUFBQztRQUFNLENBQUMsTUFBSyxJQUFJLENBQUMsUUFBUSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxZQUFZLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxZQUFZLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLElBQUUsT0FBTyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsV0FBVyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLE1BQU0sRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsUUFBUSxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLFdBQVcsRUFBQyxJQUFJLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxjQUFjLENBQUMsaUJBQWlCLEVBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsVUFBVSxJQUFFLE9BQU8sSUFBSSxDQUFDLE1BQU0sSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsWUFBWSxFQUFDLEtBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU0sQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLEtBQUcsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUksSUFBSSxDQUFDLElBQUksRUFBQztRQUFDLElBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLFlBQVksS0FBSyxJQUFFLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUQsQ0FBQyxFQUFFLFlBQVksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsRUFBQztVQUFDLEtBQUksSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUM7WUFBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLFFBQVE7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxVQUFVO1lBQUMsQ0FBQyxFQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsQ0FBQztZQUFDLEVBQUUsRUFBQyxDQUFDLENBQUM7WUFBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDO1VBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO1VBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxlQUFlLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxTQUFTLE1BQUksSUFBSSxDQUFDLHVCQUF1QixHQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxNQUFLLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQztVQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsRUFBQyxDQUFDO1VBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQztRQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUUsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO1FBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxLQUFHLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxTQUFTLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFlBQVk7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsWUFBWSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxJQUFFLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxtQkFBbUIsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxJQUFJLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxVQUFVLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxtQkFBbUIsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxZQUFZLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsU0FBUztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsVUFBVTtRQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLE1BQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsTUFBSyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsRUFBQztRQUFDLElBQUcsQ0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDO1VBQUMsSUFBRyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLEdBQUcsRUFBQztVQUFPLElBQUcsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksS0FBRyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxFQUFDLE9BQU8sSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQztVQUFDLElBQUksQ0FBQyxLQUFLLElBQUUsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsS0FBSSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxJQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxhQUFhLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFVBQVUsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsSUFBRSxJQUFJLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxRQUFRLElBQUUsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFFLElBQUksQ0FBQyxVQUFVLElBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxrQkFBa0IsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLElBQUUsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsSUFBSSxDQUFDLFlBQVksS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUcsS0FBSyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsS0FBRyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxJQUFFLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUk7UUFBQyxJQUFHLElBQUksQ0FBQyxRQUFRLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxJQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDO1lBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLGlCQUFpQixHQUFDLElBQUksQ0FBQyxpQkFBaUIsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLEtBQUs7WUFBQztVQUFLO1FBQUMsQ0FBQyxNQUFJO1VBQUMsSUFBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sRUFBQyxPQUFNLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsV0FBVyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxpQkFBaUIsSUFBRSxDQUFDLENBQUMsR0FBQyxLQUFLO1FBQUE7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsU0FBUyxDQUFDO1VBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBSSxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUUsSUFBSSxDQUFDLFFBQVEsSUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUE7TUFBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFlBQVU7TUFBQyxPQUFPLElBQUksQ0FBQyx1QkFBdUIsSUFBRSxDQUFDLENBQUMsY0FBYyxDQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsRUFBQyxJQUFJLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsaUJBQWlCLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxJQUFJLENBQUMsdUJBQXVCLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsV0FBVyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLElBQUk7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsSUFBSSxDQUFDLEdBQUcsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUTtRQUFDLElBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQTtNQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLHVCQUF1QixJQUFFLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxDQUFDLEdBQUMsV0FBVyxHQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGVBQWUsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLGVBQWUsRUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSyxFQUFDLENBQUM7UUFBQyxVQUFVLEVBQUMsQ0FBQztRQUFDLGdCQUFnQixFQUFDLENBQUM7UUFBQyxlQUFlLEVBQUMsQ0FBQztRQUFDLGlCQUFpQixFQUFDLENBQUM7UUFBQyx1QkFBdUIsRUFBQyxDQUFDO1FBQUMsc0JBQXNCLEVBQUMsQ0FBQztRQUFDLGVBQWUsRUFBQyxDQUFDLENBQUM7UUFBQyxTQUFTLEVBQUMsQ0FBQztRQUFDLFNBQVMsRUFBQztNQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLElBQUksSUFBRSxDQUFDLEVBQUMsT0FBTSxFQUFFO01BQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsTUFBSyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLGtCQUFrQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMscUJBQXFCLEVBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLGVBQWUsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLElBQUksQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsU0FBUztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQztNQUFDLE9BQU8sSUFBSSxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQztRQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsUUFBUTtRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLENBQUMsRUFBQyxDQUFDO1FBQUMsQ0FBQyxFQUFDLFVBQVUsSUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsS0FBSyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxlQUFlO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRO01BQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBQyxJQUFJLENBQUMsZUFBZSxHQUFDLEVBQUUsQ0FBQyxLQUFLLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQyxPQUFLLENBQUMsR0FBRSxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLEtBQUksSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQyxNQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUTtNQUFDLElBQUcsaUJBQWlCLEtBQUcsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQztNQUFBO01BQUMsT0FBSyxDQUFDLEdBQUUsQ0FBQyxDQUFDLEVBQUUsSUFBRSxVQUFVLElBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsS0FBSSxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUQsQ0FBQyxDQUFFLFNBQVMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLE9BQU0sQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFHLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsR0FBRyxDQUFDLEVBQUMsTUFBSyw0QkFBNEI7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxjQUFjO1FBQUMsQ0FBQyxHQUFDO1VBQUMsSUFBSSxFQUFDLGNBQWM7VUFBQyxHQUFHLEVBQUMsVUFBVTtVQUFDLElBQUksRUFBQyxPQUFPO1VBQUMsS0FBSyxFQUFDLGFBQWE7VUFBQyxPQUFPLEVBQUM7UUFBaUIsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVEsRUFBQyxZQUFVO1VBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLEdBQUMsQ0FBQyxJQUFFLEVBQUU7UUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUc7TUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsVUFBVSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxFQUFDO01BQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMscURBQXFELEdBQUMsQ0FBQyxDQUFDO0lBQUE7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0VBQUE7QUFBQyxDQUFDLEVBQUUsTUFBTSxDQUFDOzs7OztBQ1gxNHZCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEtBQUcsTUFBTSxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsWUFBVTtFQUFDLFlBQVk7O0VBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxhQUFhLEVBQUMsQ0FBQyxhQUFhLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQztJQUFDLElBQUksQ0FBQztNQUFDLENBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsSUFBRSxNQUFNO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsU0FBUztNQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsWUFBVSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsWUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDO1VBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxDQUFELENBQUM7VUFBQyxNQUFNLEVBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztVQUFDLFNBQVMsRUFBQyxJQUFJLENBQUMsQ0FBRDtRQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDO1lBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFHO1VBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztRQUFDLE9BQU0sQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZUFBZSxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEVBQUUsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztNQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUM7SUFBQyxPQUFPLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxFQUFFO01BQUMsT0FBTyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLElBQUUsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0IsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxVQUFVLENBQUMsRUFBQyxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGtCQUFrQixFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQyxLQUFJLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsTUFBTSxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsWUFBWSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDO1FBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxDQUFDLEVBQUM7TUFBQyxDQUFDO01BQUMsS0FBSSxDQUFDLENBQUMsSUFBSSxDQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFELENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7TUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSTtNQUFBLENBQUMsTUFBSyxPQUFLLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJO01BQUMsT0FBTyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJLENBQUMsQ0FBRCxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU87SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLEdBQUMsSUFBSSxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxNQUFNLElBQUUsQ0FBQyxJQUFFLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLE1BQU0sSUFBRSxDQUFDLElBQUUsSUFBSSxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLElBQUUsTUFBTSxJQUFFLENBQUMsSUFBRSxLQUFLLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxhQUFhLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEdBQUMsTUFBTSxJQUFFLENBQUMsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsSUFBSSxHQUFDLENBQUMsR0FBQyxNQUFNLElBQUUsQ0FBQyxJQUFFLElBQUksR0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLEdBQUMsS0FBSyxHQUFDLE1BQU0sSUFBRSxDQUFDLElBQUUsS0FBSyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsR0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxFQUFFO0lBQUEsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxFQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxJQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDO1FBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLENBQUQsQ0FBQztNQUFDLE9BQU8sQ0FBQyxDQUFDLFdBQVcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxFQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsT0FBTSxFQUFFLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsRUFBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsRUFBRSxHQUFDLElBQUksQ0FBQyxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsVUFBUyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdBQVcsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFBQSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFTLENBQUMsRUFBQztNQUFDLE9BQU0sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO0lBQUEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFdB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QyxHQUFDLFlBQVk7TUFBQyxDQUFDLEdBQUMsd0NBQXdDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLHVCQUF1QjtNQUFDLENBQUMsR0FBQyxnQ0FBZ0M7TUFBQyxDQUFDLEdBQUMscURBQXFEO01BQUMsQ0FBQyxHQUFDLHVCQUF1QjtNQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBRSxHQUFDLEdBQUc7TUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLElBQUksQ0FBQyxFQUFFO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxRQUFRO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsS0FBSyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsS0FBSyxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUM7UUFBQyxhQUFhLEVBQUM7TUFBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQVMsQ0FBQyxTQUFTO01BQUMsQ0FBQyxHQUFDLFlBQVU7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsQ0FBQyxLQUFLLENBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQyxFQUFDLDZCQUE2QixDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsdUNBQXVDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxvQkFBb0IsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQztNQUFBLENBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxNQUFNLEtBQUcsRUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQztRQUFDLE1BQU0sQ0FBQyxPQUFPLElBQUUsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLEVBQUU7TUFBQyxDQUFDLEdBQUMsRUFBRTtNQUFDLENBQUMsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxJQUFHLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFHLEVBQUMsS0FBSyxFQUFDLElBQUksRUFBQyxJQUFJLEVBQUMsUUFBUSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFO1FBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxJQUFJO01BQUEsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsZ0JBQWdCLEdBQUMsWUFBVSxDQUFDLENBQUM7TUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxTQUFTLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsV0FBVyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUcsTUFBTSxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxLQUFJO1VBQUMsSUFBRyxDQUFDLENBQUMsT0FBTyxHQUFDLDhCQUE4QixHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLEdBQUMsaUJBQWlCLEVBQUMsR0FBRyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsaUJBQWlCLEdBQUMsZ0JBQWdCLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUk7WUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxJQUFFLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLEVBQUMsT0FBTyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxHQUFHO1lBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQTtVQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGFBQWEsR0FBQyxjQUFjLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLFdBQVcsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUcsVUFBVSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsVUFBVSxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQztRQUFDLElBQUksQ0FBQyxHQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsTUFBTSxHQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUM7VUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLE9BQUssRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGdCQUFnQixDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBSyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLENBQUMsQ0FBQyxLQUFLLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sSUFBRSxPQUFPLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsU0FBUyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLEtBQUssQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLEtBQUssS0FBRyxDQUFDLEdBQUMsRUFBRSxLQUFHLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsUUFBUSxJQUFFLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxDQUFDLEVBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLFdBQVcsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU07VUFBQyxJQUFJLEVBQUMsQ0FBQztVQUFDLFFBQVEsRUFBQztRQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsQ0FBQyxHQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEtBQUssRUFBQyxRQUFRO01BQUMsQ0FBQztNQUFDLENBQUMsR0FBQyxDQUFDLFlBQVksRUFBQyxhQUFhLEVBQUMsV0FBVyxFQUFDLGNBQWMsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsT0FBTyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsV0FBVyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE9BQU8sRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxXQUFXLEtBQUcsQ0FBQyxNQUFJLENBQUMsR0FBQyxLQUFLLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLE9BQU8sQ0FBQyxHQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEdBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxRQUFRLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLFFBQVEsS0FBRyxDQUFDLElBQUUsS0FBSyxDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFNLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsSUFBRSxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLElBQUksQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJO1FBQUMsT0FBTyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxVQUFVLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLFVBQVUsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1FBQUMsTUFBTSxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLENBQUM7UUFBQyxLQUFLLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsR0FBRyxDQUFDO1FBQUMsS0FBSyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQztRQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLENBQUM7UUFBQyxNQUFNLEVBQUMsQ0FBQyxHQUFHLEVBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQztRQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDO1FBQUMsR0FBRyxFQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLEVBQUMsQ0FBQyxHQUFHLEVBQUMsR0FBRyxFQUFDLEdBQUcsQ0FBQztRQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDO1FBQUMsV0FBVyxFQUFDLENBQUMsR0FBRyxFQUFDLEdBQUcsRUFBQyxHQUFHLEVBQUMsQ0FBQztNQUFDLENBQUM7TUFBQyxFQUFFLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsT0FBTyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxHQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsR0FBRyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFDLEdBQUcsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxLQUFLO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxxREFBcUQ7SUFBQyxLQUFJLENBQUMsSUFBSSxFQUFFLEVBQUMsRUFBRSxJQUFFLEdBQUcsR0FBQyxDQUFDLEdBQUMsS0FBSztJQUFDLEVBQUUsR0FBQyxNQUFNLENBQUMsRUFBRSxHQUFDLEdBQUcsRUFBQyxJQUFJLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxFQUFDLE9BQU8sVUFBUyxDQUFDLEVBQUM7VUFBQyxPQUFPLENBQUM7UUFBQSxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLENBQUMsR0FBQyxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsR0FBQyxHQUFHO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsRUFBRTtRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsVUFBUyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztVQUFBO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxFQUFDLE9BQUssQ0FBQyxHQUFDLEVBQUUsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDO1FBQUEsQ0FBQyxHQUFDLFVBQVMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUM7VUFBQyxJQUFHLFFBQVEsSUFBRSxPQUFPLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQztZQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQztVQUFBO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxFQUFDLE9BQUssQ0FBQyxHQUFDLEVBQUUsQ0FBQyxHQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFBLENBQUMsR0FBQyxVQUFTLENBQUMsRUFBQztVQUFDLE9BQU8sQ0FBQztRQUFBLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQztVQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7UUFBQSxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsSUFBRSxDQUFDLENBQUMsZUFBZSxHQUFDLFVBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1FBQUMsS0FBSSxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsSUFBRyxDQUFDLENBQUMsVUFBVSxLQUFHLENBQUMsQ0FBQyxVQUFVLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxFQUFDO1lBQUMsSUFBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksRUFBQztjQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7WUFBQTtVQUFDLENBQUMsTUFBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUc7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQTtNQUFDLENBQUMsRUFBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLEVBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDLENBQUM7TUFBQyxFQUFFLElBQUUsQ0FBQyxDQUFDLGFBQWEsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVU7VUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEVBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBO1FBQUMsT0FBTTtVQUFDLEtBQUssRUFBQyxDQUFDO1VBQUMsR0FBRyxFQUFDLENBQUM7VUFBQyxRQUFRLEVBQUMsQ0FBQztVQUFDLEVBQUUsRUFBQztRQUFDLENBQUM7TUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxZQUFZLEVBQUUsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxLQUFLLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDO01BQUEsQ0FBQyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLEVBQUU7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQztRQUFDLEtBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxFQUFFLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUUsRUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxJQUFHLENBQUMsS0FBRyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEdBQUMsY0FBYyxHQUFDLGFBQWEsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxhQUFhLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxFQUFDLE9BQU8sQ0FBQztVQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsRUFBQyxDQUFDLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUFBLENBQUMsTUFBSyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxFQUFDO1VBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQztVQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDO1FBQUE7UUFBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxJQUFFLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUM7SUFBQyxLQUFJLENBQUMsR0FBQyxFQUFFLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUMsRUFBRSxFQUFFLEdBQUMsQ0FBQyxHQUFFLENBQUMsQ0FBQyxJQUFJLEdBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsRUFBRSxDQUFDLEdBQUMsRUFBRTtJQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUk7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQyxPQUFPLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxDQUFDLE1BQU0sR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJLEdBQUM7UUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDO01BQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxJQUFFLEVBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxTQUFTLElBQUUsRUFBRSxDQUFDLENBQUMsQ0FBQyxZQUFZLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsV0FBVyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsSUFBSSxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxJQUFJLENBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQztNQUFBLENBQUM7TUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLDJCQUEyQixHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDO1VBQUMsTUFBTSxFQUFDO1FBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWTtRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLFFBQVE7VUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDO1lBQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7Y0FBQyxJQUFJLENBQUMsR0FBQyxDQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsSUFBRSxNQUFNLEVBQUUsR0FBRyxDQUFDLFNBQVMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO2NBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxDQUFDLFlBQVksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxzQkFBc0IsQ0FBQyxFQUFDLENBQUMsQ0FBQztZQUFBO1VBQUMsQ0FBQyxDQUFDO1FBQUE7TUFBQyxDQUFDO0lBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLFlBQVksR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLE9BQU87TUFBQyxJQUFHLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLEtBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztNQUFBO01BQUMsT0FBTyxFQUFFLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxtQkFBbUIsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsRUFBRSxDQUFDLENBQUMsRUFBQztRQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO1VBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztRQUFBLENBQUM7UUFBQyxRQUFRLEVBQUM7TUFBQyxDQUFDLENBQUM7SUFBQSxDQUFDO0lBQUMsSUFBSSxFQUFFLEdBQUMsaUZBQWlGLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsV0FBVyxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxXQUFXO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxpQkFBaUIsQ0FBQztNQUFDLEVBQUUsR0FBQyxJQUFJLEtBQUcsQ0FBQyxDQUFDLGFBQWEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFlBQVU7UUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxZQUFZLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxZQUFZLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLFlBQVk7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLElBQUUsSUFBSSxFQUFFLENBQUQsQ0FBQyxHQUFDLElBQUksRUFBRSxDQUFELENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLEVBQUUsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxHQUFDLENBQUM7UUFBQyxLQUFJLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxFQUFFLEVBQUUsS0FBSyxDQUFDLHlCQUF5QixDQUFDLElBQUUsRUFBRSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLENBQUMsR0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQztRQUFDLElBQUcsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUM7VUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7WUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztVQUFDLElBQUcsQ0FBQyxDQUFDLE9BQU8sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxTQUFTLEVBQUM7WUFBQyxJQUFJLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Y0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7Y0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO2NBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQztZQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQTtRQUFDLENBQUMsTUFBSyxJQUFHLEVBQUUsRUFBRSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxDQUFDLElBQUUsS0FBSyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLElBQUUsQ0FBQztZQUFDLEVBQUUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUM7WUFBQyxFQUFFLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxJQUFFLEVBQUUsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxFQUFFLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxFQUFFLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsSUFBRSxHQUFHLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsR0FBRyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxJQUFFLEdBQUcsRUFBQyxDQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLElBQUUsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQywyQkFBMkIsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDO1FBQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxJQUFFLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsSUFBRSxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLElBQUUsQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZO1FBQUMsSUFBRyxDQUFDLEVBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxFQUFFO1VBQUMsSUFBSSxDQUFDO1lBQUMsQ0FBQztZQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFdBQVc7WUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZO1lBQUMsQ0FBQyxHQUFDLFVBQVUsS0FBRyxDQUFDLENBQUMsUUFBUTtZQUFDLENBQUMsR0FBQywrQ0FBK0MsR0FBQyxDQUFDLEdBQUMsUUFBUSxHQUFDLENBQUMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO1VBQUMsSUFBRyxJQUFJLElBQUUsQ0FBQyxDQUFDLEVBQUUsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsRUFBRSxJQUFFLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsT0FBTyxJQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxPQUFPLElBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLElBQUUsK0JBQStCLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLG9DQUFvQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxZQUFZLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFFLEdBQUcsS0FBRyxVQUFVLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsV0FBVyxJQUFFLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUM7WUFBQyxJQUFJLENBQUM7Y0FBQyxDQUFDO2NBQUMsQ0FBQztjQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7WUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLEVBQUMsRUFBRSxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLEVBQUUsSUFBRSxDQUFDLEtBQUcsRUFBRSxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLElBQUk7VUFBQTtRQUFDO01BQUMsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU07VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFdBQVc7UUFBQyxJQUFHLEVBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsT0FBTyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUM7UUFBQyxJQUFHLENBQUMsRUFBQztVQUFDLElBQUksQ0FBQyxHQUFDLElBQUk7VUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQztRQUFBO1FBQUMsSUFBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxRQUFRLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSTtVQUFDLElBQUcsRUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxPQUFPLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxjQUFjLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxLQUFLLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsRUFBRSxDQUFDLEVBQUMsS0FBSyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDO1FBQUE7UUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLEVBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUUsR0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBRSxHQUFDLEVBQUUsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFFLEdBQUMsRUFBRSxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxHQUFDLFdBQVcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsR0FBQyxHQUFHO01BQUEsQ0FBQztNQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsbUJBQW1CLEdBQUMsVUFBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1FBQUMsT0FBTyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsUUFBUSxHQUFDLEVBQUUsRUFBQyxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxLQUFLLENBQUMsS0FBRyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxLQUFLLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsR0FBQyxTQUFTLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsS0FBSyxDQUFDLENBQUM7TUFBQSxDQUFDO0lBQUMsRUFBRSxDQUFDLG1QQUFtUCxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFHLENBQUMsQ0FBQyxVQUFVLEVBQUMsT0FBTyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJO1VBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxNQUFNO1VBQUMsQ0FBQyxHQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBRyxRQUFRLElBQUUsT0FBTyxDQUFDLENBQUMsU0FBUyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLE9BQU8sRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVUsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDO1VBQUMsSUFBRyxDQUFDLEdBQUM7WUFBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLElBQUksSUFBRSxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQztZQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQUMsV0FBVyxFQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsb0JBQW9CLEVBQUMsQ0FBQyxDQUFDLFdBQVc7VUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxtQkFBbUIsRUFBQyxJQUFJLElBQUUsQ0FBQyxFQUFDLElBQUcsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLEtBQUksQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDO1VBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLENBQUMsVUFBVSxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxHQUFDLGVBQWUsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGFBQWEsR0FBQyxRQUFRLEdBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxVQUFVLEVBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxLQUFHLENBQUMsQ0FBQyxTQUFTLEdBQUMsRUFBRSxDQUFDLFdBQVcsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxnQkFBZ0IsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLGNBQWMsR0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxXQUFXLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxFQUFFLENBQUMsV0FBVyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLGdCQUFnQixJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsY0FBYyxHQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxFQUFDLFdBQVcsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLE1BQUksQ0FBQyxDQUFDLEtBQUssSUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUM7UUFBQTtRQUFDLEtBQUksRUFBRSxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsT0FBTyxLQUFHLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsSUFBRSxDQUFDLENBQUMsZUFBZSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsT0FBTyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxJQUFFLENBQUMsQ0FBQyxXQUFXLEVBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLElBQUksSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsQ0FBQyxlQUFlLEVBQUMsQ0FBQyxDQUFDLElBQUUsRUFBRSxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxNQUFJLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLFNBQVMsQ0FBQyxJQUFFLEVBQUUsRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxpQkFBaUIsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLEVBQUUsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxLQUFLLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsS0FBRyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEtBQUssQ0FBQyxHQUFDLE1BQU0sRUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxPQUFPLElBQUUsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxFQUFFLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsY0FBYyxHQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxXQUFXLEVBQUM7TUFBQyxZQUFZLEVBQUMsc0JBQXNCO01BQUMsTUFBTSxFQUFDLENBQUMsQ0FBQztNQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsT0FBTyxFQUFDO0lBQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGNBQWMsRUFBQztNQUFDLFlBQVksRUFBQyxLQUFLO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMscUJBQXFCLEVBQUMsc0JBQXNCLEVBQUMseUJBQXlCLEVBQUMsd0JBQXdCLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxLQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLFdBQVcsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFFLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLEVBQUUsTUFBTSxDQUFDLEVBQUMsQ0FBQyxHQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxVQUFVLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sSUFBRSxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQyxFQUFDLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsV0FBVyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxHQUFHLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsR0FBRyxJQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLElBQUUsSUFBSSxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxZQUFZLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEtBQUssRUFBQyxDQUFDLENBQUM7UUFBQyxPQUFPLENBQUM7TUFBQSxDQUFDO01BQUMsTUFBTSxFQUFDLENBQUMsQ0FBQztNQUFDLFNBQVMsRUFBQyxFQUFFLENBQUMsaUJBQWlCLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG9CQUFvQixFQUFDO01BQUMsWUFBWSxFQUFDLEtBQUs7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMscUJBQXFCO1VBQUMsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLG1CQUFtQixHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLG1CQUFtQixLQUFHLEtBQUssQ0FBQztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsaUJBQWlCLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsSUFBRSxNQUFNLEtBQUcsQ0FBQyxDQUFDLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDLE1BQUksQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxJQUFJLEdBQUMsR0FBRyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLENBQUM7VUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUM7UUFBQTtRQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxTQUFTLEVBQUM7SUFBRSxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsZ0JBQWdCLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLFNBQVMsRUFBQztJQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxhQUFhLEVBQUM7TUFBQyxZQUFZLEVBQUMsS0FBSztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG1CQUFtQixFQUFDO01BQUMsWUFBWSxFQUFDLFNBQVM7TUFBQyxNQUFNLEVBQUMsQ0FBQztJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsRUFBQztNQUFDLE1BQU0sRUFBQyxDQUFDO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLG9CQUFvQixFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsWUFBWSxFQUFDO01BQUMsTUFBTSxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsUUFBUSxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQywrQ0FBK0M7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsU0FBUyxFQUFDO01BQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxtREFBbUQ7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsTUFBTSxFQUFDO01BQUMsWUFBWSxFQUFDLHVCQUF1QjtNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztRQUFDLE9BQU8sQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFlBQVksRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsR0FBRyxFQUFDLENBQUMsR0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxHQUFHLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLFlBQVksRUFBQztNQUFDLFlBQVksRUFBQyxrQkFBa0I7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsS0FBSyxFQUFDLENBQUM7SUFBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsdUJBQXVCLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUM7TUFBQyxZQUFZLEVBQUMsZ0JBQWdCO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztRQUFDLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxnQkFBZ0IsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLE9BQU8sQ0FBQyxHQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLGdCQUFnQixFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7TUFBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDO01BQUMsU0FBUyxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQztRQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLEdBQUcsSUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUUsT0FBTyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsSUFBRSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQztNQUFBO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLGFBQWEsRUFBQztNQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsbUVBQW1FO0lBQUMsQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLDJCQUEyQixFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsVUFBVSxJQUFHLENBQUMsR0FBQyxVQUFVLEdBQUMsWUFBWTtRQUFDLE9BQU8sSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxRQUFRLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxDQUFDO01BQUMsR0FBRyxLQUFHLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsSUFBRSxDQUFDLENBQUMsZUFBZSxDQUFDLFFBQVEsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLFFBQVEsQ0FBQyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEdBQUcsS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsZ0JBQWdCLEdBQUMsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUMsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxHQUFDLGlCQUFpQixHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxFQUFFLENBQUMseUJBQXlCLEVBQUM7TUFBQyxZQUFZLEVBQUMsR0FBRztNQUFDLE1BQU0sRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxTQUFTLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLEdBQUcsQ0FBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLFdBQVcsS0FBRyxDQUFDO1FBQUMsT0FBTSxRQUFRLElBQUUsT0FBTyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsUUFBUSxLQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBRSxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFNBQVMsRUFBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLEdBQUcsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsZ0JBQWdCLElBQUUsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsWUFBWSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxTQUFTLEdBQUMsUUFBUSxFQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsUUFBUSxHQUFDLFNBQVMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsU0FBUyxFQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7TUFBQTtJQUFDLENBQUMsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxjQUFjLElBQUUsSUFBSSxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsS0FBSyxDQUFDLENBQUMsV0FBVyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLENBQUM7TUFBQSxDQUFDO01BQUMsRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUM7UUFBQyxJQUFHLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLElBQUksRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEVBQUM7VUFBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7VUFBQyxLQUFJLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBRSxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksS0FBRyxJQUFJLENBQUMsQ0FBQyxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUM7UUFBQSxDQUFDLE1BQUssSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLEtBQUcsSUFBSSxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztNQUFBLENBQUM7SUFBQyxFQUFFLENBQUMsV0FBVyxFQUFDO01BQUMsTUFBTSxFQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLElBQUUsRUFBRTtVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU87UUFBQyxJQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsWUFBWSxHQUFDLElBQUksRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBQyxDQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxVQUFVLEVBQUM7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEdBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFBQTtRQUFDLE9BQU8sQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsU0FBUyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsS0FBSyxDQUFDLEVBQUMsRUFBRSxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxTQUFTLEtBQUcsQ0FBQyxDQUFDLFlBQVksQ0FBQyxPQUFPLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUksRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO01BQUMsSUFBRyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLFVBQVUsS0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLGNBQWMsSUFBRSxhQUFhLEtBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUM7UUFBQyxJQUFJLENBQUM7VUFBQyxDQUFDO1VBQUMsQ0FBQztVQUFDLENBQUM7VUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLO1VBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSztRQUFDLElBQUcsS0FBSyxLQUFHLElBQUksQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE9BQU8sR0FBQyxFQUFFLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxpQkFBaUIsS0FBRyxDQUFDLEdBQUMsRUFBRSxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsS0FBRyxFQUFFLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsWUFBWSxJQUFFLE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUM7TUFBQTtJQUFDLENBQUM7SUFBQyxLQUFJLEVBQUUsQ0FBQyxZQUFZLEVBQUM7TUFBQyxNQUFNLEVBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsRUFBRSxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsMENBQTBDLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxFQUFDLEVBQUUsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsRUFBRSxHQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLElBQUksRUFBQyxDQUFDLENBQUMsWUFBWSxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBQyxPQUFNLENBQUMsQ0FBQztNQUFDLElBQUksQ0FBQyxPQUFPLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxNQUFNLEdBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVMsSUFBRSxDQUFDLENBQUMsU0FBUyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLEVBQUUsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsZUFBZTtNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsSUFBRyxDQUFDLElBQUUsRUFBRSxLQUFHLENBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsTUFBTSxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxLQUFHLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLFFBQVEsRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFFBQVEsSUFBRSxPQUFPLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsR0FBQyxHQUFHLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsT0FBTyxHQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxPQUFPLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxFQUFDLElBQUksQ0FBQyxjQUFjLEVBQUM7UUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLGNBQWMsRUFBQyxFQUFFLEdBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDLEVBQUMsUUFBUSxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxFQUFDLDBCQUEwQixFQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsd0JBQXdCLEtBQUcsQ0FBQyxHQUFDLFNBQVMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7UUFBQyxDQUFDLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLFdBQVcsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksRUFBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxDQUFDLElBQUUsRUFBRSxHQUFDLEVBQUUsR0FBQyxFQUFFLEdBQUMsRUFBRSxHQUFDLEVBQUUsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLElBQUksQ0FBQyxVQUFVLElBQUUsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUM7TUFBQTtNQUFDLElBQUcsQ0FBQyxFQUFDO1FBQUMsT0FBSyxDQUFDLEdBQUU7VUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxFQUFFLEdBQUMsQ0FBQyxDQUFDLEVBQUUsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEtBQUs7VUFBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQztRQUFBO1FBQUMsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDO01BQUE7TUFBQyxPQUFNLENBQUMsQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsS0FBSSxDQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBQyxDQUFDLEdBQUMsUUFBUSxJQUFFLE9BQU8sQ0FBQyxFQUFDLE9BQU8sS0FBRyxDQUFDLElBQUUsTUFBTSxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sR0FBQyxDQUFDLEdBQUMsT0FBTyxHQUFDLE1BQU0sSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxHQUFDLEdBQUcsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLGFBQWEsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxHQUFDLEVBQUUsRUFBRSxNQUFNLENBQUMsR0FBQyxFQUFFLEVBQUMsQ0FBQyxFQUFFLEtBQUcsQ0FBQyxJQUFFLE1BQU0sS0FBRyxDQUFDLE1BQUksT0FBTyxLQUFHLENBQUMsSUFBRSxRQUFRLEtBQUcsQ0FBQyxJQUFFLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsSUFBSSxJQUFFLE1BQU0sS0FBRyxDQUFDLElBQUUsS0FBSyxLQUFHLENBQUMsSUFBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLElBQUksS0FBRyxDQUFDLEdBQUMsU0FBUyxLQUFHLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsR0FBRyxLQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBQyxHQUFHLEVBQUMsRUFBRSxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxJQUFFLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsRUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUMsRUFBRSxFQUFFLE1BQU0sQ0FBQyxJQUFFLEVBQUUsR0FBQyxFQUFFLENBQUMsRUFBQyxFQUFFLEtBQUcsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLElBQUksQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLEVBQUUsS0FBRyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsRUFBQyxHQUFHLEtBQUcsQ0FBQyxJQUFFLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxHQUFHLEVBQUMsR0FBRyxDQUFDLEdBQUMsR0FBRyxFQUFDLENBQUMsQ0FBQyxXQUFXLEtBQUcsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxHQUFDLENBQUMsR0FBQyxHQUFHLENBQUMsSUFBRSxJQUFJLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEdBQUMsSUFBSSxLQUFHLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBRSxDQUFDLEtBQUcsQ0FBQyxHQUFDLEtBQUssQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsS0FBSyxJQUFFLENBQUMsR0FBQyxFQUFFLElBQUUsSUFBSSxJQUFFLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsSUFBRSxDQUFDLElBQUUsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxNQUFNLEtBQUcsQ0FBQyxJQUFFLFNBQVMsS0FBRyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxHQUFDLGdCQUFnQixHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsR0FBQyxJQUFJLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLENBQUMsS0FBRyxJQUFJLEtBQUcsQ0FBQyxJQUFFLFFBQVEsS0FBRyxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLE1BQU0sS0FBRyxDQUFDLENBQUMsTUFBTSxHQUFDLENBQUMsQ0FBQztNQUFDLE9BQU8sQ0FBQztJQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsUUFBUSxHQUFDLFVBQVMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLFFBQVE7UUFBQyxDQUFDLEdBQUMsSUFBSTtNQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsSUFBRSxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssS0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLFNBQVMsSUFBRSxDQUFDLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLO1FBQUMsSUFBRyxDQUFDLElBQUUsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEtBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxTQUFTLElBQUUsQ0FBQyxLQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsWUFBWSxLQUFHLENBQUMsSUFBSSxFQUFDLE9BQUssQ0FBQyxHQUFFO1VBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSTtZQUFDLElBQUcsQ0FBQyxLQUFHLENBQUMsQ0FBQyxJQUFJO2NBQUMsSUFBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFLLElBQUcsQ0FBQyxLQUFHLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUssSUFBRyxDQUFDLEtBQUcsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxHQUFHLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLEtBQUk7Z0JBQUMsS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEdBQUcsRUFBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxFQUFDLENBQUMsRUFBRSxFQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxHQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFJLElBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDO2dCQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUM7Y0FBQTtZQUFDLE9BQUksQ0FBQyxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsQ0FBQyxRQUFRLElBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7VUFBQyxPQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRztVQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSztRQUFBLENBQUMsTUFBSyxPQUFLLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO01BQUMsT0FBSyxPQUFLLENBQUMsR0FBRSxDQUFDLEtBQUcsQ0FBQyxDQUFDLElBQUksR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxLQUFLO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxpQkFBaUIsR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxjQUFjLEdBQUMsQ0FBQyxJQUFFLENBQUMsS0FBRyxJQUFJLENBQUMsY0FBYyxHQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFVBQVUsR0FBQyxJQUFJLENBQUMsVUFBVSxJQUFFLEVBQUUsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztJQUFBLENBQUM7SUFBQyxJQUFJLEVBQUUsR0FBQyxTQUFBLENBQUEsRUFBVTtNQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxFQUFDLElBQUksQ0FBQyxLQUFLLEVBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsQ0FBQyxXQUFXLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxHQUFDLElBQUksQ0FBQyxRQUFRLEdBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxRQUFRLEVBQUMsQ0FBQyxDQUFDO01BQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsR0FBQyxFQUFFLEVBQUMsQ0FBQyxDQUFDLElBQUksR0FBQyxJQUFJO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLEdBQUMsVUFBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxPQUFPLENBQUMsS0FBRyxDQUFDLEtBQUcsQ0FBQyxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLFFBQVEsS0FBRyxDQUFDLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxDQUFDLElBQUUsSUFBSSxLQUFHLElBQUksQ0FBQyxRQUFRLEtBQUcsSUFBSSxDQUFDLFFBQVEsR0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7SUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLEtBQUssR0FBQyxVQUFTLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLENBQUM7TUFBQyxJQUFHLENBQUMsQ0FBQyxTQUFTLElBQUUsQ0FBQyxDQUFDLEtBQUssRUFBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUM7UUFBQyxLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsU0FBUyxLQUFHLENBQUMsQ0FBQyxVQUFVLEdBQUMsQ0FBQyxDQUFDO01BQUE7TUFBQyxPQUFPLENBQUMsQ0FBQyxTQUFTLEtBQUcsQ0FBQyxHQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsS0FBRyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxLQUFLLEVBQUMsQ0FBQyxDQUFDLEtBQUssRUFBQyxDQUFDLENBQUMsS0FBSyxDQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsUUFBUSxLQUFHLElBQUksQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxFQUFDLENBQUMsQ0FBQyxLQUFLLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsS0FBSyxFQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsRUFBQyxJQUFJLENBQUMsWUFBWSxHQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLElBQUksRUFBRSxHQUFDLFNBQUEsQ0FBUyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQztNQUFDLElBQUksQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQztNQUFDLElBQUcsQ0FBQyxDQUFDLEtBQUssRUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssS0FBSSxDQUFDLEdBQUMsQ0FBQyxDQUFDLFVBQVUsRUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLE1BQU0sRUFBQyxFQUFFLENBQUMsR0FBQyxDQUFDLENBQUMsR0FBRSxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQyxLQUFLLEtBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLElBQUUsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsSUFBRSxFQUFFLEtBQUcsQ0FBQyxJQUFFLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxNQUFNLElBQUUsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQVMsR0FBQyxVQUFTLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDO01BQUMsSUFBSSxDQUFDO1FBQUMsQ0FBQztRQUFDLENBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQztRQUFDLENBQUMsR0FBQyxFQUFFO1FBQUMsQ0FBQyxHQUFDLEVBQUU7UUFBQyxDQUFDLEdBQUMsRUFBRTtRQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLGFBQWE7TUFBQyxLQUFJLENBQUMsR0FBQyxDQUFDLENBQUMsUUFBUSxJQUFFLENBQUMsQ0FBQyxNQUFNLEVBQUMsRUFBRSxDQUFDLENBQUMsRUFBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxFQUFFLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsTUFBTSxFQUFDLEVBQUUsQ0FBQyxHQUFDLENBQUMsQ0FBQyxHQUFFLElBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLEVBQUM7UUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLElBQUk7UUFDbHgrQixLQUFJLENBQUMsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQztNQUFBO01BQUMsT0FBTyxDQUFDO0lBQUEsQ0FBQyxFQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUM7RUFBQSxDQUFDLEVBQUMsQ0FBQyxDQUFDLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDOzs7OztBQ1poSTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxNQUFNLENBQUMsUUFBUSxLQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUMsRUFBRSxDQUFDLEVBQUUsSUFBSSxDQUFDLFlBQVU7RUFBQyxZQUFZOztFQUFDLElBQUksQ0FBQyxHQUFDLFFBQVEsQ0FBQyxlQUFlO0lBQUMsQ0FBQyxHQUFDLE1BQU07SUFBQyxDQUFDLEdBQUMsU0FBQSxDQUFTLENBQUMsRUFBQyxDQUFDLEVBQUM7TUFBQyxJQUFJLENBQUMsR0FBQyxHQUFHLEtBQUcsQ0FBQyxHQUFDLE9BQU8sR0FBQyxRQUFRO1FBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsR0FBQyxDQUFDO1FBQUMsQ0FBQyxHQUFDLFFBQVEsQ0FBQyxJQUFJO01BQUMsT0FBTyxDQUFDLEtBQUcsQ0FBQyxJQUFFLENBQUMsS0FBRyxDQUFDLElBQUUsQ0FBQyxLQUFHLENBQUMsR0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsT0FBTyxHQUFDLENBQUMsQ0FBQyxJQUFFLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxRQUFRLEdBQUMsQ0FBQyxDQUFDO0lBQUEsQ0FBQztJQUFDLENBQUMsR0FBQyxNQUFNLENBQUMsU0FBUyxDQUFDLE1BQU0sQ0FBQztNQUFDLFFBQVEsRUFBQyxVQUFVO01BQUMsR0FBRyxFQUFDLENBQUM7TUFBQyxPQUFPLEVBQUMsT0FBTztNQUFDLElBQUksRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDLENBQUMsRUFBQyxDQUFDLEVBQUM7UUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxLQUFHLENBQUMsRUFBQyxJQUFJLENBQUMsT0FBTyxHQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxHQUFDLENBQUMsRUFBQyxRQUFRLElBQUUsT0FBTyxDQUFDLEtBQUcsQ0FBQyxHQUFDO1VBQUMsQ0FBQyxFQUFDO1FBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsUUFBUSxLQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxDQUFDLElBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUMsR0FBRyxFQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsS0FBSyxLQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLFlBQVksRUFBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFFLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLEVBQUMsQ0FBQyxDQUFDO01BQUEsQ0FBQztNQUFDLEdBQUcsRUFBQyxTQUFBLENBQVMsQ0FBQyxFQUFDO1FBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7UUFBQyxJQUFJLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsSUFBSSxJQUFFLENBQUMsSUFBSSxDQUFDLEtBQUssR0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsS0FBSztVQUFDLENBQUMsR0FBQyxDQUFDLEdBQUMsSUFBSSxDQUFDLEtBQUs7VUFBQyxDQUFDLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxLQUFLO1FBQUMsSUFBSSxDQUFDLFNBQVMsS0FBRyxDQUFDLElBQUksQ0FBQyxLQUFLLEtBQUcsQ0FBQyxHQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsR0FBQyxDQUFDLENBQUMsSUFBRSxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBQyxHQUFHLENBQUMsR0FBQyxDQUFDLEtBQUcsSUFBSSxDQUFDLEtBQUssR0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssS0FBRyxDQUFDLEdBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxHQUFDLENBQUMsQ0FBQyxJQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsS0FBRyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUMsSUFBSSxDQUFDLEtBQUssSUFBRSxJQUFJLENBQUMsS0FBSyxJQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxJQUFJLEdBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsS0FBSyxHQUFDLENBQUMsR0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBRSxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxLQUFHLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFDLElBQUksQ0FBQyxLQUFLLEdBQUMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsS0FBSyxHQUFDLElBQUksQ0FBQyxDQUFDO01BQUE7SUFBQyxDQUFDLENBQUM7SUFBQyxDQUFDLEdBQUMsQ0FBQyxDQUFDLFNBQVM7RUFBQyxDQUFDLENBQUMsR0FBRyxHQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7SUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFVBQVUsR0FBQyxDQUFDLENBQUMsVUFBVSxHQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsVUFBVSxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsVUFBVTtFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsSUFBSSxHQUFDLFlBQVU7SUFBQyxPQUFPLElBQUksQ0FBQyxJQUFJLEdBQUMsSUFBSSxJQUFFLENBQUMsQ0FBQyxXQUFXLEdBQUMsQ0FBQyxDQUFDLFdBQVcsR0FBQyxJQUFJLElBQUUsQ0FBQyxDQUFDLFNBQVMsR0FBQyxDQUFDLENBQUMsU0FBUyxHQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsU0FBUyxHQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsU0FBUztFQUFBLENBQUMsRUFBQyxDQUFDLENBQUMsS0FBSyxHQUFDLFVBQVMsQ0FBQyxFQUFDO0lBQUMsT0FBTyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxDQUFDLENBQUMsVUFBVSxLQUFHLElBQUksQ0FBQyxLQUFLLEdBQUMsQ0FBQyxDQUFDLENBQUMsRUFBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLENBQUMsQ0FBQztFQUFBLENBQUM7QUFBQSxDQUFDLENBQUMsRUFBQyxNQUFNLENBQUMsU0FBUyxJQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIi5jdXN0b20tc2VsZWN0XCIpLmZvckVhY2goY3VzdG9tU2VsZWN0ID0+IHtcblx0Y29uc3Qgc2VsZWN0QnRuID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3IoXCIuc2VsZWN0LWJ1dHRvblwiKTtcblx0Y29uc3Qgc2VsZWN0ZWRWYWx1ZSA9IGN1c3RvbVNlbGVjdC5xdWVyeVNlbGVjdG9yKFwiLnNlbGVjdGVkLXZhbHVlXCIpO1xuXHRjb25zdCBoYW5kbGVyID0gZnVuY3Rpb24oZWxtKSB7XG5cdFx0Y29uc3QgY3VzdG9tQ2hhbmdlRXZlbnQgPSBuZXcgQ3VzdG9tRXZlbnQoJ2N1c3RvbS1zZWxlY3QtY2hhbmdlJywge1xuXHRcdFx0ZGV0YWlsOiB7XG5cdFx0XHRcdHNlbGVjdGVkT3B0aW9uOiBlbG1cblx0XHRcdH1cblx0XHR9KTtcblx0XHRzZWxlY3RlZFZhbHVlLnRleHRDb250ZW50ID0gZWxtLnRleHRDb250ZW50O1xuXHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QucmVtb3ZlKFwiYWN0aXZlXCIpO1xuXHRcdGN1c3RvbVNlbGVjdC5kaXNwYXRjaEV2ZW50KGN1c3RvbUNoYW5nZUV2ZW50KTtcblxuXHR9XG5cdHNlbGVjdEJ0bi5hZGRFdmVudExpc3RlbmVyKFwiY2xpY2tcIiwgKCkgPT4ge1xuXHRcdGN1c3RvbVNlbGVjdC5jbGFzc0xpc3QudG9nZ2xlKFwiYWN0aXZlXCIpO1xuXHRcdHNlbGVjdEJ0bi5zZXRBdHRyaWJ1dGUoXG5cdFx0XHRcImFyaWEtZXhwYW5kZWRcIixcblx0XHRcdHNlbGVjdEJ0bi5nZXRBdHRyaWJ1dGUoXCJhcmlhLWV4cGFuZGVkXCIpID09PSBcInRydWVcIiA/IFwiZmFsc2VcIiA6IFwidHJ1ZVwiXG5cdFx0KTtcblx0fSk7XG5cblx0Y3VzdG9tU2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGlmIChlLnRhcmdldC5tYXRjaGVzKCdsYWJlbCcpKSB7XG5cblx0XHRcdGNvbnN0IGFsbEl0ZW1zID0gY3VzdG9tU2VsZWN0LnF1ZXJ5U2VsZWN0b3JBbGwoJ2xpJyk7XG5cdFx0XHRhbGxJdGVtcy5mb3JFYWNoKGl0ZW0gPT4gaXRlbS5jbGFzc0xpc3QucmVtb3ZlKCdhY3RpdmUnKSk7XG5cdFx0XHRjb25zdCBjbGlja2VkUGxhbiA9IGUudGFyZ2V0LmNsb3Nlc3QoJ2xpJyk7XG5cblx0XHRcdGlmIChjbGlja2VkUGxhbikge1xuXHRcdFx0XHRjbGlja2VkUGxhbi5jbGFzc0xpc3QuYWRkKCdhY3RpdmUnKTtcblx0XHRcdFx0aGFuZGxlcihjbGlja2VkUGxhbik7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImNsaWNrXCIsIChlKSA9PiB7XG5cdFx0aWYgKCFjdXN0b21TZWxlY3QuY29udGFpbnMoZS50YXJnZXQpKSB7XG5cdFx0XHRjdXN0b21TZWxlY3QuY2xhc3NMaXN0LnJlbW92ZShcImFjdGl2ZVwiKTtcblx0XHRcdHNlbGVjdEJ0bi5zZXRBdHRyaWJ1dGUoXCJhcmlhLWV4cGFuZGVkXCIsIFwiZmFsc2VcIik7XG5cdFx0fVxuXHR9KTtcbn0pOyIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcbiAgICAvKipcbiAgICAgKiBSZWZyZXNoIExpY2Vuc2UgZGF0YVxuICAgICAqL1xuICAgIHZhciBfaXNSZWZyZXNoaW5nID0gZmFsc2U7XG4gICAgJCgnI3dwci1hY3Rpb24tcmVmcmVzaF9hY2NvdW50Jykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBpZighX2lzUmVmcmVzaGluZyl7XG4gICAgICAgICAgICB2YXIgYnV0dG9uID0gJCh0aGlzKTtcbiAgICAgICAgICAgIHZhciBhY2NvdW50ID0gJCgnI3dwci1hY2NvdW50LWRhdGEnKTtcbiAgICAgICAgICAgIHZhciBleHBpcmUgPSAkKCcjd3ByLWV4cGlyYXRpb24tZGF0YScpO1xuXG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICBfaXNSZWZyZXNoaW5nID0gdHJ1ZTtcbiAgICAgICAgICAgIGJ1dHRvbi50cmlnZ2VyKCAnYmx1cicgKTtcbiAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuICAgICAgICAgICAgZXhwaXJlLnJlbW92ZUNsYXNzKCd3cHItaXNWYWxpZCB3cHItaXNJbnZhbGlkJyk7XG5cbiAgICAgICAgICAgICQucG9zdChcbiAgICAgICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X3JlZnJlc2hfY3VzdG9tZXJfZGF0YScsXG4gICAgICAgICAgICAgICAgICAgIF9hamF4X25vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgZnVuY3Rpb24ocmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5hZGRDbGFzcygnd3ByLWlzSGlkZGVuJyk7XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKCB0cnVlID09PSByZXNwb25zZS5zdWNjZXNzICkge1xuICAgICAgICAgICAgICAgICAgICAgICAgYWNjb3VudC5odG1sKHJlc3BvbnNlLmRhdGEubGljZW5zZV90eXBlKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGV4cGlyZS5hZGRDbGFzcyhyZXNwb25zZS5kYXRhLmxpY2Vuc2VfY2xhc3MpLmh0bWwocmVzcG9uc2UuZGF0YS5saWNlbnNlX2V4cGlyYXRpb24pO1xuICAgICAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24ucmVtb3ZlQ2xhc3MoJ3dwci1pY29uLXJlZnJlc2ggd3ByLWlzSGlkZGVuJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLmFkZENsYXNzKCd3cHItaWNvbi1jaGVjaycpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSwgMjUwKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICBlbHNle1xuICAgICAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24ucmVtb3ZlQ2xhc3MoJ3dwci1pY29uLXJlZnJlc2ggd3ByLWlzSGlkZGVuJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uLmFkZENsYXNzKCd3cHItaWNvbi1jbG9zZScpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSwgMjUwKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSh7b25Db21wbGV0ZTpmdW5jdGlvbigpe1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9pc1JlZnJlc2hpbmcgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH19KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOicrPXdwci1pc0hpZGRlbid9fSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonLT13cHItaWNvbi1jaGVjayd9fSwgMC4yNSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonLT13cHItaWNvbi1jbG9zZSd9fSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnNldChidXR0b24sIHtjc3M6e2NsYXNzTmFtZTonKz13cHItaWNvbi1yZWZyZXNoJ319LCAwLjI1KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pc0hpZGRlbid9fSlcbiAgICAgICAgICAgICAgICAgICAgICAgIDtcbiAgICAgICAgICAgICAgICAgICAgfSwgMjAwMCk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgKTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfSk7XG5cbiAgICAvKipcbiAgICAgKiBTYXZlIFRvZ2dsZSBvcHRpb24gdmFsdWVzIG9uIGNoYW5nZVxuICAgICAqL1xuICAgICQoJy53cHItcmFkaW8gaW5wdXRbdHlwZT1jaGVja2JveF0nKS5vbignY2hhbmdlJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIHZhciBuYW1lICA9ICQodGhpcykuYXR0cignaWQnKTtcbiAgICAgICAgdmFyIHZhbHVlID0gJCh0aGlzKS5wcm9wKCdjaGVja2VkJykgPyAxIDogMDtcblxuXHRcdHZhciBleGNsdWRlZCA9IFsgJ2Nsb3VkZmxhcmVfYXV0b19zZXR0aW5ncycsICdjbG91ZGZsYXJlX2Rldm1vZGUnLCAnYW5hbHl0aWNzX2VuYWJsZWQnIF07XG5cdFx0aWYgKCBleGNsdWRlZC5pbmRleE9mKCBuYW1lICkgPj0gMCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfdG9nZ2xlX29wdGlvbicsXG4gICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2UsXG4gICAgICAgICAgICAgICAgb3B0aW9uOiB7XG4gICAgICAgICAgICAgICAgICAgIG5hbWU6IG5hbWUsXG4gICAgICAgICAgICAgICAgICAgIHZhbHVlOiB2YWx1ZVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBmdW5jdGlvbihyZXNwb25zZSkge31cbiAgICAgICAgKTtcblx0fSk7XG5cblx0LyoqXG4gICAgICogU2F2ZSBlbmFibGUgQ1BDU1MgZm9yIG1vYmlsZXMgb3B0aW9uLlxuICAgICAqL1xuICAgICQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycsXG4gICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0Ly8gSGlkZSBNb2JpbGUgQ1BDU1MgYnRuIG9uIHN1Y2Nlc3MuXG5cdFx0XHRcdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1oaWRlLW9uLWNsaWNrJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItc2hvdy1vbi1jbGljaycpLnNob3coKTtcblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cdFx0XHRcdH1cblx0XHRcdH1cbiAgICAgICAgKTtcbiAgICB9KTtcblxuICAgIC8qKlxuICAgICAqIFNhdmUgZW5hYmxlIEdvb2dsZSBGb250cyBPcHRpbWl6YXRpb24gb3B0aW9uLlxuICAgICAqL1xuICAgICQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycsXG4gICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0Ly8gSGlkZSBNb2JpbGUgQ1BDU1MgYnRuIG9uIHN1Y2Nlc3MuXG5cdFx0XHRcdFx0JCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1oaWRlLW9uLWNsaWNrJykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJy53cHItc2hvdy1vbi1jbGljaycpLnNob3coKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjbWluaWZ5X2dvb2dsZV9mb250cycpLnZhbCgxKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xuXG4gICAgJCggJyNyb2NrZXQtZGlzbWlzcy1wcm9tb3Rpb24nICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZGlzbWlzc19wcm9tbycsXG4gICAgICAgICAgICAgICAgbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0JCgnI3JvY2tldC1wcm9tby1iYW5uZXInKS5oaWRlKCAnc2xvdycgKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0gKTtcblxuICAgICQoICcjcm9ja2V0LWRpc21pc3MtcmVuZXdhbCcgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9kaXNtaXNzX3JlbmV3YWwnLFxuICAgICAgICAgICAgICAgIG5vbmNlOiByb2NrZXRfYWpheF9kYXRhLm5vbmNlXG4gICAgICAgICAgICB9LFxuXHRcdFx0ZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0XHRcdCQoJyNyb2NrZXQtcmVuZXdhbC1iYW5uZXInKS5oaWRlKCAnc2xvdycgKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0gKTtcblx0JCggJyN3cHItdXBkYXRlLWV4Y2x1c2lvbi1saXN0JyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0JCgnI3dwci11cGRhdGUtZXhjbHVzaW9uLW1zZycpLmh0bWwoJycpO1xuXHRcdCQuYWpheCh7XG5cdFx0XHR1cmw6IHJvY2tldF9hamF4X2RhdGEucmVzdF91cmwsXG5cdFx0XHRiZWZvcmVTZW5kOiBmdW5jdGlvbiAoIHhociApIHtcblx0XHRcdFx0eGhyLnNldFJlcXVlc3RIZWFkZXIoICdYLVdQLU5vbmNlJywgcm9ja2V0X2FqYXhfZGF0YS5yZXN0X25vbmNlICk7XG5cdFx0XHRcdHhoci5zZXRSZXF1ZXN0SGVhZGVyKCAnQWNjZXB0JywgJ2FwcGxpY2F0aW9uL2pzb24sICovKjtxPTAuMScgKTtcblx0XHRcdFx0eGhyLnNldFJlcXVlc3RIZWFkZXIoICdDb250ZW50LVR5cGUnLCAnYXBwbGljYXRpb24vanNvbicgKTtcblx0XHRcdH0sXG5cdFx0XHRtZXRob2Q6IFwiUFVUXCIsXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZXMpIHtcblx0XHRcdFx0bGV0IGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyID0gJCgnI3dwci11cGRhdGUtZXhjbHVzaW9uLW1zZycpO1xuXHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5odG1sKCcnKTtcblx0XHRcdFx0aWYgKCB1bmRlZmluZWQgIT09IHJlc3BvbnNlc1snc3VjY2VzcyddICkge1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxkaXYgY2xhc3M9XCJub3RpY2Ugbm90aWNlLWVycm9yXCI+JyArIHJlc3BvbnNlc1snbWVzc2FnZSddICsgJzwvZGl2PicgKTtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblx0XHRcdFx0T2JqZWN0LmtleXMoIHJlc3BvbnNlcyApLmZvckVhY2goKCByZXNwb25zZV9rZXkgKSA9PiB7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCAnPHN0cm9uZz4nICsgcmVzcG9uc2Vfa2V5ICsgJzogPC9zdHJvbmc+JyApO1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggcmVzcG9uc2VzW3Jlc3BvbnNlX2tleV1bJ21lc3NhZ2UnXSApO1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxicj4nICk7XG5cdFx0XHRcdH0pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9ICk7XG5cbiAgICAvKipcbiAgICAgKiBFbmFibGUgbW9iaWxlIGNhY2hlIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZW5hYmxlX21vYmlsZV9jYWNoZScsXG4gICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0Ly8gSGlkZSBNb2JpbGUgY2FjaGUgZW5hYmxlIGJ1dHRvbiBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcjd3ByX21vYmlsZV9jYWNoZV9kZWZhdWx0JykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJyN3cHJfbW9iaWxlX2NhY2hlX3Jlc3BvbnNlJykuc2hvdygpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICAgICAgICAgICAgIC8vIFNldCB2YWx1ZXMgb2YgbW9iaWxlIGNhY2hlIGFuZCBzZXBhcmF0ZSBjYWNoZSBmaWxlcyBmb3IgbW9iaWxlcyBvcHRpb24gdG8gMS5cbiAgICAgICAgICAgICAgICAgICAgJCgnI2NhY2hlX21vYmlsZScpLnZhbCgxKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI2RvX2NhY2hpbmdfbW9iaWxlX2ZpbGVzJykudmFsKDEpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG59KTtcblxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGZ1bmN0aW9uKCkge1xuXHRjb25zdCBhbmFseXRpY3NDaGVja2JveCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdhbmFseXRpY3NfZW5hYmxlZCcpO1xuXG5cdGlmIChhbmFseXRpY3NDaGVja2JveCkge1xuXHRcdGFuYWx5dGljc0NoZWNrYm94LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuXHRcdFx0Y29uc3QgaXNDaGVja2VkID0gdGhpcy5jaGVja2VkO1xuXG5cdFx0XHRmZXRjaChhamF4dXJsLCB7XG5cdFx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0XHRoZWFkZXJzOiB7XG5cdFx0XHRcdFx0J0NvbnRlbnQtVHlwZSc6ICdhcHBsaWNhdGlvbi94LXd3dy1mb3JtLXVybGVuY29kZWQnLFxuXHRcdFx0XHR9LFxuXHRcdFx0XHRib2R5OiBuZXcgVVJMU2VhcmNoUGFyYW1zKHtcblx0XHRcdFx0XHRhY3Rpb246ICdyb2NrZXRfdG9nZ2xlX29wdGluJyxcblx0XHRcdFx0XHR2YWx1ZTogaXNDaGVja2VkID8gMSA6IDAsXG5cdFx0XHRcdFx0X2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2UsXG5cdFx0XHRcdH0pXG5cdFx0XHR9KTtcblx0XHR9KTtcblx0fVxufSk7IiwiLy8gQWRkIGdyZWVuc29jayBsaWIgZm9yIGFuaW1hdGlvbnNcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL1R3ZWVuTGl0ZS5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svVGltZWxpbmVMaXRlLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9lYXNpbmcvRWFzZVBhY2subWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9wbHVnaW5zL1Njcm9sbFRvUGx1Z2luLm1pbi5qcyc7XHJcblxyXG4vLyBBZGQgc2NyaXB0c1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9wYWdlTWFuYWdlci5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL21haW4uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9maWVsZHMuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9iZWFjb24uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9hamF4LmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvcm9ja2V0Y2RuLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvY291bnRkb3duLmpzJzsiLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG4gICAgaWYgKCdCZWFjb24nIGluIHdpbmRvdykge1xuICAgICAgICAvKipcbiAgICAgICAgICogU2hvdyBiZWFjb25zIG9uIGJ1dHRvbiBcImhlbHBcIiBjbGlja1xuICAgICAgICAgKi9cbiAgICAgICAgdmFyICRoZWxwID0gJCgnLndwci1pbmZvQWN0aW9uLS1oZWxwJyk7XG4gICAgICAgICRoZWxwLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpe1xuICAgICAgICAgICAgdmFyIGlkcyA9ICQodGhpcykuZGF0YSgnYmVhY29uLWlkJyk7XG4gICAgICAgICAgICB2YXIgYnV0dG9uID0gJCh0aGlzKS5kYXRhKCd3cHJfdHJhY2tfYnV0dG9uJykgfHwgJ0JlYWNvbiBIZWxwJztcbiAgICAgICAgICAgIHZhciBjb250ZXh0ID0gJCh0aGlzKS5kYXRhKCd3cHJfdHJhY2tfY29udGV4dCcpIHx8ICdTZXR0aW5ncyc7XG4gICAgICAgICAgICBcbiAgICAgICAgICAgIC8vIFRyYWNrIHdpdGggTWl4UGFuZWwgSlMgU0RLXG4gICAgICAgICAgICB3cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KTtcbiAgICAgICAgICAgIFxuICAgICAgICAgICAgLy8gQ29udGludWUgd2l0aCBleGlzdGluZyBiZWFjb24gZnVuY3Rpb25hbGl0eVxuICAgICAgICAgICAgd3ByQ2FsbEJlYWNvbihpZHMpO1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9KTtcblxuICAgICAgICBmdW5jdGlvbiB3cHJDYWxsQmVhY29uKGFJRCl7XG4gICAgICAgICAgICBhSUQgPSBhSUQuc3BsaXQoJywnKTtcbiAgICAgICAgICAgIGlmICggYUlELmxlbmd0aCA9PT0gMCApIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICBpZiAoIGFJRC5sZW5ndGggPiAxICkge1xuICAgICAgICAgICAgICAgICAgICB3aW5kb3cuQmVhY29uKFwic3VnZ2VzdFwiLCBhSUQpO1xuXG4gICAgICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB3aW5kb3cuQmVhY29uKFwib3BlblwiKTtcbiAgICAgICAgICAgICAgICAgICAgfSwgMjAwKTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICB3aW5kb3cuQmVhY29uKFwiYXJ0aWNsZVwiLCBhSUQudG9TdHJpbmcoKSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgIH1cbiAgICB9XG4gICAgXG4gICAgLy8gTWl4UGFuZWwgdHJhY2tpbmcgZnVuY3Rpb25cbiAgICBmdW5jdGlvbiB3cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KSB7XG4gICAgICAgIGlmICh0eXBlb2YgbWl4cGFuZWwgIT09ICd1bmRlZmluZWQnICYmIG1peHBhbmVsLnRyYWNrKSB7XG4gICAgICAgICAgICAvLyBDaGVjayBpZiB1c2VyIGhhcyBvcHRlZCBpbiB1c2luZyBsb2NhbGl6ZWQgZGF0YVxuICAgICAgICAgICAgaWYgKHR5cGVvZiByb2NrZXRfbWl4cGFuZWxfZGF0YSA9PT0gJ3VuZGVmaW5lZCcgfHwgIXJvY2tldF9taXhwYW5lbF9kYXRhLm9wdGluX2VuYWJsZWQgfHwgcm9ja2V0X21peHBhbmVsX2RhdGEub3B0aW5fZW5hYmxlZCA9PT0gJzAnKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgXG4gICAgICAgICAgICAvLyBJZGVudGlmeSB1c2VyIHdpdGggaGFzaGVkIGxpY2Vuc2UgZW1haWwgaWYgYXZhaWxhYmxlXG4gICAgICAgICAgICBpZiAocm9ja2V0X21peHBhbmVsX2RhdGEudXNlcl9pZCAmJiB0eXBlb2YgbWl4cGFuZWwuaWRlbnRpZnkgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICBtaXhwYW5lbC5pZGVudGlmeShyb2NrZXRfbWl4cGFuZWxfZGF0YS51c2VyX2lkKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIFxuICAgICAgICAgICAgbWl4cGFuZWwudHJhY2soJ1dQTSBCdXR0b24gQ2xpY2tlZCcsIHtcbiAgICAgICAgICAgICAgICAnYnV0dG9uJzogYnV0dG9uLFxuICAgICAgICAgICAgICAgICdicmFuZCc6IHJvY2tldF9taXhwYW5lbF9kYXRhLmJyYW5kLFxuICAgICAgICAgICAgICAgICdwcm9kdWN0Jzogcm9ja2V0X21peHBhbmVsX2RhdGEucHJvZHVjdCxcbiAgICAgICAgICAgICAgICAnY29udGV4dCc6IGNvbnRleHQsXG4gICAgICAgICAgICAgICAgJ3BhdGgnOiByb2NrZXRfbWl4cGFuZWxfZGF0YS5wYXRoXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBcbiAgICAvLyBNYWtlIGZ1bmN0aW9uIGdsb2JhbGx5IGF2YWlsYWJsZVxuICAgIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPSB3cHJUcmFja0hlbHBCdXR0b247XG59KTtcbiIsImZ1bmN0aW9uIGdldFRpbWVSZW1haW5pbmcoZW5kdGltZSl7XG4gICAgY29uc3Qgc3RhcnQgPSBEYXRlLm5vdygpO1xuICAgIGNvbnN0IHRvdGFsID0gKGVuZHRpbWUgKiAxMDAwKSAtIHN0YXJ0O1xuICAgIGNvbnN0IHNlY29uZHMgPSBNYXRoLmZsb29yKCAodG90YWwvMTAwMCkgJSA2MCApO1xuICAgIGNvbnN0IG1pbnV0ZXMgPSBNYXRoLmZsb29yKCAodG90YWwvMTAwMC82MCkgJSA2MCApO1xuICAgIGNvbnN0IGhvdXJzID0gTWF0aC5mbG9vciggKHRvdGFsLygxMDAwKjYwKjYwKSkgJSAyNCApO1xuICAgIGNvbnN0IGRheXMgPSBNYXRoLmZsb29yKCB0b3RhbC8oMTAwMCo2MCo2MCoyNCkgKTtcblxuICAgIHJldHVybiB7XG4gICAgICAgIHRvdGFsLFxuICAgICAgICBkYXlzLFxuICAgICAgICBob3VycyxcbiAgICAgICAgbWludXRlcyxcbiAgICAgICAgc2Vjb25kc1xuICAgIH07XG59XG5cbmZ1bmN0aW9uIGluaXRpYWxpemVDbG9jayhpZCwgZW5kdGltZSkge1xuICAgIGNvbnN0IGNsb2NrID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpO1xuXG4gICAgaWYgKGNsb2NrID09PSBudWxsKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBkYXlzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLWRheXMnKTtcbiAgICBjb25zdCBob3Vyc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1ob3VycycpO1xuICAgIGNvbnN0IG1pbnV0ZXNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tbWludXRlcycpO1xuICAgIGNvbnN0IHNlY29uZHNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tc2Vjb25kcycpO1xuXG4gICAgZnVuY3Rpb24gdXBkYXRlQ2xvY2soKSB7XG4gICAgICAgIGNvbnN0IHQgPSBnZXRUaW1lUmVtYWluaW5nKGVuZHRpbWUpO1xuXG4gICAgICAgIGlmICh0LnRvdGFsIDwgMCkge1xuICAgICAgICAgICAgY2xlYXJJbnRlcnZhbCh0aW1laW50ZXJ2YWwpO1xuXG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBkYXlzU3Bhbi5pbm5lckhUTUwgPSB0LmRheXM7XG4gICAgICAgIGhvdXJzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5ob3Vycykuc2xpY2UoLTIpO1xuICAgICAgICBtaW51dGVzU3Bhbi5pbm5lckhUTUwgPSAoJzAnICsgdC5taW51dGVzKS5zbGljZSgtMik7XG4gICAgICAgIHNlY29uZHNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0LnNlY29uZHMpLnNsaWNlKC0yKTtcbiAgICB9XG5cbiAgICB1cGRhdGVDbG9jaygpO1xuICAgIGNvbnN0IHRpbWVpbnRlcnZhbCA9IHNldEludGVydmFsKHVwZGF0ZUNsb2NrLCAxMDAwKTtcbn1cblxuZnVuY3Rpb24gcnVjc3NUaW1lcihpZCwgZW5kdGltZSkge1xuXHRjb25zdCB0aW1lciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGlkKTtcblx0Y29uc3Qgbm90aWNlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JvY2tldC1ub3RpY2Utc2Fhcy1wcm9jZXNzaW5nJyk7XG5cdGNvbnN0IHN1Y2Nlc3MgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1zYWFzLXN1Y2Nlc3MnKTtcblxuXHRpZiAodGltZXIgPT09IG51bGwpIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHRmdW5jdGlvbiB1cGRhdGVUaW1lcigpIHtcblx0XHRjb25zdCBzdGFydCA9IERhdGUubm93KCk7XG5cdFx0Y29uc3QgcmVtYWluaW5nID0gTWF0aC5mbG9vciggKCAoZW5kdGltZSAqIDEwMDApIC0gc3RhcnQgKSAvIDEwMDAgKTtcblxuXHRcdGlmIChyZW1haW5pbmcgPD0gMCkge1xuXHRcdFx0Y2xlYXJJbnRlcnZhbCh0aW1lckludGVydmFsKTtcblxuXHRcdFx0aWYgKG5vdGljZSAhPT0gbnVsbCkge1xuXHRcdFx0XHRub3RpY2UuY2xhc3NMaXN0LmFkZCgnaGlkZGVuJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmIChzdWNjZXNzICE9PSBudWxsKSB7XG5cdFx0XHRcdHN1Y2Nlc3MuY2xhc3NMaXN0LnJlbW92ZSgnaGlkZGVuJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmICggcm9ja2V0X2FqYXhfZGF0YS5jcm9uX2Rpc2FibGVkICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IGRhdGEgPSBuZXcgRm9ybURhdGEoKTtcblxuXHRcdFx0ZGF0YS5hcHBlbmQoICdhY3Rpb24nLCAncm9ja2V0X3NwYXduX2Nyb24nICk7XG5cdFx0XHRkYXRhLmFwcGVuZCggJ25vbmNlJywgcm9ja2V0X2FqYXhfZGF0YS5ub25jZSApO1xuXG5cdFx0XHRmZXRjaCggYWpheHVybCwge1xuXHRcdFx0XHRtZXRob2Q6ICdQT1NUJyxcblx0XHRcdFx0Y3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG5cdFx0XHRcdGJvZHk6IGRhdGFcblx0XHRcdH0gKTtcblxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHRpbWVyLmlubmVySFRNTCA9IHJlbWFpbmluZztcblx0fVxuXG5cdHVwZGF0ZVRpbWVyKCk7XG5cdGNvbnN0IHRpbWVySW50ZXJ2YWwgPSBzZXRJbnRlcnZhbCggdXBkYXRlVGltZXIsIDEwMDApO1xufVxuXG5pZiAoIURhdGUubm93KSB7XG4gICAgRGF0ZS5ub3cgPSBmdW5jdGlvbiBub3coKSB7XG4gICAgICByZXR1cm4gbmV3IERhdGUoKS5nZXRUaW1lKCk7XG4gICAgfTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLnByb21vX2VuZCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBpbml0aWFsaXplQ2xvY2soJ3JvY2tldC1wcm9tby1jb3VudGRvd24nLCByb2NrZXRfYWpheF9kYXRhLnByb21vX2VuZCk7XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5saWNlbnNlX2V4cGlyYXRpb24gIT09ICd1bmRlZmluZWQnKSB7XG4gICAgaW5pdGlhbGl6ZUNsb2NrKCdyb2NrZXQtcmVuZXctY291bnRkb3duJywgcm9ja2V0X2FqYXhfZGF0YS5saWNlbnNlX2V4cGlyYXRpb24pO1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEubm90aWNlX2VuZF90aW1lICE9PSAndW5kZWZpbmVkJykge1xuICAgIHJ1Y3NzVGltZXIoJ3JvY2tldC1ydWNzcy10aW1lcicsIHJvY2tldF9hamF4X2RhdGEubm90aWNlX2VuZF90aW1lKTtcbn0iLCJpbXBvcnQgJy4uL2N1c3RvbS9jdXN0b20tc2VsZWN0LmpzJztcblxudmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXG5cbiAgICAvKioqXG4gICAgKiBDaGVjayBwYXJlbnQgLyBzaG93IGNoaWxkcmVuXG4gICAgKioqL1xuXG5cdGZ1bmN0aW9uIHdwclNob3dDaGlsZHJlbihhRWxlbSl7XG5cdFx0dmFyIHBhcmVudElkLCAkY2hpbGRyZW47XG5cblx0XHRhRWxlbSAgICAgPSAkKCBhRWxlbSApO1xuXHRcdHBhcmVudElkICA9IGFFbGVtLmF0dHIoJ2lkJyk7XG5cdFx0JGNoaWxkcmVuID0gJCgnW2RhdGEtcGFyZW50PVwiJyArIHBhcmVudElkICsgJ1wiXScpO1xuXG5cdFx0Ly8gVGVzdCBjaGVjayBmb3Igc3dpdGNoXG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRpZiAoICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0XHRcdHZhciBpZCA9ICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpO1xuXG5cdFx0XHRcdFx0JCgnW2RhdGEtcGFyZW50PVwiJyArIGlkICsgJ1wiXScpLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXHRcdH1cblx0XHRlbHNle1xuXHRcdFx0JGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR2YXIgaWQgPSAkKHRoaXMpLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKTtcblxuXHRcdFx0XHQkKCdbZGF0YS1wYXJlbnQ9XCInICsgaWQgKyAnXCJdJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdH0pO1xuXHRcdH1cblx0fVxuXG4gICAgLyoqXG4gICAgICogVGVsbCBpZiB0aGUgZ2l2ZW4gY2hpbGQgZmllbGQgaGFzIGFuIGFjdGl2ZSBwYXJlbnQgZmllbGQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0gIG9iamVjdCAkZmllbGQgQSBqUXVlcnkgb2JqZWN0IG9mIGEgXCIud3ByLWZpZWxkXCIgZmllbGQuXG4gICAgICogQHJldHVybiBib29sfG51bGxcbiAgICAgKi9cbiAgICBmdW5jdGlvbiB3cHJJc1BhcmVudEFjdGl2ZSggJGZpZWxkICkge1xuICAgICAgICB2YXIgJHBhcmVudDtcblxuICAgICAgICBpZiAoICEgJGZpZWxkLmxlbmd0aCApIHtcbiAgICAgICAgICAgIC8vIMKvXFxfKOODhClfL8KvXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgICRwYXJlbnQgPSAkZmllbGQuZGF0YSggJ3BhcmVudCcgKTtcblxuICAgICAgICBpZiAoIHR5cGVvZiAkcGFyZW50ICE9PSAnc3RyaW5nJyApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICRwYXJlbnQucmVwbGFjZSggL15cXHMrfFxccyskL2csICcnICk7XG5cbiAgICAgICAgaWYgKCAnJyA9PT0gJHBhcmVudCApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICQoICcjJyArICRwYXJlbnQgKTtcblxuICAgICAgICBpZiAoICEgJHBhcmVudC5sZW5ndGggKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIG1pc3Npbmc6IGxldCdzIGNvbnNpZGVyIGl0J3Mgbm90IGFjdGl2ZSB0aGVuLlxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCAhICRwYXJlbnQuaXMoICc6Y2hlY2tlZCcgKSAmJiAkcGFyZW50LmlzKCdpbnB1dCcpKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIGNoZWNrYm94IGFuZCBub3QgY2hlY2tlZDogZG9uJ3QgZGlzcGxheSB0aGUgZmllbGQgdGhlbi5cbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfVxuXG5cdFx0aWYgKCAhJHBhcmVudC5oYXNDbGFzcygncmFkaW8tYWN0aXZlJykgJiYgJHBhcmVudC5pcygnYnV0dG9uJykpIHtcblx0XHRcdC8vIFRoaXMgZmllbGQncyBwYXJlbnQgYnV0dG9uIGFuZCBpcyBub3QgYWN0aXZlOiBkb24ndCBkaXNwbGF5IHRoZSBmaWVsZCB0aGVuLlxuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cbiAgICAgICAgLy8gR28gcmVjdXJzaXZlIHRvIHRoZSBsYXN0IHBhcmVudC5cbiAgICAgICAgcmV0dXJuIHdwcklzUGFyZW50QWN0aXZlKCAkcGFyZW50LmNsb3Nlc3QoICcud3ByLWZpZWxkJyApICk7XG4gICAgfVxuXG5cdC8qKlxuXHQgKiBNYXNrcyBzZW5zaXRpdmUgaW5mb3JtYXRpb24gaW4gYW4gaW5wdXQgZmllbGQgYnkgcmVwbGFjaW5nIGFsbCBidXQgdGhlIGxhc3QgNCBjaGFyYWN0ZXJzIHdpdGggYXN0ZXJpc2tzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gaWRfc2VsZWN0b3IgLSBUaGUgSUQgb2YgdGhlIGlucHV0IGZpZWxkIHRvIGJlIG1hc2tlZC5cblx0ICogQHJldHVybnMge3ZvaWR9IC0gTW9kaWZpZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIGluLXBsYWNlLlxuXHQgKlxuXHQgKiBAZXhhbXBsZVxuXHQgKiAvLyBIVE1MOiA8aW5wdXQgdHlwZT1cInRleHRcIiBpZD1cImNyZWRpdENhcmRJbnB1dFwiIHZhbHVlPVwiMTIzNDU2Nzg5MDEyMzQ1NlwiPlxuXHQgKiBtYXNrRmllbGQoJ2NyZWRpdENhcmRJbnB1dCcpO1xuXHQgKiAvLyBSZXN1bHQ6IFVwZGF0ZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIHRvICcqKioqKioqKioqKiozNDU2Jy5cblx0ICovXG5cdGZ1bmN0aW9uIG1hc2tGaWVsZChwcm94eV9zZWxlY3RvciwgY29uY3JldGVfc2VsZWN0b3IpIHtcblx0XHR2YXIgY29uY3JldGUgPSB7XG5cdFx0XHQndmFsJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCksXG5cdFx0XHQnbGVuZ3RoJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCkubGVuZ3RoXG5cdFx0fVxuXG5cdFx0aWYgKGNvbmNyZXRlLmxlbmd0aCA+IDQpIHtcblxuXHRcdFx0dmFyIGhpZGRlblBhcnQgPSAnXFx1MjAyMicucmVwZWF0KE1hdGgubWF4KDAsIGNvbmNyZXRlLmxlbmd0aCAtIDQpKTtcblx0XHRcdHZhciB2aXNpYmxlUGFydCA9IGNvbmNyZXRlLnZhbC5zbGljZSgtNCk7XG5cblx0XHRcdC8vIENvbWJpbmUgdGhlIGhpZGRlbiBhbmQgdmlzaWJsZSBwYXJ0c1xuXHRcdFx0dmFyIG1hc2tlZFZhbHVlID0gaGlkZGVuUGFydCArIHZpc2libGVQYXJ0O1xuXG5cdFx0XHRwcm94eV9zZWxlY3Rvci52YWwobWFza2VkVmFsdWUpO1xuXHRcdH1cblx0XHQvLyBFbnN1cmUgZXZlbnRzIGFyZSBub3QgYWRkZWQgbW9yZSB0aGFuIG9uY2Vcblx0XHRpZiAoIXByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJykpIHtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdpbnB1dCcsIGhhbmRsZUlucHV0KTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdmb2N1cycsIGhhbmRsZUZvY3VzKTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJywgdHJ1ZSk7XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlIHRoZSBpbnB1dCBldmVudFxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGhhbmRsZUlucHV0KCkge1xuXHRcdFx0dmFyIHByb3h5VmFsdWUgPSBwcm94eV9zZWxlY3Rvci52YWwoKTtcblx0XHRcdGlmIChwcm94eVZhbHVlLmluZGV4T2YoJ1xcdTIwMjInKSA9PT0gLTEpIHtcblx0XHRcdFx0Y29uY3JldGVfc2VsZWN0b3IudmFsKHByb3h5VmFsdWUpO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIEhhbmRsZSB0aGUgZm9jdXMgZXZlbnRcblx0XHQgKi9cblx0XHRmdW5jdGlvbiBoYW5kbGVGb2N1cygpIHtcblx0XHRcdHZhciBjb25jcmV0ZV92YWx1ZSA9IGNvbmNyZXRlX3NlbGVjdG9yLnZhbCgpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3IudmFsKGNvbmNyZXRlX3ZhbHVlKTtcblx0XHR9XG5cblx0fVxuXG5cdFx0Ly8gVXBkYXRlIHRoZSBjb25jcmV0ZSBmaWVsZCB3aGVuIHRoZSBwcm94eSBpcyB1cGRhdGVkLlxuXG5cblx0bWFza0ZpZWxkKCQoJyNjbG91ZGZsYXJlX2FwaV9rZXlfbWFzaycpLCAkKCcjY2xvdWRmbGFyZV9hcGlfa2V5JykpO1xuXHRtYXNrRmllbGQoJCgnI2Nsb3VkZmxhcmVfem9uZV9pZF9tYXNrJyksICQoJyNjbG91ZGZsYXJlX3pvbmVfaWQnKSk7XG5cblx0Ly8gRGlzcGxheS9IaWRlIGNoaWxkcmVuIGZpZWxkcyBvbiBjaGVja2JveCBjaGFuZ2UuXG4gICAgJCggJy53cHItaXNQYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nICkub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAvLyBPbiBwYWdlIGxvYWQsIGRpc3BsYXkgdGhlIGFjdGl2ZSBmaWVsZHMuXG4gICAgJCggJy53cHItZmllbGQtLWNoaWxkcmVuJyApLmVhY2goIGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgJGZpZWxkID0gJCggdGhpcyApO1xuXG4gICAgICAgIGlmICggd3BySXNQYXJlbnRBY3RpdmUoICRmaWVsZCApICkge1xuICAgICAgICAgICAgJGZpZWxkLmFkZENsYXNzKCAnd3ByLWlzT3BlbicgKTtcbiAgICAgICAgfVxuICAgIH0gKTtcblxuXG5cblxuICAgIC8qKipcbiAgICAqIFdhcm5pbmcgZmllbGRzXG4gICAgKioqL1xuXG4gICAgdmFyICR3YXJuaW5nUGFyZW50ID0gJCgnLndwci1maWVsZC0tcGFyZW50Jyk7XG4gICAgdmFyICR3YXJuaW5nUGFyZW50SW5wdXQgPSAkKCcud3ByLWZpZWxkLS1wYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nKTtcblxuICAgIC8vIElmIGFscmVhZHkgY2hlY2tlZFxuICAgICR3YXJuaW5nUGFyZW50SW5wdXQuZWFjaChmdW5jdGlvbigpe1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAkd2FybmluZ1BhcmVudC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHdwclNob3dXYXJuaW5nKCQodGhpcykpO1xuICAgIH0pO1xuXG4gICAgZnVuY3Rpb24gd3ByU2hvd1dhcm5pbmcoYUVsZW0pe1xuICAgICAgICB2YXIgJHdhcm5pbmdGaWVsZCA9IGFFbGVtLm5leHQoJy53cHItZmllbGRXYXJuaW5nJyksXG4gICAgICAgICAgICAkdGhpc0NoZWNrYm94ID0gYUVsZW0uZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKSxcbiAgICAgICAgICAgICRuZXh0V2FybmluZyA9IGFFbGVtLnBhcmVudCgpLm5leHQoJy53cHItd2FybmluZ0NvbnRhaW5lcicpLFxuICAgICAgICAgICAgJG5leHRGaWVsZHMgPSAkbmV4dFdhcm5pbmcuZmluZCgnLndwci1maWVsZCcpLFxuICAgICAgICAgICAgcGFyZW50SWQgPSBhRWxlbS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyksXG4gICAgICAgICAgICAkY2hpbGRyZW4gPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgcGFyZW50SWQgKyAnXCJdJylcbiAgICAgICAgO1xuXG4gICAgICAgIC8vIENoZWNrIHdhcm5pbmcgcGFyZW50XG4gICAgICAgIGlmKCR0aGlzQ2hlY2tib3guaXMoJzpjaGVja2VkJykpe1xuICAgICAgICAgICAgJHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuICAgICAgICAgICAgJHRoaXNDaGVja2JveC5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgYUVsZW0udHJpZ2dlcignY2hhbmdlJyk7XG5cblxuICAgICAgICAgICAgdmFyICR3YXJuaW5nQnV0dG9uID0gJHdhcm5pbmdGaWVsZC5maW5kKCcud3ByLWJ1dHRvbicpO1xuXG4gICAgICAgICAgICAvLyBWYWxpZGF0ZSB0aGUgd2FybmluZ1xuICAgICAgICAgICAgJHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAkdGhpc0NoZWNrYm94LnByb3AoJ2NoZWNrZWQnLCB0cnVlKTtcbiAgICAgICAgICAgICAgICAkd2FybmluZ0ZpZWxkLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgICAgICAgICAgJGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cbiAgICAgICAgICAgICAgICAvLyBJZiBuZXh0IGVsZW0gPSBkaXNhYmxlZFxuICAgICAgICAgICAgICAgIGlmKCRuZXh0V2FybmluZy5sZW5ndGggPiAwKXtcbiAgICAgICAgICAgICAgICAgICAgJG5leHRGaWVsZHMucmVtb3ZlQ2xhc3MoJ3dwci1pc0Rpc2FibGVkJyk7XG4gICAgICAgICAgICAgICAgICAgICRuZXh0RmllbGRzLmZpbmQoJ2lucHV0JykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICRuZXh0RmllbGRzLmFkZENsYXNzKCd3cHItaXNEaXNhYmxlZCcpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgJGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDTkFNRVMgYWRkL3JlbW92ZSBsaW5lc1xuICAgICAqL1xuICAgICQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLW11bHRpcGxlLWNsb3NlJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKHRoaXMpLnBhcmVudCgpLnNsaWRlVXAoICdzbG93JyAsIGZ1bmN0aW9uKCl7JCh0aGlzKS5yZW1vdmUoKTsgfSApO1xuXHR9ICk7XG5cblx0JCgnLndwci1idXR0b24tLWFkZE11bHRpJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgJCgkKCcjd3ByLWNuYW1lLW1vZGVsJykuaHRtbCgpKS5hcHBlbmRUbygnI3dwci1jbmFtZXMtbGlzdCcpO1xuICAgIH0pO1xuXG5cdC8qKipcblx0ICogV3ByIFJhZGlvIGJ1dHRvblxuXHQgKioqL1xuXHR2YXIgZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRpZigkKHRoaXMpLmhhc0NsYXNzKCdyYWRpby1hY3RpdmUnKSl7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHZhciAkcGFyZW50ID0gJCh0aGlzKS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJykucmVtb3ZlQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItZmllbGRXYXJuaW5nJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkKHRoaXMpLmFkZENsYXNzKCdyYWRpby1hY3RpdmUnKTtcblx0XHR3cHJTaG93UmFkaW9XYXJuaW5nKCQodGhpcykpO1xuXG5cdH0gKTtcblxuXG5cdGZ1bmN0aW9uIHdwclNob3dSYWRpb1dhcm5pbmcoJGVsbSl7XG5cdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cdFx0JGVsbS50cmlnZ2VyKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgWyAkZWxtIF0gKTtcblx0XHRpZiAoISRlbG0uaGFzQ2xhc3MoJ2hhcy13YXJuaW5nJykgfHwgZGlzYWJsZV9yYWRpb193YXJuaW5nKSB7XG5cdFx0XHR3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKTtcblx0XHRcdCRlbG0udHJpZ2dlciggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgWyAkZWxtIF0gKTtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyICR3YXJuaW5nRmllbGQgPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgJGVsbS5hdHRyKCdpZCcpICsgJ1wiXS53cHItZmllbGRXYXJuaW5nJyk7XG5cdFx0JHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdHZhciAkd2FybmluZ0J1dHRvbiA9ICR3YXJuaW5nRmllbGQuZmluZCgnLndwci1idXR0b24nKTtcblxuXHRcdC8vIFZhbGlkYXRlIHRoZSB3YXJuaW5nXG5cdFx0JHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcblx0XHRcdCR3YXJuaW5nRmllbGQucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pO1xuXHRcdFx0JGVsbS50cmlnZ2VyKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBbICRlbG0gXSApO1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH0pO1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSkge1xuXHRcdHZhciAkcGFyZW50ID0gJGVsbS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHR2YXIgJGNoaWxkcmVuID0gJCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyW2RhdGEtcGFyZW50PVwiJyArICRlbG0uYXR0cignaWQnKSArICdcIl0nKTtcblx0XHQkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0fVxuXG5cdC8qKipcblx0ICogV3ByIE9wdGltaXplIENzcyBEZWxpdmVyeSBGaWVsZFxuXHQgKioqL1xuXHR2YXIgcnVjc3NBY3RpdmUgPSBwYXJzZUludCgkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoKSk7XG5cblx0JCggXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCAud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvblwiIClcblx0XHQub24oIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIGZ1bmN0aW9uKCBldmVudCwgJGVsbSApIHtcblx0XHRcdHRvZ2dsZUFjdGl2ZU9wdGltaXplQ3NzRGVsaXZlcnlNZXRob2QoJGVsbSk7XG5cdFx0fSk7XG5cblx0JChcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlcIikub24oXCJjaGFuZ2VcIiwgZnVuY3Rpb24oKXtcblx0XHRpZiggJCh0aGlzKS5pcyhcIjpub3QoOmNoZWNrZWQpXCIpICl7XG5cdFx0XHRkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpO1xuXHRcdH1lbHNle1xuXHRcdFx0dmFyIGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkID0gJyMnKyQoJyNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kJykuZGF0YSggJ2RlZmF1bHQnICk7XG5cdFx0XHQkKGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkKS50cmlnZ2VyKCdjbGljaycpO1xuXHRcdH1cblx0fSk7XG5cblx0ZnVuY3Rpb24gdG9nZ2xlQWN0aXZlT3B0aW1pemVDc3NEZWxpdmVyeU1ldGhvZCgkZWxtKSB7XG5cdFx0dmFyIG9wdGltaXplX21ldGhvZCA9ICRlbG0uZGF0YSgndmFsdWUnKTtcblx0XHRpZigncmVtb3ZlX3VudXNlZF9jc3MnID09PSBvcHRpbWl6ZV9tZXRob2Qpe1xuXHRcdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDEpO1xuXHRcdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0XHR9ZWxzZXtcblx0XHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgwKTtcblx0XHRcdCQoJyNhc3luY19jc3MnKS52YWwoMSk7XG5cdFx0fVxuXG5cdH1cblxuXHRmdW5jdGlvbiBkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpIHtcblx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMCk7XG5cdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0fVxuXG5cdCQoIFwiI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QgLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b25cIiApXG5cdFx0Lm9uKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgZnVuY3Rpb24oIGV2ZW50LCAkZWxtICkge1xuXHRcdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gKCdyZW1vdmVfdW51c2VkX2NzcycgPT09ICRlbG0uZGF0YSgndmFsdWUnKSAmJiAxID09PSBydWNzc0FjdGl2ZSlcblx0XHR9KTtcblxuXHQkKCBcIi53cHItbXVsdGlwbGUtc2VsZWN0IC53cHItbGlzdC1oZWFkZXJcIiApLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0JChlLnRhcmdldCkuY2xvc2VzdCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1saXN0JykudG9nZ2xlQ2xhc3MoJ29wZW4nKTtcblx0fSk7XG5cblx0JCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1jaGVja2JveCcpLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0Y29uc3QgY2hlY2tib3ggPSAkKHRoaXMpLmZpbmQoJ2lucHV0Jyk7XG5cdFx0Y29uc3QgaXNfY2hlY2tlZCA9IGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnKSAhPT0gdW5kZWZpbmVkO1xuXHRcdGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdGNvbnN0IHN1Yl9jaGVja2JveGVzID0gJChjaGVja2JveCkuY2xvc2VzdCgnLndwci1saXN0JykuZmluZCgnLndwci1saXN0LWJvZHkgaW5wdXRbdHlwZT1cImNoZWNrYm94XCJdJyk7XG5cdFx0aWYoY2hlY2tib3guaGFzQ2xhc3MoJ3dwci1tYWluLWNoZWNrYm94JykpIHtcblx0XHRcdCQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdFx0fSk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGNvbnN0IG1haW5fY2hlY2tib3ggPSAkKGNoZWNrYm94KS5jbG9zZXN0KCcud3ByLWxpc3QnKS5maW5kKCcud3ByLW1haW4tY2hlY2tib3gnKTtcblxuXHRcdGNvbnN0IHN1Yl9jaGVja2VkID0gICQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRpZigkKGNoZWNrYm94KS5hdHRyKCdjaGVja2VkJykgPT09IHVuZGVmaW5lZCkge1xuXHRcdFx0XHRyZXR1cm4gO1xuXHRcdFx0fVxuXHRcdFx0cmV0dXJuIGNoZWNrYm94O1xuXHRcdH0pO1xuXHRcdG1haW5fY2hlY2tib3guYXR0cignY2hlY2tlZCcsIHN1Yl9jaGVja2VkLmxlbmd0aCA9PT0gc3ViX2NoZWNrYm94ZXMubGVuZ3RoID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHR9KTtcblxuXHRpZiAoICQoICcud3ByLW1haW4tY2hlY2tib3gnICkubGVuZ3RoID4gMCApIHtcblx0XHQkKCcud3ByLW1haW4tY2hlY2tib3gnKS5lYWNoKChjaGVja2JveF9rZXksIGNoZWNrYm94KSA9PiB7XG5cdFx0XHRsZXQgcGFyZW50X2xpc3QgPSAkKGNoZWNrYm94KS5wYXJlbnRzKCcud3ByLWxpc3QnKTtcblx0XHRcdGxldCBub3RfY2hlY2tlZCA9IHBhcmVudF9saXN0LmZpbmQoICcud3ByLWxpc3QtYm9keSBpbnB1dFt0eXBlPWNoZWNrYm94XTpub3QoOmNoZWNrZWQpJyApLmxlbmd0aDtcblx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBub3RfY2hlY2tlZCA8PSAwID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHRcdH0pO1xuXHR9XG5cblx0bGV0IGNoZWNrQm94Q291bnRlciA9IHtcblx0XHRjaGVja2VkOiB7fSxcblx0XHR0b3RhbDoge31cblx0fTtcblx0JCgnLndwci1maWVsZC0tY2F0ZWdvcml6ZWRtdWx0aXNlbGVjdCAud3ByLWxpc3QnKS5lYWNoKGZ1bmN0aW9uICgpIHtcblx0XHQvLyBHZXQgdGhlIElEIG9mIHRoZSBjdXJyZW50IGVsZW1lbnRcblx0XHRsZXQgaWQgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG5cdFx0aWYgKGlkKSB7XG5cdFx0XHRjaGVja0JveENvdW50ZXIuY2hlY2tlZFtpZF0gPSAkKGAjJHtpZH0gaW5wdXRbdHlwZT0nY2hlY2tib3gnXTpjaGVja2VkYCkubGVuZ3RoO1xuXHRcdFx0Y2hlY2tCb3hDb3VudGVyLnRvdGFsW2lkXSA9ICQoYCMke2lkfSBpbnB1dFt0eXBlPSdjaGVja2JveCddOm5vdCgud3ByLW1haW4tY2hlY2tib3gpYCkubGVuZ3RoO1xuXHRcdFx0Ly8gVXBkYXRlIHRoZSBjb3VudGVyIHRleHRcblx0XHRcdCQoYCMke2lkfSAud3ByLWJhZGdlLWNvdW50ZXIgc3BhbmApLnRleHQoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdKTtcblx0XHRcdC8vIFNob3cgb3IgaGlkZSB0aGUgY291bnRlciBiYWRnZSBiYXNlZCBvbiB0aGUgY291bnRcblx0XHRcdCQoYCMke2lkfSAud3ByLWJhZGdlLWNvdW50ZXJgKS50b2dnbGUoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID4gMCk7XG5cblx0XHRcdC8vIENoZWNrIHRoZSBzZWxlY3QgYWxsIG9wdGlvbiBpZiBhbGwgZXhjbHVzaW9ucyBhcmUgY2hlY2tlZCBpbiBhIHNlY3Rpb24uXG5cdFx0XHRpZiAoY2hlY2tCb3hDb3VudGVyLmNoZWNrZWRbaWRdID09PSBjaGVja0JveENvdW50ZXIudG90YWxbaWRdKSB7XG5cdFx0XHRcdCQoYCMke2lkfSAud3ByLW1haW4tY2hlY2tib3hgKS5hdHRyKCdjaGVja2VkJywgdHJ1ZSk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblxuXHQvKipcblx0ICogRGVsYXkgSlMgRXhlY3V0aW9uIFNhZmUgTW9kZSBGaWVsZFxuXHQgKi9cblx0dmFyICRkamVfc2FmZV9tb2RlX2NoZWNrYm94ID0gJCgnI2RlbGF5X2pzX2V4ZWN1dGlvbl9zYWZlX21vZGUnKTtcblx0JCgnI2RlbGF5X2pzJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHRpZiAoJCh0aGlzKS5pcygnOm5vdCg6Y2hlY2tlZCknKSAmJiAkZGplX3NhZmVfbW9kZV9jaGVja2JveC5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0JGRqZV9zYWZlX21vZGVfY2hlY2tib3gudHJpZ2dlcignY2xpY2snKTtcblx0XHR9XG5cdH0pO1xuXG5cdGxldCBzdGFja2VkX3NlbGVjdCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAncm9ja2V0X3N0YWNrZWRfc2VsZWN0JyApO1xuXHRpZiAoIHN0YWNrZWRfc2VsZWN0ICkge1xuXHRcdHN0YWNrZWRfc2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2N1c3RvbS1zZWxlY3QtY2hhbmdlJyxmdW5jdGlvbihldmVudCl7XG5cblx0XHRcdGxldCBzZWxlY3RlZF9vcHRpb24gPSAkKCBldmVudC5kZXRhaWwuc2VsZWN0ZWRPcHRpb24gKTtcblxuXHRcdFx0bGV0IG5hbWUgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgnbmFtZScpO1xuXG5cdFx0XHRsZXQgc2F2aW5nID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3NhdmluZycpO1xuXHRcdFx0bGV0IHJlZ3VsYXJfcHJpY2UgID0gc2VsZWN0ZWRfb3B0aW9uLmRhdGEoJ3JlZ3VsYXItcHJpY2UnKTtcblx0XHRcdGxldCBwcmljZSAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgncHJpY2UnKTtcblx0XHRcdGxldCB1cmwgICAgPSBzZWxlY3RlZF9vcHRpb24uZGF0YSgndXJsJyk7XG5cblx0XHRcdGxldCBwYXJlbnRfaXRlbSA9ICQodGhpcykucGFyZW50cyggJy53cHItdXBncmFkZS1pdGVtJyApO1xuXG5cdFx0XHRpZiAoIHNhdmluZyApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS1zYXZpbmcgc3BhbicgKS5odG1sKCBzYXZpbmcgKTtcblx0XHRcdH1cblx0XHRcdGlmICggbmFtZSApIHtcblx0XHRcdFx0cGFyZW50X2l0ZW0uZmluZCggJy53cHItdXBncmFkZS10aXRsZScgKS5odG1sKCBuYW1lICk7XG5cdFx0XHR9XG5cdFx0XHRpZiAoIHJlZ3VsYXJfcHJpY2UgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtcHJpY2UtcmVndWxhciBzcGFuJyApLmh0bWwoIHJlZ3VsYXJfcHJpY2UgKTtcblx0XHRcdH1cblx0XHRcdGlmICggcHJpY2UgKSB7XG5cdFx0XHRcdHBhcmVudF9pdGVtLmZpbmQoICcud3ByLXVwZ3JhZGUtcHJpY2UtdmFsdWUnICkuaHRtbCggcHJpY2UgKTtcblx0XHRcdH1cblx0XHRcdGlmICggdXJsICkge1xuXHRcdFx0XHRwYXJlbnRfaXRlbS5maW5kKCAnLndwci11cGdyYWRlLWxpbmsnICkuYXR0ciggJ2hyZWYnLCB1cmwgKTtcblx0XHRcdH1cblxuXHRcdH0gKTtcblx0fVxufSk7XG4iLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG5cblxuXHQvKioqXG5cdCogRGFzaGJvYXJkIG5vdGljZVxuXHQqKiovXG5cblx0dmFyICRub3RpY2UgPSAkKCcud3ByLW5vdGljZScpO1xuXHR2YXIgJG5vdGljZUNsb3NlID0gJCgnI3dwci1jb25ncmF0dWxhdGlvbnMtbm90aWNlJyk7XG5cblx0JG5vdGljZUNsb3NlLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlRGFzaGJvYXJkTm90aWNlKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJDbG9zZURhc2hib2FyZE5vdGljZSgpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC50bygkbm90aWNlLCAxLCB7YXV0b0FscGhhOjAsIHg6NDAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLnRvKCRub3RpY2UsIDAuNiwge2hlaWdodDogMCwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNCcpXG5cdFx0ICAuc2V0KCRub3RpY2UsIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvKipcblx0ICogUm9ja2V0IEFuYWx5dGljcyBub3RpY2UgaW5mbyBjb2xsZWN0XG5cdCAqL1xuXHQkKCAnLnJvY2tldC1hbmFseXRpY3MtZGF0YS1jb250YWluZXInICkuaGlkZSgpO1xuXHQkKCAnLnJvY2tldC1wcmV2aWV3LWFuYWx5dGljcy1kYXRhJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHQkKHRoaXMpLnBhcmVudCgpLm5leHQoICcucm9ja2V0LWFuYWx5dGljcy1kYXRhLWNvbnRhaW5lcicgKS50b2dnbGUoKTtcblx0fSApO1xuXG5cdC8qKipcblx0KiBIaWRlIC8gc2hvdyBSb2NrZXQgYWRkb24gdGFicy5cblx0KioqL1xuXG5cdCQoICcud3ByLXRvZ2dsZS1idXR0b24nICkuZWFjaCggZnVuY3Rpb24oKSB7XG5cdFx0dmFyICRidXR0b24gICA9ICQoIHRoaXMgKTtcblx0XHR2YXIgJGNoZWNrYm94ID0gJGJ1dHRvbi5jbG9zZXN0KCAnLndwci1maWVsZHNDb250YWluZXItZmllbGRzZXQnICkuZmluZCggJy53cHItcmFkaW8gOmNoZWNrYm94JyApO1xuXHRcdHZhciAkbWVudUl0ZW0gPSAkKCAnW2hyZWY9XCInICsgJGJ1dHRvbi5hdHRyKCAnaHJlZicgKSArICdcIl0ud3ByLW1lbnVJdGVtJyApO1xuXG5cdFx0JGNoZWNrYm94Lm9uKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcblx0XHRcdGlmICggJGNoZWNrYm94LmlzKCAnOmNoZWNrZWQnICkgKSB7XG5cdFx0XHRcdCRtZW51SXRlbS5jc3MoICdkaXNwbGF5JywgJ2Jsb2NrJyApO1xuXHRcdFx0XHQkYnV0dG9uLmNzcyggJ2Rpc3BsYXknLCAnaW5saW5lLWJsb2NrJyApO1xuXHRcdFx0fSBlbHNle1xuXHRcdFx0XHQkbWVudUl0ZW0uY3NzKCAnZGlzcGxheScsICdub25lJyApO1xuXHRcdFx0XHQkYnV0dG9uLmNzcyggJ2Rpc3BsYXknLCAnbm9uZScgKTtcblx0XHRcdH1cblx0XHR9ICkudHJpZ2dlciggJ2NoYW5nZScgKTtcblx0fSApO1xuXG5cdC8qKipcblx0KiBIZWxwIEJ1dHRvbiBUcmFja2luZ1xuXHQqKiovXG5cdFxuXHQvLyBUcmFjayBjbGlja3Mgb24gdmFyaW91cyBoZWxwIGVsZW1lbnRzIHdpdGggZGF0YSBhdHRyaWJ1dGVzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICdbZGF0YS13cHJfdHJhY2tfaGVscF0nLCBmdW5jdGlvbihlKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgJGVsID0gJCh0aGlzKTtcblx0XHRcdHZhciBidXR0b24gPSAkZWwuZGF0YSgnd3ByX3RyYWNrX2hlbHAnKTtcblx0XHRcdHZhciBjb250ZXh0ID0gJGVsLmRhdGEoJ3dwcl90cmFja19jb250ZXh0JykgfHwgJyc7XG5cdFx0XHRcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oYnV0dG9uLCBjb250ZXh0KTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIHNwZWNpZmljIGhlbHAgcmVzb3VyY2UgY2xpY2tzIHdpdGggZXhwbGljaXQgc2VsZWN0b3JzXG5cdCQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud2lzdGlhX2VtYmVkJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR2YXIgdGl0bGUgPSAkKHRoaXMpLnRleHQoKSB8fCAnR2V0dGluZyBTdGFydGVkIFZpZGVvJztcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24odGl0bGUsICdHZXR0aW5nIFN0YXJ0ZWQnKTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRyYWNrIEZBUSBsaW5rcyBcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJ2FbZGF0YS1iZWFjb24tYXJ0aWNsZV0nLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHZhciBocmVmID0gJCh0aGlzKS5hdHRyKCdocmVmJyk7XG5cdFx0XHR2YXIgdGV4dCA9ICQodGhpcykudGV4dCgpO1xuXHRcdFx0XG5cdFx0XHQvLyBDaGVjayBpZiBpdCdzIGluIEZBUSBzZWN0aW9uIG9yIHNpZGViYXIgZG9jdW1lbnRhdGlvblxuXHRcdFx0aWYgKCQodGhpcykuY2xvc2VzdCgnLndwci1maWVsZHNDb250YWluZXItZmllbGRzZXQnKS5wcmV2KCcud3ByLW9wdGlvbkhlYWRlcicpLmZpbmQoJy53cHItdGl0bGUyJykudGV4dCgpLmluY2x1ZGVzKCdGcmVxdWVudGx5IEFza2VkIFF1ZXN0aW9ucycpKSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0ZBUSAtICcgKyB0ZXh0LCAnRGFzaGJvYXJkJyk7XG5cdFx0XHR9IGVsc2UgaWYgKCQodGhpcykuY2xvc2VzdCgnLndwci1kb2N1bWVudGF0aW9uJykubGVuZ3RoID4gMCkge1xuXHRcdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdEb2N1bWVudGF0aW9uJywgJ1NpZGViYXInKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ0RvY3VtZW50YXRpb24gTGluaycsICdHZW5lcmFsJyk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9KTtcblx0XG5cdC8vIFRyYWNrIFwiSG93IHRvIG1lYXN1cmUgbG9hZGluZyB0aW1lXCIgbGlua1xuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnYVtocmVmKj1cImhvdy10by10ZXN0LXdvcmRwcmVzcy1zaXRlLXBlcmZvcm1hbmNlXCJdJywgZnVuY3Rpb24oKSB7XG5cdFx0aWYgKHR5cGVvZiB3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uID09PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHR3aW5kb3cud3ByVHJhY2tIZWxwQnV0dG9uKCdMb2FkaW5nIFRpbWUgR3VpZGUnLCAnU2lkZWJhcicpO1xuXHRcdH1cblx0fSk7XG5cblx0Ly8gVHJhY2sgXCJOZWVkIGhlbHA/XCIgbGlua3MgKGV4aXN0aW5nIGhlbHAgYnV0dG9ucylcblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItaW5mb0FjdGlvbi0taGVscDpub3QoW2RhdGEtYmVhY29uLWlkXSknLCBmdW5jdGlvbigpIHtcblx0XHRpZiAodHlwZW9mIHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24gPT09ICdmdW5jdGlvbicpIHtcblx0XHRcdHdpbmRvdy53cHJUcmFja0hlbHBCdXR0b24oJ05lZWQgSGVscCcsICdHZW5lcmFsJyk7XG5cdFx0fVxuXHR9KTtcblxuXG5cdC8qKipcblx0KiBTaG93IHBvcGluIGFuYWx5dGljc1xuXHQqKiovXG5cblx0dmFyICR3cHJBbmFseXRpY3NQb3BpbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzJyksXG5cdFx0JHdwclBvcGluT3ZlcmxheSA9ICQoJy53cHItUG9waW4tb3ZlcmxheScpLFxuXHRcdCR3cHJBbmFseXRpY3NDbG9zZVBvcGluID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MtY2xvc2UnKSxcblx0XHQkd3ByQW5hbHl0aWNzUG9waW5CdXR0b24gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcyAud3ByLWJ1dHRvbicpLFxuXHRcdCR3cHJBbmFseXRpY3NPcGVuUG9waW4gPSAkKCcud3ByLWpzLXBvcGluJylcblx0O1xuXG5cdCR3cHJBbmFseXRpY3NPcGVuUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJPcGVuQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByQW5hbHl0aWNzQ2xvc2VQb3Bpbi5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG5cdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHdwckNsb3NlQW5hbHl0aWNzKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByQW5hbHl0aWNzUG9waW5CdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJBY3RpdmF0ZUFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByT3BlbkFuYWx5dGljcygpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC5zZXQoJHdwckFuYWx5dGljc1BvcGluLCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdCAgLnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdCAgLmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MH0se2F1dG9BbHBoYToxLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC5mcm9tVG8oJHdwckFuYWx5dGljc1BvcGluLCAwLjYsIHthdXRvQWxwaGE6MCwgbWFyZ2luVG9wOiAtMjR9LCB7YXV0b0FscGhhOjEsIG1hcmdpblRvcDowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckNsb3NlQW5hbHl0aWNzKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLmZyb21Ubygkd3ByQW5hbHl0aWNzUG9waW4sIDAuNiwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6IDB9LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDotMjQsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdCAgLmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MX0se2F1dG9BbHBoYTowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdCAgLnNldCgkd3ByQW5hbHl0aWNzUG9waW4sIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQgIC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdGZ1bmN0aW9uIHdwckFjdGl2YXRlQW5hbHl0aWNzKCl7XG5cdFx0d3ByQ2xvc2VBbmFseXRpY3MoKTtcblx0XHQkKCcjYW5hbHl0aWNzX2VuYWJsZWQnKS5wcm9wKCdjaGVja2VkJywgdHJ1ZSk7XG5cdFx0JCgnI2FuYWx5dGljc19lbmFibGVkJykudHJpZ2dlcignY2hhbmdlJyk7XG5cdH1cblxuXHQvLyBEaXNwbGF5IENUQSB3aXRoaW4gdGhlIHBvcGluIGBXaGF0IGluZm8gd2lsbCB3ZSBjb2xsZWN0P2Bcblx0JCgnI2FuYWx5dGljc19lbmFibGVkJykub24oJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcblx0XHQkKCcud3ByLXJvY2tldC1hbmFseXRpY3MtY3RhJykudG9nZ2xlQ2xhc3MoJ3dwci1pc0hpZGRlbicpO1xuXHR9KTtcblxuXHQvKioqXG5cdCogU2hvdyBwb3BpbiB1cGdyYWRlXG5cdCoqKi9cblxuXHR2YXIgJHdwclVwZ3JhZGVQb3BpbiA9ICQoJy53cHItUG9waW4tVXBncmFkZScpLFxuXHQkd3ByVXBncmFkZUNsb3NlUG9waW4gPSAkKCcud3ByLVBvcGluLVVwZ3JhZGUtY2xvc2UnKSxcblx0JHdwclVwZ3JhZGVPcGVuUG9waW4gPSAkKCcud3ByLXBvcGluLXVwZ3JhZGUtdG9nZ2xlJyk7XG5cblx0JHdwclVwZ3JhZGVPcGVuUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJPcGVuVXBncmFkZVBvcGluKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHQkd3ByVXBncmFkZUNsb3NlUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VVcGdyYWRlUG9waW4oKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwck9wZW5VcGdyYWRlUG9waW4oKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpO1xuXG5cdFx0dlRMLnNldCgkd3ByVXBncmFkZVBvcGluLCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdFx0LnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidibG9jayd9KVxuXHRcdFx0LmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MH0se2F1dG9BbHBoYToxLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHRcdC5mcm9tVG8oJHdwclVwZ3JhZGVQb3BpbiwgMC42LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDogLTI0fSwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJDbG9zZVVwZ3JhZGVQb3Bpbigpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKCk7XG5cblx0XHR2VEwuZnJvbVRvKCR3cHJVcGdyYWRlUG9waW4sIDAuNiwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6IDB9LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDotMjQsIGVhc2U6UG93ZXI0LmVhc2VPdXR9KVxuXHRcdFx0LmZyb21Ubygkd3ByUG9waW5PdmVybGF5LCAwLjYsIHthdXRvQWxwaGE6MX0se2F1dG9BbHBoYTowLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSwgJz0tLjUnKVxuXHRcdFx0LnNldCgkd3ByVXBncmFkZVBvcGluLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0XHQuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHQvKioqXG5cdCogU2lkZWJhciBvbi9vZmZcblx0KioqL1xuXHR2YXIgJHdwclNpZGViYXIgICAgPSAkKCAnLndwci1TaWRlYmFyJyApO1xuXHR2YXIgJHdwckJ1dHRvblRpcHMgPSAkKCcud3ByLWpzLXRpcHMnKTtcblxuXHQkd3ByQnV0dG9uVGlwcy5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByRGV0ZWN0VGlwcygkKHRoaXMpKTtcblx0fSk7XG5cblx0ZnVuY3Rpb24gd3ByRGV0ZWN0VGlwcyhhRWxlbSl7XG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JHdwclNpZGViYXIuY3NzKCdkaXNwbGF5JywnYmxvY2snKTtcblx0XHRcdGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvbicgKTtcblx0XHR9XG5cdFx0ZWxzZXtcblx0XHRcdCR3cHJTaWRlYmFyLmNzcygnZGlzcGxheScsJ25vbmUnKTtcblx0XHRcdGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvZmYnICk7XG5cdFx0fVxuXHR9XG5cblxuXG5cdC8qKipcblx0KiBEZXRlY3QgQWRibG9ja1xuXHQqKiovXG5cblx0aWYoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ0xLZ09jQ1Jwd21BaicpKXtcblx0XHQkKCcud3ByLWFkYmxvY2snKS5jc3MoJ2Rpc3BsYXknLCAnbm9uZScpO1xuXHR9IGVsc2Uge1xuXHRcdCQoJy53cHItYWRibG9jaycpLmNzcygnZGlzcGxheScsICdibG9jaycpO1xuXHR9XG5cblx0dmFyICRhZGJsb2NrID0gJCgnLndwci1hZGJsb2NrJyk7XG5cdHZhciAkYWRibG9ja0Nsb3NlID0gJCgnLndwci1hZGJsb2NrLWNsb3NlJyk7XG5cblx0JGFkYmxvY2tDbG9zZS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHR3cHJDbG9zZUFkYmxvY2tOb3RpY2UoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckNsb3NlQWRibG9ja05vdGljZSgpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC50bygkYWRibG9jaywgMSwge2F1dG9BbHBoYTowLCB4OjQwLCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC50bygkYWRibG9jaywgMC40LCB7aGVpZ2h0OiAwLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS40Jylcblx0XHQgIC5zZXQoJGFkYmxvY2ssIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxufSk7XG4iLCJkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsIGZ1bmN0aW9uICgpIHtcblxuICAgIHZhciAkcGFnZU1hbmFnZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiLndwci1Db250ZW50XCIpO1xuICAgIGlmKCRwYWdlTWFuYWdlcil7XG4gICAgICAgIG5ldyBQYWdlTWFuYWdlcigkcGFnZU1hbmFnZXIpO1xuICAgIH1cblxufSk7XG5cblxuLyotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSpcXFxuXHRcdENMQVNTIFBBR0VNQU5BR0VSXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXG4vKipcbiAqIE1hbmFnZXMgdGhlIGRpc3BsYXkgb2YgcGFnZXMgLyBzZWN0aW9uIGZvciBXUCBSb2NrZXQgcGx1Z2luXG4gKlxuICogUHVibGljIG1ldGhvZCA6XG4gICAgIGRldGVjdElEIC0gRGV0ZWN0IElEIHdpdGggaGFzaFxuICAgICBnZXRCb2R5VG9wIC0gR2V0IGJvZHkgdG9wIHBvc2l0aW9uXG5cdCBjaGFuZ2UgLSBEaXNwbGF5cyB0aGUgY29ycmVzcG9uZGluZyBwYWdlXG4gKlxuICovXG5cbmZ1bmN0aW9uIFBhZ2VNYW5hZ2VyKGFFbGVtKSB7XG5cbiAgICB2YXIgcmVmVGhpcyA9IHRoaXM7XG5cbiAgICB0aGlzLiRib2R5ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1ib2R5Jyk7XG4gICAgdGhpcy4kbWVudUl0ZW1zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1tZW51SXRlbScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudCA+IGZvcm0gPiAjd3ByLW9wdGlvbnMtc3VibWl0Jyk7XG4gICAgdGhpcy4kcGFnZXMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcud3ByLVBhZ2UnKTtcbiAgICB0aGlzLiRzaWRlYmFyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1TaWRlYmFyJyk7XG4gICAgdGhpcy4kY29udGVudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItQ29udGVudCcpO1xuICAgIHRoaXMuJHRpcHMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQtdGlwcycpO1xuICAgIHRoaXMuJGxpbmtzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1ib2R5IGEnKTtcbiAgICB0aGlzLiRtZW51SXRlbSA9IG51bGw7XG4gICAgdGhpcy4kcGFnZSA9IG51bGw7XG4gICAgdGhpcy5wYWdlSWQgPSBudWxsO1xuICAgIHRoaXMuYm9keVRvcCA9IDA7XG4gICAgdGhpcy5idXR0b25UZXh0ID0gdGhpcy4kc3VibWl0QnV0dG9uLnZhbHVlO1xuXG4gICAgcmVmVGhpcy5nZXRCb2R5VG9wKCk7XG5cbiAgICAvLyBJZiB1cmwgcGFnZSBjaGFuZ2VcbiAgICB3aW5kb3cub25oYXNoY2hhbmdlID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIHJlZlRoaXMuZGV0ZWN0SUQoKTtcbiAgICB9XG5cbiAgICAvLyBJZiBoYXNoIGFscmVhZHkgZXhpc3QgKGFmdGVyIHJlZnJlc2ggcGFnZSBmb3IgZXhhbXBsZSlcbiAgICBpZih3aW5kb3cubG9jYXRpb24uaGFzaCl7XG4gICAgICAgIHRoaXMuYm9keVRvcCA9IDA7XG4gICAgICAgIHRoaXMuZGV0ZWN0SUQoKTtcbiAgICB9XG4gICAgZWxzZXtcbiAgICAgICAgdmFyIHNlc3Npb24gPSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLWhhc2gnKTtcbiAgICAgICAgdGhpcy5ib2R5VG9wID0gMDtcblxuICAgICAgICBpZihzZXNzaW9uKXtcbiAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gc2Vzc2lvbjtcbiAgICAgICAgICAgIHRoaXMuZGV0ZWN0SUQoKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNle1xuICAgICAgICAgICAgdGhpcy4kbWVudUl0ZW1zWzBdLmNsYXNzTGlzdC5hZGQoJ2lzQWN0aXZlJyk7XG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCAnZGFzaGJvYXJkJyk7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24uaGFzaCA9ICcjZGFzaGJvYXJkJztcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIENsaWNrIGxpbmsgc2FtZSBoYXNoXG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRsaW5rcy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0aGlzLiRsaW5rc1tpXS5vbmNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICByZWZUaGlzLmdldEJvZHlUb3AoKTtcbiAgICAgICAgICAgIHZhciBocmVmU3BsaXQgPSB0aGlzLmhyZWYuc3BsaXQoJyMnKVsxXTtcbiAgICAgICAgICAgIGlmKGhyZWZTcGxpdCA9PSByZWZUaGlzLnBhZ2VJZCAmJiBocmVmU3BsaXQgIT0gdW5kZWZpbmVkKXtcbiAgICAgICAgICAgICAgICByZWZUaGlzLmRldGVjdElEKCk7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfVxuICAgICAgICB9O1xuICAgIH1cblxuICAgIC8vIENsaWNrIGxpbmtzIG5vdCBXUCByb2NrZXQgdG8gcmVzZXQgaGFzaFxuICAgIHZhciAkb3RoZXJsaW5rcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJyNhZG1pbm1lbnVtYWluIGEsICN3cGFkbWluYmFyIGEnKTtcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8ICRvdGhlcmxpbmtzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICRvdGhlcmxpbmtzW2ldLm9uY2xpY2sgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd3cHItaGFzaCcsICcnKTtcbiAgICAgICAgfTtcbiAgICB9XG5cbn1cblxuXG4vKlxuKiBQYWdlIGRldGVjdCBJRFxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5kZXRlY3RJRCA9IGZ1bmN0aW9uKCkge1xuICAgIHRoaXMucGFnZUlkID0gd2luZG93LmxvY2F0aW9uLmhhc2guc3BsaXQoJyMnKVsxXTtcbiAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCB0aGlzLnBhZ2VJZCk7XG5cbiAgICB0aGlzLiRwYWdlID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1QYWdlIycgKyB0aGlzLnBhZ2VJZCk7XG4gICAgdGhpcy4kbWVudUl0ZW0gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnd3ByLW5hdi0nICsgdGhpcy5wYWdlSWQpO1xuXG4gICAgdGhpcy5jaGFuZ2UoKTtcbn1cblxuXG5cbi8qXG4qIEdldCBib2R5IHRvcCBwb3NpdGlvblxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5nZXRCb2R5VG9wID0gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGJvZHlQb3MgPSB0aGlzLiRib2R5LmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgIHRoaXMuYm9keVRvcCA9IGJvZHlQb3MudG9wICsgd2luZG93LnBhZ2VZT2Zmc2V0IC0gNDc7IC8vICN3cGFkbWluYmFyICsgcGFkZGluZy10b3AgLndwci13cmFwIC0gMSAtIDQ3XG59XG5cblxuXG4vKlxuKiBQYWdlIGNoYW5nZVxuKi9cblBhZ2VNYW5hZ2VyLnByb3RvdHlwZS5jaGFuZ2UgPSBmdW5jdGlvbigpIHtcblxuICAgIHZhciByZWZUaGlzID0gdGhpcztcbiAgICBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuc2Nyb2xsVG9wID0gcmVmVGhpcy5ib2R5VG9wO1xuXG4gICAgLy8gSGlkZSBvdGhlciBwYWdlc1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kcGFnZXMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kcGFnZXNbaV0uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLiRtZW51SXRlbXMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kbWVudUl0ZW1zW2ldLmNsYXNzTGlzdC5yZW1vdmUoJ2lzQWN0aXZlJyk7XG4gICAgfVxuXG4gICAgLy8gU2hvdyBjdXJyZW50IGRlZmF1bHQgcGFnZVxuICAgIHRoaXMuJHBhZ2Uuc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG4gICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuXG4gICAgaWYgKCBudWxsID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInICkgKSB7XG4gICAgICAgIGxvY2FsU3RvcmFnZS5zZXRJdGVtKCAnd3ByLXNob3ctc2lkZWJhcicsICdvbicgKTtcbiAgICB9XG5cbiAgICBpZiAoICdvbicgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd3cHItc2hvdy1zaWRlYmFyJykgKSB7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG4gICAgfSBlbHNlIGlmICggJ29mZicgPT09IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd3cHItc2hvdy1zaWRlYmFyJykgKSB7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3dwci1qcy10aXBzJykucmVtb3ZlQXR0cmlidXRlKCAnY2hlY2tlZCcgKTtcbiAgICB9XG5cbiAgICB0aGlzLiR0aXBzLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIHRoaXMuJG1lbnVJdGVtLmNsYXNzTGlzdC5hZGQoJ2lzQWN0aXZlJyk7XG4gICAgdGhpcy4kc3VibWl0QnV0dG9uLnZhbHVlID0gdGhpcy5idXR0b25UZXh0O1xuICAgIHRoaXMuJGNvbnRlbnQuY2xhc3NMaXN0LmFkZCgnaXNOb3RGdWxsJyk7XG5cblxuICAgIC8vIEV4Y2VwdGlvbiBmb3IgZGFzaGJvYXJkXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXNoYm9hcmRcIil7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kY29udGVudC5jbGFzc0xpc3QucmVtb3ZlKCdpc05vdEZ1bGwnKTtcbiAgICB9XG5cbiAgICAvLyBFeGNlcHRpb24gZm9yIGFkZG9uc1xuICAgIGlmKHRoaXMucGFnZUlkID09IFwiYWRkb25zXCIpe1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICAvLyBFeGNlcHRpb24gZm9yIGRhdGFiYXNlXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJkYXRhYmFzZVwiKXtcbiAgICAgICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgLy8gRXhjZXB0aW9uIGZvciB0b29scyBhbmQgYWRkb25zXG4gICAgaWYodGhpcy5wYWdlSWQgPT0gXCJ0b29sc1wiIHx8IHRoaXMucGFnZUlkID09IFwiYWRkb25zXCIpe1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICBpZiAodGhpcy5wYWdlSWQgPT0gXCJpbWFnaWZ5XCIpIHtcbiAgICAgICAgdGhpcy4kc2lkZWJhci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiR0aXBzLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cblxuICAgIGlmICh0aGlzLnBhZ2VJZCA9PSBcInR1dG9yaWFsc1wiKSB7XG4gICAgICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cblxuXHRpZiAodGhpcy5wYWdlSWQgPT0gXCJwbHVnaW5zXCIpIHtcblx0XHR0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0fVxufTtcbiIsIi8qZXNsaW50LWVudiBlczYqL1xuKCAoIGRvY3VtZW50LCB3aW5kb3cgKSA9PiB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHRkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLndwci1yb2NrZXRjZG4tb3BlbicgKS5mb3JFYWNoKCAoIGVsICkgPT4ge1xuXHRcdFx0ZWwuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCBlICkgPT4ge1xuXHRcdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0XHR9ICk7XG5cdFx0fSApO1xuXG5cdFx0bWF5YmVPcGVuTW9kYWwoKTtcblxuXHRcdE1pY3JvTW9kYWwuaW5pdCgge1xuXHRcdFx0ZGlzYWJsZVNjcm9sbDogdHJ1ZVxuXHRcdH0gKTtcblxuXHRcdGNvbnN0IGlmcmFtZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyb2NrZXRjZG4taWZyYW1lJyk7XG5cdFx0Y29uc3QgbG9hZGVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwci1yb2NrZXRjZG4tbW9kYWwtbG9hZGVyJyk7XG5cdFx0XG5cdFx0aWZyYW1lLmFkZEV2ZW50TGlzdGVuZXIoJ2xvYWQnLCBmdW5jdGlvbigpIHtcblx0XHRcdGxvYWRlci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuXHRcdH0pO1xuXHR9ICk7XG5cblx0d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdsb2FkJywgKCkgPT4ge1xuXHRcdGxldCBvcGVuQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLW9wZW4tY3RhJyApLFxuXHRcdFx0Y2xvc2VDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY2xvc2UtY3RhJyApLFxuXHRcdFx0c21hbGxDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhLXNtYWxsJyApLFxuXHRcdFx0YmlnQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YScgKTtcblxuXHRcdGlmICggbnVsbCAhPT0gb3BlbkNUQSAmJiBudWxsICE9PSBzbWFsbENUQSAmJiBudWxsICE9PSBiaWdDVEEgKSB7XG5cdFx0XHRvcGVuQ1RBLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0XHRcdHNtYWxsQ1RBLmNsYXNzTGlzdC5hZGQoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdGJpZ0NUQS5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWlzSGlkZGVuJyApO1xuXG5cdFx0XHRcdHNlbmRIVFRQUmVxdWVzdCggZ2V0UG9zdERhdGEoICdiaWcnICkgKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHRpZiAoIG51bGwgIT09IGNsb3NlQ1RBICYmIG51bGwgIT09IHNtYWxsQ1RBICYmIG51bGwgIT09IGJpZ0NUQSApIHtcblx0XHRcdGNsb3NlQ1RBLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0XHRcdHNtYWxsQ1RBLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdGJpZ0NUQS5jbGFzc0xpc3QuYWRkKCAnd3ByLWlzSGlkZGVuJyApO1xuXG5cdFx0XHRcdHNlbmRIVFRQUmVxdWVzdCggZ2V0UG9zdERhdGEoICdzbWFsbCcgKSApO1xuXHRcdFx0fSApO1xuXHRcdH1cblxuXHRcdGZ1bmN0aW9uIGdldFBvc3REYXRhKCBzdGF0dXMgKSB7XG5cdFx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj10b2dnbGVfcm9ja2V0Y2RuX2N0YSc7XG5cdFx0XHRwb3N0RGF0YSArPSAnJnN0YXR1cz0nICsgc3RhdHVzO1xuXHRcdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdFx0cmV0dXJuIHBvc3REYXRhO1xuXHRcdH1cblx0fSApO1xuXG5cdHdpbmRvdy5vbm1lc3NhZ2UgPSAoIGUgKSA9PiB7XG5cdFx0Y29uc3QgaWZyYW1lVVJMID0gcm9ja2V0X2FqYXhfZGF0YS5vcmlnaW5fdXJsO1xuXG5cdFx0aWYgKCBlLm9yaWdpbiAhPT0gaWZyYW1lVVJMICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHNldENETkZyYW1lSGVpZ2h0KCBlLmRhdGEgKTtcblx0XHRjbG9zZU1vZGFsKCBlLmRhdGEgKTtcblx0XHR0b2tlbkhhbmRsZXIoIGUuZGF0YSwgaWZyYW1lVVJMICk7XG5cdFx0cHJvY2Vzc1N0YXR1cyggZS5kYXRhICk7XG5cdFx0ZW5hYmxlQ0ROKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdGRpc2FibGVDRE4oIGUuZGF0YSwgaWZyYW1lVVJMICk7XG5cdFx0dmFsaWRhdGVUb2tlbkFuZENOQU1FKCBlLmRhdGEgKTtcblx0fTtcblxuXHRmdW5jdGlvbiBtYXliZU9wZW5Nb2RhbCgpIHtcblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc3RhdHVzJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cblx0XHRcdFx0aWYgKCB0cnVlID09PSByZXNwb25zZVR4dC5zdWNjZXNzICkge1xuXHRcdFx0XHRcdE1pY3JvTW9kYWwuc2hvdyggJ3dwci1yb2NrZXRjZG4tbW9kYWwnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gY2xvc2VNb2RhbCggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2NkbkZyYW1lQ2xvc2UnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0TWljcm9Nb2RhbC5jbG9zZSggJ3dwci1yb2NrZXRjZG4tbW9kYWwnICk7XG5cblx0XHRsZXQgcGFnZXMgPSBbICdpZnJhbWUtcGF5bWVudC1zdWNjZXNzJywgJ2lmcmFtZS11bnN1YnNjcmliZS1zdWNjZXNzJyBdO1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5fcGFnZV9tZXNzYWdlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGlmICggcGFnZXMuaW5kZXhPZiggZGF0YS5jZG5fcGFnZV9tZXNzYWdlICkgPT09IC0xICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LmxvY2F0aW9uLnJlbG9hZCgpO1xuXHR9XG5cblx0ZnVuY3Rpb24gcHJvY2Vzc1N0YXR1cyggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl9wcm9jZXNzJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fcHJvY2Vzc19zZXQnO1xuXHRcdHBvc3REYXRhICs9ICcmc3RhdHVzPScgKyBkYXRhLnJvY2tldGNkbl9wcm9jZXNzO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cdH1cblxuXHRmdW5jdGlvbiBlbmFibGVDRE4oIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl91cmwnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9lbmFibGUnO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3VybD0nICsgZGF0YS5yb2NrZXRjZG5fdXJsO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXG5cdFx0cmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSAoKSA9PiB7XG5cdFx0XHRpZiAoIHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gWE1MSHR0cFJlcXVlc3QuRE9ORSAmJiAyMDAgPT09IHJlcXVlc3Quc3RhdHVzICkge1xuXHRcdFx0XHRsZXQgcmVzcG9uc2VUeHQgPSBKU09OLnBhcnNlKHJlcXVlc3QucmVzcG9uc2VUZXh0KTtcblx0XHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdCdzdWNjZXNzJzogcmVzcG9uc2VUeHQuc3VjY2Vzcyxcblx0XHRcdFx0XHRcdCdkYXRhJzogcmVzcG9uc2VUeHQuZGF0YSxcblx0XHRcdFx0XHRcdCdyb2NrZXRjZG4nOiB0cnVlXG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdFx0KTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gZGlzYWJsZUNETiggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX2Rpc2FibGUnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9kaXNhYmxlJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKSB7XG5cdFx0Y29uc3QgaHR0cFJlcXVlc3QgPSBuZXcgWE1MSHR0cFJlcXVlc3QoKTtcblxuXHRcdGh0dHBSZXF1ZXN0Lm9wZW4oICdQT1NUJywgYWpheHVybCApO1xuXHRcdGh0dHBSZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoICdDb250ZW50LVR5cGUnLCAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJyApO1xuXHRcdGh0dHBSZXF1ZXN0LnNlbmQoIHBvc3REYXRhICk7XG5cblx0XHRyZXR1cm4gaHR0cFJlcXVlc3Q7XG5cdH1cblxuXHRmdW5jdGlvbiBzZXRDRE5GcmFtZUhlaWdodCggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2NkbkZyYW1lSGVpZ2h0JyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAncm9ja2V0Y2RuLWlmcmFtZScgKS5zdHlsZS5oZWlnaHQgPSBgJHsgZGF0YS5jZG5GcmFtZUhlaWdodCB9cHhgO1xuXHR9XG5cblx0ZnVuY3Rpb24gdG9rZW5IYW5kbGVyKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdG9rZW4nICkgKSB7XG5cdFx0XHRsZXQgZGF0YSA9IHtwcm9jZXNzOlwic3Vic2NyaWJlXCIsIG1lc3NhZ2U6XCJ0b2tlbl9ub3RfcmVjZWl2ZWRcIn07XG5cdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdHtcblx0XHRcdFx0XHQnc3VjY2Vzcyc6IGZhbHNlLFxuXHRcdFx0XHRcdCdkYXRhJzogZGF0YSxcblx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHR9LFxuXHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdCk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXNhdmVfcm9ja2V0Y2RuX3Rva2VuJztcblx0XHRwb3N0RGF0YSArPSAnJnZhbHVlPScgKyBkYXRhLnJvY2tldGNkbl90b2tlbjtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl92YWxpZGF0ZV90b2tlbicgKSB8fCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdmFsaWRhdGVfY25hbWUnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl92YWxpZGF0ZV90b2tlbl9jbmFtZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdXJsPScgKyBkYXRhLnJvY2tldGNkbl92YWxpZGF0ZV9jbmFtZTtcblx0XHRwb3N0RGF0YSArPSAnJmNkbl90b2tlbj0nICsgZGF0YS5yb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW47XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cdH1cbn0gKSggZG9jdW1lbnQsIHdpbmRvdyApO1xuIiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwiVGltZWxpbmVMaXRlXCIsW1wiY29yZS5BbmltYXRpb25cIixcImNvcmUuU2ltcGxlVGltZWxpbmVcIixcIlR3ZWVuTGl0ZVwiXSxmdW5jdGlvbih0LGUsaSl7dmFyIHM9ZnVuY3Rpb24odCl7ZS5jYWxsKHRoaXMsdCksdGhpcy5fbGFiZWxzPXt9LHRoaXMuYXV0b1JlbW92ZUNoaWxkcmVuPXRoaXMudmFycy5hdXRvUmVtb3ZlQ2hpbGRyZW49PT0hMCx0aGlzLnNtb290aENoaWxkVGltaW5nPXRoaXMudmFycy5zbW9vdGhDaGlsZFRpbWluZz09PSEwLHRoaXMuX3NvcnRDaGlsZHJlbj0hMCx0aGlzLl9vblVwZGF0ZT10aGlzLnZhcnMub25VcGRhdGU7dmFyIGkscyxyPXRoaXMudmFycztmb3IocyBpbiByKWk9cltzXSxhKGkpJiYtMSE9PWkuam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpJiYocltzXT10aGlzLl9zd2FwU2VsZkluUGFyYW1zKGkpKTthKHIudHdlZW5zKSYmdGhpcy5hZGQoci50d2VlbnMsMCxyLmFsaWduLHIuc3RhZ2dlcil9LHI9MWUtMTAsbj1pLl9pbnRlcm5hbHMuaXNTZWxlY3RvcixhPWkuX2ludGVybmFscy5pc0FycmF5LG89W10saD13aW5kb3cuX2dzRGVmaW5lLmdsb2JhbHMsbD1mdW5jdGlvbih0KXt2YXIgZSxpPXt9O2ZvcihlIGluIHQpaVtlXT10W2VdO3JldHVybiBpfSxfPWZ1bmN0aW9uKHQsZSxpLHMpe3QuX3RpbWVsaW5lLnBhdXNlKHQuX3N0YXJ0VGltZSksZSYmZS5hcHBseShzfHx0Ll90aW1lbGluZSxpfHxvKX0sdT1vLnNsaWNlLGY9cy5wcm90b3R5cGU9bmV3IGU7cmV0dXJuIHMudmVyc2lvbj1cIjEuMTIuMVwiLGYuY29uc3RydWN0b3I9cyxmLmtpbGwoKS5fZ2M9ITEsZi50bz1mdW5jdGlvbih0LGUscyxyKXt2YXIgbj1zLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aTtyZXR1cm4gZT90aGlzLmFkZChuZXcgbih0LGUscykscik6dGhpcy5zZXQodCxzLHIpfSxmLmZyb209ZnVuY3Rpb24odCxlLHMscil7cmV0dXJuIHRoaXMuYWRkKChzLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aSkuZnJvbSh0LGUscykscil9LGYuZnJvbVRvPWZ1bmN0aW9uKHQsZSxzLHIsbil7dmFyIGE9ci5yZXBlYXQmJmguVHdlZW5NYXh8fGk7cmV0dXJuIGU/dGhpcy5hZGQoYS5mcm9tVG8odCxlLHMsciksbik6dGhpcy5zZXQodCxyLG4pfSxmLnN0YWdnZXJUbz1mdW5jdGlvbih0LGUscixhLG8saCxfLGYpe3ZhciBwLGM9bmV3IHMoe29uQ29tcGxldGU6aCxvbkNvbXBsZXRlUGFyYW1zOl8sb25Db21wbGV0ZVNjb3BlOmYsc21vb3RoQ2hpbGRUaW1pbmc6dGhpcy5zbW9vdGhDaGlsZFRpbWluZ30pO2ZvcihcInN0cmluZ1wiPT10eXBlb2YgdCYmKHQ9aS5zZWxlY3Rvcih0KXx8dCksbih0KSYmKHQ9dS5jYWxsKHQsMCkpLGE9YXx8MCxwPTA7dC5sZW5ndGg+cDtwKyspci5zdGFydEF0JiYoci5zdGFydEF0PWwoci5zdGFydEF0KSksYy50byh0W3BdLGUsbChyKSxwKmEpO3JldHVybiB0aGlzLmFkZChjLG8pfSxmLnN0YWdnZXJGcm9tPWZ1bmN0aW9uKHQsZSxpLHMscixuLGEsbyl7cmV0dXJuIGkuaW1tZWRpYXRlUmVuZGVyPTAhPWkuaW1tZWRpYXRlUmVuZGVyLGkucnVuQmFja3dhcmRzPSEwLHRoaXMuc3RhZ2dlclRvKHQsZSxpLHMscixuLGEsbyl9LGYuc3RhZ2dlckZyb21Ubz1mdW5jdGlvbih0LGUsaSxzLHIsbixhLG8saCl7cmV0dXJuIHMuc3RhcnRBdD1pLHMuaW1tZWRpYXRlUmVuZGVyPTAhPXMuaW1tZWRpYXRlUmVuZGVyJiYwIT1pLmltbWVkaWF0ZVJlbmRlcix0aGlzLnN0YWdnZXJUbyh0LGUscyxyLG4sYSxvLGgpfSxmLmNhbGw9ZnVuY3Rpb24odCxlLHMscil7cmV0dXJuIHRoaXMuYWRkKGkuZGVsYXllZENhbGwoMCx0LGUscykscil9LGYuc2V0PWZ1bmN0aW9uKHQsZSxzKXtyZXR1cm4gcz10aGlzLl9wYXJzZVRpbWVPckxhYmVsKHMsMCwhMCksbnVsbD09ZS5pbW1lZGlhdGVSZW5kZXImJihlLmltbWVkaWF0ZVJlbmRlcj1zPT09dGhpcy5fdGltZSYmIXRoaXMuX3BhdXNlZCksdGhpcy5hZGQobmV3IGkodCwwLGUpLHMpfSxzLmV4cG9ydFJvb3Q9ZnVuY3Rpb24odCxlKXt0PXR8fHt9LG51bGw9PXQuc21vb3RoQ2hpbGRUaW1pbmcmJih0LnNtb290aENoaWxkVGltaW5nPSEwKTt2YXIgcixuLGE9bmV3IHModCksbz1hLl90aW1lbGluZTtmb3IobnVsbD09ZSYmKGU9ITApLG8uX3JlbW92ZShhLCEwKSxhLl9zdGFydFRpbWU9MCxhLl9yYXdQcmV2VGltZT1hLl90aW1lPWEuX3RvdGFsVGltZT1vLl90aW1lLHI9by5fZmlyc3Q7cjspbj1yLl9uZXh0LGUmJnIgaW5zdGFuY2VvZiBpJiZyLnRhcmdldD09PXIudmFycy5vbkNvbXBsZXRlfHxhLmFkZChyLHIuX3N0YXJ0VGltZS1yLl9kZWxheSkscj1uO3JldHVybiBvLmFkZChhLDApLGF9LGYuYWRkPWZ1bmN0aW9uKHIsbixvLGgpe3ZhciBsLF8sdSxmLHAsYztpZihcIm51bWJlclwiIT10eXBlb2YgbiYmKG49dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChuLDAsITAscikpLCEociBpbnN0YW5jZW9mIHQpKXtpZihyIGluc3RhbmNlb2YgQXJyYXl8fHImJnIucHVzaCYmYShyKSl7Zm9yKG89b3x8XCJub3JtYWxcIixoPWh8fDAsbD1uLF89ci5sZW5ndGgsdT0wO18+dTt1KyspYShmPXJbdV0pJiYoZj1uZXcgcyh7dHdlZW5zOmZ9KSksdGhpcy5hZGQoZixsKSxcInN0cmluZ1wiIT10eXBlb2YgZiYmXCJmdW5jdGlvblwiIT10eXBlb2YgZiYmKFwic2VxdWVuY2VcIj09PW8/bD1mLl9zdGFydFRpbWUrZi50b3RhbER1cmF0aW9uKCkvZi5fdGltZVNjYWxlOlwic3RhcnRcIj09PW8mJihmLl9zdGFydFRpbWUtPWYuZGVsYXkoKSkpLGwrPWg7cmV0dXJuIHRoaXMuX3VuY2FjaGUoITApfWlmKFwic3RyaW5nXCI9PXR5cGVvZiByKXJldHVybiB0aGlzLmFkZExhYmVsKHIsbik7aWYoXCJmdW5jdGlvblwiIT10eXBlb2Ygcil0aHJvd1wiQ2Fubm90IGFkZCBcIityK1wiIGludG8gdGhlIHRpbWVsaW5lOyBpdCBpcyBub3QgYSB0d2VlbiwgdGltZWxpbmUsIGZ1bmN0aW9uLCBvciBzdHJpbmcuXCI7cj1pLmRlbGF5ZWRDYWxsKDAscil9aWYoZS5wcm90b3R5cGUuYWRkLmNhbGwodGhpcyxyLG4pLCh0aGlzLl9nY3x8dGhpcy5fdGltZT09PXRoaXMuX2R1cmF0aW9uKSYmIXRoaXMuX3BhdXNlZCYmdGhpcy5fZHVyYXRpb248dGhpcy5kdXJhdGlvbigpKWZvcihwPXRoaXMsYz1wLnJhd1RpbWUoKT5yLl9zdGFydFRpbWU7cC5fdGltZWxpbmU7KWMmJnAuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nP3AudG90YWxUaW1lKHAuX3RvdGFsVGltZSwhMCk6cC5fZ2MmJnAuX2VuYWJsZWQoITAsITEpLHA9cC5fdGltZWxpbmU7cmV0dXJuIHRoaXN9LGYucmVtb3ZlPWZ1bmN0aW9uKGUpe2lmKGUgaW5zdGFuY2VvZiB0KXJldHVybiB0aGlzLl9yZW1vdmUoZSwhMSk7aWYoZSBpbnN0YW5jZW9mIEFycmF5fHxlJiZlLnB1c2gmJmEoZSkpe2Zvcih2YXIgaT1lLmxlbmd0aDstLWk+LTE7KXRoaXMucmVtb3ZlKGVbaV0pO3JldHVybiB0aGlzfXJldHVyblwic3RyaW5nXCI9PXR5cGVvZiBlP3RoaXMucmVtb3ZlTGFiZWwoZSk6dGhpcy5raWxsKG51bGwsZSl9LGYuX3JlbW92ZT1mdW5jdGlvbih0LGkpe2UucHJvdG90eXBlLl9yZW1vdmUuY2FsbCh0aGlzLHQsaSk7dmFyIHM9dGhpcy5fbGFzdDtyZXR1cm4gcz90aGlzLl90aW1lPnMuX3N0YXJ0VGltZStzLl90b3RhbER1cmF0aW9uL3MuX3RpbWVTY2FsZSYmKHRoaXMuX3RpbWU9dGhpcy5kdXJhdGlvbigpLHRoaXMuX3RvdGFsVGltZT10aGlzLl90b3RhbER1cmF0aW9uKTp0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT10aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPTAsdGhpc30sZi5hcHBlbmQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5hZGQodCx0aGlzLl9wYXJzZVRpbWVPckxhYmVsKG51bGwsZSwhMCx0KSl9LGYuaW5zZXJ0PWYuaW5zZXJ0TXVsdGlwbGU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuYWRkKHQsZXx8MCxpLHMpfSxmLmFwcGVuZE11bHRpcGxlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmFkZCh0LHRoaXMuX3BhcnNlVGltZU9yTGFiZWwobnVsbCxlLCEwLHQpLGkscyl9LGYuYWRkTGFiZWw9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5fbGFiZWxzW3RdPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwoZSksdGhpc30sZi5hZGRQYXVzZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5jYWxsKF8sW1wie3NlbGZ9XCIsZSxpLHNdLHRoaXMsdCl9LGYucmVtb3ZlTGFiZWw9ZnVuY3Rpb24odCl7cmV0dXJuIGRlbGV0ZSB0aGlzLl9sYWJlbHNbdF0sdGhpc30sZi5nZXRMYWJlbFRpbWU9ZnVuY3Rpb24odCl7cmV0dXJuIG51bGwhPXRoaXMuX2xhYmVsc1t0XT90aGlzLl9sYWJlbHNbdF06LTF9LGYuX3BhcnNlVGltZU9yTGFiZWw9ZnVuY3Rpb24oZSxpLHMscil7dmFyIG47aWYociBpbnN0YW5jZW9mIHQmJnIudGltZWxpbmU9PT10aGlzKXRoaXMucmVtb3ZlKHIpO2Vsc2UgaWYociYmKHIgaW5zdGFuY2VvZiBBcnJheXx8ci5wdXNoJiZhKHIpKSlmb3Iobj1yLmxlbmd0aDstLW4+LTE7KXJbbl1pbnN0YW5jZW9mIHQmJnJbbl0udGltZWxpbmU9PT10aGlzJiZ0aGlzLnJlbW92ZShyW25dKTtpZihcInN0cmluZ1wiPT10eXBlb2YgaSlyZXR1cm4gdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChpLHMmJlwibnVtYmVyXCI9PXR5cGVvZiBlJiZudWxsPT10aGlzLl9sYWJlbHNbaV0/ZS10aGlzLmR1cmF0aW9uKCk6MCxzKTtpZihpPWl8fDAsXCJzdHJpbmdcIiE9dHlwZW9mIGV8fCFpc05hTihlKSYmbnVsbD09dGhpcy5fbGFiZWxzW2VdKW51bGw9PWUmJihlPXRoaXMuZHVyYXRpb24oKSk7ZWxzZXtpZihuPWUuaW5kZXhPZihcIj1cIiksLTE9PT1uKXJldHVybiBudWxsPT10aGlzLl9sYWJlbHNbZV0/cz90aGlzLl9sYWJlbHNbZV09dGhpcy5kdXJhdGlvbigpK2k6aTp0aGlzLl9sYWJlbHNbZV0raTtpPXBhcnNlSW50KGUuY2hhckF0KG4tMSkrXCIxXCIsMTApKk51bWJlcihlLnN1YnN0cihuKzEpKSxlPW4+MT90aGlzLl9wYXJzZVRpbWVPckxhYmVsKGUuc3Vic3RyKDAsbi0xKSwwLHMpOnRoaXMuZHVyYXRpb24oKX1yZXR1cm4gTnVtYmVyKGUpK2l9LGYuc2Vlaz1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnRvdGFsVGltZShcIm51bWJlclwiPT10eXBlb2YgdD90OnRoaXMuX3BhcnNlVGltZU9yTGFiZWwodCksZSE9PSExKX0sZi5zdG9wPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMucGF1c2VkKCEwKX0sZi5nb3RvQW5kUGxheT1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnBsYXkodCxlKX0sZi5nb3RvQW5kU3RvcD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnBhdXNlKHQsZSl9LGYucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt0aGlzLl9nYyYmdGhpcy5fZW5hYmxlZCghMCwhMSk7dmFyIHMsbixhLGgsbCxfPXRoaXMuX2RpcnR5P3RoaXMudG90YWxEdXJhdGlvbigpOnRoaXMuX3RvdGFsRHVyYXRpb24sdT10aGlzLl90aW1lLGY9dGhpcy5fc3RhcnRUaW1lLHA9dGhpcy5fdGltZVNjYWxlLGM9dGhpcy5fcGF1c2VkO2lmKHQ+PV8/KHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPV8sdGhpcy5fcmV2ZXJzZWR8fHRoaXMuX2hhc1BhdXNlZENoaWxkKCl8fChuPSEwLGg9XCJvbkNvbXBsZXRlXCIsMD09PXRoaXMuX2R1cmF0aW9uJiYoMD09PXR8fDA+dGhpcy5fcmF3UHJldlRpbWV8fHRoaXMuX3Jhd1ByZXZUaW1lPT09cikmJnRoaXMuX3Jhd1ByZXZUaW1lIT09dCYmdGhpcy5fZmlyc3QmJihsPSEwLHRoaXMuX3Jhd1ByZXZUaW1lPnImJihoPVwib25SZXZlcnNlQ29tcGxldGVcIikpKSx0aGlzLl9yYXdQcmV2VGltZT10aGlzLl9kdXJhdGlvbnx8IWV8fHR8fHRoaXMuX3Jhd1ByZXZUaW1lPT09dD90OnIsdD1fKzFlLTQpOjFlLTc+dD8odGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9MCwoMCE9PXV8fDA9PT10aGlzLl9kdXJhdGlvbiYmdGhpcy5fcmF3UHJldlRpbWUhPT1yJiYodGhpcy5fcmF3UHJldlRpbWU+MHx8MD50JiZ0aGlzLl9yYXdQcmV2VGltZT49MCkpJiYoaD1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIsbj10aGlzLl9yZXZlcnNlZCksMD50Pyh0aGlzLl9hY3RpdmU9ITEsMD09PXRoaXMuX2R1cmF0aW9uJiZ0aGlzLl9yYXdQcmV2VGltZT49MCYmdGhpcy5fZmlyc3QmJihsPSEwKSx0aGlzLl9yYXdQcmV2VGltZT10KToodGhpcy5fcmF3UHJldlRpbWU9dGhpcy5fZHVyYXRpb258fCFlfHx0fHx0aGlzLl9yYXdQcmV2VGltZT09PXQ/dDpyLHQ9MCx0aGlzLl9pbml0dGVkfHwobD0hMCkpKTp0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10aGlzLl9yYXdQcmV2VGltZT10LHRoaXMuX3RpbWUhPT11JiZ0aGlzLl9maXJzdHx8aXx8bCl7aWYodGhpcy5faW5pdHRlZHx8KHRoaXMuX2luaXR0ZWQ9ITApLHRoaXMuX2FjdGl2ZXx8IXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZSE9PXUmJnQ+MCYmKHRoaXMuX2FjdGl2ZT0hMCksMD09PXUmJnRoaXMudmFycy5vblN0YXJ0JiYwIT09dGhpcy5fdGltZSYmKGV8fHRoaXMudmFycy5vblN0YXJ0LmFwcGx5KHRoaXMudmFycy5vblN0YXJ0U2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uU3RhcnRQYXJhbXN8fG8pKSx0aGlzLl90aW1lPj11KWZvcihzPXRoaXMuX2ZpcnN0O3MmJihhPXMuX25leHQsIXRoaXMuX3BhdXNlZHx8Yyk7KShzLl9hY3RpdmV8fHMuX3N0YXJ0VGltZTw9dGhpcy5fdGltZSYmIXMuX3BhdXNlZCYmIXMuX2djKSYmKHMuX3JldmVyc2VkP3MucmVuZGVyKChzLl9kaXJ0eT9zLnRvdGFsRHVyYXRpb24oKTpzLl90b3RhbER1cmF0aW9uKS0odC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpOnMucmVuZGVyKCh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSkpLHM9YTtlbHNlIGZvcihzPXRoaXMuX2xhc3Q7cyYmKGE9cy5fcHJldiwhdGhpcy5fcGF1c2VkfHxjKTspKHMuX2FjdGl2ZXx8dT49cy5fc3RhcnRUaW1lJiYhcy5fcGF1c2VkJiYhcy5fZ2MpJiYocy5fcmV2ZXJzZWQ/cy5yZW5kZXIoKHMuX2RpcnR5P3MudG90YWxEdXJhdGlvbigpOnMuX3RvdGFsRHVyYXRpb24pLSh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSk6cy5yZW5kZXIoKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKSkscz1hO3RoaXMuX29uVXBkYXRlJiYoZXx8dGhpcy5fb25VcGRhdGUuYXBwbHkodGhpcy52YXJzLm9uVXBkYXRlU2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uVXBkYXRlUGFyYW1zfHxvKSksaCYmKHRoaXMuX2djfHwoZj09PXRoaXMuX3N0YXJ0VGltZXx8cCE9PXRoaXMuX3RpbWVTY2FsZSkmJigwPT09dGhpcy5fdGltZXx8Xz49dGhpcy50b3RhbER1cmF0aW9uKCkpJiYobiYmKHRoaXMuX3RpbWVsaW5lLmF1dG9SZW1vdmVDaGlsZHJlbiYmdGhpcy5fZW5hYmxlZCghMSwhMSksdGhpcy5fYWN0aXZlPSExKSwhZSYmdGhpcy52YXJzW2hdJiZ0aGlzLnZhcnNbaF0uYXBwbHkodGhpcy52YXJzW2grXCJTY29wZVwiXXx8dGhpcyx0aGlzLnZhcnNbaCtcIlBhcmFtc1wiXXx8bykpKX19LGYuX2hhc1BhdXNlZENoaWxkPWZ1bmN0aW9uKCl7Zm9yKHZhciB0PXRoaXMuX2ZpcnN0O3Q7KXtpZih0Ll9wYXVzZWR8fHQgaW5zdGFuY2VvZiBzJiZ0Ll9oYXNQYXVzZWRDaGlsZCgpKXJldHVybiEwO3Q9dC5fbmV4dH1yZXR1cm4hMX0sZi5nZXRDaGlsZHJlbj1mdW5jdGlvbih0LGUscyxyKXtyPXJ8fC05OTk5OTk5OTk5O2Zvcih2YXIgbj1bXSxhPXRoaXMuX2ZpcnN0LG89MDthOylyPmEuX3N0YXJ0VGltZXx8KGEgaW5zdGFuY2VvZiBpP2UhPT0hMSYmKG5bbysrXT1hKToocyE9PSExJiYobltvKytdPWEpLHQhPT0hMSYmKG49bi5jb25jYXQoYS5nZXRDaGlsZHJlbighMCxlLHMpKSxvPW4ubGVuZ3RoKSkpLGE9YS5fbmV4dDtyZXR1cm4gbn0sZi5nZXRUd2VlbnNPZj1mdW5jdGlvbih0LGUpe3ZhciBzLHIsbj10aGlzLl9nYyxhPVtdLG89MDtmb3IobiYmdGhpcy5fZW5hYmxlZCghMCwhMCkscz1pLmdldFR3ZWVuc09mKHQpLHI9cy5sZW5ndGg7LS1yPi0xOykoc1tyXS50aW1lbGluZT09PXRoaXN8fGUmJnRoaXMuX2NvbnRhaW5zKHNbcl0pKSYmKGFbbysrXT1zW3JdKTtyZXR1cm4gbiYmdGhpcy5fZW5hYmxlZCghMSwhMCksYX0sZi5fY29udGFpbnM9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQudGltZWxpbmU7ZTspe2lmKGU9PT10aGlzKXJldHVybiEwO2U9ZS50aW1lbGluZX1yZXR1cm4hMX0sZi5zaGlmdENoaWxkcmVuPWZ1bmN0aW9uKHQsZSxpKXtpPWl8fDA7Zm9yKHZhciBzLHI9dGhpcy5fZmlyc3Qsbj10aGlzLl9sYWJlbHM7cjspci5fc3RhcnRUaW1lPj1pJiYoci5fc3RhcnRUaW1lKz10KSxyPXIuX25leHQ7aWYoZSlmb3IocyBpbiBuKW5bc10+PWkmJihuW3NdKz10KTtyZXR1cm4gdGhpcy5fdW5jYWNoZSghMCl9LGYuX2tpbGw9ZnVuY3Rpb24odCxlKXtpZighdCYmIWUpcmV0dXJuIHRoaXMuX2VuYWJsZWQoITEsITEpO2Zvcih2YXIgaT1lP3RoaXMuZ2V0VHdlZW5zT2YoZSk6dGhpcy5nZXRDaGlsZHJlbighMCwhMCwhMSkscz1pLmxlbmd0aCxyPSExOy0tcz4tMTspaVtzXS5fa2lsbCh0LGUpJiYocj0hMCk7cmV0dXJuIHJ9LGYuY2xlYXI9ZnVuY3Rpb24odCl7dmFyIGU9dGhpcy5nZXRDaGlsZHJlbighMSwhMCwhMCksaT1lLmxlbmd0aDtmb3IodGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9MDstLWk+LTE7KWVbaV0uX2VuYWJsZWQoITEsITEpO3JldHVybiB0IT09ITEmJih0aGlzLl9sYWJlbHM9e30pLHRoaXMuX3VuY2FjaGUoITApfSxmLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtmb3IodmFyIHQ9dGhpcy5fZmlyc3Q7dDspdC5pbnZhbGlkYXRlKCksdD10Ll9uZXh0O3JldHVybiB0aGlzfSxmLl9lbmFibGVkPWZ1bmN0aW9uKHQsaSl7aWYodD09PXRoaXMuX2djKWZvcih2YXIgcz10aGlzLl9maXJzdDtzOylzLl9lbmFibGVkKHQsITApLHM9cy5fbmV4dDtyZXR1cm4gZS5wcm90b3R5cGUuX2VuYWJsZWQuY2FsbCh0aGlzLHQsaSl9LGYuZHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KDAhPT10aGlzLmR1cmF0aW9uKCkmJjAhPT10JiZ0aGlzLnRpbWVTY2FsZSh0aGlzLl9kdXJhdGlvbi90KSx0aGlzKToodGhpcy5fZGlydHkmJnRoaXMudG90YWxEdXJhdGlvbigpLHRoaXMuX2R1cmF0aW9uKX0sZi50b3RhbER1cmF0aW9uPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXtpZih0aGlzLl9kaXJ0eSl7Zm9yKHZhciBlLGkscz0wLHI9dGhpcy5fbGFzdCxuPTk5OTk5OTk5OTk5OTtyOyllPXIuX3ByZXYsci5fZGlydHkmJnIudG90YWxEdXJhdGlvbigpLHIuX3N0YXJ0VGltZT5uJiZ0aGlzLl9zb3J0Q2hpbGRyZW4mJiFyLl9wYXVzZWQ/dGhpcy5hZGQocixyLl9zdGFydFRpbWUtci5fZGVsYXkpOm49ci5fc3RhcnRUaW1lLDA+ci5fc3RhcnRUaW1lJiYhci5fcGF1c2VkJiYocy09ci5fc3RhcnRUaW1lLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nJiYodGhpcy5fc3RhcnRUaW1lKz1yLl9zdGFydFRpbWUvdGhpcy5fdGltZVNjYWxlKSx0aGlzLnNoaWZ0Q2hpbGRyZW4oLXIuX3N0YXJ0VGltZSwhMSwtOTk5OTk5OTk5OSksbj0wKSxpPXIuX3N0YXJ0VGltZStyLl90b3RhbER1cmF0aW9uL3IuX3RpbWVTY2FsZSxpPnMmJihzPWkpLHI9ZTt0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXMsdGhpcy5fZGlydHk9ITF9cmV0dXJuIHRoaXMuX3RvdGFsRHVyYXRpb259cmV0dXJuIDAhPT10aGlzLnRvdGFsRHVyYXRpb24oKSYmMCE9PXQmJnRoaXMudGltZVNjYWxlKHRoaXMuX3RvdGFsRHVyYXRpb24vdCksdGhpc30sZi51c2VzRnJhbWVzPWZ1bmN0aW9uKCl7Zm9yKHZhciBlPXRoaXMuX3RpbWVsaW5lO2UuX3RpbWVsaW5lOyllPWUuX3RpbWVsaW5lO3JldHVybiBlPT09dC5fcm9vdEZyYW1lc1RpbWVsaW5lfSxmLnJhd1RpbWU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fcGF1c2VkP3RoaXMuX3RvdGFsVGltZToodGhpcy5fdGltZWxpbmUucmF3VGltZSgpLXRoaXMuX3N0YXJ0VGltZSkqdGhpcy5fdGltZVNjYWxlfSxzfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4oZnVuY3Rpb24odCl7XCJ1c2Ugc3RyaWN0XCI7dmFyIGU9dC5HcmVlblNvY2tHbG9iYWxzfHx0O2lmKCFlLlR3ZWVuTGl0ZSl7dmFyIGkscyxuLHIsYSxvPWZ1bmN0aW9uKHQpe3ZhciBpLHM9dC5zcGxpdChcIi5cIiksbj1lO2ZvcihpPTA7cy5sZW5ndGg+aTtpKyspbltzW2ldXT1uPW5bc1tpXV18fHt9O3JldHVybiBufSxsPW8oXCJjb20uZ3JlZW5zb2NrXCIpLGg9MWUtMTAsXz1bXS5zbGljZSx1PWZ1bmN0aW9uKCl7fSxtPWZ1bmN0aW9uKCl7dmFyIHQ9T2JqZWN0LnByb3RvdHlwZS50b1N0cmluZyxlPXQuY2FsbChbXSk7cmV0dXJuIGZ1bmN0aW9uKGkpe3JldHVybiBudWxsIT1pJiYoaSBpbnN0YW5jZW9mIEFycmF5fHxcIm9iamVjdFwiPT10eXBlb2YgaSYmISFpLnB1c2gmJnQuY2FsbChpKT09PWUpfX0oKSxmPXt9LHA9ZnVuY3Rpb24oaSxzLG4scil7dGhpcy5zYz1mW2ldP2ZbaV0uc2M6W10sZltpXT10aGlzLHRoaXMuZ3NDbGFzcz1udWxsLHRoaXMuZnVuYz1uO3ZhciBhPVtdO3RoaXMuY2hlY2s9ZnVuY3Rpb24obCl7Zm9yKHZhciBoLF8sdSxtLGM9cy5sZW5ndGgsZD1jOy0tYz4tMTspKGg9ZltzW2NdXXx8bmV3IHAoc1tjXSxbXSkpLmdzQ2xhc3M/KGFbY109aC5nc0NsYXNzLGQtLSk6bCYmaC5zYy5wdXNoKHRoaXMpO2lmKDA9PT1kJiZuKWZvcihfPShcImNvbS5ncmVlbnNvY2suXCIraSkuc3BsaXQoXCIuXCIpLHU9Xy5wb3AoKSxtPW8oXy5qb2luKFwiLlwiKSlbdV09dGhpcy5nc0NsYXNzPW4uYXBwbHkobixhKSxyJiYoZVt1XT1tLFwiZnVuY3Rpb25cIj09dHlwZW9mIGRlZmluZSYmZGVmaW5lLmFtZD9kZWZpbmUoKHQuR3JlZW5Tb2NrQU1EUGF0aD90LkdyZWVuU29ja0FNRFBhdGgrXCIvXCI6XCJcIikraS5zcGxpdChcIi5cIikuam9pbihcIi9cIiksW10sZnVuY3Rpb24oKXtyZXR1cm4gbX0pOlwidW5kZWZpbmVkXCIhPXR5cGVvZiBtb2R1bGUmJm1vZHVsZS5leHBvcnRzJiYobW9kdWxlLmV4cG9ydHM9bSkpLGM9MDt0aGlzLnNjLmxlbmd0aD5jO2MrKyl0aGlzLnNjW2NdLmNoZWNrKCl9LHRoaXMuY2hlY2soITApfSxjPXQuX2dzRGVmaW5lPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiBuZXcgcCh0LGUsaSxzKX0sZD1sLl9jbGFzcz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIGU9ZXx8ZnVuY3Rpb24oKXt9LGModCxbXSxmdW5jdGlvbigpe3JldHVybiBlfSxpKSxlfTtjLmdsb2JhbHM9ZTt2YXIgdj1bMCwwLDEsMV0sZz1bXSxUPWQoXCJlYXNpbmcuRWFzZVwiLGZ1bmN0aW9uKHQsZSxpLHMpe3RoaXMuX2Z1bmM9dCx0aGlzLl90eXBlPWl8fDAsdGhpcy5fcG93ZXI9c3x8MCx0aGlzLl9wYXJhbXM9ZT92LmNvbmNhdChlKTp2fSwhMCkseT1ULm1hcD17fSx3PVQucmVnaXN0ZXI9ZnVuY3Rpb24odCxlLGkscyl7Zm9yKHZhciBuLHIsYSxvLGg9ZS5zcGxpdChcIixcIiksXz1oLmxlbmd0aCx1PShpfHxcImVhc2VJbixlYXNlT3V0LGVhc2VJbk91dFwiKS5zcGxpdChcIixcIik7LS1fPi0xOylmb3Iocj1oW19dLG49cz9kKFwiZWFzaW5nLlwiK3IsbnVsbCwhMCk6bC5lYXNpbmdbcl18fHt9LGE9dS5sZW5ndGg7LS1hPi0xOylvPXVbYV0seVtyK1wiLlwiK29dPXlbbytyXT1uW29dPXQuZ2V0UmF0aW8/dDp0W29dfHxuZXcgdH07Zm9yKG49VC5wcm90b3R5cGUsbi5fY2FsY0VuZD0hMSxuLmdldFJhdGlvPWZ1bmN0aW9uKHQpe2lmKHRoaXMuX2Z1bmMpcmV0dXJuIHRoaXMuX3BhcmFtc1swXT10LHRoaXMuX2Z1bmMuYXBwbHkobnVsbCx0aGlzLl9wYXJhbXMpO3ZhciBlPXRoaXMuX3R5cGUsaT10aGlzLl9wb3dlcixzPTE9PT1lPzEtdDoyPT09ZT90Oi41PnQ/Mip0OjIqKDEtdCk7cmV0dXJuIDE9PT1pP3MqPXM6Mj09PWk/cyo9cypzOjM9PT1pP3MqPXMqcypzOjQ9PT1pJiYocyo9cypzKnMqcyksMT09PWU/MS1zOjI9PT1lP3M6LjU+dD9zLzI6MS1zLzJ9LGk9W1wiTGluZWFyXCIsXCJRdWFkXCIsXCJDdWJpY1wiLFwiUXVhcnRcIixcIlF1aW50LFN0cm9uZ1wiXSxzPWkubGVuZ3RoOy0tcz4tMTspbj1pW3NdK1wiLFBvd2VyXCIrcyx3KG5ldyBUKG51bGwsbnVsbCwxLHMpLG4sXCJlYXNlT3V0XCIsITApLHcobmV3IFQobnVsbCxudWxsLDIscyksbixcImVhc2VJblwiKygwPT09cz9cIixlYXNlTm9uZVwiOlwiXCIpKSx3KG5ldyBUKG51bGwsbnVsbCwzLHMpLG4sXCJlYXNlSW5PdXRcIik7eS5saW5lYXI9bC5lYXNpbmcuTGluZWFyLmVhc2VJbix5LnN3aW5nPWwuZWFzaW5nLlF1YWQuZWFzZUluT3V0O3ZhciBQPWQoXCJldmVudHMuRXZlbnREaXNwYXRjaGVyXCIsZnVuY3Rpb24odCl7dGhpcy5fbGlzdGVuZXJzPXt9LHRoaXMuX2V2ZW50VGFyZ2V0PXR8fHRoaXN9KTtuPVAucHJvdG90eXBlLG4uYWRkRXZlbnRMaXN0ZW5lcj1mdW5jdGlvbih0LGUsaSxzLG4pe249bnx8MDt2YXIgbyxsLGg9dGhpcy5fbGlzdGVuZXJzW3RdLF89MDtmb3IobnVsbD09aCYmKHRoaXMuX2xpc3RlbmVyc1t0XT1oPVtdKSxsPWgubGVuZ3RoOy0tbD4tMTspbz1oW2xdLG8uYz09PWUmJm8ucz09PWk/aC5zcGxpY2UobCwxKTowPT09XyYmbj5vLnByJiYoXz1sKzEpO2guc3BsaWNlKF8sMCx7YzplLHM6aSx1cDpzLHByOm59KSx0aGlzIT09cnx8YXx8ci53YWtlKCl9LG4ucmVtb3ZlRXZlbnRMaXN0ZW5lcj1mdW5jdGlvbih0LGUpe3ZhciBpLHM9dGhpcy5fbGlzdGVuZXJzW3RdO2lmKHMpZm9yKGk9cy5sZW5ndGg7LS1pPi0xOylpZihzW2ldLmM9PT1lKXJldHVybiBzLnNwbGljZShpLDEpLHZvaWQgMH0sbi5kaXNwYXRjaEV2ZW50PWZ1bmN0aW9uKHQpe3ZhciBlLGkscyxuPXRoaXMuX2xpc3RlbmVyc1t0XTtpZihuKWZvcihlPW4ubGVuZ3RoLGk9dGhpcy5fZXZlbnRUYXJnZXQ7LS1lPi0xOylzPW5bZV0scy51cD9zLmMuY2FsbChzLnN8fGkse3R5cGU6dCx0YXJnZXQ6aX0pOnMuYy5jYWxsKHMuc3x8aSl9O3ZhciBrPXQucmVxdWVzdEFuaW1hdGlvbkZyYW1lLGI9dC5jYW5jZWxBbmltYXRpb25GcmFtZSxBPURhdGUubm93fHxmdW5jdGlvbigpe3JldHVybihuZXcgRGF0ZSkuZ2V0VGltZSgpfSxTPUEoKTtmb3IoaT1bXCJtc1wiLFwibW96XCIsXCJ3ZWJraXRcIixcIm9cIl0scz1pLmxlbmd0aDstLXM+LTEmJiFrOylrPXRbaVtzXStcIlJlcXVlc3RBbmltYXRpb25GcmFtZVwiXSxiPXRbaVtzXStcIkNhbmNlbEFuaW1hdGlvbkZyYW1lXCJdfHx0W2lbc10rXCJDYW5jZWxSZXF1ZXN0QW5pbWF0aW9uRnJhbWVcIl07ZChcIlRpY2tlclwiLGZ1bmN0aW9uKHQsZSl7dmFyIGkscyxuLG8sbCxfPXRoaXMsbT1BKCksZj1lIT09ITEmJmsscD01MDAsYz0zMyxkPWZ1bmN0aW9uKHQpe3ZhciBlLHIsYT1BKCktUzthPnAmJihtKz1hLWMpLFMrPWEsXy50aW1lPShTLW0pLzFlMyxlPV8udGltZS1sLCghaXx8ZT4wfHx0PT09ITApJiYoXy5mcmFtZSsrLGwrPWUrKGU+PW8/LjAwNDpvLWUpLHI9ITApLHQhPT0hMCYmKG49cyhkKSksciYmXy5kaXNwYXRjaEV2ZW50KFwidGlja1wiKX07UC5jYWxsKF8pLF8udGltZT1fLmZyYW1lPTAsXy50aWNrPWZ1bmN0aW9uKCl7ZCghMCl9LF8ubGFnU21vb3RoaW5nPWZ1bmN0aW9uKHQsZSl7cD10fHwxL2gsYz1NYXRoLm1pbihlLHAsMCl9LF8uc2xlZXA9ZnVuY3Rpb24oKXtudWxsIT1uJiYoZiYmYj9iKG4pOmNsZWFyVGltZW91dChuKSxzPXUsbj1udWxsLF89PT1yJiYoYT0hMSkpfSxfLndha2U9ZnVuY3Rpb24oKXtudWxsIT09bj9fLnNsZWVwKCk6Xy5mcmFtZT4xMCYmKFM9QSgpLXArNSkscz0wPT09aT91OmYmJms/azpmdW5jdGlvbih0KXtyZXR1cm4gc2V0VGltZW91dCh0LDB8MWUzKihsLV8udGltZSkrMSl9LF89PT1yJiYoYT0hMCksZCgyKX0sXy5mcHM9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KGk9dCxvPTEvKGl8fDYwKSxsPXRoaXMudGltZStvLF8ud2FrZSgpLHZvaWQgMCk6aX0sXy51c2VSQUY9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KF8uc2xlZXAoKSxmPXQsXy5mcHMoaSksdm9pZCAwKTpmfSxfLmZwcyh0KSxzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7ZiYmKCFufHw1Pl8uZnJhbWUpJiZfLnVzZVJBRighMSl9LDE1MDApfSksbj1sLlRpY2tlci5wcm90b3R5cGU9bmV3IGwuZXZlbnRzLkV2ZW50RGlzcGF0Y2hlcixuLmNvbnN0cnVjdG9yPWwuVGlja2VyO3ZhciB4PWQoXCJjb3JlLkFuaW1hdGlvblwiLGZ1bmN0aW9uKHQsZSl7aWYodGhpcy52YXJzPWU9ZXx8e30sdGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj10fHwwLHRoaXMuX2RlbGF5PU51bWJlcihlLmRlbGF5KXx8MCx0aGlzLl90aW1lU2NhbGU9MSx0aGlzLl9hY3RpdmU9ZS5pbW1lZGlhdGVSZW5kZXI9PT0hMCx0aGlzLmRhdGE9ZS5kYXRhLHRoaXMuX3JldmVyc2VkPWUucmV2ZXJzZWQ9PT0hMCxCKXthfHxyLndha2UoKTt2YXIgaT10aGlzLnZhcnMudXNlRnJhbWVzP1E6QjtpLmFkZCh0aGlzLGkuX3RpbWUpLHRoaXMudmFycy5wYXVzZWQmJnRoaXMucGF1c2VkKCEwKX19KTtyPXgudGlja2VyPW5ldyBsLlRpY2tlcixuPXgucHJvdG90eXBlLG4uX2RpcnR5PW4uX2djPW4uX2luaXR0ZWQ9bi5fcGF1c2VkPSExLG4uX3RvdGFsVGltZT1uLl90aW1lPTAsbi5fcmF3UHJldlRpbWU9LTEsbi5fbmV4dD1uLl9sYXN0PW4uX29uVXBkYXRlPW4uX3RpbWVsaW5lPW4udGltZWxpbmU9bnVsbCxuLl9wYXVzZWQ9ITE7dmFyIEM9ZnVuY3Rpb24oKXthJiZBKCktUz4yZTMmJnIud2FrZSgpLHNldFRpbWVvdXQoQywyZTMpfTtDKCksbi5wbGF5PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucmV2ZXJzZWQoITEpLnBhdXNlZCghMSl9LG4ucGF1c2U9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5wYXVzZWQoITApfSxuLnJlc3VtZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnBhdXNlZCghMSl9LG4uc2Vlaz1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnRvdGFsVGltZShOdW1iZXIodCksZSE9PSExKX0sbi5yZXN0YXJ0PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucmV2ZXJzZWQoITEpLnBhdXNlZCghMSkudG90YWxUaW1lKHQ/LXRoaXMuX2RlbGF5OjAsZSE9PSExLCEwKX0sbi5yZXZlcnNlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0fHx0aGlzLnRvdGFsRHVyYXRpb24oKSxlKSx0aGlzLnJldmVyc2VkKCEwKS5wYXVzZWQoITEpfSxuLnJlbmRlcj1mdW5jdGlvbigpe30sbi5pbnZhbGlkYXRlPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXN9LG4uaXNBY3RpdmU9ZnVuY3Rpb24oKXt2YXIgdCxlPXRoaXMuX3RpbWVsaW5lLGk9dGhpcy5fc3RhcnRUaW1lO3JldHVybiFlfHwhdGhpcy5fZ2MmJiF0aGlzLl9wYXVzZWQmJmUuaXNBY3RpdmUoKSYmKHQ9ZS5yYXdUaW1lKCkpPj1pJiZpK3RoaXMudG90YWxEdXJhdGlvbigpL3RoaXMuX3RpbWVTY2FsZT50fSxuLl9lbmFibGVkPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGF8fHIud2FrZSgpLHRoaXMuX2djPSF0LHRoaXMuX2FjdGl2ZT10aGlzLmlzQWN0aXZlKCksZSE9PSEwJiYodCYmIXRoaXMudGltZWxpbmU/dGhpcy5fdGltZWxpbmUuYWRkKHRoaXMsdGhpcy5fc3RhcnRUaW1lLXRoaXMuX2RlbGF5KTohdCYmdGhpcy50aW1lbGluZSYmdGhpcy5fdGltZWxpbmUuX3JlbW92ZSh0aGlzLCEwKSksITF9LG4uX2tpbGw9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fZW5hYmxlZCghMSwhMSl9LG4ua2lsbD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLl9raWxsKHQsZSksdGhpc30sbi5fdW5jYWNoZT1mdW5jdGlvbih0KXtmb3IodmFyIGU9dD90aGlzOnRoaXMudGltZWxpbmU7ZTspZS5fZGlydHk9ITAsZT1lLnRpbWVsaW5lO3JldHVybiB0aGlzfSxuLl9zd2FwU2VsZkluUGFyYW1zPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10Lmxlbmd0aCxpPXQuY29uY2F0KCk7LS1lPi0xOylcIntzZWxmfVwiPT09dFtlXSYmKGlbZV09dGhpcyk7cmV0dXJuIGl9LG4uZXZlbnRDYWxsYmFjaz1mdW5jdGlvbih0LGUsaSxzKXtpZihcIm9uXCI9PT0odHx8XCJcIikuc3Vic3RyKDAsMikpe3ZhciBuPXRoaXMudmFycztpZigxPT09YXJndW1lbnRzLmxlbmd0aClyZXR1cm4gblt0XTtudWxsPT1lP2RlbGV0ZSBuW3RdOihuW3RdPWUsblt0K1wiUGFyYW1zXCJdPW0oaSkmJi0xIT09aS5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIik/dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhpKTppLG5bdCtcIlNjb3BlXCJdPXMpLFwib25VcGRhdGVcIj09PXQmJih0aGlzLl9vblVwZGF0ZT1lKX1yZXR1cm4gdGhpc30sbi5kZWxheT1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJnRoaXMuc3RhcnRUaW1lKHRoaXMuX3N0YXJ0VGltZSt0LXRoaXMuX2RlbGF5KSx0aGlzLl9kZWxheT10LHRoaXMpOnRoaXMuX2RlbGF5fSxuLmR1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXQsdGhpcy5fdW5jYWNoZSghMCksdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJnRoaXMuX3RpbWU+MCYmdGhpcy5fdGltZTx0aGlzLl9kdXJhdGlvbiYmMCE9PXQmJnRoaXMudG90YWxUaW1lKHRoaXMuX3RvdGFsVGltZSoodC90aGlzLl9kdXJhdGlvbiksITApLHRoaXMpOih0aGlzLl9kaXJ0eT0hMSx0aGlzLl9kdXJhdGlvbil9LG4udG90YWxEdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gdGhpcy5fZGlydHk9ITEsYXJndW1lbnRzLmxlbmd0aD90aGlzLmR1cmF0aW9uKHQpOnRoaXMuX3RvdGFsRHVyYXRpb259LG4udGltZT1mdW5jdGlvbih0LGUpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCksdGhpcy50b3RhbFRpbWUodD50aGlzLl9kdXJhdGlvbj90aGlzLl9kdXJhdGlvbjp0LGUpKTp0aGlzLl90aW1lfSxuLnRvdGFsVGltZT1mdW5jdGlvbih0LGUsaSl7aWYoYXx8ci53YWtlKCksIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3RvdGFsVGltZTtpZih0aGlzLl90aW1lbGluZSl7aWYoMD50JiYhaSYmKHQrPXRoaXMudG90YWxEdXJhdGlvbigpKSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyl7dGhpcy5fZGlydHkmJnRoaXMudG90YWxEdXJhdGlvbigpO3ZhciBzPXRoaXMuX3RvdGFsRHVyYXRpb24sbj10aGlzLl90aW1lbGluZTtpZih0PnMmJiFpJiYodD1zKSx0aGlzLl9zdGFydFRpbWU9KHRoaXMuX3BhdXNlZD90aGlzLl9wYXVzZVRpbWU6bi5fdGltZSktKHRoaXMuX3JldmVyc2VkP3MtdDp0KS90aGlzLl90aW1lU2NhbGUsbi5fZGlydHl8fHRoaXMuX3VuY2FjaGUoITEpLG4uX3RpbWVsaW5lKWZvcig7bi5fdGltZWxpbmU7KW4uX3RpbWVsaW5lLl90aW1lIT09KG4uX3N0YXJ0VGltZStuLl90b3RhbFRpbWUpL24uX3RpbWVTY2FsZSYmbi50b3RhbFRpbWUobi5fdG90YWxUaW1lLCEwKSxuPW4uX3RpbWVsaW5lfXRoaXMuX2djJiZ0aGlzLl9lbmFibGVkKCEwLCExKSwodGhpcy5fdG90YWxUaW1lIT09dHx8MD09PXRoaXMuX2R1cmF0aW9uKSYmKHRoaXMucmVuZGVyKHQsZSwhMSksei5sZW5ndGgmJnEoKSl9cmV0dXJuIHRoaXN9LG4ucHJvZ3Jlc3M9bi50b3RhbFByb2dyZXNzPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/dGhpcy50b3RhbFRpbWUodGhpcy5kdXJhdGlvbigpKnQsZSk6dGhpcy5fdGltZS90aGlzLmR1cmF0aW9uKCl9LG4uc3RhcnRUaW1lPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0IT09dGhpcy5fc3RhcnRUaW1lJiYodGhpcy5fc3RhcnRUaW1lPXQsdGhpcy50aW1lbGluZSYmdGhpcy50aW1lbGluZS5fc29ydENoaWxkcmVuJiZ0aGlzLnRpbWVsaW5lLmFkZCh0aGlzLHQtdGhpcy5fZGVsYXkpKSx0aGlzKTp0aGlzLl9zdGFydFRpbWV9LG4udGltZVNjYWxlPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl90aW1lU2NhbGU7aWYodD10fHxoLHRoaXMuX3RpbWVsaW5lJiZ0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyl7dmFyIGU9dGhpcy5fcGF1c2VUaW1lLGk9ZXx8MD09PWU/ZTp0aGlzLl90aW1lbGluZS50b3RhbFRpbWUoKTt0aGlzLl9zdGFydFRpbWU9aS0oaS10aGlzLl9zdGFydFRpbWUpKnRoaXMuX3RpbWVTY2FsZS90fXJldHVybiB0aGlzLl90aW1lU2NhbGU9dCx0aGlzLl91bmNhY2hlKCExKX0sbi5yZXZlcnNlZD1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odCE9dGhpcy5fcmV2ZXJzZWQmJih0aGlzLl9yZXZlcnNlZD10LHRoaXMudG90YWxUaW1lKHRoaXMuX3RpbWVsaW5lJiYhdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmc/dGhpcy50b3RhbER1cmF0aW9uKCktdGhpcy5fdG90YWxUaW1lOnRoaXMuX3RvdGFsVGltZSwhMCkpLHRoaXMpOnRoaXMuX3JldmVyc2VkfSxuLnBhdXNlZD1mdW5jdGlvbih0KXtpZighYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fcGF1c2VkO2lmKHQhPXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZWxpbmUpe2F8fHR8fHIud2FrZSgpO3ZhciBlPXRoaXMuX3RpbWVsaW5lLGk9ZS5yYXdUaW1lKCkscz1pLXRoaXMuX3BhdXNlVGltZTshdCYmZS5zbW9vdGhDaGlsZFRpbWluZyYmKHRoaXMuX3N0YXJ0VGltZSs9cyx0aGlzLl91bmNhY2hlKCExKSksdGhpcy5fcGF1c2VUaW1lPXQ/aTpudWxsLHRoaXMuX3BhdXNlZD10LHRoaXMuX2FjdGl2ZT10aGlzLmlzQWN0aXZlKCksIXQmJjAhPT1zJiZ0aGlzLl9pbml0dGVkJiZ0aGlzLmR1cmF0aW9uKCkmJnRoaXMucmVuZGVyKGUuc21vb3RoQ2hpbGRUaW1pbmc/dGhpcy5fdG90YWxUaW1lOihpLXRoaXMuX3N0YXJ0VGltZSkvdGhpcy5fdGltZVNjYWxlLCEwLCEwKX1yZXR1cm4gdGhpcy5fZ2MmJiF0JiZ0aGlzLl9lbmFibGVkKCEwLCExKSx0aGlzfTt2YXIgUj1kKFwiY29yZS5TaW1wbGVUaW1lbGluZVwiLGZ1bmN0aW9uKHQpe3guY2FsbCh0aGlzLDAsdCksdGhpcy5hdXRvUmVtb3ZlQ2hpbGRyZW49dGhpcy5zbW9vdGhDaGlsZFRpbWluZz0hMH0pO249Ui5wcm90b3R5cGU9bmV3IHgsbi5jb25zdHJ1Y3Rvcj1SLG4ua2lsbCgpLl9nYz0hMSxuLl9maXJzdD1uLl9sYXN0PW51bGwsbi5fc29ydENoaWxkcmVuPSExLG4uYWRkPW4uaW5zZXJ0PWZ1bmN0aW9uKHQsZSl7dmFyIGkscztpZih0Ll9zdGFydFRpbWU9TnVtYmVyKGV8fDApK3QuX2RlbGF5LHQuX3BhdXNlZCYmdGhpcyE9PXQuX3RpbWVsaW5lJiYodC5fcGF1c2VUaW1lPXQuX3N0YXJ0VGltZSsodGhpcy5yYXdUaW1lKCktdC5fc3RhcnRUaW1lKS90Ll90aW1lU2NhbGUpLHQudGltZWxpbmUmJnQudGltZWxpbmUuX3JlbW92ZSh0LCEwKSx0LnRpbWVsaW5lPXQuX3RpbWVsaW5lPXRoaXMsdC5fZ2MmJnQuX2VuYWJsZWQoITAsITApLGk9dGhpcy5fbGFzdCx0aGlzLl9zb3J0Q2hpbGRyZW4pZm9yKHM9dC5fc3RhcnRUaW1lO2kmJmkuX3N0YXJ0VGltZT5zOylpPWkuX3ByZXY7cmV0dXJuIGk/KHQuX25leHQ9aS5fbmV4dCxpLl9uZXh0PXQpOih0Ll9uZXh0PXRoaXMuX2ZpcnN0LHRoaXMuX2ZpcnN0PXQpLHQuX25leHQ/dC5fbmV4dC5fcHJldj10OnRoaXMuX2xhc3Q9dCx0Ll9wcmV2PWksdGhpcy5fdGltZWxpbmUmJnRoaXMuX3VuY2FjaGUoITApLHRoaXN9LG4uX3JlbW92ZT1mdW5jdGlvbih0LGUpe3JldHVybiB0LnRpbWVsaW5lPT09dGhpcyYmKGV8fHQuX2VuYWJsZWQoITEsITApLHQudGltZWxpbmU9bnVsbCx0Ll9wcmV2P3QuX3ByZXYuX25leHQ9dC5fbmV4dDp0aGlzLl9maXJzdD09PXQmJih0aGlzLl9maXJzdD10Ll9uZXh0KSx0Ll9uZXh0P3QuX25leHQuX3ByZXY9dC5fcHJldjp0aGlzLl9sYXN0PT09dCYmKHRoaXMuX2xhc3Q9dC5fcHJldiksdGhpcy5fdGltZWxpbmUmJnRoaXMuX3VuY2FjaGUoITApKSx0aGlzfSxuLnJlbmRlcj1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbj10aGlzLl9maXJzdDtmb3IodGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9dGhpcy5fcmF3UHJldlRpbWU9dDtuOylzPW4uX25leHQsKG4uX2FjdGl2ZXx8dD49bi5fc3RhcnRUaW1lJiYhbi5fcGF1c2VkKSYmKG4uX3JldmVyc2VkP24ucmVuZGVyKChuLl9kaXJ0eT9uLnRvdGFsRHVyYXRpb24oKTpuLl90b3RhbER1cmF0aW9uKS0odC1uLl9zdGFydFRpbWUpKm4uX3RpbWVTY2FsZSxlLGkpOm4ucmVuZGVyKCh0LW4uX3N0YXJ0VGltZSkqbi5fdGltZVNjYWxlLGUsaSkpLG49c30sbi5yYXdUaW1lPWZ1bmN0aW9uKCl7cmV0dXJuIGF8fHIud2FrZSgpLHRoaXMuX3RvdGFsVGltZX07dmFyIEQ9ZChcIlR3ZWVuTGl0ZVwiLGZ1bmN0aW9uKGUsaSxzKXtpZih4LmNhbGwodGhpcyxpLHMpLHRoaXMucmVuZGVyPUQucHJvdG90eXBlLnJlbmRlcixudWxsPT1lKXRocm93XCJDYW5ub3QgdHdlZW4gYSBudWxsIHRhcmdldC5cIjt0aGlzLnRhcmdldD1lPVwic3RyaW5nXCIhPXR5cGVvZiBlP2U6RC5zZWxlY3RvcihlKXx8ZTt2YXIgbixyLGEsbz1lLmpxdWVyeXx8ZS5sZW5ndGgmJmUhPT10JiZlWzBdJiYoZVswXT09PXR8fGVbMF0ubm9kZVR5cGUmJmVbMF0uc3R5bGUmJiFlLm5vZGVUeXBlKSxsPXRoaXMudmFycy5vdmVyd3JpdGU7aWYodGhpcy5fb3ZlcndyaXRlPWw9bnVsbD09bD9HW0QuZGVmYXVsdE92ZXJ3cml0ZV06XCJudW1iZXJcIj09dHlwZW9mIGw/bD4+MDpHW2xdLChvfHxlIGluc3RhbmNlb2YgQXJyYXl8fGUucHVzaCYmbShlKSkmJlwibnVtYmVyXCIhPXR5cGVvZiBlWzBdKWZvcih0aGlzLl90YXJnZXRzPWE9Xy5jYWxsKGUsMCksdGhpcy5fcHJvcExvb2t1cD1bXSx0aGlzLl9zaWJsaW5ncz1bXSxuPTA7YS5sZW5ndGg+bjtuKyspcj1hW25dLHI/XCJzdHJpbmdcIiE9dHlwZW9mIHI/ci5sZW5ndGgmJnIhPT10JiZyWzBdJiYoclswXT09PXR8fHJbMF0ubm9kZVR5cGUmJnJbMF0uc3R5bGUmJiFyLm5vZGVUeXBlKT8oYS5zcGxpY2Uobi0tLDEpLHRoaXMuX3RhcmdldHM9YT1hLmNvbmNhdChfLmNhbGwociwwKSkpOih0aGlzLl9zaWJsaW5nc1tuXT1NKHIsdGhpcywhMSksMT09PWwmJnRoaXMuX3NpYmxpbmdzW25dLmxlbmd0aD4xJiYkKHIsdGhpcyxudWxsLDEsdGhpcy5fc2libGluZ3Nbbl0pKToocj1hW24tLV09RC5zZWxlY3RvcihyKSxcInN0cmluZ1wiPT10eXBlb2YgciYmYS5zcGxpY2UobisxLDEpKTphLnNwbGljZShuLS0sMSk7ZWxzZSB0aGlzLl9wcm9wTG9va3VwPXt9LHRoaXMuX3NpYmxpbmdzPU0oZSx0aGlzLCExKSwxPT09bCYmdGhpcy5fc2libGluZ3MubGVuZ3RoPjEmJiQoZSx0aGlzLG51bGwsMSx0aGlzLl9zaWJsaW5ncyk7KHRoaXMudmFycy5pbW1lZGlhdGVSZW5kZXJ8fDA9PT1pJiYwPT09dGhpcy5fZGVsYXkmJnRoaXMudmFycy5pbW1lZGlhdGVSZW5kZXIhPT0hMSkmJih0aGlzLl90aW1lPS1oLHRoaXMucmVuZGVyKC10aGlzLl9kZWxheSkpfSwhMCksST1mdW5jdGlvbihlKXtyZXR1cm4gZS5sZW5ndGgmJmUhPT10JiZlWzBdJiYoZVswXT09PXR8fGVbMF0ubm9kZVR5cGUmJmVbMF0uc3R5bGUmJiFlLm5vZGVUeXBlKX0sRT1mdW5jdGlvbih0LGUpe3ZhciBpLHM9e307Zm9yKGkgaW4gdClqW2ldfHxpIGluIGUmJlwidHJhbnNmb3JtXCIhPT1pJiZcInhcIiE9PWkmJlwieVwiIT09aSYmXCJ3aWR0aFwiIT09aSYmXCJoZWlnaHRcIiE9PWkmJlwiY2xhc3NOYW1lXCIhPT1pJiZcImJvcmRlclwiIT09aXx8ISghTFtpXXx8TFtpXSYmTFtpXS5fYXV0b0NTUyl8fChzW2ldPXRbaV0sZGVsZXRlIHRbaV0pO3QuY3NzPXN9O249RC5wcm90b3R5cGU9bmV3IHgsbi5jb25zdHJ1Y3Rvcj1ELG4ua2lsbCgpLl9nYz0hMSxuLnJhdGlvPTAsbi5fZmlyc3RQVD1uLl90YXJnZXRzPW4uX292ZXJ3cml0dGVuUHJvcHM9bi5fc3RhcnRBdD1udWxsLG4uX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9bi5fbGF6eT0hMSxELnZlcnNpb249XCIxLjEyLjFcIixELmRlZmF1bHRFYXNlPW4uX2Vhc2U9bmV3IFQobnVsbCxudWxsLDEsMSksRC5kZWZhdWx0T3ZlcndyaXRlPVwiYXV0b1wiLEQudGlja2VyPXIsRC5hdXRvU2xlZXA9ITAsRC5sYWdTbW9vdGhpbmc9ZnVuY3Rpb24odCxlKXtyLmxhZ1Ntb290aGluZyh0LGUpfSxELnNlbGVjdG9yPXQuJHx8dC5qUXVlcnl8fGZ1bmN0aW9uKGUpe3JldHVybiB0LiQ/KEQuc2VsZWN0b3I9dC4kLHQuJChlKSk6dC5kb2N1bWVudD90LmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFwiI1wiPT09ZS5jaGFyQXQoMCk/ZS5zdWJzdHIoMSk6ZSk6ZX07dmFyIHo9W10sTz17fSxOPUQuX2ludGVybmFscz17aXNBcnJheTptLGlzU2VsZWN0b3I6SSxsYXp5VHdlZW5zOnp9LEw9RC5fcGx1Z2lucz17fSxVPU4udHdlZW5Mb29rdXA9e30sRj0wLGo9Ti5yZXNlcnZlZFByb3BzPXtlYXNlOjEsZGVsYXk6MSxvdmVyd3JpdGU6MSxvbkNvbXBsZXRlOjEsb25Db21wbGV0ZVBhcmFtczoxLG9uQ29tcGxldGVTY29wZToxLHVzZUZyYW1lczoxLHJ1bkJhY2t3YXJkczoxLHN0YXJ0QXQ6MSxvblVwZGF0ZToxLG9uVXBkYXRlUGFyYW1zOjEsb25VcGRhdGVTY29wZToxLG9uU3RhcnQ6MSxvblN0YXJ0UGFyYW1zOjEsb25TdGFydFNjb3BlOjEsb25SZXZlcnNlQ29tcGxldGU6MSxvblJldmVyc2VDb21wbGV0ZVBhcmFtczoxLG9uUmV2ZXJzZUNvbXBsZXRlU2NvcGU6MSxvblJlcGVhdDoxLG9uUmVwZWF0UGFyYW1zOjEsb25SZXBlYXRTY29wZToxLGVhc2VQYXJhbXM6MSx5b3lvOjEsaW1tZWRpYXRlUmVuZGVyOjEscmVwZWF0OjEscmVwZWF0RGVsYXk6MSxkYXRhOjEscGF1c2VkOjEscmV2ZXJzZWQ6MSxhdXRvQ1NTOjEsbGF6eToxfSxHPXtub25lOjAsYWxsOjEsYXV0bzoyLGNvbmN1cnJlbnQ6MyxhbGxPblN0YXJ0OjQscHJlZXhpc3Rpbmc6NSxcInRydWVcIjoxLFwiZmFsc2VcIjowfSxRPXguX3Jvb3RGcmFtZXNUaW1lbGluZT1uZXcgUixCPXguX3Jvb3RUaW1lbGluZT1uZXcgUixxPWZ1bmN0aW9uKCl7dmFyIHQ9ei5sZW5ndGg7Zm9yKE89e307LS10Pi0xOylpPXpbdF0saSYmaS5fbGF6eSE9PSExJiYoaS5yZW5kZXIoaS5fbGF6eSwhMSwhMCksaS5fbGF6eT0hMSk7ei5sZW5ndGg9MH07Qi5fc3RhcnRUaW1lPXIudGltZSxRLl9zdGFydFRpbWU9ci5mcmFtZSxCLl9hY3RpdmU9US5fYWN0aXZlPSEwLHNldFRpbWVvdXQocSwxKSx4Ll91cGRhdGVSb290PUQucmVuZGVyPWZ1bmN0aW9uKCl7dmFyIHQsZSxpO2lmKHoubGVuZ3RoJiZxKCksQi5yZW5kZXIoKHIudGltZS1CLl9zdGFydFRpbWUpKkIuX3RpbWVTY2FsZSwhMSwhMSksUS5yZW5kZXIoKHIuZnJhbWUtUS5fc3RhcnRUaW1lKSpRLl90aW1lU2NhbGUsITEsITEpLHoubGVuZ3RoJiZxKCksIShyLmZyYW1lJTEyMCkpe2ZvcihpIGluIFUpe2ZvcihlPVVbaV0udHdlZW5zLHQ9ZS5sZW5ndGg7LS10Pi0xOyllW3RdLl9nYyYmZS5zcGxpY2UodCwxKTswPT09ZS5sZW5ndGgmJmRlbGV0ZSBVW2ldfWlmKGk9Qi5fZmlyc3QsKCFpfHxpLl9wYXVzZWQpJiZELmF1dG9TbGVlcCYmIVEuX2ZpcnN0JiYxPT09ci5fbGlzdGVuZXJzLnRpY2subGVuZ3RoKXtmb3IoO2kmJmkuX3BhdXNlZDspaT1pLl9uZXh0O2l8fHIuc2xlZXAoKX19fSxyLmFkZEV2ZW50TGlzdGVuZXIoXCJ0aWNrXCIseC5fdXBkYXRlUm9vdCk7dmFyIE09ZnVuY3Rpb24odCxlLGkpe3ZhciBzLG4scj10Ll9nc1R3ZWVuSUQ7aWYoVVtyfHwodC5fZ3NUd2VlbklEPXI9XCJ0XCIrRisrKV18fChVW3JdPXt0YXJnZXQ6dCx0d2VlbnM6W119KSxlJiYocz1VW3JdLnR3ZWVucyxzW249cy5sZW5ndGhdPWUsaSkpZm9yKDstLW4+LTE7KXNbbl09PT1lJiZzLnNwbGljZShuLDEpO3JldHVybiBVW3JdLnR3ZWVuc30sJD1mdW5jdGlvbih0LGUsaSxzLG4pe3ZhciByLGEsbyxsO2lmKDE9PT1zfHxzPj00KXtmb3IobD1uLmxlbmd0aCxyPTA7bD5yO3IrKylpZigobz1uW3JdKSE9PWUpby5fZ2N8fG8uX2VuYWJsZWQoITEsITEpJiYoYT0hMCk7ZWxzZSBpZig1PT09cylicmVhaztyZXR1cm4gYX12YXIgXyx1PWUuX3N0YXJ0VGltZStoLG09W10sZj0wLHA9MD09PWUuX2R1cmF0aW9uO2ZvcihyPW4ubGVuZ3RoOy0tcj4tMTspKG89bltyXSk9PT1lfHxvLl9nY3x8by5fcGF1c2VkfHwoby5fdGltZWxpbmUhPT1lLl90aW1lbGluZT8oXz1ffHxLKGUsMCxwKSwwPT09SyhvLF8scCkmJihtW2YrK109bykpOnU+PW8uX3N0YXJ0VGltZSYmby5fc3RhcnRUaW1lK28udG90YWxEdXJhdGlvbigpL28uX3RpbWVTY2FsZT51JiYoKHB8fCFvLl9pbml0dGVkKSYmMmUtMTA+PXUtby5fc3RhcnRUaW1lfHwobVtmKytdPW8pKSk7Zm9yKHI9ZjstLXI+LTE7KW89bVtyXSwyPT09cyYmby5fa2lsbChpLHQpJiYoYT0hMCksKDIhPT1zfHwhby5fZmlyc3RQVCYmby5faW5pdHRlZCkmJm8uX2VuYWJsZWQoITEsITEpJiYoYT0hMCk7cmV0dXJuIGF9LEs9ZnVuY3Rpb24odCxlLGkpe2Zvcih2YXIgcz10Ll90aW1lbGluZSxuPXMuX3RpbWVTY2FsZSxyPXQuX3N0YXJ0VGltZTtzLl90aW1lbGluZTspe2lmKHIrPXMuX3N0YXJ0VGltZSxuKj1zLl90aW1lU2NhbGUscy5fcGF1c2VkKXJldHVybi0xMDA7cz1zLl90aW1lbGluZX1yZXR1cm4gci89bixyPmU/ci1lOmkmJnI9PT1lfHwhdC5faW5pdHRlZCYmMipoPnItZT9oOihyKz10LnRvdGFsRHVyYXRpb24oKS90Ll90aW1lU2NhbGUvbik+ZStoPzA6ci1lLWh9O24uX2luaXQ9ZnVuY3Rpb24oKXt2YXIgdCxlLGkscyxuLHI9dGhpcy52YXJzLGE9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcyxvPXRoaXMuX2R1cmF0aW9uLGw9ISFyLmltbWVkaWF0ZVJlbmRlcixoPXIuZWFzZTtpZihyLnN0YXJ0QXQpe3RoaXMuX3N0YXJ0QXQmJih0aGlzLl9zdGFydEF0LnJlbmRlcigtMSwhMCksdGhpcy5fc3RhcnRBdC5raWxsKCkpLG49e307Zm9yKHMgaW4gci5zdGFydEF0KW5bc109ci5zdGFydEF0W3NdO2lmKG4ub3ZlcndyaXRlPSExLG4uaW1tZWRpYXRlUmVuZGVyPSEwLG4ubGF6eT1sJiZyLmxhenkhPT0hMSxuLnN0YXJ0QXQ9bi5kZWxheT1udWxsLHRoaXMuX3N0YXJ0QXQ9RC50byh0aGlzLnRhcmdldCwwLG4pLGwpaWYodGhpcy5fdGltZT4wKXRoaXMuX3N0YXJ0QXQ9bnVsbDtlbHNlIGlmKDAhPT1vKXJldHVybn1lbHNlIGlmKHIucnVuQmFja3dhcmRzJiYwIT09bylpZih0aGlzLl9zdGFydEF0KXRoaXMuX3N0YXJ0QXQucmVuZGVyKC0xLCEwKSx0aGlzLl9zdGFydEF0LmtpbGwoKSx0aGlzLl9zdGFydEF0PW51bGw7ZWxzZXtpPXt9O2ZvcihzIGluIHIpaltzXSYmXCJhdXRvQ1NTXCIhPT1zfHwoaVtzXT1yW3NdKTtpZihpLm92ZXJ3cml0ZT0wLGkuZGF0YT1cImlzRnJvbVN0YXJ0XCIsaS5sYXp5PWwmJnIubGF6eSE9PSExLGkuaW1tZWRpYXRlUmVuZGVyPWwsdGhpcy5fc3RhcnRBdD1ELnRvKHRoaXMudGFyZ2V0LDAsaSksbCl7aWYoMD09PXRoaXMuX3RpbWUpcmV0dXJufWVsc2UgdGhpcy5fc3RhcnRBdC5faW5pdCgpLHRoaXMuX3N0YXJ0QXQuX2VuYWJsZWQoITEpfWlmKHRoaXMuX2Vhc2U9aD9oIGluc3RhbmNlb2YgVD9yLmVhc2VQYXJhbXMgaW5zdGFuY2VvZiBBcnJheT9oLmNvbmZpZy5hcHBseShoLHIuZWFzZVBhcmFtcyk6aDpcImZ1bmN0aW9uXCI9PXR5cGVvZiBoP25ldyBUKGgsci5lYXNlUGFyYW1zKTp5W2hdfHxELmRlZmF1bHRFYXNlOkQuZGVmYXVsdEVhc2UsdGhpcy5fZWFzZVR5cGU9dGhpcy5fZWFzZS5fdHlwZSx0aGlzLl9lYXNlUG93ZXI9dGhpcy5fZWFzZS5fcG93ZXIsdGhpcy5fZmlyc3RQVD1udWxsLHRoaXMuX3RhcmdldHMpZm9yKHQ9dGhpcy5fdGFyZ2V0cy5sZW5ndGg7LS10Pi0xOyl0aGlzLl9pbml0UHJvcHModGhpcy5fdGFyZ2V0c1t0XSx0aGlzLl9wcm9wTG9va3VwW3RdPXt9LHRoaXMuX3NpYmxpbmdzW3RdLGE/YVt0XTpudWxsKSYmKGU9ITApO2Vsc2UgZT10aGlzLl9pbml0UHJvcHModGhpcy50YXJnZXQsdGhpcy5fcHJvcExvb2t1cCx0aGlzLl9zaWJsaW5ncyxhKTtpZihlJiZELl9vblBsdWdpbkV2ZW50KFwiX29uSW5pdEFsbFByb3BzXCIsdGhpcyksYSYmKHRoaXMuX2ZpcnN0UFR8fFwiZnVuY3Rpb25cIiE9dHlwZW9mIHRoaXMudGFyZ2V0JiZ0aGlzLl9lbmFibGVkKCExLCExKSksci5ydW5CYWNrd2FyZHMpZm9yKGk9dGhpcy5fZmlyc3RQVDtpOylpLnMrPWkuYyxpLmM9LWkuYyxpPWkuX25leHQ7dGhpcy5fb25VcGRhdGU9ci5vblVwZGF0ZSx0aGlzLl9pbml0dGVkPSEwfSxuLl9pbml0UHJvcHM9ZnVuY3Rpb24oZSxpLHMsbil7dmFyIHIsYSxvLGwsaCxfO2lmKG51bGw9PWUpcmV0dXJuITE7T1tlLl9nc1R3ZWVuSURdJiZxKCksdGhpcy52YXJzLmNzc3x8ZS5zdHlsZSYmZSE9PXQmJmUubm9kZVR5cGUmJkwuY3NzJiZ0aGlzLnZhcnMuYXV0b0NTUyE9PSExJiZFKHRoaXMudmFycyxlKTtmb3IociBpbiB0aGlzLnZhcnMpe2lmKF89dGhpcy52YXJzW3JdLGpbcl0pXyYmKF8gaW5zdGFuY2VvZiBBcnJheXx8Xy5wdXNoJiZtKF8pKSYmLTEhPT1fLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKSYmKHRoaXMudmFyc1tyXT1fPXRoaXMuX3N3YXBTZWxmSW5QYXJhbXMoXyx0aGlzKSk7ZWxzZSBpZihMW3JdJiYobD1uZXcgTFtyXSkuX29uSW5pdFR3ZWVuKGUsdGhpcy52YXJzW3JdLHRoaXMpKXtmb3IodGhpcy5fZmlyc3RQVD1oPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6bCxwOlwic2V0UmF0aW9cIixzOjAsYzoxLGY6ITAsbjpyLHBnOiEwLHByOmwuX3ByaW9yaXR5fSxhPWwuX292ZXJ3cml0ZVByb3BzLmxlbmd0aDstLWE+LTE7KWlbbC5fb3ZlcndyaXRlUHJvcHNbYV1dPXRoaXMuX2ZpcnN0UFQ7KGwuX3ByaW9yaXR5fHxsLl9vbkluaXRBbGxQcm9wcykmJihvPSEwKSwobC5fb25EaXNhYmxlfHxsLl9vbkVuYWJsZSkmJih0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkPSEwKX1lbHNlIHRoaXMuX2ZpcnN0UFQ9aVtyXT1oPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6ZSxwOnIsZjpcImZ1bmN0aW9uXCI9PXR5cGVvZiBlW3JdLG46cixwZzohMSxwcjowfSxoLnM9aC5mP2Vbci5pbmRleE9mKFwic2V0XCIpfHxcImZ1bmN0aW9uXCIhPXR5cGVvZiBlW1wiZ2V0XCIrci5zdWJzdHIoMyldP3I6XCJnZXRcIityLnN1YnN0cigzKV0oKTpwYXJzZUZsb2F0KGVbcl0pLGguYz1cInN0cmluZ1wiPT10eXBlb2YgXyYmXCI9XCI9PT1fLmNoYXJBdCgxKT9wYXJzZUludChfLmNoYXJBdCgwKStcIjFcIiwxMCkqTnVtYmVyKF8uc3Vic3RyKDIpKTpOdW1iZXIoXyktaC5zfHwwO2gmJmguX25leHQmJihoLl9uZXh0Ll9wcmV2PWgpfXJldHVybiBuJiZ0aGlzLl9raWxsKG4sZSk/dGhpcy5faW5pdFByb3BzKGUsaSxzLG4pOnRoaXMuX292ZXJ3cml0ZT4xJiZ0aGlzLl9maXJzdFBUJiZzLmxlbmd0aD4xJiYkKGUsdGhpcyxpLHRoaXMuX292ZXJ3cml0ZSxzKT8odGhpcy5fa2lsbChpLGUpLHRoaXMuX2luaXRQcm9wcyhlLGkscyxuKSk6KHRoaXMuX2ZpcnN0UFQmJih0aGlzLnZhcnMubGF6eSE9PSExJiZ0aGlzLl9kdXJhdGlvbnx8dGhpcy52YXJzLmxhenkmJiF0aGlzLl9kdXJhdGlvbikmJihPW2UuX2dzVHdlZW5JRF09ITApLG8pfSxuLnJlbmRlcj1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbixyLGEsbz10aGlzLl90aW1lLGw9dGhpcy5fZHVyYXRpb24sXz10aGlzLl9yYXdQcmV2VGltZTtpZih0Pj1sKXRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPWwsdGhpcy5yYXRpbz10aGlzLl9lYXNlLl9jYWxjRW5kP3RoaXMuX2Vhc2UuZ2V0UmF0aW8oMSk6MSx0aGlzLl9yZXZlcnNlZHx8KHM9ITAsbj1cIm9uQ29tcGxldGVcIiksMD09PWwmJih0aGlzLl9pbml0dGVkfHwhdGhpcy52YXJzLmxhenl8fGkpJiYodGhpcy5fc3RhcnRUaW1lPT09dGhpcy5fdGltZWxpbmUuX2R1cmF0aW9uJiYodD0wKSwoMD09PXR8fDA+X3x8Xz09PWgpJiZfIT09dCYmKGk9ITAsXz5oJiYobj1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIpKSx0aGlzLl9yYXdQcmV2VGltZT1hPSFlfHx0fHxfPT09dD90OmgpO2Vsc2UgaWYoMWUtNz50KXRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPTAsdGhpcy5yYXRpbz10aGlzLl9lYXNlLl9jYWxjRW5kP3RoaXMuX2Vhc2UuZ2V0UmF0aW8oMCk6MCwoMCE9PW98fDA9PT1sJiZfPjAmJl8hPT1oKSYmKG49XCJvblJldmVyc2VDb21wbGV0ZVwiLHM9dGhpcy5fcmV2ZXJzZWQpLDA+dD8odGhpcy5fYWN0aXZlPSExLDA9PT1sJiYodGhpcy5faW5pdHRlZHx8IXRoaXMudmFycy5sYXp5fHxpKSYmKF8+PTAmJihpPSEwKSx0aGlzLl9yYXdQcmV2VGltZT1hPSFlfHx0fHxfPT09dD90OmgpKTp0aGlzLl9pbml0dGVkfHwoaT0hMCk7ZWxzZSBpZih0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10LHRoaXMuX2Vhc2VUeXBlKXt2YXIgdT10L2wsbT10aGlzLl9lYXNlVHlwZSxmPXRoaXMuX2Vhc2VQb3dlcjsoMT09PW18fDM9PT1tJiZ1Pj0uNSkmJih1PTEtdSksMz09PW0mJih1Kj0yKSwxPT09Zj91Kj11OjI9PT1mP3UqPXUqdTozPT09Zj91Kj11KnUqdTo0PT09ZiYmKHUqPXUqdSp1KnUpLHRoaXMucmF0aW89MT09PW0/MS11OjI9PT1tP3U6LjU+dC9sP3UvMjoxLXUvMn1lbHNlIHRoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbyh0L2wpO2lmKHRoaXMuX3RpbWUhPT1vfHxpKXtpZighdGhpcy5faW5pdHRlZCl7aWYodGhpcy5faW5pdCgpLCF0aGlzLl9pbml0dGVkfHx0aGlzLl9nYylyZXR1cm47aWYoIWkmJnRoaXMuX2ZpcnN0UFQmJih0aGlzLnZhcnMubGF6eSE9PSExJiZ0aGlzLl9kdXJhdGlvbnx8dGhpcy52YXJzLmxhenkmJiF0aGlzLl9kdXJhdGlvbikpcmV0dXJuIHRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPW8sdGhpcy5fcmF3UHJldlRpbWU9Xyx6LnB1c2godGhpcyksdGhpcy5fbGF6eT10LHZvaWQgMDt0aGlzLl90aW1lJiYhcz90aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8odGhpcy5fdGltZS9sKTpzJiZ0aGlzLl9lYXNlLl9jYWxjRW5kJiYodGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKDA9PT10aGlzLl90aW1lPzA6MSkpfWZvcih0aGlzLl9sYXp5IT09ITEmJih0aGlzLl9sYXp5PSExKSx0aGlzLl9hY3RpdmV8fCF0aGlzLl9wYXVzZWQmJnRoaXMuX3RpbWUhPT1vJiZ0Pj0wJiYodGhpcy5fYWN0aXZlPSEwKSwwPT09byYmKHRoaXMuX3N0YXJ0QXQmJih0Pj0wP3RoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKTpufHwobj1cIl9kdW1teUdTXCIpKSx0aGlzLnZhcnMub25TdGFydCYmKDAhPT10aGlzLl90aW1lfHwwPT09bCkmJihlfHx0aGlzLnZhcnMub25TdGFydC5hcHBseSh0aGlzLnZhcnMub25TdGFydFNjb3BlfHx0aGlzLHRoaXMudmFycy5vblN0YXJ0UGFyYW1zfHxnKSkpLHI9dGhpcy5fZmlyc3RQVDtyOylyLmY/ci50W3IucF0oci5jKnRoaXMucmF0aW8rci5zKTpyLnRbci5wXT1yLmMqdGhpcy5yYXRpbytyLnMscj1yLl9uZXh0O3RoaXMuX29uVXBkYXRlJiYoMD50JiZ0aGlzLl9zdGFydEF0JiZ0aGlzLl9zdGFydFRpbWUmJnRoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKSxlfHwodGhpcy5fdGltZSE9PW98fHMpJiZ0aGlzLl9vblVwZGF0ZS5hcHBseSh0aGlzLnZhcnMub25VcGRhdGVTY29wZXx8dGhpcyx0aGlzLnZhcnMub25VcGRhdGVQYXJhbXN8fGcpKSxuJiYodGhpcy5fZ2N8fCgwPnQmJnRoaXMuX3N0YXJ0QXQmJiF0aGlzLl9vblVwZGF0ZSYmdGhpcy5fc3RhcnRUaW1lJiZ0aGlzLl9zdGFydEF0LnJlbmRlcih0LGUsaSkscyYmKHRoaXMuX3RpbWVsaW5lLmF1dG9SZW1vdmVDaGlsZHJlbiYmdGhpcy5fZW5hYmxlZCghMSwhMSksdGhpcy5fYWN0aXZlPSExKSwhZSYmdGhpcy52YXJzW25dJiZ0aGlzLnZhcnNbbl0uYXBwbHkodGhpcy52YXJzW24rXCJTY29wZVwiXXx8dGhpcyx0aGlzLnZhcnNbbitcIlBhcmFtc1wiXXx8ZyksMD09PWwmJnRoaXMuX3Jhd1ByZXZUaW1lPT09aCYmYSE9PWgmJih0aGlzLl9yYXdQcmV2VGltZT0wKSkpfX0sbi5fa2lsbD1mdW5jdGlvbih0LGUpe2lmKFwiYWxsXCI9PT10JiYodD1udWxsKSxudWxsPT10JiYobnVsbD09ZXx8ZT09PXRoaXMudGFyZ2V0KSlyZXR1cm4gdGhpcy5fbGF6eT0hMSx0aGlzLl9lbmFibGVkKCExLCExKTtlPVwic3RyaW5nXCIhPXR5cGVvZiBlP2V8fHRoaXMuX3RhcmdldHN8fHRoaXMudGFyZ2V0OkQuc2VsZWN0b3IoZSl8fGU7dmFyIGkscyxuLHIsYSxvLGwsaDtpZigobShlKXx8SShlKSkmJlwibnVtYmVyXCIhPXR5cGVvZiBlWzBdKWZvcihpPWUubGVuZ3RoOy0taT4tMTspdGhpcy5fa2lsbCh0LGVbaV0pJiYobz0hMCk7ZWxzZXtpZih0aGlzLl90YXJnZXRzKXtmb3IoaT10aGlzLl90YXJnZXRzLmxlbmd0aDstLWk+LTE7KWlmKGU9PT10aGlzLl90YXJnZXRzW2ldKXthPXRoaXMuX3Byb3BMb29rdXBbaV18fHt9LHRoaXMuX292ZXJ3cml0dGVuUHJvcHM9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc3x8W10scz10aGlzLl9vdmVyd3JpdHRlblByb3BzW2ldPXQ/dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc1tpXXx8e306XCJhbGxcIjticmVha319ZWxzZXtpZihlIT09dGhpcy50YXJnZXQpcmV0dXJuITE7YT10aGlzLl9wcm9wTG9va3VwLHM9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz10P3RoaXMuX292ZXJ3cml0dGVuUHJvcHN8fHt9OlwiYWxsXCJ9aWYoYSl7bD10fHxhLGg9dCE9PXMmJlwiYWxsXCIhPT1zJiZ0IT09YSYmKFwib2JqZWN0XCIhPXR5cGVvZiB0fHwhdC5fdGVtcEtpbGwpO2ZvcihuIGluIGwpKHI9YVtuXSkmJihyLnBnJiZyLnQuX2tpbGwobCkmJihvPSEwKSxyLnBnJiYwIT09ci50Ll9vdmVyd3JpdGVQcm9wcy5sZW5ndGh8fChyLl9wcmV2P3IuX3ByZXYuX25leHQ9ci5fbmV4dDpyPT09dGhpcy5fZmlyc3RQVCYmKHRoaXMuX2ZpcnN0UFQ9ci5fbmV4dCksci5fbmV4dCYmKHIuX25leHQuX3ByZXY9ci5fcHJldiksci5fbmV4dD1yLl9wcmV2PW51bGwpLGRlbGV0ZSBhW25dKSxoJiYoc1tuXT0xKTshdGhpcy5fZmlyc3RQVCYmdGhpcy5faW5pdHRlZCYmdGhpcy5fZW5hYmxlZCghMSwhMSl9fXJldHVybiBvfSxuLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZCYmRC5fb25QbHVnaW5FdmVudChcIl9vbkRpc2FibGVcIix0aGlzKSx0aGlzLl9maXJzdFBUPW51bGwsdGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz1udWxsLHRoaXMuX29uVXBkYXRlPW51bGwsdGhpcy5fc3RhcnRBdD1udWxsLHRoaXMuX2luaXR0ZWQ9dGhpcy5fYWN0aXZlPXRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9dGhpcy5fbGF6eT0hMSx0aGlzLl9wcm9wTG9va3VwPXRoaXMuX3RhcmdldHM/e306W10sdGhpc30sbi5fZW5hYmxlZD1mdW5jdGlvbih0LGUpe2lmKGF8fHIud2FrZSgpLHQmJnRoaXMuX2djKXt2YXIgaSxzPXRoaXMuX3RhcmdldHM7aWYocylmb3IoaT1zLmxlbmd0aDstLWk+LTE7KXRoaXMuX3NpYmxpbmdzW2ldPU0oc1tpXSx0aGlzLCEwKTtlbHNlIHRoaXMuX3NpYmxpbmdzPU0odGhpcy50YXJnZXQsdGhpcywhMCl9cmV0dXJuIHgucHJvdG90eXBlLl9lbmFibGVkLmNhbGwodGhpcyx0LGUpLHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQmJnRoaXMuX2ZpcnN0UFQ/RC5fb25QbHVnaW5FdmVudCh0P1wiX29uRW5hYmxlXCI6XCJfb25EaXNhYmxlXCIsdGhpcyk6ITF9LEQudG89ZnVuY3Rpb24odCxlLGkpe3JldHVybiBuZXcgRCh0LGUsaSl9LEQuZnJvbT1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIGkucnVuQmFja3dhcmRzPSEwLGkuaW1tZWRpYXRlUmVuZGVyPTAhPWkuaW1tZWRpYXRlUmVuZGVyLG5ldyBEKHQsZSxpKX0sRC5mcm9tVG89ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHMuc3RhcnRBdD1pLHMuaW1tZWRpYXRlUmVuZGVyPTAhPXMuaW1tZWRpYXRlUmVuZGVyJiYwIT1pLmltbWVkaWF0ZVJlbmRlcixuZXcgRCh0LGUscyl9LEQuZGVsYXllZENhbGw9ZnVuY3Rpb24odCxlLGkscyxuKXtyZXR1cm4gbmV3IEQoZSwwLHtkZWxheTp0LG9uQ29tcGxldGU6ZSxvbkNvbXBsZXRlUGFyYW1zOmksb25Db21wbGV0ZVNjb3BlOnMsb25SZXZlcnNlQ29tcGxldGU6ZSxvblJldmVyc2VDb21wbGV0ZVBhcmFtczppLG9uUmV2ZXJzZUNvbXBsZXRlU2NvcGU6cyxpbW1lZGlhdGVSZW5kZXI6ITEsdXNlRnJhbWVzOm4sb3ZlcndyaXRlOjB9KX0sRC5zZXQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbmV3IEQodCwwLGUpfSxELmdldFR3ZWVuc09mPWZ1bmN0aW9uKHQsZSl7aWYobnVsbD09dClyZXR1cm5bXTt0PVwic3RyaW5nXCIhPXR5cGVvZiB0P3Q6RC5zZWxlY3Rvcih0KXx8dDt2YXIgaSxzLG4scjtpZigobSh0KXx8SSh0KSkmJlwibnVtYmVyXCIhPXR5cGVvZiB0WzBdKXtmb3IoaT10Lmxlbmd0aCxzPVtdOy0taT4tMTspcz1zLmNvbmNhdChELmdldFR3ZWVuc09mKHRbaV0sZSkpO2ZvcihpPXMubGVuZ3RoOy0taT4tMTspZm9yKHI9c1tpXSxuPWk7LS1uPi0xOylyPT09c1tuXSYmcy5zcGxpY2UoaSwxKX1lbHNlIGZvcihzPU0odCkuY29uY2F0KCksaT1zLmxlbmd0aDstLWk+LTE7KShzW2ldLl9nY3x8ZSYmIXNbaV0uaXNBY3RpdmUoKSkmJnMuc3BsaWNlKGksMSk7cmV0dXJuIHN9LEQua2lsbFR3ZWVuc09mPUQua2lsbERlbGF5ZWRDYWxsc1RvPWZ1bmN0aW9uKHQsZSxpKXtcIm9iamVjdFwiPT10eXBlb2YgZSYmKGk9ZSxlPSExKTtmb3IodmFyIHM9RC5nZXRUd2VlbnNPZih0LGUpLG49cy5sZW5ndGg7LS1uPi0xOylzW25dLl9raWxsKGksdCl9O3ZhciBIPWQoXCJwbHVnaW5zLlR3ZWVuUGx1Z2luXCIsZnVuY3Rpb24odCxlKXt0aGlzLl9vdmVyd3JpdGVQcm9wcz0odHx8XCJcIikuc3BsaXQoXCIsXCIpLHRoaXMuX3Byb3BOYW1lPXRoaXMuX292ZXJ3cml0ZVByb3BzWzBdLHRoaXMuX3ByaW9yaXR5PWV8fDAsdGhpcy5fc3VwZXI9SC5wcm90b3R5cGV9LCEwKTtpZihuPUgucHJvdG90eXBlLEgudmVyc2lvbj1cIjEuMTAuMVwiLEguQVBJPTIsbi5fZmlyc3RQVD1udWxsLG4uX2FkZFR3ZWVuPWZ1bmN0aW9uKHQsZSxpLHMsbixyKXt2YXIgYSxvO3JldHVybiBudWxsIT1zJiYoYT1cIm51bWJlclwiPT10eXBlb2Ygc3x8XCI9XCIhPT1zLmNoYXJBdCgxKT9OdW1iZXIocyktaTpwYXJzZUludChzLmNoYXJBdCgwKStcIjFcIiwxMCkqTnVtYmVyKHMuc3Vic3RyKDIpKSk/KHRoaXMuX2ZpcnN0UFQ9bz17X25leHQ6dGhpcy5fZmlyc3RQVCx0OnQscDplLHM6aSxjOmEsZjpcImZ1bmN0aW9uXCI9PXR5cGVvZiB0W2VdLG46bnx8ZSxyOnJ9LG8uX25leHQmJihvLl9uZXh0Ll9wcmV2PW8pLG8pOnZvaWQgMH0sbi5zZXRSYXRpbz1mdW5jdGlvbih0KXtmb3IodmFyIGUsaT10aGlzLl9maXJzdFBULHM9MWUtNjtpOyllPWkuYyp0K2kucyxpLnI/ZT1NYXRoLnJvdW5kKGUpOnM+ZSYmZT4tcyYmKGU9MCksaS5mP2kudFtpLnBdKGUpOmkudFtpLnBdPWUsaT1pLl9uZXh0fSxuLl9raWxsPWZ1bmN0aW9uKHQpe3ZhciBlLGk9dGhpcy5fb3ZlcndyaXRlUHJvcHMscz10aGlzLl9maXJzdFBUO2lmKG51bGwhPXRbdGhpcy5fcHJvcE5hbWVdKXRoaXMuX292ZXJ3cml0ZVByb3BzPVtdO2Vsc2UgZm9yKGU9aS5sZW5ndGg7LS1lPi0xOyludWxsIT10W2lbZV1dJiZpLnNwbGljZShlLDEpO2Zvcig7czspbnVsbCE9dFtzLm5dJiYocy5fbmV4dCYmKHMuX25leHQuX3ByZXY9cy5fcHJldikscy5fcHJldj8ocy5fcHJldi5fbmV4dD1zLl9uZXh0LHMuX3ByZXY9bnVsbCk6dGhpcy5fZmlyc3RQVD09PXMmJih0aGlzLl9maXJzdFBUPXMuX25leHQpKSxzPXMuX25leHQ7cmV0dXJuITF9LG4uX3JvdW5kUHJvcHM9ZnVuY3Rpb24odCxlKXtmb3IodmFyIGk9dGhpcy5fZmlyc3RQVDtpOykodFt0aGlzLl9wcm9wTmFtZV18fG51bGwhPWkubiYmdFtpLm4uc3BsaXQodGhpcy5fcHJvcE5hbWUrXCJfXCIpLmpvaW4oXCJcIildKSYmKGkucj1lKSxpPWkuX25leHR9LEQuX29uUGx1Z2luRXZlbnQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxzLG4scixhLG89ZS5fZmlyc3RQVDtpZihcIl9vbkluaXRBbGxQcm9wc1wiPT09dCl7Zm9yKDtvOyl7Zm9yKGE9by5fbmV4dCxzPW47cyYmcy5wcj5vLnByOylzPXMuX25leHQ7KG8uX3ByZXY9cz9zLl9wcmV2OnIpP28uX3ByZXYuX25leHQ9bzpuPW8sKG8uX25leHQ9cyk/cy5fcHJldj1vOnI9byxvPWF9bz1lLl9maXJzdFBUPW59Zm9yKDtvOylvLnBnJiZcImZ1bmN0aW9uXCI9PXR5cGVvZiBvLnRbdF0mJm8udFt0XSgpJiYoaT0hMCksbz1vLl9uZXh0O3JldHVybiBpfSxILmFjdGl2YXRlPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10Lmxlbmd0aDstLWU+LTE7KXRbZV0uQVBJPT09SC5BUEkmJihMWyhuZXcgdFtlXSkuX3Byb3BOYW1lXT10W2VdKTtyZXR1cm4hMH0sYy5wbHVnaW49ZnVuY3Rpb24odCl7aWYoISh0JiZ0LnByb3BOYW1lJiZ0LmluaXQmJnQuQVBJKSl0aHJvd1wiaWxsZWdhbCBwbHVnaW4gZGVmaW5pdGlvbi5cIjt2YXIgZSxpPXQucHJvcE5hbWUscz10LnByaW9yaXR5fHwwLG49dC5vdmVyd3JpdGVQcm9wcyxyPXtpbml0OlwiX29uSW5pdFR3ZWVuXCIsc2V0Olwic2V0UmF0aW9cIixraWxsOlwiX2tpbGxcIixyb3VuZDpcIl9yb3VuZFByb3BzXCIsaW5pdEFsbDpcIl9vbkluaXRBbGxQcm9wc1wifSxhPWQoXCJwbHVnaW5zLlwiK2kuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkraS5zdWJzdHIoMSkrXCJQbHVnaW5cIixmdW5jdGlvbigpe0guY2FsbCh0aGlzLGkscyksdGhpcy5fb3ZlcndyaXRlUHJvcHM9bnx8W119LHQuZ2xvYmFsPT09ITApLG89YS5wcm90b3R5cGU9bmV3IEgoaSk7by5jb25zdHJ1Y3Rvcj1hLGEuQVBJPXQuQVBJO2ZvcihlIGluIHIpXCJmdW5jdGlvblwiPT10eXBlb2YgdFtlXSYmKG9bcltlXV09dFtlXSk7cmV0dXJuIGEudmVyc2lvbj10LnZlcnNpb24sSC5hY3RpdmF0ZShbYV0pLGF9LGk9dC5fZ3NRdWV1ZSl7Zm9yKHM9MDtpLmxlbmd0aD5zO3MrKylpW3NdKCk7Zm9yKG4gaW4gZilmW25dLmZ1bmN8fHQuY29uc29sZS5sb2coXCJHU0FQIGVuY291bnRlcmVkIG1pc3NpbmcgZGVwZW5kZW5jeTogY29tLmdyZWVuc29jay5cIituKX1hPSExfX0pKHdpbmRvdyk7IiwiLyohXHJcbiAqIFZFUlNJT046IGJldGEgMS45LjNcclxuICogREFURTogMjAxMy0wNC0wMlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICoqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJlYXNpbmcuQmFja1wiLFtcImVhc2luZy5FYXNlXCJdLGZ1bmN0aW9uKHQpe3ZhciBlLGkscyxyPXdpbmRvdy5HcmVlblNvY2tHbG9iYWxzfHx3aW5kb3csbj1yLmNvbS5ncmVlbnNvY2ssYT0yKk1hdGguUEksbz1NYXRoLlBJLzIsaD1uLl9jbGFzcyxsPWZ1bmN0aW9uKGUsaSl7dmFyIHM9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKCl7fSwhMCkscj1zLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gci5jb25zdHJ1Y3Rvcj1zLHIuZ2V0UmF0aW89aSxzfSxfPXQucmVnaXN0ZXJ8fGZ1bmN0aW9uKCl7fSx1PWZ1bmN0aW9uKHQsZSxpLHMpe3ZhciByPWgoXCJlYXNpbmcuXCIrdCx7ZWFzZU91dDpuZXcgZSxlYXNlSW46bmV3IGksZWFzZUluT3V0Om5ldyBzfSwhMCk7cmV0dXJuIF8ocix0KSxyfSxjPWZ1bmN0aW9uKHQsZSxpKXt0aGlzLnQ9dCx0aGlzLnY9ZSxpJiYodGhpcy5uZXh0PWksaS5wcmV2PXRoaXMsdGhpcy5jPWkudi1lLHRoaXMuZ2FwPWkudC10KX0sZj1mdW5jdGlvbihlLGkpe3ZhciBzPWgoXCJlYXNpbmcuXCIrZSxmdW5jdGlvbih0KXt0aGlzLl9wMT10fHwwPT09dD90OjEuNzAxNTgsdGhpcy5fcDI9MS41MjUqdGhpcy5fcDF9LCEwKSxyPXMucHJvdG90eXBlPW5ldyB0O3JldHVybiByLmNvbnN0cnVjdG9yPXMsci5nZXRSYXRpbz1pLHIuY29uZmlnPWZ1bmN0aW9uKHQpe3JldHVybiBuZXcgcyh0KX0sc30scD11KFwiQmFja1wiLGYoXCJCYWNrT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuKHQtPTEpKnQqKCh0aGlzLl9wMSsxKSp0K3RoaXMuX3AxKSsxfSksZihcIkJhY2tJblwiLGZ1bmN0aW9uKHQpe3JldHVybiB0KnQqKCh0aGlzLl9wMSsxKSp0LXRoaXMuX3AxKX0pLGYoXCJCYWNrSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LjUqdCp0KigodGhpcy5fcDIrMSkqdC10aGlzLl9wMik6LjUqKCh0LT0yKSp0KigodGhpcy5fcDIrMSkqdCt0aGlzLl9wMikrMil9KSksbT1oKFwiZWFzaW5nLlNsb3dNb1wiLGZ1bmN0aW9uKHQsZSxpKXtlPWV8fDA9PT1lP2U6LjcsbnVsbD09dD90PS43OnQ+MSYmKHQ9MSksdGhpcy5fcD0xIT09dD9lOjAsdGhpcy5fcDE9KDEtdCkvMix0aGlzLl9wMj10LHRoaXMuX3AzPXRoaXMuX3AxK3RoaXMuX3AyLHRoaXMuX2NhbGNFbmQ9aT09PSEwfSwhMCksZD1tLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gZC5jb25zdHJ1Y3Rvcj1tLGQuZ2V0UmF0aW89ZnVuY3Rpb24odCl7dmFyIGU9dCsoLjUtdCkqdGhpcy5fcDtyZXR1cm4gdGhpcy5fcDE+dD90aGlzLl9jYWxjRW5kPzEtKHQ9MS10L3RoaXMuX3AxKSp0OmUtKHQ9MS10L3RoaXMuX3AxKSp0KnQqdCplOnQ+dGhpcy5fcDM/dGhpcy5fY2FsY0VuZD8xLSh0PSh0LXRoaXMuX3AzKS90aGlzLl9wMSkqdDplKyh0LWUpKih0PSh0LXRoaXMuX3AzKS90aGlzLl9wMSkqdCp0KnQ6dGhpcy5fY2FsY0VuZD8xOmV9LG0uZWFzZT1uZXcgbSguNywuNyksZC5jb25maWc9bS5jb25maWc9ZnVuY3Rpb24odCxlLGkpe3JldHVybiBuZXcgbSh0LGUsaSl9LGU9aChcImVhc2luZy5TdGVwcGVkRWFzZVwiLGZ1bmN0aW9uKHQpe3Q9dHx8MSx0aGlzLl9wMT0xL3QsdGhpcy5fcDI9dCsxfSwhMCksZD1lLnByb3RvdHlwZT1uZXcgdCxkLmNvbnN0cnVjdG9yPWUsZC5nZXRSYXRpbz1mdW5jdGlvbih0KXtyZXR1cm4gMD50P3Q9MDp0Pj0xJiYodD0uOTk5OTk5OTk5KSwodGhpcy5fcDIqdD4+MCkqdGhpcy5fcDF9LGQuY29uZmlnPWUuY29uZmlnPWZ1bmN0aW9uKHQpe3JldHVybiBuZXcgZSh0KX0saT1oKFwiZWFzaW5nLlJvdWdoRWFzZVwiLGZ1bmN0aW9uKGUpe2U9ZXx8e307Zm9yKHZhciBpLHMscixuLGEsbyxoPWUudGFwZXJ8fFwibm9uZVwiLGw9W10sXz0wLHU9MHwoZS5wb2ludHN8fDIwKSxmPXUscD1lLnJhbmRvbWl6ZSE9PSExLG09ZS5jbGFtcD09PSEwLGQ9ZS50ZW1wbGF0ZSBpbnN0YW5jZW9mIHQ/ZS50ZW1wbGF0ZTpudWxsLGc9XCJudW1iZXJcIj09dHlwZW9mIGUuc3RyZW5ndGg/LjQqZS5zdHJlbmd0aDouNDstLWY+LTE7KWk9cD9NYXRoLnJhbmRvbSgpOjEvdSpmLHM9ZD9kLmdldFJhdGlvKGkpOmksXCJub25lXCI9PT1oP3I9ZzpcIm91dFwiPT09aD8obj0xLWkscj1uKm4qZyk6XCJpblwiPT09aD9yPWkqaSpnOi41Pmk/KG49MippLHI9LjUqbipuKmcpOihuPTIqKDEtaSkscj0uNSpuKm4qZykscD9zKz1NYXRoLnJhbmRvbSgpKnItLjUqcjpmJTI/cys9LjUqcjpzLT0uNSpyLG0mJihzPjE/cz0xOjA+cyYmKHM9MCkpLGxbXysrXT17eDppLHk6c307Zm9yKGwuc29ydChmdW5jdGlvbih0LGUpe3JldHVybiB0LngtZS54fSksbz1uZXcgYygxLDEsbnVsbCksZj11Oy0tZj4tMTspYT1sW2ZdLG89bmV3IGMoYS54LGEueSxvKTt0aGlzLl9wcmV2PW5ldyBjKDAsMCwwIT09by50P286by5uZXh0KX0sITApLGQ9aS5wcm90b3R5cGU9bmV3IHQsZC5jb25zdHJ1Y3Rvcj1pLGQuZ2V0UmF0aW89ZnVuY3Rpb24odCl7dmFyIGU9dGhpcy5fcHJldjtpZih0PmUudCl7Zm9yKDtlLm5leHQmJnQ+PWUudDspZT1lLm5leHQ7ZT1lLnByZXZ9ZWxzZSBmb3IoO2UucHJldiYmZS50Pj10OyllPWUucHJldjtyZXR1cm4gdGhpcy5fcHJldj1lLGUudisodC1lLnQpL2UuZ2FwKmUuY30sZC5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBpKHQpfSxpLmVhc2U9bmV3IGksdShcIkJvdW5jZVwiLGwoXCJCb3VuY2VPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMS8yLjc1PnQ/Ny41NjI1KnQqdDoyLzIuNzU+dD83LjU2MjUqKHQtPTEuNS8yLjc1KSp0Ky43NToyLjUvMi43NT50PzcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1OjcuNTYyNSoodC09Mi42MjUvMi43NSkqdCsuOTg0Mzc1fSksbChcIkJvdW5jZUluXCIsZnVuY3Rpb24odCl7cmV0dXJuIDEvMi43NT4odD0xLXQpPzEtNy41NjI1KnQqdDoyLzIuNzU+dD8xLSg3LjU2MjUqKHQtPTEuNS8yLjc1KSp0Ky43NSk6Mi41LzIuNzU+dD8xLSg3LjU2MjUqKHQtPTIuMjUvMi43NSkqdCsuOTM3NSk6MS0oNy41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzUpfSksbChcIkJvdW5jZUluT3V0XCIsZnVuY3Rpb24odCl7dmFyIGU9LjU+dDtyZXR1cm4gdD1lPzEtMip0OjIqdC0xLHQ9MS8yLjc1PnQ/Ny41NjI1KnQqdDoyLzIuNzU+dD83LjU2MjUqKHQtPTEuNS8yLjc1KSp0Ky43NToyLjUvMi43NT50PzcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1OjcuNTYyNSoodC09Mi42MjUvMi43NSkqdCsuOTg0Mzc1LGU/LjUqKDEtdCk6LjUqdCsuNX0pKSx1KFwiQ2lyY1wiLGwoXCJDaXJjT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIE1hdGguc3FydCgxLSh0LT0xKSp0KX0pLGwoXCJDaXJjSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tKE1hdGguc3FydCgxLXQqdCktMSl9KSxsKFwiQ2lyY0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy0uNSooTWF0aC5zcXJ0KDEtdCp0KS0xKTouNSooTWF0aC5zcXJ0KDEtKHQtPTIpKnQpKzEpfSkpLHM9ZnVuY3Rpb24oZSxpLHMpe3ZhciByPWgoXCJlYXNpbmcuXCIrZSxmdW5jdGlvbih0LGUpe3RoaXMuX3AxPXR8fDEsdGhpcy5fcDI9ZXx8cyx0aGlzLl9wMz10aGlzLl9wMi9hKihNYXRoLmFzaW4oMS90aGlzLl9wMSl8fDApfSwhMCksbj1yLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gbi5jb25zdHJ1Y3Rvcj1yLG4uZ2V0UmF0aW89aSxuLmNvbmZpZz1mdW5jdGlvbih0LGUpe3JldHVybiBuZXcgcih0LGUpfSxyfSx1KFwiRWxhc3RpY1wiLHMoXCJFbGFzdGljT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIHRoaXMuX3AxKk1hdGgucG93KDIsLTEwKnQpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKSsxfSwuMykscyhcIkVsYXN0aWNJblwiLGZ1bmN0aW9uKHQpe3JldHVybi0odGhpcy5fcDEqTWF0aC5wb3coMiwxMCoodC09MSkpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKSl9LC4zKSxzKFwiRWxhc3RpY0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy0uNSp0aGlzLl9wMSpNYXRoLnBvdygyLDEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpOi41KnRoaXMuX3AxKk1hdGgucG93KDIsLTEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKzF9LC40NSkpLHUoXCJFeHBvXCIsbChcIkV4cG9PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMS1NYXRoLnBvdygyLC0xMCp0KX0pLGwoXCJFeHBvSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5wb3coMiwxMCoodC0xKSktLjAwMX0pLGwoXCJFeHBvSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LjUqTWF0aC5wb3coMiwxMCoodC0xKSk6LjUqKDItTWF0aC5wb3coMiwtMTAqKHQtMSkpKX0pKSx1KFwiU2luZVwiLGwoXCJTaW5lT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIE1hdGguc2luKHQqbyl9KSxsKFwiU2luZUluXCIsZnVuY3Rpb24odCl7cmV0dXJuLU1hdGguY29zKHQqbykrMX0pLGwoXCJTaW5lSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4tLjUqKE1hdGguY29zKE1hdGguUEkqdCktMSl9KSksaChcImVhc2luZy5FYXNlTG9va3VwXCIse2ZpbmQ6ZnVuY3Rpb24oZSl7cmV0dXJuIHQubWFwW2VdfX0sITApLF8oci5TbG93TW8sXCJTbG93TW9cIixcImVhc2UsXCIpLF8oaSxcIlJvdWdoRWFzZVwiLFwiZWFzZSxcIiksXyhlLFwiU3RlcHBlZEVhc2VcIixcImVhc2UsXCIpLHB9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcInBsdWdpbnMuQ1NTUGx1Z2luXCIsW1wicGx1Z2lucy5Ud2VlblBsdWdpblwiLFwiVHdlZW5MaXRlXCJdLGZ1bmN0aW9uKHQsZSl7dmFyIGkscixzLG4sYT1mdW5jdGlvbigpe3QuY2FsbCh0aGlzLFwiY3NzXCIpLHRoaXMuX292ZXJ3cml0ZVByb3BzLmxlbmd0aD0wLHRoaXMuc2V0UmF0aW89YS5wcm90b3R5cGUuc2V0UmF0aW99LG89e30sbD1hLnByb3RvdHlwZT1uZXcgdChcImNzc1wiKTtsLmNvbnN0cnVjdG9yPWEsYS52ZXJzaW9uPVwiMS4xMi4xXCIsYS5BUEk9MixhLmRlZmF1bHRUcmFuc2Zvcm1QZXJzcGVjdGl2ZT0wLGEuZGVmYXVsdFNrZXdUeXBlPVwiY29tcGVuc2F0ZWRcIixsPVwicHhcIixhLnN1ZmZpeE1hcD17dG9wOmwscmlnaHQ6bCxib3R0b206bCxsZWZ0Omwsd2lkdGg6bCxoZWlnaHQ6bCxmb250U2l6ZTpsLHBhZGRpbmc6bCxtYXJnaW46bCxwZXJzcGVjdGl2ZTpsLGxpbmVIZWlnaHQ6XCJcIn07dmFyIGgsdSxmLF8scCxjLGQ9Lyg/OlxcZHxcXC1cXGR8XFwuXFxkfFxcLVxcLlxcZCkrL2csbT0vKD86XFxkfFxcLVxcZHxcXC5cXGR8XFwtXFwuXFxkfFxcKz1cXGR8XFwtPVxcZHxcXCs9LlxcZHxcXC09XFwuXFxkKSsvZyxnPS8oPzpcXCs9fFxcLT18XFwtfFxcYilbXFxkXFwtXFwuXStbYS16QS1aMC05XSooPzolfFxcYikvZ2ksdj0vW15cXGRcXC1cXC5dL2cseT0vKD86XFxkfFxcLXxcXCt8PXwjfFxcLikqL2csVD0vb3BhY2l0eSAqPSAqKFteKV0qKS9pLHc9L29wYWNpdHk6KFteO10qKS9pLHg9L2FscGhhXFwob3BhY2l0eSAqPS4rP1xcKS9pLGI9L14ocmdifGhzbCkvLFA9LyhbQS1aXSkvZyxTPS8tKFthLXpdKS9naSxDPS8oXig/OnVybFxcKFxcXCJ8dXJsXFwoKSl8KD86KFxcXCJcXCkpJHxcXCkkKS9naSxSPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGUudG9VcHBlckNhc2UoKX0saz0vKD86TGVmdHxSaWdodHxXaWR0aCkvaSxBPS8oTTExfE0xMnxNMjF8TTIyKT1bXFxkXFwtXFwuZV0rL2dpLE89L3Byb2dpZFxcOkRYSW1hZ2VUcmFuc2Zvcm1cXC5NaWNyb3NvZnRcXC5NYXRyaXhcXCguKz9cXCkvaSxEPS8sKD89W15cXCldKig/OlxcKHwkKSkvZ2ksTT1NYXRoLlBJLzE4MCxMPTE4MC9NYXRoLlBJLE49e30sWD1kb2N1bWVudCx6PVguY3JlYXRlRWxlbWVudChcImRpdlwiKSxJPVguY3JlYXRlRWxlbWVudChcImltZ1wiKSxFPWEuX2ludGVybmFscz17X3NwZWNpYWxQcm9wczpvfSxGPW5hdmlnYXRvci51c2VyQWdlbnQsWT1mdW5jdGlvbigpe3ZhciB0LGU9Ri5pbmRleE9mKFwiQW5kcm9pZFwiKSxpPVguY3JlYXRlRWxlbWVudChcImRpdlwiKTtyZXR1cm4gZj0tMSE9PUYuaW5kZXhPZihcIlNhZmFyaVwiKSYmLTE9PT1GLmluZGV4T2YoXCJDaHJvbWVcIikmJigtMT09PWV8fE51bWJlcihGLnN1YnN0cihlKzgsMSkpPjMpLHA9ZiYmNj5OdW1iZXIoRi5zdWJzdHIoRi5pbmRleE9mKFwiVmVyc2lvbi9cIikrOCwxKSksXz0tMSE9PUYuaW5kZXhPZihcIkZpcmVmb3hcIiksL01TSUUgKFswLTldezEsfVtcXC4wLTldezAsfSkvLmV4ZWMoRikmJihjPXBhcnNlRmxvYXQoUmVnRXhwLiQxKSksaS5pbm5lckhUTUw9XCI8YSBzdHlsZT0ndG9wOjFweDtvcGFjaXR5Oi41NTsnPmE8L2E+XCIsdD1pLmdldEVsZW1lbnRzQnlUYWdOYW1lKFwiYVwiKVswXSx0Py9eMC41NS8udGVzdCh0LnN0eWxlLm9wYWNpdHkpOiExfSgpLEI9ZnVuY3Rpb24odCl7cmV0dXJuIFQudGVzdChcInN0cmluZ1wiPT10eXBlb2YgdD90Oih0LmN1cnJlbnRTdHlsZT90LmN1cnJlbnRTdHlsZS5maWx0ZXI6dC5zdHlsZS5maWx0ZXIpfHxcIlwiKT9wYXJzZUZsb2F0KFJlZ0V4cC4kMSkvMTAwOjF9LFU9ZnVuY3Rpb24odCl7d2luZG93LmNvbnNvbGUmJmNvbnNvbGUubG9nKHQpfSxXPVwiXCIsaj1cIlwiLFY9ZnVuY3Rpb24odCxlKXtlPWV8fHo7dmFyIGkscixzPWUuc3R5bGU7aWYodm9pZCAwIT09c1t0XSlyZXR1cm4gdDtmb3IodD10LmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK3Quc3Vic3RyKDEpLGk9W1wiT1wiLFwiTW96XCIsXCJtc1wiLFwiTXNcIixcIldlYmtpdFwiXSxyPTU7LS1yPi0xJiZ2b2lkIDA9PT1zW2lbcl0rdF07KTtyZXR1cm4gcj49MD8oaj0zPT09cj9cIm1zXCI6aVtyXSxXPVwiLVwiK2oudG9Mb3dlckNhc2UoKStcIi1cIixqK3QpOm51bGx9LEg9WC5kZWZhdWx0Vmlldz9YLmRlZmF1bHRWaWV3LmdldENvbXB1dGVkU3R5bGU6ZnVuY3Rpb24oKXt9LHE9YS5nZXRTdHlsZT1mdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuO3JldHVybiBZfHxcIm9wYWNpdHlcIiE9PWU/KCFyJiZ0LnN0eWxlW2VdP249dC5zdHlsZVtlXTooaT1pfHxIKHQpKT9uPWlbZV18fGkuZ2V0UHJvcGVydHlWYWx1ZShlKXx8aS5nZXRQcm9wZXJ0eVZhbHVlKGUucmVwbGFjZShQLFwiLSQxXCIpLnRvTG93ZXJDYXNlKCkpOnQuY3VycmVudFN0eWxlJiYobj10LmN1cnJlbnRTdHlsZVtlXSksbnVsbD09c3x8biYmXCJub25lXCIhPT1uJiZcImF1dG9cIiE9PW4mJlwiYXV0byBhdXRvXCIhPT1uP246cyk6Qih0KX0sUT1FLmNvbnZlcnRUb1BpeGVscz1mdW5jdGlvbih0LGkscixzLG4pe2lmKFwicHhcIj09PXN8fCFzKXJldHVybiByO2lmKFwiYXV0b1wiPT09c3x8IXIpcmV0dXJuIDA7dmFyIG8sbCxoLHU9ay50ZXN0KGkpLGY9dCxfPXouc3R5bGUscD0wPnI7aWYocCYmKHI9LXIpLFwiJVwiPT09cyYmLTEhPT1pLmluZGV4T2YoXCJib3JkZXJcIikpbz1yLzEwMCoodT90LmNsaWVudFdpZHRoOnQuY2xpZW50SGVpZ2h0KTtlbHNle2lmKF8uY3NzVGV4dD1cImJvcmRlcjowIHNvbGlkIHJlZDtwb3NpdGlvbjpcIitxKHQsXCJwb3NpdGlvblwiKStcIjtsaW5lLWhlaWdodDowO1wiLFwiJVwiIT09cyYmZi5hcHBlbmRDaGlsZClfW3U/XCJib3JkZXJMZWZ0V2lkdGhcIjpcImJvcmRlclRvcFdpZHRoXCJdPXIrcztlbHNle2lmKGY9dC5wYXJlbnROb2RlfHxYLmJvZHksbD1mLl9nc0NhY2hlLGg9ZS50aWNrZXIuZnJhbWUsbCYmdSYmbC50aW1lPT09aClyZXR1cm4gbC53aWR0aCpyLzEwMDtfW3U/XCJ3aWR0aFwiOlwiaGVpZ2h0XCJdPXIrc31mLmFwcGVuZENoaWxkKHopLG89cGFyc2VGbG9hdCh6W3U/XCJvZmZzZXRXaWR0aFwiOlwib2Zmc2V0SGVpZ2h0XCJdKSxmLnJlbW92ZUNoaWxkKHopLHUmJlwiJVwiPT09cyYmYS5jYWNoZVdpZHRocyE9PSExJiYobD1mLl9nc0NhY2hlPWYuX2dzQ2FjaGV8fHt9LGwudGltZT1oLGwud2lkdGg9MTAwKihvL3IpKSwwIT09b3x8bnx8KG89USh0LGkscixzLCEwKSl9cmV0dXJuIHA/LW86b30sWj1FLmNhbGN1bGF0ZU9mZnNldD1mdW5jdGlvbih0LGUsaSl7aWYoXCJhYnNvbHV0ZVwiIT09cSh0LFwicG9zaXRpb25cIixpKSlyZXR1cm4gMDt2YXIgcj1cImxlZnRcIj09PWU/XCJMZWZ0XCI6XCJUb3BcIixzPXEodCxcIm1hcmdpblwiK3IsaSk7cmV0dXJuIHRbXCJvZmZzZXRcIityXS0oUSh0LGUscGFyc2VGbG9hdChzKSxzLnJlcGxhY2UoeSxcIlwiKSl8fDApfSwkPWZ1bmN0aW9uKHQsZSl7dmFyIGkscixzPXt9O2lmKGU9ZXx8SCh0LG51bGwpKWlmKGk9ZS5sZW5ndGgpZm9yKDstLWk+LTE7KXNbZVtpXS5yZXBsYWNlKFMsUildPWUuZ2V0UHJvcGVydHlWYWx1ZShlW2ldKTtlbHNlIGZvcihpIGluIGUpc1tpXT1lW2ldO2Vsc2UgaWYoZT10LmN1cnJlbnRTdHlsZXx8dC5zdHlsZSlmb3IoaSBpbiBlKVwic3RyaW5nXCI9PXR5cGVvZiBpJiZ2b2lkIDA9PT1zW2ldJiYoc1tpLnJlcGxhY2UoUyxSKV09ZVtpXSk7cmV0dXJuIFl8fChzLm9wYWNpdHk9Qih0KSkscj1QZSh0LGUsITEpLHMucm90YXRpb249ci5yb3RhdGlvbixzLnNrZXdYPXIuc2tld1gscy5zY2FsZVg9ci5zY2FsZVgscy5zY2FsZVk9ci5zY2FsZVkscy54PXIueCxzLnk9ci55LHhlJiYocy56PXIueixzLnJvdGF0aW9uWD1yLnJvdGF0aW9uWCxzLnJvdGF0aW9uWT1yLnJvdGF0aW9uWSxzLnNjYWxlWj1yLnNjYWxlWikscy5maWx0ZXJzJiZkZWxldGUgcy5maWx0ZXJzLHN9LEc9ZnVuY3Rpb24odCxlLGkscixzKXt2YXIgbixhLG8sbD17fSxoPXQuc3R5bGU7Zm9yKGEgaW4gaSlcImNzc1RleHRcIiE9PWEmJlwibGVuZ3RoXCIhPT1hJiZpc05hTihhKSYmKGVbYV0hPT0obj1pW2FdKXx8cyYmc1thXSkmJi0xPT09YS5pbmRleE9mKFwiT3JpZ2luXCIpJiYoXCJudW1iZXJcIj09dHlwZW9mIG58fFwic3RyaW5nXCI9PXR5cGVvZiBuKSYmKGxbYV09XCJhdXRvXCIhPT1ufHxcImxlZnRcIiE9PWEmJlwidG9wXCIhPT1hP1wiXCIhPT1uJiZcImF1dG9cIiE9PW4mJlwibm9uZVwiIT09bnx8XCJzdHJpbmdcIiE9dHlwZW9mIGVbYV18fFwiXCI9PT1lW2FdLnJlcGxhY2UodixcIlwiKT9uOjA6Wih0LGEpLHZvaWQgMCE9PWhbYV0mJihvPW5ldyBmZShoLGEsaFthXSxvKSkpO2lmKHIpZm9yKGEgaW4gcilcImNsYXNzTmFtZVwiIT09YSYmKGxbYV09clthXSk7cmV0dXJue2RpZnM6bCxmaXJzdE1QVDpvfX0sSz17d2lkdGg6W1wiTGVmdFwiLFwiUmlnaHRcIl0saGVpZ2h0OltcIlRvcFwiLFwiQm90dG9tXCJdfSxKPVtcIm1hcmdpbkxlZnRcIixcIm1hcmdpblJpZ2h0XCIsXCJtYXJnaW5Ub3BcIixcIm1hcmdpbkJvdHRvbVwiXSx0ZT1mdW5jdGlvbih0LGUsaSl7dmFyIHI9cGFyc2VGbG9hdChcIndpZHRoXCI9PT1lP3Qub2Zmc2V0V2lkdGg6dC5vZmZzZXRIZWlnaHQpLHM9S1tlXSxuPXMubGVuZ3RoO2ZvcihpPWl8fEgodCxudWxsKTstLW4+LTE7KXItPXBhcnNlRmxvYXQocSh0LFwicGFkZGluZ1wiK3Nbbl0saSwhMCkpfHwwLHItPXBhcnNlRmxvYXQocSh0LFwiYm9yZGVyXCIrc1tuXStcIldpZHRoXCIsaSwhMCkpfHwwO3JldHVybiByfSxlZT1mdW5jdGlvbih0LGUpeyhudWxsPT10fHxcIlwiPT09dHx8XCJhdXRvXCI9PT10fHxcImF1dG8gYXV0b1wiPT09dCkmJih0PVwiMCAwXCIpO3ZhciBpPXQuc3BsaXQoXCIgXCIpLHI9LTEhPT10LmluZGV4T2YoXCJsZWZ0XCIpP1wiMCVcIjotMSE9PXQuaW5kZXhPZihcInJpZ2h0XCIpP1wiMTAwJVwiOmlbMF0scz0tMSE9PXQuaW5kZXhPZihcInRvcFwiKT9cIjAlXCI6LTEhPT10LmluZGV4T2YoXCJib3R0b21cIik/XCIxMDAlXCI6aVsxXTtyZXR1cm4gbnVsbD09cz9zPVwiMFwiOlwiY2VudGVyXCI9PT1zJiYocz1cIjUwJVwiKSwoXCJjZW50ZXJcIj09PXJ8fGlzTmFOKHBhcnNlRmxvYXQocikpJiYtMT09PShyK1wiXCIpLmluZGV4T2YoXCI9XCIpKSYmKHI9XCI1MCVcIiksZSYmKGUub3hwPS0xIT09ci5pbmRleE9mKFwiJVwiKSxlLm95cD0tMSE9PXMuaW5kZXhPZihcIiVcIiksZS5veHI9XCI9XCI9PT1yLmNoYXJBdCgxKSxlLm95cj1cIj1cIj09PXMuY2hhckF0KDEpLGUub3g9cGFyc2VGbG9hdChyLnJlcGxhY2UodixcIlwiKSksZS5veT1wYXJzZUZsb2F0KHMucmVwbGFjZSh2LFwiXCIpKSkscitcIiBcIitzKyhpLmxlbmd0aD4yP1wiIFwiK2lbMl06XCJcIil9LGllPWZ1bmN0aW9uKHQsZSl7cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIHQmJlwiPVwiPT09dC5jaGFyQXQoMSk/cGFyc2VJbnQodC5jaGFyQXQoMCkrXCIxXCIsMTApKnBhcnNlRmxvYXQodC5zdWJzdHIoMikpOnBhcnNlRmxvYXQodCktcGFyc2VGbG9hdChlKX0scmU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbD09dD9lOlwic3RyaW5nXCI9PXR5cGVvZiB0JiZcIj1cIj09PXQuY2hhckF0KDEpP3BhcnNlSW50KHQuY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIodC5zdWJzdHIoMikpK2U6cGFyc2VGbG9hdCh0KX0sc2U9ZnVuY3Rpb24odCxlLGkscil7dmFyIHMsbixhLG8sbD0xZS02O3JldHVybiBudWxsPT10P289ZTpcIm51bWJlclwiPT10eXBlb2YgdD9vPXQ6KHM9MzYwLG49dC5zcGxpdChcIl9cIiksYT1OdW1iZXIoblswXS5yZXBsYWNlKHYsXCJcIikpKigtMT09PXQuaW5kZXhPZihcInJhZFwiKT8xOkwpLShcIj1cIj09PXQuY2hhckF0KDEpPzA6ZSksbi5sZW5ndGgmJihyJiYocltpXT1lK2EpLC0xIT09dC5pbmRleE9mKFwic2hvcnRcIikmJihhJT1zLGEhPT1hJShzLzIpJiYoYT0wPmE/YStzOmEtcykpLC0xIT09dC5pbmRleE9mKFwiX2N3XCIpJiYwPmE/YT0oYSs5OTk5OTk5OTk5KnMpJXMtKDB8YS9zKSpzOi0xIT09dC5pbmRleE9mKFwiY2N3XCIpJiZhPjAmJihhPShhLTk5OTk5OTk5OTkqcyklcy0oMHxhL3MpKnMpKSxvPWUrYSksbD5vJiZvPi1sJiYobz0wKSxvfSxuZT17YXF1YTpbMCwyNTUsMjU1XSxsaW1lOlswLDI1NSwwXSxzaWx2ZXI6WzE5MiwxOTIsMTkyXSxibGFjazpbMCwwLDBdLG1hcm9vbjpbMTI4LDAsMF0sdGVhbDpbMCwxMjgsMTI4XSxibHVlOlswLDAsMjU1XSxuYXZ5OlswLDAsMTI4XSx3aGl0ZTpbMjU1LDI1NSwyNTVdLGZ1Y2hzaWE6WzI1NSwwLDI1NV0sb2xpdmU6WzEyOCwxMjgsMF0seWVsbG93OlsyNTUsMjU1LDBdLG9yYW5nZTpbMjU1LDE2NSwwXSxncmF5OlsxMjgsMTI4LDEyOF0scHVycGxlOlsxMjgsMCwxMjhdLGdyZWVuOlswLDEyOCwwXSxyZWQ6WzI1NSwwLDBdLHBpbms6WzI1NSwxOTIsMjAzXSxjeWFuOlswLDI1NSwyNTVdLHRyYW5zcGFyZW50OlsyNTUsMjU1LDI1NSwwXX0sYWU9ZnVuY3Rpb24odCxlLGkpe3JldHVybiB0PTA+dD90KzE6dD4xP3QtMTp0LDB8MjU1KigxPjYqdD9lKzYqKGktZSkqdDouNT50P2k6Mj4zKnQ/ZSs2KihpLWUpKigyLzMtdCk6ZSkrLjV9LG9lPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYTtyZXR1cm4gdCYmXCJcIiE9PXQ/XCJudW1iZXJcIj09dHlwZW9mIHQ/W3Q+PjE2LDI1NSZ0Pj44LDI1NSZ0XTooXCIsXCI9PT10LmNoYXJBdCh0Lmxlbmd0aC0xKSYmKHQ9dC5zdWJzdHIoMCx0Lmxlbmd0aC0xKSksbmVbdF0/bmVbdF06XCIjXCI9PT10LmNoYXJBdCgwKT8oND09PXQubGVuZ3RoJiYoZT10LmNoYXJBdCgxKSxpPXQuY2hhckF0KDIpLHI9dC5jaGFyQXQoMyksdD1cIiNcIitlK2UraStpK3IrciksdD1wYXJzZUludCh0LnN1YnN0cigxKSwxNiksW3Q+PjE2LDI1NSZ0Pj44LDI1NSZ0XSk6XCJoc2xcIj09PXQuc3Vic3RyKDAsMyk/KHQ9dC5tYXRjaChkKSxzPU51bWJlcih0WzBdKSUzNjAvMzYwLG49TnVtYmVyKHRbMV0pLzEwMCxhPU51bWJlcih0WzJdKS8xMDAsaT0uNT49YT9hKihuKzEpOmErbi1hKm4sZT0yKmEtaSx0Lmxlbmd0aD4zJiYodFszXT1OdW1iZXIodFszXSkpLHRbMF09YWUocysxLzMsZSxpKSx0WzFdPWFlKHMsZSxpKSx0WzJdPWFlKHMtMS8zLGUsaSksdCk6KHQ9dC5tYXRjaChkKXx8bmUudHJhbnNwYXJlbnQsdFswXT1OdW1iZXIodFswXSksdFsxXT1OdW1iZXIodFsxXSksdFsyXT1OdW1iZXIodFsyXSksdC5sZW5ndGg+MyYmKHRbM109TnVtYmVyKHRbM10pKSx0KSk6bmUuYmxhY2t9LGxlPVwiKD86XFxcXGIoPzooPzpyZ2J8cmdiYXxoc2x8aHNsYSlcXFxcKC4rP1xcXFwpKXxcXFxcQiMuKz9cXFxcYlwiO2ZvcihsIGluIG5lKWxlKz1cInxcIitsK1wiXFxcXGJcIjtsZT1SZWdFeHAobGUrXCIpXCIsXCJnaVwiKTt2YXIgaGU9ZnVuY3Rpb24odCxlLGkscil7aWYobnVsbD09dClyZXR1cm4gZnVuY3Rpb24odCl7cmV0dXJuIHR9O3ZhciBzLG49ZT8odC5tYXRjaChsZSl8fFtcIlwiXSlbMF06XCJcIixhPXQuc3BsaXQobikuam9pbihcIlwiKS5tYXRjaChnKXx8W10sbz10LnN1YnN0cigwLHQuaW5kZXhPZihhWzBdKSksbD1cIilcIj09PXQuY2hhckF0KHQubGVuZ3RoLTEpP1wiKVwiOlwiXCIsaD0tMSE9PXQuaW5kZXhPZihcIiBcIik/XCIgXCI6XCIsXCIsdT1hLmxlbmd0aCxmPXU+MD9hWzBdLnJlcGxhY2UoZCxcIlwiKTpcIlwiO3JldHVybiB1P3M9ZT9mdW5jdGlvbih0KXt2YXIgZSxfLHAsYztpZihcIm51bWJlclwiPT10eXBlb2YgdCl0Kz1mO2Vsc2UgaWYociYmRC50ZXN0KHQpKXtmb3IoYz10LnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLHA9MDtjLmxlbmd0aD5wO3ArKyljW3BdPXMoY1twXSk7cmV0dXJuIGMuam9pbihcIixcIil9aWYoZT0odC5tYXRjaChsZSl8fFtuXSlbMF0sXz10LnNwbGl0KGUpLmpvaW4oXCJcIikubWF0Y2goZyl8fFtdLHA9Xy5sZW5ndGgsdT5wLS0pZm9yKDt1PisrcDspX1twXT1pP19bMHwocC0xKS8yXTphW3BdO3JldHVybiBvK18uam9pbihoKStoK2UrbCsoLTEhPT10LmluZGV4T2YoXCJpbnNldFwiKT9cIiBpbnNldFwiOlwiXCIpfTpmdW5jdGlvbih0KXt2YXIgZSxuLF87aWYoXCJudW1iZXJcIj09dHlwZW9mIHQpdCs9ZjtlbHNlIGlmKHImJkQudGVzdCh0KSl7Zm9yKG49dC5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxfPTA7bi5sZW5ndGg+XztfKyspbltfXT1zKG5bX10pO3JldHVybiBuLmpvaW4oXCIsXCIpfWlmKGU9dC5tYXRjaChnKXx8W10sXz1lLmxlbmd0aCx1Pl8tLSlmb3IoO3U+KytfOyllW19dPWk/ZVswfChfLTEpLzJdOmFbX107cmV0dXJuIG8rZS5qb2luKGgpK2x9OmZ1bmN0aW9uKHQpe3JldHVybiB0fX0sdWU9ZnVuY3Rpb24odCl7cmV0dXJuIHQ9dC5zcGxpdChcIixcIiksZnVuY3Rpb24oZSxpLHIscyxuLGEsbyl7dmFyIGwsaD0oaStcIlwiKS5zcGxpdChcIiBcIik7Zm9yKG89e30sbD0wOzQ+bDtsKyspb1t0W2xdXT1oW2xdPWhbbF18fGhbKGwtMSkvMj4+MF07cmV0dXJuIHMucGFyc2UoZSxvLG4sYSl9fSxmZT0oRS5fc2V0UGx1Z2luUmF0aW89ZnVuY3Rpb24odCl7dGhpcy5wbHVnaW4uc2V0UmF0aW8odCk7Zm9yKHZhciBlLGkscixzLG49dGhpcy5kYXRhLGE9bi5wcm94eSxvPW4uZmlyc3RNUFQsbD0xZS02O287KWU9YVtvLnZdLG8ucj9lPU1hdGgucm91bmQoZSk6bD5lJiZlPi1sJiYoZT0wKSxvLnRbby5wXT1lLG89by5fbmV4dDtpZihuLmF1dG9Sb3RhdGUmJihuLmF1dG9Sb3RhdGUucm90YXRpb249YS5yb3RhdGlvbiksMT09PXQpZm9yKG89bi5maXJzdE1QVDtvOyl7aWYoaT1vLnQsaS50eXBlKXtpZigxPT09aS50eXBlKXtmb3Iocz1pLnhzMCtpLnMraS54czEscj0xO2kubD5yO3IrKylzKz1pW1wieG5cIityXStpW1wieHNcIisocisxKV07aS5lPXN9fWVsc2UgaS5lPWkucytpLnhzMDtvPW8uX25leHR9fSxmdW5jdGlvbih0LGUsaSxyLHMpe3RoaXMudD10LHRoaXMucD1lLHRoaXMudj1pLHRoaXMucj1zLHImJihyLl9wcmV2PXRoaXMsdGhpcy5fbmV4dD1yKX0pLF9lPShFLl9wYXJzZVRvUHJveHk9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhLG8sbCxoLHUsZj1yLF89e30scD17fSxjPWkuX3RyYW5zZm9ybSxkPU47Zm9yKGkuX3RyYW5zZm9ybT1udWxsLE49ZSxyPXU9aS5wYXJzZSh0LGUscixzKSxOPWQsbiYmKGkuX3RyYW5zZm9ybT1jLGYmJihmLl9wcmV2PW51bGwsZi5fcHJldiYmKGYuX3ByZXYuX25leHQ9bnVsbCkpKTtyJiZyIT09Zjspe2lmKDE+PXIudHlwZSYmKG89ci5wLHBbb109ci5zK3IuYyxfW29dPXIucyxufHwoaD1uZXcgZmUocixcInNcIixvLGgsci5yKSxyLmM9MCksMT09PXIudHlwZSkpZm9yKGE9ci5sOy0tYT4wOylsPVwieG5cIithLG89ci5wK1wiX1wiK2wscFtvXT1yLmRhdGFbbF0sX1tvXT1yW2xdLG58fChoPW5ldyBmZShyLGwsbyxoLHIucnhwW2xdKSk7cj1yLl9uZXh0fXJldHVybntwcm94eTpfLGVuZDpwLGZpcnN0TVBUOmgscHQ6dX19LEUuQ1NTUHJvcFR3ZWVuPWZ1bmN0aW9uKHQsZSxyLHMsYSxvLGwsaCx1LGYsXyl7dGhpcy50PXQsdGhpcy5wPWUsdGhpcy5zPXIsdGhpcy5jPXMsdGhpcy5uPWx8fGUsdCBpbnN0YW5jZW9mIF9lfHxuLnB1c2godGhpcy5uKSx0aGlzLnI9aCx0aGlzLnR5cGU9b3x8MCx1JiYodGhpcy5wcj11LGk9ITApLHRoaXMuYj12b2lkIDA9PT1mP3I6Zix0aGlzLmU9dm9pZCAwPT09Xz9yK3M6XyxhJiYodGhpcy5fbmV4dD1hLGEuX3ByZXY9dGhpcyl9KSxwZT1hLnBhcnNlQ29tcGxleD1mdW5jdGlvbih0LGUsaSxyLHMsbixhLG8sbCx1KXtpPWl8fG58fFwiXCIsYT1uZXcgX2UodCxlLDAsMCxhLHU/MjoxLG51bGwsITEsbyxpLHIpLHIrPVwiXCI7dmFyIGYsXyxwLGMsZyx2LHksVCx3LHgsUCxTLEM9aS5zcGxpdChcIiwgXCIpLmpvaW4oXCIsXCIpLnNwbGl0KFwiIFwiKSxSPXIuc3BsaXQoXCIsIFwiKS5qb2luKFwiLFwiKS5zcGxpdChcIiBcIiksaz1DLmxlbmd0aCxBPWghPT0hMTtmb3IoKC0xIT09ci5pbmRleE9mKFwiLFwiKXx8LTEhPT1pLmluZGV4T2YoXCIsXCIpKSYmKEM9Qy5qb2luKFwiIFwiKS5yZXBsYWNlKEQsXCIsIFwiKS5zcGxpdChcIiBcIiksUj1SLmpvaW4oXCIgXCIpLnJlcGxhY2UoRCxcIiwgXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoKSxrIT09Ui5sZW5ndGgmJihDPShufHxcIlwiKS5zcGxpdChcIiBcIiksaz1DLmxlbmd0aCksYS5wbHVnaW49bCxhLnNldFJhdGlvPXUsZj0wO2s+ZjtmKyspaWYoYz1DW2ZdLGc9UltmXSxUPXBhcnNlRmxvYXQoYyksVHx8MD09PVQpYS5hcHBlbmRYdHJhKFwiXCIsVCxpZShnLFQpLGcucmVwbGFjZShtLFwiXCIpLEEmJi0xIT09Zy5pbmRleE9mKFwicHhcIiksITApO2Vsc2UgaWYocyYmKFwiI1wiPT09Yy5jaGFyQXQoMCl8fG5lW2NdfHxiLnRlc3QoYykpKVM9XCIsXCI9PT1nLmNoYXJBdChnLmxlbmd0aC0xKT9cIiksXCI6XCIpXCIsYz1vZShjKSxnPW9lKGcpLHc9Yy5sZW5ndGgrZy5sZW5ndGg+Nix3JiYhWSYmMD09PWdbM10/KGFbXCJ4c1wiK2EubF0rPWEubD9cIiB0cmFuc3BhcmVudFwiOlwidHJhbnNwYXJlbnRcIixhLmU9YS5lLnNwbGl0KFJbZl0pLmpvaW4oXCJ0cmFuc3BhcmVudFwiKSk6KFl8fCh3PSExKSxhLmFwcGVuZFh0cmEodz9cInJnYmEoXCI6XCJyZ2IoXCIsY1swXSxnWzBdLWNbMF0sXCIsXCIsITAsITApLmFwcGVuZFh0cmEoXCJcIixjWzFdLGdbMV0tY1sxXSxcIixcIiwhMCkuYXBwZW5kWHRyYShcIlwiLGNbMl0sZ1syXS1jWzJdLHc/XCIsXCI6UywhMCksdyYmKGM9ND5jLmxlbmd0aD8xOmNbM10sYS5hcHBlbmRYdHJhKFwiXCIsYywoND5nLmxlbmd0aD8xOmdbM10pLWMsUywhMSkpKTtlbHNlIGlmKHY9Yy5tYXRjaChkKSl7aWYoeT1nLm1hdGNoKG0pLCF5fHx5Lmxlbmd0aCE9PXYubGVuZ3RoKXJldHVybiBhO2ZvcihwPTAsXz0wO3YubGVuZ3RoPl87XysrKVA9dltfXSx4PWMuaW5kZXhPZihQLHApLGEuYXBwZW5kWHRyYShjLnN1YnN0cihwLHgtcCksTnVtYmVyKFApLGllKHlbX10sUCksXCJcIixBJiZcInB4XCI9PT1jLnN1YnN0cih4K1AubGVuZ3RoLDIpLDA9PT1fKSxwPXgrUC5sZW5ndGg7YVtcInhzXCIrYS5sXSs9Yy5zdWJzdHIocCl9ZWxzZSBhW1wieHNcIithLmxdKz1hLmw/XCIgXCIrYzpjO2lmKC0xIT09ci5pbmRleE9mKFwiPVwiKSYmYS5kYXRhKXtmb3IoUz1hLnhzMCthLmRhdGEucyxmPTE7YS5sPmY7ZisrKVMrPWFbXCJ4c1wiK2ZdK2EuZGF0YVtcInhuXCIrZl07YS5lPVMrYVtcInhzXCIrZl19cmV0dXJuIGEubHx8KGEudHlwZT0tMSxhLnhzMD1hLmUpLGEueGZpcnN0fHxhfSxjZT05O2ZvcihsPV9lLnByb3RvdHlwZSxsLmw9bC5wcj0wOy0tY2U+MDspbFtcInhuXCIrY2VdPTAsbFtcInhzXCIrY2VdPVwiXCI7bC54czA9XCJcIixsLl9uZXh0PWwuX3ByZXY9bC54Zmlyc3Q9bC5kYXRhPWwucGx1Z2luPWwuc2V0UmF0aW89bC5yeHA9bnVsbCxsLmFwcGVuZFh0cmE9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhPXRoaXMsbz1hLmw7cmV0dXJuIGFbXCJ4c1wiK29dKz1uJiZvP1wiIFwiK3Q6dHx8XCJcIixpfHwwPT09b3x8YS5wbHVnaW4/KGEubCsrLGEudHlwZT1hLnNldFJhdGlvPzI6MSxhW1wieHNcIithLmxdPXJ8fFwiXCIsbz4wPyhhLmRhdGFbXCJ4blwiK29dPWUraSxhLnJ4cFtcInhuXCIrb109cyxhW1wieG5cIitvXT1lLGEucGx1Z2lufHwoYS54Zmlyc3Q9bmV3IF9lKGEsXCJ4blwiK28sZSxpLGEueGZpcnN0fHxhLDAsYS5uLHMsYS5wciksYS54Zmlyc3QueHMwPTApLGEpOihhLmRhdGE9e3M6ZStpfSxhLnJ4cD17fSxhLnM9ZSxhLmM9aSxhLnI9cyxhKSk6KGFbXCJ4c1wiK29dKz1lKyhyfHxcIlwiKSxhKX07dmFyIGRlPWZ1bmN0aW9uKHQsZSl7ZT1lfHx7fSx0aGlzLnA9ZS5wcmVmaXg/Vih0KXx8dDp0LG9bdF09b1t0aGlzLnBdPXRoaXMsdGhpcy5mb3JtYXQ9ZS5mb3JtYXR0ZXJ8fGhlKGUuZGVmYXVsdFZhbHVlLGUuY29sb3IsZS5jb2xsYXBzaWJsZSxlLm11bHRpKSxlLnBhcnNlciYmKHRoaXMucGFyc2U9ZS5wYXJzZXIpLHRoaXMuY2xycz1lLmNvbG9yLHRoaXMubXVsdGk9ZS5tdWx0aSx0aGlzLmtleXdvcmQ9ZS5rZXl3b3JkLHRoaXMuZGZsdD1lLmRlZmF1bHRWYWx1ZSx0aGlzLnByPWUucHJpb3JpdHl8fDB9LG1lPUUuX3JlZ2lzdGVyQ29tcGxleFNwZWNpYWxQcm9wPWZ1bmN0aW9uKHQsZSxpKXtcIm9iamVjdFwiIT10eXBlb2YgZSYmKGU9e3BhcnNlcjppfSk7dmFyIHIscyxuPXQuc3BsaXQoXCIsXCIpLGE9ZS5kZWZhdWx0VmFsdWU7Zm9yKGk9aXx8W2FdLHI9MDtuLmxlbmd0aD5yO3IrKyllLnByZWZpeD0wPT09ciYmZS5wcmVmaXgsZS5kZWZhdWx0VmFsdWU9aVtyXXx8YSxzPW5ldyBkZShuW3JdLGUpfSxnZT1mdW5jdGlvbih0KXtpZighb1t0XSl7dmFyIGU9dC5jaGFyQXQoMCkudG9VcHBlckNhc2UoKSt0LnN1YnN0cigxKStcIlBsdWdpblwiO21lKHQse3BhcnNlcjpmdW5jdGlvbih0LGkscixzLG4sYSxsKXt2YXIgaD0od2luZG93LkdyZWVuU29ja0dsb2JhbHN8fHdpbmRvdykuY29tLmdyZWVuc29jay5wbHVnaW5zW2VdO3JldHVybiBoPyhoLl9jc3NSZWdpc3RlcigpLG9bcl0ucGFyc2UodCxpLHIscyxuLGEsbCkpOihVKFwiRXJyb3I6IFwiK2UrXCIganMgZmlsZSBub3QgbG9hZGVkLlwiKSxuKX19KX19O2w9ZGUucHJvdG90eXBlLGwucGFyc2VDb21wbGV4PWZ1bmN0aW9uKHQsZSxpLHIscyxuKXt2YXIgYSxvLGwsaCx1LGYsXz10aGlzLmtleXdvcmQ7aWYodGhpcy5tdWx0aSYmKEQudGVzdChpKXx8RC50ZXN0KGUpPyhvPWUucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIiksbD1pLnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpKTpfJiYobz1bZV0sbD1baV0pKSxsKXtmb3IoaD1sLmxlbmd0aD5vLmxlbmd0aD9sLmxlbmd0aDpvLmxlbmd0aCxhPTA7aD5hO2ErKyllPW9bYV09b1thXXx8dGhpcy5kZmx0LGk9bFthXT1sW2FdfHx0aGlzLmRmbHQsXyYmKHU9ZS5pbmRleE9mKF8pLGY9aS5pbmRleE9mKF8pLHUhPT1mJiYoaT0tMT09PWY/bDpvLGlbYV0rPVwiIFwiK18pKTtlPW8uam9pbihcIiwgXCIpLGk9bC5qb2luKFwiLCBcIil9cmV0dXJuIHBlKHQsdGhpcy5wLGUsaSx0aGlzLmNscnMsdGhpcy5kZmx0LHIsdGhpcy5wcixzLG4pfSxsLnBhcnNlPWZ1bmN0aW9uKHQsZSxpLHIsbixhKXtyZXR1cm4gdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSx0aGlzLmZvcm1hdChxKHQsdGhpcy5wLHMsITEsdGhpcy5kZmx0KSksdGhpcy5mb3JtYXQoZSksbixhKX0sYS5yZWdpc3RlclNwZWNpYWxQcm9wPWZ1bmN0aW9uKHQsZSxpKXttZSh0LHtwYXJzZXI6ZnVuY3Rpb24odCxyLHMsbixhLG8pe3ZhciBsPW5ldyBfZSh0LHMsMCwwLGEsMixzLCExLGkpO3JldHVybiBsLnBsdWdpbj1vLGwuc2V0UmF0aW89ZSh0LHIsbi5fdHdlZW4scyksbH0scHJpb3JpdHk6aX0pfTt2YXIgdmU9XCJzY2FsZVgsc2NhbGVZLHNjYWxlWix4LHkseixza2V3WCxza2V3WSxyb3RhdGlvbixyb3RhdGlvblgscm90YXRpb25ZLHBlcnNwZWN0aXZlXCIuc3BsaXQoXCIsXCIpLHllPVYoXCJ0cmFuc2Zvcm1cIiksVGU9VytcInRyYW5zZm9ybVwiLHdlPVYoXCJ0cmFuc2Zvcm1PcmlnaW5cIikseGU9bnVsbCE9PVYoXCJwZXJzcGVjdGl2ZVwiKSxiZT1FLlRyYW5zZm9ybT1mdW5jdGlvbigpe3RoaXMuc2tld1k9MH0sUGU9RS5nZXRUcmFuc2Zvcm09ZnVuY3Rpb24odCxlLGkscil7aWYodC5fZ3NUcmFuc2Zvcm0mJmkmJiFyKXJldHVybiB0Ll9nc1RyYW5zZm9ybTt2YXIgcyxuLG8sbCxoLHUsZixfLHAsYyxkLG0sZyx2PWk/dC5fZ3NUcmFuc2Zvcm18fG5ldyBiZTpuZXcgYmUseT0wPnYuc2NhbGVYLFQ9MmUtNSx3PTFlNSx4PTE3OS45OSxiPXgqTSxQPXhlP3BhcnNlRmxvYXQocSh0LHdlLGUsITEsXCIwIDAgMFwiKS5zcGxpdChcIiBcIilbMl0pfHx2LnpPcmlnaW58fDA6MDtmb3IoeWU/cz1xKHQsVGUsZSwhMCk6dC5jdXJyZW50U3R5bGUmJihzPXQuY3VycmVudFN0eWxlLmZpbHRlci5tYXRjaChBKSxzPXMmJjQ9PT1zLmxlbmd0aD9bc1swXS5zdWJzdHIoNCksTnVtYmVyKHNbMl0uc3Vic3RyKDQpKSxOdW1iZXIoc1sxXS5zdWJzdHIoNCkpLHNbM10uc3Vic3RyKDQpLHYueHx8MCx2Lnl8fDBdLmpvaW4oXCIsXCIpOlwiXCIpLG49KHN8fFwiXCIpLm1hdGNoKC8oPzpcXC18XFxiKVtcXGRcXC1cXC5lXStcXGIvZ2kpfHxbXSxvPW4ubGVuZ3RoOy0tbz4tMTspbD1OdW1iZXIobltvXSksbltvXT0oaD1sLShsfD0wKSk/KDB8aCp3KygwPmg/LS41Oi41KSkvdytsOmw7aWYoMTY9PT1uLmxlbmd0aCl7dmFyIFM9bls4XSxDPW5bOV0sUj1uWzEwXSxrPW5bMTJdLE89blsxM10sRD1uWzE0XTtpZih2LnpPcmlnaW4mJihEPS12LnpPcmlnaW4saz1TKkQtblsxMl0sTz1DKkQtblsxM10sRD1SKkQrdi56T3JpZ2luLW5bMTRdKSwhaXx8cnx8bnVsbD09di5yb3RhdGlvblgpe3ZhciBOLFgseixJLEUsRixZLEI9blswXSxVPW5bMV0sVz1uWzJdLGo9blszXSxWPW5bNF0sSD1uWzVdLFE9bls2XSxaPW5bN10sJD1uWzExXSxHPU1hdGguYXRhbjIoUSxSKSxLPS1iPkd8fEc+Yjt2LnJvdGF0aW9uWD1HKkwsRyYmKEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLE49VipJK1MqRSxYPUgqSStDKkUsej1RKkkrUipFLFM9ViotRStTKkksQz1IKi1FK0MqSSxSPVEqLUUrUipJLCQ9WiotRSskKkksVj1OLEg9WCxRPXopLEc9TWF0aC5hdGFuMihTLEIpLHYucm90YXRpb25ZPUcqTCxHJiYoRj0tYj5HfHxHPmIsST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksTj1CKkktUypFLFg9VSpJLUMqRSx6PVcqSS1SKkUsQz1VKkUrQypJLFI9VypFK1IqSSwkPWoqRSskKkksQj1OLFU9WCxXPXopLEc9TWF0aC5hdGFuMihVLEgpLHYucm90YXRpb249RypMLEcmJihZPS1iPkd8fEc+YixJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxCPUIqSStWKkUsWD1VKkkrSCpFLEg9VSotRStIKkksUT1XKi1FK1EqSSxVPVgpLFkmJks/di5yb3RhdGlvbj12LnJvdGF0aW9uWD0wOlkmJkY/di5yb3RhdGlvbj12LnJvdGF0aW9uWT0wOkYmJksmJih2LnJvdGF0aW9uWT12LnJvdGF0aW9uWD0wKSx2LnNjYWxlWD0oMHxNYXRoLnNxcnQoQipCK1UqVSkqdysuNSkvdyx2LnNjYWxlWT0oMHxNYXRoLnNxcnQoSCpIK0MqQykqdysuNSkvdyx2LnNjYWxlWj0oMHxNYXRoLnNxcnQoUSpRK1IqUikqdysuNSkvdyx2LnNrZXdYPTAsdi5wZXJzcGVjdGl2ZT0kPzEvKDA+JD8tJDokKTowLHYueD1rLHYueT1PLHYuej1EfX1lbHNlIGlmKCEoeGUmJiFyJiZuLmxlbmd0aCYmdi54PT09bls0XSYmdi55PT09bls1XSYmKHYucm90YXRpb25YfHx2LnJvdGF0aW9uWSl8fHZvaWQgMCE9PXYueCYmXCJub25lXCI9PT1xKHQsXCJkaXNwbGF5XCIsZSkpKXt2YXIgSj1uLmxlbmd0aD49Nix0ZT1KP25bMF06MSxlZT1uWzFdfHwwLGllPW5bMl18fDAscmU9Sj9uWzNdOjE7di54PW5bNF18fDAsdi55PW5bNV18fDAsdT1NYXRoLnNxcnQodGUqdGUrZWUqZWUpLGY9TWF0aC5zcXJ0KHJlKnJlK2llKmllKSxfPXRlfHxlZT9NYXRoLmF0YW4yKGVlLHRlKSpMOnYucm90YXRpb258fDAscD1pZXx8cmU/TWF0aC5hdGFuMihpZSxyZSkqTCtfOnYuc2tld1h8fDAsYz11LU1hdGguYWJzKHYuc2NhbGVYfHwwKSxkPWYtTWF0aC5hYnModi5zY2FsZVl8fDApLE1hdGguYWJzKHApPjkwJiYyNzA+TWF0aC5hYnMocCkmJih5Pyh1Kj0tMSxwKz0wPj1fPzE4MDotMTgwLF8rPTA+PV8/MTgwOi0xODApOihmKj0tMSxwKz0wPj1wPzE4MDotMTgwKSksbT0oXy12LnJvdGF0aW9uKSUxODAsZz0ocC12LnNrZXdYKSUxODAsKHZvaWQgMD09PXYuc2tld1h8fGM+VHx8LVQ+Y3x8ZD5UfHwtVD5kfHxtPi14JiZ4Pm0mJmZhbHNlfG0qd3x8Zz4teCYmeD5nJiZmYWxzZXxnKncpJiYodi5zY2FsZVg9dSx2LnNjYWxlWT1mLHYucm90YXRpb249Xyx2LnNrZXdYPXApLHhlJiYodi5yb3RhdGlvblg9di5yb3RhdGlvblk9di56PTAsdi5wZXJzcGVjdGl2ZT1wYXJzZUZsb2F0KGEuZGVmYXVsdFRyYW5zZm9ybVBlcnNwZWN0aXZlKXx8MCx2LnNjYWxlWj0xKX12LnpPcmlnaW49UDtmb3IobyBpbiB2KVQ+dltvXSYmdltvXT4tVCYmKHZbb109MCk7cmV0dXJuIGkmJih0Ll9nc1RyYW5zZm9ybT12KSx2fSxTZT1mdW5jdGlvbih0KXt2YXIgZSxpLHI9dGhpcy5kYXRhLHM9LXIucm90YXRpb24qTSxuPXMrci5za2V3WCpNLGE9MWU1LG89KDB8TWF0aC5jb3Mocykqci5zY2FsZVgqYSkvYSxsPSgwfE1hdGguc2luKHMpKnIuc2NhbGVYKmEpL2EsaD0oMHxNYXRoLnNpbihuKSotci5zY2FsZVkqYSkvYSx1PSgwfE1hdGguY29zKG4pKnIuc2NhbGVZKmEpL2EsZj10aGlzLnQuc3R5bGUsXz10aGlzLnQuY3VycmVudFN0eWxlO2lmKF8pe2k9bCxsPS1oLGg9LWksZT1fLmZpbHRlcixmLmZpbHRlcj1cIlwiO3ZhciBwLGQsbT10aGlzLnQub2Zmc2V0V2lkdGgsZz10aGlzLnQub2Zmc2V0SGVpZ2h0LHY9XCJhYnNvbHV0ZVwiIT09Xy5wb3NpdGlvbix3PVwicHJvZ2lkOkRYSW1hZ2VUcmFuc2Zvcm0uTWljcm9zb2Z0Lk1hdHJpeChNMTE9XCIrbytcIiwgTTEyPVwiK2wrXCIsIE0yMT1cIitoK1wiLCBNMjI9XCIrdSx4PXIueCxiPXIueTtpZihudWxsIT1yLm94JiYocD0oci5veHA/LjAxKm0qci5veDpyLm94KS1tLzIsZD0oci5veXA/LjAxKmcqci5veTpyLm95KS1nLzIseCs9cC0ocCpvK2QqbCksYis9ZC0ocCpoK2QqdSkpLHY/KHA9bS8yLGQ9Zy8yLHcrPVwiLCBEeD1cIisocC0ocCpvK2QqbCkreCkrXCIsIER5PVwiKyhkLShwKmgrZCp1KStiKStcIilcIik6dys9XCIsIHNpemluZ01ldGhvZD0nYXV0byBleHBhbmQnKVwiLGYuZmlsdGVyPS0xIT09ZS5pbmRleE9mKFwiRFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuTWF0cml4KFwiKT9lLnJlcGxhY2UoTyx3KTp3K1wiIFwiK2UsKDA9PT10fHwxPT09dCkmJjE9PT1vJiYwPT09bCYmMD09PWgmJjE9PT11JiYodiYmLTE9PT13LmluZGV4T2YoXCJEeD0wLCBEeT0wXCIpfHxULnRlc3QoZSkmJjEwMCE9PXBhcnNlRmxvYXQoUmVnRXhwLiQxKXx8LTE9PT1lLmluZGV4T2YoXCJncmFkaWVudChcIiYmZS5pbmRleE9mKFwiQWxwaGFcIikpJiZmLnJlbW92ZUF0dHJpYnV0ZShcImZpbHRlclwiKSksIXYpe3ZhciBQLFMsQyxSPTg+Yz8xOi0xO2ZvcihwPXIuaWVPZmZzZXRYfHwwLGQ9ci5pZU9mZnNldFl8fDAsci5pZU9mZnNldFg9TWF0aC5yb3VuZCgobS0oKDA+bz8tbzpvKSptKygwPmw/LWw6bCkqZykpLzIreCksci5pZU9mZnNldFk9TWF0aC5yb3VuZCgoZy0oKDA+dT8tdTp1KSpnKygwPmg/LWg6aCkqbSkpLzIrYiksY2U9MDs0PmNlO2NlKyspUz1KW2NlXSxQPV9bU10saT0tMSE9PVAuaW5kZXhPZihcInB4XCIpP3BhcnNlRmxvYXQoUCk6USh0aGlzLnQsUyxwYXJzZUZsb2F0KFApLFAucmVwbGFjZSh5LFwiXCIpKXx8MCxDPWkhPT1yW1NdPzI+Y2U/LXIuaWVPZmZzZXRYOi1yLmllT2Zmc2V0WToyPmNlP3Atci5pZU9mZnNldFg6ZC1yLmllT2Zmc2V0WSxmW1NdPShyW1NdPU1hdGgucm91bmQoaS1DKigwPT09Y2V8fDI9PT1jZT8xOlIpKSkrXCJweFwifX19LENlPUUuc2V0M0RUcmFuc2Zvcm1SYXRpbz1mdW5jdGlvbih0KXt2YXIgZSxpLHIscyxuLGEsbyxsLGgsdSxmLHAsYyxkLG0sZyx2LHksVCx3LHgsYixQLFM9dGhpcy5kYXRhLEM9dGhpcy50LnN0eWxlLFI9Uy5yb3RhdGlvbipNLGs9Uy5zY2FsZVgsQT1TLnNjYWxlWSxPPVMuc2NhbGVaLEQ9Uy5wZXJzcGVjdGl2ZTtpZighKDEhPT10JiYwIT09dHx8XCJhdXRvXCIhPT1TLmZvcmNlM0R8fFMucm90YXRpb25ZfHxTLnJvdGF0aW9uWHx8MSE9PU98fER8fFMueikpcmV0dXJuIFJlLmNhbGwodGhpcyx0KSx2b2lkIDA7aWYoXyl7dmFyIEw9MWUtNDtMPmsmJms+LUwmJihrPU89MmUtNSksTD5BJiZBPi1MJiYoQT1PPTJlLTUpLCFEfHxTLnp8fFMucm90YXRpb25YfHxTLnJvdGF0aW9uWXx8KEQ9MCl9aWYoUnx8Uy5za2V3WCl5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksZT15LG49VCxTLnNrZXdYJiYoUi09Uy5za2V3WCpNLHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxcInNpbXBsZVwiPT09Uy5za2V3VHlwZSYmKHc9TWF0aC50YW4oUy5za2V3WCpNKSx3PU1hdGguc3FydCgxK3cqdykseSo9dyxUKj13KSksaT0tVCxhPXk7ZWxzZXtpZighKFMucm90YXRpb25ZfHxTLnJvdGF0aW9uWHx8MSE9PU98fEQpKXJldHVybiBDW3llXT1cInRyYW5zbGF0ZTNkKFwiK1MueCtcInB4LFwiK1MueStcInB4LFwiK1MueitcInB4KVwiKygxIT09a3x8MSE9PUE/XCIgc2NhbGUoXCIraytcIixcIitBK1wiKVwiOlwiXCIpLHZvaWQgMDtlPWE9MSxpPW49MH1mPTEscj1zPW89bD1oPXU9cD1jPWQ9MCxtPUQ/LTEvRDowLGc9Uy56T3JpZ2luLHY9MWU1LFI9Uy5yb3RhdGlvblkqTSxSJiYoeT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLGg9ZiotVCxjPW0qLVQscj1lKlQsbz1uKlQsZio9eSxtKj15LGUqPXksbio9eSksUj1TLnJvdGF0aW9uWCpNLFImJih5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksdz1pKnkrcipULHg9YSp5K28qVCxiPXUqeStmKlQsUD1kKnkrbSpULHI9aSotVCtyKnksbz1hKi1UK28qeSxmPXUqLVQrZip5LG09ZCotVCttKnksaT13LGE9eCx1PWIsZD1QKSwxIT09TyYmKHIqPU8sbyo9TyxmKj1PLG0qPU8pLDEhPT1BJiYoaSo9QSxhKj1BLHUqPUEsZCo9QSksMSE9PWsmJihlKj1rLG4qPWssaCo9ayxjKj1rKSxnJiYocC09ZyxzPXIqcCxsPW8qcCxwPWYqcCtnKSxzPSh3PShzKz1TLngpLShzfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditzOnMsbD0odz0obCs9Uy55KS0obHw9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrbDpsLHA9KHc9KHArPVMueiktKHB8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K3A6cCxDW3llXT1cIm1hdHJpeDNkKFwiK1soMHxlKnYpL3YsKDB8bip2KS92LCgwfGgqdikvdiwoMHxjKnYpL3YsKDB8aSp2KS92LCgwfGEqdikvdiwoMHx1KnYpL3YsKDB8ZCp2KS92LCgwfHIqdikvdiwoMHxvKnYpL3YsKDB8Zip2KS92LCgwfG0qdikvdixzLGwscCxEPzErLXAvRDoxXS5qb2luKFwiLFwiKStcIilcIn0sUmU9RS5zZXQyRFRyYW5zZm9ybVJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYT10aGlzLmRhdGEsbz10aGlzLnQsbD1vLnN0eWxlO3JldHVybiBhLnJvdGF0aW9uWHx8YS5yb3RhdGlvbll8fGEuenx8YS5mb3JjZTNEPT09ITB8fFwiYXV0b1wiPT09YS5mb3JjZTNEJiYxIT09dCYmMCE9PXQ/KHRoaXMuc2V0UmF0aW89Q2UsQ2UuY2FsbCh0aGlzLHQpLHZvaWQgMCk6KGEucm90YXRpb258fGEuc2tld1g/KGU9YS5yb3RhdGlvbipNLGk9ZS1hLnNrZXdYKk0scj0xZTUscz1hLnNjYWxlWCpyLG49YS5zY2FsZVkqcixsW3llXT1cIm1hdHJpeChcIisoMHxNYXRoLmNvcyhlKSpzKS9yK1wiLFwiKygwfE1hdGguc2luKGUpKnMpL3IrXCIsXCIrKDB8TWF0aC5zaW4oaSkqLW4pL3IrXCIsXCIrKDB8TWF0aC5jb3MoaSkqbikvcitcIixcIithLngrXCIsXCIrYS55K1wiKVwiKTpsW3llXT1cIm1hdHJpeChcIithLnNjYWxlWCtcIiwwLDAsXCIrYS5zY2FsZVkrXCIsXCIrYS54K1wiLFwiK2EueStcIilcIix2b2lkIDApfTttZShcInRyYW5zZm9ybSxzY2FsZSxzY2FsZVgsc2NhbGVZLHNjYWxlWix4LHkseixyb3RhdGlvbixyb3RhdGlvblgscm90YXRpb25ZLHJvdGF0aW9uWixza2V3WCxza2V3WSxzaG9ydFJvdGF0aW9uLHNob3J0Um90YXRpb25YLHNob3J0Um90YXRpb25ZLHNob3J0Um90YXRpb25aLHRyYW5zZm9ybU9yaWdpbix0cmFuc2Zvcm1QZXJzcGVjdGl2ZSxkaXJlY3Rpb25hbFJvdGF0aW9uLHBhcnNlVHJhbnNmb3JtLGZvcmNlM0Qsc2tld1R5cGVcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixvLGwpe2lmKHIuX3RyYW5zZm9ybSlyZXR1cm4gbjt2YXIgaCx1LGYsXyxwLGMsZCxtPXIuX3RyYW5zZm9ybT1QZSh0LHMsITAsbC5wYXJzZVRyYW5zZm9ybSksZz10LnN0eWxlLHY9MWUtNix5PXZlLmxlbmd0aCxUPWwsdz17fTtpZihcInN0cmluZ1wiPT10eXBlb2YgVC50cmFuc2Zvcm0mJnllKWY9ei5zdHlsZSxmW3llXT1ULnRyYW5zZm9ybSxmLmRpc3BsYXk9XCJibG9ja1wiLGYucG9zaXRpb249XCJhYnNvbHV0ZVwiLFguYm9keS5hcHBlbmRDaGlsZCh6KSxoPVBlKHosbnVsbCwhMSksWC5ib2R5LnJlbW92ZUNoaWxkKHopO2Vsc2UgaWYoXCJvYmplY3RcIj09dHlwZW9mIFQpe2lmKGg9e3NjYWxlWDpyZShudWxsIT1ULnNjYWxlWD9ULnNjYWxlWDpULnNjYWxlLG0uc2NhbGVYKSxzY2FsZVk6cmUobnVsbCE9VC5zY2FsZVk/VC5zY2FsZVk6VC5zY2FsZSxtLnNjYWxlWSksc2NhbGVaOnJlKFQuc2NhbGVaLG0uc2NhbGVaKSx4OnJlKFQueCxtLngpLHk6cmUoVC55LG0ueSksejpyZShULnosbS56KSxwZXJzcGVjdGl2ZTpyZShULnRyYW5zZm9ybVBlcnNwZWN0aXZlLG0ucGVyc3BlY3RpdmUpfSxkPVQuZGlyZWN0aW9uYWxSb3RhdGlvbixudWxsIT1kKWlmKFwib2JqZWN0XCI9PXR5cGVvZiBkKWZvcihmIGluIGQpVFtmXT1kW2ZdO2Vsc2UgVC5yb3RhdGlvbj1kO2gucm90YXRpb249c2UoXCJyb3RhdGlvblwiaW4gVD9ULnJvdGF0aW9uOlwic2hvcnRSb3RhdGlvblwiaW4gVD9ULnNob3J0Um90YXRpb24rXCJfc2hvcnRcIjpcInJvdGF0aW9uWlwiaW4gVD9ULnJvdGF0aW9uWjptLnJvdGF0aW9uLG0ucm90YXRpb24sXCJyb3RhdGlvblwiLHcpLHhlJiYoaC5yb3RhdGlvblg9c2UoXCJyb3RhdGlvblhcImluIFQ/VC5yb3RhdGlvblg6XCJzaG9ydFJvdGF0aW9uWFwiaW4gVD9ULnNob3J0Um90YXRpb25YK1wiX3Nob3J0XCI6bS5yb3RhdGlvblh8fDAsbS5yb3RhdGlvblgsXCJyb3RhdGlvblhcIix3KSxoLnJvdGF0aW9uWT1zZShcInJvdGF0aW9uWVwiaW4gVD9ULnJvdGF0aW9uWTpcInNob3J0Um90YXRpb25ZXCJpbiBUP1Quc2hvcnRSb3RhdGlvblkrXCJfc2hvcnRcIjptLnJvdGF0aW9uWXx8MCxtLnJvdGF0aW9uWSxcInJvdGF0aW9uWVwiLHcpKSxoLnNrZXdYPW51bGw9PVQuc2tld1g/bS5za2V3WDpzZShULnNrZXdYLG0uc2tld1gpLGguc2tld1k9bnVsbD09VC5za2V3WT9tLnNrZXdZOnNlKFQuc2tld1ksbS5za2V3WSksKHU9aC5za2V3WS1tLnNrZXdZKSYmKGguc2tld1grPXUsaC5yb3RhdGlvbis9dSl9Zm9yKHhlJiZudWxsIT1ULmZvcmNlM0QmJihtLmZvcmNlM0Q9VC5mb3JjZTNELGM9ITApLG0uc2tld1R5cGU9VC5za2V3VHlwZXx8bS5za2V3VHlwZXx8YS5kZWZhdWx0U2tld1R5cGUscD1tLmZvcmNlM0R8fG0uenx8bS5yb3RhdGlvblh8fG0ucm90YXRpb25ZfHxoLnp8fGgucm90YXRpb25YfHxoLnJvdGF0aW9uWXx8aC5wZXJzcGVjdGl2ZSxwfHxudWxsPT1ULnNjYWxlfHwoaC5zY2FsZVo9MSk7LS15Pi0xOylpPXZlW3ldLF89aFtpXS1tW2ldLChfPnZ8fC12Pl98fG51bGwhPU5baV0pJiYoYz0hMCxuPW5ldyBfZShtLGksbVtpXSxfLG4pLGkgaW4gdyYmKG4uZT13W2ldKSxuLnhzMD0wLG4ucGx1Z2luPW8sci5fb3ZlcndyaXRlUHJvcHMucHVzaChuLm4pKTtyZXR1cm4gXz1ULnRyYW5zZm9ybU9yaWdpbiwoX3x8eGUmJnAmJm0uek9yaWdpbikmJih5ZT8oYz0hMCxpPXdlLF89KF98fHEodCxpLHMsITEsXCI1MCUgNTAlXCIpKStcIlwiLG49bmV3IF9lKGcsaSwwLDAsbiwtMSxcInRyYW5zZm9ybU9yaWdpblwiKSxuLmI9Z1tpXSxuLnBsdWdpbj1vLHhlPyhmPW0uek9yaWdpbixfPV8uc3BsaXQoXCIgXCIpLG0uek9yaWdpbj0oXy5sZW5ndGg+MiYmKDA9PT1mfHxcIjBweFwiIT09X1syXSk/cGFyc2VGbG9hdChfWzJdKTpmKXx8MCxuLnhzMD1uLmU9X1swXStcIiBcIisoX1sxXXx8XCI1MCVcIikrXCIgMHB4XCIsbj1uZXcgX2UobSxcInpPcmlnaW5cIiwwLDAsbiwtMSxuLm4pLG4uYj1mLG4ueHMwPW4uZT1tLnpPcmlnaW4pOm4ueHMwPW4uZT1fKTplZShfK1wiXCIsbSkpLGMmJihyLl90cmFuc2Zvcm1UeXBlPXB8fDM9PT10aGlzLl90cmFuc2Zvcm1UeXBlPzM6Miksbn0scHJlZml4OiEwfSksbWUoXCJib3hTaGFkb3dcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IDBweCAwcHggMHB4ICM5OTlcIixwcmVmaXg6ITAsY29sb3I6ITAsbXVsdGk6ITAsa2V5d29yZDpcImluc2V0XCJ9KSxtZShcImJvcmRlclJhZGl1c1wiLHtkZWZhdWx0VmFsdWU6XCIwcHhcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGksbixhKXtlPXRoaXMuZm9ybWF0KGUpO3ZhciBvLGwsaCx1LGYsXyxwLGMsZCxtLGcsdix5LFQsdyx4LGI9W1wiYm9yZGVyVG9wTGVmdFJhZGl1c1wiLFwiYm9yZGVyVG9wUmlnaHRSYWRpdXNcIixcImJvcmRlckJvdHRvbVJpZ2h0UmFkaXVzXCIsXCJib3JkZXJCb3R0b21MZWZ0UmFkaXVzXCJdLFA9dC5zdHlsZTtmb3IoZD1wYXJzZUZsb2F0KHQub2Zmc2V0V2lkdGgpLG09cGFyc2VGbG9hdCh0Lm9mZnNldEhlaWdodCksbz1lLnNwbGl0KFwiIFwiKSxsPTA7Yi5sZW5ndGg+bDtsKyspdGhpcy5wLmluZGV4T2YoXCJib3JkZXJcIikmJihiW2xdPVYoYltsXSkpLGY9dT1xKHQsYltsXSxzLCExLFwiMHB4XCIpLC0xIT09Zi5pbmRleE9mKFwiIFwiKSYmKHU9Zi5zcGxpdChcIiBcIiksZj11WzBdLHU9dVsxXSksXz1oPW9bbF0scD1wYXJzZUZsb2F0KGYpLHY9Zi5zdWJzdHIoKHArXCJcIikubGVuZ3RoKSx5PVwiPVwiPT09Xy5jaGFyQXQoMSkseT8oYz1wYXJzZUludChfLmNoYXJBdCgwKStcIjFcIiwxMCksXz1fLnN1YnN0cigyKSxjKj1wYXJzZUZsb2F0KF8pLGc9Xy5zdWJzdHIoKGMrXCJcIikubGVuZ3RoLSgwPmM/MTowKSl8fFwiXCIpOihjPXBhcnNlRmxvYXQoXyksZz1fLnN1YnN0cigoYytcIlwiKS5sZW5ndGgpKSxcIlwiPT09ZyYmKGc9cltpXXx8diksZyE9PXYmJihUPVEodCxcImJvcmRlckxlZnRcIixwLHYpLHc9USh0LFwiYm9yZGVyVG9wXCIscCx2KSxcIiVcIj09PWc/KGY9MTAwKihUL2QpK1wiJVwiLHU9MTAwKih3L20pK1wiJVwiKTpcImVtXCI9PT1nPyh4PVEodCxcImJvcmRlckxlZnRcIiwxLFwiZW1cIiksZj1UL3grXCJlbVwiLHU9dy94K1wiZW1cIik6KGY9VCtcInB4XCIsdT13K1wicHhcIikseSYmKF89cGFyc2VGbG9hdChmKStjK2csaD1wYXJzZUZsb2F0KHUpK2MrZykpLGE9cGUoUCxiW2xdLGYrXCIgXCIrdSxfK1wiIFwiK2gsITEsXCIwcHhcIixhKTtyZXR1cm4gYX0scHJlZml4OiEwLGZvcm1hdHRlcjpoZShcIjBweCAwcHggMHB4IDBweFwiLCExLCEwKX0pLG1lKFwiYmFja2dyb3VuZFBvc2l0aW9uXCIse2RlZmF1bHRWYWx1ZTpcIjAgMFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7dmFyIG8sbCxoLHUsZixfLHA9XCJiYWNrZ3JvdW5kLXBvc2l0aW9uXCIsZD1zfHxIKHQsbnVsbCksbT10aGlzLmZvcm1hdCgoZD9jP2QuZ2V0UHJvcGVydHlWYWx1ZShwK1wiLXhcIikrXCIgXCIrZC5nZXRQcm9wZXJ0eVZhbHVlKHArXCIteVwiKTpkLmdldFByb3BlcnR5VmFsdWUocCk6dC5jdXJyZW50U3R5bGUuYmFja2dyb3VuZFBvc2l0aW9uWCtcIiBcIit0LmN1cnJlbnRTdHlsZS5iYWNrZ3JvdW5kUG9zaXRpb25ZKXx8XCIwIDBcIiksZz10aGlzLmZvcm1hdChlKTtpZigtMSE9PW0uaW5kZXhPZihcIiVcIikhPSgtMSE9PWcuaW5kZXhPZihcIiVcIikpJiYoXz1xKHQsXCJiYWNrZ3JvdW5kSW1hZ2VcIikucmVwbGFjZShDLFwiXCIpLF8mJlwibm9uZVwiIT09Xykpe2ZvcihvPW0uc3BsaXQoXCIgXCIpLGw9Zy5zcGxpdChcIiBcIiksSS5zZXRBdHRyaWJ1dGUoXCJzcmNcIixfKSxoPTI7LS1oPi0xOyltPW9baF0sdT0tMSE9PW0uaW5kZXhPZihcIiVcIiksdSE9PSgtMSE9PWxbaF0uaW5kZXhPZihcIiVcIikpJiYoZj0wPT09aD90Lm9mZnNldFdpZHRoLUkud2lkdGg6dC5vZmZzZXRIZWlnaHQtSS5oZWlnaHQsb1toXT11P3BhcnNlRmxvYXQobSkvMTAwKmYrXCJweFwiOjEwMCoocGFyc2VGbG9hdChtKS9mKStcIiVcIik7bT1vLmpvaW4oXCIgXCIpfXJldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLG0sZyxuLGEpfSxmb3JtYXR0ZXI6ZWV9KSxtZShcImJhY2tncm91bmRTaXplXCIse2RlZmF1bHRWYWx1ZTpcIjAgMFwiLGZvcm1hdHRlcjplZX0pLG1lKFwicGVyc3BlY3RpdmVcIix7ZGVmYXVsdFZhbHVlOlwiMHB4XCIscHJlZml4OiEwfSksbWUoXCJwZXJzcGVjdGl2ZU9yaWdpblwiLHtkZWZhdWx0VmFsdWU6XCI1MCUgNTAlXCIscHJlZml4OiEwfSksbWUoXCJ0cmFuc2Zvcm1TdHlsZVwiLHtwcmVmaXg6ITB9KSxtZShcImJhY2tmYWNlVmlzaWJpbGl0eVwiLHtwcmVmaXg6ITB9KSxtZShcInVzZXJTZWxlY3RcIix7cHJlZml4OiEwfSksbWUoXCJtYXJnaW5cIix7cGFyc2VyOnVlKFwibWFyZ2luVG9wLG1hcmdpblJpZ2h0LG1hcmdpbkJvdHRvbSxtYXJnaW5MZWZ0XCIpfSksbWUoXCJwYWRkaW5nXCIse3BhcnNlcjp1ZShcInBhZGRpbmdUb3AscGFkZGluZ1JpZ2h0LHBhZGRpbmdCb3R0b20scGFkZGluZ0xlZnRcIil9KSxtZShcImNsaXBcIix7ZGVmYXVsdFZhbHVlOlwicmVjdCgwcHgsMHB4LDBweCwwcHgpXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbyxsLGg7cmV0dXJuIDk+Yz8obD10LmN1cnJlbnRTdHlsZSxoPTg+Yz9cIiBcIjpcIixcIixvPVwicmVjdChcIitsLmNsaXBUb3AraCtsLmNsaXBSaWdodCtoK2wuY2xpcEJvdHRvbStoK2wuY2xpcExlZnQrXCIpXCIsZT10aGlzLmZvcm1hdChlKS5zcGxpdChcIixcIikuam9pbihoKSk6KG89dGhpcy5mb3JtYXQocSh0LHRoaXMucCxzLCExLHRoaXMuZGZsdCkpLGU9dGhpcy5mb3JtYXQoZSkpLHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsbyxlLG4sYSl9fSksbWUoXCJ0ZXh0U2hhZG93XCIse2RlZmF1bHRWYWx1ZTpcIjBweCAwcHggMHB4ICM5OTlcIixjb2xvcjohMCxtdWx0aTohMH0pLG1lKFwiYXV0b1JvdW5kLHN0cmljdFVuaXRzXCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLHMpe3JldHVybiBzfX0pLG1lKFwiYm9yZGVyXCIse2RlZmF1bHRWYWx1ZTpcIjBweCBzb2xpZCAjMDAwXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXtyZXR1cm4gdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSx0aGlzLmZvcm1hdChxKHQsXCJib3JkZXJUb3BXaWR0aFwiLHMsITEsXCIwcHhcIikrXCIgXCIrcSh0LFwiYm9yZGVyVG9wU3R5bGVcIixzLCExLFwic29saWRcIikrXCIgXCIrcSh0LFwiYm9yZGVyVG9wQ29sb3JcIixzLCExLFwiIzAwMFwiKSksdGhpcy5mb3JtYXQoZSksbixhKX0sY29sb3I6ITAsZm9ybWF0dGVyOmZ1bmN0aW9uKHQpe3ZhciBlPXQuc3BsaXQoXCIgXCIpO3JldHVybiBlWzBdK1wiIFwiKyhlWzFdfHxcInNvbGlkXCIpK1wiIFwiKyh0Lm1hdGNoKGxlKXx8W1wiIzAwMFwiXSlbMF19fSksbWUoXCJib3JkZXJXaWR0aFwiLHtwYXJzZXI6dWUoXCJib3JkZXJUb3BXaWR0aCxib3JkZXJSaWdodFdpZHRoLGJvcmRlckJvdHRvbVdpZHRoLGJvcmRlckxlZnRXaWR0aFwiKX0pLG1lKFwiZmxvYXQsY3NzRmxvYXQsc3R5bGVGbG9hdFwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixzKXt2YXIgbj10LnN0eWxlLGE9XCJjc3NGbG9hdFwiaW4gbj9cImNzc0Zsb2F0XCI6XCJzdHlsZUZsb2F0XCI7cmV0dXJuIG5ldyBfZShuLGEsMCwwLHMsLTEsaSwhMSwwLG5bYV0sZSl9fSk7dmFyIGtlPWZ1bmN0aW9uKHQpe3ZhciBlLGk9dGhpcy50LHI9aS5maWx0ZXJ8fHEodGhpcy5kYXRhLFwiZmlsdGVyXCIpLHM9MHx0aGlzLnMrdGhpcy5jKnQ7MTAwPT09cyYmKC0xPT09ci5pbmRleE9mKFwiYXRyaXgoXCIpJiYtMT09PXIuaW5kZXhPZihcInJhZGllbnQoXCIpJiYtMT09PXIuaW5kZXhPZihcIm9hZGVyKFwiKT8oaS5yZW1vdmVBdHRyaWJ1dGUoXCJmaWx0ZXJcIiksZT0hcSh0aGlzLmRhdGEsXCJmaWx0ZXJcIikpOihpLmZpbHRlcj1yLnJlcGxhY2UoeCxcIlwiKSxlPSEwKSksZXx8KHRoaXMueG4xJiYoaS5maWx0ZXI9cj1yfHxcImFscGhhKG9wYWNpdHk9XCIrcytcIilcIiksLTE9PT1yLmluZGV4T2YoXCJwYWNpdHlcIik/MD09PXMmJnRoaXMueG4xfHwoaS5maWx0ZXI9citcIiBhbHBoYShvcGFjaXR5PVwiK3MrXCIpXCIpOmkuZmlsdGVyPXIucmVwbGFjZShULFwib3BhY2l0eT1cIitzKSl9O21lKFwib3BhY2l0eSxhbHBoYSxhdXRvQWxwaGFcIix7ZGVmYXVsdFZhbHVlOlwiMVwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7dmFyIG89cGFyc2VGbG9hdChxKHQsXCJvcGFjaXR5XCIscywhMSxcIjFcIikpLGw9dC5zdHlsZSxoPVwiYXV0b0FscGhhXCI9PT1pO3JldHVyblwic3RyaW5nXCI9PXR5cGVvZiBlJiZcIj1cIj09PWUuY2hhckF0KDEpJiYoZT0oXCItXCI9PT1lLmNoYXJBdCgwKT8tMToxKSpwYXJzZUZsb2F0KGUuc3Vic3RyKDIpKStvKSxoJiYxPT09byYmXCJoaWRkZW5cIj09PXEodCxcInZpc2liaWxpdHlcIixzKSYmMCE9PWUmJihvPTApLFk/bj1uZXcgX2UobCxcIm9wYWNpdHlcIixvLGUtbyxuKToobj1uZXcgX2UobCxcIm9wYWNpdHlcIiwxMDAqbywxMDAqKGUtbyksbiksbi54bjE9aD8xOjAsbC56b29tPTEsbi50eXBlPTIsbi5iPVwiYWxwaGEob3BhY2l0eT1cIituLnMrXCIpXCIsbi5lPVwiYWxwaGEob3BhY2l0eT1cIisobi5zK24uYykrXCIpXCIsbi5kYXRhPXQsbi5wbHVnaW49YSxuLnNldFJhdGlvPWtlKSxoJiYobj1uZXcgX2UobCxcInZpc2liaWxpdHlcIiwwLDAsbiwtMSxudWxsLCExLDAsMCE9PW8/XCJpbmhlcml0XCI6XCJoaWRkZW5cIiwwPT09ZT9cImhpZGRlblwiOlwiaW5oZXJpdFwiKSxuLnhzMD1cImluaGVyaXRcIixyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKG4ubiksci5fb3ZlcndyaXRlUHJvcHMucHVzaChpKSksbn19KTt2YXIgQWU9ZnVuY3Rpb24odCxlKXtlJiYodC5yZW1vdmVQcm9wZXJ0eT8oXCJtc1wiPT09ZS5zdWJzdHIoMCwyKSYmKGU9XCJNXCIrZS5zdWJzdHIoMSkpLHQucmVtb3ZlUHJvcGVydHkoZS5yZXBsYWNlKFAsXCItJDFcIikudG9Mb3dlckNhc2UoKSkpOnQucmVtb3ZlQXR0cmlidXRlKGUpKX0sT2U9ZnVuY3Rpb24odCl7aWYodGhpcy50Ll9nc0NsYXNzUFQ9dGhpcywxPT09dHx8MD09PXQpe3RoaXMudC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLDA9PT10P3RoaXMuYjp0aGlzLmUpO2Zvcih2YXIgZT10aGlzLmRhdGEsaT10aGlzLnQuc3R5bGU7ZTspZS52P2lbZS5wXT1lLnY6QWUoaSxlLnApLGU9ZS5fbmV4dDsxPT09dCYmdGhpcy50Ll9nc0NsYXNzUFQ9PT10aGlzJiYodGhpcy50Ll9nc0NsYXNzUFQ9bnVsbCl9ZWxzZSB0aGlzLnQuZ2V0QXR0cmlidXRlKFwiY2xhc3NcIikhPT10aGlzLmUmJnRoaXMudC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLHRoaXMuZSl9O21lKFwiY2xhc3NOYW1lXCIse3BhcnNlcjpmdW5jdGlvbih0LGUscixuLGEsbyxsKXt2YXIgaCx1LGYsXyxwLGM9dC5nZXRBdHRyaWJ1dGUoXCJjbGFzc1wiKXx8XCJcIixkPXQuc3R5bGUuY3NzVGV4dDtpZihhPW4uX2NsYXNzTmFtZVBUPW5ldyBfZSh0LHIsMCwwLGEsMiksYS5zZXRSYXRpbz1PZSxhLnByPS0xMSxpPSEwLGEuYj1jLHU9JCh0LHMpLGY9dC5fZ3NDbGFzc1BUKXtmb3IoXz17fSxwPWYuZGF0YTtwOylfW3AucF09MSxwPXAuX25leHQ7Zi5zZXRSYXRpbygxKX1yZXR1cm4gdC5fZ3NDbGFzc1BUPWEsYS5lPVwiPVwiIT09ZS5jaGFyQXQoMSk/ZTpjLnJlcGxhY2UoUmVnRXhwKFwiXFxcXHMqXFxcXGJcIitlLnN1YnN0cigyKStcIlxcXFxiXCIpLFwiXCIpKyhcIitcIj09PWUuY2hhckF0KDApP1wiIFwiK2Uuc3Vic3RyKDIpOlwiXCIpLG4uX3R3ZWVuLl9kdXJhdGlvbiYmKHQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIixhLmUpLGg9Ryh0LHUsJCh0KSxsLF8pLHQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIixjKSxhLmRhdGE9aC5maXJzdE1QVCx0LnN0eWxlLmNzc1RleHQ9ZCxhPWEueGZpcnN0PW4ucGFyc2UodCxoLmRpZnMsYSxvKSksYX19KTt2YXIgRGU9ZnVuY3Rpb24odCl7aWYoKDE9PT10fHwwPT09dCkmJnRoaXMuZGF0YS5fdG90YWxUaW1lPT09dGhpcy5kYXRhLl90b3RhbER1cmF0aW9uJiZcImlzRnJvbVN0YXJ0XCIhPT10aGlzLmRhdGEuZGF0YSl7dmFyIGUsaSxyLHMsbj10aGlzLnQuc3R5bGUsYT1vLnRyYW5zZm9ybS5wYXJzZTtpZihcImFsbFwiPT09dGhpcy5lKW4uY3NzVGV4dD1cIlwiLHM9ITA7ZWxzZSBmb3IoZT10aGlzLmUuc3BsaXQoXCIsXCIpLHI9ZS5sZW5ndGg7LS1yPi0xOylpPWVbcl0sb1tpXSYmKG9baV0ucGFyc2U9PT1hP3M9ITA6aT1cInRyYW5zZm9ybU9yaWdpblwiPT09aT93ZTpvW2ldLnApLEFlKG4saSk7cyYmKEFlKG4seWUpLHRoaXMudC5fZ3NUcmFuc2Zvcm0mJmRlbGV0ZSB0aGlzLnQuX2dzVHJhbnNmb3JtKX19O2ZvcihtZShcImNsZWFyUHJvcHNcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxyLHMsbil7cmV0dXJuIG49bmV3IF9lKHQsciwwLDAsbiwyKSxuLnNldFJhdGlvPURlLG4uZT1lLG4ucHI9LTEwLG4uZGF0YT1zLl90d2VlbixpPSEwLG59fSksbD1cImJlemllcix0aHJvd1Byb3BzLHBoeXNpY3NQcm9wcyxwaHlzaWNzMkRcIi5zcGxpdChcIixcIiksY2U9bC5sZW5ndGg7Y2UtLTspZ2UobFtjZV0pO2w9YS5wcm90b3R5cGUsbC5fZmlyc3RQVD1udWxsLGwuX29uSW5pdFR3ZWVuPWZ1bmN0aW9uKHQsZSxvKXtpZighdC5ub2RlVHlwZSlyZXR1cm4hMTt0aGlzLl90YXJnZXQ9dCx0aGlzLl90d2Vlbj1vLHRoaXMuX3ZhcnM9ZSxoPWUuYXV0b1JvdW5kLGk9ITEscj1lLnN1ZmZpeE1hcHx8YS5zdWZmaXhNYXAscz1IKHQsXCJcIiksbj10aGlzLl9vdmVyd3JpdGVQcm9wczt2YXIgbCxfLGMsZCxtLGcsdix5LFQseD10LnN0eWxlO2lmKHUmJlwiXCI9PT14LnpJbmRleCYmKGw9cSh0LFwiekluZGV4XCIscyksKFwiYXV0b1wiPT09bHx8XCJcIj09PWwpJiZ0aGlzLl9hZGRMYXp5U2V0KHgsXCJ6SW5kZXhcIiwwKSksXCJzdHJpbmdcIj09dHlwZW9mIGUmJihkPXguY3NzVGV4dCxsPSQodCxzKSx4LmNzc1RleHQ9ZCtcIjtcIitlLGw9Ryh0LGwsJCh0KSkuZGlmcywhWSYmdy50ZXN0KGUpJiYobC5vcGFjaXR5PXBhcnNlRmxvYXQoUmVnRXhwLiQxKSksZT1sLHguY3NzVGV4dD1kKSx0aGlzLl9maXJzdFBUPV89dGhpcy5wYXJzZSh0LGUsbnVsbCksdGhpcy5fdHJhbnNmb3JtVHlwZSl7Zm9yKFQ9Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGUseWU/ZiYmKHU9ITAsXCJcIj09PXguekluZGV4JiYodj1xKHQsXCJ6SW5kZXhcIixzKSwoXCJhdXRvXCI9PT12fHxcIlwiPT09dikmJnRoaXMuX2FkZExhenlTZXQoeCxcInpJbmRleFwiLDApKSxwJiZ0aGlzLl9hZGRMYXp5U2V0KHgsXCJXZWJraXRCYWNrZmFjZVZpc2liaWxpdHlcIix0aGlzLl92YXJzLldlYmtpdEJhY2tmYWNlVmlzaWJpbGl0eXx8KFQ/XCJ2aXNpYmxlXCI6XCJoaWRkZW5cIikpKTp4Lnpvb209MSxjPV87YyYmYy5fbmV4dDspYz1jLl9uZXh0O3k9bmV3IF9lKHQsXCJ0cmFuc2Zvcm1cIiwwLDAsbnVsbCwyKSx0aGlzLl9saW5rQ1NTUCh5LG51bGwsYykseS5zZXRSYXRpbz1UJiZ4ZT9DZTp5ZT9SZTpTZSx5LmRhdGE9dGhpcy5fdHJhbnNmb3JtfHxQZSh0LHMsITApLG4ucG9wKCl9aWYoaSl7Zm9yKDtfOyl7Zm9yKGc9Xy5fbmV4dCxjPWQ7YyYmYy5wcj5fLnByOyljPWMuX25leHQ7KF8uX3ByZXY9Yz9jLl9wcmV2Om0pP18uX3ByZXYuX25leHQ9XzpkPV8sKF8uX25leHQ9Yyk/Yy5fcHJldj1fOm09XyxfPWd9dGhpcy5fZmlyc3RQVD1kfXJldHVybiEwfSxsLnBhcnNlPWZ1bmN0aW9uKHQsZSxpLG4pe3ZhciBhLGwsdSxmLF8scCxjLGQsbSxnLHY9dC5zdHlsZTtmb3IoYSBpbiBlKXA9ZVthXSxsPW9bYV0sbD9pPWwucGFyc2UodCxwLGEsdGhpcyxpLG4sZSk6KF89cSh0LGEscykrXCJcIixtPVwic3RyaW5nXCI9PXR5cGVvZiBwLFwiY29sb3JcIj09PWF8fFwiZmlsbFwiPT09YXx8XCJzdHJva2VcIj09PWF8fC0xIT09YS5pbmRleE9mKFwiQ29sb3JcIil8fG0mJmIudGVzdChwKT8obXx8KHA9b2UocCkscD0ocC5sZW5ndGg+Mz9cInJnYmEoXCI6XCJyZ2IoXCIpK3Auam9pbihcIixcIikrXCIpXCIpLGk9cGUodixhLF8scCwhMCxcInRyYW5zcGFyZW50XCIsaSwwLG4pKTohbXx8LTE9PT1wLmluZGV4T2YoXCIgXCIpJiYtMT09PXAuaW5kZXhPZihcIixcIik/KHU9cGFyc2VGbG9hdChfKSxjPXV8fDA9PT11P18uc3Vic3RyKCh1K1wiXCIpLmxlbmd0aCk6XCJcIiwoXCJcIj09PV98fFwiYXV0b1wiPT09XykmJihcIndpZHRoXCI9PT1hfHxcImhlaWdodFwiPT09YT8odT10ZSh0LGEscyksYz1cInB4XCIpOlwibGVmdFwiPT09YXx8XCJ0b3BcIj09PWE/KHU9Wih0LGEscyksYz1cInB4XCIpOih1PVwib3BhY2l0eVwiIT09YT8wOjEsYz1cIlwiKSksZz1tJiZcIj1cIj09PXAuY2hhckF0KDEpLGc/KGY9cGFyc2VJbnQocC5jaGFyQXQoMCkrXCIxXCIsMTApLHA9cC5zdWJzdHIoMiksZio9cGFyc2VGbG9hdChwKSxkPXAucmVwbGFjZSh5LFwiXCIpKTooZj1wYXJzZUZsb2F0KHApLGQ9bT9wLnN1YnN0cigoZitcIlwiKS5sZW5ndGgpfHxcIlwiOlwiXCIpLFwiXCI9PT1kJiYoZD1hIGluIHI/clthXTpjKSxwPWZ8fDA9PT1mPyhnP2YrdTpmKStkOmVbYV0sYyE9PWQmJlwiXCIhPT1kJiYoZnx8MD09PWYpJiZ1JiYodT1RKHQsYSx1LGMpLFwiJVwiPT09ZD8odS89USh0LGEsMTAwLFwiJVwiKS8xMDAsZS5zdHJpY3RVbml0cyE9PSEwJiYoXz11K1wiJVwiKSk6XCJlbVwiPT09ZD91Lz1RKHQsYSwxLFwiZW1cIik6XCJweFwiIT09ZCYmKGY9USh0LGEsZixkKSxkPVwicHhcIiksZyYmKGZ8fDA9PT1mKSYmKHA9Zit1K2QpKSxnJiYoZis9dSksIXUmJjAhPT11fHwhZiYmMCE9PWY/dm9pZCAwIT09dlthXSYmKHB8fFwiTmFOXCIhPXArXCJcIiYmbnVsbCE9cCk/KGk9bmV3IF9lKHYsYSxmfHx1fHwwLDAsaSwtMSxhLCExLDAsXyxwKSxpLnhzMD1cIm5vbmVcIiE9PXB8fFwiZGlzcGxheVwiIT09YSYmLTE9PT1hLmluZGV4T2YoXCJTdHlsZVwiKT9wOl8pOlUoXCJpbnZhbGlkIFwiK2ErXCIgdHdlZW4gdmFsdWU6IFwiK2VbYV0pOihpPW5ldyBfZSh2LGEsdSxmLXUsaSwwLGEsaCE9PSExJiYoXCJweFwiPT09ZHx8XCJ6SW5kZXhcIj09PWEpLDAsXyxwKSxpLnhzMD1kKSk6aT1wZSh2LGEsXyxwLCEwLG51bGwsaSwwLG4pKSxuJiZpJiYhaS5wbHVnaW4mJihpLnBsdWdpbj1uKTtyZXR1cm4gaX0sbC5zZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZSxpLHIscz10aGlzLl9maXJzdFBULG49MWUtNjtpZigxIT09dHx8dGhpcy5fdHdlZW4uX3RpbWUhPT10aGlzLl90d2Vlbi5fZHVyYXRpb24mJjAhPT10aGlzLl90d2Vlbi5fdGltZSlpZih0fHx0aGlzLl90d2Vlbi5fdGltZSE9PXRoaXMuX3R3ZWVuLl9kdXJhdGlvbiYmMCE9PXRoaXMuX3R3ZWVuLl90aW1lfHx0aGlzLl90d2Vlbi5fcmF3UHJldlRpbWU9PT0tMWUtNilmb3IoO3M7KXtpZihlPXMuYyp0K3MucyxzLnI/ZT1NYXRoLnJvdW5kKGUpOm4+ZSYmZT4tbiYmKGU9MCkscy50eXBlKWlmKDE9PT1zLnR5cGUpaWYocj1zLmwsMj09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMjtlbHNlIGlmKDM9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czM7ZWxzZSBpZig0PT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzK3MueG4zK3MueHM0O2Vsc2UgaWYoNT09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMytzLnhuMytzLnhzNCtzLnhuNCtzLnhzNTtlbHNle2ZvcihpPXMueHMwK2Urcy54czEscj0xO3MubD5yO3IrKylpKz1zW1wieG5cIityXStzW1wieHNcIisocisxKV07cy50W3MucF09aX1lbHNlLTE9PT1zLnR5cGU/cy50W3MucF09cy54czA6cy5zZXRSYXRpbyYmcy5zZXRSYXRpbyh0KTtlbHNlIHMudFtzLnBdPWUrcy54czA7cz1zLl9uZXh0fWVsc2UgZm9yKDtzOykyIT09cy50eXBlP3MudFtzLnBdPXMuYjpzLnNldFJhdGlvKHQpLHM9cy5fbmV4dDtlbHNlIGZvcig7czspMiE9PXMudHlwZT9zLnRbcy5wXT1zLmU6cy5zZXRSYXRpbyh0KSxzPXMuX25leHR9LGwuX2VuYWJsZVRyYW5zZm9ybXM9ZnVuY3Rpb24odCl7dGhpcy5fdHJhbnNmb3JtVHlwZT10fHwzPT09dGhpcy5fdHJhbnNmb3JtVHlwZT8zOjIsdGhpcy5fdHJhbnNmb3JtPXRoaXMuX3RyYW5zZm9ybXx8UGUodGhpcy5fdGFyZ2V0LHMsITApfTt2YXIgTWU9ZnVuY3Rpb24oKXt0aGlzLnRbdGhpcy5wXT10aGlzLmUsdGhpcy5kYXRhLl9saW5rQ1NTUCh0aGlzLHRoaXMuX25leHQsbnVsbCwhMCl9O2wuX2FkZExhenlTZXQ9ZnVuY3Rpb24odCxlLGkpe3ZhciByPXRoaXMuX2ZpcnN0UFQ9bmV3IF9lKHQsZSwwLDAsdGhpcy5fZmlyc3RQVCwyKTtyLmU9aSxyLnNldFJhdGlvPU1lLHIuZGF0YT10aGlzfSxsLl9saW5rQ1NTUD1mdW5jdGlvbih0LGUsaSxyKXtyZXR1cm4gdCYmKGUmJihlLl9wcmV2PXQpLHQuX25leHQmJih0Ll9uZXh0Ll9wcmV2PXQuX3ByZXYpLHQuX3ByZXY/dC5fcHJldi5fbmV4dD10Ll9uZXh0OnRoaXMuX2ZpcnN0UFQ9PT10JiYodGhpcy5fZmlyc3RQVD10Ll9uZXh0LHI9ITApLGk/aS5fbmV4dD10OnJ8fG51bGwhPT10aGlzLl9maXJzdFBUfHwodGhpcy5fZmlyc3RQVD10KSx0Ll9uZXh0PWUsdC5fcHJldj1pKSx0fSxsLl9raWxsPWZ1bmN0aW9uKGUpe3ZhciBpLHIscyxuPWU7aWYoZS5hdXRvQWxwaGF8fGUuYWxwaGEpe249e307Zm9yKHIgaW4gZSluW3JdPWVbcl07bi5vcGFjaXR5PTEsbi5hdXRvQWxwaGEmJihuLnZpc2liaWxpdHk9MSl9cmV0dXJuIGUuY2xhc3NOYW1lJiYoaT10aGlzLl9jbGFzc05hbWVQVCkmJihzPWkueGZpcnN0LHMmJnMuX3ByZXY/dGhpcy5fbGlua0NTU1Aocy5fcHJldixpLl9uZXh0LHMuX3ByZXYuX3ByZXYpOnM9PT10aGlzLl9maXJzdFBUJiYodGhpcy5fZmlyc3RQVD1pLl9uZXh0KSxpLl9uZXh0JiZ0aGlzLl9saW5rQ1NTUChpLl9uZXh0LGkuX25leHQuX25leHQscy5fcHJldiksdGhpcy5fY2xhc3NOYW1lUFQ9bnVsbCksdC5wcm90b3R5cGUuX2tpbGwuY2FsbCh0aGlzLG4pfTt2YXIgTGU9ZnVuY3Rpb24odCxlLGkpe3ZhciByLHMsbixhO2lmKHQuc2xpY2UpZm9yKHM9dC5sZW5ndGg7LS1zPi0xOylMZSh0W3NdLGUsaSk7ZWxzZSBmb3Iocj10LmNoaWxkTm9kZXMscz1yLmxlbmd0aDstLXM+LTE7KW49cltzXSxhPW4udHlwZSxuLnN0eWxlJiYoZS5wdXNoKCQobikpLGkmJmkucHVzaChuKSksMSE9PWEmJjkhPT1hJiYxMSE9PWF8fCFuLmNoaWxkTm9kZXMubGVuZ3RofHxMZShuLGUsaSl9O3JldHVybiBhLmNhc2NhZGVUbz1mdW5jdGlvbih0LGkscil7dmFyIHMsbixhLG89ZS50byh0LGksciksbD1bb10saD1bXSx1PVtdLGY9W10sXz1lLl9pbnRlcm5hbHMucmVzZXJ2ZWRQcm9wcztmb3IodD1vLl90YXJnZXRzfHxvLnRhcmdldCxMZSh0LGgsZiksby5yZW5kZXIoaSwhMCksTGUodCx1KSxvLnJlbmRlcigwLCEwKSxvLl9lbmFibGVkKCEwKSxzPWYubGVuZ3RoOy0tcz4tMTspaWYobj1HKGZbc10saFtzXSx1W3NdKSxuLmZpcnN0TVBUKXtuPW4uZGlmcztcclxuZm9yKGEgaW4gcilfW2FdJiYoblthXT1yW2FdKTtsLnB1c2goZS50byhmW3NdLGksbikpfXJldHVybiBsfSx0LmFjdGl2YXRlKFthXSksYX0sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuNy4zXHJcbiAqIERBVEU6IDIwMTQtMDEtMTRcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt2YXIgdD1kb2N1bWVudC5kb2N1bWVudEVsZW1lbnQsZT13aW5kb3csaT1mdW5jdGlvbihpLHMpe3ZhciByPVwieFwiPT09cz9cIldpZHRoXCI6XCJIZWlnaHRcIixuPVwic2Nyb2xsXCIrcixhPVwiY2xpZW50XCIrcixvPWRvY3VtZW50LmJvZHk7cmV0dXJuIGk9PT1lfHxpPT09dHx8aT09PW8/TWF0aC5tYXgodFtuXSxvW25dKS0oZVtcImlubmVyXCIrcl18fE1hdGgubWF4KHRbYV0sb1thXSkpOmlbbl0taVtcIm9mZnNldFwiK3JdfSxzPXdpbmRvdy5fZ3NEZWZpbmUucGx1Z2luKHtwcm9wTmFtZTpcInNjcm9sbFRvXCIsQVBJOjIsdmVyc2lvbjpcIjEuNy4zXCIsaW5pdDpmdW5jdGlvbih0LHMscil7cmV0dXJuIHRoaXMuX3dkdz10PT09ZSx0aGlzLl90YXJnZXQ9dCx0aGlzLl90d2Vlbj1yLFwib2JqZWN0XCIhPXR5cGVvZiBzJiYocz17eTpzfSksdGhpcy5fYXV0b0tpbGw9cy5hdXRvS2lsbCE9PSExLHRoaXMueD10aGlzLnhQcmV2PXRoaXMuZ2V0WCgpLHRoaXMueT10aGlzLnlQcmV2PXRoaXMuZ2V0WSgpLG51bGwhPXMueD8odGhpcy5fYWRkVHdlZW4odGhpcyxcInhcIix0aGlzLngsXCJtYXhcIj09PXMueD9pKHQsXCJ4XCIpOnMueCxcInNjcm9sbFRvX3hcIiwhMCksdGhpcy5fb3ZlcndyaXRlUHJvcHMucHVzaChcInNjcm9sbFRvX3hcIikpOnRoaXMuc2tpcFg9ITAsbnVsbCE9cy55Pyh0aGlzLl9hZGRUd2Vlbih0aGlzLFwieVwiLHRoaXMueSxcIm1heFwiPT09cy55P2kodCxcInlcIik6cy55LFwic2Nyb2xsVG9feVwiLCEwKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5wdXNoKFwic2Nyb2xsVG9feVwiKSk6dGhpcy5za2lwWT0hMCwhMH0sc2V0OmZ1bmN0aW9uKHQpe3RoaXMuX3N1cGVyLnNldFJhdGlvLmNhbGwodGhpcyx0KTt2YXIgcz10aGlzLl93ZHd8fCF0aGlzLnNraXBYP3RoaXMuZ2V0WCgpOnRoaXMueFByZXYscj10aGlzLl93ZHd8fCF0aGlzLnNraXBZP3RoaXMuZ2V0WSgpOnRoaXMueVByZXYsbj1yLXRoaXMueVByZXYsYT1zLXRoaXMueFByZXY7dGhpcy5fYXV0b0tpbGwmJighdGhpcy5za2lwWCYmKGE+N3x8LTc+YSkmJmkodGhpcy5fdGFyZ2V0LFwieFwiKT5zJiYodGhpcy5za2lwWD0hMCksIXRoaXMuc2tpcFkmJihuPjd8fC03Pm4pJiZpKHRoaXMuX3RhcmdldCxcInlcIik+ciYmKHRoaXMuc2tpcFk9ITApLHRoaXMuc2tpcFgmJnRoaXMuc2tpcFkmJnRoaXMuX3R3ZWVuLmtpbGwoKSksdGhpcy5fd2R3P2Uuc2Nyb2xsVG8odGhpcy5za2lwWD9zOnRoaXMueCx0aGlzLnNraXBZP3I6dGhpcy55KToodGhpcy5za2lwWXx8KHRoaXMuX3RhcmdldC5zY3JvbGxUb3A9dGhpcy55KSx0aGlzLnNraXBYfHwodGhpcy5fdGFyZ2V0LnNjcm9sbExlZnQ9dGhpcy54KSksdGhpcy54UHJldj10aGlzLngsdGhpcy55UHJldj10aGlzLnl9fSkscj1zLnByb3RvdHlwZTtzLm1heD1pLHIuZ2V0WD1mdW5jdGlvbigpe3JldHVybiB0aGlzLl93ZHc/bnVsbCE9ZS5wYWdlWE9mZnNldD9lLnBhZ2VYT2Zmc2V0Om51bGwhPXQuc2Nyb2xsTGVmdD90LnNjcm9sbExlZnQ6ZG9jdW1lbnQuYm9keS5zY3JvbGxMZWZ0OnRoaXMuX3RhcmdldC5zY3JvbGxMZWZ0fSxyLmdldFk9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fd2R3P251bGwhPWUucGFnZVlPZmZzZXQ/ZS5wYWdlWU9mZnNldDpudWxsIT10LnNjcm9sbFRvcD90LnNjcm9sbFRvcDpkb2N1bWVudC5ib2R5LnNjcm9sbFRvcDp0aGlzLl90YXJnZXQuc2Nyb2xsVG9wfSxyLl9raWxsPWZ1bmN0aW9uKHQpe3JldHVybiB0LnNjcm9sbFRvX3gmJih0aGlzLnNraXBYPSEwKSx0LnNjcm9sbFRvX3kmJih0aGlzLnNraXBZPSEwKSx0aGlzLl9zdXBlci5fa2lsbC5jYWxsKHRoaXMsdCl9fSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7Il19
