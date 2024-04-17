<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization;

use WP_Rocket\Tests\Integration\TestCase;

class Test_ExcludeRocketLazyLoadScript extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($excluded, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_delay_js_exclusions', $excluded));
    }
}
