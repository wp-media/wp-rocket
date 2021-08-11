<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

class LiteSpeed implements Subscriber_Interface {
	/**
	 * Headers to be added.
	 *
	 * @var array.
	 */
	private $headers = [];

	/**
	 * Subscribed events for Litespeed.
	 *
	 * @since 3.9.2
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'before_rocket_clean_domain' => 'litespeed_clean_domain',
			'before_rocket_clean_file'   => 'litespeed_clean_file',
			'before_rocket_clean_home'   => [ 'litespeed_clean_home', 10, 2 ],
			'after_rocket_clean_post'    => [ 'litespeed_send_headers', 10 ],
		];
	}

	/**
	 * Add purge headers to the request
	 *
	 * @since  3.9.2
	 *
	 * @return void
	 */
	public function litespeed_send_headers() {
		if ( empty( $this->headers ) ) {
			return;
		}
		foreach ( $this->headers as $x ) {
			@header( "X-LiteSpeed-Purge: {$x}", false ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
	}

	/**
	 * Purge all pages in LiteSpeed
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	public function litespeed_clean_domain() {
		$this->litespeed_header_purge_all();
	}

	/**
	 * Purge a specific page
	 *
	 * @since 3.9.2
	 *
	 * @param string $url The url to purge.
	 *
	 * @return void
	 */
	public function litespeed_clean_file( $url ) {
		$this->litespeed_header_purge_url( trailingslashit( $url ) );
	}

	/**
	 * Purge the homepage and its pagination
	 *
	 * @since  3.9.2
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 *
	 * @return void
	 */
	public function litespeed_clean_home( $root, $lang ) {
		$home_url = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$urls     = [
			'home'       => trailingslashit( get_rocket_i18n_home_url( $lang ) ),
			'pagination' => $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ),
		];

		array_walk(
			$urls,
			function( &$url ) {
				$url = wp_parse_url( $url, PHP_URL_PATH );
			}
		);

		$urls = array_filter( $urls );

		if ( empty( $urls ) ) {
			return;
		}
		$this->headers[] = $urls['home'];
		$this->headers[] = $urls['pagination'];
	}

	/**
	 * Set LiteSpeed header for the URL to purge
	 *
	 * @since 3.9.2
	 *
	 * @param string $url The URL to purge.
	 *
	 * @return void
	 */
	private function litespeed_header_purge_url( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( ! $path ) {
			return;
		}

		$this->headers[] = $path;
	}

	/**
	 * Set LiteSpeed header to purge all
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	private function litespeed_header_purge_all() {
		@header( 'X-LiteSpeed-Purge: *' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
}
