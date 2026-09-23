<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API;

use WP_Rocket\Engine\License\API\Currency;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\Currency::format_price_with_currency_symbol
 *
 * @group License
 */
class CurrencyFormatPriceWithCurrencySymbol extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			$expected,
			Currency::format_price_with_currency_symbol(
				$config['price'],
				$config['currency'],
				$config['wrap_span'] ?? '',
				$config['span_classes'] ?? [],
				$config['with_space'] ?? false
			)
		);
	}
}
