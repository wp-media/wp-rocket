<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use VirtualFilesystemTrait;

	public function setUp() {
		$this->initDefaultStructure();

		parent::setUp();

		// Set the constant to true when running the virtual filesystem.
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_RUNNING_VFS' )->andReturn( true );

		$this->redefineRocketDirectFilesystem();
	}

	public function tearDown() {
		parent::tearDown();

		// Reset to the default of false, i.e. to ensure this constant does not impact other tests.
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_RUNNING_VFS' )->andReturn( false );
	}
}
