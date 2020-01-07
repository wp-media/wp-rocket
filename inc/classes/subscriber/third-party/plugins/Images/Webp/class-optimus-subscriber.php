<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the WebP support with Optimus.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class Optimus_Subscriber implements Webp_Interface, Subscriber_Interface {

	/**
	 * Optimus basename.
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
		if ( ! defined( 'OPTIMUS_FILE' ) ) {
			return [];
		}

		return [
			'rocket_webp_plugins' => 'register',
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** HOOKS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Register the plugin.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $webp_plugins An array of Webp_Interface objects.
	 * @return array
	 */
	public function register( $webp_plugins ) {
		$webp_plugins[] = $this;
		return $webp_plugins;
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
		return 'Optimus';
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
		return 'optimus';
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
		if ( class_exists( '\Optimus' ) && method_exists( '\Optimus', 'get_options' ) ) {
			$options = \Optimus::get_options();
		} else {
			$options = get_option( 'optimus' );
		}

		return ! empty( $options['webp_convert'] );
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
		return false;
	}

	/**
	 * Tell if the plugin uses a CDN-compatible technique to serve webp images on frontend.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_serving_webp_compatible_with_cdn() {
		return false;
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
			$this->plugin_basename = defined( 'OPTIMUS_FILE' ) ? plugin_basename( OPTIMUS_FILE ) : 'optimus/optimus.php';
		}

		return $this->plugin_basename;
	}
}
