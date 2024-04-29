<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\Pricing;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Pricing::get_promo_end
 *
 * @group License
 */
class GetPromoEnd extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		Functions\when( 'absint' )->returnArg();

		$pricing = new Pricing( $data );

		$this->assertEquals(
			$expected,
			$pricing->get_promo_end()
		);
	}
}
