<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Auth\APIKey;

use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Auth\APIKey::get_headers
 *
 * @group Cloudflare
 */
class TestGetHeaders extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $credentials, $expected ) {
		$auth = new APIKey( $credentials['email'], $credentials['api_key'] );

		$this->assertSame(
			$expected,
			$auth->get_headers()
		);
	}
}
