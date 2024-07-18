<?php

/**
 * The Table interface defines the contract for database table operations.
 *
 */
namespace WP_Rocket\Engine\Common\PerformanceHints\Database;

interface Table {

	/**
	 * Truncates the database table.
	 *
	 * This method is used to delete all rows from the database table.
	 *
	 * @return bool Returns a boolean value indicating the success or failure of the operation.
	 */
	public function truncate_table() : bool;

}
