<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $rest;

	public function __construct( Rest $rest ) {
		$this->rest = $rest;
	}

	public static function get_subscribed_events() {
		return [
			'rest_api_init' => 'register_support_route',
		];
	}

	public function register_support_route() {
		$this->rest->register_route();
	}
}
