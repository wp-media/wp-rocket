<?php

namespace WP_Rocket\Addon\Cloudflare\Auth;

interface AuthInterface {
	/**
	 * Gets headers for Cloudflare API request
	 *
	 * @return array
	 */
    public function get_headers(): array;

	/**
	 * Checks if the credentials are set.
	 *
	 * @return bool true if authorized, false otherwise.
	 */
	public function is_valid_credentials(): bool;
}
