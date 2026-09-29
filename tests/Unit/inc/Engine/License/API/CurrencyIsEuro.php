<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API;

use WP_Rocket\Engine\License\API\Currency;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Currency::is_euro
 *
 * @group License
 */
class CurrencyIsEuro extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $currency, $expected ) {
		$this->assertSame(
			$expected,
			Currency::is_euro( $currency )
		);
	}
}
