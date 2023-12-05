<?php

namespace WP_Rocket\Engine\Common\JobManager\Interfaces;

interface ManagerInterface {

    /**
     * Get pending jobs from db.
     *
     * @param integer $num_rows Number of rows to grab.
     * @return array
     */
	public function get_pending_jobs( int $num_rows ): array;

    /**
     * Process response from SaaS.
     *
     * @param array Associative array of data required to perform actions.
     * @return void
     */
    public function process( array $data ): void;

    /**
     * Log start process of jobs.
     *
     * @return void
     */
    public function log_start_process(): void;
}