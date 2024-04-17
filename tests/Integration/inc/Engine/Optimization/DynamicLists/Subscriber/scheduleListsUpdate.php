<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_ScheduleListsUpdate extends TestCase
{
    public function tear_down()
    {
        wp_clear_scheduled_hook('rocket_update_dynamic_lists');
        parent::tear_down();
    }
    public function testShouldDoExpected()
    {
        do_action('init');
        $this->assertNotFalse(wp_next_scheduled('rocket_update_dynamic_lists'));
    }
}
