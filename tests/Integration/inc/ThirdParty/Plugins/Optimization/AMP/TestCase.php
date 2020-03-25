<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use AMP_Options_Manager;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected function setSettings( $option, $value ) {
		AMP_Options_Manager::update_option( $option, $value);
	}
}
