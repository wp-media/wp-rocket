<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_detect_browser_language_status_change
 * @group ThirdParty
 * @group Polylang
 */
class Test_detectBrowserLanguageStatusChange extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/PolylangOptionsStub.php';

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/i18n/polylang.php';

		Functions\when( 'rocket_generate_config_file' )->justReturn();
		Functions\when( 'flush_rocket_htaccess' )->justReturn();
	}

	/**
	 * Answers as Polylang does while it saves: it has already changed its own copy, so these are the
	 * settings being written. They are an object that reads like an array, as they are from 3.7.
	 * Every case runs in its own process: the function this stands in for is asked about elsewhere
	 * in the suite by whether it exists at all.
	 *
	 * @param array $options The settings being written.
	 *
	 * @return void
	 */
	private function polylang_saving( array $options ) {
		Functions\when( 'PLL' )->justReturn( (object) [ 'options' => new Polylang_Options_Stub( $options ) ] );
	}

	/**
	 * The language is not in the address, so the cookie has to name it in the file. The settings
	 * being replaced say the opposite, so every cached file is about to move.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldVaryByTheLanguageWhenItIsSetFromContent() {
		$this->polylang_saving(
			[
				'browser'    => 1,
				'force_lang' => 0,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->once();

		$value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$old_value = [
			'browser'    => 0,
			'force_lang' => 1,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $old_value ) );
	}

	/**
	 * The address carries the language, so the file name does not have to, and the files named
	 * with the cookie until now go.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNotVaryByTheLanguageWhenTheAddressCarriesIt() {
		$this->polylang_saving(
			[
				'browser'    => 1,
				'force_lang' => 1,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->once();

		$value = [
			'browser'    => 1,
			'force_lang' => 1,
		];

		$old_value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $old_value ) );
	}

	/**
	 * Detection is turned on while the address still carries the language: the cookie never enters
	 * the file name, so nothing cached moves.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldKeepTheCacheWhenTheFileNamesDoNotMove() {
		$this->polylang_saving(
			[
				'browser'    => 1,
				'force_lang' => 1,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->never();

		$value = [
			'browser'    => 1,
			'force_lang' => 1,
		];

		$old_value = [
			'browser'    => 0,
			'force_lang' => 1,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $old_value ) );
	}

	/**
	 * A save that changes nothing. This hook fires on every save of the option, and the whole
	 * domain is not emptied for one that leaves every file where it is.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldKeepTheCacheWhenNothingChanges() {
		$value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->polylang_saving( $value );

		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->never();

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $value ) );
	}

	/**
	 * Detection is off, so neither list names the cookie and the files named with it go.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereWhenDetectionIsOff() {
		$this->polylang_saving(
			[
				'browser'    => 0,
				'force_lang' => 0,
			]
		);

		Filters\expectRemoved( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_domain' )->once();

		$value = [
			'browser'    => 0,
			'force_lang' => 0,
		];

		$old_value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $old_value ) );
	}

	/**
	 * Polylang is not there to read, so nothing of its is left in either list and nothing it named
	 * is cached. In its own process like the rest: whether PLL exists is the whole question here,
	 * and the suite defines it elsewhere.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereWithoutPolylang() {
		Filters\expectRemoved( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_domain' )->never();

		$value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value ) );
	}
}
