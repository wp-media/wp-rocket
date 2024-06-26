<?php

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

use WP_Rocket\Admin\Options_Data;

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
	 * Options data.
	 *
	 * @var Options_Data $options WP Rocket options data.
	 */
	private $options;

	/**
	 * AbstractSafeAPIClient constructor.
	 *
	 * @param Options_Data $options WP Rocket options data.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

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
	public function send_get_request( $params ) {
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
	 * @return mixed The response from the API, or false if a timeout is active.
	 */
	private function send_request( $method, $params ) {
		$transient_key = $this->get_transient_key();
		$api_url       = $this->get_api_url();

		if ( get_transient( $transient_key . '_timeout_active' ) === true ) {
			return false;
		}

		if ( empty( $params['body'] ) ) {
			$params['body'] = [];
		}

		$params['body']['credentials'] = [
			'wpr_email' => $this->options->get( 'consumer_email', '' ),
			'wpr_key'   => $this->options->get( 'consumer_key', '' ),
		];

		$params['method'] = strtoupper( $method );
		$response         = wp_remote_request( $api_url, $params );

		if ( is_wp_error( $response ) ) {
			$this->set_timeout_transients( $transient_key );
			return false;
		}

		delete_transient( $transient_key . '_timeout_active' );
		delete_transient( $transient_key . '_timeout' );

		return $response;
	}

	/**
	 * Set the timeout transients.
	 *
	 * @param string $transient_key The transient key.
	 */
	private function set_timeout_transients( $transient_key ) {
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
}
