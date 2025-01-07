<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Kinsta;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Kinsta::remove_partial_purge_hooks
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_RemovePartialPurgeHooks extends TestCase
{
	protected $subscriber;
	protected $cache;
	protected $cache_purge;

	protected function setUp(): void
	{
		parent::setUp();
		$this->cache_purge = Mockery::mock(Cache_Purge::class);
		$this->cache = new Kinsta_Cache();
		$this->cache->kinsta_cache_purge = $this->cache_purge;
		$GLOBALS['kinsta_cache'] = $this->cache;
		$this->subscriber = new Kinsta();
	}

	protected function tearDown(): void
	{
		unset($GLOBALS['kinsta_cache']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisablePurgeHooks($expected) {
		foreach ($expected['actions'] as $action) {
			Actions\expectRemoved($action['action'])->with($action['callback']);
		}
		foreach ($expected['filters'] as $filter) {
			Filters\expectRemoved($filter['filter'])->with($filter['callback']);
		}
		$this->subscriber->remove_partial_purge_hooks();
	}
}
