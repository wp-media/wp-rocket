<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use PLL_Frontend;
use SitePress;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * Test class covering ::get_rocket_i18n_uri
 * @uses  ::rocket_has_i18n
 * @uses  ::get_rocket_i18n_code
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nUri extends TestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/PLL_Frontend.php';
	}

	public function tear_down() {
		parent::tear_down();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $i18n_plugin, $rocket_has_i18n, $config, $expected ) {
		$this->setUpI18nPlugin( $i18n_plugin, $config );
		$this->assertSame( $expected, get_rocket_i18n_uri() );
	}

	private function setUpI18nPlugin( $i18n_plugin, $config ) {
		$config = array_merge(
			[
				'codes' => [],
				'langs' => [],
			],
			$config
		);

		switch ( $i18n_plugin ) {
			case 'wpml':
				$GLOBALS['sitepress']                   = new SitePress();
				$GLOBALS['sitepress']->active_languages = $config['codes'];
				$GLOBALS['sitepress']->home_root        = home_url();
				$GLOBALS['sitepress']->uris_config      = $config['uris'];
				break;

			case 'qtranslate':
			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $config['codes'] ];
				Functions\expect( 'qtranxf_convertURL' )
					->times( count( $config['codes'] ) )
					->andReturnUsing( function ( $home_url, $lang ) {
						return rtrim( $home_url ) . "/{$lang}";
					} );
				break;

			case 'polylang':
				if ( ! empty( $config['codes'] ) ) {
					$GLOBALS['polylang'] = new PLL_Frontend( $config['options'] );
					Functions\expect( 'PLL' )->once()->andReturn( $GLOBALS['polylang'] );
				} else {
					$GLOBALS['polylang'] = 'not-empty';
					Functions\expect( 'PLL' )->never();
				}

				Functions\expect( 'pll_languages_list' )->once()->andReturn( $config['codes'] );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
