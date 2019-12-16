<?php
namespace WP_Rocket\Tests\Unit\Buffer\Tests;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Buffer\Tests;

/**
 * @group Buffer
 */
class TestIsSpeedTool extends TestCase {
    /**
     * @covers ::is_speed_tool
     * @author Remy Perona
     */
    public function testShouldReturnTrueWhenLighthouse() {
        $config = $this->createMock('WP_Rocket\Buffer\Config');

        $config->method('get_server_input')
        ->willReturn(
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36(KHTML, like Gecko) Chrome/61.0.3116.0 Safari/537.36 Chrome-Lighthouse'
        );

        $tests = new Tests( $config );

        $this->assertTrue(
            $tests->is_speed_tool()
        );
    }

    public function testShouldReturnTrueWhenPingdom() {
        $config = $this->createMock('WP_Rocket\Buffer\Config');

        $config->method('get_server_input')
        ->willReturn(
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/61.0.3163.100 Chrome/61.0.3163.100 Safari/537.36 PingdomPageSpeed/1.0 (pingbot/2.0; +http://www.pingdom.com/)'
        );

        $tests = new Tests( $config );

        $this->assertTrue(
            $tests->is_speed_tool()
        );
    }
}
