<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_add_polylang_mandatory_cookie
 * @group ThirdParty
 * @group Polylang
 */
class Test_addPolylangMandatoryCookie extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';
	}

	/**
	 * The name Polylang uses by default, added after whatever the site already requires.
	 *
	 * @return void
	 */
	public function testShouldAppendTheLanguageCookie() {
		$this->assertSame(
			[ 'my_cookie', 'pll_language' ],
			rocket_add_polylang_mandatory_cookie( [ 'my_cookie' ] )
		);
	}
}
