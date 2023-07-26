<?php

namespace WP_Rocket\Addon\Cloudflare\Auth;

use WP_Error;

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
	 * @return bool|WP_Error true if authorized, false otherwise, WP_Error if missing credentials.
	 */
	public function is_valid_credentials();
}
