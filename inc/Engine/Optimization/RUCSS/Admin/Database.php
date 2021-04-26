<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

class Database {
	/**
	 * Instance of RUCSS resources table.
	 *
	 * @var Resources
	 */
	private $rucss_resources_table;

	/**
	 * Instance of RUCSS used_css table.
	 *
	 * @var UsedCSS
	 */
	private $rucss_usedcss_table;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Resources $rucss_resources_table RUCSS Resources Database Table.
	 * @param UsedCSS   $rucss_usedcss_table   RUCSS UsedCSS Database Table.
	 */
	public function __construct( Resources $rucss_resources_table, UsedCSS $rucss_usedcss_table ) {
		$this->rucss_resources_table = $rucss_resources_table;
		$this->rucss_usedcss_table   = $rucss_usedcss_table;
	}

	/**
	 * Drop RUCSS Database Tables.
	 *
	 * @return void
	 */
	public function drop_rucss_database_tables() {
		// If the table exist, then drop the table.
		if ( $this->rucss_resources_table->exists() ) {
			$this->rucss_resources_table->uninstall();
		}
		if ( $this->rucss_usedcss_table->exists() ) {
			$this->rucss_usedcss_table->uninstall();
		}
	}

	/**
	 * Truncate RUCSS used_css DB table.
	 *
	 * @return bool
	 */
	public function truncate_used_css_table() : bool {
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
		if ( $this->rucss_usedcss_table->exists() ) {
			$this->rucss_usedcss_table->delete_old_used_css();
		}
	}

	/**
	 * Delete old used css based on last accessed date.
	 *
	 * @return void
	 */
	public function delete_old_resources() {
		if ( $this->rucss_resources_table->exists() ) {
			$this->rucss_resources_table->delete_old_items();
		}
	}

	/**
	 * Get old used css based on last accessed date.
	 *
	 * @return array
	 */
	public function get_old_used_css() : array {
		if ( ! $this->rucss_usedcss_table->exists() ) {
			return [];
		}
		return $this->rucss_usedcss_table->get_old_used_css();
	}
}
