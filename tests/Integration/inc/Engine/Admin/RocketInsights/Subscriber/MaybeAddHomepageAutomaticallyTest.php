<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::maybe_add_homepage_automatically
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class MaybeAddHomepageAutomaticallyTest extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install the Performance Monitoring table.
		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		// Clean up data before each test.
		self::truncatePerformanceMonitoringTable();

		// Enable Performance Monitoring for the test.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );
	}

	public function tear_down() {
		// Clean up data after each test.
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Remove any test filters.
		remove_all_filters( 'rocket_insights_add_homepage_expiry_interval' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null );
		$ri_query  = $container->get( 'ri_query' );

		// Setup: Handle Rocket Insights enabled/disabled state.
		$ri_enabled = $config['ri_enabled'] ?? true;
		if ( ! $ri_enabled ) {
			remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );
			add_filter( 'rocket_rocket_insights_enabled', '__return_false' );
		}

		// Setup: Add existing URLs if specified.
		if ( $config['existing_urls'] > 0 ) {
			for ( $i = 0; $i < $config['existing_urls']; $i++ ) {
				$ri_query->add_item(
					[
						'url'       => 'https://example.com/page-' . $i,
						'is_mobile' => 0,
					]
				);
			}
		}

		// Setup: Mock license expiration.
		add_filter(
			'pre_get_rocket_option_consumer_key',
			function() {
				return 'test_key';
			}
		);

		add_filter(
			'pre_transient_wp_rocket_customer_data',
			function() use ( $config ) {
				return (object) [
					'licence_expiration' => $config['license_expiration'],
					'auto_renew'         => false, // Auto-renew disabled for testing.
				];
			}
		);

		// Setup: Configure the interval filter.
		if ( isset( $config['interval'] ) ) {
			add_filter(
				'rocket_insights_add_homepage_expiry_interval',
				function() use ( $config ) {
					return $config['interval'];
				}
			);
		}

		// Execute: Trigger the cron callback.
		do_action( 'rocket_insights_auto_add_homepage' );

		// Assert: Check database entries.
		$items        = $ri_query->query( [] );
		$actual_count = count( $items );

		$this->assertSame( $expected['database_entries'], $actual_count, 'Database entry count mismatch' );

		// Assert: Check if homepage was added.
		if ( $expected['homepage_added'] ) {
			$homepage_url = home_url( '/' );
			$found        = false;

			foreach ( $items as $item ) {
				if ( $item->url === $homepage_url ) {
					$found = true;
					break;
				}
			}

			$this->assertTrue( $found, 'Homepage should be added to database' );
		}
	}
}
