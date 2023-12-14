<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Tables;

use WP_Rocket\Dependencies\Database\Table;

class AboveTheFold extends Table {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $name = 'wpr_above_the_fold';

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_above_the_fold_version';

	/**
	 * Database version
	 *
	 * @var int
	 */
	protected $version = 20231006;

	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [];

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
			id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			url              varchar(2000)       NOT NULL default '',
			is_mobile        tinyint(1)          NOT NULL default 0,
			lcp              longtext                     default '',
			viewport         longtext                     default '',
			error_code       varchar(32)             NULL default NULL,
			error_message    longtext                NULL default NULL,
			retries          tinyint(1)          NOT NULL default 1,
			job_id           varchar(255)        NOT NULL default '',
			queue_name       varchar(255)        NOT NULL default '',
			status           varchar(255)        NOT NULL default '',
			submitted_at     timestamp           NOT NULL default '0000-00-00 00:00:00',
			modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY url (url(150), is_mobile),
			KEY modified (modified),
			KEY last_accessed (last_accessed),
			INDEX `status_index` (`status`(191)),
			INDEX `error_code_index` (`error_code`(32))";
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
		if ( empty( $db ) ) {
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
	public function truncate(): bool {
		if ( ! $this->exists() ) {
			return false;
		}
		return $this->truncate();
	}
}
