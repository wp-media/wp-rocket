class RocketBrowserCompatabilityChecker {

	constructor( options ) {
		this.passiveSupported = false;

		this._checkPassiveOption( this );
		this.options = this.passiveSupported ? options : false;
	}

	/**
	 * Initializes browser check for addEventListener passive option.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener#Safely_detecting_option_support
	 * @private
	 *
	 * @param self Instance of this object.
	 * @returns {boolean}
	 */
	_checkPassiveOption( self ) {
		try {
			const options = {
				// This function will be called when the browser attempts to access the passive property.
				get passive() {
					self.passiveSupported = true;
					return false;
				}
			};

			window.addEventListener( 'test', null, options );
			window.removeEventListener( 'test', null, options );
		} catch ( err ) {
			self.passiveSupported = false;
		}
	}

	/**
	 * Checks if the browser supports requestIdleCallback and cancelIdleCallback. If no, shims its behavior with a polyfills.
	 *
	 * @link @link https://developers.google.com/web/updates/2015/08/using-requestidlecallback
	 */
	initRequestIdleCallback() {
		if ( ! 'requestIdleCallback' in window ) {
			window.requestIdleCallback = ( cb ) => {
				const start = Date.now();
				return setTimeout( () => {
					cb( {
						didTimeout: false,
						timeRemaining: function timeRemaining() {
							return Math.max( 0, 50 - ( Date.now() - start ) );
						}
					} );
				}, 1 );
			};
		}

		if ( ! 'cancelIdleCallback' in window ) {
			window.cancelIdleCallback = ( id ) => clearTimeout( id );
		}
	}

	/**
	 * Detects if data saver mode is on.
	 *
	 * @link https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/save-data/#detecting_the_save-data_setting
	 *
	 * @returns {boolean|boolean}
	 */
	isDataSaverModeOn() {
		return (
			'connection' in navigator
			&&
			true === navigator.connection.saveData
		);
	}

	/**
	 * Checks if the browser supports link prefetch.
	 *
	 * @returns {boolean|boolean}
	 */
	supportsLinkPrefetch() {
		const elem = document.createElement( 'link' );
		return (
			elem.relList
			&&
			elem.relList.supports
			&&
			elem.relList.supports( 'prefetch' )
			&&
			window.IntersectionObserver
			&&
			'isIntersecting' in IntersectionObserverEntry.prototype
		);
	}
}

class RocketPreloadPages {

	constructor( browser ) {
		this.browser = browser;
		this.listenerOptions = this.browser.options;

		this.processedLinks = new Set;
		this.addLinkTimeoutId = null;
		this.eventTime = null;
		this.listenerThreshold = 1111;

		// A pause to prevent adding link when hover is too fast.
		this.onHoverDelayTime = 500; // milliseconds.
	}

	/**
	 * Initializes the handler.
	 */
	init() {
		if ( ! this.browser.supportsLinkPrefetch() || this.browser.isDataSaverModeOn() ) {
			return;
		}

		this.processedLinks.add( window.location.href );
		this._addEventListeners( this );
	}

	/**
	 * Adds the event listeners.
	 *
	 * @private
	 *
	 * @param self instance of this object, used for binding "this" to the listeners.
	 */
	_addEventListeners( self ) {
		document.addEventListener( 'mouseover', self.triggerOnHover.bind( self ), self.listenerOptions );

		document.addEventListener( 'mousedown', self.triggerOnClick.bind( self ), self.listenerOptions );
		document.addEventListener( 'touchstart', self.triggerOnTap.bind( self ), self.listenerOptions );
	}

	/**
	 * Adds a <link rel="prefetch" href="<url>"> for the given URL.
	 *
	 * @param string url The Given URL to prefetch.
	 */
	_addPrefetchLink( url ) {
		if ( this.processedLinks.has( url ) ) {
			return;
		}

		const elem = document.createElement( 'link' );
		elem.rel = 'prefetch';
		elem.href = url;

		document.head.appendChild( elem );

		this.processedLinks.add( url );
	}

	/**
	 * Triggers adding the link prefetch when the user hovers over a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnHover( event ) {
		if ( performance.now() - this.eventTime < this.listenerThreshold ) {
			return;
		}

		const linkElem = event.target.closest( 'a' );
		if ( ! this._isLinkOk( linkElem ) ) {
			return;
		}

		const self = this;
		linkElem.addEventListener( 'mouseout', self.resetOnHover.bind( self ), { passive: true } );

		this.addLinkTimeoutId = setTimeout( () => {
				this._addPrefetchLink( linkElem.href );
				this.addLinkTimeoutId = undefined;
			},
			this.onHoverDelayTime
		);
	}

	/**
	 * Triggers adding the link prefetch when the user clicks on a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnClick( event ) {
		const linkElem = event.target.closest( 'a' );
		if ( ! this._isLinkOk( linkElem ) ) {
			return;
		}

		this._addPrefetchLink( linkElem.href );
		this._resetAddLinkTask();
	}

	/**
	 * Triggers adding the link prefetch when the user taps a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnTap( event ) {
		this.eventTime = performance.now();
		this.triggerOnClick( event );
	}

	/**
	 * Resets the Add Link Task on hover.
	 *
	 * @param object event Event object.
	 */
	resetOnHover( event ) {
		if (
			event.relatedTarget
			&&
			event.target.closest( 'a' ) === event.relatedTarget.closest( 'a' )
			||
			this.addLinkTimeoutId
		) {
			this._resetAddLinkTask();
		}
	}

	/**
	 * Checks if the given link element is okay to process.
	 *
	 * @private
	 *
	 * @param mixed linkElem The element to check.
	 *
	 * @returns {boolean}
	 */
	_isLinkOk( linkElem ) {
		if ( null === linkElem || typeof linkElem !== 'object' ) {
			return false;
		}

		if ( ! linkElem.href ) {
			return false;
		}

		return ! this.processedLinks.has( linkElem.href );
	}

	/**
	 * Resets the add link task.
	 *
	 * @private
	 */
	_resetAddLinkTask() {
		if ( ! this.addLinkTimeoutId ) {
			return;
		}

		clearTimeout( this.addLinkTimeoutId );
		this.addLinkTimeoutId = null;
	}

	/**
	 * Named static constructor to encapsulate how to create the object.
	 */
	static run() {
		const options = {
			capture: true,
			passive: true
		};

		const browser = new RocketBrowserCompatabilityChecker( options );
		const instance = new RocketPreloadPages( browser );
		instance.init();
	}
}

RocketPreloadPages.run();
