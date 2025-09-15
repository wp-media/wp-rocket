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
});

document.addEventListener('DOMContentLoaded', function() {
	/**
	 * Performance Monitoring with Progressive Polling.
	 */

		// ==== Configuration ====
	const POLL_BASE_INTERVAL = 5000;   // Start polling at 5 seconds
	const POLL_MAX_INTERVAL = 15000;  // Max polling interval (e.g. 15 seconds)

	// ==== State ====
	let pmIds = Array.isArray(window.rocket_ajax_data?.pm_ids) ? window.rocket_ajax_data.pm_ids.slice() : [];
	let pollInterval = POLL_BASE_INTERVAL;
	let pollTimer = null;
    let globalScoreData = {
        data: {
            status: '',
            score: 0,
            pages_num: 0
        },
        html: ''
    };

	// ==== DOM Selectors ====
	const $pageUrlInput = $('#wpr-speed-radar-url-input');
	const $tableBody = $('.wpr-pma-urls-table tbody');
	const $table = $('.wpr-pma-urls-table');

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
		if (!pmIds.includes(newId)) {
			pmIds.push(newId);
		}
	}

	function removeId(id) {
		pmIds = pmIds.filter(x => x !== parseInt(id, 10));
	}

	function resetPolling() {
		if (pollTimer) {
			clearTimeout(pollTimer);
			pollTimer = null;
		}
		pollInterval = POLL_BASE_INTERVAL;
	}

	function schedulePolling() {
		resetPolling();
		if (pmIds.length > 0) {
			pollTimer = setTimeout(() => {
				getResults();
			}, pollInterval);
		}
	}

	function incrementPolling() {
		pollInterval = Math.min(pollInterval * 1.5, POLL_MAX_INTERVAL); // Exponential backoff
	}

    function isOnDashboard() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page') === 'wprocket' && window.location.hash === '#dashboard';
    }

	// ==== AJAX Handlers ====
	function getResults() {
		if (pmIds.length === 0) {
			resetPolling();
			return;
		}

		$.get(ajaxurl, {
			ids: pmIds,
			action: 'rocket_pm_get_results',
			_ajax_nonce: rocket_ajax_data.nonce
		}, function(response) {
			if (response.success && Array.isArray(response.data.results)) {
                // Update global score data and widget when status || page count changes.
                if (globalScoreData.data.status !== response.data.global_score_data.data.status || globalScoreData.data.pages_num !== response.data.global_score_data.data.pages_num) {
                    // Update global score data.
                    globalScoreData = response.data.global_score_data;

                    // Update global score widget if on dashboard.
                    if ( isOnDashboard() ) {
                        $('#wpr_global_score_widget').html(response.data.global_score_data.html);
                    }
                }
				response.data.results.forEach(result => {
					const $row = $(`[data-rocket-pm-id="${result.id}"]`);
					$row.replaceWith(result.html);

					if (result.status === 'completed' || result.status === 'failed') {
						removeId(result.id);
					}
				});

				incrementPolling();
				schedulePolling();
			} else {
				// On error, clear IDs and stop polling
				pmIds = [];
				resetPolling();
				console.error('Polling error:', response.data?.results || response);
			}
		});
	}

	function handleAddPage(e) {
		e.preventDefault();
		const pageUrl = $pageUrlInput.val().trim();

		if (!isValidUrl(pageUrl)) {
			alert('Please enter a valid URL with an extension');
			return;
		}

		$.post(ajaxurl, {
			page_url: pageUrl,
			action: 'rocket_pm_add_new_page',
			_ajax_nonce: rocket_ajax_data.nonce
		}, function(response) {
			if (response.success) {
				$pageUrlInput.val('');
				$tableBody.append(response.data.html);
				$table.removeClass('hidden');
				addIds(response.data.id);
				let pages_num_container = $('#rocket_pma_pages_num');
				pages_num_container.text( parseInt( pages_num_container.text() ) + 1 );

                // Update global score data.
                globalScoreData = response.data.global_score_data;

				// Start polling if not already running
				if (!pollTimer) {
					pollInterval = POLL_BASE_INTERVAL;
					schedulePolling();
				}
			} else {
				console.error(response.data?.message || response);
			}
		});
	}

	function handleResetPage(e) {
		e.preventDefault();

		let id = $(this).parents('.wpr-pma-item').data('rocketPmId');
		if ( ! id ) {
			return;
		}

		$.post(ajaxurl, {
			id,
			action: 'rocket_pm_reset_page',
			_ajax_nonce: rocket_ajax_data.nonce
		}, function(response) {
			if (response.success) {
				addIds(response.data.id);

				const $row = $(`[data-rocket-pm-id="${response.data.id}"]`);
				$row.replaceWith(response.data.html);

                // Update global score data.
                globalScoreData = response.data.global_score_data;

				// Start polling if not already running
				if (!pollTimer) {
					pollInterval = POLL_BASE_INTERVAL;
					schedulePolling();
				}
			} else {
				console.error(response.data?.message || response);
			}
		});
	}

	// ==== Initialization ====
	// Bind event
	$(document).on( 'click', '#add_page_speed_radar', handleAddPage );
	$(document).on( 'click', '.wpr-action-speed_radar_refresh', handleResetPage );

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
	if (isValidPageForPolling() && pmIds.length > 0) {
		pollInterval = POLL_BASE_INTERVAL;
		schedulePolling();
	}

    // Update global score widget in the cause of a slow polling interval when dashboard menu is clicked.
    $('#wpr-nav-dashboard').on('click', () => {
        if ( '' === globalScoreData.html ) {
            return;
        }

        let  globalScoreWidget = $('#wpr_global_score_widget');

        setTimeout(() => {
            if (globalScoreWidget.length && globalScoreWidget.is(':visible')) {
                // Update global score widget.
                globalScoreWidget.html(globalScoreData.html);
            }
        }, 30); // Wait for 30ms after click for target element to be ready and visible.
    })
});
