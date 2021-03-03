<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;

class Database {
	/**
	 * Instance of RUCSS resources table.
	 *
	 * @var Resources
	 */
	private $rucss_resources_table;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Resources $rucss_resources_table RUCSS Resources Database Table.
	 */
	public function __construct( Resources $rucss_resources_table ) {
		$this->rucss_resources_table = $rucss_resources_table;
	}

	/**
	 * Instantiate and creates RUCSS Database Tables.
	 *
	 * @return void
	 */
	public function instantiate_rucss_database_tables() {
		// If the table does not exist, then create the table.
		if ( ! $this->rucss_resources_table->exists() ) {
			$this->rucss_resources_table->install();
		}
	}

	/**
	 * Instantiate RUCSS tables when updating to 3.9 from older versions.
	 *
	 * @since 3.9
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function instantiate_rucss_database_tables_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.9', '>' ) ) {
			return;
		}

		$this->instantiate_rucss_database_tables();
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
	}
}
