<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_clean_files
 * @uses  ::_rocket_get_wp_rocket_cache_path
 * @uses  ::rocket_get_constant
 * @uses  ::rocket_rrmdir
 * @uses  ::_rocket_get_cache_dirs
 * @uses  ::_rocket_normalize_path
 * @uses  ::_rocket_is_windows_fs
 *
 * @group Functions
 * @group Files
 * @group vfs
 * @group Clean
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	public static function tearDownAfterClass():  void {
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
	public function testShouldCleanExpectedFiles( $urls, $config, $expected ) {
		if ( isset( $expected['debug'] ) && $expected['debug'] ) {
			$GLOBALS['debug_fs'] = true;
		}

		if ( empty( $urls ) ) {
			$this->doBailOutTest();
		} else {
			$this->doCleanFilesTest( $urls, $config, $expected );
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

	private function doCleanFilesTest( $urls, $config, $expected ) {
		Filters\expectApplied( 'rocket_url_no_dots' )
			->once()
			->with( false )
			->andReturnFirstArg();
		Actions\expectDone( 'before_rocket_clean_files' )->once()->with( $urls );

		foreach ( $urls as $url ) {
			Actions\expectDone( 'before_rocket_clean_file' )->once()->with( $url );
			$this->stubGetRocketParseUrl( $url );
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
