<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Queries;

use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSSRow;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Schemas\UsedCSS as UsedCSSSchema;

/**
 * RUCSS UsedCSS Query.
 */
class UsedCSS extends AbstractQuery {

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

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @var   string
	 */
	protected $cache_group = 'wpr_used_css';

	/**
	 * Complete a job.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string  $hash Hash.
	 *
	 * @return bool
	 */
	public function make_status_completed( string $url, bool $is_mobile, string $hash = '' ) {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$old = $this->get_row( $url, $is_mobile );

		if ( ! $old ) {
			return false;
		}

		$data = [
			'hash'   => $hash,
			'status' => 'completed',
		];

		return $this->update_item( $old->id, $data );
	}

	/**
	 * Get number of rows with the same hash value.
	 *
	 * @param string $hash Hash.
	 *
	 * @return int
	 */
	public function count_rows_by_hash( string $hash ): int {
		return $this->query(
			[
				'hash'  => $hash,
				'count' => true,
			]
		);
	}

	/**
	 * Add a row, storing the md5 of its exact url to back the (url_hash, is_mobile) unique key.
	 *
	 * @param array $data Row data.
	 *
	 * @return int|false Inserted row ID, false on failure (including a duplicate url and is_mobile pair).
	 */
	public function add_item( $data = [] ) {
		if ( isset( $data['url'] ) ) {
			$data['url_hash'] = md5( (string) $data['url'] );
		}

		return parent::add_item( $data );
	}

	/**
	 * Get the highest row ID.
	 *
	 * @return int
	 */
	public function get_max_id(): int {
		$db = $this->get_db();

		if ( ! $db ) {
			return 0;
		}

		return (int) $db->get_var( "SELECT MAX(id) FROM `{$db->{$this->table_name}}`" );
	}

	/**
	 * Delete the rows of an ID range having a newer row with the same url and is_mobile.
	 *
	 * The newest row of each group is kept: it is the one url-keyed writes target. Urls are
	 * compared with the column collation, like get_row() does.
	 *
	 * @param int $after_id Exclusive lower bound of the ID range.
	 * @param int $up_to_id Inclusive upper bound of the ID range.
	 * @param int $limit    Maximum number of rows to delete.
	 *
	 * @return int|false Number of rows deleted, false on a database error.
	 */
	public function delete_older_duplicates( int $after_id, int $up_to_id, int $limit ) {
		$db = $this->get_db();

		if ( ! $db ) {
			return false;
		}

		$table = $db->{$this->table_name};

		$ids = $db->get_col(
			$db->prepare(
				"SELECT DISTINCT t1.id FROM `{$table}` t1 INNER JOIN `{$table}` t2 ON t2.url = t1.url AND t2.is_mobile = t1.is_mobile AND t2.id > t1.id WHERE t1.id > %d AND t1.id <= %d LIMIT %d",
				$after_id,
				$up_to_id,
				$limit
			)
		);

		if ( ! empty( $db->last_error ) ) {
			return false;
		}

		foreach ( $ids as $id ) {
			$this->delete_item( (int) $id );
		}

		return count( $ids );
	}
}
