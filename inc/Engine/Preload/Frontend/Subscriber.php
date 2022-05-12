<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface
{
	/**
	 * @var ParseSitemap
	 */
	protected $parse_sitemap;

	/**
	 * @param ParseSitemap $parse_sitemap
	 */
	public function __construct(ParseSitemap $parse_sitemap)
	{
		$this->parse_sitemap = $parse_sitemap;
	}


	public static function get_subscribed_events()
	{
		return [
			'rocket_preload_job_parse_sitemap' => 'parse_sitemap',
		];
	}

	public function parse_sitemap(string $url) {
		$this->parse_sitemap->parse_sitemap($url);
	}
}
