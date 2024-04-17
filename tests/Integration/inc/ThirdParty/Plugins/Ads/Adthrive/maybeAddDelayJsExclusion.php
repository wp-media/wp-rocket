<?php

namespace WP_Rocket\Tests\Integration\Inc\ThirdParty\Plugins\Ads\Adthrive;

use WP_Rocket\Tests\Integration\TestCase;

class Test_MaybeAddDelayJsExclusion extends TestCase
{
    private $plugin_active;
    public function tear_down()
    {
        remove_filter('pre_option_active_plugins', [$this, 'active_plugin']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($settings, $expected)
    {
        $this->plugin_active = $settings['plugin_active'];
        add_filter('pre_option_active_plugins', [$this, 'active_plugin']);
        $this->assertSame($expected, apply_filters('pre_update_option_wp_rocket_settings', $settings['value'], $settings['old_value']));
    }
    public function active_plugin($plugins)
    {
        if (!$this->plugin_active) {
            return $plugins;
        }
        $plugins = [];
        $plugins[] = 'adthrive-ads/adthrive-ads.php';
        return $plugins;
    }
}
