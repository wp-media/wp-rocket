<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Tables;

use WP_Rocket\Dependencies\Database\Table;

/**
 * RUCSS Resources Table.
 */
final class Resources extends Table {

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $name = 'wpr_rucss_resources';

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_rucss_resources_version';

	/**
	 * Database version
	 *
	 * @var int
	 */
	protected $version = 20210401;


	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [
		20210331 => 'remove_hash_unique',
		20210401 => 'add_media_column',
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
			type             varchar(5)          NOT NULL default '',
			media            varchar(255)            NULL default 'all',
			content          longtext                     default NULL,
			hash             varchar(100)        NOT NULL default '',
			modified         DATETIME                NULL default '0000-00-00 00:00:00',
			last_accessed    DATETIME            NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY hash (hash),
			KEY url (url),
			KEY type (type),
			KEY last_accessed (last_accessed)";
	}

	/**
	 * Remove hash from being unique column and just make it an index.
	 *
	 * @return bool
	 */
	protected function remove_hash_unique() {
		$hash_key_exists = $this->index_exists( 'hash' );

		if ( ! $hash_key_exists ) {
			return $this->is_success( true );
		}

		$removed = $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX hash" );
		$added   = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX hash (hash)" );

		$this->is_success( $removed && $added );

	}

	/**
	 * Add media column.
	 *
	 * @return bool
	 */
	protected function add_media_column() {
		$media_column_exists = $this->column_exists( 'media' );

		if ( $media_column_exists ) {
			return $this->is_success( true );
		}

		$created = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN media VARCHAR(255) NULL default 'all' AFTER type " );

		$this->is_success( $created );

	}

}
