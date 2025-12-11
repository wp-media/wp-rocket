<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\APIHandler;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Rocket Insights API Client
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
	protected $request_path = 'performance/';

	/**
	 * Initiate a performance test with the SaaS Director API.
	 *
	 * @param string $url The URL to test.
	 * @param array  $options Test options (device, location, etc.).
	 * @param array  $args Additional request arguments (timeout, headers, etc.).
	 * @return array|\WP_Error
	 */
	public function add_to_queue( string $url, array $options = [], array $args = [] ) {
		$url = user_trailingslashit( $url );
		$url = $this->filter_url( $url, 'rocket_insights' );

		$request_body = [
			'email'       => $this->options->get( 'consumer_email', '' ),
			'key'         => $this->options->get( 'consumer_key', '' ),
			'url'         => $url,
			'is_priority' => $options['is_home'] ?? false,
		];

		$args = array_merge(
			[
				'json_encode' => true,
				'body'        => $request_body,
				'headers'     => [
					'Content-Type' => 'application/json',
				],
			],
			$args
		);

		$this->logger::debug(
			'Rocket Insights: Initiating test',
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
				'Rocket Insights: Test initiation failed',
				$error_data
			);

			return $error_data;
		}

		$response_data = json_decode( $this->response_body, true );

		$this->logger::info(
			'Rocket Insights: Test initiated successfully',
			[
				'job_id' => $response_data['uuid'],
			]
		);

		$response_data['code'] = $this->response_code;

		return $response_data;
	}

	/**
	 * Get the status of a performance test.
	 *
	 * @param string $test_id The external test ID.
	 * @param string $queue_name Queue name just in case.
	 * @param bool   $is_home Url is Homepage.
	 *
	 * @return array|\WP_Error
	 */
	public function get_queue_job_status( string $test_id, $queue_name, $is_home = false ) {
		$args = [
			'body'    => [
				'uuid' => $test_id,
			],
			'timeout' => 15,
		];

		$this->logger::debug(
			'Rocket Insights: Checking test status',
			[ 'test_id' => $test_id ]
		);

		$sent = $this->handle_get( $args );

		if ( ! $sent ) {
			$error_data = [
				'code'    => $this->response_code,
				'message' => $this->error_message,
				'status'  => 'failed',
			];

			$this->logger::error(
				'Rocket Insights: Status check failed',
				array_merge( $error_data, [ 'test_id' => $test_id ] )
			);

			return $error_data;
		}

		$response_data = json_decode( $this->response_body, true );

		if ( ! $response_data ) {
			$error_data = [
				'code'    => 400,
				'message' => 'Invalid API response - malformed JSON',
				'status'  => 'failed',
			];

			$this->logger::error(
				'Rocket Insights: Invalid status response',
				array_merge( $error_data, [ 'test_id' => $test_id ] )
			);

			return $error_data;
		}

		$this->logger::debug(
			'Rocket Insights: Status check completed',
			[
				'test_id' => $test_id,
				'status'  => $response_data['status'] ?? 'unknown',
			]
		);

		switch ( $response_data['status'] ?? 'pending' ) {
			case 'pending':
				$code = 425;
				break;
			case 'failed':
				$code = 500;
				break;
			default:
				$code = 200;
				break;
		}
		return [
			'code'   => $code,
			'status' => $response_data['status'] ?? 'pending',
			'data'   => $response_data['data'] ?? null,
		];
	}

	/**
	 * Validate add to queue response if it's valid or not..
	 *
	 * @param array $response Response array.
	 * @return bool
	 */
	public function validate_add_to_queue_response( array $response ): bool {
		return ! empty( $response['uuid'] );
	}
}
