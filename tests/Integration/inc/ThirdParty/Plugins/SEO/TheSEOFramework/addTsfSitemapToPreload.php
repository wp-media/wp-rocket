<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SEO\TheSEOFramework;

use The_SEO_Framework\Bridges\Sitemap;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

class Test_AddTsfSitemapToPreload extends TestCase
{
    use IsolateHookTrait;
    protected $is_disabled;
    public function setUp() : void
    {
        parent::setUp();
        $this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_tsf_sitemap_to_preload', 15);
    }
    public function tearDown() : void
    {
        $this->restoreWpHook('rocket_sitemap_preload_list');
        remove_filter('pre_get_rocket_option_tsf_xml_sitemap', [$this, 'is_disabled']);
        parent::tearDown();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $expected)
    {
        Functions\when('rocket_get_constant')->justReturn($config['version']);
        Sitemap::$endpoints = $config['endpoints'];
        Sitemap::$url = $config['url'];
        Sitemap::$sitemap = $config['sitemap'];
        $this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
    }
}
