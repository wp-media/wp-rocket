<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Fleet;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Hooks the Fleet route into WordPress.
 *
 * Nothing here decides whether Fleet is allowed. The route is registered
 * unconditionally and refuses on its own, because the alternative — not
 * registering it when consent is off — makes a site with Fleet switched off
 * answer 404 where a site that has simply never polled answers 401, and the
 * difference tells an outsider which licences have opted in.
 *
 * @since 3.23.4
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * The route.
	 *
	 * @var Route
	 */
	private $route;

	/**
	 * Instantiate the class.
	 *
	 * @param Route $route The route.
	 */
	public function __construct( Route $route ) {
		$this->route = $route;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @since 3.23.4
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rest_api_init' => [ 'register_routes' ],
		];
	}

	/**
	 * Register the route.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$this->route->register_routes();
	}
}
