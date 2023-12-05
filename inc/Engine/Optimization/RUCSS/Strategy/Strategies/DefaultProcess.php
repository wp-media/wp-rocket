<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;

/**
 * Class managing the default error for retry process of RUCSS
 */
class DefaultProcess implements StrategyInterface {
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
		0 => 60,   // 1 minutes
		1 => 120,  // 2 minutes
		2 => 300,  // 5 minutes
		3 => 600,  // 10 minutes.
		4 => 1200, // 20 minutes.
		5 => 1500, // 25 minutes.
	];

	/**
	 * Default value to wait before a retry.
	 *
	 * @var int
	 */
	private $default_waiting_retry = 1500;

	/**
	 * Strategy Constructor.
	 *
	 * @param UsedCSS_Query $used_css_query DB Table.
	 * @param WPRClock      $clock Clock object.
	 */
	public function __construct( UsedCSS_Query $used_css_query, WPRClock $clock ) {
		$this->used_css_query = $used_css_query;
		$this->clock          = $clock;

		/**
		 * Filter the array containing the time needed to wait for each retry.
		 *
		 * @param array $time_table_entry contains the number of retry and how long we have to wait.
		 */
		$time_table_retry = apply_filters( 'rocket_rucss_retry_table', $this->time_table_retry );

		if ( is_array( $time_table_retry ) ) {
			$this->time_table_retry = $time_table_retry;
		}
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
		if ( $row_details->retries >= count( $this->time_table_retry ) ) {
			/**
			 * Unlock preload URL.
			 *
			 * @param string $url URL to unlock
			 */
			do_action( 'rocket_preload_unlock_url', $row_details->url );

			$this->used_css_query->make_status_failed( $row_details->id, strval( $job_details['code'] ), $job_details['message'] );

			return;
		}

		$this->used_css_query->increment_retries( $row_details->id, (int) $row_details->retries, $job_details['message'] );

		$rucss_retry_duration = $this->time_table_retry[ $row_details->retries ] ?? $this->default_waiting_retry; // Default to 30 minutes.

		/**
		 * Filter used css retry duration.
		 *
		 * @param int $duration Duration between each retry in seconds.
		 */
		$rucss_retry_duration = (int) apply_filters( 'rocket_rucss_retry_duration', $rucss_retry_duration );
		if ( $rucss_retry_duration < 0 ) {
			$rucss_retry_duration = $this->default_waiting_retry;
		}

		// update the `next_retry_time` column.
		$next_retry_time = $this->clock->current_time( 'timestamp', true ) + $rucss_retry_duration;

		$this->used_css_query->update_message( $row_details->id, $job_details['code'], $job_details['message'], $row_details->error_message );
		$this->used_css_query->update_next_retry_time( (int) $row_details->id, $next_retry_time );
	}
}
