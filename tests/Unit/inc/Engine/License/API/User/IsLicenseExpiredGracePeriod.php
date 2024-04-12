<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\User::is_license_expired_grace_period
 *
 * @group License
 */
class Test_IsLicenseExpiredGracePeriod extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $data, $expected ) {
		$user = new User( $data );

		$this->assertSame(
			$expected,
			$user->is_license_expired_grace_period()
		);
	}
}
