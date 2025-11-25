<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\Database\Queries;

use WP_Rocket\Dependencies\BerlinDB\Database\Query;
use WP_Rocket\Engine\Common\Database\QueryInterface;

class AbstractQuery extends Query implements QueryInterface {
	/**
	 * Cleanup interval in months.
	 * Default is 3 months.
	 *
	 * @var int
	 */
	public $cleanup_interval = 3;

	/**
	 * Queries the database and retrieves items or counts.
	 * We extend this method to check if the query returns 'Table not exists' error to delete the transient and force creating table.
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @param bool         $use_cache Use DB cache or not. (custom parameter added by us!).
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function query( $query = [], bool $use_cache = true ) {
		$db = $this->get_db();
		if ( empty( $db ) ) {
			return ! empty( $query['count'] ) ? 0 : [];
		}

		$query_result = parent::query( $query, $use_cache );
		$last_error   = $db->last_error;
		if ( empty( $last_error ) ) {
			return $query_result;
		}

		$db_name        = rocket_get_constant( 'DB_NAME', '' );
		$prefixed_table = $db->prefix . $this->table_name;
		$pattern        = "/Table [`'\"]?" . preg_quote( $db_name, '/' ) . '\.' . preg_quote( $prefixed_table, '/' ) . "[`'\"]? doesn't exist/i";

		if ( preg_match( $pattern, $last_error ) ) {
			delete_transient( $this->table_name . '_exists' );
		}

		return ! empty( $query['count'] ) ? 0 : [];
	}

	/**
	 * Get row for specific url.
	 *
	 * @param string $url Page Url.
	 * @param bool   $is_mobile if the request is for mobile page.
	 *
	 * @return false|mixed
	 */
	public function get_row( string $url, bool $is_mobile = false ) {
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
	 * Get single row by ID.
	 *
	 * @param int $row_id DB Row ID.
	 *
	 * @return object|array|false false if no row found, array or object if row found.
	 */
	public function get_row_by_id( int $row_id ) {
		$query = $this->query(
			[
				'id' => $row_id,
			]
		);

		if ( is_array( $query ) ) {
			$query = array_pop( $query );
		}

		if ( empty( $query ) ) {
			return false;
		}

		return $query;
	}

	/**
	 * Get all rows with the same url (desktop and mobile versions).
	 *
	 * @param string $url Page url.
	 *
	 * @return array|false
	 */
	public function get_rows_by_url( string $url ) {
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
	 * Fetch on submit jobs.
	 *
	 * @param int $count Number of jobs to fetch.
	 * @return array|int
	 */
	public function get_on_submit_jobs( int $count = 100 ) {
		$in_progress_count = (int) $this->query(
			[
				'count'  => true,
				'status' => [ 'in-progress' ],
			]
		);
		$pending_count     = (int) $this->query(
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
	 * Create new DB row for specific url.
	 *
	 * @param string $url Current page url.
	 * @param string $job_id API job_id.
	 * @param string $queue_name API Queue name.
	 * @param bool   $is_mobile if the request is for mobile page.
	 * @param array  $additional_details Additional details to be saved into DB.
	 *
	 * @return bool
	 */
	public function create_new_job( string $url, string $job_id = '', string $queue_name = '', bool $is_mobile = false, array $additional_details = [] ) {
		$item = [
			'url'           => untrailingslashit( $url ),
			'is_mobile'     => $is_mobile,
			'job_id'        => $job_id,
			'queue_name'    => $queue_name,
			'status'        => 'to-submit',
			'retries'       => 0,
			'last_accessed' => current_time( 'mysql', true ),
		];

		if ( ! empty( $additional_details ) ) {
			$item = array_merge( $item, $additional_details );
		}

		$result = $this->add_item( $item );

		/**
		 * Fires after a new job has been added.
		 *
		 * @param mixed $is_success New job status: ID of inserted row if successfully added; false otherwise.
		 * @param string $timestamp Current timestamp.
		 */
		rocket_do_action_and_deprecated(
			'rocket_last_saas_job_added_time',
			[ $result, current_time( 'mysql', true ) ],
			'3.16',
			'rocket_last_rucss_job_added_time'
		);

		return $result;
	}

	/**
	 * Get pending jobs.
	 *
	 * @param int $count Number of rows.
	 *
	 * @return array
	 */
	public function get_pending_jobs( int $count = 100 ) {
		$inprogress_count = (int) $this->query(
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
				'job_id__not_in' => [
					'not_in' => '',
				],
				'orderby'        => 'modified',
				'order'          => 'asc',
			]
		);
	}

	/**
	 * Increment retries number and change status back to pending.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $error_code error code.
	 * @param string  $error_message error message.
	 *
	 * @return bool|int
	 */
	public function increment_retries( string $url, bool $is_mobile, string $error_code, string $error_message ) {
		if ( ! $this->is_allowed() ) {
			return false;
		}

		$db = $this->get_db();

		$prefixed_table_name = $db->prefix . $this->table_name;

		$old = $this->get_row( $url, $is_mobile );

		$retries          = 0;
		$previous_message = '';

		if ( $old ) {
			$retries          = $old->retries;
			$previous_message = $old->error_message;
		}

		$data = [
			'retries'       => $retries + 1,
			'status'        => 'pending',
			'error_message' => $previous_message . ' - ' . current_time( 'mysql', true ) . " {$error_code}: {$error_message}",
		];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
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
		$update_data['job_id'] = $new_job_id;
		return $this->update_item( $id, $update_data );
	}

	/**
	 * Change the status to be in-progress.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param array   $additional_update_fields Additional fields to update in the database.
	 * @return bool|int
	 */
	public function make_status_inprogress( string $url, bool $is_mobile, $additional_update_fields = [] ) {
		if ( ! $this->is_allowed() ) {
			return false;
		}

		$db = $this->get_db();

		$prefixed_table_name = $db->prefix . $this->table_name;
		$where               = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		$additional_update_fields['status'] = 'in-progress';

		return $db->update( $prefixed_table_name, $additional_update_fields, $where );
	}

	/**
	 * Reset the job and add new job_id pending.
	 *
	 * @param int    $id DB row ID.
	 * @param string $job_id API job_id.
	 * @param array  $additional_details Additional details to be saved into DB.
	 *
	 * @return bool
	 */
	public function reset_job( int $id, string $job_id = '', array $additional_details = [] ) {
		$updates = [
			'job_id'        => $job_id,
			'status'        => 'to-submit',
			'error_code'    => '',
			'error_message' => '',
			'retries'       => 0,
			'modified'      => current_time( 'mysql', true ),
			'submitted_at'  => current_time( 'mysql', true ),
		];

		if ( ! empty( $additional_details ) ) {
			$updates = wp_parse_args(
				$additional_details,
				$updates
			);
		}

		return $this->update_item(
			$id,
			$updates
		);
	}

	/**
	 * Change the status to be failed.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $error_code error code.
	 * @param string  $error_message error message.
	 *
	 * @return bool|int
	 */
	public function make_status_failed( string $url, bool $is_mobile, string $error_code, string $error_message ) {
		if ( ! $this->is_allowed() ) {
			return false;
		}

		$db = $this->get_db();

		$prefixed_table_name = $db->prefix . $this->table_name;

		$old = $this->get_row( $url, $is_mobile );

		$previous_message = $old ? $old->error_message : '';

		$data = [
			'status'        => 'failed',
			'error_code'    => $error_code,
			'error_message' => $previous_message . ' - ' . current_time( 'mysql', true ) . " {$error_code}: {$error_message}",
		];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
	}

	/**
	 * Update row last_accessed date to current date.
	 *
	 * @param int $id Used CSS id.
	 *
	 * @return bool
	 */
	public function update_last_accessed( int $id ): bool {
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
		$items = $this->get_rows_by_url( $url );

		if ( empty( $items ) ) {
			return false;
		}

		$deleted = true;
		foreach ( $items as $item ) {
			if ( empty( $item->id ) ) {
				continue;
			}
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
	 * Change the status to be pending.
	 *
	 * @param string $url DB row url.
	 * @param string $job_id API job_id.
	 * @param string $queue_name API Queue name.
	 * @param bool   $is_mobile if the request is for mobile page.
	 * @return bool|int
	 */
	public function make_status_pending( string $url, string $job_id = '', string $queue_name = '', bool $is_mobile = false ) {
		if ( ! $this->is_allowed() ) {
			return false;
		}

		$db = $this->get_db();

		$prefixed_table_name = $db->prefix . $this->table_name;
		$data                = [
			'job_id'       => $job_id,
			'queue_name'   => $queue_name,
			'status'       => 'pending',
			'is_mobile'    => $is_mobile,
			'submitted_at' => current_time( 'mysql', true ),
		];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
	}

	/**
	 * Update the error message.
	 *
	 * @param string  $url DB row url.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param int     $code Response code.
	 * @param string  $message Response message.
	 * @param string  $previous_message Previous saved message.
	 *
	 * @return bool|int
	 */
	public function update_message( string $url, bool $is_mobile, int $code, string $message, string $previous_message = '' ) {
		if ( ! $this->is_allowed() ) {
			return false;
		}

		$db = $this->get_db();

		$prefixed_table_name = $db->prefix . $this->table_name;

		$data = [ 'error_message' => $previous_message . ' - ' . current_time( 'mysql', true ) . " {$code}: {$message}" ];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
	}

	/**
	 * Updates the next_retry_time field
	 *
	 * @param string     $url DB row url.
	 * @param boolean    $is_mobile Is mobile from DB row.
	 * @param string|int $next_retry_time timestamp or mysql format date.
	 *
	 * @return bool|int either it is saved or not.
	 */
	public function update_next_retry_time( string $url, bool $is_mobile, $next_retry_time ) {
		if ( ! $this->is_allowed() ) {
			return false;
		}

		$db = $this->get_db();

		$prefixed_table_name = $db->prefix . $this->table_name;

		if ( is_string( $next_retry_time ) && strtotime( $next_retry_time ) ) {
			// If $next_retry_time is a valid date string, convert it to a timestamp.
			$next_retry_time = strtotime( $next_retry_time );
		} elseif ( ! is_numeric( $next_retry_time ) ) {
			// If it's not numeric and not a valid date string, return false.
			return false;
		}

		$data = [ 'next_retry_time' => gmdate( 'Y-m-d H:i:s', $next_retry_time ) ];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
	}

	/**
	 * Check if db action can be processed.
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		// Bail if no database interface is available.
		if ( empty( $this->get_db() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get total number of rows.
	 *
	 * @return int
	 */
	public function get_total_count() {
		return (int) $this->query(
			[
				'count' => true,
			]
		);
	}

	/**
	 * Set cleanup interval
	 *
	 * @param int $interval The interval duration, usually default to 1.
	 *
	 * @return object
	 */
	public function set_cleanup_interval( int $interval ): object {
		$this->cleanup_interval = $interval;

		return $this;
	}
}
