<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Queries;

use WP_Rocket\Dependencies\Database\Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\ResourceRow;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Schemas\Resources;

class ResourcesQuery extends Query {

	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_rucss_resources';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var string
	 */
	protected $table_alias = 'resources';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var string
	 */
	protected $table_schema = Resources::class;

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
	protected $item_name = 'resource';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var string
	 */
	protected $item_name_plural = 'resources';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var mixed
	 */
	protected $item_shape = ResourceRow::class;

	/**
	 * Create new resource row or update its contents if not created before.
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	public function create_or_update( array $resource ) {
		// check the database if those resources added before.
		$db_row = $this->get_item_by( 'url', $resource['url'] );

		if ( empty( $db_row ) ) {
			// Create this new row in DB.
			$resource_id = $this->add_item(
				[
					'url'           => $resource['url'],
					'type'          => $resource['type'],
					'content'       => $resource['content'],
					'media'         => isset( $resource['media'] ) ? $resource['media'] : '',
					'hash'          => md5( $resource['content'] ),
					'last_accessed' => current_time( 'mysql', true ),
				]
			);

			if ( $resource_id ) {
				return $resource_id;
			}

			return false;
		}

		// In all cases update last_accessed column with current date/time.
		$this->update_item(
			$db_row->id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);

		// Check the content hash.
		if ( md5( $resource['content'] ) === $db_row->hash ) {
			// Do nothing.
			return false;
		}

		// Update this row with the new content.
		$this->update_item(
			$db_row->id,
			[
				'content'  => $resource['content'],
				'hash'     => md5( $resource['content'] ),
				'modified' => current_time( 'mysql', true ),
			]
		);

		return $db_row->id;
	}

	/**
	 * Remove a resource from the table (if it is there).
	 *
	 * @since 3.9
	 *
	 * @param string $url URL of the item to remove_by_url.
	 *
	 * @return void
	 */
	public function remove_by_url( $url ) {
		$db_row = $this->get_item_by( 'url', $url );

		$this->delete_item( $db_row->id );
	}

	/**
	 * Get prewarmup total resources count.
	 *
	 * @param array $urls Urls to be checked.
	 *
	 * @return int
	 */
	public function get_prewarmup_total_count( array $urls ) : int {
		return $this->query(
			[
				'count'     => true,
				'prewarmup' => 1,
				'url__in'   => $urls,
			]
		);
	}

	/**
	 * Get prewarmup warmed resources count.
	 *
	 * @param array $urls Urls to be checked.
	 *
	 * @return int
	 */
	public function get_prewarmup_warmed_count( array $urls ) : int {
		return $this->query(
			[
				'count'         => true,
				'prewarmup'     => 1,
				'warmup_status' => 1,
				'url__in'       => $urls,
			]
		);
	}

	/**
	 * Get prewarmup NOT warmed resources' urls.
	 *
	 * @param array $urls Urls to be checked.
	 *
	 * @return array|string[]
	 */
	public function get_prewarmup_notwarmed_urls( array $urls ) : array {
		return $this->query(
			[
				'fields'        => 'url',
				'prewarmup'     => 1,
				'warmup_status' => 0,
				'url__in'       => $urls,
			]
		);
	}
}
