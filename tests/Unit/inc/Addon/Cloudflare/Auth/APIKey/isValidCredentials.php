<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Auth\APIKey;

use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Auth\CredentialsException;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Auth\APIKey::is_valid_credentials
 *
 * @group Cloudflare
 */
class TestIsValidCredentials extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $credentials, $expected ) {
		if ( 'exception' === $expected ) {
			$this->expectException( CredentialsException::class );
		}

		$auth = new APIKey( $credentials['email'], $credentials['api_key'] );

		$this->assertSame(
			$expected,
			$auth->is_valid_credentials()
		);
	}
}
