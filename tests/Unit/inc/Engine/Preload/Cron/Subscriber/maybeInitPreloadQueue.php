<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Cron\Subscriber::maybe_init_preload_queue
 * @group  Preload
 */
class Test_MaybeInitPreloadQueue extends TestCase
{
	protected $subscriber;
	protected $query;
	protected $settings;
	protected $controller;
	protected $queue_runner;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createMock(Cache::class);
		$this->settings = Mockery::mock(Settings::class);
		$this->controller = Mockery::mock(PreloadUrl::class);
		$this->queue_runner = Mockery::mock(PreloadQueueRunner::class);

		$this->subscriber = new Subscriber($this->settings, $this->query, $this->controller, $this->queue_runner);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->settings->expects()->is_enabled()->andReturn($config['is_enable']);
		$this->configureEnable($config);
		$this->subscriber->maybe_init_preload_queue();
	}

	protected function configureEnable($config) {
		if(! $config['is_enable']) {
			return;
		}
		$this->queue_runner->expects()->init();
	}
}
