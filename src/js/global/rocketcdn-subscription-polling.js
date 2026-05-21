/*eslint-env es6, browser*/
/* global wp */

/**
 * RocketCDN Subscription Polling Service
 *
 * Polls the subscription endpoint until subscription is active.
 * Uses WordPress wp.apiFetch for REST API calls.
 */
class RocketCDNSubscriptionPoller {

	/**
	 * Configuration
	 *
	 * @var {Object}
	 */
	config = {
		path: '/rocket-cdn/v1/subscription',
		pollInterval: 10000, // 10 seconds
		maxRetries: 60,      // 10 minutes total (60 * 10 seconds)
	};

	/**
	 * Polling state
	 *
	 * @var {Object}
	 */
	state = {
		isPolling: false,
		retryCount: 0,
		timerId: null,
		failureCount: 0,
	};

	/**
	 * Constructor
	 *
	 * @param {Object} options Configuration overrides
	 */
	constructor( options = {} ) {
		this.config = { ...this.config, ...options };
		this.init();
	}

	/**
	 * Initialize poller
	 *
	 * @return {void}
	 */
	init() {
		// Check if wp.apiFetch is available
		if ( ! window.wp || ! window.wp.apiFetch ) {
			console.error( '[SubscriptionPoller] wp.apiFetch is not available. Make sure WordPress is loaded.' );
		}

		document.addEventListener('rocketCDNSubscriptionLoading', () => {
			this.start();
		});
	}

	/**
	 * Start polling for subscription status
	 *
	 * Called when:
	 * 1. First page is added (trigger in cdn-driver.js)
	 * 2. Page refresh with subscription in loading state (passed via wp_localize_script)
	 *
	 * @return {void}
	 */
	start() {
		if ( this.state.isPolling ) {
			console.warn( '[SubscriptionPoller] Polling already in progress' );
			return;
		}

		console.log( '[SubscriptionPoller] Starting polling...' );
		this.state.isPolling = true;
		this.state.retryCount = 0;
		this.state.failureCount = 0;

		// Start immediate check, then interval
		this.poll();
	}

	/**
	 * Stop polling
	 *
	 * @return {void}
	 */
	stop() {
		if ( this.state.timerId ) {
			clearTimeout( this.state.timerId );
			this.state.timerId = null;
		}

		this.state.isPolling = false;
		console.log( '[SubscriptionPoller] Polling stopped' );
	}

	/**
	 * Poll subscription status
	 *
	 * @return {Promise<void>}
	 */
	async poll() {
		try {
			// Guard against max retries
			if ( this.state.retryCount >= this.config.maxRetries ) {
				console.error( '[SubscriptionPoller] Max retries reached', {
					retries: this.state.retryCount,
					max: this.config.maxRetries,
				} );
				this.stop();
				this.onMaxRetriesReached();
				return;
			}

			this.state.retryCount++;

			// ✅ Call REST endpoint using wp.apiFetch
			const subscriptionData = await this.fetchSubscriptionStatus();

			console.log( '[SubscriptionPoller] Poll #' + this.state.retryCount, {
				hasActiveSubscription: subscriptionData.has_active_subscription,
				status: subscriptionData.status,
			} );

			// ✅ SUCCESS: Subscription is active
			if ( subscriptionData.has_active_subscription ) {
				console.log( '[SubscriptionPoller] Subscription is active! Refreshing page...' );
				this.stop();
				this.onSubscriptionActive( subscriptionData );
				return;
			}

			// Reset failure counter on successful API response
			this.state.failureCount = 0;

			// Schedule next poll
			this.scheduleNextPoll();

		} catch ( error ) {
			console.error( '[SubscriptionPoller] Poll failed', error );
			this.state.failureCount++;

			// Stop after 3 consecutive failures
			if ( this.state.failureCount >= 3 ) {
				console.error( '[SubscriptionPoller] Too many consecutive failures. Stopping polling.' );
				this.stop();
				this.onPollingError( error );
				return;
			}

			// Retry on failure (exponential backoff after first failure)
			const delay = this.state.failureCount > 1
				? this.config.pollInterval * 2
				: this.config.pollInterval;
			this.state.timerId = setTimeout( () => this.poll(), delay );
		}
	}

