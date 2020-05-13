<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Expired_Cache_Purge_Subscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber::clean_expired_cache_scheduled_event
 * @group  Subscriber_ScheduledEvent
 */
class Test_CleanExpiredCacheScheduledEvent extends TestCase {

	public function testShouldNotCleanScheduledEventWhenValuesAreTheSame() {
		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);
	}

	public function testShouldNotCleanScheduledEventWhenChangedValueFromHoursToDays() {
		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'DAY_IN_SECONDS',
			]
		);
	}

	public function testShouldCleanScheduledEventWhenMinutesAndOldValueIsHours() {
		Functions\expect( 'wp_clear_scheduled_hook' )
			->once()
			->with( Expired_Cache_Purge_Subscriber::EVENT_NAME )
			->andReturnNull(); // No need to run it.

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'DAY_IN_SECONDS',
			]
		);

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);
	}

	public function testShouldNotCleanScheduledEventWhenUnitIsMinutesAndIntervalIsNotChanged() {
		Functions\expect( 'wp_clear_scheduled_hook' )->never();

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 20,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 20,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);
	}

	public function testShouldCleanScheduledEventWhenUnitIsMinutesAndIntervalIsChanged() {
		Functions\expect( 'wp_clear_scheduled_hook' )
			->once()
			->with( Expired_Cache_Purge_Subscriber::EVENT_NAME )
			->andReturnNull(); // No need to run it.

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 20,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 45,
				'purge_cron_unit'     => 'MINUTE_IN_SECONDS',
			]
		);
	}
}
