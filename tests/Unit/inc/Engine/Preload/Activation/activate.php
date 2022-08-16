<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Activation::activate
 * @group  Preload
 */
class Test_Activate extends TestCase
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
		$this->activation = new Activation($this->controller, $this->queue, $this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->controller->expects()->load_initial_sitemap();

		$this->activation->activate();
	}
}
