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
	 * @param array $urls         urls to clean.
	 * @param bool  $release_lock Whether to also release the lock held on each url.
	 * @return void
	 */
	public function partial_clean( array $urls, bool $release_lock = false ) {
		foreach ( $urls as $url ) {
			// if it has a regex, remove the regex part completely.
			if ( str_contains( $url, '*' ) ) {
				$regex_part = basename( $url );
				$url        = str_replace( $regex_part, '', $url );
			}
			if ( ! $this->is_excluded_by_filter( $url ) ) {
				$data = [
					'url'    => $url,
					'status' => 'pending',
				];

				if ( $release_lock ) {
					$data['is_locked'] = false;
				}

				$this->query->create_or_update( $data );
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
