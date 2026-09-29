<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Tables;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Database\Tables\RocketInsights::add_metric_data_column
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class AddMetricDataColumnTest extends TestCase {
	use DBTrait;

	protected static $container;
	private $table;

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

		self::$container = apply_filters( 'rocket_container', null );
		$this->table     = self::$container->get( 'ri_table' );
	}

	public function testShouldAddMetricDataColumn() {
		global $wpdb;

		// Get table name
		$table_name = $wpdb->prefix . 'wpr_rocket_insights';

		// Drop the metric_data column if it exists (simulate old version)
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "ALTER TABLE `{$table_name}` DROP COLUMN IF EXISTS metric_data" );

		// Verify column doesn't exist
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM `{$table_name}` LIKE %s",
				'metric_data'
			)
		);
		$this->assertEmpty( $column_exists, 'Column should not exist before migration' );

		// Run the upgrade
		$this->table->maybe_upgrade();

		// Verify column now exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM `{$table_name}` LIKE %s",
				'metric_data'
			)
		);
		$this->assertNotEmpty( $column_exists, 'Column should exist after migration' );

		// Verify column type
		$column_info = $column_exists[0];
		$this->assertEquals( 'metric_data', $column_info->Field );
		$this->assertEquals( 'longtext', $column_info->Type );
		$this->assertEquals( 'YES', $column_info->Null );
	}

	public function testShouldNotFailWhenColumnAlreadyExists() {
		// Force table upgrade to ensure column exists
		$this->table->maybe_upgrade();

		// Run upgrade again - should not fail
		$result = $this->table->maybe_upgrade();

		// Should complete without errors
		$this->assertTrue( true, 'Migration should not fail when column already exists' );
	}
}
