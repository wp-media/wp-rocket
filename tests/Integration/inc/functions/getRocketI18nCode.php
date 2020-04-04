<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use SitePress;
use WPMedia\PHPUnit\Integration\TestCase;

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
		$this->setUpI18nPlugin( $i18n_plugin, $codes );
		$this->assertSame( $expected, get_rocket_i18n_code() );
	}

	private function setUpI18nPlugin( $i18n_plugin, $codes ) {
		switch ( $i18n_plugin ) {
			case 'wpml':
				$GLOBALS['sitepress']                   = new SitePress();
				$GLOBALS['sitepress']->active_languages = $codes;
				break;
			case 'qtranslate':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $codes ];
				Functions\when( 'qtrans_convertURL' )->justReturn( null );
				break;
			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $codes ];
				Functions\when( 'qtranxf_convertURL' )->justReturn( null );
				break;
			case 'polylang':
				$GLOBALS['polylang'] = 'polylang';
				Functions\expect( 'pll_languages_list' )->atLeast( 1 )->andReturn( $codes );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
