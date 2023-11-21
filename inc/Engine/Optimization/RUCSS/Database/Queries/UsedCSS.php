<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Queries;

use WP_Rocket\Dependencies\Database\Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSSRow;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Schemas\UsedCSS as UsedCSSSchema;

/**
 * RUCSS UsedCSS Query.
 */
class UsedCSS extends Query {

	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_rucss_used_css';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_rucss';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var   string
	 */
	protected $table_schema = UsedCSSSchema::class;

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
	protected $item_name = 'used_css';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name_plural = 'used_css';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var   mixed
	 */
	protected $item_shape = UsedCSSRow::class;

	/**
	 * Table status.
	 *
	 * @var boolean
	 */
	public static $table_exists = false;

	/**
	 * Get pending jobs.
	 *
	 * @param int $count Number of rows.
	 *
	 * @return array
	 */
	public function get_pending_jobs( int $count = 100 ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return [];
		}

		$inprogress_count = $this->query(
			[
				'count'  => true,
				'status' => 'in-progress',
			]
		);

		if ( $inprogress_count >= $count ) {
			return [];
		}

		return $this->query(
			[
				'number'         => ( $count - $inprogress_count ),
				'status'         => 'pending',
				'fields'         => [
					'id',
					'url',
					'next_retry_time',
				],
				'job_id__not_in' => [
					'not_in' => '',
				],
				'orderby'        => 'modified',
				'order'          => 'asc',
			]
		);
	}

	/**
	 * Fetch on submit jobs.
	 *
	 * @param int $count Number of jobs to fetch.
	 * @return array|int
	 */
	public function get_on_submit_jobs( int $count = 100 ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return [];
		}

		$in_progress_count = $this->query(
			[
				'count'  => true,
				'status' => [ 'in-progress' ],
			]
		);
		$pending_count     = $this->query(
			[
				'count'  => true,
				'status' => [ 'pending' ],
			]
		);

		$processing_count = $in_progress_count + $pending_count;

		if ( 0 !== $count && $processing_count >= $count ) {
			return [];
		}

		$query_params = [
			'status'  => 'to-submit',
			'orderby' => 'modified',
			'order'   => 'asc',
		];

		if ( 0 !== $count ) {
			$query_params['number'] = ( $count - $processing_count );
		}

		return $this->query( $query_params );
	}

	/**
	 * Increment retries number and change status back to pending.
	 *
	 * @param int    $id DB row ID.
	 * @param int    $error_code error code.
	 * @param string $error_message error message.
	 *
	 * @return bool
	 */
	public function increment_retries( $id, int $error_code, string $error_message ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$old = $this->get_item( $id );

		$retries          = 0;
		$previous_message = '';

		if ( $old ) {
			$retries          = $old->retries;
			$previous_message = $old->error_message;
		}

		$update_data = [
			'retries'       => $retries + 1,
			'status'        => 'pending',
			'error_message' => $previous_message . ' - ' . current_time( 'mysql', true ) . " {$error_code}: {$error_message}",
		];

		return $this->update_item( $id, $update_data );
	}

	/**
	 * Update Job ID.
	 *
	 * @param int $id DB row ID.
	 * @param int $new_job_id new job id.
	 *
	 * @return bool
	 */
	public function update_job_id( $id, $new_job_id ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$update_data['job_id'] = $new_job_id;
		return $this->update_item( $id, $update_data );
	}

	/**
	 * Create new DB row for specific url.
	 *
	 * @param string $url Current page url.
	 * @param string $job_id API job_id.
	 * @param string $queue_name API Queue name.
	 * @param bool   $is_mobile if the request is for mobile page.
	 *
	 * @return bool
	 */
	public function create_new_job( string $url, string $job_id = '', string $queue_name = '', bool $is_mobile = false ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$item = [
			'url'           => untrailingslashit( $url ),
			'is_mobile'     => $is_mobile,
			'job_id'        => $job_id,
			'queue_name'    => $queue_name,
			'status'        => 'to-submit',
			'retries'       => 0,
			'last_accessed' => current_time( 'mysql', true ),
		];
		return $this->add_item( $item );
	}

	/**
	 * Change the status to be in-progress.
	 *
	 * @param int $id DB row ID.
	 *
	 * @return bool
	 */
	public function make_status_inprogress( int $id ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return $this->update_item(
			$id,
			[
				'status' => 'in-progress',
			]
		);
	}

	/**
	 * Reset the job and add new job_id pending.
	 *
	 * @param int    $id DB row ID.
	 * @param string $job_id API job_id.
	 *
	 * @return bool
	 */
	public function reset_job( int $id, string $job_id = '' ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return $this->update_item(
			$id,
			[
				'job_id'        => $job_id,
				'status'        => 'to-submit',
				'error_code'    => '',
				'error_message' => '',
				'retries'       => 0,
				'modified'      => current_time( 'mysql', true ),
				'submitted_at'  => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Change the status to be failed.
	 *
	 * @param int    $id DB row ID.
	 * @param string $error_code error code.
	 * @param string $error_message error message.
	 *
	 * @return bool
	 */
	public function make_status_failed( int $id, string $error_code, string $error_message ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$old = $this->get_item( $id );

		$previous_message = $old ? $old->error_message : '';

		return $this->update_item(
			$id,
			[
				'status'        => 'failed',
				'error_code'    => $error_code,
				'error_message' => $previous_message . ' - ' . current_time( 'mysql', true ) . " {$error_code}: {$error_message}",
			]
		);
	}

	/**
	 * Complete a job.
	 *
	 * @param int    $id DB row ID.
	 * @param string $hash Hash.
	 *
	 * @return bool
	 */
	public function make_status_completed( int $id, string $hash = '' ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return $this->update_item(
			$id,
			[
				'hash'   => $hash,
				'status' => 'completed',
			]
		);
	}

	/**
	 * Get Used CSS for specific url.
	 *
	 * @param string $url Page Url.
	 * @param bool   $is_mobile if the request is for mobile page.
	 *
	 * @return false|mixed
	 */
	public function get_row( string $url, bool $is_mobile = false ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$query = $this->query(
			[
				'url'       => untrailingslashit( $url ),
				'is_mobile' => $is_mobile,
			]
		);

		if ( empty( $query[0] ) ) {
			return false;
		}

		return $query[0];
	}

	/**
	 * Get all rows with the same url (desktop and mobile versions).
	 *
	 * @param string $url Page url.
	 *
	 * @return array|false
	 */
	public function get_rows_by_url( string $url ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

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
	 * Get number of rows with the same hash value.
	 *
	 * @param string $hash Hash.
	 *
	 * @return int
	 */
	public function count_rows_by_hash( string $hash ): int {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return 0;
		}

		return $this->query(
			[
				'hash'  => $hash,
				'count' => true,
			]
		);
	}

	/**
	 * Update UsedCSS Row last_accessed date to current date.
	 *
	 * @param int $id Used CSS id.
	 *
	 * @return bool
	 */
	public function update_last_accessed( int $id ): bool {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return (bool) $this->update_item(
			$id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Delete DB row by url.
	 *
	 * @param string $url Page url to be deleted.
	 *
	 * @return bool
	 */
	public function delete_by_url( string $url ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$items = $this->get_rows_by_url( $url );

		if ( ! $items ) {
			return false;
		}

		$deleted = true;
		foreach ( $items as $item ) {
			$deleted = $deleted && $this->delete_item( $item->id );
		}

		return $deleted;
	}

	/**
	 * Get the count of not completed rows.
	 *
	 * @return int
	 */
	public function get_not_completed_count() {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return 0;
		}

		return $this->query(
			[
				'count'      => true,
				'status__in' => [ 'pending', 'in-progress' ],
			]
		);
	}

	/**
	 * Get the count of completed rows.
	 *
	 * @return int
	 */
	public function get_completed_count() {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return 0;
		}

		return $this->query(
			[
				'count'  => true,
				'status' => 'completed',
			]
		);
	}

	/**
	 * Get all failed rows.
	 *
	 * @param float  $delay delay before the urls are deleted.
	 * @param string $unit unit from the delay.
	 * @return array|false
	 */
	public function get_failed_rows( float $delay = 3, string $unit = 'days' ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$query = $this->query(
			[
				'status'     => 'failed',
				'date_query' => [
					[
						'column'    => 'modified',
						'before'    => "$delay $unit ago",
						'inclusive' => true,
					],
				],
			],
			false
		);

		if ( empty( $query ) ) {
			return false;
		}

		return $query;
	}

	/**
	 * Revert status to pending.
	 *
	 * @param integer $id Used CSS id.
	 * @return boolean
	 */
	public function revert_to_pending( int $id ): bool {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return (bool) $this->update_item(
			$id,
			[
				'error_code'    => '',
				'error_message' => '',
				'retries'       => 0,
				'status'        => 'pending',
				'modified'      => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Returns the current status of `wpr_rucss_used_css` table; true if it exists, false otherwise.
	 *
	 * @return boolean
	 */
	private function table_exists(): bool {

		if ( self::$table_exists ) {
			return true;
		}

		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		// Query statement.
		$query    = 'SHOW TABLES LIKE %s';
		$like     = $db->esc_like( $db->{$this->table_name} );
		$prepared = $db->prepare( $query, $like );
		$result   = $db->get_var( $prepared );

		// Does the table exist?
		$exists = $this->is_success( $result );

		if ( $exists ) {
			self::$table_exists = $exists;
		}

		return $exists;
	}

	/**
	 * Update the error message.
	 *
	 * @param int    $job_id Job ID.
	 * @param int    $code Response code.
	 * @param string $message Response message.
	 * @param string $previous_message Previous saved message.
	 *
	 * @return bool
	 */
	public function update_message( int $job_id, int $code, string $message, string $previous_message = '' ): bool {
		return $this->update_item(
			$job_id,
			[
				'error_message' => $previous_message . ' - ' . current_time( 'mysql', true ) . " {$code}: {$message}",
			]
		);
	}

	/**
	 * Updates the next_retry_time field
	 *
	 * @param mixed      $job_id the job id.
	 * @param string|int $next_retry_time timestamp or mysql format date.
	 *
	 * @return bool either it is saved or not.
	 */
	public function update_next_retry_time( $job_id, $next_retry_time ): bool {
		if ( is_string( $next_retry_time ) && strtotime( $next_retry_time ) ) {
			// If $next_retry_time is a valid date string, convert it to a timestamp.
			$next_retry_time = strtotime( $next_retry_time );
		} elseif ( ! is_numeric( $next_retry_time ) ) {
			// If it's not numeric and not a valid date string, return false.
			return false;
		}

		return $this->update_item(
			$job_id,
			[
				'next_retry_time' => gmdate( 'Y-m-d H:i:s', $next_retry_time ),
			]
		);
	}

	/**
	 * Change the status to be pending.
	 *
	 * @param int    $id DB row ID.
	 * @param string $job_id API job_id.
	 * @param string $queue_name API Queue name.
	 * @param bool   $is_mobile if the request is for mobile page.
	 * @return bool
	 */
	public function make_status_pending( int $id, string $job_id = '', string $queue_name = '', bool $is_mobile = false ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return $this->update_item(
			$id,
			[
				'job_id'       => $job_id,
				'queue_name'   => $queue_name,
				'status'       => 'pending',
				'is_mobile'    => $is_mobile,
				'submitted_at' => current_time( 'mysql', true ),
			]
		);
	}
}
