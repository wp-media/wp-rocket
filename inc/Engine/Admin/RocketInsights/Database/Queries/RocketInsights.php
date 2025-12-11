<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Database\Queries;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Schemas\RocketInsights as RocketInsightsSchema;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights as RocketInsightsRow;
use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;

class RocketInsights extends AbstractQuery {
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
	protected $table_schema = RocketInsightsSchema::class;

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
	protected $item_shape = RocketInsightsRow::class;

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
			'modified'   => gmdate( 'Y-m-d H:i:s' ),
			'score'      => $test_data['performance_score'],
			'report_url' => $test_data['report_url'],
		];

		return (bool) $this->update_item( $db_id, $update_data );
	}

	/**
	 * Get not finished IDs.
	 *
	 * @return array
	 */
	public function get_not_finished_ids() {
		return $this->query(
			[
				'fields'     => 'ids',
				'status__in' => [
					'to-submit',
					'pending',
					'in-progress',
				],
			]
			);
	}

	/**
	 * Make one row blurred.
	 *
	 * @param int $row_id DB row ID.
	 *
	 * @return void
	 */
	public function make_blurred( $row_id ) {
		$this->update_item(
			$row_id,
			[
				'is_blurred' => 1,
			]
		);
	}

	/**
	 * Limit rows to keep the passed argument and remove others.
	 *
	 * @param int $to_keep Number of rows to keep.
	 *
	 * @return void
	 */
	public function prune_old_items( int $to_keep ) {
		$ids = $this->query(
			[
				'fields'  => 'ids',
				'offset'  => $to_keep,
				'orderby' => 'modified',
				'order'   => 'asc',
			]
			);
		foreach ( $ids as $id ) {
			$this->delete_item( $id );
		}
	}

	/**
	 * Change blurred rows into unblurred.
	 *
	 * @return bool|int
	 */
	public function unblur_rows() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		// Use table class naming helper for consistency with prefixes.
		$prefixed_table_name = $this->table_name;
		// @phpstan-ignore-next-line
		if ( ! empty( $db->prefix ) ) {
			$prefixed_table_name = $db->prefix . $this->table_name;
		}

		return $db->query( "UPDATE `$prefixed_table_name` SET is_blurred = '0' WHERE status = 'completed' AND is_blurred = '1'" );
	}
}
