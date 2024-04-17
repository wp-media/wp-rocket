<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

class Test_GetRocketOption extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpectedOptionValue($option, $default, $expected)
    {
        $this->assertSame($expected, get_rocket_option($option, $default));
    }
}
