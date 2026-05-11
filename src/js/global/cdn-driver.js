( ( document ) => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {
		initCdnDriverTabs();
		initCdnPauseToggle();
	} );

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
			} );
		} );
	}
} )( document );
