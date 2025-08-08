<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\PerformanceMonitoring\Database\Rows;

use WP_Rocket\Engine\PerformanceMonitoring\Database\Rows\PerformanceMonitoring as PerformanceMonitoringRow;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\PerformanceMonitoring\Database\Rows\PerformanceMonitoring::has_result
 *
 * @group PerformanceMonitoring
 */
class Test_HasResult extends TestCase {
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

		// Clear the table before each test.
		$this->truncatePerformanceMonitoringTable();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null );
		$pm_query = $container->get( 'pm_query' );

		// Add test data.
		$item_id = $pm_query->add_item( $config['item'] );

		$this->assertNotFalse( $item_id );

		// Get the row.
		$row = $pm_query->get_item( $item_id );

		$this->assertInstanceOf( PerformanceMonitoringRow::class, $row );

		// Test has_result method.
		$has_result = $row->has_result();

		$this->assertEquals( $expected['has_result'], $has_result );

		// Test property types and values if specified.
		if ( isset( $expected['properties'] ) ) {
			foreach ( $expected['properties'] as $property => $expected_value ) {
				if ( $property === 'id' && $expected_value === 'int' ) {
					$this->assertIsInt( $row->$property );
				} else {
					$this->assertEquals( $expected_value, $row->$property );
				}
			}
		}

		// Test all property types are correct.
		$this->assertIsInt( $row->id );
		$this->assertIsString( $row->url );
		$this->assertIsBool( $row->is_mobile );
		$this->assertIsString( $row->test_id );
		$this->assertIsString( $row->error_message );
		$this->assertIsString( $row->status );
		$this->assertIsString( $row->data );
		$this->assertIsInt( $row->modified );
		$this->assertIsInt( $row->last_accessed );
	}

	private function truncatePerformanceMonitoringTable() {
		$container = apply_filters( 'rocket_container', null );
		$pm_table = $container->get( 'pm_table' );

		if ( $pm_table->exists() ) {
			$pm_table->truncate();
		}
	}

	public static function installPerformanceMonitoringTable() {
		$container = apply_filters( 'rocket_container', null );
		$pm_table = $container->get( 'pm_table' );

		if ( ! $pm_table->exists() ) {
			$pm_table->install();
		}
	}

	public static function uninstallPerformanceMonitoringTable() {
		$container = apply_filters( 'rocket_container', null );
		$pm_table = $container->get( 'pm_table' );

		if ( $pm_table->exists() ) {
			$pm_table->uninstall();
		}
	}
}
