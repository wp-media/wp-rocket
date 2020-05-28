<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected $path_to_test_data;

	protected function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( '' );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	public function providerTestData() {
		$config = require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/common/' . $this->path_to_test_data;

		return $config['test_data'];
	}
}
