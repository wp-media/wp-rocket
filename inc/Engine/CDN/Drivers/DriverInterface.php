<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

interface DriverInterface {
	/**
	 * Check if we need to rewrite the current url or not.
	 *
	 * Also answers a broader question than the name suggests: "is this CDN mode usable
	 * at all right now?". A driver must return false both when the current URL specifically
	 * is excluded (e.g. a rejected page, or a free-tier page not yet registered) and when the
	 * mode has no usable CDN hostname configured (e.g. BYOCDN with an empty CNAME) or is
	 * otherwise force-paused (e.g. a lapsed RocketCDN subscription).
	 *
	 * @param string $url Current url to test.
	 *
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool;
}
