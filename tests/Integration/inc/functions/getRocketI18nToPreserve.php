<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Fixtures\i18n\i18nTrait;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * Test class covering ::get_rocket_i18n_to_preserve
 * @uses  ::rocket_has_i18n
 * @uses  ::get_rocket_i18n_code
 * @uses  ::get_rocket_i18n_home_url
 * @uses  ::get_rocket_parse_url
 *
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nToPreserve extends TestCase {
	use i18nTrait;

	public function tear_down() {
		parent::tear_down();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $current_lang, $config, $expected ) {
		$this->setUpI18nPlugin( $current_lang, $config );
		$this->assertSame( $expected, get_rocket_i18n_to_preserve( $current_lang ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
