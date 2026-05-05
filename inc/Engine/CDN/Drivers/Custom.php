<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

class Custom implements DriverInterface {
	/**
	 * Should rewrite url or not.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool {
		return true;
	}
}
