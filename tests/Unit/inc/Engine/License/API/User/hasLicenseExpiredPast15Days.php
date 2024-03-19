<?php

namespace WP_Rocket\tests\Unit\inc\Engine\License\API\User;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\User::has_license_expired_more_than_15_days
 *
 * @group License
 */
class hasLicenseExpiredPast15Days extends TestCase {

	/**
	 * @var User
	 */
	protected $user;

	public function set_up() {
		parent::set_up();
		$this->user = Mockery::mock(User::class);
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $data, $expected ) {
		$user = new User( $data );

		$this->assertSame(
			$expected,
			$user->has_license_expired_more_than_15_days()
		);
	}
}
