<?php

namespace WP_Rocket\Engine\Common\JobManager\Strategy\Strategies;

interface StrategyInterface {
	/**
	 * Execute the retry process of a RUCSS job.
	 *
	 * @param object $row_details DB Row of a job.
	 * @param array  $job_details Job information from the API.
	 *
	 * @return mixed
	 */
	public function execute( object $row_details, array $job_details );
}
