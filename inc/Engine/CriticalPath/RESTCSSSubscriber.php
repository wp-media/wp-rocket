<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Class RESTCSSSubscriber
 *
 * @package WP_Rocket\Engine\CriticalPath
 */
class RESTCSSSubscriber implements Subscriber_Interface {

	/**
	 * REST manager that has generate and delete methods.
	 *
	 * @var RESTWPInterface
	 */
	private $rest_manager;

	/**
	 * RESTCSSSubscriber constructor.
	 *
	 * @param RESTWPInterface $rest_manager REST manager instance.
	 */
	public function __construct( RESTWPInterface $rest_manager ) {
		$this->rest_manager = $rest_manager;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => [ 'register_routes' ],
		];
	}

	/**
	 * Registers generate/delete routes in the API.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function register_routes() {
		$this->rest_manager->register_generate_route();
		$this->rest_manager->register_delete_route();
	}

}
