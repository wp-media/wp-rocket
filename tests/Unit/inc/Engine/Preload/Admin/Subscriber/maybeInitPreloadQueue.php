<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Admin\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Admin\Subscriber;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Admin\Subscriber::maybe_init_preload_queue
 * @group  Preload
 */
class Test_MaybeInitPreloadQueue extends TestCase
{
	protected $settings;
	protected $subscriber;
	protected $queue;
	protected $queueRunner;
	protected $logger;
	protected $option;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->settings = Mockery::mock(Settings::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->queueRunner = Mockery::mock(PreloadQueueRunner::class);
		$this->logger = Mockery::mock(Logger::class);
		$this->option = Mockery::mock(Options_Data::class);
		$this->controller = Mockery::mock(ClearCache::class);
		$this->subscriber = new Subscriber($this->option, $this->settings, $this->controller, $this->queue, $this->queueRunner, $this->logger);
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
		$this->queueRunner->expects()->init();
	}
}
