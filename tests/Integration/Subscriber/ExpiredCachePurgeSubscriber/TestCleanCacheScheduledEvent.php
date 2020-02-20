<?php

namespace WP_Rocket\Tests\Integration\Subscriber\ExpiredCachePurgeSubscriber;

use WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber;
use WPMedia\PHPUnit\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers Expired_Cache_Purge_Subscriber::wp_clear_scheduled_hook
 * @group  Subscriber_ScheduledEvent
 */
class TestCleanCacheScheduledEvent extends TestCase {

	public function testShouldNotCleanScheduledEventWhenValuesAreTheSame() {
		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);

		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);
	}

	public function testShouldNotCleanScheduledEventWhenChangedValueFromHoursToDays() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);

		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'DAY_IN_SECONDS',
			]
		);
	}

	public function testShouldCleanScheduledEventWhenMinutesAndOldValueIsHours() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);

		Functions\expect( 'wp_clear_scheduled_hook' )
			->once()
			->with( Expired_Cache_Purge_Subscriber::EVENT_NAME )
			->andReturnNull(); // No need to run it.

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);
	}

	public function testShouldNotCleanScheduledEventWhenUnitIsMinutesAndIntervalIsNotChanged() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 20,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);

		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 20,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);
	}

	public function testShouldCleanScheduledEventWhenUnitIsMinutesAndIntervalIsChanged() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 20,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);

		Functions\expect( 'wp_clear_scheduled_hook' )
			->once()
			->with( Expired_Cache_Purge_Subscriber::EVENT_NAME )
			->andReturnNull(); // No need to run it.

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 45,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);
	}
}
