<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Cron;

use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Optimization\RegexTrait;

/**
 * The Class is responsible for scheduling, executing, and unscheduling the 'above the fold' cleanup.
 *
 * It uses the RegexTrait for regular expression related methods.
 * It has private properties for ATFTable, ATFQuery, and Context instances.
 */
class CronProcessor {
	use RegexTrait;

	/**
	 * Instance of the ATFQuery class.
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Constructor method.
	 * Initializes a new instance of the Controller class.
	 *
	 * @param ATFQuery $query An instance of the ATFQuery class.
	 */
	public function __construct( ATFQuery $query ) {
		$this->query = $query;
	}

	/**
	 * Schedules the 'above the fold' cleanup to run daily if it's not already scheduled.
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'rocket_atf_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'rocket_atf_cleanup' );
		}
	}

	/**
	 * Executes the 'above the fold' cleanup.
	 * It gets the current date and subtracts the interval (default to 1 month) from it.
	 * Then it deletes the rows with 'failed' status or not accessed since the interval.
	 */
	public function cleanup() {
		// Delete the rows with failed status or not accessed.
		$this->query->delete_old_rows();
	}

	/**
	 * Unscheduled the 'above the fold' cleanup, preventing it from running at the previously scheduled time.
	 */
	public function unscheduled_cleanup() {
		$timestamp = wp_next_scheduled( 'rocket_atf_cleanup' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'rocket_atf_cleanup' );
		}
	}
}
