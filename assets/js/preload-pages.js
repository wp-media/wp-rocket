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
}

class RocketPreloadPages {

	constructor( options ) {
		this.browser = new RocketBrowserCompatabilityChecker( options );
		this.listenerOptions = this.browser.options;

		this.hrefSet = new Set;
		this.timeoutId = null;
		this.eventTime = null;
		this.listenerThreshold = 1111;
		this.triggerDelay = 65; // milliseconds.
	}

	/**
	 * Initializes the handler.
	 */
	init() {
		if ( ! this.doesBrowserSupport() ) {
			return;
		}

		this._addEventListeners( this );
	}

	doesBrowserSupport() {
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

	_addEventListeners( self ) {
		document.addEventListener( 'mouseover', self.triggerOnHover.bind( self ), self.listenerOptions );

		document.addEventListener( 'mousedown', self.triggerOnClick.bind( self ), self.listenerOptions );
	}

	/**
	 * Adds a <link rel="prefetch" href="<url>"> for the given URL.
	 *
	 * @param string url The Given URL to prefetch.
	 */
	_addPrefetchLink( url ) {
		if ( this.hrefSet.has( url ) ) {
			return;
		}

		const elem = document.createElement( 'link' );

		elem.rel = 'prefetch';
		elem.href = url;
		document.head.appendChild( elem );
		this.hrefSet.add( url );
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

		this.timeoutId = setTimeout( () => {
				this._addPrefetchLink( linkElem.href );
				this.timeoutId = undefined;
			},
			self.triggerDelay
		);
	}

	triggerOnClick( evt ) {
		const linkElem = evt.target.closest( 'a' );
		if ( ! this._isLinkOk( linkElem ) ) {
			return;
		}
		this.addPrefetchLink( linkElem.href );
	}

	resetOnHover( evt ) {
		if (
			evt.relatedTarget
			&&
			evt.target.closest( 'a' ) === evt.relatedTarget.closest( 'a' )
			||
			this.timeoutId
		) {
			clearTimeout( this.timeoutId );
			this.timeoutId = null;
		}
	}

	_isLinkOk( linkElem ) {
		if ( null === linkElem || typeof linkElem !== 'object' ) {
			return false;
		}

		if ( ! linkElem.href ) {
			return false;
		}

		return true;
	}
}

const rocketPreloadPages = new RocketPreloadPages(
	{
		capture: true,
		passive: true
	}
);
rocketPreloadPages.init();
