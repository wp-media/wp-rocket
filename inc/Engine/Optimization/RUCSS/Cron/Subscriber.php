<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Cron;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {
	/**
	 * UsedCss instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Database instance
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS  $used_css UsedCSS instance.
	 * @param Database $database Database instance.
	 */
	public function __construct( UsedCSS $used_css, Database $database ) {
		$this->used_css = $used_css;
		$this->database = $database;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_rucss_pending_jobs'          => 'process_pending_jobs',
			'rocket_rucss_on_submit_jobs'        => 'process_on_submit_jobs',
			'rocket_rucss_job_check_status'      => 'check_job_status',
			'rocket_rucss_clean_rows_time_event' => 'cron_clean_rows',
			'cron_schedules'                     => 'add_interval',
			'rocket_deactivation'                => 'on_deactivation',
			'rocket_remove_rucss_failed_jobs'    => 'cron_remove_failed_jobs',
			'init'                               => [
				[ 'schedule_clean_not_commonly_used_rows' ],
				[ 'schedule_pending_jobs' ],
				[ 'initialize_rucss_queue_runner' ],
				[ 'schedule_removing_failed_jobs' ],
				[ 'schedule_on_submit_jobs' ],
			],
		];
	}

	/**
	 * Schedules cron to clean not commonly used RUCSS rows.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function schedule_clean_not_commonly_used_rows() {
		if ( ! $this->used_css->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' ) ) {
			return;
		}

		wp_schedule_event( time(), 'weekly', 'rocket_rucss_clean_rows_time_event' );
	}

	/**
	 * Initialize the queue runner for our RUCSS.
	 *
	 * @return void
	 */
	public function initialize_rucss_queue_runner() {
		if ( ! $this->used_css->is_enabled() ) {
			return;
		}

		RUCSSQueueRunner::instance()->init();
	}

	/**
	 * Process pending jobs with Cron iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		$this->used_css->process_pending_jobs();
	}

	/**
	 * Process on submit jobs with Cron iteration.
	 *
	 * @return void
	 */
	public function process_on_submit_jobs() {
		$this->used_css->process_on_submit_jobs();
	}

	/**
	 * Cron callback for deleting old rows in both table databases.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function cron_clean_rows() {
		if ( ! $this->is_deletion_enabled() ) {
			return;
		}

		$this->database->delete_old_used_css();
	}

	/**
	 * Cron callback for removing failed jobs.
	 *
	 * @return void
	 */
	public function cron_remove_failed_jobs() {
		$this->used_css->clear_failed_urls();
	}

	/**
	 * Handle job status by DB row ID.
	 *
	 * @param int $row_id DB Row ID.
	 *
	 * @return void
	 */
	public function check_job_status( int $row_id ) {
		$this->used_css->check_job_status( $row_id );
	}

	/**
	 * Adds new interval for RUCSS pending jobs cron
	 *
	 * @since 3.11.3
	 *
	 * @param array[] $schedules An array of non-default cron schedule arrays.
	 *
	 * @return array
	 */
	public function add_interval( $schedules ) {
		if ( ! $this->used_css->is_enabled() ) {
			return $schedules;
		}

		/**
		 * Filters the cron interval.
		 *
		 * @since 3.11
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_rucss_pending_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		$schedules['rocket_rucss_pending_jobs'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket Remove Unused CSS pending jobs', 'rocket' ),
		];

		$default_interval = 3 * rocket_get_constant( 'DAY_IN_SECONDS', 86400 );
		/**
		 * Filters the cron interval for clearing failed jobs.
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_remove_rucss_failed_jobs_cron_interval', $default_interval );
		$interval = (bool) $interval ? $interval : $default_interval;

		$schedules['rocket_remove_rucss_failed_jobs'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket clear Remove Unused CSS failed jobs', 'rocket' ),
		];

		/**
		 * Filters the cron interval for processing on submit jobs.
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = (int) apply_filters( 'rocket_remove_rucss_on_submit_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		$schedules['rocket_rucss_on_submit_jobs'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket procees on submit jobs', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Schedule on submit jobs.
	 *
	 * @return void
	 */
	public function schedule_on_submit_jobs() {
		if (
			! $this->used_css->is_enabled()
			&&
			wp_next_scheduled( 'rocket_rucss_on_submit_jobs' )
		) {
			wp_clear_scheduled_hook( 'rocket_rucss_on_submit_jobs' );

			return;
		}

		if ( ! $this->used_css->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_on_submit_jobs' ) ) {
			return;
		}

		wp_schedule_event( time(), 'rocket_rucss_on_submit_jobs', 'rocket_rucss_on_submit_jobs' );
	}

	/**
	 * Schedules cron to get RUCSS pendings jobs.
	 *
	 * @since 3.11.3
	 *
	 * @return void
	 */
	public function schedule_pending_jobs() {
		if (
			! $this->used_css->is_enabled()
			&&
			wp_next_scheduled( 'rocket_rucss_pending_jobs' )
		) {
			wp_clear_scheduled_hook( 'rocket_rucss_pending_jobs' );

			return;
		}

		if ( ! $this->used_css->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_pending_jobs' ) ) {
			return;
		}

		wp_schedule_event( time(), 'rocket_rucss_pending_jobs', 'rocket_rucss_pending_jobs' );
	}

	/**
	 * Schedules cron to remove failed jobs.
	 *
	 * @return void
	 */
	public function schedule_removing_failed_jobs() {
		if (
			! $this->used_css->is_enabled()
			&&
			wp_next_scheduled( 'rocket_remove_rucss_failed_jobs' )
		) {
			wp_clear_scheduled_hook( 'rocket_remove_rucss_failed_jobs' );

			return;
		}

		if ( ! $this->used_css->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_remove_rucss_failed_jobs' ) ) {
			return;
		}

		wp_schedule_event( time(), 'rocket_remove_rucss_failed_jobs', 'rocket_remove_rucss_failed_jobs' );
	}

	/**
	 * Clear schedule of RUCSS CRONs on deactivation.
	 *
	 * @return void
	 */
	public function on_deactivation() {
		wp_clear_scheduled_hook( 'action_scheduler_run_queue_rucss', [ 'WP Cron' ] );
	}

	/**
	 * Checks if the RUCSS deletion is enabled.
	 *
	 * @return bool
	 */
	protected function is_deletion_enabled(): bool {
		/**
		 * Filters the enable RUCSS deletion value
		 *
		 * @param bool $delete_rucss True to enable deletion, false otherwise.
		 */
		return (bool) apply_filters( 'rocket_rucss_deletion_enabled', true );
	}
}
