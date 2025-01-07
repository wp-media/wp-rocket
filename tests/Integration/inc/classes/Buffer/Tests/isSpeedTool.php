<?php

namespace WP_Rocket\Tests\Integration\inc\classes\Buffer\Tests;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Buffer\Config;

/**
 * Test class covering \WP_Rocket\Buffer\Tests::is_speed_tool
 * @uses   \WP_Rocket\Buffer\Tests::get_ip
 * @uses   \WP_Rocket\Buffer\Config
 * @group  Buffer
 */
class Test_IsSpeedTool extends TestCase {

	public function testShouldReturnTrueWhenLighthouse() {
		// Grab the current Config::$config_dir_path value. We'll restore it when we're done.
		$config_dir_path = $this->get_reflective_property( 'config_dir_path', 'WP_Rocket\Buffer\Config' );
		// Set the Config::$config_dir_path value to `null`.
		$this->set_reflective_property( null, 'config_dir_path', 'WP_Rocket\Buffer\Config' );

		$config = new Config(
			[
				'config_dir_path' => 'wp-content/wp-rocket/config',
				'server'          => [
					'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36(KHTML, like Gecko) Chrome/61.0.3116.0 Safari/537.36 Chrome-Lighthouse',
				],
			]
		);

		$tests = new Tests( $config );

		$this->assertTrue( $tests->is_speed_tool() );

		// Restore the Config::$config_dir_path.
		$this->set_reflective_property( $config_dir_path, 'config_dir_path', 'WP_Rocket\Buffer\Config' );
	}
}
