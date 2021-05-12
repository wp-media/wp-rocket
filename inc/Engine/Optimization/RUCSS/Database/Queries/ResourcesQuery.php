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
	 * @since 3.9
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
					'media'         => $resource['media'] ?? '',
					'hash'          => md5( $resource['content'] ),
					'prewarmup'     => $resource['prewarmup'] ?? 0,
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

		// Check the content hash and bailour if the content is the same and we are not in prewarmup.
		if ( md5( $resource['content'] ) === $db_row->hash && ! $resource['prewarmup'] ) {
			// Do nothing.
			return false;
		}

		// Update this row with the new content.
		$this->update_item(
			$db_row->id,
			[
				'prewarmup' => $resource['prewarmup'] ?? 0,
				'content'   => $resource['content'],
				'hash'      => md5( $resource['content'] ),
				'modified'  => current_time( 'mysql', true ),
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
	 * @since 3.9
	 *
	 * @return int
	 */
	public function get_prewarmup_total_count(): int {
		return $this->query(
			[
				'count'     => true,
				'prewarmup' => 1,
			]
		);
	}

	/**
	 * Get prewarmup warmed resources count.
	 *
	 * @since 3.9
	 *
	 * @return int
	 */
	public function get_prewarmup_warmed_count(): int {
		return $this->query(
			[
				'count'         => true,
				'prewarmup'     => 1,
				'warmup_status' => 1,
			]
		);
	}

	/**
	 * Get prewarmup NOT warmed resources' urls.
	 *
	 * @since 3.9
	 *
	 * @param string $type Type of the not warmed URLS (css/js/empty for both).
	 *
	 * @return array|string[]
	 */
	public function get_prewarmup_notwarmed_urls( string $type = '' ): array {
		$params = [
			'fields'        => 'url',
			'prewarmup'     => 1,
			'warmup_status' => 0,
		];

		if ( ! empty( $type ) ) {
			$params['type'] = $type;
		}

		return $this->query( $params );
	}

	/**
	 * Gets resources waiting for prewarmup response
	 *
	 * @since 3.9
	 *
	 * @return array
	 */
	public function get_waiting_prewarmup_items(): array {
		return $this->query(
			[
				'prewarmup'     => 1,
				'warmup_status' => 0,
			]
		);
	}

	/**
	 * Updates the resource row when the warmup status is ok
	 *
	 * @since 3.9
	 *
	 * @param string $url URL of the resource.
	 *
	 * @return bool
	 */
	public function update_warmup_status( string $url ) {
		$db_row = $this->get_item_by( 'url', $url );

		if ( empty( $db_row ) ) {
			return false;
		}

		return $this->update_item(
			$db_row->id,
			[
				'warmup_status' => 1,
			]
		);
	}
}
