<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\Queue;

use WP_Rocket\Logger\Logger;
use ActionScheduler_Store;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_AsyncRequest_QueueRunner;

class RUCSSQueueRunner extends AbstractQueueRunner {

	/**
	 * Cron hook name.
	 */
	const WP_CRON_HOOK = 'action_scheduler_run_queue_rucss';

	/**
	 * Cron schedule interval.
	 */
	const WP_CRON_SCHEDULE = 'every_minute';

	/**
	 * Current runner instance.
	 *
	 * @var RUCSSQueueRunner Instance.
	 */
	private static $runner = null;

	/**
	 * Get singleton instance.
	 *
	 * @return RUCSSQueueRunner Instance.
	 */
	public static function instance() {
		if ( null === self::$runner ) {
			self::$runner = new RUCSSQueueRunner();
		}
		return self::$runner;
	}

	/**
	 * Add the cron schedule.
	 *
	 * @param array $schedules Array of current schedules.
	 *
	 * @return array
	 */
	public function add_wp_cron_schedule( $schedules ) {
		if ( isset( $schedules['every_minute'] ) ) {
			return $schedules;
		}

		$schedules['every_minute'] = [
			'interval' => 60, // in seconds.
			'display'  => __( 'Every minute', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Get the cron hook name.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	protected function get_wp_cron_hook(): string {
		return self::WP_CRON_HOOK;
	}

	/**
	 * Get the cron schedule interval.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	protected function get_wp_cron_schedule(): string {
		return self::WP_CRON_SCHEDULE;
	}

	/**
	 * Get the queue group name.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	protected function get_group(): string {
		return 'rocket-rucss';
	}
}
