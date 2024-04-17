<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Deactivation\DeactivationIntent;

use WP_Rocket\Tests\Integration\TestCase;

class Test_AddModalAssets extends TestCase
{
    public function tear_down()
    {
        delete_option('wp_rocket_hide_deactivation_form');
        delete_transient('rocket_hide_deactivation_form');
        set_current_screen('front');
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected)
    {
        $this->set_snooze($config);
        set_current_screen($config['hook']);
        do_action('admin_enqueue_scripts', $config['hook']);
        if (!$expected) {
            $this->assertFalse(wp_script_is('micromodal'));
            $this->assertFalse(wp_style_is('wpr-modal'));
        } else {
            $this->assertTrue(wp_script_is('micromodal'));
            $this->assertTrue(wp_style_is('wpr-modal'));
        }
    }
    private function set_snooze($config)
    {
        if (!empty($config['option'])) {
            add_option('wp_rocket_hide_deactivation_form', $config['option']);
        }
        if (!empty($config['transient'])) {
            set_transient('rocket_hide_deactivation_form', $config['transient'], DAY_IN_SECONDS);
        }
    }
}
