<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;

class ClearCache {
	use CheckExcludedTrait;
	/**
	 * DB query.
	 *
	 * @var Cache
	 */
	protected $query;

	/**
	 * Initialise ClearCache.
	 *
	 * @param Cache $query DB query.
	 */
	public function __construct( Cache $query ) {
		$this->query = $query;
	}

	/**
	 * Clear urls listed.
	 *
	 * @param array $urls urls to clean.
	 * @return void
	 */
	public function partial_clean( array $urls ) {
		foreach ( $urls as $url ) {

			if ( ! $this->is_excluded_by_filter( $url ) ) {
				$this->query->create_or_update(
					[
						'url'    => $url,
						'status' => 'pending',
					]
				);
			} else {
				$this->query->delete_by_url( $url );
			}
		}
	}

	/**
	 * Clear all urls.
	 *
	 * @return void
	 */
	public function full_clean() {
		$this->query->set_all_to_pending();
	}

	/**
	 * Delete a URL from the preload.
	 *
	 * @param string $url URL to delete.
	 * @return void
	 */
	public function delete_url( string $url ) {
		$this->query->delete_by_url( $url );
	}
}
