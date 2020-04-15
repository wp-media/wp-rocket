<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\i18n\i18nTrait;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_i18n_home_url
 * @uses  ::rocket_has_i18n
 *
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nHomeUrl extends TestCase {
	use i18nTrait;

	protected function setUp() {
		parent::setUp();

		$this->always_qtranxf_convertURL = true;
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $lang, $config, $expected ) {
		Functions\expect( 'rocket_has_i18n' )->once()->andReturn( $config['i18n_plugin'] );
		$this->qtrans_convertURL = ( 'qtranslate' === $config['i18n_plugin'] );

		$this->setUpI18nPlugin( $lang, $config );
		$this->assertSame( $expected, get_rocket_i18n_home_url( $lang ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
