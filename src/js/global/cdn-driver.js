( ( document ) => {
	'use strict';

	function notifyCdnStateChange() {
		document.dispatchEvent( new CustomEvent( 'wpr-cdn-state-change' ) );
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		initCdnDriverTabs();
		initCdnModeToggle();
		initAddHomepage();
		initAddPage();
		initDeletePage();
		initNoCnameWarningCta();
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
		const statusIndicator = document.querySelector( '.wpr-cdn-built-in .wpr-cdn-status' );
		if ( statusIndicator && html ) {
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

			// Capture the previously active toggle for rollback on failure.
			// Read from wpr-cdn-active-indicator (maintained by PHP + this function) rather than
			// :checked, because the change event fires after the checkbox state has already flipped.
			const previouslyActiveHeader = document.querySelector( '.wpr-optionHeader.wpr-cdn-active-indicator' );
			const previouslyActive = previouslyActiveHeader
				? previouslyActiveHeader.querySelector( '.wpr-cdn-mode-toggle__input' )
				: null;

			// mode sent to the server: the toggle's mode when checking, 'nothing' when unchecking.
			const requestedMode = toggle.checked ? mode : 'nothing';
			const sectionDriver = 'byocdn' === mode ? 'your-own-cdn' : 'rocketcdn';

			if ( toggle.checked ) {
				// Uncheck all other mode toggles (mutually exclusive).
				document.querySelectorAll( '.wpr-cdn-mode-toggle__input' ).forEach( ( other ) => {
					if ( other !== toggle ) {
						other.checked = false;
					}
				} );
				toggleDriverSections( sectionDriver );
				setActiveTab( sectionDriver );
			}

			// Optimistically update the active indicator before the request completes.
			updateCdnActiveIndicator( toggle.checked ? toggle : null );

			notifyCdnStateChange();

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/mode',
				method: 'POST',
				data: { mode: requestedMode },
			} ).then( ( response ) => {
				updateRocketCDNElementsState(
					'byocdn' === mode ? 'byocdn' : 'rocketcdn',
					response.disable_rocket_cdn_elements
				);
			} ).catch( () => {
				// Revert to previous state on failure.
				toggle.checked = ! toggle.checked;
				updateCdnActiveIndicator( previouslyActive );

				if ( previouslyActive && previouslyActive !== toggle ) {
					previouslyActive.checked = true;
					const prevMode   = previouslyActive.getAttribute( 'data-cdn-mode' );
					const prevDriver = 'byocdn' === prevMode ? 'your-own-cdn' : 'rocketcdn';
					toggleDriverSections( prevDriver );
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
	 * Initial driver is derived from the PHP-rendered checked toggle (cdn_state),
	 * not from cdn_type.
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

		// Derive initial driver from whichever toggle is checked (set by PHP from cdn_state).
		const checkedToggle = document.querySelector( '.wpr-cdn-mode-toggle__input:checked' );
		const initialDriver = checkedToggle && 'byocdn' === checkedToggle.getAttribute( 'data-cdn-mode' )
			? 'your-own-cdn'
			: 'rocketcdn';

		setActiveTab( initialDriver );
		toggleDriverSections( initialDriver );
		notifyCdnStateChange();
	}

	/**
	 * Initializes the "Use RocketCDN Free instead" CTA in the BYOCDN missing-CNAME
	 * warning notice.
	 *
	 * Clicking it switches to the RocketCDN tab by delegating to the existing
	 * RocketCDN tab element, rather than duplicating tab-switch logic.
	 */
	function initNoCnameWarningCta() {
		document.addEventListener( 'click', ( event ) => {
			const cta = event.target.closest( '.wpr-cdn-no-cname-warning__cta' );

			if ( ! cta ) {
				return;
			}

			event.preventDefault();

			const rocketCdnTab = document.querySelector( '.wpr-cdn-tabs__tab[data-cdn-driver="rocketcdn"]' );

			if ( rocketCdnTab ) {
				rocketCdnTab.click();
			}
		} );
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

				// Set subscription loading state when first page is added.
				if ( response.is_subscription_creation_loading ) {
					setSubscriptionLoadingState();
				}

				// Update status indicator component.
				updateStatusIndicatorComponent( response.status_indicator_html );
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

				// Set subscription loading state when first page is added.
				if ( response.is_subscription_creation_loading ) {
					setSubscriptionLoadingState();
				}

				// Update status indicator component.
				updateStatusIndicatorComponent( response.status_indicator_html );
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
