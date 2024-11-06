<?php

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

use WP_Error;

/**
 * Class AbstractSafeAPIClient
 *
 * This abstract class provides a base for making API requests.
 * It includes methods for sending GET and POST requests, and handles transient caching to mitigate the impact of API failures.
 *
 * @package WP_Rocket\Engine\Common\JobManager\APIHandler
 */
abstract class AbstractSafeAPIClient {

	/**
	 * Get the transient key.
	 *
	 * @return string The transient key.
	 */
	abstract protected function get_transient_key();

	/**
	 * Get the API URL.
	 *
	 * @return string The API URL.
	 */
	abstract protected function get_api_url();

	/**
	 * Send a GET request.
	 *
	 * @param array $params The request parameters.
	 * @param bool  $safe Send safe request WP functions or not, default to not.
	 *
	 * @return mixed The response from the API.
	 */
	public function send_get_request( $params = [], $safe = false ) {
		return $this->send_request( 'GET', $params, $safe );
	}

	/**
	 * Send a POST request.
	 *
	 * @param array $params The request parameters.
	 * @param bool  $safe Send safe request WP functions or not, default to not.
	 *
	 * @return mixed The response from the API.
	 */
	public function send_post_request( $params = [], $safe = false ) {
		return $this->send_request( 'POST', $params, $safe );
	}

	/**
	 * Send a request to the API.
	 *
	 * @param string $method The HTTP method (GET or POST).
	 * @param array  $params The request parameters.
	 * @param bool   $safe Send safe request WP functions or not, default to not.
	 * @return mixed The response from the API, or WP_Error if a timeout is active.
	 */
	private function send_request( $method, $params = [], $safe = false ) {
		$api_url = $this->get_api_url();

		$transient_key = $this->get_transient_key();
		if ( get_transient( $transient_key . '_timeout_active' ) ) {
			return new WP_Error( 429, __( 'Too many requests.', 'rocket' ) );
		}
		// Get previous_expiration early to avoid multiple parallel requests increasing the expiration multiple times.
		$previous_expiration = (int) get_transient( $transient_key . '_timeout' );

		$params['method'] = strtoupper( $method );

		$response = $this->send_remote_request( $api_url, $method, $params, $safe );

		if ( is_wp_error( $response ) ) {
			$this->set_timeout_transients( $previous_expiration );
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) || ( ! empty( $response['response']['code'] ) && 200 !== $response['response']['code'] ) ) {
			$this->set_timeout_transients( $previous_expiration );
			return new WP_Error( 500, __( 'Not valid response.', 'rocket' ) );
		}

		$this->delete_timeout_transients();

		return $response;
	}

	/**
	 * Set the timeout transients.
	 *
	 * @param string $previous_expiration The previous value of _timeout_active transient.
	 */
	private function set_timeout_transients( $previous_expiration ) {
		$transient_key = $this->get_transient_key();

		$expiration = ( 0 === (int) $previous_expiration )
			? 300
			: ( 2 * (int) $previous_expiration <= DAY_IN_SECONDS
				? 2 * (int) $previous_expiration
				: DAY_IN_SECONDS
			);

		set_transient( $transient_key . '_timeout', $expiration, WEEK_IN_SECONDS );
		set_transient( $transient_key . '_timeout_active', true, $expiration );
	}

	/**
	 * Delete the timeout transients.
	 *
	 * This method deletes the timeout transients for the API requests. It uses the transient key obtained from the `get_transient_key` method.
	 * The transients deleted are:
	 * - `{transient_key}_timeout_active`: This transient indicates if a timeout is currently active.
	 * - `{transient_key}_timeout`: This transient stores the timeout duration.
	 *
	 * @return void
	 */
	private function delete_timeout_transients() {
		$transient_key = $this->get_transient_key();
		delete_transient( $transient_key . '_timeout_active' );
		delete_transient( $transient_key . '_timeout' );
	}

	/**
	 * Decide which WP core function will be used to send the request based on the params.
	 *
	 * @param string $api_url API Url.
	 * @param string $method Request method (GET or POST).
	 * @param array  $params Parameters being sent with the request.
	 * @param bool   $safe Send safe request WP functions or not, default to not.
	 * @return array|WP_Error
	 */
	private function send_remote_request( $api_url, $method, $params, $safe ) {
		if ( ! $safe ) {
			return wp_remote_request( $api_url, $params );
		}

		unset( $params['method'] );

		switch ( $method ) {
			case 'GET':
				return wp_safe_remote_get( $api_url, $params );
			case 'POST':
				return wp_safe_remote_post( $api_url, $params );
		}

		return new WP_Error( 400, __( 'Not valid request type.', 'rocket' ) );
	}
}
