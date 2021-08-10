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
		];
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
		$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );

		$this->litespeed_header_purge_url( $home_url );
		$this->litespeed_header_purge_url( $home_pagination_url );
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
		if ( headers_sent() ) {
			return;
		}
		$parse_url = get_rocket_parse_url( $url );
		$path      = $parse_url['path'];

		$private_prefix = 'X-LiteSpeed-Purge: ' . $path;

		@header( $private_prefix, false ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Set LiteSpeed header to purge all
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	private function litespeed_header_purge_all() {
		if ( headers_sent() ) {
			return;
		}
		$private_prefix = 'X-LiteSpeed-Purge: *';
		@header( $private_prefix ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
}
