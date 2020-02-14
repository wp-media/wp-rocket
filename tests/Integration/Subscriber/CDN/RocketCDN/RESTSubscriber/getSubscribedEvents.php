<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::get_subscribed_events
 * @group RocketCDN
 */
class Test_GetSubscribedEvents extends TestCase {
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
