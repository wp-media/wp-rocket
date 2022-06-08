<?php

namespace WP_Rocket\Engine\Preload\Cron;

use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Event_Management\Subscriber_Interface;

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
	 * Creates an instance of the class.
	 *
	 * @param Settings   $settings Preload settings.
	 * @param Cache      $query Db query.
	 * @param PreloadUrl $preload_controller Preload url controller.
	 */
	public function __construct( Settings $settings, Cache $query, PreloadUrl $preload_controller ) {
		$this->settings           = $settings;
		$this->query              = $query;
		$this->preload_controller = $preload_controller;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_clean_rows_time_event' => 'remove_old_rows',
			'rocket_preload_process_pending'       => 'process_pending_urls',
			'cron_schedules'                       => 'add_interval',
			'init'                                 => [
				[ 'schedule_clean_not_commonly_used_rows' ],
				[ 'schedule_pending_jobs' ],
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

		wp_schedule_event( time(), 'weekly', 'rocket_preload_clean_rows_time_event' );
	}

	/**
	 * Preload Url jobs.
	 *
	 * @return void
	 */
	public function process_pending_urls() {
		if ( ! $this->settings->is_enabled() ) {
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

		wp_schedule_event( time(), 'rocket_preload_process_pending', 'rocket_preload_process_pending' );
	}

	/**
	 * Remove old urls.
	 *
	 * @return void
	 */
	public function remove_old_rows() {
		$this->query->remove_all_not_accessed_rows();
	}
}
