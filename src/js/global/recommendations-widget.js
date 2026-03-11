/**
 * Recommendations Widget Handler
 *
 * Listens for Global Score updates and fetches/updates recommendations dynamically.
 */

document.addEventListener('DOMContentLoaded', function () {
	/**
	 * Updates the recommendations widget UI based on the fetched data.
	 *
	 * @param {Object} data - The recommendations data from the API.
	 * @param {string} data.status - Status: 'pending', 'loading', 'completed', 'failed'.
	 * @param {Array} data.recommendations - Array of recommendation objects.
	 * @param {Object} data.metadata - Optional metadata about recommendations.
	 */
	function updateRecommendationsWidget(data) {
		const widget = document.querySelector('.wpr-recommendations-widget');
		
		if (!widget) {
			return;
		}

		// Update widget based on status
		switch (data.status) {
			case 'loading':
				widget.classList.add('wpr-recommendations-loading');
				widget.classList.remove('wpr-recommendations-completed', 'wpr-recommendations-failed');
				// Optionally show loading state in the UI
				break;

			case 'completed':
				widget.classList.remove('wpr-recommendations-loading', 'wpr-recommendations-failed');
				widget.classList.add('wpr-recommendations-completed');
				
				// Update the recommendations list if it exists
				const recommendationsList = widget.querySelector('.wpr-recommendations-list');
				if (recommendationsList && data.recommendations) {
					// This would be replaced with actual rendering logic
					// For now, we're just ensuring the widget exists and can be updated
					console.log('Recommendations updated:', data.recommendations.length, 'items');
				}
				break;

			case 'failed':
				widget.classList.remove('wpr-recommendations-loading', 'wpr-recommendations-completed');
				widget.classList.add('wpr-recommendations-failed');
				// Optionally show error state in the UI
				if (data.error) {
					console.error('Recommendations fetch failed:', data.error);
				}
				break;

			case 'pending':
			default:
				widget.classList.remove('wpr-recommendations-loading', 'wpr-recommendations-completed', 'wpr-recommendations-failed');
				break;
		}
	}

	/**
	 * Fetches the current recommendations status from the REST API.
	 */
	function fetchRecommendationsStatus() {
		// Use WordPress REST API client if available
		if (window.wp && window.wp.apiFetch) {
			window.wp.apiFetch({
				path: '/wp-rocket/v1/recommendations/status'
			})
				.then(function (data) {
					updateRecommendationsWidget(data);
				})
				.catch(function (error) {
					console.error('Failed to fetch recommendations status:', error);
				});
		} else {
			// Fallback to fetch API
			fetch(window.wpApiSettings?.root + 'wp-rocket/v1/recommendations/status', {
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
	document.addEventListener('wprGlobalScoreUpdated', function (event) {
		console.log('Global Score updated, fetching recommendations status');
		fetchRecommendationsStatus();
	});

	/**
	 * Fetch recommendations on page load if widget exists.
	 * This ensures recommendations are loaded even without a Global Score update.
	 */
	const widget = document.querySelector('.wpr-recommendations-widget');
	if (widget) {
		fetchRecommendationsStatus();
	}
});
