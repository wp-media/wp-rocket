<?php


namespace WP_Rocket\ThirdParty\Hostings;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Event_Management\Subscriber_Interface;

class LiteSpeed implements Subscriber_Interface {
	/**
	 * @var array
	 */
	private $headers = [];

	/**
	 * Subscribed events for Litespeed.
	 *
	 * @since  3.9.1
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! isset( $_SERVER['X_LSCACHE'] ) ) {
			return [];
		}

		return [
			'before_rocket_clean_domain' => 'litespeed_clean_domain',
			'before_rocket_clean_file'   => 'litespeed_clean_file',
			'before_rocket_clean_home'   => [ 'litespeed_clean_home', 10, 2 ],
			'wp_headers'                 => 'litespeed_send_headers',
		];
	}

	/**
	 * wp headers filter callback to add headers to response.
	 *
	 * @since  3.9.1
	 * @param array $headers headers to be added to response.
	 *
	 * @return array of all headers.
	 */
	public function litespeed_send_headers( $headers ) {
		return array_merge( $headers, $this->headers );
	}

	/**
	 * Purge Litespeed all domain.
	 *
	 * @since  3.9.1
	 */
	public function litespeed_clean_domain() {
		$this->litespeed_header_purge_all();
	}

	/**
	 * Purge a specific page
	 *
	 * @since  3.9.1
	 *
	 * @param string $url The url to purge.
	 */
	public function litespeed_clean_file( $url ) {
		$this->litespeed_header_purge_url( trailingslashit( $url ) );
	}

	/**
	 * Purge the homepage and its pagination
	 *
	 * @since  3.9.1
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
	 * @since 3.9.1
	 *
	 * @param  string $url The URL to purge.
	 * @return void
	 */
	public function litespeed_header_purge_url( $url ) {
		$parse_url      = get_rocket_parse_url( $url );
		$path           = rtrim( $parse_url['path'], '/' );
		$this->headers['X-LiteSpeed-Purge'] = $path;
	}

	/**
	 * Purge Litespeed Cache
	 *
	 * @since 3.9.1
	 *
	 * @return void
	 */
	public function litespeed_header_purge_all() {
		$this->headers['X-LiteSpeed-Purge'] = '*';
	}
}
