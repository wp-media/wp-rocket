<?php

namespace WPMedia\Cloudflare\Auth;

use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;

interface AuthFactoryInterface {

	/**
	 * Create a new authentication instance.
	 *
	 * @param array $data Data to inject into the  client.
	 * @return AuthInterface
	 */
	public function create( array $data = [] ): AuthInterface;
}
