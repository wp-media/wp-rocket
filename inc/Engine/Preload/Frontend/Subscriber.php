<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Engine\Preload\Controller\CheckFinished;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Controller fetching the sitemap.
	 *
	 * @var FetchSitemap
	 */
	protected $fetch_sitemap;

	/**
	 * Controller preloading urls.
	 *
	 * @var PreloadUrl
	 */
	protected $preload_controller;

	/**
	 * Controller checking if the preload is finished.
	 *
	 * @var CheckFinished
	 */
	protected $check_finished;

	/**
	 * Controller loading the initial sitemap.
	 *
	 * @var LoadInitialSitemap
	 */
	protected $initial_sitemap;

	/**
	 * Creates an instance of the class.
	 *
	 * @param FetchSitemap       $fetch_sitemap controller fetching the sitemap.
	 * @param PreloadUrl         $preload_controller controller preloading urls.
	 * @param CheckFinished      $check_finished controller checking if the preload is finished.
	 * @param LoadInitialSitemap $initial_sitemap Controller loading the initial sitemap.
	 */
	public function __construct( FetchSitemap $fetch_sitemap, PreloadUrl $preload_controller, CheckFinished $check_finished, LoadInitialSitemap $initial_sitemap ) {
		$this->fetch_sitemap      = $fetch_sitemap;
		$this->preload_controller = $preload_controller;
		$this->check_finished     = $check_finished;
		$this->initial_sitemap    = $initial_sitemap;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_job_parse_sitemap'        => 'parse_sitemap',
			'rocket_preload_job_preload_url'          => 'preload_url',
			'rocket_preload_job_check_finished'       => 'check_finished',
			'rocket_preload_job_load_initial_sitemap' => 'load_initial_sitemap',
		];
	}

	/**
	 * Parse the sitemap.
	 *
	 * @param string $url url to parse.
	 * @return void
	 */
	public function parse_sitemap( string $url ) {
		$this->fetch_sitemap->parse_sitemap( $url );
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

	/**
	 * Check if the preload is finished.
	 *
	 * @return void
	 */
	public function check_finished() {
		$this->check_finished->check_finished();
	}

	/**
	 * Load the initial sitemap.
	 *
	 * @return void
	 */
	public function load_initial_sitemap() {
		$this->initial_sitemap->load_initial_sitemap();
	}
}
