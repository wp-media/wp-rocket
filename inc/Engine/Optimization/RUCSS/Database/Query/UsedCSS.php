<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Query;

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
	 * Delete all used_css which were not accessed in the last month.
	 *
	 * @return int|false
	 */
	public function delete_old_used_css() {
		global $wpdb;

		$prefixed_table_name = $wpdb->prefix . $this->table_name;
		$query               = "DELETE FROM `$prefixed_table_name` WHERE `last_accessed` <= date_sub(now(), interval 1 month)";
		$rows_affected       = $wpdb->query( $query );

		return $rows_affected;
	}
}
