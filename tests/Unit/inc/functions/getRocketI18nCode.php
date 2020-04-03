<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use SitePress;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_i18n_code
 * @uses  ::rocket_has_i18n
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nCode extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/SitePress.php';
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $i18n_plugin, $codes, $expected ) {
		Functions\expect( 'rocket_has_i18n' )->once()->andReturn( $i18n_plugin );

		switch ( $i18n_plugin ) {
			case 'wpml':
				$GLOBALS['sitepress']                   = new SitePress();
				$GLOBALS['sitepress']->active_languages = $codes;
				break;
			case 'qtranslate':
			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $codes ];
				break;
			case 'polylang':
				Functions\expect( 'pll_languages_list' )->once()->andReturn( $codes );
		}

		$this->assertSame( $expected, get_rocket_i18n_code() );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
