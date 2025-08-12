<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\API;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Error;

/**
 * Performance Monitoring API Client
 *
 * Handles communication with the SaaS Director API for performance testing
 */
class Client extends AbstractAPIClient implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * SaaS Director API path for performance tests.
	 *
	 * @var string
	 */
	protected $request_path = 'performance-test';

	/**
	 * Initiate a performance test with the SaaS Director API.
	 *
	 * @param string $url The URL to test.
	 * @param array  $options Test options (device, location, etc.).
	 * @return array|WP_Error
	 */
	public function initiate_test( string $url, array $options = [] ): array {
		$this->request_path = 'performance-test/initiate';

		// Default options
		$default_options = [
			'device'   => 'desktop',
			'location' => 'auto',
		];

		$options = array_merge( $default_options, $options );

		$args = [
			'body' => [
				'url'      => $url,
				'priority' => true, // Synchronous priority request
				'options'  => $options,
			],
			'timeout' => 30, // Longer timeout for initiation
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
	 * @return array|WP_Error
	 */
	public function get_test_status( string $test_id ): array {
		$this->request_path = "performance-test/{$test_id}/status";

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

	/**
	 * Parse the completed test data from API response.
	 *
	 * @param array $api_response The raw API response data.
	 * @return array Parsed test data ready for database storage.
	 */
	public function parse_test_results( array $api_response ): array {
		if ( ! isset( $api_response['data']['data'] ) ) {
			return [];
		}

		$test_data = $api_response['data']['data'];

		return [
			'gtmetrix_id'               => $test_data['gtmetrix_id'] ?? null,
			'report_url'                => $test_data['report_url'] ?? null,
			'performance_score'         => $test_data['performance_score'] ?? null,
			'structure_score'           => $test_data['structure_score'] ?? null,
			'largest_contentful_paint'  => $test_data['largest_contentful_paint'] ?? null,
			'total_blocking_time'       => $test_data['total_blocking_time'] ?? null,
			'cumulative_layout_shift'   => $test_data['cumulative_layout_shift'] ?? null,
			'first_contentful_paint'    => $test_data['first_contentful_paint'] ?? null,
			'time_to_interactive'       => $test_data['time_to_interactive'] ?? null,
			'speed_index'               => $test_data['speed_index'] ?? null,
			'fully_loaded_time'         => $test_data['fully_loaded_time'] ?? null,
			'page_size'                 => $test_data['page_size'] ?? null,
			'requests'                  => $test_data['requests'] ?? null,
			'server_name'               => $api_response['data']['server_name'] ?? null,
			'region_name'               => $api_response['data']['region_name'] ?? null,
			'browser_name'              => $api_response['data']['browser_name'] ?? null,
			'platform'                  => $api_response['data']['platform'] ?? null,
		];
	}
}
