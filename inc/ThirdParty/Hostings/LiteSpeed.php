<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

class LiteSpeed implements Subscriber_Interface {
	/**
	 * Litespeed headers
	 *
	 * @var array
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
		foreach ( $this->headers as $header ) {
			@header( "X-LiteSpeed-Purge: {$header}", false ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
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
			function ( &$url ) {
				$url = wp_parse_url( $url, PHP_URL_PATH );
			}
		);

		$urls = array_filter( $urls );

		if ( empty( $urls ) ) {
			return;
		}

		$this->send_header( 'X-LiteSpeed-Purge: ' . $urls['home'] );
		$this->send_header( 'X-LiteSpeed-Purge: ' . $urls['pagination'] );
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

		$header = 'X-LiteSpeed-Purge: ' . $path;

		$this->send_header( $header );
	}

	/**
	 * Set LiteSpeed header to purge all
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	private function litespeed_header_purge_all() {
		$this->send_header( 'X-LiteSpeed-Purge: *', true );
	}

	/**
	 * If header is not in header_list() send it
	 *
	 * @since 3.9.2
	 *
	 * @param string  $header To be sent.
	 * @param boolean $replace header.
	 *
	 * @return void
	 */
	private function send_header( $header, $replace = false ) {
		if ( headers_sent() || in_array( $header, headers_list(), true ) ) {
			return;
		}
		$this->headers[] = $header;
		@header( $header, $replace ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
}
