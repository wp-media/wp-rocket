<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::is_reseller_license_banned
 *
 * @group License
 */
class IsResellerLicenseBanned extends TestCase {
	/**
	 * Tests the is_reseller_license_banned() predicate against various user data states.
	 *
	 * @dataProvider configTestData
	 *
	 * @param object|array $data     User data to instantiate the User with.
	 * @param bool         $expected Expected return value.
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$user = new User( $data );

		$this->assertEquals(
			$expected,
			$user->is_reseller_license_banned()
		);
	}
}
