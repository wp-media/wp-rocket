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
	 * REST Post manager that has generate and delete methods.
	 *
	 * @var RESTWPPost
	 */
	private $rest_post_manager;

	/**
	 * RESTCSSSubscriber constructor.
	 *
	 * @param RESTWPPost $rest_post_manager Post manager instance.
	 */
	public function __construct( RESTWPPost $rest_post_manager ) {
		$this->rest_post_manager = $rest_post_manager;
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
		$this->rest_post_manager->register_generate_route();
		$this->rest_post_manager->register_delete_route();
	}

}
