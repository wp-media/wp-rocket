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
	 * Answers as Polylang does while it saves: its own copy is still the settings being replaced,
	 * measured on 3.8.9, where it is an object that reads like an array. Where those differ from
	 * the ones being saved, reading it instead of $value turns the case red.
	 *
	 * @param array $options The settings Polylang still holds, i.e. the previous ones.
	 *
	 * @return void
	 */
	private function polylang_holds( array $options ) {
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
		$this->polylang_holds(
			[
				'browser'    => 0,
				'force_lang' => 1,
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
		$this->polylang_holds(
			[
				'browser'    => 1,
				'force_lang' => 0,
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
		$this->polylang_holds(
			[
				'browser'    => 0,
				'force_lang' => 1,
			]
		);

		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
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

		$this->polylang_holds( $value );

		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->never();

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $value ) );
	}

	/**
	 * Detection goes off, so the cookie names nothing any more and the files it named go.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereWhenDetectionIsOff() {
		$this->polylang_holds(
			[
				'browser'    => 1,
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
	 * Called with the settings alone, as a filter may be: nothing says what the names were, so the
	 * files that may be under the old ones go.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldPurgeWhenThePreviousSettingsAreNotGiven() {
		$this->polylang_holds( [] );

		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->once();

		$value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value ) );
	}

	/**
	 * The option can be written with Polylang inactive: nothing sets the cookie then, so the lists
	 * stop naming it whatever the settings ask for.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereWithoutPolylang() {
		Filters\expectRemoved( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Filters\expectAdded( 'rocket_cache_mandatory_cookies' )->never();
		Functions\expect( 'rocket_clean_domain' )->never();

		$value = [
			'browser'    => 1,
			'force_lang' => 1,
		];

		$this->assertSame(
			$value,
			rocket_detect_browser_language_status_change( $value, [ 'browser' => 1, 'force_lang' => 1 ] )
		);
	}

	/**
	 * Polylang gone with the language still in the file names: the names go back, so the files
	 * written under them go too.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldPurgeWithoutPolylangWhenTheLanguageWasInTheFileName() {
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_domain' )->once();

		$value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->assertSame(
			$value,
			rocket_detect_browser_language_status_change( $value, [ 'browser' => 1, 'force_lang' => 0 ] )
		);
	}

	/**
	 * A save that omits detection is read as off, so neither list names the cookie and the files
	 * stay where the previous settings put them.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldNameTheCookieNowhereWhenTheSaveOmitsDetection() {
		$this->polylang_holds(
			[
				'browser'    => 1,
				'force_lang' => 1,
			]
		);

		Filters\expectRemoved( 'rocket_cache_mandatory_cookies' )->with( 'rocket_add_polylang_mandatory_cookie' );
		Filters\expectRemoved( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_domain' )->never();

		$value = [ 'force_lang' => 1 ];

		$this->assertSame(
			$value,
			rocket_detect_browser_language_status_change( $value, [ 'browser' => 1, 'force_lang' => 1 ] )
		);
	}

	/**
	 * The decision follows the settings being saved, not Polylang's own copy: while this hook runs,
	 * that copy still answers with the settings being replaced.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function testShouldFollowTheSettingsBeingSaved() {
		$old_value = [
			'browser'    => 1,
			'force_lang' => 1,
		];

		$this->polylang_holds( $old_value );

		Filters\expectAdded( 'rocket_cache_dynamic_cookies' )->with( 'rocket_add_polylang_dynamic_cookie' );
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_domain' )->once();

		$value = [
			'browser'    => 1,
			'force_lang' => 0,
		];

		$this->assertSame( $value, rocket_detect_browser_language_status_change( $value, $old_value ) );
	}
}
