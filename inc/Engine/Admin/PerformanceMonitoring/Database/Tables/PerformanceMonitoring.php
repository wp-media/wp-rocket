<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Tables;

use WP_Rocket\Engine\Common\Database\Tables\AbstractTable;

class PerformanceMonitoring extends AbstractTable {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $name = 'wpr_performance_monitoring';

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_performance_monitoring_version';

	/**
	 * Database version
	 *
	 * @var int
	 */
	protected $version = 20250808;

	/**
	 * Table schema data.
	 *
	 * @var   string
	 */
	protected $schema_data = "
		id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		url              varchar(2000)       NOT NULL default '',
		is_mobile        tinyint(1)          NOT NULL default 0,
		test_id          varchar(255)        NOT NULL default '',
		error_message    longtext                     default NULL,
		status           varchar(255)                 default NULL,
		data             longtext            NOT NULL default '',
		modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
		last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY url (url(100)),
		KEY is_mobile (is_mobile),
		KEY test_id (test_id),
		KEY status (status)";

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

		$prefixed_table_name = $this->get_name();
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
