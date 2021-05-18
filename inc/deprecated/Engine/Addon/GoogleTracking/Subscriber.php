<?php
namespace WP_Rocket\Addon\GoogleTracking;

use WP_Rocket\deprecated\DeprecatedClassTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Addon\Busting\BustingFactory;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Event subscriber for Google tracking cache busting
 *
 * @since 3.9 deprecated.
 * @since 3.1
 */
class Subscriber implements Subscriber_Interface {
	use DeprecatedClassTrait;

	/**
	 * Instance of the Busting Factory class
	 *
	 * @var BustingFactory
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
	 * @param BustingFactory $busting_factory Instance of the Busting Factory class.
	 * @param Options        $options Instance of the Option_Data class.
	 */
	public function __construct( BustingFactory $busting_factory, Options $options ) {
		self::deprecated_class( '3.9' );

		$this->busting_factory = $busting_factory;
		$this->options         = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'cron_schedules'                      => 'add_schedule',
			'init'                                => 'schedule_tracking_cache_update',
			'rocket_google_tracking_cache_update' => 'update_tracking_cache',
			'rocket_purge_cache'                  => 'delete_tracking_cache',
			'rocket_buffer'                       => 'cache_busting_google_tracking',
		];

		return $events;
	}

	/**
	 * Processes the cache busting on the HTML content
	 *
	 * Google Analytics replacement is performed first, and if no replacement occured, Google Tag Manager replacement is performed.
	 *
	 * @since 3.1
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
	 *
	 * @return void
	 */
	public function schedule_tracking_cache_update() {
		if ( ! $this->is_busting_active() ) {
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
	 *
	 * @return bool
	 */
	public function update_tracking_cache() {
		if ( ! $this->is_busting_active() ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'ga' );

		return $processor->refresh_save( $processor->get_url() );
	}

	/**
	 * Adds weekly interval to cron schedules
	 *
	 * @since 3.1
	 *
	 * @param Array $schedules An array of intervals used by cron jobs.
	 * @return Array
	 */
	public function add_schedule( $schedules ) {
		if ( ! $this->is_busting_active() ) {
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
	 * @since 3.6 Argument replacement.
	 *
	 * @param  string $type Type of cache clearance: 'all', 'post', 'term', 'user', 'url'.
	 * @return bool
	 */
	public function delete_tracking_cache( $type ) {
		if ( 'all' !== $type || ! $this->is_busting_active() ) {
			return false;
		}

		$this->busting_factory->type( 'gtm' )->delete();

		return $this->busting_factory->type( 'ga' )->delete();
	}

	/**
	 * Checks if the cache busting should happen
	 *
	 * @since 3.1
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		return $this->is_busting_active();
	}

	/**
	 * Tell if the cache busting option is active.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function is_busting_active() {
		return (bool) $this->options->get( 'google_analytics_cache', 0 );
	}
}
