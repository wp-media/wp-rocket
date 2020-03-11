<?php

namespace WP_Rocket\Tests\Unit\inc\classes\admin\settings\Settings;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Admin\Settings::sanitize_callback
 * @group  Settings
 */
class Test_SanitizeCallback extends TestCase {
	private $options;
	private $settings;

	public function setUp() {
		parent::setUp();

		$this->options  = $this->createMock( Options::class );
		$this->settings = new Settings( $this->options );
	}

	public function testShouldSanitizeDNSPrefetchEntries() {

	}
}
