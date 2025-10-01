<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Queue;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

/**
 * Rocket Insights Queue
 *
 * Manages Action Scheduler jobs for Rocket Insights workflow
 */
class Queue extends AbstractASQueue {

	/**
	 * Queue group for Rocket Insights.
	 *
	 * @var string
	 */
	protected $group = 'rocket-insights';

	/**
	 * Cleanup old tests hook.
	 *
	 * @var string
	 */
	private $reset_hook = 'rocket_insights_credit_reset';


	/**
	 * Cancel reset job.
	 */
	public function cancel_reset_job(): void {
		if ( ! $this->is_scheduled( $this->reset_hook ) ) {
			return;
		}
		$this->cancel( $this->reset_hook );
	}

	/**
	 * Schedule reset task.
	 *
	 * @return void
	 */
	public function schedule_reset_task() {
		// Schedule weekly cleanup.
		$this->schedule_recurring(
			time(),
			MONTH_IN_SECONDS,
			$this->reset_hook,
			[],
			1
		);
	}
}
