<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Database\Queries\AbstractQuery::get_pending_jobs
 *
 * @group RUCSS
 */
class GetPendingJobsTest extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists() requiring not temporary table.
		self::installUsedCssTable();
	}

	public static function tear_down_after_class() {
		self::uninstallUsedCssTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		self::truncateUsedCssTable();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnOnlyDueRows( $config, $expected ) {
		global $wpdb;

		$query = apply_filters( 'rocket_container', null )->get( 'rucss_used_css_query' );
		$now   = time();

		foreach ( $config['rows'] as $name => $row ) {
			$id = $query->add_item(
				[
					'url'        => 'https://example.org/' . $name,
					'is_mobile'  => false,
					'job_id'     => $row['job_id'],
					'queue_name' => 'rucss',
					'status'     => $row['status'],
				]
			);

			$this->assertNotFalse( $id );

			// BerlinDB stamps `modified` on every update, so force the timestamps last.
			$timestamps = [ 'modified' => gmdate( 'Y-m-d H:i:s', $now + $row['modified_offset'] ) ];

			if ( isset( $row['next_retry_offset'] ) ) {
				$timestamps['next_retry_time'] = gmdate( 'Y-m-d H:i:s', $now + $row['next_retry_offset'] );
			}

			$wpdb->update( $wpdb->prefix . 'wpr_rucss_used_css', $timestamps, [ 'id' => $id ] );
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
