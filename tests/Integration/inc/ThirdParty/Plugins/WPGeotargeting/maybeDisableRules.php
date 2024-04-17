<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\WPGeotargeting;

use WP_Rocket\Tests\Integration\TestCase;

class Test_maybeDisableRules extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        $this->assertSame($expected, apply_filters('geot/pass_basic_rules', $config['bool'], $config['opts'], $config['current_url']));
    }
}
