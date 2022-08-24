<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Mockery;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_RefreshOnUpdate extends TestCase
{
	protected $activation;
	protected $controller;
	protected $queue;
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->controller = Mockery::mock(LoadInitialSitemap::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->activation = new Activation($this->queue, $this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {

		$this->configureReloadSitemap($config);
		$this->activation->refresh_on_update($config['new_version'], $config['old_version']);
	}

	public function configureReloadSitemap($config) {
		if($config['old_version'] !== '3.11.0') {
			$this->queue->expects()->add_job_preload_job_load_initial_sitemap_async()->never();
			return;
		}

		$this->queue->expects()->add_job_preload_job_load_initial_sitemap_async();

	}
}
