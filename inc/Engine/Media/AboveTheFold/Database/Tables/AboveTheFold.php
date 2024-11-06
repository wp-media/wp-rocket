<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Tables;

use WP_Rocket\Engine\Common\PerformanceHints\Database\Table\AbstractTable;

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
	protected $version = 20240313;

	/**
	 * Key => value array of versions => methods.
	 *
	 * @var array
	 */
	protected $upgrades = [
		20240313 => 'delete_old_atf_implementation',
	];
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
			error_message    longtext                NULL default NULL,
			status           varchar(255)        NOT NULL default '',
			modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY url (url(150), is_mobile),
			KEY modified (modified),
			KEY last_accessed (last_accessed),
			INDEX `status_index` (`status`(191))";

	/**
	 * This function is responsible for deleting old columns from the 'wpr_above_the_fold' table.
	 * The columns to be deleted are: 'error_code', 'retries', 'job_id', 'queue_name', 'submitted_at', 'next_retry_time'.
	 *
	 * @return bool Returns true if all specified columns are successfully deleted or do not exist, false otherwise.
	 */
	public function delete_old_atf_implementation() {
		// An array of column names to be deleted.
		$columns_to_delete = [ 'error_code', 'retries', 'job_id', 'queue_name', 'submitted_at', 'next_retry_time' ];

		// A flag to indicate the success of the operation. It is initially set to true.
		$is_successful = true;

		// Iterate over each column name in the array.
		foreach ( $columns_to_delete as $column ) {
			// Check if the column exists in the table.
			if ( $this->column_exists( $column ) ) {
				// If the column exists, attempt to delete it.
				$query_result = $this->get_db()->query( "ALTER TABLE `{$this->table_name}` DROP COLUMN `{$column}`" );

				// If the deletion query fails, set the success flag to false.
				if ( false === $query_result ) {
					$is_successful = false;
				}
			}
		}

		// Return the success flag. If all deletion queries were successful (or the columns did not exist), this will be true. If any query failed, this will be false.
		return $is_successful;
	}
}
