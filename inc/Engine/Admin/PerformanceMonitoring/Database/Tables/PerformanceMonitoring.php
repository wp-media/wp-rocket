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
