<?php
namespace WP_Rocket\Subscriber\Third_Party\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

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
	 * Purge all the domain
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 * @param string $url  The home url.
	 * @return void
	 */
	public function clean_domain( $root, $lang, $url ) {
		$this->send_purge_request( $url );
	}

	/**
	 * Purge a specific page
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $url The url to purge.
	 */
	public function clean_file( $url ) {
		$url = str_replace( '*', '', $url );
		$this->send_purge_request( $url );
	}

	/**
	 * Purge the homepage and its pagination
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 */
	public function clean_home( $root, $lang ) {
		$url = trailingslashit( get_rocket_i18n_home_url( $lang ) );

		$this->send_purge_request( $url );
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

		$purge_request = wp_remote_get( $purge_url );

		if ( is_wp_error( $purge_request ) ) {
			Logger::error( 'Error while purging NGINX Cache for url: ' . $purge_url, [ 'NGINX Add-on' ] );
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $purge_request );

		if ( 200 === $response_code ) {
			Logger::info( 'NGINX Cache purged for url: ' . $purge_url, [ 'NGINX Add-on' ] );
			return;
		}

		if ( 404 === $response_code ) {
			Logger::error( 'URL currently not NGINX cached: ' . $purge_url, [ 'NGINX Add-on'] );
			return;
		}
	}
}
