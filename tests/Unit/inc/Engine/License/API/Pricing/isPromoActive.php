<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\Pricing;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Pricing::is_promo_active
 *
 * @group License
 */
class IsPromoActive extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		Functions\when( 'absint' )->returnArg();

		$pricing = new Pricing( $data );

		$this->assertEquals(
			$expected,
			$pricing->is_promo_active()
		);
	}
}
