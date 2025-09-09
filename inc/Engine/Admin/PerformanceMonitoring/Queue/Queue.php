<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

/**
 * Performance Monitoring Queue
 *
 * Manages Action Scheduler jobs for performance testing workflow
 */
class Queue extends AbstractASQueue {

	/**
	 * Queue group for Performance Monitoring.
	 *
	 * @var string
	 */
	protected $group = 'performance-monitoring';

	/**
	 * Cleanup old tests hook.
	 *
	 * @var string
	 */
	private $reset_hook = 'rocket_pma_credit_reset';


	/**
	 * Cancel reset job.
	 */
	public function cancel_reset_job(): void {
		$this->cancel( $this->reset_hook );
	}

	/**
	 * Schedule reset task.
	 *
	 * @return void
	 */
	public function schedule_reset_task() {
		if ( $this->is_scheduled( $this->reset_hook ) ) {
			return;
		}

		// Schedule weekly cleanup.
		$this->schedule_recurring(
			time(),
			MONTH_IN_SECONDS,
			$this->reset_hook
		);
	}
}
