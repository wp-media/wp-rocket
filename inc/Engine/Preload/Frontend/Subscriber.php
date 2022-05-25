<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Sitemap parser controller.
	 *
	 * @var ParseSitemap
	 */
	protected $parse_sitemap;

	/**
	 * Initialise the Subscriber.
	 *
	 * @param ParseSitemap $parse_sitemap Sitemap parser controller.
	 */
	public function __construct( ParseSitemap $parse_sitemap ) {
		$this->parse_sitemap = $parse_sitemap;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_job_parse_sitemap' => 'parse_sitemap',
		];
	}

	/**
	 * Parse a sitemap.
	 *
	 * @param string $url url from the sitemap.
	 *
	 * @return void
	 */
	public function parse_sitemap( string $url ) {
		$this->parse_sitemap->parse_sitemap( $url );
	}
}
