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
}
