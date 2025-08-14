<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Queue\Processor;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Processor::process_test_initiation
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_ProcessTestInitiation extends TestCase {
	use DBTrait;

	private $container;
	private $pm_query;

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

		// @phpstan-ignore-next-line
		$this->container = apply_filters( 'rocket_container', null );
		$this->pm_query = $this->container->get( 'pm_query' );
		$this->truncatePerformanceMonitoringTable();
	}

	public function tear_down() {
		$this->truncatePerformanceMonitoringTable();
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		// Count records before
		$records_before = $this->pm_query->query( [] );
		$count_before = is_array( $records_before ) ? count( $records_before ) : 0;

		// Get processor from container (it has real dependencies)
		$processor = $this->container->get( 'pm_processor' );

		// Execute the method
		$result = $processor->process_test_initiation( $config['url'], $config['options'] );

		// Count records after
		$records_after = $this->pm_query->query( [] );
		$count_after = is_array( $records_after ) ? count( $records_after ) : 0;

		// Assert database record creation
		if ( $expected['should_create_record'] ) {
			$this->assertSame( $count_before + 1, $count_after, 'Database record should be created' );

			// Verify the record was created with correct data
			$created_record = end( $records_after );
			$this->assertSame( $config['url'], $created_record->url );
		} else {
			$this->assertSame( $count_before, $count_after, 'No database record should be created' );
		}
	}

	private function truncatePerformanceMonitoringTable() {
		$pm_table = $this->container->get( 'pm_table' );

		if ( $pm_table->exists() ) {
			$pm_table->truncate();
		}
	}
}
