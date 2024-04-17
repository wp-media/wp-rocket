<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

class TestGetCloudflareIps extends TestCase
{
    private $cloudflare;
    private $response;
    public function set_up()
    {
        parent::set_up();
        $container = apply_filters('rocket_container', null);
        $this->cloudflare = $container->get('cloudflare');
    }
    public function tear_down()
    {
        remove_filter('pre_http_request', [$this, 'http_request']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
        $this->response = $config['response'];
        add_filter('pre_http_request', [$this, 'http_request']);
        if ($config['transient']) {
            set_transient('rocket_cloudflare_ips', $config['transient']);
        }
        $result = $this->cloudflare->get_cloudflare_ips();
        $this->assertNotFalse(get_transient('rocket_cloudflare_ips'));
        $this->assertEquals($expected, $result);
    }
    public function http_request()
    {
        return $this->response;
    }
}
