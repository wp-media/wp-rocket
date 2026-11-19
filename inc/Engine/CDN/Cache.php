<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\Common\Utils;

class Cache {

	/**
	 * RocketCDN Query instance.
	 *
	 * @var RocketCDN
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param RocketCDN $query RocketCDN Query instance.
	 */
	public function __construct( RocketCDN $query ) {
		$this->query = $query;
	}

	/**
	 * Clear all site's cache.
	 *
	 * @return void
	 */
	public function clear_all_cache(): void {
		rocket_clean_domain();
	}

	/**
	 * Clear rocketCDN free pages cache.
	 *
	 * @return void
	 */
	public function clear_rocketcdn_free_pages_cache(): void {
		$pages = $this->query->get_all();

		foreach ( $pages as $page ) {
			if ( empty( $page->url ) ) {
				continue;
			}

			Utils::clean_url( $page->url );
		}
	}
}
