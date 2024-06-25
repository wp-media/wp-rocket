<?php

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

use WP_Rocket\Admin\Options_Data;

/**
 * AbstractSafeAPIClient is an abstract class that provides a base for making API requests.
 * It includes methods for sending GET and POST requests, and handles transient caching to mitigate the impact of API failures.
 */
abstract class AbstractSafeAPIClient
{
	private $options;

	public function __construct(Options_Data $options)
	{
		$this->options = $options;
	}

	/**
	 * Get the transient key.
	 *
	 * @return string The transient key.
	 */
	abstract protected function getTransientKey();

	/**
	 * Get the API URL.
	 *
	 * @return string The API URL.
	 */
	abstract protected function getApiUrl();

	/**
	 * Send a GET request.
	 *
	 * @param array $params The request parameters.
	 * @return mixed The response from the API.
	 */
	public function send_get_request($params)
	{
		return $this->send_request('GET', $params);
	}

	/**
	 * Send a POST request.
	 *
	 * @param array $params The request parameters.
	 * @return mixed The response from the API.
	 */
	protected function send_post_request($params)
	{
		return $this->send_request('POST', $params);
	}

	/**
	 * Send a request to the API.
	 *
	 * @param string $method The HTTP method (GET or POST).
	 * @param array $params The request parameters.
	 * @return mixed The response from the API, or false if a timeout is active.
	 */
	private function send_request($method, $params)
	{
		$transientKey = $this->getTransientKey();
		$apiUrl = $this->getApiUrl();

		if (get_transient($transientKey . '_timeout_active') === true) {
			return false;
		}

		if (empty($params['body'])) {
			$params['body'] = [];
		}

		$params['body']['credentials'] = [
			'wpr_email' => $this->options->get('consumer_email', ''),
			'wpr_key' => $this->options->get('consumer_key', ''),
		];

		$params['method'] = strtoupper($method);
		$response = wp_remote_request($apiUrl, $params);

		if (is_wp_error($response)) {
			$this->set_timeout_transients($transientKey);
			return false;
		}

		delete_transient($transientKey . '_timeout_active');
		delete_transient($transientKey . '_timeout');

		return $response;
	}

	/**
	 * Set the timeout transients.
	 *
	 * @param string $transientKey The transient key.
	 */
	private function set_timeout_transients($transientKey)
	{
		$timeout = (int) get_transient($transientKey . '_timeout');
		$timeout = (0 === $timeout)
			? 300
			: (2 * $timeout <= DAY_IN_SECONDS
				? 2 * $timeout
				: DAY_IN_SECONDS
			);

		set_transient($transientKey . '_timeout', $timeout, WEEK_IN_SECONDS);
		set_transient($transientKey . '_timeout_active', true, $timeout);
	}
}
