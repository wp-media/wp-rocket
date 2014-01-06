<?php

/**
 * Class TaskScheduler_JobRunner
 */
class TaskScheduler_JobRunner {
	/** @var TaskScheduler_JobStore */
	private $store = NULL;

	public function __construct( TaskScheduler_JobStore $store = NULL ) {
		$this->store = $store ? $store : TaskScheduler_JobStore::instance();
	}

	public function run() {
		$count = 0;
		do {
			$jobs_run = $this->do_batch();
			$count += $jobs_run;
		} while ( $jobs_run > 0 );
		return $count;
	}

	protected function do_batch( $size = 10 ) {
		$claim = $this->store->stake_claim($size);
		foreach ( $claim->get_jobs() as $job_id ) {
			$this->process_job( $job_id );
		}
		return count($claim->get_jobs());
	}

	protected function process_job( $job_id ) {
		$job = $this->store->fetch_job( $job_id );
		$job->execute();
	}
}
 