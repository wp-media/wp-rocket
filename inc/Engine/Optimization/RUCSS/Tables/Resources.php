<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Tables;

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
	protected $version = 20210303;

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
			id            bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			url           varchar(2000)       NOT NULL default '',
			type          varchar(5)          NOT NULL default '',
			content       longtext                     default NULL,
			hash          varchar(100)        NOT NULL default '',
			last_update   timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			UNIQUE KEY hash (hash),
			KEY url (url),
			KEY type (type),
			KEY last_update (last_update),
			KEY last_accessed (last_accessed)";
	}
}
