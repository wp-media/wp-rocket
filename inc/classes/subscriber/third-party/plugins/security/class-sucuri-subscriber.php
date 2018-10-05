<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Security;

use WP_Rocket\Event_Management\Subscriber_Interface;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Sucuri Security compatibility.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class Sucuri_Subscriber implements Subscriber_Interface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( '\SucuriScanFirewall' ) ) {
			return [];
		}

		return [
			'after_rocket_clean_domain' => 'clean_firewall_cache',
			'after_rocket_clean_post'   => 'clean_firewall_cache',
			'after_rocket_clean_term'   => 'clean_firewall_cache',
			'after_rocket_clean_user'   => 'clean_firewall_cache',
			'after_rocket_clean_home'   => 'clean_firewall_cache',
			'after_rocket_clean_file'   => 'clean_firewall_cache',
		];
	}

	/**
	 * Clear Sucuri firewall cache.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function clean_firewall_cache() {
		if ( ! method_exists( '\SucuriScanFirewall', 'getKey' ) || ! method_exists( '\SucuriScanFirewall', 'clearCache' ) ) {
			return;
		}

		ob_start();
		$api_key = \SucuriScanFirewall::getKey();

		if ( $api_key ) {
			\SucuriScanFirewall::clearCache( $api_key );
		}
		ob_end_clean();
	}
}
