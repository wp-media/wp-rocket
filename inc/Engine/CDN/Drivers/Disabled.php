<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

/**
 * Driver used when no CDN mode is usable (cdn_state = nothing, or any unrecognized state).
 *
 * Fails closed: never rewrites, regardless of the URL requested.
 */
class Disabled implements DriverInterface {
	/**
	 * Should rewrite url or not.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool {
		return false;
	}
}
