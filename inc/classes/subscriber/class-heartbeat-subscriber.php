<?php
namespace WP_Rocket\Subscriber;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Event subscriber to control Heartbeat behavior.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class Heartbeat_Subscriber implements Subscriber_Interface {
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
	 * @param Options $options Instance of the Option_Data class.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
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
		$priority = PHP_INT_MAX - 60;
		return [
			'admin_enqueue_scripts' => [ 'maybe_disable_backend', $priority ],
			'wp_enqueue_scripts'    => [ 'maybe_disable_frontend', $priority ],
			'heartbeat_settings'    => [ 'maybe_modify_period', $priority ],
		];
	}

	/**
	 * Maybe disable Heartbeat on the admin area.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function maybe_disable_backend() {
		if ( ! $this->options->get( 'control_heartbeat', 0 ) ) {
			return;
		}
		if ( 'disable' !== $this->options->get( 'heartbeat_backend_behavior', '' ) ) {
			return;
		}

		wp_deregister_script( 'heartbeat' );
	}

	/**
	 * Maybe disable Heartbeat on the frontend.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function maybe_disable_frontend() {
		if ( ! $this->options->get( 'control_heartbeat', 0 ) ) {
			return;
		}
		if ( 'disable' !== $this->options->get( 'heartbeat_frontend_behavior', '' ) ) {
			return;
		}

		wp_deregister_script( 'heartbeat' );
	}

	/**
	 * Maybe modify Heartbeat periodicity.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $settings The Heartbeat settings.
	 * @return array
	 */
	public function maybe_modify_period( $settings ) {
		if ( ! $this->options->get( 'control_heartbeat', 0 ) ) {
			return $settings;
		}

		$is_frontend = $this->is_frontend();

		if ( $is_frontend && 'reduce_periodicity' !== $this->options->get( 'heartbeat_frontend_behavior', '' ) ) {
			return $settings;
		}

		if ( ! $is_frontend && 'reduce_periodicity' !== $this->options->get( 'heartbeat_backend_behavior', '' ) ) {
			return $settings;
		}

		$settings['interval'] = 120;

		return $settings;
	}

	/**
	 * Tell if we're in the frontend environment.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool True for frontend. False for backend.
	 */
	private function is_frontend() {
		$is_frontend = ! is_admin();

		/**
		 * Force frontend or backend behavior for Heartbeat.
		 * This can be useful for ajax requests or requests to admin-post.php, as there is no easy way to tell where those requests come from.
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param $is_frontend bool True for frontend. False for backend.
		 */
		return (bool) apply_filters( 'rocket_heartbeat_is_frontend', $is_frontend );
	}
}
