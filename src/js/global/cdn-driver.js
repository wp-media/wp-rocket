( ( document ) => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {
		initCdnDriverTabs();
		initCdnPauseToggle();
		initAddHomepage();
		initAddPage();
		initDeletePage();
	} );

	/**
	 * Sets the subscription loading state on the CDN UI.
	 *
	 * Disables the built-in CDN section, purge and exclude sections,
	 * and swaps the status indicator dot for a loader icon.
	 */
	function setSubscriptionLoadingState( statusIndicatorHtml ) {
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

		// Update status indicator to show loading state.
		const statusIndicator = document.querySelector( '.wpr-cdn-built-in .wpr-cdn-status' );
		if ( statusIndicator && statusIndicatorHtml ) {
			statusIndicator.outerHTML = statusIndicatorHtml;
		}
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

				// Persist the active driver selection.
				const driverValue = 'your-own-cdn' === driver ? 'byocdn' : 'rocketcdn';

				window.wp.apiFetch( {
					path: '/wp-rocket/v1/rocketcdn/driver',
					method: 'POST',
					data: { driver: driverValue },
				} );
			} );
		} );

		// Set initial state from active tab, fallback to rocketcdn.
		const activeTab = document.querySelector( '.wpr-cdn-tabs__tab--active' );
		const activeDriver = activeTab ? activeTab.getAttribute( 'data-cdn-driver' ) : 'rocketcdn';

		if ( activeDriver ) {
			toggleDriverSections( activeDriver );
		}

		// Set initial label from the active tab.
		if ( activeTab ) {
			updateDriverLabel( activeTab );
		}
	}

	/**
	 * Initializes the CDN pause/resume toggle buttons.
	 *
	 * Toggles between "PAUSE CDN" and "RESUME CDN" states,
	 * swapping the icon via a CSS modifier class.
	 */
	function initCdnPauseToggle() {
		document.querySelectorAll( '.wpr-cdn-pause' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const isPaused = button.classList.toggle( 'wpr-cdn-pause--paused' );
				button.setAttribute( 'aria-pressed', isPaused ? 'true' : 'false' );
				button.disabled = true;

				window.wp.apiFetch( {
					path: '/wp-rocket/v1/rocketcdn/pause',
					method: 'POST',
					data: { paused: isPaused ? 0 : 1 },
				} ).then( () => {
					button.disabled = false;

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
				} );
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
		const parentComponentSelector = '#wpr_add_page_component';
		const button = document.querySelector( '#wpr_add_page_component .wpr-cdn-add-page__homepage' );

		if ( ! button ) {
			return;
		}

		button.addEventListener( 'click', () => {
			button.disabled = true;

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/pages/homepage',
				method: 'POST',
			} ).then( ( response ) => {
				button.classList.add( 'wpr-isHidden' );

				console.log(response.status_indicator_html);

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

				// Set subscription loading state when first page is added.
				if ( 1 === response.count ) {
					setSubscriptionLoadingState( response.status_indicator_html );
				}
			} ).catch( () => {
				button.disabled = false;
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

			window.wp.apiFetch( {
				path: '/wp-rocket/v1/rocketcdn/pages',
				method: 'POST',
				data: { url },
			} ).then( ( response ) => {
				input.value = '';
				input.disabled = false;
				button.disabled = false;

				console.log(response.status_indicator_html);

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

				if ( response.limit === response.count ) {
					// Expand CTA banner if page limit reached and add max limit text.
					const cta = document.getElementById( 'wpr-rocketcdn-cta' );
					cta.classList.add( 'wpr-rocketcdn-cta--expanded', 'wpr-rocketcdn-cta---max-limit' );
					cta.classList.remove( 'wpr-rocketcdn-cta--collapsed' );

					// Disable input and button when page limit is reached.
					document.querySelector( '.wpr-cdn-built-in' ).classList.add( 'wpr-cdn-built-in--disabled' );
				}

				// Set subscription loading state when first page is added.
				if ( 1 === response.count ) {
					setSubscriptionLoadingState( response.status_indicator_html );
				}
			} ).catch( () => {
				input.disabled = false;
				button.disabled = false;
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

				// Show ADD HOMEPAGE button when all pages are deleted.
				if ( 0 === response.count ) {
					// Remove table list component.
					document.querySelector( '.wpr-cdn-built-in .wpr-table-list' ).remove();
					
					const homepageBtn = container.querySelector( '.wpr-cdn-add-page__homepage' );

					if ( homepageBtn ) {
						homepageBtn.classList.remove( 'wpr-isHidden' );
					}
				}

				if ( response.limit > response.count ) {
					// Collapse CTA banner when page limit is not reached and remove max limit text.
					const cta = document.getElementById( 'wpr-rocketcdn-cta' );
					cta.classList.add( 'wpr-rocketcdn-cta--collapsed' );
					cta.classList.remove( 'wpr-rocketcdn-cta--expanded', 'wpr-rocketcdn-cta---max-limit' );

					// Re-enable input and button when page limit is not reached.
					document.querySelector( '.wpr-cdn-built-in' ).classList.remove( 'wpr-cdn-built-in--disabled' );
				}
			} ).catch( () => {
				button.disabled = false;
			} );
		} );
	}
} )( document );
