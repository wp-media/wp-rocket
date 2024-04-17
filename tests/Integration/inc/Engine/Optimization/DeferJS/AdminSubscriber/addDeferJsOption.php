<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DeferJS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_AddDeferJsOption extends TestCase
{
    public function set_up()
    {
        parent::set_up();
        $this->unregisterAllCallbacksExcept('rocket_first_install_options', 'add_defer_js_option');
    }
    public function tear_down()
    {
        parent::tear_down();
        $this->restoreWpHook('rocket_first_install_options');
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpectedForFirstInstallOptions($input, $expected)
    {
        $options = isset($input['options']) ? $input['options'] : [];
        $actual = apply_filters('rocket_first_install_options', $options);
        $this->assertSame($expected, $actual);
    }
}
