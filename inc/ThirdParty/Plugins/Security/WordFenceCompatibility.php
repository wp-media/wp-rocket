<?php
namespace WP_Rocket\ThirdParty\Plugins\Security;

use WP_Rocket\Event_Management\Subscriber_Interface;
use wordfence;
use wfConfig;

/**
 * Compatibility file for WordFence plugin
 *
 * @since 3.10
 */
class WordFenceCompatibility implements Subscriber_Interface {

	/**
	 * Whitelisted_IPS.
	 */
	const WHITELISTED_IPS = [ '141.94.254.72' ];

	/**
	 * Old Whitelisted IP.
	 *
	 * @var string
	 */
	private $old_rucss_ip = '135.125.83.227';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.10
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return [];
		}

		return [
			'init' => [ 'whitelist_wordfence_firewall_ips', 11 ],
		];
	}

	/**
	 * Removes old ip from whitelist.
	 *
	 * @return void
	 */
	private function pop_old_ip() {
		// Get all whitelists.
		$whitelists = wfConfig::get( 'whitelisted', '' );

		// Convert to array.
		$whitelist_array = explode( ',', $whitelists );

		// Get old ip index.
		$old_ip_index = array_search( $this->old_rucss_ip, $whitelist_array, true );

		// Check if old ip is still whitelisted.
		if ( ! isset( $whitelist_array[ $old_ip_index ] ) ) {
			return;
		}

		// Remove old ip from whitelist.
		unset( $whitelist_array[ $old_ip_index ] );

		// Update whitelist.
		wfConfig::set( 'whitelisted', implode( ',', $whitelist_array ) );
	}

	/**
	 * Whitelist wp-rocket ips in wordfence firewall
	 *
	 * @since 3.10
	 *
	 * @return void
	 */
	public function whitelist_wordfence_firewall_ips() {

		// Pop old rucss ip.
		$this->pop_old_ip();

		/**
		 * Rocket wordfence whitelisted ips filter which adds IPs to wordfence whitelist.
		 *
		 * @since  3.10
		 *
		 * @param array  list of IPs should be whitelisted
		 */
		$ips = apply_filters( 'rocket_wordfence_whitelisted_ips', self::WHITELISTED_IPS );

		if ( empty( $ips ) ) {
			return;
		}

		foreach ( $ips as $ip ) {
			wordfence::whitelistIP( $ip );
		}
	}
}
