<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use PLL_Frontend;
use SitePress;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_i18n_home_url
 * @uses  ::rocket_has_i18n
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nHomeUrl extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/PLL_Frontend.php';
	}

	protected function setUp() {
		parent::setUp();

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

		$this->setUpI18nPlugin( $lang, $config );
		$this->assertSame( $expected, get_rocket_i18n_home_url( $lang ) );
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

		switch ( $config['i18n_plugin'] ) {
			case 'wpml':
				$GLOBALS['sitepress']                   = new SitePress();
				$GLOBALS['sitepress']->active_languages = $data['codes'];
				$GLOBALS['sitepress']->home_root        = $home_url;
				$GLOBALS['sitepress']->uris_config      = $data['uris'];
				break;

			case 'qtranslate':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $data['codes'] ];
				Functions\expect( 'qtrans_convertURL' )
					->with( $home_url, $lang, true )
					->andReturnUsing( function ( $home_url, $lang ) use ( $data ) {
						if ( empty( $lang ) ) {
							return $home_url;
						}

						if ( empty( $data['codes'] ) ) {
							return $home_url;
						}

						return trailingslashit( $home_url ) . $lang;
					} );

			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $data['codes'] ];
				Functions\expect( 'qtranxf_convertURL' )
					->with( $home_url, $lang, true )
					->andReturnUsing( function ( $home_url, $lang ) use ( $data ) {
						if ( empty( $lang ) ) {
							return $home_url;
						}

						if ( empty( $data['codes'] ) ) {
							return $home_url;
						}

						return trailingslashit( $home_url ) . $lang;
					} );
				break;

			case 'polylang':
				if ( ! empty( $data['codes'] ) ) {
					$GLOBALS['polylang'] = new PLL_Frontend( $data['options'] );
					Functions\expect( 'pll_home_url' )
						->once()
						->with( $lang )
						->andReturnUsing( function ( $lang ) use ( $data, $home_url ) {
							if ( empty( $lang ) ) {
								return $home_url;
							}

							if ( in_array( $lang, $data['langs'], true ) ) {
								return trailingslashit( $home_url ) . $lang;
							}

							return $home_url;
						} );
				} else {
					$GLOBALS['polylang'] = 'not-empty';
					Functions\expect( 'PLL' )->never();
				}

				Functions\expect( 'PLL' )->once()->andReturn( $GLOBALS['polylang'] );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
