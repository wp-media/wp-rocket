<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\Pricing;

use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Pricing::get_plus_pricing
 *
 * @group License
 */
class GetPlusPricing extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$pricing = new Pricing( $data );

		$this->assertEquals(
			$expected,
			$pricing->get_plus_pricing()
		);
	}
}
