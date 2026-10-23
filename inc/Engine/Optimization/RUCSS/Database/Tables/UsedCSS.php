<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Tables;

use WP_Rocket\Engine\Common\Database\Tables\AbstractTable;

/**
 * RUCSS UsedCSS Table.
 *
 * Fresh installs place `url_hash` after `url`; upgraded tables append it at the end (no `AFTER`,
 * so `ADD COLUMN` can be INSTANT). The column order differs between the two and is not relied on.
 */
class UsedCSS extends AbstractTable {

	/**
	 * Options row used as the in-flight lock of the url_hash upgrade.
	 */
	const UPGRADE_LOCK = 'wpr_rucss_used_css_upgrade_lock';

	/**
	 * Transient throttling the url_hash upgrade after a failure.
	 */
	const UPGRADE_RETRY = 'wpr_rucss_used_css_upgrade_retry';

	/**
	 * Option (unprefixed, read through the options API) set once the legacy duplicates are removed.
	 */
	const DEDUPED_OPTION = 'rucss_used_css_deduped';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $name = 'wpr_rucss_used_css';

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_rucss_used_css_version';

	/**
	 * Database version
	 *
	 * @var int
	 */
	protected $version = 20261021;

	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [
		20220121 => 'add_async_rucss_columns',
		20220131 => 'make_status_column_index',
		20220513 => 'add_hash_column',
		20220920 => 'make_status_column_index_instead_queue_name',
		20221104 => 'add_error_columns',
		20231010 => 'add_submitted_at_column',
		20231031 => 'add_next_retry_time_column',
		20261021 => 'add_url_hash_unique_key',
	];

	/**
	 * Table schema data.
	 *
	 * @var   string
	 */
	protected $schema_data = "
				id               		bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				url              		varchar(2000)       NOT NULL default '',
				url_hash         		varchar(32)             NULL default NULL,
				css              		longtext                     default NULL,
				hash             		varchar(32)                  default '',
				error_code       		varchar(32)             NULL default NULL,
				error_message    		longtext                NULL default NULL,
				unprocessedcss   		longtext                NULL,
				retries          		tinyint(1)          NOT NULL default 1,
				is_mobile        		tinyint(1)          NOT NULL default 0,
				job_id           		varchar(255)        NOT NULL default '',
				queue_name       		varchar(255)        NOT NULL default '',
				status           		varchar(255)        NOT NULL default '',
				modified         		timestamp           NOT NULL default '0000-00-00 00:00:00',
				last_accessed    		timestamp           NOT NULL default '0000-00-00 00:00:00',
				submitted_at     		timestamp           NULL,
				next_retry_time     	timestamp           NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (id),
				KEY url (url(150), is_mobile),
				UNIQUE KEY url_hash_is_mobile (url_hash, is_mobile),
				KEY modified (modified),
				KEY last_accessed (last_accessed),
				INDEX `status_index` (`status`(191)),
				INDEX `error_code_index` (`error_code`(32)),
				KEY hash (hash)";

	/**
	 * Add queue columns.
	 *
	 * @return bool
	 */
	protected function add_async_rucss_columns() {
		$jobid_column_exists     = $this->column_exists( 'job_id' );
		$queuename_column_exists = $this->column_exists( 'queue_name' );
		$status_column_exists    = $this->column_exists( 'status' );

		$created = true;

		if ( ! $jobid_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN job_id VARCHAR(255) NULL default '' AFTER is_mobile " );
		}
		if ( ! $queuename_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN queue_name VARCHAR(255) NULL default '' AFTER job_id " );
		}
		if ( ! $status_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN status VARCHAR(255) NULL default '' AFTER queue_name " );
		}

		return $this->is_success( $created );
	}

	/**
	 * Make status column as index.
	 *
	 * @return bool
	 */
	protected function make_status_column_index() {
		$queuename_column_exists = $this->column_exists( 'queue_name' );
		if ( ! $queuename_column_exists ) {
			return $this->is_success( false );
		}

		if ( $this->index_exists( 'queue_name_index' ) ) {
			return $this->is_success( true );
		}

		$index_added = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX `queue_name_index` (`queue_name`) " );
		return $this->is_success( $index_added );
	}

