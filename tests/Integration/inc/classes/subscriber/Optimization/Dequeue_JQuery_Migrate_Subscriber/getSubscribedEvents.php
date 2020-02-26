<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber::get_subscribed_events
 * @group  jQueryMigrate
 */
class Test_GetSubscribedEvents extends TestCase {

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'wp_default_scripts' => [ 'dequeue_jquery_migrate' ],
		];
		$this->assertSame(
			$events,
			Dequeue_JQuery_Migrate_Subscriber::get_subscribed_events()
		);
	}
}
