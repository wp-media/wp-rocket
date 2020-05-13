<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\ExpiredCachePurgeSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber::get_subscribed_events
 *

 * @group  Subscriber
 */
class TestGetSubscribedEvents extends TestCase {

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'init'                             => 'schedule_event',
			'rocket_deactivation'              => 'unschedule_event',
			'rocket_purge_time_event'          => 'purge_expired_files',
			'cron_schedules'                   => 'custom_cron_schedule',
			'update_option_wp_rocket_settings' => [ 'clean_expired_cache_scheduled_event', 10, 2 ],
		];

		$this->assertSame(
			$events,
			ExpiredCachePurgeSubscriber::get_subscribed_events()
		);
	}
}
