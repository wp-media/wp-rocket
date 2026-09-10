( ( document ) => {
	'use strict';

	function notifyCdnStateChange() {
		document.dispatchEvent( new CustomEvent( 'wpr-cdn-state-change' ) );
	}

	/**
	 * Keeps the hidden cdn_type and cdn_state form inputs in sync after a REST-driven
	 * mode change so a subsequent form save doesn't send stale values and trigger
	 * CdnStateBridge::reconcile() to recompute the wrong state.
	 *
	 * Mirrors the mapping in Rest::apply_cdn_mode():
	 *   byocdn → cdn_type=byocdn; everything else → cdn_type=rocketcdn.
	 *
	 * @param {string} mode New cdn_state value ('rocketcdn_free', 'byocdn', 'nothing', etc.).
	 */
	function syncCdnHiddenInputs( mode ) {
		const stateInput = document.getElementById( 'cdn_state' );
		if ( stateInput ) {
			stateInput.value = mode;
		}

		const typeInput = document.getElementById( 'cdn_type' );
		if ( typeInput ) {
			typeInput.value = 'byocdn' === mode ? 'byocdn' : 'rocketcdn';
		}
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		initCdnDriverTabs();
		initCdnModeToggle();
		initAddHomepage();
		initAddPage();
		initDeletePage();
		updateSubmitButtonStateOnSubscriptionLoading();
	} );

	const addHomeButton = document.querySelector( '#wpr_add_page_component .wpr-cdn-add-page__homepage' );

	/**
	 * Updates the status indicator component with new HTML content.
	 *
	 * @param {string} html - The HTML string to replace the status indicator with.
	 * @returns {void}
	 */
	function updateStatusIndicatorComponent( html ) {
		// #wpr_cdn_status_indicator is shared by the free and paid templates - the free
		// tier additionally wraps it in .wpr-cdn-built-in, the paid tier does not.
		const statusIndicator = document.getElementById( 'wpr_cdn_status_indicator' );
		if ( statusIndicator && html ) {
			statusIndicator.outerHTML = html;
		}
	}

	/**
	 * Updates the "Other CDN" (BYOCDN) status indicator with new HTML content.
	 *
	 * Scoped to the "Your CDN" section container rather than the shared
	 * #wpr_cdn_status_indicator id, since that id can also be present (hidden)
	 * in the RocketCDN section markup at the same time - a global lookup would
	 * risk updating the wrong one. Unlike RocketCDN's indicator, an empty
	 * `html` here means "no status message at all", so the element is removed
	 * rather than left untouched - along with its adjacent separator, which
	 * the initial PHP render (your-own-cdn.php) only draws while the message
	 * is showing.
	 *
	 * @param {string} html - The status indicator HTML to show, or an empty string to show none.
	 * @returns {void}
	 */
	function updateByocdnStatusIndicator( html ) {
		const container = document.querySelector( '.wpr-fieldsContainer-fieldset.your-own-cdn' );

		if ( ! container ) {
			return;
		}

		const existing = container.querySelector( '#wpr_cdn_status_indicator' );
		const separator = container.querySelector( '.wpr-cdn-built-in__separator' );

		if ( existing ) {
			if ( html ) {
				existing.outerHTML = html;
			} else {
				existing.remove();
			}
		} else if ( html ) {
			container.insertAdjacentHTML( 'afterbegin', html );
		}

		if ( ! html ) {
			if ( separator ) {
				separator.remove();
			}

			return;
		}

		if ( ! separator ) {
			const indicator = container.querySelector( '#wpr_cdn_status_indicator' );

			if ( indicator ) {
				indicator.insertAdjacentHTML( 'afterend', '<div class="wpr-cdn-built-in__separator"></div>' );
			}
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
	function updateRocketCDNElementsState( driver, disabled ) {
		if ( 'rocketcdn' === driver ) {
			if ( ! disabled ) {
				document.querySelectorAll( '.cdn-shared-section, .rocketcdn-shared-section' ).forEach( ( el ) => {
					el.classList.remove( 'wpr-cdn-disabled' );
				} );

				return;
			}

			document.querySelectorAll( '.cdn-shared-section, .rocketcdn-shared-section' ).forEach( ( el ) => {
				el.classList.add( 'wpr-cdn-disabled' );
			} );

			return;
		}

		document.querySelectorAll( '.cdn-shared-section' ).forEach( ( el ) => {
			el.classList.remove( 'wpr-cdn-disabled' );
		} );
	}

	/**
	 * Shows or hides the limit-reached tooltip on the ADD PAGE button.
	 *
	 * @param {boolean} limitReached Whether the free-tier page limit has been reached.
	 * @returns {void}
	 */
	function updateTooltipState( limitReached ) {
		const tooltip = document.querySelector( '.wpr-cdn-add-page__button-wrapper .wpr-tooltip' );
		if ( tooltip ) {
			tooltip.classList.toggle( 'wpr-isHidden', ! limitReached );
		}
	}

	/** Pending banner auto-expand timer ID, or null when no timer is active. */
	let autoExpandTimer = null;

	/**
	 * Updates the RocketCDN CTA visibility and expansion state.
	 *
	 * When the page count reaches the limit, expansion is deferred by 15 seconds so the
	 * user has time to react before the upsell banner opens. Any in-flight timer is
	 * cancelled whenever this function is called (e.g. on page deletion), ensuring stale
	 * expands never fire after the state has changed.
	 *
	 * @param {number} count Current number of free-tier pages.
	 * @param {number} limit Free-tier page limit.
	 * @returns {void}
	 */
	function updateRocketCtaState( count, limit ) {
		const cta = document.getElementById( 'wpr-rocketcdn-cta' );
		const resellerBanner = document.getElementById( 'wpr-rocketcdn-reseller-limit-cta' );

		if ( ! cta && ! resellerBanner ) {
			return;
		}

		// Cancel any pending expand — state has changed.
		if ( autoExpandTimer !== null ) {
			clearTimeout( autoExpandTimer );
			autoExpandTimer = null;
		}

		const atLimit  = count >= limit;

		if ( cta ) {
			cta.classList.toggle( 'wpr-isHidden', count === 0 );
		}

		if ( resellerBanner ) {
			resellerBanner.classList.toggle( 'wpr-isHidden', ! atLimit );
		}

		if ( cta ) {
			if ( atLimit ) {
				// Always show "Nice work!" text immediately.
				cta.classList.add( 'wpr-rocketcdn-cta---max-limit' );

				if ( ! cta.classList.contains( 'wpr-rocketcdn-cta--expanded' ) ) {
					// Banner is collapsed — keep it collapsed for 15s then expand.
					cta.classList.add( 'wpr-rocketcdn-cta--collapsed' );
					cta.classList.remove( 'wpr-rocketcdn-cta--expanded' );

					autoExpandTimer = setTimeout( () => {
						autoExpandTimer = null;
						cta.classList.remove( 'wpr-rocketcdn-cta--collapsed' );
						cta.classList.add( 'wpr-rocketcdn-cta--expanded' );
						document.dispatchEvent( new CustomEvent( 'rocketCDNBannerAutoExpanded' ) );
					}, 15000 );
				}
			} else {
				cta.classList.toggle( 'wpr-rocketcdn-cta--collapsed', count > 0 );
				cta.classList.remove( 'wpr-rocketcdn-cta--expanded', 'wpr-rocketcdn-cta---max-limit' );
			}
		}
	}

	// Cancel any pending banner expand when the user navigates away from the CDN tab.
	document.addEventListener( 'rocketJsAfterPageNavigation', ( e ) => {
		if ( e.detail.pageId !== 'page_cdn' && autoExpandTimer !== null ) {
			clearTimeout( autoExpandTimer );
			autoExpandTimer = null;
		}
	} );

	/**
	 * Listens for custom 'rocketJsAfterPageNavigation' event to update the state of the submit button
	 * based on the presence of a CDN subscription loading indicator on the CDN settings page.
	 *
	 * Disables the submit button when navigating to the CDN page if a subscription loading indicator is present,
	 * and re-enables it when navigating away from the CDN page.
	 */
	function updateSubmitButtonStateOnSubscriptionLoading() {
		document.addEventListener( 'rocketJsAfterPageNavigation', ( e ) => {
			// Bail out if submit button is not visible for the current page.
			if (getComputedStyle( e.detail.submitButton ).display === 'none') {
				return;
			}

			const classes = [
				'.wpr-icon-orange-loader',
				'.wpr-cdn-built-in--disabled',
			];

			const allPresent = classes.every( cls => document.querySelector( cls ) !== null );

			// Re-enable submit button when page is not cdn and bail out.
			if (e.detail.pageId !== 'page_cdn') {
				if (e.detail.submitButton.classList.contains( 'wpr-cdn-disabled' )) {
					e.detail.submitButton.classList.remove( 'wpr-cdn-disabled' );
				}

				return;
			}

			// Bail out if no cdn subscription loader is present.
			if ( ! allPresent ) {
				return;
			}

			// Disable submit button when on cdn page and subscription loader is present.
			e.detail.submitButton.classList.add( 'wpr-cdn-disabled' );
		} );
	}

	/**
	 * Sets the subscription loading state on the CDN UI.
	 *
	 * Disables the built-in CDN section, purge and exclude sections.
	 */
	function setSubscriptionLoadingState() {
		const builtIn = document.querySelector( '.wpr-cdn-built-in' );

		if ( builtIn ) {
			builtIn.classList.add( 'wpr-cdn-built-in--disabled' );
		}

		// Disable purge CDN cache section.
		const purgeSection = document.querySelector( '.wpr-cdn-purge.rocketcdn' );

		if ( purgeSection ) {
			purgeSection.classList.add( 'wpr-cdn-disabled' );
		}

		// Disable exclusion fields and section header.
		document.querySelectorAll( '.wpr-cdn-exclusions' ).forEach( ( el ) => {
			el.classList.add( 'wpr-cdn-disabled' );

			const textarea = el.querySelector( 'textarea' );

			if ( textarea ) {
				textarea.disabled = true;
			}
		} );

		const submitButton = document.querySelector( '#wpr-options-submit' );
		if ( submitButton ) {
			submitButton.classList.add( 'wpr-cdn-disabled' );
		}

		// Create polling mechanism to send a request every 10 seconds to get the subscription status and once the subscription is active, we will refresh the page for now.
		document.dispatchEvent(new CustomEvent('rocketCDNSubscriptionLoading', {}));
	}

	/**
	 * Updates the `wpr-cdn-active-indicator` class to reflect which CDN driver header
	 * and tab are active.
	 *
	 * @param {Element|null} activeToggle Toggle whose parent header and matching tab should
	 *                                    receive the class, or null to clear all.
	 */
	function updateCdnActiveIndicator( activeToggle ) {
		document.querySelectorAll( '.wpr-cdn-active-indicator' ).forEach( ( el ) => {
			el.classList.remove( 'wpr-cdn-active-indicator' );
		} );

		if ( activeToggle ) {
			const header = activeToggle.closest( '.wpr-optionHeader' );
			if ( header ) {
				header.classList.add( 'wpr-cdn-active-indicator' );
			}

			const mode = activeToggle.getAttribute( 'data-cdn-mode' );
			const tabDriver = 'byocdn' === mode ? 'your-own-cdn' : 'rocketcdn';
			const tab = document.querySelector( `.wpr-cdn-tabs__tab[data-cdn-driver="${ tabDriver }"]` );

			if ( tab ) {
				tab.classList.add( 'wpr-cdn-active-indicator' );
			}
		}
	}

	/**
	 * Updates the toggle checkboxes and active indicators to reflect RocketCDN Free
	 * having just been activated server-side (auto-activation from the "nothing active"
	 * state, or after a confirmed activation prompt), without a full page reload.
	 */
	function activateFreeModeUI() {
		const freeToggle = document.querySelector( '.wpr-cdn-mode-toggle__input[data-cdn-mode="rocketcdn_free"]' );

		if ( ! freeToggle ) {
			return;
		}

		document.querySelectorAll( '.wpr-cdn-mode-toggle__input' ).forEach( ( other ) => {
			other.checked = ( other === freeToggle );
		} );

		updateCdnActiveIndicator( freeToggle );
		toggleDriverSections( 'rocketcdn' );
		setActiveTab( 'rocketcdn' );
		notifyCdnStateChange();
		syncCdnHiddenInputs( 'rocketcdn_free' );
	}

	/**
	 * Initializes the CDN mode toggle checkboxes.
	 *
	 * Checking activates that mode; unchecking leaves all modes inactive ('nothing').
	 * Only one mode can be active at a time — checking one unchecks the others.
	 * The request fires immediately on toggle change.
	 */
	function initCdnModeToggle() {
		document.addEventListener( 'change', ( event ) => {
			const toggle = event.target.closest( '.wpr-cdn-mode-toggle__input' );

			if ( ! toggle ) {
				return;
			}

			const mode = toggle.getAttribute( 'data-cdn-mode' );

			if ( ! mode ) {
				return;
			}

			// The checkbox has already flipped by the time `change` fires - capture the
			// requested state, then hold the toggle at its previous state and disabled
			// until the request resolves, so nothing about it changes prematurely.
			const requestedChecked = toggle.checked;
			const requestedMode    = requestedChecked ? mode : 'nothing';
			const sectionDriver    = 'byocdn' === mode ? 'your-own-cdn' : 'rocketcdn';
			const toggleWrapper    = toggle.closest( '.wpr-cdn-mode-toggle' );

			toggle.checked  = ! requestedChecked;
			toggle.disabled = true;

			if ( toggleWrapper ) {
				toggleWrapper.classList.add( 'wpr-cdn-mode-toggle--loading' );
			}

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/mode',
				method: 'POST',
				data: { mode: requestedMode },
			} ).then( ( response ) => {
				toggle.checked  = requestedChecked;
				toggle.disabled = false;

				if ( toggleWrapper ) {
					toggleWrapper.classList.remove( 'wpr-cdn-mode-toggle--loading' );
				}

				if ( requestedChecked ) {
					// Uncheck all other mode toggles (mutually exclusive).
					document.querySelectorAll( '.wpr-cdn-mode-toggle__input' ).forEach( ( other ) => {
						if ( other !== toggle ) {
							other.checked = false;
						}
					} );
					toggleDriverSections( sectionDriver );
					setActiveTab( sectionDriver );
				}

				updateCdnActiveIndicator( requestedChecked ? toggle : null );
				notifyCdnStateChange();

				updateRocketCDNElementsState(
					'byocdn' === mode ? 'byocdn' : 'rocketcdn',
					response.disable_rocket_cdn_elements
				);
				syncCdnHiddenInputs( requestedMode );

				// The "Other CDN" toggle only ever shows the static "Your CDN is active on
				// your website" message or nothing - it must not reuse RocketCDN's tiered
				// status_indicator_html, so it's updated separately here.
				if ( 'byocdn' === mode ) {
					updateByocdnStatusIndicator( response.byocdn_status_indicator_html );
				} else {
					refreshUIElements( response );
				}
			} ).catch( () => {
				// Request failed - the toggle still reflects its pre-click state, just re-enable it.
				toggle.disabled = false;

				if ( toggleWrapper ) {
					toggleWrapper.classList.remove( 'wpr-cdn-mode-toggle--loading' );
				}
			} );
		} );
	}

	/**
	 * Toggles visibility of CDN driver sections using the hidden utility class.
	 *
	 * @param {string} activeDriver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
	 */
	function toggleDriverSections( activeDriver ) {
		document.querySelectorAll( '.rocketcdn, .your-own-cdn' ).forEach( ( section ) => {
			section.classList.toggle( 'wpr-isHidden', ! section.classList.contains( activeDriver ) );
		} );
	}

	/**
	 * Updates all .rocketcdn-driver-js spans to reflect the active driver label.
	 *
	 * @param {string} driver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
	 */
	function updateDriverLabel( driver ) {
		const tab = document.querySelector( `.wpr-cdn-tabs__tab[data-cdn-driver="${driver}"]` );

		if ( ! tab ) {
			return;
		}

		const label = tab.getAttribute( 'data-title' );

		if ( ! label ) {
			return;
		}

		document.querySelectorAll( '.rocketcdn-driver-js' ).forEach( ( span ) => {
			span.textContent = label;
		} );
	}

	/**
	 * Updates the "Need Help?" link href for the CDN Exclusions section
	 * to point to the correct docs article for the active driver.
	 *
	 * @param {string} driver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
	 */
	function updateExcludeCdnHelpUrl( driver ) {
		const link = document.querySelector( '.exclude-cdn-help-js' );

		if ( ! link ) {
			return;
		}

		const isRocketCdn = 'rocketcdn' === driver;
		const url = isRocketCdn ? link.dataset.rocketcdnUrl : link.dataset.otherCdnUrl;
		const id  = isRocketCdn ? link.dataset.rocketcdnId  : link.dataset.otherCdnId;

		if ( url ) {
			link.href = url;
		}

		if ( id ) {
			link.dataset.beaconId = id;
		}
	}

	/**
	 * Sets the active driver tab and syncs all tab-dependent UI (label spans, help URL).
	 *
	 * @param {string} driver Active CDN driver slug ('rocketcdn' or 'your-own-cdn').
	 */
	function setActiveTab( driver ) {
		document.querySelectorAll( '.wpr-cdn-tabs__tab' ).forEach( ( t ) => {
			t.classList.toggle( 'wpr-cdn-tabs__tab--active', t.getAttribute( 'data-cdn-driver' ) === driver );
		} );

		updateDriverLabel( driver );
		updateExcludeCdnHelpUrl( driver );
	}

	/**
	 * Initializes CDN driver tab switching behavior.
	 *
	 * Tabs are navigation only — no backend call on click.
	 * Initial driver is derived from the PHP-rendered active-state header
	 * (wpr-cdn-active-indicator), which reflects cdn_state even when the mode
	 * toggle itself is hidden (e.g. rocket_display_cdn_mode_toggle returning
	 * false for a hosting compatibility layer). Falls back to whichever
	 * toggle is checked when no header is marked active (e.g. cdn_state is
	 * 'nothing').
	 */
	function initCdnDriverTabs() {
		const tabs = document.querySelectorAll( '.wpr-cdn-tabs__tab' );

		if ( ! tabs.length ) {
			return;
		}

		tabs.forEach( ( tab ) => {
			tab.addEventListener( 'click', () => {
				const driver = tab.getAttribute( 'data-cdn-driver' );

				if ( ! driver ) {
					return;
				}

				setActiveTab( driver );
				toggleDriverSections( driver );
				notifyCdnStateChange();
			} );
		} );

		const activeHeader = document.querySelector( '.wpr-optionHeader.wpr-cdn-active-indicator' );
		let initialDriver;

		if ( activeHeader ) {
			initialDriver = activeHeader.classList.contains( 'your-own-cdn' ) ? 'your-own-cdn' : 'rocketcdn';
		} else {
			const checkedToggle = document.querySelector( '.wpr-cdn-mode-toggle__input:checked' );
			initialDriver = checkedToggle && 'byocdn' === checkedToggle.getAttribute( 'data-cdn-mode' )
				? 'your-own-cdn'
				: 'rocketcdn';
		}

		setActiveTab( initialDriver );
		toggleDriverSections( initialDriver );
		notifyCdnStateChange();
	}

	/**
	 * Adds a page (or the homepage) to RocketCDN free-tier delivery, transparently
	 * handling the "RocketCDN Free is inactive" activation prompt: on a 409
	 * confirm-required error, shows a native confirmation dialog and retries with
	 * `confirm_activation` if the user accepts. If the server auto-activated Free
	 * (no mode was active at all), updates the toggle UI to reflect it.
	 *
	 * @param {string} path REST path to call ('/wp-rocket/v1/rocketcdn/pages' or '.../pages/homepage').
	 * @param {Object} data Request body data (e.g. { url }).
	 * @returns {Promise} Resolves with the REST response; rejects on final failure or cancellation.
	 */
	function requestAddPage( path, data ) {
		return window.wp.apiFetch( {
			path,
			method: 'POST',
			data,
		} ).then( ( response ) => {
			if ( response.free_activated ) {
				activateFreeModeUI();
			}

			return response;
		} ).catch( ( error ) => {
			if ( 'rocketcdn_free_inactive_confirm_required' === error.code && window.confirm( error.message ) ) {
				return requestAddPage( path, Object.assign( {}, data, { confirm_activation: true } ) );
			}

			throw error;
		} );
	}

	/**
	 * Initializes the "ADD HOMEPAGE" button.
	 *
	 * Sends a POST request to the RocketCDN REST endpoint to add
	 * the site homepage as a free-tier CDN page.
	 */
	function initAddHomepage() {
		document.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '#wpr_add_page_component .wpr-cdn-add-page__homepage' );
			if ( ! button ) {
				return;
			}

			button.disabled = true;

			const builtIn = document.querySelector( '.wpr-cdn-built-in' );

			if ( builtIn ) {
				builtIn.classList.add( 'wpr-cdn-built-in--disabled' );
			}

			requestAddPage( '/wp-rocket/v1/rocketcdn/pages/homepage', {} ).then( ( response ) => {
				button.classList.add( 'wpr-isHidden' );
				updateRocketCtaState( response.count, response.limit );

				if ( builtIn ) {
					builtIn.classList.remove( 'wpr-cdn-built-in--disabled' );
				}

				if ( response.items_html ) {
					const existing = document.querySelector( '.wpr-cdn-built-in .wpr-table-list' );

					if ( existing ) {
						existing.remove();
					}

					const addPageSection = document.querySelector( '.wpr-cdn-add-page' );

					if ( addPageSection ) {
						addPageSection.insertAdjacentHTML( 'beforebegin', response.items_html );
					}
				}

				// Track banner view when first page is added and banner becomes visible.
				if ( 1 === response.count ) {
					document.dispatchEvent( new CustomEvent( 'rocketCDNBannerFirstVisible' ) );
				}

				refreshUIElements(response);
			} ).catch( () => {
				button.disabled = false;

				if ( builtIn ) {
					builtIn.classList.remove( 'wpr-cdn-built-in--disabled' );
				}
			} );
		} );
	}
	/**
	 * Initializes the "ADD PAGE" input and button.
	 *
	 * Sends a POST request to the RocketCDN REST endpoint to add
	 * a page URL to the free-tier CDN page list.
	 */
	function initAddPage() {
		const input = document.getElementById( 'wpr_cdn_add_page_input' );
		const button = document.querySelector( '.wpr-cdn-add-page__button' );

		if ( ! input || ! button ) {
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
			const builtIn = document.querySelector( '.wpr-cdn-built-in' );

			if ( builtIn ) {
				builtIn.classList.add( 'wpr-cdn-built-in--disabled' );
			}

			requestAddPage( '/wp-rocket/v1/rocketcdn/pages', { url } ).then( ( response ) => {
				input.value = '';
				input.disabled = false;
				button.disabled = false;
				addHomeButton.classList.add( 'wpr-isHidden' );
				updateRocketCtaState( response.count, response.limit );

				if ( builtIn ) {
					builtIn.classList.remove( 'wpr-cdn-built-in--disabled' );
				}

				// Update page list with response.
				if ( response.items_html ) {
					const existing = document.querySelector( '.wpr-cdn-built-in .wpr-table-list' );

					if ( existing ) {
						existing.remove();
					}

					const addPageSection = document.querySelector( '.wpr-cdn-add-page' );

					if ( addPageSection ) {
						addPageSection.insertAdjacentHTML( 'beforebegin', response.items_html );
					}
				}

				// Track banner view when first page is added and banner becomes visible.
				if ( 1 === response.count ) {
					document.dispatchEvent( new CustomEvent( 'rocketCDNBannerFirstVisible' ) );
				}

				if ( response.limit === response.count ) {
					// Disable input and button when page limit is reached.
					document.querySelector( '.wpr-cdn-built-in' ).classList.add( 'wpr-cdn-built-in--disabled' );
					const addPageWrapper = document.querySelector( '.wpr-cdn-add-page__button-wrapper' );
					if ( addPageWrapper ) {
						addPageWrapper.classList.add( 'wpr-btn-with-tool-tip' );
					}
					const addPageBtn = document.querySelector( '.wpr-cdn-add-page__button' );
					if ( addPageBtn ) {
						addPageBtn.disabled = true;
					}
					updateTooltipState( true );
					document.dispatchEvent( new CustomEvent( 'rocketCDNBannerAutoExpanded' ) );
				}

				refreshUIElements(response);
			} ).catch( () => {
				input.disabled = false;
				button.disabled = false;

				if ( builtIn ) {
					builtIn.classList.remove( 'wpr-cdn-built-in--disabled' );
				}
			} );
		}

		button.addEventListener( 'click', submitPage );

		input.addEventListener( 'keydown', ( e ) => {
			if ( 'Enter' === e.key ) {
				e.preventDefault();
				submitPage();
			}
		} );
	}

	function refreshUIElements( response ) {
		// Set subscription loading state when first page is added.
		if ( response.is_subscription_creation_loading ) {
			setSubscriptionLoadingState();
		}

		// Only the mode-toggle response carries this - keeps the add-page controls
		// (homepage/add buttons, URL input) in sync with the server's authoritative
		// disabled state (limit reached / subscription loading / forced off) right
		// after enabling or disabling RocketCDN, instead of only on a page reload.
		if ( 'free_add_page_disabled' in response ) {
			const builtIn = document.querySelector( '.wpr-cdn-built-in' );

			if ( builtIn ) {
				builtIn.classList.toggle( 'wpr-cdn-built-in--disabled', response.free_add_page_disabled );
			}
		}

		// Update status indicator component.
		updateStatusIndicatorComponent( response.status_indicator_html );
	}

	/**
	 * Initializes delete buttons for CDN page rows.
	 *
	 * Uses event delegation on the built-in CDN container to handle
	 * clicks on dynamically added delete buttons.
	 */
	function initDeletePage() {
		const container = document.querySelector( '#wpr_add_page_component' );

		if ( ! container ) {
			return;
		}

		container.parentElement.addEventListener( 'click', ( e ) => {
			const button = e.target.closest( '.wpr-table-list__delete' );

			if ( ! button ) {
				return;
			}

			const id = button.dataset.id;

			if ( ! id ) {
				return;
			}

			button.disabled = true;

			window.wp.apiFetch( {
				path: `/wp-rocket/v1/rocketcdn/pages/${ id }`,
				method: 'DELETE',
			} ).then( ( response ) => {
				updateRocketCtaState( response.count, response.limit );

				if ( response.items_html ) {
					const existing = container.parentElement.querySelector( '.wpr-cdn-built-in .wpr-table-list' );

					if ( existing ) {
						existing.remove();
					}

					const addPageSection = container.parentElement.querySelector( '.wpr-cdn-add-page' );

					if ( addPageSection ) {
						addPageSection.insertAdjacentHTML( 'beforebegin', response.items_html );
					}
				}

				// Show re-add HOMEPAGE button when all pages are deleted.
				if ( 0 === response.count ) {
					// Remove table list component.
					document.querySelector( '.wpr-cdn-built-in .wpr-table-list' ).remove();

					const homepageBtn = container.querySelector( '.wpr-cdn-add-page__homepage' );

					if ( homepageBtn ) {
						homepageBtn.classList.remove( 'wpr-isHidden' );
						homepageBtn.disabled = false;
					}
				}

				if ( response.limit > response.count ) {
					// Re-enable input and button when page limit is not reached.
					document.querySelector( '.wpr-cdn-built-in' ).classList.remove( 'wpr-cdn-built-in--disabled' );
					const addPageWrapper = document.querySelector( '.wpr-cdn-add-page__button-wrapper' );
					if ( addPageWrapper ) {
						addPageWrapper.classList.remove( 'wpr-btn-with-tool-tip' );
					}
					const addPageBtn = document.querySelector( '.wpr-cdn-add-page__button' );
					if ( addPageBtn ) {
						addPageBtn.disabled = false;
					}
					updateTooltipState( false );

					// Track auto-collapse when deletion drops count just below the limit.
					if ( response.count === response.limit - 1 ) {
						document.dispatchEvent( new CustomEvent( 'rocketCDNBannerAutoCollapsed' ) );
					}
				}

				// Update status indicator component.
				updateStatusIndicatorComponent( response.status_indicator_html );

			} ).catch( () => {
				button.disabled = false;
			} );
		} );
	}
} )( document );
