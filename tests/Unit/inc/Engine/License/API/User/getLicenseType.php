<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\User::get_license_type
 *
 * @group License
 */
class GetLicenseType extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$user = new User( $data );

		$this->assertEquals(
			$expected,
			$user->get_license_type()
		);
	}
}
