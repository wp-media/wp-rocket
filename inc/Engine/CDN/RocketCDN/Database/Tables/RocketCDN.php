<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\Database\Tables;

use WP_Rocket\Engine\Common\Database\Tables\AbstractTable;

/**
 * RocketCDN Table class.
 */
class RocketCDN extends AbstractTable {
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $name = 'wpr_rocket_cdn';

	/**
	 * Database version key (saved in _options or _sitemeta).
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_rocket_cdn_version';

	/**
	 * Database version.
	 *
	 * @var int
	 */
	protected $version = 20260420;

	/**
	 * Upgrades array.
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [];

	/**
	 * Table schema data.
	 *
	 * @var string
	 */
	protected $schema_data = "
		id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		title            varchar(255)        NOT NULL default '',
		url              varchar(2000)       NOT NULL default '',
		modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
		last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY modified (modified),
		KEY last_accessed (last_accessed),
        KEY url (url(150))";

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
}
