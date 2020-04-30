<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Fixtures\i18n\i18nTrait;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_i18n_home_url
 * @uses  ::rocket_has_i18n
 *
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nHomeUrl extends TestCase {
	use i18nTrait;

	public function setUp() {
		parent::setUp();

		$this->always_qtranxf_convertURL = true;
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $lang, $config, $expected ) {
		$this->setUpI18nPlugin( $lang, $config );
		$this->assertSame( $expected, get_rocket_i18n_home_url( $lang ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
