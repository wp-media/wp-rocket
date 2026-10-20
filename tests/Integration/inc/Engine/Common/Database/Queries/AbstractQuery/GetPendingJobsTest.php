<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\Database\Queries\AbstractQuery;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Database\Queries\AbstractQuery::get_pending_jobs
 *
 * @group RUCSS
 * @group RocketInsights
 */
class GetPendingJobsTest extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists() requiring not temporary table.
		self::installUsedCssTable();
		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallUsedCssTable();
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		self::truncateUsedCssTable();
		self::truncatePerformanceMonitoringTable();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnOnlyDueRows( $config, $expected ) {
		global $wpdb;

		$query = apply_filters( 'rocket_container', null )->get( $config['query'] );
		$now   = time();

		foreach ( $config['rows'] as $name => $row ) {
			$id = $query->add_item(
				[
					'url'        => 'https://example.org/' . $name,
					'is_mobile'  => false,
					'job_id'     => $row['job_id'],
					'queue_name' => $config['queue_name'],
					'status'     => $row['status'],
				]
			);

			$this->assertNotFalse( $id );

			// BerlinDB stamps `modified` on every update, so force the timestamps last.
			$timestamps = [ 'modified' => gmdate( 'Y-m-d H:i:s', $now + $row['modified_offset'] ) ];

			if ( isset( $row['next_retry_offset'] ) ) {
				$timestamps['next_retry_time'] = gmdate( 'Y-m-d H:i:s', $now + $row['next_retry_offset'] );
			}

			$this->assertNotFalse( $wpdb->update( $wpdb->prefix . $config['table'], $timestamps, [ 'id' => $id ] ) );
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
