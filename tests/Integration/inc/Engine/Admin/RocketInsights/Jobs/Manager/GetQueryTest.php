<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Jobs\Manager;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager::get_query
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class GetQueryTest extends TestCase {
	use DBTrait;

	protected static $container;
	private $manager;

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
		$this->manager   = self::$container->get( 'ri_manager' );
	}

	public function testShouldReturnQueryInstance() {
		$query = $this->manager->get_query();

		$this->assertInstanceOf( RocketInsightsQuery::class, $query );
	}

	public function testShouldReturnSameQueryInstance() {
		$query1 = $this->manager->get_query();
		$query2 = $this->manager->get_query();

		// Should return the same instance (not just equal, but identical)
		$this->assertSame( $query1, $query2 );
	}

	public function testShouldAllowQueryOperations() {
		$query = $this->manager->get_query();

		// Test that we can perform query operations
		// Add a test row
		$row_id = self::addPerformanceMonitoring( [
			'url'             => 'http://example.com/test',
			'title'           => 'Test Page',
			'is_mobile'       => 0,
			'job_id'          => 'test-job-id',
			'queue_name'      => 'rocket-performance-monitoring',
			'retries'         => 0,
			'data'            => '{}',
			'status'          => 'pending',
			'score'           => 0,
			'report_url'      => '',
			'error_message'   => '',
			'submitted_at'    => gmdate( 'Y-m-d H:i:s' ),
			'last_accessed'   => gmdate( 'Y-m-d H:i:s' ),
			'modified'        => gmdate( 'Y-m-d H:i:s' ),
			'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
		] );

		// Get the row using the query instance
		$row = $query->get_row_by_id( $row_id );

		$this->assertNotNull( $row );
		$this->assertEquals( 'http://example.com/test', $row->url );
		$this->assertEquals( 'pending', $row->status );

		// Clean up
		self::truncatePerformanceMonitoringTable();
	}
}
