<?php

namespace WP_Rocket\Addon\Cloudflare\Auth;

interface AuthFactoryInterface {

	/**
	 * Create a new authentication instance.
	 *
	 * @param array $data Data to inject into the  client.
	 * @return AuthInterface
	 */
	public function create( array $data = [] ): AuthInterface;
}
