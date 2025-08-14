<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring::delete_old_tests
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_DeleteOldTests extends TestCase {
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

		// Add test data with modified timestamps (for existing items, timestamp will be current).
		$item_ids = [];
		if ( isset( $config['items'] ) ) {
			foreach ( $config['items'] as $item ) {
				$item_id = $pm_query->add_item( $item );
				$this->assertNotFalse( $item_id );
				$item_ids[] = $item_id;
			}
		}

		// Verify initial count.
		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			$initial_count = $pm_query->query( [
				'number' => 999,
				'count' => true,
			], false );
			if ( isset( $config['items'] ) ) {
				$this->assertEquals( count( $config['items'] ), $initial_count );
			}
		}

		// Delete old tests.
		$deleted_count = $pm_query->delete_old_tests( $config['days'] );

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
					} elseif ( strpos( $expected['remaining_count'], '>= ' ) === 0 ) {
						$min_value = (int) substr( $expected['remaining_count'], 3 );
						$this->assertGreaterThanOrEqual( $min_value, $remaining_count );
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

			// Verify specific test_ids remaining.
			if ( isset( $expected['remaining_test_ids'] ) ) {
				$remaining_items = $pm_query->query( [
					'number' => 999,
				], false );
				$remaining_test_ids = [];
				foreach ( $remaining_items as $item ) {
					if ( ! empty( $item->test_id ) ) {
						$remaining_test_ids[] = $item->test_id;
					}
				}
				sort( $remaining_test_ids );
				sort( $expected['remaining_test_ids'] );
				$this->assertEquals( $expected['remaining_test_ids'], $remaining_test_ids );
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
}
