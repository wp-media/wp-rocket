<?php

/**
 * Class TaskScheduler_JobStore
 * @codeCoverageIgnore
 */
abstract class TaskScheduler_JobStore {
	const STATUS_COMPLETE = 'complete';
	const STATUS_PENDING = 'pending';

	/** @var TaskScheduler_JobStore */
	private static $store = NULL;

	/**
	 * @param TaskScheduler_Job $job
	 * @param DateTime $date Optional date of the first instance
	 *        to store. Otherwise uses the first date of the job's
	 *        schedule.
	 * @return string The job ID
	 */
	abstract public function save_job( TaskScheduler_Job $job, DateTime $date = NULL );

	/**
	 * @param string $job_id
	 * @return TaskScheduler_Job
	 */
	abstract public function fetch_job( $job_id );

	/**
	 * @param string $hook
	 * @param array $params
	 * @return string ID of the next job matching the criteria
	 */
	abstract public function find_job( $hook, $params = array() );

	/**
	 * @param string $job_id
	 * @return void
	 */
	abstract public function cancel_job( $job_id );


	/**
	 * @param int $max_jobs
	 * @return TaskScheduler_JobClaim
	 */
	abstract public function stake_claim( $max_jobs );

	/**
	 * @param TaskScheduler_JobClaim $claim
	 * @return mixed
	 */
	abstract public function release_claim ( TaskScheduler_JobClaim $claim );

	/**
	 * @param string $job_id
	 * @return void
	 */
	abstract public function mark_complete( $job_id );

	public function init() {}

	/**
	 * @return TaskScheduler_JobStore
	 */
	public static function instance() {
		if ( empty(self::$store) ) {
			$class = apply_filters('task_scheduler_job_store_class', 'TaskScheduler_wpPostJobStore');
			self::$store = new $class();
		}
		return self::$store;
	}
}
 