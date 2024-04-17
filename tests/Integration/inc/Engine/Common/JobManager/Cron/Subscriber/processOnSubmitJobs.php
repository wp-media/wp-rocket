<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\HTTPCallTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

class Test_processOnSubmitJobs extends TestCase
{
    use DBTrait, HTTPCallTrait;
    protected $config;
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::installFresh();
    }
    public static function tear_down_after_class()
    {
        self::uninstallAll();
        parent::tear_down_after_class();
    }
    public function set_up()
    {
        parent::set_up();
        add_filter('rocket_saas_max_pending_jobs', [$this, 'max_rows']);
        add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss_enabled']);
        $this->setup_http();
    }
    public function tear_down()
    {
        $this->tear_down_http();
        remove_filter('rocket_saas_max_pending_jobs', [$this, 'max_rows']);
        remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss_enabled']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected($config, $expected)
    {
        $this->config = $config;
        foreach ($config['rows'] as $row) {
            self::addResource($row);
        }
        do_action('rocket_saas_on_submit_jobs');
        foreach ($expected['rows'] as $row) {
            $this->assertTrue(self::resourceFound($row), json_encode($row) . ' not found');
        }
    }
    public function max_rows()
    {
        return $this->config['max_rows'];
    }
    public function rucss_enabled()
    {
        return $this->config['rucss_enabled'];
    }
}