	/**
	 * Add hash column and index
	 *
	 * @return bool
	 */
	protected function add_hash_column() {
		$hash_column_exists = $this->column_exists( 'hash' );

		$created = true;

		if ( ! $hash_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN hash VARCHAR(32) NULL default '' AFTER css, ADD KEY hash (hash) " );
		}

		return $this->is_success( $created );
	}

	/**
	 * Make status column as index.
	 *
	 * @return bool
	 */
	protected function make_status_column_index_instead_queue_name() {
		$queuename_column_exists = $this->column_exists( 'status' );
		if ( ! $queuename_column_exists ) {
			return $this->is_success( false );
		}

		if ( $this->index_exists( 'status_index' ) ) {
			return $this->is_success( true );
		}

		if ( $this->index_exists( 'queue_name_index' ) ) {
			if ( ! $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX `queue_name_index`" ) ) {
				return $this->is_success( false );
			}
		}

		$index_added = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX `status_index` (`status`(191)) " );

		return $this->is_success( $index_added );
	}

	/**
	 * Add error columns
	 *
	 * @return bool
	 */
	protected function add_error_columns() {
		return $this->add_error_message_column() && $this->add_error_code_column() && $this->make_error_code_column_index();
	}

	/**
	 * Add error_message column and index
	 *
	 * @return bool
	 */
	private function add_error_message_column() {
		$error_message_column_exists = $this->column_exists( 'error_message' );

		$created = true;

		if ( ! $error_message_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE `{$this->table_name}` ADD COLUMN error_message longtext NULL default NULL AFTER hash" );
		}

		return $this->is_success( $created );
	}

	/**
	 * Add error_code column and index
	 *
	 * @return bool
	 */
	private function add_error_code_column() {
		$error_code_column_exists = $this->column_exists( 'error_code' );

		$created = true;

		if ( ! $error_code_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE `{$this->table_name}` ADD COLUMN error_code VARCHAR(32) NULL default NULL AFTER hash" );
		}

		return $this->is_success( $created );
	}

	/**
	 * Make status column as index.
	 *
	 * @return bool
	 */
	private function make_error_code_column_index() {
		$error_code_column_exists = $this->column_exists( 'error_code' );
		if ( ! $error_code_column_exists ) {
			return $this->is_success( false );
		}

		if ( $this->index_exists( 'error_code_index' ) ) {
			return $this->is_success( true );
		}

		$index_added = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX `error_code_index` (`error_code`) " );
		return $this->is_success( $index_added );
	}

	/**
	 * Adds the submitted_at column
	 *
	 * @return bool
	 */
	protected function add_submitted_at_column() {
		$submitted_at_column_exists = $this->column_exists( 'submitted_at' );

		$created = true;

		if ( ! $submitted_at_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE `{$this->table_name}` ADD COLUMN submitted_at timestamp NULL AFTER last_accessed" );
		}

		return $this->is_success( $created );
	}

	/**
	 * Adds the next_retry_time column
	 *
	 * @return bool
	 */
	protected function add_next_retry_time_column() {
		$next_retry_time_exists = $this->column_exists( 'next_retry_time' );

		$created = true;

		if ( ! $next_retry_time_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE `{$this->table_name}` ADD COLUMN next_retry_time timestamp NOT NULL default '0000-00-00 00:00:00' AFTER submitted_at" );
		}

		return $this->is_success( $created );
	}

	/**
	 * Installs the table, and flags it as already deduplicated since it starts empty.
	 *
	 * @return void
	 */
	public function install() {
		parent::install();

		if ( $this->needs_upgrade() ) {
			return;
		}

		add_option( 'wp_rocket_' . self::DEDUPED_OPTION, 1 );
	}

