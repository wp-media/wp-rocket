class RocketLazyLoadScripts {

	constructor( triggerEvents ) {
		this.passiveSupported = false;
		this.attrName = 'data-rocketlazyloadscript';
		this.triggerEvents = triggerEvents;
		this.userEventListener = this.triggerListener.bind( this );
		this._initOptions( this );
	}

	/**
	 * Initializes the LazyLoad Scripts handler.
	 */
	init() {
		this.options = this.passiveSupported ? { passive: true } : false;

		this._addEventListener( this );
	}

	/**
	 * Resets the handler.
	 */
	reset() {
		this._removeEventListener( this );
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
	 * Adds a listener for each of the configured user interactivity event type. When an even is triggered, it invokes
	 * the triggerListener() method.
	 *
	 * @private
	 *
	 * @param self Instance of this object.
	 */
	_addEventListener( self ) {
		this.triggerEvents.forEach(
			eventName => window.addEventListener( eventName, self.userEventListener, self.options )
		);
	}

	/**
	 * Removes the listener for each of the configured user interactivity event type.
	 *
	 * @private
	 *
	 * @param self Instance of this object.
	 */
	_removeEventListener( self ) {
		this.triggerEvents.forEach(
			eventName => window.removeEventListener( eventName, self.userEventListener, self.options )
		);
	}

	/**
	 * Loads the script's src from the data attribute, which will then trigger the browser to request and
	 * load the script.
	 */
	_loadScriptSrc() {
		const scripts = document.querySelectorAll( `script[${ this.attrName }]` );

		scripts.forEach( elem => {
			const scriptSrc = elem.getAttribute( this.attrName );


			elem.setAttribute( 'src', scriptSrc );
			elem.removeAttribute( this.attrName );
		} );

		this.reset();
	}

	/**
	 * Window event listener - when triggered, invokes the load script src handler and then resets.
	 */
	triggerListener() {
		this._loadScriptSrc();
		this._removeEventListener( this );
	}
}

const rocketLazyLoadScripts = new RocketLazyLoadScripts(
	[
		'keydown',
		'mouseover',
		'touchmove',
		'touchstart'
	]
);

rocketLazyLoadScripts.init();
