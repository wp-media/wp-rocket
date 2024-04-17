<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

class Test_checkJobStatus extends FilesystemTestCase
{
    protected $path_to_test_data = '/inc/Engine/Common/JobManager/Cron/Subscriber/checkJobStatus.php';
    protected $config;
    use DBTrait;
    public static function set_up_before_class()
    {
        parent::set_up_before_class();
        self::installFresh();
    }
    public function set_up()
    {
        parent::set_up();
        add_filter('pre_http_request', [$this, 'mock_http'], 10, 3);
        add_filter('rocket_rucss_hash', [$this, 'rucss_hash']);
    }
    public function tear_down()
    {
        remove_filter('rocket_rucss_hash', [$this, 'rucss_hash']);
        remove_filter('pre_http_request', [$this, 'mock_http']);
        remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'set_rucss_option']);
        parent::tear_down();
    }
    public static function tear_down_after_class()
    {
        self::uninstallAll();
        parent::tear_down_after_class();
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldDoAsExpected($config, $expected)
    {
        add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'set_rucss_option']);
        $this->config = $config;
        $id = self::addResource($config['row']);
        do_action('rocket_saas_job_check_status', $config['row']['url'], $config['row']['is_mobile'], $config['optimization_type']);
        foreach ($expected['rows'] as $row) {
            self::assertTrue(self::resourceFound($row));
        }
        foreach ($expected['files'] as $path => $file) {
            self::assertSame($file['exists'], $this->filesystem->exists($path));
        }
    }
    public function mock_http($response, $args, $url)
    {
        if ($url === $this->config['request']['url'] && $args['method'] === $this->config['request']['method']) {
            return $this->config['request']['response'];
        }
        if ($url === $this->config['create']['url'] && $args['method'] === $this->config['create']['method']) {
            return $this->config['create']['response'];
        }
        return $response;
    }
    public function rucss_hash()
    {
        return $this->config['hash'];
    }
    public function set_rucss_option()
    {
        return 1;
    }
}

