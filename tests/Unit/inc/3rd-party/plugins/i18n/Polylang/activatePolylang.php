<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_activate_polylang
 * @group ThirdParty
 * @group Polylang
 */
class Test_activatePolylang extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		Functions\when( 'rocket_generate_config_file' )->justReturn();
		Functions\when( 'flush_rocket_htaccess' )->justReturn();
	}

	/**
	 * @dataProvider providerTestData
	 *
	 * @param array $settings What Polylang stored.
	 * @param array $expected What activation has to register and purge.
	 *
	 * @return void
	 */
	public function testShouldFollowTheStoredSettings( $settings, $expected ) {
		Functions\when( 'get_option' )->justReturn( $settings );

		$this->expectFilter( 'rocket_cache_mandatory_cookies', $expected['mandatory_cookie'], 'rocket_add_polylang_mandatory_cookie' );
		$this->expectFilter( 'rocket_cache_dynamic_cookies', $expected['dynamic_cookie'], 'rocket_add_polylang_dynamic_cookie' );
		$this->expectCall( 'rocket_clean_home', $expected['clean_home'] );
		$this->expectCall( 'rocket_clean_domain', $expected['clean_domain'] );

		rocket_activate_polylang();
	}


	private function expectFilter( $filter, $added, $callback ) {
		if ( $added ) {
			Filters\expectAdded( $filter )->with( $callback );
		} else {
			Filters\expectAdded( $filter )->never();
		}
	}

	private function expectCall( $function, $called ) {
		if ( $called ) {
			Functions\expect( $function )->once();
		} else {
			Functions\expect( $function )->never();
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'activatePolylang' );
	}
}
