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
	 * Creates an instance of the class.
	 *
	 * @param LoadInitialSitemap $controller controller creating the initial task.
	 * @param Cache              $query Cache query instance.
	 */
	public function __construct( LoadInitialSitemap $controller, $query ) {
		$this->controller = $controller;
		$this->query      = $query;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'update_option_' . WP_ROCKET_SLUG => [ 'maybe_load_initial_sitemap', 10, 2 ],
			'rocket_after_process_buffer'     => 'update_cache_row',
		];
	}

	/**
	 * Load first tasks from preload when preload option is enabled.
	 *
	 * @param array $old_value old configuration values.
	 * @param array $value new configuration values.
	 * @return void
	 */
	public function maybe_load_initial_sitemap( $old_value, $value ) {
		if ( ! isset( $value['manual_preload'], $old_value['manual_preload'] ) ) {
			return;
		}

		if ( $value['manual_preload'] === $old_value['manual_preload'] ) {
			return;
		}

		if ( ! $value['manual_preload'] ) {
			return;
		}

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
