<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\OptionSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_SanitizeOptions extends TestCase
{
    private static $admin_settings;
    public static function set_up_before_class()
    {
        $container = apply_filters('rocket_container', null);
        self::$admin_settings = $container->get('settings');
    }
    public function set_up()
    {
        parent::set_up();
        $this->unregisterAllCallbacksExcept('rocket_input_sanitize', 'sanitize_options', 14);
    }
    public function tear_down()
    {
        parent::tear_down();
        $this->restoreWpHook('rocket_input_sanitize');
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected)
    {
        $result = apply_filters('rocket_input_sanitize', $config['input'], self::$admin_settings);
        $this->assertArrayHasKey('remove_unused_css', $result);
        $this->assertArrayHasKey('remove_unused_css_safelist', $result);
        $this->assertSame($expected['remove_unused_css'], $result['remove_unused_css']);
        $this->assertSame(array_values($expected['remove_unused_css_safelist']), array_values($result['remove_unused_css_safelist']));
    }
}
