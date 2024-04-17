<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

class Test_GetRocketCacheRejectUri extends TestCase
{
    private $cache_reject_uri;
    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnExcludeDeferJSArray($config, $expected)
    {
        $this->mergeExistingSettingsAndUpdate($config['options']);
        Functions\when('rocket_get_home_dirname')->justReturn($config['home_dirname']);
        $this->cache_reject_uri = $config['filter_rocket_cache_reject_uri'];
        add_filter('rocket_cache_reject_uri', [$this, 'filter_rocket_cache_reject_uri']);
        $this->assertSame($expected, get_rocket_cache_reject_uri(true));
        remove_filter('rocket_cache_reject_uri', [$this, 'filter_rocket_cache_reject_uri']);
    }
    public function providerTestData()
    {
        return $this->getTestData(__DIR__, basename(__FILE__, '.php'));
    }
    public function filter_rocket_cache_reject_uri()
    {
        return $this->cache_reject_uri;
    }
}
