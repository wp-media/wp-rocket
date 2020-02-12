<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase as WPMediaVirtualFilesystemTestCase;

abstract class FilesystemTestCase extends WPMediaVirtualFilesystemTestCase {
	protected $cache_path;

	public function setUp() {
		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
		$this->cache_path = $this->filesystem->getUrl( 'wp-rocket' );
	}
}
