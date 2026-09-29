<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights::update_completed_tests_to_pending
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class UpdateCompletedTestsToPendingTest extends TestCase {
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

		// Force table upgrade to ensure metric_data column exists
		$this->table->maybe_upgrade();
	}

	public function tear_down() {
		self::truncatePerformanceMonitoringTable();
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpdateCompletedTests( $config, $expected ) {
		// Add test data
		foreach ( $config['database_entries'] as $entry ) {
			self::addPerformanceMonitoring( $entry );
		}

		// Run the method
		$result = $this->query->update_completed_tests_to_pending();

		// Verify result
		$this->assertEquals( $expected['updated_count'], $result );

		// Get all rows and verify their status
		$rows = $this->query->query( [] );

		$this->assertCount( $expected['total_rows'], $rows );

		// Check status of each row
		foreach ( $rows as $index => $row ) {
			$this->assertEquals(
				$expected['row_statuses'][ $index ],
				$row->status,
				"Row {$index} should have status {$expected['row_statuses'][$index]}"
			);
		}
	}

	public function testShouldReturnZeroWhenNoCompletedTests() {
		// Add only non-completed tests
		self::addPerformanceMonitoring( [
			'url'             => 'http://example.com/test-1',
			'title'           => 'Test 1',
			'is_mobile'       => 0,
			'job_id'          => 'job-1',
			'queue_name'      => 'rocket-performance-monitoring',
			'retries'         => 0,
			'data'            => '{}',
			'status'          => 'pending',
			'score'           => 0,
			'report_url'      => '',
			'error_message'   => '',
			'submitted_at'    => gmdate( 'Y-m-d H:i:s' ),
			'last_accessed'   => gmdate( 'Y-m-d H:i:s' ),
			'modified'        => gmdate( 'Y-m-d H:i:s' ),
			'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
		] );

		$result = $this->query->update_completed_tests_to_pending();

		$this->assertEquals( 0, $result );
	}
}
