<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_ChangeCacheRejectUriWithPermalink extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
        if (isset($config['permalink'])) {
            $this->set_permalink_structure($config['permalink']['structure']);
        }
        $this->assertSame($expected, apply_filters('pre_update_option_wp_rocket_settings', $config['value'], $config['old_value']));
    }
}
