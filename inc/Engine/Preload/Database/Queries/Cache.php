<?php

namespace WP_Rocket\Engine\Preload\Database\Queries;

use WP_Rocket\Logger\Logger;
use WP_Rocket\Dependencies\BerlinDB\Database\Query;
use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;
use WP_Rocket\Engine\Preload\Database\Schemas\Cache as Schema;

class Cache extends Query {

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_rocket_cache';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_cache';
	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var string
	 */
	protected $table_schema = Schema::class;

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
	protected $item_name = 'cache';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var string
	 */
	protected $item_name_plural = 'caches';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var mixed
	 */
	protected $item_shape = CacheRow::class;

	/**
	 * Instantiate query.
	 *
	 * @param Logger       $logger logger instance.
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of item query parameters.
	 *     Default empty.
	 *
	 *     @type string       $fields            Site fields to return. Accepts 'ids' (returns an array of item IDs)
	 *                                           or empty (returns an array of complete item objects). Default empty.
	 *                                           To do a date query against a field, append the field name with _query
	 *     @type bool         $count             Whether to return a item count (true) or array of item objects.
	 *                                           Default false.
	 *     @type int          $number            Limit number of items to retrieve. Use 0 for no limit.
	 *                                           Default 100.
	 *     @type int          $offset            Number of items to offset the query. Used to build LIMIT clause.
	 *                                           Default 0.
	 *     @type bool         $no_found_rows     Whether to disable the `SQL_CALC_FOUND_ROWS` query.
	 *                                           Default true.
	 *     @type string|array $orderby           Accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                           Default 'id'.
	 *     @type string       $item              How to item retrieved items. Accepts 'ASC', 'DESC'.
	 *                                           Default 'DESC'.
	 *     @type string       $search            Search term(s) to retrieve matching items for.
	 *                                           Default empty.
	 *     @type array        $search_columns    Array of column names to be searched.
	 *                                           Default empty array.
	 *     @type bool         $update_item_cache Whether to prime the cache for found items.
	 *                                           Default false.
	 *     @type bool         $update_meta_cache Whether to prime the meta cache for found items.
	 *                                           Default false.
	 * }
	 */
	public function __construct( Logger $logger, $query = [] ) {
		parent::__construct( $query );
		$this->logger = $logger;
	}

	/**
	 * Create new resource row or update its contents if not created before.
	 *
	 * @since 3.9
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	public function create_or_update( array $resource ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.resourceFound

		/**
		 * Format the url.
		 *
		 * @param string $url url to format.
		 * @return string
		 */
		$url = apply_filters( 'rocket_preload_format_url', $resource['url'] );

		$url = untrailingslashit( strtok( $url, '?' ) );

		if ( $this->is_rejected( $resource['url'] ) || get_transient( 'wp_rocket_updating' ) ) {
			return false;
		}

		// check the database if those resources added before.
		$rows = $this->query(
			[
				'url' => $url,
			],
			false
		);

		if ( count( $rows ) === 0 ) {
			// Create this new row in DB.
			$resource_id = $this->add_item(
				[
					'url'           => $url,
					'status'        => key_exists( 'status', $resource ) ? $resource['status'] : 'pending',
					'is_locked'     => key_exists( 'is_locked', $resource ) ? $resource['is_locked'] : false,
					'last_accessed' => current_time( 'mysql', true ),
				]
			);

			if ( $resource_id ) {
				return $resource_id;
			}

			$this->logger->error( "Cannot insert {$resource['url']} into {$this->table_name}" );

			return false;
		}

		$db_row = array_pop( $rows );

		$data = [
			'url'       => $url,
			'status'    => key_exists( 'status', $resource ) ? $resource['status'] : $db_row->status,
			'is_locked' => key_exists( 'is_locked', $resource ) ? $resource['is_locked'] : $db_row->is_locked,
			'modified'  => current_time( 'mysql', true ),
		];

		if ( key_exists( 'last_accessed', $resource ) && (bool) $resource['last_accessed'] ) {
			$data['last_accessed'] = current_time( 'mysql', true );
		}

		// Update this row with the new content.
		$this->update_item(
			$db_row->id,
			$data
		);

