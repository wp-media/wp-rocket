<?php
/**
 * The Queries interface defines the contract for database query operations.
 */

namespace WP_Rocket\Engine\Common\PerformanceHints\Database\Queries;

interface QueriesInterface {
	/**
	 * Deletes old rows from the database.
	 *
	 * This method is used to delete rows from the database that have not been accessed in the last month.
	 *
	 * @return bool|int Returns a boolean or integer value. The exact return value depends on the implementation.
	 */
	public function delete_old_rows();
}
