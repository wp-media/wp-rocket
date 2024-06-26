<?php

namespace WP_Rocket\Engine\Plugin;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;

/**
 * Class PluginUpdateClient
 *
 * This class extends the AbstractSafeAPIClient class and provides methods for
 * getting the transient key and API URL specific to plugin updates.
 *
 * @package WP_Rocket\Engine\Common\JobManager\APIHandler
 */
class UpdaterAPIClient extends AbstractSafeAPIClient {

	/**
	 * Get the transient key for plugin updates.
	 *
	 * This method returns the transient key used for caching plugin updates.
	 *
	 * @return string The transient key for plugin updates.
	 */
	protected function get_transient_key() {
		return 'wp_rocket_plugin_update';
	}

	/**
	 * Get the API URL for plugin updates.
	 *
	 * This method returns the API URL used for fetching plugin updates.
	 *
	 * @return string The API URL for plugin updates.
	 */
	protected function get_api_url() {
		return rocket_get_constant( 'WP_ROCKET_WEB_CHECK', 'https://wp-rocket.me/check_update.php' );
	}
}
