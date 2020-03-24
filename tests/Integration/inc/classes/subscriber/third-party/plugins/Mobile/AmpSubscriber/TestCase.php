<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\third_party\plugins\Optimization\AMP;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;
use WP_Rocket\Subscriber\Third_Party\Plugins\Optimization\AMP;

class TestCase extends BaseTestCase {
	protected function setSettings( $option, $value ) {
		\AMP_Options_Manager::update_option( $option, $value);
	}
}
