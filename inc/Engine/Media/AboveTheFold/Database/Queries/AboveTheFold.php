<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Queries;

use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Schemas\AboveTheFold as AboveTheFoldSchema;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Rows\AboveTheFold as AboveTheFoldRow;

class AboveTheFold extends AbstractQuery {

	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_above_the_fold';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_atf';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var   string
	 */
	protected $table_schema = AboveTheFoldSchema::class;

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
	protected $item_name = 'above_the_fold';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name_plural = 'above_the_fold';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var   mixed
	 */
	protected $item_shape = AboveTheFoldRow::class;

	/**
	 * Complete a job.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param array   $data LCP & Above the fold data.
	 *
	 * @return boolean|int
	 */
	public function make_job_completed( string $url, bool $is_mobile, array $data ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		$data = [
			'status'   => 'completed',
			'lcp'      => $data['lcp'],
			'viewport' => $data['viewport'],
		];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
	}

	/**
	 * Delete all rows which were not accessed in the last month.
	 *
	 * @return bool|int
	 */
	public function delete_old_rows() {
		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		/**
		 * Filters the interval (in months) to determine when an Above The Fold (ATF) entry is considered 'old'.
		 * Old ATF entries are eligible for deletion. By default, an ATF entry is considered old if it hasn't been accessed in the last month.
		 *
		 * @param int $delete_interval The interval in months after which an ATF entry is considered old. Default is 1 month.
		 */
		$delete_interval = (int) apply_filters( 'rocket_atf_cleanup_interval', 1 );

		if ( $delete_interval <= 0 ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;
		$query               = "DELETE FROM `$prefixed_table_name` WHERE status = 'failed' OR `last_accessed` <= date_sub(now(), interval $delete_interval month)";
		$rows_affected       = $db->query( $query );

		return $rows_affected;
	}
}
