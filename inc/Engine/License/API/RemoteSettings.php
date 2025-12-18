<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License\API;

class RemoteSettings {
	/**
	 * The remote settings data object
	 *
	 * @var object
	 */
	private $remote_settings;

	/**
	 * Instantiate the class
	 *
	 * @param object $remote_settings The remote settings object.
	 */
	public function __construct( $remote_settings ) {
		$this->remote_settings = $remote_settings;
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
		if ( ! isset( $this->remote_settings->rocket_insights_remote_setting ) ) {
			return true;
		}

		return (bool) $this->remote_settings->rocket_insights_remote_setting;
	}
}
