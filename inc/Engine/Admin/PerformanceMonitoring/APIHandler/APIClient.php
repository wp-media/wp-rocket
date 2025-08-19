<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\APIHandler;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Performance Monitoring API Client
 *
 * Handles communication with the SaaS Director API for performance testing
 */
class APIClient extends AbstractAPIClient implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * SaaS Director API path for performance tests.
	 *
	 * @var string
	 */
	protected $request_path = '';

	/**
	 * Initiate a performance test with the SaaS Director API.
	 *
	 * @param string $url The URL to test.
	 * @param array  $options Test options (device, location, etc.).
	 * @return array|\WP_Error
	 */
	public function add_to_queue( string $url, array $options = [] ) {
		$this->request_path = 'performance/submit';

		/**
		 * Filter the url that is sent to Saas.
		 *
		 * @param string $url contains the URL that is sent to Saas.
		 * @param string $optimization_type Optimization type.
		 */
		$url = apply_filters( 'rocket_saas_api_queued_url', $url, 'performance_monitoring' );

		$args = [
			'body'    => [
				'email' => $this->options->get( 'consumer_email', '' ),
				'key'   => $this->options->get( 'consumer_key', '' ),
				'url'      => $url,
				'is_priority' => $options['is_home'] ?? false,
				'device'  => ! $options['is_mobile'] ? 'desktop' : 'mobile',
				'region'  => $options['region'] ?? '',
			],
			'timeout' => 30, // Longer timeout for initiation.
		];

		$this->logger::debug(
			'Performance Monitoring: Initiating test',
			[
				'url'     => $url,
				'options' => $options,
			]
		);

		$sent = $this->handle_post( $args );

		if ( ! $sent ) {
			$error_data = [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];

			$this->logger::error(
				'Performance Monitoring: Test initiation failed',
				$error_data
			);

			return $error_data;
		}

		$response_data = json_decode( $this->response_body, true );

		if ( ! $response_data || ! isset( $response_data['test_id'] ) ) {
			$error_data = [
				'code'    => 400,
				'message' => 'Invalid API response - missing test_id',
			];

			$this->logger::error(
				'Performance Monitoring: Invalid API response',
				$error_data
			);

			return $error_data;
		}

		$this->logger::info(
			'Performance Monitoring: Test initiated successfully',
			[
				'test_id'              => $response_data['test_id'],
				'status'               => $response_data['status'] ?? 'unknown',
				'estimated_completion' => $response_data['estimated_completion'] ?? null,
			]
		);

		return [
			'code'                 => 200,
			'test_id'              => $response_data['test_id'],
			'status'               => $response_data['status'] ?? 'running',
			'estimated_completion' => $response_data['estimated_completion'] ?? null,
		];
	}

	/**
	 * Get the status of a performance test.
	 *
	 * @param string $test_id The external test ID.
	 * @return array|\WP_Error
	 */
	public function get_queue_job_status( string $test_id, $queue_name, $is_home = false ) {
		$this->request_path = 'performance/result?uuid=' . $test_id;

		$args = [
			'timeout' => 15,
			'headers' => [
				'X-WP-Rocket-Email' => $this->options->get( 'consumer_email', '' ),
				'X-WP-Rocket-Key'   => $this->options->get( 'consumer_key', '' ),
			],
		];

		$this->logger::debug(
			'Performance Monitoring: Checking test status',
			[ 'test_id' => $test_id ]
		);

		$sent = $this->handle_get( $args );

		if ( ! $sent ) {
			$error_data = [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];

			$this->logger::error(
				'Performance Monitoring: Status check failed',
				array_merge( $error_data, [ 'test_id' => $test_id ] )
			);

			return $error_data;
		}

		$response_data = json_decode( $this->response_body, true );

		if ( ! $response_data ) {
			$error_data = [
				'code'    => 400,
				'message' => 'Invalid API response - malformed JSON',
			];

			$this->logger::error(
				'Performance Monitoring: Invalid status response',
				array_merge( $error_data, [ 'test_id' => $test_id ] )
			);

			return $error_data;
		}

		$this->logger::debug(
			'Performance Monitoring: Status check completed',
			[
				'test_id' => $test_id,
				'status'  => $response_data['status'] ?? 'unknown',
			]
		);

		return [
			'code'   => 200,
			'status' => $response_data['status'] ?? 'running',
			'data'   => $response_data['data'] ?? null,
		];
	}
}
