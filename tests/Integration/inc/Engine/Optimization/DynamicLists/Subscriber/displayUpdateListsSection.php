<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_DisplayUpdateListsSection extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($role, $expected)
    {
        wp_set_current_user(static::factory()->user->create(['role' => $role]));
        if (is_null($expected)) {
            $this->assertStringNotContainsString('Update lists', $this->getActualHtml());
        } else {
            $this->assertStringContainsString($this->format_the_html($expected), $this->getActualHtml());
        }
    }
    private function getActualHtml()
    {
        ob_start();
        do_action('rocket_settings_tools_content');
        return $this->format_the_html(ob_get_clean());
    }
}
