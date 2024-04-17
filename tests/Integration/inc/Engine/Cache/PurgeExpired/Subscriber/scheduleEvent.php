<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeExpired\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;

/**
 * @covers Subscriber::schedule_event
 * @uses   Subscriber::get_cache_lifespan
 * @uses   \WP_Rocket\Admin\Options
 * @uses   \WP_Rocket\Admin\Options_Data
 * @uses   PurgeExpiredCache
 * @group  Subscriber
 */
class Test_ScheduleEvent extends TestCase {

	private function getSubscriberInstance() {
		return new Subscriber(
			new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) ),
			new PurgeExpiredCache( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) )
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
