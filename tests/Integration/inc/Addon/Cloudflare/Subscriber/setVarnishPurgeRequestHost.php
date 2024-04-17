<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class TestSetVarnishPurgeRequestHost extends TestCase
{
    private $option;
    private $filter;
    public function tear_down()
    {
        remove_filter('pre_get_rocket_option_varnish_auto_purge', [$this, 'set_option']);
        remove_filter('do_rocket_varnish_http_purge', [$this, 'set_filter']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
        $this->option = $config['option'];
        $this->filter = $config['filter'];
        add_filter('pre_get_rocket_option_varnish_auto_purge', [$this, 'set_option']);
        add_filter('do_rocket_varnish_http_purge', [$this, 'set_filter']);
        $this->assertSame($expected, apply_filters('rocket_varnish_purge_request_host', $config['value']));
    }
    public function set_option()
    {
        return $this->option;
    }
    public function set_filter()
    {
        return $this->filter;
    }
}
