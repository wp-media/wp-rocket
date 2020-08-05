class RocketLazyLoadScripts {
	options = { passive: true };
	attrName = 'data-rocketlazyloadscript';

	constructor( triggerEvents ) {
		this.triggerEvents = triggerEvents;
		this.userEventListener = this.triggerListener.bind( this );
	}

	init() {
		this._addEventListener( this );
	}

	reset() {
		this._removeEventListener( this );
	}

	/**
	 * Adds a listener for each of the configured user interactivity event type. When an even is triggered, it invokes
	 * the triggerListener() method.
	 *
	 * Note: self.userEventListener is holding a reference to the object's method. Why? JS returns a different function
	 * reference back.
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
	 * Note: self.userEventListener is holding a reference to the object's method. Why? JS returns a different function
	 * reference back.
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
