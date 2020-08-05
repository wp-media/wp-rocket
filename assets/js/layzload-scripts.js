class RocketLazyLoadScripts {
	options = { passive: true };
	attrName = 'data-rocketlazyloadscript';

	constructor( triggerEvents ) {
		this.triggerEvents = triggerEvents;
	}

	init() {
		this._addEventListener( this );
	}

	reset() {
		this._removeEventListener( this );
	}

	_addEventListener( self ) {
		this.triggerEvents.forEach(
			eventName => window.addEventListener( eventName, self._triggerListener.bind( self ), self.options )
		);
	}

	_removeEventListener( self ) {
		this.triggerEvents.forEach(
			eventName => window.removeEventListener( eventName, self._triggerListener, self.options )
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
	}

	/**
	 * Window event listener - when triggered, invokes the load script src handler and then resets.
	 *
	 * @param object event Event object.
	 * @private
	 */
	_triggerListener( event ) {
		console.log( event );
		this._loadScriptSrc();
		this.reset();
	}
}

const rocketLazyLoadScripts = new RocketLazyLoadScripts(
	[
		'mouseover',
		'keydown',
		'touchmove',
		'touchstart'
	]
);

rocketLazyLoadScripts.init();
