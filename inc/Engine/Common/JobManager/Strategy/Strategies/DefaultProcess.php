<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies;

use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\JobManager\Interfaces\ManagerInterface;

/**
 * Class managing the default error for retry process of RUCSS
 */
class DefaultProcess implements StrategyInterface {
	/**
     * RUCSS Job Manager.
     *
     * @var ManagerInterface
     */
    private $rucss_manager;

    /**
     * LCP Job Manager.
     *
     * @var ManagerInterface
     */
    private $atf_manager;

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
		5 => 1800, // 30 minutes.
	];

	/**
	 * Default value to wait before a retry.
	 *
	 * @var int
	 */
	private $default_waiting_retry = 1800;

	/**
	 * Strategy Constructor.
	 *
     * @param ManagerInterface $rucss_manager RUCSS Job Manager.
     * @param ManagerInterface $lcp_manager LCP Job Manager.
	 * @param WPRClock      $clock Clock object.
	 */
	public function __construct( ManagerInterface $rucss_manager, ManagerInterface $atf_manager, WPRClock $clock ) {
		$this->rucss_manager = $rucss_manager;
        $this->atf_manager = $atf_manager;
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
		$context = [
			'rucss' => $this->rucss_manager->is_allowed(),
			'atf' => $this->atf_manager->is_allowed(),
		];

		if ( $row_details->retries >= count( $this->time_table_retry ) ) {
			/**
			 * Unlock preload URL.
			 *
			 * @param string $url URL to unlock
			 */
			do_action( 'rocket_preload_unlock_url', $row_details->url );

			$this->make_status_failed( $context, $row_details->url, $row_details->is_mobile, $job_details['code'], $job_details['message'] );

			return;
		}

		$this->increment_retries( $context, $row_details->url, $row_details->is_mobile, $job_details['code'], $job_details['message'] );

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

		$this->update_message_with_next_retry_time(
			$context,
			$row_details->url,
			$row_details->is_mobile,
			$job_details['code'],
			$job_details['message'],
			$row_details->error_message,
			$next_retry_time
		);
	}

	/**
	 * Change the status to be failed.
	 *
	 * @param array $context Context.
	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string $error_code error code.
	 * @param string $error_message error message.
	 * @return void
	 */
	private function make_status_failed( array $context, string $url, bool $is_mobile, string $error_code, string $error_message ): void {
		if ( $context['rucss'] ) {
			$this->rucss_manager->make_status_failed( $url, $is_mobile, strval( $error_code ), $error_message );
		}

		if ( $context['atf'] ) {
			$this->atf_manager->make_status_failed( $url, $is_mobile, strval( $error_code ), $error_message );
		}
	}

	/**
	 * Increment retries number and change status back to pending.
	 *
	 * @param array $context Context.
	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string $error_code error code.
	 * @param string $error_message error message.
	 * @return void
	 */
	private function increment_retries( array $context, string $url, bool $is_mobile, string $error_code, string $error_message ): void {
		if ( $context['rucss'] ) {
			$this->rucss_manager->increment_retries( $url, $is_mobile, strval( $error_code ), $error_message );
		}

		if ( $context['atf'] ) {
			$this->atf_manager->increment_retries( $url, $is_mobile, strval( $error_code ), $error_message );
		}
	}

	/**
	 * Update message and next retry time.
	 *
	 * @param array $context Context
	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param int    $error_code error code.
	 * @param string $error_message error message.
     * @param string $previous_message Previous saved message.
	 * @param string|int $next_retry_time timestamp or mysql format date.
	 * @return void
	 */
	private function update_message_with_next_retry_time(
		array $context,
		string $url,
		bool $is_mobile,
		string $error_code,
		string $error_message,
		string $previous_message,
		$next_retry_time
	) {
		if ( $context['rucss'] ) {
			$this->rucss_manager->update_message( $url, $is_mobile, $error_code, $error_message, $previous_message );
			$this->rucss_manager->update_next_retry_time( $url, $is_mobile, $next_retry_time );
		}

		if ( $context['atf'] ) {
			$this->atf_manager->update_message( $url, $is_mobile, $error_code, $error_message, $previous_message );
			$this->atf_manager->update_next_retry_time( $url, $is_mobile, $next_retry_time );
		}
	}
}
