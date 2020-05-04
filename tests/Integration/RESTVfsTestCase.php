<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\RESTVfsTestCase as BaseTestCase;

abstract class RESTVfsTestCase extends BaseTestCase {
	use VirtualFilesystemTrait;

	public function setUp() {
		$this->initDefaultStructure();

		parent::setUp();

		$this->redefineRocketDirectFilesystem();
	}
}
