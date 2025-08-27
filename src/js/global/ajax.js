var $ = jQuery;
$(document).ready(function(){
    /**
     * Refresh License data
     */
    var _isRefreshing = false;
    $('#wpr-action-refresh_account').on('click', function(e) {
        if(!_isRefreshing){
            var button = $(this);
            var account = $('#wpr-account-data');
            var expire = $('#wpr-expiration-data');

            e.preventDefault();
            _isRefreshing = true;
            button.trigger( 'blur' );
            button.addClass('wpr-isLoading');
            expire.removeClass('wpr-isValid wpr-isInvalid');

            $.post(
                ajaxurl,
                {
                    action: 'rocket_refresh_customer_data',
                    _ajax_nonce: rocket_ajax_data.nonce,
                },
                function(response) {
                    button.removeClass('wpr-isLoading');
                    button.addClass('wpr-isHidden');

                    if ( true === response.success ) {
                        account.html(response.data.license_type);
                        expire.addClass(response.data.license_class).html(response.data.license_expiration);
                        setTimeout(function() {
                            button.removeClass('wpr-icon-refresh wpr-isHidden');
                            button.addClass('wpr-icon-check');
                        }, 250);
                    }
                    else{
                        setTimeout(function() {
                            button.removeClass('wpr-icon-refresh wpr-isHidden');
                            button.addClass('wpr-icon-close');
                        }, 250);
                    }

                    setTimeout(function() {
                        var vTL = new TimelineLite({onComplete:function(){
                            _isRefreshing = false;
                        }})
                          .set(button, {css:{className:'+=wpr-isHidden'}})
                          .set(button, {css:{className:'-=wpr-icon-check'}}, 0.25)
                          .set(button, {css:{className:'-=wpr-icon-close'}})
                          .set(button, {css:{className:'+=wpr-icon-refresh'}}, 0.25)
                          .set(button, {css:{className:'-=wpr-isHidden'}})
                        ;
                    }, 2000);
                }
            );
        }
        return false;
    });

    /**
     * Save Toggle option values on change
     */
    $('.wpr-radio input[type=checkbox]').on('change', function(e) {
        e.preventDefault();
        var name  = $(this).attr('id');
        var value = $(this).prop('checked') ? 1 : 0;

		var excluded = [ 'cloudflare_auto_settings', 'cloudflare_devmode', 'analytics_enabled' ];
		if ( excluded.indexOf( name ) >= 0 ) {
			return;
		}

        $.post(
            ajaxurl,
            {
                action: 'rocket_toggle_option',
                _ajax_nonce: rocket_ajax_data.nonce,
                option: {
                    name: name,
                    value: value
                }
            },
            function(response) {}
        );
	});

	/**
     * Save enable CPCSS for mobiles option.
     */
    $('#wpr-action-rocket_enable_mobile_cpcss').on('click', function(e) {
        e.preventDefault();

		$('#wpr-action-rocket_enable_mobile_cpcss').addClass('wpr-isLoading');

        $.post(
            ajaxurl,
            {
                action: 'rocket_enable_mobile_cpcss',
                _ajax_nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					// Hide Mobile CPCSS btn on success.
					$('#wpr-action-rocket_enable_mobile_cpcss').hide();
					$('.wpr-hide-on-click').hide();
					$('.wpr-show-on-click').show();
					$('#wpr-action-rocket_enable_mobile_cpcss').removeClass('wpr-isLoading');
				}
			}
        );
    });

    /**
     * Save enable Google Fonts Optimization option.
     */
    $('#wpr-action-rocket_enable_google_fonts').on('click', function(e) {
        e.preventDefault();

		$('#wpr-action-rocket_enable_google_fonts').addClass('wpr-isLoading');

        $.post(
            ajaxurl,
            {
                action: 'rocket_enable_google_fonts',
                _ajax_nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					// Hide Mobile CPCSS btn on success.
					$('#wpr-action-rocket_enable_google_fonts').hide();
					$('.wpr-hide-on-click').hide();
					$('.wpr-show-on-click').show();
                    $('#wpr-action-rocket_enable_google_fonts').removeClass('wpr-isLoading');
                    $('#minify_google_fonts').val(1);
				}
			}
        );
    });

    $( '#rocket-dismiss-promotion' ).on( 'click', function( e ) {
        e.preventDefault();

        $.post(
            ajaxurl,
            {
                action: 'rocket_dismiss_promo',
                nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					$('#rocket-promo-banner').hide( 'slow' );
				}
			}
        );
    } );

    $( '#rocket-dismiss-renewal' ).on( 'click', function( e ) {
        e.preventDefault();

        $.post(
            ajaxurl,
            {
                action: 'rocket_dismiss_renewal',
                nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					$('#rocket-renewal-banner').hide( 'slow' );
				}
			}
        );
    } );
	$( '#wpr-update-exclusion-list' ).on( 'click', function( e ) {
		e.preventDefault();
		$('#wpr-update-exclusion-msg').html('');
		$.ajax({
			url: rocket_ajax_data.rest_url,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rocket_ajax_data.rest_nonce );
				xhr.setRequestHeader( 'Accept', 'application/json, */*;q=0.1' );
				xhr.setRequestHeader( 'Content-Type', 'application/json' );
			},
			method: "PUT",
			success: function(responses) {
				let exclusion_msg_container = $('#wpr-update-exclusion-msg');
				exclusion_msg_container.html('');
				if ( undefined !== responses['success'] ) {
					exclusion_msg_container.append( '<div class="notice notice-error">' + responses['message'] + '</div>' );
					return;
				}
				Object.keys( responses ).forEach(( response_key ) => {
					exclusion_msg_container.append( '<strong>' + response_key + ': </strong>' );
					exclusion_msg_container.append( responses[response_key]['message'] );
					exclusion_msg_container.append( '<br>' );
				});
			}
		});
	} );

    /**
     * Enable mobile cache option.
     */
    $('#wpr_enable_mobile_cache').on('click', function(e) {
        e.preventDefault();

		$('#wpr_enable_mobile_cache').addClass('wpr-isLoading');

        $.post(
            ajaxurl,
            {
                action: 'rocket_enable_mobile_cache',
                _ajax_nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					// Hide Mobile cache enable button on success.
					$('#wpr_enable_mobile_cache').hide();
					$('#wpr_mobile_cache_default').hide();
					$('#wpr_mobile_cache_response').show();
                    $('#wpr_enable_mobile_cache').removeClass('wpr-isLoading');

                    // Set values of mobile cache and separate cache files for mobiles option to 1.
                    $('#cache_mobile').val(1);
                    $('#do_caching_mobile_files').val(1);
				}
			}
        );
    });
});

