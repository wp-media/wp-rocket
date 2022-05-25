<?php

namespace WP_Rocket\Engine\Preload\Database\Queries;

use WP_Rocket\Dependencies\Database\Query;
use WP_Rocket\Engine\Preload\Database\Rows\RocketCacheRow;
use WP_Rocket\Engine\Preload\Database\Schemas\RocketCache as Schema;

class RocketCache extends Query {


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
	protected $item_shape = RocketCacheRow::class;

	/**
	 * Create new resource row or update its contents if not created before.
	 *
	 * @since 3.9
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	public function create_or_update( array $resource ) {
		// check the database if those resources added before.
		$rows = $this->query(
			[
				'url'       => untrailingslashit( $resource['url'] ),
				'is_mobile' => key_exists( 'is_mobile', $resource ) ? $resource['is_mobile'] : false,
			]
		);

		if ( count( $rows ) === 0 ) {
			// Create this new row in DB.
			$resource_id = $this->add_item(
				[
					'url'           => $resource['url'],
					'is_mobile'     => key_exists( 'is_mobile', $resource ) ? $resource['is_mobile'] : false,
					'status'        => $resource['status'],
					'last_accessed' => current_time( 'mysql', true ),
				]
			);

			if ( $resource_id ) {
				return $resource_id;
			}

			return false;
		}

		$db_row = array_pop( $rows );

		// In all cases update last_accessed column with current date/time.
		$this->update_item(
			$db_row->id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);

		// Update this row with the new content.
		$this->update_item(
			$db_row->id,
			[
				'url'       => $resource['url'],
				'is_mobile' => key_exists( 'is_mobile', $resource ) ? $resource['is_mobile'] : false,
				'status'    => $resource['status'],
				'modified'  => current_time( 'mysql', true ),
			]
		);

		return $db_row->id;
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

	/**
	 * Remove all completed rows one by one.
	 *
	 * @return void
	 */
	public function remove_all_not_accessed_rows() {
		$rows = $this->query(
			[
				'status__in' => [ 'failed', 'pending' ],
				'fields'     => [
					'id',
				],
			]
		);

		foreach ( $rows as $row ) {
			$this->delete_item( $row->id );
		}
	}
}
