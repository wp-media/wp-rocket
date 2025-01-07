<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Database\Tables;

use WP_Rocket\Dependencies\BerlinDB\Database\Table;

class Cache extends Table {

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'rocket_preload_activation', [ $this, 'maybe_upgrade' ] );
		add_action( 'init', [ $this, 'maybe_upgrade' ] );
		add_action( 'admin_init',  [ $this, 'maybe_trigger_recreate_table' ], 9 );
	}

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $name = 'wpr_rocket_cache';

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_rocket_cache_version';

	/**
	 * Database version
	 *
	 * @var int
	 */
	protected $version = 20220927;

	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [
		20220927 => 'add_is_locked_column',
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
			status           varchar(255)        NOT NULL default '',
			modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
			is_locked        tinyint(1)          NOT NULL default 0,
			PRIMARY KEY (id),
			KEY url (url(191)),
			KEY modified (modified),
			KEY last_accessed (last_accessed)";
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
	 * Add is_locked column.
	 *
	 * @return bool
	 */
	public function add_is_locked_column() {
		$hash_column_exists = $this->column_exists( 'is_locked' );

		$created = true;

		if ( ! $hash_column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN is_locked tinyint(1) NOT NULL default 0 AFTER last_accessed" );
		}

		return $this->is_success( $created );
	}

	/**
	 * Truncate cache table.
	 *
	 * @return bool
	 */
	public function truncate_cache_table(): bool {
		if ( ! $this->exists() ) {
			return false;
		}

		return $this->truncate();
	}
}
