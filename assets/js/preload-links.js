class RocketPreloadLinks {

	constructor( browser, config ) {
		this.browser         = browser;
		this.config          = config;
		this.listenerOptions = this.browser.options;

		this.linksPreloaded    = new Set;
		this.addLinkTimeoutId  = null;
		this.eventTime         = null;
		this.listenerThreshold = 1111;
		this.onHoverPreloads   = 0;
	}

	/**
	 * Initializes the handler.
	 */
	init() {
		if ( ! this.browser.supportsLinkPrefetch() || this.browser.isDataSaverModeOn() ) {
			return;
		}

		this.regex = {
			excludeUris   : RegExp( this.config.excludeUris, 'i' ),
			images        : RegExp( '.(' + this.config.imageExtensions + ')$', 'i' ),
			fileExtensions: RegExp( '.(' + this.config.imageExtensions + '|php|pdf|html|htm' + ')$', 'i' )
		}

		this.linksPreloaded.add( window.location.href );
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
		if ( this.linksPreloaded.has( url.href ) ) {
			return;
		}

		const elem = document.createElement( 'link' );
		elem.rel   = 'prefetch';
		elem.href  = url.href;

		document.head.appendChild( elem );

		this.linksPreloaded.add( url.href );
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

		const [ url, linkElem ] = this._prepareUrl( event );
		if ( null === url ) {
			return;
		}

		const self = this;
		linkElem.addEventListener( 'mouseout', self.resetOnHover.bind( self ), { passive: true } );

		this.addLinkTimeoutId = setTimeout( () => {
				this.addLinkTimeoutId = undefined;

				// Start the rate throttle: 1 sec timeout.
				if ( 0 === this.onHoverPreloads ) {
					setTimeout( () => this.onHoverPreloads = 0, 1000 );
				}
				// Bail out when exceeding the rate throttle.
				else if ( this.onHoverPreloads > this.config.rateThrottle ) {
					return;
				}

				this.onHoverPreloads++;
				this._addPrefetchLink( url );
			},
			this.config.onHoverDelayTime
		);
	}

	/**
	 * Triggers adding the link prefetch when the user clicks on a <a> hyperlink.
	 *
	 * @param Event event Event instance.
	 */
	triggerOnClick( event ) {
		const [ url, linkElem ] = this._prepareUrl( event );

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
	 * Prepares the target link's URL.
	 *
	 * @private
	 *
	 * @param Event event Event instance.
	 * @returns {({protocol: *, original: *, origin: string, href: string, pathname: string}|*)[]|*[]}
	 */
	_prepareUrl( event ) {
		const linkElem = event.target.closest( 'a' );

		if ( null === linkElem || typeof linkElem !== 'object' ) {
			return [ null, null ];
		}

		if ( ! 'href' in linkElem ) {
			return [ null, null ];
		}

		// Link prefetching only works on http/https protocol.
		if ( ! this._isHttpProtocol( linkElem ) ) {
			return [ null, null ];
		}

		const href     = linkElem.href;
		const origin   = href.substring( 0, this.config.siteUrl.length );
		const pathname = this._getPathname( href, origin );
		const url      = {
			original: href,
			protocol: linkElem.protocol,
			origin  : origin,
			pathname: pathname,
			href    : origin + pathname
		}

		if ( ! this._isLinkOk( url ) ) {
			return [ null, null ];
		}

		return [ url, linkElem ];
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

		if ( this.linksPreloaded.has( url.href ) ) {
			return false;
		}

		if ( ! this._isInternal( url ) ) {
			return false;
		}

		if ( this._isQueryString( url.href ) ) {
			return false;
		}

		if ( this._isAnchor( url.href ) ) {
			return false;
		}

		if ( this._isExcluded( url.href ) ) {
			return false;
		}

		if ( this._isImage( url.href ) ) {
			return false;
		}

		return true;
	}

	_isAnchor( href ) {
		return href.indexOf( '#' ) !== -1;
	}

	_isExcluded( url ) {
		return this.regex.excludeUris.test( url );
	}

	_isHttpProtocol( linkElem ) {
		return ( 'http:' === linkElem.protocol || 'https:' === linkElem.protocol );
	}

	_isImage( href ) {
		return this.regex.images.test( href );
	}

	_isInternal( url ) {
		return url.origin === this.config.siteUrl;
	}

	_isQueryString( href ) {
		return href.indexOf( '?' ) !== -1;
	}

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
		// Bail out if the configuration not passed from the server.
		if ( typeof RocketPreloadLinksConfig === "undefined" ) {
			return;
		}

		const options  = {
			capture: true,
			passive: true
		};
		const browser  = new RocketBrowserCompatibilityChecker( options );
		const instance = new RocketPreloadLinks( browser, RocketPreloadLinksConfig );
		instance.init();
	}
}

RocketPreloadLinks.run();
