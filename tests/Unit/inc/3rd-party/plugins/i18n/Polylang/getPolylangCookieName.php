<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_get_polylang_cookie_name
 * @group ThirdParty
 * @group Polylang
 */
class Test_getPolylangCookieName extends TestCase {
	/**
	 * Each case defines PLL_COOKIE, so each one needs a process of its own.
	 *
	 * @dataProvider providerTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param string|bool|null $cookie_constant What the site set PLL_COOKIE to, null if it did not.
	 * @param string|bool      $expected        The name both cookie lists have to name.
	 *
	 * @return void
	 */
	public function testShouldReturnTheNameThePluginWrites( $cookie_constant, $expected ) {
		if ( null !== $cookie_constant ) {
			define( 'PLL_COOKIE', $cookie_constant );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		$this->assertSame( $expected, rocket_get_polylang_cookie_name() );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getPolylangCookieName' );
	}
}
