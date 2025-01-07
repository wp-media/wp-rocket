<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Cron;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Cron Controller Instance.
	 *
	 * @var Controller
	 */
	private $controller;


	/**
	 * Instantiate the cron controller class
	 *
	 * @param Controller $controller Cron controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Returns an array of events that this class subscribes to.
	 *
	 * @return array An associative array where the keys are the event names and the values are the method names to call when the event is triggered.
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_performance_hints_cleanup' => 'cleanup',
			'init'                             => 'schedule_cleanup',
			'rocket_deactivation'              => 'unscheduled_cleanup',
		];
	}

	/**
	 * Executes the performance hints cleanup.
	 *
	 * @return void
	 */
	public function cleanup() {
		$this->controller->cleanup();
	}

	/**
	 * Schedules the performance hints cleanup to run at a later time.
	 *
	 * @return void
	 */
	public function schedule_cleanup() {
		$this->controller->schedule_cleanup();
	}

	/**
	 * Unscheduled the performance hints cleanup, preventing it from running at the previously scheduled time.
	 *
	 * @return void
	 */
	public function unscheduled_cleanup() {
		$this->controller->unscheduled_cleanup();
	}
}
