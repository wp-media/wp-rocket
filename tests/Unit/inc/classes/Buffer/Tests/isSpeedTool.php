<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Tests;

use Mockery;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Tests::is_speed_tool
 * @uses   \WP_Rocket\Buffer\Tests::get_ip
 * @group  Buffer
 */
class Test_IsSpeedTool extends TestCase {

	private function getConfigMock( $return_value ) {
		$config = Mockery::mock( Config::class );
		$config->shouldReceive( 'get_server_input' )->andReturn( $return_value );

		return $config;
	}

	public function testShouldReturnTrueWhenLighthouse() {
		$config = $this->getConfigMock( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36(KHTML, like Gecko) Chrome/61.0.3116.0 Safari/537.36 Chrome-Lighthouse' );
		$tests  = new Tests( $config );

		$this->assertTrue( $tests->is_speed_tool() );
	}

	public function testShouldReturnTrueWhenPingdom() {
		$config = $this->getConfigMock( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/61.0.3163.100 Chrome/61.0.3163.100 Safari/537.36 PingdomPageSpeed/1.0 (pingbot/2.0; +http://www.pingdom.com/)' );
		$tests  = new Tests( $config );

		$this->assertTrue( $tests->is_speed_tool() );
	}
}
