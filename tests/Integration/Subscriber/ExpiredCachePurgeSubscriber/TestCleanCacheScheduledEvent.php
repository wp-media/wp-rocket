<?php
namespace WP_Rocket\Tests\Integration\Subscriber\ExpiredCachePurgeSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @group Subscriber_ScheduledEvent
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

		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => 'HOUR_IN_SECONDS',
			]
		);

		$this->assertTrue( true ); // Prevent "risky" warning.
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

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	public function testShouldCleanScheduledEventWhenMinutesAndOldValueIsHours() {
		Functions\expect( 'wp_clear_scheduled_hook' )->once();

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

		$this->assertTrue( true ); // Prevent "risky" warning.
	}
}