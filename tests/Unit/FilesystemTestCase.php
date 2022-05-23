<?php

namespace WP_Rocket\Tests\Unit;

use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use StubTrait;
	use VirtualFilesystemTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->initDefaultStructure();
		$this->init();

		$this->stubRocketGetConstant();
		$this->stubWpNormalizePath();
		$this->redefineRocketDirectFilesystem();
	}
}
