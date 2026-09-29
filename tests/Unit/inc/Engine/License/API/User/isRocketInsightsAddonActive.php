<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::is_rocket_insights_addon_active
 *
 * @group License
 */
class IsRocketInsightsAddonActive extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $sku, $expected ) {
		$user = new User( (object) [] );

		$this->assertSame(
			$expected,
			$user->is_rocket_insights_addon_active( $sku )
		);
	}
}
