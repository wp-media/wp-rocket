<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License\API;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;

class RemoteSettingsClient extends AbstractSafeAPIClient {
	/**
	 * Use the CustomerDataTrait
	 */
	use CustomerDataTrait;

	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * The API URL for remote settings.
	 */
	const REMOTE_SETTINGS_ENDPOINT = 'https://api.wp-rocket.me/api/wp-rocket/plugin-settings.php';

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Get the transient key for remote settings data.
	 *
	 * This method returns the transient key used for caching remote settings data
	 * fetched from the API.
	 *
	 * @since 3.20.3
	 *
	 * @return string The transient key for remote settings data.
	 */
	protected function get_transient_key(): string {
		return 'wp_rocket_remote_settings';
	}

	/**
	 * Get the API URL for remote settings.
	 *
	 * This method returns the API URL used for fetching remote settings data.
	 *
	 * @since 3.20.3
	 *
	 * @return string The API URL for remote settings.
	 */
	protected function get_api_url(): string {
		return self::REMOTE_SETTINGS_ENDPOINT;
	}

	/**
	 * Retrieves remote settings data from cache if available; otherwise, fetches it from the remote settings API endpoint.
	 *
	 * The remote settings data is cached in a transient for 24 hours.
	 *
	 * @since 3.20.3
	 *
	 * @return bool|object Remote settings data object on success, false on failure.
	 */
	public function get_remote_settings_data() {
		$cached_data = get_transient( $this->get_transient_key() );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$data = $this->get_raw_remote_settings_data();

		if ( empty( $data->success ) || empty( $data->data ) ) {
			return false;
		}

		set_transient( $this->get_transient_key(), $data->data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Gets the remote settings data from the remote settings API endpoint.
	 *
	 * @since 3.20.3
	 *
	 * @return bool|object Remote settings data object on success, false on failure.
	 */
	private function get_raw_remote_settings_data() {
		// Build the body parameters.
		$body_params                      = $this->get_customer_data();
		$body_params['domain']            = rawurlencode( wp_parse_url( home_url(), PHP_URL_HOST ) );
		$body_params['wp_rocket_version'] = rawurlencode( rocket_get_constant( 'WP_ROCKET_VERSION', '3.20.3' ) );

		// Send the request to the remote settings API endpoint.
		$response = $this->send_post_request(
			[
				'body' => $body_params,
			],
			true
		);

		if ( is_wp_error( $response ) || ( is_array( $response ) && 200 !== $response['response']['code'] ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Flushes the remote settings data cache.
	 *
	 * @since 3.20.3
	 *
	 * @return void
	 */
	public function flush_cache(): void {
		delete_transient( $this->get_transient_key() );
	}
}
