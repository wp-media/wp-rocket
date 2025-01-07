<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Cron;

/**
 * The Controller Class is responsible for scheduling, executing, and unschedulin performance hints optimization cleanup.
 */
class Controller {
	/**
	 * Array of factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Constructor method.
	 * Initializes a new instance of the Controller class.
	 *
	 * @param array $factories Array of factories.
	 */
	public function __construct( array $factories ) {
		$this->factories = $factories;
	}

	/**
	 * Schedules the performance cleanup to run daily if it's not already scheduled.
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'rocket_performance_hints_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'rocket_performance_hints_cleanup' );
		}
	}

	/**
	 * Executes the performance hints cleanup.
	 * It gets the current date and subtracts the interval (default to 1 month) from it.
	 * Then it deletes the rows with 'failed' status or not accessed since the interval.
	 */
	public function cleanup() {
		// Delete the rows with failed status or not accessed.
		foreach ( $this->factories as $factory ) {
			$factory->queries()->delete_old_rows();
		}
	}

	/**
	 * Unscheduled the performance hints cleanup, preventing it from running at the previously scheduled time.
	 */
	public function unscheduled_cleanup() {
		$timestamp = wp_next_scheduled( 'rocket_performance_hints_cleanup' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'rocket_performance_hints_cleanup' );
		}
	}
}
