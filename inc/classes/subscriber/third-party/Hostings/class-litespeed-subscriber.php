<?php
namespace WP_Rocket\Subscriber\Third_Party\Hostings;

use WP_Rocket\Logger\Logger;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Litespeed
 *
 * @since  3.4.1
 * @author Soponar Cristina
 */
class Litespeed_Subscriber implements Subscriber_Interface {
	/**
	 * Subscribed events for Litespeed.
	 *
	 * @since  3.4.1
	 * @author Soponar Cristina
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! isset( $_SERVER['HTTP_X_LSCACHE'] ) ) {
			return [];
		}

		return [
			'before_rocket_clean_domain' => 'litespeed_clean_domain',
			'before_rocket_clean_file'   => 'litespeed_clean_file',
			'before_rocket_clean_home'   => [ 'litespeed_clean_home', 10, 2 ],
		];
	}

	/**
	 * Purge Litespeed all domain.
	 *
	 * @since  3.4.1
	 * @author Soponar Cristina
	 */
	public function litespeed_clean_domain() {
		$this->litespeed_header_purge_all();
	}

	/**
	 * Purge a specific page
	 *
	 * @since  3.4.1
	 * @author Soponar Cristina
	 *
	 * @param string $url The url to purge.
	 */
	public function litespeed_clean_file( $url ) {
		$this->litespeed_header_purge_url( trailingslashit( $url ) );
	}

	/**
	 * Purge the homepage and its pagination
	 *
	 * @since  3.4.1
	 * @author Soponar Cristina
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 */
	public function litespeed_clean_home( $root, $lang ) {
		$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );

		$this->litespeed_header_purge_url( $home_url );
		$this->litespeed_header_purge_url( $home_pagination_url );
	}

	/**
	 * Purge Litespeed URL
	 *
	 * @since 3.4.1
	 * @author Soponar Cristina
	 *
	 * @param  string $url The URL to purge.
	 * @return void
	 */
	public function litespeed_header_purge_url( $url ) {
		if ( headers_sent() ) {
			Logger::debug(
				'X-LiteSpeed Headers already sent',
				[ 'headers_sent' ]
			);
			return;
		}

		$parse_url      = get_rocket_parse_url( $url );
		$path           = rtrim( $parse_url['path'], '/' );
		$private_prefix = 'X-LiteSpeed-Purge: ' . $path;

		Logger::debug(
			'X-LiteSpeed',
			[
				'litespeed_header_purge_url',
				'path' => $private_prefix,
			]
		);

		@header( $private_prefix );
	}

	/**
	 * Purge Litespeed Cache
	 *
	 * @since 3.4.1
	 * @author Soponar Cristina
	 *
	 * @return void
	 */
	public function litespeed_header_purge_all() {
		if ( headers_sent() ) {
			return;
		}
		$private_prefix = 'X-LiteSpeed-Purge: *';
		@header( $private_prefix );
	}

}
