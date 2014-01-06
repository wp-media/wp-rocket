<?php

/**
 * Class TaskScheduler_JobClaim
 */
class TaskScheduler_JobClaim {
	private $id = '';
	private $job_ids = array();

	public function __construct( $id, array $job_ids ) {
		$this->id = $id;
		$this->job_ids = $job_ids;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_jobs() {
		return $this->job_ids;
	}
}
 