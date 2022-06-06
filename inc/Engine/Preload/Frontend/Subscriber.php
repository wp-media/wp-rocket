<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\CheckFinished;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Controller parsing the sitemap.
	 *
	 * @var FetchSitemap
	 */
	protected $parse_sitemap;

	/**
	 * Controller preloading urls.
	 *
	 * @var PreloadUrl
	 */
	protected $preload_controller;

	/**
	 * Creates an instance of the class.
	 *
	 * @param FetchSitemap $parse_sitemap controller parsing the sitemap.
	 * @param PreloadUrl   $preload_controller controller preloading urls.
	 */
	public function __construct( FetchSitemap $parse_sitemap, PreloadUrl $preload_controller ) {
		$this->parse_sitemap      = $parse_sitemap;
		$this->preload_controller = $preload_controller;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_job_parse_sitemap' => 'parse_sitemap',
			'rocket_preload_job_preload_url'   => 'preload_url',
		];
	}

	/**
	 * Parse the sitemap.
	 *
	 * @param string $url url to parse.
	 * @return void
	 */
	public function parse_sitemap( string $url ) {
		$this->parse_sitemap->parse_sitemap( $url );
	}

	/**
	 * Preload url.
	 *
	 * @param string $url url to preload.
	 * @return void
	 */
	public function preload_url( string $url ) {
		$this->preload_controller->preload_url( $url );
	}
}
