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
			'admin_init' => 'log_message',
		];
	}

	public function log_message()
	{
		$this->test_class->test1();
	}

}
