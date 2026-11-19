<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\GetPageInsightsScore;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/get-page-insights-score ability registration and output.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class RegisterGetPageInsightsScoreAbilityTest extends TestCase {
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
	}

	public function tear_down() {
		// Clean up data after each test.
		self::truncatePerformanceMonitoringTable();

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
		$ability = wp_get_ability( 'wp-rocket/get-page-insights-score' );

		$this->assertNotNull( $ability, 'Ability should be registered' );

		// Execute the ability with input.
		$result = $ability->execute( $config['input'] );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		$this->assertArrayHasKey( 'exists', $result );

		if ( $expected['exists'] ) {
			$this->assertTrue( $result['exists'] );
			$this->assertArrayHasKey( 'results', $result );
			$this->assertCount( $expected['results_count'], $result['results'] );

			if ( ! empty( $result['results'] ) ) {
				$first = $result['results'][0];
				$this->assertArrayHasKey( 'url', $first );
				$this->assertArrayHasKey( 'score', $first );
				$this->assertArrayHasKey( 'status', $first );
				$this->assertArrayHasKey( 'modified', $first );
				$this->assertArrayHasKey( 'report_url', $first );
				$this->assertArrayHasKey( 'metric_data', $first );
				$this->assertArrayHasKey( 'is_mobile', $first );
				$this->assertArrayHasKey( 'title', $first );
			}
		} else {
			$this->assertFalse( $result['exists'] );
			$this->assertArrayHasKey( 'free_slots', $result );
			$this->assertIsInt( $result['free_slots'] );
			$this->assertGreaterThanOrEqual( 0, $result['free_slots'] );
		}
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
