<?php
namespace WP_Rocket\Subscriber\Addons\Varnish;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Addons\Varnish\Varnish;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for the Varnish Purge
 *
 * @since 3.5
 * @author Remy Perona
 */
class VarnishSubscriber implements Subscriber_Interface {
	/**
	 * Varnish instance
	 *
	 * @var Varnish
	 */
	private $varnish;

	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Varnish      $varnish Varnish instance.
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Varnish $varnish, Options_Data $options ) {
		$this->varnish = $varnish;
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'before_rocket_clean_domain' => [ 'clean_domain', 10, 3 ],
			'before_rocket_clean_file'   => [ 'clean_file' ],
			'before_rocket_clean_home'   => [ 'clean_home', 10, 2 ],
		];
	}

	/**
	 * Checks if Varnish cache should be purged
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function should_purge() {
		if ( ! apply_filters( 'do_rocket_varnish_http_purge', false ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return false;
		}

		if ( ! $this->options->get( 'varnish_auto_purge', 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Clears Varnish cache for the whole domain
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 * @param string $url  The home url.
	 * @return void
	 */
	public function clean_domain( $root, $lang, $url ) {
		if ( ! $this->should_purge() ) {
			return;
		}

		$this->varnish->purge( trailingslashit( $url ) . '?regex' );
	}

	/**
	 * Clears a specific page in Varnish cache
	 *
	 * @param [type] $url The url to purge.
	 * @return void
	 */
	public function clean_file( $url ) {
		if ( ! $this->should_purge() ) {
			return;
		}

		$this->varnish->purge( trailingslashit( $url ) . '?regex' );
	}

	/**
	 * Clears the homepage in Varnish cache
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 * @return void
	 */
	public function clean_home( $root, $lang ) {
		if ( ! $this->should_purge() ) {
			return;
		}

		$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?regex';

		$this->varnish->purge( $home_url );
		$this->varnish->purge( $home_pagination_url );
	}
}
