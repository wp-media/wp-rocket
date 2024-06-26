<?php

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

/**
 * Class PluginPricingClient
 *
 * This class extends the AbstractSafeAPIClient class and provides methods for
 * getting the transient key and API URL specific to plugin updates.
 *
 * @package WP_Rocket\Engine\Common\JobManager\APIHandler
 */
class PluginPricingClient extends AbstractSafeAPIClient {

	/**
	 * Get the transient key for plugin updates.
	 *
	 * This method returns the transient key used for caching plugin updates.
	 *
	 * @return string The transient key for plugin updates.
	 */
	protected function get_transient_key() {
		return 'wp_rocket_pricing';
	}

	/**
	 * Get the API URL for plugin updates.
	 *
	 * This method returns the API URL used for fetching plugin updates.
	 *
	 * @return string The API URL for plugin updates.
	 */
	protected function get_api_url() {
		return 'https://wp-rocket.me/stat/1.0/wp-rocket/pricing-2023.php';
	}
}
