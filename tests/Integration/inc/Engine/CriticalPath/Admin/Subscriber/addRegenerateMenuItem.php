<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_AddRegenerateMenuItem extends AdminTestCase
{
    use ProviderTrait;
    protected static $provider_class = 'Admin';
    protected $user_id = 0;
    private $filter;
    private $option;
    public function set_up()
    {
        parent::set_up();
        $this->setRoleCap('administrator', 'rocket_regenerate_critical_css');
        add_filter('show_admin_bar', [$this, 'return_true']);
        add_filter('pre_get_rocket_option_async_css', [$this, 'async_css']);
        add_filter('do_rocket_critical_css_generation', [$this, 'filter_generation']);
    }
    public function tear_down()
    {
        $this->removeRoleCap('administrator', 'rocket_regenerate_critical_css');
        remove_filter('show_admin_bar', [$this, 'return_true']);
        remove_filter('pre_get_rocket_option_async_css', [$this, 'async_css']);
        remove_filter('do_rocket_critical_css_generation', [$this, 'filter_generation']);
        unset($_SERVER['REQUEST_URI']);
        parent::tear_down();
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldDoExpected($cap, $admin, $option, $filter, $request, $expected)
    {
        if ($cap) {
            $this->setCurrentUser('administrator');
        } else {
            $this->setCurrentUser('editor');
        }
        $this->option = $option;
        $this->filter = $filter;
        $_SERVER['REQUEST_URI'] = $request;
        $wp_admin_bar = $this->initAdminBar();
        if (false === $admin) {
            set_current_screen('front');
        }
        Functions\when('wp_create_nonce')->justReturn('wp_rocket_nonce');
        // Fire the hook.
        do_action_ref_array('admin_bar_menu', [$wp_admin_bar]);
        // Check the results.
        $actual = $wp_admin_bar->get_node('regenerate-critical-path');
        if (false === $expected) {
            $this->assertNull($actual);
        } else {
            $this->assertEquals($expected, $actual);
        }
    }
    public function async_css()
    {
        return $this->option;
    }
    public function filter_generation()
    {
        return $this->filter;
    }
    protected function initAdminBar()
    {
        global $wp_admin_bar;
        set_current_screen('edit.php');
        $this->assertTrue(_wp_admin_bar_init());
        $this->assertTrue(is_admin_bar_showing());
        $this->assertInstanceOf('WP_Admin_Bar', $wp_admin_bar);
        return $wp_admin_bar;
    }
}
