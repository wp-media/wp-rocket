<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Controller to load initial tasks.
	 *
	 * @var LoadInitialSitemap
	 */
	protected $controller;

	/**
	 * Cache query instance
	 *
	 * @var Cache
	 */
	private $query;

	/**
	 * Instantiate the class
	 *
	 * @param LoadInitialSitemap $controller Controller to load initial tasks.
	 * @param Cache              $query Cache query instance.
	 */
	public function __construct( LoadInitialSitemap $controller, $query ) {
		$this->controller = $controller;
		$this->query      = $query;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'update_option_' . WP_ROCKET_SLUG => [ 'load_initial_sitemap', 10, 2 ],
			'rocket_after_process_buffer'     => 'update_cache_row',
		];
	}

	/**
	 * Load the initial sitemap into the queue.
	 *
	 * @return void
	 */
	public function load_initial_sitemap() {
		$this->controller->load_initial_sitemap();
	}

	/**
	 * Create or update the cache row after processing the buffer
	 *
	 * @return void
	 */
	public function update_cache_row() {
		global $wp;

		$url = home_url( add_query_arg( [], $wp->request ) );

		$this->query->create_or_update(
			[
				'url'    => $url,
				'status' => 'completed',
			]
		);
	}
}
