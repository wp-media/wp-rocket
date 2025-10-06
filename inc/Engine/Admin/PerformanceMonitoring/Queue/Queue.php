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
	private $credit_reset_hook = 'rocket_pma_credit_reset';

	/**
	 * Retest hook.
	 *
	 * @var string
	 */
	private $retest_hook = 'rocket_insights_retest';

	/**
	 * Cancel reset job.
	 */
	public function cancel_credit_reset_job(): void {
		if ( ! $this->is_scheduled( $this->credit_reset_hook ) ) {
			return;
		}
		$this->cancel( $this->credit_reset_hook );
	}

	/**
	 * Cancel reset job.
	 */
	public function cancel_retest_job(): void {
		if ( ! $this->is_scheduled( $this->retest_hook ) ) {
			return;
		}
		$this->cancel( $this->retest_hook );
	}

	/**
	 * Schedule reset task.
	 *
	 * @return void
	 */
	public function schedule_credit_reset_task() {
		// Schedule weekly cleanup.
		$this->schedule_recurring(
			time(),
			MONTH_IN_SECONDS,
			$this->credit_reset_hook,
			[],
			1
		);
	}

	/**
	 * Schedule retest task.
	 *
	 * @param int|null $interval Schedule interval.
	 *
	 * @return void
	 */
	public function schedule_retest_task( $interval = null ) {
		$interval = (int) ( $interval ?? MONTH_IN_SECONDS );

		// Schedule weekly cleanup.
		$this->schedule_recurring(
			time() + $interval,
			$interval,
			$this->retest_hook,
			[],
			1
		);
	}

	/**
	 * Cancel all scheduled tasks.
	 *
	 * @return void
	 */
	public function cancel_all_tasks() {
		$this->cancel_credit_reset_job();
		$this->cancel_retest_job();
	}
}
