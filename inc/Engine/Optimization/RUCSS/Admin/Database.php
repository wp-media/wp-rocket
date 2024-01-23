<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

class Database {
	/**
	 * Instance of RUCSS used_css table.
	 *
	 * @var UsedCSS
	 */
	private $rucss_usedcss_table;

	/**
	 * Creates an instance of the class.
	 *
	 * @param UsedCSS $rucss_usedcss_table   RUCSS UsedCSS Database Table.
	 */
	public function __construct( UsedCSS $rucss_usedcss_table ) {
		$this->rucss_usedcss_table = $rucss_usedcss_table;
	}

	/**
	 * Drop RUCSS Database Tables.
	 *
	 * @return void
	 */
	public function drop_rucss_database_tables() {
		// If the table exist, then drop the table.
		if ( $this->rucss_usedcss_table->exists() ) {
			$this->rucss_usedcss_table->uninstall();
		}
	}

	/**
	 * Truncate RUCSS used_css DB table.
	 *
	 * @return bool
	 */
	public function truncate_used_css_table(): bool {
		if ( ! $this->rucss_usedcss_table->exists() ) {
			return false;
		}
		return $this->rucss_usedcss_table->truncate();
	}

	/**
	 * Delete old used css based on last accessed date.
	 *
	 * @return void
	 */
	public function delete_old_used_css() {
		if ( ! $this->rucss_usedcss_table->exists() ) {
			return;
		}

		$this->rucss_usedcss_table->delete_old_used_css();
	}

	/**
	 * Get old used css based on last accessed date.
	 *
	 * @return array
	 */
	public function get_old_used_css(): array {
		if ( ! $this->rucss_usedcss_table->exists() ) {
			return [];
		}
		return $this->rucss_usedcss_table->get_old_used_css();
	}

	/**
	 * Remove all completed rows.
	 *
	 * @return bool|int
	 */
	public function remove_all_completed_rows() {
		if ( ! $this->rucss_usedcss_table->exists() ) {
			return false;
		}

		return $this->rucss_usedcss_table->remove_all_completed_rows();
	}

	/**
	 * Remove the resources table & version stored in options table
	 *
	 * @since 3.12
	 *
	 * @return bool
	 */
	public function drop_resources_table(): bool {
		global $wpdb;

		$result = $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpr_rucss_resources" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

		if ( false === $result ) {
			return false;
		}

		return delete_option( 'wpr_rucss_resources_version' );
	}
}
