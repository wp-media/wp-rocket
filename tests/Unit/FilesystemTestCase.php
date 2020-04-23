<?php

namespace WP_Rocket\Tests\Unit;

use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use VirtualFilesystemTrait;

	public function setUp() {
		$this->initDefaultStructure();

		parent::setUp();

		$this->redefineRocketDirectFilesystem();
	}
}
