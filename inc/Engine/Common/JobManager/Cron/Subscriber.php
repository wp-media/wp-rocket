<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Cron;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Subscriber implements Subscriber_Interface {
	/**
	 * JobProcessor instance
	 *
	 * @var JobProcessor
	 */
	private $job_processor;

	/**
	 * Database instance
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * ATF Table instance
	 *
	 * @var ATFTable
	 */
	private $atf_table;

    /**
     * RUCSS Context.
     *
     * @var ContextInterface
     */
    private $rucss_context;

    /**
     * LCP Context.
     *
     * @var ContextInterface
     */
    private $atf_context;

	/**
	 * Instantiate the class
	 *
	 * @param JobProcessor  $job_processor JobProcessor instance.
	 * @param Database $database Database instance.
	 * @param ContextInterface $rucss_context RUCSS Context.
	 * @param ContextInterface $atf_context Above The Fold Context.
	 */
	public function __construct(
        JobProcessor $job_processor,
        Database $database,
        ATFTable $atf_table,
        ContextInterface $rucss_context,
        ContextInterface $atf_context
    ) {
		$this->job_processor = $job_processor;
		$this->database = $database;
        $this->atf_table = $atf_table;
        $this->rucss_context = $rucss_context;
        $this->atf_context = $atf_context;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_rucss_atf_pending_jobs'          => 'process_pending_jobs',
			'rocket_rucss_atf_on_submit_jobs'        => 'process_on_submit_jobs',
			'rocket_rucss_job_check_status'      => 'check_job_status',
			'rocket_rucss_atf_clean_rows_time_event' => 'cron_clean_rows',
			'cron_schedules'                     => 'add_interval',
			'rocket_deactivation'                => 'on_deactivation',
			'rocket_remove_rucss_atf_failed_jobs'    => 'cron_remove_failed_jobs',
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
	 * Schedules cron to clean not commonly used RUCSS/ATF rows.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function schedule_clean_not_commonly_used_rows() {
		if ( ! $this->rucss_context->is_allowed() && ! $this->atf_context->is_allowed() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_atf_clean_rows_time_event' ) ) {
			return;
		}

		wp_schedule_event( time(), 'weekly', 'rocket_rucss_atf_clean_rows_time_event' );
	}

	/**
	 * Initialize the queue runner for our RUCSS.
	 *
	 * @return void
	 */
	public function initialize_rucss_queue_runner() {
		if ( ! $this->rucss_context->is_allowed() && ! $this->atf_context->is_allowed() ) {
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
		$this->job_processor->process_pending_jobs();
	}

	/**
	 * Process on submit jobs with Cron iteration.
	 *
	 * @return void
	 */
	public function process_on_submit_jobs() {
		$this->job_processor->process_on_submit_jobs();
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

        if ( $this->rucss_context->is_allowed() ) {
            $this->database->delete_old_rows();
        }

        if ( $this->atf_context->is_allowed() ) {
            $this->atf_table->delete_old_rows();
        }
	}

	/**
	 * Cron callback for removing failed jobs.
	 *
	 * @return void
	 */
	public function cron_remove_failed_jobs() {
		$this->job_processor->clear_failed_urls();
	}

	/**
	 * Handle job status by DB url and is_mobile.
	 *
	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 *
	 * @return void
	 */
	public function check_job_status( string $url, bool $is_mobile ) {
		$this->job_processor->check_job_status( $url, $is_mobile );
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
		if ( ! $this->rucss_context->is_allowed() && ! $this->atf_context->is_allowed() ) {
			return $schedules;
		}

		/**
		 * Filters the cron interval.
		 *
		 * @since 3.11
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_rucss_atf_pending_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		$schedules['rocket_rucss_atf_pending_jobs'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket process pending jobs', 'rocket' ),
		];

		$default_interval = 3 * rocket_get_constant( 'DAY_IN_SECONDS', 86400 );
		/**
		 * Filters the cron interval for clearing failed jobs.
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_remove_rucss_atf_failed_jobs_cron_interval', $default_interval );
		$interval = (bool) $interval ? $interval : $default_interval;

		$schedules['rocket_remove_rucss_atf_failed_jobs'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket clear failed jobs', 'rocket' ),
		];

		/**
		 * Filters the cron interval for processing on submit jobs.
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = (int) apply_filters( 'rocket_remove_rucss_atf_on_submit_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		$schedules['rocket_rucss_atf_on_submit_jobs'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket process on submit jobs', 'rocket' ),
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
			! $this->rucss_context->is_allowed()
			&& 
            ! $this->atf_context->is_allowed()
            &&
			wp_next_scheduled( 'rocket_rucss_atf_on_submit_jobs' )
		) {
			wp_clear_scheduled_hook( 'rocket_rucss_atf_on_submit_jobs' );

			return;
		}

		if ( ! $this->rucss_context->is_allowed() && ! $this->atf_context->is_allowed() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_atf_on_submit_jobs' ) ) {
			return;
		}

		wp_schedule_event( time(), 'rocket_rucss_atf_on_submit_jobs', 'rocket_rucss_atf_on_submit_jobs' );
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
			! $this->rucss_context->is_allowed()
			&& 
            ! $this->atf_context->is_allowed()
            &&
			wp_next_scheduled( 'rocket_rucss_atf_pending_jobs' )
		) {
			wp_clear_scheduled_hook( 'rocket_rucss_atf_pending_jobs' );

			return;
		}

		if ( ! $this->rucss_context->is_allowed() && ! $this->atf_context->is_allowed() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_atf_pending_jobs' ) ) {
			return;
		}

		wp_schedule_event( time(), 'rocket_rucss_atf_pending_jobs', 'rocket_rucss_atf_pending_jobs' );
	}

	/**
	 * Schedules cron to remove failed jobs.
	 *
	 * @return void
	 */
	public function schedule_removing_failed_jobs() {
		if (
			! $this->rucss_context->is_allowed()
			&& 
            ! $this->atf_context->is_allowed()
            &&
			wp_next_scheduled( 'rocket_remove_rucss_atf_failed_jobs' )
		) {
			wp_clear_scheduled_hook( 'rocket_remove_rucss_atf_failed_jobs' );

			return;
		}

		if ( ! $this->rucss_context->is_allowed() && ! $this->atf_context->is_allowed() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_remove_rucss_atf_failed_jobs' ) ) {
			return;
		}

		wp_schedule_event( time(), 'rocket_remove_rucss_atf_failed_jobs', 'rocket_remove_rucss_atf_failed_jobs' );
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
