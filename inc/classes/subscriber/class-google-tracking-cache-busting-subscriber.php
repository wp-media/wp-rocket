<?php
namespace WP_Rocket\Subscriber;

use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Logger\Logger;

/**
 * Event subscriber for Google tracking cache busting
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Tracking_Cache_Busting_Subscriber implements Subscriber_Interface {
	/**
	 * Instance of the Busting Factory class
	 *
	 * @var Busting_Factory
	 */
	private $busting_factory;

	/**
	 * Instance of the Option_Data class
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Busting_Factory $busting_factory Instance of the Busting Factory class.
	 * @param Options         $options Instance of the Option_Data class.
	 */
	public function __construct( Busting_Factory $busting_factory, Options $options ) {
		$this->busting_factory = $busting_factory;
		$this->options         = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'cron_schedules'                      => 'add_schedule',
			'init'                                => 'schedule_tracking_cache_update',
			'rocket_google_tracking_cache_update' => 'update_tracking_cache',
			'after_rocket_clean_cache_busting'    => 'delete_tracking_cache',
			'rocket_buffer'                       => 'cache_busting_google_tracking',
		];

		return $events;
	}

	/**
	 * Checks if the cache busting should happen
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Processes the cache busting on the HTML content
	 *
	 * Google Analytics replacement is performed first, and if no replacement occured, Google Tag Manager replacement is performed.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function cache_busting_google_tracking( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$processor = $this->busting_factory->type( 'ga' );
		$html      = $processor->replace_url( $html );

		$processor = $this->busting_factory->type( 'gtm' );
		$html      = $processor->replace_url( $html );

		return $html;
	}

	/**
	 * Schedules the auto-update of Google Analytics cache busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function schedule_tracking_cache_update() {
		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return;
		}

		if ( ! wp_next_scheduled( 'rocket_google_tracking_cache_update' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_google_tracking_cache_update' );
		}
	}

	/**
	 * Updates Google Analytics cache busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function update_tracking_cache() {
		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'ga' );

		return $processor->refresh_save( $processor->get_url() );
	}

	/**
	 * Adds weekly interval to cron schedules
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Array $schedules An array of intervals used by cron jobs.
	 * @return Array
	 */
	public function add_schedule( $schedules ) {
		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return $schedules;
		}

		$schedules['weekly'] = [
			'interval' => 604800,
			'display'  => __( 'weekly', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Deletes the GA busting file.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $ext File extension type.
	 * @return bool
	 */
	public function delete_tracking_cache( $ext ) {
		if ( 'js' !== $ext ) {
			return false;
		}

		if ( ! $this->options->get( 'google_analytics_cache', 0 ) ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'ga' );

		return $processor->delete();
	}
}
