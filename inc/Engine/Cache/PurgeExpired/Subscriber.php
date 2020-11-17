<?php
namespace WP_Rocket\Engine\Cache\PurgeExpired;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Event subscriber to clear cached files after lifespan.
 *
 * @since  3.4
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Cron name.
	 *
	 * @since  3.4
	 *
	 * @var string
	 */
	const EVENT_NAME = 'rocket_purge_time_event';

	/**
	 * WP Rocket Options instance.
	 *
	 * @since  3.4
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Expired Cache Purge instance.
	 *
	 * @since 3.4
	 *
	 * @var PurgeExpiredCache
	 */
	private $purge;

	/**
	 * Constructor.
	 *
	 * @param Options_Data      $options Options instance.
	 * @param PurgeExpiredCache $purge   Purge instance.
	 */
	public function __construct( Options_Data $options, PurgeExpiredCache $purge ) {
		$this->options = $options;
		$this->purge   = $purge;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'init'                => 'schedule_event',
			'rocket_deactivation' => 'unschedule_event',
			static::EVENT_NAME    => 'purge_expired_files',
			'cron_schedules'      => 'custom_cron_schedule',
			'wp_rocket_upgrade'   => [ 'update_lifespan_option_on_update', 13, 2 ],
		];
	}

	/**
	 * Adds a custom cron schedule based on purge lifespan interval.
	 *
	 * @since  3.4.3
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 */
	public function custom_cron_schedule( $schedules ) {
		$schedules['rocket_expired_cache_cron_interval'] = [
			'interval' => $this->get_interval(),
			'display'  => __( 'WP Rocket Expired Cache Interval', 'rocket' ),
		];

		return $schedules;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** HOOK CALLBACKS ========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Scheduling the cron event.
	 * If the task is not programmed, it is automatically added.
	 *
	 * @since  3.4
	 */
	public function schedule_event() {
		if ( $this->get_cache_lifespan() && ! wp_next_scheduled( static::EVENT_NAME ) ) {
			$interval = $this->get_interval();
			wp_schedule_event( time() + $interval, 'rocket_expired_cache_cron_interval', static::EVENT_NAME );
		}
	}

	/**
	 * Gets the interval when the scheduled clean cache purge needs to run.
	 * If Minutes option is selected, then the interval will be set to minutes.
	 * If Hours / Days options are selected, then it will be set to 1 hour.
	 *
	 * @since  3.4.3
	 *
	 * @return int $interval Interval time in seconds.
	 */
	private function get_interval() {
		$unit     = $this->options->get( 'purge_cron_unit' );
		$lifespan = $this->options->get( 'purge_cron_interval', 10 );
		$interval = HOUR_IN_SECONDS;

		if ( ! $unit || ! defined( $unit ) ) {
			$unit = 'HOUR_IN_SECONDS';
		}
		if ( 'MINUTE_IN_SECONDS' === $unit ) {
			$interval = $lifespan * MINUTE_IN_SECONDS;
		}
		return $interval;
	}

	/**
	 * Unschedule the event.
	 *
	 * @since  3.4
	 */
	public function unschedule_event() {
		wp_clear_scheduled_hook( static::EVENT_NAME );
	}

	/**
	 * Perform the event action.
	 *
	 * @since  3.4
	 */
	public function purge_expired_files() {
		$this->purge->purge_expired_files( $this->get_cache_lifespan() );
	}

	/**
	 * Get the cache lifespan in seconds.
	 * If no value is filled in the settings, return 0. It means the purge is disabled.
	 * If the value from the settings is filled but invalid, fallback to the initial value (10 hours).
	 *
	 * @since  3.4
	 *
	 * @return int The cache lifespan in seconds.
	 */
	public function get_cache_lifespan() {
		$lifespan = $this->options->get( 'purge_cron_interval' );

		if ( ! $lifespan ) {
			return 0;
		}

		$unit = $this->options->get( 'purge_cron_unit' );

		if ( $lifespan < 0 || ! $unit || ! defined( $unit ) ) {
			return 10 * HOUR_IN_SECONDS;
		}

		return $lifespan * constant( $unit );
	}

	/**
	 * Update lifespan option to remove minutes with WP Rocket Update.
	 *
	 * @since 3.8
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function update_lifespan_option_on_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.8', '>' ) ) {
			return;
		}

		$this->purge->update_lifespan_value(
			$this->options->get( 'purge_cron_interval' ),
			$this->options->get( 'purge_cron_unit' )
		);
	}
}
