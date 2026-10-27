<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\Pricing;

use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Pricing::get_pricing_by_type
 *
 * @group License
 */
class GetPricingByType extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $type, $expected ) {
		$pricing = new Pricing( $data );

		$this->assertEquals(
			$expected,
			$pricing->get_pricing_by_type( $type )
		);
	}
}
