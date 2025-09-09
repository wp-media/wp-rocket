<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PerformanceTests_Query;
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
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param PerformanceTests_Query $query Performance Tests Query instance.
	 * @param ContextInterface       $context Performance Monitoring Context.
	 * @param Options_Data           $options Options instance.
	 */
	public function __construct(
		PerformanceTests_Query $query,
		ContextInterface $context,
		Options_Data $options
	) {
		$this->query   = $query;
		$this->context = $context;
		$this->options = $options;
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
		if ( 'failed' !== $job_details['status'] ) {
			return;
		}

		// Implementation for handling failed performance tests.
		$this->logger::error(
			'Performance Monitoring: Job validation failed',
			[
				'job_id'   => $job_details['id'] ?? null,
				'page_url' => $row_details->url ?? null,
			]
		);

		$this->query->make_status_failed( $row_details->url, $row_details->is_mobile, '', $job_details['message'] ?? 'Failed with no msg' );

		/**
		 * Fires when a performance monitoring job fails.
		 *
		 * @since 3.20
		 *
		 * @param object $row_details Details related to the database row.
		 * @param array  $job_details Details related to the job.
		 */
		do_action( 'rocket_pm_job_failed', $row_details, $job_details );
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
		if ( ! empty( $job_details['status'] ) && 'pending' === $job_details['status'] ) {
			$this->logger::info(
				'Performance Monitoring: Revert to pending because of API status is pending',
				[
					'job_id' => $row_details->job_id,
				]
			);

			$this->query->revert_to_pending( $row_details->id );
			return;
		}

		$this->logger::info(
			'Performance Monitoring: Test completed successfully',
			[
				'job_id' => $row_details->job_id,
				'score'  => $job_details['performance_score'] ?? null,
			]
		);

		$this->query->make_status_completed( $row_details->id, 'completed', $this->parse_test_results( $job_details ) );

		/**
		 * Fires when a performance monitoring job completes successfully.
		 *
		 * @since 3.20
		 *
		 * @param object $row_details Details related to the database row.
		 * @param array  $job_details Details related to the job.
		 */
		do_action( 'rocket_pm_job_completed', $row_details, $job_details );
	}

	/**
	 * Set request parameters for API calls.
	 *
	 * @return array
	 */
	public function set_request_param(): array {
		return [
			'timeout' => 15,
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

	/**
	 * Parse the completed test data from API response.
	 *
	 * @param array $api_response The raw API response data.
	 * @return array Parsed test data ready for database storage.
	 */
	private function parse_test_results( array $api_response ): array {
		$defaults = [
			'report_url'        => '',
			'performance_score' => 0,
		];
		if ( ! isset( $api_response['data']['data'] ) ) {
			return $defaults;
		}

		return wp_parse_args( $api_response['data']['data'], $defaults );
	}

	/**
	 * Process Job ID by saving it into DB.
	 *
	 * @param string $url Row url.
	 * @param array  $response API Response array.
	 * @param bool   $is_mobile Is mobile or not.
	 * @param string $optimization_type Optimization type.
	 *
	 * @return void
	 */
	public function process_jobid( string $url, array $response, bool $is_mobile, string $optimization_type ) {
		$this->make_status_pending(
			$url,
			$response['uuid'],
			'',
			$is_mobile,
			$optimization_type
		);
	}

	/**
	 * Check if we need to allow retry strategies or send job to failed directly based on the feature.
	 *
	 * @return bool
	 */
	public function allow_retry_strategies() {
		return false;
	}
}
