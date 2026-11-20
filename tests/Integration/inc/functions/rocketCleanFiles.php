<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering ::rocket_clean_files
 * @uses  ::rocket_rrmdir
 * @uses  ::_rocket_get_cache_dirs
 *
 * @group Functions
 * @group Files
 * @group vfs
 * @group Clean
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';

	private $ignored_params = [];

	public function tear_down() {
		remove_filter( 'rocket_cache_ignored_parameters', [ $this, 'set_ignored_params' ], PHP_INT_MAX );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpectedFiles( $urls, $config,  $expected ) {
		if ( str_contains( implode( '', $config['create'] ?? [] ), '%' ) ) {
			$this->markTestSkipped( 'vfsStream decodes percent-encoded characters in paths; covered by the unit test.' );
		}

		$this->ignored_params = $config['ignored_params'] ?? [];
		add_filter( 'rocket_cache_ignored_parameters', [ $this, 'set_ignored_params' ], PHP_INT_MAX );

		foreach ( $config['create'] ?? [] as $file ) {
			$dir = dirname( $file );

			if ( ! $this->filesystem->is_dir( $dir ) ) {
				$this->filesystem->mkdir( $dir );
			}

			$this->filesystem->put_contents( $file, '' );
		}

		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		if ( isset( $expected['debug'] ) && $expected['debug'] ) {
			$GLOBALS['debug_fs'] = true;
		}

		// Run it.
		rocket_clean_files( $urls );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}

	public function set_ignored_params() {
		return $this->ignored_params;
	}
}
