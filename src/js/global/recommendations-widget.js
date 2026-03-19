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
	 */
	function updateRecommendationsWidget(data) {
		const widget = $('.wpr-recommendations');

		if (!widget || !data?.recommendations?.html) {
			return;
		}

		// Update the widget content with the new recommendations HTML
		widget.replaceWith(data?.recommendations?.html);
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
	 * Check if recommendations widget is in loading state and trigger fetch if needed.
	 */
	function checkAndFetchIfLoading() {
		const widget = $('.wpr-recommendations');
		if (widget && widget.attr('data-state') === 'loading') {
			// Delay fetch slightly to avoid blocking page render
			setTimeout(fetchRecommendationsStatus, 500);
		}
	}

	/**
	 * Listen for Global Score update event.
	 * This is fired by ajax.js when the Global Score polling detects a change.
	 */
	$(document).on('wprGlobalScoreUpdated rocket-insights-page-added rocket-insights-page-retest', () => {
		fetchRecommendationsStatus();
	});

	// Check if recommendations need to be fetched on page load
	checkAndFetchIfLoading();
});
