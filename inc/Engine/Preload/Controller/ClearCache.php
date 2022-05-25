<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;

class ClearCache {

	/**
	 * DB query.
	 *
	 * @var RocketCache
	 */
	protected $query;

	/**
	 * Initialise ClearCache.
	 *
	 * @param RocketCache $query DB query.
	 */
	public function __construct( RocketCache $query ) {
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
			$this->query->create_or_update(
				[
					'url'    => $url,
					'status' => 'pending',
				]
				);
		}
	}

	/**
	 * Clear all urls.
	 *
	 * @return void
	 */
	public function full_clean() {

		$urls = $this->query->query( [] );

		foreach ( $urls as $url ) {
			$this->query->create_or_update(
				[
					'url'    => $url,
					'status' => 'pending',
				]
				);
		}
	}
}
