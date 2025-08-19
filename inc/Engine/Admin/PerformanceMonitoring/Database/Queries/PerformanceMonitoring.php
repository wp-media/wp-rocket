<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries;

use WP_Rocket\Dependencies\BerlinDB\Database\Query;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Schemas\PerformanceMonitoring as PerformanceMonitoringSchema;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Rows\PerformanceMonitoring as PerformanceMonitoringRow;
use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;


class PerformanceMonitoring extends AbstractQuery {

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
		// @phpstan-ignore-next-line
		if ( property_exists( $db, 'prefix' ) && ! empty( $db->prefix ) ) {
			$prefixed_table_name = $db->prefix . $this->table_name;
		}
		$query         = "DELETE FROM `$prefixed_table_name` WHERE status = 'failed' OR `last_accessed` <= date_sub(now(), interval $delete_interval month)";
		$rows_affected = $db->query( $query );

		return $rows_affected;
	}

	/**
	 * Create a new performance test record.
	 *
	 * @param string $url The URL to test.
	 * @param array  $options Test options.
	 * @return int|false Database record ID on success, false on failure.
	 */
	public function create_test_record( string $url, array $options = [] ) {
		$is_mobile = isset( $options['device'] ) && 'mobile' === $options['device'];

		$data = [
			'url'           => $url,
			'is_mobile'     => $is_mobile ? 1 : 0,
			'status'        => 'pending',
			'modified'      => gmdate( 'Y-m-d H:i:s' ),
			'last_accessed' => gmdate( 'Y-m-d H:i:s' ),
		];

		/**
		 * Tells if the row has been added.
		 *
		 * @var int|false $result .
		 */
		$result = $this->add_item( $data );
		return $result;
	}

	/**
	 * Update test record with external test ID and status.
	 *
	 * @param int    $db_id Database record ID.
	 * @param string $test_id External test ID.
	 * @param string $status Test status.
	 * @return bool
	 */
	public function update_test_id( int $db_id, string $test_id, string $status = 'running' ): bool {
		return (bool) $this->update_item(
			$db_id,
			[
				'test_id'  => $test_id,
				'status'   => $status,
				'modified' => gmdate( 'Y-m-d H:i:s' ),
			]
		);
	}

	/**
	 * Update test status.
	 *
	 * @param int    $db_id Database record ID.
	 * @param string $status New status.
	 * @param string $error_message Optional error message.
	 * @return bool
	 */
	public function update_status( int $db_id, string $status, string $error_message = '' ): bool {
		$update_data = [
			'status'   => $status,
			'modified' => gmdate( 'Y-m-d H:i:s' ),
		];

		if ( ! empty( $error_message ) ) {
			$update_data['error_message'] = $error_message;
		}

		return (bool) $this->update_item( $db_id, $update_data );
	}

	/**
	 * Update test data with status and test results.
	 *
	 * @param int    $db_id Database record ID.
	 * @param string $status Test status.
	 * @param array  $test_data Test results data.
	 * @return bool
	 */
	public function make_status_completed( int $db_id, string $status, array $test_data ): bool {
		$update_data = [
			'status'     => $status,
			'data'       => wp_json_encode( $test_data ),
			'modified'   => gmdate( 'Y-m-d H:i:s' ),
			'score'      => $test_data['performance_score'],
			'report_url' => $test_data['report_url'],
		];

		return (bool) $this->update_item( $db_id, $update_data );
	}

	/**
	 * Delete tests older than specified number of days.
	 *
	 * @param int $days Number of days to retain tests.
	 * @return bool|int Number of deleted records or false on failure.
	 */
	public function delete_old_tests( int $days ) {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		// Use table class naming helper for consistency with prefixes.
		$prefixed_table_name = $this->table_name;
		// @phpstan-ignore-next-line
		if ( property_exists( $db, 'prefix' ) && ! empty( $db->prefix ) ) {
			$prefixed_table_name = $db->prefix . $this->table_name;
		}

		$query          = "DELETE FROM `$prefixed_table_name` WHERE `modified` <= date_sub(now(), interval %d day)";
		$prepared_query = $db->prepare( $query, $days );
		$rows_affected  = $db->query( $prepared_query );

		return $rows_affected;
	}
}
