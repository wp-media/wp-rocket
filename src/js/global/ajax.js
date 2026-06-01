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

			// Update the global mixpanel data optin state immediately
			if (typeof rocket_mixpanel_data !== 'undefined') {
				rocket_mixpanel_data.optin_enabled = isChecked ? '1' : '0';
			}

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
	const POLL_BASE_INTERVAL = 2000;   // Start polling at 2 seconds
	const POLL_MAX_INTERVAL = 5000;   // Max polling interval (5 seconds)

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
		const $tableGlobalScore = $('.wpr-ri-urls-table .wpr-global-score');
		if ($tableGlobalScore.length > 0){
			$tableGlobalScore.replaceWith(globalScoreData.row_html);
		}else if ($tableBody.length > 0) {
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

		window.wp.apiFetch(
			{
				path: window.wp.url.addQueryArgs( '/wp-rocket/v1/rocket-insights/pages/progress', { ids: rocketInsightsIds } ),
			}
		).then( ( response ) => {
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
					document.dispatchEvent(new CustomEvent('wprGlobalScoreUpdated', { detail: globalScoreData.data }));
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
		} );
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

		// Clear any previous error notice on new submission attempt.
		const $notice = $( '#wpr-ri-add-url-notice' );
		$notice.text( '' ).addClass( 'hidden' );

		const source = $(this).data('source');

		window.wp.apiFetch(
			{
				path: '/wp-rocket/v1/rocket-insights/pages/',
				method: 'POST',
				data: {
					page_url: pageUrl,
					source: source
				},
			}
		).then( ( response ) => {
			if (response.success) {
				// Clear any previous error notice on success.
				$notice.text( '' ).addClass( 'hidden' );

				if ( ! hasId(response.id) ) {
					$pageUrlInput.val('');
					$tableBody.append(response.html);

					// Custom event when new page is added.
					$(document).trigger('rocket-insights-page-added');

					$table.removeClass('hidden');
					addIds(response.id);
					let pages_num_container = $('#rocket_rocket_insights_pages_num');
					pages_num_container.text( parseInt( pages_num_container.text() ) + 1 );

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
				const errorMessage = response?.message || '';

				// Handle URL limit reached error.
				if ( response?.message && response.message.includes( 'Maximum number of URLs reached' ) ) {
					$pageUrlInput.val( '' );
					// Update UI state to reflect URL limit has been reached.
					disableAddUrlElements();
					// Show quota banner (can_add_pages = false).
					updateQuotaBanner( response.can_add_pages !== undefined ? response.can_add_pages : false );
				} else if ( errorMessage ) {
					// Show the error message in the inline notice without clearing the input,
					// so the user can correct the URL.
					$notice.text( errorMessage ).removeClass( 'hidden' );
				} else {
					// Fallback: clear the input when no message is available.
					$pageUrlInput.val( '' );
				}
			}
		} ).catch( ( error ) => {
			const errorMessage = error?.message || '';
			if ( errorMessage ) {
				$notice.text( errorMessage ).removeClass( 'hidden' );
			}
			console.error( error );
		} );
	}

	function handleResetPage(e) {
		e.preventDefault();

		const $button = $(this);
		let id = $button.parents('.wpr-ri-item').data('rocket-insights-id');
		if ( ! id ) {
			return;
		}

		const source = $button.data('source');

		window.wp.apiFetch(
			{
				path: '/wp-rocket/v1/rocket-insights/pages/' + id,
				method: 'PATCH',
				data: {
					source: source
				}
			}
		).then( ( response ) => {
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
	$(document).on( 'click', '#wpr-action-add_page_speed_radar', handleAddPage );
	$(document).on( 'click', '.wpr-action-speed_radar_refresh', handleResetPage );
	// Handle Enter key press on page url input.
	$(document).on( 'keypress', '#wpr-speed-radar-url-input', function(e) {
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
	$(document).on('click', '.wpr-percentage-score-widget .wpr-ri-add-url-button', function() {
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
			if($('.details-section-td').last().hasClass('wpr-last-expanded')) {
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

		if ( isVisible ) {
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
		$.post(
			ajaxurl,
			{
				action: 'rocket_insight_track_metric_actions',
				_ajax_nonce: rocket_ajax_data.nonce,
				event: event,
				row_id: rowId,
				source: source
			},
			function(response) {
				if (!response.success) {
					console.error('Metric action tracking failed:', response?.data || response);
				}
			}
		);
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

		$selectors.lastToggle
			.removeClass(removeState)
			.addClass(addState);

		$selectors.lastActions
			.removeClass(removeState)
			.addClass(addState);


		// Check if last detail row is not the last row in the table so as not to apply improper styling with border radius between rows.
		var $lastDetailsCell = $('.details-section-td').last();
		if ($lastDetailsCell.closest('tr').next('tr').length !== 0) {
			return;
		}

		$lastDetailsCell.removeClass(removeState)
		.addClass(addState);
	}

	// Toggle single item.
	$(document).on('click', '.wpr-ri-item-toggle-single', function() {
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
	$(document).on('click', '.wpr-ri-report', function(e) {	// Only track if link is not disabled and mixpanel is available.
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
		if ( ! isOnRocketInsights() ) {
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
