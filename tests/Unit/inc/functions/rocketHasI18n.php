<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use SitePress;
use stdClass;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_has_i18n
 * @group Functions
 * @group i18n
 */
class Test_RocketHasI18n extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/i18n.php';
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	public function testShouldReturnFalseWhenNoneFound() {
		$this->assertFalse( rocket_has_i18n() );
	}

	public function testShouldReturnFalseWhenSitePressButNotObject() {
		// Not an object.
		$GLOBALS['sitepress'] = 'not object';
		$this->assertFalse( rocket_has_i18n() );

		// Method doesn't exist.
		$GLOBALS['sitepress'] = new stdClass();
		$this->assertFalse( rocket_has_i18n() );
	}

	public function testShouldReturnWPMLWhenSitePress() {
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/SitePress.php';
		$GLOBALS['sitepress'] = new SitePress();

		$this->assertSame( 'wpml', rocket_has_i18n() );
	}

	public function testShouldReturnPolylangWhenPll() {
		$GLOBALS['polylang'] = 'en';
		$this->assertFalse( rocket_has_i18n() );

		Functions\expect( 'pll_languages_list' )->andReturn( [ 'en' ] );
		$this->assertSame( 'polylang', rocket_has_i18n() );
	}

	public function testShouldReturnFalseWhenQConfigNotArray() {
		$GLOBALS['q_config'] = 'not-array';
		$this->assertFalse( rocket_has_i18n() );
	}

	public function testShouldReturnFalseWhenQConfigFunctionsNotExist() {
		$GLOBALS['q_config'] = [ 'en' ];
		$this->assertFalse( rocket_has_i18n() );
	}


	public function testShouldReturnQTranslateWhenQConfigFunctionExists() {
		$GLOBALS['q_config'] = [ 'en' ];

		Functions\expect( 'qtrans_convertURL' )->never();
		$this->assertTrue( function_exists( 'qtrans_convertURL' ) );
		$this->assertFalse( function_exists( 'qtranxf_convertURL' ) );
		$this->assertSame( 'qtranslate', rocket_has_i18n() );

		Functions\expect( 'qtranxf_convertURL' )->never();
		$this->assertTrue( function_exists( 'qtranxf_convertURL' ) );
		$this->assertSame( 'qtranslate-x', rocket_has_i18n() );
	}
}
