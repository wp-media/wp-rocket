<?php

namespace WP_Rocket\Engine\Common\Database\Tables;

use WP_Rocket\Dependencies\BerlinDB\Database\Table;
use WP_Rocket\Engine\Common\Database\TableInterface;

class AbstractTable extends Table implements TableInterface {

	/**
	 * Table schema data.
	 *
	 * @var   string
	 */
	protected $schema_data;

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
		if ( ! $this->schema_data ) {
			return;
		}

		$this->schema = $this->schema_data;
	}

	/**
	 * Delete all rows which were not accessed in the last month.
	 *
	 * @return bool|int
	 */
	public function delete_old_rows() {
		if ( ! $this->exists() ) {
			return false;
		}
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		/**
		 * Filters the old SaaS data deletion interval
		 *
		 * @param int $delete_interval Old Saas data deletion interval in months
		 */
		$delete_interval = (int) rocket_apply_filter_and_deprecated(
			'rocket_saas_delete_interval',
			[ 1 ],
			'3.16',
			'rocket_rucss_delete_interval'
		);

		if ( $delete_interval <= 0 ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "DELETE FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval $delete_interval month)";
		$rows_affected       = $db->query( $query );

		return $rows_affected;
	}

	/**
	 * Get all rows which were not accessed in the last month.
	 *
	 * @return array
	 */
	public function get_old_rows(): array {
		if ( ! $this->exists() ) {
			return [];
		}
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return [];
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "SELECT * FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval 1 month)";
		$rows_affected       = $db->get_results( $query );

		return $rows_affected;
	}

	/**
	 * Remove all completed rows.
	 *
	 * @return bool|int
	 */
	public function remove_all_completed_rows() {
		if ( ! $this->exists() ) {
			return false;
		}
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
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
}
