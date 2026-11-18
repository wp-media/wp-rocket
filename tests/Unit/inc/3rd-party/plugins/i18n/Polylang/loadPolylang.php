<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\Polylang\Polylang_Options_Stub;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the filters the Polylang integration registers when it is loaded
 * @group ThirdParty
 * @group Polylang
 */
class Test_loadPolylang extends TestCase {
	/**
	 * Loading registers filters once, so each case needs a process of its own.
	 *
	 * @dataProvider providerTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param array $settings What Polylang is holding.
	 * @param array $expected Which filters the load has to register.
	 *
	 * @return void
	 */
	public function testShouldRegisterWhatTheSettingsAskFor( $settings, $expected ) {
		$this->expectFilter( 'rocket_cache_mandatory_cookies', $expected['mandatory_cookie'], 'rocket_add_polylang_mandatory_cookie' );
		$this->expectFilter( 'rocket_cache_dynamic_cookies', $expected['dynamic_cookie'], 'rocket_add_polylang_dynamic_cookie' );

		if ( $expected['mod_rewrite_off'] ) {
			Filters\expectAdded( 'rocket_htaccess_mod_rewrite' )->with( '__return_false', 74 );
		} else {
			Filters\expectAdded( 'rocket_htaccess_mod_rewrite' )->never();
		}

		define( 'POLYLANG_VERSION', '3.7' );

		Functions\when( 'PLL' )->justReturn( (object) [ 'options' => new Polylang_Options_Stub( $settings ) ] );

		require WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';
	}


	private function expectFilter( $filter, $added, $callback ) {
		if ( $added ) {
			Filters\expectAdded( $filter )->with( $callback );
		} else {
			Filters\expectAdded( $filter )->never();
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'loadPolylang' );
	}
}
