<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Queries\RocketInsights;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights::get_completed_metrics
 *
 * @group RocketInsights
 * @group GlobalMetrics
 * @group Database
 */
class Test_GetCompletedMetrics extends TestCase {
	use DBTrait;

	/**
	 * Query instance.
	 *
	 * @var Query
	 */
	private $query;

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

		$container   = apply_filters( 'rocket_container', null );
		$this->query = $container->get( 'ri_query' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Insert test data using Query::add_item()
		foreach ( $config['rows'] as $row ) {
			$this->query->add_item( $row );
		}

		// Execute query
		$result = $this->query->get_completed_metrics();

		// Assert count
		$this->assertCount( $expected['count'], $result );

		// If we expect results, validate structure
		if ( $expected['count'] > 0 ) {
			foreach ( $result as $metric_data ) {
				// Each result should be a JSON string (the metric_data column)
				$this->assertIsString( $metric_data );

				// Verify it's valid JSON
				$decoded = json_decode( $metric_data, true );
				$this->assertIsArray( $decoded );

				// Verify expected metric keys exist
				foreach ( $expected['expected_keys'] as $key ) {
					$this->assertArrayHasKey( $key, $decoded );
				}
			}
		}
	}
}
