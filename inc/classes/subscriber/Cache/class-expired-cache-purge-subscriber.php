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
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'init'                => 'schedule_event',
			'rocket_deactivation' => 'unschedule_event',
			static::EVENT_NAME    => 'purge_expired_files',
		];
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
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', static::EVENT_NAME );
		}
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
