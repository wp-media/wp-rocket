<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Tables;

use WP_Rocket\Engine\Common\Database\Tables\AbstractTable;

class AboveTheFold extends AbstractTable {
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
	 * Table schema data.
	 *
	 * @var   string
	 */
	protected $schema_data = "
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
			next_retry_time     	timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY url (url(150), is_mobile),
			KEY modified (modified),
			KEY last_accessed (last_accessed),
			INDEX `status_index` (`status`(191)),
			INDEX `error_code_index` (`error_code`(32))";

	/**
	 * Truncate DB table.
	 *
	 * @return bool
	 */
	public function truncate_atf_table(): bool {
		if ( ! $this->exists() ) {
			return false;
		}

		return $this->truncate();
	}

	/**
	 * Delete all rows which were not accessed in the last month.
	 *
	 * @return bool|int
	 */
	public function delete_old_rows() {
		if ( ! $this->exists() ) {
			return false;
		}
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		/**
		 * Filters the interval (in months) to determine when an Above The Fold (ATF) entry is considered 'old'.
		 * Old ATF entries are eligible for deletion. By default, an ATF entry is considered old if it hasn't been accessed in the last month.
		 *
		 * @param int $delete_interval The interval in months after which an ATF entry is considered old. Default is 1 month.
		 */
		$delete_interval = (int) apply_filters( 'rocket_atf_cleanup_interval', 1 );

		if ( $delete_interval <= 0 ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "DELETE FROM `$prefixed_table_name` WHERE status = 'failed' OR `last_accessed` <= date_sub(now(), interval $delete_interval month)";
		$rows_affected       = $db->query( $query );

		return $rows_affected;
	}
}
