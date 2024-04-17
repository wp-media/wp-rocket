<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_addOptionSafemode extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_safe_mode_reset_options', $config['options']));
    }
}
