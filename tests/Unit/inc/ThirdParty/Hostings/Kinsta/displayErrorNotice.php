<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Kinsta;

use Mockery;
use WP_Rocket\Tests\Fixtures\Kinsta\Cache_Purge;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Kinsta;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Kinsta::display_error_notice
 *
 * @group  Kinsta
 * @group  ThirdParty
 */
class Test_DisplayErrorNotice extends TestCase
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
	public function testShouldReturnAsExpected($config) {
		Functions\when('__')->returnArg(1);
		Functions\expect('current_user_can')->with('manage_options')->once()->andReturn($config['has_right']);
		$this->configureScreen($config);
		$this->configureNotice($config);
		$this->subscriber->display_error_notice();
	}

	protected function configureScreen($config) {
		if(! key_exists('screen', $config)) {
			return;
		}
		Functions\expect('get_current_screen')->with()->once()->andReturn($config['screen']);
	}

	protected function configureNotice($config) {
		if(! key_exists('notice', $config)) {
			return;
		}
		Functions\expect('rocket_notice_html')->with($config['notice'])->once();
	}
}
