<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

/**
 * Queue
 *
 * A job queue using WordPress actions.
 *
 * @version 3.11.0
 */
class Queue extends AbstractASQueue {

	/**
	 * Queue group.
	 *
	 * @var string
	 */
	protected $group = 'rocket-rucss';

	/**
	 * Pending jobs cron hook.
	 *
	 * @var string
	 */
	private $pending_job_cron = 'rocket_rucss_pending_jobs_cron';

	/**
	 * Check if pending jobs cron is scheduled.
	 *
	 * @return bool
	 */
	public function is_pending_jobs_cron_scheduled() {
		return $this->is_scheduled( $this->pending_job_cron );
	}

	/**
	 * Cancel pending jobs cron.
	 *
	 * @return void
	 */
	public function cancel_pending_jobs_cron() {
		$this->cancel_all( $this->pending_job_cron );
	}

	/**
	 * Schedule pending jobs cron.
	 *
	 * @param int $interval Cron interval in seconds.
	 *
	 * @return string
	 */
	public function schedule_pending_jobs_cron( int $interval ) {
		return $this->schedule_recurring( time(), $interval, $this->pending_job_cron );
	}

	/**
	 * Add Async job with DB row ID.
	 *
	 * @param int $usedcss_row_id DB row ID.
	 *
	 * @return string
	 */
	public function add_job_status_check_async( int $usedcss_row_id ) {
		return $this->add_async(
			'rocket_rucss_job_check_status',
			[
				$usedcss_row_id,
			]
		);
	}

}
