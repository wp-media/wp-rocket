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
	  * Process SaaS response.
	  *
	  * @param array $job_details Details related to the job..
	  * @param object $row_details Details related to the row.
      * @param string $optimization_type The type of optimization applied for the current job.
	  * @return void
	  */
    public function process( array $job_details, $row_details, string $optimization_type ): void;
}