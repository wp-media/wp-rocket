<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;

class RocketCDNFree implements DriverInterface {

	private $query;

	public function __construct( RocketCDN $query ) {
		$this->query = $query;
	}

	public function should_rewrite_url( $url ) {
		return true;
	}
}
