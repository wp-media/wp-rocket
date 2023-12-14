<?php

namespace WP_Rocket\Engine\Media\AboveTheFold\Jobs;

use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\JobManager\Managers\AbstractManager;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

class Manager implements ManagerInterface, LoggerAwareInterface {
	use LoggerAware, AbstractManager;

	/**
	 * AboveTheFold Query instance.
	 *
	 * @var ATFQuery
	 */
	protected $query;

	/**
	 * LCP Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * The type of optimization applied for the current job.
	 *
	 * @var string
	 */
	protected $optimization_type = 'atf';

	/**
	 * Check if manager can process.
	 *
	 * @var boolean
	 */
	protected $can_process = true;

	/**
	 * Instantiate the class.
	 *
	 * @param ATFQuery         $query AboveTheFold Query instance.
	 * @param ContextInterface $context Above The Fold Context.
	 */
	public function __construct( ATFQuery $query, ContextInterface $context ) {
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Get pending jobs from db.
	 *
	 * @param integer $num_rows Number of rows to grab.
	 * @return array
	 */
	public function get_pending_jobs( int $num_rows ): array {
		$this->logger::debug( "ATF: Start getting number of {$num_rows} pending jobs." );

		$pending_jobs = $this->query->get_pending_jobs( $num_rows );

		if ( ! $pending_jobs ) {
			$this->logger::debug( 'ATF: No pending jobs are there.' );

			return [];
		}

		return $pending_jobs;
	}

	/**
	 * Validate SaaS response and fail job.
	 *
	 * @param array  $job_details Details related to the job..
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 *
	 * @return void
	 */
	public function validate_and_fail( array $job_details, $row_details, string $optimization_type ): void {
		if ( ! $this->is_allowed( $optimization_type ) ) {
			return;
		}

		if ( ! isset( $job_details['contents']['above_the_fold_result'] ) ) {
			$this->make_status_failed( $row_details->url, $row_details->is_mobile, '400', 'No ATF/LCP response', $optimization_type );
			$this->can_process = false;
			return;
		}
	}

	/**
	 * Process SaaS response.
	 *
	 * @param array  $job_details Details related to the job.
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	public function process( array $job_details, $row_details, string $optimization_type ): void {
		if ( ! $this->is_allowed( $optimization_type ) || ! $this->can_process ) {
			return;
		}

		// Everything is fine, save LCP & ATF into DB, change status to completed and reset queue_name and job_id.
		$this->logger::debug( 'ATF: Save LCP and ATF for url: ' . $row_details->url );

		$lcp      = $job_details['contents']['above_the_fold_result']['lcp'];
		$viewport = $job_details['contents']['above_the_fold_result']['images_above_fold'];

		$lcp      = $lcp ? wp_json_encode( $lcp, JSON_UNESCAPED_SLASHES ) : 'not found';
		$viewport = $viewport ? wp_json_encode( $viewport, JSON_UNESCAPED_SLASHES ) : 'not found';

		$lcp_atf = [
			'lcp'      => $lcp,
			'viewport' => $viewport,
		];

		$this->query->make_job_completed( $row_details->url, $row_details->is_mobile, $lcp_atf );
	}

	/**
	 * Set the request parameter to be sent to the SaaS
	 *
	 * @return array
	 */
	public function set_request_param(): array {
		return [
			'optimization_list' => [
				'lcp',
				'above_fold',
			],
		];
	}

	/**
	 * Get the optimization type from the DB Row.
	 *
	 * @param object $row DB Row Object.
	 * @return boolean|string
	 */
	public function get_optimization_type_from_row( $row ) {
		if ( ! isset( $row->lcp ) ) {
			return false;
		}

		return $this->optimization_type;
	}
}
