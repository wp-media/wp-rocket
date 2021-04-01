<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Tables;

use WP_Rocket\Dependencies\Database\Table;

/**
 * RUCSS UsedCSS Table.
 */
final class UsedCSS extends Table {

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $name = 'wpr_rucss_used_css';

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @var string
	 */
	protected $db_version_key = 'wpr_rucss_used_css_version';

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
		20210401 => 'remove_unique_url'
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
			css              longtext                     default NULL,
			unprocessedcss   longtext            NOT NULL default '',
			retries          tinyint(1)          NOT NULL default 1,
			is_mobile        tinyint(1)          NOT NULL default 0,
			modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
			last_accessed    timestamp           NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY url (url(150), is_mobile),
			KEY modified (modified),
			KEY last_accessed (last_accessed)";
	}

	/**
	 * Delete all used_css which were not accessed in the last month.
	 *
	 * @return int|false
	 */
	public function delete_old_used_css() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $this->apply_prefix( $this->table_name );
		$query               = "DELETE FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval 1 month)";
		$rows_affected       = $db->query( $query );

		return $rows_affected;
	}

	/**
	 * Remove unique from url column.
	 *
	 * @return bool
	 */
	protected function remove_unique_url() {
		$url_key_exists = $this->index_exists( 'url' );

		if ( ! $url_key_exists ) {
			return $this->is_success( true );
		}

		$removed = $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX url" );
		$added   = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX url (url(150), is_mobile)" );

		$this->is_success( $removed && $added );
	}
}
