<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\User::is_license_expired
 *
 * @group License
 */
class IsLicenseExpired extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$user = new User( $data );

		$this->assertEquals(
			$expected,
			$user->is_license_expired()
		);
	}
}
