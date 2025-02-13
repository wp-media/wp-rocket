<?php

namespace WP_Rocket\Engine\Media\PreloadFonts\Database\Queries;

use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\AbstractQueries;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\QueriesInterface;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Schema\PreloadFonts as PreloadFontsSchema;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Rows\PreloadFonts as PreloadFontsRows;
class PreloadFonts extends AbstractQueries implements QueriesInterface {

	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_preload_fonts';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_plf';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var   string
	 */
	protected $table_schema = PreloadFontsSchema::class;

	/**
	 * Name for a single item.
	 *
	 * Use underscores between words. I.E. "term_relationship"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name = 'preload_fonts';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name_plural = 'preload_fonts';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var   mixed
	 */
	protected $item_shape = PreloadFontsRows::class;


	/**
	 * Deletes old rows from the database.
	 *
	 * This method is used to delete rows from the database that have not been accessed in the last month.
	 *
	 * @return bool|int Returns a boolean or integer value. The exact return value depends on the implementation.
	 */
	public function delete_old_rows() {
		// Get the database interface.
		$db = $this->get_db();

		// Early bailout if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		$delete_interval = $this->cleanup_interval;

		$prefixed_table_name = $db->prefix . $this->table_name;
		$query               = "DELETE FROM `$prefixed_table_name` WHERE status = 'failed' OR `last_accessed` <= date_sub(now(), interval $delete_interval month)";

		return $db->query( $query );
	}
}
