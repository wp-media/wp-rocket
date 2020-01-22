<?php

namespace WP_Rocket\Tests\Integration\Addons\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

class CloudflareTestCase extends TestCase {
	protected static $api_key;
	protected static $email;
	protected static $zone_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		// Set up the Cloudflare API's credentials.
		self::$email   = self::getCredential( 'ROCKET_CLOUDFLARE_EMAIL' );
		self::$api_key = self::getCredential( 'ROCKET_CLOUDFLARE_API_KEY' );
		self::$zone_id = self::getCredential( 'ROCKET_CLOUDFLARE_ZONE_ID' );
	}

	/**
	 * Gets the credential's value from either an environment variable (stored locally on the machine or CI) or from a local constant defined in `tests/env/local/cloudflare.php`.
	 *
	 * @param string $name Name of the environment variable or constant to find.
	 *
	 * @return string returns the value if available; else an empty string.
	 */
	protected static function getCredential( $name ) {
		$var = getenv( $name );
		if ( ! empty( $var ) ) {
			return $var;
		}

		if ( ! is_readable( WP_ROCKET_TESTS_DIR . 'env/local/cloudflare.php' ) ) {
			return '';
		}

		// This file is local to the developer's machine and not stored in the repo.
		require_once WP_ROCKET_TESTS_DIR . 'env/local/cloudflare.php';

		return rocket_get_constant( $name, '' );
	}
}
