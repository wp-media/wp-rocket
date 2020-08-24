class RocketPreloadLinks {

	constructor( browser, config ) {
		this.browser = browser;
		this.config  = config;
		this.options = this.browser.options;

		this.prefetched = new Set;
		this.onhoverId  = null;
		this.eventTime  = null;
		this.threshold  = 1111;
		this.numOnHover = 0;
	}

	/**
	 * Initializes the handler.
	 */
	init() {
		if ( ! this.browser.supportsLinkPrefetch() || this.browser.isDataSaverModeOn() ) {
			return;
		}

		this.regex = {
			excludeUris: RegExp( this.config.excludeUris, 'i' ),
			images: RegExp( '.(' + this.config.imageExt + ')$', 'i' ),
			fileExt: RegExp( '.(' + this.config.fileExt + ')$', 'i' )
		};

		this.prefetched.add( window.location.href );
		this._initListeners( this );
	}

	/**
	 * Initializes the event listeners.
	 *
	 * @private
	 *
	 * @param self instance of this object, used for binding "this" to the listeners.
	 */
	_initListeners( self ) {
		// Setting onHoverDelay to -1 disables the "on-hover" feature.
		if ( this.config.onHoverDelay > -1 ) {
			document.addEventListener( 'mouseover', self.triggerOnHover.bind( self ), self.listenerOptions );
		}

		document.addEventListener( 'mousedown', self.triggerOnClick.bind( self ), self.listenerOptions );
		document.addEventListener( 'touchstart', self.triggerOnTap.bind( self ), self.listenerOptions );
	}

	/**
	 * Triggers adding the link prefetch when the user hovers over a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnHover( event ) {
		if ( performance.now() - this.eventTime < this.threshold ) {
			return;
		}

		const linkElem = event.target.closest( 'a' );
		const url      = this._prepareUrl( linkElem );
		if ( null === url ) {
			return;
		}

		const self = this;
		linkElem.addEventListener( 'mouseout', self.resetOnHover.bind( self ), { passive: true } );

		this.onhoverId = setTimeout( () => {
				this.onhoverId = undefined;

				// Start the rate throttle: 1 sec timeout.
				if ( 0 === this.numOnHover ) {
					setTimeout( () => this.numOnHover = 0, 1000 );
				}
				// Bail out when exceeding the rate throttle.
				else if ( this.numOnHover > this.config.rateThrottle ) {
					return;
				}

				this.numOnHover++;
				this._addPrefetchLink( url );
			},
			this.config.onHoverDelay
		);
	}

	/**
	 * Triggers adding the link prefetch when the user clicks on a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnClick( event ) {
		const linkElem = event.target.closest( 'a' );
		const url      = this._prepareUrl( linkElem );

		if ( null === url ) {
			return;
		}

		this._addPrefetchLink( url );
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
	 * Adds a <link rel="prefetch" href="<url>"> for the given URL.
	 *
	 * @param string url The Given URL to prefetch.
	 */
	_addPrefetchLink( url ) {
		this.prefetched.add( url.href );

		return new Promise( ( resolve, reject ) => {
			const elem   = document.createElement( 'link' );
			elem.rel     = 'prefetch';
			elem.href    = url.href;
			elem.onload  = resolve;
			elem.onerror = reject;

			document.head.appendChild( elem );
		} );
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
			this.onhoverId
		) {
			this._resetAddLinkTask();
		}
	}

	/**
	 * Prepares the target link's URL.
	 *
	 * @private
	 *
	 * @param Element|null linkElem Instance of the link element.
	 * @returns {null|*}
	 */
	_prepareUrl( linkElem ) {
		if (
			null === linkElem
			||
			typeof linkElem !== 'object'
			||
			! 'href' in linkElem
			||
			// Link prefetching only works on http/https protocol.
			[ 'http:', 'https:' ].indexOf( linkElem.protocol ) === -1
		) {
			return null;
		}

		const origin   = linkElem.href.substring( 0, this.config.siteUrl.length );
		const pathname = this._getPathname( linkElem.href, origin );
		const url      = {
			original: linkElem.href,
			protocol: linkElem.protocol,
			origin: origin,
			pathname: pathname,
			href: origin + pathname
		};

		return this._isLinkOk( url ) ? url : null;
	}

	/**
	 * Gets the URL's pathname. Note: ensures the pathname matches the permalink structure.
	 *
	 * @private
	 *
	 * @param object url Instance of the URL.
	 * @param string origin The target link href's origin.
	 * @returns {string}
	 */
	_getPathname( url, origin ) {
		let pathname = origin
			? url.substring( this.config.siteUrl.length )
			: url;

		if ( ! pathname.startsWith( '/' ) ) {
			pathname = '/' + pathname;
		}

		if ( this._shouldAddTrailingSlash( pathname ) ) {
			return pathname + '/';
		}

		return pathname;
	}

	_shouldAddTrailingSlash( pathname ) {
		return (
			this.config.usesTrailingSlash
			&&
			! pathname.endsWith( '/' )
			&&
			! this.regex.fileExt.test( pathname )
		);
	}

	/**
	 * Checks if the given link element is okay to process.
	 *
	 * @private
	 *
	 * @param object url URL parts object.
	 *
	 * @returns {boolean}
	 */
	_isLinkOk( url ) {
		if ( null === url || typeof url !== 'object' ) {
			return false;
		}

		return (
			! this.prefetched.has( url.href )
			&&
			url.origin === this.config.siteUrl // is an internal document.
			&&
			url.href.indexOf( '?' ) === -1 // not a query string.
			&&
			url.href.indexOf( '#' ) === -1 // not an anchor.
			&&
			! this.regex.excludeUris.test( url.href ) // not excluded.
			&&
			! this.regex.images.test( url.href ) // not an image.
		);
	}

	_resetAddLinkTask() {
		if ( ! this.onhoverId ) {
			return;
		}

		clearTimeout( this.onhoverId );
		this.onhoverId = null;
	}

	/**
	 * Named static constructor to encapsulate how to create the object.
	 */
	static run() {
		// Bail out if the configuration not passed from the server.
		if ( typeof RocketPreloadLinksConfig === 'undefined' ) {
			return;
		}

		const browser  = new RocketBrowserCompatibilityChecker( {
			capture: true,
			passive: true
		} );
		const instance = new RocketPreloadLinks( browser, RocketPreloadLinksConfig );
		instance.init();
	}
}

RocketPreloadLinks.run();
