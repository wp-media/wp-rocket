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

	/**
	 * @param LoadInitialSitemap $controller
	 */
	public function __construct(LoadInitialSitemap $controller)
	{
		$this->controller = $controller;
	}


	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events()
	{
		return [
			'update_option_' . WP_ROCKET_SLUG => [ 'maybe_load_initial_sitemap', 10, 2 ],
		];
	}

	/**
	 * Load first tasks from preload when configuuration from sitemap changed.
	 * @param $old_value
	 * @param $value
	 * @return void
	 */
	public function maybe_load_initial_sitemap($old_value, $value ) {
		if ( ! isset( $value['sitemap_preload'], $old_value['sitemap_preload'] ) ) {
			return;
		}

		if ( $value['sitemap_preload'] === $old_value['sitemap_preload'] ) {
			return;
		}

		if(! $value['sitemap_preload']) {
			return;
		}

		$this->controller->load_initial_sitemap();
	}
}
