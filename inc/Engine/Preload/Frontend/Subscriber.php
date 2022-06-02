<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Engine\Preload\Controller\CheckFinished;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Controller parsing the sitemap.
	 *
	 * @var ParseSitemap
	 */
	protected $parse_sitemap;

	/**
	 * Controller to check the process is finished.
	 *
	 * @var CheckFinished
	 */
	protected $check_finished;

	/**
	 * Creates an instance of the class.
	 *
	 * @param ParseSitemap  $parse_sitemap controller parsing the sitemap.
	 * @param CheckFinished $check_finished controller to check the process is finished.
	 */
	public function __construct( ParseSitemap $parse_sitemap, CheckFinished $check_finished ) {
		$this->parse_sitemap  = $parse_sitemap;
		$this->check_finished = $check_finished;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_job_parse_sitemap' => 'parse_sitemap',
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
}
