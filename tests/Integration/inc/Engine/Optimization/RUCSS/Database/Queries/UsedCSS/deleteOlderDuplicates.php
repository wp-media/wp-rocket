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

	public function testShouldKeepOnlyTheNewestRowOfAnExactTriplicate() {
		$ids = $this->insertRows( [ 'http://example.org/a', 'http://example.org/a', 'http://example.org/a' ] );

		$this->assertSame( 2, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 100 ) );
		$this->assertSame( [ $ids[2] ], $this->remainingIds() );
	}

	public function testShouldTreatCaseVariantsAsDuplicates() {
		$ids = $this->insertRows( [ 'http://example.org/Page', 'http://example.org/page' ] );

		$this->assertSame( 1, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 100 ) );
		$this->assertSame( [ $ids[1] ], $this->remainingIds() );
	}

	public function testShouldKeepDesktopAndMobileRows() {
		$ids = $this->insertRows( [ 'http://example.org/a', 'http://example.org/a' ], [ 0, 1 ] );

		$this->assertSame( 0, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 100 ) );
		$this->assertSame( $ids, $this->remainingIds() );
	}

	public function testShouldKeepUrlsSharingALongPrefix() {
		$prefix = 'http://example.org/' . str_repeat( 'a', 160 );
		$ids    = $this->insertRows( [ $prefix . '-one', $prefix . '-two' ] );

		$this->assertSame( 0, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 100 ) );
		$this->assertSame( $ids, $this->remainingIds() );
	}

	public function testShouldDeleteNoMoreRowsThanTheLimit() {
		$urls = [];
		foreach ( range( 1, 5 ) as $number ) {
			$urls[] = "http://example.org/{$number}";
			$urls[] = "http://example.org/{$number}";
		}
		$this->insertRows( $urls );

		$this->assertSame( 3, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 3 ) );
		$this->assertCount( 7, $this->remainingIds() );

		$this->assertSame( 2, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 3 ) );
		$this->assertCount( 5, $this->remainingIds() );
	}

	public function testShouldOnlyScanTheGivenIdRange() {
		$ids = $this->insertRows( [ 'http://example.org/a', 'http://example.org/b', 'http://example.org/a', 'http://example.org/b' ] );

		// Rows 2 and 3 only: the older "b" (row 2) goes, the older "a" (row 1) is out of range.
		$this->assertSame( 1, $this->query->delete_older_duplicates( $ids[0], $ids[2], 100 ) );
		$this->assertSame( [ $ids[0], $ids[2], $ids[3] ], $this->remainingIds() );
	}

	public function testShouldReturnFalseWhenNoRowOfTheBatchCanBeDeleted() {
		global $wpdb;

		$ids = $this->insertRows( [ 'http://example.org/a', 'http://example.org/a' ] );

		$break_deletes = function ( $sql ) {
			return 0 === strpos( $sql, 'DELETE FROM' ) ? 'DELETE FROMM' : $sql;
		};

		add_filter( 'query', $break_deletes );
		$previous = $wpdb->suppress_errors( true );
		$result   = $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 100 );
		$wpdb->suppress_errors( $previous );
		remove_filter( 'query', $break_deletes );

		$this->assertFalse( $result );
		$this->assertSame( $ids, $this->remainingIds() );
	}

	public function testShouldReturnZeroOnACleanTable() {
		$this->insertRows( [ 'http://example.org/a', 'http://example.org/b' ] );

		$this->assertSame( 0, $this->query->delete_older_duplicates( 0, $this->query->get_max_id(), 100 ) );
		$this->assertCount( 2, $this->remainingIds() );
	}

	public function testShouldReturnTheHighestId() {
		$this->assertSame( 0, $this->query->get_max_id() );

		$ids = $this->insertRows( [ 'http://example.org/a', 'http://example.org/b' ] );

		$this->assertSame( $ids[1], $this->query->get_max_id() );
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
	 * @param array $urls      Urls, one per row.
	 * @param array $is_mobile is_mobile flag per row.
	 *
	 * @return int[] Inserted ids.
	 */
	private function insertRows( array $urls, array $is_mobile = [] ): array {
		global $wpdb;

		$ids = [];

		foreach ( $urls as $index => $url ) {
			$wpdb->insert(
				$this->table_name,
				[
					'url'       => $url,
					'is_mobile' => $is_mobile[ $index ] ?? 0,
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
