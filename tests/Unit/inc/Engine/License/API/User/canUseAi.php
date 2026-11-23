<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::can_use_ai
 *
 * @group License
 */
class CanUseAi extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected, $rejected = false ) {
		Functions\expect( 'get_transient' )
			->with( UserClient::LICENCE_REJECTED_TRANSIENT )
			->andReturn( $rejected ? 1 : false );

		$user = new User( $data );

		$this->assertSame(
			$expected,
			$user->can_use_ai()
		);
	}
}
