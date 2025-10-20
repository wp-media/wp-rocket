<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Jobs;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;
use WP_Rocket\Engine\Admin\RocketInsights\APIHandler\APIClient;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Common\JobManager\Managers\AbstractManager;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

/**
 * Rocket Insights Jobs Manager
 */
class Manager implements ManagerInterface, LoggerAwareInterface {
	use LoggerAware;
	use AbstractManager;

	/**
	 * Rocket Insights Query instance.
	 *
	 * @var RocketInsightsQuery
	 */
	protected $query;

	/**
	 * Rocket Insights Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * The type of optimization applied for the current job.
	 *
	 * @var string
	 */
	protected $optimization_type = 'rocket_insights';

	/**
	 * Plan instance.
	 *
	 * @var Plan
	 */
	protected $plan;

	/**
	 * API Client instance.
	 *
	 * @var APIClient
	 */
	protected $api_client;

	/**
	 * Timeout value in seconds for synchronization operations.
	 *
	 * This constant defines the maximum time allowed for sync jobs
	 * before they are considered to have timed out.
	 */
	const SYNC_TIMEOUT = 10;

	/**
	 * Instantiate the class.
	 *
	 * @param RocketInsightsQuery $query Rocket Insights Query instance.
	 * @param ContextInterface    $context Rocket Insights Context.
	 * @param Plan                $plan Plan instance.
	 * @param APIClient           $api_client API Client instance.
	 */
	public function __construct(
		RocketInsightsQuery $query,
		ContextInterface $context,
		Plan $plan,
		APIClient $api_client
	) {
		$this->query      = $query;
		$this->context    = $context;
		$this->plan       = $plan;
		$this->api_client = $api_client;
	}

	/**
	 * Get pending jobs from db.
	 *
	 * @param integer $num_rows Number of rows to grab.
	 * @return array
	 */
	public function get_pending_jobs( int $num_rows ): array {
		$this->logger::debug( "Rocket Insights: Start getting number of {$num_rows} pending jobs." );

		$pending_jobs = $this->query->get_pending_jobs( $num_rows );

		if ( ! $pending_jobs ) {
			$this->logger::debug( 'Rocket Insights: No pending jobs are there.' );
			return [];
		}

		return $pending_jobs;
	}

	/**
	 * Send the request to add url into the queue.
	 *
	 * @param string $url page URL.
	 * @param bool   $is_mobile page is for mobile.
	 * @param array  $additional_details Additional details to be saved into DB.
	 *
	 * @return bool|void
	 */
	public function add_to_the_queue( string $url, bool $is_mobile, array $additional_details = [] ) {
		$additional_details['data'] = wp_parse_args(
			$additional_details['data'] ?? [],
			[
				'start_time' => time(),
				'is_retest'  => false,
			]
			);
		$additional_details['data'] = wp_json_encode( $additional_details['data'] );
		return $this->add_url_to_the_queue( $url, $is_mobile, $additional_details );
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
			'Rocket Insights: Job validation failed',
			[
				'job_id'   => $job_details['id'] ?? null,
				'page_url' => $row_details->url ?? null,
			]
		);

		$this->query->make_status_failed( $row_details->url, $row_details->is_mobile, '', $job_details['message'] ?? 'Failed with no msg' );

		$row_details = $this->query->get_row_by_id( $row_details->id );

		/**
		 * Fires when a Rocket Insights job fails.
		 *
		 * @since 3.20
		 *
		 * @param object $row_details Details related to the database row.
		 * @param array  $job_details Details related to the job.
		 * @param string $plan Plan name.
		 */
		do_action( 'rocket_rocket_insights_job_failed', $row_details, $job_details, $this->plan->get_current_plan() );
	}

	/**
	 * Process Rocket Insights job.
	 *
	 * @param array  $job_details Details related to the job.
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	public function process( array $job_details, $row_details, string $optimization_type ): void {
		// Bail out if status is failed.
		if ( 'failed' === $job_details['status'] ) {
			return;
		}

		if ( ! empty( $job_details['status'] ) && 'pending' === $job_details['status'] ) {
			$this->logger::info(
				'Rocket Insights: Revert to pending because of API status is pending',
				[
					'job_id' => $row_details->job_id,
				]
			);

			$this->query->revert_to_pending( $row_details->id );
			return;
		}

		$this->logger::info(
			'Rocket Insights: Test completed successfully',
			[
				'job_id' => $row_details->job_id,
				'score'  => $job_details['performance_score'] ?? null,
			]
		);

		$this->query->make_status_completed( $row_details->id, 'completed', $this->parse_test_results( $job_details ) );

		$row_details = $this->query->get_row_by_id( $row_details->id );

		/**
		 * Fires when a Rocket Insights job completes successfully.
		 *
		 * @since 3.20
		 *
		 * @param object $row_details Details related to the database row.
		 * @param array  $job_details Details related to the job.
		 * @param string $plan Plan name.
		 */
		do_action( 'rocket_rocket_insights_job_completed', $row_details, $job_details, $this->plan->get_current_plan() );
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
	 * Attempt synchronous submission to GTMetrix API.
	 *
	 * @param string $url URL to test.
	 * @param bool   $is_mobile Is mobile.
	 * @param array  $additional_details Additional details.
	 * @return array|false Returns job data on success, false on failure/timeout.
	 */
	public function try_sync_submission( string $url, bool $is_mobile, array $additional_details = [] ) {
		$options = [
			'is_home' => $additional_details['is_home'] ?? false,
		];

		// Set a shorter timeout for synchronous calls (10 seconds).
		add_filter( 'http_request_timeout', [ $this, 'set_sync_timeout' ], 10 );

		$this->logger::info(
			'Performance Monitoring: Attempting synchronous submission',
			[ 'url' => $url ]
		);

		$response = $this->api_client->add_to_queue( $url, $options );

		remove_filter( 'http_request_timeout', [ $this, 'set_sync_timeout' ], 10 );

		// Check if API call was successful.
		if ( is_wp_error( $response ) || empty( $response['uuid'] ) ) {
			$this->logger::info(
				'Performance Monitoring: Synchronous submission failed, falling back to async',
				[
					'url'   => $url,
					'error' => is_wp_error( $response ) ? $response->get_error_message() : 'No UUID returned',
				]
			);
			return false;
		}

		$this->logger::info(
			'Performance Monitoring: Synchronous submission successful',
			[
				'url'    => $url,
				'job_id' => $response['uuid'],
			]
		);

		return $response;
	}

	/**
	 * Set shorter timeout for synchronous API calls.
	 *
	 * @return int
	 */
	public function set_sync_timeout() {
		return self::SYNC_TIMEOUT;
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
