/**
 * Recommendations Widget Handler
 *
 * Listens for Global Score updates and fetches/updates recommendations dynamically.
 */
var $ = jQuery;
$(document).ready(function(){
	/**
	 * Updates the recommendations widget UI based on the fetched data.
	 *
	 * @param {Object} data - The recommendations data from the API.
	 * @param {Array} data.recommendations - Array of recommendations details.
	 * @param {string} data.recommendations.html - Recommendations HTML.
	 * @param {Object} data.recommendations.tracking - Tracking data for Mixpanel.
	 */
	function updateRecommendationsWidget(data) {
		const widget = $('.wpr-recommendations');

		if (!widget || !data?.recommendations?.html) {
			return;
		}

		// Update the widget content with the new recommendations HTML
		widget.replaceWith(data?.recommendations?.html);

		// Track recommendations API response with Mixpanel
		if (data?.recommendations?.tracking) {
			trackRecommendationsEvent(data.recommendations.tracking);
		}
	}

	/**
	 * Track Rocket Insights Recommendation event with Mixpanel.
	 *
	 * @param {Object} tracking - Tracking data.
	 * @param {string} tracking.status - success or error.
	 * @param {number} tracking.quantity - Number of recommendations returned.
	 * @param {number} tracking.duration - Duration in milliseconds.
	 */
	function trackRecommendationsEvent(tracking) {
		if (typeof mixpanel === 'undefined' || !mixpanel.track) {
			return;
		}

		// Check if user has opted in
		if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
			return;
		}

		// Identify user if available
		if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
			mixpanel.identify(rocket_mixpanel_data.user_id);
		}

		// Track the event
		mixpanel.track('Rocket Insights Recommendation', {
			'status': tracking.status,
			'quantity': tracking.quantity,
			'duration': tracking.duration,
			'plugin': rocket_mixpanel_data.plugin,
			'brand': rocket_mixpanel_data.brand,
			'application': rocket_mixpanel_data.app,
			'context': rocket_mixpanel_data.context
		});
	}

	/**
	 * Fetches the current recommendations status from the REST API.
	 */
	function fetchRecommendationsStatus() {
		// Use WordPress REST API client if available
		if (window.wp && window.wp.apiFetch) {
			window.wp.apiFetch({
				path: '/wp-rocket/v1/recommendations'
			})
				.then(function (data) {
					updateRecommendationsWidget(data);
				})
				.catch(function (error) {
					console.error('Failed to fetch recommendations status:', error);
				});
		} else {
			// Fallback to fetch API
			fetch(window.wpApiSettings?.root + 'wp-rocket/v1/recommendations', {
				headers: {
					'X-WP-Nonce': window.wpApiSettings?.nonce || ''
				}
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(function (data) {
					updateRecommendationsWidget(data);
				})
				.catch(function (error) {
					console.error('Failed to fetch recommendations status:', error);
				});
		}
	}

	/**
	 * Listen for Global Score update event.
	 * This is fired by ajax.js when the Global Score polling detects a change.
	 */
	$(document).on('wprGlobalScoreUpdated rocket-insights-page-added rocket-insights-page-retest', () => {
		fetchRecommendationsStatus();
	});
});
