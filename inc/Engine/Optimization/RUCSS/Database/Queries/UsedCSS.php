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

	public function get_pending_jobs( int $count = 10 ) {
		return $this->query(
			[
				'number' => $count,
				'status' => 'pending',
				'fields' => [
					'id',
				],
				'job_id__not_in' => [
					'not_in' => '',
				],
				'orderby' => 'modified',
				'order' => 'asc',
			]
		);
	}

	public function increment_retries( $id, $retries = null ) {
		return $this->update_item( $id, [
			'retries' => $retries + 1,
			'status' => 'pending'
		] );
	}

	public function create_new_job( $url, bool $is_mobile = false ) {
		$item = [
			'url' => $url,
			'is_mobile' => $is_mobile,
			'status' => 'pending',
			'retries' => 0,
		];
		return $this->add_item( $item );
	}

	public function make_status_inprogress( int $id ) {
		return $this->update_item( $id, [
			'status' => 'in-progress'
		] );
	}

	public function make_status_pending( int $id, string $job_id, string $queue_name ) {
		return $this->update_item( $id, [
			'job_id'     => $job_id,
			'queue_name' => $queue_name,
			'status'     => 'pending'
		] );
	}

	public function make_status_failed( int $id ) {
		return $this->update_item( $id, [
			'status'     => 'failed',
			'queue_name' => '',
			'job_id'     => '',
		] );
	}

	public function make_status_completed( int $id, string $css = '' ) {
		return $this->update_item( $id, [
			'css'        => $css,
			'status'     => 'completed',
			'queue_name' => '',
			'job_id'     => '',
		] );
	}

	public function get_css( string $url, bool $is_mobile = false ) {
		$query = $this->query(
			[
				'url'       => $url,
				'is_mobile' => $is_mobile,
			]
		);

		if ( empty( $query[0] ) ) {
			return false;
		}

		return $query[0];
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
}
