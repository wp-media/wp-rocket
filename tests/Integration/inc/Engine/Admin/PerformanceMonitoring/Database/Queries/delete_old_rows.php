<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring::delete_old_rows
 *
 * @group PerformanceMonitoring AdminOnly
 */
class Test_DeleteOldRows extends TestCase {
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

		// Handle special case where table should be uninstalled.
		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			self::uninstallPerformanceMonitoringTable();
		}

		// Add test data.
		$item_ids = [];
		foreach ( $config['items'] as $item ) {
			$item_id = $pm_query->add_item( $item );
			$this->assertNotFalse( $item_id );
			$item_ids[] = $item_id;
		}

		// Handle timestamp updates for deleteOldAccessedRows test.
		if ( isset( $config['update_old_timestamp'] ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpr_performance_monitoring';
			$update_config = $config['update_old_timestamp'];
			$old_timestamp = strtotime( $update_config['last_accessed'] );
			
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table_name} SET last_accessed = %s WHERE test_id = %s",
					gmdate( 'Y-m-d H:i:s', $old_timestamp ),
					$update_config['test_id']
				)
			);
		}

		// Verify initial count.
		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			$initial_count = $pm_query->query( [
				'number' => 999,
				'count' => true,
			], false );
			$this->assertEquals( count( $config['items'] ), $initial_count );
		}

		// Delete old rows.
		$deleted_count = $pm_query->delete_old_rows();

		// Handle special expectations.
		if ( isset( $expected['result'] ) && $expected['result'] === 'false_or_zero' ) {
			$this->assertTrue( $deleted_count === false || $deleted_count === 0 );
		} elseif ( isset( $expected['deleted_count'] ) ) {
			if ( is_string( $expected['deleted_count'] ) ) {
				// Handle operators like '>= 0', '> 0', etc.
				if ( strpos( $expected['deleted_count'], '>= ' ) === 0 ) {
					$min_value = (int) substr( $expected['deleted_count'], 3 );
					$this->assertGreaterThanOrEqual( $min_value, $deleted_count );
				} elseif ( strpos( $expected['deleted_count'], '> ' ) === 0 ) {
					$min_value = (int) substr( $expected['deleted_count'], 2 );
					$this->assertGreaterThan( $min_value, $deleted_count );
				}
			} else {
				$this->assertEquals( $expected['deleted_count'], $deleted_count );
			}
		}

		// Only check remaining count if table still exists.
		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			if ( isset( $expected['remaining_count'] ) ) {
				$remaining_count = $pm_query->query( [
					'number' => 999,
					'count' => true,
				], false );
				
				if ( is_string( $expected['remaining_count'] ) ) {
					// Handle operators.
					if ( strpos( $expected['remaining_count'], '<= ' ) === 0 ) {
						$max_value = (int) substr( $expected['remaining_count'], 3 );
						$this->assertLessThanOrEqual( $max_value, $remaining_count );
					}
				} else {
					$this->assertEquals( $expected['remaining_count'], $remaining_count );
				}
			}

			// Verify specific URLs remaining.
			if ( isset( $expected['remaining_urls'] ) ) {
				$remaining_items = $pm_query->query( [
					'number' => 999,
					'fields' => 'url',
				], false );
				$remaining_urls = array_column( $remaining_items, 'url' );
				sort( $remaining_urls );
				sort( $expected['remaining_urls'] );
				$this->assertEquals( $expected['remaining_urls'], $remaining_urls );
			}

			// Verify specific test_id remaining.
			if ( isset( $expected['remaining_test_id'] ) ) {
				$remaining_items = $pm_query->query( [
					'number' => 999,
					'fields' => 'test_id',
				], false );
				$remaining_test_ids = array_column( $remaining_items, 'test_id' );
				$this->assertContains( $expected['remaining_test_id'], $remaining_test_ids );
			}

			// Verify no failed status remains.
			if ( isset( $expected['no_failed_status'] ) && $expected['no_failed_status'] ) {
				$remaining_items = $pm_query->query( [
					'number' => 999,
				], false );
				$failed_items = array_filter( $remaining_items, function( $item ) {
					return $item->status === 'failed';
				});
				$this->assertCount( 0, $failed_items, 'No failed rows should remain' );
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
