<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_activate_polylang and ::rocket_deactivate_polylang
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
	 * Settings are read from the database here, and the language is not in the address, so the
	 * cookie enters the file name and what is cached under the old names goes.
	 *
	 * @return void
	 */
	public function testShouldVaryByTheLanguageWhenItIsSetFromContent() {
		Functions\when( 'get_option' )->justReturn(
			[
				'browser'    => 1,
				'force_lang' => 0,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->once();

		rocket_activate_polylang();
	}

	/**
	 * The address carries the language, so the file name is left alone and so is the cache.
	 *
	 * @return void
	 */
	public function testShouldNotVaryByTheLanguageWhenTheAddressCarriesIt() {
		Functions\when( 'get_option' )->justReturn(
			[
				'browser'    => 1,
				'force_lang' => 1,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->never();
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->never();

		rocket_activate_polylang();
	}

	/**
	 * Browser language detection is off, so the integration leaves the site alone: nothing is
	 * registered, nothing is regenerated and nothing is purged.
	 *
	 * @return void
	 */
	public function testShouldDoNothingWhenDetectionIsOff() {
		Functions\when( 'get_option' )->justReturn(
			[
				'browser'    => 0,
				'force_lang' => 0,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->never();
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->never();
		Filters\expectAdded( 'rocket_htaccess_mod_rewrite' )->never();
		Functions\expect( 'rocket_clean_home' )->never();
		Functions\expect( 'rocket_clean_domain' )->never();

		rocket_activate_polylang();
	}

	/**
	 * Polylang is gone, so neither list names its cookie and the files it named go.
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereOnDeactivation() {
		Functions\when( 'get_option' )->justReturn(
			[
				'browser'    => 1,
				'force_lang' => 0,
			]
		);

		Filters\expectRemoved( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_domain' )->once();

		rocket_deactivate_polylang();
	}

	/**
	 * Polylang is gone, and the language was never in the file name, so nothing cached moves.
	 *
	 * @return void
	 */
	public function testShouldKeepTheCacheOnDeactivationWhenTheAddressCarriedTheLanguage() {
		Functions\when( 'get_option' )->justReturn(
			[
				'browser'    => 1,
				'force_lang' => 1,
			]
		);

		Functions\expect( 'rocket_clean_domain' )->never();

		rocket_deactivate_polylang();
	}
}
