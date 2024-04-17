<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_addCdnHelperMessage extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_cdn_helper_addons', $config['addons']));
    }
}
