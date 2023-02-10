<?php

namespace WP_Rocket\Addon\Cloudflare\Auth;

interface AuthInterface {
	/**
	 * Gets headers for Cloudflare API request
	 *
	 * @return array
	 */
    public function get_headers(): array;
}
