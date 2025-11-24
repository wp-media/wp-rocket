class RocketMixpanel {

	trackedTabs = [
		'dashboard',
		'rocket_insights'
	];

	constructor( config ) {
		this.config = config;
	}

	/**
	 * Initializes the handler.
	 */
	init() {
		if ( typeof mixpanel === 'undefined' || !mixpanel.track ) {
			return;
		}
		if ( !this.config.optin_enabled || this.config.optin_enabled === '0') {
			return;
		}
		mixpanel.identify(this.config.user_id);
		this._initListeners( this );
	}

	/**
	 * Initializes the event listeners.
	 *
	 * @param self instance of this object, used for binding "this" to the listeners.
	 */
	_initListeners( self ) {
		window.addEventListener( 'hashchange', self._onHashChange.bind( self ) );
		window.addEventListener( 'load', self._onPageLoad.bind( self ) );
	}

	/**
	 * Event listener when the hash changed in a page.
	 *
	 * @param Event event Event instance.
	 */
	_onHashChange( event ) {
		console.log('asa_1');
		const oldHash = this._cleanHash(new URL(event.oldURL).hash);
		const newHash = this._cleanHash(new URL(event.newURL).hash);

		if ( !this._canTrackTab(newHash) ) {
			return;
		}

		this._sendPageViewedEvent(this._getSource( oldHash ), newHash);
	}

	_onPageLoad() {
		const newHash = this._cleanHash(window.location.hash);
		console.log('on page load: ' + newHash);
		if ( !this._canTrackTab(newHash) ) {
			return;
		}

		this._sendPageViewedEvent(this._getSource(''), newHash);
	}

	_cleanHash( tabHash ) {
		if (!tabHash || !tabHash.startsWith('#')) {
			return tabHash;
		}
		return tabHash.substring(1);
	}

	_canTrackTab(tabHash) {
		return this.trackedTabs.includes(tabHash);
	}

	_getSource( oldHash ) {
		if ( oldHash ) {
			return `settings_${oldHash}`;
		}

		let source = this._getSourceFromQueryStringAndRemoveIt();
		if ( source ) {
			return source;
		}

		return this._getSourceFromReferrer();
	}

	_getSourceFromQueryStringAndRemoveIt() {
		const url = new URL(window.location.href);
		const urlParams = url.searchParams;

		// 1. Check for explicit source param
		if (!urlParams.has('rocket_source')) {
			return '';
		}

		// 2. Get the value
		const sourceValue = urlParams.get('rocket_source');

		// 3. Delete the parameter from the URLSearchParams object
		urlParams.delete('rocket_source');

		// 4. Update the browser's URL using the History API
		// This removes the parameter from the URL bar without reloading the page.
		window.history.replaceState(null, '', url.search ? url.href : url.pathname);

		// 5. Return the retrieved value
		return sourceValue;
	}

	_getSourceFromReferrer() {
		const referrer = document.referrer;
		if (referrer && !referrer.includes(window.location.hostname)) {
			return 'external';
		}
		return '';
	}

	_sendPageViewedEvent(source, newHash) {
		mixpanel.track('Page Viewed', {
			path: `/wp-admin/options-general.php?page=wprocket#${newHash}`,
			page_name: newHash.replace('_', ' '),
			source: source,
			plugin: rocket_mixpanel_data.plugin,
			brand: rocket_mixpanel_data.brand,
			application: rocket_mixpanel_data.app,
			context: rocket_mixpanel_data.context
		});
	}

	/**
	 * Named static constructor to encapsulate how to create the object.
	 */
	static run() {
		// Bail out if the configuration not passed from the server.
		if ( typeof rocket_mixpanel_data === 'undefined' ) {
			return;
		}

		const instance = new RocketMixpanel( rocket_mixpanel_data );
		instance.init();
	}
}

RocketMixpanel.run();
