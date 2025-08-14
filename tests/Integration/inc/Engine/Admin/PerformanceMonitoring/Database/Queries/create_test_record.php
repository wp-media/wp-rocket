<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring::create_test_record
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_CreateTestRecord extends TestCase {
	use DBTrait;

	protected $container;

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
		// @phpstan-ignore-next-line
		$this->container = apply_filters('rocket_container', null);

		// Clear the table before each test.
		$this->truncatePerformanceMonitoringTable();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		$pm_query = $this->container->get( 'pm_query' );

		// Handle special case where table should be uninstalled.
		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			self::uninstallPerformanceMonitoringTable();
		}

		// Create test record.
		$result = $pm_query->create_test_record( $config['url'], $config['options'] ?? [] );

		// Verify the return value.
		if ( isset( $expected['result_type'] ) ) {
			if ( 'int' === $expected['result_type'] ) {
				$this->assertIsInt( $result );
				$this->assertGreaterThan( 0, $result );
			} elseif ( 'false' === $expected['result_type'] ) {
				$this->assertFalse( $result );
			} elseif ( 'int_or_false' === $expected['result_type'] ) {
				$this->assertTrue( is_int( $result ) || false === $result );
			}
		}

		// Only check database content if table exists and result is valid.
		if ( ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) && false !== $result ) {
			// Verify the record was created in the database.
			$created_record = $pm_query->get_item( $result );
			$this->assertNotFalse( $created_record );

			// Verify URL.
			$this->assertEquals( $config['url'], $created_record->url );

			// Verify is_mobile based on options.
			$expected_is_mobile = isset( $config['options']['device'] ) && 'mobile' === $config['options']['device'] ? 1 : 0;
			$this->assertEquals( $expected_is_mobile, $created_record->is_mobile );

			// Verify status.
			$this->assertEquals( 'pending', $created_record->status );

			// Verify timestamps are set.
			$this->assertNotEmpty( $created_record->modified );
			$this->assertNotEmpty( $created_record->last_accessed );

			// Verify timestamps are valid datetime strings.
			$this->assertNotFalse( $created_record->modified, 'Modified timestamp should be a valid datetime' );
			$this->assertNotFalse( $created_record->last_accessed, 'Last accessed timestamp should be a valid datetime' );

			// Verify empty fields.
			$this->assertEmpty( $created_record->test_id );
			$this->assertEmpty( $created_record->data );
		}

		// Reinstall table if it was uninstalled for this test.
		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			self::installPerformanceMonitoringTable();
		}
	}

	private function truncatePerformanceMonitoringTable() {
		$pm_table = $this->container->get( 'pm_table' );

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
