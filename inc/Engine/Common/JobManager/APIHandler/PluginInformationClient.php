<?php

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

/**
 * Class PluginInformationClient
 *
 * This class extends the AbstractSafeAPIClient class and provides methods for
 * getting the transient key and API URL specific to plugin information.
 *
 * @package WP_Rocket\Engine\Common\JobManager\APIHandler
 */
class PluginInformationClient extends AbstractSafeAPIClient {

	/**
	 * Get the transient key for plugin information.
	 *
	 * This method returns the transient key used for caching plugin information.
	 *
	 * @return string The transient key for plugin information.
	 */
	protected function get_transient_key() {
		return 'wp_rocket_plugin_information';
	}

	/**
	 * Get the API URL for plugin information.
	 *
	 * This method returns the API URL used for fetching plugin information.
	 *
	 * @return string The API URL for plugin information.
	 */
	protected function get_api_url() {
		return 'https://wp-rocket.me/plugin_information.php';
	}
}
