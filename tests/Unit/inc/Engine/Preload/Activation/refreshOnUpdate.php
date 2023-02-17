<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Mockery;
use WP_Rocket\Admin\Options_Data;
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
	private $options;

	protected function setUp(): void
	{
		parent::setUp();
		$this->controller = Mockery::mock(LoadInitialSitemap::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->options = $this->createMock( Options_Data::class );
		$this->activation = new Activation($this->queue, $this->query, $this->options);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $result) {

		$this->configureReloadSitemap($result);
		$this->activation->refresh_on_update($config['new_version'], $config['old_version']);
	}

	public function configureReloadSitemap($should_preload) {
		if( ! $should_preload ) {
			$this->queue->expects()->add_job_preload_job_load_initial_sitemap_async()->never();
			return;
		}

		$this->queue->expects()->add_job_preload_job_load_initial_sitemap_async();

	}
}
