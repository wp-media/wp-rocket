<?php
namespace WP_Rocket\Subscriber\Third_Party\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber to sync NGINX cache with WP Rocket cache
 *
 * @since 3.3
 * @author Remy Perona
 */
class NGINX_Subscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'before_rocket_clean_domain' => [ 'clean_domain', 10, 3 ],
			'before_rocket_clean_file'   => 'clean_file',
			'before_rocket_clean_home'   => [ 'clean_home', 10, 2 ],
		];
	}

	/**
	 * Sends the purge request to NGINX Cache
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	private function send_purge_request( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['path'] ) ) {
			$parsed_url['path'] = '';
		}

		$purge_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/purge' . $parsed_url['path'];

		if ( isset( $parsed_url['query'] ) && '' !== $parsed_url['query'] ) {
			$purge_url .= '?' . $parsed_url['query'];
		}

		wp_remote_get( $purge_url );
	}
}
