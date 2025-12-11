<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\GlobalScore;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\GlobalScore::get_global_score_data
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class GetGlobalScoreDataTest extends TestCase {
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

		// Clean up data and cache before each test
		self::truncatePerformanceMonitoringTable();
		$this->resetGlobalScoreTransient();
	}

	public function tear_down() {
		// Clean up data and cache after each test
		self::truncatePerformanceMonitoringTable();
		$this->resetGlobalScoreTransient();
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		// Add each item individually
		foreach ( $config['items'] as $item ) {
			self::addPerformanceMonitoring( $item );
		}

		$container = apply_filters( 'rocket_container', null );
		$global_score = $container->get( 'ri_global_score' );

		$actual = $global_score->get_global_score_data();

		$this->assertEquals( $expected['data'], $actual );
	}

	private function resetGlobalScoreTransient() {
		$container = apply_filters( 'rocket_container', null );
		$global_score = $container->get( 'ri_global_score' );
		$global_score->reset();
	}
}
