<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface
{
	/**
	 * Controller to load initial tasks.
	 * @var LoadInitialSitemap
	 */
	protected $controller;

	public static function get_subscribed_events()
	{
		return [
			'update_option_' . WP_ROCKET_SLUG => [ 'load_initial_sitemap', 10, 2 ],

		];
	}

	public function load_initial_sitemap() {
		$this->controller->load_initial_sitemap();
	}
}
