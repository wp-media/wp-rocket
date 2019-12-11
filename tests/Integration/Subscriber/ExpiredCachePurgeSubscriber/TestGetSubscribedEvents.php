<?php
namespace WP_Rocket\Tests\Integration\Subscriber\ExpiredCachePurgeSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber;

class TestGetSubscribedEvents extends TestCase {
	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'init'                    => 'schedule_event',
			'rocket_deactivation'     => 'unschedule_event',
			'rocket_purge_time_event' => 'purge_expired_files',
			'cron_schedules'          => 'custom_cron_schedule',
		];

		$this->assertSame(
			$events,
			Expired_Cache_Purge_Subscriber::get_subscribed_events()
		);
	}
}
