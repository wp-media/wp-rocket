<?php
namespace WP_Rocket\Tests\Integration\Subscriber\ExpiredCachePurgeSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Cache\Expired_Cache_Purge;
use WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber;

class TestScheduleEvent extends TestCase {
	public function testShouldScheduleEvent() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => HOUR_IN_SECONDS,
			]
		);

		$options        = new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) );
		$expired_cache_purge_subscriber = new Expired_Cache_Purge_Subscriber( $options, new Expired_Cache_Purge( WP_ROCKET_CACHE_PATH ) );

		$expired_cache_purge_subscriber->schedule_event();

		$event = wp_next_scheduled( 'rocket_purge_time_event' );

		$this->assertNotFalse( $event );

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

		$options        = new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) );
		$expired_cache_purge_subscriber = new Expired_Cache_Purge_Subscriber( $options, new Expired_Cache_Purge( WP_ROCKET_CACHE_PATH ) );

		$expired_cache_purge_subscriber->schedule_event();

		$event = wp_next_scheduled( 'rocket_purge_time_event' );

		$this->assertFalse( $event );
	}
}
