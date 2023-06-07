<?php

namespace WPMedia\Cloudflare\Auth;

use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;

interface AuthFactoryInterface {

	/**
	 * @param array $data
	 * @return AuthInterface
	 */
	public function create( array $data = []): AuthInterface;
}
