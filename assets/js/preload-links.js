class RocketPreloadLinks {

	constructor( browser, config ) {
		this.browser = browser;
		this.config = config;
		this.listenerOptions = this.browser.options;
		this.pageUrl = window.location.origin;

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

		this.regex = {
			excludeUris: RegExp( '(' + this.config.excludeUris + ')', 'i' ),
			images: RegExp('.(jpg|jpeg|gif|png|tiff|bmp|webp|avif)$', 'i')
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
		if ( this.processedLinks.has( url.href ) ) {
			return;
		}

		const elem = document.createElement( 'link' );
		elem.rel = 'prefetch';
		elem.href = url.href;

		document.head.appendChild( elem );

		this.processedLinks.add( url.href );
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
				this._addPrefetchLink( url );
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

		if ( ! this._isHttpProtocol( linkElem ) ) {
			return [ null, null ];
		}

		const href = linkElem.href;
		const origin = href.substring( 0, this.pageUrl.length );
		const pathname = this._getPathname( href, origin );
		const url = {
			original: href,
			protocol: linkElem.protocol,
			origin: origin,
			pathname: pathname,
			href: origin + pathname
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
			? url.substring( this.pageUrl.length )
			: url;

		if ( ! pathname.startsWith( '/' ) ) {
			pathname = '/' + pathname;
		}

		if ( this.config.usesTrailingSlash && ! pathname.endsWith( '/' ) ) {
			return pathname + '/';
		}

		return pathname;
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

		if ( this.processedLinks.has( url.href ) ) {
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

		return ! this._isImage( url );
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
		return url.origin === this.pageUrl;
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
		const options = {
			capture: true,
			passive: true
		};

		// Bail out if the configuration not passed from the server.
		if ( typeof RocketPreloadLinksConfig === "undefined" ) {
			return;
		}

		const browser = new RocketBrowserCompatabilityChecker( options );
		const instance = new RocketPreloadLinks( browser, RocketPreloadLinksConfig );
		instance.init();
	}
}

RocketPreloadLinks.run();
