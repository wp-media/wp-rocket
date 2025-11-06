<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Database\Tables;

use WP_Rocket\Engine\Common\Database\Tables\AbstractTable;

class RocketInsights extends AbstractTable {
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
	protected $version = 20250909;

	/**
	 * Upgrades array.
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [
		20250909 => 'add_is_blurred_column',
	];

	/**
	 * Table schema data.
	 *
	 * @var   string
	 */
	protected $schema_data = "
		id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		url              varchar(2000)       NOT NULL default '',
		title            text       NULL default '',
		is_mobile        tinyint(1)          NOT NULL default 0,
		job_id           varchar(255)        NOT NULL default '',
		queue_name       varchar(255)        NOT NULL default '',
		retries          tinyint(1)          NOT NULL default 1,
		status           varchar(255)                 default NULL,
		data             longtext            NOT NULL default '',
		modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
		last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
		submitted_at     timestamp           NULL,
		next_retry_time  timestamp           NOT NULL default '0000-00-00 00:00:00',
		score            tinyint(3)          NULL default 0,
		report_url       varchar(255)        NULL default '',
		is_blurred       tinyint(1)          NOT NULL default 0,
		error_code       varchar(32)             NULL default NULL,
		error_message    longtext                NULL default NULL,
		PRIMARY KEY (id),
		KEY url (url(150), is_mobile),
		KEY modified (modified),
		KEY last_accessed (last_accessed),
		INDEX `status_index` (`status`(191)),
		INDEX `error_code_index` (`error_code`(32)),
		INDEX `is_blurred` (is_blurred)";

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
	 * Add is blurred columns.
	 *
	 * @return bool
	 */
	protected function add_is_blurred_column() {
		$column_exists = $this->column_exists( 'is_blurred' );

		$created = true;

		if ( ! $column_exists ) {
			$created &= $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN is_blurred tinyint(1) NULL default 0 AFTER report_url, ADD INDEX is_blurred (is_blurred) " );
		}

		return $this->is_success( $created );
	}
}
