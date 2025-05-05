<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries;

use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\AbstractQueries;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\QueriesInterface;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Schema\LazyRenderContent as LRCSchema;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Rows\LazyRenderContent as LRCRow;

class LazyRenderContent extends AbstractQueries implements QueriesInterface {
	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_lazy_render_content';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_lrc';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var   string
	 */
	protected $table_schema = LRCSchema::class;

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
	protected $item_name = 'lazy_render_content';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name_plural = 'lazy_render_content';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var   mixed
	 */
	protected $item_shape = LRCRow::class;

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

		$prefixed_table_name = $db->prefix . $this->table_name;
		$query               = "DELETE FROM `$prefixed_table_name` WHERE status = 'failed' OR `last_accessed` <= date_sub(now(), interval $delete_interval month)";

		return $db->query( $query );
	}
}
