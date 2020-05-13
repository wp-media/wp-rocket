<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\ExpiredCachePurgeSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Cache\ExpiredCachePurge;
use WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber::schedule_event
 * @uses   \WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber::get_cache_lifespan
 * @uses   \WP_Rocket\Admin\Options
 * @uses   \WP_Rocket\Admin\Options_Data
 * @uses   \WP_Rocket\Engine\Cache\ExpiredCachePurge
 *
 * @group  Cache
 * @group  Subscriber
 */
class Test_ScheduleEvent extends TestCase {

	private function getSubscriberInstance() {
		return new ExpiredCachePurgeSubscriber(
			new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) ),
			new ExpiredCachePurge( _rocket_get_wp_rocket_cache_path() )
		);
	}

	public function testShouldScheduleEvent() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => HOUR_IN_SECONDS,
			]
		);

		$this->getSubscriberInstance()->schedule_event();

		$this->assertNotFalse( wp_next_scheduled( 'rocket_purge_time_event' ) );

		wp_clear_scheduled_hook( 'rocket_purge_time_event' );
	}

	public function testShouldNotScheduleEventWhenNoLifespan() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 0,
				'purge_cron_unit'     => HOUR_IN_SECONDS,
			]
		);

		$this->getSubscriberInstance()->schedule_event();

		$this->assertFalse( wp_next_scheduled( 'rocket_purge_time_event' ) );
	}
}
