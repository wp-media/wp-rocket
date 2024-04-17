<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\ASTrait;
use Brain\Monkey\Functions;

class Test_PreloadUrl extends AdminTestCase
{
    use ASTrait;
    protected $mobile_cache;
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
    public function setUp() : void
    {
        parent::setUp();
        add_filter('pre_get_rocket_option_do_caching_mobile_files', [$this, 'mobile_cache']);
        add_filter('pre_http_request', [$this, 'request']);
    }
    public function tearDown() : void
    {
        parent::tearDown();
        remove_filter('pre_http_request', [$this, 'request']);
        remove_filter('pre_get_rocket_option_do_caching_mobile_files', [$this, 'mobile_cache']);
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        $this->config = $config;
        $this->mobile_cache = $config['mobile_cache'];
        self::addCache($config['existing_job']);
        do_action('rocket_preload_job_preload_url', $config['url']);
        $this->assertTrue(self::cacheFound($expected));
    }
    public function providerTestData()
    {
        return $this->getTestData(__DIR__, 'preloadUrl');
    }
    public function mobile_cache()
    {
        return $this->mobile_cache;
    }
    public function request()
    {
        if (!empty($this->config['process_generate']['is_wp_error'])) {
            return new WP_Error('error', 'error_data');
        } else {
            $message = $this->config['process_generate']['response'];
            return ['body' => $message, 'response' => ['code' => 200]];
        }
    }
}
