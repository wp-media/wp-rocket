<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Auth\APIKey;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Auth\APIKey::is_valid_credentials
 *
 * @group Cloudflare
 */
class TestIsValidCredentials extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $credentials, $expected ) {
		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();

		$auth = new APIKey( $credentials['email'], $credentials['api_key'] );

		$result = $auth->is_valid_credentials();

		if ( 'error' === $expected ) {
			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertSame(
				'cloudflare_credentials_empty',
				$result->get_error_code()
			);
		} else {
			$this->assertSame(
				$expected,
				$result
			);
		}
	}
}
