<?php

namespace WP_Rocket\Engine\Preload\Database\Tables;

use WP_Rocket\Dependencies\Database\Table;

class RocketCache extends Table {

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
	protected $version = 20220205;

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
			status           varchar(255)        NOT NULL default '',
			modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY url (url(150), is_mobile),
			KEY modified (modified),
			KEY last_accessed (last_accessed)";
	}
}
