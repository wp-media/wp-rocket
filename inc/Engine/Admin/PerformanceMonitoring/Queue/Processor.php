<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Factory;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\API\Client as APIClient;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PerformanceMonitoring_Query;

/**
 * Performance Monitoring Queue Processor
 *
 * Handles background job processing for performance tests
 */
class Processor implements Subscriber_Interface, LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Jobs factory instance.
	 *
	 * @var Factory
	 */
	private $factory;

	/**
	 * API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Queue instance.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Query instance.
	 *
	 * @var PerformanceMonitoring_Query
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Factory                     $factory Jobs factory.
	 * @param APIClient                   $api_client API client.
	 * @param Queue                       $queue Queue instance.
	 * @param PerformanceMonitoring_Query $query Query instance.
	 */
	public function __construct( Factory $factory, APIClient $api_client, Queue $queue, PerformanceMonitoring_Query $query ) {
		$this->factory    = $factory;
		$this->api_client = $api_client;
		$this->queue      = $queue;
		$this->query      = $query;
	}

	/**
	 * Get subscribed events.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'pma_initiate_test'     => 'process_test_initiation',
			'pma_check_test_status' => 'process_status_check',
			'pma_cleanup_old_tests' => 'cleanup_old_tests',
			'init'                  => 'schedule_cleanup_job',
		];
	}

	/**
	 * Process test initiation job.
	 *
	 * @param string $page_url Page URL to test.
	 * @param array  $options Test options.
	 *
	 * @throws \Exception If error happens.
	 */
	public function process_test_initiation( string $page_url, array $options = [] ): void {
		$this->logger::debug( 'Performance Monitoring: Initiating test', [ 'page_url' => $page_url ] );

		try {
			// Create database record.
			$db_id = $this->query->create_test_record( $page_url, $options );

			if ( ! $db_id ) {
				throw new \Exception( 'Failed to create database record' );
			}

			// Send API request to initiate test.
			$response = $this->api_client->initiate_test( $page_url, $options );

			if ( ! $response || ! isset( $response['test_id'] ) ) {
				throw new \Exception( 'Failed to initiate test via API: ' . ( $response['message'] ?? 'Unknown error' ) );
			}

			// Update database with external test ID and set status to 'running'.
			$this->query->update_test_id( $db_id, $response['test_id'], 'running' );

			// Schedule recurring status checks.
			$this->queue->schedule_status_check( $response['test_id'], $db_id );

			$this->logger::info(
				'Performance Monitoring: Test initiated successfully',
				[
					'test_id' => $response['test_id'],
					'db_id'   => $db_id,
				]
			);

		} catch ( \Exception $e ) {
			$this->logger::error(
				'Performance Monitoring: Test initiation failed',
				[
					'error'    => $e->getMessage(),
					'page_url' => $page_url,
				]
			);

			// Mark as failed if we have a database record.
			if ( isset( $db_id ) ) {
				$this->query->update_status( $db_id, 'failed', $e->getMessage() );
			}
		}
	}

	/**
	 * Process status check job with smart polling.
	 *
	 * @param string $test_id External test ID.
	 * @param int    $db_id Database record ID.
	 * @param int    $attempts Current attempt number.
	 * @param int    $max_attempts Maximum attempts.
	 *
	 * @throws \Exception If error happens.
	 */
	public function process_status_check( string $test_id, int $db_id, int $attempts = 0, int $max_attempts = 20 ): void {
		$this->logger::debug(
			'Performance Monitoring: Checking test status',
			[
				'test_id'      => $test_id,
				'attempt'      => $attempts + 1,
				'max_attempts' => $max_attempts,
			]
		);

		try {
			// Check if we've exceeded max attempts.
			if ( $attempts >= $max_attempts ) {
				$this->query->update_status( $db_id, 'failed', 'Test timed out after maximum attempts' );
				$this->logger::warning(
					'Performance Monitoring: Test timed out',
					[
						'test_id'  => $test_id,
						'attempts' => $attempts,
					]
				);
				return;
			}

			// Get test status from API.
			$response = $this->api_client->get_test_status( $test_id );

			if ( ! $response || ! isset( $response['status'] ) ) {
				throw new \Exception( 'Failed to get test status from API: ' . ( $response['message'] ?? 'Unknown error' ) );
			}

			switch ( $response['status'] ) {
				case 'complete':
					$this->handle_completed_test( $test_id, $db_id, $response );
					break;

				case 'failed':
					$error_message = $response['data']['error'] ?? 'Test failed on SaaS side';
					$this->query->update_status( $db_id, 'failed', $error_message );
					$this->logger::warning(
						'Performance Monitoring: Test failed',
						[
							'test_id' => $test_id,
							'error'   => $error_message,
						]
						);
					break;

				case 'running':
				default:
					// Schedule next check with exponential backoff.
					$this->schedule_next_status_check( $test_id, $db_id, $attempts + 1, $max_attempts );
					break;
			}
		} catch ( \Exception $e ) {
			$this->logger::error(
				'Performance Monitoring: Status check failed',
				[
					'error'   => $e->getMessage(),
					'test_id' => $test_id,
				]
			);

			// Schedule retry with exponential backoff.
			$this->schedule_next_status_check( $test_id, $db_id, $attempts + 1, $max_attempts );
		}
	}

	/**
	 * Schedule next status check with 5-second intervals as requested.
	 *
	 * @param string $test_id External test ID.
	 * @param int    $db_id Database record ID.
	 * @param int    $attempts Current attempt number.
	 * @param int    $max_attempts Maximum attempts.
	 */
	private function schedule_next_status_check( string $test_id, int $db_id, int $attempts, int $max_attempts ): void {
		// Always check every 5 seconds as requested.
		$delay = 5;

		// Stop after 10 minutes (120 attempts * 5 seconds = 600 seconds).
		if ( $attempts >= 120 ) {
			$this->query->update_status( $db_id, 'failed', 'Test timed out after 10 minutes' );
			$this->logger::warning( 'Performance Monitoring: Test timed out', [ 'test_id' => $test_id ] );
			return;
		}

		$args = [
			'test_id'      => $test_id,
			'db_id'        => $db_id,
			'attempts'     => $attempts,
			'max_attempts' => $max_attempts,
		];

		$this->queue->schedule_single( time() + $delay, 'pma_check_test_status', $args );
	}

	/**
	 * Handle completed test processing.
	 *
	 * @param string $test_id External test ID.
	 * @param int    $db_id Database record ID.
	 * @param array  $response API response data.
	 */
	private function handle_completed_test( string $test_id, int $db_id, array $response ): void {
		$parsed_data = $this->api_client->parse_test_results( $response );

		$test_data = array_merge(
			$parsed_data,
			[
				'completed_at' => gmdate( 'Y-m-d H:i:s' ),
			]
			);

		$this->query->update_test_data( $db_id, 'completed', $test_data );

		$this->logger::info(
			'Performance Monitoring: Test completed successfully',
			[
				'test_id' => $test_id,
				'score'   => $parsed_data['performance_score'] ?? null,
			]
		);
	}

	/**
	 * Schedule cleanup job on init.
	 */
	public function schedule_cleanup_job(): void {
		$this->queue->schedule_cleanup_job();
	}

	/**
	 * Clean up old test records.
	 */
	public function cleanup_old_tests(): void {
		$this->logger::debug( 'Performance Monitoring: Starting cleanup of old tests' );

		$deleted_count = $this->query->delete_old_tests( 30 ); // 30 days retention

		$this->logger::info(
			'Performance Monitoring: Cleanup completed',
			[ 'deleted_tests' => $deleted_count ]
		);
	}
}
