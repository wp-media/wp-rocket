<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Nginx webserver compatibility.
 *
 * Unlike the other classes in this namespace, Nginx is not a hosting provider — it is a webserver
 * that legitimately co-exists with any of the hosts detected by HostResolver. It is therefore
 * registered independently of HostResolver/HostSubscriberFactory; see issue #8768.
 *
 * @since 3.24
 */
class Nginx implements Subscriber_Interface {

	/**
	 * Whether the current webserver is Nginx.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public static function is_active(): bool {
		global $is_nginx;

		return ! empty( $is_nginx );
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_cache_query_strings' => 'better_nginx_compatibility',
		];
	}

	/**
	 * Compatibility with a usual NGINX configuration which include:
	 *      try_files $uri $uri/ /index.php?q=$uri&$args
	 *
	 * @since 2.3.9
	 *
	 * @param array $query_strings Array of query strings to cache.
	 *
	 * @return array Updated array of query strings.
	 */
	public function better_nginx_compatibility( $query_strings ) {
		global $is_nginx;

		if ( $is_nginx ) {
			$query_strings[] = 'q';
		}

		return $query_strings;
	}
}
