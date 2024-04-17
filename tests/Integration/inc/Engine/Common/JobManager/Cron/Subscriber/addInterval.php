<?php
declare (strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

class Test_AddInterval extends TestCase
{
    private $rucss;
    private $interval;
    public function tear_down()
    {
        remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'set_rucss_option']);
        remove_filter('rocket_saas_pending_jobs_cron_interval', [$this, 'set_interval']);
        remove_filter('rocket_remove_saas_failed_jobs_cron_interval', [$this, 'set_interval']);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected)
    {
        $this->rucss = $config['remove_unused_css'];
        $this->interval = $config['interval'];
        add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'set_rucss_option']);
        if (null !== $this->interval) {
            add_filter('rocket_saas_pending_jobs_cron_interval', [$this, 'set_interval']);
            add_filter('rocket_remove_saas_failed_jobs_cron_interval', [$this, 'set_interval']);
        }
        $schedules = apply_filters('cron_schedules', []);
        if (null === $expected) {
            $this->assertArrayNotHasKey('rocket_saas_pending_jobs', $schedules);
            $this->assertArrayNotHasKey('rocket_remove_saas_failed_jobs', $schedules);
            $this->assertArrayNotHasKey('rocket_saas_on_submit_jobs', $schedules);
        } else {
            $this->assertArrayHasKey('rocket_saas_pending_jobs', $schedules);
            $this->assertArrayHasKey('rocket_remove_saas_failed_jobs', $schedules);
            $this->assertArrayHasKey('rocket_saas_on_submit_jobs', $schedules);
            $this->assertContains($expected, $schedules);
        }
    }
    public function set_rucss_option()
    {
        return $this->rucss;
    }
    public function set_interval()
    {
        return $this->interval;
    }
}
