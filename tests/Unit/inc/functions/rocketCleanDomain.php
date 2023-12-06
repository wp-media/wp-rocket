<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_clean_domain
 * @uses  ::get_rocket_i18n_home_url
 * @uses  ::get_rocket_i18n_to_preserve
 * @uses  ::get_rocket_i18n_uri
 * @uses  ::get_rocket_parse_url
 * @uses  ::_rocket_get_wp_rocket_cache_path
 * @uses  ::rocket_get_constant
 * @uses  ::rocket_rrmdir
 * @uses  ::_rocket_get_cache_dirs
 * @uses  ::_rocket_normalize_path
 * @uses  ::_rocket_is_windows_fs
 *
 * @group Functions
 * @group Files
 * @group Clean
 */
class Test_RocketCleanDomain extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanDomain.php';

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		// Clean out the cached dirs before we leave this test class.
		_rocket_get_cache_dirs( '', '', true );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['debug_fs'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanSingleDomain( $i18n, $expected, $config ) {
		$dirsToPreserve = $i18n['dirs_to_preserve'];
		$url            = $expected['rocket_clean_domain_urls'][0];
		$lang           = $i18n['lang'];
		$cache_path     = _rocket_get_wp_rocket_cache_path();

		if ( ! is_null( $config['get_rocket_i18n_uri'] ) ) {
			Functions\expect( 'get_rocket_i18n_uri' )->once()->andReturn( $config['get_rocket_i18n_uri'] );
		} else {
			Functions\expect( 'get_rocket_i18n_uri' )->never();
		}

		if ( ! is_null( $config['get_rocket_i18n_home_url'] ) ) {
			Functions\expect( 'get_rocket_i18n_home_url' )
				->once()
				->with( $lang )
				->andReturn( $config['get_rocket_i18n_home_url'] );
		} else {
			Functions\expect( 'get_rocket_i18n_home_url' )->never();
		}

		Functions\expect( 'get_rocket_i18n_to_preserve' )
			->once()
			->with( $lang, $cache_path )
			->andReturn( $dirsToPreserve );

		$this->stubGetRocketParseUrl( $url );

		foreach ( $config['rocket_rrmdir'] as $dir ) {
			if ( $this->filesystem->is_dir( $dir ) ) {
				$dir = rtrim( $dir, '/' );
				Functions\expect( 'rocket_rrmdir' )
					->once()
					->with( $dir, $dirsToPreserve, $this->filesystem )
					->andReturnNull();
			} else {
				Functions\expect( 'rocket_rrmdir' )->never();
			}
		}

		Filters\expectApplied( 'rocket_clean_domain_urls' )
			->once()
			->with( $expected['rocket_clean_domain_urls'], $lang )
			->andReturnFirstArg();
		Filters\expectApplied( 'rocket_url_no_dots' )
			->once()
			->with( false )
			->andReturnFirstArg();

		Actions\expectDone( 'before_rocket_clean_domain' )
			->once()
			->with( $config['root'], $lang, $url );
		Actions\expectDone( 'after_rocket_clean_domain' )
			->once()
			->with( $config['root'], $lang, $url );
		Actions\expectDone( 'rocket_after_clean_domain' )
			->once()
			->with( $lang, $expected['rocket_clean_domain_urls'] );

		// Run it.
		rocket_clean_domain( $lang );
	}
}
