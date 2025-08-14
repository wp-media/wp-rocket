<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring::update_test_id
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_UpdateTestId extends TestCase {
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

		$db_id = null;

		// Create initial record if needed.
		if ( isset( $config['initial_record'] ) ) {
			$db_id = $pm_query->add_item( $config['initial_record'] );
			$this->assertNotFalse( $db_id );
		}

		// Update test ID.
		$result = $pm_query->update_test_id( 
			$config['db_id'] ?? $db_id, 
			$config['test_id'], 
			$config['status'] ?? 'running'
		);

		// Verify the return value.
		if ( isset( $expected['result'] ) ) {
			if ( $expected['result'] === true ) {
				$this->assertTrue( $result );
			} elseif ( $expected['result'] === false ) {
				$this->assertFalse( $result );
			}
		}

		// Only check database content if table exists and we have a valid db_id.
		if ( ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) && null !== $db_id && true === $result ) {
			// Verify the record was updated in the database.
			$updated_record = $pm_query->get_item( $db_id );
			$this->assertNotFalse( $updated_record );

			// Verify test_id was updated.
			$this->assertEquals( $config['test_id'], $updated_record->test_id );

			// Verify status was updated.
			$expected_status = $config['status'] ?? 'running';
			$this->assertEquals( $expected_status, $updated_record->status );

			// Verify modified timestamp was updated.
			$this->assertNotEmpty( $updated_record->modified );
			$this->assertNotFalse( $updated_record->modified, 'Modified timestamp should be a valid datetime' );

			// Verify other fields remain unchanged.
			if ( isset( $config['initial_record'] ) ) {
				$this->assertEquals( $config['initial_record']['url'], $updated_record->url );
				$this->assertEquals( $config['initial_record']['is_mobile'], $updated_record->is_mobile );
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
			$pm_table->uninstall();
		}
	}
}
