<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Cron;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Cron Instance.
	 *
	 * @var CronProcessor
	 */
	private $cron_processor;


	/**
	 * Instantiate the cron processor class
	 *
	 * @param CronProcessor $cron_processor Cron processor instance.
	 */
	public function __construct( CronProcessor $cron_processor ) {
		$this->cron_processor = $cron_processor;
	}

	/**
	 * Returns an array of events that this class subscribes to.
	 *
	 * @return array An associative array where the keys are the event names and the values are the method names to call when the event is triggered.
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_atf_cleanup'  => 'cleanup',
			'init'                => 'schedule_cleanup',
			'rocket_deactivation' => 'unscheduled_cleanup',
		];
	}

	/**
	 * Executes the 'above the fold' cleanup.
	 *
	 * @return void
	 */
	public function cleanup() {
		$this->cron_processor->cleanup();
	}

	/**
	 * Schedules the 'above the fold' cleanup to run at a later time.
	 *
	 * @return void
	 */
	public function schedule_atf_cleanup() {
		$this->cron_processor->schedule_cleanup();
	}

	/**
	 * Unscheduled the 'above the fold' cleanup, preventing it from running at the previously scheduled time.
	 *
	 * @return void
	 */
	public function unscheduled_cleanup() {
		$this->cron_processor->unscheduled_cleanup();
	}
}
