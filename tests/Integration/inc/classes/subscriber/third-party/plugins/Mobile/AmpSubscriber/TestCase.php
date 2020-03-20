<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\third_party\plugins\Mobile\Amp_Subscriber;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;
use WP_Rocket\Subscriber\Third_Party\Plugins\Mobile\Amp_Subscriber;

class TestCase extends BaseTestCase {
	protected function setSettings( $option, $value ) {
		\AMP_Options_Manager::update_option( $option, $value);
	}
}