	/**
	 * Schedule next poll with interval
	 *
	 * @return {void}
	 */
	scheduleNextPoll() {
		this.state.timerId = setTimeout( () => this.poll(), this.config.pollInterval );
	}

	/**
	 * Fetch subscription status from REST endpoint
	 *
	 * Uses wp.apiFetch instead of native fetch.
	 * Endpoint: GET /wp-json/rocket-cdn/v1/subscription
	 *
	 * Benefits of wp.apiFetch:
	 * - Automatically injects nonce
	 * - Handles URL construction
	 * - Manages CORS headers
	 * - Consistent error handling
	 *
	 * @return {Promise<Object>} Subscription data
	 */
	async fetchSubscriptionStatus() {
		// ✅ Use wp.apiFetch instead of native fetch
		const response = await window.wp.apiFetch( {
			path: this.config.path,
			method: 'GET',
		} );

		// ✅ wp.apiFetch already parses JSON and handles errors
		return response;
	}

	/**
	 * Callback when subscription becomes active
	 *
	 * @param {Object} subscriptionData Subscription data from API
	 * @return {void}
	 */
	onSubscriptionActive( subscriptionData ) {
		console.log( '[SubscriptionPoller] Subscription active. Data:', subscriptionData );

		// Fire custom event for other scripts to listen to
		window.dispatchEvent( new CustomEvent( 'rocketcdn:subscriptionActive', {
			detail: subscriptionData,
		} ) );

		// Show success notification
		this.showNotification( 'success', 'RocketCDN subscription activated! Refreshing page...' );

		// Refresh page after brief delay to show notification
		setTimeout( () => {
			window.location.reload();
		}, 1500 );
	}

	/**
	 * Callback when max retries reached
	 *
	 * @return {void}
	 */
	onMaxRetriesReached() {
		console.warn( '[SubscriptionPoller] Polling timed out after ' + this.config.maxRetries + ' attempts' );

		// Fire custom event
		window.dispatchEvent( new CustomEvent( 'rocketcdn:subscriptionTimeout', {
			detail: {
				retries: this.state.retryCount,
				totalTime: this.state.retryCount * this.config.pollInterval,
			},
		} ) );

		// Show notification
		this.showNotification(
			'warning',
			'Subscription activation is taking longer than expected. Please refresh the page.'
		);
	}

	/**
	 * Callback on polling error
	 *
	 * @param {Error} error Error object
	 * @return {void}
	 */
	onPollingError( error ) {
		console.error( '[SubscriptionPoller] Polling error:', error );

		// Fire custom event
		window.dispatchEvent( new CustomEvent( 'rocketcdn:subscriptionError', {
			detail: {
				error: error.message,
				failureCount: this.state.failureCount,
			},
		} ) );

		// Show notification
		this.showNotification( 'error', 'Failed to check subscription status. Please refresh manually.' );
	}

	/**
	 * Show notification to user
	 *
	 * @param {string} type Type: success, warning, error
	 * @param {string} message Message text
	 * @return {void}
	 */
	showNotification( type, message ) {
		// Use WordPress notices if available, otherwise fallback to console
		if ( window.wp && window.wp.notices ) {
			window.wp.notices.createNotice( type, message, {
				type,
				isDismissible: true,
			} );
		} else {
			const className = `rocket-cdn-notification rocket-cdn-notification--${type}`;
			const notification = document.createElement( 'div' );
			notification.className = className;
			notification.textContent = message;
			document.body.appendChild( notification );

			// Auto-remove after 5 seconds
			setTimeout( () => notification.remove(), 5000 );
		}
	}

	/**
	 * Get current polling state
	 *
	 * @return {Object}
	 */
	getState() {
		return { ...this.state };
	}
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => {
		new RocketCDNSubscriptionPoller();
	});
} else {
	new RocketCDNSubscriptionPoller();
}