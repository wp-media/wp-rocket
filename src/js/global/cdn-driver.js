( ( document ) => {
	'use strict';

	function notifyCdnStateChange() {
		document.dispatchEvent( new CustomEvent( 'wpr-cdn-state-change' ) );
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		initCdnDriverTabs();
		initCdnPauseToggle();
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

		const isVisible = count > 0;
		const atLimit  = count >= limit;

		if ( cta ) {
			cta.classList.toggle( 'wpr-isHidden', ! isVisible );
			cta.classList.toggle( 'wpr-rocketcdn-cta--collapsed', isVisible && ! isExpanded );
			cta.classList.toggle( 'wpr-rocketcdn-cta--expanded', isVisible && isExpanded );
			cta.classList.toggle( 'wpr-rocketcdn-cta---max-limit', isVisible && isExpanded );
		}

		if ( resellerBanner ) {
			resellerBanner.classList.toggle( 'wpr-isHidden', ! isExpanded );
		}

		if ( isVisible && atLimit ) {
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
			cta.classList.toggle( 'wpr-rocketcdn-cta--collapsed', isVisible );
			cta.classList.remove( 'wpr-rocketcdn-cta--expanded', 'wpr-rocketcdn-cta---max-limit' );
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
	 * Initializes CDN driver tab switching behavior.
	 *
	 * Toggles visibility of CDN driver sections (built-in-cdn / your-own-cdn)
	 * based on which tab is clicked.
	 */
	function initCdnDriverTabs() {
		const tabs = document.querySelectorAll( '.wpr-cdn-tabs__tab' );
		const driverSections = document.querySelectorAll( '.rocketcdn, .your-own-cdn' );

		if ( ! tabs.length ) {
			return;
		}

		/**
		 * Toggles visibility of CDN driver sections using the hidden utility class.
		 *
		 * @param {string} activeDriver Active CDN driver slug.
		 */
		function toggleDriverSections( activeDriver ) {
			driverSections.forEach( ( section ) => {
				section.classList.toggle( 'wpr-isHidden', ! section.classList.contains( activeDriver ) );
			} );
		}

		/**
		 * Updates all .rocketcdn-driver-js spans to reflect the active driver label.
		 * The label is read from the active tab's data-title attribute, preserving
		 * the original capitalisation set by the PHP translation.
		 *
		 * @param {HTMLElement} activeTab The currently active tab element.
		 */
		function updateDriverLabel( activeTab ) {
			const label = activeTab.getAttribute( 'data-title' );

			if ( ! label ) {
				return;
			}

			document.querySelectorAll( '.rocketcdn-driver-js' ).forEach( ( span ) => {
				// Preserve the original text-transform (uppercase spans stay uppercase via CSS).
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

		tabs.forEach( ( tab ) => {
			tab.addEventListener( 'click', () => {
				const driver = tab.getAttribute( 'data-cdn-driver' );

				if ( ! driver ) {
					return;
				}

				// Update active tab.
				tabs.forEach( ( t ) => t.classList.remove( 'wpr-cdn-tabs__tab--active' ) );
				tab.classList.add( 'wpr-cdn-tabs__tab--active' );

				// Toggle sections: show matching driver, hide others.
				toggleDriverSections( driver );

				// Update dynamic driver label spans.
				updateDriverLabel( tab );
				updateExcludeCdnHelpUrl( driver );
				notifyCdnStateChange();

				// Initial value of the hidden input is set on page load by PHP based on the active driver.
				const cdnTypeInput = document.getElementById('cdn_type');
				let currentValue = cdnTypeInput.value;

				// Persist the active driver selection.
				const driverValue = 'your-own-cdn' === driver ? 'byocdn' : 'rocketcdn';

				window.wp.apiFetch( {
					path: '/wp-rocket/v1/rocketcdn/driver',
					method: 'POST',
					data: { driver: driverValue },
				} ).then((response) => {
					// Updated hidden input value on success.
					cdnTypeInput.value = driverValue;
					
					// Update the state of RocketCDN specific elements based on the selected driver and response from the server.
					updateRocketCDNElementsState( driverValue, response.disable_rocket_cdn_elements );
				} ).catch(() => {
					// Revert active tab and sections on failure.
					cdnTypeInput.value = currentValue;
				} );
			} );
		} );

		// Set initial state from active tab, fallback to rocketcdn.
		const activeTab = document.querySelector( '.wpr-cdn-tabs__tab--active' );
		const activeDriver = activeTab ? activeTab.getAttribute( 'data-cdn-driver' ) : 'rocketcdn';

		if ( activeDriver ) {
			toggleDriverSections( activeDriver );
			notifyCdnStateChange();
		}

		// Set initial label from the active tab.
		if ( activeTab ) {
			updateDriverLabel( activeTab );
			updateExcludeCdnHelpUrl( activeDriver );
		}
	}

	/**
	 * Initializes the CDN pause/resume toggle buttons.
	 *
	 * Toggles between "PAUSE CDN" and "RESUME CDN" states,
	 * swapping the icon via a CSS modifier class.
	 */
	function initCdnPauseToggle() {
		document.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '.wpr-cdn-pause' );
			if ( ! button ) {
				return;
			}

			const isPaused = button.classList.toggle( 'wpr-cdn-pause--paused' );
			button.setAttribute( 'aria-pressed', isPaused ? 'true' : 'false' );
			button.disabled = true;

			const statusDot = document.querySelector( '.rocketcdn .wpr-cdn-indicator__dot' );
			if ( statusDot ) {
				statusDot.className = 'wpr-icon-orange-loader';
			}

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/pause',
				method: 'POST',
				data: { paused: isPaused ? 0 : 1 },
			} ).then( () => {
				// Remove the loader.
				if ( statusDot ) {
					statusDot.className = 'wpr-cdn-indicator__dot';
				}

				button.disabled = false;

				// Simulate real click to prepare checkbox state for form submission.
				document.querySelector('label[for="cdn"]').click();

				updateRocketCDNElementsState( 'rocketcdn', isPaused );

				const statusContainer = button.closest( '.wpr-cdn-status' );
				if ( ! statusContainer ) {
					return;
				}

				statusContainer.classList.toggle( 'wpr-cdn-status--paused', isPaused );
				statusContainer.classList.toggle(
					'wpr-cdn-status--long-details',
					isPaused && '1' === statusContainer.dataset.longDetails
				);

				const builtIn = statusContainer.closest( '.wpr-cdn-built-in' );
				if ( builtIn ) {
					builtIn.classList.toggle( 'wpr-cdn-built-in--paused', isPaused );
				}

				notifyCdnStateChange();

				const textKey = isPaused ? 'pausedText' : 'activeText';

				const statusText = statusContainer.querySelector( '.wpr-cdn-indicator__text' );

				if ( statusText && statusContainer.dataset[ textKey ] ) {
					statusText.textContent = statusContainer.dataset[ textKey ];
				}

				const detailsKey = isPaused ? 'pausedDetails' : 'activeDetails';
				const detailsEl = statusContainer.querySelector( '.wpr-cdn-indicator__details' );

				if ( detailsEl && statusContainer.dataset[ detailsKey ] ) {
					detailsEl.textContent = statusContainer.dataset[ detailsKey ];
				}
			} ).catch( () => {
				// Revert toggle on failure.
				button.classList.toggle( 'wpr-cdn-pause--paused', ! isPaused );
				button.setAttribute( 'aria-pressed', ! isPaused ? 'true' : 'false' );
				button.disabled = false;

				// Remove the loader.
				if ( statusDot ) {
					statusDot.className = 'wpr-cdn-indicator__dot';
				}
			} );
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

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/pages/homepage',
				method: 'POST',
			} ).then( ( response ) => {
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

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/pages',
				method: 'POST',
				data: { url },
			} ).then( ( response ) => {
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

				if ( 0 === response.count ) {
					// Update status inidicator component
					updateStatusIndicatorComponent( response.status_indicator_html );
				}

			} ).catch( () => {
				button.disabled = false;
			} );
		} );
	}
} )( document );
