<?php
namespace WP_Rocket\Engine\TestLazyload;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	private $test_class;

	public function __construct( TestClass $test_class ) {
		$this->test_class = $test_class;
	}

	public static function get_subscribed_events() {
		return [
			'wp_dashboard_setup' => 'log_message_in_dashboard_only',
		];
	}

	public function log_message_in_dashboard_only()
	{
		$this->test_class->test1();
	}

}
