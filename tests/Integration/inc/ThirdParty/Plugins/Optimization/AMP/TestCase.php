<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use AMP_Options_Manager;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected $path_to_test_data;

	protected function setSettings( $option, $value ) {
		AMP_Options_Manager::update_option( $option, $value);
	}

	public function ampDataProvider() {
		return require_once WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/ThirdParty/Plugins/Optimization/AMP/{$this->path_to_test_data}";
	}
}
