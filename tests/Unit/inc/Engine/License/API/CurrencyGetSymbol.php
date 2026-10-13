<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API;

use WP_Rocket\Engine\License\API\Currency;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Currency::get_symbol
 *
 * @group License
 */
class CurrencyGetSymbol extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $currency, $expected ) {
		$this->assertSame(
			$expected,
			Currency::get_symbol( $currency )
		);
	}
}
