<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring::update_test_data
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_UpdateTestData extends TestCase {
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

		// Update test data.
		$result = $pm_query->update_test_data( 
			$config['db_id'] ?? $db_id, 
			$config['status'], 
			$config['test_data']
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

			// Verify status was updated.
			$this->assertEquals( $config['status'], $updated_record->status );

			// Verify data was updated and properly JSON encoded.
			$this->assertNotEmpty( $updated_record->data );
			$decoded_data = json_decode( $updated_record->data, true );
			$this->assertNotNull( $decoded_data, 'Data should be valid JSON' );
			$this->assertEquals( $config['test_data'], $decoded_data );

			// Verify modified timestamp was updated.
			$this->assertNotEmpty( $updated_record->modified );
			$this->assertNotFalse(  $updated_record->modified, 'Modified timestamp should be a valid datetime' );

			// Verify other fields remain unchanged.
			if ( isset( $config['initial_record'] ) ) {
				$this->assertEquals( $config['initial_record']['url'], $updated_record->url );
				$this->assertEquals( $config['initial_record']['is_mobile'], $updated_record->is_mobile );
				if ( isset( $config['initial_record']['test_id'] ) ) {
					$this->assertEquals( $config['initial_record']['test_id'], $updated_record->test_id );
				}
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
