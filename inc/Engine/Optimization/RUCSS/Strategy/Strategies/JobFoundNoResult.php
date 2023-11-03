<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Common\Clock\WPRClock;

/**
 * Class managing the retry process of RUCSS whenever a Job is found without any results yet.
 */
class JobFoundNoResult implements StrategyInterface {
	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	protected $used_css_query;

	/**
	 * Clock Object.
	 *
	 * @var WPRClock
	 */
	protected $clock;

	/**
	 * Represents a timetable which shows how long to wait after for a new retry depending on how many retries have been made already.
	 *
	 * @var int[]
	 */
	private $time_table_retry = [
		1 => 60,   // 1-2 tries = 1 minute
		2 => 60,   // 1-2 tries = 1 minute
		3 => 300,  // 3 tries = 5 minutes
		4 => 600,  // 4-5 tries = 10 minutes
		5 => 600,  // 4-5 tries = 10 minutes
		6 => 1200, // 6-7 tries = 20 minutes
		7 => 1200, // 6-7 tries = 20 minutes
	];

	/**
	 * Strategy Constructor.
	 *
	 * @param UsedCSS_Query $used_css_query DB Table.
	 */
	public function __construct( UsedCSS_Query $used_css_query ) {
		$this->used_css_query = $used_css_query;
		$this->clock = new WPRClock();
	}

	/**
	 * Execute the strategy process.
	 *
	 * @param object $row_details Row details of the job.
	 * @param array  $job_details Job details from the API.
	 *
	 * @return void
	 */
	public function execute( object $row_details, array $job_details ): void {

		$rucss_retry_duration = $this->time_table_retry[ $row_details->retries ] ?? 1800; // Default to 30 minutes.

		/**
		 * Filter used css retry duration.
		 *
		 * @param int $duration Duration between each retry in seconds.
		 */
		$rucss_retry_duration = apply_filters( 'rocket_rucss_retry_duration', $rucss_retry_duration );

		// update the `not_proceed_before` column.
		$not_proceed_before = $this->clock->current_time( 'timestamp' ) + $rucss_retry_duration;

		$this->used_css_query->update_not_processed_before( $row_details->job_id, $not_proceed_before );
	}
}
