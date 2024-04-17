<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\DomainChange\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

class Test_addRegenerateConfigurationAction extends TestCase
{
    /**
     * @dataProvider configTestData
     * @group AdminOnly
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        if (key_exists('action', $config['args']) && 'regenerate_configuration' == $config['args']['action']) {
            Functions\expect('wp_nonce_url')->with($config['admin_url'], 'rocket_regenerate_configuration')->andReturn($config['nonce']);
        }
        $this->assertSame($expected, apply_filters('rocket_notice_args', $config['args']));
    }
}
