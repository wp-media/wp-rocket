<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Integration\TestCase;

class Test_RemovePartialPurgeHooks extends TestCase
{
    protected $cache;
    protected $cache_purge;
    public function setUp() : void
    {
        parent::setUp();
        $this->cache_purge = Mockery::mock(Cache_Purge::class);
        $this->cache = new Kinsta_Cache();
        $this->cache->kinsta_cache_purge = $this->cache_purge;
        $GLOBALS['kinsta_cache'] = $this->cache;
    }
    public function tearDown() : void
    {
        unset($GLOBALS['kinsta_cache']);
        parent::tearDown();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldDisablePurgeHooks($expected)
    {
        do_action('wp_rocket_loaded');
        foreach ($expected['actions'] as $action) {
            $this->assertFalse(has_action($action['action'], $action['callback']));
        }
        foreach ($expected['filters'] as $filter) {
            $this->assertFalse(has_filter($filter['filter'], $filter['callback']));
        }
    }
}
