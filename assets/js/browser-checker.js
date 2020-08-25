class RocketBrowserCompatibilityChecker {

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

	isSlowConnection() {
		return (
			'connection' in navigator
			&&
			'effectiveType' in navigator.connection
			&&
			(
				'2g' === navigator.connection.effectiveType
				||
				'slow-2g' === navigator.connection.effectiveType
			)
		)
	}
}
