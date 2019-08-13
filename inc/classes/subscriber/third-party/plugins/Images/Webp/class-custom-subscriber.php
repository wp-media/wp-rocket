<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the case the user wants to turn off webp cache.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class Custom_Subscriber implements Webp_Interface, Subscriber_Interface {
	use Webp_Common;

	/**
	 * Plugin basename (fake in this case).
	 *
	 * @var    string
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_basename;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_webp_plugins' => 'register',
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PUBLIC TOOLS ============================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the plugin name.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_name() {
		return 'a custom method';
	}

	/**
	 * Get the plugin identifier.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_id() {
		return 'custom';
	}

	/**
	 * Tell if the plugin converts images to webp.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_converting_to_webp() {
		return false;
	}

	/**
	 * Tell if the plugin serves webp images on frontend.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_serving_webp() {
		/** This filter is documented in inc/classes/buffer/class-cache.php */
		return (bool) apply_filters( 'rocket_disable_webp_cache', false );
	}

	/**
	 * Get the plugin basename.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function get_basename() {
		if ( empty( $this->plugin_basename ) ) {
			$this->plugin_basename = plugin_basename( WP_ROCKET_FILE );
		}

		return $this->plugin_basename;
	}
}
