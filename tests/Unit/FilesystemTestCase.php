<?php

namespace WP_Rocket\Tests\Unit;

use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	protected $original_entries = [];
	protected $shouldNotClean   = [];

	public function setUp() {
		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	protected function setUpOriginalEntries() {
		$this->original_entries = array_merge( $this->original_files, $this->original_dirs );
		$this->original_entries = array_filter( $this->original_entries );
		sort( $this->original_entries );
	}

	protected function stripVfsRoot( $path ) {
		$search = vfsStream::SCHEME . "://{$this->rootVirtualDir}";
		$search = rtrim( $search, '/\\' ) . '/';

		return str_replace( $search, '', $path );
	}

	protected function getShouldNotCleanEntries( array $shouldNotClean ) {
		$this->shouldNotClean = [];
		foreach ( $shouldNotClean as $entry => $scanDir ) {
			$this->shouldNotClean[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$this->shouldNotClean = array_merge( $this->shouldNotClean, $this->filesystem->getListing( $entry ) );
			}
		}
	}

	protected function checkCleanedIsDeleted( array $shouldClean ) {
		foreach ( $shouldClean as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				if ( false !== $this->filesystem->exists( $dir ) ) {
					echo "\n {$dir} \n";
				}
				$this->assertFalse( $this->filesystem->exists( $dir ) );

			} else {
				$this->shouldNotClean[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$this->assertSame( $contents, $this->filesystem->getFilesListing( $dir ) );
			}
		}
	}

	protected function checkNonCleanedExist( $dump_results = false ) {
		$entriesAfterCleaning = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$actual               = array_diff( $entriesAfterCleaning, $this->shouldNotClean );
		if ( $dump_results ) {
			var_dump( $actual );
		}
		$this->assertEmpty( $actual );
	}

	public function getPathToFixturesDir() {
		return WP_ROCKET_TESTS_FIXTURES_DIR;
	}

	public function getDefaultVfs() {
		return [
			'wp-admin'      => [],
			'wp-content'    => [
				'cache'            => [
					'busting'      => [
						1 => [],
					],
					'critical-css' => [],
					'min'          => [],
					'wp-rocket'    => [
						'index.html' => '',
					],
				],
				'mu-plugins'       => [],
				'plugins'          => [
					'wp-rocket' => [],
				],
				'themes'           => [
					'twentytwenty' => [],
				],
				'uploads'          => [],
				'wp-rocket-config' => [],
			],
			'wp-includes'   => [],
			'wp-config.php' => '',
		];
	}
}
