<?php

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Filesystem_Direct;

class Subscriber implements Subscriber_Interface {

	/**
	 * Name of the option saving the last base URL.
	 *
	 * @string
	 */
	const LAST_BASE_URL_OPTION = 'wp_rocket_last_base_url';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return string[]
	 */
	public static function get_subscribed_events() {
		return [
			'admin_init' => 'maybe_launch_domain_changed',
		];
	}

	/**
	 * Maybe launch the domain changed event.
	 *
	 * @return void
	 */
	public function maybe_launch_domain_changed() {
		$base_url = trailingslashit( home_url() );

		if ( ! get_option( self::LAST_BASE_URL_OPTION ) ) {
			update_option( self::LAST_BASE_URL_OPTION, $base_url );
			return;
		}

		$last_base_url = get_option( self::LAST_BASE_URL_OPTION );

		if ( $base_url === $last_base_url ) {
			return;
		}

		update_option( self::LAST_BASE_URL_OPTION, $base_url );

		do_action( 'rocket_domain_changed' );
	}
}
