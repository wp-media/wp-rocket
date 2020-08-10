class RocketPreloadPages {

	constructor() {
		this.passiveSupported        = false;
		this.hrefset                 = new Set;
		this.timeoutId               = null;
		this.eventTime               = null;
		this.doesBrowserSupport      = false;
		this.d                       = 1111;
		this.triggerDelay            = 65; // milliseconds.

		this.userEventListener = this.triggerListener.bind( this );
		this._initOptions( this );
	}

	/**
	 * Initializes the handler.
	 */
	init() {
		this._initBrowser();
	}

	_initBrowser() {
		const elem              = document.createElement( 'link' );
		this.doesBrowserSupport = (
			elem.relList
			&&
			elem.relList.supports
			&&
			elem.relList.supports( 'prefetch' )
			&&
			window.IntersectionObserver
			&&
			"isIntersecting" in IntersectionObserverEntry.prototype
		);

		this.listenerOptions = this.passiveSupported ? { passive: true } : false;
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
	_initOptions( self ) {
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
	 * Window event listener.
	 */
	triggerListener() {
		// add code here.
	}
}

const rocketPreloadPages = new RocketPreloadPages();
rocketPreloadPages.init();
