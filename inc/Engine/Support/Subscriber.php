<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Rest instance
	 *
	 * @var Rest
	 */
	private $rest;

	/**
	 * Instantiate the class
	 *
	 * @param Rest $rest Rest instance.
	 */
	public function __construct( Rest $rest ) {
		$this->rest = $rest;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => 'register_support_route',
		];
	}

	/**
	 * Registers the rest support route
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function register_support_route() {
		$this->rest->register_route();
	}
}
