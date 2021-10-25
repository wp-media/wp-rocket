<?php
namespace WP_Rocket\ThirdParty\Plugins\Security;

use WP_Rocket\Event_Management\Subscriber_Interface;
use wordfence;

/**
 * Compatibility file for WordFence plugin
 *
 * @since 3.10
 */
class WordFenceCompatibility implements Subscriber_Interface {

	/**
	 * Whitelisted_IPS.
	 */
	const WHITELISTED_IPS = [ '135.125.83.227' ];

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
	 * Whitelist wp-rocket ips in wordfence firewall
	 *
	 * @since 3.10
	 *
	 * @return void
	 */
	public function whitelist_wordfence_firewall_ips() {
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