	/**
	 * Adds the url_hash column and its unique key with (url_hash, is_mobile).
	 *
	 * @return bool
	 */
	protected function add_url_hash_unique_key() {
		if ( get_transient( self::UPGRADE_RETRY ) ) {
			return false;
		}

		$lock = $this->acquire_upgrade_lock();

		// Another worker is upgrading: wait for it without throttling.
		if ( '' === $lock ) {
			return false;
		}

		try {
			$success = $this->add_url_hash_column_and_key();
		} finally {
			$this->release_upgrade_lock( $lock );
		}

		if ( ! $success ) {
			set_transient( self::UPGRADE_RETRY, time(), HOUR_IN_SECONDS );
		}

		return $this->is_success( $success );
	}

	/**
	 * Runs the guarded DDL of the url_hash upgrade, so it is safe to re-run.
	 *
	 * @return bool
	 */
	private function add_url_hash_column_and_key(): bool {
		$db = $this->get_db();

		if ( ! $this->column_exists( 'url_hash' ) && false === $db->query( "ALTER TABLE {$this->table_name} ADD COLUMN url_hash varchar(32) NULL default NULL" ) ) {
			return false;
		}

		if ( $this->index_exists( 'url_hash_is_mobile' ) ) {
			return true;
		}

		if ( $this->add_unique_key() ) {
			return true;
		}

		// Duplicate hashes can only come from inserts made between the ADD COLUMN and the ADD UNIQUE.
		$duplicate = $db->get_var( "SELECT url_hash FROM {$this->table_name} WHERE url_hash IS NOT NULL GROUP BY url_hash, is_mobile HAVING COUNT(*) > 1 LIMIT 1" );

		if ( empty( $duplicate ) ) {
			return false;
		}

		// Existing rows stay reachable through get_row(), so they do not need a hash.
		if ( false === $db->query( "UPDATE {$this->table_name} SET url_hash = NULL WHERE url_hash IS NOT NULL" ) ) {
			return false;
		}

		return $this->add_unique_key();
	}

	/**
	 * Adds the (url_hash, is_mobile) unique key.
	 *
	 * @return bool
	 */
	private function add_unique_key(): bool {
		$added = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD UNIQUE KEY url_hash_is_mobile (url_hash, is_mobile)" );

		return $this->is_success( $added );
	}

	/**
	 * Atomically takes the in-flight upgrade lock, or reclaims it when its holder is gone.
	 *
	 * Mirrors WP_Upgrader::create_lock(): add_option() is not atomic, so the row is inserted with raw SQL.
	 *
	 * @return string Lock value acting as the owner token, empty string when the lock is held.
	 */
	private function acquire_upgrade_lock(): string {
		$db    = $this->get_db();
		$now   = time();
		$token = $now . ':' . wp_generate_password( 12, false );

		if ( 1 === (int) $db->query( $db->prepare( "INSERT IGNORE INTO `{$db->options}` (option_name, option_value, autoload) VALUES (%s, %s, 'no')", self::UPGRADE_LOCK, $token ) ) ) {
			return $token;
		}

		$current = $db->get_var( $db->prepare( "SELECT option_value FROM `{$db->options}` WHERE option_name = %s", self::UPGRADE_LOCK ) );

		// 15 minutes cover a slow ALTER on MySQL 5.7.
		if ( ! is_string( $current ) || (int) $current > $now - 15 * MINUTE_IN_SECONDS ) {
			return '';
		}

		// Compare-and-set on the stale value: only one reclaimer gets a row affected.
		if ( 1 === (int) $db->query( $db->prepare( "UPDATE `{$db->options}` SET option_value = %s WHERE option_name = %s AND option_value = %s", $token, self::UPGRADE_LOCK, $current ) ) ) {
			return $token;
		}

		return '';
	}

	/**
	 * Releases the upgrade lock, only when it is still owned by the given token.
	 *
	 * @param string $token Lock value returned by acquire_upgrade_lock().
	 *
	 * @return void
	 */
	private function release_upgrade_lock( string $token ): void {
		$db = $this->get_db();

		$db->query( $db->prepare( "DELETE FROM `{$db->options}` WHERE option_name = %s AND option_value = %s", self::UPGRADE_LOCK, $token ) );
	}
}
