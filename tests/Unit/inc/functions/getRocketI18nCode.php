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

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $i18n_plugin, $codes, $expected ) {
		$i18n_plugin = $this->setUpI18nPlugin( $i18n_plugin, $codes, $expected );
		Functions\expect( 'rocket_has_i18n' )->once()->andReturn( $i18n_plugin );

		$this->assertSame( $expected, get_rocket_i18n_code() );
	}

	private function setUpI18nPlugin( $i18n_plugin, $codes, $expect ) {
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
				$GLOBALS['polylang'] = 'polylang';

				if ( false === $expect ) {
					Functions\expect( 'pll_languages_list' )->never();
					return false;
				}

				Functions\expect( 'pll_languages_list' )->once()->andReturn( $codes );
		}

		return $i18n_plugin;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
