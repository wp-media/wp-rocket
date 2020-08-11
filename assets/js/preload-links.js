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

	constructor( options ) {
		this.browser = new RocketBrowserCompatabilityChecker( options );
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

	triggerOnHover( evt ) {
		if ( performance.now() - this.eventTime < this.listenerThreshold ) {
			return;
		}

		const linkElem = evt.target.closest( 'a' );
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

	triggerOnClick( evt ) {
		const linkElem = evt.target.closest( 'a' );
		if ( ! this._isLinkOk( linkElem ) ) {
			return;
		}

		this._addPrefetchLink( linkElem.href );
		this._resetAddLinkTask();
	}

	triggerOnTap( evt ) {
		this.eventTime = performance.now();
		this.triggerOnClick(evt);
	}

	resetOnHover( evt ) {
		if (
			evt.relatedTarget
			&&
			evt.target.closest( 'a' ) === evt.relatedTarget.closest( 'a' )
			||
			this.addLinkTimeoutId
		) {
			this._resetAddLinkTask();
		}
	}

	_resetAddLinkTask() {
		if ( ! this.addLinkTimeoutId ) {
			return;
		}

		clearTimeout( this.addLinkTimeoutId );
		this.addLinkTimeoutId = null;
	}

	_isLinkOk( linkElem ) {
		if ( null === linkElem || typeof linkElem !== 'object' ) {
			return false;
		}

		if ( ! linkElem.href ) {
			return false;
		}

		return ! this.processedLinks.has( linkElem.href );
	}
}

const rocketPreloadPages = new RocketPreloadPages(
	{
		capture: true,
		passive: true
	}
);
rocketPreloadPages.init();
