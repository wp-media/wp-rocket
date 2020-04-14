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
 * @uses  ::rocket_get_constant
 * @uses  ::rocket_rrmdir
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanDomain extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanDomain.php';

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( 'vfs://public/wp-content/cache/wp-rocket/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanSingleDomain( $i18n, $expected, $config ) {
		$dirsToPreserve = $i18n['dirs_to_preserve'];
		$url            = $expected['rocket_clean_domain_urls'][0];
		$lang           = $i18n['lang'];

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
			->with( $lang )
			->andReturn( $dirsToPreserve );
		Functions\expect( 'get_rocket_parse_url' )
			->once()
			->with( $url )
			->andReturnUsing(
				function ( $url ) {
					return array_merge(
						[
							'host'   => '',
							'path'   => '',
							'scheme' => '',
							'query'  => '',
						],
						parse_url( $url )
					);
				}
			);

		foreach ( $config['rocket_rrmdir'] as $entry ) {
			Functions\expect( 'rocket_rrmdir' )
				->once()
				->with( $entry, $dirsToPreserve )
				->andReturnNull();
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

		// Run it.
		rocket_clean_domain( $lang );
	}
}
