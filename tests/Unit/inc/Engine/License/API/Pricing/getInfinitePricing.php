<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\Pricing;

use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Pricing::get_infinite_pricing
 *
 * @group License
 */
class GetInfinitePricing extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$pricing = new Pricing( $data );

		$this->assertEquals(
			$expected,
			$pricing->get_infinite_pricing()
		);
	}
}
