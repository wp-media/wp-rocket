<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Admin\RocketInsights\Queue;

use WP_Rocket\Engine\Common\Queue\AbstractQueueRunner;
use WP_Rocket\Engine\Common\Queue\Cleaner;
use ActionScheduler_Store;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_AsyncRequest_QueueRunner;

/**
 * Rocket Insights Queue Runner
 *
 * Manages Action Scheduler jobs for Rocket Insights workflow
 * Processes jobs every 30 seconds instead of the default 60 seconds
 *
 * @since 3.20
 */
class QueueRunner extends AbstractQueueRunner {

	/**
	 * Cron hook name.
	 */
	const WP_CRON_HOOK = 'action_scheduler_run_queue_rocket_insights';

	/**
	 * Cron schedule interval.
	 */
	const WP_CRON_SCHEDULE = 'every_thirty_seconds';

	/**
	 * Current runner instance.
	 *
	 * @var QueueRunner Instance.
	 */
	private static $runner = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.20
	 *
	 * @return QueueRunner Instance.
	 */
	public static function instance() {
		if ( null === self::$runner ) {
			error_log( print_r( 'Rocket Insights Queue Runner: Creating new instance', true ) );
			self::$runner = new QueueRunner();
		}
		return self::$runner;
	}

	/**
	 * Add the cron schedule for Rocket Insights (every 30 seconds).
	 *
	 * @since 3.20
	 *
	 * @param array $schedules Array of current schedules.
	 *
	 * @return array
	 */
	public function add_wp_cron_schedule( $schedules ) {
		if ( isset( $schedules['every_thirty_seconds'] ) ) {
			return $schedules;
		}

		$schedules['every_thirty_seconds'] = [
			'interval' => 30, // in seconds.
			'display'  => __( 'Every thirty seconds', 'rocket' ),
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
		return 'rocket-insights';
	}

	/**
	 * Process a batch of jobs.
	 *
	 * @param int    $size    The maximum number of actions to process.
	 * @param string $context Optional identifer for the context in which this action is being processed, e.g. 'WP CLI'.
	 *
	 * @return int The number of actions processed.
	 */
	protected function do_batch( $size = 100, $context = '' ) {
		error_log( print_r( 'Rocket Insights Queue Runner: Starting batch processing for group: ' . $this->get_group(), true ) );
		$processed = parent::do_batch( $size, $context );
		error_log( print_r( 'Rocket Insights Queue Runner: Completed batch processing. Processed ' . $processed . ' jobs.', true ) );
		return $processed;
	}
}
