<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeExpired\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;

/**
 * Test class covering Subscriber::get_subscribed_events
 * @group Subscriber
 */
class TestGetSubscribedEvents extends TestCase {

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'init'                    => 'schedule_event',
			'rocket_deactivation'     => 'unschedule_event',
			'rocket_purge_time_event' => 'purge_expired_files',
			'cron_schedules'          => 'custom_cron_schedule',
			'wp_rocket_upgrade'       => [ 'update_lifespan_option_on_update', 13, 2 ],
		];

		$this->assertSame(
			$events,
			Subscriber::get_subscribed_events()
		);
	}
}
