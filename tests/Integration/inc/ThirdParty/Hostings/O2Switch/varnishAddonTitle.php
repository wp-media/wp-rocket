<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Integration\TestCase;

class Test_VarnishAddonTitle extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($settings, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_varnish_field_settings', $settings));
    }
}
