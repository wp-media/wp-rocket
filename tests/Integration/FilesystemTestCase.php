<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream;
use WPMedia\PHPUnit\Integration\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	protected $original_entries = [];

	public function setUp() {
		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
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
		$entries = [];
		foreach ( $shouldNotClean as $entry => $scanDir ) {
			$entries[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$entries = array_merge( $entries, $this->filesystem->getListing( $entry ) );
			}
		}

		return $entries;
	}

	protected function checkCleanedIsDeleted( array $shouldClean ) {
		foreach ( $shouldClean as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				$this->assertFalse( $this->filesystem->exists( $dir ) );
			} else {
				$shouldNotClean[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$this->assertSame( $contents, $this->filesystem->getFilesListing( $dir ) );
			}
		}
	}

	protected function checkNonCleanedExist( $shouldNotClean ) {
		$entriesAfterCleaning = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$actual               = array_diff( $entriesAfterCleaning, $shouldNotClean );
		$this->assertEmpty( $actual );
	}
}
