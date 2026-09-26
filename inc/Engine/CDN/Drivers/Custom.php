<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Engine\CDN\CDN;

class Custom implements DriverInterface {
	use CdnHostnameTrait;

	/**
	 * Constructor.
	 *
	 * @param CDN $cdn CDN instance.
	 */
	public function __construct( CDN $cdn ) {
		$this->cdn = $cdn;
	}

	/**
	 * Should rewrite url or not, once a usable CDN hostname is confirmed.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	protected function resolve_should_rewrite_url( string $url ): bool {
		return true;
	}
}
