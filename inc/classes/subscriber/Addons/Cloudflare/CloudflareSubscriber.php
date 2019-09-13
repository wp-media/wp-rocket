<?php
namespace WP_Rocket\Subscriber\Addons\Cloudflare;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Cloudflare Subscriber
 *
 * @since 3.5
 * @author Remy Perona
 */
class CloudflareSubscriber implements Subscriber_Interface {
	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_varnish_ip'                 => 'set_varnish_localhost',
			'rocket_varnish_purge_request_host' => 'set_varnish_purge_request_host',
		];
	}

	/**
	 * Sets the Varnish IP to localhost if Cloudflare is active
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string $varnish_ip Varnish IP.
	 * @return string
	 */
	public function set_varnish_localhost( $varnish_ip ) {
		if ( ! $this->should_filter_varnish() ) {
			return $varnish_ip;
		}

		return 'localhost';
	}

	/**
	 * Sets the Host header to the website domain if Cloudflare is active
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string $host the host header value.
	 * @return string
	 */
	public function set_varnish_purge_request_host( $host ) {
		if ( ! $this->should_filter_varnish() ) {
			return $host;
		}

		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Checks if we should filter the value for the Varnish purge
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function should_filter_varnish() {
		if ( ! $this->options->get( 'do_cloudflare' ) ) {
			return false;
		}

		if ( ! apply_filters( 'do_rocket_varnish_http_purge', false ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return false;
		}

		if ( ! $this->options->get( 'varnish_auto_purge', 0 ) ) {
			return false;
		}

		return true;
	}
}
