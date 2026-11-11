( ( document ) => {
	'use strict';

	/**
	 * Polls the RocketCDN subscription endpoint until is_loading becomes false.
	 */
	class RocketCDNSubscriptionPoller {
		constructor() {
			this.path = '/wp-rocket/v1/rocketcdn/subscription';
			this.pollInterval = 10000; // 10 seconds.
			this.maxRetries = 60; // 10 minutes total.
			this.timerId = null;
			this.retryCount = 0;

			document.addEventListener( 'rocketCDNSubscriptionLoading', () => this.start() );

			// Re-trigger polling after page refresh.
			const classes = [
				'.wpr-icon-orange-loader',
				'.wpr-cdn-built-in--disabled',
			];

			const allPresent = classes.every( cls => document.querySelector( cls ) !== null );

			if ( allPresent ) {
				this.start()
			}
		}

		/**
		 * Starts polling.
		 */
		start() {
			if ( this.timerId ) {
				return;
			}

			this.retryCount = 0;
			this.poll();
		}

		/**
		 * Stops polling.
		 */
		stop() {
			if ( this.timerId ) {
				clearTimeout( this.timerId );
				this.timerId = null;
			}
		}

		/**
		 * Performs a single poll and schedules the next one if still loading.
		 */
		poll() {
			if ( this.retryCount >= this.maxRetries ) {
				this.stop();
				return;
			}

			this.retryCount++;

			window.wp.apiFetch( {
				path: this.path,
				method: 'GET',
			} ).then( ( response ) => {
				if ( ! response || ! response.is_loading ) {
					this.stop();
					window.location.reload();
					return;
				}

				this.timerId = setTimeout( () => this.poll(), this.pollInterval );
			} ).catch( () => {
				this.timerId = setTimeout( () => this.poll(), this.pollInterval );
			} );
		}
	}

	new RocketCDNSubscriptionPoller();
} )( document );