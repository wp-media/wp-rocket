<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_polylang_varies_by_cookie
 * @group ThirdParty
 * @group Polylang
 */
class Test_polylangVariesByCookie extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/PolylangOptionsStub.php';

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';
	}

	/**
	 * Settings and the answer the cache file name depends on.
	 *
	 * @return array
	 */
	public function settingsProvider() {
		return [
			'detection on and the language set from content' => [ [ 'browser' => 1, 'force_lang' => 0 ], true ],
			'the address carries the language'               => [ [ 'browser' => 1, 'force_lang' => 1 ], false ],
			'detection off'                                  => [ [ 'browser' => 0, 'force_lang' => 0 ], false ],
			'the settings do not say how the language is carried' => [ [ 'browser' => 1 ], false ],
			'the settings say nothing'                       => [ [], false ],
			'values arrive as the strings a stored option can hold' => [ [ 'browser' => '1', 'force_lang' => '0' ], true ],
		];
	}

	/**
	 * @dataProvider settingsProvider
	 *
	 * @param array $settings Polylang settings.
	 * @param bool  $expected Whether the cache has to vary by the language cookie.
	 *
	 * @return void
	 */
	public function testShouldAnswerFromTheSettings( $settings, $expected ) {
		$this->assertSame( $expected, rocket_polylang_varies_by_cookie( $settings ) );
	}

	/**
	 * From Polylang 3.7 the settings are an object that answers like an array, and it has to be
	 * read the same way.
	 *
	 * @return void
	 */
	public function testShouldAnswerFromSettingsPolylangHoldsAsAnObject() {
		$this->assertTrue(
			rocket_polylang_varies_by_cookie(
				new Polylang_Options_Stub(
					[
						'browser'    => 1,
						'force_lang' => 0,
					]
				)
			)
		);
	}
}
