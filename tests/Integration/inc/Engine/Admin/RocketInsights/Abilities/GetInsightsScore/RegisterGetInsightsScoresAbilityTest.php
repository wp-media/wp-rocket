<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\GetInsightsScore;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/get-insights-scores ability registration and output.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class RegisterGetInsightsScoresAbilityTest extends TestCase {
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

		// Skip test if WordPress version is less than 6.9 (Abilities API not available).
		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}

		// Clean up data before each test.
		self::truncatePerformanceMonitoringTable();
		$this->resetGlobalScoreTransient();
	}

	public function tear_down() {
		// Clean up data after each test.
		self::truncatePerformanceMonitoringTable();
		$this->resetGlobalScoreTransient();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedOutput( $config, $expected ) {
		$this->set_up_user( $config['has_permission'] );

		// Add each item individually if provided.
		if ( ! empty( $config['items'] ) ) {
			foreach ( $config['items'] as $item ) {
				self::addPerformanceMonitoring( $item );
			}
		}

		// Get the ability.
		$ability = wp_get_ability( 'wp-rocket/get-insights-scores' );

		$this->assertNotNull( $ability, 'Ability should be registered' );

		// Execute the ability with input.
		$result = $ability->execute( $config['input'] ?? null );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		// Check the summary structure.
		$this->assertArrayHasKey( 'summary', $result );
		$this->assertArrayHasKey( 'global_score', $result['summary'] );
		$this->assertArrayHasKey( 'pages_monitored', $result['summary'] );
		$this->assertArrayHasKey( 'status', $result['summary'] );
		$this->assertArrayHasKey( 'is_running', $result['summary'] );

		// Check the results structure.
		$this->assertArrayHasKey( 'results', $result );

		// Assert expected values.
		$this->assertSame( $expected['summary']['status'], $result['summary']['status'] );
		$this->assertSame( $expected['summary']['pages_monitored'], $result['summary']['pages_monitored'] );
		$this->assertSame( $expected['summary']['is_running'], $result['summary']['is_running'] );

		// Check results count.
		$this->assertCount( $expected['results_count'], $result['results'] );

		// Check if metric_data is present when expected.
		if ( ! empty( $result['results'] ) && isset( $expected['has_metric_data'] ) ) {
			foreach ( $result['results'] as $row ) {
				if ( $expected['has_metric_data'] ) {
					$this->assertObjectHasProperty( 'metric_data', $row );
				} else {
					$this->assertObjectNotHasProperty( 'metric_data', $row );
				}
			}
		}
	}

	private function resetGlobalScoreTransient() {
		$container    = apply_filters( 'rocket_container', null );
		$global_score = $container->get( 'ri_global_score' );
		$global_score->reset();
	}

	/**
	 * Set up user with or without permission.
	 *
	 * @param bool $has_permission Whether user should have permission.
	 *
	 * @return void
	 */
	private function set_up_user( bool $has_permission ): void {
		$admin = get_role( 'administrator' );

		if ( $has_permission ) {
			$admin->add_cap( 'rocket_manage_options' );
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$admin->remove_cap( 'rocket_manage_options' );
			$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		}

		wp_set_current_user( $user_id );
	}
}
