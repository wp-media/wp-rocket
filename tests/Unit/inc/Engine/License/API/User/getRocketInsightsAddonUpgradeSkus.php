<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::get_rocket_insights_addon_upgrade_skus
 *
 * @group License
 */
class GetRocketInsightsAddonUpgradeSkus extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $sku, $expected ) {
		$user = new User( $data );

		$this->assertEquals(
			$expected,
			$user->get_rocket_insights_addon_upgrade_skus( $sku )
		);
	}
}
