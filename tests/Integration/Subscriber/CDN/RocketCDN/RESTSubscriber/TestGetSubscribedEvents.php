<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\Rocket\RESTSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;

class TestGetSubscribedEvents extends TestCase {
	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'rest_api_init' => [
				[ 'register_enable_route' ],
				[ 'register_disable_route' ],
			],
		];

		$this->assertSame(
			$events,
			RESTSubscriber::get_subscribed_events()
		);
	}
}
