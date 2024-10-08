<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Database\Queries;

use WP_Rocket\Dependencies\BerlinDB\Database\Query;

class AbstractQueries extends Query {
	/**
	 * Table status.
	 *
	 * @var boolean
	 */
	public static $table_exists = false;

	/**
	 * Get row for specific url.
	 *
	 * @param string $url Page Url.
	 * @param bool   $is_mobile if the request is for mobile page.
	 *
	 * @return false|mixed
	 */
	public function get_row( string $url, bool $is_mobile = false ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$query = $this->query(
			[
				'url'       => untrailingslashit( $url ),
				'is_mobile' => $is_mobile,
			]
		);

		if ( empty( $query[0] ) ) {
			return false;
		}

		return $query[0];
	}

	/**
	 * Delete DB row by url.
	 *
	 * @param string $url Page url to be deleted.
	 *
	 * @return bool
	 */
	public function delete_by_url( string $url ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$items = $this->get_rows_by_url( $url );

		if ( ! $items ) {
			return false;
		}

		$deleted = true;
		foreach ( $items as $item ) {
			$deleted = $deleted && $this->delete_item( $item->id );
		}

		return $deleted;
	}

	/**
	 * Get the count of not completed rows.
	 *
	 * @return int
	 */
	public function get_not_completed_count() {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return 0;
		}

		return $this->query(
			[
				'count'      => true,
				'status__in' => [ 'pending', 'in-progress' ],
			]
		);
	}

	/**
	 * Get the count of completed rows.
	 *
	 * @return int
	 */
	public function get_completed_count() {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return 0;
		}

		return $this->query(
			[
				'count'  => true,
				'status' => 'completed',
			]
		);
	}

	/**
	 * Returns the current status of the table; true if it exists, false otherwise.
	 *
	 * @return boolean
	 */
	protected function table_exists(): bool {
		if ( self::$table_exists ) {
			return true;
		}

		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( ! $db ) {
			return false;
		}

		// Query statement.
		$query    = 'SELECT table_name FROM information_schema.tables WHERE table_name = %s LIMIT 1';
		$prepared = $db->prepare( $query, $db->{$this->table_name} );
		$result   = $db->get_var( $prepared );

		// Does the table exist?
		$exists = $this->is_success( $result );

		if ( $exists ) {
			self::$table_exists = $exists;
		}

		return $exists;
	}

	/**
	 * Get all rows with the same url (desktop and mobile versions).
	 *
	 * @param string $url Page url.
	 *
	 * @return array|false
	 */
	public function get_rows_by_url( string $url ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		$query = $this->query(
			[
				'url' => untrailingslashit( $url ),
			]
		);

		if ( empty( $query ) ) {
			return false;
		}

		return $query;
	}
}
