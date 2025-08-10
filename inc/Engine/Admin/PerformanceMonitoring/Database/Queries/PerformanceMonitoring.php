<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Dependencies\BerlinDB\Database\Query;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Schemas\PerformanceMonitoring as PerformanceMonitoringSchema;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Rows\PerformanceMonitoring as PerformanceMonitoringRow;

class PerformanceMonitoring extends Query {

	/**
	 * Name of the database table to query.
	 *
	 * @var string
	 */
	protected $table_name = 'wpr_performance_monitoring';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_pm';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var   string
	 */
	protected $table_schema = PerformanceMonitoringSchema::class;

	/**
	 * Cleanup interval in months.
	 *
	 * @var   int
	 */
	protected $cleanup_interval = 3;

		/** Item ******************************************************************/

	/**
	 * Name for a single item.
	 *
	 * Use underscores between words. I.E. "term_relationship"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name = 'performance_monitoring';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name_plural = 'performance_monitoring';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var   mixed
	 */
	protected $item_shape = PerformanceMonitoringRow::class;


		/**
		 * Delete all rows which were not accessed in the last month.
		 *
		 * @return bool|int
		 */
	public function delete_old_rows() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$delete_interval = $this->cleanup_interval;

		// Use table class naming helper for consistency with prefixes.
		$prefixed_table_name = $this->table_name;
		if ( property_exists( $db, 'prefix' ) && ! empty( $db->prefix ) ) {
			$prefixed_table_name = $db->prefix . $this->table_name;
		}
		$query         = "DELETE FROM `$prefixed_table_name` WHERE status = 'failed' OR `last_accessed` <= date_sub(now(), interval $delete_interval month)";
		$rows_affected = $db->query( $query );

		return $rows_affected;
	}
}
