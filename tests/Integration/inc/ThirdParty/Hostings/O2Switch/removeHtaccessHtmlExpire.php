<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Integration\TestCase;

class Test_RemoveHtaccessHtmlExpire extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($rules, $expected)
    {
        $this->assertSame($expected, apply_filters('rocket_htaccess_mod_expires', $rules));
    }
}
