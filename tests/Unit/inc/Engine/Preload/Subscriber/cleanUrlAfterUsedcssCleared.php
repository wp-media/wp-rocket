<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Dependencies\Detection\MobileDetect;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::clean_url_after_usedcss_cleared
 *
 * @group  Preload
 */
class Test_CleanUrlAfterUsedcssCleared extends TestCase
{
	protected $subscriber;
	protected $options;
	protected $clear_cache;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->clear_cache = Mockery::mock(ClearCache::class);
		$this->subscriber = new Subscriber(
			$this->options,
			Mockery::mock(LoadInitialSitemap::class),
			$this->createMock(Cache::class),
			Mockery::mock(Activation::class),
			Mockery::mock(MobileDetect::class),
			$this->clear_cache,
			Mockery::mock(Queue::class)
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->options->shouldReceive('get')->with('manual_preload', 0)->andReturn($config['manual_preload']);

		if ($expected['cleaned']) {
			$this->clear_cache->shouldReceive('partial_clean')->once()->with([$config['url']], true);
		} else {
			$this->clear_cache->shouldReceive('partial_clean')->never();
		}

		$this->subscriber->clean_url_after_usedcss_cleared($config['url']);
	}
}
