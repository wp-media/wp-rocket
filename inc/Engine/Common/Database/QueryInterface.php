<?php
/**
 * The Queries interface defines the contract for database query operations.
 */

namespace WP_Rocket\Engine\Common\Database;

interface QueryInterface {
	/**
	 * Sets the cleanup interval.
	 *
	 * This method sets the interval at which the cleanup process should run.
	 *
	 * @param int $interval The interval in months.
	 */
	public function set_cleanup_interval( int $interval );
}
