<?php

namespace WP_Rocket\Engine\Common\JobManager\Strategy\Strategies;

use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;

/**
 * Class managing the default error for retry process
 */
class DefaultProcess implements StrategyInterface {

	/**
	 * Job Manager.
	 *
	 * @var Manager
	 */
	private $manager;

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
	 * @param Manager  $manager Job Manager.
	 * @param WPRClock $clock Clock object.
	 */
	public function __construct( Manager $manager, WPRClock $clock ) {
		$this->manager = $manager;
		$this->clock   = $clock;

		/**
		 * Filter the array containing the time needed to wait for each retry.
		 *
		 * @param array $time_table_entry contains the number of retry and how long we have to wait.
		 */
		$time_table_retry = rocket_apply_filter_and_deprecated(
			'rocket_saas_retry_table',
			[ $this->time_table_retry ],
			'3.16',
			'rocket_rucss_retry_table'
		);

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

			$this->manager->make_status_failed( $row_details->url, $row_details->is_mobile, $job_details['code'], $job_details['message'] );

			return;
		}

		$this->manager->increment_retries( $row_details->url, $row_details->is_mobile, $job_details['code'], $job_details['message'] );

		$saas_retry_duration = $this->time_table_retry[ $row_details->retries ] ?? $this->default_waiting_retry; // Default to 30 minutes.

		/**
		 * Filter SaaS retry duration.
		 *
		 * @param int $duration Duration between each retry in seconds.
		 */
		$saas_retry_duration = (int) rocket_apply_filter_and_deprecated(
			'rocket_saas_retry_duration',
			[ $saas_retry_duration ],
			'3.16',
			'rocket_rucss_retry_duration'
		);
		if ( $saas_retry_duration < 0 ) {
			$saas_retry_duration = $this->default_waiting_retry;
		}

		// update the `next_retry_time` column.
		$next_retry_time = $this->clock->current_time( 'timestamp', true ) + $saas_retry_duration;

		$this->manager->update_message( $row_details->url, $row_details->is_mobile, (int) $job_details['code'], $job_details['message'], $row_details->error_message );
		$this->manager->update_next_retry_time( $row_details->url, $row_details->is_mobile, $next_retry_time );
	}
}
