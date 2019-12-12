<?php
namespace WP_Rocket\Tests\Integration\Subscriber\ExpiredCachePurgeSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber;

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
			Expired_Cache_Purge_Subscriber::get_subscribed_events()
		);
	}
}
