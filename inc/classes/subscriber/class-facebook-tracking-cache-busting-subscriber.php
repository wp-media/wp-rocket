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
	 * @param Options         $options Instance of the Option_Data class.
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
			'cron_schedules'                        => 'add_schedule',
			'init'                                  => 'schedule_tracking_cache_update',
			'rocket_facebook_tracking_cache_update' => 'update_tracking_cache',
			'after_rocket_clean_cache_busting'      => 'delete_tracking_cache',
			'rocket_buffer'                         => 'cache_busting_facebook_tracking',
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

		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'weekly', 'rocket' ),
		);

		return $schedules;
	}

	/**
	 * Schedule the auto-update of the cache busting files.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function schedule_tracking_cache_update() {
		if ( ! $this->is_busting_active() ) {
			return;
		}

		if ( ! wp_next_scheduled( 'rocket_facebook_tracking_cache_update' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_facebook_tracking_cache_update' );
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
	public function update_tracking_cache() {
		if ( ! $this->is_busting_active() ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'fbpix' );

		return $processor->refresh_save_all();
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
	public function delete_tracking_cache( $ext ) {
		if ( 'js' !== $ext ) {
			return false;
		}

		if ( ! $this->is_busting_active() ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'fbpix' );

		return $processor->delete();
	}

	/**
	 * Process the cache busting on the HTML content.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $html HTML content.
	 * @return string
	 */
	public function cache_busting_facebook_tracking( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$processor = $this->busting_factory->type( 'fbpix' );

		return $processor->replace_url( $html );
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
