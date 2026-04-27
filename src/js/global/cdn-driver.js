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

		if ( ! tabs.length ) {
			return;
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
				document.querySelectorAll( '.rocketcdn, .your-own-cdn' ).forEach( ( section ) => {
					if ( section.classList.contains( driver ) ) {
						section.style.display = '';
					} else {
						section.style.display = 'none';
					}
				} );

				// Update dynamic driver label spans.
				updateDriverLabel( tab );
			} );
		} );

		// Set initial state: show rocketcdn, hide your-own-cdn.
		document.querySelectorAll( '.your-own-cdn' ).forEach( ( section ) => {
			section.style.display = 'none';
		} );

		// Set initial label from the active tab.
		const activeTab = document.querySelector( '.wpr-cdn-tabs__tab--active' );

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
				const textEl = button.querySelector( '.wpr-cdn-pause__text' );

				if ( textEl ) {
					textEl.textContent = isPaused ? 'RESUME CDN' : 'PAUSE CDN';
				}

				button.setAttribute( 'aria-pressed', isPaused ? 'true' : 'false' );
			} );
		} );
	}
} )( document );
