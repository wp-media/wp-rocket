<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS::delete_older_duplicates
 *
 * @group RUCSS
 */
class Test_DeleteOlderDuplicates extends TestCase {
	private $query;
	private $table_name;

	public function set_up() {
		parent::set_up();

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'wpr_rucss_used_css';

		self::installUsedCssTable();

		$this->query = apply_filters( 'rocket_container', null )->get( 'rucss_used_css_query' );
	}

	public function tear_down() {
		self::uninstallUsedCssTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDeleteTheOlderDuplicates( $config, $expected ) {
		global $wpdb;

		$ids     = $this->insertRows( $config['rows'] );
		$results = [];

		foreach ( $config['calls'] as $call ) {
			$break_delete = function ( $sql ) use ( $call, $ids ) {
				if ( null === $call['broken_delete'] || 0 !== strpos( $sql, 'DELETE FROM' ) ) {
					return $sql;
				}

				$is_broken = 'all' === $call['broken_delete'] || false !== strpos( $sql, "`id` = '{$ids[ $call['broken_delete'] - 1 ]}'" );

				return $is_broken ? 'DELETE FROMM' : $sql;
			};

			add_filter( 'query', $break_delete );
			$previous  = $wpdb->suppress_errors( true );
			$results[] = $this->query->delete_older_duplicates( $call['limit'] );
			$wpdb->suppress_errors( $previous );
			remove_filter( 'query', $break_delete );
		}

		$this->assertSame( $expected['results'], $results );
		$this->assertSame(
			array_map(
				function ( $position ) use ( $ids ) {
					return $ids[ $position - 1 ];
				},
				$expected['remaining']
			),
			$this->remainingIds()
		);
	}

	/**
	 * Inserts rows without a url_hash, like the rows that predate the unique key.
	 *
	 * @param array $rows [ url, is_mobile ] pairs, one per row.
	 *
	 * @return int[] Inserted ids.
	 */
	private function insertRows( array $rows ): array {
		global $wpdb;

		$ids = [];

		foreach ( $rows as list( $url, $is_mobile ) ) {
			$wpdb->insert(
				$this->table_name,
				[
					'url'       => $url,
					'is_mobile' => $is_mobile,
					'status'    => 'to-submit',
				]
			);
			$ids[] = (int) $wpdb->insert_id;
		}

		return $ids;
	}

	private function remainingIds(): array {
		global $wpdb;

		return array_map( 'intval', $wpdb->get_col( "SELECT id FROM {$this->table_name} ORDER BY id" ) );
	}
}
