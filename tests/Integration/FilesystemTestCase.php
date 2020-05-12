<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use VirtualFilesystemTrait;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		if ( ! defined( 'WP_ROCKET_RUNNING_VFS' ) ) {
			define( 'WP_ROCKET_RUNNING_VFS', true );
		}
	}

	public function setUp() {
		$this->initDefaultStructure();

		parent::setUp();

		$this->redefineRocketDirectFilesystem();
	}
}
