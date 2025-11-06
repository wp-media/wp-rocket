<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights::delete_old_rows
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class DeleteOldRowsTest extends TestCase {
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
		$ri_query = $container->get( 'ri_query' );

		// Add test data.
		$item_ids = [];
		foreach ( $config['items'] as $item ) {
			$item_id = $ri_query->add_item( $item );
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
					"UPDATE {$table_name} SET last_accessed = %s WHERE job_id = %s",
					gmdate( 'Y-m-d H:i:s', $old_timestamp ),
					$update_config['job_id']
				)
			);
		}

		// Verify initial count.
		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			$initial_count = $ri_query->query( [
				'number' => 999,
				'count' => true,
			], false );
			$this->assertEquals( count( $config['items'] ), $initial_count );
		}

		// Delete old rows.
		$deleted_count = $ri_query->delete_old_rows();

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
				$remaining_count = $ri_query->query( [
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
				$remaining_items = $ri_query->query( [
					'number' => 999,
					'fields' => 'url',
				], false );
				$remaining_urls = array_column( $remaining_items, 'url' );
				sort( $remaining_urls );
				sort( $expected['remaining_urls'] );
				$this->assertEquals( $expected['remaining_urls'], $remaining_urls );
			}

			// Verify specific job_id remaining.
			if ( isset( $expected['remaining_job_id'] ) ) {
				$remaining_items = $ri_query->query( [
					'number' => 999,
					'fields' => 'job_id',
				], false );
				$remaining_job_ids = array_column( $remaining_items, 'job_id' );
				$this->assertContains( $expected['remaining_job_id'], $remaining_job_ids );
			}

			// Verify no failed status remains.
			if ( isset( $expected['no_failed_status'] ) && $expected['no_failed_status'] ) {
				$remaining_items = $ri_query->query( [
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
}
