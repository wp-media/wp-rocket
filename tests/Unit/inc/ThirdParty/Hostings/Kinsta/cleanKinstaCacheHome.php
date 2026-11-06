<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Kinsta;
use Brain\Monkey\Functions;
/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Kinsta::clean_kinsta_cache_home
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_CleanKinstaCacheHome extends TestCase
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
	public function testShouldReturnAsExpected($config, $expected)
	{
		Functions\expect('get_rocket_i18n_home_url')->with($config['lang'])->andReturn($config['base_url']);
		Functions\expect('wp_safe_remote_get')->with($expected['url'], $expected['config']);
		$this->subscriber->clean_kinsta_cache_home($config['root'], $config['lang']);
	}
}
