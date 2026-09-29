<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::get_rocket_insights_addon_limit
 *
 * @group License
 */
class GetRocketInsightsAddonLimit extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $sku, $expected ) {
		$user = new User( $data );

		$this->assertSame(
			$expected,
			$user->get_rocket_insights_addon_limit( $sku )
		);
	}
}
