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
		this.triggered  = false; // tap/trigger events take priority.
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
			fileExtensions: RegExp( '.(' + this.config.fileExt + ')$', 'i' )
		};

		this._initListeners( this );
	}

	/**
	 * Adds the event listeners.
	 *
	 * @private
	 *
	 * @param self instance of this object, used for binding "this" to the listeners.
	 */
	_initListeners( self ) {
		// Setting onHoverDelay to -1 disables on-hover feature.
		if ( this.config.onHoverDelay > -1 ) {
			document.addEventListener( 'mouseover', self.triggerOnHover.bind( self ), self.options );
		}

		document.addEventListener( 'mousedown', self.triggerOnClick.bind( self ), self.options );
		document.addEventListener( 'touchstart', self.triggerOnTap.bind( self ), self.options );
	}

	/**
	 * Adds a <link rel="prefetch" href="<url>"> for the given URL.
	 *
	 * @param string url The Given URL to prefetch.
	 */
	_addPrefetchLink( url ) {
		if ( this.prefetched.has( url.href ) ) {
			return;
		}

		this.prefetched.add( url.href );

		const elem = document.createElement( 'link' );
		elem.rel   = 'prefetch';
		elem.href  = url.href;

		document.head.appendChild( elem );
	}

	/**
	 * Triggers adding the link prefetch when the user hovers over a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnHover( event ) {
		if ( this.triggered ) {
			return;
		}

		if ( performance.now() - this.eventTime < this.threshold ) {
			return;
		}

		const [ url, linkElem ] = this._prepareUrl( event );
		if ( null === url ) {
			return;
		}

		const self = this;
		linkElem.addEventListener( 'mouseout', self.resetOnHover.bind( self ), { passive: true } );

		this.onhoverId = setTimeout( () => {
				this.onhoverId = undefined;

				if ( this.triggered ) {
					return;
				}

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
		this.triggered = true;
		this._reset();

		const [ url, linkElem ] = this._prepareUrl( event );
		if ( null === url ) {
			return;
		}

		this._addPrefetchLink( url );
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
			this.onhoverId
		) {
			this._reset();
		}
	}

	/**
	 * Prepares the target link's URL.
	 *
	 * @private
	 *
	 * @param Event event Event instance.
	 * @returns {({protocol: *, original: *, origin: string, href: string, pathname: string}|*)[]|*[]}
	 */
	_prepareUrl( event ) {
		const linkElem = event.target.closest( 'a' );

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
			return [ null, null ];
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

		return this._isLinkOk( url )
			? [ url, linkElem ]
			: [ null, null ];
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

		return this._shouldAddTrailingSlash( pathname )
			? pathname + '/'
			: pathname;
	}

	_shouldAddTrailingSlash( pathname ) {
		return (
			this.config.usesTrailingSlash
			&&
			! pathname.endsWith( '/' )
			&&
			! this.regex.fileExtensions.test( pathname )
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
			url.href.indexOf( '?' ) === -1 // is not a query string.
			&&
			url.href.indexOf( '#' ) === -1 // is not an anchor.
			&&
			! this.regex.excludeUris.test( url.href ) // is not excluded.
			&&
			! this.regex.images.test( url.href ) // is not an image.
		);
	}

	_reset() {
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
