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

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->installRocketInsightsTable();
		$this->query = new Query();
	}

	/**
	 * Tear down test fixtures.
	 */
	public function tear_down() {
		$this->truncateRocketInsightsTable();
		parent::tear_down();
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
