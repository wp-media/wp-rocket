<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\Pricing;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\Pricing::get_single_to_infinite_price
 *
 * @group License
 */
class GetSingleToInfinitePrice extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $data, $expected ) {
		$pricing = new Pricing( $data );

		$this->assertEquals(
			$expected,
			$pricing->get_single_to_infinite_price()
		);
	}
}
