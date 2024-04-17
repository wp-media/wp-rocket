<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

class Test_DisplayRocketcdnStatus extends TestCase
{
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        update_option('date_format', 'Y-m-d');
    }
    public function set_up() : void
    {
        parent::set_up();
        add_filter('home_url', [$this, 'home_url_cb']);
    }
    public function tear_down()
    {
        delete_transient('rocketcdn_status');
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDisplayExpected($rocketcdn_status, $expected, $config)
    {
        $this->white_label = isset($config['white_label']) ? $config['white_label'] : $this->white_label;
        $this->home_url = $config['home_url'];
        set_transient('rocketcdn_status', $rocketcdn_status, MINUTE_IN_SECONDS);
        ob_start();
        do_action('rocket_dashboard_after_account_data');
        $actual = ob_get_clean();
        $this->assertSame($this->format_the_html($expected['integration']), $this->format_the_html($actual));
    }
}
