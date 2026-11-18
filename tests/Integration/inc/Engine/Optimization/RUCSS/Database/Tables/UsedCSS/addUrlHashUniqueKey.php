<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSS_Table;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS::add_url_hash_unique_key
 *
 * @group RUCSS
 */
class Test_AddUrlHashUniqueKey extends TestCase {
	private $table;
	private $table_name;

	public function set_up() {
		parent::set_up();

		global $wpdb;

		$this->clearUpgradeState();

		self::installUsedCssTable();

		$this->table      = apply_filters( 'rocket_container', null )->get( 'rucss_usedcss_table' );
		$this->table_name = $wpdb->prefix . 'wpr_rucss_used_css';

		// Recreate the layout of a table that has not been upgraded yet.
		$wpdb->query( "ALTER TABLE {$this->table_name} DROP INDEX url_hash_is_mobile" );
		$wpdb->query( "ALTER TABLE {$this->table_name} DROP COLUMN url_hash" );
	}

	public function tear_down() {
		$this->clearUpgradeState();

		self::uninstallUsedCssTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpgradeTheTableAsExpected( $config, $expected ) {
		global $wpdb;

		if ( $config['column_first'] ) {
			$wpdb->query( "ALTER TABLE {$this->table_name} ADD COLUMN url_hash varchar(32) NULL default NULL" );
		}

		if ( $config['legacy_rows'] ) {
			$this->insertRows( 'http://example.org/legacy', [ null, null ] );
		}

		if ( $config['duplicate_hashes'] ) {
			$this->insertRows( 'http://example.org/dup', [ md5( 'http://example.org/dup' ), md5( 'http://example.org/dup' ) ] );
		}

		if ( $config['lock'] ) {
			$age = 'stale' === $config['lock'] ? 16 * MINUTE_IN_SECONDS : 0;

			$wpdb->insert(
				$wpdb->options,
				[
					'option_name'  => UsedCSS_Table::UPGRADE_LOCK,
					'option_value' => ( time() - $age ) . ':other',
					'autoload'     => 'no',
				]
			);
		}

		if ( $config['throttled'] ) {
			set_transient( UsedCSS_Table::UPGRADE_RETRY, time(), 60 );
		}

		$upgrade = $this->get_reflective_method( 'add_url_hash_unique_key', get_class( $this->table ) );
		$results = [];

		// The duplicate scenario makes the first ADD UNIQUE fail with a logged database error.
		$previous = $wpdb->suppress_errors( true );

		for ( $run = 0; $run < $config['runs']; $run++ ) {
			$results[] = $upgrade->invoke( $this->table );
		}

		$wpdb->suppress_errors( $previous );

		$this->assertSame( $expected['results'], $results );
		$this->assertSame( $expected['column'], $this->table->column_exists( 'url_hash' ) );
		$this->assertSame( $expected['index'], $this->table->index_exists( 'url_hash_is_mobile' ) );
		$this->assertSame( $expected['lock_row'], $this->lockRowExists() );
		$this->assertSame( $expected['throttled'], false !== get_transient( UsedCSS_Table::UPGRADE_RETRY ) );

		if ( $expected['index'] ) {
			$index = $wpdb->get_row( "SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'url_hash_is_mobile'" );

			$this->assertSame( '0', (string) $index->Non_unique );
		}

		if ( null !== $expected['null_hash_rows'] ) {
			$this->assertSame(
				$expected['null_hash_rows'],
				(int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE url_hash IS NULL" )
			);
		}
	}

	/**
	 * Removes the lock and throttle left by a test.
	 *
	 * ALTER TABLE commits implicitly, so what the upgrade writes would survive the rollback of the
	 * test transaction, and the cleanup has to be committed to survive it too.
	 */
	private function clearUpgradeState() {
		global $wpdb;

		$wpdb->delete( $wpdb->options, [ 'option_name' => UsedCSS_Table::UPGRADE_LOCK ] );
		delete_transient( UsedCSS_Table::UPGRADE_RETRY );
		$wpdb->query( 'COMMIT' );
	}

	/**
	 * Inserts one row per given hash, a null hash standing for a row that predates the column.
	 *
	 * @param string $url    Row url.
	 * @param array  $hashes url_hash of each row.
	 */
	private function insertRows( string $url, array $hashes ) {
		global $wpdb;

		foreach ( $hashes as $index => $hash ) {
			$data = [
				'url'       => $url,
				'is_mobile' => 0,
				'status'    => 0 === $index ? 'to-submit' : 'pending',
			];

			if ( null !== $hash ) {
				$data['url_hash'] = $hash;
			}

			$wpdb->insert( $this->table_name, $data );
		}
	}

	private function lockRowExists(): bool {
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", UsedCSS_Table::UPGRADE_LOCK ) );
	}
}
