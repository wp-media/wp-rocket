<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_has_i18n
 * @group Functions
 * @group i18n
 */
class Test_RocketHasI18n extends TestCase {

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
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

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
