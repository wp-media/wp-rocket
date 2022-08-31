<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use AMP_Options_Manager;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use DBTrait;

	protected $path_to_test_data;

	public static function set_up_before_class() {
		self::installFresh();
		parent::set_up_before_class();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	protected function setSettings( $option, $value ) {
		AMP_Options_Manager::update_option( $option, $value);
	}

	public function ampDataProvider() {
		return require_once WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/ThirdParty/Plugins/Optimization/AMP/{$this->path_to_test_data}";
	}
}
