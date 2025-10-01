<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Tables;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Database\Tables\RocketInsights::remove_all_completed_rows
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_RemoveAllCompletedRows extends TestCase {
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
		self::truncatePerformanceMonitoringTable();
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

		// Verify initial count (bypass cache to avoid stale values).
		$initial_count = $pm_query->query( [
			'number' => 999,
			'count' => true,
		], false );
		$this->assertEquals( count( $config['items'] ), $initial_count );

		// Remove all completed rows.
		$result = $pm_table->remove_all_completed_rows();

		if ( isset( $expected['result'] ) ) {
			if ( $expected['result'] === 'false_or_zero' ) {
				$this->assertTrue( $result === false || $result === 0 );
			} else {
				$this->assertEquals( $expected['result'], $result );
			}
		} else {
			// The method should return the number of deleted rows or false
			$this->assertTrue( is_int( $result ) || $result === false );
		}

		// Only check remaining count if table still exists.
		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			// Verify remaining count (bypass cache to avoid stale values).
			$remaining_count = $pm_query->query( [
				'number' => 999,
				'count' => true,
			], false );
			$this->assertEquals( $expected['remaining_count'], $remaining_count );

			// Verify specific statuses if needed.
			if ( isset( $expected['remaining_statuses'] ) ) {
				// Fetch full rows so we can pluck 'status' reliably
				$remaining_items = $pm_query->query( [
					'number' => 999,
				], false );
				$remaining_statuses = array_map( function( $item ) { return $item->status; }, $remaining_items );
				sort( $remaining_statuses );
				sort( $expected['remaining_statuses'] );
				$this->assertEquals( $expected['remaining_statuses'], $remaining_statuses );
			}
		}

		// Reinstall table if it was uninstalled for this test.
		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			self::installPerformanceMonitoringTable();
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
