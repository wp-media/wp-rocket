<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\VirtualFilesystemDirect;
use WPMedia\PHPUnit\Integration\TestCase;

abstract class FilesystemTestCase extends TestCase {

	/**
	 * Overwrite with the structure for this test. Gets merged with the default structure.
	 *
	 * @var array
	 */
	protected $structure = [];

	/**
	 * Instance of the virtual filesystem.
	 *
	 * @var VirtualFilesystemDirect
	 */
	protected $filesystem;

	/**
	 * URL to the root directory of the virtual filesystem.
	 *
	 * @var string
	 */
	protected $rootVirtualUrl;

	/**
	 * Prepares the test environment before each test.
	 */
	public function setUp() {
		$structure            = array_merge( $this->getDefaultVfs(), $this->structure );
		$this->filesystem     = new VirtualFilesystemDirect( 'wp-content', $structure, 0777 );
		$this->rootVirtualUrl = $this->filesystem->getUrl( 'wp-content' );

		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	/**
	 * Gets the default virtual wp-content directory filesystem structure.
	 *
	 * @return array
	 */
	private function getDefaultVfs() {
		return [
			'cache'            => [
				'busting'      => [
					'1' => [],
				],
				'critical-css' => [],
				'min'          => [],
				'wp-rocket'    => [
					'index.html' => '',
				],
			],
			'mu-plugins'       => [],
			'plugins'          => [],
			'themes'           => [],
			'uploads'          => [],
			'wp-rocket-config' => [],
		];
	}
}
