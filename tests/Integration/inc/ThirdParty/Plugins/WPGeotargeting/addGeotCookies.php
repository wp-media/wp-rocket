<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\WPGeotargeting;

use WP_Rocket\Tests\Integration\TestCase;

class Test_addGeotCookies extends TestCase
{
    protected $enabled_cookies = [];
    public function set_up()
    {
        parent::set_up();
        add_filter('rocket_geotargetingwp_enabled_cookies', [$this, 'enabled_cookies']);
    }
    public function tear_down()
    {
        remove_filter('rocket_geotargetingwp_enabled_cookies', [$this, 'enabled_cookies']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        $this->enabled_cookies = $config['enabled_cookies'];
        $this->assertSame($expected, apply_filters($config['hook'], $config['cookies']));
    }
    public function enabled_cookies()
    {
        return $this->enabled_cookies;
    }
}
