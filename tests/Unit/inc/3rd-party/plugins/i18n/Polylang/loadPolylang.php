<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the filters the Polylang integration registers when it is loaded
 * @group ThirdParty
 * @group Polylang
 */
class Test_loadPolylang extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/PolylangOptionsStub.php';
	}

	/**
	 * Loads the integration as WordPress does, with Polylang holding these settings. Each case runs
	 * in its own process: the file registers filters once and defines the functions the rest of the
	 * suite asks for.
	 *
	 * @param array $options What Polylang is holding.
	 *
	 * @return void
	 */
	private function load_with( array $options ) {
		define( 'POLYLANG_VERSION', '3.7' );

		Functions\when( 'PLL' )->justReturn( (object) [ 'options' => new Polylang_Options_Stub( $options ) ] );

		require WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';
	}

	/**
	 * The language is not in the address, so the cookie has to name it in the file.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldVaryByTheLanguageWhenItIsSetFromContent() {
		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Filters\expectAdded( 'rocket_htaccess_mod_rewrite' )->with( '__return_false', 74 );

		$this->load_with(
			[
				'browser'    => 1,
				'force_lang' => 0,
			]
		);
	}

	/**
	 * The address carries the language, so the file name does not have to.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNotVaryByTheLanguageWhenTheAddressCarriesIt() {
		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->never();

		$this->load_with(
			[
				'browser'    => 1,
				'force_lang' => 1,
			]
		);
	}

	/**
	 * Detection is off, so neither list names the cookie and the rewrite rules stay.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereWhenDetectionIsOff() {
		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->never();
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->never();
		Filters\expectAdded( 'rocket_htaccess_mod_rewrite' )->never();

		$this->load_with(
			[
				'browser'    => 0,
				'force_lang' => 0,
			]
		);
	}
}
