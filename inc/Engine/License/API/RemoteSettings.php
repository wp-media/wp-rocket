<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License\API;

class RemoteSettings {
	/**
	 * The remote settings data object
	 *
	 * @var object|null
	 */
	private $remote_settings;

	/**
	 * The remote settings API Client
	 *
	 * @var RemoteSettingsClient
	 */
	private $api_client;

	/**
	 * Instantiate the class
	 *
	 * @param RemoteSettingsClient $api_client The remote settings API Client.
	 */
	public function __construct( RemoteSettingsClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Determines if the Rocket Insights remote setting is enabled.
	 *
	 * Checks if the 'rocket_insights_remote_setting' property exists in the remote settings object.
	 * Returns true if the property is not set, otherwise returns its boolean value.
	 *
	 * @since 3.20.3
	 *
	 * @return bool True if the remote setting is enabled or not set, false otherwise.
	 */
	public function is_rocket_insights_remote_setting_enabled() {
		$this->remote_settings = $this->api_client->get_remote_settings_data();

		if ( ! isset( $this->remote_settings->rocket_insights_display_post_column ) ) {
			return true;
		}

		return (bool) $this->remote_settings->rocket_insights_display_post_column;
	}
}
