<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;


class Test_addPreconnectCdn extends TestCase
{
    public function set_up()
    {
        $this->unregisterAllCallbacksExcept('wp_resource_hints', 'add_preconnect_cdn', 10);
        parent::set_up();
    }
    public function tear_down()
    {
        $this->restoreWpHook('wp_resource_hints');
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldAddPreconnectCdn($cnames, $expected)
    {
        $this->cnames = $cnames;
        add_filter('pre_get_rocket_option_cdn', [$this, 'return_true']);
        add_filter('rocket_cdn_cnames', [$this, 'setCnames']);
        ob_start();
        wp_resource_hints();
        $this->assertStringContainsString($this->format_the_html($expected), $this->format_the_html(ob_get_clean()));
    }
}
