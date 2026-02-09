<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights::make_status_completed
 * with metric_data
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class MakeStatusCompletedWithMetricsTest extends TestCase {
	use DBTrait;

	protected static $container;
	private $query;
	private $table;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();
		self::truncatePerformanceMonitoringTable();

		self::$container = apply_filters( 'rocket_container', null );
		$this->query     = self::$container->get( 'ri_query' );
		$this->table     = self::$container->get( 'ri_table' );
		
		// Force table upgrade to ensure metric_data column exists.
		$this->table->maybe_upgrade();
	}

	public function tear_down() {
		self::truncatePerformanceMonitoringTable();
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSaveMetricDataCorrectly( $config, $expected ) {
		// Create initial row.
		$row_id = self::addPerformanceMonitoring( $config['initial_data'] );

		// Update with completed status and metric data.
		$result = $this->query->make_status_completed(
			$row_id,
			$config['status'],
			$config['test_data']
		);

		$this->assertTrue( $result );

		// Retrieve the updated row.
		$row = $this->query->get_row_by_id( $row_id );

		$this->assertEquals( $expected['status'], $row->status );
		$this->assertEquals( $expected['score'], $row->score );
		$this->assertEquals( $expected['report_url'], $row->report_url );

		if ( null === $expected['metric_data'] ) {
			$this->assertNull( $row->metric_data );
		} else {
			$this->assertNotNull( $row->metric_data );
			// metric_data is already decoded by the Row constructor.
			$this->assertEquals( $expected['metric_data'], $row->metric_data );
		}
	}
}
