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
		1 => 180,  // 3 minutes
		2 => 300,  // 5 minutes
		3 => 600,  // 10 minutes
		4 => 900,  // 15 minutes.
		5 => 1200, // 20 minutes.
		6 => 1800, // 30 minutes.
	];

	/**
	 * Strategy Constructor.
	 *
	 * @param UsedCSS_Query $used_css_query DB Table.
	 * @param WPRClock      $clock Clock object.
	 */
	public function __construct( UsedCSS_Query $used_css_query, WPRClock $clock ) {
		$this->used_css_query = $used_css_query;
		$this->clock          = $clock;
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

		$this->used_css_query->increment_retries( $row_details->id, (int) $row_details->retries );

		$rucss_retry_duration = $this->time_table_retry[ $row_details->retries ] ?? 1800; // Default to 30 minutes.

		/**
		 * Filter used css retry duration.
		 *
		 * @param int $duration Duration between each retry in seconds.
		 */
		$rucss_retry_duration = apply_filters( 'rocket_rucss_retry_duration', $rucss_retry_duration );

		// update the `not_proceed_before` column.
		$not_proceed_before = $this->clock->current_time( 'timestamp' ) + $rucss_retry_duration;

		$this->used_css_query->update_message( $row_details->id, $job_details['code'], $job_details['message'], $row_details->error_message );
		$this->used_css_query->update_not_processed_before( $row_details->job_id, $not_proceed_before );
	}
}
