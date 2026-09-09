<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_get_polylang_cookie_name
 * @group ThirdParty
 * @group Polylang
 */
class Test_getPolylangCookieName extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';
	}

	/**
	 * The name Polylang uses unless the site says otherwise.
	 *
	 * @return void
	 */
	public function testShouldReturnTheDefaultName() {
		$this->assertSame( 'pll_language', rocket_get_polylang_cookie_name() );
	}

	/**
	 * The site renamed the cookie, and both lists have to name the same thing Polylang writes.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldReturnTheNameTheSiteChose() {
		define( 'PLL_COOKIE', 'my_pll' );

		$this->assertSame( 'my_pll', rocket_get_polylang_cookie_name() );
		$this->assertSame( [ 'my_cookie', 'my_pll' ], rocket_add_polylang_mandatory_cookie( [ 'my_cookie' ] ) );
		$this->assertSame( [ 'my_cookie', 'my_pll' ], rocket_add_polylang_dynamic_cookie( [ 'my_cookie' ] ) );
	}

	/**
	 * The site turned the cookie off. Both lists are built with array_filter(), which is what drops
	 * it, so neither ends up naming a cookie that is never written.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldReturnFalseWhenTheSiteTurnedTheCookieOff() {
		define( 'PLL_COOKIE', false );

		$this->assertFalse( rocket_get_polylang_cookie_name() );
		$this->assertSame( [], array_filter( rocket_add_polylang_mandatory_cookie( [] ) ) );
		$this->assertSame( [], array_filter( rocket_add_polylang_dynamic_cookie( [] ) ) );
	}
}
