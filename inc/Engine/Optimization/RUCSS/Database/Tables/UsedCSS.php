<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Tables;

use WP_Rocket\Dependencies\Database\Table;

/**
 * RUCSS UsedCSS Table.
 */
class UsedCSS extends Table {

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
	protected $version = 20220513;


	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [
		20220121 => 'add_async_rucss_columns',
		20220131 => 'make_status_column_index',
		20220513 => 'add_hash_column',
	];

	/**
	 * Setup the database schema
	 *
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "
			id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			url              varchar(2000)       NOT NULL default '',
			css              longtext                     default NULL,
			hash             varchar(32)                  default '',
			unprocessedcss   longtext                NULL,
			retries          tinyint(1)          NOT NULL default 1,
			is_mobile        tinyint(1)          NOT NULL default 0,
			job_id           varchar(255)        NOT NULL default '',
			queue_name       varchar(255)        NOT NULL default '',
			status           varchar(255)        NOT NULL default '',
			modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY url (url(150), is_mobile),
			KEY modified (modified),
			KEY last_accessed (last_accessed),
			INDEX `queue_name_index` (`queue_name`),
			KEY hash (hash)";
	}

	/**
	 * Delete all used_css which were not accessed in the last month.
	 *
	 * @return bool|int
	 */
	public function delete_old_used_css() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "DELETE FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval 1 month)";
		$rows_affected       = $db->query( $query );

		return $rows_affected;
	}

	/**
	 * Get all used_css which were not accessed in the last month.
	 *
	 * @return array
	 */
	public function get_old_used_css() : array {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "SELECT * FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval 1 month)";
		$rows_affected       = $db->get_results( $query );

		return $rows_affected;
	}

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
	 * Remove all completed rows.
	 *
	 * @return bool|int
	 */
	public function remove_all_completed_rows() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		return $db->query( "DELETE FROM `$prefixed_table_name` WHERE status IN ( 'failed', 'completed' )" );
	}

}
