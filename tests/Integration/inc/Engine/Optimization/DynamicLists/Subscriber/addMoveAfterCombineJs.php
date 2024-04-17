<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_AddMoveAfterCombineJs extends TestCase
{
    public function tear_down()
    {
        delete_transient('wpr_dynamic_lists');
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($original, $list, $expected)
    {
        set_transient('wpr_dynamic_lists', $list, HOUR_IN_SECONDS);
        $this->assertSame($expected, apply_filters('rocket_move_after_combine_js', $original));
    }
}
