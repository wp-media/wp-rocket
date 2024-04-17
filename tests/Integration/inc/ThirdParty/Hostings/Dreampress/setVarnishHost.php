<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Integration\TestCase;

class Test_VarnishHost extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($hosts, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_varnish_ip', $hosts));
    }
}
