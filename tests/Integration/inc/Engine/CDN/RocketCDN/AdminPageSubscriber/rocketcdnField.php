<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

class Test_RocketcdnField extends TestCase
{
    public function set_up()
    {
        parent::set_up();
        add_filter('pre_get_rocket_option_cdn_cnames', [$this, 'cdn_names_cb']);
    }
    public function tear_down()
    {
        remove_filter('pre_get_rocket_option_cdn_cnames', [$this, 'cdn_names_cb']);
        delete_transient('rocketcdn_status');
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldAddRocketCdnFields($config, $expected_cdn_cnames)
    {
        $this->white_label = isset($config['white_label']) ? $config['white_label'] : $this->white_label;
        $this->cdn_names = $config['cdn_names'];
        set_transient('rocketcdn_status', $config['rocketcdn_status'], MINUTE_IN_SECONDS);
        $expected = $this->config['fields'];
        $expected['cdn_cnames'] = $expected_cdn_cnames;
        $this->assertSame($expected, apply_filters('rocket_cdn_settings_fields', $this->config['fields']));
    }
}
