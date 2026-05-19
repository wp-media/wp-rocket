<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\User;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\User::get_rocket_insights_addon_btn_url
 *
 * @group License
 */
class GetRocketInsightsAddonBtnUrl extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$user = new User( $config['data'] );

		if ( $config['has_url'] ) {
			Functions\when( 'admin_url' )->justReturn( $config['admin_url'] );
			Functions\when( 'add_query_arg' )->alias(
				function ( $key, $value, $url ) {
					return $url . '&' . $key . '=' . $value;
				}
			);
		}

		$this->assertSame(
			$expected,
			$user->get_rocket_insights_addon_btn_url( $config['sku'] )
		);
	}
}
