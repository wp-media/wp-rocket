<?php

namespace WP_Rocket\Engine\Common\JobManager\Managers;

interface ManagerInterface {

	/**
	 * Get pending jobs from db.
	 *
	 * @param integer $num_rows Number of rows to grab.
	 * @return array
	 */
	public function get_pending_jobs( int $num_rows ): array;

	/**
	 * Validate SaaS response and fail job.
	 *
	 * @param array  $job_details Details related to the job..
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	public function validate_and_fail( array $job_details, $row_details, string $optimization_type ): void;

	/**
	 * Process SaaS response.
	 *
	 * @param array  $job_details Details related to the job..
	 * @param object $row_details Details related to the row.
	 * @param string $optimization_type The type of optimization applied for the current job.
	 * @return void
	 */
	public function process( array $job_details, $row_details, string $optimization_type ): void;

	/**
	 * Set the request parameter to be sent to the SaaS
	 *
	 * @return array
	 */
	public function set_request_param(): array;

	/**
	 * Get the optimization type from the DB Row.
	 *
	 * @param object $row DB Row Object.
	 * @return boolean|string
	 */
	public function get_optimization_type_from_row( $row );
}
