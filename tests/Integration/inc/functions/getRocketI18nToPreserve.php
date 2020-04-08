<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use PLL_Frontend;
use SitePress;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_i18n_to_preserve
 * @uses  ::rocket_has_i18n
 * @uses  ::get_rocket_i18n_code
 * @uses  ::get_rocket_i18n_home_url
 * @uses  ::get_rocket_parse_url
 *
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nToPreserve extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/PLL_Frontend.php';
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $current_lang, $config, $expected ) {
		$this->setUpI18nPlugin( $current_lang, $config );
		$this->assertSame( $expected, get_rocket_i18n_to_preserve( $current_lang ) );
	}

	private function setUpI18nPlugin( $lang, $config ) {
		$home_url = home_url();
		$data     = array_merge(
			[
				'codes' => [],
				'langs' => [],
				'uris'  => [],
			],
			$config['data']
		);
		$langs    = $data['langs'];

		switch ( $config['i18n_plugin'] ) {
			case 'wpml':
				$GLOBALS['sitepress']                   = new SitePress();
				$GLOBALS['sitepress']->active_languages = $data['codes'];
				$GLOBALS['sitepress']->home_root        = $home_url;
				$GLOBALS['sitepress']->uris_config      = $data['uris'];
				break;

			case 'qtranslate':
			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $langs ];

				if ( empty( $lang ) || empty( $langs ) ) {
					Functions\expect( 'qtranxf_convertURL' )->with( $home_url, $lang, true )->never();
					Functions\expect( 'qtrans_convertURL' )->with( $home_url, $lang, true )->never();

					return;
				}


				Functions\expect( 'qtranxf_convertURL' )
//					->atLeast( 1 )
					->with( $home_url, $lang, true )
					->andReturnUsing( function ( $home_url, $lang ) use ( $langs ) {
						if ( empty( $lang ) ) {
							return $home_url;
						}

						if ( empty( $langs ) ) {
							return $home_url;
						}

						return trailingslashit( $home_url ) . $lang;
					} );
				break;

			case 'polylang':
				if ( ! empty( $langs ) ) {
					$GLOBALS['polylang'] = new PLL_Frontend( $data['options'] );
					Functions\expect( 'PLL' )->andReturn( $GLOBALS['polylang'] );
					Functions\expect( 'pll_home_url' )
						->with( $lang )
						->andReturnUsing( function ( $lang ) use ( $langs, $home_url ) {
							if ( empty( $lang ) ) {
								return $home_url;
							}

							if ( in_array( $lang, $langs, true ) ) {
								return trailingslashit( $home_url ) . $lang;
							}

							return $home_url;
						} );
				} else {
					$GLOBALS['polylang'] = 'not-empty';
					Functions\expect( 'PLL' )->never();
				}

				Functions\expect( 'pll_languages_list' )->andReturn( $langs );
				break;

			default:
				Functions\expect( 'get_rocket_i18n_code' )->never();
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
