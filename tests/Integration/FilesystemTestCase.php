<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use StubTrait;
	use VirtualFilesystemTrait;

	public function setUp() {
		$this->initDefaultStructure();

		parent::setUp();

		$this->stubRocketGetConstant();
		$this->redefineRocketDirectFilesystem();
	}
}
