<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Hostings\Kinsta;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Kinsta::clean_kinsta_cache
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_CleanKinstaCache extends TestCase
{

	protected $cache;
	protected $cache_purge;

	public function setUp(): void
	{
		parent::setUp();
		$this->cache_purge = Mockery::mock(Cache_Purge::class);
		$this->cache = new Kinsta_Cache();
		$this->cache->kinsta_cache_purge = $this->cache_purge;
		$GLOBALS['kinsta_cache'] = $this->cache;
		$this->subscriber = new Kinsta();
	}

	public function tearDown(): void
	{
		unset($GLOBALS['kinsta_cache']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		if(! $config['has_cache']) {
			unset($this->cache->kinsta_cache_purge);
		}
		if($expected) {
			$this->cache_purge->expects()->purge_complete_caches();
		} else {
			$this->cache_purge->shouldReceive('purge_complete_caches')->never();
		}
		do_action('after_rocket_clean_domain');
	}
}
