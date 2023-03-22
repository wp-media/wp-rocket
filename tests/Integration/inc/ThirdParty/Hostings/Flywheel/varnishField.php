<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Flywheel::varnish_field
 *
 * @group  Flywheel
 * @group  ThirdParty
 */
class Test_varnishField extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, apply_filters('rocket_varnish_field_settings', $config));
    }
}
