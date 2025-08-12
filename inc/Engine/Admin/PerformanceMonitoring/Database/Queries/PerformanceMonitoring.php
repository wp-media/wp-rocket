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

		/** @var int|false $result result. */
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
	 * Update test with completed data.
	 *
	 * @param int    $db_id Database record ID.
	 * @param string $status Status to set.
	 * @param array  $test_data Test results data.
	 * @return bool
	 */
	public function update_test_data( int $db_id, string $status, array $test_data ): bool {
		$update_data = [
			'status'   => $status,
			'data'     => wp_json_encode( $test_data ),
			'modified' => gmdate( 'Y-m-d H:i:s' ),
		];

		if ( isset( $test_data['completed_at'] ) ) {
			$update_data['last_accessed'] = $test_data['completed_at'];
		}

		return (bool) $this->update_item( $db_id, $update_data );
	}

	/**
	 * Get pending test jobs for background processing.
	 *
	 * @param int $limit Number of jobs to retrieve.
	 * @return array
	 */
	public function get_pending_jobs( int $limit = 10 ): array {
		return $this->query(
			[
				'status'  => [ 'pending', 'running' ],
				'number'  => $limit,
				'orderby' => 'modified',
				'order'   => 'ASC',
			]
		);
	}

	/**
	 * Get the latest completed test for a URL.
	 *
	 * @param string $url URL to check.
	 * @param bool   $is_mobile Whether to check mobile version.
	 * @return object|null
	 */
	public function get_latest_test( string $url, bool $is_mobile = false ): ?object {
		$results = $this->query(
			[
				'url'       => $url,
				'is_mobile' => $is_mobile ? 1 : 0,
				'status'    => 'completed',
				'number'    => 1,
				'orderby'   => 'modified',
				'order'     => 'DESC',
			]
		);

		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * Get test by external test ID.
	 *
	 * @param string $test_id External test ID.
	 * @return object|null
	 */
	public function get_by_test_id( string $test_id ): ?object {
		$results = $this->query(
			[
				'test_id' => $test_id,
				'number'  => 1,
			]
		);

		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * Delete old test records based on retention period.
	 *
	 * @param int $retention_days Number of days to retain records.
	 * @return int Number of deleted records.
	 */
	public function delete_old_tests( int $retention_days = 30 ): int {
		$db = $this->get_db();

		if ( ! $db ) {
			return 0;
		}

		$prefixed_table_name = $this->table_name;
		// @phpstan-ignore-next-line
		if ( property_exists( $db, 'prefix' ) && ! empty( $db->prefix ) ) {
			$prefixed_table_name = $db->prefix . $this->table_name;
		}

		$query = $db->prepare(
			"DELETE FROM `$prefixed_table_name` WHERE `modified` <= DATE_SUB(NOW(), INTERVAL %d DAY)",
			$retention_days
		);

		return (int) $db->query( $query );
	}

	/**
	 * Get performance test statistics.
	 *
	 * @return array
	 */
	public function get_statistics(): array {
		$db = $this->get_db();

		if ( ! $db ) {
			return [];
		}

		$prefixed_table_name = $this->table_name;
		// @phpstan-ignore-next-line
		if ( property_exists( $db, 'prefix' ) && ! empty( $db->prefix ) ) {
			$prefixed_table_name = $db->prefix . $this->table_name;
		}

		$sql = "SELECT 
			COUNT(*) as total_tests,
			SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tests,
			SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tests,
			SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_tests,
			SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_tests
		FROM `$prefixed_table_name`";

		$results = $db->get_results( $sql, ARRAY_A );

		// @phpstan-ignore-next-line
		return $results && is_array( $results ) && ! empty( $results[0] )
			? array_map( 'intval', $results[0] )
			: [];
	}
}
