<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\Varnish\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Addon\Varnish\Subscriber;

/**
 * @covers WP_Rocket\Addon\Varnish\Subscriber::get_subscribed_events
 * @group  Addons
 * @group  Varnish
 */
class Test_GetSubscribedEvents extends TestCase {

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'before_rocket_clean_domain' => [ 'clean_domain', 10, 3 ],
			'before_rocket_clean_file'   => [ 'clean_file' ],
			'before_rocket_clean_home'   => [ 'clean_home', 10, 2 ],
		];

		$this->assertSame(
			$events,
			Subscriber::get_subscribed_events()
		);
	}
}
