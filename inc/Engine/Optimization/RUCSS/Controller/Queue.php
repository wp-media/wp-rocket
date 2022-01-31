<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Queue
 *
 * A job queue using WordPress actions.
 *
 * @version 3.11.0
 */
class Queue extends AbstractASQueue {

	protected $group = 'rocket-rucss';

	private $pending_job_cron = 'rocket_rucss_pending_jobs_cron';

	public function is_pending_jobs_cron_scheduled() {
		return $this->is_scheduled( $this->pending_job_cron );
	}

	public function cancel_pending_jobs_cron() {
		$this->cancel_all( $this->pending_job_cron );
	}

	public function schedule_pending_jobs_cron( $interval ) {
		return $this->schedule_recurring( time(), $interval, $this->pending_job_cron );
	}

	public function add_job_status_check_async( $usedcss_row_id ) {
		return $this->add_async(
			'rocket_rucss_job_check_status',
			[
				$usedcss_row_id,
			]
		);
	}

}
