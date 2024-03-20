<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Cron;

use WP_Rocket\Event_Management\Subscriber_Interface;


/**
 * The Subscriber class implements the Subscriber_Interface.
 * It provides methods for scheduling, executing, and unscheduling the 'above the fold' cleanup.
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Instance of the Controller class
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor method.
	 * Initializes a new instance of the Subscriber class.
	 *
	 * @param Controller $controller An instance of the Controller class.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Returns an array of events that this class subscribes to.
	 *
	 * @return array An associative array where the keys are the event names and the values are the method names to call when the event is triggered.
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_atf_cleanup'  => 'atf_cleanup',
			'init'                => 'schedule_atf_cleanup',
			'rocket_deactivation' => 'unschedule_atf_cleanup',
		];
	}

	/**
	 * Executes the 'above the fold' cleanup.
	 *
	 * @return void
	 */
	public function atf_cleanup() {
		$this->controller->atf_cleanup();
	}

	/**
	 * Schedules the 'above the fold' cleanup to run at a later time.
	 *
	 * @return void
	 */
	public function schedule_atf_cleanup() {
		$this->controller->schedule_atf_cleanup();
	}

	/**
	 * Unschedules the 'above the fold' cleanup, preventing it from running at the previously scheduled time.
	 *
	 * @return void
	 */
	public function unschedule_atf_cleanup() {
		$this->controller->unschedule_atf_cleanup();
	}
}
