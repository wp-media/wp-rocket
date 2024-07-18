<?php

/**
 * The Queries interface defines the contract for database query operations.
 *
 */
namespace WP_Rocket\Engine\Common\PerformanceHints\Database;

interface Queries {

	/**
	 * Marks a job as completed.
	 *
	 * This method is used to mark a job as completed in the database. It takes in the URL of the job,
	 * a boolean indicating whether the job is for a mobile device, and an array of data related to the job.
	 *
	 * @param string  $url The URL of the job to be marked as completed.
	 * @param boolean $is_mobile A boolean indicating whether the job is for a mobile device.
	 * @param array   $data An array of data related to the job. This typically includes LCP and Above the fold data.
	 *
	 * @return boolean|int Returns a boolean or integer value. The exact return value depends on the implementation.
	 */
	public function make_job_completed( string $url, bool $is_mobile, array $data );

	/**
	 * Deletes old rows from the database.
	 *
	 * This method is used to delete rows from the database that have not been accessed in the last month.
	 *
	 * @return bool|int Returns a boolean or integer value. The exact return value depends on the implementation.
	 */
	public function delete_old_rows();
}
