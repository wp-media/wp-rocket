<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDNSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDNSubscriber;

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
			RocketCDNSubscriber::get_subscribed_events()
		);
	}
}
