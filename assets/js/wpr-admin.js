(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
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
    var excluded = ['cloudflare_auto_settings', 'cloudflare_devmode'];

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
        $('#wpr_enable_mobile_cache').removeClass('wpr-isLoading'); // Set values of mobile cache and separate cache files for mobiles option to 1.

        $('#cache_mobile').val(1);
        $('#do_caching_mobile_files').val(1);
      }
    });
  });
});

},{}],2:[function(require,module,exports){
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

},{"../global/ajax.js":1,"../global/beacon.js":3,"../global/countdown.js":4,"../global/fields.js":5,"../global/main.js":6,"../global/pageManager.js":7,"../global/rocketcdn.js":8,"../lib/greensock/TimelineLite.min.js":9,"../lib/greensock/TweenLite.min.js":10,"../lib/greensock/easing/EasePack.min.js":11,"../lib/greensock/plugins/CSSPlugin.min.js":12,"../lib/greensock/plugins/ScrollToPlugin.min.js":13}],3:[function(require,module,exports){
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
});

},{}],4:[function(require,module,exports){
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

},{}],5:[function(require,module,exports){
"use strict";

var $ = jQuery;
$(document).ready(function () {
  /***
  * Check parent / show children
  ***/
  function wprShowChildren(aElem) {
    var parentId, $children;
    aElem = $(aElem);
    parentId = aElem.attr('id');
    $children = $('[data-parent="' + parentId + '"]'); // Test check for switch

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
    } // Go recursive to the last parent.


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
      var visiblePart = concrete.val.slice(-4); // Combine the hidden and visible parts

      var maskedValue = hiddenPart + visiblePart;
      proxy_selector.val(maskedValue);
    } // Ensure events are not added more than once


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
  } // Update the concrete field when the proxy is updated.


  maskField($('#cloudflare_api_key_mask'), $('#cloudflare_api_key'));
  maskField($('#cloudflare_zone_id_mask'), $('#cloudflare_zone_id')); // Display/Hide childern fields on checkbox change.

  $('.wpr-isParent input[type=checkbox]').on('change', function () {
    wprShowChildren($(this));
  }); // On page load, display the active fields.

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
  var $warningParentInput = $('.wpr-field--parent input[type=checkbox]'); // If already checked

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
        $children = $('[data-parent="' + parentId + '"]'); // Check warning parent

    if ($thisCheckbox.is(':checked')) {
      $warningField.addClass('wpr-isOpen');
      $thisCheckbox.prop('checked', false);
      aElem.trigger('change');
      var $warningButton = $warningField.find('.wpr-button'); // Validate the warning

      $warningButton.on('click', function () {
        $thisCheckbox.prop('checked', true);
        $warningField.removeClass('wpr-isOpen');
        $children.addClass('wpr-isOpen'); // If next elem = disabled

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
    var $warningButton = $warningField.find('.wpr-button'); // Validate the warning

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
  $(".wpr-multiple-select .wpr-list-header-arrow").click(function (e) {
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
});

},{}],6:[function(require,module,exports){
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
  } // Display CTA within the popin `What info will we collect?`


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

},{}],7:[function(require,module,exports){
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
  refThis.getBodyTop(); // If url page change

  window.onhashchange = function () {
    refThis.detectID();
  }; // If hash already exist (after refresh page for example)


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
  } // Click link same hash


  for (var i = 0; i < this.$links.length; i++) {
    this.$links[i].onclick = function () {
      refThis.getBodyTop();
      var hrefSplit = this.href.split('#')[1];

      if (hrefSplit == refThis.pageId && hrefSplit != undefined) {
        refThis.detectID();
        return false;
      }
    };
  } // Click links not WP rocket to reset hash


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
  document.documentElement.scrollTop = refThis.bodyTop; // Hide other pages

  for (var i = 0; i < this.$pages.length; i++) {
    this.$pages[i].style.display = 'none';
  }

  for (var i = 0; i < this.$menuItems.length; i++) {
    this.$menuItems[i].classList.remove('isActive');
  } // Show current default page


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
  this.$content.classList.add('isNotFull'); // Exception for dashboard

  if (this.pageId == "dashboard") {
    this.$sidebar.style.display = 'none';
    this.$tips.style.display = 'none';
    this.$submitButton.style.display = 'none';
    this.$content.classList.remove('isNotFull');
  } // Exception for addons


  if (this.pageId == "addons") {
    this.$submitButton.style.display = 'none';
  } // Exception for database


  if (this.pageId == "database") {
    this.$submitButton.style.display = 'none';
  } // Exception for tools and addons


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
};

},{}],8:[function(require,module,exports){
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

},{}],9:[function(require,module,exports){
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

},{}],11:[function(require,module,exports){
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

},{}],13:[function(require,module,exports){
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

},{}]},{},[2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzcmMvanMvZ2xvYmFsL2FqYXguanMiLCJzcmMvanMvZ2xvYmFsL2FwcC5qcyIsInNyYy9qcy9nbG9iYWwvYmVhY29uLmpzIiwic3JjL2pzL2dsb2JhbC9jb3VudGRvd24uanMiLCJzcmMvanMvZ2xvYmFsL2ZpZWxkcy5qcyIsInNyYy9qcy9nbG9iYWwvbWFpbi5qcyIsInNyYy9qcy9nbG9iYWwvcGFnZU1hbmFnZXIuanMiLCJzcmMvanMvZ2xvYmFsL3JvY2tldGNkbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL1RpbWVsaW5lTGl0ZS5taW4uanMiLCJzcmMvanMvbGliL2dyZWVuc29jay9Ud2VlbkxpdGUubWluLmpzIiwic3JjL2pzL2xpYi9ncmVlbnNvY2svZWFzaW5nL0Vhc2VQYWNrLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyIsInNyYy9qcy9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvU2Nyb2xsVG9QbHVnaW4ubWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQSxJQUFJLENBQUMsR0FBRyxNQUFSO0FBQ0EsQ0FBQyxDQUFDLFFBQUQsQ0FBRCxDQUFZLEtBQVosQ0FBa0IsWUFBVTtBQUN4QjtBQUNKO0FBQ0E7QUFDSSxNQUFJLGFBQWEsR0FBRyxLQUFwQjtBQUNBLEVBQUEsQ0FBQyxDQUFDLDZCQUFELENBQUQsQ0FBaUMsRUFBakMsQ0FBb0MsT0FBcEMsRUFBNkMsVUFBUyxDQUFULEVBQVk7QUFDckQsUUFBRyxDQUFDLGFBQUosRUFBa0I7QUFDZCxVQUFJLE1BQU0sR0FBRyxDQUFDLENBQUMsSUFBRCxDQUFkO0FBQ0EsVUFBSSxPQUFPLEdBQUcsQ0FBQyxDQUFDLG1CQUFELENBQWY7QUFDQSxVQUFJLE1BQU0sR0FBRyxDQUFDLENBQUMsc0JBQUQsQ0FBZDtBQUVBLE1BQUEsQ0FBQyxDQUFDLGNBQUY7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Q0FBQyxDQUFDLFdBQVIsR0FBb0IsQ0FBQyxDQUFDLFdBQXRCLEdBQWtDLFFBQU0sQ0FBQyxDQUFDLFNBQVIsR0FBa0IsQ0FBQyxDQUFDLFNBQXBCLEdBQThCLFFBQVEsQ0FBQyxJQUFULENBQWMsU0FBeEYsR0FBa0csS0FBSyxPQUFMLENBQWEsU0FBdEg7QUFBZ0ksR0FBalQsRUFBa1QsQ0FBQyxDQUFDLEtBQUYsR0FBUSxVQUFTLENBQVQsRUFBVztBQUFDLFdBQU8sQ0FBQyxDQUFDLFVBQUYsS0FBZSxLQUFLLEtBQUwsR0FBVyxDQUFDLENBQTNCLEdBQThCLENBQUMsQ0FBQyxVQUFGLEtBQWUsS0FBSyxLQUFMLEdBQVcsQ0FBQyxDQUEzQixDQUE5QixFQUE0RCxLQUFLLE1BQUwsQ0FBWSxLQUFaLENBQWtCLElBQWxCLENBQXVCLElBQXZCLEVBQTRCLENBQTVCLENBQW5FO0FBQWtHLEdBQXhhO0FBQXlhLENBQXh6RCxHQUEwekQsTUFBTSxDQUFDLFNBQVAsSUFBa0IsTUFBTSxDQUFDLFFBQVAsQ0FBZ0IsR0FBaEIsSUFBNTBEIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuICAgIC8qKlxuICAgICAqIFJlZnJlc2ggTGljZW5zZSBkYXRhXG4gICAgICovXG4gICAgdmFyIF9pc1JlZnJlc2hpbmcgPSBmYWxzZTtcbiAgICAkKCcjd3ByLWFjdGlvbi1yZWZyZXNoX2FjY291bnQnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGlmKCFfaXNSZWZyZXNoaW5nKXtcbiAgICAgICAgICAgIHZhciBidXR0b24gPSAkKHRoaXMpO1xuICAgICAgICAgICAgdmFyIGFjY291bnQgPSAkKCcjd3ByLWFjY291bnQtZGF0YScpO1xuICAgICAgICAgICAgdmFyIGV4cGlyZSA9ICQoJyN3cHItZXhwaXJhdGlvbi1kYXRhJyk7XG5cbiAgICAgICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIF9pc1JlZnJlc2hpbmcgPSB0cnVlO1xuICAgICAgICAgICAgYnV0dG9uLnRyaWdnZXIoICdibHVyJyApO1xuICAgICAgICAgICAgYnV0dG9uLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICBleHBpcmUucmVtb3ZlQ2xhc3MoJ3dwci1pc1ZhbGlkIHdwci1pc0ludmFsaWQnKTtcblxuICAgICAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfcmVmcmVzaF9jdXN0b21lcl9kYXRhJyxcbiAgICAgICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2UsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBmdW5jdGlvbihyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICBidXR0b24ucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uLmFkZENsYXNzKCd3cHItaXNIaWRkZW4nKTtcblxuICAgICAgICAgICAgICAgICAgICBpZiAoIHRydWUgPT09IHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBhY2NvdW50Lmh0bWwocmVzcG9uc2UuZGF0YS5saWNlbnNlX3R5cGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgZXhwaXJlLmFkZENsYXNzKHJlc3BvbnNlLmRhdGEubGljZW5zZV9jbGFzcykuaHRtbChyZXNwb25zZS5kYXRhLmxpY2Vuc2VfZXhwaXJhdGlvbik7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWljb24tcmVmcmVzaCB3cHItaXNIaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pY29uLWNoZWNrJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LCAyNTApO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIGVsc2V7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbi5yZW1vdmVDbGFzcygnd3ByLWljb24tcmVmcmVzaCB3cHItaXNIaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b24uYWRkQ2xhc3MoJ3dwci1pY29uLWNsb3NlJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LCAyNTApO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKHtvbkNvbXBsZXRlOmZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgX2lzUmVmcmVzaGluZyA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgfX0pXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jys9d3ByLWlzSGlkZGVuJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pY29uLWNoZWNrJ319LCAwLjI1KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOictPXdwci1pY29uLWNsb3NlJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgICAuc2V0KGJ1dHRvbiwge2Nzczp7Y2xhc3NOYW1lOicrPXdwci1pY29uLXJlZnJlc2gnfX0sIDAuMjUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5zZXQoYnV0dG9uLCB7Y3NzOntjbGFzc05hbWU6Jy09d3ByLWlzSGlkZGVuJ319KVxuICAgICAgICAgICAgICAgICAgICAgICAgO1xuICAgICAgICAgICAgICAgICAgICB9LCAyMDAwKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICApO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9KTtcblxuICAgIC8qKlxuICAgICAqIFNhdmUgVG9nZ2xlIG9wdGlvbiB2YWx1ZXMgb24gY2hhbmdlXG4gICAgICovXG4gICAgJCgnLndwci1yYWRpbyBpbnB1dFt0eXBlPWNoZWNrYm94XScpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgdmFyIG5hbWUgID0gJCh0aGlzKS5hdHRyKCdpZCcpO1xuICAgICAgICB2YXIgdmFsdWUgPSAkKHRoaXMpLnByb3AoJ2NoZWNrZWQnKSA/IDEgOiAwO1xuXG5cdFx0dmFyIGV4Y2x1ZGVkID0gWyAnY2xvdWRmbGFyZV9hdXRvX3NldHRpbmdzJywgJ2Nsb3VkZmxhcmVfZGV2bW9kZScgXTtcblx0XHRpZiAoIGV4Y2x1ZGVkLmluZGV4T2YoIG5hbWUgKSA+PSAwICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF90b2dnbGVfb3B0aW9uJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZSxcbiAgICAgICAgICAgICAgICBvcHRpb246IHtcbiAgICAgICAgICAgICAgICAgICAgbmFtZTogbmFtZSxcbiAgICAgICAgICAgICAgICAgICAgdmFsdWU6IHZhbHVlXG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGZ1bmN0aW9uKHJlc3BvbnNlKSB7fVxuICAgICAgICApO1xuXHR9KTtcblxuXHQvKipcbiAgICAgKiBTYXZlIGVuYWJsZSBDUENTUyBmb3IgbW9iaWxlcyBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9tb2JpbGVfY3Bjc3MnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBDUENTUyBidG4gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX21vYmlsZV9jcGNzcycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLWhpZGUtb24tY2xpY2snKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1zaG93LW9uLWNsaWNrJykuc2hvdygpO1xuXHRcdFx0XHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfbW9iaWxlX2NwY3NzJykucmVtb3ZlQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuICAgICAgICApO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogU2F2ZSBlbmFibGUgR29vZ2xlIEZvbnRzIE9wdGltaXphdGlvbiBvcHRpb24uXG4gICAgICovXG4gICAgJCgnI3dwci1hY3Rpb24tcm9ja2V0X2VuYWJsZV9nb29nbGVfZm9udHMnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHItYWN0aW9uLXJvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJykuYWRkQ2xhc3MoJ3dwci1pc0xvYWRpbmcnKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9lbmFibGVfZ29vZ2xlX2ZvbnRzJyxcbiAgICAgICAgICAgICAgICBfYWpheF9ub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQvLyBIaWRlIE1vYmlsZSBDUENTUyBidG4gb24gc3VjY2Vzcy5cblx0XHRcdFx0XHQkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcud3ByLWhpZGUtb24tY2xpY2snKS5oaWRlKCk7XG5cdFx0XHRcdFx0JCgnLndwci1zaG93LW9uLWNsaWNrJykuc2hvdygpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjd3ByLWFjdGlvbi1yb2NrZXRfZW5hYmxlX2dvb2dsZV9mb250cycpLnJlbW92ZUNsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG4gICAgICAgICAgICAgICAgICAgICQoJyNtaW5pZnlfZ29vZ2xlX2ZvbnRzJykudmFsKDEpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG5cbiAgICAkKCAnI3JvY2tldC1kaXNtaXNzLXByb21vdGlvbicgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oIGUgKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICAkLnBvc3QoXG4gICAgICAgICAgICBhamF4dXJsLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3JvY2tldF9kaXNtaXNzX3Byb21vJyxcbiAgICAgICAgICAgICAgICBub25jZTogcm9ja2V0X2FqYXhfZGF0YS5ub25jZVxuICAgICAgICAgICAgfSxcblx0XHRcdGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHQkKCcjcm9ja2V0LXByb21vLWJhbm5lcicpLmhpZGUoICdzbG93JyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSApO1xuXG4gICAgJCggJyNyb2NrZXQtZGlzbWlzcy1yZW5ld2FsJyApLm9uKCAnY2xpY2snLCBmdW5jdGlvbiggZSApIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICQucG9zdChcbiAgICAgICAgICAgIGFqYXh1cmwsXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYWN0aW9uOiAncm9ja2V0X2Rpc21pc3NfcmVuZXdhbCcsXG4gICAgICAgICAgICAgICAgbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0JCgnI3JvY2tldC1yZW5ld2FsLWJhbm5lcicpLmhpZGUoICdzbG93JyApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSApO1xuXHQkKCAnI3dwci11cGRhdGUtZXhjbHVzaW9uLWxpc3QnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKCcjd3ByLXVwZGF0ZS1leGNsdXNpb24tbXNnJykuaHRtbCgnJyk7XG5cdFx0JC5hamF4KHtcblx0XHRcdHVybDogcm9ja2V0X2FqYXhfZGF0YS5yZXN0X3VybCxcblx0XHRcdGJlZm9yZVNlbmQ6IGZ1bmN0aW9uICggeGhyICkge1xuXHRcdFx0XHR4aHIuc2V0UmVxdWVzdEhlYWRlciggJ1gtV1AtTm9uY2UnLCByb2NrZXRfYWpheF9kYXRhLnJlc3Rfbm9uY2UgKTtcblx0XHRcdH0sXG5cdFx0XHRtZXRob2Q6IFwiUFVUXCIsXG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbihyZXNwb25zZXMpIHtcblx0XHRcdFx0bGV0IGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyID0gJCgnI3dwci11cGRhdGUtZXhjbHVzaW9uLW1zZycpO1xuXHRcdFx0XHRleGNsdXNpb25fbXNnX2NvbnRhaW5lci5odG1sKCcnKTtcblx0XHRcdFx0aWYgKCB1bmRlZmluZWQgIT09IHJlc3BvbnNlc1snc3VjY2VzcyddICkge1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxkaXYgY2xhc3M9XCJub3RpY2Ugbm90aWNlLWVycm9yXCI+JyArIHJlc3BvbnNlc1snbWVzc2FnZSddICsgJzwvZGl2PicgKTtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblx0XHRcdFx0T2JqZWN0LmtleXMoIHJlc3BvbnNlcyApLmZvckVhY2goKCByZXNwb25zZV9rZXkgKSA9PiB7XG5cdFx0XHRcdFx0ZXhjbHVzaW9uX21zZ19jb250YWluZXIuYXBwZW5kKCAnPHN0cm9uZz4nICsgcmVzcG9uc2Vfa2V5ICsgJzogPC9zdHJvbmc+JyApO1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggcmVzcG9uc2VzW3Jlc3BvbnNlX2tleV1bJ21lc3NhZ2UnXSApO1xuXHRcdFx0XHRcdGV4Y2x1c2lvbl9tc2dfY29udGFpbmVyLmFwcGVuZCggJzxicj4nICk7XG5cdFx0XHRcdH0pO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9ICk7XG5cbiAgICAvKipcbiAgICAgKiBFbmFibGUgbW9iaWxlIGNhY2hlIG9wdGlvbi5cbiAgICAgKi9cbiAgICAkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5vbignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLmFkZENsYXNzKCd3cHItaXNMb2FkaW5nJyk7XG5cbiAgICAgICAgJC5wb3N0KFxuICAgICAgICAgICAgYWpheHVybCxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBhY3Rpb246ICdyb2NrZXRfZW5hYmxlX21vYmlsZV9jYWNoZScsXG4gICAgICAgICAgICAgICAgX2FqYXhfbm9uY2U6IHJvY2tldF9hamF4X2RhdGEubm9uY2VcbiAgICAgICAgICAgIH0sXG5cdFx0XHRmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAoIHJlc3BvbnNlLnN1Y2Nlc3MgKSB7XG5cdFx0XHRcdFx0Ly8gSGlkZSBNb2JpbGUgY2FjaGUgZW5hYmxlIGJ1dHRvbiBvbiBzdWNjZXNzLlxuXHRcdFx0XHRcdCQoJyN3cHJfZW5hYmxlX21vYmlsZV9jYWNoZScpLmhpZGUoKTtcblx0XHRcdFx0XHQkKCcjd3ByX21vYmlsZV9jYWNoZV9kZWZhdWx0JykuaGlkZSgpO1xuXHRcdFx0XHRcdCQoJyN3cHJfbW9iaWxlX2NhY2hlX3Jlc3BvbnNlJykuc2hvdygpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjd3ByX2VuYWJsZV9tb2JpbGVfY2FjaGUnKS5yZW1vdmVDbGFzcygnd3ByLWlzTG9hZGluZycpO1xuXG4gICAgICAgICAgICAgICAgICAgIC8vIFNldCB2YWx1ZXMgb2YgbW9iaWxlIGNhY2hlIGFuZCBzZXBhcmF0ZSBjYWNoZSBmaWxlcyBmb3IgbW9iaWxlcyBvcHRpb24gdG8gMS5cbiAgICAgICAgICAgICAgICAgICAgJCgnI2NhY2hlX21vYmlsZScpLnZhbCgxKTtcbiAgICAgICAgICAgICAgICAgICAgJCgnI2RvX2NhY2hpbmdfbW9iaWxlX2ZpbGVzJykudmFsKDEpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG4gICAgICAgICk7XG4gICAgfSk7XG59KTtcblxuIiwiLy8gQWRkIGdyZWVuc29jayBsaWIgZm9yIGFuaW1hdGlvbnNcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL1R3ZWVuTGl0ZS5taW4uanMnO1xyXG5pbXBvcnQgJy4uL2xpYi9ncmVlbnNvY2svVGltZWxpbmVMaXRlLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9lYXNpbmcvRWFzZVBhY2subWluLmpzJztcclxuaW1wb3J0ICcuLi9saWIvZ3JlZW5zb2NrL3BsdWdpbnMvQ1NTUGx1Z2luLm1pbi5qcyc7XHJcbmltcG9ydCAnLi4vbGliL2dyZWVuc29jay9wbHVnaW5zL1Njcm9sbFRvUGx1Z2luLm1pbi5qcyc7XHJcblxyXG4vLyBBZGQgc2NyaXB0c1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9wYWdlTWFuYWdlci5qcyc7XHJcbmltcG9ydCAnLi4vZ2xvYmFsL21haW4uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9maWVsZHMuanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9iZWFjb24uanMnO1xyXG5pbXBvcnQgJy4uL2dsb2JhbC9hamF4LmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvcm9ja2V0Y2RuLmpzJztcclxuaW1wb3J0ICcuLi9nbG9iYWwvY291bnRkb3duLmpzJzsiLCJ2YXIgJCA9IGpRdWVyeTtcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG4gICAgaWYgKCdCZWFjb24nIGluIHdpbmRvdykge1xuICAgICAgICAvKipcbiAgICAgICAgICogU2hvdyBiZWFjb25zIG9uIGJ1dHRvbiBcImhlbHBcIiBjbGlja1xuICAgICAgICAgKi9cbiAgICAgICAgdmFyICRoZWxwID0gJCgnLndwci1pbmZvQWN0aW9uLS1oZWxwJyk7XG4gICAgICAgICRoZWxwLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpe1xuICAgICAgICAgICAgdmFyIGlkcyA9ICQodGhpcykuZGF0YSgnYmVhY29uLWlkJyk7XG4gICAgICAgICAgICB3cHJDYWxsQmVhY29uKGlkcyk7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGZ1bmN0aW9uIHdwckNhbGxCZWFjb24oYUlEKXtcbiAgICAgICAgICAgIGFJRCA9IGFJRC5zcGxpdCgnLCcpO1xuICAgICAgICAgICAgaWYgKCBhSUQubGVuZ3RoID09PSAwICkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIGlmICggYUlELmxlbmd0aCA+IDEgKSB7XG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5CZWFjb24oXCJzdWdnZXN0XCIsIGFJRCk7XG5cbiAgICAgICAgICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHdpbmRvdy5CZWFjb24oXCJvcGVuXCIpO1xuICAgICAgICAgICAgICAgICAgICB9LCAyMDApO1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5CZWFjb24oXCJhcnRpY2xlXCIsIGFJRC50b1N0cmluZygpKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgfVxuICAgIH1cbn0pO1xuIiwiZnVuY3Rpb24gZ2V0VGltZVJlbWFpbmluZyhlbmR0aW1lKXtcbiAgICBjb25zdCBzdGFydCA9IERhdGUubm93KCk7XG4gICAgY29uc3QgdG90YWwgPSAoZW5kdGltZSAqIDEwMDApIC0gc3RhcnQ7XG4gICAgY29uc3Qgc2Vjb25kcyA9IE1hdGguZmxvb3IoICh0b3RhbC8xMDAwKSAlIDYwICk7XG4gICAgY29uc3QgbWludXRlcyA9IE1hdGguZmxvb3IoICh0b3RhbC8xMDAwLzYwKSAlIDYwICk7XG4gICAgY29uc3QgaG91cnMgPSBNYXRoLmZsb29yKCAodG90YWwvKDEwMDAqNjAqNjApKSAlIDI0ICk7XG4gICAgY29uc3QgZGF5cyA9IE1hdGguZmxvb3IoIHRvdGFsLygxMDAwKjYwKjYwKjI0KSApO1xuXG4gICAgcmV0dXJuIHtcbiAgICAgICAgdG90YWwsXG4gICAgICAgIGRheXMsXG4gICAgICAgIGhvdXJzLFxuICAgICAgICBtaW51dGVzLFxuICAgICAgICBzZWNvbmRzXG4gICAgfTtcbn1cblxuZnVuY3Rpb24gaW5pdGlhbGl6ZUNsb2NrKGlkLCBlbmR0aW1lKSB7XG4gICAgY29uc3QgY2xvY2sgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG5cbiAgICBpZiAoY2xvY2sgPT09IG51bGwpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGNvbnN0IGRheXNTcGFuID0gY2xvY2sucXVlcnlTZWxlY3RvcignLnJvY2tldC1jb3VudGRvd24tZGF5cycpO1xuICAgIGNvbnN0IGhvdXJzU3BhbiA9IGNsb2NrLnF1ZXJ5U2VsZWN0b3IoJy5yb2NrZXQtY291bnRkb3duLWhvdXJzJyk7XG4gICAgY29uc3QgbWludXRlc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1taW51dGVzJyk7XG4gICAgY29uc3Qgc2Vjb25kc1NwYW4gPSBjbG9jay5xdWVyeVNlbGVjdG9yKCcucm9ja2V0LWNvdW50ZG93bi1zZWNvbmRzJyk7XG5cbiAgICBmdW5jdGlvbiB1cGRhdGVDbG9jaygpIHtcbiAgICAgICAgY29uc3QgdCA9IGdldFRpbWVSZW1haW5pbmcoZW5kdGltZSk7XG5cbiAgICAgICAgaWYgKHQudG90YWwgPCAwKSB7XG4gICAgICAgICAgICBjbGVhckludGVydmFsKHRpbWVpbnRlcnZhbCk7XG5cbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGRheXNTcGFuLmlubmVySFRNTCA9IHQuZGF5cztcbiAgICAgICAgaG91cnNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0LmhvdXJzKS5zbGljZSgtMik7XG4gICAgICAgIG1pbnV0ZXNTcGFuLmlubmVySFRNTCA9ICgnMCcgKyB0Lm1pbnV0ZXMpLnNsaWNlKC0yKTtcbiAgICAgICAgc2Vjb25kc1NwYW4uaW5uZXJIVE1MID0gKCcwJyArIHQuc2Vjb25kcykuc2xpY2UoLTIpO1xuICAgIH1cblxuICAgIHVwZGF0ZUNsb2NrKCk7XG4gICAgY29uc3QgdGltZWludGVydmFsID0gc2V0SW50ZXJ2YWwodXBkYXRlQ2xvY2ssIDEwMDApO1xufVxuXG5mdW5jdGlvbiBydWNzc1RpbWVyKGlkLCBlbmR0aW1lKSB7XG5cdGNvbnN0IHRpbWVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpO1xuXHRjb25zdCBub3RpY2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1ydWNzcy1wcm9jZXNzaW5nJyk7XG5cdGNvbnN0IHN1Y2Nlc3MgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncm9ja2V0LW5vdGljZS1ydWNzcy1zdWNjZXNzJyk7XG5cblx0aWYgKHRpbWVyID09PSBudWxsKSB7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0ZnVuY3Rpb24gdXBkYXRlVGltZXIoKSB7XG5cdFx0Y29uc3Qgc3RhcnQgPSBEYXRlLm5vdygpO1xuXHRcdGNvbnN0IHJlbWFpbmluZyA9IE1hdGguZmxvb3IoICggKGVuZHRpbWUgKiAxMDAwKSAtIHN0YXJ0ICkgLyAxMDAwICk7XG5cblx0XHRpZiAocmVtYWluaW5nIDw9IDApIHtcblx0XHRcdGNsZWFySW50ZXJ2YWwodGltZXJJbnRlcnZhbCk7XG5cblx0XHRcdGlmIChub3RpY2UgIT09IG51bGwpIHtcblx0XHRcdFx0bm90aWNlLmNsYXNzTGlzdC5hZGQoJ2hpZGRlbicpO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoc3VjY2VzcyAhPT0gbnVsbCkge1xuXHRcdFx0XHRzdWNjZXNzLmNsYXNzTGlzdC5yZW1vdmUoJ2hpZGRlbicpO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoIHJvY2tldF9hamF4X2RhdGEuY3Jvbl9kaXNhYmxlZCApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBkYXRhID0gbmV3IEZvcm1EYXRhKCk7XG5cblx0XHRcdGRhdGEuYXBwZW5kKCAnYWN0aW9uJywgJ3JvY2tldF9zcGF3bl9jcm9uJyApO1xuXHRcdFx0ZGF0YS5hcHBlbmQoICdub25jZScsIHJvY2tldF9hamF4X2RhdGEubm9uY2UgKTtcblxuXHRcdFx0ZmV0Y2goIGFqYXh1cmwsIHtcblx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdGNyZWRlbnRpYWxzOiAnc2FtZS1vcmlnaW4nLFxuXHRcdFx0XHRib2R5OiBkYXRhXG5cdFx0XHR9ICk7XG5cblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHR0aW1lci5pbm5lckhUTUwgPSByZW1haW5pbmc7XG5cdH1cblxuXHR1cGRhdGVUaW1lcigpO1xuXHRjb25zdCB0aW1lckludGVydmFsID0gc2V0SW50ZXJ2YWwoIHVwZGF0ZVRpbWVyLCAxMDAwKTtcbn1cblxuaWYgKCFEYXRlLm5vdykge1xuICAgIERhdGUubm93ID0gZnVuY3Rpb24gbm93KCkge1xuICAgICAgcmV0dXJuIG5ldyBEYXRlKCkuZ2V0VGltZSgpO1xuICAgIH07XG59XG5cbmlmICh0eXBlb2Ygcm9ja2V0X2FqYXhfZGF0YS5wcm9tb19lbmQgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgaW5pdGlhbGl6ZUNsb2NrKCdyb2NrZXQtcHJvbW8tY291bnRkb3duJywgcm9ja2V0X2FqYXhfZGF0YS5wcm9tb19lbmQpO1xufVxuXG5pZiAodHlwZW9mIHJvY2tldF9hamF4X2RhdGEubGljZW5zZV9leHBpcmF0aW9uICE9PSAndW5kZWZpbmVkJykge1xuICAgIGluaXRpYWxpemVDbG9jaygncm9ja2V0LXJlbmV3LWNvdW50ZG93bicsIHJvY2tldF9hamF4X2RhdGEubGljZW5zZV9leHBpcmF0aW9uKTtcbn1cblxuaWYgKHR5cGVvZiByb2NrZXRfYWpheF9kYXRhLm5vdGljZV9lbmRfdGltZSAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBydWNzc1RpbWVyKCdyb2NrZXQtcnVjc3MtdGltZXInLCByb2NrZXRfYWpheF9kYXRhLm5vdGljZV9lbmRfdGltZSk7XG59IiwidmFyICQgPSBqUXVlcnk7XG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xuXG5cbiAgICAvKioqXG4gICAgKiBDaGVjayBwYXJlbnQgLyBzaG93IGNoaWxkcmVuXG4gICAgKioqL1xuXG5cdGZ1bmN0aW9uIHdwclNob3dDaGlsZHJlbihhRWxlbSl7XG5cdFx0dmFyIHBhcmVudElkLCAkY2hpbGRyZW47XG5cblx0XHRhRWxlbSAgICAgPSAkKCBhRWxlbSApO1xuXHRcdHBhcmVudElkICA9IGFFbGVtLmF0dHIoJ2lkJyk7XG5cdFx0JGNoaWxkcmVuID0gJCgnW2RhdGEtcGFyZW50PVwiJyArIHBhcmVudElkICsgJ1wiXScpO1xuXG5cdFx0Ly8gVGVzdCBjaGVjayBmb3Igc3dpdGNoXG5cdFx0aWYoYUVsZW0uaXMoJzpjaGVja2VkJykpe1xuXHRcdFx0JGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRpZiAoICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5pcygnOmNoZWNrZWQnKSkge1xuXHRcdFx0XHRcdHZhciBpZCA9ICQodGhpcykuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5hdHRyKCdpZCcpO1xuXG5cdFx0XHRcdFx0JCgnW2RhdGEtcGFyZW50PVwiJyArIGlkICsgJ1wiXScpLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXHRcdH1cblx0XHRlbHNle1xuXHRcdFx0JGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG5cblx0XHRcdCRjaGlsZHJlbi5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR2YXIgaWQgPSAkKHRoaXMpLmZpbmQoJ2lucHV0W3R5cGU9Y2hlY2tib3hdJykuYXR0cignaWQnKTtcblxuXHRcdFx0XHQkKCdbZGF0YS1wYXJlbnQ9XCInICsgaWQgKyAnXCJdJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdH0pO1xuXHRcdH1cblx0fVxuXG4gICAgLyoqXG4gICAgICogVGVsbCBpZiB0aGUgZ2l2ZW4gY2hpbGQgZmllbGQgaGFzIGFuIGFjdGl2ZSBwYXJlbnQgZmllbGQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0gIG9iamVjdCAkZmllbGQgQSBqUXVlcnkgb2JqZWN0IG9mIGEgXCIud3ByLWZpZWxkXCIgZmllbGQuXG4gICAgICogQHJldHVybiBib29sfG51bGxcbiAgICAgKi9cbiAgICBmdW5jdGlvbiB3cHJJc1BhcmVudEFjdGl2ZSggJGZpZWxkICkge1xuICAgICAgICB2YXIgJHBhcmVudDtcblxuICAgICAgICBpZiAoICEgJGZpZWxkLmxlbmd0aCApIHtcbiAgICAgICAgICAgIC8vIMKvXFxfKOODhClfL8KvXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgICRwYXJlbnQgPSAkZmllbGQuZGF0YSggJ3BhcmVudCcgKTtcblxuICAgICAgICBpZiAoIHR5cGVvZiAkcGFyZW50ICE9PSAnc3RyaW5nJyApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICRwYXJlbnQucmVwbGFjZSggL15cXHMrfFxccyskL2csICcnICk7XG5cbiAgICAgICAgaWYgKCAnJyA9PT0gJHBhcmVudCApIHtcbiAgICAgICAgICAgIC8vIFRoaXMgZmllbGQgaGFzIG5vIHBhcmVudCBmaWVsZDogdGhlbiB3ZSBjYW4gZGlzcGxheSBpdC5cbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9XG5cbiAgICAgICAgJHBhcmVudCA9ICQoICcjJyArICRwYXJlbnQgKTtcblxuICAgICAgICBpZiAoICEgJHBhcmVudC5sZW5ndGggKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIG1pc3Npbmc6IGxldCdzIGNvbnNpZGVyIGl0J3Mgbm90IGFjdGl2ZSB0aGVuLlxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCAhICRwYXJlbnQuaXMoICc6Y2hlY2tlZCcgKSAmJiAkcGFyZW50LmlzKCdpbnB1dCcpKSB7XG4gICAgICAgICAgICAvLyBUaGlzIGZpZWxkJ3MgcGFyZW50IGlzIGNoZWNrYm94IGFuZCBub3QgY2hlY2tlZDogZG9uJ3QgZGlzcGxheSB0aGUgZmllbGQgdGhlbi5cbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfVxuXG5cdFx0aWYgKCAhJHBhcmVudC5oYXNDbGFzcygncmFkaW8tYWN0aXZlJykgJiYgJHBhcmVudC5pcygnYnV0dG9uJykpIHtcblx0XHRcdC8vIFRoaXMgZmllbGQncyBwYXJlbnQgYnV0dG9uIGFuZCBpcyBub3QgYWN0aXZlOiBkb24ndCBkaXNwbGF5IHRoZSBmaWVsZCB0aGVuLlxuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cbiAgICAgICAgLy8gR28gcmVjdXJzaXZlIHRvIHRoZSBsYXN0IHBhcmVudC5cbiAgICAgICAgcmV0dXJuIHdwcklzUGFyZW50QWN0aXZlKCAkcGFyZW50LmNsb3Nlc3QoICcud3ByLWZpZWxkJyApICk7XG4gICAgfVxuXG5cdC8qKlxuXHQgKiBNYXNrcyBzZW5zaXRpdmUgaW5mb3JtYXRpb24gaW4gYW4gaW5wdXQgZmllbGQgYnkgcmVwbGFjaW5nIGFsbCBidXQgdGhlIGxhc3QgNCBjaGFyYWN0ZXJzIHdpdGggYXN0ZXJpc2tzLlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gaWRfc2VsZWN0b3IgLSBUaGUgSUQgb2YgdGhlIGlucHV0IGZpZWxkIHRvIGJlIG1hc2tlZC5cblx0ICogQHJldHVybnMge3ZvaWR9IC0gTW9kaWZpZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIGluLXBsYWNlLlxuXHQgKlxuXHQgKiBAZXhhbXBsZVxuXHQgKiAvLyBIVE1MOiA8aW5wdXQgdHlwZT1cInRleHRcIiBpZD1cImNyZWRpdENhcmRJbnB1dFwiIHZhbHVlPVwiMTIzNDU2Nzg5MDEyMzQ1NlwiPlxuXHQgKiBtYXNrRmllbGQoJ2NyZWRpdENhcmRJbnB1dCcpO1xuXHQgKiAvLyBSZXN1bHQ6IFVwZGF0ZXMgdGhlIGlucHV0IGZpZWxkIHZhbHVlIHRvICcqKioqKioqKioqKiozNDU2Jy5cblx0ICovXG5cdGZ1bmN0aW9uIG1hc2tGaWVsZChwcm94eV9zZWxlY3RvciwgY29uY3JldGVfc2VsZWN0b3IpIHtcblx0XHR2YXIgY29uY3JldGUgPSB7XG5cdFx0XHQndmFsJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCksXG5cdFx0XHQnbGVuZ3RoJzogY29uY3JldGVfc2VsZWN0b3IudmFsKCkubGVuZ3RoXG5cdFx0fVxuXG5cdFx0aWYgKGNvbmNyZXRlLmxlbmd0aCA+IDQpIHtcblxuXHRcdFx0dmFyIGhpZGRlblBhcnQgPSAnXFx1MjAyMicucmVwZWF0KE1hdGgubWF4KDAsIGNvbmNyZXRlLmxlbmd0aCAtIDQpKTtcblx0XHRcdHZhciB2aXNpYmxlUGFydCA9IGNvbmNyZXRlLnZhbC5zbGljZSgtNCk7XG5cblx0XHRcdC8vIENvbWJpbmUgdGhlIGhpZGRlbiBhbmQgdmlzaWJsZSBwYXJ0c1xuXHRcdFx0dmFyIG1hc2tlZFZhbHVlID0gaGlkZGVuUGFydCArIHZpc2libGVQYXJ0O1xuXG5cdFx0XHRwcm94eV9zZWxlY3Rvci52YWwobWFza2VkVmFsdWUpO1xuXHRcdH1cblx0XHQvLyBFbnN1cmUgZXZlbnRzIGFyZSBub3QgYWRkZWQgbW9yZSB0aGFuIG9uY2Vcblx0XHRpZiAoIXByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJykpIHtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdpbnB1dCcsIGhhbmRsZUlucHV0KTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLm9uKCdmb2N1cycsIGhhbmRsZUZvY3VzKTtcblx0XHRcdHByb3h5X3NlbGVjdG9yLmRhdGEoJ2V2ZW50c0F0dGFjaGVkJywgdHJ1ZSk7XG5cdFx0fVxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlIHRoZSBpbnB1dCBldmVudFxuXHRcdCAqL1xuXHRcdGZ1bmN0aW9uIGhhbmRsZUlucHV0KCkge1xuXHRcdFx0dmFyIHByb3h5VmFsdWUgPSBwcm94eV9zZWxlY3Rvci52YWwoKTtcblx0XHRcdGlmIChwcm94eVZhbHVlLmluZGV4T2YoJ1xcdTIwMjInKSA9PT0gLTEpIHtcblx0XHRcdFx0Y29uY3JldGVfc2VsZWN0b3IudmFsKHByb3h5VmFsdWUpO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8qKlxuXHRcdCAqIEhhbmRsZSB0aGUgZm9jdXMgZXZlbnRcblx0XHQgKi9cblx0XHRmdW5jdGlvbiBoYW5kbGVGb2N1cygpIHtcblx0XHRcdHZhciBjb25jcmV0ZV92YWx1ZSA9IGNvbmNyZXRlX3NlbGVjdG9yLnZhbCgpO1xuXHRcdFx0cHJveHlfc2VsZWN0b3IudmFsKGNvbmNyZXRlX3ZhbHVlKTtcblx0XHR9XG5cblx0fVxuXG5cdFx0Ly8gVXBkYXRlIHRoZSBjb25jcmV0ZSBmaWVsZCB3aGVuIHRoZSBwcm94eSBpcyB1cGRhdGVkLlxuXG5cblx0bWFza0ZpZWxkKCQoJyNjbG91ZGZsYXJlX2FwaV9rZXlfbWFzaycpLCAkKCcjY2xvdWRmbGFyZV9hcGlfa2V5JykpO1xuXHRtYXNrRmllbGQoJCgnI2Nsb3VkZmxhcmVfem9uZV9pZF9tYXNrJyksICQoJyNjbG91ZGZsYXJlX3pvbmVfaWQnKSk7XG5cblx0Ly8gRGlzcGxheS9IaWRlIGNoaWxkZXJuIGZpZWxkcyBvbiBjaGVja2JveCBjaGFuZ2UuXG4gICAgJCggJy53cHItaXNQYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nICkub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAvLyBPbiBwYWdlIGxvYWQsIGRpc3BsYXkgdGhlIGFjdGl2ZSBmaWVsZHMuXG4gICAgJCggJy53cHItZmllbGQtLWNoaWxkcmVuJyApLmVhY2goIGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgJGZpZWxkID0gJCggdGhpcyApO1xuXG4gICAgICAgIGlmICggd3BySXNQYXJlbnRBY3RpdmUoICRmaWVsZCApICkge1xuICAgICAgICAgICAgJGZpZWxkLmFkZENsYXNzKCAnd3ByLWlzT3BlbicgKTtcbiAgICAgICAgfVxuICAgIH0gKTtcblxuXG5cblxuICAgIC8qKipcbiAgICAqIFdhcm5pbmcgZmllbGRzXG4gICAgKioqL1xuXG4gICAgdmFyICR3YXJuaW5nUGFyZW50ID0gJCgnLndwci1maWVsZC0tcGFyZW50Jyk7XG4gICAgdmFyICR3YXJuaW5nUGFyZW50SW5wdXQgPSAkKCcud3ByLWZpZWxkLS1wYXJlbnQgaW5wdXRbdHlwZT1jaGVja2JveF0nKTtcblxuICAgIC8vIElmIGFscmVhZHkgY2hlY2tlZFxuICAgICR3YXJuaW5nUGFyZW50SW5wdXQuZWFjaChmdW5jdGlvbigpe1xuICAgICAgICB3cHJTaG93Q2hpbGRyZW4oJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAkd2FybmluZ1BhcmVudC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgIHdwclNob3dXYXJuaW5nKCQodGhpcykpO1xuICAgIH0pO1xuXG4gICAgZnVuY3Rpb24gd3ByU2hvd1dhcm5pbmcoYUVsZW0pe1xuICAgICAgICB2YXIgJHdhcm5pbmdGaWVsZCA9IGFFbGVtLm5leHQoJy53cHItZmllbGRXYXJuaW5nJyksXG4gICAgICAgICAgICAkdGhpc0NoZWNrYm94ID0gYUVsZW0uZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKSxcbiAgICAgICAgICAgICRuZXh0V2FybmluZyA9IGFFbGVtLnBhcmVudCgpLm5leHQoJy53cHItd2FybmluZ0NvbnRhaW5lcicpLFxuICAgICAgICAgICAgJG5leHRGaWVsZHMgPSAkbmV4dFdhcm5pbmcuZmluZCgnLndwci1maWVsZCcpLFxuICAgICAgICAgICAgcGFyZW50SWQgPSBhRWxlbS5maW5kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmF0dHIoJ2lkJyksXG4gICAgICAgICAgICAkY2hpbGRyZW4gPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgcGFyZW50SWQgKyAnXCJdJylcbiAgICAgICAgO1xuXG4gICAgICAgIC8vIENoZWNrIHdhcm5pbmcgcGFyZW50XG4gICAgICAgIGlmKCR0aGlzQ2hlY2tib3guaXMoJzpjaGVja2VkJykpe1xuICAgICAgICAgICAgJHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuICAgICAgICAgICAgJHRoaXNDaGVja2JveC5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgYUVsZW0udHJpZ2dlcignY2hhbmdlJyk7XG5cblxuICAgICAgICAgICAgdmFyICR3YXJuaW5nQnV0dG9uID0gJHdhcm5pbmdGaWVsZC5maW5kKCcud3ByLWJ1dHRvbicpO1xuXG4gICAgICAgICAgICAvLyBWYWxpZGF0ZSB0aGUgd2FybmluZ1xuICAgICAgICAgICAgJHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICAgICAkdGhpc0NoZWNrYm94LnByb3AoJ2NoZWNrZWQnLCB0cnVlKTtcbiAgICAgICAgICAgICAgICAkd2FybmluZ0ZpZWxkLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgICAgICAgICAgJGNoaWxkcmVuLmFkZENsYXNzKCd3cHItaXNPcGVuJyk7XG5cbiAgICAgICAgICAgICAgICAvLyBJZiBuZXh0IGVsZW0gPSBkaXNhYmxlZFxuICAgICAgICAgICAgICAgIGlmKCRuZXh0V2FybmluZy5sZW5ndGggPiAwKXtcbiAgICAgICAgICAgICAgICAgICAgJG5leHRGaWVsZHMucmVtb3ZlQ2xhc3MoJ3dwci1pc0Rpc2FibGVkJyk7XG4gICAgICAgICAgICAgICAgICAgICRuZXh0RmllbGRzLmZpbmQoJ2lucHV0JykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgICRuZXh0RmllbGRzLmFkZENsYXNzKCd3cHItaXNEaXNhYmxlZCcpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXQnKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpO1xuICAgICAgICAgICAgJG5leHRGaWVsZHMuZmluZCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgJGNoaWxkcmVuLnJlbW92ZUNsYXNzKCd3cHItaXNPcGVuJyk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDTkFNRVMgYWRkL3JlbW92ZSBsaW5lc1xuICAgICAqL1xuICAgICQoZG9jdW1lbnQpLm9uKCdjbGljaycsICcud3ByLW11bHRpcGxlLWNsb3NlJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHQkKHRoaXMpLnBhcmVudCgpLnNsaWRlVXAoICdzbG93JyAsIGZ1bmN0aW9uKCl7JCh0aGlzKS5yZW1vdmUoKTsgfSApO1xuXHR9ICk7XG5cblx0JCgnLndwci1idXR0b24tLWFkZE11bHRpJykub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgJCgkKCcjd3ByLWNuYW1lLW1vZGVsJykuaHRtbCgpKS5hcHBlbmRUbygnI3dwci1jbmFtZXMtbGlzdCcpO1xuICAgIH0pO1xuXG5cdC8qKipcblx0ICogV3ByIFJhZGlvIGJ1dHRvblxuXHQgKioqL1xuXHR2YXIgZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cblx0JChkb2N1bWVudCkub24oJ2NsaWNrJywgJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRpZigkKHRoaXMpLmhhc0NsYXNzKCdyYWRpby1hY3RpdmUnKSl7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHZhciAkcGFyZW50ID0gJCh0aGlzKS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItcmFkaW8tYnV0dG9ucy1jb250YWluZXIgYnV0dG9uJykucmVtb3ZlQ2xhc3MoJ3JhZGlvLWFjdGl2ZScpO1xuXHRcdCRwYXJlbnQuZmluZCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkcGFyZW50LmZpbmQoJy53cHItZmllbGRXYXJuaW5nJykucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHQkKHRoaXMpLmFkZENsYXNzKCdyYWRpby1hY3RpdmUnKTtcblx0XHR3cHJTaG93UmFkaW9XYXJuaW5nKCQodGhpcykpO1xuXG5cdH0gKTtcblxuXG5cdGZ1bmN0aW9uIHdwclNob3dSYWRpb1dhcm5pbmcoJGVsbSl7XG5cdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gZmFsc2U7XG5cdFx0JGVsbS50cmlnZ2VyKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgWyAkZWxtIF0gKTtcblx0XHRpZiAoISRlbG0uaGFzQ2xhc3MoJ2hhcy13YXJuaW5nJykgfHwgZGlzYWJsZV9yYWRpb193YXJuaW5nKSB7XG5cdFx0XHR3cHJTaG93UmFkaW9CdXR0b25DaGlsZHJlbigkZWxtKTtcblx0XHRcdCRlbG0udHJpZ2dlciggXCJyYWRpb19idXR0b25fc2VsZWN0ZWRcIiwgWyAkZWxtIF0gKTtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyICR3YXJuaW5nRmllbGQgPSAkKCdbZGF0YS1wYXJlbnQ9XCInICsgJGVsbS5hdHRyKCdpZCcpICsgJ1wiXS53cHItZmllbGRXYXJuaW5nJyk7XG5cdFx0JHdhcm5pbmdGaWVsZC5hZGRDbGFzcygnd3ByLWlzT3BlbicpO1xuXHRcdHZhciAkd2FybmluZ0J1dHRvbiA9ICR3YXJuaW5nRmllbGQuZmluZCgnLndwci1idXR0b24nKTtcblxuXHRcdC8vIFZhbGlkYXRlIHRoZSB3YXJuaW5nXG5cdFx0JHdhcm5pbmdCdXR0b24ub24oJ2NsaWNrJywgZnVuY3Rpb24oKXtcblx0XHRcdCR3YXJuaW5nRmllbGQucmVtb3ZlQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0XHRcdHdwclNob3dSYWRpb0J1dHRvbkNoaWxkcmVuKCRlbG0pO1xuXHRcdFx0JGVsbS50cmlnZ2VyKCBcInJhZGlvX2J1dHRvbl9zZWxlY3RlZFwiLCBbICRlbG0gXSApO1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH0pO1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByU2hvd1JhZGlvQnV0dG9uQ2hpbGRyZW4oJGVsbSkge1xuXHRcdHZhciAkcGFyZW50ID0gJGVsbS5wYXJlbnRzKCcud3ByLXJhZGlvLWJ1dHRvbnMnKTtcblx0XHR2YXIgJGNoaWxkcmVuID0gJCgnLndwci1leHRyYS1maWVsZHMtY29udGFpbmVyW2RhdGEtcGFyZW50PVwiJyArICRlbG0uYXR0cignaWQnKSArICdcIl0nKTtcblx0XHQkY2hpbGRyZW4uYWRkQ2xhc3MoJ3dwci1pc09wZW4nKTtcblx0fVxuXG5cdC8qKipcblx0ICogV3ByIE9wdGltaXplIENzcyBEZWxpdmVyeSBGaWVsZFxuXHQgKioqL1xuXHR2YXIgcnVjc3NBY3RpdmUgPSBwYXJzZUludCgkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoKSk7XG5cblx0JCggXCIjb3B0aW1pemVfY3NzX2RlbGl2ZXJ5X21ldGhvZCAud3ByLXJhZGlvLWJ1dHRvbnMtY29udGFpbmVyIGJ1dHRvblwiIClcblx0XHQub24oIFwicmFkaW9fYnV0dG9uX3NlbGVjdGVkXCIsIGZ1bmN0aW9uKCBldmVudCwgJGVsbSApIHtcblx0XHRcdHRvZ2dsZUFjdGl2ZU9wdGltaXplQ3NzRGVsaXZlcnlNZXRob2QoJGVsbSk7XG5cdFx0fSk7XG5cblx0JChcIiNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlcIikub24oXCJjaGFuZ2VcIiwgZnVuY3Rpb24oKXtcblx0XHRpZiggJCh0aGlzKS5pcyhcIjpub3QoOmNoZWNrZWQpXCIpICl7XG5cdFx0XHRkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpO1xuXHRcdH1lbHNle1xuXHRcdFx0dmFyIGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkID0gJyMnKyQoJyNvcHRpbWl6ZV9jc3NfZGVsaXZlcnlfbWV0aG9kJykuZGF0YSggJ2RlZmF1bHQnICk7XG5cdFx0XHQkKGRlZmF1bHRfcmFkaW9fYnV0dG9uX2lkKS50cmlnZ2VyKCdjbGljaycpO1xuXHRcdH1cblx0fSk7XG5cblx0ZnVuY3Rpb24gdG9nZ2xlQWN0aXZlT3B0aW1pemVDc3NEZWxpdmVyeU1ldGhvZCgkZWxtKSB7XG5cdFx0dmFyIG9wdGltaXplX21ldGhvZCA9ICRlbG0uZGF0YSgndmFsdWUnKTtcblx0XHRpZigncmVtb3ZlX3VudXNlZF9jc3MnID09PSBvcHRpbWl6ZV9tZXRob2Qpe1xuXHRcdFx0JCgnI3JlbW92ZV91bnVzZWRfY3NzJykudmFsKDEpO1xuXHRcdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0XHR9ZWxzZXtcblx0XHRcdCQoJyNyZW1vdmVfdW51c2VkX2NzcycpLnZhbCgwKTtcblx0XHRcdCQoJyNhc3luY19jc3MnKS52YWwoMSk7XG5cdFx0fVxuXG5cdH1cblxuXHRmdW5jdGlvbiBkaXNhYmxlT3B0aW1pemVDc3NEZWxpdmVyeSgpIHtcblx0XHQkKCcjcmVtb3ZlX3VudXNlZF9jc3MnKS52YWwoMCk7XG5cdFx0JCgnI2FzeW5jX2NzcycpLnZhbCgwKTtcblx0fVxuXG5cdCQoIFwiI29wdGltaXplX2Nzc19kZWxpdmVyeV9tZXRob2QgLndwci1yYWRpby1idXR0b25zLWNvbnRhaW5lciBidXR0b25cIiApXG5cdFx0Lm9uKCBcImJlZm9yZV9zaG93X3JhZGlvX3dhcm5pbmdcIiwgZnVuY3Rpb24oIGV2ZW50LCAkZWxtICkge1xuXHRcdFx0ZGlzYWJsZV9yYWRpb193YXJuaW5nID0gKCdyZW1vdmVfdW51c2VkX2NzcycgPT09ICRlbG0uZGF0YSgndmFsdWUnKSAmJiAxID09PSBydWNzc0FjdGl2ZSlcblx0XHR9KTtcblxuXHQkKCBcIi53cHItbXVsdGlwbGUtc2VsZWN0IC53cHItbGlzdC1oZWFkZXItYXJyb3dcIiApLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0JChlLnRhcmdldCkuY2xvc2VzdCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1saXN0JykudG9nZ2xlQ2xhc3MoJ29wZW4nKTtcblx0fSk7XG5cblx0JCgnLndwci1tdWx0aXBsZS1zZWxlY3QgLndwci1jaGVja2JveCcpLmNsaWNrKGZ1bmN0aW9uIChlKSB7XG5cdFx0Y29uc3QgY2hlY2tib3ggPSAkKHRoaXMpLmZpbmQoJ2lucHV0Jyk7XG5cdFx0Y29uc3QgaXNfY2hlY2tlZCA9IGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnKSAhPT0gdW5kZWZpbmVkO1xuXHRcdGNoZWNrYm94LmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdGNvbnN0IHN1Yl9jaGVja2JveGVzID0gJChjaGVja2JveCkuY2xvc2VzdCgnLndwci1saXN0JykuZmluZCgnLndwci1saXN0LWJvZHkgaW5wdXRbdHlwZT1cImNoZWNrYm94XCJdJyk7XG5cdFx0aWYoY2hlY2tib3guaGFzQ2xhc3MoJ3dwci1tYWluLWNoZWNrYm94JykpIHtcblx0XHRcdCQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBpc19jaGVja2VkID8gbnVsbCA6ICdjaGVja2VkJyApO1xuXHRcdFx0fSk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGNvbnN0IG1haW5fY2hlY2tib3ggPSAkKGNoZWNrYm94KS5jbG9zZXN0KCcud3ByLWxpc3QnKS5maW5kKCcud3ByLW1haW4tY2hlY2tib3gnKTtcblxuXHRcdGNvbnN0IHN1Yl9jaGVja2VkID0gICQubWFwKHN1Yl9jaGVja2JveGVzLCBjaGVja2JveCA9PiB7XG5cdFx0XHRpZigkKGNoZWNrYm94KS5hdHRyKCdjaGVja2VkJykgPT09IHVuZGVmaW5lZCkge1xuXHRcdFx0XHRyZXR1cm4gO1xuXHRcdFx0fVxuXHRcdFx0cmV0dXJuIGNoZWNrYm94O1xuXHRcdH0pO1xuXHRcdG1haW5fY2hlY2tib3guYXR0cignY2hlY2tlZCcsIHN1Yl9jaGVja2VkLmxlbmd0aCA9PT0gc3ViX2NoZWNrYm94ZXMubGVuZ3RoID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHR9KTtcblxuXHRpZiAoICQoICcud3ByLW1haW4tY2hlY2tib3gnICkubGVuZ3RoID4gMCApIHtcblx0XHQkKCcud3ByLW1haW4tY2hlY2tib3gnKS5lYWNoKChjaGVja2JveF9rZXksIGNoZWNrYm94KSA9PiB7XG5cdFx0XHRsZXQgcGFyZW50X2xpc3QgPSAkKGNoZWNrYm94KS5wYXJlbnRzKCcud3ByLWxpc3QnKTtcblx0XHRcdGxldCBub3RfY2hlY2tlZCA9IHBhcmVudF9saXN0LmZpbmQoICcud3ByLWxpc3QtYm9keSBpbnB1dFt0eXBlPWNoZWNrYm94XTpub3QoOmNoZWNrZWQpJyApLmxlbmd0aDtcblx0XHRcdCQoY2hlY2tib3gpLmF0dHIoJ2NoZWNrZWQnLCBub3RfY2hlY2tlZCA8PSAwID8gJ2NoZWNrZWQnIDogbnVsbCApO1xuXHRcdH0pO1xuXHR9XG59KTtcbiIsInZhciAkID0galF1ZXJ5O1xuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKXtcblxuXG5cdC8qKipcblx0KiBEYXNoYm9hcmQgbm90aWNlXG5cdCoqKi9cblxuXHR2YXIgJG5vdGljZSA9ICQoJy53cHItbm90aWNlJyk7XG5cdHZhciAkbm90aWNlQ2xvc2UgPSAkKCcjd3ByLWNvbmdyYXR1bGF0aW9ucy1ub3RpY2UnKTtcblxuXHQkbm90aWNlQ2xvc2Uub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VEYXNoYm9hcmROb3RpY2UoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckNsb3NlRGFzaGJvYXJkTm90aWNlKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKVxuXHRcdCAgLnRvKCRub3RpY2UsIDEsIHthdXRvQWxwaGE6MCwgeDo0MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAudG8oJG5vdGljZSwgMC42LCB7aGVpZ2h0OiAwLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS40Jylcblx0XHQgIC5zZXQoJG5vdGljZSwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdDtcblx0fVxuXG5cdC8qKlxuXHQgKiBSb2NrZXQgQW5hbHl0aWNzIG5vdGljZSBpbmZvIGNvbGxlY3Rcblx0ICovXG5cdCQoICcucm9ja2V0LWFuYWx5dGljcy1kYXRhLWNvbnRhaW5lcicgKS5oaWRlKCk7XG5cdCQoICcucm9ja2V0LXByZXZpZXctYW5hbHl0aWNzLWRhdGEnICkub24oICdjbGljaycsIGZ1bmN0aW9uKCBlICkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdCQodGhpcykucGFyZW50KCkubmV4dCggJy5yb2NrZXQtYW5hbHl0aWNzLWRhdGEtY29udGFpbmVyJyApLnRvZ2dsZSgpO1xuXHR9ICk7XG5cblx0LyoqKlxuXHQqIEhpZGUgLyBzaG93IFJvY2tldCBhZGRvbiB0YWJzLlxuXHQqKiovXG5cblx0JCggJy53cHItdG9nZ2xlLWJ1dHRvbicgKS5lYWNoKCBmdW5jdGlvbigpIHtcblx0XHR2YXIgJGJ1dHRvbiAgID0gJCggdGhpcyApO1xuXHRcdHZhciAkY2hlY2tib3ggPSAkYnV0dG9uLmNsb3Nlc3QoICcud3ByLWZpZWxkc0NvbnRhaW5lci1maWVsZHNldCcgKS5maW5kKCAnLndwci1yYWRpbyA6Y2hlY2tib3gnICk7XG5cdFx0dmFyICRtZW51SXRlbSA9ICQoICdbaHJlZj1cIicgKyAkYnV0dG9uLmF0dHIoICdocmVmJyApICsgJ1wiXS53cHItbWVudUl0ZW0nICk7XG5cblx0XHQkY2hlY2tib3gub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuXHRcdFx0aWYgKCAkY2hlY2tib3guaXMoICc6Y2hlY2tlZCcgKSApIHtcblx0XHRcdFx0JG1lbnVJdGVtLmNzcyggJ2Rpc3BsYXknLCAnYmxvY2snICk7XG5cdFx0XHRcdCRidXR0b24uY3NzKCAnZGlzcGxheScsICdpbmxpbmUtYmxvY2snICk7XG5cdFx0XHR9IGVsc2V7XG5cdFx0XHRcdCRtZW51SXRlbS5jc3MoICdkaXNwbGF5JywgJ25vbmUnICk7XG5cdFx0XHRcdCRidXR0b24uY3NzKCAnZGlzcGxheScsICdub25lJyApO1xuXHRcdFx0fVxuXHRcdH0gKS50cmlnZ2VyKCAnY2hhbmdlJyApO1xuXHR9ICk7XG5cblxuXG5cblxuXHQvKioqXG5cdCogU2hvdyBwb3BpbiBhbmFseXRpY3Ncblx0KioqL1xuXG5cdHZhciAkd3ByQW5hbHl0aWNzUG9waW4gPSAkKCcud3ByLVBvcGluLUFuYWx5dGljcycpLFxuXHRcdCR3cHJQb3Bpbk92ZXJsYXkgPSAkKCcud3ByLVBvcGluLW92ZXJsYXknKSxcblx0XHQkd3ByQW5hbHl0aWNzQ2xvc2VQb3BpbiA9ICQoJy53cHItUG9waW4tQW5hbHl0aWNzLWNsb3NlJyksXG5cdFx0JHdwckFuYWx5dGljc1BvcGluQnV0dG9uID0gJCgnLndwci1Qb3Bpbi1BbmFseXRpY3MgLndwci1idXR0b24nKSxcblx0XHQkd3ByQW5hbHl0aWNzT3BlblBvcGluID0gJCgnLndwci1qcy1wb3BpbicpXG5cdDtcblxuXHQkd3ByQW5hbHl0aWNzT3BlblBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByT3BlbkFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwckFuYWx5dGljc0Nsb3NlUG9waW4ub24oJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHR3cHJDbG9zZUFuYWx5dGljcygpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwckFuYWx5dGljc1BvcGluQnV0dG9uLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByQWN0aXZhdGVBbmFseXRpY3MoKTtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwck9wZW5BbmFseXRpY3MoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAuc2V0KCR3cHJBbmFseXRpY3NQb3BpbiwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHQgIC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHQgIC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjB9LHthdXRvQWxwaGE6MSwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAuZnJvbVRvKCR3cHJBbmFseXRpY3NQb3BpbiwgMC42LCB7YXV0b0FscGhhOjAsIG1hcmdpblRvcDogLTI0fSwge2F1dG9BbHBoYToxLCBtYXJnaW5Ub3A6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJDbG9zZUFuYWx5dGljcygpe1xuXHRcdHZhciB2VEwgPSBuZXcgVGltZWxpbmVMaXRlKClcblx0XHQgIC5mcm9tVG8oJHdwckFuYWx5dGljc1BvcGluLCAwLjYsIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOiAwfSwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6LTI0LCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHQgIC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjF9LHthdXRvQWxwaGE6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHQgIC5zZXQoJHdwckFuYWx5dGljc1BvcGluLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0ICAuc2V0KCR3cHJQb3Bpbk92ZXJsYXksIHsnZGlzcGxheSc6J25vbmUnfSlcblx0XHQ7XG5cdH1cblxuXHRmdW5jdGlvbiB3cHJBY3RpdmF0ZUFuYWx5dGljcygpe1xuXHRcdHdwckNsb3NlQW5hbHl0aWNzKCk7XG5cdFx0JCgnI2FuYWx5dGljc19lbmFibGVkJykucHJvcCgnY2hlY2tlZCcsIHRydWUpO1xuXHRcdCQoJyNhbmFseXRpY3NfZW5hYmxlZCcpLnRyaWdnZXIoJ2NoYW5nZScpO1xuXHR9XG5cblx0Ly8gRGlzcGxheSBDVEEgd2l0aGluIHRoZSBwb3BpbiBgV2hhdCBpbmZvIHdpbGwgd2UgY29sbGVjdD9gXG5cdCQoJyNhbmFseXRpY3NfZW5hYmxlZCcpLm9uKCdjaGFuZ2UnLCBmdW5jdGlvbiAoKSB7XG5cdFx0JCgnLndwci1yb2NrZXQtYW5hbHl0aWNzLWN0YScpLnRvZ2dsZUNsYXNzKCd3cHItaXNIaWRkZW4nKTtcblx0fSk7XG5cblx0LyoqKlxuXHQqIFNob3cgcG9waW4gdXBncmFkZVxuXHQqKiovXG5cblx0dmFyICR3cHJVcGdyYWRlUG9waW4gPSAkKCcud3ByLVBvcGluLVVwZ3JhZGUnKSxcblx0JHdwclVwZ3JhZGVDbG9zZVBvcGluID0gJCgnLndwci1Qb3Bpbi1VcGdyYWRlLWNsb3NlJyksXG5cdCR3cHJVcGdyYWRlT3BlblBvcGluID0gJCgnLndwci1wb3Bpbi11cGdyYWRlLXRvZ2dsZScpO1xuXG5cdCR3cHJVcGdyYWRlT3BlblBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKGUpIHtcblx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0d3ByT3BlblVwZ3JhZGVQb3BpbigpO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fSk7XG5cblx0JHdwclVwZ3JhZGVDbG9zZVBvcGluLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckNsb3NlVXBncmFkZVBvcGluKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJPcGVuVXBncmFkZVBvcGluKCl7XG5cdFx0dmFyIHZUTCA9IG5ldyBUaW1lbGluZUxpdGUoKTtcblxuXHRcdHZUTC5zZXQoJHdwclVwZ3JhZGVQb3BpbiwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHRcdC5zZXQoJHdwclBvcGluT3ZlcmxheSwgeydkaXNwbGF5JzonYmxvY2snfSlcblx0XHRcdC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjB9LHthdXRvQWxwaGE6MSwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0XHQuZnJvbVRvKCR3cHJVcGdyYWRlUG9waW4sIDAuNiwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6IC0yNH0sIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNScpXG5cdFx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gd3ByQ2xvc2VVcGdyYWRlUG9waW4oKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpO1xuXG5cdFx0dlRMLmZyb21Ubygkd3ByVXBncmFkZVBvcGluLCAwLjYsIHthdXRvQWxwaGE6MSwgbWFyZ2luVG9wOiAwfSwge2F1dG9BbHBoYTowLCBtYXJnaW5Ub3A6LTI0LCBlYXNlOlBvd2VyNC5lYXNlT3V0fSlcblx0XHRcdC5mcm9tVG8oJHdwclBvcGluT3ZlcmxheSwgMC42LCB7YXV0b0FscGhhOjF9LHthdXRvQWxwaGE6MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0sICc9LS41Jylcblx0XHRcdC5zZXQoJHdwclVwZ3JhZGVQb3BpbiwgeydkaXNwbGF5Jzonbm9uZSd9KVxuXHRcdFx0LnNldCgkd3ByUG9waW5PdmVybGF5LCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cblx0LyoqKlxuXHQqIFNpZGViYXIgb24vb2ZmXG5cdCoqKi9cblx0dmFyICR3cHJTaWRlYmFyICAgID0gJCggJy53cHItU2lkZWJhcicgKTtcblx0dmFyICR3cHJCdXR0b25UaXBzID0gJCgnLndwci1qcy10aXBzJyk7XG5cblx0JHdwckJ1dHRvblRpcHMub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuXHRcdHdwckRldGVjdFRpcHMoJCh0aGlzKSk7XG5cdH0pO1xuXG5cdGZ1bmN0aW9uIHdwckRldGVjdFRpcHMoYUVsZW0pe1xuXHRcdGlmKGFFbGVtLmlzKCc6Y2hlY2tlZCcpKXtcblx0XHRcdCR3cHJTaWRlYmFyLmNzcygnZGlzcGxheScsJ2Jsb2NrJyk7XG5cdFx0XHRsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb24nICk7XG5cdFx0fVxuXHRcdGVsc2V7XG5cdFx0XHQkd3ByU2lkZWJhci5jc3MoJ2Rpc3BsYXknLCdub25lJyk7XG5cdFx0XHRsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb2ZmJyApO1xuXHRcdH1cblx0fVxuXG5cblxuXHQvKioqXG5cdCogRGV0ZWN0IEFkYmxvY2tcblx0KioqL1xuXG5cdGlmKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdMS2dPY0NScHdtQWonKSl7XG5cdFx0JCgnLndwci1hZGJsb2NrJykuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcblx0fSBlbHNlIHtcblx0XHQkKCcud3ByLWFkYmxvY2snKS5jc3MoJ2Rpc3BsYXknLCAnYmxvY2snKTtcblx0fVxuXG5cdHZhciAkYWRibG9jayA9ICQoJy53cHItYWRibG9jaycpO1xuXHR2YXIgJGFkYmxvY2tDbG9zZSA9ICQoJy53cHItYWRibG9jay1jbG9zZScpO1xuXG5cdCRhZGJsb2NrQ2xvc2Uub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0d3ByQ2xvc2VBZGJsb2NrTm90aWNlKCk7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9KTtcblxuXHRmdW5jdGlvbiB3cHJDbG9zZUFkYmxvY2tOb3RpY2UoKXtcblx0XHR2YXIgdlRMID0gbmV3IFRpbWVsaW5lTGl0ZSgpXG5cdFx0ICAudG8oJGFkYmxvY2ssIDEsIHthdXRvQWxwaGE6MCwgeDo0MCwgZWFzZTpQb3dlcjQuZWFzZU91dH0pXG5cdFx0ICAudG8oJGFkYmxvY2ssIDAuNCwge2hlaWdodDogMCwgbWFyZ2luVG9wOjAsIGVhc2U6UG93ZXI0LmVhc2VPdXR9LCAnPS0uNCcpXG5cdFx0ICAuc2V0KCRhZGJsb2NrLCB7J2Rpc3BsYXknOidub25lJ30pXG5cdFx0O1xuXHR9XG5cbn0pO1xuIiwiZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ0RPTUNvbnRlbnRMb2FkZWQnLCBmdW5jdGlvbiAoKSB7XG5cbiAgICB2YXIgJHBhZ2VNYW5hZ2VyID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcIi53cHItQ29udGVudFwiKTtcbiAgICBpZigkcGFnZU1hbmFnZXIpe1xuICAgICAgICBuZXcgUGFnZU1hbmFnZXIoJHBhZ2VNYW5hZ2VyKTtcbiAgICB9XG5cbn0pO1xuXG5cbi8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qXFxcblx0XHRDTEFTUyBQQUdFTUFOQUdFUlxuXFwqLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xuLyoqXG4gKiBNYW5hZ2VzIHRoZSBkaXNwbGF5IG9mIHBhZ2VzIC8gc2VjdGlvbiBmb3IgV1AgUm9ja2V0IHBsdWdpblxuICpcbiAqIFB1YmxpYyBtZXRob2QgOlxuICAgICBkZXRlY3RJRCAtIERldGVjdCBJRCB3aXRoIGhhc2hcbiAgICAgZ2V0Qm9keVRvcCAtIEdldCBib2R5IHRvcCBwb3NpdGlvblxuXHQgY2hhbmdlIC0gRGlzcGxheXMgdGhlIGNvcnJlc3BvbmRpbmcgcGFnZVxuICpcbiAqL1xuXG5mdW5jdGlvbiBQYWdlTWFuYWdlcihhRWxlbSkge1xuXG4gICAgdmFyIHJlZlRoaXMgPSB0aGlzO1xuXG4gICAgdGhpcy4kYm9keSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItYm9keScpO1xuICAgIHRoaXMuJG1lbnVJdGVtcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItbWVudUl0ZW0nKTtcbiAgICB0aGlzLiRzdWJtaXRCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQgPiBmb3JtID4gI3dwci1vcHRpb25zLXN1Ym1pdCcpO1xuICAgIHRoaXMuJHBhZ2VzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLndwci1QYWdlJyk7XG4gICAgdGhpcy4kc2lkZWJhciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItU2lkZWJhcicpO1xuICAgIHRoaXMuJGNvbnRlbnQgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcud3ByLUNvbnRlbnQnKTtcbiAgICB0aGlzLiR0aXBzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLndwci1Db250ZW50LXRpcHMnKTtcbiAgICB0aGlzLiRsaW5rcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy53cHItYm9keSBhJyk7XG4gICAgdGhpcy4kbWVudUl0ZW0gPSBudWxsO1xuICAgIHRoaXMuJHBhZ2UgPSBudWxsO1xuICAgIHRoaXMucGFnZUlkID0gbnVsbDtcbiAgICB0aGlzLmJvZHlUb3AgPSAwO1xuICAgIHRoaXMuYnV0dG9uVGV4dCA9IHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZTtcblxuICAgIHJlZlRoaXMuZ2V0Qm9keVRvcCgpO1xuXG4gICAgLy8gSWYgdXJsIHBhZ2UgY2hhbmdlXG4gICAgd2luZG93Lm9uaGFzaGNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuICAgICAgICByZWZUaGlzLmRldGVjdElEKCk7XG4gICAgfVxuXG4gICAgLy8gSWYgaGFzaCBhbHJlYWR5IGV4aXN0IChhZnRlciByZWZyZXNoIHBhZ2UgZm9yIGV4YW1wbGUpXG4gICAgaWYod2luZG93LmxvY2F0aW9uLmhhc2gpe1xuICAgICAgICB0aGlzLmJvZHlUb3AgPSAwO1xuICAgICAgICB0aGlzLmRldGVjdElEKCk7XG4gICAgfVxuICAgIGVsc2V7XG4gICAgICAgIHZhciBzZXNzaW9uID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ3dwci1oYXNoJyk7XG4gICAgICAgIHRoaXMuYm9keVRvcCA9IDA7XG5cbiAgICAgICAgaWYoc2Vzc2lvbil7XG4gICAgICAgICAgICB3aW5kb3cubG9jYXRpb24uaGFzaCA9IHNlc3Npb247XG4gICAgICAgICAgICB0aGlzLmRldGVjdElEKCk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZXtcbiAgICAgICAgICAgIHRoaXMuJG1lbnVJdGVtc1swXS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgICAgICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgJ2Rhc2hib2FyZCcpO1xuICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLmhhc2ggPSAnI2Rhc2hib2FyZCc7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBDbGljayBsaW5rIHNhbWUgaGFzaFxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kbGlua3MubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgdGhpcy4kbGlua3NbaV0ub25jbGljayA9IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgcmVmVGhpcy5nZXRCb2R5VG9wKCk7XG4gICAgICAgICAgICB2YXIgaHJlZlNwbGl0ID0gdGhpcy5ocmVmLnNwbGl0KCcjJylbMV07XG4gICAgICAgICAgICBpZihocmVmU3BsaXQgPT0gcmVmVGhpcy5wYWdlSWQgJiYgaHJlZlNwbGl0ICE9IHVuZGVmaW5lZCl7XG4gICAgICAgICAgICAgICAgcmVmVGhpcy5kZXRlY3RJRCgpO1xuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfTtcbiAgICB9XG5cbiAgICAvLyBDbGljayBsaW5rcyBub3QgV1Agcm9ja2V0IHRvIHJlc2V0IGhhc2hcbiAgICB2YXIgJG90aGVybGlua3MgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcjYWRtaW5tZW51bWFpbiBhLCAjd3BhZG1pbmJhciBhJyk7XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCAkb3RoZXJsaW5rcy5sZW5ndGg7IGkrKykge1xuICAgICAgICAkb3RoZXJsaW5rc1tpXS5vbmNsaWNrID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgnd3ByLWhhc2gnLCAnJyk7XG4gICAgICAgIH07XG4gICAgfVxuXG59XG5cblxuLypcbiogUGFnZSBkZXRlY3QgSURcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZGV0ZWN0SUQgPSBmdW5jdGlvbigpIHtcbiAgICB0aGlzLnBhZ2VJZCA9IHdpbmRvdy5sb2NhdGlvbi5oYXNoLnNwbGl0KCcjJylbMV07XG4gICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oJ3dwci1oYXNoJywgdGhpcy5wYWdlSWQpO1xuXG4gICAgdGhpcy4kcGFnZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy53cHItUGFnZSMnICsgdGhpcy5wYWdlSWQpO1xuICAgIHRoaXMuJG1lbnVJdGVtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3dwci1uYXYtJyArIHRoaXMucGFnZUlkKTtcblxuICAgIHRoaXMuY2hhbmdlKCk7XG59XG5cblxuXG4vKlxuKiBHZXQgYm9keSB0b3AgcG9zaXRpb25cbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuZ2V0Qm9keVRvcCA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBib2R5UG9zID0gdGhpcy4kYm9keS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICB0aGlzLmJvZHlUb3AgPSBib2R5UG9zLnRvcCArIHdpbmRvdy5wYWdlWU9mZnNldCAtIDQ3OyAvLyAjd3BhZG1pbmJhciArIHBhZGRpbmctdG9wIC53cHItd3JhcCAtIDEgLSA0N1xufVxuXG5cblxuLypcbiogUGFnZSBjaGFuZ2VcbiovXG5QYWdlTWFuYWdlci5wcm90b3R5cGUuY2hhbmdlID0gZnVuY3Rpb24oKSB7XG5cbiAgICB2YXIgcmVmVGhpcyA9IHRoaXM7XG4gICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNjcm9sbFRvcCA9IHJlZlRoaXMuYm9keVRvcDtcblxuICAgIC8vIEhpZGUgb3RoZXIgcGFnZXNcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuJHBhZ2VzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJHBhZ2VzW2ldLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy4kbWVudUl0ZW1zLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRoaXMuJG1lbnVJdGVtc1tpXS5jbGFzc0xpc3QucmVtb3ZlKCdpc0FjdGl2ZScpO1xuICAgIH1cblxuICAgIC8vIFNob3cgY3VycmVudCBkZWZhdWx0IHBhZ2VcbiAgICB0aGlzLiRwYWdlLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcblxuICAgIGlmICggbnVsbCA9PT0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oICd3cHItc2hvdy1zaWRlYmFyJyApICkge1xuICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSggJ3dwci1zaG93LXNpZGViYXInLCAnb24nICk7XG4gICAgfVxuXG4gICAgaWYgKCAnb24nID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgIH0gZWxzZSBpZiAoICdvZmYnID09PSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnd3ByLXNob3ctc2lkZWJhcicpICkge1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyN3cHItanMtdGlwcycpLnJlbW92ZUF0dHJpYnV0ZSggJ2NoZWNrZWQnICk7XG4gICAgfVxuXG4gICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICB0aGlzLiRtZW51SXRlbS5jbGFzc0xpc3QuYWRkKCdpc0FjdGl2ZScpO1xuICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi52YWx1ZSA9IHRoaXMuYnV0dG9uVGV4dDtcbiAgICB0aGlzLiRjb250ZW50LmNsYXNzTGlzdC5hZGQoJ2lzTm90RnVsbCcpO1xuXG5cbiAgICAvLyBFeGNlcHRpb24gZm9yIGRhc2hib2FyZFxuICAgIGlmKHRoaXMucGFnZUlkID09IFwiZGFzaGJvYXJkXCIpe1xuICAgICAgICB0aGlzLiRzaWRlYmFyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIHRoaXMuJHRpcHMuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIHRoaXMuJGNvbnRlbnQuY2xhc3NMaXN0LnJlbW92ZSgnaXNOb3RGdWxsJyk7XG4gICAgfVxuXG4gICAgLy8gRXhjZXB0aW9uIGZvciBhZGRvbnNcbiAgICBpZih0aGlzLnBhZ2VJZCA9PSBcImFkZG9uc1wiKXtcbiAgICAgICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgLy8gRXhjZXB0aW9uIGZvciBkYXRhYmFzZVxuICAgIGlmKHRoaXMucGFnZUlkID09IFwiZGF0YWJhc2VcIil7XG4gICAgICAgIHRoaXMuJHN1Ym1pdEJ1dHRvbi5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIH1cblxuICAgIC8vIEV4Y2VwdGlvbiBmb3IgdG9vbHMgYW5kIGFkZG9uc1xuICAgIGlmKHRoaXMucGFnZUlkID09IFwidG9vbHNcIiB8fCB0aGlzLnBhZ2VJZCA9PSBcImFkZG9uc1wiKXtcbiAgICAgICAgdGhpcy4kc3VibWl0QnV0dG9uLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgfVxuXG4gICAgaWYgKHRoaXMucGFnZUlkID09IFwiaW1hZ2lmeVwiKSB7XG4gICAgICAgIHRoaXMuJHNpZGViYXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy4kdGlwcy5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG5cbiAgICBpZiAodGhpcy5wYWdlSWQgPT0gXCJ0dXRvcmlhbHNcIikge1xuICAgICAgICB0aGlzLiRzdWJtaXRCdXR0b24uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICB9XG59O1xuIiwiLyplc2xpbnQtZW52IGVzNiovXG4oICggZG9jdW1lbnQsIHdpbmRvdyApID0+IHtcblx0J3VzZSBzdHJpY3QnO1xuXG5cdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcud3ByLXJvY2tldGNkbi1vcGVuJyApLmZvckVhY2goICggZWwgKSA9PiB7XG5cdFx0XHRlbC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGUgKSA9PiB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdH0gKTtcblx0XHR9ICk7XG5cblx0XHRtYXliZU9wZW5Nb2RhbCgpO1xuXG5cdFx0TWljcm9Nb2RhbC5pbml0KCB7XG5cdFx0XHRkaXNhYmxlU2Nyb2xsOiB0cnVlXG5cdFx0fSApO1xuXHR9ICk7XG5cblx0d2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdsb2FkJywgKCkgPT4ge1xuXHRcdGxldCBvcGVuQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLW9wZW4tY3RhJyApLFxuXHRcdFx0Y2xvc2VDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY2xvc2UtY3RhJyApLFxuXHRcdFx0c21hbGxDVEEgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3dwci1yb2NrZXRjZG4tY3RhLXNtYWxsJyApLFxuXHRcdFx0YmlnQ1RBID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyN3cHItcm9ja2V0Y2RuLWN0YScgKTtcblxuXHRcdGlmICggbnVsbCAhPT0gb3BlbkNUQSAmJiBudWxsICE9PSBzbWFsbENUQSAmJiBudWxsICE9PSBiaWdDVEEgKSB7XG5cdFx0XHRvcGVuQ1RBLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0XHRcdHNtYWxsQ1RBLmNsYXNzTGlzdC5hZGQoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdGJpZ0NUQS5jbGFzc0xpc3QucmVtb3ZlKCAnd3ByLWlzSGlkZGVuJyApO1xuXG5cdFx0XHRcdHNlbmRIVFRQUmVxdWVzdCggZ2V0UG9zdERhdGEoICdiaWcnICkgKTtcblx0XHRcdH0gKTtcblx0XHR9XG5cblx0XHRpZiAoIG51bGwgIT09IGNsb3NlQ1RBICYmIG51bGwgIT09IHNtYWxsQ1RBICYmIG51bGwgIT09IGJpZ0NUQSApIHtcblx0XHRcdGNsb3NlQ1RBLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblx0XHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0XHRcdHNtYWxsQ1RBLmNsYXNzTGlzdC5yZW1vdmUoICd3cHItaXNIaWRkZW4nICk7XG5cdFx0XHRcdGJpZ0NUQS5jbGFzc0xpc3QuYWRkKCAnd3ByLWlzSGlkZGVuJyApO1xuXG5cdFx0XHRcdHNlbmRIVFRQUmVxdWVzdCggZ2V0UG9zdERhdGEoICdzbWFsbCcgKSApO1xuXHRcdFx0fSApO1xuXHRcdH1cblxuXHRcdGZ1bmN0aW9uIGdldFBvc3REYXRhKCBzdGF0dXMgKSB7XG5cdFx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj10b2dnbGVfcm9ja2V0Y2RuX2N0YSc7XG5cdFx0XHRwb3N0RGF0YSArPSAnJnN0YXR1cz0nICsgc3RhdHVzO1xuXHRcdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdFx0cmV0dXJuIHBvc3REYXRhO1xuXHRcdH1cblx0fSApO1xuXG5cdHdpbmRvdy5vbm1lc3NhZ2UgPSAoIGUgKSA9PiB7XG5cdFx0Y29uc3QgaWZyYW1lVVJMID0gcm9ja2V0X2FqYXhfZGF0YS5vcmlnaW5fdXJsO1xuXG5cdFx0aWYgKCBlLm9yaWdpbiAhPT0gaWZyYW1lVVJMICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdHNldENETkZyYW1lSGVpZ2h0KCBlLmRhdGEgKTtcblx0XHRjbG9zZU1vZGFsKCBlLmRhdGEgKTtcblx0XHR0b2tlbkhhbmRsZXIoIGUuZGF0YSwgaWZyYW1lVVJMICk7XG5cdFx0cHJvY2Vzc1N0YXR1cyggZS5kYXRhICk7XG5cdFx0ZW5hYmxlQ0ROKCBlLmRhdGEsIGlmcmFtZVVSTCApO1xuXHRcdGRpc2FibGVDRE4oIGUuZGF0YSwgaWZyYW1lVVJMICk7XG5cdFx0dmFsaWRhdGVUb2tlbkFuZENOQU1FKCBlLmRhdGEgKTtcblx0fTtcblxuXHRmdW5jdGlvbiBtYXliZU9wZW5Nb2RhbCgpIHtcblx0XHRsZXQgcG9zdERhdGEgPSAnJztcblxuXHRcdHBvc3REYXRhICs9ICdhY3Rpb249cm9ja2V0Y2RuX3Byb2Nlc3Nfc3RhdHVzJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cblx0XHRcdFx0aWYgKCB0cnVlID09PSByZXNwb25zZVR4dC5zdWNjZXNzICkge1xuXHRcdFx0XHRcdE1pY3JvTW9kYWwuc2hvdyggJ3dwci1yb2NrZXRjZG4tbW9kYWwnICk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gY2xvc2VNb2RhbCggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2NkbkZyYW1lQ2xvc2UnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0TWljcm9Nb2RhbC5jbG9zZSggJ3dwci1yb2NrZXRjZG4tbW9kYWwnICk7XG5cblx0XHRsZXQgcGFnZXMgPSBbICdpZnJhbWUtcGF5bWVudC1zdWNjZXNzJywgJ2lmcmFtZS11bnN1YnNjcmliZS1zdWNjZXNzJyBdO1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdjZG5fcGFnZV9tZXNzYWdlJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGlmICggcGFnZXMuaW5kZXhPZiggZGF0YS5jZG5fcGFnZV9tZXNzYWdlICkgPT09IC0xICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LmxvY2F0aW9uLnJlbG9hZCgpO1xuXHR9XG5cblx0ZnVuY3Rpb24gcHJvY2Vzc1N0YXR1cyggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl9wcm9jZXNzJyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGxldCBwb3N0RGF0YSA9ICcnO1xuXG5cdFx0cG9zdERhdGEgKz0gJ2FjdGlvbj1yb2NrZXRjZG5fcHJvY2Vzc19zZXQnO1xuXHRcdHBvc3REYXRhICs9ICcmc3RhdHVzPScgKyBkYXRhLnJvY2tldGNkbl9wcm9jZXNzO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cdH1cblxuXHRmdW5jdGlvbiBlbmFibGVDRE4oIGRhdGEsIGlmcmFtZVVSTCApIHtcblx0XHRsZXQgaWZyYW1lID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJyNyb2NrZXRjZG4taWZyYW1lJyApLmNvbnRlbnRXaW5kb3c7XG5cblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl91cmwnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9lbmFibGUnO1xuXHRcdHBvc3REYXRhICs9ICcmY2RuX3VybD0nICsgZGF0YS5yb2NrZXRjZG5fdXJsO1xuXHRcdHBvc3REYXRhICs9ICcmbm9uY2U9JyArIHJvY2tldF9hamF4X2RhdGEubm9uY2U7XG5cblx0XHRjb25zdCByZXF1ZXN0ID0gc2VuZEhUVFBSZXF1ZXN0KCBwb3N0RGF0YSApO1xuXG5cdFx0cmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSAoKSA9PiB7XG5cdFx0XHRpZiAoIHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gWE1MSHR0cFJlcXVlc3QuRE9ORSAmJiAyMDAgPT09IHJlcXVlc3Quc3RhdHVzICkge1xuXHRcdFx0XHRsZXQgcmVzcG9uc2VUeHQgPSBKU09OLnBhcnNlKHJlcXVlc3QucmVzcG9uc2VUZXh0KTtcblx0XHRcdFx0aWZyYW1lLnBvc3RNZXNzYWdlKFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdCdzdWNjZXNzJzogcmVzcG9uc2VUeHQuc3VjY2Vzcyxcblx0XHRcdFx0XHRcdCdkYXRhJzogcmVzcG9uc2VUeHQuZGF0YSxcblx0XHRcdFx0XHRcdCdyb2NrZXRjZG4nOiB0cnVlXG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdFx0KTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0ZnVuY3Rpb24gZGlzYWJsZUNETiggZGF0YSwgaWZyYW1lVVJMICkge1xuXHRcdGxldCBpZnJhbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnI3JvY2tldGNkbi1pZnJhbWUnICkuY29udGVudFdpbmRvdztcblxuXHRcdGlmICggISBkYXRhLmhhc093blByb3BlcnR5KCAncm9ja2V0Y2RuX2Rpc2FibGUnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl9kaXNhYmxlJztcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKSB7XG5cdFx0Y29uc3QgaHR0cFJlcXVlc3QgPSBuZXcgWE1MSHR0cFJlcXVlc3QoKTtcblxuXHRcdGh0dHBSZXF1ZXN0Lm9wZW4oICdQT1NUJywgYWpheHVybCApO1xuXHRcdGh0dHBSZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoICdDb250ZW50LVR5cGUnLCAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJyApO1xuXHRcdGh0dHBSZXF1ZXN0LnNlbmQoIHBvc3REYXRhICk7XG5cblx0XHRyZXR1cm4gaHR0cFJlcXVlc3Q7XG5cdH1cblxuXHRmdW5jdGlvbiBzZXRDRE5GcmFtZUhlaWdodCggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ2NkbkZyYW1lSGVpZ2h0JyApICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAncm9ja2V0Y2RuLWlmcmFtZScgKS5zdHlsZS5oZWlnaHQgPSBgJHsgZGF0YS5jZG5GcmFtZUhlaWdodCB9cHhgO1xuXHR9XG5cblx0ZnVuY3Rpb24gdG9rZW5IYW5kbGVyKCBkYXRhLCBpZnJhbWVVUkwgKSB7XG5cdFx0bGV0IGlmcmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcjcm9ja2V0Y2RuLWlmcmFtZScgKS5jb250ZW50V2luZG93O1xuXG5cdFx0aWYgKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdG9rZW4nICkgKSB7XG5cdFx0XHRsZXQgZGF0YSA9IHtwcm9jZXNzOlwic3Vic2NyaWJlXCIsIG1lc3NhZ2U6XCJ0b2tlbl9ub3RfcmVjZWl2ZWRcIn07XG5cdFx0XHRpZnJhbWUucG9zdE1lc3NhZ2UoXG5cdFx0XHRcdHtcblx0XHRcdFx0XHQnc3VjY2Vzcyc6IGZhbHNlLFxuXHRcdFx0XHRcdCdkYXRhJzogZGF0YSxcblx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHR9LFxuXHRcdFx0XHRpZnJhbWVVUkxcblx0XHRcdCk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXNhdmVfcm9ja2V0Y2RuX3Rva2VuJztcblx0XHRwb3N0RGF0YSArPSAnJnZhbHVlPScgKyBkYXRhLnJvY2tldGNkbl90b2tlbjtcblx0XHRwb3N0RGF0YSArPSAnJm5vbmNlPScgKyByb2NrZXRfYWpheF9kYXRhLm5vbmNlO1xuXG5cdFx0Y29uc3QgcmVxdWVzdCA9IHNlbmRIVFRQUmVxdWVzdCggcG9zdERhdGEgKTtcblxuXHRcdHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gKCkgPT4ge1xuXHRcdFx0aWYgKCByZXF1ZXN0LnJlYWR5U3RhdGUgPT09IFhNTEh0dHBSZXF1ZXN0LkRPTkUgJiYgMjAwID09PSByZXF1ZXN0LnN0YXR1cyApIHtcblx0XHRcdFx0bGV0IHJlc3BvbnNlVHh0ID0gSlNPTi5wYXJzZShyZXF1ZXN0LnJlc3BvbnNlVGV4dCk7XG5cdFx0XHRcdGlmcmFtZS5wb3N0TWVzc2FnZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHQnc3VjY2Vzcyc6IHJlc3BvbnNlVHh0LnN1Y2Nlc3MsXG5cdFx0XHRcdFx0XHQnZGF0YSc6IHJlc3BvbnNlVHh0LmRhdGEsXG5cdFx0XHRcdFx0XHQncm9ja2V0Y2RuJzogdHJ1ZVxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0aWZyYW1lVVJMXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdGZ1bmN0aW9uIHZhbGlkYXRlVG9rZW5BbmRDTkFNRSggZGF0YSApIHtcblx0XHRpZiAoICEgZGF0YS5oYXNPd25Qcm9wZXJ0eSggJ3JvY2tldGNkbl92YWxpZGF0ZV90b2tlbicgKSB8fCAhIGRhdGEuaGFzT3duUHJvcGVydHkoICdyb2NrZXRjZG5fdmFsaWRhdGVfY25hbWUnICkgKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0bGV0IHBvc3REYXRhID0gJyc7XG5cblx0XHRwb3N0RGF0YSArPSAnYWN0aW9uPXJvY2tldGNkbl92YWxpZGF0ZV90b2tlbl9jbmFtZSc7XG5cdFx0cG9zdERhdGEgKz0gJyZjZG5fdXJsPScgKyBkYXRhLnJvY2tldGNkbl92YWxpZGF0ZV9jbmFtZTtcblx0XHRwb3N0RGF0YSArPSAnJmNkbl90b2tlbj0nICsgZGF0YS5yb2NrZXRjZG5fdmFsaWRhdGVfdG9rZW47XG5cdFx0cG9zdERhdGEgKz0gJyZub25jZT0nICsgcm9ja2V0X2FqYXhfZGF0YS5ub25jZTtcblxuXHRcdGNvbnN0IHJlcXVlc3QgPSBzZW5kSFRUUFJlcXVlc3QoIHBvc3REYXRhICk7XG5cdH1cbn0gKSggZG9jdW1lbnQsIHdpbmRvdyApO1xuIiwiLyohXHJcbiAqIFZFUlNJT046IDEuMTIuMVxyXG4gKiBEQVRFOiAyMDE0LTA2LTI2XHJcbiAqIFVQREFURVMgQU5EIERPQ1MgQVQ6IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbVxyXG4gKlxyXG4gKiBAbGljZW5zZSBDb3B5cmlnaHQgKGMpIDIwMDgtMjAxNCwgR3JlZW5Tb2NrLiBBbGwgcmlnaHRzIHJlc2VydmVkLlxyXG4gKiBUaGlzIHdvcmsgaXMgc3ViamVjdCB0byB0aGUgdGVybXMgYXQgaHR0cDovL3d3dy5ncmVlbnNvY2suY29tL3Rlcm1zX29mX3VzZS5odG1sIG9yIGZvclxyXG4gKiBDbHViIEdyZWVuU29jayBtZW1iZXJzLCB0aGUgc29mdHdhcmUgYWdyZWVtZW50IHRoYXQgd2FzIGlzc3VlZCB3aXRoIHlvdXIgbWVtYmVyc2hpcC5cclxuICogXHJcbiAqIEBhdXRob3I6IEphY2sgRG95bGUsIGphY2tAZ3JlZW5zb2NrLmNvbVxyXG4gKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt3aW5kb3cuX2dzRGVmaW5lKFwiVGltZWxpbmVMaXRlXCIsW1wiY29yZS5BbmltYXRpb25cIixcImNvcmUuU2ltcGxlVGltZWxpbmVcIixcIlR3ZWVuTGl0ZVwiXSxmdW5jdGlvbih0LGUsaSl7dmFyIHM9ZnVuY3Rpb24odCl7ZS5jYWxsKHRoaXMsdCksdGhpcy5fbGFiZWxzPXt9LHRoaXMuYXV0b1JlbW92ZUNoaWxkcmVuPXRoaXMudmFycy5hdXRvUmVtb3ZlQ2hpbGRyZW49PT0hMCx0aGlzLnNtb290aENoaWxkVGltaW5nPXRoaXMudmFycy5zbW9vdGhDaGlsZFRpbWluZz09PSEwLHRoaXMuX3NvcnRDaGlsZHJlbj0hMCx0aGlzLl9vblVwZGF0ZT10aGlzLnZhcnMub25VcGRhdGU7dmFyIGkscyxyPXRoaXMudmFycztmb3IocyBpbiByKWk9cltzXSxhKGkpJiYtMSE9PWkuam9pbihcIlwiKS5pbmRleE9mKFwie3NlbGZ9XCIpJiYocltzXT10aGlzLl9zd2FwU2VsZkluUGFyYW1zKGkpKTthKHIudHdlZW5zKSYmdGhpcy5hZGQoci50d2VlbnMsMCxyLmFsaWduLHIuc3RhZ2dlcil9LHI9MWUtMTAsbj1pLl9pbnRlcm5hbHMuaXNTZWxlY3RvcixhPWkuX2ludGVybmFscy5pc0FycmF5LG89W10saD13aW5kb3cuX2dzRGVmaW5lLmdsb2JhbHMsbD1mdW5jdGlvbih0KXt2YXIgZSxpPXt9O2ZvcihlIGluIHQpaVtlXT10W2VdO3JldHVybiBpfSxfPWZ1bmN0aW9uKHQsZSxpLHMpe3QuX3RpbWVsaW5lLnBhdXNlKHQuX3N0YXJ0VGltZSksZSYmZS5hcHBseShzfHx0Ll90aW1lbGluZSxpfHxvKX0sdT1vLnNsaWNlLGY9cy5wcm90b3R5cGU9bmV3IGU7cmV0dXJuIHMudmVyc2lvbj1cIjEuMTIuMVwiLGYuY29uc3RydWN0b3I9cyxmLmtpbGwoKS5fZ2M9ITEsZi50bz1mdW5jdGlvbih0LGUscyxyKXt2YXIgbj1zLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aTtyZXR1cm4gZT90aGlzLmFkZChuZXcgbih0LGUscykscik6dGhpcy5zZXQodCxzLHIpfSxmLmZyb209ZnVuY3Rpb24odCxlLHMscil7cmV0dXJuIHRoaXMuYWRkKChzLnJlcGVhdCYmaC5Ud2Vlbk1heHx8aSkuZnJvbSh0LGUscykscil9LGYuZnJvbVRvPWZ1bmN0aW9uKHQsZSxzLHIsbil7dmFyIGE9ci5yZXBlYXQmJmguVHdlZW5NYXh8fGk7cmV0dXJuIGU/dGhpcy5hZGQoYS5mcm9tVG8odCxlLHMsciksbik6dGhpcy5zZXQodCxyLG4pfSxmLnN0YWdnZXJUbz1mdW5jdGlvbih0LGUscixhLG8saCxfLGYpe3ZhciBwLGM9bmV3IHMoe29uQ29tcGxldGU6aCxvbkNvbXBsZXRlUGFyYW1zOl8sb25Db21wbGV0ZVNjb3BlOmYsc21vb3RoQ2hpbGRUaW1pbmc6dGhpcy5zbW9vdGhDaGlsZFRpbWluZ30pO2ZvcihcInN0cmluZ1wiPT10eXBlb2YgdCYmKHQ9aS5zZWxlY3Rvcih0KXx8dCksbih0KSYmKHQ9dS5jYWxsKHQsMCkpLGE9YXx8MCxwPTA7dC5sZW5ndGg+cDtwKyspci5zdGFydEF0JiYoci5zdGFydEF0PWwoci5zdGFydEF0KSksYy50byh0W3BdLGUsbChyKSxwKmEpO3JldHVybiB0aGlzLmFkZChjLG8pfSxmLnN0YWdnZXJGcm9tPWZ1bmN0aW9uKHQsZSxpLHMscixuLGEsbyl7cmV0dXJuIGkuaW1tZWRpYXRlUmVuZGVyPTAhPWkuaW1tZWRpYXRlUmVuZGVyLGkucnVuQmFja3dhcmRzPSEwLHRoaXMuc3RhZ2dlclRvKHQsZSxpLHMscixuLGEsbyl9LGYuc3RhZ2dlckZyb21Ubz1mdW5jdGlvbih0LGUsaSxzLHIsbixhLG8saCl7cmV0dXJuIHMuc3RhcnRBdD1pLHMuaW1tZWRpYXRlUmVuZGVyPTAhPXMuaW1tZWRpYXRlUmVuZGVyJiYwIT1pLmltbWVkaWF0ZVJlbmRlcix0aGlzLnN0YWdnZXJUbyh0LGUscyxyLG4sYSxvLGgpfSxmLmNhbGw9ZnVuY3Rpb24odCxlLHMscil7cmV0dXJuIHRoaXMuYWRkKGkuZGVsYXllZENhbGwoMCx0LGUscykscil9LGYuc2V0PWZ1bmN0aW9uKHQsZSxzKXtyZXR1cm4gcz10aGlzLl9wYXJzZVRpbWVPckxhYmVsKHMsMCwhMCksbnVsbD09ZS5pbW1lZGlhdGVSZW5kZXImJihlLmltbWVkaWF0ZVJlbmRlcj1zPT09dGhpcy5fdGltZSYmIXRoaXMuX3BhdXNlZCksdGhpcy5hZGQobmV3IGkodCwwLGUpLHMpfSxzLmV4cG9ydFJvb3Q9ZnVuY3Rpb24odCxlKXt0PXR8fHt9LG51bGw9PXQuc21vb3RoQ2hpbGRUaW1pbmcmJih0LnNtb290aENoaWxkVGltaW5nPSEwKTt2YXIgcixuLGE9bmV3IHModCksbz1hLl90aW1lbGluZTtmb3IobnVsbD09ZSYmKGU9ITApLG8uX3JlbW92ZShhLCEwKSxhLl9zdGFydFRpbWU9MCxhLl9yYXdQcmV2VGltZT1hLl90aW1lPWEuX3RvdGFsVGltZT1vLl90aW1lLHI9by5fZmlyc3Q7cjspbj1yLl9uZXh0LGUmJnIgaW5zdGFuY2VvZiBpJiZyLnRhcmdldD09PXIudmFycy5vbkNvbXBsZXRlfHxhLmFkZChyLHIuX3N0YXJ0VGltZS1yLl9kZWxheSkscj1uO3JldHVybiBvLmFkZChhLDApLGF9LGYuYWRkPWZ1bmN0aW9uKHIsbixvLGgpe3ZhciBsLF8sdSxmLHAsYztpZihcIm51bWJlclwiIT10eXBlb2YgbiYmKG49dGhpcy5fcGFyc2VUaW1lT3JMYWJlbChuLDAsITAscikpLCEociBpbnN0YW5jZW9mIHQpKXtpZihyIGluc3RhbmNlb2YgQXJyYXl8fHImJnIucHVzaCYmYShyKSl7Zm9yKG89b3x8XCJub3JtYWxcIixoPWh8fDAsbD1uLF89ci5sZW5ndGgsdT0wO18+dTt1KyspYShmPXJbdV0pJiYoZj1uZXcgcyh7dHdlZW5zOmZ9KSksdGhpcy5hZGQoZixsKSxcInN0cmluZ1wiIT10eXBlb2YgZiYmXCJmdW5jdGlvblwiIT10eXBlb2YgZiYmKFwic2VxdWVuY2VcIj09PW8/bD1mLl9zdGFydFRpbWUrZi50b3RhbER1cmF0aW9uKCkvZi5fdGltZVNjYWxlOlwic3RhcnRcIj09PW8mJihmLl9zdGFydFRpbWUtPWYuZGVsYXkoKSkpLGwrPWg7cmV0dXJuIHRoaXMuX3VuY2FjaGUoITApfWlmKFwic3RyaW5nXCI9PXR5cGVvZiByKXJldHVybiB0aGlzLmFkZExhYmVsKHIsbik7aWYoXCJmdW5jdGlvblwiIT10eXBlb2Ygcil0aHJvd1wiQ2Fubm90IGFkZCBcIityK1wiIGludG8gdGhlIHRpbWVsaW5lOyBpdCBpcyBub3QgYSB0d2VlbiwgdGltZWxpbmUsIGZ1bmN0aW9uLCBvciBzdHJpbmcuXCI7cj1pLmRlbGF5ZWRDYWxsKDAscil9aWYoZS5wcm90b3R5cGUuYWRkLmNhbGwodGhpcyxyLG4pLCh0aGlzLl9nY3x8dGhpcy5fdGltZT09PXRoaXMuX2R1cmF0aW9uKSYmIXRoaXMuX3BhdXNlZCYmdGhpcy5fZHVyYXRpb248dGhpcy5kdXJhdGlvbigpKWZvcihwPXRoaXMsYz1wLnJhd1RpbWUoKT5yLl9zdGFydFRpbWU7cC5fdGltZWxpbmU7KWMmJnAuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nP3AudG90YWxUaW1lKHAuX3RvdGFsVGltZSwhMCk6cC5fZ2MmJnAuX2VuYWJsZWQoITAsITEpLHA9cC5fdGltZWxpbmU7cmV0dXJuIHRoaXN9LGYucmVtb3ZlPWZ1bmN0aW9uKGUpe2lmKGUgaW5zdGFuY2VvZiB0KXJldHVybiB0aGlzLl9yZW1vdmUoZSwhMSk7aWYoZSBpbnN0YW5jZW9mIEFycmF5fHxlJiZlLnB1c2gmJmEoZSkpe2Zvcih2YXIgaT1lLmxlbmd0aDstLWk+LTE7KXRoaXMucmVtb3ZlKGVbaV0pO3JldHVybiB0aGlzfXJldHVyblwic3RyaW5nXCI9PXR5cGVvZiBlP3RoaXMucmVtb3ZlTGFiZWwoZSk6dGhpcy5raWxsKG51bGwsZSl9LGYuX3JlbW92ZT1mdW5jdGlvbih0LGkpe2UucHJvdG90eXBlLl9yZW1vdmUuY2FsbCh0aGlzLHQsaSk7dmFyIHM9dGhpcy5fbGFzdDtyZXR1cm4gcz90aGlzLl90aW1lPnMuX3N0YXJ0VGltZStzLl90b3RhbER1cmF0aW9uL3MuX3RpbWVTY2FsZSYmKHRoaXMuX3RpbWU9dGhpcy5kdXJhdGlvbigpLHRoaXMuX3RvdGFsVGltZT10aGlzLl90b3RhbER1cmF0aW9uKTp0aGlzLl90aW1lPXRoaXMuX3RvdGFsVGltZT10aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPTAsdGhpc30sZi5hcHBlbmQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5hZGQodCx0aGlzLl9wYXJzZVRpbWVPckxhYmVsKG51bGwsZSwhMCx0KSl9LGYuaW5zZXJ0PWYuaW5zZXJ0TXVsdGlwbGU9ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHRoaXMuYWRkKHQsZXx8MCxpLHMpfSxmLmFwcGVuZE11bHRpcGxlPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiB0aGlzLmFkZCh0LHRoaXMuX3BhcnNlVGltZU9yTGFiZWwobnVsbCxlLCEwLHQpLGkscyl9LGYuYWRkTGFiZWw9ZnVuY3Rpb24odCxlKXtyZXR1cm4gdGhpcy5fbGFiZWxzW3RdPXRoaXMuX3BhcnNlVGltZU9yTGFiZWwoZSksdGhpc30sZi5hZGRQYXVzZT1mdW5jdGlvbih0LGUsaSxzKXtyZXR1cm4gdGhpcy5jYWxsKF8sW1wie3NlbGZ9XCIsZSxpLHNdLHRoaXMsdCl9LGYucmVtb3ZlTGFiZWw9ZnVuY3Rpb24odCl7cmV0dXJuIGRlbGV0ZSB0aGlzLl9sYWJlbHNbdF0sdGhpc30sZi5nZXRMYWJlbFRpbWU9ZnVuY3Rpb24odCl7cmV0dXJuIG51bGwhPXRoaXMuX2xhYmVsc1t0XT90aGlzLl9sYWJlbHNbdF06LTF9LGYuX3BhcnNlVGltZU9yTGFiZWw9ZnVuY3Rpb24oZSxpLHMscil7dmFyIG47aWYociBpbnN0YW5jZW9mIHQmJnIudGltZWxpbmU9PT10aGlzKXRoaXMucmVtb3ZlKHIpO2Vsc2UgaWYociYmKHIgaW5zdGFuY2VvZiBBcnJheXx8ci5wdXNoJiZhKHIpKSlmb3Iobj1yLmxlbmd0aDstLW4+LTE7KXJbbl1pbnN0YW5jZW9mIHQmJnJbbl0udGltZWxpbmU9PT10aGlzJiZ0aGlzLnJlbW92ZShyW25dKTtpZihcInN0cmluZ1wiPT10eXBlb2YgaSlyZXR1cm4gdGhpcy5fcGFyc2VUaW1lT3JMYWJlbChpLHMmJlwibnVtYmVyXCI9PXR5cGVvZiBlJiZudWxsPT10aGlzLl9sYWJlbHNbaV0/ZS10aGlzLmR1cmF0aW9uKCk6MCxzKTtpZihpPWl8fDAsXCJzdHJpbmdcIiE9dHlwZW9mIGV8fCFpc05hTihlKSYmbnVsbD09dGhpcy5fbGFiZWxzW2VdKW51bGw9PWUmJihlPXRoaXMuZHVyYXRpb24oKSk7ZWxzZXtpZihuPWUuaW5kZXhPZihcIj1cIiksLTE9PT1uKXJldHVybiBudWxsPT10aGlzLl9sYWJlbHNbZV0/cz90aGlzLl9sYWJlbHNbZV09dGhpcy5kdXJhdGlvbigpK2k6aTp0aGlzLl9sYWJlbHNbZV0raTtpPXBhcnNlSW50KGUuY2hhckF0KG4tMSkrXCIxXCIsMTApKk51bWJlcihlLnN1YnN0cihuKzEpKSxlPW4+MT90aGlzLl9wYXJzZVRpbWVPckxhYmVsKGUuc3Vic3RyKDAsbi0xKSwwLHMpOnRoaXMuZHVyYXRpb24oKX1yZXR1cm4gTnVtYmVyKGUpK2l9LGYuc2Vlaz1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnRvdGFsVGltZShcIm51bWJlclwiPT10eXBlb2YgdD90OnRoaXMuX3BhcnNlVGltZU9yTGFiZWwodCksZSE9PSExKX0sZi5zdG9wPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXMucGF1c2VkKCEwKX0sZi5nb3RvQW5kUGxheT1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnBsYXkodCxlKX0sZi5nb3RvQW5kU3RvcD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnBhdXNlKHQsZSl9LGYucmVuZGVyPWZ1bmN0aW9uKHQsZSxpKXt0aGlzLl9nYyYmdGhpcy5fZW5hYmxlZCghMCwhMSk7dmFyIHMsbixhLGgsbCxfPXRoaXMuX2RpcnR5P3RoaXMudG90YWxEdXJhdGlvbigpOnRoaXMuX3RvdGFsRHVyYXRpb24sdT10aGlzLl90aW1lLGY9dGhpcy5fc3RhcnRUaW1lLHA9dGhpcy5fdGltZVNjYWxlLGM9dGhpcy5fcGF1c2VkO2lmKHQ+PV8/KHRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPV8sdGhpcy5fcmV2ZXJzZWR8fHRoaXMuX2hhc1BhdXNlZENoaWxkKCl8fChuPSEwLGg9XCJvbkNvbXBsZXRlXCIsMD09PXRoaXMuX2R1cmF0aW9uJiYoMD09PXR8fDA+dGhpcy5fcmF3UHJldlRpbWV8fHRoaXMuX3Jhd1ByZXZUaW1lPT09cikmJnRoaXMuX3Jhd1ByZXZUaW1lIT09dCYmdGhpcy5fZmlyc3QmJihsPSEwLHRoaXMuX3Jhd1ByZXZUaW1lPnImJihoPVwib25SZXZlcnNlQ29tcGxldGVcIikpKSx0aGlzLl9yYXdQcmV2VGltZT10aGlzLl9kdXJhdGlvbnx8IWV8fHR8fHRoaXMuX3Jhd1ByZXZUaW1lPT09dD90OnIsdD1fKzFlLTQpOjFlLTc+dD8odGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9MCwoMCE9PXV8fDA9PT10aGlzLl9kdXJhdGlvbiYmdGhpcy5fcmF3UHJldlRpbWUhPT1yJiYodGhpcy5fcmF3UHJldlRpbWU+MHx8MD50JiZ0aGlzLl9yYXdQcmV2VGltZT49MCkpJiYoaD1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIsbj10aGlzLl9yZXZlcnNlZCksMD50Pyh0aGlzLl9hY3RpdmU9ITEsMD09PXRoaXMuX2R1cmF0aW9uJiZ0aGlzLl9yYXdQcmV2VGltZT49MCYmdGhpcy5fZmlyc3QmJihsPSEwKSx0aGlzLl9yYXdQcmV2VGltZT10KToodGhpcy5fcmF3UHJldlRpbWU9dGhpcy5fZHVyYXRpb258fCFlfHx0fHx0aGlzLl9yYXdQcmV2VGltZT09PXQ/dDpyLHQ9MCx0aGlzLl9pbml0dGVkfHwobD0hMCkpKTp0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10aGlzLl9yYXdQcmV2VGltZT10LHRoaXMuX3RpbWUhPT11JiZ0aGlzLl9maXJzdHx8aXx8bCl7aWYodGhpcy5faW5pdHRlZHx8KHRoaXMuX2luaXR0ZWQ9ITApLHRoaXMuX2FjdGl2ZXx8IXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZSE9PXUmJnQ+MCYmKHRoaXMuX2FjdGl2ZT0hMCksMD09PXUmJnRoaXMudmFycy5vblN0YXJ0JiYwIT09dGhpcy5fdGltZSYmKGV8fHRoaXMudmFycy5vblN0YXJ0LmFwcGx5KHRoaXMudmFycy5vblN0YXJ0U2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uU3RhcnRQYXJhbXN8fG8pKSx0aGlzLl90aW1lPj11KWZvcihzPXRoaXMuX2ZpcnN0O3MmJihhPXMuX25leHQsIXRoaXMuX3BhdXNlZHx8Yyk7KShzLl9hY3RpdmV8fHMuX3N0YXJ0VGltZTw9dGhpcy5fdGltZSYmIXMuX3BhdXNlZCYmIXMuX2djKSYmKHMuX3JldmVyc2VkP3MucmVuZGVyKChzLl9kaXJ0eT9zLnRvdGFsRHVyYXRpb24oKTpzLl90b3RhbER1cmF0aW9uKS0odC1zLl9zdGFydFRpbWUpKnMuX3RpbWVTY2FsZSxlLGkpOnMucmVuZGVyKCh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSkpLHM9YTtlbHNlIGZvcihzPXRoaXMuX2xhc3Q7cyYmKGE9cy5fcHJldiwhdGhpcy5fcGF1c2VkfHxjKTspKHMuX2FjdGl2ZXx8dT49cy5fc3RhcnRUaW1lJiYhcy5fcGF1c2VkJiYhcy5fZ2MpJiYocy5fcmV2ZXJzZWQ/cy5yZW5kZXIoKHMuX2RpcnR5P3MudG90YWxEdXJhdGlvbigpOnMuX3RvdGFsRHVyYXRpb24pLSh0LXMuX3N0YXJ0VGltZSkqcy5fdGltZVNjYWxlLGUsaSk6cy5yZW5kZXIoKHQtcy5fc3RhcnRUaW1lKSpzLl90aW1lU2NhbGUsZSxpKSkscz1hO3RoaXMuX29uVXBkYXRlJiYoZXx8dGhpcy5fb25VcGRhdGUuYXBwbHkodGhpcy52YXJzLm9uVXBkYXRlU2NvcGV8fHRoaXMsdGhpcy52YXJzLm9uVXBkYXRlUGFyYW1zfHxvKSksaCYmKHRoaXMuX2djfHwoZj09PXRoaXMuX3N0YXJ0VGltZXx8cCE9PXRoaXMuX3RpbWVTY2FsZSkmJigwPT09dGhpcy5fdGltZXx8Xz49dGhpcy50b3RhbER1cmF0aW9uKCkpJiYobiYmKHRoaXMuX3RpbWVsaW5lLmF1dG9SZW1vdmVDaGlsZHJlbiYmdGhpcy5fZW5hYmxlZCghMSwhMSksdGhpcy5fYWN0aXZlPSExKSwhZSYmdGhpcy52YXJzW2hdJiZ0aGlzLnZhcnNbaF0uYXBwbHkodGhpcy52YXJzW2grXCJTY29wZVwiXXx8dGhpcyx0aGlzLnZhcnNbaCtcIlBhcmFtc1wiXXx8bykpKX19LGYuX2hhc1BhdXNlZENoaWxkPWZ1bmN0aW9uKCl7Zm9yKHZhciB0PXRoaXMuX2ZpcnN0O3Q7KXtpZih0Ll9wYXVzZWR8fHQgaW5zdGFuY2VvZiBzJiZ0Ll9oYXNQYXVzZWRDaGlsZCgpKXJldHVybiEwO3Q9dC5fbmV4dH1yZXR1cm4hMX0sZi5nZXRDaGlsZHJlbj1mdW5jdGlvbih0LGUscyxyKXtyPXJ8fC05OTk5OTk5OTk5O2Zvcih2YXIgbj1bXSxhPXRoaXMuX2ZpcnN0LG89MDthOylyPmEuX3N0YXJ0VGltZXx8KGEgaW5zdGFuY2VvZiBpP2UhPT0hMSYmKG5bbysrXT1hKToocyE9PSExJiYobltvKytdPWEpLHQhPT0hMSYmKG49bi5jb25jYXQoYS5nZXRDaGlsZHJlbighMCxlLHMpKSxvPW4ubGVuZ3RoKSkpLGE9YS5fbmV4dDtyZXR1cm4gbn0sZi5nZXRUd2VlbnNPZj1mdW5jdGlvbih0LGUpe3ZhciBzLHIsbj10aGlzLl9nYyxhPVtdLG89MDtmb3IobiYmdGhpcy5fZW5hYmxlZCghMCwhMCkscz1pLmdldFR3ZWVuc09mKHQpLHI9cy5sZW5ndGg7LS1yPi0xOykoc1tyXS50aW1lbGluZT09PXRoaXN8fGUmJnRoaXMuX2NvbnRhaW5zKHNbcl0pKSYmKGFbbysrXT1zW3JdKTtyZXR1cm4gbiYmdGhpcy5fZW5hYmxlZCghMSwhMCksYX0sZi5fY29udGFpbnM9ZnVuY3Rpb24odCl7Zm9yKHZhciBlPXQudGltZWxpbmU7ZTspe2lmKGU9PT10aGlzKXJldHVybiEwO2U9ZS50aW1lbGluZX1yZXR1cm4hMX0sZi5zaGlmdENoaWxkcmVuPWZ1bmN0aW9uKHQsZSxpKXtpPWl8fDA7Zm9yKHZhciBzLHI9dGhpcy5fZmlyc3Qsbj10aGlzLl9sYWJlbHM7cjspci5fc3RhcnRUaW1lPj1pJiYoci5fc3RhcnRUaW1lKz10KSxyPXIuX25leHQ7aWYoZSlmb3IocyBpbiBuKW5bc10+PWkmJihuW3NdKz10KTtyZXR1cm4gdGhpcy5fdW5jYWNoZSghMCl9LGYuX2tpbGw9ZnVuY3Rpb24odCxlKXtpZighdCYmIWUpcmV0dXJuIHRoaXMuX2VuYWJsZWQoITEsITEpO2Zvcih2YXIgaT1lP3RoaXMuZ2V0VHdlZW5zT2YoZSk6dGhpcy5nZXRDaGlsZHJlbighMCwhMCwhMSkscz1pLmxlbmd0aCxyPSExOy0tcz4tMTspaVtzXS5fa2lsbCh0LGUpJiYocj0hMCk7cmV0dXJuIHJ9LGYuY2xlYXI9ZnVuY3Rpb24odCl7dmFyIGU9dGhpcy5nZXRDaGlsZHJlbighMSwhMCwhMCksaT1lLmxlbmd0aDtmb3IodGhpcy5fdGltZT10aGlzLl90b3RhbFRpbWU9MDstLWk+LTE7KWVbaV0uX2VuYWJsZWQoITEsITEpO3JldHVybiB0IT09ITEmJih0aGlzLl9sYWJlbHM9e30pLHRoaXMuX3VuY2FjaGUoITApfSxmLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtmb3IodmFyIHQ9dGhpcy5fZmlyc3Q7dDspdC5pbnZhbGlkYXRlKCksdD10Ll9uZXh0O3JldHVybiB0aGlzfSxmLl9lbmFibGVkPWZ1bmN0aW9uKHQsaSl7aWYodD09PXRoaXMuX2djKWZvcih2YXIgcz10aGlzLl9maXJzdDtzOylzLl9lbmFibGVkKHQsITApLHM9cy5fbmV4dDtyZXR1cm4gZS5wcm90b3R5cGUuX2VuYWJsZWQuY2FsbCh0aGlzLHQsaSl9LGYuZHVyYXRpb249ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KDAhPT10aGlzLmR1cmF0aW9uKCkmJjAhPT10JiZ0aGlzLnRpbWVTY2FsZSh0aGlzLl9kdXJhdGlvbi90KSx0aGlzKToodGhpcy5fZGlydHkmJnRoaXMudG90YWxEdXJhdGlvbigpLHRoaXMuX2R1cmF0aW9uKX0sZi50b3RhbER1cmF0aW9uPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXtpZih0aGlzLl9kaXJ0eSl7Zm9yKHZhciBlLGkscz0wLHI9dGhpcy5fbGFzdCxuPTk5OTk5OTk5OTk5OTtyOyllPXIuX3ByZXYsci5fZGlydHkmJnIudG90YWxEdXJhdGlvbigpLHIuX3N0YXJ0VGltZT5uJiZ0aGlzLl9zb3J0Q2hpbGRyZW4mJiFyLl9wYXVzZWQ/dGhpcy5hZGQocixyLl9zdGFydFRpbWUtci5fZGVsYXkpOm49ci5fc3RhcnRUaW1lLDA+ci5fc3RhcnRUaW1lJiYhci5fcGF1c2VkJiYocy09ci5fc3RhcnRUaW1lLHRoaXMuX3RpbWVsaW5lLnNtb290aENoaWxkVGltaW5nJiYodGhpcy5fc3RhcnRUaW1lKz1yLl9zdGFydFRpbWUvdGhpcy5fdGltZVNjYWxlKSx0aGlzLnNoaWZ0Q2hpbGRyZW4oLXIuX3N0YXJ0VGltZSwhMSwtOTk5OTk5OTk5OSksbj0wKSxpPXIuX3N0YXJ0VGltZStyLl90b3RhbER1cmF0aW9uL3IuX3RpbWVTY2FsZSxpPnMmJihzPWkpLHI9ZTt0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXMsdGhpcy5fZGlydHk9ITF9cmV0dXJuIHRoaXMuX3RvdGFsRHVyYXRpb259cmV0dXJuIDAhPT10aGlzLnRvdGFsRHVyYXRpb24oKSYmMCE9PXQmJnRoaXMudGltZVNjYWxlKHRoaXMuX3RvdGFsRHVyYXRpb24vdCksdGhpc30sZi51c2VzRnJhbWVzPWZ1bmN0aW9uKCl7Zm9yKHZhciBlPXRoaXMuX3RpbWVsaW5lO2UuX3RpbWVsaW5lOyllPWUuX3RpbWVsaW5lO3JldHVybiBlPT09dC5fcm9vdEZyYW1lc1RpbWVsaW5lfSxmLnJhd1RpbWU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fcGF1c2VkP3RoaXMuX3RvdGFsVGltZToodGhpcy5fdGltZWxpbmUucmF3VGltZSgpLXRoaXMuX3N0YXJ0VGltZSkqdGhpcy5fdGltZVNjYWxlfSxzfSwhMCl9KSx3aW5kb3cuX2dzRGVmaW5lJiZ3aW5kb3cuX2dzUXVldWUucG9wKCkoKTsiLCIvKiFcclxuICogVkVSU0lPTjogMS4xMi4xXHJcbiAqIERBVEU6IDIwMTQtMDYtMjZcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqL1xyXG4oZnVuY3Rpb24odCl7XCJ1c2Ugc3RyaWN0XCI7dmFyIGU9dC5HcmVlblNvY2tHbG9iYWxzfHx0O2lmKCFlLlR3ZWVuTGl0ZSl7dmFyIGkscyxuLHIsYSxvPWZ1bmN0aW9uKHQpe3ZhciBpLHM9dC5zcGxpdChcIi5cIiksbj1lO2ZvcihpPTA7cy5sZW5ndGg+aTtpKyspbltzW2ldXT1uPW5bc1tpXV18fHt9O3JldHVybiBufSxsPW8oXCJjb20uZ3JlZW5zb2NrXCIpLGg9MWUtMTAsXz1bXS5zbGljZSx1PWZ1bmN0aW9uKCl7fSxtPWZ1bmN0aW9uKCl7dmFyIHQ9T2JqZWN0LnByb3RvdHlwZS50b1N0cmluZyxlPXQuY2FsbChbXSk7cmV0dXJuIGZ1bmN0aW9uKGkpe3JldHVybiBudWxsIT1pJiYoaSBpbnN0YW5jZW9mIEFycmF5fHxcIm9iamVjdFwiPT10eXBlb2YgaSYmISFpLnB1c2gmJnQuY2FsbChpKT09PWUpfX0oKSxmPXt9LHA9ZnVuY3Rpb24oaSxzLG4scil7dGhpcy5zYz1mW2ldP2ZbaV0uc2M6W10sZltpXT10aGlzLHRoaXMuZ3NDbGFzcz1udWxsLHRoaXMuZnVuYz1uO3ZhciBhPVtdO3RoaXMuY2hlY2s9ZnVuY3Rpb24obCl7Zm9yKHZhciBoLF8sdSxtLGM9cy5sZW5ndGgsZD1jOy0tYz4tMTspKGg9ZltzW2NdXXx8bmV3IHAoc1tjXSxbXSkpLmdzQ2xhc3M/KGFbY109aC5nc0NsYXNzLGQtLSk6bCYmaC5zYy5wdXNoKHRoaXMpO2lmKDA9PT1kJiZuKWZvcihfPShcImNvbS5ncmVlbnNvY2suXCIraSkuc3BsaXQoXCIuXCIpLHU9Xy5wb3AoKSxtPW8oXy5qb2luKFwiLlwiKSlbdV09dGhpcy5nc0NsYXNzPW4uYXBwbHkobixhKSxyJiYoZVt1XT1tLFwiZnVuY3Rpb25cIj09dHlwZW9mIGRlZmluZSYmZGVmaW5lLmFtZD9kZWZpbmUoKHQuR3JlZW5Tb2NrQU1EUGF0aD90LkdyZWVuU29ja0FNRFBhdGgrXCIvXCI6XCJcIikraS5zcGxpdChcIi5cIikuam9pbihcIi9cIiksW10sZnVuY3Rpb24oKXtyZXR1cm4gbX0pOlwidW5kZWZpbmVkXCIhPXR5cGVvZiBtb2R1bGUmJm1vZHVsZS5leHBvcnRzJiYobW9kdWxlLmV4cG9ydHM9bSkpLGM9MDt0aGlzLnNjLmxlbmd0aD5jO2MrKyl0aGlzLnNjW2NdLmNoZWNrKCl9LHRoaXMuY2hlY2soITApfSxjPXQuX2dzRGVmaW5lPWZ1bmN0aW9uKHQsZSxpLHMpe3JldHVybiBuZXcgcCh0LGUsaSxzKX0sZD1sLl9jbGFzcz1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIGU9ZXx8ZnVuY3Rpb24oKXt9LGModCxbXSxmdW5jdGlvbigpe3JldHVybiBlfSxpKSxlfTtjLmdsb2JhbHM9ZTt2YXIgdj1bMCwwLDEsMV0sZz1bXSxUPWQoXCJlYXNpbmcuRWFzZVwiLGZ1bmN0aW9uKHQsZSxpLHMpe3RoaXMuX2Z1bmM9dCx0aGlzLl90eXBlPWl8fDAsdGhpcy5fcG93ZXI9c3x8MCx0aGlzLl9wYXJhbXM9ZT92LmNvbmNhdChlKTp2fSwhMCkseT1ULm1hcD17fSx3PVQucmVnaXN0ZXI9ZnVuY3Rpb24odCxlLGkscyl7Zm9yKHZhciBuLHIsYSxvLGg9ZS5zcGxpdChcIixcIiksXz1oLmxlbmd0aCx1PShpfHxcImVhc2VJbixlYXNlT3V0LGVhc2VJbk91dFwiKS5zcGxpdChcIixcIik7LS1fPi0xOylmb3Iocj1oW19dLG49cz9kKFwiZWFzaW5nLlwiK3IsbnVsbCwhMCk6bC5lYXNpbmdbcl18fHt9LGE9dS5sZW5ndGg7LS1hPi0xOylvPXVbYV0seVtyK1wiLlwiK29dPXlbbytyXT1uW29dPXQuZ2V0UmF0aW8/dDp0W29dfHxuZXcgdH07Zm9yKG49VC5wcm90b3R5cGUsbi5fY2FsY0VuZD0hMSxuLmdldFJhdGlvPWZ1bmN0aW9uKHQpe2lmKHRoaXMuX2Z1bmMpcmV0dXJuIHRoaXMuX3BhcmFtc1swXT10LHRoaXMuX2Z1bmMuYXBwbHkobnVsbCx0aGlzLl9wYXJhbXMpO3ZhciBlPXRoaXMuX3R5cGUsaT10aGlzLl9wb3dlcixzPTE9PT1lPzEtdDoyPT09ZT90Oi41PnQ/Mip0OjIqKDEtdCk7cmV0dXJuIDE9PT1pP3MqPXM6Mj09PWk/cyo9cypzOjM9PT1pP3MqPXMqcypzOjQ9PT1pJiYocyo9cypzKnMqcyksMT09PWU/MS1zOjI9PT1lP3M6LjU+dD9zLzI6MS1zLzJ9LGk9W1wiTGluZWFyXCIsXCJRdWFkXCIsXCJDdWJpY1wiLFwiUXVhcnRcIixcIlF1aW50LFN0cm9uZ1wiXSxzPWkubGVuZ3RoOy0tcz4tMTspbj1pW3NdK1wiLFBvd2VyXCIrcyx3KG5ldyBUKG51bGwsbnVsbCwxLHMpLG4sXCJlYXNlT3V0XCIsITApLHcobmV3IFQobnVsbCxudWxsLDIscyksbixcImVhc2VJblwiKygwPT09cz9cIixlYXNlTm9uZVwiOlwiXCIpKSx3KG5ldyBUKG51bGwsbnVsbCwzLHMpLG4sXCJlYXNlSW5PdXRcIik7eS5saW5lYXI9bC5lYXNpbmcuTGluZWFyLmVhc2VJbix5LnN3aW5nPWwuZWFzaW5nLlF1YWQuZWFzZUluT3V0O3ZhciBQPWQoXCJldmVudHMuRXZlbnREaXNwYXRjaGVyXCIsZnVuY3Rpb24odCl7dGhpcy5fbGlzdGVuZXJzPXt9LHRoaXMuX2V2ZW50VGFyZ2V0PXR8fHRoaXN9KTtuPVAucHJvdG90eXBlLG4uYWRkRXZlbnRMaXN0ZW5lcj1mdW5jdGlvbih0LGUsaSxzLG4pe249bnx8MDt2YXIgbyxsLGg9dGhpcy5fbGlzdGVuZXJzW3RdLF89MDtmb3IobnVsbD09aCYmKHRoaXMuX2xpc3RlbmVyc1t0XT1oPVtdKSxsPWgubGVuZ3RoOy0tbD4tMTspbz1oW2xdLG8uYz09PWUmJm8ucz09PWk/aC5zcGxpY2UobCwxKTowPT09XyYmbj5vLnByJiYoXz1sKzEpO2guc3BsaWNlKF8sMCx7YzplLHM6aSx1cDpzLHByOm59KSx0aGlzIT09cnx8YXx8ci53YWtlKCl9LG4ucmVtb3ZlRXZlbnRMaXN0ZW5lcj1mdW5jdGlvbih0LGUpe3ZhciBpLHM9dGhpcy5fbGlzdGVuZXJzW3RdO2lmKHMpZm9yKGk9cy5sZW5ndGg7LS1pPi0xOylpZihzW2ldLmM9PT1lKXJldHVybiBzLnNwbGljZShpLDEpLHZvaWQgMH0sbi5kaXNwYXRjaEV2ZW50PWZ1bmN0aW9uKHQpe3ZhciBlLGkscyxuPXRoaXMuX2xpc3RlbmVyc1t0XTtpZihuKWZvcihlPW4ubGVuZ3RoLGk9dGhpcy5fZXZlbnRUYXJnZXQ7LS1lPi0xOylzPW5bZV0scy51cD9zLmMuY2FsbChzLnN8fGkse3R5cGU6dCx0YXJnZXQ6aX0pOnMuYy5jYWxsKHMuc3x8aSl9O3ZhciBrPXQucmVxdWVzdEFuaW1hdGlvbkZyYW1lLGI9dC5jYW5jZWxBbmltYXRpb25GcmFtZSxBPURhdGUubm93fHxmdW5jdGlvbigpe3JldHVybihuZXcgRGF0ZSkuZ2V0VGltZSgpfSxTPUEoKTtmb3IoaT1bXCJtc1wiLFwibW96XCIsXCJ3ZWJraXRcIixcIm9cIl0scz1pLmxlbmd0aDstLXM+LTEmJiFrOylrPXRbaVtzXStcIlJlcXVlc3RBbmltYXRpb25GcmFtZVwiXSxiPXRbaVtzXStcIkNhbmNlbEFuaW1hdGlvbkZyYW1lXCJdfHx0W2lbc10rXCJDYW5jZWxSZXF1ZXN0QW5pbWF0aW9uRnJhbWVcIl07ZChcIlRpY2tlclwiLGZ1bmN0aW9uKHQsZSl7dmFyIGkscyxuLG8sbCxfPXRoaXMsbT1BKCksZj1lIT09ITEmJmsscD01MDAsYz0zMyxkPWZ1bmN0aW9uKHQpe3ZhciBlLHIsYT1BKCktUzthPnAmJihtKz1hLWMpLFMrPWEsXy50aW1lPShTLW0pLzFlMyxlPV8udGltZS1sLCghaXx8ZT4wfHx0PT09ITApJiYoXy5mcmFtZSsrLGwrPWUrKGU+PW8/LjAwNDpvLWUpLHI9ITApLHQhPT0hMCYmKG49cyhkKSksciYmXy5kaXNwYXRjaEV2ZW50KFwidGlja1wiKX07UC5jYWxsKF8pLF8udGltZT1fLmZyYW1lPTAsXy50aWNrPWZ1bmN0aW9uKCl7ZCghMCl9LF8ubGFnU21vb3RoaW5nPWZ1bmN0aW9uKHQsZSl7cD10fHwxL2gsYz1NYXRoLm1pbihlLHAsMCl9LF8uc2xlZXA9ZnVuY3Rpb24oKXtudWxsIT1uJiYoZiYmYj9iKG4pOmNsZWFyVGltZW91dChuKSxzPXUsbj1udWxsLF89PT1yJiYoYT0hMSkpfSxfLndha2U9ZnVuY3Rpb24oKXtudWxsIT09bj9fLnNsZWVwKCk6Xy5mcmFtZT4xMCYmKFM9QSgpLXArNSkscz0wPT09aT91OmYmJms/azpmdW5jdGlvbih0KXtyZXR1cm4gc2V0VGltZW91dCh0LDB8MWUzKihsLV8udGltZSkrMSl9LF89PT1yJiYoYT0hMCksZCgyKX0sXy5mcHM9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KGk9dCxvPTEvKGl8fDYwKSxsPXRoaXMudGltZStvLF8ud2FrZSgpLHZvaWQgMCk6aX0sXy51c2VSQUY9ZnVuY3Rpb24odCl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/KF8uc2xlZXAoKSxmPXQsXy5mcHMoaSksdm9pZCAwKTpmfSxfLmZwcyh0KSxzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7ZiYmKCFufHw1Pl8uZnJhbWUpJiZfLnVzZVJBRighMSl9LDE1MDApfSksbj1sLlRpY2tlci5wcm90b3R5cGU9bmV3IGwuZXZlbnRzLkV2ZW50RGlzcGF0Y2hlcixuLmNvbnN0cnVjdG9yPWwuVGlja2VyO3ZhciB4PWQoXCJjb3JlLkFuaW1hdGlvblwiLGZ1bmN0aW9uKHQsZSl7aWYodGhpcy52YXJzPWU9ZXx8e30sdGhpcy5fZHVyYXRpb249dGhpcy5fdG90YWxEdXJhdGlvbj10fHwwLHRoaXMuX2RlbGF5PU51bWJlcihlLmRlbGF5KXx8MCx0aGlzLl90aW1lU2NhbGU9MSx0aGlzLl9hY3RpdmU9ZS5pbW1lZGlhdGVSZW5kZXI9PT0hMCx0aGlzLmRhdGE9ZS5kYXRhLHRoaXMuX3JldmVyc2VkPWUucmV2ZXJzZWQ9PT0hMCxCKXthfHxyLndha2UoKTt2YXIgaT10aGlzLnZhcnMudXNlRnJhbWVzP1E6QjtpLmFkZCh0aGlzLGkuX3RpbWUpLHRoaXMudmFycy5wYXVzZWQmJnRoaXMucGF1c2VkKCEwKX19KTtyPXgudGlja2VyPW5ldyBsLlRpY2tlcixuPXgucHJvdG90eXBlLG4uX2RpcnR5PW4uX2djPW4uX2luaXR0ZWQ9bi5fcGF1c2VkPSExLG4uX3RvdGFsVGltZT1uLl90aW1lPTAsbi5fcmF3UHJldlRpbWU9LTEsbi5fbmV4dD1uLl9sYXN0PW4uX29uVXBkYXRlPW4uX3RpbWVsaW5lPW4udGltZWxpbmU9bnVsbCxuLl9wYXVzZWQ9ITE7dmFyIEM9ZnVuY3Rpb24oKXthJiZBKCktUz4yZTMmJnIud2FrZSgpLHNldFRpbWVvdXQoQywyZTMpfTtDKCksbi5wbGF5PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0LGUpLHRoaXMucmV2ZXJzZWQoITEpLnBhdXNlZCghMSl9LG4ucGF1c2U9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbCE9dCYmdGhpcy5zZWVrKHQsZSksdGhpcy5wYXVzZWQoITApfSxuLnJlc3VtZT1mdW5jdGlvbih0LGUpe3JldHVybiBudWxsIT10JiZ0aGlzLnNlZWsodCxlKSx0aGlzLnBhdXNlZCghMSl9LG4uc2Vlaz1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLnRvdGFsVGltZShOdW1iZXIodCksZSE9PSExKX0sbi5yZXN0YXJ0PWZ1bmN0aW9uKHQsZSl7cmV0dXJuIHRoaXMucmV2ZXJzZWQoITEpLnBhdXNlZCghMSkudG90YWxUaW1lKHQ/LXRoaXMuX2RlbGF5OjAsZSE9PSExLCEwKX0sbi5yZXZlcnNlPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIG51bGwhPXQmJnRoaXMuc2Vlayh0fHx0aGlzLnRvdGFsRHVyYXRpb24oKSxlKSx0aGlzLnJldmVyc2VkKCEwKS5wYXVzZWQoITEpfSxuLnJlbmRlcj1mdW5jdGlvbigpe30sbi5pbnZhbGlkYXRlPWZ1bmN0aW9uKCl7cmV0dXJuIHRoaXN9LG4uaXNBY3RpdmU9ZnVuY3Rpb24oKXt2YXIgdCxlPXRoaXMuX3RpbWVsaW5lLGk9dGhpcy5fc3RhcnRUaW1lO3JldHVybiFlfHwhdGhpcy5fZ2MmJiF0aGlzLl9wYXVzZWQmJmUuaXNBY3RpdmUoKSYmKHQ9ZS5yYXdUaW1lKCkpPj1pJiZpK3RoaXMudG90YWxEdXJhdGlvbigpL3RoaXMuX3RpbWVTY2FsZT50fSxuLl9lbmFibGVkPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGF8fHIud2FrZSgpLHRoaXMuX2djPSF0LHRoaXMuX2FjdGl2ZT10aGlzLmlzQWN0aXZlKCksZSE9PSEwJiYodCYmIXRoaXMudGltZWxpbmU/dGhpcy5fdGltZWxpbmUuYWRkKHRoaXMsdGhpcy5fc3RhcnRUaW1lLXRoaXMuX2RlbGF5KTohdCYmdGhpcy50aW1lbGluZSYmdGhpcy5fdGltZWxpbmUuX3JlbW92ZSh0aGlzLCEwKSksITF9LG4uX2tpbGw9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fZW5hYmxlZCghMSwhMSl9LG4ua2lsbD1mdW5jdGlvbih0LGUpe3JldHVybiB0aGlzLl9raWxsKHQsZSksdGhpc30sbi5fdW5jYWNoZT1mdW5jdGlvbih0KXtmb3IodmFyIGU9dD90aGlzOnRoaXMudGltZWxpbmU7ZTspZS5fZGlydHk9ITAsZT1lLnRpbWVsaW5lO3JldHVybiB0aGlzfSxuLl9zd2FwU2VsZkluUGFyYW1zPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10Lmxlbmd0aCxpPXQuY29uY2F0KCk7LS1lPi0xOylcIntzZWxmfVwiPT09dFtlXSYmKGlbZV09dGhpcyk7cmV0dXJuIGl9LG4uZXZlbnRDYWxsYmFjaz1mdW5jdGlvbih0LGUsaSxzKXtpZihcIm9uXCI9PT0odHx8XCJcIikuc3Vic3RyKDAsMikpe3ZhciBuPXRoaXMudmFycztpZigxPT09YXJndW1lbnRzLmxlbmd0aClyZXR1cm4gblt0XTtudWxsPT1lP2RlbGV0ZSBuW3RdOihuW3RdPWUsblt0K1wiUGFyYW1zXCJdPW0oaSkmJi0xIT09aS5qb2luKFwiXCIpLmluZGV4T2YoXCJ7c2VsZn1cIik/dGhpcy5fc3dhcFNlbGZJblBhcmFtcyhpKTppLG5bdCtcIlNjb3BlXCJdPXMpLFwib25VcGRhdGVcIj09PXQmJih0aGlzLl9vblVwZGF0ZT1lKX1yZXR1cm4gdGhpc30sbi5kZWxheT1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJnRoaXMuc3RhcnRUaW1lKHRoaXMuX3N0YXJ0VGltZSt0LXRoaXMuX2RlbGF5KSx0aGlzLl9kZWxheT10LHRoaXMpOnRoaXMuX2RlbGF5fSxuLmR1cmF0aW9uPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl9kdXJhdGlvbj10aGlzLl90b3RhbER1cmF0aW9uPXQsdGhpcy5fdW5jYWNoZSghMCksdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmcmJnRoaXMuX3RpbWU+MCYmdGhpcy5fdGltZTx0aGlzLl9kdXJhdGlvbiYmMCE9PXQmJnRoaXMudG90YWxUaW1lKHRoaXMuX3RvdGFsVGltZSoodC90aGlzLl9kdXJhdGlvbiksITApLHRoaXMpOih0aGlzLl9kaXJ0eT0hMSx0aGlzLl9kdXJhdGlvbil9LG4udG90YWxEdXJhdGlvbj1mdW5jdGlvbih0KXtyZXR1cm4gdGhpcy5fZGlydHk9ITEsYXJndW1lbnRzLmxlbmd0aD90aGlzLmR1cmF0aW9uKHQpOnRoaXMuX3RvdGFsRHVyYXRpb259LG4udGltZT1mdW5jdGlvbih0LGUpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0aGlzLl9kaXJ0eSYmdGhpcy50b3RhbER1cmF0aW9uKCksdGhpcy50b3RhbFRpbWUodD50aGlzLl9kdXJhdGlvbj90aGlzLl9kdXJhdGlvbjp0LGUpKTp0aGlzLl90aW1lfSxuLnRvdGFsVGltZT1mdW5jdGlvbih0LGUsaSl7aWYoYXx8ci53YWtlKCksIWFyZ3VtZW50cy5sZW5ndGgpcmV0dXJuIHRoaXMuX3RvdGFsVGltZTtpZih0aGlzLl90aW1lbGluZSl7aWYoMD50JiYhaSYmKHQrPXRoaXMudG90YWxEdXJhdGlvbigpKSx0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyl7dGhpcy5fZGlydHkmJnRoaXMudG90YWxEdXJhdGlvbigpO3ZhciBzPXRoaXMuX3RvdGFsRHVyYXRpb24sbj10aGlzLl90aW1lbGluZTtpZih0PnMmJiFpJiYodD1zKSx0aGlzLl9zdGFydFRpbWU9KHRoaXMuX3BhdXNlZD90aGlzLl9wYXVzZVRpbWU6bi5fdGltZSktKHRoaXMuX3JldmVyc2VkP3MtdDp0KS90aGlzLl90aW1lU2NhbGUsbi5fZGlydHl8fHRoaXMuX3VuY2FjaGUoITEpLG4uX3RpbWVsaW5lKWZvcig7bi5fdGltZWxpbmU7KW4uX3RpbWVsaW5lLl90aW1lIT09KG4uX3N0YXJ0VGltZStuLl90b3RhbFRpbWUpL24uX3RpbWVTY2FsZSYmbi50b3RhbFRpbWUobi5fdG90YWxUaW1lLCEwKSxuPW4uX3RpbWVsaW5lfXRoaXMuX2djJiZ0aGlzLl9lbmFibGVkKCEwLCExKSwodGhpcy5fdG90YWxUaW1lIT09dHx8MD09PXRoaXMuX2R1cmF0aW9uKSYmKHRoaXMucmVuZGVyKHQsZSwhMSksei5sZW5ndGgmJnEoKSl9cmV0dXJuIHRoaXN9LG4ucHJvZ3Jlc3M9bi50b3RhbFByb2dyZXNzPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGFyZ3VtZW50cy5sZW5ndGg/dGhpcy50b3RhbFRpbWUodGhpcy5kdXJhdGlvbigpKnQsZSk6dGhpcy5fdGltZS90aGlzLmR1cmF0aW9uKCl9LG4uc3RhcnRUaW1lPWZ1bmN0aW9uKHQpe3JldHVybiBhcmd1bWVudHMubGVuZ3RoPyh0IT09dGhpcy5fc3RhcnRUaW1lJiYodGhpcy5fc3RhcnRUaW1lPXQsdGhpcy50aW1lbGluZSYmdGhpcy50aW1lbGluZS5fc29ydENoaWxkcmVuJiZ0aGlzLnRpbWVsaW5lLmFkZCh0aGlzLHQtdGhpcy5fZGVsYXkpKSx0aGlzKTp0aGlzLl9zdGFydFRpbWV9LG4udGltZVNjYWxlPWZ1bmN0aW9uKHQpe2lmKCFhcmd1bWVudHMubGVuZ3RoKXJldHVybiB0aGlzLl90aW1lU2NhbGU7aWYodD10fHxoLHRoaXMuX3RpbWVsaW5lJiZ0aGlzLl90aW1lbGluZS5zbW9vdGhDaGlsZFRpbWluZyl7dmFyIGU9dGhpcy5fcGF1c2VUaW1lLGk9ZXx8MD09PWU/ZTp0aGlzLl90aW1lbGluZS50b3RhbFRpbWUoKTt0aGlzLl9zdGFydFRpbWU9aS0oaS10aGlzLl9zdGFydFRpbWUpKnRoaXMuX3RpbWVTY2FsZS90fXJldHVybiB0aGlzLl90aW1lU2NhbGU9dCx0aGlzLl91bmNhY2hlKCExKX0sbi5yZXZlcnNlZD1mdW5jdGlvbih0KXtyZXR1cm4gYXJndW1lbnRzLmxlbmd0aD8odCE9dGhpcy5fcmV2ZXJzZWQmJih0aGlzLl9yZXZlcnNlZD10LHRoaXMudG90YWxUaW1lKHRoaXMuX3RpbWVsaW5lJiYhdGhpcy5fdGltZWxpbmUuc21vb3RoQ2hpbGRUaW1pbmc/dGhpcy50b3RhbER1cmF0aW9uKCktdGhpcy5fdG90YWxUaW1lOnRoaXMuX3RvdGFsVGltZSwhMCkpLHRoaXMpOnRoaXMuX3JldmVyc2VkfSxuLnBhdXNlZD1mdW5jdGlvbih0KXtpZighYXJndW1lbnRzLmxlbmd0aClyZXR1cm4gdGhpcy5fcGF1c2VkO2lmKHQhPXRoaXMuX3BhdXNlZCYmdGhpcy5fdGltZWxpbmUpe2F8fHR8fHIud2FrZSgpO3ZhciBlPXRoaXMuX3RpbWVsaW5lLGk9ZS5yYXdUaW1lKCkscz1pLXRoaXMuX3BhdXNlVGltZTshdCYmZS5zbW9vdGhDaGlsZFRpbWluZyYmKHRoaXMuX3N0YXJ0VGltZSs9cyx0aGlzLl91bmNhY2hlKCExKSksdGhpcy5fcGF1c2VUaW1lPXQ/aTpudWxsLHRoaXMuX3BhdXNlZD10LHRoaXMuX2FjdGl2ZT10aGlzLmlzQWN0aXZlKCksIXQmJjAhPT1zJiZ0aGlzLl9pbml0dGVkJiZ0aGlzLmR1cmF0aW9uKCkmJnRoaXMucmVuZGVyKGUuc21vb3RoQ2hpbGRUaW1pbmc/dGhpcy5fdG90YWxUaW1lOihpLXRoaXMuX3N0YXJ0VGltZSkvdGhpcy5fdGltZVNjYWxlLCEwLCEwKX1yZXR1cm4gdGhpcy5fZ2MmJiF0JiZ0aGlzLl9lbmFibGVkKCEwLCExKSx0aGlzfTt2YXIgUj1kKFwiY29yZS5TaW1wbGVUaW1lbGluZVwiLGZ1bmN0aW9uKHQpe3guY2FsbCh0aGlzLDAsdCksdGhpcy5hdXRvUmVtb3ZlQ2hpbGRyZW49dGhpcy5zbW9vdGhDaGlsZFRpbWluZz0hMH0pO249Ui5wcm90b3R5cGU9bmV3IHgsbi5jb25zdHJ1Y3Rvcj1SLG4ua2lsbCgpLl9nYz0hMSxuLl9maXJzdD1uLl9sYXN0PW51bGwsbi5fc29ydENoaWxkcmVuPSExLG4uYWRkPW4uaW5zZXJ0PWZ1bmN0aW9uKHQsZSl7dmFyIGkscztpZih0Ll9zdGFydFRpbWU9TnVtYmVyKGV8fDApK3QuX2RlbGF5LHQuX3BhdXNlZCYmdGhpcyE9PXQuX3RpbWVsaW5lJiYodC5fcGF1c2VUaW1lPXQuX3N0YXJ0VGltZSsodGhpcy5yYXdUaW1lKCktdC5fc3RhcnRUaW1lKS90Ll90aW1lU2NhbGUpLHQudGltZWxpbmUmJnQudGltZWxpbmUuX3JlbW92ZSh0LCEwKSx0LnRpbWVsaW5lPXQuX3RpbWVsaW5lPXRoaXMsdC5fZ2MmJnQuX2VuYWJsZWQoITAsITApLGk9dGhpcy5fbGFzdCx0aGlzLl9zb3J0Q2hpbGRyZW4pZm9yKHM9dC5fc3RhcnRUaW1lO2kmJmkuX3N0YXJ0VGltZT5zOylpPWkuX3ByZXY7cmV0dXJuIGk/KHQuX25leHQ9aS5fbmV4dCxpLl9uZXh0PXQpOih0Ll9uZXh0PXRoaXMuX2ZpcnN0LHRoaXMuX2ZpcnN0PXQpLHQuX25leHQ/dC5fbmV4dC5fcHJldj10OnRoaXMuX2xhc3Q9dCx0Ll9wcmV2PWksdGhpcy5fdGltZWxpbmUmJnRoaXMuX3VuY2FjaGUoITApLHRoaXN9LG4uX3JlbW92ZT1mdW5jdGlvbih0LGUpe3JldHVybiB0LnRpbWVsaW5lPT09dGhpcyYmKGV8fHQuX2VuYWJsZWQoITEsITApLHQudGltZWxpbmU9bnVsbCx0Ll9wcmV2P3QuX3ByZXYuX25leHQ9dC5fbmV4dDp0aGlzLl9maXJzdD09PXQmJih0aGlzLl9maXJzdD10Ll9uZXh0KSx0Ll9uZXh0P3QuX25leHQuX3ByZXY9dC5fcHJldjp0aGlzLl9sYXN0PT09dCYmKHRoaXMuX2xhc3Q9dC5fcHJldiksdGhpcy5fdGltZWxpbmUmJnRoaXMuX3VuY2FjaGUoITApKSx0aGlzfSxuLnJlbmRlcj1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbj10aGlzLl9maXJzdDtmb3IodGhpcy5fdG90YWxUaW1lPXRoaXMuX3RpbWU9dGhpcy5fcmF3UHJldlRpbWU9dDtuOylzPW4uX25leHQsKG4uX2FjdGl2ZXx8dD49bi5fc3RhcnRUaW1lJiYhbi5fcGF1c2VkKSYmKG4uX3JldmVyc2VkP24ucmVuZGVyKChuLl9kaXJ0eT9uLnRvdGFsRHVyYXRpb24oKTpuLl90b3RhbER1cmF0aW9uKS0odC1uLl9zdGFydFRpbWUpKm4uX3RpbWVTY2FsZSxlLGkpOm4ucmVuZGVyKCh0LW4uX3N0YXJ0VGltZSkqbi5fdGltZVNjYWxlLGUsaSkpLG49c30sbi5yYXdUaW1lPWZ1bmN0aW9uKCl7cmV0dXJuIGF8fHIud2FrZSgpLHRoaXMuX3RvdGFsVGltZX07dmFyIEQ9ZChcIlR3ZWVuTGl0ZVwiLGZ1bmN0aW9uKGUsaSxzKXtpZih4LmNhbGwodGhpcyxpLHMpLHRoaXMucmVuZGVyPUQucHJvdG90eXBlLnJlbmRlcixudWxsPT1lKXRocm93XCJDYW5ub3QgdHdlZW4gYSBudWxsIHRhcmdldC5cIjt0aGlzLnRhcmdldD1lPVwic3RyaW5nXCIhPXR5cGVvZiBlP2U6RC5zZWxlY3RvcihlKXx8ZTt2YXIgbixyLGEsbz1lLmpxdWVyeXx8ZS5sZW5ndGgmJmUhPT10JiZlWzBdJiYoZVswXT09PXR8fGVbMF0ubm9kZVR5cGUmJmVbMF0uc3R5bGUmJiFlLm5vZGVUeXBlKSxsPXRoaXMudmFycy5vdmVyd3JpdGU7aWYodGhpcy5fb3ZlcndyaXRlPWw9bnVsbD09bD9HW0QuZGVmYXVsdE92ZXJ3cml0ZV06XCJudW1iZXJcIj09dHlwZW9mIGw/bD4+MDpHW2xdLChvfHxlIGluc3RhbmNlb2YgQXJyYXl8fGUucHVzaCYmbShlKSkmJlwibnVtYmVyXCIhPXR5cGVvZiBlWzBdKWZvcih0aGlzLl90YXJnZXRzPWE9Xy5jYWxsKGUsMCksdGhpcy5fcHJvcExvb2t1cD1bXSx0aGlzLl9zaWJsaW5ncz1bXSxuPTA7YS5sZW5ndGg+bjtuKyspcj1hW25dLHI/XCJzdHJpbmdcIiE9dHlwZW9mIHI/ci5sZW5ndGgmJnIhPT10JiZyWzBdJiYoclswXT09PXR8fHJbMF0ubm9kZVR5cGUmJnJbMF0uc3R5bGUmJiFyLm5vZGVUeXBlKT8oYS5zcGxpY2Uobi0tLDEpLHRoaXMuX3RhcmdldHM9YT1hLmNvbmNhdChfLmNhbGwociwwKSkpOih0aGlzLl9zaWJsaW5nc1tuXT1NKHIsdGhpcywhMSksMT09PWwmJnRoaXMuX3NpYmxpbmdzW25dLmxlbmd0aD4xJiYkKHIsdGhpcyxudWxsLDEsdGhpcy5fc2libGluZ3Nbbl0pKToocj1hW24tLV09RC5zZWxlY3RvcihyKSxcInN0cmluZ1wiPT10eXBlb2YgciYmYS5zcGxpY2UobisxLDEpKTphLnNwbGljZShuLS0sMSk7ZWxzZSB0aGlzLl9wcm9wTG9va3VwPXt9LHRoaXMuX3NpYmxpbmdzPU0oZSx0aGlzLCExKSwxPT09bCYmdGhpcy5fc2libGluZ3MubGVuZ3RoPjEmJiQoZSx0aGlzLG51bGwsMSx0aGlzLl9zaWJsaW5ncyk7KHRoaXMudmFycy5pbW1lZGlhdGVSZW5kZXJ8fDA9PT1pJiYwPT09dGhpcy5fZGVsYXkmJnRoaXMudmFycy5pbW1lZGlhdGVSZW5kZXIhPT0hMSkmJih0aGlzLl90aW1lPS1oLHRoaXMucmVuZGVyKC10aGlzLl9kZWxheSkpfSwhMCksST1mdW5jdGlvbihlKXtyZXR1cm4gZS5sZW5ndGgmJmUhPT10JiZlWzBdJiYoZVswXT09PXR8fGVbMF0ubm9kZVR5cGUmJmVbMF0uc3R5bGUmJiFlLm5vZGVUeXBlKX0sRT1mdW5jdGlvbih0LGUpe3ZhciBpLHM9e307Zm9yKGkgaW4gdClqW2ldfHxpIGluIGUmJlwidHJhbnNmb3JtXCIhPT1pJiZcInhcIiE9PWkmJlwieVwiIT09aSYmXCJ3aWR0aFwiIT09aSYmXCJoZWlnaHRcIiE9PWkmJlwiY2xhc3NOYW1lXCIhPT1pJiZcImJvcmRlclwiIT09aXx8ISghTFtpXXx8TFtpXSYmTFtpXS5fYXV0b0NTUyl8fChzW2ldPXRbaV0sZGVsZXRlIHRbaV0pO3QuY3NzPXN9O249RC5wcm90b3R5cGU9bmV3IHgsbi5jb25zdHJ1Y3Rvcj1ELG4ua2lsbCgpLl9nYz0hMSxuLnJhdGlvPTAsbi5fZmlyc3RQVD1uLl90YXJnZXRzPW4uX292ZXJ3cml0dGVuUHJvcHM9bi5fc3RhcnRBdD1udWxsLG4uX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9bi5fbGF6eT0hMSxELnZlcnNpb249XCIxLjEyLjFcIixELmRlZmF1bHRFYXNlPW4uX2Vhc2U9bmV3IFQobnVsbCxudWxsLDEsMSksRC5kZWZhdWx0T3ZlcndyaXRlPVwiYXV0b1wiLEQudGlja2VyPXIsRC5hdXRvU2xlZXA9ITAsRC5sYWdTbW9vdGhpbmc9ZnVuY3Rpb24odCxlKXtyLmxhZ1Ntb290aGluZyh0LGUpfSxELnNlbGVjdG9yPXQuJHx8dC5qUXVlcnl8fGZ1bmN0aW9uKGUpe3JldHVybiB0LiQ/KEQuc2VsZWN0b3I9dC4kLHQuJChlKSk6dC5kb2N1bWVudD90LmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFwiI1wiPT09ZS5jaGFyQXQoMCk/ZS5zdWJzdHIoMSk6ZSk6ZX07dmFyIHo9W10sTz17fSxOPUQuX2ludGVybmFscz17aXNBcnJheTptLGlzU2VsZWN0b3I6SSxsYXp5VHdlZW5zOnp9LEw9RC5fcGx1Z2lucz17fSxVPU4udHdlZW5Mb29rdXA9e30sRj0wLGo9Ti5yZXNlcnZlZFByb3BzPXtlYXNlOjEsZGVsYXk6MSxvdmVyd3JpdGU6MSxvbkNvbXBsZXRlOjEsb25Db21wbGV0ZVBhcmFtczoxLG9uQ29tcGxldGVTY29wZToxLHVzZUZyYW1lczoxLHJ1bkJhY2t3YXJkczoxLHN0YXJ0QXQ6MSxvblVwZGF0ZToxLG9uVXBkYXRlUGFyYW1zOjEsb25VcGRhdGVTY29wZToxLG9uU3RhcnQ6MSxvblN0YXJ0UGFyYW1zOjEsb25TdGFydFNjb3BlOjEsb25SZXZlcnNlQ29tcGxldGU6MSxvblJldmVyc2VDb21wbGV0ZVBhcmFtczoxLG9uUmV2ZXJzZUNvbXBsZXRlU2NvcGU6MSxvblJlcGVhdDoxLG9uUmVwZWF0UGFyYW1zOjEsb25SZXBlYXRTY29wZToxLGVhc2VQYXJhbXM6MSx5b3lvOjEsaW1tZWRpYXRlUmVuZGVyOjEscmVwZWF0OjEscmVwZWF0RGVsYXk6MSxkYXRhOjEscGF1c2VkOjEscmV2ZXJzZWQ6MSxhdXRvQ1NTOjEsbGF6eToxfSxHPXtub25lOjAsYWxsOjEsYXV0bzoyLGNvbmN1cnJlbnQ6MyxhbGxPblN0YXJ0OjQscHJlZXhpc3Rpbmc6NSxcInRydWVcIjoxLFwiZmFsc2VcIjowfSxRPXguX3Jvb3RGcmFtZXNUaW1lbGluZT1uZXcgUixCPXguX3Jvb3RUaW1lbGluZT1uZXcgUixxPWZ1bmN0aW9uKCl7dmFyIHQ9ei5sZW5ndGg7Zm9yKE89e307LS10Pi0xOylpPXpbdF0saSYmaS5fbGF6eSE9PSExJiYoaS5yZW5kZXIoaS5fbGF6eSwhMSwhMCksaS5fbGF6eT0hMSk7ei5sZW5ndGg9MH07Qi5fc3RhcnRUaW1lPXIudGltZSxRLl9zdGFydFRpbWU9ci5mcmFtZSxCLl9hY3RpdmU9US5fYWN0aXZlPSEwLHNldFRpbWVvdXQocSwxKSx4Ll91cGRhdGVSb290PUQucmVuZGVyPWZ1bmN0aW9uKCl7dmFyIHQsZSxpO2lmKHoubGVuZ3RoJiZxKCksQi5yZW5kZXIoKHIudGltZS1CLl9zdGFydFRpbWUpKkIuX3RpbWVTY2FsZSwhMSwhMSksUS5yZW5kZXIoKHIuZnJhbWUtUS5fc3RhcnRUaW1lKSpRLl90aW1lU2NhbGUsITEsITEpLHoubGVuZ3RoJiZxKCksIShyLmZyYW1lJTEyMCkpe2ZvcihpIGluIFUpe2ZvcihlPVVbaV0udHdlZW5zLHQ9ZS5sZW5ndGg7LS10Pi0xOyllW3RdLl9nYyYmZS5zcGxpY2UodCwxKTswPT09ZS5sZW5ndGgmJmRlbGV0ZSBVW2ldfWlmKGk9Qi5fZmlyc3QsKCFpfHxpLl9wYXVzZWQpJiZELmF1dG9TbGVlcCYmIVEuX2ZpcnN0JiYxPT09ci5fbGlzdGVuZXJzLnRpY2subGVuZ3RoKXtmb3IoO2kmJmkuX3BhdXNlZDspaT1pLl9uZXh0O2l8fHIuc2xlZXAoKX19fSxyLmFkZEV2ZW50TGlzdGVuZXIoXCJ0aWNrXCIseC5fdXBkYXRlUm9vdCk7dmFyIE09ZnVuY3Rpb24odCxlLGkpe3ZhciBzLG4scj10Ll9nc1R3ZWVuSUQ7aWYoVVtyfHwodC5fZ3NUd2VlbklEPXI9XCJ0XCIrRisrKV18fChVW3JdPXt0YXJnZXQ6dCx0d2VlbnM6W119KSxlJiYocz1VW3JdLnR3ZWVucyxzW249cy5sZW5ndGhdPWUsaSkpZm9yKDstLW4+LTE7KXNbbl09PT1lJiZzLnNwbGljZShuLDEpO3JldHVybiBVW3JdLnR3ZWVuc30sJD1mdW5jdGlvbih0LGUsaSxzLG4pe3ZhciByLGEsbyxsO2lmKDE9PT1zfHxzPj00KXtmb3IobD1uLmxlbmd0aCxyPTA7bD5yO3IrKylpZigobz1uW3JdKSE9PWUpby5fZ2N8fG8uX2VuYWJsZWQoITEsITEpJiYoYT0hMCk7ZWxzZSBpZig1PT09cylicmVhaztyZXR1cm4gYX12YXIgXyx1PWUuX3N0YXJ0VGltZStoLG09W10sZj0wLHA9MD09PWUuX2R1cmF0aW9uO2ZvcihyPW4ubGVuZ3RoOy0tcj4tMTspKG89bltyXSk9PT1lfHxvLl9nY3x8by5fcGF1c2VkfHwoby5fdGltZWxpbmUhPT1lLl90aW1lbGluZT8oXz1ffHxLKGUsMCxwKSwwPT09SyhvLF8scCkmJihtW2YrK109bykpOnU+PW8uX3N0YXJ0VGltZSYmby5fc3RhcnRUaW1lK28udG90YWxEdXJhdGlvbigpL28uX3RpbWVTY2FsZT51JiYoKHB8fCFvLl9pbml0dGVkKSYmMmUtMTA+PXUtby5fc3RhcnRUaW1lfHwobVtmKytdPW8pKSk7Zm9yKHI9ZjstLXI+LTE7KW89bVtyXSwyPT09cyYmby5fa2lsbChpLHQpJiYoYT0hMCksKDIhPT1zfHwhby5fZmlyc3RQVCYmby5faW5pdHRlZCkmJm8uX2VuYWJsZWQoITEsITEpJiYoYT0hMCk7cmV0dXJuIGF9LEs9ZnVuY3Rpb24odCxlLGkpe2Zvcih2YXIgcz10Ll90aW1lbGluZSxuPXMuX3RpbWVTY2FsZSxyPXQuX3N0YXJ0VGltZTtzLl90aW1lbGluZTspe2lmKHIrPXMuX3N0YXJ0VGltZSxuKj1zLl90aW1lU2NhbGUscy5fcGF1c2VkKXJldHVybi0xMDA7cz1zLl90aW1lbGluZX1yZXR1cm4gci89bixyPmU/ci1lOmkmJnI9PT1lfHwhdC5faW5pdHRlZCYmMipoPnItZT9oOihyKz10LnRvdGFsRHVyYXRpb24oKS90Ll90aW1lU2NhbGUvbik+ZStoPzA6ci1lLWh9O24uX2luaXQ9ZnVuY3Rpb24oKXt2YXIgdCxlLGkscyxuLHI9dGhpcy52YXJzLGE9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcyxvPXRoaXMuX2R1cmF0aW9uLGw9ISFyLmltbWVkaWF0ZVJlbmRlcixoPXIuZWFzZTtpZihyLnN0YXJ0QXQpe3RoaXMuX3N0YXJ0QXQmJih0aGlzLl9zdGFydEF0LnJlbmRlcigtMSwhMCksdGhpcy5fc3RhcnRBdC5raWxsKCkpLG49e307Zm9yKHMgaW4gci5zdGFydEF0KW5bc109ci5zdGFydEF0W3NdO2lmKG4ub3ZlcndyaXRlPSExLG4uaW1tZWRpYXRlUmVuZGVyPSEwLG4ubGF6eT1sJiZyLmxhenkhPT0hMSxuLnN0YXJ0QXQ9bi5kZWxheT1udWxsLHRoaXMuX3N0YXJ0QXQ9RC50byh0aGlzLnRhcmdldCwwLG4pLGwpaWYodGhpcy5fdGltZT4wKXRoaXMuX3N0YXJ0QXQ9bnVsbDtlbHNlIGlmKDAhPT1vKXJldHVybn1lbHNlIGlmKHIucnVuQmFja3dhcmRzJiYwIT09bylpZih0aGlzLl9zdGFydEF0KXRoaXMuX3N0YXJ0QXQucmVuZGVyKC0xLCEwKSx0aGlzLl9zdGFydEF0LmtpbGwoKSx0aGlzLl9zdGFydEF0PW51bGw7ZWxzZXtpPXt9O2ZvcihzIGluIHIpaltzXSYmXCJhdXRvQ1NTXCIhPT1zfHwoaVtzXT1yW3NdKTtpZihpLm92ZXJ3cml0ZT0wLGkuZGF0YT1cImlzRnJvbVN0YXJ0XCIsaS5sYXp5PWwmJnIubGF6eSE9PSExLGkuaW1tZWRpYXRlUmVuZGVyPWwsdGhpcy5fc3RhcnRBdD1ELnRvKHRoaXMudGFyZ2V0LDAsaSksbCl7aWYoMD09PXRoaXMuX3RpbWUpcmV0dXJufWVsc2UgdGhpcy5fc3RhcnRBdC5faW5pdCgpLHRoaXMuX3N0YXJ0QXQuX2VuYWJsZWQoITEpfWlmKHRoaXMuX2Vhc2U9aD9oIGluc3RhbmNlb2YgVD9yLmVhc2VQYXJhbXMgaW5zdGFuY2VvZiBBcnJheT9oLmNvbmZpZy5hcHBseShoLHIuZWFzZVBhcmFtcyk6aDpcImZ1bmN0aW9uXCI9PXR5cGVvZiBoP25ldyBUKGgsci5lYXNlUGFyYW1zKTp5W2hdfHxELmRlZmF1bHRFYXNlOkQuZGVmYXVsdEVhc2UsdGhpcy5fZWFzZVR5cGU9dGhpcy5fZWFzZS5fdHlwZSx0aGlzLl9lYXNlUG93ZXI9dGhpcy5fZWFzZS5fcG93ZXIsdGhpcy5fZmlyc3RQVD1udWxsLHRoaXMuX3RhcmdldHMpZm9yKHQ9dGhpcy5fdGFyZ2V0cy5sZW5ndGg7LS10Pi0xOyl0aGlzLl9pbml0UHJvcHModGhpcy5fdGFyZ2V0c1t0XSx0aGlzLl9wcm9wTG9va3VwW3RdPXt9LHRoaXMuX3NpYmxpbmdzW3RdLGE/YVt0XTpudWxsKSYmKGU9ITApO2Vsc2UgZT10aGlzLl9pbml0UHJvcHModGhpcy50YXJnZXQsdGhpcy5fcHJvcExvb2t1cCx0aGlzLl9zaWJsaW5ncyxhKTtpZihlJiZELl9vblBsdWdpbkV2ZW50KFwiX29uSW5pdEFsbFByb3BzXCIsdGhpcyksYSYmKHRoaXMuX2ZpcnN0UFR8fFwiZnVuY3Rpb25cIiE9dHlwZW9mIHRoaXMudGFyZ2V0JiZ0aGlzLl9lbmFibGVkKCExLCExKSksci5ydW5CYWNrd2FyZHMpZm9yKGk9dGhpcy5fZmlyc3RQVDtpOylpLnMrPWkuYyxpLmM9LWkuYyxpPWkuX25leHQ7dGhpcy5fb25VcGRhdGU9ci5vblVwZGF0ZSx0aGlzLl9pbml0dGVkPSEwfSxuLl9pbml0UHJvcHM9ZnVuY3Rpb24oZSxpLHMsbil7dmFyIHIsYSxvLGwsaCxfO2lmKG51bGw9PWUpcmV0dXJuITE7T1tlLl9nc1R3ZWVuSURdJiZxKCksdGhpcy52YXJzLmNzc3x8ZS5zdHlsZSYmZSE9PXQmJmUubm9kZVR5cGUmJkwuY3NzJiZ0aGlzLnZhcnMuYXV0b0NTUyE9PSExJiZFKHRoaXMudmFycyxlKTtmb3IociBpbiB0aGlzLnZhcnMpe2lmKF89dGhpcy52YXJzW3JdLGpbcl0pXyYmKF8gaW5zdGFuY2VvZiBBcnJheXx8Xy5wdXNoJiZtKF8pKSYmLTEhPT1fLmpvaW4oXCJcIikuaW5kZXhPZihcIntzZWxmfVwiKSYmKHRoaXMudmFyc1tyXT1fPXRoaXMuX3N3YXBTZWxmSW5QYXJhbXMoXyx0aGlzKSk7ZWxzZSBpZihMW3JdJiYobD1uZXcgTFtyXSkuX29uSW5pdFR3ZWVuKGUsdGhpcy52YXJzW3JdLHRoaXMpKXtmb3IodGhpcy5fZmlyc3RQVD1oPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6bCxwOlwic2V0UmF0aW9cIixzOjAsYzoxLGY6ITAsbjpyLHBnOiEwLHByOmwuX3ByaW9yaXR5fSxhPWwuX292ZXJ3cml0ZVByb3BzLmxlbmd0aDstLWE+LTE7KWlbbC5fb3ZlcndyaXRlUHJvcHNbYV1dPXRoaXMuX2ZpcnN0UFQ7KGwuX3ByaW9yaXR5fHxsLl9vbkluaXRBbGxQcm9wcykmJihvPSEwKSwobC5fb25EaXNhYmxlfHxsLl9vbkVuYWJsZSkmJih0aGlzLl9ub3RpZnlQbHVnaW5zT2ZFbmFibGVkPSEwKX1lbHNlIHRoaXMuX2ZpcnN0UFQ9aVtyXT1oPXtfbmV4dDp0aGlzLl9maXJzdFBULHQ6ZSxwOnIsZjpcImZ1bmN0aW9uXCI9PXR5cGVvZiBlW3JdLG46cixwZzohMSxwcjowfSxoLnM9aC5mP2Vbci5pbmRleE9mKFwic2V0XCIpfHxcImZ1bmN0aW9uXCIhPXR5cGVvZiBlW1wiZ2V0XCIrci5zdWJzdHIoMyldP3I6XCJnZXRcIityLnN1YnN0cigzKV0oKTpwYXJzZUZsb2F0KGVbcl0pLGguYz1cInN0cmluZ1wiPT10eXBlb2YgXyYmXCI9XCI9PT1fLmNoYXJBdCgxKT9wYXJzZUludChfLmNoYXJBdCgwKStcIjFcIiwxMCkqTnVtYmVyKF8uc3Vic3RyKDIpKTpOdW1iZXIoXyktaC5zfHwwO2gmJmguX25leHQmJihoLl9uZXh0Ll9wcmV2PWgpfXJldHVybiBuJiZ0aGlzLl9raWxsKG4sZSk/dGhpcy5faW5pdFByb3BzKGUsaSxzLG4pOnRoaXMuX292ZXJ3cml0ZT4xJiZ0aGlzLl9maXJzdFBUJiZzLmxlbmd0aD4xJiYkKGUsdGhpcyxpLHRoaXMuX292ZXJ3cml0ZSxzKT8odGhpcy5fa2lsbChpLGUpLHRoaXMuX2luaXRQcm9wcyhlLGkscyxuKSk6KHRoaXMuX2ZpcnN0UFQmJih0aGlzLnZhcnMubGF6eSE9PSExJiZ0aGlzLl9kdXJhdGlvbnx8dGhpcy52YXJzLmxhenkmJiF0aGlzLl9kdXJhdGlvbikmJihPW2UuX2dzVHdlZW5JRF09ITApLG8pfSxuLnJlbmRlcj1mdW5jdGlvbih0LGUsaSl7dmFyIHMsbixyLGEsbz10aGlzLl90aW1lLGw9dGhpcy5fZHVyYXRpb24sXz10aGlzLl9yYXdQcmV2VGltZTtpZih0Pj1sKXRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPWwsdGhpcy5yYXRpbz10aGlzLl9lYXNlLl9jYWxjRW5kP3RoaXMuX2Vhc2UuZ2V0UmF0aW8oMSk6MSx0aGlzLl9yZXZlcnNlZHx8KHM9ITAsbj1cIm9uQ29tcGxldGVcIiksMD09PWwmJih0aGlzLl9pbml0dGVkfHwhdGhpcy52YXJzLmxhenl8fGkpJiYodGhpcy5fc3RhcnRUaW1lPT09dGhpcy5fdGltZWxpbmUuX2R1cmF0aW9uJiYodD0wKSwoMD09PXR8fDA+X3x8Xz09PWgpJiZfIT09dCYmKGk9ITAsXz5oJiYobj1cIm9uUmV2ZXJzZUNvbXBsZXRlXCIpKSx0aGlzLl9yYXdQcmV2VGltZT1hPSFlfHx0fHxfPT09dD90OmgpO2Vsc2UgaWYoMWUtNz50KXRoaXMuX3RvdGFsVGltZT10aGlzLl90aW1lPTAsdGhpcy5yYXRpbz10aGlzLl9lYXNlLl9jYWxjRW5kP3RoaXMuX2Vhc2UuZ2V0UmF0aW8oMCk6MCwoMCE9PW98fDA9PT1sJiZfPjAmJl8hPT1oKSYmKG49XCJvblJldmVyc2VDb21wbGV0ZVwiLHM9dGhpcy5fcmV2ZXJzZWQpLDA+dD8odGhpcy5fYWN0aXZlPSExLDA9PT1sJiYodGhpcy5faW5pdHRlZHx8IXRoaXMudmFycy5sYXp5fHxpKSYmKF8+PTAmJihpPSEwKSx0aGlzLl9yYXdQcmV2VGltZT1hPSFlfHx0fHxfPT09dD90OmgpKTp0aGlzLl9pbml0dGVkfHwoaT0hMCk7ZWxzZSBpZih0aGlzLl90b3RhbFRpbWU9dGhpcy5fdGltZT10LHRoaXMuX2Vhc2VUeXBlKXt2YXIgdT10L2wsbT10aGlzLl9lYXNlVHlwZSxmPXRoaXMuX2Vhc2VQb3dlcjsoMT09PW18fDM9PT1tJiZ1Pj0uNSkmJih1PTEtdSksMz09PW0mJih1Kj0yKSwxPT09Zj91Kj11OjI9PT1mP3UqPXUqdTozPT09Zj91Kj11KnUqdTo0PT09ZiYmKHUqPXUqdSp1KnUpLHRoaXMucmF0aW89MT09PW0/MS11OjI9PT1tP3U6LjU+dC9sP3UvMjoxLXUvMn1lbHNlIHRoaXMucmF0aW89dGhpcy5fZWFzZS5nZXRSYXRpbyh0L2wpO2lmKHRoaXMuX3RpbWUhPT1vfHxpKXtpZighdGhpcy5faW5pdHRlZCl7aWYodGhpcy5faW5pdCgpLCF0aGlzLl9pbml0dGVkfHx0aGlzLl9nYylyZXR1cm47aWYoIWkmJnRoaXMuX2ZpcnN0UFQmJih0aGlzLnZhcnMubGF6eSE9PSExJiZ0aGlzLl9kdXJhdGlvbnx8dGhpcy52YXJzLmxhenkmJiF0aGlzLl9kdXJhdGlvbikpcmV0dXJuIHRoaXMuX3RpbWU9dGhpcy5fdG90YWxUaW1lPW8sdGhpcy5fcmF3UHJldlRpbWU9Xyx6LnB1c2godGhpcyksdGhpcy5fbGF6eT10LHZvaWQgMDt0aGlzLl90aW1lJiYhcz90aGlzLnJhdGlvPXRoaXMuX2Vhc2UuZ2V0UmF0aW8odGhpcy5fdGltZS9sKTpzJiZ0aGlzLl9lYXNlLl9jYWxjRW5kJiYodGhpcy5yYXRpbz10aGlzLl9lYXNlLmdldFJhdGlvKDA9PT10aGlzLl90aW1lPzA6MSkpfWZvcih0aGlzLl9sYXp5IT09ITEmJih0aGlzLl9sYXp5PSExKSx0aGlzLl9hY3RpdmV8fCF0aGlzLl9wYXVzZWQmJnRoaXMuX3RpbWUhPT1vJiZ0Pj0wJiYodGhpcy5fYWN0aXZlPSEwKSwwPT09byYmKHRoaXMuX3N0YXJ0QXQmJih0Pj0wP3RoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKTpufHwobj1cIl9kdW1teUdTXCIpKSx0aGlzLnZhcnMub25TdGFydCYmKDAhPT10aGlzLl90aW1lfHwwPT09bCkmJihlfHx0aGlzLnZhcnMub25TdGFydC5hcHBseSh0aGlzLnZhcnMub25TdGFydFNjb3BlfHx0aGlzLHRoaXMudmFycy5vblN0YXJ0UGFyYW1zfHxnKSkpLHI9dGhpcy5fZmlyc3RQVDtyOylyLmY/ci50W3IucF0oci5jKnRoaXMucmF0aW8rci5zKTpyLnRbci5wXT1yLmMqdGhpcy5yYXRpbytyLnMscj1yLl9uZXh0O3RoaXMuX29uVXBkYXRlJiYoMD50JiZ0aGlzLl9zdGFydEF0JiZ0aGlzLl9zdGFydFRpbWUmJnRoaXMuX3N0YXJ0QXQucmVuZGVyKHQsZSxpKSxlfHwodGhpcy5fdGltZSE9PW98fHMpJiZ0aGlzLl9vblVwZGF0ZS5hcHBseSh0aGlzLnZhcnMub25VcGRhdGVTY29wZXx8dGhpcyx0aGlzLnZhcnMub25VcGRhdGVQYXJhbXN8fGcpKSxuJiYodGhpcy5fZ2N8fCgwPnQmJnRoaXMuX3N0YXJ0QXQmJiF0aGlzLl9vblVwZGF0ZSYmdGhpcy5fc3RhcnRUaW1lJiZ0aGlzLl9zdGFydEF0LnJlbmRlcih0LGUsaSkscyYmKHRoaXMuX3RpbWVsaW5lLmF1dG9SZW1vdmVDaGlsZHJlbiYmdGhpcy5fZW5hYmxlZCghMSwhMSksdGhpcy5fYWN0aXZlPSExKSwhZSYmdGhpcy52YXJzW25dJiZ0aGlzLnZhcnNbbl0uYXBwbHkodGhpcy52YXJzW24rXCJTY29wZVwiXXx8dGhpcyx0aGlzLnZhcnNbbitcIlBhcmFtc1wiXXx8ZyksMD09PWwmJnRoaXMuX3Jhd1ByZXZUaW1lPT09aCYmYSE9PWgmJih0aGlzLl9yYXdQcmV2VGltZT0wKSkpfX0sbi5fa2lsbD1mdW5jdGlvbih0LGUpe2lmKFwiYWxsXCI9PT10JiYodD1udWxsKSxudWxsPT10JiYobnVsbD09ZXx8ZT09PXRoaXMudGFyZ2V0KSlyZXR1cm4gdGhpcy5fbGF6eT0hMSx0aGlzLl9lbmFibGVkKCExLCExKTtlPVwic3RyaW5nXCIhPXR5cGVvZiBlP2V8fHRoaXMuX3RhcmdldHN8fHRoaXMudGFyZ2V0OkQuc2VsZWN0b3IoZSl8fGU7dmFyIGkscyxuLHIsYSxvLGwsaDtpZigobShlKXx8SShlKSkmJlwibnVtYmVyXCIhPXR5cGVvZiBlWzBdKWZvcihpPWUubGVuZ3RoOy0taT4tMTspdGhpcy5fa2lsbCh0LGVbaV0pJiYobz0hMCk7ZWxzZXtpZih0aGlzLl90YXJnZXRzKXtmb3IoaT10aGlzLl90YXJnZXRzLmxlbmd0aDstLWk+LTE7KWlmKGU9PT10aGlzLl90YXJnZXRzW2ldKXthPXRoaXMuX3Byb3BMb29rdXBbaV18fHt9LHRoaXMuX292ZXJ3cml0dGVuUHJvcHM9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc3x8W10scz10aGlzLl9vdmVyd3JpdHRlblByb3BzW2ldPXQ/dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wc1tpXXx8e306XCJhbGxcIjticmVha319ZWxzZXtpZihlIT09dGhpcy50YXJnZXQpcmV0dXJuITE7YT10aGlzLl9wcm9wTG9va3VwLHM9dGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz10P3RoaXMuX292ZXJ3cml0dGVuUHJvcHN8fHt9OlwiYWxsXCJ9aWYoYSl7bD10fHxhLGg9dCE9PXMmJlwiYWxsXCIhPT1zJiZ0IT09YSYmKFwib2JqZWN0XCIhPXR5cGVvZiB0fHwhdC5fdGVtcEtpbGwpO2ZvcihuIGluIGwpKHI9YVtuXSkmJihyLnBnJiZyLnQuX2tpbGwobCkmJihvPSEwKSxyLnBnJiYwIT09ci50Ll9vdmVyd3JpdGVQcm9wcy5sZW5ndGh8fChyLl9wcmV2P3IuX3ByZXYuX25leHQ9ci5fbmV4dDpyPT09dGhpcy5fZmlyc3RQVCYmKHRoaXMuX2ZpcnN0UFQ9ci5fbmV4dCksci5fbmV4dCYmKHIuX25leHQuX3ByZXY9ci5fcHJldiksci5fbmV4dD1yLl9wcmV2PW51bGwpLGRlbGV0ZSBhW25dKSxoJiYoc1tuXT0xKTshdGhpcy5fZmlyc3RQVCYmdGhpcy5faW5pdHRlZCYmdGhpcy5fZW5hYmxlZCghMSwhMSl9fXJldHVybiBvfSxuLmludmFsaWRhdGU9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fbm90aWZ5UGx1Z2luc09mRW5hYmxlZCYmRC5fb25QbHVnaW5FdmVudChcIl9vbkRpc2FibGVcIix0aGlzKSx0aGlzLl9maXJzdFBUPW51bGwsdGhpcy5fb3ZlcndyaXR0ZW5Qcm9wcz1udWxsLHRoaXMuX29uVXBkYXRlPW51bGwsdGhpcy5fc3RhcnRBdD1udWxsLHRoaXMuX2luaXR0ZWQ9dGhpcy5fYWN0aXZlPXRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQ9dGhpcy5fbGF6eT0hMSx0aGlzLl9wcm9wTG9va3VwPXRoaXMuX3RhcmdldHM/e306W10sdGhpc30sbi5fZW5hYmxlZD1mdW5jdGlvbih0LGUpe2lmKGF8fHIud2FrZSgpLHQmJnRoaXMuX2djKXt2YXIgaSxzPXRoaXMuX3RhcmdldHM7aWYocylmb3IoaT1zLmxlbmd0aDstLWk+LTE7KXRoaXMuX3NpYmxpbmdzW2ldPU0oc1tpXSx0aGlzLCEwKTtlbHNlIHRoaXMuX3NpYmxpbmdzPU0odGhpcy50YXJnZXQsdGhpcywhMCl9cmV0dXJuIHgucHJvdG90eXBlLl9lbmFibGVkLmNhbGwodGhpcyx0LGUpLHRoaXMuX25vdGlmeVBsdWdpbnNPZkVuYWJsZWQmJnRoaXMuX2ZpcnN0UFQ/RC5fb25QbHVnaW5FdmVudCh0P1wiX29uRW5hYmxlXCI6XCJfb25EaXNhYmxlXCIsdGhpcyk6ITF9LEQudG89ZnVuY3Rpb24odCxlLGkpe3JldHVybiBuZXcgRCh0LGUsaSl9LEQuZnJvbT1mdW5jdGlvbih0LGUsaSl7cmV0dXJuIGkucnVuQmFja3dhcmRzPSEwLGkuaW1tZWRpYXRlUmVuZGVyPTAhPWkuaW1tZWRpYXRlUmVuZGVyLG5ldyBEKHQsZSxpKX0sRC5mcm9tVG89ZnVuY3Rpb24odCxlLGkscyl7cmV0dXJuIHMuc3RhcnRBdD1pLHMuaW1tZWRpYXRlUmVuZGVyPTAhPXMuaW1tZWRpYXRlUmVuZGVyJiYwIT1pLmltbWVkaWF0ZVJlbmRlcixuZXcgRCh0LGUscyl9LEQuZGVsYXllZENhbGw9ZnVuY3Rpb24odCxlLGkscyxuKXtyZXR1cm4gbmV3IEQoZSwwLHtkZWxheTp0LG9uQ29tcGxldGU6ZSxvbkNvbXBsZXRlUGFyYW1zOmksb25Db21wbGV0ZVNjb3BlOnMsb25SZXZlcnNlQ29tcGxldGU6ZSxvblJldmVyc2VDb21wbGV0ZVBhcmFtczppLG9uUmV2ZXJzZUNvbXBsZXRlU2NvcGU6cyxpbW1lZGlhdGVSZW5kZXI6ITEsdXNlRnJhbWVzOm4sb3ZlcndyaXRlOjB9KX0sRC5zZXQ9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbmV3IEQodCwwLGUpfSxELmdldFR3ZWVuc09mPWZ1bmN0aW9uKHQsZSl7aWYobnVsbD09dClyZXR1cm5bXTt0PVwic3RyaW5nXCIhPXR5cGVvZiB0P3Q6RC5zZWxlY3Rvcih0KXx8dDt2YXIgaSxzLG4scjtpZigobSh0KXx8SSh0KSkmJlwibnVtYmVyXCIhPXR5cGVvZiB0WzBdKXtmb3IoaT10Lmxlbmd0aCxzPVtdOy0taT4tMTspcz1zLmNvbmNhdChELmdldFR3ZWVuc09mKHRbaV0sZSkpO2ZvcihpPXMubGVuZ3RoOy0taT4tMTspZm9yKHI9c1tpXSxuPWk7LS1uPi0xOylyPT09c1tuXSYmcy5zcGxpY2UoaSwxKX1lbHNlIGZvcihzPU0odCkuY29uY2F0KCksaT1zLmxlbmd0aDstLWk+LTE7KShzW2ldLl9nY3x8ZSYmIXNbaV0uaXNBY3RpdmUoKSkmJnMuc3BsaWNlKGksMSk7cmV0dXJuIHN9LEQua2lsbFR3ZWVuc09mPUQua2lsbERlbGF5ZWRDYWxsc1RvPWZ1bmN0aW9uKHQsZSxpKXtcIm9iamVjdFwiPT10eXBlb2YgZSYmKGk9ZSxlPSExKTtmb3IodmFyIHM9RC5nZXRUd2VlbnNPZih0LGUpLG49cy5sZW5ndGg7LS1uPi0xOylzW25dLl9raWxsKGksdCl9O3ZhciBIPWQoXCJwbHVnaW5zLlR3ZWVuUGx1Z2luXCIsZnVuY3Rpb24odCxlKXt0aGlzLl9vdmVyd3JpdGVQcm9wcz0odHx8XCJcIikuc3BsaXQoXCIsXCIpLHRoaXMuX3Byb3BOYW1lPXRoaXMuX292ZXJ3cml0ZVByb3BzWzBdLHRoaXMuX3ByaW9yaXR5PWV8fDAsdGhpcy5fc3VwZXI9SC5wcm90b3R5cGV9LCEwKTtpZihuPUgucHJvdG90eXBlLEgudmVyc2lvbj1cIjEuMTAuMVwiLEguQVBJPTIsbi5fZmlyc3RQVD1udWxsLG4uX2FkZFR3ZWVuPWZ1bmN0aW9uKHQsZSxpLHMsbixyKXt2YXIgYSxvO3JldHVybiBudWxsIT1zJiYoYT1cIm51bWJlclwiPT10eXBlb2Ygc3x8XCI9XCIhPT1zLmNoYXJBdCgxKT9OdW1iZXIocyktaTpwYXJzZUludChzLmNoYXJBdCgwKStcIjFcIiwxMCkqTnVtYmVyKHMuc3Vic3RyKDIpKSk/KHRoaXMuX2ZpcnN0UFQ9bz17X25leHQ6dGhpcy5fZmlyc3RQVCx0OnQscDplLHM6aSxjOmEsZjpcImZ1bmN0aW9uXCI9PXR5cGVvZiB0W2VdLG46bnx8ZSxyOnJ9LG8uX25leHQmJihvLl9uZXh0Ll9wcmV2PW8pLG8pOnZvaWQgMH0sbi5zZXRSYXRpbz1mdW5jdGlvbih0KXtmb3IodmFyIGUsaT10aGlzLl9maXJzdFBULHM9MWUtNjtpOyllPWkuYyp0K2kucyxpLnI/ZT1NYXRoLnJvdW5kKGUpOnM+ZSYmZT4tcyYmKGU9MCksaS5mP2kudFtpLnBdKGUpOmkudFtpLnBdPWUsaT1pLl9uZXh0fSxuLl9raWxsPWZ1bmN0aW9uKHQpe3ZhciBlLGk9dGhpcy5fb3ZlcndyaXRlUHJvcHMscz10aGlzLl9maXJzdFBUO2lmKG51bGwhPXRbdGhpcy5fcHJvcE5hbWVdKXRoaXMuX292ZXJ3cml0ZVByb3BzPVtdO2Vsc2UgZm9yKGU9aS5sZW5ndGg7LS1lPi0xOyludWxsIT10W2lbZV1dJiZpLnNwbGljZShlLDEpO2Zvcig7czspbnVsbCE9dFtzLm5dJiYocy5fbmV4dCYmKHMuX25leHQuX3ByZXY9cy5fcHJldikscy5fcHJldj8ocy5fcHJldi5fbmV4dD1zLl9uZXh0LHMuX3ByZXY9bnVsbCk6dGhpcy5fZmlyc3RQVD09PXMmJih0aGlzLl9maXJzdFBUPXMuX25leHQpKSxzPXMuX25leHQ7cmV0dXJuITF9LG4uX3JvdW5kUHJvcHM9ZnVuY3Rpb24odCxlKXtmb3IodmFyIGk9dGhpcy5fZmlyc3RQVDtpOykodFt0aGlzLl9wcm9wTmFtZV18fG51bGwhPWkubiYmdFtpLm4uc3BsaXQodGhpcy5fcHJvcE5hbWUrXCJfXCIpLmpvaW4oXCJcIildKSYmKGkucj1lKSxpPWkuX25leHR9LEQuX29uUGx1Z2luRXZlbnQ9ZnVuY3Rpb24odCxlKXt2YXIgaSxzLG4scixhLG89ZS5fZmlyc3RQVDtpZihcIl9vbkluaXRBbGxQcm9wc1wiPT09dCl7Zm9yKDtvOyl7Zm9yKGE9by5fbmV4dCxzPW47cyYmcy5wcj5vLnByOylzPXMuX25leHQ7KG8uX3ByZXY9cz9zLl9wcmV2OnIpP28uX3ByZXYuX25leHQ9bzpuPW8sKG8uX25leHQ9cyk/cy5fcHJldj1vOnI9byxvPWF9bz1lLl9maXJzdFBUPW59Zm9yKDtvOylvLnBnJiZcImZ1bmN0aW9uXCI9PXR5cGVvZiBvLnRbdF0mJm8udFt0XSgpJiYoaT0hMCksbz1vLl9uZXh0O3JldHVybiBpfSxILmFjdGl2YXRlPWZ1bmN0aW9uKHQpe2Zvcih2YXIgZT10Lmxlbmd0aDstLWU+LTE7KXRbZV0uQVBJPT09SC5BUEkmJihMWyhuZXcgdFtlXSkuX3Byb3BOYW1lXT10W2VdKTtyZXR1cm4hMH0sYy5wbHVnaW49ZnVuY3Rpb24odCl7aWYoISh0JiZ0LnByb3BOYW1lJiZ0LmluaXQmJnQuQVBJKSl0aHJvd1wiaWxsZWdhbCBwbHVnaW4gZGVmaW5pdGlvbi5cIjt2YXIgZSxpPXQucHJvcE5hbWUscz10LnByaW9yaXR5fHwwLG49dC5vdmVyd3JpdGVQcm9wcyxyPXtpbml0OlwiX29uSW5pdFR3ZWVuXCIsc2V0Olwic2V0UmF0aW9cIixraWxsOlwiX2tpbGxcIixyb3VuZDpcIl9yb3VuZFByb3BzXCIsaW5pdEFsbDpcIl9vbkluaXRBbGxQcm9wc1wifSxhPWQoXCJwbHVnaW5zLlwiK2kuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkraS5zdWJzdHIoMSkrXCJQbHVnaW5cIixmdW5jdGlvbigpe0guY2FsbCh0aGlzLGkscyksdGhpcy5fb3ZlcndyaXRlUHJvcHM9bnx8W119LHQuZ2xvYmFsPT09ITApLG89YS5wcm90b3R5cGU9bmV3IEgoaSk7by5jb25zdHJ1Y3Rvcj1hLGEuQVBJPXQuQVBJO2ZvcihlIGluIHIpXCJmdW5jdGlvblwiPT10eXBlb2YgdFtlXSYmKG9bcltlXV09dFtlXSk7cmV0dXJuIGEudmVyc2lvbj10LnZlcnNpb24sSC5hY3RpdmF0ZShbYV0pLGF9LGk9dC5fZ3NRdWV1ZSl7Zm9yKHM9MDtpLmxlbmd0aD5zO3MrKylpW3NdKCk7Zm9yKG4gaW4gZilmW25dLmZ1bmN8fHQuY29uc29sZS5sb2coXCJHU0FQIGVuY291bnRlcmVkIG1pc3NpbmcgZGVwZW5kZW5jeTogY29tLmdyZWVuc29jay5cIituKX1hPSExfX0pKHdpbmRvdyk7IiwiLyohXHJcbiAqIFZFUlNJT046IGJldGEgMS45LjNcclxuICogREFURTogMjAxMy0wNC0wMlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICoqL1xyXG4od2luZG93Ll9nc1F1ZXVlfHwod2luZG93Ll9nc1F1ZXVlPVtdKSkucHVzaChmdW5jdGlvbigpe1widXNlIHN0cmljdFwiO3dpbmRvdy5fZ3NEZWZpbmUoXCJlYXNpbmcuQmFja1wiLFtcImVhc2luZy5FYXNlXCJdLGZ1bmN0aW9uKHQpe3ZhciBlLGkscyxyPXdpbmRvdy5HcmVlblNvY2tHbG9iYWxzfHx3aW5kb3csbj1yLmNvbS5ncmVlbnNvY2ssYT0yKk1hdGguUEksbz1NYXRoLlBJLzIsaD1uLl9jbGFzcyxsPWZ1bmN0aW9uKGUsaSl7dmFyIHM9aChcImVhc2luZy5cIitlLGZ1bmN0aW9uKCl7fSwhMCkscj1zLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gci5jb25zdHJ1Y3Rvcj1zLHIuZ2V0UmF0aW89aSxzfSxfPXQucmVnaXN0ZXJ8fGZ1bmN0aW9uKCl7fSx1PWZ1bmN0aW9uKHQsZSxpLHMpe3ZhciByPWgoXCJlYXNpbmcuXCIrdCx7ZWFzZU91dDpuZXcgZSxlYXNlSW46bmV3IGksZWFzZUluT3V0Om5ldyBzfSwhMCk7cmV0dXJuIF8ocix0KSxyfSxjPWZ1bmN0aW9uKHQsZSxpKXt0aGlzLnQ9dCx0aGlzLnY9ZSxpJiYodGhpcy5uZXh0PWksaS5wcmV2PXRoaXMsdGhpcy5jPWkudi1lLHRoaXMuZ2FwPWkudC10KX0sZj1mdW5jdGlvbihlLGkpe3ZhciBzPWgoXCJlYXNpbmcuXCIrZSxmdW5jdGlvbih0KXt0aGlzLl9wMT10fHwwPT09dD90OjEuNzAxNTgsdGhpcy5fcDI9MS41MjUqdGhpcy5fcDF9LCEwKSxyPXMucHJvdG90eXBlPW5ldyB0O3JldHVybiByLmNvbnN0cnVjdG9yPXMsci5nZXRSYXRpbz1pLHIuY29uZmlnPWZ1bmN0aW9uKHQpe3JldHVybiBuZXcgcyh0KX0sc30scD11KFwiQmFja1wiLGYoXCJCYWNrT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuKHQtPTEpKnQqKCh0aGlzLl9wMSsxKSp0K3RoaXMuX3AxKSsxfSksZihcIkJhY2tJblwiLGZ1bmN0aW9uKHQpe3JldHVybiB0KnQqKCh0aGlzLl9wMSsxKSp0LXRoaXMuX3AxKX0pLGYoXCJCYWNrSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LjUqdCp0KigodGhpcy5fcDIrMSkqdC10aGlzLl9wMik6LjUqKCh0LT0yKSp0KigodGhpcy5fcDIrMSkqdCt0aGlzLl9wMikrMil9KSksbT1oKFwiZWFzaW5nLlNsb3dNb1wiLGZ1bmN0aW9uKHQsZSxpKXtlPWV8fDA9PT1lP2U6LjcsbnVsbD09dD90PS43OnQ+MSYmKHQ9MSksdGhpcy5fcD0xIT09dD9lOjAsdGhpcy5fcDE9KDEtdCkvMix0aGlzLl9wMj10LHRoaXMuX3AzPXRoaXMuX3AxK3RoaXMuX3AyLHRoaXMuX2NhbGNFbmQ9aT09PSEwfSwhMCksZD1tLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gZC5jb25zdHJ1Y3Rvcj1tLGQuZ2V0UmF0aW89ZnVuY3Rpb24odCl7dmFyIGU9dCsoLjUtdCkqdGhpcy5fcDtyZXR1cm4gdGhpcy5fcDE+dD90aGlzLl9jYWxjRW5kPzEtKHQ9MS10L3RoaXMuX3AxKSp0OmUtKHQ9MS10L3RoaXMuX3AxKSp0KnQqdCplOnQ+dGhpcy5fcDM/dGhpcy5fY2FsY0VuZD8xLSh0PSh0LXRoaXMuX3AzKS90aGlzLl9wMSkqdDplKyh0LWUpKih0PSh0LXRoaXMuX3AzKS90aGlzLl9wMSkqdCp0KnQ6dGhpcy5fY2FsY0VuZD8xOmV9LG0uZWFzZT1uZXcgbSguNywuNyksZC5jb25maWc9bS5jb25maWc9ZnVuY3Rpb24odCxlLGkpe3JldHVybiBuZXcgbSh0LGUsaSl9LGU9aChcImVhc2luZy5TdGVwcGVkRWFzZVwiLGZ1bmN0aW9uKHQpe3Q9dHx8MSx0aGlzLl9wMT0xL3QsdGhpcy5fcDI9dCsxfSwhMCksZD1lLnByb3RvdHlwZT1uZXcgdCxkLmNvbnN0cnVjdG9yPWUsZC5nZXRSYXRpbz1mdW5jdGlvbih0KXtyZXR1cm4gMD50P3Q9MDp0Pj0xJiYodD0uOTk5OTk5OTk5KSwodGhpcy5fcDIqdD4+MCkqdGhpcy5fcDF9LGQuY29uZmlnPWUuY29uZmlnPWZ1bmN0aW9uKHQpe3JldHVybiBuZXcgZSh0KX0saT1oKFwiZWFzaW5nLlJvdWdoRWFzZVwiLGZ1bmN0aW9uKGUpe2U9ZXx8e307Zm9yKHZhciBpLHMscixuLGEsbyxoPWUudGFwZXJ8fFwibm9uZVwiLGw9W10sXz0wLHU9MHwoZS5wb2ludHN8fDIwKSxmPXUscD1lLnJhbmRvbWl6ZSE9PSExLG09ZS5jbGFtcD09PSEwLGQ9ZS50ZW1wbGF0ZSBpbnN0YW5jZW9mIHQ/ZS50ZW1wbGF0ZTpudWxsLGc9XCJudW1iZXJcIj09dHlwZW9mIGUuc3RyZW5ndGg/LjQqZS5zdHJlbmd0aDouNDstLWY+LTE7KWk9cD9NYXRoLnJhbmRvbSgpOjEvdSpmLHM9ZD9kLmdldFJhdGlvKGkpOmksXCJub25lXCI9PT1oP3I9ZzpcIm91dFwiPT09aD8obj0xLWkscj1uKm4qZyk6XCJpblwiPT09aD9yPWkqaSpnOi41Pmk/KG49MippLHI9LjUqbipuKmcpOihuPTIqKDEtaSkscj0uNSpuKm4qZykscD9zKz1NYXRoLnJhbmRvbSgpKnItLjUqcjpmJTI/cys9LjUqcjpzLT0uNSpyLG0mJihzPjE/cz0xOjA+cyYmKHM9MCkpLGxbXysrXT17eDppLHk6c307Zm9yKGwuc29ydChmdW5jdGlvbih0LGUpe3JldHVybiB0LngtZS54fSksbz1uZXcgYygxLDEsbnVsbCksZj11Oy0tZj4tMTspYT1sW2ZdLG89bmV3IGMoYS54LGEueSxvKTt0aGlzLl9wcmV2PW5ldyBjKDAsMCwwIT09by50P286by5uZXh0KX0sITApLGQ9aS5wcm90b3R5cGU9bmV3IHQsZC5jb25zdHJ1Y3Rvcj1pLGQuZ2V0UmF0aW89ZnVuY3Rpb24odCl7dmFyIGU9dGhpcy5fcHJldjtpZih0PmUudCl7Zm9yKDtlLm5leHQmJnQ+PWUudDspZT1lLm5leHQ7ZT1lLnByZXZ9ZWxzZSBmb3IoO2UucHJldiYmZS50Pj10OyllPWUucHJldjtyZXR1cm4gdGhpcy5fcHJldj1lLGUudisodC1lLnQpL2UuZ2FwKmUuY30sZC5jb25maWc9ZnVuY3Rpb24odCl7cmV0dXJuIG5ldyBpKHQpfSxpLmVhc2U9bmV3IGksdShcIkJvdW5jZVwiLGwoXCJCb3VuY2VPdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMS8yLjc1PnQ/Ny41NjI1KnQqdDoyLzIuNzU+dD83LjU2MjUqKHQtPTEuNS8yLjc1KSp0Ky43NToyLjUvMi43NT50PzcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1OjcuNTYyNSoodC09Mi42MjUvMi43NSkqdCsuOTg0Mzc1fSksbChcIkJvdW5jZUluXCIsZnVuY3Rpb24odCl7cmV0dXJuIDEvMi43NT4odD0xLXQpPzEtNy41NjI1KnQqdDoyLzIuNzU+dD8xLSg3LjU2MjUqKHQtPTEuNS8yLjc1KSp0Ky43NSk6Mi41LzIuNzU+dD8xLSg3LjU2MjUqKHQtPTIuMjUvMi43NSkqdCsuOTM3NSk6MS0oNy41NjI1Kih0LT0yLjYyNS8yLjc1KSp0Ky45ODQzNzUpfSksbChcIkJvdW5jZUluT3V0XCIsZnVuY3Rpb24odCl7dmFyIGU9LjU+dDtyZXR1cm4gdD1lPzEtMip0OjIqdC0xLHQ9MS8yLjc1PnQ/Ny41NjI1KnQqdDoyLzIuNzU+dD83LjU2MjUqKHQtPTEuNS8yLjc1KSp0Ky43NToyLjUvMi43NT50PzcuNTYyNSoodC09Mi4yNS8yLjc1KSp0Ky45Mzc1OjcuNTYyNSoodC09Mi42MjUvMi43NSkqdCsuOTg0Mzc1LGU/LjUqKDEtdCk6LjUqdCsuNX0pKSx1KFwiQ2lyY1wiLGwoXCJDaXJjT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIE1hdGguc3FydCgxLSh0LT0xKSp0KX0pLGwoXCJDaXJjSW5cIixmdW5jdGlvbih0KXtyZXR1cm4tKE1hdGguc3FydCgxLXQqdCktMSl9KSxsKFwiQ2lyY0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy0uNSooTWF0aC5zcXJ0KDEtdCp0KS0xKTouNSooTWF0aC5zcXJ0KDEtKHQtPTIpKnQpKzEpfSkpLHM9ZnVuY3Rpb24oZSxpLHMpe3ZhciByPWgoXCJlYXNpbmcuXCIrZSxmdW5jdGlvbih0LGUpe3RoaXMuX3AxPXR8fDEsdGhpcy5fcDI9ZXx8cyx0aGlzLl9wMz10aGlzLl9wMi9hKihNYXRoLmFzaW4oMS90aGlzLl9wMSl8fDApfSwhMCksbj1yLnByb3RvdHlwZT1uZXcgdDtyZXR1cm4gbi5jb25zdHJ1Y3Rvcj1yLG4uZ2V0UmF0aW89aSxuLmNvbmZpZz1mdW5jdGlvbih0LGUpe3JldHVybiBuZXcgcih0LGUpfSxyfSx1KFwiRWxhc3RpY1wiLHMoXCJFbGFzdGljT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIHRoaXMuX3AxKk1hdGgucG93KDIsLTEwKnQpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKSsxfSwuMykscyhcIkVsYXN0aWNJblwiLGZ1bmN0aW9uKHQpe3JldHVybi0odGhpcy5fcDEqTWF0aC5wb3coMiwxMCoodC09MSkpKk1hdGguc2luKCh0LXRoaXMuX3AzKSphL3RoaXMuX3AyKSl9LC4zKSxzKFwiRWxhc3RpY0luT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIDE+KHQqPTIpPy0uNSp0aGlzLl9wMSpNYXRoLnBvdygyLDEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpOi41KnRoaXMuX3AxKk1hdGgucG93KDIsLTEwKih0LT0xKSkqTWF0aC5zaW4oKHQtdGhpcy5fcDMpKmEvdGhpcy5fcDIpKzF9LC40NSkpLHUoXCJFeHBvXCIsbChcIkV4cG9PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMS1NYXRoLnBvdygyLC0xMCp0KX0pLGwoXCJFeHBvSW5cIixmdW5jdGlvbih0KXtyZXR1cm4gTWF0aC5wb3coMiwxMCoodC0xKSktLjAwMX0pLGwoXCJFeHBvSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4gMT4odCo9Mik/LjUqTWF0aC5wb3coMiwxMCoodC0xKSk6LjUqKDItTWF0aC5wb3coMiwtMTAqKHQtMSkpKX0pKSx1KFwiU2luZVwiLGwoXCJTaW5lT3V0XCIsZnVuY3Rpb24odCl7cmV0dXJuIE1hdGguc2luKHQqbyl9KSxsKFwiU2luZUluXCIsZnVuY3Rpb24odCl7cmV0dXJuLU1hdGguY29zKHQqbykrMX0pLGwoXCJTaW5lSW5PdXRcIixmdW5jdGlvbih0KXtyZXR1cm4tLjUqKE1hdGguY29zKE1hdGguUEkqdCktMSl9KSksaChcImVhc2luZy5FYXNlTG9va3VwXCIse2ZpbmQ6ZnVuY3Rpb24oZSl7cmV0dXJuIHQubWFwW2VdfX0sITApLF8oci5TbG93TW8sXCJTbG93TW9cIixcImVhc2UsXCIpLF8oaSxcIlJvdWdoRWFzZVwiLFwiZWFzZSxcIiksXyhlLFwiU3RlcHBlZEVhc2VcIixcImVhc2UsXCIpLHB9LCEwKX0pLHdpbmRvdy5fZ3NEZWZpbmUmJndpbmRvdy5fZ3NRdWV1ZS5wb3AoKSgpOyIsIi8qIVxyXG4gKiBWRVJTSU9OOiAxLjEyLjFcclxuICogREFURTogMjAxNC0wNi0yNlxyXG4gKiBVUERBVEVTIEFORCBET0NTIEFUOiBodHRwOi8vd3d3LmdyZWVuc29jay5jb21cclxuICpcclxuICogQGxpY2Vuc2UgQ29weXJpZ2h0IChjKSAyMDA4LTIwMTQsIEdyZWVuU29jay4gQWxsIHJpZ2h0cyByZXNlcnZlZC5cclxuICogVGhpcyB3b3JrIGlzIHN1YmplY3QgdG8gdGhlIHRlcm1zIGF0IGh0dHA6Ly93d3cuZ3JlZW5zb2NrLmNvbS90ZXJtc19vZl91c2UuaHRtbCBvciBmb3JcclxuICogQ2x1YiBHcmVlblNvY2sgbWVtYmVycywgdGhlIHNvZnR3YXJlIGFncmVlbWVudCB0aGF0IHdhcyBpc3N1ZWQgd2l0aCB5b3VyIG1lbWJlcnNoaXAuXHJcbiAqIFxyXG4gKiBAYXV0aG9yOiBKYWNrIERveWxlLCBqYWNrQGdyZWVuc29jay5jb21cclxuICovXHJcbih3aW5kb3cuX2dzUXVldWV8fCh3aW5kb3cuX2dzUXVldWU9W10pKS5wdXNoKGZ1bmN0aW9uKCl7XCJ1c2Ugc3RyaWN0XCI7d2luZG93Ll9nc0RlZmluZShcInBsdWdpbnMuQ1NTUGx1Z2luXCIsW1wicGx1Z2lucy5Ud2VlblBsdWdpblwiLFwiVHdlZW5MaXRlXCJdLGZ1bmN0aW9uKHQsZSl7dmFyIGkscixzLG4sYT1mdW5jdGlvbigpe3QuY2FsbCh0aGlzLFwiY3NzXCIpLHRoaXMuX292ZXJ3cml0ZVByb3BzLmxlbmd0aD0wLHRoaXMuc2V0UmF0aW89YS5wcm90b3R5cGUuc2V0UmF0aW99LG89e30sbD1hLnByb3RvdHlwZT1uZXcgdChcImNzc1wiKTtsLmNvbnN0cnVjdG9yPWEsYS52ZXJzaW9uPVwiMS4xMi4xXCIsYS5BUEk9MixhLmRlZmF1bHRUcmFuc2Zvcm1QZXJzcGVjdGl2ZT0wLGEuZGVmYXVsdFNrZXdUeXBlPVwiY29tcGVuc2F0ZWRcIixsPVwicHhcIixhLnN1ZmZpeE1hcD17dG9wOmwscmlnaHQ6bCxib3R0b206bCxsZWZ0Omwsd2lkdGg6bCxoZWlnaHQ6bCxmb250U2l6ZTpsLHBhZGRpbmc6bCxtYXJnaW46bCxwZXJzcGVjdGl2ZTpsLGxpbmVIZWlnaHQ6XCJcIn07dmFyIGgsdSxmLF8scCxjLGQ9Lyg/OlxcZHxcXC1cXGR8XFwuXFxkfFxcLVxcLlxcZCkrL2csbT0vKD86XFxkfFxcLVxcZHxcXC5cXGR8XFwtXFwuXFxkfFxcKz1cXGR8XFwtPVxcZHxcXCs9LlxcZHxcXC09XFwuXFxkKSsvZyxnPS8oPzpcXCs9fFxcLT18XFwtfFxcYilbXFxkXFwtXFwuXStbYS16QS1aMC05XSooPzolfFxcYikvZ2ksdj0vW15cXGRcXC1cXC5dL2cseT0vKD86XFxkfFxcLXxcXCt8PXwjfFxcLikqL2csVD0vb3BhY2l0eSAqPSAqKFteKV0qKS9pLHc9L29wYWNpdHk6KFteO10qKS9pLHg9L2FscGhhXFwob3BhY2l0eSAqPS4rP1xcKS9pLGI9L14ocmdifGhzbCkvLFA9LyhbQS1aXSkvZyxTPS8tKFthLXpdKS9naSxDPS8oXig/OnVybFxcKFxcXCJ8dXJsXFwoKSl8KD86KFxcXCJcXCkpJHxcXCkkKS9naSxSPWZ1bmN0aW9uKHQsZSl7cmV0dXJuIGUudG9VcHBlckNhc2UoKX0saz0vKD86TGVmdHxSaWdodHxXaWR0aCkvaSxBPS8oTTExfE0xMnxNMjF8TTIyKT1bXFxkXFwtXFwuZV0rL2dpLE89L3Byb2dpZFxcOkRYSW1hZ2VUcmFuc2Zvcm1cXC5NaWNyb3NvZnRcXC5NYXRyaXhcXCguKz9cXCkvaSxEPS8sKD89W15cXCldKig/OlxcKHwkKSkvZ2ksTT1NYXRoLlBJLzE4MCxMPTE4MC9NYXRoLlBJLE49e30sWD1kb2N1bWVudCx6PVguY3JlYXRlRWxlbWVudChcImRpdlwiKSxJPVguY3JlYXRlRWxlbWVudChcImltZ1wiKSxFPWEuX2ludGVybmFscz17X3NwZWNpYWxQcm9wczpvfSxGPW5hdmlnYXRvci51c2VyQWdlbnQsWT1mdW5jdGlvbigpe3ZhciB0LGU9Ri5pbmRleE9mKFwiQW5kcm9pZFwiKSxpPVguY3JlYXRlRWxlbWVudChcImRpdlwiKTtyZXR1cm4gZj0tMSE9PUYuaW5kZXhPZihcIlNhZmFyaVwiKSYmLTE9PT1GLmluZGV4T2YoXCJDaHJvbWVcIikmJigtMT09PWV8fE51bWJlcihGLnN1YnN0cihlKzgsMSkpPjMpLHA9ZiYmNj5OdW1iZXIoRi5zdWJzdHIoRi5pbmRleE9mKFwiVmVyc2lvbi9cIikrOCwxKSksXz0tMSE9PUYuaW5kZXhPZihcIkZpcmVmb3hcIiksL01TSUUgKFswLTldezEsfVtcXC4wLTldezAsfSkvLmV4ZWMoRikmJihjPXBhcnNlRmxvYXQoUmVnRXhwLiQxKSksaS5pbm5lckhUTUw9XCI8YSBzdHlsZT0ndG9wOjFweDtvcGFjaXR5Oi41NTsnPmE8L2E+XCIsdD1pLmdldEVsZW1lbnRzQnlUYWdOYW1lKFwiYVwiKVswXSx0Py9eMC41NS8udGVzdCh0LnN0eWxlLm9wYWNpdHkpOiExfSgpLEI9ZnVuY3Rpb24odCl7cmV0dXJuIFQudGVzdChcInN0cmluZ1wiPT10eXBlb2YgdD90Oih0LmN1cnJlbnRTdHlsZT90LmN1cnJlbnRTdHlsZS5maWx0ZXI6dC5zdHlsZS5maWx0ZXIpfHxcIlwiKT9wYXJzZUZsb2F0KFJlZ0V4cC4kMSkvMTAwOjF9LFU9ZnVuY3Rpb24odCl7d2luZG93LmNvbnNvbGUmJmNvbnNvbGUubG9nKHQpfSxXPVwiXCIsaj1cIlwiLFY9ZnVuY3Rpb24odCxlKXtlPWV8fHo7dmFyIGkscixzPWUuc3R5bGU7aWYodm9pZCAwIT09c1t0XSlyZXR1cm4gdDtmb3IodD10LmNoYXJBdCgwKS50b1VwcGVyQ2FzZSgpK3Quc3Vic3RyKDEpLGk9W1wiT1wiLFwiTW96XCIsXCJtc1wiLFwiTXNcIixcIldlYmtpdFwiXSxyPTU7LS1yPi0xJiZ2b2lkIDA9PT1zW2lbcl0rdF07KTtyZXR1cm4gcj49MD8oaj0zPT09cj9cIm1zXCI6aVtyXSxXPVwiLVwiK2oudG9Mb3dlckNhc2UoKStcIi1cIixqK3QpOm51bGx9LEg9WC5kZWZhdWx0Vmlldz9YLmRlZmF1bHRWaWV3LmdldENvbXB1dGVkU3R5bGU6ZnVuY3Rpb24oKXt9LHE9YS5nZXRTdHlsZT1mdW5jdGlvbih0LGUsaSxyLHMpe3ZhciBuO3JldHVybiBZfHxcIm9wYWNpdHlcIiE9PWU/KCFyJiZ0LnN0eWxlW2VdP249dC5zdHlsZVtlXTooaT1pfHxIKHQpKT9uPWlbZV18fGkuZ2V0UHJvcGVydHlWYWx1ZShlKXx8aS5nZXRQcm9wZXJ0eVZhbHVlKGUucmVwbGFjZShQLFwiLSQxXCIpLnRvTG93ZXJDYXNlKCkpOnQuY3VycmVudFN0eWxlJiYobj10LmN1cnJlbnRTdHlsZVtlXSksbnVsbD09c3x8biYmXCJub25lXCIhPT1uJiZcImF1dG9cIiE9PW4mJlwiYXV0byBhdXRvXCIhPT1uP246cyk6Qih0KX0sUT1FLmNvbnZlcnRUb1BpeGVscz1mdW5jdGlvbih0LGkscixzLG4pe2lmKFwicHhcIj09PXN8fCFzKXJldHVybiByO2lmKFwiYXV0b1wiPT09c3x8IXIpcmV0dXJuIDA7dmFyIG8sbCxoLHU9ay50ZXN0KGkpLGY9dCxfPXouc3R5bGUscD0wPnI7aWYocCYmKHI9LXIpLFwiJVwiPT09cyYmLTEhPT1pLmluZGV4T2YoXCJib3JkZXJcIikpbz1yLzEwMCoodT90LmNsaWVudFdpZHRoOnQuY2xpZW50SGVpZ2h0KTtlbHNle2lmKF8uY3NzVGV4dD1cImJvcmRlcjowIHNvbGlkIHJlZDtwb3NpdGlvbjpcIitxKHQsXCJwb3NpdGlvblwiKStcIjtsaW5lLWhlaWdodDowO1wiLFwiJVwiIT09cyYmZi5hcHBlbmRDaGlsZClfW3U/XCJib3JkZXJMZWZ0V2lkdGhcIjpcImJvcmRlclRvcFdpZHRoXCJdPXIrcztlbHNle2lmKGY9dC5wYXJlbnROb2RlfHxYLmJvZHksbD1mLl9nc0NhY2hlLGg9ZS50aWNrZXIuZnJhbWUsbCYmdSYmbC50aW1lPT09aClyZXR1cm4gbC53aWR0aCpyLzEwMDtfW3U/XCJ3aWR0aFwiOlwiaGVpZ2h0XCJdPXIrc31mLmFwcGVuZENoaWxkKHopLG89cGFyc2VGbG9hdCh6W3U/XCJvZmZzZXRXaWR0aFwiOlwib2Zmc2V0SGVpZ2h0XCJdKSxmLnJlbW92ZUNoaWxkKHopLHUmJlwiJVwiPT09cyYmYS5jYWNoZVdpZHRocyE9PSExJiYobD1mLl9nc0NhY2hlPWYuX2dzQ2FjaGV8fHt9LGwudGltZT1oLGwud2lkdGg9MTAwKihvL3IpKSwwIT09b3x8bnx8KG89USh0LGkscixzLCEwKSl9cmV0dXJuIHA/LW86b30sWj1FLmNhbGN1bGF0ZU9mZnNldD1mdW5jdGlvbih0LGUsaSl7aWYoXCJhYnNvbHV0ZVwiIT09cSh0LFwicG9zaXRpb25cIixpKSlyZXR1cm4gMDt2YXIgcj1cImxlZnRcIj09PWU/XCJMZWZ0XCI6XCJUb3BcIixzPXEodCxcIm1hcmdpblwiK3IsaSk7cmV0dXJuIHRbXCJvZmZzZXRcIityXS0oUSh0LGUscGFyc2VGbG9hdChzKSxzLnJlcGxhY2UoeSxcIlwiKSl8fDApfSwkPWZ1bmN0aW9uKHQsZSl7dmFyIGkscixzPXt9O2lmKGU9ZXx8SCh0LG51bGwpKWlmKGk9ZS5sZW5ndGgpZm9yKDstLWk+LTE7KXNbZVtpXS5yZXBsYWNlKFMsUildPWUuZ2V0UHJvcGVydHlWYWx1ZShlW2ldKTtlbHNlIGZvcihpIGluIGUpc1tpXT1lW2ldO2Vsc2UgaWYoZT10LmN1cnJlbnRTdHlsZXx8dC5zdHlsZSlmb3IoaSBpbiBlKVwic3RyaW5nXCI9PXR5cGVvZiBpJiZ2b2lkIDA9PT1zW2ldJiYoc1tpLnJlcGxhY2UoUyxSKV09ZVtpXSk7cmV0dXJuIFl8fChzLm9wYWNpdHk9Qih0KSkscj1QZSh0LGUsITEpLHMucm90YXRpb249ci5yb3RhdGlvbixzLnNrZXdYPXIuc2tld1gscy5zY2FsZVg9ci5zY2FsZVgscy5zY2FsZVk9ci5zY2FsZVkscy54PXIueCxzLnk9ci55LHhlJiYocy56PXIueixzLnJvdGF0aW9uWD1yLnJvdGF0aW9uWCxzLnJvdGF0aW9uWT1yLnJvdGF0aW9uWSxzLnNjYWxlWj1yLnNjYWxlWikscy5maWx0ZXJzJiZkZWxldGUgcy5maWx0ZXJzLHN9LEc9ZnVuY3Rpb24odCxlLGkscixzKXt2YXIgbixhLG8sbD17fSxoPXQuc3R5bGU7Zm9yKGEgaW4gaSlcImNzc1RleHRcIiE9PWEmJlwibGVuZ3RoXCIhPT1hJiZpc05hTihhKSYmKGVbYV0hPT0obj1pW2FdKXx8cyYmc1thXSkmJi0xPT09YS5pbmRleE9mKFwiT3JpZ2luXCIpJiYoXCJudW1iZXJcIj09dHlwZW9mIG58fFwic3RyaW5nXCI9PXR5cGVvZiBuKSYmKGxbYV09XCJhdXRvXCIhPT1ufHxcImxlZnRcIiE9PWEmJlwidG9wXCIhPT1hP1wiXCIhPT1uJiZcImF1dG9cIiE9PW4mJlwibm9uZVwiIT09bnx8XCJzdHJpbmdcIiE9dHlwZW9mIGVbYV18fFwiXCI9PT1lW2FdLnJlcGxhY2UodixcIlwiKT9uOjA6Wih0LGEpLHZvaWQgMCE9PWhbYV0mJihvPW5ldyBmZShoLGEsaFthXSxvKSkpO2lmKHIpZm9yKGEgaW4gcilcImNsYXNzTmFtZVwiIT09YSYmKGxbYV09clthXSk7cmV0dXJue2RpZnM6bCxmaXJzdE1QVDpvfX0sSz17d2lkdGg6W1wiTGVmdFwiLFwiUmlnaHRcIl0saGVpZ2h0OltcIlRvcFwiLFwiQm90dG9tXCJdfSxKPVtcIm1hcmdpbkxlZnRcIixcIm1hcmdpblJpZ2h0XCIsXCJtYXJnaW5Ub3BcIixcIm1hcmdpbkJvdHRvbVwiXSx0ZT1mdW5jdGlvbih0LGUsaSl7dmFyIHI9cGFyc2VGbG9hdChcIndpZHRoXCI9PT1lP3Qub2Zmc2V0V2lkdGg6dC5vZmZzZXRIZWlnaHQpLHM9S1tlXSxuPXMubGVuZ3RoO2ZvcihpPWl8fEgodCxudWxsKTstLW4+LTE7KXItPXBhcnNlRmxvYXQocSh0LFwicGFkZGluZ1wiK3Nbbl0saSwhMCkpfHwwLHItPXBhcnNlRmxvYXQocSh0LFwiYm9yZGVyXCIrc1tuXStcIldpZHRoXCIsaSwhMCkpfHwwO3JldHVybiByfSxlZT1mdW5jdGlvbih0LGUpeyhudWxsPT10fHxcIlwiPT09dHx8XCJhdXRvXCI9PT10fHxcImF1dG8gYXV0b1wiPT09dCkmJih0PVwiMCAwXCIpO3ZhciBpPXQuc3BsaXQoXCIgXCIpLHI9LTEhPT10LmluZGV4T2YoXCJsZWZ0XCIpP1wiMCVcIjotMSE9PXQuaW5kZXhPZihcInJpZ2h0XCIpP1wiMTAwJVwiOmlbMF0scz0tMSE9PXQuaW5kZXhPZihcInRvcFwiKT9cIjAlXCI6LTEhPT10LmluZGV4T2YoXCJib3R0b21cIik/XCIxMDAlXCI6aVsxXTtyZXR1cm4gbnVsbD09cz9zPVwiMFwiOlwiY2VudGVyXCI9PT1zJiYocz1cIjUwJVwiKSwoXCJjZW50ZXJcIj09PXJ8fGlzTmFOKHBhcnNlRmxvYXQocikpJiYtMT09PShyK1wiXCIpLmluZGV4T2YoXCI9XCIpKSYmKHI9XCI1MCVcIiksZSYmKGUub3hwPS0xIT09ci5pbmRleE9mKFwiJVwiKSxlLm95cD0tMSE9PXMuaW5kZXhPZihcIiVcIiksZS5veHI9XCI9XCI9PT1yLmNoYXJBdCgxKSxlLm95cj1cIj1cIj09PXMuY2hhckF0KDEpLGUub3g9cGFyc2VGbG9hdChyLnJlcGxhY2UodixcIlwiKSksZS5veT1wYXJzZUZsb2F0KHMucmVwbGFjZSh2LFwiXCIpKSkscitcIiBcIitzKyhpLmxlbmd0aD4yP1wiIFwiK2lbMl06XCJcIil9LGllPWZ1bmN0aW9uKHQsZSl7cmV0dXJuXCJzdHJpbmdcIj09dHlwZW9mIHQmJlwiPVwiPT09dC5jaGFyQXQoMSk/cGFyc2VJbnQodC5jaGFyQXQoMCkrXCIxXCIsMTApKnBhcnNlRmxvYXQodC5zdWJzdHIoMikpOnBhcnNlRmxvYXQodCktcGFyc2VGbG9hdChlKX0scmU9ZnVuY3Rpb24odCxlKXtyZXR1cm4gbnVsbD09dD9lOlwic3RyaW5nXCI9PXR5cGVvZiB0JiZcIj1cIj09PXQuY2hhckF0KDEpP3BhcnNlSW50KHQuY2hhckF0KDApK1wiMVwiLDEwKSpOdW1iZXIodC5zdWJzdHIoMikpK2U6cGFyc2VGbG9hdCh0KX0sc2U9ZnVuY3Rpb24odCxlLGkscil7dmFyIHMsbixhLG8sbD0xZS02O3JldHVybiBudWxsPT10P289ZTpcIm51bWJlclwiPT10eXBlb2YgdD9vPXQ6KHM9MzYwLG49dC5zcGxpdChcIl9cIiksYT1OdW1iZXIoblswXS5yZXBsYWNlKHYsXCJcIikpKigtMT09PXQuaW5kZXhPZihcInJhZFwiKT8xOkwpLShcIj1cIj09PXQuY2hhckF0KDEpPzA6ZSksbi5sZW5ndGgmJihyJiYocltpXT1lK2EpLC0xIT09dC5pbmRleE9mKFwic2hvcnRcIikmJihhJT1zLGEhPT1hJShzLzIpJiYoYT0wPmE/YStzOmEtcykpLC0xIT09dC5pbmRleE9mKFwiX2N3XCIpJiYwPmE/YT0oYSs5OTk5OTk5OTk5KnMpJXMtKDB8YS9zKSpzOi0xIT09dC5pbmRleE9mKFwiY2N3XCIpJiZhPjAmJihhPShhLTk5OTk5OTk5OTkqcyklcy0oMHxhL3MpKnMpKSxvPWUrYSksbD5vJiZvPi1sJiYobz0wKSxvfSxuZT17YXF1YTpbMCwyNTUsMjU1XSxsaW1lOlswLDI1NSwwXSxzaWx2ZXI6WzE5MiwxOTIsMTkyXSxibGFjazpbMCwwLDBdLG1hcm9vbjpbMTI4LDAsMF0sdGVhbDpbMCwxMjgsMTI4XSxibHVlOlswLDAsMjU1XSxuYXZ5OlswLDAsMTI4XSx3aGl0ZTpbMjU1LDI1NSwyNTVdLGZ1Y2hzaWE6WzI1NSwwLDI1NV0sb2xpdmU6WzEyOCwxMjgsMF0seWVsbG93OlsyNTUsMjU1LDBdLG9yYW5nZTpbMjU1LDE2NSwwXSxncmF5OlsxMjgsMTI4LDEyOF0scHVycGxlOlsxMjgsMCwxMjhdLGdyZWVuOlswLDEyOCwwXSxyZWQ6WzI1NSwwLDBdLHBpbms6WzI1NSwxOTIsMjAzXSxjeWFuOlswLDI1NSwyNTVdLHRyYW5zcGFyZW50OlsyNTUsMjU1LDI1NSwwXX0sYWU9ZnVuY3Rpb24odCxlLGkpe3JldHVybiB0PTA+dD90KzE6dD4xP3QtMTp0LDB8MjU1KigxPjYqdD9lKzYqKGktZSkqdDouNT50P2k6Mj4zKnQ/ZSs2KihpLWUpKigyLzMtdCk6ZSkrLjV9LG9lPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYTtyZXR1cm4gdCYmXCJcIiE9PXQ/XCJudW1iZXJcIj09dHlwZW9mIHQ/W3Q+PjE2LDI1NSZ0Pj44LDI1NSZ0XTooXCIsXCI9PT10LmNoYXJBdCh0Lmxlbmd0aC0xKSYmKHQ9dC5zdWJzdHIoMCx0Lmxlbmd0aC0xKSksbmVbdF0/bmVbdF06XCIjXCI9PT10LmNoYXJBdCgwKT8oND09PXQubGVuZ3RoJiYoZT10LmNoYXJBdCgxKSxpPXQuY2hhckF0KDIpLHI9dC5jaGFyQXQoMyksdD1cIiNcIitlK2UraStpK3IrciksdD1wYXJzZUludCh0LnN1YnN0cigxKSwxNiksW3Q+PjE2LDI1NSZ0Pj44LDI1NSZ0XSk6XCJoc2xcIj09PXQuc3Vic3RyKDAsMyk/KHQ9dC5tYXRjaChkKSxzPU51bWJlcih0WzBdKSUzNjAvMzYwLG49TnVtYmVyKHRbMV0pLzEwMCxhPU51bWJlcih0WzJdKS8xMDAsaT0uNT49YT9hKihuKzEpOmErbi1hKm4sZT0yKmEtaSx0Lmxlbmd0aD4zJiYodFszXT1OdW1iZXIodFszXSkpLHRbMF09YWUocysxLzMsZSxpKSx0WzFdPWFlKHMsZSxpKSx0WzJdPWFlKHMtMS8zLGUsaSksdCk6KHQ9dC5tYXRjaChkKXx8bmUudHJhbnNwYXJlbnQsdFswXT1OdW1iZXIodFswXSksdFsxXT1OdW1iZXIodFsxXSksdFsyXT1OdW1iZXIodFsyXSksdC5sZW5ndGg+MyYmKHRbM109TnVtYmVyKHRbM10pKSx0KSk6bmUuYmxhY2t9LGxlPVwiKD86XFxcXGIoPzooPzpyZ2J8cmdiYXxoc2x8aHNsYSlcXFxcKC4rP1xcXFwpKXxcXFxcQiMuKz9cXFxcYlwiO2ZvcihsIGluIG5lKWxlKz1cInxcIitsK1wiXFxcXGJcIjtsZT1SZWdFeHAobGUrXCIpXCIsXCJnaVwiKTt2YXIgaGU9ZnVuY3Rpb24odCxlLGkscil7aWYobnVsbD09dClyZXR1cm4gZnVuY3Rpb24odCl7cmV0dXJuIHR9O3ZhciBzLG49ZT8odC5tYXRjaChsZSl8fFtcIlwiXSlbMF06XCJcIixhPXQuc3BsaXQobikuam9pbihcIlwiKS5tYXRjaChnKXx8W10sbz10LnN1YnN0cigwLHQuaW5kZXhPZihhWzBdKSksbD1cIilcIj09PXQuY2hhckF0KHQubGVuZ3RoLTEpP1wiKVwiOlwiXCIsaD0tMSE9PXQuaW5kZXhPZihcIiBcIik/XCIgXCI6XCIsXCIsdT1hLmxlbmd0aCxmPXU+MD9hWzBdLnJlcGxhY2UoZCxcIlwiKTpcIlwiO3JldHVybiB1P3M9ZT9mdW5jdGlvbih0KXt2YXIgZSxfLHAsYztpZihcIm51bWJlclwiPT10eXBlb2YgdCl0Kz1mO2Vsc2UgaWYociYmRC50ZXN0KHQpKXtmb3IoYz10LnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpLHA9MDtjLmxlbmd0aD5wO3ArKyljW3BdPXMoY1twXSk7cmV0dXJuIGMuam9pbihcIixcIil9aWYoZT0odC5tYXRjaChsZSl8fFtuXSlbMF0sXz10LnNwbGl0KGUpLmpvaW4oXCJcIikubWF0Y2goZyl8fFtdLHA9Xy5sZW5ndGgsdT5wLS0pZm9yKDt1PisrcDspX1twXT1pP19bMHwocC0xKS8yXTphW3BdO3JldHVybiBvK18uam9pbihoKStoK2UrbCsoLTEhPT10LmluZGV4T2YoXCJpbnNldFwiKT9cIiBpbnNldFwiOlwiXCIpfTpmdW5jdGlvbih0KXt2YXIgZSxuLF87aWYoXCJudW1iZXJcIj09dHlwZW9mIHQpdCs9ZjtlbHNlIGlmKHImJkQudGVzdCh0KSl7Zm9yKG49dC5yZXBsYWNlKEQsXCJ8XCIpLnNwbGl0KFwifFwiKSxfPTA7bi5sZW5ndGg+XztfKyspbltfXT1zKG5bX10pO3JldHVybiBuLmpvaW4oXCIsXCIpfWlmKGU9dC5tYXRjaChnKXx8W10sXz1lLmxlbmd0aCx1Pl8tLSlmb3IoO3U+KytfOyllW19dPWk/ZVswfChfLTEpLzJdOmFbX107cmV0dXJuIG8rZS5qb2luKGgpK2x9OmZ1bmN0aW9uKHQpe3JldHVybiB0fX0sdWU9ZnVuY3Rpb24odCl7cmV0dXJuIHQ9dC5zcGxpdChcIixcIiksZnVuY3Rpb24oZSxpLHIscyxuLGEsbyl7dmFyIGwsaD0oaStcIlwiKS5zcGxpdChcIiBcIik7Zm9yKG89e30sbD0wOzQ+bDtsKyspb1t0W2xdXT1oW2xdPWhbbF18fGhbKGwtMSkvMj4+MF07cmV0dXJuIHMucGFyc2UoZSxvLG4sYSl9fSxmZT0oRS5fc2V0UGx1Z2luUmF0aW89ZnVuY3Rpb24odCl7dGhpcy5wbHVnaW4uc2V0UmF0aW8odCk7Zm9yKHZhciBlLGkscixzLG49dGhpcy5kYXRhLGE9bi5wcm94eSxvPW4uZmlyc3RNUFQsbD0xZS02O287KWU9YVtvLnZdLG8ucj9lPU1hdGgucm91bmQoZSk6bD5lJiZlPi1sJiYoZT0wKSxvLnRbby5wXT1lLG89by5fbmV4dDtpZihuLmF1dG9Sb3RhdGUmJihuLmF1dG9Sb3RhdGUucm90YXRpb249YS5yb3RhdGlvbiksMT09PXQpZm9yKG89bi5maXJzdE1QVDtvOyl7aWYoaT1vLnQsaS50eXBlKXtpZigxPT09aS50eXBlKXtmb3Iocz1pLnhzMCtpLnMraS54czEscj0xO2kubD5yO3IrKylzKz1pW1wieG5cIityXStpW1wieHNcIisocisxKV07aS5lPXN9fWVsc2UgaS5lPWkucytpLnhzMDtvPW8uX25leHR9fSxmdW5jdGlvbih0LGUsaSxyLHMpe3RoaXMudD10LHRoaXMucD1lLHRoaXMudj1pLHRoaXMucj1zLHImJihyLl9wcmV2PXRoaXMsdGhpcy5fbmV4dD1yKX0pLF9lPShFLl9wYXJzZVRvUHJveHk9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhLG8sbCxoLHUsZj1yLF89e30scD17fSxjPWkuX3RyYW5zZm9ybSxkPU47Zm9yKGkuX3RyYW5zZm9ybT1udWxsLE49ZSxyPXU9aS5wYXJzZSh0LGUscixzKSxOPWQsbiYmKGkuX3RyYW5zZm9ybT1jLGYmJihmLl9wcmV2PW51bGwsZi5fcHJldiYmKGYuX3ByZXYuX25leHQ9bnVsbCkpKTtyJiZyIT09Zjspe2lmKDE+PXIudHlwZSYmKG89ci5wLHBbb109ci5zK3IuYyxfW29dPXIucyxufHwoaD1uZXcgZmUocixcInNcIixvLGgsci5yKSxyLmM9MCksMT09PXIudHlwZSkpZm9yKGE9ci5sOy0tYT4wOylsPVwieG5cIithLG89ci5wK1wiX1wiK2wscFtvXT1yLmRhdGFbbF0sX1tvXT1yW2xdLG58fChoPW5ldyBmZShyLGwsbyxoLHIucnhwW2xdKSk7cj1yLl9uZXh0fXJldHVybntwcm94eTpfLGVuZDpwLGZpcnN0TVBUOmgscHQ6dX19LEUuQ1NTUHJvcFR3ZWVuPWZ1bmN0aW9uKHQsZSxyLHMsYSxvLGwsaCx1LGYsXyl7dGhpcy50PXQsdGhpcy5wPWUsdGhpcy5zPXIsdGhpcy5jPXMsdGhpcy5uPWx8fGUsdCBpbnN0YW5jZW9mIF9lfHxuLnB1c2godGhpcy5uKSx0aGlzLnI9aCx0aGlzLnR5cGU9b3x8MCx1JiYodGhpcy5wcj11LGk9ITApLHRoaXMuYj12b2lkIDA9PT1mP3I6Zix0aGlzLmU9dm9pZCAwPT09Xz9yK3M6XyxhJiYodGhpcy5fbmV4dD1hLGEuX3ByZXY9dGhpcyl9KSxwZT1hLnBhcnNlQ29tcGxleD1mdW5jdGlvbih0LGUsaSxyLHMsbixhLG8sbCx1KXtpPWl8fG58fFwiXCIsYT1uZXcgX2UodCxlLDAsMCxhLHU/MjoxLG51bGwsITEsbyxpLHIpLHIrPVwiXCI7dmFyIGYsXyxwLGMsZyx2LHksVCx3LHgsUCxTLEM9aS5zcGxpdChcIiwgXCIpLmpvaW4oXCIsXCIpLnNwbGl0KFwiIFwiKSxSPXIuc3BsaXQoXCIsIFwiKS5qb2luKFwiLFwiKS5zcGxpdChcIiBcIiksaz1DLmxlbmd0aCxBPWghPT0hMTtmb3IoKC0xIT09ci5pbmRleE9mKFwiLFwiKXx8LTEhPT1pLmluZGV4T2YoXCIsXCIpKSYmKEM9Qy5qb2luKFwiIFwiKS5yZXBsYWNlKEQsXCIsIFwiKS5zcGxpdChcIiBcIiksUj1SLmpvaW4oXCIgXCIpLnJlcGxhY2UoRCxcIiwgXCIpLnNwbGl0KFwiIFwiKSxrPUMubGVuZ3RoKSxrIT09Ui5sZW5ndGgmJihDPShufHxcIlwiKS5zcGxpdChcIiBcIiksaz1DLmxlbmd0aCksYS5wbHVnaW49bCxhLnNldFJhdGlvPXUsZj0wO2s+ZjtmKyspaWYoYz1DW2ZdLGc9UltmXSxUPXBhcnNlRmxvYXQoYyksVHx8MD09PVQpYS5hcHBlbmRYdHJhKFwiXCIsVCxpZShnLFQpLGcucmVwbGFjZShtLFwiXCIpLEEmJi0xIT09Zy5pbmRleE9mKFwicHhcIiksITApO2Vsc2UgaWYocyYmKFwiI1wiPT09Yy5jaGFyQXQoMCl8fG5lW2NdfHxiLnRlc3QoYykpKVM9XCIsXCI9PT1nLmNoYXJBdChnLmxlbmd0aC0xKT9cIiksXCI6XCIpXCIsYz1vZShjKSxnPW9lKGcpLHc9Yy5sZW5ndGgrZy5sZW5ndGg+Nix3JiYhWSYmMD09PWdbM10/KGFbXCJ4c1wiK2EubF0rPWEubD9cIiB0cmFuc3BhcmVudFwiOlwidHJhbnNwYXJlbnRcIixhLmU9YS5lLnNwbGl0KFJbZl0pLmpvaW4oXCJ0cmFuc3BhcmVudFwiKSk6KFl8fCh3PSExKSxhLmFwcGVuZFh0cmEodz9cInJnYmEoXCI6XCJyZ2IoXCIsY1swXSxnWzBdLWNbMF0sXCIsXCIsITAsITApLmFwcGVuZFh0cmEoXCJcIixjWzFdLGdbMV0tY1sxXSxcIixcIiwhMCkuYXBwZW5kWHRyYShcIlwiLGNbMl0sZ1syXS1jWzJdLHc/XCIsXCI6UywhMCksdyYmKGM9ND5jLmxlbmd0aD8xOmNbM10sYS5hcHBlbmRYdHJhKFwiXCIsYywoND5nLmxlbmd0aD8xOmdbM10pLWMsUywhMSkpKTtlbHNlIGlmKHY9Yy5tYXRjaChkKSl7aWYoeT1nLm1hdGNoKG0pLCF5fHx5Lmxlbmd0aCE9PXYubGVuZ3RoKXJldHVybiBhO2ZvcihwPTAsXz0wO3YubGVuZ3RoPl87XysrKVA9dltfXSx4PWMuaW5kZXhPZihQLHApLGEuYXBwZW5kWHRyYShjLnN1YnN0cihwLHgtcCksTnVtYmVyKFApLGllKHlbX10sUCksXCJcIixBJiZcInB4XCI9PT1jLnN1YnN0cih4K1AubGVuZ3RoLDIpLDA9PT1fKSxwPXgrUC5sZW5ndGg7YVtcInhzXCIrYS5sXSs9Yy5zdWJzdHIocCl9ZWxzZSBhW1wieHNcIithLmxdKz1hLmw/XCIgXCIrYzpjO2lmKC0xIT09ci5pbmRleE9mKFwiPVwiKSYmYS5kYXRhKXtmb3IoUz1hLnhzMCthLmRhdGEucyxmPTE7YS5sPmY7ZisrKVMrPWFbXCJ4c1wiK2ZdK2EuZGF0YVtcInhuXCIrZl07YS5lPVMrYVtcInhzXCIrZl19cmV0dXJuIGEubHx8KGEudHlwZT0tMSxhLnhzMD1hLmUpLGEueGZpcnN0fHxhfSxjZT05O2ZvcihsPV9lLnByb3RvdHlwZSxsLmw9bC5wcj0wOy0tY2U+MDspbFtcInhuXCIrY2VdPTAsbFtcInhzXCIrY2VdPVwiXCI7bC54czA9XCJcIixsLl9uZXh0PWwuX3ByZXY9bC54Zmlyc3Q9bC5kYXRhPWwucGx1Z2luPWwuc2V0UmF0aW89bC5yeHA9bnVsbCxsLmFwcGVuZFh0cmE9ZnVuY3Rpb24odCxlLGkscixzLG4pe3ZhciBhPXRoaXMsbz1hLmw7cmV0dXJuIGFbXCJ4c1wiK29dKz1uJiZvP1wiIFwiK3Q6dHx8XCJcIixpfHwwPT09b3x8YS5wbHVnaW4/KGEubCsrLGEudHlwZT1hLnNldFJhdGlvPzI6MSxhW1wieHNcIithLmxdPXJ8fFwiXCIsbz4wPyhhLmRhdGFbXCJ4blwiK29dPWUraSxhLnJ4cFtcInhuXCIrb109cyxhW1wieG5cIitvXT1lLGEucGx1Z2lufHwoYS54Zmlyc3Q9bmV3IF9lKGEsXCJ4blwiK28sZSxpLGEueGZpcnN0fHxhLDAsYS5uLHMsYS5wciksYS54Zmlyc3QueHMwPTApLGEpOihhLmRhdGE9e3M6ZStpfSxhLnJ4cD17fSxhLnM9ZSxhLmM9aSxhLnI9cyxhKSk6KGFbXCJ4c1wiK29dKz1lKyhyfHxcIlwiKSxhKX07dmFyIGRlPWZ1bmN0aW9uKHQsZSl7ZT1lfHx7fSx0aGlzLnA9ZS5wcmVmaXg/Vih0KXx8dDp0LG9bdF09b1t0aGlzLnBdPXRoaXMsdGhpcy5mb3JtYXQ9ZS5mb3JtYXR0ZXJ8fGhlKGUuZGVmYXVsdFZhbHVlLGUuY29sb3IsZS5jb2xsYXBzaWJsZSxlLm11bHRpKSxlLnBhcnNlciYmKHRoaXMucGFyc2U9ZS5wYXJzZXIpLHRoaXMuY2xycz1lLmNvbG9yLHRoaXMubXVsdGk9ZS5tdWx0aSx0aGlzLmtleXdvcmQ9ZS5rZXl3b3JkLHRoaXMuZGZsdD1lLmRlZmF1bHRWYWx1ZSx0aGlzLnByPWUucHJpb3JpdHl8fDB9LG1lPUUuX3JlZ2lzdGVyQ29tcGxleFNwZWNpYWxQcm9wPWZ1bmN0aW9uKHQsZSxpKXtcIm9iamVjdFwiIT10eXBlb2YgZSYmKGU9e3BhcnNlcjppfSk7dmFyIHIscyxuPXQuc3BsaXQoXCIsXCIpLGE9ZS5kZWZhdWx0VmFsdWU7Zm9yKGk9aXx8W2FdLHI9MDtuLmxlbmd0aD5yO3IrKyllLnByZWZpeD0wPT09ciYmZS5wcmVmaXgsZS5kZWZhdWx0VmFsdWU9aVtyXXx8YSxzPW5ldyBkZShuW3JdLGUpfSxnZT1mdW5jdGlvbih0KXtpZighb1t0XSl7dmFyIGU9dC5jaGFyQXQoMCkudG9VcHBlckNhc2UoKSt0LnN1YnN0cigxKStcIlBsdWdpblwiO21lKHQse3BhcnNlcjpmdW5jdGlvbih0LGkscixzLG4sYSxsKXt2YXIgaD0od2luZG93LkdyZWVuU29ja0dsb2JhbHN8fHdpbmRvdykuY29tLmdyZWVuc29jay5wbHVnaW5zW2VdO3JldHVybiBoPyhoLl9jc3NSZWdpc3RlcigpLG9bcl0ucGFyc2UodCxpLHIscyxuLGEsbCkpOihVKFwiRXJyb3I6IFwiK2UrXCIganMgZmlsZSBub3QgbG9hZGVkLlwiKSxuKX19KX19O2w9ZGUucHJvdG90eXBlLGwucGFyc2VDb21wbGV4PWZ1bmN0aW9uKHQsZSxpLHIscyxuKXt2YXIgYSxvLGwsaCx1LGYsXz10aGlzLmtleXdvcmQ7aWYodGhpcy5tdWx0aSYmKEQudGVzdChpKXx8RC50ZXN0KGUpPyhvPWUucmVwbGFjZShELFwifFwiKS5zcGxpdChcInxcIiksbD1pLnJlcGxhY2UoRCxcInxcIikuc3BsaXQoXCJ8XCIpKTpfJiYobz1bZV0sbD1baV0pKSxsKXtmb3IoaD1sLmxlbmd0aD5vLmxlbmd0aD9sLmxlbmd0aDpvLmxlbmd0aCxhPTA7aD5hO2ErKyllPW9bYV09b1thXXx8dGhpcy5kZmx0LGk9bFthXT1sW2FdfHx0aGlzLmRmbHQsXyYmKHU9ZS5pbmRleE9mKF8pLGY9aS5pbmRleE9mKF8pLHUhPT1mJiYoaT0tMT09PWY/bDpvLGlbYV0rPVwiIFwiK18pKTtlPW8uam9pbihcIiwgXCIpLGk9bC5qb2luKFwiLCBcIil9cmV0dXJuIHBlKHQsdGhpcy5wLGUsaSx0aGlzLmNscnMsdGhpcy5kZmx0LHIsdGhpcy5wcixzLG4pfSxsLnBhcnNlPWZ1bmN0aW9uKHQsZSxpLHIsbixhKXtyZXR1cm4gdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSx0aGlzLmZvcm1hdChxKHQsdGhpcy5wLHMsITEsdGhpcy5kZmx0KSksdGhpcy5mb3JtYXQoZSksbixhKX0sYS5yZWdpc3RlclNwZWNpYWxQcm9wPWZ1bmN0aW9uKHQsZSxpKXttZSh0LHtwYXJzZXI6ZnVuY3Rpb24odCxyLHMsbixhLG8pe3ZhciBsPW5ldyBfZSh0LHMsMCwwLGEsMixzLCExLGkpO3JldHVybiBsLnBsdWdpbj1vLGwuc2V0UmF0aW89ZSh0LHIsbi5fdHdlZW4scyksbH0scHJpb3JpdHk6aX0pfTt2YXIgdmU9XCJzY2FsZVgsc2NhbGVZLHNjYWxlWix4LHkseixza2V3WCxza2V3WSxyb3RhdGlvbixyb3RhdGlvblgscm90YXRpb25ZLHBlcnNwZWN0aXZlXCIuc3BsaXQoXCIsXCIpLHllPVYoXCJ0cmFuc2Zvcm1cIiksVGU9VytcInRyYW5zZm9ybVwiLHdlPVYoXCJ0cmFuc2Zvcm1PcmlnaW5cIikseGU9bnVsbCE9PVYoXCJwZXJzcGVjdGl2ZVwiKSxiZT1FLlRyYW5zZm9ybT1mdW5jdGlvbigpe3RoaXMuc2tld1k9MH0sUGU9RS5nZXRUcmFuc2Zvcm09ZnVuY3Rpb24odCxlLGkscil7aWYodC5fZ3NUcmFuc2Zvcm0mJmkmJiFyKXJldHVybiB0Ll9nc1RyYW5zZm9ybTt2YXIgcyxuLG8sbCxoLHUsZixfLHAsYyxkLG0sZyx2PWk/dC5fZ3NUcmFuc2Zvcm18fG5ldyBiZTpuZXcgYmUseT0wPnYuc2NhbGVYLFQ9MmUtNSx3PTFlNSx4PTE3OS45OSxiPXgqTSxQPXhlP3BhcnNlRmxvYXQocSh0LHdlLGUsITEsXCIwIDAgMFwiKS5zcGxpdChcIiBcIilbMl0pfHx2LnpPcmlnaW58fDA6MDtmb3IoeWU/cz1xKHQsVGUsZSwhMCk6dC5jdXJyZW50U3R5bGUmJihzPXQuY3VycmVudFN0eWxlLmZpbHRlci5tYXRjaChBKSxzPXMmJjQ9PT1zLmxlbmd0aD9bc1swXS5zdWJzdHIoNCksTnVtYmVyKHNbMl0uc3Vic3RyKDQpKSxOdW1iZXIoc1sxXS5zdWJzdHIoNCkpLHNbM10uc3Vic3RyKDQpLHYueHx8MCx2Lnl8fDBdLmpvaW4oXCIsXCIpOlwiXCIpLG49KHN8fFwiXCIpLm1hdGNoKC8oPzpcXC18XFxiKVtcXGRcXC1cXC5lXStcXGIvZ2kpfHxbXSxvPW4ubGVuZ3RoOy0tbz4tMTspbD1OdW1iZXIobltvXSksbltvXT0oaD1sLShsfD0wKSk/KDB8aCp3KygwPmg/LS41Oi41KSkvdytsOmw7aWYoMTY9PT1uLmxlbmd0aCl7dmFyIFM9bls4XSxDPW5bOV0sUj1uWzEwXSxrPW5bMTJdLE89blsxM10sRD1uWzE0XTtpZih2LnpPcmlnaW4mJihEPS12LnpPcmlnaW4saz1TKkQtblsxMl0sTz1DKkQtblsxM10sRD1SKkQrdi56T3JpZ2luLW5bMTRdKSwhaXx8cnx8bnVsbD09di5yb3RhdGlvblgpe3ZhciBOLFgseixJLEUsRixZLEI9blswXSxVPW5bMV0sVz1uWzJdLGo9blszXSxWPW5bNF0sSD1uWzVdLFE9bls2XSxaPW5bN10sJD1uWzExXSxHPU1hdGguYXRhbjIoUSxSKSxLPS1iPkd8fEc+Yjt2LnJvdGF0aW9uWD1HKkwsRyYmKEk9TWF0aC5jb3MoLUcpLEU9TWF0aC5zaW4oLUcpLE49VipJK1MqRSxYPUgqSStDKkUsej1RKkkrUipFLFM9ViotRStTKkksQz1IKi1FK0MqSSxSPVEqLUUrUipJLCQ9WiotRSskKkksVj1OLEg9WCxRPXopLEc9TWF0aC5hdGFuMihTLEIpLHYucm90YXRpb25ZPUcqTCxHJiYoRj0tYj5HfHxHPmIsST1NYXRoLmNvcygtRyksRT1NYXRoLnNpbigtRyksTj1CKkktUypFLFg9VSpJLUMqRSx6PVcqSS1SKkUsQz1VKkUrQypJLFI9VypFK1IqSSwkPWoqRSskKkksQj1OLFU9WCxXPXopLEc9TWF0aC5hdGFuMihVLEgpLHYucm90YXRpb249RypMLEcmJihZPS1iPkd8fEc+YixJPU1hdGguY29zKC1HKSxFPU1hdGguc2luKC1HKSxCPUIqSStWKkUsWD1VKkkrSCpFLEg9VSotRStIKkksUT1XKi1FK1EqSSxVPVgpLFkmJks/di5yb3RhdGlvbj12LnJvdGF0aW9uWD0wOlkmJkY/di5yb3RhdGlvbj12LnJvdGF0aW9uWT0wOkYmJksmJih2LnJvdGF0aW9uWT12LnJvdGF0aW9uWD0wKSx2LnNjYWxlWD0oMHxNYXRoLnNxcnQoQipCK1UqVSkqdysuNSkvdyx2LnNjYWxlWT0oMHxNYXRoLnNxcnQoSCpIK0MqQykqdysuNSkvdyx2LnNjYWxlWj0oMHxNYXRoLnNxcnQoUSpRK1IqUikqdysuNSkvdyx2LnNrZXdYPTAsdi5wZXJzcGVjdGl2ZT0kPzEvKDA+JD8tJDokKTowLHYueD1rLHYueT1PLHYuej1EfX1lbHNlIGlmKCEoeGUmJiFyJiZuLmxlbmd0aCYmdi54PT09bls0XSYmdi55PT09bls1XSYmKHYucm90YXRpb25YfHx2LnJvdGF0aW9uWSl8fHZvaWQgMCE9PXYueCYmXCJub25lXCI9PT1xKHQsXCJkaXNwbGF5XCIsZSkpKXt2YXIgSj1uLmxlbmd0aD49Nix0ZT1KP25bMF06MSxlZT1uWzFdfHwwLGllPW5bMl18fDAscmU9Sj9uWzNdOjE7di54PW5bNF18fDAsdi55PW5bNV18fDAsdT1NYXRoLnNxcnQodGUqdGUrZWUqZWUpLGY9TWF0aC5zcXJ0KHJlKnJlK2llKmllKSxfPXRlfHxlZT9NYXRoLmF0YW4yKGVlLHRlKSpMOnYucm90YXRpb258fDAscD1pZXx8cmU/TWF0aC5hdGFuMihpZSxyZSkqTCtfOnYuc2tld1h8fDAsYz11LU1hdGguYWJzKHYuc2NhbGVYfHwwKSxkPWYtTWF0aC5hYnModi5zY2FsZVl8fDApLE1hdGguYWJzKHApPjkwJiYyNzA+TWF0aC5hYnMocCkmJih5Pyh1Kj0tMSxwKz0wPj1fPzE4MDotMTgwLF8rPTA+PV8/MTgwOi0xODApOihmKj0tMSxwKz0wPj1wPzE4MDotMTgwKSksbT0oXy12LnJvdGF0aW9uKSUxODAsZz0ocC12LnNrZXdYKSUxODAsKHZvaWQgMD09PXYuc2tld1h8fGM+VHx8LVQ+Y3x8ZD5UfHwtVD5kfHxtPi14JiZ4Pm0mJmZhbHNlfG0qd3x8Zz4teCYmeD5nJiZmYWxzZXxnKncpJiYodi5zY2FsZVg9dSx2LnNjYWxlWT1mLHYucm90YXRpb249Xyx2LnNrZXdYPXApLHhlJiYodi5yb3RhdGlvblg9di5yb3RhdGlvblk9di56PTAsdi5wZXJzcGVjdGl2ZT1wYXJzZUZsb2F0KGEuZGVmYXVsdFRyYW5zZm9ybVBlcnNwZWN0aXZlKXx8MCx2LnNjYWxlWj0xKX12LnpPcmlnaW49UDtmb3IobyBpbiB2KVQ+dltvXSYmdltvXT4tVCYmKHZbb109MCk7cmV0dXJuIGkmJih0Ll9nc1RyYW5zZm9ybT12KSx2fSxTZT1mdW5jdGlvbih0KXt2YXIgZSxpLHI9dGhpcy5kYXRhLHM9LXIucm90YXRpb24qTSxuPXMrci5za2V3WCpNLGE9MWU1LG89KDB8TWF0aC5jb3Mocykqci5zY2FsZVgqYSkvYSxsPSgwfE1hdGguc2luKHMpKnIuc2NhbGVYKmEpL2EsaD0oMHxNYXRoLnNpbihuKSotci5zY2FsZVkqYSkvYSx1PSgwfE1hdGguY29zKG4pKnIuc2NhbGVZKmEpL2EsZj10aGlzLnQuc3R5bGUsXz10aGlzLnQuY3VycmVudFN0eWxlO2lmKF8pe2k9bCxsPS1oLGg9LWksZT1fLmZpbHRlcixmLmZpbHRlcj1cIlwiO3ZhciBwLGQsbT10aGlzLnQub2Zmc2V0V2lkdGgsZz10aGlzLnQub2Zmc2V0SGVpZ2h0LHY9XCJhYnNvbHV0ZVwiIT09Xy5wb3NpdGlvbix3PVwicHJvZ2lkOkRYSW1hZ2VUcmFuc2Zvcm0uTWljcm9zb2Z0Lk1hdHJpeChNMTE9XCIrbytcIiwgTTEyPVwiK2wrXCIsIE0yMT1cIitoK1wiLCBNMjI9XCIrdSx4PXIueCxiPXIueTtpZihudWxsIT1yLm94JiYocD0oci5veHA/LjAxKm0qci5veDpyLm94KS1tLzIsZD0oci5veXA/LjAxKmcqci5veTpyLm95KS1nLzIseCs9cC0ocCpvK2QqbCksYis9ZC0ocCpoK2QqdSkpLHY/KHA9bS8yLGQ9Zy8yLHcrPVwiLCBEeD1cIisocC0ocCpvK2QqbCkreCkrXCIsIER5PVwiKyhkLShwKmgrZCp1KStiKStcIilcIik6dys9XCIsIHNpemluZ01ldGhvZD0nYXV0byBleHBhbmQnKVwiLGYuZmlsdGVyPS0xIT09ZS5pbmRleE9mKFwiRFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuTWF0cml4KFwiKT9lLnJlcGxhY2UoTyx3KTp3K1wiIFwiK2UsKDA9PT10fHwxPT09dCkmJjE9PT1vJiYwPT09bCYmMD09PWgmJjE9PT11JiYodiYmLTE9PT13LmluZGV4T2YoXCJEeD0wLCBEeT0wXCIpfHxULnRlc3QoZSkmJjEwMCE9PXBhcnNlRmxvYXQoUmVnRXhwLiQxKXx8LTE9PT1lLmluZGV4T2YoXCJncmFkaWVudChcIiYmZS5pbmRleE9mKFwiQWxwaGFcIikpJiZmLnJlbW92ZUF0dHJpYnV0ZShcImZpbHRlclwiKSksIXYpe3ZhciBQLFMsQyxSPTg+Yz8xOi0xO2ZvcihwPXIuaWVPZmZzZXRYfHwwLGQ9ci5pZU9mZnNldFl8fDAsci5pZU9mZnNldFg9TWF0aC5yb3VuZCgobS0oKDA+bz8tbzpvKSptKygwPmw/LWw6bCkqZykpLzIreCksci5pZU9mZnNldFk9TWF0aC5yb3VuZCgoZy0oKDA+dT8tdTp1KSpnKygwPmg/LWg6aCkqbSkpLzIrYiksY2U9MDs0PmNlO2NlKyspUz1KW2NlXSxQPV9bU10saT0tMSE9PVAuaW5kZXhPZihcInB4XCIpP3BhcnNlRmxvYXQoUCk6USh0aGlzLnQsUyxwYXJzZUZsb2F0KFApLFAucmVwbGFjZSh5LFwiXCIpKXx8MCxDPWkhPT1yW1NdPzI+Y2U/LXIuaWVPZmZzZXRYOi1yLmllT2Zmc2V0WToyPmNlP3Atci5pZU9mZnNldFg6ZC1yLmllT2Zmc2V0WSxmW1NdPShyW1NdPU1hdGgucm91bmQoaS1DKigwPT09Y2V8fDI9PT1jZT8xOlIpKSkrXCJweFwifX19LENlPUUuc2V0M0RUcmFuc2Zvcm1SYXRpbz1mdW5jdGlvbih0KXt2YXIgZSxpLHIscyxuLGEsbyxsLGgsdSxmLHAsYyxkLG0sZyx2LHksVCx3LHgsYixQLFM9dGhpcy5kYXRhLEM9dGhpcy50LnN0eWxlLFI9Uy5yb3RhdGlvbipNLGs9Uy5zY2FsZVgsQT1TLnNjYWxlWSxPPVMuc2NhbGVaLEQ9Uy5wZXJzcGVjdGl2ZTtpZighKDEhPT10JiYwIT09dHx8XCJhdXRvXCIhPT1TLmZvcmNlM0R8fFMucm90YXRpb25ZfHxTLnJvdGF0aW9uWHx8MSE9PU98fER8fFMueikpcmV0dXJuIFJlLmNhbGwodGhpcyx0KSx2b2lkIDA7aWYoXyl7dmFyIEw9MWUtNDtMPmsmJms+LUwmJihrPU89MmUtNSksTD5BJiZBPi1MJiYoQT1PPTJlLTUpLCFEfHxTLnp8fFMucm90YXRpb25YfHxTLnJvdGF0aW9uWXx8KEQ9MCl9aWYoUnx8Uy5za2V3WCl5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksZT15LG49VCxTLnNrZXdYJiYoUi09Uy5za2V3WCpNLHk9TWF0aC5jb3MoUiksVD1NYXRoLnNpbihSKSxcInNpbXBsZVwiPT09Uy5za2V3VHlwZSYmKHc9TWF0aC50YW4oUy5za2V3WCpNKSx3PU1hdGguc3FydCgxK3cqdykseSo9dyxUKj13KSksaT0tVCxhPXk7ZWxzZXtpZighKFMucm90YXRpb25ZfHxTLnJvdGF0aW9uWHx8MSE9PU98fEQpKXJldHVybiBDW3llXT1cInRyYW5zbGF0ZTNkKFwiK1MueCtcInB4LFwiK1MueStcInB4LFwiK1MueitcInB4KVwiKygxIT09a3x8MSE9PUE/XCIgc2NhbGUoXCIraytcIixcIitBK1wiKVwiOlwiXCIpLHZvaWQgMDtlPWE9MSxpPW49MH1mPTEscj1zPW89bD1oPXU9cD1jPWQ9MCxtPUQ/LTEvRDowLGc9Uy56T3JpZ2luLHY9MWU1LFI9Uy5yb3RhdGlvblkqTSxSJiYoeT1NYXRoLmNvcyhSKSxUPU1hdGguc2luKFIpLGg9ZiotVCxjPW0qLVQscj1lKlQsbz1uKlQsZio9eSxtKj15LGUqPXksbio9eSksUj1TLnJvdGF0aW9uWCpNLFImJih5PU1hdGguY29zKFIpLFQ9TWF0aC5zaW4oUiksdz1pKnkrcipULHg9YSp5K28qVCxiPXUqeStmKlQsUD1kKnkrbSpULHI9aSotVCtyKnksbz1hKi1UK28qeSxmPXUqLVQrZip5LG09ZCotVCttKnksaT13LGE9eCx1PWIsZD1QKSwxIT09TyYmKHIqPU8sbyo9TyxmKj1PLG0qPU8pLDEhPT1BJiYoaSo9QSxhKj1BLHUqPUEsZCo9QSksMSE9PWsmJihlKj1rLG4qPWssaCo9ayxjKj1rKSxnJiYocC09ZyxzPXIqcCxsPW8qcCxwPWYqcCtnKSxzPSh3PShzKz1TLngpLShzfD0wKSk/KDB8dyp2KygwPnc/LS41Oi41KSkvditzOnMsbD0odz0obCs9Uy55KS0obHw9MCkpPygwfHcqdisoMD53Py0uNTouNSkpL3YrbDpsLHA9KHc9KHArPVMueiktKHB8PTApKT8oMHx3KnYrKDA+dz8tLjU6LjUpKS92K3A6cCxDW3llXT1cIm1hdHJpeDNkKFwiK1soMHxlKnYpL3YsKDB8bip2KS92LCgwfGgqdikvdiwoMHxjKnYpL3YsKDB8aSp2KS92LCgwfGEqdikvdiwoMHx1KnYpL3YsKDB8ZCp2KS92LCgwfHIqdikvdiwoMHxvKnYpL3YsKDB8Zip2KS92LCgwfG0qdikvdixzLGwscCxEPzErLXAvRDoxXS5qb2luKFwiLFwiKStcIilcIn0sUmU9RS5zZXQyRFRyYW5zZm9ybVJhdGlvPWZ1bmN0aW9uKHQpe3ZhciBlLGkscixzLG4sYT10aGlzLmRhdGEsbz10aGlzLnQsbD1vLnN0eWxlO3JldHVybiBhLnJvdGF0aW9uWHx8YS5yb3RhdGlvbll8fGEuenx8YS5mb3JjZTNEPT09ITB8fFwiYXV0b1wiPT09YS5mb3JjZTNEJiYxIT09dCYmMCE9PXQ/KHRoaXMuc2V0UmF0aW89Q2UsQ2UuY2FsbCh0aGlzLHQpLHZvaWQgMCk6KGEucm90YXRpb258fGEuc2tld1g/KGU9YS5yb3RhdGlvbipNLGk9ZS1hLnNrZXdYKk0scj0xZTUscz1hLnNjYWxlWCpyLG49YS5zY2FsZVkqcixsW3llXT1cIm1hdHJpeChcIisoMHxNYXRoLmNvcyhlKSpzKS9yK1wiLFwiKygwfE1hdGguc2luKGUpKnMpL3IrXCIsXCIrKDB8TWF0aC5zaW4oaSkqLW4pL3IrXCIsXCIrKDB8TWF0aC5jb3MoaSkqbikvcitcIixcIithLngrXCIsXCIrYS55K1wiKVwiKTpsW3llXT1cIm1hdHJpeChcIithLnNjYWxlWCtcIiwwLDAsXCIrYS5zY2FsZVkrXCIsXCIrYS54K1wiLFwiK2EueStcIilcIix2b2lkIDApfTttZShcInRyYW5zZm9ybSxzY2FsZSxzY2FsZVgsc2NhbGVZLHNjYWxlWix4LHkseixyb3RhdGlvbixyb3RhdGlvblgscm90YXRpb25ZLHJvdGF0aW9uWixza2V3WCxza2V3WSxzaG9ydFJvdGF0aW9uLHNob3J0Um90YXRpb25YLHNob3J0Um90YXRpb25ZLHNob3J0Um90YXRpb25aLHRyYW5zZm9ybU9yaWdpbix0cmFuc2Zvcm1QZXJzcGVjdGl2ZSxkaXJlY3Rpb25hbFJvdGF0aW9uLHBhcnNlVHJhbnNmb3JtLGZvcmNlM0Qsc2tld1R5cGVcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixvLGwpe2lmKHIuX3RyYW5zZm9ybSlyZXR1cm4gbjt2YXIgaCx1LGYsXyxwLGMsZCxtPXIuX3RyYW5zZm9ybT1QZSh0LHMsITAsbC5wYXJzZVRyYW5zZm9ybSksZz10LnN0eWxlLHY9MWUtNix5PXZlLmxlbmd0aCxUPWwsdz17fTtpZihcInN0cmluZ1wiPT10eXBlb2YgVC50cmFuc2Zvcm0mJnllKWY9ei5zdHlsZSxmW3llXT1ULnRyYW5zZm9ybSxmLmRpc3BsYXk9XCJibG9ja1wiLGYucG9zaXRpb249XCJhYnNvbHV0ZVwiLFguYm9keS5hcHBlbmRDaGlsZCh6KSxoPVBlKHosbnVsbCwhMSksWC5ib2R5LnJlbW92ZUNoaWxkKHopO2Vsc2UgaWYoXCJvYmplY3RcIj09dHlwZW9mIFQpe2lmKGg9e3NjYWxlWDpyZShudWxsIT1ULnNjYWxlWD9ULnNjYWxlWDpULnNjYWxlLG0uc2NhbGVYKSxzY2FsZVk6cmUobnVsbCE9VC5zY2FsZVk/VC5zY2FsZVk6VC5zY2FsZSxtLnNjYWxlWSksc2NhbGVaOnJlKFQuc2NhbGVaLG0uc2NhbGVaKSx4OnJlKFQueCxtLngpLHk6cmUoVC55LG0ueSksejpyZShULnosbS56KSxwZXJzcGVjdGl2ZTpyZShULnRyYW5zZm9ybVBlcnNwZWN0aXZlLG0ucGVyc3BlY3RpdmUpfSxkPVQuZGlyZWN0aW9uYWxSb3RhdGlvbixudWxsIT1kKWlmKFwib2JqZWN0XCI9PXR5cGVvZiBkKWZvcihmIGluIGQpVFtmXT1kW2ZdO2Vsc2UgVC5yb3RhdGlvbj1kO2gucm90YXRpb249c2UoXCJyb3RhdGlvblwiaW4gVD9ULnJvdGF0aW9uOlwic2hvcnRSb3RhdGlvblwiaW4gVD9ULnNob3J0Um90YXRpb24rXCJfc2hvcnRcIjpcInJvdGF0aW9uWlwiaW4gVD9ULnJvdGF0aW9uWjptLnJvdGF0aW9uLG0ucm90YXRpb24sXCJyb3RhdGlvblwiLHcpLHhlJiYoaC5yb3RhdGlvblg9c2UoXCJyb3RhdGlvblhcImluIFQ/VC5yb3RhdGlvblg6XCJzaG9ydFJvdGF0aW9uWFwiaW4gVD9ULnNob3J0Um90YXRpb25YK1wiX3Nob3J0XCI6bS5yb3RhdGlvblh8fDAsbS5yb3RhdGlvblgsXCJyb3RhdGlvblhcIix3KSxoLnJvdGF0aW9uWT1zZShcInJvdGF0aW9uWVwiaW4gVD9ULnJvdGF0aW9uWTpcInNob3J0Um90YXRpb25ZXCJpbiBUP1Quc2hvcnRSb3RhdGlvblkrXCJfc2hvcnRcIjptLnJvdGF0aW9uWXx8MCxtLnJvdGF0aW9uWSxcInJvdGF0aW9uWVwiLHcpKSxoLnNrZXdYPW51bGw9PVQuc2tld1g/bS5za2V3WDpzZShULnNrZXdYLG0uc2tld1gpLGguc2tld1k9bnVsbD09VC5za2V3WT9tLnNrZXdZOnNlKFQuc2tld1ksbS5za2V3WSksKHU9aC5za2V3WS1tLnNrZXdZKSYmKGguc2tld1grPXUsaC5yb3RhdGlvbis9dSl9Zm9yKHhlJiZudWxsIT1ULmZvcmNlM0QmJihtLmZvcmNlM0Q9VC5mb3JjZTNELGM9ITApLG0uc2tld1R5cGU9VC5za2V3VHlwZXx8bS5za2V3VHlwZXx8YS5kZWZhdWx0U2tld1R5cGUscD1tLmZvcmNlM0R8fG0uenx8bS5yb3RhdGlvblh8fG0ucm90YXRpb25ZfHxoLnp8fGgucm90YXRpb25YfHxoLnJvdGF0aW9uWXx8aC5wZXJzcGVjdGl2ZSxwfHxudWxsPT1ULnNjYWxlfHwoaC5zY2FsZVo9MSk7LS15Pi0xOylpPXZlW3ldLF89aFtpXS1tW2ldLChfPnZ8fC12Pl98fG51bGwhPU5baV0pJiYoYz0hMCxuPW5ldyBfZShtLGksbVtpXSxfLG4pLGkgaW4gdyYmKG4uZT13W2ldKSxuLnhzMD0wLG4ucGx1Z2luPW8sci5fb3ZlcndyaXRlUHJvcHMucHVzaChuLm4pKTtyZXR1cm4gXz1ULnRyYW5zZm9ybU9yaWdpbiwoX3x8eGUmJnAmJm0uek9yaWdpbikmJih5ZT8oYz0hMCxpPXdlLF89KF98fHEodCxpLHMsITEsXCI1MCUgNTAlXCIpKStcIlwiLG49bmV3IF9lKGcsaSwwLDAsbiwtMSxcInRyYW5zZm9ybU9yaWdpblwiKSxuLmI9Z1tpXSxuLnBsdWdpbj1vLHhlPyhmPW0uek9yaWdpbixfPV8uc3BsaXQoXCIgXCIpLG0uek9yaWdpbj0oXy5sZW5ndGg+MiYmKDA9PT1mfHxcIjBweFwiIT09X1syXSk/cGFyc2VGbG9hdChfWzJdKTpmKXx8MCxuLnhzMD1uLmU9X1swXStcIiBcIisoX1sxXXx8XCI1MCVcIikrXCIgMHB4XCIsbj1uZXcgX2UobSxcInpPcmlnaW5cIiwwLDAsbiwtMSxuLm4pLG4uYj1mLG4ueHMwPW4uZT1tLnpPcmlnaW4pOm4ueHMwPW4uZT1fKTplZShfK1wiXCIsbSkpLGMmJihyLl90cmFuc2Zvcm1UeXBlPXB8fDM9PT10aGlzLl90cmFuc2Zvcm1UeXBlPzM6Miksbn0scHJlZml4OiEwfSksbWUoXCJib3hTaGFkb3dcIix7ZGVmYXVsdFZhbHVlOlwiMHB4IDBweCAwcHggMHB4ICM5OTlcIixwcmVmaXg6ITAsY29sb3I6ITAsbXVsdGk6ITAsa2V5d29yZDpcImluc2V0XCJ9KSxtZShcImJvcmRlclJhZGl1c1wiLHtkZWZhdWx0VmFsdWU6XCIwcHhcIixwYXJzZXI6ZnVuY3Rpb24odCxlLGksbixhKXtlPXRoaXMuZm9ybWF0KGUpO3ZhciBvLGwsaCx1LGYsXyxwLGMsZCxtLGcsdix5LFQsdyx4LGI9W1wiYm9yZGVyVG9wTGVmdFJhZGl1c1wiLFwiYm9yZGVyVG9wUmlnaHRSYWRpdXNcIixcImJvcmRlckJvdHRvbVJpZ2h0UmFkaXVzXCIsXCJib3JkZXJCb3R0b21MZWZ0UmFkaXVzXCJdLFA9dC5zdHlsZTtmb3IoZD1wYXJzZUZsb2F0KHQub2Zmc2V0V2lkdGgpLG09cGFyc2VGbG9hdCh0Lm9mZnNldEhlaWdodCksbz1lLnNwbGl0KFwiIFwiKSxsPTA7Yi5sZW5ndGg+bDtsKyspdGhpcy5wLmluZGV4T2YoXCJib3JkZXJcIikmJihiW2xdPVYoYltsXSkpLGY9dT1xKHQsYltsXSxzLCExLFwiMHB4XCIpLC0xIT09Zi5pbmRleE9mKFwiIFwiKSYmKHU9Zi5zcGxpdChcIiBcIiksZj11WzBdLHU9dVsxXSksXz1oPW9bbF0scD1wYXJzZUZsb2F0KGYpLHY9Zi5zdWJzdHIoKHArXCJcIikubGVuZ3RoKSx5PVwiPVwiPT09Xy5jaGFyQXQoMSkseT8oYz1wYXJzZUludChfLmNoYXJBdCgwKStcIjFcIiwxMCksXz1fLnN1YnN0cigyKSxjKj1wYXJzZUZsb2F0KF8pLGc9Xy5zdWJzdHIoKGMrXCJcIikubGVuZ3RoLSgwPmM/MTowKSl8fFwiXCIpOihjPXBhcnNlRmxvYXQoXyksZz1fLnN1YnN0cigoYytcIlwiKS5sZW5ndGgpKSxcIlwiPT09ZyYmKGc9cltpXXx8diksZyE9PXYmJihUPVEodCxcImJvcmRlckxlZnRcIixwLHYpLHc9USh0LFwiYm9yZGVyVG9wXCIscCx2KSxcIiVcIj09PWc/KGY9MTAwKihUL2QpK1wiJVwiLHU9MTAwKih3L20pK1wiJVwiKTpcImVtXCI9PT1nPyh4PVEodCxcImJvcmRlckxlZnRcIiwxLFwiZW1cIiksZj1UL3grXCJlbVwiLHU9dy94K1wiZW1cIik6KGY9VCtcInB4XCIsdT13K1wicHhcIikseSYmKF89cGFyc2VGbG9hdChmKStjK2csaD1wYXJzZUZsb2F0KHUpK2MrZykpLGE9cGUoUCxiW2xdLGYrXCIgXCIrdSxfK1wiIFwiK2gsITEsXCIwcHhcIixhKTtyZXR1cm4gYX0scHJlZml4OiEwLGZvcm1hdHRlcjpoZShcIjBweCAwcHggMHB4IDBweFwiLCExLCEwKX0pLG1lKFwiYmFja2dyb3VuZFBvc2l0aW9uXCIse2RlZmF1bHRWYWx1ZTpcIjAgMFwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7dmFyIG8sbCxoLHUsZixfLHA9XCJiYWNrZ3JvdW5kLXBvc2l0aW9uXCIsZD1zfHxIKHQsbnVsbCksbT10aGlzLmZvcm1hdCgoZD9jP2QuZ2V0UHJvcGVydHlWYWx1ZShwK1wiLXhcIikrXCIgXCIrZC5nZXRQcm9wZXJ0eVZhbHVlKHArXCIteVwiKTpkLmdldFByb3BlcnR5VmFsdWUocCk6dC5jdXJyZW50U3R5bGUuYmFja2dyb3VuZFBvc2l0aW9uWCtcIiBcIit0LmN1cnJlbnRTdHlsZS5iYWNrZ3JvdW5kUG9zaXRpb25ZKXx8XCIwIDBcIiksZz10aGlzLmZvcm1hdChlKTtpZigtMSE9PW0uaW5kZXhPZihcIiVcIikhPSgtMSE9PWcuaW5kZXhPZihcIiVcIikpJiYoXz1xKHQsXCJiYWNrZ3JvdW5kSW1hZ2VcIikucmVwbGFjZShDLFwiXCIpLF8mJlwibm9uZVwiIT09Xykpe2ZvcihvPW0uc3BsaXQoXCIgXCIpLGw9Zy5zcGxpdChcIiBcIiksSS5zZXRBdHRyaWJ1dGUoXCJzcmNcIixfKSxoPTI7LS1oPi0xOyltPW9baF0sdT0tMSE9PW0uaW5kZXhPZihcIiVcIiksdSE9PSgtMSE9PWxbaF0uaW5kZXhPZihcIiVcIikpJiYoZj0wPT09aD90Lm9mZnNldFdpZHRoLUkud2lkdGg6dC5vZmZzZXRIZWlnaHQtSS5oZWlnaHQsb1toXT11P3BhcnNlRmxvYXQobSkvMTAwKmYrXCJweFwiOjEwMCoocGFyc2VGbG9hdChtKS9mKStcIiVcIik7bT1vLmpvaW4oXCIgXCIpfXJldHVybiB0aGlzLnBhcnNlQ29tcGxleCh0LnN0eWxlLG0sZyxuLGEpfSxmb3JtYXR0ZXI6ZWV9KSxtZShcImJhY2tncm91bmRTaXplXCIse2RlZmF1bHRWYWx1ZTpcIjAgMFwiLGZvcm1hdHRlcjplZX0pLG1lKFwicGVyc3BlY3RpdmVcIix7ZGVmYXVsdFZhbHVlOlwiMHB4XCIscHJlZml4OiEwfSksbWUoXCJwZXJzcGVjdGl2ZU9yaWdpblwiLHtkZWZhdWx0VmFsdWU6XCI1MCUgNTAlXCIscHJlZml4OiEwfSksbWUoXCJ0cmFuc2Zvcm1TdHlsZVwiLHtwcmVmaXg6ITB9KSxtZShcImJhY2tmYWNlVmlzaWJpbGl0eVwiLHtwcmVmaXg6ITB9KSxtZShcInVzZXJTZWxlY3RcIix7cHJlZml4OiEwfSksbWUoXCJtYXJnaW5cIix7cGFyc2VyOnVlKFwibWFyZ2luVG9wLG1hcmdpblJpZ2h0LG1hcmdpbkJvdHRvbSxtYXJnaW5MZWZ0XCIpfSksbWUoXCJwYWRkaW5nXCIse3BhcnNlcjp1ZShcInBhZGRpbmdUb3AscGFkZGluZ1JpZ2h0LHBhZGRpbmdCb3R0b20scGFkZGluZ0xlZnRcIil9KSxtZShcImNsaXBcIix7ZGVmYXVsdFZhbHVlOlwicmVjdCgwcHgsMHB4LDBweCwwcHgpXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXt2YXIgbyxsLGg7cmV0dXJuIDk+Yz8obD10LmN1cnJlbnRTdHlsZSxoPTg+Yz9cIiBcIjpcIixcIixvPVwicmVjdChcIitsLmNsaXBUb3AraCtsLmNsaXBSaWdodCtoK2wuY2xpcEJvdHRvbStoK2wuY2xpcExlZnQrXCIpXCIsZT10aGlzLmZvcm1hdChlKS5zcGxpdChcIixcIikuam9pbihoKSk6KG89dGhpcy5mb3JtYXQocSh0LHRoaXMucCxzLCExLHRoaXMuZGZsdCkpLGU9dGhpcy5mb3JtYXQoZSkpLHRoaXMucGFyc2VDb21wbGV4KHQuc3R5bGUsbyxlLG4sYSl9fSksbWUoXCJ0ZXh0U2hhZG93XCIse2RlZmF1bHRWYWx1ZTpcIjBweCAwcHggMHB4ICM5OTlcIixjb2xvcjohMCxtdWx0aTohMH0pLG1lKFwiYXV0b1JvdW5kLHN0cmljdFVuaXRzXCIse3BhcnNlcjpmdW5jdGlvbih0LGUsaSxyLHMpe3JldHVybiBzfX0pLG1lKFwiYm9yZGVyXCIse2RlZmF1bHRWYWx1ZTpcIjBweCBzb2xpZCAjMDAwXCIscGFyc2VyOmZ1bmN0aW9uKHQsZSxpLHIsbixhKXtyZXR1cm4gdGhpcy5wYXJzZUNvbXBsZXgodC5zdHlsZSx0aGlzLmZvcm1hdChxKHQsXCJib3JkZXJUb3BXaWR0aFwiLHMsITEsXCIwcHhcIikrXCIgXCIrcSh0LFwiYm9yZGVyVG9wU3R5bGVcIixzLCExLFwic29saWRcIikrXCIgXCIrcSh0LFwiYm9yZGVyVG9wQ29sb3JcIixzLCExLFwiIzAwMFwiKSksdGhpcy5mb3JtYXQoZSksbixhKX0sY29sb3I6ITAsZm9ybWF0dGVyOmZ1bmN0aW9uKHQpe3ZhciBlPXQuc3BsaXQoXCIgXCIpO3JldHVybiBlWzBdK1wiIFwiKyhlWzFdfHxcInNvbGlkXCIpK1wiIFwiKyh0Lm1hdGNoKGxlKXx8W1wiIzAwMFwiXSlbMF19fSksbWUoXCJib3JkZXJXaWR0aFwiLHtwYXJzZXI6dWUoXCJib3JkZXJUb3BXaWR0aCxib3JkZXJSaWdodFdpZHRoLGJvcmRlckJvdHRvbVdpZHRoLGJvcmRlckxlZnRXaWR0aFwiKX0pLG1lKFwiZmxvYXQsY3NzRmxvYXQsc3R5bGVGbG9hdFwiLHtwYXJzZXI6ZnVuY3Rpb24odCxlLGkscixzKXt2YXIgbj10LnN0eWxlLGE9XCJjc3NGbG9hdFwiaW4gbj9cImNzc0Zsb2F0XCI6XCJzdHlsZUZsb2F0XCI7cmV0dXJuIG5ldyBfZShuLGEsMCwwLHMsLTEsaSwhMSwwLG5bYV0sZSl9fSk7dmFyIGtlPWZ1bmN0aW9uKHQpe3ZhciBlLGk9dGhpcy50LHI9aS5maWx0ZXJ8fHEodGhpcy5kYXRhLFwiZmlsdGVyXCIpLHM9MHx0aGlzLnMrdGhpcy5jKnQ7MTAwPT09cyYmKC0xPT09ci5pbmRleE9mKFwiYXRyaXgoXCIpJiYtMT09PXIuaW5kZXhPZihcInJhZGllbnQoXCIpJiYtMT09PXIuaW5kZXhPZihcIm9hZGVyKFwiKT8oaS5yZW1vdmVBdHRyaWJ1dGUoXCJmaWx0ZXJcIiksZT0hcSh0aGlzLmRhdGEsXCJmaWx0ZXJcIikpOihpLmZpbHRlcj1yLnJlcGxhY2UoeCxcIlwiKSxlPSEwKSksZXx8KHRoaXMueG4xJiYoaS5maWx0ZXI9cj1yfHxcImFscGhhKG9wYWNpdHk9XCIrcytcIilcIiksLTE9PT1yLmluZGV4T2YoXCJwYWNpdHlcIik/MD09PXMmJnRoaXMueG4xfHwoaS5maWx0ZXI9citcIiBhbHBoYShvcGFjaXR5PVwiK3MrXCIpXCIpOmkuZmlsdGVyPXIucmVwbGFjZShULFwib3BhY2l0eT1cIitzKSl9O21lKFwib3BhY2l0eSxhbHBoYSxhdXRvQWxwaGFcIix7ZGVmYXVsdFZhbHVlOlwiMVwiLHBhcnNlcjpmdW5jdGlvbih0LGUsaSxyLG4sYSl7dmFyIG89cGFyc2VGbG9hdChxKHQsXCJvcGFjaXR5XCIscywhMSxcIjFcIikpLGw9dC5zdHlsZSxoPVwiYXV0b0FscGhhXCI9PT1pO3JldHVyblwic3RyaW5nXCI9PXR5cGVvZiBlJiZcIj1cIj09PWUuY2hhckF0KDEpJiYoZT0oXCItXCI9PT1lLmNoYXJBdCgwKT8tMToxKSpwYXJzZUZsb2F0KGUuc3Vic3RyKDIpKStvKSxoJiYxPT09byYmXCJoaWRkZW5cIj09PXEodCxcInZpc2liaWxpdHlcIixzKSYmMCE9PWUmJihvPTApLFk/bj1uZXcgX2UobCxcIm9wYWNpdHlcIixvLGUtbyxuKToobj1uZXcgX2UobCxcIm9wYWNpdHlcIiwxMDAqbywxMDAqKGUtbyksbiksbi54bjE9aD8xOjAsbC56b29tPTEsbi50eXBlPTIsbi5iPVwiYWxwaGEob3BhY2l0eT1cIituLnMrXCIpXCIsbi5lPVwiYWxwaGEob3BhY2l0eT1cIisobi5zK24uYykrXCIpXCIsbi5kYXRhPXQsbi5wbHVnaW49YSxuLnNldFJhdGlvPWtlKSxoJiYobj1uZXcgX2UobCxcInZpc2liaWxpdHlcIiwwLDAsbiwtMSxudWxsLCExLDAsMCE9PW8/XCJpbmhlcml0XCI6XCJoaWRkZW5cIiwwPT09ZT9cImhpZGRlblwiOlwiaW5oZXJpdFwiKSxuLnhzMD1cImluaGVyaXRcIixyLl9vdmVyd3JpdGVQcm9wcy5wdXNoKG4ubiksci5fb3ZlcndyaXRlUHJvcHMucHVzaChpKSksbn19KTt2YXIgQWU9ZnVuY3Rpb24odCxlKXtlJiYodC5yZW1vdmVQcm9wZXJ0eT8oXCJtc1wiPT09ZS5zdWJzdHIoMCwyKSYmKGU9XCJNXCIrZS5zdWJzdHIoMSkpLHQucmVtb3ZlUHJvcGVydHkoZS5yZXBsYWNlKFAsXCItJDFcIikudG9Mb3dlckNhc2UoKSkpOnQucmVtb3ZlQXR0cmlidXRlKGUpKX0sT2U9ZnVuY3Rpb24odCl7aWYodGhpcy50Ll9nc0NsYXNzUFQ9dGhpcywxPT09dHx8MD09PXQpe3RoaXMudC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLDA9PT10P3RoaXMuYjp0aGlzLmUpO2Zvcih2YXIgZT10aGlzLmRhdGEsaT10aGlzLnQuc3R5bGU7ZTspZS52P2lbZS5wXT1lLnY6QWUoaSxlLnApLGU9ZS5fbmV4dDsxPT09dCYmdGhpcy50Ll9nc0NsYXNzUFQ9PT10aGlzJiYodGhpcy50Ll9nc0NsYXNzUFQ9bnVsbCl9ZWxzZSB0aGlzLnQuZ2V0QXR0cmlidXRlKFwiY2xhc3NcIikhPT10aGlzLmUmJnRoaXMudC5zZXRBdHRyaWJ1dGUoXCJjbGFzc1wiLHRoaXMuZSl9O21lKFwiY2xhc3NOYW1lXCIse3BhcnNlcjpmdW5jdGlvbih0LGUscixuLGEsbyxsKXt2YXIgaCx1LGYsXyxwLGM9dC5nZXRBdHRyaWJ1dGUoXCJjbGFzc1wiKXx8XCJcIixkPXQuc3R5bGUuY3NzVGV4dDtpZihhPW4uX2NsYXNzTmFtZVBUPW5ldyBfZSh0LHIsMCwwLGEsMiksYS5zZXRSYXRpbz1PZSxhLnByPS0xMSxpPSEwLGEuYj1jLHU9JCh0LHMpLGY9dC5fZ3NDbGFzc1BUKXtmb3IoXz17fSxwPWYuZGF0YTtwOylfW3AucF09MSxwPXAuX25leHQ7Zi5zZXRSYXRpbygxKX1yZXR1cm4gdC5fZ3NDbGFzc1BUPWEsYS5lPVwiPVwiIT09ZS5jaGFyQXQoMSk/ZTpjLnJlcGxhY2UoUmVnRXhwKFwiXFxcXHMqXFxcXGJcIitlLnN1YnN0cigyKStcIlxcXFxiXCIpLFwiXCIpKyhcIitcIj09PWUuY2hhckF0KDApP1wiIFwiK2Uuc3Vic3RyKDIpOlwiXCIpLG4uX3R3ZWVuLl9kdXJhdGlvbiYmKHQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIixhLmUpLGg9Ryh0LHUsJCh0KSxsLF8pLHQuc2V0QXR0cmlidXRlKFwiY2xhc3NcIixjKSxhLmRhdGE9aC5maXJzdE1QVCx0LnN0eWxlLmNzc1RleHQ9ZCxhPWEueGZpcnN0PW4ucGFyc2UodCxoLmRpZnMsYSxvKSksYX19KTt2YXIgRGU9ZnVuY3Rpb24odCl7aWYoKDE9PT10fHwwPT09dCkmJnRoaXMuZGF0YS5fdG90YWxUaW1lPT09dGhpcy5kYXRhLl90b3RhbER1cmF0aW9uJiZcImlzRnJvbVN0YXJ0XCIhPT10aGlzLmRhdGEuZGF0YSl7dmFyIGUsaSxyLHMsbj10aGlzLnQuc3R5bGUsYT1vLnRyYW5zZm9ybS5wYXJzZTtpZihcImFsbFwiPT09dGhpcy5lKW4uY3NzVGV4dD1cIlwiLHM9ITA7ZWxzZSBmb3IoZT10aGlzLmUuc3BsaXQoXCIsXCIpLHI9ZS5sZW5ndGg7LS1yPi0xOylpPWVbcl0sb1tpXSYmKG9baV0ucGFyc2U9PT1hP3M9ITA6aT1cInRyYW5zZm9ybU9yaWdpblwiPT09aT93ZTpvW2ldLnApLEFlKG4saSk7cyYmKEFlKG4seWUpLHRoaXMudC5fZ3NUcmFuc2Zvcm0mJmRlbGV0ZSB0aGlzLnQuX2dzVHJhbnNmb3JtKX19O2ZvcihtZShcImNsZWFyUHJvcHNcIix7cGFyc2VyOmZ1bmN0aW9uKHQsZSxyLHMsbil7cmV0dXJuIG49bmV3IF9lKHQsciwwLDAsbiwyKSxuLnNldFJhdGlvPURlLG4uZT1lLG4ucHI9LTEwLG4uZGF0YT1zLl90d2VlbixpPSEwLG59fSksbD1cImJlemllcix0aHJvd1Byb3BzLHBoeXNpY3NQcm9wcyxwaHlzaWNzMkRcIi5zcGxpdChcIixcIiksY2U9bC5sZW5ndGg7Y2UtLTspZ2UobFtjZV0pO2w9YS5wcm90b3R5cGUsbC5fZmlyc3RQVD1udWxsLGwuX29uSW5pdFR3ZWVuPWZ1bmN0aW9uKHQsZSxvKXtpZighdC5ub2RlVHlwZSlyZXR1cm4hMTt0aGlzLl90YXJnZXQ9dCx0aGlzLl90d2Vlbj1vLHRoaXMuX3ZhcnM9ZSxoPWUuYXV0b1JvdW5kLGk9ITEscj1lLnN1ZmZpeE1hcHx8YS5zdWZmaXhNYXAscz1IKHQsXCJcIiksbj10aGlzLl9vdmVyd3JpdGVQcm9wczt2YXIgbCxfLGMsZCxtLGcsdix5LFQseD10LnN0eWxlO2lmKHUmJlwiXCI9PT14LnpJbmRleCYmKGw9cSh0LFwiekluZGV4XCIscyksKFwiYXV0b1wiPT09bHx8XCJcIj09PWwpJiZ0aGlzLl9hZGRMYXp5U2V0KHgsXCJ6SW5kZXhcIiwwKSksXCJzdHJpbmdcIj09dHlwZW9mIGUmJihkPXguY3NzVGV4dCxsPSQodCxzKSx4LmNzc1RleHQ9ZCtcIjtcIitlLGw9Ryh0LGwsJCh0KSkuZGlmcywhWSYmdy50ZXN0KGUpJiYobC5vcGFjaXR5PXBhcnNlRmxvYXQoUmVnRXhwLiQxKSksZT1sLHguY3NzVGV4dD1kKSx0aGlzLl9maXJzdFBUPV89dGhpcy5wYXJzZSh0LGUsbnVsbCksdGhpcy5fdHJhbnNmb3JtVHlwZSl7Zm9yKFQ9Mz09PXRoaXMuX3RyYW5zZm9ybVR5cGUseWU/ZiYmKHU9ITAsXCJcIj09PXguekluZGV4JiYodj1xKHQsXCJ6SW5kZXhcIixzKSwoXCJhdXRvXCI9PT12fHxcIlwiPT09dikmJnRoaXMuX2FkZExhenlTZXQoeCxcInpJbmRleFwiLDApKSxwJiZ0aGlzLl9hZGRMYXp5U2V0KHgsXCJXZWJraXRCYWNrZmFjZVZpc2liaWxpdHlcIix0aGlzLl92YXJzLldlYmtpdEJhY2tmYWNlVmlzaWJpbGl0eXx8KFQ/XCJ2aXNpYmxlXCI6XCJoaWRkZW5cIikpKTp4Lnpvb209MSxjPV87YyYmYy5fbmV4dDspYz1jLl9uZXh0O3k9bmV3IF9lKHQsXCJ0cmFuc2Zvcm1cIiwwLDAsbnVsbCwyKSx0aGlzLl9saW5rQ1NTUCh5LG51bGwsYykseS5zZXRSYXRpbz1UJiZ4ZT9DZTp5ZT9SZTpTZSx5LmRhdGE9dGhpcy5fdHJhbnNmb3JtfHxQZSh0LHMsITApLG4ucG9wKCl9aWYoaSl7Zm9yKDtfOyl7Zm9yKGc9Xy5fbmV4dCxjPWQ7YyYmYy5wcj5fLnByOyljPWMuX25leHQ7KF8uX3ByZXY9Yz9jLl9wcmV2Om0pP18uX3ByZXYuX25leHQ9XzpkPV8sKF8uX25leHQ9Yyk/Yy5fcHJldj1fOm09XyxfPWd9dGhpcy5fZmlyc3RQVD1kfXJldHVybiEwfSxsLnBhcnNlPWZ1bmN0aW9uKHQsZSxpLG4pe3ZhciBhLGwsdSxmLF8scCxjLGQsbSxnLHY9dC5zdHlsZTtmb3IoYSBpbiBlKXA9ZVthXSxsPW9bYV0sbD9pPWwucGFyc2UodCxwLGEsdGhpcyxpLG4sZSk6KF89cSh0LGEscykrXCJcIixtPVwic3RyaW5nXCI9PXR5cGVvZiBwLFwiY29sb3JcIj09PWF8fFwiZmlsbFwiPT09YXx8XCJzdHJva2VcIj09PWF8fC0xIT09YS5pbmRleE9mKFwiQ29sb3JcIil8fG0mJmIudGVzdChwKT8obXx8KHA9b2UocCkscD0ocC5sZW5ndGg+Mz9cInJnYmEoXCI6XCJyZ2IoXCIpK3Auam9pbihcIixcIikrXCIpXCIpLGk9cGUodixhLF8scCwhMCxcInRyYW5zcGFyZW50XCIsaSwwLG4pKTohbXx8LTE9PT1wLmluZGV4T2YoXCIgXCIpJiYtMT09PXAuaW5kZXhPZihcIixcIik/KHU9cGFyc2VGbG9hdChfKSxjPXV8fDA9PT11P18uc3Vic3RyKCh1K1wiXCIpLmxlbmd0aCk6XCJcIiwoXCJcIj09PV98fFwiYXV0b1wiPT09XykmJihcIndpZHRoXCI9PT1hfHxcImhlaWdodFwiPT09YT8odT10ZSh0LGEscyksYz1cInB4XCIpOlwibGVmdFwiPT09YXx8XCJ0b3BcIj09PWE/KHU9Wih0LGEscyksYz1cInB4XCIpOih1PVwib3BhY2l0eVwiIT09YT8wOjEsYz1cIlwiKSksZz1tJiZcIj1cIj09PXAuY2hhckF0KDEpLGc/KGY9cGFyc2VJbnQocC5jaGFyQXQoMCkrXCIxXCIsMTApLHA9cC5zdWJzdHIoMiksZio9cGFyc2VGbG9hdChwKSxkPXAucmVwbGFjZSh5LFwiXCIpKTooZj1wYXJzZUZsb2F0KHApLGQ9bT9wLnN1YnN0cigoZitcIlwiKS5sZW5ndGgpfHxcIlwiOlwiXCIpLFwiXCI9PT1kJiYoZD1hIGluIHI/clthXTpjKSxwPWZ8fDA9PT1mPyhnP2YrdTpmKStkOmVbYV0sYyE9PWQmJlwiXCIhPT1kJiYoZnx8MD09PWYpJiZ1JiYodT1RKHQsYSx1LGMpLFwiJVwiPT09ZD8odS89USh0LGEsMTAwLFwiJVwiKS8xMDAsZS5zdHJpY3RVbml0cyE9PSEwJiYoXz11K1wiJVwiKSk6XCJlbVwiPT09ZD91Lz1RKHQsYSwxLFwiZW1cIik6XCJweFwiIT09ZCYmKGY9USh0LGEsZixkKSxkPVwicHhcIiksZyYmKGZ8fDA9PT1mKSYmKHA9Zit1K2QpKSxnJiYoZis9dSksIXUmJjAhPT11fHwhZiYmMCE9PWY/dm9pZCAwIT09dlthXSYmKHB8fFwiTmFOXCIhPXArXCJcIiYmbnVsbCE9cCk/KGk9bmV3IF9lKHYsYSxmfHx1fHwwLDAsaSwtMSxhLCExLDAsXyxwKSxpLnhzMD1cIm5vbmVcIiE9PXB8fFwiZGlzcGxheVwiIT09YSYmLTE9PT1hLmluZGV4T2YoXCJTdHlsZVwiKT9wOl8pOlUoXCJpbnZhbGlkIFwiK2ErXCIgdHdlZW4gdmFsdWU6IFwiK2VbYV0pOihpPW5ldyBfZSh2LGEsdSxmLXUsaSwwLGEsaCE9PSExJiYoXCJweFwiPT09ZHx8XCJ6SW5kZXhcIj09PWEpLDAsXyxwKSxpLnhzMD1kKSk6aT1wZSh2LGEsXyxwLCEwLG51bGwsaSwwLG4pKSxuJiZpJiYhaS5wbHVnaW4mJihpLnBsdWdpbj1uKTtyZXR1cm4gaX0sbC5zZXRSYXRpbz1mdW5jdGlvbih0KXt2YXIgZSxpLHIscz10aGlzLl9maXJzdFBULG49MWUtNjtpZigxIT09dHx8dGhpcy5fdHdlZW4uX3RpbWUhPT10aGlzLl90d2Vlbi5fZHVyYXRpb24mJjAhPT10aGlzLl90d2Vlbi5fdGltZSlpZih0fHx0aGlzLl90d2Vlbi5fdGltZSE9PXRoaXMuX3R3ZWVuLl9kdXJhdGlvbiYmMCE9PXRoaXMuX3R3ZWVuLl90aW1lfHx0aGlzLl90d2Vlbi5fcmF3UHJldlRpbWU9PT0tMWUtNilmb3IoO3M7KXtpZihlPXMuYyp0K3MucyxzLnI/ZT1NYXRoLnJvdW5kKGUpOm4+ZSYmZT4tbiYmKGU9MCkscy50eXBlKWlmKDE9PT1zLnR5cGUpaWYocj1zLmwsMj09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMjtlbHNlIGlmKDM9PT1yKXMudFtzLnBdPXMueHMwK2Urcy54czErcy54bjErcy54czIrcy54bjIrcy54czM7ZWxzZSBpZig0PT09cilzLnRbcy5wXT1zLnhzMCtlK3MueHMxK3MueG4xK3MueHMyK3MueG4yK3MueHMzK3MueG4zK3MueHM0O2Vsc2UgaWYoNT09PXIpcy50W3MucF09cy54czArZStzLnhzMStzLnhuMStzLnhzMitzLnhuMitzLnhzMytzLnhuMytzLnhzNCtzLnhuNCtzLnhzNTtlbHNle2ZvcihpPXMueHMwK2Urcy54czEscj0xO3MubD5yO3IrKylpKz1zW1wieG5cIityXStzW1wieHNcIisocisxKV07cy50W3MucF09aX1lbHNlLTE9PT1zLnR5cGU/cy50W3MucF09cy54czA6cy5zZXRSYXRpbyYmcy5zZXRSYXRpbyh0KTtlbHNlIHMudFtzLnBdPWUrcy54czA7cz1zLl9uZXh0fWVsc2UgZm9yKDtzOykyIT09cy50eXBlP3MudFtzLnBdPXMuYjpzLnNldFJhdGlvKHQpLHM9cy5fbmV4dDtlbHNlIGZvcig7czspMiE9PXMudHlwZT9zLnRbcy5wXT1zLmU6cy5zZXRSYXRpbyh0KSxzPXMuX25leHR9LGwuX2VuYWJsZVRyYW5zZm9ybXM9ZnVuY3Rpb24odCl7dGhpcy5fdHJhbnNmb3JtVHlwZT10fHwzPT09dGhpcy5fdHJhbnNmb3JtVHlwZT8zOjIsdGhpcy5fdHJhbnNmb3JtPXRoaXMuX3RyYW5zZm9ybXx8UGUodGhpcy5fdGFyZ2V0LHMsITApfTt2YXIgTWU9ZnVuY3Rpb24oKXt0aGlzLnRbdGhpcy5wXT10aGlzLmUsdGhpcy5kYXRhLl9saW5rQ1NTUCh0aGlzLHRoaXMuX25leHQsbnVsbCwhMCl9O2wuX2FkZExhenlTZXQ9ZnVuY3Rpb24odCxlLGkpe3ZhciByPXRoaXMuX2ZpcnN0UFQ9bmV3IF9lKHQsZSwwLDAsdGhpcy5fZmlyc3RQVCwyKTtyLmU9aSxyLnNldFJhdGlvPU1lLHIuZGF0YT10aGlzfSxsLl9saW5rQ1NTUD1mdW5jdGlvbih0LGUsaSxyKXtyZXR1cm4gdCYmKGUmJihlLl9wcmV2PXQpLHQuX25leHQmJih0Ll9uZXh0Ll9wcmV2PXQuX3ByZXYpLHQuX3ByZXY/dC5fcHJldi5fbmV4dD10Ll9uZXh0OnRoaXMuX2ZpcnN0UFQ9PT10JiYodGhpcy5fZmlyc3RQVD10Ll9uZXh0LHI9ITApLGk/aS5fbmV4dD10OnJ8fG51bGwhPT10aGlzLl9maXJzdFBUfHwodGhpcy5fZmlyc3RQVD10KSx0Ll9uZXh0PWUsdC5fcHJldj1pKSx0fSxsLl9raWxsPWZ1bmN0aW9uKGUpe3ZhciBpLHIscyxuPWU7aWYoZS5hdXRvQWxwaGF8fGUuYWxwaGEpe249e307Zm9yKHIgaW4gZSluW3JdPWVbcl07bi5vcGFjaXR5PTEsbi5hdXRvQWxwaGEmJihuLnZpc2liaWxpdHk9MSl9cmV0dXJuIGUuY2xhc3NOYW1lJiYoaT10aGlzLl9jbGFzc05hbWVQVCkmJihzPWkueGZpcnN0LHMmJnMuX3ByZXY/dGhpcy5fbGlua0NTU1Aocy5fcHJldixpLl9uZXh0LHMuX3ByZXYuX3ByZXYpOnM9PT10aGlzLl9maXJzdFBUJiYodGhpcy5fZmlyc3RQVD1pLl9uZXh0KSxpLl9uZXh0JiZ0aGlzLl9saW5rQ1NTUChpLl9uZXh0LGkuX25leHQuX25leHQscy5fcHJldiksdGhpcy5fY2xhc3NOYW1lUFQ9bnVsbCksdC5wcm90b3R5cGUuX2tpbGwuY2FsbCh0aGlzLG4pfTt2YXIgTGU9ZnVuY3Rpb24odCxlLGkpe3ZhciByLHMsbixhO2lmKHQuc2xpY2UpZm9yKHM9dC5sZW5ndGg7LS1zPi0xOylMZSh0W3NdLGUsaSk7ZWxzZSBmb3Iocj10LmNoaWxkTm9kZXMscz1yLmxlbmd0aDstLXM+LTE7KW49cltzXSxhPW4udHlwZSxuLnN0eWxlJiYoZS5wdXNoKCQobikpLGkmJmkucHVzaChuKSksMSE9PWEmJjkhPT1hJiYxMSE9PWF8fCFuLmNoaWxkTm9kZXMubGVuZ3RofHxMZShuLGUsaSl9O3JldHVybiBhLmNhc2NhZGVUbz1mdW5jdGlvbih0LGkscil7dmFyIHMsbixhLG89ZS50byh0LGksciksbD1bb10saD1bXSx1PVtdLGY9W10sXz1lLl9pbnRlcm5hbHMucmVzZXJ2ZWRQcm9wcztmb3IodD1vLl90YXJnZXRzfHxvLnRhcmdldCxMZSh0LGgsZiksby5yZW5kZXIoaSwhMCksTGUodCx1KSxvLnJlbmRlcigwLCEwKSxvLl9lbmFibGVkKCEwKSxzPWYubGVuZ3RoOy0tcz4tMTspaWYobj1HKGZbc10saFtzXSx1W3NdKSxuLmZpcnN0TVBUKXtuPW4uZGlmcztcclxuZm9yKGEgaW4gcilfW2FdJiYoblthXT1yW2FdKTtsLnB1c2goZS50byhmW3NdLGksbikpfXJldHVybiBsfSx0LmFjdGl2YXRlKFthXSksYX0sITApfSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7IiwiLyohXHJcbiAqIFZFUlNJT046IDEuNy4zXHJcbiAqIERBVEU6IDIwMTQtMDEtMTRcclxuICogVVBEQVRFUyBBTkQgRE9DUyBBVDogaHR0cDovL3d3dy5ncmVlbnNvY2suY29tXHJcbiAqXHJcbiAqIEBsaWNlbnNlIENvcHlyaWdodCAoYykgMjAwOC0yMDE0LCBHcmVlblNvY2suIEFsbCByaWdodHMgcmVzZXJ2ZWQuXHJcbiAqIFRoaXMgd29yayBpcyBzdWJqZWN0IHRvIHRoZSB0ZXJtcyBhdCBodHRwOi8vd3d3LmdyZWVuc29jay5jb20vdGVybXNfb2ZfdXNlLmh0bWwgb3IgZm9yXHJcbiAqIENsdWIgR3JlZW5Tb2NrIG1lbWJlcnMsIHRoZSBzb2Z0d2FyZSBhZ3JlZW1lbnQgdGhhdCB3YXMgaXNzdWVkIHdpdGggeW91ciBtZW1iZXJzaGlwLlxyXG4gKiBcclxuICogQGF1dGhvcjogSmFjayBEb3lsZSwgamFja0BncmVlbnNvY2suY29tXHJcbiAqKi9cclxuKHdpbmRvdy5fZ3NRdWV1ZXx8KHdpbmRvdy5fZ3NRdWV1ZT1bXSkpLnB1c2goZnVuY3Rpb24oKXtcInVzZSBzdHJpY3RcIjt2YXIgdD1kb2N1bWVudC5kb2N1bWVudEVsZW1lbnQsZT13aW5kb3csaT1mdW5jdGlvbihpLHMpe3ZhciByPVwieFwiPT09cz9cIldpZHRoXCI6XCJIZWlnaHRcIixuPVwic2Nyb2xsXCIrcixhPVwiY2xpZW50XCIrcixvPWRvY3VtZW50LmJvZHk7cmV0dXJuIGk9PT1lfHxpPT09dHx8aT09PW8/TWF0aC5tYXgodFtuXSxvW25dKS0oZVtcImlubmVyXCIrcl18fE1hdGgubWF4KHRbYV0sb1thXSkpOmlbbl0taVtcIm9mZnNldFwiK3JdfSxzPXdpbmRvdy5fZ3NEZWZpbmUucGx1Z2luKHtwcm9wTmFtZTpcInNjcm9sbFRvXCIsQVBJOjIsdmVyc2lvbjpcIjEuNy4zXCIsaW5pdDpmdW5jdGlvbih0LHMscil7cmV0dXJuIHRoaXMuX3dkdz10PT09ZSx0aGlzLl90YXJnZXQ9dCx0aGlzLl90d2Vlbj1yLFwib2JqZWN0XCIhPXR5cGVvZiBzJiYocz17eTpzfSksdGhpcy5fYXV0b0tpbGw9cy5hdXRvS2lsbCE9PSExLHRoaXMueD10aGlzLnhQcmV2PXRoaXMuZ2V0WCgpLHRoaXMueT10aGlzLnlQcmV2PXRoaXMuZ2V0WSgpLG51bGwhPXMueD8odGhpcy5fYWRkVHdlZW4odGhpcyxcInhcIix0aGlzLngsXCJtYXhcIj09PXMueD9pKHQsXCJ4XCIpOnMueCxcInNjcm9sbFRvX3hcIiwhMCksdGhpcy5fb3ZlcndyaXRlUHJvcHMucHVzaChcInNjcm9sbFRvX3hcIikpOnRoaXMuc2tpcFg9ITAsbnVsbCE9cy55Pyh0aGlzLl9hZGRUd2Vlbih0aGlzLFwieVwiLHRoaXMueSxcIm1heFwiPT09cy55P2kodCxcInlcIik6cy55LFwic2Nyb2xsVG9feVwiLCEwKSx0aGlzLl9vdmVyd3JpdGVQcm9wcy5wdXNoKFwic2Nyb2xsVG9feVwiKSk6dGhpcy5za2lwWT0hMCwhMH0sc2V0OmZ1bmN0aW9uKHQpe3RoaXMuX3N1cGVyLnNldFJhdGlvLmNhbGwodGhpcyx0KTt2YXIgcz10aGlzLl93ZHd8fCF0aGlzLnNraXBYP3RoaXMuZ2V0WCgpOnRoaXMueFByZXYscj10aGlzLl93ZHd8fCF0aGlzLnNraXBZP3RoaXMuZ2V0WSgpOnRoaXMueVByZXYsbj1yLXRoaXMueVByZXYsYT1zLXRoaXMueFByZXY7dGhpcy5fYXV0b0tpbGwmJighdGhpcy5za2lwWCYmKGE+N3x8LTc+YSkmJmkodGhpcy5fdGFyZ2V0LFwieFwiKT5zJiYodGhpcy5za2lwWD0hMCksIXRoaXMuc2tpcFkmJihuPjd8fC03Pm4pJiZpKHRoaXMuX3RhcmdldCxcInlcIik+ciYmKHRoaXMuc2tpcFk9ITApLHRoaXMuc2tpcFgmJnRoaXMuc2tpcFkmJnRoaXMuX3R3ZWVuLmtpbGwoKSksdGhpcy5fd2R3P2Uuc2Nyb2xsVG8odGhpcy5za2lwWD9zOnRoaXMueCx0aGlzLnNraXBZP3I6dGhpcy55KToodGhpcy5za2lwWXx8KHRoaXMuX3RhcmdldC5zY3JvbGxUb3A9dGhpcy55KSx0aGlzLnNraXBYfHwodGhpcy5fdGFyZ2V0LnNjcm9sbExlZnQ9dGhpcy54KSksdGhpcy54UHJldj10aGlzLngsdGhpcy55UHJldj10aGlzLnl9fSkscj1zLnByb3RvdHlwZTtzLm1heD1pLHIuZ2V0WD1mdW5jdGlvbigpe3JldHVybiB0aGlzLl93ZHc/bnVsbCE9ZS5wYWdlWE9mZnNldD9lLnBhZ2VYT2Zmc2V0Om51bGwhPXQuc2Nyb2xsTGVmdD90LnNjcm9sbExlZnQ6ZG9jdW1lbnQuYm9keS5zY3JvbGxMZWZ0OnRoaXMuX3RhcmdldC5zY3JvbGxMZWZ0fSxyLmdldFk9ZnVuY3Rpb24oKXtyZXR1cm4gdGhpcy5fd2R3P251bGwhPWUucGFnZVlPZmZzZXQ/ZS5wYWdlWU9mZnNldDpudWxsIT10LnNjcm9sbFRvcD90LnNjcm9sbFRvcDpkb2N1bWVudC5ib2R5LnNjcm9sbFRvcDp0aGlzLl90YXJnZXQuc2Nyb2xsVG9wfSxyLl9raWxsPWZ1bmN0aW9uKHQpe3JldHVybiB0LnNjcm9sbFRvX3gmJih0aGlzLnNraXBYPSEwKSx0LnNjcm9sbFRvX3kmJih0aGlzLnNraXBZPSEwKSx0aGlzLl9zdXBlci5fa2lsbC5jYWxsKHRoaXMsdCl9fSksd2luZG93Ll9nc0RlZmluZSYmd2luZG93Ll9nc1F1ZXVlLnBvcCgpKCk7Il19

//# sourceMappingURL=wpr-admin.js.map
