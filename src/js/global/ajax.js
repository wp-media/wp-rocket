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

			// Start polling if not already running.addClass('wpr-isLoading');
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
		const $summaryInfo    = $('.wpr-ri-summary-info');
		const isFree  = window.rocket_ajax_data?.is_free === '1';
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

	function updateGlobalScoreRow(globalScoreData){
		if ( isOnRocketInsights() ) {
			const $tableGlobalScore = $('.wpr-ri-urls-table .wpr-global-score');
			if ($tableGlobalScore.length > 0){
				$tableGlobalScore.replaceWith(globalScoreData.row_html);
			}else {
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

		$.get(ajaxurl, {
			ids: rocketInsightsIds,
			action: 'rocket_rocket_insights_get_results',
			_ajax_nonce: rocket_ajax_data.nonce
		}, function(response) {
			if (response.success && Array.isArray(response.data.results)) {
				// Update credit status
				updateCreditState(response.data.has_credit);

				// Update quota banner visibility
				updateQuotaBanner(response.data.can_add_pages);

                // Update global score data and widget when status || page count changes.
                if (globalScoreData.data.status !== response.data.global_score_data.data.status || globalScoreData.data.pages_num !== response.data.global_score_data.data.pages_num) {
                    // Update global score data.
                    globalScoreData = response.data.global_score_data;

                    // Update global score widget if on dashboard.
                    if ( isOnDashboard() ) {
                        $('#wpr_global_score_widget').html(response.data.global_score_data.html);
                    }
					// Update global score row in table if on Rocket Insights page.
					updateGlobalScoreRow(globalScoreData);
                }
				response.data.results.forEach(result => {
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
				console.error('Polling error:', response.data?.results || response);
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
			alert('Please enter a valid URL with an extension');
			return;
		}

		$.post(ajaxurl, {
			page_url: pageUrl,
			action: 'rocket_rocket_insights_add_new_page',
			_ajax_nonce: rocket_ajax_data.nonce
		}, function(response) {
			if (response.success) {
				$pageUrlInput.val('');
				$tableBody.append(response.data.html);
				$table.removeClass('hidden');
				addIds(response.data.id);
				let pages_num_container = $('#rocket_rocket_insights_pages_num');
				pages_num_container.text( parseInt( pages_num_container.text() ) + 1 );

				// Update credit status
				updateCreditState(response.data.has_credit);

                // Update global score data.
                globalScoreData = response.data.global_score_data;

				// Update global score row in table if on Rocket Insights page.
				updateGlobalScoreRow(globalScoreData);

				if ('disabled_btn_html' in globalScoreData) {
					$('#wpr_rocket_insights_add_page_btn_wrapper').html(globalScoreData.disabled_btn_html.rocket_insights);
				}

				// Show/hide quota banner based on can_add_pages
				updateQuotaBanner(response.data.can_add_pages);

				// Start polling if not already running
				if (!pollTimer) {
					pollInterval = POLL_BASE_INTERVAL;
					schedulePolling();
				}
			} else {
				// Clear the input field on error
				$pageUrlInput.val('');

				// Handle URL limit reached error
				if (response.data?.message && response.data.message.includes('Maximum number of URLs reached')) {
					// Update UI state to reflect URL limit has been reached
					disableAddUrlElements();
					// Show quota banner (can_add_pages = false)
					updateQuotaBanner(response.data.can_add_pages !== undefined ? response.data.can_add_pages : false);
				}

				console.error(response.data?.message || response);
			}
		});
	}

	function handleResetPage(e) {
		e.preventDefault();

		let id = $(this).parents('.wpr-ri-item').data('rocket-insights-id');
		if ( ! id ) {
			return;
		}

		$.post(ajaxurl, {
			id,
			action: 'rocket_rocket_insights_reset_page',
			_ajax_nonce: rocket_ajax_data.nonce
		}, function(response) {
			if (response.success) {
				addIds(response.data.id);

				const $row = $(`[data-rocket-insights-id="${response.data.id}"]`);
				$row.replaceWith(response.data.html);

				// Update credit status
				updateCreditState(response.data.has_credit);

				// Update quota banner visibility
				updateQuotaBanner(response.data.can_add_pages);

                // Update global score data.
                globalScoreData = response.data.global_score_data;

				// Update global score row in table if on Rocket Insights page.
				updateGlobalScoreRow(globalScoreData);
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
	$(document).on( 'click', '#wpr-action-add_page_speed_radar', handleAddPage );
	$(document).on( 'click', '.wpr-action-speed_radar_refresh', handleResetPage );
	// Handle Enter key press on page url input.
	$(document).on( 'keypress', $pageUrlInput, function(e) {
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
	$('.wpr-Header-nav a').on('click', function() {
		const id = this.id;
		decideGlobalScoreToUpdate(id);
	});

	// Handle UI update on the rocket insights tab when "Add Pages" button on the global score widget is clicked.
	$(document).on('click', '.wpr-percentage-score-widget .wpr-ri-add-url-button', function() {
		if (!this.textContent.includes('Add Pages')) {
			return;
		}

		// Delay UI update a bit till element is visible.
		setTimeout(() => {
			updateGlobalScoreRow(globalScoreData);
		}, 30);
	});
});
