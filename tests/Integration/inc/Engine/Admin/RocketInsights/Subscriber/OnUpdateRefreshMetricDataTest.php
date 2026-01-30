<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::on_update_refresh_metric_data
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class OnUpdateRefreshMetricDataTest extends TestCase {
	use DBTrait;

	protected static $container;
	private $subscriber;
	private $query;

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
		self::truncatePerformanceMonitoringTable();

		self::$container = apply_filters( 'rocket_container', null );
		$this->subscriber = self::$container->get( 'ri_subscriber' );
		$this->query      = self::$container->get( 'ri_query' );
	}

	public function tear_down() {
		self::truncatePerformanceMonitoringTable();
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehavAsExpected( $config, $expected ) {
		// Add test data to database.
		foreach ( $config['database_entries'] as $entry ) {
			self::addPerformanceMonitoring( $entry );
		}

		// Call the upgrade callback.
		$this->subscriber->on_update_refresh_metric_data( $config['new_version'], $config['old_version'] );

		// Get all rows and check their status.
		$rows = $this->query->query( [] );

		$this->assertCount( $expected['row_count'], $rows );

		foreach ( $rows as $index => $row ) {
			$this->assertEquals( $expected['statuses'][ $index ], $row->status );
		}
	}
}
