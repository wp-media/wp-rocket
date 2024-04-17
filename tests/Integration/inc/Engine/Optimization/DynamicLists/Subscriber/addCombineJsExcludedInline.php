<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_AddCombineJsExcludedInline extends TestCase
{
    public function tear_down()
    {
        delete_transient('wpr_dynamic_lists');
        $this->restoreWpHook('rocket_excluded_inline_js_content');
        parent::tear_down();
    }
    public function set_up()
    {
        parent::set_up();
        $this->unregisterAllCallbacksExcept('rocket_excluded_inline_js_content', 'add_combine_js_excluded_inline');
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($original, $list, $expected)
    {
        set_transient('wpr_dynamic_lists', $list, HOUR_IN_SECONDS);
        $this->assertSame($expected, apply_filters('rocket_excluded_inline_js_content', $original));
    }
}
