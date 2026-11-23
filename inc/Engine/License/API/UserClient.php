<?php

namespace WP_Rocket\Engine\License\API;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;

class UserClient extends AbstractSafeAPIClient {
	/**
	 * Use the CustomerDataTrait
	 */
	use CustomerDataTrait;

	const USER_ENDPOINT = 'https://api.wp-rocket.me/stat/1.0/wp-rocket/user.php';

	/**
	 * Status codes of the user endpoint's answers about the licence itself, such as an unknown user. Their body is JSON with `success` false.
	 */
	const LICENCE_ERROR_CODES = [ 403, 404 ];

	/**
	 * Cache duration, in seconds, of an answer that reports a licence error.
	 */
	const LICENCE_ERROR_CACHE_DURATION = 5 * MINUTE_IN_SECONDS;

	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Get the transient key for plugin updates.
	 *
	 * This method returns the transient key used for caching plugin updates.
	 *
	 * @return string The transient key for plugin updates.
	 */
	protected function get_transient_key() {
		return 'wpr_user_information';
	}

	/**
	 * Get the API URL for plugin updates.
	 *
	 * This method returns the API URL used for fetching plugin updates.
	 *
	 * @return string The API URL for plugin updates.
	 */
	protected function get_api_url() {
		return self::USER_ENDPOINT;
	}

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Gets user data from cache if it exists, else gets it from the user endpoint
	 *
	 * Cache the user data for 24 hours in a transient, and an answer that reports a licence error for a few minutes
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	public function get_user_data() {
		$cached_data = get_transient( 'wp_rocket_customer_data' );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$data = $this->get_raw_user_data();

		if ( false === $data ) {
			return false;
		}

		set_transient(
			'wp_rocket_customer_data',
			$data,
			$this->is_licence_error_data( $data ) ? self::LICENCE_ERROR_CACHE_DURATION : DAY_IN_SECONDS
		);

		return $data;
	}

	/**
	 * Flushes the user data cache.
	 *
	 * @return void
	 */
	public function flush_cache() {
		delete_transient( 'wp_rocket_customer_data' );
	}

	/**
	 * Gets the user data from the user endpoint
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	private function get_raw_user_data() {
		$customer_data = $this->get_customer_data();

		$response = $this->send_post_request(
			[
				'body' => 'user_id=' . rawurlencode( $customer_data['email'] ) . '&consumer_key=' . $customer_data['key'],
			],
			true
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return false;
		}

		return json_decode( $body );
	}

	/**
	 * Validate response code: the licence error answers of the user endpoint are valid too.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_code( $response ) {
		return parent::valid_response_code( $response )
			|| in_array( $response['response']['code'] ?? 0, self::LICENCE_ERROR_CODES, true );
	}

	/**
	 * Validate response body: a licence error answer must carry the JSON of an error, anything else is not an answer of the endpoint.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_body( $response ) {
		if ( ! parent::valid_response_body( $response ) ) {
			return false;
		}

		if ( ! in_array( $response['response']['code'] ?? 0, self::LICENCE_ERROR_CODES, true ) ) {
			return true;
		}

		return $this->is_licence_error_data( json_decode( wp_remote_retrieve_body( $response ) ) );
	}

	/**
	 * Checks whether decoded user data is the answer to a licence error.
	 *
	 * @param mixed $data Decoded user data.
	 * @return bool
	 */
	private function is_licence_error_data( $data ): bool {
		return is_object( $data ) && isset( $data->success ) && false === $data->success;
	}
}
