<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPXCloud;

use WP_Rocket\Tests\Integration\TestCase;

class Test_RemoveHtaccessHtmlExpire extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($expected)
    {
        $this->assertSame($expected, apply_filters('after_rocket_htaccess_rules', ''));
    }
}
