<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PerformanceTests_Query;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\API\Client as APIClient;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\Managers\AbstractManager;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

/**
 * Performance Monitoring Jobs Manager
 */
class Manager implements ManagerInterface, LoggerAwareInterface {
	use LoggerAware;
	use AbstractManager;

	/**
	 * Performance Tests Query instance.
	 *
	 * @var PerformanceTests_Query
	 */
	protected $query;

	/**
	 * API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Performance Monitoring Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * The type of optimization applied for the current job.
	 *
	 * @var string
	 */
	protected $optimization_type = 'performance_monitoring';

	/**
	 * Check if manager can process.
	 *
	 * @var boolean
	 */
	protected $can_process = true;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param PerformanceTests_Query $query Performance Tests Query instance.
	 * @param APIClient              $api_client API Client instance.
	 * @param ContextInterface       $context Performance Monitoring Context.
	 * @param Options_Data           $options Options instance.
	 */
	public function __construct(
		PerformanceTests_Query $query,
		APIClient $api_client,
		ContextInterface $context,
		Options_Data $options
	) {
		$this->query      = $query;
		$this->api_client = $api_client;
		$this->context    = $context;
		$this->options    = $options;
	}

	/**
	 * Get pending jobs from db.
	 *
	 * @param integer $num_rows Number of rows to grab.
	 * @return array
	 */
	public function get_pending_jobs( int $num_rows ): array {
		$this->logger::debug( "Performance Monitoring: Start getting number of {$num_rows} pending jobs." );

		$pending_jobs = $this->query->get_pending_jobs( $num_rows );

		if ( ! $pending_jobs ) {
			$this->logger::debug( 'Performance Monitoring: No pending jobs are there.' );
			return [];
		}

		return $pending_jobs;
	}

	/**
	 * Validate SaaS response and fail job.
	 *
	 * @param array  $job_details Details related to the job.
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	public function validate_and_fail( array $job_details, $row_details, string $optimization_type ): void {
		// Implementation for handling failed performance tests.
		$this->logger::error(
			'Performance Monitoring: Job validation failed',
			[
				'job_id'   => $job_details['id'] ?? null,
				'page_url' => $row_details->url ?? null,
			]
		);

		$this->query->update_status( $row_details->id, 'failed' );
	}

	/**
	 * Process performance monitoring job.
	 *
	 * @param array  $job_details Details related to the job.
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	public function process( array $job_details, $row_details, string $optimization_type ): void {
		$this->logger::debug(
			'Performance Monitoring: Processing job',
			[
				'job_id' => $job_details['id'] ?? null,
				'status' => $job_details['status'] ?? null,
			]
		);

		switch ( $job_details['status'] ) {
			case 'completed':
				$this->handle_completed_test( $job_details, $row_details );
				break;
			case 'running':
				$this->schedule_status_check( $row_details );
				break;
			case 'failed':
				$this->handle_failed_test( $job_details, $row_details );
				break;
		}
	}

	/**
	 * Handle completed performance test.
	 *
	 * @param array  $job_details Details from SaaS API.
	 * @param object $row_details Database row details.
	 */
	private function handle_completed_test( array $job_details, $row_details ): void {
		$parsed_data = $this->api_client->parse_test_results( $job_details );

		$test_data = array_merge(
			$parsed_data,
			[
				'completed_at' => gmdate( 'Y-m-d H:i:s' ),
			]
			);

		$this->query->update_test_data( $row_details->id, 'completed', $test_data );

		$this->logger::info(
			'Performance Monitoring: Test completed successfully',
			[
				'test_id' => $row_details->test_id,
				'score'   => $parsed_data['performance_score'] ?? null,
			]
		);
	}

	/**
	 * Handle failed performance test.
	 *
	 * @param array  $job_details Details from SaaS API.
	 * @param object $row_details Database row details.
	 */
	private function handle_failed_test( array $job_details, $row_details ): void {
		$error_message = $job_details['message'] ?? 'Performance test failed';

		$this->query->update_status( $row_details->id, 'failed', $error_message );

		$this->logger::warning(
			'Performance Monitoring: Test failed',
			[
				'test_id' => $row_details->test_id,
				'error'   => $error_message,
			]
		);
	}

	/**
	 * Schedule recurring status check for running test.
	 *
	 * @param object $row_details Database row details.
	 */
	private function schedule_status_check( $row_details ): void {
		// This would be implemented with the Queue system.
		// For now, we just log that a status check should be scheduled.
		$this->logger::debug(
			'Performance Monitoring: Test still running, should schedule next status check',
			[ 'test_id' => $row_details->test_id ]
		);
	}

	/**
	 * Set request parameters for API calls.
	 *
	 * @return array
	 */
	public function set_request_param(): array {
		return [
			'timeout' => 15,
			'headers' => [
				'X-WP-Rocket-Email' => $this->options->get( 'consumer_email', '' ),
				'X-WP-Rocket-Key'   => $this->options->get( 'consumer_key', '' ),
			],
		];
	}

	/**
	 * Get optimization type from row.
	 *
	 * @param object $row Database row.
	 * @return string
	 */
	public function get_optimization_type_from_row( $row ): string {
		return $this->optimization_type;
	}
}
