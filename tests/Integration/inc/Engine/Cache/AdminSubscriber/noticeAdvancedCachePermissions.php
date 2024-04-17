<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_NoticeAdvancedCachePermissions extends AdminTestCase
{
    private static $subscriber;
    public static function set_up_before_class()
    {
        $container = apply_filters('rocket_container', null);
        self::$subscriber = $container->get('admin_cache_subscriber');
    }
    public function testShouldCheckActionIsRegistered()
    {
        $this->assertSame(10, has_action('admin_notices', [self::$subscriber, 'notice_advanced_cache_permissions']));
    }
}
