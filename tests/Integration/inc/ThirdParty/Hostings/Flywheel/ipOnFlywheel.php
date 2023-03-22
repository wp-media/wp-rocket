<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Flywheel::ip_on_flywheel
 *
 * @group  Flywheel
 * @group  ThirdParty
 */
class Test_ipOnFlywheel extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, apply_filters('rocket_varnish_ip', $config));
    }
}
