<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::get_update_blocked_reason
 *
 * @group License
 */
class GetUpdateBlockedReason extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$this->stubTranslationFunctions();
		
		$user = new User( $data );

		$this->assertSame(
			$expected,
			$user->get_update_blocked_reason()
		);
	}
}
