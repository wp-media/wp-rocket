<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Cache query instance
	 *
	 * @var CacheQuery
	 */
	private $query;

	/**
	 * Instantiate the class
	 *
	 * @param [type] $query Cache query instance.
	 */
	public function __construct( $query ) {
		$this->query = $query;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_after_process_buffer' => 'update_cache_row',
		];
	}

	/**
	 * Update the cache row when processing the buffer
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
