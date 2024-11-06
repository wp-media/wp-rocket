<?php

namespace WP_Rocket\Engine\Common\PerformanceHints\Database\Table;

use WP_Rocket\Dependencies\BerlinDB\Database\Table;

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
	 * Truncate DB table.
	 *
	 * @return bool
	 */
	public function truncate_table(): bool {
		if ( ! $this->exists() ) {
			return false;
		}

		return $this->truncate();
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
