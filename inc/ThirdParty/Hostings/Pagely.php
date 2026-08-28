<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Pagely hosting compatibility.
 *
 * @since 3.24
 */
class Pagely implements Subscriber_Interface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_after_clean_domain' => 'clean_pagely',
		];
	}

	/**
	 * Call the cache server to purge the cache with Pagely hosting.
	 *
	 * @since 2.5.7
	 *
	 * @return void
	 */
	public function clean_pagely() {
		// Redundant safety net: HostResolver already detects PagelyCachePurge at boot time, but keep this
		// guard so the callback degrades safely to a no-op if the class is somehow not defined yet (#8768).
		if ( class_exists( 'PagelyCachePurge' ) ) {
			$purger = new \PagelyCachePurge();
			$purger->purgeAll();
		}
	}
}
