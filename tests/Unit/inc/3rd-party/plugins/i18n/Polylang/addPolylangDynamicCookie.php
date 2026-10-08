<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_add_polylang_dynamic_cookie
 * @group ThirdParty
 * @group Polylang
 */
class Test_addPolylangDynamicCookie extends TestCase {
	/**
	 * Each case defines PLL_COOKIE, so each one needs a process of its own.
	 *
	 * @dataProvider providerTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param string|bool|null $cookie_constant What the site set PLL_COOKIE to, null if it did not.
	 * @param array            $cookies         What the site already varies by.
	 * @param array            $expected        The list the filter returns.
	 *
	 * @return void
	 */
	public function testShouldNameTheCookiePolylangWrites( $cookie_constant, $cookies, $expected ) {
		if ( null !== $cookie_constant ) {
			define( 'PLL_COOKIE', $cookie_constant );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		$this->assertSame( $expected, array_filter( rocket_add_polylang_dynamic_cookie( $cookies ) ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addPolylangDynamicCookie' );
	}
}
