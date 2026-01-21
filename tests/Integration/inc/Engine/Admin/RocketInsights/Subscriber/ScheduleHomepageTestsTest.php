<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::schedule_homepage_tests
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class ScheduleHomepageTestsTest extends TestCase {
	use DBTrait;

	private $hook_fired = false;

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

		// Clean up data before each test
		self::truncatePerformanceMonitoringTable();

		// Enable Performance Monitoring for the test
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Unregister all callbacks except the one we're testing
		$this->unregisterAllCallbacksExcept( 'wp_rocket_first_install', 'schedule_homepage_tests' );

		// Add a hook to capture when rocket_rocket_insights_job_added is fired
		add_action( 'rocket_rocket_insights_job_added', [ $this, 'capture_hook_fired' ] );

		$this->hook_fired = false;
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Remove our test hook
		remove_action( 'rocket_rocket_insights_job_added', [ $this, 'capture_hook_fired' ] );

		// Restore the hook
		$this->restoreWpHook( 'wp_rocket_first_install' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		// Trigger the wp_rocket_first_install hook
		do_action( 'wp_rocket_first_install' );

		// Check if an entry was added to the database
		$container = apply_filters( 'rocket_container', null );
		$ri_query = $container->get( 'ri_query' );

		$items = $ri_query->query( [] );
		$actual_count = count( $items );

		// Assert the expected number of database entries
		$this->assertSame( $expected['database_entries'], $actual_count );

		if ( $expected['database_entries'] > 0 ) {
			// Check if the first item exists
			$item = $items[0];
			$this->assertNotEmpty( $item->url );
		}

		// Assert that the rocket_rocket_insights_job_added hook was fired
		$this->assertSame( $expected['hook_fired'], $this->hook_fired );
	}

	/**
	 * Callback to capture when rocket_rocket_insights_job_added hook is fired.
	 *
	 * @param string $url The URL that was added for monitoring.
	 */
	public function capture_hook_fired( $url ) {
		$this->hook_fired = true;
	}
}
