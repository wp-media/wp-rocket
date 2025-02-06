<?php

namespace WP_Rocket\Engine\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class ActionSchedulerSubscriber implements Subscriber_Interface {

	use ReturnTypesTrait;

	public static function get_subscribed_events() {
		return [
			'hook' => 'callback',
		];
	}

	public function callback() {
		return get_option('test_option', false );
	}
}
