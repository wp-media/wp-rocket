<?php

namespace WP_Rocket\Engine\HealthCheck;

use WP_Rocket\Event_Management\Subscriber_Interface;

class PageCache implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'http_headers_useragent' => [ 'page_cache_useragent', 10, 2 ],
		];
	}

	/**
	 * Pass plugin header to skip test "mandatory cookie".
	 *
	 * @param string $user_agent WordPress user agent string.
	 * @param string $url        The request URL.
	 * @return string
	 */
	public function page_cache_useragent( $user_agent, $url = null ) {
		$uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		if (
			strpos( $uri, 'wp-site-health' ) !== false &&
			strpos( $uri, 'page-cache' ) !== false
		) {
			$user_agent = 'WP Rocket';
		}

		return $user_agent;
	}
}
