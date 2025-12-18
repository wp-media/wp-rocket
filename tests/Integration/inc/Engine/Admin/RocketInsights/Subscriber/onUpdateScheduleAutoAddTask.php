<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Subscriber::on_update_schedule_auto_add_task
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_OnUpdateScheduleAutoAddTask extends TestCase {
	use DBTrait;

	protected $subscriber;

	public function set_up() {
		parent::set_up();

		$this->installPerformanceMonitoringTable();

		$this->subscriber = $this->container->get( 'ri_subscriber' ); // @phpstan-ignore-line

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'on_update_schedule_auto_add_task', 10 );
	}

	public function tear_down() {
		$this->truncatePerformanceMonitoringTable();
		$this->restoreWpHook( 'wp_rocket_upgrade' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveLikeExpected( $config, $expected ) {
		// Set up options.
		foreach ( $config['options'] as $key => $value ) {
			update_option( $key, $value ); // @phpstan-ignore-line
		}

		// Set up license data.
		if ( isset( $config['license_data'] ) ) {
			update_option( 'wp_rocket_settings', [ 'consumer_key' => 'test_key' ] ); // @phpstan-ignore-line
			set_transient( 'wp_rocket_customer_data', $config['license_data'] );
		}

		// Add existing URLs if specified.
		if ( ! empty( $config['existing_urls'] ) ) {
			foreach ( $config['existing_urls'] as $url_data ) {
				$this->subscriber->add_homepage( 'manual' );
			}
		}

		// Trigger the upgrade hook.
		do_action( 'wp_rocket_upgrade', $config['new_version'], $config['old_version'] );

		// Check if cron was scheduled.
		$scheduled = as_next_scheduled_action( 'rocket_insights_auto_add_homepage' );

		if ( $expected['scheduled'] ) {
			$this->assertNotFalse( $scheduled, 'Cron should be scheduled' );
		} else {
			$this->assertFalse( $scheduled, 'Cron should not be scheduled' );
		}
	}
}
