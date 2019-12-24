<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber
 * @group RocketCDN
 */
class TestGetSubscribedEvents extends TestCase {
	/**
	 * @covers ::get_subscribed_events
	 */
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
