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
	protected $version = 20231031;

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
	];

	/**
	 * Instantiate class.
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_init', [ $this, 'maybe_trigger_recreate_table' ], 9 );
		add_action( 'init',  [ $this, 'maybe_upgrade' ] );
	}

	/**
	 * Setup the database schema
	 *
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "
			id               		bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			url              		varchar(2000)       NOT NULL default '',
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
			KEY modified (modified),
			KEY last_accessed (last_accessed),
			INDEX `status_index` (`status`(191)),
			INDEX `error_code_index` (`error_code`(32)),
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

		/**
		 * Filters the old RUCSS deletion interval
		 *
		 * @param int $delete_interval Old RUCSS deletion interval in months
		 */
		$delete_interval = (int) apply_filters( 'rocket_rucss_delete_interval', 1 );

		if ( $delete_interval <= 0 ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "DELETE FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval $delete_interval month)";
		$rows_affected       = $db->query( $query );

		return $rows_affected;
	}

	/**
	 * Get all used_css which were not accessed in the last month.
	 *
	 * @return array
	 */
	public function get_old_used_css(): array {
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

	/**
	 * Returns name from table.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->apply_prefix( $this->table_name );
	}

	/**
	 * Trigger recreation of cache table if not exist.
	 *
	 * @return void
	 */
	public function maybe_trigger_recreate_table() {
		if ( $this->exists() ) {
			return;
		}

		delete_option( $this->db_version_key );
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
}
