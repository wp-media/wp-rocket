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
	 * @return mixed The response from the API.
	 */
	public function send_get_request( $params = [] ) {
		return $this->send_request( 'GET', $params );
	}

	/**
	 * Send a POST request.
	 *
	 * @param array $params The request parameters.
	 * @return mixed The response from the API.
	 */
	public function send_post_request( $params ) {
		return $this->send_request( 'POST', $params );
	}

	/**
	 * Send a request to the API.
	 *
	 * @param string $method The HTTP method (GET or POST).
	 * @param array  $params The request parameters.
	 * @return mixed The response from the API, or WP_Error if a timeout is active.
	 */
	private function send_request( $method, $params ) {
		$api_url = $this->get_api_url();

		if ( true === get_transient( $this->get_transient_key() . '_timeout_active' ) ) {
			return new WP_Error( 429, __( 'Too many requests.', 'rocket' ) );
		}

		if ( empty( $params['body'] ) ) {
			$params['body'] = [];
		}

		$params['method'] = strtoupper( $method );
		$response         = wp_remote_request( $api_url, $params );

		if ( is_wp_error( $response ) || ( is_array( $response ) && 200 !== $response['response']['code'] ) ) {
			$this->set_timeout_transients();
			return false;
		}

		$this->delete_timeout_transients();

		return $response;
	}

	/**
	 * Set the timeout transients.
	 */
	protected function set_timeout_transients() {
		$transient_key = $this->get_transient_key();

		$timeout = (int) get_transient( $transient_key . '_timeout' );
		$timeout = ( 0 === $timeout )
			? 300
			: ( 2 * $timeout <= DAY_IN_SECONDS
				? 2 * $timeout
				: DAY_IN_SECONDS
			);

		set_transient( $transient_key . '_timeout', $timeout, WEEK_IN_SECONDS );
		set_transient( $transient_key . '_timeout_active', true, $timeout );
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
	protected function delete_timeout_transients() {
		$transient_key = $this->get_transient_key();
		delete_transient( $transient_key . '_timeout_active' );
		delete_transient( $transient_key . '_timeout' );
	}
}
