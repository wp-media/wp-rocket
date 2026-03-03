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
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		global $wpdb;

		// Insert test data
		foreach ( $config['rows'] as $row ) {
			$wpdb->insert(
				$wpdb->prefix . 'wpr_rocket_insights',
				[
					'url'         => $row['url'],
					'status'      => $row['status'],
					'metric_data' => $row['metric_data'],
					'score'       => $row['score'],
				]
			);
		}

		// Execute query
		$result = $this->query->get_completed_metrics();

		// Assert count
		$this->assertCount( $expected['count'], $result );

		// Assert each result is a JSON string
		foreach ( $result as $metric_data ) {
			$this->assertIsString( $metric_data );

			// Verify it's valid JSON
			$decoded = json_decode( $metric_data, true );
			$this->assertIsArray( $decoded );
			$this->assertArrayHasKey( 'largest_contentful_paint', $decoded );
		}
	}
}
