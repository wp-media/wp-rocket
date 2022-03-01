<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Queries;

use WP_Rocket\Dependencies\Database\Query;

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
	protected $table_schema = '\\WP_Rocket\\Engine\\Optimization\\RUCSS\\Database\\Schemas\\UsedCSS';

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
	protected $item_shape = '\\WP_Rocket\\Engine\\Optimization\\RUCSS\\Database\\Row\\UsedCSS';

	/**
	 * Get pending jobs.
	 *
	 * @param int $count Number of rows.
	 *
	 * @return array
	 */
	public function get_pending_jobs( int $count = 100 ) {
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
	 * Increment retries number and change status back to pending.
	 *
	 * @param int $id DB row ID.
	 * @param int $retries Current number of retries.
	 *
	 * @return bool
	 */
	public function increment_retries( $id, $retries = null ) {
		return $this->update_item(
			$id,
			[
				'retries' => $retries + 1,
				'status'  => 'pending',
			]
		);
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
	public function create_new_job( string $url, string $job_id, string $queue_name, bool $is_mobile = false ) {
		$item = [
			'url'        => untrailingslashit( $url ),
			'is_mobile'  => $is_mobile,
			'job_id'     => $job_id,
			'queue_name' => $queue_name,
			'status'     => 'pending',
			'retries'    => 0,
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
		return $this->update_item(
			$id,
			[
				'status' => 'in-progress',
			]
		);
	}

	/**
	 * Change the status to be pending.
	 *
	 * @param int    $id DB row ID.
	 * @param string $job_id API job_id.
	 * @param string $queue_name API Queue name.
	 *
	 * @return bool
	 */
	public function make_status_pending( int $id, string $job_id, string $queue_name ) {
		return $this->update_item(
			$id,
			[
				'job_id'     => $job_id,
				'queue_name' => $queue_name,
				'status'     => 'pending',
			]
		);
	}

	/**
	 * Change the status to be failed.
	 *
	 * @param int $id DB row ID.
	 *
	 * @return bool
	 */
	public function make_status_failed( int $id ) {
		return $this->update_item(
			$id,
			[
				'status'     => 'failed',
				'queue_name' => '',
				'job_id'     => '',
			]
		);
	}

	/**
	 * Complete a job.
	 *
	 * @param int    $id DB row ID.
	 * @param string $css Used CSS.
	 *
	 * @return bool
	 */
	public function make_status_completed( int $id, string $css = '' ) {
		return $this->update_item(
			$id,
			[
				'css'        => $css,
				'status'     => 'completed',
				'queue_name' => '',
				'job_id'     => '',
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
	 * Update UsedCSS Row last_accessed date to current date.
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

		if ( ! $items ) {
			return false;
		}

		$deleted = true;
		foreach ( $items as $item ) {
			$deleted = $deleted && $this->delete_item( $item->id );
		}

		return $deleted;
	}

}
