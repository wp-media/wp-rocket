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
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		$data = [
			'hash'   => $hash,
			'status' => 'completed',
		];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
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
}
