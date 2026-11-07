<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::get_rocket_i18n_to_preserve
 *
 * @uses  ::rocket_has_i18n
 * @uses  ::get_rocket_i18n_code
 * @uses  ::get_rocket_i18n_home_url
 * @uses  ::get_rocket_parse_url
 *
 * @group Functions
 * @group i18n
 */
class Test_GetRocketI18nToPreserve extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $current_lang, $config, $expected, $mocks ) {
		if ( array_key_exists( 'rocket_has_i18n', $mocks ) && is_null( $mocks['rocket_has_i18n'] ) ) {
			Functions\expect( 'rocket_has_i18n' )->never();
		} else {
			Functions\expect( 'rocket_has_i18n' )->once()->andReturn( $config['rocket_has_i18n'] );
		}

		if ( is_null( $mocks['get_rocket_i18n_code'] ) ) {
			Functions\expect( 'get_rocket_i18n_code' )->never();
		} else {
			Functions\expect( 'get_rocket_i18n_code' )->once()->andReturn( $mocks['get_rocket_i18n_code'] );
		}

		if ( is_null( $mocks['get_rocket_i18n_home_url'] ) ) {
			Functions\expect( 'get_rocket_i18n_home_url' )->never();
			Functions\expect( 'get_rocket_parse_url' )->never();
		} else {
			foreach ( $mocks['get_rocket_i18n_home_url'] as $mock_lang => $i18n_home_url ) {
				Functions\expect( 'get_rocket_i18n_home_url' )
					->once()
					->with( $mock_lang )
					->andReturn( $i18n_home_url );
			}

			foreach ( $mocks['get_rocket_parse_url'] as $i18n_home_url => $parse_url ) {
				Functions\expect( 'get_rocket_parse_url' )
					->once()
					->with( $i18n_home_url )
					->andReturn( $parse_url );
			}

			Functions\expect( 'rocket_get_constant' )
				->atLeast()
				->with( 'WP_ROCKET_CACHE_PATH' )
				->andReturn( 'vfs://public/wp-content/cache/wp-rocket/' );

			Filters\expectApplied( 'rocket_langs_to_preserve' )->once()->with( $expected );
		}

		$this->assertSame( $expected, get_rocket_i18n_to_preserve( $current_lang ) );
	}

	public function testShouldEscapeCachePathAndHostInPreservePatterns() {
		$cache_path = '/releases/2024-09-02T11:32:23+0000/wp-content/cache/wp-rocket/';

		Functions\when( 'rocket_has_i18n' )->justReturn( true );
		Functions\when( 'get_rocket_i18n_code' )->justReturn( [ 'en' ] );
		Functions\when( 'get_rocket_i18n_home_url' )->justReturn( 'https://example.org/en/' );
		Functions\when( 'get_rocket_parse_url' )->justReturn( [ 'host' => 'example.org', 'path' => '/en/' ] );
		Functions\when( '_rocket_is_windows_fs' )->justReturn( false );
		Filters\expectApplied( 'rocket_langs_to_preserve' )->once();

		$patterns = get_rocket_i18n_to_preserve( 'fr', $cache_path );

		// The pattern used by rocket_rrmdir() must match the real directory of the preserved language.
		$subject = '/releases/2024-09-02T11:32:23+0000/wp-content/cache/wp-rocket/example.org/en';
		$this->assertSame( 1, preg_match( '#^' . $patterns[0] . '$#', $subject ) );
	}

	public function testShouldEscapeWindowsCachePathInPreservePatterns() {
		$cache_path = 'C:\releases\2024-09-02T11:32:23+0000\wp-content\cache\wp-rocket\\';

		Functions\when( '_rocket_is_windows_fs' )->justReturn( true );
		Functions\when( 'rocket_has_i18n' )->justReturn( true );
		Functions\when( 'get_rocket_i18n_code' )->justReturn( [ 'en' ] );
		Functions\when( 'get_rocket_i18n_home_url' )->justReturn( 'https://example.org/en/' );
		Functions\when( 'get_rocket_parse_url' )->justReturn( [ 'host' => 'example.org', 'path' => '/en/' ] );
		Filters\expectApplied( 'rocket_langs_to_preserve' )->once();

		$patterns = get_rocket_i18n_to_preserve( 'fr', $cache_path );

		// Explicit backslash-separated subject, as the filesystem path looks on Windows.
		$subject = 'C:\releases\2024-09-02T11:32:23+0000\wp-content\cache\wp-rocket\example.org\en';
		$this->assertSame( 1, preg_match( '#^' . $patterns[0] . '$#', $subject ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
