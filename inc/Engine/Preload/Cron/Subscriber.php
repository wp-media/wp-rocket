<?php

namespace WP_Rocket\Engine\Preload\Cron;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Tables\Cache as CacheTable;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Preload settings.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Db query.
	 *
	 * @var Cache
	 */
	protected $query;

	/**
	 * Preload url controller.
	 *
	 * @var PreloadUrl
	 */
	protected $preload_controller;

	/**
	 * Preload queue runner.
	 *
	 * @var PreloadQueueRunner
	 */
	protected $queue_runner;

	/**
	 * Cache table.
	 *
	 * @var CacheTable
	 */
	protected $table;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Settings           $settings Preload settings.
	 * @param Cache              $query Db query.
	 * @param PreloadUrl         $preload_controller Preload url controller.
	 * @param PreloadQueueRunner $preload_queue_runner preload queue runner.
	 * @param CacheTable         $table Cache table.
	 */
	public function __construct( Settings $settings, Cache $query, PreloadUrl $preload_controller, PreloadQueueRunner $preload_queue_runner, CacheTable $table ) {
		$this->settings           = $settings;
		$this->query              = $query;
		$this->preload_controller = $preload_controller;
		$this->queue_runner       = $preload_queue_runner;
		$this->table              = $table;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_clean_rows_time_event'       => 'remove_old_rows',
			'rocket_preload_process_pending'             => 'process_pending_urls',
			'rocket_preload_revert_old_in_progress_rows' => 'revert_old_in_progress_rows',
			'cron_schedules'                             => [
				[ 'add_interval' ],
				[ 'add_revert_old_in_progress_interval' ],
			],
			'init'                                       => [
				[ 'schedule_clean_not_commonly_used_rows' ],
				[ 'schedule_pending_jobs' ],
				[ 'schedule_revert_old_in_progress_rows' ],
				[ 'maybe_init_preload_queue' ],
			],
		];
	}

	/**
	 * Schedule clean from removing of old urls.
	 *
	 * @return void
	 */
	public function schedule_clean_not_commonly_used_rows() {

		if ( wp_next_scheduled( 'rocket_preload_clean_rows_time_event' ) ) {
			return;
		}

		wp_schedule_event( time() + 10 * MINUTE_IN_SECONDS, 'weekly', 'rocket_preload_clean_rows_time_event' );
	}

	/**
	 * Preload Url jobs.
	 *
	 * @return void
	 */
	public function process_pending_urls() {
		if ( ! $this->settings->is_enabled() || ! $this->table->exists() ) {
			return;
		}

		$this->preload_controller->process_pending_jobs();
	}

	/**
	 * Add the interval for the cron.
	 *
	 * @param array $schedules Cron schedules.
	 * @return mixed
	 */
	public function add_interval( $schedules ) {
		if ( ! $this->settings->is_enabled() ) {
			return $schedules;
		}

		/**
		 * Filters the cron interval.
		 *
		 * @since 3.11
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_preload_pending_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		$schedules['rocket_preload_process_pending'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket Preload pending jobs', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Add the interval for the cron.
	 *
	 * @param array $schedules Cron schedules.
	 * @return mixed
	 */
	public function add_revert_old_in_progress_interval( $schedules ) {
		if ( ! $this->settings->is_enabled() ) {
			return $schedules;
		}

		/**
		 * Filters the cron interval.
		 *
		 * @since 3.11
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_preload_revert_old_in_progress_rows_cron_interval', 12 * rocket_get_constant( 'HOUR_IN_SECONDS', 60 * 60 ) );

		$schedules['rocket_revert_old_in_progress_rows'] = [
			'interval' => $interval,
			'display'  => esc_html__( 'WP Rocket Preload revert stuck in-progress jobs', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Schedule pending preload urls.
	 *
	 * @return void
	 */
	public function schedule_pending_jobs() {

		if (
			! $this->settings->is_enabled()
			&&
			wp_next_scheduled( 'rocket_preload_process_pending' )
		) {
			wp_clear_scheduled_hook( 'rocket_preload_process_pending' );

			return;
		}

		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_preload_process_pending' ) ) {
			return;
		}

		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'rocket_preload_process_pending', 'rocket_preload_process_pending' );
	}

	/**
	 * Schedule revert stuck in progress row cron.
	 *
	 * @return void
	 */
	public function schedule_revert_old_in_progress_rows() {
		if (
			! $this->settings->is_enabled()
			&&
			wp_next_scheduled( 'rocket_preload_revert_old_in_progress_rows' )
		) {
			wp_clear_scheduled_hook( 'rocket_preload_revert_old_in_progress_rows' );

			return;
		}

		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_preload_revert_old_in_progress_rows' ) ) {
			return;
		}

		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'rocket_revert_old_in_progress_rows', 'rocket_preload_revert_old_in_progress_rows' );
	}

	/**
	 * Remove old urls.
	 *
	 * @return void
	 */
	public function remove_old_rows() {
		if ( ! $this->table->exists() ) {
			return;
		}
		$this->query->remove_all_not_accessed_rows();
	}

	/**
	 * Remove old in-progress urls.
	 *
	 * @return void
	 */
	public function revert_old_in_progress_rows() {
		if ( ! $this->table->exists() ) {
			return;
		}
		$this->query->revert_old_in_progress();
	}

	/**
	 * Set the preload queue runner.
	 *
	 * @return void
	 */
	public function maybe_init_preload_queue() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$this->queue_runner->init();

	}
}
