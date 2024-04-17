<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

class Test_RunRocketAfterWPEngine extends AdminTestCase
{
    use CapTrait;
    protected $user_id = 0;
    private static $subscriber;
    private static $wpengine;
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::hasAdminCapBeforeClass();
        self::setAdminCap();
        $container = apply_filters('rocket_container', null);
        self::$subscriber = $container->get('admin_cache_subscriber');
        self::$wpengine = $container->get('wpengine');
    }
    public static function tear_down_after_class()
    {
        parent::tear_down_after_class();
        self::resetAdminCap();
    }
    public function set_up()
    {
        parent::set_up();
        $this->user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->user_id);
    }
    public function testShouldRunRocketAfterWPEngine()
    {
        Functions\expect('check_admin_referer')->once()->andReturn(true);
        Functions\expect('run_rocket_bot')->once();
        Functions\expect('run_rocket_sitemap_preload')->once();
        do_action('admin_init');
    }
}