document.addEventListener('DOMContentLoaded', function() {
	const analyticsCheckbox = document.getElementById('analytics_enabled');

	if (analyticsCheckbox) {
		analyticsCheckbox.addEventListener('change', function() {
			const isChecked = this.checked;

			fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'rocket_toggle_optin',
					value: isChecked ? 1 : 0,
					_ajax_nonce: rocket_ajax_data.nonce,
				})
			});
		});
	}

     /**
     * Start Performance Monitoring.
     */

    // Create shared data.
    let pmIds = [];
    let pmCheckInterval;

    // Handle new page addtion event.
    $("#add_page_speed_radar").on('click', (e) => {
        e.preventDefault();
        const pageUrlSelector = $('#wpr-speed-radar-url-input');
        const pageUrl = pageUrlSelector.val();
        // Validate empty input.
        if ('' === pageUrl) {
            alert('No page address was added');
            return;
        }

        // Validate invalid url.
        try {
            const url = new URL(pageUrl);
            // Require a TLD (domain extension)
            if (!url.hostname.includes('.') || '' === url.hostname.split('.').slice(-1)[0]) {
                alert('Please enter a valid url with an extension');
                return;
            }
        } catch (_) {
            alert('Please enter a valid URL.');
            return;
        }

        // Payload
        const payload = {
            page_url: pageUrl,
            action: 'rocket_pm_add_new_page',
            _ajax_nonce: rocket_ajax_data.nonce
        }

        // Send payload.
        $.post(ajaxurl, payload, function (response) {
            if (response.success) {
                // Clear url field.
                pageUrlSelector.val('');

                // Update UI.
                $('.wpr-speed-radar-table tbody').append(response.data.html);

                // Push new added ids to be tracked.
                pmIds.push(response.data.id);

                // Start polling for tracking results every 30s; Only Start Polling if none started.
                if (!pmCheckInterval) {
                pmCheckInterval = setInterval(() => {
                    getResults();
                }, 30000);
                }

                return;
            }
            // Log error response.
            console.log(response.data.message);
        });
    });

    // Handle polling logic.
    const getResults = () => {
        // Stop Polling.
        if (pmIds.length === 0) {
            clearInterval(pmCheckInterval);
            pmCheckInterval = null;
            return;
        }

        // Payload
        const payload = {
            ids: pmIds,
            action: 'rocket_pm_get_results',
            _ajax_nonce: rocket_ajax_data.nonce
        }

        // Get results
        $.get(ajaxurl, payload, function (response) {
            if (response.success && Array.isArray(response.data.results)) {
                response.data.results.forEach(result => {
                // Update row UI
                $(`[data-rocket-pm-id="${result.id}"] .wpr-speed-radar-score`).html(result.status);
        
                // 🔹 Remove completed ids
                if ('completed' === result.status) {
                    $(`[data-rocket-pm-id="${result.id}"] .wpr-speed-radar-score`).html(result.score);
                    pmIds = pmIds.filter(x => x !== parseInt(result.id));
                }
                });

                return;
            }
            // Empty ids: Stop polling in the case of an error response.
            pmIds = [];
            // Log error response.
            console.log(response.data.results);
        });
    }

    /**
     * Handle real time monitoring on refresh
     */
    if ($('.wpr-speed-radar-item-result').length > 0) {
        $('.wpr-speed-radar-item-result').each(function () {
            if ('completed' !== $(this).data('rocket-pm-status')) {
                pmIds.push($(this).data('rocket-pm-id'));
            }
        });
    }

    // Detect if current screen is dashboard.
    let isDashboard = '?page=wprocket#dashboard' === window.location.search + window.location.hash;

    // Keep tracking after page refresh on when on dashboard screen.
    const keepTrackingAfterRefresh = () => {
        if (!pmCheckInterval && 0 !== pmIds.length && isDashboard) {
            pmCheckInterval = setInterval(() => {
                getResults();
            }, 30000);
        }
    }

    keepTrackingAfterRefresh();
});