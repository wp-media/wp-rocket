<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use VirtualFilesystemTrait;

	public function setUp() {
		parent::setUp();

		$this->redefineRocketDirectFilesystem();
	}
}
