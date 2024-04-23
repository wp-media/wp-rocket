<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use PLL_Frontend;
use SitePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_i18n_uri
 * @uses  ::rocket_has_i18n
 * @uses  ::get_rocket_i18n_code
 *
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nUri extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/PLL_Frontend.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';
	}

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $i18n_plugin, $rocket_has_i18n, $config, $expected ) {
		Functions\expect( 'rocket_has_i18n' )->once()->andReturn( $rocket_has_i18n );
		$this->setUpI18nPlugin( $i18n_plugin, $config, $expected );
		$this->assertSame( $expected, get_rocket_i18n_uri() );
	}

	private function setUpI18nPlugin( $i18n_plugin, $config, $expected ) {
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
				$GLOBALS['sitepress']->home_root        = 'http://example.org';
				$GLOBALS['sitepress']->uris_config      = $config['uris'];

				Functions\expect( 'get_rocket_i18n_code' )
					->once()
					->andReturn( $config['langs'] );
				break;

			case 'qtranslate':
			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $config['codes'] ];

				Functions\expect( 'get_rocket_i18n_code' )
					->once()
					->andReturn( $config['langs'] );
				Functions\expect( ( 'qtranslate' === $i18n_plugin ) ? 'qtrans_convertURL' : 'qtranxf_convertURL' )
					->times( count( $config['codes'] ) )
					->andReturnUsing( function ( $home_url, $lang ) {
						return rtrim( $home_url ) . "/{$lang}";
					} );
				break;

			case 'polylang':
				if ( ! empty( $config['codes'] ) ) {
					$GLOBALS['polylang'] = new PLL_Frontend( $config['options'] );
					$pll_list            = $GLOBALS['polylang']->model->get_languages_list();
					Functions\expect( 'wp_list_pluck' )
						->once()
						->with( $pll_list, 'search_url' )
						->andReturn( $expected );
					Functions\expect( 'PLL' )->once()->andReturn( $GLOBALS['polylang'] );
				} else {
					$GLOBALS['polylang'] = 'not-empty';
				}

				Functions\expect( 'pll_languages_list' )->andReturn( $config['codes'] );
		}
	}
}
