<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;

class RocketCDNFree implements DriverInterface {

	/**
	 * Query instance.
	 *
	 * @var RocketCDN
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param RocketCDN $query Query instance.
	 */
	public function __construct( RocketCDN $query ) {
		$this->query = $query;
	}

	/**
	 * Should rewrite url or not.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool {
		return $this->query->is_url_found( $url );
	}
}
