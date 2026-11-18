<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use WP_Rocket\Tests\Fixtures\Polylang\Polylang_Options_Stub;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_polylang_varies_by_cookie
 * @group ThirdParty
 * @group Polylang
 */
class Test_polylangVariesByCookie extends TestCase {
	/**
	 * A case may define PLL_COOKIE, so each one needs a process of its own.
	 *
	 * @dataProvider providerTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param array            $settings        Polylang settings.
	 * @param bool             $expected        Whether the cache has to vary by the language cookie.
	 * @param string           $held_as         How the settings are held: an array unless the case says otherwise.
	 * @param string|bool|null $cookie_constant What the site set PLL_COOKIE to, null if it did not.
	 *
	 * @return void
	 */
	public function testShouldAnswerFromTheSettings( $settings, $expected, $held_as = 'array', $cookie_constant = null ) {
		if ( null !== $cookie_constant ) {
			define( 'PLL_COOKIE', $cookie_constant );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		switch ( $held_as ) {
			case 'object':
				$settings = new Polylang_Options_Stub( $settings );
				break;
			case 'plain_object':
				$settings = new \stdClass();
				break;
		}

		$this->assertSame( $expected, rocket_polylang_varies_by_cookie( $settings ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'polylangVariesByCookie' );
	}
}
