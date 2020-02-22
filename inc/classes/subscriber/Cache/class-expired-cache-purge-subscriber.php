<?php
namespace WP_Rocket\Subscriber\Cache;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Cache\Expired_Cache_Purge;

/**
 * Event subscriber to clear cached files after lifespan.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class Expired_Cache_Purge_Subscriber implements Subscriber_Interface {

	/**
	 * Cron name.
	 *
	 * @since  3.4
	 * @author Grégory Viguier
	 *
	 * @var string
	 */
	const EVENT_NAME = 'rocket_purge_time_event';

	/**
	 * WP Rocket Options instance.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Expired Cache Purge instance.
	 *
	 * @since 3.4
	 * @access private
	 * @author Remy Perona
	 *
	 * @var Expired_Cache_Purge
	 */
	private $purge;

	/**
	 * Constructor.
	 *
	 * @param Options_Data        $options Options instance.
	 * @param Expired_Cache_Purge $purge   Purge instance.
	 */
	public function __construct( Options_Data $options, Expired_Cache_Purge $purge ) {
		$this->options = $options;
		$this->purge   = $purge;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'init'                            => 'schedule_event',
			'rocket_deactivation'             => 'unschedule_event',
			static::EVENT_NAME                => 'purge_expired_files',
			'cron_schedules'                  => 'custom_cron_schedule',
			'update_option_' . WP_ROCKET_SLUG => [ 'clean_expired_cache_scheduled_event', 10, 2 ],
		];
	}

	/**
	 * Clean expired cache scheduled event when Lifespan is changed to minutes.
	 *
	 * @since  3.4.3
	 * @author Soponar Cristina
	 *
	 * @param array $old_value An array of previous values for the settings.
	 * @param array $value     An array of submitted values for the settings.
	 */
	public function clean_expired_cache_scheduled_event( $old_value, $value ) {
		if ( empty( $value['purge_cron_unit'] ) ) {
			return;
		}

		$old_value['purge_cron_unit'] = isset( $old_value['purge_cron_unit'] ) ? $old_value['purge_cron_unit'] : '';

		$unit_list = [ 'HOUR_IN_SECONDS', 'DAY_IN_SECONDS' ];
		// Bail out if the cron unit is changed from hours to days.
		// Allow clean scheduled event when is changed from Minutes to Hours or Days, or the other way around.
		$allow_clear_event = false;
		if ( in_array( $old_value['purge_cron_unit'], $unit_list, true ) && 'MINUTE_IN_SECONDS' === $value['purge_cron_unit'] ) {
			$allow_clear_event = true;
		}
		if ( in_array( $value['purge_cron_unit'], $unit_list, true ) && 'MINUTE_IN_SECONDS' === $old_value['purge_cron_unit'] ) {
			$allow_clear_event = true;
		}
		// Allow if interval is changed when unit is set to minutes.
		if (
			'MINUTE_IN_SECONDS' === $old_value['purge_cron_unit']
			&&
			'MINUTE_IN_SECONDS' === $value['purge_cron_unit']
			&&
			$old_value['purge_cron_interval'] !== $value['purge_cron_interval']
		) {
			$allow_clear_event = true;
		}

		// Bail out if the cron unit is not changed from minutes to hours / days or other way around.
		if ( ! $allow_clear_event ) {
			return;
		}
		$this->unschedule_event();
	}

	/**
	 * Adds a custom cron schedule based on purge lifespan interval.
	 *
	 * @since  3.4.3
	 * @access public
	 * @author Soponar Cristina
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
	 * @access public
	 * @author Grégory Viguier
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
	 * @access private
	 * @author Soponar Cristina
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
	 * @access public
	 * @author Grégory Viguier
	 */
	public function unschedule_event() {
		wp_clear_scheduled_hook( static::EVENT_NAME );
	}

	/**
	 * Perform the event action.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
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
	 * @access public
	 * @author Grégory Viguier
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
}
