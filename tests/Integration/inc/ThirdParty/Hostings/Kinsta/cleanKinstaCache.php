<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Kinsta::clean_kinsta_cache
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_CleanKinstaCache extends TestCase {

	protected $cache;
	protected $cache_purge;

	public function set_up() {
		parent::set_up();

		$this->cache_purge = Mockery::mock(Cache_Purge::class);
		$this->cache = new Kinsta_Cache();
		$this->cache->kinsta_cache_purge = $this->cache_purge;
		$GLOBALS['kinsta_cache'] = $this->cache;
	}

	public function tear_down() {
		unset($GLOBALS['kinsta_cache']);

		parent::tear_down();
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
		do_action('rocket_after_clean_domain');
	}
}