		return $db_row->id;
	}

	/**
	 * Create new resource row or update its contents if not created before.
	 *
	 * @since 3.9
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	public function create_or_nothing( array $resource ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.resourceFound

		if ( $this->is_rejected( $resource['url'] ) ) {
			return false;
		}

		/**
			* Format the url.
			*
			* @param string $url url to format.
			* @return string
			*/
		$url = apply_filters( 'rocket_preload_format_url', $resource['url'] );

		$url = strtok( $url, '?' );

		// check the database if those resources added before.
		$rows = $this->query(
			[
				'url' => untrailingslashit( $url ),
			],
			false
		);

		if ( count( $rows ) > 0 ) {
			return false;
		}

		// Create this new row in DB.
		$resource_id = $this->add_item(
			[
				'url'           => untrailingslashit( $url ),
				'status'        => key_exists( 'status', $resource ) ? $resource['status'] : 'pending',
				'is_locked'     => key_exists( 'is_locked', $resource ) ? $resource['is_locked'] : false,
				'last_accessed' => current_time( 'mysql', true ),
			]
		);

		if ( $resource_id ) {
			return $resource_id;
		}

		$this->logger->error( "Cannot insert {$resource['url']} into {$this->table_name}" );

		return false;
	}

	/**
	 * Get all rows with the same url (desktop and mobile versions).
	 *
	 * @param string $url Page url.
	 *
	 * @return array|false
	 */
	public function get_rows_by_url( string $url ) {

		$url = strtok( $url, '?' );

		$query = $this->query(
			[
				'url' => untrailingslashit( $url ),
			]
		);

		if ( empty( $query ) ) {
			return false;
		}

		return $query;
	}

	/**
	 * Delete DB row by url.
	 *
	 * @param string $url Page url to be deleted.
	 *
	 * @return bool
	 */
	public function delete_by_url( string $url ) {
		$items = $this->get_rows_by_url( $url );

		if ( ! $items ) {
			return false;
		}

		$deleted = true;
		foreach ( $items as $item ) {
			if ( ! is_bool( $item ) ) {
				$deleted = $deleted && $this->delete_item( $item->id );
			}
		}

		return $deleted;
	}

	/**
	 * Get all preload caches which were not accessed in the last month.
	 *
	 * @param float  $delay delay before the not accessed row is deleted.
	 * @param string $unit unit from the delay.
	 * @return array
	 */
	public function get_old_cache( float $delay = 1, string $unit = 'month' ): array {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return [];
		}

		$prefixed_table_name = $db->prefix . $this->table_name;
		$query               = "SELECT id FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval $delay $unit)";
		$rows_affected       = $db->get_results( $query );

		return $rows_affected;
	}

	/**
	 * Remove all completed rows one by one.
	 *
	 * @param float  $delay delay before the not accessed row is deleted.
	 * @param string $unit unit from the delay.
	 * @return void
	 */
	public function remove_all_not_accessed_rows( float $delay = 1, string $unit = 'month' ) {
		$rows = $this->get_old_cache( $delay, $unit );

		foreach ( $rows as $row ) {
			if ( ! is_bool( $row ) ) {
				$this->delete_item( $row->id );
			}
		}
	}

	/**
	 * Fetch pending jobs.
	 *
	 * @param int $total total of jobs to fetch.
	 * @return array
	 */
	public function get_pending_jobs( int $total = 45 ) {
		$inprogress_count = $this->query(
			[
				'count'     => true,
				'status'    => 'in-progress',
				'is_locked' => false,
			],
			false
		);

		if ( $total <= 0 || (int) $inprogress_count >= $total ) {
			return [];
		}

		$orderby = 'modified';

		/**
		 * Filter order for preloading pending urls.
		 *
		 * @param bool $orderby order for preloading pending urls.
		 *
		 * @returns bool
		 */
		if ( apply_filters( 'rocket_preload_order', false ) ) {
			$orderby = 'id';
		}

		return $this->query(
			[
				'number'         => ( $total - $inprogress_count ),
				'status'         => 'pending',
				'fields'         => [
					'id',
					'url',
				],
				'job_id__not_in' => [
					'not_in' => '',
				],
				'is_locked'      => false,
				'orderby'        => $orderby,
				'order'          => 'asc',
			]
		);
	}

	/**
	 * Change the status from the task to inprogress.
	 *
	 * @param int $id id from the task.
	 * @return bool
	 */
	public function make_status_inprogress( int $id ) {
		return $this->update_item(
			$id,
			[
				'status'   => 'in-progress',
				'modified' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Make the status from the task to complete.
	 *
	 * @param string $url url from the task.
	 * @return bool
	 */
	public function make_status_complete( string $url ) {
		$tasks = $this->query(
			[
				'url' => $url,
			]
		);

		if ( count( $tasks ) === 0 ) {
			return false;
		}

		$task = array_pop( $tasks );

		return $this->update_item(
			$task->id,
			[
				'status'   => 'completed',
				'modified' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Check if pending jobs are remaining.
	 *
	 * @return bool
	 */
	public function has_pending_jobs() {
		$pending_count = $this->query(
			[
				'count'  => true,
				'status' => 'pending',
			]
		);
		return 0 !== $pending_count;
	}

	/**
	 * Revert in-progress urls.
	 *
	 * @return void
	 */
	public function revert_in_progress() {
		$in_progress_list = $this->query(
			[
				'status' => 'in-progress',
			]
		);
		foreach ( $in_progress_list as $in_progress ) {
			$this->update_item(
				$in_progress->id,
				[
					'status'   => 'pending',
					'modified' => current_time( 'mysql', true ),
				]
				);
		}
	}

	/**
	 * Revert old in-progress rows
	 *
	 * @deprecated
	 */
	public function revert_old_in_progress() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;
		$db->query( "UPDATE `$prefixed_table_name` SET status = 'pending', modified = '" . current_time( 'mysql', true ) . "' WHERE status = 'in-progress' AND `modified` <= date_sub(now(), interval 12 hour)" );
	}

	/**
	 * Revert old failed rows.
	 */
	public function revert_old_failed() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;
		return $db->query( "UPDATE `$prefixed_table_name` SET status = 'pending', modified = '" . current_time( 'mysql', true ) . "' WHERE status = 'failed' AND `modified` <= date_sub(now(), interval 12 hour)" );
	}

	/**
	 * Set all rows to pending.
	 */
	public function set_all_to_pending() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		/**
		 * Filter condition for cleaning URLS in the database.
		 *
		 * @param string $condition condition for cleaning URLS in the database.
		 * @returns string
		 */
		$condition = apply_filters( 'rocket_preload_all_to_pending_condition', ' WHERE 1 = 1' );

		$db->query( "UPDATE `$prefixed_table_name` SET status = 'pending', modified = '" . current_time( 'mysql', true ) . "'$condition" );
	}

	/**
	 * Check if the page is preloaded.
	 *
	 * @param string $url url from the page to check.
	 * @return bool
	 */
	public function is_preloaded( string $url ): bool {

		$pending_count = $this->query(
			[
				'count'  => true,
				'status' => 'in-progress',
				'url'    => untrailingslashit( $url ),
			]
		);
		return 0 !== $pending_count;
	}

	/**
	 * Check if the page is pending.
	 *
	 * @param string $url url from the page to check.
	 * @return bool
	 */
	public function is_pending( string $url ): bool {
		$pending_count = $this->query(
			[
				'count'  => true,
				'status' => 'pending',
				'url'    => untrailingslashit( $url ),
			]
		);

		return 0 !== $pending_count;
	}

	/**
	 * Remove all entries from the table.
	 *
	 * @return false|void
	 */
	public function remove_all() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		$db->query( "DELETE FROM `$prefixed_table_name` WHERE 1 = 1" );
	}

	/**
	 * Lock a URL.
	 *
	 * @param string $url URL to lock.
	 *
	 * @return void
	 */
	public function lock( string $url ) {
		$this->create_or_update(
			[
				'url'       => $url,
				'is_locked' => true,
			]
			);
	}

	/**
	 * Unlock all URLs.
	 *
	 * @return false|void
	 */
	public function unlock_all() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		$db->query( "UPDATE `$prefixed_table_name` SET is_locked = false;" );
	}

	/**
	 * Unlock a URL.
	 *
	 * @param string $url URL to unlock.
	 *
	 * @return void
	 */
	public function unlock( string $url ) {
		$this->create_or_update(
			[
				'url'       => $url,
				'is_locked' => false,
			]
			);
	}

	/**
	 * Check if the url is rejected.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_rejected( string $url ): bool {
		$extensions = [
			'php' => 1,
			'xml' => 1,
			'xsl' => 1,
			'kml' => 1,
		];

		$extension = pathinfo( $url, PATHINFO_EXTENSION );

		return $extension && isset( $extensions[ $extension ] );
	}

	/**
	 * Make the status from the task to failed.
	 *
	 * @param int $id id from the task.
	 * @return bool
	 */
	public function make_status_failed( int $id ) {
		return $this->update_item(
			$id,
			[
				'status'   => 'failed',
				'modified' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Update last accessed from the row.
	 *
	 * @param int $id id from the row.
	 * @return bool
	 */
	public function update_last_access( int $id ) {
		return $this->update_item(
			$id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Return outdated in-progress jobs.
	 *
	 * @param int $delay delay to delete.
	 * @return array|int
	 */
	public function get_outdated_in_progress_jobs( int $delay = 3 ) {

		return $this->query(
			[
				'status'     => 'in-progress',
				'is_locked'  => false,
				'date_query' => [
					[
						'column' => 'modified',
						'before' => "$delay minute ago",
					],
				],
			],
			false
		);
	}
}
