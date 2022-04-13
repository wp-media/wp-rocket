<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers ::rocket_has_i18n
 * @group Functions
 * @group i18n
 */
class Test_RocketHasI18n extends TestCase {
	protected function tearDown(): void {
		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $globals, $expected, array $config = [] ) {
		foreach( $globals as $key => $value ) {
			$GLOBALS[$key] = $value;
		}

		if ( array_key_exists( 'pll_languages_list', $config ) ) {
			Functions\expect( 'pll_languages_list' )->once()->andReturn( $config['pll_languages_list'] );
		}

		if ( array_key_exists( 'q_config', $globals ) ) {
			Functions\when( 'qtranxf_convertURL' )->justReturn();
		}

		$this->assertSame( $expected, rocket_has_i18n() );
	}
}
