<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use WP_Rocket\Tests\Integration\TestCase;

class Test_VarnishField extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($settings, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_varnish_field_settings', $settings));
    }
}
