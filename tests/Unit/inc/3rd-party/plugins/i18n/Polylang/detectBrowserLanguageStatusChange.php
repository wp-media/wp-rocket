<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\Polylang\Polylang_Options_Stub;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_detect_browser_language_status_change
 * @group ThirdParty
 * @group Polylang
 */
class Test_detectBrowserLanguageStatusChange extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		Functions\when( 'rocket_generate_config_file' )->justReturn();
		Functions\when( 'flush_rocket_htaccess' )->justReturn();
	}

	/**
	 * The filter registers and removes filters, so each case needs a process of its own.
	 *
	 * @dataProvider providerTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param array|null $polylang_holds The settings Polylang still answers with, null when it is gone.
	 * @param array      $value          The settings being saved.
	 * @param array|null $old_value      The settings being replaced, null when the filter is called without them.
	 * @param array      $expected       What the save has to register, remove and purge.
	 *
	 * @return void
	 */
	public function testShouldFollowTheSettingsBeingSaved( $polylang_holds, $value, $old_value, $expected ) {
		if ( null !== $polylang_holds ) {
			// Polylang's own copy still answers with the settings being replaced.
			Functions\when( 'PLL' )->justReturn( (object) [ 'options' => new Polylang_Options_Stub( $polylang_holds ) ] );
		}

		$this->expectFilter( 'rocket_cache_mandatory_cookies', $expected, 'mandatory_cookie', 'rocket_add_polylang_mandatory_cookie' );
		$this->expectFilter( 'rocket_cache_dynamic_cookies', $expected, 'dynamic_cookie', 'rocket_add_polylang_dynamic_cookie' );

		if ( ! empty( $expected['mandatory_never_added'] ) ) {
			Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->never();
		}

		$this->expectCall( 'rocket_clean_home', $expected );
		$this->expectCall( 'rocket_clean_domain', $expected );

		$returned = null === $old_value
			? rocket_detect_browser_language_status_change( $value )
			: rocket_detect_browser_language_status_change( $value, $old_value );

		$this->assertSame( $value, $returned );
	}


	private function expectFilter( $filter, array $expected, $key, $callback ) {
		switch ( $expected[ $key ] ?? null ) {
			case 'added':
				Filters\expectAdded( $filter )->with( $callback );
				break;
			case 'removed':
				Filters\expectRemoved( $filter )->with( $callback );
				break;
		}
	}

	private function expectCall( $function, array $expected ) {
		$key = str_replace( 'rocket_', '', $function );

		if ( ! array_key_exists( $key, $expected ) ) {
			return;
		}

		$expected[ $key ]
			? Functions\expect( $function )->once()
			: Functions\expect( $function )->never();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'detectBrowserLanguageStatusChange' );
	}
}
