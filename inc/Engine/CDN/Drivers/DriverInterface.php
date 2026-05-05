<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

interface DriverInterface {
	/**
	 * Check if we need to rewrite the current url or not.
	 *
	 * @param string $url Current url to test.
	 *
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool;
}
