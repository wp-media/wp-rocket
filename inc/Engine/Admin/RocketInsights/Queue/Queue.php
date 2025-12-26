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
	private $credit_reset_hook = 'rocket_insights_credit_reset';

	/**
	 * Retest hook.
	 *
	 * @var string
	 */
	private $retest_hook = 'rocket_insights_retest';

	/**
	 * Auto-add homepage hook.
	 *
	 * @var string
	 */
	private $auto_add_homepage_hook = 'rocket_insights_auto_add_homepage';

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
		// Schedule weekly cleanup.
		$this->schedule_recurring(
			time() + $interval,
			$interval ?? MONTH_IN_SECONDS,
			$this->retest_hook,
			[],
			1
		);
	}

	/**
	 * Schedule auto-add homepage task.
	 *
	 * Schedules a daily recurring task to automatically add homepage
	 * when no URLs are tracked and license is expiring soon.
	 *
	 * @since 3.20.3
	 *
	 * @return void
	 */
	public function schedule_auto_add_homepage_task(): void {
		if ( $this->is_scheduled( $this->auto_add_homepage_hook ) ) {
			return;
		}

		$this->schedule_recurring(
			time(),
			DAY_IN_SECONDS,
			$this->auto_add_homepage_hook,
			[],
			1
		);
	}

	/**
	 * Cancel auto-add homepage task.
	 *
	 * @since 3.20.3
	 *
	 * @return void
	 */
	public function cancel_auto_add_homepage_task(): void {
		if ( ! $this->is_scheduled( $this->auto_add_homepage_hook ) ) {
			return;
		}
		$this->cancel( $this->auto_add_homepage_hook );
	}

	/**
	 * Cancel all scheduled tasks.
	 *
	 * @return void
	 */
	public function cancel_all_tasks() {
		$this->cancel_credit_reset_job();
		$this->cancel_retest_job();
		$this->cancel_auto_add_homepage_task();
	}

	/**
	 * Deprecate old AS actions.
	 *
	 * @return void
	 */
	public function deprecate_old_actions() {
		$this->deprecate_action( 'rocket_pma_credit_reset' );
		$this->deprecate_action( 'rocket_insights_retest' );
	}
}
