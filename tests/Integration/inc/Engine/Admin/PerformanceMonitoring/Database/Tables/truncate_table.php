<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Tables;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Tables\PerformanceMonitoring::truncate_table
 *
 * @group PerformanceMonitoring
 */
class Test_TruncateTable extends TestCase {
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
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null );
		$pm_query = $container->get( 'pm_query' );
		$pm_table = $container->get( 'pm_table' );

		// Handle special case where table should be uninstalled.
		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			self::uninstallPerformanceMonitoringTable();
		}

		// Add test data.
		foreach ( $config['items'] as $item ) {
			$item_id = $pm_query->add_item( $item );
			$this->assertNotFalse( $item_id );
		}

		// Check for table name test.
		if ( isset( $expected['table_name_contains'] ) ) {
			$table_name = $pm_table->get_name();
			$this->assertStringContainsString( $expected['table_name_contains'], $table_name );
			return;
		}

		// Verify initial count.
		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			$initial_count = $pm_query->query( [
				'number' => 999,
				'count' => true,
			], false );
			$this->assertEquals( count( $config['items'] ), $initial_count );
		}

		// Truncate table.
		$result = $pm_table->truncate_table();

		if ( isset( $expected['result'] ) ) {
			if ( $expected['result'] === 'boolean' ) {
				$this->assertIsBool( $result );
			} else {
				$this->assertEquals( $expected['result'], $result );
			}
		}

		// Only check remaining count if table still exists and result was true.
		if ( ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) && $result === true ) {
			if ( isset( $expected['remaining_count'] ) ) {
				$remaining_count = $pm_query->query( [
					'number' => 999,
					'count' => true,
				], false );
				$this->assertEquals( $expected['remaining_count'], $remaining_count );
			}
		}

		// Reinstall table if it was uninstalled for this test.
		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			self::installPerformanceMonitoringTable();
		}
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

		if ( $pm_table && $pm_table->exists() ) {
			global $wpdb;
			$prev = $wpdb->suppress_errors();
			$wpdb->suppress_errors( true );
			$pm_table->uninstall();
			$wpdb->suppress_errors( $prev );
		}
	}
}
