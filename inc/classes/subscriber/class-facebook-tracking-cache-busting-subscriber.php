<?php
namespace WP_Rocket\Subscriber;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Event subscriber for Facebook tracking cache busting.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class Facebook_Tracking_Cache_Busting_Subscriber implements Subscriber_Interface {

	/**
	 * Name of the cron.
	 *
	 * @var    string
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	const CRON_NAME = 'rocket_facebook_tracking_cache_update';
	/**
	 * Instance of the Busting Factory class.
	 *
	 * @var    Busting_Factory
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 */
	private $busting_factory;

	/**
	 * Instance of the Option_Data class.
	 *
	 * @var    Options
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param Busting_Factory $busting_factory Instance of the Busting Factory class.
	 * @param Options         $options         Instance of the Options_Data class.
	 */
	public function __construct( Busting_Factory $busting_factory, Options $options ) {
		$this->busting_factory = $busting_factory;
		$this->options         = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'cron_schedules'                   => 'add_schedule',
			'init'                             => 'schedule_cache_update',
			self::CRON_NAME                    => 'update_cache',
			'after_rocket_clean_cache_busting' => 'delete_cache',
			'rocket_buffer'                    => 'cache_busting_facebook_tracking',
		];

		return $events;
	}

	/**
	 * Add weekly interval to cron schedules.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $schedules An array of intervals used by cron jobs.
	 * @return array
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
	 * (Un)Schedule the auto-update of the cache busting files.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function schedule_cache_update() {
		$scheduled = wp_next_scheduled( self::CRON_NAME );

		if ( ! $this->is_busting_active() ) {
			if ( $scheduled ) {
				wp_clear_scheduled_hook( self::CRON_NAME );
			}
			return;
		}

		if ( ! $scheduled ) {
			wp_schedule_event( time(), 'weekly', self::CRON_NAME );
		}
	}

	/**
	 * Update the Facebook Pixel cache busting files.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function update_cache() {
		if ( ! $this->is_busting_active() ) {
			return false;
		}

		$html = $this->busting_factory->type( 'fbsdk' )->refresh();

		return $this->busting_factory->type( 'fbpix' )->refresh_all();
	}

	/**
	 * Delete Facebook Pixel cache busting files.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $ext File extension type.
	 * @return bool
	 */
	public function delete_cache( $ext ) {
		if ( 'js' !== $ext || ! $this->is_busting_active() ) {
			return false;
		}

		$html = $this->busting_factory->type( 'fbsdk' )->delete();

		return $this->busting_factory->type( 'fbpix' )->delete_all();
	}

	/**
	 * Process the cache busting on the HTML contents.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $html HTML contents.
	 * @return string
	 */
	public function cache_busting_facebook_tracking( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$html = $this->busting_factory->type( 'fbsdk' )->replace_url( $html );

		return $this->busting_factory->type( 'fbpix' )->replace_url( $html );
	}

	/**
	 * Tell if the cache busting should happen.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		return $this->is_busting_active();
	}

	/**
	 * Tell if the cache busting option is active.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_busting_active() {
		return (bool) $this->options->get( 'facebook_pixel_cache', 0 );
	}
}
