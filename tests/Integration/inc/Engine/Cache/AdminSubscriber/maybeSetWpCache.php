<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_MaybeSetWpCache extends AdminTestCase
{
    private static $subscriber;
    public static function set_up_before_class()
    {
        $container = apply_filters('rocket_container', null);
        self::$subscriber = $container->get('admin_cache_subscriber');
    }
    public function testShouldCheckActionIsRegistered()
    {
        $this->assertSame(10, has_action('admin_init', [self::$subscriber, 'maybe_set_wp_cache']));
        $this->assertSame(12, has_action('update_option_wp_rocket_settings', [self::$subscriber, 'maybe_set_wp_cache']));
    }
}
