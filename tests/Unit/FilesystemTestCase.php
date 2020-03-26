<?php

namespace WP_Rocket\Tests\Unit;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {

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
}
