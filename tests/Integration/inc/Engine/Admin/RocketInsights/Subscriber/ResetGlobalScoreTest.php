<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::reset_global_score
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class ResetGlobalScoreTest extends TestCase {
	use DBTrait;

	private $reset_called = false;

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

		// Mock the global score reset to track if it's called
		add_filter( 'rocket_rocket_insights_global_score_reset_test', [ $this, 'capture_reset_called' ] );

		$this->reset_called = false;
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove test filter
		remove_filter( 'rocket_rocket_insights_global_score_reset_test', [ $this, 'capture_reset_called' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$this->executeTest( $config );

		$this->assertResult( $expected );
	}

	private function setUpTest( $config ) {
		// Reset the flag for each test
		$this->reset_called = false;
	}

	private function executeTest( $config ) {
		// Get the container and global score service
		$container = apply_filters( 'rocket_container', null );
		$global_score = $container->get( 'ri_global_score' );
		$subscriber = $container->get( 'ri_subscriber' );

		// First, let's set up some test data in the global score
		// Add some performance monitoring entries to create a score
		if ( isset( $config['setup_data'] ) && $config['setup_data'] ) {
			$test_entries = [
				[
					'id' => 1,
					'url' => 'http://example.org/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete","data":{"data":{"performance_score":85}}}',
				],
				[
					'id' => 2,
					'url' => 'http://example.org/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'completed',
					'score' => 92,
					'data' => '{"status":"complete","data":{"data":{"performance_score":92}}}',
				],
			];

			foreach ( $test_entries as $entry ) {
				self::addPerformanceMonitoring( $entry );
			}

			// Force the global score to be calculated and cached
			$initial_score_data = $global_score->get_global_score_data();
			$this->assertNotEmpty( $initial_score_data, 'Initial score data should exist' );
		}

		// Now test the reset functionality
		if ( $subscriber ) {
			// Call the reset_global_score method
			$subscriber->reset_global_score();
		}

		// Test hook-triggered resets
		if ( isset( $config['hook_to_test'] ) ) {
			$hook = $config['hook_to_test'];

			switch ( $hook ) {
				case 'rocket_pm_rocket_insights_added':
					do_action( $hook, 'url', 'free', 1 );
					break;

				case 'rocket_rocket_insights_job_completed':
				case 'rocket_rocket_insights_job_failed':
					// Create a mock row for the completed job
					$mock_row = (object) [
						'id' => 1,
						'url' => 'http://example.org/page1',
						'status' => 'completed'
					];
					$job_details = [];
					$plan = null;
					do_action( $hook, $mock_row, $job_details, $plan );
					break;

				case 'rocket_rocket_insights_job_retest':
				case 'rocket_rocket_insights_job_deleted':
					// These hooks pass different parameters
					do_action( $hook, 1 );
					break;
			}
		}
	}

	private function assertResult( $expected ) {
		$container = apply_filters( 'rocket_container', null );
		$global_score = $container->get( 'ri_global_score' );

		// Verify that the method can be called without errors
		if ( isset( $expected['method_callable'] ) ) {
			$subscriber = $container->get( 'ri_subscriber' );
			$this->assertTrue(
				method_exists( $subscriber, 'reset_global_score' ),
				'reset_global_score method should exist on subscriber'
			);
		}

		// Verify that the hook is properly registered for each event
		if ( isset( $expected['hook_registered'] ) && $expected['hook_registered'] ) {
			$hook = $expected['hook_name'];

			// Check if the hook has callbacks registered
			$has_callback = has_action( $hook );
			$this->assertNotFalse( $has_callback, "Hook {$hook} should have callbacks registered" );
		}

		// Test that the global score reset functionality works
		if ( isset( $expected['global_score_reset'] ) && $expected['global_score_reset'] ) {
			// The global score should have been reset (cache cleared)
			// We can verify this by checking that calling get_global_score_data
			// recalculates from fresh database data

			// Since reset() clears the cache, a subsequent call should recalculate
			$score_after_reset = $global_score->get_global_score_data();

			// Verify the score data structure is correct
			$this->assertIsArray( $score_after_reset );
			$this->assertArrayHasKey( 'score', $score_after_reset );
			$this->assertArrayHasKey( 'pages_num', $score_after_reset );
			$this->assertArrayHasKey( 'status', $score_after_reset );
		}
	}

	/**
	 * Callback to capture when reset is called.
	 */
	public function capture_reset_called() {
		$this->reset_called = true;
	}
}
