<?php

namespace WP_Rocket\Engine\Common\Database;

interface TableInterface {
	/**
	 * Delete all rows which were not accessed in the last month.
	 *
	 * @return bool|int
	 */
	public function delete_old_rows();

	/**
	 * Get all rows which were not accessed in the last month.
	 *
	 * @return array
	 */
	public function get_old_rows(): array;

	/**
	 * Remove all completed rows.
	 *
	 * @return bool|int
	 */
	public function remove_all_completed_rows();

	/**
	 * Returns name from table.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Trigger recreation of cache table if not exist.
	 *
	 * @return void
	 */
	public function maybe_trigger_recreate_table();
}
