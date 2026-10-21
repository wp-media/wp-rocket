<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

use ReflectionMethod;
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

	public function testShouldAddColumnAndKeyAndKeepLegacyRows() {
		$this->insertLegacyDuplicates();

		$this->assertTrue( $this->upgrade() );

		$this->assertTrue( $this->table->column_exists( 'url_hash' ) );
		$this->assertTrue( $this->table->index_exists( 'url_hash_is_mobile' ) );
		$this->assertSame( '0', $this->unique_flag() );
		$this->assertSame( 2, $this->countRows( 'url_hash IS NULL' ) );
		$this->assertFalse( $this->lockRowExists() );
		$this->assertFalse( get_transient( UsedCSS_Table::UPGRADE_RETRY ) );
	}

	public function testShouldBeIdempotent() {
		$this->assertTrue( $this->upgrade() );
		$this->assertTrue( $this->upgrade() );
		$this->assertTrue( $this->table->index_exists( 'url_hash_is_mobile' ) );
	}

	public function testShouldReturnFalseWithoutThrottleWhenLockIsHeld() {
		global $wpdb;

		$wpdb->insert(
			$wpdb->options,
			[
				'option_name'  => UsedCSS_Table::UPGRADE_LOCK,
				'option_value' => time() . ':other',
				'autoload'     => 'no',
			]
		);

		$this->assertFalse( $this->upgrade() );

		$this->assertFalse( $this->table->column_exists( 'url_hash' ) );
		$this->assertFalse( get_transient( UsedCSS_Table::UPGRADE_RETRY ) );
		$this->assertTrue( $this->lockRowExists() );
	}

	public function testShouldReclaimStaleLock() {
		global $wpdb;

		$wpdb->insert(
			$wpdb->options,
			[
				'option_name'  => UsedCSS_Table::UPGRADE_LOCK,
				'option_value' => ( time() - 16 * MINUTE_IN_SECONDS ) . ':gone',
				'autoload'     => 'no',
			]
		);

		$this->assertTrue( $this->upgrade() );

		$this->assertTrue( $this->table->index_exists( 'url_hash_is_mobile' ) );
		$this->assertFalse( $this->lockRowExists() );
	}

	public function testShouldNullDuplicateHashesAndRetryTheKey() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE {$this->table_name} ADD COLUMN url_hash varchar(32) NULL default NULL" );

		foreach ( [ 'to-submit', 'pending' ] as $status ) {
			$wpdb->insert(
				$this->table_name,
				[
					'url'       => 'http://example.org/dup',
					'url_hash'  => md5( 'http://example.org/dup' ),
					'is_mobile' => 0,
					'status'    => $status,
				]
			);
		}

		$previous = $wpdb->suppress_errors( true );
		$result   = $this->upgrade();
		$wpdb->suppress_errors( $previous );

		$this->assertTrue( $result );
		$this->assertTrue( $this->table->index_exists( 'url_hash_is_mobile' ) );
		$this->assertSame( 2, $this->countRows( 'url_hash IS NULL' ) );
		$this->assertFalse( get_transient( UsedCSS_Table::UPGRADE_RETRY ) );
	}

	public function testShouldDoNothingWhileThrottled() {
		set_transient( UsedCSS_Table::UPGRADE_RETRY, time(), 60 );

		$this->assertFalse( $this->upgrade() );
		$this->assertFalse( $this->table->column_exists( 'url_hash' ) );
		$this->assertFalse( $this->lockRowExists() );
	}

	private function upgrade() {
		$method = new ReflectionMethod( $this->table, 'add_url_hash_unique_key' );
		$method->setAccessible( true );

		return $method->invoke( $this->table );
	}

	private function insertLegacyDuplicates() {
		global $wpdb;

		foreach ( [ 'to-submit', 'pending' ] as $status ) {
			$wpdb->insert(
				$this->table_name,
				[
					'url'       => 'http://example.org/legacy',
					'is_mobile' => 0,
					'status'    => $status,
				]
			);
		}
	}

	private function countRows( string $where ): int {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}" );
	}

	private function unique_flag(): string {
		global $wpdb;

		$rows = $wpdb->get_results( "SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'url_hash_is_mobile'" );

		return (string) $rows[0]->Non_unique;
	}

	private function lockRowExists(): bool {
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", UsedCSS_Table::UPGRADE_LOCK ) );
	}
}
