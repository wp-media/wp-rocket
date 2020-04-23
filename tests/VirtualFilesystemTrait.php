<?php

namespace WP_Rocket\Tests;

use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream;

trait VirtualFilesystemTrait {
	protected $original_entries = [];
	protected $shouldNotClean   = [];

	protected function initDefaultStructure() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		if ( array_key_exists( 'structure', $this->config ) ) {
			return;
		}

		$this->config['structure'] = require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php';
	}

	protected function redefineRocketDirectFilesystem() {
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

	protected function checkCleanedIsDeleted( array $shouldClean, $dump_results = false ) {
		foreach ( $shouldClean as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				$actual = $this->filesystem->exists( $dir );
				if ( $dump_results && false !== $actual ) {
					var_dump( $this->filesystem->getFilesListing( $dir ) );
				}
				$this->assertFalse( $actual );
			} else {
				$this->shouldNotClean[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$entries = $this->filesystem->getFilesListing( $dir );
				if ( $dump_results ) {
					var_dump( $entries );
				}
				$this->assertSame( $contents, $entries );
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
