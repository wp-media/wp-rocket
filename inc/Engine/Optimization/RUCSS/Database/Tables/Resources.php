<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Tables;

use WP_Rocket\Dependencies\Database\Table;

/**
 * RUCSS Resources Table.
 */
class Resources extends Table {

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
	protected $version = 20210429;


	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [];

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
			prewarmup        tinyint(1) unsigned     NULL default 0,
			warmup_status    tinyint(1) unsigned     NULL default 0,
			modified         DATETIME                NULL default '0000-00-00 00:00:00',
			last_accessed    DATETIME            NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY hash (hash),
			KEY url (url(150)),
			KEY type (type),
			KEY last_accessed (last_accessed)";
	}

	/**
	 * Delete all resources which were not accessed in the last month.
	 *
	 * @return bool|int
	 */
	public function delete_old_items() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "DELETE FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval 1 month)";

		return $db->query( $query );
	}

	/**
	 * Reset warmup fields [prewarmup and warmup_status].
	 *
	 * @return bool|int
	 */
	public function reset_prewarmup_fields() {
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
		$query               = "UPDATE `$prefixed_table_name` SET prewarmup = 0, warmup_status = 0 WHERE prewarmup = 1";

		return $db->query( $query );
	}
}
