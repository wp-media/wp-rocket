<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Post;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Kinsta::clean_kinsta_post_cache
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_CleanKinstaPostCache extends TestCase
{
	use FilterTrait;

	protected $cache;
	protected $cache_purge;

	public function setUp(): void
	{
		parent::setUp();
		$this->cache_purge = Mockery::mock(Cache_Purge::class);
		$this->cache = new Kinsta_Cache();
		$this->cache->kinsta_cache_purge = $this->cache_purge;
		$GLOBALS['kinsta_cache'] = $this->cache;
		$this->unregisterAllCallbacksExcept('after_rocket_clean_post', 'clean_kinsta_post_cache');
	}

	public function tearDown(): void
	{
		$this->restoreWpFilter('after_rocket_clean_post');
		unset($GLOBALS['kinsta_cache']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected)
	{
		$this->cache_purge->expects()->initiate_purge($expected['id'], $expected['type']);
		$post = new WP_Post($config['post']);
		do_action('after_rocket_clean_post', $post, [], 'lang');
	}
}
