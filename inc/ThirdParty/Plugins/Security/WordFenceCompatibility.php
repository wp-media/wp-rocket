<?php
namespace WP_Rocket\ThirdParty\Plugins\Security;

use WP_Rocket\Event_Management\Subscriber_Interface;
use Exception;
use wordfence;
use WP_Rocket\Logger\Logger;


/**
 * Compatibility file for WordFence plugin
 *
 * @since 3.10
 */
class WordFenceCompatibility implements Subscriber_Interface {

	/**
	 * Whitelisted_IPS.
	 */
	const Whitelisted_IPS = ['1.1.1.1','2.2.2.2'];

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
	 * whitelist wp-rocket ips in wordfence firewall
	 *
	 * @since 3.10
	 *
	 * @return void
	 */
	public function whitelist_wordfence_firewall_ips() {

		$ips = apply_filters( 'rocket_wordfence_whitelisted_ips', self::Whitelisted_IPS );

		if ( empty( $ips ) ) {
			return;
		}

		foreach ( $ips as $ip ) {
			wordfence::whitelistIP( $ip );
		}
	}
}
