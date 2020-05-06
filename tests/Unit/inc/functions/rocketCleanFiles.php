<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_clean_files
 * @uses  ::rocket_rrmdir
 * @uses  ::_rocket_get_cache_path_iterator
 * @uses  ::_rocket_get_entries_regex
 *
 * @group Functions
 * @group Files
 * @group vfs
 * @group rocket_clean_files
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( 'vfs://public/wp-content/cache/wp-rocket/' );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['debug_fs'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpectedFiles( $urls, $expected ) {
		if ( isset( $expected['debug'] ) && $expected['debug'] ) {
			$GLOBALS['debug_fs'] = true;
		}

		if ( empty( $urls ) ) {
			$this->doBailOutTest();
		} else {
			$this->doCleanFilesTest( $urls, $expected );
		}

		// Run it.
		rocket_clean_files( $urls );
	}

	private function doBailOutTest() {
		Filters\expectApplied( 'rocket_url_no_dots' )->never();
		Actions\expectDone( 'before_rocket_clean_files' )->never();
		Actions\expectDone( 'before_rocket_clean_file' )->never();
		Functions\expect( 'get_rocket_parse_url' )->never();
		Actions\expectDone( 'after_rocket_clean_file' )->never();
		Functions\expect( 'rocket_rrmdir' )->never();
	}

	private function doCleanFilesTest( $urls, $expected ) {
		Filters\expectApplied( 'rocket_url_no_dots' )
			->once()
			->with( false )
			->andReturnFirstArg();
		Actions\expectDone( 'before_rocket_clean_files' )->once()->with( $urls );

		foreach ( $urls as $url ) {
			Actions\expectDone( 'before_rocket_clean_file' )->once()->with( $url );
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
			Actions\expectDone( 'after_rocket_clean_file' )->once()->with( $url );
		}

		foreach ( array_keys( $expected['cleaned'] ) as $file ) {
			if ( $this->filesystem->is_dir( $file ) ) {
				Functions\expect( 'rocket_rrmdir' )
					->once()
					->with( $file, [], $this->filesystem )
					->andReturnNull();
			} else {
				Functions\expect( 'rocket_rrmdir' )->with( $file )->never();
			}
		}
	}
}
