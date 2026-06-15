<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\Database\Queries;

use WP_Rocket\Engine\CDN\RocketCDN\Database\Rows\RocketCDN as RocketCDNRow;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Schemas\RocketCDN as RocketCDNSchema;
use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;

/**
 * RocketCDN Query class.
 */
class RocketCDN extends AbstractQuery {
	/**
	 * Name of the database table to query.
	 *
	 * @var string
	 */
	protected $table_name = 'wpr_rocket_cdn';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var string
	 */
	protected $table_alias = 'wpr_cdn';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var string
	 */
	protected $table_schema = RocketCDNSchema::class;

	/**
	 * Name for a single item.
	 *
	 * Use underscores between words. I.E. "term_relationship"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var string
	 */
	protected $item_name = 'rocket_cdn';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var string
	 */
	protected $item_name_plural = 'rocket_cdn';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var mixed
	 */
	protected $item_shape = RocketCDNRow::class;

	/**
	 * Get all pages from the database.
	 *
	 * @return RocketCDNRow[]
	 */
	public function get_all(): array {
		return $this->query( [] );
	}

	/**
	 * Get a page by URL.
	 *
	 * @param string $url The URL to search for.
	 *
	 * @return RocketCDNRow|false
	 */
	public function get_by_url( string $url ) {
		$normalized_url = untrailingslashit( $url );

		$items = $this->query(
			[
				'url' => $normalized_url,
			]
		);

		if ( empty( $items ) ) {
			return false;
		}

		return $items[0];
	}

	/**
	 * Check if the url is found in the database.
	 *
	 * @param string $url The URL to search for.
	 *
	 * @return bool
	 */
	public function is_url_found( string $url ): bool {
		$normalized_url = untrailingslashit( $url );

		$counter = $this->query(
			[
				'count' => true,
				'url'   => $normalized_url,
			]
		);

		return 0 < (int) $counter;
	}

	/**
	 * Get total count of pages.
	 *
	 * @param bool $bool_cache Use DB cache or not.
	 *
	 * @return int
	 */
	public function get_total_count( bool $bool_cache = true ): int {
		return (int) $this->query(
			[
				'count' => true,
			],
			$bool_cache
		);
	}

	/**
	 * Delete all rows from the table.
	 *
	 * @return bool|int
	 */
	public function delete_all_rows() {
		$db = $this->get_db();

		if ( ! $db ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		$query         = "DELETE FROM `$prefixed_table_name`";
		$rows_affected = $db->query( $query );

		return $rows_affected;
	}
}
