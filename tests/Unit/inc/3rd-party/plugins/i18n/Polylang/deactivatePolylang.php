<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_deactivate_polylang
 * @group ThirdParty
 * @group Polylang
 */
class Test_deactivatePolylang extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		Functions\when( 'rocket_generate_config_file' )->justReturn();
		Functions\when( 'flush_rocket_htaccess' )->justReturn();
	}

	/**
	 * @dataProvider providerTestData
	 *
	 * @param array $settings     What Polylang stored.
	 * @param bool  $clean_domain Whether the files named after the language have to go.
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereAndPurgeWhatItNamed( $settings, $clean_domain ) {
		Functions\when( 'get_option' )->justReturn( $settings );

		Filters\expectRemoved( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );

		if ( $clean_domain ) {
			Functions\expect( 'rocket_clean_domain' )->once();
		} else {
			Functions\expect( 'rocket_clean_domain' )->never();
		}

		rocket_deactivate_polylang();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'deactivatePolylang' );
	}
}
