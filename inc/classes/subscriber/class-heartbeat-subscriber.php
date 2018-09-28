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
			'admin_enqueue_scripts' => [ 'maybe_disable', $priority ],
			'wp_enqueue_scripts'    => [ 'maybe_disable', $priority ],
			'heartbeat_settings'    => [ 'maybe_modify_period', $priority ],
		];
	}

	/**
	 * Maybe disable Heartbeat.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function maybe_disable() {
		if ( ! $this->behavior_match_context( 'disable' ) ) {
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
		if ( ! $this->behavior_match_context( 'reduce_periodicity' ) ) {
			return $settings;
		}

		$settings['interval']        = 120;
		$settings['minimalInterval'] = 120;

		return $settings;
	}

	/**
	 * Tell if we're in frontend, backend, or a post edition page.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return string Either 'site' (frontend), 'admin' (backend), or 'editor'.
	 */
	private function get_current_context() {
		$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$request_uri = explode( '?', $request_uri, 2 );
		$request_uri = reset( $request_uri );

		if ( $request_uri && preg_match( '@/wp-admin/post(-new)?\.php$@', $request_uri ) ) {
			$context = 'editor';
		} elseif ( is_admin() ) {
			$context = 'admin';

			if ( wp_doing_ajax() && ! empty( $_POST['action'] ) ) {
				if ( 'wp-remove-post-lock' === wp_unslash( $_POST['action'] ) ) {
					$context = 'editor';
				} elseif ( ! empty( $_POST['screen_id'] ) ) {
					switch ( $_POST['screen_id'] ) {
						case 'post':
							$context = 'editor';
							break;
						case 'front':
							$context = 'site';
					}
				}
			}
		} else {
			$context = 'site';
		}

		/**
		 * Filter the current context.
		 * This can be useful for ajax requests or requests to admin-post.php, as there is no easy way to tell where those requests come from.
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param $context string Either 'site' (frontend), 'admin' (backend), or 'editor'.
		 */
		$filtered_context = apply_filters( 'rocket_heartbeat_context', $context );

		$contexts = [
			'editor' => 1,
			'admin'  => 1,
			'site'   => 1,
		];

		return isset( $contexts[ $filtered_context ] ) ? $filtered_context : $context;
	}

	/**
	 * Tell if the given behavior is what is set in the addon settings, accordingly to the current context.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $behavior Either '', 'disable', or 'reduce_periodicity'.
	 * @return bool
	 */
	private function behavior_match_context( $behavior ) {
		if ( ! $this->options->get( 'control_heartbeat', 0 ) ) {
			return false;
		}

		$context = $this->get_current_context();

		return $behavior === $this->options->get( 'heartbeat_' . $context . '_behavior', '' );
	}
}
