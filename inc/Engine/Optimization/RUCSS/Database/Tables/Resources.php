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
	protected $version = 20210317;


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
			content          longtext                     default NULL,
			hash             varchar(100)        NOT NULL default '',
			modified         DATETIME            NOT NULL default '0000-00-00 00:00:00',
			last_accessed    DATETIME            NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			UNIQUE KEY hash (hash),
			KEY url (url),
			KEY type (type),
			KEY last_accessed (last_accessed)";
	}
}
