<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Queries;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Database\Queries\AbstractQuery::get_pending_jobs
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class GetPendingJobsTest extends TestCase {
	use DBTrait;

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
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnOnlyDueRows( $config, $expected ) {
		global $wpdb;

		$query = apply_filters( 'rocket_container', null )->get( 'ri_query' );
		$now   = time();

		foreach ( $config['rows'] as $name => $row ) {
			$id = self::addPerformanceMonitoring(
				[
					'url'        => 'https://example.org/' . $name,
					'is_mobile'  => false,
					'job_id'     => $row['job_id'],
					'queue_name' => 'rocket_insights',
					'status'     => $row['status'],
				]
			);

			$this->assertNotFalse( $id );

			// BerlinDB stamps `modified` on every update, so force the timestamps last.
			$timestamps = [ 'modified' => gmdate( 'Y-m-d H:i:s', $now + $row['modified_offset'] ) ];

			if ( isset( $row['next_retry_offset'] ) ) {
				$timestamps['next_retry_time'] = gmdate( 'Y-m-d H:i:s', $now + $row['next_retry_offset'] );
			}

			$wpdb->update( $wpdb->prefix . 'wpr_performance_monitoring', $timestamps, [ 'id' => $id ] );
		}

		$urls = array_column( $query->get_pending_jobs( $config['count'] ), 'url' );

		$this->assertSame(
			array_map(
				static function ( $name ) {
					return 'https://example.org/' . $name;
				},
				$expected
			),
			$urls
		);
	}
}
