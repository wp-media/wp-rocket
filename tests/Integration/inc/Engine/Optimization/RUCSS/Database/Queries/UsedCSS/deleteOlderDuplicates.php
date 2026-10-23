<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSS_Table;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS::delete_older_duplicates
 *
 * The table is a permanent one: MySQL rejects a temporary table referenced twice in a self-join.
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

		$this->withPermanentTables(
			function () {
				global $wpdb;

				$wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );
				delete_transient( 'wpr_rucss_used_css_exists' );

				self::installUsedCssTable();
			}
		);

		$this->query = apply_filters( 'rocket_container', null )->get( 'rucss_used_css_query' );
	}

	public function tear_down() {
		global $wpdb;

		$this->withPermanentTables(
			function () {
				global $wpdb;

				self::uninstallUsedCssTable();
				$wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );
			}
		);

		// The table creation committed what ran after it, so the flag set by install() is removed explicitly.
		apply_filters( 'rocket_container', null )->get( 'options_api' )->delete( UsedCSS_Table::DEDUPED_OPTION );
		$wpdb->query( 'COMMIT' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDeleteTheOlderDuplicatesOfTheRange( $config, $expected ) {
		global $wpdb;

		$ids = $this->insertRows( $config['rows'] );

		// Positions are 1-based; 0 stands for "before the first row".
		$id_at = function ( $position ) use ( $ids ) {
			return 0 === $position ? 0 : $ids[ $position - 1 ];
		};

		$this->assertSame( $id_at( $expected['max_id'] ), $this->query->get_max_id() );

		$results = [];

		foreach ( $config['calls'] as $call ) {
			$up_to_id = 'max' === $call['up_to'] ? $this->query->get_max_id() : $id_at( $call['up_to'] );

			$break_delete = function ( $sql ) use ( $call, $ids ) {
				if ( null === $call['broken_delete'] || 0 !== strpos( $sql, 'DELETE FROM' ) ) {
					return $sql;
				}

				$is_broken = 'all' === $call['broken_delete'] || false !== strpos( $sql, "`id` = '{$ids[ $call['broken_delete'] - 1 ]}'" );

				return $is_broken ? 'DELETE FROMM' : $sql;
			};

			add_filter( 'query', $break_delete );
			$previous  = $wpdb->suppress_errors( true );
			$results[] = $this->query->delete_older_duplicates( $id_at( $call['after'] ), $up_to_id, $call['limit'] );
			$wpdb->suppress_errors( $previous );
			remove_filter( 'query', $break_delete );
		}

		$this->assertSame( $expected['results'], $results );
		$this->assertSame(
			array_map( $id_at, $expected['remaining'] ),
			$this->remainingIds()
		);
	}

	/**
	 * Runs a callback with the test suite's temporary-table query filters suspended.
	 *
	 * @param callable $callback Callback.
	 */
	private function withPermanentTables( callable $callback ) {
		remove_filter( 'query', [ $this, '_create_temporary_tables' ] );
		remove_filter( 'query', [ $this, '_drop_temporary_tables' ] );

		$callback();

		add_filter( 'query', [ $this, '_create_temporary_tables' ] );
		add_filter( 'query', [ $this, '_drop_temporary_tables' ] );
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
