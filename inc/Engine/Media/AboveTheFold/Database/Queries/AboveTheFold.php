<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Queries;

use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;

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
	protected $table_schema = '\\WP_Rocket\\Engine\\Media\\AboveTheFold\\Database\\Schemas\\AboveTheFold';

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
	protected $item_shape = '\\WP_Rocket\\Engine\\Media\\AboveTheFold\\Database\\Row\\AboveTheFold';

	/**
	 * Complete a job.
	 *
	 * @param integer $id DB row ID.
	 * @param array Associative array of data to save:
     *                       - 'lcp': LCP.
     *                       - 'viewport': Array of above the fold images.
	 * 
	 * @return boolean
	 */
	public function make_job_completed( int $id, array $data ): bool {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		return $this->update_item(
			$id,
			[
				'status' => 'completed',
				'lcp' => $data['lcp'],
				'viewport' => $data['viewport'],
			]
		);
	}
}
