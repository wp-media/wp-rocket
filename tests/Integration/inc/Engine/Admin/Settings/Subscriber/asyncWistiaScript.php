<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_AsyncWistiaScript extends AdminTestCase
{
    /**
     * @dataProvider providerTestData
     */
    public function testShouldMaybeAsyncScript($tag, $handle, $expected)
    {
        $this->assertSame($expected, apply_filters('script_loader_tag', $tag, $handle));
    }
    public function providerTestData()
    {
        $dir = dirname(__DIR__) . '/Page';
        return $this->getTestData($dir, 'asyncWistiaScript');
    }
}
